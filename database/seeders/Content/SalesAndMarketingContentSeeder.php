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

class SalesAndMarketingContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Sales & Marketing')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Sales & Marketing" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in Sales & Marketing already has modules. Skipping content seed.');
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
                'title' => 'Module 1: Understanding Sales and the Zambian Customer',
                'description' => 'Learn what selling really means, why Zambian customers buy, and how to identify the people most likely to purchase from you.',
            ],
            [
                'title' => 'Module 2: Building Product Knowledge and Pricing for Profit',
                'description' => 'Understand your product inside out, set prices that cover costs and attract buyers, and explain value in Kwacha terms customers trust.',
            ],
            [
                'title' => 'Module 3: Selling Face-to-Face and Over the Phone',
                'description' => 'Master greeting, questioning, handling objections, and closing a sale in person, at the market, or during a WhatsApp call.',
            ],
            [
                'title' => 'Module 4: Marketing with WhatsApp, Facebook, and Word of Mouth',
                'description' => 'Use free and low-cost tools to tell people about your business, plan simple promotions, and time them around Zambian buying cycles.',
            ],
            [
                'title' => 'Module 5: Keeping Customers Loyal and Growing the Business',
                'description' => 'Turn one-time buyers into repeat customers through service, follow-up, loyalty rewards, and asking for referrals.',
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
                'title' => 'Assignment 1: Create a Sales Pitch and Simple Marketing Plan',
                'description' => 'Develop a practical sales pitch and one-page marketing plan for a real or imaginary Zambian small business.',
                'instructions' => "<ol><li>Choose one Zambian small business (for example, a hair salon in Kalomo, a vegetable stall at Soweto Market, a phone accessories tuckshop, or a poultry farm near Choma).</li><li>Write a one-paragraph description of the product or service, the target customer, and the price in ZMW.</li><li>Create a 30-second sales pitch in writing. It must include a greeting, one question to understand the customer, a benefit, and a clear call to action.</li><li>Describe three ways you will market the business using free or low-cost methods (WhatsApp, Facebook, word of mouth, flyers, market demonstrations).</li><li>Explain how you will time at least one promotion around payday, a farming season, or a local event.</li><li>Save your work as a Word document or PDF and upload it here. Name the file: SalesPitch_YourName.</li></ol>",
                'due_weeks' => 2,
            ],
            [
                'title' => 'Assignment 2: Design a Customer-Retention Strategy Using WhatsApp and Loyalty Rewards',
                'description' => 'Build a practical customer-retention plan for a local shop that uses WhatsApp follow-up and a simple loyalty reward system.',
                'instructions' => "<ol><li>Choose the same business from Assignment 1 or a new one.</li><li>Design a simple loyalty reward customers can understand (for example, \"Buy 5 bags of mealie-meal, get 1 free\" or \"Get K5 off your next purchase after three visits\").</li><li>Write three WhatsApp follow-up message templates: one to thank a new customer, one to remind a customer to reorder, and one to announce a special offer.</li><li>Explain how you will record customer purchases without expensive software (notebook, phone contacts, WhatsApp labels, or a simple card).</li><li>Describe how you would ask a happy customer for a referral, and what small reward you would give for a successful referral.</li><li>Save everything in one Word document or PDF and upload it here. Name the file: Retention_YourName.</li></ol>",
                'due_weeks' => 4,
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
                'due_date' => now()->addWeeks($data['due_weeks']),
                'allow_late_submission' => 1,
                'late_penalty_percent' => 0,
                'max_file_size_mb' => 10,
                'allowed_file_types' => 'pdf,doc,docx,jpg,png',
            ]);
        }
    }

    // =========================================================================
    // MODULE 1
    // =========================================================================

    private function module1Lessons(): array
    {
        return [
            [
                'title' => 'Lesson 1.1: What Is Selling and Why People Buy',
                'duration_minutes' => 60,
                'content' => $this->lesson1_1(),
            ],
            [
                'title' => 'Lesson 1.2: Understanding the Zambian Buyer',
                'duration_minutes' => 75,
                'content' => $this->lesson1_2(),
            ],
            [
                'title' => 'Lesson 1.3: Finding Your Ideal Customer',
                'duration_minutes' => 60,
                'content' => $this->lesson1_3(),
            ],
            [
                'title' => 'Module 1 Quiz: Understanding Sales and the Zambian Customer',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Complete this quiz to check your understanding of selling fundamentals, Zambian buyer behaviour, and customer targeting. You need 60% to pass. Good luck!</p>',
            ],
        ];
    }

    private function lesson1_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to explain what selling really is, identify the main reasons people buy, and describe how a simple sales conversation moves a stranger from "just looking" to "I will take it."</p>

<figure><img class="lesson-diagram" src="/assets/diagrams/sales-marketing/customer-journey.svg" alt="Customer journey from awareness to advocacy for a Zambian small business"><figcaption>Figure: How a stranger becomes a loyal customer who recommends your business.</figcaption></figure>

<h2>What Is Selling?</h2>
<p>Selling is not tricking people into buying things they do not need. Selling is helping someone solve a problem or satisfy a desire in exchange for money. A good seller listens first, understands what the customer wants, and then shows how a product or service can deliver that result. When done well, both the customer and the seller walk away happy.</p>
<p>Think about the last time you bought something. Maybe it was a new pair of chitenge materials, a bag of mealie-meal, or a phone charger. You did not buy because the seller shouted the loudest. You bought because the item solved a problem for you: you needed food, you wanted to look smart, or your charger had stopped working. Selling starts with understanding that problem.</p>

<h2>Why Do People Buy?</h2>
<p>People buy for emotional reasons and justify the decision with logic. The six most common reasons are:</p>
<ul>
<li><strong>Need</strong>: They have a problem that must be solved now. A farmer needs seed before planting season. A parent needs school shoes in December.</li>
<li><strong>Want</strong>: They desire something that makes life nicer. A salon visit, a new hairstyle, or a cold Fanta on a hot afternoon.</li>
<li><strong>Fear</strong>: They want to avoid loss or danger. A family buys a gas stove because load-shedding makes electric cooking unreliable.</li>
<li><strong>Greed or gain</strong>: They believe the purchase will save or make money. A shopkeeper buys stock in bulk because the unit price is lower.</li>
<li><strong>Pride or status</strong>: They want to look good in front of others. A young professional buys a smart outfit for church or a job interview.</li>
<li><strong>Convenience</strong>: They want to save time or effort. A busy civil servant buys ready-cooked lunch instead of cooking at home.</li>
</ul>
<p>The best sellers figure out which reason is driving the customer and speak directly to it. If a mother is buying school shoes because she fears her child will be teased, talking about durability alone will miss the mark. You must also say, "These shoes look smart and will keep your child comfortable all term."</p>

<h2>The Sales Conversation in Four Steps</h2>
<p>Every sale follows a simple path, whether it happens at Soweto Market, in a WhatsApp chat, or over the phone.</p>
<ol>
<li><strong>Greet and build trust</strong>: Smile, greet the person by name if you know it, and show you are ready to help. A warm "Muli shani?" or "Good morning, how can I help you today?" opens the door.</li>
<li><strong>Ask questions</strong>: Find out what they need. "Are you looking for something for yourself or for the house?" "What size does your child wear?" "When do you need it by?"</li>
<li><strong>Present the solution</strong>: Show the product and explain the benefit. Do not just say "This is K120." Say "This blanket is warm, it is the right size for a double bed, and the colour will not fade quickly."</li>
<li><strong>Ask for the sale</strong>: Many sellers do all the work and then forget to close. Say "Shall I wrap it for you?" "Would you like to pay with MoMo or cash?" or "How many would you like?"</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Pick an item you sell or could sell.</li>
<li>Write down one problem it solves and one desire it satisfies.</li>
<li>Practice greeting a customer, asking two questions, presenting the benefit, and closing with one clear sentence.</li>
<li>Record yourself on your phone or practice with a friend.</li>
<li>Ask your practice partner which part felt most natural and which part felt forced.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Selling</strong>: Helping a customer solve a problem or satisfy a desire in exchange for payment.</li>
<li><strong>Customer need</strong>: A problem or requirement that drives a purchase decision.</li>
<li><strong>Benefit</strong>: The positive result a customer gets from using a product or service.</li>
<li><strong>Close the sale</strong>: The moment you ask the customer to make a decision or complete the purchase.</li>
<li><strong>Value</strong>: The balance between what the customer pays and what they receive.</li>
</ul>

<h2>Summary</h2>
<p>Selling is a service, not a battle. People buy to solve problems, satisfy desires, avoid fears, or gain convenience. A simple four-step conversation — greet, question, present, close — turns a stranger into a customer. Master these basics and every other sales skill becomes easier.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal/a/sal-talks-with-ayele-shakur-ceo-of-epic" target="_blank" rel="noopener">Khan Academy — Conversations with Entrepreneurs</a></li>
<li><a href="https://www.gutenberg.org/ebooks/1326" target="_blank" rel="noopener">Dale Carnegie — How to Win Friends and Influence People (public domain summary)</a></li>
<li><a href="https://www.zambiastatistics.gov.zm/" target="_blank" rel="noopener">Zambia Statistics Agency — Understanding Local Consumers</a></li>
</ul>
HTML;
    }

    private function lesson1_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will understand how Zambian customers make buying decisions, how factors like income cycles, seasonality, and trust affect those decisions, and how to adapt your sales approach to local realities.</p>

<h2>Zambian Buying Is Personal</h2>
<p>In Zambia, many purchases are made face-to-face and paid for in cash or mobile money. Customers often buy from people they know, people their friends know, or sellers who have built a reputation in the community. Trust matters more than a fancy website. A customer at Kalomo Central Market may walk past three vegetable stalls to buy from the fourth because "Bana Musonda always has fresh tomatoes and she does not overcharge."</p>
<p>This means your character is part of your brand. If you are honest, reliable, and friendly, word spreads. If you argue with customers, sell poor quality, or refuse to make things right when something goes wrong, word spreads even faster — and it can destroy your business.</p>

<h2>Income Cycles and Timing</h2>
<p>Many Zambian workers are paid monthly. Civil servants, teachers, nurses, and some private-sector employees receive salaries around the 28th of each month. The week after payday is usually the busiest time for shops, salons, and restaurants. Customers have cash in hand and are more willing to buy higher-priced items.</p>
<p>The third and fourth weeks of the month are quieter. This is when people buy only essentials and look for bargains. Smart sellers plan two types of offers: bigger promotions after payday when customers can spend, and smaller "stretch the budget" offers later in the month.</p>
<p>Farming seasons also shape buying. Before the rains, farmers spend heavily on seed, fertiliser, and labour. After harvest, they have income to spend on household goods, school fees, and celebrations. If you sell to farming communities, plan your marketing around these cycles.</p>

<h2>The Role of Mobile Money</h2>
<p>Mobile money has changed how Zambians pay. Even customers who do not have a bank account can send and receive money using MTN Mobile Money, Airtel Money, or Zamtel Kwacha. For a seller, accepting mobile money means fewer trips to the bank, less risk of carrying cash, and faster payment. For a customer, it means they can buy even when they do not have physical cash.</p>
<p>However, mobile money comes with costs. Agents charge fees, network problems can delay payments, and some customers fear sending money to a stranger. Always confirm payment before handing over goods, and keep a record of every transaction. A simple notebook with dates, names, amounts, and phone numbers can save you from disputes.</p>

<h2>Load-Shedding and Buying Behaviour</h2>
<p>When the power goes off, shopping patterns change. Customers buy more candles, charcoal, and foods that do not need refrigeration. They spend less time in shops with no lights and more time on their phones. A seller who has a solar lamp at their stall, a charged phone for MoMo payments, and a calm attitude during outages gains an advantage over competitors who close early or complain loudly.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Ask three local customers or shop owners when they usually have the most money during the month.</li>
<li>List three products or services that sell better after payday and three that sell better during load-shedding.</li>
<li>Write one sentence you could say to a customer to build trust quickly.</li>
<li>Identify one way mobile money could make buying easier for your customers.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Income cycle</strong>: The pattern of when customers receive and spend money.</li>
<li><strong>Mobile money</strong>: A payment service that uses a mobile phone account instead of a bank account.</li>
<li><strong>Trust</strong>: Confidence a customer has that you will deliver what you promise.</li>
<li><strong>Seasonality</strong>: Changes in buying behaviour caused by seasons, farming cycles, or holidays.</li>
<li><strong>Load-shedding</strong>: Scheduled power outages that affect business hours and customer behaviour.</li>
</ul>

<h2>Summary</h2>
<p>Zambian customers buy from people they trust, pay according to income cycles, and adapt quickly to conditions like load-shedding. When you understand these realities, you can time your sales efforts, choose your payment methods, and build the kind of reputation that keeps customers coming back.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.boz.zm/" target="_blank" rel="noopener">Bank of Zambia — Financial Literacy Materials</a></li>
<li><a href="https://www.zambiastatistics.gov.zm/" target="_blank" rel="noopener">Zambia Statistics Agency — Economic Updates</a></li>
<li><a href="https://www.mtn.zm/mobile-money" target="_blank" rel="noopener">MTN Zambia — Mobile Money Guide</a></li>
</ul>
HTML;
    }

    private function lesson1_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to describe your ideal customer in clear detail, explain why targeting matters more than trying to sell to everyone, and choose the right place and message to reach that customer.</p>

<h2>Why "Everyone" Is the Wrong Target</h2>
<p>If you try to sell to everyone, you end up selling to no one. A message that is too general gets ignored. Imagine a seller shouting "Nice things for sale!" at Soweto Market. No one stops because the message does not match anyone's need. Now imagine a seller saying "Fresh village chickens, K90 each, already cleaned — perfect for Sunday lunch." The right people stop immediately.</p>
<p>Your ideal customer is the person who needs your product, can afford it, and is easy for you to reach. The more specific you are, the easier it is to choose where to advertise, what to say, and how to price.</p>

<h2>Four Questions to Find Your Ideal Customer</h2>
<ol>
<li><strong>Where do they live or work?</strong> A shop near a school sells to parents and students. A shop near a hospital sells to staff, patients, and visitors.</li>
<li><strong>How old are they and what is their income?</strong> A teenager wants affordable fashion. A working mother wants quality and convenience.</li>
<li><strong>What problem are they trying to solve?</strong> A farmer needs reliable seed. A newly employed graduate needs professional clothing.</li>
<li><strong>Where do they already spend time?</strong> Are they on Facebook, WhatsApp, at church, at the market, or in specific offices?</li>
</ol>

<h2>A Worked Example: The Tuckshop</h2>
<p>Suppose you run a small tuckshop near a government compound in Kalomo. Your ideal customers might be:</p>
<ul>
<li><strong>Civil servants</strong> aged 25 to 50 who buy lunch, airtime, and small groceries on weekdays.</li>
<li><strong>School pupils</strong> who buy snacks and drinks during break time.</li>
<li><strong>Households</strong> in the area who need last-minute items like salt, sugar, or candles during load-shedding.</li>
</ul>
<p>Each group needs a different message. For civil servants: "Quick lunch packs ready by 12:30 — pay with MoMo or cash." For pupils: "Cold drinks and buns at break time." For households: "Open until 20:00 — candles, matches, and bread available."</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a business you know.</li>
<li>Answer the four questions above for that business.</li>
<li>Write a one-sentence description of the ideal customer.</li>
<li>Write three different messages, one for each customer group.</li>
<li>Show your work to someone who knows the business and ask if your descriptions feel accurate.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Ideal customer</strong>: The specific person most likely to buy from you.</li>
<li><strong>Target market</strong>: The group of customers you choose to focus your selling efforts on.</li>
<li><strong>Customer profile</strong>: A short description of your ideal customer including age, location, income, and need.</li>
<li><strong>Message</strong>: The words you use to attract a specific customer group.</li>
<li><strong>Channel</strong>: The place or method you use to reach customers, such as WhatsApp, a market stall, or Facebook.</li>
</ul>

<h2>Summary</h2>
<p>Finding your ideal customer saves time and money. Instead of shouting to everyone, you speak directly to the people who need what you sell. Use location, age, income, problem, and channel to build a clear customer profile, then craft messages that make them feel understood.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.score.org/resource/business-planning-financial-statements-template-gallery" target="_blank" rel="noopener">SCORE — Customer Profile Templates</a></li>
<li><a href="https://www.sba.gov/business-guide/plan-your-market-research" target="_blank" rel="noopener">U.S. Small Business Administration — Market Research</a></li>
<li><a href="https://www.zambiastatistics.gov.zm/" target="_blank" rel="noopener">Zambia Statistics Agency — Local Demographics</a></li>
</ul>
HTML;
    }

    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: Understanding Sales and the Zambian Customer',
            'description' => 'Test your understanding of selling fundamentals, Zambian buyer behaviour, and customer targeting.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following best describes selling?',
                    'explanation' => 'Selling is about helping customers solve problems or satisfy desires, not manipulating them.',
                    'options' => [
                        ['text' => 'Tricking people into buying things they do not need', 'is_correct' => false],
                        ['text' => 'Helping someone solve a problem or satisfy a desire in exchange for money', 'is_correct' => true],
                        ['text' => 'Shouting louder than competitors at the market', 'is_correct' => false],
                        ['text' => 'Giving away products for free to attract crowds', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why do many Zambian customers prefer to buy from sellers they know or who are recommended by friends?',
                    'explanation' => 'Trust and personal reputation play a major role in Zambian buying decisions.',
                    'options' => [
                        ['text' => 'Because they enjoy bargaining more with friends', 'is_correct' => false],
                        ['text' => 'Because they have no access to shops', 'is_correct' => false],
                        ['text' => 'Because trust and reputation reduce the risk of being cheated', 'is_correct' => true],
                        ['text' => 'Because unknown sellers always charge higher prices', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'When is the best time to promote higher-priced products to civil servants and formal workers in Zambia?',
                    'explanation' => 'Many formal workers are paid around the 28th, so the week after payday is when they have the most spending power.',
                    'options' => [
                        ['text' => 'The third week of the month', 'is_correct' => false],
                        ['text' => 'The week after payday (around the 29th to 5th)', 'is_correct' => true],
                        ['text' => 'Only during public holidays', 'is_correct' => false],
                        ['text' => 'Early in the morning every day', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a reason people buy?',
                    'explanation' => 'People buy for many reasons including need, want, fear, gain, pride, and convenience.',
                    'options' => [
                        ['text' => 'Only because the product is cheap', 'is_correct' => false],
                        ['text' => 'Need, want, fear, gain, pride, or convenience', 'is_correct' => true],
                        ['text' => 'Only because of television advertising', 'is_correct' => false],
                        ['text' => 'Only because the seller is related to them', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the final step of the four-step sales conversation?',
                    'explanation' => 'Closing means asking the customer to make a decision or complete the purchase.',
                    'options' => [
                        ['text' => 'Greet the customer warmly', 'is_correct' => false],
                        ['text' => 'Ask questions about their needs', 'is_correct' => false],
                        ['text' => 'Present the product and its benefits', 'is_correct' => false],
                        ['text' => 'Ask for the sale', 'is_correct' => true],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Trying to sell to everyone is usually more effective than targeting a specific customer group.',
                    'explanation' => 'A general message often gets ignored. Targeting a specific group allows you to craft a message that speaks directly to their needs.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Accepting mobile money can help a seller make sales even when the customer does not have physical cash.',
                    'explanation' => 'Mobile money allows customers to pay using their phone, increasing convenience and sales opportunities.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for the balance between what a customer pays and what they receive? (One word)',
                    'explanation' => 'Value is the customer\'s perception of whether the price is fair compared to the benefit received.',
                    'correct_answer' => 'Value',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the name of the pattern of when customers receive and spend money? (Two words)',
                    'explanation' => 'Income cycle describes how customers\' available money changes over time, such as monthly salaries.',
                    'correct_answer' => 'Income cycle',
                ],
            ],
        ];
    }

    // =========================================================================
    // MODULE 2
    // =========================================================================

    private function module2Lessons(): array
    {
        return [
            [
                'title' => 'Lesson 2.1: Knowing Your Product Inside Out',
                'duration_minutes' => 60,
                'content' => $this->lesson2_1(),
            ],
            [
                'title' => 'Lesson 2.2: Pricing for Profit in Kwacha',
                'duration_minutes' => 75,
                'content' => $this->lesson2_2(),
            ],
            [
                'title' => 'Lesson 2.3: Features, Benefits, and the Value Story',
                'duration_minutes' => 60,
                'content' => $this->lesson2_3(),
            ],
            [
                'title' => 'Module 2 Quiz: Product Knowledge and Pricing',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your understanding of product knowledge, pricing, costs, and communicating value to Zambian customers. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson2_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will know why deep product knowledge builds customer confidence, what facts you should learn about every product you sell, and how to answer common questions without guessing.</p>

<h2>Why Product Knowledge Matters</h2>
<p>Customers can smell uncertainty. If you hesitate when asked "How long does this battery last?" or "Is this fertiliser good for maize?" they lose trust. A seller who knows the product can answer quickly, recommend the right option, and handle objections before they become problems.</p>
<p>Imagine you sell phone chargers at a tuckshop in Choma. A customer asks, "Will this charger work with my Itel phone?" If you know your stock, you say, "Yes, this USB charger works with any phone that uses a micro-USB cable, including Itel." If you do not know, you waste the customer's time and may lose the sale. Worse, you might sell the wrong item and create a return.</p>

<h2>What You Should Know About Every Product</h2>
<ul>
<li><strong>Name and model</strong>: What is the product called? Is there a size, colour, or model number?</li>
<li><strong>Price</strong>: How much does it cost you, and how much do you sell it for?</li>
<li><strong>Uses</strong>: What problem does it solve? How is it used?</li>
<li><strong>Benefits</strong>: What result does the customer get? Save time? Save money? Look better? Stay healthy?</li>
<li><strong>Limitations</strong>: What does it NOT do? Being honest about limits builds trust.</li>
<li><strong>Quality proof</strong>: Is it original, locally made, durable, or guaranteed? Can you show a sample or testimonial?</li>
<li><strong>Stock level</strong>: Do you have it now, or must the customer wait?</li>
</ul>

<h2>Practical Example: Selling Fertiliser to a Farmer</h2>
<p>A farmer walks into your agro-shop and asks about fertiliser. A weak seller says, "This one is K450 per bag." A knowledgeable seller says, "This basal dressing is K450 for a 50kg bag. It is formulated for maize and works well in our Southern Province soils. One bag covers about 0.25 hectares. We also have top dressing if you are at the tasselling stage. Would you like me to explain the difference?"</p>
<p>The second seller answers the real question behind the price: "Will this work for my farm?" That builds trust and often leads to a larger sale.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Pick three products you sell or could sell.</li>
<li>Write down the name, price, main use, main benefit, and one limitation for each.</li>
<li>Practice answering these customer questions out loud: "How much?" "Why should I buy this one?" "Will it last?" "What if it does not work?"</li>
<li>Ask a friend to play the customer and give you feedback.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Product knowledge</strong>: The detailed understanding a seller has about what they sell.</li>
<li><strong>Feature</strong>: A fact about the product, such as size, colour, or material.</li>
<li><strong>Benefit</strong>: The positive result the customer experiences from a feature.</li>
<li><strong>Objection</strong>: A concern or reason a customer gives for not buying.</li>
<li><strong>Stock level</strong>: The quantity of a product currently available to sell.</li>
</ul>

<h2>Summary</h2>
<p>Know your product better than your customer does. Learn the name, price, uses, benefits, limits, quality proof, and stock level. When you can answer questions confidently and honestly, customers trust you more and buy more.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.sba.gov/business-guide/manage-your-business/manage-your-inventory" target="_blank" rel="noopener">U.S. Small Business Administration — Inventory Management</a></li>
<li><a href="https://www.score.org/resource/article/5-ways-improve-your-product-knowledge" target="_blank" rel="noopener">SCORE — Improving Product Knowledge</a></li>
<li><a href="https://www.zambiafarmershub.org/" target="_blank" rel="noopener">Zambia Farmers Hub — Agricultural Inputs</a></li>
</ul>
HTML;
    }

    private function lesson2_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to calculate the true cost of a product, set a selling price that makes a profit, and explain your price to customers in a way that focuses on value rather than apologising.</p>

<h2>The Real Cost Is More Than the Purchase Price</h2>
<p>Many small-business owners make a dangerous mistake: they price based on what the neighbour charges, without knowing their own true cost. Your true cost includes the price you paid for the item, transport to bring it to your shop, packaging, any losses from damage or theft, and sometimes the time you spent sourcing it.</p>
<p>Let us work through an example. You buy ten plastic basins at K25 each from a wholesaler in Lusaka. Transport costs K100 for the trip. That means the total cost is (10 × K25) + K100 = K350. Divided by ten basins, each basin really costs you K35. If you sell each one at K40, your profit is K5 per basin, or about 14% profit margin. If you sell at K38, you are losing money without realising it.</p>

<h2>Three Simple Pricing Methods</h2>
<ol>
<li><strong>Cost-plus pricing</strong>: Add a percentage to your true cost. If your cost is K35 and you want a 30% margin, your selling price is K35 + (K35 × 0.30) = K45.50. This is simple and ensures you never sell at a loss.</li>
<li><strong>Competitor pricing</strong>: Check what others charge and price near them. Use this carefully — if their costs are lower than yours, matching their price may hurt you.</li>
<li><strong>Value-based pricing</strong>: Price based on what the customer is willing to pay because of convenience, quality, or urgency. A cold drink at a bus station on a hot day can sell for more than the same drink in a supermarket because the customer values convenience.</li>
</ol>

<h2>How to Talk About Price Without Apologising</h2>
<p>Never say "Sorry, it is expensive." Instead, explain the value. Try these phrases:</p>
<ul>
<li>"This bag is K90 because it is 50kg and the maize is this year's crop — it will not have weevils."</li>
<li>"The price includes delivery within Kalomo, so you save transport money."</li>
<li>"This one costs more than the others, but it lasts longer, so you replace it less often."</li>
</ul>
<p>When customers understand why the price is fair, they complain less and buy more confidently.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose one product you sell or could sell.</li>
<li>Write down every cost involved: purchase price, transport, packaging, and any other expenses.</li>
<li>Calculate your true cost per unit.</li>
<li>Choose a profit margin and calculate your selling price.</li>
<li>Write one sentence explaining the value behind your price.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>True cost</strong>: The full cost of bringing a product to sale, including purchase price, transport, and other expenses.</li>
<li><strong>Profit</strong>: The money left after subtracting all costs from the selling price.</li>
<li><strong>Profit margin</strong>: Profit expressed as a percentage of the selling price or cost price.</li>
<li><strong>Cost-plus pricing</strong>: Setting price by adding a fixed percentage to the product cost.</li>
<li><strong>Value-based pricing</strong>: Setting price based on how much the customer values the benefit.</li>
</ul>

<h2>Summary</h2>
<p>Good pricing starts with knowing your true cost. Add a reasonable profit, check the market, and then explain your price in terms of value. A confident, honest price conversation turns price objections into trust.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.sba.gov/business-guide/plan-your-business/calculate-your-startup-costs" target="_blank" rel="noopener">U.S. Small Business Administration — Calculating Costs</a></li>
<li><a href="https://www.score.org/resource/article/pricing-strategies-small-business" target="_blank" rel="noopener">SCORE — Pricing Strategies for Small Business</a></li>
<li><a href="https://www.zra.org.zm/" target="_blank" rel="noopener">Zambia Revenue Authority — Business Tax Basics</a></li>
</ul>
HTML;
    }

    private function lesson2_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to turn boring product facts into compelling customer benefits, tell a short value story, and explain why your product is worth the price in Kwacha.</p>

<h2>Features Tell, Benefits Sell</h2>
<p>A feature is a fact. A benefit is what that fact does for the customer. Customers do not buy features; they buy benefits. If you sell a solar lamp, the feature is "6-hour battery life." The benefit is "Your children can study after load-shedding without buying candles every week." The benefit connects the product to the customer's life.</p>
<p>Here are more examples:</p>
<table>
<tr><th>Product</th><th>Feature</th><th>Benefit</th></tr>
<tr><td>Rice</td><td>5kg bag</td><td>Enough to feed a family of five for a week</td></tr>
<tr><td>Phone case</td><td>Shockproof</td><td>Protects your phone if it drops on the concrete</td></tr>
<tr><td>Hair braiding</td><td>Lasts three weeks</td><td>Saves you salon trips and money this month</td></tr>
<tr><td>Fertiliser</td><td>High nitrogen</td><td>Gives your maize the green leaves needed for a better harvest</td></tr>
</table>

<h2>Building the Value Story</h2>
<p>A value story connects the customer's problem to your product and the result they will enjoy. It has three parts:</p>
<ol>
<li><strong>The problem</strong>: "Load-shedding means my shop closes early and I lose sales."</li>
<li><strong>The solution</strong>: "This solar lamp gives bright light for six hours after sunset."</li>
<li><strong>The result</strong>: "I can keep my shop open until 21:00 and serve more customers."</li>
</ol>
<p>When you tell a value story, the customer imagines themselves using the product. That imagination is what creates desire.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose three features of a product you sell.</li>
<li>Turn each feature into a customer benefit starting with "so you can..." or "which means..."</li>
<li>Write a three-sentence value story for one product.</li>
<li>Practice saying the value story out loud until it feels natural.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Feature</strong>: A factual characteristic of a product.</li>
<li><strong>Benefit</strong>: The advantage or positive result a customer gets from a feature.</li>
<li><strong>Value story</strong>: A short narrative that connects a customer problem to a product solution and a desired result.</li>
<li><strong>Desire</strong>: The feeling of wanting a product because it promises a better situation.</li>
</ul>

<h2>Summary</h2>
<p>Features describe the product; benefits describe the customer's life after buying it. Use a simple value story — problem, solution, result — to make your product feel relevant and worth the price. When customers can imagine the benefit, they are much closer to buying.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.copyblogger.com/features-vs-benefits/" target="_blank" rel="noopener">Copyblogger — Features vs. Benefits</a></li>
<li><a href="https://www.canva.com/designschool/" target="_blank" rel="noopener">Canva Design School — Visual Storytelling</a></li>
<li><a href="https://www.score.org/resource/article/how-sell-value-not-price" target="_blank" rel="noopener">SCORE — How to Sell Value, Not Price</a></li>
</ul>
HTML;
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Product Knowledge and Pricing',
            'description' => 'Assess your understanding of product knowledge, pricing methods, and communicating value.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a benefit, not a feature?',
                    'explanation' => 'A benefit explains the positive result for the customer, while a feature is just a fact.',
                    'options' => [
                        ['text' => 'The dress is made of cotton', 'is_correct' => false],
                        ['text' => 'The phone has a 5000mAh battery', 'is_correct' => false],
                        ['text' => 'The solar lamp lets children study after load-shedding', 'is_correct' => true],
                        ['text' => 'The fertiliser comes in a 50kg bag', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'A seller buys 10 basins at K25 each and spends K100 on transport. What is the true cost per basin?',
                    'explanation' => 'Total cost is (10 × K25) + K100 = K350. Divided by 10 basins, the true cost is K35 each.',
                    'options' => [
                        ['text' => 'K25', 'is_correct' => false],
                        ['text' => 'K30', 'is_correct' => false],
                        ['text' => 'K35', 'is_correct' => true],
                        ['text' => 'K40', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which pricing method adds a percentage to the product cost to ensure profit?',
                    'explanation' => 'Cost-plus pricing adds a markup to the cost to determine the selling price.',
                    'options' => [
                        ['text' => 'Competitor pricing', 'is_correct' => false],
                        ['text' => 'Value-based pricing', 'is_correct' => false],
                        ['text' => 'Cost-plus pricing', 'is_correct' => true],
                        ['text' => 'Loss-leader pricing', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why should a seller avoid saying "Sorry, it is expensive" when a customer questions the price?',
                    'explanation' => 'Apologising weakens the seller\'s position. It is better to explain the value behind the price.',
                    'options' => [
                        ['text' => 'Because customers enjoy hearing sellers apologise', 'is_correct' => false],
                        ['text' => 'Because it makes the product sound low quality', 'is_correct' => false],
                        ['text' => 'Because it weakens confidence in the value of the product', 'is_correct' => true],
                        ['text' => 'Because it is rude to customers', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What are the three parts of a value story?',
                    'explanation' => 'A value story connects the problem, the solution, and the result.',
                    'options' => [
                        ['text' => 'Price, product, place', 'is_correct' => false],
                        ['text' => 'Problem, solution, result', 'is_correct' => true],
                        ['text' => 'Need, want, demand', 'is_correct' => false],
                        ['text' => 'Cost, margin, profit', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A seller should know the limitations of a product as well as its benefits.',
                    'explanation' => 'Honesty about limitations builds trust and reduces returns or complaints.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'The best way to set prices is to copy exactly what your neighbour charges.',
                    'explanation' => 'Competitor pricing can be useful, but your costs may differ, so copying blindly can lead to losses.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for a factual characteristic of a product, such as size or colour? (One word)',
                    'explanation' => 'A feature is a fact about the product, while a benefit explains what it does for the customer.',
                    'correct_answer' => 'Feature',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the money left after subtracting all costs from the selling price? (One word)',
                    'explanation' => 'Profit is what remains after all costs have been deducted from the selling price.',
                    'correct_answer' => 'Profit',
                ],
            ],
        ];
    }

    // =========================================================================
    // MODULE 3
    // =========================================================================

    private function module3Lessons(): array
    {
        return [
            [
                'title' => 'Lesson 3.1: Greeting, Questioning, and Listening',
                'duration_minutes' => 60,
                'content' => $this->lesson3_1(),
            ],
            [
                'title' => 'Lesson 3.2: Handling Objections Without Arguing',
                'duration_minutes' => 75,
                'content' => $this->lesson3_2(),
            ],
            [
                'title' => 'Lesson 3.3: Closing the Sale and Following Up',
                'duration_minutes' => 60,
                'content' => $this->lesson3_3(),
            ],
            [
                'title' => 'Module 3 Quiz: Selling Face-to-Face and Over the Phone',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your understanding of greeting customers, asking questions, handling objections, and closing sales. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson3_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to greet customers warmly, ask the right questions to uncover their needs, and listen actively so the customer feels understood.</p>

<h2>First Impressions Happen in Seconds</h2>
<p>A customer decides whether to trust you within the first few seconds of meeting you. Your greeting sets the tone for the whole conversation. A cold or distracted seller makes the customer feel like an interruption. A warm, attentive seller makes the customer feel welcome.</p>
<p>In Zambia, a friendly greeting in the local language can break the ice immediately. "Muli shani?" "Ulipo?" or a simple "Good morning, how can I help you today?" shows respect and openness. Make eye contact, smile genuinely, and put down your phone. Customers notice when you are not present.</p>

<h2>The Power of Questions</h2>
<p>Many sellers talk too much and ask too little. They jump straight to "This is K50" without understanding what the customer actually wants. Questions help you discover the customer's situation, budget, and urgency.</p>
<p>Use three types of questions:</p>
<ul>
<li><strong>Open questions</strong>: These invite the customer to talk. "What are you looking for today?" "Tell me about the occasion."</li>
<li><strong>Clarifying questions</strong>: These check your understanding. "So you need something for a child starting Grade 1?" "Is this for daily use or for a special event?"</li>
<li><strong>Closing questions</strong>: These move toward a decision. "Would you like the blue one or the black one?" "Shall I pack it for you?"</li>
</ul>

<h2>Listen More Than You Talk</h2>
<p>Active listening means paying full attention, nodding, and repeating back what you heard. When a customer says, "I need shoes that will last the whole school term," respond with, "So durability is the most important thing for you. Let me show you our strongest pair." This shows you were listening and moves the conversation forward.</p>
<p>Avoid interrupting. Avoid looking at other customers while someone is talking to you. Avoid jumping to conclusions. The best sellers are often the best listeners.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write two open questions, two clarifying questions, and two closing questions for a product you sell.</li>
<li>Practice a full greeting and questioning conversation with a friend playing the customer.</li>
<li>Record the conversation and count how many seconds you talk versus how many seconds the customer talks. Aim for the customer to talk at least half the time.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Open question</strong>: A question that cannot be answered with a simple yes or no.</li>
<li><strong>Clarifying question</strong>: A question that confirms your understanding of what the customer said.</li>
<li><strong>Closing question</strong>: A question that guides the customer toward a purchase decision.</li>
<li><strong>Active listening</strong>: Paying close attention, showing you understand, and responding thoughtfully.</li>
</ul>

<h2>Summary</h2>
<p>A strong sale starts with a warm greeting, continues with good questions, and succeeds through active listening. When customers feel understood, they trust you and are more willing to buy.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.mindtools.com/page6.html" target="_blank" rel="noopener">MindTools — Active Listening</a></li>
<li><a href="https://www.dalecarnegie.com/en" target="_blank" rel="noopener">Dale Carnegie — Interpersonal Skills</a></li>
<li><a href="https://www.salesforce.com/resources/articles/sales-skills/" target="_blank" rel="noopener">Salesforce — Essential Sales Skills</a></li>
</ul>
HTML;
    }

    private function lesson3_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to recognise common customer objections, respond to them calmly, and turn resistance into a reason to buy.</p>

<h2>Objections Are Not Rejection</h2>
<p>When a customer says "It is too expensive" or "Let me think about it," many sellers feel rejected and give up. In reality, an objection is often a request for more information. The customer is interested enough to explain why they are hesitating. Your job is to answer the concern and guide them forward.</p>
<p>The most common objections in Zambia are about price, quality, trust, timing, and authority. "I need to ask my husband." "I do not have the money right now." "I have never heard of this brand." "Can I get it cheaper at Soweto Market?" Each objection has a respectful response.</p>

<h2>How to Handle Price Objections</h2>
<p>Never argue about price. Instead, return to value. If a customer says "It is too expensive," you might reply:</p>
<ul>
<li>"I understand. Many customers felt that way until they saw how long it lasts. Can I show you the difference?"</li>
<li>"This one costs more upfront, but it saves you money because you will not need to replace it in three months."</li>
<li>"We also have a smaller size at K30 if the budget is tight this week."</li>
</ul>
<p>Offering a cheaper alternative keeps the conversation alive without making the customer feel pressured.</p>

<h2>How to Handle "I Need to Think About It"</h2>
<p>This usually means the customer is not yet convinced. Ask what specifically they need to think about. "Is it the price, the size, or something else?" Once they name the concern, address it. If they really need time, agree on a follow-up: "No problem. I will save this one for you until Friday. Can I send you a quick WhatsApp reminder?"</p>

<h2>How to Handle Trust Objections</h2>
<p>If a customer says "I do not know if this will work," offer proof. Show testimonials, offer a guarantee, or give a small trial. "This cream has worked for many customers. Here is a photo of results after two weeks." "If the charger does not work on your phone, bring it back within three days and I will replace it." Proof reduces fear.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write down the three most common objections you hear or expect to hear.</li>
<li>Write a calm, respectful response to each one.</li>
<li>Practice saying the responses out loud until they sound natural.</li>
<li>Role-play with a friend who raises each objection.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Objection</strong>: A concern or reason a customer gives for not buying immediately.</li>
<li><strong>Price objection</strong>: A concern that the product costs too much.</li>
<li><strong>Trust objection</strong>: A concern about whether the product or seller is reliable.</li>
<li><strong>Reframe</strong>: To present the same information in a new way that changes the customer's view.</li>
</ul>

<h2>Summary</h2>
<p>Objections are a normal part of selling. Handle them with calm, respect, and useful information. When you address the real concern behind the objection, customers often move from "maybe" to "yes."</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.hubspot.com/sales/handling-objections" target="_blank" rel="noopener">HubSpot — How to Handle Sales Objections</a></li>
<li><a href="https://www.salesforce.com/resources/articles/sales-skills/" target="_blank" rel="noopener">Salesforce — Sales Skills</a></li>
<li><a href="https://blog.hubspot.com/sales/sales-objection-handling" target="_blank" rel="noopener">HubSpot Blog — Objection Handling Scripts</a></li>
</ul>
HTML;
    }

    private function lesson3_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to ask for the sale confidently, handle "no" without giving up, and follow up with customers in a way that builds long-term relationships.</p>

<h2>Asking for the Sale</h2>
<p>The close is the moment of truth. Many sellers never reach it because they are afraid of hearing "no." But a customer cannot say yes if you never ask. The close should feel like the natural next step in the conversation, not a sudden pressure tactic.</p>
<p>Here are simple closing phrases:</p>
<ul>
<li>"Shall I wrap this for you?"</li>
<li>"Would you like to pay with cash or MoMo?"</li>
<li>"How many would you like?"</li>
<li>"Shall I reserve one for you until Saturday?"</li>
</ul>
<p>These questions assume the customer wants to buy and simply ask for the details. They feel helpful rather than pushy.</p>

<h2>Handling "No"</h2>
<p>If the customer says no, stay polite. Do not argue or show frustration. Ask why, if appropriate: "Is there something I can explain better?" Sometimes the answer gives you information for next time. Thank them for considering you and leave the door open: "No problem at all. Please feel free to come back if you change your mind. My name is [your name]."</p>
<p>A "no" today is not a "no" forever. Many customers return later if they were treated with respect.</p>

<h2>Following Up Without Being Annoying</h2>
<p>Follow-up turns one-time shoppers into repeat customers. After a sale, send a short WhatsApp message: "Thank you for buying from us today. We hope the [product] serves you well. Let us know if you need anything else." After a few days, check in: "How is the [product] working?"</p>
<p>If a customer said they would come back but did not, send one polite reminder: "Hi [name], you asked me to remind you about the [product]. It is still available. Would you like me to set one aside for you?" One reminder is helpful. Five reminders are annoying.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write three closing questions for your product.</li>
<li>Practice saying them out loud with confidence.</li>
<li>Write a WhatsApp follow-up message you could send after a sale.</li>
<li>Role-play a sale where the customer says no, and practice responding politely.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Close the sale</strong>: To ask the customer to make the purchase decision.</li>
<li><strong>Assumptive close</strong>: A closing technique that assumes the customer wants to buy and asks for details.</li>
<li><strong>Follow-up</strong>: Contacting a customer after a sale or conversation to build the relationship.</li>
<li><strong>Referral</strong>: A new customer who comes because an existing customer recommended you.</li>
</ul>

<h2>Summary</h2>
<p>Closing is not a trick; it is a natural question at the end of a helpful conversation. Handle rejection with grace, follow up with care, and every customer becomes an opportunity for repeat business or a referral.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.hubspot.com/sales/closing-techniques" target="_blank" rel="noopener">HubSpot — Sales Closing Techniques</a></li>
<li><a href="https://www.salesforce.com/resources/articles/sales-skills/" target="_blank" rel="noopener">Salesforce — Sales Skills</a></li>
<li><a href="https://www.dalecarnegie.com/en" target="_blank" rel="noopener">Dale Carnegie — Professional Selling</a></li>
</ul>
HTML;
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Selling Face-to-Face and Over the Phone',
            'description' => 'Assess your understanding of greeting customers, handling objections, and closing sales.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main purpose of asking open questions?',
                    'explanation' => 'Open questions encourage the customer to share information about their needs and situation.',
                    'options' => [
                        ['text' => 'To prove you know more than the customer', 'is_correct' => false],
                        ['text' => 'To invite the customer to talk about their needs', 'is_correct' => true],
                        ['text' => 'To quickly end the conversation', 'is_correct' => false],
                        ['text' => 'To confuse the customer', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'How should a seller respond when a customer says "It is too expensive"?',
                    'explanation' => 'The best response returns to value and may offer a smaller or cheaper alternative.',
                    'options' => [
                        ['text' => 'Argue that the price is fair', 'is_correct' => false],
                        ['text' => 'Ignore the comment and keep talking', 'is_correct' => false],
                        ['text' => 'Explain the value and offer a smaller option if needed', 'is_correct' => true],
                        ['text' => 'Raise the price to prove quality', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is an example of active listening?',
                    'explanation' => 'Repeating back what you heard shows you were paying attention and understand the customer.',
                    'options' => [
                        ['text' => 'Looking at your phone while the customer talks', 'is_correct' => false],
                        ['text' => 'Interrupting to give advice immediately', 'is_correct' => false],
                        ['text' => 'Repeating back what the customer said to confirm understanding', 'is_correct' => true],
                        ['text' => 'Changing the subject to a new product', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is an assumptive close?',
                    'explanation' => 'An assumptive close asks for details as if the customer has already decided to buy.',
                    'options' => [
                        ['text' => 'Asking the customer to leave the shop', 'is_correct' => false],
                        ['text' => 'Assuming the customer cannot afford the product', 'is_correct' => false],
                        ['text' => 'Asking for details such as payment method or quantity', 'is_correct' => true],
                        ['text' => 'Telling the customer they must buy today', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is follow-up important after a sale?',
                    'explanation' => 'Follow-up builds relationships, encourages repeat business, and can lead to referrals.',
                    'options' => [
                        ['text' => 'It annoys the customer until they block you', 'is_correct' => false],
                        ['text' => 'It is only important for expensive products', 'is_correct' => false],
                        ['text' => 'It turns one-time buyers into repeat customers', 'is_correct' => true],
                        ['text' => 'It replaces the need for a good product', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A customer who raises an objection is usually not interested in buying.',
                    'explanation' => 'An objection often shows interest; the customer is asking for more information before deciding.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Sending five follow-up reminders in one day is a good sales strategy.',
                    'explanation' => 'Too many reminders feel pushy and can annoy the customer. One polite reminder is usually enough.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for a new customer who comes because an existing customer recommended you? (One word)',
                    'explanation' => 'A referral is a customer gained through recommendation from an existing customer.',
                    'correct_answer' => 'Referral',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What type of question invites the customer to talk rather than answer yes or no? (Two words)',
                    'explanation' => 'Open questions encourage detailed responses and help uncover customer needs.',
                    'correct_answer' => 'Open question',
                ],
            ],
        ];
    }

    // =========================================================================
    // MODULE 4
    // =========================================================================

    private function module4Lessons(): array
    {
        return [
            [
                'title' => 'Lesson 4.1: Marketing Basics for Small Zambian Businesses',
                'duration_minutes' => 60,
                'content' => $this->lesson4_1(),
            ],
            [
                'title' => 'Lesson 4.2: Selling with WhatsApp and Facebook',
                'duration_minutes' => 75,
                'content' => $this->lesson4_2(),
            ],
            [
                'title' => 'Lesson 4.3: Word of Mouth, Flyers, and Local Promotions',
                'duration_minutes' => 60,
                'content' => $this->lesson4_3(),
            ],
            [
                'title' => 'Module 4 Quiz: Marketing with WhatsApp, Facebook, and Word of Mouth',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your understanding of marketing basics, WhatsApp and Facebook selling, and local promotion strategies. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson4_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will understand the difference between sales and marketing, know the four main parts of marketing, and be able to choose low-cost marketing tactics that work for a small Zambian business.</p>

<figure><img class="lesson-diagram" src="/assets/diagrams/sales-marketing/sales-funnel.svg" alt="Sales funnel showing how many people see a Zambian shop's advert, show interest, and finally buy"><figcaption>Figure: How a sales funnel works for a small Zambian shop.</figcaption></figure>

<h2>Sales vs Marketing</h2>
<p>Sales is the direct conversation that turns a person into a buyer. Marketing is everything you do to attract people to that conversation. If sales is asking someone to dance, marketing is the music, the lighting, and the invitation that makes them want to walk onto the dance floor.</p>
<p>For example, a hair salon posts photos of braided styles on WhatsApp Status. That is marketing. When a customer messages to book an appointment and the stylist explains the price and time, that is sales. Both are needed. Good marketing makes sales easier by bringing the right people to you.</p>

<h2>The Four Ps of Marketing</h2>
<p>Every marketing plan can be broken into four parts:</p>
<ul>
<li><strong>Product</strong>: What you sell and how it solves a problem. A clean, well-packaged product sells better than the same item thrown carelessly on a shelf.</li>
<li><strong>Price</strong>: What the customer pays. Price sends a message about quality. A price that is too low can make people suspicious; a price that is too high can drive them away.</li>
<li><strong>Place</strong>: Where customers find you. This could be your shop, your WhatsApp number, your Facebook page, or your stall at the market.</li>
<li><strong>Promotion</strong>: How you tell people about your product. This includes word of mouth, flyers, social media, signboards, and special offers.</li>
</ul>

<h2>Low-Cost Marketing Tactics</h2>
<p>You do not need a big budget to market effectively. Here are tactics that cost little or nothing:</p>
<ul>
<li><strong>WhatsApp Status</strong>: Post photos and prices daily. Free and reaches everyone who has saved your number.</li>
<li><strong>Facebook Page</strong>: Create a free business page and post twice a week.</li>
<li><strong>Word of mouth</strong>: Treat every customer so well that they tell others.</li>
<li><strong>Signboard or chalkboard</strong>: A clear sign at your stall attracts passers-by.</li>
<li><strong>Cross-promotion</strong>: Partner with a nearby business. A tailor can leave flyers at a fabric shop; a vegetable seller can recommend a spice seller.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Write down the four Ps for a business you know.</li>
<li>Choose three low-cost marketing tactics that would suit that business.</li>
<li>Write one marketing message using the four Ps.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Marketing</strong>: Activities that attract potential customers to your business.</li>
<li><strong>Sales</strong>: The direct process of converting a potential customer into a buyer.</li>
<li><strong>Four Ps</strong>: Product, price, place, and promotion — the main elements of a marketing plan.</li>
<li><strong>Promotion</strong>: Communication used to inform, persuade, or remind customers about your product.</li>
</ul>

<h2>Summary</h2>
<p>Marketing brings people to your sales conversation. The four Ps — product, price, place, promotion — give you a simple framework. With free tools like WhatsApp Status and word of mouth, even the smallest business can market itself effectively.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.score.org/resource/article/marketing-basics-small-business" target="_blank" rel="noopener">SCORE — Marketing Basics for Small Business</a></li>
<li><a href="https://www.sba.gov/business-guide/grow-your-business/marketing-sales" target="_blank" rel="noopener">U.S. Small Business Administration — Marketing and Sales</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal" target="_blank" rel="noopener">Khan Academy — Entrepreneurship Conversations</a></li>
</ul>
HTML;
    }

    private function lesson4_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to set up WhatsApp Business and a Facebook Page for selling, post content that attracts local buyers, and handle enquiries professionally through chat.</p>

<h2>WhatsApp Business as Your Digital Shop Counter</h2>
<p>For many Zambian small businesses, WhatsApp is the most important marketing tool. It is free, most people already use it, and it allows you to send photos, prices, and payment details instantly. With WhatsApp Business, you also get a catalogue, quick replies, and labels to organise customers.</p>
<p>Set up your profile with your real business name, a clear description, your location, and your hours. Add a profile picture of your shop sign or best product. Customers should understand what you sell within five seconds of opening your profile.</p>

<h2>What to Post on WhatsApp Status</h2>
<p>Your status updates disappear after 24 hours, so post something useful every day. Good content includes:</p>
<ul>
<li>Photos of new stock with prices</li>
<li>Short videos showing the product in use</li>
<li>Customer testimonials or "thank you" photos of packaged orders</li>
<li>Announcements of special offers or new arrivals</li>
<li>Reminders about your opening hours or delivery days</li>
</ul>
<p>Keep videos short, especially during load-shedding when people use mobile data carefully. A 15-second video is better than a two-minute one.</p>

<h2>Facebook for Discovery</h2>
<p>Facebook helps new customers find you when they search for businesses in your area. Create a Facebook Page with the same name as your WhatsApp Business account. Post at least twice a week. Join local community groups and participate politely — do not spam every group with the same advert.</p>
<p>When posting on Facebook, always include a photo, a price if possible, and a clear call to action. "Fresh okra, K5 per bunch, available today at Kalomo Central Market. WhatsApp 0977-123456 to reserve." That post tells the customer everything they need to act.</p>

<h2>Handling Enquiries Professionally</h2>
<p>When someone messages you, reply quickly. Even if you are busy, send a short acknowledgment: "Thank you for your message. I will check stock and reply in five minutes." Use quick replies for common questions about prices, location, and payment methods. Always confirm mobile money payments before delivering goods.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Set up or improve your WhatsApp Business profile.</li>
<li>Post one photo or video to your status with a price and a call to action.</li>
<li>Create a Facebook Page for your business or update an existing one.</li>
<li>Write three quick replies for common customer questions.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>WhatsApp Business</strong>: A free app for small businesses with tools like catalogues and quick replies.</li>
<li><strong>Catalogue</strong>: A digital product list customers can browse inside WhatsApp Business.</li>
<li><strong>Status update</strong>: A temporary post visible to contacts for 24 hours.</li>
<li><strong>Call to action</strong>: A clear instruction telling the customer what to do next.</li>
</ul>

<h2>Summary</h2>
<p>WhatsApp and Facebook are powerful, low-cost marketing tools for Zambian businesses. A complete profile, regular posts, and professional chat responses turn your phone into a shop counter that is open even when load-shedding keeps customers at home.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://business.whatsapp.com/products/business-app" target="_blank" rel="noopener">WhatsApp Business — Getting Started</a></li>
<li><a href="https://www.facebook.com/business/help" target="_blank" rel="noopener">Meta Business Help Centre</a></li>
<li><a href="https://www.canva.com/designschool/" target="_blank" rel="noopener">Canva Design School — Social Media Content</a></li>
</ul>
HTML;
    }

    private function lesson4_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to use word-of-mouth marketing, design simple flyers, and plan local promotions timed around Zambian events and buying cycles.</p>

<h2>The Power of Word of Mouth</h2>
<p>In Zambia, a recommendation from a friend, neighbour, or relative is often more powerful than any advert. People trust people they know. If you treat one customer well, they may bring three more. If you treat one customer badly, they may warn ten others.</p>
<p>Word of mouth starts with excellent service. Be honest about prices, deliver on time, apologise when things go wrong, and thank customers sincerely. Then make it easy for them to recommend you: give them an extra business card, ask them to share your WhatsApp number, or offer a small reward for referrals.</p>

<h2>Simple Flyers That Work</h2>
<p>A flyer does not need to be fancy. It needs to be clear. Include these five things:</p>
<ol>
<li><strong>Headline</strong>: What you offer. "Fresh Vegetables Delivered Daily."</li>
<li><strong>Benefit</strong>: Why it matters. "Save time and eat healthy."</li>
<li><strong>Price or offer</strong>: "From K15 per bundle."</li>
<li><strong>Contact</strong>: WhatsApp number, phone number, or location.</li>
<li><strong>Call to action</strong>: "Order before 6 p.m. for next-day delivery."</li>
</ol>
<p>Print in black and white if colour is too expensive. A clear black-and-white flyer beats a colourful but confusing one.</p>

<h2>Timing Promotions Around Local Life</h2>
<p>Zambian buying follows real rhythms. Plan your promotions around:</p>
<ul>
<li><strong>Payday week</strong>: Run your best offers from the 29th to the 5th.</li>
<li><strong>Farming seasons</strong>: Sell inputs before planting; sell household and celebration items after harvest.</li>
<li><strong>Holidays</strong>: Mother's Day, Father's Day, Farmer's Day, Independence Day, and Christmas create natural buying moments.</li>
<li><strong>School calendar</strong>: Parents buy uniforms, books, and shoes in December and January.</li>
<li><strong>Load-shedding schedules</strong>: Promote candles, solar lamps, charcoal, and ready meals when power is unreliable.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Write a one-page flyer for a Zambian small business.</li>
<li>Plan one promotion around payday, a farming season, or a holiday.</li>
<li>Write a script for asking a happy customer for a referral.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Word of mouth</strong>: Marketing that happens when customers tell others about your business.</li>
<li><strong>Referral</strong>: A new customer gained through a recommendation.</li>
<li><strong>Flyer</strong>: A small printed advertisement distributed by hand or posted in public places.</li>
<li><strong>Promotion</strong>: A special activity designed to increase sales or awareness.</li>
</ul>

<h2>Summary</h2>
<p>Word of mouth, simple flyers, and well-timed promotions are affordable ways to grow a small business in Zambia. Build trust, make sharing easy, and plan your marketing around the rhythms of local life.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool/" target="_blank" rel="noopener">Canva Design School — Flyer Design</a></li>
<li><a href="https://www.score.org/resource/article/how-build-word-mouth-marketing" target="_blank" rel="noopener">SCORE — Word-of-Mouth Marketing</a></li>
<li><a href="https://www.sba.gov/business-guide/grow-your-business/marketing-sales" target="_blank" rel="noopener">U.S. Small Business Administration — Marketing and Sales</a></li>
</ul>
HTML;
    }

    private function module4Quiz(): array
    {
        return [
            'title' => 'Module 4 Quiz: Marketing with WhatsApp, Facebook, and Word of Mouth',
            'description' => 'Assess your understanding of marketing basics, social media selling, and local promotions.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the difference between marketing and sales?',
                    'explanation' => 'Marketing attracts potential customers; sales is the direct conversation that converts them into buyers.',
                    'options' => [
                        ['text' => 'Marketing is the same as sales', 'is_correct' => false],
                        ['text' => 'Marketing attracts customers, while sales converts them', 'is_correct' => true],
                        ['text' => 'Sales happens before marketing', 'is_correct' => false],
                        ['text' => 'Marketing is only for big companies', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is one of the Four Ps of marketing?',
                    'explanation' => 'The Four Ps are product, price, place, and promotion.',
                    'options' => [
                        ['text' => 'Profit', 'is_correct' => false],
                        ['text' => 'People', 'is_correct' => false],
                        ['text' => 'Promotion', 'is_correct' => true],
                        ['text' => 'Process', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which WhatsApp Business feature lets you save answers to common questions?',
                    'explanation' => 'Quick replies allow you to insert pre-written answers using shortcuts.',
                    'options' => [
                        ['text' => 'Status updates', 'is_correct' => false],
                        ['text' => 'Catalogue', 'is_correct' => false],
                        ['text' => 'Quick replies', 'is_correct' => true],
                        ['text' => 'Labels', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the best length for a product video posted to WhatsApp Status?',
                    'explanation' => 'Short videos are easier to watch and use less mobile data, especially during load-shedding.',
                    'options' => [
                        ['text' => '5 to 10 minutes', 'is_correct' => false],
                        ['text' => '15 to 45 seconds', 'is_correct' => true],
                        ['text' => '2 to 3 hours', 'is_correct' => false],
                        ['text' => 'Exactly 60 seconds', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is word-of-mouth marketing especially powerful in Zambia?',
                    'explanation' => 'People tend to trust recommendations from friends, family, and neighbours more than paid advertising.',
                    'options' => [
                        ['text' => 'Because flyers are illegal', 'is_correct' => false],
                        ['text' => 'Because radio is too expensive', 'is_correct' => false],
                        ['text' => 'Because people trust recommendations from people they know', 'is_correct' => true],
                        ['text' => 'Because it is the only form of marketing allowed', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A clear black-and-white flyer is better than a colourful but confusing flyer.',
                    'explanation' => 'Clarity matters more than decoration. Customers need to understand the offer quickly.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'The best time to promote expensive items is usually the third week of the month.',
                    'explanation' => 'The week after payday is usually the best time because customers have more money available.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for a clear instruction telling a customer what to do next? (Three words)',
                    'explanation' => 'A call to action tells the customer the next step, such as "message us" or "order now."',
                    'correct_answer' => 'Call to action',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'Which P of marketing refers to where customers find and buy your product? (One word)',
                    'explanation' => 'Place refers to the location or channel where customers access your product.',
                    'correct_answer' => 'Place',
                ],
            ],
        ];
    }

    // =========================================================================
    // MODULE 5
    // =========================================================================

    private function module5Lessons(): array
    {
        return [
            [
                'title' => 'Lesson 5.1: Customer Service That Creates Loyalty',
                'duration_minutes' => 60,
                'content' => $this->lesson5_1(),
            ],
            [
                'title' => 'Lesson 5.2: Simple Loyalty and Reward Systems',
                'duration_minutes' => 75,
                'content' => $this->lesson5_2(),
            ],
            [
                'title' => 'Lesson 5.3: Asking for Referrals and Growing the Business',
                'duration_minutes' => 60,
                'content' => $this->lesson5_3(),
            ],
            [
                'title' => 'Module 5 Quiz: Keeping Customers Loyal and Growing the Business',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your understanding of customer service, loyalty systems, and referral strategies. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson5_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will understand why customer service is a marketing tool, know the habits that build loyalty, and be able to recover from a service mistake without losing the customer.</p>

<h2>Service Is Part of the Product</h2>
<p>When a customer buys from you, they are not just buying the item. They are buying the whole experience: the greeting, the waiting time, the payment process, the packaging, and the follow-up. Two shops can sell the same bag of mealie-meal at the same price, but the one with friendly service and clean packaging will win more repeat customers.</p>
<p>In Zambia, where many businesses rely on relationships, great service is your competitive advantage. A customer who feels respected will travel past three closer shops to buy from you.</p>

<h2>Habits That Build Loyalty</h2>
<ul>
<li><strong>Greet every customer</strong>: Even if you are busy, acknowledge the person. "I will be with you in one minute" is better than ignoring them.</li>
<li><strong>Keep your promises</strong>: If you say delivery is at 10 a.m., deliver at 10 a.m. If you say an item will arrive on Tuesday, make sure it does.</li>
<li><strong>Be honest</strong>: If a product is out of stock, say so. If a delivery will be late, call before the customer calls you.</li>
<li><strong>Say thank you</strong>: A sincere "Thank you for your business" or a follow-up message makes customers feel valued.</li>
<li><strong>Make things right</strong>: When something goes wrong, apologise and fix it quickly. A refunded K50 can save a customer relationship worth thousands over time.</li>
</ul>

<h2>Recovering from a Mistake</h2>
<p>Every business makes mistakes. A wrong item, a late delivery, or a rude employee can happen. The key is how you respond. Follow this process:</p>
<ol>
<li><strong>Apologise sincerely</strong>: "I am sorry this happened."</li>
<li><strong>Listen to the full complaint</strong>: Let the customer explain without interrupting.</li>
<li><strong>Fix the problem</strong>: Replace the item, refund the money, or deliver what was promised.</li>
<li><strong>Offer something extra</strong>: A small discount or free item shows you value the relationship.</li>
<li><strong>Follow up</strong>: Check that the customer is satisfied with the solution.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Write down three things your business does well for customers.</li>
<li>Write down one thing you could improve.</li>
<li>Write a script for apologising and fixing a common problem in your business.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Customer service</strong>: The support and experience you provide before, during, and after a sale.</li>
<li><strong>Loyalty</strong>: A customer's tendency to return to your business instead of switching to competitors.</li>
<li><strong>Service recovery</strong>: The process of fixing a service failure to keep the customer's trust.</li>
<li><strong>Repeat customer</strong>: A person who buys from you more than once.</li>
</ul>

<h2>Summary</h2>
<p>Great customer service is a powerful marketing tool. Greet people warmly, keep your promises, be honest, say thank you, and fix mistakes quickly. Loyal customers are the foundation of a growing business.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.dalecarnegie.com/en" target="_blank" rel="noopener">Dale Carnegie — Customer Relationships</a></li>
<li><a href="https://www.hubspot.com/service/customer-service" target="_blank" rel="noopener">HubSpot — Customer Service Guide</a></li>
<li><a href="https://www.salesforce.com/resources/articles/customer-service/" target="_blank" rel="noopener">Salesforce — Customer Service Best Practices</a></li>
</ul>
HTML;
    }

    private function lesson5_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to design a simple loyalty programme that fits a small Zambian business, choose rewards customers actually want, and track loyalty without expensive software.</p>

<h2>Why Loyalty Programmes Work</h2>
<p>It is cheaper to keep an existing customer than to find a new one. A loyalty programme gives customers a reason to come back. It does not need to be complicated. A simple paper card with ten boxes — "Buy 9 cups of chips, get the 10th free" — can increase repeat visits.</p>
<p>The best loyalty rewards are easy to understand, easy to earn, and valuable to the customer. A complicated points system confuses people. A clear "buy five, get one free" offer is immediately understood.</p>

<h2>Simple Loyalty Ideas for Zambian Businesses</h2>
<ul>
<li><strong>Punch card</strong>: A paper card stamped with each purchase. After a set number, the customer gets a free item or discount.</li>
<li><strong>Discount on next purchase</strong>: "Come back within two weeks and get 10% off your next order."</li>
<li><strong>Bundle reward</strong>: "Buy three hair treatments, get a free hair wash."</li>
<li><strong>Referral reward</strong>: "Bring a friend and both of you get K10 off."</li>
<li><strong>VIP early access</strong>: Let loyal customers know about new stock or special offers before everyone else.</li>
</ul>

<h2>Tracking Without Software</h2>
<p>You do not need a computer to run a loyalty programme. Use a notebook, a stack of paper cards, or WhatsApp labels. For example, create a WhatsApp label called "Loyal Customer" and note how many times each person has bought. When they reach the reward, send them a congratulatory message.</p>
<p>The important thing is consistency. If a customer expects their tenth purchase to be free, keep your promise. Broken loyalty programmes destroy trust faster than no programme at all.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose one loyalty idea that would suit your business.</li>
<li>Decide the reward and how many purchases are needed to earn it.</li>
<li>Design a simple paper card or notebook system to track it.</li>
<li>Explain the programme to three customers and ask if it would make them return.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Loyalty programme</strong>: A system that rewards customers for repeat purchases.</li>
<li><strong>Reward</strong>: Something given to a customer in return for their loyalty.</li>
<li><strong>Punch card</strong>: A paper card marked with each purchase until a free reward is earned.</li>
<li><strong>Repeat purchase</strong>: When a customer buys from you again.</li>
</ul>

<h2>Summary</h2>
<p>Loyalty programmes do not need to be expensive or digital. A simple, clear reward system tracked with paper or WhatsApp can keep customers coming back. The key is to offer real value and keep every promise.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.score.org/resource/article/customer-loyalty-programs-small-business" target="_blank" rel="noopener">SCORE — Customer Loyalty Programs</a></li>
<li><a href="https://www.sba.gov/business-guide/grow-your-business/customer-loyalty" target="_blank" rel="noopener">U.S. Small Business Administration — Building Customer Loyalty</a></li>
<li><a href="https://www.hubspot.com/service/customer-loyalty" target="_blank" rel="noopener">HubSpot — Customer Loyalty Guide</a></li>
</ul>
HTML;
    }

    private function lesson5_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to ask happy customers for referrals confidently, design a simple referral reward, and identify practical ways to grow your business beyond your current customer base.</p>

<h2>The Best Customers Come From Referrals</h2>
<p>A referred customer already trusts you because someone they trust vouched for you. They are easier to sell to, less likely to argue about price, and more likely to become loyal. Yet many business owners never ask for referrals because they feel shy or forget.</p>
<p>Asking is not begging. If you have delivered good service, you have earned the right to ask. The key is timing. Ask after a successful purchase, a compliment, or a repeat visit — when the customer is happiest.</p>

<h2>How to Ask for a Referral</h2>
<p>Use simple, direct language:</p>
<ul>
<li>"I am glad you liked the [product]. Do you know anyone else who might need this?"</li>
<li>"If you are happy with our service, please tell your friends. Here is an extra card they can use."</li>
<li>"We grow mostly through referrals. Is there anyone in your office or church group who would benefit from what we sell?"</li>
</ul>
<p>Make it easy for the customer to refer. Give them your WhatsApp number, a flyer, or a sample they can pass on.</p>

<h2>Referral Rewards</h2>
<p>A small reward encourages referrals. Make sure the reward is clear and fair:</p>
<ul>
<li>"Refer a friend and both of you get K10 off your next purchase."</li>
<li>"For every new customer you bring, you get a free [small product]."</li>
<li>"If three friends buy from us, your next service is half price."</li>
</ul>
<p>Reward both the referrer and the new customer when possible. This makes the customer feel generous rather than greedy.</p>

<h2>Other Ways to Grow</h2>
<p>Beyond referrals, consider these growth strategies:</p>
<ul>
<li><strong>Sell more to existing customers</strong>: Offer related products. A customer who buys hair extensions may also buy shampoo.</li>
<li><strong>Expand your place</strong>: Add delivery, open an extra day, or start selling at a nearby market.</li>
<li><strong>Raise prices carefully</strong>: If your costs rise and your quality is good, a small price increase may be accepted.</li>
<li><strong>Partner with others</strong>: A baker can partner with a tea seller; a tailor can partner with a fabric shop.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Write a short script for asking a happy customer for a referral.</li>
<li>Design one referral reward for your business.</li>
<li>List three related products or services you could offer to existing customers.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Referral</strong>: A new customer gained through recommendation.</li>
<li><strong>Referral reward</strong>: A benefit given to a customer who brings in new business.</li>
<li><strong>Upsell</strong>: Offering a higher-value or related product to an existing customer.</li>
<li><strong>Cross-sell</strong>: Selling a complementary product to a customer who is already buying.</li>
</ul>

<h2>Summary</h2>
<p>Referrals are the most valuable new customers a business can get. Ask happy customers confidently, reward them fairly, and always make it easy to share your business. Combine referrals with upselling, partnerships, and careful expansion to grow steadily.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.hubspot.com/sales/referral-marketing" target="_blank" rel="noopener">HubSpot — Referral Marketing Guide</a></li>
<li><a href="https://www.score.org/resource/article/how-get-referrals-small-business" target="_blank" rel="noopener">SCORE — How to Get Referrals</a></li>
<li><a href="https://www.sba.gov/business-guide/grow-your-business" target="_blank" rel="noopener">U.S. Small Business Administration — Growing Your Business</a></li>
</ul>
HTML;
    }

    private function module5Quiz(): array
    {
        return [
            'title' => 'Module 5 Quiz: Keeping Customers Loyal and Growing the Business',
            'description' => 'Assess your understanding of customer service, loyalty programmes, and referral strategies.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is great customer service a competitive advantage for small Zambian businesses?',
                    'explanation' => 'In a market where many sellers offer similar products, service quality can be the reason customers return.',
                    'options' => [
                        ['text' => 'Because it replaces the need for a product', 'is_correct' => false],
                        ['text' => 'Because it allows you to charge any price', 'is_correct' => false],
                        ['text' => 'Because customers often choose where to buy based on how they are treated', 'is_correct' => true],
                        ['text' => 'Because it removes all business costs', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the first step in service recovery after a mistake?',
                    'explanation' => 'A sincere apology acknowledges the customer\'s experience and begins rebuilding trust.',
                    'options' => [
                        ['text' => 'Blame the supplier', 'is_correct' => false],
                        ['text' => 'Ignore the complaint', 'is_correct' => false],
                        ['text' => 'Apologise sincerely', 'is_correct' => true],
                        ['text' => 'Raise the price', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a simple loyalty programme idea?',
                    'explanation' => 'A punch card is a simple, low-cost way to reward repeat purchases.',
                    'options' => [
                        ['text' => 'Hiring a professional accountant', 'is_correct' => false],
                        ['text' => 'Giving random discounts every day', 'is_correct' => false],
                        ['text' => 'A paper card stamped with each purchase until a free reward', 'is_correct' => true],
                        ['text' => 'Changing your business name every month', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'When is the best time to ask a customer for a referral?',
                    'explanation' => 'The best time is when the customer is happy, such as after a compliment or successful purchase.',
                    'options' => [
                        ['text' => 'Before they have bought anything', 'is_correct' => false],
                        ['text' => 'When they are complaining', 'is_correct' => false],
                        ['text' => 'After they have expressed satisfaction', 'is_correct' => true],
                        ['text' => 'Only on public holidays', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is cross-selling?',
                    'explanation' => 'Cross-selling means offering a complementary product to a customer who is already buying.',
                    'options' => [
                        ['text' => 'Selling your business to a competitor', 'is_correct' => false],
                        ['text' => 'Offering a complementary product to an existing buyer', 'is_correct' => true],
                        ['text' => 'Selling only one product forever', 'is_correct' => false],
                        ['text' => 'Lowering all prices by 50%', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Loyalty programmes must use expensive software to be effective.',
                    'explanation' => 'Simple paper cards or WhatsApp labels can be very effective for small businesses.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A referred customer usually trusts your business more than a stranger who found you online.',
                    'explanation' => 'Referrals come with built-in trust because someone the customer knows has already vouched for you.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for the process of fixing a service mistake to keep a customer\'s trust? (Two words)',
                    'explanation' => 'Service recovery is the process of resolving a service failure and restoring customer confidence.',
                    'correct_answer' => 'Service recovery',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for selling a higher-value product to an existing customer? (One word)',
                    'explanation' => 'Upselling encourages a customer to buy a more expensive or upgraded version.',
                    'correct_answer' => 'Upsell',
                ],
            ],
        ];
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
        $this->command->info('=== Sales & Marketing Content Seed Summary ===');
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
        $this->command->info('Certificate in Sales & Marketing content seeded successfully.');
    }
}
