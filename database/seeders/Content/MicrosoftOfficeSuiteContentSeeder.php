<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MicrosoftOfficeSuiteContentSeeder extends Seeder
{
    private int $courseId;
    private int $modulesCreated = 0;
    private int $lessonsCreated = 0;
    private int $quizzesCreated = 0;
    private int $questionsCreated = 0;
    private array $skippedModules = [];
    private array $skippedQuizzes = [];

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Microsoft Office Suite')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Microsoft Office Suite" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        DB::transaction(function () use ($course) {
            // --- New Modules ---
            $newModuleDefinitions = [
                [
                    'title' => 'Microsoft Outlook for Business Communication',
                    'description' => 'Master email etiquette, calendar management, contacts, and tasks using Microsoft Outlook to run a professional small office or stay organised in any workplace.',
                    'display_order' => 4,
                ],
                [
                    'title' => 'Advanced Excel and Office Integration',
                    'description' => 'Unlock advanced Excel formulas, pivot tables, and learn how to link Word, Excel, and PowerPoint together to build powerful documents, reports, and presentations for a Zambian business.',
                    'display_order' => 5,
                ],
            ];

            foreach ($newModuleDefinitions as $modDef) {
                $exists = Module::where('course_id', $this->courseId)
                    ->whereRaw('LOWER(TRIM(title)) = ?', [strtolower(trim($modDef['title']))])
                    ->exists();

                if ($exists) {
                    $this->skippedModules[] = $modDef['title'];
                    $this->command->info("Module '{$modDef['title']}' already exists. Skipping.");
                    continue;
                }

                $module = Module::create([
                    'course_id' => $this->courseId,
                    'title' => $modDef['title'],
                    'description' => $modDef['description'],
                    'display_order' => $modDef['display_order'],
                    'duration_minutes' => 0,
                    'is_published' => 1,
                ]);

                $this->modulesCreated++;
                $this->createLessonsForNewModule($module);
            }

            // --- Quizzes for all modules (existing + new) ---
            foreach ($course->modules()->orderBy('display_order')->get() as $module) {
                $lastLesson = $module->lessons()->reorder()->orderByDesc('display_order')->first();
                if (! $lastLesson) {
                    continue;
                }

                $alreadyHasQuiz = Quiz::where('course_id', $this->courseId)
                    ->where('lesson_id', $lastLesson->id)
                    ->exists();

                if ($alreadyHasQuiz) {
                    $this->skippedQuizzes[] = $module->title;
                    continue;
                }

                $this->createQuizForModule($module, $lastLesson);
            }
        });

        $this->printSummary();
    }

    private function createLessonsForNewModule(Module $module): void
    {
        $methodMap = [
            'Microsoft Outlook for Business Communication' => 'outlookLessons',
            'Advanced Excel and Office Integration' => 'advancedExcelLessons',
        ];

        $method = $methodMap[$module->title] ?? null;
        if (! $method) {
            return;
        }

        $lessonsData = $this->{$method}();
        $lessonIds = [];
        $moduleDuration = 0;

        foreach ($lessonsData as $lessonIndex => $lessonData) {
            $displayOrder = $lessonIndex + 1;
            $isPreview = 0;
            $duration = $lessonData['duration_minutes'];
            $moduleDuration += $duration;

            $lesson = Lesson::create([
                'module_id' => $module->id,
                'title' => $lessonData['title'],
                'content' => $lessonData['content'] ?? null,
                'lesson_type' => $lessonData['lesson_type'] ?? 'Reading',
                'duration_minutes' => $duration,
                'display_order' => $displayOrder,
                'is_preview' => $isPreview,
                'is_mandatory' => 1,
                'points' => 10,
            ]);

            $lessonIds[] = $lesson->id;
            $this->lessonsCreated++;
        }

        $module->duration_minutes = $moduleDuration;
        $module->save();
    }

    private function createQuizForModule(Module $module, Lesson $lastLesson): void
    {
        $methodMap = [
            'Microsoft Word Mastery' => 'wordQuiz',
            'Microsoft Excel Mastery' => 'excelQuiz',
            'Presentation & Design (PowerPoint + Publisher)' => 'presentationQuiz',
            'Microsoft Outlook for Business Communication' => 'outlookQuiz',
            'Advanced Excel and Office Integration' => 'advancedExcelQuiz',
        ];

        $method = $methodMap[$module->title] ?? null;
        if (! $method) {
            return;
        }

        $quizData = $this->{$method}();

        $quiz = Quiz::create([
            'course_id' => $this->courseId,
            'lesson_id' => $lastLesson->id,
            'title' => $quizData['title'],
            'description' => $quizData['description'],
            'quiz_type' => 'Graded',
            'time_limit_minutes' => $quizData['time_limit_minutes'] ?? 20,
            'max_attempts' => 3,
            'passing_score' => 60.00,
            'show_correct_answers' => 1,
            'is_published' => 1,
        ]);

        $this->quizzesCreated++;

        foreach ($quizData['questions'] as $qIndex => $qData) {
            $question = Question::create([
                'question_type' => $qData['type'],
                'question_text' => $qData['text'],
                'points' => $qData['type'] === 'Short Answer' ? 3 : 2,
                'explanation' => $qData['explanation'],
                'correct_answer' => $qData['correct_answer'] ?? null,
            ]);

            $this->questionsCreated++;

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

            $quiz->questions()->attach($question->question_id, ['display_order' => $qIndex + 1]);
        }
    }


    // ------------------------------------------------------------------
    // Module 4: Microsoft Outlook for Business Communication — Lessons
    // ------------------------------------------------------------------

    private function outlookLessons(): array
    {
        return [
            [
                'title' => '4.1 Getting Started with Microsoft Outlook',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to open Microsoft Outlook, identify the main parts of the Outlook window, set up an email account, send your first email, and understand how Outlook can help you manage business communication professionally in a Zambian office or from a college computer.</p>

<h2>Why Outlook Matters for Zambian Businesses</h2>
<p>Email is the backbone of modern business communication. Whether you are applying for a job at a Lusaka firm, writing to the Zambia Revenue Authority about your TPIN, or corresponding with a supplier in Ndola, a professional email makes a lasting impression. Microsoft Outlook is more than just email. It combines your inbox, calendar, contacts, and tasks in one place, so you never miss a meeting, forget a deadline, or lose a client's phone number.</p>
<p>In a small office in Kalomo, Outlook can help a business owner keep track of orders from customers, schedule delivery dates, and set reminders to pay ZESCO bills before load-shedding. For a civil servant, Outlook is the standard tool for internal memos and meeting invitations. Learning Outlook is therefore an essential part of any office-skills certificate.</p>

<h2>The Outlook Window</h2>
<p>When you open Outlook, you see a clean window divided into areas. On the far left is the <strong>folder pane</strong>, which lists your email folders such as Inbox, Sent Items, Drafts, and Deleted Items. Next to it is the <strong>message list</strong>, showing the emails in the selected folder. On the right is the <strong>reading pane</strong>, where you preview the content of a selected email without opening it fully.</p>
<p>At the top is the <strong>ribbon</strong>, a toolbar with tabs such as Home, Send/Receive, Folder, and View. The ribbon changes depending on what you are doing. When you write a new email, the ribbon shows formatting tools such as font colour, bold, and bullet lists. When you view the calendar, the ribbon shows scheduling options.</p>

<h2>Setting Up an Email Account</h2>
<p>Outlook can connect to many types of email accounts. If your college or workplace uses Microsoft Exchange, your IT administrator will provide the settings. If you have a personal Gmail or Yahoo account, you can add it manually. At Edutrack College, the computers may already have Outlook configured with a practice account, but it is useful to know how to add your own.</p>
<p>To add an account, click <strong>File</strong> in the top-left corner, then <strong>Add Account</strong>. Type your email address and password. Outlook will try to detect the correct settings automatically. If it fails, you may need to enter the server details manually. For Gmail, this usually means enabling IMAP in your Gmail settings first. If you get stuck, ask your instructor or search the Microsoft support website for "Outlook Gmail setup."</p>

<h2>Sending Your First Email</h2>
<p>Click the <strong>New Email</strong> button on the Home tab. A new window opens with three essential boxes:</p>
<ul>
<li><strong>To</strong> — type the recipient's email address here. You can add multiple addresses separated by semicolons.</li>
<li><strong>Subject</strong> — write a short, clear summary of what the email is about. A good subject helps the recipient prioritise your message.</li>
<li><strong>Body</strong> — the main text of your email goes here.</li>
</ul>
<p>After writing your message, click <strong>Send</strong>. The email moves to your Outbox and then to Sent Items once it leaves your computer. If you are not connected to the internet, Outlook stores the email and sends it automatically when the connection returns. This is useful in areas with intermittent mobile data.</p>

<h2>Worked Example: Emailing a Supplier in Lusaka</h2>
<p>Mrs Banda runs a small shop selling dried fish and vegetables in Kalomo. She wants to order fresh stock from a supplier in Lusaka. She opens Outlook and follows these steps:</p>
<ol>
<li>She clicks <strong>New Email</strong> and types the supplier's address in the To box.</li>
<li>In the Subject line she writes: "Order Request — Dried Fish and Vegetables for June."</li>
<li>In the body she greets the supplier politely, lists the quantities she needs, asks for the total price in Kwacha, and requests delivery by Friday.</li>
<li>She proofreads the email, checks the spelling by pressing F7, and clicks Send.</li>
<li>She then drags the supplier's reply to a folder named "Suppliers" so she can find it later.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Open Microsoft Outlook on the college computer or your laptop.</li>
<li>Identify the folder pane, message list, and reading pane. Click each folder to see what it contains.</li>
<li>Write a practice email to yourself or a classmate with the subject "Practising Outlook at Edutrack College."</li>
<li>Add the recipient's address, write two sentences in the body, and click Send.</li>
<li>Create a new folder by right-clicking your Inbox, selecting New Folder, and naming it "Practice."</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Inbox</strong> — the main folder where new emails arrive.</li>
<li><strong>Sent Items</strong> — a folder that stores copies of emails you have sent.</li>
<li><strong>Ribbon</strong> — the toolbar at the top of Outlook that changes depending on the task.</li>
<li><strong>Reading pane</strong> — the area where you preview an email without fully opening it.</li>
<li><strong>Subject line</strong> — a brief description of an email's contents that helps the recipient understand its purpose.</li>
</ul>

<h2>Summary</h2>
<p>Microsoft Outlook is a powerful tool for managing emails, appointments, and contacts in one place. Understanding the main window, setting up an account, and sending a well-structured email are the first steps toward professional communication. Whether you run a small business, work in an office, or study at college, Outlook keeps your digital life organised and efficient.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/outlook">Microsoft Support — Outlook Help</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/outlook-getting-started/">Microsoft Learn — Getting Started with Outlook</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.2 Writing Professional Emails',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to write clear, polite, and professional emails, use the CC and BCC fields correctly, attach files safely, create a professional email signature, and avoid common mistakes that damage your credibility when writing to employers, clients, or government offices in Zambia.</p>

<h2>The Anatomy of a Professional Email</h2>
<p>A professional email is like a formal letter, but faster. It has a clear structure that helps the reader understand your message quickly. Every professional email should contain:</p>
<ul>
<li><strong>Subject line</strong> — specific and concise. Instead of "Hello," write "Application for Office Assistant Position — Reference OA/2025."</li>
<li><strong>Salutation</strong> — a polite greeting. Use "Dear Mr Phiri," or "Dear Hiring Manager," when you do not know the name. Avoid "Hey" or "Hi dude" in business emails.</li>
<li><strong>Opening sentence</strong> — state your purpose immediately. "I am writing to request a quotation for 500 bags of cement."</li>
<li><strong>Body paragraphs</strong> — explain the details in short paragraphs. One idea per paragraph makes reading easier.</li>
<li><strong>Closing</strong> — a polite sign-off such as "Yours faithfully," or "Kind regards," followed by your full name.</li>
<li><strong>Signature block</strong> — your name, title, phone number, and organisation.</li>
</ul>

<h2>CC and BCC Explained</h2>
<p><strong>CC</strong> stands for Carbon Copy. When you add addresses to the CC field, those people receive a copy of the email and everyone can see their addresses. Use CC when you want to keep someone informed without requiring them to act. For example, if you email a supplier about an order, you might CC your accountant so they know the transaction is happening.</p>
<p><strong>BCC</strong> stands for Blind Carbon Copy. BCC recipients receive the email, but their addresses are hidden from everyone else. This is essential when sending a message to many people who do not know each other. If you are emailing fifty parents from a PTA WhatsApp group about a school fee meeting, use BCC to protect their privacy. Never share private email addresses in the To or CC fields without permission.</p>

<h2>Attaching Files Safely</h2>
<p>Outlook allows you to attach documents, spreadsheets, images, and PDFs to an email. Click the <strong>Paperclip</strong> icon on the Message tab, browse to your file, and select it. Before sending, check that the attachment is the correct file and that it is not too large. Many email servers reject attachments larger than 25 megabytes.</p>
<p>Always name your attachments clearly. Instead of "Document1.docx," use "June_Invoice_Kalomo_Shop.docx." If you are sending a CV to an employer, name it "YourName_CV_2025.pdf." This shows professionalism and helps the recipient find your file later. Never open unexpected attachments from unknown senders, as they may contain viruses.</p>

<h2>Creating an Email Signature</h2>
<p>An email signature appears automatically at the bottom of every email you send. To create one in Outlook, click <strong>File &gt; Options &gt; Mail &gt; Signatures</strong>. Type your name, job title, phone number, and organisation name. You can also add a small logo, but keep it simple. A cluttered signature with large images looks unprofessional and makes emails slow to load.</p>
<p>Example signature for a Zambian small-business owner:</p>
<blockquote>
<p>Grace Banda<br>
Proprietor — Banda Fresh Produce<br>
Phone: +260 97 123 4567<br>
Kalomo, Zambia</p>
</blockquote>

<h2>Common Mistakes to Avoid</h2>
<ul>
<li><strong>ALL CAPS</strong> — writing in capital letters looks like shouting. Use normal sentence case.</li>
<li><strong>No subject line</strong> — emails without subjects often go unread or end up in spam folders.</li>
<li><strong>Slang and abbreviations</strong> — "lol," "btw," and "g2g" are fine for friends but not for business.</li>
<li><strong>Replying in anger</strong> — if an email upsets you, wait one hour before responding. A calm reply protects your reputation.</li>
<li><strong>Forgetting to attach</strong> — if you mention an attachment, double-check that you actually attached it before clicking Send.</li>
</ul>

<h2>Worked Example: Emailing ZRA About a TPIN</h2>
<p>Mr Zulu needs to confirm his Taxpayer Identification Number with ZRA. He composes the following email:</p>
<blockquote>
<p><strong>Subject:</strong> TPIN Confirmation Request — Mr John Zulu, NRC 123456/10/1</p>
<p>Dear Sir or Madam,</p>
<p>I am writing to request confirmation of my Taxpayer Identification Number (TPIN). I applied on 15 May 2025 at the ZRA office in Lusaka but have not yet received written confirmation.</p>
<p>My full name is John Zulu. My National Registration Card number is 123456/10/1. My mobile number is +260 96 765 4321.</p>
<p>Please could you confirm my TPIN by reply email or SMS at your earliest convenience. I need this number to file my quarterly tax return before the 30 June deadline.</p>
<p>Kind regards,<br>
John Zulu<br>
Phone: +260 96 765 4321<br>
Email: john.zulu@email.com</p>
</blockquote>

<h2>Try It Yourself</h2>
<ol>
<li>Open Outlook and click New Email.</li>
<li>Write an email to a fictional employer applying for an office assistant position. Include a clear subject, polite greeting, two body paragraphs, and a professional closing.</li>
<li>Add your own email signature in Outlook under File &gt; Options &gt; Mail &gt; Signatures.</li>
<li>Attach a practice document from your Documents folder. Check that the filename is descriptive.</li>
<li>Send the email to yourself or a classmate and review how it looks in your Inbox.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>CC (Carbon Copy)</strong> — sends a copy of the email to additional recipients whose addresses are visible to everyone.</li>
<li><strong>BCC (Blind Carbon Copy)</strong> — sends a copy of the email while hiding the recipient's address from others.</li>
<li><strong>Salutation</strong> — the greeting at the beginning of an email or letter.</li>
<li><strong>Email signature</strong> — a block of text automatically added to the end of every email, containing your contact details.</li>
<li><strong>Attachment</strong> — a file such as a document or image sent along with an email message.</li>
</ul>

<h2>Summary</h2>
<p>Professional email writing is a skill that opens doors to employment, business partnerships, and government services. Use clear subject lines, polite greetings, and concise paragraphs. Master CC and BCC to respect privacy, attach files with descriptive names, and create a signature that represents you well. Avoid slang, shouting in capitals, and angry replies. A well-written email reflects your professionalism before you ever meet the recipient in person.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/outlook">Microsoft Support — Outlook Email Basics</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/outlook-compose-send/">Microsoft Learn — Compose and Send Email</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.3 Calendar and Meeting Management',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create appointments and meetings in the Outlook calendar, set reminders, invite attendees, respond to meeting requests, and use recurring events to manage regular activities such as church services, PTA meetings, or weekly stock checks in your small business.</p>

<h2>The Outlook Calendar</h2>
<p>The calendar in Outlook is more than a digital diary. It helps you schedule your time, share availability with colleagues, and receive automatic reminders before important events. To open the calendar, click the <strong>Calendar</strong> icon at the bottom of the folder pane. You can view your schedule by day, work week, full week, or month.</p>
<p>Each coloured block on the calendar represents an appointment or meeting. Appointments are events that involve only you, such as "Prepare invoices" or "Study for Excel quiz." Meetings involve other people and include invitations sent by email. When someone invites you to a meeting, you receive an email with buttons to Accept, Tentative, or Decline. Your response updates both your calendar and the organiser's tracking list.</p>

<h2>Creating an Appointment</h2>
<p>To create a new appointment, click <strong>New Appointment</strong> on the Home tab. A window opens where you enter:</p>
<ul>
<li><strong>Subject</strong> — a short description of the event.</li>
<li><strong>Location</strong> — where the event happens. For online meetings, you might type "Microsoft Teams" or "Zoom."</li>
<li><strong>Start time</strong> and <strong>End time</strong> — the exact beginning and end.</li>
<li><strong>Reminder</strong> — a pop-up alert before the event. The default is fifteen minutes, but you can change it to one hour or one day.</li>
</ul>
<p>Click <strong>Save &amp; Close</strong> to add the appointment to your calendar. If you set a reminder, Outlook will alert you with a sound and a pop-up message at the chosen time. This is especially helpful during busy weeks when it is easy to forget a ZRA deadline or a supplier payment.</p>

<h2>Scheduling a Meeting with Others</h2>
<p>A meeting is an appointment that includes other people. Click <strong>New Meeting</strong> instead of New Appointment. The window looks similar, but it has a <strong>To</strong> field where you add the email addresses of attendees. Outlook sends each person an invitation email. When they respond, you see their status in the <strong>Scheduling Assistant</strong> tab.</p>
<p>The Scheduling Assistant shows everyone's free and busy times side by side, making it easy to pick a slot that works for the whole group. This is invaluable for coordinating a church committee meeting where members have different work shifts, or for setting a PTA meeting time that suits both teachers and working parents.</p>

<h2>Recurring Events</h2>
<p>Some events happen repeatedly. A weekly stock check every Monday morning, a monthly review with your accountant, or a daily reminder to backup your files. Instead of creating these one by one, use the <strong>Recurrence</strong> button. Choose how often the event repeats — daily, weekly, monthly, or yearly — and set an end date if needed. Outlook then creates every occurrence automatically.</p>
<p>For example, Mrs Banda sets a recurring appointment every Friday at 16:00 called "Weekly Sales Summary." She adds a reminder thirty minutes before so she has time to gather her receipt book and mobile money statements. Over the year, this simple habit gives her a clear record of her business performance.</p>

<h2>Worked Example: Planning a Church Fundraiser Meeting</h2>
<p>Mr Phiri is the treasurer of his church in Kalomo. He needs to organise a fundraising committee meeting to plan a building project. He uses Outlook as follows:</p>
<ol>
<li>He clicks <strong>New Meeting</strong> and types "Church Fundraiser Planning Meeting" in the Subject box.</li>
<li>In Location he writes "Church Hall, Kalomo."</li>
<li>He sets the date to next Saturday and the time from 14:00 to 16:00.</li>
<li>In the To field he adds the email addresses of the pastor, the choir leader, and three committee members.</li>
<li>He types a short message in the body explaining the agenda: reviewing the budget, assigning roles, and setting a deadline for ticket sales.</li>
<li>He clicks <strong>Send</strong>. Outlook sends invitations to everyone and places the meeting on his calendar.</li>
<li>He sets a reminder for one day before so he can prepare the financial report.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Open the Outlook Calendar and switch between Day, Work Week, and Month views.</li>
<li>Create an appointment titled "Study Time" for tomorrow at 18:00 for one hour. Set a reminder for thirty minutes before.</li>
<li>Create a recurring weekly appointment called "Weekly Stock Check" every Monday at 08:00. Set it to repeat for ten weeks.</li>
<li>Create a meeting invitation to two classmates. Set a subject, time, and location. Send it and check your Sent Items.</li>
<li>Respond to a meeting invitation you have received by clicking Accept, Tentative, or Decline. Observe how your calendar updates.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Appointment</strong> — a calendar event that involves only you.</li>
<li><strong>Meeting</strong> — a calendar event that includes other people who receive email invitations.</li>
<li><strong>Reminder</strong> — a pop-up alert that warns you before an event starts.</li>
<li><strong>Recurring event</strong> — an appointment or meeting that repeats automatically on a schedule you define.</li>
<li><strong>Scheduling Assistant</strong> — a tool that shows the availability of all meeting attendees so you can choose a suitable time.</li>
</ul>

<h2>Summary</h2>
<p>The Outlook calendar transforms how you manage time. By creating appointments, sending meeting invitations, setting reminders, and using recurring events, you ensure that deadlines, meetings, and responsibilities never slip through the cracks. Whether you are coordinating a church project, managing a small business, or juggling college classes, a well-maintained calendar is your most reliable assistant.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/calendar-in-outlook-9c2e65e2-d2a3-4f18-b30b-3285800b02d3">Microsoft Support — Outlook Calendar</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/outlook-manage-calendar/">Microsoft Learn — Manage Your Calendar</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.4 Contacts, Tasks and Rules',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to store and organise contact information in Outlook, create task lists with deadlines, mark tasks as complete, and set up email rules that automatically sort incoming messages into folders so your inbox stays tidy and nothing important is lost.</p>

<h2>Managing Contacts</h2>
<p>Your phone probably stores names and numbers, but Outlook contacts hold far more detail. Each contact card can include multiple phone numbers, email addresses, physical addresses, job titles, birthdays, and notes. This is ideal for a small-business owner who needs to remember a supplier's bank details, a customer's preferred delivery day, or an employee's NRC number.</p>
<p>To create a contact, click the <strong>People</strong> icon at the bottom of the Outlook window, then <strong>New Contact</strong>. Fill in the fields you need and click <strong>Save &amp; Close</strong>. You can group contacts into categories such as Customers, Suppliers, Family, and Church. To assign a category, open a contact, click <strong>Categorize</strong> on the Contact tab, and choose a colour. When you view your contact list, each person appears with a coloured tag that makes scanning quick and easy.</p>

<h2>Creating and Managing Tasks</h2>
<p>Tasks are to-do items with deadlines. Click the <strong>Tasks</strong> icon, then <strong>New Task</strong>. Give the task a subject, set a due date, and optionally add a reminder. Tasks appear in your task list and can be sorted by due date, priority, or status. When you finish a task, click the checkbox next to it. The task stays visible but is marked complete, giving you a satisfying record of what you have achieved.</p>
<p>For a market vendor, daily tasks might include "Order tomatoes from Chipata supplier," "Pay ZESCO bill before Friday," and "Update stock spreadsheet." For a student, tasks could be "Submit assignment by Wednesday," "Read Module 5 notes," and "Email lecturer about exam date." Using tasks alongside the calendar means you see both your fixed appointments and your flexible to-do items in one integrated view.</p>

<h2>Email Rules for Automatic Sorting</h2>
<p>As your business grows, your inbox fills with invoices, customer enquiries, newsletters, and personal messages. Reading through everything manually wastes time. Outlook <strong>rules</strong> solve this by acting on emails automatically based on conditions you set.</p>
<p>To create a rule, click <strong>Home &gt; Rules &gt; Manage Rules &amp; Alerts</strong>. Click <strong>New Rule</strong>. Choose a template such as "Move messages from someone to a folder." Then define the condition: for example, "from edutrackzambia@gmail.com." Choose the action: "move it to the Edutrack folder." Click Finish. From now on, every email from that address goes straight to the chosen folder, leaving your inbox free for new messages.</p>
<p>Practical rules for a Zambian business owner:</p>
<ul>
<li>Move all emails from your biggest supplier to a "Suppliers" folder.</li>
<li>Flag emails with "invoice" in the subject line with a red category.</li>
<li>Forward emails from ZRA to your accountant automatically.</li>
<li>Move newsletters and promotions to a "Read Later" folder so they do not distract you during work hours.</li>
</ul>

<h2>Worked Example: Organising a Small Shop</h2>
<p>Mrs Lungu runs a grocery shop in Kalomo. She sets up Outlook to manage her business communication as follows:</p>
<ol>
<li>She creates contact cards for her three main suppliers, her landlord, and her ZRA contact. Each card includes the phone number, email, and physical address.</li>
<li>She creates a task called "Pay rent by the 5th" with a due date and a reminder two days before.</li>
<li>She creates a recurring task every Monday called "Check stock levels and place orders."</li>
<li>She creates a rule that moves every email with "invoice" in the subject to a folder named "Invoices 2025."</li>
<li>She creates another rule that moves emails from her biggest supplier to a "Choma Suppliers" folder.</li>
</ol>
<p>Now Mrs Lungu opens Outlook each morning and sees only new, unclassified emails in her inbox. Everything else is already sorted. Her tasks remind her of deadlines, and her contacts are available instantly when a customer asks for a supplier's number.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open the People view and create three new contacts: a fictional supplier, a customer, and a colleague. Include at least a name, phone number, and email address for each.</li>
<li>Assign a different category colour to each contact.</li>
<li>Open the Tasks view and create two tasks: one due tomorrow and one due next week. Set a reminder on one of them.</li>
<li>Mark one task as complete and observe how it changes in the list.</li>
<li>Create a rule that moves all emails from your own email address to a "Practice" folder. Send yourself a test email and confirm the rule works.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Contact</strong> — a record in Outlook storing a person's name, phone, email, and other details.</li>
<li><strong>Category</strong> — a colour-coded label used to organise contacts, emails, or tasks.</li>
<li><strong>Task</strong> — a to-do item with a subject, due date, and optional reminder.</li>
<li><strong>Rule</strong> — an automated instruction that tells Outlook what to do with incoming emails.</li>
<li><strong>Due date</strong> — the deadline by which a task should be finished.</li>
</ul>

<h2>Summary</h2>
<p>Outlook contacts, tasks, and rules transform email from a chaotic inbox into an organised command centre. Contacts keep your network at your fingertips. Tasks ensure deadlines are met. Rules sort messages automatically so you focus on what matters. Together, these tools give any small-business owner, student, or office worker the structure needed to succeed in a busy digital world.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/outlook">Microsoft Support — Outlook People and Tasks</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/outlook-organize/">Microsoft Learn — Organize Outlook</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.5 Outlook Module Assessment',
                'duration_minutes' => 45,
                'lesson_type' => 'Quiz',
                'content' => <<<'HTML'
<p>This quiz covers Microsoft Outlook basics, professional email writing, calendar management, and organising contacts and tasks.</p>
HTML,
            ],
        ];
    }


    // ------------------------------------------------------------------
    // Module 5: Advanced Excel and Office Integration — Lessons
    // ------------------------------------------------------------------

    private function advancedExcelLessons(): array
    {
        return [
            [
                'title' => '5.1 Advanced Formulas and Functions',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to use advanced Excel functions including IF, VLOOKUP, COUNTIF, SUMIF, and text functions to build spreadsheets that automatically calculate stock values, grade student marks, analyse sales data, and flag overdue payments for a small business in Zambia.</p>

<h2>Beyond Basic Formulas</h2>
<p>You already know how to add, subtract, multiply, and divide in Excel. Basic formulas such as =A1+B1 are useful, but real-world problems demand smarter tools. A shop owner who sells tomatoes, onions, and dried fish needs to know which item makes the most profit. A teacher needs to convert raw marks into letter grades. A church treasurer needs to count how many members paid tithe each month. Advanced formulas handle all of this automatically.</p>

<h2>The IF Function</h2>
<p>The IF function makes decisions. It checks whether a condition is true or false, then returns one value if true and another if false. The structure is:</p>
<blockquote>
<p>=IF(condition, value_if_true, value_if_false)</p>
</blockquote>
<p>Suppose Mrs Banda offers a K5 discount to any customer who buys more than ten items. In cell D2 she types:</p>
<blockquote>
<p>=IF(C2&gt;10, "Discount K5", "No discount")</p>
</blockquote>
<p>If the quantity in cell C2 is greater than ten, Excel writes "Discount K5." Otherwise it writes "No discount." Nested IF statements allow more than two outcomes. For example, a teacher might use:</p>
<blockquote>
<p>=IF(B2&gt;=80, "A", IF(B2&gt;=70, "B", IF(B2&gt;=60, "C", "Fail")))</p>
</blockquote>
<p>This converts a percentage into a grade. Be careful with nested IFs: too many levels become hard to read. For complex logic, consider breaking the calculation into several cells.</p>

<h2>VLOOKUP</h2>
<p>VLOOKUP searches for a value in the leftmost column of a table and returns a value from the same row in a column you specify. The structure is:</p>
<blockquote>
<p>=VLOOKUP(lookup_value, table_array, col_index_num, range_lookup)</p>
</blockquote>
<p>Imagine Mr Zulu has a price list in cells A2:C10. Column A contains product names, column B contains unit prices, and column C contains stock quantities. In another sheet he wants to type a product name and see the price automatically. He types:</p>
<blockquote>
<p>=VLOOKUP("Tomatoes", A2:C10, 2, FALSE)</p>
</blockquote>
<p>Excel searches for "Tomatoes" in column A, finds the row, and returns the value from the second column, which is the price. The word FALSE means Excel must find an exact match. If the product is not found, Excel shows #N/A. To avoid this error, wrap the formula in IFERROR:</p>
<blockquote>
<p>=IFERROR(VLOOKUP("Tomatoes", A2:C10, 2, FALSE), "Not found")</p>
</blockquote>

<h2>COUNTIF and SUMIF</h2>
<p>COUNTIF counts how many cells meet a condition. SUMIF adds up values that meet a condition. These are perfect for summary tables.</p>
<p>Example: a church treasurer tracks donations in column B and donor names in column A. To count how many donations exceeded K100:</p>
<blockquote>
<p>=COUNTIF(B2:B100, "&gt;100")</p>
</blockquote>
<p>To sum only the donations from a specific member named "Mrs Lungu":</p>
<blockquote>
<p>=SUMIF(A2:A100, "Mrs Lungu", B2:B100)</p>
</blockquote>

<h2>Text Functions</h2>
<p>Text functions clean and standardise data. CONCATENATE or the &amp; symbol joins text from multiple cells. UPPER converts text to capitals. PROPER capitalises the first letter of each word, which is useful for names.</p>
<blockquote>
<p>=PROPER("john zulu")</p>
</blockquote>
<p>This returns "John Zulu." If you have a list of customer names entered inconsistently, drag the PROPER formula down the column to standardise them all.</p>

<h2>Worked Example: Stock and Sales Summary</h2>
<p>Mr Phiri runs a hardware shop. He creates a spreadsheet with the following columns:</p>
<ul>
<li>A: Product name</li>
<li>B: Quantity in stock</li>
<li>C: Unit cost (what he paid)</li>
<li>D: Unit price (what he charges)</li>
<li>E: Stock value (=B*C)</li>
<li>F: Profit per unit (=D-C)</li>
<li>G: Reorder flag (=IF(B&lt;10, "Reorder", "OK"))</li>
</ul>
<p>With these formulas, Mr Phiri opens his spreadsheet each morning and sees instantly which items are running low. The stock value column tells him how much money is tied up in inventory. The profit column shows which products earn the most. All of this updates automatically whenever he changes a quantity or price.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open Excel and create a table with columns for Product, Quantity, and Unit Price.</li>
<li>Add an IF formula that writes "Low stock" if quantity is below five, otherwise "OK."</li>
<li>Create a small price lookup table on a second sheet. Use VLOOKUP to pull a price into your first sheet based on a product name you type.</li>
<li>Use SUMIF to calculate total sales for one specific product across ten rows of data.</li>
<li>Use PROPER to standardise a list of five names typed in lowercase.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>IF function</strong> — a formula that returns one value if a condition is true and another if it is false.</li>
<li><strong>VLOOKUP</strong> — a function that searches for a value in the first column of a table and returns a value from the same row.</li>
<li><strong>COUNTIF</strong> — a function that counts cells that meet a specified condition.</li>
<li><strong>SUMIF</strong> — a function that adds values only if they meet a specified condition.</li>
<li><strong>IFERROR</strong> — a function that displays a custom message instead of an error code.</li>
</ul>

<h2>Summary</h2>
<p>Advanced Excel formulas transform static tables into intelligent business tools. IF makes decisions, VLOOKUP retrieves data from tables, COUNTIF and SUMIF summarise information conditionally, and text functions clean your data. Once you master these functions, you can build spreadsheets that track stock, grade students, analyse church donations, and flag overdue payments with almost no manual work.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/excel">Microsoft Support — Excel Functions</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/excel-functions/">Microsoft Learn — Excel Functions</a></li>
<li><a href="https://www.w3schools.com/excel/">W3Schools — Excel Tutorial</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.2 Pivot Tables and Data Analysis',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create a pivot table from raw sales data, group and filter information, calculate totals and averages, and use the results to make informed decisions about stock, pricing, and marketing for a small business or community organisation in Zambia.</p>

<h2>What Is a Pivot Table?</h2>
<p>A pivot table is a powerful Excel tool that summarises large amounts of data in seconds. Imagine you have a year's worth of sales records: thousands of rows showing dates, products, quantities, prices, and customer names. Reading every row to find trends would take days. A pivot table does it instantly. It groups the data, counts items, adds up sales, finds averages, and presents everything in a clean table that you can rearrange by dragging fields.</p>
<p>The name "pivot" comes from the ability to rotate the table. One moment you see total sales by product. With a single drag, you see total sales by month. Another drag shows sales by customer. This flexibility makes pivot tables the most popular data-analysis feature in Excel.</p>

<h2>Creating Your First Pivot Table</h2>
<p>Before creating a pivot table, your raw data must be organised as a proper table with headers in the first row and no completely blank rows or columns. Each column should contain only one type of data: dates in one column, text in another, numbers in another.</p>
<p>Select any cell inside your data range. Then click <strong>Insert &gt; PivotTable</strong>. Excel asks where the data is and where to place the pivot table. Choose to place it on a new worksheet. A blank pivot table appears on the left, and the <strong>PivotTable Fields</strong> pane appears on the right.</p>
<p>The fields pane lists every column header from your data. At the bottom are four areas:</p>
<ul>
<li><strong>Filters</strong> — filters the entire report by a condition.</li>
<li><strong>Columns</strong> — places fields across the top of the table.</li>
<li><strong>Rows</strong> — places fields down the side of the table.</li>
<li><strong>Values</strong> — performs calculations such as sum, count, or average.</li>
</ul>

<h2>A Practical Example: Market Stall Sales</h2>
<p>Mrs Banda records every sale in a spreadsheet with columns for Date, Product, Quantity, Unit Price, and Total. After one month she has two hundred rows. She creates a pivot table to answer three questions.</p>
<p><strong>Question 1: Which product sells the most?</strong><br>
She drags "Product" to the Rows area and "Quantity" to the Values area. Excel automatically sums the quantities. The pivot table shows Tomatoes: 450, Onions: 320, Dried Fish: 180. Tomatoes are her best seller.</p>
<p><strong>Question 2: Which day of the week is busiest?</strong><br>
She drags "Date" to the Rows area and right-clicks a date to choose <strong>Group &gt; Days</strong>. She drags "Total" to the Values area. The pivot table shows Saturday generated K1,200 while Tuesday generated only K350. She decides to open an extra half-day on Saturdays.</p>
<p><strong>Question 3: What is the average sale value?</strong><br>
She drags "Total" to the Values area, then clicks the field and chooses <strong>Value Field Settings &gt; Average</strong>. The result is K45 per transaction. She uses this to estimate how many customers she needs per day to reach her weekly target.</p>

<h2>Filtering and Slicing</h2>
<p>Pivot tables include built-in filters. Click the drop-down arrow next to any row or column header to show or hide specific items. For deeper analysis, add a <strong>Slicer</strong>. Click inside the pivot table, then choose <strong>Insert &gt; Slicer</strong>. Select the field you want to filter, such as "Product." A panel of buttons appears. Click a button to filter the entire pivot table instantly. Slicers are especially useful when presenting data to a group because they make the spreadsheet interactive.</p>

<h2>Worked Example: Church Tithe Analysis</h2>
<p>The treasurer of a church in Kalomo has a spreadsheet with columns for Member Name, Month, and Amount. He wants to see total tithes per member and per quarter. He creates a pivot table, drags "Member Name" to Rows, "Month" to Columns, and "Amount" to Values. The result is a grid showing each member's contribution across twelve months. He drags "Quarter" to the Filters area and selects Q1 to focus on January to March. He discovers that fifteen members gave consistently every month, while ten gave only occasionally. The church leadership uses this insight to plan personal follow-up visits.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a simple sales table with at least five columns: Date, Product, Quantity, Price, and Total. Enter twenty rows of sample data.</li>
<li>Insert a pivot table on a new worksheet. Show total sales by product.</li>
<li>Rearrange the pivot table to show total sales by month.</li>
<li>Add a slicer for the Product field and use it to filter the report.</li>
<li>Change the Values calculation from Sum to Average and observe how the report changes.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Pivot table</strong> — an Excel tool that summarises and analyses large data sets by grouping and calculating values.</li>
<li><strong>Field</strong> — a column from your source data used in a pivot table.</li>
<li><strong>Values area</strong> — the part of a pivot table where calculations such as sum, count, or average are performed.</li>
<li><strong>Slicer</strong> — a visual filter panel that lets you quickly show or hide data in a pivot table.</li>
<li><strong>Grouping</strong> — combining individual data points into categories such as months, quarters, or product types.</li>
</ul>

<h2>Summary</h2>
<p>Pivot tables are the fastest way to turn raw data into meaningful insights. By dragging fields between rows, columns, and values, you can analyse sales, donations, expenses, or any other numerical data in seconds. Filters and slicers let you explore the data interactively. For any small-business owner, treasurer, or student who works with numbers, pivot tables are an indispensable skill.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/create-a-pivottable-to-analyze-worksheet-data-a9a84538-bfe9-40a9-a8e9-f99134456576">Microsoft Support — Create a PivotTable</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/excel-pivottables/">Microsoft Learn — PivotTables</a></li>
<li><a href="https://www.w3schools.com/excel/">W3Schools — Excel Tutorial</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.3 Linking Word, Excel and PowerPoint',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to copy data and charts from Excel into Word and PowerPoint, create live links so that updates in Excel automatically refresh in other documents, embed spreadsheets inside reports, and build professional presentations and documents that draw on multiple Office applications.</p>

<h2>Why Link Applications?</h2>
<p>Real-world documents rarely live in one program alone. A business proposal in Word might include a budget table from Excel and a strategy chart from PowerPoint. A school report card in Word might pull student marks from an Excel sheet. A church annual report might combine a Word narrative, Excel financial tables, and PowerPoint slides for the AGM presentation. Instead of recreating content in each program, you link the applications so they share information automatically.</p>

<h2>Copying and Pasting with Linking</h2>
<p>The simplest link is a copy-and-paste operation that maintains a connection to the source. In Excel, select the cells or chart you want to share. Press <strong>Ctrl+C</strong> to copy. Switch to Word or PowerPoint. Click the drop-down arrow below the Paste button and choose <strong>Paste Special</strong>. Select <strong>Paste Link</strong> and choose the format. Microsoft Excel Worksheet Object is the best choice for editable tables. Picture format is best for charts that should not be altered accidentally.</p>
<p>When you paste a link, the object in Word or PowerPoint is connected to the original Excel file. If you later open Excel, change the numbers, and save the file, the linked table in Word updates automatically the next time you open the Word document. This saves hours of manual copying and prevents errors caused by outdated figures.</p>

<h2>Embedding an Excel Spreadsheet</h2>
<p>Sometimes you want the Excel data inside the Word document but not connected to an external file. This is called <strong>embedding</strong>. The data becomes part of the Word file. To embed, use Paste Special and choose <strong>Paste</strong> (not Paste Link) with the Microsoft Excel Worksheet Object format. Double-click the embedded table in Word and a miniature Excel ribbon appears, letting you edit formulas and formatting as if you were in Excel. When you click outside the table, it returns to normal Word editing mode.</p>
<p>Embedding is useful when you need to send a document to someone who does not have access to the original Excel file. The downside is that the Word file becomes larger, and changes made in the original Excel file do not flow through to the embedded copy.</p>

<h2>Linking Charts to PowerPoint</h2>
<p>PowerPoint presentations often contain charts that change every month. Instead of remaking slides, link the charts directly to Excel. Copy the chart in Excel, switch to PowerPoint, and choose Paste Special &gt; Paste Link &gt; Microsoft Excel Chart Object. The chart appears in the slide. When the underlying data in Excel changes, right-click the chart in PowerPoint and choose <strong>Update Link</strong>, or set PowerPoint to update links automatically when the presentation opens.</p>
<p>For a business owner presenting monthly sales to a bank manager, this means the PowerPoint slides are always current. There is no risk of showing last month's chart by mistake. The bank sees accurate, professional data that builds trust.</p>

<h2>Worked Example: A Small Business Annual Report</h2>
<p>Mrs Zulu owns a poultry business. Each year she submits a report to her cooperative. She builds the report as follows:</p>
<ol>
<li>In Excel she maintains a workbook called "Annual_Accounts_2025.xlsx" with sheets for Income, Expenses, and Profit Summary.</li>
<li>In Word she writes the narrative: an introduction, achievements, challenges, and plans for next year.</li>
<li>She copies the Profit Summary table from Excel and pastes it as a linked object into the Word report. The table updates if she corrects any figures.</li>
<li>She copies a bar chart showing monthly egg sales from Excel and pastes it as a linked picture into the Word report.</li>
<li>For the cooperative meeting, she creates a PowerPoint presentation. She links the same charts directly from Excel into the slides.</li>
<li>She saves all three files in one folder named "Annual_Report_2025." When she updates the Excel file, both Word and PowerPoint reflect the changes.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Create a small table in Excel with three rows of data and a simple chart.</li>
<li>Copy the table and paste it as a linked object into a new Word document. Change the numbers in Excel, save, and reopen Word to see the update.</li>
<li>Copy the chart and paste it as a linked picture into a PowerPoint slide. Update the Excel data and refresh the link in PowerPoint.</li>
<li>Embed an Excel table directly into Word using Paste Special without linking. Double-click it and edit a cell value.</li>
<li>Save all three files and observe the file sizes. Notice that the embedded version makes the Word file larger.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Paste Link</strong> — pasting content while maintaining a live connection to the original file so updates flow through automatically.</li>
<li><strong>Embed</strong> — inserting a copy of an object into another document so it becomes part of that file.</li>
<li><strong>Linked object</strong> — content in one program that remains connected to its source file in another program.</li>
<li><strong>Update Link</strong> — refreshing a linked object manually to pull the latest data from the source file.</li>
<li><strong>Paste Special</strong> — an advanced paste option that lets you choose the format and linking behaviour.</li>
</ul>

<h2>Summary</h2>
<p>Linking Word, Excel, and PowerPoint turns separate documents into a unified system. Linked objects stay current when source data changes. Embedded objects travel with the document but do not update automatically. Charts, tables, and text can flow between programs, saving time and eliminating copy-paste errors. For anyone who prepares reports, proposals, or presentations, mastering Office integration is a professional advantage.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/copy-excel-data-or-charts-to-word-9af8d56e-6f27-4b6f-8f85-62e010cde1fa">Microsoft Support — Copy Excel Data to Word</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/integrate-office-apps/">Microsoft Learn — Integrate Office Apps</a></li>
<li><a href="https://www.w3schools.com/excel/">W3Schools — Excel Tutorial</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.4 Mail Merge and Automation Basics',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to set up a mail merge in Microsoft Word using an Excel data source, create personalised letters, labels, and envelopes, and record a simple macro in Excel to automate repetitive tasks such as formatting reports or applying standard calculations.</p>

<h2>What Is Mail Merge?</h2>
<p>Mail merge is a feature that takes a single letter or document and creates many personalised copies automatically. Instead of typing each recipient's name and address manually, you write the letter once and tell Word to pull names, addresses, and other details from a list. This is invaluable for churches sending personalised invitation letters, businesses mailing invoices, schools producing report cards, and political parties distributing campaign materials.</p>
<p>The process needs two files: a <strong>main document</strong> in Word and a <strong>data source</strong> in Excel or Outlook contacts. The main document contains the text that stays the same for everyone. The data source contains the information that changes, such as names, addresses, and account numbers. Special fields called <strong>merge fields</strong> tell Word exactly where to insert each piece of data.</p>

<h2>Preparing the Data Source</h2>
<p>Open Excel and create a table with clear headers in the first row. Each column should represent one piece of information. For a church invitation, your columns might be:</p>
<ul>
<li>FirstName</li>
<li>LastName</li>
<li>Address</li>
<li>Phone</li>
<li>MemberSince</li>
</ul>
<p>Enter one row per recipient. Do not leave blank rows between data. Save the file with a clear name such as "Church_Members_List.xlsx." Close Excel before starting the merge in Word.</p>

<h2>Setting Up the Main Document</h2>
<p>Open Word and type the body of your letter. Leave blank spaces where personalised information will appear. For example:</p>
<blockquote>
<p>Dear [FirstName],</p>
<p>We are pleased to invite you to our annual church fundraiser on Saturday, 15 August 2025, at 14:00 in the church hall.</p>
<p>As a valued member since [MemberSince], your presence and support mean a great deal to our congregation.</p>
<p>Kind regards,<br>
The Church Committee</p>
</blockquote>
<p>Click the <strong>Mailings</strong> tab, then <strong>Start Mail Merge &gt; Letters</strong>. Click <strong>Select Recipients &gt; Use an Existing List</strong> and browse to your Excel file. Word links to the spreadsheet. Now click <strong>Insert Merge Field</strong> and place each field in the correct spot in your letter. Replace "[FirstName]" with the actual merge field <<FirstName>>. Replace "[MemberSince]" with <<MemberSince>>.</p>

<h2>Previewing and Completing the Merge</h2>
<p>Click <strong>Preview Results</strong> on the Mailings tab. Word shows the first recipient's data inserted into the letter. Use the arrow buttons to scroll through recipients and check that everything looks correct. If a name is missing or an address is wrong, fix it in the Excel file, save, and refresh the link in Word.</p>
<p>When you are satisfied, click <strong>Finish &amp; Merge &gt; Edit Individual Documents</strong>. Choose <strong>All</strong> records. Word generates a new document containing every personalised letter, one per page. Save this document and print it, or save each page as a separate PDF if you need to email them individually.</p>

<h2>Recording a Simple Macro</h2>
<p>A macro is a recording of actions that Excel can replay automatically. If you format a report the same way every week, a macro does it in one click. To record a macro, click the <strong>View</strong> tab, then <strong>Macros &gt; Record Macro</strong>. Give the macro a name such as "FormatReport" and choose to store it in <strong>This Workbook</strong>. Click OK. Excel starts recording everything you do.</p>
<p>Perform the actions you want to automate: select a range, apply bold headings, set number formats, add borders, and adjust column widths. When finished, click <strong>Macros &gt; Stop Recording</strong>. To run the macro later, click <strong>Macros &gt; View Macros</strong>, select "FormatReport," and click <strong>Run</strong>. The entire sequence happens instantly.</p>

<h2>Worked Example: School Report Cards</h2>
<p>A teacher at a private school in Kalomo needs to produce end-of-term report cards for forty students. She prepares an Excel sheet with columns for StudentName, Class, Mathematics, English, Science, and Total. In Word she designs a report card template with the school logo, a greeting, and spaces for each subject mark. She uses mail merge to pull each student's name and marks from Excel. She adds an IF field in Word to display "Pass" or "Repeat" based on the total. In under an hour she produces forty individual report cards that would have taken days to type by hand.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create an Excel list with five fictional names, addresses, and phone numbers. Save and close the file.</li>
<li>Open Word and write a short letter inviting someone to a workshop. Leave spaces for the name and address.</li>
<li>Use the Mailings tab to link your Excel file and insert merge fields for name and address.</li>
<li>Preview the results, then complete the merge to produce five personalised letters.</li>
<li>Open a new Excel workbook, record a macro that formats a range with bold headings and borders, then run the macro on a different range.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Mail merge</strong> — a Word feature that creates multiple personalised documents from a single template and a data source.</li>
<li><strong>Merge field</strong> — a placeholder in the main document that tells Word where to insert data from the source.</li>
<li><strong>Data source</strong> — a file such as an Excel spreadsheet or Outlook contacts list that contains the information to be merged.</li>
<li><strong>Macro</strong> — a recorded sequence of actions that can be replayed automatically to save time.</li>
<li><strong>Main document</strong> — the Word template containing the text and merge fields for a mail merge.</li>
</ul>

<h2>Summary</h2>
<p>Mail merge and macros are automation superpowers. Mail merge turns a single letter into hundreds of personalised documents, perfect for invitations, invoices, and reports. Macros record repetitive actions and replay them instantly, freeing you from tedious formatting. Together, these tools let you work faster, reduce errors, and present a professional image whether you run a church, a school, or a small business.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/use-mail-merge-for-bulk-email-letters-labels-and-envelopes-f488ed5b-b8c9-4df3-9f40-9b4ab6532425">Microsoft Support — Mail Merge</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/word-mail-merge/">Microsoft Learn — Word Mail Merge</a></li>
<li><a href="https://support.microsoft.com/en-us/office/automate-tasks-with-the-macro-recorder-974ef220-f716-4e01-b015-3ea70e64937b">Microsoft Support — Macro Recorder</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.5 Advanced Excel and Integration Assessment',
                'duration_minutes' => 45,
                'lesson_type' => 'Quiz',
                'content' => <<<'HTML'
<p>This quiz covers advanced Excel formulas, pivot tables, linking Office applications, mail merge, and macro basics.</p>
HTML,
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Quizzes
    // ------------------------------------------------------------------

    private function wordQuiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: Microsoft Word Mastery',
            'description' => 'Test your understanding of Microsoft Word basics, formatting, tables, and document layout.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which tab in Microsoft Word contains the Bold, Italic, and Underline buttons?',
                    'explanation' => 'The Home tab contains the font formatting group, including Bold, Italic, Underline, and font colour options.',
                    'options' => [
                        ['text' => 'Insert', 'is_correct' => false],
                        ['text' => 'Home', 'is_correct' => true],
                        ['text' => 'Design', 'is_correct' => false],
                        ['text' => 'View', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the quickest way to save a document in Word?',
                    'explanation' => 'Pressing Ctrl+S saves the current document immediately without needing to navigate menus.',
                    'options' => [
                        ['text' => 'Click File then Close', 'is_correct' => false],
                        ['text' => 'Press Ctrl+S', 'is_correct' => true],
                        ['text' => 'Press Ctrl+P', 'is_correct' => false],
                        ['text' => 'Click the X button', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which feature lets you create a list with numbers that increase automatically?',
                    'explanation' => 'Numbering creates an ordered list with sequential numbers, while bullets create unordered lists.',
                    'options' => [
                        ['text' => 'Bullets', 'is_correct' => false],
                        ['text' => 'Numbering', 'is_correct' => true],
                        ['text' => 'Indent', 'is_correct' => false],
                        ['text' => 'Sort', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In Word, what is a "table" used for?',
                    'explanation' => 'A table organises information into rows and columns, making data such as prices, schedules, and lists easy to read.',
                    'options' => [
                        ['text' => 'To draw pictures', 'is_correct' => false],
                        ['text' => 'To organise data in rows and columns', 'is_correct' => true],
                        ['text' => 'To change page colours', 'is_correct' => false],
                        ['text' => 'To add music', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'The ruler in Word can be used to adjust left and right margins.',
                    'explanation' => 'The horizontal ruler displays margin boundaries and allows you to drag them to change margins directly.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Word documents can only be saved as .docx files.',
                    'explanation' => 'Word supports many formats including .docx, .pdf, .txt, and .rtf. You can save as PDF using File > Save As.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the keyboard shortcut to undo the last action in Word? (two keys separated by a plus sign)',
                    'explanation' => 'Ctrl+Z reverses the most recent action, such as accidental deletion or formatting changes.',
                    'correct_answer' => 'Ctrl+Z',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which view shows how a document will look when printed, including headers and footers?',
                    'explanation' => 'Print Layout view displays the document exactly as it will appear on the printed page, including margins and headers.',
                    'options' => [
                        ['text' => 'Draft view', 'is_correct' => false],
                        ['text' => 'Web Layout view', 'is_correct' => false],
                        ['text' => 'Print Layout view', 'is_correct' => true],
                        ['text' => 'Outline view', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What happens when you press Ctrl+B while text is selected?',
                    'explanation' => 'Ctrl+B is the keyboard shortcut to apply bold formatting to the currently selected text.',
                    'options' => [
                        ['text' => 'The text becomes italic', 'is_correct' => false],
                        ['text' => 'The text becomes bold', 'is_correct' => true],
                        ['text' => 'The text is deleted', 'is_correct' => false],
                        ['text' => 'The text is underlined', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function excelQuiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Microsoft Excel Mastery',
            'description' => 'Test your understanding of Excel cells, formulas, functions, charts, and data analysis basics.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In Excel, what does the function =SUM(A1:A5) do?',
                    'explanation' => 'The SUM function adds all the numbers in the range from A1 to A5 inclusive.',
                    'options' => [
                        ['text' => 'Multiplies the values in A1 to A5', 'is_correct' => false],
                        ['text' => 'Adds the values in A1 to A5', 'is_correct' => true],
                        ['text' => 'Finds the average of A1 to A5', 'is_correct' => false],
                        ['text' => 'Counts the cells in A1 to A5', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the correct formula to calculate the average of cells B1 through B10?',
                    'explanation' => 'The AVERAGE function calculates the arithmetic mean of the numbers in the specified range.',
                    'options' => [
                        ['text' => '=TOTAL(B1:B10)', 'is_correct' => false],
                        ['text' => '=AVERAGE(B1:B10)', 'is_correct' => true],
                        ['text' => '=MEAN(B1:B10)', 'is_correct' => false],
                        ['text' => '=SUM(B1:B10)/2', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which chart type is best for showing how parts make up a whole?',
                    'explanation' => 'A pie chart divides a circle into slices proportional to each category, making it ideal for showing percentages of a total.',
                    'options' => [
                        ['text' => 'Line chart', 'is_correct' => false],
                        ['text' => 'Bar chart', 'is_correct' => false],
                        ['text' => 'Pie chart', 'is_correct' => true],
                        ['text' => 'Scatter chart', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In Excel, what happens when you drag the fill handle (the small square at the bottom-right of a selected cell) downward?',
                    'explanation' => 'Dragging the fill handle copies the content or extends a series, such as dates or numbers, into adjacent cells.',
                    'options' => [
                        ['text' => 'It deletes the cell contents', 'is_correct' => false],
                        ['text' => 'It copies or extends the data into adjacent cells', 'is_correct' => true],
                        ['text' => 'It opens the Format Cells dialog', 'is_correct' => false],
                        ['text' => 'It inserts a new row', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A formula in Excel must always begin with an equals sign (=).',
                    'explanation' => 'Excel recognises formulas by the equals sign at the beginning. Without it, the entry is treated as plain text.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Excel can only store numbers and cannot store text.',
                    'explanation' => 'Excel stores text, numbers, dates, formulas, and many other data types in its cells.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the name of the intersection of a row and a column in Excel? (one word)',
                    'explanation' => 'A cell is the basic unit of a worksheet, formed where one row and one column intersect.',
                    'correct_answer' => 'Cell',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which keyboard shortcut selects all cells in the current worksheet?',
                    'explanation' => 'Ctrl+A selects all cells in the current region, and pressing it a second time selects the entire worksheet.',
                    'options' => [
                        ['text' => 'Ctrl+C', 'is_correct' => false],
                        ['text' => 'Ctrl+A', 'is_correct' => true],
                        ['text' => 'Ctrl+Z', 'is_correct' => false],
                        ['text' => 'Ctrl+S', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does the function =MAX(C1:C10) return?',
                    'explanation' => 'The MAX function finds the largest number in the specified range.',
                    'options' => [
                        ['text' => 'The smallest number in C1 to C10', 'is_correct' => false],
                        ['text' => 'The largest number in C1 to C10', 'is_correct' => true],
                        ['text' => 'The total of C1 to C10', 'is_correct' => false],
                        ['text' => 'The count of cells in C1 to C10', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function presentationQuiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Presentation and Design',
            'description' => 'Test your understanding of PowerPoint slides, design principles, transitions, and Publisher basics.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In PowerPoint, what is a "slide"?',
                    'explanation' => 'A slide is a single page in a presentation, similar to a page in a document but designed for screen display.',
                    'options' => [
                        ['text' => 'A type of chart', 'is_correct' => false],
                        ['text' => 'A single page in a presentation', 'is_correct' => true],
                        ['text' => 'A file format', 'is_correct' => false],
                        ['text' => 'A font style', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which PowerPoint feature lets you add movement between slides?',
                    'explanation' => 'Transitions control the visual effect when moving from one slide to the next, such as fade, push, or dissolve.',
                    'options' => [
                        ['text' => 'Animation', 'is_correct' => false],
                        ['text' => 'Transition', 'is_correct' => true],
                        ['text' => 'Theme', 'is_correct' => false],
                        ['text' => 'Slide Master', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the purpose of Slide Show view in PowerPoint?',
                    'explanation' => 'Slide Show view displays the presentation full-screen so the audience can see it clearly during a meeting or lecture.',
                    'options' => [
                        ['text' => 'To edit text', 'is_correct' => false],
                        ['text' => 'To print handouts', 'is_correct' => false],
                        ['text' => 'To present slides to an audience full-screen', 'is_correct' => true],
                        ['text' => 'To insert new slides', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a good design practice for presentations?',
                    'explanation' => 'Simple, uncluttered slides with readable fonts help the audience focus on your message rather than struggling to read.',
                    'options' => [
                        ['text' => 'Use as many colours as possible on every slide', 'is_correct' => false],
                        ['text' => 'Write every word you plan to say on the slide', 'is_correct' => false],
                        ['text' => 'Keep slides simple with large, readable fonts', 'is_correct' => true],
                        ['text' => 'Use at least five different fonts per slide', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Microsoft Publisher is mainly used to create professional documents such as brochures, flyers, and newsletters.',
                    'explanation' => 'Publisher is a desktop publishing program designed for creating multi-page documents with rich layouts, unlike Word which is better for linear text.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Animations in PowerPoint control how objects move on a single slide, while transitions control movement between slides.',
                    'explanation' => 'Animations affect individual elements on one slide, whereas transitions affect the entire slide as it appears or disappears.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the keyboard shortcut to start a PowerPoint slide show from the beginning? (one key)',
                    'explanation' => 'Pressing F5 starts the slide show from the first slide, while Shift+F5 starts from the current slide.',
                    'correct_answer' => 'F5',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which file extension is used for PowerPoint presentations by default?',
                    'explanation' => 'The .pptx extension is the default format for PowerPoint presentations created in modern versions of Microsoft Office.',
                    'options' => [
                        ['text' => '.docx', 'is_correct' => false],
                        ['text' => '.xlsx', 'is_correct' => false],
                        ['text' => '.pptx', 'is_correct' => true],
                        ['text' => '.pdf', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In PowerPoint, where do you change the background design for all slides at once?',
                    'explanation' => 'The Slide Master controls the default layout, fonts, colours, and background for every slide in the presentation.',
                    'options' => [
                        ['text' => 'Slide Show tab', 'is_correct' => false],
                        ['text' => 'View tab > Slide Master', 'is_correct' => true],
                        ['text' => 'Insert tab', 'is_correct' => false],
                        ['text' => 'Review tab', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function outlookQuiz(): array
    {
        return [
            'title' => 'Module 4 Quiz: Microsoft Outlook',
            'description' => 'Test your knowledge of Outlook email, calendar, contacts, tasks, and rules.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => "Which Outlook feature sends a copy of an email while hiding the recipient's address from others?",
                    'explanation' => 'BCC, or Blind Carbon Copy, hides the recipient email address so other recipients cannot see it.',
                    'options' => [
                        ['text' => 'CC', 'is_correct' => false],
                        ['text' => 'BCC', 'is_correct' => true],
                        ['text' => 'Reply All', 'is_correct' => false],
                        ['text' => 'Forward', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the purpose of the Outlook Calendar?',
                    'explanation' => 'The Calendar stores appointments, meetings, and events, and can send automatic reminders before they start.',
                    'options' => [
                        ['text' => 'To send emails', 'is_correct' => false],
                        ['text' => 'To store passwords', 'is_correct' => false],
                        ['text' => 'To schedule appointments and meetings', 'is_correct' => true],
                        ['text' => 'To edit documents', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'When you receive a meeting invitation in Outlook, which of these is NOT a valid response?',
                    'explanation' => 'Outlook meeting responses are Accept, Tentative, and Decline. "Ignore" is not a standard response button.',
                    'options' => [
                        ['text' => 'Accept', 'is_correct' => false],
                        ['text' => 'Tentative', 'is_correct' => false],
                        ['text' => 'Decline', 'is_correct' => false],
                        ['text' => 'Ignore', 'is_correct' => true],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does a recurring appointment do in Outlook?',
                    'explanation' => 'A recurring appointment repeats automatically on a schedule you define, such as every Monday or every first day of the month.',
                    'options' => [
                        ['text' => 'It sends emails every day', 'is_correct' => false],
                        ['text' => 'It repeats automatically on a set schedule', 'is_correct' => true],
                        ['text' => 'It deletes old calendar entries', 'is_correct' => false],
                        ['text' => 'It imports contacts from Excel', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'An email signature appears automatically at the bottom of every email you send.',
                    'explanation' => 'Once configured under File > Options > Mail > Signatures, Outlook appends your signature to every outgoing message.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Outlook rules can only be used to delete spam emails.',
                    'explanation' => 'Rules can move emails to folders, flag messages, forward emails, categorise items, and much more, not just delete spam.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What do the letters CC stand for in an email? (two words)',
                    'explanation' => 'CC stands for Carbon Copy, a term from the days of typewriters when carbon paper was used to make copies.',
                    'correct_answer' => 'Carbon Copy',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which Outlook view lets you manage and categorise your list of people and their contact details?',
                    'explanation' => 'The People view, sometimes called Contacts, is where you store names, phone numbers, email addresses, and other details.',
                    'options' => [
                        ['text' => 'Mail view', 'is_correct' => false],
                        ['text' => 'Calendar view', 'is_correct' => false],
                        ['text' => 'People view', 'is_correct' => true],
                        ['text' => 'Tasks view', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the safest practice when writing an email to a government office such as ZRA?',
                    'explanation' => 'A clear subject line, polite tone, and complete contact details show professionalism and help the recipient respond efficiently.',
                    'options' => [
                        ['text' => 'Write in ALL CAPS to show urgency', 'is_correct' => false],
                        ['text' => 'Use slang to appear friendly', 'is_correct' => false],
                        ['text' => 'Use a clear subject line, polite greeting, and complete contact details', 'is_correct' => true],
                        ['text' => 'Leave the subject line blank', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function advancedExcelQuiz(): array
    {
        return [
            'title' => 'Module 5 Quiz: Advanced Excel and Office Integration',
            'description' => 'Test your understanding of advanced formulas, pivot tables, linking Office applications, mail merge, and macros.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which function returns one value if a condition is true and another value if it is false?',
                    'explanation' => 'The IF function evaluates a logical condition and returns different results depending on whether the condition is true or false.',
                    'options' => [
                        ['text' => 'SUM', 'is_correct' => false],
                        ['text' => 'IF', 'is_correct' => true],
                        ['text' => 'AVERAGE', 'is_correct' => false],
                        ['text' => 'COUNT', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does the VLOOKUP function do?',
                    'explanation' => 'VLOOKUP searches for a value in the first column of a table and returns a corresponding value from a specified column in the same row.',
                    'options' => [
                        ['text' => 'It counts the number of cells in a range', 'is_correct' => false],
                        ['text' => 'It looks up a value in a table and returns data from the same row', 'is_correct' => true],
                        ['text' => 'It creates a chart automatically', 'is_correct' => false],
                        ['text' => 'It merges two workbooks', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which Excel tool lets you summarise a large data set by dragging fields into rows, columns, and values areas?',
                    'explanation' => 'A pivot table summarises large data sets interactively by grouping and calculating values based on fields you drag into different areas.',
                    'options' => [
                        ['text' => 'Data Validation', 'is_correct' => false],
                        ['text' => 'Conditional Formatting', 'is_correct' => false],
                        ['text' => 'PivotTable', 'is_correct' => true],
                        ['text' => 'Sort and Filter', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'When you paste a linked Excel chart into PowerPoint, what happens if you update the original Excel data?',
                    'explanation' => 'A linked object stays connected to its source file, so changes in Excel flow through to PowerPoint when the link is refreshed.',
                    'options' => [
                        ['text' => 'The chart in PowerPoint updates automatically or when refreshed', 'is_correct' => true],
                        ['text' => 'The chart in PowerPoint is deleted', 'is_correct' => false],
                        ['text' => 'Nothing happens', 'is_correct' => false],
                        ['text' => 'Excel crashes', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A macro in Excel records a sequence of actions so they can be replayed automatically later.',
                    'explanation' => 'Macros are recordings of user actions that can be replayed with a single click or keyboard shortcut.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Mail merge in Word requires at least two files: a main document and a data source.',
                    'explanation' => 'Mail merge combines a Word template with a data source such as an Excel list or Outlook contacts to create personalised documents.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the name of the Excel function that adds values only if they meet a condition? (one word)',
                    'explanation' => 'SUMIF adds values in a range only when they satisfy a specified condition.',
                    'correct_answer' => 'SUMIF',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which paste option creates a live connection between Excel data and a Word document?',
                    'explanation' => 'Paste Link maintains a connection to the source file, so updates in Excel are reflected in the destination document.',
                    'options' => [
                        ['text' => 'Paste', 'is_correct' => false],
                        ['text' => 'Paste Link', 'is_correct' => true],
                        ['text' => 'Paste as Picture', 'is_correct' => false],
                        ['text' => 'Keep Text Only', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is a merge field in a mail merge?',
                    'explanation' => 'A merge field is a placeholder in the main document that Word replaces with data from the source list during the merge.',
                    'options' => [
                        ['text' => 'A field that deletes unwanted recipients', 'is_correct' => false],
                        ['text' => 'A placeholder that is replaced with data from the source list', 'is_correct' => true],
                        ['text' => 'A password field', 'is_correct' => false],
                        ['text' => 'A chart inserted from Excel', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function printSummary(): void
    {
        $this->command->info('Microsoft Office Suite content seeding complete.');
        $this->command->info("Modules created: {$this->modulesCreated} | Lessons created: {$this->lessonsCreated} | Quizzes created: {$this->quizzesCreated} | Questions created: {$this->questionsCreated}");

        if (! empty($this->skippedModules)) {
            $this->command->warn('Skipped modules (already exist): ' . implode(', ', $this->skippedModules));
        }

        if (! empty($this->skippedQuizzes)) {
            $this->command->warn('Skipped quizzes (already linked): ' . implode(', ', $this->skippedQuizzes));
        }
    }
}
