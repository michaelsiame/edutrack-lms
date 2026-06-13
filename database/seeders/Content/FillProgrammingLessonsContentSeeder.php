<?php

namespace Database\Seeders\Content;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Fills the legacy empty lessons of the Python Programming (course 5) and
 * Web Development (course 7) courses with real lesson notes, by lesson id.
 * Idempotent: only writes where the current content is < 200 chars, and
 * converts the no-video placeholder Video lessons to Reading.
 */
class FillProgrammingLessonsContentSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $lessons = $this->lessons();
        $filled = 0;
        $skipped = 0;

        DB::transaction(function () use ($lessons, &$filled, &$skipped) {
            foreach ($lessons as $id => $html) {
                $row = DB::table('lessons')->where('id', $id)->first();
                if (!$row) {
                    $this->command->warn("Lesson {$id} not found, skipping.");
                    continue;
                }
                if (strlen((string) $row->content) >= 200) {
                    $skipped++;
                    continue;
                }
                DB::table('lessons')->where('id', $id)->update([
                    'content' => trim($html),
                    'lesson_type' => $row->lesson_type === 'Video' ? 'Reading' : $row->lesson_type,
                    'updated_at' => now(),
                ]);
                $filled++;
            }
        });

        $this->command->info("Programming lessons content: {$filled} filled, {$skipped} already had content.");
    }

    private function lessons(): array
    {
        return [
            // ===================== PYTHON (course 5) =====================
            1 => <<<'HTML'
<h2>Welcome to Python Programming</h2>
<p>By the end of this lesson you will understand what Python is, why it is one of the best first languages to learn, and the kinds of real jobs and projects it opens up here in Zambia and beyond.</p>
<h3>What is Python?</h3>
<p>Python is a programming language: a way of writing instructions that a computer follows. It was designed to be easy to read, almost like plain English, which is exactly why colleges and companies use it to teach beginners. When you write <code>print("Hello")</code> the computer simply prints the word Hello. No complicated symbols, no fuss.</p>
<p>Python is used everywhere: by banks to process mobile-money transactions, by data analysts studying maize yields, by websites, by scientists, and by students automating boring tasks like renaming hundreds of files. If a computer can do it, there is a good chance Python can tell it how.</p>
<h3>Why learn Python first?</h3>
<ul>
<li><strong>It is readable.</strong> You spend your energy solving the problem, not fighting the language.</li>
<li><strong>It is in demand.</strong> Python skills appear in many Zambian job adverts for data, finance and software roles, and on freelancing sites like Upwork.</li>
<li><strong>It is free.</strong> Everything you need runs on a modest computer and costs nothing.</li>
<li><strong>It grows with you.</strong> The same language takes you from your first <code>print</code> to building real applications.</li>
</ul>
<h3>A taste of what you will write</h3>
<p>Here is a tiny program that works out the change from a K100 note after buying airtime:</p>
<pre><code>price = 25
paid = 100
change = paid - price
print("Your change is K", change)</code></pre>
<p>That is real Python. By the middle of this course you will write programs far more useful than this one.</p>
<h3>Try It Yourself</h3>
<ol>
<li>Write down three tasks you do on a phone or computer that feel repetitive.</li>
<li>Beside each, imagine a small program doing it for you. Keep this list; you may build one of them by the end of the course.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>Programming language</strong> - a set of words and rules for giving a computer instructions.</li>
<li><strong>Code</strong> - the instructions you write.</li>
<li><strong>Program</strong> - a finished set of instructions that does a task.</li>
<li><strong>Print</strong> - the Python instruction that shows text on the screen.</li>
</ul>
<h3>Summary</h3>
<p>Python is a readable, free, widely-used language and an excellent first step into software. In the next lessons you will install it and run your very first program.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://www.python.org/about/gettingstarted/">python.org - Getting Started</a></li>
<li><a href="https://www.freecodecamp.org/news/the-python-guide-for-beginners/">freeCodeCamp - Python guide for beginners</a></li>
<li><a href="https://www.w3schools.com/python/python_intro.asp">W3Schools - Python Introduction</a></li>
</ul>
HTML,

            2 => <<<'HTML'
<h2>Installing Python and IDE Setup</h2>
<p>By the end of this lesson you will have Python installed on a computer and a place to write code, and you will be able to confirm that everything works.</p>
<h3>Step 1: Download Python</h3>
<p>Go to <a href="https://www.python.org/downloads/">python.org/downloads</a> and download the latest version for your system (Windows is most common in our labs). Run the installer.</p>
<p><strong>Very important on Windows:</strong> on the first screen of the installer, tick the box that says <em>"Add Python to PATH"</em> before clicking Install. This one tick saves you many headaches later.</p>
<h3>Step 2: Confirm it installed</h3>
<p>Open Command Prompt (search "cmd" in the Start menu) and type:</p>
<pre><code>python --version</code></pre>
<p>If you see something like <code>Python 3.12.0</code>, you are ready. If it says "not recognised", Python was likely installed without the PATH tick - re-run the installer and tick it.</p>
<h3>Step 3: Choose where to write code (an IDE or editor)</h3>
<p>An IDE (Integrated Development Environment) is a program for writing and running code comfortably. Two good free choices:</p>
<ul>
<li><strong>IDLE</strong> - comes free with Python. Open it from the Start menu. Perfect for beginners; nothing to install.</li>
<li><strong>VS Code</strong> - a powerful free editor from Microsoft. Install the "Python" extension after installing VS Code. This is what many professionals use.</li>
</ul>
<p>For this course, IDLE is enough to start. Move to VS Code when you feel ready.</p>
<h3>Try It Yourself</h3>
<ol>
<li>Open IDLE. You will see a window with <code>&gt;&gt;&gt;</code> - this is the Python shell.</li>
<li>Type <code>print("I installed Python")</code> and press Enter.</li>
<li>If your message appears, congratulations - your setup works.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>Install</strong> - to put a program onto your computer.</li>
<li><strong>PATH</strong> - a setting that lets you run Python from anywhere on Windows.</li>
<li><strong>IDE</strong> - a program for writing, running and fixing code.</li>
<li><strong>IDLE</strong> - the simple editor that ships with Python.</li>
<li><strong>Shell</strong> - the window where you can type Python one line at a time.</li>
</ul>
<h3>Summary</h3>
<p>Download Python from python.org, tick "Add to PATH", confirm with <code>python --version</code>, and write code in IDLE or VS Code. With your tools ready, the next lesson is your first real program.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://www.python.org/downloads/">python.org - Downloads</a></li>
<li><a href="https://code.visualstudio.com/docs/python/python-tutorial">VS Code - Python tutorial</a></li>
</ul>
HTML,

            3 => <<<'HTML'
<h2>Your First Python Program</h2>
<p>By the end of this lesson you will write, save and run a complete Python program, and understand exactly what each line does.</p>
<h3>The classic first program</h3>
<p>Every programmer starts here. In IDLE, click File &gt; New File, then type:</p>
<pre><code>print("Hello, Zambia!")</code></pre>
<p>Save it as <code>hello.py</code> (the <code>.py</code> ending tells the computer it is Python). Then press F5 to run it. The words appear in the shell. You just ran a program.</p>
<h3>Making it do something useful</h3>
<p>Programs become interesting when they take input. The <code>input()</code> function asks the user a question:</p>
<pre><code>name = input("What is your name? ")
print("Welcome to the course,", name)</code></pre>
<p>Run it, type your name, press Enter, and the program greets you. Here <code>name</code> is a <strong>variable</strong> - a labelled box that stores a value so you can use it later.</p>
<h3>A small real example</h3>
<pre><code>fee = input("Enter the course fee in Kwacha: ")
fee = int(fee)
deposit = fee * 0.30
print("Your 30% deposit is K", deposit)</code></pre>
<p>This asks for a fee, converts the text to a number with <code>int()</code>, works out a 30% deposit, and prints it - the same deposit rule Edutrack uses for course payments.</p>
<h3>Try It Yourself</h3>
<ol>
<li>Write a program that asks for the user's town and prints "Greetings from " followed by the town.</li>
<li>Change the deposit example to print a 50% deposit instead of 30%.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>print()</strong> - shows text on the screen.</li>
<li><strong>input()</strong> - asks the user to type something.</li>
<li><strong>Variable</strong> - a named store for a value.</li>
<li><strong>int()</strong> - converts text into a whole number.</li>
<li><strong>.py</strong> - the file ending for Python programs.</li>
</ul>
<h3>Summary</h3>
<p>You wrote a program that prints, takes input, stores it in a variable, and does a calculation. Those four ideas are the foundation of everything ahead.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://www.w3schools.com/python/python_getstarted.asp">W3Schools - Python Get Started</a></li>
<li><a href="https://www.freecodecamp.org/news/python-print-function/">freeCodeCamp - The print function</a></li>
</ul>
HTML,

            4 => <<<'HTML'
<h2>Python Syntax Basics</h2>
<p>By the end of this lesson you will understand Python's basic rules of writing - indentation, comments, and variables - so your programs run without errors.</p>
<h3>Variables hold values</h3>
<p>A variable is created the moment you give it a value with <code>=</code>:</p>
<pre><code>shop_name = "Kalomo Hardware"
items_in_stock = 240
price_per_item = 15.50</code></pre>
<p>Names should be lowercase and descriptive. Use underscores between words (<code>price_per_item</code>), never spaces.</p>
<h3>Comments explain your code</h3>
<p>Anything after a <code>#</code> is a comment - Python ignores it. Comments remind you (and others) what the code does:</p>
<pre><code># Work out the total value of stock
total_value = items_in_stock * price_per_item
print(total_value)</code></pre>
<h3>Indentation matters</h3>
<p>Unlike many languages, Python uses <strong>indentation</strong> (spaces at the start of a line) to group code. This is not decoration - it is part of the rules. You will see why in the lessons on if-statements and loops. For now, remember: lines that belong together must be indented the same amount, usually four spaces.</p>
<h3>Common beginner errors</h3>
<ul>
<li>Forgetting the closing quote: <code>print("Hello)</code> - Python complains.</li>
<li>Mixing tabs and spaces for indentation - pick spaces and stick to them.</li>
<li>Misspelling a variable name - <code>Total</code> and <code>total</code> are different to Python.</li>
</ul>
<h3>Try It Yourself</h3>
<ol>
<li>Create three variables for a market stall: product name, quantity, and price.</li>
<li>Add a comment above each line saying what it stores.</li>
<li>Print the total value (quantity times price).</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>Syntax</strong> - the grammar rules of a language.</li>
<li><strong>Comment</strong> - a note in the code, ignored when running.</li>
<li><strong>Indentation</strong> - leading spaces that group lines together.</li>
<li><strong>Assignment</strong> - giving a variable a value using <code>=</code>.</li>
</ul>
<h3>Summary</h3>
<p>Use clear variable names, comment your intentions, and keep indentation consistent. Get these habits right now and the rest of Python feels natural.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://www.w3schools.com/python/python_syntax.asp">W3Schools - Python Syntax</a></li>
<li><a href="https://docs.python.org/3/tutorial/introduction.html">python.org - An Informal Introduction</a></li>
</ul>
HTML,

            5 => <<<'HTML'
<h2>Numbers in Python</h2>
<p>By the end of this lesson you will work confidently with whole numbers and decimals, and use Python as a powerful calculator for real money problems.</p>
<h3>Two kinds of numbers</h3>
<ul>
<li><strong>Integers</strong> (<code>int</code>) - whole numbers like <code>0</code>, <code>240</code>, <code>-5</code>.</li>
<li><strong>Floats</strong> (<code>float</code>) - numbers with a decimal point like <code>15.50</code>, <code>0.30</code>.</li>
</ul>
<h3>The arithmetic operators</h3>
<pre><code>a = 100
b = 30
print(a + b)   # 130  addition
print(a - b)   # 70   subtraction
print(a * b)   # 3000 multiplication
print(a / b)   # 3.33 division (always a float)
print(a % b)   # 10   remainder (modulo)
print(a ** 2)  # 10000 power</code></pre>
<h3>A worked money example</h3>
<p>Imagine a poultry business. Each chick costs K12, you buy 50, and feed costs K300:</p>
<pre><code>chicks = 50
cost_per_chick = 12
feed = 300
total_cost = chicks * cost_per_chick + feed
print("Total start-up cost: K", total_cost)   # K 900

selling_price = 85
revenue = chicks * selling_price
profit = revenue - total_cost
print("Expected profit: K", profit)            # K 3350</code></pre>
<h3>Rounding</h3>
<p>Money should usually show two decimals. Use <code>round()</code>:</p>
<pre><code>deposit = 4500 * 0.30
print(round(deposit, 2))   # 1350.0</code></pre>
<h3>Try It Yourself</h3>
<ol>
<li>A taxi charges K8 per kilometre plus a K15 base fare. Work out the fare for a 12 km trip.</li>
<li>Use <code>%</code> to check whether 2026 is a leap year hint: a year divisible by 4 has no remainder.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>int</strong> - a whole number.</li>
<li><strong>float</strong> - a number with decimals.</li>
<li><strong>Operator</strong> - a symbol that performs a calculation.</li>
<li><strong>Modulo (%)</strong> - gives the remainder of a division.</li>
<li><strong>round()</strong> - rounds a number to a set number of decimals.</li>
</ul>
<h3>Summary</h3>
<p>Python handles integers and floats with the familiar operators plus modulo and powers. You can now turn real costing problems into short, reliable calculations.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://www.w3schools.com/python/python_numbers.asp">W3Schools - Python Numbers</a></li>
<li><a href="https://docs.python.org/3/tutorial/introduction.html#numbers">python.org - Numbers</a></li>
</ul>
HTML,

            6 => <<<'HTML'
<h2>Strings and String Methods</h2>
<p>By the end of this lesson you will store and manipulate text - joining it, changing its case, and pulling out parts - which is essential for names, addresses and messages.</p>
<h3>What is a string?</h3>
<p>A string is text wrapped in quotes. Single or double quotes both work:</p>
<pre><code>first = "Chanda"
last = 'Mwale'</code></pre>
<h3>Joining and formatting</h3>
<p>The cleanest way to build a message is an <strong>f-string</strong> (note the <code>f</code> before the quote):</p>
<pre><code>full = f"{first} {last}"
print(full)                       # Chanda Mwale
amount = 150
print(f"Dear {first}, your fee is K{amount}.")</code></pre>
<h3>Useful string methods</h3>
<pre><code>name = "  chanda mwale  "
print(name.strip())        # removes spaces -> "chanda mwale"
print(name.title())        # "  Chanda Mwale  "
print(name.upper())        # "  CHANDA MWALE  "
print(len(name.strip()))   # 12  (number of characters)
print("mwale" in name)     # True</code></pre>
<h3>Picking out parts (slicing)</h3>
<pre><code>phone = "0977123456"
print(phone[:4])     # "0977"  the network prefix
print(phone[-3:])    # "456"   last three digits</code></pre>
<h3>Try It Yourself</h3>
<ol>
<li>Ask the user for their full name, then print it in Title Case with <code>.title()</code>.</li>
<li>From a phone number string, print just the first four characters.</li>
<li>Build an f-string that says "Hello NAME, welcome to Edutrack" using their input.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>String</strong> - text in quotes.</li>
<li><strong>f-string</strong> - a string with <code>{variables}</code> inserted, prefixed with f.</li>
<li><strong>Method</strong> - an action you can run on a value, e.g. <code>.upper()</code>.</li>
<li><strong>Slicing</strong> - taking a section of a string with <code>[start:end]</code>.</li>
<li><strong>len()</strong> - counts the characters.</li>
</ul>
<h3>Summary</h3>
<p>Strings hold text, f-strings build readable messages, and methods like <code>strip</code>, <code>title</code> and slicing let you clean and reshape it - exactly what you need for forms and receipts.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://www.w3schools.com/python/python_strings.asp">W3Schools - Python Strings</a></li>
<li><a href="https://docs.python.org/3/tutorial/introduction.html#text">python.org - Text</a></li>
</ul>
HTML,

            7 => <<<'HTML'
<h2>Lists and Tuples</h2>
<p>By the end of this lesson you will store many values in one variable using lists, add and remove items, and know when to use a tuple instead.</p>
<h3>A list holds many values</h3>
<pre><code>stock = ["sugar", "mealie meal", "cooking oil", "salt"]
print(stock[0])      # sugar  (counting starts at 0)
print(stock[-1])     # salt   (last item)
print(len(stock))    # 4</code></pre>
<h3>Changing a list</h3>
<pre><code>stock.append("bread")     # add to the end
stock.remove("salt")      # remove by value
stock[0] = "brown sugar"  # change an item
print(stock)              # ['brown sugar', 'mealie meal', 'cooking oil', 'bread']</code></pre>
<h3>Looping over a list (a preview)</h3>
<pre><code>prices = [25, 90, 60, 18]
print("Total stock value: K", sum(prices))   # 193
print("Most expensive: K", max(prices))       # 90</code></pre>
<p><code>sum()</code>, <code>max()</code> and <code>min()</code> work straight on lists of numbers - handy for sales totals.</p>
<h3>Tuples: lists that cannot change</h3>
<p>A tuple uses round brackets and is <strong>fixed</strong> once created. Use it for values that should never change, like coordinates or a fixed pair:</p>
<pre><code>location = (-16.97, 26.48)   # latitude, longitude of Kalomo
print(location[0])           # -16.97
# location[0] = 0  would cause an error - tuples are read-only</code></pre>
<h3>Try It Yourself</h3>
<ol>
<li>Make a list of five subjects you study. Print the first and last.</li>
<li>Append a new subject, then print how many subjects there are.</li>
<li>Make a list of their marks and print the average using <code>sum(marks)/len(marks)</code>.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>List</strong> - an ordered, changeable collection in square brackets.</li>
<li><strong>Index</strong> - an item's position, starting at 0.</li>
<li><strong>append() / remove()</strong> - add or delete list items.</li>
<li><strong>Tuple</strong> - an ordered collection in round brackets that cannot change.</li>
</ul>
<h3>Summary</h3>
<p>Lists store and manage many values and pair beautifully with <code>sum</code>, <code>max</code> and <code>min</code>. Tuples are their fixed cousins for data that must stay constant.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://www.w3schools.com/python/python_lists.asp">W3Schools - Python Lists</a></li>
<li><a href="https://docs.python.org/3/tutorial/datastructures.html">python.org - Data Structures</a></li>
</ul>
HTML,

            8 => <<<'HTML'
<h2>Dictionaries and Sets</h2>
<p>By the end of this lesson you will store labelled data with dictionaries (key and value pairs) and use sets to handle unique items.</p>
<h3>A dictionary stores pairs</h3>
<p>Where a list uses numbered positions, a dictionary uses meaningful labels called <strong>keys</strong>:</p>
<pre><code>student = {
    "name": "Natasha Banda",
    "age": 19,
    "course": "Python Programming",
    "fee_paid": True
}
print(student["name"])     # Natasha Banda
print(student["course"])   # Python Programming</code></pre>
<h3>Adding and changing entries</h3>
<pre><code>student["town"] = "Kalomo"     # add a new pair
student["age"] = 20            # change a value
print(student.keys())          # all the labels
print(student.values())        # all the values</code></pre>
<h3>A practical example: a price list</h3>
<pre><code>prices = {"sugar": 25, "bread": 18, "oil": 90}
item = input("Which item? ")
print(f"{item} costs K{prices[item]}")</code></pre>
<h3>Sets: collections with no duplicates</h3>
<p>A set automatically removes repeats - useful for counting unique things:</p>
<pre><code>towns = ["Kalomo", "Lusaka", "Kalomo", "Choma", "Lusaka"]
unique = set(towns)
print(unique)        # {'Kalomo', 'Lusaka', 'Choma'}
print(len(unique))   # 3 distinct towns</code></pre>
<h3>Try It Yourself</h3>
<ol>
<li>Build a dictionary describing yourself: name, age, town, favourite subject.</li>
<li>Add a new key for your phone network, then print all the keys.</li>
<li>Make a list with repeated items and use <code>set()</code> to count the unique ones.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>Dictionary</strong> - a collection of key:value pairs in curly braces.</li>
<li><strong>Key</strong> - the label used to look up a value.</li>
<li><strong>Value</strong> - the data stored under a key.</li>
<li><strong>Set</strong> - a collection that keeps only unique values.</li>
</ul>
<h3>Summary</h3>
<p>Dictionaries give your data meaningful labels, making programs easier to read, while sets keep collections free of duplicates. Together they round out Python's core data tools.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://www.w3schools.com/python/python_dictionaries.asp">W3Schools - Python Dictionaries</a></li>
<li><a href="https://docs.python.org/3/tutorial/datastructures.html#dictionaries">python.org - Dictionaries</a></li>
</ul>
HTML,

            9 => <<<'HTML'
<h2>If-Else Statements</h2>
<p>By the end of this lesson you will make your programs take decisions - doing one thing when a condition is true and another when it is false.</p>
<h3>The idea of a condition</h3>
<p>A condition is a question with a yes/no answer. Python checks it and chooses a path. Note the colon and the indentation:</p>
<pre><code>deposit = 1500
fee = 4500

if deposit &gt;= fee * 0.30:
    print("Access granted - you have paid at least 30%.")
else:
    print("Please pay a larger deposit to access the course.")</code></pre>
<h3>Comparison operators</h3>
<ul>
<li><code>==</code> equal to (two equals signs - one is assignment)</li>
<li><code>!=</code> not equal to</li>
<li><code>&gt;</code> greater than, <code>&lt;</code> less than</li>
<li><code>&gt;=</code> greater than or equal, <code>&lt;=</code> less than or equal</li>
</ul>
<h3>Choosing between many options with elif</h3>
<pre><code>score = int(input("Enter the exam score: "))

if score &gt;= 75:
    print("Distinction")
elif score &gt;= 60:
    print("Merit")
elif score &gt;= 50:
    print("Pass")
else:
    print("Fail")</code></pre>
<p>Python checks each branch top to bottom and runs the first that is true - exactly how a grading classification works.</p>
<h3>Combining conditions</h3>
<pre><code>age = 20
has_nrc = True
if age &gt;= 18 and has_nrc:
    print("Eligible to register")</code></pre>
<p><code>and</code> needs both true; <code>or</code> needs at least one true; <code>not</code> flips a condition.</p>
<h3>Try It Yourself</h3>
<ol>
<li>Ask for a person's age and print "Adult" if 18 or over, otherwise "Minor".</li>
<li>Write the grading example and test it with scores 80, 55 and 30.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>Condition</strong> - an expression that is True or False.</li>
<li><strong>if / elif / else</strong> - the keywords that choose a path.</li>
<li><strong>Comparison operator</strong> - symbols like <code>==</code>, <code>&gt;=</code>.</li>
<li><strong>and / or / not</strong> - join or flip conditions.</li>
</ul>
<h3>Summary</h3>
<p>With if, elif and else your programs respond differently to different situations. This is the heart of useful software - from grading to payment gating.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://www.w3schools.com/python/python_conditions.asp">W3Schools - Python If...Else</a></li>
<li><a href="https://docs.python.org/3/tutorial/controlflow.html#if-statements">python.org - if Statements</a></li>
</ul>
HTML,

            10 => <<<'HTML'
<h2>For Loops</h2>
<p>By the end of this lesson you will repeat actions automatically with a for loop, instead of writing the same line many times.</p>
<h3>Looping over a list</h3>
<pre><code>students = ["Mwila", "Chanda", "Bwalya"]
for name in students:
    print("Marking register for", name)</code></pre>
<p>The loop runs once for each item, putting it in <code>name</code> each time. Note the colon and indentation - the indented line is what repeats.</p>
<h3>Looping a set number of times with range()</h3>
<pre><code>for i in range(5):
    print("Attendance day", i + 1)</code></pre>
<p><code>range(5)</code> gives the numbers 0,1,2,3,4. Adding 1 makes a friendly 1 to 5.</p>
<h3>A running total</h3>
<pre><code>sales = [120, 80, 200, 50]
total = 0
for amount in sales:
    total = total + amount
print("Total sales: K", total)   # 450</code></pre>
<p>This pattern - start a total at 0, add each item - is one you will use constantly.</p>
<h3>Looping over a dictionary</h3>
<pre><code>prices = {"sugar": 25, "bread": 18}
for item, price in prices.items():
    print(f"{item}: K{price}")</code></pre>
<h3>Try It Yourself</h3>
<ol>
<li>Loop over a list of five towns and print "Visited " before each.</li>
<li>Use <code>range(1, 13)</code> to print the 7 times table.</li>
<li>Given a list of daily sales, print the total and the average.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>Loop</strong> - code that repeats.</li>
<li><strong>for</strong> - runs once per item in a collection.</li>
<li><strong>range()</strong> - produces a sequence of numbers to loop over.</li>
<li><strong>Accumulator</strong> - a variable (like total) that builds up inside a loop.</li>
</ul>
<h3>Summary</h3>
<p>For loops let one short block of code handle a whole list, a number range, or a dictionary. The running-total pattern turns loops into real reporting tools.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://www.w3schools.com/python/python_for_loops.asp">W3Schools - Python For Loops</a></li>
<li><a href="https://docs.python.org/3/tutorial/controlflow.html#for-statements">python.org - for Statements</a></li>
</ul>
HTML,

            11 => <<<'HTML'
<h2>While Loops</h2>
<p>By the end of this lesson you will repeat actions until a condition changes, and avoid the common trap of a loop that never stops.</p>
<h3>How a while loop works</h3>
<p>A while loop keeps running <em>as long as</em> its condition stays true:</p>
<pre><code>count = 1
while count &lt;= 5:
    print("Ringing customer", count)
    count = count + 1</code></pre>
<p>Each time round, Python rechecks the condition. The line <code>count = count + 1</code> is vital - it moves the loop towards stopping.</p>
<h3>The infinite loop trap</h3>
<p>If the condition never becomes false, the loop runs forever and the program freezes. Always make sure something inside the loop changes the condition. If you ever get stuck, press Ctrl+C to stop it.</p>
<h3>Looping until the user is done</h3>
<pre><code>total = 0
while True:
    entry = input("Enter a sale amount (or 'done'): ")
    if entry == "done":
        break
    total = total + int(entry)
print("Total takings: K", total)</code></pre>
<p>Here <code>break</code> jumps out of the loop when the user types "done" - perfect when you do not know in advance how many entries there will be.</p>
<h3>While vs for</h3>
<ul>
<li>Use a <strong>for</strong> loop when you know the items or count (a list, a range).</li>
<li>Use a <strong>while</strong> loop when you repeat until something happens (user quits, balance reaches zero).</li>
</ul>
<h3>Try It Yourself</h3>
<ol>
<li>Print "Loading..." five times using a while loop and a counter.</li>
<li>Write a mobile-money style PIN check: keep asking for a PIN until the user enters 1234, then print "Access granted".</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>while</strong> - repeats while a condition is true.</li>
<li><strong>Infinite loop</strong> - a loop whose condition never becomes false.</li>
<li><strong>break</strong> - immediately exits a loop.</li>
<li><strong>Counter</strong> - a variable that tracks how many times a loop has run.</li>
</ul>
<h3>Summary</h3>
<p>While loops repeat until a condition changes, which suits open-ended tasks like menus and PIN checks. Always change the condition (or use break) so the loop can end.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://www.w3schools.com/python/python_while_loops.asp">W3Schools - Python While Loops</a></li>
<li><a href="https://docs.python.org/3/reference/compound_stmts.html#while">python.org - while statement</a></li>
</ul>
HTML,

            // ===================== WEB DEVELOPMENT (course 7) =====================
            13 => <<<'HTML'
<h2>Introduction to HTML5</h2>
<p>By the end of this lesson you will understand what HTML is, how a web page is built from tags, and you will write your first heading and paragraph.</p>
<h3>What is HTML?</h3>
<p>HTML (HyperText Markup Language) is the language of web pages. It does not "program" anything - it <strong>describes structure</strong>: this is a heading, this is a paragraph, this is an image. Every website you visit, from a bank to a news site, is built on HTML.</p>
<h3>Tags and elements</h3>
<p>HTML is made of <strong>tags</strong> written in angle brackets. Most come in pairs - an opening tag and a closing tag with a slash:</p>
<pre><code>&lt;h1&gt;Kalomo Hardware&lt;/h1&gt;
&lt;p&gt;We sell tools, paint and building supplies.&lt;/p&gt;</code></pre>
<p>Here <code>&lt;h1&gt;</code> means "biggest heading" and <code>&lt;p&gt;</code> means "paragraph". The text between the tags is the content. A tag plus its content is called an <strong>element</strong>.</p>
<h3>You need no special software</h3>
<p>HTML is just text. Write it in Notepad (or VS Code), save the file with a <code>.html</code> ending, and open it in any web browser like Chrome. The browser reads your tags and shows the page.</p>
<h3>Your first page</h3>
<pre><code>&lt;h1&gt;Welcome to my page&lt;/h1&gt;
&lt;p&gt;My name is Chanda and I am learning web development.&lt;/p&gt;
&lt;h2&gt;About Kalomo&lt;/h2&gt;
&lt;p&gt;Kalomo is a town in the Southern Province of Zambia.&lt;/p&gt;</code></pre>
<p>Save as <code>index.html</code> and double-click it - your page opens in the browser.</p>
<h3>Try It Yourself</h3>
<ol>
<li>Create <code>index.html</code> with one <code>h1</code> title and two paragraphs about yourself.</li>
<li>Add an <code>h2</code> subheading and a paragraph under it.</li>
<li>Open it in a browser and refresh after each change.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>HTML</strong> - the markup language that structures web pages.</li>
<li><strong>Tag</strong> - an instruction in angle brackets, usually in a pair.</li>
<li><strong>Element</strong> - an opening tag, content, and closing tag together.</li>
<li><strong>Browser</strong> - the program that reads HTML and displays the page.</li>
</ul>
<h3>Summary</h3>
<p>HTML describes the structure of a page using paired tags. With nothing more than a text editor and a browser you can already build and view a simple web page.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/HTML/Introduction_to_HTML">MDN - Introduction to HTML</a></li>
<li><a href="https://www.w3schools.com/html/html_intro.asp">W3Schools - HTML Introduction</a></li>
</ul>
HTML,

            14 => <<<'HTML'
<h2>HTML Document Structure</h2>
<p>By the end of this lesson you will write a complete, correctly structured HTML5 document with the standard skeleton every real page uses.</p>
<h3>The HTML5 skeleton</h3>
<p>A proper page is wrapped in a standard structure. Learn this by heart:</p>
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;Kalomo Hardware&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;h1&gt;Welcome&lt;/h1&gt;
    &lt;p&gt;Your trusted hardware shop.&lt;/p&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>
<h3>What each part does</h3>
<ul>
<li><code>&lt;!DOCTYPE html&gt;</code> - tells the browser this is modern HTML5.</li>
<li><code>&lt;html lang="en"&gt;</code> - the whole document; <code>lang</code> states the language.</li>
<li><code>&lt;head&gt;</code> - information <em>about</em> the page, not shown in the body: the title, character set, and the viewport setting that makes pages work on phones.</li>
<li><code>&lt;title&gt;</code> - the text shown on the browser tab.</li>
<li><code>&lt;body&gt;</code> - everything the visitor actually sees.</li>
</ul>
<h3>Why the viewport line matters</h3>
<p>Most Zambians browse on phones. The <code>viewport</code> meta tag tells mobile browsers to fit the page to the screen width. Leave it out and your site looks tiny and zoomed-out on a phone. Always include it.</p>
<h3>Try It Yourself</h3>
<ol>
<li>Type the full skeleton above into a new <code>index.html</code>.</li>
<li>Change the title to your own name and check the browser tab.</li>
<li>Add two paragraphs inside the body and view the result.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>DOCTYPE</strong> - declares the HTML version.</li>
<li><strong>head</strong> - holds page information, not visible content.</li>
<li><strong>body</strong> - holds the visible content.</li>
<li><strong>meta viewport</strong> - makes the page fit mobile screens.</li>
</ul>
<h3>Summary</h3>
<p>Every real page uses the DOCTYPE, html, head and body skeleton. The head carries the title and the all-important viewport tag; the body carries what people see.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/HTML/Introduction_to_HTML/The_head_metadata_in_HTML">MDN - What's in the head</a></li>
<li><a href="https://www.w3schools.com/html/html_basic.asp">W3Schools - HTML Basic</a></li>
</ul>
HTML,

            15 => <<<'HTML'
<h2>Semantic HTML Elements</h2>
<p>By the end of this lesson you will structure a page with meaningful elements like header, nav, main and footer, which helps search engines and screen readers understand your content.</p>
<h3>What does "semantic" mean?</h3>
<p>Semantic means the tag describes its <em>purpose</em>, not just its appearance. <code>&lt;header&gt;</code> clearly marks the top of a page; a plain <code>&lt;div&gt;</code> tells you nothing. Search engines like Google read these tags to understand your site, which helps people find your business.</p>
<h3>The main building blocks</h3>
<pre><code>&lt;body&gt;
  &lt;header&gt;
    &lt;h1&gt;Kalomo Hardware&lt;/h1&gt;
  &lt;/header&gt;

  &lt;nav&gt;
    &lt;a href="index.html"&gt;Home&lt;/a&gt;
    &lt;a href="products.html"&gt;Products&lt;/a&gt;
    &lt;a href="contact.html"&gt;Contact&lt;/a&gt;
  &lt;/nav&gt;

  &lt;main&gt;
    &lt;section&gt;
      &lt;h2&gt;Our Products&lt;/h2&gt;
      &lt;p&gt;Tools, paint, cement and more.&lt;/p&gt;
    &lt;/section&gt;
  &lt;/main&gt;

  &lt;footer&gt;
    &lt;p&gt;Call us: 0977 123 456&lt;/p&gt;
  &lt;/footer&gt;
&lt;/body&gt;</code></pre>
<h3>What each element is for</h3>
<ul>
<li><code>&lt;header&gt;</code> - the top area, often a logo and title.</li>
<li><code>&lt;nav&gt;</code> - the navigation menu of links.</li>
<li><code>&lt;main&gt;</code> - the main content, one per page.</li>
<li><code>&lt;section&gt;</code> - a grouped block of related content.</li>
<li><code>&lt;footer&gt;</code> - the bottom area, often contacts and copyright.</li>
</ul>
<h3>Links between pages</h3>
<p>The <code>&lt;a&gt;</code> (anchor) tag links pages: <code>&lt;a href="contact.html"&gt;Contact&lt;/a&gt;</code>. The <code>href</code> is the destination.</p>
<h3>Try It Yourself</h3>
<ol>
<li>Rebuild your page using header, nav, main and footer.</li>
<li>Put three links in the nav and your phone number in the footer.</li>
<li>Add a section with an h2 and a short paragraph.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>Semantic element</strong> - a tag that describes its purpose.</li>
<li><strong>nav</strong> - the navigation links area.</li>
<li><strong>main / section</strong> - the primary content and its groups.</li>
<li><strong>anchor (a)</strong> - a link to another page or site.</li>
</ul>
<h3>Summary</h3>
<p>Semantic tags give your page meaning, improving accessibility and search ranking. A clear header, nav, main and footer structure is the mark of a professional page.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Glossary/Semantics">MDN - Semantics</a></li>
<li><a href="https://www.w3schools.com/html/html5_semantic_elements.asp">W3Schools - Semantic Elements</a></li>
</ul>
HTML,

            16 => <<<'HTML'
<h2>Forms and Input Elements</h2>
<p>By the end of this lesson you will build a form that collects information from visitors - names, emails, choices - the basis of contact pages, sign-ups and orders.</p>
<h3>The form element</h3>
<p>A form wraps the fields people fill in:</p>
<pre><code>&lt;form&gt;
  &lt;label&gt;Full name:&lt;/label&gt;
  &lt;input type="text" name="fullname"&gt;

  &lt;label&gt;Email:&lt;/label&gt;
  &lt;input type="email" name="email"&gt;

  &lt;label&gt;Phone:&lt;/label&gt;
  &lt;input type="tel" name="phone"&gt;

  &lt;button type="submit"&gt;Send&lt;/button&gt;
&lt;/form&gt;</code></pre>
<h3>Common input types</h3>
<ul>
<li><code>type="text"</code> - any short text.</li>
<li><code>type="email"</code> - checks for a valid email shape.</li>
<li><code>type="tel"</code> - phone numbers (shows a number keypad on phones).</li>
<li><code>type="number"</code> - numeric values only.</li>
<li><code>type="date"</code> - a date picker.</li>
</ul>
<h3>Labels matter</h3>
<p>Always pair an <code>&lt;label&gt;</code> with its input. Labels tell users what to type and let screen-reader users complete your form. Connect them with matching <code>id</code> and <code>for</code>:</p>
<pre><code>&lt;label for="email"&gt;Email:&lt;/label&gt;
&lt;input type="email" id="email" name="email"&gt;</code></pre>
<h3>Choices: dropdowns and textareas</h3>
<pre><code>&lt;label for="course"&gt;Course of interest:&lt;/label&gt;
&lt;select id="course" name="course"&gt;
  &lt;option&gt;Python Programming&lt;/option&gt;
  &lt;option&gt;Web Development&lt;/option&gt;
&lt;/select&gt;

&lt;label for="msg"&gt;Message:&lt;/label&gt;
&lt;textarea id="msg" name="message"&gt;&lt;/textarea&gt;</code></pre>
<h3>Try It Yourself</h3>
<ol>
<li>Build a contact form with name, email, phone and a message textarea.</li>
<li>Add a dropdown of three courses.</li>
<li>Mark a field as compulsory by adding <code>required</code> to its input.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>form</strong> - the container for input fields.</li>
<li><strong>input</strong> - a single field; its <code>type</code> sets the kind.</li>
<li><strong>label</strong> - text describing a field, linked by for/id.</li>
<li><strong>select / textarea</strong> - a dropdown and a multi-line text box.</li>
</ul>
<h3>Summary</h3>
<p>Forms gather information through inputs of different types, always paired with labels for clarity and accessibility. This is how a website turns visitors into enquiries and customers.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/Forms">MDN - Web forms</a></li>
<li><a href="https://www.w3schools.com/html/html_forms.asp">W3Schools - HTML Forms</a></li>
</ul>
HTML,

            17 => <<<'HTML'
<h2>CSS Basics and Selectors</h2>
<p>By the end of this lesson you will add colour, fonts and spacing to a page with CSS, and target exactly the elements you want to style.</p>
<h3>What is CSS?</h3>
<p>HTML builds the structure; CSS (Cascading Style Sheets) makes it look good. CSS controls colours, fonts, sizes, spacing and layout. Without CSS, every website would be plain black text on white.</p>
<h3>Three ways to add CSS - use the best one</h3>
<p>The professional way is a separate file linked in the head:</p>
<pre><code>&lt;!-- in index.html, inside &lt;head&gt; --&gt;
&lt;link rel="stylesheet" href="style.css"&gt;</code></pre>
<p>Then in <code>style.css</code> you write rules.</p>
<h3>The shape of a CSS rule</h3>
<pre><code>h1 {
  color: navy;
  font-size: 32px;
  text-align: center;
}</code></pre>
<p>The <strong>selector</strong> (<code>h1</code>) chooses what to style; inside the braces are <strong>properties</strong> and <strong>values</strong>, each ending in a semicolon.</p>
<h3>The three core selectors</h3>
<ul>
<li><strong>Element</strong>: <code>p { ... }</code> styles every paragraph.</li>
<li><strong>Class</strong>: <code>.highlight { ... }</code> styles any element with <code>class="highlight"</code>. Reusable - use this most.</li>
<li><strong>ID</strong>: <code>#header { ... }</code> styles the single element with <code>id="header"</code>.</li>
</ul>
<pre><code>&lt;p class="highlight"&gt;Special offer this week!&lt;/p&gt;</code></pre>
<pre><code>.highlight {
  background-color: gold;
  padding: 10px;
}</code></pre>
<h3>Try It Yourself</h3>
<ol>
<li>Create <code>style.css</code> and link it in your page's head.</li>
<li>Make all paragraphs dark grey and centre your h1.</li>
<li>Add a class called <code>price</code> that shows text in green and bold, and apply it to a paragraph.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>CSS</strong> - the language that styles HTML.</li>
<li><strong>Selector</strong> - what a rule targets (element, class or id).</li>
<li><strong>Property / value</strong> - what to change and to what, e.g. <code>color: navy</code>.</li>
<li><strong>Class</strong> - a reusable label for styling many elements.</li>
</ul>
<h3>Summary</h3>
<p>CSS rules pair a selector with properties and values. Link one stylesheet, lean on classes for reusable styles, and your pages start to look like real websites.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/CSS/First_steps">MDN - CSS first steps</a></li>
<li><a href="https://www.w3schools.com/css/css_selectors.asp">W3Schools - CSS Selectors</a></li>
</ul>
HTML,

            18 => <<<'HTML'
<h2>The Box Model and Layout</h2>
<p>By the end of this lesson you will understand how every element is a box with content, padding, border and margin - the key to controlling spacing and layout.</p>
<h3>Everything is a box</h3>
<p>In CSS, every element is a rectangular box made of four layers, from the inside out:</p>
<ul>
<li><strong>Content</strong> - the text or image itself.</li>
<li><strong>Padding</strong> - space <em>inside</em> the box, between content and border.</li>
<li><strong>Border</strong> - the line around the padding.</li>
<li><strong>Margin</strong> - space <em>outside</em> the box, pushing other elements away.</li>
</ul>
<h3>Seeing it in code</h3>
<pre><code>.card {
  padding: 16px;
  border: 1px solid grey;
  margin: 20px;
  background-color: white;
}</code></pre>
<p>Padding gives the content breathing room; margin separates this card from its neighbours.</p>
<h3>A tip that prevents headaches</h3>
<p>By default, padding and border add to an element's width, which surprises beginners. Add this rule once at the top of your stylesheet so width means what you expect:</p>
<pre><code>* {
  box-sizing: border-box;
}</code></pre>
<h3>Controlling size</h3>
<pre><code>.banner {
  width: 100%;
  max-width: 800px;
  height: 200px;
  margin: 0 auto;   /* centres a fixed-width box */
}</code></pre>
<p><code>max-width</code> keeps a box from stretching too wide on big screens, while <code>margin: 0 auto</code> centres it.</p>
<h3>Try It Yourself</h3>
<ol>
<li>Add <code>box-sizing: border-box</code> to all elements.</li>
<li>Make a <code>.card</code> class with padding, a border and a margin, and apply it to a section.</li>
<li>Create a centred banner that is full width but never wider than 800px.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>Box model</strong> - content, padding, border, margin.</li>
<li><strong>Padding</strong> - space inside the border.</li>
<li><strong>Margin</strong> - space outside the border.</li>
<li><strong>box-sizing: border-box</strong> - makes width include padding and border.</li>
</ul>
<h3>Summary</h3>
<p>Understanding the box model - and setting box-sizing to border-box - gives you precise control over spacing and sizing, the foundation of every layout.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/CSS/Building_blocks/The_box_model">MDN - The box model</a></li>
<li><a href="https://www.w3schools.com/css/css_boxmodel.asp">W3Schools - CSS Box Model</a></li>
</ul>
HTML,

            19 => <<<'HTML'
<h2>Flexbox Layout</h2>
<p>By the end of this lesson you will arrange elements in neat rows and columns with Flexbox, the modern way to build navigation bars, card rows and page layouts.</p>
<h3>The problem Flexbox solves</h3>
<p>Placing boxes side by side used to be painful. Flexbox makes a container lay its children out in a row (or column) with even spacing and alignment, in just a few lines.</p>
<h3>Turning on Flexbox</h3>
<pre><code>.menu {
  display: flex;
  gap: 16px;
}</code></pre>
<p>Add <code>display: flex</code> to a container and its direct children line up in a row. <code>gap</code> adds even space between them.</p>
<h3>Aligning items</h3>
<pre><code>.bar {
  display: flex;
  justify-content: space-between;  /* push items to the ends */
  align-items: center;             /* vertically centre them */
}</code></pre>
<ul>
<li><code>justify-content</code> controls spacing along the row: <code>center</code>, <code>space-between</code>, <code>space-around</code>, <code>flex-start</code>, <code>flex-end</code>.</li>
<li><code>align-items</code> controls vertical alignment: <code>center</code>, <code>flex-start</code>, <code>stretch</code>.</li>
</ul>
<h3>A row of product cards</h3>
<pre><code>.products {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;   /* cards drop to the next line on small screens */
}
.products .card {
  flex: 1;           /* each card shares the space equally */
  min-width: 200px;
}</code></pre>
<p><code>flex-wrap: wrap</code> is the trick that lets a row of cards fold neatly onto phones.</p>
<h3>Try It Yourself</h3>
<ol>
<li>Make your nav a flex container with the logo on the left and links on the right using <code>justify-content: space-between</code>.</li>
<li>Build a row of three product cards that wrap on narrow screens.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>Flexbox</strong> - a layout system for rows and columns.</li>
<li><strong>display: flex</strong> - turns a container into a flex container.</li>
<li><strong>justify-content</strong> - spacing along the main axis.</li>
<li><strong>align-items</strong> - alignment across the other axis.</li>
<li><strong>flex-wrap</strong> - lets items wrap onto new lines.</li>
</ul>
<h3>Summary</h3>
<p>Flexbox arranges elements with simple, readable rules for spacing and alignment, and <code>flex-wrap</code> keeps layouts tidy on every screen size. It is the everyday tool for modern layouts.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/CSS/CSS_layout/Flexbox">MDN - Flexbox</a></li>
<li><a href="https://www.w3schools.com/css/css3_flexbox.asp">W3Schools - CSS Flexbox</a></li>
</ul>
HTML,

            20 => <<<'HTML'
<h2>Responsive Design with Media Queries</h2>
<p>By the end of this lesson you will make a page adapt to any screen - phone, tablet or desktop - using media queries, essential since most Zambians browse on phones.</p>
<h3>Why responsive design matters</h3>
<p>A site that looks great on a laptop can be unusable on a phone if it does not adapt. Responsive design means the layout responds to the screen size. Step one was the viewport meta tag you met earlier; step two is media queries.</p>
<h3>What is a media query?</h3>
<p>A media query applies CSS only when a condition about the screen is true - usually its width:</p>
<pre><code>/* default styles apply to all screens (mobile first) */
.container {
  width: 100%;
}

/* extra styles only on screens 768px wide or more */
@media (min-width: 768px) {
  .container {
    width: 750px;
    margin: 0 auto;
  }
}</code></pre>
<h3>The "mobile first" approach</h3>
<p>Write your basic styles for small phones first, then add media queries to enhance the layout on bigger screens. This keeps the phone experience - the most common one here - simple and fast.</p>
<h3>A responsive card row</h3>
<pre><code>.products {
  display: flex;
  flex-direction: column;   /* stacked on phones */
  gap: 16px;
}

@media (min-width: 600px) {
  .products {
    flex-direction: row;    /* side by side on larger screens */
    flex-wrap: wrap;
  }
}</code></pre>
<h3>Common breakpoints</h3>
<ul>
<li>Up to 600px - phones</li>
<li>600px to 992px - tablets</li>
<li>992px and above - laptops and desktops</li>
</ul>
<h3>Try It Yourself</h3>
<ol>
<li>Make your product cards stack in a column by default and sit in a row above 600px.</li>
<li>Increase your h1 font size on screens wider than 768px using a media query.</li>
<li>Resize the browser window slowly and watch the layout change.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>Responsive design</strong> - layouts that adapt to screen size.</li>
<li><strong>Media query</strong> - CSS that applies only when a condition is met.</li>
<li><strong>Breakpoint</strong> - a screen width where the layout changes.</li>
<li><strong>Mobile first</strong> - design for phones, then enhance for bigger screens.</li>
</ul>
<h3>Summary</h3>
<p>Media queries let one page serve every device. Start mobile first, add breakpoints for larger screens, and your site works for the phone-majority audience in Zambia.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/CSS/CSS_layout/Media_queries">MDN - Media queries</a></li>
<li><a href="https://www.w3schools.com/css/css_rwd_mediaqueries.asp">W3Schools - Responsive Media Queries</a></li>
</ul>
HTML,
        ];
    }
}
