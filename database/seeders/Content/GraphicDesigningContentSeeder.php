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

class GraphicDesigningContentSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Graphic Designing')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Graphic Designing" not found. Aborting.');
            return;
        }

        if ($course->modules()->count() > 0) {
            $this->command->info('Graphic Designing course already has content. Skipping.');
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
    }

    /* --------------------------------------------------------------------- */
    /*  Module 1 – Foundations of Design                                     */
    /* --------------------------------------------------------------------- */
    private function seedModule1(Course $course): void
    {
        $module = Module::create([
            'course_id'       => $course->id,
            'title'           => 'Foundations of Design',
            'description'     => 'Learn the core principles that make every design effective, from contrast and alignment to hierarchy and colour.',
            'display_order'   => 1,
            'duration_minutes'=> 0,
            'is_published'    => true,
        ]);

        $lessons = [];

        // Lesson 1.1
        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => 'The Building Blocks of Good Design',
            'content'          => $this->lesson1_1(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 60,
            'display_order'    => 1,
            'is_preview'       => true,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        // Lesson 1.2
        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => 'Colour Theory for the Zambian Market',
            'content'          => $this->lesson1_2(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 60,
            'display_order'    => 2,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        // Lesson 1.3
        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => 'Typography That Speaks',
            'content'          => $this->lesson1_3(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 60,
            'display_order'    => 3,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        // Lesson 1.4 – Quiz
        $quizLesson = Lesson::create([
            'module_id'        => $module->id,
            'title'            => 'Module 1 Quiz: Design Foundations',
            'content'          => '<p>Complete this quiz to test your understanding of design principles, colour theory, and typography.</p>',
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
<p>By the end of this lesson, you will be able to look at any poster, flyer, or social media graphic and explain why it works—or why it does not. You will understand the four basic principles of design and know how to apply them to your own projects, whether you are designing a church programme, a shop promotion, or a WhatsApp advert for your side business.</p>

<h2>The Four Principles of Good Design</h2>
<p>Every professional designer follows four simple rules, often called the <strong>CRAP</strong> principles: <strong>Contrast</strong>, <strong>Repetition</strong>, <strong>Alignment</strong>, and <strong>Proximity</strong>. These were made famous by designer Robin Williams, and they apply just as much to a flyer for a Kalomo market stall as they do to a billboard in Lusaka.</p>

<h3>1. Contrast</h3>
<p>Contrast means making elements different so they stand out. If your heading and body text are both dark blue and the same size, nobody knows what to read first. Contrast can be created with colour, size, weight (bold vs normal), or style.</p>
<p><strong>Example:</strong> Imagine a funeral programme where the name of the deceased is written in small, light grey letters at the top. It feels disrespectful and hard to find. Now imagine the name in large, bold black type with plenty of space around it. The second version uses contrast to show importance and dignity.</p>

<h3>2. Repetition</h3>
<p>Repetition means using the same visual elements throughout a design. If your headings are in one font on page one, keep them in that font on every page. If your logo uses gold and green, use those colours on your business card, flyers, and Facebook cover photo.</p>
<p><strong>Example:</strong> A chicken-rearing business called "Kalomo Poultry" uses a red rooster icon on its flyer but switches to a blue chicken on its price list. Customers might think these are two different businesses. Repetition builds trust and recognition.</p>

<h3>3. Alignment</h3>
<p>Alignment means nothing in your design should look randomly placed. Every item should have a visual connection to something else. Left-aligning all text creates a clean edge down the page. Centre alignment works for formal invitations but can look messy if overused.</p>
<p><strong>Example:</strong> A school event poster has the date in the top-left corner, the time in the centre, and the venue in the bottom-right. Your eye jumps around. Aligning all three pieces of information to the left creates order and makes the poster easier to read during load-shedding by candlelight.</p>

<h3>4. Proximity</h3>
<p>Proximity means grouping related items together. If the address, phone number, and Airtel Money payment details are scattered across the flyer, people will miss them. Put them in one block so the reader sees them as a single unit.</p>

<h2>Worked Example: Fixing a Bad Flyer</h2>
<p>Let us look at a real-world example. A shop in Soweto Market wants to advertise a promotion: "Maize Meal K120, Cooking Oil K85, Soap K15." The owner types this in black 12-point text on a white background, adds a clipart basket, and prints 50 copies. Nothing stands out.</p>
<p>Here is how we fix it using CRAP:</p>
<ol>
<li><strong>Contrast:</strong> Make "Special Prices" huge and red. Make the product names bold and the prices even larger.</li>
<li><strong>Repetition:</strong> Use the same red for every price and the same bold font for every product name.</li>
<li><strong>Alignment:</strong> Left-align every line so the left edge is clean.</li>
<li><strong>Proximity:</strong> Group each product with its price, and put the shop name, phone number, and MTN MoMo number together in a box at the bottom.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Find three printed flyers or posters in your community—perhaps from church, a shop, or a school.</li>
<li>Write down which of the four principles each one uses well and which are missing.</li>
<li>Using pen and paper, sketch a quick improved version of the weakest flyer.</li>
<li>Snap a photo of your sketch and save it for your portfolio.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Contrast:</strong> The difference between elements that makes them stand out from one another.</li>
<li><strong>Repetition:</strong> Reusing the same colours, fonts, or shapes to create consistency.</li>
<li><strong>Alignment:</strong> Placing text and images along common edges or centres to create visual order.</li>
<li><strong>Proximity:</strong> Grouping related items close together so they are seen as one unit.</li>
<li><strong>Hierarchy:</strong> Arranging elements to show which is most important and which is least.</li>
</ul>

<h2>Summary</h2>
<p>Good design is not about expensive software or artistic talent. It is about structure. When you use contrast, repetition, alignment, and proximity, your posters, flyers, and social media graphics become clearer, more professional, and more persuasive. These four principles are the foundation of everything you will design in this course.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool">Canva Design School</a> — free articles and tutorials on design basics.</li>
<li><a href="https://www.w3schools.com">W3Schools</a> — simple explanations of visual design concepts.</li>
<li><a href="https://www.gimp.org/tutorials">GIMP Tutorials</a> — official guides for the free image editor you will use later in this course.</li>
</ul>
HTML;
    }

    private function lesson1_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will understand how colours work together and how to choose palettes that match the message you want to send. You will be able to select colours that feel right for a Zambian audience, whether you are designing for a funeral programme, a mobile money agent poster, or a school fundraiser.</p>

<h2>Why Colour Matters</h2>
<p>Colour is often the first thing people notice in a design. Before they read a single word, their brain has already formed an opinion based on the colours you chose. A bright red and yellow poster feels urgent and energetic. A black and white layout feels serious and formal. Choosing the wrong colours can confuse your audience or even offend them.</p>

<h2>The Colour Wheel</h2>
<p>The colour wheel is a circle that shows the relationship between colours. It is the most useful tool a designer owns.</p>
<ul>
<li><strong>Primary colours:</strong> Red, blue, and yellow. These cannot be made by mixing other colours.</li>
<li><strong>Secondary colours:</strong> Green, orange, and purple. These are made by mixing two primary colours.</li>
<li><strong>Tertiary colours:</strong> Yellow-green, red-orange, and so on. These sit between primary and secondary colours.</li>
</ul>

<h2>Colour Harmony Rules</h2>
<p>Designers use three main rules to create pleasant colour combinations:</p>
<ol>
<li><strong>Complementary:</strong> Two colours opposite each other on the wheel, such as blue and orange. These create high contrast and grab attention. Use them when you want people to act quickly—such as a limited-time shop promotion.</li>
<li><strong>Analogous:</strong> Three colours next to each other, such as yellow, yellow-orange, and orange. These feel calm and unified. Good for agricultural brochures or nature-themed designs.</li>
<li><strong>Monochromatic:</strong> Different shades and tints of a single colour, such as dark green, medium green, and light green. This looks elegant and professional. Excellent for formal documents and corporate branding.</li>
</ol>

<h2>Colour Psychology in Zambia</h2>
<p>Colours carry cultural meaning. In Zambia, some associations are strong:</p>
<ul>
<li><strong>Green:</strong> Agriculture, nature, growth, and the national flag. Popular for farming cooperatives and environmental campaigns.</li>
<li><strong>Red:</strong> Danger, urgency, love, or HIV/AIDS awareness. Use carefully—it demands attention.</li>
<li><strong>Gold / Yellow:</strong> Wealth, prestige, and celebration. Common in wedding invitations and graduation programmes.</li>
<li><strong>Black:</strong> Mourning and formality. Standard for funeral programmes but can feel heavy in adverts.</li>
<li><strong>White:</strong> Purity and peace. Often paired with black for funerals, or with bright colours for clean, modern looks.</li>
</ul>

<h2>Worked Example: Choosing Colours for a Chicken-Rearing Business</h2>
<p>A young entrepreneur in Kalomo starts a side business selling broiler chickens and eggs. She wants a simple logo and flyer. Let us pick colours that fit.</p>
<p>Her business is about health, freshness, and local farming. Green suggests nature and growth. A warm golden yellow suggests healthy chicks and sunshine. A dark brown adds earthiness and grounds the design. We use <strong>analogous green and yellow</strong> for the main feel, with brown for text. This feels local, trustworthy, and appetising—not like a cold corporate chain.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open the Canva app on your phone or visit canva.com on a computer.</li>
<li>Create a new design—any size.</li>
<li>Choose a complementary colour pair and fill two shapes with those colours.</li>
<li>Now choose an analogous trio and do the same.</li>
<li>Finally, create a monochromatic scheme using three shades of blue.</li>
<li>Take a screenshot and label each scheme.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Hue:</strong> The pure name of a colour, such as red or blue.</li>
<li><strong>Saturation:</strong> How intense or dull a colour is. A fully saturated red is vivid; a desaturated red looks greyish.</li>
<li><strong>Complementary colours:</strong> Two colours opposite each other on the colour wheel.</li>
<li><strong>Monochromatic:</strong> A colour scheme based on a single hue with different lightness levels.</li>
<li><strong>Colour psychology:</strong> The study of how colours affect human emotions and behaviour.</li>
</ul>

<h2>Summary</h2>
<p>Colour is a powerful design tool that goes far beyond personal taste. By understanding the colour wheel, harmony rules, and local cultural associations, you can choose palettes that communicate the right message to a Zambian audience. Whether you are designing for a church crusade or a market stall, the colours you pick will shape how people feel before they read a single word.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool">Canva Design School</a> — articles on colour theory and palette creation.</li>
<li><a href="https://www.khanacademy.org">Khan Academy</a> — free lessons on art and colour fundamentals.</li>
</ul>
HTML;
    }

    private function lesson1_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will understand how to choose and combine fonts so your designs are readable, professional, and appropriate for their audience. You will know the difference between serif and sans-serif typefaces, how to create hierarchy with text size and weight, and why some fonts work for funeral programmes while others suit a Zambezi FM radio advert.</p>

<h2>What Is Typography?</h2>
<p>Typography is the art of arranging letters and text so they are readable and visually appealing. You use typography every time you type a WhatsApp message, write a school report, or design a poster. As a graphic designer, your job is to choose typefaces that match the message and organise them so the reader knows where to look first.</p>

<h2>Serif vs Sans-Serif</h2>
<p>The two largest families of typefaces are serif and sans-serif.</p>
<ul>
<li><strong>Serif fonts</strong> have small decorative strokes at the ends of letters. Times New Roman and Georgia are examples. They feel traditional, formal, and trustworthy. A law firm, a bank, or a funeral programme often uses serif fonts because they convey seriousness.</li>
<li><strong>Sans-serif fonts</strong> do not have those strokes. Arial, Helvetica, and Open Sans are examples. They feel modern, clean, and friendly. A tech start-up, a mobile money agent, or a school event poster often uses sans-serif because it looks approachable.</li>
</ul>

<h2>Creating Hierarchy with Type</h2>
<p>Hierarchy tells the reader what to read first, second, and third. You create hierarchy using three tools:</p>
<ol>
<li><strong>Size:</strong> The most important text should be the largest. A poster headline might be 48 points; the body text might be 12 points.</li>
<li><strong>Weight:</strong> Bold text stands out more than regular text. Use bold for headings and key phrases.</li>
<li><strong>Spacing:</strong> Adding space above a heading separates it from the previous section. Tightening the space between lines in a paragraph keeps related text together.</li>
</ol>

<h2>Font Pairing</h2>
<p>Most designs use two fonts: one for headings and one for body text. The rule is simple: pair a serif with a sans-serif for contrast, or use two fonts from the same family for harmony. Never use more than three fonts in one design—it looks unprofessional.</p>
<p><strong>Safe pairings for beginners:</strong></p>
<ul>
<li>Merriweather (serif heading) + Open Sans (sans-serif body)</li>
<li>Montserrat (sans-serif heading) + Lora (serif body)</li>
<li>Roboto (sans-serif for both, using bold for headings)</li>
</ul>

<h2>Worked Example: A Poster for Zambezi FM</h2>
<p>A local radio station wants a poster advertising a community health talk show. The poster will be nailed to trees and shop walls in Kalomo, so it must be readable from three metres away.</p>
<ol>
<li><strong>Headline:</strong> "Health Matters—Live on Zambezi FM" in bold Montserrat, 60 points, all caps. Sans-serif is bold and modern.</li>
<li><strong>Subheading:</strong> "Every Wednesday at 19:00" in Montserrat, 30 points, regular weight.</li>
<li><strong>Body:</strong> Details about the topic and how to send questions via SMS or WhatsApp, in Open Sans, 18 points.</li>
<li><strong>Hierarchy check:</strong> The reader sees the station name first, the time second, and the details third. The sans-serif fonts feel energetic and accessible.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Open a word processor or Canva.</li>
<li>Type the sentence: "Kalomo Community School invites you to the Annual Prize-Giving Day."</li>
<li>Format it in five different fonts: one serif, one sans-serif, one decorative, one script, and one bold display font.</li>
<li>Print or screenshot the result and ask a friend which version feels most appropriate for a school event.</li>
<li>Write one sentence explaining why the winning font works best.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Serif:</strong> A typeface with small decorative strokes at the ends of letters.</li>
<li><strong>Sans-serif:</strong> A typeface without decorative strokes; cleaner and more modern.</li>
<li><strong>Kerning:</strong> Adjusting the space between two specific letters.</li>
<li><strong>Leading:</strong> The vertical space between lines of text.</li>
<li><strong>Typeface:</strong> A family of fonts that share the same design; for example, Times New Roman is a typeface, and Times New Roman Bold is a font within it.</li>
</ul>

<h2>Summary</h2>
<p>Typography is not just about picking a pretty font. It is about guiding the reader, setting the right mood, and making information easy to digest. By understanding serif versus sans-serif, creating clear hierarchy, and pairing fonts wisely, you can make everything from a funeral programme to a radio advert look polished and professional.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool">Canva Design School</a> — beginner guides on font pairing and typography rules.</li>
<li><a href="https://www.w3schools.com">W3Schools</a> — basic explanations of text styling and web typography.</li>
<li><a href="https://www.gimp.org/tutorials">GIMP Tutorials</a> — working with text in images.</li>
</ul>
HTML;
    }

    private function createModule1Quiz(Course $course): void
    {
        $quiz = Quiz::create([
            'course_id'           => $course->id,
            'lesson_id'           => null,
            'title'               => 'Module 1 Quiz: Design Foundations',
            'description'         => 'Test your knowledge of the CRAP principles, colour theory, and typography.',
            'quiz_type'           => 'Graded',
            'time_limit_minutes'  => 20,
            'max_attempts'        => 3,
            'passing_score'       => 60.00,
            'show_correct_answers'=> true,
            'is_published'        => true,
        ]);

        $q1 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'Which design principle means making elements different so they stand out?','points'=>2,'explanation'=>'Contrast creates visual difference between elements so the important ones are noticed first.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q1->question_id,'option_text'=>'Repetition','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q1->question_id,'option_text'=>'Contrast','is_correct'=>true,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q1->question_id,'option_text'=>'Alignment','is_correct'=>false,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q1->question_id,'option_text'=>'Proximity','is_correct'=>false,'display_order'=>4]);

        $q2 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'Which two colours are complementary on the colour wheel?','points'=>2,'explanation'=>'Blue and orange sit opposite each other on the colour wheel, making them complementary.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q2->question_id,'option_text'=>'Red and yellow','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q2->question_id,'option_text'=>'Blue and orange','is_correct'=>true,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q2->question_id,'option_text'=>'Green and purple','is_correct'=>false,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q2->question_id,'option_text'=>'Black and white','is_correct'=>false,'display_order'=>4]);

        $q3 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'A serif font is best described as:','points'=>2,'explanation'=>'Serif fonts have small decorative strokes at the ends of letters and feel traditional.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q3->question_id,'option_text'=>'A font without decorative strokes','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q3->question_id,'option_text'=>'A font with small strokes at the ends of letters','is_correct'=>true,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q3->question_id,'option_text'=>'A font made only of pictures','is_correct'=>false,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q3->question_id,'option_text'=>'A font used only for headlines','is_correct'=>false,'display_order'=>4]);

        $q4 = Question::create(['question_type'=>'True/False','question_text'=>'In Zambian culture, gold and yellow are often associated with mourning and funerals.','points'=>2,'explanation'=>'Gold and yellow are usually associated with wealth, prestige, and celebration. Black and white are more common for funerals.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q4->question_id,'option_text'=>'True','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q4->question_id,'option_text'=>'False','is_correct'=>true,'display_order'=>2]);

        $q5 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'Which font pairing strategy is recommended for beginners?','points'=>2,'explanation'=>'Pairing a serif heading with a sans-serif body creates pleasant contrast while keeping the design readable.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q5->question_id,'option_text'=>'Use five or more different fonts for variety','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q5->question_id,'option_text'=>'Use only decorative script fonts everywhere','is_correct'=>false,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q5->question_id,'option_text'=>'Pair a serif heading with a sans-serif body text','is_correct'=>true,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q5->question_id,'option_text'=>'Never use bold text in any design','is_correct'=>false,'display_order'=>4]);

        $q6 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'What does proximity mean in design?','points'=>2,'explanation'=>'Proximity means placing related items close together so readers see them as a single unit.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q6->question_id,'option_text'=>'Making everything the same size','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q6->question_id,'option_text'=>'Grouping related items close together','is_correct'=>true,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q6->question_id,'option_text'=>'Aligning text to the centre only','is_correct'=>false,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q6->question_id,'option_text'=>'Using the same colour for every element','is_correct'=>false,'display_order'=>4]);

        $q7 = Question::create(['question_type'=>'True/False','question_text'=>'A monochromatic colour scheme uses different shades and tints of a single hue.','points'=>2,'explanation'=>'Monochromatic schemes are based on one hue with varying lightness and saturation levels.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q7->question_id,'option_text'=>'True','is_correct'=>true,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q7->question_id,'option_text'=>'False','is_correct'=>false,'display_order'=>2]);

        $q8 = Question::create(['question_type'=>'Short Answer','question_text'=>'What is the one-word design principle that means repeating colours, fonts, or shapes to create consistency?','points'=>3,'explanation'=>'Repetition builds familiarity and trust by using the same visual elements throughout a design.','correct_answer'=>'repetition']);

        $q9 = Question::create(['question_type'=>'Short Answer','question_text'=>'In typography, the vertical space between lines of text is called what? (one word)','points'=>3,'explanation'=>'Leading controls how much air exists between lines, which strongly affects readability.','correct_answer'=>'leading']);

        DB::table('quiz_questions')->insert([
            ['quiz_id'=>$quiz->id,'question_id'=>$q1->question_id,'display_order'=>1,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q2->question_id,'display_order'=>2,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q3->question_id,'display_order'=>3,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q4->question_id,'display_order'=>4,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q5->question_id,'display_order'=>5,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q6->question_id,'display_order'=>6,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q7->question_id,'display_order'=>7,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q8->question_id,'display_order'=>8,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q9->question_id,'display_order'=>9,'points_override'=>null],
        ]);
    }

    /* --------------------------------------------------------------------- */
    /*  Module 2 – Canva for Business and Events                             */
    /* --------------------------------------------------------------------- */
    private function seedModule2(Course $course): void
    {
        $module = Module::create([
            'course_id'       => $course->id,
            'title'           => 'Canva for Business and Events',
            'description'     => 'Master Canva on phone and computer to create professional posters, flyers, and social media graphics for real Zambian businesses and community events.',
            'display_order'   => 2,
            'duration_minutes'=> 0,
            'is_published'    => true,
        ]);

        $lessons = [];

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => 'Getting Started with Canva on Phone and Computer',
            'content'          => $this->lesson2_1(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 60,
            'display_order'    => 1,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => 'Designing Posters and Flyers for Local Events',
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
            'title'            => 'Social Media Graphics That Get Shares',
            'content'          => $this->lesson2_3(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 60,
            'display_order'    => 3,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $quizLesson = Lesson::create([
            'module_id'        => $module->id,
            'title'            => 'Module 2 Quiz: Canva and Event Design',
            'content'          => '<p>Complete this quiz to test your understanding of Canva, poster design, and social media graphics.</p>',
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
<p>By the end of this lesson, you will have a free Canva account set up on your phone and computer, and you will know how to navigate the workspace, choose templates, and create a simple business card. You will understand the difference between Canva Free and Canva Pro, and you will know which features matter most for a small business in Zambia.</p>

<h2>Why Canva?</h2>
<p>Canva is a free online design tool that runs in your web browser and on your Android or iPhone. You do not need to install heavy software like Adobe Photoshop, and you do not need a powerful computer. If you can use WhatsApp and Facebook, you can use Canva. For a market vendor in Soweto Market, a youth group leader in Kalomo, or a school teacher preparing a fundraiser, Canva makes professional design possible on any budget.</p>

<h2>Setting Up Your Account</h2>
<ol>
<li>Go to <strong>canva.com</strong> on your phone or computer, or download the Canva app from the Google Play Store.</li>
<li>Sign up using your email address, Google account, or phone number.</li>
<li>Choose the free plan. Canva Pro offers extra templates and tools, but the free version is powerful enough for most local businesses.</li>
<li>On the home screen, you will see a search bar and categories such as Social Media, Marketing, and Events.</li>
</ol>

<h2>The Canva Workspace</h2>
<p>When you open a design, the screen is divided into three main areas:</p>
<ul>
<li><strong>Left sidebar:</strong> Templates, uploads, elements (shapes, lines, frames), text, and backgrounds.</li>
<li><strong>Centre canvas:</strong> The area where you build your design. This is your digital page.</li>
<li><strong>Top toolbar:</strong> Undo, redo, download, share, and resize options.</li>
</ul>

<h2>Choosing the Right Template Size</h2>
<p>Before you start designing, pick the correct size. Canva has presets for common formats:</p>
<ul>
<li><strong>A4:</strong> Standard paper size for flyers, programmes, and certificates.</li>
<li><strong>A5:</strong> Half of A4; good for handbills and small adverts.</li>
<li><strong>Instagram Post (Square):</strong> 1080 × 1080 pixels. Best for Facebook and Instagram feeds.</li>
<li><strong>Facebook Cover:</strong> 820 × 312 pixels. Fits at the top of a Facebook page.</li>
<li><strong>Story:</strong> 1080 × 1920 pixels. Tall format for WhatsApp Status, Instagram Stories, and Facebook Stories.</li>
</ul>

<h2>Worked Example: A Simple Business Card</h2>
<p>A mobile money agent in Kalomo needs a business card to hand out at the market. She wants her name, phone number, and a note that she accepts Airtel Money and MTN MoMo.</p>
<ol>
<li>Search "business card" in Canva and pick a clean, two-sided template.</li>
<li>On the front, replace the placeholder name with "Mary Banda, Mobile Money Agent."</li>
<li>Change the phone number to her actual line, starting with +260.</li>
<li>On the back, add small logos or text saying "Airtel Money & MTN MoMo Accepted."</li>
<li>Keep the background white or light grey for easy printing at a local shop.</li>
<li>Download as PDF Print for the best quality when sending to a printer in Lusaka or Livingstone.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Open Canva on your phone or computer.</li>
<li>Create a new design and search for "Business Card."</li>
<li>Choose any free template and replace the text with your own name and a pretend business.</li>
<li>Add one shape or icon that matches your pretend business.</li>
<li>Download the result as a PNG and save it to your phone gallery.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Template:</strong> A pre-made design layout that you can customise with your own text and images.</li>
<li><strong>Canvas:</strong> The blank or template area in the centre of the screen where you place your design elements.</li>
<li><strong>Element:</strong> Any item you add to a design, such as a shape, line, photo, or icon.</li>
<li><strong>Grid:</strong> Invisible lines that help you align objects evenly on the canvas.</li>
<li><strong>Brand kit:</strong> A saved collection of your logo, colours, and fonts for consistent designs.</li>
</ul>

<h2>Summary</h2>
<p>Canva is the most accessible design tool for Zambian entrepreneurs and community organisers. By setting up a free account, learning the workspace, and practising with templates, you can already produce designs that look professional enough for print shops and social media. In the next lessons, you will apply these skills to real projects: event posters and social media graphics.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool">Canva Design School</a> — official free tutorials for beginners.</li>
<li><a href="https://support.google.com">Google Support</a> — help with Google account sign-in if you use that to register on Canva.</li>
</ul>
HTML;
    }

    private function lesson2_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will know how to design posters and flyers for real events in your community—church crusades, school fundraisers, shop promotions, and funeral programmes. You will understand paper sizes, how to organise information so it is read in the right order, and how to prepare files for a local print shop.</p>

<h2>Posters vs Flyers</h2>
<p>A <strong>poster</strong> is usually large (A3 or bigger) and designed to be read from a distance. It is nailed to walls, pinned to notice boards, or taped to shop windows. A <strong>flyer</strong> is smaller (A5 or A6) and designed to be handed out or stacked on a counter. The same event might need both: a poster to attract attention outside, and a flyer people can take home.</p>

<h2>What Every Event Poster Needs</h2>
<p>Missing information is the most common mistake in community design. Every event poster should include these six items, organised from most to least important:</p>
<ol>
<li><strong>Event name:</strong> The biggest text on the page. If someone walks past in three seconds, they should know what is happening.</li>
<li><strong>Date and time:</strong> Day of the week, full date, and start time. For example, "Saturday, 15 June 2025, 08:00 hours."</li>
<li><strong>Venue:</strong> Be specific. "Kalomo Community Hall" is better than "Town Centre."</li>
<li><strong>Purpose or headline:</strong> One line saying why people should come. "Free health screening for all ages" or "Back-to-school uniforms at half price."</li>
<li><strong>Contact or payment details:</strong> Phone number, WhatsApp, or mobile money details if tickets are sold in advance.</li>
<li><strong>Organiser or sponsor:</strong> Church name, school name, or business name at the bottom.</li>
</ol>

<h2>Worked Example: A School Fundraising Braii</h2>
<p>Kalomo Basic School wants to raise money for new desks. They are hosting a fundraising braii on a Saturday afternoon. Parents can buy plates of chicken, nshima, and vegetables. Tickets are sold in advance via MTN MoMo.</p>
<p>Here is the information hierarchy for the poster:</p>
<ul>
<li><strong>Headline:</strong> "Fundraising Braii for Kalomo Basic School" in large, bold, dark green letters.</li>
<li><strong>Subheadline:</strong> "Delicious food, music, and community spirit" in slightly smaller gold text.</li>
<li><strong>Details block:</strong> Date, time, and venue grouped together with an icon of a calendar and a map pin.</li>
<li><strong>Ticket info:</strong> "Tickets K50 per plate. Pay via MTN MoMo: 0965 992 967."</li>
<li><strong>Contact:</strong> "For tickets or enquiries, WhatsApp Mrs. Chileshe on 0977 123 456."</li>
</ul>
<p>The colours green and gold feel celebratory and local. The MTN MoMo detail is crucial because many parents cannot visit the school during working hours.</p>

<h2>Designing for Funeral Programmes</h2>
<p>Funeral programmes are a real local staple. They require a respectful, formal design with clear hierarchy. A typical Zambian funeral programme is A5 or A4, folded in half, and contains:</p>
<ul>
<li>The name of the deceased, large and centred.</li>
<li>Dates of birth and passing.</li>
<li>Order of service (hymns, readings, eulogies).</li>
<li>Names of surviving family members.</li>
<li>A photograph, usually black and white or sepia.</li>
</ul>
<p>Use serif fonts for the name and body text. Keep colours dark and dignified—black, dark grey, deep green, or navy. Avoid bright reds, yellows, or cartoonish clip art. White space around the name is a sign of respect.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open Canva and choose the A4 template.</li>
<li>Design a poster for an imaginary church crusade or school event in your community.</li>
<li>Include all six required pieces of information listed above.</li>
<li>Use the CRAP principles from Module 1: contrast for the headline, alignment for the details, proximity for related items, and repetition for colours and fonts.</li>
<li>Export as PDF Print and save it.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Bleed:</strong> Extra space around the edge of a design that allows the printer to trim without leaving white borders.</li>
<li><strong>Resolution:</strong> The amount of detail in an image, measured in DPI (dots per inch). Print needs 300 DPI; screens need 72 DPI.</li>
<li><strong>DPI:</strong> Dots per inch. A measure of image quality for printing.</li>
<li><strong>Layout:</strong> The arrangement of text and images on a page.</li>
<li><strong>White space:</strong> Empty areas in a design that help the eye rest and focus on what matters.</li>
</ul>

<h2>Summary</h2>
<p>Posters and flyers are the bread and butter of community graphic design in Zambia. By organising information in a clear hierarchy, choosing respectful colours for solemn occasions, and including practical details like mobile money numbers, you create designs that are not just pretty—they are useful. In the next lesson, you will adapt these skills for social media, where sizes and audiences change.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool">Canva Design School</a> — guides on poster and flyer design.</li>
<li><a href="https://www.gimp.org/tutorials">GIMP Tutorials</a> — for advanced image editing before placing photos in posters.</li>
</ul>
HTML;
    }

    private function lesson2_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will know the correct sizes and shapes for social media graphics on Facebook, Instagram, and WhatsApp Status. You will be able to design posts that look professional on a small phone screen, and you will understand how to write short, effective captions that encourage people to share your content.</p>

<h2>Why Social Media Graphics Matter</h2>
<p>In Zambia, WhatsApp and Facebook are the main ways businesses and community groups share news. A well-designed graphic stops the scroll. A poorly designed one—blurry, crowded, or the wrong shape—gets ignored. If you run a small business, a church media team, or a school PTA WhatsApp group, social media graphics are often the first impression people have of your organisation.</p>

<h2>Standard Sizes You Must Know</h2>
<table>
<thead>
<tr><th>Platform</th><th>Format</th><th>Size (pixels)</th><th>Best Use</th></tr>
</thead>
<tbody>
<tr><td>Facebook Feed</td><td>Square or landscape</td><td>1200 × 1200 or 1200 × 630</td><td>Announcements, promotions</td></tr>
<tr><td>Instagram Feed</td><td>Square</td><td>1080 × 1080</td><td>Product photos, quotes</td></tr>
<tr><td>Instagram/Facebook Story</td><td>Portrait</td><td>1080 × 1920</td><td>Quick updates, events</td></tr>
<tr><td>WhatsApp Status</td><td>Portrait</td><td>1080 × 1920</td><td>Daily updates, specials</td></tr>
<tr><td>Facebook Cover</td><td>Landscape</td><td>820 × 312</td><td>Page banner, branding</td></tr>
</tbody>
</table>

<h2>Designing for Small Screens</h2>
<p>Most Zambians access social media on mobile phones with screens between five and six inches wide. This means:</p>
<ul>
<li><strong>Text must be huge.</strong> If you cannot read the headline from arm's length, it is too small.</li>
<li><strong>One message per graphic.</strong> Do not cram prices, directions, a menu, and three phone numbers into one post. Split them into a carousel or multiple posts.</li>
<li><strong>High contrast.</strong> Phone screens are viewed outdoors, in bright sunlight, and during load-shedding by torchlight. Light text on a dark background, or dark text on a light background, works best.</li>
<li><strong>Brand consistently.</strong> Use the same two or three colours and one or two fonts in every post so people recognise your page instantly.</li>
</ul>

<h2>Worked Example: A Load-Shedding Schedule Graphic</h2>
<p>A small shop in Kalomo wants to tell customers their new hours during ZESCO load-shedding. The owner designs a WhatsApp Status graphic:</p>
<ol>
<li><strong>Background:</strong> Dark navy blue with a subtle pattern of light bulbs.</li>
<li><strong>Headline:</strong> "New Shop Hours During Load-Shedding" in bold white sans-serif, 72 points.</li>
<li><strong>Details:</strong> "Monday–Friday: 08:00–12:00 & 14:00–18:00. Saturday: 08:00–14:00." in yellow, 40 points.</li>
<li><strong>Contact:</strong> "Call or WhatsApp 0977 123 456" in white, 32 points.</li>
<li><strong>Logo:</strong> Small shop logo at the bottom centre for brand recognition.</li>
</ol>
<p>The graphic is 1080 × 1920 pixels, saved as a JPEG at high quality. It takes five seconds to read and answers the customer's question immediately.</p>

<h2>Writing Captions That Work</h2>
<p>A beautiful graphic is wasted with a weak caption. Good captions for a Zambian audience are:</p>
<ul>
<li><strong>Short:</strong> Two to four sentences maximum for Facebook and Instagram.</li>
<li><strong>Action-oriented:</strong> "Order today," "Share this post," "Save the date," or "Comment below."</li>
<li><strong>Local:</strong> Mention the town or area. "Available in Kalomo town centre" gets more local engagement than "Available now."</li>
<li><strong>Contact-friendly:</strong> Always include a phone number or WhatsApp link. Many customers will not click a website link but will happily send a message.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Open Canva and create an Instagram Post (1080 × 1080 pixels).</li>
<li>Design a graphic advertising a pretend weekend special at a local shop. Include a headline, price, and contact number.</li>
<li>Now resize it to Story format (1080 × 1920) and adjust the layout so it still looks good.</li>
<li>Save both versions as PNG files.</li>
<li>Write a two-sentence caption for each, including a call to action.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Aspect ratio:</strong> The proportional relationship between width and height. For example, 1:1 is square; 16:9 is wide.</li>
<li><strong>Pixel:</strong> The smallest unit of a digital image. More pixels mean sharper images.</li>
<li><strong>Engagement:</strong> Any interaction with a post, such as a like, comment, share, or save.</li>
<li><strong>Call-to-action:</strong> A phrase that tells the reader what to do next, such as "Call now" or "Order today."</li>
<li><strong>Hashtag:</strong> A word or phrase preceded by # that groups posts on social media.</li>
</ul>

<h2>Summary</h2>
<p>Social media graphics are a powerful, low-cost way to reach customers in Zambia. By designing for small screens, using the correct sizes, and pairing strong visuals with clear captions, you can help any business or community group get noticed on Facebook, Instagram, and WhatsApp.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool">Canva Design School</a> — social media design tutorials and size guides.</li>
<li><a href="https://grow.google">Google Digital Garage</a> — free courses on social media strategy and content creation.</li>
</ul>
HTML;
    }

    private function createModule2Quiz(Course $course): void
    {
        $quiz = Quiz::create([
            'course_id'           => $course->id,
            'lesson_id'           => null,
            'title'               => 'Module 2 Quiz: Canva and Event Design',
            'description'         => 'Test your Canva skills and your knowledge of poster and social media design.',
            'quiz_type'           => 'Graded',
            'time_limit_minutes'  => 20,
            'max_attempts'        => 3,
            'passing_score'       => 60.00,
            'show_correct_answers'=> true,
            'is_published'        => true,
        ]);

        $q1 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'Which Canva template size is best for a WhatsApp Status graphic?','points'=>2,'explanation'=>'WhatsApp Status uses a tall portrait format, which is 1080 × 1920 pixels.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q1->question_id,'option_text'=>'1200 × 630 pixels','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q1->question_id,'option_text'=>'1080 × 1920 pixels','is_correct'=>true,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q1->question_id,'option_text'=>'820 × 312 pixels','is_correct'=>false,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q1->question_id,'option_text'=>'A4 size','is_correct'=>false,'display_order'=>4]);

        $q2 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'What file format should you use when sending a business card design to a print shop?','points'=>2,'explanation'=>'PDF Print preserves quality and includes bleed settings that printers need.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q2->question_id,'option_text'=>'JPEG','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q2->question_id,'option_text'=>'PNG','is_correct'=>false,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q2->question_id,'option_text'=>'PDF Print','is_correct'=>true,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q2->question_id,'option_text'=>'GIF','is_correct'=>false,'display_order'=>4]);

        $q3 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'On an event poster, which information should be the largest and most visible?','points'=>2,'explanation'=>'The event name is the first thing a passer-by should understand.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q3->question_id,'option_text'=>'The organiser\'s phone number','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q3->question_id,'option_text'=>'The event name','is_correct'=>true,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q3->question_id,'option_text'=>'The date and time','is_correct'=>false,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q3->question_id,'option_text'=>'A list of sponsors','is_correct'=>false,'display_order'=>4]);

        $q4 = Question::create(['question_type'=>'True/False','question_text'=>'A funeral programme should use bright red and yellow colours to celebrate life.','points'=>2,'explanation'=>'Funeral programmes in Zambia require dark, dignified colours such as black, grey, or deep green.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q4->question_id,'option_text'=>'True','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q4->question_id,'option_text'=>'False','is_correct'=>true,'display_order'=>2]);

        $q5 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'Which of the following is the best call-to-action for a social media post?','points'=>2,'explanation'=>'A call-to-action tells the reader exactly what to do next, increasing engagement.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q5->question_id,'option_text'=>'The shop is open.','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q5->question_id,'option_text'=>'We sell many things.','is_correct'=>false,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q5->question_id,'option_text'=>'Order today via WhatsApp on 0977 123 456.','is_correct'=>true,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q5->question_id,'option_text'=>'The owner is nice.','is_correct'=>false,'display_order'=>4]);

        $q6 = Question::create(['question_type'=>'True/False','question_text'=>'An Instagram Feed post should normally be a square 1080 × 1080 pixels.','points'=>2,'explanation'=>'Instagram Feed posts display best at 1080 × 1080 pixels, which is a 1:1 aspect ratio.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q6->question_id,'option_text'=>'True','is_correct'=>true,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q6->question_id,'option_text'=>'False','is_correct'=>false,'display_order'=>2]);

        $q7 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'When designing social media graphics for Zambian audiences, why is high contrast important?','points'=>2,'explanation'=>'High contrast ensures readability on small phone screens viewed outdoors or by torchlight during load-shedding.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q7->question_id,'option_text'=>'It makes the file size smaller','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q7->question_id,'option_text'=>'It allows more colours to be used','is_correct'=>false,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q7->question_id,'option_text'=>'It improves readability on mobile screens in bright or dim light','is_correct'=>true,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q7->question_id,'option_text'=>'It is required by Facebook rules','is_correct'=>false,'display_order'=>4]);

        $q8 = Question::create(['question_type'=>'Short Answer','question_text'=>'What is the common term for the proportional relationship between the width and height of an image? (two words)','points'=>3,'explanation'=>'Aspect ratio describes how wide an image is compared to how tall it is.','correct_answer'=>'aspect ratio']);

        $q9 = Question::create(['question_type'=>'Short Answer','question_text'=>'In Canva, the central area where you place text and images is called the what? (one word)','points'=>3,'explanation'=>'The canvas is the working area in the centre of the Canva workspace.','correct_answer'=>'canvas']);

        DB::table('quiz_questions')->insert([
            ['quiz_id'=>$quiz->id,'question_id'=>$q1->question_id,'display_order'=>1,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q2->question_id,'display_order'=>2,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q3->question_id,'display_order'=>3,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q4->question_id,'display_order'=>4,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q5->question_id,'display_order'=>5,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q6->question_id,'display_order'=>6,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q7->question_id,'display_order'=>7,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q8->question_id,'display_order'=>8,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q9->question_id,'display_order'=>9,'points_override'=>null],
        ]);
    }

    /* --------------------------------------------------------------------- */
    /*  Module 3 – Photo Editing with GIMP                                   */
    /* --------------------------------------------------------------------- */
    private function seedModule3(Course $course): void
    {
        $module = Module::create([
            'course_id'       => $course->id,
            'title'           => 'Photo Editing with GIMP',
            'description'     => 'Learn to use the free GNU Image Manipulation Program to fix, enhance, and prepare photographs for print and web.',
            'display_order'   => 3,
            'duration_minutes'=> 0,
            'is_published'    => true,
        ]);

        $lessons = [];

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => 'Introduction to GIMP and Basic Photo Fixes',
            'content'          => $this->lesson3_1(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 75,
            'display_order'    => 1,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => 'Removing Backgrounds and Touching Up Photos',
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
            'title'            => 'Preparing Images for Print and Web',
            'content'          => $this->lesson3_3(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 60,
            'display_order'    => 3,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $quizLesson = Lesson::create([
            'module_id'        => $module->id,
            'title'            => 'Module 3 Quiz: Photo Editing',
            'content'          => '<p>Complete this quiz to test your understanding of GIMP, photo corrections, and image file formats.</p>',
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
<p>By the end of this lesson, you will have downloaded and installed GIMP, understand the main workspace, and know how to fix common photo problems such as poor brightness, dull colours, and crooked horizons. You will be able to take a dark photo from a market stall at dusk and turn it into a clear, saleable image.</p>

<h2>What Is GIMP?</h2>
<p>GIMP stands for <strong>GNU Image Manipulation Program</strong>. It is a free, open-source photo editor that works on Windows, Mac, and Linux computers. GIMP can do most of what Adobe Photoshop can do—adjust colours, remove blemishes, cut out backgrounds, and save images in many formats—without costing a single Kwacha. For a student at Edutrack Computer Training College or a small business owner in Kalomo, GIMP is the most practical choice for professional photo editing.</p>

<h2>Installing GIMP</h2>
<ol>
<li>Visit <strong>gimp.org</strong> on your computer.</li>
<li>Click "Download" and choose the version for your operating system (Windows, macOS, or Linux).</li>
<li>Run the installer and follow the prompts. The process takes about five minutes on a standard college computer.</li>
<li>Open GIMP. The first launch may take a moment as it loads fonts and brushes.</li>
</ol>

<h2>The GIMP Workspace</h2>
<p>When GIMP opens, you see three main areas:</p>
<ul>
<li><strong>Toolbox (left):</strong> Contains selection tools, paint brushes, erasers, text tool, and more.</li>
<li><strong>Image window (centre):</strong> The canvas where your photo appears.</li>
<li><strong>Layers and dialogs (right):</strong> Shows layers, colours, brushes, and undo history.</li>
</ul>
<p>Do not be intimidated by the number of buttons. You only need a handful for most tasks.</p>

<h2>Basic Photo Fixes</h2>
<h3>Brightness and Contrast</h3>
<p>Go to <strong>Colours → Brightness-Contrast</strong>. Move the Brightness slider right to lighten a dark photo. Move the Contrast slider right to make light areas lighter and dark areas darker. Be careful not to overdo it—too much contrast makes skin look grey and unnatural.</p>

<h3>Levels (Advanced Brightness)</h3>
<p>For finer control, use <strong>Colours → Levels</strong>. You will see a graph called a histogram. Drag the left triangle to the start of the dark area, the right triangle to the start of the light area, and the middle triangle to adjust overall brightness. This tool is perfect for photos taken during load-shedding when the only light comes from a small lamp or phone torch.</p>

<h3>Cropping and Straightening</h3>
<p>Use the <strong>Rectangle Select Tool</strong> to draw a box around the part of the photo you want to keep. Then go to <strong>Image → Crop to Selection</strong>. To straighten a crooked horizon, use the <strong>Rotate Tool</strong> and drag until the horizon line is level.</p>

<h2>Worked Example: The Market Stall Photo</h2>
<p>A trader in Soweto Market takes a photo of her tomato stall at 18:00 during load-shedding. The photo is dark, yellowish, and slightly tilted. Here is the fix:</p>
<ol>
<li>Open the photo in GIMP.</li>
<li>Go to <strong>Colours → Auto → White Balance</strong> to remove the yellow cast.</li>
<li>Open <strong>Colours → Brightness-Contrast</strong> and increase brightness by 25 and contrast by 15.</li>
<li>Use <strong>Colours → Saturation</strong> to increase colour intensity by 10 so the tomatoes look red and fresh.</li>
<li>Select the Crop Tool, draw a rectangle around the stall, and crop.</li>
<li>Use the Rotate Tool to straighten the table edge.</li>
<li>Export as JPEG, quality 90, for use on WhatsApp or Facebook.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Find or take a photo that is too dark, too bright, or crooked.</li>
<li>Open it in GIMP.</li>
<li>Apply Brightness-Contrast and Saturation adjustments.</li>
<li>Crop it to remove unnecessary background.</li>
<li>Straighten the horizon if needed.</li>
<li>Save the original and the edited version side by side for comparison.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Layer:</strong> A transparent sheet in GIMP that holds part of an image. You can edit layers independently.</li>
<li><strong>Toolbox:</strong> The panel on the left side of GIMP containing all editing tools.</li>
<li><strong>Canvas:</strong> The main area where the image is displayed and edited.</li>
<li><strong>Brightness:</strong> The overall lightness or darkness of an image.</li>
<li><strong>Contrast:</strong> The difference between the lightest and darkest parts of an image.</li>
</ul>

<h2>Summary</h2>
<p>GIMP is a powerful, free tool for photo editing. By learning a few basic corrections—brightness, contrast, colour balance, crop, and rotate—you can rescue poor photos and turn them into professional images suitable for social media, flyers, and online shops. These skills are essential for any designer working with real photographs.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.gimp.org/tutorials">GIMP Tutorials</a> — official beginner tutorials from the GIMP team.</li>
<li><a href="https://www.w3schools.com">W3Schools</a> — general guides on image formats and web graphics.</li>
</ul>
HTML;
    }

    private function lesson3_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to remove backgrounds from photos, smooth skin blemishes, and prepare product images for Facebook shops or WhatsApp catalogues. These techniques help small businesses in Zambia present their goods professionally without hiring a studio photographer.</p>

<h2>Why Remove Backgrounds?</h2>
<p>A clean, white or transparent background makes a product look professional. If you sell shoes, handbags, or vegetables online, a busy background distracts the buyer. Removing the background puts all attention on the product. This is especially useful for Facebook Marketplace listings, WhatsApp Business catalogues, and e-commerce websites.</p>

<h2>Selection Tools in GIMP</h2>
<p>GIMP offers several ways to select part of a photo:</p>
<ul>
<li><strong>Rectangle Select:</strong> Best for boxes and straight-edged objects.</li>
<li><strong>Ellipse Select:</strong> Best for round objects like plates or faces.</li>
<li><strong>Free Select (Lasso):</strong> Draw any shape around an object with your mouse. Good for irregular shapes.</li>
<li><strong>Fuzzy Select (Magic Wand):</strong> Click a colour area and GIMP selects all connected pixels of that colour. Excellent for solid backgrounds.</li>
<li><strong>Foreground Select:</strong> Roughly outline the object, then paint the inside. GIMP separates foreground from background automatically. Best for complex edges like hair or fur.</li>
</ul>

<h2>Step-by-Step: Removing a Solid Background</h2>
<ol>
<li>Open your photo in GIMP.</li>
<li>Select the <strong>Fuzzy Select Tool</strong> from the toolbox.</li>
<li>Click on the background colour. You will see marching ants around the selected area.</li>
<li>If the selection misses spots, hold Shift and click those areas to add them.</li>
<li>Go to <strong>Select → Invert</strong>. Now the object is selected instead of the background.</li>
<li>Right-click the layer in the Layers panel and choose <strong>Add Alpha Channel</strong>. This allows transparency.</li>
<li>Press the Delete key. The background becomes a checkerboard pattern, which means it is transparent.</li>
<li>Use <strong>File → Export As</strong> and save as PNG to keep the transparency.</li>
</ol>

<h2>Touching Up Skin and Imperfections</h2>
<p>The <strong>Heal Tool</strong> and <strong>Clone Tool</strong> remove spots, scars, and dust from photos.</p>
<ul>
<li><strong>Clone Tool:</strong> Copies pixels from one area to another. Hold Ctrl and click a clean area to set the source, then paint over the blemish.</li>
<li><strong>Heal Tool:</strong> Similar to Clone, but it blends the copied pixels with the surrounding area. Better for skin because the result looks natural.</li>
</ul>
<p><strong>Example:</strong> A tailor in Kalomo takes a portrait for her business card. She has a small scar on her cheek. Using the Heal Tool, she samples clean skin from nearby and paints over the scar. The result is subtle and professional.</p>

<h2>Worked Example: A Product Photo for Facebook</h2>
<p>A young man sells hand-knitted chitenge bags through WhatsApp. He takes a photo on his kitchen table. The background is cluttered with cups and a radio.</p>
<ol>
<li>He opens the photo in GIMP and uses the Free Select Tool to trace around the bag.</li>
<li>He inverts the selection and deletes the background, then adds a plain white colour behind the bag on a new layer.</li>
<li>He uses Brightness-Contrast to make the chitenge colours vivid.</li>
<li>He crops the image to a square, 1080 × 1080 pixels, for Instagram and Facebook.</li>
<li>He exports as JPEG, quality 95, and uploads it to his WhatsApp Status with a price: "K350 each. Order via WhatsApp."</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Take a photo of a single object against a plain wall or table.</li>
<li>Open it in GIMP and remove the background using the Fuzzy Select or Free Select tool.</li>
<li>Add a white layer behind the object.</li>
<li>Export as PNG and JPEG. Compare the file sizes.</li>
<li>Use the Heal Tool to remove any small marks or dust spots on the object.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Selection:</strong> An active area of an image that you can edit independently from the rest.</li>
<li><strong>Magic Wand:</strong> The Fuzzy Select Tool, which selects areas of similar colour with one click.</li>
<li><strong>Alpha Channel:</strong> The part of an image file that controls transparency.</li>
<li><strong>Feathering:</strong> Softening the edge of a selection so the cut-out blends smoothly.</li>
<li><strong>Crop:</strong> Trimming away the outer parts of an image to improve composition or fit a size.</li>
</ul>

<h2>Summary</h2>
<p>Background removal and photo touch-ups turn casual snapshots into professional product images. Using GIMP's selection tools, alpha channels, and healing tools, you can help any Zambian business look more credible online. These skills are in high demand because every shop owner, tailor, and market vendor now sells through WhatsApp and Facebook.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.gimp.org/tutorials">GIMP Tutorials</a> — detailed guides on selection tools and layer masks.</li>
<li><a href="https://www.canva.com/designschool">Canva Design School</a> — product photography tips for non-designers.</li>
</ul>
HTML;
    }

    private function lesson3_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will understand the difference between image file formats, know when to use JPEG versus PNG versus PDF, and be able to prepare images at the correct resolution for both print shops and social media. You will save time and money by giving printers and web platforms exactly what they need.</p>

<h2>File Formats Explained</h2>
<p>Every time you save an image, you choose a format. That format determines quality, file size, and transparency support. Choosing the wrong format can mean a blurry poster, a website that loads slowly, or a printer who rejects your file.</p>

<h3>JPEG</h3>
<p>JPEG is the most common photo format. It compresses images to make file sizes small, which is great for websites and WhatsApp. However, JPEG does not support transparency, and heavy compression makes photos look blocky or pixelated. Use JPEG for photographs that will be viewed on screens.</p>

<h3>PNG</h3>
<p>PNG supports transparency, so it is ideal for logos, icons, and product photos with removed backgrounds. PNG files are larger than JPEGs, but they do not lose quality when saved repeatedly. Use PNG when you need a transparent background or when text must stay sharp.</p>

<h3>PDF</h3>
<p>PDF is not an image format in the traditional sense—it is a document format that can contain images, text, and vector graphics. Print shops almost always prefer PDF because it preserves fonts, colours, and layout exactly as you designed them. Use PDF when sending flyers, posters, or multi-page documents to a printer.</p>

<h3>TIFF</h3>
<p>TIFF is a high-quality format used by professional photographers and printers. Files are very large. Unless a printer specifically asks for TIFF, stick with PDF or high-quality JPEG.</p>

<h2>Resolution: DPI and PPI</h2>
<p><strong>DPI</strong> means dots per inch. It tells a printer how many ink dots to place in every inch of paper. <strong>PPI</strong> means pixels per inch, which is the screen equivalent.</p>
<ul>
<li><strong>Web and social media:</strong> 72–96 PPI is enough. Screens cannot show more detail than this, and higher resolutions only make files larger and slower to load.</li>
<li><strong>Print:</strong> 300 DPI is the standard. A shop banner or funeral programme printed at 300 DPI looks sharp and professional. Anything below 200 DPI will look soft or blurry when held in the hand.</li>
</ul>

<h2>Worked Example: The Same Image, Three Ways</h2>
<p>A designer creates a poster for a school prize-giving day. She needs versions for:</p>
<ol>
<li><strong>Facebook event cover:</strong> She exports as JPEG, 1200 × 630 pixels, 72 PPI. File size: 250 KB. Loads quickly on mobile networks.</li>
<li><strong>WhatsApp Status:</strong> She exports as JPEG, 1080 × 1920 pixels, 72 PPI. File size: 400 KB. Easy to forward.</li>
<li><strong>Print shop in Lusaka:</strong> She exports the final design as PDF, with all images embedded at 300 DPI. File size: 8 MB. The printer receives sharp text and photos ready for an A3 poster.</li>
</ol>

<h2>Compressing Images Without Ruining Them</h2>
<p>When exporting from GIMP, you control JPEG quality with a slider from 0 to 100. For social media, 85–90 is a good balance. For print shops, always send the original high-resolution file or a PDF—do not compress first.</p>
<p>In GIMP, go to <strong>File → Export As</strong>, choose JPEG, and set quality. Watch the preview window to see if faces become blocky or text becomes fuzzy.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open any photo in GIMP.</li>
<li>Export it as JPEG at quality 100, 80, 50, and 20.</li>
<li>Note the file size of each version.</li>
<li>Zoom in to 200% and compare how faces and text look at each quality level.</li>
<li>Export the same image as PNG and note the file size difference.</li>
<li>Write down which quality level you would use for WhatsApp, Facebook, and a print shop.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>DPI:</strong> Dots per inch. A measure of print resolution.</li>
<li><strong>JPEG:</strong> A compressed image format ideal for photos on screens.</li>
<li><strong>PNG:</strong> An image format that supports transparency and preserves sharp edges.</li>
<li><strong>Compression:</strong> Reducing file size by removing some image data. Lossy compression (JPEG) sacrifices quality; lossless (PNG) does not.</li>
<li><strong>Resolution:</strong> The amount of detail in an image, usually measured in pixels per inch or dots per inch.</li>
</ul>

<h2>Summary</h2>
<p>Choosing the right file format and resolution saves money, time, and embarrassment. Use JPEG for social media, PNG for transparent graphics, and PDF for print jobs. Always check whether the destination needs 72 PPI for screens or 300 DPI for paper. These simple rules separate amateur designers from professionals.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.gimp.org/tutorials">GIMP Tutorials</a> — exporting and saving images in different formats.</li>
<li><a href="https://www.w3schools.com">W3Schools</a> — explanations of image formats for web use.</li>
</ul>
HTML;
    }

    private function createModule3Quiz(Course $course): void
    {
        $quiz = Quiz::create([
            'course_id'           => $course->id,
            'lesson_id'           => null,
            'title'               => 'Module 3 Quiz: Photo Editing',
            'description'         => 'Test your knowledge of GIMP tools, file formats, and image resolution.',
            'quiz_type'           => 'Graded',
            'time_limit_minutes'  => 20,
            'max_attempts'        => 3,
            'passing_score'       => 60.00,
            'show_correct_answers'=> true,
            'is_published'        => true,
        ]);

        $q1 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'Which GIMP tool is best for selecting an area of similar colour with a single click?','points'=>2,'explanation'=>'The Fuzzy Select Tool, also called the Magic Wand, selects connected areas of similar colour.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q1->question_id,'option_text'=>'Rectangle Select','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q1->question_id,'option_text'=>'Free Select','is_correct'=>false,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q1->question_id,'option_text'=>'Fuzzy Select (Magic Wand)','is_correct'=>true,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q1->question_id,'option_text'=>'Text Tool','is_correct'=>false,'display_order'=>4]);

        $q2 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'Which image format supports transparent backgrounds?','points'=>2,'explanation'=>'PNG supports an alpha channel, which allows parts of the image to be transparent.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q2->question_id,'option_text'=>'JPEG','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q2->question_id,'option_text'=>'PNG','is_correct'=>true,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q2->question_id,'option_text'=>'TIFF','is_correct'=>false,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q2->question_id,'option_text'=>'BMP','is_correct'=>false,'display_order'=>4]);

        $q3 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'What is the standard print resolution in DPI for professional-quality posters and flyers?','points'=>2,'explanation'=>'Print shops require 300 DPI for sharp, professional results on paper.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q3->question_id,'option_text'=>'72 DPI','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q3->question_id,'option_text'=>'150 DPI','is_correct'=>false,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q3->question_id,'option_text'=>'300 DPI','is_correct'=>true,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q3->question_id,'option_text'=>'600 DPI','is_correct'=>false,'display_order'=>4]);

        $q4 = Question::create(['question_type'=>'True/False','question_text'=>'JPEG files can have transparent backgrounds.','points'=>2,'explanation'=>'JPEG does not support transparency. Use PNG if you need a transparent background.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q4->question_id,'option_text'=>'True','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q4->question_id,'option_text'=>'False','is_correct'=>true,'display_order'=>2]);

        $q5 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'Which format do most print shops prefer for flyers and posters?','points'=>2,'explanation'=>'PDF preserves fonts, layout, and image quality, making it the standard for print jobs.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q5->question_id,'option_text'=>'PNG','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q5->question_id,'option_text'=>'JPEG','is_correct'=>false,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q5->question_id,'option_text'=>'PDF','is_correct'=>true,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q5->question_id,'option_text'=>'GIF','is_correct'=>false,'display_order'=>4]);

        $q6 = Question::create(['question_type'=>'True/False','question_text'=>'The Heal Tool in GIMP blends copied pixels with the surrounding area for a natural look.','points'=>2,'explanation'=>'The Heal Tool is specifically designed to blend repairs into the original texture, unlike the Clone Tool.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q6->question_id,'option_text'=>'True','is_correct'=>true,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q6->question_id,'option_text'=>'False','is_correct'=>false,'display_order'=>2]);

        $q7 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'For web and social media graphics, what PPI range is generally sufficient?','points'=>2,'explanation'=>'Screens display images at 72–96 PPI, so higher resolutions waste file size without improving appearance.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q7->question_id,'option_text'=>'300–600 PPI','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q7->question_id,'option_text'=>'72–96 PPI','is_correct'=>true,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q7->question_id,'option_text'=>'150–200 PPI','is_correct'=>false,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q7->question_id,'option_text'=>'10–30 PPI','is_correct'=>false,'display_order'=>4]);

        $q8 = Question::create(['question_type'=>'Short Answer','question_text'=>'In GIMP, what must you add to a layer before you can make parts of it transparent? (two words)','points'=>3,'explanation'=>'The Alpha Channel controls transparency in GIMP layers.','correct_answer'=>'alpha channel']);

        $q9 = Question::create(['question_type'=>'Short Answer','question_text'=>'What does DPI stand for? (three words)','points'=>3,'explanation'=>'Dots per inch measures how many ink dots a printer places in one inch of paper.','correct_answer'=>'dots per inch']);

        DB::table('quiz_questions')->insert([
            ['quiz_id'=>$quiz->id,'question_id'=>$q1->question_id,'display_order'=>1,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q2->question_id,'display_order'=>2,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q3->question_id,'display_order'=>3,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q4->question_id,'display_order'=>4,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q5->question_id,'display_order'=>5,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q6->question_id,'display_order'=>6,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q7->question_id,'display_order'=>7,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q8->question_id,'display_order'=>8,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q9->question_id,'display_order'=>9,'points_override'=>null],
        ]);
    }

    /* --------------------------------------------------------------------- */
    /*  Module 4 – Logos, Brand Kits, and Professional Identity              */
    /* --------------------------------------------------------------------- */
    private function seedModule4(Course $course): void
    {
        $module = Module::create([
            'course_id'       => $course->id,
            'title'           => 'Logos, Brand Kits, and Professional Identity',
            'description'     => 'Learn to design logos and brand kits that help local businesses look trustworthy, consistent, and memorable.',
            'display_order'   => 4,
            'duration_minutes'=> 0,
            'is_published'    => true,
        ]);

        $lessons = [];

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => 'Designing Logos for Local Businesses',
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
            'title'            => 'Building a Simple Brand Kit',
            'content'          => $this->lesson4_2(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 60,
            'display_order'    => 2,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => 'Funeral Programmes and Formal Documents',
            'content'          => $this->lesson4_3(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 75,
            'display_order'    => 3,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $quizLesson = Lesson::create([
            'module_id'        => $module->id,
            'title'            => 'Module 4 Quiz: Branding and Formal Design',
            'content'          => '<p>Complete this quiz to test your understanding of logo design, brand kits, and formal document layout.</p>',
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
<p>By the end of this lesson, you will understand the main types of logos, know how to sketch ideas for a local business, and be able to create a simple but effective logo using Canva or GIMP. You will also know which mistakes to avoid so your logo looks professional on business cards, shop signs, and phone screens.</p>

<h2>What Is a Logo?</h2>
<p>A logo is a visual symbol that identifies a business, organisation, or product. It is often the first thing a customer sees, and it appears on everything from shop signs to WhatsApp profile pictures. A good logo is simple, memorable, and appropriate for the business it represents.</p>

<h2>The Five Types of Logos</h2>
<ol>
<li><strong>Wordmark:</strong> Text only, using a distinctive font. Examples include Google and Coca-Cola. Best for businesses with short, unique names.</li>
<li><strong>Lettermark:</strong> Initials or abbreviations. Examples include HBO and IBM. Best for businesses with long names that are hard to remember.</li>
<li><strong>Pictorial Mark:</strong> A recognisable image or icon. Examples include Apple and Twitter. Best when the image is closely tied to the brand.</li>
<li><strong>Abstract Mark:</strong> A geometric shape that represents the brand idea. Examples include Pepsi and Adidas. Best for businesses that want a unique, modern feel.</li>
<li><strong>Combination Mark:</strong> Text and an icon together. Examples include Burger King and Lacoste. Best for new businesses because the name and image reinforce each other.</li>
</ol>

<h2>Designing for a Zambian Business</h2>
<p>Most small businesses in Zambia need a combination mark: the business name plus a simple icon. The icon should relate to what the business does. A maize mill might use an ear of maize. A tailoring shop might use a needle and thread. A mobile money agent might use a phone and cash symbol.</p>

<h2>Worked Example: Kalomo Poultry Logo</h2>
<p>A young entrepreneur starts a chicken-rearing business called "Kalomo Poultry." She wants a logo for her flyers, price lists, and eventually a painted shop sign.</p>
<ol>
<li><strong>Type:</strong> Combination mark. The name is readable, and a simple icon adds visual interest.</li>
<li><strong>Icon:</strong> A stylised chicken silhouette in dark green, facing right. The shape is simple enough to recognise even when printed small on a business card.</li>
<li><strong>Text:</strong> "KALOMO POULTRY" in bold, clean sans-serif letters. The word "Kalomo" is slightly larger than "Poultry" to emphasise the local connection.</li>
<li><strong>Colours:</strong> Dark green for the icon (nature, agriculture) and dark grey for the text (professional, easy to read). A touch of gold is used as an accent on taglines.</li>
<li><strong>Test:</strong> She shrinks the logo to the size of a fingernail. The chicken shape is still clear. She enlarges it to A3 size. The edges stay smooth because the icon is a vector shape created in Canva.</li>
</ol>

<h2>Common Logo Mistakes</h2>
<ul>
<li><strong>Too many colours:</strong> Limit yourself to two or three colours. More than that looks chaotic and expensive to print.</li>
<li><strong>Too much detail:</strong> A logo with a chicken, a maize cob, a sun, and a river will look like a mess at small sizes. Choose one symbol.</li>
<li><strong>Using clip art:</strong> Do not download random images from Google. They are often copyrighted and look generic. Create or customise your icon in Canva.</li>
<li><strong>Ignoring black and white:</strong> A good logo must work in one colour. If it needs colour to make sense, it is too weak.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Think of a real or imaginary business in your community (shop, farm, tailoring, hair salon).</li>
<li>Decide which of the five logo types fits best.</li>
<li>Sketch three rough logo ideas on paper. Each should take less than two minutes.</li>
<li>Pick your favourite sketch and recreate it in Canva using shapes and text.</li>
<li>Test it at three sizes: very small (like a phone app icon), medium (like a business card), and large (like a shop sign).</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Wordmark:</strong> A text-only logo that uses stylised lettering.</li>
<li><strong>Vector:</strong> An image made of mathematical paths rather than pixels, so it can scale to any size without losing quality.</li>
<li><strong>Scalable:</strong> Able to be enlarged or reduced without becoming blurry or pixelated.</li>
<li><strong>Monogram:</strong> A logo made from the initials of a person or organisation.</li>
<li><strong>Trademark:</strong> A legally registered symbol or name that identifies a brand and protects it from copying.</li>
</ul>

<h2>Summary</h2>
<p>A logo is the face of a business. By choosing the right type, keeping the design simple, and testing it at different sizes, you can create logos that help Zambian businesses look established and trustworthy. Whether you are designing for a poultry farm or a tailoring shop, the same principles apply: simplicity, relevance, and scalability.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool">Canva Design School</a> — logo design fundamentals and tutorials.</li>
<li><a href="https://www.gimp.org/tutorials">GIMP Tutorials</a> — creating and editing logo graphics.</li>
</ul>
HTML;
    }

    private function lesson4_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will know how to create a simple brand kit that keeps a business looking consistent across every flyer, social media post, and shop sign. You will understand how to document colours, fonts, and logo rules so that anyone—even someone who did not design the original logo—can produce materials that match.</p>

<h2>What Is a Brand Kit?</h2>
<p>A brand kit is a short document that defines the visual rules for a business. It typically includes:</p>
<ul>
<li>The official logo and variations (colour, black and white, icon only).</li>
<li>The exact colours, usually written as hex codes (for screens) and CMYK values (for print).</li>
<li>The approved fonts for headings and body text.</li>
<li>Examples of correct and incorrect logo usage.</li>
</ul>
<p>For a small business in Zambia, a brand kit does not need to be a 50-page manual. A single A4 page with clear rules is enough to keep designs consistent.</p>

<h2>Why Consistency Matters</h2>
<p>When a customer sees the same colours, fonts, and logo on a shop sign, a Facebook post, and a business card, they begin to trust the business. Inconsistent branding looks amateur. If a hair salon uses pink on Monday, blue on Tuesday, and green on Wednesday, customers may wonder if it is even the same salon.</p>

<h2>Worked Example: A Tailoring Shop Brand Kit</h2>
<p>Mrs. Mutale runs a tailoring shop in Kalomo. She wants to look professional so she can attract contracts from schools and churches. Her brand kit fits on one A4 page:</p>
<ul>
<li><strong>Logo:</strong> A needle and thread icon next to the words "Mutale Fashions." The icon is always green; the text is always black.</li>
<li><strong>Primary colour:</strong> Forest green #228B22. Used for headings, borders, and the logo icon.</li>
<li><strong>Secondary colour:</strong> Cream #FFFDD0. Used as a background colour on business cards and flyers.</li>
<li><strong>Heading font:</strong> Montserrat Bold. Used for all titles and shop signs.</li>
<li><strong>Body font:</strong> Open Sans Regular. Used for addresses, prices, and descriptions.</li>
<li><strong>Rules:</strong> The logo must never be stretched. The green must never be replaced with bright red. No more than two fonts may appear on any design.</li>
</ul>

<h2>Creating Your Brand Kit Document</h2>
<ol>
<li>Open Canva and create an A4 document.</li>
<li>Place the logo at the top, showing the colour version and the black-and-white version.</li>
<li>Below the logo, create two coloured squares with the hex codes written underneath.</li>
<li>Write the heading font name in large letters and the body font name in smaller letters.</li>
<li>Add two small example designs—perhaps a business card and a social media post—showing the brand kit in action.</li>
<li>Export as PDF for easy sharing with printers or assistants.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Choose the logo you created in the previous lesson.</li>
<li>Pick two colours and two fonts that match the business personality.</li>
<li>Create a one-page brand kit in Canva with logo, colours, fonts, and two example designs.</li>
<li>Show it to a friend and ask if the business feels trustworthy and consistent.</li>
<li>Save the brand kit as a PDF and as a PNG for easy reference.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Brand kit:</strong> A set of rules and assets that define how a brand looks across all materials.</li>
<li><strong>Style guide:</strong> A document that explains how to use brand colours, fonts, and logos correctly.</li>
<li><strong>Primary colour:</strong> The main colour associated with a brand.</li>
<li><strong>Secondary colour:</strong> A supporting colour used for accents, backgrounds, or highlights.</li>
<li><strong>Logo lockup:</strong> The exact arrangement of a logo and text that should not be altered.</li>
</ul>

<h2>Summary</h2>
<p>A brand kit turns a single logo into a complete visual identity. By documenting colours, fonts, and usage rules, you help businesses present themselves consistently whether they are printing a flyer, posting on Facebook, or painting a shop wall. For Zambian small businesses, this level of professionalism can be the difference between being ignored and being trusted.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool">Canva Design School</a> — building brand kits and style guides.</li>
<li><a href="https://www.w3schools.com">W3Schools</a> — understanding hex colour codes for digital design.</li>
</ul>
HTML;
    }

    private function lesson4_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will understand how to design respectful, well-organised funeral programmes and other formal documents. You will know the standard layout for Zambian funeral programmes, which fonts convey dignity, and how to balance text and white space so the document feels calm rather than cluttered.</p>

<h2>The Role of the Funeral Programme</h2>
<p>In Zambia, the funeral programme is more than a piece of paper. It is a keepsake that family members treasure for years. A well-designed programme honours the deceased, guides mourners through the service, and provides a record of the person's life. Because of this emotional weight, the design must be handled with great care and respect.</p>

<h2>Standard Content of a Zambian Funeral Programme</h2>
<ul>
<li><strong>Cover:</strong> Full name of the deceased, dates of birth and passing, a photograph, and a short verse or hymn title.</li>
<li><strong>Order of Service:</strong> A numbered list of events—prayers, hymns, readings, eulogies, and committal.</li>
<li><strong>Tribute or Biography:</strong> A brief life history written by the family.</li>
<li><strong>Family Members:</strong> Names of surviving spouse, children, parents, and siblings.</li>
<li><strong>Acknowledgements:</strong> Thanks to churches, funeral committees, and donors.</li>
</ul>

<h2>Design Rules for Funeral Programmes</h2>
<ol>
<li><strong>Colours:</strong> Use dark, muted tones. Black, charcoal grey, deep green, or navy blue are appropriate. Avoid bright colours, gradients, and patterns that feel festive.</li>
<li><strong>Fonts:</strong> Use serif fonts for the name and body text—they feel formal and timeless. Avoid script fonts that are hard to read, and avoid playful display fonts entirely.</li>
<li><strong>Photographs:</strong> One or two photos maximum. They should be clear, recent, and respectful. Sepia or black-and-white treatment is common and dignified.</li>
<li><strong>White space:</strong> Do not crowd the page. Generous margins and space between sections create a sense of peace and allow elderly readers to follow the text easily.</li>
<li><strong>Alignment:</strong> Centre-align the name and dates on the cover. Left-align the order of service inside for easy reading.</li>
</ol>

<h2>Worked Example: A Four-Page A5 Programme</h2>
<p>A family in Kalomo needs a funeral programme for their late father. They choose an A5 size folded in half, giving four pages.</p>
<ul>
<li><strong>Page 1 (Cover):</strong> A black-and-white portrait at the top. Below it, in large serif type: "In Loving Memory of Mr. Joseph Banda." Dates: "14 March 1955 – 10 June 2025." A thin gold line separates the name from the verse: "The Lord is my shepherd."</li>
<li><strong>Page 2 (Order of Service):</strong> Left-aligned, numbered list. Each item includes the hymn number, Bible reference, or speaker name. A subtle grey line separates each section.</li>
<li><strong>Page 3 (Biography and Family):</strong> A short tribute in two paragraphs, followed by a list of family members. Names are in bold; relationships are in regular weight.</li>
<li><strong>Page 4 (Acknowledgements and Back Cover):</strong> A thank-you message from the family, followed by funeral committee contact numbers. The back cover repeats the name and dates in small type.</li>
</ul>

<h2>Other Formal Documents</h2>
<p>The same principles apply to wedding invitations, graduation programmes, and certificate designs:</p>
<ul>
<li>Choose one or two elegant fonts.</li>
<li>Use generous margins.</li>
<li>Let the most important text be the largest.</li>
<li>Keep decorative elements minimal.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Open Canva and create an A5 document (148 × 210 mm).</li>
<li>Design a funeral programme cover using the rules above.</li>
<li>Use a serif font for the name and a sans-serif font for the dates.</li>
<li>Include a placeholder photo and a short Bible verse or poem line.</li>
<li>Show your design to someone older than 50 and ask if the text is easy to read.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Layout grid:</strong> A system of invisible lines that helps align text and images consistently on a page.</li>
<li><strong>Margin:</strong> The empty space around the edges of a page.</li>
<li><strong>Gutter:</strong> The inner margin of a folded document, where pages meet at the spine.</li>
<li><strong>Formal typeface:</strong> A font that looks serious, traditional, and appropriate for solemn occasions.</li>
<li><strong>Hierarchy:</strong> The visual arrangement that shows which information is most important.</li>
</ul>

<h2>Summary</h2>
<p>Funeral programmes and formal documents demand a different design approach than adverts or social media posts. The goal is not excitement but dignity. By using restrained colours, readable serif fonts, generous white space, and careful alignment, you create documents that honour the occasion and serve the people who read them.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool">Canva Design School</a> — layout and typography for formal documents.</li>
<li><a href="https://www.gimp.org/tutorials">GIMP Tutorials</a> — converting photos to black and white or sepia.</li>
</ul>
HTML;
    }

    private function createModule4Quiz(Course $course): void
    {
        $quiz = Quiz::create([
            'course_id'           => $course->id,
            'lesson_id'           => null,
            'title'               => 'Module 4 Quiz: Branding and Formal Design',
            'description'         => 'Test your knowledge of logo types, brand kits, and funeral programme design.',
            'quiz_type'           => 'Graded',
            'time_limit_minutes'  => 20,
            'max_attempts'        => 3,
            'passing_score'       => 60.00,
            'show_correct_answers'=> true,
            'is_published'        => true,
        ]);

        $q1 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'Which logo type combines text and an icon?','points'=>2,'explanation'=>'A combination mark includes both the business name and a symbol.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q1->question_id,'option_text'=>'Wordmark','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q1->question_id,'option_text'=>'Lettermark','is_correct'=>false,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q1->question_id,'option_text'=>'Combination Mark','is_correct'=>true,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q1->question_id,'option_text'=>'Abstract Mark','is_correct'=>false,'display_order'=>4]);

        $q2 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'What is the main advantage of a vector logo over a pixel-based logo?','points'=>2,'explanation'=>'Vector graphics use mathematical paths, so they can scale to any size without losing quality.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q2->question_id,'option_text'=>'It has more colours','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q2->question_id,'option_text'=>'It can be enlarged or reduced without becoming blurry','is_correct'=>true,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q2->question_id,'option_text'=>'It is always smaller in file size','is_correct'=>false,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q2->question_id,'option_text'=>'It cannot be edited','is_correct'=>false,'display_order'=>4]);

        $q3 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'Which font style is most appropriate for a funeral programme?','points'=>2,'explanation'=>'Serif fonts feel formal, traditional, and respectful—ideal for solemn documents.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q3->question_id,'option_text'=>'Bright pink script font','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q3->question_id,'option_text'=>'Bold cartoon display font','is_correct'=>false,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q3->question_id,'option_text'=>'Elegant serif font','is_correct'=>true,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q3->question_id,'option_text'=>'Italic graffiti font','is_correct'=>false,'display_order'=>4]);

        $q4 = Question::create(['question_type'=>'True/False','question_text'=>'A brand kit should include the business logo, approved colours, and designated fonts.','points'=>2,'explanation'=>'A brand kit documents visual standards so all designs stay consistent.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q4->question_id,'option_text'=>'True','is_correct'=>true,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q4->question_id,'option_text'=>'False','is_correct'=>false,'display_order'=>2]);

        $q5 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'For a small Zambian business, how many colours should a logo typically use?','points'=>2,'explanation'=>'Limiting a logo to two or three colours keeps printing affordable and the design clean.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q5->question_id,'option_text'=>'Ten or more for variety','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q5->question_id,'option_text'=>'Exactly one colour only','is_correct'=>false,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q5->question_id,'option_text'=>'Two or three colours','is_correct'=>true,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q5->question_id,'option_text'=>'Every colour in the rainbow','is_correct'=>false,'display_order'=>4]);

        $q6 = Question::create(['question_type'=>'True/False','question_text'=>'Clip art downloaded from Google is always safe to use in a paid logo design.','points'=>2,'explanation'=>'Most images found through Google are copyrighted. Using them in paid work can lead to legal issues.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q6->question_id,'option_text'=>'True','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q6->question_id,'option_text'=>'False','is_correct'=>true,'display_order'=>2]);

        $q7 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'In a funeral programme, what is the purpose of generous white space?','points'=>2,'explanation'=>'White space creates calm, improves readability for elderly mourners, and shows respect.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q7->question_id,'option_text'=>'To make the document look cheaper to print','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q7->question_id,'option_text'=>'To create calm and improve readability','is_correct'=>true,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q7->question_id,'option_text'=>'To fit more text on one page','is_correct'=>false,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q7->question_id,'option_text'=>'To hide spelling mistakes','is_correct'=>false,'display_order'=>4]);

        $q8 = Question::create(['question_type'=>'Short Answer','question_text'=>'What is the term for a logo made entirely of the initials of a business name? (one word)','points'=>3,'explanation'=>'A lettermark uses initials or abbreviations as the logo.','correct_answer'=>'lettermark']);

        $q9 = Question::create(['question_type'=>'Short Answer','question_text'=>'What does a brand kit document that controls how a logo and its text must be arranged? (two words)','points'=>3,'explanation'=>'A logo lockup specifies the exact arrangement of icon and text and how they may not be altered.','correct_answer'=>'logo lockup']);

        DB::table('quiz_questions')->insert([
            ['quiz_id'=>$quiz->id,'question_id'=>$q1->question_id,'display_order'=>1,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q2->question_id,'display_order'=>2,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q3->question_id,'display_order'=>3,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q4->question_id,'display_order'=>4,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q5->question_id,'display_order'=>5,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q6->question_id,'display_order'=>6,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q7->question_id,'display_order'=>7,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q8->question_id,'display_order'=>8,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q9->question_id,'display_order'=>9,'points_override'=>null],
        ]);
    }

    /* --------------------------------------------------------------------- */
    /*  Module 5 – Preparing for Print, Pricing, and Your Portfolio          */
    /* --------------------------------------------------------------------- */
    private function seedModule5(Course $course): void
    {
        $module = Module::create([
            'course_id'       => $course->id,
            'title'           => 'Preparing for Print, Pricing, and Your Portfolio',
            'description'     => 'Learn how to export files correctly for print shops, set prices for design work in Zambia, and build a portfolio that attracts real clients.',
            'display_order'   => 5,
            'duration_minutes'=> 0,
            'is_published'    => true,
        ]);

        $lessons = [];

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => 'CMYK vs RGB, Bleed, and Exporting for Print Shops',
            'content'          => $this->lesson5_1(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 75,
            'display_order'    => 1,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => 'Pricing Your Design Work in Zambia',
            'content'          => $this->lesson5_2(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 60,
            'display_order'    => 2,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $lessons[] = Lesson::create([
            'module_id'        => $module->id,
            'title'            => 'Building a Portfolio That Wins Clients',
            'content'          => $this->lesson5_3(),
            'lesson_type'      => 'Reading',
            'duration_minutes' => 60,
            'display_order'    => 3,
            'is_preview'       => false,
            'is_mandatory'     => true,
            'points'           => 10,
        ]);

        $quizLesson = Lesson::create([
            'module_id'        => $module->id,
            'title'            => 'Module 5 Quiz: Print, Pricing, and Portfolio',
            'content'          => '<p>Complete this quiz to test your understanding of print preparation, pricing, and portfolio building.</p>',
            'lesson_type'      => 'Quiz',
            'duration_minutes' => 20,
            'display_order'    => 4,
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
<p>By the end of this lesson, you will understand the difference between RGB and CMYK colour modes, know what bleed and crop marks are, and be able to export a design that any print shop in Lusaka, Livingstone, or Ndola can use without calling you for corrections. You will save time, avoid reprints, and build a reputation as a designer who understands production.</p>

<h2>RGB vs CMYK</h2>
<p>Every colour you see on a screen is made of light. Every colour you see on paper is made of ink. These two systems use different colour models.</p>
<ul>
<li><strong>RGB (Red, Green, Blue):</strong> Used for screens—phones, computers, projectors. It creates colours by adding light. RGB has a wider range of bright colours, especially neon greens and deep blues.</li>
<li><strong>CMYK (Cyan, Magenta, Yellow, Black):</strong> Used for printing. It creates colours by subtracting light—ink absorbs certain wavelengths and reflects others. CMYK cannot reproduce the brightest RGB colours, which is why a vibrant orange on your screen may look duller on paper.</li>
</ul>
<p><strong>Rule:</strong> Design in RGB for social media and websites. Convert to CMYK or use CMYK-aware PDF export for print jobs. Canva and GIMP handle this automatically when you choose PDF Print, but it is your job to check the result.</p>

<h2>What Is Bleed?</h2>
<p>Bleed is extra space around the edge of your design that gets trimmed off after printing. If you want colour or images to go right to the paper edge, you must extend them past the trim line into the bleed area. Without bleed, a small shift in the cutting machine leaves an ugly white strip along the edge.</p>
<p>Standard bleed is <strong>3 mm</strong> on all sides. For an A4 flyer (210 × 297 mm), you would design on a canvas of <strong>216 × 303 mm</strong> and place important text at least 5 mm inside the trim line.</p>

<h2>Crop Marks</h2>
<p>Crop marks are small lines printed in the corners that show the printer exactly where to cut. They sit outside the bleed area. When you export from Canva as PDF Print, crop marks are usually included automatically. If you export from GIMP, you may need to add them manually or ask the printer if they need them.</p>

<h2>Preparing Files for a Zambian Print Shop</h2>
<p>Print shops in major Zambian towns typically accept PDF files. Here is the checklist before you send a file:</p>
<ol>
<li><strong>Size:</strong> Is the canvas the correct final size plus 3 mm bleed on all sides?</li>
<li><strong>Resolution:</strong> Are all images 300 DPI?</li>
<li><strong>Colour mode:</strong> Did you export as CMYK or PDF Print?</li>
<li><strong>Fonts:</strong> Are all fonts embedded in the PDF? If not, the printer's computer may substitute a different font and ruin the layout.</li>
<li><strong>Bleed:</strong> Does background colour or imagery extend into the bleed area?</li>
<li><strong>Safe area:</strong> Is all important text at least 5 mm away from the trim edge?</li>
</ol>

<h2>Worked Example: Exporting a Funeral Programme</h2>
<p>A client needs 200 copies of an A5 funeral programme, printed in colour and folded in half.</p>
<ol>
<li>In Canva, the designer creates an A5 document (148 × 210 mm) and adds 3 mm bleed, making the working canvas 154 × 216 mm.</li>
<li>All background colour extends to the edge of the 154 × 216 mm canvas.</li>
<li>All text and photos sit at least 8 mm inside the trim edge.</li>
<li>Photos are imported at 300 DPI or higher.</li>
<li>The designer exports as PDF Print, which automatically includes crop marks.</li>
<li>Before sending, she opens the PDF to check that the fonts look correct and the colours are not unexpectedly bright or dull.</li>
<li>She sends the file via WhatsApp to the printer in Lusaka, along with clear instructions: "A5, folded, 200 copies, double-sided, colour."</li>
</ol>

<h2>Common Print Mistakes</h2>
<ul>
<li><strong>Low-resolution images:</strong> A photo pulled from Facebook is usually 72 DPI and will look blurry when printed. Always use the original high-resolution file.</li>
<li><strong>Forgotten bleed:</strong> Colour that stops exactly at the edge will almost certainly leave white strips after trimming.</li>
<li><strong>Wrong colour mode:</strong> Bright RGB colours become muddy in CMYK. Preview the conversion before sending.</li>
<li><strong>Missing fonts:</strong> If you used a custom font and did not embed it, the printer's software may replace it with Arial, breaking your layout.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Create an A4 design in Canva with a coloured background that extends to the edges.</li>
<li>Add text and a photo, keeping both at least 10 mm from the edges.</li>
<li>Export as PDF Print.</li>
<li>Open the PDF and zoom in. Check that the text is sharp and the photo is not pixelated.</li>
<li>Write a short email or WhatsApp message explaining the print job to an imaginary printer.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>CMYK:</strong> Cyan, Magenta, Yellow, and Black—the ink colours used in printing.</li>
<li><strong>RGB:</strong> Red, Green, and Blue—the light colours used on screens.</li>
<li><strong>Bleed:</strong> Extra design area that extends past the trim line to ensure colour reaches the edge after cutting.</li>
<li><strong>Crop marks:</strong> Printed lines that indicate where the paper should be trimmed.</li>
<li><strong>PDF/X:</strong> A standardised PDF format designed specifically for reliable printing.</li>
</ul>

<h2>Summary</h2>
<p>Print preparation is where many amateur designers fail. By understanding RGB versus CMYK, adding bleed, embedding fonts, and checking resolution, you ensure that your designs look as good on paper as they do on screen. Printers respect designers who send ready-to-print files, and clients appreciate not having to pay for reprints.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool">Canva Design School</a> — preparing files for professional printing.</li>
<li><a href="https://www.gimp.org/tutorials">GIMP Tutorials</a> — colour modes and export settings.</li>
</ul>
HTML;
    }

    private function lesson5_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will know how to set fair prices for common design jobs in Zambia. You will understand the difference between charging per hour and charging per project, know how to handle revisions and deposits, and be able to create a simple price list that clients respect.</p>

<h2>Why Pricing Matters</h2>
<p>Many new designers in Zambia undervalue their work. They design a logo for K50 or a flyer for K20 because they are afraid of losing the client. But cheap prices attract demanding clients who do not respect your time. Fair prices attract serious business owners who see design as an investment. Your job is to price honestly, deliver quality, and build a reputation that justifies your rates.</p>

<h2>Per Hour vs Per Project</h2>
<table>
<thead>
<tr><th>Method</th><th>Best For</th><th>Risk</th></tr>
</thead>
<tbody>
<tr><td>Per Hour</td><td>Ongoing work, uncertain scope, client meetings</td><td>Client may dispute hours; you must track time carefully</td></tr>
<tr><td>Per Project</td><td>Clear deliverables: logo, flyer, social media package</td><td>If the client requests many changes, you may work extra unpaid hours</td></tr>
</tbody>
</table>
<p>For most design work in Zambia, <strong>per project</strong> pricing is better because clients know the total cost upfront. You can include a set number of revisions—say, two rounds—and charge extra for additional changes.</p>

<h2>Sample Price Ranges for Zambian Designers</h2>
<p>These are realistic ranges for a beginner to intermediate designer in 2025. Prices vary by town, client budget, and complexity.</p>
<ul>
<li><strong>Simple flyer (A5 or A6):</strong> K50–K150</li>
<li><strong>Poster (A3 or A2):</strong> K100–K300</li>
<li><strong>Business card design:</strong> K80–K200</li>
<li><strong>Logo design:</strong> K300–K800</li>
<li><strong>Social media package (10 posts):</strong> K200–K500</li>
<li><strong>Funeral programme (4-page A5):</strong> K150–K400</li>
<li><strong>Brand kit (logo + colours + fonts):</strong> K400–K1,200</li>
</ul>
<p>Always ask for a <strong>50% deposit</strong> before starting work. This protects you from clients who disappear after receiving the design. The remaining 50% is paid before you hand over the final files.</p>

<h2>Managing Revisions and Scope Creep</h2>
<p><strong>Scope creep</strong> happens when a client keeps adding requests after the project has started. A flyer becomes a poster, then a banner, then a social media package, all for the original price.</p>
<p>Prevent this by writing a simple agreement before you start:</p>
<ol>
<li>List exactly what is included: "One A4 flyer, two rounds of revisions, final files in PDF and JPEG."</li>
<li>State the price and payment terms: "Total K250. K125 deposit to start. K125 on delivery."</li>
<li>Define revision limits: "Two rounds of changes included. Additional changes at K50 per round."</li>
<li>State what is not included: "Printing, photography, and copywriting are separate charges."</li>
</ol>

<h2>Worked Example: A Church Event Package</h2>
<p>A church in Kalomo asks for a poster, 100 flyers, and five social media posts for a youth crusade.</p>
<ul>
<li><strong>Poster design:</strong> K200</li>
<li><strong>Flyer design:</strong> K150</li>
<li><strong>Social media package (5 posts):</strong> K250</li>
<li><strong>Total design fee:</strong> K600</li>
<li><strong>Deposit:</strong> K300 before work begins</li>
<li><strong>Final payment:</strong> K300 before files are released</li>
<li><strong>Revisions:</strong> Two rounds included for each item</li>
</ul>
<p>The designer sends the agreement via WhatsApp. The church committee approves and sends the deposit via MTN MoMo. The designer begins work with confidence.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a simple price list for five design services you feel confident offering.</li>
<li>Write a short client agreement template that includes scope, price, deposit, and revision limits.</li>
<li>Ask two friends or family members if your prices feel fair. Adjust if needed.</li>
<li>Save your price list as a PDF that you can send to future clients.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Project rate:</strong> A fixed fee for a complete design job, agreed before work begins.</li>
<li><strong>Hourly rate:</strong> A fee based on the number of hours spent working.</li>
<li><strong>Revision:</strong> A round of changes requested by the client after seeing a draft.</li>
<li><strong>Deposit:</strong> A partial payment upfront that secures the designer's time and commitment.</li>
<li><strong>Scope creep:</strong> The gradual expansion of a project's requirements beyond the original agreement.</li>
</ul>

<h2>Summary</h2>
<p>Pricing design work is about respect—respect for your own time and respect for the client's budget. By setting clear project rates, collecting deposits, and writing simple agreements, you protect yourself and build a professional reputation. In Zambia's growing digital economy, skilled designers who charge fairly and deliver reliably will always find work.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google">Google Digital Garage</a> — free courses on freelancing and pricing your services.</li>
<li><a href="https://www.canva.com/designschool">Canva Design School</a> — guides on building a design business.</li>
</ul>
HTML;
    }

    private function lesson5_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will know how to build a design portfolio that shows your best work, explains your process, and convinces potential clients to hire you. You will learn what to include, what to leave out, and how to present your work even if you have never had a paying client.</p>

<h2>What Is a Portfolio?</h2>
<p>A portfolio is a collection of your best design work, organised so that a potential client can see your skills, style, and range in under two minutes. It is your most important marketing tool. Whether you are applying for a job, pitching to a church committee, or messaging a shop owner on WhatsApp, your portfolio does the talking before you do.</p>

<h2>What to Include</h2>
<p>You do not need fifty projects. Five strong pieces are better than twenty weak ones. Include:</p>
<ul>
<li><strong>Variety:</strong> Show different types of work—logos, flyers, social media posts, and photo edits.</li>
<li><strong>Real or realistic projects:</strong> If you have not had clients yet, create designs for imaginary businesses. A logo for "Kalomo Fresh Produce," a flyer for a school event, and a social media package for a hair salon all count.</li>
<li><strong>Process:</strong> For one or two projects, show a before-and-after or a sketch-to-final comparison. This proves you think, not just click.</li>
<li><strong>Context:</strong> Beside each piece, write one or two sentences explaining what the client needed and how your design solved the problem.</li>
</ul>

<h2>What to Leave Out</h2>
<ul>
<li>Projects you are not proud of.</li>
<li>Designs that look very similar to each other.</li>
<li>Work where you only changed a few words in a template.</li>
<li>Personal art or doodles unless they directly relate to a design skill.</li>
</ul>

<h2>Portfolio Formats for Zambian Designers</h2>
<p>You do not need a custom website to start. Here are practical options:</p>
<ol>
<li><strong>Canva Portfolio:</strong> Create a multi-page Canva document with full-screen images and captions. Export as PDF and send via WhatsApp or email.</li>
<li><strong>Google Drive Folder:</strong> Create a folder with subfolders for logos, flyers, and social media. Share the link with a short introduction message.</li>
<li><strong>Facebook Album:</strong> Create a "Design Work" album on your Facebook page. Upload high-quality images with descriptions.</li>
<li><strong>Simple Website:</strong> If you are comfortable with technology, a free website on a platform like WordPress.com or Wix can look very professional.</li>
</ol>

<h2>Presenting Each Project</h2>
<p>For every piece in your portfolio, include:</p>
<ul>
<li><strong>Project title:</strong> "Logo for Kalomo Poultry"</li>
<li><strong>Client or context:</strong> "A small chicken-rearing business needing a trustworthy local identity."</li>
<li><strong>Your solution:</strong> "I chose green and gold to reflect agriculture and quality, and created a scalable vector icon of a rooster."</li>
<li><strong>Final image:</strong> A large, high-resolution mock-up showing the logo on a business card and shop sign.</li>
</ul>

<h2>Worked Example: A Beginner's Portfolio</h2>
<p>Thandi has just completed this graphic design course. She has no paying clients yet, but she has class exercises and personal projects. Her portfolio has five pieces:</p>
<ol>
<li><strong>Brand Identity for Kalomo Poultry:</strong> Logo, business card, and brand kit. Shows colour theory and consistency.</li>
<li><strong>School Fundraising Poster:</strong> A3 poster with clear hierarchy and mobile money details. Shows event design skills.</li>
<li><strong>Social Media Package for a Hair Salon:</strong> Five Instagram posts with consistent colours and fonts. Shows social media expertise.</li>
<li><strong>Photo Retouch for a Market Vendor:</strong> Before-and-after of a dark product photo brightened in GIMP. Shows technical skill.</li>
<li><strong>Funeral Programme Template:</strong> A respectful four-page A5 design. Shows formal document skills.</li>
</ol>
<p>She creates a 12-page Canva PDF with each project on a full page, plus a cover page with her name, phone number, and WhatsApp contact. She sends it to three local businesses and receives her first enquiry within a week.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Gather your three best designs from this course.</li>
<li>Create two additional pieces for imaginary clients.</li>
<li>Choose one portfolio format—Canva PDF, Google Drive, or Facebook album.</li>
<li>Write a short description for each project explaining the client's need and your solution.</li>
<li>Add your contact details and a professional photo of yourself.</li>
<li>Share the portfolio with a friend and ask for honest feedback.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Portfolio:</strong> A curated collection of work that demonstrates a designer's skills and experience.</li>
<li><strong>Mock-up:</strong> A realistic presentation of a design placed on a real-world object, such as a business card or billboard.</li>
<li><strong>Case study:</strong> A detailed explanation of a project, including the problem, process, and result.</li>
<li><strong>Client brief:</strong> A document or conversation that outlines what a client needs from a design project.</li>
<li><strong>Testimonial:</strong> A positive statement from a client about your work, used to build trust with future clients.</li>
</ul>

<h2>Summary</h2>
<p>A strong portfolio is not about having dozens of clients. It is about showing that you can solve real design problems with skill and professionalism. By choosing your best work, writing clear project descriptions, and presenting everything in an accessible format, you create a tool that opens doors to paid opportunities in Zambia's growing creative economy.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool">Canva Design School</a> — creating portfolios and presenting design work.</li>
<li><a href="https://grow.google">Google Digital Garage</a> — building an online presence and marketing your skills.</li>
</ul>
HTML;
    }

    private function createModule5Quiz(Course $course): void
    {
        $quiz = Quiz::create([
            'course_id'           => $course->id,
            'lesson_id'           => null,
            'title'               => 'Module 5 Quiz: Print, Pricing, and Portfolio',
            'description'         => 'Test your understanding of print preparation, pricing strategies, and portfolio building.',
            'quiz_type'           => 'Graded',
            'time_limit_minutes'  => 20,
            'max_attempts'        => 3,
            'passing_score'       => 60.00,
            'show_correct_answers'=> true,
            'is_published'        => true,
        ]);

        $q1 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'Which colour mode is used by printers?','points'=>2,'explanation'=>'CMYK (Cyan, Magenta, Yellow, Black) is the standard ink-based colour mode for printing.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q1->question_id,'option_text'=>'RGB','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q1->question_id,'option_text'=>'CMYK','is_correct'=>true,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q1->question_id,'option_text'=>'HEX','is_correct'=>false,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q1->question_id,'option_text'=>'PPI','is_correct'=>false,'display_order'=>4]);

        $q2 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'What is the standard bleed size added around a print design?','points'=>2,'explanation'=>'Standard bleed is 3 mm on all sides to allow for trimming variations.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q2->question_id,'option_text'=>'1 mm','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q2->question_id,'option_text'=>'3 mm','is_correct'=>true,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q2->question_id,'option_text'=>'10 mm','is_correct'=>false,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q2->question_id,'option_text'=>'25 mm','is_correct'=>false,'display_order'=>4]);

        $q3 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'Which pricing method gives the client a clear total cost before work begins?','points'=>2,'explanation'=>'A project rate provides a fixed fee for the entire job, making budgeting predictable for the client.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q3->question_id,'option_text'=>'Hourly rate','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q3->question_id,'option_text'=>'Project rate','is_correct'=>true,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q3->question_id,'option_text'=>'Daily rate with overtime','is_correct'=>false,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q3->question_id,'option_text'=>'Cost-plus markup','is_correct'=>false,'display_order'=>4]);

        $q4 = Question::create(['question_type'=>'True/False','question_text'=>'It is common practice for designers to request a 50% deposit before starting a project.','points'=>2,'explanation'=>'A 50% deposit protects the designer from non-payment and shows the client is serious.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q4->question_id,'option_text'=>'True','is_correct'=>true,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q4->question_id,'option_text'=>'False','is_correct'=>false,'display_order'=>2]);

        $q5 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'What is the best number of projects to include in a beginner design portfolio?','points'=>2,'explanation'=>'Five strong, varied projects are more impressive than twenty weak or repetitive ones.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q5->question_id,'option_text'=>'Exactly one project','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q5->question_id,'option_text'=>'Five strong pieces','is_correct'=>true,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q5->question_id,'option_text'=>'Every design you have ever made','is_correct'=>false,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q5->question_id,'option_text'=>'At least fifty projects','is_correct'=>false,'display_order'=>4]);

        $q6 = Question::create(['question_type'=>'True/False','question_text'=>'RGB colours always print exactly as they appear on a computer screen.','points'=>2,'explanation'=>'RGB and CMYK have different colour ranges. Bright RGB colours often look duller when converted to CMYK for printing.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q6->question_id,'option_text'=>'True','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q6->question_id,'option_text'=>'False','is_correct'=>true,'display_order'=>2]);

        $q7 = Question::create(['question_type'=>'Multiple Choice','question_text'=>'What is scope creep in a design project?','points'=>2,'explanation'=>'Scope creep is when a client keeps adding new requests beyond the original agreed work.','correct_answer'=>null]);
        QuestionOption::create(['question_id'=>$q7->question_id,'option_text'=>'Using too many colours','is_correct'=>false,'display_order'=>1]);
        QuestionOption::create(['question_id'=>$q7->question_id,'option_text'=>'The gradual expansion of project requirements beyond the agreement','is_correct'=>true,'display_order'=>2]);
        QuestionOption::create(['question_id'=>$q7->question_id,'option_text'=>'A type of computer virus','is_correct'=>false,'display_order'=>3]);
        QuestionOption::create(['question_id'=>$q7->question_id,'option_text'=>'Printing beyond the bleed area','is_correct'=>false,'display_order'=>4]);

        $q8 = Question::create(['question_type'=>'Short Answer','question_text'=>'What do the letters CMYK stand for? (four words, separated by commas)','points'=>3,'explanation'=>'CMYK stands for Cyan, Magenta, Yellow, and Black—the four inks used in colour printing.','correct_answer'=>'cyan, magenta, yellow, black']);

        $q9 = Question::create(['question_type'=>'Short Answer','question_text'=>'What is the term for a realistic presentation of a design on a real object such as a T-shirt or billboard? (one word)','points'=>3,'explanation'=>'A mock-up shows how a design will look in a real-world context.','correct_answer'=>'mock-up']);

        DB::table('quiz_questions')->insert([
            ['quiz_id'=>$quiz->id,'question_id'=>$q1->question_id,'display_order'=>1,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q2->question_id,'display_order'=>2,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q3->question_id,'display_order'=>3,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q4->question_id,'display_order'=>4,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q5->question_id,'display_order'=>5,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q6->question_id,'display_order'=>6,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q7->question_id,'display_order'=>7,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q8->question_id,'display_order'=>8,'points_override'=>null],
            ['quiz_id'=>$quiz->id,'question_id'=>$q9->question_id,'display_order'=>9,'points_override'=>null],
        ]);
    }

    /* --------------------------------------------------------------------- */
    /*  Assignments                                                          */
    /* --------------------------------------------------------------------- */
    private function seedAssignments(Course $course): void
    {
        Assignment::create([
            'course_id'           => $course->id,
            'lesson_id'           => null,
            'title'               => 'Mid-Course Project: Community Event Design Package',
            'description'         => 'Design a complete visual package for a community event of your choice. This assignment tests your skills in poster design, flyer layout, and social media sizing using Canva.',
            'instructions'        => <<<'INST'
<p><strong>Objective:</strong> Create a consistent design package for a real or imaginary community event in Zambia.</p>

<p><strong>Event ideas (choose one):</strong></p>
<ul>
<li>A church crusade or fundraising braii</li>
<li>A school prize-giving day or sports event</li>
<li>A shop opening or promotion in your town</li>
<li>A community health screening day</li>
</ul>

<p><strong>Deliverables:</strong></p>
<ol>
<li><strong>A3 Poster:</strong> Designed to be read from a distance. Must include event name, date, time, venue, contact number, and one mobile money payment option (Airtel Money or MTN MoMo). Export as PDF Print.</li>
<li><strong>A5 Flyer:</strong> Designed to be handed out. Same information as the poster but reorganised for the smaller size. Export as PDF Print.</li>
<li><strong>Instagram Post:</strong> 1080 × 1080 pixels. A simplified version with bold headline and key details. Export as PNG.</li>
<li><strong>WhatsApp Status:</strong> 1080 × 1920 pixels. Portrait format with a strong call-to-action. Export as PNG.</li>
</ol>

<p><strong>Requirements:</strong></p>
<ul>
<li>All four items must use the same two or three colours and the same two fonts.</li>
<li>Apply the CRAP principles from Module 1.</li>
<li>Text must be readable on a phone screen.</li>
<li>Include a fake but realistic Zambian phone number starting with +260 or 09.</li>
</ul>

<p><strong>Submission:</strong></p>
<ol>
<li>Submit all four files in a single ZIP folder or as individual file uploads.</li>
<li>Include a short paragraph (50–100 words) explaining which CRAP principles you used and why you chose your colour palette.</li>
<li>Accepted formats: PDF, PNG, JPG. Maximum file size per file: 10 MB.</li>
</ol>
INST,
            'max_points'          => 100,
            'passing_points'      => 50,
            'allowed_file_types'  => 'pdf,png,jpg,jpeg',
            'max_file_size_mb'    => 10,
            'allow_late_submission'=> true,
            'due_date'            => null,
        ]);

        Assignment::create([
            'course_id'           => $course->id,
            'lesson_id'           => null,
            'title'               => 'Final Project: Brand Identity Package',
            'description'         => 'Create a complete brand identity for a real or imaginary local Zambian business. This assignment tests your ability to design logos, brand kits, and supporting materials.',
            'instructions'        => <<<'INST'
<p><strong>Objective:</strong> Develop a full brand identity package for a local business.</p>

<p><strong>Business ideas (choose one or invent your own):</strong></p>
<ul>
<li>A poultry farm in Kalomo</li>
<li>A mobile money agency</li>
<li>A tailoring and fashion design shop</li>
<li>A vegetable stall that delivers via WhatsApp</li>
<li>A hair salon or barbershop</li>
</ul>

<p><strong>Deliverables:</strong></p>
<ol>
<li><strong>Logo:</strong> A combination mark (text + icon) designed in Canva or GIMP. Show the colour version and a black-and-white version on one page. Export as PNG and PDF.</li>
<li><strong>One-Page Brand Kit:</strong> An A4 document showing your logo, two primary colours with hex codes, one heading font, one body font, and two example designs (business card and social media post). Export as PDF.</li>
<li><strong>Business Card Design:</strong> Front and back, including business name, person\'s name, phone number, and location. Export as PDF Print.</li>
<li><strong>Social Media Template:</strong> One Instagram Post (1080 × 1080) advertising a product or service using the brand colours and fonts. Export as PNG.</li>
</ol>

<p><strong>Requirements:</strong></p>
<ul>
<li>The logo must be simple enough to recognise when printed small on a business card.</li>
<li>All materials must use the same colours and fonts.</li>
<li>Include at least one Zambian contextual detail: prices in Kwacha, a local phone number format, or a reference to a town or market.</li>
<li>Explain your design choices in 100–150 words: why you chose the icon, colours, and fonts.</li>
</ul>

<p><strong>Submission:</strong></p>
<ol>
<li>Submit all files in a single ZIP folder or as individual uploads.</li>
<li>Include your written explanation as a PDF or Word document.</li>
<li>Accepted formats: PDF, PNG, JPG, DOC, DOCX. Maximum file size per file: 10 MB.</li>
</ol>
INST,
            'max_points'          => 100,
            'passing_points'      => 50,
            'allowed_file_types'  => 'pdf,png,jpg,jpeg,doc,docx',
            'max_file_size_mb'    => 10,
            'allow_late_submission'=> true,
            'due_date'            => null,
        ]);
    }
}
