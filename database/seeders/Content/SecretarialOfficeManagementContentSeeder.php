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

class SecretarialOfficeManagementContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Secretarial & Office Management')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Secretarial & Office Management" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in Secretarial & Office Management already has modules. Skipping content seed.');
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
                'title' => 'Module 1: The Role of a Secretary and Office Professional',
                'description' => 'Understand the modern secretary\'s role in Zambian offices, the daily tasks involved, and the personal traits needed to succeed in ministries, NGOs, banks, schools and clinics.',
            ],
            [
                'title' => 'Module 2: Communication Skills — Memos, Letters, Minutes and Email Etiquette',
                'description' => 'Learn how to write clear memos, official letters, agendas and minutes, and how to handle email and telephone communication in a professional Zambian workplace.',
            ],
            [
                'title' => 'Module 3: Filing, Records Management and Office Organisation',
                'description' => 'Set up reliable filing systems, classify records, follow retention rules, and keep the office layout, stationery and inventory under control.',
            ],
            [
                'title' => 'Module 4: Reception, Telephone Skills and Customer Service',
                'description' => 'Manage the reception area, answer and transfer telephone calls politely, and handle visitors and complaints in a way that builds trust.',
            ],
            [
                'title' => 'Module 5: Computer Applications for Office Work',
                'description' => 'Use Microsoft Word, Excel and PowerPoint to produce professional documents, manage records and deliver clear presentations in an office setting.',
            ],
            [
                'title' => 'Module 6: Diary Management, Travel Arrangements and Confidentiality',
                'description' => 'Manage appointments and diaries, plan local and international travel, and protect confidential information in line with Zambian workplace expectations.',
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
                'title' => 'Assignment 1: Draft a Complete Set of Office Documents',
                'description' => 'Produce a memo, a meeting agenda and minutes for a real or imaginary Zambian organisation.',
                'instructions' => '<ol><li>Choose one Zambian organisation setting: a ministry department, an NGO field office, a bank branch, a school office or a clinic administration desk.</li><li>Write one internal memo on a realistic topic, for example a change in filing deadlines, a new visitor-sign-in rule, or a staff meeting reminder. Use correct memo format: To, From, Date, Subject, body and action required.</li><li>Write a one-page agenda for a staff meeting with at least five items, including time allocation and the person responsible for each item.</li><li>Write the minutes for that meeting. Include attendance, apologies, key decisions, action items with owners and deadlines, and the date of the next meeting.</li><li>Save your documents as one Word document or PDF. Name the file: OfficeDocs_YourName.</li></ol>',
                'due_weeks' => 2,
            ],
            [
                'title' => 'Assignment 2: Create a Filing-System Plan and Reception Manual',
                'description' => 'Design a practical filing system and a short reception-desk procedure manual for a small Zambian office.',
                'instructions' => '<ol><li>Choose a small office such as a community school head office, a local clinic, a youth NGO or a microfinance branch.</li><li>Draw or describe a filing system plan. Explain whether you will use alphabetical, numerical or subject filing, and give five example file labels.</li><li>Write a short records-retention rule, for example how long to keep payment vouchers, meeting minutes and staff leave forms before archiving or destroying them.</li><li>Write a one-page reception-desk procedure manual. Include how to greet visitors, how to handle telephone calls, how to take and pass on messages, and what to do when the manager is not available.</li><li>Save your plan and manual as one Word document or PDF. Name the file: FilingReception_YourName.</li></ol>',
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
                'title' => 'Lesson 1.1: What Secretarial Work Looks Like Today',
                'duration_minutes' => 60,
                'content' => $this->lesson1_1(),
            ],
            [
                'title' => 'Lesson 1.2: Professional Traits of a Secretary',
                'duration_minutes' => 60,
                'content' => $this->lesson1_2(),
            ],
            [
                'title' => 'Lesson 1.3: Office Ethics, Confidentiality and Personal Presentation',
                'duration_minutes' => 60,
                'content' => $this->lesson1_3(),
            ],
            [
                'title' => 'Module 1 Quiz: The Role of a Secretary and Office Professional',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Complete this quiz to check your understanding of the modern secretary\'s role, professional traits and office ethics. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson1_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to describe what a secretary or office professional does in a modern Zambian workplace, identify the different organisations that employ secretaries, and explain why clear document flow matters for any office.</p>

<figure><img class="lesson-diagram" src="/assets/diagrams/secretarial-office-management/document-flow-office.svg" alt="Document flow showing create, review, approve, distribute and file stages."><figcaption>Figure: Document flow in a Zambian office — every paper or email follows a clear path.</figcaption></figure>

<h2>What Is a Secretary Today?</h2>
<p>A secretary is no longer only a person who types letters and answers the phone. In today\'s offices, a secretary is an organiser, a communicator, a record keeper and often the first point of contact for visitors and callers. The title may be Receptionist, Office Assistant, Administrative Assistant, Personal Assistant or Executive Secretary, but the core work is the same: keep the office running smoothly so that managers and staff can focus on their main jobs.</p>
<p>In a Zambian context, secretaries work in government ministries in Lusaka, district council offices, NGO coordination desks, bank branches, school administration blocks, clinics and private companies. A secretary at the Ministry of Education might handle letters from schools, schedule meetings for the provincial education officer and maintain confidential staff files. A secretary at a bank in Kitwe might process customer correspondence, prepare meeting rooms and control office stationery. A secretary at a rural clinic might book patient appointments, keep stock cards for medicines and take minutes at clinic-in-charge meetings.</p>

<h2>Common Daily Tasks</h2>
<p>Although every office is different, most secretaries perform a similar set of tasks each day. These include receiving and directing visitors, answering telephone calls, handling incoming and outgoing mail, scheduling appointments, preparing meeting documents, taking minutes, filing records, ordering stationery, making travel arrangements, and using computers to produce letters, reports and spreadsheets.</p>
<p>The order of these tasks changes constantly. A good secretary knows how to prioritise. If the District Commissioner is expecting an urgent letter and a visitor is waiting at reception, the secretary must decide which task needs attention first. This ability to manage time and stay calm under pressure is one of the most valuable skills in the job.</p>

<h2>Where the Secretary Fits in the Office</h2>
<p>The secretary usually sits between the manager and the rest of the staff, customers and outside organisations. Information flows through the secretary\'s desk. Letters arrive, are opened, sorted and passed to the right person. Decisions made in meetings are recorded by the secretary and turned into action lists. Appointments are booked through the secretary\'s diary. Because of this central position, the secretary must be reliable, discreet and highly organised.</p>
<p>Think of the secretary as the office engine. When the engine is well maintained, the whole vehicle moves. When it breaks down, delays and confusion follow everywhere.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Visit or imagine a small office in your town, such as a clinic, bank or school.</li>
<li>List ten tasks you think a secretary does there in one day.</li>
<li>Circle the three tasks that you believe are most important for keeping the office running.</li>
<li>Write one sentence explaining why the secretary is the "information hub" of that office.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Secretary</strong>: An office professional who handles correspondence, records, appointments and communication.</li>
<li><strong>Administrative assistant</strong>: A staff member who supports managers by organising tasks, files and schedules.</li>
<li><strong>Information hub</strong>: The central point through which messages, documents and instructions pass.</li>
<li><strong>Prioritise</strong>: To decide which tasks are most urgent and important.</li>
<li><strong>Discretion</strong>: The ability to keep information private and use good judgement.</li>
</ul>

<h2>Summary</h2>
<p>Secretarial work in Zambia is diverse and essential. Secretaries keep information moving, support managers, serve visitors and callers, and maintain the records that allow organisations to function legally and efficiently.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/teamwork-skills/" target="_blank" rel="noopener">Microsoft Learn — Teamwork and Office Skills</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal" target="_blank" rel="noopener">Khan Academy — Professional Skills Conversations</a></li>
</ul>
HTML;
    }

    private function lesson1_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to list the personal and professional traits that make a secretary successful, and you will understand how punctuality, appearance and attitude affect trust in a Zambian workplace.</p>

<h2>More Than Skills</h2>
<p>Having typing speed, computer knowledge and filing skills is important, but employers often say that attitude and character matter just as much. A secretary who arrives late, gossips about colleagues or ignores visitors will create problems even if they can type 80 words per minute. On the other hand, a secretary who is calm, friendly and dependable becomes an asset that the whole office relies on.</p>
<p>In Zambia, relationships and respect are highly valued. A secretary must greet people politely, use appropriate titles such as Mr, Mrs, Dr or Honourable, and show patience even when callers are frustrated. The way a secretary speaks to a farmer waiting at the reception desk should be as respectful as the way they speak to a senior government official.</p>

<h2>Seven Traits Employers Look For</h2>
<ul>
<li><strong>Reliability</strong>: Be at work on time, meet deadlines and keep promises. If you say a letter will be ready by 10 a.m., it must be ready.</li>
<li><strong>Accuracy</strong>: Check names, dates, amounts and spelling. A wrong figure in a payment voucher can cause serious problems.</li>
<li><strong>Discretion</strong>: Do not discuss office matters outside the office. Staff salaries, medical records and disciplinary issues are private.</li>
<li><strong>Initiative</strong>: Notice what needs to be done before being told. If the printer is out of paper, replace it. If a visitor looks lost, help them.</li>
<li><strong>Flexibility</strong>: Be willing to handle unexpected tasks. A secretary may be asked to cover reception, photocopy documents or assist at an event.</li>
<li><strong>Communication</strong>: Speak and write clearly. Listen carefully and confirm instructions when necessary.</li>
<li><strong>Neatness</strong>: Keep your desk, files and appearance tidy. A messy desk suggests a messy mind.</li>
</ul>

<h2>Personal Presentation</h2>
<p>Dress codes vary. A lawyer\'s office may expect formal business attire, while a community NGO may accept smart casual clothing. Whatever the code, cleanliness and neatness are non-negotiable. Strong perfume or cologne, chipped nail polish, wrinkled clothes or unpolished shoes can damage the professional image of the whole office. In a Zambian context, modest and respectful dress is especially important when dealing with government offices, traditional leaders and international partners.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Look at the seven traits above. Rate yourself from 1 to 5 on each one.</li>
<li>Choose the two traits you need to improve most and write two practical steps for each.</li>
<li>Ask a friend or family member which trait they think is your strongest and why.</li>
<li>Plan what you would wear for your first day as a secretary in a bank branch.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Reliability</strong>: Consistently doing what is expected, on time and to a good standard.</li>
<li><strong>Discretion</strong>: Careful and sensible use of private or sensitive information.</li>
<li><strong>Initiative</strong>: The ability to act independently and solve problems without waiting for instructions.</li>
<li><strong>Professional image</strong>: The impression you create through your behaviour, dress and communication.</li>
<li><strong>Etiquette</strong>: The accepted rules of polite behaviour in a workplace or society.</li>
</ul>

<h2>Summary</h2>
<p>Professional secretaries are trusted because of their character, not just their skills. Punctuality, accuracy, discretion, initiative and respectful communication make a secretary valuable in any Zambian organisation.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/develop-your-soft-skills/" target="_blank" rel="noopener">Microsoft Learn — Develop Your Soft Skills</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal" target="_blank" rel="noopener">Khan Academy — Career Conversations</a></li>
</ul>
HTML;
    }

    private function lesson1_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will understand why office ethics matter, how to handle confidential information, and how personal presentation and behaviour affect your reputation as a secretary in Zambia.</p>

<h2>What Are Office Ethics?</h2>
<p>Office ethics are the standards of right and wrong behaviour at work. They guide how we treat colleagues, handle money, use office property, keep secrets and serve the public. For a secretary, ethics are especially important because the role gives access to private information, money, signatures and keys.</p>
<p>A simple example shows why this matters. Imagine a secretary at a district education office sees a list of teachers who will be transferred next month. If the secretary tells a friend before the official announcement, the friend may spread the news, cause anxiety among staff and damage trust in the office. Even if the secretary meant no harm, sharing the information was unethical.</p>

<h2>Confidentiality in Practice</h2>
<p>Confidential information includes staff records, medical files, salary details, examination results, minutes of disciplinary meetings, customer account details and any document marked "confidential." A secretary must keep such information secure and share it only with authorised people.</p>
<p>Practical rules include:</p>
<ul>
<li>Do not leave confidential papers open on your desk when you leave the room.</li>
<li>Do not discuss office matters in public places such as buses, markets or restaurants.</li>
<li>Lock filing cabinets and drawers that contain sensitive files.</li>
<li>Password-protect computer files and do not share your login details.</li>
<li>Dispose of confidential waste by shredding or burning, not by throwing it in a public bin.</li>
</ul>

<h2>Avoiding Conflicts of Interest</h2>
<p>A conflict of interest happens when your personal interests interfere with your duties. If a secretary is asked to recommend a supplier for office stationery and the secretary\'s brother owns a stationery shop, the secretary should declare the relationship and let someone else make the decision. Transparency protects both the secretary and the organisation.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write down three examples of confidential information in an office you know.</li>
<li>List five practical ways to protect that information.</li>
<li>Describe a situation where a secretary might face a conflict of interest and how to handle it.</li>
<li>Draft a short personal code of conduct that you would follow as a secretary.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Ethics</strong>: Principles that guide right and wrong behaviour.</li>
<li><strong>Confidentiality</strong>: Keeping private information secret and secure.</li>
<li><strong>Conflict of interest</strong>: A situation where personal interests could unfairly influence work decisions.</li>
<li><strong>Transparency</strong>: Being open and honest about decisions and relationships.</li>
<li><strong>Disposal</strong>: Getting rid of records or waste in a safe and approved way.</li>
</ul>

<h2>Summary</h2>
<p>Ethics and confidentiality are the foundation of trust in secretarial work. A secretary who protects information, avoids conflicts of interest and behaves professionally earns respect and protects the reputation of the organisation.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/develop-your-soft-skills/" target="_blank" rel="noopener">Microsoft Learn — Work Ethics and Professionalism</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal" target="_blank" rel="noopener">Khan Academy — Integrity at Work</a></li>
</ul>
HTML;
    }

    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: The Role of a Secretary and Office Professional',
            'description' => 'Test your understanding of the secretary\'s role, professional traits and office ethics.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following best describes the modern secretary\'s role?',
                    'explanation' => 'A modern secretary organises, communicates, keeps records and supports the whole office, not just types letters.',
                    'options' => [
                        ['text' => 'Only typing letters and making tea', 'is_correct' => false],
                        ['text' => 'The organiser, communicator and record keeper of the office', 'is_correct' => true],
                        ['text' => 'The highest paid manager in the company', 'is_correct' => false],
                        ['text' => 'The person who cleans the office', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is the secretary often called the "information hub" of an office?',
                    'explanation' => 'Information such as letters, calls, appointments and meeting decisions usually pass through the secretary.',
                    'options' => [
                        ['text' => 'Because the secretary is the oldest employee', 'is_correct' => false],
                        ['text' => 'Because the secretary controls the internet', 'is_correct' => false],
                        ['text' => 'Because information flows through the secretary to staff and visitors', 'is_correct' => true],
                        ['text' => 'Because the secretary earns the most money', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which personal trait means doing what is expected on time and to a good standard?',
                    'explanation' => 'Reliability means others can depend on you to complete tasks correctly and on time.',
                    'options' => [
                        ['text' => 'Initiative', 'is_correct' => false],
                        ['text' => 'Reliability', 'is_correct' => true],
                        ['text' => 'Flexibility', 'is_correct' => false],
                        ['text' => 'Discretion', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should a secretary do with a document marked "confidential" left open on a desk?',
                    'explanation' => 'Confidential papers should be stored securely and not left where unauthorised people can see them.',
                    'options' => [
                        ['text' => 'Photocopy it for personal records', 'is_correct' => false],
                        ['text' => 'Leave it for the owner to collect later', 'is_correct' => false],
                        ['text' => 'Store it safely or hand it to the responsible person', 'is_correct' => true],
                        ['text' => 'Show it to a colleague for advice', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A secretary may discuss staff salary details with a friend as long as the friend promises not to tell anyone.',
                    'explanation' => 'Salary information is confidential and must never be shared with unauthorised people, even if they promise secrecy.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for a situation where personal interests could unfairly influence work decisions? (Three words)',
                    'explanation' => 'A conflict of interest occurs when personal interests interfere with professional duties.',
                    'correct_answer' => 'Conflict of interest',
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
                'title' => 'Lesson 2.1: Writing Memos and Official Letters',
                'duration_minutes' => 75,
                'content' => $this->lesson2_1(),
            ],
            [
                'title' => 'Lesson 2.2: Agendas, Minutes and the Meeting Cycle',
                'duration_minutes' => 75,
                'content' => $this->lesson2_2(),
            ],
            [
                'title' => 'Lesson 2.3: Email Etiquette for Zambian Workplaces',
                'duration_minutes' => 60,
                'content' => $this->lesson2_3(),
            ],
            [
                'title' => 'Lesson 2.4: Telephone Skills and Taking Messages',
                'duration_minutes' => 45,
                'content' => $this->lesson2_4(),
            ],
            [
                'title' => 'Module 2 Quiz: Communication Skills',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your knowledge of memos, official letters, agendas, minutes, email and telephone skills. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson2_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to write a clear internal memo and a professional official letter using correct format, tone and structure for a Zambian workplace.</p>

<h2>What Is a Memo?</h2>
<p>A memo, short for memorandum, is a short internal message used within an organisation. It is not sent to people outside the organisation. Memos are used to inform staff, give instructions, announce changes, request action or remind people about deadlines. Because they are internal, memos can be less formal than letters, but they must still be polite, clear and accurate.</p>
<p>A typical memo has the following parts:</p>
<ul>
<li><strong>To:</strong> The person or department receiving the memo.</li>
<li><strong>From:</strong> The person writing the memo.</li>
<li><strong>Date:</strong> The day the memo is written.</li>
<li><strong>Subject:</strong> A short description of the topic.</li>
<li><strong>Body:</strong> The message, often with a clear purpose, details and action required.</li>
</ul>

<h2>Memo Example</h2>
<blockquote>
<p><strong>To:</strong> All Staff<br><strong>From:</strong> M. Banda, Office Secretary<br><strong>Date:</strong> 12 June 2026<br><strong>Subject:</strong> New Visitor Sign-in Procedure</p>
<p>Starting Monday, 15 June 2026, all visitors must sign in at the reception desk before entering the main office. This change is necessary for security and fire safety.</p>
<p>Please remind any visitors you are expecting to report to reception first. The sign-in book is on the desk near the entrance.</p>
<p><strong>Action required:</strong> All staff to comply by 15 June 2026.</p>
</blockquote>

<h2>Official Letters</h2>
<p>An official letter is sent to people outside the organisation, such as government departments, suppliers, banks, schools or customers. It must be more formal than a memo and follow a standard layout. In Zambia, many organisations still use printed letterheads that include the name, address, telephone number and email of the organisation.</p>
<p>A block-style letter uses these parts:</p>
<ol>
<li>Sender\'s address or letterhead at the top.</li>
<li>Date.</li>
<li>Recipient\'s name, title and address.</li>
<li>Salutation, such as "Dear Sir/Madam" or "Dear Mr. Phiri".</li>
<li>Subject line.</li>
<li>Body paragraphs.</li>
<li>Complimentary close, such as "Yours faithfully" or "Yours sincerely".</li>
<li>Signature and typed name.</li>
<li>Enclosure or copy notation if needed.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Write a memo to staff about a fire drill that will take place next Friday at 10 a.m.</li>
<li>Write an official letter from a school head teacher to the District Education Board Secretary requesting a new photocopier.</li>
<li>Check your drafts for correct format, spelling and polite tone.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Memo</strong>: A short written message used for internal communication.</li>
<li><strong>Official letter</strong>: A formal letter sent to people outside an organisation.</li>
<li><strong>Salutation</strong>: The greeting at the beginning of a letter.</li>
<li><strong>Complimentary close</strong>: The polite ending of a letter.</li>
<li><strong>Letterhead</strong>: Printed stationery showing an organisation\'s name and contact details.</li>
</ul>

<h2>Summary</h2>
<p>Memos and official letters are basic tools of office communication. Use memos for internal messages and letters for external correspondence. Always use correct format, clear language and a respectful tone.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/communicate-effectively/" target="_blank" rel="noopener">Microsoft Learn — Communicate Effectively at Work</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal" target="_blank" rel="noopener">Khan Academy — Workplace Communication</a></li>
</ul>
HTML;
    }

    private function lesson2_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to prepare a meeting agenda, take accurate minutes, and follow the full meeting cycle from notice to follow-up in a Zambian office.</p>

<figure><img class="lesson-diagram" src="/assets/diagrams/secretarial-office-management/meeting-cycle.svg" alt="Meeting cycle showing notice, agenda, meeting and minutes, action list and follow-up."><figcaption>Figure: Meeting cycle — notice, agenda, minutes, actions and follow-up form a continuous loop.</figcaption></figure>

<h2>The Purpose of an Agenda</h2>
<p>An agenda is a list of topics to be discussed at a meeting. It helps people prepare, keeps the meeting focused and makes it easier to write minutes afterwards. The secretary usually prepares the agenda in consultation with the chairperson. A good agenda includes the date, time, venue, attendees, apologies for absence, and each item with a time allocation and the person responsible.</p>
<p>Example agenda for a school staff meeting:</p>
<ol>
<li>Welcome and apologies — 5 minutes — Chairperson</li>
<li>Minutes of previous meeting — 5 minutes — Secretary</li>
<li>Matters arising — 10 minutes — All</li>
<li>Examination results review — 15 minutes — Head of Academics</li>
<li>Staff duty roster — 10 minutes — Deputy Head</li>
<li>Any other business — 10 minutes — All</li>
<li>Date of next meeting — 5 minutes — Chairperson</li>
</ol>

<h2>Taking Minutes</h2>
<p>Minutes are the official written record of what happened in a meeting. They are not a word-for-word transcript. They record who was present, the decisions made and the actions agreed. Good minutes answer three questions for each item: What was decided? Who will do it? By when?</p>
<p>A standard set of minutes includes:</p>
<ul>
<li>Name of the organisation and the meeting.</li>
<li>Date, time and place.</li>
<li>Names of those present and apologies for absence.</li>
<li>A record of each agenda item discussed.</li>
<li>Decisions made.</li>
<li>Action items with responsible person and deadline.</li>
<li>Date, time and place of the next meeting.</li>
<li>Signature of the chairperson and secretary.</li>
</ul>

<h2>The Meeting Cycle</h2>
<p>Formal meetings follow a repeating cycle. First, a notice is sent to members. Then the agenda is distributed in advance. The meeting is held and minutes are taken. After the meeting, an action list is produced and shared. Finally, the secretary follows up to check that actions were completed before the next meeting begins the cycle again. When this cycle is respected, meetings become productive and decisions are implemented.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write a one-page agenda for a clinic staff meeting about drug stock management.</li>
<li>Write ten lines of sample minutes for the first three agenda items.</li>
<li>Create an action list with at least three tasks, responsible persons and deadlines.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Agenda</strong>: A list of items to be discussed at a meeting.</li>
<li><strong>Minutes</strong>: The official written record of a meeting.</li>
<li><strong>Notice</strong>: Advance information about a meeting sent to members.</li>
<li><strong>Action list</strong>: A list of tasks, responsible persons and deadlines agreed in a meeting.</li>
<li><strong>Matters arising</strong>: Items from previous minutes that need follow-up.</li>
</ul>

<h2>Summary</h2>
<p>Agendas keep meetings focused, minutes create accountability, and the meeting cycle ensures that decisions lead to action. Mastering these tools is essential for any secretary in Zambia.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/communicate-effectively/" target="_blank" rel="noopener">Microsoft Learn — Meeting Communication</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal" target="_blank" rel="noopener">Khan Academy — Collaboration Skills</a></li>
</ul>
HTML;
    }

    private function lesson2_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to write professional emails, use clear subject lines, follow email etiquette rules, and avoid common mistakes that damage your credibility at work.</p>

<h2>Why Email Etiquette Matters</h2>
<p>Email is one of the main ways that offices in Zambia communicate with government ministries, donors, suppliers and partners. A poorly written email can cause confusion, delay decisions and make the sender look unprofessional. A clear, polite email saves time and builds trust.</p>
<p>Many offices in Zambia have limited internet access and electricity. Emails should therefore be concise and well-structured so the reader can understand the message quickly, even on a small phone screen.</p>

<h2>Email Structure</h2>
<p>A professional email has these parts:</p>
<ul>
<li><strong>Subject line</strong>: Short and specific. Instead of "Meeting", write "Staff Meeting — 20 June 2026, 09:00 hrs".</li>
<li><strong>Salutation</strong>: "Dear Mr. Phiri," or "Dear Team,". Use first names only if you know the person well.</li>
<li><strong>Opening</strong>: One polite sentence before the main message.</li>
<li><strong>Body</strong>: Short paragraphs with the most important information first.</li>
<li><strong>Action</strong>: Clearly state what you want the reader to do.</li>
<li><strong>Closing</strong>: "Kind regards," or "Yours sincerely," followed by your full name, title and contact details.</li>
</ul>

<h2>Email Dos and Don'ts</h2>
<table>
<tr><th>Do</th><th>Don't</th></tr>
<tr><td>Use a clear subject line</td><td>Leave the subject line blank</td></tr>
<tr><td>Proofread before sending</td><td>Use slang or excessive abbreviations</td></tr>
<tr><td>Reply within one working day</td><td>Ignore emails or delay replies without reason</td></tr>
<tr><td>Use "Reply All" only when necessary</td><td>Use "Reply All" for personal messages</td></tr>
<tr><td>Keep attachments small</td><td>Send huge files without warning</td></tr>
</table>

<h2>Try It Yourself</h2>
<ol>
<li>Draft an email to a supplier requesting a quotation for 100 reams of A4 paper.</li>
<li>Rewrite a messy email by applying the structure above.</li>
<li>Write a brief email to your manager explaining why a report will be one day late.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Subject line</strong>: The title of an email that tells the reader what it is about.</li>
<li><strong>Salutation</strong>: The greeting at the start of an email.</li>
<li><strong>Attachment</strong>: A file sent with an email.</li>
<li><strong>Carbon copy (CC)</strong>: Sending a copy of an email to someone for information.</li>
<li><strong>Proofread</strong>: Checking writing for errors before sending.</li>
</ul>

<h2>Summary</h2>
<p>Professional emails use clear subject lines, polite language and a logical structure. Good email etiquette protects your reputation and helps your organisation communicate effectively.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/communicate-effectively/" target="_blank" rel="noopener">Microsoft Learn — Professional Email</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal" target="_blank" rel="noopener">Khan Academy — Communication Skills</a></li>
</ul>
HTML;
    }

    private function lesson2_4(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to answer telephone calls professionally, transfer calls correctly, take clear messages and handle difficult callers in a Zambian office.</p>

<h2>Answering the Telephone</h2>
<p>The telephone is often the first contact a person has with an organisation. A friendly, professional greeting creates a good impression. A poor greeting can damage the organisation\'s reputation before the caller even explains why they are calling.</p>
<p>A good telephone greeting includes:</p>
<ul>
<li>A greeting, such as "Good morning" or "Good afternoon".</li>
<li>The name of the organisation.</li>
<li>Your name or department.</li>
<li>An offer of help, such as "How may I help you?"</li>
</ul>
<p>Example: "Good morning, Kalomo District Health Office, this is Memory speaking. How may I help you?"</p>

<h2>Taking a Message</h2>
<p>When the person the caller wants is not available, take a clear message. A telephone message slip should include:</p>
<ul>
<li>Date and time of the call.</li>
<li>Caller\'s name and organisation.</li>
<li>Telephone number or email address.</li>
<li>The reason for the call.</li>
<li>Any action required.</li>
<li>Name of the person who took the message.</li>
</ul>
<p>Always repeat the caller\'s name and number back to make sure you wrote them correctly. Promise a return call time and make sure the message reaches the right person quickly.</p>

<h2>Handling Difficult Callers</h2>
<p>Some callers are angry, impatient or confused. Stay calm, listen without interrupting, apologise if the organisation made a mistake, and explain what you will do to help. Do not raise your voice or argue. If the caller becomes abusive, politely inform them that you will end the call if the disrespect continues, and then report to your supervisor.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Practise answering the phone with a classmate using the greeting above.</li>
<li>Role-play taking a message for a manager who is in a meeting.</li>
<li>Write a short script for handling an angry caller who has been waiting for a payment.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Telephone etiquette</strong>: The polite and professional way to use the telephone at work.</li>
<li><strong>Message slip</strong>: A small form used to record details of a telephone call.</li>
<li><strong>Transfer</strong>: To pass a telephone call to another person or extension.</li>
<li><strong>Hold</strong>: To pause a call while the caller waits.</li>
<li><strong>Follow-up</strong>: To check that a message or task has been dealt with.</li>
</ul>

<h2>Summary</h2>
<p>Telephone skills are essential for receptionists and secretaries. A professional greeting, clear messages and calm handling of difficult calls reflect well on the whole organisation.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/communicate-effectively/" target="_blank" rel="noopener">Microsoft Learn — Phone and Video Communication</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal" target="_blank" rel="noopener">Khan Academy — Customer Interaction Skills</a></li>
</ul>
HTML;
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Communication Skills',
            'description' => 'Assess your understanding of memos, official letters, agendas, minutes, email and telephone skills.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which type of document is used for internal communication within an organisation?',
                    'explanation' => 'A memo is a short internal message used to inform or instruct staff.',
                    'options' => [
                        ['text' => 'Official letter', 'is_correct' => false],
                        ['text' => 'Memo', 'is_correct' => true],
                        ['text' => 'Invoice', 'is_correct' => false],
                        ['text' => 'Bank statement', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should minutes of a meeting record?',
                    'explanation' => 'Minutes record decisions made and actions agreed, including who is responsible and deadlines.',
                    'options' => [
                        ['text' => 'Every word spoken during the meeting', 'is_correct' => false],
                        ['text' => 'Only the chairperson\'s speech', 'is_correct' => false],
                        ['text' => 'Decisions, action items and responsible persons', 'is_correct' => true],
                        ['text' => 'The lunch menu', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which part of an email tells the reader what the message is about before opening it?',
                    'explanation' => 'The subject line summarises the email topic and helps the reader prioritise.',
                    'options' => [
                        ['text' => 'The attachment', 'is_correct' => false],
                        ['text' => 'The subject line', 'is_correct' => true],
                        ['text' => 'The signature', 'is_correct' => false],
                        ['text' => 'The CC list', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'When taking a telephone message, which detail is most important to repeat back to the caller?',
                    'explanation' => 'The caller\'s name and contact number must be accurate so the message can be returned.',
                    'options' => [
                        ['text' => 'The weather forecast', 'is_correct' => false],
                        ['text' => 'The caller\'s name and telephone number', 'is_correct' => true],
                        ['text' => 'Your own lunch order', 'is_correct' => false],
                        ['text' => 'The caller\'s favourite colour', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'An official letter should be more formal than an internal memo.',
                    'explanation' => 'Official letters are sent outside the organisation and should follow formal conventions.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the list of topics to be discussed at a meeting called? (One word)',
                    'explanation' => 'An agenda lists the topics, time allocations and responsible persons for a meeting.',
                    'correct_answer' => 'Agenda',
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
                'title' => 'Lesson 3.1: Filing Systems and Records Classification',
                'duration_minutes' => 75,
                'content' => $this->lesson3_1(),
            ],
            [
                'title' => 'Lesson 3.2: Records Retention, Archiving and Disposal',
                'duration_minutes' => 60,
                'content' => $this->lesson3_2(),
            ],
            [
                'title' => 'Lesson 3.3: Office Layout, Stationery and Inventory Control',
                'duration_minutes' => 60,
                'content' => $this->lesson3_3(),
            ],
            [
                'title' => 'Module 3 Quiz: Filing, Records and Office Organisation',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your knowledge of filing systems, records retention, office layout and stationery control. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson3_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to set up a simple filing system, choose the right classification method for an office, and explain why good filing saves time and protects important records.</p>

<h2>Why Filing Matters</h2>
<p>Every office produces papers and digital records: letters, memos, reports, payment vouchers, meeting minutes and staff files. Without a filing system, these records become a pile on a desk or a jumble of folders on a computer. Important documents get lost, deadlines are missed and the organisation cannot prove what was decided or paid.</p>
<p>In a Zambian government office, good filing is especially important. Auditors may ask to see records from two years ago. A missing payment voucher can delay a project. A lost contract can create legal problems. Filing is not boring paperwork; it is the memory of the organisation.</p>

<h2>Common Filing Systems</h2>
<ul>
<li><strong>Alphabetical filing</strong>: Files are arranged by name, usually the surname of a person or the name of an organisation. Best for customer files, staff records and correspondence.</li>
<li><strong>Numerical filing</strong>: Each file is given a number. Best for large organisations where many people have similar names.</li>
<li><strong>Subject filing</strong>: Files are grouped by topic, such as "Finance", "Projects", "Staff" or "Meetings". Best for policy documents and project records.</li>
<li><strong>Chronological filing</strong>: Records are arranged by date. Often used inside another system, such as meeting minutes filed by year.</li>
</ul>

<h2>How to Start a Filing System</h2>
<ol>
<li>Decide which system matches the records you keep.</li>
<li>Create clear file labels using a consistent format.</li>
<li>Use sturdy folders or boxes and keep them in a safe, dry place.</li>
<li>Make an index or file list so anyone can find a file quickly.</li>
<li>Assign one person to be responsible for maintaining the system.</li>
<li>Review the system every few months and remove old or duplicate papers.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Collect ten sample papers from a school or office.</li>
<li>Decide whether alphabetical, numerical or subject filing suits them best.</li>
<li>Create file labels and arrange the papers in order.</li>
<li>Write a short index listing each file and where it is stored.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Filing system</strong>: A method of organising and storing records so they can be found quickly.</li>
<li><strong>Alphabetical filing</strong>: Arranging files by the letters of a name or title.</li>
<li><strong>Numerical filing</strong>: Arranging files by assigned numbers.</li>
<li><strong>Subject filing</strong>: Arranging files by topic or category.</li>
<li><strong>Index</strong>: A list that shows what files exist and where they are located.</li>
</ul>

<h2>Summary</h2>
<p>Good filing protects records, saves time and supports accountability. Choose the system that matches the records, label clearly, and keep an index so anyone in the office can find what they need.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/organize-data/" target="_blank" rel="noopener">Microsoft Learn — Organise Data and Files</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal" target="_blank" rel="noopener">Khan Academy — Office Organisation</a></li>
</ul>
HTML;
    }

    private function lesson3_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will understand how long different types of records should be kept, how to archive old files, and how to dispose of confidential records safely.</p>

<h2>What Is Records Retention?</h2>
<p>Records retention means deciding how long each type of record must be kept before it is archived or destroyed. Not every paper needs to stay in the active filing cabinet forever. Keeping everything forever wastes space and makes finding current records harder. Destroying records too early can break the law or remove evidence that the organisation needs.</p>
<p>In Zambia, organisations often follow a retention schedule based on the type of record and legal requirements. For example, financial records may need to be kept for seven years, employment records for the duration of employment plus several years, and minutes of board meetings permanently.</p>

<h2>Example Retention Schedule</h2>
<table>
<tr><th>Type of Record</th><th>Suggested Retention Period</th></tr>
<tr><td>Annual financial statements</td><td>Permanent</td></tr>
<tr><td>Payment vouchers</td><td>7 years</td></tr>
<tr><td>Staff leave forms</td><td>3 years after employment ends</td></tr>
<tr><td>Meeting minutes</td><td>Permanent or 10 years</td></tr>
<tr><td>General correspondence</td><td>2–5 years</td></tr>
</table>

<h2>Archiving and Disposal</h2>
<p>Records that are no longer needed every day but must still be kept should be moved to an archive. An archive can be a separate cupboard, a storeroom or a digital folder. Archive boxes should be clearly labelled with the contents and the destruction date.</p>
<p>When the retention period ends, dispose of records properly. Ordinary papers can be recycled or thrown away. Confidential papers should be shredded or burned. Digital files should be deleted securely so they cannot be recovered.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write a simple retention schedule for a small NGO office with at least five record types.</li>
<li>Describe how you would archive the minutes of meetings from three years ago.</li>
<li>Explain how you would safely dispose of old staff medical records.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Retention schedule</strong>: A plan that states how long each type of record must be kept.</li>
<li><strong>Archive</strong>: Storage for records that are no longer active but must be kept.</li>
<li><strong>Disposal</strong>: The process of getting rid of records when they are no longer needed.</li>
<li><strong>Confidential</strong>: Information that must be kept private.</li>
<li><strong>Shred</strong>: To cut paper into small pieces so it cannot be read.</li>
</ul>

<h2>Summary</h2>
<p>Records retention balances the need to keep evidence with the need to save space. Follow a retention schedule, archive carefully and dispose of confidential records securely.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/organize-data/" target="_blank" rel="noopener">Microsoft Learn — Records Management Basics</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal" target="_blank" rel="noopener">Khan Academy — Workplace Responsibility</a></li>
</ul>
HTML;
    }

    private function lesson3_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to organise an office layout for efficiency, control stationery supplies and keep a simple inventory that prevents shortages and waste.</p>

<h2>Office Layout</h2>
<p>A good office layout helps staff work quickly and comfortably. The reception area should be near the entrance so visitors can be greeted. The manager\'s office may need privacy but should still be accessible. The filing area should be dry, secure and close to the people who use the files most. Workstations need enough light, space for a computer and documents, and easy access to shared equipment such as printers and photocopiers.</p>
<p>In a Zambian office where load-shedding is common, it is wise to place equipment near backup power sources if available and to keep important equipment away from direct sunlight and dust. A clean, tidy office also makes a good impression on visitors and auditors.</p>

<h2>Stationery Control</h2>
<p>Stationery includes pens, paper, envelopes, files, staplers, printer cartridges and other supplies. Without control, stationery runs out at the worst time or disappears too quickly. A simple stationery control system includes:</p>
<ul>
<li>A storage cupboard kept locked.</li>
<li>A stock card for each item showing quantity received, issued and remaining.</li>
<li>A requisition form that staff sign when taking supplies.</li>
<li>A minimum stock level that triggers reordering.</li>
<li>A regular count, for example every month.</li>
</ul>

<h2>Inventory Records</h2>
<p>An inventory is a list of all movable assets in the office, such as computers, chairs, printers and projectors. Each item should have a label, a description, a serial number if available, the date bought and the person responsible. This helps prevent theft, supports insurance claims and makes audits easier.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Draw a simple floor plan for a small office with reception, two work stations, a filing area and a manager\'s office.</li>
<li>Create a stationery stock card for A4 paper.</li>
<li>List ten items you would include in an office inventory and the information you would record for each.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Office layout</strong>: The arrangement of furniture, equipment and work areas in an office.</li>
<li><strong>Stationery</strong>: Materials used in an office, such as paper, pens and envelopes.</li>
<li><strong>Stock card</strong>: A record showing the movement of an item in stock.</li>
<li><strong>Inventory</strong>: A complete list of items or assets owned by the organisation.</li>
<li><strong>Requisition</strong>: A formal request for supplies.</li>
</ul>

<h2>Summary</h2>
<p>A sensible office layout, controlled stationery and an accurate inventory reduce waste, save money and help staff work efficiently. These tasks are a normal part of secretarial and office management work.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/organize-data/" target="_blank" rel="noopener">Microsoft Learn — Organise Your Workspace</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal" target="_blank" rel="noopener">Khan Academy — Planning and Organisation</a></li>
</ul>
HTML;
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Filing, Records and Office Organisation',
            'description' => 'Assess your understanding of filing systems, retention, archiving and office inventory.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which filing system arranges records by topic or category?',
                    'explanation' => 'Subject filing groups records by topic, such as finance, staff or projects.',
                    'options' => [
                        ['text' => 'Alphabetical filing', 'is_correct' => false],
                        ['text' => 'Numerical filing', 'is_correct' => false],
                        ['text' => 'Subject filing', 'is_correct' => true],
                        ['text' => 'Chronological filing', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main purpose of a records retention schedule?',
                    'explanation' => 'A retention schedule states how long each type of record must be kept before archiving or disposal.',
                    'options' => [
                        ['text' => 'To decorate the filing room', 'is_correct' => false],
                        ['text' => 'To decide how long records should be kept', 'is_correct' => true],
                        ['text' => 'To increase the number of files', 'is_correct' => false],
                        ['text' => 'To hide mistakes from auditors', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which method is safest for disposing of confidential papers?',
                    'explanation' => 'Confidential papers should be shredded or burned so they cannot be read.',
                    'options' => [
                        ['text' => 'Throw them in the public bin', 'is_correct' => false],
                        ['text' => 'Leave them on the desk', 'is_correct' => false],
                        ['text' => 'Shred or burn them', 'is_correct' => true],
                        ['text' => 'Give them to a friend', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does a stock card show?',
                    'explanation' => 'A stock card tracks the quantity of an item received, issued and remaining.',
                    'options' => [
                        ['text' => 'Staff salaries', 'is_correct' => false],
                        ['text' => 'Quantity received, issued and remaining', 'is_correct' => true],
                        ['text' => 'Meeting minutes', 'is_correct' => false],
                        ['text' => 'Customer phone numbers', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Every office record should be kept forever in the active filing cabinet.',
                    'explanation' => 'Old records should be archived or disposed of according to a retention schedule to save space.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for a complete list of items or assets owned by an organisation? (One word)',
                    'explanation' => 'An inventory lists movable assets such as computers, furniture and equipment.',
                    'correct_answer' => 'Inventory',
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
                'title' => 'Lesson 4.1: Reception Duties and First Impressions',
                'duration_minutes' => 60,
                'content' => $this->lesson4_1(),
            ],
            [
                'title' => 'Lesson 4.2: Handling Telephone Calls Professionally',
                'duration_minutes' => 60,
                'content' => $this->lesson4_2(),
            ],
            [
                'title' => 'Lesson 4.3: Customer Service and Complaint Handling',
                'duration_minutes' => 60,
                'content' => $this->lesson4_3(),
            ],
            [
                'title' => 'Module 4 Quiz: Reception and Customer Service',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your knowledge of reception duties, telephone skills and customer service. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson4_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to manage a reception area, greet visitors professionally, maintain a visitor register and create a positive first impression for your organisation.</p>

<h2>The Reception Area Is the Face of the Organisation</h2>
<p>The reception area is the first place visitors see when they enter an office. A clean, welcoming reception sends the message that the organisation is professional and organised. A messy reception desk, unfriendly greeting or long wait tells visitors that the organisation does not care. In Zambian culture, hospitality matters, so a warm, respectful welcome is especially important.</p>

<h2>Key Reception Duties</h2>
<ul>
<li><strong>Greet visitors</strong>: Stand or smile, make eye contact and offer a polite greeting in English or a local language when appropriate.</li>
<li><strong>Maintain a visitor register</strong>: Record the visitor\'s name, organisation, purpose, time in and time out. This is important for security and fire safety.</li>
<li><strong>Issue visitor badges</strong>: If available, give visitors a badge so staff know they are authorised to be in the building.</li>
<li><strong>Inform the host</strong>: Call or message the person the visitor has come to see and direct the visitor to the waiting area.</li>
<li><strong>Keep the area tidy</strong>: Arrange chairs, keep magazines neat and make sure the desk is clean.</li>
</ul>

<h2>The Visitor Register</h2>
<p>A visitor register is a legal and security record. It should be easy to complete and stored safely. Typical columns include date, name, organisation, phone number, person visited, purpose, time in and time out. In the event of a fire or security incident, the register helps account for everyone in the building.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Design a simple visitor register form for a Zambian district office.</li>
<li>Write a greeting you would use when a visitor arrives.</li>
<li>List five things you would check every morning before the office opens.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Reception</strong>: The area where visitors are welcomed and assisted.</li>
<li><strong>Visitor register</strong>: A record of everyone who enters the office.</li>
<li><strong>First impression</strong>: The immediate opinion people form when they see or meet someone.</li>
<li><strong>Hospitality</strong>: Friendly and generous treatment of visitors.</li>
<li><strong>Badge</strong>: A card or tag showing that a visitor is authorised.</li>
</ul>

<h2>Summary</h2>
<p>Reception work is about more than sitting at a desk. It shapes the first impression of the organisation, keeps people safe and ensures that visitors are directed to the right person efficiently.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/communicate-effectively/" target="_blank" rel="noopener">Microsoft Learn — Welcome Visitors</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal" target="_blank" rel="noopener">Khan Academy — Professional Presence</a></li>
</ul>
HTML;
    }

    private function lesson4_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to answer telephone calls politely, place callers on hold, transfer calls correctly, take complete messages and end calls professionally.</p>

<h2>Professional Telephone Manner</h2>
<p>Telephone calls are still a major form of business communication in Zambia, especially when internet connections are unreliable. The way a secretary answers the phone represents the whole organisation. Speak clearly, smile while talking — it changes your tone — and listen carefully.</p>

<h2>Answering, Holding and Transferring</h2>
<p>Answer within three rings if possible. Give a greeting, the organisation name and your name. If you need to transfer a call, tell the caller what you are doing and why. If the person is not available, offer to take a message and promise a return call.</p>
<p>When placing a caller on hold, ask permission first. For example: "May I place you on hold for a moment while I check?" Do not leave callers on hold for long. If it will take time, offer to call them back.</p>

<h2>Closing the Call</h2>
<p>End the call politely. Summarise any agreed actions, thank the caller and say goodbye. Example: "Thank you, Mr. Phiri. I will pass your message to the accountant and she will call you back before 4 p.m. Goodbye." Wait for the caller to hang up first.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Practise the full greeting with a partner.</li>
<li>Role-play transferring a call and taking a message.</li>
<li>Write a short checklist to keep beside the telephone for difficult calls.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Telephone manner</strong>: The way a person speaks on the phone.</li>
<li><strong>Hold</strong>: To pause a call while the caller waits.</li>
<li><strong>Transfer</strong>: To redirect a call to another extension or person.</li>
<li><strong>Return call</strong>: A phone call made in response to a message.</li>
<li><strong>Extension</strong>: A telephone line connected to the same main number.</li>
</ul>

<h2>Summary</h2>
<p>A professional telephone manner builds trust and helps callers feel valued. Always answer politely, transfer carefully, take clear messages and close calls with a summary of what will happen next.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/communicate-effectively/" target="_blank" rel="noopener">Microsoft Learn — Phone Communication</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal" target="_blank" rel="noopener">Khan Academy — Speaking Confidently</a></li>
</ul>
HTML;
    }

    private function lesson4_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to deliver good customer service at the reception desk, handle complaints calmly and turn difficult situations into opportunities to build trust.</p>

<h2>What Is Customer Service?</h2>
<p>Customer service is the help and care you give to the people who use your organisation\'s services. These people may be clients, patients, parents, students, suppliers or visitors. Good customer service makes people want to return. Bad service drives them away and damages the organisation\'s name.</p>
<p>In a Zambian office, customer service often happens face-to-face at the reception desk or over the telephone. A secretary who is patient, respectful and efficient becomes known as the person who "knows how to help."</p>

<h2>The LADDER Method for Complaints</h2>
<ul>
<li><strong>Listen</strong>: Let the person explain without interrupting.</li>
<li><strong>Apologise</strong>: Say sorry that they had a bad experience, even if it was not your fault.</li>
<li><strong>Discuss</strong>: Ask questions to understand the problem fully.</li>
<li><strong>Decide</strong>: Agree on a fair solution.</li>
<li><strong>Act</strong>: Do what you promised quickly.</li>
<li><strong>Review</strong>: Follow up to make sure the problem is resolved.</li>
</ul>

<h2>Staying Calm Under Pressure</h2>
<p>Some customers will be angry or rude. Do not take it personally. Keep your voice low and steady, use the person\'s name if you know it, and focus on solving the problem. If a customer becomes threatening, call a supervisor or security immediately.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Think of a time you received good or bad service. What made it good or bad?</li>
<li>Write a short script using the LADDER method for a visitor who has been waiting too long.</li>
<li>Role-play handling a complaint with a classmate.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Customer service</strong>: The assistance and care given to people who use an organisation\'s services.</li>
<li><strong>Complaint</strong>: An expression of dissatisfaction about a product or service.</li>
<li><strong>Empathy</strong>: Understanding and sharing the feelings of another person.</li>
<li><strong>Resolution</strong>: The solving of a problem or complaint.</li>
<li><strong>Follow-up</strong>: Checking later to ensure a problem has been solved.</li>
</ul>

<h2>Summary</h2>
<p>Good customer service is respectful, patient and solution-focused. When complaints arise, listen, apologise, discuss, decide, act and review. This approach protects the organisation\'s reputation and turns frustrated visitors into loyal supporters.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/customer-service-fundamentals/" target="_blank" rel="noopener">Microsoft Learn — Customer Service Fundamentals</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal" target="_blank" rel="noopener">Khan Academy — Handling Difficult Conversations</a></li>
</ul>
HTML;
    }

    private function module4Quiz(): array
    {
        return [
            'title' => 'Module 4 Quiz: Reception and Customer Service',
            'description' => 'Assess your understanding of reception duties, telephone skills and complaint handling.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is the reception area important?',
                    'explanation' => 'The reception area creates the first impression visitors have of the organisation.',
                    'options' => [
                        ['text' => 'It is where staff eat lunch', 'is_correct' => false],
                        ['text' => 'It creates the first impression of the organisation', 'is_correct' => true],
                        ['text' => 'It stores confidential records', 'is_correct' => false],
                        ['text' => 'It is only for deliveries', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What information should a visitor register record?',
                    'explanation' => 'A visitor register records name, organisation, purpose, time in and time out for security.',
                    'options' => [
                        ['text' => 'Only the visitor\'s favourite colour', 'is_correct' => false],
                        ['text' => 'Name, organisation, purpose and times in and out', 'is_correct' => true],
                        ['text' => 'The visitor\'s salary', 'is_correct' => false],
                        ['text' => 'Staff opinions about the visitor', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Before placing a caller on hold, what should you do?',
                    'explanation' => 'Always ask permission before placing a caller on hold and explain why.',
                    'options' => [
                        ['text' => 'Hang up immediately', 'is_correct' => false],
                        ['text' => 'Ask permission and explain why', 'is_correct' => true],
                        ['text' => 'Transfer them without warning', 'is_correct' => false],
                        ['text' => 'Ignore the caller', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In the LADDER method, what does the "A" stand for?',
                    'explanation' => 'The A in LADDER stands for Apologise, recognising the customer\'s bad experience.',
                    'options' => [
                        ['text' => 'Argue', 'is_correct' => false],
                        ['text' => 'Apologise', 'is_correct' => true],
                        ['text' => 'Avoid', 'is_correct' => false],
                        ['text' => 'Accuse', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'When a customer complains, you should interrupt them quickly to defend the organisation.',
                    'explanation' => 'You should listen fully before responding. Interrupting makes the customer more angry.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for assistance and care given to people who use an organisation\'s services? (Two words)',
                    'explanation' => 'Customer service is the help and care provided to clients, visitors and users.',
                    'correct_answer' => 'Customer service',
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
                'title' => 'Lesson 5.1: Microsoft Word for Professional Documents',
                'duration_minutes' => 75,
                'content' => $this->lesson5_1(),
            ],
            [
                'title' => 'Lesson 5.2: Microsoft Excel for Office Records',
                'duration_minutes' => 75,
                'content' => $this->lesson5_2(),
            ],
            [
                'title' => 'Lesson 5.3: Microsoft PowerPoint for Office Presentations',
                'duration_minutes' => 60,
                'content' => $this->lesson5_3(),
            ],
            [
                'title' => 'Module 5 Quiz: Computer Applications for Office Work',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your knowledge of Microsoft Word, Excel and PowerPoint for secretarial work. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson5_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to use Microsoft Word to create, format and print professional office documents such as letters, memos and reports.</p>

<h2>Why Word Skills Matter</h2>
<p>Microsoft Word is still the most common word-processing program in Zambian offices. A secretary who can format a document quickly and correctly produces work that looks professional and is easy to read. Poor formatting, on the other hand, makes even good content look careless.</p>

<h2>Essential Word Skills</h2>
<ul>
<li><strong>Opening and saving</strong>: Use Save As to choose the location and file name. Save regularly while working.</li>
<li><strong>Font and paragraph formatting</strong>: Choose a readable font such as Calibri or Times New Roman, set line spacing and align text correctly.</li>
<li><strong>Headers and footers</strong>: Add page numbers, document title or date at the top or bottom of each page.</li>
<li><strong>Bullets and numbering</strong>: Use automatic bullets for lists and numbering for steps.</li>
<li><strong>Tables</strong>: Insert tables to present information clearly, such as schedules or budgets.</li>
<li><strong>Spell check and grammar</strong>: Always proofread and use the spelling tool, but remember it does not catch every mistake.</li>
</ul>

<h2>Formatting a Business Letter</h2>
<p>A business letter in Word should have margins of about 2.5 centimetres on all sides, a clear subject line and single or 1.5 line spacing. Use bold for headings and keep the body left-aligned. Avoid using too many colours or fancy fonts.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open Microsoft Word and create a new document.</li>
<li>Type a short memo about a staff meeting.</li>
<li>Format it with a bold heading, bullet points and your name at the bottom.</li>
<li>Save it as Memo_Practice_YourName.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Word processor</strong>: A computer program used to create and edit text documents.</li>
<li><strong>Font</strong>: The style and size of text.</li>
<li><strong>Line spacing</strong>: The amount of space between lines of text.</li>
<li><strong>Header</strong>: Text that appears at the top of every page.</li>
<li><strong>Footer</strong>: Text that appears at the bottom of every page.</li>
</ul>

<h2>Summary</h2>
<p>Microsoft Word is a powerful tool for producing professional office documents. Learn the basic formatting tools, use tables and lists wisely, and always check spelling before printing or sending.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/word" target="_blank" rel="noopener">Microsoft Support — Word Basics</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal" target="_blank" rel="noopener">Khan Academy — Digital Productivity</a></li>
</ul>
HTML;
    }

    private function lesson5_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to use Microsoft Excel to create simple spreadsheets, enter and format data, use basic formulas and produce clear office records such as attendance lists and petty cash summaries.</p>

<h2>Why Excel Is Useful in the Office</h2>
<p>Excel helps secretaries manage numbers and lists efficiently. Instead of calculating totals by hand, Excel does it automatically. Instead of rewriting lists, you can sort and filter them. Many offices use Excel for attendance registers, stock lists, budgets, petty cash books and contact lists.</p>

<h2>Basic Excel Skills</h2>
<ul>
<li><strong>Entering data</strong>: Click a cell and type. Press Enter to move down or Tab to move right.</li>
<li><strong>Formatting cells</strong>: Change font, borders, alignment and number formats such as currency or date.</li>
<li><strong>Simple formulas</strong>: Use =SUM(A1:A10) to add numbers, =AVERAGE(B1:B10) for averages and =C1*D1 for multiplication.</li>
<li><strong>Sorting and filtering</strong>: Arrange data alphabetically or numerically, or show only rows that meet certain conditions.</li>
<li><strong>Printing</strong>: Use Print Preview to check that columns fit on the page before printing.</li>
</ul>

<h2>Example: Petty Cash Record</h2>
<table>
<tr><th>Date</th><th>Description</th><th>Amount In (K)</th><th>Amount Out (K)</th><th>Balance (K)</th></tr>
<tr><td>01/06/2026</td><td>Opening balance</td><td>500.00</td><td>—</td><td>=500.00</td></tr>
<tr><td>02/06/2026</td><td>Stationery</td><td>—</td><td>75.00</td><td>425.00</td></tr>
<tr><td>03/06/2026</td><td>Refund for postage</td><td>20.00</td><td>—</td><td>445.00</td></tr>
</table>
<p>In a real spreadsheet, the balance column would use formulas to calculate automatically.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open Excel and create a simple attendance register.</li>
<li>Include columns for name, department, date and present/absent.</li>
<li>Use a formula to count how many people are present.</li>
<li>Save the file as Attendance_Practice_YourName.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Spreadsheet</strong>: A digital worksheet made of rows and columns.</li>
<li><strong>Cell</strong>: A single box in a spreadsheet where data is entered.</li>
<li><strong>Formula</strong>: A calculation written in a cell, starting with an equals sign.</li>
<li><strong>Worksheet</strong>: A single page within an Excel workbook.</li>
<li><strong>Filter</strong>: A tool that shows only rows matching chosen criteria.</li>
</ul>

<h2>Summary</h2>
<p>Excel helps secretaries manage numbers, lists and records quickly and accurately. Learn to enter data, format cells, use simple formulas and present information in tables.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/excel" target="_blank" rel="noopener">Microsoft Support — Excel Basics</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal" target="_blank" rel="noopener">Khan Academy — Spreadsheet Skills</a></li>
</ul>
HTML;
    }

    private function lesson5_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to create a simple but effective PowerPoint presentation for an office meeting, using clear slides, readable text and appropriate visuals.</p>

<h2>PowerPoint in the Office</h2>
<p>Secretaries and office professionals often help managers prepare presentations for staff meetings, donor visits, training sessions or board reports. A good presentation supports the speaker and makes information easier to understand. A bad presentation confuses the audience and wastes time.</p>

<h2>Rules for Effective Slides</h2>
<ul>
<li><strong>One idea per slide</strong>: Do not crowd many topics onto one slide.</li>
<li><strong>Large text</strong>: Use at least 24-point font for body text and larger for titles.</li>
<li><strong>Readable colours</strong>: Use dark text on a light background or light text on a dark background. Avoid bright or clashing colours.</li>
<li><strong>Limited bullets</strong>: Use three to five bullet points per slide, not ten.</li>
<li><strong>Simple visuals</strong>: Use charts, tables or pictures that support the message. Avoid animations that distract.</li>
</ul>

<h2>Slide Structure</h2>
<p>A typical short office presentation has:</p>
<ol>
<li>Title slide with topic, presenter and date.</li>
<li>Introduction slide explaining the purpose.</li>
<li>Content slides with key points.</li>
<li>A chart or table slide if data is needed.</li>
<li>Conclusion or action summary slide.</li>
<li>Thank-you slide with contact details.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Open PowerPoint and create a five-slide presentation about office safety.</li>
<li>Use a consistent design and readable fonts.</li>
<li>Include one simple chart or table.</li>
<li>Practice presenting it in two minutes.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Presentation</strong>: A spoken report supported by visual slides.</li>
<li><strong>Slide</strong>: A single screen in a presentation.</li>
<li><strong>Bullet point</strong>: A short item in a list.</li>
<li><strong>Animation</strong>: A movement effect added to text or objects on a slide.</li>
<li><strong>Visual aid</strong>: An image, chart or diagram used to explain information.</li>
</ul>

<h2>Summary</h2>
<p>PowerPoint presentations should be clear, simple and supportive. One idea per slide, large text, readable colours and limited animations help the audience understand and remember the message.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/powerpoint" target="_blank" rel="noopener">Microsoft Support — PowerPoint Basics</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal" target="_blank" rel="noopener">Khan Academy — Presentation Skills</a></li>
</ul>
HTML;
    }

    private function module5Quiz(): array
    {
        return [
            'title' => 'Module 5 Quiz: Computer Applications for Office Work',
            'description' => 'Assess your understanding of Microsoft Word, Excel and PowerPoint for secretarial tasks.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which Microsoft Office program is mainly used to create letters and memos?',
                    'explanation' => 'Microsoft Word is the word-processing program used for text documents.',
                    'options' => [
                        ['text' => 'Excel', 'is_correct' => false],
                        ['text' => 'PowerPoint', 'is_correct' => false],
                        ['text' => 'Word', 'is_correct' => true],
                        ['text' => 'Outlook', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In Excel, what does the formula =SUM(B1:B5) do?',
                    'explanation' => 'The SUM formula adds all the numbers in the range B1 to B5.',
                    'options' => [
                        ['text' => 'Finds the highest value', 'is_correct' => false],
                        ['text' => 'Adds the values from B1 to B5', 'is_correct' => true],
                        ['text' => 'Counts the cells', 'is_correct' => false],
                        ['text' => 'Sorts the data', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the recommended maximum number of bullet points per slide?',
                    'explanation' => 'Slides are easier to read with three to five bullet points.',
                    'options' => [
                        ['text' => 'One bullet point', 'is_correct' => false],
                        ['text' => 'Three to five bullet points', 'is_correct' => true],
                        ['text' => 'Fifteen bullet points', 'is_correct' => false],
                        ['text' => 'As many as possible', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which feature should you use before printing to check that the document looks correct?',
                    'explanation' => 'Print Preview shows how the document will look when printed.',
                    'options' => [
                        ['text' => 'Save As', 'is_correct' => false],
                        ['text' => 'Print Preview', 'is_correct' => true],
                        ['text' => 'Undo', 'is_correct' => false],
                        ['text' => 'Bold', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'In PowerPoint, it is best to put as much text as possible on each slide.',
                    'explanation' => 'Slides should have limited text so the audience can focus on the speaker.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for a single box in an Excel spreadsheet where data is entered? (One word)',
                    'explanation' => 'A cell is the intersection of a row and a column in a spreadsheet.',
                    'correct_answer' => 'Cell',
                ],
            ],
        ];
    }

    // =========================================================================
    // MODULE 6
    // =========================================================================

    private function module6Lessons(): array
    {
        return [
            [
                'title' => 'Lesson 6.1: Managing Appointments and Diaries',
                'duration_minutes' => 60,
                'content' => $this->lesson6_1(),
            ],
            [
                'title' => 'Lesson 6.2: Travel Arrangements and Itineraries',
                'duration_minutes' => 60,
                'content' => $this->lesson6_2(),
            ],
            [
                'title' => 'Lesson 6.3: Confidentiality, Data Protection and Professional Conduct',
                'duration_minutes' => 75,
                'content' => $this->lesson6_3(),
            ],
            [
                'title' => 'Module 6 Quiz: Diary Management, Travel and Confidentiality',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your knowledge of diary management, travel arrangements and confidentiality. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson6_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to manage a manager\'s appointment diary, schedule meetings without conflicts and handle cancellations and changes professionally.</p>

<h2>The Diary as a Control Tool</h2>
<p>A diary is more than a calendar. It is the tool that controls where the manager needs to be, who they need to meet and what preparation is required. A well-managed diary prevents double bookings, missed appointments and wasted time. In a busy Zambian office, the secretary\'s skill in diary management directly affects the manager\'s productivity.</p>

<h2>Paper and Electronic Diaries</h2>
<p>Some offices still use paper desk diaries, while others use Outlook, Google Calendar or phone calendars. Whatever the system, the principles are the same. Record the date, time, venue, purpose and name of the person or organisation. Add travel time if the meeting is outside the office. Note any documents the manager needs to bring.</p>

<h2>Diary Rules</h2>
<ul>
<li>Check the diary every morning and confirm appointments for the next day.</li>
<li>Never make an appointment without checking availability first.</li>
<li>Leave gaps between meetings for travel and preparation.</li>
<li>Write cancellations clearly and note the reason.</li>
<li>Protect the diary from unauthorised access.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Create a one-week diary for a district commissioner with at least five appointments.</li>
<li>Include travel time, location and documents needed for each appointment.</li>
<li>Practise moving one appointment and sending a polite reschedule message.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Diary</strong>: A record of appointments and scheduled activities.</li>
<li><strong>Double booking</strong>: Scheduling two appointments at the same time.</li>
<li><strong>Availability</strong>: The times when a person is free to attend a meeting.</li>
<li><strong>Cancellation</strong>: The act of calling off a planned appointment.</li>
<li><strong>Itinerary</strong>: A detailed plan of a journey or series of appointments.</li>
</ul>

<h2>Summary</h2>
<p>Managing a diary well requires attention to detail, good communication and respect for the manager\'s time. Confirm appointments, avoid double bookings and keep the diary secure.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/outlook" target="_blank" rel="noopener">Microsoft Support — Outlook Calendar</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal" target="_blank" rel="noopener">Khan Academy — Time Management</a></li>
</ul>
HTML;
    }

    private function lesson6_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to make local and international travel arrangements, prepare a travel itinerary and pack the documents a traveller needs for a business trip.</p>

<h2>Travel Arrangements for Zambian Offices</h2>
<p>Secretaries often book transport and accommodation for managers, staff and visitors. Local travel may involve buses, shuttles or flights from Lusaka to Livingstone, Ndola or Solwezi. International travel may involve flights, visas, hotel bookings and airport transfers. Good planning prevents missed flights, expensive bookings and unnecessary stress.</p>

<h2>Steps in Making Travel Arrangements</h2>
<ol>
<li>Confirm the purpose, dates and destinations of the trip.</li>
<li>Check the traveller\'s passport validity and visa requirements.</li>
<li>Book transport and accommodation according to the organisation\'s policy.</li>
<li>Prepare an itinerary with times, locations and contact numbers.</li>
<li>Arrange any required payments or travel advances.</li>
<li>Gather documents such as tickets, booking confirmations and identification.</li>
<li>Inform relevant people at the destination of the arrival details.</li>
</ol>

<h2>What Is an Itinerary?</h2>
<p>An itinerary is a detailed travel plan. It should include the date and time of each leg of the journey, the transport company or flight number, departure and arrival locations, accommodation details, meeting times and emergency contact numbers. A good itinerary helps the traveller stay on schedule and lets the office know where they are.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Prepare a one-day itinerary for a manager travelling from Lusaka to Ndola for a meeting.</li>
<li>Include departure time, arrival time, meeting time, lunch and return travel.</li>
<li>List the documents the traveller should carry.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Itinerary</strong>: A detailed plan of a journey.</li>
<li><strong>Visa</strong>: Official permission to enter a country.</li>
<li><strong>Booking confirmation</strong>: A document showing that a seat or room has been reserved.</li>
<li><strong>Travel advance</strong>: Money given to a traveller before a trip to cover expenses.</li>
<li><strong>Airport transfer</strong>: Transport between the airport and the hotel or meeting place.</li>
</ul>

<h2>Summary</h2>
<p>Travel arrangements require careful planning. Confirm details early, check passport and visa requirements, book according to policy and provide a clear itinerary so the traveller can focus on the business purpose of the trip.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/outlook" target="_blank" rel="noopener">Microsoft Support — Outlook Travel Tips</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal" target="_blank" rel="noopener">Khan Academy — Planning Skills</a></li>
</ul>
HTML;
    }

    private function lesson6_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will understand the importance of confidentiality and data protection, know how to handle sensitive records, and be able to act professionally in challenging situations.</p>

<h2>Confidentiality in Office Work</h2>
<p>Secretaries handle sensitive information every day. This includes staff personal details, medical records, salary information, contract negotiations, minutes of disciplinary hearings and private correspondence. Keeping this information confidential is both an ethical duty and often a legal requirement.</p>
<p>A breach of confidentiality can cause serious harm. It can damage reputations, lead to unfair treatment, break trust with donors or partners and expose the organisation to legal action. Even a careless comment in a public place can be a breach.</p>

<h2>Data Protection Practices</h2>
<ul>
<li>Collect only the information that is necessary.</li>
<li>Store paper records in locked cabinets.</li>
<li>Use strong passwords and do not share accounts.</li>
<li>Back up important digital files regularly.</li>
<li>Limit access to sensitive information to authorised people only.</li>
<li>Dispose of confidential waste securely.</li>
</ul>

<h2>Professional Conduct</h2>
<p>Professional conduct means behaving in a way that brings credit to your organisation. It includes honesty, punctuality, respect for colleagues, appropriate dress, careful use of office resources and loyalty to the organisation\'s mission. A secretary who behaves professionally becomes a trusted representative of the office.</p>

<h2>Try It Yourself</h2>
<ol>
<li>List five types of confidential information you might handle as a secretary.</li>
<li>Write three rules you would follow to protect staff records.</li>
<li>Describe how you would respond if a stranger asked for details about a staff member.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Confidentiality</strong>: Keeping private information secure and not sharing it inappropriately.</li>
<li><strong>Data protection</strong>: Practices that keep personal and sensitive information safe.</li>
<li><strong>Breach</strong>: An incident where confidential information is wrongly disclosed.</li>
<li><strong>Authorised access</strong>: Permission given to specific people to view certain information.</li>
<li><strong>Professional conduct</strong>: Behaviour that meets the standards expected at work.</li>
</ul>

<h2>Summary</h2>
<p>Confidentiality and data protection are at the heart of professional secretarial work. Handle sensitive information carefully, follow security rules and maintain conduct that reflects well on your organisation.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/security-fundamentals/" target="_blank" rel="noopener">Microsoft Learn — Security Fundamentals</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal" target="_blank" rel="noopener">Khan Academy — Ethics at Work</a></li>
</ul>
HTML;
    }

    private function module6Quiz(): array
    {
        return [
            'title' => 'Module 6 Quiz: Diary Management, Travel and Confidentiality',
            'description' => 'Assess your understanding of diary management, travel arrangements and confidentiality.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main purpose of an appointment diary?',
                    'explanation' => 'A diary controls where the manager needs to be, when and for what purpose.',
                    'options' => [
                        ['text' => 'To record staff salaries', 'is_correct' => false],
                        ['text' => 'To schedule and track appointments', 'is_correct' => true],
                        ['text' => 'To store office stationery', 'is_correct' => false],
                        ['text' => 'To write meeting minutes', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which document gives detailed travel plans including times, locations and contacts?',
                    'explanation' => 'An itinerary is a detailed plan of a journey or series of appointments.',
                    'options' => [
                        ['text' => 'Visa', 'is_correct' => false],
                        ['text' => 'Itinerary', 'is_correct' => true],
                        ['text' => 'Invoice', 'is_correct' => false],
                        ['text' => 'Memo', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should a secretary do with confidential paper waste?',
                    'explanation' => 'Confidential paper waste should be shredded or burned to prevent unauthorised reading.',
                    'options' => [
                        ['text' => 'Recycle it with newspapers', 'is_correct' => false],
                        ['text' => 'Leave it on the desk', 'is_correct' => false],
                        ['text' => 'Shred or burn it', 'is_correct' => true],
                        ['text' => 'Give it to a visitor', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is an example of sensitive information?',
                    'explanation' => 'Staff salary details are sensitive and must be kept confidential.',
                    'options' => [
                        ['text' => 'The office opening hours', 'is_correct' => false],
                        ['text' => 'Staff salary details', 'is_correct' => true],
                        ['text' => 'The public phone number', 'is_correct' => false],
                        ['text' => 'The date of the next public holiday', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A secretary may share a manager\'s travel itinerary with anyone who asks for it.',
                    'explanation' => 'Travel itineraries should be shared only with authorised people for safety and confidentiality reasons.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for scheduling two appointments at the same time? (Two words)',
                    'explanation' => 'Double booking happens when two appointments overlap.',
                    'correct_answer' => 'Double booking',
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
        $this->command->info('=== Secretarial & Office Management Content Seed Summary ===');
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
        $this->command->info('Certificate in Secretarial & Office Management content seeded successfully.');
    }
}
