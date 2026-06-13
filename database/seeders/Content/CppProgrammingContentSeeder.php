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

class CppProgrammingContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in C++ Programming')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in C++ Programming" not found. Aborting.');

            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in C++ Programming already has modules. Skipping content seed.');

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
                'title' => 'Module 1: Welcome to C++ — Tools and Your First Program',
                'description' => 'Discover why C++ still matters, set up Code::Blocks or VS Code with the g++ compiler, and write, compile, and run your first C++ program.',
            ],
            [
                'title' => 'Module 2: Variables, Types, and Kwacha Arithmetic',
                'description' => 'Learn C++ variables, data types, operators, type conversion, constants, and how to calculate shop bills and change in Kwacha.',
            ],
            [
                'title' => 'Module 3: Control Flow — Decisions and Loops',
                'description' => 'Use if-else, switch, for, while, and do-while to build a ZESCO tariff calculator and automate repeated tasks.',
            ],
            [
                'title' => 'Module 4: Functions, Arrays, and Strings',
                'description' => 'Write reusable functions, work with arrays and C++ strings, and build a market stall price lookup program.',
            ],
            [
                'title' => 'Module 5: Pointers and Object-Oriented Programming',
                'description' => 'Understand pointers without fear, then create classes, objects, constructors, and inheritance to model a school register.',
            ],
            [
                'title' => 'Module 6: File I/O, a Console Project, and Paths Forward',
                'description' => 'Read and write files, build a market sales tracker console project, practise debugging habits, and explore where C++ can take you next.',
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
                'title' => '1.1 Why C++ Still Matters for Zambian Learners',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what C++ is, describe where it is used in the real world, and identify at least three practical opportunities that C++ skills can create for school leavers, marketeers, civil servants, and small business owners in Zambia.</p>

<h2>What Is C++?</h2>
<p>C++ is a powerful programming language created in the 1980s as an extension of the C language. It adds features for organising code around objects and is often described as a "middle-level" language because it gives programmers close control over computer hardware while also providing modern tools for building large, structured programs. C++ is used to write operating systems, game engines, web browsers, banking systems, embedded software for cars and machines, and even parts of mobile phone networks.</p>
<p>Unlike some languages that hide details from the programmer, C++ teaches you how the computer actually works. This makes it an excellent foundation if you want to become a software engineer, an electronics hobbyist, or simply someone who can think about problems with the precision that programming demands.</p>

<h2>Why Bother with C++ in Zambia?</h2>
<p>Many people ask whether C++ is still worth learning when newer languages seem easier. The answer is yes, for several practical reasons that matter right here in Zambia:</p>
<ul>
<li><strong>C++ is the backbone of systems and games.</strong> Popular game engines, high-frequency trading platforms, and large parts of Windows, Linux, and macOS are written in C++. If you dream of working in game development or systems programming anywhere in the world, C++ is a direct route.</li>
<li><strong>Embedded devices and Arduino use C++.</strong> Chicken-rearing temperature monitors, irrigation sensors, solar charge controllers, and smart security systems are often programmed in C or C++. A small business owner who can modify Arduino code can automate tasks without paying expensive consultants.</li>
<li><strong>Competitive programming opens scholarships and jobs.</strong> International programming contests such as the International Olympiad in Informatics and many coding interviews use C++ because of its speed. Strong C++ skills can lead to scholarships, remote work, or internships.</li>
<li><strong>Learning C++ makes other languages easier.</strong> Java, C#, Python, and JavaScript all borrow ideas from C++. Once you understand C++, picking up these other languages becomes faster.</li>
<li><strong>Local opportunities are growing.</strong> Banks, mining companies, telecoms, and government systems in Lusaka and across Southern Africa need developers who understand performance-critical code and can maintain older C++ systems.</li>
</ul>

<h2>Worked Example: From Market Records to a Faster System</h2>
<p>Consider Mr Banda, who runs a small shop in Kalomo. He currently writes every sale in a notebook. At the end of the month he spends an entire Saturday adding up totals. After learning C++, he writes a simple console program that records each sale, calculates the running total, and prints a monthly report in seconds. The program runs on an old laptop at the college and later on a cheap Raspberry Pi at the shop. Understanding C++ gave him the confidence to build something that fits his exact business, rather than waiting for an off-the-shelf app that may not match his needs.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Use your phone or a college computer to search for "C++ programming jobs Africa" or "embedded C++ jobs Zambia." Read two job adverts and note the skills they require.</li>
<li>List three devices or services you use that probably contain C++ code, such as a web browser, a video game, or an ATM.</li>
<li>Ask someone in a bank, shop, or government office whether their organisation uses any older computer systems. Older systems are often written in C or C++.</li>
<li>Write two sentences in your notebook titled "Why I am learning C++" explaining your personal goal.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Programming language</strong> — a set of rules and vocabulary used to write instructions that a computer can follow.</li>
<li><strong>Compiler</strong> — a program that translates human-readable source code into machine instructions the computer can execute.</li>
<li><strong>Embedded system</strong> — a small computer built into another device, such as a prepaid meter, car, or sensor.</li>
<li><strong>Syntax</strong> — the grammar rules of a programming language, such as where to place semicolons and braces.</li>
<li><strong>Source code</strong> — the text a programmer writes before it is compiled into a runnable program.</li>
</ul>

<h2>Summary</h2>
<p>C++ is a fast, flexible language that remains important for games, systems, embedded devices, and competitive programming. It is an excellent skill for Zambian learners because it opens local job opportunities, supports entrepreneurship through hardware projects, and makes every other programming language easier to learn. This course will teach you C++ step by step, using examples from everyday Zambian life.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/cpp/">W3Schools — C++ Tutorial</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Free Coding Curriculum</a></li>
<li><a href="https://learn.microsoft.com/en-us/cpp/cpp/">Microsoft Learn — C++ Documentation</a></li>
<li><a href="https://www.arduino.cc/en/Guide/HomePage">Arduino — Getting Started</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.2 Setting Up Code::Blocks or VS Code with g++',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to install a free C++ compiler and code editor on a Windows computer, configure the editor so it can build and run C++ programs, and confirm that everything works by compiling a tiny test program.</p>

<h2>What You Need</h2>
<p>You do not need an expensive computer to learn C++. A modest Windows laptop or desktop with at least 4 GB of RAM, 5 GB of free disk space, and occasional internet access is enough for beginners. If you use a college computer, ask permission before installing software. The two most common free setups for beginners are <strong>Code::Blocks with MinGW</strong> and <strong>Visual Studio Code with g++</strong>. Code::Blocks is simpler because everything comes in one installer. VS Code is more modern and works well if you are comfortable installing a compiler separately.</p>

<h2>Option A: Installing Code::Blocks with MinGW</h2>
<p>Code::Blocks is an Integrated Development Environment, or IDE. An IDE combines a text editor, compiler, and debugger in one window. The version that includes MinGW also installs the g++ compiler automatically.</p>
<ol>
<li>Open your web browser and visit <a href="https://www.codeblocks.org/downloads/">codeblocks.org/downloads</a>.</li>
<li>Download the file whose name ends with <code>mingw-setup.exe</code>. This is the version that includes the compiler. Avoid the plain version without MinGW unless you already have a compiler installed.</li>
<li>Run the installer. Accept the licence, keep the default installation folder, and choose "Full" installation when asked.</li>
<li>After installation, open Code::Blocks. A small window may ask you to choose a compiler; select GNU GCC Compiler and click "Set as default."</li>
<li>Click File → New → Project, choose Console Application, then select C++. Give it a name such as "FirstTest" and save it in your Documents folder.</li>
<li>Click the green "Build and run" button. If a black window appears with "Hello world!" or similar text, your setup is working.</li>
</ol>

<h2>Option B: Installing VS Code with g++</h2>
<p>Visual Studio Code, or VS Code, is a lightweight editor made by Microsoft. It does not include a compiler, so you must install MinGW separately.</p>
<ol>
<li>Download and install MinGW-w64 from <a href="https://www.mingw-w64.org/downloads/">mingw-w64.org</a>. The MSYS2 installer is reliable but requires an internet connection during installation. Make sure to install the <code>mingw-w64-x86_64-gcc</code> package.</li>
<li>Add the compiler to your system PATH. The default folder is usually <code>C:\msys64\mingw64\bin</code> or <code>C:\mingw64\bin</code>.</li>
<li>Open a Command Prompt, type <code>g++ --version</code>, and press Enter. You should see version information. If you see "g++ is not recognised," check your PATH setting.</li>
<li>Download VS Code from <a href="https://code.visualstudio.com/">code.visualstudio.com</a> and install it.</li>
<li>Open VS Code, click Extensions, and install the "C/C++" extension by Microsoft.</li>
<li>Create a folder named "CppPractice" in Documents, open it in VS Code, create a file called <code>main.cpp</code>, and type a simple "Hello, Kalomo!" program. Press Ctrl+Shift+B to build, then run the resulting <code>main.exe</code> from the terminal.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Install either Code::Blocks or VS Code on your computer or college machine.</li>
<li>Create a new console project or file named "SetupTest" and type a program that prints your name and the name of your town.</li>
<li>Build and run the program. Take a screenshot of the output window or terminal.</li>
<li>If you get an error, read the message carefully. Common mistakes include missing semicolons, misspelling <code>main</code>, or forgetting to save the file before building.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Compiler</strong> — a tool that turns C++ source code into an executable program.</li>
<li><strong>g++</strong> — the C++ compiler that is part of the GNU Compiler Collection.</li>
<li><strong>IDE</strong> — Integrated Development Environment; a program that combines editing, building, and debugging tools.</li>
<li><strong>PATH</strong> — a system setting that tells Windows where to find command-line programs such as g++.</li>
<li><strong>Console application</strong> — a text-based program that reads and writes in a terminal or command window.</li>
</ul>

<h2>Summary</h2>
<p>Before you can write C++ programs, you need a code editor and a compiler. Code::Blocks with MinGW is the easiest all-in-one choice for beginners, while VS Code with g++ gives you a more modern editor once you are comfortable installing a compiler. After following the steps in this lesson, you should be able to create a new project, build it, and see output on the screen.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.codeblocks.org/downloads/">Code::Blocks Downloads</a></li>
<li><a href="https://code.visualstudio.com/docs/cpp/config-mingw">VS Code — Configure MinGW</a></li>
<li><a href="https://www.mingw-w64.org/downloads/">MinGW-w64 Downloads</a></li>
<li><a href="https://www.w3schools.com/cpp/cpp_install.asp">W3Schools — C++ Install</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.3 Your First C++ Program',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to write a simple C++ program that prints text to the screen, explain the purpose of the main function and the <code>#include</code> directive, and build and run your program using your chosen editor.</p>

<h2>The Simplest C++ Program</h2>
<p>Every C++ program starts with a special function called <code>main</code>. When you run the program, the computer looks for <code>main</code> and begins executing the instructions inside it. Below is the smallest useful C++ program. It prints a greeting.</p>
<pre><code>#include &lt;iostream&gt;

int main() {
    std::cout &lt;&lt; "Hello, Kalomo!" &lt;&lt; std::endl;
    return 0;
}</code></pre>
<p>Let us break this down line by line. <code>#include &lt;iostream&gt;</code> tells the compiler to include the input-output stream library, which provides <code>std::cout</code> for printing to the screen. <code>int main()</code> declares the main function. The curly braces <code>{ }</code> surround the body of the function. <code>std::cout &lt;&lt; "Hello, Kalomo!"</code> sends the text to the screen, and <code>std::endl</code> moves the cursor to the next line. Finally, <code>return 0;</code> tells the operating system that the program finished successfully.</p>

<h2>Understanding the Parts</h2>
<ul>
<li><strong>Include directive</strong>: Libraries such as <code>iostream</code> contain pre-written code. You include them so you do not have to write everything yourself.</li>
<li><strong>Semicolons</strong>: In C++, most statements end with a semicolon. Forgetting one is one of the most common beginner errors.</li>
<li><strong>Curly braces</strong>: These group statements together. Every opening brace must have a matching closing brace.</li>
<li><strong>Main function</strong>: The entry point of the program. The operating system calls this function when the program starts.</li>
<li><strong>Return value</strong>: <code>return 0;</code> is the conventional way to say "the program ended normally."</li>
</ul>

<h2>Worked Example: A Personal Greeting</h2>
<p>Suppose you want the program to greet a student by name. You can print several lines of text.</p>
<pre><code>#include &lt;iostream&gt;

int main() {
    std::cout &lt;&lt; "Welcome to Edutrack C++." &lt;&lt; std::endl;
    std::cout &lt;&lt; "Student name: Mercy Mumba" &lt;&lt; std::endl;
    std::cout &lt;&lt; "Town: Kalomo" &lt;&lt; std::endl;
    return 0;
}</code></pre>
<p>When you run this program, you see three lines of output. Each <code>&lt;&lt;</code> operator sends more text to the output stream. You can also combine text on one line: <code>std::cout &lt;&lt; "Name: " &lt;&lt; "Mercy" &lt;&lt; std::endl;</code>.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open your editor and create a new C++ file named <code>greeting.cpp</code>.</li>
<li>Type the simple "Hello, Kalomo!" program exactly as shown above.</li>
<li>Change the message to include your own name, your town, and one thing you hope to build with C++.</li>
<li>Build and run the program. Fix any compiler errors before moving on.</li>
<li>Experiment by adding a fourth line that prints "ZMW" to represent the Zambian Kwacha.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Function</strong> — a named block of code that performs a specific task.</li>
<li><strong>Library</strong> — a collection of pre-written code that programmers can reuse.</li>
<li><strong>Statement</strong> — a single instruction in a program, usually ending with a semicolon.</li>
<li><strong>Operator</strong> — a symbol that performs an action, such as <code>&lt;&lt;</code> for sending output.</li>
<li><strong>String</strong> — a sequence of characters enclosed in double quotes.</li>
</ul>

<h2>Summary</h2>
<p>A C++ program is built from includes, a main function, statements, and semicolons. The <code>iostream</code> library lets you print text using <code>std::cout</code> and the <code>&lt;&lt;</code> operator. By writing and running your first program, you have already taken the most important step in learning to code: turning ideas into working software.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/cpp/cpp_syntax.asp">W3Schools — C++ Syntax</a></li>
<li><a href="https://www.freecodecamp.org/news/learn-c-with-free-31-hour-course/">freeCodeCamp — C++ Basics</a></li>
<li><a href="https://learn.microsoft.com/en-us/cpp/cpp/main-function-command-line-args">Microsoft Learn — The main Function</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.4 Reading Errors and Using <code>cin</code>',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to read simple compiler error messages and fix common mistakes, use <code>std::cin</code> to read keyboard input, and write a program that asks the user a question and responds based on the answer.</p>

<h2>Making Friends with Error Messages</h2>
<p>Compiler errors are not punishment. They are the compiler's way of telling you that it does not understand something. Learning to read errors is one of the most valuable skills a programmer can develop. Common C++ errors include missing semicolons, missing closing braces, misspelled variable names, and using a variable before declaring it.</p>
<p>For example, if you forget the semicolon after <code>std::cout &lt;&lt; "Hello"</code>, the compiler might report an error on the next line. Always look at the line number in the error message, then look just before it. Many errors are caused by a mistake on the previous line. If the message says "expected ';' before 'return'," you almost certainly forgot a semicolon.</p>

<h2>Reading Keyboard Input with <code>cin</code></h2>
<p>Printing text is useful, but most programs also need to read input. The <code>std::cin</code> object reads data from the keyboard. You use the <code>&gt;&gt;</code> operator to store what the user types into a variable. Here is an example:</p>
<pre><code>#include &lt;iostream&gt;
#include &lt;string&gt;

int main() {
    std::string name;
    std::cout &lt;&lt; "Enter your name: ";
    std::cin &gt;&gt; name;
    std::cout &lt;&lt; "Welcome, " &lt;&lt; name &lt;&lt; "!" &lt;&lt; std::endl;
    return 0;
}</code></pre>
<p>In this program, <code>std::string name;</code> creates a variable that can hold text. The program pauses at <code>std::cin &gt;&gt; name;</code> and waits for the user to type something and press Enter. Whatever the user types is stored in <code>name</code>, and the next line prints a personalised greeting.</p>

<h2>Worked Example: A Simple Age Checker</h2>
<p>Suppose you want to ask the user for their age and print whether they are old enough to register for a course.</p>
<pre><code>#include &lt;iostream&gt;

int main() {
    int age;
    std::cout &lt;&lt; "Enter your age: ";
    std::cin &gt;&gt; age;
    std::cout &lt;&lt; "Next year you will be " &lt;&lt; age + 1 &lt;&lt; " years old." &lt;&lt; std::endl;
    return 0;
}</code></pre>
<p>This program uses an <code>int</code> variable to store a whole number. You will learn more about types in the next module, but notice how <code>std::cin</code> makes the program interactive.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Deliberately remove a semicolon from one of your earlier programs and read the error message. Then fix it.</li>
<li>Write a program that asks for the user's first name and last name, then prints "Hello, First Last!"</li>
<li>Write a program that asks for a number of ZESCO units and prints "You bought X units," replacing X with the number entered.</li>
<li>Try entering text when the program expects a number. Observe the strange behaviour, and remember that <code>std::cin</code> is not yet robust; we will improve input handling later in the course.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Compiler error</strong> — a message from the compiler explaining why it cannot translate your code.</li>
<li><strong>Variable</strong> — a named storage location that holds a value.</li>
<li><strong>Input</strong> — data that a program receives from the user or another source.</li>
<li><strong>Output</strong> — data that a program sends to the screen, file, or another destination.</li>
<li><strong>String</strong> — a sequence of text characters.</li>
</ul>

<h2>Summary</h2>
<p>Error messages are a normal part of programming. By reading them carefully, you can fix most beginner mistakes quickly. Programs become more useful when they read input, and <code>std::cin</code> is the simplest way to get keyboard input in C++. Combined with <code>std::cout</code>, you can now write interactive console programs.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/cpp/cpp_user_input.asp">W3Schools — C++ User Input</a></li>
<li><a href="https://www.freecodecamp.org/news/learn-c-with-free-31-hour-course/">freeCodeCamp — C++ for Beginners</a></li>
<li><a href="https://learn.microsoft.com/en-us/cpp/cpp/errors-cpp">Microsoft Learn — C++ Errors</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: Welcome to C++',
            'description' => 'Test your understanding of why C++ matters, how to set up a compiler, and how to write your first program.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following best describes C++?',
                    'explanation' => 'C++ is a compiled, general-purpose language used for systems, games, and embedded programming.',
                    'options' => [
                        ['text' => 'A web-only scripting language', 'is_correct' => false],
                        ['text' => 'A compiled language used for systems and games', 'is_correct' => true],
                        ['text' => 'A database query language', 'is_correct' => false],
                        ['text' => 'A spreadsheet formula language', 'is_correct' => false],
                    ],
                ],

                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which function is the entry point of a C++ program?',
                    'explanation' => 'Execution begins at the main function in every C++ program.',
                    'options' => [
                        ['text' => 'start()', 'is_correct' => false],
                        ['text' => 'main()', 'is_correct' => true],
                        ['text' => 'entry()', 'is_correct' => false],
                        ['text' => 'run()', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which library provides std::cout and std::cin?',
                    'explanation' => 'The iostream library provides standard input and output streams.',
                    'options' => [
                        ['text' => 'cmath', 'is_correct' => false],
                        ['text' => 'string', 'is_correct' => false],
                        ['text' => 'iostream', 'is_correct' => true],
                        ['text' => 'fstream', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the purpose of a semicolon at the end of a C++ statement?',
                    'explanation' => 'A semicolon marks the end of a statement, just like a full stop ends a sentence.',
                    'options' => [
                        ['text' => 'It starts a new function', 'is_correct' => false],
                        ['text' => 'It marks the end of a statement', 'is_correct' => true],
                        ['text' => 'It prints output', 'is_correct' => false],
                        ['text' => 'It includes a library', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Code::Blocks with MinGW is a common all-in-one setup for beginner C++ programmers on Windows.',
                    'explanation' => 'The Code::Blocks MinGW setup bundles the editor and compiler together, making it beginner-friendly.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'std::cin uses the << operator to send output to the screen.',
                    'explanation' => 'std::cin uses the >> operator to read input, while std::cout uses << for output.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'Which object is used to print text to the screen in standard C++? (one word)',
                    'explanation' => 'std::cout is the standard character output object used for printing text.',
                    'correct_answer' => 'cout',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which command checks whether g++ is installed on a Windows command prompt?',
                    'explanation' => 'g++ --version displays the installed compiler version if it is available.',
                    'options' => [
                        ['text' => 'g++ --install', 'is_correct' => false],
                        ['text' => 'g++ --version', 'is_correct' => true],
                        ['text' => 'g++ --start', 'is_correct' => false],
                        ['text' => 'g++ --compile', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does return 0; typically mean at the end of main?',
                    'explanation' => 'return 0; conventionally signals that the program finished successfully.',
                    'options' => [
                        ['text' => 'The program failed', 'is_correct' => false],
                        ['text' => 'The program has no output', 'is_correct' => false],
                        ['text' => 'The program ended successfully', 'is_correct' => true],
                        ['text' => 'The program should restart', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module2Lessons(): array
    {
        return [
            [
                'title' => '2.1 Variables and Data Types in C++',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to declare variables of different types, explain the difference between whole numbers, decimal numbers, single characters, and true-or-false values, and choose an appropriate type for common pieces of data such as prices, ages, and names.</p>

<h2>What Is a Variable?</h2>
<p>A variable is a named box in the computer's memory where you can store a value. You give the box a name so you can use it later, and you tell the compiler what kind of value it will hold so the computer knows how much space to reserve. In C++, you must declare a variable before you use it. A declaration consists of a type followed by a name.</p>

<h2>Common C++ Data Types</h2>
<p>C++ provides several built-in types for different kinds of data:</p>
<ul>
<li><strong>int</strong> — stores whole numbers such as 25, -3, or 1000. Use it for counts and ages.</li>
<li><strong>double</strong> — stores numbers with decimal places such as 12.50 or -0.99. Use it for money amounts, measurements, and averages.</li>
<li><strong>char</strong> — stores a single character such as 'A', 'b', or '5'. Use it when you need exactly one letter or symbol.</li>
<li><strong>bool</strong> — stores either true or false. Use it for flags and yes-or-no decisions.</li>
<li><strong>std::string</strong> — stores a sequence of characters such as a name or address. It comes from the <code>&lt;string&gt;</code> library.</li>
</ul>

<h2>Declaring Variables</h2>
<p>Here are some examples of variable declarations:</p>
<pre><code>int age = 22;
double price = 45.99;
char grade = 'A';
bool isPaid = true;
std::string name = "Mercy Mumba";</code></pre>
<p>You can also declare a variable without giving it a value immediately, but you must assign a value before using it. For example, <code>int quantity;</code> creates a variable, but <code>std::cout &lt;&lt; quantity;</code> before assignment would give unpredictable results.</p>

<h2>Worked Example: Declaring Shop Data</h2>
<p>Imagine you are writing a small program to record a sale at a Kalomo shop.</p>
<pre><code>#include &lt;iostream&gt;
#include &lt;string&gt;

int main() {
    std::string item = "Bag of mealie meal";
    int quantity = 2;
    double unitPrice = 75.50;
    bool paid = true;

    double total = quantity * unitPrice;

    std::cout &lt;&lt; "Item: " &lt;&lt; item &lt;&lt; std::endl;
    std::cout &lt;&lt; "Quantity: " &lt;&lt; quantity &lt;&lt; std::endl;
    std::cout &lt;&lt; "Total: ZMW " &lt;&lt; total &lt;&lt; std::endl;
    std::cout &lt;&lt; "Paid: " &lt;&lt; paid &lt;&lt; std::endl;

    return 0;
}</code></pre>
<p>This program stores four different kinds of data and prints them. Notice how <code>double</code> is used for money, because prices usually include ngwee.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write a program that declares variables for your name, age, home town, and whether you have used a computer before.</li>
<li>Print all the values on separate lines using <code>std::cout</code>.</li>
<li>Change the value of one variable and run the program again.</li>
<li>Experiment with incorrect declarations, such as <code>int price = 12.50;</code>, and observe the warning or result.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Variable</strong> — a named location in memory that stores a value.</li>
<li><strong>Data type</strong> — the kind of value a variable can hold, such as int, double, or string.</li>
<li><strong>Declaration</strong> — a statement that introduces a variable and its type.</li>
<li><strong>Initialisation</strong> — giving a variable a value when it is declared.</li>
<li><strong>Identifier</strong> — the name you choose for a variable or function.</li>
</ul>

<h2>Summary</h2>
<p>C++ variables are declared with a type and a name, and they can hold whole numbers, decimal numbers, characters, true-or-false values, or text. Choosing the right type is important for correctness and memory use. With variables, you can now store and display real-world information such as names, prices, and quantities.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/cpp/cpp_variables.asp">W3Schools — C++ Variables</a></li>
<li><a href="https://www.w3schools.com/cpp/cpp_data_types.asp">W3Schools — C++ Data Types</a></li>
<li><a href="https://www.freecodecamp.org/news/learn-c-with-free-31-hour-course/">freeCodeCamp — C++ Tutorial</a></li>
</ul>
HTML,
            ],

            [
                'title' => '2.2 Operators and Kwacha Arithmetic',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to use arithmetic operators to perform calculations in C++, understand integer division and the modulus operator, and write a program that calculates totals, discounts, and change in Zambian Kwacha.</p>

<h2>Arithmetic Operators</h2>
<p>C++ uses familiar symbols for arithmetic:</p>
<ul>
<li><strong>+</strong> addition</li>
<li><strong>-</strong> subtraction</li>
<li><strong>*</strong> multiplication</li>
<li><strong>/</strong> division</li>
<li><strong>%</strong> modulus, which gives the remainder after division</li>
</ul>
<p>When both operands are integers, division gives an integer result. For example, <code>7 / 2</code> gives <code>3</code>, not <code>3.5</code>, because the decimal part is discarded. If you want a decimal result, at least one operand must be a <code>double</code>, such as <code>7.0 / 2</code>.</p>

<h2>Modulus: The Remainder Operator</h2>
<p>The modulus operator <code>%</code> is extremely useful when dealing with money, time, or grouping. For example, if you have 237 ngwee and you want to know how many whole kwacha and how many ngwee remain, you can use division and modulus.</p>
<pre><code>int totalNgwee = 237;
int kwacha = totalNgwee / 100;  // 2
int ngwee = totalNgwee % 100;   // 37</code></pre>
<p>This gives 2 Kwacha and 37 ngwee. The modulus operator will also help you determine whether a number is even or odd: a number is even if <code>number % 2 == 0</code>.</p>

<h2>Worked Example: Shop Receipt Calculator</h2>
<p>Suppose a customer buys three items at a shop in Kalomo. The program should calculate the subtotal, apply a discount, add tax, and show the final amount.</p>
<pre><code>#include &lt;iostream&gt;

int main() {
    double rice = 45.50;
    double cookingOil = 28.00;
    double salt = 8.50;

    double subtotal = rice + cookingOil + salt;
    double discount = subtotal * 0.05;  // 5% discount
    double afterDiscount = subtotal - discount;
    double tax = afterDiscount * 0.16;  // 16% VAT
    double total = afterDiscount + tax;

    std::cout &lt;&lt; "Subtotal: ZMW " &lt;&lt; subtotal &lt;&lt; std::endl;
    std::cout &lt;&lt; "Discount: ZMW " &lt;&lt; discount &lt;&lt; std::endl;
    std::cout &lt;&lt; "VAT: ZMW " &lt;&lt; tax &lt;&lt; std::endl;
    std::cout &lt;&lt; "Total: ZMW " &lt;&lt; total &lt;&lt; std::endl;

    return 0;
}</code></pre>
<p>This program uses addition, subtraction, multiplication, and assignment. Notice how parentheses are not needed here because multiplication happens before addition according to operator precedence.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write a program that asks for the price of an item and the amount paid, then calculates the change.</li>
<li>Extend the program to calculate how many K50, K20, K10, K5, and K2 notes should be given as change. Use integer division and modulus.</li>
<li>Write a program that reads a number of eggs and prints how many dozens that is and how many eggs remain.</li>
<li>Test your shop receipt program with different prices to ensure the arithmetic is correct.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Operator</strong> — a symbol that performs a calculation or comparison.</li>
<li><strong>Operand</strong> — a value that an operator works on.</li>
<li><strong>Integer division</strong> — division that discards the decimal part when both operands are integers.</li>
<li><strong>Modulus</strong> — the remainder after division, represented by the % operator.</li>
<li><strong>Operator precedence</strong> — the rules that decide which operations are performed first.</li>
</ul>

<h2>Summary</h2>
<p>C++ provides arithmetic operators for addition, subtraction, multiplication, division, and modulus. Integer division and modulus are especially useful for splitting amounts into notes and coins. By combining variables and operators, you can build practical calculators for shop receipts, change, and everyday Zambian transactions.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/cpp/cpp_operators.asp">W3Schools — C++ Operators</a></li>
<li><a href="https://www.freecodecamp.org/news/learn-c-with-free-31-hour-course/">freeCodeCamp — C++ Operators</a></li>
<li><a href="https://learn.microsoft.com/en-us/cpp/cpp/cpp-built-in-operators-precedence-and-associativity">Microsoft Learn — C++ Operators</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.3 Type Conversion and Constants',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to convert values between numeric types safely, understand when data can be lost during conversion, and use constants to protect values that should not change, such as VAT rates and discount percentages.</p>

<h2>Implicit and Explicit Conversion</h2>
<p>Sometimes you need to change a value from one type to another. C++ performs some conversions automatically. For example, if you assign an <code>int</code> to a <code>double</code>, the integer is converted to a decimal value without losing data.</p>
<pre><code>int eggs = 12;
double dozen = eggs;  // dozen becomes 12.0</code></pre>
<p>However, converting a <code>double</code> to an <code>int</code> can lose data because the decimal part is discarded.</p>
<pre><code>double price = 19.99;
int wholePrice = price;  // wholePrice becomes 19</code></pre>
<p>This automatic conversion is called <strong>implicit conversion</strong>. You can also convert explicitly using a <strong>cast</strong>:</p>
<pre><code>double average = 7.8;
int rounded = static_cast&lt;int&gt;(average);  // rounded becomes 7</code></pre>
<p>Casting does not change the original variable; it creates a temporary converted value. Use casts when you deliberately want to change a type and you understand what may be lost.</p>

<h2>Constants</h2>
<p>A constant is a variable whose value cannot change after it is initialised. In C++, declare a constant with the <code>const</code> keyword. Constants are useful for values that should remain fixed, such as the VAT rate or the number of months in a year.</p>
<pre><code>const double VAT_RATE = 0.16;
const int MONTHS_IN_YEAR = 12;</code></pre>
<p>By convention, constant names are written in uppercase with underscores. Using constants makes your code easier to read and safer to modify. If the government changes the VAT rate, you update one constant instead of searching through the entire program.</p>

<h2>Worked Example: Converting and Fixing Values</h2>
<p>Suppose you sell tomatoes by weight. The scale gives a <code>double</code> in kilograms, but your customer wants to know how many grams they bought.</p>
<pre><code>#include &lt;iostream&gt;

int main() {
    const double GRAMS_PER_KG = 1000.0;
    double weightKg = 2.75;
    int weightGrams = static_cast&lt;int&gt;(weightKg * GRAMS_PER_KG);

    std::cout &lt;&lt; "Weight in kg: " &lt;&lt; weightKg &lt;&lt; std::endl;
    std::cout &lt;&lt; "Weight in grams: " &lt;&lt; weightGrams &lt;&lt; std::endl;

    return 0;
}</code></pre>
<p>This program uses a constant for the conversion factor and a cast to produce a whole number of grams.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Declare a constant for the current exchange rate of your choice and use it to convert an amount.</li>
<li>Write a program that reads a temperature in Celsius as a double, converts it to an integer Fahrenheit, and prints both values.</li>
<li>Try assigning a large int to a short int. Observe any warning or unexpected result.</li>
<li>Explain in your own words why converting from double to int may cause data loss.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Type conversion</strong> — changing a value from one data type to another.</li>
<li><strong>Implicit conversion</strong> — automatic conversion performed by the compiler.</li>
<li><strong>Explicit conversion</strong> — conversion requested by the programmer using a cast.</li>
<li><strong>const</strong> — a keyword that makes a variable unchangeable after initialisation.</li>
<li><strong>Literal</strong> — a fixed value written directly in code, such as 100 or "Hello".</li>
</ul>

<h2>Summary</h2>
<p>C++ allows values to be converted between types, but you must be careful because some conversions lose data. Constants protect important values from accidental change and make programs easier to maintain. Together, type conversion and constants help you write accurate and reliable arithmetic programs.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/cpp/cpp_type_conversion.asp">W3Schools — C++ Type Conversion</a></li>
<li><a href="https://www.w3schools.com/cpp/cpp_constants.asp">W3Schools — C++ Constants</a></li>
<li><a href="https://learn.microsoft.com/en-us/cpp/cpp/type-conversions-and-type-safety-modern-cpp">Microsoft Learn — Type Conversions</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.4 Worked Example: Shop Receipt with User Input',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to combine variables, arithmetic, constants, and user input to build a complete shop receipt calculator that reads prices and quantities from the user and prints a formatted total.</p>

<h2>Bringing the Pieces Together</h2>
<p>So far you have learned how to print text, read input, store values in variables, perform arithmetic, and use constants. This lesson combines all of these skills into one practical program. The program will ask a shopkeeper to enter the names, quantities, and unit prices of three items, then calculate and display a receipt.</p>

<h2>Step-by-Step Program</h2>
<pre><code>#include &lt;iostream&gt;
#include &lt;string&gt;
#include &lt;iomanip&gt;

int main() {
    const double VAT_RATE = 0.16;

    std::string item1, item2, item3;
    int qty1, qty2, qty3;
    double price1, price2, price3;

    std::cout &lt;&lt; "Enter item 1 name: ";
    std::cin &gt;&gt; item1;
    std::cout &lt;&lt; "Enter quantity and unit price: ";
    std::cin &gt;&gt; qty1 &gt;&gt; price1;

    std::cout &lt;&lt; "Enter item 2 name: ";
    std::cin &gt;&gt; item2;
    std::cout &lt;&lt; "Enter quantity and unit price: ";
    std::cin &gt;&gt; qty2 &gt;&gt; price2;

    std::cout &lt;&lt; "Enter item 3 name: ";
    std::cin &gt;&gt; item3;
    std::cout &lt;&lt; "Enter quantity and unit price: ";
    std::cin &gt;&gt; qty3 &gt;&gt; price3;

    double line1 = qty1 * price1;
    double line2 = qty2 * price2;
    double line3 = qty3 * price3;
    double subtotal = line1 + line2 + line3;
    double vat = subtotal * VAT_RATE;
    double total = subtotal + vat;

    std::cout &lt;&lt; std::fixed &lt;&lt; std::setprecision(2);
    std::cout &lt;&lt; "\n--- RECEIPT ---" &lt;&lt; std::endl;
    std::cout &lt;&lt; item1 &lt;&lt; " x" &lt;&lt; qty1 &lt;&lt; " = ZMW " &lt;&lt; line1 &lt;&lt; std::endl;
    std::cout &lt;&lt; item2 &lt;&lt; " x" &lt;&lt; qty2 &lt;&lt; " = ZMW " &lt;&lt; line2 &lt;&lt; std::endl;
    std::cout &lt;&lt; item3 &lt;&lt; " x" &lt;&lt; qty3 &lt;&lt; " = ZMW " &lt;&lt; line3 &lt;&lt; std::endl;
    std::cout &lt;&lt; "Subtotal: ZMW " &lt;&lt; subtotal &lt;&lt; std::endl;
    std::cout &lt;&lt; "VAT: ZMW " &lt;&lt; vat &lt;&lt; std::endl;
    std::cout &lt;&lt; "TOTAL: ZMW " &lt;&lt; total &lt;&lt; std::endl;

    return 0;
}</code></pre>
<p>This program uses <code>std::fixed</code> and <code>std::setprecision(2)</code> from the <code>&lt;iomanip&gt;</code> library to display money with exactly two decimal places. Without this, the output might show many decimal places or only one.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Type the receipt program exactly as shown and run it with sample data.</li>
<li>Add a constant discount rate and apply it before VAT is calculated.</li>
<li>Change the program to ask for four items instead of three.</li>
<li>Experiment with <code>std::setprecision</code> values such as 1, 3, and 4 to see how the output changes.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Formatted output</strong> — controlling how values are displayed, such as the number of decimal places.</li>
<li><strong>iomanip</strong> — a library that provides tools for formatting input and output.</li>
<li><strong>setprecision</strong> — a function that sets the number of digits displayed for floating-point numbers.</li>
<li><strong>fixed</strong> — a format flag that forces fixed-point notation for decimals.</li>
<li><strong>Chaining</strong> — using multiple &lt;&lt; or &gt;&gt; operators in one statement.</li>
</ul>

<h2>Summary</h2>
<p>By combining variables, constants, arithmetic, input, and output, you can build programs that solve real business problems. The shop receipt calculator is a practical example that a small business owner in Kalomo could use to speed up checkout and reduce arithmetic mistakes. Always format money carefully using <code>std::fixed</code> and <code>std::setprecision(2)</code>.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/cpp/cpp_user_input.asp">W3Schools — C++ User Input</a></li>
<li><a href="https://www.w3schools.com/cpp/cpp_data_types_float.asp">W3Schools — C++ Float Precision</a></li>
<li><a href="https://learn.microsoft.com/en-us/cpp/standard-library/iomanip">Microsoft Learn — iomanip Library</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Variables, Types, and Kwacha Arithmetic',
            'description' => 'Test your knowledge of C++ variables, data types, operators, type conversion, and constants.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which C++ type is most appropriate for storing a price such as 45.99?',
                    'explanation' => 'Money values usually have decimal places, so double is the appropriate type.',
                    'options' => [
                        ['text' => 'int', 'is_correct' => false],
                        ['text' => 'char', 'is_correct' => false],
                        ['text' => 'double', 'is_correct' => true],
                        ['text' => 'bool', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the result of the expression 17 % 5?',
                    'explanation' => '17 divided by 5 is 3 with a remainder of 2, so 17 % 5 equals 2.',
                    'options' => [
                        ['text' => '3', 'is_correct' => false],
                        ['text' => '2', 'is_correct' => true],
                        ['text' => '0', 'is_correct' => false],
                        ['text' => '5', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What happens when you assign a double value such as 19.99 to an int variable?',
                    'explanation' => 'Converting a double to an int truncates the decimal part, which may lose data.',
                    'options' => [
                        ['text' => 'The value is rounded to the nearest integer', 'is_correct' => false],
                        ['text' => 'The decimal part is discarded', 'is_correct' => true],
                        ['text' => 'The compiler rejects the assignment', 'is_correct' => false],
                        ['text' => 'The variable becomes a double', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which keyword declares a variable whose value cannot change?',
                    'explanation' => 'The const keyword makes a variable read-only after initialisation.',
                    'options' => [
                        ['text' => 'static', 'is_correct' => false],
                        ['text' => 'final', 'is_correct' => false],
                        ['text' => 'const', 'is_correct' => true],
                        ['text' => 'readonly', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'The expression 7 / 2 evaluates to 3.5 when both operands are ints.',
                    'explanation' => 'Integer division discards the decimal part, so 7 / 2 equals 3.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'By convention, constant names in C++ are written in uppercase with underscores.',
                    'explanation' => 'Constants are commonly named with uppercase letters and underscores, such as VAT_RATE.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'Which operator gives the remainder of an integer division? (one symbol)',
                    'explanation' => 'The % operator returns the remainder after division.',
                    'correct_answer' => '%',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which library provides std::setprecision for formatting output?',
                    'explanation' => 'The iomanip library contains formatting helpers such as setprecision and fixed.',
                    'options' => [
                        ['text' => 'iostream', 'is_correct' => false],
                        ['text' => 'string', 'is_correct' => false],
                        ['text' => 'iomanip', 'is_correct' => true],
                        ['text' => 'cmath', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which type is best for storing a whole number such as 1000?',
                    'explanation' => 'int is designed to store whole numbers.',
                    'options' => [
                        ['text' => 'double', 'is_correct' => false],
                        ['text' => 'char', 'is_correct' => false],
                        ['text' => 'int', 'is_correct' => true],
                        ['text' => 'string', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module3Lessons(): array
    {
        return [
            [
                'title' => '3.1 Making Decisions with if-else and switch',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to write C++ programs that make decisions using <code>if</code>, <code>else if</code>, <code>else</code>, and <code>switch</code>, and choose the right decision structure for different problems.</p>

<h2>Why Programs Need Decisions</h2>
<p>Most useful programs do not follow a straight line. They make choices based on conditions. A mobile money app checks whether the balance is enough before sending money. A ZESCO token app checks whether the meter number is valid. A school system checks whether a mark is high enough to pass. In C++, the <code>if</code> statement is the main tool for making decisions.</p>

<h2>The if Statement</h2>
<p>An <code>if</code> statement tests a condition. If the condition is true, the code inside the block runs. If not, it is skipped.</p>
<pre><code>#include &lt;iostream&gt;

int main() {
    int age;
    std::cout &lt;&lt; "Enter your age: ";
    std::cin &gt;&gt; age;

    if (age &gt;= 18) {
        std::cout &lt;&lt; "You are an adult." &lt;&lt; std::endl;
    }

    return 0;
}</code></pre>
<p>The condition inside the parentheses must evaluate to true or false. Comparison operators include <code>==</code> (equal to), <code>!=</code> (not equal to), <code>&lt;</code>, <code>&gt;</code>, <code>&lt;=</code>, and <code>&gt;=</code>.</p>

<h2>if-else and else if</h2>
<p>When there are two possible paths, use <code>if-else</code>. When there are many paths, use <code>else if</code>.</p>
<pre><code>if (mark &gt;= 80) {
    std::cout &lt;&lt; "Distinction" &lt;&lt; std::endl;
} else if (mark &gt;= 60) {
    std::cout &lt;&lt; "Merit" &lt;&lt; std::endl;
} else if (mark &gt;= 50) {
    std::cout &lt;&lt; "Pass" &lt;&lt; std::endl;
} else {
    std::cout &lt;&lt; "Fail" &lt;&lt; std::endl;
}</code></pre>
<p>Only one branch runs. The conditions are checked from top to bottom, so the order matters. If you test <code>mark &gt;= 60</code> before <code>mark &gt;= 80</code>, a distinction will never be awarded.</p>

<h2>The switch Statement</h2>
<p>When you compare one variable against many exact values, <code>switch</code> can be cleaner than many <code>else if</code> statements.</p>
<pre><code>int choice;
std::cin &gt;&gt; choice;

switch (choice) {
    case 1:
        std::cout &lt;&lt; "Buy ZESCO tokens" &lt;&lt; std::endl;
        break;
    case 2:
        std::cout &lt;&lt; "Buy airtime" &lt;&lt; std::endl;
        break;
    case 3:
        std::cout &lt;&lt; "Send money" &lt;&lt; std::endl;
        break;
    default:
        std::cout &lt;&lt; "Invalid choice" &lt;&lt; std::endl;
}</code></pre>
<p>Each <code>case</code> must end with <code>break;</code> unless you want the code to fall through to the next case. The <code>default</code> case handles unexpected values.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write a program that reads a student's mark and prints the grade: A for 80+, B for 65-79, C for 50-64, and F below 50.</li>
<li>Write a program that asks for a day number from 1 to 7 and prints the corresponding day name using a switch.</li>
<li>Write a program that checks whether a ZMW amount is enough to buy a K50 ZESCO token and prints an appropriate message.</li>
<li>Experiment by removing a break statement in a switch. Observe the fall-through behaviour.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Condition</strong> — an expression that evaluates to true or false.</li>
<li><strong>if statement</strong> — a control structure that runs code only when a condition is true.</li>
<li><strong>else if</strong> — a way to test additional conditions after an initial if.</li>
<li><strong>switch</strong> — a control structure that selects one of many code blocks based on a value.</li>
<li><strong>break</strong> — a statement that exits a switch or loop immediately.</li>
</ul>

<h2>Summary</h2>
<p>Decision-making allows programs to react differently to different inputs. Use <code>if-else</code> for range checks and complex conditions, and use <code>switch</code> when comparing one variable against a fixed list of values. These structures are the foundation of interactive programs.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/cpp/cpp_conditions.asp">W3Schools — C++ Conditions</a></li>
<li><a href="https://www.w3schools.com/cpp/cpp_switch.asp">W3Schools — C++ Switch</a></li>
<li><a href="https://www.freecodecamp.org/news/learn-c-with-free-31-hour-course/">freeCodeCamp — C++ Control Flow</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.2 Repeating Tasks with Loops',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to write <code>for</code>, <code>while</code>, and <code>do-while</code> loops, decide which loop to use for a given task, and avoid common beginner mistakes such as infinite loops and off-by-one errors.</p>

<h2>Why Use Loops?</h2>
<p>Loops let a program repeat instructions without writing the same code many times. If you need to print a receipt for twenty customers, you do not want to copy the same lines twenty times. A loop does the work for you. C++ has three main loops: <code>for</code>, <code>while</code>, and <code>do-while</code>.</p>

<h2>The for Loop</h2>
<p>A <code>for</code> loop is best when you know how many times you want to repeat something. It has three parts inside the parentheses: initialisation, condition, and update.</p>
<pre><code>#include &lt;iostream&gt;

int main() {
    for (int i = 1; i &lt;= 5; i++) {
        std::cout &lt;&lt; "Customer " &lt;&lt; i &lt;&lt; std::endl;
    }
    return 0;
}</code></pre>
<p>This loop runs five times. The variable <code>i</code> starts at 1, the loop continues while <code>i &lt;= 5</code>, and <code>i++</code> adds 1 after each iteration.</p>

<h2>The while Loop</h2>
<p>A <code>while</code> loop is best when you do not know in advance how many times to repeat, but you do know the condition that should stop the loop.</p>
<pre><code>double balance = 100.0;
double amount;

while (balance &gt; 0) {
    std::cout &lt;&lt; "Balance: ZMW " &lt;&lt; balance &lt;&lt; std::endl;
    std::cout &lt;&lt; "Enter expense (0 to stop): ";
    std::cin &gt;&gt; amount;
    balance = balance - amount;
}</code></pre>
<p>The loop continues as long as the balance is greater than zero. Be careful: if the condition never becomes false, the loop runs forever.</p>

<h2>The do-while Loop</h2>
<p>A <code>do-while</code> loop is similar to a while loop, but it checks the condition at the end. This guarantees the body runs at least once.</p>
<pre><code>int choice;
do {
    std::cout &lt;&lt; "1. Check balance\n2. Buy tokens\n3. Exit\n";
    std::cin &gt;&gt; choice;
} while (choice != 3);</code></pre>
<p>This is useful for menus where you want to show the options at least once before checking whether the user wants to quit.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write a for loop that prints the numbers from 1 to 10 and their squares.</li>
<li>Write a while loop that asks the user to enter prices until they type -1, then prints the total.</li>
<li>Write a do-while loop that displays a simple calculator menu until the user chooses to exit.</li>
<li>Create a small table showing the sum of K10 mobile money contributions for 12 members of a savings group.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Loop</strong> — a control structure that repeats a block of code.</li>
<li><strong>Iteration</strong> — one execution of a loop body.</li>
<li><strong>Infinite loop</strong> — a loop that never stops because its condition is always true.</li>
<li><strong>Off-by-one error</strong> — a common mistake where a loop runs one time too many or too few.</li>
<li><strong>Increment</strong> — increasing a variable, usually by 1, often written as i++.</li>
</ul>

<h2>Summary</h2>
<p>Loops save time and reduce errors by repeating code. Use a for loop when you know the number of iterations, a while loop when the stopping condition is more important than the count, and a do-while loop when the body must run at least once. Always make sure the loop condition can eventually become false.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/cpp/cpp_for_loop.asp">W3Schools — C++ For Loop</a></li>
<li><a href="https://www.w3schools.com/cpp/cpp_while_loop.asp">W3Schools — C++ While Loop</a></li>
<li><a href="https://www.w3schools.com/cpp/cpp_do_while_loop.asp">W3Schools — C++ Do/While Loop</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.3 Worked Example: ZESCO Tariff Calculator',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to combine decisions and loops to build a realistic ZESCO tariff calculator that recommends a purchasing strategy based on units and budget.</p>

<h2>Understanding the Problem</h2>
<p>ZESCO tariffs often have different rates depending on how much electricity you buy. For this lesson, imagine a simplified tariff structure:</p>
<ul>
<li>The first 100 units cost K1.50 each.</li>
<li>Units from 101 to 200 cost K2.00 each.</li>
<li>Any units above 200 cost K2.80 each.</li>
</ul>
<p>A customer wants to know how many units they can buy with a given amount of money, or how much a given number of units will cost. We can build both calculators using decisions and loops.</p>

<h2>Calculator 1: Cost for a Given Number of Units</h2>
<pre><code>#include &lt;iostream&gt;
#include &lt;iomanip&gt;

int main() {
    const double TIER1_RATE = 1.50;
    const double TIER2_RATE = 2.00;
    const double TIER3_RATE = 2.80;
    const int TIER1_LIMIT = 100;
    const int TIER2_LIMIT = 200;

    int units;
    std::cout &lt;&lt; "Enter number of units: ";
    std::cin &gt;&gt; units;

    double cost = 0.0;

    if (units &lt;= TIER1_LIMIT) {
        cost = units * TIER1_RATE;
    } else if (units &lt;= TIER2_LIMIT) {
        cost = TIER1_LIMIT * TIER1_RATE + (units - TIER1_LIMIT) * TIER2_RATE;
    } else {
        cost = TIER1_LIMIT * TIER1_RATE
             + (TIER2_LIMIT - TIER1_LIMIT) * TIER2_RATE
             + (units - TIER2_LIMIT) * TIER3_RATE;
    }

    std::cout &lt;&lt; std::fixed &lt;&lt; std::setprecision(2);
    std::cout &lt;&lt; "Cost for " &lt;&lt; units &lt;&lt; " units: ZMW " &lt;&lt; cost &lt;&lt; std::endl;

    return 0;
}</code></pre>
<p>This program uses tiered pricing. Notice how each branch subtracts the units already accounted for in the previous tier before applying the next rate.</p>

<h2>Calculator 2: Maximum Units for a Budget</h2>
<p>Now suppose a customer has K200 and wants to know the maximum units they can buy. We can use a loop to subtract the cost of each tier until the budget is exhausted.</p>
<pre><code>double budget;
std::cout &lt;&lt; "Enter your budget in ZMW: ";
std::cin &gt;&gt; budget;

int units = 0;
double remaining = budget;

while (remaining &gt;= TIER1_RATE && units &lt; TIER1_LIMIT) {
    units++;
    remaining -= TIER1_RATE;
}
while (remaining &gt;= TIER2_RATE && units &lt; TIER2_LIMIT) {
    units++;
    remaining -= TIER2_RATE;
}
while (remaining &gt;= TIER3_RATE) {
    units++;
    remaining -= TIER3_RATE;
}

std::cout &lt;&lt; "You can buy " &lt;&lt; units &lt;&lt; " units with ZMW " &lt;&lt; budget &lt;&lt; std::endl;</code></pre>
<p>This second calculator shows how loops and decisions work together. In a real application, you might prefer a formula over a loop for speed, but the loop makes the logic easy to understand.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Type both calculators and test them with several inputs, including edge cases such as 0, 100, 101, 200, and 201 units.</li>
<li>Add a small fixed service charge of K3.00 to every purchase.</li>
<li>Modify the budget calculator to also report how much change is left in ngwee.</li>
<li>Write a short paragraph explaining how tiered pricing affects poor households.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Tiered pricing</strong> — a pricing model where different quantities are charged at different rates.</li>
<li><strong>Edge case</strong> — an input at the boundary of valid values, such as 0 or the tier limit.</li>
<li><strong>Accumulate</strong> — to build up a total gradually inside a loop.</li>
<li><strong>Compound condition</strong> — a condition that combines two or more tests using && or ||.</li>
<li><strong>Increment/decrement</strong> — adding or subtracting from a variable, often by 1.</li>
</ul>

<h2>Summary</h2>
<p>The ZESCO tariff calculator combines constants, decisions, and loops to solve a real problem. By breaking the problem into tiers and handling each tier separately, the program remains clear and correct. Always test edge cases to make sure your logic handles boundaries properly.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/cpp/cpp_conditions.asp">W3Schools — C++ Conditions</a></li>
<li><a href="https://www.w3schools.com/cpp/cpp_for_loop.asp">W3Schools — C++ Loops</a></li>
<li><a href="https://www.freecodecamp.org/news/learn-c-with-free-31-hour-course/">freeCodeCamp — C++ Tutorial</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.4 Nested Loops, break, and continue',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to place one loop inside another, use <code>break</code> to exit a loop early, use <code>continue</code> to skip an iteration, and recognise when these tools make your programs clearer.</p>

<h2>Nested Loops</h2>
<p>A nested loop is a loop inside another loop. The inner loop runs completely for each iteration of the outer loop. Nested loops are useful for tables, grids, and repeating tasks within repeating tasks.</p>
<pre><code>#include &lt;iostream&gt;

int main() {
    for (int week = 1; week &lt;= 4; week++) {
        std::cout &lt;&lt; "Week " &lt;&lt; week &lt;&lt; ":" &lt;&lt; std::endl;
        for (int day = 1; day &lt;= 7; day++) {
            std::cout &lt;&lt; "  Day " &lt;&lt; day &lt;&lt; std::endl;
        }
    }
    return 0;
}</code></pre>
<p>This prints four weeks, each containing seven days. Be careful with nested loops: the total number of iterations is the product of the iterations of each loop.</p>

<h2>The break Statement</h2>
<p>The <code>break</code> statement immediately exits the nearest enclosing loop or switch. It is useful when you have found what you are looking for and no longer need to continue.</p>
<pre><code>int pin;
bool found = false;
for (int attempt = 1; attempt &lt;= 3; attempt++) {
    std::cout &lt;&lt; "Enter PIN: ";
    std::cin &gt;&gt; pin;
    if (pin == 1234) {
        std::cout &lt;&lt; "Access granted." &lt;&lt; std::endl;
        found = true;
        break;
    }
}
if (!found) {
    std::cout &lt;&lt; "Access denied." &lt;&lt; std::endl;
}</code></pre>
<p>As soon as the correct PIN is entered, the loop stops. Without <code>break</code>, the program would ask for all three attempts even after success.</p>

<h2>The continue Statement</h2>
<p>The <code>continue</code> statement skips the rest of the current iteration and moves to the next one. It is useful when you want to ignore certain values.</p>
<pre><code>for (int day = 1; day &lt;= 30; day++) {
    if (day % 7 == 0) {
        continue;  // skip Sundays
    }
    std::cout &lt;&lt; "Working on day " &lt;&lt; day &lt;&lt; std::endl;
}</code></pre>
<p>This loop skips every seventh day, representing a weekly rest day.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write a nested loop that prints a multiplication table from 1 to 5.</li>
<li>Write a program that searches a list of prices for the first item below K10 and stops as soon as it finds one.</li>
<li>Write a loop that prints odd numbers from 1 to 20 using continue to skip even numbers.</li>
<li>Draw a simple 5x5 grid of asterisks using nested loops.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Nested loop</strong> — a loop that is placed inside another loop.</li>
<li><strong>break</strong> — a statement that exits the current loop or switch immediately.</li>
<li><strong>continue</strong> — a statement that skips the rest of the current loop iteration.</li>
<li><strong>Inner loop</strong> — the loop that is nested inside another loop.</li>
<li><strong>Outer loop</strong> — the loop that contains another loop inside it.</li>
</ul>

<h2>Summary</h2>
<p>Nested loops handle repeating tasks within repeating tasks, while <code>break</code> and <code>continue</code> give you finer control over loop flow. Use them to make your programs more efficient and easier to read, but avoid overusing them because too many nested structures can become confusing.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/cpp/cpp_break.asp">W3Schools — C++ Break/Continue</a></li>
<li><a href="https://www.w3schools.com/cpp/cpp_for_loop_nested.asp">W3Schools — C++ Nested Loops</a></li>
<li><a href="https://www.freecodecamp.org/news/learn-c-with-free-31-hour-course/">freeCodeCamp — C++ Loops</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Control Flow',
            'description' => 'Test your understanding of if-else, switch, loops, break, continue, and the ZESCO tariff calculator.',
            'time_limit_minutes' => 25,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which operator tests whether two values are equal in C++?',
                    'explanation' => 'The == operator compares two values for equality. A single = is assignment, not comparison.',
                    'options' => [
                        ['text' => '=', 'is_correct' => false],
                        ['text' => '==', 'is_correct' => true],
                        ['text' => '!=', 'is_correct' => false],
                        ['text' => '===', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which loop is best when you know exactly how many times to repeat?',
                    'explanation' => 'A for loop is ideal when the number of iterations is known in advance.',
                    'options' => [
                        ['text' => 'while', 'is_correct' => false],
                        ['text' => 'do-while', 'is_correct' => false],
                        ['text' => 'for', 'is_correct' => true],
                        ['text' => 'switch', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does the break statement do inside a loop?',
                    'explanation' => 'break exits the nearest enclosing loop or switch immediately.',
                    'options' => [
                        ['text' => 'Skips the current iteration', 'is_correct' => false],
                        ['text' => 'Exits the loop immediately', 'is_correct' => true],
                        ['text' => 'Restarts the loop', 'is_correct' => false],
                        ['text' => 'Does nothing', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which loop always executes its body at least once?',
                    'explanation' => 'A do-while loop checks its condition after the body, so the body runs at least once.',
                    'options' => [
                        ['text' => 'for', 'is_correct' => false],
                        ['text' => 'while', 'is_correct' => false],
                        ['text' => 'do-while', 'is_correct' => true],
                        ['text' => 'if', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'In a switch statement, each case should usually end with a break statement.',
                    'explanation' => 'Without break, execution falls through to the next case, which is usually not what you want.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'The continue statement exits the loop completely.',
                    'explanation' => 'continue skips the rest of the current iteration but does not exit the loop.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'Which logical operator returns true only if both conditions are true? (one word)',
                    'explanation' => 'The && operator returns true only when both operands are true.',
                    'correct_answer' => '&&',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In the ZESCO tariff calculator, why are constants used for rates?',
                    'explanation' => 'Constants protect values from accidental change and make updates easier.',
                    'options' => [
                        ['text' => 'To make the program slower', 'is_correct' => false],
                        ['text' => 'To allow rates to change automatically', 'is_correct' => false],
                        ['text' => 'To prevent accidental changes and ease updates', 'is_correct' => true],
                        ['text' => 'To hide values from the user', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'How many times does the inner loop run if the outer loop runs 3 times and the inner loop runs 4 times?',
                    'explanation' => 'The total iterations are the product of the two loop counts: 3 * 4 = 12.',
                    'options' => [
                        ['text' => '3', 'is_correct' => false],
                        ['text' => '4', 'is_correct' => false],
                        ['text' => '7', 'is_correct' => false],
                        ['text' => '12', 'is_correct' => true],
                    ],
                ],
            ],
        ];
    }

    private function module4Lessons(): array
    {
        return [
            [
                'title' => '4.1 Writing Functions in C++',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to declare and call functions, explain parameters and return values, and use functions to organise your programs into manageable pieces.</p>

<h2>Why Functions Matter</h2>
<p>A function is a named block of code that performs a specific task. Functions help you avoid repeating code, make programs easier to read, and divide large problems into smaller pieces. In C++, every program already has at least one function: <code>main</code>. You can write additional functions and call them from <code>main</code>.</p>

<h2>Declaring and Calling a Function</h2>
<p>A function has a return type, a name, optional parameters, and a body. Below is a simple function that adds two numbers.</p>
<pre><code>#include &lt;iostream&gt;

int add(int a, int b) {
    return a + b;
}

int main() {
    int result = add(5, 3);
    std::cout &lt;&lt; "Sum: " &lt;&lt; result &lt;&lt; std::endl;
    return 0;
}</code></pre>
<p>The function <code>add</code> has two parameters, <code>a</code> and <code>b</code>. It returns an <code>int</code>. In <code>main</code>, we call the function with the arguments 5 and 3, and store the returned value in <code>result</code>.</p>

<h2>void Functions</h2>
<p>Not every function returns a value. Functions that perform an action but do not return anything use the <code>void</code> return type.</p>
<pre><code>void printWelcome() {
    std::cout &lt;&lt; "Welcome to Edutrack C++." &lt;&lt; std::endl;
}</code></pre>
<p>You call this function by writing <code>printWelcome();</code>. It prints the message and returns nothing.</p>

<h2>Worked Example: A Discount Helper</h2>
<p>Suppose a shop offers different discounts based on the total purchase. We can write a function that calculates the discount.</p>
<pre><code>double calculateDiscount(double total) {
    if (total &gt;= 500) {
        return total * 0.10;
    } else if (total &gt;= 200) {
        return total * 0.05;
    } else {
        return 0.0;
    }
}</code></pre>
<p>Now <code>main</code> can call this function for every customer. If the government changes the discount rules, you only update this one function.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write a function that converts Kwacha to ngwee and call it from main.</li>
<li>Write a void function that prints a receipt header with the shop name and date.</li>
<li>Write a function that takes a mark and returns a grade string such as "Distinction" or "Pass".</li>
<li>Write a main function that calls all three functions and displays the results.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Function</strong> — a named block of code that performs a task and may return a value.</li>
<li><strong>Parameter</strong> — a variable in the function declaration that receives a value when the function is called.</li>
<li><strong>Argument</strong> — the actual value passed to a function when it is called.</li>
<li><strong>Return value</strong> — the value a function sends back to its caller.</li>
<li><strong>void</strong> — a return type meaning the function returns no value.</li>
</ul>

<h2>Summary</h2>
<p>Functions let you organise code into reusable, named blocks. They accept parameters, perform work, and optionally return values. By using functions, you make your programs shorter, clearer, and easier to maintain.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/cpp/cpp_functions.asp">W3Schools — C++ Functions</a></li>
<li><a href="https://www.freecodecamp.org/news/learn-c-with-free-31-hour-course/">freeCodeCamp — C++ Functions</a></li>
<li><a href="https://learn.microsoft.com/en-us/cpp/cpp/functions-cpp">Microsoft Learn — C++ Functions</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.2 Arrays and Strings',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to declare and use arrays to store multiple values, access array elements by index, and work with C++ strings as a more convenient alternative to arrays of characters.</p>

<h2>What Is an Array?</h2>
<p>An array is a collection of variables of the same type, stored under one name. Instead of creating five separate variables for five prices, you can create one array with five elements.</p>
<pre><code>double prices[5] = {12.50, 8.00, 45.00, 15.75, 6.50};
std::cout &lt;&lt; prices[0] &lt;&lt; std::endl;  // prints 12.50</code></pre>
<p>Array indices start at 0, so the first element is <code>prices[0]</code> and the last is <code>prices[4]</code>. Accessing <code>prices[5]</code> would be a mistake because the array only has five elements.</p>

<h2>Loops and Arrays</h2>
<p>Arrays and loops work naturally together. To print every price, use a loop:</p>
<pre><code>for (int i = 0; i &lt; 5; i++) {
    std::cout &lt;&lt; "Price " &lt;&lt; i + 1 &lt;&lt; ": ZMW " &lt;&lt; prices[i] &lt;&lt; std::endl;
}</code></pre>
<p>Using the array size in the loop condition makes the code easier to update. If you add more prices, you only need to change the array declaration.</p>

<h2>C++ Strings</h2>
<p>Strings are sequences of characters. The <code>std::string</code> type from the <code>&lt;string&gt;</code> library is much easier to use than the older C-style string arrays.</p>
<pre><code>#include &lt;string&gt;

std::string name = "Kalomo";
std::cout &lt;&lt; name &lt;&lt; std::endl;
std::cout &lt;&lt; "Length: " &lt;&lt; name.length() &lt;&lt; std::endl;</code></pre>
<p>You can concatenate strings with the + operator, compare them with ==, and access individual characters with brackets.</p>

<h2>Worked Example: Parallel Arrays</h2>
<p>Suppose you want to store product names and their prices together. You can use two parallel arrays.</p>
<pre><code>std::string items[4] = {"Tomatoes", "Onions", "Rape", "Potatoes"};
double prices[4] = {12.50, 8.00, 5.00, 15.00};

for (int i = 0; i &lt; 4; i++) {
    std::cout &lt;&lt; items[i] &lt;&lt; " = ZMW " &lt;&lt; prices[i] &lt;&lt; std::endl;
}</code></pre>
<p>The arrays are parallel because <code>items[i]</code> corresponds to <code>prices[i]</code>. Keeping the arrays the same length is essential.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create an array of seven daily rainfall amounts and calculate the total and average.</li>
<li>Write a program that stores the names of five students in a string array and prints them.</li>
<li>Ask the user for four prices, store them in an array, then print the highest price.</li>
<li>Experiment with accessing an index outside the array bounds. Some compilers give warnings, while others may let the program run with strange results.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Array</strong> — a collection of values of the same type stored under one name.</li>
<li><strong>Index</strong> — a number used to access a specific element of an array.</li>
<li><strong>Element</strong> — a single value stored in an array.</li>
<li><strong>Parallel arrays</strong> — two or more arrays whose related data is stored at matching indices.</li>
<li><strong>Concatenation</strong> — joining two strings together.</li>
</ul>

<h2>Summary</h2>
<p>Arrays let you store many related values in one place, and loops let you process them efficiently. C++ strings make text handling much simpler than older C-style arrays. Parallel arrays are a common pattern for linking related data, such as product names and prices.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/cpp/cpp_arrays.asp">W3Schools — C++ Arrays</a></li>
<li><a href="https://www.w3schools.com/cpp/cpp_strings.asp">W3Schools — C++ Strings</a></li>
<li><a href="https://learn.microsoft.com/en-us/cpp/cpp/arrays-cpp">Microsoft Learn — C++ Arrays</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.3 Worked Example: Market Stall Price List',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to combine functions, arrays, and loops to build a price-list program that a market vendor could use to look up prices and calculate totals.</p>

<h2>The Problem</h2>
<p>A vendor at Kalomo market sells tomatoes, onions, rape, potatoes, and cabbages. Customers often ask for prices, and the vendor needs a quick way to look them up. We will build a console program that stores items and prices in parallel arrays and lets the user look up a price by item number.</p>

<h2>Step-by-Step Program</h2>
<pre><code>#include &lt;iostream&gt;
#include &lt;string&gt;
#include &lt;iomanip&gt;

void printMenu(const std::string items[], const double prices[], int size) {
    std::cout &lt;&lt; "\n--- Price List ---" &lt;&lt; std::endl;
    for (int i = 0; i &lt; size; i++) {
        std::cout &lt;&lt; i + 1 &lt;&lt; ". " &lt;&lt; items[i]
                  &lt;&lt; " = ZMW " &lt;&lt; prices[i] &lt;&lt; std::endl;
    }
}

double calculateTotal(const double prices[], int quantity[], int size) {
    double total = 0.0;
    for (int i = 0; i &lt; size; i++) {
        total += prices[i] * quantity[i];
    }
    return total;
}

int main() {
    const int SIZE = 5;
    std::string items[SIZE] = {"Tomatoes", "Onions", "Rape", "Potatoes", "Cabbages"};
    double prices[SIZE] = {12.50, 8.00, 5.00, 15.00, 20.00};
    int quantity[SIZE] = {0};

    printMenu(items, prices, SIZE);

    for (int i = 0; i &lt; SIZE; i++) {
        std::cout &lt;&lt; "Enter quantity for " &lt;&lt; items[i] &lt;&lt; ": ";
        std::cin &gt;&gt; quantity[i];
    }

    double total = calculateTotal(prices, quantity, SIZE);

    std::cout &lt;&lt; std::fixed &lt;&lt; std::setprecision(2);
    std::cout &lt;&lt; "\nTotal: ZMW " &lt;&lt; total &lt;&lt; std::endl;

    return 0;
}</code></pre>
<p>This program uses two functions: <code>printMenu</code> displays the list, and <code>calculateTotal</code> adds up the cost. The arrays are passed to functions along with their size. This pattern keeps <code>main</code> short and readable.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Type the program and test it with sample quantities.</li>
<li>Add a discount function that gives 10% off if the total is K100 or more.</li>
<li>Allow the user to look up a single price by entering the item number.</li>
<li>Add a search function that finds the index of an item by name. This is harder but very useful.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Parallel arrays</strong> — arrays that hold related data at the same indices.</li>
<li><strong>Array parameter</strong> — an array passed to a function, usually with a size parameter.</li>
<li><strong>const</strong> — used here to promise that a function will not modify an array.</li>
<li><strong>Lookup</strong> — finding information by an identifier such as a number or name.</li>
<li><strong>Running total</strong> — a total that is updated inside a loop.</li>
</ul>

<h2>Summary</h2>
<p>The market stall price list shows how functions, arrays, and loops work together to solve a practical business problem. By separating display logic from calculation logic, the program is easier to understand and maintain. Parallel arrays keep related data aligned, and functions keep the code organised.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/cpp/cpp_arrays.asp">W3Schools — C++ Arrays</a></li>
<li><a href="https://www.w3schools.com/cpp/cpp_functions.asp">W3Schools — C++ Functions</a></li>
<li><a href="https://www.freecodecamp.org/news/learn-c-with-free-31-hour-course/">freeCodeCamp — C++ Tutorial</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.4 String Operations and C-Style Strings',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to use common <code>std::string</code> operations, understand the difference between C++ strings and C-style strings, and choose the right string type for your programs.</p>

<h2>Common String Operations</h2>
<p>The <code>std::string</code> type provides many useful member functions. Here are some of the most common:</p>
<ul>
<li><strong>length()</strong> or <strong>size()</strong> — returns the number of characters.</li>
<li><strong>empty()</strong> — returns true if the string has no characters.</li>
<li><strong>substr(start, length)</strong> — returns a portion of the string.</li>
<li><strong>find(text)</strong> — returns the position of a substring, or a special value if not found.</li>
<li><strong>append(text)</strong> or the + operator — adds text to the end.</li>
</ul>

<h2>Worked Example: Formatting a Phone Number</h2>
<p>Suppose you want to check whether a Zambian phone number starts with "+260".</p>
<pre><code>#include &lt;iostream&gt;
#include &lt;string&gt;

int main() {
    std::string phone;
    std::cout &lt;&lt; "Enter phone number: ";
    std::cin &gt;&gt; phone;

    if (phone.length() == 13 && phone.substr(0, 4) == "+260") {
        std::cout &lt;&lt; "Valid Zambian number." &lt;&lt; std::endl;
    } else {
        std::cout &lt;&lt; "Please use +260 format." &lt;&lt; std::endl;
    }

    return 0;
}</code></pre>
<p>This program checks the length and prefix. In a real application, you would add more validation.</p>

<h2>C-Style Strings</h2>
<p>Before <code>std::string</code>, C and C++ used arrays of characters ending with a special null character <code>\0</code>. These are called C-style strings.</p>
<pre><code>char name[20] = "Kalomo";</code></pre>
<p>C-style strings are still important because many operating system and hardware interfaces use them. However, they are harder to work with because you must manage the array size yourself. For most beginner programs, <code>std::string</code> is the better choice.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write a program that reads a full name and prints the number of characters.</li>
<li>Write a program that checks whether an email address contains the "@" symbol.</li>
<li>Write a program that reads a product code such as "ZESCO123" and extracts the numeric part.</li>
<li>Experiment with <code>std::getline</code> to read a line of text that contains spaces.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Member function</strong> — a function that belongs to an object, such as string.length().</li>
<li><strong>Substring</strong> — a smaller string taken from within a larger string.</li>
<li><strong>C-style string</strong> — an array of characters ending with a null character \0.</li>
<li><strong>Null character</strong> — a special character that marks the end of a C-style string.</li>
<li><strong>Validation</strong> — checking that user input meets expected rules.</li>
</ul>

<h2>Summary</h2>
<p>C++ strings provide powerful tools for working with text, including length checks, substrings, and concatenation. C-style strings still appear in low-level code, but <code>std::string</code> is safer and easier for beginners. String operations are essential for processing names, phone numbers, codes, and messages.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/cpp/cpp_strings.asp">W3Schools — C++ Strings</a></li>
<li><a href="https://www.w3schools.com/cpp/cpp_strings_length.asp">W3Schools — C++ String Length</a></li>
<li><a href="https://learn.microsoft.com/en-us/cpp/standard-library/basic-string-class">Microsoft Learn — std::string</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module4Quiz(): array
    {
        return [
            'title' => 'Module 4 Quiz: Functions, Arrays, and Strings',
            'description' => 'Test your understanding of functions, parameters, return values, arrays, and strings.',
            'time_limit_minutes' => 25,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does the void keyword mean in a function declaration?',
                    'explanation' => 'void means the function does not return a value to its caller.',
                    'options' => [
                        ['text' => 'The function returns an integer', 'is_correct' => false],
                        ['text' => 'The function returns a string', 'is_correct' => false],
                        ['text' => 'The function returns nothing', 'is_correct' => true],
                        ['text' => 'The function is private', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the index of the first element of an array in C++?',
                    'explanation' => 'C++ arrays are zero-indexed, so the first element is at index 0.',
                    'options' => [
                        ['text' => '0', 'is_correct' => true],
                        ['text' => '1', 'is_correct' => false],
                        ['text' => '-1', 'is_correct' => false],
                        ['text' => 'Depends on the array size', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which statement correctly calls a function named greet that takes a string parameter?',
                    'explanation' => 'You call a function by writing its name followed by parentheses containing the argument.',
                    'options' => [
                        ['text' => 'greet[] "Alice";', 'is_correct' => false],
                        ['text' => 'greet("Alice");', 'is_correct' => true],
                        ['text' => 'greet string "Alice";', 'is_correct' => false],
                        ['text' => 'call greet("Alice");', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'How do you obtain the number of characters in a std::string named message?',
                    'explanation' => 'The length() and size() member functions both return the number of characters.',
                    'options' => [
                        ['text' => 'message.count()', 'is_correct' => false],
                        ['text' => 'message.length()', 'is_correct' => true],
                        ['text' => 'length(message)', 'is_correct' => false],
                        ['text' => 'message.size', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A function can accept more than one parameter.',
                    'explanation' => 'Functions can have multiple parameters separated by commas, each with its own type and name.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'C-style strings are generally easier and safer for beginners than std::string.',
                    'explanation' => 'std::string is safer and easier because it manages memory automatically.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'Which keyword sends a value back from a function to its caller? (one word)',
                    'explanation' => 'The return keyword ends the function and sends a value back to the caller.',
                    'correct_answer' => 'return',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What are parallel arrays?',
                    'explanation' => 'Parallel arrays store related data at matching indices, such as items[i] and prices[i].',
                    'options' => [
                        ['text' => 'Arrays that have the same length but unrelated data', 'is_correct' => false],
                        ['text' => 'Arrays that run at the same time', 'is_correct' => false],
                        ['text' => 'Arrays that store related data at matching indices', 'is_correct' => true],
                        ['text' => 'Arrays that are always sorted', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which symbol is used to concatenate two std::string values?',
                    'explanation' => 'The + operator joins two C++ strings together.',
                    'options' => [
                        ['text' => '&', 'is_correct' => false],
                        ['text' => '+', 'is_correct' => true],
                        ['text' => '.', 'is_correct' => false],
                        ['text' => ',', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module5Lessons(): array
    {
        return [
            [
                'title' => '5.1 Pointers Explained Gently',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what a pointer is, declare and use pointers safely, understand the address-of and dereference operators, and see why pointers are important without being overwhelmed by them.</p>

<h2>What Is a Pointer?</h2>
<p>A pointer is a variable that stores a memory address. Think of memory as a long street of houses, each with a unique address. A normal variable is like the contents of a house. A pointer is like a piece of paper that tells you the house number. Instead of carrying the contents, you carry the address.</p>
<p>Many beginners fear pointers, but the core idea is simple. A pointer points to where something lives in memory. You can use the pointer to read or modify that thing.</p>

<h2>Declaring and Using Pointers</h2>
<p>A pointer is declared by placing an asterisk before the variable name.</p>
<pre><code>int age = 25;
int* ptr = &age;  // ptr stores the address of age</code></pre>
<p>The <code>&</code> operator gives the address of a variable. The <code>*</code> operator, when used with a pointer, gives the value stored at that address. This is called dereferencing.</p>
<pre><code>std::cout &lt;&lt; "Address: " &lt;&lt; ptr &lt;&lt; std::endl;
std::cout &lt;&lt; "Value: " &lt;&lt; *ptr &lt;&lt; std::endl;</code></pre>
<p>If you change <code>*ptr</code>, you are actually changing <code>age</code> because <code>ptr</code> points to <code>age</code>.</p>

<h2>Why Pointers Matter</h2>
<p>Pointers are essential in C++ because they allow efficient handling of large data, dynamic memory allocation, and interaction with hardware. Operating systems, embedded devices, and game engines rely heavily on pointers. For now, you do not need to memorise every detail, but you should recognise pointer syntax when you see it.</p>

<h2>Worked Example: Swapping Two Values</h2>
<p>Pointers let a function modify variables outside itself. Here is a swap function:</p>
<pre><code>void swap(int* a, int* b) {
    int temp = *a;
    *a = *b;
    *b = temp;
}

int main() {
    int x = 5, y = 10;
    swap(&x, &y);
    std::cout &lt;&lt; "x: " &lt;&lt; x &lt;&lt; ", y: " &lt;&lt; y &lt;&lt; std::endl;
    return 0;
}</code></pre>
<p>After calling <code>swap(&x, &y)</code>, the values of <code>x</code> and <code>y</code> are exchanged. This works because the function receives addresses, not copies.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Declare an int variable and a pointer to it. Print both the address and the value.</li>
<li>Use a pointer to change the value of a variable from main.</li>
<li>Write a function that doubles a number by accepting a pointer parameter.</li>
<li>Read about the null pointer and explain why initialising unused pointers to nullptr is a good habit.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Pointer</strong> — a variable that stores the memory address of another variable.</li>
<li><strong>Address-of operator (&)</strong> — returns the memory address of a variable.</li>
<li><strong>Dereference operator (*)</strong> — accesses the value stored at the address held by a pointer.</li>
<li><strong>nullptr</strong> — a special value meaning the pointer points to nothing.</li>
<li><strong>Pass by pointer</strong> — passing an address to a function so it can modify the original variable.</li>
</ul>

<h2>Summary</h2>
<p>Pointers store memory addresses and allow functions to work directly with variables in other parts of a program. They are powerful but require care. Initialise pointers properly, avoid dereferencing null pointers, and remember that pointers are just addresses with a type.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/cpp/cpp_pointers.asp">W3Schools — C++ Pointers</a></li>
<li><a href="https://www.freecodecamp.org/news/learn-c-with-free-31-hour-course/">freeCodeCamp — C++ Tutorial</a></li>
<li><a href="https://learn.microsoft.com/en-us/cpp/cpp/pointers-cpp">Microsoft Learn — C++ Pointers</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.2 Classes and Objects: A School Register',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to define a simple C++ class, create objects from the class, access member variables, and explain how object-oriented programming helps model real-world things.</p>

<h2>What Is Object-Oriented Programming?</h2>
<p>Object-oriented programming, or OOP, is a way of organising code around objects that represent real-world things. A student, a product, a bank account, and a ZESCO meter can all be objects. Each object has data, called attributes, and behaviours, called methods. OOP helps you write code that mirrors the way you think about the world.</p>

<h2>Defining a Class</h2>
<p>A class is a blueprint for creating objects. Here is a simple Student class:</p>
<pre><code>#include &lt;iostream&gt;
#include &lt;string&gt;

class Student {
public:
    std::string name;
    int age;
    std::string course;
};</code></pre>
<p>The <code>public:</code> label means the variables can be accessed from outside the class. A class definition ends with a semicolon.</p>

<h2>Creating and Using Objects</h2>
<p>Once you have a class, you can create objects and use the dot operator to access their members.</p>
<pre><code>int main() {
    Student s1;
    s1.name = "Mercy Mumba";
    s1.age = 20;
    s1.course = "C++ Programming";

    std::cout &lt;&lt; s1.name &lt;&lt; " is studying " &lt;&lt; s1.course &lt;&lt; std::endl;
    return 0;
}</code></pre>
<p>Each object has its own copy of the attributes. You can create many students from the same Student class.</p>

<h2>Worked Example: A Mini School Register</h2>
<p>Suppose you want to store records for a small class. You can create an array of Student objects.</p>
<pre><code>const int CLASS_SIZE = 3;
Student classList[CLASS_SIZE];

classList[0].name = "John Banda";
classList[0].age = 19;
classList[0].course = "C++ Programming";

classList[1].name = "Mary Zulu";
classList[1].age = 21;
classList[1].course = "Java Programming";

classList[2].name = "Peter Chilekwa";
classList[2].age = 20;
classList[2].course = "C++ Programming";

for (int i = 0; i &lt; CLASS_SIZE; i++) {
    std::cout &lt;&lt; classList[i].name &lt;&lt; ", " &lt;&lt; classList[i].age &lt;&lt; std::endl;
}</code></pre>
<p>This mini register stores names, ages, and courses. In the next lesson, you will learn how to protect this data using private fields and public methods.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Define a Product class with attributes for name, price, and quantity.</li>
<li>Create three Product objects and print their details.</li>
<li>Create an array of Product objects representing items in a shop.</li>
<li>Calculate the total value of all products in the array.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Class</strong> — a blueprint that defines the attributes and methods of objects.</li>
<li><strong>Object</strong> — a specific instance created from a class.</li>
<li><strong>Attribute</strong> — a data member of a class, such as name or age.</li>
<li><strong>Method</strong> — a function defined inside a class.</li>
<li><strong>Member</strong> — an attribute or method belonging to a class.</li>
</ul>

<h2>Summary</h2>
<p>Classes and objects let you model real-world things in code. A class is a blueprint, and an object is a specific instance. By grouping related data together, classes make programs easier to understand and extend. The school register example shows how classes can represent students cleanly.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/cpp/cpp_classes.asp">W3Schools — C++ Classes</a></li>
<li><a href="https://www.w3schools.com/cpp/cpp_class_methods.asp">W3Schools — C++ Class Methods</a></li>
<li><a href="https://www.freecodecamp.org/news/learn-c-with-free-31-hour-course/">freeCodeCamp — C++ OOP</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.3 Constructors, Getters, and Setters',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to write constructors to initialise objects, use private fields with public getter and setter methods, and explain how encapsulation protects data.</p>

<h2>Encapsulation</h2>
<p>Encapsulation is the practice of hiding internal details and exposing only what is necessary. In C++, you make fields private and provide public methods to access them. This prevents outside code from putting invalid data into an object.</p>

<h2>Private and Public Members</h2>
<pre><code>class Student {
private:
    std::string name;
    int age;

public:
    void setName(std::string newName) {
        if (!newName.empty()) {
            name = newName;
        }
    }

    std::string getName() {
        return name;
    }

    void setAge(int newAge) {
        if (newAge &gt;= 0 && newAge &lt;= 120) {
            age = newAge;
        }
    }

    int getAge() {
        return age;
    }
};</code></pre>
<p>Now code outside the class cannot change <code>name</code> or <code>age</code> directly. It must use the setter methods, which can include validation.</p>

<h2>Constructors</h2>
<p>A constructor is a special method that runs automatically when an object is created. It has the same name as the class and no return type.</p>
<pre><code>class Student {
public:
    Student(std::string n, int a) {
        name = n;
        age = a;
    }
    // ... getters and setters
};

Student s1("John Banda", 19);</code></pre>
<p>Constructors make sure objects start in a valid state. Without a constructor, you would have to remember to set every field manually.</p>

<h2>Worked Example: Validated Product Data</h2>
<p>Below is a Product class that refuses negative prices.</p>
<pre><code>class Product {
private:
    std::string name;
    double price;

public:
    Product(std::string n, double p) {
        name = n;
        setPrice(p);
    }

    void setPrice(double p) {
        if (p &gt;= 0) {
            price = p;
        }
    }

    double getPrice() {
        return price;
    }

    std::string getName() {
        return name;
    }
};</code></pre>
<p>This class ensures that no product can accidentally have a negative price.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Rewrite your Student class to use private fields, getters, setters, and a constructor.</li>
<li>Add validation so age cannot be negative or greater than 120.</li>
<li>Create a Product class with a constructor and a setter that rejects negative quantities.</li>
<li>Write a main function that creates objects and demonstrates the validation.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Encapsulation</strong> — hiding internal details and exposing only controlled access.</li>
<li><strong>Private</strong> — an access modifier that restricts access to members within the class.</li>
<li><strong>Public</strong> — an access modifier that allows access from outside the class.</li>
<li><strong>Constructor</strong> — a special method that initialises an object when it is created.</li>
<li><strong>Getter/setter</strong> — methods that read or modify private fields safely.</li>
</ul>

<h2>Summary</h2>
<p>Constructors initialise objects automatically, while getters and setters provide controlled access to private fields. Encapsulation protects data from invalid values and makes classes easier to maintain. These concepts are at the heart of object-oriented programming.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/cpp/cpp_constructors.asp">W3Schools — C++ Constructors</a></li>
<li><a href="https://www.w3schools.com/cpp/cpp_access_specifiers.asp">W3Schools — C++ Access Specifiers</a></li>
<li><a href="https://learn.microsoft.com/en-us/cpp/cpp/constructors-cpp">Microsoft Learn — C++ Constructors</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.4 Inheritance and Vectors Briefly',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe inheritance, create a simple derived class, understand the purpose of std::vector, and know where to learn more about these advanced topics.</p>

<h2>What Is Inheritance?</h2>
<p>Inheritance allows you to create a new class based on an existing class. The new class inherits the attributes and methods of the existing class and can add its own. Inheritance is useful when you have similar kinds of objects, such as students and teachers, or savings accounts and current accounts.</p>

<h2>A Simple Example</h2>
<p>Suppose you have a Person class with name and age. You can create a Student class that inherits from Person and adds a course field.</p>
<pre><code>class Person {
protected:
    std::string name;
    int age;

public:
    Person(std::string n, int a) {
        name = n;
        age = a;
    }

    std::string getName() { return name; }
};

class Student : public Person {
private:
    std::string course;

public:
    Student(std::string n, int a, std::string c) : Person(n, a) {
        course = c;
    }

    std::string getCourse() { return course; }
};</code></pre>
<p><code>Student</code> inherits <code>name</code>, <code>age</code>, and <code>getName()</code> from <code>Person</code>. The colon after <code>Student</code> declares the inheritance, and the base constructor is called in the initialiser list.</p>

<h2>What Is a Vector?</h2>
<p>A vector is a dynamic array provided by the C++ Standard Library. Unlike plain arrays, vectors can grow and shrink at runtime. They also keep track of their own size.</p>
<pre><code>#include &lt;vector&gt;

std::vector&lt;std::string&gt; names;
names.push_back("John");
names.push_back("Mary");

for (size_t i = 0; i &lt; names.size(); i++) {
    std::cout &lt;&lt; names[i] &lt;&lt; std::endl;
}</code></pre>
<p>Vectors are convenient when you do not know in advance how many items you need. They handle memory for you, which reduces the chance of errors.</p>

<h2>When to Learn More</h2>
<p>Inheritance and vectors are large topics. This lesson gives you a gentle introduction. As you continue learning C++, you will encounter polymorphism, virtual functions, iterators, and algorithms. For now, focus on understanding that classes can be extended and that vectors are safer than raw arrays for growing collections.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a Person class with name and age, then create a Teacher class that inherits from Person and adds a subject.</li>
<li>Create a vector of strings and add five student names. Print the names using a loop.</li>
<li>Try adding and removing elements from the vector using push_back and pop_back.</li>
<li>Compare the size of a vector to the fixed size of a plain array.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Inheritance</strong> — creating a new class from an existing class.</li>
<li><strong>Base class</strong> — the existing class that is inherited from.</li>
<li><strong>Derived class</strong> — the new class that inherits from a base class.</li>
<li><strong>Vector</strong> — a dynamic array that can change size at runtime.</li>
<li><strong>push_back</strong> — a vector method that adds an element to the end.</li>
</ul>

<h2>Summary</h2>
<p>Inheritance lets you reuse code by building new classes from existing ones. Vectors provide flexible, dynamic arrays that grow with your data. Both topics are important for larger C++ programs, and this lesson gives you a foundation to explore them further.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/cpp/cpp_inheritance.asp">W3Schools — C++ Inheritance</a></li>
<li><a href="https://www.w3schools.com/cpp/cpp_vectors.asp">W3Schools — C++ Vectors</a></li>
<li><a href="https://learn.microsoft.com/en-us/cpp/standard-library/vector-class">Microsoft Learn — std::vector</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module5Quiz(): array
    {
        return [
            'title' => 'Module 5 Quiz: Pointers and Object-Oriented Programming',
            'description' => 'Test your understanding of pointers, classes, objects, constructors, encapsulation, and inheritance.',
            'time_limit_minutes' => 25,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does a pointer store?',
                    'explanation' => 'A pointer stores the memory address of another variable.',
                    'options' => [
                        ['text' => 'A string value', 'is_correct' => false],
                        ['text' => 'A memory address', 'is_correct' => true],
                        ['text' => 'A function result', 'is_correct' => false],
                        ['text' => 'A boolean flag', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which operator returns the address of a variable?',
                    'explanation' => 'The address-of operator & returns the memory address of a variable.',
                    'options' => [
                        ['text' => '*', 'is_correct' => false],
                        ['text' => '&', 'is_correct' => true],
                        ['text' => '%', 'is_correct' => false],
                        ['text' => '->', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the relationship between a class and an object?',
                    'explanation' => 'A class is a blueprint, and an object is an instance created from that blueprint.',
                    'options' => [
                        ['text' => 'A class is a type of object', 'is_correct' => false],
                        ['text' => 'An object is a blueprint for creating classes', 'is_correct' => false],
                        ['text' => 'A class is a blueprint; an object is an instance', 'is_correct' => true],
                        ['text' => 'They are the same thing', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which keyword makes a field accessible only within its own class?',
                    'explanation' => 'The private access modifier restricts access to the class in which the field is declared.',
                    'options' => [
                        ['text' => 'public', 'is_correct' => false],
                        ['text' => 'private', 'is_correct' => true],
                        ['text' => 'protected', 'is_correct' => false],
                        ['text' => 'static', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A constructor has the same name as the class and no return type.',
                    'explanation' => 'Constructors are special methods named after the class. They do not have a return type, not even void.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Encapsulation means making all fields public so any code can change them.',
                    'explanation' => 'Encapsulation hides internal details and exposes only controlled access, usually through private fields and public methods.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'Which keyword is used to create a new object from a class? (one word)',
                    'explanation' => 'The new keyword allocates memory and calls the constructor to create a new object on the heap.',
                    'correct_answer' => 'new',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is a vector in C++?',
                    'explanation' => 'std::vector is a dynamic array that can grow and shrink at runtime.',
                    'options' => [
                        ['text' => 'A fixed-size array', 'is_correct' => false],
                        ['text' => 'A mathematical direction', 'is_correct' => false],
                        ['text' => 'A dynamic array from the Standard Library', 'is_correct' => true],
                        ['text' => 'A type of pointer', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which method adds an element to the end of a std::vector?',
                    'explanation' => 'push_back adds an element to the end of a vector.',
                    'options' => [
                        ['text' => 'add()', 'is_correct' => false],
                        ['text' => 'append()', 'is_correct' => false],
                        ['text' => 'push_back()', 'is_correct' => true],
                        ['text' => 'insert_end()', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module6Lessons(): array
    {
        return [
            [
                'title' => '6.1 Reading and Writing Files',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to open text files for reading and writing, write data to a file, read data back from a file, and handle simple file errors gracefully.</p>

<h2>Why Files Matter</h2>
<p>Programs that only store data in memory lose everything when they close. Files let you save data permanently. A shop's sales records, a school's student register, and a farmer's irrigation log all need to survive after the program ends. C++ provides the <code>fstream</code> library for file operations.</p>

<h2>Writing to a File</h2>
<p>To write to a file, use an <code>ofstream</code> object. The file is created if it does not exist, or overwritten if it does.</p>
<pre><code>#include &lt;iostream&gt;
#include &lt;fstream&gt;

int main() {
    std::ofstream outFile("sales.txt");

    if (!outFile) {
        std::cout &lt;&lt; "Could not open file for writing." &lt;&lt; std::endl;
        return 1;
    }

    outFile &lt;&lt; "Tomatoes 12.50 10" &lt;&lt; std::endl;
    outFile &lt;&lt; "Onions 8.00 5" &lt;&lt; std::endl;
    outFile.close();

    std::cout &lt;&lt; "Sales saved." &lt;&lt; std::endl;
    return 0;
}</code></pre>
<p>Always check whether the file opened successfully. If the disk is full or the path is invalid, <code>outFile</code> will be false.</p>

<h2>Reading from a File</h2>
<p>To read from a file, use an <code>ifstream</code> object. You can read values with the <code>&gt;&gt;</code> operator, just like with <code>std::cin</code>.</p>
<pre><code>std::ifstream inFile("sales.txt");
if (!inFile) {
    std::cout &lt;&lt; "Could not open file for reading." &lt;&lt; std::endl;
    return 1;
}

std::string item;
double price;
int quantity;

while (inFile &gt;&gt; item &gt;&gt; price &gt;&gt; quantity) {
    std::cout &lt;&lt; item &lt;&lt; " " &lt;&lt; quantity &lt;&lt; " @ ZMW " &lt;&lt; price &lt;&lt; std::endl;
}

inFile.close();</code></pre>
<p>The loop continues as long as there is data to read. When the end of the file is reached, the read fails and the loop stops.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write a program that saves three product names and prices to a file.</li>
<li>Write another program that reads the file and prints the contents.</li>
<li>Modify the reader to calculate the total value of all products.</li>
<li>Experiment with opening a file that does not exist. Observe the error handling.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>fstream</strong> — the C++ library for file input and output.</li>
<li><strong>ofstream</strong> — an output file stream used for writing to files.</li>
<li><strong>ifstream</strong> — an input file stream used for reading from files.</li>
<li><strong>Open</strong> — to create a connection between a program and a file.</li>
<li><strong>Close</strong> — to release the connection to a file when finished.</li>
</ul>

<h2>Summary</h2>
<p>Files allow programs to save data permanently. Use <code>ofstream</code> to write, <code>ifstream</code> to read, and always check that the file opened successfully. File handling is essential for real-world applications such as sales trackers, registers, and logs.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/cpp/cpp_files.asp">W3Schools — C++ Files</a></li>
<li><a href="https://www.freecodecamp.org/news/learn-c-with-free-31-hour-course/">freeCodeCamp — C++ File I/O</a></li>
<li><a href="https://learn.microsoft.com/en-us/cpp/standard-library/fstream">Microsoft Learn — fstream</a></li>
</ul>
HTML,
            ],
            [
                'title' => '6.2 Console Project: Market Sales Tracker',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to build a complete console application that records sales, saves them to a file, loads them when the program starts, and prints a simple report.</p>

<h2>The Project Brief</h2>
<p>A market vendor wants a simple sales tracker that runs on a college computer. The program should:</p>
<ul>
<li>Show a menu to add a sale, view all sales, and save and exit.</li>
<li>Store each sale with an item name, quantity, and unit price.</li>
<li>Save sales to a text file so they are not lost when the program closes.</li>
<li>Load sales from the text file when the program starts.</li>
<li>Print a report showing total sales and total revenue.</li>
</ul>

<h2>Project Structure</h2>
<p>The project uses a Sale struct, a vector of sales, and simple file operations.</p>
<pre><code>#include &lt;iostream&gt;
#include &lt;fstream&gt;
#include &lt;vector&gt;
#include &lt;string&gt;
#include &lt;iomanip&gt;

struct Sale {
    std::string item;
    int quantity;
    double unitPrice;
};

void saveSales(const std::vector&lt;Sale&gt;& sales, const std::string& filename) {
    std::ofstream outFile(filename);
    if (!outFile) return;
    for (const auto& sale : sales) {
        outFile &lt;&lt; sale.item &lt;&lt; " " &lt;&lt; sale.quantity &lt;&lt; " " &lt;&lt; sale.unitPrice &lt;&lt; std::endl;
    }
    outFile.close();
}

std::vector&lt;Sale&gt; loadSales(const std::string& filename) {
    std::vector&lt;Sale&gt; sales;
    std::ifstream inFile(filename);
    if (!inFile) return sales;

    Sale s;
    while (inFile &gt;&gt; s.item &gt;&gt; s.quantity &gt;&gt; s.unitPrice) {
        sales.push_back(s);
    }
    inFile.close();
    return sales;
}</code></pre>
<p>A struct is similar to a class but its members are public by default. It is a convenient way to group related data.</p>

<h2>Menu and Reporting</h2>
<pre><code>int main() {
    std::vector&lt;Sale&gt; sales = loadSales("market_sales.txt");
    int choice;

    do {
        std::cout &lt;&lt; "\n1. Add Sale\n2. View Sales\n3. Save and Exit\nChoice: ";
        std::cin &gt;&gt; choice;

        if (choice == 1) {
            Sale s;
            std::cout &lt;&lt; "Item: ";
            std::cin &gt;&gt; s.item;
            std::cout &lt;&lt; "Quantity: ";
            std::cin &gt;&gt; s.quantity;
            std::cout &lt;&lt; "Unit price: ";
            std::cin &gt;&gt; s.unitPrice;
            sales.push_back(s);
        } else if (choice == 2) {
            double totalRevenue = 0.0;
            std::cout &lt;&lt; std::fixed &lt;&lt; std::setprecision(2);
            for (const auto& sale : sales) {
                double lineTotal = sale.quantity * sale.unitPrice;
                totalRevenue += lineTotal;
                std::cout &lt;&lt; sale.item &lt;&lt; " x" &lt;&lt; sale.quantity
                          &lt;&lt; " = ZMW " &lt;&lt; lineTotal &lt;&lt; std::endl;
            }
            std::cout &lt;&lt; "Total revenue: ZMW " &lt;&lt; totalRevenue &lt;&lt; std::endl;
        }
    } while (choice != 3);

    saveSales(sales, "market_sales.txt");
    std::cout &lt;&lt; "Sales saved. Goodbye." &lt;&lt; std::endl;
    return 0;
}</code></pre>
<p>This menu repeats until the user chooses to exit. Sales are loaded at the start and saved at the end. The report calculates revenue per line and a grand total.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Build the Market Sales Tracker project step by step.</li>
<li>Add a fourth menu option to delete the last sale.</li>
<li>Add a date field to each sale and include it in the file and report.</li>
<li>Test the program by adding sales, exiting, reopening, and confirming that data was saved.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Struct</strong> — a simple way to group related variables; members are public by default.</li>
<li><strong>Vector</strong> — a dynamic array that grows as you add elements.</li>
<li><strong>Revenue</strong> — the total income from sales.</li>
<li><strong>Persistence</strong> — the ability to save data so it survives after the program closes.</li>
<li><strong>Menu-driven program</strong> — a program that presents options to the user in a loop.</li>
</ul>

<h2>Summary</h2>
<p>The Market Sales Tracker brings together structs, vectors, loops, decisions, and file handling into a single useful program. By saving data to a file, the program demonstrates persistence, which is essential for real business software.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/cpp/cpp_structs.asp">W3Schools — C++ Structs</a></li>
<li><a href="https://www.w3schools.com/cpp/cpp_files.asp">W3Schools — C++ Files</a></li>
<li><a href="https://www.w3schools.com/cpp/cpp_vectors.asp">W3Schools — C++ Vectors</a></li>
</ul>
HTML,
            ],
            [
                'title' => '6.3 Debugging Habits',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe a systematic approach to finding and fixing bugs, use simple debugging techniques such as print statements and reading error messages, and adopt habits that prevent bugs in the first place.</p>

<h2>Everyone Has Bugs</h2>
<p>Even professional programmers write code with bugs. A bug is simply a mistake that makes a program behave incorrectly. The difference between a beginner and an experienced programmer is not the absence of bugs, but the ability to find and fix them quickly. Debugging is a skill that improves with practice.</p>

<h2>Read the Error Message</h2>
<p>When your program does not compile, the compiler usually tells you exactly where the problem is. Start at the first error. Look at the line number, then look at the lines just before it. Common errors include missing semicolons, mismatched braces, undeclared variables, and incorrect function signatures.</p>

<h2>Use Print Statements</h2>
<p>When a program compiles but produces wrong output, add temporary print statements to show the values of variables at different points. This is often faster than using a debugger for small programs.</p>
<pre><code>std::cout &lt;&lt; "DEBUG: quantity = " &lt;&lt; quantity &lt;&lt; std::endl;
std::cout &lt;&lt; "DEBUG: price = " &lt;&lt; price &lt;&lt; std::endl;</code></pre>
<p>Once you find the problem, remove or comment out the debug prints before submitting the program.</p>

<h2>Isolate the Problem</h2>
<p>If a large program is failing, try to reproduce the bug in a smaller program. Comment out half the code and see if the bug still happens. This process, called bisection, helps you narrow down the cause.</p>

<h2>Prevent Bugs with Good Habits</h2>
<ul>
<li><strong>Save and compile often.</strong> The sooner you catch an error, the easier it is to fix.</li>
<li><strong>Format your code consistently.</strong> Aligned braces make missing braces obvious.</li>
<li><strong>Use meaningful names.</strong> A variable named <code>customerTotal</code> is clearer than <code>x</code>.</li>
<li><strong>Initialise variables.</strong> Uninitialised variables contain unpredictable values.</li>
<li><strong>Test edge cases.</strong> Try zero, negative numbers, empty input, and maximum values.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Take a working program and introduce one bug deliberately. See how long it takes you to find it.</li>
<li>Add temporary debug prints to a program that gives wrong output.</li>
<li>Find a missing brace in a program by carefully matching opening and closing braces.</li>
<li>List three habits you will use to reduce bugs in your own code.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Bug</strong> — an error in a program that causes incorrect behaviour.</li>
<li><strong>Debugging</strong> — the process of finding and fixing bugs.</li>
<li><strong>Compiler error</strong> — a message explaining why code cannot be compiled.</li>
<li><strong>Runtime error</strong> — an error that occurs while the program is running.</li>
<li><strong>Edge case</strong> — an unusual input that tests the boundaries of a program.</li>
</ul>

<h2>Summary</h2>
<p>Debugging is a normal and important part of programming. Read error messages carefully, use print statements to inspect values, isolate problems in smaller programs, and develop habits that prevent bugs. Good debuggers are not born; they are made through patient practice.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.freecodecamp.org/news/what-is-debugging-how-to-debug-code/">freeCodeCamp — How to Debug Code</a></li>
<li><a href="https://www.w3schools.com/cpp/cpp_errors.asp">W3Schools — C++ Errors</a></li>
<li><a href="https://learn.microsoft.com/en-us/visualstudio/debugger/">Microsoft Learn — Debugging</a></li>
</ul>
HTML,
            ],
            [
                'title' => '6.4 Paths After C++',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe several directions you can take after completing this course, identify resources for continuing your learning, and create a personal plan for the next steps in your programming journey.</p>

<h2>Where Can C++ Take You?</h2>
<p>Completing this course is a real achievement. You now understand variables, control flow, functions, arrays, pointers, classes, file handling, and a complete project. These skills open several doors. Here are some natural next steps for a Zambian learner.</p>

<h2>Arduino and Embedded Systems</h2>
<p>Arduino boards are small, affordable computers used to control lights, motors, sensors, and displays. The Arduino language is based on C++, so almost everything you have learned applies directly. With Arduino, you can build automatic chicken coop doors, soil moisture sensors for farms, security alarms, or simple robots. This path is excellent for entrepreneurs and tinkerers.</p>

<h2>Competitive Programming</h2>
<p>Competitive programming contests reward speed, accuracy, and algorithmic thinking. C++ is the most popular language in contests because it is fast and has a rich standard library. Websites such as Codeforces, HackerRank, and LeetCode offer free practice problems. Strong competitive programmers can win scholarships, travel, and job opportunities.</p>

<h2>Game Development</h2>
<p>Many game engines, including Unreal Engine, use C++. If you enjoy graphics and interactive software, learning a game engine is a logical next step. Start with small 2D games before attempting large 3D projects.</p>

<h2>Systems and Backend Development</h2>
<p>C++ is used in operating systems, databases, network servers, and financial systems. If you prefer large-scale software, study data structures, algorithms, and software engineering principles. This path often leads to remote work or employment with banks, mining companies, and telecoms.</p>

<h2>Keep Learning Locally</h2>
<p>You do not need to leave Zambia to keep learning. Join local study groups, PTA WhatsApp groups for coding, or college clubs. Share your projects with classmates. Teach what you have learned, because teaching is one of the best ways to deepen your own understanding.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose one path from this lesson that interests you most.</li>
<li>Find one free online resource for that path and bookmark it.</li>
<li>Write three goals for the next three months, such as "Build an Arduino project" or "Solve 50 programming problems."</li>
<li>Share your goal with one classmate or mentor who can encourage you.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Embedded system</strong> — a small computer inside another device.</li>
<li><strong>Competitive programming</strong> — solving algorithmic problems under time constraints.</li>
<li><strong>Algorithm</strong> — a step-by-step procedure for solving a problem.</li>
<li><strong>Game engine</strong> — software that provides tools for building games.</li>
<li><strong>Backend</strong> — the server-side part of an application that processes data.</li>
</ul>

<h2>Summary</h2>
<p>C++ is a foundation, not a destination. From here you can explore Arduino, competitive programming, game development, or systems programming. The habits you have built, such as writing functions, testing edge cases, and reading errors, will serve you in every direction. Keep practising, keep building, and keep learning.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.arduino.cc/en/Guide/HomePage">Arduino — Getting Started</a></li>
<li><a href="https://www.hackerrank.com/">HackerRank — Practice Coding</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Free Coding Curriculum</a></li>
<li><a href="https://www.w3schools.com/cpp/">W3Schools — C++ Tutorial</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module6Quiz(): array
    {
        return [
            'title' => 'Module 6 Quiz: Files, Project, and Next Steps',
            'description' => 'Test your understanding of file I/O, the Market Sales Tracker project, debugging, and paths after C++.',
            'time_limit_minutes' => 25,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which stream type is used to write data to a file in C++?',
                    'explanation' => 'ofstream is an output file stream used for writing to files.',
                    'options' => [
                        ['text' => 'ifstream', 'is_correct' => false],
                        ['text' => 'ofstream', 'is_correct' => true],
                        ['text' => 'iostream', 'is_correct' => false],
                        ['text' => 'stringstream', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is file handling important in the Market Sales Tracker project?',
                    'explanation' => 'File handling saves data so it is not lost when the program closes.',
                    'options' => [
                        ['text' => 'It makes the program compile faster', 'is_correct' => false],
                        ['text' => 'It allows data to persist between program runs', 'is_correct' => true],
                        ['text' => 'It prevents all bugs', 'is_correct' => false],
                        ['text' => 'It reduces the need for variables', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which C++ feature groups related variables with public default access?',
                    'explanation' => 'A struct groups related data and its members are public by default.',
                    'options' => [
                        ['text' => 'class', 'is_correct' => false],
                        ['text' => 'struct', 'is_correct' => true],
                        ['text' => 'vector', 'is_correct' => false],
                        ['text' => 'namespace', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the first thing you should do when a program fails to compile?',
                    'explanation' => 'Start with the first compiler error and read the surrounding lines carefully.',
                    'options' => [
                        ['text' => 'Delete the entire program', 'is_correct' => false],
                        ['text' => 'Look at the first error message', 'is_correct' => true],
                        ['text' => 'Rewrite all functions', 'is_correct' => false],
                        ['text' => 'Ignore it and run again', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Arduino programming is based on C++.',
                    'explanation' => 'The Arduino language is built on C/C++ syntax and concepts.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'ifstream is used to write data to a file.',
                    'explanation' => 'ifstream is an input file stream used for reading, not writing.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'Which method adds an element to the end of a std::vector? (one word)',
                    'explanation' => 'push_back adds an element to the end of a vector.',
                    'correct_answer' => 'push_back',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which operator reads data from an ifstream object?',
                    'explanation' => 'The >> operator reads formatted data from an input stream, including ifstream.',
                    'options' => [
                        ['text' => '&lt;&lt;', 'is_correct' => false],
                        ['text' => '&gt;&gt;', 'is_correct' => true],
                        ['text' => '%', 'is_correct' => false],
                        ['text' => '=', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a good debugging habit?',
                    'explanation' => 'Reading error messages, testing edge cases, and using print statements are all good debugging habits.',
                    'options' => [
                        ['text' => 'Ignoring compiler warnings', 'is_correct' => false],
                        ['text' => 'Only testing with perfect input', 'is_correct' => false],
                        ['text' => 'Using print statements to inspect values', 'is_correct' => true],
                        ['text' => 'Writing all code before compiling', 'is_correct' => false],
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
            'title' => 'Mid-Course C++ Console Program: ZESCO Tariff Calculator',
            'description' => 'Build a console C++ program that calculates the cost of ZESCO units using tiered pricing, applying the variables, decisions, loops, and arithmetic skills from Modules 1 to 3.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Open Code::Blocks or VS Code and create a new C++ file named "ZescoTariffCalculator.cpp" in your CppPractice folder.
Step 2: Declare constants for three pricing tiers: first 100 units at K1.50 each, units 101-200 at K2.00 each, and units above 200 at K2.80 each.
Step 3: Ask the user to enter the number of units they want to buy.
Step 4: Use if-else statements to calculate the total cost correctly for each tier. Make sure you only charge the higher rate for units within that tier.
Step 5: Display the number of units and the total cost formatted to two decimal places with the ZMW symbol.
Step 6: Add error handling so that negative unit values are rejected with a clear message.
Step 7: Test your program with at least five values: 0, 50, 100, 150, 250, and 500 units. Take screenshots of your code and the output, or submit the .cpp file with a brief explanation of how your program works.
INSTRUCTIONS,
            'max_points' => 100,
            'passing_points' => 50,
            'due_date' => null,
            'allow_late_submission' => 1,
            'allowed_file_types' => 'pdf,doc,docx,jpg,png',
            'max_file_size_mb' => 10,
        ]);

        Assignment::create([
            'course_id' => $this->courseId,
            'lesson_id' => null,
            'title' => 'End-of-Course Project: Market Sales Tracker',
            'description' => 'Build a complete console C++ application called Market Sales Tracker that uses structs, vectors, file handling, and a menu to manage sales for a small market business.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Create a new folder named "MarketSalesTracker" inside your CppPractice folder.
Step 2: Create a struct named Sale with fields for item name, quantity, and unit price.
Step 3: Write a function loadSales that reads sales from a text file named "market_sales.txt" and returns a vector of Sale objects. If the file does not exist, return an empty vector.
Step 4: Write a function saveSales that writes all sales from the vector to "market_sales.txt", one sale per line, with the fields separated by spaces.
Step 5: In main, create a menu loop with at least three options: add a sale, view all sales with total revenue, and save and exit.
Step 6: Implement the add-sale option by reading item name, quantity, and unit price from the user and adding a Sale to the vector.
Step 7: Implement the view-sales option by looping through the vector, printing each sale, and calculating the total revenue. Format money to two decimal places.
Step 8: Test the program by adding several sales, exiting, reopening the program, and confirming that the saved data loads correctly.
Step 9: Submit all .cpp source files, the market_sales.txt file from a test run, and screenshots or a short video demonstrating the program. Include a README note explaining how to compile and run your project.
INSTRUCTIONS,
            'max_points' => 100,
            'passing_points' => 50,
            'due_date' => null,
            'allow_late_submission' => 1,
            'allowed_file_types' => 'pdf,doc,docx,jpg,png',
            'max_file_size_mb' => 10,
        ]);
    }

    private function printSummary(): void
    {
        $this->command->info('C++ Programming content seeded successfully.');
        $this->command->info('Modules: 6 | Lessons: 24 | Quizzes: 6 | Assignments: 2');
    }
}
