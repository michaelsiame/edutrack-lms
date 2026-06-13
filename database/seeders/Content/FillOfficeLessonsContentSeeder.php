<?php

namespace Database\Seeders\Content;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Fills the legacy empty lessons of the Microsoft Office Suite course and
 * selected Cybersecurity Fundamentals lessons with real lesson notes, by lesson id.
 * Idempotent: only writes where the current content is < 200 chars, and
 * converts the no-video placeholder Video lessons to Reading.
 */
class FillOfficeLessonsContentSeeder extends Seeder
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

        $this->command->info("Office and Cybersecurity lessons content: {$filled} filled, {$skipped} already had content.");
    }

    private function lessons(): array
    {
        return [
            // ===================== CERTIFICATE IN MICROSOFT OFFICE SUITE (course 1) =====================
            // ----- WORD MODULE -----
            28 => <<<'HTML'
<h2>Word Module Notes</h2>
<p>By the end of this module you will be able to create, format and print professional documents using Microsoft Word - everything from a school letter to a church flyer - and you will understand how each topic in the module builds towards that goal.</p>
<h3>What is Microsoft Word for?</h3>
<p>Microsoft Word is a word processor. In plain terms, it is a program for typing, editing and presenting text on a page. If you have ever typed a letter, a school report, an invoice or a programme, you were doing word processing. In an office, a school or a small business in Zambia, Word is the tool most people reach for first.</p>
<p>At Edutrack this module teaches the skills employers expect from clerks, receptionists, teachers, marketers and administrators. You do not need to memorise every button - you need to know the right tool for each job and where to find it.</p>
<h3>Key skills covered in this module</h3>
<ul>
<li><strong>Creating, saving and opening documents.</strong> Starting a new file, giving it a sensible name, saving it where you can find it again, and opening older files.</li>
<li><strong>Typing and basic text formatting.</strong> Font, size, bold, italic, underline and colour; selecting text; using cut, copy and paste.</li>
<li><strong>Paragraph formatting.</strong> Alignment, line spacing, indentation, bullets and numbering, and styles that keep long documents tidy.</li>
<li><strong>Tables.</strong> Inserting rows and columns, merging cells, adding borders and shading, and using tables to present lists of information clearly.</li>
<li><strong>Objects.</strong> Inserting pictures, shapes and WordArt, and controlling how text flows around them.</li>
<li><strong>Page setup.</strong> Margins, orientation, paper size, headers, footers and page numbers.</li>
<li><strong>Mail merge.</strong> Creating one letter or label and printing it for many people by linking to a list of names and addresses.</li>
<li><strong>Printing and PDF export.</strong> Previewing before you print, choosing the right printer, and saving as PDF for WhatsApp or email.</li>
</ul>
<h3>How the module is structured</h3>
<p>The module begins with the Word screen and simple text work. Once you can save and format a paragraph confidently, you move on to longer documents and page layout. Objects and tables come next because most real documents combine text with visuals. Mail merge and final printing are saved for last, since they assume you can build a clean document first.</p>
<h3>Try It Yourself</h3>
<ol>
<li>Open Microsoft Word and create a blank document.</li>
<li>Type your full name, your course and today's date on three separate lines.</li>
<li>Save the document to the Desktop as <code>Word_Practice_YourName.docx</code>.</li>
<li>Close Word and reopen the file to confirm you can find it again.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>Word processor</strong> - a program for creating and editing text documents.</li>
<li><strong>Ribbon</strong> - the toolbar area at the top of Word that holds the tabs and commands.</li>
<li><strong>Document</strong> - a file you create in Word, usually with a <code>.docx</code> extension.</li>
<li><strong>Mail merge</strong> - producing many personalised documents from one template and a list.</li>
<li><strong>PDF</strong> - a file format that preserves formatting and can be read on almost any device.</li>
</ul>
<h3>Summary</h3>
<p>This module covers the full Word workflow: create, format, enhance with tables and objects, set up the page, and share or print the finished document. Each lesson adds one practical skill that you can use immediately at school, church, work or in your own business.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://support.microsoft.com/en-us/word">Microsoft Support - Word</a></li>
<li><a href="https://support.google.com/docs/answer/2763168">Google Docs Help - Create and edit documents</a></li>
<li><a href="https://edu.gcfglobal.org/en/word2016/">GCFGlobal - Word 2016 Tutorial</a></li>
</ul>
HTML,

            29 => <<<'HTML'
<h2>Getting Started and Text Basics</h2>
<p>By the end of this lesson you will launch Microsoft Word confidently, understand the main areas of the screen, and create, save and open documents using basic text formatting.</p>
<h3>Launching Word</h3>
<p>On a Windows computer, click the Start button and type "Word". Select Microsoft Word from the results. On the opening screen you will see options for a blank document and various templates. For now, choose <strong>Blank document</strong> so you can practise without distractions.</p>
<h3>The Word screen</h3>
<p>Take a moment to spot these four areas:</p>
<ul>
<li><strong>The Ribbon</strong> - the row of tabs (Home, Insert, Layout, etc.) across the top. Each tab groups related tools.</li>
<li><strong>The Quick Access Toolbar</strong> - the small strip above the ribbon with Save, Undo and Redo.</li>
<li><strong>The Page</strong> - the white sheet in the middle where you type. This is your document.</li>
<li><strong>The Status Bar</strong> - the strip at the bottom showing page number, word count and zoom controls.</li>
</ul>
<h3>Creating, saving and opening</h3>
<p>Always save early. Press <code>Ctrl + S</code> (or File &gt; Save As) and choose a location such as Desktop or Documents. Give the file a clear name such as <code>Business_Letter_Draft.docx</code>. The <code>.docx</code> ending means it is a Word document. To open an existing file, press <code>Ctrl + O</code> or choose File &gt; Open.</p>
<h3>Basic text formatting</h3>
<p>Type this short text: "Kalomo General Dealers supplies quality goods to the Southern Province." Select any word by double-clicking it. On the Home tab use these buttons:</p>
<ul>
<li><strong>B</strong> - bold text.</li>
<li><strong><em>I</em></strong> - italic text.</li>
<li><strong><u>U</u></strong> - underline text.</li>
<li><strong>Font and Size</strong> - change the typeface and point size.</li>
<li><strong>Font Colour</strong> - change the colour of selected text.</li>
</ul>
<p>Try making "Kalomo General Dealers" bold and the whole sentence 12 pt in black. Avoid using too many colours in one document; in business, dark text on a white page looks professional.</p>
<h3>Try It Yourself</h3>
<ol>
<li>Create a new blank document and save it as <code>Text_Basics.docx</code>.</li>
<li>Type three sentences about a shop or service in your town.</li>
<li>Apply bold to the shop name, italic to one important phrase, and change one word to a different colour.</li>
<li>Use Save, close Word, and reopen the document.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>Ribbon tab</strong> - a category on the toolbar, such as Home or Insert.</li>
<li><strong>Save As</strong> - saving a document for the first time or under a new name.</li>
<li><strong>Font</strong> - the design of the letters, such as Calibri or Times New Roman.</li>
<li><strong>Point size</strong> - the height of text, measured in points.</li>
</ul>
<h3>Summary</h3>
<p>Word opens with a ribbon of tools and a blank page. Save your work, format text with bold, italic, underline, font and size, and you have the foundation of every Word document.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/word-for-windows-training-7bcd85e6-2c3d-4c3c-a2a5-5ed8847eae50">Microsoft Support - Word for Windows training</a></li>
<li><a href="https://edu.gcfglobal.org/en/word2016/getting-started-with-word/1/">GCFGlobal - Getting Started with Word</a></li>
</ul>
HTML,

            30 => <<<'HTML'
<h2>Formatting and Paragraph Layouts</h2>
<p>By the end of this lesson you will control the look and structure of paragraphs in Word, including alignment, spacing, indentation, bullets, numbering and styles, so your documents are easy to read and professionally laid out.</p>
<h3>Paragraph alignment</h3>
<p>Alignment decides how a paragraph sits between the margins. On the Home tab, use the four alignment buttons:</p>
<ul>
<li><strong>Left align</strong> - the default; text starts at the left margin.</li>
<li><strong>Centre</strong> - useful for headings, titles and invitations.</li>
<li><strong>Right align</strong> - useful for dates and signatures.</li>
<li><strong>Justify</strong> - spreads text evenly across the line, giving a neat edge on both sides; common in reports and letters.</li>
</ul>
<h3>Line spacing and indentation</h3>
<p>Line spacing controls the gap between lines. For most business documents, 1.15 or 1.5 is comfortable. School reports often use double spacing. On the Home tab click the Line and Paragraph Spacing button to choose a value.</p>
<p>Indentation moves a paragraph in from the margin. A first-line indent is the traditional way to start a paragraph in an essay. Increase Indent is useful for numbered steps or quotes.</p>
<h3>Bullets and numbering</h3>
<p>Bullets create unordered lists. Use them when order does not matter, such as a list of services. Numbering creates ordered lists and is best for instructions, agendas or steps. Place your cursor in the paragraph and click the Bullets or Numbering button on the Home tab. To start a new item, press Enter.</p>
<h3>Styles</h3>
<p>A style is a saved set of formatting choices. Instead of manually making every heading 16 pt, bold and blue, apply the Heading 1 style and Word does it for you. If you later change the style, every heading updates at once. Styles are the secret to long documents that look consistent.</p>
<h3>Try It Yourself</h3>
<ol>
<li>Create a document and type three paragraphs about a local event.</li>
<li>Centre the title, left-align the body, and right-align a made-up date.</li>
<li>Change line spacing to 1.5 for the whole document.</li>
<li>Turn the middle paragraph into a bulleted list of three items.</li>
<li>Apply Heading 1 to the title and Heading 2 to a subheading.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>Alignment</strong> - the horizontal position of text between margins.</li>
<li><strong>Line spacing</strong> - the gap between lines in a paragraph.</li>
<li><strong>Indentation</strong> - moving a paragraph in from the left or right margin.</li>
<li><strong>Style</strong> - a saved combination of formatting that can be applied repeatedly.</li>
</ul>
<h3>Summary</h3>
<p>Professional documents depend on clean alignment, spacing, lists and styles. Master these tools and every letter, report or agenda you produce will look organised and readable.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/change-the-spacing-between-lines-or-paragraphs-0b1e9269-0d31-4e9b-9b1c-1b1b1b1b1b1b">Microsoft Support - Line and paragraph spacing</a></li>
<li><a href="https://edu.gcfglobal.org/en/word2016/formatting-text/1/">GCFGlobal - Formatting Text in Word</a></li>
</ul>
HTML,

            31 => <<<'HTML'
<h2>Working with Objects and Tables</h2>
<p>By the end of this lesson you will insert and arrange tables, pictures, shapes and WordArt in a document, control how text wraps around objects, and set up page layout including margins, orientation, headers, footers and page numbers.</p>
<h3>Inserting a table</h3>
<p>Tables organise information in rows and columns. Click the Insert tab, then Table. Drag to choose the number of rows and columns, or click Insert Table to specify exact numbers. For example, a price list for a Kalomo shop might have columns for Item, Quantity and Price.</p>
<p>Once the table is inserted, use the Table Design and Layout tabs that appear to add borders, shading, merge cells or adjust column widths. Keep tables simple: clear headings and consistent alignment make data easy to read.</p>
<h3>Inserting pictures and shapes</h3>
<p>On the Insert tab choose Pictures to add an image from your computer, or Shapes to draw arrows, boxes and lines. After inserting a picture, the Picture Format tab appears. Use Wrap Text to decide how the picture sits with nearby text:</p>
<ul>
<li><strong>In Line with Text</strong> - the picture behaves like a large character.</li>
<li><strong>Square</strong> - text flows around the picture in a square shape.</li>
<li><strong>Tight</strong> - text follows the edges of the picture closely.</li>
<li><strong>Behind Text / In Front of Text</strong> - layers the picture behind or in front.</li>
</ul>
<h3>Page setup</h3>
<p>Open the Layout tab to change margins, orientation and paper size. A business letter usually uses Portrait A4 with normal margins. A flyer or certificate may use Landscape. Headers and footers are added by double-clicking at the top or bottom of the page, or by choosing Insert &gt; Header or Footer. Page numbers go in the footer or header, added from the Insert tab.</p>
<h3>Try It Yourself</h3>
<ol>
<li>Create a document and insert a table with three columns and four rows for a simple price list.</li>
<li>Fill the cells with headings and example items and prices.</li>
<li>Insert a picture from the computer and set its wrap text to Square.</li>
<li>Add a header with the shop name and a footer with page numbers.</li>
<li>Change the orientation to Landscape and compare the result.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>Table</strong> - rows and columns used to organise information.</li>
<li><strong>Wrap Text</strong> - how text flows around an inserted object.</li>
<li><strong>Orientation</strong> - Portrait (tall) or Landscape (wide).</li>
<li><strong>Header / Footer</strong> - repeated areas at the top and bottom of each page.</li>
</ul>
<h3>Summary</h3>
<p>Tables, pictures, shapes and page setup turn plain text into complete documents. Combine them with the formatting skills from earlier lessons and you can produce flyers, reports, letters and programmes that look polished.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/insert-a-table-in-word-3e4a4c98-1a60-4b64-a678-4c3c5c5c5c5c">Microsoft Support - Insert a table</a></li>
<li><a href="https://edu.gcfglobal.org/en/word2016/working-with-tables/1/">GCFGlobal - Working with Tables</a></li>
</ul>
HTML,

            32 => <<<'HTML'
<h2>Word Practical Project</h2>
<p>This is the assessed assignment for the Microsoft Word module. You will produce a single, fully formatted document that demonstrates the key skills you have learned.</p>
<h3>Objective</h3>
<p>Produce a professional one-page Word document. You may choose either a <strong>business letter</strong> to a supplier or a <strong>flyer</strong> for a local event such as a church fundraiser, school open day or community clean-up campaign. The document must show that you can use headings, text formatting, a table, a picture and a header/footer.</p>
<h3>Step-by-step what to produce</h3>
<ol>
<li>Create a new Word document and save it as <code>Word_Project_YourName.docx</code>.</li>
<li>Add a clear title or letter heading at the top.</li>
<li>Format the heading using a Heading style, centre alignment and a larger font size.</li>
<li>Write at least two paragraphs of body text. Apply bold, italic or underline to at least three phrases.</li>
<li>Insert a table with at least two columns and three rows. Fill it with relevant information such as items, dates or prices.</li>
<li>Insert one picture, shape or logo and set appropriate text wrapping.</li>
<li>Add a header with the document title or your name.</li>
<li>Add a footer with page numbers.</li>
<li>Check margins, spacing and overall appearance. Print or export to PDF.</li>
</ol>
<h3>What to submit</h3>
<p>Submit the <code>.docx</code> file through the learning system. If you export a PDF for printing, submit the PDF as well. Make sure your name appears in the filename and in the document header or footer.</p>
<h3>Marking criteria (out of 100)</h3>
<table>
<tr><th>Criteria</th><th>Marks</th></tr>
<tr><td>Correct document created and saved with proper filename</td><td>10</td></tr>
<tr><td>Clear title and professional layout</td><td>15</td></tr>
<tr><td>Effective text formatting (bold, italic, underline, font, size, alignment)</td><td>20</td></tr>
<tr><td>Well-structured table with appropriate content and formatting</td><td>20</td></tr>
<tr><td>Picture or object inserted with suitable wrap text</td><td>15</td></tr>
<tr><td>Header and footer included with relevant information</td><td>15</td></tr>
<tr><td>Overall neatness, spelling and presentation</td><td>5</td></tr>
<tr><td><strong>Total</strong></td><td><strong>100</strong></td></tr>
</table>
<h3>Important notes</h3>
<ul>
<li>All content must be appropriate and original.</li>
<li>Do not copy text from the internet without giving credit.</li>
<li>Ask your instructor if you need help saving or submitting the file.</li>
</ul>
HTML,

            // ----- EXCEL MODULE -----
            33 => <<<'HTML'
<h2>Excel Module Notes</h2>
<p>By the end of this module you will be able to build, format and analyse spreadsheets in Microsoft Excel - skills used daily by shops, schools, churches, banks and government offices across Zambia.</p>
<h3>What is Microsoft Excel for?</h3>
<p>Excel is a spreadsheet program. A spreadsheet is a grid of cells arranged in rows and columns where you can store text, numbers and formulas. Its real power is calculation: change one number and every formula that depends on it updates automatically. That makes Excel perfect for budgets, sales records, stock lists, results sheets and invoices.</p>
<p>Whether you plan to work in finance, run a small business, manage a school or assist in an office, spreadsheet skills make you far more productive. Employers regularly list Excel as a required or preferred skill.</p>
<h3>Key skills covered in this module</h3>
<ul>
<li><strong>Workbooks and worksheets.</strong> Understanding the Excel screen, creating new files, moving between sheets and renaming them.</li>
<li><strong>Data entry and cell formatting.</strong> Typing text, numbers and dates; changing fonts, colours, borders and number formats such as currency.</li>
<li><strong>Formulas.</strong> Writing calculations using +, -, * and /.</li>
<li><strong>Functions.</strong> Using built-in shortcuts such as SUM, AVERAGE, MAX, MIN and COUNT.</li>
<li><strong>Cell references.</strong> Relative references that adjust when copied, and absolute references that stay fixed.</li>
<li><strong>Sorting and filtering.</strong> Rearranging data to find the largest, smallest or matching entries.</li>
<li><strong>Conditional formatting.</strong> Automatically highlighting values that meet rules, such as low stock or overspending.</li>
<li><strong>Charts.</strong> Turning numbers into visual bar, column or pie charts.</li>
<li><strong>Printing and sharing.</strong> Page setup, print areas and exporting to PDF.</li>
</ul>
<h3>How the module is structured</h3>
<p>You begin with the Excel screen and simple data entry. Next you learn formulas and functions with real money examples. Sorting, filtering and conditional formatting follow because they help you make sense of larger lists. Charts come after that, and the module ends with a practical budget project that ties everything together.</p>
<h3>Try It Yourself</h3>
<ol>
<li>Open Microsoft Excel and create a blank workbook.</li>
<li>In cell A1 type "Item", in B1 type "Quantity" and in C1 type "Price".</li>
<li>Enter three example rows, such as Sugar, 5, 25.00.</li>
<li>Save the workbook as <code>Excel_Practice_YourName.xlsx</code>.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>Spreadsheet</strong> - a grid of cells used to store and calculate data.</li>
<li><strong>Workbook</strong> - an Excel file that can contain many worksheets.</li>
<li><strong>Cell</strong> - one box in the grid, identified by its column letter and row number.</li>
<li><strong>Formula</strong> - a calculation beginning with an equals sign.</li>
<li><strong>Function</strong> - a built-in formula such as SUM or AVERAGE.</li>
</ul>
<h3>Summary</h3>
<p>Excel turns raw numbers into useful information. This module takes you from the first click to a working budget, giving you practical skills you can use in employment or your own enterprise.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://support.microsoft.com/en-us/excel">Microsoft Support - Excel</a></li>
<li><a href="https://edu.gcfglobal.org/en/excel2016/">GCFGlobal - Excel 2016 Tutorial</a></li>
</ul>
HTML,

            34 => <<<'HTML'
<h2>Interface and Cell Basics</h2>
<p>By the end of this lesson you will understand the Excel screen, know the difference between a workbook and a worksheet, enter and edit data in cells, and apply basic cell formatting.</p>
<h3>The Excel screen</h3>
<p>When Excel opens you see a blank workbook. The main parts are:</p>
<ul>
<li><strong>The Ribbon</strong> - tabs such as Home, Insert, Formulas and Data that hold the tools.</li>
<li><strong>The Worksheet grid</strong> - columns labelled A, B, C and rows numbered 1, 2, 3.</li>
<li><strong>The Name Box</strong> - shows the active cell reference, such as B5.</li>
<li><strong>The Formula Bar</strong> - shows what is inside the selected cell, whether text, a number or a formula.</li>
<li><strong>Sheet tabs</strong> - at the bottom; click to move between worksheets.</li>
</ul>
<h3>Workbooks and worksheets</h3>
<p>An Excel file is called a <strong>workbook</strong>. A workbook can contain many <strong>worksheets</strong>, each with its own grid. Think of a workbook as a file folder and worksheets as pages inside it. Right-click a sheet tab to rename, insert or delete a sheet. A sensible name, such as "Sales" or "Budget", keeps work organised.</p>
<h3>Entering and editing data</h3>
<p>Click a cell and type. Press Enter to move down, Tab to move right, or click the green tick on the formula bar to confirm. If you make a mistake, click the cell and retype, or press F2 to edit inside the cell. To clear a cell, select it and press Delete.</p>
<h3>Cell formatting</h3>
<p>On the Home tab you can change font, size, colour and alignment. The Number group is especially important: choose General, Number, Currency, Date or Percentage. For Zambian Kwacha, select Currency and look for the K or ZMW format, depending on your Excel version. Borders and Fill Colour help you create clear tables.</p>
<h3>Try It Yourself</h3>
<ol>
<li>Open Excel and create a blank workbook.</li>
<li>Rename Sheet1 to "Practice" by right-clicking the sheet tab.</li>
<li>In A1:C1 type "Item", "Quantity" and "Unit Price".</li>
<li>Enter three rows of data and format the Unit Price column as Currency.</li>
<li>Add borders to the table and save the file.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>Workbook</strong> - an Excel file.</li>
<li><strong>Worksheet</strong> - a single sheet or tab inside a workbook.</li>
<li><strong>Cell</strong> - the intersection of a column and a row.</li>
<li><strong>Cell reference</strong> - the address of a cell, such as D4.</li>
<li><strong>Currency format</strong> - a number format that shows a currency symbol.</li>
</ul>
<h3>Summary</h3>
<p>Excel stores data in workbooks, worksheets and cells. Enter data carefully, format numbers as currency or dates when needed, and rename sheets so your files stay organised.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/excel-video-training-9bc05390-e94c-46af-a5b3-d7c22f6990bb">Microsoft Support - Excel video training</a></li>
<li><a href="https://edu.gcfglobal.org/en/excel2016/getting-started-with-excel/1/">GCFGlobal - Getting Started with Excel</a></li>
</ul>
HTML,

            35 => <<<'HTML'
<h2>Essential Formulas and Functions</h2>
<p>By the end of this lesson you will write formulas using arithmetic operators and use the SUM, AVERAGE, MAX, MIN and COUNT functions, and you will understand the difference between relative and absolute cell references.</p>
<h3>Writing formulas</h3>
<p>Every Excel formula starts with an equals sign. You can use the four basic operators:</p>
<ul>
<li><strong>+</strong> addition</li>
<li><strong>-</strong> subtraction</li>
<li><strong>*</strong> multiplication</li>
<li><strong>/</strong> division</li>
</ul>
<p>For example, if B2 holds quantity and C2 holds unit price, the total in D2 is <code>=B2*C2</code>. When you change B2 or C2, D2 updates automatically.</p>
<h3>A worked example: Kalomo shop sales sheet</h3>
<p>Imagine a small shop in Kalomo selling mealie meal, sugar and cooking oil:</p>
<table>
<tr><th>Item</th><th>Quantity</th><th>Unit Price (ZMW)</th><th>Total (ZMW)</th></tr>
<tr><td>Mealie meal</td><td>10</td><td>75</td><td>=B2*C2</td></tr>
<tr><td>Sugar</td><td>5</td><td>25</td><td>=B3*C3</td></tr>
<tr><td>Cooking oil</td><td>4</td><td>90</td><td>=B4*C4</td></tr>
</table>
<p>Type the formula in D2, press Enter, then copy it down to D3 and D4. Excel adjusts the references automatically.</p>
<h3>Common functions</h3>
<ul>
<li><strong>=SUM(range)</strong> - adds all numbers in a range, e.g. <code>=SUM(D2:D4)</code>.</li>
<li><strong>=AVERAGE(range)</strong> - finds the mean of a range.</li>
<li><strong>=MAX(range)</strong> - returns the largest value.</li>
<li><strong>=MIN(range)</strong> - returns the smallest value.</li>
<li><strong>=COUNT(range)</strong> - counts how many cells contain numbers.</li>
</ul>
<h3>Relative vs absolute references</h3>
<p>A <strong>relative</strong> reference such as D2 changes when copied. An <strong>absolute</strong> reference such as <code>$D$2</code> stays fixed. Use dollar signs when a value must not move, such as a VAT rate stored in one cell. For example, <code>=B2*$F$1</code> copies the quantity but always uses the rate in F1.</p>
<h3>Try It Yourself</h3>
<ol>
<li>Recreate the Kalomo shop table with quantities, unit prices and totals.</li>
<li>Below the table, use SUM for the grand total, AVERAGE for the average unit price, MAX for the most expensive item and COUNT for the number of items.</li>
<li>Add a fixed discount rate in one cell and use an absolute reference to calculate discounted totals.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>Formula</strong> - a calculation in Excel starting with =.</li>
<li><strong>Function</strong> - a pre-built formula such as SUM.</li>
<li><strong>Range</strong> - a group of cells, such as A1:C5.</li>
<li><strong>Relative reference</strong> - a cell reference that adjusts when copied.</li>
<li><strong>Absolute reference</strong> - a cell reference fixed with dollar signs.</li>
</ul>
<h3>Summary</h3>
<p>Formulas and functions are the heart of Excel. Arithmetic operators do simple calculations, functions save time with common tasks, and absolute references let you lock important values such as rates or targets.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/overview-of-formulas-in-excel-ecfdc708-9162-49e8-b993-c311f47ca173">Microsoft Support - Overview of formulas</a></li>
<li><a href="https://edu.gcfglobal.org/en/excel2016/creating-simple-formulas/1/">GCFGlobal - Creating Simple Formulas</a></li>
</ul>
HTML,

            36 => <<<'HTML'
<h2>Data Analysis and Charts</h2>
<p>By the end of this lesson you will sort and filter lists, apply conditional formatting, and create a chart from spreadsheet data so you can turn raw numbers into clear visual information.</p>
<h3>Sorting data</h3>
<p>Sorting rearranges rows into order. Click anywhere inside a table, go to the Data tab, and choose Sort A to Z or Sort Z to A. For a sales list you might sort by Date or by Total to see the largest sale first. Make sure your table has headings and select the option to treat the first row as headers.</p>
<h3>Filtering data</h3>
<p>Filtering hides rows that do not match your criteria. On the Data tab click Filter. Drop-down arrows appear in the header row. Click an arrow to choose which values to show. This is useful for answering questions like "Show me all sales made in Choma" or "Show only items that are out of stock."</p>
<h3>Conditional formatting</h3>
<p>Conditional formatting automatically changes the look of cells based on their values. On the Home tab choose Conditional Formatting. Common rules include Highlight Cell Rules &gt; Greater Than or Less Than. For example, you could highlight any expense over ZMW 500 in red, or any stock count below 10 in yellow. The formatting updates if the values change.</p>
<h3>Creating a chart</h3>
<p>Select your data including headings, then go to Insert and choose a chart type. A Column chart is good for comparing values, a Line chart for trends over time, and a Pie chart for showing parts of a whole. After inserting, use the Chart Design and Format tabs to add titles, labels and colours. Keep charts simple: a clear title and labelled axes are more important than fancy effects.</p>
<h3>Try It Yourself</h3>
<ol>
<li>Create a table of monthly sales for five products.</li>
<li>Sort the table by total sales from highest to lowest.</li>
<li>Use a filter to show only rows where sales are greater than ZMW 1,000.</li>
<li>Apply conditional formatting to highlight the top three totals in green.</li>
<li>Insert a column chart of monthly sales and add a chart title.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>Sort</strong> - arranging data in a chosen order.</li>
<li><strong>Filter</strong> - showing only rows that match criteria.</li>
<li><strong>Conditional formatting</strong> - formatting that changes based on cell values.</li>
<li><strong>Chart</strong> - a visual representation of spreadsheet data.</li>
</ul>
<h3>Summary</h3>
<p>Sorting, filtering and conditional formatting help you find meaning in lists, while charts communicate that meaning to others. These are the tools that make spreadsheets persuasive and useful.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/sort-data-in-a-range-or-table-0d1e2c4b-2b7e-4b9b-9b9b-9b9b9b9b9b9b">Microsoft Support - Sort data</a></li>
<li><a href="https://edu.gcfglobal.org/en/excel2016/sorting-data/1/">GCFGlobal - Sorting Data</a></li>
</ul>
HTML,

            37 => <<<'HTML'
<h2>Excel Budget Project</h2>
<p>This is the assessed assignment for the Microsoft Excel module. You will build a working budget spreadsheet that uses formulas, functions, conditional formatting and a chart.</p>
<h3>Objective</h3>
<p>Create a monthly budget in Zambian Kwacha for either a household or a small business. The spreadsheet must show income, expenses, totals, averages, conditional formatting to flag overspending, and a chart that summarises the budget.</p>
<h3>Step-by-step what to produce</h3>
<ol>
<li>Create a new Excel workbook and save it as <code>Excel_Budget_YourName.xlsx</code>.</li>
<li>Rename Sheet1 to "Budget".</li>
<li>Create an "Income" section with at least three income sources, such as Salary, Business Sales and Rent. Enter realistic ZMW amounts.</li>
<li>Use the SUM function to calculate total income.</li>
<li>Create an "Expenses" section with at least five expense categories, such as Rent, Food, Transport, Airtime and School Fees. Enter realistic amounts.</li>
<li>Use the SUM function to calculate total expenses.</li>
<li>Calculate the balance using <code>=Total_Income - Total_Expenses</code>.</li>
<li>Use the AVERAGE function on the expense amounts.</li>
<li>Apply conditional formatting to highlight any expense that is greater than ZMW 500 in red, or any category that is over budget.</li>
<li>Create a column or pie chart comparing income sources or expense categories, and add a chart title.</li>
<li>Format currency values as ZMW/Kwacha and add borders to make the sheet neat.</li>
</ol>
<h3>What to submit</h3>
<p>Submit the <code>.xlsx</code> file through the learning system. Optionally export a PDF for printing. Ensure your name appears in the filename and in a header cell near the top of the sheet.</p>
<h3>Marking criteria (out of 100)</h3>
<table>
<tr><th>Criteria</th><th>Marks</th></tr>
<tr><td>Workbook created and saved with correct filename; sheet renamed</td><td>10</td></tr>
<tr><td>Income and expenses sections with realistic data</td><td>15</td></tr>
<tr><td>Correct use of SUM, AVERAGE and balance formula</td><td>25</td></tr>
<tr><td>Correct currency formatting and neat layout</td><td>10</td></tr>
<tr><td>Conditional formatting used meaningfully</td><td>15</td></tr>
<tr><td>Chart included with title and relevant data</td><td>15</td></tr>
<tr><td>Overall presentation, spelling and professionalism</td><td>10</td></tr>
<tr><td><strong>Total</strong></td><td><strong>100</strong></td></tr>
</table>
<h3>Important notes</h3>
<ul>
<li>Use formulas, not hand-typed totals. The instructor may change a number to test whether calculations update.</li>
<li>All figures should be realistic for a Zambian household or small business.</li>
<li>Ask your instructor if you are unsure which chart type to choose.</li>
</ul>
HTML,

            // ----- POWERPOINT AND PUBLISHER MODULE -----
            38 => <<<'HTML'
<h2>PowerPoint and Publisher Notes</h2>
<p>By the end of this module you will be able to create clear, attractive presentations in Microsoft PowerPoint and produce printed materials such as flyers, programmes and booklets in Microsoft Publisher - skills useful for meetings, events, classrooms and marketing.</p>
<h3>What is PowerPoint for?</h3>
<p>PowerPoint is a presentation program. It lets you build a sequence of slides containing text, images, charts and video, then show them on a screen or projector. A good presentation supports what the speaker says without replacing them. It is used in schools, churches, businesses, workshops and training sessions.</p>
<h3>What is Publisher for?</h3>
<p>Microsoft Publisher is a desktop publishing program. While Word is best for long text documents, Publisher is designed for pages with a strong visual layout, such as flyers, posters, newsletters, certificates and event programmes. You drag text boxes, pictures and shapes into position exactly where you want them.</p>
<h3>Key skills covered in this module</h3>
<ul>
<li><strong>PowerPoint basics.</strong> Creating a presentation, adding and deleting slides, applying themes and layouts, formatting text and objects.</li>
<li><strong>Visual elements.</strong> Inserting pictures, shapes, SmartArt and charts to support your message.</li>
<li><strong>Design and flow.</strong> Using consistent colours, fonts and alignment; limiting text per slide; using headings and bullet points.</li>
<li><strong>Transitions and animations.</strong> Adding movement between slides and to objects without distracting the audience.</li>
<li><strong>Presenting.</strong> Running a slide show, using presenter view, printing handouts, and practical speaking tips.</li>
<li><strong>Publisher basics.</strong> Opening templates, setting up a publication, adding and formatting text boxes, inserting pictures and shapes.</li>
<li><strong>Design principles.</strong> Alignment, contrast, repetition and hierarchy - the rules that make any page look professional.</li>
<li><strong>Preparing for print.</strong> Page setup, margins, bleed, and exporting to PDF.</li>
</ul>
<h3>How the module is structured</h3>
<p>You start with PowerPoint essentials, then move through design, transitions and presenting. Publisher follows, using the same design principles but applied to printed pages. The two programs share ideas about layout, so skills learned in one help the other.</p>
<h3>Try It Yourself</h3>
<ol>
<li>Open PowerPoint and create a blank presentation.</li>
<li>On the title slide, type "My First Presentation" and your name.</li>
<li>Add a second slide with a title and a bulleted list of three topics you would like to present.</li>
<li>Save the file as <code>PowerPoint_Practice_YourName.pptx</code>.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>Presentation</strong> - a file made of slides shown to an audience.</li>
<li><strong>Slide</strong> - one screen or page in a presentation.</li>
<li><strong>Theme</strong> - a pre-designed set of colours, fonts and effects.</li>
<li><strong>Desktop publishing</strong> - creating printed or digital documents with careful visual layout.</li>
<li><strong>Template</strong> - a pre-made design that you customise.</li>
</ul>
<h3>Summary</h3>
<p>PowerPoint helps you communicate ideas to a group; Publisher helps you produce polished printed pieces. Both rely on the same design fundamentals, and both are valuable in education, business and community work.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://support.microsoft.com/en-us/powerpoint">Microsoft Support - PowerPoint</a></li>
<li><a href="https://support.microsoft.com/en-us/office/publisher-help-and-learning-0d81713f-1b6e-4c2e-9b0e-3e6a3e8e6a3e">Microsoft Support - Publisher help</a></li>
<li><a href="https://edu.gcfglobal.org/en/powerpoint2016/">GCFGlobal - PowerPoint 2016 Tutorial</a></li>
</ul>
HTML,

            39 => <<<'HTML'
<h2>PowerPoint Essentials</h2>
<p>By the end of this lesson you will create a presentation, add and delete slides, apply themes and layouts, and format text and objects so your slides look clean and professional.</p>
<h3>Creating a presentation</h3>
<p>Open PowerPoint and choose Blank Presentation. The first slide is the Title slide, which has placeholders for a title and subtitle. Click inside a placeholder and type. To add a new slide, click New Slide on the Home tab or press <code>Ctrl + M</code>.</p>
<h3>Slide layouts</h3>
<p>Every slide uses a layout, such as Title Slide, Title and Content, Two Content, or Blank. Choose the layout that matches your content. "Title and Content" is the most common and gives you a heading plus a large area for bullets, a picture or a chart. You can change the layout at any time from the Home tab.</p>
<h3>Themes and design</h3>
<p>A theme applies consistent colours, fonts and background styles to every slide. Go to the Design tab and browse the themes. Pick one that suits your topic. Avoid overly bright or busy themes; simple designs keep the audience focused on your message. You can also change the colour variant from the Variants group on the Design tab.</p>
<h3>Formatting text and objects</h3>
<p>Select text and use the Home tab to change font, size, colour, bold, italic and alignment. For objects such as shapes or pictures, click the object to reveal its Format tab. Use the Format tab to change fill colour, outline, size and position. Remember that less is more: one or two fonts per presentation and large text that can be read from the back of the room.</p>
<h3>Try It Yourself</h3>
<ol>
<li>Create a presentation with four slides: a title slide and three content slides.</li>
<li>Apply a professional theme from the Design tab.</li>
<li>On one slide, add a bulleted list of three benefits of learning computer skills.</li>
<li>Format the title of each slide in bold and a larger font size.</li>
<li>Save the presentation as <code>PowerPoint_Essentials_YourName.pptx</code>.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>Slide layout</strong> - the arrangement of placeholders on a slide.</li>
<li><strong>Theme</strong> - a coordinated set of colours, fonts and effects.</li>
<li><strong>Placeholder</strong> - a box on a slide that holds text or objects.</li>
<li><strong>Variants</strong> - different colour options within the same theme.</li>
</ul>
<h3>Summary</h3>
<p>A good PowerPoint presentation starts with the right layout and theme. Keep formatting consistent, text readable and slides uncluttered, and your audience will follow your message easily.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/basic-tasks-for-creating-a-powerpoint-presentation-0785b45b-4a08-4d82-9b52-9a23b39a0cb1">Microsoft Support - Basic PowerPoint tasks</a></li>
<li><a href="https://edu.gcfglobal.org/en/powerpoint2016/getting-started-with-powerpoint/1/">GCFGlobal - Getting Started with PowerPoint</a></li>
</ul>
HTML,

            40 => <<<'HTML'
<h2>Transitions and Presenting</h2>
<p>By the end of this lesson you will add transitions and animations to a presentation, run a slide show confidently, print handouts, and apply practical tips for speaking in front of an audience.</p>
<h3>Slide transitions</h3>
<p>A transition is the effect shown when moving from one slide to the next. Select a slide, go to the Transitions tab, and choose a subtle effect such as Fade or Push. Click Apply To All if you want every slide to use the same transition. Avoid fast or noisy effects; they distract the audience. A simple transition used consistently looks far more professional.</p>
<h3>Animations</h3>
<p>Animations control how objects appear on a slide. Select an object, go to the Animations tab, and choose Appear, Fade or Wipe. Use animations to reveal bullet points one by one so the audience listens rather than reading ahead. Do not animate every object on every slide; that quickly becomes tiring.</p>
<h3>Running the slide show</h3>
<p>Press F5 to start the presentation from the first slide, or Shift + F5 to start from the current slide. Click the mouse, press the space bar or use the arrow keys to move forward. Press Esc to exit. If you have two screens, PowerPoint shows Presenter View with the current slide, next slide, notes and a timer.</p>
<h3>Practical presenting tips</h3>
<ul>
<li><strong>Look at the audience, not the screen.</strong> Your slides support you; they are not the speaker.</li>
<li><strong>Speak slowly and clearly.</strong> Pause between points.</li>
<li><strong>Keep slides brief.</strong> Aim for a few bullets, not whole paragraphs.</li>
<li><strong>Practise aloud.</strong> Time yourself so you do not rush or overrun.</li>
<li><strong>Have a backup plan.</strong> Save the file on a USB drive and as a PDF in case the computer fails.</li>
</ul>
<h3>Printing handouts</h3>
<p>Go to File &gt; Print and under Settings choose Handouts. You can print 2, 3, 4 or 6 slides per page. Three slides per page leaves lines for notes and is popular for training sessions.</p>
<h3>Try It Yourself</h3>
<ol>
<li>Open your practice presentation and add a Fade transition to every slide.</li>
<li>Apply a simple animation to the bullet points on one slide.</li>
<li>Run the slide show from the beginning and practise moving through the slides.</li>
<li>Export or print the presentation as a handout with three slides per page.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>Transition</strong> - an effect between slides.</li>
<li><strong>Animation</strong> - an effect applied to objects on a slide.</li>
<li><strong>Presenter View</strong> - a speaker-only view with notes and a timer.</li>
<li><strong>Handouts</strong> - printed pages of the slides for the audience.</li>
</ul>
<h3>Summary</h3>
<p>Subtle transitions and animations add polish, while confident delivery and clear slides keep the audience engaged. Practise running the show and printing handouts so you are ready for any presentation situation.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/add-change-or-remove-transitions-between-slides-3de92c65-aa4e-4fd8-b7e0-c4a69758ee7c">Microsoft Support - Slide transitions</a></li>
<li><a href="https://edu.gcfglobal.org/en/powerpoint2016/animating-text-and-objects/1/">GCFGlobal - Animating Text and Objects</a></li>
</ul>
HTML,

            41 => <<<'HTML'
<h2>Publisher and Graphic Design</h2>
<p>By the end of this lesson you will use Microsoft Publisher to create a flyer, programme or booklet, apply basic design principles, and prepare your publication for print or PDF.</p>
<h3>Starting a publication</h3>
<p>Open Publisher and choose a template or a blank publication. Common sizes include A4, A5 and A6 for flyers, and Letter for booklets. Publisher opens in a workspace where you drag text boxes, pictures and shapes onto the page. Unlike Word, Publisher is designed for pages where exact position matters.</p>
<h3>Working with text boxes</h3>
<p>Click Insert &gt; Draw Text Box and drag to create a box. Type or paste your text inside. You can resize, rotate and move text boxes freely. Use the Format tab to change font, colour, alignment and spacing. For a church programme or funeral programme, create separate text boxes for the title, order of service, hymns, tributes and contact details.</p>
<h3>Inserting pictures and shapes</h3>
<p>Use Insert &gt; Pictures to add images from your computer. Use Insert &gt; Shapes to add rectangles, circles, arrows and lines. The Picture Tools and Drawing Tools tabs appear when an object is selected. Crop pictures, adjust brightness, add borders and arrange layers using Send to Back or Bring to Front.</p>
<h3>Design principles</h3>
<p>Good design is not about using every colour and font available. Keep these four principles in mind:</p>
<ul>
<li><strong>Alignment.</strong> Every element should line up with something else. Avoid placing things randomly.</li>
<li><strong>Contrast.</strong> Make headings stand out from body text through size, colour or weight.</li>
<li><strong>Repetition.</strong> Use the same fonts, colours and shapes throughout for consistency.</li>
<li><strong>Hierarchy.</strong> Guide the reader's eye from the most important element to the least.</li>
</ul>
<h3>Preparing for print</h3>
<p>Before printing, check page setup, margins and bleed if your design goes to the edge of the paper. Use File &gt; Export &gt; Create PDF/XPS Document to produce a PDF that looks the same on any computer. For a funeral programme or church booklet, print a test copy first to check alignment and colours.</p>
<h3>Try It Yourself</h3>
<ol>
<li>Open Publisher and create an A5 flyer for a real or imaginary local event.</li>
<li>Add a title text box, a body text box, one picture and one shape.</li>
<li>Apply alignment, contrast and repetition so the flyer looks unified.</li>
<li>Export the finished design as a PDF.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>Publication</strong> - a document created in Publisher, such as a flyer or booklet.</li>
<li><strong>Text box</strong> - a movable container for text.</li>
<li><strong>Bleed</strong> - printing that extends slightly past the edge of the page.</li>
<li><strong>Hierarchy</strong> - visual ranking that shows the reader what to read first.</li>
</ul>
<h3>Summary</h3>
<p>Publisher gives you precise control over page layout for flyers, programmes and booklets. Combine text boxes, pictures and shapes with alignment, contrast, repetition and hierarchy to produce professional print-ready publications.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/publisher-help-and-learning-0d81713f-1b6e-4c2e-9b0e-3e6a3e8e6a3e">Microsoft Support - Publisher help</a></li>
<li><a href="https://edu.gcfglobal.org/en/publisher2016/">GCFGlobal - Publisher 2016 Tutorial</a></li>
</ul>
HTML,

            // ===================== CYBERSECURITY FUNDAMENTALS (course 35) =====================
            107 => <<<'HTML'
<h2>Digital Forensics Fundamentals</h2>
<p>By the end of this lesson you will understand what digital forensics is, why evidence must be handled carefully, the basic steps of an investigation, and why preserving evidence matters in both criminal cases and workplace incidents.</p>
<h3>What is digital forensics?</h3>
<p>Digital forensics is the process of finding, preserving, analysing and presenting digital evidence. That evidence can be on computers, phones, servers, USB drives, cameras, networks or cloud accounts. In Zambia, digital evidence is increasingly used in fraud cases, cybercrime investigations, company disputes and even civil matters such as leaked documents or stolen customer records.</p>
<p>Forensics is not only for the police. A bank may investigate suspicious transactions, a school may review who accessed exam papers, and a business may trace how a laptop was misused. Anyone handling evidence must follow careful procedures so the evidence can be trusted later.</p>
<h3>Why evidence matters</h3>
<p>Evidence is only useful if a court, manager or auditor believes it is genuine. That means showing that nobody changed it after it was collected. Investigators take hashes, photographs, detailed notes and chain-of-custody forms to prove that the evidence stayed exactly as it was found.</p>
<h3>Basic steps in a digital investigation</h3>
<ol>
<li><strong>Identification.</strong> Decide what devices, accounts or files might contain evidence.</li>
<li><strong>Preservation.</strong> Make a forensic copy and protect the original. Do not turn a device on and off repeatedly, and do not browse its files casually.</li>
<li><strong>Collection.</strong> Securely gather the copied data with proper labels and documentation.</li>
<li><strong>Analysis.</strong> Examine the data using approved tools and methods, looking for files, logs, timestamps and deleted items.</li>
<li><strong>Reporting.</strong> Write a clear report explaining what was found, how it was found, and what it means.</li>
</ol>
<h3>The chain of custody</h3>
<p>Chain of custody is a record of who handled evidence, when and why. Every person who receives the evidence signs for it. If the chain is broken, a lawyer can argue that the evidence might have been tampered with. Good notes are as important as technical skill.</p>
<h3>Tools at a high level</h3>
<p>Professional investigators use tools such as Autopsy, FTK Imager, Caine Linux and Sleuth Kit to create disk images and search files. These tools are specialised and require training. Beginners should focus first on understanding principles: do not touch evidence unless authorised, document everything, and report honestly.</p>
<h3>Try It Yourself</h3>
<ol>
<li>Imagine a USB drive is found after a suspected data leak in an office. Write down five things you should do before plugging it into your own computer.</li>
<li>List three pieces of information that a chain-of-custody form should record.</li>
<li>Research one free forensics tool and write two sentences about what it does.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>Digital forensics</strong> - the recovery and investigation of material found in digital devices.</li>
<li><strong>Evidence</strong> - information that can be used to prove or disprove something.</li>
<li><strong>Chain of custody</strong> - a documented record of evidence handling.</li>
<li><strong>Hash</strong> - a digital fingerprint used to verify that a file has not changed.</li>
<li><strong>Forensic image</strong> - an exact bit-by-bit copy of a storage device.</li>
</ul>
<h3>Summary</h3>
<p>Digital forensics is about more than technology - it is about trust. Identify, preserve, collect, analyse and report evidence carefully, maintain the chain of custody, and the results can stand up to scrutiny in any setting.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://owasp.org/www-project-forensics/">OWASP - Forensics Project</a></li>
<li><a href="https://www.autopsy.com/support/">Autopsy Digital Forensics - Documentation</a></li>
<li><a href="https://support.google.com/policies/answer/9116375">Google Safety Center - Protecting data</a></li>
</ul>
HTML,

            119 => <<<'HTML'
<h2>Career Preparation and Next Steps</h2>
<p>By the end of this lesson you will know the main entry-level paths into cybersecurity, the certifications that help you get started, how to build a portfolio from Zambia, and practical ways to continue learning and find work.</p>
<h3>Why cybersecurity as a career?</h3>
<p>Cybersecurity protects people, organisations and countries from digital attacks. As Zambia grows its digital economy - mobile money, online banking, e-government, e-commerce - the need for security-aware workers increases. You do not need to be a genius programmer to start; you need curiosity, discipline and a willingness to keep learning.</p>
<h3>Entry-level roles</h3>
<p>These roles are realistic first steps:</p>
<ul>
<li><strong>Security Operations Centre (SOC) Analyst</strong> - monitors alerts and investigates suspicious activity.</li>
<li><strong>IT Support / System Administrator</strong> - keeps systems running securely and often handles backups, updates and user access.</li>
<li><strong>Network Technician</strong> - installs and maintains networks, applying security configurations.</li>
<li><strong>Compliance / Risk Assistant</strong> - helps organisations meet security standards and document controls.</li>
<li><strong>Freelance Penetration Tester or Consultant</strong> - after building skills and trust, tests websites and networks for small businesses.</li>
</ul>
<h3>Certifications to aim for</h3>
<p>Certifications prove your knowledge to employers. Beginner-friendly options include:</p>
<ul>
<li><strong>CompTIA Security+</strong> - a widely recognised foundation in security concepts, tools and procedures.</li>
<li><strong>Cisco Certified Support Technician (CCST) Cybersecurity</strong> - entry-level Cisco credential focused on security basics.</li>
<li><strong>CompTIA A+ and Network+</strong> - useful foundations if you want to move into IT support or networking first.</li>
<li><strong>Google Cybersecurity Certificate</strong> - an online beginner course covering SIEM tools, Linux, Python and incident response.</li>
</ul>
<h3>Building a portfolio and home lab</h3>
<p>Employers want evidence of skill, not just certificates. Start a home lab with free tools: VirtualBox or VMware Player, Kali Linux, Wireshark, Snort or Suricata, and a vulnerable practice machine such as Metasploitable. Document every exercise you complete in a blog, GitHub repository or simple PDF portfolio. Even basic write-ups show that you can explain your thinking.</p>
<h3>Finding work from Zambia</h3>
<p>Remote and freelance opportunities exist on platforms such as Upwork, LinkedIn and international security consultancies, but they require proof of skill and good communication. Locally, banks, telecom companies, government agencies, NGOs and schools all need people who understand security. Attend tech meet-ups, join online communities such as OWASP and follow Zambian ICT groups to learn about openings.</p>
<h3>Continuing education</h3>
<p>Security changes constantly. Make a habit of reading reputable sources, listening to security podcasts, and practising on platforms such as TryHackMe, Hack The Box and OverTheWire. Pick one specialisation after the basics - cloud security, penetration testing, incident response or governance - and deepen it over time.</p>
<h3>Try It Yourself</h3>
<ol>
<li>Write a one-page career plan: your target role in three years, the certifications you will pursue, and the home lab tools you will set up.</li>
<li>Create a LinkedIn profile or update your existing one, listing your skills and any completed courses.</li>
<li>Join one online security community and read five posts this week.</li>
</ol>
<h3>Key Terms</h3>
<ul>
<li><strong>SOC Analyst</strong> - a security professional who monitors systems for threats.</li>
<li><strong>Certification</strong> - a credential that shows you have passed an exam in a subject.</li>
<li><strong>Home lab</strong> - a personal practice environment for learning technology.</li>
<li><strong>Freelancing</strong> - working for clients independently rather than as a permanent employee.</li>
</ul>
<h3>Summary</h3>
<p>Cybersecurity offers real opportunities for learners in Zambia who combine certifications, hands-on practice and community engagement. Start with entry-level roles, build a portfolio, keep learning, and the path opens up over time.</p>
<h3>Free Resources</h3>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/paths/security-fundamentals/">Microsoft Learn - Security Fundamentals</a></li>
<li><a href="https://owasp.org/www-project-top-ten/">OWASP - Top 10 Web Application Security Risks</a></li>
<li><a href="https://support.google.com/a/answer/9223656">Google Workspace Admin Help - Security basics</a></li>
</ul>
HTML,
        ];
    }
}
