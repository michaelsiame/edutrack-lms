<?php

namespace Database\Seeders\Content;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Assignment;

class DataAnalysisContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Data Analysis')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Data Analysis" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if ($course->modules()->count() > 0) {
            $this->command->info('Data Analysis course already has content. Skipping.');
            return;
        }

        DB::transaction(function () use ($course) {
            $this->seedModule1($course);
            $this->seedModule2($course);
            $this->seedModule3($course);
            $this->seedModule4($course);
            $this->seedModule5($course);
            $this->seedAssignments($course);
        });

        $this->printSummary();
    }

    /* --------------------------------------------------------------------- */
    /*  Module 1 – Why Data Matters and Excel Fundamentals                   */
    /* --------------------------------------------------------------------- */
    private function seedModule1(Course $course): void
    {
        $module = Module::create([
            'course_id'        => $course->id,
            'title'            => 'Module 1: Why Data Matters and Excel Fundamentals',
            'description'      => 'Understand why data analysis helps Zambian workplaces, and learn how to enter, clean, and organise data in Excel.',
            'display_order'    => 1,
            'duration_minutes' => 0,
            'is_published'     => true,
        ]);

        $lessons = [];

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => '1.1 Why Data Analysis Matters',
            'content'          => $this->lesson1_1(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 60,
            'display_order'    => 1,
            'is_preview'       => true,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => '1.2 Excel Fundamentals and Clean Data Entry',
            'content'          => $this->lesson1_2(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 75,
            'display_order'    => 2,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => '1.3 Organising and Cleaning Your First Dataset',
            'content'          => $this->lesson1_3(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 75,
            'display_order'    => 3,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $quizLesson = Lesson::create([
            'module_id'        => $module->id,
            'title'            => 'Module 1 Quiz: Data and Excel Basics',
            'content'          => '<p>Complete this quiz to test your understanding of why data analysis matters, Excel basics, and clean data entry.</p>',
            'lesson_type'      => 'Quiz',
            'duration_minutes' => 20,
            'display_order'    => 4,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $module->update(['duration_minutes' => array_sum(array_column($lessons, 'duration_minutes')) + 20]);

        $this->createModule1Quiz($course);
    }

    private function lesson1_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what data analysis is in plain language, give three examples of how it helps people in Zambia make better decisions, and describe the difference between raw data and useful information. You will also know how to spot a good question that data can answer.</p>

<h2>What Is Data Analysis?</h2>
<p>Data analysis is the process of collecting information, organising it, and looking for patterns that help us make better decisions. In simple terms, it means turning numbers and facts into answers. Every time you check which product sells best in your shop, compare school results, or decide how much maize seed to buy, you are doing a form of data analysis.</p>
<p>Data becomes powerful when we write it down, organise it, and look at it carefully. A shop owner in Soweto Market might remember that sugar sells fast, but if she keeps a small notebook of daily sales, she can see exactly how fast, which size of bag sells best, and whether sales rise before public holidays. That knowledge helps her order the right stock and avoid losing money.</p>

<h2>Why Data Analysis Matters in Zambia</h2>
<p>Data analysis is not only for big companies in Lusaka. It helps ordinary Zambians every day:</p>
<ul>
<li><strong>CDF project reporting:</strong> Constituency Development Fund projects must show how money was spent and what was achieved. Clear data makes reports honest and easy to understand.</li>
<li><strong>Small business sales:</strong> A marketeer who records each sale can see which products make profit and which ones waste shelf space.</li>
<li><strong>School results:</strong> A teacher who tracks test marks can see which topics learners understand and where extra lessons are needed.</li>
<li><strong>Farming decisions:</strong> A smallholder who records maize yield per field can compare seed types and plan better for next season.</li>
<li><strong>Mobile money records:</strong> Airtel Money and MTN MoMo agents who reconcile daily float and transaction counts avoid losses from errors or fraud.</li>
</ul>

<h2>From Raw Data to Useful Information</h2>
<p>Raw data is a collection of facts before they are organised. Useful information is data that has been sorted, summarised, and presented so someone can act on it. For example, a list of every sale in a shop for one month is raw data. A summary that shows total sales per product, best-selling days, and average transaction size is useful information.</p>

<h2>Worked Example: A Shopkeeper's Monthly Review</h2>
<p>Mary runs a small grocery shop in Kalomo. For one month she writes down every sale: date, item, quantity, and price. At the end of the month she has 400 lines of raw data. By adding up the totals she learns:</p>
<ul>
<li>Cooking oil brought in K3,200.</li>
<li>Soap brought in K1,800.</li>
<li>Sales were highest on Fridays and Saturdays.</li>
<li>The average customer spent K45.</li>
</ul>
<p>Now Mary can decide to order more cooking oil before weekends, reduce the soap variety that sells slowly, and plan promotions for quiet weekdays. The same records that looked like a mess became a tool for profit.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Think of one activity you do each week where you make a decision based on memory or guessing. Examples include shopping, selling goods, or planning study time.</li>
<li>On your phone or in a small notebook, record the basic facts for five days. For example, if you sell airtime, write down date, network, amount sold, and profit.</li>
<li>At the end of five days, add up the totals and write one sentence about what you learned.</li>
<li>Share your finding with a friend or classmate and ask if the number surprises them.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Data:</strong> Facts, numbers, or details collected for reference or analysis.</li>
<li><strong>Raw data:</strong> Unorganised facts before they have been cleaned or summarised.</li>
<li><strong>Information:</strong> Data that has been organised so it is meaningful and useful.</li>
<li><strong>Analysis:</strong> The process of examining data to find patterns, trends, and answers.</li>
<li><strong>Decision:</strong> A choice made after considering information.</li>
</ul>

<h2>Summary</h2>
<p>Data analysis helps people in every part of Zambia make better decisions, from CDF project reporting to shopkeeping and farming. The first step is to collect facts carefully, then organise them so patterns become clear. When raw data becomes useful information, ordinary people gain the power to plan, save money, and grow their businesses.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learndigital.withgoogle.com/digitalgarage/course/intro-to-data-and-data-science">Google Digital Garage — Introduction to Data and Data Science</a></li>
<li><a href="https://www.khanacademy.org/math/statistics-probability">Khan Academy — Statistics and Probability</a></li>
<li><a href="https://support.google.com/docs/answer/6063024">Google Sheets Help — Get Started with Sheets</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/introduction-to-data-analysis/">Microsoft Learn — Introduction to Data Analysis</a></li>
</ul>
HTML;
    }

    private function lesson1_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to open Microsoft Excel, identify the main parts of the screen, enter and edit data in cells, use simple formatting, and save a workbook. You will also learn the rules for clean data entry that make later analysis easy and accurate.</p>

<h2>What Is a Spreadsheet?</h2>
<p>A spreadsheet is a computer tool that stores information in a grid of rows and columns. Each box in the grid is called a <strong>cell</strong>. A spreadsheet can add, subtract, sort, filter, and graph your data automatically. Microsoft Excel and Google Sheets are the two most common spreadsheet tools in Zambia. Many college computer labs, government offices, and small businesses use Excel, while Google Sheets is useful when several people need to work on the same file using their phones.</p>

<h2>The Main Parts of Excel</h2>
<p>When you open Excel you see a blank grid called a <strong>worksheet</strong>. A collection of worksheets is called a <strong>workbook</strong>. The main parts are:</p>
<ul>
<li><strong>Ribbon:</strong> The toolbar at the top with tabs such as Home, Insert, and Formulas.</li>
<li><strong>Cells:</strong> The small boxes where you type data. Each cell has an address such as A1, B2, or C10.</li>
<li><strong>Columns:</strong> The vertical letters A, B, C, and so on.</li>
<li><strong>Rows:</strong> The horizontal numbers 1, 2, 3, and so on.</li>
<li><strong>Name Box:</strong> Shows the address of the active cell.</li>
<li><strong>Formula Bar:</strong> Shows what is inside the active cell, including formulas.</li>
</ul>

<h2>Clean Data Entry Rules</h2>
<p>Clean data means data that is easy to analyse. If you enter data carelessly, you will spend hours fixing mistakes later. Follow these rules from the start:</p>
<ol>
<li><strong>One piece of information per cell.</strong> Do not put name and phone number in the same cell.</li>
<li><strong>Use clear headers.</strong> The first row should describe each column, for example Date, Product, Quantity, Price.</li>
<li><strong>Be consistent.</strong> Write "MTN MoMo" the same way every time, not "MTN", "mtn", or "Mtn momo".</li>
<li><strong>Avoid empty rows in the middle.</strong> Empty rows break sorting, filtering, and formulas.</li>
<li><strong>Use proper dates.</strong> Type dates as 15/01/2026 or use Excel's date format so the computer understands them.</li>
<li><strong>Do not use spaces at the start or end.</strong> " Kalomo" and "Kalomo" look the same but are treated as different by the computer.</li>
</ol>

<h2>Worked Example: Creating a Sales Table</h2>
<p>Let us create a simple sales table for a small shop. Open Excel and type the following in cells A1 to D1:</p>
<table>
<tr><th>Date</th><th>Product</th><th>Quantity</th><th>Price (K)</th></tr>
<tr><td>05/01/2026</td><td>Cooking Oil 1L</td><td>3</td><td>45</td></tr>
<tr><td>05/01/2026</td><td>Soap</td><td>5</td><td>12</td></tr>
<tr><td>06/01/2026</td><td>Sugar 1kg</td><td>2</td><td>35</td></tr>
</table>
<p>After entering the data, click on cell A1 and press Ctrl+T to create a table. Excel will add filters to each header and format the rows nicely. This small step makes sorting and filtering much easier later.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open Excel or Google Sheets on a computer or phone.</li>
<li>Create a new workbook and type headers in row 1: Date, Item, Category, Amount (K).</li>
<li>Enter at least ten rows of your own spending for the past two weeks. Categories could be Transport, Food, Airtime, or School.</li>
<li>Apply bold formatting to the header row and adjust column widths so everything is visible.</li>
<li>Save the file with a clear name such as My_Spending_January_2026.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Cell:</strong> A single box in a spreadsheet where data is entered.</li>
<li><strong>Worksheet:</strong> One page or grid inside a spreadsheet file.</li>
<li><strong>Workbook:</strong> A file that can contain many worksheets.</li>
<li><strong>Header row:</strong> The first row of a table that names each column.</li>
<li><strong>Clean data:</strong> Data that is consistent, complete, and ready for analysis.</li>
</ul>

<h2>Summary</h2>
<p>Excel is a powerful spreadsheet tool used in offices, schools, and businesses across Zambia. Learning the main parts of the screen and following clean data entry rules will save you time and prevent errors. A well-structured table with clear headers, consistent text, and proper dates is the foundation of every analysis you will do in this course.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/excel">Microsoft Support — Excel</a></li>
<li><a href="https://support.google.com/docs/answer/6063024">Google Sheets Help — Get Started</a></li>
<li><a href="https://www.w3schools.com/excel/">W3Schools — Excel Tutorial</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/introduction-to-microsoft-excel/">Microsoft Learn — Excel Basics</a></li>
</ul>
HTML;
    }

    private function lesson1_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to remove duplicate rows, find and fix common errors, split text into separate columns, and prepare a messy dataset for analysis. You will also understand why data cleaning takes most of an analyst's time and why it is worth doing properly.</p>

<h2>Why Data Cleaning Matters</h2>
<p>Most real-world data is messy. Names are spelled differently, dates are written in mixed formats, numbers are stored as text, and extra spaces hide everywhere. If you analyse dirty data, your answers will be wrong. Cleaning data is the step where you fix these problems before you calculate, chart, or report anything.</p>
<p>Imagine a CDF project list where one village is written as "Choma", "choma", and "CHOMA". A simple count would show three different villages instead of one. Fixing this by hand is slow, but Excel has tools that help you clean data faster and more accurately.</p>

<h2>Common Data Problems and Fixes</h2>
<table>
<tr><th>Problem</th><th>Example</th><th>Fix</th></tr>
<tr><td>Extra spaces</td><td>" Kalomo "</td><td>Use the TRIM function or Find & Replace</td></tr>
<tr><td>Different spellings</td><td>"MTN", "Mtn", "mtn"</td><td>Use Find & Replace to standardise</td></tr>
<tr><td>Numbers as text</td><td>'120 stored as text</td><td>Convert to Number using Paste Special or Value</td></tr>
<tr><td>Blank cells</td><td>Missing dates or amounts</td><td>Fill manually or flag for follow-up</td></tr>
<tr><td>Duplicate rows</td><td>Same sale recorded twice</td><td>Use Remove Duplicates tool</td></tr>
<tr><td>Mixed date formats</td><td>12/03/2026 and 2026-03-12</td><td>Format cells as Date</td></tr>
</table>

<h2>Worked Example: Cleaning a Mobile Money Agent Record</h2>
<p>An MTN MoMo agent has the following daily record:</p>
<table>
<tr><th>Date</th><th>Customer</th><th>Type</th><th>Amount</th></tr>
<tr><td>10/01/2026</td><td> John Banda</td><td>deposit</td><td>K150</td></tr>
<tr><td>10/01/2026</td><td>Mary Zulu </td><td>Withdrawal</td><td>K80</td></tr>
<tr><td>10/01/2026</td><td>John Banda</td><td>deposit</td><td>K150</td></tr>
</table>
<p>Problems: extra spaces in names, mixed capitalisation in "Type", duplicate row, and "K" before numbers. The cleaned table should look like this:</p>
<table>
<tr><th>Date</th><th>Customer</th><th>Type</th><th>Amount</th></tr>
<tr><td>10/01/2026</td><td>John Banda</td><td>Deposit</td><td>150</td></tr>
<tr><td>10/01/2026</td><td>Mary Zulu</td><td>Withdrawal</td><td>80</td></tr>
</table>

<h2>Try It Yourself</h2>
<ol>
<li>Open the spending workbook you created in the previous lesson.</li>
<li>Add three deliberate mistakes: an extra space in one item name, a number typed as text, and a duplicated row.</li>
<li>Use Excel's Remove Duplicates tool on the Data tab to remove the duplicate.</li>
<li>Use Find & Replace to remove the extra space and standardise one category name.</li>
<li>Convert the text number to a real number by selecting the cell, clicking the warning triangle, and choosing Convert to Number.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Data cleaning:</strong> The process of fixing errors and inconsistencies in a dataset.</li>
<li><strong>Duplicate:</strong> A row that appears more than once in a dataset.</li>
<li><strong>Trim:</strong> To remove extra spaces from the beginning and end of text.</li>
<li><strong>Text to Columns:</strong> An Excel tool that splits one column of text into several columns.</li>
<li><strong>Standardise:</strong> To make similar values look the same throughout a dataset.</li>
</ul>

<h2>Common Cleaning Mistakes</h2>
<p>Even careful analysts make mistakes when cleaning data. One common error is deleting rows that look blank but actually contain important information in other columns. Another is replacing text too aggressively, such as changing "MTN MoMo" and "MTN Mobile" to the same value when they really are different services. Always keep a backup copy of the original file before you start cleaning, and document each change you make so you can explain your work later.</p>
<p>A third mistake is assuming that clean-looking data is accurate. A mobile-money record might have the right format but the wrong amount if the agent pressed an extra key. Use totals and spot checks to compare your cleaned data against known values, such as the closing balance on an agent's float.</p>

<h2>Summary</h2>
<p>Cleaning data is a critical skill for any analyst. Real data from shops, schools, and government projects is rarely perfect, but Excel provides fast tools to remove duplicates, trim spaces, fix formats, and standardise values. Time spent cleaning data is time saved from making wrong decisions later.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/remove-duplicates-in-excel-2007-2010-2013-2016-or-365-89398E1F-A29D-44A0-BF4A-31832A1C4F7E">Microsoft Support — Remove Duplicates in Excel</a></li>
<li><a href="https://support.google.com/docs/answer/6325535">Google Sheets Help — Clean Up Data</a></li>
<li><a href="https://www.w3schools.com/excel/excel_remove_duplicates.asp">W3Schools — Excel Remove Duplicates</a></li>
</ul>
HTML;
    }

    private function createModule1Quiz(Course $course): void
    {
        $quiz = Quiz::create([
            'course_id'            => $course->id,
            'lesson_id'            => null,
            'title'                => 'Module 1 Quiz: Data and Excel Basics',
            'description'          => 'Test your understanding of why data analysis matters, Excel fundamentals, and clean data entry.',
            'quiz_type'            => 'Graded',
            'time_limit_minutes'   => 20,
            'max_attempts'         => 3,
            'passing_score'        => 60.00,
            'show_correct_answers' => 1,
            'is_published'         => 1,
        ]);

        $questions = [
            [
                'type' => 'Multiple Choice',
                'text' => 'What is the main purpose of data analysis?',
                'explanation' => 'Data analysis turns raw facts into useful information so people can make better decisions.',
                'options' => [
                    ['text' => 'To collect as many numbers as possible', 'is_correct' => false],
                    ['text' => 'To turn data into useful information for decisions', 'is_correct' => true],
                    ['text' => 'To create colourful charts only', 'is_correct' => false],
                    ['text' => 'To replace human judgement completely', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'Which of the following is an example of raw data?',
                'explanation' => 'A list of every sale is raw data because it has not yet been organised or summarised.',
                'options' => [
                    ['text' => 'A summary showing Friday is the busiest sales day', 'is_correct' => false],
                    ['text' => 'A chart of monthly profit', 'is_correct' => false],
                    ['text' => 'A list of every sale made in January', 'is_correct' => true],
                    ['text' => 'The average amount spent per customer', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'In Excel, what is a cell?',
                'explanation' => 'A cell is a single box in the spreadsheet grid where you enter data.',
                'options' => [
                    ['text' => 'A toolbar at the top of the screen', 'is_correct' => false],
                    ['text' => 'A single box in the spreadsheet grid', 'is_correct' => true],
                    ['text' => 'A file containing many worksheets', 'is_correct' => false],
                    ['text' => 'A chart type in Excel', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'Which rule helps keep data clean?',
                'explanation' => 'Putting only one piece of information in each cell keeps data organised and easy to analyse.',
                'options' => [
                    ['text' => 'Put name and phone number in the same cell', 'is_correct' => false],
                    ['text' => 'Use different spellings for the same thing', 'is_correct' => false],
                    ['text' => 'Put one piece of information per cell', 'is_correct' => true],
                    ['text' => 'Leave blank rows between data rows', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'Which Excel tool removes repeated rows?',
                'explanation' => 'The Remove Duplicates tool on the Data tab deletes repeated rows from a selected range.',
                'options' => [
                    ['text' => 'Find & Replace', 'is_correct' => false],
                    ['text' => 'Text to Columns', 'is_correct' => false],
                    ['text' => 'Remove Duplicates', 'is_correct' => true],
                    ['text' => 'Freeze Panes', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'True/False',
                'text' => 'Data cleaning is usually the fastest step in data analysis.',
                'correct_answer' => 'False',
                'explanation' => 'Data cleaning often takes more time than analysis because real data is usually messy.',
            ],
            [
                'type' => 'True/False',
                'text' => 'The first row of a table should usually contain clear column headers.',
                'correct_answer' => 'True',
                'explanation' => 'Clear headers make the table understandable and help with sorting, filtering, and formulas.',
            ],
            [
                'type' => 'Short Answer',
                'text' => 'What is the letter-and-number address of the cell in column B and row 5? (one word)',
                'correct_answer' => 'B5',
                'explanation' => 'Excel cells are named by their column letter followed by their row number, so column B row 5 is B5.',
            ],
        ];

        $this->attachQuestions($quiz, $questions);
    }

    /* --------------------------------------------------------------------- */
    /*  Module 2 – Formulas for Everyday Analysis                            */
    /* --------------------------------------------------------------------- */
    private function seedModule2(Course $course): void
    {
        $module = Module::create([
            'course_id'        => $course->id,
            'title'            => 'Module 2: Formulas for Everyday Analysis',
            'description'      => 'Use Excel formulas including SUM, AVERAGE, IF, COUNTIF and VLOOKUP to calculate and find information quickly.',
            'display_order'    => 2,
            'duration_minutes' => 0,
            'is_published'     => true,
        ]);

        $lessons = [];

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => '2.1 SUM, AVERAGE and COUNT Functions',
            'content'          => $this->lesson2_1(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 75,
            'display_order'    => 1,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => '2.2 IF and COUNTIF Functions',
            'content'          => $this->lesson2_2(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 75,
            'display_order'    => 2,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => '2.3 Finding Records with VLOOKUP',
            'content'          => $this->lesson2_3(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 75,
            'display_order'    => 3,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $quizLesson = Lesson::create([
            'module_id'        => $module->id,
            'title'            => 'Module 2 Quiz: Excel Formulas',
            'content'          => '<p>Complete this quiz to test your understanding of SUM, AVERAGE, COUNT, IF, COUNTIF and VLOOKUP.</p>',
            'lesson_type'      => 'Quiz',
            'duration_minutes' => 20,
            'display_order'    => 4,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $module->update(['duration_minutes' => array_sum(array_column($lessons, 'duration_minutes')) + 20]);

        $this->createModule2Quiz($course);
    }

    private function lesson2_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to use the SUM, AVERAGE, and COUNT functions in Excel to total, average, and count numbers in a dataset. You will also understand when each function is useful and how to avoid common mistakes such as including text cells in a calculation.</p>

<h2>Why Formulas Save Time</h2>
<p>Imagine you have a month of shop sales with 500 rows. Adding each amount with a calculator would take hours and almost certainly produce errors. Excel formulas do the work in seconds and update automatically when the data changes. Once you learn a few key formulas, you can analyse much larger datasets than you could ever manage by hand.</p>

<h2>The SUM Function</h2>
<p>SUM adds numbers together. It is the most common formula in Excel. The syntax is:</p>
<blockquote>=SUM(range)</blockquote>
<p>For example, if your sales amounts are in cells D2 to D20, type =SUM(D2:D20) in an empty cell and press Enter. Excel adds every number in that range. If a cell contains text, SUM ignores it. If a number is stored as text, however, SUM will ignore it too, so make sure amounts are real numbers.</p>
<p><strong>Example:</strong> A market stall records daily takings: K120, K85, K200, K150. The formula =SUM(120,85,200,150) returns K555.</p>

<h2>The AVERAGE Function</h2>
<p>AVERAGE calculates the mean of a group of numbers. The syntax is:</p>
<blockquote>=AVERAGE(range)</blockquote>
<p>If a shop's daily sales for a week are K300, K450, K200, K500, K350, K600, and K400, the formula =AVERAGE(300,450,200,500,350,600,400) returns K400. This tells the owner that an average day brings in K400, which helps with planning stock and cash flow.</p>

<h2>The COUNT Function</h2>
<p>COUNT counts how many cells in a range contain numbers. It does not count text or blank cells. The syntax is:</p>
<blockquote>=COUNT(range)</blockquote>
<p>If you have a list of 50 transactions but some rows are blank or contain notes, COUNT tells you exactly how many valid number entries there are. A related function, COUNTA, counts any non-empty cell whether it contains text or numbers.</p>

<h2>Worked Example: Weekly Sales Summary</h2>
<p>A small shop has the following daily sales for one week:</p>
<table>
<tr><th>Day</th><th>Sales (K)</th></tr>
<tr><td>Monday</td><td>340</td></tr>
<tr><td>Tuesday</td><td>510</td></tr>
<tr><td>Wednesday</td><td>280</td></tr>
<tr><td>Thursday</td><td>620</td></tr>
<tr><td>Friday</td><td>750</td></tr>
<tr><td>Saturday</td><td>890</td></tr>
<tr><td>Sunday</td><td>410</td></tr>
</table>
<p>Using Excel:</p>
<ul>
<li>=SUM(B2:B8) returns 3,800. Total weekly sales were K3,800.</li>
<li>=AVERAGE(B2:B8) returns 542.86. Average daily sales were about K543.</li>
<li>=COUNT(B2:B8) returns 7. There are seven daily sales figures.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Open your spending workbook or create a new table with at least fifteen amounts.</li>
<li>In an empty cell, type =SUM( and select the range of amounts, then close the bracket and press Enter.</li>
<li>In another cell, type =AVERAGE( and select the same range.</li>
<li>In a third cell, type =COUNT( and select the same range.</li>
<li>Compare the three results and write one sentence explaining what each tells you.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Formula:</strong> A calculation written in a cell that starts with an equals sign.</li>
<li><strong>Function:</strong> A built-in command in Excel such as SUM or AVERAGE.</li>
<li><strong>Range:</strong> A group of cells, written as A1:A10 or B2:D10.</li>
<li><strong>Syntax:</strong> The correct way to write a formula or function.</li>
<li><strong>Mean:</strong> The average of a set of numbers, calculated by dividing the total by the count.</li>
</ul>

<h2>Common Mistakes With SUM, AVERAGE and COUNT</h2>
<p>One common mistake is including the total cell inside the range you are summing. For example, if cell B11 already contains =SUM(B2:B10), do not write =SUM(B2:B11) or you will double-count the total. Another mistake is trying to average a range that contains text or empty cells; while AVERAGE ignores text, it does include zeros, which can pull the average down if zeros represent missing data rather than real zero values.</p>
<p>COUNT only counts numbers, so if you use it on a column of names it returns zero. Make sure you use COUNTA when you want to count any non-empty cell. Checking your results with a quick manual calculation on a small sample helps you catch these errors early.</p>

<h2>Summary</h2>
<p>SUM, AVERAGE, and COUNT are the foundation of Excel analysis. They let you total income, find average performance, and count entries quickly and accurately. With these three functions you can already summarise shop sales, school marks, project spending, and many other everyday datasets.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/sum-function-043e1c7d-7726-4e80-8f32-07b23e057f89">Microsoft Support — SUM Function</a></li>
<li><a href="https://support.microsoft.com/en-us/office/average-function-047bac88-d466-426c-a32b-8f33eb960cf6">Microsoft Support — AVERAGE Function</a></li>
<li><a href="https://www.w3schools.com/excel/excel_functions.php">W3Schools — Excel Functions</a></li>
</ul>
HTML;
    }

    private function lesson2_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to use the IF function to make decisions in Excel and the COUNTIF function to count items that meet a condition. These functions help you answer questions such as "How many sales were above K100?" or "Did this student pass?"</p>

<h2>The IF Function</h2>
<p>The IF function tests whether something is true or false and then returns one value if true and another if false. The syntax is:</p>
<blockquote>=IF(condition, value_if_true, value_if_false)</blockquote>
<p>For example, if a student needs 50 marks to pass and the mark is in cell B2, you can write:</p>
<blockquote>=IF(B2>=50,"Pass","Fail")</blockquote>
<p>Excel checks the mark. If it is 50 or more, the cell shows "Pass". If it is below 50, the cell shows "Fail". The words Pass and Fail are text, so they must be inside quotation marks.</p>

<h2>COUNTIF: Count With a Condition</h2>
<p>COUNTIF counts how many cells meet a condition you specify. The syntax is:</p>
<blockquote>=COUNTIF(range, condition)</blockquote>
<p>If you have a list of payment methods in column C and want to know how many were MTN MoMo, write =COUNTIF(C:C,"MTN MoMo"). If you want to count how many sales were greater than K100 in column D, write =COUNTIF(D:D,">100").</p>

<h2>Worked Example: School Results</h2>
<p>A teacher records the following marks out of 100:</p>
<table>
<tr><th>Student</th><th>Mark</th></tr>
<tr><td>Abel</td><td>62</td></tr>
<tr><td>Betty</td><td>48</td></tr>
<tr><td>Charles</td><td>75</td></tr>
<tr><td>Diana</td><td>33</td></tr>
<tr><td>Esther</td><td>55</td></tr>
</table>
<p>To decide who passed, the teacher types in C2:</p>
<blockquote>=IF(B2>=50,"Pass","Fail")</blockquote>
<p>After filling down, the results show Pass, Fail, Pass, Fail, Pass. The teacher can then use =COUNTIF(C2:C6,"Pass") to find that three students passed, and =COUNTIF(C2:C6,"Fail") to find that two failed.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a table with at least ten sales amounts and the payment method for each sale.</li>
<li>Use COUNTIF to count how many sales used Airtel Money and how many used MTN MoMo.</li>
<li>In a new column, use IF to label each sale as "High" if the amount is K100 or more, or "Low" if it is below K100.</li>
<li>Use COUNTIF again to count how many High sales you have.</li>
<li>Change one amount and watch the IF labels update automatically.</li>
</ol>

<h2>Nesting IF Functions</h2>
<p>Sometimes one condition is not enough. You can place one IF function inside another to create multiple outcomes. This is called a nested IF. For example, a teacher might want to grade students as Distinction, Pass, or Fail based on two cut-off points.</p>
<blockquote>=IF(B2>=75,"Distinction",IF(B2>=50,"Pass","Fail"))</blockquote>
<p>Excel checks the first condition. If the mark is 75 or above, it returns Distinction. If not, it checks the second condition. If the mark is 50 or above, it returns Pass. Otherwise it returns Fail. Be careful not to nest too many IFs, because the formula becomes hard to read. For more than three outcomes, consider using a lookup table instead.</p>

<h2>Using IF and COUNTIF Together</h2>
<p>These two functions are often used as a pair. IF labels each row, and COUNTIF counts how many rows received each label. For example, a marketeer labels each sale as "Profit" if the margin is above K10 and "Low Margin" otherwise. Then she uses COUNTIF to find how many profitable sales she made and what percentage they represent.</p>
<p>This combination is useful for reports. A CDF project officer could label each project as "On Track" or "Delayed" based on the completion percentage, then count how many projects are in each category. The result is a quick status summary that does not require manual counting.</p>

<h2>Key Terms</h2>
<ul>
<li><strong>Condition:</strong> A test that is either true or false, such as B2>=50.</li>
<li><strong>IF function:</strong> A function that returns one value for true and another for false.</li>
<li><strong>COUNTIF function:</strong> A function that counts cells that meet a single condition.</li>
<li><strong>Comparison operator:</strong> Symbols such as >, <, >=, <=, and = used to compare values.</li>
<li><strong>Fill down:</strong> Copying a formula to other rows by dragging the small square at the bottom-right of a cell.</li>
</ul>

<h2>Summary</h2>
<p>IF and COUNTIF add decision-making power to your spreadsheets. IF lets you label or calculate based on a condition, while COUNTIF lets you count only the items that matter. Together they help you analyse passing rates, payment methods, sales categories, and many other everyday questions.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/if-function-69aed7c9-4e8a-4755-a9bc-aa8bbff73be2">Microsoft Support — IF Function</a></li>
<li><a href="https://support.microsoft.com/en-us/office/countif-function-e0de10c6-f538-4d27-b945-4c8b2d32b6b8">Microsoft Support — COUNTIF Function</a></li>
<li><a href="https://www.w3schools.com/excel/excel_if_function.asp">W3Schools — Excel IF Function</a></li>
</ul>
HTML;
    }

    private function lesson2_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to use the VLOOKUP function to find information in a table by searching for a value in the first column. You will understand when VLOOKUP is useful, how to avoid common errors, and how to apply it to real tasks such as finding prices or student details.</p>

<h2>What Is VLOOKUP?</h2>
<p>VLOOKUP stands for Vertical Lookup. It searches down the first column of a table for a value you specify, then returns information from another column in the same row. It is one of the most useful Excel functions for anyone who works with lists of customers, products, students, or employees.</p>
<p>The syntax is:</p>
<blockquote>=VLOOKUP(lookup_value, table_array, col_index_num, [range_lookup])</blockquote>
<ul>
<li><strong>lookup_value:</strong> The value you are searching for.</li>
<li><strong>table_array:</strong> The table that contains the data.</li>
<li><strong>col_index_num:</strong> The column number in the table that has the answer you want.</li>
<li><strong>range_lookup:</strong> Use FALSE for an exact match, which is almost always what you want.</li>
</ul>

<h2>Worked Example: Finding Product Prices</h2>
<p>A shop keeps prices in a table:</p>
<table>
<tr><th>Product Code</th><th>Product Name</th><th>Price (K)</th></tr>
<tr><td>P001</td><td>Cooking Oil 1L</td><td>45</td></tr>
<tr><td>P002</td><td>Soap</td><td>12</td></tr>
<tr><td>P003</td><td>Sugar 1kg</td><td>35</td></tr>
<tr><td>P004</td><td>Maize Meal 5kg</td><td>80</td></tr>
</table>
<p>If the table is in A2:C5 and a sales sheet has product code P003 in cell F2, the formula =VLOOKUP(F2,A2:C5,3,FALSE) returns 35. The function looks down the first column, finds P003, then returns the value from the third column, which is the price.</p>

<h2>Common VLOOKUP Mistakes</h2>
<ul>
<li><strong>#N/A error:</strong> The lookup value was not found. Check spelling and extra spaces.</li>
<li><strong>Wrong column number:</strong> If you ask for column 4 but the table only has 3 columns, you get #REF!.</li>
<li><strong>Not using FALSE:</strong> If you leave out FALSE, Excel may return an approximate match that is not what you expected.</li>
<li><strong>Lookup value not in first column:</strong> VLOOKUP can only search the first column of the table. If your code is in the second column, move it or use a different function.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Create a small price list with product code, name, and price in Excel.</li>
<li>On a separate sheet, create a sales table with product code and quantity sold.</li>
<li>Use VLOOKUP to pull the price into the sales table next to each product code.</li>
<li>Add a Total column that multiplies price by quantity.</li>
<li>Test by changing a product code and confirming the price updates.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>VLOOKUP:</strong> A function that searches for a value in the first column of a table and returns a value from another column.</li>
<li><strong>Lookup value:</strong> The value you want to find in the table.</li>
<li><strong>Table array:</strong> The range that contains the data you are searching.</li>
<li><strong>Exact match:</strong> A search that finds the exact value, not a close value.</li>
<li><strong>#N/A:</strong> An Excel error that means the lookup value was not found.</li>
</ul>

<h2>When VLOOKUP Is Not the Best Choice</h2>
<p>VLOOKUP works well when your lookup value is in the first column and you want to return a value to the right. However, it struggles if the lookup column is not first, if you need to look to the left, or if your data is not arranged vertically. In newer versions of Excel, the XLOOKUP function solves many of these problems, but it is not available in older versions. In Google Sheets, you can use VLOOKUP or INDEX and MATCH for more flexibility. For this course, focus on mastering VLOOKUP first, then explore these alternatives when you need them.</p>

<h2>Summary</h2>
<p>VLOOKUP is a powerful way to connect information across tables. Once you can find prices, names, or categories automatically, you save time and reduce typing errors. Remember to use FALSE for exact matches and keep your lookup value free of extra spaces.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/vlookup-function-0bbc8083-26fe-4963-8ab8-93a18ad188a1">Microsoft Support — VLOOKUP Function</a></li>
<li><a href="https://www.w3schools.com/excel/excel_vlookup.asp">W3Schools — Excel VLOOKUP</a></li>
<li><a href="https://support.google.com/docs/answer/3093318">Google Sheets Help — VLOOKUP</a></li>
</ul>
HTML;
    }

    private function createModule2Quiz(Course $course): void
    {
        $quiz = Quiz::create([
            'course_id'            => $course->id,
            'lesson_id'            => null,
            'title'                => 'Module 2 Quiz: Excel Formulas',
            'description'          => 'Test your understanding of SUM, AVERAGE, COUNT, IF, COUNTIF and VLOOKUP.',
            'quiz_type'            => 'Graded',
            'time_limit_minutes'   => 20,
            'max_attempts'         => 3,
            'passing_score'        => 60.00,
            'show_correct_answers' => 1,
            'is_published'         => 1,
        ]);

        $questions = [
            [
                'type' => 'Multiple Choice',
                'text' => 'What does the SUM function do?',
                'explanation' => 'SUM adds all the numbers in a selected range.',
                'options' => [
                    ['text' => 'Counts text cells', 'is_correct' => false],
                    ['text' => 'Adds numbers in a range', 'is_correct' => true],
                    ['text' => 'Finds the highest value', 'is_correct' => false],
                    ['text' => 'Sorts data alphabetically', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'Which formula calculates the average of cells B2 to B10?',
                'explanation' => 'The AVERAGE function followed by the range B2:B10 calculates the mean.',
                'options' => [
                    ['text' => '=SUM(B2:B10)', 'is_correct' => false],
                    ['text' => '=AVERAGE(B2:B10)', 'is_correct' => true],
                    ['text' => '=COUNT(B2:B10)', 'is_correct' => false],
                    ['text' => '=IF(B2:B10)', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'What does the IF function need as its first argument?',
                'explanation' => 'The first argument of IF is the condition that is tested as true or false.',
                'options' => [
                    ['text' => 'A range to sum', 'is_correct' => false],
                    ['text' => 'A condition to test', 'is_correct' => true],
                    ['text' => 'A table to search', 'is_correct' => false],
                    ['text' => 'A chart title', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'Which function counts how many cells meet a condition?',
                'explanation' => 'COUNTIF counts cells in a range that satisfy a single condition.',
                'options' => [
                    ['text' => 'SUMIF', 'is_correct' => false],
                    ['text' => 'COUNTIF', 'is_correct' => true],
                    ['text' => 'AVERAGEIF', 'is_correct' => false],
                    ['text' => 'VLOOKUP', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'In VLOOKUP, what does the third argument specify?',
                'explanation' => 'The third argument is the column index number that contains the value to return.',
                'options' => [
                    ['text' => 'The lookup value', 'is_correct' => false],
                    ['text' => 'The table range', 'is_correct' => false],
                    ['text' => 'The column number to return', 'is_correct' => true],
                    ['text' => 'Whether to use an exact match', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'True/False',
                'text' => 'The COUNT function counts both numbers and text in a range.',
                'correct_answer' => 'False',
                'explanation' => 'COUNT only counts cells that contain numbers. COUNTA counts non-empty cells including text.',
            ],
            [
                'type' => 'True/False',
                'text' => 'VLOOKUP can search any column of a table, not just the first column.',
                'correct_answer' => 'False',
                'explanation' => 'VLOOKUP always searches the first column of the table array.',
            ],
            [
                'type' => 'Short Answer',
                'text' => 'What symbol must every Excel formula start with? (one character)',
                'correct_answer' => '=',
                'explanation' => 'Every Excel formula begins with an equals sign so Excel knows it is a calculation.',
            ],
        ];

        $this->attachQuestions($quiz, $questions);
    }

    /* --------------------------------------------------------------------- */
    /*  Module 3 – Sorting, Filtering and Pivot Tables                       */
    /* --------------------------------------------------------------------- */
    private function seedModule3(Course $course): void
    {
        $module = Module::create([
            'course_id'        => $course->id,
            'title'            => 'Module 3: Sorting, Filtering and Pivot Tables',
            'description'      => 'Learn to sort, filter and summarise datasets using pivot tables so you can find answers quickly.',
            'display_order'    => 3,
            'duration_minutes' => 0,
            'is_published'     => true,
        ]);

        $lessons = [];

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => '3.1 Sorting and Filtering Data',
            'content'          => $this->lesson3_1(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 60,
            'display_order'    => 1,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => '3.2 Introduction to Pivot Tables',
            'content'          => $this->lesson3_2(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 75,
            'display_order'    => 2,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => '3.3 Practical Data Exploration',
            'content'          => $this->lesson3_3(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 75,
            'display_order'    => 3,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $quizLesson = Lesson::create([
            'module_id'        => $module->id,
            'title'            => 'Module 3 Quiz: Sorting, Filtering and Pivot Tables',
            'content'          => '<p>Complete this quiz to test your understanding of sorting, filtering and pivot tables.</p>',
            'lesson_type'      => 'Quiz',
            'duration_minutes' => 20,
            'display_order'    => 4,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $module->update(['duration_minutes' => array_sum(array_column($lessons, 'duration_minutes')) + 20]);

        $this->createModule3Quiz($course);
    }

    private function lesson3_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to sort data alphabetically or numerically, filter a table to show only the rows you need, and combine sorting and filtering to answer specific questions such as "Which Airtel Money transactions were above K500 last week?"</p>

<h2>Sorting Data</h2>
<p>Sorting rearranges rows into a particular order. You can sort text alphabetically, numbers from smallest to largest, or dates from oldest to newest. Sorting helps you find the top performers, the lowest prices, or the earliest dates in a dataset.</p>
<p>To sort in Excel, click any cell inside your data range, go to the Data tab, and click Sort A to Z or Sort Z to A. For more control, choose Custom Sort and select the column, sort order, and whether your data has headers.</p>
<p><strong>Example:</strong> A head teacher sorts a marksheet by total score from highest to lowest to see which students are top of the class. A shop owner sorts sales by date to see the busiest days of the month.</p>

<h2>Filtering Data</h2>
<p>Filtering hides rows that do not meet your criteria so you can focus on what matters. The filter arrows appear when you press Ctrl+Shift+L or click Filter on the Data tab. You can then choose to show only rows that contain a specific value, are above a number, or fall between two dates.</p>
<p><strong>Example:</strong> A CDF committee filters a project list to show only completed projects, or only projects in one ward. An Airtel Money agent filters transactions to show only deposits or only withdrawals.</p>

<h2>Worked Example: Finding Large Sales</h2>
<p>A shop has the following sales data:</p>
<table>
<tr><th>Date</th><th>Customer</th><th>Item</th><th>Amount (K)</th><th>Payment</th></tr>
<tr><td>03/01/2026</td><td>J. Banda</td><td>Oil</td><td>90</td><td>Cash</td></tr>
<tr><td>03/01/2026</td><td>M. Zulu</td><td>Soap</td><td>36</td><td>MoMo</td></tr>
<tr><td>04/01/2026</td><td>P. Musonda</td><td>Meal</td><td>160</td><td>Cash</td></tr>
<tr><td>04/01/2026</td><td>L. Tembo</td><td>Oil</td><td>135</td><td>Airtel</td></tr>
</table>
<p>To find all sales above K100, click the filter arrow on the Amount column, choose Number Filters, then Greater Than, type 100, and click OK. Only rows with amounts above K100 remain visible. To find only cash payments, filter the Payment column to show Cash.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open a dataset with at least twenty rows, or expand your spending workbook.</li>
<li>Apply filters to every header using Ctrl+Shift+L.</li>
<li>Sort the data by amount from highest to lowest.</li>
<li>Filter to show only rows where the amount is greater than K50.</li>
<li>Clear the filter and then filter by a category or payment method.</li>
<li>Write down two facts you discovered using sorting and filtering.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Sort:</strong> To arrange rows in a specific order such as alphabetical or numerical.</li>
<li><strong>Filter:</strong> To hide rows that do not meet chosen criteria.</li>
<li><strong>Ascending order:</strong> From smallest to largest, or A to Z.</li>
<li><strong>Descending order:</strong> From largest to smallest, or Z to A.</li>
<li><strong>Criteria:</strong> The condition used to filter data, such as "greater than 100".</li>
</ul>

<h2>Filtering by Date and Number</h2>
<p>Filter arrows are not limited to showing or hiding single categories. You can also filter numbers and dates using conditions. For numbers, you can choose Greater Than, Less Than, or Between. For dates, you can filter by This Week, Next Week, This Month, or a custom range. This is useful when you want to see all sales above K100, all transactions from last week, or all school attendance records from the current term.</p>
<p>When you combine a date filter with a pivot table, you can produce weekly or monthly summaries quickly. For example, an Airtel Money agent could filter the last seven days and then create a pivot table showing deposits versus withdrawals.</p>

<h2>Summary</h2>
<p>Sorting and filtering are two of the fastest ways to explore a dataset. Sorting shows order and extremes, while filtering lets you focus on a subset of rows. Used together, they turn a large table into clear answers for everyday questions.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/sort-data-in-a-range-or-table-0d3e5b8b-9012-4e18-90ec-02b858c34b6e">Microsoft Support — Sort Data in Excel</a></li>
<li><a href="https://support.microsoft.com/en-us/office/filter-data-in-a-range-or-table-01832226-31b5-4568-8786-49f24d8c0399">Microsoft Support — Filter Data in Excel</a></li>
<li><a href="https://www.w3schools.com/excel/excel_filter.asp">W3Schools — Excel Filter</a></li>
</ul>
HTML;
    }

    private function lesson3_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create a pivot table in Excel, place fields in the correct areas, and summarise a dataset by category, date, or any other variable. You will also understand why pivot tables are one of the most powerful tools for quick data analysis.</p>

<h2>What Is a Pivot Table?</h2>
<p>A pivot table is a tool that summarises a large dataset without changing the original data. You can drag column names into different areas to see totals, averages, counts, or other summaries. Pivot tables are perfect for answering questions such as "What is my total sales per product?" or "How many deposits did I receive each month?"</p>

<h2>Creating a Pivot Table</h2>
<p>To create a pivot table in Excel:</p>
<ol>
<li>Click anywhere inside your data range.</li>
<li>Go to the Insert tab and click PivotTable.</li>
<li>Confirm the data range and choose where to place the pivot table.</li>
<li>In the PivotTable Fields pane, drag fields to the Rows, Columns, Values, and Filters areas.</li>
</ol>

<h2>The Four Areas of a Pivot Table</h2>
<ul>
<li><strong>Rows:</strong> The categories you want to list down the side, such as product name or month.</li>
<li><strong>Columns:</strong> Categories you want across the top, such as payment method.</li>
<li><strong>Values:</strong> The numbers to summarise, usually using SUM, COUNT, or AVERAGE.</li>
<li><strong>Filters:</strong> Optional fields that let you narrow the whole pivot table, such as a specific year.</li>
</ul>

<h2>Worked Example: Sales by Product and Payment Method</h2>
<p>A small shop wants to know which products sell best and how customers pay. After creating a pivot table from the sales data, the owner drags:</p>
<ul>
<li>Product to the Rows area.</li>
<li>Payment to the Columns area.</li>
<li>Amount to the Values area, ensuring it is set to Sum.</li>
</ul>
<p>The pivot table shows total sales for each product, broken down by cash, Airtel Money, and MTN MoMo. The owner can quickly see that cooking oil sells mostly for cash, while soap sales are mostly mobile money.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open a dataset with at least thirty rows that includes categories and amounts.</li>
<li>Create a pivot table from the data.</li>
<li>Drag Category to Rows and Amount to Values.</li>
<li>Double-click the Amount field in Values and try changing the summary from Sum to Average and then to Count.</li>
<li>Add a second field to Columns and observe how the summary changes.</li>
<li>Write one sentence explaining the most interesting pattern you found.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Pivot table:</strong> A tool that summarises and rearranges a dataset without changing the original data.</li>
<li><strong>Field:</strong> A column from your dataset that can be dragged into a pivot table area.</li>
<li><strong>Values area:</strong> The part of a pivot table that shows the numbers being summarised.</li>
<li><strong>Summary function:</strong> The calculation used in a pivot table, such as Sum, Average, or Count.</li>
<li><strong>Refresh:</strong> Updating a pivot table after the source data changes.</li>
</ul>

<h2>Pivot Table Tips</h2>
<p>When you first create a pivot table, it may show numbers in a way that is hard to read. Right-click any number in the Values area and choose Number Format to add currency symbols, decimal places, or percentage signs. You can also rename the column headers in the pivot table to make them clearer, as long as the names do not conflict with the original field names.</p>
<p>Another useful trick is to move a field to the Filters area. This lets you focus the entire pivot table on one category at a time. For example, you could place Payment Method in Filters and then view cash-only sales while keeping the same row and column layout.</p>

<h2>Summary</h2>
<p>Pivot tables let you explore large datasets in seconds by dragging fields into rows, columns, and values. They are ideal for summarising sales, student results, project spending, and any other data grouped by categories. Once you master pivot tables, you can answer complex questions faster than with formulas alone.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/create-a-pivottable-to-analyze-worksheet-data-a9a84538-bfe9-40a9-a8e9-f99134456576">Microsoft Support — Create a PivotTable</a></li>
<li><a href="https://www.w3schools.com/excel/excel_pivot_tables.asp">W3Schools — Excel Pivot Tables</a></li>
<li><a href="https://support.google.com/docs/answer/1218656">Google Sheets Help — Pivot Tables</a></li>
</ul>
HTML;
    }

    private function lesson3_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to combine sorting, filtering and pivot tables to investigate a real dataset from start to finish. You will follow a structured process to ask a question, prepare the data, explore patterns, and present a short answer.</p>

<h2>A Simple Analysis Process</h2>
<p>Professional analysts follow a process when they explore data. The process keeps them focused and helps them avoid jumping to conclusions. The steps are:</p>
<ol>
<li><strong>Ask a clear question.</strong> For example, "Which payment method brings in the most money?"</li>
<li><strong>Prepare the data.</strong> Clean, standardise, and remove duplicates.</li>
<li><strong>Explore.</strong> Use sorting, filtering, and pivot tables to find patterns.</li>
<li><strong>Interpret.</strong> Decide what the patterns mean.</li>
<li><strong>Communicate.</strong> Share your answer with a chart and a short explanation.</li>
</ol>

<h2>Worked Example: Exploring Mobile Money Transactions</h2>
<p>An MTN MoMo agent keeps a daily log with Date, Transaction Type, Amount, and Customer Phone. She wants to know whether deposits or withdrawals are more common and which days are busiest.</p>
<p>First she cleans the data: she trims spaces, fixes "Dep" and "dep" to "Deposit", and removes one duplicated row. Then she filters to show only deposits and uses COUNT to find there are 120 deposit transactions. She filters again for withdrawals and finds 80. Next she creates a pivot table with Date in Rows and Amount in Values, summarised by Sum, to see daily totals. Finally she sorts the dates and creates a line chart showing the trend. She discovers that Fridays and Saturdays have the highest totals, so she keeps extra float on those days.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Find or create a dataset with at least forty rows and at least four columns.</li>
<li>Write down one clear question you want to answer.</li>
<li>Clean the data using the rules from Module 1.</li>
<li>Use sorting and filtering to explore the data.</li>
<li>Create a pivot table that directly answers your question.</li>
<li>Create one chart and write a one-paragraph answer to your question.</li>
</ol>

<h2>Case Study: School Attendance</h2>
<p>A head teacher wants to know which days of the week have the lowest attendance. She has a term of attendance records with Date, Class, and Present/Absent. She cleans the data, filters out public holidays, and creates a pivot table with Day of Week in Rows and Attendance in Values summarised by Count. She then uses a percentage formula to find the attendance rate for each day.</p>
<p>The analysis shows that Friday afternoons have the lowest attendance. The head teacher discusses the finding with staff and discovers that many parents collect children early to travel to weekend markets. With this evidence, the school schedules important lessons earlier in the week and plans parent meetings to address the issue.</p>

<h2>Tips for Clear Findings</h2>
<p>When you finish exploring, always return to your original question and check whether you have answered it. Write your answer as a single sentence that anyone could understand. Then ask yourself whether the data really supports that answer, or whether you are guessing. A clear finding is specific, supported by evidence, and useful for making a decision.</p>

<h2>Key Terms</h2>
<ul>
<li><strong>Analysis process:</strong> A step-by-step method for investigating data.</li>
<li><strong>Interpret:</strong> To explain what data patterns mean in real life.</li>
<li><strong>Communicate:</strong> To share findings with others using words, charts, or tables.</li>
<li><strong>Pattern:</strong> A repeated or noticeable relationship in data.</li>
<li><strong>Conclusion:</strong> The final answer or recommendation based on analysis.</li>
</ul>

<h2>Summary</h2>
<p>Sorting, filtering, and pivot tables become most powerful when you use them together as part of a clear process. Start with a question, prepare the data, explore with tools, interpret the results, and communicate your findings. This process is the same whether you are analysing shop sales, school marks, or mobile-money transactions.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/math/statistics-probability">Khan Academy — Statistics and Probability</a></li>
<li><a href="https://support.microsoft.com/en-us/office/sort-data-in-a-range-or-table-0d3e5b8b-9012-4e18-90ec-02b858c34b6e">Microsoft Support — Sort Data</a></li>
<li><a href="https://support.microsoft.com/en-us/office/create-a-pivottable-to-analyze-worksheet-data-a9a84538-bfe9-40a9-a8e9-f99134456576">Microsoft Support — Create a PivotTable</a></li>
</ul>
HTML;
    }

    private function createModule3Quiz(Course $course): void
    {
        $quiz = Quiz::create([
            'course_id'            => $course->id,
            'lesson_id'            => null,
            'title'                => 'Module 3 Quiz: Sorting, Filtering and Pivot Tables',
            'description'          => 'Test your understanding of sorting, filtering and pivot tables.',
            'quiz_type'            => 'Graded',
            'time_limit_minutes'   => 20,
            'max_attempts'         => 3,
            'passing_score'        => 60.00,
            'show_correct_answers' => 1,
            'is_published'         => 1,
        ]);

        $questions = [
            [
                'type' => 'Multiple Choice',
                'text' => 'What does sorting do in Excel?',
                'explanation' => 'Sorting rearranges rows into a chosen order such as alphabetical or numerical.',
                'options' => [
                    ['text' => 'Hides rows that do not meet a condition', 'is_correct' => false],
                    ['text' => 'Rearranges rows into a specific order', 'is_correct' => true],
                    ['text' => 'Creates a chart automatically', 'is_correct' => false],
                    ['text' => 'Deletes duplicate values', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'Which feature hides rows that do not meet a condition?',
                'explanation' => 'Filtering hides rows that do not match the criteria you choose.',
                'options' => [
                    ['text' => 'Sorting', 'is_correct' => false],
                    ['text' => 'Filtering', 'is_correct' => true],
                    ['text' => 'Pivot table', 'is_correct' => false],
                    ['text' => 'Freeze panes', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'In a pivot table, where do you drag the field you want to summarise numerically?',
                'explanation' => 'Numerical fields such as amounts are dragged to the Values area to be summarised.',
                'options' => [
                    ['text' => 'Rows', 'is_correct' => false],
                    ['text' => 'Columns', 'is_correct' => false],
                    ['text' => 'Values', 'is_correct' => true],
                    ['text' => 'Filters', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'Which area of a pivot table lists categories down the side?',
                'explanation' => 'The Rows area displays categories vertically down the left side of the pivot table.',
                'options' => [
                    ['text' => 'Columns', 'is_correct' => false],
                    ['text' => 'Values', 'is_correct' => false],
                    ['text' => 'Rows', 'is_correct' => true],
                    ['text' => 'Filters', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'What should you do after changing the source data used by a pivot table?',
                'explanation' => 'Pivot tables do not update automatically when source data changes; you must refresh them.',
                'options' => [
                    ['text' => 'Delete the pivot table and recreate it', 'is_correct' => false],
                    ['text' => 'Click Refresh in the pivot table options', 'is_correct' => true],
                    ['text' => 'Save and close the file', 'is_correct' => false],
                    ['text' => 'Apply a filter', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'True/False',
                'text' => 'Filtering permanently deletes rows from a dataset.',
                'correct_answer' => 'False',
                'explanation' => 'Filtering only hides rows; the original data remains in the worksheet.',
            ],
            [
                'type' => 'True/False',
                'text' => 'A pivot table can summarise data using Sum, Average, or Count.',
                'correct_answer' => 'True',
                'explanation' => 'Pivot tables offer several summary functions including Sum, Average, and Count.',
            ],
            [
                'type' => 'Short Answer',
                'text' => 'Which Excel keyboard shortcut toggles filters on and off for a data range? (one word, no spaces)',
                'correct_answer' => 'Ctrl+Shift+L',
                'explanation' => 'Ctrl+Shift+L applies or removes filter arrows from the selected data range.',
            ],
        ];

        $this->attachQuestions($quiz, $questions);
    }

    /* --------------------------------------------------------------------- */
    /*  Module 4 – Charts and Basic Statistics                               */
    /* --------------------------------------------------------------------- */
    private function seedModule4(Course $course): void
    {
        $module = Module::create([
            'course_id'        => $course->id,
            'title'            => 'Module 4: Charts and Basic Statistics',
            'description'      => 'Create honest charts and use basic statistics such as mean, median and percentages to describe real situations.',
            'display_order'    => 4,
            'duration_minutes' => 0,
            'is_published'     => true,
        ]);

        $lessons = [];

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => '4.1 Charts That Tell the Truth',
            'content'          => $this->lesson4_1(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 75,
            'display_order'    => 1,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => '4.2 Mean, Median and Percentages',
            'content'          => $this->lesson4_2(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 75,
            'display_order'    => 2,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => '4.3 Statistics with Maize Yield and Sales Examples',
            'content'          => $this->lesson4_3(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 60,
            'display_order'    => 3,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $quizLesson = Lesson::create([
            'module_id'        => $module->id,
            'title'            => 'Module 4 Quiz: Charts and Statistics',
            'content'          => '<p>Complete this quiz to test your understanding of charts, mean, median and percentages.</p>',
            'lesson_type'      => 'Quiz',
            'duration_minutes' => 20,
            'display_order'    => 4,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $module->update(['duration_minutes' => array_sum(array_column($lessons, 'duration_minutes')) + 20]);

        $this->createModule4Quiz($course);
    }

    private function lesson4_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to choose the right chart type for your data, create a chart in Excel, and avoid common tricks that make charts misleading. You will also understand how charts help communicate findings to people who do not want to read tables of numbers.</p>

<h2>Why Charts Matter</h2>
<p>A good chart shows a pattern in seconds that might take minutes to see in a table. Charts help shop owners, teachers, project managers, and farmers explain their findings to others. However, a bad chart can hide the truth or exaggerate small differences. Learning to create honest charts is an important skill.</p>

<h2>Common Chart Types</h2>
<ul>
<li><strong>Column chart:</strong> Best for comparing amounts across categories, such as sales per product.</li>
<li><strong>Line chart:</strong> Best for showing changes over time, such as weekly sales or maize prices.</li>
<li><strong>Pie chart:</strong> Best for showing parts of a whole, such as how a budget is divided. Use only when you have a few categories.</li>
<li><strong>Bar chart:</strong> Similar to a column chart but horizontal. Useful when category names are long.</li>
</ul>

<h2>How to Create an Honest Chart</h2>
<ol>
<li><strong>Choose the right type.</strong> Do not use a pie chart for twenty categories and do not use a line chart for unrelated categories.</li>
<li><strong>Start the vertical axis at zero.</strong> If you start it at a higher number, small differences look huge and the chart becomes misleading.</li>
<li><strong>Label everything clearly.</strong> Add a title, axis labels, and units such as Kwacha or kilograms.</li>
<li><strong>Use colours with purpose.</strong> Avoid rainbow colours that do not mean anything.</li>
<li><strong>Keep it simple.</strong> Remove gridlines and decorations that do not add information.</li>
</ol>

<h2>Worked Example: Monthly Sales Chart</h2>
<p>A shop records monthly sales for the first half of the year:</p>
<table>
<tr><th>Month</th><th>Sales (K)</th></tr>
<tr><td>January</td><td>4,200</td></tr>
<tr><td>February</td><td>4,800</td></tr>
<tr><td>March</td><td>5,100</td></tr>
<tr><td>April</td><td>4,900</td></tr>
<tr><td>May</td><td>5,500</td></tr>
<tr><td>June</td><td>6,200</td></tr>
</table>
<p>A column chart with months on the horizontal axis and sales on the vertical axis starting at zero clearly shows the upward trend. A line chart would also work well because the data is over time. A pie chart would be a poor choice because months are not parts of a single whole.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open a dataset that includes categories and amounts, or use your spending data.</li>
<li>Select the data and go to Insert > Recommended Charts.</li>
<li>Create a column chart and a pie chart from the same data.</li>
<li>Decide which chart tells the story more clearly and delete the other.</li>
<li>Add a chart title and axis labels. Make sure the vertical axis starts at zero.</li>
<li>Save the chart as an image to use in a report.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Chart:</strong> A visual representation of data.</li>
<li><strong>Axis:</strong> A line on a chart that shows the scale or categories.</li>
<li><strong>Vertical axis:</strong> The up-and-down axis, usually showing values.</li>
<li><strong>Horizontal axis:</strong> The left-to-right axis, usually showing categories or time.</li>
<li><strong>Misleading chart:</strong> A chart that distorts the truth, often by changing the axis scale.</li>
</ul>

<h2>Choosing Colours Carefully</h2>
<p>Colour can help or harm a chart. Use one main colour for most bars or lines, and reserve a second colour for highlighting something important. Avoid using many different colours just for decoration. In Zambia, some colours carry strong meanings: green is associated with agriculture and the national flag, red can signal danger or urgency, and gold suggests celebration. Choose colours that match your message and your audience.</p>
<p>If you print charts in black and white, patterns and labels become more important than colour. Always test your chart in greyscale before printing to make sure it is still readable.</p>

<h2>Summary</h2>
<p>Charts turn numbers into pictures that people can understand quickly. Choosing the right chart type, starting axes at zero, and labelling clearly keeps your charts honest and useful. A truthful chart builds trust with your audience, whether they are customers, committee members, or classmates.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/create-a-chart-from-start-to-end-0baf399e-dd61-4e18-8a73-b3fd5d5680c2">Microsoft Support — Create a Chart in Excel</a></li>
<li><a href="https://www.khanacademy.org/math/statistics-probability">Khan Academy — Statistics and Probability</a></li>
<li><a href="https://www.w3schools.com/excel/excel_charts.asp">W3Schools — Excel Charts</a></li>
</ul>
HTML;
    }

    private function lesson4_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to calculate the mean and median of a dataset, explain when each measure is useful, and calculate percentages to compare parts to a whole. You will also know how outliers can affect the mean and why the median sometimes gives a better picture of typical values.</p>

<h2>The Mean</h2>
<p>The mean, or average, is the most common measure of a typical value. To find the mean, add all the values together and divide by how many values there are. In Excel you use the AVERAGE function.</p>
<p><strong>Example:</strong> Five students score 55, 60, 65, 70, and 80 on a test. The mean is (55+60+65+70+80)/5 = 330/5 = 66.</p>
<p>The mean is useful but it can be pulled up or down by extreme values. If one student scores 20 instead of 80, the mean drops to 54 even though most students scored above 55.</p>

<h2>The Median</h2>
<p>The median is the middle value when numbers are arranged in order. Half the values are below the median and half are above. In Excel you use the MEDIAN function.</p>
<p><strong>Example:</strong> For the scores 55, 60, 65, 70, 80, the median is 65 because it is the middle number. If one student scores 20, the ordered list becomes 20, 55, 60, 65, 70, and the median is still 60. The median is less affected by extreme scores.</p>

<h2>Percentages</h2>
<p>A percentage shows a part of a whole as a fraction of 100. The formula is:</p>
<blockquote>Percentage = (Part / Whole) x 100</blockquote>
<p><strong>Example:</strong> A shop's total monthly sales are K10,000 and cooking oil sales are K2,500. The percentage of sales from cooking oil is (2,500 / 10,000) x 100 = 25%.</p>

<h2>Worked Example: Comparing Mean and Median</h2>
<p>Seven households report their weekly maize sales in Kwacha:</p>
<table>
<tr><th>Household</th><th>Sales (K)</th></tr>
<tr><td>A</td><td>80</td></tr>
<tr><td>B</td><td>90</td></tr>
<tr><td>C</td><td>100</td></tr>
<tr><td>D</td><td>110</td></tr>
<tr><td>E</td><td>120</td></tr>
<tr><td>F</td><td>130</td></tr>
<tr><td>G</td><td>500</td></tr>
</table>
<p>The mean is (80+90+100+110+120+130+500)/7 = 1,130/7 = K161.43. The median is K110, the middle value. The mean is much higher because household G sold K500, which is an outlier. If you want to describe a typical household, the median of K110 is more realistic.</p>

<h2>Try It Yourself</h2>
<ol>
<li>List the ages of ten people you know, or use a dataset of amounts.</li>
<li>Calculate the mean using the AVERAGE function in Excel.</li>
<li>Calculate the median using the MEDIAN function.</li>
<li>Calculate what percentage the largest value is of the total.</li>
<li>Write two sentences: one using the mean and one using the median to describe the data.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Mean:</strong> The average of a set of numbers, found by dividing the total by the count.</li>
<li><strong>Median:</strong> The middle value when numbers are arranged in order.</li>
<li><strong>Outlier:</strong> A value that is much higher or lower than the others.</li>
<li><strong>Percentage:</strong> A ratio expressed as a fraction of 100.</li>
<li><strong>Typical value:</strong> A single number that represents the centre of a dataset.</li>
</ul>

<h2>Choosing Between Mean and Median</h2>
<p>Use the mean when your data is fairly balanced and you want a measure that uses every value. Use the median when there are extreme outliers that would distort the mean. For example, when reporting average income in a community, the median is usually fairer because a few very high incomes can make the mean look larger than most people actually earn. When reporting average test marks for a class of similar ability, the mean is usually fine.</p>
<p>When you present findings, it is often helpful to report both numbers and explain why they differ. This shows your audience that you understand the data and are not hiding important details.</p>

<h2>Summary</h2>
<p>Mean, median, and percentages are simple but powerful tools for describing data. The mean gives an overall average, the median shows the middle value, and percentages help compare parts to a whole. Knowing when to use each measure helps you avoid being misled by extreme values and helps you tell a true story with your data.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/math/statistics-probability/summarizing-quantitative-data">Khan Academy — Mean and Median</a></li>
<li><a href="https://support.microsoft.com/en-us/office/median-function-d0916313-4753-414c-8537-ce85bddddd3f">Microsoft Support — MEDIAN Function</a></li>
<li><a href="https://www.w3schools.com/excel/excel_percentages.asp">W3Schools — Excel Percentages</a></li>
</ul>
HTML;
    }

    private function lesson4_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to apply mean, median, and percentage calculations to real farming and sales situations in Zambia. You will work through maize yield examples and small-business sales examples, and you will understand how statistics help farmers and traders make practical decisions.</p>

<h2>Statistics in Farming</h2>
<p>Smallholder farmers in Zambia often keep records of seed type, plot size, fertiliser used, and yield. Basic statistics help them compare practices and plan for the next season. A farmer who records yield per lima can see which seed variety performs best on their own land.</p>

<h2>Worked Example: Maize Yield Comparison</h2>
<p>A farmer in Kalomo tests two maize varieties on five limas each. The yield per lima in 50kg bags is:</p>
<table>
<tr><th>Variety A</th><th>Variety B</th></tr>
<tr><td>12</td><td>14</td></tr>
<tr><td>13</td><td>15</td></tr>
<tr><td>11</td><td>10</td></tr>
<tr><td>14</td><td>16</td></tr>
<tr><td>10</td><td>15</td></tr>
</table>
<p>For Variety A, the mean yield is (12+13+11+14+10)/5 = 12 bags. The median is 12 bags.</p>
<p>For Variety B, the mean yield is (14+15+10+16+15)/5 = 14 bags. The median is 15 bags.</p>
<p>Variety B gives a higher average yield, but one lima produced only 10 bags, so results are not uniform. The farmer might choose Variety B but also investigate why one lima underperformed.</p>

<h2>Statistics in Sales</h2>
<p>Small businesses use statistics to compare products, days, and payment methods. A trader at Soweto Market can record daily sales and then calculate which products contribute most to profit.</p>

<h2>Worked Example: Shop Sales Breakdown</h2>
<p>A shop's weekly sales by product are:</p>
<table>
<tr><th>Product</th><th>Sales (K)</th></tr>
<tr><td>Cooking oil</td><td>2,500</td></tr>
<tr><td>Soap</td><td>1,200</td></tr>
<tr><td>Sugar</td><td>1,800</td></tr>
<tr><td>Maize meal</td><td>3,200</td></tr>
<tr><td>Airtime</td><td>800</td></tr>
</table>
<p>Total sales = K9,500. The percentages are:</p>
<ul>
<li>Cooking oil: (2,500 / 9,500) x 100 = 26.3%</li>
<li>Soap: (1,200 / 9,500) x 100 = 12.6%</li>
<li>Sugar: (1,800 / 9,500) x 100 = 18.9%</li>
<li>Maize meal: (3,200 / 9,500) x 100 = 33.7%</li>
<li>Airtime: (800 / 9,500) x 100 = 8.4%</li>
</ul>
<p>Maize meal is the biggest contributor. The owner might increase maize meal stock before busy weekends.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a table of maize yields or sales amounts for at least five periods or products.</li>
<li>Calculate the mean and median of the amounts.</li>
<li>Calculate each item as a percentage of the total.</li>
<li>Create a simple pie chart showing the percentage breakdown.</li>
<li>Write one recommendation based on your findings.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Yield:</strong> The amount of crop produced per unit of land.</li>
<li><strong>Lima:</strong> A common unit of land area in Zambia, roughly 0.25 hectares.</li>
<li><strong>Contribution:</strong> The share that one item adds to a total, often shown as a percentage.</li>
<li><strong>Comparison:</strong> Looking at two or more sets of data to find differences.</li>
<li><strong>Recommendation:</strong> A suggested action based on data analysis.</li>
</ul>

<h2>When Averages Hide the Truth</h2>
<p>Averages can be useful, but they can also hide important differences. If a school reports that the average pass rate across all classes is 70%, it may sound good, but one class could have a 95% pass rate while another has 40%. Always look beyond the average to understand the spread of the data. Reporting the highest and lowest values, or using a chart, often gives a fuller picture.</p>
<p>In business, average sales might look healthy even if most days are quiet and a few event days pull the number up. A shop owner who plans staffing based only on the average may be caught short on busy days. Combine averages with charts and counts to see the whole story.</p>

<h2>Reporting Statistics Responsibly</h2>
<p>When you share statistics, always explain how you calculated them and what the data includes. Saying "average yield is 20 bags per lima" means little if you do not say how many fields were measured or over what period. Good reporting includes the sample size, the time period, and any limitations. This honesty helps your audience trust your conclusions and use them wisely.</p>

<h2>Summary</h2>
<p>Mean, median, and percentages are not just classroom exercises. Farmers use them to compare seed varieties, shopkeepers use them to plan stock, and project managers use them to report progress. When you connect statistics to real decisions, you turn numbers into action.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/math/statistics-probability">Khan Academy — Statistics and Probability</a></li>
<li><a href="https://www.w3schools.com/excel/excel_percentages.asp">W3Schools — Excel Percentages</a></li>
<li><a href="https://support.microsoft.com/en-us/office/create-a-pie-chart-from-your-data-709803a2-a018-4a57-8aa3-95bd1f6326fd">Microsoft Support — Pie Charts</a></li>
</ul>
HTML;
    }

    private function createModule4Quiz(Course $course): void
    {
        $quiz = Quiz::create([
            'course_id'            => $course->id,
            'lesson_id'            => null,
            'title'                => 'Module 4 Quiz: Charts and Statistics',
            'description'          => 'Test your understanding of charts, mean, median and percentages.',
            'quiz_type'            => 'Graded',
            'time_limit_minutes'   => 20,
            'max_attempts'         => 3,
            'passing_score'        => 60.00,
            'show_correct_answers' => 1,
            'is_published'         => 1,
        ]);

        $questions = [
            [
                'type' => 'Multiple Choice',
                'text' => 'Which chart type is best for showing how values change over time?',
                'explanation' => 'A line chart is designed to show trends and changes over time.',
                'options' => [
                    ['text' => 'Pie chart', 'is_correct' => false],
                    ['text' => 'Column chart', 'is_correct' => false],
                    ['text' => 'Line chart', 'is_correct' => true],
                    ['text' => 'Doughnut chart', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'Why should a bar chart\'s vertical axis usually start at zero?',
                'explanation' => 'Starting at zero prevents small differences from looking larger than they really are.',
                'options' => [
                    ['text' => 'To make the chart taller', 'is_correct' => false],
                    ['text' => 'To avoid misleading the reader', 'is_correct' => true],
                    ['text' => 'To use more colours', 'is_correct' => false],
                    ['text' => 'To fit more categories', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'What is the median of these values: 12, 14, 16, 18, 20?',
                'explanation' => 'The median is the middle value when the numbers are in order. Here it is 16.',
                'options' => [
                    ['text' => '14', 'is_correct' => false],
                    ['text' => '16', 'is_correct' => true],
                    ['text' => '18', 'is_correct' => false],
                    ['text' => '20', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'Which measure of typical value is least affected by extreme outliers?',
                'explanation' => 'The median uses only the middle value, so extreme high or low values have little effect.',
                'options' => [
                    ['text' => 'Mean', 'is_correct' => false],
                    ['text' => 'Median', 'is_correct' => true],
                    ['text' => 'Percentage', 'is_correct' => false],
                    ['text' => 'Range', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'A shop sells K2,000 of airtime out of K10,000 total sales. What percentage is airtime?',
                'explanation' => '(2,000 / 10,000) x 100 = 20%.',
                'options' => [
                    ['text' => '10%', 'is_correct' => false],
                    ['text' => '20%', 'is_correct' => true],
                    ['text' => '25%', 'is_correct' => false],
                    ['text' => '50%', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'True/False',
                'text' => 'A pie chart is suitable for comparing monthly sales over a full year.',
                'correct_answer' => 'False',
                'explanation' => 'A line or column chart is better for showing changes over time. Pie charts show parts of a single whole.',
            ],
            [
                'type' => 'True/False',
                'text' => 'The mean is calculated by dividing the total of all values by the number of values.',
                'correct_answer' => 'True',
                'explanation' => 'Mean = total sum divided by count of values.',
            ],
            [
                'type' => 'Short Answer',
                'text' => 'What Excel function calculates the middle value of a range? (one word)',
                'correct_answer' => 'MEDIAN',
                'explanation' => 'The MEDIAN function returns the middle value of a sorted range.',
            ],
        ];

        $this->attachQuestions($quiz, $questions);
    }

    /* --------------------------------------------------------------------- */
    /*  Module 5 – Google Sheets, Power BI and Dashboards                    */
    /* --------------------------------------------------------------------- */
    private function seedModule5(Course $course): void
    {
        $module = Module::create([
            'course_id'        => $course->id,
            'title'            => 'Module 5: Google Sheets, Power BI and Dashboards',
            'description'      => 'Work together in Google Sheets, explore Power BI free version, and build a simple dashboard you can present.',
            'display_order'    => 5,
            'duration_minutes' => 0,
            'is_published'     => true,
        ]);

        $lessons = [];

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => '5.1 Google Sheets for Shared Work',
            'content'          => $this->lesson5_1(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 60,
            'display_order'    => 1,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => '5.2 Introduction to Power BI Free Version',
            'content'          => $this->lesson5_2(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 75,
            'display_order'    => 2,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => '5.3 Building a Simple Dashboard',
            'content'          => $this->lesson5_3(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 75,
            'display_order'    => 3,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => '5.4 Presenting Your Findings',
            'content'          => $this->lesson5_4(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 60,
            'display_order'    => 4,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $quizLesson = Lesson::create([
            'module_id'        => $module->id,
            'title'            => 'Module 5 Quiz: Shared Work and Dashboards',
            'content'          => '<p>Complete this quiz to test your understanding of Google Sheets, Power BI, dashboards and presenting findings.</p>',
            'lesson_type'      => 'Quiz',
            'duration_minutes' => 20,
            'display_order'    => 5,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $module->update(['duration_minutes' => array_sum(array_column($lessons, 'duration_minutes')) + 20]);

        $this->createModule5Quiz($course);
    }

    private function lesson5_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create and share a spreadsheet in Google Sheets, invite others to edit or view, and understand when Google Sheets is a better choice than Excel for teamwork. You will also learn how to work offline and protect important cells from accidental changes.</p>

<h2>Why Google Sheets?</h2>
<p>Google Sheets is a free online spreadsheet tool that works in a web browser or mobile app. It is especially useful when several people need to contribute to the same file, because everyone sees changes as they happen. A PTA committee updating a fundraising list, a CDF ward team collecting project data, or a small business tracking daily sales from different phones can all use Google Sheets to stay in sync.</p>

<h2>Creating and Sharing a Sheet</h2>
<p>To start, go to sheets.google.com and sign in with a Google account. Click the blank spreadsheet icon to create a new file. The layout is similar to Excel: rows, columns, cells, and formulas. To share:</p>
<ol>
<li>Click the Share button in the top-right corner.</li>
<li>Enter the email addresses of the people you want to share with.</li>
<li>Choose Viewer, Commenter, or Editor.</li>
<li>Click Send.</li>
</ol>
<p>You can also click Copy link to create a shareable link. Be careful with Editor access, because anyone with the link can change or delete your data.</p>

<h2>Google Sheets Features for Teamwork</h2>
<ul>
<li><strong>Version history:</strong> See who changed what and restore an older version if something goes wrong.</li>
<li><strong>Comments:</strong> Mention a teammate in a cell to ask a question or explain a value.</li>
<li><strong>Protected ranges:</strong> Lock important cells or sheets so only certain people can edit them.</li>
<li><strong>Offline mode:</strong> Turn on offline access in settings so you can edit without internet and sync later.</li>
</ul>

<h2>Worked Example: PTA Fundraising Tracker</h2>
<p>A PTA WhatsApp group decides to raise money for school desks. Instead of one person collecting every contribution by hand, they create a Google Sheet with columns for Name, Amount, Date, and Payment Method. Each committee member adds their own collections directly. The treasurer uses SUM to see the running total, and everyone can check progress at any time. Sharing the link in the WhatsApp group keeps the project transparent.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Go to sheets.google.com and create a blank spreadsheet.</li>
<li>Type a simple budget with columns for Item, Quantity, Unit Price, and Total.</li>
<li>Enter at least five items and use a formula to calculate each total.</li>
<li>Click Share and send view-only access to yourself at another email address, or copy a shareable link.</li>
<li>Add a comment to one cell explaining a value.</li>
<li>Check the version history by clicking File > Version history.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Google Sheets:</strong> A free online spreadsheet application from Google.</li>
<li><strong>Share:</strong> Giving other people access to view or edit a file.</li>
<li><strong>Permission:</strong> The level of access given, such as Viewer, Commenter, or Editor.</li>
<li><strong>Version history:</strong> A record of changes made to a file over time.</li>
<li><strong>Protected range:</strong> A cell or group of cells that is locked from editing.</li>
</ul>

<h2>Google Sheets vs Excel</h2>
<p>Google Sheets and Excel are very similar for basic tasks. Excel usually has more advanced features and works well with large files on a desktop computer. Google Sheets is better for collaboration, works directly in a browser, and is free with a Google account. For a student in Zambia who uses a smartphone or a college computer, Google Sheets is often the easier choice. For someone working with thousands of rows or advanced formulas, Excel may be stronger.</p>
<p>Many analysts use both: Excel for heavy analysis and Google Sheets for collecting data from others. The skills you learn in this course transfer between the two because their formulas and layouts are very similar.</p>

<h2>Summary</h2>
<p>Google Sheets makes collaboration easy because multiple people can work on the same spreadsheet from phones or computers. It is a practical choice for committees, small teams, and anyone who needs to collect data from several sources. Learning to share safely, protect important cells, and track changes keeps your teamwork reliable.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/docs/topic/9054603">Google Sheets Help Centre</a></li>
<li><a href="https://support.google.com/docs/answer/2494822">Google Support — Share Files</a></li>
<li><a href="https://www.w3schools.com/googlesheets/">W3Schools — Google Sheets Tutorial</a></li>
</ul>
HTML;
    }

    private function lesson5_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to download and install the free version of Microsoft Power BI Desktop, import an Excel or CSV file, and create a simple visual. You will also understand the difference between Power BI and Excel, and when Power BI is the better tool for dashboards.</p>

<h2>What Is Power BI?</h2>
<p>Power BI is a business intelligence tool from Microsoft. The free desktop version lets you import data from many sources, transform it, and create interactive reports and dashboards. Unlike Excel, which is designed for working inside a spreadsheet, Power BI is designed for building visual reports that update when the data changes.</p>

<h2>Getting Power BI Desktop</h2>
<p>Power BI Desktop is free to download from the Microsoft website. Install it on a Windows computer. If you use a Mac or Linux computer, you can use the web version through Power BI service, though some features are limited without a paid licence. For this course, the free desktop version is enough to learn the basics.</p>

<h2>Importing Data</h2>
<p>After opening Power BI Desktop:</p>
<ol>
<li>Click Get Data on the Home ribbon.</li>
<li>Choose Excel Workbook or Text/CSV.</li>
<li>Navigate to your file and click Open.</li>
<li>Select the sheet or table you want to import and click Load.</li>
</ol>
<p>Once loaded, your data appears in the Fields pane on the right. You can drag fields onto the report canvas and choose visual types such as tables, bar charts, and cards.</p>

<h2>Worked Example: Sales Dashboard Elements</h2>
<p>A shop owner imports a sales file into Power BI. She drags Amount to a card visual to show total sales, Product to a bar chart to compare products, and Date to a line chart to see trends over time. She then clicks a product in the bar chart and the line chart updates to show only that product. This interactivity is what makes dashboards powerful.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Download and install Power BI Desktop from the Microsoft website, or use the web service if desktop is not available.</li>
<li>Export one of your Excel practice files to CSV format.</li>
<li>Import the CSV into Power BI.</li>
<li>Create one bar chart and one card showing a total.</li>
<li>Experiment with changing colours and titles.</li>
<li>Save the report with a clear file name.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Power BI:</strong> A Microsoft tool for building interactive data reports and dashboards.</li>
<li><strong>Dashboard:</strong> A visual display of key information from one or more datasets.</li>
<li><strong>Visual:</strong> A chart, table, card, or other graphic element in a report.</li>
<li><strong>Field:</strong> A column from your dataset that can be used in visuals.</li>
<li><strong>Canvas:</strong> The blank area where you place visuals to build a report.</li>
</ul>

<h2>Getting Data Ready for Power BI</h2>
<p>Before you import data into Power BI, make sure it is clean and well structured. Remove blank rows, ensure each column has a clear header, and check that numbers are stored as numbers, not text. Power BI has a Query Editor that can help you clean data after import, but it is faster to prepare the data in Excel or Google Sheets first.</p>
<p>When you load the data, Power BI tries to guess the data type of each column. Check these guesses carefully. For example, a date column might be loaded as text if the format is unusual, and a number column might be loaded as text if it contains currency symbols. Correct these in the Query Editor so your calculations and filters work properly.</p>

<h2>Summary</h2>
<p>Power BI free version is a useful next step after Excel. It helps you build interactive dashboards that update automatically when the data changes. While Excel is excellent for data entry and calculations, Power BI shines when you want to present findings in a professional, clickable report.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://powerbi.microsoft.com/en-us/desktop/">Microsoft Power BI Desktop Download</a></li>
<li><a href="https://learn.microsoft.com/en-us/power-bi/fundamentals/">Microsoft Learn — Power BI Fundamentals</a></li>
<li><a href="https://support.microsoft.com/en-us/power-bi">Microsoft Support — Power BI</a></li>
</ul>
HTML;
    }

    private function lesson5_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to plan and build a simple dashboard in Excel or Power BI, choose the right metrics to display, and arrange visuals so the story is clear. You will also learn the importance of keeping dashboards updated and matching the design to the audience.</p>

<h2>What Makes a Good Dashboard?</h2>
<p>A dashboard is a one-page visual summary of the most important information from your data. A good dashboard answers key questions at a glance, uses consistent colours, and avoids clutter. The person reading it should understand the main message within a few seconds.</p>

<h2>Steps to Build a Dashboard</h2>
<ol>
<li><strong>Decide the purpose.</strong> Who will use the dashboard and what decisions will they make?</li>
<li><strong>Choose key metrics.</strong> Select three to five numbers that matter most, such as total sales, average transaction, best product, and payment split.</li>
<li><strong>Prepare clean data.</strong> Make sure your dataset is accurate and updated regularly.</li>
<li><strong>Choose visual types.</strong> Use cards for single numbers, bar charts for comparisons, and line charts for trends.</li>
<li><strong>Arrange logically.</strong> Place the most important information at the top left, where readers start.</li>
<li><strong>Label clearly.</strong> Add titles, units, and dates so the context is obvious.</li>
</ol>

<h2>Worked Example: Shop Sales Dashboard</h2>
<p>A small grocery shop owner wants a weekly dashboard. She chooses these metrics:</p>
<ul>
<li>Total sales this week — shown as a large card.</li>
<li>Sales by product — shown as a bar chart.</li>
<li>Sales by day — shown as a line chart.</li>
<li>Payment method split — shown as a pie chart.</li>
</ul>
<p>She builds the dashboard in Excel using pivot charts and slicers. Each Monday she copies the new week's data into the source sheet, refreshes the pivot tables, and the dashboard updates. The dashboard helps her decide what to stock and which payment methods need more float.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a dataset you have built during this course, such as spending, sales, or maize yields.</li>
<li>Decide on three key metrics and the best chart type for each.</li>
<li>Build a one-page dashboard in Excel or Power BI.</li>
<li>Add a title that includes the time period covered.</li>
<li>Ask a friend or classmate to look at the dashboard for ten seconds and tell you the main message.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Metric:</strong> A single measure used to track performance, such as total sales.</li>
<li><strong>KPI:</strong> Key Performance Indicator, a metric that shows progress toward an important goal.</li>
<li><strong>Slicer:</strong> A clickable button in Excel that filters pivot tables and charts.</li>
<li><strong>Refresh:</strong> Updating a report with the latest data.</li>
<li><strong>Audience:</strong> The people who will read or use the dashboard.</li>
</ul>

<h2>Dashboard Mistakes to Avoid</h2>
<p>The most common dashboard mistake is trying to show everything at once. A screen full of numbers and charts confuses the reader and hides the main message. Another mistake is using fancy 3D effects or decorations that do not add meaning. Simple, flat designs are usually easier to read. A third mistake is forgetting to update the dashboard. Old data leads to old decisions, so build a habit of refreshing the data on a schedule, such as every Monday morning.</p>
<p>Finally, avoid colours that are hard to distinguish, especially for people with colour blindness. Use labels, patterns, or contrasting colours to make sure everyone can understand your dashboard.</p>

<h2>Dashboard Layout Tips</h2>
<p>Place the most important number, called the headline metric, at the top left where readers look first. Put supporting charts below or beside it in a logical reading order. Group related visuals together and leave enough white space so the page does not feel crowded.</p>
<p>Use consistent colours and fonts throughout the dashboard. If one chart uses blue for sales, do not use blue for costs in another chart unless you intend the comparison. Consistency helps readers understand the dashboard faster and reduces confusion.</p>

<h2>Summary</h2>
<p>Building a dashboard means turning data into a clear visual story. Start with the audience and the decisions they need to make, choose a few key metrics, and use charts that communicate honestly. A well-designed dashboard saves time and helps people act on data instead of guessing.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/power-bi-tableau-fundamentals/">Microsoft Learn — Dashboard Fundamentals</a></li>
<li><a href="https://support.microsoft.com/en-us/office/create-a-pivottable-to-analyze-worksheet-data-a9a84538-bfe9-40a9-a8e9-f99134456576">Microsoft Support — PivotTables for Dashboards</a></li>
<li><a href="https://www.w3schools.com/excel/excel_pivot_charts.asp">W3Schools — Excel Pivot Charts</a></li>
</ul>
HTML;
    }

    private function lesson5_4(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to prepare and deliver a short presentation of your data findings, explain charts in plain language, answer questions from your audience, and recommend actions based on evidence. You will also learn how to structure your presentation so it is clear and persuasive.</p>

<h2>Why Presentation Skills Matter</h2>
<p>Analysis is only useful if people understand and act on it. A well-presented finding can convince a CDF committee to fund a project, help a shop owner change stock, or show a farmer which seed variety works best. Your job as an analyst is not just to calculate numbers but to explain what they mean.</p>

<h2>Structure of a Good Data Presentation</h2>
<ol>
<li><strong>Start with the question.</strong> Tell the audience what problem you investigated. Example: "How can we increase sales at our shop?"</li>
<li><strong>Explain the data.</strong> Briefly describe where the data came from and how it was collected.</li>
<li><strong>Show the key finding.</strong> Use one chart or number to highlight the most important result.</li>
<li><strong>Give evidence.</strong> Support the finding with two or three additional charts or calculations.</li>
<li><strong>Recommend action.</strong> End with clear, practical steps the audience can take.</li>
</ol>

<h2>Speaking About Charts</h2>
<p>When you show a chart, do not just say "Here is a chart." Explain what it shows and why it matters. For example: "This bar chart shows that maize meal contributes 34% of weekly sales, more than any other product. This means we should make sure we never run out of maize meal, especially on weekends."</p>

<h2>Worked Example: Shop Sales Presentation</h2>
<p>A marketeer presents her monthly sales analysis to her family business:</p>
<ul>
<li><strong>Question:</strong> Which products should we focus on next month?</li>
<li><strong>Data:</strong> Daily sales records for January 2026.</li>
<li><strong>Key finding:</strong> Maize meal and cooking oil make up over half of all sales.</li>
<li><strong>Evidence:</strong> A pie chart shows the percentage breakdown. A line chart shows sales rising on Fridays and Saturdays.</li>
<li><strong>Recommendation:</strong> Increase maize meal stock before weekends and run a small promotion for slow-moving items like soap.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Choose one dataset and one chart you created during this course.</li>
<li>Prepare a two-minute presentation using the five-step structure above.</li>
<li>Practice explaining the chart without reading from the screen.</li>
<li>Record yourself or present to a classmate.</li>
<li>Ask for feedback on whether your recommendation was clear and convincing.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Key finding:</strong> The most important result from your analysis.</li>
<li><strong>Recommendation:</strong> A suggested action based on data.</li>
<li><strong>Evidence:</strong> The charts, numbers, and facts that support your finding.</li>
<li><strong>Audience:</strong> The people listening to your presentation.</li>
<li><strong>Call to action:</strong> A clear statement of what you want the audience to do next.</li>
</ul>

<h2>Handling Questions From the Audience</h2>
<p>After you present, people may ask how you collected the data, why you chose one chart over another, or whether the findings apply to other situations. Prepare for these questions by keeping your original dataset and notes nearby. If you do not know an answer, say so honestly and offer to follow up later. Guessing damages your credibility.</p>
<p>Listen carefully to each question to understand what the person really wants to know. Sometimes a question about a number is really a question about what to do next. Use questions as a chance to restate your recommendation and explain how the data supports it.</p>

<h2>Summary</h2>
<p>Presenting data is the final step that turns analysis into impact. A strong presentation starts with a clear question, shows honest evidence, and ends with practical recommendations. Whether you are speaking to a committee, a customer, or your own family, plain language and clear visuals will help your findings lead to action.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool">Canva Design School</a> — free guides on presentations and visual communication.</li>
<li><a href="https://www.khanacademy.org/computing/computer-animation">Khan Academy — Storytelling</a></li>
<li><a href="https://learndigital.withgoogle.com/digitalgarage/course/digital-marketing">Google Digital Garage — Communication Skills</a></li>
</ul>
HTML;
    }

    private function createModule5Quiz(Course $course): void
    {
        $quiz = Quiz::create([
            'course_id'            => $course->id,
            'lesson_id'            => null,
            'title'                => 'Module 5 Quiz: Shared Work and Dashboards',
            'description'          => 'Test your understanding of Google Sheets, Power BI, dashboards and presenting findings.',
            'quiz_type'            => 'Graded',
            'time_limit_minutes'   => 20,
            'max_attempts'         => 3,
            'passing_score'        => 60.00,
            'show_correct_answers' => 1,
            'is_published'         => 1,
        ]);

        $questions = [
            [
                'type' => 'Multiple Choice',
                'text' => 'What is a major advantage of Google Sheets over desktop Excel for teamwork?',
                'explanation' => 'Google Sheets lets multiple people edit the same file at the same time from any device.',
                'options' => [
                    ['text' => 'It has more formulas than Excel', 'is_correct' => false],
                    ['text' => 'Multiple people can edit in real time', 'is_correct' => true],
                    ['text' => 'It works only on Windows computers', 'is_correct' => false],
                    ['text' => 'It cannot be used on phones', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'Which Microsoft tool is designed mainly for building interactive dashboards?',
                'explanation' => 'Power BI is a business intelligence tool focused on creating interactive reports and dashboards.',
                'options' => [
                    ['text' => 'Word', 'is_correct' => false],
                    ['text' => 'Excel', 'is_correct' => false],
                    ['text' => 'Power BI', 'is_correct' => true],
                    ['text' => 'Outlook', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'What should be the first step when planning a dashboard?',
                'explanation' => 'Understanding the audience and purpose guides every other design choice.',
                'options' => [
                    ['text' => 'Choose background colours', 'is_correct' => false],
                    ['text' => 'Decide the purpose and audience', 'is_correct' => true],
                    ['text' => 'Add as many charts as possible', 'is_correct' => false],
                    ['text' => 'Import all available data', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'Which visual is best for showing a single total number on a dashboard?',
                'explanation' => 'A card visual displays one key number clearly, such as total sales.',
                'options' => [
                    ['text' => 'Line chart', 'is_correct' => false],
                    ['text' => 'Pie chart', 'is_correct' => false],
                    ['text' => 'Card', 'is_correct' => true],
                    ['text' => 'Scatter chart', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'In a data presentation, where should the recommendation appear?',
                'explanation' => 'The recommendation should come after the evidence so the audience understands why it is suggested.',
                'options' => [
                    ['text' => 'Before any data is shown', 'is_correct' => false],
                    ['text' => 'In the middle of the introduction', 'is_correct' => false],
                    ['text' => 'After the key findings and evidence', 'is_correct' => true],
                    ['text' => 'Only in the appendix', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'True/False',
                'text' => 'Power BI Desktop is free to download and use.',
                'correct_answer' => 'True',
                'explanation' => 'Microsoft offers Power BI Desktop as a free download, although some online features require a paid licence.',
            ],
            [
                'type' => 'True/False',
                'text' => 'A dashboard should include every possible chart to be useful.',
                'correct_answer' => 'False',
                'explanation' => 'A good dashboard focuses on a few key metrics and avoids clutter.',
            ],
            [
                'type' => 'Short Answer',
                'text' => 'What feature in Google Sheets lets you see earlier versions of a file? (two words)',
                'correct_answer' => 'version history',
                'explanation' => 'Version history records changes and lets you restore earlier versions of a Google Sheet.',
            ],
        ];

        $this->attachQuestions($quiz, $questions);
    }

    /* --------------------------------------------------------------------- */
    /*  Assignments                                                          */
    /* --------------------------------------------------------------------- */
    private function seedAssignments(Course $course): void
    {
        Assignment::create([
            'course_id'             => $course->id,
            'lesson_id'             => null,
            'title'                 => 'Mid-Course Assignment: Analyse a Small Business Sales Report',
            'description'           => 'Apply sorting, filtering, formulas and pivot tables to a provided sales dataset and produce a short written report with charts.',
            'instructions'          => <<<'INST'
<p><strong>Objective:</strong> Show that you can clean, summarise, and interpret a small-business sales dataset using the skills from Modules 1 to 3.</p>

<p><strong>Dataset:</strong> Download the file <em>sample_shop_sales.csv</em> from the course resources page. If it is not available, create your own dataset with at least 50 rows and the following columns: Date, Product, Quantity, Unit Price (K), Payment Method, Customer Type.</p>

<p><strong>Steps:</strong></p>
<ol>
<li>Import the dataset into Excel or Google Sheets.</li>
<li>Check for and fix at least two data-quality problems such as extra spaces, blank cells, inconsistent payment names, or numbers stored as text.</li>
<li>Add a Total column using a formula that multiplies Quantity by Unit Price.</li>
<li>Use SUM, AVERAGE and COUNT to calculate total sales, average sale value, and number of transactions.</li>
<li>Use COUNTIF to count how many transactions used each payment method.</li>
<li>Create a pivot table showing total sales by product.</li>
<li>Create one chart that clearly shows your main finding.</li>
</ol>

<p><strong>What to submit:</strong></p>
<ul>
<li>Your completed spreadsheet file (.xlsx or .ods).</li>
<li>A one-page written report saved as PDF or Word, explaining your main finding and one recommendation for the business owner.</li>
<li>A screenshot of your pivot table and chart.</li>
</ul>

<p><strong>Marking criteria:</strong> Correct calculations (40%), clean data (20%), clear pivot table and chart (20%), and practical recommendation (20%).</p>
INST,
            'max_points'            => 100,
            'passing_points'        => 50,
            'allowed_file_types'    => 'pdf,doc,docx,xlsx,ods,png,jpg,jpeg',
            'max_file_size_mb'      => 10,
            'allow_late_submission' => true,
            'due_date'              => null,
        ]);

        Assignment::create([
            'course_id'             => $course->id,
            'lesson_id'             => null,
            'title'                 => 'Final Assignment: Build and Present a Data Dashboard',
            'description'           => 'Create an interactive dashboard and a short presentation that tells a clear story with data and recommends action.',
            'instructions'          => <<<'INST'
<p><strong>Objective:</strong> Demonstrate that you can build a simple dashboard and present findings to an audience.</p>

<p><strong>Dataset:</strong> Use the same sales dataset from the mid-course assignment, or collect your own real data such as household spending, farm yields, school marks, or mobile-money transactions. Your dataset must have at least 50 rows.</p>

<p><strong>Steps:</strong></p>
<ol>
<li>Clean your dataset and document any changes you made.</li>
<li>Choose three to five key metrics that answer a clear question.</li>
<li>Build a one-page dashboard in Excel, Google Sheets, or Power BI.</li>
<li>Include at least one card or highlighted total, one comparison chart, and one trend or category chart.</li>
<li>Make sure all titles, axes, and units are clearly labelled.</li>
<li>Prepare a two- to three-minute presentation using the five-step structure: question, data, key finding, evidence, recommendation.</li>
</ol>

<p><strong>What to submit:</strong></p>
<ul>
<li>Your dashboard file (.xlsx, .pbix, or a shareable Google Sheets link).</li>
<li>A PDF or Word document with screenshots of the dashboard and a written summary.</li>
<li>A video or audio recording of your presentation, OR written slide notes if recording is not possible.</li>
</ul>

<p><strong>Marking criteria:</strong> Clean data and accurate calculations (25%), clear dashboard design (25%), appropriate choice of metrics and visuals (25%), and persuasive presentation with a practical recommendation (25%).</p>
INST,
            'max_points'            => 100,
            'passing_points'        => 50,
            'allowed_file_types'    => 'pdf,doc,docx,xlsx,pbix,png,jpg,jpeg,mp4,mp3,m4a',
            'max_file_size_mb'      => 25,
            'allow_late_submission' => true,
            'due_date'              => null,
        ]);
    }

    /* --------------------------------------------------------------------- */
    /*  Helpers                                                              */
    /* --------------------------------------------------------------------- */
    private function attachQuestions(Quiz $quiz, array $questions): void
    {
        foreach ($questions as $qIndex => $qData) {
            $question = Question::create([
                'question_type' => $qData['type'],
                'question_text' => $qData['text'],
                'points'        => $qData['type'] === 'Short Answer' ? 3 : 2,
                'explanation'   => $qData['explanation'],
                'correct_answer'=> $qData['correct_answer'] ?? null,
            ]);

            if ($qData['type'] === 'Multiple Choice') {
                foreach ($qData['options'] as $optIndex => $opt) {
                    QuestionOption::create([
                        'question_id'   => $question->question_id,
                        'option_text'   => $opt['text'],
                        'is_correct'    => $opt['is_correct'],
                        'display_order' => $optIndex + 1,
                    ]);
                }
            } elseif ($qData['type'] === 'True/False') {
                QuestionOption::create([
                    'question_id'   => $question->question_id,
                    'option_text'   => 'True',
                    'is_correct'    => $qData['correct_answer'] === 'True',
                    'display_order' => 1,
                ]);
                QuestionOption::create([
                    'question_id'   => $question->question_id,
                    'option_text'   => 'False',
                    'is_correct'    => $qData['correct_answer'] === 'False',
                    'display_order' => 2,
                ]);
            }

            $quiz->questions()->attach($question->question_id, [
                'display_order'   => $qIndex + 1,
                'points_override' => null,
            ]);
        }
    }

    private function printSummary(): void
    {
        $moduleCount = Module::where('course_id', $this->courseId)->count();
        $lessonCount = Lesson::whereHas('module', function ($query) {
            $query->where('course_id', $this->courseId);
        })->count();
        $quizCount = Quiz::where('course_id', $this->courseId)->count();
        $assignmentCount = Assignment::where('course_id', $this->courseId)->count();
        $questionCount = Quiz::where('course_id', $this->courseId)
            ->withCount('questions')
            ->get()
            ->sum('questions_count');

        $this->command->newLine();
        $this->command->info('Data Analysis content seeded successfully.');
        $this->command->table(
            ['Course', 'Modules', 'Lessons', 'Quizzes', 'Questions', 'Assignments'],
            [['Certificate in Data Analysis', $moduleCount, $lessonCount, $quizCount, $questionCount, $assignmentCount]]
        );
    }
}
