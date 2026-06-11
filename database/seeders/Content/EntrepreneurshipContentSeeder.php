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

class EntrepreneurshipContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Entrepreneurship')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Entrepreneurship" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in Entrepreneurship already has modules. Skipping content seed.');
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
                'title' => 'Module 1: Finding and Testing Your Business Idea',
                'description' => 'Learn how to spot business opportunities in your community, test ideas cheaply, and write a simple business plan.',
            ],
            [
                'title' => 'Module 2: Registration and Compliance',
                'description' => 'Register your business with PACRA, get a ZRA TPIN, and understand council levies and trading licences.',
            ],
            [
                'title' => 'Module 3: Costing and Pricing in Kwacha',
                'description' => 'Calculate costs, set prices for profit, and apply real examples from chicken rearing, salons, and grocery trading.',
            ],
            [
                'title' => 'Module 4: Record Keeping and Mobile Money',
                'description' => 'Keep accurate records in an exercise book and Excel, and use mobile money safely for business transactions.',
            ],
            [
                'title' => 'Module 5: Marketing and Funding',
                'description' => 'Market your business on a small budget and explore funding options including chilimba, CEEC, and bank loans.',
            ],
            [
                'title' => 'Module 6: Growing and Sustaining Your Business',
                'description' => 'Hire your first helper, manage growth, and learn the common causes of business failure in Zambia.',
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
                'title' => '1.1 Spotting Business Opportunities in Your Community',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to identify at least three business opportunities in your own community, explain why a problem can be a source of income, and list the main factors that make a business idea worth pursuing in a Zambian context.</p>

<h2>Opportunities Are All Around You</h2>
<p>A business opportunity is simply a problem that people are willing to pay someone to solve. In Kalomo, in Lusaka's Soweto Market, or in a rural village near the Zambezi, opportunities exist everywhere. The woman who walks two kilometres to fetch water represents an opportunity for a borehole-drilling business. The farmer who loses chickens to disease represents an opportunity for affordable veterinary advice. The shopkeeper who runs out of change during busy hours represents an opportunity for a mobile money agent.</p>
<p>You do not need to invent something new. Most successful Zambian businesses copy an existing idea and do it better, cheaper, or closer to the customer. The key is to open your eyes, listen to complaints, and watch where people struggle or spend money.</p>

<h2>Where to Look for Ideas</h2>
<p>Start with your own daily life. What frustrates you? What takes too long? What costs too much? If you face a problem, chances are hundreds of other people face it too. Here are practical places to look:</p>
<ul>
<li><strong>Your market or bus station</strong> — What are people buying? What is missing? Are there long queues for something that could be provided faster?</li>
<li><strong>WhatsApp groups</strong> — PTA groups, church groups, and community groups often reveal needs. A parent asking where to buy uniforms is a signal. A farmer asking about fertiliser prices is a signal.</li>
<li><strong>Government and NGO programmes</strong> — When CEEC announces loans or when a new road is built, new opportunities appear. A new tarmac road might mean a tyre-repair business or a roadside restaurant.</li>
<li><strong>Your skills</strong> — Can you plait hair, bake cakes, fix phones, or sew uniforms? A skill that seems ordinary to you may be valuable to others.</li>
</ul>

<h2>The Three Tests of a Good Idea</h2>
<p>Not every idea should become a business. Before you spend a single Kwacha, test your idea against three questions:</p>
<ol>
<li><strong>Do people really want it?</strong> Talk to ten potential customers. If most say they would buy, you have demand. If they say "maybe," you do not have demand yet.</li>
<li><strong>Can I provide it at a price people will pay?</strong> A luxury car wash in a low-income area will struggle. A simple, fast car wash using buckets and soap might thrive.</li>
<li><strong>Can I do it better or differently than existing sellers?</strong> If five people already sell tomatoes on the same corner, you need a reason customers will choose you. Fresher stock? Lower prices? Home delivery?</li>
</ol>

<h2>Worked Example: Mrs Banda's Chicken Opportunity</h2>
<p>Mrs Banda lives near a school in Kalomo. Every day she hears parents complain that their children come home hungry because the school tuck-shop sells only sweets and biscuits. She asks herself the three questions:</p>
<ol>
<li>Do people want it? She asks fifteen parents. Twelve say they would pay for a healthy lunch box delivered to the school.</li>
<li>Can she provide it at a fair price? She calculates that rice, beans, and a piece of chicken cost K12 per plate. Parents say they would pay K20. She has a margin.</li>
<li>Can she do it differently? The tuck-shop opens only at break time. She offers pre-ordered lunch boxes delivered to the classroom at 12:30. No one else does this.</li>
</ol>
<p>Mrs Banda has found a real opportunity. She has not spent any money yet, but she has evidence.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Walk around your neighbourhood or market for thirty minutes. Write down three problems you see or hear people complain about.</li>
<li>For each problem, ask two strangers or neighbours: "Would you pay someone to solve this? How much?" Write their answers.</li>
<li>Pick the problem with the strongest positive response. Write one paragraph explaining why it passes the three tests.</li>
<li>Share your paragraph with a friend or family member. Do they agree it is a real opportunity?</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Business opportunity</strong> — a problem or need that people are willing to pay money to have solved.</li>
<li><strong>Demand</strong> — the desire and ability of customers to buy a product or service at a given price.</li>
<li><strong>Margin</strong> — the difference between what it costs you to make or buy something and what you sell it for.</li>
<li><strong>Target customer</strong> — the specific group of people most likely to buy from you.</li>
<li><strong>Competitive advantage</strong> — the reason customers choose you instead of someone else.</li>
</ul>

<h2>Summary</h2>
<p>Business opportunities exist everywhere in Zambia if you learn to see problems as potential income. Look at your community, listen to complaints, and test every idea against three questions: Is there real demand? Can I price it right? Can I do it differently? Mrs Banda's lunch-box idea shows that you do not need money to start looking; you need curiosity and the willingness to ask questions.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/">Google Digital Garage — Fundamentals of Digital Marketing</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-entrepreneurs">Khan Academy — Conversations with Entrepreneurs</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/introduction-to-data/">Microsoft Learn — Introduction to Data</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.2 Testing Your Idea Before You Spend Money',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to test a business idea using cheap, simple methods, explain why starting small reduces risk, and describe how to gather honest feedback from real potential customers.</p>

<h2>Why Test First?</h2>
<p>Many Zambian businesses fail because the owner spends all their savings on stock, rent, and equipment before discovering that no one wants to buy. Testing means finding out whether customers will actually pay before you commit serious money. It is cheaper to discover a bad idea early than to close a shop six months later with debts and unsold stock.</p>
<p>Testing is not complicated. It means offering your product or service to a small group of people, observing their reaction, and measuring whether they pay. If they pay, you expand. If they do not, you change the idea or try a different one.</p>

<h2>Five Cheap Ways to Test an Idea</h2>
<ol>
<li><strong>The Facebook or WhatsApp post</strong> — Post a photo of your product with a price in a local group. Count how many people ask for details. If no one comments, your price, product, or description needs work.</li>
<li><strong>The pop-up stall</strong> — Instead of renting a shop, sell from a table at a market for one Saturday. Your total cost might be K50 for stock and transport. If you sell out, you have proof of demand.</li>
<li><strong>The pre-order</strong> — Tell customers you will make ten items or prepare ten meals. Take orders and payment in advance. If you cannot get ten orders, you know the idea needs adjustment before you buy ingredients.</li>
<li><strong>The sample giveaway</strong> — Give a small free taste, haircut, or repair to five people. Ask them three questions: Would you buy this? How often? What would you pay? Honest answers are worth more than polite smiles.</li>
<li><strong>The competitor visit</strong> — Spend an hour watching a business similar to yours. Count customers. Note prices. Ask buyers why they chose that seller. This teaches you what works and what gaps you can fill.</li>
</ol>

<h2>Worked Example: Mr Tembo Tests a Barber Shop</h2>
<p>Mr Tembo wants to open a barber shop in Kalomo. He has K3,000 saved. Instead of renting a room and buying chairs immediately, he tests his idea over two weeks:</p>
<ol>
<li><strong>Week one:</strong> He posts before-and-after photos of haircuts he has done for friends on a local WhatsApp group. He offers home visits for K30. Three people book. He learns that customers like the convenience but complain that he is slow.</li>
<li><strong>Week two:</strong> He sets up a chair under a tree near the bus station on Saturday morning. He pays nothing for rent. He cuts hair for K25 and gives every customer a small card with his phone number. Twelve people sit in his chair. Eight say they would visit a proper shop if he had one.</li>
<li><strong>Decision:</strong> Mr Tembo now knows that demand exists, that K25-K30 is an acceptable price, and that speed matters. He also has a list of twenty phone numbers for marketing. He rents a small room for K400 per month, buys one used chair and clippers for K1,200, and keeps K1,400 for stock and emergencies.</li>
</ol>

<h2>Common Testing Mistakes</h2>
<ul>
<li><strong>Asking friends and family only</strong> — They will lie to encourage you. Ask strangers who have no reason to be kind.</li>
<li><strong>Giving things away for free without a plan</strong> — Free samples are useful only if you ask for feedback and a commitment to buy next time.</li>
<li><strong>Testing for one day and giving up</strong> — Weather, holidays, and pay-day cycles affect sales. Test on at least two different days.</li>
<li><strong>Ignoring negative feedback</strong> — If three people say your price is too high, they are probably right. Adjust before you scale up.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Pick one business idea from Lesson 1.1.</li>
<li>Choose the cheapest testing method from the list above. Write a one-day plan including what you will offer, where, and how much it will cost you.</li>
<li>Decide on three questions you will ask every person who tries your product or service.</li>
<li>Estimate how many sales or positive responses you need before you would feel confident spending K500 on the idea.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Proof of concept</strong> — a small, cheap demonstration that your idea can work in the real world.</li>
<li><strong>Minimum viable product</strong> — the simplest version of your product that you can sell or test with customers.</li>
<li><strong>Feedback</strong> — honest comments from customers about what they like, dislike, and would change.</li>
<li><strong>Pre-order</strong> — taking orders and sometimes payment before you make or buy the product.</li>
<li><strong>Scale up</strong> — to grow a business after testing shows that the idea works.</li>
</ul>

<h2>Summary</h2>
<p>Testing your idea before spending money is the difference between a smart entrepreneur and a gambler. Use cheap methods like social media posts, pop-up stalls, pre-orders, and free samples to gather real evidence. Ask strangers for honest feedback, test on multiple days, and only invest your savings when you have proof that people will pay. Mr Tembo's barber shop test shows that a weekend under a tree can teach you more than a month of daydreaming.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/">Google Digital Garage — Online Marketing</a></li>
<li><a href="https://www.khanacademy.org/economics-finance-domain/microeconomics">Khan Academy — Microeconomics</a></li>
<li><a href="https://www.w3schools.com/">W3Schools — General Learning Resources</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.3 Writing a Simple Business Plan',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to write a one-page business plan that describes your product, your customers, your costs, your prices, and your goals. You will also be able to explain why a simple plan is better than no plan at all.</p>

<h2>What Is a Business Plan?</h2>
<p>A business plan is a written description of what your business will do, who it will serve, how it will make money, and what you need to get started. You do not need fifty pages or complicated graphs. For a small Zambian business, a single typed page or two handwritten pages is enough. The purpose is to force you to think clearly and to show lenders or partners that you have a serious idea.</p>
<p>Many people avoid writing a plan because they think it is difficult. In reality, if you can answer seven simple questions, you can write a business plan. The questions are: What will I sell? Who will buy it? Why will they choose me? What will it cost to start? What will it cost to run each month? How much will I charge? What are my goals for the next year?</p>

<h2>The Seven Sections of a Simple Plan</h2>
<ol>
<li><strong>Business Description</strong> — Write two sentences saying what your business does and where it operates. Example: "Kalomo Fresh Eggs sells free-range eggs to households and small restaurants in Kalomo town. The business operates from my home in Mongu township and delivers by bicycle."</li>
<li><strong>Products or Services</strong> — List exactly what you sell. Be specific. Instead of "food," write "plain chips with tomato sauce, K10 per plate, served from 16:00 to 20:00 near the bus station."</li>
<li><strong>Target Customers</strong> — Describe the people most likely to buy. Include age, location, income level, and habits. Example: "Primary customers are working mothers aged 25-40 who live within 2 km of the market and want fresh eggs for breakfast."</li>
<li><strong>Marketing Plan</strong> — Explain how customers will find out about you. Will you use WhatsApp status updates? Posters at the market? Word of mouth? A free sample day?</li>
<li><strong>Operations Plan</strong> — Describe where you will work, what hours you will keep, and what equipment you need. Will you need electricity? Water? A fridge? Transport?</li>
<li><strong>Financial Plan</strong> — List your start-up costs, monthly running costs, selling price, and estimated monthly sales. This section is so important that Module 3 covers it in detail. For now, estimate honestly.</li>
<li><strong>Goals</strong> — Write three targets for the next twelve months. Example: "Sell 50 eggs per day by month three. Hire one helper by month six. Increase profit to K2,000 per month by month twelve."</li>
</ol>

<h2>Worked Example: A One-Page Plan for a Salon</h2>
<p>Grace wants to open a small hair salon in her home. Her one-page plan looks like this:</p>
<ul>
<li><strong>Business:</strong> Grace's Corner Salon, located at Plot 45, Kalomo.</li>
<li><strong>Products:</strong> Plaiting (K50-K150), retouch (K80), wash and set (K60). Open Tuesday to Saturday, 08:00 to 18:00.</li>
<li><strong>Customers:</strong> Women aged 18-45 living within walking distance. Target: ten customers per week to start.</li>
<li><strong>Marketing:</strong> WhatsApp status photos, word of mouth, loyalty card: every sixth style is half price.</li>
<li><strong>Operations:</strong> Uses one room at home. Needs two styling chairs, a mirror, a hair dryer, and running water.</li>
<li><strong>Start-up costs:</strong> K2,500 for chairs, mirror, dryer, and initial stock of chemicals and attachments.</li>
<li><strong>Monthly costs:</strong> K300 for electricity and water, K400 for stock, K200 for transport to Lusaka for supplies.</li>
<li><strong>Prices and profit:</strong> Average sale K80. Ten customers per week = K3,200 per month. After costs of K900, estimated profit is K2,300 per month.</li>
<li><strong>Goals:</strong> Ten regular customers by month two. Fifteen customers by month six. Save K5,000 for a rented shop by month twelve.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Open a text editor or take a clean exercise book page.</li>
<li>Write a one-page business plan for your chosen idea using the seven sections above.</li>
<li>Ask a friend or family member to read it. Can they explain your business back to you in their own words? If not, rewrite the unclear parts.</li>
<li>Estimate your start-up costs and monthly running costs as honestly as you can. Do not guess; phone a supplier or visit a shop to check real prices.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Business plan</strong> — a written document that explains what a business will do, who it will serve, and how it will make money.</li>
<li><strong>Start-up costs</strong> — the one-time expenses needed to open a business, such as equipment, stock, and registration fees.</li>
<li><strong>Running costs</strong> — the regular monthly expenses such as rent, electricity, transport, and replacement stock.</li>
<li><strong>Target market</strong> — the specific group of customers a business aims to serve.</li>
<li><strong>Operations</strong> — the day-to-day activities needed to run a business, including hours, location, and processes.</li>
</ul>

<h2>Summary</h2>
<p>A simple business plan forces you to think clearly about your idea before you spend money. Answer seven questions: what you sell, who buys it, why they choose you, how you market, how you operate, what it costs, and what your goals are. Grace's salon plan shows that one page is enough if the thinking behind it is honest. Show your plan to someone you trust, check real prices instead of guessing, and treat the plan as a living document that you update as you learn.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-entrepreneurs">Khan Academy — Conversations with Entrepreneurs</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/introduction-to-data/">Microsoft Learn — Data Fundamentals</a></li>
<li><a href="https://www.libreoffice.org/get-help/">LibreOffice — Getting Started with Writer and Calc</a></li>
</ul>
HTML,
            ],
        ];
    }


    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: Finding and Testing Your Business Idea',
            'description' => 'Test your understanding of spotting opportunities, testing ideas cheaply, and writing a simple business plan.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is the best definition of a business opportunity?',
                    'explanation' => 'A business opportunity exists when people have a problem or need and are willing to pay for a solution.',
                    'options' => [
                        ['text' => 'A problem that people are willing to pay someone to solve', 'is_correct' => true],
                        ['text' => 'A brand-new invention no one has seen before', 'is_correct' => false],
                        ['text' => 'Any idea that makes the owner happy', 'is_correct' => false],
                        ['text' => 'A shop located in the centre of town', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Mrs Banda identified a lunch-box opportunity near a school. What was her competitive advantage?',
                    'explanation' => 'Her competitive advantage was delivering pre-ordered healthy meals to classrooms at a specific time, which the tuck-shop did not offer.',
                    'options' => [
                        ['text' => 'She had the cheapest prices in town', 'is_correct' => false],
                        ['text' => 'She offered pre-ordered lunch boxes delivered to classrooms', 'is_correct' => true],
                        ['text' => 'She owned the school building', 'is_correct' => false],
                        ['text' => 'She only sold to teachers', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is it important to test a business idea before spending a lot of money?',
                    'explanation' => 'Testing reduces risk by revealing whether real customers will actually pay before you commit savings and take on debt.',
                    'options' => [
                        ['text' => 'It guarantees the government will give you a loan', 'is_correct' => false],
                        ['text' => 'It proves you are smarter than competitors', 'is_correct' => false],
                        ['text' => 'It shows whether customers will pay before you spend savings', 'is_correct' => true],
                        ['text' => 'It allows you to avoid paying tax', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A pop-up stall at a market for one Saturday is a cheap way to test demand for a product.',
                    'explanation' => 'A pop-up stall is a low-cost method to observe real customer behaviour and measure sales before committing to rent and stock.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'You should only ask friends and family for feedback because they know you best.',
                    'explanation' => 'Friends and family often give encouraging but dishonest feedback. Strangers provide more honest opinions about your product.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which section of a simple business plan describes how customers will find out about your business?',
                    'explanation' => 'The marketing plan explains how you will attract customers through channels like WhatsApp, posters, word of mouth, or samples.',
                    'options' => [
                        ['text' => 'Financial Plan', 'is_correct' => false],
                        ['text' => 'Operations Plan', 'is_correct' => false],
                        ['text' => 'Marketing Plan', 'is_correct' => true],
                        ['text' => 'Target Customers', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the name for the simplest version of a product that you can sell or test with customers? (two words)',
                    'explanation' => 'A minimum viable product is the most basic form of a product that allows you to test demand with real customers.',
                    'correct_answer' => 'Minimum Viable Product',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In Grace\'s salon business plan, what was her estimated monthly profit after costs?',
                    'explanation' => 'Grace estimated K3,200 monthly income minus K900 monthly costs, giving a profit of K2,300 per month.',
                    'options' => [
                        ['text' => 'K900', 'is_correct' => false],
                        ['text' => 'K2,300', 'is_correct' => true],
                        ['text' => 'K3,200', 'is_correct' => false],
                        ['text' => 'K5,000', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }


    private function module2Lessons(): array
    {
        return [
            [
                'title' => '2.1 Registering Your Business with PACRA',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain why business registration matters, list the different business structures available in Zambia, and describe the basic steps for registering a business with the Patents and Companies Registration Agency.</p>

<h2>Why Register Your Business?</h2>
<p>Many small businesses in Zambia operate informally. The owner sells tomatoes, braids hair, or repairs phones without ever visiting a government office. While this avoids paperwork in the short term, it creates serious problems later. An unregistered business cannot open a corporate bank account, cannot apply for a CEEC loan, cannot sign formal contracts with large buyers, and may be shut down by the council for lack of a trading licence.</p>
<p>Registration gives your business a legal identity separate from you as a person. If the business owes money, your personal house and belongings are usually protected. Registration also builds trust. Customers, suppliers, and lenders prefer to deal with a business that has a certificate on the wall.</p>

<h2>Types of Business Structures in Zambia</h2>
<p>Before you register, you must choose a structure. The three most common for small Zambian businesses are:</p>
<ul>
<li><strong>Sole Proprietorship</strong> — One person owns everything. It is the simplest and cheapest to register. You are personally responsible for all debts. This is ideal for a market stall, a small salon, or a one-person repair service.</li>
<li><strong>Partnership</strong> — Two or more people own the business together. A partnership agreement should be written down to avoid future fights about money and responsibilities. Suitable for a small farm or a shop run by siblings.</li>
<li><strong>Private Limited Company</strong> — The business is a separate legal person. Ownership is divided into shares. Directors manage the company. This costs more to register and requires annual returns, but it protects personal assets and looks professional to banks and large clients. Best for a growing business with employees and formal contracts.</li>
</ul>

<h2>How to Register with PACRA</h2>
<p>PACRA, the Patents and Companies Registration Agency, is the government body that records all businesses in Zambia. You can visit a PACRA office in person or use their online portal. The basic steps are:</p>
<ol>
<li><strong>Choose and reserve a name</strong> — Think of three possible names in case your first choice is already taken. The name should describe what you do and be easy to remember. PACRA will check that no one else is using it.</li>
<li><strong>Decide on your structure</strong> — Sole proprietorship, partnership, or private limited company.</li>
<li><strong>Complete the application form</strong> — Provide your NRC number, physical address, description of the business, and names of any partners or directors.</li>
<li><strong>Pay the registration fee</strong> — Fees change over time, so check the current amount on the PACRA website or at the office. Keep your receipt.</li>
<li><strong>Receive your certificate</strong> — Once approved, PACRA issues a certificate of registration. Make several photocopies and keep the original in a safe place.</li>
</ol>

<h2>Worked Example: Mr Chanda Registers a Hardware Shop</h2>
<p>Mr Chanda wants to open "Chanda's Hardware" in Kalomo. He chooses a sole proprietorship because he is the only owner and wants to keep costs low. He visits the PACRA office with his NRC and three name choices. His first choice is available. He fills out the form, pays the fee, and receives his certificate after five working days. He immediately makes three photocopies: one for his shop wall, one for his bank, and one for the council when he applies for a trading licence.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write down three possible names for a business you might start. Search online or ask at PACRA whether any of them are already taken.</li>
<li>List the pros and cons of a sole proprietorship versus a private limited company for your specific idea.</li>
<li>Draw a simple flowchart showing the five steps from choosing a name to receiving your PACRA certificate.</li>
<li>Phone or visit your local council office and ask what documents they require alongside a PACRA certificate to issue a trading licence.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>PACRA</strong> — the Patents and Companies Registration Agency, the Zambian government body that registers businesses.</li>
<li><strong>Sole proprietorship</strong> — a business owned and run by one person who is personally responsible for its debts.</li>
<li><strong>Private limited company</strong> — a business that is a separate legal entity from its owners, offering protection for personal assets.</li>
<li><strong>Certificate of registration</strong> — the official document from PACRA that proves your business legally exists.</li>
<li><strong>Partnership agreement</strong> — a written contract between partners describing who does what and how profits are shared.</li>
</ul>

<h2>Summary</h2>
<p>Registering your business with PACRA gives it legal standing, protects your personal assets, and builds trust with customers and lenders. Choose the right structure for your size and ambitions: sole proprietorship for simple one-person businesses, partnership for shared ownership, or a private limited company for growth and formal contracts. Follow the five steps carefully, keep your certificate safe, and remember that registration is the foundation upon which all other compliance is built.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.pacra.org.zm/">PACRA Official Website</a></li>
<li><a href="https://www.zra.org.zm/">Zambia Revenue Authority</a></li>
<li><a href="https://www.w3schools.com/">W3Schools — General Learning Resources</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.2 Getting Your ZRA TPIN and Understanding Tax',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what a ZRA TPIN is, describe how to apply for one, list the main taxes that affect small businesses in Zambia, and understand the basics of keeping records for tax purposes.</p>

<h2>What Is a TPIN?</h2>
<p>TPIN stands for Taxpayer Identification Number. It is a unique ten-digit number issued by the Zambia Revenue Authority, or ZRA. Every registered business in Zambia must have a TPIN. Without one, you cannot file tax returns, import goods, or claim tax refunds. Banks often ask for a TPIN when opening a business account. Large customers may refuse to pay you if you cannot provide a tax invoice with a TPIN on it.</p>
<p>The TPIN is free to obtain, but once you have it, ZRA expects you to file returns on time even if your business made no money that month. Failing to file can lead to penalties and interest charges.</p>

<h2>How to Get a TPIN</h2>
<p>You can apply for a TPIN online through the ZRA e-services portal or in person at a ZRA office. The steps are:</p>
<ol>
<li><strong>Gather your documents</strong> — You need your NRC, your PACRA certificate, a passport photo, and proof of your business address such as a utility bill or a letter from your chief.</li>
<li><strong>Complete the application</strong> — The form asks for your business name, structure, address, description of activities, and estimated monthly turnover.</li>
<li><strong>Submit and wait</strong> — Online applications are usually processed within a few days. In-person applications may take one to two weeks.</li>
<li><strong>Receive your TPIN certificate</strong> — ZRA will issue a certificate showing your TPIN, tax type, and filing frequency. Keep this safe.</li>
</ol>

<h2>Taxes That Affect Small Businesses</h2>
<p>Not every tax applies to every business, but you should know the main ones:</p>
<ul>
<li><strong>Income Tax</strong> — Tax on the profit your business makes. Small businesses with low turnover may qualify for a presumptive tax, which is a simplified fixed amount based on turnover rather than a complicated profit calculation.</li>
<li><strong>Value Added Tax (VAT)</strong> — A tax on goods and services. Only businesses with annual turnover above the VAT threshold must register for VAT. If you are below the threshold, you do not charge VAT.</li>
<li><strong>Turnover Tax</strong> — Some small businesses pay a simple percentage of total sales instead of income tax. Check with ZRA whether this applies to you.</li>
<li><strong>Pay As You Earn (PAYE)</strong> — If you hire employees and pay them wages, you must deduct income tax from their salaries and send it to ZRA every month.</li>
<li><strong>Property rates</strong> — Charged by the local council based on the value of your business premises. This is not collected by ZRA, but it is still a tax you must pay.</li>
</ul>

<h2>Record Keeping for Tax</h2>
<p>ZRA can ask to see your business records at any time. Good records protect you from penalties and help you pay only the tax you actually owe. Keep the following:</p>
<ol>
<li>Copies of all sales receipts or invoices.</li>
<li>Records of all business purchases and expenses.</li>
<li>A simple monthly summary of money in and money out.</li>
<li>Bank statements for your business account.</li>
<li>Your TPIN certificate and all ZRA correspondence.</li>
</ol>
<p>We cover record keeping in detail in Module 4, but the most important habit is to write down every transaction on the day it happens. Do not rely on memory.</p>

<h2>Worked Example: Ms Mutale Learns About Presumptive Tax</h2>
<p>Ms Mutale sells second-hand clothes at Soweto Market. Her monthly sales average K4,000. She visits ZRA and learns that because her turnover is below the VAT threshold and she has no employees, she qualifies for presumptive tax. Instead of calculating complicated profits and losses, she pays a flat K200 per quarter. She files a simple return online every three months. This saves her the cost of hiring an accountant and keeps her compliant with the law.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Visit the ZRA website or a ZRA office and ask for the current presumptive tax rates for your type of business.</li>
<li>List the documents you would need to gather before applying for a TPIN. Check which ones you already have and which you still need.</li>
<li>Calculate whether your estimated annual turnover is above or below the current VAT threshold.</li>
<li>Ask two registered business owners in your area how often they file tax returns and whether they use an accountant or do it themselves.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>TPIN</strong> — Taxpayer Identification Number, a unique number issued by ZRA to every registered taxpayer.</li>
<li><strong>ZRA</strong> — Zambia Revenue Authority, the government agency responsible for collecting taxes.</li>
<li><strong>Presumptive tax</strong> — a simplified tax for small businesses based on turnover rather than detailed profit calculations.</li>
<li><strong>VAT</strong> — Value Added Tax, a consumption tax charged on goods and services above a certain turnover threshold.</li>
<li><strong>Turnover</strong> — the total amount of money received from sales before any expenses are deducted.</li>
</ul>

<h2>Summary</h2>
<p>A ZRA TPIN is essential for any serious business in Zambia. It is free to obtain, but it comes with the responsibility of filing returns on time. Understand which taxes apply to you: income tax, presumptive tax, VAT, PAYE, or property rates. Keep simple daily records so that when ZRA asks questions, you have answers. Ms Mutale's story shows that small businesses can stay compliant without expensive accountants by using presumptive tax and filing quarterly.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.zra.org.zm/">Zambia Revenue Authority Official Website</a></li>
<li><a href="https://www.pacra.org.zm/">PACRA Official Website</a></li>
<li><a href="https://www.libreoffice.org/get-help/">LibreOffice — Record Keeping Spreadsheets</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.3 Council Levies, Trading Licences, and Other Permits',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what a trading licence is, describe how to apply for one at your local council, list common permits required for specific businesses, and understand the consequences of operating without proper documentation.</p>

<h2>What Is a Trading Licence?</h2>
<p>A trading licence is permission from your local council to run a business within its area. Every district council in Zambia has the power to issue these licences and to charge levies for using public space, advertising, or operating a market stall. The licence is usually valid for one year and must be renewed before it expires.</p>
<p>Operating without a trading licence is risky. Council inspectors can confiscate your goods, fine you, or close your stall. In some towns, unlicensed businesses are barred from applying for council tenders or participating in market allocations.</p>

<h2>How to Apply for a Trading Licence</h2>
<p>The exact process varies from council to council, but the general steps are similar across Zambia:</p>
<ol>
<li><strong>Visit the council offices</strong> — Go to the revenue or licensing department. Ask for a trading licence application form for your type of business.</li>
<li><strong>Complete the form</strong> — You will need your NRC, your PACRA certificate, your TPIN certificate, proof of your business address, and sometimes a sketch of your shop layout.</li>
<li><strong>Pay the licence fee</strong> — Fees depend on your business type and size. A small shop pays less than a large factory. Some councils charge a flat rate; others calculate fees based on turnover or floor area.</li>
<li><strong>Receive your licence</strong> — Once approved, you get a printed licence card or certificate. Display it where customers and inspectors can see it.</li>
<li><strong>Renew annually</strong> — Mark your calendar. Late renewal often attracts penalties.</li>
</ol>

<h2>Other Permits You May Need</h2>
<p>Depending on what you sell or do, you may need additional permits:</p>
<ul>
<li><strong>Food handling licence</strong> — Required for restaurants, bakeries, butcheries, and anyone who prepares food for sale. A health inspector will visit your premises to check cleanliness, water supply, and waste disposal.</li>
<li><strong>Fire certificate</strong> — Required for businesses that use gas, flammable materials, or operate in multi-storey buildings. The fire brigade inspects your extinguishers and exits.</li>
<li><strong>Environmental clearance</strong> — Required for businesses that generate significant waste, noise, or pollution. A hairdresser using chemicals might need this.</li>
<li><strong>Signage permit</strong> — Some councils charge a separate fee for hanging a business sign above your door or on the street.</li>
<li><strong>Market stall permit</strong> — If you trade in a council-run market, you need a stall allocation permit in addition to your trading licence.</li>
</ul>

<h2>Worked Example: The Bakery Permit Checklist</h2>
<p>Mrs Ngoma wants to open a small bakery in Mongu. Before she bakes her first loaf, she completes the following checklist:</p>
<ol>
<li>PACRA registration — done. She is a sole proprietor.</li>
<li>ZRA TPIN — obtained and displayed.</li>
<li>Trading licence — applied for at the Mongu Council and paid K350 for the year.</li>
<li>Food handling licence — a health inspector visits her kitchen, checks that she has running water, a covered rubbish bin, and a hand-washing station. He approves her and issues the licence.</li>
<li>Signage permit — she pays K50 to hang a wooden sign above her door.</li>
<li>Fire certificate — because she uses a gas oven, the fire brigade checks her gas cylinder storage and issues a certificate.</li>
</ol>
<p>Total cost before baking: approximately K1,200 in fees and certificates. Mrs Ngoma knows that operating without these documents could cost her far more in fines and lost reputation.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Visit your local council office and ask for a list of all licences and permits required for your chosen business type.</li>
<li>Ask for the current fee schedule. Write down the costs for a trading licence, any special permits, and annual renewal fees.</li>
<li>Draw a checklist of every document and permit you would need before opening your business.</li>
<li>Ask a registered business owner in your area whether council inspectors visit regularly and what happens to unlicensed traders.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Trading licence</strong> — permission from the local council to operate a business within a specific area.</li>
<li><strong>Council levy</strong> — a fee charged by the local authority for services such as refuse collection, street lighting, or market maintenance.</li>
<li><strong>Food handling licence</strong> — a permit proving that a food business meets health and hygiene standards.</li>
<li><strong>Signage permit</strong> — permission to display a business sign in a public or semi-public space.</li>
<li><strong>Renewal</strong> — the process of extending a licence or permit for another year by paying the required fee.</li>
</ul>

<h2>Summary</h2>
<p>A trading licence is not optional; it is the legal permission you need to operate in your district. Apply at your local council with your PACRA certificate, TPIN, NRC, and proof of address. Check whether you need extra permits for food handling, fire safety, signage, or environmental compliance. Mrs Ngoma's bakery checklist shows that preparation costs money, but operating without permits costs far more in fines, confiscations, and lost trust. Keep your licences visible and renew them on time.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.zra.org.zm/">Zambia Revenue Authority</a></li>
<li><a href="https://www.pacra.org.zm/">PACRA Official Website</a></li>
<li><a href="https://www.w3schools.com/">W3Schools — General Learning Resources</a></li>
</ul>
HTML,
            ],
        ];
    }


    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Registration and Compliance',
            'description' => 'Test your knowledge of PACRA registration, ZRA TPIN, taxes, and local council licences.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which government agency in Zambia registers businesses and issues certificates of registration?',
                    'explanation' => 'PACRA, the Patents and Companies Registration Agency, is responsible for business registration in Zambia.',
                    'options' => [
                        ['text' => 'Zambia Revenue Authority (ZRA)', 'is_correct' => false],
                        ['text' => 'Patents and Companies Registration Agency (PACRA)', 'is_correct' => true],
                        ['text' => 'Ministry of Education', 'is_correct' => false],
                        ['text' => 'Bank of Zambia', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main advantage of a private limited company over a sole proprietorship?',
                    'explanation' => 'A private limited company is a separate legal entity, which generally protects the owners\' personal assets from business debts.',
                    'options' => [
                        ['text' => 'It costs less to register', 'is_correct' => false],
                        ['text' => 'It protects personal assets from business debts', 'is_correct' => true],
                        ['text' => 'It does not need a TPIN', 'is_correct' => false],
                        ['text' => 'It never pays tax', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A TPIN is required before a business can file tax returns with ZRA.',
                    'explanation' => 'The Taxpayer Identification Number is mandatory for filing tax returns, opening corporate bank accounts, and issuing tax invoices.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Presumptive tax is only for large companies with many employees.',
                    'explanation' => 'Presumptive tax is designed for small businesses with low turnover to simplify tax compliance.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following documents is typically required when applying for a trading licence at your local council?',
                    'explanation' => 'Councils usually require your PACRA certificate, TPIN, NRC, and proof of address to issue a trading licence.',
                    'options' => [
                        ['text' => 'A university degree certificate', 'is_correct' => false],
                        ['text' => 'Your PACRA certificate and TPIN', 'is_correct' => true],
                        ['text' => 'A birth certificate for all employees', 'is_correct' => false],
                        ['text' => 'A bank statement showing K50,000', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'A bakery in Zambia would typically need which extra permit in addition to a trading licence?',
                    'explanation' => 'Food businesses must obtain a food handling licence after passing a health inspection.',
                    'options' => [
                        ['text' => 'A driving licence', 'is_correct' => false],
                        ['text' => 'A food handling licence', 'is_correct' => true],
                        ['text' => 'A fishing permit', 'is_correct' => false],
                        ['text' => 'An import licence', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What do the letters TPIN stand for? (four words)',
                    'explanation' => 'TPIN stands for Taxpayer Identification Number, issued by the Zambia Revenue Authority.',
                    'correct_answer' => 'Taxpayer Identification Number',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What happens if you operate a business without a valid trading licence?',
                    'explanation' => 'Council inspectors can confiscate goods, issue fines, or close an unlicensed business.',
                    'options' => [
                        ['text' => 'Nothing, if the business is small', 'is_correct' => false],
                        ['text' => 'Council inspectors may confiscate goods, fine you, or close the business', 'is_correct' => true],
                        ['text' => 'You automatically receive a free licence after six months', 'is_correct' => false],
                        ['text' => 'You are only allowed to operate on weekends', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }


    private function module3Lessons(): array
    {
        return [
            [
                'title' => '3.1 Knowing Your Costs: Materials, Labour, and Overheads',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to identify the three main types of business costs, calculate the total cost of producing a product or service, and explain why knowing your costs is essential before you set a price.</p>

<h2>Why Costs Matter</h2>
<p>Many small businesses in Zambia fail because the owner sets prices based on what competitors charge or what feels fair, without knowing whether the price actually covers all costs. If you sell a plate of chips for K15 but it costs you K16 to make, you lose money on every sale. You might feel busy and successful, but your business is dying slowly.</p>
<p>Knowing your costs allows you to set prices that generate profit, negotiate confidently with suppliers, and decide whether an order is worth accepting. It also helps you spot waste. If your electricity bill is higher than expected, accurate cost records will show you the problem.</p>

<h2>The Three Types of Costs</h2>
<p>Every business has three main types of costs:</p>
<ol>
<li><strong>Materials or stock costs</strong> — The raw materials or goods you buy to create or resell your product. For a chicken rearer, this includes chicks, feed, and vaccines. For a salon, this includes hair chemicals, attachments, and towels. For a grocery shop, this is the cost of the goods on your shelves.</li>
<li><strong>Labour costs</strong> — Wages you pay to workers, including your own time if you could have been paid elsewhere. If you spend eight hours plaiting hair, your labour cost is what you could have earned doing something else with those eight hours. When you hire someone, their wage is a direct labour cost.</li>
<li><strong>Overheads</strong> — Costs that do not change much whether you sell one item or one hundred. Rent, electricity, water, transport, phone airtime, and licence fees are overheads. These must be paid even on days when no customer walks through the door.</li>
</ol>

<h2>Calculating Total Cost Per Unit</h2>
<p>To price correctly, you must know the cost of producing one unit of whatever you sell. A "unit" might be one plate of food, one haircut, one dressed chicken, or one hour of labour. The formula is simple:</p>
<p><strong>Total cost per unit = Materials per unit + Labour per unit + Overhead per unit</strong></p>
<p>To find the overhead per unit, add up all your monthly overheads and divide by the number of units you expect to sell in a month. For example, if your monthly overheads are K1,500 and you expect to sell 300 plates of chips, your overhead per plate is K5.</p>

<h2>Worked Example: Costing a Plate of Chips</h2>
<p>Mr Banda runs a small chips stall near the bus station. He wants to know his true cost per plate:</p>
<ul>
<li><strong>Materials:</strong> Potatoes K4, cooking oil K2, salt and tomato sauce K1. Total materials = K7 per plate.</li>
<li><strong>Labour:</strong> He pays a helper K600 per month. They sell about 200 plates per month. Labour per plate = K3.</li>
<li><strong>Overheads:</strong> Stall rent K400, electricity for the fryer K300, transport to buy stock K200, trading licence K100 per month. Total overheads = K1,000 per month. Overhead per plate = K1,000 / 200 = K5.</li>
</ul>
<p><strong>Total cost per plate = K7 + K3 + K5 = K15.</strong></p>
<p>Mr Banda now knows that if he charges K15, he breaks even. If he charges K18, he makes K3 profit per plate. If a customer offers to buy fifty plates at K12 each, he knows that accepting would lose him money.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a simple product or service you might sell.</li>
<li>List every material or stock item needed for one unit. Phone a shop or visit a market to find real prices.</li>
<li>Estimate your monthly overheads: rent, electricity, water, transport, licence fees, and phone airtime.</li>
<li>Guess how many units you can realistically sell in one month. Divide total overheads by that number to get overhead per unit.</li>
<li>Add materials, labour, and overhead per unit. This is your true cost. Write it down and keep it safe.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Materials cost</strong> — the cost of raw materials or goods used to produce a product or service.</li>
<li><strong>Labour cost</strong> — wages paid to workers, including the value of the owner's own time.</li>
<li><strong>Overheads</strong> — fixed business costs such as rent, electricity, and licences that do not vary directly with sales volume.</li>
<li><strong>Cost per unit</strong> — the total cost to produce one item or deliver one service.</li>
<li><strong>Break-even</strong> — the point at which total sales income equals total costs, resulting in zero profit and zero loss.</li>
</ul>

<h2>Summary</h2>
<p>Setting prices without knowing your costs is like driving at night without headlights. Every business has materials costs, labour costs, and overheads. Calculate your true cost per unit by adding all three and dividing overheads by expected monthly sales. Mr Banda's chips example shows that a price that looks profitable may actually be a loss. Take the time to visit shops, check real prices, and write down every cost before you open for business.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/economics-finance-domain/microeconomics">Khan Academy — Microeconomics</a></li>
<li><a href="https://www.libreoffice.org/get-help/">LibreOffice — Calc Spreadsheets</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/introduction-to-data/">Microsoft Learn — Data Fundamentals</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.2 Pricing for Profit: Chicken Rearing and Salon Examples',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to calculate a selling price that covers costs and generates profit, explain the difference between mark-up and margin, and apply pricing strategies to real Zambian businesses such as chicken rearing and hair salons.</p>

<h2>Profit Is Not Optional</h2>
<p>Profit is the money left over after you have paid all your costs. Without profit, your business cannot grow, cannot survive unexpected problems, and cannot reward you for the risk and effort you put in. A business that breaks even every month is only one bad week away from closing. A business that loses money is borrowing from its own future.</p>
<p>Pricing is the most important decision you make as a business owner. It is also one of the hardest. Price too high and customers walk away. Price too low and you work hard for nothing. The right price covers all costs, leaves a healthy profit, and feels fair to the customer.</p>

<h2>Mark-Up versus Margin</h2>
<p>These two words are often confused, but they mean different things:</p>
<ul>
<li><strong>Mark-up</strong> is the percentage you add to your cost price to arrive at your selling price. If something costs K50 and you sell it for K75, your mark-up is K25, which is 50% of the cost.</li>
<li><strong>Margin</strong> is the profit expressed as a percentage of the selling price. In the same example, the margin is K25 divided by K75, which is 33%.</li>
</ul>
<p>Most small businesses in Zambia think in terms of mark-up because it is easier to calculate. However, banks and investors usually talk about margin. Learn both.</p>

<h2>Worked Example: Chicken Rearing Pricing</h2>
<p>Mrs Zulu raises broiler chickens to sell dressed birds to households in Kalomo. Here is her costing for one batch of fifty birds:</p>
<ul>
<li>Day-old chicks: K15 each × 50 = K750</li>
<li>Feed for six weeks: K2,500</li>
<li>Vaccines and charcoal: K400</li>
<li>Labour: K600</li>
<li>Electricity and water: K300</li>
<li>Transport to market: K200</li>
</ul>
<p><strong>Total cost for 50 birds = K4,750.</strong></p>
<p>She expects to sell 45 birds after losses. Cost per bird = K4,750 / 45 = K105.</p>
<p>She wants a 40% mark-up. Selling price = K105 + (K105 × 0.40) = K105 + K42 = <strong>K147 per bird</strong>. She rounds this to K150 for easy change.</p>
<p>Her profit per bird = K150 - K105 = K45. Total profit for 45 birds = K2,025. She repeats this every six weeks, giving her about K13,500 profit over six months if all batches succeed.</p>

<h2>Worked Example: Salon Pricing</h2>
<p>Grace from Module 1 wants to set prices for her salon. She calculates her monthly costs:</p>
<ul>
<li>Rent: K400</li>
<li>Electricity and water: K300</li>
<li>Stock: K600</li>
<li>Helper wage: K800</li>
<li>Transport to Lusaka for supplies: K200</li>
<li>Total monthly overheads and stock = K2,300</li>
</ul>
<p>She plans to serve forty customers per month. Overhead and stock per customer = K2,300 / 40 = K57.50.</p>
<p>She decides on the following prices, each including a profit margin:</p>
<ul>
<li>Plaiting: K80 (cost K57.50, profit K22.50)</li>
<li>Retouch: K100 (cost K60, profit K40)</li>
<li>Wash and set: K70 (cost K50, profit K20)</li>
</ul>
<p>If she averages K80 per customer and serves forty customers, her monthly income is K3,200. After costs of K2,300, her profit is K900. This is lower than her original estimate, so she knows she must either increase customers to fifty or raise prices slightly.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Using the cost per unit you calculated in Lesson 3.1, add a 30% mark-up. What is your selling price?</li>
<li>Calculate your profit margin as a percentage of that selling price.</li>
<li>Ask three potential customers whether they would pay your proposed price. If all three say yes, consider raising it. If two say no, reconsider your costs or your target market.</li>
<li>Write a simple price list for your business with at least three products or services. Next to each price, write the estimated profit.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Profit</strong> — the money remaining after all business costs have been paid.</li>
<li><strong>Mark-up</strong> — the percentage added to the cost price to determine the selling price.</li>
<li><strong>Margin</strong> — the profit expressed as a percentage of the selling price.</li>
<li><strong>Selling price</strong> — the amount a customer pays for a product or service.</li>
<li><strong>Batch</strong> — a group of products produced or bought together, such as fifty chickens raised in one cycle.</li>
</ul>

<h2>Summary</h2>
<p>Pricing for profit means knowing your costs, adding a sensible mark-up, and verifying that customers will pay. Mark-up is calculated on cost; margin is calculated on selling price. Mrs Zulu's chicken business shows how batch costing works over a six-week cycle. Grace's salon shows why monthly estimates sometimes need adjustment when real numbers arrive. Always test your prices with real customers before printing a final price list.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/economics-finance-domain/microeconomics">Khan Academy — Microeconomics</a></li>
<li><a href="https://www.libreoffice.org/get-help/">LibreOffice — Calc for Business Budgets</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/introduction-to-data/">Microsoft Learn — Data Fundamentals</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.3 Mark-Ups, Margins, and the Grocery Business',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to calculate mark-ups and margins for a grocery shop, explain why different products have different margins, and describe how to adjust prices when costs change or competition increases.</p>

<h2>The Grocery Shop Reality</h2>
<p>A small grocery shop, or "kantemba," is one of the most common businesses in Zambia. It looks simple: buy goods from a wholesaler, mark up the price, and sell to neighbours. In reality, grocery pricing is tricky. Some items like sugar and cooking oil have thin margins because customers know the price and will walk to the next shop if yours is higher. Other items like biscuits, airtime, and soap have better margins because customers are less price-sensitive.</p>
<p>Smart grocery owners know that profit comes from the mix of products, not from any single item. They attract customers with low-margin essentials and make money on higher-margin extras.</p>

<h2>Calculating Grocery Margins</h2>
<p>Imagine you run a small shop in Kalomo. Here are three products with different cost and pricing strategies:</p>
<table>
<thead>
<tr><th>Product</th><th>Cost Price</th><th>Selling Price</th><th>Mark-Up</th><th>Margin</th></tr>
</thead>
<tbody>
<tr><td>1 kg sugar</td><td>K25</td><td>K28</td><td>12%</td><td>10.7%</td></tr>
<tr><td>Bar of soap</td><td>K15</td><td>K20</td><td>33%</td><td>25%</td></tr>
<tr><td>Packet of biscuits</td><td>K8</td><td>K12</td><td>50%</td><td>33%</td></tr>
</tbody>
</table>
<p>Sugar has the lowest margin because customers compare prices. Biscuits have the highest margin because they are an impulse buy. A shop that sells only sugar will struggle. A shop that sells sugar, soap, and biscuits together can average a healthy overall margin.</p>

<h2>Responding to Cost Changes</h2>
<p>Wholesale prices rise. Transport costs increase when fuel prices go up. Rent goes up when the lease is renewed. When costs change, you have three options:</p>
<ol>
<li><strong>Raise your selling price</strong> — This protects your margin but may annoy customers. Do it gradually and explain if asked.</li>
<li><strong>Reduce your costs</strong> — Find a cheaper supplier, buy in larger quantities, or reduce waste. Every Kwacha saved is a Kwacha of profit.</li>
<li><strong>Accept a lower margin</strong> — Sometimes competition forces you to absorb a cost increase temporarily. This is risky if it lasts too long.</li>
</ol>

<h2>Competition and Price Matching</h2>
<p>If a new shop opens across the street, do not immediately slash prices. Price wars hurt everyone. Instead, ask yourself why customers should choose you. Can you offer credit to trusted regulars? Can you deliver to their door? Can you stay open later? Can you sell fresh bread or airtime that the competitor lacks? Compete on service and convenience, not just price.</p>

<h2>Worked Example: Adjusting Prices After a Fuel Increase</h2>
<p>Mr Phiri's grocery shop sells a 25 kg bag of mealie meal at K180, which he buys from the wholesaler at K155. His margin is K25 per bag, or 13.9%. When fuel prices rise, the wholesaler increases the price to K165. Mr Phiri has three choices:</p>
<ol>
<li>Keep selling at K180. His margin drops to K15, or 8.3%. He makes less money but keeps customers happy.</li>
<li>Raise the price to K190. His margin returns to K25, but some customers may complain or buy elsewhere.</li>
<li>Negotiate with the wholesaler for a discount if he buys five bags at once. He pays K162 per bag, sells at K185, and maintains a margin of K23, or 12.4%. This is his best option because it preserves most of his profit without a dramatic price increase.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Visit a local grocery shop or wholesaler. Write down the cost prices of five common items.</li>
<li>Estimate the selling prices in a typical shop. Calculate the mark-up and margin for each item.</li>
<li>Identify which item has the highest margin and which has the lowest. Why do you think this is?</li>
<li>Imagine the wholesaler raises all prices by 10%. Choose one item and decide whether you would raise your selling price, absorb the cost, or find a cheaper supplier. Explain your reasoning in two sentences.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Mark-up</strong> — the percentage added to cost to set the selling price.</li>
<li><strong>Margin</strong> — profit as a percentage of the selling price.</li>
<li><strong>Price-sensitive</strong> — describing a product where customers notice and react strongly to price changes.</li>
<li><strong>Impulse buy</strong> — a product that customers purchase without planning, often because it is displayed attractively.</li>
<li><strong>Product mix</strong> — the combination of different products a business sells to balance low-margin and high-margin items.</li>
</ul>

<h2>Summary</h2>
<p>Grocery pricing is about balancing thin margins on essentials with better margins on convenience items. Calculate mark-up and margin for every product, watch how costs change, and respond by negotiating with suppliers rather than starting a price war. Mr Phiri's mealie meal example shows that buying in bulk can protect your margin without shocking your customers. Remember that service, trust, and convenience are often more important to shoppers than a one-Kwacha difference.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/economics-finance-domain/microeconomics">Khan Academy — Microeconomics</a></li>
<li><a href="https://www.libreoffice.org/get-help/">LibreOffice — Calc Spreadsheets</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/introduction-to-data/">Microsoft Learn — Data Fundamentals</a></li>
</ul>
HTML,
            ],
        ];
    }


    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Costing and Pricing in Kwacha',
            'description' => 'Test your ability to calculate costs, mark-ups, and margins for small Zambian businesses.',
            'time_limit_minutes' => 25,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is an example of an overhead cost?',
                    'explanation' => 'Overheads are fixed costs like rent and electricity that do not change directly with the number of units sold.',
                    'options' => [
                        ['text' => 'The potatoes used to make one plate of chips', 'is_correct' => false],
                        ['text' => 'Monthly stall rent', 'is_correct' => true],
                        ['text' => 'The cooking oil for today\'s sales', 'is_correct' => false],
                        ['text' => 'A bag of fertiliser for one crop', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Mr Banda\'s chips stall has a total cost of K15 per plate. He adds a 30% mark-up. What is his selling price?',
                    'explanation' => 'A 30% mark-up on K15 is K4.50, so the selling price is K15 + K4.50 = K19.50.',
                    'options' => [
                        ['text' => 'K18.00', 'is_correct' => false],
                        ['text' => 'K19.50', 'is_correct' => true],
                        ['text' => 'K20.00', 'is_correct' => false],
                        ['text' => 'K45.00', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In the chicken rearing example, what was Mrs Zulu\'s cost per dressed bird?',
                    'explanation' => 'Total cost K4,750 divided by 45 sellable birds equals K105 per bird.',
                    'options' => [
                        ['text' => 'K95', 'is_correct' => false],
                        ['text' => 'K105', 'is_correct' => true],
                        ['text' => 'K150', 'is_correct' => false],
                        ['text' => 'K4,750', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Mark-up and margin mean exactly the same thing.',
                    'explanation' => 'Mark-up is calculated as a percentage of cost, while margin is calculated as a percentage of selling price. They are different.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A grocery shop should aim to make the same margin on every product it sells.',
                    'explanation' => 'Different products have different price sensitivities. Essentials like sugar often have thin margins, while impulse items have higher margins.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which strategy best protects profit when a supplier raises prices?',
                    'explanation' => 'Negotiating for bulk discounts reduces your cost per unit and protects your margin without forcing a large price increase on customers.',
                    'options' => [
                        ['text' => 'Immediately cut prices to beat competitors', 'is_correct' => false],
                        ['text' => 'Buy in bulk and negotiate a discount', 'is_correct' => true],
                        ['text' => 'Stop buying from that supplier forever', 'is_correct' => false],
                        ['text' => 'Hide the price increase from customers', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'If a product costs K40 and sells for K60, what is the profit margin as a percentage? (one number)',
                    'explanation' => 'Profit is K20. Margin = K20 / K60 × 100 = 33.3%, which rounds to 33%.',
                    'correct_answer' => '33',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Grace\'s salon has monthly costs of K2,300 and plans to serve 40 customers. What is her cost per customer before profit?',
                    'explanation' => 'K2,300 divided by 40 customers equals K57.50 per customer.',
                    'options' => [
                        ['text' => 'K40', 'is_correct' => false],
                        ['text' => 'K57.50', 'is_correct' => true],
                        ['text' => 'K80', 'is_correct' => false],
                        ['text' => 'K2,300', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for the point where total income equals total costs? (two words)',
                    'explanation' => 'Break-even is the point where a business makes neither profit nor loss.',
                    'correct_answer' => 'Break Even',
                ],
            ],
        ];
    }


    private function module4Lessons(): array
    {
        return [
            [
                'title' => '4.1 Daily Record Keeping in an Exercise Book',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to set up a simple manual bookkeeping system in an exercise book, record daily sales and expenses accurately, and explain why written records are essential for tax, loans, and business decisions.</p>

<h2>Why Records Matter</h2>
<p>The most common mistake among small business owners in Zambia is keeping everything in their head. They remember who owes them money, they guess how much profit they made last month, and they assume they will never forget. Then a customer denies a debt, ZRA asks for records, or a bank refuses a loan because there is no evidence of income.</p>
<p>Good records protect you, help you make better decisions, and increase the value of your business. A shop with written books can be sold to a new owner. A shop that exists only in the owner's memory dies when the owner stops working.</p>

<h2>The Simplest System: One Exercise Book</h2>
<p>You do not need expensive software. A hard-backed exercise book from a stationer, a pen, and a calculator are enough to start. Divide each page into four columns:</p>
<ol>
<li><strong>Date</strong> — The day the transaction happened.</li>
<li><strong>Description</strong> — What happened: "Sold 5 kg sugar to Mrs Banda," "Bought 2 bags mealie meal from wholesaler," "Paid electricity."</li>
<li><strong>Money In (Income)</strong> — How much you received.</li>
<li><strong>Money Out (Expense)</strong> — How much you spent.</li>
</ol>
<p>At the end of each day, draw a line and add up the day's totals. At the end of each week, add up the week's totals. At the end of each month, add up the month's totals. This gives you an instant picture of whether you are making or losing money.</p>

<h2>Rules for Honest Recording</h2>
<ul>
<li><strong>Write on the day</strong> — Do not wait until evening. If you are busy, write during a quiet moment. Memory fades and numbers get confused.</li>
<li><strong>Record everything</strong> — Even K2 for a bottle of water. Small expenses add up over a month.</li>
<li><strong>Use actual amounts</strong> — If a customer pays K18 instead of K20 because you gave a discount, write K18. Do not pretend it was a full-price sale.</li>
<li><strong>Keep receipts</strong> — Staple or tape supplier receipts into the back of the book. If ZRA visits, you have proof of your expenses.</li>
<li><strong>Never tear out pages</strong> — A book with missing pages looks suspicious. If you make a mistake, cross it out with a single line and write the correction next to it.</li>
</ul>

<h2>Worked Example: A Week in Mr Lungu's Shop</h2>
<p>Mr Lungu runs a small grocery in Kalomo. His exercise book for one week looks like this:</p>
<table>
<thead>
<tr><th>Date</th><th>Description</th><th>Money In</th><th>Money Out</th></tr>
</thead>
<tbody>
<tr><td>Mon 03 Jun</td><td>Sold sugar, soap, airtime</td><td>K145</td><td></td></tr>
<tr><td>Mon 03 Jun</td><td>Bought mealie meal from wholesaler</td><td></td><td>K310</td></tr>
<tr><td>Tue 04 Jun</td><td>Sold cooking oil, biscuits, sweets</td><td>K92</td><td></td></tr>
<tr><td>Tue 04 Jun</td><td>Paid council levy</td><td></td><td>K50</td></tr>
<tr><td>Wed 05 Jun</td><td>Sold airtime, soap, sugar</td><td>K118</td><td></td></tr>
<tr><td>Thu 06 Jun</td><td>Sold mealie meal, cooking oil</td><td>K205</td><td></td></tr>
<tr><td>Fri 07 Jun</td><td>Bought stock from Lusaka</td><td></td><td>K480</td></tr>
<tr><td>Fri 07 Jun</td><td>Paid transport</td><td></td><td>K80</td></tr>
<tr><td>Sat 08 Jun</td><td>Sold various items</td><td>K167</td><td></td></tr>
</tbody>
</table>
<p><strong>Week total:</strong> Money In = K727. Money Out = K920. Mr Lungu realises immediately that this was a bad week. His stock purchase was large, but his sales were low. He decides to reduce his next stock order and run a small promotion to attract more customers.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Buy a hard-backed exercise book and a pen. Label the first page "Income and Expenses."</li>
<li>Draw four columns: Date, Description, Money In, Money Out.</li>
<li>For the next seven days, record every transaction related to a real or imaginary business. Do not skip anything.</li>
<li>At the end of the week, calculate your total income, total expenses, and profit or loss.</li>
<li>Look at your numbers. What is your biggest expense? What is your best-selling item? Write two sentences describing what you learned.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Bookkeeping</strong> — the practice of recording all financial transactions of a business.</li>
<li><strong>Income</strong> — money received from sales or services.</li>
<li><strong>Expense</strong> — money spent on stock, overheads, or other business costs.</li>
<li><strong>Receipt</strong> — a written proof of payment received or given.</li>
<li><strong>Ledger</strong> — a book or document where financial transactions are recorded in order.</li>
</ul>

<h2>Summary</h2>
<p>A simple exercise book is the foundation of good business management. Record every transaction on the day it happens, keep receipts, and total your numbers weekly and monthly. Mr Lungu's weekly summary shows how quickly written records reveal problems. Without records, he might have continued buying too much stock and wondered why his pocket was empty. With records, he can act fast and make informed decisions.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.libreoffice.org/get-help/">LibreOffice — Getting Started with Calc</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/introduction-to-data/">Microsoft Learn — Data Fundamentals</a></li>
<li><a href="https://www.khanacademy.org/economics-finance-domain/microeconomics">Khan Academy — Economics Basics</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.2 Using Excel for Business Records',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create a simple spreadsheet for tracking income and expenses, use basic formulas to calculate totals and profit, and explain when moving from paper to a computer makes sense for a small Zambian business.</p>

<h2>When to Move to a Computer</h2>
<p>An exercise book is excellent for a one-person business with a few daily transactions. But when your business grows, paper becomes slow and error-prone. A shop with fifty products, a salon with a hundred customers per month, or a farm with multiple batches of chickens produces too many numbers for a book. A spreadsheet on a computer can add automatically, search for past transactions, and produce neat summaries for the bank or ZRA.</p>
<p>You do not need to buy Microsoft Excel. LibreOffice Calc is free, works on any computer, and handles everything a small business needs. If you have a smartphone, Google Sheets is also free and stores your data online so you cannot lose it.</p>

<h2>Setting Up Your First Spreadsheet</h2>
<p>Open LibreOffice Calc or Google Sheets. Create a new file and name it "Business Records 2025." In the first row, type these column headers:</p>
<ol>
<li>Date</li>
<li>Item or Description</li>
<li>Category (e.g., Sales, Stock, Rent, Transport)</li>
<li>Income (Kwacha)</li>
<li>Expense (Kwacha)</li>
<li>Notes</li>
</ol>
<p>Each row below represents one transaction. Type the date, what happened, the category, and the amount. Put income in column D and expenses in column E. Leave the other column blank.</p>

<h2>Using Formulas</h2>
<p>The power of a spreadsheet is automatic calculation. Here are the three formulas every small business needs:</p>
<ul>
<li><strong>Total Income:</strong> Click an empty cell and type <code>=SUM(D2:D50)</code> to add all numbers in the Income column from row 2 to row 50.</li>
<li><strong>Total Expenses:</strong> Type <code>=SUM(E2:E50)</code> to add all numbers in the Expense column.</li>
<li><strong>Profit:</strong> Type <code>=D51-E51</code> assuming D51 contains total income and E51 contains total expenses.</li>
</ul>
<p>When you add new transactions, the totals update automatically. You never need a calculator again.</p>

<h2>Worked Example: Ms Tembo's Salon Spreadsheet</h2>
<p>Ms Tembo runs a busy salon and serves about thirty customers per week. She creates a spreadsheet with six columns. After one month, her sheet shows:</p>
<ul>
<li>Total Income (SUM of column D) = K4,800</li>
<li>Total Expenses (SUM of column E) = K1,650</li>
<li>Profit = K4,800 - K1,650 = K3,150</li>
</ul>
<p>She also uses the spreadsheet to see which categories cost the most. She sorts column C and discovers that "Stock" is her biggest expense at K800 per month. She phones her supplier and negotiates a 10% discount for buying in larger quantities. Her next month's stock cost drops to K720, increasing her profit without raising prices.</p>

<h2>Backing Up Your Records</h2>
<p>A computer file can be lost if the hard drive fails, the laptop is stolen, or load-shedding interrupts a save. Protect your records with these habits:</p>
<ul>
<li>Save your file every time you add new data. Use Ctrl+S.</li>
<li>Make a copy on a USB flash drive every Friday.</li>
<li>If you use Google Sheets, your data is saved automatically online.</li>
<li>Email a copy to yourself at the end of each month.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Open LibreOffice Calc on a college computer or Google Sheets on your phone.</li>
<li>Create a spreadsheet with the six column headers described above.</li>
<li>Enter at least twenty real or imaginary transactions.</li>
<li>Use the SUM formula to calculate total income and total expenses.</li>
<li>Calculate profit. Add a note in the Notes column next to your biggest expense explaining how you might reduce it.</li>
<li>Save the file and make a backup copy on a USB drive or email it to yourself.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Spreadsheet</strong> — a computer program that organises data in rows and columns and performs calculations automatically.</li>
<li><strong>Formula</strong> — an instruction that tells the spreadsheet how to calculate a result, such as =SUM() or =D5-E5.</li>
<li><strong>Cell</strong> — a single box in a spreadsheet where you enter one piece of data.</li>
<li><strong>Column</strong> — a vertical set of cells, usually labelled with a letter such as A, B, or C.</li>
<li><strong>Backup</strong> — a copy of a file stored in a separate location to prevent data loss.</li>
</ul>

<h2>Summary</h2>
<p>Spreadsheets turn bookkeeping from a chore into a tool. LibreOffice Calc and Google Sheets are free and powerful enough for any small Zambian business. Set up six columns, enter every transaction, and use SUM formulas to see your totals and profit instantly. Ms Tembo's story shows that organised records do more than satisfy ZRA; they reveal opportunities to cut costs and increase profit. Save often, back up weekly, and treat your spreadsheet as one of your most valuable business assets.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.libreoffice.org/get-help/">LibreOffice Calc Tutorials</a></li>
<li><a href="https://support.google.com/docs/answer/6000292">Google Sheets Help — Create and Edit Spreadsheets</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/introduction-to-data/">Microsoft Learn — Data Fundamentals</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.3 Mobile Money for Business: Till Numbers and Separation',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to set up a mobile money till number for your business, explain why separating business and personal money is essential, and describe how to handle mobile money transactions safely.</p>

<h2>Mobile Money Is Banking for Small Businesses</h2>
<p>In Zambia, many people do not live near a bank branch. Mobile money changes this. Airtel Money and MTN MoMo allow customers to pay you instantly using their phone, and allow you to pay suppliers, buy electricity tokens, and transfer money without travelling. For a small business, mobile money is faster than cash, safer than keeping large amounts of money at home, and creates a digital record of every transaction.</p>
<p>However, mobile money is also a common source of business failure. When business money and personal money mix in the same wallet, the owner loses track of profit, spends business money on household needs, and cannot prove income to a bank or ZRA.</p>

<h2>Setting Up a Business Till Number</h2>
<p>A till number is a special mobile money account linked to your business name. Customers pay by dialling a short code and entering your till number. The money goes into your business account, not your personal wallet. Here is how to get one:</p>
<ol>
<li><strong>Choose a provider</strong> — Airtel Money and MTN MoMo both offer till numbers. Some businesses register with both to avoid losing customers who use only one network.</li>
<li><strong>Visit an authorised agent</strong> — Bring your NRC, PACRA certificate, and TPIN. Some providers also require a passport photo and proof of address.</li>
<li><strong>Complete the application</strong> — Choose a business name that customers will recognise. If your shop is "Kalomo Fresh Eggs," use that name, not your personal name.</li>
<li><strong>Receive your till number and credentials</strong> — The agent will give you a till number, a PIN, and instructions for checking your balance and withdrawing money.</li>
<li><strong>Display the number prominently</strong> — Write it on a sign at your stall, add it to your WhatsApp status, and print it on receipts.</li>
</ol>

<h2>Separating Business and Personal Money</h2>
<p>This is the most important financial habit for any entrepreneur. When separation is maintained:</p>
<ul>
<li>You know exactly how much profit the business made.</li>
<li>You can pay yourself a regular "salary" from the business instead of dipping in randomly.</li>
<li>ZRA and banks can see clean records.</li>
<li>You resist the temptation to use business money for personal emergencies.</li>
</ul>
<p>Practical steps to maintain separation:</p>
<ol>
<li>Open a separate mobile money wallet or bank account for the business.</li>
<li>Deposit all business income into the business account on the day it is received.</li>
<li>Pay all business expenses from the business account.</li>
<li>Once a week, transfer a fixed amount to your personal wallet as your salary. This amount should be based on your profit, not your wishes.</li>
<li>Never use business money for personal shopping, school fees, or funerals unless you record it as a loan or owner's withdrawal.</li>
</ol>

<h2>Security Rules for Mobile Money</h2>
<ul>
<li><strong>Never share your PIN</strong> — Not with employees, not with family, not with agents who claim to be fixing a problem.</li>
<li><strong>Use a strong PIN</strong> — Avoid birthdays and simple sequences like 1234. Change it every three months.</li>
<li><strong>Check your balance daily</strong> — If a transaction looks wrong, report it to the mobile money helpline immediately.</li>
<li><strong>Beware of scams</strong> — No legitimate agent will call and ask for your PIN. No real promotion requires you to send money first to receive a prize.</li>
<li><strong>Keep your phone locked</strong> — Use a password or fingerprint. If your phone is stolen, a thief cannot access your mobile money.</li>
</ul>

<h2>Worked Example: Mrs Zulu's Mobile Money System</h2>
<p>Mrs Zulu sells dressed chickens. She registers an Airtel Money till number in the name "Zulu's Poultry." Her system works like this:</p>
<ol>
<li>Customers pay either cash or via Airtel Money to her till number.</li>
<li>At the end of each day, she checks her till balance by dialling the short code.</li>
<li>She transfers all cash and mobile money income into her business wallet before going home.</li>
<li>She pays her feed supplier and the person who helps her dress chickens from the business wallet.</li>
<li>Every Saturday, she transfers K800 to her personal wallet as her salary. She records this in her exercise book as "Owner's salary."</li>
<li>When her mother needs K300 for medicine, Mrs Zulu records it as "Owner's loan" and plans to repay it from next week's salary instead of pretending it was a business expense.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Visit an Airtel Money or MTN MoMo agent and ask for a till number application form.</li>
<li>Write down the documents required and the fees, if any.</li>
<li>Create a simple rule for yourself: "I will pay myself K___ every ___ from my business profits." Choose realistic numbers.</li>
<li>Role-play with a friend: one person pretends to be a scammer asking for your PIN. Practise saying no and hanging up.</li>
<li>Write a short poster for your business showing your till number and the message: "Pay safely with Airtel Money or MTN MoMo."</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Till number</strong> — a mobile money account linked to a business name that allows customers to pay electronically.</li>
<li><strong>Mobile money</strong> — electronic financial services accessed through a mobile phone, such as Airtel Money and MTN MoMo.</li>
<li><strong>Separation of funds</strong> — the practice of keeping business money in a separate account from personal money.</li>
<li><strong>Owner's salary</strong> — a regular, planned payment from business profits to the owner as personal income.</li>
<li><strong>Owner's loan</strong> — money taken from the business for personal use that is recorded as a debt to be repaid.</li>
</ul>

<h2>Summary</h2>
<p>Mobile money is a powerful tool for Zambian businesses, but only if used with discipline. Register a till number in your business name, display it proudly, and keep your PIN secret. Most importantly, separate business money from personal money so you can see your true profit, pay yourself properly, and build trust with lenders and tax authorities. Mrs Zulu's weekly salary system shows that treating yourself as an employee of your own business is the first step toward professional management.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/android/answer/6174145">Google Android Help — Device Security</a></li>
<li><a href="https://www.libreoffice.org/get-help/">LibreOffice — Calc for Financial Records</a></li>
<li><a href="https://www.khanacademy.org/economics-finance-domain/microeconomics">Khan Academy — Economics Basics</a></li>
</ul>
HTML,
            ],
        ];
    }


    private function module4Quiz(): array
    {
        return [
            'title' => 'Module 4 Quiz: Record Keeping and Mobile Money',
            'description' => 'Test your understanding of manual bookkeeping, spreadsheets, and mobile money management for small businesses.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main advantage of writing transactions down on the day they happen?',
                    'explanation' => 'Recording immediately prevents memory errors and ensures accuracy for tax, loans, and decision-making.',
                    'options' => [
                        ['text' => 'It makes the book look neater', 'is_correct' => false],
                        ['text' => 'Memory fades and numbers get confused', 'is_correct' => true],
                        ['text' => 'It impresses customers', 'is_correct' => false],
                        ['text' => 'It reduces the need for a calculator', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In a spreadsheet, which formula calculates the total of all expenses in cells E2 to E50?',
                    'explanation' => 'The SUM formula adds all numbers in the specified range. =SUM(E2:E50) is the correct syntax.',
                    'options' => [
                        ['text' => '=ADD(E2:E50)', 'is_correct' => false],
                        ['text' => '=SUM(E2:E50)', 'is_correct' => true],
                        ['text' => '=TOTAL(E2-E50)', 'is_correct' => false],
                        ['text' => '=E2+E50', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'It is acceptable to tear a page out of your exercise book if you make a mistake.',
                    'explanation' => 'Torn pages look suspicious. Cross out errors with a single line and write the correction next to them.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A business till number should be registered in the owner\'s personal name for simplicity.',
                    'explanation' => 'A till number should be registered in the business name to separate business and personal funds and build trust.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is it dangerous to mix business and personal mobile money in the same wallet?',
                    'explanation' => 'Mixing funds makes it impossible to know true profit, creates tax problems, and leads to spending business money on personal needs.',
                    'options' => [
                        ['text' => 'Mobile money providers charge higher fees for mixed accounts', 'is_correct' => false],
                        ['text' => 'You cannot know your true profit or prove income to a bank', 'is_correct' => true],
                        ['text' => 'It is illegal to have more than one mobile money account', 'is_correct' => false],
                        ['text' => 'Customers refuse to pay into personal wallets', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a safe mobile money practice?',
                    'explanation' => 'Checking your balance daily helps you spot unauthorised transactions quickly.',
                    'options' => [
                        ['text' => 'Sharing your PIN with a trusted employee', 'is_correct' => false],
                        ['text' => 'Checking your balance daily', 'is_correct' => true],
                        ['text' => 'Using your birth year as your PIN', 'is_correct' => false],
                        ['text' => 'Sending money to claim a prize you were told you won', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the name for money taken from the business for personal use that is recorded as a debt to be repaid? (two words)',
                    'explanation' => 'An owner\'s loan is personal money borrowed from the business that should be recorded and ideally repaid.',
                    'correct_answer' => 'Owner Loan',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Ms Tembo used her spreadsheet to discover that her biggest expense category was stock. What did she do next?',
                    'explanation' => 'She negotiated a bulk discount with her supplier, reducing her stock cost and increasing profit without raising prices.',
                    'options' => [
                        ['text' => 'She stopped buying stock for a month', 'is_correct' => false],
                        ['text' => 'She negotiated a 10% bulk discount', 'is_correct' => true],
                        ['text' => 'She fired her helper', 'is_correct' => false],
                        ['text' => 'She switched to a manual exercise book', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }


    private function module5Lessons(): array
    {
        return [
            [
                'title' => '5.1 Basic Marketing on a Small Budget',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to define marketing in simple terms, identify low-cost ways to attract customers, and explain why understanding your customer is more important than spending money on advertising.</p>

<h2>What Is Marketing?</h2>
<p>Marketing is not just advertising. It is everything you do to attract customers, keep them happy, and persuade them to buy again. Your prices are marketing. Your cleanliness is marketing. The way you greet people is marketing. A friendly smile and a remembered name can be more powerful than a billboard.</p>
<p>For a small Zambian business with a tight budget, marketing must be creative, personal, and consistent. You cannot afford to waste money on radio spots that reach people who will never visit your shop. Instead, you must focus on the people most likely to buy and reach them where they already are.</p>

<h2>The Four Ps for Small Businesses</h2>
<p>Every marketing plan can be built around four simple questions:</p>
<ol>
<li><strong>Product</strong> — What exactly are you selling? Is it high quality? Does it solve a real problem? A product that people need markets itself.</li>
<li><strong>Price</strong> — Does your price feel fair? Can customers compare it easily? Special offers and loyalty discounts can attract first-time buyers without permanently slashing prices.</li>
<li><strong>Place</strong> — Where do customers find you? Are you visible from the road? Is your stall clean and inviting? If you sell online, is your till number easy to find?</li>
<li><strong>Promotion</strong> — How do people hear about you? Word of mouth, WhatsApp, posters, and community events are free or cheap. Use them before you spend on radio or newspapers.</li>
</ol>

<h2>Low-Cost Marketing Tactics That Work in Zambia</h2>
<ul>
<li><strong>Word of mouth</strong> — Treat every customer so well that they tell their friends. Offer a small discount or free item to customers who bring a new buyer.</li>
<li><strong>WhatsApp Status</strong> — Post photos of your products every morning. Show fresh stock, happy customers, and special offers. It is free and reaches everyone who has your number.</li>
<li><strong>Loyalty cards</strong> — "Buy five, get one free" or "Every sixth haircut half price." This keeps customers coming back instead of trying competitors.</li>
<li><strong>Community involvement</strong> — Sponsor a local football team, donate to a school event, or offer a discount during a church fundraiser. People remember businesses that support their community.</li>
<li><strong>Demonstrations</strong> — If you sell a product, let people try it. A taste of your bread, a test of your phone charger, or a free sample of your lotion builds trust instantly.</li>
</ul>

<h2>Worked Example: Mrs Banda Markets Her Lunch Boxes</h2>
<p>Remember Mrs Banda from Module 1? She now needs parents to know about her lunch-box service. Her marketing plan costs almost nothing:</p>
<ol>
<li>She visits the school at closing time and hands out small printed cards with her phone number and a photo of a healthy lunch box. The cards cost K50 for one hundred.</li>
<li>She joins the school WhatsApp group and politely offers a free sample lunch box to the first five parents who reply. Five parents try it. Four order regularly.</li>
<li>She starts a simple loyalty scheme: every tenth lunch box is free. Parents feel rewarded and tell other parents.</li>
<li>She asks satisfied customers to post a photo of the lunch box on their own WhatsApp Status with her phone number. Three parents do this in the first month.</li>
</ol>
<p>Within six weeks, Mrs Banda has twenty regular customers and does not need to spend any more on promotion.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Describe your ideal customer: age, location, income, habits, and where they spend time.</li>
<li>Write down three free or very cheap ways you could reach fifty of these customers in one week.</li>
<li>Design a simple loyalty offer for your business. How many purchases before the reward? What is the reward?</li>
<li>Ask five people in your community what makes them choose one shop over another. Write down their answers and look for patterns.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Marketing</strong> — all activities designed to attract, satisfy, and retain customers.</li>
<li><strong>Word of mouth</strong> — free promotion that happens when satisfied customers tell others about your business.</li>
<li><strong>Loyalty scheme</strong> — a reward programme that encourages repeat purchases.</li>
<li><strong>Target customer</strong> — the specific group of people most likely to buy from you.</li>
<li><strong>Promotion</strong> — communication activities that inform potential customers about your product or service.</li>
</ul>

<h2>Summary</h2>
<p>Marketing is not about big budgets; it is about understanding people. Focus on the four Ps: product, price, place, and promotion. Use free tools like WhatsApp, word of mouth, loyalty cards, and community events before you spend on expensive advertising. Mrs Banda's lunch-box success shows that a personal approach, a free sample, and a loyalty scheme can grow a customer base faster than a radio spot. Treat every customer like your only customer, and they will bring their friends.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/">Google Digital Garage — Fundamentals of Digital Marketing</a></li>
<li><a href="https://www.khanacademy.org/economics-finance-domain/microeconomics">Khan Academy — Economics and Business Basics</a></li>
<li><a href="https://www.canva.com/designschool/">Canva Design School — Creating Simple Posters and Flyers</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.2 Using WhatsApp, Radio, and Posters to Reach Customers',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create a simple WhatsApp marketing strategy, design an effective poster, and decide when local radio advertising is worth the cost for a small Zambian business.</p>

<h2>WhatsApp as a Business Tool</h2>
<p>WhatsApp is the most popular messaging app in Zambia. Almost every adult with a smartphone uses it daily. For a small business, WhatsApp is not just for chatting; it is a free marketing channel, a customer service desk, and a sales counter all in one.</p>
<p>There are three ways to use WhatsApp for business:</p>
<ol>
<li><strong>WhatsApp Status</strong> — Post photos and short videos of your products every day. Everyone who has saved your number will see your updates. Show new stock, special offers, happy customers, and behind-the-scenes moments. Be consistent: post every morning so people remember you.</li>
<li><strong>Broadcast lists</strong> — Create a list of customers and send one message to all of them at once. Unlike a group, recipients cannot see each other's numbers. Use this sparingly: too many messages annoy people. Once or twice a week is enough.</li>
<li><strong>WhatsApp Business</strong> — A free app with extra features: a business profile, automated greetings, quick replies, and labels to organise chats. If you handle many customer enquiries, switch to WhatsApp Business.</li>
</ol>

<h2>Designing Posters That Work</h2>
<p>A poster must communicate in three seconds because people walk past quickly. Follow these rules:</p>
<ul>
<li><strong>One big headline</strong> — Say what you sell in five words or fewer. "Fresh Eggs Daily" or "Professional Plaiting K50."</li>
<li><strong>One clear photo</strong> — A picture of your best product. If you sell a service, show a happy customer.</li>
<li><strong>One call to action</strong> — Tell people exactly what to do. "Call 0977 123 456" or "Visit us opposite the bus station."</li>
<li><strong>Readable from two metres</strong> — Use large fonts and high contrast. Dark text on a light background works best.</li>
<li><strong>Print in colour if possible</strong> — Colour posters attract more attention than black and white, but a well-designed black-and-white poster is better than a messy colour one.</li>
</ul>

<h2>When to Use Local Radio</h2>
<p>Radio advertising is more expensive than WhatsApp or posters, but it reaches people who do not use smartphones, especially in rural areas. Consider radio only when:</p>
<ul>
<li>Your target customers listen to a specific station, such as Zambezi FM or a local community radio.</li>
<li>You have a special event, such as a grand opening or a clearance sale.</li>
<li>You can afford to run the advert at least ten times over two weeks. One advert is wasted money.</li>
<li>You have a simple, memorable message and a phone number that is easy to repeat.</li>
</ul>
<p>Always ask the radio station for a discount on multiple bookings. Community stations often offer lower rates than national ones.</p>

<h2>Worked Example: Mr Tembo's Barber Shop Marketing</h2>
<p>Mr Tembo from Module 1 now has a small shop. His monthly marketing budget is K200. He spends it like this:</p>
<ul>
<li>K50 on one hundred business cards with his phone number and shop location.</li>
<li>K80 on five colour posters placed at the bus station, the market entrance, and three nearby shops.</li>
<li>K0 on WhatsApp Status updates every morning showing before-and-after haircuts.</li>
<li>K0 on a broadcast message every Friday reminding customers that Saturday is his busiest day and early booking is recommended.</li>
<li>K70 on ten short radio adverts on the local community station during the week of his shop's first anniversary.</li>
</ul>
<p>He tracks where new customers heard about him by simply asking. After three months, 60% came from word of mouth and WhatsApp, 25% from posters, and 15% from radio. He decides to increase his poster budget and reduce radio spending.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Take out your phone and look at your WhatsApp Status. Which business posts do you remember? Why?</li>
<li>Design a simple poster for your business on paper. Show it to three people. Can they tell what you sell and how to contact you in three seconds?</li>
<li>Phone your local radio station and ask for their advertising rate card. How much would ten thirty-second spots cost?</li>
<li>Create a weekly posting schedule for WhatsApp Status. Plan what you will post each day for one week.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>WhatsApp Status</strong> — a feature that lets users share photos and videos visible to contacts for twenty-four hours.</li>
<li><strong>Broadcast list</strong> — a WhatsApp feature that sends a single message to multiple recipients privately.</li>
<li><strong>Call to action</strong> — a clear instruction telling the customer what to do next, such as "Call now" or "Visit today."</li>
<li><strong>Rate card</strong> — a document from a media outlet showing the cost of different types of advertising.</li>
<li><strong>Community radio</strong> — a local radio station serving a specific town or district, often with lower advertising rates than national stations.</li>
</ul>

<h2>Summary</h2>
<p>WhatsApp, posters, and local radio are powerful tools for Zambian businesses when used strategically. WhatsApp Status is free and effective for daily visibility. Posters must be simple, bold, and placed where your customers pass. Radio is worth considering for special events or rural markets, but only with repetition and a memorable message. Mr Tembo's budget split shows that tracking where customers come from helps you spend money where it works.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool/">Canva Design School — Creating Flyers and Posters</a></li>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/">Google Digital Garage — Digital Marketing</a></li>
<li><a href="https://support.google.com/business/answer/638制作">Google Business Profile Help</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.3 Funding Your Business: Chilimba, CEEC, and Bank Loans',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe the main funding options available to Zambian entrepreneurs, explain the honest pros and cons of each, and decide which option suits your business stage and risk tolerance.</p>

<h2>Why Funding Matters</h2>
<p>Most businesses need money to grow. You might need stock, equipment, a shop, or working capital to survive slow months. The question is not whether you need money, but where to get it without losing control of your business or drowning in debt.</p>
<p>In Zambia, entrepreneurs have four main sources of funding: personal savings, village banking or chilimba, government programmes like CEEC, and commercial bank loans. Each has advantages and dangers. Understanding them honestly will save you from signing contracts you regret.</p>

<h2>Option 1: Personal Savings and Family</h2>
<p>The safest money is money you already own. If you save K5,000 from a salaried job or a side business, you owe nothing to anyone. Family members may also lend or gift money.</p>
<ul>
<li><strong>Pros:</strong> No interest, no paperwork, no risk of losing collateral.</li>
<li><strong>Cons:</strong> Limited amount. Family loans can damage relationships if the business fails.</li>
</ul>

<h2>Option 2: Village Banking and Chilimba</h2>
<p>Chilimba, also called village banking or solidarity lending, is a group of people who contribute money regularly and take turns receiving the pooled sum. In Zambia, these groups meet weekly or monthly. Each member might contribute K100, and every month one member receives K3,000 or more depending on the group size.</p>
<ul>
<li><strong>Pros:</strong> No bank required, built-in accountability, flexible terms, and often zero interest.</li>
<li><strong>Cons:</strong> Limited amounts, risk of group collapse if members default, and the schedule may not match your business needs.</li>
</ul>

<h2>Option 3: Citizens Economic Empowerment Commission (CEEC)</h2>
<p>CEEC is a Zambian government programme that provides loans and grants to citizens, especially women, youth, and people with disabilities. The funds are meant for productive activities such as agriculture, manufacturing, and trading.</p>
<ul>
<li><strong>Pros:</strong> Lower interest rates than banks, longer repayment periods, and some categories qualify for grants that do not need repayment.</li>
<li><strong>Cons:</strong> Application process is slow and competitive. Requirements include a business plan, PACRA registration, and sometimes collateral. Not everyone who applies receives funding.</li>
</ul>

<h2>Option 4: Commercial Bank Loans</h2>
<p>Banks such as Zanaco, Stanbic, and FNB offer business loans to registered enterprises with collateral and a trading history.</p>
<ul>
<li><strong>Pros:</strong> Larger amounts, professional relationship, and building a credit history for future borrowing.</li>
<li><strong>Cons:</strong> High interest rates, strict repayment schedules, collateral requirements such as land title or a vehicle, and penalties for late payment. If the business fails, you may lose your collateral.</li>
</ul>

<h2>Worked Example: Mrs Zulu Chooses Her Funding</h2>
<p>Mrs Zulu needs K5,000 to expand her chicken rearing from fifty birds to two hundred. She considers her options:</p>
<ol>
<li>She has K2,000 in personal savings. This is not enough alone.</li>
<li>Her chilimba group can give her K2,500 in two months, but she needs the money now for the next batch.</li>
<li>She applies to CEEC. After three months, she is approved for a K4,000 loan at 10% annual interest, repayable over two years. She uses her K2,000 savings plus K3,000 from CEEC and keeps K1,000 of the loan as emergency reserve.</li>
<li>She decides against a bank loan because she has no collateral other than her home, and she is not willing to risk it.</li>
</ol>
<p>Mrs Zulu's decision is based on her risk tolerance, her timeline, and her available resources. She did not borrow the maximum she could; she borrowed only what she needed with a repayment plan she can afford.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Calculate how much money you would need to start or expand your chosen business. Be specific: list equipment, stock, rent, and licences.</li>
<li>Rate each funding option from 1 to 5 based on: speed, amount available, cost, risk, and suitability for your situation.</li>
<li>Ask two business owners in your area how they funded their start-up. What would they do differently?</li>
<li>Write one paragraph explaining which funding option you would choose and why, including the main risk you would face.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Chilimba</strong> — a rotating savings group where members contribute regularly and take turns receiving the pooled funds.</li>
<li><strong>CEEC</strong> — Citizens Economic Empowerment Commission, a Zambian government body that provides loans and grants to citizens.</li>
<li><strong>Collateral</strong> — an asset such as land or a vehicle that a lender can seize if a borrower fails to repay a loan.</li>
<li><strong>Interest rate</strong> — the percentage charged by a lender on top of the amount borrowed.</li>
<li><strong>Credit history</strong> — a record of how reliably a person or business has borrowed and repaid money in the past.</li>
</ul>

<h2>Summary</h2>
<p>Funding is a tool, not a gift. Personal savings are safest but limited. Chilimba builds community and discipline but provides modest amounts. CEEC offers affordable government support if you can wait and meet the requirements. Bank loans provide large sums but demand collateral and strict repayment. Mrs Zulu's careful mix of savings and a CEEC loan shows that the best funding plan matches your real needs, your timeline, and your willingness to take risk. Never borrow more than your business can repay, and never risk your home on an untested idea.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.ceec.org.zm/">Citizens Economic Empowerment Commission</a></li>
<li><a href="https://www.khanacademy.org/economics-finance-domain/microeconomics">Khan Academy — Economics and Finance</a></li>
<li><a href="https://www.libreoffice.org/get-help/">LibreOffice — Budget Spreadsheets</a></li>
</ul>
HTML,
            ],
        ];
    }


    private function module5Quiz(): array
    {
        return [
            'title' => 'Module 5 Quiz: Marketing and Funding',
            'description' => 'Test your knowledge of low-cost marketing, customer outreach, and business funding options in Zambia.',
            'time_limit_minutes' => 25,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is NOT one of the four Ps of marketing?',
                    'explanation' => 'The four Ps are Product, Price, Place, and Promotion. "Profit" is an outcome, not one of the four Ps.',
                    'options' => [
                        ['text' => 'Product', 'is_correct' => false],
                        ['text' => 'Price', 'is_correct' => false],
                        ['text' => 'Profit', 'is_correct' => true],
                        ['text' => 'Promotion', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main advantage of using WhatsApp Status for business marketing?',
                    'explanation' => 'WhatsApp Status is free, reaches everyone who has saved your number, and allows daily visual updates.',
                    'options' => [
                        ['text' => 'It charges no fees and reaches saved contacts daily', 'is_correct' => true],
                        ['text' => 'It automatically sends messages to strangers', 'is_correct' => false],
                        ['text' => 'It replaces the need for a trading licence', 'is_correct' => false],
                        ['text' => 'It guarantees sales within one week', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A poster should contain multiple detailed paragraphs explaining the business history.',
                    'explanation' => 'Posters must communicate in three seconds. One headline, one photo, and one call to action are enough.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Chilimba is a type of rotating savings group common in Zambia.',
                    'explanation' => 'Chilimba, also called village banking, is a group savings method where members pool contributions and take turns receiving the sum.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which funding option typically requires collateral such as land or a vehicle?',
                    'explanation' => 'Commercial bank loans usually require collateral to secure the loan, unlike personal savings or chilimba.',
                    'options' => [
                        ['text' => 'Personal savings', 'is_correct' => false],
                        ['text' => 'Chilimba', 'is_correct' => false],
                        ['text' => 'Commercial bank loan', 'is_correct' => true],
                        ['text' => 'CEEC grant', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why did Mrs Zulu decide against a commercial bank loan for her chicken business?',
                    'explanation' => 'She lacked collateral besides her home and was unwilling to risk losing it on an unproven expansion.',
                    'options' => [
                        ['text' => 'Banks do not lend to women', 'is_correct' => false],
                        ['text' => 'She had no collateral except her home and refused to risk it', 'is_correct' => true],
                        ['text' => 'Bank loans are always illegal for farms', 'is_correct' => false],
                        ['text' => 'She did not have a TPIN', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What does CEEC stand for? (five words)',
                    'explanation' => 'CEEC stands for Citizens Economic Empowerment Commission, a Zambian government funding programme.',
                    'correct_answer' => 'Citizens Economic Empowerment Commission',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the biggest risk of using a bank loan to fund a new, untested business idea?',
                    'explanation' => 'If the business fails, the borrower may lose collateral and still owe the remaining debt plus penalties.',
                    'options' => [
                        ['text' => 'The bank will give you too much money', 'is_correct' => false],
                        ['text' => 'You may lose your collateral and still owe the debt', 'is_correct' => true],
                        ['text' => 'The government will tax your loan', 'is_correct' => false],
                        ['text' => 'You cannot open a mobile money account', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for an asset a lender can take if you fail to repay a loan? (one word)',
                    'explanation' => 'Collateral is property or an asset pledged as security for a loan.',
                    'correct_answer' => 'Collateral',
                ],
            ],
        ];
    }


    private function module6Lessons(): array
    {
        return [
            [
                'title' => '6.1 Hiring Your First Employee',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to decide when your business is ready to hire, describe the legal requirements for employing someone in Zambia, and explain how to recruit, train, and manage a first employee without destroying your profits.</p>

<h2>When Is It Time to Hire?</h2>
<p>Hiring your first employee is a big step. It means your business is growing, but it also means new costs, new laws, and new management responsibilities. Hire too early and you may not have enough work or income to cover wages. Hire too late and you may lose customers because you cannot serve them fast enough.</p>
<p>Signs that you are ready to hire include:</p>
<ul>
<li>You are turning away customers because you are too busy.</li>
<li>You are working twelve hours a day, seven days a week, and still falling behind.</li>
<li>You have identified a specific task, such as delivery or cleaning, that someone else could do for less than your time is worth.</li>
<li>Your profit is stable and can cover a monthly wage even during slow weeks.</li>
</ul>

<h2>Legal Requirements for Hiring in Zambia</h2>
<p>Zambian labour law protects workers and employers. Before you hire, understand these basics:</p>
<ol>
<li><strong>Employment contract</strong> — Write a simple contract describing the job, hours, wage, probation period, and notice period. Both you and the worker sign it. This prevents misunderstandings.</li>
<li><strong>Minimum wage</strong> — Check the current minimum wage for your sector. Paying below the legal minimum can result in fines from the Ministry of Labour.</li>
<li><strong>NAPSA contributions</strong> — If your employee qualifies, you must register with the National Pension Scheme Authority and contribute a percentage of their wage. This gives the worker a pension when they retire.</li>
<li><strong>PAYE tax</strong> — If the wage is above the tax threshold, you must deduct income tax from the employee's salary and send it to ZRA every month.</li>
<li><strong>Leave days</strong> — Employees are entitled to annual leave, sick leave, and maternity leave according to the law. Plan your staffing so that absences do not shut down your business.</li>
<li><strong>Safe working conditions</strong> — Provide a safe environment. If your employee is injured because of unsafe equipment, you may be liable.</li>
</ol>

<h2>Finding and Choosing the Right Person</h2>
<p>Your first employee will represent your business when you are not there. Choose carefully:</p>
<ul>
<li><strong>Ask your network</strong> — Reliable workers often come through recommendations from people you trust.</li>
<li><strong>Test before you commit</strong> — Offer a one-week or one-month probation. Watch punctuality, honesty, and attitude.</li>
<li><strong>Check references</strong> — Phone previous employers and ask simple questions: Was this person reliable? Did they steal? Would you hire them again?</li>
<li><strong>Look for attitude over experience</strong> — You can teach someone to plait hair or operate a till. You cannot teach honesty or a willingness to work hard.</li>
</ul>

<h2>Managing Your First Employee</h2>
<p>Management is a skill that grows with practice. Start with these habits:</p>
<ol>
<li><strong>Be clear about expectations</strong> — Explain what success looks like. A cleaner knows they have succeeded when the floor is washed and the bins are empty by 08:00.</li>
<li><strong>Pay on time, every time</strong> — Late wages destroy trust faster than anything else. If you cannot pay on the agreed date, explain why and give a new date you can keep.</li>
<li><strong>Give feedback regularly</strong> — Do not wait for a problem to explode. Praise good work immediately. Correct mistakes calmly and privately.</li>
<li><strong>Do not micromanage</strong> — Tell them what to do, not how to do every tiny step. If they find a faster way to achieve the same result, let them.</li>
</ol>

<h2>Worked Example: Grace Hires a Helper</h2>
<p>Grace's salon has grown to sixty customers per month. She is exhausted and customers are waiting too long. She decides to hire a helper to wash hair, sweep, and manage the till.</p>
<ol>
<li>She asks her aunt, who knows a young woman named Mary looking for work.</li>
<li>Grace interviews Mary and gives her a two-week probation at K600 per month.</li>
<li>During probation, Mary arrives on time, learns the prices quickly, and customers like her. Grace offers a permanent contract at K800 per month with one day off per week.</li>
<li>Grace registers with NAPSA and starts deducting PAYE because Mary's wage is above the threshold. She keeps a small notebook recording Mary's attendance and wages.</li>
<li>After three months, Grace trains Mary to do simple plaiting. Mary's wage rises to K1,000. Grace can now serve more customers and her profit increases even after paying the higher wage.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>List three tasks in your business that someone else could do. Estimate how many hours each task takes per week.</li>
<li>Calculate whether your current profit can cover a monthly wage of K800 for at least six months, even if sales drop by 20%.</li>
<li>Draft a simple one-page employment contract for an imaginary helper. Include job title, hours, wage, start date, and notice period.</li>
<li>Ask two employers in your area about their biggest challenge with employees. Write down their answers.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Employment contract</strong> — a written agreement between employer and employee describing terms of work.</li>
<li><strong>Probation</strong> — a trial period during which either party can end the employment with short notice.</li>
<li><strong>NAPSA</strong> — National Pension Scheme Authority, the body that manages pension contributions for Zambian workers.</li>
<li><strong>PAYE</strong> — Pay As You Earn, the system where employers deduct income tax from wages and send it to ZRA.</li>
<li><strong>Micromanage</strong> — to control every small detail of an employee's work, which often reduces motivation.</li>
</ul>

<h2>Summary</h2>
<p>Hiring your first employee is a sign of business health, but it comes with legal and financial responsibilities. Check that your profit is stable, write a simple contract, comply with NAPSA and PAYE, and choose for attitude over experience. Grace's helper Mary shows that a good first hire can increase capacity and profit if managed with respect, clear expectations, and timely payment. Treat your employee well, and they will help your business grow. Treat them badly, and they will cost you customers and reputation.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.zra.org.zm/">Zambia Revenue Authority — PAYE Information</a></li>
<li><a href="https://www.napsa.co.zm/">National Pension Scheme Authority</a></li>
<li><a href="https://www.libreoffice.org/get-help/">LibreOffice — Document Templates</a></li>
</ul>
HTML,
            ],
            [
                'title' => '6.2 Managing Growth and Avoiding Common Failures',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to recognise the signs of healthy and unhealthy growth, describe the most common reasons small businesses fail in Zambia, and explain how to build a business that lasts.</p>

<h2>Growth Is Not Always Good</h2>
<p>Many entrepreneurs believe that bigger is always better. They borrow money to open a second shop, hire too many employees, or buy expensive equipment before they need it. Sometimes this works. Often it leads to cash shortages, unpaid debts, and closure.</p>
<p>Healthy growth means your profit is increasing faster than your costs. Unhealthy growth means your sales are rising but your profit is falling because expenses are out of control. The signs of unhealthy growth include: constant borrowing to pay wages, frequent stock shortages, angry customers because of delays, and working longer hours for the same or less money.</p>

<h2>Common Causes of Business Failure in Zambia</h2>
<p>After studying thousands of small businesses, researchers and lenders have identified the most common causes of failure:</p>
<ol>
<li><strong>Running out of cash</strong> — Even a profitable business can fail if it does not have cash when bills are due. This happens when customers owe you money but you must pay suppliers immediately.</li>
<li><strong>Poor record keeping</strong> — The owner does not know whether the business is profitable until it is too late. This lesson was covered in Module 4 for a reason.</li>
<li><strong>Overborrowing</strong> — The owner takes loans with high interest and cannot generate enough profit to cover repayments. The business becomes a machine for paying banks instead of earning for the owner.</li>
<li><strong>Ignoring customers</strong> — The owner becomes proud or lazy. Quality drops. Staff are rude. Customers leave and do not return.</li>
<li><strong>Failing to adapt</strong> — A new competitor opens, prices change, or customer tastes shift. The owner keeps doing exactly what worked last year and wonders why sales are falling.</li>
<li><strong>Theft and fraud</strong> — Employees, partners, or even family members steal stock or cash. Without records and oversight, theft can continue for months unnoticed.</li>
<li><strong>Personal problems</strong> — Divorce, illness, or funeral expenses drain business funds because the owner never separated personal and business money.</li>
</ol>

<h2>How to Build a Business That Lasts</h2>
<p>Long-lasting businesses share certain habits:</p>
<ul>
<li><strong>Keep emergency savings</strong> — Set aside three months of running costs in a separate account. When load-shedding increases, a supplier fails, or sales drop during the rainy season, this reserve keeps you alive.</li>
<li><strong>Reinvest profit wisely</strong> — Do not spend all profit on a new car or a bigger house. Reinvest in better stock, training, or equipment that generates more income.</li>
<li><strong>Listen to customers</strong> — Ask regular customers what they think. Complaints are free advice. Fix problems quickly and thank customers for telling you.</li>
<li><strong>Watch your competitors</strong> — Visit their shops. Note their prices. Learn from their successes and their mistakes. Copy what works; avoid what fails.</li>
<li><strong>Train your people</strong> — A well-trained employee makes fewer mistakes, serves customers better, and can run the business when you are sick or travelling.</li>
<li><strong>Stay legal</strong> — Keep your PACRA, TPIN, trading licence, and NAPSA contributions current. Fines and closures destroy businesses that were otherwise healthy.</li>
</ul>

<h2>Worked Example: Mr Chanda's Hardware Shop Grows Carefully</h2>
<p>Mr Chanda opened his hardware shop in Kalomo two years ago. He has survived while two competitors closed. Here is how he manages growth:</p>
<ol>
<li>He keeps K6,000 in a separate Airtel Money business wallet as emergency savings. This covers two months of rent and wages.</li>
<li>He reinvests 50% of profit back into stock. Last year he added roofing sheets to his product line because customers kept asking. Sales rose by 30%.</li>
<li>Every Saturday morning, he walks through his shop and asks three customers what they think. Last month, a customer mentioned that cement prices were lower at a competitor. Mr Chanda negotiated with his supplier and matched the price.</li>
<li>He employs two people. Both have written contracts and NAPSA contributions. He pays them on the first of every month, never late.</li>
<li>He visits the competitor's shop once a month to check prices and displays. He noticed they stopped selling nails in small packets. He started selling nails in small, affordable packets and gained new customers.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>List the three biggest risks that could destroy your business in the next year. For each, write one action you could take now to reduce the risk.</li>
<li>Calculate how much money you would need to cover three months of running costs. This is your emergency fund target.</li>
<li>Ask three customers or potential customers what they dislike about businesses like yours. Write their answers and choose one to fix.</li>
<li>Write a one-page "survival plan" for your business. Include: how much cash you keep in reserve, how you will handle a 20% drop in sales, and which costs you would cut first.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Cash flow</strong> — the movement of money into and out of a business. Positive cash flow means more money coming in than going out.</li>
<li><strong>Emergency fund</strong> — savings set aside to cover unexpected expenses or slow sales periods.</li>
<li><strong>Reinvestment</strong> — using profit to improve the business rather than spending it on personal items.</li>
<li><strong>Overborrowing</strong> — taking on more debt than the business can realistically repay from its profits.</li>
<li><strong>Adaptation</strong> — changing business practices in response to new competition, customer needs, or market conditions.</li>
</ul>

<h2>Summary</h2>
<p>Businesses fail for predictable reasons: cash shortages, poor records, too much debt, ignored customers, and failure to adapt. Mr Chanda's careful growth shows that survival is not about luck; it is about habits. Keep an emergency fund, reinvest wisely, listen to customers, watch competitors, train your staff, and stay legal. Growth is good only when it is profitable and sustainable. A small business that lasts twenty years is more successful than a large one that closes in two.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/economics-finance-domain/microeconomics">Khan Academy — Economics and Business Basics</a></li>
<li><a href="https://www.libreoffice.org/get-help/">LibreOffice — Calc for Budgeting</a></li>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/">Google Digital Garage — Business Skills</a></li>
</ul>
HTML,
            ],
            [
                'title' => '6.3 Planning for the Future: Succession, Exit, and Legacy',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain why every business needs a long-term plan, describe simple ways to pass a business to family or sell it, and outline how to close a business properly if it no longer works.</p>

<h2>Why Think About the Future?</h2>
<p>Most small business owners in Zambia focus only on surviving today. They do not think about what happens if they get sick, retire, or want to try something new. A business that depends entirely on one person is fragile. If that person disappears, the business disappears. Planning for the future protects your family, your employees, and the value you have built.</p>
<p>Thinking ahead also helps you make better decisions today. If you know you want to sell the business in ten years, you will keep better records, build a strong brand, and avoid mixing personal and business money. These habits make the business more valuable to a buyer.</p>

<h2>Succession: Passing the Business to Family</h2>
<p>Many Zambian businesses are family businesses. A father runs a shop, then passes it to his children. But succession fails when it is not planned. The children may not know how to manage stock, deal with suppliers, or handle cash. The father may have kept everything in his head, so there are no written procedures.</p>
<p>To prepare for family succession:</p>
<ol>
<li><strong>Train your successor early</strong> — Let them work in every part of the business while you are still healthy and active. Let them make small mistakes and learn from them.</li>
<li><strong>Write down key processes</strong> — How do you order stock? How do you set prices? Who are your best suppliers? A simple manual, even in an exercise book, is priceless.</li>
<li><strong>Separate ownership and management</strong> — Decide who owns the business and who runs it. Ownership can be shared among children; management should go to the most capable person.</li>
<li><strong>Make a will</strong> — If you die without a will, your business may be tied up in family disputes for years. A simple written will, signed by witnesses, prevents this.</li>
</ol>

<h2>Selling Your Business</h2>
<p>If you want to sell, you need to prove value. A buyer will ask:</p>
<ul>
<li>How much profit does the business make each month? Show bank statements and tax returns.</li>
<li>Does the business have loyal customers? Show a list of regular buyers or a history of repeat sales.</li>
<li>Are the licences and registrations current? A buyer will not pay for a business that might be shut down by the council.</li>
<li>Can the business run without you? If everything depends on your personal relationships, the business is worth less.</li>
</ul>
<p>Start preparing for sale at least two years before you actually want to sell. Clean up your records, settle any debts, and make sure the premises lease can be transferred to a new owner.</p>

<h2>Closing a Business Properly</h2>
<p>Sometimes a business must close. Maybe the market has changed, your health has failed, or a better opportunity has appeared. Closing properly protects your reputation and your credit history:</p>
<ol>
<li>Inform your customers and suppliers in advance. Do not disappear owing people money.</li>
<li>Pay all outstanding debts, including wages, supplier invoices, and taxes.</li>
<li>File a final tax return with ZRA and close your TPIN if necessary.</li>
<li>Sell or give away remaining stock and equipment.</li>
<li>Cancel your trading licence and any other permits so you are not charged renewal fees.</li>
</ol>

<h2>Worked Example: Mr Banda Plans for His Chips Stall</h2>
<p>Mr Banda has run his chips stall for five years. He is now fifty-five and wants to hand it to his son, James, who is twenty. Over the next two years, Mr Banda:</p>
<ol>
<li>Teaches James how to choose good potatoes, manage the fryer safely, and calculate prices.</li>
<li>Writes a one-page manual describing suppliers, opening procedures, and daily cleaning routines.</li>
<li>Opens a separate mobile money till number in James's name and gradually transfers customer payments to the new account.</li>
<li>Updates his will to state that the stall equipment belongs to James.</li>
</ol>
<p>When Mr Banda retires, James takes over smoothly. Customers barely notice the change because the quality and service remain the same.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write a one-page "business manual" for your chosen business. Describe how to open, how to serve customers, and how to close at the end of the day.</li>
<li>List three people who could potentially run your business if you were unable to work for three months. What skills would they need to learn?</li>
<li>Calculate the approximate value of your business if you were to sell it today. Base this on six months of average profit.</li>
<li>Write a short paragraph describing what you want your business to look like in five years. Will you expand, sell, or pass it to family?</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Succession</strong> — the process of passing a business to a new owner or manager, often within a family.</li>
<li><strong>Business valuation</strong> — an estimate of what a business is worth based on profit, assets, and future potential.</li>
<li><strong>Liquidation</strong> — the process of selling a business's assets and closing it down.</li>
<li><strong>Will</strong> — a legal document stating how a person's property and assets should be distributed after death.</li>
<li><strong>Credit history</strong> — a record of how reliably a person or business has borrowed and repaid money.</li>
</ul>

<h2>Summary</h2>
<p>A business that lasts beyond its founder is a true success. Plan for succession by training your successor, writing down processes, and making a will. If you choose to sell, prepare by cleaning up records and proving consistent profit. If you must close, do so honourably by paying debts and informing stakeholders. Mr Banda's handover to his son shows that planning starts years before the actual transition. Think about your legacy today, and your business will reward you tomorrow.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/economics-finance-domain/microeconomics">Khan Academy — Economics and Business Basics</a></li>
<li><a href="https://www.libreoffice.org/get-help/">LibreOffice — Document Templates</a></li>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/">Google Digital Garage — Business Skills</a></li>
</ul>
HTML,
            ],
        ];
    }


    private function module6Quiz(): array
    {
        return [
            'title' => 'Module 6 Quiz: Growing and Sustaining Your Business',
            'description' => 'Test your understanding of hiring, managing growth, and avoiding common causes of business failure.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a sign that a business is ready to hire its first employee?',
                    'explanation' => 'Turning away customers because you are too busy is a strong signal that demand exceeds your personal capacity.',
                    'options' => [
                        ['text' => 'The owner wants to take more holidays', 'is_correct' => false],
                        ['text' => 'Customers are being turned away because the owner is too busy', 'is_correct' => true],
                        ['text' => 'The business has made a profit for one week', 'is_correct' => false],
                        ['text' => 'A competitor has hired three staff', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does an employer in Zambia typically need to deduct from an employee\'s salary and send to ZRA?',
                    'explanation' => 'PAYE (Pay As You Earn) is income tax deducted from wages and remitted to ZRA by the employer.',
                    'options' => [
                        ['text' => 'VAT', 'is_correct' => false],
                        ['text' => 'PAYE', 'is_correct' => true],
                        ['text' => 'Council levy', 'is_correct' => false],
                        ['text' => 'ZESCO token fees', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Unhealthy business growth occurs when sales rise but profit falls because costs are out of control.',
                    'explanation' => 'This is the definition of unhealthy growth: increasing sales without increasing, or while decreasing, profit.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A profitable business can still fail if it does not have enough cash when bills are due.',
                    'explanation' => 'Cash flow problems are a leading cause of business failure, even when the business is technically profitable on paper.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which habit is most associated with long-lasting small businesses?',
                    'explanation' => 'Maintaining an emergency fund helps businesses survive unexpected expenses, slow seasons, and other shocks.',
                    'options' => [
                        ['text' => 'Spending all profit on personal luxuries', 'is_correct' => false],
                        ['text' => 'Keeping an emergency fund of several months\' running costs', 'is_correct' => true],
                        ['text' => 'Never visiting competitors', 'is_correct' => false],
                        ['text' => 'Avoiding all borrowing regardless of circumstances', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why did Mr Chanda visit his competitor\'s shop once a month?',
                    'explanation' => 'Monitoring competitors helps you spot gaps in their offering and opportunities for your own business.',
                    'options' => [
                        ['text' => 'To steal their customers personally', 'is_correct' => false],
                        ['text' => 'To check prices, displays, and product gaps', 'is_correct' => true],
                        ['text' => 'To report them to the council', 'is_correct' => false],
                        ['text' => 'To offer them a partnership', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for using profit to improve the business rather than spending it on personal items? (one word)',
                    'explanation' => 'Reinvestment means putting profits back into the business to fund growth, better stock, or improved equipment.',
                    'correct_answer' => 'Reinvestment',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is the LEAST important trait when choosing your first employee?',
                    'explanation' => 'Experience can be taught; honesty, punctuality, and a good attitude are harder to instil.',
                    'options' => [
                        ['text' => 'Honesty', 'is_correct' => false],
                        ['text' => 'Punctuality', 'is_correct' => false],
                        ['text' => 'Willingness to work hard', 'is_correct' => false],
                        ['text' => 'Previous experience in an unrelated field', 'is_correct' => true],
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
            'title' => 'Business Plan and Costing Project',
            'description' => 'Apply the first three modules by writing a complete business plan and costing exercise for a real or imaginary Zambian business.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Choose a business idea that you could realistically start in your community. Examples: a chips stall, a hair salon, a chicken-rearing project, a grocery shop, or a phone-repair service.

Step 2: Write a one-page business plan using the seven sections from Module 1.3: Business Description, Products or Services, Target Customers, Marketing Plan, Operations Plan, Financial Plan, and Goals.

Step 3: Calculate your costs for one month. List all materials, labour, and overheads with real prices. Use a spreadsheet (LibreOffice Calc, Google Sheets, or Excel) and include formulas for totals.

Step 4: Calculate your cost per unit and propose a selling price with a 30% mark-up. Show your working clearly.

Step 5: Write a short paragraph explaining which funding source you would use to start this business and why.

Submit your business plan as a PDF document and your costing spreadsheet as a .ods, .xlsx, or Google Sheets link.
INSTRUCTIONS,
            'max_points' => 100,
            'passing_points' => 50,
            'due_date' => null,
            'allow_late_submission' => 1,
            'allowed_file_types' => 'pdf,doc,docx,ods,xlsx',
            'max_file_size_mb' => 10,
        ]);

        Assignment::create([
            'course_id' => $this->courseId,
            'lesson_id' => null,
            'title' => 'Complete Business Proposal and Pitch',
            'description' => 'Create a full business proposal that demonstrates understanding of registration, marketing, funding, record keeping, and growth management.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Choose the same business idea from Assignment 1, or a new one if you prefer.

Step 2: Write a three-page proposal covering:
  a) Business concept and target market (Module 1)
  b) Registration checklist: PACRA, TPIN, trading licence, and any special permits (Module 2)
  c) Detailed costing and pricing for at least three products or services, including mark-up and margin calculations (Module 3)
  d) A one-month record-keeping sample: ten transactions in a simple spreadsheet with income, expenses, and profit calculated using formulas (Module 4)
  e) A marketing plan using at least two channels: WhatsApp, posters, radio, or word of mouth (Module 5)
  f) A funding plan: state how much you need, where you would get it, and the main risk (Module 5)
  g) A growth and survival plan: how you would hire your first employee, what emergency savings you would keep, and how you would handle a 20% drop in sales (Module 6)

Step 3: Create a one-minute "elevator pitch" script. Imagine you have sixty seconds to convince a CEEC officer or a bank manager to fund your business. Write the exact words you would say.

Submit your proposal as a PDF and your pitch script as a separate PDF or Word document.
INSTRUCTIONS,
            'max_points' => 100,
            'passing_points' => 50,
            'due_date' => null,
            'allow_late_submission' => 1,
            'allowed_file_types' => 'pdf,doc,docx',
            'max_file_size_mb' => 10,
        ]);
    }

    private function printSummary(): void
    {
        $course = Course::find($this->courseId);
        $moduleCount = $course->modules()->count();
        $lessonCount = $course->lessons()->count();
        $quizCount = $course->quizzes()->count();
        $assignmentCount = $course->assignments()->count();

        $questionCount = Quiz::where('course_id', $this->courseId)
            ->withCount('questions')
            ->get()
            ->sum('questions_count');

        $this->command->info('Certificate in Entrepreneurship content seeded successfully.');
        $this->command->info("Modules: {$moduleCount} | Lessons: {$lessonCount} | Quizzes: {$quizCount} | Questions: {$questionCount} | Assignments: {$assignmentCount}");
    }
}
