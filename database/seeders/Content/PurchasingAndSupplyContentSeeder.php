<?php

namespace Database\Seeders\Content;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchasingAndSupplyContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Purchasing & Supply')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Purchasing & Supply" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in Purchasing & Supply already has modules. Skipping content seed.');
            return;
        }

        DB::transaction(function () {
            $modules = $this->createModules();

            foreach ($modules as $moduleIndex => $module) {
                $moduleNumber = $moduleIndex + 1;
                $lessonsData = $this->{"module{$moduleNumber}Lessons"}();

                $lessonIds = [];
                $moduleDuration = 0;

                foreach ($lessonsData as $lessonIndex => $lessonData) {
                    $displayOrder = $lessonIndex + 1;
                    $isPreview = ($moduleIndex === 0 && $lessonIndex === 0) ? 1 : 0;
                    $lessonType = $lessonData['type'] ?? 'Reading';
                    $duration = $lessonData['duration_minutes'];
                    $moduleDuration += $duration;

                    $lesson = Lesson::create([
                        'module_id' => $module->id,
                        'title' => $lessonData['title'],
                        'content' => $lessonData['content'] ?? null,
                        'lesson_type' => $lessonType,
                        'duration_minutes' => $duration,
                        'display_order' => $displayOrder,
                        'is_preview' => $isPreview,
                        'is_mandatory' => 1,
                        'points' => 10,
                    ]);

                    $lessonIds[] = $lesson->id;
                }

                $module->duration_minutes = $moduleDuration;
                $module->save();

                $quizData = $this->{"module{$moduleNumber}Quiz"}();
                $quiz = Quiz::create([
                    'course_id' => $this->courseId,
                    'lesson_id' => end($lessonIds),
                    'title' => $quizData['title'],
                    'description' => $quizData['description'],
                    'quiz_type' => 'Graded',
                    'time_limit_minutes' => $quizData['time_limit_minutes'],
                    'max_attempts' => 3,
                    'passing_score' => 60.00,
                    'show_correct_answers' => 1,
                    'is_published' => 1,
                ]);

                foreach ($quizData['questions'] as $qIndex => $qData) {
                    $question = Question::create([
                        'question_type' => $qData['type'],
                        'question_text' => $qData['text'],
                        'points' => $qData['type'] === 'Short Answer' ? 3 : 2,
                        'explanation' => $qData['explanation'],
                        'correct_answer' => $qData['correct_answer'] ?? null,
                    ]);

                    if ($qData['type'] === 'Multiple Choice') {
                        foreach ($qData['options'] as $optIndex => $opt) {
                            QuestionOption::create([
                                'question_id' => $question->question_id,
                                'option_text' => $opt['text'],
                                'is_correct' => $opt['is_correct'],
                                'display_order' => $optIndex + 1,
                            ]);
                        }
                    } elseif ($qData['type'] === 'True/False') {
                        QuestionOption::create([
                            'question_id' => $question->question_id,
                            'option_text' => 'True',
                            'is_correct' => $qData['correct_answer'] === 'True',
                            'display_order' => 1,
                        ]);
                        QuestionOption::create([
                            'question_id' => $question->question_id,
                            'option_text' => 'False',
                            'is_correct' => $qData['correct_answer'] === 'False',
                            'display_order' => 2,
                        ]);
                    }

                    DB::table('quiz_questions')->insert([
                        'quiz_id' => $quiz->id,
                        'question_id' => $question->question_id,
                        'display_order' => $qIndex + 1,
                        'points_override' => null,
                    ]);
                }
            }

            $this->createAssignments(end($lessonIds));
        });

        $this->printSummary();
    }

    private function createModules(): array
    {
        $moduleData = [
            [
                'title' => 'Module 1: Introduction to Purchasing and Supply in Zambia',
                'description' => 'Understand what purchasing and supply means for organisations, how the procurement cycle works, and the rules that guide public buying in Zambia.',
            ],
            [
                'title' => 'Module 2: Sourcing and Selecting Suppliers in Zambia',
                'description' => 'Learn how to find, evaluate and compare local and international suppliers so you buy quality goods at the right total cost.',
            ],
            [
                'title' => 'Module 3: Procurement Documents and Ordering',
                'description' => 'Master the documents that turn a need into a contract: requisitions, specifications, purchase orders, and payment records.',
            ],
            [
                'title' => 'Module 4: Receiving, Inspection and Inventory Control',
                'description' => 'Receive and inspect goods correctly, keep accurate stock records, and manage inventory for farms, schools and clinics.',
            ],
            [
                'title' => 'Module 5: Logistics, Cost Control and Procurement Reporting',
                'description' => 'Plan cross-border and local transport, manage currency and cost risks, and write procurement reports that support good decisions.',
            ],
        ];

        $modules = [];
        foreach ($moduleData as $index => $data) {
            $modules[] = Module::create([
                'course_id' => $this->courseId,
                'title' => $data['title'],
                'description' => $data['description'],
                'display_order' => $index + 1,
                'duration_minutes' => 0,
                'is_published' => 1,
            ]);
        }

        return $modules;
    }

    private function createAssignments(?int $lastLessonId): void
    {
        $assignmentData = [
            [
                'title' => 'Assignment 1: Prepare a Purchase Requisition and Supplier Comparison Table',
                'description' => 'Create the documents needed to buy a common item for a Zambian school, clinic or farm.',
                'instructions' => "<ol><li>Choose one organisation type: a rural school in Kalomo, a clinic in Choma, or a small farm near Livingstone.</li><li>Choose one item to buy (for example, 500 exercise books, a month's supply of gloves and syringes, or 20 bags of fertiliser).</li><li>Write a one-page purchase requisition that includes: date, department, item description, quantity needed, reason for the purchase, and requested delivery date.</li><li>Find or invent quotations from at least two suppliers: one local (for example, a supplier in Lusaka or Ndola) and one imported (for example, from South Africa or China).</li><li>Create a comparison table with columns for supplier name, unit price, total price, delivery time, payment terms, quality grade, and after-sales support.</li><li>Write three sentences explaining which supplier you would choose and why.</li><li>Save your work as a Word document or PDF and upload it here. Name the file: PurchasingReq_YourName.</li></ol>",
                'due_date' => now()->addWeeks(2),
            ],
            [
                'title' => 'Assignment 2: Write a Procurement Report Evaluating Local vs Imported Suppliers',
                'description' => 'Write a short report that compares local and imported sourcing for a common item in Zambia.',
                'instructions' => "<ol><li>Choose one common item such as stationery, medical supplies, farming inputs, cleaning materials or office furniture.</li><li>Write one paragraph describing the item, the quantity needed, and the organisation buying it (school, clinic, farm or small business).</li><li>Compare local Zambian suppliers with imported suppliers in at least four areas: price, delivery time, payment currency and risk, and ease of returns.</li><li>Explain how exchange rates (USD to ZMW) and transport from Dar es Salaam or Beira could change the final cost.</li><li>Give a clear recommendation: which source would you choose, and what conditions would you add to protect the buyer?</li><li>Save your work as a Word document or PDF and upload it here. Name the file: ProcurementReport_YourName.</li></ol>",
                'due_date' => now()->addWeeks(4),
            ],
        ];

        foreach ($assignmentData as $index => $data) {
            Assignment::create([
                'course_id' => $this->courseId,
                'lesson_id' => $lastLessonId,
                'title' => $data['title'],
                'description' => $data['description'],
                'instructions' => $data['instructions'],
                'max_points' => 100,
                'passing_points' => 50,
                'due_date' => $data['due_date'],
                'allow_late_submission' => 1,
                'late_penalty_percent' => 0,
                'max_file_size_mb' => 10,
                'allowed_file_types' => 'pdf,doc,docx,jpg,png',
            ]);
        }
    }

    private function printSummary(): void
    {
        $moduleCount = Module::where('course_id', $this->courseId)->count();
        $lessonCount = Lesson::whereIn('module_id', Module::where('course_id', $this->courseId)->pluck('id'))->count();
        $quizCount = Quiz::where('course_id', $this->courseId)->count();
        $questionCount = DB::table('quiz_questions')
            ->whereIn('quiz_id', Quiz::where('course_id', $this->courseId)->pluck('id'))
            ->count();
        $assignmentCount = Assignment::where('course_id', $this->courseId)->count();

        $this->command->newLine();
        $this->command->info('=== Purchasing & Supply Content Seed Summary ===');
        $this->command->table(
            ['Metric', 'Count'],
            [
                ['Modules', $moduleCount],
                ['Lessons', $lessonCount],
                ['Quizzes', $quizCount],
                ['Questions', $questionCount],
                ['Assignments', $assignmentCount],
            ]
        );
        $this->command->info('Certificate in Purchasing & Supply content seeded successfully.');
    }

    // =========================================================================
    // MODULE 1
    // =========================================================================

    private function module1Lessons(): array
    {
        return [
            [
                'title' => 'Lesson 1.1: What Purchasing & Supply Means for Organisations',
                'duration_minutes' => 60,
                'content' => $this->lesson1_1(),
            ],
            [
                'title' => 'Lesson 1.2: The Zambian Procurement Environment',
                'duration_minutes' => 60,
                'content' => $this->lesson1_2(),
            ],
            [
                'title' => 'Lesson 1.3: Roles and Ethics of a Purchasing Officer',
                'duration_minutes' => 60,
                'content' => $this->lesson1_3(),
            ],
            [
                'title' => 'Module 1 Quiz: Introduction to Purchasing and Supply',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Complete this quiz to check your understanding of purchasing and supply principles, the Zambian procurement environment, and professional ethics. You need 60% to pass.</p>',
            ],
        ];
    }

    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: Introduction to Purchasing and Supply',
            'description' => 'Test your understanding of the procurement cycle, the Zambian procurement environment, and ethical conduct.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which body regulates public procurement in Zambia?',
                    'explanation' => 'The Zambia Public Procurement Authority (ZPPA) oversees public procurement and issues standard bidding documents.',
                    'options' => [
                        ['text' => 'Bank of Zambia', 'is_correct' => false],
                        ['text' => 'Zambia Public Procurement Authority (ZPPA)', 'is_correct' => true],
                        ['text' => 'TEVETA', 'is_correct' => false],
                        ['text' => 'Zambia Revenue Authority', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In the procurement cycle, what step comes immediately after "Select Supplier"?',
                    'explanation' => 'After selecting a supplier, the buyer issues a purchase order or local purchase order to confirm the order.',
                    'options' => [
                        ['text' => 'Receive and inspect goods', 'is_correct' => false],
                        ['text' => 'Place order', 'is_correct' => true],
                        ['text' => 'Pay the invoice', 'is_correct' => false],
                        ['text' => 'Dispose of assets', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is record keeping important in purchasing?',
                    'explanation' => 'Good records create an audit trail, prove value for money, and help resolve disputes with suppliers.',
                    'options' => [
                        ['text' => 'It makes the office look tidy', 'is_correct' => false],
                        ['text' => 'It supports audits, transparency and dispute resolution', 'is_correct' => true],
                        ['text' => 'It allows secret deals with suppliers', 'is_correct' => false],
                        ['text' => 'It removes the need for a budget', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is an example of unethical behaviour in procurement?',
                    'explanation' => 'Accepting personal gifts from a supplier can create a conflict of interest and bias purchasing decisions.',
                    'options' => [
                        ['text' => 'Comparing three quotations', 'is_correct' => false],
                        ['text' => 'Keeping purchase files for five years', 'is_correct' => false],
                        ['text' => 'Accepting a cash gift from a supplier to award a contract', 'is_correct' => true],
                        ['text' => 'Writing clear specifications', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Purchasing is only about finding the lowest price.',
                    'explanation' => 'Purchasing considers total cost, quality, delivery time, payment terms and after-sales support, not just price.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What does ZPPA stand for?',
                    'explanation' => 'ZPPA stands for Zambia Public Procurement Authority.',
                    'correct_answer' => 'Zambia Public Procurement Authority',
                ],
            ],
        ];
    }

    private function lesson1_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to explain what purchasing and supply means for an organisation, describe the five main steps of the procurement cycle, and give examples of how this works in Zambian schools, clinics, farms and government departments.</p>

<h2>What Is Purchasing and Supply?</h2>
<p>Purchasing and supply is the professional work of buying the goods and services an organisation needs, at the right quality, in the right quantity, at the right price, from the right supplier, and delivered at the right time. It is not simply walking to a shop with cash. It includes planning, choosing suppliers, placing orders, receiving goods, paying invoices, and keeping records that can be checked later.</p>
<p>Every organisation in Zambia depends on purchasing and supply. A Ministry of Health clinic needs gloves, syringes and malaria test kits. A Ministry of Education school needs chalk, textbooks and desks. A farm needs seed, fertiliser and fuel. A small business in Kalomo needs stock, packaging and internet data. When purchasing is done well, money is saved, services improve and fraud is reduced.</p>

<figure><img class="lesson-diagram" src="/assets/diagrams/purchasing-supply/procurement-cycle.svg" alt="The five-stage procurement cycle used by Zambian organisations."><figcaption>Figure: The procurement cycle moves from identifying a need through supplier selection, ordering, receiving and payment.</figcaption></figure>

<h2>The Procurement Cycle in Everyday Language</h2>
<p>The diagram above shows the five stages of a typical procurement cycle. <strong>Identify need</strong> means someone in the organisation realises that something is required. A school bursar may notice that there are only twenty exercise books left in the store. A nurse may see that the clinic is running out of gloves. <strong>Select supplier</strong> means finding someone who can provide the item at a fair price and good quality. In the public sector this must follow Zambia Public Procurement Authority (ZPPA) rules.</p>
<p><strong>Place order</strong> is the moment the organisation confirms what it wants. This usually happens through a purchase order or local purchase order (LPO). <strong>Receive and inspect</strong> means checking that what arrived matches what was ordered. If 100 boxes of chalk were ordered but only 80 arrive, the receiving officer must record the shortage. Finally, <strong>pay and record</strong> means processing the supplier's invoice, making payment through bank transfer or mobile money, and filing all documents for the audit.</p>

<h2>Why Purchasing Matters in Zambia</h2>
<p>Zambia is a landlocked country. Many goods must travel long distances by road from Dar es Salaam in Tanzania or Beira in Mozambique. Transport costs, fuel prices, border delays and currency changes all affect the final price of the items an organisation buys. A purchasing officer who understands these factors can save an organisation thousands of Kwacha every year.</p>
<p>Public money must also be used transparently. The ZPPA sets rules so that government contracts are awarded fairly. Ministries, councils and public schools must advertise tenders above certain thresholds, evaluate bids openly, and keep complete files. This protects public funds and builds trust.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Think of one item your school, workplace or community organisation bought recently.</li>
<li>Write down who identified the need, who chose the supplier, and how payment was made.</li>
<li>Draw the five-stage procurement cycle for that one purchase.</li>
<li>List one risk that could have gone wrong at each stage.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Purchasing</strong>: The process of buying goods and services for an organisation.</li>
<li><strong>Supply</strong>: The flow of goods and services from suppliers to the end user.</li>
<li><strong>Procurement cycle</strong>: The repeating steps of identifying need, selecting supplier, placing order, receiving and inspecting, and paying.</li>
<li><strong>Local Purchase Order (LPO)</strong>: A document used to confirm a purchase from a supplier.</li>
<li><strong>ZPPA</strong>: Zambia Public Procurement Authority, the body that regulates public procurement.</li>
</ul>

<h2>Summary</h2>
<p>Purchasing and supply is much more than shopping. It is a structured process that helps organisations get what they need while protecting money and reputation. Understanding the procurement cycle is the first step towards becoming a professional buyer in Zambia.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.zppa.org.zm/" target="_blank" rel="noopener">Zambia Public Procurement Authority</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/procurement-fundamentals/" target="_blank" rel="noopener">Microsoft Learn — Procurement Fundamentals</a></li>
<li><a href="https://www.cips.org/" target="_blank" rel="noopener">Chartered Institute of Procurement &amp; Supply</a></li>
</ul>
HTML;
    }

    private function lesson1_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will understand the main laws and institutions that shape public procurement in Zambia, know the difference between public and private sector buying, and be able to explain why transparency matters when public funds are spent.</p>

<h2>The Zambian Procurement Landscape</h2>
<p>In Zambia, procurement happens in both the public and private sectors. A private company can usually choose its own suppliers as long as it gets value for money. A public school, hospital or ministry must follow extra rules because the money comes from taxpayers. The main law is the Public Procurement Act, which is enforced by the Zambia Public Procurement Authority, commonly called ZPPA.</p>
<p>ZPPA publishes standard bidding documents, sets thresholds for competitive bidding, and trains procurement officers across ministries and local authorities. If a public institution wants to buy goods or services above a set amount, it must advertise the tender, give enough time for bidders to respond, evaluate bids fairly, and publish the award. These steps reduce corruption and make sure public money is used for the benefit of citizens.</p>

<h2>Public vs Private Procurement</h2>
<p>Public procurement must be transparent, competitive and fair. Decisions are recorded and can be audited. In contrast, a private farm or shop can negotiate directly with a trusted supplier. Both sectors, however, need good planning. A clinic run by a church and a government clinic both need reliable gloves, but only the government clinic must follow ZPPA procedures.</p>
<p>Large organisations such as the Ministry of Health, Ministry of Education and local councils buy huge volumes every year. They often use framework contracts with approved suppliers. A framework contract means the organisation pre-qualifies several suppliers for one year and then calls off goods when needed. This saves time and ensures quality standards.</p>

<h2>Local Content and Social Value</h2>
<p>Zambian procurement policy encourages buying from local suppliers where possible. Local buying reduces transport costs, creates jobs, and keeps money inside the country. However, some specialised items such as medical equipment or certain ICT hardware may only be available from South Africa, India or China. A good purchasing officer balances local content rules with the need for quality and value.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Find one example of a public tender advertised by a Zambian ministry or council.</li>
<li>List three rules that public buyers must follow but private buyers may not.</li>
<li>Explain why local content is encouraged in Zambian procurement.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Public procurement</strong>: Buying goods and services with government or public money.</li>
<li><strong>Tender</strong>: A formal invitation to suppliers to submit bids for a contract.</li>
<li><strong>Threshold</strong>: A money limit above which special rules such as open tendering must be used.</li>
<li><strong>Framework contract</strong>: A long-term agreement with pre-approved suppliers for repeated purchases.</li>
<li><strong>Local content</strong>: Preferring goods and services produced within Zambia.</li>
</ul>

<h2>Summary</h2>
<p>Zambian public procurement is guided by law, transparency and value for money. Understanding ZPPA rules, threshold limits and the difference between public and private buying helps a purchasing officer avoid mistakes and legal problems.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.zppa.org.zm/" target="_blank" rel="noopener">Zambia Public Procurement Authority</a></li>
<li><a href="https://www.afdb.org/en/topics-and-sectors/initiatives-partnerships/public-procurement" target="_blank" rel="noopener">African Development Bank — Public Procurement</a></li>
</ul>
HTML;
    }

    private function lesson1_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will know the main duties of a purchasing officer, understand the importance of ethical behaviour, and be able to identify situations that create a conflict of interest.</p>

<h2>Who Is a Purchasing Officer?</h2>
<p>A purchasing officer, sometimes called a procurement officer or buyer, is the person responsible for getting the goods and services an organisation needs. In a small school this might be the bursar or head teacher. In a large ministry it is a trained professional working in a procurement unit. The job includes preparing requisitions, finding suppliers, evaluating quotations, raising purchase orders, receiving goods, checking invoices and keeping files.</p>
<p>The purchasing officer also acts as a guardian of money. A careless buyer can waste funds, buy poor quality goods, or cause projects to stop because materials do not arrive. A dishonest buyer can award contracts to friends or family and steal from the organisation. For this reason, honesty and accuracy are just as important as negotiation skills.</p>

<h2>Ethics in Purchasing</h2>
<p>Ethical purchasing means making decisions based on the best interest of the organisation, not personal gain. It requires transparency, fairness, confidentiality and professionalism. A purchasing officer should never accept cash, expensive gifts, free holidays or secret commissions from suppliers. Even small favours such as airtime or a free lunch can create pressure to favour one supplier over another.</p>
<p><strong>Conflict of interest</strong> happens when a buyer has a personal relationship with a supplier. For example, if a purchasing officer's brother owns a stationery company, the officer should declare the relationship and remove themselves from decisions about that supplier. Many organisations require staff to sign a declaration of interest each year.</p>

<h2>Practical Standards in Zambia</h2>
<p>Zambian public officers must follow the Code of Conduct in the Public Service. The code expects impartiality, accountability and integrity. Keeping proper records is not just good practice; it is a legal requirement. A well-organised file should contain the requisition, quotations, evaluation report, purchase order, delivery note, inspection report, invoice and proof of payment. If an auditor visits, these documents prove that the purchase was proper.</p>

<h2>Try It Yourself</h2>
<ol>
<li>List five qualities of a good purchasing officer.</li>
<li>Describe a situation that would be a conflict of interest.</li>
<li>Write three rules you would include in a code of conduct for buyers in your organisation.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Purchasing officer</strong>: The person responsible for buying goods and services for an organisation.</li>
<li><strong>Conflict of interest</strong>: A situation where personal interests could influence professional decisions.</li>
<li><strong>Declaration of interest</strong>: A written statement disclosing relationships that may affect impartiality.</li>
<li><strong>Transparency</strong>: Making procurement decisions and records open to review.</li>
<li><strong>Accountability</strong>: Being answerable for how public or organisational money is spent.</li>
</ul>

<h2>Summary</h2>
<p>A purchasing officer holds a position of trust. Ethical behaviour, clear records and avoidance of conflicts of interest protect both the organisation and the officer's reputation.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.zppa.org.zm/" target="_blank" rel="noopener">Zambia Public Procurement Authority — Ethics</a></li>
<li><a href="https://www.cips.org/knowledge/procurement-topics/ethics/" target="_blank" rel="noopener">CIPS — Procurement Ethics</a></li>
</ul>
HTML;
    }

    // =========================================================================
    // MODULE 2
    // =========================================================================

    private function module2Lessons(): array
    {
        return [
            [
                'title' => 'Lesson 2.1: Identifying Local and International Suppliers',
                'duration_minutes' => 60,
                'content' => $this->lesson2_1(),
            ],
            [
                'title' => 'Lesson 2.2: Evaluating and Comparing Supplier Quotations',
                'duration_minutes' => 75,
                'content' => $this->lesson2_2(),
            ],
            [
                'title' => 'Lesson 2.3: Building Long-Term Supplier Relationships',
                'duration_minutes' => 60,
                'content' => $this->lesson2_3(),
            ],
            [
                'title' => 'Module 2 Quiz: Sourcing and Selecting Suppliers',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your knowledge of supplier identification, quotation evaluation, and relationship management. You need 60% to pass.</p>',
            ],
        ];
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Sourcing and Selecting Suppliers',
            'description' => 'Assess your understanding of supplier sourcing, quotation comparison and supplier relationship management.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is an advantage of using a local Zambian supplier?',
                    'explanation' => 'Local suppliers usually deliver faster, accept Kwacha, and are easier to visit if there is a problem.',
                    'options' => [
                        ['text' => 'No need to inspect goods', 'is_correct' => false],
                        ['text' => 'Faster delivery and payment in ZMW', 'is_correct' => true],
                        ['text' => 'Always the lowest price', 'is_correct' => false],
                        ['text' => 'No contract is needed', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'When comparing quotations, what does "total cost of ownership" include?',
                    'explanation' => 'Total cost includes price, transport, customs duties, insurance, financing and after-sales costs, not just the quoted unit price.',
                    'options' => [
                        ['text' => 'Only the unit price on the quotation', 'is_correct' => false],
                        ['text' => 'Price plus transport, duties, insurance and operating costs', 'is_correct' => true],
                        ['text' => 'The supplier\'s profit margin only', 'is_correct' => false],
                        ['text' => 'The buyer\'s salary', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which Zambian city is well known as a wholesale and distribution centre for many goods?',
                    'explanation' => 'Lusaka is the capital and main commercial hub, with major wholesale markets and distribution warehouses.',
                    'options' => [
                        ['text' => 'Mongu', 'is_correct' => false],
                        ['text' => 'Lusaka', 'is_correct' => true],
                        ['text' => 'Kaoma', 'is_correct' => false],
                        ['text' => 'Mansa', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is a common risk when importing from China or South Africa?',
                    'explanation' => 'Exchange rates between USD and ZMW can change between order and payment, increasing the real cost.',
                    'options' => [
                        ['text' => 'Goods always arrive within one day', 'is_correct' => false],
                        ['text' => 'Foreign exchange rate changes', 'is_correct' => true],
                        ['text' => 'No transport is needed', 'is_correct' => false],
                        ['text' => 'Quality is always higher', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A long-term supplier relationship is based only on getting the lowest price every time.',
                    'explanation' => 'Good relationships balance price, quality, reliability, communication and trust over time.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What document does a supplier send to a buyer showing the price of goods or services?',
                    'explanation' => 'A quotation is a supplier\'s offer stating price, terms and conditions for a requested item.',
                    'correct_answer' => 'Quotation',
                ],
            ],
        ];
    }

    private function lesson2_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to find potential suppliers inside Zambia and abroad, list the main sources of supplier information, and explain the first questions to ask before placing an order.</p>

<h2>Finding Suppliers Inside Zambia</h2>
<p>Zambia has a growing network of suppliers, distributors and manufacturers. The main commercial hubs are Lusaka, Kitwe, Ndola and, to a lesser extent, Livingstone. A school in Kalomo looking for stationery may find a wholesaler in Lusaka who delivers by bus or courier. A clinic needing medical gloves may order from a distributor in Ndola who keeps stock imported from South Africa.</p>
<p>Local suppliers can be found through trade directories, the Zambia Chamber of Commerce, sector associations, word of mouth, trade fairs, and online searches. Many wholesalers now use WhatsApp Business to share catalogues and prices. Visiting a supplier's premises is valuable because you can see their stock, storage conditions and professionalism before you commit.</p>

<figure><img class="lesson-diagram" src="/assets/diagrams/purchasing-supply/local-vs-imported-comparison.svg" alt="Comparison of sourcing locally versus importing for a Zambian organisation."><figcaption>Figure: Compare local and imported suppliers using price, delivery time, currency risk and after-sales support.</figcaption></figure>

<h2>Finding International Suppliers</h2>
<p>Some items are not made in Zambia or are cheaper to import in large quantities. International suppliers can be found through manufacturer websites, Alibaba, trade missions, and business matching events organised by the Zambia Development Agency. Common source countries for Zambian buyers include South Africa for machinery and vehicles, China for electronics and hardware, and India for pharmaceuticals and textiles.</p>
<p>When importing, the buyer must think beyond the unit price. There is freight cost, insurance, customs duty, value-added tax, clearing agent fees, inland transport from Dar es Salaam or Beira, and the risk that the exchange rate between the US Dollar and the Zambian Kwacha may move before payment. All of these items must be added to the quotation to know the true landed cost.</p>

<h2>First Questions to Ask a Supplier</h2>
<p>Before requesting a formal quotation, a buyer should ask:</p>
<ul>
<li>Do you keep stock in Zambia, or do you import on order?</li>
<li>What is your minimum order quantity?</li>
<li>How long is your normal delivery time?</li>
<li>What payment terms do you offer?</li>
<li>Can you provide references from other Zambian customers?</li>
<li>What happens if the goods are damaged or wrong?</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Choose one product your organisation might buy.</li>
<li>List three possible local suppliers and one possible international supplier.</li>
<li>Write five questions you would ask each supplier before requesting a quotation.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Supplier</strong>: A person or company that provides goods or services.</li>
<li><strong>Quotation</strong>: A supplier's written offer showing price and terms.</li>
<li><strong>Landed cost</strong>: The total cost of an imported item including price, freight, insurance, duty and inland transport.</li>
<li><strong>Minimum order quantity</strong>: The smallest amount a supplier is willing to sell in one order.</li>
<li><strong>Payment terms</strong>: The agreed conditions and timing for payment.</li>
</ul>

<h2>Summary</h2>
<p>Finding the right supplier means looking at both local and international options and asking clear questions up front. A supplier who looks cheap on paper may become expensive once transport, duty and currency risk are included.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.zda.org.zm/" target="_blank" rel="noopener">Zambia Development Agency</a></li>
<li><a href="https://www.zccm-ih.com.zm/" target="_blank" rel="noopener">Zambia Chamber of Commerce</a></li>
<li><a href="https://www.trade.gov/" target="_blank" rel="noopener">Trade.gov — Find International Suppliers</a></li>
</ul>
HTML;
    }

    private function lesson2_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to compare supplier quotations fairly, calculate total cost of ownership, and select the supplier that offers the best overall value for a Zambian organisation.</p>

<h2>More Than the Sticker Price</h2>
<p>The biggest mistake a buyer can make is to choose the supplier with the lowest unit price without checking what is included. One quotation may show K25 per box of gloves, while another shows K28. At first the K25 quotation looks cheaper. But if the K25 supplier charges K150 for transport and the K28 supplier delivers free for orders above K500, the second option may be better.</p>
<p>Total cost of ownership means adding every cost that will be paid over the life of the purchase. This includes purchase price, transport, customs duty, insurance, bank charges, installation, training, spare parts, maintenance and disposal. It also includes risk costs such as late delivery or poor quality.</p>

<h2>A Worked Example: Buying Desks for a School</h2>
<p>Suppose a school needs fifty desks. Supplier A quotes K450 per desk, delivery in two weeks, payment on order. Supplier B quotes K470 per desk, delivery in one week, payment thirty days after delivery, and a one-year repair guarantee. At first, Supplier A is cheaper by K1,000 overall. But if delayed desks mean classes start without furniture, the school may lose more than K1,000 in teaching time and reputation. Supplier B's faster delivery and credit terms may be worth the extra cost.</p>

<h2>Using a Comparison Table</h2>
<p>A good way to compare suppliers is to build a table with one row for each supplier and columns for price, quality, delivery time, payment terms, after-sales support, references and total cost. Give each criterion a weight if some are more important than others. For a hospital buying surgical equipment, quality and after-sales support may be more important than price. For a school buying cleaning materials, price and delivery speed may matter most.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Find or invent three quotations for one item.</li>
<li>Build a comparison table with at least six columns.</li>
<li>Calculate the total landed cost for any imported option.</li>
<li>Write two sentences explaining which supplier you would choose and why.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Total cost of ownership</strong>: The full cost of buying, using and disposing of an item.</li>
<li><strong>Landed cost</strong>: The cost of an imported item after freight, insurance and duty.</li>
<li><strong>Payment terms</strong>: Conditions such as cash on delivery, payment in advance, or thirty days credit.</li>
<li><strong>After-sales support</strong>: Service provided after purchase, such as repairs, spare parts or training.</li>
<li><strong>Weighted score</strong>: A comparison method that gives different importance to each criterion.</li>
</ul>

<h2>Summary</h2>
<p>Choosing a supplier is a value decision, not just a price decision. A clear comparison table that includes total cost, delivery, payment terms and support helps the buyer defend the choice to management and auditors.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.cips.org/knowledge/procurement-topics/supplier-management/" target="_blank" rel="noopener">CIPS — Supplier Management</a></li>
<li><a href="https://www.procurementclassroom.com/total-cost-of-ownership/" target="_blank" rel="noopener">Procurement Classroom — Total Cost of Ownership</a></li>
</ul>
HTML;
    }

    private function lesson2_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will understand why long-term supplier relationships matter, know how to communicate expectations clearly, and be able to conduct a simple supplier performance review.</p>

<h2>Why Relationships Matter</h2>
<p>Buying from the cheapest supplier every time can create problems. A new supplier may not understand your needs, may deliver late, or may disappear after the sale. A trusted supplier who knows your organisation will warn you about stock shortages, suggest better products, and sometimes give you credit during a difficult month. In Zambia, where mobile money and cross-border transport can be unpredictable, a reliable supplier is a valuable partner.</p>

<h2>Setting Clear Expectations</h2>
<p>Good relationships start with clear agreements. A purchase order or contract should state exactly what is being bought, the quantity, quality standard, delivery date, place of delivery, price, payment terms, and what happens if something goes wrong. Verbal agreements are risky because memories differ. Written terms protect both the buyer and the supplier.</p>
<p>Buyers should also treat suppliers fairly. Paying invoices on time, responding to questions quickly, and giving honest feedback build loyalty. If a supplier makes a mistake, the buyer should report it formally and give a chance to correct it before ending the relationship.</p>

<h2>Supplier Performance Review</h2>
<p>A simple performance review can be done every six months. Rate the supplier on delivery time, quality, price stability, communication and problem solving. Keep a short record with scores and comments. If scores fall, meet the supplier, explain the problem, and agree on improvements. If scores stay low, it may be time to find a replacement.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Design a one-page supplier scorecard with five criteria and a 1-5 rating scale.</li>
<li>Think of one supplier you know and score them honestly.</li>
<li>Write one improvement request you would send to that supplier.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Supplier relationship management</strong>: The practice of building and maintaining productive supplier partnerships.</li>
<li><strong>Performance review</strong>: A regular assessment of how well a supplier meets agreed standards.</li>
<li><strong>Scorecard</strong>: A simple tool that rates a supplier against several criteria.</li>
<li><strong>Loyalty</strong>: Continued business given to a supplier who consistently performs well.</li>
</ul>

<h2>Summary</h2>
<p>Strong supplier relationships reduce risk and improve service. Clear contracts, fair treatment and regular performance reviews turn buying from a one-off transaction into a partnership.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.cips.org/knowledge/procurement-topics/supplier-relationship-management/" target="_blank" rel="noopener">CIPS — Supplier Relationship Management</a></li>
</ul>
HTML;
    }

    // =========================================================================
    // MODULE 3
    // =========================================================================

    private function module3Lessons(): array
    {
        return [
            [
                'title' => 'Lesson 3.1: Purchase Requisitions and Specifications',
                'duration_minutes' => 60,
                'content' => $this->lesson3_1(),
            ],
            [
                'title' => 'Lesson 3.2: Purchase Orders and Contracts',
                'duration_minutes' => 75,
                'content' => $this->lesson3_2(),
            ],
            [
                'title' => 'Lesson 3.3: Payments, Mobile Money and Record Keeping',
                'duration_minutes' => 60,
                'content' => $this->lesson3_3(),
            ],
            [
                'title' => 'Module 3 Quiz: Procurement Documents and Ordering',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your understanding of requisitions, specifications, purchase orders, contracts and payment records. You need 60% to pass.</p>',
            ],
        ];
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Procurement Documents and Ordering',
            'description' => 'Assess your understanding of procurement documents, ordering and payment records.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which document is usually created FIRST in the procurement cycle?',
                    'explanation' => 'The user department creates a purchase requisition to ask the buying department to source an item.',
                    'options' => [
                        ['text' => 'Purchase order', 'is_correct' => false],
                        ['text' => 'Invoice', 'is_correct' => false],
                        ['text' => 'Purchase requisition', 'is_correct' => true],
                        ['text' => 'Goods received note', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is a clear specification important?',
                    'explanation' => 'A clear specification tells the supplier exactly what is required, reducing mistakes and disputes.',
                    'options' => [
                        ['text' => 'It makes the document longer', 'is_correct' => false],
                        ['text' => 'It reduces mistakes and disputes', 'is_correct' => true],
                        ['text' => 'It hides the real need from the supplier', 'is_correct' => false],
                        ['text' => 'It removes the need for a budget', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main purpose of a purchase order?',
                    'explanation' => 'A purchase order is the buyer\'s formal instruction to the supplier to supply goods or services on stated terms.',
                    'options' => [
                        ['text' => 'To request approval to buy', 'is_correct' => false],
                        ['text' => 'To confirm the order and terms to the supplier', 'is_correct' => true],
                        ['text' => 'To receive goods into the store', 'is_correct' => false],
                        ['text' => 'To advertise a tender', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a common mobile money payment method in Zambia?',
                    'explanation' => 'MTN Mobile Money and Airtel Money are widely used mobile money services in Zambia.',
                    'options' => [
                        ['text' => 'PayPal only', 'is_correct' => false],
                        ['text' => 'MTN Mobile Money or Airtel Money', 'is_correct' => true],
                        ['text' => 'Bitcoin only', 'is_correct' => false],
                        ['text' => 'Postal order', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A verbal order is enough for expensive or repeated purchases.',
                    'explanation' => 'Written records protect both buyer and supplier and are needed for audits.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What does LPO commonly stand for in Zambian purchasing?',
                    'explanation' => 'LPO stands for Local Purchase Order, a common document used to confirm purchases from local suppliers.',
                    'correct_answer' => 'Local Purchase Order',
                ],
            ],
        ];
    }

    private function lesson3_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to write a clear purchase requisition, describe the item needed with accurate specifications, and explain why good specifications help both buyers and suppliers.</p>

<h2>The Purchase Requisition</h2>
<p>A purchase requisition is the document a department uses to ask the purchasing unit to buy something. It is the starting point of formal buying. A good requisition answers the questions: what, how much, why, when, and who needs it. It should also state the budget code or funding source so the finance office knows the money is available.</p>
<p>For example, a head teacher at a school in Kalomo may complete a requisition for two hundred exercise books for Grade 8 pupils, to be delivered before the start of term, charged to the stationery budget. Without this document, the bursar would not know whether the request is urgent, how many books are needed, or whether money has been set aside.</p>

<h2>Writing a Good Specification</h2>
<p>A specification describes exactly what is required. It can include size, weight, colour, material, brand, model number, performance standard, packaging and quantity. The more specific the specification, the less likely the supplier will deliver the wrong item. However, the specification should not be so narrow that only one supplier can meet it, unless there is a genuine technical reason.</p>
<p>Example of a weak specification: "Buy paper." Example of a strong specification: "A4 white photocopy paper, 80 gsm, 500 sheets per ream, 10 reams, suitable for laser and inkjet printers, delivered in sealed cartons." The second version removes guesswork and makes comparison easier.</p>

<h2>Approval Workflow</h2>
<p>In most organisations, requisitions must be approved before a purchase is made. The approval level depends on the amount and type of purchase. A head of department may approve small items, while the principal or board may approve large or unusual purchases. In public bodies, ZPPA thresholds also determine who must approve and how many quotations are required.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write a purchase requisition for ten bags of cement for a rural school.</li>
<li>Include what, quantity, reason, delivery date and budget line.</li>
<li>Improve a weak specification by adding at least four details.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Purchase requisition</strong>: An internal request to buy goods or services.</li>
<li><strong>Specification</strong>: A detailed description of what is required.</li>
<li><strong>Budget code</strong>: A reference that links a purchase to an approved budget line.</li>
<li><strong>Approval workflow</strong>: The sequence of people who must authorise a purchase.</li>
</ul>

<h2>Summary</h2>
<p>Clear requisitions and specifications prevent confusion, speed up approval, and help suppliers quote accurately. They are the foundation of professional purchasing.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.zppa.org.zm/" target="_blank" rel="noopener">ZPPA — Standard Documents</a></li>
<li><a href="https://www.cips.org/knowledge/procurement-topics/specification/" target="_blank" rel="noopener">CIPS — Writing Specifications</a></li>
</ul>
HTML;
    }

    private function lesson3_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to explain the purpose of a purchase order, list the key terms it should contain, and describe when a formal contract is needed instead of a simple order.</p>

<h2>From Requisition to Order</h2>
<p>Once the requisition is approved and a supplier is selected, the buyer issues a purchase order or local purchase order (LPO). The LPO is the buyer's official offer to buy. It becomes a binding document once the supplier accepts it. In Zambia, LPOs are commonly used for local purchases, while international orders may use pro forma invoices and more detailed contracts.</p>

<h2>Key Contents of a Purchase Order</h2>
<p>A purchase order should include:</p>
<ul>
<li>Buyer and supplier names and addresses</li>
<li>LPO number and date</li>
<li>Description of goods or services with quantities and specifications</li>
<li>Unit price and total amount</li>
<li>Delivery date and delivery address</li>
<li>Payment terms</li>
<li>Any special conditions, such as inspection before payment</li>
<li>Authorising signature</li>
</ul>
<p>Keep a copy of every purchase order. It is the proof of what was agreed and is essential if a dispute arises.</p>

<h2>When to Use a Contract</h2>
<p>A one-off purchase can usually be handled with a purchase order. However, long-term or high-value supply should be covered by a written contract. Contracts are useful for framework agreements, service-level agreements, leasing equipment, construction work, and any arrangement where failure would seriously harm the organisation. A contract sets out responsibilities, penalties for late delivery, warranties, termination conditions, and how disputes will be resolved.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Draft a simple LPO for ten boxes of chalk for a school.</li>
<li>List five clauses you would add to a contract for a one-year cleaning service.</li>
<li>Explain the difference between a purchase order and a contract in two sentences.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Purchase order (PO)</strong>: A document issued by a buyer to a seller confirming a purchase.</li>
<li><strong>Local Purchase Order (LPO)</strong>: A purchase order used for local suppliers in Zambia.</li>
<li><strong>Contract</strong>: A legally binding agreement that sets out rights and duties.</li>
<li><strong>Pro forma invoice</strong>: A supplier's preliminary bill, often used for import orders.</li>
</ul>

<h2>Summary</h2>
<p>Purchase orders and contracts turn agreements into enforceable records. A complete purchase order protects the buyer, while a contract is needed for complex or long-term arrangements.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.zppa.org.zm/" target="_blank" rel="noopener">ZPPA — Contract Management</a></li>
<li><a href="https://www.investopedia.com/terms/p/purchase-order.asp" target="_blank" rel="noopener">Investopedia — Purchase Order</a></li>
</ul>
HTML;
    }

    private function lesson3_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to explain common payment methods in Zambia, understand the advantages and risks of mobile money, and describe the records that must be kept after payment.</p>

<h2>Payment Methods in Zambia</h2>
<p>Organisations in Zambia pay suppliers using several methods. Bank transfer is common for large or formal payments because it creates a clear record. Cash may be used for small purchases but is risky because it is hard to trace. Cheques are still used by some organisations although they are becoming less common. Mobile money services such as MTN Mobile Money and Airtel Money are convenient for small and urgent payments, especially when a supplier does not have a bank account.</p>

<h2>Mobile Money for Procurement</h2>
<p>Mobile money has changed how small businesses and even some organisations pay for goods. A clinic buying vegetables from a local market vendor can pay instantly using Airtel Money. A school paying a casual labourer for urgent repairs can use MTN Mobile Money. The buyer receives a confirmation SMS with a transaction ID, which should be saved as proof of payment.</p>
<p>However, mobile money has limits. Transaction limits may be lower than bank transfers, fees apply, and both sender and receiver need registered wallets. For large procurements, mobile money is usually not appropriate. Each organisation should have a payment policy that states when mobile money is allowed and who can authorise it.</p>

<h2>The Payment Record Trail</h2>
<p>After paying a supplier, the buyer must keep the invoice, the LPO, the delivery note, the goods received note, proof of payment, and any correspondence. This file proves that the purchase was authorised, delivered and paid for. During an audit or a dispute, missing documents can cause serious problems.</p>

<h2>Try It Yourself</h2>
<ol>
<li>List three advantages and three disadvantages of mobile money payments.</li>
<li>Describe the documents that should be in a complete payment file.</li>
<li>Write a short payment policy for a small organisation.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Bank transfer</strong>: Payment made directly from the buyer's bank account to the supplier's account.</li>
<li><strong>Mobile money</strong>: Electronic money service operated by mobile network providers.</li>
<li><strong>Proof of payment</strong>: A receipt, bank slip or SMS confirming that payment was made.</li>
<li><strong>Invoice</strong>: A supplier's request for payment showing what was supplied and how much is owed.</li>
</ul>

<h2>Summary</h2>
<p>Choosing the right payment method and keeping a complete record trail protects the organisation and the supplier. Mobile money is useful for small payments, while bank transfers remain the standard for larger transactions.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.boz.zm/" target="_blank" rel="noopener">Bank of Zambia</a></li>
<li><a href="https://www.mtn.co.zm/mobile-money/" target="_blank" rel="noopener">MTN Zambia — Mobile Money</a></li>
<li><a href="https://www.airtel.co.zm/airtel-money/" target="_blank" rel="noopener">Airtel Zambia — Airtel Money</a></li>
</ul>
HTML;
    }

    // =========================================================================
    // MODULE 4
    // =========================================================================

    private function module4Lessons(): array
    {
        return [
            [
                'title' => 'Lesson 4.1: Goods Receipt, Inspection and Returns',
                'duration_minutes' => 60,
                'content' => $this->lesson4_1(),
            ],
            [
                'title' => 'Lesson 4.2: Stores and Inventory Management',
                'duration_minutes' => 75,
                'content' => $this->lesson4_2(),
            ],
            [
                'title' => 'Lesson 4.3: Distribution to Farms, Schools and Clinics',
                'duration_minutes' => 60,
                'content' => $this->lesson4_3(),
            ],
            [
                'title' => 'Module 4 Quiz: Receiving, Inspection and Inventory Control',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your understanding of receiving goods, inspection, returns, inventory control and distribution. You need 60% to pass.</p>',
            ],
        ];
    }

    private function module4Quiz(): array
    {
        return [
            'title' => 'Module 4 Quiz: Receiving, Inspection and Inventory Control',
            'description' => 'Assess your understanding of receiving, inspection, inventory management and distribution.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should a receiving officer do FIRST when a delivery arrives?',
                    'explanation' => 'The receiving officer should check the delivery against the purchase order and count the items before signing.',
                    'options' => [
                        ['text' => 'Pay the driver immediately', 'is_correct' => false],
                        ['text' => 'Check the goods against the purchase order and count them', 'is_correct' => true],
                        ['text' => 'Store everything without checking', 'is_correct' => false],
                        ['text' => 'Return the goods automatically', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does GRN stand for?',
                    'explanation' => 'GRN stands for Goods Received Note, the document that records what was accepted from a delivery.',
                    'options' => [
                        ['text' => 'General Receipt Number', 'is_correct' => false],
                        ['text' => 'Goods Received Note', 'is_correct' => true],
                        ['text' => 'Goods Return Notice', 'is_correct' => false],
                        ['text' => 'Government Reference Number', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which method assumes that the oldest stock is used first?',
                    'explanation' => 'First In First Out (FIFO) means the oldest stock is issued first, reducing the risk of expiry.',
                    'options' => [
                        ['text' => 'Last In First Out', 'is_correct' => false],
                        ['text' => 'First In First Out', 'is_correct' => true],
                        ['text' => 'Highest price first', 'is_correct' => false],
                        ['text' => 'Random issue', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is a reorder level?',
                    'explanation' => 'The reorder level is the stock quantity at which a new order should be placed to avoid running out.',
                    'options' => [
                        ['text' => 'The maximum stock allowed', 'is_correct' => false],
                        ['text' => 'The point at which to place a new order', 'is_correct' => true],
                        ['text' => 'The quantity sold last month', 'is_correct' => false],
                        ['text' => 'The annual budget for stock', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A damaged delivery should be accepted and hidden from records to avoid conflict with the supplier.',
                    'explanation' => 'Damaged or wrong deliveries must be recorded and reported so the supplier can correct the problem.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What does FIFO stand for in inventory management?',
                    'explanation' => 'FIFO stands for First In First Out, an inventory issuing method.',
                    'correct_answer' => 'First In First Out',
                ],
            ],
        ];
    }

    private function lesson4_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to receive and inspect goods correctly, complete a Goods Received Note, and handle damaged or incorrect deliveries professionally.</p>

<h2>The Receiving Process</h2>
<p>Receiving goods is a critical control point. It is the moment the organisation takes ownership of what it bought. If receiving is done poorly, the organisation may pay for items it never received, accept poor quality, or lose the right to complain later. The receiving officer must therefore check every delivery carefully before signing the supplier's delivery note.</p>

<figure><img class="lesson-diagram" src="/assets/diagrams/purchasing-supply/supply-chain-zambia.svg" alt="Supply chain flow into Zambia from source to end user."><figcaption>Figure: Goods move from overseas source through port and distribution hub before reaching the final user.</figcaption></figure>

<h2>Checking the Delivery</h2>
<p>When a truck arrives at a school, clinic or farm, the receiving officer should compare the delivery note with the purchase order. Count the boxes or bags. Open a sample if necessary. Check for visible damage, wrong sizes, short delivery, or expired items. If the order was for 100 boxes of gloves but only 80 arrive, the officer must note the shortage on the delivery note and ask the driver to sign the correction.</p>
<p>For imports arriving through Dar es Salaam or Beira, delays and damage are more likely. The receiving officer should photograph damaged containers, keep the original packaging, and notify the supplier and clearing agent immediately.</p>

<h2>Goods Received Note</h2>
<p>A Goods Received Note (GRN) is an internal document that records what was actually accepted. It usually includes the date, supplier name, LPO number, item description, quantity received, condition, and the signature of the receiving officer. The GRN, delivery note and purchase order should match before the invoice is approved for payment.</p>

<h2>Returning Goods</h2>
<p>If goods are wrong, damaged or poor quality, the buyer has the right to return them or ask for a replacement. The return must be documented with a Goods Return Note. The buyer should inform the supplier quickly and keep copies of all correspondence. A good supplier will accept returns and correct the problem. A supplier who refuses returns is a sign that the relationship may not be reliable.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Design a simple Goods Received Note form for a clinic.</li>
<li>Write the steps a receiving officer should follow when a delivery arrives.</li>
<li>Describe how you would handle a delivery of ten broken chairs.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Receiving</strong>: Accepting and checking goods delivered by a supplier.</li>
<li><strong>Delivery note</strong>: A supplier's document listing what was delivered.</li>
<li><strong>Goods Received Note (GRN)</strong>: An internal record of goods accepted.</li>
<li><strong>Short delivery</strong>: When fewer items are delivered than ordered.</li>
<li><strong>Goods Return Note</strong>: A document recording goods sent back to a supplier.</li>
</ul>

<h2>Summary</h2>
<p>Careful receiving and inspection protect the organisation from paying for incorrect or damaged goods. A complete GRN and clear return process create an audit trail and hold suppliers accountable.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.cips.org/knowledge/procurement-topics/logistics/" target="_blank" rel="noopener">CIPS — Logistics and Receiving</a></li>
<li><a href="https://www.zppa.org.zm/" target="_blank" rel="noopener">ZPPA — Inspection Guidelines</a></li>
</ul>
HTML;
    }

    private function lesson4_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will understand the purpose of inventory control, be able to use a stock card, and know how to set reorder levels and safety stock for common Zambian organisations.</p>

<h2>Why Inventory Control Matters</h2>
<p>Inventory is money sitting on a shelf. If an organisation holds too much stock, it ties up cash that could be used elsewhere. If it holds too little, operations stop. A clinic cannot treat patients if it runs out of gloves. A school cannot print exams if it runs out of paper. A farm cannot plant on time if fertiliser is missing. Inventory control balances these risks.</p>

<h2>Stock Cards and Records</h2>
<p>A stock card is a simple record of every movement of an item. Each card shows the item name, unit of measure, location, receipts, issues, and balance. Whenever goods arrive, the balance increases. Whenever goods are issued, the balance decreases. A physical count should be done regularly to check that the card balance matches what is on the shelf.</p>

<h2>Reorder Level and Safety Stock</h2>
<p>The reorder level is the point at which a new order should be placed. It is usually calculated as the lead time demand plus safety stock. Lead time is how long it takes from placing an order to receiving goods. Safety stock is extra inventory kept to cover unexpected delays. For a school in a rural area that orders chalk from Lusaka with a two-week lead time, the reorder level might be enough chalk for three weeks of use.</p>

<h2>FIFO and Storage</h2>
<p><strong>First In First Out (FIFO)</strong> means the oldest stock is used before newer stock. This is especially important for items with expiry dates such as medicines, fertiliser, and food. Storage conditions also matter. Medicines need cool, dry conditions. Cement must be kept off damp floors. In Zambia, load-shedding can affect cold rooms, so organisations using refrigeration must have backup plans.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Design a stock card for one item used by a clinic or school.</li>
<li>Calculate a reorder level using realistic lead time and usage figures.</li>
<li>Explain why FIFO is important for medicines.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Inventory</strong>: Goods and materials held for use or sale.</li>
<li><strong>Stock card</strong>: A record of receipts, issues and balance for one item.</li>
<li><strong>Reorder level</strong>: The stock balance that triggers a new order.</li>
<li><strong>Safety stock</strong>: Extra inventory kept to protect against unexpected demand or delays.</li>
<li><strong>FIFO</strong>: First In First Out, an inventory issuing method.</li>
</ul>

<h2>Summary</h2>
<p>Inventory control ensures that organisations have enough stock without wasting money. Simple tools such as stock cards, reorder levels and FIFO keep operations running smoothly.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.cips.org/knowledge/procurement-topics/inventory-management/" target="_blank" rel="noopener">CIPS — Inventory Management</a></li>
<li><a href="https://www.khanacademy.org/economics-finance-domain/microeconomics/supply-demand-equilibrium" target="_blank" rel="noopener">Khan Academy — Supply and Demand Basics</a></li>
</ul>
HTML;
    }

    private function lesson4_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to plan the distribution of goods from a central store to multiple users, understand last-mile logistics challenges in Zambia, and apply FIFO when issuing stock.</p>

<h2>Distribution from Central Store to End Users</h2>
<p>Many organisations in Zambia receive goods at a central store and then distribute them to schools, clinics, farms or depots. The Ministry of Health, for example, may keep medicines in a central medical store in Lusaka and ship them to district hospitals and health posts. The Ministry of Education may send textbooks from a central warehouse to schools across the province.</p>
<p>Good distribution planning starts with a distribution schedule. The schedule shows what is going to each location, the quantity, the vehicle, the route, and the delivery date. It also records who receives the goods at the destination.</p>

<h2>Last-Mile Challenges</h2>
<p>The last mile is the final leg of delivery to the end user. In Zambia, last-mile delivery can be difficult because of poor road conditions, long distances, limited vehicles, fuel costs, and seasonal rains. A clinic in a remote area may receive supplies only once a month. A school off the main road may need a motorcycle or bicycle to carry books from the nearest town.</p>
<p>Load-shedding also affects distribution. Cold medicines and vaccines need refrigeration, and power cuts can spoil stock if backup generators or solar fridges are not available. Planning deliveries early in the day and using insulated boxes can reduce this risk.</p>

<h2>Issuing Stock Fairly</h2>
<p>When issuing stock, use FIFO and record every issue on a stock issue voucher. The voucher should be signed by both the storekeeper and the recipient. This prevents stock from disappearing and creates a clear paper trail. If one school receives more books than another for no clear reason, the community may lose trust in the distribution system.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Draw a simple distribution plan for delivering ten boxes of medical supplies to three rural clinics.</li>
<li>List three last-mile challenges that could delay your plan.</li>
<li>Write a stock issue voucher for one box of gloves issued to a clinic.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Distribution</strong>: Moving goods from a central point to end users.</li>
<li><strong>Last-mile logistics</strong>: The final delivery stage to the end user.</li>
<li><strong>Distribution schedule</strong>: A plan showing what goes where, when and how.</li>
<li><strong>Stock issue voucher</strong>: A document recording goods issued from a store.</li>
</ul>

<h2>Summary</h2>
<p>Effective distribution gets the right goods to the right place at the right time. Planning, documentation and awareness of local transport challenges are essential for farms, schools and clinics.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.cips.org/knowledge/procurement-topics/logistics/" target="_blank" rel="noopener">CIPS — Logistics</a></li>
<li><a href="https://www.who.int/teams/health-product-policy-and-standards/access-to-medicines-and-health-products" target="_blank" rel="noopener">WHO — Access to Medicines and Health Products</a></li>
</ul>
HTML;
    }

    // =========================================================================
    // MODULE 5
    // =========================================================================

    private function module5Lessons(): array
    {
        return [
            [
                'title' => 'Lesson 5.1: Transport, Ports and Cross-Border Buying',
                'duration_minutes' => 75,
                'content' => $this->lesson5_1(),
            ],
            [
                'title' => 'Lesson 5.2: Managing Currency and Cost Risks',
                'duration_minutes' => 60,
                'content' => $this->lesson5_2(),
            ],
            [
                'title' => 'Lesson 5.3: Writing Procurement Reports and Continuous Improvement',
                'duration_minutes' => 60,
                'content' => $this->lesson5_3(),
            ],
            [
                'title' => 'Module 5 Quiz: Logistics, Cost Control and Reporting',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your understanding of transport, currency risk, cost control and procurement reporting. You need 60% to pass.</p>',
            ],
        ];
    }

    private function module5Quiz(): array
    {
        return [
            'title' => 'Module 5 Quiz: Logistics, Cost Control and Reporting',
            'description' => 'Assess your understanding of transport modes, currency risk, cost control and procurement reporting.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which two ports are commonly used to import goods into landlocked Zambia?',
                    'explanation' => 'Zambia is landlocked, so most sea freight enters through Dar es Salaam in Tanzania or Beira in Mozambique.',
                    'options' => [
                        ['text' => 'Durban and Lagos', 'is_correct' => false],
                        ['text' => 'Dar es Salaam and Beira', 'is_correct' => true],
                        ['text' => 'Mombasa and Luanda', 'is_correct' => false],
                        ['text' => 'Walvis Bay and Maputo only', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main risk when a Zambian buyer agrees to pay a South African supplier in US Dollars?',
                    'explanation' => 'If the Zambian Kwacha weakens against the US Dollar before payment, the buyer needs more Kwacha to settle the invoice.',
                    'options' => [
                        ['text' => 'The supplier may refuse ZMW', 'is_correct' => false],
                        ['text' => 'Exchange-rate movement can increase the real cost', 'is_correct' => true],
                        ['text' => 'The goods will always arrive early', 'is_correct' => false],
                        ['text' => 'Local transport becomes free', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which transport mode is usually cheapest for moving large volumes over long distances?',
                    'explanation' => 'Sea freight is generally the cheapest per tonne for large international shipments, although it is slower than air.',
                    'options' => [
                        ['text' => 'Air freight', 'is_correct' => false],
                        ['text' => 'Road freight alone from overseas', 'is_correct' => false],
                        ['text' => 'Sea freight', 'is_correct' => true],
                        ['text' => 'Motorcycle courier', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which document is used to record goods being returned to a supplier?',
                    'explanation' => 'A Goods Return Note documents items sent back to a supplier because they were wrong, damaged or poor quality.',
                    'options' => [
                        ['text' => 'Purchase requisition', 'is_correct' => false],
                        ['text' => 'Goods Return Note', 'is_correct' => true],
                        ['text' => 'Invoice', 'is_correct' => false],
                        ['text' => 'Bank statement', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A procurement report should only include good news to keep management happy.',
                    'explanation' => 'A good procurement report includes both achievements and problems so managers can make informed decisions.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What does FIFO stand for in stores management?',
                    'explanation' => 'FIFO stands for First In First Out, ensuring older stock is issued before newer stock.',
                    'correct_answer' => 'First In First Out',
                ],
            ],
        ];
    }

    private function lesson5_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to compare transport modes used in Zambia, understand the role of Dar es Salaam and Beira ports, and identify the documents needed for cross-border buying.</p>

<h2>Moving Goods into Zambia</h2>
<p>Zambia has no coastline, so almost every imported item arrives by road or rail from a neighbouring port. The two main routes are from Dar es Salaam in Tanzania and Beira in Mozambique. Some goods also come through Durban in South Africa or Walvis Bay in Namibia. Each route has different costs, times, road conditions and border procedures.</p>
<p>Road transport is flexible and widely used. Trucks can collect containers at the port and deliver directly to a warehouse in Lusaka, Kitwe or Ndola. Rail transport is cheaper for bulk cargo such as fertiliser and fuel but is less flexible and can be slower. Air freight is fast and expensive, used mainly for urgent medicines, spare parts or high-value electronics.</p>

<h2>Key Documents for Cross-Border Buying</h2>
<p>When goods cross a border, several documents are required. These usually include a commercial invoice, packing list, bill of lading or airway bill, certificate of origin, import declaration, and customs entries. In Zambia, the Zambia Revenue Authority (ZRA) handles customs clearance. Many importers use a clearing agent to prepare documents and pay duties.</p>

<h2>Cost Factors on the Road</h2>
<p>A buyer who imports must include many costs beyond the supplier's price. These include port handling, freight, insurance, customs duty, excise duty, value-added tax, clearing agent fees, weighbridge fees, fuel levies, and inland transport to the final destination. Delays at the border also create costs, especially for perishable goods or urgent supplies.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Compare road, rail and air freight for importing one hundred bags of fertiliser.</li>
<li>List five documents needed for cross-border buying.</li>
<li>Estimate the extra costs that would be added to a USD 5,000 shipment arriving through Beira.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Freight</strong>: The cost of transporting goods.</li>
<li><strong>Bill of lading</strong>: A document issued by a carrier confirming receipt of goods for shipment.</li>
<li><strong>Customs duty</strong>: A tax charged on goods entering a country.</li>
<li><strong>Clearing agent</strong>: A professional who prepares customs documents and pays duties on behalf of an importer.</li>
<li><strong>Landlocked</strong>: A country without a coastline, relying on neighbours for sea access.</li>
</ul>

<h2>Summary</h2>
<p>Transport and cross-border buying add significant cost and complexity for Zambian organisations. Choosing the right route and preparing correct documents prevents delays, fines and unexpected expenses.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.zra.org.zm/" target="_blank" rel="noopener">Zambia Revenue Authority</a></li>
<li><a href="https://www.trade.gov/country-commercial-guides/zambia-import-requirements-and-documentation" target="_blank" rel="noopener">Trade.gov — Zambia Import Requirements</a></li>
</ul>
HTML;
    }

    private function lesson5_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will understand how exchange rates affect imported purchases, know how to manage price escalation, and be able to compare local and imported sourcing using total cost.</p>

<h2>Currency Risk for Zambian Buyers</h2>
<p>Many international suppliers quote prices in US Dollars, Euros or South African Rand. A Zambian buyer must convert these prices into Zambian Kwacha to know the real cost. If the Kwacha weakens before payment, the buyer pays more than planned. This is called foreign exchange risk or currency risk.</p>
<p>For example, a hospital orders medical equipment quoted at USD 10,000 when the exchange rate is K22 per dollar. The expected cost is K220,000. If the Kwacha weakens to K24 per dollar before payment, the cost rises to K240,000. That K20,000 difference could have bought other essential supplies.</p>

<h2>Managing Currency and Cost Risks</h2>
<p>Buyers can reduce currency risk in several ways. One option is to pay in Kwacha where the supplier accepts it. Another is to buy foreign currency forward or when the rate is favourable. A third is to include a price-review clause in the contract that limits increases. Buying locally also removes most currency risk, although it may mean higher unit prices for some items.</p>

<h2>Price Escalation</h2>
<p>Price escalation happens when the final cost is higher than the original quotation. Causes include exchange-rate movement, fuel price increases, new taxes, customs delays, and supplier mistakes. A professional buyer anticipates these risks and adds a contingency to the budget. Keeping some suppliers on a framework contract can also lock in prices for a period.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Convert a USD 2,500 invoice into Kwacha at two different exchange rates and show the difference.</li>
<li>List three ways to reduce currency risk when importing.</li>
<li>Explain why a local supplier may be cheaper in total cost even if the unit price is higher.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Currency risk</strong>: The risk that exchange-rate changes increase the cost of an imported item.</li>
<li><strong>Exchange rate</strong>: The value of one currency compared to another.</li>
<li><strong>Forward contract</strong>: An agreement to buy foreign currency at a set rate on a future date.</li>
<li><strong>Price escalation</strong>: An increase in final cost above the original quotation.</li>
<li><strong>Contingency</strong>: Extra budget kept to cover unexpected costs.</li>
</ul>

<h2>Summary</h2>
<p>Currency and cost risks are real challenges for Zambian buyers. Comparing total cost, managing exchange exposure, and planning for price escalation protect the organisation's budget.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.boz.zm/" target="_blank" rel="noopener">Bank of Zambia — Exchange Rates</a></li>
<li><a href="https://www.investopedia.com/terms/c/currencyrisk.asp" target="_blank" rel="noopener">Investopedia — Currency Risk</a></li>
</ul>
HTML;
    }

    private function lesson5_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to write a simple procurement report, measure procurement performance using key indicators, and suggest improvements that save money and improve service.</p>

<h2>The Purpose of a Procurement Report</h2>
<p>A procurement report tells management what the purchasing function has achieved, where money was spent, and what problems need attention. It supports accountability and helps leaders make decisions. A good report is short, factual and includes both numbers and explanations.</p>

<h2>What to Include</h2>
<p>A basic procurement report should include:</p>
<ul>
<li>Total spending during the period</li>
<li>Number of purchase orders raised</li>
<li>Number of suppliers used</li>
<li>Average delivery time</li>
<li>Cases of late delivery, poor quality or disputes</li>
<li>Savings achieved through negotiation or local buying</li>
<li>Key risks and recommendations</li>
</ul>

<h2>Key Performance Indicators</h2>
<p>Key performance indicators (KPIs) help measure how well procurement is working. Common KPIs include cost savings, purchase order cycle time, supplier on-time delivery rate, quality rejection rate, and number of emergency purchases. For example, if a clinic's on-time delivery rate is 60%, management knows that stock-outs are likely and that supplier performance needs attention.</p>

<h2>Continuous Improvement</h2>
<p>Good procurement teams review their work regularly and look for improvements. They ask questions such as: Can we buy more locally? Can we reduce emergency orders by improving forecasting? Can we negotiate better payment terms? Can we consolidate purchases to get volume discounts? Small improvements repeated every month lead to large savings over a year.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write a one-page procurement report for a school for one term.</li>
<li>Include at least five KPIs with realistic numbers.</li>
<li>List three recommendations for improvement.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Procurement report</strong>: A document summarising procurement activities, spending and performance.</li>
<li><strong>KPI</strong>: Key performance indicator, a measurable value that shows how well a process is performing.</li>
<li><strong>Cost savings</strong>: Money saved through better buying, negotiation or process improvement.</li>
<li><strong>Continuous improvement</strong>: The ongoing effort to make processes better.</li>
</ul>

<h2>Summary</h2>
<p>Procurement reports turn activity into insight. By tracking spending, delivery, quality and savings, organisations can improve purchasing practices and show that public or organisational money is being well managed.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.cips.org/knowledge/procurement-topics/procurement-performance/" target="_blank" rel="noopener">CIPS — Procurement Performance</a></li>
<li><a href="https://www.zppa.org.zm/" target="_blank" rel="noopener">ZPPA — Reporting Templates</a></li>
</ul>
HTML;
    }
}
