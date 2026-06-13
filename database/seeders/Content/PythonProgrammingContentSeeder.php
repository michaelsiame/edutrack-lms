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

class PythonProgrammingContentSeeder extends Seeder
{
    private int $courseId;

    private array $created = [
        'lessons' => 0,
        'quizzes' => 0,
        'questions' => 0,
        'assignments' => 0,
    ];

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Python Programming')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Python Programming" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        DB::transaction(function () use ($course) {
            $modules = Module::where('course_id', $this->courseId)
                ->orderBy('display_order')
                ->get();

            if ($modules->isEmpty()) {
                $this->command->error('No modules found for Certificate in Python Programming. Aborting.');
                return;
            }

            foreach ($modules as $moduleIndex => $module) {
                $moduleNumber = $moduleIndex + 1;

                // Add missing lessons for this module.
                $desiredLessons = $this->{"module{$moduleNumber}Lessons"}();
                $existingLessonTitles = $module->lessons()->pluck('title')->toArray();
                $nextDisplayOrder = $module->lessons()->max('display_order') ?? 0;

                foreach ($desiredLessons as $lessonData) {
                    if (in_array($lessonData['title'], $existingLessonTitles)) {
                        $this->command->info("Skipping existing lesson: {$lessonData['title']}");
                        continue;
                    }

                    $nextDisplayOrder++;

                    Lesson::create([
                        'module_id' => $module->id,
                        'title' => $lessonData['title'],
                        'content' => $lessonData['content'] ?? null,
                        'lesson_type' => $lessonData['lesson_type'] ?? 'Reading',
                        'duration_minutes' => $lessonData['duration_minutes'],
                        'display_order' => $nextDisplayOrder,
                        'is_preview' => 0,
                        'is_mandatory' => 1,
                        'points' => 10,
                    ]);

                    $this->created['lessons']++;
                }

                // Recalculate module duration from all lessons.
                $module->duration_minutes = $module->lessons()->sum('duration_minutes');
                $module->save();

                // Add module quiz if it does not already exist.
                $quizData = $this->{"module{$moduleNumber}Quiz"}();
                $existingQuiz = Quiz::where('course_id', $this->courseId)
                    ->where('title', $quizData['title'])
                    ->first();

                if ($existingQuiz) {
                    $this->command->info("Skipping existing quiz: {$quizData['title']}");
                    continue;
                }

                $lastLesson = $module->lessons()->orderByDesc('display_order')->first();

                $quiz = Quiz::create([
                    'course_id' => $this->courseId,
                    'lesson_id' => $lastLesson?->id,
                    'title' => $quizData['title'],
                    'description' => $quizData['description'],
                    'quiz_type' => 'Graded',
                    'time_limit_minutes' => $quizData['time_limit_minutes'],
                    'max_attempts' => 3,
                    'passing_score' => 60.00,
                    'show_correct_answers' => 1,
                    'is_published' => 1,
                ]);

                $this->created['quizzes']++;

                foreach ($quizData['questions'] as $qIndex => $qData) {
                    $question = Question::create([
                        'question_type' => $qData['type'],
                        'question_text' => $qData['text'],
                        'points' => $qData['type'] === 'Short Answer' ? 3 : 2,
                        'explanation' => $qData['explanation'],
                        'correct_answer' => $qData['correct_answer'] ?? null,
                    ]);

                    $this->created['questions']++;

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

            $this->createAssignments();
        });

        $this->printSummary();
    }

    private function createAssignments(): void
    {
        $existingAssignmentCount = Assignment::where('course_id', $this->courseId)->count();

        if ($existingAssignmentCount >= 2) {
            $this->command->info('Certificate in Python Programming already has 2 or more assignments. Skipping assignment creation.');
            return;
        }

        $assignmentTitles = [
            'Mid-Course Python Project: Build a Shop Receipt Calculator',
            'End-of-Course Python Project: Build a Small Business Record System',
        ];

        foreach ($assignmentTitles as $title) {
            if (Assignment::where('course_id', $this->courseId)->where('title', $title)->exists()) {
                $this->command->info("Skipping existing assignment: {$title}");
                continue;
            }

            if (str_contains($title, 'Mid-Course')) {
                Assignment::create([
                    'course_id' => $this->courseId,
                    'lesson_id' => null,
                    'title' => $title,
                    'description' => 'Combine variables, input, arithmetic, and control flow to create a Python program that prints a receipt for a small shop in Kalomo.',
                    'instructions' => <<<'INSTRUCTIONS'
Step 1: Create a new Python file named "shop_receipt.py" in your PythonPractice folder.
Step 2: Ask the user for the names and prices of three items sold in a shop, for example mealie meal, cooking oil, and soap.
Step 3: Ask for the quantity sold for each item.
Step 4: Calculate the subtotal, add 16% VAT, and calculate the final total in Zambian Kwacha.
Step 5: Print a neatly formatted receipt showing each line item, the subtotal, VAT, and final total.
Step 6: Test your program with at least two different sets of values, including a sale where the customer buys more than one unit of an item.
Step 7: Submit your .py file and screenshots of the program running in IDLE, VS Code, or a mobile Python app.
INSTRUCTIONS,
                    'max_points' => 100,
                    'passing_points' => 50,
                    'due_date' => null,
                    'allow_late_submission' => 1,
                    'allowed_file_types' => 'pdf,doc,docx,jpg,png,py',
                    'max_file_size_mb' => 10,
                ]);
            } else {
                Assignment::create([
                    'course_id' => $this->courseId,
                    'lesson_id' => null,
                    'title' => $title,
                    'description' => 'Build a complete Python program that uses functions, classes, file handling, and exception handling to manage stock and sales for a small business.',
                    'instructions' => <<<'INSTRUCTIONS'
Step 1: Create a new folder named "SmallBusinessSystem" inside your PythonPractice folder.
Step 2: Create a Product class with attributes for name, price, and quantity. Include methods to calculate stock value and update quantity.
Step 3: Create a Business class that stores a list of Product objects and has methods to add products, sell products, and print a stock report.
Step 4: Save the stock to a CSV file named "stock.csv" when the program ends. Load the stock from "stock.csv" when the program starts.
Step 5: Add exception handling so the program does not crash if the CSV file is missing or the user enters invalid data.
Step 6: Demonstrate your program by adding at least five products, selling some items, and printing the final report.
Step 7: Submit all .py source files, the stock.csv file, and screenshots or a short video showing the program running. Include a brief note explaining how to run your code.
INSTRUCTIONS,
                    'max_points' => 100,
                    'passing_points' => 50,
                    'due_date' => null,
                    'allow_late_submission' => 1,
                    'allowed_file_types' => 'pdf,doc,docx,jpg,png,py,csv',
                    'max_file_size_mb' => 10,
                ]);
            }

            $this->created['assignments']++;
        }
    }

    private function printSummary(): void
    {
        $totalLessons = Lesson::whereHas('module', fn ($q) => $q->where('course_id', $this->courseId))->count();
        $totalQuizzes = Quiz::where('course_id', $this->courseId)->count();
        $totalQuestions = Question::whereHas('quizzes', fn ($q) => $q->where('course_id', $this->courseId))->count();
        $totalAssignments = Assignment::where('course_id', $this->courseId)->count();

        $this->command->info('Certificate in Python Programming content seed complete.');
        $this->command->info("Created this run: Lessons {$this->created['lessons']} | Quizzes {$this->created['quizzes']} | Questions {$this->created['questions']} | Assignments {$this->created['assignments']}");
        $this->command->info("Current totals: Lessons {$totalLessons} | Quizzes {$totalQuizzes} | Questions {$totalQuestions} | Assignments {$totalAssignments}");
    }

    // -------------------------------------------------------------------------
    // Module 1: Introduction to Python
    // -------------------------------------------------------------------------

    private function module1Lessons(): array
    {
        return [
            [
                'title' => '1.5 Python Tools for Zambian Learners — IDLE, VS Code, and Mobile Apps',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to install and open Python on a college computer, a laptop, or an Android smartphone; write and save a simple Python script; add comments to explain your code; and run your program so that you can practise even when mains power is unreliable.</p>

<h2>Why Python?</h2>
<p>Python is a programming language that is free to learn, easy to read, and used by beginners and professionals all over the world. In Zambia, Python can help a market trader calculate profit, a teacher prepare marks, a farmer track rainfall, or a young person apply for remote work. Because Python runs on Windows, Linux, Android, and macOS, you can learn it on almost any device you already own.</p>
<p>Python is also a good first language because its code looks close to normal English. A command such as <code>print("Hello, Kalomo!")</code> does exactly what it says: it prints the words on the screen. You do not need to memorise complicated symbols before you can write useful programs.</p>

<h2>Getting Python on Your Computer</h2>
<p>The official Python installer is available at <a href="https://www.python.org/downloads/">python.org/downloads</a>. At Edutrack Computer Training College you can ask the instructor to help you install Python on the lab computers. At home, download the installer for Windows, run it, and make sure the option "Add Python to PATH" is ticked before you click Install.</p>
<p>After installation you have two useful tools. The first is the <strong>Python interpreter</strong>, sometimes called the Python shell. You open it by typing <code>python</code> in the command prompt or terminal and pressing Enter. You can type one line at a time and see the result immediately. The second tool is an <strong>Integrated Development Environment</strong> (IDE). IDLE is installed automatically with Python. VS Code is a more advanced free editor that you can download separately.</p>

<h2>Python on a Smartphone</h2>
<p>If load-shedding means your laptop battery is flat, you can still practise on an Android phone. Install an app such as Pydroid 3 from the Google Play Store. It gives you an editor, a Python interpreter, and a way to run scripts. You can write small programs while travelling on a bus or waiting at a shop, then copy your files to the college computer later using WhatsApp, email, or a USB cable.</p>

<h2>Worked Example: Your First Saved Program</h2>
<p>Open IDLE and choose File &gt; New File. Type the following script exactly, then save it as <code>profit.py</code> in your PythonPractice folder:</p>
<pre><code># This program calculates profit for a market stall
price = 35
quantity = 12
total_sales = price * quantity
cost_price = 25
total_cost = cost_price * quantity
profit = total_sales - total_cost
print("Total sales: K", total_sales)
print("Total cost: K", total_cost)
print("Profit: K", profit)
</code></pre>
<p>Press F5 to run the program. The output shows that if you sell 12 items at K35 each after buying them at K25 each, your profit is K120. Change the numbers and run the program again. You are now controlling the computer with your own instructions.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Install Python on the college computer or install Pydroid 3 on your Android phone.</li>
<li>Open your Python editor and create a new file named <code>hello_zambia.py</code>.</li>
<li>Write two lines: one that prints your name, and one that calculates the change from K100 for an item that costs K35.</li>
<li>Save the file and run it. Fix any spelling mistakes and run it again.</li>
<li>Add a comment above each line explaining what it does.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Interpreter</strong> — a program that reads and executes Python code one line at a time.</li>
<li><strong>Script</strong> — a saved file that contains several lines of Python code.</li>
<li><strong>IDE</strong> — a text editor with extra tools that help you write and run code.</li>
<li><strong>Comment</strong> — text in a program that is ignored by Python and is meant for humans; it starts with a <code>#</code> symbol.</li>
<li><strong>Shell</strong> — the interactive window where you can type Python commands and see instant results.</li>
</ul>

<h2>Summary</h2>
<p>Python is free, runs on many devices, and uses readable English-like commands. You can install it on a college computer, a laptop, or an Android phone so that you can keep learning even when electricity is unpredictable. Writing a script, saving it, and running it is the basic cycle that every programmer repeats many times a day.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.python.org/about/gettingstarted/">Python.org — Getting Started</a></li>
<li><a href="https://www.w3schools.com/python/">W3Schools Python Tutorial</a></li>
<li><a href="https://www.freecodecamp.org/learn/scientific-computing-with-python/">freeCodeCamp — Scientific Computing with Python</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/paths/python-language/">Microsoft Learn — Python Language</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1: Introduction to Python Quiz',
            'description' => 'Check your understanding of Python tools, scripts, comments, and running programs.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does the print() function do in Python?',
                    'explanation' => 'print() displays output on the screen.',
                    'options' => [
                        ['text' => 'It deletes files.', 'is_correct' => false],
                        ['text' => 'It displays output on the screen.', 'is_correct' => true],
                        ['text' => 'It connects to the internet.', 'is_correct' => false],
                        ['text' => 'It repeats code forever.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which file extension is used for Python programs?',
                    'explanation' => 'Python source files use the .py extension.',
                    'options' => [
                        ['text' => '.doc', 'is_correct' => false],
                        ['text' => '.java', 'is_correct' => false],
                        ['text' => '.py', 'is_correct' => true],
                        ['text' => '.mp4', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is an example of a Python interpreter or IDE?',
                    'explanation' => 'IDLE is the simple IDE that comes with Python; Pydroid 3 runs Python on Android.',
                    'options' => [
                        ['text' => 'Microsoft Word', 'is_correct' => false],
                        ['text' => 'IDLE', 'is_correct' => true],
                        ['text' => 'Google Chrome', 'is_correct' => false],
                        ['text' => 'WhatsApp', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What symbol starts a comment in Python?',
                    'explanation' => 'Comments in Python begin with the # symbol.',
                    'options' => [
                        ['text' => '//', 'is_correct' => false],
                        ['text' => '#', 'is_correct' => true],
                        ['text' => '&lt;!--', 'is_correct' => false],
                        ['text' => '$', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which Android app is mentioned as a way to practise Python on a phone?',
                    'explanation' => 'Pydroid 3 is a Python IDE available for Android devices.',
                    'options' => [
                        ['text' => 'Pydroid 3', 'is_correct' => true],
                        ['text' => 'Excel Mobile', 'is_correct' => false],
                        ['text' => 'TikTok', 'is_correct' => false],
                        ['text' => 'Camera', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does IDE stand for?',
                    'explanation' => 'IDE stands for Integrated Development Environment.',
                    'options' => [
                        ['text' => 'Integrated Development Environment', 'is_correct' => true],
                        ['text' => 'Internal Data Entry', 'is_correct' => false],
                        ['text' => 'Internet Download Engine', 'is_correct' => false],
                        ['text' => 'Input Display Editor', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Python is free to download and use.',
                    'explanation' => 'Python is open-source software and is free for anyone to download and use.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A script is a saved set of instructions that can be run many times.',
                    'explanation' => 'A script is a file containing code that you can save and execute whenever you need it.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What function displays text in Python? (one word)',
                    'explanation' => 'The print() function is used to display text and values on the screen.',
                    'correct_answer' => 'print',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why might a student in Kalomo practise Python on a phone app?',
                    'explanation' => 'A phone app lets you practise when mains power is off or a laptop is unavailable.',
                    'options' => [
                        ['text' => 'It makes phone calls cheaper.', 'is_correct' => false],
                        ['text' => 'It works even during load-shedding.', 'is_correct' => true],
                        ['text' => 'It improves the camera quality.', 'is_correct' => false],
                        ['text' => 'It installs Microsoft Office.', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Module 2: Data Types and Variables
    // -------------------------------------------------------------------------

    private function module2Lessons(): array
    {
        return [
            [
                'title' => '2.5 Type Conversion and User Input: Building a Bus Fare Calculator',
                'duration_minutes' => 70,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to ask the user for information while a program is running, convert text input into numbers, perform calculations, and build a simple bus fare calculator that works for real journeys in Zambia.</p>

<h2>Making Programs Interactive</h2>
<p>So far your programs have used fixed values. In real life, prices change, quantities vary, and users want to enter their own data. Python provides the <code>input()</code> function to pause the program and wait for the user to type something. Whatever the user types is returned as a <strong>string</strong>, which is Python's name for text.</p>
<p>Imagine a conductor on a minibus from Kalomo to Livingstone. The fare may be K120 per passenger, but the number of passengers changes every trip. The conductor needs a quick way to work out the total. A Python program can ask for the number of passengers and then multiply it by the fare.</p>

<h2>Reading Numbers from Input</h2>
<p>Because <code>input()</code> returns text, you must convert it before doing maths. Use <code>int()</code> to convert to a whole number and <code>float()</code> to convert to a decimal number. Here is a simple fare calculator:</p>
<pre><code>fare = 120
passengers = int(input("How many passengers? "))
total = fare * passengers
print("Total fare: K", total)
</code></pre>
<p>If the user enters <code>3</code>, the program converts it to the integer 3, multiplies by 120, and prints <code>Total fare: K 360</code>. This is exactly the kind of tool a transport business can use at the start of each journey.</p>

<h2>Handling Decimal Money</h2>
<p>Not all amounts are whole numbers. A bag of mealie meal may cost K189.99. When you need to keep decimals, use <code>float()</code> instead of <code>int()</code>. For example:</p>
<pre><code>price = float(input("Enter the price per item: K"))
quantity = int(input("How many items? "))
total = price * quantity
print("Amount due: K", total)
</code></pre>
<p>Be careful with money in production systems: banks often avoid floats because of tiny rounding errors. For learning purposes, floats are fine, and later you can learn about the <code>decimal</code> module.</p>

<h2>Worked Example: Shop Bill with Discount</h2>
<p>A small shop in Soweto Market sells cooking oil at K85 per bottle. The owner gives a K5 discount for every bottle bought in bulk above ten. The program below calculates the bill:</p>
<pre><code>price = 85
quantity = int(input("How many bottles? "))
discount_per_bottle = 5 if quantity > 10 else 0
unit_price = price - discount_per_bottle
total = unit_price * quantity
print("Quantity:", quantity)
print("Unit price: K", unit_price)
print("Total: K", total)
</code></pre>
<p>This example uses a conditional expression to set the discount. Even though conditionals are covered in the next module, you can see how type conversion and input make the program practical.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a new file named <code>bus_fare.py</code>.</li>
<li>Ask the user for the fare per passenger and the number of passengers.</li>
<li>Convert both inputs to the correct types and calculate the total fare.</li>
<li>Print the result in a friendly sentence such as "The total fare for 4 passengers is K480."</li>
<li>Test your program with the Kalomo to Livingstone fare of K120 for 3 passengers, and with a local fare of K15 for 12 passengers.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>input()</strong> — a Python function that reads text typed by the user.</li>
<li><strong>int()</strong> — a function that converts a value into a whole number.</li>
<li><strong>float()</strong> — a function that converts a value into a decimal number.</li>
<li><strong>string</strong> — a data type that represents text in Python.</li>
<li><strong>TypeError</strong> — an error that happens when you try to use a value in a way that does not match its type.</li>
</ul>

<h2>Summary</h2>
<p>Interactive programs are more useful than programs with fixed values. Use <code>input()</code> to collect data from the user, then convert the data with <code>int()</code> or <code>float()</code> before doing calculations. This simple pattern lies behind every calculator, point-of-sale system, and mobile money app you use.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/python/python_user_input.asp">W3Schools — Python User Input</a></li>
<li><a href="https://docs.python.org/3/library/functions.html#input">Python Docs — input()</a></li>
<li><a href="https://www.freecodecamp.org/news/python-input-function-how-to-get-user-input/">freeCodeCamp — Python input() Function</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2: Data Types and Variables Quiz',
            'description' => 'Test your knowledge of variables, data types, input, and type conversion.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which function reads text typed by the user?',
                    'explanation' => 'input() pauses the program and returns whatever the user types.',
                    'options' => [
                        ['text' => 'print()', 'is_correct' => false],
                        ['text' => 'input()', 'is_correct' => true],
                        ['text' => 'read()', 'is_correct' => false],
                        ['text' => 'scan()', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the data type of the value 25?',
                    'explanation' => '25 is a whole number, so its type is int.',
                    'options' => [
                        ['text' => 'string', 'is_correct' => false],
                        ['text' => 'float', 'is_correct' => false],
                        ['text' => 'int', 'is_correct' => true],
                        ['text' => 'bool', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the data type of "K50"?',
                    'explanation' => 'Values inside quotation marks are strings in Python.',
                    'options' => [
                        ['text' => 'int', 'is_correct' => false],
                        ['text' => 'float', 'is_correct' => false],
                        ['text' => 'string', 'is_correct' => true],
                        ['text' => 'list', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which function converts the string "100" into the whole number 100?',
                    'explanation' => 'int() converts a suitable string or number into an integer.',
                    'options' => [
                        ['text' => 'str()', 'is_correct' => false],
                        ['text' => 'float()', 'is_correct' => false],
                        ['text' => 'int()', 'is_correct' => true],
                        ['text' => 'bool()', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What happens when you run "5" + 3 in Python?',
                    'explanation' => 'You cannot add a string and an integer directly; Python raises a TypeError.',
                    'options' => [
                        ['text' => 'It prints 8.', 'is_correct' => false],
                        ['text' => 'It prints "53".', 'is_correct' => false],
                        ['text' => 'It raises a TypeError.', 'is_correct' => true],
                        ['text' => 'It prints 15.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which operator gives the remainder after division?',
                    'explanation' => 'The modulus operator % returns the remainder.',
                    'options' => [
                        ['text' => '/', 'is_correct' => false],
                        ['text' => '//', 'is_correct' => false],
                        ['text' => '%', 'is_correct' => true],
                        ['text' => '*', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A variable name in Python can start with a number.',
                    'explanation' => 'Variable names must start with a letter or an underscore, not a digit.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'The input() function always returns a string.',
                    'explanation' => 'input() returns a string even if the user types only digits.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'Which function converts a string to a decimal number? (one word)',
                    'explanation' => 'float() converts a value to a decimal number.',
                    'correct_answer' => 'float',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'If mealie_meal = 180 and quantity = 2, what is mealie_meal * quantity?',
                    'explanation' => '180 multiplied by 2 equals 360.',
                    'options' => [
                        ['text' => 'K360', 'is_correct' => true],
                        ['text' => 'K182', 'is_correct' => false],
                        ['text' => 'K90', 'is_correct' => false],
                        ['text' => 'K380', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Module 3: Control Flow
    // -------------------------------------------------------------------------

    private function module3Lessons(): array
    {
        return [
            [
                'title' => '3.5 Planning Code with Pseudocode and Flowcharts',
                'duration_minutes' => 65,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to plan a program before you write it, describe logic using plain-language pseudocode, draw a simple flowchart, and trace through loops and decisions so that your programs do what you intend.</p>

<h2>Why Plan Before Coding?</h2>
<p>Many beginners open their editor and start typing immediately. This works for tiny programs, but as soon as a problem becomes slightly complex, mistakes creep in. Planning first saves time because you think through the steps, spot problems early, and explain your idea to others before committing to code.</p>
<p>Consider a small shop owner who wants to buy ZESCO units only when the meter balance is low. The steps seem simple, but a program needs to know the current balance, compare it to a limit, decide whether to buy tokens, and repeat this check every day. Writing this straight into Python without planning often leads to missing steps.</p>

<h2>Pseudocode</h2>
<p><strong>Pseudocode</strong> is a plain-language description of what a program should do. It is not real code, so there are no strict rules, but it should be clear enough that another person can follow it. Here is pseudocode for the ZESCO token decision:</p>
<pre><code>START
    SET low_balance_limit to 50
    ASK user for current_balance
    IF current_balance &lt; low_balance_limit THEN
        SHOW "Buy ZESCO tokens"
        ASK user how many units to buy
        CALCULATE total cost
        SHOW total cost
    ELSE
        SHOW "Balance is okay"
    END IF
END
</code></pre>
<p>This plan shows the decision, the input, the calculation, and the output. Once the plan is clear, turning it into Python is much easier.</p>

<h2>Flowcharts</h2>
<p>A flowchart is a picture of the same plan. Different shapes mean different things. A rounded rectangle means start or stop. A diamond means a decision. A rectangle means a process. Arrows show the order of steps. You can draw a flowchart on paper, on a blackboard, or using a free tool. The value is that you can see loops and branches at a glance.</p>

<h2>Tracing Code by Hand</h2>
<p>Before you run a program, you can trace it with a pen and paper. Write down each variable and update it as you move through the code. For example, trace this loop:</p>
<pre><code>total = 0
for day in range(7):
    sales = float(input("Enter sales for day: "))
    total = total + sales
print("Weekly sales:", total)
</code></pre>
<p>On paper, write <code>total = 0</code>. Then imagine entering sales of 100, 150, and 90. Update total to 100, then 250, then 340. Tracing helps you confirm that the loop repeats the correct number of times and that the total grows correctly.</p>

<h2>Worked Example: Sending Mobile Money</h2>
<p>Pseudocode for sending MTN MoMo to a vendor could look like this:</p>
<pre><code>START
    ASK for vendor_phone_number
    ASK for amount_to_send
    IF amount_to_send &gt; account_balance THEN
        SHOW "Insufficient funds"
    ELSE
        DEDUCT amount_to_send from account_balance
        SHOW "Sent K" + amount_to_send + " to " + vendor_phone_number
    END IF
END
</code></pre>
<p>This plan shows a real decision that protects the user from sending money they do not have. When you convert it to Python, the if-else block will match the plan almost line for line.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Pick a daily decision you make, such as whether to carry an umbrella, buy airtime, or take a minibus.</li>
<li>Write pseudocode for that decision on paper. Include at least one input and one IF statement.</li>
<li>Draw a simple flowchart with a start, a decision diamond, and an end.</li>
<li>Trace your pseudocode with three different inputs to make sure every path works.</li>
<li>Open your Python editor and convert the pseudocode into a working program.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Pseudocode</strong> — an informal, plain-language plan for a program.</li>
<li><strong>Flowchart</strong> — a diagram that shows the steps and decisions in a process.</li>
<li><strong>Algorithm</strong> — a step-by-step set of instructions for solving a problem.</li>
<li><strong>Trace</strong> — to follow the values of variables through a program by hand.</li>
<li><strong>Decision</strong> — a point in a program where different actions are taken based on a condition.</li>
</ul>

<h2>Summary</h2>
<p>Good programmers plan before they code. Pseudocode lets you describe logic in plain English, flowcharts let you see the logic visually, and tracing lets you check your plan before the computer runs it. These habits turn vague ideas into reliable programs.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/computing/computer-science/algorithms/intro-to-algorithms/a/what-are-algorithms">Khan Academy — What Are Algorithms?</a></li>
<li><a href="https://www.w3schools.com/python/python_conditions.asp">W3Schools — Python Conditions</a></li>
<li><a href="https://www.freecodecamp.org/news/introduction-to-algorithms-with-python/">freeCodeCamp — Introduction to Algorithms with Python</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3: Control Flow Assessment',
            'description' => 'Test your understanding of decisions, loops, and planning code.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which keyword starts a conditional statement in Python?',
                    'explanation' => 'The if keyword introduces a conditional block.',
                    'options' => [
                        ['text' => 'if', 'is_correct' => true],
                        ['text' => 'else', 'is_correct' => false],
                        ['text' => 'while', 'is_correct' => false],
                        ['text' => 'for', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which loop repeats while a condition stays true?',
                    'explanation' => 'A while loop continues as long as its condition is true.',
                    'options' => [
                        ['text' => 'for', 'is_correct' => false],
                        ['text' => 'while', 'is_correct' => true],
                        ['text' => 'if', 'is_correct' => false],
                        ['text' => 'def', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does the following code print? for i in range(3): print(i)',
                    'explanation' => 'range(3) produces 0, 1, and 2.',
                    'options' => [
                        ['text' => '0 1 2', 'is_correct' => true],
                        ['text' => '1 2 3', 'is_correct' => false],
                        ['text' => '0 1 2 3', 'is_correct' => false],
                        ['text' => '3', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which block runs when an if condition is false?',
                    'explanation' => 'The else block runs when the if condition is false.',
                    'options' => [
                        ['text' => 'else', 'is_correct' => true],
                        ['text' => 'elif', 'is_correct' => false],
                        ['text' => 'except', 'is_correct' => false],
                        ['text' => 'finally', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which loop is best when you know exactly how many times to repeat?',
                    'explanation' => 'A for loop is ideal when the number of repetitions is known.',
                    'options' => [
                        ['text' => 'while', 'is_correct' => false],
                        ['text' => 'for', 'is_correct' => true],
                        ['text' => 'if', 'is_correct' => false],
                        ['text' => 'switch', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does the break statement do inside a loop?',
                    'explanation' => 'break immediately exits the loop.',
                    'options' => [
                        ['text' => 'Skips one iteration', 'is_correct' => false],
                        ['text' => 'Exits the loop completely', 'is_correct' => true],
                        ['text' => 'Starts the loop again', 'is_correct' => false],
                        ['text' => 'Prints a message', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Indentation is required in Python if/else blocks.',
                    'explanation' => 'Python uses indentation to show which lines belong to an if or else block.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A while loop always runs at least one time.',
                    'explanation' => 'A while loop checks its condition before running, so it may not run at all.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What keyword checks another condition after an if statement? (one word)',
                    'explanation' => 'elif is short for "else if" and checks another condition.',
                    'correct_answer' => 'elif',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is pseudocode?',
                    'explanation' => 'Pseudocode is an informal plan written in plain language.',
                    'options' => [
                        ['text' => 'A secret password', 'is_correct' => false],
                        ['text' => 'An informal plan in plain language', 'is_correct' => true],
                        ['text' => 'A type of Python error', 'is_correct' => false],
                        ['text' => 'A built-in Python module', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }


    // -------------------------------------------------------------------------
    // Module 4: Functions and Modules
    // -------------------------------------------------------------------------

    private function module4Lessons(): array
    {
        return [
            [
                'title' => '4.1 Defining and Calling Functions',
                'duration_minutes' => 70,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what a function is, define your own functions using the <code>def</code> keyword, call a function to make it run, and understand why functions help you write cleaner, reusable code.</p>

<h2>What Is a Function?</h2>
<p>A function is a named block of code that performs a specific task. You have already used built-in functions such as <code>print()</code>, <code>input()</code>, and <code>len()</code>. Now you will learn to create your own functions. Functions are powerful because they let you write a solution once and use it many times, just like a recipe you can follow whenever you want to cook the same meal.</p>
<p>Imagine a chicken-rearing side business in Kalomo. Every week the owner calculates profit by subtracting feed costs from sales. Instead of typing the same calculation again and again, she can write a function called <code>calculate_profit</code> and call it whenever she has new numbers.</p>

<h2>Defining a Function</h2>
<p>In Python you define a function with the <code>def</code> keyword, followed by a name, parentheses, and a colon. The lines inside the function are indented. Here is a simple example:</p>
<pre><code>def greet_customer():
    print("Welcome to Edutrack Shop!")
    print("How may I help you today?")

# Calling the function
greet_customer()
</code></pre>
<p>When Python reaches the definition, it remembers the code but does not run it. When you call <code>greet_customer()</code>, Python jumps to the function, runs the two print statements, and then returns to where it was called.</p>

<h2>Worked Example: A Profit Function</h2>
<p>Below is a function that calculates weekly profit for a small business:</p>
<pre><code>def calculate_profit(sales, costs):
    profit = sales - costs
    print("Profit for this week: K", profit)

# Using the function with different weeks
calculate_profit(1200, 800)
calculate_profit(1500, 900)
calculate_profit(950, 700)
</code></pre>
<p>Each call uses the same logic but with different numbers. If the business owner later wants to show profit as a percentage, she only needs to change one place: inside the function. Every call will automatically use the updated logic.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a new file named <code>functions_practice.py</code>.</li>
<li>Write a function named <code>say_hello</code> that prints "Hello, welcome to Python!".</li>
<li>Write a function named <code>show_price</code> that prints the price of an item. Call it with the price of a 2-litre bottle of cooking oil.</li>
<li>Write a function named <code>calculate_area</code> that prints the area of a rectangular plot given its length and width. Test it with a plot that is 20 metres by 15 metres.</li>
<li>Call each function at least twice with different values.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Function</strong> — a named block of code that performs a specific task.</li>
<li><strong>def</strong> — the Python keyword used to define a function.</li>
<li><strong>Call</strong> — to run a function by writing its name followed by parentheses.</li>
<li><strong>Argument</strong> — a value passed into a function when it is called.</li>
<li><strong>Return</strong> — to send a result back from a function to the caller.</li>
</ul>

<h2>Summary</h2>
<p>Functions let you name, store, and reuse blocks of code. They make programs shorter, easier to read, and easier to fix because a change in one place updates every place that uses the function. The pattern of defining a function with <code>def</code> and calling it by name is one of the most important skills in Python.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/python/python_functions.asp">W3Schools — Python Functions</a></li>
<li><a href="https://docs.python.org/3/tutorial/controlflow.html#defining-functions">Python Docs — Defining Functions</a></li>
<li><a href="https://www.freecodecamp.org/news/functions-in-python/">freeCodeCamp — Functions in Python</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.2 Parameters, Arguments, and Return Values',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to pass data into functions using parameters, send results back using return values, and use default arguments so that your functions are flexible and easy to reuse.</p>

<h2>Parameters and Arguments</h2>
<p>A <strong>parameter</strong> is a name listed in the function definition. An <strong>argument</strong> is the actual value passed to the function when it is called. The two words are related but not identical. Think of a parameter as an empty box and an argument as the item placed inside the box.</p>
<pre><code>def show_ticket_price(destination, price):
    print("Ticket to", destination, "costs K", price)

show_ticket_price("Livingstone", 120)
show_ticket_price("Lusaka", 180)
</code></pre>
<p>In this example, <code>destination</code> and <code>price</code> are parameters. <code>"Livingstone"</code> and <code>120</code> are arguments. The function becomes useful because it works for any destination and any price.</p>

<h2>Return Values</h2>
<p>Sometimes a function needs to give a result back so the caller can use it later. The <code>return</code> keyword sends a value back. For example, a function that calculates change should return the amount so the main program can decide what notes to give:</p>
<pre><code>def calculate_change(amount_paid, price):
    change = amount_paid - price
    return change

change_due = calculate_change(500, 340)
print("Give the customer K", change_due, "change.")
</code></pre>
<p>Without <code>return</code>, the function would print the change but the rest of the program would not be able to use the number. Returning values makes functions building blocks for larger programs.</p>

<h2>Default Arguments</h2>
<p>You can give a parameter a default value. If the caller does not provide an argument, the default is used. This is useful for common cases. For example, a bus company may have a standard fare of K120, but occasionally runs a special fare:</p>
<pre><code>def bus_fare(passengers, fare=120):
    return passengers * fare

print(bus_fare(3))       # Uses default fare: K360
print(bus_fare(3, 100))  # Uses special fare: K300
</code></pre>
<p>Default arguments reduce typing and make functions easier to use for typical situations while still allowing unusual situations.</p>

<h2>Worked Example: Shop Discount</h2>
<p>A shop gives a 10% discount to customers who spend K200 or more. The function below returns the final amount to pay:</p>
<pre><code>def final_amount(total):
    if total >= 200:
        discount = total * 0.10
        return total - discount
    return total

print(final_amount(250))  # Returns 225.0
print(final_amount(150))  # Returns 150
</code></pre>
<p>The function has two return paths. If the total is large enough, it subtracts the discount. Otherwise it returns the original total. The caller simply receives the correct amount and does not need to know how the decision was made.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>bus_fare_function.py</code>.</li>
<li>Write a function <code>total_fare(passengers, fare)</code> that returns the total fare.</li>
<li>Add a default fare of K120 and test the function with and without the second argument.</li>
<li>Write a function <code>change(amount_paid, price)</code> that returns the change.</li>
<li>Use the change function inside a small program that tells the cashier how much change to give for a K500 note.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Parameter</strong> — a variable listed in a function definition that receives an argument.</li>
<li><strong>Argument</strong> — a value passed into a function when it is called.</li>
<li><strong>Return value</strong> — the result sent back by a function to the caller.</li>
<li><strong>Default argument</strong> — a parameter value that is used when no argument is supplied.</li>
<li><strong>Scope</strong> — the area of a program where a variable can be used.</li>
</ul>

<h2>Summary</h2>
<p>Functions become truly useful when they accept inputs, make decisions, and return results. Parameters and arguments let you reuse the same logic with different data, while return values let the rest of the program use the result. Default arguments add convenience without sacrificing flexibility.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/python/python_functions.asp">W3Schools — Python Functions</a></li>
<li><a href="https://docs.python.org/3/tutorial/controlflow.html#more-on-defining-functions">Python Docs — More on Defining Functions</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/python-functions/">Microsoft Learn — Python Functions</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.3 Built-in Modules and Creating Your Own Module',
                'duration_minutes' => 70,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to import and use Python's built-in modules, understand the difference between the standard library and external libraries, and create your own module file that can be reused across different programs.</p>

<h2>What Is a Module?</h2>
<p>A module is a file that contains Python code, usually functions and variables, that you can reuse in other programs. Python comes with many built-in modules collected in the <strong>standard library</strong>. These modules provide ready-made tools for maths, dates, random numbers, file handling, and much more. Using modules saves you from writing everything from scratch.</p>
<p>For example, if you want to calculate the square root of a number, you do not need to write the maths yourself. You can import the <code>math</code> module and use <code>math.sqrt()</code>.</p>

<h2>Importing Built-in Modules</h2>
<p>The <code>import</code> keyword loads a module into your program. Here are some useful examples:</p>
<pre><code>import math
print(math.sqrt(144))   # 12.0

import random
print(random.randint(1, 6))  # A random number between 1 and 6

import datetime
today = datetime.date.today()
print(today)
</code></pre>
<p>The <code>math</code> module helps with school results and engineering. The <code>random</code> module is useful for games and lucky draws. The <code>datetime</code> module is essential for invoices, schedules, and age calculations.</p>

<h2>Creating Your Own Module</h2>
<p>Any Python file can be a module. Create a file named <code>utilities.py</code> and add functions to it:</p>
<pre><code># utilities.py
def discount_price(price, percent):
    return price - (price * percent / 100)

def format_money(amount):
    return "K{:.2f}".format(amount)
</code></pre>
<p>Now create another file in the same folder and import your module:</p>
<pre><code># main.py
import utilities

original = 250
discounted = utilities.discount_price(original, 10)
print("Original:", utilities.format_money(original))
print("Discounted:", utilities.format_money(discounted))
</code></pre>
<p>When you run <code>main.py</code>, Python finds <code>utilities.py</code> in the same folder and uses the functions inside it. This is how real projects stay organised: each file has a clear responsibility.</p>

<h2>Worked Example: Random Token Generator</h2>
<p>Imagine a school raffle where each ticket needs a random number between 1000 and 9999. The program below uses the <code>random</code> module:</p>
<pre><code>import random

def draw_ticket():
    return random.randint(1000, 9999)

print("Winning ticket:", draw_ticket())
</code></pre>
<p>This program is short because the hard work of generating random numbers is handled by the module. The programmer only needs to know the right function to call.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>conversions.py</code> and write a function <code>km_to_miles(km)</code> that returns the distance in miles. There are approximately 0.621371 miles in a kilometre.</li>
<li>Create a second file named <code>travel.py</code> that imports <code>conversions</code> and converts 350 km to miles.</li>
<li>Use the <code>math</code> module to print the area of a circle with radius 7 metres. The formula is pi times radius squared.</li>
<li>Use the <code>datetime</code> module to print today's date in the format "Today is 11 June 2026".</li>
<li>Test that both files run without errors.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Module</strong> — a Python file containing reusable code.</li>
<li><strong>Import</strong> — the act of loading a module into a program.</li>
<li><strong>Standard library</strong> — the collection of modules that come with Python.</li>
<li><strong>Namespace</strong> — the area where names such as variables and functions are stored.</li>
<li><strong>Library</strong> — a collection of modules that provide extra functionality.</li>
</ul>

<h2>Summary</h2>
<p>Modules let you reuse your own code and tap into code written by others. Python's standard library gives you free tools for maths, dates, random numbers, and more. As your programs grow, splitting code into modules keeps each file short and focused.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://docs.python.org/3/tutorial/modules.html">Python Docs — Modules</a></li>
<li><a href="https://www.w3schools.com/python/python_modules.asp">W3Schools — Python Modules</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/python-standard-library/">Microsoft Learn — Python Standard Library</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.4 Building a ZESCO Token Cost Calculator with Functions',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to break a real problem into smaller functions, pass data between those functions, and build a working ZESCO token cost calculator that a household or small business could actually use.</p>

<h2>Breaking a Problem into Functions</h2>
<p>Large programs can be overwhelming if you try to write everything at once. The solution is <strong>decomposition</strong>: breaking the problem into smaller tasks and writing one function for each task. A ZESCO token calculator needs to:</p>
<ul>
<li>ask the user how many kilowatt-hours (kWh) they want,</li>
<li>look up the price per kWh,</li>
<li>calculate the total cost,</li>
<li>apply any discount for buying in bulk,</li>
<li>print a clear receipt.</li>
</ul>
<p>Each of these steps can become a function. The main part of the program simply calls the functions in the right order.</p>

<h2>Worked Example: Token Calculator</h2>
<pre><code>def get_units():
    return float(input("How many kWh do you want? "))

def price_per_kwh():
    return 1.20

def calculate_cost(units, rate):
    return units * rate

def apply_discount(cost, units):
    if units >= 100:
        return cost * 0.95  # 5% discount
    return cost

def print_receipt(units, cost):
    print("--- ZESCO Token Receipt ---")
    print("Units:", units, "kWh")
    print("Total cost: K{:.2f}".format(cost))
    print("Thank you for using our service.")

# Main program
units = get_units()
rate = price_per_kwh()
cost = calculate_cost(units, rate)
final_cost = apply_discount(cost, units)
print_receipt(units, final_cost)
</code></pre>
<p>Each function has one job. If the power company changes the tariff, you only update <code>price_per_kwh</code>. If the discount rules change, you only update <code>apply_discount</code>. This makes the program easy to maintain.</p>

<h2>Why This Matters</h2>
<p>A small shop owner in Kalomo may need to budget for electricity each month. By entering the number of units she plans to buy, she can see the total cost immediately. She can also experiment with buying more units to see if the discount saves money. Functions make the calculator reliable because each part is tested separately.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>zesco_calculator.py</code>.</li>
<li>Copy the example above and run it.</li>
<li>Change the price per kWh to the current local rate if you know it.</li>
<li>Add a function named <code>add_vat(cost)</code> that adds 16% VAT and returns the new total.</li>
<li>Update the main program so that VAT is added after the discount.</li>
<li>Test the calculator with 50 kWh, 100 kWh, and 200 kWh.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Decomposition</strong> — breaking a large problem into smaller, manageable parts.</li>
<li><strong>Helper function</strong> — a small function that performs part of a larger task.</li>
<li><strong>Reusable code</strong> — code written so it can be used in several places or programs.</li>
<li><strong>Maintainability</strong> — how easy it is to update a program when requirements change.</li>
<li><strong>Receipt</strong> — a printed record showing details of a transaction.</li>
</ul>

<h2>Summary</h2>
<p>Functions shine when they work together to solve real problems. By splitting a ZESCO token calculator into input, calculation, discount, and output functions, you create a program that is easy to read, test, and update. This is the same approach used in professional software for banks, shops, and utilities.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/python/python_functions.asp">W3Schools — Python Functions</a></li>
<li><a href="https://docs.python.org/3/tutorial/controlflow.html#defining-functions">Python Docs — Defining Functions</a></li>
<li><a href="https://www.freecodecamp.org/news/functions-in-python/">freeCodeCamp — Functions in Python</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module4Quiz(): array
    {
        return [
            'title' => 'Module 4: Functions and Modules Quiz',
            'description' => 'Test your knowledge of defining functions, parameters, return values, and modules.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which keyword is used to define a function in Python?',
                    'explanation' => 'The def keyword introduces a function definition.',
                    'options' => [
                        ['text' => 'function', 'is_correct' => false],
                        ['text' => 'def', 'is_correct' => true],
                        ['text' => 'define', 'is_correct' => false],
                        ['text' => 'func', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which keyword sends a value back from a function to the caller?',
                    'explanation' => 'return is used to send a result back from a function.',
                    'options' => [
                        ['text' => 'send', 'is_correct' => false],
                        ['text' => 'return', 'is_correct' => true],
                        ['text' => 'yield', 'is_correct' => false],
                        ['text' => 'exit', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In total_cost(180, 2), what are 180 and 2 called?',
                    'explanation' => 'Values passed to a function are called arguments.',
                    'options' => [
                        ['text' => 'Parameters', 'is_correct' => false],
                        ['text' => 'Arguments', 'is_correct' => true],
                        ['text' => 'Variables', 'is_correct' => false],
                        ['text' => 'Returns', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is a default argument?',
                    'explanation' => 'A default argument is a parameter value used when the caller supplies no argument.',
                    'options' => [
                        ['text' => 'An argument that must always be provided', 'is_correct' => false],
                        ['text' => 'A value used when no argument is supplied', 'is_correct' => true],
                        ['text' => 'An error message', 'is_correct' => false],
                        ['text' => 'A type of loop', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why do programmers use functions?',
                    'explanation' => 'Functions reduce duplication, make testing easier, and organise code.',
                    'options' => [
                        ['text' => 'To make programs longer', 'is_correct' => false],
                        ['text' => 'To reduce duplication and organise code', 'is_correct' => true],
                        ['text' => 'To hide errors', 'is_correct' => false],
                        ['text' => 'To avoid comments', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which statement correctly imports the math module?',
                    'explanation' => 'Use the import keyword followed by the module name.',
                    'options' => [
                        ['text' => 'use math', 'is_correct' => false],
                        ['text' => 'include math', 'is_correct' => false],
                        ['text' => 'import math', 'is_correct' => true],
                        ['text' => 'load math', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A function can be defined without any parameters.',
                    'explanation' => 'Functions can have empty parentheses if they need no input.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'The return statement must always return a number.',
                    'explanation' => 'return can send back any type of value, including strings, lists, and booleans.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What keyword defines a function in Python? (one word)',
                    'explanation' => 'The def keyword is used to define functions.',
                    'correct_answer' => 'def',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is decomposition in programming?',
                    'explanation' => 'Decomposition means breaking a problem into smaller parts.',
                    'options' => [
                        ['text' => 'Combining all code into one long file', 'is_correct' => false],
                        ['text' => 'Breaking a problem into smaller parts', 'is_correct' => true],
                        ['text' => 'Deleting old code', 'is_correct' => false],
                        ['text' => 'Encrypting data', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }


    // -------------------------------------------------------------------------
    // Module 5: Object-Oriented Programming
    // -------------------------------------------------------------------------

    private function module5Lessons(): array
    {
        return [
            [
                'title' => '5.1 Classes and Objects: Modelling Real Things',
                'duration_minutes' => 70,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what a class and an object are, create a simple class in Python, create objects from that class, and understand how object-oriented programming helps you model real people, products, and businesses.</p>

<h2>From Real Things to Code</h2>
<p>Every day you deal with objects: a phone, a chair, a bag of mealie meal, a customer, a minibus. Each object has characteristics and behaviours. A customer has a name, a phone number, and maybe an NRC number. A customer can also place an order, pay money, or ask a question. In programming, a <strong>class</strong> is a blueprint that describes what an object is like, and an <strong>object</strong> is one actual example built from that blueprint.</p>
<p>Think of a class like a recipe for nshima. The recipe describes the ingredients and steps. One pot of nshima cooked at home is an object. Another pot cooked at college is a different object, but both follow the same recipe.</p>

<h2>Defining a Class</h2>
<p>In Python you define a class with the <code>class</code> keyword. The special method <code>__init__</code> runs when a new object is created and sets up its initial data. Here is a Customer class:</p>
<pre><code>class Customer:
    def __init__(self, name, phone, nrc):
        self.name = name
        self.phone = phone
        self.nrc = nrc

customer1 = Customer("Grace Banda", "0977123456", "123456/78/9")
customer2 = Customer("John Phiri", "0966123456", "987654/32/1")

print(customer1.name)
print(customer2.phone)
</code></pre>
<p><code>self</code> refers to the object being created. When we write <code>customer1.name</code>, we are asking for the name stored inside the <code>customer1</code> object.</p>

<h2>Worked Example: Modelling a Product</h2>
<p>A small shop in Kalomo sells many products. Each product has a name, a price, and a quantity in stock. Instead of using separate variables for every product, the shop owner can use a class:</p>
<pre><code>class Product:
    def __init__(self, name, price, quantity):
        self.name = name
        self.price = price
        self.quantity = quantity

mealie_meal = Product("Mealie meal 25kg", 189.99, 20)
oil = Product("Cooking oil 2L", 85.00, 15)

print(mealie_meal.name, "costs K", mealie_meal.price)
</code></pre>
<p>This code is clean because all the information about a product stays together in one object. If the price changes, you update the object, not a scattered list of variables.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>classes_practice.py</code>.</li>
<li>Define a <code>Student</code> class with attributes for name, course, and phone number.</li>
<li>Create two student objects with your own details and a friend's details.</li>
<li>Print each student's name and course.</li>
<li>Define a <code>Product</code> class and create three products you might find in a shop near your home.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Class</strong> — a blueprint that defines what objects of a certain type will contain and do.</li>
<li><strong>Object</strong> — a single instance created from a class.</li>
<li><strong>Attribute</strong> — a piece of data stored inside an object.</li>
<li><strong>Instance</strong> — another word for an object created from a class.</li>
<li><strong>Blueprint</strong> — a plan that describes how something should be built.</li>
</ul>

<h2>Summary</h2>
<p>Classes and objects let you group related data together and model real-world things in code. A class is the blueprint, and each object is a specific example. This approach is the foundation of object-oriented programming and is used in almost every professional software system.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/python/python_classes.asp">W3Schools — Python Classes and Objects</a></li>
<li><a href="https://docs.python.org/3/tutorial/classes.html">Python Docs — Classes</a></li>
<li><a href="https://www.freecodecamp.org/news/object-oriented-programming-in-python/">freeCodeCamp — Object-Oriented Programming in Python</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.2 Attributes and Methods',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to add behaviours to a class using methods, use the <code>self</code> keyword correctly, and build a simple bank account or mobile money wallet model that can deposit, withdraw, and report its balance.</p>

<h2>Methods Are Functions Inside a Class</h2>
<p>In the previous lesson you learned that objects store data in attributes. Objects can also have <strong>methods</strong>, which are functions that belong to the object. Methods describe what the object can do. A <code>BankAccount</code> object might have methods to deposit money, withdraw money, and check the balance.</p>
<p>Methods are defined inside the class and always take <code>self</code> as the first parameter. The <code>self</code> parameter lets the method access the object's own attributes.</p>

<h2>Worked Example: BankAccount Class</h2>
<pre><code>class BankAccount:
    def __init__(self, owner, balance):
        self.owner = owner
        self.balance = balance

    def deposit(self, amount):
        self.balance = self.balance + amount
        print("Deposited K", amount)

    def withdraw(self, amount):
        if amount > self.balance:
            print("Insufficient funds.")
        else:
            self.balance = self.balance - amount
            print("Withdrew K", amount)

    def check_balance(self):
        print(self.owner, "has K", self.balance)

# Using the class
account = BankAccount("Grace Banda", 500)
account.deposit(200)
account.withdraw(100)
account.check_balance()
</code></pre>
<p>The <code>deposit</code>, <code>withdraw</code>, and <code>check_balance</code> methods all use <code>self.balance</code> to read or change the account's balance. This keeps the account's data and behaviour in one place.</p>

<h2>Mobile Money Wallet Example</h2>
<p>A mobile money wallet is similar to a bank account. It has an owner, a balance, and methods to send money and buy airtime. This is the kind of model that Airtel Money and MTN MoMo systems use behind the scenes.</p>
<pre><code>class MobileWallet:
    def __init__(self, owner, balance):
        self.owner = owner
        self.balance = balance

    def send_money(self, amount, recipient):
        if amount > self.balance:
            print("Not enough money.")
        else:
            self.balance -= amount
            print("Sent K", amount, "to", recipient)

    def buy_airtime(self, amount):
        if amount <= self.balance:
            self.balance -= amount
            print("Bought K", amount, "airtime.")
        else:
            print("Insufficient balance.")
</code></pre>
<p>When you use a real mobile money service, the code is far more complex, but the basic idea is the same: an object stores your balance, and methods change that balance safely.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>wallet.py</code>.</li>
<li>Write a <code>MobileWallet</code> class with an owner, balance, and methods to deposit, send money, and check balance.</li>
<li>Create a wallet object with an opening balance of K1,000.</li>
<li>Deposit K500, send K300 to a friend, and print the final balance.</li>
<li>Add a method to buy airtime that only works if the balance is enough.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Method</strong> — a function that belongs to a class and usually works with the object's data.</li>
<li><strong>Constructor</strong> — the special <code>__init__</code> method that runs when an object is created.</li>
<li><strong>self</strong> — a reference to the current object inside a method.</li>
<li><strong>Instance variable</strong> — an attribute that belongs to one particular object.</li>
<li><strong>Behaviour</strong> — what an object can do, usually represented by methods.</li>
</ul>

<h2>Summary</h2>
<p>Attributes store data, and methods define behaviour. Together they turn a simple data structure into a useful model of a real thing. The <code>self</code> keyword connects a method to the object it belongs to, allowing each object to keep its own state.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/python/python_classes.asp">W3Schools — Python Classes and Objects</a></li>
<li><a href="https://docs.python.org/3/tutorial/classes.html">Python Docs — Classes</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/python-object-oriented-programming/">Microsoft Learn — Object-Oriented Programming in Python</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.3 Inheritance and Reusing Code',
                'duration_minutes' => 70,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create a child class that inherits from a parent class, add new attributes and methods to the child class, override inherited methods, and explain how inheritance reduces repeated code.</p>

<h2>What Is Inheritance?</h2>
<p><strong>Inheritance</strong> is a way to create a new class based on an existing class. The new class, called the <strong>child class</strong>, automatically has all the attributes and methods of the existing class, called the <strong>parent class</strong>. The child class can then add its own features or change the ones it inherited.</p>
<p>Think of a transport company. All vehicles have a make, model, and year. Buses also have a passenger capacity. Lorries also have a load capacity in tonnes. Instead of writing the make, model, and year code three times, you create a <code>Vehicle</code> parent class and let <code>Bus</code> and <code>Lorry</code> inherit from it.</p>

<h2>Worked Example: Vehicle, Bus, and Lorry</h2>
<pre><code>class Vehicle:
    def __init__(self, make, model, year):
        self.make = make
        self.model = model
        self.year = year

    def describe(self):
        print(self.year, self.make, self.model)

class Bus(Vehicle):
    def __init__(self, make, model, year, capacity):
        super().__init__(make, model, year)
        self.capacity = capacity

    def describe(self):
        super().describe()
        print("Passenger capacity:", self.capacity)

class Lorry(Vehicle):
    def __init__(self, make, model, year, load_tonnes):
        super().__init__(make, model, year)
        self.load_tonnes = load_tonnes

# Create objects
bus = Bus("Toyota", "Coaster", 2020, 30)
lorry = Lorry("Mercedes", "Actros", 2019, 15)

bus.describe()
lorry.describe()
</code></pre>
<p>The <code>super()</code> function calls the parent class's methods. This lets the child class reuse the parent's <code>__init__</code> and <code>describe</code> methods while adding its own details.</p>

<h2>Overriding Methods</h2>
<p>When a child class provides its own version of a method that already exists in the parent class, it is called <strong>overriding</strong>. In the example above, <code>Bus.describe()</code> overrides <code>Vehicle.describe()</code> so that it can also print the passenger capacity. The <code>Lorry</code> class does not override <code>describe</code>, so it uses the parent version.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>inheritance.py</code>.</li>
<li>Define a <code>Person</code> class with attributes for name and NRC number.</li>
<li>Create an <code>Employee</code> class that inherits from <code>Person</code> and adds a staff ID and department.</li>
<li>Create a <code>Manager</code> class that inherits from <code>Employee</code> and adds a list of staff members managed.</li>
<li>Override the <code>describe</code> method in <code>Manager</code> to include the number of staff managed.</li>
<li>Create one object of each class and call their describe methods.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Inheritance</strong> — creating a new class from an existing class so the new class reuses its code.</li>
<li><strong>Parent class</strong> — the class that is inherited from.</li>
<li><strong>Child class</strong> — the class that inherits from another class.</li>
<li><strong>Override</strong> — to replace an inherited method with a new version in the child class.</li>
<li><strong>super()</strong> — a function that lets a child class call methods from its parent class.</li>
</ul>

<h2>Summary</h2>
<p>Inheritance lets you build new classes on top of existing ones, reducing repetition and keeping related code together. It is especially useful when you have several kinds of things that share common features but also have their own special behaviours.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/python/python_inheritance.asp">W3Schools — Python Inheritance</a></li>
<li><a href="https://docs.python.org/3/tutorial/classes.html#inheritance">Python Docs — Inheritance</a></li>
<li><a href="https://www.freecodecamp.org/news/object-oriented-programming-in-python/">freeCodeCamp — Object-Oriented Programming in Python</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.4 Building a Small Business Class in Python',
                'duration_minutes' => 80,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to combine classes, lists, and methods to model a small business, process a sale, update stock, and print a simple report. You will also understand how object-oriented design helps real shops and side businesses keep track of money and stock.</p>

<h2>Modelling a Small Business</h2>
<p>A small business has products, stock, sales, and money. Instead of keeping everything in one long script, you can model the business with classes. A <code>Product</code> class describes each item. A <code>SmallBusiness</code> class manages a list of products and records sales.</p>
<p>Imagine a shop at Kalomo market that sells mealie meal, cooking oil, soap, and sugar. The owner wants to know how many items are left and how much money has been made. An object-oriented program can answer these questions quickly.</p>

<h2>Worked Example: Product and SmallBusiness Classes</h2>
<pre><code>class Product:
    def __init__(self, name, price, quantity):
        self.name = name
        self.price = price
        self.quantity = quantity

    def stock_value(self):
        return self.price * self.quantity

class SmallBusiness:
    def __init__(self, name):
        self.name = name
        self.products = []
        self.total_sales = 0

    def add_product(self, product):
        self.products.append(product)

    def sell(self, product_name, amount):
        for product in self.products:
            if product.name == product_name:
                if product.quantity >= amount:
                    product.quantity -= amount
                    sale_amount = product.price * amount
                    self.total_sales += sale_amount
                    print("Sold", amount, product.name, "for K", sale_amount)
                    return
                else:
                    print("Not enough", product_name, "in stock.")
                    return
        print(product_name, "not found.")

    def report(self):
        print("---", self.name, "Report ---")
        for product in self.products:
            print(product.name, "-", product.quantity, "left - value K", product.stock_value())
        print("Total sales: K", self.total_sales)
</code></pre>
<p>The <code>Product</code> class handles the details of one item. The <code>SmallBusiness</code> class handles the collection of products and the sales total. Each class has a clear job, which makes the program easier to extend.</p>

<h2>Using the Model</h2>
<pre><code>shop = SmallBusiness("Kalomo General Store")
shop.add_product(Product("Mealie meal 25kg", 189.99, 20))
shop.add_product(Product("Cooking oil 2L", 85.00, 15))
shop.add_product(Product("Bar soap", 12.50, 50))

shop.sell("Cooking oil 2L", 2)
shop.sell("Bar soap", 5)
shop.report()
</code></pre>
<p>When you run this code, the report shows updated stock levels and the total sales so far. This is the same pattern used by point-of-sale systems in shops and supermarkets.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>small_business.py</code>.</li>
<li>Copy the Product and SmallBusiness classes from the example.</li>
<li>Add at least five products that a real shop near you sells.</li>
<li>Sell several items and print the report after each sale.</li>
<li>Add a <code>restock</code> method to the SmallBusiness class that increases the quantity of a product.</li>
<li>Test the restock method and verify the report shows the new quantity.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Encapsulation</strong> — keeping data and the methods that work with that data together in one class.</li>
<li><strong>State</strong> — the current values of an object's attributes.</li>
<li><strong>Business logic</strong> — the rules that describe how a business process works.</li>
<li><strong>Model</strong> — a simplified representation of something real in code.</li>
<li><strong>Stock value</strong> — the total worth of items currently in stock.</li>
</ul>

<h2>Summary</h2>
<p>Object-oriented programming helps you model real businesses by combining classes, lists, and methods. A well-designed program keeps product details and business operations separate, making it easy to add new features such as restocking, discounts, or daily reports.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/python/python_classes.asp">W3Schools — Python Classes and Objects</a></li>
<li><a href="https://docs.python.org/3/tutorial/classes.html">Python Docs — Classes</a></li>
<li><a href="https://www.freecodecamp.org/news/object-oriented-programming-in-python/">freeCodeCamp — Object-Oriented Programming in Python</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module5Quiz(): array
    {
        return [
            'title' => 'Module 5: Object-Oriented Programming Quiz',
            'description' => 'Test your understanding of classes, objects, methods, and inheritance.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is a class in object-oriented programming?',
                    'explanation' => 'A class is a blueprint for creating objects.',
                    'options' => [
                        ['text' => 'A finished program', 'is_correct' => false],
                        ['text' => 'A blueprint for objects', 'is_correct' => true],
                        ['text' => 'A type of variable', 'is_correct' => false],
                        ['text' => 'A syntax error', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is an object?',
                    'explanation' => 'An object is a single instance created from a class.',
                    'options' => [
                        ['text' => 'A Python keyword', 'is_correct' => false],
                        ['text' => 'An instance of a class', 'is_correct' => true],
                        ['text' => 'A type of loop', 'is_correct' => false],
                        ['text' => 'A module name', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which special method runs when a new object is created?',
                    'explanation' => '__init__ is the constructor method that runs when an object is created.',
                    'options' => [
                        ['text' => '__start__', 'is_correct' => false],
                        ['text' => '__init__', 'is_correct' => true],
                        ['text' => '__create__', 'is_correct' => false],
                        ['text' => '__new__', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does self refer to inside a method?',
                    'explanation' => 'self refers to the current object.',
                    'options' => [
                        ['text' => 'The current object', 'is_correct' => true],
                        ['text' => 'The parent class', 'is_correct' => false],
                        ['text' => 'A new file', 'is_correct' => false],
                        ['text' => 'The Python interpreter', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does inheritance allow a child class to do?',
                    'explanation' => 'Inheritance lets a child class reuse attributes and methods from a parent class.',
                    'options' => [
                        ['text' => 'Delete the parent class', 'is_correct' => false],
                        ['text' => 'Reuse parent attributes and methods', 'is_correct' => true],
                        ['text' => 'Ignore indentation rules', 'is_correct' => false],
                        ['text' => 'Run faster', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which syntax creates a Bus class that inherits from Vehicle?',
                    'explanation' => 'In Python, inheritance is written as class Child(Parent):.',
                    'options' => [
                        ['text' => 'class Bus(Vehicle):', 'is_correct' => true],
                        ['text' => 'class Bus extends Vehicle:', 'is_correct' => false],
                        ['text' => 'class Bus inherits Vehicle:', 'is_correct' => false],
                        ['text' => 'class Bus -> Vehicle:', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A class can have both attributes and methods.',
                    'explanation' => 'Classes typically store data in attributes and provide behaviour through methods.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Methods are called using parentheses, such as account.deposit(50).',
                    'explanation' => 'Methods are called by writing the object name, a dot, the method name, and parentheses.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the first parameter of most methods in a Python class? (one word)',
                    'explanation' => 'self is the conventional first parameter of instance methods.',
                    'correct_answer' => 'self',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is encapsulation?',
                    'explanation' => 'Encapsulation means bundling data and the methods that operate on that data together.',
                    'options' => [
                        ['text' => 'Writing all code in one line', 'is_correct' => false],
                        ['text' => 'Bundling data and methods together', 'is_correct' => true],
                        ['text' => 'Deleting unused variables', 'is_correct' => false],
                        ['text' => 'Copying code from the internet', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }


    // -------------------------------------------------------------------------
    // Module 6: File Handling and Exceptions
    // -------------------------------------------------------------------------

    private function module6Lessons(): array
    {
        return [
            [
                'title' => '6.1 Reading and Writing Text Files',
                'duration_minutes' => 70,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create, open, read, and write text files in Python, use the <code>with</code> statement to manage files safely, and explain why saving data to a file matters for businesses and personal records.</p>

<h2>Why Files Matter</h2>
<p>When you close a Python program, the variables disappear. If you run a calculator and then close it, the results are gone. For a real business, that is a problem. A shop owner in Kalomo needs last week's sales to still be available today. A student needs her assignment to be saved. A farmer needs rainfall records from last season. <strong>Files</strong> let programs keep data even when the computer is turned off.</p>

<h2>Opening a File</h2>
<p>Python has a built-in <code>open()</code> function. It needs a file name and a mode. The most common modes are:</p>
<ul>
<li><code>'r'</code> — read mode, used to read an existing file.</li>
<li><code>'w'</code> — write mode, used to create a new file or overwrite an existing one.</li>
<li><code>'a'</code> — append mode, used to add data to the end of an existing file.</li>
</ul>
<p>Here is how to write a simple message to a file:</p>
<pre><code>with open("welcome.txt", "w") as file:
    file.write("Welcome to Python programming.\n")
    file.write("Keep practising every day.\n")

print("File saved.")
</code></pre>
<p>The <code>with</code> statement automatically closes the file when the block ends, even if an error occurs. This is the safest way to work with files.</p>

<h2>Reading a File</h2>
<p>To read the file back, use read mode:</p>
<pre><code>with open("welcome.txt", "r") as file:
    content = file.read()
    print(content)
</code></pre>
<p>The <code>read()</code> method loads the whole file into one string. If the file is large, you can use <code>readline()</code> to read one line at a time or loop over the file object directly.</p>

<h2>Worked Example: Daily Sales Log</h2>
<p>A small shop wants to save each day's total sales. The program below appends a new line to <code>sales.txt</code> every time it runs:</p>
<pre><code>date = "2026-06-11"
amount = 850.50

with open("sales.txt", "a") as file:
    file.write(date + ": K" + str(amount) + "\n")

with open("sales.txt", "r") as file:
    print(file.read())
</code></pre>
<p>Because the program uses append mode, old sales are not lost when a new sale is added. Over time, the shop owner builds a useful record of daily income.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>file_practice.py</code>.</li>
<li>Write a program that creates a file named <code>family_numbers.txt</code> and saves three names with phone numbers, one per line.</li>
<li>Close the file and then open it in read mode to print the contents.</li>
<li>Change the program to append one more contact and print the updated file.</li>
<li>Check that the file exists in your PythonPractice folder using your file manager.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>File object</strong> — a value that represents an open file in Python.</li>
<li><strong>Read mode</strong> — opening a file with 'r' to read its contents.</li>
<li><strong>Write mode</strong> — opening a file with 'w' to create or overwrite it.</li>
<li><strong>with statement</strong> — a Python construct that opens a file and closes it automatically.</li>
<li><strong>Persistence</strong> — the ability of data to survive after a program ends.</li>
</ul>

<h2>Summary</h2>
<p>Files give programs memory that lasts beyond a single run. Using <code>open()</code>, <code>write()</code>, <code>read()</code>, and the <code>with</code> statement, you can save logs, records, and notes safely. This is the foundation of every database, document, and report system you will use.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/python/python_file_handling.asp">W3Schools — Python File Handling</a></li>
<li><a href="https://docs.python.org/3/tutorial/inputoutput.html#reading-and-writing-files">Python Docs — Reading and Writing Files</a></li>
<li><a href="https://www.freecodecamp.org/news/python-file-handling/">freeCodeCamp — Python File Handling</a></li>
</ul>
HTML,
            ],
            [
                'title' => '6.2 Handling Errors with try and except',
                'duration_minutes' => 70,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to catch and handle errors using <code>try</code>, <code>except</code>, <code>else</code>, and <code>finally</code>, explain common Python exceptions, and write programs that respond gracefully when something goes wrong.</p>

<h2>Errors Happen</h2>
<p>No program is perfect. A user may type letters where a number is expected. A file may be missing. A network connection may fail. Without error handling, these problems crash the program and frustrate the user. With error handling, the program can explain the problem and continue running.</p>
<p>In Python, an error is called an <strong>exception</strong>. Common exceptions include <code>ValueError</code> when a conversion fails, <code>FileNotFoundError</code> when a file does not exist, and <code>TypeError</code> when values are used incorrectly.</p>

<h2>The try and except Blocks</h2>
<p>The <code>try</code> block contains code that might cause an error. The <code>except</code> block contains code that runs if an error occurs. Here is an example:</p>
<pre><code>try:
    age = int(input("Enter your age: "))
    print("Next year you will be", age + 1)
except ValueError:
    print("Please enter a whole number.")
</code></pre>
<p>If the user types "twenty", <code>int()</code> raises a <code>ValueError</code>. The program does not crash. Instead, it prints a friendly message asking for a number.</p>

<h2>Catching File Errors</h2>
<p>File operations often need error handling because the file may not exist. The program below tries to open a sales log and creates a new one if it is missing:</p>
<pre><code>try:
    with open("sales.txt", "r") as file:
        print(file.read())
except FileNotFoundError:
    print("No sales file found. Creating a new one.")
    with open("sales.txt", "w") as file:
        file.write("Sales log started.\n")
</code></pre>
<p>This pattern is useful for load-shedding situations where a file may not have been saved correctly. The program recovers instead of stopping.</p>

<h2>else and finally</h2>
<p>The <code>else</code> block runs only if no exception occurred. The <code>finally</code> block runs no matter what. Use <code>finally</code> for cleanup, such as closing a resource. Here is an example that validates an NRC-style number:</p>
<pre><code>try:
    nrc = input("Enter your NRC number: ")
    if len(nrc) &lt; 5:
        raise ValueError("NRC number is too short.")
except ValueError as error:
    print("Invalid input:", error)
else:
    print("NRC recorded:", nrc)
finally:
    print("Validation complete.")
</code></pre>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>error_handling.py</code>.</li>
<li>Ask the user for a number and use try/except to catch <code>ValueError</code>.</li>
<li>Try to open a file named <code>missing.txt</code> and catch <code>FileNotFoundError</code>.</li>
<li>Write a small program that divides two numbers and catches <code>ZeroDivisionError</code>.</li>
<li>Add a <code>finally</code> block that prints "Calculation finished." every time.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Exception</strong> — an error that occurs while a program is running.</li>
<li><strong>try</strong> — a block of code that may raise an exception.</li>
<li><strong>except</strong> — a block that runs if a specific exception occurs.</li>
<li><strong>finally</strong> — a block that runs whether or not an exception occurs.</li>
<li><strong>Error handling</strong> — writing code that responds to errors without crashing.</li>
</ul>

<h2>Summary</h2>
<p>Errors are normal, but crashes are not. By using <code>try</code>, <code>except</code>, <code>else</code>, and <code>finally</code>, you can write programs that handle bad input, missing files, and unexpected values gracefully. This makes your software more reliable and professional.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/python/python_try_except.asp">W3Schools — Python Try Except</a></li>
<li><a href="https://docs.python.org/3/tutorial/errors.html">Python Docs — Errors and Exceptions</a></li>
<li><a href="https://www.freecodecamp.org/news/python-try-except-handling-exceptions/">freeCodeCamp — Python Try Except</a></li>
</ul>
HTML,
            ],
            [
                'title' => '6.3 Working with CSV Files for Business Records',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to read and write CSV files using Python's <code>csv</code> module, explain why CSV is a common format for business data, and build a simple stock or sales tracker that stores records in rows and columns.</p>

<h2>What Is CSV?</h2>
<p>CSV stands for Comma-Separated Values. A CSV file stores data in a table where each row is on a new line and each value inside the row is separated by a comma. It is a simple format that can be opened in Excel, LibreOffice Calc, Google Sheets, and many other programs. Because it is plain text, it is also easy for Python to read and write.</p>
<p>Many organisations in Zambia use CSV files to exchange data. A school might export a list of students as CSV. A shop might export daily sales as CSV. A government department might publish statistics as CSV. Learning to work with CSV files is a practical skill for office work and business analysis.</p>

<h2>Writing a CSV File</h2>
<p>Python's built-in <code>csv</code> module makes CSV work straightforward. Here is how to write a stock file:</p>
<pre><code>import csv

with open("stock.csv", "w", newline="") as file:
    writer = csv.writer(file)
    writer.writerow(["Item", "Quantity", "Price"])
    writer.writerow(["Mealie meal 25kg", 20, 189.99])
    writer.writerow(["Cooking oil 2L", 15, 85.00])
    writer.writerow(["Bar soap", 50, 12.50])

print("Stock saved.")
</code></pre>
<p>The <code>newline=""</code> option prevents extra blank lines on some systems. The <code>writerow()</code> method writes one row at a time.</p>

<h2>Reading a CSV File</h2>
<pre><code>import csv

with open("stock.csv", "r") as file:
    reader = csv.reader(file)
    for row in reader:
        print(row)
</code></pre>
<p>Each row is returned as a list of strings. If you want to treat numbers as numbers, you must convert them with <code>int()</code> or <code>float()</code>.</p>

<h2>Using a Dictionary Reader</h2>
<p>The <code>csv.DictReader</code> class reads rows as dictionaries, using the first row as column names. This makes the code easier to understand:</p>
<pre><code>import csv

with open("stock.csv", "r") as file:
    reader = csv.DictReader(file)
    for row in reader:
        name = row["Item"]
        quantity = int(row["Quantity"])
        price = float(row["Price"])
        value = quantity * price
        print(name, "stock value: K", value)
</code></pre>
<p>This version refers to columns by name instead of position, which reduces mistakes when the file structure changes.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>csv_practice.py</code>.</li>
<li>Write a program that creates <code>stock.csv</code> with headers Item, Quantity, and Price, and at least five rows of shop products.</li>
<li>Write a second program that reads <code>stock.csv</code> and prints the total stock value.</li>
<li>Add a new product to the CSV file using append mode.</li>
<li>Open the CSV file in LibreOffice Calc or Excel and verify the columns are correct.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>CSV</strong> — Comma-Separated Values, a plain-text format for tabular data.</li>
<li><strong>Delimiter</strong> — the character that separates values in a CSV file, usually a comma.</li>
<li><strong>Row</strong> — one horizontal line of data in a table.</li>
<li><strong>Header</strong> — the first row of a CSV file that names each column.</li>
<li><strong>csv module</strong> — Python's built-in module for reading and writing CSV files.</li>
</ul>

<h2>Summary</h2>
<p>CSV files are a common way to store and share business records. Python's <code>csv</code> module lets you create spreadsheets, read stock lists, and calculate totals without needing Excel. This skill is valuable for finance, administration, and small business management.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://docs.python.org/3/library/csv.html">Python Docs — CSV File Reading and Writing</a></li>
<li><a href="https://www.w3schools.com/python/python_file_handling.asp">W3Schools — Python File Handling</a></li>
<li><a href="https://www.freecodecamp.org/news/python-csv-module-handling-csv-files/">freeCodeCamp — Python CSV Module</a></li>
</ul>
HTML,
            ],
            [
                'title' => '6.4 Planning a Backup System for Load-Shedding',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain why backups are important, write a Python script that copies important files to a safe location, use timestamps in file names, and plan a simple backup routine that protects work from power cuts and hardware failure.</p>

<h2>Why Backups Matter</h2>
<p>Load-shedding, sudden power cuts, and failing storage devices can destroy hours of work in an instant. A student may lose an assignment. A shop owner may lose sales records. A college may lose marks. A <strong>backup</strong> is a copy of important files stored in a second location so that if the original is lost, the copy remains.</p>
<p>Backups do not need expensive equipment. A USB flash drive, a memory card, an external hard drive, or a cloud folder can all serve as a backup location. The important thing is to copy files regularly and to keep the backup in a different physical place from the original.</p>

<h2>Using Python to Copy Files</h2>
<p>Python's <code>shutil</code> module can copy files and folders. The <code>datetime</code> module can add a date to the backup name. Here is a simple script that copies a file called <code>sales.txt</code> to a backup folder with today's date:</p>
<pre><code>import shutil
import datetime
import os

source = "sales.txt"
backup_folder = "backups"

# Create the backup folder if it does not exist
if not os.path.exists(backup_folder):
    os.makedirs(backup_folder)

today = datetime.date.today()
backup_name = "sales_" + str(today) + ".txt"
destination = os.path.join(backup_folder, backup_name)

shutil.copy(source, destination)
print("Backup saved to", destination)
</code></pre>
<p>This script creates a folder called <code>backups</code>, builds a file name that includes the date, and copies the source file. Running the script every day creates a history of backups.</p>

<h2>Worked Example: Backing Up a Folder</h2>
<p>If you have a whole folder of college work, you can back it up with <code>shutil.copytree()</code>:</p>
<pre><code>import shutil
import datetime

source_folder = "PythonPractice"
today = datetime.date.today()
backup_folder = "PythonPractice_backup_" + str(today)

shutil.copytree(source_folder, backup_folder)
print("Folder backed up as", backup_folder)
</code></pre>
<p>This command copies the entire folder and everything inside it. If the backup folder already exists, Python will raise an error, so in a real script you would add error handling or remove the old folder first.</p>

<h2>Planning a Backup Routine</h2>
<p>A good backup plan answers three questions: what to back up, where to store it, and how often to do it. For a student, the answers might be:</p>
<ul>
<li><strong>What:</strong> Python practice files, assignments, and notes.</li>
<li><strong>Where:</strong> a USB flash drive or a Google Drive folder.</li>
<li><strong>How often:</strong> at the end of every study session.</li>
</ul>
<p>For a small business, the plan might include daily backups of sales.csv and weekly backups of the entire accounts folder.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a file named <code>backup.py</code>.</li>
<li>Write a script that copies one of your Python files to a <code>backups</code> folder with today's date in the file name.</li>
<li>Run the script twice and check that two backup files are created.</li>
<li>Modify the script to back up a whole folder instead of a single file.</li>
<li>Add a try/except block that prints a friendly message if the source file is missing.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Backup</strong> — a copy of files kept in a separate location for safety.</li>
<li><strong>Redundancy</strong> — having extra copies so that data is not lost if one copy fails.</li>
<li><strong>Timestamp</strong> — a date and time added to a file name or record.</li>
<li><strong>Automation</strong> — making a task happen automatically, often on a schedule.</li>
<li><strong>Data loss</strong> — the accidental destruction or deletion of important information.</li>
</ul>

<h2>Summary</h2>
<p>Backups protect your work from power cuts, hardware failure, and human error. Python's <code>shutil</code> and <code>datetime</code> modules make it easy to copy files and folders with date-stamped names. A simple backup routine can save a student or business from losing valuable records.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://docs.python.org/3/library/shutil.html">Python Docs — shutil Module</a></li>
<li><a href="https://www.w3schools.com/python/python_file_handling.asp">W3Schools — Python File Handling</a></li>
<li><a href="https://support.google.com/drive/answer/2424384">Google Drive Help — Back Up Files</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module6Quiz(): array
    {
        return [
            'title' => 'Module 6: File Handling and Exceptions Quiz',
            'description' => 'Test your understanding of files, exceptions, CSV files, and backups.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which mode opens a file for writing in Python?',
                    'explanation' => "The 'w' mode opens a file for writing, creating it if it does not exist.",
                    'options' => [
                        ['text' => "'r'", 'is_correct' => false],
                        ['text' => "'w'", 'is_correct' => true],
                        ['text' => "'a'", 'is_correct' => false],
                        ['text' => "'x'", 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which statement safely closes a file automatically?',
                    'explanation' => 'The with statement ensures the file is closed even if an error occurs.',
                    'options' => [
                        ['text' => 'if', 'is_correct' => false],
                        ['text' => 'while', 'is_correct' => false],
                        ['text' => 'with', 'is_correct' => true],
                        ['text' => 'for', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which exception is raised when a file does not exist?',
                    'explanation' => 'FileNotFoundError is raised when Python cannot find the file.',
                    'options' => [
                        ['text' => 'ValueError', 'is_correct' => false],
                        ['text' => 'TypeError', 'is_correct' => false],
                        ['text' => 'FileNotFoundError', 'is_correct' => true],
                        ['text' => 'KeyError', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which Python module is used to read and write CSV files?',
                    'explanation' => 'The csv module provides tools for working with comma-separated value files.',
                    'options' => [
                        ['text' => 'json', 'is_correct' => false],
                        ['text' => 'csv', 'is_correct' => true],
                        ['text' => 'os', 'is_correct' => false],
                        ['text' => 'sys', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => "What does the 'a' mode do when opening a file?",
                    'explanation' => "The 'a' mode opens a file for appending, adding new data to the end.",
                    'options' => [
                        ['text' => 'Reads the file', 'is_correct' => false],
                        ['text' => 'Appends to the file', 'is_correct' => true],
                        ['text' => 'Deletes the file', 'is_correct' => false],
                        ['text' => 'Renames the file', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which function reads the entire contents of a file as one string?',
                    'explanation' => 'read() loads the whole file into a single string.',
                    'options' => [
                        ['text' => 'readline()', 'is_correct' => false],
                        ['text' => 'read()', 'is_correct' => true],
                        ['text' => 'write()', 'is_correct' => false],
                        ['text' => 'close()', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'The finally block always runs whether or not an exception occurs.',
                    'explanation' => 'finally is executed after try and except, regardless of whether an exception happened.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'You can only open one file per Python program.',
                    'explanation' => 'A Python program can open many files at the same time.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What Python keyword starts a block that may raise an exception? (one word)',
                    'explanation' => 'The try block contains code that may raise an exception.',
                    'correct_answer' => 'try',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is it important to back up files?',
                    'explanation' => 'Backups protect against data loss from power cuts, hardware failure, or accidental deletion.',
                    'options' => [
                        ['text' => 'To use more storage space', 'is_correct' => false],
                        ['text' => 'To protect against data loss', 'is_correct' => true],
                        ['text' => 'To slow down the computer', 'is_correct' => false],
                        ['text' => 'To increase internet usage', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }
}
