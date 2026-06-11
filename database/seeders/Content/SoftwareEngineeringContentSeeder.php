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

class SoftwareEngineeringContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Software Engineering')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Software Engineering" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in Software Engineering already has modules. Skipping content seed.');
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
                'title' => 'Module 1: Foundations of Software Engineering',
                'description' => 'Discover what software engineering really means beyond writing code, explore the software development life cycle, and analyse a real Zambian case study.',
            ],
            [
                'title' => 'Module 2: Gathering Requirements from Real Users',
                'description' => 'Learn how to talk to real users, capture their needs accurately, and translate interviews into clear requirements and user stories.',
            ],
            [
                'title' => 'Module 3: Designing Before You Code',
                'description' => 'Master basic software design principles, draw flowcharts to represent logic, and write pseudocode that bridges ideas and implementation.',
            ],
            [
                'title' => 'Module 4: Version Control with Git and GitHub',
                'description' => 'Understand why version control matters, learn Git fundamentals, and collaborate with others using GitHub pull requests and code reviews.',
            ],
            [
                'title' => 'Module 5: Testing and Deploying Software',
                'description' => 'Explore why bugs exist, learn fundamental testing techniques, and understand how to deploy simple applications to the web.',
            ],
            [
                'title' => 'Module 6: Teams, Methodologies, and Your Career',
                'description' => 'Compare Agile and Waterfall approaches, practise team communication and documentation, and map out software engineering careers and freelancing from Zambia.',
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

    // ============================================================
    // MODULE 1
    // ============================================================

    private function module1Lessons(): array
    {
        return [
            [
                'title' => '1.1 What Is Software Engineering and Why Does It Matter?',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain the difference between "coding" and "software engineering" in your own words, describe why structured processes matter when building programs that real people depend on, and identify the key qualities that make software reliable and maintainable.</p>

<h2>Beyond Just Writing Code</h2>
<p>Many people think software engineering is simply "writing code." While coding is an important skill, it is only one part of a much larger picture. Imagine you want to build a house. You could buy bricks and cement and start laying walls, but without a plan, a budget, an understanding of who will live there, and inspections along the way, the house might collapse or fail to meet the family's needs. Software engineering is the disciplined approach to building software so that it works correctly, can be improved over time, and serves the people who use it.</p>
<p>In Zambia, a self-taught developer might write a Python script to calculate prices for a market stall. That is coding. But if that same person is hired to build a mobile-money agent management system for a company in Lusaka, they need software engineering. The system must handle thousands of transactions, protect sensitive customer data, work when the internet is slow, and be updated when ZRA tax rules change. Building that system requires planning, design, testing, documentation, and teamwork — not just typing code into an editor.</p>

<h2>What Is Software Engineering?</h2>
<p>Software engineering is the application of engineering principles to the design, development, maintenance, testing, and evaluation of software. It treats software creation as an engineering discipline rather than an artistic craft. This means we follow systematic methods, use proven tools, measure quality, and document our decisions so that other people can understand and improve our work.</p>
<p>The term was first coined in the 1960s when people realised that writing large programs without structure led to disasters: projects ran over budget, software crashed, and maintenance became impossible. Today, software engineering includes requirements gathering, system design, coding, testing, deployment, and ongoing maintenance. It also involves understanding the business context, user needs, and ethical responsibilities.</p>

<h2>Key Qualities of Good Software</h2>
<p>Not all software is equal. Good software engineering produces systems that share these qualities:</p>
<ul>
<li><strong>Reliability</strong> — The software performs correctly under expected conditions. A ZESCO token purchasing app that crashes when network latency is high is unreliable.</li>
<li><strong>Maintainability</strong> — Other developers can read, understand, and modify the code months or years later. Clear naming, comments, and documentation make this possible.</li>
<li><strong>Scalability</strong> — The system can handle growth. A bus-ticket booking app that works for ten users but fails for a thousand needs better engineering.</li>
<li><strong>Usability</strong> — Real people can use it without frustration. A farmer in Mongu should be able to check crop prices on your app without calling a nephew for help.</li>
<li><strong>Security</strong> — Sensitive data is protected. An app that stores NRC numbers or mobile money PINs in plain text is dangerous.</li>
</ul>

<h2>Worked Example: The Informal System</h2>
<p>Mr Mutale runs a small hardware shop in Kalomo. He keeps stock records in a notebook. When a customer asks if he has roofing sheets, he walks to the back store and checks. This "system" works for one shop, but it has problems:</p>
<ol>
<li>Only Mr Mutale knows where everything is recorded.</li>
<li>If the notebook is lost in a rainy season flood, the records disappear.</li>
<li>He cannot quickly see which items are running low.</li>
<li>His son, who studies in Lusaka, cannot help manage stock from a distance.</li>
</ol>
<p>A software engineer would not simply write a program. They would first interview Mr Mutale, understand his daily routine, design a simple database, create a user-friendly interface, test it with real data, train Mr Mutale to use it, and plan for backups. That complete process is software engineering.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Think of a small business or organisation you know in Zambia. Write down three problems they face that software could solve.</li>
<li>For each problem, identify whether a simple script would be enough, or whether a full software engineering process is needed. Explain why.</li>
<li>Search online for one news story about a software failure (for example, a website crash or data breach). Write two sentences describing what went wrong and how better engineering might have prevented it.</li>
<li>List the five qualities of good software from this lesson. Next to each one, write a real Zambian example where that quality matters.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Software engineering</strong> — the disciplined application of engineering principles to design, build, test, and maintain software systems.</li>
<li><strong>Coding</strong> — the act of writing instructions in a programming language; one part of software engineering.</li>
<li><strong>Reliability</strong> — the ability of software to perform correctly and consistently under normal conditions.</li>
<li><strong>Maintainability</strong> — how easily software can be understood, modified, and extended by other developers.</li>
<li><strong>Scalability</strong> — the capacity of a system to handle increased workload or growth without failing.</li>
</ul>

<h2>Summary</h2>
<p>Software engineering is far broader than coding. It is a structured discipline that ensures software is reliable, maintainable, scalable, usable, and secure. Whether you are building a simple tool for a local shop or a national payment platform, the principles of software engineering help you deliver something that works, lasts, and serves real people well.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn">MDN Web Docs — Learn Web Development</a></li>
<li><a href="https://www.freecodecamp.org/news/what-is-software-engineering/">freeCodeCamp — What Is Software Engineering?</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/paths/software-engineering/">Microsoft Learn — Software Engineering Fundamentals</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-science">Khan Academy — Computer Science Principles</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.2 The Software Development Life Cycle',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to name and describe the main phases of the software development life cycle, explain why each phase matters, and recognise how skipping a phase can lead to project failure. You will also be able to map the SDLC to a simple real-world project.</p>

<h2>What Is the SDLC?</h2>
<p>The Software Development Life Cycle, or SDLC, is the structured process that software teams follow to plan, create, test, and deploy software. Think of it as the recipe for building a reliable program. Just as you would not start baking a cake without knowing who will eat it, how many guests there are, and what ingredients you have, a software engineer does not start coding without understanding what the software must do, who will use it, and what constraints exist.</p>
<p>The SDLC is important because software projects are complex. Requirements change, technology evolves, users discover problems, and teams lose members. A clear process helps everyone stay aligned, catch mistakes early, and deliver something useful on time and within budget.</p>

<h2>The Six Main Phases</h2>
<p>While different organisations use slightly different names, most SDLC models include these six phases:</p>

<h3>1. Planning and Requirements Analysis</h3>
<p>Before any code is written, the team asks: What problem are we solving? Who are the users? What is the budget? What is the deadline? In Zambia, this might mean visiting a mobile money agency in Livingstone to watch how agents record transactions, interviewing shop owners about their stock-tracking pain points, or reading ZRA guidelines to understand what data a tax app must collect.</p>

<h3>2. System Design</h3>
<p>Once requirements are clear, architects and senior developers design the system. They choose technologies, sketch database tables, plan how different parts will communicate, and create user interface mock-ups. For a Zambian farmer-input voucher system, the design must consider offline usage, low-bandwidth sync, and local language support.</p>

<h3>3. Implementation (Coding)</h3>
<p>This is where developers write the actual code. However, coding within the SDLC is disciplined. Developers follow style guides, write unit tests alongside their code, and commit changes to version control regularly. They do not work in isolation; they integrate their code with the team's work daily or weekly.</p>

<h3>4. Testing</h3>
<p>Testing verifies that the software works as intended. Testers check that buttons do what they promise, that calculations are accurate, that the app handles errors gracefully, and that security holes are closed. A payment app must be tested with real Kwacha amounts, with fake "insufficient balance" scenarios, and with network interruptions.</p>

<h3>5. Deployment</h3>
<p>Deployment means releasing the software to users. This might involve uploading files to a web server, publishing an app to the Google Play Store, or installing software on computers at a government office. Deployment plans include rollback strategies: if the new version breaks, how quickly can the old version be restored?</p>

<h3>6. Maintenance</h3>
<p>Software is never truly "finished." Users report bugs, laws change, new devices appear, and security vulnerabilities are discovered. Maintenance includes fixing defects, adding small features, updating documentation, and optimising performance. A well-engineered system is easier to maintain because its structure is clear and its code is documented.</p>

<h2>Why Skipping Phases Is Dangerous</h2>
<p>A common mistake among beginners is to jump straight to coding. This is like building a house without a blueprint. You might finish quickly, but the result often has these problems:</p>
<ul>
<li><strong>Missing features</strong> — Users expected mobile money integration, but you built only cash handling.</li>
<li><strong>Poor usability</strong> — The interface confuses non-technical users such as market vendors.</li>
<li><strong>Hidden bugs</strong> — Without systematic testing, critical errors appear only after launch.</li>
<li><strong>Unmaintainable code</strong> — Six months later, neither you nor anyone else can understand what you wrote.</li>
</ul>

<h2>Worked Example: Applying the SDLC to a School Result Portal</h2>
<p>Suppose a secondary school in Kalomo wants a portal where parents can check their children's exam results online. Here is how the SDLC applies:</p>
<ol>
<li><strong>Planning</strong> — Interview the head teacher, parents, and the school bursar. Discover that parents use mostly Android phones, that data costs matter, and that results must be kept private.</li>
<li><strong>Design</strong> — Design a lightweight mobile-friendly website. Plan a database with tables for students, subjects, grades, and parent accounts. Decide to send results by SMS as a backup for parents with slow internet.</li>
<li><strong>Coding</strong> — Developers build the site using PHP and MySQL, following coding standards and committing to Git daily.</li>
<li><strong>Testing</strong> — Test with dummy data. Verify that Parent A sees only Child A's results. Check that the SMS service works on Airtel and MTN networks.</li>
<li><strong>Deployment</strong> — Host the site on a local server with SSL encryption. Train two teachers to upload results.</li>
<li><strong>Maintenance</strong> — After launch, add a feature for termly report cards. Fix a bug where special characters in student names break the PDF generator.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Draw the six SDLC phases in a circle or flowchart on paper. Label each phase and write one sentence describing what happens.</li>
<li>Pick a simple app idea (for example, "a tool for Soweto Market vendors to share wholesale prices"). For each SDLC phase, write two sentences describing what you would do.</li>
<li>Interview a small business owner you know. Ask them about a time they bought or used software that did not meet their needs. Which SDLC phase do you think was skipped or done poorly?</li>
<li>Search online for "software project failure case study" and read one article. Identify which phases of the SDLC were neglected.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>SDLC</strong> — Software Development Life Cycle; the structured process of planning, designing, building, testing, deploying, and maintaining software.</li>
<li><strong>Requirements analysis</strong> — the phase where teams discover and document what the software must do.</li>
<li><strong>Deployment</strong> — releasing software to production so real users can access it.</li>
<li><strong>Maintenance</strong> — ongoing work to fix bugs, improve performance, and adapt software to changing needs.</li>
<li><strong>Rollback</strong> — reverting to a previous version of software when a new deployment causes problems.</li>
</ul>

<h2>Summary</h2>
<p>The Software Development Life Cycle provides a repeatable framework for building software that works. Each phase — planning, design, coding, testing, deployment, and maintenance — plays an essential role. Skipping phases saves time in the short term but creates costly problems later. Whether you are building a national platform or a tool for a local shop, respecting the SDLC is a hallmark of professional software engineering.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.freecodecamp.org/news/software-development-life-cycle-sdlc/">freeCodeCamp — SDLC Explained</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/intro-to-tech-stack/">Microsoft Learn — Introduction to the Tech Stack</a></li>
<li><a href="https://www.w3schools.com/whatis/whatis_sdlc.asp">W3Schools — What Is SDLC?</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.3 Case Study: Building a Mobile Money Agent App in Zambia',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to apply the SDLC to a realistic Zambian case study, identify the stakeholders and constraints of a mobile money agent management system, and explain why software engineering decisions must consider local infrastructure such as intermittent power and varying network quality.</p>

<h2>The Case Study</h2>
<p>Chisenga Technologies is a small software company in Lusaka. They have been asked to build a mobile money agent management app for a network of agents across Zambia. These agents help customers deposit cash, withdraw money, buy airtime, and pay bills using Airtel Money and MTN MoMo. Currently, each agent keeps handwritten ledgers. The head office cannot see real-time balances, fraud is hard to detect, and agents sometimes run out of float without warning.</p>
<p>The client wants an Android app that agents use on their phones, plus a web dashboard for managers in Lusaka. The budget is modest. The deadline is three months. The app must work in areas with poor internet and during load-shedding. This is a perfect case study for seeing software engineering in action.</p>

<h2>Phase 1: Planning and Requirements</h2>
<p>The Chisenga team travels to agent locations in Lusaka, Kitwe, and Kalomo. They observe that agents often work under a tree or in a small shop with one power socket shared among several phones. They interview agents and learn that:</p>
<ul>
<li>Agents need to record every deposit and withdrawal in Kwacha.</li>
<li>They need to see their current "float" (available cash balance) at a glance.</li>
<li>The app must work offline because mobile data is expensive and 3G is unreliable in rural areas.</li>
<li>Managers need daily summaries by SMS or email, not just a web dashboard.</li>
<li>The app must be simple enough for agents who have basic smartphone skills.</li>
</ul>
<p>The team documents these requirements and gets the client to sign off. They also identify risks: power cuts, network failures, phone theft, and the need for local language support.</p>

<h2>Phase 2: Design Decisions</h2>
<p>The architects make several key design choices:</p>
<ul>
<li><strong>Offline-first architecture</strong> — The app stores transactions locally on the phone using a lightweight database. When the network is available, it syncs to a central server.</li>
<li><strong>Minimal data usage</strong> — Sync packets are compressed. Images are avoided. The app works on 2G networks.</li>
<li><strong>Battery efficiency</strong> — Background sync is limited to once per hour to preserve battery during load-shedding.</li>
<li><strong>Simple interface</strong> — Large buttons, clear labels in English and Bemba, and confirmation dialogs for every transaction.</li>
<li><strong>Security</strong> — Each agent logs in with a PIN. Transactions are encrypted. The app locks after five minutes of inactivity.</li>
</ul>

<h2>Phase 3: Coding with Constraints</h2>
<p>Developers write the Android app in Kotlin and the dashboard in PHP with a MySQL database. They follow a coding standard so that if one developer leaves, another can take over. They use Git for version control and commit code daily. Unit tests verify that calculations such as commission and float balance are always accurate. They simulate network failures in testing to ensure the offline mode works correctly.</p>

<h2>Phase 4: Testing in the Real World</h2>
<p>Before full release, Chisenga recruits ten agents in Lusaka for a pilot. Testers discover that the app crashes when an agent enters a comma in a Kwacha amount (for example, "1,500" instead of "1500"). They fix this bug. They also find that the sync fails on MTN networks between midnight and 4 a.m. because of carrier maintenance windows. They adjust the sync schedule. Without real-world testing, these issues would have frustrated hundreds of agents.</p>

<h2>Phase 5 and 6: Deployment and Maintenance</h2>
<p>The app is deployed via the Google Play Store for easy updates. The team trains regional supervisors to install the app and troubleshoot basic problems. Maintenance includes pushing updates when ZRA changes the tax reporting format, adding a Nyanja language option after user requests, and optimising the sync logic when the agent network grows from one hundred to one thousand users.</p>

<h2>Try It Yourself</h2>
<ol>
<li>List five stakeholders in the Chisenga case study. For each one, write one sentence describing what they need from the software.</li>
<li>Identify three constraints specific to Zambia that influenced the design. Explain how the design addressed each one.</li>
<li>Imagine you are a tester. Write three test cases for the agent app: one for a normal deposit, one for offline mode, and one for a wrong PIN entry.</li>
<li>Research one real Zambian fintech or mobile money company. Write a paragraph describing what software engineering challenges they likely face.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Stakeholder</strong> — any person or group with an interest in the success of a software project, such as users, clients, or managers.</li>
<li><strong>Float</strong> — the amount of cash or electronic balance available to a mobile money agent to serve customer transactions.</li>
<li><strong>Offline-first</strong> — a design approach where software functions without an internet connection and syncs data when connectivity returns.</li>
<li><strong>Pilot</strong> — a small-scale trial of software with real users before a full launch.</li>
<li><strong>Sync</strong> — the process of updating data between a local device and a central server so both have the latest information.</li>
</ul>

<h2>Summary</h2>
<p>The Chisenga Technologies case study shows how software engineering principles apply directly to Zambian reality. Requirements must be gathered from real agents in the field. Design must accommodate power cuts, expensive data, and varying network quality. Testing must happen with actual users before launch. And maintenance must continue as the business grows and regulations change. Good software engineering is not theoretical; it is practical problem-solving for real people.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn">MDN Web Docs — Learn Web Development</a></li>
<li><a href="https://www.freecodecamp.org/news/what-is-software-engineering/">freeCodeCamp — Software Engineering Explained</a></li>
<li><a href="https://grow.google/intl/en_uk/">Grow with Google — Digital Skills</a></li>
</ul>
HTML,
            ],
        ];
    }

    // ============================================================
    // MODULE 2
    // ============================================================

    private function module2Lessons(): array
    {
        return [
            [
                'title' => '2.1 How to Interview Real Users and Gather Requirements',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to plan and conduct a user interview, ask questions that reveal real needs rather than surface wishes, document findings accurately, and distinguish between functional requirements (what the system must do) and non-functional requirements (how well it must do it).</p>

<h2>Why Talk to Users?</h2>
<p>The single most common reason software projects fail is that developers build the wrong thing. They assume they know what users want, or they copy features from foreign apps that do not match local habits. In Zambia, an app designed for London bankers will fail for Kalomo market vendors because the users, their devices, their network conditions, and their daily problems are completely different.</p>
<p>Talking to real users is called <strong>requirements elicitation</strong>. It is not a casual chat. It is a structured process of discovering what people actually do, what frustrates them, what they value, and what constraints they face. The goal is not to ask "What features do you want?" — because users often describe symptoms, not root problems. The goal is to understand their world deeply enough that you can design the right solution.</p>

<h2>Preparing for an Interview</h2>
<p>Before you meet a user, do your homework. If you are building an app for a poultry farm, learn basic poultry terminology. If you are building for ZRA tax filers, read the tax guidelines. Prepare a list of open-ended questions that begin with "how," "what," or "why." Avoid leading questions such as "Would you like a button here?" because they suggest answers.</p>
<p>Good preparation also means choosing the right people. Interview not only the main user but also people nearby: a shopkeeper's assistant, a farmer's spouse, a head teacher's clerk. These people often see problems the main user does not notice.</p>

<h2>Types of Requirements</h2>
<p>Requirements fall into two broad categories:</p>
<ul>
<li><strong>Functional requirements</strong> describe what the system must do. Examples: "The app shall calculate commission in Kwacha." "The system shall send an SMS when float drops below K500."</li>
<li><strong>Non-functional requirements</strong> describe qualities the system must have. Examples: "The app shall load within three seconds on a 2G network." "The system shall encrypt all NRC numbers." "The interface shall support users with primary-school reading levels."</li>
</ul>
<p>Both types are essential. A system that does the right things badly is as useless as a beautiful system that does the wrong things.</p>

<h2>Worked Example: Interviewing a Market Vendor</h2>
<p>Imagine you are building a stock-tracking app for vendors at Soweto Market. You interview Mrs Banda, who sells dried fish, groundnuts, and cooking oil. Here is how a good interview flows:</p>
<ol>
<li><strong>Context</strong> — "Tell me about a typical market day. What time do you arrive? What do you do first?" Mrs Banda explains that she opens at 06:00, counts leftover stock from yesterday, and decides what to buy from the wholesale depot.</li>
<li><strong>Pain points</strong> — "What is the hardest part of keeping track of what you have?" She says she sometimes sells on credit and forgets who owes what. She also runs out of groundnuts unexpectedly because she does not track sales daily.</li>
<li><strong>Current workarounds</strong> — "How do you handle this now?" She shows you a small notebook with scribbled names and amounts. She admits she lost one notebook during the rainy season.</li>
<li><strong>Values</strong> — "If you had a magic tool that solved one problem, which would you choose?" She says tracking credit customers is most important because lost debts hurt her family income.</li>
<li><strong>Constraints</strong> — "What phone do you use? How do you feel about typing?" She has an older Android phone with a cracked screen. She prefers voice notes over typing.</li>
</ol>
<p>From this interview, you now know that credit tracking is the highest-priority feature, that voice input may be valuable, and that the app must work on old Android devices with small screens.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a person you know who uses any kind of software or digital tool for work. Write five open-ended interview questions about their daily routine, frustrations, and wishes.</li>
<li>Conduct the interview. Take notes or record it with permission. Identify at least two functional requirements and two non-functional requirements from what they say.</li>
<li>Write a one-paragraph summary of your interview. Include: who you spoke to, their role, the biggest problem they face, and one surprising thing you learned.</li>
<li>Compare your findings with a classmate who interviewed someone different. Discuss how the requirements would change if you were building for both users.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Requirements elicitation</strong> — the process of discovering and documenting what users need from a software system.</li>
<li><strong>Functional requirement</strong> — a specific action or behaviour the software must perform.</li>
<li><strong>Non-functional requirement</strong> — a quality or constraint the software must satisfy, such as speed, security, or usability.</li>
<li><strong>Stakeholder</strong> — any person or group affected by or interested in the software.</li>
<li><strong>Open-ended question</strong> — a question that cannot be answered with a simple yes or no, encouraging detailed responses.</li>
</ul>

<h2>Summary</h2>
<p>Gathering requirements by interviewing real users is the foundation of successful software. It prevents you from building the wrong thing, reveals constraints you might never have imagined, and earns user trust before a single line of code is written. Always distinguish functional requirements from non-functional ones, and remember that the best interviews happen in the user's real environment — a market stall, a farm, or an office — not in a conference room.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.freecodecamp.org/news/how-to-conduct-user-interviews/">freeCodeCamp — How to Conduct User Interviews</a></li>
<li><a href="https://grow.google/intl/en_uk/">Grow with Google — Digital Skills and UX Basics</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-science/algorithms">Khan Academy — Algorithms and Problem Solving</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.2 Writing User Stories and Acceptance Criteria',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to write clear user stories in the standard format, define acceptance criteria that make stories testable, and organise stories into a product backlog. You will also understand why user stories are more useful than long specification documents for many Zambian software projects.</p>

<h2>What Is a User Story?</h2>
<p>A user story is a short, simple description of a feature told from the perspective of the person who wants it. It captures who needs something, what they need, and why. User stories are popular in Agile software development because they are easy to understand, quick to write, and flexible enough to change as a project evolves.</p>
<p>The standard format is:</p>
<blockquote>
<p>As a <strong>[type of user]</strong>, I want <strong>[some goal]</strong> so that <strong>[some reason]</strong>.</p>
</blockquote>
<p>For example: "As a mobile money agent, I want to see my current float balance so that I know whether I can serve a large withdrawal request." This one sentence tells the developer who the user is, what feature is needed, and why it matters. It is far more useful than a vague requirement such as "The system should show balances."</p>

<h2>Why User Stories Work in Zambia</h2>
<p>Large specification documents work well in massive corporations with stable requirements. In Zambia, many software projects serve small businesses, NGOs, or government departments with limited budgets and shifting priorities. A fifty-page specification takes weeks to write and becomes outdated before coding starts. User stories, by contrast, can be written in an afternoon, discussed with stakeholders immediately, and revised without rewriting a whole document.</p>
<p>User stories also force developers to think about the human being behind the feature. A story about "a farmer checking crop prices" keeps the team focused on real value rather than technical elegance.</p>

<h2>Writing Good User Stories</h2>
<p>Good stories follow the <strong>INVEST</strong> principles:</p>
<ul>
<li><strong>Independent</strong> — The story can be built and delivered without waiting for every other story.</li>
<li><strong>Negotiable</strong> — The details are open to discussion between developers and users.</li>
<li><strong>Valuable</strong> — It delivers clear value to a real user.</li>
<li><strong>Estimable</strong> — Developers can roughly guess how long it will take.</li>
<li><strong>Small</strong> — It fits within one or two weeks of work.</li>
<li><strong>Testable</strong> — You can verify whether it works.</li>
</ul>

<h2>Acceptance Criteria</h2>
<p>Acceptance criteria define when a story is "done." They are specific conditions that must be met for the story to be accepted by the user or product owner. Without acceptance criteria, developers and users may disagree about whether a feature is complete.</p>
<p>For the float-balance story, acceptance criteria might be:</p>
<ul>
<li>The balance updates automatically after every transaction.</li>
<li>The balance is visible on the home screen within two seconds of opening the app.</li>
<li>If the balance is below K500, a yellow warning banner appears.</li>
<li>The balance is accurate to the nearest ngwee.</li>
</ul>
<p>Notice that each criterion is testable. A tester can open the app, perform a transaction, and verify the banner colour. There is no ambiguity.</p>

<h2>Worked Example: A Chicken-Rearing Business</h2>
<p>Mrs Nkhoma runs a small chicken-rearing business near Kalomo. She wants software to help her track eggs, sales, and feed costs. Here are three well-written user stories:</p>
<ol>
<li>As Mrs Nkhoma, I want to record how many eggs I collect each morning so that I can spot when my hens are laying less and investigate health problems.</li>
<li>As Mrs Nkhoma, I want to log every sale of eggs or chickens with the price and customer name so that I know how much money I have made.</li>
<li>As Mrs Nkhoma, I want to see a simple weekly profit summary so that I can decide whether to expand my flock or reduce feed purchases.</li>
</ol>
<p>Acceptance criteria for Story 2:</p>
<ul>
<li>The sale form has fields for date, item (eggs or chickens), quantity, price per unit, and customer name.</li>
<li>The total amount is calculated automatically (quantity × price).</li>
<li>The record is saved even if the phone is offline.</li>
<li>All records can be viewed in a list sorted by date.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Think of a simple app for a Zambian context (for example, a church donation tracker, a boda-boda fare calculator, or a school attendance app).</li>
<li>Write five user stories using the standard format. Each story should represent a different type of user or a different goal.</li>
<li>For each story, write three to four acceptance criteria that make it testable.</li>
<li>Review your stories against the INVEST principles. Which principle is hardest to satisfy? Rewrite one story to improve it.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>User story</strong> — a short description of a feature from the perspective of the user who needs it.</li>
<li><strong>Acceptance criteria</strong> — specific, testable conditions that must be met for a user story to be considered complete.</li>
<li><strong>Product backlog</strong> — an ordered list of user stories, features, and tasks waiting to be developed.</li>
<li><strong>INVEST</strong> — a set of qualities (Independent, Negotiable, Valuable, Estimable, Small, Testable) that make a good user story.</li>
<li><strong>Product owner</strong> — the person responsible for defining what the product should do and prioritising the backlog.</li>
</ul>

<h2>Summary</h2>
<p>User stories are a lightweight, human-centred way to capture software requirements. They keep the focus on real users, encourage conversation between developers and stakeholders, and adapt well to changing needs. Pairing each story with clear acceptance criteria ensures that everyone agrees when a feature is truly done. For small and medium projects in Zambia, user stories are often more practical than heavy specification documents.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.freecodecamp.org/news/how-to-write-user-stories/">freeCodeCamp — How to Write User Stories</a></li>
<li><a href="https://www.w3schools.com/agile/agile_user_stories.asp">W3Schools — Agile User Stories</a></li>
<li><a href="https://grow.google/intl/en_uk/">Grow with Google — Project Management Basics</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.3 From Requirements to Specifications',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to convert informal user stories and interview notes into a structured software requirements specification, recognise the difference between user requirements and system requirements, and create a simple specification document that a developer can use to start designing a solution.</p>

<h2>From Conversation to Document</h2>
<p>User interviews and stories give you rich, qualitative information. But before developers start building, someone must organise this information into a clear, structured document called a <strong>Software Requirements Specification</strong>, or SRS. The SRS bridges the gap between "what users said" and "what the system must do." It becomes the contract between the client and the development team.</p>
<p>In Zambia, where many projects are funded by grants or have tight budgets, a clear SRS protects both sides. The client can point to the document and say, "This is what I paid for." The developer can say, "This is what was agreed, and here is proof that it works." Without an SRS, projects often end in disputes about scope, delays, and unpaid invoices.</p>

<h2>What Goes into an SRS?</h2>
<p>A simple but complete SRS includes these sections:</p>
<ol>
<li><strong>Introduction</strong> — Purpose of the document, intended audience, and scope of the project.</li>
<li><strong>Overall Description</strong> — Product perspective, user classes, operating environment, and constraints.</li>
<li><strong>Functional Requirements</strong> — Detailed list of what the system must do. Often numbered for traceability.</li>
<li><strong>Non-Functional Requirements</strong> — Performance, security, reliability, usability, and compatibility requirements.</li>
<li><strong>External Interface Requirements</strong> — How the system interacts with users, hardware, other software, or communication systems.</li>
<li><strong>Data Requirements</strong> — What data the system stores, how it is structured, and how long it is retained.</li>
</ol>

<h2>User Requirements vs System Requirements</h2>
<p>User requirements are written in everyday language that non-technical stakeholders can understand. For example: "The farmer should be able to check crop prices without an internet connection." System requirements translate this into technical terms that developers understand. For example: "The mobile application shall cache the latest price list in local SQLite storage and display cached data when the device reports no network connectivity."</p>
<p>Both are necessary. User requirements keep the team focused on value. System requirements give developers precise targets.</p>

<h2>Worked Example: Specification for a PTA Communication App</h2>
<p>A parent-teacher association in Kalomo wants an app to replace the noisy WhatsApp group that buries important announcements. Here is how part of an SRS might look:</p>

<h3>Functional Requirement FR-03: Send Announcement</h3>
<p>The system shall allow authorised users (head teacher and class representatives) to compose and send announcements. The announcement shall include a title, body text, and optional attachment. Upon sending, the system shall push a notification to all registered parents within sixty seconds.</p>

<h3>Non-Functional Requirement NFR-02: Notification Reliability</h3>
<p>The system shall deliver push notifications to at least ninety-five percent of active devices within two minutes under normal network conditions. If a device is offline, the notification shall be queued and delivered when connectivity returns.</p>

<h3>Non-Functional Requirement NFR-05: Low Data Usage</h3>
<p>A typical text announcement with no attachment shall consume no more than five kilobytes of mobile data per recipient.</p>

<p>These requirements are specific, measurable, and testable. A developer knows exactly what to build, and a tester knows exactly how to verify it.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose one of your user stories from Lesson 2.2. Rewrite it as both a user requirement and a system requirement.</li>
<li>Draft a two-page Software Requirements Specification for a simple app idea. Include at least: introduction, three functional requirements, and two non-functional requirements.</li>
<li>Show your draft to a friend who is not technical. Ask them to read one functional requirement and explain what the system does. If they are confused, rewrite it in clearer language.</li>
<li>Search online for "software requirements specification template." Compare your draft with a professional template and identify two sections you could add.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>SRS</strong> — Software Requirements Specification; a document that formally defines what a software system must do.</li>
<li><strong>User requirement</strong> — a requirement expressed in language that non-technical stakeholders can understand.</li>
<li><strong>System requirement</strong> — a technical requirement that specifies how the system must behave internally.</li>
<li><strong>Traceability</strong> — the ability to track each requirement from its origin through design, coding, and testing.</li>
<li><strong>Scope</strong> — the boundaries of what a project will and will not include.</li>
</ul>

<h2>Summary</h2>
<p>Turning informal user stories into a structured Software Requirements Specification is a critical step in professional software engineering. The SRS protects clients and developers, provides a basis for design and testing, and ensures that everyone agrees on what success looks like. Good requirements are specific, measurable, and written at two levels: user requirements for stakeholders and system requirements for developers.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/agile/agile_user_stories.asp">W3Schools — Agile and Requirements</a></li>
<li><a href="https://learn.microsoft.com/en-us/azure/devops/manage/requirements/">Microsoft Learn — Requirements Management</a></li>
<li><a href="https://www.freecodecamp.org/news/software-requirements-specification/">freeCodeCamp — Writing a Software Requirements Specification</a></li>
</ul>
HTML,
            ],
        ];
    }


    // ============================================================
    // MODULE 3
    // ============================================================

    private function module3Lessons(): array
    {
        return [
            [
                'title' => '3.1 Software Design Principles for Beginners',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain why design comes before coding, describe the concepts of modularity, abstraction, and separation of concerns, and apply these principles to break a complex problem into smaller, manageable pieces that can be built and tested independently.</p>

<h2>Why Design Matters</h2>
<p>Imagine you are building a house. You could buy bricks and start laying walls wherever it feels right, but the result would likely have no proper foundation, rooms that do not connect, and plumbing that misses the bathroom. Software design serves the same purpose as architectural blueprints: it gives you a plan before you invest time and money in construction.</p>
<p>In software engineering, design is the phase where you decide how the system will be structured. You identify the main components, define how they communicate, choose data formats, and plan for future changes. Good design makes coding faster, testing easier, and maintenance cheaper. Bad design creates a mess that developers call "spaghetti code" — tangled, fragile, and impossible to improve.</p>

<h2>Modularity</h2>
<p><strong>Modularity</strong> means breaking a system into separate, self-contained parts called <strong>modules</strong>. Each module has a specific job and communicates with other modules through well-defined interfaces. For example, a mobile money app might have separate modules for user authentication, transaction logging, balance calculation, and SMS notifications.</p>
<p>The benefits of modularity are enormous. If the ZRA changes tax rules, you update only the tax-calculation module without touching the rest of the app. If a developer wants to replace the SMS service with WhatsApp notifications, they change only the notification module. Modularity is like organising a kitchen: pots go in one cupboard, plates in another, and spices on a specific shelf. When you need something, you know exactly where to look.</p>

<h2>Abstraction</h2>
<p><strong>Abstraction</strong> means hiding complex details behind a simple interface. When you press the brake pedal in a car, you do not need to understand hydraulics, friction coefficients, or brake-fluid pressure. The pedal is an abstraction: it hides complexity and gives you a simple action to perform.</p>
<p>In software, abstraction means that a module's internal workings are hidden from other modules. A payment module might expose a simple function called <code>processPayment(amount, phoneNumber)</code>. Other parts of the system call this function without knowing whether the module talks to Airtel Money, MTN MoMo, or a bank API. If the payment provider changes, only the payment module needs updating.</p>

<h2>Separation of Concerns</h2>
<p><strong>Separation of concerns</strong> is the principle that each part of a system should address one specific concern. In a web application, the three classic concerns are:</p>
<ul>
<li><strong>Presentation</strong> — what the user sees (buttons, colours, layouts).</li>
<li><strong>Business logic</strong> — the rules and calculations (commission rates, eligibility checks).</li>
<li><strong>Data storage</strong> — how information is saved and retrieved (database tables, file storage).</li>
</ul>
<p>When these concerns are mixed, a small change to the colour of a button might accidentally break a tax calculation. When they are separated, different developers can work on different layers without stepping on each other's toes.</p>

<h2>Worked Example: Designing a School Fees App</h2>
<p>A private school in Lusaka wants an app where parents pay termly fees via mobile money and receive digital receipts. Here is how design principles apply:</p>
<ul>
<li><strong>Modularity</strong> — The system has modules for: parent login, fee calculation, payment gateway integration, receipt generation, and admin reporting.</li>
<li><strong>Abstraction</strong> — The payment gateway module exposes a single <code>initiatePayment</code> method. The rest of the system does not care whether it uses Lenco, Airtel Money, or bank transfer.</li>
<li><strong>Separation of concerns</strong> — The receipt PDF is generated by a dedicated module. The fee rules are managed by a different module. The screen layout is handled by the frontend module.</li>
</ul>
<p>If the school later decides to accept Zamtel Kwacha, only the payment gateway module changes. If they redesign the receipt, only the receipt module changes. The rest of the system remains untouched.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Think of a simple app for a Zambian business or community. List at least four modules it would need, and write one sentence describing each module's job.</li>
<li>For one of those modules, describe the abstraction it would provide. What simple function or interface would it expose to the rest of the system?</li>
<li>Draw a simple diagram showing three layers: presentation, business logic, and data storage. Write one example of what belongs in each layer for your chosen app.</li>
<li>Find an app on your phone. Identify one place where you see abstraction in action — a simple button or action that hides complex technology behind it.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Modularity</strong> — dividing a system into independent modules, each with a specific responsibility.</li>
<li><strong>Abstraction</strong> — hiding complex implementation details behind a simple, easy-to-use interface.</li>
<li><strong>Separation of concerns</strong> — organising software so that each section addresses a distinct aspect of the problem.</li>
<li><strong>Interface</strong> — the boundary where two modules meet; it defines what one module offers and another can use.</li>
<li><strong>Spaghetti code</strong> — software with tangled, unstructured dependencies that make it hard to understand or modify.</li>
</ul>

<h2>Summary</h2>
<p>Software design is not optional decoration; it is the foundation of a maintainable system. Modularity keeps components independent, abstraction hides unnecessary complexity, and separation of concerns prevents changes in one area from breaking another. These principles are especially valuable in Zambia, where projects often have small teams, limited budgets, and evolving requirements. A well-designed system can adapt; a poorly designed one collapses under the first change.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.freecodecamp.org/news/software-design-principles/">freeCodeCamp — Software Design Principles</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn">MDN Web Docs — Learn Web Development (Architecture Section)</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-science/algorithms">Khan Academy — Algorithms and Design</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.2 Drawing Flowcharts to Map Logic',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to draw standard flowchart symbols, map simple and multi-step processes using flowcharts, and translate a real-world procedure into a visual diagram that a developer can turn into code.</p>

<h2>What Is a Flowchart?</h2>
<p>A flowchart is a diagram that shows the steps of a process in order, using symbols connected by arrows. It is one of the oldest and most reliable tools in software engineering because it forces you to think clearly about what happens first, what happens next, and what decisions must be made along the way. Before you write a single line of code, a flowchart helps you spot missing steps, dead ends, and logical errors.</p>
<p>In Zambia, flowcharts are not just for software engineers. A ZRA officer might draw a flowchart to show taxpayers how to file a return. A nurse might map the steps for registering a patient. But for software developers, flowcharts are essential because they bridge human thinking and machine logic.</p>

<h2>Common Flowchart Symbols</h2>
<ul>
<li><strong>Oval</strong> — Start or end of the process.</li>
<li><strong>Rectangle</strong> — A process step or action.</li>
<li><strong>Diamond</strong> — A decision point with yes/no or true/false branches.</li>
<li><strong>Parallelogram</strong> — Input or output (for example, reading data or displaying a result).</li>
<li><strong>Arrow</strong> — Shows the direction of flow from one step to the next.</li>
</ul>

<h2>Building a Simple Flowchart</h2>
<p>Let us map the process of buying ZESCO tokens using Airtel Money:</p>
<ol>
<li><strong>Start</strong> (oval)</li>
<li><strong>Open Airtel Money app</strong> (rectangle)</li>
<li><strong>Enter meter number</strong> (parallelogram)</li>
<li><strong>Is the meter number valid?</strong> (diamond)
<ul>
<li>No → <strong>Show error message</strong> (rectangle) → Go back to step 3.</li>
<li>Yes → Continue.</li>
</ul>
</li>
<li><strong>Enter amount</strong> (parallelogram)</li>
<li><strong>Is amount ≥ K10?</strong> (diamond)
<ul>
<li>No → <strong>Show minimum amount error</strong> (rectangle) → Go back to step 5.</li>
<li>Yes → Continue.</li>
</ul>
</li>
<li><strong>Confirm payment with PIN</strong> (rectangle)</li>
<li><strong>Display token number</strong> (parallelogram)</li>
<li><strong>End</strong> (oval)</li>
</ol>
<p>This flowchart shows two decision points that a developer must handle: invalid meter numbers and amounts that are too small. Without drawing this first, a programmer might forget to validate the meter number and waste hours debugging later.</p>

<h2>Nested Decisions and Loops</h2>
<p>Real processes are rarely straight lines. A flowchart for a mobile money agent app might include a loop: "While there are pending transactions, process the next one." A loan approval system might have nested decisions: "Is the applicant over eighteen? If yes, is their monthly income above K2,000? If yes, do they have an existing default?"</p>
<p>When drawing nested decisions, keep the chart readable. Use clear labels on every arrow. If a flowchart becomes too large, break it into smaller sub-flowcharts. One main chart shows the big picture; separate charts show the details of complex modules.</p>

<h2>Worked Example: School Exam Grading Flowchart</h2>
<p>A teacher wants software that assigns letter grades based on percentage marks. The flowchart logic is:</p>
<ol>
<li>Start.</li>
<li>Input percentage mark.</li>
<li>Is mark ≥ 80? Yes → Grade A. No → Continue.</li>
<li>Is mark ≥ 70? Yes → Grade B. No → Continue.</li>
<li>Is mark ≥ 60? Yes → Grade C. No → Continue.</li>
<li>Is mark ≥ 50? Yes → Grade D. No → Grade F.</li>
<li>Output grade.</li>
<li>End.</li>
</ol>
<p>This sequence of diamonds is a common pattern called an <strong>if-else ladder</strong>. Drawing it as a flowchart before coding prevents mistakes such as checking for Grade C before Grade B, which would misclassify an 85 percent student as a C.</p>

<h2>Try It Yourself</h2>
<ol>
<li>On paper, draw a flowchart for making a cup of tea. Include at least one decision point (for example, "Is there water in the kettle?").</li>
<li>Draw a flowchart for withdrawing cash from an ATM. Include decisions for PIN validation, balance check, and receipt choice.</li>
<li>Choose a real process you use regularly (for example, registering for a mobile money account or checking NAPSA contributions online). Map it as a flowchart with at least six steps and two decision points.</li>
<li>Show your flowchart to a classmate. Ask them to trace through it with sample data. If they get stuck or confused, revise the diagram.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Flowchart</strong> — a visual diagram that uses symbols and arrows to represent the steps and decisions in a process.</li>
<li><strong>Decision point</strong> — a step where the flow branches based on a condition, usually represented by a diamond.</li>
<li><strong>Process step</strong> — an action or operation, usually represented by a rectangle.</li>
<li><strong>If-else ladder</strong> — a series of decision points that check conditions in sequence.</li>
<li><strong>Loop</strong> — a structure where a set of steps repeats until a condition is met.</li>
</ul>

<h2>Summary</h2>
<p>Flowcharts are a powerful design tool that help you think through a process before writing code. They reveal hidden decisions, prevent logical errors, and communicate your plan to teammates and stakeholders. Mastering the five basic symbols — oval, rectangle, diamond, parallelogram, and arrow — gives you a universal language for describing any procedure, from buying ZESCO tokens to grading school exams.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/agile/agile_flowcharts.asp">W3Schools — Flowcharts in Software Development</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-science/algorithms">Khan Academy — Algorithms and Flowcharts</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/intro-to-tech-stack/">Microsoft Learn — Technology Fundamentals</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.3 Writing Clear Pseudocode',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to write pseudocode that describes algorithms in plain English, translate flowcharts into pseudocode and vice versa, and explain why pseudocode is a valuable bridge between human ideas and actual programming languages.</p>

<h2>What Is Pseudocode?</h2>
<p>Pseudocode is a way of writing out the steps of an algorithm using plain language mixed with simple programming structures such as IF, THEN, ELSE, WHILE, and FOR. It is not a real programming language. You cannot run it on a computer. But that is exactly why it is useful: because it has no strict syntax rules, you can focus entirely on the logic of the solution.</p>
<p>Pseudocode sits halfway between a flowchart and real code. A flowchart is visual and great for seeing the big picture. Pseudocode is textual and great for working out detailed logic. Many experienced developers write pseudocode before they open their code editor, because it helps them catch mistakes while the cost of fixing them is still zero.</p>

<h2>Basic Pseudocode Structures</h2>
<p>Here are the most common structures you will use:</p>

<h3>Sequence</h3>
<p>Steps that happen one after another:</p>
<pre><code>START
    DISPLAY "Welcome to the ZESCO token app"
    INPUT meterNumber
    INPUT amount
    DISPLAY "Processing payment..."
    CALL buyToken(meterNumber, amount)
    DISPLAY "Token purchased successfully"
END
</code></pre>

<h3>Decision (IF-THEN-ELSE)</h3>
<pre><code>IF amount &lt; 10 THEN
    DISPLAY "Minimum purchase is K10"
ELSE
    PROCESS payment
    DISPLAY tokenNumber
END IF
</code></pre>

<h3>Loop (WHILE)</h3>
<pre><code>WHILE userWantsToContinue = true
    DISPLAY menu
    INPUT choice
    PROCESS choice
    ASK "Do you want to continue?"
    INPUT userWantsToContinue
END WHILE
</code></pre>

<h3>Loop (FOR)</h3>
<pre><code>FOR each student IN classList
    CALCULATE finalGrade
    DISPLAY student.name + ": " + finalGrade
END FOR
</code></pre>

<h2>Pseudocode Conventions</h2>
<p>Although pseudocode has no official standard, good pseudocode follows these habits:</p>
<ul>
<li>Use indentation to show which steps belong inside loops or decisions.</li>
<li>Use descriptive names such as <code>customerBalance</code> instead of <code>x</code>.</li>
<li>Keep one action per line.</li>
<li>Capitalise keywords such as IF, THEN, ELSE, WHILE, FOR, and END.</li>
<li>Do not worry about semicolons, brackets, or exact syntax.</li>
</ul>

<h2>Worked Example: Calculating Market Vendor Profit</h2>
<p>Mrs Banda wants to know her daily profit from selling tomatoes and dried fish. Here is the pseudocode:</p>
<pre><code>START
    SET totalIncome = 0
    SET totalExpense = 0

    FOR each sale IN todaysSales
        ADD sale.amount TO totalIncome
    END FOR

    FOR each expense IN todaysExpenses
        ADD expense.amount TO totalExpense
    END FOR

    SET profit = totalIncome - totalExpense

    IF profit &gt; 0 THEN
        DISPLAY "Today's profit: K" + profit
    ELSE IF profit = 0 THEN
        DISPLAY "You broke even today."
    ELSE
        DISPLAY "Today's loss: K" + ABS(profit)
    END IF
END
</code></pre>
<p>This pseudocode is clear enough that any programmer could translate it into Python, PHP, JavaScript, or another language. It also makes the logic easy to review with Mrs Banda before any code is written.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write pseudocode for a simple login process: ask for a username and password, check them against stored values, and display "Access granted" or "Access denied."</li>
<li>Convert the ZESCO token flowchart from Lesson 3.2 into pseudocode. Include the decision points for invalid meter numbers and minimum amounts.</li>
<li>Write pseudocode for a program that calculates the average of five exam marks and displays whether the student passed (average ≥ 50) or failed.</li>
<li>Swap pseudocode with a classmate. Try to trace through their logic with test data. Identify any steps that are unclear or missing.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Pseudocode</strong> — a plain-language description of an algorithm that uses programming-like structures without strict syntax.</li>
<li><strong>Algorithm</strong> — a step-by-step procedure for solving a problem or completing a task.</li>
<li><strong>Sequence</strong> — a set of steps that execute in order, one after another.</li>
<li><strong>Condition</strong> — a true or false statement that determines which branch of a decision is taken.</li>
<li><strong>Iteration</strong> — repeating a set of steps, also known as a loop.</li>
</ul>

<h2>Summary</h2>
<p>Pseudocode is an essential design tool that lets you work out algorithms in plain language before committing to a specific programming language. It is faster to write than real code, easier to change, and accessible to non-technical stakeholders. Combined with flowcharts, pseudocode gives you a complete toolkit for planning software logic that is correct, clear, and ready for implementation.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/computing/computer-science/algorithms">Khan Academy — Algorithms and Pseudocode</a></li>
<li><a href="https://www.w3schools.com/agile/agile_pseudocode.asp">W3Schools — Pseudocode Basics</a></li>
<li><a href="https://www.freecodecamp.org/news/what-is-pseudocode/">freeCodeCamp — What Is Pseudocode?</a></li>
</ul>
HTML,
            ],
        ];
    }

    // ============================================================
    // MODULE 4
    // ============================================================

    private function module4Lessons(): array
    {
        return [
            [
                'title' => '4.1 Why Every Developer Needs Version Control',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what version control is, describe common problems it solves, distinguish between local, centralised, and distributed version control systems, and articulate why Git has become the industry standard for software teams around the world.</p>

<h2>The Problem: Code Chaos</h2>
<p>Imagine you are building a website for a client in Lusaka. You write the home page and save it as <code>index.html</code>. The next day you add a contact form and save over the same file. On the third day the client says they prefer the old home page. You open <code>index.html</code> and stare at the contact form. The old version is gone. You have no backup, no history, and no way to recover your previous work.</p>
<p>Now imagine you are working with two classmates on a college project. You each have a copy of the code on your laptops. You edit the login page at the same time your teammate edits it. When you both email your versions to each other, you have three conflicting files and no idea which changes to keep. You spend an entire afternoon copying and pasting lines between versions, hoping you do not break anything.</p>
<p>These problems — lost work, conflicting changes, and fear of experimentation — are exactly what <strong>version control</strong> solves.</p>

<h2>What Is Version Control?</h2>
<p>Version control is a system that records changes to files over time so that you can recall specific versions later. It is like an unlimited "undo" button for your entire project. It also enables multiple people to work on the same files simultaneously without overwriting each other's work.</p>
<p>Think of it as the "Track Changes" feature in Microsoft Word or Google Docs, but far more powerful. Instead of tracking one document, version control tracks every file in a project. Instead of showing changes inline, it stores a complete history that you can browse, compare, and restore at any moment.</p>

<h2>Types of Version Control</h2>
<p>There are three main approaches:</p>
<ul>
<li><strong>Local version control</strong> — Keeps patch files on your own computer. Simple but limited to one person.</li>
<li><strong>Centralised version control</strong> — Uses a single central server that stores all versions. Everyone checks out files from this server. Examples include Subversion (SVN) and CVS. If the central server fails, history may be lost.</li>
<li><strong>Distributed version control</strong> — Every contributor has a complete copy of the entire project history on their own machine. Changes are shared between copies. <strong>Git</strong> is the most popular distributed system. If one computer fails, every other copy still has the full history.</li>
</ul>

<h2>Why Git?</h2>
<p>Git was created in 2005 by Linus Torvalds, the creator of Linux, to manage the Linux kernel's enormous codebase. It is fast, free, open-source, and designed for collaboration. Today, Git is used by almost every software company in the world, from Google and Facebook to start-ups in Lusaka and Nairobi.</p>
<p>Git solves real problems that Zambian developers face:</p>
<ul>
<li><strong>Load-shedding resilience</strong> — Because every developer has a full local copy, you can continue working during a power cut and sync later.</li>
<li><strong>Offline work</strong> — You can commit changes, browse history, and create branches without an internet connection.</li>
<li><strong>Experimentation</strong> — You can try new ideas in a separate branch. If the idea fails, you delete the branch. If it succeeds, you merge it back.</li>
<li><strong>Accountability</strong> — Every change is tagged with the author's name, date, and a message explaining what was done.</li>
</ul>

<h2>Worked Example: A Team Project Without Version Control</h2>
<p>Three Edutrack students are building a library management system for their final project. Without Git, they agree verbally to split the work: Alice builds the login page, Bwalya builds the book search, and Chanda builds the borrowing records. On presentation day, they discover:</p>
<ul>
<li>Alice saved her work on a flash drive that got corrupted.</li>
<li>Bwalya overwrote the shared <code>database.php</code> file with his own version, breaking Alice's login.</li>
<li>Chanda has two copies of her code on her laptop and cannot remember which is the final one.</li>
</ul>
<p>With Git, each student would commit their work daily to a shared repository. Conflicts would be detected and resolved immediately. Alice's lost flash drive would not matter because her code exists on Bwalya's machine and on the remote server. The team would present with confidence.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a folder on your computer called <code>version_control_practice</code>. Create a simple text file, save it, then make three different versions of the same file with different names such as <code>story_v1.txt</code>, <code>story_v2.txt</code>, and <code>story_v3.txt</code>. Reflect on how messy this becomes after only three versions.</li>
<li>Interview a local developer or search online for a story about a project that failed because of poor version control. Summarise what went wrong.</li>
<li>List five benefits of Git that are especially relevant for developers working in Zambia with unreliable electricity and internet.</li>
<li>Watch a short introductory video about Git on freeCodeCamp or another free platform. Write three sentences summarising what you learned.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Version control</strong> — a system that records changes to files over time and allows recovery of previous versions.</li>
<li><strong>Repository</strong> — a storage location for a project's files and their complete version history.</li>
<li><strong>Commit</strong> — a snapshot of changes saved to the repository with a descriptive message.</li>
<li><strong>Branch</strong> — an independent line of development that lets you experiment without affecting the main codebase.</li>
<li><strong>Distributed version control</strong> — a system where every user has a full copy of the repository, not just the latest files.</li>
</ul>

<h2>Summary</h2>
<p>Version control is not a luxury for large companies; it is essential for anyone who writes code, from solo learners to professional teams. Git, the most widely used distributed version control system, protects your work from loss, enables safe experimentation, and makes collaboration possible even under challenging conditions such as load-shedding and poor connectivity. Learning Git is one of the most important steps you can take toward becoming a professional software engineer.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.freecodecamp.org/news/learn-git-and-github-course/">freeCodeCamp — Git and GitHub for Beginners</a></li>
<li><a href="https://www.w3schools.com/git/">W3Schools — Git Tutorial</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/intro-to-git/">Microsoft Learn — Introduction to Git</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.2 Git Fundamentals: Commits, Branches, and Merging',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to initialise a Git repository, stage and commit changes with clear messages, create and switch between branches, merge branches back together, and resolve simple merge conflicts. You will practise these skills using real files on your computer.</p>

<h2>Setting Up Git</h2>
<p>Before you can use Git, you must install it. On Windows, download the installer from git-scm.com. On Linux, install it through your package manager. On macOS, Git usually comes pre-installed or can be installed via Homebrew. Once installed, open a terminal or command prompt and configure your identity:</p>
<pre><code>git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
</code></pre>
<p>This name and email will appear on every commit you make, so use a professional email address — especially if you plan to share your work with employers or clients.</p>

<h2>Your First Repository</h2>
<p>A <strong>repository</strong> (or "repo") is a folder that Git watches for changes. To create one, navigate to your project folder in the terminal and type:</p>
<pre><code>git init
</code></pre>
<p>This creates a hidden <code>.git</code> folder that stores the entire history of your project. You now have a local repository ready to track changes.</p>

<h2>The Basic Workflow</h2>
<p>Git tracks changes in a three-step workflow:</p>
<ol>
<li><strong>Edit</strong> — You modify files in your working directory as normal.</li>
<li><strong>Stage</strong> — You mark which changed files you want to include in the next snapshot. This is done with <code>git add filename</code> or <code>git add .</code> to stage all changes.</li>
<li><strong>Commit</strong> — You save the staged snapshot permanently with <code>git commit -m "Descriptive message"</code>.</li>
</ol>
<p>Think of staging as packing your suitcase: you decide what goes in before you zip it shut. The commit is the act of zipping the suitcase and labelling it.</p>

<h2>Writing Good Commit Messages</h2>
<p>A commit message should explain <em>why</em> a change was made, not just <em>what</em> changed. Compare these two messages:</p>
<ul>
<li>Bad: <code>"fixed stuff"</code></li>
<li>Good: <code>"Fix validation bug: meter numbers under 10 digits now rejected with clear error message"</code></li>
</ul>
<p>Good messages help your future self and your teammates understand the project's history. They are especially valuable when you return to a project after load-shedding disrupted your work for a week.</p>

<h2>Branches</h2>
<p>A <strong>branch</strong> is an independent line of development. By default, Git creates a branch called <code>main</code> (or <code>master</code> on older setups). You can create a new branch to experiment:</p>
<pre><code>git branch feature-login-page
git checkout feature-login-page
</code></pre>
<p>Or in one command:</p>
<pre><code>git checkout -b feature-login-page
</code></pre>
<p>Now you can edit files freely. If the experiment fails, switch back to <code>main</code> and delete the branch. If it succeeds, merge it back into <code>main</code>.</p>

<h2>Merging</h2>
<p>To bring changes from one branch into another, use <code>git merge</code>:</p>
<pre><code>git checkout main
git merge feature-login-page
</code></pre>
<p>Git will combine the histories. If the same line was edited in both branches, Git cannot decide which version to keep, and a <strong>merge conflict</strong> occurs. You must open the conflicting file, choose the correct version, mark the conflict as resolved with <code>git add</code>, and complete the merge with <code>git commit</code>.</p>

<h2>Worked Example: Building a Contact Form</h2>
<p>You are adding a contact form to a website. Here is your Git workflow:</p>
<ol>
<li>Create a branch: <code>git checkout -b contact-form</code></li>
<li>Edit <code>contact.html</code> and save.</li>
<li>Stage the change: <code>git add contact.html</code></li>
<li>Commit: <code>git commit -m "Add contact form with name, email, and message fields"</code></li>
<li>Test the form. It works.</li>
<li>Switch to main: <code>git checkout main</code></li>
<li>Merge: <code>git merge contact-form</code></li>
<li>Delete the branch: <code>git branch -d contact-form</code></li>
</ol>
<p>If a teammate edited <code>contact.html</code> on <code>main</code> while you were working, Git will alert you to a conflict. You resolve it by keeping both sets of changes where they do not overlap.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a new folder on your computer. Initialise a Git repository inside it.</li>
<li>Create a text file named <code>readme.txt</code> with one sentence describing a simple app idea. Stage and commit it with a clear message.</li>
<li>Create a branch called <code>feature-add-price</code>. Edit the file to add a second sentence about pricing in Kwacha. Commit the change.</li>
<li>Switch back to <code>main</code>. Notice that your second sentence is gone.</li>
<li>Merge the branch into <code>main</code>. Verify that the second sentence now appears.</li>
<li>Run <code>git log --oneline</code> and observe your commit history.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Repository</strong> — a directory tracked by Git, containing all files and their full history.</li>
<li><strong>Stage</strong> — to mark modified files for inclusion in the next commit.</li>
<li><strong>Commit</strong> — a permanent snapshot of staged changes, saved with a message and timestamp.</li>
<li><strong>Branch</strong> — a parallel version of the codebase used for isolated development.</li>
<li><strong>Merge conflict</strong> — an error that occurs when Git cannot automatically reconcile changes from two branches.</li>
</ul>

<h2>Summary</h2>
<p>Git's core workflow — edit, stage, commit — gives you a safety net for every change you make. Branches let you experiment without risk, and merging brings successful experiments back into the main project. By writing clear commit messages and resolving conflicts carefully, you build a project history that is readable, trustworthy, and collaborative. These fundamentals are the foundation of every professional development workflow.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/git/">W3Schools — Git Tutorial</a></li>
<li><a href="https://www.freecodecamp.org/news/learn-git-and-github-course/">freeCodeCamp — Git and GitHub Course</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/intro-to-git/">Microsoft Learn — Git Fundamentals</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.3 GitHub: Collaboration, Pull Requests, and Code Reviews',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create a GitHub account, push a local Git repository to GitHub, create and manage pull requests, understand the purpose of code reviews, and collaborate with others on a shared codebase using industry-standard practices.</p>

<h2>What Is GitHub?</h2>
<p>GitHub is a web-based platform that hosts Git repositories online. While Git is the tool that tracks changes on your computer, GitHub is the service that stores those repositories in the cloud, making them accessible to teammates, employers, and the open-source community. It adds collaboration features such as issue tracking, project boards, pull requests, and automated testing.</p>
<p>Think of Git as the engine and GitHub as the vehicle. You can use Git without GitHub, just as an engine can run without a car. But for most professional and team projects, GitHub provides the infrastructure that makes collaboration practical.</p>

<h2>Creating a Repository on GitHub</h2>
<p>To get started, create a free account at github.com. Once logged in, click the "+" icon and choose "New repository." Give it a name, add a short description, and decide whether it should be public (visible to everyone) or private (visible only to invited collaborators). For a portfolio or open-source project, public is usually best. For client work or proprietary code, choose private.</p>

<h2>Connecting Local and Remote</h2>
<p>After creating a repository on GitHub, you connect your local repository to it using a <strong>remote</strong>. The standard command is:</p>
<pre><code>git remote add origin https://github.com/yourusername/your-repo.git
git branch -M main
git push -u origin main
</code></pre>
<p>From then on, you can push your local commits to GitHub with <code>git push</code> and download teammates' changes with <code>git pull</code>. This synchronises everyone's work through a central online copy.</p>

<h2>Pull Requests</h2>
<p>A <strong>pull request</strong> (PR) is a proposal to merge changes from one branch into another. It is the standard way teams review code before it enters the main project. When you finish a feature on your branch, you push it to GitHub and open a pull request. Your teammates can see exactly what changed, leave comments, request modifications, and ultimately approve or reject the merge.</p>
<p>Pull requests are powerful because they:</p>
<ul>
<li>Prevent broken code from entering the main branch.</li>
<li>Spread knowledge across the team — everyone sees how problems are solved.</li>
<li>Create a permanent record of design decisions and code reviews.</li>
</ul>

<h2>Code Reviews</h2>
<p>A <strong>code review</strong> is the act of reading another developer's code and providing feedback. Good reviewers check for correctness, clarity, security, and consistency with project standards. They do not just look for bugs; they suggest better variable names, simpler algorithms, and missing tests.</p>
<p>In Zambia, where many developers work remotely or as freelancers, code reviews on GitHub replace the in-person conversations that happen in large office buildings. A junior developer in Chipata can submit a pull request and receive detailed feedback from a senior engineer in Lusaka within hours.</p>

<h2>Worked Example: Adding a Feature as a Team</h2>
<p>Alice and Bwalya are building an e-commerce site for a craft shop. Alice is tasked with adding a "Add to Cart" button. Her workflow:</p>
<ol>
<li>She pulls the latest <code>main</code> branch from GitHub: <code>git pull origin main</code></li>
<li>She creates a branch: <code>git checkout -b feature-add-to-cart</code></li>
<li>She writes and tests the code locally.</li>
<li>She commits: <code>git commit -m "Add Add to Cart button with quantity selector"</code></li>
<li>She pushes the branch to GitHub: <code>git push origin feature-add-to-cart</code></li>
<li>On GitHub, she opens a pull request from <code>feature-add-to-cart</code> to <code>main</code>.</li>
<li>Bwalya reviews the PR. He notices that the button does not show a loading state during slow networks. He leaves a comment.</li>
<li>Alice updates the code, commits the fix, and pushes again. The PR updates automatically.</li>
<li>Bwalya approves. Alice merges the PR into <code>main</code>.</li>
</ol>
<p>This process ensures that every change is reviewed, tested, and documented before it reaches the live website.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a free GitHub account if you do not already have one.</li>
<li>Create a new public repository named <code>learning-git</code>.</li>
<li>On your computer, create a folder, initialise Git, add a <code>README.md</code> file, and push it to GitHub.</li>
<li>Create a branch, make a change, push the branch, and open a pull request to merge it into <code>main</code>.</li>
<li>Explore three open-source repositories on GitHub (search for "Zambia" or any topic you like). Look at their pull requests and code reviews. Write two sentences about what you observed.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>GitHub</strong> — a web platform for hosting Git repositories and collaborating on code.</li>
<li><strong>Remote</strong> — a version of your repository hosted on the internet, typically on GitHub.</li>
<li><strong>Push</strong> — uploading your local commits to a remote repository.</li>
<li><strong>Pull</strong> — downloading commits from a remote repository to your local machine.</li>
<li><strong>Pull request</strong> — a request to merge changes from one branch into another, usually reviewed before approval.</li>
<li><strong>Code review</strong> — the process of examining and discussing code changes before they are merged.</li>
</ul>

<h2>Summary</h2>
<p>GitHub transforms Git from a personal tool into a team platform. By hosting repositories online, enabling pull requests, and supporting code reviews, GitHub makes it possible for developers anywhere — including across Zambia — to build software together professionally. Understanding how to push, pull, branch, and review code on GitHub is essential for anyone who wants to work in a modern software team or contribute to open-source projects.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.freecodecamp.org/news/learn-git-and-github-course/">freeCodeCamp — Git and GitHub for Beginners</a></li>
<li><a href="https://docs.github.com/en/get-started">GitHub Docs — Getting Started</a></li>
<li><a href="https://www.w3schools.com/git/git_remote_getstarted.asp">W3Schools — GitHub Remote Tutorial</a></li>
</ul>
HTML,
            ],
        ];
    }


    // ============================================================
    // MODULE 5
    // ============================================================

    private function module5Lessons(): array
    {
        return [
            [
                'title' => '5.1 Understanding Why Software Has Bugs',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what a software bug is, identify the most common causes of bugs in real projects, describe the difference between a syntax error and a logic error, and appreciate why testing is an essential part of software engineering rather than an afterthought.</p>

<h2>What Is a Bug?</h2>
<p>A <strong>bug</strong> is an error, flaw, or unintended behaviour in software that causes it to produce incorrect results or behave in unexpected ways. The term originated in the 1940s when a real moth was found trapped inside a mechanical computer relay. Today, bugs are far more common than insects in machines, and they cost the software industry billions of dollars every year.</p>
<p>In Zambia, a bug in a mobile money app could mean a customer is charged twice for one transaction. A bug in a hospital records system could mean a patient receives the wrong medication. A bug in a school grading program could mean a student fails who should have passed. Bugs are not just inconveniences; they can have serious real-world consequences.</p>

<h2>Why Do Bugs Happen?</h2>
<p>Software is written by humans, and humans make mistakes. Even the best developers introduce bugs. Understanding the common causes helps teams prevent them:</p>
<ul>
<li><strong>Misunderstood requirements</strong> — The developer built what they thought the user wanted, not what the user actually needed. A ZRA tax app that calculates monthly instead of quarterly tax has a requirements bug.</li>
<li><strong>Complexity</strong> — Large systems have so many interacting parts that no single person can hold all the logic in their head. A change in the login module accidentally breaks the password reset feature.</li>
<li><strong>Poor design</strong> — Rushed or sloppy architecture creates hidden dependencies. Fixing one bug creates two new ones.</li>
<li><strong>Time pressure</strong> — Deadlines force developers to skip reviews, cut corners, and avoid testing. The result is fragile code.</li>
<li><strong>Environmental differences</strong> — Software that works on the developer's fast laptop with fibre internet may fail on an old Android phone with 2G connectivity in a rural area.</li>
<li><strong>Human error</strong> — A typo in a variable name, a missing minus sign in a formula, or an off-by-one error in a loop. These are simple mistakes with serious effects.</li>
</ul>

<h2>Syntax Errors vs Logic Errors</h2>
<p>Not all bugs are the same. <strong>Syntax errors</strong> are mistakes in the grammar of a programming language. They are usually caught immediately by the compiler or interpreter. For example, forgetting a closing bracket or misspelling a keyword. Syntax errors are annoying but easy to fix because the computer tells you exactly where they are.</p>
<p><strong>Logic errors</strong> are far more dangerous. The code runs without crashing, but it produces the wrong result. Imagine a commission calculator for a mobile money agent. The code runs perfectly, but it calculates commission as one percent instead of one and a half percent. The computer does not complain. The agent loses money silently for months. Logic errors require careful testing and domain knowledge to catch.</p>

<h2>The Cost of Bugs</h2>
<p>Studies in software engineering show that the cost of fixing a bug increases dramatically the later it is discovered. A bug caught during requirements analysis costs almost nothing to fix — you simply rewrite a sentence. A bug caught during coding costs a few minutes. A bug caught during testing costs hours. A bug caught after deployment, when real users are affected, can cost days of emergency work, reputation damage, and lost revenue.</p>
<p>For a small Zambian business, a production bug might mean paying a developer overtime to fix a website during a holiday sale. For a government system, it might mean public embarrassment and questions in Parliament. The lesson is clear: invest in finding bugs early.</p>

<h2>Worked Example: The Off-by-One Bug</h2>
<p>A school attendance app marks students as present or absent. The developer writes a loop that processes students at indices 0 to 29 for a class of thirty pupils. But they write the loop condition as <code>i &lt; 29</code> instead of <code>i &lt; 30</code>. The last student, number thirty, is never recorded. The app shows ninety-seven percent attendance when it should show one hundred percent.</p>
<p>This is a classic <strong>off-by-one error</strong>, one of the most common logic bugs in programming. It would be caught easily by a test that checks every student is present when all are marked present. Without that test, the bug hides silently.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Think of a time you used software that behaved unexpectedly. Describe what you expected, what actually happened, and which category of bug cause (requirements, complexity, time pressure, etc.) you think was most likely.</li>
<li>Write a simple calculation on paper, such as "Calculate ten percent commission on K1,500." Then introduce a logic error deliberately — for example, divide by one hundred twice instead of once. Explain what the wrong answer would be and why a computer would not detect the mistake automatically.</li>
<li>Search online for a famous software bug story. Summarise what went wrong, what the consequences were, and at what stage the bug should have been caught.</li>
<li>List three habits a development team can adopt to reduce the number of bugs they ship to users.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Bug</strong> — an error or flaw in software that causes incorrect or unintended behaviour.</li>
<li><strong>Syntax error</strong> — a mistake in the grammatical rules of a programming language, usually caught by the compiler.</li>
<li><strong>Logic error</strong> — a mistake in the algorithm or reasoning of a program, causing wrong output despite correct syntax.</li>
<li><strong>Off-by-one error</strong> — a common logic bug where a loop or calculation processes one item too many or too few.</li>
<li><strong>Production</strong> — the live environment where real users interact with the software.</li>
</ul>

<h2>Summary</h2>
<p>Bugs are an unavoidable part of software development, but their impact can be minimised. Understanding why bugs happen — misunderstood requirements, complexity, time pressure, and human error — helps teams prevent them. Distinguishing syntax errors from logic errors reminds us that the most dangerous bugs are the ones the computer does not complain about. The key message is that testing must be built into every phase of development, not tacked on at the end.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.freecodecamp.org/news/types-of-software-bugs/">freeCodeCamp — Types of Software Bugs</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-science/algorithms">Khan Academy — Debugging and Problem Solving</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/JavaScript/First_steps/What_went_wrong">MDN Web Docs — What Went Wrong? Debugging</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.2 Manual and Automated Testing Basics',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe the main types of software testing, write simple manual test cases with steps and expected results, explain the purpose of unit testing and integration testing, and understand why automated testing saves time and improves quality in the long run.</p>

<h2>What Is Software Testing?</h2>
<p>Software testing is the process of evaluating a system to find out whether it behaves as expected and to identify defects before users do. Testing is not about proving that software is perfect; it is about finding problems so they can be fixed. A good tester thinks like a troublemaker: they try unexpected inputs, click buttons in unusual orders, and simulate worst-case conditions.</p>
<p>In Zambia, where mobile networks are unreliable and devices vary widely, testing must go beyond the developer's desk. An app that works on a college computer with fast Wi-Fi may crash on a three-year-old Android phone with limited storage. Testing in realistic conditions is essential.</p>

<h2>Manual Testing</h2>
<p><strong>Manual testing</strong> means a human follows a set of steps, observes the results, and compares them to what should happen. It is the most accessible form of testing because it requires no special tools — just patience and attention to detail.</p>
<p>A <strong>test case</strong> is a documented set of steps with expected results. For example:</p>
<table>
<tr><th>Test Case ID</th><th>TC-001</th></tr>
<tr><th>Title</th><td>Valid mobile money deposit</td></tr>
<tr><th>Preconditions</th><td>Agent is logged in with float balance of K5,000.</td></tr>
<tr><th>Steps</th><td>1. Select "Deposit Cash". 2. Enter customer phone: 097XXXXXXX. 3. Enter amount: K200. 4. Confirm with PIN. 5. Tap "Submit".</td></tr>
<tr><th>Expected Result</th><td>Transaction succeeds. Customer receives SMS. Agent float drops to K4,800.</td></tr>
</table>
<p>Manual testing is valuable for finding usability problems, verifying visual layouts, and exploring edge cases that automated tests miss. However, it is slow and repetitive. Running the same fifty test cases before every release is exhausting and error-prone.</p>

<h2>Automated Testing</h2>
<p><strong>Automated testing</strong> means writing code that tests your code. Once written, automated tests run in seconds, can be executed hundreds of times, and never forget a step. They are especially valuable for regression testing: verifying that new changes have not broken existing features.</p>
<p>The most common types of automated tests are:</p>
<ul>
<li><strong>Unit tests</strong> — Test individual functions or methods in isolation. For example, a unit test for a tax calculator verifies that <code>calculateTax(1500)</code> returns exactly <code>225.00</code> every time.</li>
<li><strong>Integration tests</strong> — Test how multiple modules work together. For example, verifying that the login module correctly passes user data to the permissions module.</li>
<li><strong>End-to-end tests</strong> — Test the entire application from the user's perspective. For example, a script that opens a browser, fills out a registration form, and checks that a confirmation email arrives.</li>
</ul>

<h2>Testing Pyramid</h2>
<p>Software engineers often describe testing as a pyramid. At the base are many fast, cheap unit tests. In the middle are fewer integration tests. At the top are a small number of slow, expensive end-to-end tests. A healthy project has a solid base of unit tests, because catching bugs at the unit level is fastest and cheapest.</p>

<h2>Worked Example: Testing a Discount Calculator</h2>
<p>A shop wants software that applies a ten percent discount to orders over K500. Here are test cases:</p>
<ul>
<li><strong>Unit test 1:</strong> Input K600 → Expect K540 (ten percent discount applied).</li>
<li><strong>Unit test 2:</strong> Input K500 → Expect K500 (exactly at threshold, no discount).</li>
<li><strong>Unit test 3:</strong> Input K499 → Expect K499 (below threshold, no discount).</li>
<li><strong>Unit test 4:</strong> Input K0 → Expect K0 (edge case, no crash).</li>
<li><strong>Unit test 5:</strong> Input -K100 → Expect error (invalid input handled gracefully).</li>
</ul>
<p>Writing these tests before coding the calculator ensures that the developer thinks about edge cases. It also creates a safety net: if someone later changes the discount threshold to K1,000, the tests will immediately reveal which cases break.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a simple feature from an app you use (for example, "buying ZESCO tokens" or "sending money via MoMo"). Write three manual test cases with steps and expected results.</li>
<li>For each test case, identify whether it is a positive test (normal use) or a negative test (error handling).</li>
<li>Research one free automated testing tool (for example, PHPUnit for PHP, Jest for JavaScript, or pytest for Python). Write two sentences about what it does and when you would use it.</li>
<li>Discuss with a classmate: why is it impossible to test every possible input? What strategies do testers use to choose the most important cases?</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Manual testing</strong> — testing performed by a human following documented steps and observing results.</li>
<li><strong>Automated testing</strong> — testing performed by scripts or programs that execute test cases automatically.</li>
<li><strong>Unit test</strong> — a test that verifies the behaviour of a single, isolated function or component.</li>
<li><strong>Integration test</strong> — a test that verifies that multiple components work correctly together.</li>
<li><strong>Regression testing</strong> — re-running tests after changes to ensure that existing functionality still works.</li>
</ul>

<h2>Summary</h2>
<p>Testing is not a chore to rush through before release; it is a core engineering practice that protects users and saves money. Manual testing finds usability and visual problems that machines miss. Automated testing provides speed, consistency, and a safety net for refactoring. A balanced approach — many unit tests, some integration tests, and a few end-to-end tests — gives projects the best chance of delivering reliable software that works under real Zambian conditions.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.freecodecamp.org/news/software-testing-beginners-guide/">freeCodeCamp — Software Testing Beginner's Guide</a></li>
<li><a href="https://www.w3schools.com/software-testing/">W3Schools — Software Testing Tutorial</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/test-with-playwright/">Microsoft Learn — Automated Testing with Playwright</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.3 Deploying a Simple Web Application',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what deployment means, describe the difference between development, staging, and production environments, list common deployment methods for simple web applications, and identify basic security practices that must be in place before any app goes live.</p>

<h2>What Is Deployment?</h2>
<p>Deployment is the process of making software available for real users. When you write code on your laptop, you are in a <strong>development environment</strong>. Only you can see the app. Deployment moves that code to a server connected to the internet so that anyone with a browser or phone can access it. It is the moment a project transitions from "something I built" to "something people use."</p>
<p>For a Zambian developer, deployment might mean uploading files to a shared hosting account, publishing an Android app on the Google Play Store, or configuring a virtual private server in a data centre. The method depends on the project's size, budget, and technical requirements.</p>

<h2>Three Environments</h2>
<p>Professional teams maintain at least three separate environments:</p>
<ul>
<li><strong>Development</strong> — The developer's local machine. Code changes frequently. Bugs are expected. Data is often fake.</li>
<li><strong>Staging</strong> — A copy of production used for final testing. Clients review features here. Testers verify that everything works on realistic data before launch.</li>
<li><strong>Production</strong> — The live system that real users interact with. Changes are rare and carefully planned. Downtime costs money and reputation.</li>
</ul>
<p>Deploying directly from development to production is dangerous. A missing semicolon that crashed your local app could take down a live e-commerce site during a flash sale. Staging catches these mistakes before users see them.</p>

<h2>Deployment Methods</h2>
<p>For simple web applications, common deployment approaches include:</p>
<ul>
<li><strong>Shared hosting</strong> — Cheap and easy. You upload files via FTP or a control panel. Suitable for small portfolios and brochure sites. Limited control and scalability.</li>
<li><strong>Virtual Private Server (VPS)</strong> — You rent a virtual machine and configure it yourself. More control, more responsibility. Suitable for custom applications and APIs.</li>
<li><strong>Platform-as-a-Service (PaaS)</strong> — Providers such as Heroku, Render, or Railway handle servers and infrastructure for you. You push code via Git and the platform deploys automatically. Good for startups and rapid prototyping.</li>
<li><strong>Cloud providers</strong> — AWS, Google Cloud, and Azure offer powerful but complex services. Suitable for large-scale applications with global users.</li>
</ul>

<h2>Security Basics Before Deployment</h2>
<p>Deploying without security is like opening a shop without locks. At minimum, every live application should have:</p>
<ul>
<li><strong>HTTPS</strong> — Encrypts data between the user and the server. Essential for any site that handles passwords, payments, or personal information. Free certificates are available from Let's Encrypt.</li>
<li><strong>Strong passwords</strong> — Default admin passwords such as "admin123" are the first thing attackers try.</li>
<li><strong>Environment variables</strong> — Secrets such as database passwords and API keys must never be written directly in code. They belong in environment variables that the server reads at runtime.</li>
<li><strong>Input validation</strong> — Never trust user input. A contact form that accepts HTML could allow attackers to inject malicious scripts.</li>
<li><strong>Backups</strong> — Regular automatic backups of the database and files. If the server fails, you can restore within hours, not weeks.</li>
</ul>

<h2>Worked Example: Deploying a Portfolio Site</h2>
<p>Chisenga, a junior developer in Lusaka, has built a personal portfolio website using HTML, CSS, and a little PHP. Her deployment plan:</p>
<ol>
<li>She buys a domain name (chisenga.dev) and a small shared hosting plan from a local provider.</li>
<li>She uploads her files using the hosting control panel's file manager.</li>
<li>She creates a MySQL database for her contact form submissions.</li>
<li>She installs a free SSL certificate so the site uses HTTPS.</li>
<li>She tests every page on her phone using mobile data, not just Wi-Fi.</li>
<li>She sets up a weekly backup schedule through the hosting panel.</li>
<li>She updates her CV and LinkedIn profile with the live URL.</li>
</ol>
<p>This simple deployment is appropriate for a portfolio. If she later builds a client project with user accounts and payments, she will need a VPS or PaaS with more robust security and monitoring.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Research three hosting providers that serve Zambia or Africa. Compare their prices, features, and support options. Write a one-paragraph recommendation for a student building their first portfolio site.</li>
<li>List five differences between a development environment and a production environment. Explain why each difference matters.</li>
<li>Imagine you are deploying a small business inventory app. Write a deployment checklist with at least eight items covering security, backups, and testing.</li>
<li>Search for "OWASP Top Ten" and read the brief descriptions of the first three risks. Write one sentence about each, explaining how it could affect a Zambian web application.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Deployment</strong> — the process of releasing software to a live environment where users can access it.</li>
<li><strong>Production environment</strong> — the live server or platform that serves real users.</li>
<li><strong>Staging environment</strong> — a pre-production copy of the system used for final testing and client review.</li>
<li><strong>HTTPS</strong> — Hypertext Transfer Protocol Secure; encrypts data between a user's browser and the web server.</li>
<li><strong>Environment variable</strong> — a configuration value stored outside the codebase, used for secrets and settings that vary between environments.</li>
</ul>

<h2>Summary</h2>
<p>Deployment is the bridge between development and real-world use. Maintaining separate development, staging, and production environments prevents costly mistakes. Choosing the right deployment method — shared hosting, VPS, PaaS, or cloud — depends on the project's needs and budget. Above all, security must never be an afterthought: HTTPS, strong passwords, input validation, environment variables, and backups are non-negotiable minimums before any application goes live.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/Server-side">MDN Web Docs — Server-Side Website Programming</a></li>
<li><a href="https://www.freecodecamp.org/news/deployment-guide/">freeCodeCamp — Web Deployment Guide</a></li>
<li><a href="https://owasp.org/www-project-top-ten/">OWASP — Top Ten Web Application Security Risks</a></li>
</ul>
HTML,
            ],
        ];
    }

    // ============================================================
    // MODULE 6
    // ============================================================

    private function module6Lessons(): array
    {
        return [
            [
                'title' => '6.1 Agile vs Waterfall in Plain Language',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe the Waterfall and Agile approaches to software development, explain the key differences between them, identify situations where each approach is most appropriate, and describe the roles and ceremonies of the Scrum framework in language a non-technical client can understand.</p>

<h2>Two Ways to Build Software</h2>
<p>There are many ways to organise a software project, but two models dominate the conversation: <strong>Waterfall</strong> and <strong>Agile</strong>. Understanding both helps you choose the right approach for each project and communicate effectively with clients, managers, and teammates.</p>

<h2>Waterfall: Plan Everything First</h2>
<p>The Waterfall model treats software development like a factory assembly line. Each phase must be completed before the next begins. The sequence is:</p>
<ol>
<li>Requirements gathering</li>
<li>System design</li>
<li>Implementation (coding)</li>
<li>Testing</li>
<li>Deployment</li>
<li>Maintenance</li>
</ol>
<p>Waterfall works well when requirements are stable and well understood. A government agency replacing an old tax filing system with a new one might use Waterfall because the laws, forms, and workflows are already defined. A construction company commissioning a project management tool with fixed specifications might also prefer Waterfall.</p>
<p>The downside is rigidity. If the client changes their mind after design is complete, going back is expensive. In Zambia, where small businesses often discover new needs as they see the software take shape, Waterfall can feel like building a house with no option to move the walls.</p>

<h2>Agile: Build, Learn, Adapt</h2>
<p>Agile rejects the idea that you can plan everything perfectly at the start. Instead, teams build software in small increments called <strong>iterations</strong> or <strong>sprints</strong>, typically lasting one to four weeks. At the end of each sprint, the team delivers a working piece of software, gathers feedback, and adjusts the plan for the next sprint.</p>
<p>The Agile Manifesto, written in 2001, values:</p>
<ul>
<li>Individuals and interactions over processes and tools.</li>
<li>Working software over comprehensive documentation.</li>
<li>Customer collaboration over contract negotiation.</li>
<li>Responding to change over following a plan.</li>
</ul>
<p>Agile is popular in startups, product companies, and any environment where requirements evolve. A Zambian fintech building a new payment feature might use Agile because they need to respond quickly to competitor moves, regulator feedback, and user behaviour.</p>

<h2>Scrum: A Popular Agile Framework</h2>
<p><strong>Scrum</strong> is the most widely used Agile framework. It defines specific roles, events, and artifacts:</p>
<ul>
<li><strong>Product Owner</strong> — Represents the users and business. Maintains the product backlog and decides what gets built next.</li>
<li><strong>Scrum Master</strong> — Facilitates the process, removes obstacles, and ensures the team follows Scrum practices.</li>
<li><strong>Development Team</strong> — The people who design, code, test, and deliver the software.</li>
</ul>
<p>Scrum events include:</p>
<ul>
<li><strong>Sprint Planning</strong> — The team chooses which backlog items to complete in the upcoming sprint.</li>
<li><strong>Daily Stand-up</strong> — A fifteen-minute meeting where each person says what they did yesterday, what they will do today, and what is blocking them.</li>
<li><strong>Sprint Review</strong> — The team demonstrates working software to stakeholders.</li>
<li><strong>Sprint Retrospective</strong> — The team reflects on what went well, what did not, and how to improve next time.</li>
</ul>

<h2>Which Should You Choose?</h2>
<p>Neither Waterfall nor Agile is always right. The choice depends on the project:</p>
<table>
<tr><th>Choose Waterfall when...</th><th>Choose Agile when...</th></tr>
<tr><td>Requirements are fixed and well understood.</td><td>Requirements are likely to change.</td></tr>
<tr><td>The client wants a fixed price and fixed deadline.</td><td>The client wants to see progress early and often.</td></tr>
<tr><td>The project is small and simple.</td><td>The project is complex or long-term.</td></tr>
<tr><td>Regulations demand extensive documentation upfront.</td><td>Speed to market matters more than perfect documentation.</td></tr>
<tr><td>The team has little experience with iterative methods.</td><td>The team is comfortable with regular feedback and adaptation.</td></tr>
</table>

<h2>Worked Example: A Mobile Money Feature</h2>
<p>A Zambian mobile network wants to add a "send money to multiple recipients" feature. If they use Waterfall, they would spend three months planning, designing, and coding before anyone sees the feature. By then, competitors may have launched something better, and user feedback arrives too late to influence the design.</p>
<p>If they use Agile with two-week sprints, the first sprint might deliver a basic "send to two people" prototype. Stakeholders test it immediately. The second sprint adds validation and error handling based on feedback. The third sprint adds a confirmation screen. Each sprint produces working, testable software. The final product is shaped by real user input, not guesswork.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Describe a software project you know about in Zambia. Decide whether Waterfall or Agile would be more suitable and explain your reasoning with at least three arguments.</li>
<li>Imagine you are explaining Scrum to a shop owner who has never worked in tech. Write a one-page explanation using only everyday language. Avoid jargon.</li>
<li>List three risks of using Agile on a project with a very strict, non-negotiable deadline. How could a team mitigate each risk?</li>
<li>Research one real company that uses Agile and one that uses Waterfall. Write two sentences about each, describing why their chosen approach fits their business.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Waterfall</strong> — a linear project management approach where each phase is completed before the next begins.</li>
<li><strong>Agile</strong> — an iterative approach to software development that delivers working software in small increments and adapts to feedback.</li>
<li><strong>Scrum</strong> — a popular Agile framework with defined roles, events, and artifacts.</li>
<li><strong>Sprint</strong> — a fixed time period (usually one to four weeks) during which a Scrum team completes a set of backlog items.</li>
<li><strong>Product backlog</strong> — an ordered list of features, fixes, and tasks that the team plans to work on.</li>
</ul>

<h2>Summary</h2>
<p>Waterfall and Agile represent two different philosophies of project management. Waterfall plans everything upfront and executes in sequence; Agile builds in small cycles and adapts continuously. Scrum provides a concrete framework for practicing Agile with clear roles and regular ceremonies. The best software engineers know when to use each approach and can explain the trade-offs to clients and stakeholders in plain language.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.freecodecamp.org/news/agile-software-development-guide/">freeCodeCamp — Agile Software Development Guide</a></li>
<li><a href="https://www.w3schools.com/agile/">W3Schools — Agile Development Tutorial</a></li>
<li><a href="https://learn.microsoft.com/en-us/devops/plan/what-is-agile">Microsoft Learn — What Is Agile?</a></li>
</ul>
HTML,
            ],
            [
                'title' => '6.2 Working in a Development Team',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe the typical roles in a software development team, explain why documentation and communication are as important as coding skills, practise writing clear technical documentation, and identify behaviours that build trust and productivity in diverse teams.</p>

<h2>It Is Never a Solo Sport</h2>
<p>Hollywood portrays programmers as lone geniuses typing furiously in dark rooms. In reality, professional software is built by teams. A small startup might have five people; a large bank might have five hundred. Even freelance developers communicate constantly with clients, designers, and other contractors. Learning to work well in a team is as important as learning to write clean code.</p>
<p>In Zambia, software teams are often small and multi-skilled. A single developer might write backend code, design the database, and deploy the server. Even in these lean teams, communication matters. The client in Lusaka needs updates. The designer in Kitwe needs specifications. The junior intern in Kalomo needs mentorship. Team skills make all of this possible.</p>

<h2>Common Team Roles</h2>
<p>As teams grow, specialisation emerges:</p>
<ul>
<li><strong>Software Developer / Engineer</strong> — Writes the code. May specialise in frontend (what users see), backend (servers and databases), or full-stack (both).</li>
<li><strong>Quality Assurance (QA) Engineer</strong> — Tests the software, writes test cases, and verifies that requirements are met.</li>
<li><strong>Product Manager / Product Owner</strong> — Decides what to build and why. Represents the user and the business.</li>
<li><strong>UX/UI Designer</strong> — Designs how the software looks and feels. Creates wireframes, mock-ups, and user flows.</li>
<li><strong>DevOps Engineer</strong> — Manages servers, deployment pipelines, and infrastructure.</li>
<li><strong>Technical Writer</strong> — Creates documentation for users and developers.</li>
</ul>
<p>Not every team has all these roles. A three-person startup might combine them into "developer," "designer," and "founder." But understanding the responsibilities helps everyone contribute more effectively.</p>

<h2>Documentation: The Unsung Hero</h2>
<p>Documentation is written information about the software: how to install it, how to use it, how the code is organised, and how to change it. Good documentation saves hours of confusion. Bad documentation — or no documentation — forces new team members to reverse-engineer the codebase, wasting days or weeks.</p>
<p>Essential documents in a software project include:</p>
<ul>
<li><strong>README</strong> — A file at the root of the project explaining what the software does, how to install it, and how to run it.</li>
<li><strong>API documentation</strong> — Describes how other systems can communicate with yours. Lists endpoints, parameters, and expected responses.</li>
<li><strong>Code comments</strong> — Short explanations inside the code itself, clarifying why a particular approach was chosen.</li>
<li><strong>User manual</strong> — Instructions for end users, often with screenshots and step-by-step guides.</li>
<li><strong>Architecture decision records (ADRs)</strong> — Documents explaining why major technical choices were made.</li>
</ul>

<h2>Communication Practices</h2>
<p>Great teams communicate early and often. Here are practices that work across cultures and time zones:</p>
<ul>
<li><strong>Write clear commit messages</strong> — Every commit should explain why the change was made, not just what changed.</li>
<li><strong>Update tickets promptly</strong> — If your team uses an issue tracker, keep statuses current so everyone knows what is blocked or in progress.</li>
<li><strong>Ask questions early</strong> — If a requirement is unclear, ask before you build the wrong thing. It is cheaper to clarify than to rebuild.</li>
<li><strong>Share knowledge</strong> — When you learn something useful, document it or present it to the team. This prevents bus factor disasters.</li>
<li><strong>Be respectful in code reviews</strong> — Critique the code, not the person. Suggest improvements rather than declaring mistakes.</li>
</ul>

<h2>Worked Example: Handing Over a Project</h2>
<p>Mr Phiri has been the sole developer on a church donation tracking app for two years. He is leaving for a new job. His handover package includes:</p>
<ol>
<li>A README explaining how to set up the development environment on Windows and Linux.</li>
<li>A list of all external services used (SMS gateway, payment provider, hosting account) with contact details and login recovery instructions.</li>
<li>Architecture diagrams showing how the frontend, backend, and database connect.</li>
<li>A walkthrough video of the codebase, explaining where key features live.</li>
<li>Contact information for the church treasurer, who serves as the product owner.</li>
</ol>
<p>Because of this documentation, the new developer starts contributing within days instead of weeks.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write a README for a hypothetical project. Include: project name, description, installation steps, usage example, and contact information.</li>
<li>Imagine you are reviewing a teammate's code. Their function is correct but uses unclear variable names. Write a polite comment suggesting improvements.</li>
<li>List three ways a remote team in Zambia can stay coordinated without meeting in person every day.</li>
<li>Research the term "bus factor." Write two sentences explaining what it means and how documentation reduces it.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Full-stack developer</strong> — a developer who works on both frontend and backend parts of an application.</li>
<li><strong>DevOps</strong> — practices that combine software development and IT operations to shorten the development lifecycle.</li>
<li><strong>Bus factor</strong> — the number of people who, if hit by a bus, would put a project in jeopardy due to lost knowledge.</li>
<li><strong>README</strong> — a text file that introduces and explains a software project.</li>
<li><strong>API documentation</strong> — instructions describing how to interact with a software application's programming interface.</li>
</ul>

<h2>Summary</h2>
<p>Software development is a team activity that depends on clear roles, thorough documentation, and respectful communication. Whether you work in a five-person startup or a multinational company, your ability to explain your work, ask good questions, and support your colleagues determines your success as much as your technical skills. Invest time in writing READMEs, updating tickets, and sharing knowledge — these habits separate junior developers from senior engineers.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.freecodecamp.org/news/how-to-write-a-good-readme-file/">freeCodeCamp — How to Write a Good README</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/intro-to-tech-communication/">Microsoft Learn — Technical Communication</a></li>
<li><a href="https://grow.google/intl/en_uk/">Grow with Google — Collaboration and Professional Skills</a></li>
</ul>
HTML,
            ],
            [
                'title' => '6.3 Software Engineering Careers and Freelancing from Zambia',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe multiple career paths available to software engineers in Zambia, explain how to build a credible portfolio and online presence, outline the practical steps for finding freelance work on platforms such as Upwork, and identify local opportunities for software developers beyond traditional employment.</p>

<h2>The Growing Opportunity</h2>
<p>Software engineering is one of the most portable careers in the world. Unlike medicine or law, which require local licences, code works the same in Lusaka as in London. A well-written Python script or a well-designed website delivers value regardless of where the developer sits. For Zambians, this means access to global clients, remote jobs, and competitive salaries — provided you build the right skills and present yourself professionally.</p>
<p>Within Zambia, the demand for software developers is growing. Banks need mobile apps. NGOs need data management systems. Government agencies need digital services. Schools need learning platforms. Churches need member databases. Every organisation that uses a computer eventually needs custom software, and they need people who can build it reliably.</p>

<h2>Career Paths</h2>
<p>Software engineering offers several directions:</p>
<ul>
<li><strong>Full-time employment</strong> — Working for a company as a salaried developer. Stable income, benefits, and mentorship. Common in banks, telecoms, and tech companies.</li>
<li><strong>Freelancing</strong> — Working for multiple clients on a project basis. Higher income potential but irregular cash flow. Requires self-discipline and business skills.</li>
<li><strong>Contracting</strong> — Similar to freelancing but usually through an agency or for fixed terms (for example, six months). Common for large government or NGO projects.</li>
<li><strong>Entrepreneurship</strong> — Building your own product or software agency. Highest risk and highest reward. Requires technical skill, business sense, and persistence.</li>
<li><strong>Remote work for international companies</strong> — Earning dollars or euros while living in Zambia. Requires strong English, proven experience, and the ability to work across time zones.</li>
</ul>

<h2>Building Your Portfolio</h2>
<p>Whether you seek employment or freelance work, your portfolio is your most powerful credential. A portfolio is a collection of real projects that demonstrate what you can build. It is more convincing than any certificate because it proves you can deliver.</p>
<p>A strong portfolio includes:</p>
<ul>
<li><strong>Three to five real projects</strong> — Not tutorial copies. Projects you built to solve a real problem. Even a simple stock tracker for your uncle's shop counts if it works.</li>
<li><strong>Source code on GitHub</strong> — Clean, well-commented code with a professional README. Employers and clients will look at your commits and coding style.</li>
<li><strong>Live demos</strong> — Deployed versions that people can click and use. A working link is worth more than a screenshot.</li>
<li><strong>Case studies</strong> — A short write-up for each project explaining the problem, your solution, technologies used, and what you learned.</li>
</ul>

<h2>Freelancing Platforms</h2>
<p>Platforms such as Upwork, Fiverr, and Toptal connect freelancers with clients worldwide. Getting started is challenging because you have no reviews, but persistence pays off:</p>
<ol>
<li><strong>Complete your profile</strong> — Use a professional photo, write a clear headline, and describe your skills specifically (for example, "PHP and Laravel developer with e-commerce experience" rather than "web developer").</li>
<li><strong>Start small</strong> — Bid on small, fixed-price jobs to build reviews. A K150 job that earns a five-star review is worth more than a rejected bid on a K15,000 project.</li>
<li><strong>Write personalised proposals</strong> — Mention the client's project by name. Show that you read the brief. Propose a specific approach.</li>
<li><strong>Deliver excellence</strong> — Meet deadlines, communicate clearly, and fix bugs promptly. Reviews are everything on these platforms.</li>
<li><strong>Withdraw wisely</strong> — Use mobile money or bank transfer options. Factor platform fees and exchange rates into your pricing.</li>
</ol>

<h2>Local Opportunities</h2>
<p>Do not overlook the local market. Many Zambian businesses need software but do not know where to find developers. Attend tech meetups in Lusaka, join online communities such as the Zambia ICT forum, and tell everyone what you do. Word of mouth is powerful. A single satisfied client can lead to five more through referrals.</p>
<p>Consider specialising in a niche. Instead of being a general "web developer," become the person who builds school management systems, or church databases, or agricultural tracking apps. Specialisation makes marketing easier and allows you to charge premium rates.</p>

<h2>Worked Example: From Learner to Earner</h2>
<p>Grace completes this Software Engineering course at Edutrack. She builds three projects: a portfolio website, a simple inventory tracker for her mother's shop, and a clone of a popular news site. She deploys all three, writes case studies, and uploads the code to GitHub.</p>
<p>She creates an Upwork profile and bids on five small jobs per week. After two months, she lands her first client: a small NGO in Botswana that needs a donation receipt system. She delivers on time, earns a five-star review, and raises her rates. Within a year, she has regular clients from Zambia, Botswana, and the United Kingdom. She works from a co-working space in Lusaka and earns more than many traditional graduates.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Draft your ideal LinkedIn or Upwork headline. It should be specific, mention technologies, and indicate who you help.</li>
<li>List three real problems in your community that you could solve with software. For each one, write one paragraph describing the project and the technologies you would use.</li>
<li>Research one Zambian tech company or startup. Write two sentences about what they do and what skills they likely need.</li>
<li>Create a simple one-page personal portfolio website. Include your name, a photo, a short bio, links to your GitHub and LinkedIn, and contact information.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Portfolio</strong> — a curated collection of projects and case studies that demonstrates your skills to employers and clients.</li>
<li><strong>Freelancing</strong> — working for multiple clients on a project or hourly basis, rather than as a permanent employee.</li>
<li><strong>Remote work</strong> — employment where the developer works from home or a local office while serving a company based elsewhere.</li>
<li><strong>Time zone</strong> — the local time of a geographic region; important when collaborating with international teams.</li>
<li><strong>Niche</strong> — a specialised area of expertise that distinguishes you from generalist competitors.</li>
</ul>

<h2>Summary</h2>
<p>Software engineering offers Zambian learners a genuine path to local and global opportunity. Whether you choose employment, freelancing, contracting, or entrepreneurship, your success depends on building real projects, maintaining a professional portfolio, communicating clearly, and delivering reliable work. Start small, specialise over time, and treat every project — even unpaid ones — as a stepping stone toward your next opportunity. The demand for skilled software engineers is growing, and with discipline and persistence, you can build a rewarding career from anywhere in Zambia.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.freecodecamp.org/news/how-to-build-a-developer-portfolio/">freeCodeCamp — How to Build a Developer Portfolio</a></li>
<li><a href="https://grow.google/intl/en_uk/">Grow with Google — Career Certificates and Digital Skills</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn">MDN Web Docs — Learn Web Development</a></li>
<li><a href="https://www.linkedin.com/learning/">LinkedIn Learning — Career Development (free trial available)</a></li>
</ul>
HTML,
            ],
        ];
    }


    // ============================================================
    // QUIZZES
    // ============================================================

    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: Foundations of Software Engineering',
            'description' => 'Test your understanding of software engineering principles, the SDLC, and the mobile money agent case study.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following best describes the difference between coding and software engineering?',
                    'explanation' => 'Coding is the act of writing instructions, while software engineering is the disciplined process of designing, building, testing, and maintaining software that serves real users reliably.',
                    'options' => [
                        ['text' => 'Coding is faster than software engineering.', 'is_correct' => false],
                        ['text' => 'Software engineering applies engineering principles to the entire lifecycle of software, not just writing code.', 'is_correct' => true],
                        ['text' => 'Software engineering does not involve writing code.', 'is_correct' => false],
                        ['text' => 'Coding is only done by senior developers.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In the SDLC, which phase comes immediately after requirements analysis?',
                    'explanation' => 'After gathering and documenting requirements, the next phase is system design, where architects plan how the software will be structured.',
                    'options' => [
                        ['text' => 'Deployment', 'is_correct' => false],
                        ['text' => 'System Design', 'is_correct' => true],
                        ['text' => 'Testing', 'is_correct' => false],
                        ['text' => 'Maintenance', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which quality means that software can be understood and modified by other developers months or years later?',
                    'explanation' => 'Maintainability refers to how easily software can be read, understood, and changed by developers other than the original author.',
                    'options' => [
                        ['text' => 'Reliability', 'is_correct' => false],
                        ['text' => 'Scalability', 'is_correct' => false],
                        ['text' => 'Maintainability', 'is_correct' => true],
                        ['text' => 'Usability', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In the Chisenga Technologies case study, why was an offline-first architecture chosen for the agent app?',
                    'explanation' => 'The design team learned that agents work in areas with unreliable internet and expensive mobile data, so the app stores transactions locally and syncs when connectivity returns.',
                    'options' => [
                        ['text' => 'Because offline-first apps are always cheaper to build.', 'is_correct' => false],
                        ['text' => 'Because agents work in areas with unreliable internet and need to record transactions without connectivity.', 'is_correct' => true],
                        ['text' => 'Because the client requested no internet access at all.', 'is_correct' => false],
                        ['text' => 'Because offline apps are easier to test.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which SDLC phase involves releasing software to real users?',
                    'explanation' => 'Deployment is the phase where the software is made available to users, whether through app stores, web servers, or physical installation.',
                    'options' => [
                        ['text' => 'Testing', 'is_correct' => false],
                        ['text' => 'Implementation', 'is_correct' => false],
                        ['text' => 'Deployment', 'is_correct' => true],
                        ['text' => 'Maintenance', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'The Software Development Life Cycle includes maintenance as a final phase because software needs ongoing updates and fixes after launch.',
                    'explanation' => 'Maintenance is indeed a core SDLC phase. Software requires bug fixes, security patches, and feature updates throughout its lifetime.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Skipping the planning phase of the SDLC usually saves time and produces better software.',
                    'explanation' => 'Skipping planning often leads to building the wrong thing, causing expensive rework, missed deadlines, and unhappy users.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for the amount of cash or electronic balance available to a mobile money agent? (one word)',
                    'explanation' => 'Float is the term for the money an agent has available to serve customer deposits and withdrawals.',
                    'correct_answer' => 'Float',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a non-functional requirement?',
                    'explanation' => 'Response time is a quality or constraint the system must satisfy, making it a non-functional requirement. Adding a login feature is a functional requirement.',
                    'options' => [
                        ['text' => 'The system shall allow users to log in.', 'is_correct' => false],
                        ['text' => 'The system shall store customer names.', 'is_correct' => false],
                        ['text' => 'The system shall respond to user actions within two seconds on a 2G network.', 'is_correct' => true],
                        ['text' => 'The system shall send email receipts.', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Gathering Requirements from Real Users',
            'description' => 'Test your knowledge of user interviews, user stories, acceptance criteria, and requirements specifications.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main goal of requirements elicitation?',
                    'explanation' => 'Requirements elicitation is the structured process of discovering and documenting what users actually need from a software system.',
                    'options' => [
                        ['text' => 'To write code as quickly as possible.', 'is_correct' => false],
                        ['text' => 'To discover and document what users actually need.', 'is_correct' => true],
                        ['text' => 'To create a detailed project budget.', 'is_correct' => false],
                        ['text' => 'To design the database schema.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which type of question is best for uncovering detailed information during a user interview?',
                    'explanation' => 'Open-ended questions beginning with "how," "what," or "why" encourage interviewees to share rich, detailed responses rather than simple yes/no answers.',
                    'options' => [
                        ['text' => 'Leading questions that suggest an answer.', 'is_correct' => false],
                        ['text' => 'Closed questions that require yes or no answers.', 'is_correct' => false],
                        ['text' => 'Open-ended questions that begin with how, what, or why.', 'is_correct' => true],
                        ['text' => 'Technical questions about programming languages.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In the standard user story format, which component describes the reason the user wants the feature?',
                    'explanation' => 'The "so that" clause explains the benefit or motivation behind the user story, keeping the team focused on value.',
                    'options' => [
                        ['text' => 'As a [type of user]', 'is_correct' => false],
                        ['text' => 'I want [some goal]', 'is_correct' => false],
                        ['text' => 'So that [some reason]', 'is_correct' => true],
                        ['text' => 'Given [some context]', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What do acceptance criteria define?',
                    'explanation' => 'Acceptance criteria are the specific, testable conditions that must be met for a user story to be considered complete and accepted.',
                    'options' => [
                        ['text' => 'The programming language to be used.', 'is_correct' => false],
                        ['text' => 'The testable conditions that must be met for a story to be complete.', 'is_correct' => true],
                        ['text' => 'The salary of the development team.', 'is_correct' => false],
                        ['text' => 'The colour scheme of the application.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which document formally defines what a software system must do and serves as a contract between client and developer?',
                    'explanation' => 'The Software Requirements Specification (SRS) is the formal document that captures all functional and non-functional requirements.',
                    'options' => [
                        ['text' => 'Git commit log', 'is_correct' => false],
                        ['text' => 'Software Requirements Specification', 'is_correct' => true],
                        ['text' => 'Marketing brochure', 'is_correct' => false],
                        ['text' => 'Employee contract', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A functional requirement describes what the system must do, while a non-functional requirement describes how well it must do it.',
                    'explanation' => 'This is correct. Functional requirements specify actions and behaviours, while non-functional requirements specify qualities such as speed, security, and usability.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'User stories are most useful when they are long, detailed documents that describe every possible scenario.',
                    'explanation' => 'User stories are intentionally short and simple. Detailed scenarios are captured in acceptance criteria, not in the story itself.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What does the acronym SRS stand for in software engineering? (three words)',
                    'explanation' => 'SRS stands for Software Requirements Specification, the document that formally defines system requirements.',
                    'correct_answer' => 'Software Requirements Specification',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which INVEST principle states that a user story should be small enough to complete within one or two weeks?',
                    'explanation' => 'The Small principle ensures that stories fit within a single sprint or iteration, making them easier to estimate and deliver.',
                    'options' => [
                        ['text' => 'Independent', 'is_correct' => false],
                        ['text' => 'Valuable', 'is_correct' => false],
                        ['text' => 'Small', 'is_correct' => true],
                        ['text' => 'Testable', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Designing Before You Code',
            'description' => 'Test your understanding of software design principles, flowcharts, and pseudocode.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which design principle means hiding complex implementation details behind a simple interface?',
                    'explanation' => 'Abstraction hides internal complexity and exposes only what is necessary, making systems easier to use and maintain.',
                    'options' => [
                        ['text' => 'Modularity', 'is_correct' => false],
                        ['text' => 'Abstraction', 'is_correct' => true],
                        ['text' => 'Separation of concerns', 'is_correct' => false],
                        ['text' => 'Encapsulation', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In a flowchart, which symbol represents a decision point?',
                    'explanation' => 'The diamond shape is the standard symbol for a decision point where the flow branches based on a condition.',
                    'options' => [
                        ['text' => 'Oval', 'is_correct' => false],
                        ['text' => 'Rectangle', 'is_correct' => false],
                        ['text' => 'Diamond', 'is_correct' => true],
                        ['text' => 'Parallelogram', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is an example of separation of concerns?',
                    'explanation' => 'Keeping the user interface separate from the business logic is a classic example of separation of concerns.',
                    'options' => [
                        ['text' => 'Writing all code in a single file for speed.', 'is_correct' => false],
                        ['text' => 'Mixing database queries with HTML display code.', 'is_correct' => false],
                        ['text' => 'Keeping the user interface code separate from the business logic code.', 'is_correct' => true],
                        ['text' => 'Using only one programming language for everything.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the primary purpose of pseudocode?',
                    'explanation' => 'Pseudocode describes algorithms in plain language with simple programming structures, allowing developers to focus on logic before coding.',
                    'options' => [
                        ['text' => 'To replace actual programming languages entirely.', 'is_correct' => false],
                        ['text' => 'To describe algorithms in plain language so logic can be planned before coding.', 'is_correct' => true],
                        ['text' => 'To compile and run programs on any operating system.', 'is_correct' => false],
                        ['text' => 'To create visual diagrams of software architecture.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which flowchart symbol is used for the start or end of a process?',
                    'explanation' => 'The oval (or terminator) symbol marks the beginning and end of a flowchart.',
                    'options' => [
                        ['text' => 'Rectangle', 'is_correct' => false],
                        ['text' => 'Diamond', 'is_correct' => false],
                        ['text' => 'Parallelogram', 'is_correct' => false],
                        ['text' => 'Oval', 'is_correct' => true],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Modularity means breaking a system into separate, self-contained parts called modules.',
                    'explanation' => 'This is the definition of modularity. Each module has a specific responsibility and communicates through well-defined interfaces.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Pseudocode has strict syntax rules that must be followed exactly like a real programming language.',
                    'explanation' => 'Pseudocode intentionally has no strict syntax. It is meant to express logic clearly without worrying about language-specific rules.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What type of error occurs when a loop processes one item too many or too few? (two words)',
                    'explanation' => 'An off-by-one error is a common logic bug where a loop or array index is off by a single element.',
                    'correct_answer' => 'Off-by-one',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following best explains why design should come before coding?',
                    'explanation' => 'Design provides a plan that prevents costly mistakes, unclear structure, and unmaintainable code that often results from coding without planning.',
                    'options' => [
                        ['text' => 'Design is required by law in most countries.', 'is_correct' => false],
                        ['text' => 'Coding without design is faster but produces better results.', 'is_correct' => false],
                        ['text' => 'Design provides a plan that prevents costly mistakes and unmaintainable code.', 'is_correct' => true],
                        ['text' => 'Design is only needed for large government projects.', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module4Quiz(): array
    {
        return [
            'title' => 'Module 4 Quiz: Version Control with Git and GitHub',
            'description' => 'Test your knowledge of Git fundamentals, branching, merging, and GitHub collaboration.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the primary purpose of version control?',
                    'explanation' => 'Version control records changes to files over time, enabling recovery of previous versions and collaboration among multiple developers.',
                    'options' => [
                        ['text' => 'To make code run faster.', 'is_correct' => false],
                        ['text' => 'To record changes to files over time and enable collaboration.', 'is_correct' => true],
                        ['text' => 'To design user interfaces.', 'is_correct' => false],
                        ['text' => 'To translate code between programming languages.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which Git command saves a snapshot of staged changes permanently with a descriptive message?',
                    'explanation' => 'The git commit command creates a permanent snapshot of the staged changes in the repository history.',
                    'options' => [
                        ['text' => 'git push', 'is_correct' => false],
                        ['text' => 'git add', 'is_correct' => false],
                        ['text' => 'git commit', 'is_correct' => true],
                        ['text' => 'git pull', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is a branch in Git?',
                    'explanation' => 'A branch is an independent line of development that allows experimentation without affecting the main codebase.',
                    'options' => [
                        ['text' => 'A command that deletes the repository.', 'is_correct' => false],
                        ['text' => 'An independent line of development for safe experimentation.', 'is_correct' => true],
                        ['text' => 'A tool for compressing files.', 'is_correct' => false],
                        ['text' => 'A type of database table.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What happens when Git cannot automatically reconcile changes from two branches?',
                    'explanation' => 'A merge conflict occurs when the same line has been edited differently in both branches, requiring manual resolution.',
                    'options' => [
                        ['text' => 'Git automatically deletes one branch.', 'is_correct' => false],
                        ['text' => 'Git creates a backup copy of the repository.', 'is_correct' => false],
                        ['text' => 'A merge conflict occurs and must be resolved manually.', 'is_correct' => true],
                        ['text' => 'Git pushes both versions to the remote server.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the purpose of a pull request on GitHub?',
                    'explanation' => 'A pull request is a proposal to merge changes from one branch into another, allowing team review before integration.',
                    'options' => [
                        ['text' => 'To delete a repository permanently.', 'is_correct' => false],
                        ['text' => 'To request permission to download Git.', 'is_correct' => false],
                        ['text' => 'To propose merging changes and allow team review before integration.', 'is_correct' => true],
                        ['text' => 'To automatically fix bugs in the code.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'In a distributed version control system like Git, every developer has a complete copy of the entire project history on their own machine.',
                    'explanation' => 'This is a defining feature of distributed version control. Each clone contains the full repository history, not just the latest files.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'The git push command downloads commits from a remote repository to your local machine.',
                    'explanation' => 'git push uploads local commits to a remote repository. git pull is the command that downloads remote commits to your local machine.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for a code review practice where teammates examine each other\'s changes before merging? (two words)',
                    'explanation' => 'Code review is the practice of reading and providing feedback on another developer\'s code before it is merged into the main project.',
                    'correct_answer' => 'Code review',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which command stages all modified files in the current directory for the next commit?',
                    'explanation' => 'git add . stages all changes in the current directory and its subdirectories, preparing them for commit.',
                    'options' => [
                        ['text' => 'git commit -m "update"', 'is_correct' => false],
                        ['text' => 'git push origin main', 'is_correct' => false],
                        ['text' => 'git add .', 'is_correct' => true],
                        ['text' => 'git merge feature', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module5Quiz(): array
    {
        return [
            'title' => 'Module 5 Quiz: Testing and Deploying Software',
            'description' => 'Test your understanding of software bugs, testing types, deployment environments, and security basics.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is a logic error?',
                    'explanation' => 'A logic error occurs when code runs without crashing but produces incorrect results due to a mistake in the algorithm or reasoning.',
                    'options' => [
                        ['text' => 'A mistake in the grammar of a programming language.', 'is_correct' => false],
                        ['text' => 'An error that causes the program to crash immediately.', 'is_correct' => false],
                        ['text' => 'A mistake in reasoning that produces incorrect output despite correct syntax.', 'is_correct' => true],
                        ['text' => 'A hardware failure in the computer.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which type of test verifies the behaviour of a single, isolated function?',
                    'explanation' => 'Unit tests verify individual functions or methods in isolation, ensuring each small component works correctly.',
                    'options' => [
                        ['text' => 'Integration test', 'is_correct' => false],
                        ['text' => 'End-to-end test', 'is_correct' => false],
                        ['text' => 'Unit test', 'is_correct' => true],
                        ['text' => 'Manual test', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which environment is a pre-production copy used for final testing before software goes live?',
                    'explanation' => 'The staging environment mirrors production and is used for final testing and client review before deployment to live users.',
                    'options' => [
                        ['text' => 'Development environment', 'is_correct' => false],
                        ['text' => 'Production environment', 'is_correct' => false],
                        ['text' => 'Staging environment', 'is_correct' => true],
                        ['text' => 'Local environment', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is HTTPS important for a live web application?',
                    'explanation' => 'HTTPS encrypts data between the user and the server, protecting sensitive information such as passwords and payment details from interception.',
                    'options' => [
                        ['text' => 'It makes the website load faster.', 'is_correct' => false],
                        ['text' => 'It encrypts data between the user and the server.', 'is_correct' => true],
                        ['text' => 'It prevents users from bookmarking the site.', 'is_correct' => false],
                        ['text' => 'It automatically fixes bugs in the code.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is regression testing?',
                    'explanation' => 'Regression testing means re-running existing tests after code changes to ensure that previously working functionality has not been broken.',
                    'options' => [
                        ['text' => 'Testing only new features.', 'is_correct' => false],
                        ['text' => 'Re-running tests after changes to ensure existing functionality still works.', 'is_correct' => true],
                        ['text' => 'Testing software on old operating systems.', 'is_correct' => false],
                        ['text' => 'Testing done exclusively by the client.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A syntax error is usually caught immediately by the compiler or interpreter and is generally easy to fix.',
                    'explanation' => 'Syntax errors violate the grammatical rules of a programming language, so the development environment flags them immediately with clear messages.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Deploying directly from a developer\'s laptop to production is a safe and recommended practice.',
                    'explanation' => 'Deploying directly from development to production is dangerous because untested code can crash the live system and affect real users.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What type of test verifies that multiple modules work correctly together? (two words)',
                    'explanation' => 'Integration tests verify that multiple components or modules interact correctly when combined.',
                    'correct_answer' => 'Integration test',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following should NEVER be written directly in source code?',
                    'explanation' => 'Secrets such as database passwords and API keys should be stored in environment variables, not hard-coded in source code where they could be exposed.',
                    'options' => [
                        ['text' => 'Function names', 'is_correct' => false],
                        ['text' => 'Database passwords and API keys', 'is_correct' => true],
                        ['text' => 'HTML tags', 'is_correct' => false],
                        ['text' => 'Variable declarations', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module6Quiz(): array
    {
        return [
            'title' => 'Module 6 Quiz: Teams, Methodologies, and Your Career',
            'description' => 'Test your understanding of Agile and Waterfall, Scrum practices, teamwork, and software engineering career paths.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In the Waterfall model, which characteristic best describes its approach to phases?',
                    'explanation' => 'Waterfall is a linear, sequential approach where each phase must be completed before the next one begins.',
                    'options' => [
                        ['text' => 'Phases overlap and repeat continuously.', 'is_correct' => false],
                        ['text' => 'Each phase must be completed before the next begins.', 'is_correct' => true],
                        ['text' => 'There are no defined phases.', 'is_correct' => false],
                        ['text' => 'Coding happens before requirements are gathered.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is a sprint in Scrum?',
                    'explanation' => 'A sprint is a fixed time period, usually one to four weeks, during which a Scrum team completes a set of planned backlog items.',
                    'options' => [
                        ['text' => 'A meeting to fire underperforming team members.', 'is_correct' => false],
                        ['text' => 'A race between developers to finish code.', 'is_correct' => false],
                        ['text' => 'A fixed time period during which the team completes planned work.', 'is_correct' => true],
                        ['text' => 'A document that lists all project risks.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which Agile value states that working software is more important than comprehensive documentation?',
                    'explanation' => 'The Agile Manifesto values working software over comprehensive documentation, though it does not reject documentation entirely.',
                    'options' => [
                        ['text' => 'Individuals and interactions over processes and tools.', 'is_correct' => false],
                        ['text' => 'Working software over comprehensive documentation.', 'is_correct' => true],
                        ['text' => 'Customer collaboration over contract negotiation.', 'is_correct' => false],
                        ['text' => 'Responding to change over following a plan.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the role of the Scrum Master?',
                    'explanation' => 'The Scrum Master facilitates the Scrum process, removes obstacles for the team, and ensures Scrum practices are followed.',
                    'options' => [
                        ['text' => 'To write all the code for the team.', 'is_correct' => false],
                        ['text' => 'To set salaries and hire new developers.', 'is_correct' => false],
                        ['text' => 'To facilitate the process, remove obstacles, and ensure Scrum practices are followed.', 'is_correct' => true],
                        ['text' => 'To decide what features the product will include.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is the best reason to maintain thorough documentation in a software project?',
                    'explanation' => 'Documentation helps new team members understand the system, reduces bus factor risk, and preserves knowledge when people leave.',
                    'options' => [
                        ['text' => 'It replaces the need for any testing.', 'is_correct' => false],
                        ['text' => 'It allows the team to skip the design phase.', 'is_correct' => false],
                        ['text' => 'It helps new team members understand the system and preserves knowledge when people leave.', 'is_correct' => true],
                        ['text' => 'It guarantees the software will have no bugs.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Agile is always the best choice for every software project, regardless of context.',
                    'explanation' => 'Neither Agile nor Waterfall is universally best. The choice depends on project size, stability of requirements, client preferences, and team experience.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A code review should critique the code, not the person who wrote it.',
                    'explanation' => 'Effective code reviews focus on improving the code while maintaining respect and psychological safety for the author.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What term describes the number of people who, if unavailable, would put a project in jeopardy due to lost knowledge? (two words)',
                    'explanation' => 'Bus factor measures the concentration of critical knowledge in a team. High bus factor risk means only a few people know how key parts work.',
                    'correct_answer' => 'Bus factor',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which career path involves working for multiple clients on a project basis rather than as a permanent employee?',
                    'explanation' => 'Freelancing means offering services to multiple clients on a project or hourly basis, providing flexibility but irregular income.',
                    'options' => [
                        ['text' => 'Full-time employment', 'is_correct' => false],
                        ['text' => 'Freelancing', 'is_correct' => true],
                        ['text' => 'Remote work', 'is_correct' => false],
                        ['text' => 'Open-source contribution', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    // ============================================================
    // ASSIGNMENTS
    // ============================================================

    private function createAssignments(): void
    {
        Assignment::create([
            'course_id' => $this->courseId,
            'lesson_id' => null,
            'title' => 'Requirements and Design for a Local Business App',
            'description' => 'Apply the skills from Modules 1–3 to gather requirements, write user stories, and create design artifacts for a real or hypothetical Zambian business.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Choose a small business or organisation in Zambia (for example, a market stall in Soweto Market, a poultry farm near Kalomo, a church, or a tailoring shop). If you do not have a real client, invent a realistic scenario.

Step 2: Conduct a short requirements interview. If you have a real contact, ask them these questions (or similar): What is the hardest part of your daily work? What records do you keep? What problems cost you time or money? If you are inventing a scenario, write detailed, realistic answers.

Step 3: Write three user stories for the software you would build, using the standard format: "As a [user], I want [goal] so that [reason]."

Step 4: For each user story, write three to four acceptance criteria that make it testable.

Step 5: Draw a flowchart for one key process (for example, recording a sale, registering a member, or checking stock). You may draw on paper and photograph it, or use a free digital tool.

Step 6: Write pseudocode for one calculation or decision in your flowchart.

Step 7: Compile everything into a single PDF document. Name it "Requirements_and_Design.pdf". Upload the PDF here.
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
            'title' => 'Build, Test, and Deploy a Simple Portfolio Website',
            'description' => 'Demonstrate your Git, testing, deployment, and career-development skills by creating and publishing a simple personal portfolio website.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Create a simple personal portfolio website with at least three pages: Home (with your name and a short bio), Projects (with at least two projects described), and Contact (with a form or your email address).

Step 2: Use HTML and CSS. You may use a simple CSS framework such as Bootstrap, or write your own styles.

Step 3: Initialise a Git repository for your project. Make at least five commits with clear, descriptive messages that explain what each commit adds or changes.

Step 4: Create a branch called "feature-contact-page", add the contact page on that branch, and merge it back into your main branch.

Step 5: Push your repository to GitHub (public or private). Ensure the README includes a project description and instructions for viewing the site.

Step 6: Deploy the site using any free hosting service (for example, GitHub Pages, Netlify, or Vercel). Include the live URL in your README.

Step 7: Write three manual test cases for your contact form or navigation menu. Include steps and expected results.

Step 8: Take a screenshot of your Git commit history and your deployed site. Include both screenshots in a PDF named "Portfolio_Submission.pdf". Also provide the GitHub repository URL and the live site URL in the submission text. Upload the PDF here.
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
        $modulesCount = Module::where('course_id', $this->courseId)->count();
        $lessonsCount = Lesson::whereIn('module_id', Module::where('course_id', $this->courseId)->pluck('id'))->count();
        $quizzesCount = Quiz::where('course_id', $this->courseId)->count();
        $questionsCount = Question::whereIn('question_id', function ($query) {
            $query->select('question_id')->from('quiz_questions')
                ->whereIn('quiz_id', Quiz::where('course_id', $this->courseId)->pluck('id'));
        })->count();
        $assignmentsCount = Assignment::where('course_id', $this->courseId)->count();

        $this->command->info('Certificate in Software Engineering content seeded successfully.');
        $this->command->info("Modules: {$modulesCount} | Lessons: {$lessonsCount} | Quizzes: {$quizzesCount} | Questions: {$questionsCount} | Assignments: {$assignmentsCount}");
    }
}
