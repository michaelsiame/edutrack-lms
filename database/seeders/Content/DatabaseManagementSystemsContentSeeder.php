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

class DatabaseManagementSystemsContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Database Management Systems')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Database Management Systems" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in Database Management Systems already has modules. Skipping content seed.');
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

                // Create module quiz
                $quizData = $this->{"module{$moduleNumber}Quiz"}();
                $quiz = Quiz::create([
                    'course_id' => $this->courseId,
                    'lesson_id' => null,
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
                'title' => 'Module 1: Database Foundations',
                'description' => 'Understand what databases are, how they differ from paper records and spreadsheets, and learn the core concepts of tables, rows, columns, keys, and relationships.',
            ],
            [
                'title' => 'Module 2: Designing a Real-World Database',
                'description' => 'Plan and design a database for a Zambian business or organisation, create tables with correct data types, and apply normalisation to avoid duplication.',
            ],
            [
                'title' => 'Module 3: SQL and MySQL Basics',
                'description' => 'Write SQL queries to read, insert, update, and delete data in MySQL, and learn how to link tables together using JOINs.',
            ],
            [
                'title' => 'Module 4: Database Administration, Backups, and Reports',
                'description' => 'Use phpMyAdmin to manage databases, protect data with backups and security practices, and generate simple reports from your data.',
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

    /* ==================================================================== */
    /*  Module 1 – Database Foundations                                     */
    /* ==================================================================== */

    private function module1Lessons(): array
    {
        return [
            [
                'title' => '1.1 What Is a Database and Why Does Your Business Need One?',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what a database is in plain language, describe why a database is more powerful than an exercise book or spreadsheet for keeping records, and give two real examples of how a database could help a Zambian shop, school, or clinic work more efficiently.</p>

<h2>What Is a Database?</h2>
<p>A database is an organised collection of information that is stored electronically so that it can be searched, updated, and analysed quickly. Think of it as a very smart filing cabinet. Instead of flipping through pages of paper, you type a question and the database gives you an answer in seconds.</p>
<p>You already use databases every day, even if you do not realise it. When you check your Airtel Money balance, the system looks up your account in a database. When a clinic nurse pulls up your patient history on a computer, that is a database. When the school bursar prints a list of pupils who have not paid fees, that list comes from a database. Even the ZESCO token vending system relies on databases to track prepaid electricity purchases.</p>

<h2>Why Not Just Use an Exercise Book or Excel?</h2>
<p>Many small businesses in Zambia still keep records in hardback exercise books or on paper ledgers. This works when the business is tiny, but it creates serious problems as the business grows.</p>

<h3>The Problem with Paper Records</h3>
<ul>
<li><strong>They are slow to search.</strong> If a shopkeeper in Soweto Market wants to know how many bags of mealie meal she sold in January, she must flip through every page of her book.</li>
<li><strong>They are easy to damage or lose.</strong> A flooded shop, a fire, or simply misplacing the book can destroy years of records.</li>
<li><strong>Only one person can use them at a time.</strong> If the owner is writing in the book, the assistant cannot check stock at the same time.</li>
<li><strong>Mistakes are hard to fix.</strong> Crossing out numbers looks untidy, and correction fluid makes pages hard to read.</li>
</ul>

<h3>The Problem with Excel Alone</h3>
<p>Microsoft Excel is better than paper because it lets you sort, filter, and calculate automatically. However, a spreadsheet is not a true database. If you have one sheet for customers and another for sales, there is no automatic way to stop someone from entering a sale for a customer who does not exist. A proper database enforces these rules and stops errors before they happen.</p>

<h2>Worked Example: Mama Ngosa's Shop</h2>
<p>Mama Ngosa runs a small grocery shop in Kalomo. She sells sugar, cooking oil, soap, and airtime vouchers. For two years she wrote every sale in an exercise book. One rainy season, water leaked into her storeroom and soaked half the pages. She could no longer tell which suppliers she still owed money to.</p>
<p>After completing a short computer course, Mama Ngosa switched to a simple database. She created one table for <strong>products</strong> (with columns for name, quantity in stock, and cost price) and another table for <strong>sales</strong> (with columns for date, product, quantity sold, and amount paid). Now, when she wants to reorder stock, she runs a quick query and sees exactly which items are running low. When the tax officer asks for her quarterly sales total, she prints a report in under a minute instead of adding numbers by hand for three hours.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Take a blank sheet of paper and draw three columns: <strong>Item</strong>, <strong>Quantity</strong>, and <strong>Price</strong>.</li>
<li>List five items you might find in a small Zambian shop (for example: 1 kg sugar, 750 ml cooking oil, a bar of soap, a bundle of airtime, a bag of charcoal).</li>
<li>Imagine you sell three of those items today. Cross out the old quantity and write the new one next to it.</li>
<li>Now imagine you have fifty items and thirty sales per day. Write one sentence describing why a database would be easier than paper for this task.</li>
<li>Ask yourself: "What would happen to my paper records if there was load-shedding and a candle fell over?"</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Database</strong>: An organised electronic collection of data that can be searched, updated, and reported on.</li>
<li><strong>Record</strong>: One complete set of information about a single item or person, such as one sale or one patient.</li>
<li><strong>Query</strong>: A question you ask a database, for example "Show me all customers who owe more than K100."</li>
<li><strong>Spreadsheet</strong>: A grid of rows and columns used for calculations, like Microsoft Excel or Google Sheets.</li>
<li><strong>Report</strong>: A formatted summary of data from a database, often printed or saved as a PDF.</li>
</ul>

<h2>Summary</h2>
<p>A database is an electronic tool for storing and organising information so that it can be found quickly and accurately. Unlike exercise books, databases cannot be ruined by water or fire if backups are kept. Unlike spreadsheets, databases can enforce rules that prevent mistakes. For any Zambian business, school, or clinic that handles more than a handful of records each day, a database saves time, reduces errors, and makes reporting far easier.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/sql/sql_intro.asp">W3Schools – SQL Introduction</a> – A gentle beginner's guide to what databases are and why they matter.</li>
<li><a href="https://www.khanacademy.org/computing/computer-programming/sql">Khan Academy – SQL Basics</a> – Free interactive lessons on databases with practice exercises.</li>
<li><a href="https://dev.mysql.com/doc/refman/8.0/en/what-is-mysql.html">MySQL Official Documentation – What Is MySQL?</a> – The official explanation of the world's most popular free database system.</li>
</ul>
HTML,
            ],
            [
                'title' => '1.2 Understanding Tables, Rows, and Columns',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain how a database organises information into tables, rows, and columns, design a simple table for a real Zambian scenario, and identify appropriate data types for common pieces of information such as names, phone numbers, dates, and amounts of money.</p>

<h2>Tables: The Building Blocks of a Database</h2>
<p>Every database is made up of one or more <strong>tables</strong>. A table is like a single sheet in an Excel workbook, but with stricter rules. Each table stores information about one type of thing only. For example, you might have a <strong>Customers</strong> table, a <strong>Products</strong> table, and a <strong>Sales</strong> table. You would not mix customer names and product prices in the same table because that makes the database messy and hard to maintain.</p>
<p>Think of a table as a grid. The vertical divisions are called <strong>columns</strong> (or fields), and each column holds one type of information. The horizontal divisions are called <strong>rows</strong> (or records), and each row holds the information about one particular item or person.</p>

<h2>Worked Example: A Customers Table for a Kalomo Shop</h2>
<p>Imagine a hardware shop in Kalomo that sells building materials on credit to local builders. The shopkeeper needs to know each customer's name, phone number, and outstanding balance. Here is how that table might look:</p>

<table>
<thead>
<tr><th>customer_id</th><th>full_name</th><th>phone_number</th><th>town</th><th>balance_owed</th></tr>
</thead>
<tbody>
<tr><td>1</td><td>John Banda</td><td>0965123456</td><td>Kalomo</td><td>450.00</td></tr>
<tr><td>2</td><td>Mary Lungu</td><td>0977123456</td><td>Livingstone</td><td>0.00</td></tr>
<tr><td>3</td><td>Peter Mwale</td><td>0955123456</td><td>Kalomo</td><td>1200.00</td></tr>
</tbody>
</table>

<p>Each row is one customer. Each column is one fact about that customer. Notice that every row has the same columns in the same order. This consistency is what makes the database powerful. If the shopkeeper wants every customer who owes more than K500, the database can scan the <strong>balance_owed</strong> column and return only the matching rows in a fraction of a second.</p>

<h2>What Is a Data Type?</h2>
<p>A data type tells the database what kind of information a column is allowed to hold. Choosing the right data type prevents errors and saves space. Here are the most common types you will use in MySQL:</p>

<ul>
<li><strong>INT</strong> – Whole numbers, such as quantities, ages, or ID numbers. Example: <code>45</code></li>
<li><strong>VARCHAR</strong> – Text of varying length, such as names, addresses, or phone numbers. Example: <code>VARCHAR(50)</code> means up to fifty characters.</li>
<li><strong>DATE</strong> – Calendar dates in the format YYYY-MM-DD. Example: <code>2026-06-11</code></li>
<li><strong>DECIMAL</strong> – Numbers with decimal places, perfect for money. Example: <code>DECIMAL(10,2)</code> means up to ten digits with two after the decimal point, such as <code>1250.50</code>.</li>
<li><strong>TEXT</strong> – Long passages of text, such as notes or descriptions.</li>
<li><strong>BOOLEAN</strong> – A true or false value, often stored as <code>1</code> (true) or <code>0</code> (false).</li>
</ul>

<h3>Why Phone Numbers Should Be VARCHAR, Not INT</h3>
<p>A common beginner mistake is to store phone numbers as numbers. If you store <code>0965123456</code> as an INT, the leading zero is dropped and the database stores <code>965123456</code> instead. Phone numbers are not really numbers; you never add, subtract, or multiply them. Store them as VARCHAR so the leading zero stays exactly where it belongs.</p>

<h2>Try It Yourself</h2>
<ol>
<li>On a sheet of paper, draw a table with five columns: <strong>product_id</strong>, <strong>product_name</strong>, <strong>category</strong>, <strong>quantity_in_stock</strong>, and <strong>unit_price</strong>.</li>
<li>Fill in at least four rows with products you might sell in a Zambian shop. Use realistic prices in Kwacha, such as K25.00 for a bar of soap or K180.00 for a 5-litre container of cooking oil.</li>
<li>Next to each column name, write the data type you think is best: INT, VARCHAR, DATE, or DECIMAL.</li>
<li>Look at your <strong>quantity_in_stock</strong> column. Write one sentence explaining why INT is better than DECIMAL for counting whole items.</li>
<li>Ask a friend to read your table. Can they tell, just by looking, what one row represents?</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Table</strong>: A structure in a database that holds data about one type of thing, organised into rows and columns.</li>
<li><strong>Row (Record)</strong>: A single horizontal entry in a table containing all the information about one item or person.</li>
<li><strong>Column (Field)</strong>: A single vertical category of information within a table, such as "phone_number" or "price".</li>
<li><strong>Data Type</strong>: A rule that defines what kind of data a column can hold, such as numbers, text, or dates.</li>
<li><strong>VARCHAR</strong>: A text data type that stores a variable number of characters up to a specified limit.</li>
</ul>

<h2>Summary</h2>
<p>Databases store information in tables made up of rows and columns. Each table focuses on one type of thing, each row is one record, and each column is one field. Choosing the correct data type for each column keeps your data clean and prevents common errors, such as losing the leading zero from a Zambian mobile phone number. When you design a table, think first about what real-world object it represents and what facts you need to record about that object.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/sql/sql_datatypes.asp">W3Schools – SQL Data Types</a> – A clear reference for MySQL data types with examples.</li>
<li><a href="https://dev.mysql.com/doc/refman/8.0/en/data-types.html">MySQL Documentation – Data Types</a> – The official guide to every data type available in MySQL.</li>
<li><a href="https://www.khanacademy.org/computing/computer-programming/sql">Khan Academy – SQL</a> – Free practice exercises on tables and data types.</li>
</ul>
HTML,
            ],
            [
                'title' => '1.3 Primary Keys, Foreign Keys, and Relationships',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what a primary key is and why every table needs one, describe how foreign keys link two tables together, and identify one-to-many relationships in everyday Zambian situations such as a school with many pupils or a clinic with many patients.</p>

<h2>Why Every Table Needs a Primary Key</h2>
<p>Imagine a school in Kalomo that keeps a list of pupils in a database. Two pupils are both named "Mary Banda." If the school secretary wants to update Mary Banda's guardian phone number, how does the database know <em>which</em> Mary Banda to change? This is where a <strong>primary key</strong> becomes essential.</p>
<p>A primary key is a special column (or group of columns) that contains a unique value for every single row. No two rows can ever have the same primary key. Common examples include a pupil ID number, an NRC number, or an auto-generated number that the database creates automatically. Once a primary key is set, the database will refuse to insert a duplicate, which protects you from accidentally creating two records for the same person.</p>

<h3>Good and Bad Choices for Primary Keys</h3>
<ul>
<li><strong>Good:</strong> An auto-incrementing number such as <code>1, 2, 3, 4...</code> that the database manages for you.</li>
<li><strong>Good:</strong> A government-issued unique identifier such as an NRC number, <em>provided</em> every person in the table is guaranteed to have one.</li>
<li><strong>Bad:</strong> A person's name, because names are not unique. A school might easily have three pupils named "John Mwale."</li>
<li><strong>Bad:</strong> A phone number, because people change numbers and sometimes share family lines.</li>
</ul>

<h2>Linking Tables with Foreign Keys</h2>
<p>A <strong>foreign key</strong> is a column in one table that stores the primary key value from another table. It is the glue that holds a database together. Without foreign keys, you would have to copy the same information into many places, which wastes space and invites mistakes.</p>

<h2>Worked Example: A School Database</h2>
<p>Consider a community school with two tables: <strong>Classes</strong> and <strong>Pupils</strong>.</p>

<p>The <strong>Classes</strong> table has a primary key called <strong>class_id</strong>:</p>
<table>
<thead>
<tr><th>class_id</th><th>class_name</th><th>teacher_name</th></tr>
</thead>
<tbody>
<tr><td>1</td><td>Grade 8A</td><td>Mr. Tembo</td></tr>
<tr><td>2</td><td>Grade 8B</td><td>Mrs. Mulenga</td></tr>
<tr><td>3</td><td>Grade 9A</td><td>Mr. Banda</td></tr>
</tbody>
</table>

<p>The <strong>Pupils</strong> table has its own primary key, <strong>pupil_id</strong>, and a foreign key called <strong>class_id</strong> that links each pupil to a class:</p>
<table>
<thead>
<tr><th>pupil_id</th><th>full_name</th><th>class_id</th><th>guardian_phone</th></tr>
</thead>
<tbody>
<tr><td>101</td><td>Chileshe Mwape</td><td>1</td><td>0965123456</td></tr>
<tr><td>102</td><td>Mutale Kapamba</td><td>1</td><td>0977123456</td></tr>
<tr><td>103</td><td>Lombe Sichone</td><td>2</td><td>0955123456</td></tr>
</tbody>
</table>

<p>Notice that Chileshe and Mutale both have <code>class_id = 1</code>, which means they belong to Grade 8A. Lombe has <code>class_id = 2</code>, so she belongs to Grade 8B. The <strong>class_id</strong> column in the Pupils table is a foreign key because it references the primary key of the Classes table. This is called a <strong>one-to-many relationship</strong>: one class has many pupils, but each pupil belongs to only one class.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Draw two tables on paper: <strong>Clinics</strong> and <strong>Patients</strong>.</li>
<li>In the Clinics table, create three columns: <strong>clinic_id</strong> (primary key), <strong>clinic_name</strong>, and <strong>town</strong>. Add two rows: Kalomo Rural Health Centre and Monze General Hospital.</li>
<li>In the Patients table, create four columns: <strong>patient_id</strong> (primary key), <strong>patient_name</strong>, <strong>clinic_id</strong> (foreign key), and <strong>date_of_birth</strong>.</li>
<li>Add four patients. Make sure at least two patients share the same clinic_id, and one patient has a different clinic_id.</li>
<li>Write one sentence explaining what would go wrong if you tried to give a patient a clinic_id of <code>99</code> when no clinic has that ID.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Primary Key</strong>: A column (or set of columns) that uniquely identifies each row in a table.</li>
<li><strong>Foreign Key</strong>: A column in one table that stores the primary key value from another table, creating a link between the two tables.</li>
<li><strong>Relationship</strong>: A connection between two tables based on matching data in their key columns.</li>
<li><strong>One-to-Many Relationship</strong>: A relationship where one record in the first table can be linked to many records in the second table, but each record in the second table links to only one in the first.</li>
<li><strong>Auto-Increment</strong>: A feature that automatically generates the next number in sequence for a primary key column.</li>
</ul>

<h2>Summary</h2>
<p>Primary keys give every row a unique identity so the database can tell records apart, even when names or other details are the same. Foreign keys link tables together without copying data, which saves space and prevents contradictions. One-to-many relationships are the most common pattern in real-world databases: one clinic has many patients, one teacher teaches many pupils, and one supplier provides many products. Understanding keys and relationships is the foundation of good database design.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/sql/sql_primarykey.asp">W3Schools – SQL Primary Key</a> – Examples of creating and using primary keys in MySQL.</li>
<li><a href="https://www.w3schools.com/sql/sql_foreignkey.asp">W3Schools – SQL Foreign Key</a> – How foreign keys link tables with practical syntax.</li>
<li><a href="https://www.khanacademy.org/computing/computer-programming/sql">Khan Academy – SQL</a> – Interactive exercises on relational data.</li>
</ul>
HTML,
            ],
            [
                'title' => '1.4 Module 1 Quiz: Database Foundations',
                'type' => 'Quiz',
                'duration_minutes' => 20,
                'content' => '<p>Complete this quiz to test your understanding of databases, tables, rows, columns, keys, and relationships.</p>',
            ],
        ];
    }

    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: Database Foundations',
            'description' => 'Test your knowledge of what databases are, how tables are structured, and how keys link data together.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following best describes a database?',
                    'explanation' => 'A database is an organised electronic collection of data designed for fast searching, updating, and reporting.',
                    'options' => [
                        ['text' => 'An organised electronic collection of data that can be searched and updated', 'is_correct' => true],
                        ['text' => 'A printed ledger used for accounting', 'is_correct' => false],
                        ['text' => 'A type of computer virus', 'is_correct' => false],
                        ['text' => 'A social media platform', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In a database table, what does a row represent?',
                    'explanation' => 'Each row (also called a record) holds all the information about one specific item, person, or event.',
                    'options' => [
                        ['text' => 'One complete record about a single item or person', 'is_correct' => true],
                        ['text' => 'A category of information such as a name or price', 'is_correct' => false],
                        ['text' => 'The title of the table', 'is_correct' => false],
                        ['text' => 'A colour used in the database design', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why should a Zambian mobile phone number be stored as VARCHAR rather than INT?',
                    'explanation' => 'Storing a phone number as an INT removes the leading zero. VARCHAR preserves the full number exactly as dialled.',
                    'options' => [
                        ['text' => 'Because VARCHAR preserves the leading zero', 'is_correct' => true],
                        ['text' => 'Because INT cannot store numbers longer than six digits', 'is_correct' => false],
                        ['text' => 'Because phone numbers need decimal places', 'is_correct' => false],
                        ['text' => 'Because VARCHAR is always faster than INT', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A primary key column can contain duplicate values for different rows.',
                    'explanation' => 'A primary key must be unique for every row. Duplicate values would break the rule and the database would reject them.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A foreign key in one table stores the primary key value from another table to create a link.',
                    'explanation' => 'This is exactly what a foreign key does. It references a primary key in another table to establish a relationship.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In a school database, a Pupils table has a class_id column that matches the class_id in a Classes table. What is this column called?',
                    'explanation' => 'The class_id column in the Pupils table is a foreign key because it references the primary key of the Classes table.',
                    'options' => [
                        ['text' => 'A foreign key', 'is_correct' => true],
                        ['text' => 'A primary key', 'is_correct' => false],
                        ['text' => 'A data type', 'is_correct' => false],
                        ['text' => 'A report', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which data type is most appropriate for storing the price of a bag of mealie meal in Kwacha?',
                    'explanation' => 'DECIMAL is the correct choice for money because it stores exact decimal values such as 150.00 without rounding errors.',
                    'options' => [
                        ['text' => 'DECIMAL', 'is_correct' => true],
                        ['text' => 'INT', 'is_correct' => false],
                        ['text' => 'DATE', 'is_correct' => false],
                        ['text' => 'BOOLEAN', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the name of the special column that uniquely identifies every row in a table? (two words)',
                    'explanation' => 'The primary key uniquely identifies each row and prevents duplicate records.',
                    'correct_answer' => 'Primary key',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of these is a good choice for a primary key in a Patients table at a clinic?',
                    'explanation' => 'An auto-incrementing number is reliable because it is guaranteed unique and does not depend on personal details that might change or be shared.',
                    'options' => [
                        ['text' => 'An auto-incrementing number managed by the database', 'is_correct' => true],
                        ['text' => 'The patient\'s first name only', 'is_correct' => false],
                        ['text' => 'The patient\'s favourite colour', 'is_correct' => false],
                        ['text' => 'The day of the week they were born', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    /* ==================================================================== */
    /*  Module 2 – Designing a Real-World Database                          */
    /* ==================================================================== */

    private function module2Lessons(): array
    {
        return [
            [
                'title' => '2.1 Planning a Database for a Zambian Business',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to identify the main "things" that need to be tracked in a real Zambian business, list the important facts about each thing, and sketch a simple database plan on paper before touching a computer.</p>

<h2>Start with Paper, Not the Keyboard</h2>
<p>The biggest mistake beginners make is opening the computer and creating tables before they understand what they are building. A good database designer always starts with a pencil and paper. Talk to the people who will use the database. Visit the shop, the clinic, or the school office. Write down every piece of information they currently record, even if it seems unimportant at first.</p>

<h2>Step 1: Identify the Entities</h2>
<p>An <strong>entity</strong> is a real-world thing that you need to store information about. In a small grocery shop, the entities might be:</p>
<ul>
<li><strong>Products</strong> – what the shop sells</li>
<li><strong>Suppliers</strong> – the people or companies that deliver stock</li>
<li><strong>Sales</strong> – each transaction when a customer buys something</li>
<li><strong>Customers</strong> – people who buy on credit or have loyalty accounts</li>
</ul>
<p>In a rural health clinic, the entities might be <strong>Patients</strong>, <strong>Visits</strong>, <strong>Medicines</strong>, and <strong>Health Workers</strong>. In a school, they might be <strong>Pupils</strong>, <strong>Parents</strong>, <strong>Classes</strong>, and <strong>Fees Payments</strong>.</p>

<h2>Step 2: List the Attributes</h2>
<p>An <strong>attribute</strong> is one fact about an entity. For the <strong>Patients</strong> entity at a clinic, the attributes might include:</p>
<ul>
<li>Full name</li>
<li>Date of birth</li>
<li>NRC number</li>
<li>Village or township</li>
<li>Guardian phone number</li>
<li>Date of first visit</li>
<li>Known allergies</li>
</ul>
<p>For the <strong>Products</strong> entity in a shop, attributes might be product name, category, supplier, cost price, selling price, and quantity in stock.</p>

<h2>Worked Example: Designing a Clinic Patient Database</h2>
<p>Nurse Tembo works at a rural health centre near Kalomo. She sees forty to sixty patients per day. Currently she writes each patient's details in a large register book. The book fills up every three months, and finding old records takes half an hour. She wants a simple database.</p>

<p>After interviewing Nurse Tembo, we identify three main entities:</p>
<ol>
<li><strong>Patients</strong> – people who visit the clinic</li>
<li><strong>Visits</strong> – each time a patient comes to the clinic</li>
<li><strong>Treatments</strong> – medicines or procedures given during a visit</li>
</ol>

<p>We then list the key attributes for each:</p>
<ul>
<li><strong>Patients:</strong> patient_id, full_name, date_of_birth, gender, village, guardian_phone, nrc_number</li>
<li><strong>Visits:</strong> visit_id, patient_id, visit_date, weight_kg, temperature_c, diagnosis_notes, nurse_name</li>
<li><strong>Treatments:</strong> treatment_id, visit_id, medicine_name, dosage, quantity_dispensed</li>
</ul>

<p>Notice that <strong>Visits</strong> has a <strong>patient_id</strong> foreign key, and <strong>Treatments</strong> has a <strong>visit_id</strong> foreign key. This creates a chain: one patient has many visits, and one visit has many treatments. Nurse Tembo can now search for any patient by name and see their entire visit history in seconds.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a real Zambian business or organisation you know well. It could be a market stall, a barber shop, a church, a farm, or a community school.</li>
<li>On paper, write down at least three entities that this organisation needs to track.</li>
<li>For each entity, list at least four attributes (facts) you would store.</li>
<li>Draw arrows to show which entities are related. For example, draw an arrow from Customers to Sales and label it "one customer makes many sales."</li>
<li>Show your diagram to a friend. Can they understand the business just by looking at your plan?</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Entity</strong>: A real-world object or concept about which data is stored, such as a patient, product, or sale.</li>
<li><strong>Attribute</strong>: A single fact or property of an entity, such as a patient's date of birth or a product's price.</li>
<li><strong>Entity-Relationship Diagram (ERD)</strong>: A simple drawing that shows entities as boxes and relationships as lines between them.</li>
<li><strong>Cardinality</strong>: A fancy word for "how many." In a one-to-many relationship, one record on one side matches many records on the other side.</li>
</ul>

<h2>Summary</h2>
<p>Good database design begins with understanding the real world. Start by identifying the entities your organisation cares about, then list the attributes for each entity, and finally sketch how the entities relate to one another. Only after this planning is complete should you open the computer and create tables. A few minutes of planning on paper can save hours of frustration later.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/sql/sql_create_table.asp">W3Schools – SQL CREATE TABLE</a> – How to turn your paper plan into real database tables.</li>
<li><a href="https://www.khanacademy.org/computing/computer-programming/sql">Khan Academy – SQL</a> – Practice designing tables and relationships.</li>
<li><a href="https://dev.mysql.com/doc/refman/8.0/en/create-table.html">MySQL Documentation – CREATE TABLE</a> – The official reference for creating tables in MySQL.</li>
</ul>
HTML,
            ],
            [
                'title' => '2.2 Creating Tables and Choosing Data Types',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to write a basic CREATE TABLE statement in SQL, choose appropriate data types for common Zambian business data, and set primary keys and NOT NULL constraints to keep your data clean from the very first day.</p>

<h2>From Paper Plan to SQL</h2>
<p>Once you have sketched your entities and attributes on paper, the next step is to translate that plan into SQL code that MySQL can understand. The command you need is <code>CREATE TABLE</code>. This command tells the database the name of the table, the names of the columns, the data type of each column, and any special rules such as which column is the primary key.</p>

<h2>The Basic Syntax</h2>
<p>Here is the simplest form of a CREATE TABLE statement:</p>
<pre><code>CREATE TABLE table_name (
    column_name DATA_TYPE CONSTRAINTS,
    column_name DATA_TYPE CONSTRAINTS,
    PRIMARY KEY (column_name)
);</code></pre>

<h2>Worked Example: A Stock Table for a Shop</h2>
<p>Let us return to Mama Ngosa's grocery shop in Kalomo. After planning, she decides her first table will be called <strong>products</strong>. Here is the SQL to create it:</p>

<pre><code>CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    supplier_name VARCHAR(100),
    cost_price DECIMAL(10,2) NOT NULL,
    selling_price DECIMAL(10,2) NOT NULL,
    quantity_in_stock INT NOT NULL DEFAULT 0,
    reorder_level INT DEFAULT 10
);</code></pre>

<p>Let us break this down line by line:</p>
<ul>
<li><strong>product_id INT AUTO_INCREMENT PRIMARY KEY</strong> – This creates a whole-number column that automatically increases by one for each new product. It is the primary key, so every product gets a unique number.</li>
<li><strong>product_name VARCHAR(100) NOT NULL</strong> – This stores up to one hundred characters of text. <code>NOT NULL</code> means the database will refuse to insert a product if the name is missing.</li>
<li><strong>category VARCHAR(50) NOT NULL</strong> – Groups products into categories such as "food", "cleaning", or "airtime".</li>
<li><strong>supplier_name VARCHAR(100)</strong> – No <code>NOT NULL</code> here because some products might be self-produced or have an unknown supplier.</li>
<li><strong>cost_price DECIMAL(10,2) NOT NULL</strong> – Stores money with two decimal places. The <code>10</code> means up to ten digits total.</li>
<li><strong>quantity_in_stock INT NOT NULL DEFAULT 0</strong> – Starts at zero if no quantity is provided.</li>
<li><strong>reorder_level INT DEFAULT 10</strong> – When stock drops below this number, Mama Ngosa knows it is time to reorder.</li>
</ul>

<h2>Common Constraints Explained</h2>
<ul>
<li><strong>PRIMARY KEY</strong> – Uniquely identifies each row. Only one per table.</li>
<li><strong>NOT NULL</strong> – The column must have a value. Empty values are rejected.</li>
<li><strong>UNIQUE</strong> – No two rows can have the same value in this column, but it is not the primary key.</li>
<li><strong>DEFAULT</strong> – Automatically fills in a value if none is provided.</li>
<li><strong>FOREIGN KEY</strong> – Links this column to a primary key in another table.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>On paper, write a CREATE TABLE statement for a <strong>customers</strong> table. Include these columns: customer_id, full_name, phone_number, town, credit_limit.</li>
<li>Decide which data type each column should use and mark which columns should be NOT NULL.</li>
<li>Make customer_id an auto-incrementing primary key.</li>
<li>Write one sentence explaining why phone_number should be VARCHAR rather than INT.</li>
<li>If you have access to a computer with XAMPP or MySQL installed, open phpMyAdmin, click the SQL tab, and type your CREATE TABLE statement. Click Go and check that the table is created without errors.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>CREATE TABLE</strong>: An SQL command that creates a new empty table with specified columns and rules.</li>
<li><strong>Constraint</strong>: A rule applied to a column, such as NOT NULL or PRIMARY KEY, that limits what data can be entered.</li>
<li><strong>NOT NULL</strong>: A constraint that prevents a column from being left empty.</li>
<li><strong>DEFAULT</strong>: A value that the database inserts automatically if the user does not provide one.</li>
<li><strong>AUTO_INCREMENT</strong>: A MySQL feature that automatically generates the next sequential number for a primary key.</li>
</ul>

<h2>Summary</h2>
<p>The CREATE TABLE statement turns your paper plan into a real database structure. Choosing the right data types and constraints from the start protects your data from common mistakes such as missing names, negative prices, or duplicate IDs. A well-designed table makes every future query faster and every report more reliable. Take your time with this step, because fixing a table design after it is full of real data is much harder than getting it right the first time.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/sql/sql_create_table.asp">W3Schools – SQL CREATE TABLE</a> – Step-by-step examples of creating tables with constraints.</li>
<li><a href="https://www.w3schools.com/sql/sql_notnull.asp">W3Schools – SQL NOT NULL</a> – How and why to use the NOT NULL constraint.</li>
<li><a href="https://dev.mysql.com/doc/refman/8.0/en/create-table.html">MySQL Documentation – CREATE TABLE Syntax</a> – Complete reference for all table options.</li>
</ul>
HTML,
            ],
            [
                'title' => '2.3 Normalisation and Avoiding Data Duplication',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what normalisation is and why it matters, identify duplicate data in a poorly designed table, and reorganise a flat table into two or more related tables that follow the first and second normal forms.</p>

<h2>What Is Normalisation?</h2>
<p><strong>Normalisation</strong> is the process of organising data in a database so that duplication is reduced and relationships are properly defined. A normalised database is easier to update, less likely to contain contradictions, and takes up less storage space. The goal is simple: every piece of information should be stored in exactly one place. If you need to change a supplier's phone number, you should only change it in one table, not in twenty different product records.</p>

<h2>The Problem with Duplication</h2>
<p>Imagine a hardware shop that stores all its information in one giant table called <strong>sales</strong>. Every time a sale is made, the shopkeeper writes the customer name, customer phone, product name, supplier name, supplier phone, quantity, and price all on the same line. After one hundred sales, the supplier's phone number appears one hundred times. If the supplier changes their number, the shopkeeper must find and update every single row. If even one row is missed, the database now contains two different phone numbers for the same supplier, and nobody knows which one is correct.</p>

<h2>First Normal Form (1NF): Atomic Values</h2>
<p>A table is in <strong>First Normal Form</strong> when every cell contains only one value, and there are no repeating groups of columns. For example, a pupil record should not have columns called <code>subject_1</code>, <code>subject_2</code>, and <code>subject_3</code>. Instead, there should be a separate table where each row links one pupil to one subject. This makes it easy to add a fourth subject without redesigning the table.</p>

<h2>Second Normal Form (2NF): No Partial Dependencies</h2>
<p>A table is in <strong>Second Normal Form</strong> when it is already in 1NF and every non-key column depends on the <em>whole</em> primary key, not just part of it. In practice, this usually means removing columns that describe something other than the main entity of the table.</p>

<h2>Worked Example: A Shop Receipt Table Before and After Normalisation</h2>

<h3>Before Normalisation (One Big Table)</h3>
<table>
<thead>
<tr><th>sale_id</th><th>date</th><th>customer</th><th>product</th><th>supplier</th><th>supplier_phone</th><th>qty</th><th>price</th></tr>
</thead>
<tbody>
<tr><td>1</td><td>2026-06-01</td><td>Mr. Banda</td><td>Cement</td><td>BuildIt Ltd</td><td>0965123456</td><td>10</td><td>1200.00</td></tr>
<tr><td>2</td><td>2026-06-02</td><td>Mrs. Zulu</td><td>Cement</td><td>BuildIt Ltd</td><td>0965123456</td><td>5</td><td>600.00</td></tr>
<tr><td>3</td><td>2026-06-02</td><td>Mr. Banda</td><td>Nails</td><td>Hardware Plus</td><td>0977123456</td><td>2</td><td>80.00</td></tr>
</tbody>
</table>

<p>Problems: BuildIt Ltd's phone number is duplicated. If it changes, two rows must be updated. The supplier information does not really belong in a table about sales.</p>

<h3>After Normalisation (Three Related Tables)</h3>

<p><strong>customers</strong> table:</p>
<table>
<thead>
<tr><th>customer_id</th><th>customer_name</th><th>phone</th></tr>
</thead>
<tbody>
<tr><td>1</td><td>Mr. Banda</td><td>0955123456</td></tr>
<tr><td>2</td><td>Mrs. Zulu</td><td>0966123456</td></tr>
</tbody>
</table>

<p><strong>products</strong> table:</p>
<table>
<thead>
<tr><th>product_id</th><th>product_name</th><th>supplier_id</th><th>unit_price</th></tr>
</thead>
<tbody>
<tr><td>1</td><td>Cement</td><td>1</td><td>120.00</td></tr>
<tr><td>2</td><td>Nails</td><td>2</td><td>40.00</td></tr>
</tbody>
</table>

<p><strong>suppliers</strong> table:</p>
<table>
<thead>
<tr><th>supplier_id</th><th>supplier_name</th><th>phone</th></tr>
</thead>
<tbody>
<tr><td>1</td><td>BuildIt Ltd</td><td>0965123456</td></tr>
<tr><td>2</td><td>Hardware Plus</td><td>0977123456</td></tr>
</tbody>
</table>

<p><strong>sales</strong> table:</p>
<table>
<thead>
<tr><th>sale_id</th><th>date</th><th>customer_id</th><th>product_id</th><th>quantity</th></tr>
</thead>
<tbody>
<tr><td>1</td><td>2026-06-01</td><td>1</td><td>1</td><td>10</td></tr>
<tr><td>2</td><td>2026-06-02</td><td>2</td><td>1</td><td>5</td></tr>
<tr><td>3</td><td>2026-06-02</td><td>1</td><td>2</td><td>2</td></tr>
</tbody>
</table>

<p>Now the supplier phone number exists in only one place. If BuildIt Ltd changes their number, only one row in the suppliers table needs updating. The sales table is smaller and faster to search because it only stores IDs, not long text strings.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Draw the "before" table from the worked example on your own paper.</li>
<li>Circle every piece of information that is duplicated.</li>
<li>Redraw the data as three separate tables: <strong>customers</strong>, <strong>products</strong>, and <strong>sales</strong>. Add a <strong>suppliers</strong> table if you wish.</li>
<li>Write one sentence explaining what would happen if you deleted a supplier from the suppliers table but left their products in the products table.</li>
<li>Look around your own life. Can you find a real example where the same information is written down in more than one place? How could a database prevent that?</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Normalisation</strong>: The process of organising tables to reduce duplication and improve data integrity.</li>
<li><strong>First Normal Form (1NF)</strong>: A table is in 1NF when each cell contains a single value and there are no repeating groups.</li>
<li><strong>Second Normal Form (2NF)</strong>: A table is in 2NF when it is in 1NF and every non-key column depends on the entire primary key.</li>
<li><strong>Duplication (Redundancy)</strong>: Storing the same piece of information in more than one place, which wastes space and causes inconsistencies.</li>
<li><strong>Atomic Value</strong>: A single indivisible piece of data in one table cell, such as one phone number rather than a comma-separated list.</li>
</ul>

<h2>Summary</h2>
<p>Normalisation removes duplicate data by splitting large tables into smaller, focused tables that are linked by foreign keys. First Normal Form requires atomic values and no repeating groups. Second Normal Form removes partial dependencies by ensuring every column describes the main entity of the table. A normalised database is smaller, faster, and far easier to maintain, which is especially important for small Zambian businesses that cannot afford to waste time fixing data errors.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/sql/sql_create_table.asp">W3Schools – SQL CREATE TABLE</a> – Practice creating the individual tables you need for normalisation.</li>
<li><a href="https://www.khanacademy.org/computing/computer-programming/sql">Khan Academy – SQL</a> – Interactive exercises on relational data design.</li>
<li><a href="https://dev.mysql.com/doc/refman/8.0/en/data-types.html">MySQL Documentation – Data Types</a> – Reference for choosing types when designing normalised tables.</li>
</ul>
HTML,
            ],
            [
                'title' => '2.4 Module 2 Quiz: Designing a Real-World Database',
                'type' => 'Quiz',
                'duration_minutes' => 20,
                'content' => '<p>Complete this quiz to test your understanding of database planning, table creation, data types, and normalisation.</p>',
            ],
        ];
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Designing a Real-World Database',
            'description' => 'Test your knowledge of planning entities, choosing data types, creating tables, and avoiding duplication through normalisation.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In database design, what is an "entity"?',
                    'explanation' => 'An entity is a real-world object or concept about which the database stores information, such as a patient, product, or student.',
                    'options' => [
                        ['text' => 'A real-world object or concept the database stores information about', 'is_correct' => true],
                        ['text' => 'A type of computer virus', 'is_correct' => false],
                        ['text' => 'A command used to delete data', 'is_correct' => false],
                        ['text' => 'A colour scheme for the database interface', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which SQL constraint ensures a column cannot be left empty?',
                    'explanation' => 'The NOT NULL constraint rejects any attempt to insert a row without providing a value for that column.',
                    'options' => [
                        ['text' => 'NOT NULL', 'is_correct' => true],
                        ['text' => 'PRIMARY KEY', 'is_correct' => false],
                        ['text' => 'UNIQUE', 'is_correct' => false],
                        ['text' => 'DEFAULT', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does AUTO_INCREMENT do in MySQL?',
                    'explanation' => 'AUTO_INCREMENT automatically generates the next sequential number for a primary key column, saving you from manually choosing IDs.',
                    'options' => [
                        ['text' => 'Automatically generates the next number for a primary key', 'is_correct' => true],
                        ['text' => 'Automatically deletes old records', 'is_correct' => false],
                        ['text' => 'Automatically backs up the database', 'is_correct' => false],
                        ['text' => 'Automatically encrypts passwords', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'First Normal Form (1NF) requires that each cell in a table contains only a single value.',
                    'explanation' => '1NF requires atomic values, meaning no cell should contain a list or multiple values separated by commas.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'In a normalised database, the same supplier phone number should be stored in every product row supplied by that company.',
                    'explanation' => 'Normalisation aims to store each piece of information in exactly one place. The supplier phone should live in the suppliers table only.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which data type is best for storing a patient\'s date of birth in MySQL?',
                    'explanation' => 'The DATE data type stores calendar values in the standard YYYY-MM-DD format, which is ideal for dates of birth.',
                    'options' => [
                        ['text' => 'DATE', 'is_correct' => true],
                        ['text' => 'VARCHAR', 'is_correct' => false],
                        ['text' => 'INT', 'is_correct' => false],
                        ['text' => 'DECIMAL', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the process called that organises database tables to reduce duplication? (one word)',
                    'explanation' => 'Normalisation is the process of structuring tables to minimise redundancy and dependency.',
                    'correct_answer' => 'Normalisation',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'A shop has a products table and a separate suppliers table linked by supplier_id. What is this an example of?',
                    'explanation' => 'This is an example of normalisation, where related but distinct information is split into separate linked tables to avoid duplication.',
                    'options' => [
                        ['text' => 'Normalisation', 'is_correct' => true],
                        ['text' => 'A spreadsheet', 'is_correct' => false],
                        ['text' => 'A computer virus', 'is_correct' => false],
                        ['text' => 'A primary key conflict', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'When creating a table for school fees payments, which column is most likely to be a foreign key?',
                    'explanation' => 'The pupil_id column in a fees_payments table would reference the primary key of the pupils table, making it a foreign key.',
                    'options' => [
                        ['text' => 'pupil_id referencing the pupils table', 'is_correct' => true],
                        ['text' => 'The amount paid', 'is_correct' => false],
                        ['text' => 'The date of payment', 'is_correct' => false],
                        ['text' => 'The receipt number', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    /* ==================================================================== */
    /*  Module 3 – SQL and MySQL Basics                                     */
    /* ==================================================================== */

    private function module3Lessons(): array
    {
        return [
            [
                'title' => '3.1 Introduction to SQL and MySQL',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what SQL is and why it is the universal language of databases, describe what MySQL is and where it is used in Zambia and around the world, and open either the MySQL command line or phpMyAdmin and run your first simple command.</p>

<h2>What Is SQL?</h2>
<p><strong>SQL</strong> stands for <strong>Structured Query Language</strong>. It is the standard language used to communicate with relational databases such as MySQL, PostgreSQL, Microsoft SQL Server, and Oracle. Think of SQL as the middleman between you and the database. You write instructions in English-like sentences, and the database translates those instructions into actions such as creating tables, inserting data, or generating reports.</p>
<p>SQL is not a programming language like Python or Java in the traditional sense. You cannot use SQL to build a mobile app or a website on its own. However, almost every app, website, and business system that stores data uses SQL behind the scenes. When you check your Airtel Money balance, SQL queries run in the background. When a nurse at a clinic looks up your patient record, SQL is doing the work. When the ZRA system generates a tax statement, SQL is retrieving and calculating the numbers.</p>

<h2>What Is MySQL?</h2>
<p><strong>MySQL</strong> is a free, open-source database management system that understands SQL. It was created in Sweden in the 1990s and is now owned by Oracle Corporation. MySQL is the most popular database system on the internet. It powers Facebook, Twitter, YouTube, WordPress, and millions of smaller websites and applications.</p>
<p>In Zambia, MySQL is an excellent choice for small businesses, schools, and clinics because it costs nothing to download and use. It runs on Windows, Linux, and macOS. At Edutrack Computer Training College, MySQL is installed on the college computers as part of the XAMPP package, which bundles MySQL with Apache and PHP.</p>

<h2>Two Ways to Talk to MySQL</h2>
<p>There are two main ways beginners interact with MySQL:</p>

<h3>1. The Command Line</h3>
<p>The command line is a black window where you type SQL commands directly. It looks old-fashioned, but it is powerful and fast. On a college computer with XAMPP installed, you can open the XAMPP Control Panel, start MySQL, and then click "Shell" to open a command window. From there you type:</p>
<pre><code>mysql -u root -p</code></pre>
<p>Press Enter, and you are connected to the MySQL server. You can now create databases, run queries, and manage users.</p>

<h3>2. phpMyAdmin</h3>
<p>phpMyAdmin is a web-based tool that gives you a graphical interface for MySQL. You click buttons instead of typing every command. It is excellent for beginners because you can see your tables, browse data, and run SQL queries in a friendly environment. To open phpMyAdmin on a college computer, start Apache and MySQL in the XAMPP Control Panel, then open a browser and visit:</p>
<pre><code>http://localhost/phpmyadmin</code></pre>

<h2>Worked Example: Creating Your First Database</h2>
<p>Let us create a database for a small poultry business called "Kalomo Poultry." Open phpMyAdmin and follow these steps:</p>
<ol>
<li>Click <strong>New</strong> on the left sidebar.</li>
<li>Type <code>kalomo_poultry</code> in the Database name box.</li>
<li>Click <strong>Create</strong>.</li>
<li>You will see a message saying the database has been created. Congratulations, you have just run the equivalent of the SQL command: <code>CREATE DATABASE kalomo_poultry;</code></li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>If you have access to a computer with XAMPP installed, start Apache and MySQL from the XAMPP Control Panel.</li>
<li>Open your web browser and go to <code>http://localhost/phpmyadmin</code>.</li>
<li>Click <strong>New</strong> and create a database named <code>my_first_db</code>.</li>
<li>Click on your new database in the left sidebar. You should see a message saying "No tables found in database."</li>
<li>Click the <strong>SQL</strong> tab and type: <code>SHOW DATABASES;</code> then click <strong>Go</strong>.</li>
<li>Look at the list of databases. Can you see <code>my_first_db</code> in the list?</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>SQL</strong>: Structured Query Language, the standard language for interacting with relational databases.</li>
<li><strong>MySQL</strong>: A popular free and open-source relational database management system.</li>
<li><strong>Command Line</strong>: A text-only interface where you type commands directly to the computer.</li>
<li><strong>phpMyAdmin</strong>: A free web-based tool that provides a graphical interface for managing MySQL databases.</li>
<li><strong>XAMPP</strong>: A software package that bundles Apache, MySQL, PHP, and Perl for easy local web development.</li>
</ul>

<h2>Summary</h2>
<p>SQL is the universal language for asking questions of and giving instructions to relational databases. MySQL is a free database system that understands SQL and is used by millions of websites and businesses worldwide. Beginners can interact with MySQL through the command line for speed and power, or through phpMyAdmin for a friendly graphical experience. Installing XAMPP on a college computer or laptop gives you everything you need to practise SQL for free.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/sql/">W3Schools – SQL Tutorial</a> – A complete beginner-friendly SQL tutorial with an online practice editor.</li>
<li><a href="https://dev.mysql.com/doc/refman/8.0/en/">MySQL Reference Manual</a> – The official documentation for MySQL.</li>
<li><a href="https://www.phpmyadmin.net/docs/">phpMyAdmin Documentation</a> – Official guides for using the phpMyAdmin web interface.</li>
</ul>
HTML,
            ],
            [
                'title' => '3.2 SELECT, WHERE, and ORDER BY',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to write SELECT statements to retrieve data from a single table, use the WHERE clause to filter results based on conditions, and sort results with ORDER BY so that the most important information appears first.</p>

<h2>The SELECT Statement</h2>
<p>The <code>SELECT</code> statement is the most common SQL command. It asks the database to show you data from one or more tables. The simplest form is:</p>
<pre><code>SELECT column_name FROM table_name;</code></pre>
<p>If you want to see every column, use an asterisk:</p>
<pre><code>SELECT * FROM table_name;</code></pre>

<h2>Filtering with WHERE</h2>
<p>The <code>WHERE</code> clause lets you set conditions. Only rows that meet the condition are returned. Common operators include:</p>
<ul>
<li><code>=</code> Equal to</li>
<li><code>&lt;&gt;</code> or <code>!=</code> Not equal to</li>
<li><code>&gt;</code> Greater than</li>
<li><code>&lt;</code> Less than</li>
<li><code>&gt;=</code> Greater than or equal to</li>
<li><code>&lt;=</code> Less than or equal to</li>
<li><code>LIKE</code> Matches a pattern (use <code>%</code> as a wildcard)</li>
<li><code>BETWEEN</code> Within a range</li>
</ul>

<h2>Sorting with ORDER BY</h2>
<p>The <code>ORDER BY</code> clause sorts your results. By default it sorts in ascending order (A to Z, smallest to largest). Add <code>DESC</code> for descending order (Z to A, largest to smallest).</p>

<h2>Worked Example: Managing Patients at a Clinic</h2>
<p>Imagine a clinic database with a <strong>patients</strong> table. Here are some realistic queries the clinic staff might run:</p>

<h3>Example 1: List all patients from Kalomo</h3>
<pre><code>SELECT full_name, phone_number
FROM patients
WHERE town = 'Kalomo';</code></pre>

<h3>Example 2: Find patients born before 1990</h3>
<pre><code>SELECT full_name, date_of_birth
FROM patients
WHERE date_of_birth &lt; '1990-01-01';</code></pre>

<h3>Example 3: List all patients sorted alphabetically by name</h3>
<pre><code>SELECT full_name, town
FROM patients
ORDER BY full_name ASC;</code></pre>

<h3>Example 4: Find patients whose names start with "M"</h3>
<pre><code>SELECT full_name, town
FROM patients
WHERE full_name LIKE 'M%';</code></pre>

<h3>Example 5: Find patients with a balance owed greater than K500</h3>
<pre><code>SELECT full_name, balance_owed
FROM patients
WHERE balance_owed &gt; 500
ORDER BY balance_owed DESC;</code></pre>

<h2>Try It Yourself</h2>
<ol>
<li>Open phpMyAdmin and select the <code>kalomo_poultry</code> database you created earlier, or any database with a table containing data.</li>
<li>Click the <strong>SQL</strong> tab and type: <code>SELECT * FROM your_table_name;</code> Replace <code>your_table_name</code> with the actual name of a table. Click <strong>Go</strong> and observe the results.</li>
<li>Now add a WHERE clause. Type: <code>SELECT * FROM your_table_name WHERE some_column = 'some_value';</code> Replace the column and value with real ones from your table.</li>
<li>Try sorting the results. Type: <code>SELECT * FROM your_table_name ORDER BY some_column DESC;</code></li>
<li>Write down three real questions a shopkeeper, nurse, or teacher might ask that could be answered with SELECT, WHERE, and ORDER BY.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>SELECT</strong>: The SQL command used to retrieve data from one or more tables.</li>
<li><strong>WHERE</strong>: A clause that filters rows based on a condition.</li>
<li><strong>ORDER BY</strong>: A clause that sorts the result set by one or more columns.</li>
<li><strong>Wildcard</strong>: A special character such as <code>%</code> that matches any sequence of characters in a LIKE pattern.</li>
<li><strong>Operator</strong>: A symbol such as <code>=</code>, <code>&gt;</code>, or <code>&lt;</code> used to compare values in a condition.</li>
</ul>

<h2>Summary</h2>
<p>SELECT is the foundation of every database query. It retrieves the data you want. WHERE narrows the results to only the rows that matter. ORDER BY arranges the results so the most relevant information appears first. Together, these three commands allow you to answer almost any simple question about your data, from "Which patients owe money?" to "What products are running low?" Master these three commands before moving on, because every advanced query is built from these simple building blocks.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/sql/sql_select.asp">W3Schools – SQL SELECT</a> – The basics of retrieving data with examples.</li>
<li><a href="https://www.w3schools.com/sql/sql_where.asp">W3Schools – SQL WHERE</a> – How to filter data using conditions.</li>
<li><a href="https://www.w3schools.com/sql/sql_orderby.asp">W3Schools – SQL ORDER BY</a> – Sorting your query results.</li>
<li><a href="https://www.khanacademy.org/computing/computer-programming/sql">Khan Academy – SQL</a> – Interactive practice with SELECT, WHERE, and ORDER BY.</li>
</ul>
HTML,
            ],
            [
                'title' => '3.3 INSERT, UPDATE, and DELETE',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to add new records to a table using INSERT, modify existing records with UPDATE, and remove records using DELETE. You will also understand the dangers of careless DELETE and UPDATE commands and learn how to use WHERE to protect your data.</p>

<h2>Adding Data with INSERT</h2>
<p>The <code>INSERT INTO</code> statement adds a new row to a table. You specify the table name, the columns you want to fill, and the values for those columns.</p>
<pre><code>INSERT INTO table_name (column1, column2, column3)
VALUES (value1, value2, value3);</code></pre>

<h2>Changing Data with UPDATE</h2>
<p>The <code>UPDATE</code> statement changes existing data. You must always include a <code>WHERE</code> clause to specify which rows to change. Without WHERE, every single row in the table will be updated, which is almost never what you want.</p>
<pre><code>UPDATE table_name
SET column1 = new_value
WHERE condition;</code></pre>

<h2>Removing Data with DELETE</h2>
<p>The <code>DELETE</code> statement removes rows from a table. Like UPDATE, it must include a WHERE clause. If you forget WHERE, you will delete every row in the table. There is no Undo button in SQL.</p>
<pre><code>DELETE FROM table_name
WHERE condition;</code></pre>

<h2>Worked Example: Managing Stock for a Small Shop</h2>
<p>Mama Ngosa's grocery shop uses a <strong>products</strong> table. Here is how she manages her stock with INSERT, UPDATE, and DELETE:</p>

<h3>Example 1: Adding a new product</h3>
<p>Mama Ngosa starts selling a new brand of cooking oil. She adds it to the database:</p>
<pre><code>INSERT INTO products (product_name, category, cost_price, selling_price, quantity_in_stock)
VALUES ('Sunflower Oil 5L', 'Cooking', 145.00, 180.00, 24);</code></pre>

<h3>Example 2: Updating stock after a delivery</h3>
<p>A delivery truck arrives with twenty more bags of mealie meal. The product_id for mealie meal is 3:</p>
<pre><code>UPDATE products
SET quantity_in_stock = quantity_in_stock + 20
WHERE product_id = 3;</code></pre>

<h3>Example 3: Updating a price</h3>
<p>The supplier has increased the price of sugar. Mama Ngosa raises her selling price from K85 to K95:</p>
<pre><code>UPDATE products
SET selling_price = 95.00
WHERE product_name = 'White Sugar 2kg';</code></pre>

<h3>Example 4: Deleting a discontinued product</h3>
<p>A certain brand of soap is no longer available. The product_id is 7:</p>
<pre><code>DELETE FROM products
WHERE product_id = 7;</code></pre>

<h2>A Warning About DELETE and UPDATE</h2>
<p>Before running any DELETE or UPDATE, run a SELECT with the same WHERE clause first. This shows you exactly which rows will be affected. For example:</p>
<pre><code>SELECT * FROM products WHERE product_id = 7;</code></pre>
<p>If the result shows exactly the product you want to remove, then and only then should you run the DELETE. This simple habit has saved millions of database users from accidental disasters.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open phpMyAdmin, select a database, and click the <strong>SQL</strong> tab.</li>
<li>Write an INSERT statement to add a new row to any table. Check that the row appears when you browse the table.</li>
<li>Write an UPDATE statement that changes one column in exactly one row. Use a WHERE clause with the primary key to be safe.</li>
<li>Before deleting anything, write a SELECT statement with the same WHERE clause you plan to use for DELETE. Confirm it returns only the row you want to remove.</li>
<li>Write one sentence explaining why UPDATE without a WHERE clause is dangerous in a clinic database.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>INSERT</strong>: An SQL command that adds one or more new rows to a table.</li>
<li><strong>UPDATE</strong>: An SQL command that changes existing data in one or more rows.</li>
<li><strong>DELETE</strong>: An SQL command that removes one or more rows from a table.</li>
<li><strong>SET</strong>: The keyword in an UPDATE statement that specifies which columns to change and what their new values should be.</li>
<li><strong>WHERE clause</strong>: A condition that limits which rows are affected by SELECT, UPDATE, or DELETE.</li>
</ul>

<h2>Summary</h2>
<p>INSERT adds new data, UPDATE changes existing data, and DELETE removes data. These three commands are powerful but potentially dangerous. Always include a WHERE clause in UPDATE and DELETE, and always preview your changes with SELECT first. In a real Zambian business, a careless DELETE could erase years of sales history, and a careless UPDATE could change every price in the shop. Develop safe habits now, and they will protect your data for the rest of your career.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/sql/sql_insert.asp">W3Schools – SQL INSERT</a> – How to add new rows with examples.</li>
<li><a href="https://www.w3schools.com/sql/sql_update.asp">W3Schools – SQL UPDATE</a> – Changing existing data safely.</li>
<li><a href="https://www.w3schools.com/sql/sql_delete.asp">W3Schools – SQL DELETE</a> – Removing rows and common pitfalls.</li>
<li><a href="https://www.khanacademy.org/computing/computer-programming/sql">Khan Academy – SQL</a> – Practice exercises on modifying data.</li>
</ul>
HTML,
            ],
            [
                'title' => '3.4 JOINs: Linking Tables Together',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain why data is split across multiple tables, write an INNER JOIN query to combine data from two related tables, and read a joined result set that shows meaningful information from both tables at once.</p>

<h2>Why Split Data Across Tables?</h2>
<p>In Module 2 you learned about normalisation: storing each piece of information in exactly one place. This means a well-designed database has many small tables rather than one giant table. But when you want to see information from more than one table at the same time, you need a way to put the pieces back together. That way is called a <strong>JOIN</strong>.</p>

<h2>What Is a JOIN?</h2>
<p>A JOIN is an SQL operation that combines rows from two or more tables based on a related column between them. The most common type is the <strong>INNER JOIN</strong>, which returns only rows where the matching value exists in <em>both</em> tables.</p>

<h2>The INNER JOIN Syntax</h2>
<pre><code>SELECT columns
FROM table1
INNER JOIN table2
ON table1.common_column = table2.common_column;</code></pre>

<h2>Worked Example: Linking Customers and Sales</h2>
<p>Mama Ngosa's shop has two tables. The <strong>customers</strong> table stores customer details, and the <strong>sales</strong> table stores each transaction. The tables are linked by the <strong>customer_id</strong> column.</p>

<p><strong>customers</strong> table:</p>
<table>
<thead>
<tr><th>customer_id</th><th>full_name</th><th>town</th></tr>
</thead>
<tbody>
<tr><td>1</td><td>Mr. Banda</td><td>Kalomo</td></tr>
<tr><td>2</td><td>Mrs. Zulu</td><td>Monze</td></tr>
<tr><td>3</td><td>Ms. Mwanza</td><td>Kalomo</td></tr>
</tbody>
</table>

<p><strong>sales</strong> table:</p>
<table>
<thead>
<tr><th>sale_id</th><th>customer_id</th><th>sale_date</th><th>amount</th></tr>
</thead>
<tbody>
<tr><td>101</td><td>1</td><td>2026-06-01</td><td>450.00</td></tr>
<tr><td>102</td><td>2</td><td>2026-06-02</td><td>200.00</td></tr>
<tr><td>103</td><td>1</td><td>2026-06-03</td><td>120.00</td></tr>
<tr><td>104</td><td>3</td><td>2026-06-04</td><td>800.00</td></tr>
</tbody>
</table>

<p>Mama Ngosa wants a report showing each sale with the customer's name, not just the customer_id. Here is the query:</p>
<pre><code>SELECT sales.sale_id, customers.full_name, sales.sale_date, sales.amount
FROM sales
INNER JOIN customers
ON sales.customer_id = customers.customer_id;</code></pre>

<p>The result looks like this:</p>
<table>
<thead>
<tr><th>sale_id</th><th>full_name</th><th>sale_date</th><th>amount</th></tr>
</thead>
<tbody>
<tr><td>101</td><td>Mr. Banda</td><td>2026-06-01</td><td>450.00</td></tr>
<tr><td>102</td><td>Mrs. Zulu</td><td>2026-06-02</td><td>200.00</td></tr>
<tr><td>103</td><td>Mr. Banda</td><td>2026-06-03</td><td>120.00</td></tr>
<tr><td>104</td><td>Ms. Mwanza</td><td>2026-06-04</td><td>800.00</td></tr>
</tbody>
</table>

<p>Notice that Mr. Banda appears twice because he made two separate sales. The INNER JOIN correctly matched each sale to the right customer by comparing the customer_id in both tables.</p>

<h2>Try It Yourself</h2>
<ol>
<li>In phpMyAdmin, create two simple tables: <strong>teachers</strong> (teacher_id, teacher_name, subject) and <strong>classes</strong> (class_id, class_name, teacher_id).</li>
<li>Insert at least three teachers and three classes. Make sure at least two classes share the same teacher_id.</li>
<li>Write an INNER JOIN query that shows the class_name and teacher_name together.</li>
<li>Add a WHERE clause to your JOIN so it only shows classes taught by a specific teacher.</li>
<li>Write one sentence explaining what would happen if you tried to JOIN on a column that does not exist in one of the tables.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>JOIN</strong>: An SQL operation that combines rows from two or more tables based on a related column.</li>
<li><strong>INNER JOIN</strong>: A join that returns only rows where the matching value exists in both tables.</li>
<li><strong>ON</strong>: The keyword that specifies which columns to match when joining two tables.</li>
<li><strong>Alias</strong>: A short nickname for a table name, often used in JOINs to make queries easier to read. Example: <code>FROM sales s</code>.</li>
<li><strong>Result Set</strong>: The table of data returned by a SELECT or JOIN query.</li>
</ul>

<h2>Summary</h2>
<p>JOINs are the bridge that connects normalised tables back together into meaningful information. The INNER JOIN is the most common type: it matches rows from two tables where the key columns are equal. Without JOINs, you would have to store duplicate data in giant flat tables. With JOINs, you can keep your database clean and efficient while still producing reports that show names, dates, amounts, and relationships all in one view. Every database professional uses JOINs daily, so practise them until they feel natural.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/sql/sql_join.asp">W3Schools – SQL JOIN</a> – Visual explanations of INNER JOIN, LEFT JOIN, and more.</li>
<li><a href="https://www.w3schools.com/sql/sql_join_inner.asp">W3Schools – SQL INNER JOIN</a> – Focused examples of the most common join type.</li>
<li><a href="https://www.khanacademy.org/computing/computer-programming/sql">Khan Academy – SQL</a> – Interactive exercises on joining tables.</li>
</ul>
HTML,
            ],
            [
                'title' => '3.5 Module 3 Quiz: SQL and MySQL Basics',
                'type' => 'Quiz',
                'duration_minutes' => 25,
                'content' => '<p>Complete this quiz to test your understanding of SQL syntax, SELECT, WHERE, ORDER BY, INSERT, UPDATE, DELETE, and JOINs.</p>',
            ],
        ];
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: SQL and MySQL Basics',
            'description' => 'Test your knowledge of SQL commands for retrieving, adding, updating, deleting, and linking data in MySQL.',
            'time_limit_minutes' => 25,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does SQL stand for?',
                    'explanation' => 'SQL stands for Structured Query Language, the standard language for managing data in relational databases.',
                    'options' => [
                        ['text' => 'Structured Query Language', 'is_correct' => true],
                        ['text' => 'Simple Question Language', 'is_correct' => false],
                        ['text' => 'System Quality Level', 'is_correct' => false],
                        ['text' => 'Standard Query Logic', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which SQL command is used to retrieve data from a table?',
                    'explanation' => 'SELECT is the command used to query and retrieve data from one or more tables.',
                    'options' => [
                        ['text' => 'SELECT', 'is_correct' => true],
                        ['text' => 'INSERT', 'is_correct' => false],
                        ['text' => 'UPDATE', 'is_correct' => false],
                        ['text' => 'DELETE', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which clause filters rows so only matching records are returned?',
                    'explanation' => 'The WHERE clause sets conditions that rows must meet to be included in the result set.',
                    'options' => [
                        ['text' => 'WHERE', 'is_correct' => true],
                        ['text' => 'ORDER BY', 'is_correct' => false],
                        ['text' => 'GROUP BY', 'is_correct' => false],
                        ['text' => 'HAVING', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'It is safe to run UPDATE without a WHERE clause if you are sure you want to change every row.',
                    'explanation' => 'While technically possible, running UPDATE without WHERE is extremely risky. Always double-check with SELECT first.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'An INNER JOIN returns only rows where the matching value exists in both tables.',
                    'explanation' => 'This is the definition of an INNER JOIN. It excludes rows from either table that do not have a match.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which SQL command adds a new row to a table?',
                    'explanation' => 'INSERT INTO is the command used to add new records to a database table.',
                    'options' => [
                        ['text' => 'INSERT INTO', 'is_correct' => true],
                        ['text' => 'ADD ROW', 'is_correct' => false],
                        ['text' => 'CREATE RECORD', 'is_correct' => false],
                        ['text' => 'UPDATE', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'Which SQL keyword sorts query results in ascending or descending order? (one word)',
                    'explanation' => 'ORDER BY sorts the result set. Add ASC for ascending or DESC for descending.',
                    'correct_answer' => 'ORDER BY',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Before running a DELETE command, what is the safest thing to do first?',
                    'explanation' => 'Running a SELECT with the same WHERE clause shows exactly which rows will be deleted, preventing accidental data loss.',
                    'options' => [
                        ['text' => 'Run SELECT with the same WHERE clause to preview the rows', 'is_correct' => true],
                        ['text' => 'Restart the computer', 'is_correct' => false],
                        ['text' => 'Delete the entire database and restore it', 'is_correct' => false],
                        ['text' => 'Ask a friend to watch', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In a JOIN query, what does the ON keyword do?',
                    'explanation' => 'ON specifies the matching columns from each table that the database should compare to link rows together.',
                    'options' => [
                        ['text' => 'It specifies which columns to match between the two tables', 'is_correct' => true],
                        ['text' => 'It turns the computer on', 'is_correct' => false],
                        ['text' => 'It sorts the results alphabetically', 'is_correct' => false],
                        ['text' => 'It deletes unmatched rows permanently', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the name of the free web-based tool that provides a graphical interface for managing MySQL databases? (one word)',
                    'explanation' => 'phpMyAdmin is the most widely used free web interface for MySQL and MariaDB management.',
                    'correct_answer' => 'phpMyAdmin',
                ],
            ],
        ];
    }

    /* ==================================================================== */
    /*  Module 4 – Database Administration, Backups, and Reports            */
    /* ==================================================================== */

    private function module4Lessons(): array
    {
        return [
            [
                'title' => '4.1 Using phpMyAdmin',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to navigate phpMyAdmin confidently, create databases and tables using the graphical interface, import data from CSV and SQL files, export databases for backup or sharing, and run SQL queries in the built-in SQL editor.</p>

<h2>Why phpMyAdmin Matters</h2>
<p>Not every database user is comfortable typing commands into a black terminal window. phpMyAdmin bridges that gap by giving MySQL a friendly, web-based face. It is installed automatically with XAMPP and is the tool most Zambian college students and small-business owners use to manage their first database. Even professional developers use phpMyAdmin for quick browsing, imports, and exports because it is faster than typing long commands for routine tasks.</p>

<h2>Creating a Database in phpMyAdmin</h2>
<ol>
<li>Open your browser and go to <code>http://localhost/phpmyadmin</code>.</li>
<li>On the left sidebar, click <strong>New</strong>.</li>
<li>Type a database name using lowercase letters and underscores, such as <code>school_fees_db</code>.</li>
<li>Choose <strong>utf8mb4_general_ci</strong> from the Collation dropdown. This character set supports special characters and local languages.</li>
<li>Click <strong>Create</strong>. The database appears in the left sidebar.</li>
</ol>

<h2>Creating a Table Without Writing SQL</h2>
<ol>
<li>Click on your new database in the left sidebar.</li>
<li>In the "Create table" section, type a table name such as <code>pupils</code>.</li>
<li>Enter the number of columns you need, for example <code>5</code>, and click <strong>Go</strong>.</li>
<li>For each column, type the name, choose the data type, set the length if needed, and check boxes for Null, Primary Key, or Auto Increment.</li>
<li>When finished, click <strong>Save</strong>. Your table is ready to use.</li>
</ol>

<h2>Importing Data from a CSV File</h2>
<p>Imagine a school bursar has been keeping pupil names and fees in an Excel spreadsheet. She saves the sheet as a CSV (Comma Separated Values) file and wants to move it into the database. Here is how:</p>
<ol>
<li>In phpMyAdmin, click the table where the data should go.</li>
<li>Click the <strong>Import</strong> tab at the top.</li>
<li>Click <strong>Choose File</strong> and select the CSV file from your computer.</li>
<li>Set the format to <strong>CSV</strong> and make sure "Columns separated by" is set to a comma.</li>
<li>If the first row of your CSV contains column names, check the box "The first line of the file contains the table column names."</li>
<li>Click <strong>Go</strong>. phpMyAdmin reads the file and inserts the rows.</li>
</ol>

<h2>Exporting a Database for Backup</h2>
<p>Backups are your safety net. If the computer crashes, if load-shedding corrupts a file, or if a virus strikes, a recent backup can save your business. To export a database:</p>
<ol>
<li>Click the database name in the left sidebar.</li>
<li>Click the <strong>Export</strong> tab.</li>
<li>Choose <strong>Quick</strong> for a simple export, or <strong>Custom</strong> if you want more control.</li>
<li>Make sure the format is <strong>SQL</strong>.</li>
<li>Click <strong>Go</strong>. The browser downloads a <code>.sql</code> file containing every table, row, and setting from your database.</li>
<li>Store this file on a flash drive, in cloud storage, or on a second computer.</li>
</ol>

<h2>Running SQL Queries in phpMyAdmin</h2>
<p>Even if you prefer the graphical interface, there are times when only SQL will do. phpMyAdmin has a full SQL editor:</p>
<ol>
<li>Click a database or table.</li>
<li>Click the <strong>SQL</strong> tab.</li>
<li>Type or paste your SQL query in the large white box.</li>
<li>Click <strong>Go</strong> or press Ctrl+Enter.</li>
<li>The results appear below the box. If there is an error, phpMyAdmin shows a clear red message explaining what went wrong.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Open phpMyAdmin and create a new database called <code>practice_backup</code>.</li>
<li>Create a table called <code>friends</code> with three columns: friend_id (INT, auto-increment, primary key), friend_name (VARCHAR, NOT NULL), and town (VARCHAR).</li>
<li>Use the <strong>Insert</strong> tab to add at least four rows. Include friends from Kalomo, Monze, and Livingstone.</li>
<li>Click the <strong>SQL</strong> tab and run: <code>SELECT * FROM friends WHERE town = 'Kalomo';</code></li>
<li>Click the <strong>Export</strong> tab and export the entire database as an SQL file. Save it to your Desktop.</li>
<li>Delete the <code>practice_backup</code> database using the <strong>Operations</strong> tab, then use the <strong>Import</strong> tab to restore it from the SQL file you just saved.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>phpMyAdmin</strong>: A free web-based application for managing MySQL and MariaDB databases through a graphical interface.</li>
<li><strong>Import</strong>: The process of bringing data from an external file into a database table.</li>
<li><strong>Export</strong>: The process of saving database structure and data to an external file for backup or transfer.</li>
<li><strong>CSV</strong>: Comma Separated Values, a simple text format for spreadsheet data where each row is on a new line and columns are separated by commas.</li>
<li><strong>Collation</strong>: A set of rules that defines how text is sorted and compared, including support for special characters.</li>
</ul>

<h2>Summary</h2>
<p>phpMyAdmin makes MySQL accessible to beginners by providing buttons, forms, and menus for tasks that would otherwise require typed commands. You can create databases and tables visually, import data from spreadsheets, export complete backups, and run raw SQL when needed. For any Zambian student or small-business owner learning databases, phpMyAdmin is the most practical daily tool. Master it now, and you will manage your data with confidence.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.phpmyadmin.net/docs/">phpMyAdmin Official Documentation</a> – Comprehensive guides for every feature.</li>
<li><a href="https://www.apachefriends.org/docs/">XAMPP Documentation</a> – How to install and configure XAMPP on Windows, Linux, or macOS.</li>
<li><a href="https://dev.mysql.com/doc/refman/8.0/en/">MySQL Reference Manual</a> – Official MySQL documentation for when you need to understand what is happening behind the phpMyAdmin interface.</li>
</ul>
HTML,
            ],
            [
                'title' => '4.2 Backups, Integrity, and Security',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain why regular backups are essential for any Zambian business, describe how to create a backup using phpMyAdmin or the command line, identify common threats to database integrity and security, and apply basic protective measures such as strong passwords and user access control.</p>

<h2>Why Backups Matter</h2>
<p>A database without a backup is like a shop without a roof. Eventually, something will go wrong. In Zambia, the risks are real and frequent: load-shedding can corrupt open files, power surges can damage hard drives, flash drives can be lost or infected by viruses, and rainwater can destroy computers in poorly sealed storerooms. When disaster strikes, a recent backup is the difference between a few minutes of inconvenience and months of lost work.</p>

<h2>How Often Should You Back Up?</h2>
<p>The answer depends on how often your data changes. A small shop that makes ten sales per day might back up weekly. A busy clinic that registers fifty patients per day should back up daily. A bank or mobile money agent should back up every hour. The golden rule is: back up as often as you can afford to lose data. If losing one day of work would be painful, back up every day.</p>

<h2>Creating a Backup in phpMyAdmin</h2>
<p>As you learned in the previous lesson, phpMyAdmin makes backups simple:</p>
<ol>
<li>Select your database.</li>
<li>Click <strong>Export</strong>.</li>
<li>Choose <strong>Custom</strong> export.</li>
<li>Under "Tables," make sure all tables are selected.</li>
<li>Under "Output," choose <strong>Save output to a file</strong>.</li>
<li>Click <strong>Go</strong> and save the <code>.sql</code> file to a safe location.</li>
</ol>

<h2>Creating a Backup from the Command Line</h2>
<p>For larger databases or automated backups, the command line is more powerful. Open a terminal and type:</p>
<pre><code>mysqldump -u root -p database_name &gt; backup_file.sql</code></pre>
<p>Press Enter, type your MySQL password when prompted, and the backup is created. To restore from this file later:</p>
<pre><code>mysql -u root -p database_name &lt; backup_file.sql</code></pre>

<h2>Protecting Data Integrity</h2>
<p><strong>Data integrity</strong> means your data is accurate, consistent, and reliable. A database with poor integrity contains contradictions: a sale without a customer, a product with a negative price, or a patient whose date of birth is in the future. MySQL provides several tools to enforce integrity:</p>

<ul>
<li><strong>NOT NULL</strong> – Prevents missing values in essential columns.</li>
<li><strong>UNIQUE</strong> – Prevents duplicate values in columns such as email addresses or NRC numbers.</li>
<li><strong>CHECK constraints</strong> – Reject impossible values. For example, a CHECK constraint can ensure that quantity_in_stock is never negative.</li>
<li><strong>Foreign key constraints</strong> – Prevent orphan records. If a patient is deleted, a foreign key constraint can automatically delete their visits or block the deletion until the visits are handled.</li>
</ul>

<h2>Basic Security Practices</h2>
<p>Your database contains valuable information: customer names, phone numbers, sales totals, and possibly NRC numbers or medical details. Protecting this data is not just good practice; in some cases it is required by law. Here are the essentials:</p>

<ul>
<li><strong>Change the default root password.</strong> MySQL's default root account has no password on many XAMPP installations. Set a strong password immediately.</li>
<li><strong>Create limited user accounts.</strong> Do not give everyone the root password. Create separate users with permission to only the databases they need. A shop assistant might only need to read and insert data, not delete tables.</li>
<li><strong>Keep backups offsite.</strong> Store a copy of your backup on a flash drive kept at home, or upload it to a secure cloud service. If the shop burns down, a backup on the shop computer is useless.</li>
<li><strong>Never share passwords in WhatsApp or SMS.</strong> Write them down and store them in a locked drawer if necessary, but do not send them through messaging apps where they can be intercepted.</li>
</ul>

<h2>Worked Example: A Backup Schedule for a Small Business</h2>
<p>Mr. Banda runs a building-materials shop in Kalomo. He uses MySQL to track stock and sales. Here is his backup plan:</p>
<ul>
<li><strong>Daily:</strong> At 6:00 PM, before closing, he exports the database to a SQL file on his computer.</li>
<li><strong>Weekly:</strong> Every Friday evening, he copies the SQL file to a flash drive and takes it home.</li>
<li><strong>Monthly:</strong> He emails the SQL file to himself as an attachment, providing an offsite copy.</li>
<li><strong>After major changes:</strong> Whenever he updates prices for many products or imports a large supplier list, he creates an immediate backup.</li>
</ul>
<p>This schedule means the most data he can ever lose is one day of transactions. For a small business, that is acceptable.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open phpMyAdmin and export one of your practice databases.</li>
<li>Save the SQL file to your Desktop, then copy it to a flash drive or email it to yourself.</li>
<li>In phpMyAdmin, click the <strong>User Accounts</strong> tab. Look at the list of MySQL users. How many are there?</li>
<li>Write one sentence explaining why it is dangerous to use the root account for everyday database work.</li>
<li>List three things that could destroy your database in Zambia, and next to each one write how a backup would help you recover.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Backup</strong>: A copy of database data and structure stored separately so it can be restored if the original is lost or damaged.</li>
<li><strong>Data Integrity</strong>: The accuracy, consistency, and reliability of data throughout its lifecycle.</li>
<li><strong>mysqldump</strong>: A command-line utility that exports MySQL databases to SQL text files.</li>
<li><strong>Constraint</strong>: A rule enforced by the database to prevent invalid data, such as NOT NULL or UNIQUE.</li>
<li><strong>User Access Control</strong>: The practice of giving each database user only the permissions they need and no more.</li>
</ul>

<h2>Summary</h2>
<p>Backups are your insurance policy against power failures, hardware crashes, floods, and human error. Create them regularly, store them offsite, and test that you can restore from them. Data integrity constraints such as NOT NULL, UNIQUE, and foreign keys prevent errors at the point of entry. Security practices such as strong passwords and limited user accounts protect sensitive information from theft and misuse. Together, these habits ensure that your database remains accurate, available, and trustworthy no matter what challenges arise.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://dev.mysql.com/doc/refman/8.0/en/backup-and-recovery.html">MySQL Documentation – Backup and Recovery</a> – Official guide to mysqldump and other backup methods.</li>
<li><a href="https://www.phpmyadmin.net/docs/">phpMyAdmin Documentation</a> – How to export and import databases through the web interface.</li>
<li><a href="https://www.w3schools.com/sql/sql_primarykey.asp">W3Schools – SQL Constraints</a> – Reference for NOT NULL, UNIQUE, and other integrity rules.</li>
</ul>
HTML,
            ],
            [
                'title' => '4.3 Creating Simple Reports',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what a database report is, use SQL aggregation functions such as COUNT, SUM, and GROUP BY to summarise data, and export query results to CSV format for use in spreadsheets or printed documents.</p>

<h2>What Is a Report?</h2>
<p>A <strong>report</strong> is a formatted summary of data that answers a business question. Unlike a raw table of data, a report highlights totals, averages, trends, and comparisons. A school head teacher might want a report showing how many pupils paid fees each month. A shop owner might want a report showing total sales by product category. A clinic manager might want a report showing how many patients were treated each day of the week. All of these reports come from the same database; the only difference is how the data is grouped and summarised.</p>

<h2>Aggregation Functions</h2>
<p>SQL provides built-in functions that calculate values across multiple rows:</p>
<ul>
<li><strong>COUNT(*)</strong> – Counts the total number of rows.</li>
<li><strong>SUM(column)</strong> – Adds up all the numeric values in a column.</li>
<li><strong>AVG(column)</strong> – Calculates the average of a numeric column.</li>
<li><strong>MAX(column)</strong> – Finds the highest value.</li>
<li><strong>MIN(column)</strong> – Finds the lowest value.</li>
</ul>

<h2>Grouping with GROUP BY</h2>
<p>Aggregation functions become truly useful when combined with <code>GROUP BY</code>. This clause tells the database to divide the rows into groups and calculate the aggregation for each group separately.</p>

<h2>Worked Example: Monthly Sales Report for a Shop</h2>
<p>Mama Ngosa wants to know how much she sold in each month so she can plan her stock orders. Her <strong>sales</strong> table has columns: sale_id, sale_date, product_id, quantity, and total_amount.</p>

<h3>Example 1: Total sales for the entire year</h3>
<pre><code>SELECT SUM(total_amount) AS yearly_total
FROM sales
WHERE sale_date BETWEEN '2026-01-01' AND '2026-12-31';</code></pre>

<h3>Example 2: Number of sales per month</h3>
<pre><code>SELECT MONTH(sale_date) AS month, COUNT(*) AS number_of_sales
FROM sales
WHERE YEAR(sale_date) = 2026
GROUP BY MONTH(sale_date);</code></pre>

<h3>Example 3: Total revenue per product category</h3>
<pre><code>SELECT p.category, SUM(s.total_amount) AS category_total
FROM sales s
INNER JOIN products p ON s.product_id = p.product_id
GROUP BY p.category;</code></pre>

<h3>Example 4: Average sale amount per customer</h3>
<pre><code>SELECT c.full_name, AVG(s.total_amount) AS average_sale
FROM sales s
INNER JOIN customers c ON s.customer_id = c.customer_id
GROUP BY c.customer_id
ORDER BY average_sale DESC;</code></pre>

<h2>Exporting Reports to CSV</h2>
<p>Once you have written a query that produces the right report, you often want to share it. phpMyAdmin makes this easy:</p>
<ol>
<li>Run your query in the <strong>SQL</strong> tab.</li>
<li>Scroll to the bottom of the results and click <strong>Export</strong>.</li>
<li>Choose <strong>CSV for MS Excel</strong> as the format.</li>
<li>Click <strong>Go</strong>. The browser downloads a CSV file.</li>
<li>Open the CSV file in Microsoft Excel or LibreOffice Calc to add colours, charts, and headers before printing or emailing.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>In phpMyAdmin, create a simple <strong>sales</strong> table with these columns: sale_id, sale_date, product_name, quantity, and total_amount.</li>
<li>Insert at least eight rows of realistic sales data. Use different dates, products, and amounts in Kwacha.</li>
<li>Write a query that uses COUNT(*) to show how many sales were made in total.</li>
<li>Write a query that uses SUM(total_amount) to show the total revenue.</li>
<li>Write a query that uses GROUP BY product_name to show total sales for each product.</li>
<li>Export the result of your GROUP BY query as a CSV file and open it in a spreadsheet.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Report</strong>: A formatted summary of database data designed to answer a specific business question.</li>
<li><strong>Aggregation Function</strong>: An SQL function such as COUNT, SUM, or AVG that calculates a single value from multiple rows.</li>
<li><strong>GROUP BY</strong>: An SQL clause that divides rows into groups so that aggregation functions can be applied to each group separately.</li>
<li><strong>Alias</strong>: A temporary name given to a column or table in a query, often used with AS. Example: <code>SUM(amount) AS total_revenue</code>.</li>
<li><strong>CSV Export</strong>: Saving query results as a comma-separated text file for use in spreadsheet programs.</li>
</ul>

<h2>Summary</h2>
<p>Reports turn raw database rows into actionable business intelligence. Aggregation functions such as COUNT, SUM, and AVG summarise large datasets into single meaningful numbers. GROUP BY breaks those summaries into categories so you can compare performance across products, months, customers, or locations. Exporting to CSV lets you share your findings with colleagues, managers, or accountants who prefer spreadsheets. For any Zambian business owner, clerk, or manager, the ability to generate accurate reports from a database is one of the most valuable skills you can possess.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/sql/sql_count_avg_sum.asp">W3Schools – SQL COUNT, AVG, SUM</a> – Reference and examples for aggregation functions.</li>
<li><a href="https://www.w3schools.com/sql/sql_groupby.asp">W3Schools – SQL GROUP BY</a> – How to group and summarise data.</li>
<li><a href="https://www.khanacademy.org/computing/computer-programming/sql">Khan Academy – SQL</a> – Practice exercises on aggregation and grouping.</li>
</ul>
HTML,
            ],
            [
                'title' => '4.4 Module 4 Quiz: Administration, Backups, and Reports',
                'type' => 'Quiz',
                'duration_minutes' => 20,
                'content' => '<p>Complete this quiz to test your understanding of phpMyAdmin, backups, data integrity, security, and report generation.</p>',
            ],
        ];
    }

    private function module4Quiz(): array
    {
        return [
            'title' => 'Module 4 Quiz: Administration, Backups, and Reports',
            'description' => 'Test your knowledge of phpMyAdmin, backup strategies, database security, and SQL reporting functions.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which tool provides a graphical, web-based interface for managing MySQL databases?',
                    'explanation' => 'phpMyAdmin is a free web application that lets users manage MySQL databases through buttons and forms rather than typed commands.',
                    'options' => [
                        ['text' => 'phpMyAdmin', 'is_correct' => true],
                        ['text' => 'Microsoft Word', 'is_correct' => false],
                        ['text' => 'Adobe Photoshop', 'is_correct' => false],
                        ['text' => 'VLC Media Player', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the purpose of the mysqldump command?',
                    'explanation' => 'mysqldump is a command-line utility that exports a MySQL database to an SQL text file for backup or transfer.',
                    'options' => [
                        ['text' => 'To export a database to an SQL file for backup', 'is_correct' => true],
                        ['text' => 'To delete all tables in a database', 'is_correct' => false],
                        ['text' => 'To install MySQL on a new computer', 'is_correct' => false],
                        ['text' => 'To send emails from the database', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which SQL function calculates the total of a numeric column?',
                    'explanation' => 'SUM adds all values in a column and returns the total, which is ideal for calculating total revenue or total stock.',
                    'options' => [
                        ['text' => 'SUM', 'is_correct' => true],
                        ['text' => 'COUNT', 'is_correct' => false],
                        ['text' => 'AVERAGE', 'is_correct' => false],
                        ['text' => 'MAX', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'The NOT NULL constraint prevents a column from being left empty.',
                    'explanation' => 'NOT NULL is an integrity constraint that rejects any insert or update that would leave the column without a value.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'It is safe to store your only database backup on the same computer as the live database.',
                    'explanation' => 'Storing the only backup on the same computer is risky. If the computer is stolen, flooded, or damaged, both the live database and the backup are lost.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which clause is used with aggregation functions to divide results into groups?',
                    'explanation' => 'GROUP BY splits rows into groups so that aggregation functions like SUM and COUNT operate on each group separately.',
                    'options' => [
                        ['text' => 'GROUP BY', 'is_correct' => true],
                        ['text' => 'ORDER BY', 'is_correct' => false],
                        ['text' => 'WHERE', 'is_correct' => false],
                        ['text' => 'HAVING', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What SQL function counts the number of rows in a result set? (one word)',
                    'explanation' => 'COUNT(*) returns the total number of rows, while COUNT(column) returns the number of non-null values in that column.',
                    'correct_answer' => 'COUNT',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In phpMyAdmin, which tab do you click to bring data from a CSV file into a table?',
                    'explanation' => 'The Import tab in phpMyAdmin provides options for uploading CSV, SQL, and other file formats into database tables.',
                    'options' => [
                        ['text' => 'Import', 'is_correct' => true],
                        ['text' => 'Export', 'is_correct' => false],
                        ['text' => 'Structure', 'is_correct' => false],
                        ['text' => 'Privileges', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of these is a good security practice for a MySQL database?',
                    'explanation' => 'Creating limited user accounts with only the permissions they need reduces the damage if a password is stolen or misused.',
                    'options' => [
                        ['text' => 'Create limited user accounts instead of giving everyone the root password', 'is_correct' => true],
                        ['text' => 'Share the root password in the PTA WhatsApp group', 'is_correct' => false],
                        ['text' => 'Use "password" as the root password for easy remembering', 'is_correct' => false],
                        ['text' => 'Never create backups because they waste space', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    /* ==================================================================== */
    /*  Assignments                                                         */
    /* ==================================================================== */

    private function createAssignments(): void
    {
        Assignment::create([
            'course_id' => $this->courseId,
            'lesson_id' => null,
            'title' => 'Design a Database for a Zambian Small Business',
            'description' => 'Apply your knowledge of database planning, table design, and normalisation to create a complete database design for a real Zambian business scenario.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Choose one of the following Zambian businesses:
  (a) A poultry farm in Kalomo that sells eggs and chickens to local shops and households.
  (b) A small private clinic that registers patients, records visits, and dispenses medicines.
  (c) A community school that tracks pupils, teachers, classes, and termly fee payments.

Step 2: On paper or in a typed document, identify at least THREE entities (tables) your database needs. For each entity, list at least FOUR attributes (columns) with appropriate data types.

Step 3: Identify the primary key for each table and draw arrows to show foreign key relationships between tables. Label each relationship as "one-to-many."

Step 4: Write CREATE TABLE statements in SQL for at least two of your tables. Include primary keys, appropriate data types, and at least one NOT NULL constraint per table.

Step 5: Explain in two to three sentences why your design avoids data duplication. Give a specific example of information that appears in only one place because of your normalisation.

Step 6: Save your work as a PDF document named "Database_Design_Assignment.pdf" and upload it here.
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
            'title' => 'Build and Query a Complete MySQL Database',
            'description' => 'Create a working MySQL database with multiple tables, insert realistic data, write queries to retrieve and update information, and generate a simple report.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Open phpMyAdmin on a college computer or your own XAMPP installation. Create a new database named "my_business_db".

Step 2: Create THREE tables with at least these columns:
  - customers: customer_id (primary key, auto-increment), full_name, phone_number, town
  - products: product_id (primary key, auto-increment), product_name, category, unit_price, quantity_in_stock
  - sales: sale_id (primary key, auto-increment), customer_id (foreign key), product_id (foreign key), sale_date, quantity, total_amount

Step 3: Insert at least FIVE rows into the customers table, FIVE rows into the products table, and EIGHT rows into the sales table. Use realistic Zambian names, towns (Kalomo, Livingstone, Monze, etc.), and prices in Kwacha.

Step 4: Write and run the following SQL queries. Take a screenshot of each query and its results:
  (a) SELECT showing all customers from one specific town.
  (b) SELECT with INNER JOIN showing sale details together with customer names and product names.
  (c) UPDATE that increases the price of one product by 10 percent.
  (d) SELECT with SUM and GROUP BY showing total sales revenue per product category.

Step 5: Export your entire database as an SQL file using phpMyAdmin's Export tab.

Step 6: Create a short document (one page) that explains:
  - What business your database represents.
  - Which query you found most useful and why.
  - How you would back up this database in real life.

Step 7: Zip together your SQL export file, your screenshots, and your explanation document. Name the ZIP file "Database_Project.zip" and upload it here.
INSTRUCTIONS,
            'max_points' => 100,
            'passing_points' => 50,
            'due_date' => null,
            'allow_late_submission' => 1,
            'allowed_file_types' => 'pdf,doc,docx,zip,jpg,png',
            'max_file_size_mb' => 10,
        ]);
    }

    private function printSummary(): void
    {
        $this->command->info('Database Management Systems content seeded successfully.');
        $this->command->info('Modules: 4 | Lessons: 17 | Quizzes: 4 | Assignments: 2');
    }
}
