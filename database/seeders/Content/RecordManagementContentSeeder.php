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

class RecordManagementContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Record Management')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Record Management" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in Record Management already has modules. Skipping content seed.');
            return;
        }

        DB::transaction(function () {
            $modules = $this->createModules();
            $questionBank = [];

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

                // Update module duration
                $module->duration_minutes = $moduleDuration;
                $module->save();

                // Create module quiz (final lesson already created as Quiz type; create Quiz record separately)
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

            // Assignments
            $this->createAssignments();
        });

        $this->printSummary();
    }

    private function createModules(): array
    {
        $moduleData = [
            [
                'title' => 'Module 1: Foundations of Records Management',
                'description' => 'Understand why records matter, identify different record types, and learn the core principles of good record keeping in a Zambian context.',
            ],
            [
                'title' => 'Module 2: Paper Filing Systems',
                'description' => 'Master alphabetical, numerical, subject and chronological filing methods, and learn how to track file movement using registers.',
            ],
            [
                'title' => 'Module 3: Electronic Records Management',
                'description' => 'Organise digital files, build registers in spreadsheets, and use cloud storage to keep electronic records safe and accessible.',
            ],
            [
                'title' => 'Module 4: Retention, Confidentiality and Disaster Preparedness',
                'description' => 'Apply retention and disposal schedules, protect confidential information under the Data Protection Act, and prepare for disasters.',
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

    private function module1Lessons(): array
    {
        return [
            [
                'title' => '1.1 Why Records Matter in Zambian Workplaces',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain why records are essential for any organisation, describe the consequences of poor record keeping using real Zambian examples, and list five everyday situations where accurate records protect money, time and legal rights.</p>

<h2>What Are Records?</h2>
<p>A record is any document or digital file that provides evidence of a business activity or decision. Records include receipts, invoices, meeting minutes, employee files, student registers, ZRA tax returns, bank statements and even WhatsApp messages that confirm an order. If you can use it to prove something happened, it is a record.</p>
<p>In Zambia, every organisation—from a one-person market stall in Soweto Market to a government ministry in Lusaka—creates records daily. The difference between success and failure often comes down to how well those records are kept.</p>

<h2>Why Records Matter</h2>
<p>Good records protect you in four ways:</p>
<ul>
<li><strong>Legal protection</strong> — If a customer disputes a payment, a signed receipt proves what was agreed. If ZRA audits your business, your tax records show you complied.</li>
<li><strong>Financial control</strong> — Without records, you cannot know whether your chicken-rearing business in Kalomo made a profit or a loss last month.</li>
<li><strong>Operational efficiency</strong> — A clinic that keeps proper patient files can treat people faster and avoid dangerous mistakes like double-dosing medication.</li>
<li><strong>Accountability</strong> — A school that records attendance and fees can show parents exactly where their money went.</li>
</ul>

<h2>Worked Example: The Missing Receipt</h2>
<p>Mrs Phiri runs a small grocery shop. She sells goods on credit to several neighbours and keeps the details in her head. One customer denies owing ZMW 350 for bags of mealie-meal and cooking oil. Because Mrs Phiri has no written record of the credit sale, she cannot prove the debt. She loses the money and the friendship.</p>
<p>A simple exercise book with columns for date, customer name, items, amount and signature would have prevented the loss. The lesson is clear: memory is not a filing system.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Look around your home, shop or office and find five items that count as records (receipt, invoice, bank SMS, letter, email).</li>
<li>For each item, write down what would happen if it were lost.</li>
<li>Choose one record type you currently keep poorly. Write a simple plan to improve it this week.</li>
<li>If you use a phone for business, start saving screenshots of mobile money receipts in a dedicated album.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Record</strong> — documentary evidence of a transaction, decision or activity.</li>
<li><strong>Audit</strong> — an official inspection of records to check accuracy and compliance.</li>
<li><strong>Compliance</strong> — following laws, regulations and organisational policies.</li>
<li><strong>Accountability</strong> — being able to explain and justify decisions and spending.</li>
<li><strong>Evidence</strong> — information that proves something is true or false.</li>
</ul>

<h2>Summary</h2>
<p>Records are the memory of an organisation. They protect money, prove compliance, improve efficiency and build trust with customers, employees and government bodies. Whether you run a market stall, a school or a government office in Zambia, losing records means losing money, time and credibility. The first step to better record management is to treat every receipt, note and file as an asset.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.zra.org.zm">Zambia Revenue Authority — Taxpayer Information</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/organize-files-folders/">Microsoft Learn — Organise Files and Folders</a></li>
<li><a href="https://www.khanacademy.org">Khan Academy — General Learning Resources</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.2 Types of Records and Record Systems',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>After this lesson you will be able to classify records by purpose and format, identify which system suits a given organisation, and explain the difference between operational, financial, legal and personnel records using examples from Zambian workplaces.</p>

<h2>Classifying Records by Purpose</h2>
<p>Not all records serve the same function. Understanding the type of record helps you decide how long to keep it, who may access it and where to store it. The four main categories are:</p>

<h3>Operational Records</h3>
<p>These support day-to-day work. Examples include stock cards in a shop, patient appointment books in a clinic, delivery schedules for a courier, and lesson attendance registers at Edutrack Computer Training College. Operational records are usually active for a short time but may need to be kept for several months for follow-up.</p>

<h3>Financial Records</h3>
<p>These track money moving in and out. Examples include cash books, MTN MoMo and Airtel Money transaction histories, ZRA TPIN registration documents, PAYE returns, bank statements and invoices. Financial records must be kept for at least five years under Zambian tax law because ZRA can audit past returns.</p>

<h3>Legal Records</h3>
<p>These prove rights, obligations or compliance. Examples include contracts of employment, land title deeds, business registration certificates from PACRA, tenancy agreements and court judgments. Legal records often need to be kept permanently or for the life of the asset they describe.</p>

<h3>Personnel Records</h3>
<p>These relate to employees or students. Examples include job application forms, NRC copies, qualification certificates, disciplinary notes, leave records and payroll details. Personnel records are highly confidential because they contain sensitive personal data.</p>

<h2>Classifying by Format</h2>
<p>Records also come in different physical forms:</p>
<ul>
<li><strong>Paper records</strong> — still common in most Zambian offices; easy to create but need physical space and protection from moisture and pests.</li>
<li><strong>Electronic records</strong> — files on a computer, smartphone or server; fast to search but need backup and protection from power surges during load-shedding.</li>
<li><strong>Microfilm and microfiche</strong> — older photographic formats sometimes found in archives; stable over decades but require special equipment.</li>
<li><strong>Audio and video</strong> — meeting recordings, training videos and voicemail messages; increasingly common but need clear labelling.</li>
</ul>

<h2>Worked Example: A Small Construction Firm</h2>
<p>Chanda Builders in Kalomo keeps the following records:</p>
<ul>
<li>Operational: daily work schedules, material delivery notes, site inspection reports.</li>
<li>Financial: invoices to clients, receipts from suppliers, ZMW-denominated quotations, mobile money payment confirmations.</li>
<li>Legal: signed contracts with clients, PACRA business registration, ZRA tax clearance certificate.</li>
<li>Personnel: employee NRC copies, apprenticeship agreements, safety training attendance sheets.</li>
</ul>
<p>Each type is stored differently. Financial records go in a lockable steel cabinet in the manager’s office. Personnel files are in a separate cabinet with restricted access. Daily operational sheets stay on the site foreman’s desk until the project ends.</p>

<h2>Try It Yourself</h2>
<ol>
<li>List every record type you create or receive in one week at your workplace, school or business.</li>
<li>Sort them into the four purpose categories: operational, financial, legal, personnel.</li>
<li>Decide which format each record currently uses: paper, electronic or both.</li>
<li>Identify one record type that is stored in the wrong place or wrong format. Plan a move.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Operational record</strong> — a document that supports daily business activities.</li>
<li><strong>Financial record</strong> — a document that tracks income, expenditure and assets.</li>
<li><strong>Legal record</strong> — a document that establishes rights, duties or compliance.</li>
<li><strong>Personnel record</strong> — a document containing information about an employee or student.</li>
<li><strong>Active record</strong> — a record needed for current work and consulted frequently.</li>
</ul>

<h2>Summary</h2>
<p>Records fall into four main purpose categories—operational, financial, legal and personnel—and several format categories, chiefly paper and electronic. Knowing the type of record tells you how long to keep it, where to store it and who should access it. A small construction firm, a clinic and a college all create the same basic types of records; the difference lies in how well they are organised and protected.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.pacra.org.zm">Patents and Companies Registration Agency — Zambia</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Office Skills</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/personal-finance">Khan Academy — Personal Finance</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.3 Principles of Good Record Keeping',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to apply the six core principles of good record keeping—accuracy, completeness, timeliness, consistency, accessibility and security—to any paper or electronic system in a Zambian office, shop or government department.</p>

<h2>The Six Principles</h2>
<p>Good record keeping is not about having the most expensive software or the biggest filing cabinet. It is about following six simple principles every day. Let us look at each one with practical examples.</p>

<h3>1. Accuracy</h3>
<p>Every record must reflect the truth. If a customer pays ZMW 500, the receipt must say ZMW 500, not ZMW 50 or ZMW 5,000. If an employee’s NRC number is 123456/10/1, copying it as 123456/01/1 could cause payroll errors or bank rejection. Always double-check names, amounts, dates and identification numbers before filing.</p>

<h3>2. Completeness</h3>
<p>A record should contain every piece of information needed to understand the event. A credit sale note without a customer signature is incomplete because the customer can later deny the debt. A meeting minute without a list of attendees is incomplete because you cannot prove who made a decision.</p>

<h3>3. Timeliness</h3>
<p>Records should be created at the time of the event, not days or weeks later. If a nurse waits until Friday to write up Monday’s patient notes, details will be forgotten and dangerous errors can occur. If a shopkeeper records sales at the end of the month instead of daily, theft or stock loss may go unnoticed.</p>

<h3>4. Consistency</h3>
<p>Everyone in the organisation should use the same format and rules. If one clerk writes dates as 05/06/2026 and another writes June 5, 2026, searching for records becomes difficult. If one person files invoices by supplier name and another by invoice number, nothing can be found quickly. Agree on standard formats and stick to them.</p>

<h3>5. Accessibility</h3>
<p>People who need records should be able to find them within minutes. A file buried under old newspapers on a manager’s desk is not accessible. A spreadsheet saved on one employee’s personal laptop with no password shared is not accessible. Organise files so that authorised staff can retrieve them without asking the one person who "knows where everything is."</p>

<h3>6. Security</h3>
<p>Records must be protected from unauthorised access, loss and damage. Paper records need lockable cabinets and fire safety. Electronic records need passwords, backups and protection from viruses. Personnel and medical records are especially sensitive; showing them to the wrong person can break the law and destroy trust.</p>

<h2>Worked Example: Fixing a Broken System</h2>
<p>Mr Zulu runs a transport business with three minibuses. His records are a mess. Receipts live in the glove compartments of the buses. Fuel purchases are noted on scraps of paper that get washed with his overalls. Driver attendance is recorded only when someone fails to show up.</p>
<p>Using the six principles, Mr Zulu makes these changes:</p>
<ul>
<li><strong>Accuracy</strong> — He buys printed receipt books and insists every passenger fare is recorded immediately.</li>
<li><strong>Completeness</strong> — Each receipt shows date, route, amount, vehicle registration and driver initials.</li>
<li><strong>Timeliness</strong> — Drivers hand in receipts daily, not weekly.</li>
<li><strong>Consistency</strong> — All dates use DD/MM/YYYY format; all amounts are in ZMW.</li>
<li><strong>Accessibility</strong> — Receipts go into labelled envelopes by vehicle and week, stored in one metal box.</li>
<li><strong>Security</strong> — The box locks and stays in Mr Zulu’s office, not on a bus.</li>
</ul>
<p>Within one month he can see exactly which route earns the most money and which driver is most reliable.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Pick one record you keep regularly (sales, attendance, expenses).</li>
<li>Score yourself from 1 to 5 on each of the six principles.</li>
<li>Write down one specific improvement for the lowest-scoring principle.</li>
<li>Implement that improvement today and review it after one week.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Accuracy</strong> — the quality of being correct and free from error.</li>
<li><strong>Completeness</strong> — containing all necessary parts and information.</li>
<li><strong>Timeliness</strong> — being recorded or available at the right time, not late.</li>
<li><strong>Consistency</strong> — always following the same rules and formats.</li>
<li><strong>Accessibility</strong> — being easy for authorised people to find and use.</li>
<li><strong>Security</strong> — protection against unauthorised access, loss or damage.</li>
</ul>

<h2>Summary</h2>
<p>The six principles of good record keeping—accuracy, completeness, timeliness, consistency, accessibility and security—form a practical checklist for any organisation. You do not need expensive technology to apply them. A market trader with a ruled exercise book and a consistent habit can keep better records than a large office with computers that nobody uses properly. Start with one principle, make it a habit, then add the next.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/docs/answer/6000292">Google Docs Help — Create and Share Documents</a></li>
<li><a href="https://www.libreoffice.org/get-help/">LibreOffice — Free Office Suite Help</a></li>
<li><a href="https://www.khanacademy.org">Khan Academy — General Learning</a></li>
</ul>
HTML,
            ],
            [
                'title' => 'Module 1 Quiz: Foundations of Records Management',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Complete the quiz to demonstrate your understanding of why records matter, record types and the core principles of good record keeping.</p>',
            ],
        ];
    }

    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: Foundations of Records Management',
            'description' => 'Test your understanding of record importance, classification and the six principles of good record keeping.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following best describes a "record"?',
                    'explanation' => 'A record is evidence of a transaction, decision or activity, not just any piece of paper.',
                    'options' => [
                        ['text' => 'Any piece of paper in an office', 'is_correct' => false],
                        ['text' => 'Documentary evidence of a business activity or decision', 'is_correct' => true],
                        ['text' => 'Only documents signed by a manager', 'is_correct' => false],
                        ['text' => 'Emails sent to customers', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Under Zambian tax law, how long should financial records generally be kept?',
                    'explanation' => 'ZRA may audit past returns, so financial records should be kept for at least five years.',
                    'options' => [
                        ['text' => 'One year', 'is_correct' => false],
                        ['text' => 'Three years', 'is_correct' => false],
                        ['text' => 'At least five years', 'is_correct' => true],
                        ['text' => 'Ten years', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which principle requires that records be created at the time of the event?',
                    'explanation' => 'Timeliness means recording information when the event happens, not days later.',
                    'options' => [
                        ['text' => 'Accuracy', 'is_correct' => false],
                        ['text' => 'Completeness', 'is_correct' => false],
                        ['text' => 'Timeliness', 'is_correct' => true],
                        ['text' => 'Consistency', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'A clinic stores patient files in a locked cabinet with restricted access. Which principle does this demonstrate?',
                    'explanation' => 'Security means protecting records from unauthorised access and damage.',
                    'options' => [
                        ['text' => 'Accessibility', 'is_correct' => false],
                        ['text' => 'Consistency', 'is_correct' => false],
                        ['text' => 'Security', 'is_correct' => true],
                        ['text' => 'Timeliness', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which record type includes contracts of employment and business registration certificates?',
                    'explanation' => 'Legal records establish rights, obligations and compliance.',
                    'options' => [
                        ['text' => 'Operational records', 'is_correct' => false],
                        ['text' => 'Financial records', 'is_correct' => false],
                        ['text' => 'Legal records', 'is_correct' => true],
                        ['text' => 'Personnel records', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is consistency important when keeping records?',
                    'explanation' => 'Consistency ensures everyone uses the same formats so records can be found and understood quickly.',
                    'options' => [
                        ['text' => 'It makes the office look tidy', 'is_correct' => false],
                        ['text' => 'It ensures records can be searched and understood by anyone', 'is_correct' => true],
                        ['text' => 'It reduces the number of records created', 'is_correct' => false],
                        ['text' => 'It allows any employee to delete old files', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Memory is a reliable substitute for written records in a small business.',
                    'explanation' => 'Memory fails; written or electronic records provide proof that memory cannot.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A credit sale note without a customer signature is an incomplete record.',
                    'explanation' => 'Without a signature, the customer can later deny the debt, so the record lacks essential evidence.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What one word describes the principle of being correct and free from error in record keeping?',
                    'explanation' => 'Accuracy means every detail in a record is correct.',
                    'correct_answer' => 'Accuracy',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What type of record tracks money moving in and out of an organisation?',
                    'explanation' => 'Financial records include invoices, receipts, bank statements and tax returns.',
                    'correct_answer' => 'Financial',
                ],
            ],
        ];
    }

    private function module2Lessons(): array
    {
        return [
            [
                'title' => '2.1 Alphabetical, Numerical and Subject Filing',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>After this lesson you will be able to set up and maintain three fundamental paper filing systems—alphabetical, numerical and subject—and choose the right method for a given Zambian office, school or small business.</p>

<h2>Alphabetical Filing</h2>
<p>Alphabetical filing arranges records by name, usually surname first. It is the easiest system to learn and works well for customer files, patient records and supplier directories. In Zambia, where many people share common surnames such as Banda, Phiri or Zulu, you need a tie-breaker rule.</p>
<p><strong>Standard rule:</strong> File by surname, then first name, then other names. For example:</p>
<ol>
<li>Banda, Agnes M.</li>
<li>Banda, Charles K.</li>
<li>Banda, Grace T.</li>
</ol>
<p>Always write the surname in capital letters on the file label so clerks can spot it quickly. If two people have identical names, add a date of birth or NRC number in brackets: Banda, John (NRC 123456/10/1).</p>

<h2>Numerical Filing</h2>
<p>Numerical filing gives each file a unique number. It is confidential because the number reveals nothing about the person, and it allows unlimited growth. Hospitals, banks and large government offices often use numerical filing for personnel and medical records.</p>
<p><strong>How it works:</strong> Assign the next available number to each new file. Maintain an alphabetical index card or computer list that links names to numbers. For example, file 10427 might belong to Mrs Ngwira. Only staff with access to the index know this.</p>
<p>A simple numerical system for a small clinic in Kalomo might look like this:</p>
<table>
<tr><th>File Number</th><th>Patient Name</th><th>Date Opened</th></tr>
<tr><td>001</td><td>Chileshe, Mary</td><td>03/01/2026</td></tr>
<tr><td>002</td><td>Mwale, James</td><td>05/01/2026</td></tr>
<tr><td>003</td><td>Nyirenda, Ruth</td><td>08/01/2026</td></tr>
</table>

<h2>Subject Filing</h2>
<p>Subject filing groups records by topic rather than by person. It suits project files, committee minutes and correspondence about specific issues. For example, a college might use subjects such as "Examinations," "Staff Training," "Building Maintenance" and "Student Discipline."</p>
<p>Subject filing requires a <strong>classification list</strong>—an agreed list of subjects with rules for where borderline topics go. Without this list, one clerk files a teacher's leave request under "Staff" while another files it under "Human Resources," and the record is lost.</p>

<h2>Worked Example: Choosing a System</h2>
<p>Mrs Tembo runs a hardware shop. She has:</p>
<ul>
<li>200 customer credit accounts,</li>
<li>50 supplier catalogues and invoices, and</li>
<li>30 employee files.</li>
</ul>
<p>She decides on three separate systems:</p>
<ul>
<li><strong>Customer accounts</strong> — alphabetical by surname, because staff need to find accounts quickly at the counter.</li>
<li><strong>Supplier records</strong> — subject filing by product type (cement, plumbing, electrical, paint), because she often needs every quote for one product category.</li>
<li><strong>Employee files</strong> — numerical, to protect confidentiality and prevent casual browsing.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Gather 10 pieces of paper from your workplace or home that represent records.</li>
<li>Sort them alphabetically by surname, then by subject, then by number.</li>
<li>Decide which system felt fastest and most logical for each group.</li>
<li>Design a simple file label template on paper and test it with three real files.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Alphabetical filing</strong> — arranging records in A-to-Z order by name.</li>
<li><strong>Numerical filing</strong> — arranging records by assigned number, with an index linking numbers to names.</li>
<li><strong>Subject filing</strong> — arranging records by topic or category.</li>
<li><strong>Tie-breaker</strong> — a secondary rule used when two items sort identically under the main rule.</li>
<li><strong>Classification list</strong> — an agreed directory of subjects used for consistent subject filing.</li>
</ul>

<h2>Summary</h2>
<p>Alphabetical filing is simple and fast for name-based retrieval. Numerical filing protects confidentiality and scales without limit. Subject filing groups related topics for project and committee work. Many organisations use a combination. The key is to choose a system that matches how people actually search for information, write the rules down, and train everyone to follow them.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.libreoffice.org/get-help/">LibreOffice — Free Office Suite Help</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Office Skills</a></li>
<li><a href="https://www.khanacademy.org">Khan Academy — General Learning</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.2 Chronological and Geographical Filing',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to organise records by date and by location, understand when these methods are superior to name-based filing, and apply them to Zambian examples such as district project files and monthly sales reports.</p>

<h2>Chronological Filing</h2>
<p>Chronological filing arranges records by date, usually with the newest at the front or top. It is ideal for records where the date is the most important search key. Common examples include:</p>
<ul>
<li>daily cash sheets from a shop,</li>
<li>incoming correspondence registers,</li>
<li>meeting minutes arranged by meeting date,</li>
<li>bank statements, and</li>
<li>ZESCO prepaid token purchase histories.</li>
</ul>
<p><strong>Tip:</strong> Always use the same date format. DD/MM/YYYY is standard in Zambia and avoids the confusion of American MM/DD/YYYY. A date written 04/05/2026 could mean 4 May or 5 April depending on the system; writing the month name removes doubt: 04-May-2026.</p>

<h2>Combining Chronological with Other Systems</h2>
<p>Most offices use a hybrid. For example, a council office in Livingstone might file land application forms first by <strong>district</strong> (geographical), then within each district by <strong>date received</strong> (chronological). A clinic might file patient folders alphabetically, but place the most recent consultation notes at the front of each folder so the doctor sees the latest information first.</p>

<h2>Geographical Filing</h2>
<p>Geographical filing arranges records by physical location. It suits organisations that work across many places. Examples include:</p>
<ul>
<li>a national NGO with projects in Kalomo, Mongu, Kitwe and Chipata,</li>
<li>a bus company with routes from Lusaka to provincial towns,</li>
<li>a seed supplier with depots in each district, and</li>
<li>a government ministry with regional offices.</li>
</ul>
<p>The usual hierarchy is country, then province, then district, then ward or town. Within the lowest level you may add alphabetical or chronological order. A file path might look like:</p>
<blockquote>
<p>Southern Province / Kalomo District / Agriculture Project / 2026 / File 12</p>
</blockquote>

<h2>Worked Example: A Monthly Reporting System</h2>
<p>Mr Mutale manages 15 community health workers across Choma District. Each worker submits a paper report at month end. Mr Mutale uses geographical-then-chronological filing:</p>
<ol>
<li>A metal cabinet is divided into four sections: North, South, East, West.</li>
<li>Within each section, reports hang in monthly folders: Jan-2026, Feb-2026, Mar-2026.</li>
<li>Each report is stamped with the date received and initialed by the clerk.</li>
</ol>
<p>When the district medical officer asks for February reports from the north, Mr Mutale opens one folder and has every document in seconds.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Collect all your receipts, bills or letters from the past three months.</li>
<li>Sort them first by date, oldest to newest. Note how easy it is to see spending patterns.</li>
<li>Now re-sort them by type (electricity, water, airtime, food). Note which view is more useful.</li>
<li>Decide which hybrid system you will use going forward and label two physical folders.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Chronological filing</strong> — arranging records by date.</li>
<li><strong>Geographical filing</strong> — arranging records by location or region.</li>
<li><strong>Hybrid system</strong> — combining two or more filing methods in one system.</li>
<li><strong>Date stamp</strong> — an ink stamp showing the date a document was received or created.</li>
<li><strong>Hierarchy</strong> — a system of levels, from broad to specific.</li>
</ul>

<h2>Summary</h2>
<p>Chronological filing puts time first; it works for correspondence, bank statements and reports. Geographical filing puts place first; it works for multi-site organisations and regional programmes. In practice, most Zambian offices use hybrid systems that combine date, place, subject or name. The essential step is to write the rules down and follow them every day.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.libreoffice.org/get-help/">LibreOffice — Free Office Suite Help</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Office Skills</a></li>
<li><a href="https://support.google.com/docs/answer/6000292">Google Docs Help — Organise Documents</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.3 File Movement Registers and Tracking',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to design and use a file movement register, explain why tracking is essential for accountability, and apply correct checkout and return procedures in any Zambian office or government department.</p>

<h2>Why Track File Movement?</h2>
<p>A file that leaves the filing cabinet is a file at risk. It can be lost, damaged, read by unauthorised people or simply forgotten on someone’s desk. A file movement register tells you who has the file, when they took it, why they need it and when they must return it. Without this record, confidential documents can vanish and nobody is responsible.</p>
<p>In government offices and NGOs across Zambia, auditors routinely ask for file movement registers. If a personnel file goes missing and the register shows no checkout entry, the clerk in charge may face disciplinary action.</p>

<h2>Designing a File Movement Register</h2>
<p>A good register, whether paper or electronic, contains at least these columns:</p>
<table>
<tr><th>Date Out</th><th>File Number/Name</th><th>Borrower Name</th><th>Department</th><th>Purpose</th><th>Date Due Back</th><th>Date Returned</th><th>Receiver Initials</th></tr>
<tr><td>05/06/2026</td><td>Emp-1042</td><td>Chanda B.</td><td>HR</td><td>Annual review</td><td>12/06/2026</td><td>11/06/2026</td><td>M.P.</td></tr>
<tr><td>06/06/2026</td><td>Fin-2026-03</td><td>Nyirenda T.</td><td>Accounts</td><td>Audit query</td><td>13/06/2026</td><td></td><td></td></tr>
</table>

<h2>Checkout and Return Procedures</h2>
<p><strong>Checkout:</strong></p>
<ol>
<li>The borrower completes the register in person. Nobody signs on behalf of another person.</li>
<li>The filing clerk checks that the previous entry shows the file was returned.</li>
<li>The clerk notes the due-back date, usually within five working days.</li>
<li>The file is handed over; both parties initial the entry.</li>
</ol>
<p><strong>Return:</strong></p>
<ol>
<li>The borrower brings the file to the registry, not to the clerk’s desk or canteen.</li>
<li>The clerk checks the file for missing pages or damage.</li>
<li>The clerk fills in the "Date Returned" column and initials it.</li>
<li>The file goes back to its correct place in the cabinet immediately.</li>
</ol>

<h2>Electronic Tracking</h2>
<p>Even small offices can use a simple Excel sheet or Google Sheet as a movement register. The advantage is automatic sorting and filtering. If you need to know every file borrowed by the Accounts department in June, a filter gives the answer in seconds. A paper register requires manual searching.</p>
<p>However, electronic registers need backup. If the laptop hard drive fails and there is no copy, the entire tracking history is lost. Save the sheet to Google Drive or email a copy to yourself every Friday.</p>

<h2>Worked Example: The Missing File</h2>
<p>A primary school in Mongu keeps pupil transfer certificates in numerical order. The head teacher needs certificate 847 for a court case. The filing cabinet is empty. Because the school has no movement register, nobody knows whether the certificate was borrowed, lost or stolen. The court case is delayed and the parents threaten to sue.</p>
<p>If a simple register had been kept, the head teacher would know that the deputy head borrowed the file on 12 March for a district meeting and never returned it. One phone call would solve the problem.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Draw a file movement register on paper with the eight columns shown above.</li>
<li>Choose five books or folders from your shelf. Pretend they are office files.</li>
<li>Lend each "file" to a friend or family member and record the transaction in your register.</li>
<li>Track due dates. When a file is two days overdue, follow up using the register.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>File movement register</strong> — a log that tracks who borrows a file, when and why.</li>
<li><strong>Checkout</strong> — the process of recording a file leaving the registry.</li>
<li><strong>Return</strong> — the process of recording a file coming back to the registry.</li>
<li><strong>Due-back date</strong> — the deadline by which a borrowed file must be returned.</li>
<li><strong>Audit trail</strong> — a documented history showing who handled a record and when.</li>
</ul>

<h2>Summary</h2>
<p>File movement registers are the safety net of any filing system. They turn an anonymous cabinet into an accountable system where every file has a known location and a known custodian. Whether you use a ruled book, an Excel sheet or a Google Form, the rules are the same: every movement is recorded, every borrower is named, and every return is confirmed. Without tracking, even the best filing system becomes a black hole.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.libreoffice.org/get-help/">LibreOffice — Free Office Suite Help</a></li>
<li><a href="https://support.google.com/docs/answer/6000292">Google Docs Help — Create and Share Documents</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/organize-files-folders/">Microsoft Learn — Organise Files and Folders</a></li>
</ul>
HTML,
            ],
            [
                'title' => 'Module 2 Quiz: Paper Filing Systems',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Complete the quiz to show your understanding of alphabetical, numerical, subject, chronological and geographical filing, and file movement tracking.</p>',
            ],
        ];
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Paper Filing Systems',
            'description' => 'Test your knowledge of filing methods, hybrid systems and file movement registers.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In alphabetical filing, what is the standard tie-breaker when two people share the same surname?',
                    'explanation' => 'When surnames match, file by first name, then other names.',
                    'options' => [
                        ['text' => 'File by date of birth', 'is_correct' => false],
                        ['text' => 'File by first name, then other names', 'is_correct' => true],
                        ['text' => 'File by NRC number only', 'is_correct' => false],
                        ['text' => 'File by district of origin', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is numerical filing considered more confidential than alphabetical filing?',
                    'explanation' => 'A number reveals nothing about the person, so casual browsers cannot identify sensitive files.',
                    'options' => [
                        ['text' => 'Numbers are harder to read', 'is_correct' => false],
                        ['text' => 'A number does not reveal the person\'s name or identity', 'is_correct' => true],
                        ['text' => 'Alphabetical files are always left unlocked', 'is_correct' => false],
                        ['text' => 'Numerical files use smaller cabinets', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which filing method groups records by topic such as "Examinations" or "Staff Training"?',
                    'explanation' => 'Subject filing organises records by topic or category rather than by name or number.',
                    'options' => [
                        ['text' => 'Alphabetical filing', 'is_correct' => false],
                        ['text' => 'Numerical filing', 'is_correct' => false],
                        ['text' => 'Subject filing', 'is_correct' => true],
                        ['text' => 'Chronological filing', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'A council office files land applications first by district, then by date received. What is this called?',
                    'explanation' => 'Combining geographical and chronological filing creates a hybrid system.',
                    'options' => [
                        ['text' => 'Pure chronological filing', 'is_correct' => false],
                        ['text' => 'Hybrid filing', 'is_correct' => true],
                        ['text' => 'Random filing', 'is_correct' => false],
                        ['text' => 'Alphabetical filing', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the purpose of a file movement register?',
                    'explanation' => 'The register tracks who has a file, when they took it and when it is due back.',
                    'options' => [
                        ['text' => 'To list every file in the cabinet', 'is_correct' => false],
                        ['text' => 'To track who borrows files and when they must return them', 'is_correct' => true],
                        ['text' => 'To calculate the cost of filing cabinets', 'is_correct' => false],
                        ['text' => 'To translate file names into numbers', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which date format is recommended for Zambian offices to avoid confusion?',
                    'explanation' => 'DD/MM/YYYY is standard in Zambia and avoids American MM/DD/YYYY confusion.',
                    'options' => [
                        ['text' => 'MM/DD/YYYY', 'is_correct' => false],
                        ['text' => 'DD/MM/YYYY', 'is_correct' => true],
                        ['text' => 'YYYY/DD/MM', 'is_correct' => false],
                        ['text' => 'Any format is acceptable', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'In a file movement register, one person may sign on behalf of another borrower.',
                    'explanation' => 'Borrowers must sign personally so the audit trail is accurate and accountable.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Geographical filing is useful for organisations that operate across multiple districts or provinces.',
                    'explanation' => 'Geographical filing arranges records by location, making it ideal for multi-site organisations.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What one-word name is given to a system that combines two or more filing methods?',
                    'explanation' => 'A hybrid system mixes methods such as geographical plus chronological filing.',
                    'correct_answer' => 'Hybrid',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'In numerical filing, what document links a file number to a person\'s name?',
                    'explanation' => 'The index is the alphabetical list or database that connects numbers to names.',
                    'correct_answer' => 'Index',
                ],
            ],
        ];
    }

    private function module3Lessons(): array
    {
        return [
            [
                'title' => '3.1 Organising Computer Files and Folders',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to design a clear folder structure on a Windows computer, use consistent file-naming rules, and explain why good digital organisation saves time and prevents data loss during load-shedding or hardware failure.</p>

<h2>Why Digital Organisation Matters</h2>
<p>A computer desktop covered in files named "New Document," "stuff," "final_final_v2" or "IMG_2047" is a digital version of a messy paper desk. You cannot find what you need, you accidentally overwrite important work, and when the hard drive fails—as they all do eventually—you have no idea what was lost.</p>
<p>In Zambia, where power cuts are common and UPS backup is rare in small offices, a sudden shutdown can corrupt open files. Organised files are easier to back up quickly because you know exactly where the important data lives.</p>

<h2>Designing a Folder Structure</h2>
<p>Think of your computer like a filing cabinet. The main folders are the drawers; subfolders are the files inside. A good structure for a small business might look like this:</p>
<blockquote>
<p>C:\Business Records\<br>
&nbsp;&nbsp;├── 01_Financial\<br>
&nbsp;&nbsp;│&nbsp;&nbsp;&nbsp;├── 2025\<br>
&nbsp;&nbsp;│&nbsp;&nbsp;&nbsp;└── 2026\<br>
&nbsp;&nbsp;├── 02_Customers\<br>
&nbsp;&nbsp;│&nbsp;&nbsp;&nbsp;├── Active\<br>
&nbsp;&nbsp;│&nbsp;&nbsp;&nbsp;└── Closed\<br>
&nbsp;&nbsp;├── 03_Suppliers\<br>
&nbsp;&nbsp;├── 04_Employees\<br>
&nbsp;&nbsp;├── 05_Correspondence\<br>
&nbsp;&nbsp;└── 06_Tax_ZRA\<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├── TPIN_Documents\<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├── Monthly_Returns\<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;└── Audit_Files</p>
</blockquote>
<p>Leading numbers (01_, 02_) force folders to sort in your preferred order rather than alphabetically.</p>

<h2>File-Naming Rules</h2>
<p>A good file name tells you what the document is, when it was created and which version it is, without opening it. Use this formula:</p>
<blockquote>
<p><strong>YYYYMMDD_DescriptiveName_Version.ext</strong></p>
</blockquote>
<p>Examples:</p>
<ul>
<li>20260605_Invoice_ChandaBuilders_v01.pdf</li>
<li>20260610_Payslip_May_Phiri_v02.xlsx</li>
<li>20260611_ZRA_Payment_Receipt_v01.jpg</li>
</ul>
<p>Avoid spaces in file names; use underscores or hyphens instead. Some older systems and web uploads break when spaces are present.</p>

<h2>Worked Example: From Chaos to Order</h2>
<p>Ms Kunda runs a catering business. Her Documents folder contains 300 unorganised files. She spends ten minutes every day searching for recipes, supplier quotes and event invoices. After applying the lessons above, she creates a simple structure:</p>
<ul>
<li>Events → 2026 → June → 15-June-Wedding-Mutale</li>
<li>Suppliers → Veg_Market → Price_Lists</li>
<li>Finance → Invoices_Out → 2026</li>
<li>Finance → Invoices_In → 2026</li>
</ul>
<p>She names every new file with the date-first rule. Now when a client calls about a deposit paid in March, she finds the receipt in under thirty seconds. She also copies the entire Finance folder to a flash drive every Friday, so load-shedding no longer terrifies her.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open your computer’s Documents folder. Count how many files have meaningless names.</li>
<li>Create a new folder structure with at least three main categories relevant to your work or study.</li>
<li>Rename ten existing files using the YYYYMMDD_Description_Version format.</li>
<li>Create a "Backup" folder and copy your most important ten files into it.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Folder structure</strong> — the organised hierarchy of directories on a computer.</li>
<li><strong>File-naming convention</strong> — a set of rules for naming files consistently.</li>
<li><strong>Root directory</strong> — the top-level folder from which all others branch.</li>
<li><strong>Subfolder</strong> — a folder inside another folder.</li>
<li><strong>Version control</strong> — keeping track of different drafts or editions of a file.</li>
</ul>

<h2>Summary</h2>
<p>Digital files need the same discipline as paper files. A clear folder structure, consistent naming and regular backup protect your work from chaos, power cuts and hardware failure. Start with three main folders, apply the date-first naming rule, and back up your most critical files every week. These habits cost nothing but save hours of frustration.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/organize-files-folders/">Microsoft Learn — Organise Files and Folders</a></li>
<li><a href="https://support.google.com/drive/answer/2424384">Google Drive Help — Create and Manage Folders</a></li>
<li><a href="https://www.libreoffice.org/get-help/">LibreOffice — Free Office Suite Help</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.2 Using Spreadsheets for Record Registers',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>After this lesson you will be able to build a simple but powerful record register in Microsoft Excel or LibreOffice Calc, use basic formulas to calculate totals and averages, and apply data-validation rules that prevent common typing errors.</p>

<h2>Why Spreadsheets for Registers?</h2>
<p>A spreadsheet register turns a static list into a living tool. Unlike a paper book, a spreadsheet can add up columns automatically, highlight overdue items in red, sort by any column, and print neat reports for managers or auditors. For a small Zambian business that cannot afford specialised record-management software, Excel or LibreOffice Calc is the perfect bridge between paper and expensive databases.</p>

<h2>Designing a Simple Register</h2>
<p>Let us build a customer payment register for a hardware shop. The columns should be:</p>
<table>
<tr><th>Date</th><th>Invoice No</th><th>Customer</th><th>Amount (ZMW)</th><th>Payment Method</th><th>Receipt No</th><th>Balance (ZMW)</th></tr>
<tr><td>05/06/2026</td><td>INV-1024</td><td>Chileshe, M.</td><td>850.00</td><td>Cash</td><td>R-401</td><td>0.00</td></tr>
<tr><td>06/06/2026</td><td>INV-1025</td><td>Mwale, J.</td><td>1200.00</td><td>MTN MoMo</td><td>R-402</td><td>200.00</td></tr>
</table>

<h2>Basic Formulas</h2>
<p>Learn these three formulas and you can handle most small-business registers:</p>
<ul>
<li><strong>SUM</strong> — <code>=SUM(D2:D50)</code> adds all values in the Amount column.</li>
<li><strong>AVERAGE</strong> — <code>=AVERAGE(D2:D50)</code> finds the typical payment size.</li>
<li><strong>IF</strong> — <code>=IF(G2=0,"Paid","Owing")</code> labels each row as paid or owing automatically.</li>
</ul>

<h2>Data Validation</h2>
<p>Data validation stops people from entering nonsense. For example:</p>
<ul>
<li>Set the Date column to accept only proper dates.</li>
<li>Set the Payment Method column to a dropdown list: Cash, MTN MoMo, Airtel Money, Bank Transfer, Cheque.</li>
<li>Set the Amount column to accept only numbers greater than zero.</li>
</ul>
<p>In Excel: select the column → Data → Data Validation. In LibreOffice Calc: Data → Validity.</p>

<h2>Worked Example: A Stock Register</h2>
<p>Mrs Banda keeps stock for her grocery shop. She builds a spreadsheet with these columns: Date, Item, Quantity In, Quantity Out, Balance, Unit Price (ZMW), Total Value (ZMW).</p>
<p>She enters the formula <code>=D2-E2</code> in the Balance column so stock levels update automatically. She uses <code>=F2*G2</code> in the Total Value column. At month end she selects the Total Value column and reads the SUM at the bottom of the screen: ZMW 14,250. She now knows the value of stock on hand without counting every item manually.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open Excel or LibreOffice Calc.</li>
<li>Create a register for one area of your life: household expenses, school marks, or church donations.</li>
<li>Use at least one SUM formula and one IF formula.</li>
<li>Add data validation to one column (for example, a dropdown list of categories).</li>
<li>Save the file with a proper date-first name and make a copy in another folder.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Register</strong> — a formal list of transactions, names or events kept in order.</li>
<li><strong>Formula</strong> — a calculation instruction entered into a spreadsheet cell.</li>
<li><strong>Data validation</strong> — a rule that restricts what can be typed into a cell.</li>
<li><strong>Dropdown list</strong> — a menu of permitted choices inside a spreadsheet cell.</li>
<li><strong>Cell</strong> — a single box in a spreadsheet where data or a formula is entered.</li>
</ul>

<h2>Summary</h2>
<p>Spreadsheets are the most powerful free tool available for record management in small Zambian businesses. A well-designed register with formulas and data validation reduces errors, speeds up reporting and impresses auditors. You do not need advanced training; three formulas—SUM, AVERAGE and IF—plus one validation rule, will transform how you keep records.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.libreoffice.org/get-help/">LibreOffice — Calc Help and Tutorials</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/get-started-with-office/">Microsoft Learn — Excel Basics</a></li>
<li><a href="https://support.google.com/docs/answer/46977">Google Sheets Help — Formulas</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.3 Cloud Storage and Backup Basics',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what cloud storage is, set up Google Drive for basic file backup, describe the 3-2-1 backup rule in plain language, and identify the risks and benefits of storing Zambian business records online.</p>

<h2>What Is Cloud Storage?</h2>
<p>Cloud storage means saving your files on someone else’s computers—called servers—over the internet, instead of only on your own laptop or phone. Companies such as Google, Microsoft and Dropbox run these servers in secure buildings around the world. When you save a file to Google Drive, copies exist on multiple servers so that if one breaks, your file is still safe.</p>
<p>For Zambian users, cloud storage solves three big problems:</p>
<ul>
<li><strong>Load-shedding and power surges</strong> — if your computer dies during a blackout, cloud copies survive.</li>
<li><strong>Theft or fire</strong> — a stolen laptop or a burned office does not destroy files saved online.</li>
<li><strong>Access from anywhere</strong> — you can open a document on a phone, tablet or college computer without carrying a flash drive.</li>
</ul>

<h2>Getting Started with Google Drive</h2>
<p>Google Drive gives 15 GB of free storage and works on any phone or computer with internet. To set it up:</p>
<ol>
<li>Create a free Google account at gmail.com using your existing email or a new Gmail address.</li>
<li>Go to drive.google.com and sign in.</li>
<li>Create folders that match your computer folder structure (for example, Finance_2026, Customers, Tax_ZRA).</li>
<li>Upload files by dragging them into the browser window or using the "New" button.</li>
<li>Install the Google Drive desktop app if you want files to sync automatically from your computer.</li>
</ol>

<h2>The 3-2-1 Backup Rule</h2>
<p>Professional record keepers follow a simple rule: keep <strong>three</strong> copies of important data, on <strong>two</strong> different types of storage, with <strong>one</strong> copy stored off-site. For a small business in Kalomo this might mean:</p>
<ul>
<li>Copy 1 — the original on your office computer.</li>
<li>Copy 2 — a weekly backup on an external hard drive or flash drive kept in a separate room.</li>
<li>Copy 3 — a copy in Google Drive or another cloud service.</li>
</ul>
<p>This sounds like overkill until a power surge destroys your computer and the flash drive you left plugged into it. Then the cloud copy becomes a lifesaver.</p>

<h2>Risks to Consider</h2>
<p>Cloud storage is not perfect. You need an internet connection to upload and download large files, which can be slow or expensive on mobile data. Some free accounts have storage limits; 15 GB fills quickly if you back up photos and videos. Finally, you must protect your Google password as carefully as your bank PIN, because anyone with the password can see, change or delete your files.</p>

<h2>Worked Example: A Backup Routine</h2>
<p>Mr Zulu runs a transport business. Every Friday at 4 p.m. he does the following:</p>
<ol>
<li>Copies his Excel cash book from the office computer to a flash drive labelled "Backup."</li>
<li>Unplugs the flash drive and stores it in his locked desk drawer.</li>
<li>Opens Google Drive on his phone and uploads the same Excel file to a folder named "Transport_Backups_2026."</li>
<li>Checks that the file appears in Google Drive and opens correctly.</li>
</ol>
<p>The whole process takes five minutes. When his laptop was stolen from the office in March, he bought a new computer, signed into Google Drive, and had every record restored before closing time.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a free Google account if you do not already have one.</li>
<li>Create three folders in Google Drive that match your most important record categories.</li>
<li>Upload five important files and check that they open on your phone.</li>
<li>Write a simple weekly backup schedule on paper and stick it to your computer monitor.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Cloud storage</strong> — saving files on remote internet servers instead of local devices.</li>
<li><strong>Sync</strong> — automatically copying files between a computer and cloud storage so both stay up to date.</li>
<li><strong>3-2-1 backup rule</strong> — three copies, two media types, one off-site.</li>
<li><strong>Off-site</strong> — stored in a different physical location from the original.</li>
<li><strong>Server</strong> — a powerful computer that stores and shares data over a network.</li>
</ul>

<h2>Summary</h2>
<p>Cloud storage turns the internet into a safety deposit box for your records. For Zambian businesses facing power cuts, theft and hardware failure, a simple routine of weekly backups to Google Drive plus a local flash drive follows the 3-2-1 rule and protects years of hard work. The setup takes thirty minutes; the peace of mind lasts forever.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/drive/answer/2424384">Google Drive Help — Getting Started</a></li>
<li><a href="https://learn.microsoft.com/en-us/onedrive/">Microsoft OneDrive Help</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => 'Module 3 Quiz: Electronic Records Management',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Complete the quiz to demonstrate your understanding of digital file organisation, spreadsheet registers and cloud backup.</p>',
            ],
        ];
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Electronic Records Management',
            'description' => 'Test your knowledge of folder structures, spreadsheet registers and cloud storage backup.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which file-naming convention is recommended to keep documents sorted by date?',
                    'explanation' => 'Starting with YYYYMMDD ensures files sort chronologically in any folder view.',
                    'options' => [
                        ['text' => 'Document_final_v2.docx', 'is_correct' => false],
                        ['text' => '20260605_Invoice_Chanda_v01.pdf', 'is_correct' => true],
                        ['text' => 'Invoice_June.docx', 'is_correct' => false],
                        ['text' => 'New Document (2).docx', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In a spreadsheet, which formula adds all values in cells D2 through D50?',
                    'explanation' => 'SUM is the formula that totals a range of numbers.',
                    'options' => [
                        ['text' => '=ADD(D2:D50)', 'is_correct' => false],
                        ['text' => '=SUM(D2:D50)', 'is_correct' => true],
                        ['text' => '=TOTAL(D2:D50)', 'is_correct' => false],
                        ['text' => '=COUNT(D2:D50)', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does the 3-2-1 backup rule recommend?',
                    'explanation' => 'The rule says keep three copies, on two different media, with one off-site.',
                    'options' => [
                        ['text' => 'Three passwords, two computers, one flash drive', 'is_correct' => false],
                        ['text' => 'Three copies, two media types, one off-site', 'is_correct' => true],
                        ['text' => 'Three folders, two files, one email', 'is_correct' => false],
                        ['text' => 'Three users, two admins, one guest', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why should spaces be avoided in file names?',
                    'explanation' => 'Some systems and web uploads break or replace spaces, causing broken links.',
                    'options' => [
                        ['text' => 'They make files larger', 'is_correct' => false],
                        ['text' => 'Some systems break when spaces are present', 'is_correct' => true],
                        ['text' => 'They slow down the computer', 'is_correct' => false],
                        ['text' => 'They are illegal in all countries', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which tool prevents users from typing invalid data into a spreadsheet cell?',
                    'explanation' => 'Data validation restricts input to permitted values, dates or formats.',
                    'options' => [
                        ['text' => 'SUM formula', 'is_correct' => false],
                        ['text' => 'Data validation', 'is_correct' => true],
                        ['text' => 'Print preview', 'is_correct' => false],
                        ['text' => 'Spell check', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main advantage of cloud storage for a Zambian office?',
                    'explanation' => 'Cloud copies survive local disasters such as theft, fire or power surges.',
                    'options' => [
                        ['text' => 'It is always free forever', 'is_correct' => false],
                        ['text' => 'Files survive theft, fire and hardware failure', 'is_correct' => true],
                        ['text' => 'It does not need internet access', 'is_correct' => false],
                        ['text' => 'It prints documents automatically', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A flash drive left plugged into the office computer counts as an off-site backup.',
                    'explanation' => 'Off-site means stored in a different physical location, not just a different device in the same room.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Leading numbers in folder names help control the sort order in Windows File Explorer.',
                    'explanation' => 'Numbers sort before letters, so 01_Finance appears before Accounts.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What one word describes a menu of permitted choices inside a spreadsheet cell?',
                    'explanation' => 'A dropdown list restricts input to predefined options.',
                    'correct_answer' => 'Dropdown',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What free Google service gives 15 GB of cloud storage for files and backups?',
                    'explanation' => 'Google Drive is the free cloud storage service from Google.',
                    'correct_answer' => 'Google Drive',
                ],
            ],
        ];
    }
