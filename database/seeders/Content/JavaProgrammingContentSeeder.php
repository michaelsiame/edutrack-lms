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

class JavaProgrammingContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Java Programming')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Java Programming" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in Java Programming already has modules. Skipping content seed.');
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
                'title' => 'Module 1: Welcome to Java — Installing and Writing Your First Program',
                'description' => 'Discover why Java matters for Zambian learners, install the JDK and VS Code, and compile your first program from the command line.',
            ],
            [
                'title' => 'Module 2: Variables, Data Types, and Operators with Kwacha Calculations',
                'description' => 'Learn Java variables, primitive data types, constants, operators, and how to calculate shop bills using Kwacha and ngwee.',
            ],
            [
                'title' => 'Module 3: Control Flow — Making Decisions and Repeating Tasks',
                'description' => 'Use relational and logical operators, if-else and switch statements, loops, and build a ZESCO token purchase calculator.',
            ],
            [
                'title' => 'Module 4: Methods and Arrays',
                'description' => 'Write reusable methods, understand parameters and return values, work with arrays, and create a mini bus fare calculator.',
            ],
            [
                'title' => 'Module 5: Object-Oriented Programming — Modelling Real Businesses',
                'description' => 'Create classes and objects, model a shop or bus company, and apply encapsulation and inheritance in Java.',
            ],
            [
                'title' => 'Module 6: Collections, Exceptions, Files, and Your Final Project',
                'description' => 'Use ArrayList and HashMap, handle exceptions, read and write files, and build the Market Stock Manager console project.',
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
                'title' => '1.1 Why Java Matters for Zambian Learners',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what Java is, describe why it remains one of the most important programming languages in the world, and identify concrete job and business opportunities that Java skills open for learners in Zambia and the wider Southern African region.</p>

<h2>What Is Java?</h2>
<p>Java is a programming language and computing platform first released in 1995. It is used to build desktop applications, mobile applications, web servers, banking systems, government portals, and large enterprise software. One of Java's biggest strengths is that code written in Java can run on almost any computer or operating system without modification. This principle is often described as "write once, run anywhere."</p>
<p>Java is also an <strong>object-oriented</strong> language. Object-oriented programming helps developers organise their code into reusable pieces that mirror real-world things, such as a customer, a product, or a bus ticket. This makes Java an excellent language for learners who want to think clearly about how software models real businesses and organisations.</p>

<h2>Why Java Matters in Zambia</h2>
<p>Many Zambians assume that programming is only useful in Silicon Valley or Europe. The truth is that Java skills are in demand much closer to home. Here are some practical reasons why Java is worth your time:</p>
<ul>
<li><strong>Android smartphones dominate Zambia.</strong> Almost every mobile app you use on an Android phone is built with Java or its close relative Kotlin. If you want to create apps for farmers, market vendors, schools, or health workers, Java is the gateway.</li>
<li><strong>Banks and mobile money platforms use Java.</strong> The backend systems that process Airtel Money, MTN MoMo, and bank transactions often rely on Java because it is secure, stable, and scalable.</li>
<li><strong>Government and large organisations trust Java.</strong> Systems that handle taxes, national identification, payroll, and procurement frequently use Java because it can manage millions of records reliably.</li>
<li><strong>Remote work is growing.</strong> A Zambian developer with Java skills can work for clients in Lusaka, Johannesburg, Nairobi, London, or anywhere with an internet connection.</li>
<li><strong>Java teachers logical thinking.</strong> Even if you never become a full-time programmer, the problem-solving habits you develop in Java will help you in business analysis, finance, project management, and entrepreneurship.</li>
</ul>

<h2>Worked Example: From a Market Stall to a Mobile App</h2>
<p>Mary runs a small vegetable stall at Soweto Market in Kalomo. She writes prices and sales in a notebook, which sometimes gets wet or lost. After learning Java, she teams up with a friend who knows Android development. Together they build a simple stock-tracking app that runs on her phone. The app lets her record how many tomatoes, onions, and bunches of rape she buys each morning, how much she sells them for, and how much profit she makes. The app is built in Java. It saves her hours of arithmetic every week and helps her prove her income when she applies for a small loan.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open the web browser on your phone or college computer and search for "Java programming jobs Zambia" or "Java developer remote Africa." Read three job descriptions and note the skills they ask for.</li>
<li>List three apps or services you use that might rely on Java, such as banking apps, government websites, or Android tools.</li>
<li>Ask one person you know who works in an office, bank, or shop whether their organisation uses any computer systems that might be built with Java. Write down their answer.</li>
<li>Create a simple note in your phone or notebook titled "Why I am learning Java" and write two sentences about your personal goal.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Programming language</strong> — a set of rules and vocabulary used to write instructions that a computer can follow.</li>
<li><strong>Object-oriented</strong> — a way of organising code around objects that represent real-world things and their behaviours.</li>
<li><strong>Backend</strong> — the part of a software system that processes data and business logic, usually hidden from the user.</li>
<li><strong>Platform</strong> — the combination of hardware and software that a program runs on, such as Windows, Android, or Linux.</li>
<li><strong>Remote work</strong> — employment where you perform tasks from a location of your choice, communicating over the internet.</li>
</ul>

<h2>Summary</h2>
<p>Java is a powerful, widely used programming language that offers real opportunities for Zambian learners. It powers Android phones, banking systems, government services, and global enterprise software. Learning Java will not only prepare you for technical careers; it will also sharpen your ability to solve problems, model businesses, and create useful digital tools for your community.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/Java">MDN Web Docs — Learn Java</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Free Coding Curriculum</a></li>
<li><a href="https://www.w3schools.com/java/">W3Schools — Java Tutorial</a></li>
<li><a href="https://grow.google/">Grow with Google — Free Training</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.2 Installing JDK and VS Code on a Modest Windows Computer',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to download and install the Java Development Kit (JDK) and the Visual Studio Code editor on a modest Windows computer, configure the essential extensions for Java programming, and verify that your installation is working correctly.</p>

<h2>What You Need</h2>
<p>You do not need an expensive computer to learn Java. A modest Windows laptop or desktop with at least 4 GB of RAM, 5 GB of free disk space, and a reliable internet connection is enough for beginners. If your college computer is shared, ask permission before installing software. In many cases the college lab will already have Java installed, but it is still valuable to know how to install it yourself so you can practise at home or in an internet café.</p>

<h2>Understanding the JDK</h2>
<p>The <strong>Java Development Kit (JDK)</strong> is a bundle of tools that allows you to write, compile, and run Java programs. The JDK includes the <strong>Java compiler</strong>, which translates your human-readable code into instructions the computer can execute, and the <strong>Java Runtime Environment (JRE)</strong>, which runs those instructions. Modern JDK versions such as Java 17 or Java 21 are free for personal and educational use. For this course we recommend Java 17 because it is stable, widely used, and has many helpful features.</p>

<h2>Step-by-Step: Installing the JDK</h2>
<ol>
<li>Open your web browser and visit the official Adoptium website at <a href="https://adoptium.net">adoptium.net</a>. Adoptium provides free, community-supported JDK builds.</li>
<li>Download the latest Long-Term Support (LTS) version for Windows x64. Choose the installer (.msi) file because it is easiest for beginners.</li>
<li>Run the installer and follow the prompts. When you reach the custom setup screen, keep the default settings selected.</li>
<li>After installation, open a command prompt by pressing the Windows key, typing "cmd", and pressing Enter.</li>
<li>Type <code>java -version</code> and press Enter. You should see a message showing the installed Java version. Type <code>javac -version</code> to confirm the compiler is installed.</li>
</ol>

<h2>Installing Visual Studio Code</h2>
<p>Visual Studio Code, usually called <strong>VS Code</strong>, is a free code editor created by Microsoft. It is lightweight enough to run well on older computers and supports Java through extensions. To install it:</p>
<ol>
<li>Visit <a href="https://code.visualstudio.com">code.visualstudio.com</a> and download the Windows installer.</li>
<li>Run the installer. Accept the licence agreement and keep the default options selected.</li>
<li>During installation, you may see checkboxes for "Add to PATH" and "Open with Code." Make sure these are selected because they make daily use easier.</li>
<li>Open VS Code after installation finishes.</li>
</ol>

<h2>Adding Java Extensions</h2>
<p>VS Code needs extensions to understand Java code. Click the Extensions icon on the left sidebar (it looks like four squares with one separated). Search for "Extension Pack for Java" and install it. This pack includes tools for compiling, running, debugging, and formatting Java programs. After installation, restart VS Code.</p>

<h2>Worked Example: Verifying Your Setup</h2>
<p>After installing the JDK and VS Code, Chanda opens the command prompt and types:</p>
<pre><code>C:\Users\Chanda&gt; java -version
openjdk version "17.0.8" 2023-07-18
OpenJDK Runtime Environment Temurin-17.0.8+7
</code></pre>
<p>Then she types:</p>
<pre><code>C:\Users\Chanda&gt; javac -version
javac 17.0.8
</code></pre>
<p>Both commands return version information, so she knows Java is installed correctly. She then opens VS Code, installs the Extension Pack for Java, and creates a new folder on her Desktop called "Java Practice" to store her course files.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Check whether Java is already installed on your computer. Open the command prompt and run <code>java -version</code>. Write down the version number.</li>
<li>If Java is not installed, download and install the JDK following the steps above. If you cannot install it, ask your instructor for help or use a college computer.</li>
<li>Download and install Visual Studio Code if it is not already available.</li>
<li>Install the "Extension Pack for Java" in VS Code and restart the editor.</li>
<li>Create a folder named "JavaPractice" on your Desktop or in your documents folder. You will use it throughout this course.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>JDK (Java Development Kit)</strong> — the software package that includes the Java compiler, runtime, and libraries needed to develop Java applications.</li>
<li><strong>Compiler</strong> — a program that translates code written by a human into a form that the computer can execute.</li>
<li><strong>VS Code</strong> — a free, lightweight code editor that supports many programming languages including Java.</li>
<li><strong>Extension</strong> — an add-on that gives an editor extra features, such as Java syntax highlighting and debugging.</li>
<li><strong>Command prompt</strong> — a text-based interface in Windows where you type commands to control the computer.</li>
</ul>

<h2>Summary</h2>
<p>Installing Java and a good code editor is the first practical step in becoming a Java programmer. The JDK provides the compiler and runtime, while VS Code provides a friendly place to write your code. Once you confirm that <code>java -version</code> and <code>javac -version</code> work, and once the Java extensions are installed in VS Code, you are ready to write your first Java program.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/java/openjdk/download">Microsoft Learn — OpenJDK Downloads</a></li>
<li><a href="https://code.visualstudio.com/docs/java/java-tutorial">VS Code — Java Tutorial</a></li>
<li><a href="https://www.w3schools.com/java/java_getstarted.asp">W3Schools — Java Get Started</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.3 Your First Java Program: Hello, Kalomo!',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create a new Java file, write a simple program that displays text on the screen, understand the basic structure of a Java program, and run your program successfully from VS Code or the command line.</p>

<h2>The Structure of a Java Program</h2>
<p>Every Java program is made up of one or more <strong>classes</strong>. A class is a container for code. For now, think of it as a file that holds your instructions. Inside a class, you write <strong>methods</strong>, which are blocks of code that perform specific tasks. The special method named <code>main</code> is where the program begins executing. When Java runs your program, it looks for the <code>main</code> method and starts there.</p>

<h2>Writing Your First Program</h2>
<p>Open VS Code and create a new file named <code>HelloKalomo.java</code> inside your JavaPractice folder. Type the following code exactly as shown:</p>
<pre><code>public class HelloKalomo {
    public static void main(String[] args) {
        System.out.println("Hello, Kalomo!");
    }
}
</code></pre>
<p>Let us break this down line by line:</p>
<ul>
<li><code>public class HelloKalomo</code> declares a class named HelloKalomo. The filename must match the class name, which is why the file is called <code>HelloKalomo.java</code>.</li>
<li><code>{</code> and <code>}</code> are braces that group code together. Everything inside the outer braces belongs to the class.</li>
<li><code>public static void main(String[] args)</code> is the main method. It is the entry point of the program.</li>
<li><code>System.out.println("Hello, Kalomo!");</code> tells the computer to print the text "Hello, Kalomo!" followed by a new line.</li>
<li>The semicolon at the end of the println line is required. It marks the end of a statement.</li>
</ul>

<h2>Running the Program from VS Code</h2>
<p>VS Code makes running Java programs easy. With <code>HelloKalomo.java</code> open, look for the small play button (a triangle) near the top-right of the editor window. Click it and choose "Run Java." A terminal panel should appear at the bottom of VS Code, and you should see the output:</p>
<pre><code>Hello, Kalomo!
</code></pre>
<p>If you see this message, congratulations. You have written and run your first Java program.</p>

<h2>Worked Example: Personalising the Greeting</h2>
<p>Mike wants his program to greet his home town. He creates a file named <code>HelloZambia.java</code> and writes:</p>
<pre><code>public class HelloZambia {
    public static void main(String[] args) {
        System.out.println("Hello, Zambia!");
        System.out.println("I am learning Java at Edutrack College.");
    }
}
</code></pre>
<p>When Mike runs the program, the output is:</p>
<pre><code>Hello, Zambia!
I am learning Java at Edutrack College.
</code></pre>
<p>Each call to <code>System.out.println</code> prints on a new line. If he had used <code>System.out.print</code> instead, the second line would continue on the same line as the first.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open VS Code and create a file named <code>HelloKalomo.java</code> in your JavaPractice folder.</li>
<li>Type the program shown above, paying careful attention to braces, brackets, and the semicolon.</li>
<li>Click the Run Java button in VS Code and confirm the output.</li>
<li>Change the message to print your own name and the name of your home town or area.</li>
<li>Add a third <code>System.out.println</code> statement that prints one sentence about why you are learning Java.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Class</strong> — a container that holds related code in a Java program.</li>
<li><strong>Method</strong> — a named block of code that performs a specific task.</li>
<li><strong>main method</strong> — the special method where Java begins executing a program.</li>
<li><code>System.out.println</code> — a Java statement that prints text to the screen followed by a new line.</li>
<li><strong>Statement</strong> — a single complete instruction in a program, usually ending with a semicolon.</li>
</ul>

<h2>Summary</h2>
<p>Writing your first Java program is an important milestone. You learned that a Java program needs a class with a matching filename and a <code>main</code> method as the starting point. You used <code>System.out.println</code> to display text, and you ran the program from VS Code. These simple ideas form the foundation for every Java application you will build from now on.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_syntax.asp">W3Schools — Java Syntax</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Learn to Code</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Computer Programming</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.4 Compiling and Running from the Command Line',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to compile a Java file using <code>javac</code>, run the compiled program using <code>java</code>, understand the difference between source code and bytecode, and fix common beginner errors by reading compiler messages.</p>

<h2>Why Learn the Command Line?</h2>
<p>VS Code's Run button is convenient, but professional developers often need to compile and run programs from the command line. Understanding the command line also helps you troubleshoot problems when an editor is not available. If you work on a server in Lusaka, deploy software to the cloud, or run automated tests, the command line will be your best friend.</p>

<h2>Source Code and Bytecode</h2>
<p>When you write a Java program, you create a text file called <strong>source code</strong>. The file extension is <code>.java</code>. Humans can read source code, but computers need a translated version. The Java compiler, <code>javac</code>, translates your source code into <strong>bytecode</strong>, which is stored in a file with the extension <code>.class</code>. The Java runtime, <code>java</code>, then executes the bytecode. This two-step process is what makes Java portable across different machines.</p>

<h2>Compiling a Java File</h2>
<p>Open the command prompt and navigate to the folder that contains your Java file. For example, if your <code>HelloKalomo.java</code> file is on the Desktop inside a folder called JavaPractice, type:</p>
<pre><code>cd Desktop\JavaPractice
</code></pre>
<p>Then compile the file with:</p>
<pre><code>javac HelloKalomo.java
</code></pre>
<p>If the compilation succeeds, the command prompt returns to the prompt with no message. A new file named <code>HelloKalomo.class</code> appears in the same folder. This is the bytecode file.</p>

<h2>Running the Compiled Program</h2>
<p>To run the program, type:</p>
<pre><code>java HelloKalomo
</code></pre>
<p>Notice that you do not include the <code>.class</code> extension when running the program. The output should be:</p>
<pre><code>Hello, Kalomo!
</code></pre>

<h2>Common Errors and How to Fix Them</h2>
<p>Beginners often see error messages. These messages are helpful once you learn to read them. Here are three common errors:</p>
<ul>
<li><strong>"class HelloKalomo is public, should be declared in a file named HelloKalomo.java"</strong> — the class name and filename do not match. Rename the file or the class so they are identical, including capital letters.</li>
<li><strong>"';' expected"</strong> — you forgot a semicolon at the end of a statement. Add the missing semicolon.</li>
<li><strong>"cannot find symbol"</strong> — you may have misspelled a Java keyword such as <code>System</code> or <code>println</code>. Check your spelling carefully.</li>
</ul>

<h2>Worked Example: Compiling from the Command Line</h2>
<p>Grace has saved her program in <code>C:\Users\Grace\Documents\JavaPractice</code>. She opens the command prompt and follows these steps:</p>
<ol>
<li>She types <code>cd Documents\JavaPractice</code> and presses Enter.</li>
<li>She types <code>dir</code> to list files and confirms that <code>HelloKalomo.java</code> is present.</li>
<li>She types <code>javac HelloKalomo.java</code>. No error appears, so she knows compilation succeeded.</li>
<li>She types <code>dir</code> again and sees <code>HelloKalomo.class</code> in the list.</li>
<li>She types <code>java HelloKalomo</code> and smiles when the screen shows "Hello, Kalomo!"</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Open the command prompt and navigate to your JavaPractice folder.</li>
<li>Type <code>javac HelloKalomo.java</code> and confirm that a <code>.class</code> file is created.</li>
<li>Run the program with <code>java HelloKalomo</code>.</li>
<li>Deliberately remove one semicolon from the program, save it, and try to compile again. Read the error message, then restore the semicolon.</li>
<li>Change the class name in the code but not the filename, then try to compile. Read the error message and fix the mismatch.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Source code</strong> — the human-readable text of a program, usually stored in a .java file.</li>
<li><strong>Bytecode</strong> — the intermediate code produced by the Java compiler, stored in a .class file.</li>
<li><strong>Compile</strong> — to translate source code into bytecode using the javac command.</li>
<li><strong>Command prompt</strong> — the text-based interface where you type commands to compile and run programs.</li>
<li><strong>Error message</strong> — feedback from the compiler explaining why it cannot translate your code.</li>
</ul>

<h2>Summary</h2>
<p>Compiling and running Java from the command line gives you a deeper understanding of how Java works. The <code>javac</code> command turns your source code into bytecode, and the <code>java</code> command runs that bytecode. Reading error messages carefully is a skill that will save you many hours as your programs become more complex.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/windows/terminal/">Microsoft Learn — Windows Terminal</a></li>
<li><a href="https://www.w3schools.com/java/java_getstarted.asp">W3Schools — Java Get Started</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Programming Tutorials</a></li>
</ul>
HTML,
            ],
        ];
    }


    private function module2Lessons(): array
    {
        return [
            [
                'title' => '2.1 Variables and Java Data Types',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to declare variables in Java, choose the correct primitive data type for different kinds of information, understand the difference between text and numeric data, and write small programs that store and display prices, names, and true-or-false values.</p>

<h2>What Is a Variable?</h2>
<p>A <strong>variable</strong> is a named container that stores a value in your program. You can think of a variable as a labelled box. The label is the name, and the contents are the value. In a shop program, you might have variables for the price of a bag of mealie meal, the quantity sold, the customer's name, and whether the customer has paid. Variables let your program remember information while it runs.</p>

<h2>Declaring Variables in Java</h2>
<p>Before you can use a variable, you must declare it. Declaring a variable means telling Java what type of data it will hold and giving it a name. Here are some examples:</p>
<pre><code>int quantity = 5;
double price = 18.50;
String productName = "Mealie meal 25kg";
boolean isPaid = false;
char grade = 'A';
</code></pre>
<p>Each declaration has a <strong>data type</strong>, a <strong>name</strong>, and an <strong>initial value</strong>. The data type tells Java how much memory to allocate and what operations are allowed. The name should describe the value clearly. The initial value is optional but recommended for beginners because it makes the code easier to understand.</p>

<h2>Primitive Data Types</h2>
<p>Java provides several built-in data types for simple values. The most common ones are:</p>
<ul>
<li><strong>int</strong> — whole numbers such as 5, 100, or -12. Use int for quantities, counts, and ages.</li>
<li><strong>double</strong> — numbers with decimal places such as 18.50 or 3.14. Use double for money amounts, measurements, and averages.</li>
<li><strong>String</strong> — text such as names, addresses, and descriptions. String values are written inside double quotation marks.</li>
<li><strong>boolean</strong> — a true-or-false value. Use boolean for conditions such as whether a payment has been made.</li>
<li><strong>char</strong> — a single character such as 'A' or 'Z'. Use char when you need exactly one letter or symbol.</li>
</ul>

<h2>Worked Example: A Simple Shop Receipt</h2>
<p>Suppose Mrs Banda sells cooking oil and sugar at her shop in Kalomo. She wants a program that stores the item name, quantity sold, unit price, and whether the customer paid by mobile money. Her program might look like this:</p>
<pre><code>public class ShopReceipt {
    public static void main(String[] args) {
        String item = "Cooking oil 2 litres";
        int quantity = 3;
        double unitPrice = 28.75;
        boolean paidByMoMo = true;

        double total = quantity * unitPrice;

        System.out.println("Item: " + item);
        System.out.println("Quantity: " + quantity);
        System.out.println("Total: K" + total);
        System.out.println("Paid by mobile money: " + paidByMoMo);
    }
}
</code></pre>
<p>The output is:</p>
<pre><code>Item: Cooking oil 2 litres
Quantity: 3
Total: K86.25
Paid by mobile money: true
</code></pre>

<h2>Try It Yourself</h2>
<ol>
<li>Create a new file named <code>VariablesPractice.java</code> in your JavaPractice folder.</li>
<li>Declare variables to store your name as a String, your age as an int, your height in metres as a double, and whether you are a student as a boolean.</li>
<li>Print each variable on its own line using <code>System.out.println</code>.</li>
<li>Add two int variables for the number of Airtel Money transactions and MTN MoMo transactions you made this week. Print their sum.</li>
<li>Experiment by changing the values and running the program again.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Variable</strong> — a named storage location that holds a value in a program.</li>
<li><strong>Data type</strong> — a classification that tells the compiler what kind of value a variable can hold.</li>
<li><strong>int</strong> — a Java data type for whole numbers.</li>
<li><strong>double</strong> — a Java data type for numbers with decimal places.</li>
<li><strong>String</strong> — a Java type for storing text.</li>
</ul>

<h2>Summary</h2>
<p>Variables are essential building blocks of every Java program. By choosing the right data type, you ensure that your program stores information efficiently and correctly. The primitive types int, double, boolean, and char, along with the String type, cover most of the simple data needs you will encounter in beginner programs.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_variables.asp">W3Schools — Java Variables</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Learn Programming</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Computer Programming</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.2 Declaring Constants for Prices and VAT',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to declare constants in Java using the <code>final</code> keyword, explain why constants are useful for values that should not change, and apply constants to VAT rates, discount percentages, and fixed prices in a Zambian business context.</p>

<h2>What Is a Constant?</h2>
<p>A <strong>constant</strong> is a variable whose value cannot be changed after it is set. In Java, you declare a constant by adding the keyword <code>final</code> before the data type. Constants are usually written in UPPER_CASE with underscores separating words. For example:</p>
<pre><code>final double VAT_RATE = 0.16;
final double DELIVERY_FEE = 25.00;
</code></pre>
<p>Once you assign a value to a constant, any attempt to change it later will cause a compiler error. This protects your program from accidental mistakes.</p>

<h2>Why Use Constants?</h2>
<p>Imagine you are writing a program for a shop in Kalomo. The VAT rate is currently 16 percent. If you type 0.16 in ten different places and the government changes the rate, you must find and update every occurrence. If you miss one, your calculations become wrong. A constant solves this problem. You define the rate once and use the constant name everywhere. When the rate changes, you update only one line.</p>

<h2>Worked Example: Calculating VAT on a Sale</h2>
<p>Mr Tembo sells electrical goods. He wants a program that calculates the VAT and total price for a ZESCO solar lamp that costs K450 before tax.</p>
<pre><code>public class VatCalculator {
    public static void main(String[] args) {
        final double VAT_RATE = 0.16;
        double itemPrice = 450.00;

        double vatAmount = itemPrice * VAT_RATE;
        double totalPrice = itemPrice + vatAmount;

        System.out.println("Item price: K" + itemPrice);
        System.out.println("VAT (16%): K" + vatAmount);
        System.out.println("Total to pay: K" + totalPrice);
    }
}
</code></pre>
<p>The output is:</p>
<pre><code>Item price: K450.0
VAT (16%): K72.0
Total to pay: K522.0
</code></pre>
<p>If the VAT rate changes, Mr Tembo only needs to update the single line that defines <code>VAT_RATE</code>.</p>

<h2>Naming Conventions for Constants</h2>
<p>Java programmers use clear conventions to make code readable:</p>
<ul>
<li>Constants are written in UPPER_CASE_WITH_UNDERSCORES.</li>
<li>Variable names start with a lowercase letter and use camelCase, such as <code>itemPrice</code>.</li>
<li>Class names start with an uppercase letter, such as <code>VatCalculator</code>.</li>
</ul>
<p>Following these conventions makes your code easier to read for instructors, classmates, and employers.</p>

<h2>Worked Example: Applying a Discount</h2>
<p>Mrs Phiri runs a boutique in Soweto Market. She offers a 10 percent discount on all sales above K200. By storing the threshold and discount rate as constants, she can adjust her promotion without searching through every line of code.</p>
<pre><code>public class DiscountCalculator {
    public static void main(String[] args) {
        final double DISCOUNT_THRESHOLD = 200.00;
        final double DISCOUNT_RATE = 0.10;
        double purchaseAmount = 350.00;

        double finalAmount = purchaseAmount;
        if (purchaseAmount &gt;= DISCOUNT_THRESHOLD) {
            finalAmount = purchaseAmount - (purchaseAmount * DISCOUNT_RATE);
        }

        System.out.println("Original amount: K" + purchaseAmount);
        System.out.println("Final amount: K" + finalAmount);
    }
}
</code></pre>
<p>The output shows the original amount of K350.00 reduced to K315.00 because the purchase exceeds the threshold. If Mrs Phiri later wants to lower the threshold to K150, she changes one constant and the entire program updates.</p>

<h2>Common Mistakes</h2>
<ul>
<li>Trying to reassign a constant after it has been initialised. Java will report a compiler error.</li>
<li>Using lowercase names for constants. Although the program still works, it makes the code harder to read.</li>
<li>Forgetting that <code>final</code> variables must be given a value before they are used.</li>
<li>Confusing the VAT rate with the VAT amount. The rate is 0.16; the amount is price multiplied by the rate.</li>
</ul>

<h2>Real-World Connection</h2>
<p>Zambian businesses, from small shops in Kalomo to large retailers in Lusaka, must apply VAT and adjust prices when tax rules change. Many accounting systems and point-of-sale programs store tax rates as constants so that updates are quick and consistent. When you learn to use constants well, you are practising the same discipline used by software developers who build systems for banks, supermarkets, and government agencies across Zambia.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>ConstantsPractice.java</code>.</li>
<li>Declare a constant for the current VAT rate and constants for three item prices of your choice.</li>
<li>Calculate the total price including VAT for each item and print the results.</li>
<li>Add a constant for a discount percentage and calculate the discounted price before VAT.</li>
<li>Change the VAT constant value and run the program again to see all totals update automatically.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Constant</strong> — a variable whose value cannot be changed after it is first assigned.</li>
<li><strong>final</strong> — the Java keyword used to declare a constant.</li>
<li><strong>VAT</strong> — Value Added Tax, a tax added to the price of goods and services.</li>
<li><strong>Naming convention</strong> — a standard way of naming variables, constants, and classes to make code readable.</li>
<li><strong>camelCase</strong> — a naming style where each new word starts with a capital letter and no spaces are used.</li>
</ul>

<h2>Summary</h2>
<p>Constants help you write cleaner, safer programs by preventing accidental changes to important values. Use <code>final</code> to declare constants in Java, name them in UPPER_CASE, and use them for values such as VAT rates, delivery fees, and discount percentages that should stay the same throughout your program.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_variables.asp">W3Schools — Java Variables and Constants</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Programming Fundamentals</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Training</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Programming</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.3 Arithmetic and Assignment Operators',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to use Java's arithmetic operators to perform calculations, combine assignment with arithmetic using shorthand operators, understand integer division and the modulus operator, and apply these skills to everyday calculations involving money and quantities.</p>

<h2>Arithmetic Operators</h2>
<p>Java provides the same basic arithmetic operators you learned in school:</p>
<ul>
<li><code>+</code> addition</li>
<li><code>-</code> subtraction</li>
<li><code>*</code> multiplication</li>
<li><code>/</code> division</li>
<li><code>%</code> modulus (remainder after division)</li>
</ul>
<p>These operators work with numbers. You can use them with variables or literal values. For example:</p>
<pre><code>int total = 10 + 5;
double average = 75.5 / 2;
int remainder = 17 % 5; // remainder is 2
</code></pre>

<h2>Integer Division</h2>
<p>When both operands of the division operator are integers, Java performs <strong>integer division</strong> and discards the remainder. This can surprise beginners. For example:</p>
<pre><code>int result = 7 / 2; // result is 3, not 3.5
</code></pre>
<p>If you need a decimal result, make sure at least one operand is a double:</p>
<pre><code>double result = 7.0 / 2; // result is 3.5
</code></pre>

<h2>Assignment Operators</h2>
<p>Besides the simple <code>=</code> assignment operator, Java provides shorthand operators that combine arithmetic with assignment:</p>
<ul>
<li><code>+=</code> add and assign</li>
<li><code>-=</code> subtract and assign</li>
<li><code>*=</code> multiply and assign</li>
<li><code>/=</code> divide and assign</li>
<li><code>%=</code> modulus and assign</li>
</ul>
<p>For example, <code>balance += 50</code> is a shorter way of writing <code>balance = balance + 50</code>.</p>

<h2>Operator Precedence</h2>
<p>Java follows the same rules as mathematics for deciding which operation happens first. Multiplication, division, and modulus happen before addition and subtraction. Parentheses can be used to change the order. For example:</p>
<pre><code>int result = 10 + 5 * 2; // result is 20, not 30
int result2 = (10 + 5) * 2; // result is 30
</code></pre>
<p>When in doubt, use parentheses to make your intentions clear. Clear code prevents bugs and makes your programs easier to review.</p>

<h2>Worked Example: Sharing a Transport Cost</h2>
<p>A group of five students from Kalomo College hires a minibus to Lusaka for K1,250. They want to split the cost equally and know how much change is left over if anyone pays with a K300 note.</p>
<pre><code>public class TransportShare {
    public static void main(String[] args) {
        int totalCost = 1250;
        int students = 5;
        int amountPaid = 300;

        int share = totalCost / students;
        int remainder = totalCost % students;
        int change = amountPaid - share;

        System.out.println("Each student pays: K" + share);
        System.out.println("Remainder after dividing: K" + remainder);
        System.out.println("Change from K300: K" + change);
    }
}
</code></pre>
<p>The output is:</p>
<pre><code>Each student pays: K250
Remainder after dividing: K0
Change from K300: K50
</code></pre>

<h2>Worked Example: Chicken Rearing Profit</h2>
<p>Mrs Banda buys 50 day-old chicks at K15 each and sells the mature chickens at K75 each. She spends K500 on feed and K200 on vaccines. Her profit is the total sales minus the total costs.</p>
<pre><code>public class ChickenProfit {
    public static void main(String[] args) {
        int chicks = 50;
        double chickPrice = 15.00;
        double salePrice = 75.00;
        double feedCost = 500.00;
        double vaccineCost = 200.00;

        double totalCost = (chicks * chickPrice) + feedCost + vaccineCost;
        double totalSales = chicks * salePrice;
        double profit = totalSales - totalCost;

        System.out.println("Total cost: K" + totalCost);
        System.out.println("Total sales: K" + totalSales);
        System.out.println("Profit: K" + profit);
    }
}
</code></pre>
<p>The program shows a total cost of K1,450, total sales of K3,750, and a profit of K2,300. This type of calculation helps small-scale farmers decide whether an investment is worthwhile.</p>

<h2>Common Mistakes</h2>
<ul>
<li>Forgetting that dividing two integers discards the remainder. Use <code>7.0 / 2</code> to keep the decimal part.</li>
<li>Confusing the assignment operator <code>=</code> with the comparison operator <code>==</code>.</li>
<li>Writing expressions without parentheses and then being surprised by the result.</li>
<li>Using <code>%</code> when you meant <code>/</code>, or vice versa.</li>
</ul>

<h2>Real-World Connection</h2>
<p>Every time a shopkeeper calculates change, a bus conductor divides a fare, or a farmer budgets for seed and fertiliser, they are using the same arithmetic operators found in Java. Mobile money apps such as Airtel Money and MTN MoMo use these operators to update balances, calculate transaction fees, and convert between Kwacha and ngwee. Understanding arithmetic in Java prepares you to build similar tools for Zambian businesses.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>OperatorsPractice.java</code>.</li>
<li>Declare variables for the price of a bus ticket from Kalomo to Livingstone and the number of passengers.</li>
<li>Calculate the total fare, the fare per person, and any remainder.</li>
<li>Use the modulus operator to find how many ngwee are left after converting an amount in ngwee to whole Kwacha. Remember that 1 Kwacha = 100 ngwee.</li>
<li>Experiment with the shorthand assignment operators by starting a variable at zero and adding different amounts to it.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Arithmetic operator</strong> — a symbol such as +, -, *, /, or % used to perform a mathematical calculation.</li>
<li><strong>Integer division</strong> — division where the fractional part is discarded because both operands are whole numbers.</li>
<li><strong>Modulus operator</strong> — the % operator, which returns the remainder after division.</li>
<li><strong>Assignment operator</strong> — an operator such as = or += that stores a value in a variable.</li>
<li><strong>Operand</strong> — a value or variable that an operator acts upon.</li>
</ul>

<h2>Summary</h2>
<p>Arithmetic and assignment operators let your programs perform calculations. Be careful with integer division, and remember that the modulus operator is useful for finding remainders. Shorthand assignment operators make your code shorter and clearer when you update a variable based on its current value.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_operators.asp">W3Schools — Java Operators</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Coding Challenges</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Programming</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Training</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.4 A Worked Example: Calculating a Shop Bill with Kwacha and Ngwee',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to combine variables, constants, and arithmetic operators to build a realistic shop bill calculator in Java, handle Kwacha and ngwee correctly, and display a formatted receipt that a small business in Zambia could actually use.</p>

<h2>Planning the Program</h2>
<p>Before writing code, it helps to plan what the program should do. A shop bill calculator needs to:</p>
<ol>
<li>Store the prices of several items.</li>
<li>Store the quantity purchased for each item.</li>
<li>Calculate the subtotal.</li>
<li>Add VAT at the correct rate.</li>
<li>Display a clear receipt showing the amount due.</li>
</ol>

<h2>Worked Example: Kalomo General Store Receipt</h2>
<p>Mrs Zulu runs Kalomo General Store. A customer buys two bags of mealie meal, five bars of soap, and three litres of cooking oil. The program below calculates the total bill.</p>
<pre><code>public class ShopBill {
    public static void main(String[] args) {
        final double VAT_RATE = 0.16;

        double mealieMealPrice = 145.00;
        int mealieMealQty = 2;

        double soapPrice = 12.50;
        int soapQty = 5;

        double oilPrice = 28.75;
        int oilQty = 3;

        double subtotal = (mealieMealPrice * mealieMealQty)
                        + (soapPrice * soapQty)
                        + (oilPrice * oilQty);

        double vat = subtotal * VAT_RATE;
        double total = subtotal + vat;

        System.out.println("KALOMO GENERAL STORE");
        System.out.println("--------------------");
        System.out.println("Mealie meal 25kg x " + mealieMealQty + " = K" + (mealieMealPrice * mealieMealQty));
        System.out.println("Soap x " + soapQty + " = K" + (soapPrice * soapQty));
        System.out.println("Cooking oil x " + oilQty + " = K" + (oilPrice * oilQty));
        System.out.println("--------------------");
        System.out.println("Subtotal: K" + subtotal);
        System.out.println("VAT (16%): K" + vat);
        System.out.println("TOTAL: K" + total);
    }
}
</code></pre>
<p>The output is:</p>
<pre><code>KALOMO GENERAL STORE
--------------------
Mealie meal 25kg x 2 = K290.0
Soap x 5 = K62.5
Cooking oil x 3 = K86.25
--------------------
Subtotal: K438.75
VAT (16%): K70.2
TOTAL: K508.95
</code></pre>

<h2>Handling Ngwee</h2>
<p>The Zambian Kwacha is divided into 100 ngwee. In Java, amounts in ngwee can be stored as integers to avoid rounding errors. For example, K5.50 is 550 ngwee. If you need to convert between Kwacha and ngwee, use multiplication and division by 100. For most small business programs, storing money as double is acceptable for learning purposes, but professional accounting software often uses integer ngwee or specialised decimal classes to ensure precision.</p>

<h2>Worked Example: Converting Kwacha to Ngwee</h2>
<p>A customer pays K250.75 in cash. The cashier wants to know the equivalent amount in ngwee to check against the till count.</p>
<pre><code>public class KwachaToNgwee {
    public static void main(String[] args) {
        double kwachaAmount = 250.75;
        int ngweeAmount = (int) (kwachaAmount * 100);

        System.out.println("Amount in Kwacha: K" + kwachaAmount);
        System.out.println("Amount in ngwee: " + ngweeAmount + " ngwee");
    }
}
</code></pre>
<p>The output shows 25075 ngwee. Notice the use of <code>(int)</code> to convert the double result into a whole number. This is called casting. In real financial systems, developers use classes such as <code>BigDecimal</code> to avoid tiny rounding errors.</p>

<h2>Common Mistakes</h2>
<ul>
<li>Forgetting to multiply quantity by price before adding to the subtotal.</li>
<li>Using integer division for money, which can lose ngwee.</li>
<li>Hard-coding the VAT rate in many places instead of using a constant.</li>
<li>Printing numbers without labels, making receipts hard to read.</li>
</ul>

<h2>Best Practice</h2>
<p>Always label your output clearly and store tax rates as constants. When calculating money, use <code>double</code> for learning projects, but be aware that banks and large retailers use more precise types. Test your calculator with simple values that you can verify with a phone calculator. For example, if one item costs K100 and the VAT rate is 16 percent, the total should be exactly K116.</p>

<h2>Real-World Connection</h2>
<p>Shop bill calculators are used in supermarkets, hardware stores, and restaurants across Zambia. Modern point-of-sale systems often run on Java because it is reliable and works on many devices. By learning to build a receipt calculator, you are taking the first step toward understanding how those systems work. A small shop in Soweto Market could use a program like this to reduce errors and speed up checkout during busy hours.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>ShopBill.java</code>.</li>
<li>Write the program above and run it. Check that the output matches.</li>
<li>Add a fourth item of your choice, such as sugar or salt. Update the calculations and the printed receipt.</li>
<li>Add a discount feature. If the subtotal is over K500, apply a 5 percent discount before adding VAT.</li>
<li>Modify the output so that each line shows both Kwacha and ngwee, for example "TOTAL: K508.95 (50895 ngwee)."</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Subtotal</strong> — the total before tax or discounts are applied.</li>
<li><strong>Receipt</strong> — a document showing the items purchased and the amount paid.</li>
<li><strong>Ngwee</strong> — a subdivision of the Zambian Kwacha; one hundred ngwee equal one Kwacha.</li>
<li><strong>Rounding error</strong> — a small inaccuracy that can occur when computers store decimal numbers using binary fractions.</li>
<li><strong>Output formatting</strong> — the way a program presents information on the screen or in a document.</li>
</ul>

<h2>Summary</h2>
<p>This lesson brought together variables, constants, and operators to create a practical shop bill calculator. You saw how a real Zambian business could use Java to speed up checkout, reduce arithmetic mistakes, and produce clear receipts. As your skills grow, you can extend this program to read input from a cashier, save receipts to a file, and track daily sales.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_operators.asp">W3Schools — Java Operators</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Practice Projects</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Free Training</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Programming</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module3Lessons(): array
    {
        return [
            [
                'title' => '3.1 Relational and Logical Operators',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to use relational operators to compare values, combine comparisons with logical operators, and understand how Java evaluates true-or-false expressions so that your programs can make decisions.</p>

<h2>Making Decisions in Programs</h2>
<p>So far, your programs have run from top to bottom without making choices. Real-world programs must decide what to do based on conditions. A banking app decides whether a PIN is correct. A ZESCO token app decides whether the meter number is valid. A shop program decides whether a customer qualifies for a discount. These decisions are based on comparisons.</p>

<h2>Relational Operators</h2>
<p>Relational operators compare two values and produce a boolean result. Java's relational operators are:</p>
<ul>
<li><code>==</code> equal to</li>
<li><code>!=</code> not equal to</li>
<li><code>&gt;</code> greater than</li>
<li><code>&lt;</code> less than</li>
<li><code>&gt;=</code> greater than or equal to</li>
<li><code>&lt;=</code> less than or equal to</li>
</ul>
<p>For example, <code>age &gt;= 18</code> is true if age is 18 or more. Be careful not to confuse <code>==</code> (comparison) with <code>=</code> (assignment).</p>

<h2>Logical Operators</h2>
<p>Logical operators let you combine multiple comparisons:</p>
<ul>
<li><code>&amp;&amp;</code> logical AND — true only if both conditions are true</li>
<li><code>||</code> logical OR — true if at least one condition is true</li>
<li><code>!</code> logical NOT — reverses true to false and false to true</li>
</ul>
<p>For example, <code>balance &gt;= 50 &amp;&amp; meterNumber.length() == 11</code> checks that the balance is enough and the meter number has the correct length.</p>

<h2>Worked Example: Validating a Customer</h2>
<p>A mobile money agent wants to check whether a customer can withdraw K200. The customer must have at least K200 in their wallet and must have provided a valid National Registration Card number, which the system treats as 9 characters long.</p>
<pre><code>public class ValidateWithdrawal {
    public static void main(String[] args) {
        double walletBalance = 350.00;
        String nrc = "123456/78/9";

        boolean canWithdraw = walletBalance &gt;= 200 &amp;&amp; nrc.length() &gt;= 9;

        System.out.println("Can withdraw K200: " + canWithdraw);
    }
}
</code></pre>

<h2>Worked Example: Checking a ZESCO Meter Top-Up</h2>
<p>A customer can buy a ZESCO token only if the meter number has exactly 11 digits and the purchase amount is at least K20. The OR operator can also allow a special admin override for testing.</p>
<pre><code>public class ZescoCheck {
    public static void main(String[] args) {
        String meterNumber = "12345678901";
        double amount = 50.00;
        boolean isAdmin = false;

        boolean canPurchase = meterNumber.length() == 11 &amp;&amp; (amount &gt;= 20 || isAdmin);

        System.out.println("Can purchase token: " + canPurchase);
    }
}
</code></pre>
<p>The expression inside the parentheses ensures that the admin override applies only when the meter number is valid. This shows how parentheses and logical operators work together.</p>

<h2>Common Mistakes</h2>
<ul>
<li>Using a single <code>=</code> instead of <code>==</code> when comparing values.</li>
<li>Writing <code>&amp;</code> instead of <code>&amp;&amp;</code>. A single ampersand is a different operator called bitwise AND.</li>
<li>Assuming that <code>||</code> means exclusive or. It is true when either or both conditions are true.</li>
<li>Forgetting that <code>!</code> only affects the expression immediately after it unless parentheses are used.</li>
</ul>

<h2>Real-World Connection</h2>
<p>Relational and logical operators appear in almost every software system used in Zambia. Banks use them to check account balances and PINs. ZESCO uses them to validate meter numbers and purchase amounts. Schools use them to determine whether a student has passed. Mobile money platforms use them to prevent transactions when a wallet balance is too low. By mastering these operators, you are learning the building blocks of systems that millions of Zambians rely on every day.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>ComparisonPractice.java</code>.</li>
<li>Declare an int variable for a student's score and a boolean variable for whether they submitted on time.</li>
<li>Use relational and logical operators to check whether the student passed with at least 50 percent and submitted on time. Print the result.</li>
<li>Write a second check that is true if the score is either 100 or the student has a valid excuse.</li>
<li>Experiment with different values until you can predict the output before running the program.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Relational operator</strong> — an operator that compares two values and returns true or false.</li>
<li><strong>Logical operator</strong> — an operator that combines boolean expressions, such as AND, OR, and NOT.</li>
<li><strong>Boolean expression</strong> — an expression that evaluates to true or false.</li>
<li><strong>Condition</strong> — a boolean expression used to make a decision in a program.</li>
<li><strong>Comparison</strong> — the act of checking whether one value is equal to, greater than, or less than another.</li>
</ul>

<h2>Summary</h2>
<p>Relational and logical operators are the building blocks of decision-making in Java. They allow your programs to compare values and combine multiple conditions. Mastering these operators is essential before you learn if-statements, loops, and more advanced control flow.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_operators.asp">W3Schools — Java Operators</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Learn to Code</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Programming</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Training</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.2 if, else if, else Statements',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to write if, else if, and else statements in Java, chain multiple conditions together, and build programs that respond differently depending on user data such as exam scores, purchase amounts, or account balances.</p>

<h2>The if Statement</h2>
<p>The <code>if</code> statement is the simplest way to make a decision. It runs a block of code only if a condition is true. The syntax is:</p>
<pre><code>if (condition) {
    // code to run when condition is true
}
</code></pre>
<p>For example:</p>
<pre><code>int score = 75;
if (score &gt;= 50) {
    System.out.println("You passed.");
}
</code></pre>

<h2>Adding else</h2>
<p>Use <code>else</code> to run code when the condition is false:</p>
<pre><code>int score = 40;
if (score &gt;= 50) {
    System.out.println("You passed.");
} else {
    System.out.println("You need to retake the exam.");
}
</code></pre>

<h2>Chaining with else if</h2>
<p>When there are more than two possibilities, use <code>else if</code> to check additional conditions:</p>
<pre><code>int score = 82;
if (score &gt;= 80) {
    System.out.println("Distinction");
} else if (score &gt;= 65) {
    System.out.println("Merit");
} else if (score &gt;= 50) {
    System.out.println("Pass");
} else {
    System.out.println("Fail");
}
</code></pre>

<h2>Worked Example: Classifying a Customer</h2>
<p>A shop in Kalomo gives customers different discount levels based on how much they spend. The program below decides the discount.</p>
<pre><code>public class CustomerDiscount {
    public static void main(String[] args) {
        double amount = 650.00;

        if (amount &gt;= 1000) {
            System.out.println("Gold customer: 10% discount");
        } else if (amount &gt;= 500) {
            System.out.println("Silver customer: 5% discount");
        } else if (amount &gt;= 200) {
            System.out.println("Bronze customer: 2% discount");
        } else {
            System.out.println("No discount available");
        }
    }
}
</code></pre>
<p>The output is "Silver customer: 5% discount" because 650 is at least 500 but less than 1000.</p>

<h2>Worked Example: School Grade Classification</h2>
<p>In a Zambian school, marks are often grouped into grade bands. The program below converts a percentage into a letter grade.</p>
<pre><code>public class GradeClassifier {
    public static void main(String[] args) {
        int marks = 72;

        if (marks &gt;= 80) {
            System.out.println("Grade A");
        } else if (marks &gt;= 70) {
            System.out.println("Grade B");
        } else if (marks &gt;= 60) {
            System.out.println("Grade C");
        } else if (marks &gt;= 50) {
            System.out.println("Grade D");
        } else {
            System.out.println("Grade F");
        }
    }
}
</code></pre>
<p>With marks of 72, the program prints "Grade B". Notice how the order matters. If the conditions were reversed, every student with 80 percent or more would be marked as Grade D because the first matching condition would run.</p>

<h2>Common Mistakes</h2>
<ul>
<li>Writing conditions in the wrong order so that a broader condition hides a more specific one.</li>
<li>Forgetting braces when the if or else body has more than one statement.</li>
<li>Using <code>=</code> instead of <code>==</code> inside the condition.</li>
<li>Creating gaps between ranges, such as checking <code>&gt;= 80</code> and <code>&lt;= 60</code> but forgetting what happens between 61 and 79.</li>
</ul>

<h2>Real-World Connection</h2>
<p>If-statements are everywhere in software that supports Zambian daily life. Examination management systems use them to assign grades. Loan applications use them to decide eligibility. Mobile money apps use them to approve or reject payments. Taxi booking apps use them to estimate fares based on distance. Learning to write correct if-else chains is one of the most practical programming skills you can develop.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>IfElsePractice.java</code>.</li>
<li>Declare a variable for an exam score out of 100.</li>
<li>Use if, else if, and else to print "A" for 80 and above, "B" for 65-79, "C" for 50-64, and "F" for below 50.</li>
<li>Add a second program that checks a mobile money wallet balance and prints whether the user can buy a K50 ZESCO token.</li>
<li>Test your programs with values at the boundaries, such as exactly 50, 65, and 80.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>if statement</strong> — a control structure that runs code only when a condition is true.</li>
<li><strong>else</strong> — the part of an if statement that runs when the condition is false.</li>
<li><strong>else if</strong> — an additional condition checked only when previous conditions were false.</li>
<li><strong>Control flow</strong> — the order in which statements execute in a program.</li>
<li><strong>Block</strong> — a group of statements enclosed in braces.</li>
</ul>

<h2>Summary</h2>
<p>If, else if, and else statements let your programs make decisions based on conditions. They are essential for classifying data, validating input, and controlling what happens under different circumstances. Always test boundary values to make sure your conditions cover every possibility correctly.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_conditions.asp">W3Schools — Java Conditions</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Coding Curriculum</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Computer Programming</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Training</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.3 switch Statements',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to write switch statements in Java, decide when a switch statement is cleaner than a chain of if-else statements, and use switch to build menus and selection systems.</p>

<h2>When to Use switch</h2>
<p>A <code>switch</code> statement is useful when you want to choose one path from many based on a single variable or expression. It is often cleaner than a long chain of <code>else if</code> statements when all conditions compare the same value. For example, a program that prints a message based on a menu choice is a good candidate for switch.</p>

<h2>switch Syntax</h2>
<p>The basic syntax looks like this:</p>
<pre><code>int choice = 2;
switch (choice) {
    case 1:
        System.out.println("Option 1 selected");
        break;
    case 2:
        System.out.println("Option 2 selected");
        break;
    default:
        System.out.println("Invalid choice");
}
</code></pre>
<p>The <code>break</code> statement stops the program from continuing into the next case. The <code>default</code> case runs when none of the other cases match.</p>

<h2>Understanding Fall-Through</h2>
<p>If you forget a <code>break</code>, Java continues executing the next case. This is called <strong>fall-through</strong>. Sometimes programmers use fall-through intentionally, but most of the time it causes bugs. Always include <code>break</code> unless you have a clear reason not to.</p>

<h2>Worked Example: Choosing a Mobile Money Provider</h2>
<p>A program asks the user to choose a mobile money provider and then prints the appropriate USSD code. In this example the choice is set directly in the code; later you will learn how to read input from the keyboard.</p>
<pre><code>public class MobileMoneyMenu {
    public static void main(String[] args) {
        int provider = 1;

        switch (provider) {
            case 1:
                System.out.println("Airtel Money selected.");
                break;
            case 2:
                System.out.println("MTN MoMo selected.");
                break;
            case 3:
                System.out.println("Zamtel Kwacha selected.");
                break;
            default:
                System.out.println("Unknown provider.");
        }
    }
}
</code></pre>

<h2>Worked Example: ZESCO Payment Channel</h2>
<p>A ZESCO customer can pay through different channels. The program below prints instructions based on the selected channel.</p>
<pre><code>public class ZescoChannel {
    public static void main(String[] args) {
        int channel = 2;

        switch (channel) {
            case 1:
                System.out.println("Dial *115# to pay via Airtel Money.");
                break;
            case 2:
                System.out.println("Dial *303# to pay via MTN MoMo.");
                break;
            case 3:
                System.out.println("Visit a ZESCO outlet with cash or card.");
                break;
            case 4:
                System.out.println("Pay through your bank's mobile app.");
                break;
            default:
                System.out.println("Invalid payment channel selected.");
        }
    }
}
</code></pre>

<h2>Common Mistakes</h2>
<ul>
<li>Forgetting <code>break</code> and accidentally running the next case.</li>
<li>Using a switch variable type that Java does not support, such as <code>double</code>.</li>
<li>Forgetting the <code>default</code> case, which leaves invalid inputs unhandled.</li>
<li>Trying to use ranges in case labels. Switch checks for exact matches, not ranges.</li>
</ul>

<h2>Real-World Connection</h2>
<p>Switch statements are used in interactive voice response systems, USSD menus, and ATM interfaces. When you dial a short code to check your Airtel Money balance or buy ZESCO tokens, the system behind the menu is often built with logic similar to a switch statement. Each number you press routes your call or transaction to a specific case. Learning switch helps you understand how these user-facing menus are implemented.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>SwitchPractice.java</code>.</li>
<li>Write a switch statement that prints the name of a Zambian province based on an int value from 1 to 4. For example, 1 for Southern, 2 for Lusaka, 3 for Copperbelt, 4 for Central.</li>
<li>Add a default case for unknown province numbers.</li>
<li>Remove one break statement and observe how the output changes. This will help you understand why break is important.</li>
<li>Rewrite one of your if-else programs from the previous lesson as a switch statement if it makes sense to do so.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>switch statement</strong> — a control structure that selects one of many code paths based on a value.</li>
<li><strong>case</strong> — a possible value that the switch expression can match.</li>
<li><strong>break</strong> — a statement that exits the switch statement so that the next case does not run.</li>
<li><strong>default</strong> — the case that runs when no other case matches.</li>
<li><strong>Menu</strong> — a list of options presented to a user.</li>
</ul>

<h2>Summary</h2>
<p>Switch statements provide a clean way to choose between many alternatives based on a single value. They are especially useful for menus and classification systems. Always include break statements to prevent fall-through behaviour, and include a default case to handle unexpected values.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_switch.asp">W3Schools — Java Switch</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Java Tutorials</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Programming</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Training</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.4 for and while Loops',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to write for loops and while loops in Java, choose the right loop for a given task, and use loops to repeat calculations, count values, and process sequences of data.</p>

<h2>Why Use Loops?</h2>
<p>Programs often need to repeat an action many times. A shop owner may want to print ten receipts. A teacher may want to calculate the average score for thirty students. A farmer may want to predict crop yields for each month of the year. Writing the same code over and over is inefficient and error-prone. <strong>Loops</strong> solve this problem by repeating a block of code automatically.</p>

<h2>The for Loop</h2>
<p>A <code>for</code> loop is ideal when you know in advance how many times you want to repeat something. The syntax is:</p>
<pre><code>for (int i = 1; i &lt;= 10; i++) {
    System.out.println("Count: " + i);
}
</code></pre>
<p>The loop has three parts inside the parentheses: initialisation, condition, and update. First, <code>int i = 1</code> creates a counter. Then <code>i &lt;= 10</code> checks whether the loop should continue. Finally, <code>i++</code> increases the counter by one after each repetition.</p>

<h2>The while Loop</h2>
<p>A <code>while</code> loop repeats as long as a condition is true. It is useful when you do not know in advance how many repetitions are needed. The syntax is:</p>
<pre><code>int number = 1;
while (number &lt;= 5) {
    System.out.println("Number: " + number);
    number++;
}
</code></pre>

<h2>Infinite Loops</h2>
<p>An <strong>infinite loop</strong> never stops because its condition is always true. Infinite loops are usually bugs, but they can also be used intentionally in servers and real-time systems. To stop an accidental infinite loop in VS Code, click the red square stop button or close the terminal. Always make sure the loop condition can eventually become false.</p>

<h2>Worked Example: Counting Daily Sales</h2>
<p>A trader wants to print the total sales for each day of a five-day market week.</p>
<pre><code>public class WeeklySales {
    public static void main(String[] args) {
        double[] sales = {150.00, 230.50, 80.00, 310.00, 195.00};
        double total = 0.0;

        for (int day = 0; day &lt; sales.length; day++) {
            System.out.println("Day " + (day + 1) + " sales: K" + sales[day]);
            total += sales[day];
        }

        System.out.println("Total weekly sales: K" + total);
    }
}
</code></pre>

<h2>Worked Example: Load-Shedding Battery Planning</h2>
<p>A family in Kalomo wants to know how many hours their backup battery will last if load-shedding continues. The battery starts at 100 percent and loses 20 percent each hour.</p>
<pre><code>public class BatteryLife {
    public static void main(String[] args) {
        int batteryPercent = 100;
        int hour = 0;

        while (batteryPercent &gt; 0) {
            hour++;
            batteryPercent -= 20;
            System.out.println("Hour " + hour + ": " + batteryPercent + "% remaining");
        }

        System.out.println("Battery will last approximately " + hour + " hours.");
    }
}
</code></pre>
<p>The program prints the battery level each hour until it reaches zero. This kind of simulation helps families and small businesses plan for power outages.</p>

<h2>Common Mistakes</h2>
<ul>
<li>Forgetting to update the counter in a while loop, causing an infinite loop.</li>
<li>Using <code>&lt;=</code> instead of <code>&lt;</code> and accessing an array index that does not exist.</li>
<li>Starting the loop counter at 1 when arrays start at index 0.</li>
<li>Modifying the loop counter inside the loop body in unexpected ways.</li>
</ul>

<h2>Real-World Connection</h2>
<p>Loops power many everyday systems. Payroll software loops through every employee to calculate salaries. Billing systems loop through every transaction to generate monthly statements. ZESCO billing systems loop through thousands of meters to calculate consumption. Social media apps loop through posts to fill your feed. Any system that processes a list of items uses loops, making them one of the most important programming concepts to master.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>LoopsPractice.java</code>.</li>
<li>Write a for loop that prints the multiplication table for 7 from 1 to 12.</li>
<li>Write a while loop that starts with a wallet balance of K500 and subtracts K50 each iteration until the balance is zero. Print the balance after each subtraction.</li>
<li>Use a loop to calculate the sum of all whole numbers from 1 to 100.</li>
<li>Experiment with changing the loop condition to see what happens when the condition is never true.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Loop</strong> — a control structure that repeats a block of code.</li>
<li><strong>for loop</strong> — a loop that repeats a known number of times, controlled by a counter.</li>
<li><strong>while loop</strong> — a loop that repeats while a condition remains true.</li>
<li><strong>Counter</strong> — a variable that keeps track of how many times a loop has run.</li>
<li><strong>Iteration</strong> — one complete pass through a loop.</li>
</ul>

<h2>Summary</h2>
<p>Loops are one of the most powerful tools in programming. The for loop is best when you know the number of repetitions in advance, while the while loop is best when repetition depends on a condition. Together with if-statements, loops allow you to build programs that handle large amounts of data without writing repetitive code.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_for_loop.asp">W3Schools — Java For Loop</a></li>
<li><a href="https://www.w3schools.com/java/java_while_loop.asp">W3Schools — Java While Loop</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Practice Loops</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Programming</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.5 A Worked Example: ZESCO Token Purchase Calculator',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to combine if-statements, loops, and arithmetic operators to build a realistic ZESCO token calculator in Java, validate simple input conditions, and display a clear summary of units purchased and total cost.</p>

<h2>Understanding the Problem</h2>
<p>A ZESCO token purchase involves several decisions. The customer enters a meter number, chooses an amount in Kwacha, and pays. In our simplified calculator, we will assume that each Kwacha buys a fixed number of units, for example 2 units per Kwacha. The program should:</p>
<ol>
<li>Check that the meter number is not empty.</li>
<li>Check that the purchase amount is at least K20.</li>
<li>Calculate the number of units.</li>
<li>Add a small transaction fee if the amount is below K100.</li>
<li>Print a summary.</li>
</ol>

<h2>Worked Example</h2>
<pre><code>public class ZescoCalculator {
    public static void main(String[] args) {
        String meterNumber = "12345678901";
        double amount = 75.00;
        final double UNITS_PER_KWACHA = 2.0;
        final double MINIMUM_PURCHASE = 20.00;
        final double SMALL_FEE = 2.50;

        if (meterNumber.length() &lt; 5) {
            System.out.println("Error: Meter number is too short.");
        } else if (amount &lt; MINIMUM_PURCHASE) {
            System.out.println("Error: Minimum purchase is K" + MINIMUM_PURCHASE);
        } else {
            double fee = 0.0;
            if (amount &lt; 100) {
                fee = SMALL_FEE;
            }

            double units = amount * UNITS_PER_KWACHA;
            double total = amount + fee;

            System.out.println("ZESCO Token Receipt");
            System.out.println("Meter: " + meterNumber);
            System.out.println("Units: " + units);
            System.out.println("Amount: K" + amount);
            System.out.println("Fee: K" + fee);
            System.out.println("Total paid: K" + total);
        }
    }
}
</code></pre>

<h2>Worked Example: Bulk Purchase Discount</h2>
<p>ZESCO sometimes offers better rates for larger purchases. We can extend the calculator to add bonus units when the purchase amount is K200 or more.</p>
<pre><code>public class ZescoBulkCalculator {
    public static void main(String[] args) {
        String meterNumber = "12345678901";
        double amount = 250.00;
        final double UNITS_PER_KWACHA = 2.0;
        final double BONUS_RATE = 0.05;

        double units = amount * UNITS_PER_KWACHA;

        if (amount &gt;= 200) {
            units = units + (units * BONUS_RATE);
        }

        double total = amount;

        System.out.println("ZESCO Bulk Token Receipt");
        System.out.println("Meter: " + meterNumber);
        System.out.println("Amount: K" + amount);
        System.out.println("Units including bonus: " + units);
        System.out.println("Total paid: K" + total);
    }
}
</code></pre>
<p>The customer receives 525 units instead of 500 because the K250 purchase qualifies for the 5 percent bonus. This shows how business rules can be added without rewriting the whole program.</p>

<h2>Common Mistakes</h2>
<ul>
<li>Validating input after using it in a calculation.</li>
<li>Using <code>=</code> instead of <code>==</code> or forgetting to check the correct length.</li>
<li>Hard-coding values such as the minimum purchase instead of using constants.</li>
<li>Forgetting that the nested if checks the amount after the outer if has already confirmed the meter number.</li>
</ul>

<h2>Real-World Connection</h2>
<p>ZESCO token purchases, mobile money top-ups, and airtime purchases all follow a similar pattern: validate input, apply business rules, calculate totals, and print a receipt. Java developers working for utility companies and fintech startups in Zambia build systems like this every day. A well-tested calculator prevents errors that could cost customers money or cause failed transactions.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>ZescoCalculator.java</code>.</li>
<li>Type the program above and run it with different amounts and meter numbers.</li>
<li>Change the constants so that 3 units are purchased per Kwacha.</li>
<li>Add a loop that prints the units added for each K10 increment up to the purchase amount.</li>
<li>Add a discount so that purchases of K200 or more receive 5 percent extra units.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Input validation</strong> — checking that user input meets requirements before processing it.</li>
<li><strong>Transaction fee</strong> — a small charge added to a purchase.</li>
<li><strong>Nested if</strong> — an if statement placed inside another if statement.</li>
<li><strong>Summary</strong> — a concise report showing the important results of a calculation.</li>
<li><strong>Constant</strong> — a value that does not change, such as the units per Kwacha in this example.</li>
</ul>

<h2>Summary</h2>
<p>This lesson combined control flow concepts to build a practical ZESCO token calculator. You used if-statements to validate input, constants to represent business rules, and arithmetic to calculate totals. As you learn more Java, you can extend this calculator to read user input, save receipts to a file, and handle multiple payment methods.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_conditions.asp">W3Schools — Java Conditions</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Build Projects</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Training</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Programming</a></li>
</ul>
HTML,
            ],
        ];
    }


    private function module4Lessons(): array
    {
        return [
            [
                'title' => '4.1 Writing and Calling Methods',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to define your own methods in Java, call methods from the main method, understand the purpose of method signatures, and organise your code into reusable blocks.</p>

<h2>What Is a Method?</h2>
<p>A <strong>method</strong> is a named block of code that performs a specific task. You have already used the <code>main</code> method and <code>System.out.println</code>. Now you will learn to write your own methods. Methods help you avoid repeating code, make programs easier to read, and divide large problems into smaller, manageable pieces.</p>

<h2>Defining a Simple Method</h2>
<p>A method definition has a return type, a name, parentheses, and a body. Here is a method that prints a welcome message:</p>
<pre><code>public static void printWelcome() {
    System.out.println("Welcome to Edutrack Java!");
}
</code></pre>
<p>The keyword <code>void</code> means the method does not return a value. The method name is <code>printWelcome</code>. The empty parentheses mean the method takes no information. To run the method, you <strong>call</strong> it like this:</p>
<pre><code>public class MethodDemo {
    public static void main(String[] args) {
        printWelcome();
    }

    public static void printWelcome() {
        System.out.println("Welcome to Edutrack Java!");
    }
}
</code></pre>

<h2>Methods with Parameters</h2>
<p>Methods become more useful when they accept <strong>parameters</strong>, which are values passed into the method. For example:</p>
<pre><code>public static void greet(String name) {
    System.out.println("Hello, " + name + "!");
}
</code></pre>
<p>You call it with <code>greet("Mary")</code>, and it prints "Hello, Mary!"</p>

<h2>Method Scope</h2>
<p>Variables declared inside a method are only visible inside that method. This is called <strong>scope</strong>. If you declare a variable inside <code>main</code>, another method cannot use it unless you pass it as a parameter. Keeping variables scoped to the methods that need them reduces confusion and prevents accidental changes.</p>

<h2>Worked Example: A Simple Interest Calculator</h2>
<p>A method can hide the details of a calculation. Here is a program with a method that calculates simple interest.</p>
<pre><code>public class InterestCalculator {
    public static void main(String[] args) {
        double interest = calculateInterest(1000, 0.05, 2);
        System.out.println("Interest earned: K" + interest);
    }

    public static double calculateInterest(double principal, double rate, int years) {
        return principal * rate * years;
    }
}
</code></pre>
<p>The method takes three parameters, performs the calculation, and returns the result. The main method prints the returned value.</p>

<h2>Worked Example: Printing a Receipt Header</h2>
<p>A shop program might reuse the same header on every receipt. A method keeps the header format in one place.</p>
<pre><code>public class ReceiptHeader {
    public static void main(String[] args) {
        printShopHeader();
        System.out.println("Item: Solar lamp");
        System.out.println("Price: K450.00");
        printShopHeader();
    }

    public static void printShopHeader() {
        System.out.println("========================");
        System.out.println("  KALOMO GENERAL STORE  ");
        System.out.println("========================");
    }
}
</code></pre>
<p>Whenever the shop name or design changes, only the header method needs updating.</p>

<h2>Common Mistakes</h2>
<ul>
<li>Calling a method that is not static from a static main method without creating an object.</li>
<li>Forgetting the return type in the method declaration.</li>
<li>Declaring a variable inside a method and trying to use it in another method.</li>
<li>Misspelling the method name when calling it.</li>
</ul>

<h2>Real-World Connection</h2>
<p>Methods are the building blocks of large software systems. A banking application might have methods for deposit, withdraw, transfer, and check balance. A school management system might have methods for enrolment, grading, and report generation. By breaking software into methods, teams of developers can work on different parts of the same project without stepping on each other's work.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>MethodsPractice.java</code>.</li>
<li>Write a method named <code>printHeader</code> that prints the name of your college.</li>
<li>Write a method named <code>add</code> that takes two int parameters and returns their sum. Call it from main and print the result.</li>
<li>Write a method named <code>convertToKwacha</code> that takes an amount in ngwee as an int and returns the equivalent in Kwacha as a double.</li>
<li>Call each method at least twice with different values.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Method</strong> — a named block of code that performs a specific task.</li>
<li><strong>Parameter</strong> — a value passed into a method when it is called.</li>
<li><strong>Return type</strong> — the type of value a method sends back to its caller.</li>
<li><strong>Method call</strong> — the act of running a method by writing its name and arguments.</li>
<li><strong>void</strong> — a return type that means the method does not return a value.</li>
</ul>

<h2>Summary</h2>
<p>Methods help you write organised, reusable Java programs. You can define methods that take parameters, perform calculations, and return results. Learning to break problems into methods is one of the most important steps in becoming a confident programmer.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_methods.asp">W3Schools — Java Methods</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Learn Java</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Programming</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Training</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.2 Parameters and Return Values',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to pass multiple parameters to a method, return values of different data types, and understand how the return statement ends a method and sends a result back to the caller.</p>

<h2>Passing Multiple Parameters</h2>
<p>A method can accept more than one parameter. Each parameter has its own type and name, and parameters are separated by commas. For example, a method that calculates the cost of a taxi ride might need distance and price per kilometre:</p>
<pre><code>public static double taxiFare(double distanceKm, double pricePerKm) {
    return distanceKm * pricePerKm;
}
</code></pre>
<p>When you call the method, you provide values in the same order:</p>
<pre><code>double fare = taxiFare(15.5, 8.00);
</code></pre>

<h2>The return Statement</h2>
<p>The <code>return</code> statement does two things: it ends the method immediately, and it sends a value back to the caller. A method declared with a non-void return type must return a value of that type. For example, a method declared as <code>public static int square(int x)</code> must return an int.</p>

<h2>Returning Different Data Types</h2>
<p>A method can return any data type: int, double, String, boolean, or even more complex types. Choose the return type that matches the result you want to send back. If a method only performs an action and has nothing to send back, declare it as <code>void</code>.</p>

<h2>Worked Example: Currency Conversion</h2>
<p>A small business imports goods and wants to convert prices from US dollars to Zambian Kwacha using a fixed exchange rate.</p>
<pre><code>public class CurrencyConverter {
    public static void main(String[] args) {
        double priceInKwacha = convertUsdToKwacha(45.00, 25.50);
        System.out.println("Price in Kwacha: K" + priceInKwacha);
    }

    public static double convertUsdToKwacha(double usd, double exchangeRate) {
        return usd * exchangeRate;
    }
}
</code></pre>
<p>The method multiplies the dollar amount by the exchange rate and returns the Kwacha amount. The main method stores and prints the result.</p>

<h2>Worked Example: Calculating Change</h2>
<p>A cashier wants a method that calculates the change due when a customer pays more than the purchase price.</p>
<pre><code>public class ChangeCalculator {
    public static void main(String[] args) {
        double purchase = 185.50;
        double amountPaid = 200.00;

        double change = calculateChange(amountPaid, purchase);
        System.out.println("Change due: K" + change);
    }

    public static double calculateChange(double paid, double cost) {
        return paid - cost;
    }
}
</code></pre>
<p>This simple method can be reused in any part of a shop program that handles cash payments.</p>

<h2>Common Mistakes</h2>
<ul>
<li>Passing arguments in the wrong order, which causes incorrect calculations.</li>
<li>Forgetting to use the returned value; the result is lost if you do not store or print it.</li>
<li>Declaring a method as <code>void</code> and then trying to return a value.</li>
<li>Returning a value of the wrong type, which causes a compiler error.</li>
</ul>

<h2>Real-World Connection</h2>
<p>Parameters and return values allow programs to behave like real-world calculators and converters. An e-commerce site uses them to compute shipping costs based on weight and destination. A currency exchange app uses them to convert between Kwacha and dollars. A ride-hailing app uses them to calculate fares from distance and time. These small, reusable methods are the foundation of larger business applications.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>ReturnValuesPractice.java</code>.</li>
<li>Write a method <code>calculateVAT</code> that takes a price and a VAT rate and returns the VAT amount.</li>
<li>Write a method <code>calculateDiscount</code> that takes a price and a discount percentage and returns the discounted price.</li>
<li>Call both methods from main and print a receipt showing original price, VAT, discount, and total.</li>
<li>Experiment by changing the parameter values and observe how the output changes.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Parameter list</strong> — the set of parameters declared in a method's signature.</li>
<li><strong>Return value</strong> — the value a method sends back to the code that called it.</li>
<li><strong>Argument</strong> — the actual value passed to a method when it is called.</li>
<li><strong>Method signature</strong> — the combination of a method's name and parameter list.</li>
<li><strong>Exchange rate</strong> — the value of one currency expressed in terms of another.</li>
</ul>

<h2>Summary</h2>
<p>Parameters let methods receive input, and return values let methods produce output. By combining parameters and return values, you can build methods that perform calculations, transform data, and hide complexity from the rest of your program.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_methods_param.asp">W3Schools — Java Method Parameters</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Coding Challenges</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Programming</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Training</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.3 Method Overloading',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to define multiple methods with the same name but different parameters, explain how Java decides which method to call, and use overloading to make your programs more flexible and readable.</p>

<h2>What Is Method Overloading?</h2>
<p><strong>Method overloading</strong> means having two or more methods with the same name in the same class, as long as their parameter lists are different. The difference can be in the number of parameters or the types of parameters. Java uses the method signature, which includes the parameter types, to decide which version to call.</p>

<h2>Example of Overloading</h2>
<pre><code>public class Calculator {
    public static int add(int a, int b) {
        return a + b;
    }

    public static double add(double a, double b) {
        return a + b;
    }

    public static int add(int a, int b, int c) {
        return a + b + c;
    }
}
</code></pre>
<p>The <code>add</code> method is overloaded three times. The first version works with integers, the second with doubles, and the third with three integers.</p>

<h2>How Java Chooses the Right Method</h2>
<p>When you call an overloaded method, Java looks at the number and types of arguments you provide. It then selects the method whose parameters match most closely. If no exact match exists, Java may convert a value to a compatible type, such as converting an int to a double.</p>

<h2>Worked Example: Area Calculator</h2>
<p>A farmer wants to calculate the area of rectangular plots. Some plots are measured in metres and others in whole metres plus centimetres.</p>
<pre><code>public class AreaCalculator {
    public static void main(String[] args) {
        System.out.println("Area in m2: " + rectangleArea(10, 20));
        System.out.println("Area in m2: " + rectangleArea(10.5, 20.5));
    }

    public static int rectangleArea(int length, int width) {
        return length * width;
    }

    public static double rectangleArea(double length, double width) {
        return length * width;
    }
}
</code></pre>

<h2>Worked Example: Bus Fare Overloading</h2>
<p>A bus company charges different rates for adults, students, and groups. Overloading lets us use one method name, <code>calculateFare</code>, for all cases.</p>
<pre><code>public class BusFare {
    public static void main(String[] args) {
        System.out.println("Adult fare: K" + calculateFare(180.00));
        System.out.println("Student fare: K" + calculateFare(180.00, 0.5));
        System.out.println("Group fare: K" + calculateFare(180.00, 4, 0.1));
    }

    public static double calculateFare(double baseFare) {
        return baseFare;
    }

    public static double calculateFare(double baseFare, double discountRate) {
        return baseFare * (1 - discountRate);
    }

    public static double calculateFare(double baseFare, int passengers, double groupDiscount) {
        return baseFare * passengers * (1 - groupDiscount);
    }
}
</code></pre>

<h2>Common Mistakes</h2>
<ul>
<li>Creating two methods with the same name and the same parameter types. Java cannot tell them apart.</li>
<li>Relying only on different return types to overload a method. Java does not allow this.</li>
<li>Calling an overloaded method with arguments that match more than one version, causing confusion.</li>
<li>Overloading too many similar methods, which can make the code harder to read.</li>
</ul>

<h2>Real-World Connection</h2>
<p>Method overloading appears in many libraries and frameworks. Java's own <code>System.out.println</code> is overloaded to accept Strings, integers, doubles, and other types. Payment systems might overload a <code>processPayment</code> method to handle cash, mobile money, and card transactions. Overloading makes APIs easier to learn because developers can use familiar method names for related operations.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>OverloadingPractice.java</code>.</li>
<li>Write two overloaded methods named <code>printPrice</code>. One accepts an int representing ngwee, and the other accepts a double representing Kwacha.</li>
<li>Write overloaded <code>calculateTotal</code> methods: one for two prices, another for three prices.</li>
<li>Call each version from main and print the results.</li>
<li>Explain to a classmate why Java can tell the methods apart even though they share a name.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Method overloading</strong> — defining multiple methods with the same name but different parameter lists.</li>
<li><strong>Signature</strong> — a method's name plus its parameter types, used by Java to identify overloaded methods.</li>
<li><strong>Compile-time polymorphism</strong> — another name for method overloading, because the correct method is chosen when the program is compiled.</li>
<li><strong>Parameter type</strong> — the data type of a parameter, such as int or double.</li>
<li><strong>Flexible</strong> — able to work with different kinds of input.</li>
</ul>

<h2>Summary</h2>
<p>Method overloading allows you to create multiple versions of a method that handle different data types or numbers of arguments. This makes your code cleaner and easier to use because callers can use one familiar method name for related tasks.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_methods_overloading.asp">W3Schools — Java Method Overloading</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Java Lessons</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Programming</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Training</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.4 Arrays: Declaring, Filling, and Looping',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to declare arrays in Java, store multiple values of the same type, access individual elements by index, and use loops to process every element in an array.</p>

<h2>What Is an Array?</h2>
<p>An <strong>array</strong> is a collection of values that have the same data type. Instead of creating ten separate variables for ten test scores, you can create one array that holds all ten scores. Arrays are useful whenever you need to work with a list of related items, such as daily rainfall amounts, bus fares to different towns, or names of students in a class.</p>

<h2>Declaring and Initialising Arrays</h2>
<p>You can declare an array and initialise it with values at the same time:</p>
<pre><code>int[] scores = {78, 85, 92, 67, 88};
String[] towns = {"Kalomo", "Choma", "Livingstone", "Lusaka"};
</code></pre>
<p>Alternatively, you can declare an array of a specific size and fill it later:</p>
<pre><code>double[] rainfall = new double[12];
rainfall[0] = 150.5;
rainfall[1] = 120.0;
</code></pre>
<p>Array indices start at 0, so the first element is at index 0 and the last element is at index length minus 1.</p>

<h2>Filling an Array with a Loop</h2>
<p>You can use a loop to set the values of an array instead of typing each one. This is especially useful for large arrays.</p>
<pre><code>int[] squares = new int[10];
for (int i = 0; i &lt; squares.length; i++) {
    squares[i] = (i + 1) * (i + 1);
}
</code></pre>
<p>After this loop, the array contains the squares of the numbers 1 through 10.</p>

<h2>Worked Example: Finding the Highest Score</h2>
<pre><code>public class HighestScore {
    public static void main(String[] args) {
        int[] scores = {78, 85, 92, 67, 88};
        int highest = scores[0];

        for (int i = 1; i &lt; scores.length; i++) {
            if (scores[i] &gt; highest) {
                highest = scores[i];
            }
        }

        System.out.println("The highest score is: " + highest);
    }
}
</code></pre>

<h2>Worked Example: Monthly Rainfall Average</h2>
<p>A farmer in Southern Province records rainfall for each month of the year. The program calculates the total and average rainfall.</p>
<pre><code>public class RainfallAverage {
    public static void main(String[] args) {
        double[] rainfall = {150.5, 120.0, 95.5, 45.0, 10.0, 0.0, 0.0, 2.5, 25.0, 70.0, 130.0, 160.0};
        double total = 0.0;

        for (int month = 0; month &lt; rainfall.length; month++) {
            total += rainfall[month];
        }

        double average = total / rainfall.length;
        System.out.println("Total rainfall: " + total + " mm");
        System.out.println("Average rainfall: " + average + " mm");
    }
}
</code></pre>

<h2>Common Mistakes</h2>
<ul>
<li>Trying to access an index that is too large, such as index 5 in a 5-element array.</li>
<li>Forgetting that array indices start at 0, not 1.</li>
<li>Using <code>length()</code> instead of <code>length</code> for arrays. <code>length()</code> is for Strings.</li>
<li>Creating an array but never initialising its elements before using them.</li>
</ul>

<h2>Real-World Connection</h2>
<p>Arrays are used to store lists of data in almost every application. A school stores student marks in arrays. A weather station stores daily temperature readings. A bus company stores passenger counts for each route. A shop stores prices for all products. Learning to declare, fill, and loop through arrays prepares you to handle the lists of information that appear in real Zambian businesses and government systems.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>ArraysPractice.java</code>.</li>
<li>Create an array of double values representing the prices of five items in a shop.</li>
<li>Use a loop to calculate and print the total price.</li>
<li>Write a loop that finds the lowest price in the array.</li>
<li>Create a String array containing the names of four Zambian provinces and print each name.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Array</strong> — a collection of values of the same type stored under one name.</li>
<li><strong>Index</strong> — a number that identifies the position of an element in an array.</li>
<li><strong>Element</strong> — a single value stored in an array.</li>
<li><strong>Length</strong> — the number of elements an array can hold, accessed with <code>.length</code>.</li>
<li><strong>Iterate</strong> — to process each element of an array one by one.</li>
</ul>

<h2>Summary</h2>
<p>Arrays allow you to store and process collections of related data. You declare an array with square brackets, access elements by index starting from zero, and use loops to perform calculations across the whole collection. Arrays are the foundation for more advanced data structures you will learn later.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_arrays.asp">W3Schools — Java Arrays</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Practice Arrays</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Programming</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Training</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.5 A Worked Example: Mini Bus Fare Calculator Using Arrays',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to combine arrays, methods, and loops to build a mini bus fare calculator, calculate totals and averages from array data, and present a simple report that could help a bus owner track income.</p>

<h2>Planning the Calculator</h2>
<p>A small bus operator travels between Kalomo and several destinations. The bus has a fixed fare for each route, and the number of passengers varies each day. The program will store routes and fares in arrays, calculate daily income for each route, and produce a summary with the total and average income.</p>

<h2>Worked Example</h2>
<pre><code>public class BusFareCalculator {
    public static void main(String[] args) {
        String[] routes = {"Kalomo-Choma", "Kalomo-Livingstone", "Kalomo-Lusaka"};
        double[] fares = {45.00, 90.00, 180.00};
        int[] passengers = {25, 18, 12};

        double totalIncome = 0.0;

        System.out.println("Daily Bus Income Report");
        System.out.println("-----------------------");

        for (int i = 0; i &lt; routes.length; i++) {
            double income = fares[i] * passengers[i];
            totalIncome += income;
            System.out.println(routes[i] + ": " + passengers[i] + " passengers = K" + income);
        }

        double averageIncome = totalIncome / routes.length;
        System.out.println("-----------------------");
        System.out.println("Total income: K" + totalIncome);
        System.out.println("Average income per route: K" + averageIncome);
    }
}
</code></pre>

<h2>Worked Example: Route with Highest Income</h2>
<p>A bus owner wants to know which route earns the most money so that he can add more buses on busy days.</p>
<pre><code>public class TopRoute {
    public static void main(String[] args) {
        String[] routes = {"Kalomo-Choma", "Kalomo-Livingstone", "Kalomo-Lusaka"};
        double[] fares = {45.00, 90.00, 180.00};
        int[] passengers = {25, 18, 12};

        double highestIncome = 0.0;
        String topRoute = "";

        for (int i = 0; i &lt; routes.length; i++) {
            double income = fares[i] * passengers[i];
            if (income &gt; highestIncome) {
                highestIncome = income;
                topRoute = routes[i];
            }
        }

        System.out.println("Top route: " + topRoute);
        System.out.println("Income: K" + highestIncome);
    }
}
</code></pre>
<p>The program finds that Kalomo-Lusaka earns the most money because it has the highest fare, even with fewer passengers than the Kalomo-Choma route.</p>

<h2>Common Mistakes</h2>
<ul>
<li>Using parallel arrays of different lengths, which causes an index out of bounds error.</li>
<li>Using the wrong index when reading from one array and writing to another.</li>
<li>Dividing the total by the wrong number, such as the number of fares instead of the number of routes.</li>
<li>Forgetting to initialise the total to zero before adding values in a loop.</li>
</ul>

<h2>Best Practice</h2>
<p>When you use parallel arrays, keep them the same length and document which index represents which item. Consider creating a class to group related data instead of relying on many parallel arrays. Classes make the code safer and easier to understand, especially as the program grows.</p>

<h2>Real-World Connection</h2>
<p>Transport companies in Zambia need to track income by route, fuel costs, and passenger numbers. A simple calculator like this one helps owners decide where to allocate buses and when to adjust fares. Larger companies use database systems, but the logic of arrays, loops, and totals is the same. This lesson shows how a small Java program can support business decisions.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>BusFareCalculator.java</code>.</li>
<li>Type the program above and run it.</li>
<li>Add a fourth route and update the arrays accordingly.</li>
<li>Write a method <code>calculateIncome</code> that takes fare and passengers as parameters and returns the income. Call it inside the loop.</li>
<li>Add a feature that prints a special message for the route with the highest income.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Parallel arrays</strong> — two or more arrays of the same length where related data is stored at the same index.</li>
<li><strong>Daily income</strong> — the total money earned from sales or services in one day.</li>
<li><strong>Average</strong> — the total divided by the number of items.</li>
<li><strong>Report</strong> — a formatted summary of information.</li>
<li><strong>Route</strong> — a fixed path between two places.</li>
</ul>

<h2>Summary</h2>
<p>This lesson combined arrays, loops, and methods to create a practical bus fare calculator. Parallel arrays let you store related information such as routes, fares, and passenger counts. By processing the arrays in a loop, you can calculate totals and averages efficiently and produce a useful report.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_arrays.asp">W3Schools — Java Arrays</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Build Projects</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Training</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Programming</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module5Lessons(): array
    {
        return [
            [
                'title' => '5.1 Classes and Objects',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to define a simple class in Java, create objects from that class, understand the difference between a class and an object, and use objects to model real-world entities such as products and customers.</p>

<h2>From Real Things to Code</h2>
<p>In everyday life, you interact with objects: a bag of mealie meal, a mobile phone, a customer, a bus. Each object has <strong>attributes</strong> that describe it and <strong>behaviours</strong> that it can perform. In Java, a <strong>class</strong> is a blueprint that defines what attributes and behaviours an object will have. An <strong>object</strong> is a specific instance created from that blueprint.</p>

<h2>Defining a Class</h2>
<p>Here is a simple class that represents a product in a shop:</p>
<pre><code>public class Product {
    String name;
    double price;
    int quantity;
}
</code></pre>
<p>This class says that every product has a name, a price, and a quantity. To create an object, you use the <code>new</code> keyword:</p>
<pre><code>Product rice = new Product();
rice.name = "Rice 5kg";
rice.price = 55.00;
rice.quantity = 20;
</code></pre>

<h2>Class vs Object</h2>
<p>A class is like a recipe, while an object is the actual meal cooked from that recipe. One class can create many objects. All objects created from the same class share the same structure, but each object has its own values. For example, the Product class can create objects for rice, sugar, and cooking oil, each with different prices and quantities.</p>

<h2>Worked Example: Modelling a Customer</h2>
<pre><code>public class Customer {
    String firstName;
    String lastName;
    String phoneNumber;
    double balance;
}
</code></pre>
<pre><code>public class CustomerDemo {
    public static void main(String[] args) {
        Customer customer = new Customer();
        customer.firstName = "Grace";
        customer.lastName = "Banda";
        customer.phoneNumber = "+260 97X XXX XXX";
        customer.balance = 150.00;

        System.out.println("Customer: " + customer.firstName + " " + customer.lastName);
        System.out.println("Phone: " + customer.phoneNumber);
        System.out.println("Balance: K" + customer.balance);
    }
}
</code></pre>

<h2>Worked Example: Modelling a Student</h2>
<p>A college can use a Student class to track each learner's progress.</p>
<pre><code>public class Student {
    String name;
    String course;
    double averageScore;
}

public class StudentDemo {
    public static void main(String[] args) {
        Student student1 = new Student();
        student1.name = "John Musonda";
        student1.course = "Java Programming";
        student1.averageScore = 78.5;

        Student student2 = new Student();
        student2.name = "Mary Zulu";
        student2.course = "Java Programming";
        student2.averageScore = 85.0;

        System.out.println(student1.name + " scored " + student1.averageScore);
        System.out.println(student2.name + " scored " + student2.averageScore);
    }
}
</code></pre>

<h2>Common Mistakes</h2>
<ul>
<li>Confusing the class name with the object name. The class is the blueprint; the object is the instance.</li>
<li>Forgetting to use <code>new</code> when creating an object.</li>
<li>Trying to access a field before assigning a value to it.</li>
<li>Giving a class and a file different names when the class is public.</li>
</ul>

<h2>Real-World Connection</h2>
<p>Object-oriented design is used in systems that manage students, patients, bank accounts, and products. The University of Zambia student records system, hospital patient management systems, and bank account databases all use classes to represent real-world entities. By learning classes and objects, you are learning the same design approach used by professional software engineers across Africa.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>Product.java</code> with a Product class containing name, price, and quantity fields.</li>
<li>Create a second file named <code>ShopDemo.java</code> with a main method.</li>
<li>In main, create two Product objects, assign values to their fields, and print the details of each product.</li>
<li>Calculate the total value of each product by multiplying price by quantity.</li>
<li>Create a Student class with fields for name, course, and average score. Create two student objects and print their details.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Class</strong> — a blueprint that defines the attributes and behaviours of a type of object.</li>
<li><strong>Object</strong> — a specific instance created from a class.</li>
<li><strong>Field</strong> — a variable declared inside a class that stores an attribute of an object.</li>
<li><strong>Instance</strong> — another word for an object created from a class.</li>
<li><strong>new</strong> — the Java keyword used to create a new object.</li>
</ul>

<h2>Summary</h2>
<p>Classes and objects are the foundation of object-oriented programming. A class is a blueprint, and an object is a specific example built from that blueprint. By modelling real things as classes, you can write code that is easier to understand, maintain, and extend.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_classes.asp">W3Schools — Java Classes and Objects</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Object-Oriented Programming</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Programming</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Training</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.2 Fields, Constructors, Getters, and Setters',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to write constructors to initialise objects, create getter and setter methods to control access to fields, and explain how encapsulation improves the quality of your Java code.</p>

<h2>Constructors</h2>
<p>A <strong>constructor</strong> is a special method that runs when an object is created. It has the same name as the class and no return type. Constructors are used to set the initial values of an object's fields. For example:</p>
<pre><code>public class Product {
    String name;
    double price;
    int quantity;

    public Product(String name, double price, int quantity) {
        this.name = name;
        this.price = price;
        this.quantity = quantity;
    }
}
</code></pre>
<p>You create an object and set its values in one line:</p>
<pre><code>Product rice = new Product("Rice 5kg", 55.00, 20);
</code></pre>

<h2>The this Keyword</h2>
<p>The <code>this</code> keyword refers to the current object. When a constructor parameter has the same name as a field, <code>this.name</code> refers to the field and <code>name</code> refers to the parameter. Using <code>this</code> removes ambiguity and makes the code clear.</p>

<h2>Getters and Setters</h2>
<p><strong>Getter</strong> methods return the value of a field, and <strong>setter</strong> methods change it. They are useful because they let you control how fields are accessed and modified. For example, a setter for price could reject negative values.</p>
<pre><code>public void setPrice(double price) {
    if (price &gt;= 0) {
        this.price = price;
    }
}

public double getPrice() {
    return price;
}
</code></pre>

<h2>Worked Example: A Bank Account Class</h2>
<pre><code>public class BankAccount {
    private String accountNumber;
    private double balance;

    public BankAccount(String accountNumber, double balance) {
        this.accountNumber = accountNumber;
        this.balance = balance;
    }

    public void deposit(double amount) {
        if (amount &gt; 0) {
            balance += amount;
        }
    }

    public double getBalance() {
        return balance;
    }
}
</code></pre>

<h2>Worked Example: A Student Record</h2>
<p>A college uses getters and setters to protect student marks from invalid changes.</p>
<pre><code>public class Student {
    private String name;
    private double averageScore;

    public Student(String name, double averageScore) {
        this.name = name;
        setAverageScore(averageScore);
    }

    public void setAverageScore(double averageScore) {
        if (averageScore &gt;= 0 &amp;&amp; averageScore &lt;= 100) {
            this.averageScore = averageScore;
        }
    }

    public double getAverageScore() {
        return averageScore;
    }
}
</code></pre>

<h2>Common Mistakes</h2>
<ul>
<li>Trying to return a value from a constructor. Constructors have no return type.</li>
<li>Forgetting <code>this</code> when parameter names match field names, which leaves fields uninitialised.</li>
<li>Writing a getter that modifies the field instead of just returning it.</li>
<li>Making setters that accept any value without validation.</li>
</ul>

<h2>Real-World Connection</h2>
<p>Getters and setters are used in almost every Java application. Banking software uses them to protect account balances. E-commerce sites use them to manage product prices and stock levels. Student information systems use them to ensure marks stay within valid ranges. These small methods are a key part of writing safe, professional code.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>Student.java</code> with private fields for name and score.</li>
<li>Add a constructor, getters, and a setter for the score that rejects negative values.</li>
<li>Create a file named <code>StudentDemo.java</code> that creates two students and prints their scores.</li>
<li>Try to set a negative score and observe that the setter prevents it.</li>
<li>Explain to a classmate why getters and setters are better than making fields public.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Constructor</strong> — a special method that initialises a new object.</li>
<li><strong>Getter</strong> — a method that returns the value of a private field.</li>
<li><strong>Setter</strong> — a method that modifies the value of a private field.</li>
<li><strong>Encapsulation</strong> — the practice of hiding internal details and exposing only controlled access.</li>
<li><strong>private</strong> — an access modifier that restricts access to within the class.</li>
</ul>

<h2>Summary</h2>
<p>Constructors make object creation safer and more convenient. Getters and setters protect fields from invalid changes and make code easier to maintain. Together, these techniques implement encapsulation, a core principle of object-oriented programming.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_constructors.asp">W3Schools — Java Constructors</a></li>
<li><a href="https://www.w3schools.com/java/java_encapsulation.asp">W3Schools — Java Encapsulation</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — OOP Concepts</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Programming</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.3 Modelling a Shop or Bus Company',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to design a small object-oriented model for a real Zambian business, create multiple related classes, and write a main program that demonstrates how objects interact.</p>

<h2>Designing a Simple Business Model</h2>
<p>Object-oriented design begins by identifying the important entities in a problem. For a shop, the entities might include Product, Customer, and Sale. For a bus company, the entities might include Bus, Route, and Ticket. Each entity becomes a class with appropriate fields and methods.</p>

<h2>Worked Example: A Simple Shop Model</h2>
<p>Here is a small model for a shop in Soweto Market. The Product class holds item details, and the Sale class records a transaction.</p>
<pre><code>public class Product {
    private String name;
    private double price;

    public Product(String name, double price) {
        this.name = name;
        this.price = price;
    }

    public double getPrice() {
        return price;
    }

    public String getName() {
        return name;
    }
}

public class Sale {
    private Product product;
    private int quantity;

    public Sale(Product product, int quantity) {
        this.product = product;
        this.quantity = quantity;
    }

    public double getTotal() {
        return product.getPrice() * quantity;
    }
}
</code></pre>

<h2>Worked Example: A Bus Company Model</h2>
<p>A bus company can be modelled with Bus, Route, and Ticket classes.</p>
<pre><code>public class Bus {
    private String registrationNumber;
    private int capacity;

    public Bus(String registrationNumber, int capacity) {
        this.registrationNumber = registrationNumber;
        this.capacity = capacity;
    }

    public int getCapacity() {
        return capacity;
    }
}

public class Route {
    private String name;
    private double fare;

    public Route(String name, double fare) {
        this.name = name;
        this.fare = fare;
    }

    public double getFare() {
        return fare;
    }
}

public class Ticket {
    private Route route;
    private int quantity;

    public Ticket(Route route, int quantity) {
        this.route = route;
        this.quantity = quantity;
    }

    public double getTotal() {
        return route.getFare() * quantity;
    }
}
</code></pre>

<h2>Common Mistakes</h2>
<ul>
<li>Creating one giant class that tries to handle everything instead of splitting responsibilities.</li>
<li>Forgetting to initialise related objects before passing them to another class.</li>
<li>Using public fields instead of getters and setters, which breaks encapsulation.</li>
<li>Naming classes as verbs instead of nouns. Classes should represent things, not actions.</li>
</ul>

<h2>Best Practice</h2>
<p>Start your design by listing the nouns in the problem description. Each important noun is a candidate for a class. Then list what each noun knows (fields) and what it does (methods). Keep classes focused on one responsibility. A Product class should manage product data; a Sale class should manage transaction data. This separation makes the code easier to test and extend.</p>

<h2>Real-World Connection</h2>
<p>Zambian businesses of all sizes use software models similar to the ones in this lesson. A shop in Kalomo might track products and sales. A bus company might track routes, buses, and tickets. A farm cooperative might track members, deliveries, and payments. Object-oriented design helps developers create software that matches how the real business works.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create files named <code>Product.java</code>, <code>Sale.java</code>, and <code>ShopDemo.java</code>.</li>
<li>Implement the Product and Sale classes shown above.</li>
<li>In ShopDemo, create a few products, create sales, and print the total for each sale.</li>
<li>Add a Customer class with name and phone number fields. Modify Sale to include a customer.</li>
<li>Calculate the grand total of all sales in the main method.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Entity</strong> — a real-world object or concept that becomes a class in a program.</li>
<li><strong>Model</strong> — a simplified representation of a real system in code.</li>
<li><strong>Transaction</strong> — a business event such as a sale or payment.</li>
<li><strong>Relationship</strong> — a connection between two classes, such as a Sale having a Product.</li>
<li><strong>Object interaction</strong> — the way objects call each other's methods to accomplish tasks.</li>
</ul>

<h2>Summary</h2>
<p>Object-oriented design helps you model real businesses by turning entities into classes and relationships into object interactions. A well-designed model makes it easier to add new features later, such as discounts, receipts, or inventory tracking.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_classes.asp">W3Schools — Java Classes</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Object-Oriented Programming</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Programming</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Training</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.4 Inheritance and Method Overriding',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create a subclass that inherits from a parent class, override methods to provide specialised behaviour, and explain how inheritance promotes code reuse in object-oriented programs.</p>

<h2>What Is Inheritance?</h2>
<p><strong>Inheritance</strong> allows one class to acquire the fields and methods of another class. The class that is inherited from is called the <strong>parent</strong> or <strong>superclass</strong>. The class that inherits is called the <strong>child</strong> or <strong>subclass</strong>. Inheritance is useful when several classes share common features but also have their own special behaviours.</p>

<h2>Example: Vehicles</h2>
<p>Imagine you are writing a program for a transport company. You might have a Vehicle class with fields such as registrationNumber and capacity, and methods such as move(). Subclasses such as Bus and Truck can inherit from Vehicle and add their own specific features.</p>
<pre><code>public class Vehicle {
    protected String registrationNumber;

    public Vehicle(String registrationNumber) {
        this.registrationNumber = registrationNumber;
    }

    public void move() {
        System.out.println("Vehicle is moving.");
    }
}

public class Bus extends Vehicle {
    private int passengerCapacity;

    public Bus(String registrationNumber, int passengerCapacity) {
        super(registrationNumber);
        this.passengerCapacity = passengerCapacity;
    }

    @Override
    public void move() {
        System.out.println("Bus " + registrationNumber + " is carrying passengers.");
    }
}
</code></pre>

<h2>The extends and super Keywords</h2>
<p>The <code>extends</code> keyword tells Java that a class inherits from another class. The <code>super</code> keyword calls the parent class constructor or methods. When a subclass constructor runs, it often calls <code>super(...)</code> first to initialise the inherited fields.</p>

<h2>Worked Example: School Staff</h2>
<p>A school has both teachers and administrators. Both are employees, but they have different responsibilities.</p>
<pre><code>public class Employee {
    protected String name;
    protected double salary;

    public Employee(String name, double salary) {
        this.name = name;
        this.salary = salary;
    }

    public void work() {
        System.out.println(name + " is working.");
    }
}

public class Teacher extends Employee {
    private String subject;

    public Teacher(String name, double salary, String subject) {
        super(name, salary);
        this.subject = subject;
    }

    @Override
    public void work() {
        System.out.println(name + " is teaching " + subject + ".");
    }
}
</code></pre>

<h2>Best Practice</h2>
<p>Use inheritance only when the subclass truly is a specialised version of the parent class. A Bus is a Vehicle, so inheritance makes sense. A Bus is not a Driver, so a Bus class should not inherit from a Driver class. When classes only share some behaviour but are not logically related, consider composition instead. Composition means one class contains an object of another class rather than inheriting from it. For example, a Bus might contain a Driver object instead of extending Driver.</p>

<h2>Common Mistakes</h2>
<ul>
<li>Forgetting to call the parent constructor with <code>super</code> when the parent has no default constructor.</li>
<li>Trying to inherit from multiple classes. Java supports single inheritance only.</li>
<li>Overriding a method but forgetting the <code>@Override</code> annotation.</li>
<li>Making fields private in the parent when subclasses need to access them directly. Use protected instead.</li>
</ul>

<h2>Real-World Connection</h2>
<p>Inheritance is used in large systems to reduce duplication. A payroll system might have an Employee superclass with subclasses for SalariedEmployee and HourlyEmployee. A banking system might have an Account superclass with SavingsAccount and CurrentAccount subclasses. Government and corporate systems across Zambia use these patterns to keep software maintainable. The Zambia Revenue Authority, banks, and mobile network operators all rely on object-oriented designs that use inheritance to manage thousands of similar records without duplicating code.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>Person.java</code> with name and phone fields, plus a method <code>displayInfo</code>.</li>
<li>Create subclasses <code>Student</code> and <code>Instructor</code> that extend Person.</li>
<li>Add at least one extra field to each subclass and override <code>displayInfo</code> to include it.</li>
<li>Create objects of each subclass and call <code>displayInfo</code>.</li>
<li>Explain to a classmate what code you did not have to rewrite because of inheritance.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Inheritance</strong> — a mechanism where one class acquires the properties and methods of another.</li>
<li><strong>Superclass</strong> — the class that is inherited from.</li>
<li><strong>Subclass</strong> — the class that inherits from a superclass.</li>
<li><strong>Override</strong> — to replace an inherited method with a specialised version in a subclass.</li>
<li><strong>super</strong> — a keyword used to call the constructor or methods of the superclass.</li>
</ul>

<h2>Summary</h2>
<p>Inheritance lets you build new classes based on existing ones, reducing duplication and making code easier to maintain. Method overriding lets subclasses provide specialised behaviour while keeping a common interface. These tools are essential for building larger object-oriented systems.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_inheritance.asp">W3Schools — Java Inheritance</a></li>
<li><a href="https://www.w3schools.com/java/java_polymorphism.asp">W3Schools — Java Polymorphism</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — OOP in Java</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Programming</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.5 Encapsulation',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain encapsulation in your own words, apply private access modifiers to fields, and design classes that protect their internal state while exposing only necessary operations.</p>

<h2>What Is Encapsulation?</h2>
<p><strong>Encapsulation</strong> means bundling data and the methods that operate on that data inside a class, while hiding the internal details from the outside world. It is like driving a car: you use the steering wheel and pedals without needing to understand the engine. In Java, encapsulation is achieved by making fields private and providing public methods to access or modify them safely.</p>

<h2>Why Encapsulation Matters</h2>
<p>Without encapsulation, any part of a program can change an object's data directly. This leads to bugs that are hard to trace. With encapsulation, you control how data is changed. For example, you can ensure that a price is never negative, that a password field is read-only, or that a balance is only updated through approved methods.</p>

<h2>Access Modifiers</h2>
<p>Java provides access modifiers to control visibility:</p>
<ul>
<li><strong>public</strong> — accessible from any class.</li>
<li><strong>private</strong> — accessible only within the same class.</li>
<li><strong>protected</strong> — accessible within the same package and subclasses.</li>
<li><strong>default</strong> — accessible within the same package when no modifier is given.</li>
</ul>
<p>For encapsulation, fields are usually private and methods are public.</p>

<h2>Worked Example: A Secure Bank Account</h2>
<pre><code>public class SecureAccount {
    private String accountNumber;
    private double balance;

    public SecureAccount(String accountNumber, double openingBalance) {
        this.accountNumber = accountNumber;
        if (openingBalance &gt;= 0) {
            this.balance = openingBalance;
        }
    }

    public void deposit(double amount) {
        if (amount &gt; 0) {
            balance += amount;
        }
    }

    public boolean withdraw(double amount) {
        if (amount &gt; 0 &amp;&amp; amount &lt;= balance) {
            balance -= amount;
            return true;
        }
        return false;
    }

    public double getBalance() {
        return balance;
    }
}
</code></pre>

<h2>Worked Example: A Product with Validation</h2>
<p>A shop program should not allow negative prices or quantities. Encapsulation enforces these rules.</p>
<pre><code>public class Product {
    private String name;
    private double price;
    private int quantity;

    public Product(String name, double price, int quantity) {
        this.name = name;
        setPrice(price);
        setQuantity(quantity);
    }

    public void setPrice(double price) {
        if (price &gt;= 0) {
            this.price = price;
        }
    }

    public void setQuantity(int quantity) {
        if (quantity &gt;= 0) {
            this.quantity = quantity;
        }
    }

    public double getStockValue() {
        return price * quantity;
    }
}
</code></pre>

<h2>Common Mistakes</h2>
<ul>
<li>Making fields public when they should be private.</li>
<li>Writing getters that accidentally change the object's state.</li>
<li>Writing setters that accept invalid values without checking.</li>
<li>Exposing internal collections directly, which allows outside code to modify them.</li>
</ul>

<h2>Real-World Connection</h2>
<p>Encapsulation is essential in financial software, healthcare systems, and government databases. A system that stores National Registration Card information must protect fields from unauthorised changes. A mobile money wallet must ensure that balances can only increase through deposits and decrease through valid withdrawals. By encapsulating data, developers reduce the risk of fraud and errors.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>SecureAccount.java</code> with the class above.</li>
<li>Create a demo program that creates an account, deposits money, withdraws money, and prints the balance.</li>
<li>Try to withdraw more than the balance and confirm that the method returns false.</li>
<li>Add a method <code>getAccountNumber</code> that returns a masked version of the account number, showing only the last four digits.</li>
<li>Explain how encapsulation protects the account from invalid operations.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Encapsulation</strong> — hiding internal details and exposing only controlled access to an object's data.</li>
<li><strong>Private</strong> — an access modifier that hides a field or method from outside the class.</li>
<li><strong>Public</strong> — an access modifier that allows access from any other class.</li>
<li><strong>Internal state</strong> — the current values of an object's fields.</li>
<li><strong>Validation</strong> — checking that data meets requirements before using it.</li>
</ul>

<h2>Summary</h2>
<p>Encapsulation is one of the most important principles of object-oriented programming. By keeping fields private and exposing only well-defined public methods, you protect your data from accidental or malicious changes and make your classes easier to use correctly.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_encapsulation.asp">W3Schools — Java Encapsulation</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — OOP Principles</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Programming</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Training</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module6Lessons(): array
    {
        return [
            [
                'title' => '6.1 ArrayList and HashMap',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to use ArrayList to store collections that grow and shrink, use HashMap to store key-value pairs, and understand when collections are more convenient than arrays.</p>

<h2>Limitations of Arrays</h2>
<p>Arrays are useful, but they have a fixed size. Once you create an array with five elements, you cannot easily add a sixth. In real programs, the number of items is often unknown at the start. A shop may receive new stock throughout the day. A messaging app may receive new contacts over time. <strong>Collections</strong> solve this problem by providing flexible data structures that can grow and shrink.</p>

<h2>ArrayList</h2>
<p><code>ArrayList</code> is a resizable array provided by Java's standard library. You must import it before using it:</p>
<pre><code>import java.util.ArrayList;

ArrayList&lt;String&gt; names = new ArrayList&lt;&gt;();
names.add("Grace");
names.add("John");
System.out.println(names.get(0));
</code></pre>
<p>ArrayList uses angle brackets to specify the type of elements it will hold. You can add and remove elements without worrying about the size.</p>

<h2>HashMap</h2>
<p><code>HashMap</code> stores data as key-value pairs. It is useful when you want to look up a value quickly based on a unique key. For example, you could map phone numbers to customer names or product codes to prices.</p>
<pre><code>import java.util.HashMap;

HashMap&lt;String, Double&gt; prices = new HashMap&lt;&gt;();
prices.put("Rice", 55.00);
prices.put("Sugar", 90.00);
System.out.println("Rice price: K" + prices.get("Rice"));
</code></pre>

<h2>Worked Example: Managing a Product List</h2>
<pre><code>import java.util.ArrayList;

public class ProductList {
    public static void main(String[] args) {
        ArrayList&lt;String&gt; products = new ArrayList&lt;&gt;();
        products.add("Mealie meal");
        products.add("Cooking oil");
        products.add("Sugar");

        System.out.println("Stock list:");
        for (String product : products) {
            System.out.println("- " + product);
        }

        products.remove("Sugar");
        System.out.println("After removal: " + products);
    }
}
</code></pre>

<h2>Worked Example: Mobile Money Menu Prices</h2>
<p>A small shop accepts mobile money payments. The owner uses a HashMap to store transaction fees for each provider.</p>
<pre><code>import java.util.HashMap;

public class MobileMoneyFees {
    public static void main(String[] args) {
        HashMap&lt;String, Double&gt; fees = new HashMap&lt;&gt;();
        fees.put("Airtel Money", 2.50);
        fees.put("MTN MoMo", 2.50);
        fees.put("Zamtel Kwacha", 3.00);

        String provider = "MTN MoMo";
        double fee = fees.getOrDefault(provider, 0.0);

        System.out.println("Fee for " + provider + " is K" + fee);
    }
}
</code></pre>
<p>The <code>getOrDefault</code> method returns a default value if the key is not found, which prevents the program from crashing when a provider is unknown.</p>

<h2>Common Mistakes</h2>
<ul>
<li>Forgetting to import <code>java.util.ArrayList</code> or <code>java.util.HashMap</code>.</li>
<li>Trying to access an ArrayList index that does not exist.</li>
<li>Using a key that is not in the HashMap and getting <code>null</code> back without checking.</li>
<li>Adding the wrong type to a generic collection, which causes a compiler error.</li>
</ul>

<h2>Real-World Connection</h2>
<p>Collections are used in almost every modern application. A messaging app uses an ArrayList to store conversations. A shopping cart uses an ArrayList to hold items. A contact list might use a HashMap to look up phone numbers by name. Zambian fintech apps use HashMaps to cache exchange rates and transaction fees. Understanding collections is essential for building professional software.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>CollectionsPractice.java</code>.</li>
<li>Create an ArrayList of five Zambian towns and print them using a loop.</li>
<li>Create a HashMap that maps product names to prices. Add at least four items.</li>
<li>Write code that checks whether a product exists in the map and prints its price.</li>
<li>Remove one item from the ArrayList and one item from the HashMap, then print the updated collections.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Collection</strong> — a Java framework of classes for storing groups of objects.</li>
<li><strong>ArrayList</strong> — a resizable list that can grow and shrink.</li>
<li><strong>HashMap</strong> — a collection that stores key-value pairs for fast lookup.</li>
<li><strong>Key</strong> — a unique identifier used to retrieve a value from a map.</li>
<li><strong>Generics</strong> — the use of angle brackets to specify the type of elements in a collection.</li>
</ul>

<h2>Summary</h2>
<p>Collections such as ArrayList and HashMap provide flexible alternatives to arrays. ArrayList is ideal when you need an ordered list of items that can change size. HashMap is ideal when you need to associate keys with values for quick lookup. These tools are essential for building real-world applications.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_arraylist.asp">W3Schools — Java ArrayList</a></li>
<li><a href="https://www.w3schools.com/java/java_hashmap.asp">W3Schools — Java HashMap</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Java Collections</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Programming</a></li>
</ul>
HTML,
            ],
            [
                'title' => '6.2 Exception Handling with try-catch',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what exceptions are, handle errors gracefully using try-catch blocks, and write programs that do not crash when unexpected input or problems occur.</p>

<h2>What Is an Exception?</h2>
<p>An <strong>exception</strong> is an event that disrupts the normal flow of a program. Common exceptions include dividing by zero, trying to open a file that does not exist, or accessing an array index that is out of bounds. When Java encounters an exception, it creates an exception object and may terminate the program unless you handle the situation.</p>

<h2>The try-catch Block</h2>
<p>A <code>try-catch</code> block lets you attempt risky code and respond if an exception occurs. The risky code goes inside the <code>try</code> block, and the recovery code goes inside the <code>catch</code> block.</p>
<pre><code>public class DivisionExample {
    public static void main(String[] args) {
        int numerator = 10;
        int denominator = 0;

        try {
            int result = numerator / denominator;
            System.out.println("Result: " + result);
        } catch (ArithmeticException e) {
            System.out.println("Cannot divide by zero.");
        }

        System.out.println("Program continues normally.");
    }
}
</code></pre>

<h2>Handling Multiple Exception Types</h2>
<p>You can catch different exceptions with separate catch blocks. For example, you might catch <code>NumberFormatException</code> when converting text to a number and <code>IOException</code> when reading a file.</p>
<pre><code>try {
    int number = Integer.parseInt(userInput);
    double result = 100.0 / number;
} catch (NumberFormatException e) {
    System.out.println("Please enter a valid number.");
} catch (ArithmeticException e) {
    System.out.println("Cannot divide by zero.");
}
</code></pre>

<h2>Worked Example: Safe Number Input</h2>
<pre><code>public class SafeInput {
    public static void main(String[] args) {
        String userInput = "abc";

        try {
            int number = Integer.parseInt(userInput);
            System.out.println("You entered: " + number);
        } catch (NumberFormatException e) {
            System.out.println("Invalid input. Please enter a whole number.");
        }
    }
}
</code></pre>

<h2>Worked Example: Safe Array Access</h2>
<p>A program that processes student marks should not crash if the user asks for an invalid index.</p>
<pre><code>public class SafeArrayAccess {
    public static void main(String[] args) {
        int[] marks = {78, 85, 92};
        int index = 5;

        try {
            System.out.println("Mark: " + marks[index]);
        } catch (ArrayIndexOutOfBoundsException e) {
            System.out.println("Invalid index. Please use a value between 0 and " + (marks.length - 1));
        }
    }
}
</code></pre>

<h2>Common Mistakes</h2>
<ul>
<li>Catching an exception but doing nothing inside the catch block. This hides problems.</li>
<li>Catching a very general exception such as <code>Exception</code> when a specific one would be clearer.</li>
<li>Putting too much code inside a try block, which makes it hard to identify what caused the exception.</li>
<li>Ignoring the exception message, which often contains useful details for debugging.</li>
</ul>

<h2>Real-World Connection</h2>
<p>Exception handling is critical in systems that handle money, health records, and customer data. A mobile money app must not crash when a user enters the wrong PIN format. A hospital system must not lose data when a network connection fails. Government portals must handle heavy traffic without stopping. Try-catch blocks help developers build software that recovers from problems and keeps serving users.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>ExceptionPractice.java</code>.</li>
<li>Write a program that divides two numbers. Use try-catch to handle division by zero.</li>
<li>Create an array of three elements and write code that tries to access index 10. Catch the resulting exception and print a friendly message.</li>
<li>Write a method that converts a String to an int and returns -1 if the conversion fails.</li>
<li>Explain why graceful error handling improves the user experience.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Exception</strong> — an error or unexpected event that disrupts program execution.</li>
<li><strong>try block</strong> — the section of code where an exception might occur.</li>
<li><strong>catch block</strong> — the section of code that handles a specific exception.</li>
<li><strong>Graceful handling</strong> — responding to errors in a controlled way without crashing.</li>
<li><strong>NumberFormatException</strong> — an exception that occurs when text cannot be converted to a number.</li>
</ul>

<h2>Summary</h2>
<p>Exception handling lets you build robust programs that recover from errors. By placing risky code in a try block and providing catch blocks for possible exceptions, you can prevent crashes and give users helpful feedback when something goes wrong.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_try_catch.asp">W3Schools — Java Exceptions</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Java Error Handling</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Programming</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Training</a></li>
</ul>
HTML,
            ],
            [
                'title' => '6.3 Reading and Writing Text Files',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to read text from a file, write text to a file, and use try-with-resources to handle files safely in Java.</p>

<h2>Why Files Matter</h2>
<p>Programs lose their data when they close unless that data is saved somewhere permanent. Files provide a simple way to store information between program runs. A shop can save its daily sales to a file. A college can store student records. A bus company can keep route schedules. Learning to read and write files is essential for building practical applications.</p>

<h2>Writing to a File</h2>
<p>Java provides the <code>FileWriter</code> and <code>BufferedWriter</code> classes for writing text. Here is a simple example:</p>
<pre><code>import java.io.FileWriter;
import java.io.IOException;
import java.io.PrintWriter;

public class WriteFileExample {
    public static void main(String[] args) {
        try (PrintWriter writer = new PrintWriter(new FileWriter("sales.txt"))) {
            writer.println("Date: 2025-06-11");
            writer.println("Total: K450.00");
            System.out.println("File written successfully.");
        } catch (IOException e) {
            System.out.println("An error occurred while writing the file.");
        }
    }
}
</code></pre>

<h2>Reading from a File</h2>
<pre><code>import java.io.BufferedReader;
import java.io.FileReader;
import java.io.IOException;

public class ReadFileExample {
    public static void main(String[] args) {
        try (BufferedReader reader = new BufferedReader(new FileReader("sales.txt"))) {
            String line;
            while ((line = reader.readLine()) != null) {
                System.out.println(line);
            }
        } catch (IOException e) {
            System.out.println("Could not read the file.");
        }
    }
}
</code></pre>

<h2>Appending to a File</h2>
<p>By default, <code>FileWriter</code> overwrites an existing file. To add data to the end of a file instead, pass <code>true</code> as the second argument.</p>
<pre><code>PrintWriter writer = new PrintWriter(new FileWriter("sales.txt", true));
writer.println("New sale: K120.00");
</code></pre>
<p>This is useful for log files and daily sales records where you want to keep historical entries.</p>

<h2>Worked Example: Saving Student Marks</h2>
<pre><code>import java.io.FileWriter;
import java.io.IOException;
import java.io.PrintWriter;

public class SaveMarks {
    public static void main(String[] args) {
        String[] students = {"John Musonda", "Mary Zulu", "Peter Banda"};
        int[] marks = {78, 85, 92};

        try (PrintWriter writer = new PrintWriter(new FileWriter("marks.txt"))) {
            for (int i = 0; i &lt; students.length; i++) {
                writer.println(students[i] + ": " + marks[i]);
            }
            System.out.println("Marks saved successfully.");
        } catch (IOException e) {
            System.out.println("Failed to save marks.");
        }
    }
}
</code></pre>

<h2>Common Mistakes</h2>
<ul>
<li>Forgetting to import <code>java.io.*</code> classes.</li>
<li>Trying to read a file that does not exist without handling the exception.</li>
<li>Forgetting that <code>FileWriter</code> overwrites existing files by default.</li>
<li>Not closing the file, which can leave data incomplete or locked.</li>
</ul>

<h2>Real-World Connection</h2>
<p>File handling is the first step toward data persistence. Small shops in Zambia might use text files to record daily sales before moving to a database. Schools might export student lists to CSV files. Government offices might generate reports as text files. Understanding file input and output prepares you to build systems that keep records beyond a single program run.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>FilePractice.java</code>.</li>
<li>Write a program that creates a file named "products.txt" and writes at least three product names and prices to it.</li>
<li>Write a second program that reads "products.txt" and prints each line.</li>
<li>Modify the writer to append new data instead of overwriting the file.</li>
<li>Explain why the try-with-resources statement is safer than manually closing files.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>File</strong> — a named collection of data stored on a disk.</li>
<li><strong>FileWriter</strong> — a Java class used to write character data to a file.</li>
<li><strong>BufferedReader</strong> — a Java class used to read text from a file efficiently.</li>
<li><strong>try-with-resources</strong> — a Java statement that automatically closes resources such as files.</li>
<li><strong>IOException</strong> — an exception that occurs during input or output operations.</li>
</ul>

<h2>Summary</h2>
<p>Reading and writing files allows your programs to persist data between runs. Use FileWriter and PrintWriter to write text, and BufferedReader to read text. Always handle IOExceptions and use try-with-resources to ensure files are closed properly.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_files.asp">W3Schools — Java Files</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Java File Handling</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Programming</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Training</a></li>
</ul>
HTML,
            ],
            [
                'title' => '6.4 Console Project: Market Stock Manager',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to plan and build a complete console application that uses classes, collections, file handling, and exception handling to manage stock for a small market business in Zambia.</p>

<h2>Project Overview</h2>
<p>The Market Stock Manager is a console program that helps a small trader keep track of products. The program should allow the user to add products, view the current stock list, update quantities, remove products, and save the stock to a file so that data is not lost when the program closes.</p>

<h2>Recommended Class Design</h2>
<ul>
<li><strong>Product</strong> — fields for name, price, and quantity, with getters, setters, and a constructor.</li>
<li><strong>StockManager</strong> — uses an ArrayList or HashMap to store products and provides methods for add, remove, update, view, and save.</li>
<li><strong>MarketStockApp</strong> — contains the main method and a simple text menu.</li>
</ul>

<h2>Worked Example: The Product Class</h2>
<pre><code>public class Product {
    private String name;
    private double price;
    private int quantity;

    public Product(String name, double price, int quantity) {
        this.name = name;
        this.price = price;
        this.quantity = quantity;
    }

    public String getName() { return name; }
    public double getPrice() { return price; }
    public int getQuantity() { return quantity; }

    public void setQuantity(int quantity) {
        if (quantity &gt;= 0) {
            this.quantity = quantity;
        }
    }

    public double getStockValue() {
        return price * quantity;
    }
}
</code></pre>

<h2>Worked Example: A Simple Menu Loop</h2>
<p>The main application repeatedly displays options and waits for the user to choose one.</p>
<pre><code>import java.util.Scanner;

public class MarketStockApp {
    public static void main(String[] args) {
        Scanner scanner = new Scanner(System.in);
        boolean running = true;

        while (running) {
            System.out.println("1. Add product");
            System.out.println("2. View stock");
            System.out.println("3. Exit");
            System.out.print("Choose an option: ");
            String choice = scanner.nextLine();

            switch (choice) {
                case "1":
                    System.out.println("Add product feature coming soon.");
                    break;
                case "2":
                    System.out.println("View stock feature coming soon.");
                    break;
                case "3":
                    running = false;
                    break;
                default:
                    System.out.println("Invalid choice.");
            }
        }

        scanner.close();
        System.out.println("Goodbye!");
    }
}
</code></pre>

<h2>Common Mistakes</h2>
<ul>
<li>Putting all the code in the main method instead of separating it into classes.</li>
<li>Forgetting to validate user input, which can cause exceptions.</li>
<li>Not saving data to a file before the program exits.</li>
<li>Using the wrong collection type. Use ArrayList when order matters and HashMap when fast lookup by name is needed.</li>
</ul>

<h2>Best Practice</h2>
<p>Build the project one feature at a time. Start with the Product class, then the StockManager, then the menu, and finally file persistence. Test each feature before moving to the next. Keep the user interface separate from the business logic so that you can change the menu without rewriting the stock calculations.</p>

<h2>Real-World Connection</h2>
<p>Inventory management is essential for shops, warehouses, and pharmacies. A small trader in Soweto Market could use a program like this to know when to restock popular items. A pharmacy could track medicine quantities and expiry dates. A hardware store could monitor fast-moving products. By completing this project, you demonstrate skills that are valuable to employers in retail, logistics, and agriculture across Zambia.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a new folder named "MarketStockManager" inside your JavaPractice folder.</li>
<li>Create the Product class as shown above.</li>
<li>Create a StockManager class that stores products in an ArrayList and has methods to add, remove, and list products.</li>
<li>Create a MarketStockApp class with a menu loop that lets the user choose actions by number.</li>
<li>Add a save-to-file feature using FileWriter and a load-from-file feature using BufferedReader.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Console application</strong> — a program that interacts with the user through text commands.</li>
<li><strong>Menu loop</strong> — a loop that repeatedly displays options and processes user choices.</li>
<li><strong>Stock value</strong> — the total worth of items in stock, calculated as price multiplied by quantity.</li>
<li><strong>Persistence</strong> — saving data so it survives after the program closes.</li>
<li><strong>Modular design</strong> — dividing a program into separate, manageable classes.</li>
</ul>

<h2>Summary</h2>
<p>The Market Stock Manager project brings together many of the skills you have learned in this course. By designing classes, using collections, handling files, and building a text menu, you create a program that could genuinely help a small business in Zambia track its inventory. This is the kind of project you can expand and show to potential employers or clients.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/java/java_files.asp">W3Schools — Java File Handling</a></li>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Build a Project</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Programming</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Training</a></li>
</ul>
HTML,
            ],
            [
                'title' => '6.5 Where Java Jobs Are in Zambia and Beyond',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to identify concrete career paths that require Java skills, describe the industries that hire Java developers in Zambia and the region, and plan your next steps for continuing your Java education after this certificate course.</p>

<h2>Java Careers in Zambia</h2>
<p>Java developers are needed in many sectors of the Zambian economy. Banks and financial institutions use Java for backend transaction processing. Mobile network operators use Java for customer portals and mobile money systems. Government agencies use Java for e-government services. NGOs and international organisations use Java for data collection and reporting systems. Even smaller tech startups in Lusaka, Kitwe, and Ndola hire Java developers for web and mobile projects.</p>

<h2>Regional and Remote Opportunities</h2>
<p>Beyond Zambia, Java skills open doors across Africa and the world. South Africa, Kenya, Nigeria, and Egypt all have growing technology sectors. Remote work platforms allow Zambian developers to take contracts from clients in Europe, North America, and Asia. A strong portfolio of Java projects, including the Market Stock Manager, can help you win freelance work or land a remote position.</p>

<h2>Building Your Portfolio</h2>
<p>Employers and clients want to see what you can build. Create a GitHub account and upload your course projects. Write short README files that explain what each project does, how to run it, and what you learned. Include screenshots of your console output. A public portfolio is one of the best ways to demonstrate your skills, especially if you do not yet have formal work experience.</p>

<h2>Next Steps After This Course</h2>
<p>Your learning journey does not end here. Consider these paths:</p>
<ul>
<li><strong>Android development</strong> — build mobile apps using Java and Android Studio.</li>
<li><strong>Web development</strong> — learn Java frameworks such as Spring Boot to build websites and APIs.</li>
<li><strong>Database skills</strong> — learn SQL so your Java programs can store and query data in relational databases.</li>
<li><strong>Cloud computing</strong> — learn how to deploy Java applications to cloud platforms.</li>
<li><strong>Certifications</strong> — consider Oracle Java certifications to validate your knowledge.</li>
</ul>

<h2>Worked Example: A Job Search Plan</h2>
<p>John finishes the Certificate in Java Programming and wants to find work within six months. His plan is:</p>
<ol>
<li>Week 1-2: Clean up his course projects and upload them to GitHub.</li>
<li>Week 3-4: Build one new project, a simple Android app for tracking chicken-rearing expenses.</li>
<li>Month 2: Learn basic SQL and connect a Java program to a database.</li>
<li>Month 3: Apply for internships and junior developer roles in Lusaka and remote positions online.</li>
<li>Month 4-6: Continue learning, contribute to open-source projects, and attend local tech meetups if possible.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Search online for "Java developer jobs Zambia" and read at least three job postings.</li>
<li>Create a free GitHub account if you do not already have one.</li>
<li>Upload your Market Stock Manager project with a README file.</li>
<li>Write a short LinkedIn or CV statement that describes your Java skills and the projects you have built.</li>
<li>Choose one next-step topic from the list above and find one free resource to start learning it next week.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Backend developer</strong> — a programmer who builds server-side logic and databases.</li>
<li><strong>Portfolio</strong> — a collection of projects that demonstrates your skills to employers.</li>
<li><strong>Freelance</strong> — working for multiple clients on a project basis rather than as a permanent employee.</li>
<li><strong>Framework</strong> — a pre-built structure that simplifies development in a particular area.</li>
<li><strong>Open source</strong> — software whose source code is freely available for anyone to view and contribute to.</li>
</ul>

<h2>Summary</h2>
<p>Java skills create real career opportunities in Zambia, the region, and the global remote job market. By building a portfolio, continuing to learn, and targeting the right industries, you can turn your certificate into a meaningful career. Stay curious, keep practising, and remember that every expert developer was once a beginner.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.freecodecamp.org/">freeCodeCamp — Free Coding Curriculum</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn">MDN Web Docs — Learn Web Development</a></li>
<li><a href="https://grow.google/">Grow with Google — Free Training</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Computer Programming</a></li>
</ul>
HTML,
            ],
        ];
    }


    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: Welcome to Java',
            'description' => 'Test your knowledge of Java importance, installation, first program structure, and command-line compilation.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which tool translates Java source code into bytecode?',
                    'explanation' => 'The Java compiler, javac, translates .java source files into .class bytecode files that the Java runtime can execute.',
                    'options' => [
                        ['text' => 'javac', 'is_correct' => true],
                        ['text' => 'java', 'is_correct' => false],
                        ['text' => 'jdk', 'is_correct' => false],
                        ['text' => 'jre', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What must the filename match in a simple public Java class?',
                    'explanation' => 'In Java, the filename must match the public class name exactly, including capital letters.',
                    'options' => [
                        ['text' => 'The main method name', 'is_correct' => false],
                        ['text' => 'The public class name', 'is_correct' => true],
                        ['text' => 'The package name', 'is_correct' => false],
                        ['text' => 'The author name', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which command runs a compiled Java program named HelloKalomo?',
                    'explanation' => 'You use java followed by the class name, without the .class extension, to run bytecode.',
                    'options' => [
                        ['text' => 'javac HelloKalomo', 'is_correct' => false],
                        ['text' => 'java HelloKalomo.java', 'is_correct' => false],
                        ['text' => 'java HelloKalomo', 'is_correct' => true],
                        ['text' => 'run HelloKalomo', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which statement prints text followed by a new line?',
                    'explanation' => 'System.out.println adds a new line after printing, while System.out.print does not.',
                    'options' => [
                        ['text' => 'System.out.print("Hello");', 'is_correct' => false],
                        ['text' => 'System.println.out("Hello");', 'is_correct' => false],
                        ['text' => 'System.out.println("Hello");', 'is_correct' => true],
                        ['text' => 'print.out("Hello");', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Java is widely used to build Android mobile applications.',
                    'explanation' => 'Java is one of the primary languages for Android development, making it highly relevant for mobile apps in Zambia.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'The JDK includes only the Java Runtime Environment and does not include the compiler.',
                    'explanation' => 'The JDK includes both the compiler javac and the runtime, along with other development tools and libraries.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What keyword begins the special method where Java starts executing a program? (one word)',
                    'explanation' => 'The main method is the entry point of a Java program. Java looks for public static void main(String[] args) to start execution.',
                    'correct_answer' => 'main',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is Java described as "write once, run anywhere"?',
                    'explanation' => 'Java bytecode can run on any platform that has a Java Virtual Machine, so the same compiled program can run on Windows, Linux, or macOS.',
                    'options' => [
                        ['text' => 'It only runs on Windows', 'is_correct' => false],
                        ['text' => 'It compiles directly to machine code', 'is_correct' => false],
                        ['text' => 'Bytecode runs on any JVM', 'is_correct' => true],
                        ['text' => 'It does not need compilation', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which editor is recommended in this course for writing Java on a modest Windows computer?',
                    'explanation' => 'Visual Studio Code is a free, lightweight editor that supports Java through extensions and runs well on modest hardware.',
                    'options' => [
                        ['text' => 'Microsoft Word', 'is_correct' => false],
                        ['text' => 'Adobe Photoshop', 'is_correct' => false],
                        ['text' => 'Visual Studio Code', 'is_correct' => true],
                        ['text' => 'Notepad only', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Variables, Data Types, and Operators',
            'description' => 'Test your knowledge of Java variables, primitive types, constants, operators, and shop bill calculations.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which data type would you use to store the price of a bag of mealie meal in Kwacha?',
                    'explanation' => 'Money amounts contain decimal places, so double is the appropriate primitive type for storing prices.',
                    'options' => [
                        ['text' => 'int', 'is_correct' => false],
                        ['text' => 'boolean', 'is_correct' => false],
                        ['text' => 'double', 'is_correct' => true],
                        ['text' => 'char', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the result of the expression 7 / 2 in Java when both operands are integers?',
                    'explanation' => 'Integer division discards the fractional part, so 7 / 2 evaluates to 3.',
                    'options' => [
                        ['text' => '3.5', 'is_correct' => false],
                        ['text' => '3', 'is_correct' => true],
                        ['text' => '4', 'is_correct' => false],
                        ['text' => '2', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which keyword is used to declare a constant in Java?',
                    'explanation' => 'The final keyword prevents a variable from being reassigned after its initial value is set.',
                    'options' => [
                        ['text' => 'constant', 'is_correct' => false],
                        ['text' => 'static', 'is_correct' => false],
                        ['text' => 'final', 'is_correct' => true],
                        ['text' => 'const', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does the modulus operator % return?',
                    'explanation' => 'The modulus operator returns the remainder after division, such as 17 % 5 giving 2.',
                    'options' => [
                        ['text' => 'The quotient', 'is_correct' => false],
                        ['text' => 'The remainder', 'is_correct' => true],
                        ['text' => 'The square root', 'is_correct' => false],
                        ['text' => 'The product', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A variable declared with final can have its value changed later in the program.',
                    'explanation' => 'A final variable is a constant. Once assigned, its value cannot be changed.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'The expression double result = 7.0 / 2 produces the value 3.5.',
                    'explanation' => 'When at least one operand is a double, Java performs floating-point division and keeps the decimal part.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'How many ngwee are in one Zambian Kwacha? (one number)',
                    'explanation' => 'One Kwacha equals one hundred ngwee, which is why amounts can be converted by multiplying or dividing by 100.',
                    'correct_answer' => '100',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which operator adds a value to a variable and assigns the result back in shorthand?',
                    'explanation' => 'The += operator adds the right-hand value to the variable and stores the result in the same variable.',
                    'options' => [
                        ['text' => '==', 'is_correct' => false],
                        ['text' => '+=', 'is_correct' => true],
                        ['text' => '++=', 'is_correct' => false],
                        ['text' => '=+', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In a shop bill calculator, what is the subtotal?',
                    'explanation' => 'The subtotal is the total cost of items before tax or discounts are applied.',
                    'options' => [
                        ['text' => 'The final amount including VAT', 'is_correct' => false],
                        ['text' => 'The total before tax or discounts', 'is_correct' => true],
                        ['text' => 'The amount of change given', 'is_correct' => false],
                        ['text' => 'The transaction fee', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Control Flow',
            'description' => 'Test your knowledge of relational and logical operators, if-else and switch statements, loops, and the ZESCO token calculator.',
            'time_limit_minutes' => 25,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which operator checks whether two values are equal in Java?',
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
                    'text' => 'What is the result of true && false?',
                    'explanation' => 'The logical AND operator && is true only when both operands are true, so true && false is false.',
                    'options' => [
                        ['text' => 'true', 'is_correct' => false],
                        ['text' => 'false', 'is_correct' => true],
                        ['text' => 'maybe', 'is_correct' => false],
                        ['text' => 'error', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which control structure is usually best for selecting from a menu with several numbered options?',
                    'explanation' => 'A switch statement is often cleaner than a long chain of if-else statements when choosing from many discrete values.',
                    'options' => [
                        ['text' => 'for loop', 'is_correct' => false],
                        ['text' => 'while loop', 'is_correct' => false],
                        ['text' => 'switch statement', 'is_correct' => true],
                        ['text' => 'try-catch', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'How many times will the loop body execute for (int i = 0; i < 5; i++)?',
                    'explanation' => 'The loop runs while i is 0, 1, 2, 3, and 4. When i reaches 5, the condition becomes false, so it executes 5 times.',
                    'options' => [
                        ['text' => '4', 'is_correct' => false],
                        ['text' => '5', 'is_correct' => true],
                        ['text' => '6', 'is_correct' => false],
                        ['text' => '0', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A while loop is best when you know exactly how many times to repeat an action.',
                    'explanation' => 'A for loop is usually better when the number of repetitions is known. A while loop is better when repetition depends on a condition.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'The break statement in a switch prevents fall-through to the next case.',
                    'explanation' => 'Without break, execution continues into the next case. The break statement exits the switch.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the value of 17 % 5? (one number)',
                    'explanation' => '17 divided by 5 is 3 with a remainder of 2, so the modulus operator returns 2.',
                    'correct_answer' => '2',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In the ZESCO token calculator, why is input validation important?',
                    'explanation' => 'Input validation ensures that the meter number and purchase amount meet requirements before the program processes them.',
                    'options' => [
                        ['text' => 'It makes the program run faster', 'is_correct' => false],
                        ['text' => 'It prevents invalid data from causing incorrect results', 'is_correct' => true],
                        ['text' => 'It reduces the file size', 'is_correct' => false],
                        ['text' => 'It removes comments from the code', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which logical operator returns true if at least one condition is true?',
                    'explanation' => 'The logical OR operator || returns true when either or both operands are true.',
                    'options' => [
                        ['text' => '&&', 'is_correct' => false],
                        ['text' => '||', 'is_correct' => true],
                        ['text' => '!', 'is_correct' => false],
                        ['text' => '==', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module4Quiz(): array
    {
        return [
            'title' => 'Module 4 Quiz: Methods and Arrays',
            'description' => 'Test your knowledge of methods, parameters, return values, overloading, arrays, and the bus fare calculator.',
            'time_limit_minutes' => 25,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does the void keyword mean in a method declaration?',
                    'explanation' => 'void means the method does not return any value to its caller.',
                    'options' => [
                        ['text' => 'The method returns an integer', 'is_correct' => false],
                        ['text' => 'The method returns a string', 'is_correct' => false],
                        ['text' => 'The method returns nothing', 'is_correct' => true],
                        ['text' => 'The method is private', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In Java, what is the index of the first element of an array?',
                    'explanation' => 'Java arrays are zero-indexed, meaning the first element is at index 0.',
                    'options' => [
                        ['text' => '0', 'is_correct' => true],
                        ['text' => '1', 'is_correct' => false],
                        ['text' => '-1', 'is_correct' => false],
                        ['text' => 'Depends on the array size', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which statement correctly calls a method named greet that takes a String parameter?',
                    'explanation' => 'You call a method by writing its name followed by parentheses containing the argument, such as greet("Alice").',
                    'options' => [
                        ['text' => 'greet[] "Alice";', 'is_correct' => false],
                        ['text' => 'greet("Alice");', 'is_correct' => true],
                        ['text' => 'greet String "Alice";', 'is_correct' => false],
                        ['text' => 'call greet("Alice");', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is method overloading?',
                    'explanation' => 'Method overloading means defining multiple methods with the same name but different parameter lists.',
                    'options' => [
                        ['text' => 'Writing a method that never returns', 'is_correct' => false],
                        ['text' => 'Defining multiple methods with the same name but different parameters', 'is_correct' => true],
                        ['text' => 'Calling a method many times', 'is_correct' => false],
                        ['text' => 'Deleting a method and rewriting it', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A method can accept more than one parameter.',
                    'explanation' => 'Methods can have multiple parameters separated by commas, each with its own type and name.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'In Java, an array size can grow automatically when you add new elements.',
                    'explanation' => 'Standard arrays have a fixed size. For a resizable collection, use ArrayList instead.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'Which keyword sends a value back from a method to its caller? (one word)',
                    'explanation' => 'The return keyword ends the method and sends a value back to the code that called it.',
                    'correct_answer' => 'return',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'How do you access the number of elements in an array named sales?',
                    'explanation' => 'Every Java array has a length property that returns the number of elements it can hold.',
                    'options' => [
                        ['text' => 'sales.size()', 'is_correct' => false],
                        ['text' => 'sales.length()', 'is_correct' => false],
                        ['text' => 'sales.length', 'is_correct' => true],
                        ['text' => 'sales.count', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In the bus fare calculator, why are routes, fares, and passengers called parallel arrays?',
                    'explanation' => 'Parallel arrays store related data at the same indices, so routes[i] corresponds to fares[i] and passengers[i].',
                    'options' => [
                        ['text' => 'They are all the same length', 'is_correct' => false],
                        ['text' => 'They run at the same time', 'is_correct' => false],
                        ['text' => 'Related data is stored at matching indices', 'is_correct' => true],
                        ['text' => 'They have identical values', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module5Quiz(): array
    {
        return [
            'title' => 'Module 5 Quiz: Object-Oriented Programming',
            'description' => 'Test your knowledge of classes, objects, constructors, getters and setters, inheritance, and encapsulation.',
            'time_limit_minutes' => 25,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the relationship between a class and an object?',
                    'explanation' => 'A class is a blueprint, and an object is a specific instance created from that blueprint.',
                    'options' => [
                        ['text' => 'A class is a type of object', 'is_correct' => false],
                        ['text' => 'An object is a blueprint for creating classes', 'is_correct' => false],
                        ['text' => 'A class is a blueprint; an object is an instance', 'is_correct' => true],
                        ['text' => 'They are the same thing', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which keyword is used to create a new object from a class?',
                    'explanation' => 'The new keyword allocates memory and calls the constructor to create a new object.',
                    'options' => [
                        ['text' => 'create', 'is_correct' => false],
                        ['text' => 'new', 'is_correct' => true],
                        ['text' => 'object', 'is_correct' => false],
                        ['text' => 'make', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the purpose of a getter method?',
                    'explanation' => 'A getter returns the value of a private field, allowing controlled read access to object data.',
                    'options' => [
                        ['text' => 'To change a private field directly', 'is_correct' => false],
                        ['text' => 'To create a new object', 'is_correct' => false],
                        ['text' => 'To return the value of a private field', 'is_correct' => true],
                        ['text' => 'To delete an object', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which keyword makes a field accessible only within its own class?',
                    'explanation' => 'The private access modifier restricts access to the class in which the field or method is declared.',
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
                    'text' => 'Encapsulation means making all fields public so any class can change them.',
                    'explanation' => 'Encapsulation hides internal details and exposes only controlled access, usually through private fields and public methods.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What keyword is used in a subclass to call the constructor of its parent class? (one word)',
                    'explanation' => 'The super keyword calls the parent class constructor or other parent class members from a subclass.',
                    'correct_answer' => 'super',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does it mean to override a method?',
                    'explanation' => 'Overriding means providing a specialised implementation of an inherited method in a subclass.',
                    'options' => [
                        ['text' => 'Delete the method from the parent class', 'is_correct' => false],
                        ['text' => 'Provide a specialised version in a subclass', 'is_correct' => true],
                        ['text' => 'Rename the method', 'is_correct' => false],
                        ['text' => 'Make the method private', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is an example of encapsulation?',
                    'explanation' => 'Making fields private and providing public getter and setter methods is the classic example of encapsulation.',
                    'options' => [
                        ['text' => 'Using public fields for all data', 'is_correct' => false],
                        ['text' => 'Writing all code in the main method', 'is_correct' => false],
                        ['text' => 'Making fields private and using getters and setters', 'is_correct' => true],
                        ['text' => 'Copying code between classes', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module6Quiz(): array
    {
        return [
            'title' => 'Module 6 Quiz: Collections, Exceptions, Files, and Projects',
            'description' => 'Test your knowledge of ArrayList, HashMap, exception handling, file input/output, and the Market Stock Manager project.',
            'time_limit_minutes' => 25,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which collection would you use to store a list of product names that can grow and shrink?',
                    'explanation' => 'ArrayList is a resizable list that can grow or shrink as items are added or removed.',
                    'options' => [
                        ['text' => 'HashMap', 'is_correct' => false],
                        ['text' => 'Array', 'is_correct' => false],
                        ['text' => 'ArrayList', 'is_correct' => true],
                        ['text' => 'String', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What kind of data structure stores key-value pairs?',
                    'explanation' => 'HashMap stores data as key-value pairs, allowing fast lookup of a value using its key.',
                    'options' => [
                        ['text' => 'ArrayList', 'is_correct' => false],
                        ['text' => 'HashMap', 'is_correct' => true],
                        ['text' => 'int array', 'is_correct' => false],
                        ['text' => 'for loop', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which block contains code that might throw an exception?',
                    'explanation' => 'The try block contains code that may throw an exception, while catch blocks handle exceptions if they occur.',
                    'options' => [
                        ['text' => 'catch', 'is_correct' => false],
                        ['text' => 'finally', 'is_correct' => false],
                        ['text' => 'try', 'is_correct' => true],
                        ['text' => 'throw', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which class is commonly used to read text from a file line by line?',
                    'explanation' => 'BufferedReader is commonly used with FileReader to read text files line by line efficiently.',
                    'options' => [
                        ['text' => 'FileWriter', 'is_correct' => false],
                        ['text' => 'PrintWriter', 'is_correct' => false],
                        ['text' => 'BufferedReader', 'is_correct' => true],
                        ['text' => 'Scanner only', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A HashMap stores values in the order they were inserted.',
                    'explanation' => 'A basic HashMap does not guarantee insertion order. If order matters, consider LinkedHashMap.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'The try-with-resources statement automatically closes files after use.',
                    'explanation' => 'Try-with-resources declares resources inside the try statement and closes them automatically, even if an exception occurs.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What keyword marks a method in a subclass that replaces an inherited method? (one word)',
                    'explanation' => 'The @Override annotation indicates that a method overrides a method inherited from a parent class.',
                    'correct_answer' => 'Override',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In the Market Stock Manager project, why is file handling important?',
                    'explanation' => 'File handling allows the program to save stock data so it is not lost when the program closes.',
                    'options' => [
                        ['text' => 'It makes the program compile faster', 'is_correct' => false],
                        ['text' => 'It allows data to persist between program runs', 'is_correct' => true],
                        ['text' => 'It prevents all exceptions', 'is_correct' => false],
                        ['text' => 'It reduces the number of classes needed', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which Java technology is commonly used to build Android mobile applications?',
                    'explanation' => 'Java is one of the primary languages for Android development, along with Kotlin.',
                    'options' => [
                        ['text' => 'Spring Boot', 'is_correct' => false],
                        ['text' => 'Android SDK with Java', 'is_correct' => true],
                        ['text' => 'Laravel', 'is_correct' => false],
                        ['text' => 'React only', 'is_correct' => false],
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
            'title' => 'Mid-Course Java Console Program: Shop Change Calculator',
            'description' => 'Build a simple console Java program that calculates change for a cash purchase in Kwacha and ngwee, applying the control flow and arithmetic skills from Modules 1 to 3.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Open VS Code and create a new Java file named "ShopChangeCalculator.java" in your JavaPractice folder.
Step 2: Write a main method that declares variables for the item price, the amount paid by the customer, and the change due. Use double for money amounts.
Step 3: Calculate the change as amount paid minus price. If the amount paid is less than the price, print a message asking for more money.
Step 4: If change is due, calculate how many K50, K20, K10, K5, and K2 notes are needed, and how many K1, 50n, and 25n coins are needed. Use integer division and the modulus operator.
Step 5: Print a clear receipt showing the price, amount paid, change, and a breakdown of notes and coins.
Step 6: Test your program with at least three different purchase scenarios, including one where the customer pays with a K500 note for a small purchase.
Step 7: Take screenshots of your code and the console output, or submit the .java file itself along with a brief explanation of how your program works.
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
            'title' => 'End-of-Course Project: Market Stock Manager',
            'description' => 'Build a complete console Java application called Market Stock Manager that uses classes, collections, file handling, and exception handling to manage stock for a small market business.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Create a new folder named "MarketStockManager" inside your JavaPractice folder.
Step 2: Create a Product class with private fields for name, price, and quantity. Provide a constructor, getters, setters with validation, and a method getStockValue that returns price times quantity.
Step 3: Create a StockManager class that uses an ArrayList or HashMap to store Product objects. Implement methods to add a product, remove a product by name, update quantity, list all products, and calculate the total stock value.
Step 4: Create a MarketStockApp class with a main method that displays a text menu. The menu should repeat until the user chooses to exit, and should call the appropriate StockManager methods.
Step 5: Add file persistence. When the program exits, save all products to a text file named "stock.txt". When the program starts, load any existing data from "stock.txt" if it exists. Use try-catch to handle file errors gracefully.
Step 6: Add at least five products to your stock list during a test run. Show screenshots of adding, updating, removing, and listing products, plus the saved "stock.txt" file contents.
Step 7: Submit all .java source files, the stock.txt file, and screenshots or a short video demonstrating the program running. Include a README note explaining how to compile and run your project.
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
        $this->command->info('Java Programming content seeded successfully.');
        $this->command->info('Modules: 6 | Lessons: 28 | Quizzes: 6 | Assignments: 2');
    }
}
