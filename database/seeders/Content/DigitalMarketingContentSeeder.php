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

class DigitalMarketingContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Digital Marketing')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Digital Marketing" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in Digital Marketing already has modules. Skipping content seed.');
            return;
        }

        DB::transaction(function () {
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

                    DB::table('quiz_questions')->insert([
                        'quiz_id' => $quiz->id,
                        'question_id' => $question->question_id,
                        'display_order' => $qIndex + 1,
                        'points_override' => null,
                    ]);
                }
            }

            $this->createAssignments(end($lessonIds));
        });

        $this->printSummary();
    }

    private function createModules(): array
    {
        $moduleData = [
            [
                'title' => 'Module 1: Marketing Fundamentals for Your Business',
                'description' => 'Learn the core building blocks of marketing: understanding what you are selling, who you are selling to, and how to price and position your offer so customers choose you over competitors.',
            ],
            [
                'title' => 'Module 2: Social Media and Content Creation with Your Phone',
                'description' => 'Set up free business profiles on WhatsApp and Facebook, then learn how to take attractive photos and videos with your smartphone and write captions that turn viewers into buyers.',
            ],
            [
                'title' => 'Module 3: Getting Found Online and Spending Smart on Ads',
                'description' => 'Make your business visible on Google, learn how to boost posts with small Kwacha budgets, and read simple metrics so you know what is working and what is wasting money.',
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

    private function createAssignments(?int $lastLessonId): void
    {
        $assignmentData = [
            [
                'title' => 'Assignment 1: Create a One-Page Marketing Plan',
                'description' => 'Write a simple but complete marketing plan for a real or imaginary small business in Zambia.',
                'instructions' => "<ol><li>Choose one business idea (for example, a vegetable stall at Soweto Market, a chicken-rearing side business, or a tailoring shop in Kalomo).</li><li>Write one paragraph describing the product or service and the price in ZMW.</li><li>List three customer groups who would buy it (be specific: age, location, income level).</li><li>Write one sentence that explains why someone should choose this business instead of a competitor.</li><li>Describe how you would tell people about it using free methods only (word of mouth, WhatsApp, Facebook, flyers).</li><li>Save your work as a Word document or PDF and upload it here. Name the file: MarketingPlan_YourName.</li></ol>",
                'due_date' => null,
            ],
            [
                'title' => 'Assignment 2: Build a Complete Digital Presence and Content Calendar',
                'description' => 'Create a practical digital marketing kit for a Zambian small business, including social media setup and a one-month content calendar.',
                'instructions' => "<ol><li>Choose the same business from Assignment 1 or a new one.</li><li>Write three sample Facebook posts (each 2-4 sentences) with a photo description for each. One post should announce a new product, one should share a customer testimonial, and one should offer a weekend special.</li><li>Create a simple two-week content calendar in a table format. Include the day, type of post, and a one-sentence caption idea. Plan around a real event (for example, Farmer's Day, payday week, or the start of the farming season).</li><li>Write a short paragraph explaining which post you would boost with K50 and why.</li><li>Save everything in one Word document or PDF and upload it here. Name the file: DigitalKit_YourName.</li></ol>",
                'due_date' => null,
            ],
        ];

        foreach ($assignmentData as $index => $data) {
            Assignment::create([
                'course_id' => $this->courseId,
                'lesson_id' => $lastLessonId,
                'title' => $data['title'],
                'description' => $data['description'],
                'instructions' => $data['instructions'],
                'max_points' => 100,
                'passing_points' => 50,
                'due_date' => $data['due_date'],
                'allow_late_submission' => 1,
                'late_penalty_percent' => 0,
                'max_file_size_mb' => 10,
                'allowed_file_types' => 'pdf,doc,docx,jpg,png',
            ]);
        }
    }

    // =========================================================================
    // MODULE 1
    // =========================================================================

    private function module1Lessons(): array
    {
        return [
            [
                'title' => 'Lesson 1.1: Understanding Your Product, Price, and Customer',
                'duration_minutes' => 60,
                'content' => $this->lesson1_1(),
            ],
            [
                'title' => 'Lesson 1.2: Finding and Speaking to the Right Audience',
                'duration_minutes' => 75,
                'content' => $this->lesson1_2(),
            ],
            [
                'title' => 'Lesson 1.3: Building Your Brand Message',
                'duration_minutes' => 60,
                'content' => $this->lesson1_3(),
            ],
            [
                'title' => 'Module 1 Quiz: Marketing Fundamentals',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Complete this quiz to check your understanding of marketing fundamentals, audience identification, and brand messaging. You need 60% to pass. Good luck!</p>',
            ],
        ];
    }

    private function lesson1_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to describe your product or service in simple terms that a customer understands, set a price that covers your costs and attracts buyers, and explain who your ideal customer is using real details from daily life in Zambia.</p>

<h2>What Is Marketing, Really?</h2>
<p>Marketing is not just shouting about what you sell. It is the whole process of understanding what people need, creating something valuable for them, pricing it fairly, and telling them about it in a way that makes them want to buy. Whether you run a small grocery stall in Kalomo, sell second-hand clothes at Soweto Market, or offer tailoring services from your home, marketing is what turns a passer-by into a paying customer.</p>

<h2>The Three Basics: Product, Price, and Place</h2>
<p>Before you post anything on Facebook or print a single flyer, you must be clear on three things. First, your <strong>product</strong> is not just the item itself; it is the benefit the customer receives. If you sell ZESCO prepaid tokens from your shop, the product is not the paper receipt — it is the convenience of not walking to the main ZESCO office. If you sell roasted maize outside a school, the product is a hot, affordable snack that fills a hungry stomach at break time.</p>
<p>Second, your <strong>price</strong> must cover your costs and leave you with profit, but it must also feel fair to the customer. Let us work through an example. Suppose you sell bags of charcoal. Each bag costs you K35 from the supplier. You spend K5 on transport to bring ten bags to your stand. That means each bag really costs you K40. If you sell at K45, you make K5 profit per bag. But if the competitor across the road sells at K42, you may need to sell at K42 and make only K2 profit, then find a cheaper supplier or sell more volume. This is why knowing your numbers matters.</p>
<p>Third, your <strong>place</strong> is where the customer finds you. A busy corner near the bus station may bring foot traffic, but a WhatsApp Business catalogue can reach customers who are at home during load-shedding. The best place is wherever your customer already spends time.</p>

<h2>Who Is Your Customer?</h2>
<p>A common mistake is saying "everyone is my customer." That is not true. If you sell airtime and MTN MoMo services, your main customers may be people who do not have bank accounts and need to send money to relatives in rural areas. If you sell high-quality hair braiding, your customers may be working women aged 25 to 40 who earn a regular salary and care about looking professional.</p>
<p>Write down three details about your ideal customer: their age range, where they live or work, and one problem they have that your business solves. For example: "Women aged 30 to 50 who work in town offices and need ready-made lunch because they have no time to cook during the week."</p>

<h2>Try It Yourself</h2>
<ol>
<li>Pick one product or service you could sell tomorrow.</li>
<li>Write down the real cost to you, including transport and any packaging.</li>
<li>Set a selling price and calculate your profit per item.</li>
<li>Describe your ideal customer in one sentence with age, location, and need.</li>
<li>Ask two people who match that description whether your price feels fair.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Product</strong>: The item or service you sell, described by the benefit it gives the customer.</li>
<li><strong>Profit</strong>: The money left after you subtract all your costs from your selling price.</li>
<li><strong>Target customer</strong>: The specific group of people most likely to buy from you.</li>
<li><strong>Value proposition</strong>: A simple sentence explaining why someone should buy from you instead of someone else.</li>
<li><strong>Place (distribution)</strong>: The location or channel where customers find and buy your product.</li>
</ul>

<h2>Summary</h2>
<p>Marketing starts long before advertising. It begins with a clear understanding of what you sell, how much it truly costs you, who needs it most, and where those people already gather. Get these basics right, and every advert you place later will work much harder.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/" target="_blank" rel="noopener">Google Digital Garage — Fundamentals of Digital Marketing</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/market-your-business/" target="_blank" rel="noopener">Microsoft Learn — Market Your Business</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal/a/sal-talks-with-ayele-shakur-ceo-of-epic" target="_blank" rel="noopener">Khan Academy — Conversations with Entrepreneurs</a></li>
</ul>
HTML;
    }

    private function lesson1_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to group potential customers into clear segments based on where they live, what they earn, and how they behave, and you will know how to write a simple message that speaks directly to each group's needs.</p>

<h2>Why "Everyone" Is the Wrong Answer</h2>
<p>Imagine you are selling fresh vegetables. If you shout "Nice tomatoes!" at every person who walks past, some will buy, but many will ignore you because they are not cooking today, they already bought vegetables, or they think your tomatoes look too expensive. Now imagine you see a woman in a school uniform blouse carrying a heavy bag and you say, "Madam, these tomatoes are firm — perfect for the week." She is far more likely to stop because your message matches her situation.</p>
<p>This is what audience targeting means. You do not need to reach everybody. You need to reach the right people with the right words at the right time.</p>

<h2>Four Ways to Segment Your Audience</h2>
<p><strong>Geographic segmentation</strong> means grouping people by where they are. A shop in Livingstone may stock more cold drinks and sunscreen because of the heat and tourism. A shop in a farming area near Kalomo may sell more fertilizer, seed, and farming gloves just before the planting season.</p>
<p><strong>Demographic segmentation</strong> uses age, gender, income, and job type. A civil servant who receives a monthly salary on the 28th has different buying habits from a casual labourer who is paid daily. A student needs affordable data bundles and cheap meals. A parent needs school supplies in January and uniforms in December.</p>
<p><strong>Psychographic segmentation</strong> looks at lifestyle and values. Some people buy the cheapest option no matter what. Others will pay more for quality because they value reliability. A mother who worries about her children's health may prefer organic vegetables even if they cost 20% more.</p>
<p><strong>Behavioural segmentation</strong> looks at what people actually do. Do they buy every week or only at month-end? Do they respond to WhatsApp messages or do they prefer to see the product in person? A customer who always pays with Airtel Money may appreciate a small discount for mobile money payment because it saves you the trip to the bank.</p>

<h2>A Worked Example: The Chicken Business</h2>
<p>Let us say you sell live chickens from your backyard in Choma. Here is how you might segment your buyers:</p>
<ul>
<li><strong>Segment A — Restaurants and lodges</strong>: Buy five to ten chickens at once. Want consistent size and reliable supply. Price is less important than reliability.</li>
<li><strong>Segment B — Families for special occasions</strong>: Buy one or two chickens for Christmas, weddings, or funerals. Want healthy-looking birds. Willing to pay a little more for plump chickens.</li>
<li><strong>Segment C — Daily household buyers</strong>: Buy one chicken to cook for the family. Very price-sensitive. Shop on weekends or just after payday.</li>
</ul>
<p>Your message to Segment A should say, "Reliable weekly supply, uniform size, delivery available." Your message to Segment C should say, "Healthy village chickens at K80 each — special price on Saturdays." Same product, different words.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write down one product you sell or want to sell.</li>
<li>Create three segments using any of the methods above. Give each segment a name and one sentence describing them.</li>
<li>Write a different marketing message for each segment — just one sentence.</li>
<li>Show your messages to a friend or family member and ask which one would make them most interested.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Audience segmentation</strong>: Dividing potential customers into smaller groups with shared characteristics.</li>
<li><strong>Demographics</strong>: Measurable traits like age, income, gender, education, and occupation.</li>
<li><strong>Psychographics</strong>: Lifestyle, attitudes, and values that influence buying decisions.</li>
<li><strong>Target market</strong>: The specific segment you choose to focus your marketing efforts on.</li>
<li><strong>Customer persona</strong>: A simple imaginary profile of your ideal customer, used to guide your messaging.</li>
</ul>

<h2>Summary</h2>
<p>Effective marketing speaks to one person at a time, even when many people are listening. By dividing your audience into clear segments and crafting messages that match their needs, fears, and daily habits, you will spend less money on advertising and see better results.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/" target="_blank" rel="noopener">Google Digital Garage — Know Your Audience</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal" target="_blank" rel="noopener">Khan Academy — Entrepreneurship Conversations</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/understand-your-customers/" target="_blank" rel="noopener">Microsoft Learn — Understand Your Customers</a></li>
</ul>
HTML;
    }

    private function lesson1_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to write a short, clear brand message that tells customers what you do, why it matters, and why they should trust you — and you will be able to use that message consistently across your shop, your phone, and your social media.</p>

<h2>What Is a Brand Message?</h2>
<p>Your brand message is not a fancy slogan you pay an agency to create. It is simply the answer to three questions that every customer asks, even if they do not say them out loud: What do you sell? Why should I care? Why should I believe you? If you can answer these three questions in two sentences, you have a brand message.</p>
<p>Think about Zambezi FM radio adverts. The best ones do not list every product in the shop. They say something like, "At Bana Musonda's Kitchen, we cook nshima and relish fresh every morning because we know a hungry worker needs a proper meal. Come taste the difference." That is a brand message. It tells you what they do (fresh meals), why it matters (workers need energy), and why to believe them ("taste the difference").</p>

<h2>The Three Parts of a Strong Message</h2>
<p><strong>Part 1 — The Promise</strong>: What specific result will the customer get? Not "we sell clothes" but "we sell work-ready outfits that help you look professional on your first day." Not "we do hair" but "we braid styles that last three weeks, even in the rainy season."</p>
<p><strong>Part 2 — The Proof</strong>: Why should they believe you? This can be a testimonial, a guarantee, a number, or a detail about how you work. "Over 200 satisfied customers in Kalomo" is proof. "We use quality thread, not cheap imports" is proof. "If your bag tears within 30 days, we repair it free" is proof.</p>
<p><strong>Part 3 — The Personality</strong>: How do you sound? Friendly and neighbourly? Professional and efficient? Fun and youthful? Your personality should match your customer. A funeral service should sound respectful and calm. A children's party planner should sound energetic and cheerful. Pick one tone and stick to it.</p>

<h2>Building Your Message Step by Step</h2>
<p>Let us build a message for a real example: a woman who bakes scones and sells them to offices and shops.</p>
<p>Step 1: The promise. "Freshly baked scones delivered to your shop or office before 8 a.m., so your customers and staff have something warm with their tea."</p>
<p>Step 2: The proof. "Baked every morning with real butter — no margarine. Trusted by five shops along the main road."</p>
<p>Step 3: The personality. Warm, reliable, neighbourly.</p>
<p>Full message: "Start your morning right with scones baked fresh before sunrise using real butter. Delivered to your door by 8 a.m. — because your customers deserve the best. Five local shops already trust us; join them today."</p>

<h2>Keeping Your Message Consistent</h2>
<p>Once you have your message, use it everywhere. Your WhatsApp status should say the same thing as your shop sign. Your Facebook "About" section should repeat it. When a customer asks what you do, say your message out loud. Consistency builds trust. If one place says "cheap and fast" and another says "premium quality," customers will feel confused and shop elsewhere.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write the promise for your business in one sentence. What result does the customer get?</li>
<li>Write one sentence of proof. Use a number, a detail, or a guarantee.</li>
<li>Choose one word that describes your tone: friendly, professional, fun, calm, bold, or warm.</li>
<li>Combine these into a two-sentence message.</li>
<li>Read it to someone who does not know your business. Ask them to tell you what you sell and who it is for.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Brand message</strong>: A clear statement of what you offer, why it matters, and why customers should trust you.</li>
<li><strong>Value proposition</strong>: The specific benefit a customer receives by choosing your business.</li>
<li><strong>Social proof</strong>: Evidence that other people trust you, such as testimonials, reviews, or customer numbers.</li>
<li><strong>Tone of voice</strong>: The personality and emotion in your written and spoken communication.</li>
<li><strong>Consistency</strong>: Using the same message, colours, and tone across all places where customers find you.</li>
</ul>

<h2>Summary</h2>
<p>A strong brand message is short, specific, and repeated often. It answers what you do, why it matters, and why you can be trusted. When your message is the same on your shop sign, your WhatsApp status, and your Facebook page, customers remember you and recommend you with confidence.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/" target="_blank" rel="noopener">Google Digital Garage — Brand Building Basics</a></li>
<li><a href="https://www.canva.com/designschool/" target="_blank" rel="noopener">Canva Design School — Branding for Small Business</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/build-your-brand/" target="_blank" rel="noopener">Microsoft Learn — Build Your Brand</a></li>
</ul>
HTML;
    }

    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: Marketing Fundamentals',
            'description' => 'Test your understanding of product positioning, audience segmentation, and brand messaging.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following best describes a "value proposition"?',
                    'explanation' => 'A value proposition explains why a customer should choose your business over competitors, focusing on the unique benefit they receive.',
                    'options' => [
                        ['text' => 'A list of all the products you sell', 'is_correct' => false],
                        ['text' => 'A sentence explaining why someone should buy from you instead of a competitor', 'is_correct' => true],
                        ['text' => 'The total amount of money you spend on advertising each month', 'is_correct' => false],
                        ['text' => 'A government registration number for your business', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'A shop owner in Kalomo sells ZESCO prepaid tokens. Which of the following is the REAL product they are selling?',
                    'explanation' => 'The real product is the benefit to the customer — convenience — not the physical receipt or the token itself.',
                    'options' => [
                        ['text' => 'A paper receipt from the ZESCO system', 'is_correct' => false],
                        ['text' => 'The convenience of buying electricity without walking to the main ZESCO office', 'is_correct' => true],
                        ['text' => 'A solar panel for charging phones', 'is_correct' => false],
                        ['text' => 'A discount on the next month\'s electricity bill', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which segmentation method groups customers by age, income, and job type?',
                    'explanation' => 'Demographic segmentation uses measurable characteristics like age, income, gender, and occupation.',
                    'options' => [
                        ['text' => 'Geographic segmentation', 'is_correct' => false],
                        ['text' => 'Psychographic segmentation', 'is_correct' => false],
                        ['text' => 'Demographic segmentation', 'is_correct' => true],
                        ['text' => 'Behavioural segmentation', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'A chicken seller has three types of buyers: restaurants, families for special events, and daily household buyers. What is this an example of?',
                    'explanation' => 'Dividing customers into groups based on their needs and buying behaviour is audience segmentation.',
                    'options' => [
                        ['text' => 'Setting prices randomly', 'is_correct' => false],
                        ['text' => 'Audience segmentation', 'is_correct' => true],
                        ['text' => 'Product bundling', 'is_correct' => false],
                        ['text' => 'Government taxation', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should a strong brand message include?',
                    'explanation' => 'A strong brand message includes a clear promise, proof that the promise is real, and a consistent personality or tone.',
                    'options' => [
                        ['text' => 'Only the price of your cheapest item', 'is_correct' => false],
                        ['text' => 'A promise, proof, and a consistent personality', 'is_correct' => true],
                        ['text' => 'A long list of every product you have ever sold', 'is_correct' => false],
                        ['text' => 'Complaints about your competitors', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'The best marketing strategy is to try to sell to everyone who walks past your shop.',
                    'explanation' => 'Trying to sell to everyone usually wastes effort. It is more effective to target specific customer segments with messages that match their needs.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Your brand message should be different on WhatsApp than it is on your shop sign.',
                    'explanation' => 'Consistency builds trust. Your core brand message should be the same everywhere, even if the format changes slightly.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the one word for the money left after you subtract your costs from your selling price? (One word)',
                    'explanation' => 'Profit is the amount remaining after all costs have been deducted from revenue.',
                    'correct_answer' => 'Profit',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What type of segmentation groups customers by where they live or work, such as "Livingstone" versus "Kalomo"? (One word)',
                    'explanation' => 'Geographic segmentation divides markets based on location, climate, or region.',
                    'correct_answer' => 'Geographic',
                ],
            ],
        ];
    }

    // =========================================================================
    // MODULE 2
    // =========================================================================

    private function module2Lessons(): array
    {
        return [
            [
                'title' => 'Lesson 2.1: Setting Up WhatsApp Business and Facebook for Your Shop',
                'duration_minutes' => 75,
                'content' => $this->lesson2_1(),
            ],
            [
                'title' => 'Lesson 2.2: Taking Great Photos and Videos with Your Phone',
                'duration_minutes' => 60,
                'content' => $this->lesson2_2(),
            ],
            [
                'title' => 'Lesson 2.3: Writing Captions and Creating a Simple Content Calendar',
                'duration_minutes' => 75,
                'content' => $this->lesson2_3(),
            ],
            [
                'title' => 'Module 2 Quiz: Social Media and Content Creation',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your knowledge of WhatsApp Business, Facebook pages, mobile photography, and content planning. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson2_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to create a WhatsApp Business profile and a Facebook Page for a small shop, list products with prices and photos, and use basic features like status updates and quick replies to answer customer questions faster.</p>

<h2>Why WhatsApp Business Matters for Zambian Shops</h2>
<p>In Zambia, WhatsApp is not just for chatting with family. It is a shop counter, a catalogue, a payment confirmation tool, and a customer service desk all in one. Many small business owners already use personal WhatsApp to take orders, but switching to WhatsApp Business gives you tools that save time and make you look more professional.</p>
<p>WhatsApp Business is free to download from the Google Play Store or Apple App Store. You can use it on the same phone as your personal WhatsApp if you have a dual-SIM phone, or you can use one SIM for business and one for family. If you only have one number, you can switch your personal account to Business — but back up your chats first.</p>

<h2>Setting Up Your WhatsApp Business Profile</h2>
<p>Step 1: Download WhatsApp Business and verify your business phone number.</p>
<p>Step 2: Tap the three dots menu, go to Settings, then Business Tools, then Business Profile. Fill in every field:</p>
<ul>
<li><strong>Business name</strong>: Use the exact name customers know you by. If your shop sign says "Bana Chileshe Fresh Produce," use that name.</li>
<li><strong>Category</strong>: Pick the closest match, such as "Grocery Store" or "Clothing Store."</li>
<li><strong>Description</strong>: Write two sentences using your brand message. Example: "Fresh vegetables and fruits delivered to your door in Kalomo. Order by 6 p.m. for next-morning delivery."</li>
<li><strong>Address</strong>: Add your area or a landmark people know, such as "Opposite Kalomo Central Market, near the bus station."</li>
<li><strong>Hours</strong>: Be honest. If you open at 8 a.m. and close at 6 p.m., say so. Customers lose trust if they arrive and find you closed.</li>
<li><strong>Email and website</strong>: If you have a Facebook page, put the link here.</li>
</ul>
<p>Step 3: Add a profile photo. Use your shop sign, your logo, or a clear photo of your best product. Avoid blurry images or pictures of your children — this is a business account.</p>

<h2>Using the Catalogue Feature</h2>
<p>The catalogue lets customers browse your products without you typing prices every time. To add a product:</p>
<ol>
<li>Go to Business Tools, then Catalogue.</li>
<li>Tap "Add Product or Service."</li>
<li>Add a clear photo, the product name, a short description, and the price in ZMW.</li>
<li>Tap Save.</li>
</ol>
<p>Add at least five products to start. A customer who sees a full catalogue trusts you more than one who sees an empty page. Update prices immediately when they change. Nothing frustrates a customer more than seeing K45 in the catalogue and being told K55 in the chat.</p>

<h2>Creating a Facebook Page</h2>
<p>A Facebook Page is like a free website. It helps people find you when they search "tailor Kalomo" or "fresh fish Choma." To create one:</p>
<ol>
<li>Open the Facebook app, tap the menu, and scroll to "Pages."</li>
<li>Tap "Create" and choose "Business or Brand."</li>
<li>Enter your page name (same as your WhatsApp Business name) and category.</li>
<li>Add a profile picture and cover photo. The cover photo should show what you do — a flat lay of your products, your shop front, or a happy customer holding their purchase.</li>
<li>Fill in the About section with your description, hours, phone number, and WhatsApp link.</li>
<li>Post your first update: a photo of a product with the price and how to order.</li>
</ol>

<h2>Quick Replies and Labels</h2>
<p>Quick replies save you from typing the same message fifty times a day. Set up quick replies for common questions like "What are your prices?" and "How do I pay?" Go to Business Tools, Quick Replies, and create shortcuts. For example, type "/price" and WhatsApp will insert your full price list.</p>
<p>Labels help you organise chats. Use labels like "New Customer," "Pending Payment," "Paid — Ready for Delivery," and "Completed." This is especially useful during busy times like month-end or before holidays.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Download WhatsApp Business and create your profile using the steps above.</li>
<li>Add at least three products to your catalogue with clear photos and prices.</li>
<li>Create a Facebook Page with the same name and post one photo with a caption.</li>
<li>Set up two quick replies for your most common customer questions.</li>
<li>Ask a friend to find your page and tell you whether they understand what you sell within ten seconds.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>WhatsApp Business</strong>: A free app designed for small business owners, with catalogue, quick replies, and labels.</li>
<li><strong>Catalogue</strong>: A digital product list inside WhatsApp Business where customers can browse items and prices.</li>
<li><strong>Quick reply</strong>: A saved message triggered by a shortcut, used to answer frequent questions faster.</li>
<li><strong>Facebook Page</strong>: A public profile on Facebook for a business, organisation, or public figure.</li>
<li><strong>Cover photo</strong>: The large banner image at the top of a Facebook Page.</li>
</ul>

<h2>Summary</h2>
<p>WhatsApp Business and Facebook are powerful, free tools that turn your phone into a digital shop front. A complete profile, an up-to-date catalogue, and organised labels build trust and save you hours of repetitive messaging every week.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/business/answer/3039617" target="_blank" rel="noopener">Google Business Profile Help — Getting Started</a></li>
<li><a href="https://www.facebook.com/business/help" target="_blank" rel="noopener">Meta Business Help Centre</a></li>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/" target="_blank" rel="noopener">Google Digital Garage — Social Media Strategy</a></li>
</ul>
HTML;
    }

    private function lesson2_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to take clear, attractive photos and short videos using only your smartphone, edit them lightly using free apps, and choose the best shots to post on social media or send to customers.</p>

<h2>Your Phone Is Enough</h2>
<p>You do not need a professional camera to market your business. Most smartphones made after 2019 have cameras good enough for social media. What matters more than the device is the light, the angle, and the story your photo tells. A well-lit photo of a K15 plate of nshima and relish taken on a K1,200 phone will outperform a dark, blurry photo taken on an expensive camera every time.</p>

<h2>Step 1: Use Natural Light</h2>
<p>The biggest mistake in phone photography is shooting in the dark. In Zambia, we are blessed with strong sunlight for most of the year. Use it. The best light is soft morning light (before 9 a.m.) or late afternoon light (after 4 p.m.). Midday sun creates harsh shadows, so if you must shoot at noon, place your product in open shade — under a tree, an awning, or just inside a doorway where direct sun does not hit it.</p>
<p>Never use your phone's flash for product photos. It makes food look greasy, fabrics look shiny, and faces look pale. If the room is dark, move near a window or step outside.</p>

<h2>Step 2: Clean Your Lens and Compose the Shot</h2>
<p>Your phone lives in your pocket or handbag. The lens collects dust and fingerprints. Before every shoot, wipe it gently with a soft cloth or the edge of your chitenge. You will be amazed how much sharper your photos become.</p>
<p>For composition, use the rule of thirds. Imagine your screen divided into nine squares. Place the most important part of your photo — the product, the person's face, or the detail you want to show — where the lines cross, not dead in the centre. Most phones have a grid option in the camera settings; turn it on.</p>
<p>Shoot from multiple angles. If you sell handbags, take one photo from the front, one from the side to show depth, one of the inside to show compartments, and one of the strap detail. Give the customer the same view they would get if they picked it up in a shop.</p>

<h2>Step 3: Keep the Background Simple</h2>
<p>A busy background distracts from your product. If you are photographing tomatoes, do not place them on a patterned tablecloth with a pile of dirty dishes behind them. Use a plain surface: a clean wooden table, a white plate, a neutral chitenge spread flat, or even a clean concrete floor. The product should be the star.</p>

<h2>Step 4: Shoot Short Videos That Sell</h2>
<p>Video gets more attention than photos on Facebook and WhatsApp Status. You do not need a script or editing software. A 15-second video of steam rising from fresh mandasi, or a 20-second clip of you wrapping a customer's purchase in neat paper, tells a story that a photo cannot.</p>
<p>Hold your phone horizontally for Facebook and vertically for WhatsApp Status. Keep your hands steady — brace your elbows against your body. Do not zoom with your fingers; walk closer instead. Digital zoom reduces quality. Keep videos under 45 seconds unless you are explaining a process step by step.</p>

<h2>Step 5: Edit Lightly with Free Apps</h2>
<p>Use free apps like Snapseed (Google) or the built-in editor in your phone's Gallery app. Do not over-edit. Small adjustments are enough:</p>
<ul>
<li><strong>Brightness</strong>: Increase slightly if the photo looks dark.</li>
<li><strong>Contrast</strong>: Increase a little to make colours pop.</li>
<li><strong>Crop</strong>: Remove distracting edges.</li>
<li><strong>Sharpen</strong>: Add a small amount if the photo looks soft.</li>
</ul>
<p>Avoid heavy filters that change colours unnaturally. A customer who orders a red dress based on a filtered photo that looks pink will be disappointed when it arrives.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose one product you sell.</li>
<li>Take five photos of it using the light, angle, and background tips above.</li>
<li>Pick your best photo and edit it using only brightness, contrast, and crop.</li>
<li>Take a 15-second video of the product in use or being prepared.</li>
<li>Post the photo and video to your WhatsApp Status and ask three friends which one makes them want to buy.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Natural light</strong>: Illumination from the sun, used instead of artificial lights or camera flash.</li>
<li><strong>Rule of thirds</strong>: A composition technique that places the subject off-centre for a more balanced image.</li>
<li><strong>Composition</strong>: The arrangement of visual elements within a photograph.</li>
<li><strong>Digital zoom</strong>: Magnifying an image using software, which reduces photo quality.</li>
<li><strong>Aspect ratio</strong>: The proportional relationship between width and height of an image or video.</li>
</ul>

<h2>Summary</h2>
<p>Great product photography is about light, simplicity, and story. With a clean lens, natural sunlight, a plain background, and light editing, your smartphone can produce images that compete with much more expensive setups. Practice every day, and you will develop an eye for what sells.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool/" target="_blank" rel="noopener">Canva Design School — Photography Basics</a></li>
<li><a href="https://support.google.com/photos/answer/6128858" target="_blank" rel="noopener">Google Photos Help — Edit Photos</a></li>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/" target="_blank" rel="noopener">Google Digital Garage — Content Creation</a></li>
</ul>
HTML;
    }

    private function lesson2_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to write social media captions that encourage customers to act, plan two weeks of posts using a simple content calendar, and time your posts around Zambian events and buying patterns.</p>

<h2>What Makes a Good Caption?</h2>
<p>A caption is the text that goes with your photo or video. A bad caption says what the photo already shows: "Here is a dress." A good caption tells the customer why the dress matters to them: "This cotton dress keeps you cool during load-shedding evenings — no ironing needed. Perfect for church or the office. K120. WhatsApp us to reserve your size."</p>
<p>Every good caption does three things: it catches attention, it gives useful information, and it tells the reader what to do next. That final instruction is called a <strong>call to action</strong>. Without it, people scroll past. With it, they message you.</p>

<h2>The CAPTION Formula</h2>
<p>Use this simple structure until it becomes natural:</p>
<ul>
<li><strong>C — Catch attention</strong>: Ask a question or make a bold statement. "Tired of buying tomatoes that go soft in two days?"</li>
<li><strong>A — Add value</strong>: Explain what makes this product useful. "These tomatoes come straight from the farmer this morning."</li>
<li><strong>P — Prove it</strong>: Add social proof or a detail. "Sold ten crates already this week."</li>
<li><strong>T — Tell them what to do</strong>: Give a clear next step. "Tap the link in our bio or WhatsApp 0977-123456 to order."</li>
<li><strong>I — Include urgency</strong>: Give a reason to act now. "Delivery slots for tomorrow are filling fast."</li>
<li><strong>O — Offer a bonus</strong>: Sweeten the deal. "Free bag of onions with orders over K100."</li>
<li><strong>N — Note your brand</strong>: Sign off consistently. "— Bana Chileshe Fresh Produce, Kalomo."</li>
</ul>
<p>You do not need to use every letter every time, but always include at least Catch, Add value, and Tell them what to do.</p>

<h2>Planning Around Zambian Rhythms</h2>
<p>Your customers do not buy at random. They buy in patterns you can predict. Civil servants and formal workers receive salaries around the 28th of each month. The week after payday is your busiest sales period. Plan your biggest promotions for the 29th to the 5th. The third week of the month is usually quieter — this is a good time for smaller offers, reminders, and engagement posts that build trust without asking for money.</p>
<p>Farming seasons also affect buying. Just before planting season (October-November), farmers buy inputs and may have less cash for extras. After harvest (May-June), they have money to spend — this is when luxury items, home improvements, and celebrations sell well.</p>
<p>Holidays and events create natural marketing moments. Mother's Day, Father's Day, Farmer's Day, Independence Day, and Christmas are all opportunities. Plan your content calendar at least two weeks ahead so you are not scrambling for a photo on the morning of the holiday.</p>

<h2>A Simple Two-Week Content Calendar</h2>
<p>Here is an example for a small grocery and produce shop:</p>
<table>
<tr><th>Day</th><th>Type</th><th>Post Idea</th></tr>
<tr><td>Monday</td><td>Product</td><td>Photo of fresh okra with price and ordering instructions</td></tr>
<tr><td>Tuesday</td><td>Engagement</td><td>Poll: "What vegetable should we stock more of this week?"</td></tr>
<tr><td>Wednesday</td><td>Behind the scenes</td><td>Short video of early morning market shopping</td></tr>
<tr><td>Thursday</td><td>Testimonial</td><td>Screenshot of a happy customer message</td></tr>
<tr><td>Friday</td><td>Offer</td><td>Weekend special: "Buy two chickens, get free seasoning"</td></tr>
<tr><td>Saturday</td><td>Product</td><td>Photo of a complete meal bundle with total price</td></tr>
<tr><td>Sunday</td><td>Rest or inspiration</td><td>Quote about hard work and community</td></tr>
</table>
<p>Repeat with small variations for the second week. The goal is consistency, not complexity. Posting three to five times per week is better than posting ten times one week and nothing the next.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write three captions for one product using the CAPTION formula.</li>
<li>Draw a simple two-week calendar on paper. Plan one post for each day.</li>
<li>Look at a calendar and identify the next Zambian holiday or payday week. Write one post idea for that event.</li>
<li>Post one caption today and track how many replies or orders it generates compared to your usual posts.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Caption</strong>: The text that accompanies a photo or video on social media.</li>
<li><strong>Call to action (CTA)</strong>: A clear instruction telling the reader what to do next, such as "message us" or "tap to order."</li>
<li><strong>Content calendar</strong>: A schedule that plans what you will post and when.</li>
<li><strong>Engagement post</strong>: Content designed to start a conversation rather than make a direct sale.</li>
<li><strong>Social proof</strong>: Evidence that other people trust and buy from you, such as testimonials or sales numbers.</li>
</ul>

<h2>Summary</h2>
<p>Good captions turn viewers into buyers by catching attention, adding value, and asking for action. A simple content calendar keeps you consistent and helps you ride the natural buying rhythms of Zambian life — paydays, farming seasons, and holidays. Plan ahead, post regularly, and watch your engagement grow.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool/" target="_blank" rel="noopener">Canva Design School — Social Media Content</a></li>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/" target="_blank" rel="noopener">Google Digital Garage — Social Media Content Strategy</a></li>
<li><a href="https://www.facebook.com/business/help" target="_blank" rel="noopener">Meta Business Help Centre — Creating Content</a></li>
</ul>
HTML;
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Social Media and Content Creation',
            'description' => 'Assess your understanding of WhatsApp Business setup, mobile photography, caption writing, and content planning.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which WhatsApp Business feature lets customers browse your products and prices without you typing them each time?',
                    'explanation' => 'The catalogue is a built-in product list where customers can view items, descriptions, and prices independently.',
                    'options' => [
                        ['text' => 'Quick replies', 'is_correct' => false],
                        ['text' => 'Labels', 'is_correct' => false],
                        ['text' => 'Catalogue', 'is_correct' => true],
                        ['text' => 'Status updates', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the best type of light for product photography using a smartphone?',
                    'explanation' => 'Natural light, especially in the early morning or late afternoon, produces the most flattering product images without harsh shadows.',
                    'options' => [
                        ['text' => 'Phone flash in a dark room', 'is_correct' => false],
                        ['text' => 'Direct midday sun', 'is_correct' => false],
                        ['text' => 'Soft natural light from a window or open shade', 'is_correct' => true],
                        ['text' => 'Neon tube lights', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In the CAPTION formula, what does the "T" stand for?',
                    'explanation' => 'The "T" in CAPTION stands for "Tell them what to do" — the call to action that guides the customer to the next step.',
                    'options' => [
                        ['text' => 'Type fast', 'is_correct' => false],
                        ['text' => 'Tell them what to do', 'is_correct' => true],
                        ['text' => 'Tag a friend', 'is_correct' => false],
                        ['text' => 'Take a photo', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why should you avoid using heavy filters on product photos?',
                    'explanation' => 'Heavy filters change colours unnaturally, which can mislead customers and cause disappointment when the real product arrives.',
                    'options' => [
                        ['text' => 'Because filters make files too large to upload', 'is_correct' => false],
                        ['text' => 'Because customers prefer black-and-white images', 'is_correct' => false],
                        ['text' => 'Because they can change colours and mislead buyers', 'is_correct' => true],
                        ['text' => 'Because Facebook bans filtered photos', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'When is the best time to run a major promotion for customers with formal salaries in Zambia?',
                    'explanation' => 'Most formal workers are paid around the 28th, so the days immediately following payday have the highest spending power.',
                    'options' => [
                        ['text' => 'The third week of the month', 'is_correct' => false],
                        ['text' => 'The week after payday (around the 29th to 5th)', 'is_correct' => true],
                        ['text' => 'Only on public holidays', 'is_correct' => false],
                        ['text' => 'Early morning on Mondays', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main purpose of a content calendar?',
                    'explanation' => 'A content calendar helps you plan what to post and when, ensuring consistency and reducing last-minute stress.',
                    'options' => [
                        ['text' => 'To automatically post content without your input', 'is_correct' => false],
                        ['text' => 'To plan posts in advance and maintain consistency', 'is_correct' => true],
                        ['text' => 'To store customer phone numbers', 'is_correct' => false],
                        ['text' => 'To calculate your monthly profit', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'You should use your phone flash when taking product photos indoors at night.',
                    'explanation' => 'Phone flash creates harsh, unflattering light. It is better to move near a window or wait for daylight.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A Facebook Page is free to create and can help customers find your business when they search online.',
                    'explanation' => 'Facebook Pages are free public profiles that appear in search results, making them a valuable discovery tool.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for a clear instruction in a caption that tells the reader what to do next? (Two words)',
                    'explanation' => 'A call to action (CTA) directs the audience toward a specific response, such as "message us" or "order now."',
                    'correct_answer' => 'Call to action',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What free WhatsApp Business tool lets you save and reuse common messages using a shortcut like "/price"? (Two words)',
                    'explanation' => 'Quick replies allow you to store frequently sent messages and insert them instantly with a short code.',
                    'correct_answer' => 'Quick replies',
                ],
            ],
        ];
    }

    // =========================================================================
    // MODULE 3
    // =========================================================================

    private function module3Lessons(): array
    {
        return [
            [
                'title' => 'Lesson 3.1: Google Business Profile and Local Search',
                'duration_minutes' => 60,
                'content' => $this->lesson3_1(),
            ],
            [
                'title' => 'Lesson 3.2: Boosting Posts and Running Small-Budget Adverts',
                'duration_minutes' => 75,
                'content' => $this->lesson3_2(),
            ],
            [
                'title' => 'Lesson 3.3: Measuring What Works — Insights, Reach, and Sales',
                'duration_minutes' => 60,
                'content' => $this->lesson3_3(),
            ],
            [
                'title' => 'Module 3 Quiz: Getting Found and Spending Smart',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your knowledge of Google Business Profile, paid advertising basics, and measuring marketing results. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson3_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to create and verify a Google Business Profile for a local shop, add correct hours and photos, and understand how it helps customers find you when they search for businesses like yours on Google.</p>

<h2>What Is Google Business Profile?</h2>
<p>When someone searches "tailor near me" or "best nshima place Kalomo" on Google, a map appears with local businesses. Those businesses did not pay Google to appear there. They simply claimed their free Google Business Profile. This profile shows your business name, address, phone number, hours, photos, reviews, and a link to call or message you directly.</p>
<p>For a small business in Zambia, this is one of the most powerful free tools available. It puts you on the same map as big competitors. A customer who has never heard of you can find you, see your photos, read a review, and call you in under a minute.</p>

<h2>Creating Your Profile Step by Step</h2>
<p>Step 1: Go to <a href="https://business.google.com" target="_blank" rel="noopener">business.google.com</a> on your phone or computer. Sign in with a Google account. If you do not have one, create one using your business email or phone number.</p>
<p>Step 2: Search for your business name. If it already appears (because Google found it on a directory or website), claim it. If it does not appear, click "Add your business" and enter your name exactly as customers know it.</p>
<p>Step 3: Choose your category. Be specific. "Restaurant" is too broad. "Zambian restaurant" or "Fast food restaurant" is better. You can add up to ten categories, but the first one is the most important.</p>
<p>Step 4: Add your location. If you serve customers at a physical shop, enter your address. If you deliver or work from home and do not want your home address public, you can hide the street address and list only your area or city. However, a visible address helps you appear on Google Maps.</p>
<p>Step 5: Add your service area. If you deliver, list the towns or areas you cover. A shop in Kalomo might list "Kalomo, Choma, Livingstone" if they deliver that far.</p>
<p>Step 6: Add contact details. Use the same phone number and WhatsApp number you use for business. Consistency helps Google confirm you are real.</p>
<p>Step 7: Verify your business. Google usually sends a postcard with a code to your address. This takes one to two weeks. When the card arrives, log in and enter the code. Until you verify, your profile will not appear on Google Maps.</p>

<h2>Optimising Your Profile</h2>
<p>After verification, add at least five high-quality photos: your shop front, your best product, your team, your interior, and one photo of a happy customer (with their permission). Businesses with photos receive 42% more requests for directions and 35% more clicks to their websites, according to Google's own research.</p>
<p>Keep your hours accurate. If you close early on Sundays or public holidays, update the profile. Nothing annoys a customer more than driving to a shop that Google says is open but finding it locked.</p>
<p>Ask happy customers to leave reviews. A simple message works: "Thank you for shopping with us today. If you have a moment, could you leave us a review on Google? It helps other people find us." Respond to every review, good or bad. A polite reply to a negative review shows professionalism.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a Google account for your business if you do not have one.</li>
<li>Go to business.google.com and start your profile.</li>
<li>Enter your business name, category, address, and phone number exactly as they appear on your shop sign and WhatsApp.</li>
<li>Upload five photos that show what your business looks like and what you sell.</li>
<li>Request verification and wait for the postcard. When it arrives, complete verification.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Google Business Profile</strong>: A free tool that lets businesses manage how they appear on Google Search and Maps.</li>
<li><strong>Local search</strong>: A search query that includes a location or uses "near me" to find nearby businesses.</li>
<li><strong>Verification</strong>: The process Google uses to confirm a business is real, usually by sending a postcard with a code.</li>
<li><strong>Service area</strong>: The geographic region a business serves, especially for delivery or mobile services.</li>
<li><strong>Review</strong>: A customer rating and comment left on a business profile that influences other buyers.</li>
</ul>

<h2>Summary</h2>
<p>Google Business Profile is a free, powerful way to appear on Google Maps and Search. A complete, verified profile with accurate hours, good photos, and positive reviews helps local customers find and trust your business before they ever visit.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/business/answer/3039617" target="_blank" rel="noopener">Google Business Profile Help — Getting Started</a></li>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/" target="_blank" rel="noopener">Google Digital Garage — Local SEO Basics</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/improve-visibility-search/" target="_blank" rel="noopener">Microsoft Learn — Improve Visibility in Search</a></li>
</ul>
HTML;
    }

    private function lesson3_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to boost a Facebook post for as little as K50, choose the right audience for a small-budget advert, and understand the difference between boosting a post and running a proper ad campaign so you do not waste money.</p>

<h2>Why Even Small Budgets Can Work</h2>
<p>You do not need K5,000 to advertise on Facebook. In fact, a well-targeted K50 boost can reach more of the right people than a badly targeted K500 campaign. The secret is not the amount of money; it is who you show the advert to and what the advert says.</p>
<p>In Zambia, many small business owners are afraid of paid advertising because they think it is only for big companies. This is not true. A mother selling homemade peanut butter can boost a post for K30 and reach 2,000 people in Lusaka who have shown interest in organic food. That is more efficient than printing 500 flyers that most people throw away.</p>

<h2>Boosting a Post vs Running an Ad</h2>
<p><strong>Boosting</strong> is the simplest option. You write a normal Facebook post, add a photo, then click "Boost Post." You choose how much to spend, how many days to run it, and who should see it. Facebook does the rest. Boosting is best for beginners and for promoting a single product or offer quickly.</p>
<p><strong>Running an ad</strong> through Meta Ads Manager gives you more control. You can choose different objectives (page likes, website clicks, messages, or sales), create multiple versions of the same advert to see which works best, and target very specific audiences. However, Ads Manager has more buttons and settings, so it is better to learn boosting first.</p>

<h2>How to Boost a Post Wisely</h2>
<p>Step 1: Choose the right post. Do not boost a post that says "Good morning everyone." Boost a post that has a clear offer, a good photo, and a call to action. Example: "Freshly ground peanut butter, no sugar added. K35 for 500g. Delivery available in Lusaka. Message us to order."</p>
<p>Step 2: Set your audience. Facebook lets you choose by location, age, gender, and interests. For a local shop, set the location to your town plus 10 to 15 kilometres. If you only deliver within town, do not waste money showing the advert to people in Kitwe when you are in Ndola. Set the age range to match your typical customer. If you sell baby clothes, target women aged 22 to 40. If you sell phone accessories, target everyone aged 18 to 45.</p>
<p>Step 3: Set your budget and duration. A K50 budget over five days is usually better than K50 in one day. Spreading the budget gives Facebook more time to learn who responds to your advert and show it to similar people. Start small. If the K50 boost brings you three sales, then you know a K100 boost might bring six.</p>
<p>Step 4: Choose where the advert appears. For small budgets, "Automatic Placements" is fine. Facebook will show your advert in the News Feed, Stories, and Messenger where it performs best.</p>

<h2>What to Watch Out For</h2>
<p>Do not boost a post with a blurry photo or a confusing message. You are paying for attention; make sure you capture it in the first two seconds. Do not target too broad an audience. "Everyone in Zambia aged 18 to 65" is a waste of money. Be specific. Do not run an advert without checking your inbox. If 50 people comment "How much?" and you reply two days later, you have lost them.</p>

<h2>A Worked Example: K50 Boost for a Hair Braiding Salon</h2>
<p>Post: "Back-to-work special: Cornrows with extensions, K80. Bookings open for this week. WhatsApp 0977-123456 or message us here."</p>
<p>Audience: Women aged 20 to 45, located in Ndola plus 10km, interests: "beauty salons," "hair care," "fashion."</p>
<p>Budget: K50 over five days (K10 per day).</p>
<p>Result: Reaches approximately 1,500 to 2,500 people. If even 2% message you, that is 30 to 50 potential customers for K50. If ten of them book, your K50 has generated K800 in revenue.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write one Facebook post with a clear offer, a good photo, and a call to action.</li>
<li>Before boosting it, ask two friends whether they understand what you are selling and how to buy it.</li>
<li>Set a K30 to K50 budget, target your town plus 10km, and choose the right age and gender for your product.</li>
<li>Run the boost for three to five days.</li>
<li>Track how many messages, comments, or sales you receive. Write down the cost per result.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Boosted post</strong>: A regular Facebook post that you pay to show to more people.</li>
<li><strong>Target audience</strong>: The specific group of people you choose to see your advert based on location, age, interests, and behaviour.</li>
<li><strong>Budget</strong>: The total amount of money you are willing to spend on an advert or boosted post.</li>
<li><strong>Reach</strong>: The number of unique people who see your advert.</li>
<li><strong>Engagement</strong>: Actions people take on your post, such as likes, comments, shares, and clicks.</li>
</ul>

<h2>Summary</h2>
<p>Paid advertising on Facebook is affordable and effective when you start small, target tightly, and choose posts that already have a clear offer. A K50 boost aimed at the right people in your town can bring more customers than expensive print adverts ever could.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.facebook.com/business/help" target="_blank" rel="noopener">Meta Business Help Centre — Advertising Basics</a></li>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/" target="_blank" rel="noopener">Google Digital Garage — Online Advertising</a></li>
<li><a href="https://www.canva.com/designschool/" target="_blank" rel="noopener">Canva Design School — Social Media Ads</a></li>
</ul>
HTML;
    }

    private function lesson3_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to read basic Facebook and WhatsApp metrics, calculate simple return on investment for a paid post, and decide whether to repeat, adjust, or stop a marketing activity based on real numbers rather than guesswork.</p>

<h2>Why Numbers Matter More Than Likes</h2>
<p>Many small business owners celebrate when a post gets 200 likes. Likes feel good, but they do not pay school fees. What matters is whether those likes turned into messages, orders, or walk-in customers. Marketing without measurement is like driving to Lusaka with your eyes closed. You might get there, but you are more likely to crash.</p>
<p>The good news is that you do not need complicated software. Facebook, WhatsApp Business, and even a simple notebook can give you the numbers you need to make smart decisions.</p>

<h2>Facebook Insights: What to Look At</h2>
<p>On your Facebook Page, tap "Insights" or "Professional Dashboard." Here are the three numbers that actually matter for a small business:</p>
<ul>
<li><strong>Reach</strong>: How many unique people saw your post. If your reach is low, your content is not being shown. Try posting at different times or using more engaging photos and videos.</li>
<li><strong>Engagement</strong>: The total of likes, comments, shares, and clicks. Divide engagement by reach to get your engagement rate. A rate above 3% is good for a small page. Below 1% means your content needs work.</li>
<li><strong>Link clicks / messages started</strong>: How many people took action. This is the most important number. If 1,000 people saw your post but only two clicked to message you, the post was not convincing enough.</li>
</ul>

<h2>Tracking WhatsApp Business Numbers</h2>
<p>WhatsApp Business does not give fancy graphs, but it gives useful data if you pay attention. Check these weekly:</p>
<ul>
<li>How many people viewed your catalogue?</li>
<li>How many messages did you receive?</li>
<li>How many of those messages turned into orders?</li>
<li>How many people saved your number or shared your status?</li>
</ul>
<p>Keep a simple notebook or phone note with these numbers every Sunday evening. After four weeks, you will see patterns. You might notice that posts on Friday evenings get more messages than posts on Monday mornings. Act on that pattern.</p>

<h2>Calculating Return on Investment (ROI)</h2>
<p>Return on investment tells you whether your marketing money is coming back to you with profit. The formula is simple:</p>
<blockquote>
<p>ROI = (Money you made from the advert − Money you spent on the advert) ÷ Money you spent on the advert × 100</p>
</blockquote>
<p>Here is a real example. You boost a post for K50. Five people message you. Two of them buy a dress at K120 each. Your total revenue is K240. Your profit margin on each dress is K40, so your total profit from the advert is K80.</p>
<blockquote>
<p>ROI = (K80 − K50) ÷ K50 × 100 = 60%</p>
</blockquote>
<p>A 60% ROI means every K1 you spent returned K1.60 in profit. That is a good result. If your ROI is negative, you spent more than you made. That does not mean advertising does not work; it means you need to change the audience, the message, or the product.</p>

<h2>When to Repeat, Adjust, or Stop</h2>
<table>
<tr><th>Result</th><th>Action</th></tr>
<tr><td>High reach, high messages, good sales</td><td>Repeat the same post with a slightly larger budget</td></tr>
<tr><td>High reach, low messages</td><td>Adjust the caption or call to action; the audience is right but the message is weak</td></tr>
<tr><td>Low reach, low everything</td><td>Change the photo, the time of posting, or the audience targeting</td></tr>
<tr><td>Good messages but no sales</td><td>The price may be too high, or the product may not match the promise</td></tr>
<tr><td>Negative ROI after three tries</td><td>Stop paid ads for now and focus on free organic posting</td></tr>
</table>

<h2>Try It Yourself</h2>
<ol>
<li>Look at your last five Facebook posts. Write down the reach and engagement for each.</li>
<li>Calculate the engagement rate for each post (engagement ÷ reach × 100).</li>
<li>Identify which post had the highest engagement rate. What was different about it?</li>
<li>If you have ever boosted a post, calculate its ROI using the formula above.</li>
<li>Set one simple marketing goal for next week, such as "Get ten WhatsApp messages from Facebook posts." Track it daily.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Reach</strong>: The number of unique individuals who see your content.</li>
<li><strong>Engagement rate</strong>: The percentage of people who interact with your content out of those who saw it.</li>
<li><strong>Return on investment (ROI)</strong>: A measure of profitability calculated by comparing profit gained to money spent.</li>
<li><strong>Conversion</strong>: When a viewer takes the desired action, such as sending a message or making a purchase.</li>
<li><strong>Metrics</strong>: Quantitative measurements used to track and assess marketing performance.</li>
</ul>

<h2>Summary</h2>
<p>Likes are nice, but sales pay the bills. By tracking reach, engagement, and conversion weekly, and by calculating ROI for every paid post, you replace guesswork with facts. Use those facts to repeat what works, fix what underperforms, and stop what wastes your Kwacha.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/" target="_blank" rel="noopener">Google Digital Garage — Marketing Analytics</a></li>
<li><a href="https://www.facebook.com/business/help" target="_blank" rel="noopener">Meta Business Help Centre — Page Insights</a></li>
<li><a href="https://www.khanacademy.org/math/probability/data-distributions-overview" target="_blank" rel="noopener">Khan Academy — Data and Statistics Basics</a></li>
</ul>
HTML;
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Getting Found and Spending Smart',
            'description' => 'Assess your understanding of Google Business Profile, paid advertising, and measuring marketing results.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main purpose of a Google Business Profile?',
                    'explanation' => 'A Google Business Profile helps local businesses appear on Google Search and Maps when people search for services nearby.',
                    'options' => [
                        ['text' => 'To sell products directly on Google Shopping', 'is_correct' => false],
                        ['text' => 'To help customers find your business in local search and on Google Maps', 'is_correct' => true],
                        ['text' => 'To send bulk emails to customers', 'is_correct' => false],
                        ['text' => 'To create a website for free', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is the BEST audience targeting for a small K50 boost by a hair salon in Ndola?',
                    'explanation' => 'Tight targeting by location, age, gender, and relevant interests gives the best return on a small budget.',
                    'options' => [
                        ['text' => 'Everyone in Zambia aged 18 to 65', 'is_correct' => false],
                        ['text' => 'Women aged 20 to 45 in Ndola plus 10km with interests in beauty and hair care', 'is_correct' => true],
                        ['text' => 'Only men aged 18 to 30', 'is_correct' => false],
                        ['text' => 'People who have never used Facebook before', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does "engagement rate" measure?',
                    'explanation' => 'Engagement rate compares the number of interactions to the number of people who saw the content, showing how effective the post is.',
                    'options' => [
                        ['text' => 'The total amount of money spent on advertising', 'is_correct' => false],
                        ['text' => 'The percentage of viewers who interact with your content', 'is_correct' => true],
                        ['text' => 'The speed of your internet connection', 'is_correct' => false],
                        ['text' => 'The number of employees in your business', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'A business spends K50 on a boosted post and makes K80 profit from the sales it generates. What is the ROI?',
                    'explanation' => 'ROI = (Profit − Cost) ÷ Cost × 100 = (80 − 50) ÷ 50 × 100 = 60%.',
                    'options' => [
                        ['text' => '30%', 'is_correct' => false],
                        ['text' => '60%', 'is_correct' => true],
                        ['text' => '80%', 'is_correct' => false],
                        ['text' => '130%', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'If a Facebook post has high reach but very few messages, what should you change?',
                    'explanation' => 'High reach with low action usually means the audience is correct but the message, caption, or call to action needs improvement.',
                    'options' => [
                        ['text' => 'Increase the advertising budget to K500', 'is_correct' => false],
                        ['text' => 'Change the caption or call to action', 'is_correct' => true],
                        ['text' => 'Delete the Facebook Page and start a new one', 'is_correct' => false],
                        ['text' => 'Stop using Facebook entirely', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why should a small business owner track marketing numbers weekly?',
                    'explanation' => 'Weekly tracking reveals patterns and helps you make informed decisions rather than relying on guesswork.',
                    'options' => [
                        ['text' => 'Because Facebook requires a weekly report', 'is_correct' => false],
                        ['text' => 'To replace guesswork with facts and spot patterns', 'is_correct' => true],
                        ['text' => 'To impress friends and family', 'is_correct' => false],
                        ['text' => 'Because it is required by ZRA', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Google usually verifies a business by sending a postcard with a code to the business address.',
                    'explanation' => 'Postcard verification is the standard method Google uses to confirm a business location is real.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A high number of likes on a post always means the marketing was successful.',
                    'explanation' => 'Likes do not necessarily lead to sales. Success is better measured by messages, orders, and return on investment.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for when a viewer takes a desired action, such as messaging you or making a purchase? (One word)',
                    'explanation' => 'Conversion is the moment a prospect completes the desired action, turning interest into a measurable result.',
                    'correct_answer' => 'Conversion',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What does ROI stand for? (Three words)',
                    'explanation' => 'Return on investment measures the profitability of an expenditure by comparing profit to cost.',
                    'correct_answer' => 'Return on investment',
                ],
            ],
        ];
    }

    private function printSummary(): void
    {
        $moduleCount = Module::where('course_id', $this->courseId)->count();
        $lessonCount = Lesson::whereIn('module_id', Module::where('course_id', $this->courseId)->pluck('id'))->count();
        $quizCount = Quiz::where('course_id', $this->courseId)->count();
        $questionCount = DB::table('quiz_questions')
            ->whereIn('quiz_id', Quiz::where('course_id', $this->courseId)->pluck('id'))
            ->count();
        $assignmentCount = Assignment::where('course_id', $this->courseId)->count();

        $this->command->newLine();
        $this->command->info('=== Digital Marketing Content Seed Summary ===');
        $this->command->table(
            ['Metric', 'Count'],
            [
                ['Modules', $moduleCount],
                ['Lessons', $lessonCount],
                ['Quizzes', $quizCount],
                ['Questions', $questionCount],
                ['Assignments', $assignmentCount],
            ]
        );
        $this->command->info('Certificate in Digital Marketing content seeded successfully.');
    }
}
