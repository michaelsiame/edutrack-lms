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

class DigitalLiteracyContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Digital Literacy')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Digital Literacy" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in Digital Literacy already has modules. Skipping content seed.');
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
                    'time_limit_minutes' => 20,
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
                'title' => 'Module 1: Computers and Smartphones for Everyday Use',
                'description' => 'Learn what a computer is, identify its parts, navigate an Android smartphone, and keep your devices safe.',
            ],
            [
                'title' => 'Module 2: Files, Folders, and the Online World',
                'description' => 'Organise your files, browse the internet, send emails, and stay safe from online scams.',
            ],
            [
                'title' => 'Module 3: Digital Tools for Everyday Zambia',
                'description' => 'Use Google tools, mobile money, and e-government services to solve real problems in Zambia.',
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
                'title' => '1.1 What Is a Computer and What Are Its Parts?',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what a computer is in your own words, name the main parts of a desktop computer and a laptop, and identify common ports and buttons so that you can set up and use a computer with confidence at home, at college, or in a small business.</p>

<h2>What Is a Computer?</h2>
<p>A computer is an electronic machine that takes in information, processes it according to instructions, and produces a result. You already use computers every day, even if you do not realise it. An Android smartphone is a computer. The ATM at the bank is a computer. The point-of-sale machine in a shop that accepts Airtel Money or MTN MoMo is also a computer. Even the digital ZESCO prepaid meter on your wall contains a small computer.</p>
<p>Computers help us write documents, keep records, send messages, browse the internet, watch videos, and run businesses. A market stall owner in Soweto Market who tracks stock in a notebook can do the same task faster and more accurately with a computer. A parent in Kalomo who wants to email a teacher can do so from a college computer or a smartphone.</p>

<h2>The Main Parts of a Computer</h2>
<p>Most computers have the same basic parts, whether they are large desktops in an office or small laptops carried to class. Understanding these parts will help you talk to technicians, buy the right equipment, and fix simple problems yourself.</p>

<h3>The Monitor</h3>
<p>The monitor is the screen. It displays pictures, words, and videos. Some monitors are separate boxes that sit on a desk; others are built into a laptop lid. If the screen is black, check that the monitor is turned on and that the cable connecting it to the computer is secure. During load-shedding, remember that a monitor needs electricity just like any other appliance, so an uninterruptible power supply or a charged laptop battery can keep you working.</p>

<h3>The Keyboard</h3>
<p>The keyboard is the panel of buttons with letters, numbers, and symbols. You use it to type emails, essays, and search terms. The most common layout is called QWERTY, named after the first six letter keys on the top row. If you plan to type in local Zambian languages, the standard keyboard still works, but you may need to learn a few extra key combinations or use on-screen tools.</p>

<h3>The Mouse</h3>
<p>The mouse is the handheld device that moves a small arrow, called the cursor or pointer, across the screen. You click to select things, double-click to open them, and right-click to see extra options. On a laptop, the touchpad below the keyboard does the same job. If your mouse stops working, check that the USB receiver is plugged in or that the batteries are not flat.</p>

<h3>The CPU</h3>
<p>The CPU, or Central Processing Unit, is often called the brain of the computer. It sits inside a box on or under the desk, or inside the laptop casing. You cannot see it easily, but it performs the calculations and follows the instructions that make everything work. A faster CPU means the computer can run more programs at once without slowing down. For basic tasks like word processing and browsing, a modest CPU is perfectly adequate.</p>

<h3>Other Important Parts</h3>
<ul>
<li><strong>Speakers</strong> produce sound. They may be built into the monitor or laptop, or they may be separate boxes.</li>
<li><strong>The hard drive or SSD</strong> stores your files, programs, and operating system even when the power is off.</li>
<li><strong>RAM</strong> is temporary memory that helps the computer work quickly while it is turned on. More RAM usually means smoother multitasking.</li>
<li><strong>USB ports</strong> are small rectangular slots where you plug in flash drives, mice, keyboards, and phone chargers.</li>
</ul>

<h2>Worked Example: Setting Up a College Computer</h2>
<p>Imagine you arrive at Edutrack Computer Training College and sit down at a desktop computer. Follow these steps to get started:</p>
<ol>
<li>Press the power button on the front of the CPU box. You should hear a gentle whir and see lights appear.</li>
<li>Wait for the Windows desktop to load. This may take one or two minutes.</li>
<li>Check that the monitor is on. If it says "No Signal," make sure the cable at the back is pushed in firmly.</li>
<li>Move the mouse and watch the cursor respond. If nothing happens, check that the USB plug is seated properly.</li>
<li>Open the on-screen keyboard or a text editor and type a few words to confirm the keyboard is working.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Look at the computer in front of you. Point to the monitor, the keyboard, the mouse, and the CPU box.</li>
<li>Count how many USB ports you can see on the front and back of the CPU box or on the sides of the laptop.</li>
<li>Find the power button on both the monitor and the CPU box. Practice turning the computer on and off safely using the Start menu: click Start, then the Power icon, then Shut down.</li>
<li>Draw a simple diagram of a desktop computer and label each part. Show it to a classmate and explain what each part does.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Monitor</strong> — the screen that displays images and text from the computer.</li>
<li><strong>CPU</strong> — the Central Processing Unit; the electronic brain that carries out instructions.</li>
<li><strong>Cursor / Pointer</strong> — the arrow on the screen that you control with the mouse or touchpad.</li>
<li><strong>USB port</strong> — a slot on the computer for connecting external devices such as flash drives and mice.</li>
<li><strong>Keyboard</strong> — the set of keys used to type letters, numbers, and commands into the computer.</li>
</ul>

<h2>Summary</h2>
<p>A computer is an electronic device that processes information to help you work, study, and communicate. The main parts you interact with are the monitor, keyboard, mouse, and CPU. Understanding these components allows you to set up equipment correctly, describe problems to an instructor or technician, and feel more confident whenever you sit in front of a desktop or laptop. Remember that your smartphone is also a computer, so the same logical thinking applies there too.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/intro-to-computers/">Microsoft Learn — Introduction to Computers</a></li>
<li><a href="https://support.google.com/android/answer/6174145">Google Android Help — Device Basics</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://www.w3schools.com/computer/">W3Schools — Computer Basics</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.2 Using Your Android Smartphone with Confidence',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to turn your Android smartphone on and off, navigate the home screen and app drawer, connect to Wi-Fi and mobile data, install apps safely from the Google Play Store, and adjust basic settings such as brightness and battery saver so that your phone lasts longer during load-shedding.</p>

<h2>Your Smartphone Is a Pocket Computer</h2>
<p>An Android smartphone is a powerful computer that fits in your pocket. In Zambia, it is often the first and only computer a person owns. You can use it to send WhatsApp messages to your PTA group, check ZRA tax information, buy ZESCO tokens, sell tomatoes from your Soweto Market stall, or study course materials online. Because the phone is so important, learning to use it well is one of the best investments you can make.</p>

<h2>The Home Screen and App Drawer</h2>
<p>When you turn on your phone and unlock it, you see the <strong>home screen</strong>. This is your personal space. It usually shows a clock, the weather, and a set of app icons such as Phone, Messages, Camera, and Chrome. You can tap and hold an icon to move it, or drag one icon on top of another to create a folder.</p>
<p>To see every app installed on your phone, swipe up from the bottom of the screen. This opens the <strong>app drawer</strong>, which is like a catalogue of all your programs. If you cannot find an app, type its name in the search bar at the top of the app drawer.</p>

<h2>Turning Your Phone On and Off</h2>
<p>The <strong>power button</strong> is usually on the right-hand side of the phone. Press and hold it for one second to wake the screen when it is dark. To turn the phone completely off, press and hold the same button until a menu appears, then tap "Power off." To restart the phone, choose "Restart" instead. Restarting can fix minor problems such as a frozen app or a camera that will not open.</p>

<h2>Connecting to the Internet</h2>
<p>Your phone can reach the internet in two main ways. <strong>Wi-Fi</strong> connects you through a wireless router, such as the one at Edutrack College or in a café. It is usually faster and does not use your mobile data bundle. <strong>Mobile data</strong> uses the signal from your SIM card provider, such as Airtel or MTN. It works almost anywhere but costs money from your airtime or data bundle.</p>
<p>To connect to Wi-Fi, open <strong>Settings</strong>, tap <strong>Network &amp; Internet</strong>, then <strong>Wi-Fi</strong>, and choose the network name. Enter the password if required. A small Wi-Fi icon appears at the top of your screen when you are connected. To turn mobile data on or off, swipe down from the top of the screen to open Quick Settings and tap the Mobile Data icon.</p>

<h2>Installing Apps Safely</h2>
<p>The safest place to download apps is the <strong>Google Play Store</strong>. It is the colourful triangle icon on your phone. Open it, search for the app you need, and tap <strong>Install</strong>. The Play Store checks apps for viruses and removes dangerous ones. Never install apps from random links sent on WhatsApp or Facebook, because they may contain malware that steals your passwords or mobile money PIN.</p>

<h2>Battery Tips for Load-Shedding</h2>
<p>When electricity is unavailable, your phone battery becomes precious. Here are practical ways to make it last:</p>
<ul>
<li>Lower the screen brightness. Open Settings &gt; Display and move the brightness slider to the left.</li>
<li>Turn on Battery Saver. Swipe down, tap the battery icon, or find it in Settings &gt; Battery.</li>
<li>Close apps you are not using. Tap the square navigation button and swipe apps away.</li>
<li>Turn off Wi-Fi and mobile data when you do not need them, especially if the signal is weak because the phone uses extra power searching for a network.</li>
</ul>

<h2>Worked Example: Buying ZESCO Tokens on Your Phone</h2>
<p>Mrs Lungu needs electricity tokens because load-shedding is scheduled for the evening. She follows these steps on her Android phone:</p>
<ol>
<li>Unlocks the phone and connects to her home Wi-Fi to save mobile data.</li>
<li>Opens the Airtel Money or MTN MoMo app from the app drawer.</li>
<li>Selects "Pay Bill" or "Utilities," chooses ZESCO, and enters her meter number.</li>
<li>Enters the amount, for example K50, confirms with her PIN, and receives an SMS with the token number.</li>
<li>She copies the token number into her phone's Notes app so she will not lose the SMS.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Press and hold the power button. Practise turning the screen off and on without shutting the phone down.</li>
<li>Open the app drawer and find three apps you have never opened before. Tap each one to see what it does.</li>
<li>Go to Settings &gt; Display and reduce your brightness to the lowest comfortable level.</li>
<li>Check Settings &gt; Battery and note which apps are using the most power. Close any you do not need.</li>
<li>If you have mobile data, turn it off and connect to a trusted Wi-Fi network instead.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Home screen</strong> — the main screen of your phone where you place your favourite apps and widgets.</li>
<li><strong>App drawer</strong> — the complete list of every app installed on your phone.</li>
<li><strong>Wi-Fi</strong> — a wireless internet connection through a router or hotspot.</li>
<li><strong>Mobile data</strong> — internet access provided by your mobile network operator such as Airtel or MTN.</li>
<li><strong>Google Play Store</strong> — the official and safest place to download Android apps.</li>
</ul>

<h2>Summary</h2>
<p>Your Android smartphone is a versatile computer that you can use for study, business, and daily life in Zambia. Mastering the home screen, app drawer, power controls, and internet connections gives you a solid foundation. Always install apps from the Google Play Store, manage your battery carefully during load-shedding, and treat your phone with the same respect you would give a laptop or desktop computer.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/android/answer/6174145">Google Android Help — Device Basics</a></li>
<li><a href="https://support.google.com/googleplay/answer/2521768">Google Play Help — Install Apps</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.3 Typing, the Mouse, and Basic Controls',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to type words and sentences using the keyboard, use the mouse to click, double-click, and right-click, and perform basic actions such as selecting text, copying, pasting, and opening a simple document so that you can create notes, letters, and lists on a computer.</p>

<h2>Getting Comfortable with the Keyboard</h2>
<p>The keyboard is your primary way of putting words into a computer. Each key represents a letter, number, or symbol. The long space bar at the bottom creates spaces between words. The Enter or Return key moves the cursor to a new line. The Backspace key deletes the character to the left of the cursor, while the Delete key removes the character to the right.</p>
<p>Good posture helps you type faster and avoids wrist pain. Sit with your back straight and your feet flat on the floor. Place the keyboard so that your elbows bend at about ninety degrees. Let your fingers rest lightly on the keys rather than pressing hard. If you are using a computer at Edutrack College, adjust the chair height so that your wrists are level with the keyboard.</p>

<h2>Mouse Actions You Must Know</h2>
<p>The mouse controls the cursor, the small arrow on the screen. There are four essential actions:</p>
<ul>
<li><strong>Click</strong> — press the left mouse button once to select an item or place the cursor.</li>
<li><strong>Double-click</strong> — press the left button twice quickly to open a file, folder, or program.</li>
<li><strong>Right-click</strong> — press the right mouse button once to open a context menu with extra options such as Copy, Paste, and Rename.</li>
<li><strong>Click and drag</strong> — hold the left button down, move the mouse, and release to move an item or select a block of text.</li>
</ul>
<p>On a laptop, the touchpad performs the same actions. Tap once to click, tap twice to double-click, and tap with two fingers to right-click. If your touchpad seems too sensitive, you can adjust the speed in Settings.</p>

<h2>Selecting, Copying, and Pasting</h2>
<p>These three actions save enormous amounts of time. <strong>Selecting</strong> means highlighting text or an object so the computer knows you want to work with it. Click at the beginning of a word, hold the mouse button down, and drag to the end of the word to select it. Alternatively, double-click a word to select it instantly.</p>
<p>Once text is selected, you can copy it. The quickest way is to hold the <strong>Ctrl</strong> key and press <strong>C</strong>. The text is now stored in an invisible clipboard. Move your cursor to where you want the text to appear, then press <strong>Ctrl</strong> and <strong>V</strong> to paste it. If you make a mistake, press <strong>Ctrl</strong> and <strong>Z</strong> to undo the last action.</p>

<h2>Worked Example: Creating a Simple Letter</h2>
<p>Mr Phiri wants to write a short letter to the head teacher of his child's school in Kalomo. He opens LibreOffice Writer on the college computer and follows these steps:</p>
<ol>
<li>He clicks the Writer icon to open the program. A blank page appears.</li>
<li>He types the date, then presses Enter twice to create a gap.</li>
<li>He types "Dear Head Teacher," and presses Enter again.</li>
<li>He writes two paragraphs about his child's attendance and asks for a meeting.</li>
<li>He realises he spelled "attendance" incorrectly. He double-clickes the word to select it, types the correct spelling, and the old word is replaced automatically.</li>
<li>He clicks File, then Save As, chooses the Desktop, names the file "Letter_to_Head_Teacher," and clicks Save.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Open LibreOffice Writer or Notepad on the computer in front of you.</li>
<li>Type your full name, your village or area in Kalomo District, and your phone number on three separate lines.</li>
<li>Select your name, copy it with Ctrl+C, and paste it at the bottom of the page with Ctrl+V.</li>
<li>Practice undoing your last action with Ctrl+Z, then redo it with Ctrl+Y.</li>
<li>Save the document to the Desktop with the filename "My_Practice_Document."</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Cursor</strong> — the blinking line or arrow on the screen that shows where your typing will appear.</li>
<li><strong>Double-click</strong> — pressing the left mouse button twice quickly to open an item.</li>
<li><strong>Right-click</strong> — pressing the right mouse button to open a menu of extra options.</li>
<li><strong>Copy and paste</strong> — duplicating text or objects from one place to another using the clipboard.</li>
<li><strong>Undo</strong> — reversing the last action you performed, usually with Ctrl+Z.</li>
</ul>

<h2>Summary</h2>
<p>Typing, clicking, and basic editing are the foundation of almost everything you do on a computer. Once you are comfortable with the keyboard and mouse, you can write letters, fill in forms, search the internet, and complete college assignments. Practise the copy, paste, and undo shortcuts until they feel automatic, because you will use them hundreds of times in your digital life.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.libreoffice.org/get-help/">LibreOffice Help and Documentation</a></li>
<li><a href="https://learn.microsoft.com/en-us/windows/keyboard-layout/">Microsoft Learn — Keyboard Shortcuts</a></li>
<li><a href="https://www.w3schools.com/typing/">W3Schools — Typing Tutorial</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.4 Keeping Your Device Safe and Clean',
                'duration_minutes' => 45,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to clean your computer and smartphone safely, protect devices from power surges during load-shedding, perform basic software maintenance, and adopt simple habits that keep your equipment working well for years.</p>

<h2>Physical Cleaning</h2>
<p>Dust, dirt, and food crumbs can damage keyboards, block cooling vents, and scratch screens. Cleaning your devices regularly is a form of maintenance that costs almost nothing but prevents expensive repairs. Use a soft, dry microfiber cloth to wipe screens. Do not spray water or cleaning liquid directly onto the screen; instead, dampen the cloth slightly and then wipe. For keyboards, turn the keyboard upside down and gently tap it to dislodge debris. A small brush or a can of compressed air can remove dust from between the keys.</p>
<p>On a smartphone, the screen collects fingerprints and oils from your hands. Wipe it daily with a clean, soft cloth. If you use a phone case, remove it once a week and clean both the case and the back of the phone. Avoid using harsh chemicals such as bleach or strong detergents, because they can damage the protective coating on screens.</p>

<h2>Protecting Against Power Surges</h2>
<p>In Zambia, power fluctuations are common, especially when electricity returns after load-shedding. A sudden spike in voltage can burn out a computer's power supply or damage the motherboard. The safest rule is to <strong>unplug your computer from the wall socket when the power goes off</strong>. When electricity returns, wait five to ten minutes before plugging it back in, because the initial surge is often the strongest. If you can afford a surge protector or an uninterruptible power supply, these devices absorb excess voltage and protect your equipment.</p>
<p>For laptops, remove the charger during load-shedding and run on battery power. This not only protects the laptop from surges but also lets you continue working for a few hours. Remember to charge the laptop fully whenever electricity is available.</p>

<h2>Software Maintenance</h2>
<p>Keeping your software up to date is just as important as physical cleaning. Updates fix security holes, improve performance, and add new features. On Windows, open <strong>Settings &gt; Update &amp; Security &gt; Windows Update</strong> and check for updates at least once a month. On an Android phone, open <strong>Settings &gt; System &gt; System Update</strong> to check for the latest version.</p>
<p>You should also keep your apps updated. In the Google Play Store, tap your profile picture, then <strong>Manage apps &amp; device</strong>, and tap <strong>Update all</strong> to install the latest versions. Updated apps are less likely to contain security vulnerabilities that attackers could exploit.</p>

<h2>Basic Security Habits</h2>
<p>Always lock your phone with a PIN, password, or fingerprint. If you leave your phone unattended in a classroom or market, an unlocked device is an open invitation to thieves. On a shared college computer, log out of your accounts before you leave and do not save passwords in the browser unless it is your personal laptop. Keep drinks away from keyboards. A single cup of tea spilled into a laptop can destroy it instantly. If you must have a drink nearby, use a bottle with a tight lid and place it on the floor.</p>

<h2>Worked Example: The Safe Shutdown</h2>
<p>Mrs Zulu finishes her class at Edutrack College and needs to leave before load-shedding begins. She follows this safe routine:</p>
<ol>
<li>She saves her work in LibreOffice Writer by pressing Ctrl+S.</li>
<li>She closes all open programs by clicking the X on each window.</li>
<li>She clicks the Start button, selects Power, and chooses Shut down. She waits until the screen goes completely black and the fan stops.</li>
<li>She unplugs the monitor and the CPU box from the wall socket.</li>
<li>She wipes the keyboard and mouse with a dry cloth before leaving.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Look at your smartphone or the computer in front of you. Identify any visible dust or dirt.</li>
<li>Clean the screen with a soft, dry cloth. If you are on a desktop, wipe the mouse and keyboard as well.</li>
<li>Check Settings on your phone or computer for any pending software updates. If you have a reliable internet connection, install them.</li>
<li>Practise shutting down the computer properly using the Start menu rather than the power button.</li>
<li>Organise your phone's home screen by grouping related apps into folders. Long-press an icon, drag it onto another, and name the folder.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Power surge</strong> — a sudden increase in electrical voltage that can damage electronic equipment.</li>
<li><strong>Load-shedding</strong> — planned electricity outages that occur when demand exceeds supply.</li>
<li><strong>Software update</strong> — a newer version of a program or operating system that fixes problems or adds features.</li>
<li><strong>Surge protector</strong> — a device that absorbs excess voltage to protect connected electronics.</li>
<li><strong>Shut down</strong> — turning the computer off properly through the operating system so files are not damaged.</li>
</ul>

<h2>Summary</h2>
<p>Taking care of your computer and smartphone involves both physical cleaning and digital maintenance. Wipe screens and keyboards gently, protect devices from power surges during load-shedding by unplugging them, install software updates regularly, and lock your phone with a PIN. These simple habits will extend the life of your equipment, keep your data safer, and save you money on repairs.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/android/answer/6174145">Google Android Help — Device Care</a></li>
<li><a href="https://learn.microsoft.com/en-us/windows/security/operating-system-security/device-management/">Microsoft Learn — Device Security</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
        ];
    }


    private function module2Lessons(): array
    {
        return [
            [
                'title' => '2.1 Understanding Files and Folders',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what files and folders are, recognise common file extensions, create and name folders on your computer, and organise your documents so that you can find them quickly whenever you need them.</p>

<h2>What Is a File?</h2>
<p>A file is a single collection of information stored on a computer. It could be a letter you typed, a photograph you took, a song you downloaded, or a spreadsheet of your chicken-rearing business accounts. Every file has a <strong>name</strong> and usually a <strong>file extension</strong> that tells the computer what kind of file it is. For example, a document might be called "My_Letter.docx," where ".docx" is the extension that tells the computer to open it with Microsoft Word or LibreOffice Writer. A photo called "Market_Day.jpg" uses the ".jpg" extension, which tells the computer it is an image.</p>
<p>Common file extensions you will see include:</p>
<ul>
<li><strong>.docx</strong> and <strong>.odt</strong> — word processing documents</li>
<li><strong>.pdf</strong> — portable documents that look the same on every device</li>
<li><strong>.xlsx</strong> and <strong>.ods</strong> — spreadsheets with rows and columns</li>
<li><strong>.jpg</strong> and <strong>.png</strong> — photographs and images</li>
<li><strong>.mp4</strong> — video files</li>
<li><strong>.txt</strong> — plain text files with no formatting</li>
</ul>

<h2>What Is a Folder?</h2>
<p>A folder is like a filing cabinet or a drawer in a real office. It holds related files together so they do not get lost among hundreds of other items. You can put folders inside other folders to create deeper levels of organisation. For example, you might have a main folder called "My_Business" that contains sub-folders named "Invoices," "Receipts," and "Stock_Lists." Inside the "Invoices" folder, you place every invoice file you create. This structure makes it easy to find the invoice from March without scrolling through unrelated photos and music files.</p>

<h2>Creating and Naming Folders</h2>
<p>On Windows, the easiest way to create a folder is to right-click on an empty area of the Desktop or inside another folder, select <strong>New &gt; Folder</strong>, and then type a name. Good names are short, clear, and free of special characters. Avoid using symbols such as / \ : * ? " &lt; &gt; | because Windows does not allow them in folder names. It is also wise to avoid spaces if you plan to share files online, because spaces can sometimes break web links. Instead of "My Business Files," write "My_Business_Files" using underscores.</p>

<h2>Moving, Copying, and Deleting Files</h2>
<p>To move a file from one folder to another, click and drag it. To copy a file so that it exists in two places at once, hold the <strong>Ctrl</strong> key while dragging. If you delete a file by mistake, it usually goes to the <strong>Recycle Bin</strong> on Windows. You can open the Recycle Bin, find the file, and restore it to its original location. However, if you empty the Recycle Bin or delete a file from a flash drive, the file is gone permanently, so always think twice before deleting.</p>

<h2>Worked Example: Organising a Small Business</h2>
<p>Mr Banda runs a side business selling dried fish and vegetables from his home in Kalomo. He decides to organise his computer files so that tax time is less stressful. He creates the following folder structure on his Desktop:</p>
<ul>
<li><strong>Banda_Business</strong> (main folder)
<ul>
<li><strong>2025_Invoices</strong> — contains PDF invoices sent to customers</li>
<li><strong>2025_Receipts</strong> — contains photos of paper receipts taken with his phone</li>
<li><strong>Stock_Lists</strong> — contains spreadsheets tracking how much fish and maize he has</li>
<li><strong>ZRA_Documents</strong> — contains his TPIN registration and tax forms</li>
</ul>
</li>
</ul>
<p>Now, when ZRA asks for his quarterly records, he opens the "ZRA_Documents" folder instead of searching the entire computer.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Right-click on your Desktop and create three new folders named "Personal," "College," and "Business."</li>
<li>Open LibreOffice Writer, type a short list of three things you learned in Module 1, and save the file inside the "College" folder as "My_Notes.txt".</li>
<li>Create a sub-folder inside "Business" called "Practice_Invoices."</li>
<li>Copy your "My_Notes.txt" file from the "College" folder into the "Personal" folder. Check that the original still exists in "College."</li>
<li>Rename one of your folders by right-clicking it and choosing Rename.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>File</strong> — a single piece of information stored on a computer, such as a document or image.</li>
<li><strong>File extension</strong> — the letters after the dot in a filename that identify the file type, for example .pdf or .jpg.</li>
<li><strong>Folder</strong> — a container used to group related files together on a computer.</li>
<li><strong>File path</strong> — the full address of a file showing which folders you must open to reach it.</li>
<li><strong>Recycle Bin</strong> — a temporary storage area for deleted files on Windows, from which you can restore them.</li>
</ul>

<h2>Summary</h2>
<p>Files and folders are the building blocks of digital organisation. A file is a single item of information, while a folder groups related files together. Using clear names, consistent folder structures, and safe practices for moving and deleting files will save you hours of frustration. Whether you are a student keeping college notes or a business owner storing ZRA records, good file management is a skill that pays off every day.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/windows/win32/shell/folders">Microsoft Learn — Working with Files and Folders</a></li>
<li><a href="https://support.google.com/android/answer/6187345">Google Android Help — Files and Storage</a></li>
<li><a href="https://www.w3schools.com/computer/computer_files_folders.asp">W3Schools — Files and Folders</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.2 Getting On the Internet and Searching with Google',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to open a web browser, navigate to a website using its address, perform effective searches on Google, evaluate whether a website is trustworthy, and bookmark useful pages so you can return to them later.</p>

<h2>What Is the Internet?</h2>
<p>The internet is a giant network of computers connected around the world. When you open a web browser such as Google Chrome and visit a website, your computer sends a request across the internet to another computer called a server. That server sends the website back to your screen. In Zambia, you access the internet through Wi-Fi at college, home, or cafés, or through mobile data on your Airtel or MTN SIM card. The internet allows you to read news, check exam results, sell goods, apply for government services, and study online courses such as this one.</p>

<h2>The Web Browser</h2>
<p>A web browser is the program you use to view websites. Google Chrome, Microsoft Edge, Mozilla Firefox, and Safari are all browsers. At Edutrack College, you will most likely use Google Chrome. The browser window has several important parts:</p>
<ul>
<li><strong>The address bar</strong> — the long box at the top where you type a website address, such as www.zra.org.zm. This is also called the URL bar.</li>
<li><strong>The search bar</strong> — on the Google homepage, this is the large box in the centre where you type keywords.</li>
<li><strong>Tabs</strong> — small labels at the top that let you open several websites at once without closing the first one.</li>
<li><strong>Bookmarks</strong> — saved links to websites you visit often, usually shown as small icons below the address bar.</li>
</ul>

<h2>Typing a Website Address</h2>
<p>If you already know the address of a website, click the address bar, type the address exactly, and press Enter. For example, to visit the Zambia Revenue Authority website, type <strong>www.zra.org.zm</strong>. Be careful with spelling. Scammers sometimes create fake websites with addresses that look almost identical to real ones, such as "zra-zambia.com" instead of the official domain. When in doubt, search for the organisation on Google and look for the official link.</p>

<h2>Searching with Google</h2>
<p>When you do not know the exact address, use Google to search. Open <strong>www.google.com</strong> or type your keywords directly into the Chrome address bar. The words you type are called <strong>search terms</strong> or <strong>keywords</strong>. The more specific your keywords, the better your results. For example, searching for "ZRA TPIN registration Zambia" will give you far more relevant links than simply typing "tax."</p>
<p>Here are tips for better searches:</p>
<ul>
<li>Use quotation marks to search for an exact phrase: "TEVETA registered college Kalomo"</li>
<li>Use a minus sign to exclude words: chickens -recipes</li>
<li>Add the word "official" or "government" when looking for state services.</li>
<li>Include the year if you want recent information: Zambian budget 2025</li>
</ul>

<h2>Evaluating Websites</h2>
<p>Not everything on the internet is true. Before you trust a website, ask these questions:</p>
<ul>
<li>Does the address end in .gov.zm, .org.zm, .edu, or another official domain?</li>
<li>Is the information recent? Check the date at the top or bottom of the page.</li>
<li>Does the site ask for your password or PIN? Official government sites will not ask for banking PINs by email or pop-up.</li>
<li>Are there spelling mistakes and sensational headlines? These are warning signs of unreliable or scam sites.</li>
</ul>

<h2>Worked Example: Finding Official Information</h2>
<p>Ms Chanda needs to register for a TPIN so she can file taxes for her small tailoring business. She follows these steps:</p>
<ol>
<li>Opens Google Chrome and types "ZRA TPIN registration Zambia official" into the search bar.</li>
<li>Looks at the results and identifies the link that starts with <strong>www.zra.org.zm</strong>.</li>
<li>Clicks the link and checks that a small padlock icon appears next to the address, meaning the connection is secure.</li>
<li>Navigates to the TPIN section, reads the requirements, and downloads the application form as a PDF.</li>
<li>Bookmarks the page by clicking the star icon in the address bar so she can return easily.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Open Google Chrome and type <strong>www.google.com</strong> into the address bar.</li>
<li>Search for "TEVETA registered colleges in Southern Province Zambia."</li>
<li>Look at the first five results. Click the one that seems most official and trustworthy.</li>
<li>Bookmark the Edutrack College website by clicking the star icon in the address bar.</li>
<li>Search for your own village or town in Zambia and find one interesting fact from a reliable source.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Browser</strong> — a program such as Google Chrome used to view websites.</li>
<li><strong>URL</strong> — the address of a website, for example www.zra.org.zm.</li>
<li><strong>Search engine</strong> — a service such as Google that helps you find websites by typing keywords.</li>
<li><strong>Bookmark</strong> — a saved link to a website that you can click to return instantly.</li>
<li><strong>Keyword</strong> — a word or phrase you type into a search engine to find information.</li>
</ul>

<h2>Summary</h2>
<p>The internet connects you to a world of information, but you need basic skills to use it safely and effectively. Open a browser, type a website address or search query, evaluate whether the site is trustworthy, and bookmark useful pages. With practice, you will be able to find official government forms, research college assignments, and verify business opportunities in minutes rather than hours.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/chrome/answer/95346">Google Chrome Help — Browse the Web</a></li>
<li><a href="https://support.google.com/websearch/answer/134479">Google Search Help — Search Tips</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.3 Sending and Receiving Email',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create a Gmail account, compose and send an email, reply to and forward messages, attach files, and follow basic email etiquette so that your messages look professional and are easy to read.</p>

<h2>What Is Email?</h2>
<p>Email, short for electronic mail, is a way of sending written messages from one person to another over the internet. Unlike SMS text messages, which are limited to a few hundred characters, an email can be as long as a letter and can include attachments such as documents, photos, and spreadsheets. Email is essential for modern business and education. A student applying for a bursary, a shop owner ordering stock from Lusaka, and a teacher communicating with parents all rely on email.</p>

<h2>Creating a Gmail Account</h2>
<p>Gmail is a free email service provided by Google. To create an account, open <strong>www.gmail.com</strong> in Chrome and click <strong>Create account</strong>. You will need to provide your first and last name, choose a username such as "michael.banda2025," and create a strong password. Google will ask for a phone number to verify your identity and to help you recover the account if you forget your password. Use your own phone number, because you will receive a code by SMS that you must enter to complete the setup.</p>
<p>Choose a username that looks professional, especially if you plan to use the account for job applications or business. A name like "john.chisala91" is better than "coolguyzambia99" because it tells the recipient who you are immediately.</p>

<h2>The Parts of an Email</h2>
<p>When you click <strong>Compose</strong> in Gmail, a new window appears with several fields:</p>
<ul>
<li><strong>To</strong> — the email address of the main recipient.</li>
<li><strong>Subject</strong> — a short description of what the email is about. Always include a clear subject so the recipient knows why you are writing.</li>
<li><strong>CC</strong> — stands for Carbon Copy. Use this field to send a copy to someone who needs to see the message but is not the main recipient. For example, you might email a supplier and CC your business partner.</li>
<li><strong>BCC</strong> — stands for Blind Carbon Copy. Recipients in the BCC field receive the email, but the other recipients cannot see their addresses. This is useful when emailing a group and protecting everyone's privacy.</li>
<li><strong>Body</strong> — the main text of your message.</li>
</ul>

<h2>Attaching Files</h2>
<p>To send a document or photo with your email, click the paperclip icon in the compose window. Choose the file from your computer. Gmail allows attachments up to 25 megabytes. If your file is larger, you can upload it to Google Drive and share a link instead. When you attach a file, mention it in the body of the email so the recipient knows to look for it. For example, write "Please find my assignment attached as a PDF."</p>

<h2>Email Etiquette</h2>
<p>Professional emails follow simple rules. Use a greeting such as "Dear Mr Phiri," or "Hello Team," rather than jumping straight into the message. Write in clear paragraphs and avoid typing everything in capital letters, because this looks like shouting. Check your spelling before sending. Sign off with your full name and, if appropriate, your phone number. Finally, always read your email one more time before clicking Send, because you cannot take it back.</p>

<h2>Worked Example: Emailing a College Assignment</h2>
<p>Grace has finished her essay and needs to submit it to her instructor. She composes the following email:</p>
<blockquote>
<p><strong>To:</strong> instructor@edutrackzambia.com</p>
<p><strong>Subject:</strong> Assignment Submission — Introduction to Digital Literacy</p>
<p>Dear Instructor,</p>
<p>I have completed the assignment for Module 2. Please find the attached PDF document named "Grace_Chiputa_Module2_Assignment.pdf".</p>
<p>If you have any questions or need clarification, you can reach me on +260 97X XXX XXX.</p>
<p>Kind regards,</p>
<p>Grace Chiputa</p>
</blockquote>

<h2>Try It Yourself</h2>
<ol>
<li>If you do not already have one, create a Gmail account at www.gmail.com.</li>
<li>Compose an email to yourself. Write "Practice Email" in the subject line and a short message in the body.</li>
<li>Attach a photo or document from your computer or phone.</li>
<li>Send the email, then open your inbox and reply to it.</li>
<li>Create a simple email signature in Gmail Settings that includes your name and phone number.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Inbox</strong> — the folder where new emails arrive.</li>
<li><strong>Compose</strong> — to write a new email message.</li>
<li><strong>Attachment</strong> — a file such as a document or photo sent along with an email.</li>
<li><strong>CC</strong> — Carbon Copy; sends a copy of the email to additional recipients.</li>
<li><strong>Spam</strong> — unwanted or junk emails, often sent in bulk by advertisers or scammers.</li>
</ul>

<h2>Summary</h2>
<p>Email is one of the most important communication tools for education and business in Zambia. Creating a Gmail account is free and straightforward. Always write a clear subject, use appropriate greetings, mention any attachments, and check your spelling before sending. With these habits, your emails will be read, respected, and answered promptly.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/mail/answer/8494">Gmail Help — Getting Started</a></li>
<li><a href="https://support.google.com/mail/answer/40111">Gmail Help — Attach Files</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.4 Online Safety and Avoiding Scams in Zambia',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to recognise common online scams that target Zambians, protect your personal information, verify whether a message or website is trustworthy, and know what to do if you suspect a scam.</p>

<h2>Why Online Safety Matters</h2>
<p>As more Zambians use smartphones, mobile money, and social media, criminals have moved their scams online. They send fake SMS messages promising prize money from radio stations, create fraudulent Facebook pages that look like real banks, and send emails designed to steal passwords. Falling for a scam can cost you money, compromise your identity, and put your friends and family at risk because scammers often use stolen accounts to spread their attacks further.</p>

<h2>Common Scams in Zambia</h2>
<p>Here are scams you are likely to encounter:</p>
<ul>
<li><strong>Fake prize messages</strong> — An SMS says you have won K5,000 from Zambezi FM or a lottery, but you must send K50 via MTN MoMo or Airtel Money to "claim" it. The prize does not exist. Once you send the money, the scammer disappears.</li>
<li><strong>Phishing emails</strong> — An email that looks like it comes from your bank or ZRA asks you to click a link and enter your password. The link leads to a fake website that steals your details.</li>
<li><strong>Social media impersonation</strong> — A scammer copies a friend's Facebook profile and sends you a message asking for an urgent loan.</li>
<li><strong>Fake job offers</strong> — A message promises high pay for little work, but asks you to pay a "registration fee" first.</li>
<li><strong>Romance scams</strong> — Someone builds a relationship with you online and eventually asks for money for a fake emergency.</li>
</ul>

<h2>How to Spot a Scam</h2>
<p>Scammers rely on urgency and emotion. They want you to act before you think. Look for these warning signs:</p>
<ul>
<li>Spelling mistakes and poor grammar in official-looking messages.</li>
<li>Requests for your PIN, password, or one-time code. No legitimate bank, mobile money provider, or government office will ever ask for these.</li>
<li>Urgent threats such as "Your account will close in one hour" or "Act now or lose everything."</li>
<li>Links that look odd when you hover over them. For example, a message claiming to be from ZRA might link to "zra-tax-help.com" instead of "www.zra.org.zm."</li>
<li>Offers that seem too good to be true, such as free money or guaranteed high returns on investment.</li>
</ul>

<h2>Protecting Yourself</h2>
<p>These habits will keep you safer:</p>
<ul>
<li>Never share your mobile money PIN, banking password, or OTP with anyone, even if they claim to be from the company.</li>
<li>Do not click links in unexpected emails or SMS messages. Instead, type the official website address into your browser manually.</li>
<li>Check that websites use HTTPS. Look for a padlock icon next to the address bar. While this is not a guarantee of safety, its absence is a red flag.</li>
<li>Keep your phone and computer software updated so that security holes are patched.</li>
<li>If a friend sends an unusual request for money by WhatsApp or Facebook, call them on a known phone number to verify before sending anything.</li>
</ul>

<h2>What to Do If You Are Targeted</h2>
<p>If you receive a suspicious message, do not reply, do not click any links, and do not send money. Delete the message. If you have already shared personal information or sent money, contact your mobile money provider or bank immediately. Report scams to the Zambia Police Cyber Crime Unit or to the relevant company. You can also warn friends and family so they do not fall for the same trick.</p>

<h2>Worked Example: Analysing a Suspicious SMS</h2>
<p>Mr Tembo receives the following message: "CONGRATULATIONS! You have won K5,000 from Zambezi FM lucky draw. Send K50 via MoMo to 097X XXX XXX to collect your prize." He analyses it step by step:</p>
<ol>
<li>He did not enter any competition, so he should not have won anything.</li>
<li>The message asks him to send money first, which is the opposite of how real prizes work.</li>
<li>The phone number is a personal line, not an official Zambezi FM business number.</li>
<li>There is no mention of how to verify the win on an official website.</li>
<li>He deletes the message and tells his neighbour, who received the same text.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Look through your SMS inbox and identify any message that promises money or asks for personal details.</li>
<li>Check the sender's phone number or email address. Does it match the organisation it claims to represent?</li>
<li>Visit a website you use often, such as a bank or ZRA. Check that the address starts with HTTPS and that a padlock icon is visible.</li>
<li>Write down three warning signs you will look for in every unexpected message from now on.</li>
<li>Talk to a family member about mobile money safety and explain why they should never share their PIN.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Phishing</strong> — a scam where criminals send fake messages to trick you into giving away passwords or personal information.</li>
<li><strong>Scam</strong> — a dishonest scheme designed to cheat people out of money or information.</li>
<li><strong>HTTPS</strong> — a secure version of website communication that encrypts data between your browser and the server.</li>
<li><strong>PIN</strong> — a secret personal identification number used to authorise transactions.</li>
<li><strong>Spam</strong> — unsolicited and often fraudulent messages sent in bulk.</li>
</ul>

<h2>Summary</h2>
<p>Online scams are a serious risk for Zambian internet users, but you can protect yourself by staying alert, refusing to share sensitive information, and verifying unexpected requests through trusted channels. Remember that no legitimate organisation will ask for your PIN or password by SMS or email. When in doubt, slow down, ask questions, and seek advice from someone you trust.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/accounts/answer/6294825">Google Help — Avoid Phishing</a></li>
<li><a href="https://www.cisco.com/c/en/us/training-events/networking-academy.html">Cisco Networking Academy — Cybersecurity Essentials</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
        ];
    }


    private function module3Lessons(): array
    {
        return [
            [
                'title' => '3.1 Google Tools for Study and Small Business',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create and use a Google account, write documents in Google Docs, build simple budgets in Google Sheets, store and share files in Google Drive, and apply these tools to real situations such as school assignments and small business record-keeping in Zambia.</p>

<h2>Why Google Tools?</h2>
<p>Google provides a free suite of productivity tools that work on any device with an internet connection. This is especially valuable in Zambia, where many people access the internet primarily through smartphones rather than laptops. With Google Docs, you can write an essay on a college computer during the day and continue editing it on your phone in the evening. With Google Sheets, you can track sales for your market stall without buying expensive accounting software. With Google Drive, you can keep copies of important documents such as your NRC, TPIN certificate, and school results so that they are safe even if your phone is lost or damaged.</p>

<h2>Google Docs</h2>
<p>Google Docs is a word processor similar to Microsoft Word and LibreOffice Writer. It runs inside your web browser, so there is nothing to install. To start, open <strong>docs.google.com</strong>, sign in with your Google account, and click the blank document template. You can type, format headings, insert images, and create tables just like in any other word processor. The biggest advantage is <strong>autosave</strong>: your work is saved to the internet every few seconds, so a power cut or a flat battery will not erase your essay.</p>
<p>Docs also supports <strong>collaboration</strong>. If you are working on a group assignment, you can share the document with your classmates and see each other's changes in real time. This is much easier than emailing versions back and forth.</p>

<h2>Google Sheets</h2>
<p>Google Sheets is a spreadsheet program. A spreadsheet organises information into rows and columns, making it perfect for budgets, stock lists, and timetables. Each box in the grid is called a <strong>cell</strong>. You can type text, numbers, or formulas into cells. For example, if you sell tomatoes and dried fish at Soweto Market, you can create a simple spreadsheet with columns for Date, Item, Quantity Sold, Price per Unit, and Total Income. At the bottom of the Total Income column, you can enter a formula such as <code>=SUM(E2:E20)</code> to add up every sale automatically.</p>
<p>Spreadsheets save hours of manual calculation and reduce mistakes. If you change one price, every total that depends on it updates instantly.</p>

<h2>Google Drive</h2>
<p>Google Drive is online storage space for your files. Every Google account comes with 15 gigabytes of free storage. You can upload documents, photos, videos, and PDFs from your computer or phone. Once a file is in Drive, you can access it from any device by signing into your Google account. You can also share files with other people by sending them a link. This is useful when you need to submit an assignment to an instructor or send a price list to a customer.</p>

<h2>Worked Example: A Chicken-Rearing Budget</h2>
<p>Mrs Nkhoma runs a small chicken-rearing business in Kalomo. She wants to know whether she is making a profit each month. She opens Google Sheets on her phone and creates the following simple budget:</p>
<table>
<tr><th>Item</th><th>Income (K)</th><th>Expense (K)</th></tr>
<tr><td>Egg sales</td><td>800</td><td></td></tr>
<tr><td>Chicken sales</td><td>600</td><td></td></tr>
<tr><td>Feed</td><td></td><td>450</td></tr>
<tr><td>Medicine</td><td></td><td>150</td></tr>
<tr><td>Transport</td><td></td><td>100</td></tr>
<tr><td><strong>Total</strong></td><td><strong>=SUM(B2:B3)</strong></td><td><strong>=SUM(C4:C6)</strong></td></tr>
<tr><td><strong>Profit</strong></td><td colspan="2"><strong>=B7-C7</strong></td></tr>
</table>
<p>At the end of the month, she simply updates the numbers and the profit calculates automatically. She saves the spreadsheet to Google Drive so she can show it to her husband or to a loan officer at the bank.</p>

<h2>Try It Yourself</h2>
<ol>
<li>If you do not have one, create a free Google account at www.google.com.</li>
<li>Open Google Docs and write a short paragraph about your favourite subject at college.</li>
<li>Open Google Sheets and create a simple budget with at least three income items and three expense items. Use a SUM formula to calculate totals.</li>
<li>Upload a photo or document from your phone to Google Drive.</li>
<li>Share the Google Doc with a classmate or family member by clicking the Share button and entering their email address.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Cloud storage</strong> — saving files on the internet so you can access them from any device.</li>
<li><strong>Spreadsheet</strong> — a digital grid of rows and columns used for organising numbers and calculations.</li>
<li><strong>Collaboration</strong> — working together with other people on the same document at the same time.</li>
<li><strong>Template</strong> — a pre-designed document that gives you a ready-made structure to fill in.</li>
<li><strong>Sharing</strong> — giving other people permission to view or edit your files.</li>
</ul>

<h2>Summary</h2>
<p>Google Docs, Sheets, and Drive are powerful, free tools that help students and business owners in Zambia work more efficiently. Docs handles writing, Sheets handles numbers and budgets, and Drive keeps everything backed up online. Because they run in a web browser, you can use them on a college computer, a smartphone, or a borrowed tablet. Learning these tools now will save you time and money throughout your digital life.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/docs/answer/88438">Google Docs Help — Get Started</a></li>
<li><a href="https://support.google.com/docs/answer/1218656">Google Sheets Help — Create and Edit</a></li>
<li><a href="https://support.google.com/drive/answer/2424384">Google Drive Help — Store Files</a></li>
<li><a href="https://grow.google/">Grow with Google — Free Training</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.2 Mobile Money and Safe Online Payments',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain how mobile money works in Zambia, register for Airtel Money or MTN MoMo, send money and buy services safely, understand transaction fees, and protect your PIN from fraudsters.</p>

<h2>What Is Mobile Money?</h2>
<p>Mobile money is a service that turns your mobile phone into a digital wallet. Instead of carrying cash, you can store money electronically on your SIM card and use it to send funds to other people, pay bills, buy airtime, purchase ZESCO electricity tokens, and even pay for goods in shops. In Zambia, the two largest mobile money providers are <strong>Airtel Money</strong> and <strong>MTN Mobile Money</strong>, commonly called MoMo. These services have transformed daily life, especially in areas where banks are far away. A farmer in a rural village can receive payment for maize directly on his phone, and a market trader in Kalomo can pay suppliers in Lusaka within seconds.</p>

<h2>Registering for Mobile Money</h2>
<p>To use mobile money, you need a registered SIM card from Airtel or MTN. Registration usually requires visiting an authorised agent with your National Registration Card. The agent will help you create a mobile money account and set a secret <strong>PIN</strong>, which is a four- or five-digit number that authorises every transaction. Choose a PIN that is not easy to guess. Avoid birthdays, simple sequences such as 1234, or the same digits repeated. Memorise your PIN and never write it down in your phone or share it with anyone, including friends, family, or people claiming to be from the mobile money company.</p>

<h2>Common Transactions</h2>
<p>Once registered, you can perform several useful transactions directly from your phone:</p>
<ul>
<li><strong>Send money</strong> — Transfer funds to another mobile money user by entering their phone number and the amount, then confirming with your PIN.</li>
<li><strong>Buy airtime or data</strong> — Purchase credit for your own phone or send it to someone else.</li>
<li><strong>Buy ZESCO tokens</strong> — Pay for electricity tokens by entering your meter number and the amount you wish to buy.</li>
<li><strong>Pay bills</strong> — Settle water, television, or insurance bills through the mobile money menu.</li>
<li><strong>Cash-in and cash-out</strong> — Deposit physical cash into your mobile wallet at an agent, or withdraw cash from your wallet.</li>
</ul>

<h2>Understanding Fees</h2>
<p>Mobile money is convenient, but it is not always free. Providers charge small fees for sending money, withdrawing cash, and paying certain bills. These fees are usually deducted automatically from the amount you send or from your wallet balance. Before you complete a transaction, the system displays the fee so you can decide whether to continue. Over time, frequent small fees can add up, so it is wise to plan your transactions. For example, sending one large amount to a supplier may cost less in fees than sending several small amounts across the week.</p>

<h2>Keeping Your PIN Safe</h2>
<p>Your PIN is the key to your mobile money wallet. If a fraudster learns it, they can empty your account in minutes. Follow these rules:</p>
<ul>
<li>Never tell your PIN to anyone, even if they claim to be a mobile money agent or company employee.</li>
<li>Do not enter your PIN in response to a phone call, SMS, or pop-up message.</li>
<li>Shield the keypad when typing your PIN in public.</li>
<li>Change your PIN occasionally, especially if you think someone may have seen it.</li>
<li>If your phone is stolen, report it to your mobile network and mobile money provider immediately so they can block the account.</li>
</ul>

<h2>Worked Example: Buying ZESCO Tokens</h2>
<p>Mr Mutale needs electricity tokens before evening load-shedding. He uses Airtel Money on his Android phone:</p>
<ol>
<li>He dials the Airtel Money USSD code or opens the Airtel Money app.</li>
<li>He selects "Pay Bill" and then "ZESCO."</li>
<li>He enters his prepaid meter number carefully, double-checking each digit.</li>
<li>He enters K50 as the amount.</li>
<li>The system shows the total cost including the transaction fee. He confirms.</li>
<li>He enters his PIN when prompted.</li>
<li>Within seconds he receives an SMS containing the 20-digit token number.</li>
<li>He enters the token number into his ZESCO meter and the units are added.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>If you have a mobile money account, check your current balance and note it down.</li>
<li>Review your last three transactions in the mobile money menu or app. Do the amounts match what you remember?</li>
<li>Practise buying airtime for your own phone using mobile money. Start with a small amount such as K5.</li>
<li>Ask a trusted friend or family member if they use mobile money. Discuss how you would both react if someone asked for your PIN.</li>
<li>Write down the customer care number for your mobile money provider and save it in your phone contacts.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Mobile money</strong> — a digital wallet service linked to your mobile phone number.</li>
<li><strong>PIN</strong> — a secret number you enter to authorise mobile money transactions.</li>
<li><strong>Agent</strong> — an authorised person or shop where you can deposit or withdraw cash for mobile money.</li>
<li><strong>Transaction fee</strong> — a small charge deducted when you send, withdraw, or pay using mobile money.</li>
<li><strong>Token</strong> — a code purchased for ZESCO prepaid meters that adds electricity units to your home.</li>
</ul>

<h2>Summary</h2>
<p>Mobile money is one of the most important financial tools for Zambians today. It allows you to send money, pay bills, buy airtime, and purchase ZESCO tokens from your phone. Register with an authorised agent, memorise your PIN, and never share it with anyone. Always check transaction fees before confirming a payment, and review your transaction history regularly. With these precautions, mobile money is a safe and powerful way to manage your finances.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/android/answer/6187345">Google Android Help — Security</a></li>
<li><a href="https://grow.google/">Grow with Google — Digital Skills</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.3 E-Government Services in Zambia',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe what e-government means, access the eNAPSA and ZRA online portals, understand the purpose of a TPIN, and use digital channels to save time and money when interacting with government services in Zambia.</p>

<h2>What Is E-Government?</h2>
<p>E-government means using the internet and digital technology to deliver government services to citizens and businesses. Instead of travelling to Lusaka or waiting in long queues at a government office, you can complete tasks online from your home, college, or local internet café. Zambia has made significant progress in this area. Today you can check your national pension contributions, register a business, file tax returns, and access forms without visiting a physical office. This saves transport money, reduces lost work hours, and helps rural citizens participate fully in national systems.</p>

<h2>eNAPSA: Checking Your Pension Contributions</h2>
<p>The National Pension Scheme Authority, known as NAPSA, manages retirement savings for employed Zambians. Every month, your employer deducts a percentage of your salary and contributes it to NAPSA. It is important to verify that these contributions are actually being paid, because your future pension depends on them.</p>
<p>The <strong>eNAPSA portal</strong> allows you to log in, view your contribution history, and download statements. To register, you typically need your NRC number and some personal details. Once registered, you can check whether your employer has been paying on time and whether the amounts are correct. If you spot a missing month, you can raise the issue with your employer or NAPSA directly.</p>

<h2>ZRA Online: Taxes and the TPIN</h2>
<p>The Zambia Revenue Authority collects taxes that fund public services such as roads, schools, and hospitals. Every taxpayer and business needs a <strong>Taxpayer Identification Number</strong>, or <strong>TPIN</strong>. This number is like an identity card for tax purposes. You need it to file returns, pay tax, and conduct many formal business transactions.</p>
<p>The ZRA website at <strong>www.zra.org.zm</strong> allows you to:</p>
<ul>
<li>Apply for a new TPIN online</li>
<li>File tax returns for income tax, VAT, and other categories</li>
<li>Check your tax account balance</li>
<li>Download forms and guidelines</li>
<li>Track the status of your submissions</li>
</ul>
<p>Using ZRA online is faster than visiting a tax office, and it creates a digital record of everything you submit. This is useful if you ever need to prove compliance for a loan application or a business tender.</p>

<h2>Other E-Government Services</h2>
<p>Beyond NAPSA and ZRA, Zambia offers other digital services:</p>
<ul>
<li><strong>Online business registration</strong> through the Patents and Companies Registration Agency.</li>
<li><strong>E-procurement portals</strong> for government tenders and contracts.</li>
<li><strong>Digital health services</strong> in some districts for appointment booking and health records.</li>
<li><strong>E-voucher systems</strong> for agricultural inputs under the Farmer Input Support Programme.</li>
</ul>

<h2>Worked Example: Checking NAPSA Contributions Online</h2>
<p>Ms Zulu has worked at a retail shop in Kalomo for two years. She wants to confirm that her boss has been paying NAPSA. She follows these steps:</p>
<ol>
<li>She opens Google Chrome on her phone and types <strong>enapsa.co.zm</strong> into the address bar.</li>
<li>She clicks "Register" and enters her NRC number, full name, date of birth, and phone number.</li>
<li>She creates a password and verifies her account via an SMS code.</li>
<li>She logs in and navigates to "Contribution History."</li>
<li>She sees a table showing every month her employer paid, the amount deducted, and the employer's contribution.</li>
<li>She notices one missing month, takes a screenshot, and shows it to her employer, who corrects the error.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Open your browser and visit the eNAPSA website. Read the homepage and note what services are offered.</li>
<li>Visit the ZRA website and find the page that explains how to apply for a TPIN. Write down the documents you would need.</li>
<li>Search online for "Zambia e-government services 2025" and identify one digital service you did not know about before.</li>
<li>Ask three employed adults you know whether they have ever checked their NAPSA contributions online. Share what you learned.</li>
<li>Bookmark both the eNAPSA and ZRA websites in your browser for future reference.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>E-government</strong> — the delivery of government information and services through digital channels.</li>
<li><strong>TPIN</strong> — Taxpayer Identification Number; a unique number assigned by ZRA for tax purposes.</li>
<li><strong>eNAPSA</strong> — the online portal for checking National Pension Scheme Authority contributions.</li>
<li><strong>Portal</strong> — a website that serves as a gateway to specific services or information.</li>
<li><strong>Contribution</strong> — a payment made into a pension or social security fund.</li>
</ul>

<h2>Summary</h2>
<p>E-government services in Zambia are making it easier for citizens and businesses to interact with the state. Through eNAPSA you can verify your pension contributions, and through ZRA online you can register for taxes, file returns, and manage your business obligations. These digital tools save time, reduce travel costs, and create transparent records. As Zambia continues to expand its digital infrastructure, citizens who are comfortable using these portals will have a clear advantage in managing their legal and financial affairs.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.zra.org.zm/">Zambia Revenue Authority Official Website</a></li>
<li><a href="https://www.enapsa.co.zm/">eNAPSA Portal</a></li>
<li><a href="https://grow.google/">Grow with Google — Digital Skills Training</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.4 Building Your Digital Future',
                'duration_minutes' => 45,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to review the digital skills you have gained in this course, identify how they apply to real opportunities in Zambia, set personal goals for continued learning, and find free resources to keep improving your digital literacy long after the course ends.</p>

<h2>Looking Back at What You Have Learned</h2>
<p>You began this course as an absolute beginner, and now you understand the parts of a computer, how to navigate an Android smartphone, how to organise files and folders, how to browse the internet safely, how to send professional emails, how to use Google Docs and Sheets, how to manage mobile money securely, and how to access e-government services. These skills are not abstract. They are practical tools that solve everyday problems for people in Kalomo, Lusaka, and every district in between.</p>

<h2>Applying Your Skills in Real Life</h2>
<p>Let us look at how your new abilities can improve specific situations:</p>
<ul>
<li><strong>A student</strong> can use Google Docs to write assignments, Google Drive to back them up, and email to submit them to teachers on time. No more lost notebooks or smudged ink.</li>
<li><strong>A market vendor</strong> can use Google Sheets to track daily sales and expenses, mobile money to receive payments without handling large amounts of cash, and WhatsApp to share price lists with regular customers.</li>
<li><strong>A parent</strong> can use email to communicate with school staff, eNAPSA to check pension contributions, and ZRA online to manage a small family business.</li>
<li><strong>A farmer</strong> can search online for weather forecasts, crop prices, and agricultural advice, then save the best articles in a bookmarks folder.</li>
<li><strong>An aspiring professional</strong> can create a simple CV in Google Docs, save it as a PDF, and email it to employers with a clear subject line and polite message.</li>
</ul>

<h2>Setting Your Digital Goals</h2>
<p>Learning does not stop when the course ends. Technology changes constantly, and the people who keep learning are the ones who benefit most. Set yourself two or three specific goals for the next six months. For example:</p>
<ol>
<li>I will practise typing for fifteen minutes every week until I can type without looking at the keyboard.</li>
<li>I will create a Google Sheets budget for my personal or business finances and update it every Sunday.</li>
<li>I will complete one free online course from Grow with Google or Cisco Networking Academy to deepen my skills.</li>
</ol>
<p>Write your goals down and put them somewhere visible, such as on your phone's home screen or above your desk.</p>

<h2>Staying Safe as You Grow</h2>
<p>As you use more digital tools, you will encounter more risks. Scammers improve their tricks every year. Keep the safety habits from Module 2 at the front of your mind. Always verify unexpected requests, never share your PIN or passwords, and update your devices regularly. If something feels wrong, pause and ask for advice. There is no shame in double-checking; there is only wisdom.</p>

<h2>Building an Online Presence</h2>
<p>In the modern world, having a positive online presence can open doors. This does not mean you need to be famous. It simply means that when someone searches your name or business, they find accurate and helpful information. Consider creating a simple Facebook page for your business, listing your services, contact number, and location. If you offer professional skills such as tailoring, carpentry, or tutoring, a clean Google Doc CV and a polite email address can help you find work that might otherwise go to someone less prepared.</p>

<h2>Worked Example: A Six-Month Digital Plan</h2>
<p>Mr Bwalya has just completed the Certificate in Digital Literacy. He writes the following plan:</p>
<table>
<tr><th>Month</th><th>Goal</th><th>How He Will Do It</th></tr>
<tr><td>1</td><td>Organise all business records</td><td>Create folders on laptop; scan paper receipts</td></tr>
<tr><td>2</td><td>Set up mobile money for customers</td><td>Register Airtel Money; display payment number at stall</td></tr>
<tr><td>3</td><td>Start a business Facebook page</td><td>Post photos of products three times per week</td></tr>
<tr><td>4</td><td>Learn basic spreadsheets</td><td>Use Google Sheets to track stock and profit</td></tr>
<tr><td>5</td><td>Apply for TPIN online</td><td>Visit ZRA website; complete registration</td></tr>
<tr><td>6</td><td>Email three potential suppliers</td><td>Write professional emails with clear subjects</td></tr>
</table>

<h2>Try It Yourself</h2>
<ol>
<li>List three skills from this course that you did not have two weeks ago.</li>
<li>Write two specific goals for how you will use your digital skills in the next three months.</li>
<li>Find one free online course or tutorial from the Free Resources list and commit to starting it within seven days.</li>
<li>Share one thing you learned with a friend or family member who does not use computers yet. Teach them patiently.</li>
<li>Create a simple document titled "My Digital Future" that lists your goals, your strengths, and the resources you will use. Save it to Google Drive.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Digital literacy</strong> — the ability to use digital technology confidently and safely to find, create, and communicate information.</li>
<li><strong>Continuous learning</strong> — the habit of regularly acquiring new knowledge and skills throughout your life.</li>
<li><strong>Online profile</strong> — the information about you or your business that appears on the internet.</li>
<li><strong>Networking</strong> — building relationships with other people for mutual support and opportunity.</li>
<li><strong>Goal</strong> — a specific target you set for yourself to achieve within a defined period.</li>
</ul>

<h2>Summary</h2>
<p>This course has given you a strong foundation in digital literacy, but your journey is just beginning. The skills you have learned can improve your studies, your business, your finances, and your access to government services. Set clear goals, practise regularly, stay alert to online scams, and keep exploring free learning resources. Zambia's digital future is growing every day, and with commitment and curiosity, you can grow right along with it.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/">Grow with Google — Free Digital Skills Training</a></li>
<li><a href="https://www.khanacademy.org/">Khan Academy — Free Courses in Many Subjects</a></li>
<li><a href="https://www.cisco.com/c/en/us/training-events/networking-academy.html">Cisco Networking Academy — Skills for All</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn">MDN Web Docs — Learn Web Development</a></li>
</ul>
HTML,
            ],
        ];
    }


    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: Computers and Smartphones',
            'description' => 'Test your knowledge of computer parts, smartphone basics, typing, mouse controls, and device safety.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which part of the computer displays pictures and words?',
                    'explanation' => 'The monitor is the screen that shows you everything the computer produces, from text documents to videos.',
                    'options' => [
                        ['text' => 'Monitor', 'is_correct' => true],
                        ['text' => 'Keyboard', 'is_correct' => false],
                        ['text' => 'Mouse', 'is_correct' => false],
                        ['text' => 'Speaker', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main job of the CPU?',
                    'explanation' => 'The CPU, or Central Processing Unit, is the electronic brain of the computer that carries out instructions and performs calculations.',
                    'options' => [
                        ['text' => 'Stores files forever', 'is_correct' => false],
                        ['text' => 'Shows images on screen', 'is_correct' => false],
                        ['text' => 'Processes instructions and calculations', 'is_correct' => true],
                        ['text' => 'Connects to Wi-Fi', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which button is most commonly used to turn on an Android smartphone?',
                    'explanation' => 'The power button, usually on the side of the phone, wakes the screen or starts the phone when pressed and held.',
                    'options' => [
                        ['text' => 'Home button', 'is_correct' => false],
                        ['text' => 'Power button', 'is_correct' => true],
                        ['text' => 'Volume down', 'is_correct' => false],
                        ['text' => 'Back button', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the small arrow on the screen that you move with a mouse called?',
                    'explanation' => 'The cursor, also called the pointer, shows where your mouse actions will take effect on the screen.',
                    'options' => [
                        ['text' => 'Icon', 'is_correct' => false],
                        ['text' => 'Cursor/Pointer', 'is_correct' => true],
                        ['text' => 'Folder', 'is_correct' => false],
                        ['text' => 'Tab', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'You should always turn off a Windows computer by holding the power button until the screen goes black.',
                    'explanation' => 'Holding the power button forces an abrupt shutdown that can damage open files. Always use Start > Power > Shut down instead.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A smartphone can connect to the internet using either Wi-Fi or mobile data.',
                    'explanation' => 'Smartphones support both Wi-Fi connections through wireless routers and mobile data through cellular networks such as Airtel and MTN.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the name of the small picture you double-click to open a program? (one word)',
                    'explanation' => 'An icon is a small image on the screen that represents a program, file, or folder. Double-clicking it opens the item.',
                    'correct_answer' => 'Icon',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of these is NOT a common port or connection on a computer?',
                    'explanation' => 'ZESCO provides mains electricity through wall sockets, not data connections. USB, HDMI, and Ethernet are standard computer ports.',
                    'options' => [
                        ['text' => 'USB port', 'is_correct' => false],
                        ['text' => 'HDMI port', 'is_correct' => false],
                        ['text' => 'Ethernet port', 'is_correct' => false],
                        ['text' => 'ZESCO socket', 'is_correct' => true],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'During load-shedding, what is the safest thing to do with your computer?',
                    'explanation' => 'Unplugging the computer protects it from power surges that often occur when electricity returns after an outage.',
                    'options' => [
                        ['text' => 'Pour water on it to cool it', 'is_correct' => false],
                        ['text' => 'Unplug it from the wall socket', 'is_correct' => true],
                        ['text' => 'Leave it running on battery', 'is_correct' => false],
                        ['text' => 'Turn the brightness to maximum', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Files, Folders, and the Internet',
            'description' => 'Test your knowledge of file management, web browsing, email, and online safety.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does a file extension such as .pdf tell you?',
                    'explanation' => 'A file extension indicates the type of file and which program can open it, helping the computer handle the file correctly.',
                    'options' => [
                        ['text' => 'The file size in MB', 'is_correct' => false],
                        ['text' => 'The type of file and what program opens it', 'is_correct' => true],
                        ['text' => 'The name of the folder', 'is_correct' => false],
                        ['text' => 'The date it was created', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which program is used to browse websites on the internet?',
                    'explanation' => 'Google Chrome is a web browser, a program designed specifically to display websites and navigate the internet.',
                    'options' => [
                        ['text' => 'Microsoft Word', 'is_correct' => false],
                        ['text' => 'Google Chrome', 'is_correct' => true],
                        ['text' => 'Windows File Explorer', 'is_correct' => false],
                        ['text' => 'Calculator', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the first page you usually see when you visit a website called?',
                    'explanation' => 'The home page is the main entry point of a website, the page that loads first when you type the site\'s address.',
                    'options' => [
                        ['text' => 'Home page', 'is_correct' => true],
                        ['text' => 'Index page', 'is_correct' => false],
                        ['text' => 'Search page', 'is_correct' => false],
                        ['text' => 'Blog page', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Gmail is a free email service provided by Google.',
                    'explanation' => 'Gmail is indeed a free email service run by Google. Anyone with a Google account can use it at no cost.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'It is safe to open an email attachment from a sender you do not recognise.',
                    'explanation' => 'Unknown attachments may contain malware or lead to phishing sites. Only open attachments from trusted senders.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which search query is most likely to lead you to the official ZRA website?',
                    'explanation' => 'Specific, official keywords such as "ZRA Zambia official" help search engines return the genuine government website.',
                    'options' => [
                        ['text' => 'buy cheap phones', 'is_correct' => false],
                        ['text' => 'ZRA Zambia official', 'is_correct' => true],
                        ['text' => 'football scores today', 'is_correct' => false],
                        ['text' => 'Kalomo weather', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'In an email, what do the letters "CC" stand for? (two words)',
                    'explanation' => 'CC stands for Carbon Copy. It sends a copy of the email to additional recipients so they are informed.',
                    'correct_answer' => 'Carbon Copy',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'On a computer, a folder is most similar to which real-world office item?',
                    'explanation' => 'A folder groups related files together, just as a filing cabinet or drawer groups related paper documents.',
                    'options' => [
                        ['text' => 'A filing cabinet or drawer', 'is_correct' => true],
                        ['text' => 'A pen', 'is_correct' => false],
                        ['text' => 'A chair', 'is_correct' => false],
                        ['text' => 'A window', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a warning sign of a phishing message?',
                    'explanation' => 'Scammers often use poor spelling and create false urgency to pressure victims into acting without thinking.',
                    'options' => [
                        ['text' => 'Spelling mistakes and urgent threats', 'is_correct' => true],
                        ['text' => 'A familiar sender name only', 'is_correct' => false],
                        ['text' => 'A short message', 'is_correct' => false],
                        ['text' => 'A message sent during the day', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Digital Tools and E-Government',
            'description' => 'Test your knowledge of Google tools, mobile money, e-government services, and digital safety in Zambia.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which Google tool is best for creating a budget spreadsheet for your small business?',
                    'explanation' => 'Google Sheets is designed for organising numbers in rows and columns, making it ideal for budgets and calculations.',
                    'options' => [
                        ['text' => 'Google Docs', 'is_correct' => false],
                        ['text' => 'Google Sheets', 'is_correct' => true],
                        ['text' => 'Google Drive', 'is_correct' => false],
                        ['text' => 'Google Maps', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What must you have before you can send money using mobile money in Zambia?',
                    'explanation' => 'Mobile money is linked to your registered phone number and protected by a secret PIN that only you should know.',
                    'options' => [
                        ['text' => 'A laptop computer', 'is_correct' => false],
                        ['text' => 'A registered SIM card and your PIN', 'is_correct' => true],
                        ['text' => 'A printer', 'is_correct' => false],
                        ['text' => 'A ZESCO meter number', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of these services can you check online using the eNAPSA portal?',
                    'explanation' => 'The eNAPSA portal allows employed Zambians to view their National Pension Scheme contribution history and statements.',
                    'options' => [
                        ['text' => 'Your NAPSA contribution history', 'is_correct' => true],
                        ['text' => 'Your water bill from LWSC', 'is_correct' => false],
                        ['text' => 'Your secondary school grades', 'is_correct' => false],
                        ['text' => 'Your Zambezi FM subscription', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Google Drive allows you to store files online so you can open them from any device with internet.',
                    'explanation' => 'Google Drive is cloud storage, which means your files are saved on the internet and accessible from phones, tablets, and computers.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'If someone calls claiming to be from your mobile money provider and asks for your PIN, you should give it to them to fix your account.',
                    'explanation' => 'No legitimate mobile money agent or provider employee will ever ask for your PIN. This is always a scam.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'At ZRA, what is a TPIN mainly used for?',
                    'explanation' => 'The Taxpayer Identification Number is essential for registering with ZRA and filing tax returns in Zambia.',
                    'options' => [
                        ['text' => 'Paying radio station fees', 'is_correct' => false],
                        ['text' => 'Tax registration and filing returns', 'is_correct' => true],
                        ['text' => 'Buying ZESCO tokens', 'is_correct' => false],
                        ['text' => 'Applying for an NRC', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'Name one thing you can buy in Zambia using mobile money. (one word or short phrase)',
                    'explanation' => 'Mobile money is widely used to purchase airtime, electricity tokens, data bundles, and other goods and services.',
                    'correct_answer' => 'Airtime',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which Google service would help a customer find directions to your shop in Kalomo?',
                    'explanation' => 'Google Maps provides location data, directions, and navigation, making it the right choice for helping customers find a physical shop.',
                    'options' => [
                        ['text' => 'Google Maps', 'is_correct' => true],
                        ['text' => 'Google Sheets', 'is_correct' => false],
                        ['text' => 'Gmail', 'is_correct' => false],
                        ['text' => 'Google Docs', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'You receive an SMS saying you have won K5,000 and must send K50 to collect it. What should you do?',
                    'explanation' => 'This is a classic advance-fee scam. Legitimate prizes never require payment upfront. Delete the message and do not reply.',
                    'options' => [
                        ['text' => 'Send the K50 immediately via MoMo', 'is_correct' => false],
                        ['text' => 'Delete the message and do not reply', 'is_correct' => true],
                        ['text' => 'Share your PIN so they can deposit the prize', 'is_correct' => false],
                        ['text' => 'Forward the message to all your contacts', 'is_correct' => false],
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
            'title' => 'Organise Your Digital Workspace',
            'description' => 'Practice file management and internet search skills by organising folders and finding official information online.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: On the college computer or your own laptop, create three new folders on the Desktop named "Personal", "College", and "Business".
Step 2: Open a text editor (Notepad or LibreOffice Writer) and type a short list of three things you learned in Module 1. Save this file inside the "College" folder as "My_Notes.txt".
Step 3: Open Google Chrome and search for "ZRA TPIN registration Zambia".
Step 4: Identify the official ZRA website link (look for www.zra.org.zm).
Step 5: Write down the website address and one sentence about what a TPIN is used for.
Step 6: Save this information as a second document in the "Business" folder named "ZRA_Info.txt". Submit both text files in a ZIP folder, or take a screenshot of your Desktop showing the three folders and upload it.
INSTRUCTIONS,
            'max_points' => 100,
            'passing_points' => 50,
            'due_date' => null,
            'allow_late_submission' => 1,
            'allowed_file_types' => 'pdf,doc,docx,jpg,png,zip',
            'max_file_size_mb' => 10,
        ]);

        Assignment::create([
            'course_id' => $this->courseId,
            'lesson_id' => null,
            'title' => 'My Digital Toolkit for Zambia',
            'description' => 'Create a simple document describing three digital tools you have learned and how you would use them to improve a real business or study situation in Zambia.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Choose one of these scenarios: (a) You run a small market stall selling tomatoes and dried fish in Soweto Market, or (b) You are a member of a PTA WhatsApp group for your child's school in Kalomo.
Step 2: Open Google Docs or Microsoft Word and create a one-page document.
Step 3: List THREE digital tools from this course (for example: Google Sheets, Gmail, mobile money, Google Drive, email, file folders).
Step 4: For each tool, write two sentences explaining exactly how it would help your chosen scenario. Be specific: mention real activities like tracking stock, sending price lists, saving receipt photos, or emailing the head teacher.
Step 5: Save your document and export it as a PDF. Name the file "My_Digital_Toolkit.pdf". Upload the PDF file here.
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
        $this->command->info('Digital Literacy content seeded successfully.');
        $this->command->info('Modules: 3 | Lessons: 12 | Quizzes: 3 | Assignments: 2');
    }
}
