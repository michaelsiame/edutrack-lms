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

class ComputerStudiesContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Computer Studies')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Computer Studies" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in Computer Studies already has modules. Skipping content seed.');
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
                'title' => 'Module 1: Computer Systems, History, Hardware and Software',
                'description' => 'Understand what a computer is, trace the history of computing, identify hardware components, and distinguish system software from application software.',
            ],
            [
                'title' => 'Module 2: Operating Systems and File Management',
                'description' => 'Learn how operating systems control a computer, compare Windows, Android and Linux, and practise managing files, folders and user accounts.',
            ],
            [
                'title' => 'Module 3: Word Processing and Presentation Software',
                'description' => 'Create and format documents such as letters, CVs and reports, then design clear presentation slides for school or business.',
            ],
            [
                'title' => 'Module 4: Spreadsheets and Database Introduction',
                'description' => 'Use spreadsheets to calculate shop income and expenses, and understand how databases store and organise information.',
            ],
            [
                'title' => 'Module 5: Networks, the Internet and ICT in Zambia',
                'description' => 'Explore computer networks, browse the web safely, use email, and discover how ICT supports e-government, mobile banking and agriculture in Zambia.',
            ],
            [
                'title' => 'Module 6: Ethics, Safety and Programming Concepts',
                'description' => 'Apply computer ethics and online safety, protect your health while using computers, and learn basic programming ideas with pseudocode and Scratch.',
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
                'title' => '1.1 What Is a Computer? A Short History',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to define a computer in plain language, describe how computers have changed from huge calculators to pocket smartphones, and explain why this history matters to a student or small business owner in Zambia today.</p>

<h2>What Is a Computer?</h2>
<p>A computer is an electronic machine that accepts data, processes it according to a set of instructions, and produces useful information. The data can be numbers, words, pictures, sound, or video. The instructions are called a program. You already meet computers every day: the smartphone in your pocket, the cash register at a shop, the ATM at the bank, the ZESCO prepaid meter at home, and the laptop in a college classroom are all computers.</p>
<p>What makes a computer special is that it can follow a program that can be changed. A calculator always does the same limited set of sums, but a computer can run many different programs. One moment it can be a typewriter, the next a music player, and the next a banking tool.</p>

<h2>A Brief History of Computers</h2>
<p>Computers did not appear overnight. They developed through several generations, each smaller, faster and cheaper than the one before.</p>
<p><strong>First generation (1940s–1950s):</strong> These computers used large vacuum tubes and filled entire rooms. They generated enormous heat and could only be used by highly trained engineers.</p>
<p><strong>Second generation (1950s–1960s):</strong> Transistors replaced vacuum tubes. Computers became smaller, more reliable and less expensive. They were still mainly used by governments, universities and large companies.</p>
<p><strong>Third generation (1960s–1970s):</strong> Integrated circuits put many transistors on a single chip. This made computers even smaller and faster. Businesses began to use them for payroll, accounting and records.</p>
<p><strong>Fourth generation (1970s–today):</strong> Microprocessors put a complete CPU on one tiny chip. This led to personal computers, laptops, tablets and smartphones. A farmer in Southern Province today can hold more computing power in a phone than a room-sized computer from the 1960s.</p>

<h2>Worked Example: From Room-Sized Machines to Mobile Money</h2>
<p>Imagine a timeline. In 1960 a bank in Lusaka might have needed a room full of machines just to calculate salaries. By 1995 a small office could buy a personal computer for a few thousand Kwacha and run accounting software. By 2010 a shopkeeper could use a feature phone to send money. Today a market vendor in Kalomo can use an Android smartphone to accept Airtel Money or MTN MoMo, check prices on WhatsApp, and order stock from Lusaka. Each step depended on computers becoming smaller, cheaper and easier to use.</p>

<h2>Try It Yourself</h2>
<ol>
<li>List five electronic devices you have used this week. Mark each one with a tick if you think it contains a computer inside.</li>
<li>Ask an older relative or neighbour whether they remember using phones, radios or banks before mobile phones and computers. Write three sentences summarising what they say.</li>
<li>Draw a simple timeline with four boxes labelled First, Second, Third and Fourth generation. Under each box write one way that generation changed computers.</li>
<li>Search online for "history of computers" and find one image of an early room-sized computer. Save the image or note the website address.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Data</strong> — raw facts such as numbers, names or measurements that a computer processes.</li>
<li><strong>Program</strong> — a set of instructions that tells a computer what to do.</li>
<li><strong>CPU</strong> — Central Processing Unit; the main chip that carries out instructions inside a computer.</li>
<li><strong>Microprocessor</strong> — a complete CPU placed on a single integrated circuit chip.</li>
<li><strong>Generation</strong> — a major stage in the development of computer technology.</li>
</ul>

<h2>Summary</h2>
<p>A computer is an electronic device that processes data using programs. Over four generations computers have moved from room-sized machines to smartphones. This progress makes modern tools such as mobile money, online learning and digital record keeping possible for ordinary Zambians. Understanding this history helps you see computers not as mysterious boxes, but as tools that have become affordable and useful for everyday life.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://www.w3schools.com/computer/">W3Schools — Computer Basics</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/intro-to-computers/">Microsoft Learn — Introduction to Computers</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/Common_questions/How_does_the_Internet_work">MDN Web Docs — How Does the Internet Work?</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.2 Computer Hardware: Input, Processing, Output and Storage',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to name the four main groups of computer hardware, give Zambian examples of input, processing, output and storage devices, and identify common ports and buttons on a desktop or laptop computer.</p>

<h2>The Four Parts of a Computer System</h2>
<p>Computer hardware is any part of a computer you can touch. Most hardware fits into four groups. <strong>Input devices</strong> let you put data into the computer. <strong>Processing devices</strong> do the thinking and calculation. <strong>Output devices</strong> show you the results. <strong>Storage devices</strong> keep information safe even when the power is off.</p>
<p>Think of a shop in Kalomo that uses a computer. The cashier types prices using a keyboard, or scans a barcode with a scanner; both are input devices. The CPU inside the computer calculates the total. The monitor shows the receipt on the screen and the printer produces a paper receipt; both are output devices. At the end of the day the sales records are saved to a hard drive or flash disk; these are storage devices.</p>

<h2>Input Devices</h2>
<p>Input devices send information into the computer. Common examples include:</p>
<ul>
<li><strong>Keyboard</strong> — used to type letters, numbers and commands.</li>
<li><strong>Mouse</strong> — moves a pointer on the screen so you can click icons and buttons.</li>
<li><strong>Microphone</strong> — records sound, used for voice calls or voice typing.</li>
<li><strong>Webcam</strong> — captures video and photographs, often used for online classes.</li>
<li><strong>Scanner</strong> — converts paper documents into digital files.</li>
<li><strong>Barcode reader</strong> — reads product codes in shops and supermarkets.</li>
</ul>

<h2>Processing and Output Devices</h2>
<p>The main processing device is the <strong>CPU</strong>, or Central Processing Unit. It is sometimes called the brain of the computer. A faster CPU can run more programs at the same time without slowing down. RAM, or Random Access Memory, is temporary working space used while the computer is switched on.</p>
<p>Output devices include the <strong>monitor</strong>, which displays text and images, and the <strong>printer</strong>, which produces paper copies. Speakers produce sound, and projectors display the computer screen on a wall. In a college classroom a teacher might connect a laptop to a projector so every student can see the lesson.</p>

<h2>Storage Devices</h2>
<p>Storage keeps your files when the power is off. Common storage devices include:</p>
<ul>
<li><strong>Hard disk drive (HDD)</strong> — stores large amounts of data cheaply but contains moving parts.</li>
<li><strong>Solid-state drive (SSD)</strong> — faster and more reliable than a hard disk because it has no moving parts.</li>
<li><strong>USB flash drive</strong> — a small removable device you can carry in a pocket; useful for moving files between college and home.</li>
<li><strong>Memory card</strong> — often used in phones, cameras and drones.</li>
<li><strong>Cloud storage</strong> — stores files on the internet so you can access them from any device.</li>
</ul>

<h2>Worked Example: Setting Up a Desktop Computer</h2>
<p>Mwaka starts work at a small accounting office in Kalomo. She receives a desktop computer with a monitor, keyboard, mouse, CPU box and printer. She follows these steps:</p>
<ol>
<li>Places the monitor at eye level and connects its cable to the back of the CPU box.</li>
<li>Plugs the keyboard and mouse USB cables into USB ports on the front of the CPU box.</li>
<li>Connects the printer to the CPU box using a USB cable and plugs it into a power socket.</li>
<li>Presses the power button on the CPU box and waits for Windows to load.</li>
<li>Inserts a USB flash drive to copy her CV and college notes onto the computer.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Look at the computer in front of you. Identify the monitor, keyboard, mouse, CPU box or laptop body, and any USB ports.</li>
<li>Group each device you can see into input, processing, output or storage. Write your answers in a notebook.</li>
<li>Find the power button on the monitor and on the CPU box or laptop. Practise turning the computer on and shutting it down safely using the Start menu.</li>
<li>Count how many USB ports are available. Why might you need more than one?</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Hardware</strong> — the physical parts of a computer that you can touch.</li>
<li><strong>Input device</strong> — equipment used to send data into a computer, such as a keyboard or mouse.</li>
<li><strong>Output device</strong> — equipment that shows or prints results, such as a monitor or printer.</li>
<li><strong>CPU</strong> — Central Processing Unit; the main chip that processes instructions.</li>
<li><strong>Storage device</strong> — hardware that keeps data safe when the computer is switched off.</li>
</ul>

<h2>Summary</h2>
<p>Computer hardware is organised into input, processing, output and storage. Input devices feed data in, the CPU and RAM process it, output devices show or print results, and storage devices keep files safe. Knowing these groups helps you choose equipment, describe faults to a technician, and understand how computers work in homes, schools and businesses across Zambia.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://www.w3schools.com/computer/computer_hardware.php">W3Schools — Computer Hardware</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/intro-to-computers/">Microsoft Learn — Introduction to Computers</a></li>
<li><a href="https://support.google.com/android/answer/6174145">Google Android Help — Device Basics</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.3 System Software and Application Software',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain the difference between system software and application software, give examples of each type used in Zambia, and choose the right program for common tasks such as writing a letter, managing money or browsing the internet.</p>

<h2>Software Makes Hardware Useful</h2>
<p>Hardware is the physical computer. <strong>Software</strong> is the set of instructions, also called programs, that tell the hardware what to do. Without software a computer is just an expensive box. With the right software it becomes a tool for writing, calculating, communicating, learning and doing business.</p>
<p>Software is usually divided into two main categories: <strong>system software</strong> and <strong>application software</strong>. Understanding the difference will help you install the right programs, fix simple problems, and decide which tools your business or study needs.</p>

<h2>System Software</h2>
<p>System software runs the computer itself. It manages hardware, controls files, and provides a platform for application software. The most important piece of system software is the <strong>operating system</strong>, such as Microsoft Windows, Android, Apple iOS or Linux.</p>
<p>Other examples of system software include:</p>
<ul>
<li><strong>Device drivers</strong> — small programs that help the operating system talk to hardware such as printers, webcams and scanners.</li>
<li><strong>Utility programs</strong> — tools for maintaining the computer, such as antivirus software, disk cleanup and backup tools.</li>
<li><strong>Language translators</strong> — programs that convert code written by programmers into instructions the computer can follow.</li>
</ul>
<p>When you turn on a college computer and see the Windows desktop, you are looking at system software. When your phone asks for permission before an app uses the camera, the operating system is doing its job.</p>

<h2>Application Software</h2>
<p>Application software is what people use to do real work. It is designed for specific tasks. Examples include:</p>
<ul>
<li><strong>Word processors</strong> such as Microsoft Word and LibreOffice Writer, used to type letters, essays and CVs.</li>
<li><strong>Spreadsheets</strong> such as Microsoft Excel and LibreOffice Calc, used for budgets, sales records and calculations.</li>
<li><strong>Presentation software</strong> such as Microsoft PowerPoint and LibreOffice Impress, used to create slides.</li>
<li><strong>Web browsers</strong> such as Google Chrome, Microsoft Edge and Mozilla Firefox, used to visit websites.</li>
<li><strong>Email clients</strong> such as Microsoft Outlook and Mozilla Thunderbird, used to send and receive email.</li>
<li><strong>Mobile apps</strong> such as Airtel Money, MTN MoMo, WhatsApp and Facebook, used on smartphones.</li>
</ul>

<h2>Worked Example: Choosing Software for a Poultry Business</h2>
<p>Mr Banda runs a small chicken-rearing business near Kalomo. He needs software to help his business grow. Here is how he chooses:</p>
<ol>
<li>He uses <strong>Android</strong> on his phone and <strong>Windows</strong> on his laptop; both are operating system software.</li>
<li>He writes letters to suppliers using <strong>LibreOffice Writer</strong>, which is free and legal.</li>
<li>He tracks feed costs and egg sales in <strong>LibreOffice Calc</strong>, a spreadsheet application.</li>
<li>He advertises his chickens on a WhatsApp group, which is a messaging application.</li>
<li>He receives payments through <strong>MTN MoMo</strong>, a mobile money application.</li>
</ol>
<p>Each program is application software except the operating systems on his phone and laptop.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open the computer or phone in front of you. List three pieces of application software and one piece of system software you can see.</li>
<li>Think about a small shop. Name one application the owner could use for each of these tasks: writing price lists, calculating profit, sending messages to customers, and browsing the internet.</li>
<li>Search online for "LibreOffice download" and write down why LibreOffice is useful for students who cannot afford Microsoft Office.</li>
<li>Explain to a classmate the difference between Windows and Microsoft Word in one minute.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Software</strong> — the programs and instructions that make a computer work.</li>
<li><strong>System software</strong> — software that runs the computer and manages hardware, such as an operating system.</li>
<li><strong>Application software</strong> — programs designed for specific user tasks, such as word processing or browsing.</li>
<li><strong>Operating system</strong> — system software that controls the computer and lets you run applications.</li>
<li><strong>Device driver</strong> — software that lets the operating system communicate with a hardware device.</li>
</ul>

<h2>Summary</h2>
<p>Software brings hardware to life. System software, especially the operating system, manages the computer and its devices. Application software helps users do practical work such as writing, calculating, browsing and communicating. By choosing the right software for each task, you can study more effectively, run a business more efficiently, and avoid paying for tools you do not need.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/computer/computer_software.php">W3Schools — Computer Software</a></li>
<li><a href="https://www.libreoffice.org/discover/libreoffice/">LibreOffice — What Is LibreOffice?</a></li>
<li><a href="https://learn.microsoft.com/en-us/windows/">Microsoft Learn — Windows Documentation</a></li>
<li><a href="https://support.google.com/android/answer/6174145">Google Android Help — Device Basics</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.4 Choosing and Caring for a Computer in Zambia',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to compare desktop, laptop and smartphone computers, list important factors when buying a computer in Zambia, and describe simple maintenance habits that protect your device from dust, heat, power surges and load-shedding.</p>

<h2>Which Computer Do You Need?</h2>
<p>There is no single best computer for everyone. The right choice depends on what you want to do, where you will use it, and how much money you can spend. In Zambia a school leaver may start with a smartphone, a college student may share a laptop, and a small business owner may need a desktop with a printer.</p>
<p>A <strong>desktop computer</strong> is usually cheaper for the same power and easier to upgrade, but it stays in one place. A <strong>laptop</strong> is portable and can work during load-shedding if the battery is charged. A <strong>smartphone</strong> is the most affordable computer for many Zambians and fits in a pocket, but it is harder to type long documents and run complex programs.</p>

<h2>Key Factors When Buying</h2>
<p>Before spending money, consider these points:</p>
<ul>
<li><strong>Purpose</strong> — Will you browse the internet, write assignments, run a business, edit videos or learn programming?</li>
<li><strong>Processor (CPU)</strong> — A faster CPU handles more tasks at once. For basic use an Intel Core i3 or similar is enough.</li>
<li><strong>RAM</strong> — Random Access Memory affects speed. Four gigabytes is the minimum for comfortable use; eight gigabytes is better.</li>
<li><strong>Storage</strong> — A 256 GB SSD is faster than a 1 TB traditional hard drive for most daily tasks.</li>
<li><strong>Battery life</strong> — Important for laptops and phones, especially where electricity is unreliable.</li>
<li><strong>After-sales support</strong> — Check whether there is a local warranty or repair shop in Lusaka, Livingstone or Kitwe.</li>
<li><strong>Price</strong> — Compare prices from several shops. A basic laptop may cost between K4,000 and K10,000 depending on specifications and exchange rates.</li>
</ul>

<h2>Protecting Your Computer</h2>
<p>Zambian conditions can be hard on electronics. Dust, heat, humidity and unstable power can damage computers. Here are practical protection habits:</p>
<ul>
<li>Keep the computer clean. Wipe the screen and keyboard gently with a soft, dry cloth. Use a cover when the computer is not in use.</li>
<li>Allow airflow. Do not block the cooling vents on a laptop or CPU box.</li>
<li>Use a surge protector or uninterruptible power supply to protect against power spikes during load-shedding.</li>
<li>Charge laptop and phone batteries fully when power is available so you can work during outages.</li>
<li>Install antivirus software and keep the operating system updated.</li>
<li>Back up important files to a USB drive or cloud storage in case the device is stolen or damaged.</li>
</ul>

<h2>Worked Example: Buying a College Laptop</h2>
<p>Grace has saved K5,000 from her small tailoring business. She wants a laptop for her Certificate in Computer Studies classes. She visits three shops in town and compares models. She chooses a laptop with 8 GB of RAM, a 256 GB SSD, and a battery that lasts six hours. She also buys a surge protector because her area experiences frequent power cuts. Her total budget is K6,500 including the protector and a bag. She keeps the receipt for warranty purposes.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write a list of the main things you would use a computer for. Decide whether a smartphone, laptop or desktop would be best for each task.</li>
<li>Visit at least two computer shop websites or social media pages and compare the prices of one laptop model. Note the processor, RAM and storage for each.</li>
<li>Check the battery level of your phone or laptop. Practise closing unused apps and lowering screen brightness to extend battery life.</li>
<li>Make a checklist of five things you should do to protect a computer from dust, heat and power problems.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Desktop computer</strong> — a computer designed to be used on a desk, with separate monitor, keyboard and CPU box.</li>
<li><strong>Laptop</strong> — a portable computer with screen, keyboard and trackpad built into one unit.</li>
<li><strong>RAM</strong> — temporary memory used by the computer while programs are running.</li>
<li><strong>SSD</strong> — Solid-State Drive; a fast storage device with no moving parts.</li>
<li><strong>Surge protector</strong> — a device that protects electronics from sudden increases in electrical voltage.</li>
</ul>

<h2>Summary</h2>
<p>Choosing a computer means balancing purpose, performance, portability and price. In Zambia it is also important to plan for dust, heat, unstable power and load-shedding. Good buying decisions and simple maintenance habits will help your computer last longer, work faster, and protect the documents and photos you cannot afford to lose.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/computer/computer_hardware.php">W3Schools — Computer Hardware</a></li>
<li><a href="https://learn.microsoft.com/en-us/windows/">Microsoft Learn — Windows Documentation</a></li>
<li><a href="https://support.google.com/android/answer/6174145">Google Android Help — Device Basics</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: Computer Systems, History, Hardware and Software',
            'description' => 'Test your understanding of computer history, hardware components, and the difference between system and application software.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is the main processing device inside a computer?',
                    'explanation' => 'The CPU, or Central Processing Unit, is the main chip that carries out instructions and performs calculations.',
                    'options' => [
                        ['text' => 'CPU', 'is_correct' => true],
                        ['text' => 'Monitor', 'is_correct' => false],
                        ['text' => 'Keyboard', 'is_correct' => false],
                        ['text' => 'Printer', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A smartphone contains a computer inside it.',
                    'explanation' => 'A smartphone is a small computer with a processor, memory, storage and an operating system.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which generation of computers introduced microprocessors?',
                    'explanation' => 'The fourth generation of computers is defined by microprocessors, which put a complete CPU on a single chip.',
                    'options' => [
                        ['text' => 'First generation', 'is_correct' => false],
                        ['text' => 'Second generation', 'is_correct' => false],
                        ['text' => 'Third generation', 'is_correct' => false],
                        ['text' => 'Fourth generation', 'is_correct' => true],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What type of device is a USB flash drive?',
                    'explanation' => 'A USB flash drive is a storage device used to save and move files between computers.',
                    'options' => [
                        ['text' => 'Input device', 'is_correct' => false],
                        ['text' => 'Output device', 'is_correct' => false],
                        ['text' => 'Storage device', 'is_correct' => true],
                        ['text' => 'Processing device', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Microsoft Word is an example of system software.',
                    'explanation' => 'Microsoft Word is application software because it is designed for a specific task: word processing.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of these is an example of system software?',
                    'explanation' => 'Microsoft Windows is an operating system, which is the main type of system software.',
                    'options' => [
                        ['text' => 'Microsoft Word', 'is_correct' => false],
                        ['text' => 'Microsoft Windows', 'is_correct' => true],
                        ['text' => 'Google Chrome', 'is_correct' => false],
                        ['text' => 'WhatsApp', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which input device is commonly used to move a pointer on the screen?',
                    'explanation' => 'A mouse is an input device that moves a pointer or cursor on the screen.',
                    'options' => [
                        ['text' => 'Monitor', 'is_correct' => false],
                        ['text' => 'Printer', 'is_correct' => false],
                        ['text' => 'Mouse', 'is_correct' => true],
                        ['text' => 'Speaker', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'Give one example of an output device. (one word)',
                    'explanation' => 'Output devices display or print information, such as monitors, printers and speakers.',
                    'correct_answer' => 'Monitor',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is a laptop useful during load-shedding?',
                    'explanation' => 'A laptop has a built-in battery, so it can continue working for some time without mains electricity.',
                    'options' => [
                        ['text' => 'It has a built-in battery', 'is_correct' => true],
                        ['text' => 'It does not need software', 'is_correct' => false],
                        ['text' => 'It has no screen', 'is_correct' => false],
                        ['text' => 'It is cheaper than a smartphone', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What does CPU stand for? (three words)',
                    'explanation' => 'CPU stands for Central Processing Unit, the main chip that processes instructions.',
                    'correct_answer' => 'Central Processing Unit',
                ],
            ],
        ];
    }


    private function module2Lessons(): array
    {
        return [
            [
                'title' => '2.1 What Is an Operating System?',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what an operating system does, name the most common operating systems used on desktops, laptops and phones in Zambia, and describe how the operating system helps you run programs and manage hardware.</p>

<h2>The Role of an Operating System</h2>
<p>An <strong>operating system</strong> is system software that manages all the hardware and software on a computer. It acts as a bridge between you and the machine. When you click an icon, type on the keyboard or plug in a USB drive, the operating system translates your action into instructions for the hardware.</p>
<p>Think of an operating system like the manager of a busy shop. The manager does not serve every customer personally, but makes sure the staff, stock, money and equipment work together smoothly. In the same way, the operating system manages the processor, memory, storage, screen and input devices so that your applications can run.</p>

<h2>Common Operating Systems</h2>
<p>There are many operating systems, but you will meet these most often in Zambia:</p>
<ul>
<li><strong>Microsoft Windows</strong> — the most common operating system on desktop and laptop computers in schools, offices and internet cafés.</li>
<li><strong>Android</strong> — the most popular operating system on smartphones and tablets in Zambia, used by brands such as Samsung, Tecno, Itel and Huawei.</li>
<li><strong>Apple iOS</strong> — used on iPhones and iPads; less common but popular with some professionals.</li>
<li><strong>Linux</strong> — a free and open-source operating system used on servers and by people who want a low-cost alternative to Windows. Ubuntu is a popular version.</li>
<li><strong>Chrome OS</strong> — a lightweight operating system on Chromebooks, designed mainly for web browsing and online tools.</li>
</ul>

<h2>What an Operating System Does</h2>
<p>The operating system performs several important tasks:</p>
<ul>
<li><strong>Booting the computer</strong> — starts the computer when you press the power button and loads the desktop.</li>
<li><strong>Running programs</strong> — opens and closes applications such as Word, Chrome or Excel.</li>
<li><strong>Managing files</strong> — creates, copies, moves and deletes files and folders on storage devices.</li>
<li><strong>Controlling hardware</strong> — communicates with the keyboard, mouse, monitor, printer and USB devices.</li>
<li><strong>Providing security</strong> — checks passwords, controls user accounts and protects against viruses.</li>
<li><strong>Managing memory</strong> — decides how much RAM each program can use so the computer does not slow down.</li>
</ul>

<h2>Worked Example: Starting a Windows Computer</h2>
<p>When Chanda presses the power button on a college computer, the operating system goes through these steps:</p>
<ol>
<li>The computer runs a quick hardware check called POST.</li>
<li>Windows loads from the hard drive or SSD into memory.</li>
<li>The login screen appears and Chanda types her password.</li>
<li>The desktop loads, showing icons, the taskbar and the Start menu.</li>
<li>Chanda opens Microsoft Word and begins typing her assignment.</li>
</ol>
<p>Every action she takes is managed by the operating system, even though she only sees the applications.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Look at the device you are using. What operating system is running? Write down its name and version if you can find it.</li>
<li>Open the Start menu or app drawer. Count how many applications are installed.</li>
<li>Restart the computer or phone and watch the startup screen. Can you see the name of the operating system?</li>
<li>Ask three people what operating system their phones use. Make a simple table showing how many use Android and how many use something else.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Operating system</strong> — system software that manages computer hardware and provides services for applications.</li>
<li><strong>Desktop</strong> — the main screen of a graphical operating system where icons and windows appear.</li>
<li><strong>Taskbar</strong> — a bar, usually at the bottom of the screen, that shows open programs and the Start menu.</li>
<li><strong>Booting</strong> — the process of starting a computer and loading the operating system.</li>
<li><strong>Open-source</strong> — software whose source code is freely available for anyone to view, use or modify.</li>
</ul>

<h2>Summary</h2>
<p>The operating system is the most important piece of system software on any computer. It manages hardware, runs programs, organises files and keeps the computer secure. Whether you use Windows on a college computer, Android on a smartphone or Linux on a server, the same basic principles apply. Understanding the operating system helps you troubleshoot problems and choose the right device for your needs.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://learn.microsoft.com/en-us/windows/">Microsoft Learn — Windows Documentation</a></li>
<li><a href="https://support.google.com/android/answer/6174145">Google Android Help — Device Basics</a></li>
<li><a href="https://learn.microsoft.com/en-us/windows/">Microsoft Learn — Windows Documentation</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.2 Managing Files and Folders',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create, name, copy, move and delete files and folders, explain why good file organisation matters, and use a sensible folder structure for your school or business documents.</p>

<h2>Files and Folders</h2>
<p>A <strong>file</strong> is a single piece of information stored on a computer, such as a letter, a photograph, a song or a spreadsheet. Every file has a <strong>filename</strong> and a <strong>file extension</strong> that tells you what type of file it is. For example, <code>report.docx</code> is a Word document, <code>photo.jpg</code> is an image, and <code>budget.xlsx</code> is an Excel spreadsheet.</p>
<p>A <strong>folder</strong>, also called a <strong>directory</strong>, is a container that holds related files and other folders. Folders help you organise information so you can find it quickly. Without folders, all your files would be mixed together in one long list.</p>

<h2>Why Good Organisation Matters</h2>
<p>Imagine a shop that throws every receipt, invoice and bank statement into one cardboard box. Finding last month's electricity bill would take a long time. A computer without folders is similar. Good organisation saves time, reduces errors, and protects important documents from being accidentally deleted.</p>
<p>For a student, good folders might mean separate folders for each subject or module. For a business owner, folders might separate sales, purchases, taxes and employee records.</p>

<h2>Naming Files Sensibly</h2>
<p>A good filename is clear and descriptive. Avoid names like <code>document1.docx</code> or <code>image.jpg</code> because they do not tell you what is inside. Better names include the topic and date, such as <code>Computer_Studies_Module2_Notes_June2026.docx</code> or <code>Kalomo_Shop_Sales_May2026.xlsx</code>.</p>
<p>When naming files, it is best to:</p>
<ul>
<li>Use short but meaningful names.</li>
<li>Avoid spaces if you plan to share files online; use underscores instead.</li>
<li>Include dates so you can sort by time.</li>
<li>Do not use special characters such as <code>/ \ : * ? " &lt; &gt; |</code> because these are not allowed in filenames on Windows.</li>
</ul>

<h2>Worked Example: Organising a College Project</h2>
<p>Patience is working on her Certificate in Computer Studies assignments. She creates this folder structure on her laptop:</p>
<pre><code>Computer_Studies_2026/
  Module1/
    Notes/
    Assignments/
  Module2/
    Notes/
    Assignments/
  Module3/
    Notes/
    Assignments/
</code></pre>
<p>Inside each Assignments folder she saves her work with clear filenames. When her instructor asks for last week's task, she finds it in seconds instead of searching through hundreds of files.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open File Explorer on Windows or the Files app on Android.</li>
<li>Create a new folder named "Computer_Studies_Practice" on the Desktop or in your documents.</li>
<li>Inside it create three subfolders: "Notes", "Assignments" and "Images".</li>
<li>Create a short text file in the Notes folder and name it "My_First_File.txt".</li>
<li>Practise copying the file into the Assignments folder, then rename it to "My_First_File_Copy.txt".</li>
<li>Delete the copy and then restore it from the Recycle Bin or Trash.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>File</strong> — a single stored item such as a document, image or spreadsheet.</li>
<li><strong>Folder</strong> — a container used to organise files and other folders.</li>
<li><strong>File extension</strong> — the letters after the dot in a filename, such as .docx or .xlsx.</li>
<li><strong>Directory</strong> — another name for a folder in computing.</li>
<li><strong>Backup</strong> — a copy of important files kept in a safe place.</li>
</ul>

<h2>Summary</h2>
<p>Files and folders are the basic building blocks of computer organisation. A sensible folder structure and clear filenames help students and businesses find information quickly, avoid mistakes, and keep important documents safe. Spending a few minutes organising your files today will save hours of searching later.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/intro-to-computers/">Microsoft Learn — Introduction to Computers</a></li>
<li><a href="https://support.google.com/android/answer/6167344">Google Android Help — Find, Open and Close Apps and Files</a></li>
<li><a href="https://www.w3schools.com/computer/computer_file_extensions.asp">W3Schools — File Extensions</a></li>
<li><a href="https://learn.microsoft.com/en-us/windows/">Microsoft Learn — Windows Documentation</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.3 User Accounts, Settings and Security Basics',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain why user accounts are used, change basic computer settings such as brightness and language, and follow simple security habits to protect your computer and personal information.</p>

<h2>User Accounts</h2>
<p>A <strong>user account</strong> is a way for the operating system to know who is using the computer. On a shared college computer, each student may have a separate account. This keeps each person's files, settings and browsing history private.</p>
<p>There are usually two types of accounts:</p>
<ul>
<li><strong>Administrator account</strong> — can install software, change system settings and create or delete other user accounts.</li>
<li><strong>Standard account</strong> — can use programs and save files but cannot make major changes to the computer.</li>
</ul>
<p>On a family computer the parents might have administrator accounts while the children use standard accounts. This protects the computer from accidental or harmful changes.</p>

<h2>Changing Basic Settings</h2>
<p>Every operating system has a Settings or Control Panel area. Common settings you can change include:</p>
<ul>
<li><strong>Display brightness</strong> — lower brightness to save battery during load-shedding.</li>
<li><strong>Wi-Fi and mobile data</strong> — connect to networks or turn data on and off.</li>
<li><strong>Date, time and region</strong> — set the correct time zone, which should be Africa/Lusaka for Zambia.</li>
<li><strong>Language and keyboard</strong> — choose English (United Kingdom) for British spelling.</li>
<li><strong>Passwords and screen lock</strong> — protect your account from other users.</li>
<li><strong>Updates</strong> — keep the operating system and apps up to date for security.</li>
</ul>

<h2>Basic Security Habits</h2>
<p>Computers store personal information such as passwords, photographs, messages and business records. Good security habits protect this information:</p>
<ul>
<li>Use a strong password that mixes letters, numbers and symbols. Do not use your birthday or phone number.</li>
<li>Lock the screen or log out when you step away from a shared computer.</li>
<li>Do not share your password with friends or classmates.</li>
<li>Install updates when prompted; they often fix security problems.</li>
<li>Be careful with USB drives from other people; they may contain viruses.</li>
<li>Back up important files regularly.</li>
</ul>

<h2>Worked Example: Setting Up a Shared Computer</h2>
<p>A small college lab has six Windows computers used by many students. The technician creates one administrator account and a standard account for each class. Students log in with their standard account, save work to their own cloud storage or USB drive, and log out at the end of the lesson. The administrator password is known only to the technician, so students cannot install games or remove important software.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open Settings on your computer or phone. Find the display brightness and adjust it.</li>
<li>Check the date, time and time zone. Make sure they are correct for Zambia.</li>
<li>If you are allowed, practise changing the screen lock password or PIN on your phone.</li>
<li>Write a list of three reasons why you should not share your computer password.</li>
<li>Find the update section in Settings and check whether any updates are available.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>User account</strong> — a profile that identifies a person to the operating system.</li>
<li><strong>Administrator</strong> — a user account with permission to change system settings and install software.</li>
<li><strong>Password</strong> — a secret word or phrase used to prove who you are when logging in.</li>
<li><strong>Screen lock</strong> — a feature that hides your work until you enter a password or PIN.</li>
<li><strong>Update</strong> — a newer version of software that fixes problems or improves security.</li>
</ul>

<h2>Summary</h2>
<p>User accounts keep computer use organised and secure on shared machines. Basic settings such as brightness, language and time zone make the computer comfortable to use. Simple security habits protect your personal information, your business records, and the computer itself from viruses and misuse.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/windows/">Microsoft Learn — Windows Documentation</a></li>
<li><a href="https://support.google.com/android/answer/6174145">Google Android Help — Device Basics</a></li>
<li><a href="https://www.w3schools.com/computer/computer_security.asp">W3Schools — Computer Security</a></li>
<li><a href="https://owasp.org/www-community/controls/Password_Policy">OWASP — Password Policy</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.4 Mobile Operating Systems and App Management',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe how mobile operating systems differ from desktop operating systems, install and manage apps safely on an Android phone, and use mobile settings to control data usage, storage and battery life.</p>

<h2>Mobile Operating Systems</h2>
<p>A <strong>mobile operating system</strong> is designed for smartphones and tablets. It must manage a small screen, touch input, a camera, mobile networks, GPS and a battery. The two most common mobile operating systems are <strong>Android</strong> and <strong>iOS</strong>. In Zambia, Android is by far the most common because Android phones are available at many price points.</p>
<p>Mobile operating systems use <strong>apps</strong> instead of the full desktop programs you find on Windows. Apps are downloaded from an app store. On Android the official store is the <strong>Google Play Store</strong>. Some phones also come with alternative app stores, but the Play Store is the safest place to download.</p>

<h2>Installing and Managing Apps</h2>
<p>To install a new app on Android, open the Play Store, search for the app by name, and tap <strong>Install</strong>. The Play Store checks apps for malware and removes harmful ones. You should avoid installing apps from random websites or WhatsApp links because they may steal your data or money.</p>
<p>To manage apps you already have:</p>
<ul>
<li>Open <strong>Settings &gt; Apps</strong> to see every installed app.</li>
<li>Tap an app to open its details, force it to stop, or uninstall it.</li>
<li>Clear an app's cache if it is using too much storage or behaving slowly.</li>
<li>Check app permissions to stop an app from accessing your camera, contacts or location unnecessarily.</li>
</ul>

<h2>Saving Data, Storage and Battery</h2>
<p>Mobile data in Zambia costs money, so managing it carefully is important. Android provides tools to see which apps use the most data. You can also turn off mobile data for apps you do not need online, or restrict background data so apps only use the internet when you open them.</p>
<p>Storage can fill up quickly with photos, videos and chat messages. You can free space by deleting old downloads, moving photos to cloud storage or a memory card, and uninstalling unused apps. Battery life is precious during load-shedding, so lower brightness, turn off unused connections, and close apps running in the background.</p>

<h2>Worked Example: Buying ZESCO Tokens by Phone</h2>
<p>Mrs Chileshe needs electricity tokens before evening load-shedding. She uses her Android phone:</p>
<ol>
<li>Connects to her home Wi-Fi to save mobile data.</li>
<li>Opens the MTN MoMo app from the app drawer.</li>
<li>Selects "Pay Bill", chooses "ZESCO", and enters her meter number.</li>
<li>Types K50, confirms with her PIN, and waits for an SMS with the token number.</li>
<li>Copies the token into her Notes app so she will not lose the SMS.</li>
</ol>
<p>Her mobile operating system and the MoMo app work together to make this possible.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open your phone's app drawer and count how many apps you have installed.</li>
<li>Go to Settings &gt; Apps and find the app that uses the most storage. Decide whether you still need it.</li>
<li>Check Settings &gt; Network &amp; Internet &gt; Data usage to see which apps used mobile data this week.</li>
<li>Lower your screen brightness to a comfortable level and turn on Battery Saver.</li>
<li>Check app permissions for one app. Revoke any permission that seems unnecessary.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Mobile operating system</strong> — software that controls a smartphone or tablet, such as Android or iOS.</li>
<li><strong>App</strong> — a program designed for a mobile device or computer.</li>
<li><strong>Google Play Store</strong> — the official and safest place to download Android apps.</li>
<li><strong>Cache</strong> — temporary data stored by an app to help it load faster.</li>
<li><strong>Permission</strong> — approval given to an app to access features such as the camera or location.</li>
</ul>

<h2>Summary</h2>
<p>Mobile operating systems such as Android power the smartphones that many Zambians use as their main computer. Installing apps safely from the Play Store, managing storage and data usage, and saving battery life are essential skills. These habits help you avoid scams, control costs, and keep your phone working during load-shedding.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/android/answer/6174145">Google Android Help — Device Basics</a></li>
<li><a href="https://support.google.com/googleplay/answer/2521768">Google Play Help — Install Apps</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://www.w3schools.com/computer/">W3Schools — Computer Basics</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Operating Systems and File Management',
            'description' => 'Test your understanding of operating systems, files, folders, user accounts and mobile app management.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main job of an operating system?',
                    'explanation' => 'The operating system manages hardware and software resources and provides services for applications.',
                    'options' => [
                        ['text' => 'To manage computer hardware and run programs', 'is_correct' => true],
                        ['text' => 'To create word documents only', 'is_correct' => false],
                        ['text' => 'To connect computers to the internet automatically', 'is_correct' => false],
                        ['text' => 'To delete all files on the computer', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Android is an example of a mobile operating system.',
                    'explanation' => 'Android is a mobile operating system used on smartphones and tablets.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a folder?',
                    'explanation' => 'A folder is a container that holds files and other folders.',
                    'options' => [
                        ['text' => 'report.docx', 'is_correct' => false],
                        ['text' => 'image.jpg', 'is_correct' => false],
                        ['text' => 'School_Work', 'is_correct' => true],
                        ['text' => 'song.mp3', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which account type can install software and change system settings?',
                    'explanation' => 'An administrator account has permission to install software and change system settings.',
                    'options' => [
                        ['text' => 'Standard account', 'is_correct' => false],
                        ['text' => 'Guest account', 'is_correct' => false],
                        ['text' => 'Administrator account', 'is_correct' => true],
                        ['text' => 'Student account', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'It is safe to install Android apps from any link sent on WhatsApp.',
                    'explanation' => 'Apps from unknown links may contain malware. Use the official Google Play Store instead.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why should you use folders on your computer?',
                    'explanation' => 'Folders help organise files so you can find them quickly and avoid losing them.',
                    'options' => [
                        ['text' => 'To make the computer run faster', 'is_correct' => false],
                        ['text' => 'To organise related files together', 'is_correct' => true],
                        ['text' => 'To delete files automatically', 'is_correct' => false],
                        ['text' => 'To hide files from other users', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the safest place to download Android apps? (two words)',
                    'explanation' => 'The Google Play Store is the official and safest source for Android apps.',
                    'correct_answer' => 'Google Play Store',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should you do when leaving a shared computer?',
                    'explanation' => 'Logging out or locking the screen protects your files and accounts from other users.',
                    'options' => [
                        ['text' => 'Leave all programs open', 'is_correct' => false],
                        ['text' => 'Turn off the monitor only', 'is_correct' => false],
                        ['text' => 'Log out or lock the screen', 'is_correct' => true],
                        ['text' => 'Delete the operating system', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which setting helps save battery during load-shedding?',
                    'explanation' => 'Lowering screen brightness reduces battery use and helps the phone last longer.',
                    'options' => [
                        ['text' => 'Increasing screen brightness', 'is_correct' => false],
                        ['text' => 'Turning on Bluetooth', 'is_correct' => false],
                        ['text' => 'Lowering screen brightness', 'is_correct' => true],
                        ['text' => 'Using mobile data instead of Wi-Fi', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the name of the process that starts a computer and loads the operating system? (one word)',
                    'explanation' => 'Booting is the process of starting a computer and loading the operating system.',
                    'correct_answer' => 'Booting',
                ],
            ],
        ];
    }


    private function module3Lessons(): array
    {
        return [
            [
                'title' => '3.1 Word Processing Basics',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to open a word processor, create and save a document, type and edit text, and apply basic formatting such as font size, bold and alignment.</p>

<h2>What Is Word Processing?</h2>
<p>Word processing is using a computer to create, edit and format text documents. A <strong>word processor</strong> is an application program designed for this purpose. Common word processors include <strong>Microsoft Word</strong>, <strong>LibreOffice Writer</strong> and <strong>Google Docs</strong>. Word processors are used for letters, CVs, school assignments, reports, flyers and many other documents.</p>
<p>Before computers, people typed documents on typewriters. Making a mistake meant using correction fluid or retyping the whole page. With a word processor you can correct mistakes instantly, move paragraphs around, change fonts, and save many versions of the same document.</p>

<h2>Creating and Saving a Document</h2>
<p>When you open a word processor you usually see a blank page and a blinking cursor. The cursor shows where your typed text will appear. To create a document:</p>
<ol>
<li>Open the word processor from the Start menu or desktop.</li>
<li>Type your text.</li>
<li>Click <strong>File &gt; Save As</strong> or press Ctrl+S.</li>
<li>Choose a location, type a filename, and click Save.</li>
</ol>
<p>It is important to save your work often. If the power goes off during load-shedding, you could lose everything you typed since the last save. Press <strong>Ctrl+S</strong> every few minutes as a habit.</p>

<h2>Basic Formatting</h2>
<p>Formatting changes the appearance of text. The most common formatting tools are:</p>
<ul>
<li><strong>Bold</strong> — makes text darker and thicker, useful for headings and important words.</li>
<li><strong>Italic</strong> — slants text, often used for book titles or emphasis.</li>
<li><strong>Underline</strong> — draws a line under text, sometimes used for headings.</li>
<li><strong>Font size</strong> — changes how large the text appears. Normal body text is usually 11 or 12 points.</li>
<li><strong>Alignment</strong> — positions text on the page: left-aligned, centred, right-aligned or justified.</li>
</ul>

<h2>Worked Example: Typing a Business Letter</h2>
<p>Mr Mulenga wants to write a letter to a supplier. He opens LibreOffice Writer and types the letter. He makes the heading bold and centred, the date right-aligned, and the body left-aligned. He saves the document as <code>Supplier_Letter_March2026.docx</code>. Before printing, he uses the spelling checker to find mistakes. The whole process takes ten minutes instead of the hours it would have taken with a typewriter.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open Microsoft Word, LibreOffice Writer or Google Docs.</li>
<li>Type a short paragraph about why you are studying computer studies.</li>
<li>Make the first sentence bold and the second sentence italic.</li>
<li>Change the font size of the title to 16 points and the body to 12 points.</li>
<li>Centre the title and left-align the body.</li>
<li>Save the document with a clear filename in your Computer_Studies_Practice folder.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Word processor</strong> — software used to create, edit and format text documents.</li>
<li><strong>Cursor</strong> — the blinking line that shows where text will appear when you type.</li>
<li><strong>Font</strong> — the style and design of text letters.</li>
<li><strong>Alignment</strong> — the position of text on a page, such as left, centre, right or justified.</li>
<li><strong>Save</strong> — to store a document on a computer or storage device so you can open it later.</li>
</ul>

<h2>Summary</h2>
<p>Word processing is one of the most useful computer skills for students and businesses. A word processor lets you create documents quickly, correct mistakes easily, and apply professional formatting. Learning to save your work regularly and use basic formatting tools will help you produce clear, attractive documents for school, work and personal use.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/intro-to-computers/">Microsoft Learn — Introduction to Computers</a></li>
<li><a href="https://www.libreoffice.org/discover/libreoffice/">LibreOffice — What Is LibreOffice?</a></li>
<li><a href="https://support.google.com/docs/answer/7068618">Google Docs Help — Create a Document</a></li>
<li><a href="https://www.w3schools.com/">W3Schools</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.2 Formatting Documents: Letters, CVs and Reports',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to format a formal letter, create a simple curriculum vitae, and structure a short report using headings, page numbers and lists.</p>

<h2>Formatting a Formal Letter</h2>
<p>A formal letter follows a standard layout. Whether you are writing to a school, a bank or a government office, a well-formatted letter looks professional and is easier to read. A typical formal letter includes:</p>
<ol>
<li>Your address at the top right or left.</li>
<li>The date.</li>
<li>The recipient's address and title.</li>
<li>A greeting such as "Dear Sir or Madam" or "Dear Mr Banda".</li>
<li>The body of the letter in short, clear paragraphs.</li>
<li>A closing such as "Yours faithfully" or "Yours sincerely".</li>
<li>Your signature and typed name.</li>
</ol>
<p>Use single spacing within paragraphs and a blank line between paragraphs. Align the body to the left. Keep the font professional, such as Times New Roman or Arial, at 12 points.</p>

<h2>Creating a Curriculum Vitae</h2>
<p>A <strong>curriculum vitae</strong>, or CV, is a document that summarises your education, skills, work experience and contact details. Employers read CVs quickly, so the layout must be clear. A simple CV includes:</p>
<ul>
<li>Full name and contact information at the top.</li>
<li>A short personal statement or career objective.</li>
<li>Education history, starting with the most recent.</li>
<li>Work experience, starting with the most recent.</li>
<li>Skills relevant to the job.</li>
<li>Referees, if requested.</li>
</ul>
<p>Use bold headings and bullet points to make the CV easy to scan. Avoid colours and fancy designs unless you are applying for a design job. Save the file as a PDF when sending it by email so the formatting does not change.</p>

<h2>Structuring a Report</h2>
<p>A report presents information in an organised way. A short college report might have:</p>
<ol>
<li>A title page with the title, your name and the date.</li>
<li>Headings and subheadings to divide the content.</li>
<li>Numbered or bulleted lists where appropriate.</li>
<li>Page numbers in the footer.</li>
<li>A short conclusion at the end.</li>
</ol>

<h2>Worked Example: Formatting a Job Application Letter</h2>
<p>Agness is applying for a shop assistant position. She opens LibreOffice Writer and sets the page margins to 2.5 centimetres. She types her address, the date, the shop's address and a formal greeting. She writes three short paragraphs explaining why she wants the job and what skills she has. She uses bold for the subject line "Application for Shop Assistant Position" and saves the document as PDF before emailing it.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a formal letter to a college asking for information about a course.</li>
<li>Format the letter with your address, the date, a greeting, three paragraphs and a closing.</li>
<li>Create a one-page CV for yourself or a fictional person. Use bold headings and bullet points.</li>
<li>Insert page numbers at the bottom of a two-page document.</li>
<li>Save both documents as PDF files.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Formal letter</strong> — a letter written in a polite, professional style for official purposes.</li>
<li><strong>Curriculum vitae (CV)</strong> — a document that summarises a person's education, skills and work experience.</li>
<li><strong>PDF</strong> — Portable Document Format; a file type that keeps formatting the same on any device.</li>
<li><strong>Margin</strong> — the empty space around the edges of a page.</li>
<li><strong>Bullet point</strong> — a dot or symbol used to introduce items in a list.</li>
</ul>

<h2>Summary</h2>
<p>Good document formatting makes letters, CVs and reports easier to read and more professional. A formal letter follows a clear structure, a CV highlights your strengths, and a report uses headings and lists to organise information. Practising these formats prepares you for job applications, college assignments and business communication.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/intro-to-computers/">Microsoft Learn — Introduction to Computers</a></li>
<li><a href="https://www.libreoffice.org/discover/libreoffice/">LibreOffice — What Is LibreOffice?</a></li>
<li><a href="https://support.google.com/docs/answer/7068618">Google Docs Help</a></li>
<li><a href="https://www.w3schools.com/">W3Schools</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.3 Presentation Basics',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create a simple presentation, add and format slides, insert text and images, and explain when presentations are useful in school and business.</p>

<h2>What Is Presentation Software?</h2>
<p>Presentation software is used to create slideshows. A slideshow is a series of slides shown one after another, usually on a screen or projector. Common presentation programs include <strong>Microsoft PowerPoint</strong>, <strong>LibreOffice Impress</strong> and <strong>Google Slides</strong>.</p>
<p>People use presentations to teach lessons, explain business ideas, report project progress, or share information at meetings. A student might present a project to the class. A farmer group might present crop results to a cooperative. A shop owner might present a sales plan to a bank.</p>

<h2>Creating Your First Slides</h2>
<p>A typical presentation has:</p>
<ul>
<li>A <strong>title slide</strong> with the topic and presenter's name.</li>
<li><strong>Content slides</strong> with headings and short bullet points.</li>
<li>A <strong>conclusion slide</strong> that summarises the main message.</li>
</ul>
<p>To add a slide, look for a "New Slide" button. To change the layout, choose from options such as Title Slide, Title and Content, or Two Content. Keep text short. Slides are not essays; they support what the speaker says.</p>

<h2>Formatting Tips</h2>
<p>Good presentations are easy to read from the back of a room. Follow these tips:</p>
<ul>
<li>Use a large font, at least 24 points for body text and larger for titles.</li>
<li>Use high contrast between text and background, such as dark text on a light background.</li>
<li>Limit each slide to one main idea and a few bullet points.</li>
<li>Use images and charts only when they help explain the point.</li>
<li>Avoid too many animations; they distract the audience.</li>
</ul>

<h2>Worked Example: A Presentation for a Market Stall</h2>
<p>Joseph sells second-hand clothes at Soweto Market. He has been asked to explain his business idea at a local youth group meeting. He creates a five-slide presentation:</p>
<ol>
<li>Title slide: "Growing My Second-Hand Clothes Business".</li>
<li>Slide 2: "What I Sell" with photos of clothes and price ranges in Kwacha.</li>
<li>Slide 3: "My Customers" with a simple bullet list of target buyers.</li>
<li>Slide 4: "My Plan" showing how he will use WhatsApp to advertise.</li>
<li>Slide 5: "Thank You" with his phone number for orders.</li>
</ol>
<p>The slides are simple, clear and support his spoken explanation.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open Microsoft PowerPoint, LibreOffice Impress or Google Slides.</li>
<li>Create a title slide for a topic you know well, such as "My Village" or "How to Rear Chickens".</li>
<li>Add three content slides with short bullet points.</li>
<li>Insert one image on one slide, either from the internet or your own photos.</li>
<li>Change the slide design or theme so the text is easy to read.</li>
<li>Practise presenting your slides to a friend or classmate.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Presentation software</strong> — a program used to create and display slideshows.</li>
<li><strong>Slide</strong> — a single page or screen in a presentation.</li>
<li><strong>Theme</strong> — a pre-designed set of colours, fonts and layouts for a presentation.</li>
<li><strong>Layout</strong> — the arrangement of text and objects on a slide.</li>
<li><strong>Animation</strong> — a movement effect applied to text or objects on a slide.</li>
</ul>

<h2>Summary</h2>
<p>Presentation software helps you share ideas clearly with an audience. A well-designed presentation uses short text, readable fonts, relevant images and a logical flow. Whether you are presenting at school, in church, in a community meeting or to a potential customer, these skills help you communicate with confidence.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/intro-to-computers/">Microsoft Learn — Introduction to Computers</a></li>
<li><a href="https://www.libreoffice.org/discover/libreoffice/">LibreOffice — What Is LibreOffice?</a></li>
<li><a href="https://support.google.com/slides/answer/3161456">Google Slides Help</a></li>
<li><a href="https://www.canva.com/designschool/">Canva Design School</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.4 Designing Effective Slides and Delivering a Talk',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to apply design principles to presentation slides, avoid common mistakes, and practise simple speaking habits that help you deliver a clear and confident talk.</p>

<h2>Design Principles for Slides</h2>
<p>Effective slides support the speaker; they do not replace the speaker. The audience should listen to you and glance at the slides for key points. To achieve this, keep each slide simple and focused.</p>
<p>Use the <strong>6x6 rule</strong> as a guide: no more than six bullet points per slide and no more than six words per bullet point. This forces you to summarise. Use headings, images and diagrams instead of long paragraphs.</p>

<h2>Choosing Colours and Fonts</h2>
<p>Colour and font choices affect readability. Use dark text on a light background for printed handouts and well-lit rooms. Use light text on a dark background for dark rooms, such as during a projector presentation. Avoid bright red and green together because some people cannot distinguish them easily.</p>
<p>Choose one or two fonts for the whole presentation. A common combination is a bold font for titles and a simple font for body text. Do not use more than three colours. Consistency makes the presentation look professional.</p>

<h2>Common Mistakes to Avoid</h2>
<ul>
<li>Too much text on one slide.</li>
<li>Reading every word from the slide instead of speaking naturally.</li>
<li>Too many animations or sound effects.</li>
<li>Low-resolution images that look blurry when projected.</li>
<li>Inconsistent fonts, colours and slide layouts.</li>
</ul>

<h2>Delivering the Talk</h2>
<p>Good slides are only half of a good presentation. You also need to speak clearly. Practise these habits:</p>
<ul>
<li>Face the audience, not the screen.</li>
<li>Speak slowly and loudly enough for everyone to hear.</li>
<li>Use simple language and explain technical words.</li>
<li>Make eye contact with different people in the room.</li>
<li>Pause after important points so the audience can think.</li>
<li>Prepare for questions by anticipating what people might ask.</li>
</ul>

<h2>Worked Example: Improving a Bad Slide</h2>
<p>Here is a bad slide:</p>
<blockquote>
<p>My Business Plan: I want to start a poultry business because there is a high demand for chickens in my area and I have some experience from helping my uncle and I think I can make money if I work hard and save my profits and reinvest in more chicks and better feed.</p>
</blockquote>
<p>A better version has a title "Why Poultry?" and three bullet points:</p>
<ul>
<li>High local demand for chickens.</li>
<li>Experience from family poultry work.</li>
<li>Profits reinvested for growth.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Open your presentation from the previous lesson.</li>
<li>Reduce the text on each slide to short bullet points.</li>
<li>Check that all fonts and colours are consistent.</li>
<li>Remove any animation that does not help understanding.</li>
<li>Practise presenting in front of a mirror or record yourself on your phone.</li>
<li>Ask a friend for one thing you did well and one thing to improve.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>6x6 rule</strong> — a presentation guideline: no more than six bullet points and six words per point.</li>
<li><strong>Contrast</strong> — the difference in brightness between text and background.</li>
<li><strong>Consistency</strong> — using the same styles, fonts and colours throughout a presentation.</li>
<li><strong>Eye contact</strong> — looking at audience members while speaking.</li>
<li><strong>Readability</strong> — how easy text is to read quickly.</li>
</ul>

<h2>Summary</h2>
<p>Good presentations combine clear slide design with confident speaking. Keep slides simple, choose readable colours and fonts, and avoid common mistakes such as reading every word. Practising your delivery will help you share your ideas effectively in any setting, from a classroom to a community meeting.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/intro-to-computers/">Microsoft Learn — Introduction to Computers</a></li>
<li><a href="https://www.libreoffice.org/discover/libreoffice/">LibreOffice — What Is LibreOffice?</a></li>
<li><a href="https://www.canva.com/designschool/">Canva Design School</a></li>
<li><a href="https://support.google.com/slides/answer/3161456">Google Slides Help</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Word Processing and Presentations',
            'description' => 'Test your understanding of word processing, document formatting, and presentation design.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which key combination is commonly used to save a document?',
                    'explanation' => 'Ctrl+S is the standard keyboard shortcut for saving a document.',
                    'options' => [
                        ['text' => 'Ctrl+P', 'is_correct' => false],
                        ['text' => 'Ctrl+S', 'is_correct' => true],
                        ['text' => 'Ctrl+C', 'is_correct' => false],
                        ['text' => 'Ctrl+Z', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A CV should always include your favourite hobbies and colours.',
                    'explanation' => 'A CV should focus on education, skills and experience relevant to the job.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which program is an example of presentation software?',
                    'explanation' => 'Microsoft PowerPoint is a widely used presentation program.',
                    'options' => [
                        ['text' => 'Microsoft Excel', 'is_correct' => false],
                        ['text' => 'Microsoft PowerPoint', 'is_correct' => true],
                        ['text' => 'Microsoft Word', 'is_correct' => false],
                        ['text' => 'Mozilla Firefox', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does the 6x6 rule recommend?',
                    'explanation' => 'The 6x6 rule suggests no more than six bullet points and six words per point.',
                    'options' => [
                        ['text' => 'Six slides per presentation', 'is_correct' => false],
                        ['text' => 'Six images per slide', 'is_correct' => false],
                        ['text' => 'Six bullet points and six words each', 'is_correct' => true],
                        ['text' => 'Six colours per slide', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Slides should contain long paragraphs that the speaker reads aloud.',
                    'explanation' => 'Slides should use short bullet points that support the speaker, not replace them.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which file format is best for sending a document that should keep its formatting?',
                    'explanation' => 'PDF preserves formatting across different devices and programs.',
                    'options' => [
                        ['text' => 'TXT', 'is_correct' => false],
                        ['text' => 'PDF', 'is_correct' => true],
                        ['text' => 'BMP', 'is_correct' => false],
                        ['text' => 'MP3', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What does CV stand for? (two words)',
                    'explanation' => 'CV stands for Curriculum Vitae, a summary of education and experience.',
                    'correct_answer' => 'Curriculum Vitae',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which font size is recommended as a minimum for presentation body text?',
                    'explanation' => 'Body text should be at least 24 points so it can be read from the back of a room.',
                    'options' => [
                        ['text' => '8 points', 'is_correct' => false],
                        ['text' => '12 points', 'is_correct' => false],
                        ['text' => '24 points', 'is_correct' => true],
                        ['text' => '60 points', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should you do when presenting to an audience?',
                    'explanation' => 'Facing the audience and speaking clearly helps you connect with listeners.',
                    'options' => [
                        ['text' => 'Read every word from the slides', 'is_correct' => false],
                        ['text' => 'Face the screen and whisper', 'is_correct' => false],
                        ['text' => 'Face the audience and speak clearly', 'is_correct' => true],
                        ['text' => 'Use as many animations as possible', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'Name one example of word processing software. (one word)',
                    'explanation' => 'Microsoft Word, LibreOffice Writer and Google Docs are all word processors.',
                    'correct_answer' => 'Word',
                ],
            ],
        ];
    }


    private function module4Lessons(): array
    {
        return [
            [
                'title' => '4.1 Spreadsheet Basics',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to open a spreadsheet, identify rows, columns and cells, enter and edit data, and understand why spreadsheets are useful for small businesses and personal budgets.</p>

<h2>What Is a Spreadsheet?</h2>
<p>A spreadsheet is a computer program used to organise data in rows and columns. The data is stored in cells. A cell is the box where a row and a column meet. Each cell has an address, such as A1, B2 or C10, made from the column letter and the row number.</p>
<p>Common spreadsheet programs include <strong>Microsoft Excel</strong>, <strong>LibreOffice Calc</strong> and <strong>Google Sheets</strong>. Spreadsheets are used for budgets, invoices, sales records, gradebooks, stock lists and many other tasks where numbers need to be added, sorted or analysed.</p>

<h2>Rows, Columns and Cells</h2>
<p>A spreadsheet is like a large table. <strong>Columns</strong> run vertically and are labelled with letters: A, B, C and so on. <strong>Rows</strong> run horizontally and are labelled with numbers: 1, 2, 3 and so on. A <strong>cell</strong> is the intersection of one row and one column.</p>
<p>If you click on the cell in column B and row 3, the cell address is B3. The cell that is currently selected is called the <strong>active cell</strong>. You can type numbers, text or formulas into a cell.</p>

<h2>Entering and Editing Data</h2>
<p>To enter data, click a cell and type. Press Enter to move down to the next cell, or Tab to move right. If you make a mistake, click the cell and type again, or double-click to edit inside the cell.</p>
<p>Spreadsheets can recognise different types of data:</p>
<ul>
<li><strong>Numbers</strong> such as 50 or 1250.50 can be used in calculations.</li>
<li><strong>Text</strong> such as "Tomatoes" or "Rent" labels your data.</li>
<li><strong>Dates</strong> such as 15/06/2026 can be sorted and used in calculations.</li>
<li><strong>Formulas</strong> begin with an equals sign and perform calculations.</li>
</ul>

<h2>Worked Example: A Simple Shop Stock List</h2>
<p>Mrs Mwanza runs a small grocery shop. She creates a spreadsheet to track stock. Column A lists item names, column B lists quantities, and column C lists prices in Kwacha. The first few rows look like this:</p>
<table>
<tr><th>Item</th><th>Quantity</th><th>Price (K)</th></tr>
<tr><td>Sugar</td><td>20</td><td>45.00</td></tr>
<tr><td>Salt</td><td>15</td><td>8.00</td></tr>
<tr><td>Soap</td><td>30</td><td>12.00</td></tr>
</table>
<p>This simple list helps her see what is in stock without counting items by hand.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open Microsoft Excel, LibreOffice Calc or Google Sheets.</li>
<li>Type "Item" in cell A1, "Quantity" in cell B1, and "Price" in cell C1.</li>
<li>Enter at least five items you might find in a shop.</li>
<li>Click on different cells and note their addresses in the Name Box.</li>
<li>Save the spreadsheet with a clear filename.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Spreadsheet</strong> — a program for organising and calculating data in rows and columns.</li>
<li><strong>Cell</strong> — the box formed where a row and a column meet.</li>
<li><strong>Row</strong> — a horizontal line of cells in a spreadsheet.</li>
<li><strong>Column</strong> — a vertical line of cells in a spreadsheet.</li>
<li><strong>Active cell</strong> — the cell that is currently selected and ready for input.</li>
</ul>

<h2>Summary</h2>
<p>A spreadsheet is a powerful tool for organising numbers and text. By understanding rows, columns and cells, you can create lists, budgets and records for personal or business use. Spreadsheets save time, reduce arithmetic errors, and make it easy to update information when prices or quantities change.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/intro-to-computers/">Microsoft Learn — Introduction to Computers</a></li>
<li><a href="https://www.libreoffice.org/discover/libreoffice/">LibreOffice — What Is LibreOffice?</a></li>
<li><a href="https://support.google.com/sheets/answer/4933138">Google Sheets Help</a></li>
<li><a href="https://www.w3schools.com/">W3Schools</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.2 Formulas and Functions for Small Business',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to write simple formulas in a spreadsheet, use common functions such as SUM, AVERAGE and COUNT, and apply these skills to a small business budget or sales record.</p>

<h2>What Is a Formula?</h2>
<p>A <strong>formula</strong> is a calculation written in a spreadsheet cell. Every formula begins with an equals sign <code>=</code>. The spreadsheet calculates the result automatically. If you change a number used in the formula, the result updates immediately.</p>
<p>For example, if cell A1 contains 50 and cell B1 contains 30, the formula <code>=A1+B1</code> in cell C1 will show 80. If you change A1 to 60, C1 will change to 90 without you retyping anything.</p>

<h2>Basic Arithmetic in Spreadsheets</h2>
<p>You can use these operators in spreadsheet formulas:</p>
<ul>
<li><code>+</code> for addition</li>
<li><code>-</code> for subtraction</li>
<li><code>*</code> for multiplication</li>
<li><code>/</code> for division</li>
</ul>
<p>For example, <code>=B2*C2</code> multiplies the values in cells B2 and C2. <code>=D2-D3</code> subtracts D3 from D2.</p>

<h2>Common Functions</h2>
<p>A <strong>function</strong> is a built-in formula with a special name. Common functions include:</p>
<ul>
<li><code>=SUM(A1:A10)</code> — adds all numbers from A1 to A10.</li>
<li><code>=AVERAGE(A1:A10)</code> — finds the average of the numbers.</li>
<li><code>=COUNT(A1:A10)</code> — counts how many cells contain numbers.</li>
<li><code>=MAX(A1:A10)</code> — finds the largest number.</li>
<li><code>=MIN(A1:A10)</code> — finds the smallest number.</li>
</ul>

<h2>Worked Example: Calculating Shop Sales</h2>
<p>Mr Tembo sells airtime, sugar and soap. He records his sales for one week in a spreadsheet. Column A lists items, column B lists quantities sold, and column C lists price per item. In column D he enters the formula <code>=B2*C2</code> to calculate total income for each item. At the bottom he uses <code>=SUM(D2:D10)</code> to find the weekly total. The spreadsheet shows he earned K1,245.50 during the week.</p>
<p>If he later notices he sold ten more airtime vouchers than he first recorded, he changes the quantity in B2. The total in column D and the weekly total both update automatically.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open a spreadsheet and create a table with columns for Item, Quantity, Price and Total.</li>
<li>Enter five items with quantities and prices.</li>
<li>In the Total column, write a formula to multiply Quantity by Price.</li>
<li>At the bottom, use the SUM function to calculate the grand total.</li>
<li>Change one quantity and watch the totals update.</li>
<li>Calculate the average price using the AVERAGE function.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Formula</strong> — a calculation in a spreadsheet that starts with an equals sign.</li>
<li><strong>Function</strong> — a built-in formula with a name, such as SUM or AVERAGE.</li>
<li><strong>Operator</strong> — a symbol that performs a calculation, such as +, -, * or /.</li>
<li><strong>Cell reference</strong> — the address of a cell used in a formula, such as A1 or B2.</li>
<li><strong>Range</strong> — a group of cells, such as A1:A10.</li>
</ul>

<h2>Summary</h2>
<p>Formulas and functions turn a spreadsheet from a simple list into a powerful calculator. By using arithmetic operators and functions such as SUM and AVERAGE, you can manage budgets, track sales, and analyse business data. Spreadsheets update automatically when numbers change, saving time and reducing errors for small businesses in Zambia.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/intro-to-computers/">Microsoft Learn — Introduction to Computers</a></li>
<li><a href="https://www.libreoffice.org/discover/libreoffice/">LibreOffice — What Is LibreOffice?</a></li>
<li><a href="https://support.google.com/sheets/answer/46977">Google Sheets Help — Functions</a></li>
<li><a href="https://www.w3schools.com/">W3Schools</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.3 Introduction to Databases',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what a database is, identify common uses of databases in Zambia, and describe the main parts of a database: tables, records and fields.</p>

<h2>What Is a Database?</h2>
<p>A <strong>database</strong> is an organised collection of data that is stored electronically. Unlike a spreadsheet, which is mainly used for calculations, a database is designed to store large amounts of related information and find it quickly. Businesses, schools, hospitals, banks and government departments all use databases.</p>
<p>For example, a school might keep a database of students. Each student has a name, class, date of birth and parent contact number. A shop might keep a database of customers and their purchase history. The Road Transport and Safety Agency keeps a database of vehicle registrations. ZRA keeps a database of taxpayers and TPINs.</p>

<h2>Database Management Systems</h2>
<p>A <strong>Database Management System (DBMS)</strong> is software used to create and manage databases. Examples include:</p>
<ul>
<li><strong>Microsoft Access</strong> — often used in small businesses and schools.</li>
<li><strong>LibreOffice Base</strong> — a free and open-source database program.</li>
<li><strong>MySQL</strong> and <strong>PostgreSQL</strong> — used for larger systems and websites.</li>
<li><strong>SQLite</strong> — used inside mobile apps and small programs.</li>
</ul>

<h2>Tables, Records and Fields</h2>
<p>A database stores data in <strong>tables</strong>. A table looks like a spreadsheet but has a fixed structure. Each row in a table is called a <strong>record</strong>. Each column is called a <strong>field</strong>.</p>
<p>Imagine a customer table for a shop:</p>
<table>
<tr><th>CustomerID</th><th>FirstName</th><th>LastName</th><th>Phone</th></tr>
<tr><td>1</td><td>John</td><td>Banda</td><td>0977123456</td></tr>
<tr><td>2</td><td>Mary</td><td>Chileshe</td><td>0966123456</td></tr>
</table>
<p>Each row is one record. Each column is one field. The CustomerID is a special field that uniquely identifies each customer.</p>

<h2>Worked Example: A School Student Database</h2>
<p>Edutrack Computer Training College keeps a database of students. The database has a table called Students with fields such as StudentID, FirstName, LastName, Course, Phone and DateEnrolled. When a new student enrols, the college adds a new record. When a student changes phone number, the college updates that one field. When the college wants a list of all Computer Studies students, the database can produce it in seconds.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Think of three places in your community that probably use databases.</li>
<li>Draw a simple table for a shop's customers. Label the fields and records.</li>
<li>Write down five fields that might be in a database of books in a library.</li>
<li>Search online for "what is a database" and read one explanation. Note the website.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Database</strong> — an organised collection of data stored electronically.</li>
<li><strong>DBMS</strong> — Database Management System; software used to create and manage databases.</li>
<li><strong>Table</strong> — a collection of related records in a database.</li>
<li><strong>Record</strong> — one complete row of information in a table.</li>
<li><strong>Field</strong> — one column in a table that stores one type of information.</li>
</ul>

<h2>Summary</h2>
<p>Databases are everywhere in modern life, from schools and shops to government agencies. They store related information in tables made of records and fields. A Database Management System helps people create, update and search databases efficiently. Understanding databases prepares you to work with information systems in any organisation.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/sql/">W3Schools — SQL Tutorial</a></li>
<li><a href="https://www.libreoffice.org/discover/libreoffice/">LibreOffice — What Is LibreOffice?</a></li>
<li><a href="https://learn.microsoft.com/en-us/office/client-developer/access/">Microsoft Learn — Access</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming/sql">Khan Academy — SQL</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.4 Creating a Simple Table and Query',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to design a simple database table, enter sample data, and understand how a query can find specific records quickly.</p>

<h2>Designing a Table</h2>
<p>Before creating a table, you must decide what information you want to store. Each piece of information becomes a field. A good table design avoids repeating information and makes searching easy.</p>
<p>For example, if you want to keep a list of club members, useful fields might be:</p>
<ul>
<li>MemberID — a unique number for each member.</li>
<li>FirstName</li>
<li>LastName</li>
<li>PhoneNumber</li>
<li>JoinDate</li>
<li>MembershipType</li>
</ul>
<p>The MemberID field is special because it uniquely identifies each member. It is called a <strong>primary key</strong>.</p>

<h2>Data Types</h2>
<p>Each field has a <strong>data type</strong> that tells the database what kind of information to store. Common data types include:</p>
<ul>
<li><strong>Text</strong> — for names, addresses and descriptions.</li>
<li><strong>Number</strong> — for quantities, prices and IDs.</li>
<li><strong>Date/Time</strong> — for dates of birth, joining dates and appointments.</li>
<li><strong>Yes/No</strong> — for true or false values, such as whether a member has paid fees.</li>
</ul>

<h2>What Is a Query?</h2>
<p>A <strong>query</strong> is a question you ask the database. Queries help you find specific records without reading the whole table. For example, you could ask the database to show only members who joined after January 2026, or only members whose phone numbers start with 0977.</p>
<p>In a simple DBMS such as Microsoft Access or LibreOffice Base, you can create queries by filling in a form. In larger systems you write queries in a language called <strong>SQL</strong>.</p>

<h2>Worked Example: A Church Group Membership Database</h2>
<p>A church group in Kalomo wants to keep track of members who volunteer for community work. They create a Members table with fields MemberID, FirstName, LastName, Phone, Area and CanDrive. They enter records for twenty members. Later they create a query to find all members who live in the town area and can drive. The database returns four names, and the organisers call them to help transport food donations.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open Microsoft Access, LibreOffice Base or Google Sheets as a simple table.</li>
<li>Design a table for a small club or business. List at least five fields and their data types.</li>
<li>Enter at least ten sample records.</li>
<li>Ask a simple question that a query could answer, such as "Who joined in 2026?"</li>
<li>Search online for "SQL SELECT statement" and read what it does.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Primary key</strong> — a field that uniquely identifies each record in a table.</li>
<li><strong>Data type</strong> — the kind of information a field can hold, such as text or number.</li>
<li><strong>Query</strong> — a request to find or manipulate data in a database.</li>
<li><strong>SQL</strong> — Structured Query Language; a standard language for working with databases.</li>
<li><strong>Record</strong> — one complete set of fields in a table.</li>
</ul>

<h2>Summary</h2>
<p>Designing a database table means choosing the right fields and data types. A primary key gives each record a unique identity, and queries let you find information quickly. Even a simple database can save hours of searching through paper records and help small organisations work more efficiently.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/sql/">W3Schools — SQL Tutorial</a></li>
<li><a href="https://www.libreoffice.org/discover/libreoffice/">LibreOffice — What Is LibreOffice?</a></li>
<li><a href="https://learn.microsoft.com/en-us/office/client-developer/access/">Microsoft Learn — Access</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming/sql">Khan Academy — SQL</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module4Quiz(): array
    {
        return [
            'title' => 'Module 4 Quiz: Spreadsheets and Databases',
            'description' => 'Test your understanding of spreadsheet formulas, functions, and basic database concepts.',
            'time_limit_minutes' => 25,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What symbol must begin every spreadsheet formula?',
                    'explanation' => 'All spreadsheet formulas begin with an equals sign (=).',
                    'options' => [
                        ['text' => '#', 'is_correct' => false],
                        ['text' => '=', 'is_correct' => true],
                        ['text' => '$', 'is_correct' => false],
                        ['text' => '@', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'In a spreadsheet, a cell is the intersection of a row and a column.',
                    'explanation' => 'A cell is where a row and a column meet.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which function adds a range of numbers?',
                    'explanation' => 'The SUM function adds all numbers in a specified range.',
                    'options' => [
                        ['text' => 'AVERAGE', 'is_correct' => false],
                        ['text' => 'COUNT', 'is_correct' => false],
                        ['text' => 'SUM', 'is_correct' => true],
                        ['text' => 'MAX', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is a row in a database table called?',
                    'explanation' => 'Each row in a database table is one record.',
                    'options' => [
                        ['text' => 'Field', 'is_correct' => false],
                        ['text' => 'Record', 'is_correct' => true],
                        ['text' => 'Table', 'is_correct' => false],
                        ['text' => 'Query', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A database is mainly designed for drawing pictures.',
                    'explanation' => 'A database is designed for storing and retrieving organised data, not drawing.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a database management system?',
                    'explanation' => 'Microsoft Access is an example of a database management system.',
                    'options' => [
                        ['text' => 'Microsoft Word', 'is_correct' => false],
                        ['text' => 'Microsoft Excel', 'is_correct' => false],
                        ['text' => 'Microsoft Access', 'is_correct' => true],
                        ['text' => 'Microsoft PowerPoint', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What does DBMS stand for? (four words)',
                    'explanation' => 'DBMS stands for Database Management System.',
                    'correct_answer' => 'Database Management System',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does the formula =B2*C2 do?',
                    'explanation' => 'The * symbol means multiplication, so the formula multiplies B2 by C2.',
                    'options' => [
                        ['text' => 'Adds B2 and C2', 'is_correct' => false],
                        ['text' => 'Multiplies B2 by C2', 'is_correct' => true],
                        ['text' => 'Divides B2 by C2', 'is_correct' => false],
                        ['text' => 'Subtracts C2 from B2', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which field uniquely identifies each record in a table?',
                    'explanation' => 'The primary key uniquely identifies each record.',
                    'options' => [
                        ['text' => 'Foreign key', 'is_correct' => false],
                        ['text' => 'Primary key', 'is_correct' => true],
                        ['text' => 'Query key', 'is_correct' => false],
                        ['text' => 'Data key', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'Which function finds the average of a range of numbers? (one word)',
                    'explanation' => 'The AVERAGE function calculates the mean of a range.',
                    'correct_answer' => 'AVERAGE',
                ],
            ],
        ];
    }


    private function module5Lessons(): array
    {
        return [
            [
                'title' => '5.1 Computer Networks: LAN, WAN and Wireless',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what a computer network is, describe the difference between LAN and WAN, compare wired and wireless connections, and identify common networking devices used in Zambia.</p>

<h2>What Is a Network?</h2>
<p>A <strong>computer network</strong> is two or more computers connected together so they can share resources. Resources that can be shared include files, printers, internet access and software. Networks are everywhere: in schools, offices, internet cafés, banks and homes.</p>
<p>In a small shop, a network might connect one computer to a receipt printer and the internet. In a college, a network connects dozens of computers in different rooms so students can share files and access online lessons. At home, a Wi-Fi network lets family members use the same internet connection on phones, laptops and tablets.</p>

<h2>LAN and WAN</h2>
<p>Networks are often classified by size:</p>
<ul>
<li><strong>LAN</strong> — Local Area Network. Covers a small area such as a home, office or school building. A college computer lab usually uses a LAN.</li>
<li><strong>WAN</strong> — Wide Area Network. Covers a large area such as a town, country or the whole world. The internet is the largest WAN.</li>
</ul>
<p>A LAN is usually faster and cheaper to manage because the cables and devices are in one place. A WAN needs telephone lines, fibre optic cables, satellites or mobile networks to connect distant places.</p>

<h2>Wired and Wireless Connections</h2>
<p>Computers can connect to a network using cables or wireless signals.</p>
<ul>
<li><strong>Wired</strong> connections use Ethernet cables. They are usually fast and reliable but limit where you can place the computer.</li>
<li><strong>Wireless</strong> connections use radio waves, often called Wi-Fi. They allow devices to connect without cables, which is convenient for phones and laptops.</li>
</ul>
<p>In Zambia, many homes and small businesses use Wi-Fi routers connected to an internet service provider. Mobile phones also connect to the internet wirelessly through Airtel, MTN or Zamtel networks.</p>

<h2>Common Networking Devices</h2>
<ul>
<li><strong>Router</strong> — directs data between devices on a network and connects the network to the internet.</li>
<li><strong>Switch</strong> — connects multiple computers in a LAN using cables.</li>
<li><strong>Wireless access point</strong> — creates a Wi-Fi area so wireless devices can join the network.</li>
<li><strong>Modem</strong> — connects a network to an internet service provider using a phone line, fibre or mobile signal.</li>
<li><strong>Network interface card</strong> — the hardware inside a computer that allows it to connect to a network.</li>
</ul>

<h2>Worked Example: A College Computer Lab Network</h2>
<p>Edutrack College has a computer lab with twenty student computers. The computers connect to a switch using Ethernet cables. The switch connects to a router, which connects to the internet through a modem. A wireless access point provides Wi-Fi for students' phones. This setup is a LAN. When a student in Kalomo visits a website hosted in the United States, the request travels from the LAN through the college's internet connection into the global WAN we call the internet.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Look around your home, school or workplace. List three devices that connect to a network.</li>
<li>Find the Wi-Fi router if you can see it. Count how many cables are plugged into it.</li>
<li>Draw a simple diagram showing a router, two computers and one phone connected wirelessly.</li>
<li>Ask someone how they connect to the internet at home: Wi-Fi, mobile data, or both?</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Network</strong> — two or more computers connected to share resources.</li>
<li><strong>LAN</strong> — Local Area Network; a network covering a small area.</li>
<li><strong>WAN</strong> — Wide Area Network; a network covering a large area.</li>
<li><strong>Router</strong> — a device that directs data between networks.</li>
<li><strong>Wi-Fi</strong> — a wireless technology that allows devices to connect to a network.</li>
</ul>

<h2>Summary</h2>
<p>Computer networks allow devices to share resources and communicate. A LAN covers a small area such as a college or office, while a WAN covers larger distances such as a country or the whole world. Wireless connections through Wi-Fi and mobile networks are common in Zambia, but wired connections still offer speed and reliability. Understanding networking helps you troubleshoot connection problems and make better use of shared resources.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://www.w3schools.com/computer/computer_networking.asp">W3Schools — Computer Networking</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/Common_questions/How_does_the_Internet_work">MDN Web Docs — How Does the Internet Work?</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/Common_questions/How_does_the_Internet_work">MDN Web Docs — How Does the Internet Work?</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.2 The Internet, Web Browsers and Search Engines',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what the internet is, use a web browser to visit websites, use a search engine effectively, and identify reliable information online.</p>

<h2>What Is the Internet?</h2>
<p>The <strong>internet</strong> is a global network of millions of connected computers. It allows people to send messages, share files, watch videos, buy goods, pay bills and access information from almost anywhere in the world. The internet is not owned by any single person or company. It works because computers agree to use the same rules, called <strong>protocols</strong>, to communicate.</p>
<p>In Zambia the internet reaches people through fixed internet providers, mobile networks and satellite services. A student in Kalomo can use the internet to research a topic, email a teacher, or watch an educational video.</p>

<h2>Web Browsers</h2>
<p>A <strong>web browser</strong> is software used to view websites. Common browsers include <strong>Google Chrome</strong>, <strong>Mozilla Firefox</strong>, <strong>Microsoft Edge</strong> and <strong>Safari</strong>. A browser reads code from a website and displays it as text, images and videos on your screen.</p>
<p>When you type a website address such as <code>www.zra.org.zm</code> into the address bar, the browser contacts the correct server and loads the page. You can open many pages at once using tabs, and you can bookmark pages you want to visit again.</p>

<h2>Search Engines</h2>
<p>A <strong>search engine</strong> is a website that helps you find information on the internet. Google is the most popular search engine, but others include Bing, DuckDuckGo and Yahoo. When you type a question or keyword into a search engine, it returns a list of web pages it thinks are relevant.</p>
<p>To search effectively:</p>
<ul>
<li>Use specific keywords. "ZRA TPIN registration Zambia" is better than "tax".</li>
<li>Use quotation marks to search for an exact phrase.</li>
<li>Use the minus sign to exclude words. For example, "jaguar -car" finds the animal, not the vehicle.</li>
<li>Check the website address before clicking. Government sites usually end in .gov.zm, educational sites in .edu or .ac.zm, and organisations in .org.zm.</li>
</ul>

<h2>Finding Reliable Information</h2>
<p>Not everything on the internet is true. Before trusting a website, ask:</p>
<ul>
<li>Who wrote the information? Are they an expert?</li>
<li>When was the page last updated?</li>
<li>Does the site have a clear purpose, such as education, news or selling products?</li>
<li>Can the information be checked against another source?</li>
</ul>

<h2>Worked Example: Searching for TEVETA Information</h2>
<p>John wants to know how to verify that a college is registered with TEVETA. He opens Google Chrome and types "TEVETA Zambia registered colleges list" into the search box. He skips the advertisements and clicks a result from a .gov.zm or .org.zm website. He finds a list of accredited institutions and confirms that Edutrack Computer Training College appears on it.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open a web browser and visit the official ZRA website at www.zra.org.zm.</li>
<li>Use a search engine to find "TEVETA Zambia" and open the official TEVETA website.</li>
<li>Search for information about your district. Write down two facts from a reliable source.</li>
<li>Try searching with quotation marks around a phrase and notice how the results change.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Internet</strong> — a global network of connected computers.</li>
<li><strong>Web browser</strong> — software used to view websites.</li>
<li><strong>Search engine</strong> — a website that helps users find information on the internet.</li>
<li><strong>Website</strong> — a collection of related web pages on the internet.</li>
<li><strong>URL</strong> — Uniform Resource Locator; the address of a web page.</li>
</ul>

<h2>Summary</h2>
<p>The internet connects millions of computers worldwide. Web browsers let you view websites, and search engines help you find information quickly. Learning to search effectively and judge the reliability of websites helps you find accurate information for study, business and daily life in Zambia.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/Common_questions/How_does_the_Internet_work">MDN Web Docs — How Does the Internet Work?</a></li>
<li><a href="https://support.google.com/websearch/answer/134479">Google Search Help</a></li>
<li><a href="https://www.w3schools.com/computer/">W3Schools — Computer Basics</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.3 Email and Online Communication',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create and use an email account, write clear and polite emails, and describe other online communication tools commonly used in Zambia.</p>

<h2>What Is Email?</h2>
<p><strong>Email</strong>, short for electronic mail, is a way of sending messages over the internet. An email can include text, attachments such as documents or photos, and links to websites. Email addresses have the format <code>name@example.com</code> or <code>name@example.org.zm</code>.</p>
<p>Email is widely used for job applications, business communication, school assignments, and contacting government offices. Unlike phone calls, email leaves a written record that you can refer to later. It is also cheaper than international phone calls.</p>

<h2>Creating an Email Account</h2>
<p>Popular free email services include Gmail, Outlook and Yahoo Mail. To create an account you usually need:</p>
<ul>
<li>A name and preferred email address.</li>
<li>A strong password.</li>
<li>A phone number for security and recovery.</li>
<li>An agreement to the service's terms.</li>
</ul>
<p>When choosing an email address for job applications, use a professional name such as <code>john.banda@gmail.com</code> instead of a nickname. Employers may judge you by your email address.</p>

<h2>Writing Good Emails</h2>
<p>A clear email has several parts:</p>
<ul>
<li><strong>Subject line</strong> — a short summary of what the email is about.</li>
<li><strong>Greeting</strong> — such as "Dear Sir or Madam" or "Hello Mr Banda".</li>
<li><strong>Body</strong> — the main message, written in short paragraphs.</li>
<li><strong>Closing</strong> — such as "Yours faithfully" or "Kind regards".</li>
<li><strong>Signature</strong> — your full name and contact number.</li>
</ul>
<p>Keep emails polite and to the point. Use proper spelling and grammar. If you attach a file, mention it in the email body so the recipient knows to look for it.</p>

<h2>Other Online Communication Tools</h2>
<p>Many Zambians also use:</p>
<ul>
<li><strong>WhatsApp</strong> — for text, voice and video messages, often used by families, businesses and community groups.</li>
<li><strong>Facebook</strong> — for social networking, advertising and community pages.</li>
<li><strong>Telegram</strong> — for messaging and sharing large files.</li>
<li><strong>Zoom, Google Meet and Microsoft Teams</strong> — for video meetings and online classes.</li>
</ul>

<h2>Worked Example: Emailing a Job Application</h2>
<p>Mary sees a job advertisement for a receptionist. She writes an email with the subject "Application for Receptionist Position." She greets the employer politely, explains that she is applying for the position, mentions her certificate, and attaches her CV as a PDF. She ends with "Yours faithfully, Mary Tembo" and her phone number. The employer can reply directly to her email or call her.</p>

<h2>Try It Yourself</h2>
<ol>
<li>If you do not already have one, create a free Gmail account.</li>
<li>Write an email to a friend or classmate with a clear subject line and polite greeting.</li>
<li>Practise attaching a document to the email, such as a CV or a class note.</li>
<li>Write a list of three situations where email is better than WhatsApp, and three where WhatsApp is better.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Email</strong> — electronic messages sent over the internet.</li>
<li><strong>Attachment</strong> — a file such as a document or photo sent with an email.</li>
<li><strong>Subject line</strong> — the short title of an email that tells the recipient what it is about.</li>
<li><strong>CC</strong> — carbon copy; sends a copy of the email to additional recipients.</li>
<li><strong>Video call</strong> — a live conversation over the internet using a camera and microphone.</li>
</ul>

<h2>Summary</h2>
<p>Email is an essential tool for formal communication in education, business and government. A well-written email is clear, polite and properly formatted. Alongside email, tools such as WhatsApp, Zoom and social media help Zambians stay connected with family, customers and colleagues.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/mail/answer/8494">Gmail Help — Create a Gmail Account</a></li>
<li><a href="https://support.google.com/mail/answer/8494">Gmail Help — Create a Gmail Account</a></li>
<li><a href="https://www.w3schools.com/">W3Schools</a></li>
<li><a href="https://support.google.com/meet/answer/9302870">Google Meet Help</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.4 ICT in Zambian Society',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe how Information and Communication Technology is used in Zambian government, banking, agriculture, education and health, and discuss both the benefits and the challenges of ICT in Zambia.</p>

<h2>What Is ICT?</h2>
<p><strong>ICT</strong> stands for Information and Communication Technology. It includes computers, the internet, mobile phones, software and the services they make possible. ICT is changing how Zambians work, learn, trade and interact with government.</p>

<h2>ICT in Government</h2>
<p>The Zambian government uses ICT to provide services to citizens. This is often called <strong>e-government</strong>. Examples include:</p>
<ul>
<li>The ZRA online portal where taxpayers register for a TPIN and file returns.</li>
<li>The National Registration Office systems for issuing National Registration Cards.</li>
<li>Online portals for checking examination results and applying for some licences.</li>
</ul>
<p>E-government can reduce paperwork, shorten waiting times, and make services available to people outside major towns. However, it also requires reliable internet and digital skills.</p>

<h2>ICT in Banking and Mobile Money</h2>
<p>Mobile money services such as <strong>Airtel Money</strong> and <strong>MTN MoMo</strong> have transformed banking in Zambia. People who live far from a bank branch can send money, pay bills, buy airtime and receive payments using a mobile phone. Small businesses use mobile money to accept payments from customers quickly and safely.</p>
<p>Traditional banks also offer internet banking and mobile apps. Customers can check balances, transfer money and pay school fees without visiting a branch.</p>

<h2>ICT in Agriculture</h2>
<p>Farmers in Zambia use ICT in many ways:</p>
<ul>
<li>Mobile apps and SMS services provide weather forecasts and farming advice.</li>
<li>Farmers check market prices online before selling crops.</li>
<li>WhatsApp groups help farmers share information about pests, buyers and transport.</li>
<li>Mobile money allows farmers to receive payments and buy inputs without travelling long distances.</li>
</ul>

<h2>ICT in Education and Health</h2>
<p>In education, ICT provides online courses, digital libraries, video lessons and tools such as Google Classroom. In health, ICT helps hospitals keep patient records, reminds patients of appointments, and allows telemedicine consultations in remote areas.</p>

<h2>Benefits and Challenges</h2>
<p>Benefits of ICT include faster communication, easier access to information, new business opportunities and better government services. Challenges include the cost of devices and data, unreliable electricity, limited internet coverage in rural areas, cybercrime, and the need for training.</p>

<h2>Worked Example: A Farmer Using ICT</h2>
<p>Mr Phiri grows maize near Kalomo. Every morning he checks the weather forecast on his phone. He belongs to a WhatsApp group where farmers share maize prices from different buyers. When he sells his crop, the buyer pays him through MTN MoMo. He uses part of the money to buy ZESCO tokens through mobile money, avoiding a long trip to town. ICT saves him time and helps him make better decisions.</p>

<h2>Try It Yourself</h2>
<ol>
<li>List three ways ICT has changed daily life in your community.</li>
<li>Find one government service that can be accessed online. Write down the website and what the service does.</li>
<li>Interview a small business owner. Ask how they use phones, mobile money or the internet for their business.</li>
<li>Write two paragraphs: one about the benefits of ICT in Zambia and one about the challenges.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>ICT</strong> — Information and Communication Technology.</li>
<li><strong>E-government</strong> — using digital technology to deliver government services.</li>
<li><strong>Mobile money</strong> — financial services operated from a mobile phone.</li>
<li><strong>Telemedicine</strong> — providing medical advice or services over a distance using technology.</li>
<li><strong>Cybercrime</strong> — criminal activity carried out using computers or the internet.</li>
</ul>

<h2>Summary</h2>
<p>ICT touches almost every part of Zambian society, from paying taxes and receiving wages to selling crops and studying online. While ICT brings many benefits, challenges such as cost, electricity and skills must be addressed. Understanding how ICT is used in Zambia prepares you to take part in the country's digital future.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://grow.google/">Grow with Google — Free Training</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/Common_questions/How_does_the_Internet_work">MDN Web Docs — How Does the Internet Work?</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module5Quiz(): array
    {
        return [
            'title' => 'Module 5 Quiz: Networks, Internet and ICT in Zambia',
            'description' => 'Test your understanding of computer networks, the internet, email and the role of ICT in Zambian society.',
            'time_limit_minutes' => 25,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which term describes a network that covers a small area such as a school or office?',
                    'explanation' => 'A LAN, or Local Area Network, covers a limited area such as a building.',
                    'options' => [
                        ['text' => 'WAN', 'is_correct' => false],
                        ['text' => 'LAN', 'is_correct' => true],
                        ['text' => 'WWW', 'is_correct' => false],
                        ['text' => 'HTML', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'The internet is a global network of connected computers.',
                    'explanation' => 'The internet connects millions of computers worldwide.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which device directs data between computers on a network and connects to the internet?',
                    'explanation' => 'A router directs data between networks and usually connects a local network to the internet.',
                    'options' => [
                        ['text' => 'Monitor', 'is_correct' => false],
                        ['text' => 'Keyboard', 'is_correct' => false],
                        ['text' => 'Router', 'is_correct' => true],
                        ['text' => 'Printer', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is a web browser used for?',
                    'explanation' => 'A web browser is software used to view websites on the internet.',
                    'options' => [
                        ['text' => 'Writing documents', 'is_correct' => false],
                        ['text' => 'Viewing websites', 'is_correct' => true],
                        ['text' => 'Creating databases', 'is_correct' => false],
                        ['text' => 'Editing photos', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'WhatsApp can be used for both personal and business communication in Zambia.',
                    'explanation' => 'WhatsApp is widely used in Zambia for messaging, business advertising and community groups.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does ICT stand for?',
                    'explanation' => 'ICT stands for Information and Communication Technology.',
                    'options' => [
                        ['text' => 'Internet and Computer Training', 'is_correct' => false],
                        ['text' => 'Information and Communication Technology', 'is_correct' => true],
                        ['text' => 'International Computer Test', 'is_correct' => false],
                        ['text' => 'Internal Control Technology', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'Name one mobile money service used in Zambia. (one word)',
                    'explanation' => 'Airtel Money and MTN MoMo are the two main mobile money services in Zambia.',
                    'correct_answer' => 'Airtel Money',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is an example of e-government?',
                    'explanation' => 'Filing tax returns through the ZRA online portal is an example of e-government.',
                    'options' => [
                        ['text' => 'Sending a WhatsApp message to a friend', 'is_correct' => false],
                        ['text' => 'Filing tax returns on the ZRA website', 'is_correct' => true],
                        ['text' => 'Watching a video on YouTube', 'is_correct' => false],
                        ['text' => 'Playing a game on a phone', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of these is a challenge of ICT in Zambia?',
                    'explanation' => 'Unreliable electricity is one of the main challenges facing ICT use in Zambia.',
                    'options' => [
                        ['text' => 'Too many computers in every home', 'is_correct' => false],
                        ['text' => 'Unreliable electricity', 'is_correct' => true],
                        ['text' => 'No mobile phones', 'is_correct' => false],
                        ['text' => 'Free internet everywhere', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What does the @ symbol separate in an email address? (two words: local part and domain)',
                    'explanation' => 'An email address has a local part before @ and a domain after @, such as name@example.com.',
                    'correct_answer' => 'local part and domain',
                ],
            ],
        ];
    }


    private function module6Lessons(): array
    {
        return [
            [
                'title' => '6.1 Computer Ethics, Copyright and Cybercrime',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what computer ethics means, describe copyright and why it matters, and identify common types of cybercrime and how to avoid them.</p>

<h2>What Are Computer Ethics?</h2>
<p><strong>Computer ethics</strong> are the rules of right and wrong behaviour when using computers and the internet. Ethics guide us to use technology in ways that respect other people, protect property, and follow the law. Even if something is possible with a computer, it may not be right.</p>
<p>For example, it is possible to copy a friend's paid software without paying, but it is unethical and illegal. It is possible to spread rumours about someone on social media, but it can harm their reputation and may break the law. Good computer ethics help us use technology responsibly.</p>

<h2>Copyright and Software Piracy</h2>
<p><strong>Copyright</strong> is the legal protection given to creators of original work such as books, music, films and software. When you buy a book or a song, you are usually buying the right to use it, not the right to copy and sell it.</p>
<p><strong>Software piracy</strong> means copying, distributing or using software without permission. Using pirated software is illegal in Zambia and can also be dangerous because pirated programs often contain viruses. Instead of pirating, you can use free and legal alternatives such as LibreOffice, GIMP and Linux.</p>

<h2>Common Types of Cybercrime</h2>
<p><strong>Cybercrime</strong> is crime committed using computers or the internet. Common examples include:</p>
<ul>
<li><strong>Phishing</strong> — fake emails or messages that trick you into giving passwords or PINs.</li>
<li><strong>Identity theft</strong> — stealing someone's personal information to commit fraud.</li>
<li><strong>Online fraud</strong> — scams such as fake prizes, fake jobs or fake products.</li>
<li><strong>Cyberbullying</strong> — using the internet to harass, threaten or embarrass someone.</li>
<li><strong>Hacking</strong> — breaking into computer systems without permission.</li>
</ul>

<h2>How to Stay Ethical and Safe</h2>
<ul>
<li>Use legal software and respect copyright.</li>
<li>Do not share other people's private information without permission.</li>
<li>Think before posting; once something is online, it is hard to remove.</li>
<li>Do not click links or open attachments from unknown senders.</li>
<li>Report cyberbullying and online scams to the authorities or platform administrators.</li>
</ul>

<h2>Worked Example: A Phishing Message</h2>
<p>Mrs Mwamba receives an SMS saying "Your Airtel Money account has been blocked. Click this link to verify your PIN." The message looks urgent. However, Airtel Money will never ask for her PIN by message. She deletes the SMS and reports it. By recognising the phishing attempt, she protects her money.</p>

<h2>Try It Yourself</h2>
<ol>
<li>List three examples of ethical behaviour when using computers.</li>
<li>Find one free legal alternative to Microsoft Office and write down its name and website.</li>
<li>Ask a friend to describe a scam message they have received. Write down how you would identify it as fake.</li>
<li>Create a poster with three tips for avoiding cybercrime.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Computer ethics</strong> — moral guidelines for using computers and the internet responsibly.</li>
<li><strong>Copyright</strong> — legal protection for original creative work.</li>
<li><strong>Software piracy</strong> — copying or using software without permission.</li>
<li><strong>Phishing</strong> — a scam that tries to steal personal information by pretending to be a trusted organisation.</li>
<li><strong>Cybercrime</strong> — illegal activity carried out using computers or the internet.</li>
</ul>

<h2>Summary</h2>
<p>Computer ethics guide us to use technology responsibly and legally. Respecting copyright, avoiding software piracy, and protecting ourselves from cybercrime are essential skills for every computer user. By thinking before we click, post or copy, we build a safer and fairer digital community.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/computer/computer_security.asp">W3Schools — Computer Security</a></li>
<li><a href="https://owasp.org/www-community/attacks/Phishing">OWASP — Phishing</a></li>
<li><a href="https://www.libreoffice.org/discover/libreoffice/">LibreOffice — Free Office Suite</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '6.2 Health, Safety and Ergonomics',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe common health risks of computer use, set up a safe and comfortable workstation, and follow safety procedures to protect yourself and your equipment.</p>

<h2>Health Risks of Computer Use</h2>
<p>Using computers for long periods can cause health problems if you are not careful. Common issues include:</p>
<ul>
<li><strong>Eye strain</strong> — tired, dry or sore eyes from looking at a screen too long.</li>
<li><strong>Back and neck pain</strong> — caused by poor posture or an uncomfortable chair.</li>
<li><strong>Repetitive strain injury</strong> — pain in the wrists or hands from typing or using a mouse too much.</li>
<li><strong>Headaches</strong> — often caused by screen glare, poor lighting or long hours without breaks.</li>
</ul>

<h2>Ergonomics: Working Comfortably</h2>
<p><strong>Ergonomics</strong> is the science of designing a workspace to fit the person using it. A good workstation reduces strain and helps you work longer without discomfort. Follow these tips:</p>
<ul>
<li>Sit with your back straight and supported by the chair.</li>
<li>Keep your feet flat on the floor.</li>
<li>Position the monitor at eye level, about an arm's length away.</li>
<li>Keep the keyboard and mouse close enough that your elbows stay at your sides.</li>
<li>Use a wrist rest if available, and keep wrists straight while typing.</li>
</ul>

<h2>Taking Breaks</h2>
<p>Your eyes and muscles need rest. Follow the <strong>20-20-20 rule</strong>: every 20 minutes, look at something 20 feet away for 20 seconds. Stand up, stretch and walk around for a few minutes every hour. This helps prevent eye strain and muscle stiffness.</p>

<h2>Safety Around Equipment</h2>
<p>Computers and electricity can be dangerous if not handled properly. Follow these safety rules:</p>
<ul>
<li>Do not touch plugs or switches with wet hands.</li>
<li>Keep drinks away from keyboards and laptops.</li>
<li>Do not overload power sockets with too many devices.</li>
<li>Turn off and unplug equipment before cleaning it.</li>
<li>Use a surge protector to protect against power spikes.</li>
</ul>

<h2>Worked Example: Setting Up a Safe Home Study Area</h2>
<p>Grace studies computer studies at home using a laptop. She places the laptop on a table, not on her lap, so the screen is at eye level. She uses a chair with back support and keeps her feet on the floor. She takes a five-minute break every hour and keeps her water bottle away from the laptop. During load-shedding she saves her work often and shuts down the laptop properly when the battery is low.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Sit at a computer and check your posture. Are your back straight, feet flat and screen at eye level?</li>
<li>Set a timer for 20 minutes and practise the 20-20-20 rule.</li>
<li>Check your study area for hazards such as overloaded sockets, drinks near the computer, or trailing cables.</li>
<li>Write a short safety poster for a computer lab with five rules.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Ergonomics</strong> — designing a workspace to fit the user and reduce strain.</li>
<li><strong>Eye strain</strong> — discomfort in the eyes caused by long screen use.</li>
<li><strong>Repetitive strain injury</strong> — damage to muscles or tendons from repeated movements.</li>
<li><strong>20-20-20 rule</strong> — every 20 minutes, look at something 20 feet away for 20 seconds.</li>
<li><strong>Surge protector</strong> — a device that protects electronics from sudden voltage increases.</li>
</ul>

<h2>Summary</h2>
<p>Computers are safe when used correctly, but long hours of poor posture, screen glare and repetitive movements can cause health problems. Ergonomics, regular breaks and basic electrical safety protect both the user and the equipment. These habits are especially important in places with unstable power, such as during Zambian load-shedding.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/computer/computer_security.asp">W3Schools — Computer Security</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://learn.microsoft.com/en-us/windows/">Microsoft Learn — Windows</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '6.3 Programming Concepts: Algorithms, Pseudocode and Scratch',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what an algorithm is, write simple pseudocode, understand basic programming concepts such as sequence, selection and repetition, and create a simple program in Scratch.</p>

<h2>What Is an Algorithm?</h2>
<p>An <strong>algorithm</strong> is a step-by-step set of instructions to solve a problem or complete a task. Algorithms are not only for computers; you follow algorithms every day. A recipe for cooking nshima is an algorithm. The instructions for buying ZESCO tokens through mobile money are an algorithm. A set of directions to a friend's house is also an algorithm.</p>
<p>A computer program is simply an algorithm written in a language the computer understands.</p>

<h2>Pseudocode</h2>
<p><strong>Pseudocode</strong> is a way of writing an algorithm in plain language, using some programming-style words. It is not a real programming language, but it helps programmers plan their code before writing it.</p>
<p>Here is pseudocode for calculating the total price of two items:</p>
<pre><code>START
    INPUT price1
    INPUT price2
    total = price1 + price2
    DISPLAY total
STOP
</code></pre>

<h2>Basic Programming Concepts</h2>
<p>Most programs use three basic structures:</p>
<ul>
<li><strong>Sequence</strong> — instructions carried out in order, one after another.</li>
<li><strong>Selection</strong> — making a decision, often using IF statements.</li>
<li><strong>Repetition</strong> — repeating instructions, often using loops.</li>
</ul>
<p>For example, a program that calculates a discount might use sequence to read the price, selection to check if the price is high enough for a discount, and repetition to process many items.</p>

<h2>Introduction to Scratch</h2>
<p><strong>Scratch</strong> is a free visual programming language created by MIT. Instead of typing code, you drag and drop colourful blocks that fit together like puzzle pieces. Scratch is excellent for beginners because you can see the results immediately and learn programming concepts without worrying about syntax errors.</p>
<p>With Scratch you can create animations, games, stories and interactive art. A student in Zambia could create a Scratch project that teaches younger children how to count in a local language, or a simple game about road safety.</p>

<h2>Worked Example: A Scratch Story</h2>
<p>Thandi wants to make a Scratch project about a chicken crossing the road. She drags a "when green flag clicked" block, then adds blocks to make the chicken move forward, say "Look left and right!", and stop at the other side. She tests the project, sees the chicken move, and adds a second character. She has learned sequence and simple instructions without writing any typed code.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write pseudocode for making a cup of tea. Number each step.</li>
<li>Visit the Scratch website at scratch.mit.edu and create a free account.</li>
<li>Create a simple project that moves a sprite across the screen and says a message.</li>
<li>Write pseudocode for a program that asks for two numbers and displays their sum.</li>
<li>Explain to a classmate the difference between sequence, selection and repetition.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Algorithm</strong> — a step-by-step set of instructions to solve a problem.</li>
<li><strong>Pseudocode</strong> — a simple way of writing an algorithm using plain language and programming-style words.</li>
<li><strong>Sequence</strong> — instructions carried out in order.</li>
<li><strong>Selection</strong> — choosing between different actions based on a condition.</li>
<li><strong>Repetition</strong> — repeating a set of instructions, also called a loop.</li>
</ul>

<h2>Summary</h2>
<p>Programming is about giving clear instructions to solve problems. Algorithms and pseudocode help you plan your thinking before you write real code. Scratch makes programming visual and fun, allowing beginners to explore sequence, selection and repetition. These ideas are the foundation of all programming, whether you later learn Python, Java or web development.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Computer Programming</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Computer Programming</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Learn to Code</a></li>
<li><a href="https://www.w3schools.com/python/">W3Schools — Python Tutorial</a></li>
</ul>
HTML,
            ],
            [
                'title' => '6.4 Career Paths and Continuing Your ICT Journey',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to identify ICT-related career paths available in Zambia, understand the value of lifelong learning, and create a simple personal plan for continuing your computer studies after this course.</p>

<h2>Why ICT Skills Matter for Careers</h2>
<p>ICT skills are no longer only for people who want to become programmers. Almost every modern job uses computers in some way. A receptionist uses email and appointment software. A farmer uses mobile apps for weather and market prices. A nurse uses digital patient records. A teacher uses computers to prepare lessons. Even a market vendor can benefit from knowing how to track sales and advertise on WhatsApp.</p>

<h2>ICT Career Paths</h2>
<p>Here are some careers that use ICT skills:</p>
<ul>
<li><strong>Data entry clerk</strong> — types information into computer systems accurately.</li>
<li><strong>IT support technician</strong> — helps people fix computer and network problems.</li>
<li><strong>Web developer</strong> — builds and maintains websites.</li>
<li><strong>Software developer</strong> — creates computer programs and mobile apps.</li>
<li><strong>Network administrator</strong> — manages computer networks in organisations.</li>
<li><strong>Digital marketer</strong> — promotes businesses online through social media and websites.</li>
<li><strong>Graphic designer</strong> — creates visual content using computer software.</li>
<li><strong>Database administrator</strong> — manages and protects organisational data.</li>
<li><strong>Cybersecurity analyst</strong> — protects systems from online threats.</li>
</ul>

<h2>Continuing Your Learning</h2>
<p>Technology changes quickly. The skills you learn today will need updating throughout your life. This is called <strong>lifelong learning</strong>. You can continue learning through:</p>
<ul>
<li>Further courses at Edutrack Computer Training College or TEVETA-accredited institutions.</li>
<li>Free online platforms such as Khan Academy, freeCodeCamp and W3Schools.</li>
<li>YouTube tutorials on specific software or programming languages.</li>
<li>Practising regularly at home, at college or in a community internet café.</li>
<li>Joining online communities and forums where learners help each other.</li>
</ul>

<h2>Building a Portfolio</h2>
<p>A <strong>portfolio</strong> is a collection of your best work. For ICT careers, a portfolio might include documents you have formatted, spreadsheets you have created, websites you have built, or screenshots of Scratch projects. A strong portfolio shows employers what you can do better than a certificate alone.</p>

<h2>Worked Example: Planning the Next Step</h2>
<p>Joseph finishes the Certificate in Computer Studies and enjoys working with spreadsheets and databases. He decides to enrol in a Database Management Systems course next. While studying, he volunteers to manage the membership list for his church group using LibreOffice Base. He saves screenshots and a report as portfolio pieces. After six months he applies for a data entry position at a local NGO and shows his portfolio during the interview.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose one ICT career that interests you. Write three sentences explaining why it appeals to you.</li>
<li>List three skills you would need for that career.</li>
<li>Find one free online course related to that skill and write down the website.</li>
<li>Create a folder on your computer named "My ICT Portfolio" and add at least two pieces of work from this course.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Lifelong learning</strong> — continuing to gain new knowledge and skills throughout your life.</li>
<li><strong>Portfolio</strong> — a collection of work that shows your skills and experience.</li>
<li><strong>ICT career</strong> — a job that uses information and communication technology.</li>
<li><strong>Network administrator</strong> — a person who manages computer networks.</li>
<li><strong>Database administrator</strong> — a person who manages and maintains databases.</li>
</ul>

<h2>Summary</h2>
<p>ICT skills open many career paths in Zambia and beyond. Whether you want to work in an office, start a business, farm, teach or develop software, computer skills will help you succeed. The key is to keep learning, practise regularly, and build a portfolio that proves your abilities. Your journey with technology does not end with this certificate; it is just beginning.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Free Coding Curriculum</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Computer Programming</a></li>
<li><a href="https://grow.google/">Grow with Google — Free Training</a></li>
<li><a href="https://grow.google/">Grow with Google — Free Training</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module6Quiz(): array
    {
        return [
            'title' => 'Module 6 Quiz: Ethics, Safety and Programming Concepts',
            'description' => 'Test your understanding of computer ethics, health and safety, algorithms, pseudocode and programming basics.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is software piracy?',
                    'explanation' => 'Software piracy is copying or using software without permission, which is illegal.',
                    'options' => [
                        ['text' => 'Swimming while using a computer', 'is_correct' => false],
                        ['text' => 'Copying software without permission', 'is_correct' => true],
                        ['text' => 'Buying software from a shop', 'is_correct' => false],
                        ['text' => 'Installing updates', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'It is safe to click a link in an SMS asking for your mobile money PIN.',
                    'explanation' => 'Legitimate providers never ask for your PIN by message. This is a phishing scam.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does the 20-20-20 rule help prevent?',
                    'explanation' => 'The 20-20-20 rule helps prevent eye strain during long screen use.',
                    'options' => [
                        ['text' => 'Hunger', 'is_correct' => false],
                        ['text' => 'Eye strain', 'is_correct' => true],
                        ['text' => 'Computer viruses', 'is_correct' => false],
                        ['text' =>'Slow internet', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is an algorithm?',
                    'explanation' => 'An algorithm is a step-by-step set of instructions to solve a problem.',
                    'options' => [
                        ['text' => 'A type of computer virus', 'is_correct' => false],
                        ['text' => 'A step-by-step set of instructions', 'is_correct' => true],
                        ['text' =>'A computer monitor', 'is_correct' => false],
                        ['text' => 'A storage device', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Pseudocode is a real programming language.',
                    'explanation' => 'Pseudocode is a planning tool written in plain language, not a real programming language.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a visual programming language for beginners?',
                    'explanation' => 'Scratch is a visual programming language that uses drag-and-drop blocks.',
                    'options' => [
                        ['text' => 'Microsoft Word', 'is_correct' => false],
                        ['text' => 'Scratch', 'is_correct' => true],
                        ['text' => 'Excel', 'is_correct' => false],
                        ['text' => 'PowerPoint', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What word describes the science of designing a comfortable and safe workspace? (one word)',
                    'explanation' => 'Ergonomics is the study of designing workspaces that fit the user.',
                    'correct_answer' => 'Ergonomics',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of these is an example of selection in programming?',
                    'explanation' => 'An IF statement chooses between different actions based on a condition.',
                    'options' => [
                        ['text' => 'A loop that repeats ten times', 'is_correct' => false],
                        ['text' => 'An IF statement that checks a condition', 'is_correct' => true],
                        ['text' => 'A list of instructions in order', 'is_correct' => false],
                        ['text' => 'Saving a file', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which career involves protecting computer systems from online threats?',
                    'explanation' => 'A cybersecurity analyst protects systems and networks from cyber threats.',
                    'options' => [
                        ['text' => 'Data entry clerk', 'is_correct' => false],
                        ['text' => 'Graphic designer', 'is_correct' => false],
                        ['text' => 'Cybersecurity analyst', 'is_correct' => true],
                        ['text' => 'Web developer', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What type of legal protection is given to creators of original work such as books, music and software? (one word)',
                    'explanation' => 'Copyright protects original creative work from being copied without permission.',
                    'correct_answer' => 'Copyright',
                ],
            ],
        ];
    }


    private function createAssignments(): void
    {
        Assignment::create([
            'course_id' => $this->courseId,
            'lesson_id' => null,
            'title' => 'Build Your Digital Office Toolkit',
            'description' => 'Create a set of practical documents that demonstrate your word processing, spreadsheet and file management skills for a small Zambian business.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Create a new folder on your computer named "Computer_Studies_Assignment_1".
Step 2: Inside the folder, create a subfolder called "Documents" and another called "Spreadsheets".
Step 3: Open a word processor and write a formal business letter from a fictional shop in Kalomo to a supplier in Lusaka requesting a price list for rice, sugar and cooking oil. Use a proper letter layout with your address, date, greeting, body, closing and signature.
Step 4: Save the letter as a PDF named "Supplier_Letter.pdf" in the Documents folder.
Step 5: Open a spreadsheet and create a stock list for a small grocery shop. Include columns for Item, Quantity, Unit Price (in Kwacha) and Total Value. Enter at least ten items and use a formula to calculate the Total Value and a SUM function for the grand total.
Step 6: Save the spreadsheet as "Shop_Stock.xlsx" or "Shop_Stock.ods" in the Spreadsheets folder.
Step 7: Compress the "Computer_Studies_Assignment_1" folder into a ZIP file and upload it here.
INSTRUCTIONS,
            'max_points' => 100,
            'passing_points' => 50,
            'due_date' => null,
            'allow_late_submission' => 1,
            'allowed_file_types' => 'pdf,doc,docx,xlsx,ods,zip',
            'max_file_size_mb' => 10,
        ]);

        Assignment::create([
            'course_id' => $this->courseId,
            'lesson_id' => null,
            'title' => 'ICT for My Community',
            'description' => 'Research and present how ICT can solve a real problem in your community, school or small business.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Choose one of these topics:
  (a) How a small market stall in Kalomo could use ICT to increase sales.
  (b) How a school could use ICT to improve communication with parents.
  (c) How a farmer group could use ICT to find better market prices.
Step 2: Open presentation software such as Microsoft PowerPoint, LibreOffice Impress or Google Slides.
Step 3: Create a presentation of at least five slides with a title slide, three content slides and a conclusion slide.
Step 4: Include at least two real examples of ICT tools or services used in Zambia, such as WhatsApp, Airtel Money, MTN MoMo, Google Sheets, email or government websites.
Step 5: Apply the 6x6 rule: no more than six bullet points and six words per point.
Step 6: Add one relevant image and one table or chart if possible.
Step 7: Save or export the presentation as a PDF named "ICT_for_My_Community.pdf" and upload it here.
INSTRUCTIONS,
            'max_points' => 100,
            'passing_points' => 50,
            'due_date' => null,
            'allow_late_submission' => 1,
            'allowed_file_types' => 'pdf,ppt,pptx,odp',
            'max_file_size_mb' => 10,
        ]);
    }

    private function printSummary(): void
    {
        $this->command->info('Certificate in Computer Studies content seeded successfully.');
        $this->command->info('Modules: 6 | Lessons: 24 | Quizzes: 6 | Assignments: 2');
    }
}
