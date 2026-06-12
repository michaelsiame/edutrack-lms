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

class ComputerAndBusinessHandlingContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Computer & Business Handling')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Computer & Business Handling" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in Computer & Business Handling already has modules. Skipping content seed.');
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

                $module->duration_minutes = $moduleDuration;
                $module->save();

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

            $this->createAssignments();
        });

        $this->printSummary();
    }

    private function createModules(): array
    {
        $moduleData = [
            [
                'title' => 'Module 1: Computer Essentials and Business Documents',
                'description' => 'Refresh core computer skills, build typing speed, and create professional business letters and invoices in Microsoft Word.',
            ],
            [
                'title' => 'Module 2: Spreadsheets for Business Records',
                'description' => 'Use Microsoft Excel to keep a simple cash book, track stock, and manage a basic customer database for a small business.',
            ],
            [
                'title' => 'Module 3: Digital Communication and Office Operations',
                'description' => 'Master business email etiquette, record mobile money transactions, and handle printing, scanning, and copying workflows.',
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
                'title' => '1.1 Computer Essentials for the Workplace',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to switch on and safely shut down a Windows computer, identify the parts you touch every day, create and find your files, and protect your work from power cuts. These skills are the foundation for everything else you will do in this course, whether in a college lab, a government office, or a small shop in Kalomo.</p>

<h2>Computers at Work in Zambia</h2>
<p>A computer is an electronic tool that receives information, processes it, and gives a result. In Zambia you meet computers in many places: the desktop at a TEVETA-accredited college, the laptop in an NGO office, the point-of-sale machine in a shop that accepts Airtel Money or MTN MoMo, and the smartphone in your pocket. A small business owner who sells maize meal and cooking oil needs the same basic skills as a receptionist: turn the machine on, open the right program, type information correctly, save it, and print or share it.</p>
<p>This course assumes you have used a computer before, but perhaps you are slow, unsure, or worried about breaking something. Do not worry. We will go step by step, and every step is designed for ordinary Zambians doing real work.</p>

<h2>Parts of the Computer You Need to Know</h2>
<h3>System Unit, Monitor, Keyboard, and Mouse</h3>
<p>The <strong>system unit</strong> is the box that contains the processor, memory, and storage. It may stand upright like a tower on the floor or lie flat like a desktop unit. The <strong>monitor</strong> is the screen that shows your work. The <strong>keyboard</strong> is where you type letters, numbers, and symbols. The <strong>mouse</strong> moves the pointer on the screen and lets you click, double-click, and right-click. On a laptop, the keyboard and a flat touchpad are built into the same case.</p>

<h3>USB Ports and Flash Disks</h3>
<p>USB ports are small rectangular slots on the front or back of the system unit, or on the sides of a laptop. You can plug in a <strong>flash disk</strong> (also called a USB stick or memory stick) to copy your files from one computer to another. This is useful when load-shedding interrupts internet access and you must carry your work physically to a printer or to the college.</p>

<h2>Starting and Shutting Down Windows</h2>
<p>Most college and office computers in Zambia run Microsoft Windows. Follow this safe routine:</p>
<ol>
<li>Press the power button on the system unit or laptop.</li>
<li>Wait for Windows to load. You may see a login screen. Enter the username and password your college or workplace gave you.</li>
<li>When you finish working, click the Start button, then the Power icon, then choose <strong>Shut down</strong>. Do not pull out the power cable or press the power button unless the computer is frozen.</li>
</ol>
<p>Shutting down properly protects your files from corruption. If the power goes off during load-shedding before you can shut down, an uninterruptible power supply or laptop battery gives you a few minutes to save and close safely.</p>

<h2>Finding Your Way Around the Desktop</h2>
<p>The <strong>desktop</strong> is the main screen you see after logging in. Important icons include the Recycle Bin, File Explorer, and any shortcuts your college has placed there. <strong>File Explorer</strong> is your window into folders and files. Use it to open Documents, create new folders, and copy files to a flash disk.</p>

<h2>Worked Example: Creating a Folder for Your Business Documents</h2>
<p>Imagine you help at a small shop in Kalomo. You need a place to keep all your computer files for the shop. Here is what to do:</p>
<ol>
<li>Click the File Explorer icon on the taskbar.</li>
<li>In the left pane, click <strong>This PC</strong>, then <strong>Documents</strong>.</li>
<li>Right-click in the empty space, choose <strong>New</strong>, then <strong>Folder</strong>.</li>
<li>Type <code>Kalomo_Shop_Records</code> and press Enter.</li>
<li>Double-click the folder to open it. Now save all your shop files inside this folder.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Switch on a college computer and log in.</li>
<li>Open File Explorer and create a new folder inside Documents.</li>
<li>Name the folder with your first name and the word <em>Business</em>, for example <code>Grace_Business</code>.</li>
<li>Create two folders inside it named <code>Letters</code> and <code>Money_Records</code>.</li>
<li>Take a screenshot or write down the full path you see at the top of File Explorer.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>System unit:</strong> The main box of a desktop computer that holds the processor, memory, and storage.</li>
<li><strong>Desktop:</strong> The main screen you see after logging into Windows.</li>
<li><strong>File Explorer:</strong> The Windows tool for viewing and organising folders and files.</li>
<li><strong>Flash disk:</strong> A small removable storage device that plugs into a USB port.</li>
<li><strong>Load-shedding:</strong> Planned power cuts. Save your work often and use a battery backup when possible.</li>
</ul>

<h2>Summary</h2>
<p>This lesson refreshed your understanding of basic computer parts and Windows operation. You learned how to start and shut down safely, use File Explorer, create folders, and protect work during load-shedding. These simple habits prevent lost files and embarrassment in the workplace.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/windows/windows-basics-2d14d688-2292-3d46-9497-6f0754a100c5" target="_blank">Microsoft Support: Windows Basics</a></li>
<li><a href="https://support.microsoft.com/en-us/windows/find-and-open-file-explorer-ef370130-1cc9-9dc1-d978-5b48e3a38e93" target="_blank">Microsoft Support: Find and Open File Explorer</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/work-with-computers/" target="_blank">Microsoft Learn: Work with Computers</a></li>
</ul>
HTML
            ],
            [
                'title' => '1.2 Typing and Document Basics',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to type more confidently using all ten fingers, create a new document in Microsoft Word, apply basic formatting such as font size and alignment, save with a sensible filename, and avoid common mistakes that make business documents look unprofessional.</p>

<h2>Why Good Typing Matters in Business</h2>
<p>Typing is not just about speed. It is about producing clean documents that other people can read without confusion. A shop invoice with random spaces, a business letter in three different font sizes, or an email with no paragraphs all make your organisation look careless. In contrast, neat typing helps customers trust you, helps bosses read your reports quickly, and helps you keep accurate records.</p>
<p>A receptionist who types twenty words per minute but produces error-free letters is more useful than someone who types sixty words per minute but makes many mistakes. Accuracy comes first; speed comes with practice.</p>

<h2>Sitting Correctly at the Keyboard</h2>
<p>Good posture prevents back pain and helps you type faster. Sit with both feet flat on the floor. Keep your back straight and your elbows close to your body. Place the keyboard directly in front of you so your wrists are straight, not bent. The monitor should be at eye level and about an arm's length away. In a busy college lab, you may need to adjust the chair height and monitor tilt before you begin.</p>

<h2>The Home Keys</h2>
<p>The <strong>home keys</strong> are the row where your fingers rest when you are not typing: <code>A S D F</code> for the left hand and <code>J K L ;</code> for the right hand. The small bumps on the <code>F</code> and <code>J</code> keys help you find these positions without looking down. From the home keys, each finger reaches up, down, or sideways to press other keys. Learning this pattern reduces the need to hunt and peck.</p>

<h2>Creating a Document in Microsoft Word</h2>
<p>Microsoft Word is the most common word processor in Zambian offices. To start a new document:</p>
<ol>
<li>Click the Start button and type <strong>Word</strong>, then press Enter.</li>
<li>When Word opens, click <strong>Blank document</strong>.</li>
<li>Type the heading and body text.</li>
<li>Click the Save icon or press <strong>Ctrl + S</strong>.</li>
<li>Choose a location such as Documents, type a clear filename, and click <strong>Save</strong>.</li>
</ol>

<h2>Basic Formatting</h2>
<p>At the top of Word you see the <strong>Ribbon</strong>, which contains tabs such as Home, Insert, and Layout. On the Home tab you can:</p>
<ul>
<li><strong>Change the font</strong>: Calibri and Times New Roman are safe choices for business documents.</li>
<li><strong>Change the font size</strong>: Use size 12 for body text and size 14 or 16 for headings.</li>
<li><strong>Align text</strong>: Left-align body text; centre headings.</li>
<li><strong>Use bold</strong>: Use bold for headings and important words, but not for whole paragraphs.</li>
</ul>

<h2>Worked Example: Formatting a Simple Notice</h2>
<p>Suppose you are asked to type this notice for the staff room of a Kalomo shop:</p>
<blockquote>
STAFF NOTICE<br>
All staff must record mobile money sales in the cash book before closing the shop. Thank you.<br>
Manager
</blockquote>
<p>Follow these steps:</p>
<ol>
<li>Type the text as plain paragraphs.</li>
<li>Select "STAFF NOTICE" and make it bold, size 16, and centre-aligned.</li>
<li>Select the body text and make it size 12, left-aligned.</li>
<li>Select "Manager" and make it italic.</li>
<li>Save the file as <code>Staff_Notice_Mobile_Money.docx</code>.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Open Microsoft Word and create a blank document.</li>
<li>Type a short notice for a small business. Heading: <em>Price Increase Notice</em>. Body: Explain that a bag of mealie meal now costs K180 and a bottle of cooking oil costs K45, effective Monday.</li>
<li>Format the heading in bold, size 16, centred.</li>
<li>Format the body in size 12, left-aligned.</li>
<li>Save the document in your <code>Grace_Business/Letters</code> folder as <code>Price_Increase_Notice.docx</code>.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Word processor:</strong> A program such as Microsoft Word used to type, edit, and format documents.</li>
<li><strong>Home keys:</strong> The resting position for your fingers on the keyboard: <code>A S D F</code> and <code>J K L ;</code>.</li>
<li><strong>Ribbon:</strong> The toolbar at the top of Microsoft Word with tabs for formatting and tools.</li>
<li><strong>Font:</strong> The style of the text, such as Calibri or Times New Roman.</li>
<li><strong>Alignment:</strong> The position of text on the page, such as left, centre, or right.</li>
</ul>

<h2>Summary</h2>
<p>This lesson covered practical typing and document skills. You learned correct posture, the home keys, how to open Microsoft Word, apply basic formatting, and save files with clear names. These habits make your business documents easy to read and professional to look at.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/typography/" target="_blank">W3Schools: Typography Basics</a></li>
<li><a href="https://support.microsoft.com/en-us/office/word-for-windows-training-7bcd85e6-2c3d-4c3c-a2a5-5ed8847eae50" target="_blank">Microsoft Support: Word for Windows Training</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/introduction-to-word/" target="_blank">Microsoft Learn: Introduction to Word</a></li>
</ul>
HTML
            ],
            [
                'title' => '1.3 Business Letters and Invoices in Word',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to produce a properly laid out business letter and a simple invoice in Microsoft Word. You will know where to put addresses, dates, subject lines, and totals, and you will understand how to save and print these documents for customers, suppliers, and employers.</p>

<h2>Why Letters and Invoices Still Matter</h2>
<p>Even in the age of WhatsApp and email, many Zambian businesses still need printed or PDF letters and invoices. A supplier may ask for a formal letter requesting a quotation. A customer buying goods on credit needs an invoice showing what was bought and how much is owed. Government offices often require typed letters with correct formatting. Learning to create these documents in Word makes you more employable and helps any small business look organised.</p>

<h2>Parts of a Business Letter</h2>
<p>A standard business letter has a clear order:</p>
<ol>
<li><strong>Sender's address</strong> at the top right or top left.</li>
<li><strong>Date</strong> below the sender's address.</li>
<li><strong>Recipient's address</strong> below the date on the left.</li>
<li><strong>Salutation</strong> such as "Dear Sir/Madam," or "Dear Mr. Banda,".</li>
<li><strong>Subject line</strong> telling the reader what the letter is about.</li>
<li><strong>Body</strong> of the letter in short, polite paragraphs.</li>
<li><strong>Closing</strong> such as "Yours faithfully," or "Yours sincerely,".</li>
<li><strong>Signature and typed name</strong> of the sender.</li>
</ol>

<h2>Formatting Rules</h2>
<ul>
<li>Use single line spacing within paragraphs and one blank line between paragraphs.</li>
<li>Use a clear font such as Times New Roman or Calibri, size 12.</li>
<li>Left-align all text except the sender's address, which may be right-aligned.</li>
<li>Keep the tone polite, clear, and brief. Avoid long sentences.</li>
</ul>

<h2>Worked Example: A Letter Requesting a Quotation</h2>
<p>Suppose you manage a small shop in Kalomo and want to buy printing paper from a supplier in Lusaka. Your letter might look like this:</p>
<blockquote>
<p>15 June 2026</p>
<p>The Sales Manager<br>
Zed Office Supplies Ltd<br>
P.O. Box 12345<br>
Lusaka</p>
<p>Dear Sir/Madam,</p>
<p><strong>Subject: Request for Quotation for Printing Paper</strong></p>
<p>We are a small retail shop in Kalomo and wish to purchase printing paper for our office use. Please send us your quotation for five reams of A4 paper, including delivery charges to Kalomo.</p>
<p>We would appreciate a reply by 22 June 2026. Payment will be made by bank transfer within seven days of delivery.</p>
<p>Yours faithfully,</p>
<p>Grace Mwenda<br>
Shop Manager</p>
</blockquote>

<h2>Creating a Simple Invoice</h2>
<p>An invoice is a document that asks for payment. It should include:</p>
<ul>
<li>Your business name and contact details.</li>
<li>The word <strong>INVOICE</strong> as a clear heading.</li>
<li>An invoice number and date.</li>
<li>The customer's name.</li>
<li>A list of items, quantities, unit prices, and totals.</li>
<li>The grand total.</li>
<li>Payment instructions such as bank details or mobile money number.</li>
</ul>

<h2>Worked Example: Invoice for a Sale</h2>
<p>Here is an invoice for a customer who bought stock from your Kalomo shop:</p>
<table>
<tr><th>Item</th><th>Quantity</th><th>Unit Price (K)</th><th>Total (K)</th></tr>
<tr><td>2-litre cooking oil</td><td>4</td><td>45.00</td><td>180.00</td></tr>
<tr><td>10 kg mealie meal</td><td>2</td><td>180.00</td><td>360.00</td></tr>
<tr><td>Bar of soap</td><td>6</td><td>15.00</td><td>90.00</td></tr>
<tr><td colspan="3"><strong>Grand Total</strong></td><td><strong>630.00</strong></td></tr>
</table>
<p>In Word you can create this table using the Insert tab, then Table. Set the column widths evenly, add bold to headings, and right-align the numbers.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open Microsoft Word and create a business letter to a supplier requesting prices for three items your shop needs.</li>
<li>Use the correct layout: sender address, date, recipient address, salutation, subject, body, closing, and signature block.</li>
<li>Save it as <code>Quotation_Request_Letter.docx</code> in your <code>Letters</code> folder.</li>
<li>Create a second document: an invoice for one customer who bought five items from your shop. Include item names, quantities, unit prices, line totals, and a grand total.</li>
<li>Save it as <code>Invoice_Sample.docx</code> and export it as a PDF if your Word version allows.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Salutation:</strong> The greeting at the start of a letter, such as "Dear Sir/Madam,".</li>
<li><strong>Subject line:</strong> A brief phrase that tells the reader the topic of the letter.</li>
<li><strong>Quotation:</strong> A document stating the price a supplier will charge for goods or services.</li>
<li><strong>Invoice:</strong> A document sent to a customer showing what is owed for goods or services.</li>
<li><strong>Grand total:</strong> The final amount due after adding up all items.</li>
</ul>

<h2>Summary</h2>
<p>This lesson showed you how to produce two essential business documents in Word: a formal letter and an invoice. You learned the correct layout for letters, how to insert and format tables for invoices, and how to save your work with meaningful filenames. These documents are used daily in shops, offices, and government institutions across Zambia.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/insert-a-table-in-word-3e4c344c-bafc-4e5d-93a5-1d52db1d9e5d" target="_blank">Microsoft Support: Insert a Table in Word</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/format-word-documents/" target="_blank">Microsoft Learn: Format Word Documents</a></li>
<li><a href="https://www.w3schools.com/html/html_tables.asp" target="_blank">W3Schools: HTML Tables</a></li>
</ul>
HTML
            ],
        ];
    }

    private function module2Lessons(): array
    {
        return [
            [
                'title' => '2.1 Simple Bookkeeping in Excel: The Cash Book',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to open Microsoft Excel, enter money coming in and going out of a small business, write simple formulas to calculate totals, and keep a basic cash book that shows whether the business made a profit or loss.</p>

<h2>Why Every Small Business Needs a Cash Book</h2>
<p>A <strong>cash book</strong> is a record of all cash received and all cash paid out. It is one of the simplest and most powerful tools for a small business. Whether you sell tomatoes at Kalomo market, run a barbershop, or manage a tuckshop, you need to know how much money entered the business and how much left it. Without this record, you cannot tell whether you are making a profit, whether customers owe you money, or whether too much cash is disappearing.</p>
<p>Many Zambian businesses fail not because they lack customers but because the owner does not track money. A cash book helps you catch mistakes, prepare for tax visits, and decide whether you can afford new stock.</p>

<h2>Starting Microsoft Excel</h2>
<p>Excel is a spreadsheet program. A <strong>spreadsheet</strong> is a page made of rows and columns that form cells. Each cell has an address such as <code>A1</code> or <code>B5</code>. To open Excel, click the Start button, type <strong>Excel</strong>, and press Enter. Choose <strong>Blank workbook</strong>.</p>

<h2>Designing a Simple Cash Book</h2>
<p>A basic cash book needs these columns:</p>
<ul>
<li><strong>Date</strong> of the transaction.</li>
<li><strong>Description</strong> of what happened.</li>
<li><strong>Income (Money In)</strong> for sales and money received.</li>
<li><strong>Expenses (Money Out)</strong> for stock, transport, rent, and other costs.</li>
<li><strong>Balance</strong> showing how much cash remains.</li>
</ul>

<h2>Worked Example: Cash Book for a Small Shop</h2>
<p>Mrs. Mwenda runs a small shop in Kalomo. On 1 June she starts with K500 in the till. During the week she records the following:</p>
<table>
<tr><th>Date</th><th>Description</th><th>Income (K)</th><th>Expense (K)</th></tr>
<tr><td>1 Jun</td><td>Cash in hand</td><td>500.00</td><td></td></tr>
<tr><td>2 Jun</td><td>Sold mealie meal and cooking oil</td><td>250.00</td><td></td></tr>
<tr><td>2 Jun</td><td>Bought bread for resale</td><td></td><td>120.00</td></tr>
<tr><td>3 Jun</td><td>Sold airtime and soap</td><td>95.00</td><td></td></tr>
<tr><td>4 Jun</td><td>Paid transport to town</td><td></td><td>60.00</td></tr>
</table>
<p>In Excel you enter this data in columns A to D. In the balance column, use a formula to calculate the running total. If income is in column C and expenses in column D, the first balance formula in E2 is <code>=C2-D2</code>. For the next row, the formula is <code>=E2+C3-D3</code>. Copy this formula down so the balance updates automatically.</p>

<h2>Calculating Profit</h2>
<p>To find the profit for the period, add all income and subtract all expenses. In Excel, use the <code>SUM</code> function:</p>
<ul>
<li>Total income: <code>=SUM(C2:C20)</code></li>
<li>Total expenses: <code>=SUM(D2:D20)</code></li>
<li>Profit: <code>=Total_Income_Cell - Total_Expense_Cell</code></li>
</ul>
<p>Using the example above, total income is K845 and total expenses are K180, so the profit is K665. Remember that profit is not the same as cash in the till; some profit may still be owed by customers or tied up in unsold stock.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open Excel and create a new blank workbook.</li>
<li>Type the cash book headings: Date, Description, Income, Expense, Balance.</li>
<li>Enter five real or imagined transactions for a small business in Kalomo.</li>
<li>Write formulas to calculate the running balance for each row.</li>
<li>Use the SUM function to calculate total income, total expenses, and profit.</li>
<li>Save the file as <code>Cash_Book_Practice.xlsx</code> in your <code>Money_Records</code> folder.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Cash book:</strong> A record of all money received and paid out by a business.</li>
<li><strong>Spreadsheet:</strong> A page of rows and columns used to enter and calculate data.</li>
<li><strong>Cell:</strong> A single box in a spreadsheet where a row and column meet, such as <code>B4</code>.</li>
<li><strong>Formula:</strong> A calculation written in a cell, starting with an equals sign.</li>
<li><strong>Profit:</strong> The amount left when total expenses are subtracted from total income.</li>
</ul>

<h2>Summary</h2>
<p>This lesson introduced simple bookkeeping using Microsoft Excel. You learned how to design a cash book, enter income and expenses, calculate running balances with formulas, and find the profit using the SUM function. A well-kept cash book gives any Zambian business owner a clear picture of financial health.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/excel-for-windows-training-9bc05390-e94c-46af-a5b3-d7c22f6990bb" target="_blank">Microsoft Support: Excel for Windows Training</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/introduction-to-excel/" target="_blank">Microsoft Learn: Introduction to Excel</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit-conversations/khan-academy-annual-report" target="_blank">Khan Academy: Financial Literacy Talks</a></li>
</ul>
HTML
            ],
            [
                'title' => '2.2 Stock Sheets and Inventory Tracking',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create a stock sheet in Excel, record opening stock, purchases, sales, and closing stock, and use simple formulas to warn you when items are running low. This helps any small shop or side business avoid running out of best-selling goods.</p>

<h2>Why Stock Control Matters</h2>
<p><strong>Stock</strong> is the goods a business keeps to sell. If you run a shop and run out of sugar, cooking oil, or airtime, you lose sales. If you buy too much stock, your cash is trapped on the shelf and some goods may expire. Good stock control means having the right amount of each item at the right time.</p>
<p>In Zambia, many small shops lose money because they do not track stock. The owner may think sales are good, but theft, damage, and forgotten purchases slowly eat the profit. A simple stock sheet helps you spot these problems early.</p>

<h2>What a Stock Sheet Tracks</h2>
<p>A basic stock sheet for a shop should show:</p>
<ul>
<li><strong>Item name</strong> such as "2-litre cooking oil" or "Airtel K5 airtime voucher".</li>
<li><strong>Opening stock</strong> at the start of the day or week.</li>
<li><strong>Stock received</strong> from suppliers during the period.</li>
<li><strong>Stock sold</strong> to customers.</li>
<li><strong>Closing stock</strong> remaining at the end of the period.</li>
<li><strong>Reorder level</strong>, the minimum quantity before you must buy more.</li>
</ul>

<h2>Worked Example: Tuckshop Stock Sheet</h2>
<p>Here is part of a stock sheet for a small tuckshop in Kalomo for the week beginning 1 June 2026:</p>
<table>
<tr><th>Item</th><th>Opening</th><th>Received</th><th>Sold</th><th>Closing</th><th>Reorder Level</th></tr>
<tr><td>Mealie meal 10 kg</td><td>15</td><td>20</td><td>18</td><td>17</td><td>10</td></tr>
<tr><td>Cooking oil 2 l</td><td>12</td><td>15</td><td>20</td><td>7</td><td>10</td></tr>
<tr><td>Soap bar</td><td>30</td><td>25</td><td>40</td><td>15</td><td>20</td></tr>
</table>
<p>The <strong>closing stock</strong> formula is <code>=Opening + Received - Sold</code>. For mealie meal, that is <code>=15+20-18</code>, giving 17 bags left.</p>

<h2>Using Conditional Formatting to Warn of Low Stock</h2>
<p>Excel can automatically highlight items that need reordering. To set this up:</p>
<ol>
<li>Select the closing stock column.</li>
<li>On the Home tab, click <strong>Conditional Formatting</strong>, then <strong>Highlight Cell Rules</strong>, then <strong>Less Than</strong>.</li>
<li>In the box, type the cell reference of the reorder level, or type a fixed number such as 10.</li>
<li>Choose a red fill and click OK.</li>
</ol>
<p>Now, whenever closing stock falls below the reorder level, the cell turns red and you know to buy more.</p>

<h2>Counting Physical Stock</h2>
<p>A stock sheet is only useful if it matches reality. Once a week, count the actual items on the shelf and compare the numbers with your sheet. If the sheet says you have ten bags of mealie meal but you only count eight, investigate. Perhaps stock was sold but not recorded, or perhaps stock was taken without permission. Either way, the difference is a warning sign.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open Excel and create a new stock sheet with these columns: Item, Opening, Received, Sold, Closing, Reorder Level.</li>
<li>Enter at least six items a real Kalomo shop might sell, such as mealie meal, cooking oil, soap, sugar, salt, and airtime vouchers.</li>
<li>Use formulas to calculate closing stock for each item.</li>
<li>Apply conditional formatting so any closing stock below the reorder level turns red.</li>
<li>Save the file as <code>Stock_Sheet_Practice.xlsx</code>.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Stock:</strong> Goods kept by a business for sale to customers.</li>
<li><strong>Opening stock:</strong> The quantity of stock at the start of a period.</li>
<li><strong>Closing stock:</strong> The quantity of stock remaining at the end of a period.</li>
<li><strong>Reorder level:</strong> The minimum quantity that should trigger a new purchase.</li>
<li><strong>Conditional formatting:</strong> Excel feature that changes cell appearance based on cell values.</li>
</ul>

<h2>Summary</h2>
<p>This lesson taught you how to build a practical stock sheet in Excel. You learned to record stock movements, calculate closing stock with formulas, and use conditional formatting to highlight low stock. Regular stock checks against the sheet help prevent lost sales and protect profit.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/use-conditional-formatting-to-highlight-information-fed60dfa-1d3f-4e13-9ecb-f1951ff89d7f" target="_blank">Microsoft Support: Conditional Formatting in Excel</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/get-started-with-formulas-and-functions/" target="_blank">Microsoft Learn: Get Started with Excel Formulas</a></li>
<li><a href="https://www.khanacademy.org/economics-finance-domain/core-finance/accounting-and-financial-stateme" target="_blank">Khan Academy: Accounting and Financial Statements</a></li>
</ul>
HTML
            ],
            [
                'title' => '2.3 Building a Basic Customer Database',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create a simple customer database in Excel, enter customer details safely, sort and filter the list, and understand why businesses must protect personal information such as phone numbers and National Registration Card numbers.</p>

<h2>Why a Customer Database Helps</h2>
<p>A <strong>customer database</strong> is an organised list of people who buy from you or may buy from you. It usually includes names, phone numbers, locations, and what they last bought. With this list you can send reminders about new stock, follow up on credit sales, and greet repeat customers by name. A shop that remembers its customers builds trust and gets more repeat business.</p>
<p>In Zambia, many small businesses keep customer details in a phone or a notebook. That works when the business is tiny, but as it grows a spreadsheet becomes easier to search, sort, and back up. Even a basic Excel database is better than losing a notebook.</p>

<h2>What to Include in a Customer Database</h2>
<p>Keep the database simple at first. Useful columns include:</p>
<ul>
<li><strong>Customer ID</strong> — a unique number for each customer.</li>
<li><strong>Full name</strong></li>
<li><strong>Phone number</strong></li>
<li><strong>Town or area</strong> such as Kalomo, Lusaka, or Mongu.</li>
<li><strong>Date added</strong></li>
<li><strong>Last purchase</strong></li>
<li><strong>Notes</strong> such as "buys on credit" or "prefers Airtel Money".</li>
</ul>
<p>Avoid collecting information you do not need. Do not ask for NRC numbers unless the law or your business process requires it. If you do collect sensitive data, store it securely and do not share it.</p>

<h2>Worked Example: Customer Database for a Side Business</h2>
<p>Suppose you sell chickens and eggs as a side business in Kalomo. Your database might look like this:</p>
<table>
<tr><th>ID</th><th>Name</th><th>Phone</th><th>Area</th><th>Last Purchase</th><th>Notes</th></tr>
<tr><td>001</td><td>Mrs. Namonda</td><td>0977-123456</td><td>Kalomo Central</td><td>05-Jun-2026</td><td>Prefers broilers</td></tr>
<tr><td>002</td><td>Mr. Hamoonga</td><td>0966-654321</td><td>Sichifulo</td><td>02-Jun-2026</td><td>Pays cash</td></tr>
<tr><td>003</td><td>Chileshe Shop</td><td>0975-111222</td><td>Kalomo Central</td><td>07-Jun-2026</td><td>Credit allowed, 7 days</td></tr>
</table>
<p>To create this in Excel, type the headings in row 1, then enter each customer in a new row. Use the Format as Table feature to make sorting and filtering easier. Select the whole data range, then on the Home tab click <strong>Format as Table</strong> and choose a style.</p>

<h2>Sorting and Filtering</h2>
<p>Once your data is formatted as a table, drop-down arrows appear in the heading row. You can:</p>
<ul>
<li><strong>Sort A to Z</strong> by customer name.</li>
<li><strong>Filter</strong> to show only customers in Kalomo Central.</li>
<li><strong>Sort by date</strong> to see who has not bought recently and may need a reminder.</li>
</ul>
<p>These simple tools turn a long list into useful information quickly.</p>

<h2>Protecting Customer Information</h2>
<p>Customer data is private. In Zambia, the Data Protection Act sets rules for how personal information must be handled. Even without reading the whole Act, follow these practical rules:</p>
<ul>
<li>Collect only what you need.</li>
<li>Keep the database on a password-protected computer or encrypted flash disk.</li>
<li>Do not send customer lists by WhatsApp to people who do not need them.</li>
<li>Delete information for customers who ask you to.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Open Excel and create a customer database with the columns listed above.</li>
<li>Enter at least ten imaginary customers for a small business of your choice.</li>
<li>Apply Format as Table to the data range.</li>
<li>Filter the list to show only customers from one area.</li>
<li>Sort the list by last purchase date, oldest first.</li>
<li>Save the file as <code>Customer_Database_Practice.xlsx</code>.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Customer database:</strong> An organised list of customer details used for communication and sales.</li>
<li><strong>Sort:</strong> To arrange data in a particular order, such as alphabetical or by date.</li>
<li><strong>Filter:</strong> To display only rows that meet certain conditions.</li>
<li><strong>Personal data:</strong> Information about a person, such as phone numbers, addresses, or NRC numbers.</li>
<li><strong>Data protection:</strong> Rules and practices that keep personal information safe.</li>
</ul>

<h2>Summary</h2>
<p>This lesson showed you how to build and use a basic customer database in Excel. You learned what columns to include, how to sort and filter data, and why protecting personal information matters. A tidy customer database helps a small Zambian business stay organised and treat customers well.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/sort-data-in-a-range-or-table-0d497e30-80a7-4e4e-80c9-27d6e5d5b3a9" target="_blank">Microsoft Support: Sort Data in Excel</a></li>
<li><a href="https://support.microsoft.com/en-us/office/filter-data-in-a-range-or-table-01832226-31b5-4568-8786-49f087d115ed" target="_blank">Microsoft Support: Filter Data in Excel</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/create-manage-excel-tables/" target="_blank">Microsoft Learn: Create and Manage Excel Tables</a></li>
</ul>
HTML
            ],
        ];
    }

    private function module3Lessons(): array
    {
        return [
            [
                'title' => '3.1 Email Etiquette for Business',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to write clear, polite, and professional business emails. You will know how to use subject lines, greetings, paragraphs, and sign-offs correctly, and you will understand the common mistakes that make emails look careless or confusing.</p>

<h2>Why Email Etiquette Matters</h2>
<p>Email is still one of the most important ways that businesses, government offices, and NGOs communicate in Zambia. A well-written email can get you a job interview, resolve a payment dispute, or confirm an order with a supplier. A badly written email can create confusion, offend the reader, or damage your reputation.</p>
<p>Email etiquette is not about using big words. It is about being clear, polite, and respectful of the reader's time. When someone opens your email, they should immediately understand who you are, why you are writing, and what you want them to do.</p>

<h2>Parts of a Professional Email</h2>
<p>A good business email contains:</p>
<ul>
<li><strong>Subject line:</strong> A short, clear summary of the message.</li>
<li><strong>Greeting:</strong> "Dear Mr. Banda," or "Dear Sir/Madam," for formal emails; "Hello Grace," for colleagues you know.</li>
<li><strong>Opening:</strong> One sentence stating the purpose of the email.</li>
<li><strong>Body:</strong> Short paragraphs with the details.</li>
<li><strong>Closing request:</strong> A polite sentence saying what you need next.</li>
<li><strong>Sign-off:</strong> "Yours sincerely," or "Yours faithfully," followed by your name and contact details.</li>
</ul>

<h2>Subject Line Rules</h2>
<p>The subject line is the first thing the reader sees. It should be specific and short. Avoid vague subjects such as "Hello" or "Urgent". Instead, use:</p>
<ul>
<li>"Request for quotation: A4 printing paper"</li>
<li>"Payment confirmation for invoice INV-0024"</li>
<li>"Application for Office Assistant position"</li>
</ul>

<h2>Worked Example: Email to a Supplier</h2>
<blockquote>
<p><strong>Subject:</strong> Request for quotation: A4 paper and ink cartridges</p>
<p>Dear Sir/Madam,</p>
<p>I am writing on behalf of Edutrack Computer Training College in Kalomo. We would like to request a quotation for the following items:</p>
<ul>
<li>Five reams of A4 paper</li>
<li>Two black ink cartridges, model number HP 650</li>
</ul>
<p>Please include delivery charges to Kalomo in your quotation. We would appreciate a reply by Friday, 19 June 2026.</p>
<p>Yours faithfully,</p>
<p>Grace Mwenda<br>
Administrator<br>
Edutrack Computer Training College<br>
Email: admin@edutrackzambia.com<br>
Phone: +260 770 666 937</p>
</blockquote>

<h2>Common Mistakes to Avoid</h2>
<ul>
<li><strong>All capital letters:</strong> THIS LOOKS LIKE SHOUTING. Use capital letters only for headings or emphasis.</li>
<li><strong>No subject line:</strong> Emails without subjects are easily ignored or marked as spam.</li>
<li><strong>Long, unclear paragraphs:</strong> Break your message into short paragraphs with blank lines between them.</li>
<li><strong>Informal language in formal emails:</strong> Avoid slang and abbreviations such as "lol" or "btw".</li>
<li><strong>Large attachments without warning:</strong> Mention an attachment in the email body and keep files small when possible.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Open Gmail, Outlook, or any email account.</li>
<li>Compose a formal business email to a supplier requesting prices for three items your shop needs.</li>
<li>Use a clear subject line, polite greeting, short paragraphs, and a proper sign-off.</li>
<li>Send the email to yourself or to a classmate for feedback.</li>
<li>Save a copy of the sent email or take a screenshot for your records.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Subject line:</strong> The short line that summarises an email's purpose.</li>
<li><strong>Greeting:</strong> The opening words of an email, such as "Dear Sir/Madam,".</li>
<li><strong>Sign-off:</strong> The closing words before your name, such as "Yours faithfully,".</li>
<li><strong>Attachment:</strong> A file sent with an email, such as a PDF or Word document.</li>
<li><strong>Etiquette:</strong> The rules of polite and professional behaviour.</li>
</ul>

<h2>Summary</h2>
<p>This lesson covered the basics of professional email communication. You learned the parts of a business email, how to write effective subject lines, and common mistakes to avoid. Polite and clear emails help you build trust with employers, suppliers, and customers.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/mail/answer/8395" target="_blank">Google Support: Write Effective Emails</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/outlook-send-email/" target="_blank">Microsoft Learn: Send and Receive Email in Outlook</a></li>
<li><a href="https://www.w3schools.com/html/html_links.asp" target="_blank">W3Schools: HTML Links</a></li>
</ul>
HTML
            ],
            [
                'title' => '3.2 Mobile Money Record-Keeping',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to record mobile money transactions accurately, match transaction messages with your cash book, and avoid common errors when handling Airtel Money and MTN MoMo payments in a small business.</p>

<h2>Mobile Money in Zambia</h2>
<p>Airtel Money and MTN Mobile Money have changed how Zambians send and receive money. A customer in Lusaka can pay a supplier in Kalomo instantly. A shop can accept payments without handling large amounts of cash. A civil servant can receive a loan repayment without travelling to the bank. However, mobile money is only useful if you keep good records.</p>
<p>Every mobile money transaction produces a message on the sender's phone and a confirmation message on the receiver's phone. These messages are your receipts. If you do not record them, you will not know who paid, when they paid, or how much they paid.</p>

<h2>Information in a Mobile Money Message</h2>
<p>A typical MTN or Airtel Money confirmation message contains:</p>
<ul>
<li><strong>Transaction ID</strong> — a unique code such as <code>TXN123456789</code>.</li>
<li><strong>Amount</strong> in Kwacha.</li>
<li><strong>Sender and receiver phone numbers</strong> or names.</li>
<li><strong>Date and time</strong> of the transaction.</li>
<li><strong>Balance</strong> remaining in the wallet.</li>
</ul>
<p>Record the transaction ID carefully. If a customer says they paid but the money did not arrive, the transaction ID is the first thing the mobile money agent will ask for.</p>

<h2>Recording Mobile Money in Your Cash Book</h2>
<p>Mobile money payments are still money received by the business. Treat them the same as cash sales in your cash book. Add a column called "Mobile Money Reference" so you can match each entry with the SMS or app message. Your cash book might look like this:</p>
<table>
<tr><th>Date</th><th>Description</th><th>Cash (K)</th><th>MoMo (K)</th><th>Reference</th></tr>
<tr><td>08 Jun</td><td>Sold airtime vouchers</td><td>50.00</td><td></td><td></td></tr>
<tr><td>08 Jun</td><td>Payment from Mrs. Namonda</td><td></td><td>180.00</td><td>TXN987654321</td></tr>
<tr><td>09 Jun</td><td>Sold mealie meal</td><td>360.00</td><td></td><td></td></tr>
<tr><td>09 Jun</td><td>Payment from Chileshe Shop</td><td></td><td>420.00</td><td>TXN123456789</td></tr>
</table>

<h2>Worked Example: Reconciling a Day's Mobile Money</h2>
<p>At the end of the day, Mr. Banda checks his MTN MoMo wallet and sees a balance of K850. He records every transaction message and compares them with the amounts in his cash book. The total of all MoMo payments in his cash book is K850. The numbers match, so he is happy. If the totals did not match, he would check each transaction ID until he found the error.</p>

<h2>Safety Rules for Mobile Money</h2>
<ul>
<li>Never share your PIN with anyone, including people who claim to be from the mobile money company.</li>
<li>Always confirm the sender's name and amount before handing over goods.</li>
<li>Keep screenshots or SMS records of every transaction for at least one month.</li>
<li>Withdraw large balances regularly so you do not lose everything if your phone is stolen.</li>
<li>Use a separate business phone or SIM if possible, not your personal line.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Open your Excel cash book from lesson 2.1.</li>
<li>Add a column called "Mobile Money Reference".</li>
<li>Enter at least four transactions: two cash sales and two mobile money payments.</li>
<li>For each mobile money payment, write a realistic transaction ID such as <code>TXN123456789</code>.</li>
<li>Calculate total cash, total mobile money, and combined income using SUM formulas.</li>
<li>Save the file as <code>Cash_Book_With_MoMo.xlsx</code>.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Mobile money:</strong> A service that allows money to be sent, received, and stored using a mobile phone.</li>
<li><strong>Transaction ID:</strong> A unique code that identifies a single mobile money payment.</li>
<li><strong>Wallet:</strong> The electronic account linked to a mobile money phone number.</li>
<li><strong>Reconcile:</strong> To compare two sets of records to check that they agree.</li>
<li><strong>PIN:</strong> A secret number used to authorise mobile money transactions.</li>
</ul>

<h2>Summary</h2>
<p>This lesson explained how to record and manage mobile money transactions in a small business. You learned what information appears in confirmation messages, how to add mobile money to your cash book, and how to keep mobile money safe. Good records turn mobile money from a risk into a useful business tool.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/docs/answer/3094054" target="_blank">Google Support: Add Numbers in Google Sheets</a></li>
<li><a href="https://support.microsoft.com/en-us/office/sum-function-043e1c7d-7726-4e80-8f32-07b23e057f89" target="_blank">Microsoft Support: SUM Function in Excel</a></li>
<li><a href="https://www.consumerfinance.gov/consumer-tools/money-as-you-grow/" target="_blank">Consumer Financial Protection Bureau: Money as You Grow</a></li>
</ul>
HTML
            ],
            [
                'title' => '3.3 Printing, Scanning, and Copying Workflows',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to print a document correctly, make a clear photocopy, scan a document to a PDF, and choose the right settings for paper size, colour, and number of copies. You will also learn how to handle common problems such as paper jams and low ink.</p>

<h2>The Multi-Function Printer</h2>
<p>Most offices and colleges in Zambia use a <strong>multi-function printer</strong>, which can print, copy, and scan. This machine saves space and money because one device does three jobs. Whether you work in a government office, an NGO, or a small business, you will probably use one every day. Knowing how to use it confidently makes you more valuable at work.</p>

<h2>Printing a Document</h2>
<p>Before you press print, check these settings:</p>
<ul>
<li><strong>Printer:</strong> Make sure the correct printer is selected.</li>
<li><strong>Number of copies:</strong> Choose how many you need.</li>
<li><strong>Colour or black and white:</strong> Colour is more expensive, so use black and white unless colour is necessary.</li>
<li><strong>Paper size:</strong> A4 is the standard size in Zambia.</li>
<li><strong>Orientation:</strong> Portrait for letters, landscape for wide tables.</li>
<li><strong>Pages:</strong> Print all pages or only the pages you need.</li>
</ul>
<p>Always use <strong>Print Preview</strong> before printing many copies. It shows you whether the document looks correct on the page and helps you avoid wasting paper and ink.</p>

<h2>Worked Example: Printing an Invoice</h2>
<p>Suppose you need to print two copies of the invoice you created in lesson 1.3. Here is the workflow:</p>
<ol>
<li>Open the Word document.</li>
<li>Press <strong>Ctrl + P</strong> to open the print screen.</li>
<li>Check that the printer name is correct.</li>
<li>Set copies to 2.</li>
<li>Choose <strong>A4</strong> paper and <strong>Portrait</strong> orientation.</li>
<li>Click <strong>Print Preview</strong> to confirm the invoice fits on one page.</li>
<li>Click <strong>Print</strong>.</li>
</ol>

<h2>Making Photocopies</h2>
<p>Photocopying is straightforward, but quality matters. Follow these steps:</p>
<ol>
<li>Lift the lid and place the document face down on the glass, aligning it with the corner mark.</li>
<li>Close the lid gently.</li>
<li>On the control panel, choose the number of copies.</li>
<li>Select colour or black and white.</li>
<li>Choose the paper size, usually A4.</li>
<li>Press the Start or Copy button.</li>
</ol>
<p>For multiple pages, use the automatic document feeder if the machine has one. Place pages face up in the tray. Check the first copy before making many copies, especially for important documents such as certificates or NRC copies.</p>

<h2>Scanning a Document</h2>
<p>Scanning turns a paper document into an electronic file, usually a PDF. This is useful when you need to email a signed letter, keep a backup of an invoice, or submit documents online. The basic steps are:</p>
<ol>
<li>Place the document on the scanner glass or in the feeder.</li>
<li>Open the scanner software on the computer.</li>
<li>Choose the file type, usually PDF.</li>
<li>Select colour or black and white. Use black and white for text documents to keep file sizes small.</li>
<li>Choose the resolution. 200 or 300 DPI is usually enough.</li>
<li>Click Scan and save the file with a clear name.</li>
</ol>

<h2>Handling Common Problems</h2>
<ul>
<li><strong>Paper jam:</strong> Open the printer cover, remove the jammed paper gently, close the cover, and try again.</li>
<li><strong>Faded prints:</strong> The toner or ink may be low. Check the levels and replace if needed.</li>
<li><strong>Streaks on copies:</strong> Clean the scanner glass with a soft, dry cloth.</li>
<li><strong>Printer not responding:</strong> Check that the printer is turned on, connected to the computer or network, and not showing an error message.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>On a college computer, open one of your Word documents from this course.</li>
<li>Use Print Preview to check how it looks on A4 paper.</li>
<li>Print one copy in black and white.</li>
<li>Photocopy one page from a book or handout. Check that the copy is clear.</li>
<li>Scan a paper document to PDF and save it as <code>Scanned_Document.pdf</code>.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Multi-function printer:</strong> A device that can print, photocopy, and scan.</li>
<li><strong>Portrait:</strong> Page orientation where the page is taller than it is wide.</li>
<li><strong>Landscape:</strong> Page orientation where the page is wider than it is tall.</li>
<li><strong>PDF:</strong> Portable Document Format, a file type that keeps the same appearance on any device.</li>
<li><strong>DPI:</strong> Dots per inch, a measure of scanning or printing quality.</li>
</ul>

<h2>Summary</h2>
<p>This lesson covered the practical skills of printing, photocopying, and scanning. You learned how to choose correct settings, use print preview, make clear copies, and scan to PDF. You also learned how to fix common printer problems. These workflows are essential in almost every Zambian office and shop.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/print-documents-in-word-46f49e17-36a7-4694-a820-6e3a99ce4f51" target="_blank">Microsoft Support: Print Documents in Word</a></li>
<li><a href="https://support.microsoft.com/en-us/windows/print-and-scan-from-windows-906acbc3-caab-49bf-8e3f-7b885eb75d5e" target="_blank">Microsoft Support: Print and Scan from Windows</a></li>
<li><a href="https://www.libreoffice.org/discover/what-is-libreoffice/" target="_blank">LibreOffice: What is LibreOffice?</a></li>
</ul>
HTML
            ],
        ];
    }

    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: Computer Essentials and Business Documents',
            'description' => 'Test your knowledge of computer basics, typing, Microsoft Word, business letters, and invoices.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which part of a computer displays pictures and words on a screen?',
                    'explanation' => 'The monitor is the screen that displays output from the computer.',
                    'options' => [
                        ['text' => 'Keyboard', 'is_correct' => false],
                        ['text' => 'Monitor', 'is_correct' => true],
                        ['text' => 'Mouse', 'is_correct' => false],
                        ['text' => 'CPU', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the safest way to turn off a Windows computer at the end of the day?',
                    'explanation' => 'Using the Start menu Shut down option closes programs and saves settings safely.',
                    'options' => [
                        ['text' => 'Pull out the power cable', 'is_correct' => false],
                        ['text' => 'Press the power button quickly', 'is_correct' => false],
                        ['text' => 'Click Start, then Power, then Shut down', 'is_correct' => true],
                        ['text' => 'Close the monitor lid only', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'The home keys for touch typing are A, S, D, F for the left hand and J, K, L, ; for the right hand.',
                    'explanation' => 'These are the resting positions for touch typing on a standard QWERTY keyboard.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In Microsoft Word, which toolbar contains buttons for font size, bold, and alignment?',
                    'explanation' => 'The Ribbon contains tabs and tools including font size, bold, and alignment on the Home tab.',
                    'options' => [
                        ['text' => 'Taskbar', 'is_correct' => false],
                        ['text' => 'Ribbon', 'is_correct' => true],
                        ['text' => 'Scrollbar', 'is_correct' => false],
                        ['text' => 'Status bar', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of these is the correct order for parts of a formal business letter?',
                    'explanation' => 'A formal business letter places the sender address first, then date, recipient address, salutation, subject, body, closing, and signature.',
                    'options' => [
                        ['text' => 'Body, date, subject, sender address', 'is_correct' => false],
                        ['text' => 'Sender address, date, recipient address, salutation, subject, body, closing', 'is_correct' => true],
                        ['text' => 'Subject first, then body, then sender address', 'is_correct' => false],
                        ['text' => 'Signature, date, body, recipient address', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'An invoice is a document sent to a customer showing how much money the customer owes for goods or services.',
                    'explanation' => 'An invoice requests payment and lists items, quantities, prices, and the total amount due.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What keyboard shortcut saves a document in Microsoft Word? (one word)',
                    'explanation' => 'Pressing Ctrl + S saves the current document quickly.',
                    'correct_answer' => 'Ctrl+S',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'A flash disk is useful in a Zambian office mainly because it allows you to:',
                    'explanation' => 'Flash disks store files and let you move them between computers, which is helpful when internet access is unreliable.',
                    'options' => [
                        ['text' => 'Make phone calls', 'is_correct' => false],
                        ['text' => 'Copy files from one computer to another', 'is_correct' => true],
                        ['text' => 'Connect to the internet', 'is_correct' => false],
                        ['text' => 'Print documents faster', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which font size is generally best for the body text of a business letter?',
                    'explanation' => 'Size 12 is standard and readable for business letter body text.',
                    'options' => [
                        ['text' => '8', 'is_correct' => false],
                        ['text' => '10', 'is_correct' => false],
                        ['text' => '12', 'is_correct' => true],
                        ['text' => '20', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'In Word, using all capital letters for a whole paragraph is considered polite emphasis.',
                    'explanation' => 'All capitals look like shouting and should not be used for whole paragraphs.',
                    'correct_answer' => 'False',
                ],
            ],
        ];
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Spreadsheets for Business Records',
            'description' => 'Test your knowledge of Excel cash books, stock sheets, customer databases, and simple formulas.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is a cash book used for in a small business?',
                    'explanation' => 'A cash book records all money received and paid out, helping the owner track income, expenses, and profit.',
                    'options' => [
                        ['text' => 'To design posters', 'is_correct' => false],
                        ['text' => 'To record all money in and money out', 'is_correct' => true],
                        ['text' => 'To send emails', 'is_correct' => false],
                        ['text' => 'To print invoices', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In Excel, what does the formula =SUM(C2:C10) do?',
                    'explanation' => 'The SUM function adds all numbers in the range C2 through C10.',
                    'options' => [
                        ['text' => 'Counts the number of cells', 'is_correct' => false],
                        ['text' => 'Adds the values in cells C2 to C10', 'is_correct' => true],
                        ['text' => 'Finds the highest value in column C', 'is_correct' => false],
                        ['text' => 'Multiplies C2 by C10', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Profit is calculated by subtracting total expenses from total income.',
                    'explanation' => 'Profit equals income minus expenses, whether calculated in a cash book or any other record.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which Excel feature can automatically highlight stock that has fallen below the reorder level?',
                    'explanation' => 'Conditional formatting changes cell appearance based on values, such as turning low stock red.',
                    'options' => [
                        ['text' => 'Sort', 'is_correct' => false],
                        ['text' => 'Filter', 'is_correct' => false],
                        ['text' => 'Conditional formatting', 'is_correct' => true],
                        ['text' => 'Find and Replace', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In a stock sheet, what is the correct formula for closing stock?',
                    'explanation' => 'Closing stock equals opening stock plus stock received minus stock sold.',
                    'options' => [
                        ['text' => 'Opening + Sold - Received', 'is_correct' => false],
                        ['text' => 'Received - Opening + Sold', 'is_correct' => false],
                        ['text' => 'Opening + Received - Sold', 'is_correct' => true],
                        ['text' => 'Sold - Received', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A customer database should collect as much personal information as possible, including NRC numbers, even if it is not needed.',
                    'explanation' => 'Collect only the information you need and protect personal data such as NRC numbers carefully.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'In Excel, every cell has an address made from a column letter and a row number. What is the address of the cell in column B and row 5? (one word)',
                    'explanation' => 'Cell addresses combine the column letter and row number, so column B row 5 is B5.',
                    'correct_answer' => 'B5',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which Excel tool adds drop-down arrows to headings so you can show only certain rows?',
                    'explanation' => 'Filter hides rows that do not match the criteria you choose.',
                    'options' => [
                        ['text' => 'Sort', 'is_correct' => false],
                        ['text' => 'Filter', 'is_correct' => true],
                        ['text' => 'Format Painter', 'is_correct' => false],
                        ['text' => 'Freeze Panes', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is it important to count physical stock and compare it with the stock sheet?',
                    'explanation' => 'Comparing physical stock with the stock sheet reveals errors, theft, or unrecorded sales.',
                    'options' => [
                        ['text' => 'To make the spreadsheet longer', 'is_correct' => false],
                        ['text' => 'To check that the sheet matches reality', 'is_correct' => true],
                        ['text' => 'To delete old data', 'is_correct' => false],
                        ['text' => 'To change font colours', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'In Excel, Format as Table can make sorting and filtering a customer database easier.',
                    'explanation' => 'Formatting data as a table adds filter arrows and makes the data easier to manage.',
                    'correct_answer' => 'True',
                ],
            ],
        ];
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Digital Communication and Office Operations',
            'description' => 'Test your knowledge of business email etiquette, mobile money records, and printing, scanning, and copying workflows.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which part of an email tells the reader what the message is about before they open it?',
                    'explanation' => 'The subject line summarises the email\'s purpose and helps the reader prioritise.',
                    'options' => [
                        ['text' => 'Greeting', 'is_correct' => false],
                        ['text' => 'Subject line', 'is_correct' => true],
                        ['text' => 'Signature', 'is_correct' => false],
                        ['text' => 'Attachment', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which greeting is most appropriate for a formal business email to someone you have not met?',
                    'explanation' => '"Dear Sir/Madam," is a polite and widely accepted formal greeting when you do not know the recipient.',
                    'options' => [
                        ['text' => 'Hey there,', 'is_correct' => false],
                        ['text' => 'Dear Sir/Madam,', 'is_correct' => true],
                        ['text' => 'Yo buddy,', 'is_correct' => false],
                        ['text' => 'Hi friend,', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'It is safe to share your mobile money PIN with an agent who calls to fix your account.',
                    'explanation' => 'No legitimate mobile money provider will ask for your PIN. Sharing it is a serious security risk.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should you record in your cash book for every mobile money payment?',
                    'explanation' => 'The transaction ID links the cash book entry to the mobile money SMS or app confirmation.',
                    'options' => [
                        ['text' => 'The customer\'s home address only', 'is_correct' => false],
                        ['text' => 'The transaction ID or reference number', 'is_correct' => true],
                        ['text' => 'The colour of the customer\'s phone', 'is_correct' => false],
                        ['text' => 'The agent\s name only', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the standard paper size for most business documents in Zambia?',
                    'explanation' => 'A4 is the standard paper size used in offices, colleges, and printers in Zambia.',
                    'options' => [
                        ['text' => 'Letter', 'is_correct' => false],
                        ['text' => 'A3', 'is_correct' => false],
                        ['text' => 'A4', 'is_correct' => true],
                        ['text' => 'A5', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'When scanning a text document, using black and white instead of colour usually produces a smaller file size.',
                    'explanation' => 'Black and white scanning uses fewer colours and creates smaller PDF files, which are easier to email.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What should you always check before printing many copies of a document? (two words)',
                    'explanation' => 'Print Preview shows how the document will look on paper and helps avoid wasted prints.',
                    'correct_answer' => 'Print Preview',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of these is a poor practice in a formal business email?',
                    'explanation' => 'Writing in all capital letters appears aggressive, like shouting, and is unprofessional.',
                    'options' => [
                        ['text' => 'Using a clear subject line', 'is_correct' => false],
                        ['text' => 'Writing the whole email in capital letters', 'is_correct' => true],
                        ['text' => 'Including your contact details', 'is_correct' => false],
                        ['text' => 'Keeping paragraphs short', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should you do first if the printer shows a paper jam?',
                    'explanation' => 'Open the printer cover and remove the jammed paper gently before trying to print again.',
                    'options' => [
                        ['text' => 'Hit the printer firmly', 'is_correct' => false],
                        ['text' => 'Turn off the computer monitor', 'is_correct' => false],
                        ['text' => 'Open the cover and remove the jammed paper carefully', 'is_correct' => true],
                        ['text' => 'Print the document again immediately', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A multi-function printer can usually print, photocopy, and scan documents.',
                    'explanation' => 'Multi-function printers combine printing, copying, and scanning in one device.',
                    'correct_answer' => 'True',
                ],
            ],
        ];
    }

    private function createAssignments(): void
    {
        Assignment::create([
            'course_id' => $this->courseId,
            'lesson_id' => null,
            'title' => 'Create a Business Letter and Invoice',
            'description' => 'Use Microsoft Word to produce a professional quotation request letter and a sales invoice for a small Zambian business.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Open Microsoft Word and create a new blank document.
Step 2: Write a formal business letter from a small shop in Kalomo to a supplier in Lusaka. Request a quotation for three items your shop needs, such as mealie meal, cooking oil, and soap.
Step 3: Include the sender address, date, recipient address, salutation, subject line, body, closing, and signature block.
Step 4: Save this document as "Quotation_Letter.docx".
Step 5: Create a second Word document: a sales invoice for one customer who bought at least four items from your shop.
Step 6: Include item names, quantities, unit prices in Kwacha, line totals, and a grand total. Use a table to present the information neatly.
Step 7: Save the invoice as "Sales_Invoice.docx".
Step 8: Submit both documents as a ZIP file, or as separate uploads if the system allows.
INSTRUCTIONS,
            'max_points' => 100,
            'passing_points' => 50,
            'due_date' => null,
            'allow_late_submission' => 1,
            'allowed_file_types' => 'pdf,doc,docx,zip',
            'max_file_size_mb' => 10,
        ]);

        Assignment::create([
            'course_id' => $this->courseId,
            'lesson_id' => null,
            'title' => 'Build a Cash Book and Stock Sheet in Excel',
            'description' => 'Create an Excel workbook with a simple cash book and a stock sheet for a small business, using formulas and conditional formatting.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Open Microsoft Excel and create a new blank workbook.
Step 2: On the first worksheet, create a cash book with these columns: Date, Description, Cash Income, Mobile Money Income, Expenses, Mobile Money Reference, and Balance.
Step 3: Enter at least eight transactions for a small business in Kalomo. Include both cash and mobile money sales, and at least two expenses such as transport or stock purchase.
Step 4: Use formulas to calculate the running balance for each row, and use the SUM function to find total income, total expenses, and profit.
Step 5: On a second worksheet, create a stock sheet with these columns: Item, Opening Stock, Stock Received, Stock Sold, Closing Stock, and Reorder Level.
Step 6: Enter at least six items a shop might sell. Use formulas to calculate closing stock for each item.
Step 7: Apply conditional formatting so any closing stock below the reorder level is highlighted in red.
Step 8: Save the workbook as "Cash_Book_and_Stock.xlsx" and submit it.
INSTRUCTIONS,
            'max_points' => 100,
            'passing_points' => 50,
            'due_date' => null,
            'allow_late_submission' => 1,
            'allowed_file_types' => 'pdf,xls,xlsx',
            'max_file_size_mb' => 10,
        ]);
    }

    private function printSummary(): void
    {
        $this->command->info('Computer & Business Handling content seeded successfully.');
        $this->command->info('Modules: 3 | Lessons: 9 | Quizzes: 3 | Assignments: 2');
    }
}
