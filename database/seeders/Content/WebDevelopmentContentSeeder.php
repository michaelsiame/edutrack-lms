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

class WebDevelopmentContentSeeder extends Seeder
{
    private int $courseId;

    private array $existingModuleTitles = [];

    private array $existingLessonTitles = [];

    private array $existingQuizTitles = [];

    private array $existingAssignmentTitles = [];

    private array $counts = [
        'modules_created' => 0,
        'modules_reused' => 0,
        'lessons_created' => 0,
        'lessons_skipped' => 0,
        'quizzes_created' => 0,
        'quizzes_skipped' => 0,
        'questions_created' => 0,
        'assignments_created' => 0,
        'assignments_skipped' => 0,
    ];

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Web Development')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Web Development" not found. Aborting.');

            return;
        }

        $this->courseId = $course->id;

        // Idempotent guards: skip any item whose title already exists in this course.
        $this->existingModuleTitles = Module::where('course_id', $this->courseId)->pluck('title')->toArray();
        $this->existingLessonTitles = Lesson::whereIn('module_id', function ($query) {
            $query->select('id')->from('modules')->where('course_id', $this->courseId);
        })->pluck('title')->toArray();
        $this->existingQuizTitles = Quiz::where('course_id', $this->courseId)->pluck('title')->toArray();
        $this->existingAssignmentTitles = Assignment::where('course_id', $this->courseId)->pluck('title')->toArray();

        DB::transaction(function () {
            $this->seedModule3JavaScriptBasics();
            $this->seedModule4ModernJavaScript();
            $this->seedModule5ResponsiveDesign();
            $this->seedModule6LaunchingSite();
            $this->seedMissingQuizzes();
            $this->seedAssignments();
        });

        $this->printSummary();
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    private function findOrCreateModule(string $title, string $description, int $displayOrder): Module
    {
        if (in_array($title, $this->existingModuleTitles, true)) {
            $module = Module::where('course_id', $this->courseId)->where('title', $title)->first();
            $this->counts['modules_reused']++;
            $this->command->info("Reusing existing module: {$title}");

            return $module;
        }

        $module = Module::create([
            'course_id' => $this->courseId,
            'title' => $title,
            'description' => $description,
            'display_order' => $displayOrder,
            'duration_minutes' => 0,
            'is_published' => 1,
        ]);

        $this->counts['modules_created']++;
        $this->existingModuleTitles[] = $title;

        return $module;
    }

    private function createLessons(Module $module, array $lessonsData): array
    {
        $lessonIds = [];

        foreach ($lessonsData as $index => $lessonData) {
            $title = $lessonData['title'];

            if (in_array($title, $this->existingLessonTitles, true)) {
                $this->counts['lessons_skipped']++;
                $this->command->warn("Skipping existing lesson: {$title}");

                $existingLesson = Lesson::where('module_id', $module->id)->where('title', $title)->first();
                if ($existingLesson) {
                    $lessonIds[] = $existingLesson->id;
                }

                continue;
            }

            $displayOrder = $index + 1;
            $duration = $lessonData['duration_minutes'];

            $lesson = Lesson::create([
                'module_id' => $module->id,
                'title' => $title,
                'content' => $lessonData['content'],
                'lesson_type' => 'Reading',
                'duration_minutes' => $duration,
                'display_order' => $displayOrder,
                'is_preview' => 0,
                'is_mandatory' => 1,
                'points' => 10,
            ]);

            $lessonIds[] = $lesson->id;
            $this->existingLessonTitles[] = $title;
            $this->counts['lessons_created']++;
        }

        // Recalculate module duration from all lessons to keep schema consistent.
        $module->duration_minutes = Lesson::where('module_id', $module->id)->sum('duration_minutes');
        $module->save();

        return $lessonIds;
    }

    private function createQuiz(Module $module, array $quizData, array $lessonIds): void
    {
        $title = $quizData['title'];

        if (in_array($title, $this->existingQuizTitles, true)) {
            $this->counts['quizzes_skipped']++;
            $this->command->warn("Skipping existing quiz: {$title}");

            return;
        }

        $lastLessonId = ! empty($lessonIds) ? end($lessonIds) : null;

        $quiz = Quiz::create([
            'course_id' => $this->courseId,
            'lesson_id' => $lastLessonId,
            'title' => $title,
            'description' => $quizData['description'],
            'quiz_type' => 'Graded',
            'time_limit_minutes' => $quizData['time_limit_minutes'] ?? 20,
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
            $this->counts['questions_created']++;
        }

        $this->counts['quizzes_created']++;
    }

    private function createAssignment(array $data): void
    {
        $title = $data['title'];

        if (in_array($title, $this->existingAssignmentTitles, true)) {
            $this->counts['assignments_skipped']++;
            $this->command->warn("Skipping existing assignment: {$title}");

            return;
        }

        Assignment::create([
            'course_id' => $this->courseId,
            'lesson_id' => $data['lesson_id'] ?? null,
            'title' => $title,
            'description' => $data['description'],
            'instructions' => $data['instructions'],
            'max_points' => $data['max_points'] ?? 100,
            'passing_points' => $data['passing_points'] ?? 50,
            'allow_late_submission' => $data['allow_late_submission'] ?? 1,
            'max_file_size_mb' => $data['max_file_size_mb'] ?? 10,
            'allowed_file_types' => $data['allowed_file_types'] ?? 'pdf,doc,docx,jpg,png,zip',
        ]);

        $this->counts['assignments_created']++;
    }

    // -----------------------------------------------------------------
    // Module 3: JavaScript Basics
    // -----------------------------------------------------------------

    private function seedModule3JavaScriptBasics(): void
    {
        $module = $this->findOrCreateModule(
            'JavaScript Basics',
            'Add interactivity to your web pages with JavaScript: variables, functions, events, and DOM manipulation.',
            3
        );

        $lessonIds = $this->createLessons($module, $this->module3Lessons());

        if (! empty($lessonIds)) {
            $this->createQuiz($module, $this->module3Quiz(), $lessonIds);
        }
    }

    private function module3Lessons(): array
    {
        return [
            [
                'title' => '3.1 What Is JavaScript and Why Does It Matter?',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what JavaScript does in a web page, describe the difference between HTML, CSS, and JavaScript, and identify real Zambian websites and services that rely on JavaScript to work.</p>

<h2>HTML, CSS, and JavaScript Work Together</h2>
<p>Think of a web page like a small shop in Kalomo. <strong>HTML</strong> is the structure: the walls, the shelves, and the counter. <strong>CSS</strong> is the decoration: the paint, the signage, and the layout that makes the shop attractive. <strong>JavaScript</strong> is the person working in the shop: the one who responds when a customer asks a question, calculates change, opens the till, and updates the stock list.</p>
<p>HTML tells the browser what is on the page. CSS tells the browser how it should look. JavaScript tells the browser how to behave when the user does something. Without JavaScript, a web page is mostly static. With JavaScript, it becomes interactive.</p>

<h2>What Can JavaScript Do?</h2>
<p>JavaScript can read input from a user, make decisions, perform calculations, update the page without reloading it, and communicate with servers. Here are everyday examples that matter in Zambia:</p>
<ul>
<li>When you check your Airtel Money or MTN MoMo balance in a browser, JavaScript displays the result instantly.</li>
<li>When you type your meter number on the ZESCO token website, JavaScript checks that the number looks correct before you pay.</li>
<li>When you add items to an online shop cart, JavaScript updates the total price and item count.</li>
<li>When you open a mobile menu on a small screen, JavaScript shows and hides the navigation links.</li>
</ul>

<h2>Where JavaScript Runs</h2>
<p>JavaScript was originally created to run inside web browsers such as Chrome, Firefox, and Edge. Every modern browser contains a JavaScript engine. This means your code runs on the visitor's computer or phone. You do not need expensive server software to start learning. You can write JavaScript on a college computer, save it in an HTML file, and open that file in a browser to see it work.</p>

<h2>Worked Example: A Simple Alert</h2>
<p>JavaScript code is usually placed inside a <code>&lt;script&gt;</code> element in an HTML file. Here is the smallest useful example:</p>
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;
    &lt;title&gt;My First JavaScript&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;h1&gt;Welcome to Kalomo&lt;/h1&gt;
    &lt;script&gt;
        alert("Hello from Kalomo!");
    &lt;/script&gt;
&lt;/body&gt;
&lt;/html&gt;
</code></pre>
<p>When you open this file in a browser, a small pop-up appears with the message "Hello from Kalomo!" The <code>alert()</code> function is built into the browser. It is a simple way to test that JavaScript is running.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open the HTML file you created in Module 1 or create a new file called <code>first-javascript.html</code>.</li>
<li>Add a <code>&lt;script&gt;</code> element just before the closing <code>&lt;/body&gt;</code> tag.</li>
<li>Inside the script, write <code>alert("I am learning JavaScript!");</code></li>
<li>Save the file and open it in Google Chrome. You should see a pop-up message.</li>
<li>Change the message to greet your own town or area, then refresh the page.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>JavaScript</strong> — a programming language that adds behaviour and interactivity to web pages.</li>
<li><strong>Script</strong> — a small program written in JavaScript that runs inside a web browser.</li>
<li><strong>Function</strong> — a named block of code that performs a specific task, such as <code>alert()</code>.</li>
<li><strong>Browser engine</strong> — the part of a web browser that reads and runs JavaScript code.</li>
<li><strong>Interactive</strong> — a web page that responds to user actions such as clicks, typing, and scrolling.</li>
</ul>

<h2>Summary</h2>
<p>JavaScript brings web pages to life. It works alongside HTML and CSS to create interactive experiences such as mobile menus, form checks, shopping carts, and live balance updates. Because JavaScript runs in the browser, you can start learning it with nothing more than a text editor and a computer at college or home.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/JavaScript/First_steps/What_is_JavaScript">MDN Web Docs — What is JavaScript?</a></li>
<li><a href="https://www.w3schools.com/js/js_intro.asp">W3Schools — JavaScript Introduction</a></li>
<li><a href="https://www.freecodecamp.org/learn/javascript-algorithms-and-data-structures/">freeCodeCamp — JavaScript Algorithms and Data Structures</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.2 Variables, Data Types, and Simple Output',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to declare variables in JavaScript, store text and numbers, display values in the browser console, and write a small script that calculates a price in Kwacha.</p>

<h2>What Is a Variable?</h2>
<p>A <strong>variable</strong> is a labelled container that holds a value. In JavaScript you can create a variable with <code>let</code> or <code>const</code>. Use <code>let</code> when the value may change later, and <code>const</code> when it should stay the same. For example, the price of mealie meal might change, so you use <code>let</code>. The name of your shop probably stays the same, so you use <code>const</code>.</p>

<h2>Declaring and Using Variables</h2>
<p>Here is a simple example that stores a product name and price:</p>
<pre><code>const shopName = "Kalomo Fresh Foods";
let priceOfMealieMeal = 145;
let quantity = 2;
let total = priceOfMealieMeal * quantity;

console.log(shopName);
console.log("Total: K" + total);
</code></pre>
<p>The <code>console.log()</code> function prints messages to the browser's developer console. This is the easiest way to check what your code is doing. To open the console in Chrome, press <strong>F12</strong> and click the <strong>Console</strong> tab.</p>

<h2>Common Data Types</h2>
<p>JavaScript works with several common types of data:</p>
<ul>
<li><strong>String</strong> — text written inside quotes, such as <code>"Kalomo"</code> or <code>'ZMW'</code>.</li>
<li><strong>Number</strong> — whole numbers or decimals, such as <code>145</code> or <code>18.50</code>.</li>
<li><strong>Boolean</strong> — a true or false value, such as <code>true</code> or <code>false</code>.</li>
</ul>
<p>Unlike some other languages, JavaScript does not force you to declare the type. The browser figures it out from the value you store.</p>

<h2>Worked Example: Calculating a Transport Fare</h2>
<p>Suppose you run a small transport business between Kalomo and Livingstone. You charge K180 per seat and a customer books three seats. Your script can calculate the total:</p>
<pre><code>const route = "Kalomo to Livingstone";
const pricePerSeat = 180;
let seatsBooked = 3;
let totalFare = pricePerSeat * seatsBooked;

console.log("Route: " + route);
console.log("Seats booked: " + seatsBooked);
console.log("Total fare: K" + totalFare);
</code></pre>
<p>When you run this in the console, it prints the route, the number of seats, and the total fare of K540. If another customer books a different number of seats, you only change <code>seatsBooked</code> and run the script again.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open your <code>first-javascript.html</code> file or create a new HTML file.</li>
<li>Inside a <code>&lt;script&gt;</code> tag, declare three variables: a product name, a unit price in Kwacha, and a quantity sold.</li>
<li>Calculate the total cost and print it with <code>console.log()</code>.</li>
<li>Open the file in Chrome, press F12, and click the Console tab to see your output.</li>
<li>Change the quantity and refresh the page to see the new total.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Variable</strong> — a named container that stores a value in a program.</li>
<li><strong>let</strong> — a keyword used to declare a variable whose value can change.</li>
<li><strong>const</strong> — a keyword used to declare a variable whose value must stay the same.</li>
<li><strong>console.log()</strong> — a function that prints messages to the browser's developer console.</li>
<li><strong>String</strong> — a sequence of characters, or text, used in a program.</li>
</ul>

<h2>Summary</h2>
<p>Variables let your JavaScript programs remember information. Use <code>const</code> for values that do not change and <code>let</code> for values that do. Strings store text, numbers store amounts, and <code>console.log()</code> helps you see what is happening while you learn. These simple ideas are the foundation for every interactive feature you will build.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/JavaScript/First_steps/Variables">MDN Web Docs — JavaScript Variables</a></li>
<li><a href="https://www.w3schools.com/js/js_variables.asp">W3Schools — JavaScript Variables</a></li>
<li><a href="https://www.freecodecamp.org/learn/javascript-algorithms-and-data-structures/">freeCodeCamp — JavaScript Practice</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.3 Functions and Event Listeners',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to write your own JavaScript functions, connect a function to a button click using an event listener, and build a simple page that responds when a user interacts with it.</p>

<h2>Why Functions Matter</h2>
<p>A <strong>function</strong> is a reusable block of code that performs a specific task. Functions help you avoid repeating the same code over and over. For example, if you run a small shop and need to calculate the total price many times, you can write one function and call it whenever a customer buys something.</p>

<h2>Writing a Function</h2>
<p>Here is a simple function that calculates a total price including a small delivery fee:</p>
<pre><code>function calculateTotal(price, deliveryFee) {
    return price + deliveryFee;
}

let total = calculateTotal(85, 15);
console.log("Total to pay: K" + total);
</code></pre>
<p>The function is named <code>calculateTotal</code>. It accepts two pieces of information called <strong>parameters</strong>: <code>price</code> and <code>deliveryFee</code>. It returns the sum. You can call the function with different numbers each time.</p>

<h2>Event Listeners</h2>
<p>An <strong>event listener</strong> is how JavaScript waits for something to happen. The most common event is a <strong>click</strong>. You can tell the browser: "When the user clicks this button, run this function." This is how menus open, forms submit, and counters increase.</p>

<h2>Worked Example: A Simple Click Counter</h2>
<p>Imagine you are counting how many customers visit your stall today. Each time someone arrives, you press a button and the counter goes up.</p>
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;
    &lt;title&gt;Customer Counter&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;h1&gt;Customers Today&lt;/h1&gt;
    &lt;p id="counter"&gt;0&lt;/p&gt;
    &lt;button id="addButton"&gt;Add Customer&lt;/button&gt;

    &lt;script&gt;
        let count = 0;

        function addCustomer() {
            count = count + 1;
            document.getElementById("counter").innerText = count;
        }

        document.getElementById("addButton").addEventListener("click", addCustomer);
    &lt;/script&gt;
&lt;/body&gt;
&lt;/html&gt;
</code></pre>
<p>When the button is clicked, the <code>addCustomer</code> function runs. It increases <code>count</code> by one and updates the text inside the paragraph with id <code>counter</code>. The page updates without reloading.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a new HTML file called <code>customer-counter.html</code>.</li>
<li>Copy the example above and save it.</li>
<li>Open the file in Chrome and click the button several times. Watch the number increase.</li>
<li>Change the button text to "Next Customer" and add a second button that resets the counter to zero.</li>
<li>Write a function that shows an alert with the current count when a "Show Total" button is clicked.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Function</strong> — a reusable block of code that performs a specific task.</li>
<li><strong>Parameter</strong> — information passed into a function so it can do its work.</li>
<li><strong>Return</strong> — the value a function sends back after it finishes.</li>
<li><strong>Event listener</strong> — code that waits for an action such as a click and then runs a function.</li>
<li><strong>DOM</strong> — the Document Object Model; the browser's internal representation of a web page.</li>
</ul>

<h2>Summary</h2>
<p>Functions let you organise your JavaScript into reusable pieces. Event listeners connect those functions to user actions such as button clicks. Together they turn a static page into an interactive tool. The customer counter example shows the same pattern used by shopping carts, like buttons, and form submissions across the web.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/JavaScript/Building_blocks/Functions">MDN Web Docs — JavaScript Functions</a></li>
<li><a href="https://www.w3schools.com/js/js_functions.asp">W3Schools — JavaScript Functions</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/JavaScript/Building_blocks/Events">MDN Web Docs — JavaScript Events</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.4 Manipulating the DOM',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to select HTML elements with JavaScript, change their text and styles, add and remove classes, and build a simple page that updates itself based on user choices.</p>

<h2>The DOM Is a Tree</h2>
<p>When a browser loads an HTML page, it creates an internal model called the <strong>Document Object Model</strong>, or <strong>DOM</strong>. The DOM is like a family tree. The <code>&lt;html&gt;</code> element is the root. It has children such as <code>&lt;head&gt;</code> and <code>&lt;body&gt;</code>. Those children have their own children, all the way down to individual paragraphs, images, and buttons. JavaScript can climb this tree, find any element, and change it.</p>

<h2>Selecting Elements</h2>
<p>JavaScript provides several ways to find an element:</p>
<ul>
<li><code>document.getElementById("id")</code> — finds one element by its id attribute.</li>
<li><code>document.querySelector(".class")</code> — finds the first element that matches a CSS selector.</li>
<li><code>document.querySelectorAll(".class")</code> — finds all matching elements.</li>
</ul>
<p>Once you have selected an element, you can read or change almost anything about it.</p>

<h2>Changing Text and Style</h2>
<p>Every element has an <code>innerText</code> property for its visible text and a <code>style</code> property for its inline styles. You can also add or remove CSS classes using <code>classList</code>.</p>
<pre><code>let heading = document.getElementById("main-heading");
heading.innerText = "Welcome to Soweto Market";
heading.style.color = "green";
heading.classList.add("highlight");
</code></pre>

<h2>Worked Example: Show/Hide Prices</h2>
<p>Suppose you sell products and want to show the price only when a customer clicks a button. This keeps the page clean and lets curious visitors see details when they want.</p>
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;
    &lt;title&gt;Price Toggle&lt;/title&gt;
    &lt;style&gt;
        .hidden { display: none; }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;h1&gt;Kalomo Fresh Produce&lt;/h1&gt;
    &lt;p&gt;Tomatoes — &lt;span id="tomato-price" class="hidden"&gt;K15 per heap&lt;/span&gt;&lt;/p&gt;
    &lt;button id="toggle-btn"&gt;Show Prices&lt;/button&gt;

    &lt;script&gt;
        let button = document.getElementById("toggle-btn");
        let price = document.getElementById("tomato-price");

        button.addEventListener("click", function() {
            price.classList.toggle("hidden");
            if (price.classList.contains("hidden")) {
                button.innerText = "Show Prices";
            } else {
                button.innerText = "Hide Prices";
            }
        });
    &lt;/script&gt;
&lt;/body&gt;
&lt;/html&gt;
</code></pre>
<p>This example uses <code>classList.toggle("hidden")</code> to add the class if it is missing and remove it if it is present. The button text also changes to match the new state.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a new HTML file with a heading, a paragraph, and a button.</li>
<li>Use <code>document.getElementById</code> to select the heading and the button.</li>
<li>Write a function that changes the heading text when the button is clicked.</li>
<li>Add a second button that changes the paragraph's text colour to blue.</li>
<li>Add a CSS class in a <code>&lt;style&gt;</code> block and use JavaScript to add or remove that class when a button is clicked.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>DOM</strong> — the browser's tree-shaped representation of an HTML page.</li>
<li><strong>getElementById</strong> — a JavaScript method that finds one element by its id.</li>
<li><strong>innerText</strong> — a property that holds the visible text inside an element.</li>
<li><strong>classList</strong> — a property used to add, remove, or toggle CSS classes on an element.</li>
<li><strong>querySelector</strong> — a flexible method that finds the first element matching a CSS selector.</li>
</ul>

<h2>Summary</h2>
<p>DOM manipulation is the heart of front-end interactivity. You select an element, then read or change its content, style, or classes. With just a few methods such as <code>getElementById</code>, <code>innerText</code>, and <code>classList</code>, you can build toggles, counters, menus, and live updates that make your websites feel modern and responsive to users.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/JavaScript/Client-side_web_APIs/Manipulating_documents">MDN Web Docs — Manipulating Documents</a></li>
<li><a href="https://www.w3schools.com/js/js_htmldom.asp">W3Schools — JavaScript HTML DOM</a></li>
<li><a href="https://www.freecodecamp.org/learn/javascript-algorithms-and-data-structures/">freeCodeCamp — JavaScript Practice</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'JavaScript Basics Quiz',
            'description' => 'Test your understanding of JavaScript variables, functions, events, and DOM manipulation.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which language is mainly responsible for adding interactivity to a web page?',
                    'explanation' => 'JavaScript is the programming language that controls behaviour and interactivity in the browser.',
                    'options' => [
                        ['text' => 'HTML', 'is_correct' => false],
                        ['text' => 'CSS', 'is_correct' => false],
                        ['text' => 'JavaScript', 'is_correct' => true],
                        ['text' => 'SQL', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which keyword should you use to declare a variable whose value will not change?',
                    'explanation' => 'Use const for values that must remain constant after they are set.',
                    'options' => [
                        ['text' => 'let', 'is_correct' => false],
                        ['text' => 'var', 'is_correct' => false],
                        ['text' => 'const', 'is_correct' => true],
                        ['text' => 'change', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does console.log() do in JavaScript?',
                    'explanation' => 'console.log() prints messages to the browser developer console for debugging.',
                    'options' => [
                        ['text' => 'It opens a pop-up alert.', 'is_correct' => false],
                        ['text' => 'It prints output to the browser console.', 'is_correct' => true],
                        ['text' => 'It changes the page background colour.', 'is_correct' => false],
                        ['text' => 'It closes the browser window.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which method finds an HTML element by its id attribute?',
                    'explanation' => 'document.getElementById() returns the element with the matching id.',
                    'options' => [
                        ['text' => 'document.querySelectorAll()', 'is_correct' => false],
                        ['text' => 'document.getElementById()', 'is_correct' => true],
                        ['text' => 'document.findElement()', 'is_correct' => false],
                        ['text' => 'document.getClass()', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'An event listener waits for user actions such as clicks and then runs a function.',
                    'explanation' => 'Event listeners connect user actions to JavaScript functions.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'JavaScript can only run on a web server, not in the browser.',
                    'explanation' => 'JavaScript runs in the browser, although it can also run on servers with Node.js.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'Which keyword declares a variable that can be reassigned later? (one word)',
                    'explanation' => 'let declares a variable whose value can change.',
                    'correct_answer' => 'let',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What property holds the visible text inside an HTML element? (one word)',
                    'explanation' => 'innerText holds the visible text content of an element.',
                    'correct_answer' => 'innerText',
                ],
            ],
        ];
    }

    // -----------------------------------------------------------------
    // Module 4: Modern JavaScript
    // -----------------------------------------------------------------

    private function seedModule4ModernJavaScript(): void
    {
        $module = $this->findOrCreateModule(
            'Modern JavaScript',
            'Use arrays, objects, conditionals, loops, form validation, and simple API calls to build richer web pages.',
            4
        );

        $lessonIds = $this->createLessons($module, $this->module4Lessons());

        if (! empty($lessonIds)) {
            $this->createQuiz($module, $this->module4Quiz(), $lessonIds);
        }
    }

    private function module4Lessons(): array
    {
        return [
            [
                'title' => '4.1 Arrays, Objects, and Loops',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to store lists of data in arrays, group related data in objects, repeat actions with loops, and write JavaScript that handles many items at once.</p>

<h2>Storing Lists with Arrays</h2>
<p>An <strong>array</strong> is a list of values. You can store product names, prices, phone numbers, or anything else in an array. Arrays are written inside square brackets, with commas between items.</p>
<pre><code>let vegetables = ["tomatoes", "onions", "rape", "cabbage"];
let prices = [15, 10, 8, 12];

console.log(vegetables[0]); // tomatoes
console.log(prices[1]);     // 10
</code></pre>
<p>Array items are numbered starting from zero. The first item is at index 0, the second at index 1, and so on.</p>

<h2>Grouping Data with Objects</h2>
<p>An <strong>object</strong> groups related values together using named properties. This is useful for describing one thing, such as a product, a customer, or a bus ticket.</p>
<pre><code>let product = {
    name: "Mealie meal 25kg",
    price: 145,
    inStock: true
};

console.log(product.name);  // Mealie meal 25kg
console.log(product.price); // 145
</code></pre>

<h2>Loops</h2>
<p>A <strong>loop</strong> repeats a block of code many times. The <code>for...of</code> loop is a simple way to go through every item in an array.</p>
<pre><code>let items = ["soap", "salt", "sugar"];

for (let item of items) {
    console.log("Stock item: " + item);
}
</code></pre>
<p>This prints each item on its own line. Loops are powerful because they let you process hundreds or thousands of items with just a few lines of code.</p>

<h2>Worked Example: Shop Stock List</h2>
<p>Imagine you want to print a simple stock list for a small shop. Each product is an object with a name and price. You loop through the array and display each item.</p>
<pre><code>let stock = [
    { name: "Cooking oil 1L", price: 28 },
    { name: "Soap bar", price: 12 },
    { name: "Sugar 1kg", price: 35 }
];

for (let item of stock) {
    console.log(item.name + " — K" + item.price);
}
</code></pre>
<p>The output is:</p>
<pre><code>Cooking oil 1L — K28
Soap bar — K12
Sugar 1kg — K35
</code></pre>

<h2>Try It Yourself</h2>
<ol>
<li>Create an array of five products you might sell at a market stall.</li>
<li>Use a <code>for...of</code> loop to print each product name in the console.</li>
<li>Create an object that describes one product with name, price, and quantity properties.</li>
<li>Calculate the total value of that product by multiplying price by quantity and print it.</li>
<li>Create an array of objects, each representing a different product, and loop through printing the name and total value of each.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Array</strong> — an ordered list of values stored in a single variable.</li>
<li><strong>Object</strong> — a collection of related properties grouped under one name.</li>
<li><strong>Loop</strong> — code that repeats a block of instructions multiple times.</li>
<li><strong>Index</strong> — the position number of an item in an array, starting from zero.</li>
<li><strong>Property</strong> — a named value inside an object, such as <code>price</code> or <code>name</code>.</li>
</ul>

<h2>Summary</h2>
<p>Arrays let you store lists, objects let you group related data, and loops let you process many items efficiently. These three tools are essential for building real web applications such as shopping carts, product galleries, and record keepers. Once you can combine them, you can handle much more interesting projects.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/JavaScript/First_steps/Arrays">MDN Web Docs — Arrays</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/JavaScript/Objects/Basics">MDN Web Docs — Object Basics</a></li>
<li><a href="https://www.w3schools.com/js/js_arrays.asp">W3Schools — JavaScript Arrays</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.2 Conditionals and Form Validation',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to use if-else statements to make decisions, check user input for common mistakes, and add simple validation to a web form so that bad data is caught before it is submitted.</p>

<h2>Making Decisions with If-Else</h2>
<p>Programs often need to make choices. An <code>if</code> statement checks a condition and runs code only if the condition is true. An <code>else</code> block runs when the condition is false.</p>
<pre><code>let age = 17;

if (age &gt;= 18) {
    console.log("You may register.");
} else {
    console.log("You must be 18 or older.");
}
</code></pre>

<h2>Combining Conditions</h2>
<p>You can combine conditions with <code>&amp;&amp;</code> (and) and <code>||</code> (or). This is useful when several things must be true at once.</p>
<pre><code>let balance = 250;
let amount = 200;

if (balance &gt;= amount &amp;&amp; amount &gt; 0) {
    console.log("Payment approved.");
} else {
    console.log("Payment failed.");
}
</code></pre>

<h2>Why Validate Forms?</h2>
<p>Forms are where users enter data. They might type a wrong phone number, leave a field empty, or enter a negative amount. Validation catches these problems early. It saves time, reduces errors, and gives users clear feedback. A mobile money portal, for example, should check that a phone number has ten digits before processing a payment.</p>

<h2>Worked Example: Validating a Booking Form</h2>
<p>Suppose you run a small transport booking service. A customer enters their name, phone number, and number of seats. You want to check that all fields are filled and the phone number is valid.</p>
<pre><code>&lt;form id="booking-form"&gt;
    &lt;input type="text" id="name" placeholder="Your name"&gt;
    &lt;input type="text" id="phone" placeholder="Phone number"&gt;
    &lt;input type="number" id="seats" placeholder="Seats"&gt;
    &lt;button type="submit"&gt;Book&lt;/button&gt;
&lt;/form&gt;
&lt;p id="message"&gt;&lt;/p&gt;

&lt;script&gt;
    document.getElementById("booking-form").addEventListener("submit", function(event) {
        event.preventDefault();

        let name = document.getElementById("name").value.trim();
        let phone = document.getElementById("phone").value.trim();
        let seats = document.getElementById("seats").value;
        let message = document.getElementById("message");

        if (name === "" || phone === "" || seats === "") {
            message.innerText = "Please fill in all fields.";
            message.style.color = "red";
        } else if (phone.length !== 10) {
            message.innerText = "Phone number must be 10 digits.";
            message.style.color = "red";
        } else {
            message.innerText = "Booking accepted. We will call you on " + phone + ".";
            message.style.color = "green";
        }
    });
&lt;/script&gt;
</code></pre>
<p>The <code>event.preventDefault()</code> line stops the form from reloading the page. This lets JavaScript handle the response and show a message instantly.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create an HTML form with fields for name, email, and a message.</li>
<li>Write JavaScript that checks whether the name and message fields are not empty.</li>
<li>Show a red message if any field is empty.</li>
<li>If everything is filled, show a green confirmation message.</li>
<li>Add a check that the message is at least 10 characters long.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Conditional</strong> — code that runs only when a condition is true.</li>
<li><strong>if-else</strong> — a statement that chooses between two blocks of code.</li>
<li><strong>Validation</strong> — checking user input to make sure it is correct and complete.</li>
<li><strong>event.preventDefault()</strong> — a method that stops the browser's default action for an event.</li>
<li><strong>trim()</strong> — a string method that removes spaces from the beginning and end of text.</li>
</ul>

<h2>Summary</h2>
<p>Conditionals let your programs make decisions, and validation helps you catch bad input before it causes problems. Together they are the foundation of user-friendly forms. Whether you are building a booking form, a payment page, or a registration system, these skills help you guide users and protect your data.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/JavaScript/Building_blocks/conditionals">MDN Web Docs — Conditionals</a></li>
<li><a href="https://www.w3schools.com/js/js_validation.asp">W3Schools — JavaScript Form Validation</a></li>
<li><a href="https://www.freecodecamp.org/learn/javascript-algorithms-and-data-structures/">freeCodeCamp — JavaScript Algorithms</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.3 Fetching Data from APIs',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will understand what an API is, fetch data from a public API using JavaScript, display that data on a web page, and explain why this matters for live services such as exchange rates and weather updates.</p>

<h2>What Is an API?</h2>
<p>An <strong>API</strong>, or Application Programming Interface, is a way for two programs to talk to each other. On the web, an API usually means a server that sends data when you ask for it. Your web page can request data from an API and then display it without reloading. This is how news feeds update, weather apps show forecasts, and currency converters get live rates.</p>

<h2>The fetch() Function</h2>
<p>Modern JavaScript includes a built-in function called <code>fetch()</code> for making API requests. It asks a server for data and returns a <strong>promise</strong>. A promise is a way of saying "I will give you the result when it is ready." You use <code>.then()</code> to handle the result.</p>
<pre><code>fetch("https://api.example.com/rates")
    .then(response =&gt; response.json())
    .then(data =&gt; {
        console.log(data);
    });
</code></pre>

<h2>Worked Example: Displaying an Exchange Rate</h2>
<p>Suppose you want to show the current USD to ZMW exchange rate on a small business page. You fetch the data and update a paragraph on the page.</p>
<pre><code>&lt;p id="rate"&gt;Loading rate...&lt;/p&gt;

&lt;script&gt;
    fetch("https://api.exchangerate-api.com/v4/latest/USD")
        .then(response =&gt; response.json())
        .then(data =&gt; {
            let rate = data.rates.ZMW;
            document.getElementById("rate").innerText = "1 USD = " + rate + " ZMW";
        })
        .catch(error =&gt; {
            document.getElementById("rate").innerText = "Could not load rate.";
        });
&lt;/script&gt;
</code></pre>
<p>This example fetches live rate data and displays it. The <code>.catch()</code> block handles errors, such as when the phone has no data connection.</p>

<h2>APIs in Zambia</h2>
<p>Zambian businesses and developers use APIs for many practical tasks:</p>
<ul>
<li><strong>Mobile money APIs</strong> let online shops accept Airtel Money or MTN MoMo payments.</li>
<li><strong>SMS APIs</strong> send appointment reminders or delivery notifications to customers.</li>
<li><strong>Weather APIs</strong> help farmers plan planting and harvesting.</li>
<li><strong>Currency APIs</strong> help importers and exporters track exchange rates.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Create an HTML paragraph with id <code>rate</code>.</li>
<li>Use the fetch example above to load the USD to ZMW rate and display it.</li>
<li>Open the page and check that the rate appears. Try switching mobile data off to see the error message.</li>
<li>Fetch data from a different public API, such as a random quote API, and display the quote on the page.</li>
<li>Read the API documentation and identify one piece of data you could use in a local business website.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>API</strong> — a service that lets programs request and exchange data.</li>
<li><strong>fetch()</strong> — a JavaScript function for making HTTP requests.</li>
<li><strong>JSON</strong> — a common format for sending structured data between a server and a web page.</li>
<li><strong>Promise</strong> — an object that represents a value that will be available later.</li>
<li><strong>Endpoint</strong> — a specific URL where an API can be accessed.</li>
</ul>

<h2>Summary</h2>
<p>APIs connect your web pages to live data. With <code>fetch()</code>, you can pull exchange rates, weather, quotes, or any other public data into your site. This is how modern websites stay current without requiring the user to refresh the page. Understanding APIs opens the door to building richer, more useful web applications for Zambian users.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API">MDN Web Docs — Fetch API</a></li>
<li><a href="https://www.w3schools.com/js/js_api_intro.asp">W3Schools — Web APIs</a></li>
<li><a href="https://www.freecodecamp.org/news/how-to-fetch-data-from-an-api-using-javascript/">freeCodeCamp — Fetch Data with JavaScript</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.4 Storing Data in the Browser',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to save small amounts of data in the browser using localStorage, retrieve that data when the page loads, and build a simple note-taking or preference-saving feature.</p>

<h2>What Is localStorage?</h2>
<p><strong>localStorage</strong> is a built-in browser feature that lets you store small pieces of data on the user's computer or phone. The data stays even after the browser is closed. This is useful for remembering user preferences, saving drafts, or keeping a simple shopping cart on a device with limited internet.</p>

<h2>Saving and Reading Data</h2>
<p>You can save a value with <code>localStorage.setItem()</code> and read it back with <code>localStorage.getItem()</code>.</p>
<pre><code>localStorage.setItem("userName", "Grace");
let name = localStorage.getItem("userName");
console.log(name); // Grace
</code></pre>
<p>Only strings can be stored directly. If you want to store an object or array, convert it to a JSON string first.</p>
<pre><code>let cart = [
    { item: "Tomatoes", price: 15 },
    { item: "Onions", price: 10 }
];

localStorage.setItem("cart", JSON.stringify(cart));

let savedCart = JSON.parse(localStorage.getItem("cart"));
console.log(savedCart);
</code></pre>

<h2>Worked Example: A Simple Note Saver</h2>
<p>Imagine a student wants to write notes during load-shedding and have them available when the power returns. A simple localStorage note saver keeps the text safe in the browser.</p>
<pre><code>&lt;textarea id="note" rows="6" cols="40"&gt;&lt;/textarea&gt;&lt;br&gt;
&lt;button id="save"&gt;Save Note&lt;/button&gt;
&lt;button id="load"&gt;Load Note&lt;/button&gt;
&lt;p id="status"&gt;&lt;/p&gt;

&lt;script&gt;
    let noteBox = document.getElementById("note");
    let status = document.getElementById("status");

    document.getElementById("save").addEventListener("click", function() {
        localStorage.setItem("myNote", noteBox.value);
        status.innerText = "Note saved.";
    });

    document.getElementById("load").addEventListener("click", function() {
        let saved = localStorage.getItem("myNote");
        if (saved) {
            noteBox.value = saved;
            status.innerText = "Note loaded.";
        } else {
            status.innerText = "No note found.";
        }
    });
&lt;/script&gt;
</code></pre>

<h2>Limits of localStorage</h2>
<p>localStorage is simple but has limits. It can only store about 5 to 10 megabytes of data, and everything is stored as text. It is not secure for passwords or sensitive information. For large or private data, you need a server and a database.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a textarea and two buttons labelled Save and Load.</li>
<li>Save the textarea content to localStorage when Save is clicked.</li>
<li>Load the saved content back into the textarea when Load is clicked.</li>
<li>Close the browser tab and reopen it. Click Load and confirm the note is still there.</li>
<li>Store a user preference such as background colour using localStorage and apply it when the page loads.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>localStorage</strong> — browser storage that keeps data after the page is closed.</li>
<li><strong>setItem()</strong> — a method that saves a key-value pair in localStorage.</li>
<li><strong>getItem()</strong> — a method that reads a value from localStorage by its key.</li>
<li><strong>JSON.stringify()</strong> — converts a JavaScript object into a JSON string.</li>
<li><strong>JSON.parse()</strong> — converts a JSON string back into a JavaScript object.</li>
</ul>

<h2>Summary</h2>
<p>localStorage lets you save small amounts of data directly in the browser. It is useful for notes, preferences, and simple carts. Remember that it stores text only, so objects and arrays must be converted with JSON. For sensitive or large data, always use a proper server database.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Web/API/Web_Storage_API">MDN Web Docs — Web Storage API</a></li>
<li><a href="https://www.w3schools.com/jsref/prop_win_localstorage.asp">W3Schools — localStorage</a></li>
<li><a href="https://www.freecodecamp.org/news/javascript-localstorage-how-to-store-data-in-the-browser/">freeCodeCamp — localStorage Guide</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module4Quiz(): array
    {
        return [
            'title' => 'Modern JavaScript Quiz',
            'description' => 'Check your knowledge of arrays, objects, conditionals, API calls, and browser storage.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which bracket type is used to create an array in JavaScript?',
                    'explanation' => 'Arrays are created with square brackets [].',
                    'options' => [
                        ['text' => '{}', 'is_correct' => false],
                        ['text' => '[]', 'is_correct' => true],
                        ['text' => '()', 'is_correct' => false],
                        ['text' => '<>', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does event.preventDefault() do in a form submit handler?',
                    'explanation' => 'It stops the browser from performing its default form submission action.',
                    'options' => [
                        ['text' => 'It resets the form fields.', 'is_correct' => false],
                        ['text' => 'It prevents the default action, such as page reload.', 'is_correct' => true],
                        ['text' => 'It validates all input fields.', 'is_correct' => false],
                        ['text' => 'It submits the form silently.', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which method converts a JavaScript object into a JSON string?',
                    'explanation' => 'JSON.stringify() converts objects and arrays into strings.',
                    'options' => [
                        ['text' => 'JSON.parse()', 'is_correct' => false],
                        ['text' => 'JSON.stringify()', 'is_correct' => true],
                        ['text' => 'object.toString()', 'is_correct' => false],
                        ['text' => 'localStorage.save()', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which JavaScript function is commonly used to request data from an API?',
                    'explanation' => 'fetch() is the modern way to make HTTP requests in the browser.',
                    'options' => [
                        ['text' => 'ask()', 'is_correct' => false],
                        ['text' => 'getData()', 'is_correct' => false],
                        ['text' => 'fetch()', 'is_correct' => true],
                        ['text' => 'request()', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'An object groups related data using named properties.',
                    'explanation' => 'Objects store related values as named properties.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'localStorage is the best place to store a user password.',
                    'explanation' => 'localStorage is not secure; passwords should never be stored there.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What keyword introduces a block of code that runs when a condition is true? (one word)',
                    'explanation' => 'The if statement runs code when its condition is true.',
                    'correct_answer' => 'if',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the position number of the first item in an array? (one word or number)',
                    'explanation' => 'Array indexing starts at zero.',
                    'correct_answer' => '0',
                ],
            ],
        ];
    }

    // -----------------------------------------------------------------
    // Module 5: Responsive Design and CSS Frameworks
    // -----------------------------------------------------------------

    private function seedModule5ResponsiveDesign(): void
    {
        $module = $this->findOrCreateModule(
            'Responsive Design and CSS Frameworks',
            'Build websites that look good on phones, tablets, and desktops using media queries, Flexbox, Grid, and Bootstrap.',
            5
        );

        $lessonIds = $this->createLessons($module, $this->module5Lessons());

        if (! empty($lessonIds)) {
            $this->createQuiz($module, $this->module5Quiz(), $lessonIds);
        }
    }

    private function module5Lessons(): array
    {
        return [
            [
                'title' => '5.1 Thinking Mobile-First in Zambia',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will understand what mobile-first design means, know why it matters in Zambia, and be able to plan a page layout that works well on small screens before adapting it for larger ones.</p>

<h2>Why Mobile-First?</h2>
<p>In Zambia, many people access the internet using smartphones rather than laptops. A market vendor in Kalomo, a farmer in a rural area, and a student on a college bus are all likely to view your website on a small screen first. If your site is hard to use on a phone, you lose visitors before they even read your message. Mobile-first design means you plan for the smallest screen first, then add more features for larger screens.</p>

<h2>The Mobile-First Mindset</h2>
<p>When you design mobile-first, you ask different questions. Instead of "How can I fit everything on a big monitor?" you ask "What is the most important thing a phone user needs?" You start with a single column, large touch-friendly buttons, and simple navigation. Later, you use media queries to rearrange content for tablets and desktops.</p>

<h2>Key Mobile-First Practices</h2>
<ul>
<li><strong>Single column layout</strong> — stack content vertically so it fits narrow screens.</li>
<li><strong>Large touch targets</strong> — make buttons and links big enough to tap with a thumb.</li>
<li><strong>Readable text</strong> — use font sizes that do not require zooming.</li>
<li><strong>Fast loading</strong> — keep images and scripts small so the page loads quickly on slow mobile data.</li>
<li><strong>Simple navigation</strong> — use a collapsible menu instead of a long row of links.</li>
</ul>

<h2>Worked Example: Mobile-First Product Card</h2>
<p>Imagine you are building a page for a local chicken-rearing business. On a phone, each product should take the full width. On a desktop, three products can sit side by side.</p>
<pre><code>&lt;style&gt;
    .product {
        border: 1px solid #ccc;
        padding: 16px;
        margin-bottom: 16px;
    }

    @media (min-width: 768px) {
        .product-list {
            display: flex;
            gap: 16px;
        }
        .product {
            flex: 1;
        }
    }
&lt;/style&gt;

&lt;div class="product-list"&gt;
    &lt;div class="product"&gt;
        &lt;h3&gt;Day-old chicks&lt;/h3&gt;
        &lt;p&gt;K15 each&lt;/p&gt;
    &lt;/div&gt;
    &lt;div class="product"&gt;
        &lt;h3&gt;Layers feed&lt;/h3&gt;
        &lt;p&gt;K250 per 50kg bag&lt;/p&gt;
    &lt;/div&gt;
    &lt;div class="product"&gt;
        &lt;h3&gt;Vaccines&lt;/h3&gt;
        &lt;p&gt;From K80&lt;/p&gt;
    &lt;/div&gt;
&lt;/div&gt;
</code></pre>
<p>On a phone, each product stacks. On a tablet or larger, the products line up horizontally. The mobile layout is the default; the wider layout is added only when needed.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a simple HTML page with three sections: header, main content, and footer.</li>
<li>Make each section full width by default so it looks good on a phone.</li>
<li>Open the page in Chrome and use Developer Tools to switch to a mobile view.</li>
<li>Add a media query that makes the main content and sidebar sit side by side on screens wider than 768 pixels.</li>
<li>Test by resizing the browser window from narrow to wide.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Mobile-first</strong> — designing for small screens first, then enhancing for larger screens.</li>
<li><strong>Touch target</strong> — the area a user taps on a touchscreen; it should be large enough to hit easily.</li>
<li><strong>Media query</strong> — CSS code that applies styles only when certain conditions are met, such as screen width.</li>
<li><strong>Viewport</strong> — the visible area of a web page on a device screen.</li>
<li><strong>Breakpoint</strong> — the screen width at which a layout changes.</li>
</ul>

<h2>Summary</h2>
<p>Mobile-first design puts the needs of smartphone users first. In Zambia, where mobile internet is common, this approach ensures your website reaches the widest audience. Start with a simple single-column layout, make buttons easy to tap, and use media queries to improve the experience on larger screens.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/CSS/CSS_layout/Responsive_Design">MDN Web Docs — Responsive Design</a></li>
<li><a href="https://www.w3schools.com/css/css_rwd_intro.asp">W3Schools — Responsive Web Design</a></li>
<li><a href="https://grow.google/">Grow with Google — Digital Skills</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.2 Media Queries and Breakpoints',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to write CSS media queries, choose sensible breakpoints for common devices, and change layout, font sizes, and spacing based on screen width.</p>

<h2>What Is a Media Query?</h2>
<p>A <strong>media query</strong> is a CSS rule that applies styles only when certain conditions are true. The most common condition is screen width. Media queries let you say things like "if the screen is wider than 768 pixels, make the navigation horizontal." They are the main tool for responsive design.</p>

<h2>Basic Media Query Syntax</h2>
<pre><code>@media (min-width: 768px) {
    body {
        font-size: 18px;
    }
}
</code></pre>
<p>This rule increases the font size only when the viewport is at least 768 pixels wide. You can also use <code>max-width</code> to target smaller screens.</p>

<h2>Common Breakpoints</h2>
<p>Breakpoints are the widths where your layout changes. You do not need dozens of them. A simple set is:</p>
<ul>
<li><strong>Phones</strong>: up to 600 pixels</li>
<li><strong>Tablets</strong>: 601 to 991 pixels</li>
<li><strong>Desktops</strong>: 992 pixels and above</li>
</ul>
<p>These numbers are guidelines, not strict rules. Test your site on real devices and resize the browser to find what works for your content.</p>

<h2>Worked Example: Responsive Navigation</h2>
<p>A navigation menu should stack on phones and spread out on larger screens. Here is one way to do it.</p>
<pre><code>&lt;style&gt;
    nav ul {
        list-style: none;
        padding: 0;
    }

    nav li {
        padding: 12px;
        border-bottom: 1px solid #ddd;
    }

    @media (min-width: 768px) {
        nav ul {
            display: flex;
        }
        nav li {
            border-bottom: none;
        }
    }
&lt;/style&gt;

&lt;nav&gt;
    &lt;ul&gt;
        &lt;li&gt;&lt;a href="index.html"&gt;Home&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="products.html"&gt;Products&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="prices.html"&gt;Prices&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="contact.html"&gt;Contact&lt;/a&gt;&lt;/li&gt;
    &lt;/ul&gt;
&lt;/nav&gt;
</code></pre>
<p>On a small screen, each link appears on its own line. On a larger screen, the links sit in a row.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a page with a heading and a paragraph.</li>
<li>Write a media query that changes the heading colour on screens narrower than 600 pixels.</li>
<li>Add another media query that changes the paragraph font size on screens wider than 992 pixels.</li>
<li>Create a simple two-column layout using Flexbox that becomes a single column on small screens.</li>
<li>Resize your browser and confirm each breakpoint works.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Media query</strong> — a CSS technique that applies styles based on conditions such as screen width.</li>
<li><strong>Breakpoint</strong> — a specific screen width where the layout changes.</li>
<li><strong>min-width</strong> — a media query condition that targets screens at least as wide as a value.</li>
<li><strong>max-width</strong> — a media query condition that targets screens no wider than a value.</li>
<li><strong>Viewport</strong> — the visible area of a web page in the browser.</li>
</ul>

<h2>Summary</h2>
<p>Media queries are the heart of responsive CSS. They let you adapt layouts, text sizes, and spacing to different screen widths. Combine them with a mobile-first approach and a small set of sensible breakpoints, and your pages will work well on phones, tablets, and desktops across Zambia.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_media_queries/Using_media_queries">MDN Web Docs — Media Queries</a></li>
<li><a href="https://www.w3schools.com/css/css3_mediaqueries_ex.asp">W3Schools — CSS Media Queries Examples</a></li>
<li><a href="https://www.freecodecamp.org/news/css-media-queries-breakpoints/">freeCodeCamp — Media Query Breakpoints</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.3 Introduction to Bootstrap',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will understand what a CSS framework is, know why Bootstrap is popular, add Bootstrap to a web page, and use its grid system to build a responsive layout quickly.</p>

<h2>What Is a CSS Framework?</h2>
<p>A <strong>CSS framework</strong> is a collection of pre-written CSS and JavaScript that helps you build websites faster. Instead of writing every style from scratch, you use classes that the framework provides. Frameworks handle common tasks such as grids, buttons, forms, and navigation. This saves time and helps your site look consistent.</p>

<h2>Why Bootstrap?</h2>
<p>Bootstrap is one of the most widely used CSS frameworks. It is free, well documented, and includes a powerful grid system. With Bootstrap, you can create professional-looking pages without being a design expert. This is useful for small business owners and students who want good results quickly.</p>

<h2>Adding Bootstrap to Your Page</h2>
<p>The easiest way to start is by adding Bootstrap from a Content Delivery Network, or CDN. A CDN hosts the files on fast servers around the world. You add one link to your HTML head.</p>
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;
    &lt;title&gt;My Bootstrap Page&lt;/title&gt;
    &lt;link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;h1&gt;Hello, Bootstrap&lt;/h1&gt;
&lt;/body&gt;
&lt;/html&gt;
</code></pre>

<h2>The Bootstrap Grid</h2>
<p>Bootstrap divides the page into 12 columns. You decide how many columns each piece of content should use at different screen sizes. The class names follow a pattern: <code>col-</code> for extra small screens, <code>col-sm-</code> for small screens, <code>col-md-</code> for medium screens, and <code>col-lg-</code> for large screens.</p>
<pre><code>&lt;div class="container"&gt;
    &lt;div class="row"&gt;
        &lt;div class="col-12 col-md-4"&gt;
            &lt;p&gt;Product 1&lt;/p&gt;
        &lt;/div&gt;
        &lt;div class="col-12 col-md-4"&gt;
            &lt;p&gt;Product 2&lt;/p&gt;
        &lt;/div&gt;
        &lt;div class="col-12 col-md-4"&gt;
            &lt;p&gt;Product 3&lt;/p&gt;
        &lt;/div&gt;
    &lt;/div&gt;
&lt;/div&gt;
</code></pre>
<p>On small screens, each product takes all 12 columns and stacks vertically. On medium screens and larger, each product takes 4 columns, so three products fit side by side.</p>

<h2>Worked Example: A Simple Business Page</h2>
<p>Here is a basic page for a tailoring shop. It uses Bootstrap classes for a navigation bar, a header, and a three-column services section.</p>
<pre><code>&lt;nav class="navbar navbar-expand-lg navbar-light bg-light"&gt;
    &lt;div class="container"&gt;
        &lt;a class="navbar-brand" href="#"&gt;Kalomo Tailors&lt;/a&gt;
    &lt;/div&gt;
&lt;/nav&gt;

&lt;header class="container my-4"&gt;
    &lt;h1&gt;Quality Tailoring in Kalomo&lt;/h1&gt;
    &lt;p&gt;School uniforms, traditional wear, and alterations.&lt;/p&gt;
&lt;/header&gt;

&lt;section class="container"&gt;
    &lt;div class="row"&gt;
        &lt;div class="col-md-4"&gt;
            &lt;h3&gt;School Uniforms&lt;/h3&gt;
            &lt;p&gt;From K150&lt;/p&gt;
        &lt;/div&gt;
        &lt;div class="col-md-4"&gt;
            &lt;h3&gt;Traditional Wear&lt;/h3&gt;
            &lt;p&gt;Custom designs&lt;/p&gt;
        &lt;/div&gt;
        &lt;div class="col-md-4"&gt;
            &lt;h3&gt;Alterations&lt;/h3&gt;
            &lt;p&gt;From K30&lt;/p&gt;
        &lt;/div&gt;
    &lt;/div&gt;
&lt;/section&gt;
</code></pre>

<h2>Try It Yourself</h2>
<ol>
<li>Create a new HTML file and add the Bootstrap CSS link from the CDN.</li>
<li>Build a container with one row and three columns.</li>
<li>Make each column full width on small screens and one-third width on medium screens.</li>
<li>Add a Bootstrap button to one of the columns.</li>
<li>Resize the browser to see the columns stack and expand.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>CSS framework</strong> — a pre-built set of CSS and JavaScript files that speed up website development.</li>
<li><strong>Bootstrap</strong> — a popular free CSS framework with a responsive grid system.</li>
<li><strong>Grid system</strong> — a layout method that divides a page into rows and columns.</li>
<li><strong>CDN</strong> — a network of servers that delivers files quickly from a location near the user.</li>
<li><strong>Class</strong> — an HTML attribute used to apply predefined styles or behaviour.</li>
</ul>

<h2>Summary</h2>
<p>Bootstrap helps you build responsive websites faster by providing a grid system, ready-made components, and consistent styling. You can add it through a CDN and start using its classes immediately. For learners and small businesses in Zambia, Bootstrap is a practical way to create professional pages without writing large amounts of custom CSS.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://getbootstrap.com/docs/5.3/getting-started/introduction/">Bootstrap Official Documentation</a></li>
<li><a href="https://www.w3schools.com/bootstrap5/">W3Schools — Bootstrap 5 Tutorial</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/CSS/CSS_layout/Grids">MDN Web Docs — CSS Grids</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.4 Building a Responsive Page with Bootstrap',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to combine Bootstrap components and utility classes to build a complete responsive page, including a navigation bar, a hero section, a services grid, and a contact form.</p>

<h2>Planning the Page</h2>
<p>Before writing code, decide what the page needs. A typical small business website includes:</p>
<ul>
<li>A navigation bar with links to the main sections.</li>
<li>A hero section that tells visitors what the business does.</li>
<li>A services or products section.</li>
<li>A contact section with a form or phone number.</li>
<li>A footer with basic information.</li>
</ul>

<h2>Bootstrap Components</h2>
<p>Bootstrap provides ready-made components. Some useful ones are:</p>
<ul>
<li><strong>Navbar</strong> — a responsive navigation bar that collapses on small screens.</li>
<li><strong>Cards</strong> — flexible containers for products or services.</li>
<li><strong>Buttons</strong> — styled buttons with many colour options.</li>
<li><strong>Forms</strong> — styled input fields, labels, and buttons.</li>
<li><strong>Grid</strong> — rows and columns for responsive layouts.</li>
</ul>

<h2>Worked Example: A Small Farm Business Page</h2>
<p>Here is a complete but simple page for a farm selling fresh produce. It uses Bootstrap classes throughout.</p>
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;Soweto Fresh Farm&lt;/title&gt;
    &lt;link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;nav class="navbar navbar-expand-md navbar-dark bg-success"&gt;
        &lt;div class="container"&gt;
            &lt;a class="navbar-brand" href="#"&gt;Soweto Fresh Farm&lt;/a&gt;
            &lt;button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu"&gt;
                &lt;span class="navbar-toggler-icon"&gt;&lt;/span&gt;
            &lt;/button&gt;
            &lt;div class="collapse navbar-collapse" id="menu"&gt;
                &lt;ul class="navbar-nav ms-auto"&gt;
                    &lt;li class="nav-item"&gt;&lt;a class="nav-link" href="#"&gt;Home&lt;/a&gt;&lt;/li&gt;
                    &lt;li class="nav-item"&gt;&lt;a class="nav-link" href="#"&gt;Produce&lt;/a&gt;&lt;/li&gt;
                    &lt;li class="nav-item"&gt;&lt;a class="nav-link" href="#"&gt;Contact&lt;/a&gt;&lt;/li&gt;
                &lt;/ul&gt;
            &lt;/div&gt;
        &lt;/div&gt;
    &lt;/nav&gt;

    &lt;header class="container my-5 text-center"&gt;
        &lt;h1&gt;Fresh Produce from Kalomo&lt;/h1&gt;
        &lt;p class="lead"&gt;Tomatoes, onions, rape, and cabbage delivered to your door.&lt;/p&gt;
        &lt;a href="#" class="btn btn-success"&gt;Order Now&lt;/a&gt;
    &lt;/header&gt;

    &lt;section class="container"&gt;
        &lt;div class="row"&gt;
            &lt;div class="col-md-4 mb-4"&gt;
                &lt;div class="card"&gt;
                    &lt;div class="card-body"&gt;
                        &lt;h5 class="card-title"&gt;Tomatoes&lt;/h5&gt;
                        &lt;p class="card-text"&gt;K15 per heap. Fresh from the garden.&lt;/p&gt;
                    &lt;/div&gt;
                &lt;/div&gt;
            &lt;/div&gt;
            &lt;div class="col-md-4 mb-4"&gt;
                &lt;div class="card"&gt;
                    &lt;div class="card-body"&gt;
                        &lt;h5 class="card-title"&gt;Onions&lt;/h5&gt;
                        &lt;p class="card-text"&gt;K10 per heap. Perfect for stews.&lt;/p&gt;
                    &lt;/div&gt;
                &lt;/div&gt;
            &lt;/div&gt;
            &lt;div class="col-md-4 mb-4"&gt;
                &lt;div class="card"&gt;
                    &lt;div class="card-body"&gt;
                        &lt;h5 class="card-title"&gt;Cabbage&lt;/h5&gt;
                        &lt;p class="card-text"&gt;K12 per head. Large and firm.&lt;/p&gt;
                    &lt;/div&gt;
                &lt;/div&gt;
            &lt;/div&gt;
        &lt;/div&gt;
    &lt;/section&gt;

    &lt;footer class="bg-light text-center py-3"&gt;
        &lt;p&gt;&amp;copy; 2025 Soweto Fresh Farm, Kalomo&lt;/p&gt;
    &lt;/footer&gt;
&lt;/body&gt;
&lt;/html&gt;
</code></pre>

<h2>Try It Yourself</h2>
<ol>
<li>Copy the example into a new HTML file and open it in a browser.</li>
<li>Replace the farm name and produce with your own business idea.</li>
<li>Add a fourth product card in a new column.</li>
<li>Change the navigation colour by replacing <code>bg-success</code> with another Bootstrap colour class such as <code>bg-primary</code> or <code>bg-dark</code>.</li>
<li>Resize the browser to check that the menu collapses and the cards stack on small screens.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Component</strong> — a reusable building block such as a navbar, card, or button.</li>
<li><strong>Utility class</strong> — a small class that applies a single style, such as margin or colour.</li>
<li><strong>Responsive navbar</strong> — a navigation bar that collapses into a menu button on small screens.</li>
<li><strong>Card</strong> — a flexible container used to display content such as products or services.</li>
<li><strong>Hero section</strong> — a large prominent area at the top of a page that introduces the site.</li>
</ul>

<h2>Summary</h2>
<p>With Bootstrap you can build complete responsive pages using a small set of classes. A navbar, hero section, card grid, and footer form a solid foundation for most small business websites. Practice by adapting the example to a real business in your area, and you will quickly see how powerful a framework can be.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://getbootstrap.com/docs/5.3/components/navbar/">Bootstrap — Navbar Documentation</a></li>
<li><a href="https://getbootstrap.com/docs/5.3/components/card/">Bootstrap — Cards Documentation</a></li>
<li><a href="https://www.w3schools.com/bootstrap5/">W3Schools — Bootstrap 5 Tutorial</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module5Quiz(): array
    {
        return [
            'title' => 'Responsive Design and CSS Frameworks Quiz',
            'description' => 'Test your knowledge of mobile-first design, media queries, breakpoints, and Bootstrap.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does mobile-first design mean?',
                    'explanation' => 'Mobile-first means designing for small screens first, then enhancing for larger screens.',
                    'options' => [
                        ['text' => 'Designing only for mobile phones', 'is_correct' => false],
                        ['text' => 'Designing for desktops first and shrinking later', 'is_correct' => false],
                        ['text' => 'Designing for small screens first, then adding features for larger screens', 'is_correct' => true],
                        ['text' => 'Ignoring tablets completely', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which CSS feature applies styles based on screen width?',
                    'explanation' => 'Media queries let you apply CSS only when certain conditions are met.',
                    'options' => [
                        ['text' => 'Flexbox', 'is_correct' => false],
                        ['text' => 'Media query', 'is_correct' => true],
                        ['text' => 'Variable', 'is_correct' => false],
                        ['text' => 'Selector', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'How many columns does Bootstrap use in its grid system?',
                    'explanation' => 'Bootstrap divides each row into 12 columns.',
                    'options' => [
                        ['text' => '6', 'is_correct' => false],
                        ['text' => '10', 'is_correct' => false],
                        ['text' => '12', 'is_correct' => true],
                        ['text' => '16', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which class would make a Bootstrap column full width on phones and one-third width on medium screens?',
                    'explanation' => 'col-12 uses all 12 columns on small screens; col-md-4 uses 4 columns on medium screens and up.',
                    'options' => [
                        ['text' => 'col-4 col-md-12', 'is_correct' => false],
                        ['text' => 'col-12 col-md-4', 'is_correct' => true],
                        ['text' => 'col-small-12 col-large-4', 'is_correct' => false],
                        ['text' => 'col-phone-full col-desktop-third', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A breakpoint is the screen width where a layout changes.',
                    'explanation' => 'Breakpoints are the widths at which responsive designs adjust their layout.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Bootstrap is a programming language like JavaScript.',
                    'explanation' => 'Bootstrap is a CSS framework, not a programming language.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What keyword begins a CSS media query rule? (one word)',
                    'explanation' => 'Media queries begin with the @media rule.',
                    'correct_answer' => '@media',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What Bootstrap class adds horizontal spacing automatically between columns? (one word)',
                    'explanation' => 'The row class creates a horizontal group of columns.',
                    'correct_answer' => 'row',
                ],
            ],
        ];
    }

    // -----------------------------------------------------------------
    // Module 6: Launching Your Website
    // -----------------------------------------------------------------

    private function seedModule6LaunchingSite(): void
    {
        $module = $this->findOrCreateModule(
            'Launching Your Website',
            'Take a website live by choosing a domain name, selecting affordable hosting, uploading files, and submitting the site to Google.',
            6
        );

        $lessonIds = $this->createLessons($module, $this->module6Lessons());

        if (! empty($lessonIds)) {
            $this->createQuiz($module, $this->module6Quiz(), $lessonIds);
        }
    }

    private function module6Lessons(): array
    {
        return [
            [
                'title' => '6.1 Choosing and Registering a Domain Name',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will understand what a domain name is, know how to choose a good domain for a Zambian business, compare common domain extensions, and register a domain through a registrar.</p>

<h2>What Is a Domain Name?</h2>
<p>A <strong>domain name</strong> is the address people type into a browser to visit your website. It is easier to remember than a string of numbers. For example, <code>edutrackzambia.com</code> is a domain name. Behind the scenes, computers use IP addresses, but domain names let humans find websites easily.</p>

<h2>Choosing a Good Domain</h2>
<p>A good domain name should be:</p>
<ul>
<li><strong>Short and simple</strong> — easy to type and say over the phone.</li>
<li><strong>Easy to spell</strong> — avoid confusing words or unusual spellings.</li>
<li><strong>Relevant</strong> — related to your business or name.</li>
<li><strong>Memorable</strong> — so customers can find you again.</li>
</ul>
<p>For a tailoring shop in Kalomo, good options might be <code>kalomotailors.com</code> or <code>chisalatailors.com</code>. Avoid names that are very long or hard to pronounce.</p>

<h2>Domain Extensions</h2>
<p>The part after the dot is called the <strong>top-level domain</strong> or <strong>extension</strong>. Common choices include:</p>
<ul>
<li><strong>.com</strong> — the most recognised extension worldwide. Good for businesses.</li>
<li><strong>.zm</strong> — the country code for Zambia. Shows local identity.</li>
<li><strong>.co.zm</strong> — often used by Zambian companies.</li>
<li><strong>.org.zm</strong> — commonly used by non-profit organisations in Zambia.</li>
</ul>
<p>A <code>.zm</code> domain can build trust with local customers, while <code>.com</code> is better if you want an international audience. Prices vary, so compare registrars.</p>

<h2>How to Register a Domain</h2>
<ol>
<li>Think of several possible domain names in case your first choice is taken.</li>
<li>Visit a domain registrar such as Namecheap, Truehost, or a local Zambian registrar.</li>
<li>Type your desired domain into the search box.</li>
<li>If the domain is available, add it to your cart and create an account.</li>
<li>Pay the registration fee, which is usually yearly.</li>
<li>Keep your login details safe. You will need them to connect the domain to your hosting.</li>
</ol>

<h2>Worked Example: Registering kalomofresh.co.zm</h2>
<p>Mrs Zulu wants a website for her fresh produce business. She searches for <code>kalomofresh.co.zm</code> but it is taken. She tries <code>kalomofreshproduce.co.zm</code> and it is available. She registers it for one year at a cost of about K250. She records the login details in her phone and her email so she does not lose them.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Think of a business idea or use your own name.</li>
<li>Write down five possible domain names, from favourite to backup.</li>
<li>Visit a domain registrar and check whether each name is available.</li>
<li>Note the price for one year of registration.</li>
<li>Choose one available name and explain why it is a good choice.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Domain name</strong> — the human-readable address of a website, such as example.com.</li>
<li><strong>Registrar</strong> — a company that sells and manages domain name registrations.</li>
<li><strong>Top-level domain</strong> — the extension at the end of a domain name, such as .com or .zm.</li>
<li><strong>Country code domain</strong> — a domain extension assigned to a specific country, such as .zm for Zambia.</li>
<li><strong>Availability</strong> — whether a domain name is free to register or already taken.</li>
</ul>

<h2>Summary</h2>
<p>A domain name is your website's address. Choose one that is short, relevant, and easy to remember. Decide whether a global extension such as .com or a local extension such as .co.zm suits your audience. Register through a trusted registrar and keep your login details safe.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.icann.org/en/domain-names">ICANN — Domain Name Resources</a></li>
<li><a href="https://www.namecheap.com/domains/">Namecheap — Domain Search</a></li>
<li><a href="https://support.google.com/domains/answer/6301485">Google Domains Help — Choose a Domain Name</a></li>
</ul>
HTML,
            ],
            [
                'title' => '6.2 Cheap Hosting for Zambian Websites',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will understand what web hosting does, compare affordable hosting options suitable for small Zambian websites, and know what to look for in a hosting plan.</p>

<h2>What Is Web Hosting?</h2>
<p><strong>Web hosting</strong> is a service that stores your website files and makes them available on the internet. Think of it as renting space on a computer that is always connected to the internet. When someone types your domain into a browser, the hosting server sends your files to their screen.</p>

<h2>Types of Hosting</h2>
<p>There are several types of hosting. For a beginner or small business, two are most relevant:</p>
<ul>
<li><strong>Shared hosting</strong> — your site shares a server with many other sites. It is the cheapest option and is fine for small websites with moderate traffic.</li>
<li><strong>VPS hosting</strong> — you get a virtual private server with more control and power. It costs more and is better for larger or busier sites.</li>
</ul>
<p>Most learners and small businesses in Zambia should start with shared hosting. You can upgrade later if your site grows.</p>

<h2>What to Look For</h2>
<p>When comparing hosting plans, consider these factors:</p>
<ul>
<li><strong>Price</strong> — monthly or yearly cost in US dollars or Kwacha.</li>
<li><strong>Storage</strong> — how much space you have for files, images, and emails.</li>
<li><strong>Bandwidth</strong> — how much data can be transferred to visitors each month.</li>
<li><strong>Email accounts</strong> — whether you can create addresses such as info@yourdomain.com.</li>
<li><strong>SSL certificate</strong> — a security feature that gives your site HTTPS. Many hosts provide this free.</li>
<li><strong>Support</strong> — how quickly the host responds when you have problems.</li>
</ul>

<h2>Affordable Hosting Options</h2>
<p>Some well-known affordable hosts include:</p>
<ul>
<li><strong>Truehost</strong> — popular in Africa, accepts local payment methods, and offers .zm domains.</li>
<li><strong>Namecheap</strong> — low-cost shared hosting with good support.</li>
<li><strong>Hostinger</strong> — cheap introductory plans, but prices rise after the first term.</li>
<li><strong>InfinityFree</strong> — a free option for learning, but not suitable for a serious business.</li>
</ul>
<p>For learning, a free host is fine. For a real business, paying a small monthly fee gives you reliability and support.</p>

<h2>Worked Example: Comparing Plans</h2>
<p>Mr Banda wants to host a small shop website. He compares two shared hosting plans:</p>
<table>
<tr><th>Feature</th><th>Plan A</th><th>Plan B</th></tr>
<tr><td>Yearly price</td><td>K350</td><td>K600</td></tr>
<tr><td>Storage</td><td>10 GB</td><td>50 GB</td></tr>
<tr><td>Email accounts</td><td>5</td><td>Unlimited</td></tr>
<tr><td>SSL certificate</td><td>Free</td><td>Free</td></tr>
<tr><td>Support</td><td>Email only</td><td>Chat and email</td></tr>
</table>
<p>Plan A is cheaper and enough for a small site. Plan B offers more storage and support. Mr Banda chooses Plan A because his site is small and he can upgrade later.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Search online for "cheap web hosting Zambia" or "shared hosting Africa."</li>
<li>Find two hosting providers and compare their cheapest plans.</li>
<li>Note the price, storage, bandwidth, and SSL certificate options.</li>
<li>Decide which plan would suit a small business website with fewer than 100 visitors per day.</li>
<li>Write a short paragraph explaining your choice.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Web hosting</strong> — a service that stores website files and serves them over the internet.</li>
<li><strong>Shared hosting</strong> — a low-cost hosting plan where many websites share one server.</li>
<li><strong>Bandwidth</strong> — the amount of data transferred between the server and visitors.</li>
<li><strong>SSL certificate</strong> — a security feature that encrypts data and enables HTTPS.</li>
<li><strong>Uptime</strong> — the percentage of time a hosting server is online and available.</li>
</ul>

<h2>Summary</h2>
<p>Web hosting makes your site visible on the internet. Shared hosting is affordable and suitable for most small websites. When choosing a host, compare price, storage, bandwidth, email accounts, SSL, and support. Start small, and upgrade when your site grows.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.namecheap.com/hosting/shared/">Namecheap — Shared Hosting</a></li>
<li><a href="https://www.hostinger.com/web-hosting">Hostinger — Web Hosting</a></li>
<li><a href="https://support.google.com/webmasters/answer/63429">Google Search Central — Web Hosting</a></li>
</ul>
HTML,
            ],
            [
                'title' => '6.3 Uploading Your Site and Going Live',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to upload website files to a hosting server, connect a domain name to a hosting account, test the live site, and fix common problems that appear after launch.</p>

<h2>How Files Reach the Server</h2>
<p>When you build a website on your computer, only you can see it. To share it with the world, you must copy the files to your hosting server. The most common ways to do this are:</p>
<ul>
<li><strong>File Manager</strong> — a tool in your hosting control panel that lets you upload files through the browser.</li>
<li><strong>FTP</strong> — File Transfer Protocol; a way to move files using an FTP program such as FileZilla.</li>
<li><strong>Git</strong> — a version control system that can deploy files automatically.</li>
</ul>
<p>For beginners, the File Manager is usually the easiest place to start.</p>

<h2>Uploading with a File Manager</h2>
<ol>
<li>Log in to your hosting control panel.</li>
<li>Open the File Manager.</li>
<li>Find the public folder, often called <code>public_html</code> or <code>htdocs</code>.</li>
<li>Upload your HTML, CSS, JavaScript, and image files into that folder.</li>
<li>Make sure your main page is named <code>index.html</code>.</li>
</ol>

<h2>Connecting Domain to Hosting</h2>
<p>After uploading files, you must point your domain to your hosting server. This is done using <strong>nameservers</strong>. Your host will give you two or more nameserver addresses, such as:</p>
<pre><code>ns1.examplehost.com
ns2.examplehost.com
</code></pre>
<p>Log in to your domain registrar, find the nameserver settings, and replace the default nameservers with the ones from your host. Changes can take a few minutes to 48 hours to spread across the internet.</p>

<h2>Testing the Live Site</h2>
<p>Once the domain is connected, open your site in a browser. Check that:</p>
<ul>
<li>The home page loads at your domain name.</li>
<li>All links work.</li>
<li>Images appear.</li>
<li>The site looks good on a phone.</li>
<li>There are no broken pages.</li>
</ul>

<h2>Worked Example: Launching a Small Business Site</h2>
<p>Mrs Lungu has built a one-page site for her tailor shop. She registers <code>lungutailors.co.zm</code>, buys shared hosting, and uploads her files. She sets the nameservers at her registrar. After two hours, her site loads. She tests it on her phone and notices the contact form is too wide. She edits the CSS, uploads the new file, and refreshes the page. The site now looks correct.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a simple index.html file with your name and a short description.</li>
<li>Sign up for a free hosting account for practice if you do not have paid hosting.</li>
<li>Use the File Manager to upload your index.html file.</li>
<li>Open the provided temporary URL or your domain to see the live page.</li>
<li>Make a small change to the file, upload it again, and refresh the browser.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>File Manager</strong> — a browser-based tool for uploading and managing files on a server.</li>
<li><strong>FTP</strong> — a protocol for transferring files between your computer and a server.</li>
<li><strong>Nameserver</strong> — a server that tells browsers where to find your website.</li>
<li><strong>public_html</strong> — the public folder on many shared hosting accounts.</li>
<li><strong>Propagation</strong> — the time it takes for domain changes to spread across the internet.</li>
</ul>

<h2>Summary</h2>
<p>Going live means uploading files, pointing your domain to your host, and testing everything. Start with the File Manager, set your nameservers correctly, and be patient while changes propagate. Always test your site on both desktop and mobile after launch.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/domains/answer/3290309">Google Domains Help — Connect Your Domain</a></li>
<li><a href="https://www.w3schools.com/howto/howto_website_upload.asp">W3Schools — How to Upload a Website</a></li>
<li><a href="https://filezilla-project.org/">FileZilla — Free FTP Software</a></li>
</ul>
HTML,
            ],
            [
                'title' => '6.4 Google Search Console and Indexing',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will understand how Google finds websites, be able to add your site to Google Search Console, submit a sitemap, and use basic tools to monitor how your site appears in search results.</p>

<h2>How Google Finds Your Site</h2>
<p>Google uses small programs called <strong>crawlers</strong> or <strong>spiders</strong> to explore the web. They follow links from page to page and store information in Google's <strong>index</strong>. When someone searches, Google looks in its index and shows the most relevant pages. If your site is not indexed, it will not appear in search results.</p>

<h2>What Is Google Search Console?</h2>
<p><strong>Google Search Console</strong> is a free tool from Google. It lets you tell Google about your site, check whether your pages are indexed, see what search terms people use to find you, and fix errors. Every website owner should use it.</p>

<h2>Adding Your Site</h2>
<ol>
<li>Go to <a href="https://search.google.com/search-console">Google Search Console</a> and sign in with a Google account.</li>
<li>Click "Add property" and enter your domain name, such as <code>lungutailors.co.zm</code>.</li>
<li>Verify ownership. The easiest method is often adding a DNS record or uploading a small HTML file to your site.</li>
<li>Once verified, you can see your site's data.</li>
</ol>

<h2>Submitting a Sitemap</h2>
<p>A <strong>sitemap</strong> is a file that lists all the important pages on your site. It helps Google find and index your content faster. A simple sitemap looks like this:</p>
<pre><code>&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"&gt;
    &lt;url&gt;
        &lt;loc&gt;https://lungutailors.co.zm/&lt;/loc&gt;
    &lt;/url&gt;
    &lt;url&gt;
        &lt;loc&gt;https://lungutailors.co.zm/services.html&lt;/loc&gt;
    &lt;/url&gt;
&lt;/urlset&gt;
</code></pre>
<p>Save this as <code>sitemap.xml</code>, upload it to your site, and submit the URL in Google Search Console.</p>

<h2>Basic SEO Tips</h2>
<p><strong>SEO</strong>, or Search Engine Optimisation, means improving your site so it appears higher in search results. Simple steps include:</p>
<ul>
<li>Use clear, descriptive page titles.</li>
<li>Write helpful content that answers real questions.</li>
<li>Add headings that describe each section.</li>
<li>Make sure your site loads quickly.</li>
<li>Ensure your site works well on mobile phones.</li>
</ul>

<h2>Worked Example: Checking Index Status</h2>
<p>Mr Phiri launches a site for his hardware shop. After two weeks, he opens Google Search Console and sees that only his home page is indexed. He submits a sitemap with links to his products and contact pages. A few days later, more pages appear in the index. He also notices that people find his site by searching for "hardware shop Kalomo," which tells him his local keywords are working.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a free Google account if you do not have one.</li>
<li>Visit Google Search Console and add a property for your practice domain or subdomain.</li>
<li>Verify ownership using the method your host supports.</li>
<li>Create a simple sitemap.xml file for your site.</li>
<li>Submit the sitemap and check back after a few days to see if any pages are indexed.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Crawler</strong> — a program that explores web pages and follows links.</li>
<li><strong>Index</strong> — Google's database of web pages used to answer search queries.</li>
<li><strong>Google Search Console</strong> — a free Google tool for monitoring a site's search performance.</li>
<li><strong>Sitemap</strong> — a file that lists the pages of a website to help search engines find them.</li>
<li><strong>SEO</strong> — practices that help a website appear higher in search engine results.</li>
</ul>

<h2>Summary</h2>
<p>Google needs to know your site exists before it can show it in search results. Google Search Console is the free tool that connects you to Google's index. Verify your site, submit a sitemap, and follow basic SEO practices. Over time, your site can attract visitors who are searching for exactly what you offer.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://search.google.com/search-console/about">Google Search Console</a></li>
<li><a href="https://developers.google.com/search/docs/fundamentals/seo-starter-guide">Google SEO Starter Guide</a></li>
<li><a href="https://support.google.com/webmasters/answer/7451184">Google Help — Add a Site to Search Console</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module6Quiz(): array
    {
        return [
            'title' => 'Launching Your Website Quiz',
            'description' => 'Test your understanding of domains, hosting, uploading files, and getting indexed by Google.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main purpose of a domain name?',
                    'explanation' => 'A domain name is the human-readable address people use to find a website.',
                    'options' => [
                        ['text' => 'To store website files', 'is_correct' => false],
                        ['text' => 'To make a website easy to remember and find', 'is_correct' => true],
                        ['text' => 'To protect a site from hackers', 'is_correct' => false],
                        ['text' => 'To send emails automatically', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which folder is commonly used as the public folder on shared hosting?',
                    'explanation' => 'public_html is the standard public folder on many shared hosting accounts.',
                    'options' => [
                        ['text' => 'private_files', 'is_correct' => false],
                        ['text' => 'public_html', 'is_correct' => true],
                        ['text' => 'documents', 'is_correct' => false],
                        ['text' => 'my_website', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does SSL stand for in web hosting?',
                    'explanation' => 'SSL stands for Secure Sockets Layer and enables HTTPS encryption.',
                    'options' => [
                        ['text' => 'Simple Site Loader', 'is_correct' => false],
                        ['text' => 'Secure Sockets Layer', 'is_correct' => true],
                        ['text' => 'Shared Server Link', 'is_correct' => false],
                        ['text' => 'Static Storage Location', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which tool should you use to tell Google about your website and submit a sitemap?',
                    'explanation' => 'Google Search Console is the free tool for submitting sitemaps and monitoring indexing.',
                    'options' => [
                        ['text' => 'Google Docs', 'is_correct' => false],
                        ['text' => 'Google Search Console', 'is_correct' => true],
                        ['text' => 'Google Maps', 'is_correct' => false],
                        ['text' => 'Google Drive', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A .zm domain extension is the country code top-level domain for Zambia.',
                    'explanation' => '.zm is the country code top-level domain assigned to Zambia.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'After changing nameservers, the new settings take effect instantly everywhere.',
                    'explanation' => 'DNS changes can take minutes to 48 hours to propagate worldwide.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the standard filename for the main page of a website? (one word)',
                    'explanation' => 'index.html is the default page most servers show when a directory is requested.',
                    'correct_answer' => 'index.html',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What file lists important pages on a site to help search engines crawl it? (one word)',
                    'explanation' => 'A sitemap lists pages to help search engines find and index content.',
                    'correct_answer' => 'sitemap',
                ],
            ],
        ];
    }

    // -----------------------------------------------------------------
    // Missing quizzes for existing modules
    // -----------------------------------------------------------------

    private function seedMissingQuizzes(): void
    {
        // Module 2 (CSS3 Styling) already has lessons but no quiz in the base seed.
        $module = Module::where('course_id', $this->courseId)->where('title', 'CSS3 Styling')->first();

        if (! $module) {
            $this->command->warn('CSS3 Styling module not found; skipping its quiz.');

            return;
        }

        $lessonIds = $module->lessons()->orderBy('display_order')->pluck('id')->toArray();

        if (empty($lessonIds)) {
            $this->command->warn('CSS3 Styling module has no lessons; skipping its quiz.');

            return;
        }

        $this->createQuiz($module, $this->module2Quiz(), $lessonIds);
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'CSS3 Styling Quiz',
            'description' => 'Test your understanding of CSS selectors, the box model, Flexbox, and responsive media queries.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which CSS property changes the colour of text?',
                    'explanation' => 'The color property sets the foreground colour of text.',
                    'options' => [
                        ['text' => 'background-color', 'is_correct' => false],
                        ['text' => 'color', 'is_correct' => true],
                        ['text' => 'font-style', 'is_correct' => false],
                        ['text' => 'text-align', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In the CSS box model, which property adds space between the content and the border?',
                    'explanation' => 'Padding is the space between the content and the border.',
                    'options' => [
                        ['text' => 'Margin', 'is_correct' => false],
                        ['text' => 'Padding', 'is_correct' => true],
                        ['text' => 'Border', 'is_correct' => false],
                        ['text' => 'Width', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which display value is used to make a container flexible along one axis?',
                    'explanation' => 'display: flex turns an element into a flex container.',
                    'options' => [
                        ['text' => 'block', 'is_correct' => false],
                        ['text' => 'inline', 'is_correct' => false],
                        ['text' => 'flex', 'is_correct' => true],
                        ['text' => 'grid', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which symbol is used to select an element by its id in CSS?',
                    'explanation' => 'The hash symbol # selects an element by its id.',
                    'options' => [
                        ['text' => '.', 'is_correct' => false],
                        ['text' => '#', 'is_correct' => true],
                        ['text' => '*', 'is_correct' => false],
                        ['text' => '$', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which CSS rule applies styles only when the viewport is at least 768 pixels wide?',
                    'explanation' => '@media (min-width: 768px) targets viewports of 768 pixels and wider.',
                    'options' => [
                        ['text' => '@media (max-width: 768px)', 'is_correct' => false],
                        ['text' => '@media (min-width: 768px)', 'is_correct' => true],
                        ['text' => '@screen (min-width: 768px)', 'is_correct' => false],
                        ['text' => '@device (min-width: 768px)', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'An external stylesheet is linked to HTML using the &lt;link&gt; tag in the head section.',
                    'explanation' => 'The &lt;link rel="stylesheet" href="..."&gt; tag links an external CSS file.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Margin is the space outside an element\'s border.',
                    'explanation' => 'Margin creates space outside the border, separating the element from others.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'Which CSS property sets the size of text? (one word)',
                    'explanation' => 'The font-size property controls the size of text.',
                    'correct_answer' => 'font-size',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'Which CSS layout method arranges items in rows and columns together? (one word)',
                    'explanation' => 'CSS Grid arranges items in both rows and columns.',
                    'correct_answer' => 'Grid',
                ],
            ],
        ];
    }

    // -----------------------------------------------------------------
    // Assignments
    // -----------------------------------------------------------------

    private function seedAssignments(): void
    {
        $this->createAssignment([
            'title' => 'One-Page Website for a Local Business',
            'description' => 'Build a complete one-page website for a real or imaginary local business in Zambia.',
            'instructions' => <<<'INSTR'
Create a one-page website for a local business such as a shop, farm, tailoring service, or transport service in Zambia.

Requirements:
1. Create an HTML file named index.html.
2. Include a clear header with the business name and a short tagline.
3. Add at least three sections: About the Business, Services or Products, and Contact Details.
4. Use CSS to style the page. You may write custom CSS or use Bootstrap from a CDN.
5. Make the page responsive so it looks good on a phone and a desktop.
6. Add one small JavaScript feature, such as a click-to-show price, a simple form alert, or a mobile menu toggle.
7. Include real Zambian details such as prices in Kwacha, a local phone number format, and the town or area.

Submission:
- Submit a ZIP file containing your index.html file and any supporting files such as CSS, images, or JavaScript.
- Include a short README.txt explaining the business and which feature you added with JavaScript.
INSTR,
            'max_points' => 100,
            'passing_points' => 50,
            'allow_late_submission' => 1,
            'max_file_size_mb' => 10,
            'allowed_file_types' => 'zip,pdf,doc,docx,jpg,png',
        ]);
    }

    // -----------------------------------------------------------------
    // Summary
    // -----------------------------------------------------------------

    private function printSummary(): void
    {
        $this->command->newLine();
        $this->command->info('=== Web Development Content Seeder Summary ===');
        $this->command->info('Modules created: '.$this->counts['modules_created']);
        $this->command->info('Existing modules reused: '.$this->counts['modules_reused']);
        $this->command->info('Lessons created: '.$this->counts['lessons_created']);
        $this->command->info('Lessons skipped (already exist): '.$this->counts['lessons_skipped']);
        $this->command->info('Quizzes created: '.$this->counts['quizzes_created']);
        $this->command->info('Quizzes skipped (already exist): '.$this->counts['quizzes_skipped']);
        $this->command->info('Questions created: '.$this->counts['questions_created']);
        $this->command->info('Assignments created: '.$this->counts['assignments_created']);
        $this->command->info('Assignments skipped (already exist): '.$this->counts['assignments_skipped']);
        $this->command->newLine();
        $this->command->info('Seeder finished.');
    }
}
