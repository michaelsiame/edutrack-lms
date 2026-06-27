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

class TradeCertificateComputerStudiesLevel3ContentSeeder extends Seeder
{
    private int $courseId;

    private int $modulesCreated = 0;

    private int $lessonsCreated = 0;

    private int $quizzesCreated = 0;

    private int $questionsCreated = 0;

    public function run(): void
    {
        $course = Course::where('title', 'Trade Certificate in Computer Studies Level III')->first();

        if (! $course) {
            $this->command->error('Course "Trade Certificate in Computer Studies Level III" not found. Aborting.');

            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Trade Certificate in Computer Studies Level III already has modules. Skipping content seed.');

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

                // Module duration remains the official nominal hours * 60 set during creation.
                $module->save();

                $quizData = $this->{"module{$moduleNumber}Quiz"}();
                $quiz = Quiz::create([
                    'course_id' => $this->courseId,
                    'lesson_id' => end($lessonIds),
                    'title' => $quizData['title'],
                    'description' => $quizData['description'],
                    'quiz_type' => 'Graded',
                    'time_limit_minutes' => $quizData['time_limit_minutes'] ?? 20,
                    'max_attempts' => 3,
                    'passing_score' => 50.00,
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

            // Assignments
            $this->createAssignments();
        });

        $this->printSummary();
    }

    private function createModules(): array
    {
        $moduleData = [
            [
                'title' => 'A1.1 Using Computer Gadgets & Media in Everyday Life',
                'description' => 'Identify computer hardware and peripherals, understand the input-process-output cycle, and explore industrial, commercial and social applications of computers in Zambia.',
                'duration_minutes' => 1200,
            ],
            [
                'title' => 'A1.2 Carrying Out Basic Computer Operations',
                'description' => 'Practise health and safety, care for computer systems, perform basic operations, respect software copyright, run security checks, and compare storage capacities.',
                'duration_minutes' => 600,
            ],
            [
                'title' => 'A1.3 Using Word Processing Package Effectively',
                'description' => 'Create, format, edit, search, replace, paginate and print documents using a word processor such as Microsoft Word or LibreOffice Writer.',
                'duration_minutes' => 1200,
            ],
            [
                'title' => 'A1.4 Using Spreadsheet Package Effectively',
                'description' => 'Enter, format and edit spreadsheet data, write simple and advanced formulas, sort records, create charts, and print worksheets for Zambian business examples.',
                'duration_minutes' => 1200,
            ],
            [
                'title' => 'A1.5 Using PowerPoint Package Effectively',
                'description' => 'Design, edit and deliver presentations using slides, objects, animation, transitions and handouts in presentation software.',
                'duration_minutes' => 1200,
            ],
            [
                'title' => 'A1.6 Using Desktop Publishing Packages Effectively',
                'description' => 'Produce professional publications such as flyers, brochures, notices and newsletters using desktop publishing tools, templates, graphics and typography.',
                'duration_minutes' => 1200,
            ],
            [
                'title' => 'A1.7 Using Networks & the Internet',
                'description' => 'Understand wired and wireless networks, describe the benefits of networking and the internet, and use web browsers and email effectively.',
                'duration_minutes' => 1200,
            ],
            [
                'title' => 'A1.8 Using Multimedia Files',
                'description' => 'Create multimedia presentations, design simple graphics, record audio and video, and digitise images using scanners and cameras.',
                'duration_minutes' => 1200,
            ],
        ];

        $modules = [];
        foreach ($moduleData as $index => $data) {
            $modules[] = Module::create([
                'course_id' => $this->courseId,
                'title' => $data['title'],
                'description' => $data['description'],
                'display_order' => $index + 1,
                'duration_minutes' => $data['duration_minutes'],
                'is_published' => 1,
            ]);
            $this->modulesCreated++;
        }

        return $modules;
    }

    private function createAssignments(): void
    {
        Assignment::create([
            'course_id' => $this->courseId,
            'lesson_id' => null,
            'title' => 'Build a TEVETA Computer Studies Portfolio',
            'description' => 'Create a portfolio of practical documents that demonstrate your word processing, spreadsheet, presentation, desktop publishing and file management skills for a Zambian business or community organisation.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Create a new folder on your computer named "TEVETA_Level3_Portfolio".
Step 2: Inside the folder, create subfolders called "Word_Documents", "Spreadsheets", "Presentations" and "DTP".
Step 3: Open a word processor and type a formal business letter from a small shop in Kalomo to a supplier in Lusaka requesting prices for maize meal, cooking oil and sugar. Include your address, date, greeting, body, closing and signature. Save it as a PDF named "Supplier_Letter.pdf" in the Word_Documents folder.
Step 4: Create a spreadsheet that tracks one week of sales for a tuck shop. Include columns for Date, Item, Quantity Sold, Unit Price (ZMW) and Total Sales. Use formulas to calculate totals and a SUM function for the weekly total. Save it as "Tuckshop_Sales.xlsx" or "Tuckshop_Sales.ods" in the Spreadsheets folder.
Step 5: Create a presentation of at least five slides about the importance of computer skills for small businesses in Zambia. Include a title slide, three content slides and a conclusion. Save or export it as "ICT_for_Business.pdf" in the Presentations folder.
Step 6: Design a one-page flyer in a desktop publishing or word processing program advertising a community clean-up day in your area. Include a heading, date, time, venue and contact number. Save it as "Community_Cleanup_Flyer.pdf" in the DTP folder.
Step 7: Compress the "TEVETA_Level3_Portfolio" folder into a ZIP file and upload it here.
INSTRUCTIONS,
            'max_points' => 100,
            'passing_points' => 50,
            'due_date' => null,
            'allow_late_submission' => 1,
            'allowed_file_types' => 'pdf,doc,docx,xlsx,ods,ppt,pptx,odp,zip',
            'max_file_size_mb' => 15,
        ]);

        Assignment::create([
            'course_id' => $this->courseId,
            'lesson_id' => null,
            'title' => 'ICT, Networks and Multimedia in My Community',
            'description' => 'Research and present how computer networks, the internet, email and multimedia can solve a real problem in your community, school or small business.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Choose one of these topics:
  (a) How a school in your area could use computer networks and email to improve communication with parents and teachers.
  (b) How a small business could use the internet and mobile money to increase sales.
  (c) How a health clinic could use multimedia to educate the community about disease prevention.
Step 2: Research your topic using at least one reliable Zambian source, such as a government website, newspaper or educational institution.
Step 3: Create a multimedia presentation of at least five slides. Include text, at least one image, and either an audio clip, a video clip or a chart.
Step 4: Explain how computer networks, the internet, email or multimedia help solve the problem.
Step 5: Save or export the presentation as a PDF named "ICT_Networks_Multimedia_Community.pdf" and upload it here.
INSTRUCTIONS,
            'max_points' => 100,
            'passing_points' => 50,
            'due_date' => null,
            'allow_late_submission' => 1,
            'allowed_file_types' => 'pdf,ppt,pptx,odp',
            'max_file_size_mb' => 15,
        ]);
    }

    private function printSummary(): void
    {
        $this->command->info('================================================');
        $this->command->info('Trade Certificate in Computer Studies Level III');
        $this->command->info('Content seeding completed successfully.');
        $this->command->info('================================================');
        $this->command->info("Modules created: {$this->modulesCreated}");
        $this->command->info("Lessons created: {$this->lessonsCreated}");
        $this->command->info("Quizzes created: {$this->quizzesCreated}");
        $this->command->info("Questions created: {$this->questionsCreated}");
        $this->command->info('Assignments created: 2');

        $course = Course::find($this->courseId);
        $this->command->info('');
        $this->command->info('Module titles and lesson counts:');
        foreach ($course->modules()->orderBy('display_order')->get() as $module) {
            $count = $module->lessons()->count();
            $this->command->info("- {$module->title}: {$count} lessons");
        }
        $totalLessons = $course->lessons()->count();
        $totalQuizzes = $course->quizzes()->count();
        $totalQuestions = $course->quizzes()->withCount('questions')->get()->sum('questions_count');
        $this->command->info('');
        $this->command->info("Total lessons in course: {$totalLessons}");
        $this->command->info("Total quizzes in course: {$totalQuizzes}");
        $this->command->info("Total questions in course: {$totalQuestions}");
    }

    // ------------------------------------------------------------------
    // Module 1: A1.1 Using Computer Gadgets & Media in Everyday Life
    // ------------------------------------------------------------------

    private function module1Lessons(): array
    {
        return [
            [
                'title' => 'A1.1.1 Parts of a Computer and the Input-Process-Output Cycle',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to name the main parts of a computer system, classify hardware as input, processing, output or storage, and explain the input-process-output cycle using examples from everyday Zambian life.</p>

<h2>What Is a Computer System?</h2>
<p>A computer system is made up of hardware and software working together. <strong>Hardware</strong> is anything you can touch, such as the screen, keyboard, mouse and system unit. <strong>Software</strong> is the set of instructions that tells the hardware what to do. A computer is useful because it can accept data, process it quickly and accurately, and produce information that helps people make decisions.</p>

<h2>The Four Main Hardware Groups</h2>
<p>Hardware devices are usually grouped into four categories. Understanding these groups helps you choose, connect and troubleshoot equipment.</p>
<ul>
<li><strong>Input devices</strong> feed data and instructions into the computer. Examples include the keyboard, mouse, microphone, scanner, webcam, barcode reader and digital camera.</li>
<li><strong>Processing devices</strong> carry out calculations and control the system. The Central Processing Unit (CPU) and Random Access Memory (RAM) are the main processing components.</li>
<li><strong>Output devices</strong> show or produce results. Examples include the monitor, printer, speakers and projector.</li>
<li><strong>Storage devices</strong> keep data safe when the power is switched off. Examples include the hard disk drive, solid-state drive, USB flash drive and memory card.</li>
</ul>

<h2>The Input-Process-Output Cycle</h2>
<p>Every useful computer task follows a simple pattern. First, the user provides <strong>input</strong>. Then the computer <strong>processes</strong> the input according to a program. Finally, the computer produces <strong>output</strong>. This is called the input-process-output cycle, or IPO.</p>
<p>Imagine a student at Edutrack Computer Training College who wants to check her marks. She types her student number into the keyboard, which is input. The computer searches the student database and calculates her average, which is processing. The monitor then displays her report, which is output. Without any one of these steps, the task cannot be completed.</p>

<h2>Worked Example: Buying a Bus Ticket in Lusaka</h2>
<p>When a conductor uses a computerised ticketing system at a Lusaka bus station, the IPO cycle works like this:</p>
<ol>
<li><strong>Input:</strong> the conductor selects the destination and fare on a touchscreen or keyboard.</li>
<li><strong>Process:</strong> the system calculates the total price, deducts any discounts and records the sale.</li>
<li><strong>Output:</strong> a thermal printer produces the ticket and a small monitor shows the balance.</li>
</ol>
<p>The same cycle happens in a supermarket till, a mobile money agent's phone and an ATM.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Look at the computer in front of you. List every piece of hardware you can see and classify each as input, processing, output or storage.</li>
<li>Think about using an ATM to withdraw cash. Write down the input, process and output steps.</li>
<li>Draw a simple diagram showing the IPO cycle with arrows pointing from Input to Process to Output.</li>
<li>Explain to a classmate why a touchscreen is both an input and an output device.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Hardware</strong> — the physical parts of a computer that you can touch.</li>
<li><strong>Software</strong> — the programs and instructions that control the hardware.</li>
<li><strong>Input device</strong> — equipment used to send data into a computer.</li>
<li><strong>Output device</strong> — equipment that displays or prints information.</li>
<li><strong>CPU</strong> — Central Processing Unit; the main chip that processes instructions.</li>
<li><strong>RAM</strong> — temporary memory used while the computer is running.</li>
</ul>

<h2>Summary</h2>
<p>A computer system consists of input, processing, output and storage hardware directed by software. The input-process-output cycle explains how a computer turns raw data into useful information. Recognising these parts and processes is the first step toward confident computer use at college, in the office or in business.</p>
HTML,
            ],
            [
                'title' => 'A1.1.2 Peripherals and Ports: Connecting Devices Correctly',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to identify common peripheral devices, recognise the correct ports for connecting them, and follow safe procedures when attaching or removing equipment such as scanners, printers, fax machines and digital cameras.</p>

<h2>What Are Peripherals?</h2>
<p>A <strong>peripheral device</strong> is any piece of hardware that connects to the computer but is not part of the main system unit. Peripherals extend what the computer can do. A basic desktop computer can work with only a keyboard, mouse and monitor, but adding a printer, scanner, webcam or external hard drive makes it much more useful.</p>
<p>In a Zambian government office or a college computer lab, you will often find several peripherals attached to one computer. Knowing which cable goes into which port prevents damage and saves time when setting up new equipment.</p>

<h2>Common Ports and Connectors</h2>
<table>
<thead>
<tr><th>Port</th><th>Shape</th><th>Typical Use</th></tr>
</thead>
<tbody>
<tr><td>USB Type-A</td><td>Rectangular</td><td>Keyboard, mouse, flash drive, printer</td></tr>
<tr><td>USB Type-C</td><td>Oval, reversible</td><td>Modern phones, laptops, some printers</td></tr>
<tr><td>HDMI</td><td>Trapezoid</td><td>Monitor, projector, TV</td></tr>
<tr><td>VGA</td><td>D-shaped with pins</td><td>Older monitor or projector</td></tr>
<tr><td>Ethernet (RJ-45)</td><td>Wide phone-style</td><td>Wired network or internet cable</td></tr>
<tr><td>Audio jack</td><td>Round</td><td>Headphones, microphone, speakers</td></tr>
<tr><td>Power socket</td><td>Varies</td><td>Connects the computer to mains electricity</td></tr>
</tbody>
</table>

<h2>Connecting a Scanner, Printer and Digital Camera</h2>
<p>When you buy a new peripheral, the box usually contains a cable and a small CD or a link to download a <strong>driver</strong>. A driver is software that lets the operating system communicate with the device. Modern versions of Windows often install drivers automatically when you plug in a USB device.</p>
<p>To connect a scanner or printer, first plug the USB cable into the device and then into a free USB port on the computer. Turn the device on. Windows should detect it and install the driver. If the device does not work, check that the cable is firmly pushed in, that the power is on, and that the correct driver is installed.</p>
<p>A digital camera usually connects with a USB cable or by inserting its memory card into a card reader. Once connected, the computer treats the camera like a storage device and you can copy photos into a folder.</p>

<h2>Worked Example: Setting Up a Small Office in Kalomo</h2>
<p>Mrs Mutale opens a small stationery shop and buys a desktop computer, a flatbed scanner, a laser printer and a telephone with fax. She sets up the equipment as follows:</p>
<ol>
<li>She places the monitor, keyboard and mouse on the desk and connects them to the system unit.</li>
<li>She plugs the printer into a USB port at the back of the system unit and connects it to power.</li>
<li>She connects the scanner to another USB port and installs the driver from the manufacturer's website.</li>
<li>She plugs the telephone line into the fax machine and then connects the fax to the computer if it supports PC faxing.</li>
<li>She labels each cable so she can reconnect equipment quickly after cleaning.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Examine the back and sides of a computer. Identify at least three different ports.</li>
<li>Connect a USB flash drive to the computer and safely eject it using the system tray icon.</li>
<li>Look at a printer or scanner in the lab. Identify the power cable and data cable.</li>
<li>Write a checklist for safely connecting a new peripheral to a computer.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Peripheral</strong> — a device connected to a computer to add functionality.</li>
<li><strong>Port</strong> — a socket on a computer where a cable plugs in.</li>
<li><strong>USB</strong> — Universal Serial Bus; a common connector for peripherals.</li>
<li><strong>Driver</strong> — software that allows the operating system to use a hardware device.</li>
<li><strong>Safe ejection</strong> — using the operating system to prepare a removable device before unplugging it.</li>
</ul>

<h2>Summary</h2>
<p>Peripherals extend the capabilities of a computer. Connecting them to the correct ports and installing the right drivers ensures that scanners, printers, cameras and other devices work reliably. Safe connection habits protect both the equipment and the data you are working with.</p>
HTML,
            ],
            [
                'title' => 'A1.1.3 Industrial and Commercial Applications of Computers',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe how computers are used in industry and commerce, give Zambian examples of e-commerce, e-government, real-time booking, computer-aided design and diagnostic systems, and explain how these applications improve speed, accuracy and customer service.</p>

<h2>Computers in Industry and Commerce</h2>
<p>Computers are no longer found only in offices. They control machines in factories, guide engineers who design buildings, help doctors diagnose illness, and allow customers to buy goods without visiting a shop. These specialised uses are called <strong>applications</strong>. Each application takes advantage of the computer's speed, accuracy and ability to store large amounts of information.</p>

<h2>Real-Time Booking Systems</h2>
<p>A real-time system updates information immediately as events happen. When you book a bus ticket online, the system shows only the seats that are still available because it updates the seat map the moment another customer buys a ticket. Airlines, coach companies and hotels all use real-time booking. In Zambia, real-time booking is growing for intercity buses and event tickets, although many customers still prefer to pay at an agent or use mobile money.</p>

<h2>Computer-Aided Design</h2>
<p><strong>Computer-Aided Design (CAD)</strong> software helps architects and engineers create precise drawings. Instead of drawing a house plan by hand, an architect uses CAD to draw walls, doors, windows and electrical fittings on screen. Changes are made instantly, and the software can produce three-dimensional views. In Zambia, CAD is used by construction firms, mining companies and technical colleges that teach drafting.</p>

<h2>E-Commerce and E-Government</h2>
<p><strong>E-commerce</strong> means buying and selling goods and services over the internet. A Zambian farmer who sells groundnuts through a Facebook page is doing e-commerce. A shop in Lusaka that accepts orders on WhatsApp and payments by Airtel Money is also doing e-commerce. E-commerce reduces rent costs, reaches more customers and allows businesses to operate outside normal hours.</p>
<p><strong>E-government</strong> means providing government services online. The Zambia Revenue Authority (ZRA) allows taxpayers to register for a TPIN, file returns and pay taxes through its online portal. The National Registration office and some council services also use computer systems to issue NRCs, licences and certificates.</p>

<h2>Car Wheel Alignment and Diagnostics</h2>
<p>Modern garages use computerised wheel alignment machines. Sensors attached to the wheels send measurements to a computer, which compares them to the manufacturer's specifications and tells the mechanic exactly how to adjust each wheel. Diagnostic tools plug into a car's computer port and read error codes, saving hours of manual checking. These tools are becoming common in larger towns such as Lusaka, Ndola and Kitwe.</p>

<h2>Worked Example: Ordering Stock from Lusaka</h2>
<p>Mr Mwape runs an electrical shop in Livingstone. When a customer asks for a solar panel that is not in stock, Mr Mwape uses his supplier's website in Lusaka to check availability. He places the order online, pays by bank transfer, and receives a tracking number. The supplier uses a computerised inventory system that updates stock levels in real time. The customer receives the panel three days later. The computer has made ordering faster, more accurate and easier to track than a phone call or a handwritten order book.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Name three industries in Zambia where CAD could be useful.</li>
<li>Describe how a real-time booking system helps a bus company avoid selling the same seat twice.</li>
<li>Visit the ZRA website and list two services that can be done online.</li>
<li>Think of one local business that uses social media or WhatsApp to sell products. How does the computer help the business?</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Application</strong> — a practical use of computers in a particular field.</li>
<li><strong>Real-time system</strong> — a system that updates information immediately as events happen.</li>
<li><strong>CAD</strong> — Computer-Aided Design; software used to create technical drawings.</li>
<li><strong>E-commerce</strong> — buying and selling over the internet.</li>
<li><strong>E-government</strong> — delivering government services through digital systems.</li>
</ul>

<h2>Summary</h2>
<p>Computers support a wide range of industrial and commercial activities, from booking tickets and designing buildings to diagnosing vehicles and collecting taxes. These applications save time, reduce errors and give businesses and government agencies new ways to serve the public. As internet access improves across Zambia, such applications will become even more important.</p>
HTML,
            ],
            [
                'title' => 'A1.1.4 Computer-Based Learning and Its Social Effects',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what computer-based learning and programmed learning are, describe the social and economic effects of computers in Zambia, and discuss both the benefits and the challenges of living in a digital society.</p>

<h2>Computer-Based Learning</h2>
<p><strong>Computer-based learning</strong>, also called e-learning, uses a computer or mobile device to deliver lessons, exercises and assessments. Students can watch videos, read notes, answer quizzes and submit assignments without always being in a classroom. This is especially useful in Zambia where some learners live far from training centres or cannot attend classes every day.</p>
<p><strong>Programmed learning</strong> is a special type of computer-based learning where the material is broken into small steps. After each step the learner answers a question. If the answer is correct, the learner moves to the next step. If the answer is wrong, the computer explains the mistake and offers extra practice. This step-by-step approach is common in language apps, mathematics drills and vocational training software.</p>

<h2>Social Effects of Computers</h2>
<p>Computers have changed how people communicate, learn, work and socialise. A student in Kalomo can now watch a lecture recorded in Lusaka, chat with classmates on WhatsApp, and research assignments using the internet. Families keep in touch through video calls, and community groups share information through social media.</p>
<p>However, computers can also create problems. People who cannot afford devices or internet access may be left behind. Excessive screen time can affect health and relationships. False information spreads quickly online, and cyberbullying can harm young people. A balanced view recognises both the opportunities and the risks.</p>

<h2>Economic Effects of Computers</h2>
<p>On the economic side, computers help businesses reduce costs, reach more customers and create new types of jobs. A tailor who advertises on Facebook can find customers beyond her neighbourhood. A farmer who checks market prices online can decide the best day to sell his maize. New jobs have appeared in web design, data entry, mobile money agency, computer repair and online customer service.</p>
<p>At the same time, some traditional jobs have declined because computers can do the same work faster. Shop assistants, typists and file clerks may find their roles changing. Workers who learn computer skills are better prepared for the modern economy.</p>

<h2>Worked Example: A Rural Student Uses E-Learning</h2>
<p>Beauty lives in a village near Choma. She enrolled at Edutrack Computer Training College but cannot travel to Kalomo every week. The college gives her access to an online learning platform where she watches lesson videos, downloads notes and submits quizzes. She studies at home in the evening after helping her family. When she visits the college once a month, she takes practical exams. E-learning has made it possible for her to gain a certificate without leaving her community.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Describe one advantage and one disadvantage of computer-based learning for a student in a rural area.</li>
<li>Explain the difference between e-learning and programmed learning.</li>
<li>List three new jobs that exist because of computers and the internet.</li>
<li>Discuss with a partner: does computers bring people closer together or push them apart? Give reasons.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Computer-based learning</strong> — education delivered through computers or mobile devices.</li>
<li><strong>Programmed learning</strong> — step-by-step learning where the learner answers questions after each small section.</li>
<li><strong>Digital divide</strong> — the gap between people who have access to technology and those who do not.</li>
<li><strong>Cyberbullying</strong> — using digital devices to threaten, embarrass or harass someone.</li>
<li><strong>Economy</strong> — the system of producing, distributing and consuming goods and services in a country or region.</li>
</ul>

<h2>Summary</h2>
<p>Computer-based learning and programmed learning offer flexible ways to study, especially where distance or work limits attendance. Computers also have wide social and economic effects, improving communication and business opportunities while creating new challenges such as inequality and online harm. Understanding these effects helps you use computers responsibly and prepare for a changing world.</p>
HTML,
            ],
            [
                'title' => 'A1.1.5 Office Equipment and Dual-Purpose Devices',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to identify common office equipment such as photocopiers, scanners, fax machines, shredders and telephones, explain how some devices work as both input and output, and describe the use of magnetic cards, smart cards and point-of-sale equipment in Zambian shops and banks.</p>

<h2>Common Office Equipment</h2>
<p>A well-equipped office needs more than just computers. Several machines help staff produce, copy, send and protect documents. Each piece of equipment has a specific purpose, and using the right machine saves time and money.</p>
<ul>
<li><strong>Photocopier</strong> — makes paper copies of documents quickly. Modern photocopiers can also scan and email pages.</li>
<li><strong>Scanner</strong> — converts paper documents and photographs into digital files.</li>
<li><strong>Fax machine</strong> — sends documents over a telephone line. Although email is more common today, some government offices and banks still use fax.</li>
<li><strong>Shredder</strong> — cuts paper into thin strips or small pieces so confidential information cannot be read.</li>
<li><strong>Telephone</strong> — allows voice communication. Many offices now use internet-based phones or mobile phones.</li>
</ul>

<h2>Devices That Are Both Input and Output</h2>
<p>Some devices send data into the computer and also display or produce results. A <strong>touchscreen</strong> is the best example. When you tap an app on a smartphone, your finger provides input. When the screen shows a map or a message, it is acting as output. Laptop screens and desktop monitors with built-in touch support also work this way.</p>
<p>Another example is a <strong>multifunction printer</strong>. It receives a document from the computer to print, which is output, but when it scans a page back into the computer, it acts as input.</p>

<h2>Magnetic and Smart Cards</h2>
<p>A <strong>magnetic card</strong> stores data on a brown or black magnetic strip, like the back of an ATM card. When the card is swiped through a reader, the machine reads the account number and other details. <strong>Smart cards</strong> contain a small chip that can store more information and perform security checks. Many modern bank cards and mobile money agent cards are smart cards.</p>
<p>In Zambia, ATM cards allow customers to withdraw cash, check balances and pay bills at automated teller machines. Some supermarkets and filling stations also accept card payments through point-of-sale terminals.</p>

<h2>Point-of-Sale Equipment</h2>
<p>A <strong>Point-of-Sale (POS)</strong> terminal is the equipment used at a checkout counter. A typical POS system in a shop includes a computer, a barcode reader, a receipt printer, a cash drawer and a card reader. Some systems also include a magnetic deactivator to disable security tags on clothes.</p>
<p>When a customer buys rice at a supermarket in Lusaka, the cashier scans the barcode, the POS system looks up the price, adds it to the bill and prints a receipt. If the customer pays by card, the card reader sends the payment details to the bank for approval. The whole process takes only a few seconds.</p>

<h2>Worked Example: A Day at a Mobile Money Booth</h2>
<p>Chileshe works at an Airtel Money booth in Ndola. Her equipment includes a smartphone with the Airtel Money app, a receipt printer, a customer-facing display and a secure cash box. When a customer deposits K500, Chileshe enters the amount as input, the app processes the transaction, and the printer produces a receipt as output. The smartphone screen is both input and output because she touches it to enter details and it displays the confirmation message.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Visit an office or shop and list every piece of office equipment you can see.</li>
<li>Explain why a photocopier that can also scan is a dual-purpose device.</li>
<li>Draw and label a point-of-sale system, including the barcode reader, printer and card reader.</li>
<li>Describe the difference between a magnetic card and a smart card.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Photocopier</strong> — a machine that makes paper copies of documents.</li>
<li><strong>Scanner</strong> — a device that converts paper documents into digital files.</li>
<li><strong>Fax machine</strong> — a machine that sends documents over telephone lines.</li>
<li><strong>Shredder</strong> — a machine that destroys paper by cutting it into small pieces.</li>
<li><strong>Smart card</strong> — a card containing a chip that stores and processes data.</li>
<li><strong>Point-of-Sale (POS)</strong> — the place where a customer pays for goods or services.</li>
</ul>

<h2>Summary</h2>
<p>Office equipment such as photocopiers, scanners, fax machines, shredders and telephones support daily business tasks. Some devices, including touchscreens and multifunction printers, act as both input and output. Magnetic cards, smart cards and POS systems are essential in banking and retail, making transactions faster and more secure for businesses and customers across Zambia.</p>
HTML,
            ],
        ];
    }

    private function module1Quiz(): array
    {
        return [
            'title' => 'A1.1 Quiz: Using Computer Gadgets & Media in Everyday Life',
            'description' => 'Test your knowledge of computer parts, peripherals, applications and office equipment.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is an input device?',
                    'explanation' => 'A keyboard is an input device because it sends data into the computer.',
                    'options' => [
                        ['text' => 'Monitor', 'is_correct' => false],
                        ['text' => 'Printer', 'is_correct' => false],
                        ['text' => 'Keyboard', 'is_correct' => true],
                        ['text' => 'Speaker', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does CAD stand for?',
                    'explanation' => 'CAD stands for Computer-Aided Design, used for technical drawing.',
                    'options' => [
                        ['text' => 'Computer-Aided Drawing', 'is_correct' => false],
                        ['text' => 'Computer-Aided Design', 'is_correct' => true],
                        ['text' => 'Central Application Design', 'is_correct' => false],
                        ['text' => 'Computerised Automatic Drawing', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A touchscreen can act as both an input and an output device.',
                    'explanation' => 'A touchscreen accepts touch input and also displays information, so it is both input and output.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which port is commonly used to connect a printer to a computer?',
                    'explanation' => 'USB is the most common port for connecting printers, keyboards and other peripherals.',
                    'options' => [
                        ['text' => 'HDMI', 'is_correct' => false],
                        ['text' => 'VGA', 'is_correct' => false],
                        ['text' => 'USB', 'is_correct' => true],
                        ['text' => 'Ethernet', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A smart card stores data on a magnetic strip only.',
                    'explanation' => 'A smart card contains a chip, while a magnetic card stores data on a magnetic strip.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the first step in the input-process-output cycle?',
                    'explanation' => 'The cycle begins when the user provides input to the computer.',
                    'options' => [
                        ['text' => 'Processing', 'is_correct' => false],
                        ['text' => 'Output', 'is_correct' => false],
                        ['text' => 'Storage', 'is_correct' => false],
                        ['text' => 'Input', 'is_correct' => true],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Module 2: A1.2 Carrying Out Basic Computer Operations
    // ------------------------------------------------------------------

    private function module2Lessons(): array
    {
        return [
            [
                'title' => 'A1.2.1 Health, Safety and Ergonomics',
                'duration_minutes' => 35,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to set up a computer workstation that protects your health, explain why good posture matters, and follow basic safety rules when using computers in a Zambian college, office or home.</p>

<h2>Why Health and Safety Matter</h2>
<p>Using a computer seems safe, but poor habits can cause long-term health problems. Sitting badly for many hours can lead to back and neck pain. Staring at a screen for too long can tire the eyes and cause headaches. Repeated typing and mouse use can damage the wrists and fingers. These problems are called <strong>Repetitive Strain Injuries</strong> or RSIs.</p>

<h2>Setting Up an Ergonomic Workstation</h2>
<p><strong>Ergonomics</strong> means arranging equipment so the user can work comfortably and safely. A good workstation has the following features:</p>
<ul>
<li>The chair supports the lower back and allows the feet to rest flat on the floor.</li>
<li>The monitor is at eye level and about an arm's length away.</li>
<li>The keyboard and mouse are close enough that the elbows stay at the sides.</li>
<li>The wrists are kept straight while typing, not bent upwards or sideways.</li>
<li>The room has enough light to read paper documents without glare on the screen.</li>
</ul>

<h2>Safe Working Habits</h2>
<p>Taking short breaks is one of the best ways to stay healthy. The 20-20-20 rule is helpful: every twenty minutes, look at something twenty feet away for twenty seconds. This relaxes the eye muscles. Standing up, stretching and walking for a few minutes every hour also reduce muscle tension.</p>
<p>Other safety rules include keeping drinks away from the keyboard, not overloading power sockets, and using a surge protector during Zambia's frequent load-shedding and power surges. Cables should be tucked away so nobody trips over them.</p>

<h2>Worked Example: Correct Posture at Edutrack College</h2>
<p>Maryam sits at a computer in the Edutrack Computer Training College lab. She adjusts her chair so her feet rest flat on the floor and her knees are level with her hips. She moves the monitor until the top of the screen is at eye level. She places the keyboard directly in front of her and keeps her wrists straight while typing. Every thirty minutes she stands up, stretches her arms and looks out of the window. At the end of the day she does not feel tired or sore.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Sit at a computer and check your posture. Are your feet flat on the floor? Is the monitor at eye level?</li>
<li>Measure the distance from your eyes to the screen. It should be about an arm's length.</li>
<li>Practise the 20-20-20 rule during a lesson.</li>
<li>Draw a diagram of an ergonomic workstation and label the chair, monitor, keyboard and mouse positions.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Ergonomics</strong> — designing a workspace to fit the worker and prevent injury.</li>
<li><strong>Posture</strong> — the position of the body while sitting or standing.</li>
<li><strong>Repetitive Strain Injury (RSI)</strong> — damage caused by repeating the same movement many times.</li>
<li><strong>Surge protector</strong> — a device that protects equipment from sudden increases in voltage.</li>
</ul>

<h2>Summary</h2>
<p>Good health and safety habits prevent pain and injury when using computers. An ergonomic workstation, regular breaks, correct posture and careful use of power protect students and workers. These habits are especially important in Zambia, where power fluctuations and long study sessions are common.</p>
HTML,
            ],
            [
                'title' => 'A1.2.2 Caring for Computer Systems and Preventing Data Loss',
                'duration_minutes' => 35,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe how to care for computer hardware and software, explain the causes of data loss, and make simple backups to protect important school or business files.</p>

<h2>Caring for Hardware</h2>
<p>Computers last longer when they are treated well. Dust can block cooling vents and cause overheating. Spilled drinks can destroy keyboards and motherboards. Rough handling can damage screens and hard drives. Practical care includes keeping food and drink away from the computer, cleaning the screen with a soft dry cloth, and using a cover or case when moving a laptop.</p>
<p>In Zambia, heat and dust are common problems. A computer should be placed where air can flow freely around it. Fans and vents should be checked regularly for dust. During load-shedding, a laptop battery should be charged when power is available, and a desktop should be connected to an uninterruptible power supply or surge protector.</p>

<h2>Caring for Software</h2>
<p>Software also needs care. The operating system and applications should be updated regularly to fix security holes. Unused programs should be removed to free space. Antivirus software should be installed and kept up to date. Users should avoid downloading software from unknown websites because it may contain viruses or steal personal information.</p>

<h2>Preventing Data Loss</h2>
<p>Data loss happens when files are deleted by mistake, the storage device fails, the computer is stolen, or a virus destroys information. Important files such as assignments, business records, photographs and certificates should be backed up. A <strong>backup</strong> is a copy of data stored in a safe place.</p>
<p>Good backup habits include:</p>
<ul>
<li>Saving work frequently while typing, especially during load-shedding.</li>
<li>Keeping a copy on a USB flash drive or external hard drive.</li>
<li>Using cloud storage such as Google Drive or OneDrive when internet is available.</li>
<li>Keeping backup devices in a different location from the computer.</li>
</ul>

<h2>Worked Example: A Student Protects Her Project</h2>
<p>Linda is writing her final project for the Trade Certificate in Computer Studies Level III. She saves the file every ten minutes. At the end of each day she copies it to a USB flash drive and also uploads it to her Google Drive account. One evening there is a power surge and her computer will not start. Because her work is backed up, she continues editing on a college computer the next day without losing any work.</p>

<h2>Try It Yourself</h2>
<ol>
<li>List five things that can damage a computer physically.</li>
<li>Explain three ways to prevent data loss.</li>
<li>Create a folder on a USB drive and copy an important file to it as a backup.</li>
<li>Write a short checklist for daily computer care.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Backup</strong> — a copy of files kept in a safe place.</li>
<li><strong>Overheating</strong> — when a computer becomes too hot to work properly.</li>
<li><strong>Virus</strong> — harmful software that can damage files or steal information.</li>
<li><strong>Antivirus</strong> — software that detects and removes viruses.</li>
<li><strong>Load-shedding</strong> — planned power cuts to manage electricity supply.</li>
</ul>

<h2>Summary</h2>
<p>Caring for hardware and software helps computers last longer and work reliably. Regular backups protect valuable files from accidents, theft, power surges and viruses. These habits are essential for students, office workers and business owners who depend on digital information.</p>
HTML,
            ],
            [
                'title' => 'A1.2.3 Basic Operations, Copyright, Security and User Interfaces',
                'duration_minutes' => 30,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to start and shut down a computer correctly, explain software copyright and licensing, perform basic security checks, identify types of user interfaces, force a shutdown on an unresponsive computer, and compare common storage capacities.</p>

<h2>Starting and Shutting Down</h2>
<p>To start a desktop computer, press the power button on the system unit and the monitor. Wait for the operating system to load. To shut down safely, click the Start menu, choose Power, and select Shut down. This allows the computer to close all programs and save settings. Never turn off the power at the socket while the computer is still running, because this can damage files.</p>

<h2>Copyright and Software Licensing</h2>
<p>Software is protected by copyright law. This means you cannot copy or install a program without permission. There are different types of software licences:</p>
<ul>
<li><strong>Commercial software</strong> must be bought. Microsoft Office is an example.</li>
<li><strong>Freeware</strong> is free to use. Some PDF readers are freeware.</li>
<li><strong>Shareware</strong> can be tried for free for a limited time, but you must pay to keep using it.</li>
<li><strong>Open-source software</strong> allows users to view and modify the code. LibreOffice is an example.</li>
</ul>
<p>The <strong>End User Licence Agreement (EULA)</strong> is the legal document you accept when installing software. It explains what you are allowed to do. Using pirated software is illegal and can also bring viruses.</p>

<h2>Security Checks and Virus Scans</h2>
<p>Security protects both the computer and the data on it. A computer should have a username and password so that only authorised people can use it. The password should be strong, mixing letters, numbers and symbols. Antivirus software should be installed and set to scan files regularly. Users should also check that important files have not been changed accidentally, which is called checking <strong>data integrity</strong>.</p>

<h2>Types of User Interfaces</h2>
<p>A <strong>user interface</strong> is the way a person interacts with a computer. The most common type today is the <strong>Graphical User Interface (GUI)</strong>, which uses icons, menus and windows. Microsoft Windows and Android use GUIs. An older type is the <strong>Command Line Interface (CLI)</strong>, where the user types commands as text. A <strong>touch interface</strong> uses finger taps and swipes on a screen.</p>

<h2>Force Shutdown and Storage Capacities</h2>
<p>If a computer stops responding, sometimes the only choice is a <strong>force shutdown</strong>. On a desktop, hold the power button for several seconds until the machine turns off. On a laptop, the same method works, but use it only when normal shutdown fails.</p>
<p>Storage capacities are measured in bytes. Common units are:</p>
<ul>
<li><strong>Kilobyte (KB)</strong> — about one page of text.</li>
<li><strong>Megabyte (MB)</strong> — about one minute of music or a small photo.</li>
<li><strong>Gigabyte (GB)</strong> — about one hour of video or hundreds of photos.</li>
<li><strong>Terabyte (TB)</strong> — about one thousand GB, enough for a large media collection.</li>
</ul>

<h2>Worked Example: Choosing Storage for a Business</h2>
<p>Mr Banda runs a small photography studio in Livingstone. He takes about one hundred photos per event and each photo is about 5 MB. After ten events he has about 500 MB. A 16 GB memory card can hold many events, but for long-term storage he buys a 1 TB external hard drive. This gives him enough space for years of photos and backups.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Practise shutting down a computer using the Start menu.</li>
<li>Read the EULA or licence information of one program on the computer.</li>
<li>Open the antivirus software and run a quick scan.</li>
<li>List three differences between a GUI and a CLI.</li>
<li>Convert these sizes from smallest to largest: TB, MB, GB, KB.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Copyright</strong> — legal protection that prevents copying without permission.</li>
<li><strong>EULA</strong> — End User Licence Agreement; the rules for using a software product.</li>
<li><strong>GUI</strong> — Graphical User Interface; uses pictures and menus.</li>
<li><strong>CLI</strong> — Command Line Interface; uses typed commands.</li>
<li><strong>Data integrity</strong> — making sure data is accurate and has not been changed incorrectly.</li>
</ul>

<h2>Summary</h2>
<p>Basic computer operations include safe start-up and shutdown, respecting software licences, running security checks, and choosing the right storage. Understanding user interfaces helps you use different devices, and knowing how to force a shutdown can rescue an unresponsive computer. These skills form the foundation for confident and responsible computer use.</p>
HTML,
            ],
        ];
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'A1.2 Quiz: Carrying Out Basic Computer Operations',
            'description' => 'Test your understanding of health and safety, computer care, copyright, security and basic operations.',
            'time_limit_minutes' => 15,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following helps prevent eye strain?',
                    'explanation' => 'The 20-20-20 rule relaxes the eyes by looking at a distant object regularly.',
                    'options' => [
                        ['text' => 'Typing faster', 'is_correct' => false],
                        ['text' => 'Using a larger chair', 'is_correct' => false],
                        ['text' => 'The 20-20-20 rule', 'is_correct' => true],
                        ['text' => 'Turning off the monitor', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Freeware is software that must be purchased before use.',
                    'explanation' => 'Freeware is free to use; commercial software is purchased.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should you do if a computer stops responding and normal shutdown does not work?',
                    'explanation' => 'Holding the power button for several seconds forces the computer to turn off.',
                    'options' => [
                        ['text' => 'Pull out all cables', 'is_correct' => false],
                        ['text' => 'Pour water on it', 'is_correct' => false],
                        ['text' => 'Hold the power button until it turns off', 'is_correct' => true],
                        ['text' => 'Press any key repeatedly', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A Graphical User Interface uses typed commands instead of icons.',
                    'explanation' => 'A GUI uses icons and menus; a command line interface uses typed commands.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which storage size is the largest?',
                    'explanation' => 'A terabyte is larger than a gigabyte, megabyte and kilobyte.',
                    'options' => [
                        ['text' => 'Kilobyte', 'is_correct' => false],
                        ['text' => 'Megabyte', 'is_correct' => false],
                        ['text' => 'Gigabyte', 'is_correct' => false],
                        ['text' => 'Terabyte', 'is_correct' => true],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Module 3: A1.3 Using Word Processing Package Effectively
    // ------------------------------------------------------------------

    private function module3Lessons(): array
    {
        return [
            [
                'title' => 'A1.3.1 Getting Started with Word Processing',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to open a word processor, identify the main parts of the window, use the menu bar and toolbars, and understand the difference between Microsoft Word, LibreOffice Writer and other word processing programs.</p>

<h2>What Is Word Processing?</h2>
<p>Word processing is the use of a computer to create, edit, format and print text documents. A <strong>word processor</strong> is an application designed for this purpose. Before computers, documents were typed on typewriters. Mistakes required correction fluid or retyping the whole page. Word processors make it easy to correct errors, move text, change fonts and save many versions of a document.</p>

<h2>Opening and Closing Word</h2>
<p>To open Microsoft Word, click the Start menu, find Microsoft Office, and select Microsoft Word. Alternatively, double-click the Word icon on the desktop. When Word opens, you see a blank document with a blinking cursor. To close Word, click the X button in the top-right corner or press Alt+F4. Always save your work before closing.</p>
<p>LibreOffice Writer is opened in a similar way. At Edutrack Computer Training College, both programs may be installed. The skills learned in one transfer easily to the other because both use ribbons or menus with similar names.</p>

<h2>The Word Window</h2>
<p>The Word window has several important areas:</p>
<ul>
<li><strong>Title bar</strong> — shows the name of the document at the top.</li>
<li><strong>Menu bar or ribbon</strong> — contains tabs such as Home, Insert, Page Layout and View.</li>
<li><strong>Toolbar or Quick Access Toolbar</strong> — holds shortcuts for Save, Undo and Redo.</li>
<li><strong>Ruler</strong> — shows margins and tabs.</li>
<li><strong>Document area</strong> — the white page where you type.</li>
<li><strong>Status bar</strong> — at the bottom, shows the page number and word count.</li>
</ul>

<h2>Keyboard Skills</h2>
<p>Good keyboard skills make word processing faster. The <strong>function keys</strong> along the top of the keyboard perform special tasks. For example, F7 starts spell check in Word. Common shortcuts include Ctrl+C to copy, Ctrl+V to paste, Ctrl+X to cut, Ctrl+Z to undo and Ctrl+S to save. Learning these shortcuts saves time compared with using the mouse for every action.</p>

<h2>Worked Example: Opening and Customising Word</h2>
<p>John opens Microsoft Word to type a business letter. He clicks the File tab and chooses Options. In the General section he sets his username so the document properties show his name. He adds Save and Undo to the Quick Access Toolbar. He then types the letter, saves it as "Letter_to_Supplier.docx" and checks the status bar to see he has written 250 words.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open Microsoft Word or LibreOffice Writer.</li>
<li>Identify the title bar, ribbon or menu bar, ruler and status bar.</li>
<li>Customise the Quick Access Toolbar by adding Save and Print Preview.</li>
<li>Type your name and the date. Save the document with a clear filename.</li>
<li>Practise the shortcuts Ctrl+S, Ctrl+C, Ctrl+V and Ctrl+Z.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Word processor</strong> — software used to create and edit text documents.</li>
<li><strong>Ribbon</strong> — a toolbar with tabs and commands in Microsoft Office programs.</li>
<li><strong>Quick Access Toolbar</strong> — a small toolbar for common commands such as Save and Undo.</li>
<li><strong>Cursor</strong> — the blinking line that shows where text will appear.</li>
<li><strong>Function keys</strong> — keys labelled F1 to F12 that perform special tasks.</li>
</ul>

<h2>Summary</h2>
<p>Word processors make document creation fast and flexible. Knowing the window layout, toolbars and keyboard shortcuts helps you work efficiently. Whether you use Microsoft Word or LibreOffice Writer, the same principles apply: type, save, format and print.</p>
HTML,
            ],
            [
                'title' => 'A1.3.2 Text Manipulation and Proof Reading',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to select, move, copy and delete text, insert the date and time, check the word count, use spell check and thesaurus tools, and find specific words in a document.</p>

<h2>Selecting and Editing Text</h2>
<p>To change text, you must first <strong>select</strong> it. Click and drag the mouse over the text you want to change, or hold Shift and press the arrow keys. Once text is selected, you can delete it, change its font, move it or copy it.</p>
<p>To move text, select it and click Cut, or press Ctrl+X. Click where you want the text to go and click Paste, or press Ctrl+V. To copy text instead of moving it, use Copy or Ctrl+C. To delete a word, double-click it and press Delete. To undo a mistake, press Ctrl+Z.</p>

<h2>Copying Between Windows</h2>
<p>You can copy text from one document to another. Open both documents, select the text in the first, press Ctrl+C, switch to the second document, and press Ctrl+V. This is useful when combining notes from different lessons or reusing a standard paragraph in many letters.</p>

<h2>Inserting Date and Time and Counting Words</h2>
<p>To insert the current date or time, click the Insert tab and choose Date & Time. You can select different formats. The date updates automatically if you choose a field that refreshes. To see the word count, look at the status bar at the bottom of the window. For a detailed count including characters, click the word count area.</p>

<h2>Proof Reading Tools</h2>
<p>Proof reading means checking a document for mistakes. Word processors help with several tools:</p>
<ul>
<li><strong>Spell check</strong> finds spelling mistakes. Press F7 to start a full check.</li>
<li><strong>Grammar check</strong> suggests corrections for grammar and punctuation.</li>
<li><strong>Thesaurus</strong> offers synonyms to avoid repeating the same word.</li>
<li><strong>Find</strong> locates a word or phrase. Press Ctrl+F to open the Find pane.</li>
</ul>
<p>These tools are helpful, but they do not replace careful reading. A spell checker may not notice if you write "form" instead of "from."</p>

<h2>Worked Example: Editing a Job Application Letter</h2>
<p>Grace writes a job application letter in Word. She notices she typed the wrong employer name. She uses Find to locate the old name, then uses Replace to change every occurrence to the correct name. She runs spell check and accepts three corrections. She checks the word count to make sure the letter is between 200 and 300 words. Finally she saves the corrected document.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Type a short paragraph about your career goals.</li>
<li>Select one sentence and move it to the end of the paragraph.</li>
<li>Copy the paragraph into a new document.</li>
<li>Insert the current date at the top of the document.</li>
<li>Run spell check and check the word count.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Select</strong> — to highlight text so it can be changed.</li>
<li><strong>Cut</strong> — to remove selected text and place it on the clipboard.</li>
<li><strong>Copy</strong> — to duplicate selected text to the clipboard.</li>
<li><strong>Paste</strong> — to place copied or cut text into a document.</li>
<li><strong>Synonym</strong> — a word with a similar meaning to another word.</li>
</ul>

<h2>Summary</h2>
<p>Word processors provide powerful tools for editing and proof reading. Selecting, cutting, copying and pasting make it easy to rearrange text. Spell check, thesaurus, find and word count help you produce polished documents. Always read through your work because automatic tools can miss some errors.</p>
HTML,
            ],
            [
                'title' => 'A1.3.3 Formatting Text, Paragraphs and Pages',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to change font size and style, align paragraphs, set line spacing, apply bullets and numbering, use undo and redo, and zoom in and out of a document.</p>

<h2>Font Formatting</h2>
<p>Font formatting changes the appearance of text. The most common changes are:</p>
<ul>
<li><strong>Font family</strong> — the design of the letters, such as Times New Roman or Arial.</li>
<li><strong>Font size</strong> — measured in points. Normal body text is usually 11 or 12 points; headings are larger.</li>
<li><strong>Font style</strong> — bold, italic or underline.</li>
<li><strong>Font colour</strong> — the colour of the text.</li>
</ul>
<p>To apply formatting, select the text and use the commands on the Home tab or the Format menu. You can also use shortcuts such as Ctrl+B for bold, Ctrl+I for italic and Ctrl+U for underline.</p>

<h2>Paragraph Formatting</h2>
<p>Paragraph formatting controls how blocks of text look. You can set:</p>
<ul>
<li><strong>Alignment</strong> — left, centre, right or justified.</li>
<li><strong>Line spacing</strong> — the space between lines, such as single, 1.5 or double.</li>
<li><strong>Bullets and numbering</strong> — for lists.</li>
<li><strong>Indentation</strong> — moving text in from the margin.</li>
</ul>
<p>For a business letter, the body is usually left-aligned with single spacing. Reports often use justified text and headings in bold. Use bullets for unordered lists and numbering for steps that must follow an order.</p>

<h2>Zoom, Undo and Redo</h2>
<p>The zoom control changes how large the page looks on screen. It does not change the printed size. To see more of the page, zoom out. To read small text, zoom in. The Undo button reverses the last action. Redo puts it back. These are useful when experimenting with formatting.</p>

<h2>Worked Example: Formatting a Meeting Agenda</h2>
<p>The secretary of a church committee in Kalomo prepares a meeting agenda. She types the title "Church Fundraiser Meeting" and makes it bold, centred and 16 points. She types the date and venue in italic. She creates a numbered list for the agenda items: opening prayer, minutes, financial report, AOB and closing prayer. She sets line spacing to 1.5 so the document is easy to read. Finally she saves and prints ten copies.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Type three paragraphs about a local event in your community.</li>
<li>Make the title bold, centred and 16 points.</li>
<li>Change the body text to 12 points and justified.</li>
<li>Add a bulleted list of at least three items.</li>
<li>Change the zoom to 150 percent and then back to 100 percent.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Font</strong> — the style and size of text characters.</li>
<li><strong>Alignment</strong> — how text is positioned horizontally on the page.</li>
<li><strong>Line spacing</strong> — the amount of space between lines of text.</li>
<li><strong>Indentation</strong> — moving text away from the margin.</li>
<li><strong>Justified</strong> — text aligned evenly on both left and right margins.</li>
</ul>

<h2>Summary</h2>
<p>Formatting makes documents clear and professional. Font choices, paragraph alignment, line spacing, bullets and numbering all help readers understand your message. Undo, redo and zoom make editing easier. Good formatting is especially important for business letters, reports and agendas.</p>
HTML,
            ],
            [
                'title' => 'A1.3.4 Pagination, Saving and Printing',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to change page orientation and paper size, insert page numbers through headers and footers, save documents in different formats, use print preview, and print all, current or selected pages.</p>

<h2>Pagination</h2>
<p><strong>Pagination</strong> means controlling how a document is divided into pages. Word processors allow you to set the <strong>orientation</strong> to portrait, which is taller than wide, or landscape, which is wider than tall. You can also choose the paper size, such as A4 or Letter. A document can mix orientations by inserting section breaks. For example, a report might be portrait, but a wide table could be placed on a landscape page.</p>

<h2>Headers and Footers</h2>
<p>A <strong>header</strong> appears at the top of every page and a <strong>footer</strong> appears at the bottom. Headers often contain the document title, while footers often contain page numbers. To insert a page number, double-click in the footer area and choose Page Number from the Header & Footer tools. You can place the number at the centre or right side of the footer.</p>

<h2>Saving in Different Formats</h2>
<p>Documents can be saved in several formats. The default Word format is .docx. You can also save as .pdf, which keeps the formatting the same on any computer, or as a web page for viewing in a browser. To save in a different format, choose File, Save As, and select the format from the drop-down list.</p>

<h2>Printing</h2>
<p>Before printing, use <strong>Print Preview</strong> to see how the document will look on paper. This helps you catch formatting problems and saves paper. To print, choose File and then Print, or press Ctrl+P. You can choose to print the whole document, only the current page, or a range of pages such as pages 1 to 3. You can also select specific text and choose Print Selection.</p>

<h2>Worked Example: Preparing a ZRA Letter</h2>
<p>Mr Zulu writes a letter to the Zambia Revenue Authority requesting a TPIN confirmation. He sets the page to A4 portrait. He adds his address as a header and a page number in the footer. He checks the letter in Print Preview and notices the second paragraph is split awkwardly across pages, so he inserts a page break. He saves the final letter as a PDF so he can email it to ZRA and also print a copy for his records.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a two-page document and add page numbers in the footer.</li>
<li>Change the page orientation to landscape and observe the difference.</li>
<li>Save the document as a PDF and compare the file sizes.</li>
<li>Use Print Preview to check how the document will look when printed.</li>
<li>Print only the current page.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Portrait</strong> — page orientation that is taller than it is wide.</li>
<li><strong>Landscape</strong> — page orientation that is wider than it is tall.</li>
<li><strong>Header</strong> — text that appears at the top of every page.</li>
<li><strong>Footer</strong> — text that appears at the bottom of every page.</li>
<li><strong>Print Preview</strong> — a screen view showing how a document will look when printed.</li>
</ul>

<h2>Summary</h2>
<p>Pagination, headers, footers and saving options give you control over the final appearance of a document. Print preview prevents wasted paper, and choosing the right file format makes sharing easier. These skills are essential for producing professional letters, reports and assignments.</p>
HTML,
            ],
            [
                'title' => 'A1.3.5 Search, Replace and Simple Graphics',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to use Find and Replace to change text throughout a document, draw simple shapes, insert clip art and WordArt, and combine shapes and text to create basic graphics.</p>

<h2>Search and Replace</h2>
<p>The <strong>Find</strong> tool locates a word or phrase. Press Ctrl+F to open the navigation pane. The <strong>Replace</strong> tool finds a word and replaces it with another. Press Ctrl+H to open Replace. This is useful when you need to change a name, a date or a price in a long document. You can replace one occurrence at a time or use <strong>Replace All</strong> to change every occurrence at once. Use Replace All with care because it may change words you did not intend to change.</p>

<h2>Drawing Simple Graphics</h2>
<p>Word processors include tools for drawing lines, rectangles, circles, arrows and other shapes. On the Insert tab, choose Shapes. Click and drag on the page to draw the shape. You can change the outline colour, fill colour and size. Shapes are useful for creating simple diagrams, flowcharts and organisational charts.</p>

<h2>Clip Art and WordArt</h2>
<p><strong>Clip art</strong> is a collection of ready-made pictures that you can insert into a document. Modern versions of Word include online pictures and icons instead of traditional clip art. <strong>WordArt</strong> is decorative text with special effects such as shadows, reflections and curved shapes. Use WordArt sparingly for titles or posters; too much decoration looks unprofessional in a business document.</p>

<h2>Combining Shapes and Text</h2>
<p>You can combine shapes, text boxes and pictures to create simple graphics such as flyers or notices. Insert a shape as a background, add a text box on top, and adjust colours until the design is clear. Group objects so they move together. To group, select all objects, right-click, and choose Group.</p>

<h2>Worked Example: Updating a Price List</h2>
<p>Mrs Lungu has a price list in Word with fifty items. The price of mealie meal has changed from K120 to K135. She opens Replace, types "K120" in Find and "K135" in Replace, then clicks Replace All. In seconds every occurrence is updated. She then inserts a rectangle shape around the new price to draw attention to it and saves the document.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Type a paragraph that includes the word "Kalomo" three times.</li>
<li>Use Replace All to change "Kalomo" to "Livingstone."</li>
<li>Insert three shapes and change their fill colours.</li>
<li>Add a text box and type a title inside it.</li>
<li>Insert a piece of clip art or an online icon related to education.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Find</strong> — a tool that locates text in a document.</li>
<li><strong>Replace</strong> — a tool that changes one word or phrase to another.</li>
<li><strong>Clip art</strong> — ready-made pictures that can be inserted into documents.</li>
<li><strong>WordArt</strong> — decorative text with special visual effects.</li>
<li><strong>Group</strong> — to join objects so they move and resize together.</li>
</ul>

<h2>Summary</h2>
<p>Find and Replace save time when updating repeated text. Drawing tools, clip art and WordArt help you add visual interest to documents. By combining shapes and text, you can create simple but effective graphics for notices, flyers and presentations.</p>
HTML,
            ],
        ];
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'A1.3 Quiz: Using Word Processing Package Effectively',
            'description' => 'Test your understanding of text manipulation, formatting, pagination, printing and simple graphics.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which keyboard shortcut is used to save a document?',
                    'explanation' => 'Ctrl+S is the shortcut to save a document in most word processors.',
                    'options' => [
                        ['text' => 'Ctrl+P', 'is_correct' => false],
                        ['text' => 'Ctrl+S', 'is_correct' => true],
                        ['text' => 'Ctrl+C', 'is_correct' => false],
                        ['text' => 'Ctrl+Z', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does the spell checker do?',
                    'explanation' => 'The spell checker finds words that may be spelled incorrectly.',
                    'options' => [
                        ['text' => 'Counts the pages', 'is_correct' => false],
                        ['text' => 'Finds spelling mistakes', 'is_correct' => true],
                        ['text' => 'Changes the font', 'is_correct' => false],
                        ['text' => 'Inserts pictures', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Page orientation can be either portrait or landscape.',
                    'explanation' => 'Word processors support both portrait and landscape orientations.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which feature changes every occurrence of one word to another?',
                    'explanation' => 'Replace All updates every occurrence of the found text.',
                    'options' => [
                        ['text' => 'Find', 'is_correct' => false],
                        ['text' => 'Undo', 'is_correct' => false],
                        ['text' => 'Replace All', 'is_correct' => true],
                        ['text' => 'Print Preview', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Headers appear at the bottom of every page.',
                    'explanation' => 'Headers appear at the top of every page; footers appear at the bottom.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which page orientation is wider than it is tall?',
                    'explanation' => 'Landscape orientation is wider than it is tall.',
                    'options' => [
                        ['text' => 'Portrait', 'is_correct' => false],
                        ['text' => 'Landscape', 'is_correct' => true],
                        ['text' => 'A4', 'is_correct' => false],
                        ['text' => 'Letter', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Module 4: A1.4 Using Spreadsheet Package Effectively
    // ------------------------------------------------------------------

    private function module4Lessons(): array
    {
        return [
            [
                'title' => 'A1.4.1 Introduction to Spreadsheets',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain the difference between a worksheet and a workbook, identify the main features of a spreadsheet, and describe everyday spreadsheet applications used by Zambian businesses and organisations.</p>

<h2>What Is a Spreadsheet?</h2>
<p>A spreadsheet is a program that organises data in rows and columns. The intersection of a row and a column is called a <strong>cell</strong>. Each cell can hold text, numbers or a formula. Spreadsheets are excellent for calculations because they update answers automatically when the input values change.</p>

<h2>Workbook and Worksheet</h2>
<p>A <strong>workbook</strong> is a spreadsheet file. A workbook can contain many <strong>worksheets</strong>, each like a separate page. For example, a tuck-shop workbook might have one worksheet for daily sales, another for expenses and another for profit summary. Tabs at the bottom of the window let you switch between worksheets.</p>

<h2>Common Spreadsheet Features</h2>
<p>Every spreadsheet program has similar features. The workspace is a grid of cells. Columns are labelled with letters such as A, B, C. Rows are labelled with numbers such as 1, 2, 3. A cell address such as B3 refers to the cell in column B and row 3. The formula bar shows the contents of the selected cell. Toolbars and ribbons provide buttons for formatting, inserting functions and creating charts.</p>

<h2>Everyday Applications</h2>
<p>Spreadsheets are used in many real situations. A school bursar tracks fees and payments. A shop owner records sales and calculates profit. A church treasurer records offerings and prepares monthly reports. A CDF committee tracks how much money has been disbursed and which projects have been funded. Even a small farm can use a spreadsheet to compare the cost of seed, fertiliser and labour against the selling price of crops.</p>

<h2>Worked Example: A Tuck-Shop Sales Sheet</h2>
<p>Mr Tembo runs a tuck shop near a school in Kalomo. He creates a spreadsheet to track daily sales. Column A lists items such as buns, sweets, soft drinks and exercise books. Column B shows the quantity sold. Column C shows the unit price in Kwacha. Column D contains a formula to calculate total sales for each item by multiplying quantity by price. At the bottom of column D, a SUM formula gives the daily total. When he changes a quantity, the totals update automatically.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open Microsoft Excel or LibreOffice Calc.</li>
<li>Create a new workbook and identify the worksheet tabs.</li>
<li>Click on cell C5 and note the cell address shown in the Name Box.</li>
<li>Type "Item", "Quantity" and "Price" in cells A1, B1 and C1.</li>
<li>List five items you might sell in a small shop.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Cell</strong> — the box formed where a row and column meet.</li>
<li><strong>Worksheet</strong> — a single page or grid within a spreadsheet file.</li>
<li><strong>Workbook</strong> — a spreadsheet file that can contain many worksheets.</li>
<li><strong>Cell address</strong> — the reference of a cell, such as A1 or B3.</li>
<li><strong>Formula bar</strong> — the area that displays the contents of the active cell.</li>
</ul>

<h2>Summary</h2>
<p>Spreadsheets organise data in cells, worksheets and workbooks. They are powerful tools for calculation and record keeping in shops, schools, churches and community projects. Understanding the basic layout and terms is the first step toward creating useful spreadsheets.</p>
HTML,
            ],
            [
                'title' => 'A1.4.2 Entering, Editing and Formatting Data',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to enter and edit data in cells, format numbers and text, adjust column widths and row heights, insert and delete rows and columns, and prepare a worksheet for printing.</p>

<h2>Entering Data</h2>
<p>To enter data, click a cell and type. Press Enter to move down one cell or Tab to move right. You can enter text, numbers or dates. Text is usually left-aligned by default, while numbers are right-aligned. If a number is too wide for the column, the cell may display hash symbols. This does not mean the data is lost; it simply means the column needs to be wider.</p>

<h2>Editing and Deleting Data</h2>
<p>To edit a cell, either double-click it or click it and press F2. Make your changes and press Enter. To clear the contents, select the cell and press Delete. To delete the entire row or column, right-click the row or column header and choose Delete. To insert a new row or column, right-click the header and choose Insert.</p>

<h2>Formatting Cells</h2>
<p>Formatting makes a spreadsheet easier to read. You can:</p>
<ul>
<li>Change font size and style for headings.</li>
<li>Apply borders to separate sections.</li>
<li>Set number formats, such as currency for prices in Kwacha or percentage for growth rates.</li>
<li>Choose decimal places, for example two decimal places for money.</li>
<li>Change text orientation so words run vertically.</li>
</ul>

<h2>Column Width and Row Height</h2>
<p>To adjust a column width, position the mouse on the line between two column headers and drag. To fit the column to the widest entry, double-click the line between the headers. Row heights can be adjusted in the same way. Good formatting makes all data visible without wasting space.</p>

<h2>Worked Example: Formatting a CDF Disbursement Tracker</h2>
<p>A CDF committee secretary creates a worksheet with columns for Project Name, Amount Approved, Amount Spent and Balance. She enters project names in column A and amounts in columns B and C. She formats the amount columns as currency with the Kwacha symbol. She inserts a new row for a project she forgot. She widens column A so the project names fit. In column D she types a formula to subtract amount spent from amount approved. The worksheet is now clear and ready to present to the committee.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Enter ten rows of data about tuck-shop sales.</li>
<li>Format the price column as currency with two decimal places.</li>
<li>Insert a new row after row 5.</li>
<li>Delete an empty column you do not need.</li>
<li>Adjust column widths so all data is visible.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Format</strong> — to change the appearance of cells.</li>
<li><strong>Currency format</strong> — a number format that displays money values.</li>
<li><strong>Decimal places</strong> — the number of digits shown after the decimal point.</li>
<li><strong>Column width</strong> — how wide a column is.</li>
<li><strong>Row height</strong> — how tall a row is.</li>
</ul>

<h2>Summary</h2>
<p>Entering, editing and formatting data are basic spreadsheet skills. Proper formatting makes worksheets easy to read and professional. Adjusting columns, rows, number formats and alignment helps you present data clearly to colleagues, customers and committees.</p>
HTML,
            ],
            [
                'title' => 'A1.4.3 Formulas and Functions',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to write simple formulas using up to ten cell addresses, use common functions such as SUM and AVERAGE, build more advanced formulas, and understand why formulas must begin with an equals sign.</p>

<h2>How Formulas Work</h2>
<p>A <strong>formula</strong> is a calculation written in a cell. Every formula begins with an equals sign (=). Without the equals sign, the spreadsheet treats the entry as text. A simple formula might add two cells, for example =A1+B1. You can also use operators such as minus (-), multiply (*) and divide (/).</p>

<h2>Simple Formulas</h2>
<p>A formula can use up to ten cell addresses or more. For a tuck shop, the total sales of an item can be calculated as =B2*C2, where B2 is the quantity and C2 is the price. To add a whole column, use =B2+B3+B4+B5+B6+B7+B8+B9+B10. However, this is slow for long lists. Functions make the job easier.</p>

<h2>Common Functions</h2>
<p>A <strong>function</strong> is a built-in formula with a name. The most common functions are:</p>
<ul>
<li><strong>SUM</strong> — adds a range of cells. Example: =SUM(B2:B10).</li>
<li><strong>AVERAGE</strong> — finds the mean of a range. Example: =AVERAGE(C2:C10).</li>
<li><strong>MAX</strong> — finds the highest value. Example: =MAX(D2:D10).</li>
<li><strong>MIN</strong> — finds the lowest value. Example: =MIN(D2:D10).</li>
<li><strong>COUNT</strong> — counts how many cells contain numbers.</li>
</ul>

<h2>Advanced Formulas</h2>
<p>More advanced formulas combine functions and operators. For example, a shop owner might calculate profit with =D2-E2, where D2 is sales and E2 is costs. To calculate a ten percent discount, use =B2*0.9. To check if stock is low, use =IF(B2<10,"Reorder","OK"). Advanced formulas help automate decisions and reduce manual work.</p>

<h2>Worked Example: Student Marks</h2>
<p>A teacher at a college records student marks in a spreadsheet. Column B has test marks out of 50 and column C has exam marks out of 100. In column D she calculates the total using =B2+C2. In column E she calculates the average using =AVERAGE(B2:C2). In column F she uses an IF formula to show "Pass" if the average is at least 50 and "Fail" if it is lower. The spreadsheet updates automatically when she enters new marks.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a spreadsheet with columns for Item, Quantity, Price and Total.</li>
<li>Write a formula in the Total column to multiply quantity by price.</li>
<li>Use the SUM function to calculate the grand total.</li>
<li>Use the AVERAGE function to find the average price.</li>
<li>Write an IF formula to label items with quantity below five as "Low stock."</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Formula</strong> — a calculation entered in a cell, beginning with =.</li>
<li><strong>Function</strong> — a built-in formula such as SUM or AVERAGE.</li>
<li><strong>Range</strong> — a group of cells, such as A1:A10.</li>
<li><strong>Operator</strong> — a symbol such as +, -, * or / used in calculations.</li>
<li><strong>Cell reference</strong> — the address of a cell used in a formula.</li>
</ul>

<h2>Summary</h2>
<p>Formulas and functions turn a spreadsheet from a static table into a powerful calculator. Simple formulas link cells, while functions such as SUM and AVERAGE perform common calculations quickly. Advanced formulas with IF and percentages can automate business decisions and save many hours of manual work.</p>
HTML,
            ],
            [
                'title' => 'A1.4.4 Sorting, Filtering and Charts',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to sort data alphabetically or numerically, filter a list to show only certain records, create bar, line and pie charts using a chart wizard, and print a worksheet and a chart.</p>

<h2>Sorting Data</h2>
<p><strong>Sorting</strong> rearranges rows into a particular order. You can sort text alphabetically, numbers from smallest to largest, or dates from oldest to newest. To sort, select the data including headers, then choose Data and Sort. Make sure the first row contains headers so the spreadsheet does not sort the headings into the data.</p>

<h2>Filtering Data</h2>
<p><strong>Filtering</strong> hides rows that do not meet certain conditions. For example, you can filter a sales list to show only sales made in March, or only products that sold more than ten units. To filter, select the data and turn on AutoFilter. Drop-down arrows appear in the header row. Click an arrow and choose the values you want to keep.</p>

<h2>Creating Charts</h2>
<p>A chart makes numbers easier to understand. To create a chart, select the data you want to show, then click Insert and choose a chart type. The <strong>Chart Wizard</strong> guides you through the steps. Common chart types are:</p>
<ul>
<li><strong>Bar chart</strong> — compares values across categories.</li>
<li><strong>Line chart</strong> — shows trends over time.</li>
<li><strong>Pie chart</strong> — shows how a total is divided into parts.</li>
</ul>
<p>After creating a chart, you can add a title, labels and a legend. You can also move and resize the chart on the worksheet.</p>

<h2>Printing Worksheets and Charts</h2>
<p>Before printing, use Print Preview to check the layout. You can change the print magnification so the whole sheet fits on one page, or set the print area to include only selected cells. To print a chart alone, click the chart and choose Print. To print both data and chart, make sure both are included in the print area.</p>

<h2>Worked Example: Visualising CDF Spending</h2>
<p>A CDF committee has a worksheet showing amounts spent on roads, water, education and health. The secretary sorts the data from highest to lowest spending. She then creates a pie chart to show what share of the budget each sector received. She adds a title "CDF Disbursement by Sector" and prints the chart for the community meeting. The visual chart makes it easy for villagers to understand how the money was used.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Enter a list of ten students with names and marks.</li>
<li>Sort the list from highest to lowest mark.</li>
<li>Use AutoFilter to show only students who passed.</li>
<li>Create a bar chart comparing the marks.</li>
<li>Print preview and adjust the print area to fit on one page.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Sort</strong> — to arrange data in a particular order.</li>
<li><strong>Filter</strong> — to display only rows that meet certain conditions.</li>
<li><strong>Chart</strong> — a visual representation of data.</li>
<li><strong>Chart Wizard</strong> — a tool that guides you through creating a chart.</li>
<li><strong>Legend</strong> — a key that explains the colours or symbols in a chart.</li>
</ul>

<h2>Summary</h2>
<p>Sorting and filtering help you organise and analyse lists. Charts turn numbers into pictures that are easy to understand. Printing options let you share your work on paper. Together, these skills help you produce useful reports from spreadsheet data.</p>
HTML,
            ],
            [
                'title' => 'A1.4.5 Saving, Printing and Amending a Spreadsheet',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to save a spreadsheet to a folder, use Save As to create a copy with a new name, change print magnification, amend an existing sheet, and review a complete spreadsheet project from start to finish.</p>

<h2>Saving and Saving As</h2>
<p>Spreadsheets should be saved often, especially in areas with unreliable power. Click Save or press Ctrl+S to save changes to the current file. Use <strong>Save As</strong> when you want to create a new file with a different name or format. For example, you might save "Sales_June.xlsx" as "Sales_July.xlsx" and then update the figures. Save As is also used to save a copy as a PDF for sharing.</p>

<h2>Finding a Saved File</h2>
<p>To find a saved file, open File Explorer and navigate to the folder where you saved it. Spreadsheets usually have icons that look like small green grids. If you cannot remember the location, use the search box in File Explorer and type part of the filename. Good folder habits, such as saving all college work in one main folder, make finding files much easier.</p>

<h2>Changing Print Magnification</h2>
<p>Print magnification controls how large the spreadsheet appears on the printed page. If the sheet is too wide, set the scale to fit the page. In Excel, go to Page Layout, Scale to Fit, and choose Width: 1 page. In LibreOffice Calc, use Format, Page Style, Sheet tab, and set Scale to Width: 1 page. This shrinks the content so everything fits on one sheet of paper.</p>

<h2>Amending a Sheet</h2>
<p>Amending means making changes to improve the sheet. You might add new rows, update old prices, correct formulas, or change formatting. When amending, check that formulas still refer to the correct cells. If you insert a row inside a range used by a SUM formula, the formula usually adjusts automatically. If it does not, update the range manually.</p>

<h2>Worked Example: Amending a Tuck-Shop Sheet</h2>
<p>Mrs Banda keeps a monthly tuck-shop sales sheet. At the start of July she opens "Sales_June.xlsx" and uses Save As to create "Sales_July.xlsx." She clears the June quantities and enters July sales. She adds two new products the shop now sells. She updates the prices because sugar has become more expensive. She checks that the SUM formula still covers all rows. Finally she prints a copy for her husband to review.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a simple budget spreadsheet.</li>
<li>Save it in your Documents folder with a clear name.</li>
<li>Use Save As to create a copy with a different name.</li>
<li>Change the print magnification to fit the sheet on one page.</li>
<li>Amend the budget by adding a new expense category and updating the total formula.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Save As</strong> — saves the current file under a new name or format.</li>
<li><strong>Print magnification</strong> — the scaling applied when printing.</li>
<li><strong>Amend</strong> — to change or improve a document.</li>
<li><strong>Scale to fit</strong> — reduces or enlarges content to fit the printed page.</li>
<li><strong>Formula range</strong> — the group of cells included in a formula.</li>
</ul>

<h2>Summary</h2>
<p>Saving, printing and amending are the final steps in using a spreadsheet. Save As lets you create versions, print scaling helps you fit data on paper, and careful amendment keeps formulas correct. These skills allow you to manage budgets, sales records and reports throughout the year.</p>
HTML,
            ],
        ];
    }

    private function module4Quiz(): array
    {
        return [
            'title' => 'A1.4 Quiz: Using Spreadsheet Package Effectively',
            'description' => 'Test your understanding of worksheets, formulas, functions, sorting, filtering and charts.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What must every formula begin with?',
                    'explanation' => 'A formula must begin with an equals sign so the spreadsheet knows to calculate.',
                    'options' => [
                        ['text' => 'A letter', 'is_correct' => false],
                        ['text' => 'A number', 'is_correct' => false],
                        ['text' => 'An equals sign', 'is_correct' => true],
                        ['text' => 'A bracket', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which function adds a range of cells?',
                    'explanation' => 'The SUM function adds all the numbers in a range.',
                    'options' => [
                        ['text' => 'ADD', 'is_correct' => false],
                        ['text' => 'TOTAL', 'is_correct' => false],
                        ['text' => 'SUM', 'is_correct' => true],
                        ['text' => 'PLUS', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A workbook can contain many worksheets.',
                    'explanation' => 'A workbook is a file that can hold multiple worksheets.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which chart type is best for showing parts of a whole?',
                    'explanation' => 'A pie chart shows how a total is divided into parts.',
                    'options' => [
                        ['text' => 'Bar chart', 'is_correct' => false],
                        ['text' => 'Line chart', 'is_correct' => false],
                        ['text' => 'Pie chart', 'is_correct' => true],
                        ['text' => 'Scatter chart', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Filtering permanently deletes rows that do not match the criteria.',
                    'explanation' => 'Filtering only hides rows; it does not delete them.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What happens when you double-click the line between two column headers?',
                    'explanation' => 'Double-clicking the column boundary auto-fits the column width.',
                    'options' => [
                        ['text' => 'The column is deleted', 'is_correct' => false],
                        ['text' => 'The column width adjusts to fit the widest entry', 'is_correct' => true],
                        ['text' => 'A new column is inserted', 'is_correct' => false],
                        ['text' => 'The data is sorted', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Module 5: A1.5 Using PowerPoint Package Effectively
    // ------------------------------------------------------------------

    private function module5Lessons(): array
    {
        return [
            [
                'title' => 'A1.5.1 Getting Started with Presentation Software',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to start Microsoft PowerPoint, identify the ribbon and Quick Access Toolbar, understand basic presentation principles, and use Help to find answers to questions.</p>

<h2>What Is Presentation Software?</h2>
<p>Presentation software is used to create slide shows. A presentation is a series of slides that explain a topic, support a speech, or teach a lesson. Microsoft PowerPoint is the most common presentation program, but LibreOffice Impress and Google Slides are also popular. The skills learned in one program transfer to the others.</p>

<h2>Starting PowerPoint</h2>
<p>Open PowerPoint from the Start menu or desktop. When it starts, you may see a blank presentation or a choice of templates. A <strong>template</strong> is a ready-made design that includes colours, fonts and layouts. Choosing a suitable template saves time and gives the presentation a professional look.</p>

<h2>The PowerPoint Window</h2>
<p>The main areas of the PowerPoint window are:</p>
<ul>
<li><strong>Slide pane</strong> — the large area where you edit the current slide.</li>
<li><strong>Thumbnail pane</strong> — shows small pictures of all slides on the left.</li>
<li><strong>Ribbon</strong> — contains tabs such as Home, Insert, Design, Transitions and Animations.</li>
<li><strong>Quick Access Toolbar</strong> — small shortcuts at the top for Save, Undo and Redo.</li>
<li><strong>Notes pane</strong> — area below the slide where the presenter can write speaker notes.</li>
</ul>

<h2>Presentation Principles</h2>
<p>A good presentation is clear, brief and visually appealing. Each slide should focus on one main idea. Use short bullet points instead of long paragraphs. Choose colours that are easy to read, such as dark text on a light background. Avoid too many animations because they distract the audience.</p>

<h2>Worked Example: Planning a Presentation</h2>
<p>A student at Edutrack Computer Training College is asked to present "The Importance of Computers in Small Business." She plans five slides: a title slide, what computers can do, an example of a Zambian business, benefits to the community, and a thank-you slide. She chooses a simple template with a blue background. She writes short points and adds one picture per slide. Her presentation is easy to follow and looks professional.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open Microsoft PowerPoint or LibreOffice Impress.</li>
<li>Identify the slide pane, thumbnail pane and ribbon.</li>
<li>Choose a template and create a title slide.</li>
<li>Type a title and your name in the placeholders.</li>
<li>Save the presentation with a clear filename.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Presentation</strong> — a series of slides used to explain or support a topic.</li>
<li><strong>Slide</strong> — one page of a presentation.</li>
<li><strong>Template</strong> — a ready-made design with colours, fonts and layouts.</li>
<li><strong>Ribbon</strong> — the toolbar with tabs and commands.</li>
<li><strong>Placeholder</strong> — a box on a slide where you type text or insert objects.</li>
</ul>

<h2>Summary</h2>
<p>Presentation software helps you create slides that support talks and lessons. Knowing the window layout, templates and basic principles is the first step. Good presentations are clear, short and focused on one idea per slide.</p>
HTML,
            ],
            [
                'title' => 'A1.5.2 Handling Slides and Views',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to add, insert, delete and rearrange slides, switch between views, apply design templates, choose slide layouts, change slide backgrounds, and save, close and open presentations.</p>

<h2>Views in PowerPoint</h2>
<p>PowerPoint has several views that help you work in different ways:</p>
<ul>
<li><strong>Normal view</strong> — the main editing view with slide and thumbnail panes.</li>
<li><strong>Slide Sorter view</strong> — shows all slides as thumbnails, useful for reordering.</li>
<li><strong>Slide Show view</strong> — displays the presentation full screen as the audience will see it.</li>
<li><strong>Notes Page view</strong> — shows one slide with space for speaker notes.</li>
</ul>

<h2>Adding and Deleting Slides</h2>
<p>To add a new slide, click New Slide on the Home tab. Choose a layout such as Title Slide, Title and Content, or Two Content. To delete a slide, right-click its thumbnail and choose Delete Slide. To move a slide, drag it to a new position in the thumbnail pane or in Slide Sorter view.</p>

<h2>Design Templates and Backgrounds</h2>
<p>A <strong>design template</strong> sets the colours, fonts and background for the whole presentation. To apply a template, click the Design tab and choose a theme. You can also change the background of one or all slides by clicking Background Styles. Choose colours and designs that match the topic and are easy to read.</p>

<h2>Saving, Closing and Opening</h2>
<p>Save a presentation by pressing Ctrl+S. PowerPoint's default format is .pptx. To share with someone who does not have PowerPoint, save as a PDF. Close a presentation by clicking the X or choosing File, Close. Open an existing presentation from File, Open or by double-clicking the file in File Explorer.</p>

<h2>Worked Example: Reorganising a Lesson Presentation</h2>
<p>A teacher has created a ten-slide lesson but realises the topic about networks should come before the topic about the internet. She switches to Slide Sorter view, clicks and drags the network slide to an earlier position, and then returns to Normal view. She applies a new design template that matches the college colours. She deletes a duplicate slide and adds a blank slide at the end for questions.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a new presentation with five slides.</li>
<li>Apply a design template.</li>
<li>Change the layout of slide 2 to Title and Content.</li>
<li>Switch to Slide Sorter view and reorder the slides.</li>
<li>Delete one slide and add a new one.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Normal view</strong> — the main view for editing individual slides.</li>
<li><strong>Slide Sorter view</strong> — a view that shows all slides as thumbnails.</li>
<li><strong>Slide Show view</strong> — the full-screen view used when presenting.</li>
<li><strong>Design template</strong> — a set of colours, fonts and backgrounds for a presentation.</li>
<li><strong>Layout</strong> — the arrangement of placeholders on a slide.</li>
</ul>

<h2>Summary</h2>
<p>Managing slides and views is essential for building a well-organised presentation. Templates, layouts and backgrounds control the appearance, while views help you edit, reorder and present. Saving in the correct format ensures your presentation can be opened and viewed by others.</p>
HTML,
            ],
            [
                'title' => 'A1.5.3 Inserting Objects into Slides',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to insert organisation charts, pictures, charts, shapes and drawing objects into slides, move, resize, copy, rotate and flip objects, arrange and distribute objects, and import images from other programs.</p>

<h2>Inserting Objects</h2>
<p>Objects make slides more interesting and informative. On the Insert tab you can add:</p>
<ul>
<li><strong>Tables</strong> — for organised data.</li>
<li><strong>Pictures</strong> — photographs or clip art.</li>
<li><strong>Charts</strong> — graphs based on spreadsheet data.</li>
<li><strong>Shapes</strong> — lines, arrows, rectangles and other drawing objects.</li>
<li><strong>SmartArt</strong> — diagrams such as organisation charts and process flows.</li>
</ul>

<h2>Organisation Charts</h2>
<p>An <strong>organisation chart</strong> shows the structure of a group or business. To insert one, choose SmartArt and select a hierarchy layout. Type names and titles into the boxes. This is useful for showing the management structure of a company, a church committee or a CDF project team.</p>

<h2>Moving, Resizing and Copying Objects</h2>
<p>To move an object, click and drag it. To resize, drag the small circles called <strong>handles</strong> at the corners or edges. Hold Shift while dragging a corner to keep the proportions. To copy an object, press Ctrl+C and then Ctrl+V. To delete, select the object and press Delete.</p>

<h2>Rotate, Flip, Arrange and Distribute</h2>
<p>To rotate an object, drag the circular arrow above it. To flip it horizontally or vertically, use the Rotate options on the Format tab. To arrange objects, use Bring to Front or Send to Back so the correct object appears on top. To distribute objects evenly, select them and choose Distribute Horizontally or Distribute Vertically.</p>

<h2>Worked Example: Creating a School Org Chart</h2>
<p>A head teacher needs a slide showing the school management team. She inserts a SmartArt organisation chart. At the top she types "Head Teacher." Below she adds "Deputy Head", "Bursar" and "Heads of Department." She adds boxes for Mathematics, Science and Languages under Heads of Department. She changes the colours to match the school uniform and resizes the chart to fit the slide.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Insert a picture into a slide and resize it.</li>
<li>Draw three shapes and arrange them using Send to Front and Send to Back.</li>
<li>Create an organisation chart for a small business or church group.</li>
<li>Rotate one object and flip another.</li>
<li>Copy an object and paste it onto another slide.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Object</strong> — any item inserted onto a slide, such as a picture or shape.</li>
<li><strong>Handle</strong> — a small circle used to resize or rotate an object.</li>
<li><strong>SmartArt</strong> — a feature for creating diagrams and charts.</li>
<li><strong>Organisation chart</strong> — a diagram showing the structure of an organisation.</li>
<li><strong>Distribute</strong> — to space objects evenly.</li>
</ul>

<h2>Summary</h2>
<p>Objects such as pictures, charts, shapes and organisation charts make presentations more informative. Moving, resizing, rotating and arranging objects gives you control over the slide layout. These tools help you create slides that clearly explain your message.</p>
HTML,
            ],
            [
                'title' => 'A1.5.4 Formatting Slides and Animation',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to apply text effects and bullets, use undo and redo, align and space objects, cut, copy and paste slide content, apply animation schemes and custom animations, insert headers and footers, and use the slide master.</p>

<h2>Text Effects and Bullets</h2>
<p>Text on slides should be easy to read. Use bold for headings and bullet points for lists. Avoid long sentences. To change bullets, select the list and choose a bullet style from the Home tab. You can also use numbering for steps that follow an order. Text effects such as shadow or glow should be used sparingly.</p>

<h2>Undo, Redo, Cut, Copy and Paste</h2>
<p>These standard editing commands work in PowerPoint just as they do in Word. Ctrl+Z undoes the last action. Ctrl+Y redoes it. Ctrl+X cuts, Ctrl+C copies and Ctrl+V pastes. You can paste between slides and even between programs. For example, you can copy a chart from Excel and paste it into a PowerPoint slide.</p>

<h2>Alignment and Spacing</h2>
<p>Aligned objects look neat and professional. Select several objects and use Align Left, Align Centre or Align Top from the Format tab. Use the grid and guides to position objects accurately. Even spacing makes the slide easier to read and more attractive.</p>

<h2>Animations and Transitions</h2>
<p><strong>Transitions</strong> control how one slide moves to the next, such as fade or wipe. <strong>Animations</strong> control how objects appear on a slide, such as flying in or appearing one by one. Use simple effects and avoid too many animations, which can distract the audience. To apply an animation, select an object and choose an effect from the Animations tab.</p>

<h2>Headers, Footers and Slide Master</h2>
<p>A <strong>header</strong> or <strong>footer</strong> can show the date, slide number or footer text on every slide. To add one, choose Insert, Header & Footer. The <strong>Slide Master</strong> controls the design of all slides. Changes made in Slide Master view apply to every slide in the presentation. This is useful for adding a logo or changing the font for the whole presentation.</p>

<h2>Worked Example: Formatting a Community Project Presentation</h2>
<p>A youth group presents a CDF water project to the council. They use Slide Master to add the group logo to every slide. They format headings in bold and use bullet points for benefits. They add a footer with the date and slide numbers. They apply a fade transition between slides and a simple appear animation for each bullet. The presentation looks consistent and professional.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Format a list with different bullet styles.</li>
<li>Align three objects to the left of a slide.</li>
<li>Add a footer with slide numbers to all slides.</li>
<li>Apply a transition between slides.</li>
<li>Apply a simple animation to a text box.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Transition</strong> — the effect when moving from one slide to the next.</li>
<li><strong>Animation</strong> — the way an object appears or moves on a slide.</li>
<li><strong>Slide Master</strong> — the master slide that controls the design of all slides.</li>
<li><strong>Footer</strong> — text that appears at the bottom of every slide.</li>
<li><strong>Alignment</strong> — positioning objects in a straight line.</li>
</ul>

<h2>Summary</h2>
<p>Formatting slides includes text effects, alignment, bullets and the use of Slide Master for consistency. Animations and transitions add interest but should be used carefully. Headers and footers provide useful information on every slide.</p>
HTML,
            ],
            [
                'title' => 'A1.5.5 Presenting, Printing and Output Formats',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to set up a slide show, run a presentation, choose output formats, print slides and handouts, and prepare effectively to speak in front of an audience.</p>

<h2>Setting Up the Slide Show</h2>
<p>Before presenting, check the slide show settings. On the Slide Show tab, choose Set Up Slide Show. You can decide whether to advance slides manually or automatically, whether to loop the show, and whether to show it on a full screen or in a window. For most classroom or business presentations, manual advance is best because the speaker controls the pace.</p>

<h2>Running the Slide Show</h2>
<p>To start the slide show from the beginning, press F5. To start from the current slide, press Shift+F5. During the show, click the mouse or press the spacebar to move forward. Press Escape to stop. Right-click during the show to see options such as Next, Previous, Pointer and Screen. The pointer can be used to draw attention to important points.</p>

<h2>Output Formats</h2>
<p>PowerPoint presentations can be saved in several formats. The default .pptx format preserves editing. The .pdf format is useful for sharing because it looks the same on any device. You can also save slides as pictures in .jpg or .png format. Some presenters create a video file so the presentation can play automatically.</p>

<h2>Printing Slides and Handouts</h2>
<p>To print, choose File, Print. You can print full slides, notes pages, or handouts with multiple slides per page. Handouts are useful for the audience because they provide space to write notes. Choose the number of slides per page, such as three or six, depending on how much detail the audience needs.</p>

<h2>Worked Example: Presenting at a PTA Meeting</h2>
<p>A school bursar prepares a presentation about fee payments for a PTA meeting. She sets the slide show to advance manually. She prints handouts with three slides per page so parents can take notes. She practises the presentation twice and prepares a few speaker notes. During the meeting she speaks clearly, points to key figures on the slides, and answers questions from parents.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Set up a slide show to advance manually.</li>
<li>Run the presentation from the beginning.</li>
<li>Practise using the pointer during the slide show.</li>
<li>Print handouts with three slides per page.</li>
<li>Save the presentation as a PDF.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Slide show</strong> — the full-screen display of a presentation.</li>
<li><strong>Handout</strong> — a printed copy of slides for the audience.</li>
<li><strong>Manual advance</strong> — moving to the next slide by clicking or pressing a key.</li>
<li><strong>Output format</strong> — the file type used to save or share a presentation.</li>
<li><strong>Speaker notes</strong> — reminders for the presenter, visible only in Notes view.</li>
</ul>

<h2>Summary</h2>
<p>Presenting well requires more than good slides. Setting up the slide show, practising, using output formats and printing handouts all help the audience understand your message. Speaking clearly and engaging with the audience completes a successful presentation.</p>
HTML,
            ],
        ];
    }

    private function module5Quiz(): array
    {
        return [
            'title' => 'A1.5 Quiz: Using PowerPoint Package Effectively',
            'description' => 'Test your understanding of slide handling, objects, formatting, animation and presenting.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which view shows all slides as thumbnails and is useful for reordering?',
                    'explanation' => 'Slide Sorter view displays all slides as thumbnails for easy reordering.',
                    'options' => [
                        ['text' => 'Normal view', 'is_correct' => false],
                        ['text' => 'Slide Sorter view', 'is_correct' => true],
                        ['text' => 'Notes Page view', 'is_correct' => false],
                        ['text' => 'Slide Show view', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the difference between a transition and an animation?',
                    'explanation' => 'A transition affects how slides change; an animation affects how objects appear on a slide.',
                    'options' => [
                        ['text' => 'They are the same thing', 'is_correct' => false],
                        ['text' => 'Transitions move objects; animations change slides', 'is_correct' => false],
                        ['text' => 'Transitions change slides; animations affect objects', 'is_correct' => true],
                        ['text' => 'Transitions add sound; animations add pictures', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'The Slide Master controls the design of all slides in a presentation.',
                    'explanation' => 'Changes made in Slide Master view apply to all slides using that master.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which key starts a slide show from the beginning?',
                    'explanation' => 'Pressing F5 starts the slide show from the first slide.',
                    'options' => [
                        ['text' => 'F1', 'is_correct' => false],
                        ['text' => 'F5', 'is_correct' => true],
                        ['text' => 'Esc', 'is_correct' => false],
                        ['text' => 'Ctrl+P', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A presentation can only be saved as a .pptx file.',
                    'explanation' => 'Presentations can be saved in many formats, including PDF and image formats.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which object would you use to show the structure of a company?',
                    'explanation' => 'An organisation chart shows hierarchy and structure.',
                    'options' => [
                        ['text' => 'Pie chart', 'is_correct' => false],
                        ['text' => 'Organisation chart', 'is_correct' => true],
                        ['text' => 'Bar chart', 'is_correct' => false],
                        ['text' => 'Table', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Module 6: A1.6 Using Desktop Publishing Packages Effectively
    // ------------------------------------------------------------------

    private function module6Lessons(): array
    {
        return [
            [
                'title' => 'A1.6.1 Introduction to Desktop Publishing',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to define desktop publishing, name two types of publication, explain the difference between desktop publishing and word processing, and identify common DTP software used in Zambia.</p>

<h2>What Is Desktop Publishing?</h2>
<p><strong>Desktop publishing (DTP)</strong> is the creation of documents that combine text and graphics in a visually appealing layout. DTP is used for flyers, brochures, newsletters, notices, calendars, business cards, menus, certificates and posters. While word processors are best for letters and reports, DTP programs give more control over the placement of text and images on a page.</p>

<h2>Types of Publications</h2>
<p>Publications can be divided into two main types. <strong>Print publications</strong> are designed to be printed on paper, such as brochures, church bulletins and shop flyers. <strong>Digital publications</strong> are designed to be viewed on a screen, such as PDF newsletters, social media graphics and online menus. Many documents today are created once and shared both on paper and online.</p>

<h2>DTP vs Word Processing</h2>
<p>Word processors focus on typing and formatting text in a continuous flow. They are ideal for reports, essays and letters. DTP programs focus on page layout. They allow you to place text boxes and pictures anywhere on the page, wrap text around images, and work with multiple pages at once. For a simple letter, use a word processor. For a poster or brochure, use a DTP program.</p>

<h2>Common DTP Software</h2>
<p>In Zambia, Microsoft Publisher is a common DTP program found in offices and colleges. LibreOffice Draw and Scribus are free alternatives. Some people also use PowerPoint or Canva to create simple publications because these tools are easy to learn. For professional printing, Adobe InDesign is used by designers and print shops.</p>

<h2>Worked Example: Choosing the Right Tool</h2>
<p>A church in Kalomo needs two documents. For a letter to the bishop, the secretary uses Microsoft Word because it is mainly text. For a colourful flyer advertising a fundraising dinner, she uses Microsoft Publisher because she needs to place pictures, text boxes and a logo freely on the page. Choosing the right tool makes each task easier and produces a better result.</p>

<h2>Try It Yourself</h2>
<ol>
<li>List five documents that are better created with DTP than with a word processor.</li>
<li>Explain the difference between a print publication and a digital publication.</li>
<li>Open Microsoft Publisher or LibreOffice Draw and identify the page area and toolbars.</li>
<li>Think of a local event and decide whether it needs a flyer, a brochure or a notice.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Desktop publishing (DTP)</strong> — creating documents that combine text and graphics in a designed layout.</li>
<li><strong>Publication</strong> — a finished document such as a flyer, brochure or newsletter.</li>
<li><strong>Layout</strong> — the arrangement of text and images on a page.</li>
<li><strong>Text box</strong> — a container for text that can be placed anywhere on a page.</li>
<li><strong>Print publication</strong> — a document designed to be printed on paper.</li>
</ul>

<h2>Summary</h2>
<p>Desktop publishing is used to create visually rich documents such as flyers, brochures and newsletters. It differs from word processing because it gives precise control over layout. Choosing the right tool for each task helps you produce professional publications for church, business or community events.</p>
HTML,
            ],
            [
                'title' => 'A1.6.2 Templates and Pre-Designed Layouts',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to use built-in templates, choose colour palettes for outlines and fills, create calendars, notices, flyers, brochures, newsletters and resumes, and retrieve and edit pre-designed graphics.</p>

<h2>Using Templates</h2>
<p>A <strong>template</strong> is a ready-made design that you can customise. DTP software includes templates for flyers, brochures, business cards, calendars and newsletters. Using a template saves time because the layout, colours and fonts are already chosen. You only replace the placeholder text and pictures with your own content.</p>

<h2>Working with Colours</h2>
<p>Colours should be chosen carefully. Use a small number of colours that work well together. Most DTP programs have colour palettes that show matching colours. The <strong>fill</strong> is the colour inside a shape, while the <strong>outline</strong> is the colour around the edge. For a professional look, use dark text on a light background and add one or two accent colours for headings and borders.</p>

<h2>Creating Common Publications</h2>
<p>With templates and simple tools you can create many publications:</p>
<ul>
<li><strong>Calendar</strong> — a grid of days and dates, useful for churches and schools.</li>
<li><strong>Notice</strong> — a single page with a clear headline and short message.</li>
<li><strong>Flyer</strong> — a single sheet advertising an event or product.</li>
<li><strong>Brochure</strong> — a folded sheet with information on several panels.</li>
<li><strong>Newsletter</strong> — a multi-page document with articles and pictures.</li>
<li><strong>Resume</strong> — a formatted summary of a person's education and work experience.</li>
</ul>

<h2>Worked Example: Creating a Church Calendar</h2>
<p>A church administrator in Livingstone needs a calendar for the year. She opens Microsoft Publisher and chooses a calendar template. She changes the colour scheme to match the church colours. She types the dates and adds events such as Easter, youth meetings and fundraising days. She inserts small clip-art images for decoration. She prints copies for members and saves the file as a PDF for sharing on WhatsApp.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open a DTP program and choose a flyer template.</li>
<li>Replace the placeholder text with information about a college event.</li>
<li>Change the fill and outline colours of a shape.</li>
<li>Save the publication as a PDF.</li>
<li>Create a simple one-page notice using a blank page instead of a template.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Template</strong> — a ready-made design that can be customised.</li>
<li><strong>Colour palette</strong> — a set of colours that work well together.</li>
<li><strong>Fill</strong> — the colour or pattern inside a shape.</li>
<li><strong>Outline</strong> — the line around the edge of a shape or text box.</li>
<li><strong>Brochure</strong> — a folded document with several panels of information.</li>
</ul>

<h2>Summary</h2>
<p>Templates and pre-designed layouts make desktop publishing faster and easier. Choosing good colours and adapting templates for calendars, notices, flyers and newsletters helps you produce attractive publications. Customising templates while keeping a consistent style is a key DTP skill.</p>
HTML,
            ],
            [
                'title' => 'A1.6.3 Elementary Graphics and Text Frames',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to use basic graphic elements, create and edit frames for text and graphics, edit a page layout, and input or import text into a DTP document.</p>

<h2>Basic Graphic Elements</h2>
<p>DTP programs provide tools for drawing simple shapes such as lines, rectangles, circles, arrows and stars. These basic elements can be combined to create logos, borders and simple illustrations. Each element can be resized, rotated, coloured and layered. Learning to control these elements is the foundation of graphic design.</p>

<h2>Frames for Text and Graphics</h2>
<p>In DTP, text and pictures are usually placed in <strong>frames</strong>. A text frame holds paragraphs and can be linked to another text frame so that overflow text continues on the next page. A picture frame holds an image and can be cropped or resized. Frames can be moved independently, which gives more layout freedom than a word processor.</p>

<h2>Editing Layout</h2>
<p>Editing layout means arranging frames, shapes and images on the page. You can drag objects to new positions, resize them, align them with guides, and layer them in front of or behind other objects. A good layout has a clear visual order. The most important information should be the largest or most colourful. White space, which is empty space on the page, helps the design breathe.</p>

<h2>Importing Text</h2>
<p>Instead of typing long text directly into a DTP program, you can import it from a word processor. Save the text as a .docx or .rtf file, then use the Insert or Place command in the DTP program. This is useful when a newsletter article has already been written in Word. After importing, you can format the text and adjust the frame size to fit the layout.</p>

<h2>Worked Example: Designing a Business Brochure</h2>
<p>A new restaurant in Lusaka wants a tri-fold brochure. The owner writes the menu text in Word and saves it. In Publisher she creates a tri-fold brochure from a template. She imports the menu text into a text frame on the inside panel. She adds picture frames for photos of the dishes. She draws a rectangle as a background for the restaurant name and changes the fill to gold. She aligns the frames with the page guides and prints a test copy.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Draw five different shapes and change their colours.</li>
<li>Create a text frame and type a short paragraph.</li>
<li>Import text from a Word document into a DTP text frame.</li>
<li>Arrange text and picture frames on a page using alignment guides.</li>
<li>Add a border around a picture frame.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Frame</strong> — a container for text or graphics in a DTP program.</li>
<li><strong>Text frame</strong> — a box that holds text and can be linked to other frames.</li>
<li><strong>Picture frame</strong> — a box that holds an image.</li>
<li><strong>Layout</strong> — the arrangement of text, images and other elements on a page.</li>
<li><strong>White space</strong> — empty space that helps a design look clean and organised.</li>
</ul>

<h2>Summary</h2>
<p>Basic shapes, frames and layout tools are the building blocks of desktop publishing. Text frames and picture frames give flexibility that word processors cannot match. Good layout uses alignment, colour and white space to guide the reader's eye through the publication.</p>
HTML,
            ],
            [
                'title' => 'A1.6.4 Charts, Diagrams, Text Wrapping and Importing',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to edit chart and diagram templates, wrap text around graphics, import text and graphics from other programs, and explain how productivity tools complement each other.</p>

<h2>Charts and Diagrams in DTP</h2>
<p>Charts and diagrams help readers understand information quickly. DTP programs allow you to insert charts similar to those in spreadsheets. You can edit the data behind the chart and change the chart type. Diagrams such as flowcharts and organisation charts can also be inserted and customised. Always add a title and labels so the chart is clear without extra explanation.</p>

<h2>Text Wrapping Around Graphics</h2>
<p>When a picture is placed in the middle of text, the text can either stay above and below the picture or flow around it. <strong>Text wrapping</strong> controls how text behaves next to a picture. Common wrapping options include:</p>
<ul>
<li><strong>Square</strong> — text wraps around the rectangular edge of the picture.</li>
<li><strong>Tight</strong> — text wraps closely around the actual shape of the picture.</li>
<li><strong>Behind text</strong> — the picture is placed behind the text.</li>
<li><strong>In front of text</strong> — the picture covers the text.</li>
</ul>

<h2>Importing Text and Graphics</h2>
<p>DTP becomes more powerful when you import content from other programs. Text can come from a word processor. Pictures can come from a digital camera, scanner or image file. Charts can come from a spreadsheet. This is why it is important to learn several productivity tools. Each tool does one job well, and together they produce professional publications.</p>

<h2>How Productivity Tools Complement Each Other</h2>
<p>Word processors are best for text, spreadsheets for calculations, presentation software for slides and DTP for page layout. A real project often uses all four. For example, a CDF committee might write a report in Word, create a budget chart in Excel, present findings in PowerPoint, and design a printed summary brochure in Publisher.</p>

<h2>Worked Example: A Zambian Flier for a Community Clean-Up</h2>
<p>A youth group in Ndola designs a flier for a community clean-up day. They write the announcement in Word and import it into Publisher. They take a photo of the area with a phone and import the image. They insert a small bar chart showing how many bags of rubbish were collected last year. They wrap the text around the photo and add a coloured banner at the top. The finished flier is saved as a PDF and shared on social media.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Insert a picture and practise different text wrapping options.</li>
<li>Import a chart or diagram from another program into a DTP document.</li>
<li>Create a flier that uses both imported text and an imported picture.</li>
<li>Explain to a classmate why DTP and word processing are both needed.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Text wrapping</strong> — the way text flows around a picture or object.</li>
<li><strong>Import</strong> — to bring content from another file or program into the current document.</li>
<li><strong>Chart</strong> — a visual representation of data.</li>
<li><strong>Diagram</strong> — a drawing that explains information or a process.</li>
<li><strong>Productivity tools</strong> — software such as word processors, spreadsheets and DTP programs.</li>
</ul>

<h2>Summary</h2>
<p>Charts, diagrams and text wrapping improve the appearance and clarity of DTP documents. Importing content from word processors, spreadsheets and cameras saves time and combines the strengths of different productivity tools. Understanding how these tools work together is essential for modern office work.</p>
HTML,
            ],
            [
                'title' => 'A1.6.5 Clip Art, Colours and Fonts',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to add, edit, resize and crop clip art and pre-designed graphics, integrate text with images, and apply the three elements of fonts: typeface, style and size.</p>

<h2>Using Clip Art and Pre-Designed Graphics</h2>
<p><strong>Clip art</strong> is a collection of ready-made pictures that can be added to publications. Modern DTP programs include online libraries of icons, illustrations and photographs. Pre-designed graphics such as banners, borders and backgrounds can also be downloaded. When using these graphics, choose images that match the topic and audience. Avoid overloading a page with too many pictures.</p>

<h2>Editing, Resizing and Cropping</h2>
<p>After inserting a picture, you can make it larger or smaller by dragging the corner handles. Hold Shift while dragging to keep the original proportions. <strong>Cropping</strong> removes unwanted parts of a picture. Most DTP programs have a Crop tool that lets you drag the edges inward. Cropping is useful when you want to focus on one part of a photograph, such as a person's face or a product.</p>

<h2>Integrating Text and Images</h2>
<p>Text and images should work together. Place captions near pictures to explain them. Use headings to introduce sections. Make sure text is readable over any background image by adding a solid colour box behind the text. Keep a consistent style for captions and headings throughout the publication.</p>

<h2>The Three Elements of Fonts</h2>
<p>A font has three main characteristics:</p>
<ul>
<li><strong>Typeface</strong> — the design of the letters, such as Arial, Times New Roman or Calibri.</li>
<li><strong>Style</strong> — variations such as regular, bold or italic.</li>
<li><strong>Size</strong> — how large the letters are, measured in points.</li>
</ul>
<p>Choose no more than two typefaces for one publication. Use a clear typeface for body text and a bolder or more decorative one for headings. Make sure the size is large enough to read easily.</p>

<h2>Worked Example: A Church Bulletin</h2>
<p>A church in Kalomo produces a weekly bulletin. The secretary uses a two-column layout in Publisher. She imports the order of service from Word and adds clip art of a cross. She crops the cross image to focus on the centre. She sets headings in a bold 14-point typeface and body text in an 11-point typeface. She uses only black and one accent colour to keep printing costs low. The bulletin is photocopied and handed out at the door.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Insert a clip art image or icon into a publication.</li>
<li>Resize the image and crop it to remove unwanted parts.</li>
<li>Add a caption below the image.</li>
<li>Apply three different font sizes to a heading, subheading and body text.</li>
<li>Choose two typefaces and use one for headings and one for body text.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Clip art</strong> — ready-made pictures for use in documents.</li>
<li><strong>Crop</strong> — to cut off part of a picture.</li>
<li><strong>Typeface</strong> — the design or family of letters.</li>
<li><strong>Font style</strong> — variations such as bold or italic.</li>
<li><strong>Font size</strong> — the height of letters, measured in points.</li>
</ul>

<h2>Summary</h2>
<p>Clip art, careful editing and good font choices bring a publication to life. Resizing and cropping focus attention on the right parts of an image. Choosing typeface, style and size consistently creates a professional and readable design. These finishing touches make the difference between an amateur publication and a polished one.</p>
HTML,
            ],
        ];
    }

    private function module6Quiz(): array
    {
        return [
            'title' => 'A1.6 Quiz: Using Desktop Publishing Packages Effectively',
            'description' => 'Test your understanding of DTP concepts, templates, frames, graphics, text wrapping and fonts.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which task is best suited to desktop publishing rather than word processing?',
                    'explanation' => 'Desktop publishing is designed for page layouts that combine text and graphics, such as flyers.',
                    'options' => [
                        ['text' => 'Writing a business letter', 'is_correct' => false],
                        ['text' => 'Creating a flyer', 'is_correct' => true],
                        ['text' => 'Typing an essay', 'is_correct' => false],
                        ['text' => 'Writing a memo', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is a frame in desktop publishing?',
                    'explanation' => 'A frame is a container for text or graphics on a page.',
                    'options' => [
                        ['text' => 'A type of font', 'is_correct' => false],
                        ['text' => 'A container for text or graphics', 'is_correct' => true],
                        ['text' => 'A printing command', 'is_correct' => false],
                        ['text' => 'A colour palette', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Text wrapping controls how text flows around a picture.',
                    'explanation' => 'Text wrapping determines the relationship between text and nearby graphics.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is one of the three font elements?',
                    'explanation' => 'Typeface, style and size are the three main elements of a font.',
                    'options' => [
                        ['text' => 'Colour', 'is_correct' => false],
                        ['text' => 'Typeface', 'is_correct' => true],
                        ['text' => 'Shape', 'is_correct' => false],
                        ['text' => 'Border', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Cropping a picture makes it larger.',
                    'explanation' => 'Cropping removes part of a picture; it does not enlarge it.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which document is usually folded into several panels?',
                    'explanation' => 'A brochure is a folded document with multiple panels.',
                    'options' => [
                        ['text' => 'Flyer', 'is_correct' => false],
                        ['text' => 'Notice', 'is_correct' => false],
                        ['text' => 'Brochure', 'is_correct' => true],
                        ['text' => 'Calendar', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Module 7: A1.7 Using Networks & the Internet
    // ------------------------------------------------------------------

    private function module7Lessons(): array
    {
        return [
            [
                'title' => 'A1.7.1 Understanding Computer Networks',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what a computer network is, compare wired and wireless networks, distinguish between a LAN and a WAN, and describe common networking devices such as routers, switches and modems.</p>

<h2>What Is a Network?</h2>
<p>A <strong>computer network</strong> is two or more computers connected together so they can share resources. Resources can include files, printers, internet access and software. Networks allow people to communicate, share information and work together even when they are in different places.</p>

<h2>Wired vs Wireless Networks</h2>
<p>A <strong>wired network</strong> uses cables to connect devices. Ethernet cables are the most common type. Wired networks are usually faster and more stable than wireless networks, but they limit where devices can be used. A <strong>wireless network</strong> uses radio waves, usually through Wi-Fi. Wireless networks are convenient because laptops, phones and tablets can connect without cables. In Zambia, homes, offices and cafés commonly use Wi-Fi routers that also provide wired ports.</p>

<h2>LAN and WAN</h2>
<p>A <strong>Local Area Network (LAN)</strong> covers a small area such as a home, school or office building. All computers in the Edutrack Computer Training College lab might be on one LAN. A <strong>Wide Area Network (WAN)</strong> covers a large area, such as a city, country or the whole world. The internet is the largest WAN. Banks, mobile phone companies and government departments use WANs to connect offices across Zambia.</p>

<h2>Networking Devices</h2>
<p>Several devices make networks work:</p>
<ul>
<li><strong>Router</strong> — directs traffic between networks, such as between a home network and the internet.</li>
<li><strong>Switch</strong> — connects many devices on the same LAN.</li>
<li><strong>Modem</strong> — connects a network to the internet through a telephone or fibre line.</li>
<li><strong>Access point</strong> — creates a wireless area for Wi-Fi devices.</li>
<li><strong>Network interface card</strong> — the hardware inside a computer that allows it to connect to a network.</li>
</ul>

<h2>Worked Example: A College Computer Lab</h2>
<p>The computer lab at Edutrack College has twenty desktop computers. Each computer is connected by an Ethernet cable to a switch. The switch is connected to a router, which is connected to a modem for internet access. Students can share a printer connected to the network and access lesson materials stored on a central computer. This LAN makes it easy for the instructor to distribute files and collect assignments.</p>

<h2>Try It Yourself</h2>
<ol>
<li>List three devices in your home or school that are connected to a network.</li>
<li>Explain the difference between a LAN and a WAN.</li>
<li>Identify the router or Wi-Fi device in your classroom or home.</li>
<li>Draw a simple diagram showing three computers connected to a switch and router.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Network</strong> — connected computers that can share resources.</li>
<li><strong>Wired network</strong> — a network that uses cables to connect devices.</li>
<li><strong>Wireless network</strong> — a network that uses radio waves to connect devices.</li>
<li><strong>LAN</strong> — Local Area Network; covers a small area.</li>
<li><strong>WAN</strong> — Wide Area Network; covers a large area.</li>
<li><strong>Router</strong> — a device that directs data between networks.</li>
</ul>

<h2>Summary</h2>
<p>Networks connect computers so they can share resources and communicate. Wired networks are fast and reliable, while wireless networks offer flexibility. LANs serve small areas and WANs connect distant places. Routers, switches, modems and access points are the building blocks of networks.</p>
HTML,
            ],
            [
                'title' => 'A1.7.2 Benefits of Networks and the Internet',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe the benefits of networking computers in different locations, explain why organisations connect to the internet, and give Zambian examples of how networks improve business, education and government.</p>

<h2>Why Network Computers?</h2>
<p>Organisations network computers for many reasons. Sharing a single printer among ten staff members is cheaper than buying ten printers. Sharing files on a central server makes backup easier. Networks also allow staff to communicate through email and messaging, and they allow managers to monitor systems from one place.</p>

<h2>Benefits of Networking</h2>
<p>The main benefits of computer networks include:</p>
<ul>
<li><strong>Resource sharing</strong> — printers, scanners, internet access and files can be shared.</li>
<li><strong>Communication</strong> — email, video calls and instant messaging connect people quickly.</li>
<li><strong>Cost savings</strong> — fewer devices and easier maintenance reduce expenses.</li>
<li><strong>Data management</strong> — central storage makes backup and security simpler.</li>
<li><strong>Collaboration</strong> — people in different locations can work on the same project.</li>
</ul>

<h2>Why Use the Internet?</h2>
<p>The internet is a global network that connects millions of smaller networks. It gives access to information, communication tools, online services and entertainment. In Zambia, the internet supports e-government services, online banking, mobile money, e-learning, e-commerce and remote work. For a small business, an internet connection can open new markets and reduce communication costs.</p>

<h2>Zambian Examples</h2>
<p>A mobile money agent uses a network to send and receive money across the country. A school uses the internet to access educational websites and communicate with parents. A government office uses the internet to submit reports to Lusaka. A farmer checks market prices online before selling crops. These examples show that networking and the internet are essential for modern life in Zambia.</p>

<h2>Worked Example: Connecting Branch Offices</h2>
<p>A hardware supplier has shops in Lusaka, Kitwe and Livingstone. Each shop has a LAN for local staff. The three LANs are connected through a WAN so the head office can see stock levels in every branch. When a customer in Livingstone asks for an item that is out of stock, the shop checks the network and arranges transfer from Kitwe. The internet connection also allows online sales and mobile money payments. Networking has made the business more efficient and responsive.</p>

<h2>Try It Yourself</h2>
<ol>
<li>List five benefits of networking computers in a school.</li>
<li>Describe how a small business in Zambia can benefit from the internet.</li>
<li>Think of a service you have used that requires a network. How did it help you?</li>
<li>Discuss whether mobile money would be possible without computer networks.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Resource sharing</strong> — using the same device or file from several computers.</li>
<li><strong>Server</strong> — a central computer that stores files and manages network resources.</li>
<li><strong>Collaboration</strong> — working together on a task.</li>
<li><strong>Internet</strong> — a global network connecting millions of computers.</li>
<li><strong>E-learning</strong> — learning using electronic resources and the internet.</li>
</ul>

<h2>Summary</h2>
<p>Computer networks provide resource sharing, communication, cost savings and better data management. The internet extends these benefits across the world. In Zambia, networks and the internet support businesses, schools, government and mobile money services, making them essential tools for development.</p>
HTML,
            ],
            [
                'title' => 'A1.7.3 Browsing the Web with Search Engines',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to open a web browser, enter a URL, use a search engine to find information, access information on a LAN and WAN, save web pages, and download documents safely.</p>

<h2>Web Browsers</h2>
<p>A <strong>web browser</strong> is software used to view websites. Common browsers include Google Chrome, Microsoft Edge, Mozilla Firefox and Safari. A browser translates the code of a website into text, images and links that you can read and click. Browsers also allow you to save pages, download files and manage bookmarks.</p>

<h2>URLs and Addresses</h2>
<p>A <strong>URL</strong>, or Uniform Resource Locator, is the address of a webpage. For example, <code>https://www.zra.org.zm</code> is the URL of the Zambia Revenue Authority website. To visit a website, type its URL into the browser's address bar and press Enter. If you do not know the URL, you can use a search engine.</p>

<h2>Using Search Engines</h2>
<p>A <strong>search engine</strong> is a website that helps you find information on the internet. Google is the most popular search engine, but Bing and Yahoo are also used. To search, type keywords into the search box. For example, typing "TEVETA accredited colleges in Zambia" will show a list of relevant websites. Use specific keywords for better results. Put phrases in quotation marks to find exact words.</p>

<h2>Accessing Information on LAN and WAN</h2>
<p>Information can be stored on a local network or on the internet. A file stored on a server in your college is on the LAN. A website hosted in another country is on the WAN. Browsers can access both if the network is configured correctly. For example, a college portal may open from the LAN, while Google opens from the WAN.</p>

<h2>Saving and Downloading</h2>
<p>To save a webpage for offline reading, press Ctrl+S and choose a location. To download a document, click a download link and choose Save. Be careful when downloading files from unknown websites because they may contain viruses. Always scan downloaded files with antivirus software before opening them.</p>

<h2>Worked Example: Researching TEVETA Requirements</h2>
<p>A student wants to know the entry requirements for the Trade Certificate in Computer Studies Level III. She opens Chrome and types "TEVETA Computer Studies Level III" into Google. She clicks a result from the TEVETA website and reads the syllabus. She saves the page as a PDF for later study. She also downloads a PDF application form and saves it in her Documents folder.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open a web browser and identify the address bar.</li>
<li>Type the URL of a Zambian government website and visit it.</li>
<li>Use a search engine to find information about computer training in Zambia.</li>
<li>Save a webpage to your computer.</li>
<li>Download a document and save it in the correct folder.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Web browser</strong> — software used to view websites.</li>
<li><strong>URL</strong> — the address of a webpage.</li>
<li><strong>Search engine</strong> — a tool that finds websites based on keywords.</li>
<li><strong>Keyword</strong> — a word or phrase typed into a search engine.</li>
<li><strong>Download</strong> — to copy a file from the internet to your computer.</li>
</ul>

<h2>Summary</h2>
<p>Web browsers, URLs and search engines are the main tools for finding information on the internet. Safe downloading and saving habits help you collect useful resources. Understanding the difference between LAN and WAN access helps you know where information comes from and how to reach it.</p>
HTML,
            ],
            [
                'title' => 'A1.7.4 Practical Email Skills',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to compose a new email, address it correctly, attach a file, send the message, and follow email etiquette when communicating with employers, teachers, businesses and government offices in Zambia.</p>

<h2>What Is Email?</h2>
<p><strong>Email</strong>, short for electronic mail, is a way of sending messages over a network or the internet. An email can include text, attachments such as documents or pictures, and links to websites. Email is widely used for formal communication in Zambia, including job applications, business enquiries and correspondence with institutions such as ZRA and TEVETA.</p>

<h2>Composing an Email</h2>
<p>Most email services, such as Gmail, Outlook and Yahoo, have a similar layout. To compose a new email, click the Compose or New button. Fill in these fields:</p>
<ul>
<li><strong>To</strong> — the email address of the main recipient.</li>
<li><strong>Subject</strong> — a short description of the email's purpose.</li>
<li><strong>Body</strong> — the main message.</li>
</ul>
<p>Write the body in clear paragraphs. Use a polite greeting such as "Dear Sir or Madam," or "Dear Mr Phiri," and a respectful closing such as "Yours faithfully," or "Kind regards."</p>

<h2>Attaching a File</h2>
<p>To send a document with an email, click the paperclip icon or the Attach button. Browse to the file, select it, and click Open. Wait for the file to upload before sending. Make sure the attachment is the correct file and that it is not too large. Many email services limit attachments to 25 MB.</p>

<h2>Email Etiquette</h2>
<p>Good email etiquette makes a positive impression. Follow these rules:</p>
<ul>
<li>Use a clear and specific subject line.</li>
<li>Be polite and professional.</li>
<li>Keep messages concise but complete.</li>
<li>Check spelling and grammar before sending.</li>
<li>Do not write in all capital letters.</li>
<li>Use CC to keep others informed and BCC to protect private addresses.</li>
</ul>

<h2>Worked Example: Emailing a College Administrator</h2>
<p>Chileshe wants to ask about course fees at Edutrack Computer Training College. She composes the following email:</p>
<blockquote>
<p><strong>Subject:</strong> Enquiry About Trade Certificate in Computer Studies Level III Fees</p>
<p>Dear Sir or Madam,</p>
<p>I am interested in enrolling in the Trade Certificate in Computer Studies Level III at Edutrack Computer Training College. Could you please send me information about the course fees, intake dates and entry requirements?</p>
<p>My name is Chileshe Banda and my contact number is +260 97 123 4567.</p>
<p>Kind regards,<br>
Chileshe Banda</p>
</blockquote>
<p>This email is clear, polite and contains all the information the college needs to reply.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open an email account or email program.</li>
<li>Compose a practice email to a classmate with a clear subject.</li>
<li>Attach a document to the email.</li>
<li>Check the spelling and send the email.</li>
<li>Write a list of five email etiquette rules.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Email</strong> — electronic mail sent over a network.</li>
<li><strong>Attachment</strong> — a file sent with an email.</li>
<li><strong>Subject line</strong> — a short summary of an email's contents.</li>
<li><strong>CC</strong> — carbon copy; sends a copy to additional visible recipients.</li>
<li><strong>BCC</strong> — blind carbon copy; hides additional recipients' addresses.</li>
</ul>

<h2>Summary</h2>
<p>Email is an essential communication tool for study, work and business. Composing clear messages, attaching files correctly and following etiquette help you communicate professionally. Good email skills are valuable when applying for jobs, contacting institutions and managing business in Zambia.</p>
HTML,
            ],
            [
                'title' => 'A1.7.5 Using the Internet Responsibly',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe safe and responsible behaviour online, recognise common internet risks, and explain how Zambian laws and community values apply to digital communication.</p>

<h2>Responsible Internet Use</h2>
<p>The internet is a powerful tool, but it must be used responsibly. Responsible users respect other people, protect their own information, and follow the law. Before posting or sending anything online, ask whether it is true, kind and necessary. Once information is shared on the internet, it can be difficult to remove.</p>

<h2>Common Online Risks</h2>
<p>Users should be aware of these common risks:</p>
<ul>
<li><strong>Scams and fraud</strong> — fake messages asking for money or personal details.</li>
<li><strong>Malware</strong> — harmful software that can damage devices or steal data.</li>
<li><strong>Cyberbullying</strong> — using technology to harass or embarrass others.</li>
<li><strong>Privacy breaches</strong> — sharing personal information with the wrong people.</li>
<li><strong>Misinformation</strong> — false information that spreads quickly online.</li>
</ul>

<h2>Protecting Yourself Online</h2>
<p>Simple habits reduce online risks. Use strong passwords and do not share them. Avoid clicking links in unexpected messages. Do not send money to people you do not know. Keep personal information such as your NRC number, bank details and home address private. Install antivirus software and keep the operating system updated.</p>

<h2>Netiquette and Zambian Values</h2>
<p><strong>Netiquette</strong> is etiquette for the internet. It includes being polite, respecting others' opinions, and not sending spam. In Zambia, values such as respect for elders, honesty and ubuntu apply online just as they do in person. Students should avoid sharing examination answers, spreading gossip, or posting content that harms the reputation of others.</p>

<h2>Worked Example: Avoiding an Online Scam</h2>
<p>Mr Zulu receives an email claiming he has won a smartphone and must send K200 for delivery. He checks the sender's address and sees it is not from a real company. He does not reply or send money. He deletes the email and warns his friends. By thinking carefully and protecting his personal information, he avoids losing money.</p>

<h2>Try It Yourself</h2>
<ol>
<li>List five rules for responsible internet use.</li>
<li>Describe what you would do if you received a suspicious message asking for money.</li>
<li>Discuss how Zambian cultural values apply to online behaviour.</li>
<li>Check the privacy settings on one of your social media accounts.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Netiquette</strong> — good manners and behaviour on the internet.</li>
<li><strong>Malware</strong> — harmful software such as viruses and spyware.</li>
<li><strong>Scam</strong> — a dishonest plan to steal money or information.</li>
<li><strong>Misinformation</strong> — false or inaccurate information shared as if it were true.</li>
<li><strong>Privacy</strong> — protecting personal information from being seen by others.</li>
</ul>

<h2>Summary</h2>
<p>Using the internet responsibly means protecting yourself, respecting others and following the law. Understanding risks such as scams, malware and cyberbullying helps you stay safe. Good netiquette and Zambian values guide positive behaviour online, whether at school, work or home.</p>
HTML,
            ],
        ];
    }

    private function module7Quiz(): array
    {
        return [
            'title' => 'A1.7 Quiz: Using Networks & the Internet',
            'description' => 'Test your understanding of networks, the internet, web browsing and email.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which type of network covers a small area such as a school?',
                    'explanation' => 'A LAN, or Local Area Network, covers a limited area such as a building or campus.',
                    'options' => [
                        ['text' => 'WAN', 'is_correct' => false],
                        ['text' => 'LAN', 'is_correct' => true],
                        ['text' => 'MAN', 'is_correct' => false],
                        ['text' => 'Internet', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does URL stand for?',
                    'explanation' => 'URL stands for Uniform Resource Locator, the address of a webpage.',
                    'options' => [
                        ['text' => 'Universal Resource Link', 'is_correct' => false],
                        ['text' => 'Uniform Resource Locator', 'is_correct' => true],
                        ['text' => 'Universal Reference Link', 'is_correct' => false],
                        ['text' => 'Uniform Reference Locator', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A wireless network uses cables to connect devices.',
                    'explanation' => 'Wireless networks use radio waves, not cables, to connect devices.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which device directs traffic between a home network and the internet?',
                    'explanation' => 'A router directs data between different networks, such as a home LAN and the internet.',
                    'options' => [
                        ['text' => 'Switch', 'is_correct' => false],
                        ['text' => 'Router', 'is_correct' => true],
                        ['text' => 'Printer', 'is_correct' => false],
                        ['text' => 'Scanner', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Email can include attached files such as documents and pictures.',
                    'explanation' => 'Email supports attachments, allowing files to be sent with the message.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which field of an email should contain a short description of the message?',
                    'explanation' => 'The subject line summarises the purpose of the email.',
                    'options' => [
                        ['text' => 'To', 'is_correct' => false],
                        ['text' => 'CC', 'is_correct' => false],
                        ['text' => 'Subject', 'is_correct' => true],
                        ['text' => 'Body', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Module 8: A1.8 Using Multimedia Files
    // ------------------------------------------------------------------

    private function module8Lessons(): array
    {
        return [
            [
                'title' => 'A1.8.1 Introduction to Multimedia',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to define multimedia, identify the main types of multimedia, explain how multimedia is used in education and business in Zambia, and plan a simple multimedia presentation.</p>

<h2>What Is Multimedia?</h2>
<p><strong>Multimedia</strong> is the combination of different types of media in one presentation or document. The main types of media are text, images, audio, video and animation. A multimedia presentation might include written bullet points, photographs, background music, a recorded voice explanation and a short video clip. Multimedia makes information more interesting and easier to understand.</p>

<h2>Types of Multimedia</h2>
<p>Common multimedia elements include:</p>
<ul>
<li><strong>Text</strong> — headings, paragraphs, captions and labels.</li>
<li><strong>Images</strong> — photographs, drawings, diagrams and clip art.</li>
<li><strong>Audio</strong> — music, speech, sound effects and recorded interviews.</li>
<li><strong>Video</strong> — moving pictures with sound.</li>
<li><strong>Animation</strong> — moving graphics created by computer.</li>
</ul>

<h2>Uses of Multimedia in Zambia</h2>
<p>Multimedia is used in many ways. Schools use video lessons to explain difficult topics. Churches project song lyrics and sermon slides. Businesses create advertisement videos for social media. Health workers use educational videos to teach communities about diseases and hygiene. Radio stations combine audio, text and images on their websites. As smartphones become more common, multimedia content reaches more people across Zambia.</p>

<h2>Planning a Multimedia Presentation</h2>
<p>Before creating multimedia, plan the content. Decide on the purpose, the audience and the main message. Choose the media types that best support the message. For example, a presentation about handwashing might use text for steps, images for demonstration, and a short video showing correct technique. Keep files small so the presentation runs smoothly, especially on computers with limited storage or slow internet.</p>

<h2>Worked Example: A Community Health Presentation</h2>
<p>A health worker in Southern Province prepares a multimedia presentation about malaria prevention. She includes text about symptoms, images of mosquito nets, audio of a local language explanation, and a short video showing how to hang a net properly. The combination of media helps the audience remember the message. She saves the presentation on a laptop and also on a USB drive so she can show it in villages without internet.</p>

<h2>Try It Yourself</h2>
<ol>
<li>List five types of multimedia and give one example of each.</li>
<li>Think of a topic you could teach using multimedia. Which media types would you use?</li>
<li>Search for one educational video online that explains a computer topic.</li>
<li>Plan a three-slide multimedia presentation about your college.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Multimedia</strong> — the combination of text, images, audio, video and animation.</li>
<li><strong>Media</strong> — different ways of presenting information.</li>
<li><strong>Animation</strong> — moving images created by computer.</li>
<li><strong>Audio</strong> — sound such as music or speech.</li>
<li><strong>Video</strong> — moving pictures, usually with sound.</li>
</ul>

<h2>Summary</h2>
<p>Multimedia combines text, images, audio, video and animation to communicate messages effectively. It is used in education, health, business and entertainment across Zambia. Good planning ensures that each media type supports the message and that the final presentation is suitable for the audience and equipment available.</p>
HTML,
            ],
            [
                'title' => 'A1.8.2 Designing Graphics',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to use graphics software to create simple images, understand the difference between bitmap and vector graphics, and apply basic design principles such as balance, contrast and simplicity.</p>

<h2>Graphics Software</h2>
<p><strong>Graphics software</strong> is used to create and edit images. Some programs, such as Microsoft Paint and GIMP, are good for editing photographs and creating simple drawings. Others, such as Inkscape and CorelDRAW, are designed for vector graphics such as logos and illustrations. Many DTP and presentation programs also include basic drawing tools.</p>

<h2>Bitmap and Vector Graphics</h2>
<p>There are two main types of digital graphics. <strong>Bitmap</strong> images are made of tiny coloured dots called pixels. Photographs are bitmap images. They can lose quality if enlarged too much. <strong>Vector</strong> graphics are made of mathematical shapes and lines. They can be enlarged without losing quality, which makes them ideal for logos and diagrams.</p>

<h2>Basic Design Principles</h2>
<p>Good graphic design follows simple principles:</p>
<ul>
<li><strong>Balance</strong> — distribute visual weight evenly on the page.</li>
<li><strong>Contrast</strong> — make important elements stand out using colour, size or boldness.</li>
<li><strong>Simplicity</strong> — avoid clutter; include only what is necessary.</li>
<li><strong>Alignment</strong> — line up elements to create a clean, organised look.</li>
<li><strong>Repetition</strong> — use the same colours, fonts and styles throughout.</li>
</ul>

<h2>Worked Example: Creating a Simple Logo</h2>
<p>A youth cooperative in Kalomo wants a logo. A student uses Inkscape to draw a circle and adds the cooperative name in bold letters. She chooses green and gold because they represent growth and prosperity. She keeps the design simple so it is easy to recognise and print. The logo is saved in vector format so it can be used on small name tags or large banners without losing quality.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open a graphics program such as Paint or Inkscape.</li>
<li>Draw a simple picture using shapes and lines.</li>
<li>Change the colours and add text.</li>
<li>Save the image in two different formats, such as PNG and JPG.</li>
<li>Explain the difference between bitmap and vector graphics.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Graphics software</strong> — programs used to create and edit images.</li>
<li><strong>Bitmap</strong> — an image made of pixels, such as a photograph.</li>
<li><strong>Vector</strong> — an image made of mathematical shapes that can be resized without losing quality.</li>
<li><strong>Pixel</strong> — a tiny square of colour that makes up a digital image.</li>
<li><strong>Contrast</strong> — the difference between elements that makes them stand out.</li>
</ul>

<h2>Summary</h2>
<p>Graphics software allows you to create and edit images for publications, presentations and websites. Understanding bitmap and vector graphics helps you choose the right format. Applying basic design principles makes your graphics clear, attractive and professional.</p>
HTML,
            ],
            [
                'title' => 'A1.8.3 Producing Video and Audio',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to record audio and video using common devices, save recordings in suitable formats, and describe basic steps for producing a short video or audio clip for education or business.</p>

<h2>Recording Audio</h2>
<p>Audio can be recorded using a smartphone, a computer microphone or a digital voice recorder. Common audio formats include MP3 and WAV. MP3 files are smaller and easier to share, while WAV files are larger but higher quality. When recording, choose a quiet location, speak clearly and keep the microphone at a consistent distance from the speaker.</p>

<h2>Recording Video</h2>
<p>Most smartphones and laptops can record video. When recording, hold the device steady or use a tripod if possible. Make sure there is enough light and that the background is not distracting. Speak clearly and plan what you want to say before pressing record. Short videos are usually more effective than long ones.</p>

<h2>Basic Video Production Steps</h2>
<p>Producing a simple video involves these steps:</p>
<ol>
<li><strong>Plan</strong> — decide the topic, audience and message.</li>
<li><strong>Script</strong> — write what will be said and shown.</li>
<li><strong>Record</strong> — capture video and audio using a camera or phone.</li>
<li><strong>Transfer</strong> — copy the files to a computer.</li>
<li><strong>Edit</strong> — trim unwanted parts and arrange clips in order.</li>
<li><strong>Save</strong> — export the final video in a common format such as MP4.</li>
</ol>

<h2>Worked Example: Recording a Tutorial</h2>
<p>A student at Edutrack College records a tutorial about saving a document. He writes a short script, gathers a phone and a tripod, and records himself demonstrating the steps on a laptop. He transfers the video to a computer, trims the beginning and end, and adds a title card. He saves the final video as an MP4 and uploads it to the college learning platform.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Use a phone to record a thirty-second audio message introducing yourself.</li>
<li>Record a one-minute video explaining how to do a simple computer task.</li>
<li>Transfer the files to a computer and check that they play correctly.</li>
<li>Rename the files with clear names and save them in a folder.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Audio</strong> — recorded sound such as speech or music.</li>
<li><strong>Video</strong> — recorded moving pictures.</li>
<li><strong>MP3</strong> — a common compressed audio format.</li>
<li><strong>MP4</strong> — a common compressed video format.</li>
<li><strong>Tripod</strong> — a stand that holds a camera steady.</li>
</ul>

<h2>Summary</h2>
<p>Audio and video are powerful multimedia tools. Recording with a phone or computer is simple, but planning and good technique improve quality. Saving files in common formats such as MP3 and MP4 makes them easy to share and play on different devices.</p>
HTML,
            ],
            [
                'title' => 'A1.8.4 Digitising Images',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create digital images using a scanner and a digital camera, save images in common formats, perform basic edits such as cropping and resizing, and use digitised images in presentations and publications.</p>

<h2>What Is Digitising?</h2>
<p><strong>Digitising</strong> means converting something from physical form into digital form that a computer can store and display. Photographs, drawings and documents can be digitised using a scanner or a digital camera. Once digitised, images can be edited, shared, printed and inserted into documents.</p>

<h2>Using a Scanner</h2>
<p>A <strong>scanner</strong> converts paper documents and photographs into digital files. To scan, place the document face down on the glass, close the lid, and start the scan using the scanner software. Choose a suitable resolution. Higher resolution produces better quality but larger files. For documents, 200 to 300 dots per inch is usually enough. Save the scanned image as a JPG, PNG or PDF depending on the purpose.</p>

<h2>Using a Digital Camera</h2>
<p>A <strong>digital camera</strong> captures photographs directly as digital files. Modern smartphones also work as digital cameras. When taking a picture, hold the camera steady, focus on the subject, and check the lighting. After taking the photo, transfer it to a computer using a USB cable, memory card or cloud service. Rename the file with a meaningful name so you can find it later.</p>

<h2>Basic Image Editing</h2>
<p>After digitising an image, you may need to edit it. Common basic edits include:</p>
<ul>
<li><strong>Cropping</strong> — removing unwanted edges.</li>
<li><strong>Resizing</strong> — changing the dimensions or file size.</li>
<li><strong>Rotating</strong> — turning the image to the correct orientation.</li>
<li><strong>Adjusting brightness and contrast</strong> — improving visibility.</li>
</ul>
<p>Simple editing can be done in Paint, Photos app, GIMP or online editors. Save the edited image with a new filename so the original is preserved.</p>

<h2>Worked Example: Digitising Old Certificates</h2>
<p>A college needs digital copies of old student certificates for record keeping. The administrator places each certificate on a flatbed scanner and scans it at 300 dots per inch. She saves each scan as a PDF named with the student's name and certificate number. She then crops the scans to remove blank edges and stores them in a folder on the college server. The certificates are now safe from physical damage and easy to search.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Scan a document or photograph and save it on the computer.</li>
<li>Take a photo with a digital camera or phone and transfer it to the computer.</li>
<li>Open an image editor and crop a photograph.</li>
<li>Resize an image to fit a presentation slide.</li>
<li>Insert a digitised image into a Word document or PowerPoint slide.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Digitise</strong> — to convert physical material into digital form.</li>
<li><strong>Scanner</strong> — a device that converts paper into digital images.</li>
<li><strong>Resolution</strong> — the level of detail in a digital image, measured in dots per inch.</li>
<li><strong>Crop</strong> — to remove part of an image.</li>
<li><strong>Resize</strong> — to change the size of an image.</li>
</ul>

<h2>Summary</h2>
<p>Digitising images with scanners and cameras turns physical documents and photos into digital files. Basic editing improves the appearance and usefulness of these images. Digitised images can be used in presentations, publications, records and websites, making them valuable tools in education and business.</p>
HTML,
            ],
            [
                'title' => 'A1.8.5 Creating a Multimedia Presentation',
                'duration_minutes' => 40,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to combine text, images, audio and video into one multimedia presentation, test the presentation on the available equipment, and reflect on how multimedia can improve communication.</p>

<h2>Planning Your Presentation</h2>
<p>A successful multimedia presentation starts with a clear plan. Decide on the topic, the audience and the purpose. List the main points you want to communicate. Choose the media types that will support each point. For example, a presentation about Zambian agriculture might use text for facts, photos of crops, a chart of rainfall data, and a short interview with a farmer.</p>

<h2>Combining Media in PowerPoint</h2>
<p>PowerPoint and similar programs allow you to combine many media types on one slide. You can:</p>
<ul>
<li>Type text in placeholders or text boxes.</li>
<li>Insert pictures from files or online sources.</li>
<li>Add audio clips that play automatically or when clicked.</li>
<li>Insert video clips from files or links.</li>
<li>Apply animations and transitions sparingly.</li>
</ul>
<p>Make sure media files are stored in the same folder as the presentation so they can be found when the presentation is moved to another computer.</p>

<h2>Testing the Presentation</h2>
<p>Always test a multimedia presentation before showing it to an audience. Check that all images, audio and video play correctly. Test the volume of audio. Make sure the presentation opens on the computer and projector you will use. During load-shedding, ensure the laptop battery is charged or a backup power source is available.</p>

<h2>Reflecting on Multimedia</h2>
<p>Multimedia improves communication because it reaches people through different senses. Some people remember pictures better than words. Others understand a demonstration better after watching a video. However, too much multimedia can be distracting. The best presentations use media to support the message, not to replace it.</p>

<h2>Worked Example: A Presentation About Edutrack College</h2>
<p>A student creates a multimedia presentation to introduce Edutrack Computer Training College. The first slide has the college name and a photo of the building. The second slide uses bullet points to list courses. The third slide shows a chart of student enrolment growth. The fourth slide includes a short audio clip of the principal welcoming new students. The final slide has contact details and a map. She tests the presentation in the college lab and saves all files in one folder.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a five-slide multimedia presentation on a topic of your choice.</li>
<li>Include text, at least one image, and either audio or video.</li>
<li>Apply one simple transition.</li>
<li>Test the presentation in slide show view.</li>
<li>Save all related files in one folder.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Multimedia presentation</strong> — a presentation that uses more than one type of media.</li>
<li><strong>Media file</strong> — a file containing audio, video or image content.</li>
<li><strong>Placeholder</strong> — a box on a slide for text or other content.</li>
<li><strong>Slide show view</strong> — the full-screen display of a presentation.</li>
<li><strong>Backup</strong> — a copy of files kept in case the original is lost.</li>
</ul>

<h2>Summary</h2>
<p>Creating a multimedia presentation involves planning, combining media, testing and saving files carefully. Multimedia can make communication more engaging and memorable when it supports the main message. With practice, you can produce presentations that inform, persuade and inspire your audience.</p>
HTML,
            ],
        ];
    }

    private function module8Quiz(): array
    {
        return [
            'title' => 'A1.8 Quiz: Using Multimedia Files',
            'description' => 'Test your understanding of multimedia, graphics, audio, video and digitising images.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a type of multimedia?',
                    'explanation' => 'Video is one of the main types of multimedia, along with text, images and audio.',
                    'options' => [
                        ['text' => 'Spreadsheet', 'is_correct' => false],
                        ['text' => 'Video', 'is_correct' => true],
                        ['text' => 'Database', 'is_correct' => false],
                        ['text' => 'Operating system', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which graphic type can be enlarged without losing quality?',
                    'explanation' => 'Vector graphics are made of mathematical shapes and can be resized without losing quality.',
                    'options' => [
                        ['text' => 'Bitmap', 'is_correct' => false],
                        ['text' => 'JPEG', 'is_correct' => false],
                        ['text' => 'Vector', 'is_correct' => true],
                        ['text' => 'PNG', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A scanner converts paper documents into digital files.',
                    'explanation' => 'Scanners create digital images of paper documents and photographs.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which format is commonly used for video files?',
                    'explanation' => 'MP4 is a widely used compressed video format.',
                    'options' => [
                        ['text' => 'MP3', 'is_correct' => false],
                        ['text' => 'DOCX', 'is_correct' => false],
                        ['text' => 'MP4', 'is_correct' => true],
                        ['text' => 'XLSX', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Higher resolution scans always produce smaller file sizes.',
                    'explanation' => 'Higher resolution scans produce larger file sizes because they contain more detail.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which device is used to hold a camera steady while recording video?',
                    'explanation' => 'A tripod holds a camera steady to reduce shaking.',
                    'options' => [
                        ['text' => 'Scanner', 'is_correct' => false],
                        ['text' => 'Tripod', 'is_correct' => true],
                        ['text' => 'Microphone', 'is_correct' => false],
                        ['text' => 'Router', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }
}
