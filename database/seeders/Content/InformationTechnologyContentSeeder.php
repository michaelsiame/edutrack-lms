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

class InformationTechnologyContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Information Technology')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Information Technology" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in Information Technology already has modules. Skipping content seed.');
            return;
        }

        DB::transaction(function () use ($course) {
            $modules = $this->createModules();

            foreach ($modules as $moduleIndex => $module) {
                $moduleNumber = $moduleIndex + 1;
                $lessonsData = $this->{"module{$moduleNumber}Lessons"}();

                $lessonIds = [];
                $moduleDuration = 0;

                foreach ($lessonsData as $lessonIndex => $lessonData) {
                    $displayOrder = $lessonIndex + 1;
                    $isPreview = ($moduleIndex === 0 && $lessonIndex === 0) ? 1 : 0;
                    $duration = $lessonData['duration_minutes'];
                    $moduleDuration += $duration;

                    $lesson = Lesson::create([
                        'module_id' => $module->id,
                        'title' => $lessonData['title'],
                        'content' => $lessonData['content'] ?? null,
                        'lesson_type' => 'Reading',
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

                // Create module quiz
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

                    $quiz->questions()->attach($question->question_id, ['display_order' => $qIndex + 1]);
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
                'title' => 'Module 1: IT Foundations — From Computer Literacy to Information Technology',
                'description' => 'Understand the difference between computer literacy and IT, explore operating systems, and master file management for an office environment.',
            ],
            [
                'title' => 'Module 2: Office Networks and Connectivity',
                'description' => 'Learn how office networks operate, share printers and files securely, set up Wi-Fi, and troubleshoot everyday network problems.',
            ],
            [
                'title' => 'Module 3: Cloud Services and the Business Software Landscape',
                'description' => 'Use Google Workspace and OneDrive for real teamwork, and learn how to choose business software that fits a Zambian organisation.',
            ],
            [
                'title' => 'Module 4: System Administration, Support, and Smart IT Procurement',
                'description' => 'Manage user accounts and updates, protect data with backups, deliver professional IT support, and make wise IT purchasing decisions.',
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
                'title' => '1.1 What Is Information Technology?',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain the difference between computer literacy and information technology, describe the role of an IT professional in a small Zambian organisation, and identify how IT skills can improve business, education, and government services in your community.</p>

<h2>Computer Literacy Versus Information Technology</h2>
<p>Computer literacy means knowing how to use a computer and common software. If you can turn on a laptop, type a letter in Microsoft Word, send an email, and search Google, you are computer literate. Information technology, or IT, goes much deeper. IT is the use of computers, networks, storage, and software to manage, process, and protect information. An IT professional not only uses technology; they understand how it works, how to fix it, and how to choose the right tools for an organisation.</p>
<p>Think of the difference between a person who can drive a car and a person who can maintain the engine, diagnose faults, and advise a company on which vehicles to buy. Both need driving skills, but the second person has a much broader understanding of how the vehicle operates. Computer literacy is like driving. Information technology is like understanding, maintaining, and managing the whole transport system.</p>

<h2>What Does IT Cover?</h2>
<p>Information technology includes many connected areas. In a small Zambian business or college, IT work might involve:</p>
<ul>
<li><strong>Hardware</strong> — selecting, installing, and repairing computers, printers, routers, and servers.</li>
<li><strong>Software</strong> — choosing operating systems, office suites, accounting packages, and security tools.</li>
<li><strong>Networking</strong> — connecting computers together so they can share files, printers, and internet access.</li>
<li><strong>Data management</strong> — storing files safely, making backups, and helping people find the information they need.</li>
<li><strong>Security</strong> — protecting devices and data from viruses, scams, power surges, and unauthorised users.</li>
<li><strong>Support</strong> — helping colleagues solve problems politely and recording those problems so they do not happen again.</li>
</ul>

<h2>Why IT Matters in Zambia</h2>
<p>Good IT makes organisations faster, more reliable, and more professional. A school in Kalomo that keeps student records on a well-organised computer system can produce reports in minutes instead of days. A small shop that uses cloud accounting software can see instantly whether it is making a profit. A clinic that backs up patient records protects lives when paper files are lost or damaged. Even a chicken-rearing side business benefits from IT when the owner uses spreadsheets to track feed costs and egg sales.</p>
<p>Many Zambian organisations waste money on technology they do not need, or they buy good equipment but fail to maintain it. A trained IT person can prevent these mistakes. They can recommend a K3,500 desktop computer instead of a K12,000 gaming laptop for office work, configure a secure Wi-Fi network, and set up automatic backups so that a power surge during load-shedding does not destroy years of records.</p>

<h2>Worked Example: A Day in the Life of an IT Assistant</h2>
<p>Grace works as an IT assistant at a small college in Livingstone. Her morning includes these tasks:</p>
<ol>
<li>She checks that the office router is working and that staff can access the internet.</li>
<li>She helps a lecturer who cannot print by restarting the printer and clearing a paper jam.</li>
<li>She creates a new user account for a finance officer who joined yesterday.</li>
<li>She checks the backup log and confirms that last night's copy of student records completed successfully.</li>
<li>She logs a support ticket about a slow computer so the technician can replace its failing hard drive before it crashes.</li>
</ol>
<p>None of these tasks require advanced programming, but they all require organised IT knowledge. Grace saves the college time and money every day.</p>

<h2>Try It Yourself</h2>
<ol>
<li>List three tasks you do on a computer or smartphone that show computer literacy.</li>
<li>For each task, write one way an IT professional would understand it more deeply. For example, sending an email is literacy; configuring email security is IT.</li>
<li>Interview someone who works in an office, shop, school, or clinic. Ask them what technology problems slow them down.</li>
<li>Write two sentences explaining how an IT assistant could help that organisation.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Computer literacy</strong> — the ability to use computers and common software confidently.</li>
<li><strong>Information technology (IT)</strong> — the use of computers, networks, and software to manage, process, and protect information.</li>
<li><strong>Hardware</strong> — the physical parts of a computer system such as the monitor, keyboard, and router.</li>
<li><strong>Software</strong> — the programs and applications that run on computer hardware.</li>
<li><strong>Network</strong> — a group of connected computers and devices that can share resources and information.</li>
</ul>

<h2>Summary</h2>
<p>Information technology is broader than computer literacy. While literacy focuses on using tools, IT focuses on understanding, managing, and supporting those tools inside an organisation. In Zambia, skilled IT workers help schools, businesses, and government offices save time, reduce costs, and protect valuable information. This course will build your IT knowledge step by step so that you can support real organisations confidently.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/introduction-to-information-technology/">Microsoft Learn — Introduction to Information Technology</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://support.google.com/a/answer/63834">Google Workspace Admin Help — IT Basics</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.2 Operating Systems: The Manager Behind the Screen',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what an operating system does, compare Microsoft Windows and common alternatives, perform essential tasks such as installing software and checking system settings, and understand why updates matter for security and performance.</p>

<h2>What Is an Operating System?</h2>
<p>An operating system, or OS, is the main software that manages a computer. It controls the hardware, runs applications, organises files, and provides the interface you see on the screen. Without an operating system, a computer is just a collection of electronic parts. With an operating system, it becomes a useful tool for writing documents, browsing the internet, and running business software.</p>
<p>The most common operating system for office computers in Zambia is Microsoft Windows. Other options include Linux distributions such as Ubuntu, which is free and increasingly popular in schools and government offices, and macOS, which runs on Apple computers. Mobile devices use Android and iOS, which are also operating systems. This lesson focuses on Windows because it dominates Zambian workplaces, but the principles apply to all modern operating systems.</p>

<h2>What the Operating System Manages</h2>
<p>The operating system performs several important jobs behind the scenes:</p>
<ul>
<li><strong>Process management</strong> — decides which programs run and how much processor time each one receives.</li>
<li><strong>Memory management</strong> — allocates RAM to open programs so the computer runs smoothly.</li>
<li><strong>File management</strong> — keeps track of where documents, photos, and programs are stored on the hard drive or SSD.</li>
<li><strong>Device management</strong> — communicates with printers, keyboards, mice, monitors, and network adapters.</li>
<li><strong>Security</strong> — controls user accounts, passwords, and permissions to protect data.</li>
</ul>

<h2>Checking System Information</h2>
<p>An IT assistant often needs to know what version of Windows a computer is running, how much RAM it has, and how much storage space remains. On Windows 10 or Windows 11, press the Windows key and type "About your PC." The window that opens shows the processor, installed RAM, and system type. To check free disk space, open File Explorer, click "This PC," and look at the drives listed. A drive that is nearly full will slow down the computer and may prevent updates from installing.</p>

<h2>Installing and Uninstalling Software</h2>
<p>Installing software correctly is a basic IT skill. Whenever possible, download installers from the official website. For example, download LibreOffice from <a href="https://www.libreoffice.org">libreoffice.org</a>, VLC from <a href="https://www.videolan.org">videolan.org</a>, and Google Chrome from <a href="https://www.google.com/chrome">google.com/chrome</a>. Avoid downloading from third-party sites that bundle extra programs or malware.</p>
<p>To uninstall software on Windows, open Settings, then Apps, then Installed apps. Find the program you no longer need, click the three dots, and choose Uninstall. Removing unused software frees disk space and reduces security risks.</p>

<h2>Worked Example: Freeing Space and Updating Windows</h2>
<p>Mr Banda's office computer in Kalomo is running slowly. Grace, the IT assistant, follows this procedure:</p>
<ol>
<li>She opens File Explorer and checks "This PC." The C: drive shows only 3 GB free out of 256 GB, which is too low.</li>
<li>She opens Disk Cleanup from the Start menu, selects the C: drive, and removes temporary files and recycle bin items.</li>
<li>She uninstalls three old games and an expired antivirus trial that the previous user installed.</li>
<li>She checks Windows Update in Settings. Several important security updates are pending, which she installs and restarts to complete.</li>
<li>After the restart, the computer has 18 GB free and responds much faster.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>On a college or office computer, open "About your PC" and write down the Windows version, processor, and RAM.</li>
<li>Open File Explorer, click "This PC," and record how much free space remains on the C: drive.</li>
<li>Open Windows Update and check whether any updates are pending. If it is safe to do so, install them.</li>
<li>List two programs installed on the computer that are not needed for office work. Do not uninstall them without permission.</li>
<li>Restart the computer using the Start menu, not the power button, and notice how long the process takes.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Operating system (OS)</strong> — the main software that manages computer hardware and runs applications.</li>
<li><strong>Windows</strong> — a family of operating systems developed by Microsoft, widely used in offices.</li>
<li><strong>Linux</strong> — a free, open-source operating system family often used on servers and budget computers.</li>
<li><strong>RAM</strong> — temporary memory that the computer uses while programs are running.</li>
<li><strong>Windows Update</strong> — a service that downloads security patches and improvements for Windows.</li>
</ul>

<h2>Summary</h2>
<p>The operating system is the invisible manager that makes a computer usable. It handles hardware, runs programs, organises files, and protects users. An IT professional must know how to check system information, install and remove software safely, and keep the operating system updated. These simple maintenance habits prevent many of the slow-downs and security problems that affect Zambian offices.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/windows/">Microsoft Learn — Windows Documentation</a></li>
<li><a href="https://ubuntu.com/tutorials">Ubuntu Tutorials</a></li>
<li><a href="https://www.w3schools.com/computer/computer_os.asp">W3Schools — Operating Systems</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.3 File Management Mastery for the Office',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create a clear folder structure for an office or small business, name files consistently, find lost documents quickly, and apply backup habits that protect important records from accidental deletion or hardware failure.</p>

<h2>Why File Management Matters</h2>
<p>Poor file management wastes time and causes stress. Imagine a school bursar who saves fee receipts on the Desktop with names like "doc1.docx" and "new folder." When the auditor arrives, she spends hours searching for documents. Now imagine a small shop owner who keeps every invoice in one folder called "My Stuff." He cannot tell which file belongs to which month. Good file management prevents these problems. It makes information easy to find, share, and protect.</p>
<p>In a Zambian office, file management is especially important because staff may share computers, electricity may be unreliable, and paper backups may be damaged by rain or insects. A clear digital filing system helps everyone work efficiently and creates a reliable record for tax, audit, and management purposes.</p>

<h2>Designing a Folder Structure</h2>
<p>A good folder structure mirrors how the organisation thinks about its work. Start with a main folder for the organisation or department, then create sub-folders by year, function, or project. Here is an example for a small training college:</p>
<ul>
<li><strong>Edutrack_Admin</strong>
<ul>
<li><strong>2025_Students</strong> — enrolment forms, results, attendance</li>
<li><strong>2025_Finance</strong> — invoices, receipts, bank statements</li>
<li><strong>2025_Staff</strong> — contracts, leave records, payslips</li>
<li><strong>Policies</strong> — student handbook, staff rules, IT policy</li>
</ul>
</li>
</ul>
<p>Notice that each year folder starts with the year first. This keeps folders in chronological order when sorted alphabetically. Avoid spaces in folder names when files will be shared online or used in web links. Underscores or hyphens are safer than spaces.</p>

<h2>Naming Files Consistently</h2>
<p>A good filename tells you what the file contains without opening it. Use a consistent pattern such as:</p>
<p><code>YYYY-MM-DD_DocumentType_Description_Version.ext</code></p>
<p>For example, a receipt from a supplier might be named <code>2025-06-10_Invoice_MunaliStationery_K450.pdf</code>. This name tells you the date, the type of document, the supplier, and the amount. If there are multiple versions, use <code>v1</code>, <code>v2</code>, or <code>FINAL</code> at the end to avoid confusion.</p>

<h2>Finding Lost Files</h2>
<p>Windows File Explorer has a search box in the top-right corner. If you know part of a filename, type it there. If you know the file type, search for <code>*.pdf</code> or <code>*.docx</code> to list all files of that type in the current folder and its sub-folders. You can also sort files by date modified to find the most recent work. Training staff to use search saves enormous amounts of time compared with clicking through every folder by hand.</p>

<h2>Worked Example: Organising a Small Business</h2>
<p>Mrs Nkhoma runs a dressmaking business in Kalomo. She takes photos of every order, writes measurements in documents, and keeps supplier price lists. Her old system was a single folder called "Work" with random filenames. After learning file management, she creates this structure:</p>
<ul>
<li><strong>Nkhoma_Tailoring</strong>
<ul>
<li><strong>2025_Orders</strong> — <code>2025-06-12_Order_Chiputa_WeddingDress_K1200.pdf</code></li>
<li><strong>2025_Receipts</strong> — <code>2025-06-10_Receipt_KamwalaFabrics_K350.pdf</code></li>
<li><strong>Designs</strong> — photos and sketches of dress styles</li>
<li><strong>Suppliers</strong> — price lists and contact details</li>
</ul>
</li>
</ul>
<p>Now she can find any order in seconds, and her accountant can locate receipts quickly at tax time.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a main folder on your computer called "IT_Practice_Business."</li>
<li>Inside it, create four sub-folders: "Invoices," "Receipts," "Customers," and "Reports."</li>
<li>Create three blank documents and name them using the pattern: <code>YYYY-MM-DD_Type_BriefDescription.ext</code>.</li>
<li>Move each document into the correct sub-folder.</li>
<li>Use the search box in File Explorer to find one of your files by typing part of its name.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Folder structure</strong> — the organised arrangement of folders and sub-folders used to store files.</li>
<li><strong>Filename convention</strong> — a consistent pattern for naming files so they are easy to identify and sort.</li>
<li><strong>File extension</strong> — the part of a filename after the dot that indicates the file type, such as .pdf or .docx.</li>
<li><strong>Search filter</strong> — a technique such as typing *.pdf to show only files of a particular type.</li>
<li><strong>Backup</strong> — a copy of important files stored in a separate location for safety.</li>
</ul>

<h2>Summary</h2>
<p>Good file management is a practical IT skill that every organisation needs. A clear folder structure, consistent filenames, and effective use of search save time and reduce errors. When files are organised well, backups become easier, audits become less stressful, and staff can focus on their real work instead of hunting for documents.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/windows/client-management/file-explorer/">Microsoft Learn — File Explorer</a></li>
<li><a href="https://support.google.com/drive/answer/2424384">Google Drive Help — Organise Files</a></li>
<li><a href="https://www.w3schools.com/computer/computer_files_folders.asp">W3Schools — Files and Folders</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.4 Software, Hardware, and the Business IT Environment',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to distinguish between system software and application software, describe the main hardware components found in a business computer, explain the concept of a server, and make sensible decisions when matching technology to business needs.</p>

<h2>System Software Versus Application Software</h2>
<p>Software is usually divided into two broad categories. <strong>System software</strong> runs the computer itself. The operating system, device drivers, and utility programs such as disk cleaners are system software. <strong>Application software</strong> helps users perform specific tasks. Microsoft Word, Google Chrome, accounting programs, and point-of-sale systems are all application software.</p>
<p>When an organisation buys software, it must decide whether it needs a new application to do a job, or whether it needs to upgrade the underlying system software. A slow computer might not need a new word processor; it might need more RAM or a clean installation of Windows. Understanding this distinction helps IT staff fix the right problem.</p>

<h2>Core Hardware Components</h2>
<p>Every business computer contains similar parts. Knowing them helps you describe problems to technicians and buy appropriate equipment.</p>
<ul>
<li><strong>Processor (CPU)</strong> — performs calculations and instructions. A modern Intel Core i3 or AMD Ryzen 3 is adequate for most office work.</li>
<li><strong>RAM</strong> — temporary working memory. For Windows 11, 8 GB is a practical minimum for smooth multitasking.</li>
<li><strong>Storage drive</strong> — holds files and programs permanently. SSDs are faster and more reliable than older mechanical hard drives.</li>
<li><strong>Motherboard</strong> — the main circuit board that connects all components.</li>
<li><strong>Power supply unit</strong> — converts mains electricity into voltages the computer can use. During load-shedding, a laptop battery or UPS keeps this supply stable.</li>
<li><strong>Network adapter</strong> — allows the computer to connect to Wi-Fi or Ethernet cables.</li>
</ul>

<h2>What Is a Server?</h2>
<p>A server is a computer that provides services to other computers on a network. It might store shared files, host a database, manage user accounts, or run a website. In a small Zambian office, a server can be a normal desktop computer with extra storage and a reliable operating system. In larger organisations, servers are dedicated machines kept in a secure, cool room.</p>
<p>For example, a college might have a server that stores student records and allows lecturers to access them from any office computer. A clinic might have a small server that backs up patient files every night. Even cloud services such as Google Drive rely on massive servers in data centres around the world.</p>

<h2>Matching Technology to Business Needs</h2>
<p>One of the most valuable IT skills is the ability to recommend the right technology for the right situation. Before buying anything, ask these questions:</p>
<ol>
<li>What problem are we trying to solve?</li>
<li>How many people will use the technology?</li>
<li>Do we need it to work without internet, or is a cloud service acceptable?</li>
<li>What is the realistic budget, including maintenance and training?</li>
<li>What happens if the equipment fails? Can we get local support?</li>
</ol>

<h2>Worked Example: Choosing a Computer for an Office</h2>
<p>A small NGO in Kalomo needs a new computer for its administrator. The administrator uses email, writes reports, manages a spreadsheet of donors, and attends occasional video meetings. Grace the IT assistant recommends:</p>
<ul>
<li>A desktop or laptop with an Intel Core i3 processor or equivalent</li>
<li>8 GB of RAM</li>
<li>256 GB SSD storage</li>
<li>Windows 11 or Ubuntu Linux, depending on what other computers the NGO uses</li>
<li>A basic UPS to protect against power surges and load-shedding</li>
</ul>
<p>This specification is sufficient for the work and avoids wasting money on a high-end gaming or graphics computer. Grace also plans to include a small monitor, keyboard, and mouse in the budget.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Look at the computer in front of you and identify at least four hardware components you can see or know are inside.</li>
<li>List three pieces of system software and three pieces of application software on that computer.</li>
<li>Write a short paragraph describing what a server could do for a small business you know, such as a shop or school.</li>
<li>Search online for the price of a basic office desktop computer in Zambia. Compare it with a gaming computer. What makes them different?</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>System software</strong> — software that manages the computer itself, including the operating system and drivers.</li>
<li><strong>Application software</strong> — programs that help users perform specific tasks such as writing, browsing, or accounting.</li>
<li><strong>CPU</strong> — Central Processing Unit; the chip that carries out instructions.</li>
<li><strong>SSD</strong> — Solid State Drive; a fast, reliable storage device with no moving parts.</li>
<li><strong>Server</strong> — a computer that provides shared resources or services to other computers on a network.</li>
</ul>

<h2>Summary</h2>
<p>Hardware and software work together to create a useful business IT environment. System software manages the computer, application software helps people do their jobs, and servers provide shared services across a network. A good IT professional matches the right technology to the organisation's needs and budget, avoiding both underpowered equipment and unnecessary expense.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/windows-hardware/design/component-guidelines/">Microsoft Learn — Hardware Guidelines</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://www.w3schools.com/computer/computer_hardware.asp">W3Schools — Computer Hardware</a></li>
</ul>
HTML,
            ],
        ];
    }


    private function module2Lessons(): array
    {
        return [
            [
                'title' => '2.1 How Office Networks Work',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain the purpose of a computer network, identify the main devices used in a small office network, understand the difference between a local area network and the internet, and describe how IP addresses help devices communicate.</p>

<h2>What Is a Network?</h2>
<p>A network is a group of computers and devices connected together so they can share information and resources. In a small Zambian office, a network lets several staff members use the same printer, access the same files, and connect to the internet through one link. Without a network, every computer would need its own printer and its own internet connection, which would be expensive and inefficient.</p>
<p>Networks are everywhere. The Wi-Fi in your college, the mobile data on your phone, and the ATM connected to your bank all use networks. Understanding how a simple office network works is the foundation for troubleshooting connectivity problems and planning IT growth.</p>

<h2>Common Network Devices</h2>
<p>A small office network usually contains these devices:</p>
<ul>
<li><strong>Router</strong> — the central device that directs traffic between computers on the local network and the wider internet. It often includes a Wi-Fi access point.</li>
<li><strong>Switch</strong> — a device with multiple Ethernet ports that lets computers connect using cables. Many small routers have built-in switches.</li>
<li><strong>Access point</strong> — broadcasts a Wi-Fi signal so wireless devices can join the network.</li>
<li><strong>Modem</strong> — connects the network to the internet service provider. In Zambia this might be a ZICTA-licensed ISP, Airtel, MTN, or a fibre provider.</li>
<li><strong>Network cables</strong> — Ethernet cables that connect computers, printers, and switches with a physical wire.</li>
</ul>

<h2>LAN Versus WAN</h2>
<p>A <strong>Local Area Network (LAN)</strong> covers a small area such as an office, school, or home. Devices on the same LAN can share files and printers quickly without using the internet. A <strong>Wide Area Network (WAN)</strong> covers a larger area and connects LANs together. The internet is the largest WAN in the world. When you send an email from Kalomo to Lusaka, the message travels across a WAN.</p>

<h2>IP Addresses</h2>
<p>Every device on a network needs an address so other devices can find it. An <strong>IP address</strong> is like a phone number for a computer. On most office networks, the router assigns addresses automatically using a service called DHCP. A typical local address looks like <code>192.168.1.10</code>. Public IP addresses are used on the internet and are assigned by your internet service provider.</p>
<p>If two devices accidentally get the same IP address, neither can communicate properly. Restarting the router usually fixes this because the router reassigns fresh addresses. This is one of the first tricks an IT assistant learns.</p>

<h2>Worked Example: A Three-Person Office Network</h2>
<p>Mr Tembo's real estate agency in Livingstone has three desktop computers, one printer, and two smartphones. Grace sets up the network as follows:</p>
<ol>
<li>The fibre ISP installs a modem that converts the fibre signal into an Ethernet connection.</li>
<li>Grace connects the modem to a router that provides both wired Ethernet ports and Wi-Fi.</li>
<li>The three desktop computers connect to the router with Ethernet cables for reliable speed.</li>
<li>The printer connects to the router by Wi-Fi so staff can print from any device.</li>
<li>The smartphones connect to Wi-Fi to save mobile data.</li>
<li>Grace changes the default router password and sets a strong Wi-Fi password.</li>
</ol>
<p>Now all devices can reach the internet and share the printer, while the desktops enjoy the stability of wired connections.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Draw a simple diagram of your college or home network. Label the router, modem, computers, printer, and any phones.</li>
<li>On a Windows computer, open Command Prompt and type <code>ipconfig</code>. Find the IPv4 address and the default gateway. Write them down.</li>
<li>Identify whether your device is connected by Wi-Fi or Ethernet cable.</li>
<li>Find the Wi-Fi password label on a router if one is available, or ask your instructor where the router is located.</li>
<li>List two advantages of a wired Ethernet connection over Wi-Fi.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Network</strong> — connected computers and devices that can share resources and information.</li>
<li><strong>Router</strong> — a device that directs traffic between a local network and the internet.</li>
<li><strong>LAN</strong> — Local Area Network; a network covering a small physical area such as an office.</li>
<li><strong>WAN</strong> — Wide Area Network; a network that connects separate LANs over a larger area.</li>
<li><strong>IP address</strong> — a unique address assigned to each device on a network.</li>
</ul>

<h2>Summary</h2>
<p>Office networks connect computers, printers, and phones so they can share resources and access the internet. The router is the heart of the network, while switches, access points, and modems provide connections. Understanding LANs, WANs, and IP addresses prepares you to set up, secure, and troubleshoot small networks in Zambian workplaces.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/network-fundamentals/">Microsoft Learn — Networking Fundamentals</a></li>
<li><a href="https://www.w3schools.com/computer/computer_networking.asp">W3Schools — Computer Networking</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.2 Sharing Files and Printers on a Local Network',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to share a folder or printer on a Windows network, connect to a shared resource from another computer, understand the security risks of open shares, and apply simple permissions so only the right people can access sensitive files.</p>

<h2>Why Share on a Network?</h2>
<p>Sharing files and printers across a network saves money and improves teamwork. Instead of buying a printer for every desk, an office can buy one reliable printer and let everyone use it. Instead of emailing documents back and forth, staff can save files in a shared folder that several people can open. For a small Zambian organisation, these savings are significant.</p>
<p>However, sharing also creates risk. If a folder is shared with everyone and contains salary records or student results, unauthorised people may read or delete important information. Good IT practice means sharing only what is needed and applying permissions carefully.</p>

<h2>Sharing a Folder in Windows</h2>
<p>To share a folder on a Windows network, follow these steps:</p>
<ol>
<li>Right-click the folder you want to share and choose Properties.</li>
<li>Click the Sharing tab, then click Advanced Sharing.</li>
<li>Tick "Share this folder" and give it a share name that users will see on the network.</li>
<li>Click Permissions. Remove "Everyone" if it is listed, then add only the users or groups who need access.</li>
<li>Assign either Read permission, Change permission, or Full Control depending on what each user needs.</li>
<li>Click OK to save the settings.</li>
</ol>
<p>Users on the same network can now access the folder by typing the computer's network path into File Explorer, such as <code>\\OfficePC1\\SharedFiles</code>.</p>

<h2>Sharing a Printer</h2>
<p>If a printer is connected to one computer by USB, that computer can share the printer with the network. Open Settings, go to Bluetooth and devices, then Printers and scanners. Select the printer, click Printer properties, and go to the Sharing tab. Tick "Share this printer" and give it a short name. Other computers can then add the printer by searching for network printers during setup.</p>
<p>A better long-term solution is a network printer with its own Wi-Fi or Ethernet connection. This printer does not depend on any single computer being turned on, so it is more reliable for an office.</p>

<h2>Understanding Permissions</h2>
<p>Permissions control who can do what with a shared resource. The three main levels are:</p>
<ul>
<li><strong>Read</strong> — the user can open and copy files but cannot change or delete them.</li>
<li><strong>Change</strong> — the user can open, edit, and delete files.</li>
<li><strong>Full Control</strong> — the user can change permissions and take ownership of the files.</li>
</ul>
<p>For most office folders, Read or Change is enough. Only administrators should have Full Control. This limits the damage that a mistake or a virus can cause.</p>

<h2>Worked Example: Sharing Department Folders</h2>
<p>A small college has three departments: Administration, Finance, and Academics. Grace creates a shared folder for each department on the office server. She then sets permissions as follows:</p>
<ul>
<li>Administration folder: Read/Change for administration staff, Read only for management.</li>
<li>Finance folder: Read/Change for finance staff only; no access for other departments.</li>
<li>Academics folder: Read/Change for lecturers, Read only for students.</li>
</ul>
<p>Each department can collaborate on its own files, but staff cannot accidentally open confidential finance records. When a new employee joins, Grace adds them to the correct group and they automatically receive the right permissions.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a new folder on your computer called "Network_Practice."</li>
<li>Right-click it, open Properties, and go to the Sharing tab.</li>
<li>Share the folder with a specific user account on the computer. Set the permission to Read only.</li>
<li>From another computer or account on the same network, try to open the shared folder.</li>
<li>Check whether your college printer is a network printer or a shared USB printer. Ask your instructor if you are unsure.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>File sharing</strong> — making files on one computer available to other computers on a network.</li>
<li><strong>Printer sharing</strong> — allowing multiple computers to send print jobs to one printer.</li>
<li><strong>Permissions</strong> — rules that control who can access a file or folder and what they can do with it.</li>
<li><strong>Read permission</strong> — allows a user to view and copy files but not change them.</li>
<li><strong>Full Control</strong> — the highest permission level, allowing a user to change permissions and ownership.</li>
</ul>

<h2>Summary</h2>
<p>Network sharing makes offices more efficient by letting staff use common printers and access shared folders. Setting correct permissions is essential to protect sensitive information. An IT assistant must know how to share resources safely, connect users to those resources, and explain the importance of limiting access to confidential data.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/windows-server/storage/file-server/file-server-smb-overview">Microsoft Learn — File Sharing Overview</a></li>
<li><a href="https://support.microsoft.com/en-us/windows/share-files-in-file-explorer-7bf21088-13c1-4a22-b27e-29476d29fed0">Microsoft Support — Share Files in File Explorer</a></li>
<li><a href="https://www.w3schools.com/computer/computer_networking.asp">W3Schools — Computer Networking</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.3 Setting Up and Securing Wi-Fi for a Small Office',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to configure a basic wireless network, choose strong Wi-Fi security settings, place a router for good coverage, and explain why hiding a network or using a weak password creates serious security risks.</p>

<h2>How Wi-Fi Works</h2>
<p>Wi-Fi uses radio waves to connect devices to a network without cables. A small device called an access point or wireless router broadcasts a network name, known as the SSID. Computers, phones, and printers within range can join by selecting the network and entering the password. In a Zambian office, Wi-Fi is convenient for laptops, smartphones, and guests, but it must be secured because the signal can travel outside the building.</p>

<h2>Choosing Security Settings</h2>
<p>Modern routers offer several security standards. The most common are WEP, WPA, WPA2, and WPA3. <strong>WEP is outdated and insecure</strong> and should never be used. <strong>WPA2</strong> is the minimum acceptable standard today. <strong>WPA3</strong> is newer and more secure but may not be supported by older devices. When setting up an office router, choose WPA2 or WPA3 with AES encryption.</p>
<p>The Wi-Fi password should be long and complex. A good password has at least twelve characters and includes a mix of uppercase letters, lowercase letters, numbers, and symbols. Avoid simple passwords such as "password123" or the business name followed by "2025." These are easy to guess and expose the network to attackers sitting in a car outside the office.</p>

<h2>Router Placement and Coverage</h2>
<p>Where you place the router affects Wi-Fi performance. For the best coverage:</p>
<ul>
<li>Place the router in a central location, not hidden in a corner or cupboard.</li>
<li>Keep it away from thick walls, metal cabinets, and large electrical equipment.</li>
<li>Elevate the router on a shelf rather than placing it on the floor.</li>
<li>Avoid placing it near microwave ovens or cordless phones, which can interfere with the signal.</li>
</ul>
<p>If the office is large or has multiple floors, one router may not cover every room. In that case, add a second access point or a mesh Wi-Fi system to extend coverage.</p>

<h2>Guest Networks</h2>
<p>Many modern routers allow you to create a separate guest network. This is a wise feature for offices that receive visitors. Guests can use the internet without accessing shared files, printers, or servers on the main network. The guest network should have its own password and should be reset regularly.</p>

<h2>Worked Example: Securing a Small Office Router</h2>
<p>Grace sets up Wi-Fi for a law firm in Lusaka. She follows this checklist:</p>
<ol>
<li>She changes the router's default administrator password. The default password is often printed on the router and easy to find online.</li>
<li>She sets the Wi-Fi security to WPA2 with AES encryption.</li>
<li>She creates a strong Wi-Fi password such as "Kal0mo@LawFi2025!" and writes it down securely.</li>
<li>She changes the default SSID from the router brand name to something neutral that does not identify the business.</li>
<li>She enables a guest network for clients with a different password.</li>
<li>She updates the router firmware to the latest version to fix known security holes.</li>
</ol>
<p>These steps protect the firm's client files from casual attackers and reduce the risk of unauthorised access.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Log in to a router's settings page by typing its default gateway address into a browser. Ask your instructor for the password.</li>
<li>Find the Wi-Fi security setting and confirm it is set to WPA2 or WPA3.</li>
<li>Look for the SSID and note whether it identifies the business.</li>
<li>Check whether the router has a guest network option and whether it is enabled.</li>
<li>Draw a floor plan of a small office and mark the best place to position the router.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Wi-Fi</strong> — wireless networking technology that uses radio waves to connect devices.</li>
<li><strong>SSID</strong> — Service Set Identifier; the name of a wireless network.</li>
<li><strong>WPA2/WPA3</strong> — security standards that encrypt Wi-Fi traffic to protect it from eavesdropping.</li>
<li><strong>Encryption</strong> — the process of scrambling data so that only authorised devices can read it.</li>
<li><strong>Guest network</strong> — a separate Wi-Fi network for visitors that isolates them from the main office network.</li>
</ul>

<h2>Summary</h2>
<p>Wi-Fi is essential for modern offices, but it must be configured securely. Use WPA2 or WPA3 encryption, choose a strong password, place the router for good coverage, and enable a guest network for visitors. These practices protect the organisation's data from attackers who might be nearby.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.consumer.ftc.gov/articles/how-secure-your-home-wi-fi-router">FTC Consumer Advice — Secure Your Wi-Fi Router</a></li>
<li><a href="https://support.google.com/wifi/answer/6246513">Google Nest Wifi Help — Improve Wi-Fi Performance</a></li>
<li><a href="https://www.wi-fi.org/">Wi-Fi Alliance</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.4 Troubleshooting Common Network Problems',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to follow a structured process to diagnose common network problems, use built-in Windows tools to check connectivity, and apply practical fixes that restore internet access and file sharing in a small office.</p>

<h2>A Structured Approach to Troubleshooting</h2>
<p>When someone reports a network problem, an IT assistant should not guess. A structured approach saves time and avoids making the problem worse. Start with the simplest explanations first. The classic troubleshooting steps are:</p>
<ol>
<li>Identify the exact problem. Is it one device or many? Is it the internet, the local network, or a specific service?</li>
<li>Check physical connections. Are cables plugged in? Is the router powered on? Are indicator lights normal?</li>
<li>Restart the affected device and the router. Many problems clear after a restart.</li>
<li>Check settings such as Wi-Fi passwords, IP addresses, and airplane mode.</li>
<li>Test with another device or cable to isolate the fault.</li>
<li>Document what you found and what fixed it.</li>
</ol>

<h2>Common Network Problems and Fixes</h2>
<p>Here are problems you are likely to see in a Zambian office:</p>
<ul>
<li><strong>No internet on any device</strong> — Check the router lights, restart the router and modem, and confirm that the ISP account is active and paid. During load-shedding, power may have interrupted the connection.</li>
<li><strong>One computer cannot connect</strong> — Check the Ethernet cable or Wi-Fi password. Run the Windows Network Troubleshooter. Release and renew the IP address using <code>ipconfig /release</code> and <code>ipconfig /renew</code> in Command Prompt.</li>
<li><strong>Slow internet</strong> — Check how many people are streaming or downloading. Test the speed at a quiet time. A slow connection may simply be an overloaded shared link, not a fault.</li>
<li><strong>Cannot access a shared folder</strong> — Verify that the sharing computer is turned on, that the folder is still shared, and that the user has the correct permissions. Check that both computers are on the same network.</li>
<li><strong>Printer not printing</strong> — Restart the printer, check for paper jams, low toner, or offline status in Windows. For network printers, verify the printer's IP address.</li>
</ul>

<h2>Useful Windows Commands</h2>
<p>Open Command Prompt and try these commands:</p>
<ul>
<li><code>ipconfig</code> — shows the IP address, subnet mask, and default gateway.</li>
<li><code>ping google.com</code> — tests whether the computer can reach the internet.</li>
<li><code>ping 192.168.1.1</code> — tests whether the computer can reach the router.</li>
<li><code>tracert google.com</code> — shows the path data takes to reach a website.</li>
</ul>
<p>If ping to the router fails, the problem is local. If ping to Google fails but the router responds, the problem is likely with the internet connection or ISP.</p>

<h2>Worked Example: Restoring Internet After Load-Shedding</h2>
<p>The power returns after load-shedding at a clinic in Choma, but no computer can access the internet. Grace follows her checklist:</p>
<ol>
<li>She checks the router lights. The power light is on, but the internet light is flashing red.</li>
<li>She unplugs the router and modem, waits thirty seconds, and plugs them back in.</li>
<li>After two minutes, the internet light turns solid green and websites load again.</li>
<li>She updates the support log with the time, cause, and fix so that future staff know what to do.</li>
</ol>
<p>Grace also reminds the clinic to invest in a UPS so that the router stays online during short outages.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open Command Prompt and run <code>ipconfig</code>. Identify your IP address and default gateway.</li>
<li>Run <code>ping google.com</code> and note how many replies succeed.</li>
<li>Disconnect from Wi-Fi and try to open a website. Then reconnect and confirm it works.</li>
<li>Write a short troubleshooting guide for a user who says "the internet is not working." Include at least five steps.</li>
<li>Ask your instructor about a real network problem that happened at the college and how it was fixed.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Troubleshooting</strong> — the process of identifying and solving a problem systematically.</li>
<li><strong>IP address conflict</strong> — when two devices on a network have the same IP address, causing connection problems.</li>
<li><strong>Default gateway</strong> — the IP address of the router that connects the local network to other networks.</li>
<li><strong>Ping</strong> — a command that tests whether another device on a network responds.</li>
<li><strong>ISP</strong> — Internet Service Provider; the company that supplies internet access.</li>
</ul>

<h2>Summary</h2>
<p>Network troubleshooting is a practical skill that every IT assistant needs. By following a structured process, checking physical connections, restarting devices, and using simple commands such as ping and ipconfig, you can solve many common office network problems. Always document your work so that the organisation learns from each incident.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/network-fundamentals/">Microsoft Learn — Networking Fundamentals</a></li>
<li><a href="https://support.microsoft.com/en-us/windows/fix-network-connection-issues-abb4b6c3-76fc-4f5a-9911-465ee428fcd7">Microsoft Support — Fix Network Connection Issues</a></li>
<li><a href="https://www.w3schools.com/computer/computer_networking.asp">W3Schools — Computer Networking</a></li>
</ul>
HTML,
            ],
        ];
    }


    private function module3Lessons(): array
    {
        return [
            [
                'title' => '3.1 Introduction to Cloud Services for Zambian Businesses',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what cloud computing means, describe the main benefits and risks for small Zambian organisations, distinguish between public and private cloud models, and identify common cloud services used for storage, email, and productivity.</p>

<h2>What Is the Cloud?</h2>
<p>The cloud refers to computing services delivered over the internet instead of from local computers or servers. When you save a file to Google Drive, send an email through Gmail, or watch a video on YouTube, you are using cloud services. The actual computers that store and process your data are in large data centres owned by companies such as Google, Microsoft, and Amazon. You access them through your web browser or apps.</p>
<p>For Zambian businesses, the cloud is both an opportunity and a challenge. It allows small organisations to use powerful tools without buying expensive servers. It also depends on a reliable internet connection, which can be a problem in areas with slow or expensive data.</p>

<h2>Benefits of Cloud Services</h2>
<p>Cloud services offer several advantages for small organisations:</p>
<ul>
<li><strong>Lower upfront cost</strong> — you pay a subscription instead of buying hardware.</li>
<li><strong>Automatic updates</strong> — the provider maintains the software and applies security patches.</li>
<li><strong>Access from anywhere</strong> — staff can work from home, the office, or a client site.</li>
<li><strong>Disaster recovery</strong> — data is copied across multiple locations, protecting against local hardware failure.</li>
<li><strong>Collaboration</strong> — several people can edit the same document at the same time.</li>
</ul>

<h2>Risks and Considerations</h2>
<p>The cloud is not perfect. Before moving important data to the cloud, consider these risks:</p>
<ul>
<li><strong>Internet dependency</strong> — if the internet is down, cloud services may be unavailable.</li>
<li><strong>Data privacy</strong> — your data is stored on someone else's computers. Read the provider's privacy policy.</li>
<li><strong>Subscription costs</strong> — monthly fees can add up over time.</li>
<li><strong>Vendor lock-in</strong> — it can be difficult to move data from one provider to another.</li>
</ul>
<p>For organisations in Zambia, it is wise to keep a local copy of critical data even when using the cloud. This combination gives both convenience and protection during internet outages.</p>

<h2>Public, Private, and Hybrid Cloud</h2>
<p>A <strong>public cloud</strong> is shared among many customers. Google Drive and Microsoft OneDrive are public cloud services. A <strong>private cloud</strong> is used by only one organisation and is usually hosted on its own servers. A <strong>hybrid cloud</strong> combines both: sensitive data stays on local servers, while everyday collaboration happens in the public cloud.</p>

<h2>Worked Example: A Clinic Moves Patient Records</h2>
<p>A small clinic in Mongu wants to stop storing patient records only on one computer. The IT assistant recommends a hybrid approach:</p>
<ol>
<li>Everyday appointment schedules and non-sensitive reports go into Google Drive so staff can access them from any device.</li>
<li>Confidential patient records remain on a password-protected local computer with encrypted backups.</li>
<li>The clinic pays for a reliable internet connection and keeps a mobile data dongle as a backup.</li>
<li>Staff are trained not to share cloud passwords and to log out of shared computers.</li>
</ol>
<p>This plan balances the convenience of the cloud with the need to protect sensitive information.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open your web browser and sign in to a free Google or Microsoft account.</li>
<li>Upload a non-sensitive document to Google Drive or OneDrive.</li>
<li>Open the document on a different device, such as your phone, to confirm cloud access.</li>
<li>List three benefits and three risks of cloud computing for a small shop in Kalomo.</li>
<li>Search online for "data centres in Africa" and note one country mentioned besides South Africa.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Cloud computing</strong> — delivering computing services such as storage, software, and processing over the internet.</li>
<li><strong>Public cloud</strong> — cloud services shared among many customers and managed by a provider.</li>
<li><strong>Private cloud</strong> — cloud infrastructure used by a single organisation.</li>
<li><strong>Hybrid cloud</strong> — a combination of public cloud and private or local systems.</li>
<li><strong>Vendor lock-in</strong> — difficulty moving data or services from one provider to another.</li>
</ul>

<h2>Summary</h2>
<p>Cloud services give Zambian businesses access to powerful tools without buying expensive equipment. They improve collaboration, disaster recovery, and remote work. However, they depend on the internet and require careful attention to privacy and cost. A thoughtful hybrid approach often works best for small organisations.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://aws.amazon.com/what-is-cloud-computing/">AWS — What Is Cloud Computing?</a></li>
<li><a href="https://support.google.com/drive/answer/2424384">Google Drive Help — Store Files</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.2 Google Workspace for Teamwork and Productivity',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to use Google Workspace tools for document creation, spreadsheets, file storage, and email; manage sharing settings so that only the right people can access files; and apply these tools to a realistic Zambian business scenario.</p>

<h2>What Is Google Workspace?</h2>
<p>Google Workspace is a collection of cloud-based productivity tools from Google. It includes Gmail for email, Google Drive for storage, Google Docs for word processing, Google Sheets for spreadsheets, Google Slides for presentations, Google Meet for video meetings, and Google Forms for surveys. A free version is available for personal use, while businesses can subscribe to a paid version with custom email addresses, more storage, and administrative controls.</p>
<p>For Zambian organisations, Google Workspace is attractive because it works well on smartphones and low-cost computers. A market vendor can update a stock spreadsheet on her phone. A teacher can share a lesson plan with colleagues in real time. An NGO can hold a board meeting over Google Meet instead of paying for travel.</p>

<h2>Sharing and Permissions</h2>
<p>One of the most important Google Workspace skills is managing sharing correctly. When you share a Google Doc or Sheet, you can choose:</p>
<ul>
<li><strong>Restricted</strong> — only specific people can open the file.</li>
<li><strong>Anyone with the link</strong> — anyone who has the link can open it. This is risky for sensitive data.</li>
<li><strong>Viewer, Commenter, or Editor</strong> — controls what the recipient can do. Viewers can only read; commenters can suggest changes; editors can change the file directly.</li>
</ul>
<p>As an IT assistant, you should encourage staff to use Restricted sharing whenever possible and to avoid public links for confidential documents. You should also remind people to remove access when someone leaves the organisation.</p>

<h2>Google Drive Organisation</h2>
<p>Google Drive uses folders just like a computer. A well-organised Drive makes collaboration much easier. Consider creating a top-level folder for the organisation, then sub-folders for departments, projects, or years. Name files consistently, and use the Star feature to mark files you access often. Use the search bar to find documents quickly.</p>

<h2>Worked Example: Managing a PTA WhatsApp Fundraiser with Google Workspace</h2>
<p>A parents' association at a Kalomo school is raising funds for new desks. The chairperson, Mr Zulu, uses Google Workspace to coordinate the project:</p>
<ol>
<li>He creates a Google Form to collect pledges from parents. The form asks for name, phone number, and pledged amount.</li>
<li>Responses are saved automatically in a Google Sheet. He shares the sheet with the treasurer as a Viewer and with the secretary as an Editor.</li>
<li>He writes updates in a Google Doc and shares it with all parents using a link set to Viewer.</li>
<li>He schedules a Google Meet for committee members who cannot attend in person.</li>
<li>All files are stored in a shared Google Drive folder called "2025_Desk_Fundraising."</li>
</ol>
<p>This system keeps the project organised, transparent, and accessible from any phone or computer.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Sign in to a Google account and open Google Drive.</li>
<li>Create a folder named "IT_Course_Practice."</li>
<li>Create a Google Doc inside the folder and write a short report on one benefit of cloud computing.</li>
<li>Share the document with a classmate or your instructor as a Commenter.</li>
<li>Create a Google Sheet with three columns: Item, Quantity, and Total Price. Enter at least four rows and use a SUM formula at the bottom.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Google Workspace</strong> — Google's suite of cloud productivity and collaboration tools.</li>
<li><strong>Collaboration</strong> — working together on the same document or project in real time.</li>
<li><strong>Sharing permissions</strong> — settings that control who can view, comment on, or edit a file.</li>
<li><strong>Cloud storage</strong> — saving files on internet servers instead of only on a local device.</li>
<li><strong>Google Forms</strong> — a tool for creating online surveys and collecting responses in a spreadsheet.</li>
</ul>

<h2>Summary</h2>
<p>Google Workspace provides powerful, low-cost tools for communication, document creation, and collaboration. Managing sharing permissions carefully protects sensitive information, while good folder organisation makes files easy to find. These skills are directly useful for small businesses, schools, and community organisations across Zambia.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/a/answer/63834">Google Workspace Admin Help</a></li>
<li><a href="https://support.google.com/docs/answer/88438">Google Docs Help — Get Started</a></li>
<li><a href="https://support.google.com/docs/answer/1218656">Google Sheets Help — Create and Edit</a></li>
<li><a href="https://grow.google/">Grow with Google — Free Training</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.3 Microsoft OneDrive and Office Online',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to use Microsoft OneDrive to store and sync files, create and edit documents with Office Online, compare OneDrive with Google Drive, and decide which cloud office suite is appropriate for a given organisation.</p>

<h2>What Is OneDrive?</h2>
<p>OneDrive is Microsoft's cloud storage service. It is built into Windows and works closely with Microsoft Word, Excel, and PowerPoint. When you save a file to your OneDrive folder, it automatically copies to the cloud and to any other device signed in to the same account. This means you can start a report on a college computer, continue it on your phone, and finish it on a laptop at home.</p>
<p>Every Microsoft account comes with a small amount of free OneDrive storage. Businesses can subscribe to Microsoft 365, which includes more storage plus desktop versions of Word, Excel, Outlook, and Teams.</p>

<h2>Office Online</h2>
<p>Office Online is the web-based version of Microsoft Office. It runs in a browser and includes Word Online, Excel Online, PowerPoint Online, and OneNote Online. These versions are not as powerful as the desktop programs, but they are free and sufficient for most everyday tasks. They are especially useful when using a computer that does not have Microsoft Office installed, such as a shared college machine.</p>

<h2>OneDrive Versus Google Drive</h2>
<p>Both OneDrive and Google Drive store files in the cloud, but they have different strengths. OneDrive integrates best with Microsoft Office and Windows. If an organisation already uses Word and Excel on Windows computers, OneDrive is a natural choice. Google Drive integrates best with Google Docs and Sheets and works very well on Android phones. If staff mainly use smartphones or need free tools, Google Drive may be better.</p>
<p>As an IT assistant, your job is not to favour one brand. Your job is to match the tool to the organisation's existing habits, budget, and devices.</p>

<h2>Syncing and Sharing</h2>
<p>OneDrive has a sync client that keeps a copy of your cloud files on your computer. When you edit a file, the changes upload automatically. You can also share files with others by right-clicking and choosing "Share." Just like Google Drive, you can set permissions to view or edit, and you can require a password for extra security.</p>
<p>Be careful with the "Always keep on this device" option. It stores a full local copy, which is good for offline work but uses more disk space. For rarely used files, choose "Free up space" to keep only a cloud copy.</p>

<h2>Worked Example: Choosing a Cloud Suite for an NGO</h2>
<p>A small NGO in Kabwe already owns Windows laptops with Microsoft Office. Most staff are comfortable with Word and Excel. Grace recommends Microsoft 365 Business Basic, which gives them:</p>
<ul>
<li>Business email using their own domain name, such as info@ngo-kabwe.org</li>
<li>1 TB of OneDrive storage per user</li>
<li>Online versions of Word, Excel, and PowerPoint</li>
<li>Microsoft Teams for meetings</li>
</ul>
<p>This choice keeps staff in familiar software while adding cloud storage and professional email. If the NGO had no existing Office licences and mostly used Android phones, Grace might have recommended Google Workspace instead.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a free Microsoft account if you do not have one.</li>
<li>Sign in to OneDrive through a web browser and upload a document.</li>
<li>Open Word Online and create a short document. Save it to OneDrive.</li>
<li>Install the OneDrive desktop app if available, or use the OneDrive folder already in Windows.</li>
<li>Compare the OneDrive interface with Google Drive. Write three similarities and three differences.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>OneDrive</strong> — Microsoft's cloud storage and file synchronisation service.</li>
<li><strong>Office Online</strong> — free web-based versions of Microsoft Word, Excel, and PowerPoint.</li>
<li><strong>Microsoft 365</strong> — a subscription service that includes Office apps, cloud storage, and email.</li>
<li><strong>Sync</strong> — keeping files the same across multiple devices by copying changes automatically.</li>
<li><strong>Cloud storage</strong> — saving files on remote servers accessed over the internet.</li>
</ul>

<h2>Summary</h2>
<p>Microsoft OneDrive and Office Online give organisations a familiar way to store, sync, and edit files in the cloud. They work best for teams that already use Microsoft Office on Windows. When advising an organisation, consider existing habits, budget, and devices before choosing between OneDrive, Google Drive, or a hybrid approach.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/onedrive">Microsoft Support — OneDrive</a></li>
<li><a href="https://support.microsoft.com/en-us/office/office-online-introduction-76ac0ed0-fefd-414a-9948-5fa5118c8c32">Microsoft Support — Office Online Introduction</a></li>
<li><a href="https://learn.microsoft.com/en-us/microsoft-365/">Microsoft Learn — Microsoft 365</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.4 Choosing Business Software: Accounting, POS, and More',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe the main categories of business software used in small Zambian organisations, evaluate software options against real business needs, and make practical recommendations that balance features, cost, training, and local support.</p>

<h2>The Business Software Landscape</h2>
<p>Business software helps organisations perform specific functions more efficiently. In Zambia, small businesses, schools, and NGOs use a wide range of applications. The main categories include:</p>
<ul>
<li><strong>Accounting software</strong> — records income, expenses, invoices, and tax. Examples include QuickBooks, Sage, Zoho Books, and open-source tools such as GnuCash.</li>
<li><strong>Point-of-sale (POS) software</strong> — processes sales in shops, restaurants, and markets. Examples include Shopify POS, Square, and many local systems.</li>
<li><strong>Customer relationship management (CRM)</strong> — tracks customers, leads, and communication. Examples include HubSpot CRM, Zoho CRM, and Bitrix24.</li>
<li><strong>Inventory and stock management</strong> — tracks products, quantities, and reorder levels.</li>
<li><strong>Human resources and payroll</strong> — manages staff records, leave, and salaries.</li>
<li><strong>Communication and collaboration</strong> — email, messaging, video calls, and shared documents.</li>
</ul>

<h2>Choosing Software Wisely</h2>
<p>When an organisation asks you to recommend software, do not start with the brand names. Start with the problem. Ask questions such as:</p>
<ol>
<li>What task is taking too long or causing errors?</li>
<li>How many people will use the software?</li>
<li>Does the organisation need the software to work offline?</li>
<li>What is the total budget, including setup, training, and ongoing subscription?</li>
<li>Is local support available in Zambia if something goes wrong?</li>
<li>Can the software produce reports needed by ZRA, banks, or donors?</li>
</ol>

<h2>Free and Open-Source Options</h2>
<p>For organisations with very limited budgets, free and open-source software can be a good starting point. LibreOffice provides word processing, spreadsheets, and presentations at no cost. GnuCash handles accounting for small businesses. Moodle can manage online learning. These tools are legal, safe, and maintained by communities of developers. The trade-off is that support usually comes from online forums rather than local vendors.</p>

<h2>Worked Example: Selecting Accounting Software for a Shop</h2>
<p>Mrs Banda runs a hardware shop in Kitwe. She currently records sales in a notebook and is making errors. She asks Grace to recommend accounting software. Grace investigates and recommends a simple cloud accounting package with these features:</p>
<ul>
<li>Invoicing and receipt recording</li>
<li>Expense tracking</li>
<li>VAT reporting that matches ZRA requirements</li>
<li>Mobile app so she can check sales from her phone</li>
<li>Monthly subscription within her budget</li>
<li>Ability to export data so she is not locked in forever</li>
</ul>
<p>Grace also trains two staff members and creates a simple procedure manual. Within a month, Mrs Banda can see her profit each day and prepare VAT returns more easily.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Search online for three accounting software options available in Zambia. Note one advantage and one disadvantage of each.</li>
<li>Choose a small business you know, such as a shop or salon. List two software tools that could help it.</li>
<li>Compare LibreOffice with Microsoft Office online. Which would you recommend for a school with no budget?</li>
<li>Write five questions you would ask before recommending any business software.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Accounting software</strong> — programs that record financial transactions and produce reports.</li>
<li><strong>Point-of-sale (POS) system</strong> — software and hardware used to process customer sales.</li>
<li><strong>CRM</strong> — Customer Relationship Management; software for tracking customers and sales leads.</li>
<li><strong>Open-source software</strong> — software whose source code is freely available and can be modified by anyone.</li>
<li><strong>Subscription model</strong> — paying a regular fee to use software rather than buying it once.</li>
</ul>

<h2>Summary</h2>
<p>The business software landscape offers many tools for accounting, sales, customer management, inventory, and communication. A good IT professional does not simply recommend the most famous brand. Instead, they match the software to the organisation's needs, budget, devices, and local support. This careful approach prevents wasted money and helps staff adopt new tools successfully.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.gnucash.org/">GnuCash — Free Accounting Software</a></li>
<li><a href="https://www.libreoffice.org/">LibreOffice — Free Office Suite</a></li>
<li><a href="https://www.zoho.com/books/">Zoho Books — Cloud Accounting</a></li>
<li><a href="https://grow.google/">Grow with Google — Digital Skills</a></li>
</ul>
HTML,
            ],
        ];
    }


    private function module4Lessons(): array
    {
        return [
            [
                'title' => '4.1 User Accounts, Permissions, and Updates',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create and manage user accounts on Windows, assign appropriate permission levels, explain why administrator accounts should be limited, and keep operating systems and applications updated to protect against security threats.</p>

<h2>Why User Accounts Matter</h2>
<p>In an office, different people need different levels of access. A receptionist may only need to use a browser and a calendar. An accountant needs access to financial files and accounting software. An IT administrator needs to install programs and change system settings. User accounts let the operating system give each person the right access while protecting sensitive areas from accidental or malicious changes.</p>
<p>Without separate accounts, everyone uses the same password and the same permissions. If one person makes a mistake or downloads a virus, the entire computer is at risk. Good account management is one of the simplest and most effective security measures an organisation can take.</p>

<h2>Types of Windows Accounts</h2>
<p>Windows offers several account types. The most common are:</p>
<ul>
<li><strong>Administrator</strong> — can install software, change system settings, create other accounts, and access all files. This level should be reserved for IT staff only.</li>
<li><strong>Standard user</strong> — can use programs and save files but cannot make system-wide changes. This is the right level for most office staff.</li>
<li><strong>Guest</strong> — a limited account for temporary users. It is rarely used in modern Windows versions.</li>
</ul>
<p>When you create a new account, choose Standard user by default. If a staff member later needs to install software, an administrator can enter a password to approve the action without giving the user permanent administrator rights.</p>

<h2>Creating and Managing Accounts</h2>
<p>On Windows 11, open Settings, then Accounts, then Other users. Click "Add account" to create a local account or add a Microsoft account. For a small office, local accounts are often simpler because they do not depend on internet access. Give each account a clear username, such as the person's first name and surname, and set a strong password.</p>
<p>When someone leaves the organisation, disable or delete their account promptly. This prevents former employees from accessing files and systems. Before deleting an account, back up any personal or work files stored in that account's folders.</p>

<h2>Keeping Systems Updated</h2>
<p>Software updates fix security holes, improve stability, and add new features. The operating system, web browser, office suite, antivirus, and other applications all need regular updates. On Windows, enable automatic updates and schedule them for times when the computer is usually on and connected to power. Remember that updates can fail if the internet is slow or if the disk is nearly full, so prepare in advance.</p>

<h2>Worked Example: Setting Up Accounts for a New Staff Member</h2>
<p>A new finance officer joins a college in Ndola. Grace prepares the computer as follows:</p>
<ol>
<li>She logs in with an administrator account and opens Settings > Accounts > Other users.</li>
<li>She creates a local account named "maria.mwamba" with a strong password.</li>
<li>She changes the account type to Standard user.</li>
<li>She creates a folder on the shared drive for finance documents and gives Maria permission to read and edit.</li>
<li>She installs the approved accounting software using the administrator account.</li>
<li>She runs Windows Update and installs pending updates before handing over the computer.</li>
</ol>
<p>Maria can now do her job without having dangerous administrator privileges on the computer.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open Windows Settings and navigate to Accounts > Other users.</li>
<li>Create a new local account named "practice_user" with a strong password.</li>
<li>Change the account type between Standard user and Administrator and observe the difference.</li>
<li>Sign in to the practice account and try to install a program. Note what happens.</li>
<li>Delete the practice account after the exercise, making sure no important files are inside it.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>User account</strong> — a record that identifies a person to the operating system and controls what they can access.</li>
<li><strong>Administrator</strong> — a user with full control over the computer, including the ability to install software and manage accounts.</li>
<li><strong>Standard user</strong> — a user who can run programs and save files but cannot change system-wide settings.</li>
<li><strong>Permissions</strong> — rules that determine which files and settings a user can access or change.</li>
<li><strong>Patch</strong> — a small update that fixes a security problem or software bug.</li>
</ul>

<h2>Summary</h2>
<p>User accounts and permissions are fundamental to office security. Most staff should use Standard accounts, while Administrator rights should be limited to IT staff. Regular updates protect systems from known security flaws. These basic administration tasks prevent many of the problems that affect small organisations in Zambia.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/windows/security/identity-protection/access-control/local-accounts">Microsoft Learn — Local Accounts</a></li>
<li><a href="https://support.microsoft.com/en-us/windows/create-a-local-user-or-administrator-account-in-windows-20de74e0-ac7f-3502-a866-329eb7d41b1a">Microsoft Support — Create a Local User Account</a></li>
<li><a href="https://www.w3schools.com/cybersecurity/">W3Schools — Cybersecurity</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.2 Backups and Data Protection',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain why backups are essential, design a simple backup plan for a small organisation, choose appropriate backup media and locations, and describe how to protect data from power problems, theft, and human error.</p>

<h2>Why Backups Are Essential</h2>
<p>Data loss can destroy a business or organisation. A college might lose years of student records. A shop might lose its sales history and customer list. A clinic might lose patient information. Backups are copies of important data stored in a separate location so that the original can be recovered if something goes wrong.</p>
<p>Common causes of data loss in Zambia include power surges during load-shedding, hardware failure, viruses, accidental deletion, theft, and fire or water damage. A good backup plan reduces the impact of all these events.</p>

<h2>The 3-2-1 Backup Rule</h2>
<p>A widely recommended strategy is the 3-2-1 rule:</p>
<ul>
<li>Keep <strong>3 copies</strong> of important data: the original plus two backups.</li>
<li>Use <strong>2 different types</strong> of storage, such as an external hard drive and cloud storage.</li>
<li>Keep <strong>1 copy offsite</strong>, such as in the cloud or at a different physical location.</li>
</ul>
<p>For a small Zambian business, this might mean keeping files on the office computer, copying them to an external drive once a week, and syncing the most important files to Google Drive or OneDrive.</p>

<h2>Backup Media and Methods</h2>
<p>There are several ways to back up data. Each has advantages and disadvantages:</p>
<ul>
<li><strong>External hard drives or USB flash drives</strong> — cheap and portable, but can be lost, stolen, or damaged.</li>
<li><strong>Network-attached storage (NAS)</strong> — a dedicated device on the network that backs up multiple computers automatically.</li>
<li><strong>Cloud storage</strong> — convenient and offsite, but depends on internet speed and subscription cost.</li>
<li><strong>Optical discs such as DVDs</strong> — durable for archiving but low capacity and becoming obsolete.</li>
</ul>

<h2>Protecting Against Power Problems</h2>
<p>In Zambia, unreliable electricity is a major threat to data. A power cut while saving a file can corrupt it. A power surge when electricity returns can damage the computer. Protect your systems with these habits:</p>
<ul>
<li>Use an uninterruptible power supply (UPS) for computers, routers, and servers.</li>
<li>Save work frequently and enable autosave in programs that support it.</li>
<li>Unplug sensitive equipment during load-shedding.</li>
<li>Keep backups disconnected from the computer when not actively copying, so viruses cannot reach them.</li>
</ul>

<h2>Worked Example: A Weekly Backup Routine</h2>
<p>A small law firm in Lusaka implements this backup plan:</p>
<ol>
<li>Every Friday at 4 PM, the office manager copies the week's case files from the server to an external hard drive.</li>
<li>The external drive is stored in a locked cabinet away from the main server.</li>
<li>Important documents are also synced to a Google Drive folder shared with the senior partner.</li>
<li>Once a month, the firm verifies that files can be restored from the backup by opening a few random documents.</li>
<li>The office manager keeps a log showing the date of each backup.</li>
</ol>
<p>This plan is simple, affordable, and effective. It protects against hardware failure, accidental deletion, and minor disasters.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Identify the three most important types of data in a school, shop, or clinic you know.</li>
<li>Design a 3-2-1 backup plan for that organisation. State what media you would use and how often you would back up.</li>
<li>On a computer, copy an important folder to a USB drive or cloud storage folder.</li>
<li>Check whether the folder copied correctly by opening a file from the backup location.</li>
<li>Write a short memo explaining to staff why they must not store the only copy of important files on their Desktop.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Backup</strong> — a copy of data stored separately from the original for recovery purposes.</li>
<li><strong>3-2-1 rule</strong> — a backup strategy using three copies, two media types, and one offsite copy.</li>
<li><strong>Restore</strong> — the process of copying data back from a backup to its original location.</li>
<li><strong>UPS</strong> — Uninterruptible Power Supply; a battery backup that protects against power cuts and surges.</li>
<li><strong>Offsite backup</strong> — a backup stored at a different physical location from the original data.</li>
</ul>

<h2>Summary</h2>
<p>Backups are one of the most important responsibilities in IT. A simple, regular backup routine protects organisations from data loss caused by power problems, hardware failure, theft, and mistakes. The 3-2-1 rule provides a clear starting point, and testing restores ensures that backups actually work when needed.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.backblaze.com/blog/the-3-2-1-backup-strategy/">Backblaze — The 3-2-1 Backup Strategy</a></li>
<li><a href="https://support.google.com/drive/answer/2424384">Google Drive Help — Store Files</a></li>
<li><a href="https://support.microsoft.com/en-us/windows/backup-and-restore-in-windows-3520916b-ea84-4d97-a5b2-1be16e5816fc">Microsoft Support — Backup and Restore</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.3 IT Support Etiquette and Ticketing',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to deliver professional IT support, record support requests using a ticketing system, communicate clearly with users who have different levels of technical knowledge, and follow up so that problems stay solved.</p>

<h2>What Is IT Support?</h2>
<p>IT support is the help that technical staff give to users when technology does not work as expected. It includes fixing computers, resetting passwords, explaining software, and installing equipment. In a small Zambian organisation, the IT support person might also be the administrator, the trainer, and the procurement officer. Good support is not only about technical skill; it is also about attitude, communication, and organisation.</p>

<h2>Professional Etiquette</h2>
<p>Users often feel stressed or embarrassed when technology fails. A supportive attitude makes a big difference. Follow these etiquette guidelines:</p>
<ul>
<li><strong>Greet people politely</strong> and introduce yourself if they do not know you.</li>
<li><strong>Listen carefully</strong> to the problem before touching the computer. Let the user explain in their own words.</li>
<li><strong>Avoid jargon</strong> when speaking to non-technical staff. Say "the program is frozen" instead of "the process has hung."</li>
<li><strong>Ask permission</strong> before accessing personal files or changing settings.</li>
<li><strong>Stay calm</strong> even if the user is frustrated. Your calmness helps them trust you.</li>
<li><strong>Explain what you did</strong> in simple terms so the user can avoid the problem next time.</li>
</ul>

<h2>Using a Ticketing System</h2>
<p>A ticketing system records every support request so that nothing is forgotten. A simple ticket includes:</p>
<ul>
<li>The user's name and contact details</li>
<li>The date and time the problem was reported</li>
<li>A clear description of the issue</li>
<li>Steps already tried</li>
<li>The person assigned to fix it</li>
<li>The current status: Open, In Progress, Waiting for User, or Closed</li>
<li>The final solution</li>
</ul>
<p>For a very small organisation, a ticketing system can be as simple as a shared Google Sheet or a notebook kept at the IT desk. Larger organisations use dedicated software such as osTicket, Zendesk, or Spiceworks.</p>

<h2>Prioritising Requests</h2>
<p>Not every problem is equally urgent. A server that stores student records failing is more urgent than one person wanting a new mouse. Learn to priorities based on impact and urgency:</p>
<ul>
<li><strong>High priority</strong> — systems down, security breach, data loss, or many users affected.</li>
<li><strong>Medium priority</strong> — one user unable to work, software bug, or hardware failure.</li>
<li><strong>Low priority</strong> — training requests, minor improvements, or non-urgent equipment setup.</li>
</ul>
<p>Always communicate the priority to the user so they know when to expect help.</p>

<h2>Worked Example: Handling a Password Reset Request</h2>
<p>Mrs Zulu calls the IT desk because she cannot log in to her email. Grace follows a professional process:</p>
<ol>
<li>She greets Mrs Zulu and confirms her name and department.</li>
<li>She opens a ticket with the subject "Email login failure — Mrs Zulu, Finance."</li>
<li>She asks whether Mrs Zulu is seeing a specific error message and whether caps lock is on.</li>
<li>After confirming a forgotten password, Grace resets the password using her administrator account.</li>
<li>She gives Mrs Zulu a temporary password and asks her to change it immediately.</li>
<li>She documents the solution and closes the ticket after confirming that Mrs Zulu can log in.</li>
</ol>
<p>This process is polite, secure, and recorded for future reference.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a simple ticket template in a document or spreadsheet with the fields listed above.</li>
<li>Role-play with a classmate. One person is a frustrated user whose printer will not print; the other is the IT assistant.</li>
<li>Practise explaining a technical solution without using jargon.</li>
<li>Write a short email to a user explaining that their problem has been fixed and what they should do if it happens again.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Ticket</strong> — a recorded support request that tracks a problem from report to resolution.</li>
<li><strong>End user</strong> — the person who uses the technology, as opposed to the person who supports it.</li>
<li><strong>SLA</strong> — Service Level Agreement; a formal target for how quickly support requests should be handled.</li>
<li><strong>Jargon</strong> — technical language that may be confusing to non-experts.</li>
<li><strong>Escalation</strong> — passing a problem to a more senior or specialised technician when it cannot be solved at the current level.</li>
</ul>

<h2>Summary</h2>
<p>Professional IT support combines technical ability with communication and organisation. Polite, patient interactions build trust, while ticketing systems ensure that problems are tracked and resolved. Prioritising requests fairly and documenting solutions help small organisations provide consistent, reliable support.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.zendesk.com/blog/it-support-ticketing-system/">Zendesk — What Is an IT Ticketing System?</a></li>
<li><a href="https://learn.microsoft.com/en-us/microsoft-365/admin/admin-overview/admin-overview">Microsoft Learn — Microsoft 365 Admin Center</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.4 Databases, Reports, and IT Procurement for Small Organisations',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what a database is and why organisations use them, create simple reports from stored information, apply basic IT procurement principles when buying equipment and software, and advise a small Zambian organisation on making technology purchases that match its needs and budget.</p>

<h2>What Is a Database?</h2>
<p>A database is an organised collection of information that can be searched, sorted, and updated. Unlike a spreadsheet, which is designed for calculations and small datasets, a database is built to handle large amounts of related information reliably. A school might use a database to store student enrolments, grades, attendance, and fee payments. A shop might use a database to track stock, suppliers, and sales.</p>
<p>Databases are everywhere behind the scenes. When you check your mobile money balance, search for a product online, or log in to a college portal, a database is supplying the information.</p>

<h2>Tables, Records, and Fields</h2>
<p>A simple database stores information in tables. Each table represents one type of thing, such as Students or Products. Each row in a table is a <strong>record</strong>, which is one complete entry. Each column is a <strong>field</strong>, which is one piece of information about that entry.</p>
<p>For example, a Students table might have fields such as Student_ID, First_Name, Last_Name, Phone, and Course. One record could be "STU001, Mary, Banda, 097X XXX XXX, Certificate in Information Technology."</p>

<h2>Creating Simple Reports</h2>
<p>A report turns raw data into useful information. A school database might produce a report showing how many students passed each course. A shop database might produce a report showing which products sold best in June. Reports help managers make decisions without reading every individual record.</p>
<p>Even without a formal database, you can create reports using spreadsheets. For example, a shop owner can export sales data from a POS system into Excel or Google Sheets and use pivot tables to summarise sales by product or by week.</p>

<h2>IT Procurement Basics</h2>
<p>Procurement means buying goods and services. IT procurement is the process of choosing and purchasing technology. A careless purchase wastes money; a wise purchase supports the organisation for years. Before buying anything, create a clear requirement document that answers these questions:</p>
<ul>
<li>What business need will this technology address?</li>
<li>What are the minimum technical specifications?</li>
<li>How many units are needed?</li>
<li>What is the total cost, including software, licences, training, support, and ongoing maintenance?</li>
<li>Which vendors supply the product in Zambia, and what is their reputation?</li>
<li>What warranty and after-sales support are available?</li>
</ul>

<h2>Worked Example: Buying Computers for a School</h2>
<p>A community school in Kalomo wants to buy ten computers for a new computer lab. Grace helps the head teacher with procurement:</p>
<ol>
<li>She defines the need: basic computing lessons, typing practice, internet research, and office applications.</li>
<li>She specifies the minimum requirements: dual-core processor, 8 GB RAM, 256 GB SSD, Windows 11 or Ubuntu, and at least a three-year warranty.</li>
<li>She gets quotes from three local suppliers in Lusaka and one in Livingstone.</li>
<li>She checks whether the supplier offers on-site support and spare parts availability.</li>
<li>She includes the cost of a UPS, antivirus software, and basic maintenance in the total budget.</li>
<li>She recommends the supplier that offers the best balance of price, warranty, and local support, not necessarily the cheapest.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Create a simple table in a spreadsheet with columns for Student_ID, Name, Course, and Fee_Paid. Enter at least five records.</li>
<li>Use a filter to show only students who have paid their fees.</li>
<li>Search online for the price of a desktop computer suitable for office work in Zambia. Note the specifications.</li>
<li>Write a one-page procurement note for a small business that needs a printer. Include the need, budget, and three evaluation criteria.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Database</strong> — an organised collection of data that can be searched, sorted, and reported on.</li>
<li><strong>Record</strong> — one complete entry in a database table, usually shown as a row.</li>
<li><strong>Field</strong> — one piece of information in a record, usually shown as a column.</li>
<li><strong>Report</strong> — a summary of data designed to help people make decisions.</li>
<li><strong>Procurement</strong> — the process of purchasing goods and services for an organisation.</li>
</ul>

<h2>Summary</h2>
<p>Databases help organisations store and use large amounts of information efficiently. Reports turn that data into actionable knowledge. IT procurement ensures that organisations buy the right technology at the right price with proper support. Together, these skills allow an IT professional to contribute meaningfully to the management and growth of a small Zambian organisation.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/sql/">W3Schools — SQL Tutorial</a></li>
<li><a href="https://support.google.com/docs/answer/1218656">Google Sheets Help — Create Reports</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/design-data-model/">Microsoft Learn — Design a Data Model</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: IT Foundations',
            'description' => 'Test your knowledge of information technology concepts, operating systems, file management, and business IT environments.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which statement best describes the difference between computer literacy and information technology?',
                    'explanation' => 'Computer literacy is the ability to use computers and software, while information technology involves understanding, managing, and supporting computer systems in an organisation.',
                    'options' => [
                        ['text' => 'They mean exactly the same thing.', 'is_correct' => false],
                        ['text' => 'Computer literacy is about using tools; IT is about understanding and managing them.', 'is_correct' => true],
                        ['text' => 'IT is only about hardware repair.', 'is_correct' => false],
                        ['text' => 'Computer literacy is only for beginners.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is an example of system software?',
                    'explanation' => 'The operating system is system software because it manages the computer itself. Word processors and browsers are application software.',
                    'options' => [
                        ['text' => 'Microsoft Word', 'is_correct' => false],
                        ['text' => 'Google Chrome', 'is_correct' => false],
                        ['text' => 'Microsoft Windows', 'is_correct' => true],
                        ['text' => 'LibreOffice Calc', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main purpose of a folder structure?',
                    'explanation' => 'A folder structure groups related files together so they can be found quickly and managed efficiently.',
                    'options' => [
                        ['text' => 'To make files harder to delete', 'is_correct' => false],
                        ['text' => 'To organise files so they are easy to find', 'is_correct' => true],
                        ['text' => 'To increase internet speed', 'is_correct' => false],
                        ['text' => 'To replace the need for backups', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'An operating system manages hardware, runs applications, and organises files.',
                    'explanation' => 'The operating system performs all these tasks and provides the user interface for the computer.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'It is good practice to store the only copy of important business files on the Desktop.',
                    'explanation' => 'Files stored in only one place are at risk from hardware failure, accidental deletion, and power problems. Backups are essential.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which component is often called the brain of the computer?',
                    'explanation' => 'The CPU, or Central Processing Unit, performs calculations and executes instructions, earning it the nickname "brain" of the computer.',
                    'options' => [
                        ['text' => 'Hard drive', 'is_correct' => false],
                        ['text' => 'RAM', 'is_correct' => false],
                        ['text' => 'CPU', 'is_correct' => true],
                        ['text' => 'Monitor', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What does the abbreviation "OS" stand for in computing? (two words)',
                    'explanation' => 'OS stands for Operating System, the main software that manages a computer.',
                    'correct_answer' => 'Operating System',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why should most office staff use a Standard user account instead of an Administrator account?',
                    'explanation' => 'Standard accounts cannot make system-wide changes or install software, which limits damage from mistakes and malware.',
                    'options' => [
                        ['text' => 'Standard accounts are faster.', 'is_correct' => false],
                        ['text' => 'Standard accounts cannot make system-wide changes, improving security.', 'is_correct' => true],
                        ['text' => 'Administrator accounts are free.', 'is_correct' => false],
                        ['text' => 'Standard accounts use less electricity.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which storage type is faster and has no moving parts?',
                    'explanation' => 'A Solid State Drive (SSD) uses flash memory and has no moving parts, making it faster and more reliable than older mechanical hard drives.',
                    'options' => [
                        ['text' => 'Mechanical hard drive', 'is_correct' => false],
                        ['text' => 'DVD disc', 'is_correct' => false],
                        ['text' => 'SSD', 'is_correct' => true],
                        ['text' => 'Magnetic tape', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Office Networks and Connectivity',
            'description' => 'Test your understanding of office networks, file and printer sharing, Wi-Fi security, and network troubleshooting.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which device directs traffic between a local network and the internet?',
                    'explanation' => 'A router connects the local network to the internet and directs data between them.',
                    'options' => [
                        ['text' => 'Printer', 'is_correct' => false],
                        ['text' => 'Router', 'is_correct' => true],
                        ['text' => 'Keyboard', 'is_correct' => false],
                        ['text' => 'Monitor', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does LAN stand for?',
                    'explanation' => 'LAN stands for Local Area Network, a network covering a small area such as an office or home.',
                    'options' => [
                        ['text' => 'Large Area Network', 'is_correct' => false],
                        ['text' => 'Local Area Network', 'is_correct' => true],
                        ['text' => 'Long Access Node', 'is_correct' => false],
                        ['text' => 'Linked Application Network', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which Wi-Fi security standard should be used as a minimum today?',
                    'explanation' => 'WPA2 is the minimum acceptable Wi-Fi security standard. WEP is outdated and insecure.',
                    'options' => [
                        ['text' => 'WEP', 'is_correct' => false],
                        ['text' => 'WPA', 'is_correct' => false],
                        ['text' => 'WPA2', 'is_correct' => true],
                        ['text' => 'HTTP', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A guest network allows visitors to use the internet without accessing the main office network.',
                    'explanation' => 'Guest networks isolate visitors from shared files, printers, and servers on the main network.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Using the default router administrator password is safe if the office is small.',
                    'explanation' => 'Default passwords are widely known and should be changed immediately to protect the network.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which command tests whether your computer can reach a website?',
                    'explanation' => 'The ping command sends small test messages to another device and reports whether replies were received.',
                    'options' => [
                        ['text' => 'format', 'is_correct' => false],
                        ['text' => 'ping', 'is_correct' => true],
                        ['text' => 'copy', 'is_correct' => false],
                        ['text' => 'delete', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the name of the wireless network name broadcast by a router? (three letters)',
                    'explanation' => 'SSID stands for Service Set Identifier and is the name users see when choosing a Wi-Fi network.',
                    'correct_answer' => 'SSID',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'When sharing a folder, which permission level allows a user to open files but not change them?',
                    'explanation' => 'Read permission lets users view and copy files but prevents them from modifying or deleting the contents.',
                    'options' => [
                        ['text' => 'Full Control', 'is_correct' => false],
                        ['text' => 'Change', 'is_correct' => false],
                        ['text' => 'Read', 'is_correct' => true],
                        ['text' => 'Delete', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'If no device in the office can access the internet, what should you check first?',
                    'explanation' => 'If all devices are affected, the problem is usually with the router, modem, or internet service rather than one computer.',
                    'options' => [
                        ['text' => 'The keyboard on one computer', 'is_correct' => false],
                        ['text' => 'The router, modem, and ISP connection', 'is_correct' => true],
                        ['text' => 'The monitor brightness', 'is_correct' => false],
                        ['text' => 'The printer toner', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Cloud Services and Business Software',
            'description' => 'Test your knowledge of cloud computing, Google Workspace, OneDrive, and choosing business software.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main advantage of cloud computing for a small business?',
                    'explanation' => 'Cloud computing reduces upfront costs because businesses pay subscriptions instead of buying expensive servers.',
                    'options' => [
                        ['text' => 'It never needs internet access.', 'is_correct' => false],
                        ['text' => 'It lowers upfront hardware costs.', 'is_correct' => true],
                        ['text' => 'It removes the need for backups.', 'is_correct' => false],
                        ['text' => 'It always works during load-shedding.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which Google Workspace tool is best for creating an online survey?',
                    'explanation' => 'Google Forms is designed for creating surveys and automatically collects responses in a Google Sheet.',
                    'options' => [
                        ['text' => 'Google Docs', 'is_correct' => false],
                        ['text' => 'Google Sheets', 'is_correct' => false],
                        ['text' => 'Google Forms', 'is_correct' => true],
                        ['text' => 'Google Meet', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which cloud storage service is made by Microsoft and integrates closely with Word and Excel?',
                    'explanation' => 'OneDrive is Microsoft\'s cloud storage service and works seamlessly with Microsoft Office applications.',
                    'options' => [
                        ['text' => 'Google Drive', 'is_correct' => false],
                        ['text' => 'Dropbox', 'is_correct' => false],
                        ['text' => 'OneDrive', 'is_correct' => true],
                        ['text' => 'iCloud', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A public cloud service is shared among many customers and managed by a provider.',
                    'explanation' => 'Public cloud services such as Google Drive and OneDrive are shared among many users and run by the provider.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'When sharing a Google Doc, the "Anyone with the link" option is the safest choice for confidential files.',
                    'explanation' => 'Anyone with the link can access the file without signing in, which is risky for confidential information.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does CRM software mainly help an organisation manage?',
                    'explanation' => 'Customer Relationship Management software helps track customers, leads, and communication.',
                    'options' => [
                        ['text' => 'Computer repairs', 'is_correct' => false],
                        ['text' => 'Customer relationships and sales leads', 'is_correct' => true],
                        ['text' => 'Router configuration', 'is_correct' => false],
                        ['text' => 'Employee salaries only', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What does "SaaS" stand for when describing cloud software? (three words)',
                    'explanation' => 'SaaS stands for Software as a Service, where software is hosted by a provider and accessed over the internet.',
                    'correct_answer' => 'Software as a Service',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which factor should you consider first when recommending business software?',
                    'explanation' => 'The organisation\'s specific needs and problems should guide every software recommendation.',
                    'options' => [
                        ['text' => 'The brand name of the software', 'is_correct' => false],
                        ['text' => 'The organisation\'s needs and budget', 'is_correct' => true],
                        ['text' => 'The colour of the software logo', 'is_correct' => false],
                        ['text' => 'How many countries the vendor operates in', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of these is an example of open-source software?',
                    'explanation' => 'LibreOffice is open-source software that is free to use and modify.',
                    'options' => [
                        ['text' => 'Microsoft Office', 'is_correct' => false],
                        ['text' => 'Adobe Photoshop', 'is_correct' => false],
                        ['text' => 'LibreOffice', 'is_correct' => true],
                        ['text' => 'Zoom', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module4Quiz(): array
    {
        return [
            'title' => 'Module 4 Quiz: System Administration, Support, and Procurement',
            'description' => 'Test your understanding of user accounts, backups, IT support, databases, and IT procurement.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why should most office staff use a Standard user account rather than an Administrator account?',
                    'explanation' => 'Standard accounts cannot install software or change system settings, which limits accidental damage and malware risks.',
                    'options' => [
                        ['text' => 'Standard accounts are cheaper.', 'is_correct' => false],
                        ['text' => 'Standard accounts improve security by limiting system changes.', 'is_correct' => true],
                        ['text' => 'Standard accounts print faster.', 'is_correct' => false],
                        ['text' => 'Administrator accounts are not available on Windows.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'According to the 3-2-1 backup rule, how many copies of important data should you keep?',
                    'explanation' => 'The 3-2-1 rule recommends three copies of data: the original plus two backups.',
                    'options' => [
                        ['text' => 'One copy', 'is_correct' => false],
                        ['text' => 'Two copies', 'is_correct' => false],
                        ['text' => 'Three copies', 'is_correct' => true],
                        ['text' => 'Five copies', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main purpose of a support ticket?',
                    'explanation' => 'A support ticket records a problem from report to resolution so nothing is forgotten and patterns can be tracked.',
                    'options' => [
                        ['text' => 'To charge users money for help', 'is_correct' => false],
                        ['text' => 'To track problems and solutions', 'is_correct' => true],
                        ['text' => 'To replace email', 'is_correct' => false],
                        ['text' => 'To install software automatically', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A UPS protects computers from power cuts and surges.',
                    'explanation' => 'An Uninterruptible Power Supply provides battery backup during outages and helps protect against voltage spikes.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'When buying technology, the cheapest option is always the best choice for a small organisation.',
                    'explanation' => 'The cheapest option may lack warranty, support, or suitability. Total cost, support, and fitness for purpose matter more.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In a database, what is a single row in a table called?',
                    'explanation' => 'A record is one complete entry in a database table, typically shown as a row.',
                    'options' => [
                        ['text' => 'Field', 'is_correct' => false],
                        ['text' => 'Record', 'is_correct' => true],
                        ['text' => 'Column', 'is_correct' => false],
                        ['text' => 'Report', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What does "UPS" stand for? (three words)',
                    'explanation' => 'UPS stands for Uninterruptible Power Supply, a battery backup device.',
                    'correct_answer' => 'Uninterruptible Power Supply',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which behaviour best demonstrates good IT support etiquette?',
                    'explanation' => 'Listening carefully and explaining solutions in simple language helps users feel respected and reduces future problems.',
                    'options' => [
                        ['text' => 'Using technical jargon to impress the user', 'is_correct' => false],
                        ['text' => 'Listening carefully and explaining solutions simply', 'is_correct' => true],
                        ['text' => 'Fixing the computer without asking permission', 'is_correct' => false],
                        ['text' => 'Telling the user the problem is their fault', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'When procuring computers for a school, which factor is most important to include in the total budget?',
                    'explanation' => 'The total cost of ownership includes not just the computer but also software, support, training, and protection such as a UPS.',
                    'options' => [
                        ['text' => 'Only the price of the computer tower', 'is_correct' => false],
                        ['text' => 'The computer, software, warranty, support, and accessories such as a UPS', 'is_correct' => true],
                        ['text' => 'The colour of the computer case', 'is_correct' => false],
                        ['text' => 'The number of stickers on the monitor', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function createAssignments(): void
    {
        Assignment::create([
            'course_id' => $this->courseId,
            'lesson_id' => null,
            'title' => 'Design an Office IT Setup and File Plan',
            'description' => 'Apply your knowledge of hardware, operating systems, file management, and networks by designing a practical IT environment for a small Zambian organisation.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Choose a small Zambian organisation such as a community school, a market vendor cooperative, a small clinic, or an NGO office with 5-10 staff.
Step 2: Open a word processor and write one paragraph describing the organisation, its staff roles, and the main technology problems it faces.
Step 3: Recommend a basic hardware list for the office. Include at least: two desktop or laptop computers, one printer, one router, and one UPS. State one reason for each choice.
Step 4: Create a folder structure for the organisation. Show at least one main folder with four sub-folders, and give one example filename using the YYYY-MM-DD naming convention.
Step 5: Describe the local network setup in three sentences: how computers connect, how the printer is shared, and what Wi-Fi security setting you would use.
Step 6: Save your document as a PDF named "Office_IT_Plan.pdf" and upload it here.
INSTRUCTIONS,
            'max_points' => 100,
            'passing_points' => 50,
            'due_date' => null,
            'allow_late_submission' => 1,
            'allowed_file_types' => 'pdf,doc,docx',
            'max_file_size_mb' => 10,
        ]);

        Assignment::create([
            'course_id' => $this->courseId,
            'lesson_id' => null,
            'title' => 'IT Support Manual for a Small Organisation',
            'description' => 'Create a short IT support manual that combines backup procedures, user account guidance, and a support ticket template.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Open a word processor and create a document titled "IT Support Manual".
Step 2: Write a section called "Backups" that explains the 3-2-1 rule in your own words. Include how often backups should happen and where they should be stored.
Step 3: Write a section called "User Accounts" that explains the difference between Administrator and Standard user accounts, and states which type most office staff should use.
Step 4: Create a section called "Support Ticket" that includes a blank template with at least these fields: Date, User Name, Problem Description, Steps Tried, Solution, and Status.
Step 5: Add a short "Network Troubleshooting" checklist with at least four steps for a user who reports "the internet is not working."
Step 6: Save your document as a PDF named "IT_Support_Manual.pdf" and upload it here.
INSTRUCTIONS,
            'max_points' => 100,
            'passing_points' => 50,
            'due_date' => null,
            'allow_late_submission' => 1,
            'allowed_file_types' => 'pdf,doc,docx',
            'max_file_size_mb' => 10,
        ]);
    }

    private function printSummary(): void
    {
        $this->command->info('Certificate in Information Technology content seeded successfully.');
        $this->command->info('Modules: 4 | Lessons: 16 | Quizzes: 4 | Assignments: 2');
    }
}
