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

class CyberSecurityContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Cyber Security')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Cyber Security" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in Cyber Security already has modules. Skipping content seed.');
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
                'title' => 'Module 1: Understanding Cyber Threats in Zambia',
                'description' => 'Learn what cyber threats look like for ordinary Zambians and small businesses, and how attackers think.',
            ],
            [
                'title' => 'Module 2: Passwords, Accounts and Two-Factor Authentication',
                'description' => 'Build strong digital locks using passphrases, password managers and two-factor authentication.',
            ],
            [
                'title' => 'Module 3: Phishing, Mobile Money Fraud and Social Engineering',
                'description' => 'Recognise and stop phishing, MTN MoMo/Airtel Money scams, and social-engineering tricks.',
            ],
            [
                'title' => 'Module 4: Securing Phones, Windows PCs and Small Office Networks',
                'description' => 'Harden the devices and networks used in homes, shops and small offices across Zambia.',
            ],
            [
                'title' => 'Module 5: Data Protection, Zambian Law and Incident Response',
                'description' => 'Protect personal and business data, understand Zambia’s Cyber Security Act, and respond to breaches.',
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
                'title' => '1.1 What is Cyber Security and Why It Matters in Zambia',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain cyber security in plain language, identify why it matters for a Zambian household or small business, and list the three main goals every security plan must achieve: keeping information private, accurate and available when needed.</p>

<h2>What Is Cyber Security?</h2>
<p>Cyber security is the practice of protecting computers, phones, networks and data from theft, damage or misuse. It is not only for banks or big companies in Lusaka. A market trader in Soweto Market who accepts Airtel Money, a poultry farmer in Kalomo who keeps customer records on a smartphone, and a civil servant who files ZRA returns online all need cyber security.</p>
<p>Think of cyber security like locking your shop. You do not leave your cash box open overnight. In the same way, you should not leave your phone, laptop or online accounts open to attackers.</p>

<h2>Worked Example: A Civil Servant’s ZRA Login</h2>
<p>Mr Mumba works in accounts at a ministry office in Lusaka. Every month he logs into the ZRA e-filing portal using a shared office computer. He saves his password in the browser because it is convenient, and he rarely locks the computer when he steps out for tea.</p>
<p>One afternoon a visitor uses the unlocked computer to open the browser, sees the saved password, and submits a fake tax return that changes the company’s TPIN contact details. The ministry only discovers the fraud when genuine ZRA notices stop arriving. From that day, Mr Mumba:</p>
<ul>
<li>locks the computer every time he leaves the desk,</li>
<li>removes saved passwords from the browser and uses a password manager,</li>
<li>turns on two-factor authentication for the ZRA portal, and</li>
<li>logs out of e-filing at the end of each session.</li>
</ul>
<p>This example shows that cyber security is about habits as much as technology.</p>

<h2>Worked Example: A Small Shop Goes Digital</h2>
<p>Mrs Banda runs a grocery shop in Kalomo. She started selling on WhatsApp and now receives payments through MTN Mobile Money. Her phone stores:</p>
<ul>
<li>customer names and phone numbers,</li>
<li>supplier bank details,</li>
<li>daily sales totals, and</li>
<li>her Airtel Money PIN written in a note.</li>
</ul>
<p>One day her phone is stolen. Because the PIN was saved in a note, the thief withdraws ZMW 1,200 from her mobile money account and messages her customers asking for more money. A simple security habit—memorising the PIN and using app lock—would have prevented most of the damage.</p>

<h2>The CIA Triad</h2>
<p>Security professionals use three words to describe what they protect. You will see these throughout the course:</p>
<ul>
<li><strong>Confidentiality</strong> — only the right people can see the information. Example: your NRC number or TPIN.</li>
<li><strong>Integrity</strong> — information cannot be changed secretly. Example: your ZRA e-filing records or exam results.</li>
<li><strong>Availability</strong> — information and services are there when you need them. Example: being able to buy ZESCO tokens online during load-shedding.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>List three digital things you own or use (phone, email, mobile money account, Facebook page, etc.).</li>
<li>For each one, write down what would happen if a stranger gained access to it.</li>
<li>Decide which of the three CIA goals matters most for each item.</li>
<li>Put a screen lock on your phone today if you have not already done so.</li>
<li>Check whether any computer you use saves passwords in the browser and remove any that are not yours.</li>
<li>Ask a colleague or family member to name one digital asset they would hate to lose.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Cyber security</strong> — protecting devices, networks and data from harm.</li>
<li><strong>Confidentiality</strong> — keeping information away from unauthorised people.</li>
<li><strong>Integrity</strong> — keeping information accurate and unaltered.</li>
<li><strong>Availability</strong> — ensuring systems and data can be used when needed.</li>
<li><strong>Threat</strong> — any possible danger to your information or systems.</li>
<li><strong>Asset</strong> — anything of value that needs protection, such as data, devices or accounts.</li>
<li><strong>Vulnerability</strong> — a weakness that an attacker could exploit.</li>
</ul>

<h2>Summary</h2>
<p>Cyber security is a daily responsibility for every Zambian who uses a phone, a computer or mobile money. The CIA triad—confidentiality, integrity and availability—gives you a simple way to decide what to protect and why. Small changes, such as locking your phone and not storing PINs in notes, already make you a harder target.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.cisco.com/c/en/us/training-events/networking-academy.html">Cisco Networking Academy — Skills for All</a></li>
<li><a href="https://owasp.org/www-project-security-knowledge-framework/">OWASP Security Knowledge Framework</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.2 Common Threats: Malware, Ransomware and Network Attacks',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>After this lesson you will be able to recognise malware, ransomware and basic network attacks, describe how they spread in Zambia, and take the first practical steps to defend a Windows PC or Android phone.</p>

<h2>Malware: Software That Harms You</h2>
<p>Malware is short for malicious software. It includes viruses, worms, trojans, spyware and adware. Attackers trick you into installing malware through fake apps, infected USB drives or malicious links in WhatsApp messages. Once installed, malware can steal passwords, spy on messages or turn your phone into part of a botnet.</p>

<h2>Ransomware: Your Files Held Hostage</h2>
<p>Ransomware locks your files and demands payment—usually in cryptocurrency—before you can use them again. In 2017 the WannaCry attack affected organisations in many countries, including hospitals and universities. A small business in Zambia that loses its customer records, invoices and ZRA documents to ransomware may never recover them, even if it pays.</p>

<h2>Network Attacks</h2>
<p>When you connect to public Wi-Fi at a shopping mall, bus station or college, attackers on the same network can sometimes intercept your traffic. This is called a man-in-the-middle attack. They may capture passwords or redirect you to fake login pages that look like your bank or mobile money app.</p>

<h2>Worked Example: The Infected USB Drive at the Internet Café</h2>
<p>Chikondi runs a typing and printing bureau in Mongu. A customer leaves a USB drive behind and asks Chikondi to print a CV from it. Without thinking, Chikondi plugs the drive into the shop computer. The drive contains malware that spreads across the network and encrypts all customer files, including exam result templates and wedding invitations.</p>
<p>The attackers demand ZMW 4,500 in Bitcoin to unlock the files. Chikondi refuses to pay because he has a weekly backup on an external drive stored at home. He wipes the computer, reinstalls Windows, restores the backup and loses only two days of work. From then on, he scans every USB drive with Windows Security before opening files and disables autorun on all shop computers.</p>

<h2>Worked Example: The “Free Data” App</h2>
<p>John sees a WhatsApp message promising free unlimited internet. He downloads an APK file from the link and installs it. The app asks for permission to access contacts, SMS and phone calls. Within hours:</p>
<ul>
<li>his contacts receive the same message from his number,</li>
<li>his mobile money balance drops by ZMW 300, and</li>
<li>his phone battery drains because the app is running hidden tasks.</li>
</ul>
<p>The damage happened because John installed software from outside the Google Play Store and granted permissions without thinking.</p>

<h2>Practical Defences</h2>
<ul>
<li>Install apps only from the official Google Play Store or Apple App Store.</li>
<li>Keep your phone and laptop operating systems updated.</li>
<li>Do not plug unknown USB drives into your computer; scan any drive you must use.</li>
<li>Use mobile data or a trusted home Wi-Fi password instead of open public Wi-Fi for banking.</li>
<li>Back up important files to a separate drive or trusted cloud service.</li>
<li>Review app permissions regularly and remove access that is not needed.</li>
<li>Disable USB autorun on Windows computers used for business.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Open your Android phone’s Settings &gt; Apps &gt; Permissions. Review which apps can access SMS, contacts and microphone.</li>
<li>Check whether your Windows PC has Windows Security turned on and is up to date.</li>
<li>List any apps you installed through a link shared on WhatsApp or Facebook. Consider uninstalling those that are not from an official store.</li>
<li>Find the backup settings on your phone or computer and run a backup today.</li>
<li>Ask three friends whether they have ever received a “free data” or “free airtime” link.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Malware</strong> — software designed to damage or steal from a device.</li>
<li><strong>Ransomware</strong> — malware that encrypts files and demands payment.</li>
<li><strong>Phishing</strong> — fake messages that trick you into giving away information.</li>
<li><strong>Man-in-the-middle attack</strong> — intercepting communication between two parties.</li>
<li><strong>Botnet</strong> — a network of infected devices controlled by an attacker.</li>
<li><strong>Autorun</strong> — a Windows feature that automatically runs software from removable drives.</li>
<li><strong>APK</strong> — an Android application package file, often used to install apps outside official stores.</li>
</ul>

<h2>Summary</h2>
<p>Malware and ransomware are real risks for Zambian students, businesses and civil servants. Most infections begin with a downloaded file, a clicked link or an unsafe network. You can prevent the majority of attacks by using official app stores, updating devices, avoiding unknown USB drives and treating public Wi-Fi with caution.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/windows/security/">Microsoft Learn — Windows Security</a></li>
<li><a href="https://support.google.com/android/answer/6174145">Google Android Security Help</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.3 The Mind of an Attacker: Who Targets You and Why',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>After this lesson you will be able to describe the most common motives behind cyber attacks, explain why ordinary people are targeted, and spot the difference between a random scam and a focused attack aimed at you or your organisation.</p>

<h2>Why Do Attackers Attack?</h2>
<p>Most cyber crime is motivated by money. Attackers want to steal cash, sell personal information, extort victims with ransomware or take over accounts to commit fraud. Some attackers are also motivated by spying, activism or revenge. In Zambia, the attacks most people see are financial: fake mobile-money messages, romance scams and business-email fraud.</p>

<h2>Who Is a Target?</h2>
<p>A common myth is that only rich or famous people get attacked. In reality, attackers send millions of messages and only need a few victims to make a profit. A market seller, a college student and a government office worker are all valuable because they have:</p>
<ul>
<li>money in mobile wallets or bank accounts,</li>
<li>contacts that can be used to spread scams, and</li>
<li>information such as NRC numbers, TPINs or passwords that unlock other accounts.</li>
</ul>

<h2>Opportunistic vs Targeted Attacks</h2>
<p><strong>Opportunistic attacks</strong> are sent to many people at once. A WhatsApp message saying “You have won ZMW 5,000 from Zambezi FM” is opportunistic. The attacker does not know you personally; they are hoping someone will believe it.</p>
<p><strong>Targeted attacks</strong> focus on one person or organisation. An attacker may research a business owner on Facebook, learn the names of suppliers, then send a fake invoice that looks real. These attacks take more effort but often succeed because they feel personal.</p>

<h2>Worked Example: The Fake School Fees Message</h2>
<p>A parent in Ndola receives a WhatsApp message that appears to come from her child’s secondary school. The message says fees must be paid into a new Ecobank account by midday because the school’s usual account is “under audit.” The sender uses the head teacher’s name and a similar profile picture.</p>
<p>The parent becomes suspicious because the school always sends printed notices and never requests fee changes by WhatsApp. She calls the school office on the number printed on her child’s report card. The school confirms the message is fake. The attacker had copied photos and names from the school’s public Facebook page. The defence is to verify unusual payment requests through a trusted, separate channel.</p>

<h2>Worked Example: The Supplier Invoice Trick</h2>
<p>Mr Tembo runs a building-supplies shop in Lusaka. He receives an email that appears to come from his cement supplier. The email uses the supplier’s real logo and asks him to pay an invoice of ZMW 18,500 into a new account because of a “bank system upgrade.” The email address is one letter different from the real supplier’s address. Mr Tembo nearly pays, but he calls the supplier first and discovers the email is fake.</p>
<p>This is a targeted business-email compromise attack. The defence is simple: verify payment changes by phone using a number you already have, not one in the suspicious email.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Search your email or SMS inbox for messages that ask urgently for money, a password or an OTP.</li>
<li>For each message, ask: did the sender use my name? Does the tone create panic? Can I verify it another way?</li>
<li>Write down one habit you will start, such as always calling back a known number before sending money.</li>
<li>Look at your social media profiles and remove any information an attacker could use to pretend they know you.</li>
<li>Discuss one example of a targeted or opportunistic scam with a family member this week.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Opportunistic attack</strong> — an attack sent to many potential victims at random.</li>
<li><strong>Targeted attack</strong> — an attack directed at a specific person or organisation.</li>
<li><strong>Business-email compromise</strong> — tricking a business into sending money or data through fake emails.</li>
<li><strong>Social engineering</strong> — manipulating people into breaking security rules.</li>
<li><strong>OTP</strong> — one-time password, usually a short code sent by SMS or app.</li>
<li><strong>Impersonation</strong> — pretending to be someone else to gain trust or access.</li>
<li><strong>Verification</strong> — checking a request through a trusted, separate channel.</li>
</ul>

<h2>Summary</h2>
<p>Attackers target ordinary Zambians because small sums from many victims add up quickly. Understanding whether an attack is opportunistic or targeted helps you choose the right defence. The most important habit is to slow down and verify any unexpected request for money or information using a trusted channel.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.cisco.com/c/en/us/training-events/networking-academy.html">Cisco Networking Academy — Skills for All</a></li>
<li><a href="https://owasp.org/www-project-security-knowledge-framework/">OWASP Security Knowledge Framework</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => 'Module 1 Quiz: Understanding Cyber Threats',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Complete the quiz to show your understanding of cyber threats, the CIA triad and attacker motives.</p>',
            ],
        ];
    }

    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: Understanding Cyber Threats',
            'description' => 'Test your understanding of cyber security basics, the CIA triad, common threats and attacker motives.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In the CIA triad, what does the letter C stand for?',
                    'explanation' => 'Confidentiality means only authorised people can access information, such as your NRC number or mobile-money PIN.',
                    'options' => [
                        ['text' => 'Confidentiality', 'is_correct' => true],
                        ['text' => 'Computing', 'is_correct' => false],
                        ['text' => 'Compliance', 'is_correct' => false],
                        ['text' => 'Cryptography', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which situation best describes the availability principle?',
                    'explanation' => 'Availability means systems and data are accessible when you need them, such as buying ZESCO tokens during load-shedding.',
                    'options' => [
                        ['text' => 'Only the shop owner can see the sales records', 'is_correct' => false],
                        ['text' => 'A student can still buy ZESCO tokens online during load-shedding', 'is_correct' => true],
                        ['text' => 'An invoice total cannot be changed after payment', 'is_correct' => false],
                        ['text' => 'A phone PIN is stored in a secure note', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is malware?',
                    'explanation' => 'Malware is malicious software designed to damage devices, steal data or perform other harmful actions.',
                    'options' => [
                        ['text' => 'A type of antivirus program', 'is_correct' => false],
                        ['text' => 'Software that improves internet speed', 'is_correct' => false],
                        ['text' => 'Software designed to harm or steal from a device', 'is_correct' => true],
                        ['text' => 'A secure messaging application', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'How does ransomware usually demand payment?',
                    'explanation' => 'Ransomware encrypts the victim’s files and refuses to unlock them until a ransom is paid.',
                    'options' => [
                        ['text' => 'By sending a polite letter', 'is_correct' => false],
                        ['text' => 'By locking files and demanding money to unlock them', 'is_correct' => true],
                        ['text' => 'By asking the user to complete a survey', 'is_correct' => false],
                        ['text' => 'By offering a discount on antivirus software', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'You receive an email from a supplier asking you to pay an invoice into a new bank account. What should you do first?',
                    'explanation' => 'Verify unexpected payment changes by calling the supplier on a known phone number, not one from the suspicious email.',
                    'options' => [
                        ['text' => 'Pay immediately to keep the business relationship', 'is_correct' => false],
                        ['text' => 'Forward the email to your accountant and do nothing else', 'is_correct' => false],
                        ['text' => 'Call the supplier using a phone number you already trust', 'is_correct' => true],
                        ['text' => 'Reply to the email to confirm the new details', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which is the safest place to install an Android app from?',
                    'explanation' => 'Official app stores such as Google Play Store review apps for malware more carefully than random download links.',
                    'options' => [
                        ['text' => 'A link shared on WhatsApp', 'is_correct' => false],
                        ['text' => 'Google Play Store', 'is_correct' => true],
                        ['text' => 'A file sent by Bluetooth', 'is_correct' => false],
                        ['text' => 'Any website that promises free features', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Public Wi-Fi is always safe for mobile banking as long as it requires a password.',
                    'explanation' => 'A shared Wi-Fi password does not protect your data from other people on the same network who may intercept traffic.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Cyber attackers only target large companies and wealthy individuals.',
                    'explanation' => 'Attackers often send scams to thousands of ordinary people because even small amounts from a few victims add up.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What one word describes keeping information accurate and unaltered?',
                    'explanation' => 'Integrity means information is not changed secretly or by unauthorised people.',
                    'correct_answer' => 'Integrity',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What type of attack intercepts communication between you and a website when you are on the same network?',
                    'explanation' => 'A man-in-the-middle attack intercepts data between two parties, often on public Wi-Fi.',
                    'correct_answer' => 'Man-in-the-middle',
                ],
            ],
        ];
    }

    private function module2Lessons(): array
    {
        return [
            [
                'title' => '2.1 Building Strong Passwords and Passphrases',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create strong, memorable passwords, recognise weak passwords that attackers can guess quickly, and explain why reusing the same password across many accounts is dangerous.</p>

<h2>Why Passwords Matter</h2>
<p>A password is often the only thing standing between your accounts and an attacker. If someone guesses your email password, they may be able to reset your mobile money, Facebook, ZRA e-filing and online banking passwords. If someone guesses your phone lock PIN, they can access your photos, messages and payment apps.</p>

<h2>What Makes a Password Weak?</h2>
<p>Attackers use automated tools that can try millions of common passwords in minutes. A weak password usually has one or more of these problems:</p>
<ul>
<li>It is short—eight characters or fewer.</li>
<li>It is a common word such as “password”, “12345678” or “qwerty”.</li>
<li>It uses personal information such as your name, child’s name, phone number or birth year.</li>
<li>It is reused on many accounts.</li>
</ul>

<h2>Worked Example: The Market Trader’s Mobile Money PIN</h2>
<p>Amai runs a vegetable stall at Soweto Market in Lusaka. She uses MTN Mobile Money to receive payments from suppliers and to send money to her sister in Kitwe. Her PIN is 1990, the year she was born. An attacker who steals her phone guesses the PIN in three tries because the birth year is visible on her Facebook profile.</p>
<p>Amai now uses a six-digit PIN that is not related to her birthday, phone number or NRC. She also turns on app lock so the mobile money app requires a fingerprint. She saves her mobile money and supplier passwords in a password manager so she does not have to reuse simple ones. Her new habits protect the money she needs to restock her stall every morning.</p>

<h2>Worked Example: From Weak to Strong</h2>
<p>Consider these passwords for a fictional student named Grace, born in 2001:</p>
<table>
<thead>
<tr><th>Password</th><th>Why It Is Weak or Strong</th></tr>
</thead>
<tbody>
<tr><td>grace2001</td><td>Uses her name and birth year—easy to guess from Facebook.</td></tr>
<tr><td>Password123</td><td>One of the most common passwords in the world.</td></tr>
<tr><td>7#kL9!mP$2qR</td><td>Strong but hard to remember without a password manager.</td></tr>
<tr><td>Blue-Chicken-Kalomo-2024!</td><td>Long, unique and easier to remember; a passphrase.</td></tr>
</tbody>
</table>

<h2>Passphrases: Long and Memorable</h2>
<p>A passphrase is a sentence or a chain of random words. It is easier to remember than a random mix of letters and numbers, but still hard to crack because of its length. A good passphrase:</p>
<ul>
<li>has at least 16 characters,</li>
<li>uses four or more unrelated words or a full sentence,</li>
<li>includes at least one number or symbol, and</li>
<li>is not a famous quote or song lyric.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Write down three passwords you currently use.</li>
<li>Check each one against the weaknesses listed above.</li>
<li>Create one new passphrase of at least 16 characters that you can remember.</li>
<li>Change the password on one important account today, such as your email or mobile money app.</li>
<li>Ask a friend what their mobile money PIN is based on and encourage them to change it if it uses a birth year or phone number.</li>
<li>Make a list of your five most important accounts and plan to give each a unique passphrase.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Password</strong> — a secret string used to prove who you are.</li>
<li><strong>Passphrase</strong> — a longer password made of words or a sentence.</li>
<li><strong>Brute-force attack</strong> — trying many passwords quickly until one works.</li>
<li><strong>Credential stuffing</strong> — using leaked username and password pairs on other websites.</li>
<li><strong>Reuse</strong> — using the same password on more than one account.</li>
<li><strong>PIN</strong> — a short numeric code, often used for phones and mobile money.</li>
<li><strong>App lock</strong> — a feature that protects individual apps with a PIN, pattern or fingerprint.</li>
</ul>

<h2>Summary</h2>
<p>Strong passwords are long, unique and not based on personal information. Passphrases help you remember long passwords. Reusing passwords is one of the biggest risks because one leaked password can unlock many accounts. Start by securing your most important accounts—email, mobile money and banking—with strong, unique passphrases.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/accounts/answer/32040">Google — Create a Strong Password</a></li>
<li><a href="https://www.ncsc.gov.uk/collection/top-tips-for-staying-secure-online/use-a-strong-and-separate-password-for-your-email">UK NCSC — Use a Strong and Separate Password</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.2 Password Managers and Why They Help',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what a password manager does, compare the risks of writing passwords down with the risks of reusing them, and set up a simple password manager on your phone or computer.</p>

<h2>The Password Problem</h2>
<p>Most people have dozens of accounts: email, social media, mobile money, online shopping, ZRA e-filing, work portals and more. Remembering a unique strong password for every account is nearly impossible. Many people solve this by reusing passwords or writing them on paper. Both have risks.</p>

<h2>What Is a Password Manager?</h2>
<p>A password manager is a secure digital vault that stores your passwords. You only need to remember one strong master password or use your fingerprint to unlock the manager. It can also create strong random passwords for you and fill them into websites and apps automatically.</p>

<h2>Why Use a Password Manager?</h2>
<ul>
<li>It remembers unique passwords for every account.</li>
<li>It creates strong passwords you do not have to memorise.</li>
<li>It protects passwords with encryption, which is much safer than a note in your phone.</li>
<li>It warns you if a password has been found in a data breach.</li>
</ul>

<h2>Free and Trusted Password Managers</h2>
<p>For personal use, Bitwarden and KeePassXC are well-known free options. Bitwarden stores passwords in the cloud so they sync between your phone and laptop. KeePassXC stores them locally in a single file. Both are open source and have been reviewed by security experts. Choose the one that suits your needs and learn how to back up your vault.</p>

<h2>Worked Example: A Poultry Farmer’s Many Accounts</h2>
<p>Mr Ngoma keeps layers on a small farm outside Kalomo. He sells eggs to shops in town and uses Facebook, WhatsApp Business, Airtel Money, a bank app and a ZRA TPIN portal. He used the same password, “Kalomo2022”, for every account because it was easy to remember.</p>
<p>When an online shop he once bought feed from is breached, his password is leaked. Attackers try “Kalomo2022” on his email, mobile money and Facebook. They take over his Facebook page and post fake egg orders. After recovering the page, Mr Ngoma installs Bitwarden, creates a strong master passphrase, and gives every account a unique password. He also turns on 2FA wherever it is supported. Six months later he receives another breach alert, but this time only one unused account is affected.</p>

<h2>Worked Example: Setting Up Bitwarden</h2>
<p>Here is a safe way to start with Bitwarden:</p>
<ol>
<li>Download Bitwarden from the official website or app store only.</li>
<li>Create one very strong master passphrase—this is the only password you must remember.</li>
<li>Store the master passphrase somewhere safe, such as a locked drawer, in case you forget it.</li>
<li>Save your most important accounts first: email, mobile money and banking.</li>
<li>Turn on two-factor authentication for Bitwarden itself.</li>
<li>Export an encrypted backup of your vault once a month and store it somewhere safe.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Choose one free password manager and install it on your main device.</li>
<li>Create a master passphrase of at least 16 characters.</li>
<li>Save three of your most important accounts into the password manager.</li>
<li>Use the password generator to create a new strong password for one of those accounts and update the account.</li>
<li>Check whether any of your saved passwords have appeared in known data breaches.</li>
<li>Write down the master passphrase backup location and tell one trusted person where it is kept.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Password manager</strong> — an encrypted app that stores and generates passwords.</li>
<li><strong>Master password</strong> — the single strong password that unlocks your password manager.</li>
<li><strong>Encryption</strong> — scrambling data so it can only be read with the right key.</li>
<li><strong>Data breach</strong> — an incident where private information is stolen from an organisation.</li>
<li><strong>Vault</strong> — the encrypted storage area inside a password manager.</li>
<li><strong>Sync</strong> — keeping the same data up to date across multiple devices.</li>
<li><strong>Backup code</strong> — a code used to recover access if you lose your master password or device.</li>
</ul>

<h2>Summary</h2>
<p>Password managers solve the problem of remembering many strong passwords. They let you use unique, random passwords for every account while only memorising one master passphrase. Storing passwords in an encrypted manager is much safer than reusing passwords or keeping them in unencrypted notes.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://bitwarden.com/help/">Bitwarden Help Centre</a></li>
<li><a href="https://keepassxc.org/docs/">KeePassXC Documentation</a></li>
<li><a href="https://support.google.com/accounts/answer/3167300">Google — Password Manager</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.3 Two-Factor Authentication (2FA)',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what two-factor authentication is, compare SMS codes with authenticator apps, and enable 2FA on your most important accounts such as email and mobile money.</p>

<h2>What Is Two-Factor Authentication?</h2>
<p>Two-factor authentication, or 2FA, means proving your identity in two different ways. The first factor is something you know, usually your password. The second factor is something you have, such as your phone. Even if an attacker steals your password, they still need your phone to get in. This extra step is especially important for Zambians who rely on mobile money and email to manage daily life.</p>

<h2>Common Second Factors</h2>
<ul>
<li><strong>SMS code</strong> — a one-time code sent by text message. Better than nothing, but attackers can intercept SMS or swap your SIM card.</li>
<li><strong>Authenticator app</strong> — an app such as Google Authenticator or Authy that generates a six-digit code on your phone. It does not need mobile network signal.</li>
<li><strong>Hardware key</strong> — a small USB device such as YubiKey. Very secure but less common in Zambia.</li>
<li><strong>Biometric</strong> — your fingerprint or face scan. Convenient, but best used together with another factor.</li>
</ul>

<h2>Worked Example: A Student’s Stolen Password</h2>
<p>Naomi is a first-year student at a college in Livingstone. She uses the same password for her college email and a gaming forum. When the forum is hacked, her password is published online. An attacker tries it on her Gmail account and gets the password right.</p>
<p>Fortunately, Naomi turned on 2FA using Google Authenticator six months earlier. The attacker sees a prompt asking for a six-digit code. Because Naomi’s phone is in her bag, the attacker cannot continue. She later receives a security alert, changes her password, and updates every other account that reused the old one. The 2FA layer turned a likely disaster into a close call.</p>

<h2>Worked Example: Protecting an Email Account</h2>
<p>Imagine Grace’s email password is leaked in a data breach. Without 2FA, an attacker logs in immediately and resets her Facebook and mobile money passwords. With 2FA turned on, the attacker sees a prompt asking for a six-digit code from her authenticator app. Because the attacker does not have her phone, the account stays safe.</p>

<h2>Which Accounts Need 2FA First?</h2>
<p>You do not need to enable 2FA everywhere on day one. Start with the accounts that protect everything else:</p>
<ol>
<li>Email account — because attackers use email to reset other passwords.</li>
<li>Mobile money and banking apps — because they control your money.</li>
<li>Social media accounts — because they can be used to scam your friends.</li>
<li>Work or school accounts — because they may hold sensitive data.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Open your email account settings and search for “two-step verification” or “2FA”.</li>
<li>Choose an authenticator app method if it is available.</li>
<li>Scan the QR code with Google Authenticator, Authy or a similar app.</li>
<li>Save the backup codes somewhere safe, not on the same phone.</li>
<li>Repeat the process for your mobile money or banking app if it supports 2FA.</li>
<li>Review your email account’s recent activity and sign out any devices you do not recognise.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Two-factor authentication (2FA)</strong> — using two different proofs to log in.</li>
<li><strong>Factor</strong> — something used to prove identity, such as a password or phone.</li>
<li><strong>Authenticator app</strong> — an app that generates time-based login codes.</li>
<li><strong>SIM swap</strong> — an attack where someone takes over your mobile number.</li>
<li><strong>Backup code</strong> — a one-time code used if you lose your second factor.</li>
<li><strong>Time-based code</strong> — a login code that changes every 30 seconds in an authenticator app.</li>
<li><strong>Security alert</strong> — a notification that someone may have tried to access your account.</li>
</ul>

<h2>Summary</h2>
<p>Two-factor authentication adds a strong extra layer of defence. If your password is stolen, 2FA usually stops the attacker. Authenticator apps are safer than SMS codes because they do not depend on your mobile network. Enable 2FA on your email, money and social accounts first, and keep backup codes in a safe place.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.authenticatorapp.com/">Authenticator App Guides</a></li>
<li><a href="https://support.google.com/accounts/answer/185839">Google — Turn on 2-Step Verification</a></li>
<li><a href="https://2fa.directory/">2FA Directory — List of Sites That Support 2FA</a></li>
</ul>
HTML,
            ],
            [
                'title' => 'Module 2 Quiz: Passwords and Two-Factor Authentication',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Answer questions about strong passwords, password managers and two-factor authentication.</p>',
            ],
        ];
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Passwords and Two-Factor Authentication',
            'description' => 'Check your understanding of strong passwords, password managers and two-factor authentication.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the safest way to manage many strong, unique passwords?',
                    'explanation' => 'A password manager stores each password securely and generates strong ones for you.',
                    'options' => [
                        ['text' => 'Write them all in one notebook', 'is_correct' => false],
                        ['text' => 'Use the same password everywhere', 'is_correct' => false],
                        ['text' => 'Use a password manager', 'is_correct' => true],
                        ['text' => 'Email them to yourself', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is the strongest password?',
                    'explanation' => 'A long passphrase with unrelated words and symbols is strong and memorable.',
                    'options' => [
                        ['text' => 'grace2001', 'is_correct' => false],
                        ['text' => 'Password123', 'is_correct' => false],
                        ['text' => 'Blue-Chicken-Kalomo-2024!', 'is_correct' => true],
                        ['text' => 'qwerty', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is credential stuffing?',
                    'explanation' => 'Credential stuffing uses username and password pairs leaked from one site to try logging in elsewhere.',
                    'options' => [
                        ['text' => 'Filling in a login form with fake details', 'is_correct' => false],
                        ['text' => 'Using leaked passwords on other websites', 'is_correct' => true],
                        ['text' => 'Creating many fake accounts', 'is_correct' => false],
                        ['text' => 'Sending phishing emails to many people', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main risk of reusing the same password on many accounts?',
                    'explanation' => 'If one account is breached, attackers can try the same password on your other accounts.',
                    'options' => [
                        ['text' => 'You will forget the password', 'is_correct' => false],
                        ['text' => 'The password becomes shorter', 'is_correct' => false],
                        ['text' => 'One data breach can unlock many accounts', 'is_correct' => true],
                        ['text' => 'It slows down your internet', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which two-factor authentication method is generally safer than SMS codes?',
                    'explanation' => 'Authenticator apps do not rely on your mobile network and cannot be intercepted by SIM swaps.',
                    'options' => [
                        ['text' => 'Email confirmation', 'is_correct' => false],
                        ['text' => 'Authenticator app', 'is_correct' => true],
                        ['text' => 'Security question', 'is_correct' => false],
                        ['text' => 'Writing the code on paper', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should you do with 2FA backup codes?',
                    'explanation' => 'Backup codes let you recover your account if you lose your phone, so store them somewhere safe and separate.',
                    'options' => [
                        ['text' => 'Share them with a friend', 'is_correct' => false],
                        ['text' => 'Store them on the same phone as the authenticator app', 'is_correct' => false],
                        ['text' => 'Keep them in a safe place separate from your phone', 'is_correct' => true],
                        ['text' => 'Post them in a secure note online', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A password manager stores all your passwords in an encrypted vault.',
                    'explanation' => 'Password managers encrypt your passwords so they cannot be read without the master password.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A passphrase should be a famous quote so it is easy to remember.',
                    'explanation' => 'Famous quotes are in attackers’ password lists, so they make weak passphrases.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What does the acronym 2FA stand for?',
                    'explanation' => '2FA means two-factor authentication, requiring two different proofs of identity.',
                    'correct_answer' => 'Two-factor authentication',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What type of attack takes control of your mobile phone number?',
                    'explanation' => 'A SIM swap attack moves your phone number to a device controlled by the attacker.',
                    'correct_answer' => 'SIM swap',
                ],
            ],
        ];
    }

    private function module3Lessons(): array
    {
        return [
            [
                'title' => '3.1 Recognising Phishing Emails and Messages',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to identify phishing emails, SMS and WhatsApp messages, explain the tricks attackers use to create urgency, and describe the steps to take before clicking any link or attachment.</p>

<h2>What Is Phishing?</h2>
<p>Phishing is a scam where an attacker pretends to be a trusted person or organisation to steal your passwords, money or personal information. The message may look like it comes from your bank, mobile money provider, ZRA, Facebook or a delivery company. Phishing happens through email, SMS, WhatsApp, Facebook Messenger and even phone calls.</p>

<h2>Common Signs of Phishing</h2>
<ul>
<li><strong>Urgency or fear</strong> — “Your account will be closed in 24 hours” or “You have won ZMW 10,000.”</li>
<li><strong>Unexpected attachments</strong> — invoices, payment receipts or documents you did not ask for.</li>
<li><strong>Suspicious links</strong> — the visible text says one thing, but the actual web address is different.</li>
<li><strong>Spelling mistakes</strong> — real companies usually proofread messages carefully.</li>
<li><strong>Requests for passwords or OTPs</strong> — no legitimate company will ask for these.</li>
</ul>

<h2>Worked Example: The Fake ZRA Email</h2>
<p>Mrs Mwanza receives an email that appears to be from ZRA. It says her TPIN account has suspicious activity and she must “verify” her login details within 24 hours. The email contains a button labelled “Verify TPIN.” When she hovers over the button, the link shows a strange address such as <code>zra-verification-login.xyz</code>. She does not click. Instead, she opens her browser, types <code>www.zra.org.zm</code> directly and logs in there. Her account is fine; the email was phishing.</p>

<h2>How to Check a Link Safely</h2>
<ol>
<li>On a computer, hover your mouse over the link without clicking. The real address appears at the bottom of the browser.</li>
<li>On a phone, press and hold the link to see a preview of the address.</li>
<li>Look for misspellings, extra words or strange domain endings such as <code>.xyz</code>, <code>.tk</code> or <code>.top</code>.</li>
<li>Compare the link with the official domain you know, such as <code>zra.org.zm</code> or <code>mtn.zm</code>.</li>
<li>When in doubt, open the website by typing the official address manually.</li>
<li>Report suspicious messages to your provider or forward them to a known fraud-reporting address.</li>
</ol>

<h2>Worked Example: The Compromised Friend’s WhatsApp</h2>
<p>James receives a WhatsApp message from his cousin: “Bro, please help me buy these ZESCO tokens quickly, my power is about to go off. Send ZMW 200 to this MoMo number and I will pay you back tomorrow.” The message feels urgent and the number is new.</p>
<p>James calls his cousin on the old family number. The cousin says his WhatsApp was hacked an hour ago and several relatives received the same message. James reports the number to MTN and warns other family members. The key defence was verifying through a different channel instead of replying to the compromised account.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open your email or SMS inbox and find one message that asks you to click a link or open an attachment.</li>
<li>Check the sender’s address carefully. Is it exactly the company’s real domain?</li>
<li>Hover over or long-press any link to see the real destination.</li>
<li>Delete any message that looks suspicious. Do not reply.</li>
<li>Forward one phishing example to a friend and explain why it is suspicious.</li>
<li>Save your bank and mobile money provider’s official customer-care numbers in your phone.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Phishing</strong> — fake messages that trick you into giving away information or money.</li>
<li><strong>Link spoofing</strong> — making a link look like it goes to a trusted site when it does not.</li>
<li><strong>Attachment</strong> — a file sent with a message, such as a PDF or invoice.</li>
<li><strong>Domain</strong> — the address of a website, such as zra.org.zm.</li>
<li><strong>OTP</strong> — one-time password, a short code used for login or payment confirmation.</li>
<li><strong>Compromised account</strong> — an account that an attacker has taken control of.</li>
<li><strong>Verification channel</strong> — a separate, trusted way to confirm a request.</li>
</ul>

<h2>Summary</h2>
<p>Phishing messages use urgency, fear and fake branding to make you act quickly. Slow down, check the sender and the real link destination, and never share passwords or OTPs. When in doubt, contact the company using a known phone number or official website.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/mail/answer/8253">Google — Avoid and Report Phishing</a></li>
<li><a href="https://www.ncsc.gov.uk/collection/phishing">UK NCSC — Phishing Advice</a></li>
<li><a href="https://www.consumer.ftc.gov/articles/how-recognize-and-avoid-phishing-scams">FTC — How to Recognize Phishing</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.2 Mobile Money Fraud: MoMo and Airtel Money Scams',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to recognise common MTN Mobile Money and Airtel Money scams, explain why you should never share an OTP or PIN, and describe the correct way to reverse a mistaken transaction.</p>

<h2>Why Mobile Money Is a Target</h2>
<p>Mobile money is fast, convenient and widely used in Zambia. Unfortunately, that also makes it attractive to criminals. A scammer only needs your PIN and a one-time password to empty your wallet. Because transactions are quick, victims often realise the fraud too late.</p>

<h2>Common Mobile Money Scams</h2>
<ul>
<li><strong>Fake merchant payment</strong> — you are asked to “pay” for goods that do not exist, often through social media.</li>
<li><strong>Reverse transaction scam</strong> — a stranger claims they sent money to your account by mistake and asks you to send it back. They may send a fake SMS as proof.</li>
<li><strong>PIN or OTP request</strong> — someone pretending to be customer service asks for your PIN or OTP to “fix” your account.</li>
<li><strong>Fake promotions</strong> — messages saying you have won money or airtime and must pay a small fee to claim it.</li>
<li><strong>Phishing via SMS</strong> — links that look like mobile money login pages steal your details.</li>
</ul>

<h2>Worked Example: The “Wrong Send” Trick</h2>
<p>Mr Zulu receives an SMS that appears to show a ZMW 500 deposit into his MTN MoMo wallet. A minute later a caller says, “I sent money to the wrong number, please send it back.” Mr Zulu checks his actual MoMo balance and sees no deposit. He tells the caller to contact MTN directly and blocks the number. The SMS was fake; the caller hoped Mr Zulu would send real money before checking.</p>

<h2>Worked Example: The Fake Airtel Money Promotion</h2>
<p>Mercy sees a Facebook post claiming Airtel Money is giving ZMW 1,000 to “the first 50 customers who register today.” The post includes a link to a website that looks like Airtel Zambia’s official page. It asks for her phone number, PIN and OTP to “activate the reward.”</p>
<p>Mercy stops. She knows Airtel never asks for a PIN or OTP over the internet. She opens the My Airtel app from her home screen instead and sees no promotion. She reports the fake page to Airtel’s social media team and warns her church WhatsApp group. The scammer’s website is taken down a few days later. The defence is simple: never enter your PIN or OTP on a website or form that arrives through a link.</p>

<h2>Golden Rules for Mobile Money Safety</h2>
<ol>
<li>Never share your PIN or OTP with anyone, including people who say they work for MTN or Airtel.</li>
<li>Always confirm money has actually arrived by checking your balance in the official app or dialling the official USSD code.</li>
<li>If someone sends money by mistake, tell them to contact their mobile money provider, not you.</li>
<li>Do not pay for goods advertised on social media until you verify the seller is real.</li>
<li>Use a strong PIN that is not your birth year or phone number.</li>
<li>Enable app lock or biometric protection on your mobile money app if it is available.</li>
<li>Keep the official customer-care numbers saved in your phone for quick reporting.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Open your MTN or Airtel Money app and locate the customer service number.</li>
<li>Check your transaction history for any payment you do not recognise.</li>
<li>Change your mobile money PIN to a number that is not related to your birth date or phone number.</li>
<li>Delete any SMS or WhatsApp message asking for your PIN or OTP.</li>
<li>Review your app permissions and remove access to SMS from apps that do not need it.</li>
<li>Ask a family member to recite the three most important mobile money safety rules.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Mobile money</strong> — electronic money stored on a mobile phone and used for payments and transfers.</li>
<li><strong>OTP</strong> — one-time password sent by SMS or app to confirm a transaction.</li>
<li><strong>PIN</strong> — personal identification number used to access your mobile wallet.</li>
<li><strong>USSD</strong> — a code such as *303# that runs services on any phone.</li>
<li><strong>Reverse transaction</strong> — sending money back after a mistaken or fraudulent transfer.</li>
<li><strong>Customer care</strong> — the official support channel of a mobile money or telecom provider.</li>
<li><strong>Biometric lock</strong> — using a fingerprint or face scan to open an app.</li>
</ul>

<h2>Summary</h2>
<p>Mobile money scams prey on trust and urgency. Your PIN and OTP are the keys to your wallet; never give them to anyone. Always verify deposits in your official app or via official USSD codes, and report suspicious messages to your mobile money provider.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.mtn.zm/momo/">MTN Zambia — MoMo Information</a></li>
<li><a href="https://www.airtel.co.zm/">Airtel Zambia</a></li>
<li><a href="https://www.bankofzambia.co.zm/">Bank of Zambia — Consumer Protection</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.3 Social Engineering Tactics and How to Resist',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe common social-engineering tactics, recognise manipulation techniques such as authority, urgency and familiarity, and apply a simple verification habit to resist pressure.</p>

<h2>What Is Social Engineering?</h2>
<p>Social engineering is the art of manipulating people into breaking security rules. Instead of hacking computers, attackers hack human trust. They may pretend to be your boss, a friend, a bank official or a technician. Their goal is to make you reveal information, send money or install harmful software. These attacks are common in Zambia because mobile money and WhatsApp make it easy for criminals to reach many people quickly.</p>

<h2>Six Common Manipulation Tricks</h2>
<ul>
<li><strong>Authority</strong> — “I am calling from the bank / ZRA / your office. Do this now.”</li>
<li><strong>Urgency</strong> — “Your account will be blocked in one hour.”</li>
<li><strong>Fear</strong> — “You are under investigation. Pay a fine immediately.”</li>
<li><strong>Familiarity</strong> — using your name, your friend’s name or details from social media.</li>
<li><strong>Reciprocity</strong> — “I helped you before, now you can help me quickly.”</li>
<li><strong>Curiosity</strong> — “You have a parcel / a payment / a video of yourself.”</li>
</ul>

<h2>Worked Example: The Fake Technician</h2>
<p>A small college in Livingstone receives a call from a person claiming to be from the internet provider. The caller says the college router has a problem and asks the receptionist to install “remote support software.” The receptionist becomes suspicious and says, “I will call our usual technician first.” The caller becomes angry and insists. The receptionist hangs up and reports the call. It was a social-engineering attempt to gain remote access to the college network.</p>

<h2>Worked Example: The PTA WhatsApp Group Urgency</h2>
<p>Mrs Chileshe is the treasurer of a PTA WhatsApp group for a school in Kitwe. Late one evening she receives a message from the “chairperson” asking her to send ZMW 3,000 to a new MoMo number urgently so the school can pay a transport deposit for a trip the next day. The profile picture matches the chairperson and the tone sounds exactly like her.</p>
<p>Mrs Chileshe remembers the STOP method. She slows down, thinks that the chairperson would never ask for money at night, and calls the chairperson on the number saved in her phone. The chairperson says her WhatsApp was cloned. Mrs Chileshe warns the group, deletes the message and reports the number. The manipulation tricks were authority, urgency and familiarity.</p>

<h2>The STOP Method</h2>
<p>Use this four-step pause when someone asks for sensitive information or urgent action:</p>
<ol>
<li><strong>Slow down</strong> — do not act while under pressure.</li>
<li><strong>Think</strong> — does this request make sense?</li>
<li><strong>Verify</strong> — contact the person or organisation through a known channel.</li>
<li><strong>Protect</strong> — refuse, report and warn others if it is a scam.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Think of a recent phone call, message or email that created pressure.</li>
<li>Which manipulation trick did it use: authority, urgency, fear, familiarity, reciprocity or curiosity?</li>
<li>Write down how you could verify the request using a known phone number or in-person contact.</li>
<li>Discuss one example with a friend or family member to help them recognise the tactic.</li>
<li>Practise saying, “I will call you back on the number I have,” the next time someone asks for money or data.</li>
<li>Create a short note in your phone with the STOP steps for quick reference.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Social engineering</strong> — manipulating people to break security rules.</li>
<li><strong>Pretexting</strong> — creating a fake story to gain trust.</li>
<li><strong>Baiting</strong> — offering something attractive to make you click or download.</li>
<li><strong>Tailgating</strong> — following someone into a secure area without permission.</li>
<li><strong>Verification</strong> — checking a request through a trusted, separate channel.</li>
<li><strong>Cloned account</strong> — a fake profile that copies a real person’s identity.</li>
<li><strong>STOP method</strong> — a four-step pause: Slow down, Think, Verify, Protect.</li>
</ul>

<h2>Summary</h2>
<p>Social engineers exploit trust and emotion, not technical skill. Authority, urgency and fear are their favourite tools. The best defence is to slow down, think critically and verify any unusual request through a known channel. Teaching friends and family to do the same makes your whole community safer.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.cisa.gov/secure-our-world/recognize-and-report-phishing">CISA — Recognize and Report Phishing</a></li>
<li><a href="https://www.ncsc.gov.uk/collection/social-engineering">UK NCSC — Social Engineering Guidance</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => 'Module 3 Quiz: Phishing, Mobile Money Fraud and Social Engineering',
                'duration_minutes' => 25,
                'type' => 'Quiz',
                'content' => '<p>Show what you know about phishing, mobile money scams and social-engineering tactics.</p>',
            ],
        ];
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Phishing, Mobile Money Fraud and Social Engineering',
            'description' => 'Test your ability to recognise phishing, mobile money scams and social-engineering pressure.',
            'time_limit_minutes' => 25,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'You receive an email that appears to be from ZRA asking you to verify your TPIN. What is the safest action?',
                    'explanation' => 'Opening the official website by typing the address manually avoids fake links in emails.',
                    'options' => [
                        ['text' => 'Click the link and enter your details', 'is_correct' => false],
                        ['text' => 'Type the official ZRA website address into your browser', 'is_correct' => true],
                        ['text' => 'Reply to the email with your TPIN', 'is_correct' => false],
                        ['text' => 'Forward the email to your contacts', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Someone calls claiming to be from MTN MoMo customer service and asks for your OTP. What should you do?',
                    'explanation' => 'Legitimate mobile money staff will never ask for your OTP or PIN.',
                    'options' => [
                        ['text' => 'Give them the OTP to fix the problem', 'is_correct' => false],
                        ['text' => 'Refuse and end the call', 'is_correct' => true],
                        ['text' => 'Ask them to call back later', 'is_correct' => false],
                        ['text' => 'Share only the PIN, not the OTP', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'A stranger says they sent money to your mobile wallet by mistake. What should you do first?',
                    'explanation' => 'Always verify in the official app or via official USSD before sending money back.',
                    'options' => [
                        ['text' => 'Send the money back immediately', 'is_correct' => false],
                        ['text' => 'Check your actual balance in the official mobile money app', 'is_correct' => true],
                        ['text' => 'Share your PIN so they can reverse it themselves', 'is_correct' => false],
                        ['text' => 'Ignore the message and delete it without checking', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of these is a strong sign that a message is phishing?',
                    'explanation' => 'Phishing often creates urgency and uses links that do not match the real company domain.',
                    'options' => [
                        ['text' => 'It comes from a known phone number', 'is_correct' => false],
                        ['text' => 'It asks you to update your password by clicking a suspicious link', 'is_correct' => true],
                        ['text' => 'It has perfect spelling', 'is_correct' => false],
                        ['text' => 'It does not ask for money', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does social engineering primarily attack?',
                    'explanation' => 'Social engineering manipulates people and their trust rather than technical systems.',
                    'options' => [
                        ['text' => 'Computer hardware', 'is_correct' => false],
                        ['text' => 'People and their trust', 'is_correct' => true],
                        ['text' => 'Only public Wi-Fi networks', 'is_correct' => false],
                        ['text' => 'Encrypted databases', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In the STOP method for resisting social engineering, which step comes first?',
                    'explanation' => 'Slowing down gives you time to think and avoid acting under pressure.',
                    'options' => [
                        ['text' => 'Think', 'is_correct' => false],
                        ['text' => 'Slow down', 'is_correct' => true],
                        ['text' => 'Verify', 'is_correct' => false],
                        ['text' => 'Protect', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'It is safe to share your OTP with customer service if they are trying to help you.',
                    'explanation' => 'No legitimate service will ever ask for your OTP or PIN.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Phishing only happens through email.',
                    'explanation' => 'Phishing can happen through SMS, WhatsApp, phone calls and social media as well as email.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What three-letter code is sent to confirm a mobile money transaction?',
                    'explanation' => 'An OTP, or one-time password, is used to confirm transactions and logins.',
                    'correct_answer' => 'OTP',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'Name one manipulation trick used in social engineering.',
                    'explanation' => 'Attackers use authority, urgency, fear, familiarity, reciprocity or curiosity to manipulate victims.',
                    'correct_answer' => 'Authority',
                ],
            ],
        ];
    }

    private function module4Lessons(): array
    {
        return [
            [
                'title' => '4.1 Securing Your Android Phone and iPhone',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to secure a smartphone using screen locks, biometrics, app permissions, find-my-device features and safe-update habits.</p>

<h2>Why Phone Security Matters</h2>
<p>For many Zambians, the smartphone is the main computer. It holds mobile money apps, WhatsApp business chats, family photos, NRC photos, ZRA login details and more. If your phone is lost, stolen or infected, the damage can be immediate and serious. Good phone security is therefore one of the most valuable skills in this course. It is also important when charging at public places or borrowing cables, because infected charging stations can sometimes transfer malware to your device. A few minutes spent checking settings today can prevent weeks of recovery later.</p>

<h2>Essential Phone Security Steps</h2>
<ol>
<li><strong>Set a strong screen lock</strong> — use a PIN of at least six digits, a pattern that is hard to guess, or a fingerprint/face scan.</li>
<li><strong>Encrypt the device</strong> — modern Android and iPhone models encrypt data automatically when a screen lock is set.</li>
<li><strong>Keep the operating system updated</strong> — updates fix security holes that attackers exploit.</li>
<li><strong>Review app permissions</strong> — a torch app should not need access to contacts or SMS.</li>
<li><strong>Enable Find My Device or Find My iPhone</strong> — this lets you locate, lock or erase a lost phone.</li>
<li><strong>Avoid “rooting” or “jailbreaking”</strong> — these remove built-in security protections.</li>
<li><strong>Turn on automatic app updates</strong> — this ensures security patches are installed quickly.</li>
<li><strong>Back up photos and contacts regularly</strong> — use cloud storage or an SD card so you do not lose memories and numbers if the phone is damaged.</li>
</ol>

<h2>Worked Example: The Lost Phone at the Bus Station</h2>
<p>Patricia boards a minibus in Lusaka and realises her Android phone is missing when she reaches her stop. Because she prepared in advance, she can use a friend’s phone to:</p>
<ol>
<li>call her number in case it fell under a seat,</li>
<li>log into Find My Device from a browser,</li>
<li>lock the phone remotely with a message showing her husband’s number,</li>
<li>check the last known location, and</li>
<li>erase the device when it becomes clear it will not be returned.</li>
</ol>
<p>Patricia loses the handset but not her mobile money, WhatsApp Business contacts or family photos because she had cloud backups and a strong screen lock.</p>

<h2>Worked Example: Permission Check</h2>
<p>A farmer installs a “weather forecast” app. During installation it asks for permission to access contacts, microphone, camera and SMS. A weather app only needs location and internet. The farmer denies the unnecessary permissions. Two weeks later he reads online that the same app was stealing contacts. Because he denied the permissions, his contacts stayed safe.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open your phone’s Settings and find the Security or Privacy section.</li>
<li>Confirm that a screen lock is active and that Find My Device/Find My iPhone is turned on.</li>
<li>Go to Apps &gt; Permissions and remove unnecessary permissions from apps that do not need them.</li>
<li>Check for pending system updates and install them if possible.</li>
<li>Turn on automatic backups for your photos and contacts.</li>
<li>Write down the web address for Find My Device or Find My iPhone and practise logging in from a computer.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Screen lock</strong> — the first barrier that stops someone using your phone.</li>
<li><strong>Encryption</strong> — scrambling data so it cannot be read without the unlock method.</li>
<li><strong>Permissions</strong> — what an app is allowed to access on your device.</li>
<li><strong>Find My Device</strong> — Google’s service to locate, ring or erase a lost Android phone.</li>
<li><strong>Rooting / jailbreaking</strong> — removing manufacturer security restrictions.</li>
<li><strong>Remote wipe</strong> — erasing a lost device from another location.</li>
<li><strong>Biometric lock</strong> — unlocking with a fingerprint or face scan.</li>
</ul>

<h2>Summary</h2>
<p>Your phone is a powerful computer that needs strong protection. Start with a screen lock, updates, sensible app permissions and a find-my-device service. These simple steps protect your money, identity and personal information if your phone is lost or targeted.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/android/answer/6174145">Google Android Security Help</a></li>
<li><a href="https://support.apple.com/en-us/HT201472">Apple — If your iPhone is lost or stolen</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.2 Securing a Windows PC: Updates, Antivirus and Firewall',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to check that a Windows PC is up to date, enable built-in antivirus and firewall protection, and describe safe practices for downloading and installing software.</p>

<h2>Keep Windows Updated</h2>
<p>Microsoft releases security updates regularly. Many attacks succeed only because the victim’s computer is missing an update that was released months earlier. On Windows 10 or 11, go to Settings &gt; Windows Update and check for updates. Install all available updates and restart the computer when asked. If you have a metered connection, schedule updates for when you have affordable data.</p>

<h2>Use Built-In Antivirus</h2>
<p>Windows Security (formerly Windows Defender) is free and built into Windows. It provides real-time protection against viruses, ransomware and spyware. Unless you have a specific business reason to use a different product, Windows Security is sufficient for most users. Make sure it is turned on and that virus definitions are up to date.</p>

<h2>Enable the Firewall</h2>
<p>A firewall controls which programs can communicate over the internet. Windows has a built-in firewall that should remain enabled. Turning it off makes your computer visible to attackers on the network. You can check the firewall status in Control Panel &gt; Windows Defender Firewall.</p>

<h2>Safe Software Habits</h2>
<ul>
<li>Download software only from the official vendor’s website.</li>
<li>Be careful of “free” versions of paid software—these often contain malware.</li>
<li>During installation, choose “Custom” or “Advanced” and decline extra toolbars or antivirus bundles.</li>
<li>Uninstall programs you no longer use.</li>
<li>Keep commonly attacked programs such as browsers, PDF readers and office suites updated.</li>
</ul>

<h2>Worked Example: The Shared Office Computer</h2>
<p>A small NGO in Ndola has one Windows computer that three staff members share. Everyone logs in with the same administrator account and the password is written on a sticky note on the monitor. One staff member installs a “free PDF converter” from a pop-up advert. The installer adds a toolbar, changes the browser homepage, and quietly installs spyware that records keystrokes.</p>
<p>The IT volunteer cleans the computer and then improves security by:</p>
<ol>
<li>creating a standard user account for daily work and a separate admin account for installations,</li>
<li>removing the sticky note and storing the admin password in a password manager,</li>
<li>running a full scan with Windows Security,</li>
<li>uninstalling the fake PDF converter and toolbar,</li>
<li>educating staff to download only from official sites, and</li>
<li>setting Windows Update to install security patches automatically.</li>
</ol>

<h2>Worked Example: Cleaning Up a PC</h2>
<p>A college lab computer had become slow and was showing pop-up adverts. The technician:</p>
<ol>
<li>ran Windows Update and installed all pending updates,</li>
<li>confirmed Windows Security was active and ran a full scan,</li>
<li>removed three browser toolbars installed with “free” games,</li>
<li>uninstalled old versions of Java and Adobe Reader, and</li>
<li>created a standard user account for students, keeping the admin password private.</li>
</ol>
<p>The computer became faster and the adverts stopped.</p>

<h2>Try It Yourself</h2>
<ol>
<li>On a Windows PC, open Settings &gt; Windows Update and check for updates.</li>
<li>Open Windows Security and confirm that Virus &amp; threat protection is active.</li>
<li>Review installed programs in Settings &gt; Apps and remove anything you do not recognise.</li>
<li>Check the Windows Firewall is turned on for all network types.</li>
<li>Create a standard user account for daily use if you currently use an administrator account.</li>
<li>Run a full scan with Windows Security and review the results.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Windows Security</strong> — Microsoft’s built-in antivirus and security suite.</li>
<li><strong>Firewall</strong> — a barrier that controls network traffic in and out of a computer.</li>
<li><strong>Virus definitions</strong> — the database of known malware used by antivirus software.</li>
<li><strong>Malware bundle</strong> — unwanted software installed alongside a requested program.</li>
<li><strong>Admin account</strong> — an account with full control over the computer.</li>
<li><strong>Standard user account</strong> — an account with limited permissions, safer for daily use.</li>
<li><strong>Keystroke logger</strong> — malware that records everything you type.</li>
</ul>

<h2>Summary</h2>
<p>A secure Windows PC is an updated Windows PC with built-in antivirus and firewall active, plus careful software installation habits. You do not need expensive tools to be safe. Regular updates and removal of unnecessary software prevent most common attacks.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/windows/security/">Microsoft Learn — Windows Security</a></li>
<li><a href="https://support.microsoft.com/en-us/windows/keep-your-pc-up-to-date">Microsoft — Keep Your PC Up to Date</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.3 Small Office Network Safety: Router, Wi-Fi and Guests',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to secure a small-office or home Wi-Fi network, explain why router admin passwords matter, and set up a separate guest network where appropriate.</p>

<h2>The Router Is the Front Door</h2>
<p>Your router connects your computers, phones and printers to the internet. If the router is poorly secured, attackers can enter your network, intercept traffic, or use your internet connection for illegal activity. Securing the router is one of the most important steps for any small office. In Zambia, where load-shedding sometimes resets devices, it is also wise to check router settings after a long power outage.</p>

<h2>Router Security Checklist</h2>
<ul>
<li><strong>Change the admin password</strong> — the default password is often “admin” and is easy to find online.</li>
<li><strong>Update router firmware</strong> — manufacturers release security patches just like Microsoft and Google.</li>
<li><strong>Use WPA2 or WPA3 encryption</strong> — do not use WEP or an open network.</li>
<li><strong>Set a strong Wi-Fi password</strong> — at least 12 characters.</li>
<li><strong>Change the default network name (SSID)</strong> — avoid revealing the router brand.</li>
<li><strong>Disable remote management</strong> — unless you have a specific need, do not allow configuration from the internet.</li>
<li><strong>Turn off WPS</strong> — the Wi-Fi Protected Setup button can be exploited by attackers close to the building.</li>
</ul>

<h2>Guest Networks</h2>
<p>If customers, students or visitors regularly use your Wi-Fi, create a guest network. A guest network keeps visitors separated from your office computers, printers and point-of-sale devices. Many modern routers support this feature in their settings.</p>

<h2>Worked Example: The Open Shop Wi-Fi</h2>
<p>Mr Lungu runs a phone-accessory shop in Chipata. He left his Wi-Fi open so customers could use it. One day his printer started printing strange documents and his point-of-sale tablet became slow. An attacker had connected to the open Wi-Fi and reached his business devices. Mr Lungu changed the router admin password, enabled WPA2, set a strong Wi-Fi password and created a separate guest network. The problems stopped.</p>

<h2>Worked Example: Setting Up a Rural Training Centre Network</h2>
<p>A community training centre in Mpika offers free computer lessons to youth. The centre has one router, three office computers, a printer and many student phones connecting every day. The coordinator takes these steps:</p>
<ol>
<li>logs into the router using the address on the label and changes the default admin password to a long passphrase,</li>
<li>checks for a firmware update and installs it,</li>
<li>sets the main Wi-Fi to WPA3 with a 14-character password known only to staff,</li>
<li>creates a guest network called “CTC-Students” with a simpler password that changes every term,</li>
<li>disables remote management and WPS, and</li>
<li>writes the new settings in a notebook stored in the locked office.</li>
</ol>
<p>Students can access the internet for research, but they cannot reach the office printer or finance computer. At the end of each term the coordinator reviews the list of connected devices and changes the guest password, keeping the network clean and secure.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Find your router and locate the label showing the default admin address and password.</li>
<li>Log in and change the admin password to a strong, unique passphrase.</li>
<li>Check that the Wi-Fi security is set to WPA2 or WPA3.</li>
<li>If your router supports it, enable a guest network for visitors.</li>
<li>Look for a firmware update option and install any available update.</li>
<li>Disable WPS and remote management if you do not need them.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Router</strong> — the device that connects your local network to the internet.</li>
<li><strong>Firmware</strong> — the software that runs inside the router.</li>
<li><strong>WPA2 / WPA3</strong> — encryption standards that protect Wi-Fi traffic.</li>
<li><strong>SSID</strong> — the name of your Wi-Fi network.</li>
<li><strong>Guest network</strong> — a separate Wi-Fi network for visitors.</li>
<li><strong>Remote management</strong> — the ability to configure a router from outside the local network.</li>
<li><strong>WPS</strong> — a button-based connection feature that can weaken Wi-Fi security.</li>
</ul>

<h2>Summary</h2>
<p>Your router protects every device on your network. Change default passwords, use strong Wi-Fi encryption, keep firmware updated and separate guests from business devices. These steps turn a vulnerable small office into a much harder target.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.cisa.gov/secure-our-world/secure-your-internet-connected-devices">CISA — Secure Your Internet-Connected Devices</a></li>
<li><a href="https://www.ncsc.gov.uk/collection/small-business-guide/protecting-your-organisation-malware">UK NCSC — Protecting Your Organisation from Malware</a></li>
<li><a href="https://www.cisco.com/c/en/us/training-events/networking-academy.html">Cisco Networking Academy — Skills for All</a></li>
</ul>
HTML,
            ],
            [
                'title' => 'Module 4 Quiz: Securing Devices and Networks',
                'duration_minutes' => 25,
                'type' => 'Quiz',
                'content' => '<p>Demonstrate your knowledge of phone, Windows PC and small-office network security.</p>',
            ],
        ];
    }

    private function module4Quiz(): array
    {
        return [
            'title' => 'Module 4 Quiz: Securing Devices and Networks',
            'description' => 'Check your knowledge of phone, Windows and small-office network security.',
            'time_limit_minutes' => 25,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is the first thing you should do to secure a smartphone?',
                    'explanation' => 'A screen lock is the basic first defence if your phone is lost or stolen.',
                    'options' => [
                        ['text' => 'Install a weather app', 'is_correct' => false],
                        ['text' => 'Set a strong screen lock', 'is_correct' => true],
                        ['text' => 'Root the phone', 'is_correct' => false],
                        ['text' => 'Turn off automatic updates', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does encrypting a phone do?',
                    'explanation' => 'Encryption scrambles data so it cannot be read without the correct unlock method.',
                    'options' => [
                        ['text' => 'Makes the screen brighter', 'is_correct' => false],
                        ['text' => 'Scrambles data so it cannot be read without unlocking', 'is_correct' => true],
                        ['text' => 'Blocks all app installations', 'is_correct' => false],
                        ['text' => 'Increases internet speed', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which built-in tool provides antivirus protection on Windows 10 and 11?',
                    'explanation' => 'Windows Security is the free built-in antivirus and security tool in modern Windows versions.',
                    'options' => [
                        ['text' => 'Windows Firewall only', 'is_correct' => false],
                        ['text' => 'Windows Security', 'is_correct' => true],
                        ['text' => 'Internet Explorer', 'is_correct' => false],
                        ['text' => 'Microsoft Word', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is it risky to leave a router’s default admin password unchanged?',
                    'explanation' => 'Default passwords are public knowledge, so attackers can easily take control of the router.',
                    'options' => [
                        ['text' => 'It makes the Wi-Fi faster', 'is_correct' => false],
                        ['text' => 'Default passwords are publicly known and easy to guess', 'is_correct' => true],
                        ['text' => 'It disables encryption', 'is_correct' => false],
                        ['text' => 'It stops firmware updates', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which Wi-Fi encryption standard should you use?',
                    'explanation' => 'WPA2 and WPA3 are modern encryption standards; WEP is outdated and insecure.',
                    'options' => [
                        ['text' => 'WEP', 'is_correct' => false],
                        ['text' => 'Open network', 'is_correct' => false],
                        ['text' => 'WPA2 or WPA3', 'is_correct' => true],
                        ['text' => 'HTTP', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main benefit of a guest Wi-Fi network?',
                    'explanation' => 'A guest network keeps visitors separated from business devices and sensitive data.',
                    'options' => [
                        ['text' => 'It makes the internet faster for the owner', 'is_correct' => false],
                        ['text' => 'It prevents visitors from using the internet', 'is_correct' => false],
                        ['text' => 'It separates visitor devices from business devices', 'is_correct' => true],
                        ['text' => 'It hides the router password', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Rooting or jailbreaking a phone removes important security protections.',
                    'explanation' => 'Rooting or jailbreaking gives full control but removes built-in security barriers.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A firewall controls which programs can communicate over the network.',
                    'explanation' => 'A firewall filters incoming and outgoing network traffic based on security rules.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the name of the software inside a router that should be kept updated?',
                    'explanation' => 'Firmware is the software that runs hardware devices such as routers.',
                    'correct_answer' => 'Firmware',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What Windows feature provides free built-in antivirus protection?',
                    'explanation' => 'Windows Security provides antivirus, firewall and other protections at no extra cost.',
                    'correct_answer' => 'Windows Security',
                ],
            ],
        ];
    }

    private function module5Lessons(): array
    {
        return [
            [
                'title' => '5.1 Backups and Data Protection Basics',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain why backups matter, choose between local and cloud backup strategies, and set up a simple backup routine for a small business or personal files.</p>

<h2>Why Backups Matter</h2>
<p>Ransomware, phone theft, hardware failure and accidental deletion can all destroy important data. If you have a recent backup, you can recover without paying criminals or losing years of work. A shop that loses its sales records, a student who loses a final-year project, and an office that loses ZRA documents all suffer the same problem: they did not have a backup.</p>

<h2>The 3-2-1 Backup Rule</h2>
<p>A simple and effective backup strategy is:</p>
<ul>
<li><strong>3 copies</strong> of important data — the original plus two backups.</li>
<li><strong>2 different media types</strong> — for example, your computer and an external drive or cloud storage.</li>
<li><strong>1 copy off-site</strong> — stored somewhere else, so a fire or theft at your location does not destroy everything.</li>
</ul>

<h2>Local vs Cloud Backup</h2>
<p><strong>Local backup</strong> means copying files to an external hard drive or USB stick. It is fast and you control the device, but it can be lost or damaged in the same incident as your computer.</p>
<p><strong>Cloud backup</strong> copies files over the internet to a service such as Google Drive, Microsoft OneDrive or Backblaze. It protects against local disasters and often keeps older versions of files. The downside is that it needs internet and may have a subscription cost.</p>

<h2>Worked Example: A Small Business Backup Plan</h2>
<p>A hairdressing salon in Lusaka keeps customer appointments, photos of hairstyles and supplier invoices on one laptop. The owner sets up this plan:</p>
<ol>
<li>Every Friday she copies important folders to an external hard drive stored at home.</li>
<li>Every day, key documents sync automatically to a free Google Drive account.</li>
<li>Once a month she checks that she can open files from both backups.</li>
<li>She keeps the external drive disconnected from the laptop except during backups to protect it from ransomware.</li>
</ol>
<p>When ransomware locks the laptop one Monday, she wipes it, reinstalls Windows and restores her files from the external drive. She loses only the work from Friday evening to Monday morning.</p>

<h2>Worked Example: A Farmer’s Phone Backup</h2>
<p>Mrs Lungu, a poultry farmer in Kalomo, stores customer orders, delivery photos and supplier contacts on her Android phone. One rainy season her phone falls into water and stops working. Because she turned on Google Photos backup and contact sync, she buys a new phone, signs in with her Google account, and recovers all her photos and numbers within an hour. She loses only a few SMS messages. The lesson is that automatic cloud backup can save a small business from a single accident.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Identify the three most important folders or files on your phone or computer.</li>
<li>Copy them to a second location today: a USB drive, an SD card or a cloud storage account.</li>
<li>Set a weekly reminder to repeat the backup.</li>
<li>Test restoring one file to make sure the backup works.</li>
<li>Check whether your phone automatically backs up photos and contacts to the cloud.</li>
<li>Store one backup copy in a different physical location from your main device.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Backup</strong> — a copy of data kept for recovery.</li>
<li><strong>Ransomware</strong> — malware that locks files until a payment is made.</li>
<li><strong>Cloud storage</strong> — storing files on a provider’s servers over the internet.</li>
<li><strong>External drive</strong> — a portable storage device connected by USB.</li>
<li><strong>Off-site backup</strong> — a backup stored in a different physical location.</li>
<li><strong>Sync</strong> — automatically keeping files the same across devices.</li>
<li><strong>Restore</strong> — copying backup data back to its original location.</li>
</ul>

<h2>Summary</h2>
<p>Backups are your insurance against ransomware, theft and accidents. The 3-2-1 rule gives you a practical target: three copies, two media types and one off-site. Start small by backing up your most important files today, then build a regular routine.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/drive/answer/2424384">Google Drive Help — Back Up and Sync</a></li>
<li><a href="https://support.microsoft.com/en-us/windows/backup-and-restore-in-windows-10-7d660603-3257-4c86-9b2a-375a3dbb2755">Microsoft — Backup and Restore in Windows</a></li>
<li><a href="https://www.cisa.gov/secure-our-world/secure-your-data">CISA — Secure Your Data</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.2 Zambia’s Cyber Security Act and Personal Privacy',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe the purpose of Zambia’s Cyber Security and Cyber Crimes Act, list common cyber crimes it addresses, and explain basic privacy responsibilities when handling other people’s information.</p>

<h2>Why Laws Matter</h2>
<p>Technology changes faster than culture, so governments create laws to define unacceptable behaviour online. In Zambia, the Cyber Security and Cyber Crimes Act helps protect citizens, businesses and government systems from online attacks. It also creates penalties for people who commit cyber crimes such as hacking, fraud, identity theft and sharing intimate images without consent.</p>

<h2>Key Areas Covered</h2>
<p>The Act addresses many issues, including:</p>
<ul>
<li><strong>Unauthorised access</strong> — hacking into someone’s computer, phone or account without permission.</li>
<li><strong>Computer misuse</strong> — damaging, deleting or altering data belonging to others.</li>
<li><strong>Cyber fraud</strong> — online scams, phishing and mobile money fraud.</li>
<li><strong>Identity-related crimes</strong> — stealing or using someone’s NRC number, TPIN or personal details.</li>
<li><strong>Cyber bullying and harassment</strong> — using phones or social media to threaten or harm others.</li>
<li><strong>Child online protection</strong> — protecting children from exploitation and harmful content.</li>
</ul>

<h2>Personal Data Protection</h2>
<p>If you run a business, a school group or a community organisation, you probably collect personal information: names, phone numbers, NRC numbers, exam results or medical details. You have a responsibility to:</p>
<ul>
<li>collect only what you really need,</li>
<li>keep it safe from unauthorised access,</li>
<li>not share it without consent, and</li>
<li>delete it when you no longer need it.</li>
</ul>

<h2>Worked Example: The Unprotected PTA List</h2>
<p>A PTA WhatsApp group admin saved a spreadsheet containing parents’ names, phone numbers and children’s names on a shared college computer without a password. A student found the file and shared it as a prank. The parents received unwanted calls. The admin now stores the file on a password-protected account and shares it only with the PTA chairperson and treasurer.</p>

<h2>Worked Example: A Shop’s Customer Database</h2>
<p>Mr Chanda owns an electronics shop in Livingstone. He collects customer names, phone numbers and NRC copies for warranty records. He stores them on a laptop in the back office that has no password and is shared by all staff. One day a former employee copies the list and starts his own business, calling the same customers with lower prices.</p>
<p>Mr Chanda responds by:</p>
<ol>
<li>password-protecting the laptop and the customer spreadsheet,</li>
<li>limiting access to the shop manager and himself,</li>
<li>collecting only name and phone number for most sales,</li>
<li>deleting NRC copies once the warranty period ends, and</li>
<li>training staff that customer data is confidential.</li>
</ol>
<p>These steps reduce the risk of identity-related crimes and build customer trust.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Look at any lists, forms or spreadsheets where you store other people’s information.</li>
<li>Ask: do I still need all this data? Who can access it? Is it protected by a password?</li>
<li>Delete data you no longer need.</li>
<li>Add a password to any spreadsheet or document containing personal details.</li>
<li>Write a short privacy notice for your group or business explaining what data you collect and why.</li>
<li>Check whether you have old photos or copies of NRCs that should be deleted.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Cyber Security and Cyber Crimes Act</strong> — Zambian law covering online security and criminal behaviour.</li>
<li><strong>Personal data</strong> — information that identifies a person, such as name, NRC number or phone number.</li>
<li><strong>Consent</strong> — permission given by a person for their data to be used.</li>
<li><strong>Unauthorised access</strong> — entering a system or account without permission.</li>
<li><strong>Data minimisation</strong> — collecting only the data you actually need.</li>
<li><strong>Identity-related crime</strong> — using someone’s personal details without permission to commit fraud or harm.</li>
<li><strong>Retention</strong> — how long data is kept before it is deleted.</li>
</ul>

<h2>Summary</h2>
<p>Zambia’s cyber security law sets clear boundaries for online behaviour and protects individuals and organisations from cyber crime. Beyond obeying the law, handling personal data responsibly builds trust. Collect less, protect what you keep, and delete what you no longer need.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.zicta.zm/">Zambia Information and Communications Technology Authority (ZICTA)</a></li>
<li><a href="https://www.zra.org.zm/">Zambia Revenue Authority — Secure e-Services</a></li>
<li><a href="https://www.cisa.gov/secure-our-world">CISA — Secure Our World</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.3 What to Do After a Breach',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to take immediate, practical steps after a suspected security breach, know who to report cyber crime to in Zambia, and document an incident clearly for recovery and reporting.</p>

<h2>First Response Matters</h2>
<p>No security plan is perfect. If your phone is stolen, your email is hacked or ransomware locks your files, acting quickly limits the damage. Panic helps attackers, so the goal is to stay calm and follow a clear checklist.</p>

<h2>Immediate Steps After a Breach</h2>
<ol>
<li><strong>Stop and isolate</strong> — disconnect the affected device from the internet. Turn off Wi-Fi and mobile data.</li>
<li><strong>Change passwords</strong> — start with your email, mobile money and banking accounts, then work through others.</li>
<li><strong>Enable or check 2FA</strong> — make sure attackers cannot get back in even if they know a password.</li>
<li><strong>Check for unauthorised activity</strong> — review recent logins, sent messages and transactions.</li>
<li><strong>Scan for malware</strong> — run a full antivirus scan on affected computers.</li>
<li><strong>Restore from backup</strong> — if files are lost or encrypted, use a clean backup.</li>
<li><strong>Report</strong> — contact your bank, mobile money provider and the police if money was stolen.</li>
<li><strong>Warn contacts</strong> — tell friends, customers or colleagues that your account may send suspicious messages.</li>
</ol>

<h2>Reporting Cyber Crime in Zambia</h2>
<p>If you lose money or suffer serious harm, report the incident to:</p>
<ul>
<li>Your bank or mobile money provider immediately.</li>
<li>The nearest police station with a cyber crime unit.</li>
<li>ZICTA for matters related to online communications and electronic transactions.</li>
</ul>
<p>Keep evidence such as screenshots, SMS messages, phone numbers and transaction IDs.</p>

<h2>Documenting an Incident</h2>
<p>A clear record helps you recover and supports any investigation. Write down:</p>
<ul>
<li>date and time you noticed the problem,</li>
<li>what you were doing when it happened,</li>
<li>what data or money may have been lost,</li>
<li>steps you have already taken, and</li>
<li>contact details of anyone involved.</li>
</ul>

<h2>Worked Example: The Stolen Mobile Money Phone</h2>
<p>Mrs Mutale’s phone is snatched while she is shopping in Lusaka. Within ten minutes she:</p>
<ol>
<li>borrows a friend’s phone to call MTN customer care and report the SIM stolen,</li>
<li>uses a computer to change her email and mobile money passwords,</li>
<li>checks her bank app for unauthorised transactions,</li>
<li>asks her son to sign her out of WhatsApp Web so the thief cannot message her contacts, and</li>
<li>visits an MTN shop the next day to get a replacement SIM.</li>
</ol>
<p>Because she acted quickly, the thief cannot use her mobile money and her contacts are warned before any scam messages are sent.</p>

<h2>Worked Example: The Hacked Email Account</h2>
<p>Mr Banda notices emails in his Sent folder that he did not write. He immediately:</p>
<ol>
<li>signs out all sessions in his email settings,</li>
<li>changes his email password using a device he trusts,</li>
<li>turns on 2FA,</li>
<li>checks other accounts for password reset emails,</li>
<li>warns his contacts not to open strange messages from his address, and</li>
<li>reports suspicious logins to his email provider.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Find the “sign out all devices” or “recent activity” page in your email account.</li>
<li>Review the list of devices and locations. If you see anything unfamiliar, sign out and change your password.</li>
<li>Write down the phone number for your mobile money provider’s customer care.</li>
<li>Save ZICTA’s contact details on your phone.</li>
<li>Practise taking a screenshot on your phone so you can capture evidence quickly.</li>
<li>Create a simple incident checklist in your notes app for future reference.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Breach</strong> — an incident where security is broken and data may be exposed.</li>
<li><strong>Incident response</strong> — the process of handling and recovering from a security incident.</li>
<li><strong>Isolation</strong> — disconnecting a device from networks to stop further damage.</li>
<li><strong>Evidence</strong> — information such as screenshots or messages that supports a report.</li>
<li><strong>Recovery</strong> — restoring normal operations after an incident.</li>
<li><strong>Customer care</strong> — the official support channel for your bank or mobile money provider.</li>
<li><strong>Transaction ID</strong> — a unique reference number for a payment or transfer.</li>
</ul>

<h2>Summary</h2>
<p>Breaches happen, but a calm, structured response reduces harm. Disconnect, change passwords, enable 2FA, check for damage, restore from backup and report to the right authorities. Keeping evidence and documenting the incident helps you recover and may help catch the attacker.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.zicta.zm/">ZICTA — Zambia Information and Communications Technology Authority</a></li>
<li><a href="https://www.cisa.gov/secure-our-world/report-cyber-incidents">CISA — Report Cyber Incidents</a></li>
<li><a href="https://support.google.com/mail/answer/50270">Google — Check for Unusual Activity in Gmail</a></li>
</ul>
HTML,
            ],
            [
                'title' => 'Module 5 Quiz: Data Protection, Law and Incident Response',
                'duration_minutes' => 25,
                'type' => 'Quiz',
                'content' => '<p>Test your understanding of backups, data protection, Zambian cyber law and incident response.</p>',
            ],
        ];
    }

    private function module5Quiz(): array
    {
        return [
            'title' => 'Module 5 Quiz: Data Protection, Law and Incident Response',
            'description' => 'Test your understanding of backups, data protection, Zambian cyber law and incident response.',
            'time_limit_minutes' => 25,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'According to the 3-2-1 backup rule, how many copies of important data should you have?',
                    'explanation' => 'The 3-2-1 rule recommends three copies: the original plus two backups.',
                    'options' => [
                        ['text' => 'One copy', 'is_correct' => false],
                        ['text' => 'Two copies', 'is_correct' => false],
                        ['text' => 'Three copies', 'is_correct' => true],
                        ['text' => 'Five copies', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of these is an example of personal data?',
                    'explanation' => 'An NRC number directly identifies a person and must be protected.',
                    'options' => [
                        ['text' => 'The name of a town', 'is_correct' => false],
                        ['text' => 'A person’s NRC number', 'is_correct' => true],
                        ['text' => 'A public weather forecast', 'is_correct' => false],
                        ['text' => 'The college motto', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the first step you should take after discovering a suspected breach?',
                    'explanation' => 'Disconnecting from the internet limits further damage from malware or remote attackers.',
                    'options' => [
                        ['text' => 'Post about it on social media', 'is_correct' => false],
                        ['text' => 'Disconnect the affected device from the internet', 'is_correct' => true],
                        ['text' => 'Delete all your files', 'is_correct' => false],
                        ['text' => 'Wait to see if it gets worse', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which Zambian Act addresses cyber crime and online security?',
                    'explanation' => 'Zambia’s Cyber Security and Cyber Crimes Act covers cyber crime and online security.',
                    'options' => [
                        ['text' => 'The Road Traffic Act', 'is_correct' => false],
                        ['text' => 'The Cyber Security and Cyber Crimes Act', 'is_correct' => true],
                        ['text' => 'The Education Act', 'is_correct' => false],
                        ['text' => 'The Companies Act', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does data minimisation mean?',
                    'explanation' => 'Data minimisation means collecting only the personal information you actually need.',
                    'options' => [
                        ['text' => 'Keeping all data forever', 'is_correct' => false],
                        ['text' => 'Collecting only the data you really need', 'is_correct' => true],
                        ['text' => 'Sharing data with everyone in the organisation', 'is_correct' => false],
                        ['text' => 'Making data as small as possible in file size', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why should you keep evidence such as screenshots after a cyber crime?',
                    'explanation' => 'Evidence supports your report to banks, mobile money providers and law enforcement.',
                    'options' => [
                        ['text' => 'To post them online for attention', 'is_correct' => false],
                        ['text' => 'To help with recovery and reporting', 'is_correct' => true],
                        ['text' => 'To blame someone else', 'is_correct' => false],
                        ['text' => 'To delete the incident faster', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A business should collect as much personal data as possible just in case it is useful later.',
                    'explanation' => 'Collecting unnecessary data increases risk and violates the principle of data minimisation.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'After a breach, you should change passwords starting with your email and money accounts.',
                    'explanation' => 'Email and financial accounts are the most sensitive because they can be used to reset other accounts.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the process of handling and recovering from a security incident called?',
                    'explanation' => 'Incident response is the structured approach to managing and recovering from breaches.',
                    'correct_answer' => 'Incident response',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What type of backup is stored in a different physical location from the original data?',
                    'explanation' => 'An off-site backup protects against local disasters such as fire or theft.',
                    'correct_answer' => 'Off-site backup',
                ],
            ],
        ];
    }

    private function createAssignments(): void
    {
        $now = now();

        Assignment::create([
            'course_id' => $this->courseId,
            'lesson_id' => null,
            'title' => 'Assignment 1: Secure Your Digital Life',
            'description' => 'Apply what you learned in Modules 1–3 by auditing your own digital habits and creating a personal security plan.',
            'instructions' => <<<'TXT'
<p>Submit a single document (PDF, DOC or DOCX) with the following sections:</p>
<ol>
<li><strong>Device audit</strong> — List the phones, laptops and tablets you use. State whether each has a screen lock, automatic updates turned on, and a find-my-device feature enabled.</li>
<li><strong>Password review</strong> — List three accounts you use regularly. For each, state whether the password is unique, strong and whether two-factor authentication is enabled.</li>
<li><strong>Phishing example</strong> — Describe one suspicious message, call or email you have received or heard about. Identify the manipulation tactic used (urgency, fear, authority, familiarity, reciprocity or curiosity) and explain how you verified or would verify it.</li>
<li><strong>Mobile money safety</strong> — Write three rules you will follow to protect your mobile money account.</li>
<li><strong>Action plan</strong> — List five concrete actions you will take this week to improve your security.</li>
</ol>
<p>Your document should be between 500 and 1,000 words. Include your name and date at the top.</p>
TXT,
            'max_points' => 100,
            'passing_points' => 50,
            'allowed_file_types' => 'pdf,doc,docx,jpg,png',
            'max_file_size_mb' => 10,
            'allow_late_submission' => 1,
            'due_date' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        Assignment::create([
            'course_id' => $this->courseId,
            'lesson_id' => null,
            'title' => 'Assignment 2: Small Office Security Plan',
            'description' => 'Design a practical security plan for a small Zambian business based on Modules 4 and 5.',
            'instructions' => <<<'TXT'
<p>Submit a single document (PDF, DOC or DOCX) that presents a security plan for a small business of your choice, such as a shop, salon, school office or farm business.</p>
<ol>
<li><strong>Business profile</strong> — Name the business, its location and the devices and accounts it uses (phones, computers, Wi-Fi, mobile money, email).</li>
<li><strong>Device and network security</strong> — Explain how the business will secure phones, Windows PCs and the Wi-Fi router. Include at least three practical steps.</li>
<li><strong>Backup plan</strong> — Describe a 3-2-1 backup strategy suitable for the business. State what will be backed up, how often and where copies will be stored.</li>
<li><strong>Data protection</strong> — List any personal data the business collects and explain how it will be kept safe and for how long it will be kept.</li>
<li><strong>Incident response</strong> — Create a one-page checklist of what the business should do within the first hour of discovering a breach or fraud attempt.</li>
</ol>
<p>Your document should be between 600 and 1,200 words. Use headings and bullet points to make it easy to read. Include your name and date at the top.</p>
TXT,
            'max_points' => 100,
            'passing_points' => 50,
            'allowed_file_types' => 'pdf,doc,docx,jpg,png',
            'max_file_size_mb' => 10,
            'allow_late_submission' => 1,
            'due_date' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function printSummary(): void
    {
        $moduleCount = Module::where('course_id', $this->courseId)->count();
        $lessonCount = Lesson::whereHas('module', fn ($q) => $q->where('course_id', $this->courseId))->count();
        $quizCount = Quiz::where('course_id', $this->courseId)->count();
        $questionCount = DB::table('quizzes')
            ->join('quiz_questions', 'quizzes.id', '=', 'quiz_questions.quiz_id')
            ->where('quizzes.course_id', $this->courseId)
            ->count();
        $assignmentCount = Assignment::where('course_id', $this->courseId)->count();

        $this->command->info('Cyber Security content seeded successfully.');
        $this->command->table(
            ['Course Component', 'Count'],
            [
                ['Modules', $moduleCount],
                ['Lessons', $lessonCount],
                ['Quizzes', $quizCount],
                ['Questions', $questionCount],
                ['Assignments', $assignmentCount],
            ]
        );
    }
}
