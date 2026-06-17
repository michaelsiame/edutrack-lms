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

class ECommerceOnlineBusinessContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in E-Commerce & Online Business')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in E-Commerce & Online Business" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in E-Commerce & Online Business already has modules. Skipping content seed.');
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
                'title' => 'Module 1: Setting Up Your Online Shop with WhatsApp and Facebook',
                'description' => 'Learn how to choose products that sell well online, build a WhatsApp Business catalogue, create a Facebook and Instagram presence, and set prices that cover costs and delivery.',
            ],
            [
                'title' => 'Module 2: Taking Photos, Writing Descriptions, and Posting Content',
                'description' => 'Use your smartphone to take clear product photos, write descriptions that answer buyer questions, and plan a simple posting schedule that keeps customers engaged.',
            ],
            [
                'title' => 'Module 3: Payments Made Easy with Mobile Money and Lenco',
                'description' => 'Set up MTN Mobile Money, Airtel Money, Zamtel Kwacha and Lenco payment links so customers can pay safely, while learning how to record payments and avoid fraud.',
            ],
            [
                'title' => 'Module 4: Packing and Delivering Orders Across Zambia',
                'description' => 'Pack products safely on a budget, choose between courier services and bus parcel delivery along routes like the Great North Road, and keep customers informed during load-shedding.',
            ],
            [
                'title' => 'Module 5: Taxes, Records, and Growing Your Online Business',
                'description' => 'Keep simple sales records, understand basic ZRA obligations, and learn practical steps to scale from your first ten customers to a steady online income.',
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
                'title' => 'Assignment 1: Create a Complete E-Commerce Launch Plan',
                'description' => 'Develop a practical launch plan for a Zambian product you could sell online, including product photos, pricing, payment method, and delivery plan.',
                'instructions' => "<ol><li>Choose one product that is made or sourced in Zambia (for example, chitenge bags, pure honey, handmade crafts, fresh farm produce, or groundnut oil).</li><li>Take or describe at least three product photos: one showing the full product, one showing detail or texture, and one showing the product in use or context.</li><li>Write a clear product description of 80 to 120 words. Include the product name, what problem it solves, the main benefit, the price in ZMW, and how to order.</li><li>Show your cost calculation: buying or making cost, packaging cost, estimated delivery cost, and your profit per item.</li><li>State which payment method you will accept (MTN MoMo, Airtel Money, Zamtel Kwacha, Lenco link, or bank transfer) and why it suits your target customer.</li><li>Describe your delivery plan for customers in Kalomo, Lusaka, Ndola, and Kitwe. Mention whether you will use a courier, bus parcel, or local pickup.</li><li>Save your plan as a PDF or Word document and upload it here. Name the file: EcommerceLaunchPlan_YourName.</li></ol>",
                'due_weeks' => 2,
            ],
            [
                'title' => 'Assignment 2: Build a Simple Online Store on WhatsApp Business and Facebook/Instagram',
                'description' => 'Create a working mini online store using WhatsApp Business catalogue and a Facebook or Instagram page, with sample posts and a clear order process.',
                'instructions' => "<ol><li>Download WhatsApp Business (if you do not already have it) and create or update a business profile with a clear description, address or area, business hours, and a profile photo.</li><li>Add at least five products to your WhatsApp Business catalogue. Each product must have a clear photo, name, price in ZMW, and a short description.</li><li>Create a Facebook Page or Instagram business account for the same business. Use the same name and profile photo as your WhatsApp Business account.</li><li>Write three sample posts for the page: one product announcement, one customer testimonial, and one weekend or payday special. Each post must include a photo description and a call to action.</li><li>Document your order process step by step: how a customer contacts you, how you confirm stock and price, how they pay, and how you deliver.</li><li>Take screenshots of your WhatsApp catalogue and Facebook/Instagram page and include them in your submission.</li><li>Save everything in one PDF or Word document and upload it here. Name the file: OnlineStore_YourName.</li></ol>",
                'due_weeks' => 4,
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
                'due_date' => now()->addWeeks($data['due_weeks']),
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
                'title' => 'Lesson 1.1: Choosing What to Sell Online',
                'duration_minutes' => 60,
                'content' => $this->lesson1_1(),
            ],
            [
                'title' => 'Lesson 1.2: Creating Your WhatsApp Business Catalogue',
                'duration_minutes' => 75,
                'content' => $this->lesson1_2(),
            ],
            [
                'title' => 'Lesson 1.3: Building a Facebook and Instagram Shop Page',
                'duration_minutes' => 75,
                'content' => $this->lesson1_3(),
            ],
            [
                'title' => 'Lesson 1.4: Pricing Your Products for Online Buyers',
                'duration_minutes' => 60,
                'content' => $this->lesson1_4(),
            ],
            [
                'title' => 'Module 1 Quiz: Setting Up Your Online Shop',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your understanding of product selection, WhatsApp Business catalogues, Facebook and Instagram pages, and online pricing. You need 60% to pass. Good luck!</p>',
            ],
        ];
    }

    private function lesson1_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to decide which products are suitable for selling online, calculate a healthy profit margin, and explain why some local Zambian products have an advantage in online markets.</p>

<figure><img class="lesson-diagram" src="/assets/diagrams/ecommerce-online-business/online-order-flow.svg" alt="Diagram showing how an online order travels from browsing a social catalogue through payment with mobile money to delivery across Zambia."><figcaption>Figure: How an online order travels from social browsing to payment and delivery in Zambia.</figcaption></figure>

<h2>What Sells Well Online?</h2>
<p>Not every product is a good fit for online selling. The best online products are easy to photograph, light enough to deliver at a reasonable cost, and solve a clear problem for the buyer. In Zambia, products that connect to our culture, natural resources, and daily needs often perform well because local buyers understand them and diaspora customers miss them.</p>
<p>Think about items you already see on WhatsApp statuses and Facebook pages: pure honey from Northwestern Province, handmade chitenge bags and wraps, beaded jewellery, carved wooden crafts, dried mushrooms and kapenta, organic groundnut oil, fresh farm vegetables, and even custom birthday cakes. These products work because they are visual, have a story, and can be shipped or delivered without too much damage.</p>

<h2>Digital Products vs Physical Products</h2>
<p><strong>Physical products</strong> are items you can touch and must deliver. They need packaging, a delivery plan, and stock management. A physical product business is great when you enjoy making or sourcing things, but you must remember that every order costs money for materials, packaging, and transport.</p>
<p><strong>Digital products</strong> are files or services delivered online. Examples include printable planner templates, wedding invitation designs, social media graphics, resume templates, or online tutoring sessions. Digital products have almost zero delivery cost and can be sold many times, but you need skills that customers are willing to pay for. A student in Lusaka who designs event flyers on Canva can sell the same design to ten customers with no extra printing cost.</p>

<h2>Calculating Profit Before You Start</h2>
<p>Before you list a product, do the numbers. Suppose you want to sell a 500g jar of pure honey.</p>
<ul>
<li>Cost to buy or produce the honey: K35</li>
<li>Glass jar and label: K8</li>
<li>Packaging for delivery: K5</li>
<li>Mobile money withdrawal fee (about 2%): K2</li>
<li>Bus parcel delivery to Lusaka: K25</li>
</ul>
<p>Your total cost is K75. If you sell the jar for K120, your profit is K45 per jar, which is a 60% markup on cost. That is healthy for a small online business. If you sell for K85, your profit is only K10 and one refund or broken jar wipes out several sales. Always price for profit, not just to be cheap.</p>

<h2>The Local Advantage</h2>
<p>Zambian sellers have advantages that big foreign websites cannot copy easily. You can offer same-day delivery in your town, personal WhatsApp service, mobile money payment, and culturally specific products. A customer in Ndola who wants chitenge fabric for a kitchen party would rather buy from a seller who understands the occasion than from a generic overseas site.</p>

<h2>Try It Yourself</h2>
<ol>
<li>List five products you could sell online. Mark each as physical or digital.</li>
<li>For your best idea, write down the cost of making or buying one unit.</li>
<li>Add packaging, payment fees, and estimated delivery costs.</li>
<li>Choose a selling price and calculate your profit per unit.</li>
<li>Ask two friends or family members whether they would buy it at that price.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Profit margin</strong>: The percentage of the selling price that is profit after all costs are paid.</li>
<li><strong>Digital product</strong>: A product delivered electronically, such as a file, design, or online service.</li>
<li><strong>Physical product</strong>: A tangible item that must be packed and delivered.</li>
<li><strong>Markup</strong>: The amount added to the cost price to set the selling price.</li>
<li><strong>Target market</strong>: The specific group of people most likely to buy your product.</li>
</ul>

<h2>Summary</h2>
<p>Choosing the right product is the foundation of online selling success. Look for items that photograph well, ship safely, solve a real need, and leave you with enough profit after all costs. Start with one or two products, get them right, then expand.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/" target="_blank" rel="noopener">Google Digital Garage — Online Business Basics</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/market-your-business/" target="_blank" rel="noopener">Microsoft Learn — Market Your Business</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more/talks-and-interviews/talks-and-interviews-unit/conversations-with-sal" target="_blank" rel="noopener">Khan Academy — Entrepreneurship Conversations</a></li>
</ul>
HTML;
    }

    private function lesson1_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to set up a WhatsApp Business account, create a product catalogue with photos and prices, and use quick replies and labels to manage customer conversations efficiently.</p>

<h2>Why WhatsApp Business Is Your First Online Shop</h2>
<p>In Zambia, WhatsApp is often the first place a customer discovers a product. They see a photo on a friend's status, ask for the seller's number, and start a chat. WhatsApp Business turns that casual chat into a professional selling experience. It is free, works well even on older phones, and does not need a website.</p>
<p>With WhatsApp Business, you can create a catalogue that customers browse without you typing prices every time. You can set quick replies for common questions, label chats to track orders, and share your products to your status with one tap. For a seller in Kalomo, Choma, or Kitwe, this is the fastest way to start selling online.</p>

<h2>Setting Up Your Profile</h2>
<p>Download WhatsApp Business from the Google Play Store or Apple App Store. Verify your business phone number. Then go to Settings, Business Tools, and Business Profile. Fill in every field carefully because customers judge your business from this page:</p>
<ul>
<li><strong>Business name</strong>: Use the name people already know. If your market stall is called "Bana Mutale Crafts," use exactly that name.</li>
<li><strong>Category</strong>: Choose the closest match, such as "Shopping &amp; Retail," "Food &amp; Drink," or "Handicrafts."</li>
<li><strong>Description</strong>: Write two sentences that say what you sell and why it is special. Example: "Handmade chitenge bags and purses from Kalomo. Bright designs, strong stitching, and delivery to Lusaka, Ndola, and Kitwe."</li>
<li><strong>Address</strong>: Add your area or a landmark, such as "Opposite Kalomo Central Market."</li>
<li><strong>Hours</strong>: Be honest. If you close on Sundays, say so.</li>
<li><strong>Email and website</strong>: If you have a Facebook page, paste the link here.</li>
</ul>

<h2>Building Your Catalogue</h2>
<p>The catalogue is the heart of your WhatsApp shop. To add a product, go to Business Tools, then Catalogue, then "Add Product or Service." Add a clear photo, the product name, a short description, and the price in ZMW. Include details like size, colour options, and delivery time. Customers trust sellers who give complete information upfront.</p>
<p>Keep your catalogue updated. If an item is out of stock, remove it or mark it clearly. If the price changes, update it immediately. Nothing annoys a customer more than seeing K80 in the catalogue and being told K100 in the chat. Aim for at least five products before you start promoting your shop.</p>

<h2>Quick Replies and Labels</h2>
<p><strong>Quick replies</strong> let you answer common questions instantly. Create shortcuts like "/price" for your price list, "/pay" for payment instructions, and "/delivery" for delivery areas and costs. This saves time and makes you look professional.</p>
<p><strong>Labels</strong> help you organise chats. Use labels such as "New Enquiry," "Awaiting Payment," "Paid — Pack," "Shipped," and "Completed." During busy weeks, labels prevent orders from being forgotten.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Install WhatsApp Business and complete your business profile.</li>
<li>Add at least five products to your catalogue with clear photos and prices.</li>
<li>Create three quick replies for common customer questions.</li>
<li>Create five labels to track orders from enquiry to delivery.</li>
<li>Share one product from your catalogue to your WhatsApp status.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>WhatsApp Business</strong>: A free app for small businesses with catalogue, quick replies, and labels.</li>
<li><strong>Catalogue</strong>: A digital product list customers can browse inside WhatsApp.</li>
<li><strong>Quick reply</strong>: A saved message you can send using a shortcut.</li>
<li><strong>Label</strong>: A tag used to organise customer chats by status.</li>
<li><strong>WhatsApp Status</strong>: A 24-hour photo or video visible to your contacts.</li>
</ul>

<h2>Summary</h2>
<p>WhatsApp Business is the fastest and cheapest online shop for most Zambian entrepreneurs. A complete profile, an up-to-date catalogue, and organised labels turn casual chats into reliable sales.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://business.whatsapp.com/products/business-app" target="_blank" rel="noopener">WhatsApp Business — Getting Started</a></li>
<li><a href="https://www.facebook.com/business/help" target="_blank" rel="noopener">Meta Business Help Centre</a></li>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/" target="_blank" rel="noopener">Google Digital Garage — Sell Online</a></li>
</ul>
HTML;
    }

    private function lesson1_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to create a Facebook Page and Instagram business account for your shop, link them to WhatsApp, and write an "About" section that helps customers find and trust you.</p>

<figure><img class="lesson-diagram" src="/assets/diagrams/ecommerce-online-business/social-vs-website-store.svg" alt="Diagram comparing social commerce on WhatsApp, Facebook and Instagram with a dedicated website store."><figcaption>Figure: Comparing social commerce with a dedicated website store for Zambian sellers.</figcaption></figure>

<h2>Facebook and Instagram as Shop Fronts</h2>
<p>A Facebook Page is like a free website. It appears in Google search, shows your products, and lets customers message you directly. Instagram is more visual and works well for fashion, crafts, food, and beauty products. Together, these platforms help you reach customers who may never have heard of your WhatsApp number.</p>
<p>Many Zambian buyers search Facebook for things like "tailor Kalomo," "honey Lusaka," or "chitenge bags Ndola." If your page has the right name, location, and keywords, you can appear in those searches without paying for adverts.</p>

<h2>Creating a Facebook Page</h2>
<p>Open the Facebook app, tap the menu, and select "Pages." Tap "Create" and choose "Business or Brand." Enter your page name — use the same name as your WhatsApp Business account. Choose a category such as "Shopping &amp; Retail" or "Product/Service."</p>
<p>Add a profile picture and cover photo. Your profile picture should be your logo or a clear product photo. Your cover photo should show what you sell. A flat lay of your products on a clean background works well. Fill in the About section with the same description you used on WhatsApp Business, plus your phone number, email, and the areas you deliver to.</p>

<h2>Setting Up Instagram for Business</h2>
<p>If your products are visual, Instagram is a powerful tool. Create an Instagram account and switch it to a professional account in Settings. Connect it to your Facebook Page. Add a profile photo, bio, and a link. Because Instagram allows only one clickable link in your bio, many sellers use a "link in bio" service or simply write "WhatsApp 0977-123456" in the bio.</p>
<p>Post photos and short videos regularly. Use simple captions that include the price and how to order. Example: "Sunset orange chitenge tote bag. K85. Fits a laptop and two books. WhatsApp 0977-123456 or DM to order. Delivery to Lusaka, Ndola, and Kitwe."</p>

<h2>Linking Everything Together</h2>
<p>Your WhatsApp Business, Facebook Page, and Instagram should all point to each other. Put your WhatsApp number on Facebook and Instagram. Put your Facebook link in your WhatsApp profile. This creates a simple online ecosystem. A customer can find you on Facebook, see your products on Instagram, and order through WhatsApp.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a Facebook Page for your business using the same name as your WhatsApp Business account.</li>
<li>Upload a profile picture and a cover photo that show what you sell.</li>
<li>Write an About section with your location, phone number, and delivery areas.</li>
<li>Create or convert an Instagram account and connect it to your Facebook Page.</li>
<li>Post one photo with a caption that includes the price and how to order.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Facebook Page</strong>: A public profile on Facebook for a business or brand.</li>
<li><strong>Instagram business account</strong>: An Instagram profile with extra tools for sellers, including insights and contact buttons.</li>
<li><strong>Cover photo</strong>: The large banner image at the top of a Facebook Page.</li>
<li><strong>Bio</strong>: The short description under your Instagram profile name.</li>
<li><strong>Link in bio</strong>: A single clickable link allowed in an Instagram profile.</li>
</ul>

<h2>Summary</h2>
<p>Facebook and Instagram extend your reach beyond WhatsApp contacts. A consistent name, clear photos, and linked profiles help customers find you, trust you, and order from you on the platform they prefer.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.facebook.com/business/help" target="_blank" rel="noopener">Meta Business Help Centre — Pages</a></li>
<li><a href="https://help.instagram.com/502981923235522" target="_blank" rel="noopener">Instagram Help — Professional Account</a></li>
<li><a href="https://www.canva.com/designschool/" target="_blank" rel="noopener">Canva Design School — Social Media</a></li>
</ul>
HTML;
    }

    private function lesson1_4(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to set prices that cover all your costs, include delivery and mobile money fees, and still leave a profit that makes the business worth running.</p>

<h2>Price Is a Message</h2>
<p>Your price tells customers what to expect. A very low price suggests low quality. A very high price without explanation pushes customers away. The right price says, "This product is worth it, and here is why." For online sellers, pricing is even more important because customers cannot touch the product before they pay.</p>

<h2>The Full Cost Method</h2>
<p>Start by writing down every cost connected to one sale. Do not guess — use real numbers. For a handmade leather belt sold online, the costs might include:</p>
<ul>
<li>Leather strip and buckle: K45</li>
<li>Thread, dye, and finishing materials: K8</li>
<li>Packaging box and tissue paper: K7</li>
<li>Mobile money withdrawal fee on the selling price: roughly 2%</li>
<li>Delivery to Lusaka by courier: K30</li>
</ul>
<p>If you sell the belt for K120, your costs are K45 + K8 + K7 + K2.40 + K30 = K92.40. Your profit is K27.60, which is about 23% of the selling price. That is acceptable for a handmade item, but you may want to raise the price to K140 to earn K47.60 and cover occasional refunds or re-shipping.</p>

<h2>Delivery and Mobile Money Fees</h2>
<p>Many new sellers forget to include delivery and payment fees in their prices. MTN Mobile Money and Airtel Money charge fees to withdraw money. Lenco payment links also have transaction fees. Couriers charge per kilogram or per package. If you absorb these costs, your profit disappears. If you pass them to the customer, you must be transparent.</p>
<p>A common approach is to offer "free delivery within Kalomo" to encourage local sales, and charge a separate delivery fee for Lusaka, Ndola, Kitwe, and other towns. Always state delivery fees clearly before the customer pays. Surprise fees are the main reason online buyers cancel orders.</p>

<h2>Psychological Pricing</h2>
<p>Prices ending in .99 or .95 feel lower than round numbers. K99 feels closer to K90 than to K100, even though the difference is only K1. Bundling also helps. Instead of selling one jar of honey for K120, offer two jars for K220. The customer feels they are saving K20, and you move more stock.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose one product from your planned shop.</li>
<li>List every cost involved in one sale, including materials, packaging, payment fees, and delivery.</li>
<li>Set a selling price and calculate your profit as a percentage of the price.</li>
<li>Write a sentence explaining your delivery policy for local and out-of-town customers.</li>
<li>Compare your price to two similar products online and note whether you are higher, lower, or the same.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Cost price</strong>: The total amount it costs you to produce or buy a product.</li>
<li><strong>Selling price</strong>: The amount the customer pays.</li>
<li><strong>Gross profit</strong>: Selling price minus direct costs, before other business expenses.</li>
<li><strong>Payment fee</strong>: A charge deducted by mobile money or card payment providers.</li>
<li><strong>Psychological pricing</strong>: Pricing strategies that make a price feel lower or more attractive.</li>
</ul>

<h2>Summary</h2>
<p>Smart pricing covers costs, includes fees, and leaves a healthy profit. Be transparent about delivery fees, use psychological pricing where appropriate, and never compete on price alone unless you are sure you can afford it.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/" target="_blank" rel="noopener">Google Digital Garage — Pricing Strategy</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/price-product/" target="_blank" rel="noopener">Microsoft Learn — Price Your Product</a></li>
<li><a href="https://www.khanacademy.org/economics-finance-domain/microeconomics" target="_blank" rel="noopener">Khan Academy — Microeconomics Basics</a></li>
</ul>
HTML;
    }

    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: Setting Up Your Online Shop',
            'description' => 'Test your understanding of product selection, WhatsApp Business catalogues, Facebook and Instagram pages, and online pricing.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a strong reason to choose a product for online selling?',
                    'explanation' => 'Products that are easy to photograph, light to deliver, and solve a clear problem are ideal for online selling.',
                    'options' => [
                        ['text' => 'It is difficult to describe in words', 'is_correct' => false],
                        ['text' => 'It is heavy and expensive to ship', 'is_correct' => false],
                        ['text' => 'It is visual, has a story, and solves a customer need', 'is_correct' => true],
                        ['text' => 'It can only be sold in person', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main purpose of a WhatsApp Business catalogue?',
                    'explanation' => 'The catalogue lets customers browse products, prices, and descriptions without the seller typing them each time.',
                    'options' => [
                        ['text' => 'To send bulk messages to all contacts', 'is_correct' => false],
                        ['text' => 'To let customers browse products and prices independently', 'is_correct' => true],
                        ['text' => 'To create a Facebook advert', 'is_correct' => false],
                        ['text' => 'To withdraw mobile money', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which platform is described as "like a free website" that can appear in Google search?',
                    'explanation' => 'A Facebook Page is public, searchable, and can include contact details, making it similar to a free business website.',
                    'options' => [
                        ['text' => 'WhatsApp Status', 'is_correct' => false],
                        ['text' => 'Facebook Page', 'is_correct' => true],
                        ['text' => 'Instagram Stories', 'is_correct' => false],
                        ['text' => 'MTN Mobile Money', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'When pricing an online product, which cost is most often forgotten by new sellers?',
                    'explanation' => 'New sellers often forget mobile money withdrawal fees, courier costs, and packaging when setting prices.',
                    'options' => [
                        ['text' => 'The product photo', 'is_correct' => false],
                        ['text' => 'Delivery and payment fees', 'is_correct' => true],
                        ['text' => 'The business name', 'is_correct' => false],
                        ['text' => 'The customer\'s phone number', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does it mean to "absorb" delivery costs?',
                    'explanation' => 'Absorbing delivery costs means the seller pays them out of the selling price instead of charging the customer separately.',
                    'options' => [
                        ['text' => 'The customer pays the courier directly', 'is_correct' => false],
                        ['text' => 'The seller includes delivery in the price and pays it from profit', 'is_correct' => true],
                        ['text' => 'The product is delivered by bus parcel only', 'is_correct' => false],
                        ['text' => 'The courier gives a discount', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A digital product has almost zero delivery cost and can be sold many times.',
                    'explanation' => 'Digital products such as templates, designs, or online services are delivered electronically, so reproduction cost is very low.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'You should use a different business name on WhatsApp, Facebook, and Instagram to reach more people.',
                    'explanation' => 'Consistency builds trust. Use the same business name across all platforms so customers can find and recognise you.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for the percentage of the selling price that is profit after all costs are paid? (Two words)',
                    'explanation' => 'Profit margin measures how much of each sale is profit.',
                    'correct_answer' => 'Profit margin',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What WhatsApp Business feature lets you send a saved message using a shortcut like "/price"? (Two words)',
                    'explanation' => 'Quick replies allow you to store and reuse common messages quickly.',
                    'correct_answer' => 'Quick replies',
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
                'title' => 'Lesson 2.1: Product Photography with Your Phone',
                'duration_minutes' => 60,
                'content' => $this->lesson2_1(),
            ],
            [
                'title' => 'Lesson 2.2: Writing Product Descriptions That Sell',
                'duration_minutes' => 60,
                'content' => $this->lesson2_2(),
            ],
            [
                'title' => 'Lesson 2.3: Creating a Weekly Posting Plan',
                'duration_minutes' => 75,
                'content' => $this->lesson2_3(),
            ],
            [
                'title' => 'Module 2 Quiz: Photos, Descriptions, and Posting',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your knowledge of mobile product photography, writing descriptions, and planning social media posts. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson2_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to take clear, attractive product photos using only your smartphone, edit them lightly with free tools, and choose images that make customers want to buy.</p>

<h2>Your Phone Camera Is Enough</h2>
<p>You do not need an expensive camera to sell online. Most smartphones from the last five years can take photos good enough for WhatsApp, Facebook, and Instagram. What matters most is light, cleanliness, and composition. A well-lit photo of a K40 chitenge purse taken on an entry-level phone will outsell a dark, blurry photo taken on a professional camera.</p>

<h2>Use Natural Light</h2>
<p>Zambia has strong sunlight for most of the year, which is perfect for product photography. Shoot outdoors in the early morning or late afternoon when the light is soft. Avoid direct midday sun because it creates harsh shadows. If you must shoot inside, place your product near a window. Never use your phone's flash — it makes fabrics look shiny and food look unappealing.</p>
<p>During load-shedding, natural light becomes even more important. If you plan to take photos in the evening, charge a power bank during the day and use a small LED lamp or torch with a white cloth over it to soften the light.</p>

<h2>Clean Backgrounds and Sharp Focus</h2>
<p>Place your product on a clean, simple background. A plain wooden table, a white bed sheet, a clean chitenge spread flat, or a neutral floor works well. Remove anything that distracts from the product. Before shooting, wipe your phone lens with a soft cloth — you will be surprised how much sharper your photos become.</p>
<p>Tap the screen where you want the camera to focus. For small products like jewellery or honey jars, get close but not so close that the image blurs. Take at least five photos from different angles: front, side, detail, in use, and scale. A photo of a handbag next to a notebook shows its real size better than a photo of the bag alone.</p>

<h2>Telling a Story with Video</h2>
<p>Short videos get more attention than photos. A 15-second clip of honey being poured, a bag being packed, or a dress being worn tells a story that a still image cannot. Hold your phone steady, keep the background simple, and keep videos under 45 seconds unless you are demonstrating a process.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose one product and find a clean background with good natural light.</li>
<li>Take six photos from different angles and distances.</li>
<li>Take one 15-second video showing the product in use or being prepared.</li>
<li>Edit your best photo using only brightness, contrast, and crop.</li>
<li>Show your photos to two people and ask which one makes them want to buy.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Natural light</strong>: Light from the sun, used instead of flash or artificial lights.</li>
<li><strong>Composition</strong>: The arrangement of the product and background in a photo.</li>
<li><strong>Depth of field</strong>: How much of the photo is in sharp focus.</li>
<li><strong>Flat lay</strong>: A photo taken from directly above, showing items arranged on a surface.</li>
<li><strong>Scale reference</strong>: An object placed next to a product to show its real size.</li>
</ul>

<h2>Summary</h2>
<p>Good product photos are the most important sales tool for online sellers. Use natural light, clean backgrounds, multiple angles, and short videos to show customers exactly what they are buying.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool/" target="_blank" rel="noopener">Canva Design School — Photography Basics</a></li>
<li><a href="https://support.google.com/photos/answer/6128858" target="_blank" rel="noopener">Google Photos Help — Edit Photos</a></li>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/" target="_blank" rel="noopener">Google Digital Garage — Content Creation</a></li>
</ul>
HTML;
    }

    private function lesson2_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to write product descriptions that answer customer questions, highlight benefits, and include a clear call to action that leads to an order.</p>

<h2>Descriptions Sell Benefits, Not Just Features</h2>
<p>A feature is a fact about the product. A benefit is what the fact does for the customer. "Made from 100% cotton chitenge" is a feature. "Cool and comfortable even in the hot season, and the colours stay bright after many washes" is a benefit. Customers buy benefits. Your description should translate every feature into a benefit.</p>

<h2>The Simple Description Formula</h2>
<p>Use this structure for every product description:</p>
<ol>
<li><strong>Headline</strong>: A short, catchy sentence that names the product and the main benefit. "Handwoven reed basket — strong enough for market shopping, beautiful enough for home display."</li>
<li><strong>What it is</strong>: Two or three sentences describing size, material, colour, and use. "This basket is 40cm wide and 30cm tall. It is handwoven by women in Kalomo using locally sourced reeds."</li>
<li><strong>Why it helps</strong>: One or two sentences about the problem it solves. "Use it for shopping, storing vegetables, or as a gift hamper. It does not tear like plastic bags."</li>
<li><strong>Price and how to order</strong>: A clear call to action. "K95 each. WhatsApp 0977-123456 or DM us to order. Delivery available countrywide."</li>
</ol>

<h2>Answer Questions Before They Are Asked</h2>
<p>Think about what a customer from Lusaka, Ndola, or Kitwe would want to know before paying. How big is it? How long does delivery take? Is it returnable? Does the price include delivery? Add these answers to your description. This reduces back-and-forth messages and builds trust.</p>

<h2>Using Simple, Honest Language</h2>
<p>Avoid exaggeration. Do not say "the best honey in Zambia" unless you have proof. Instead say "pure, unprocessed honey from hive to jar." Honest descriptions create loyal customers who come back and recommend you. Exaggerated descriptions create disappointed customers who leave bad comments.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose one product from your planned shop.</li>
<li>Write a headline, a "what it is" paragraph, a "why it helps" paragraph, and a price/call to action.</li>
<li>Identify three customer questions and answer them in the description.</li>
<li>Ask a friend to read the description and tell you what the product is, how much it costs, and how to order.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Feature</strong>: A fact about a product, such as size, material, or colour.</li>
<li><strong>Benefit</strong>: The positive result a customer gets from a feature.</li>
<li><strong>Call to action</strong>: A clear instruction telling the customer what to do next.</li>
<li><strong>Product description</strong>: The text that explains what a product is, why it matters, and how to buy it.</li>
<li><strong>Return policy</strong>: The rules about whether a customer can return a product and get a refund.</li>
</ul>

<h2>Summary</h2>
<p>A great product description turns curiosity into confidence. Focus on benefits, answer common questions, use honest language, and always end with a clear call to action.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool/" target="_blank" rel="noopener">Canva Design School — Writing for Social Media</a></li>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/" target="_blank" rel="noopener">Google Digital Garage — Content Writing</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/write-marketing-copy/" target="_blank" rel="noopener">Microsoft Learn — Write Marketing Copy</a></li>
</ul>
HTML;
    }

    private function lesson2_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to plan a simple weekly posting schedule for your online shop, choose content types that keep customers interested, and time your posts around Zambian buying patterns.</p>

<h2>Why Consistency Beats Perfection</h2>
<p>Many small businesses post ten times in one week and then disappear for a month. Customers forget them. It is better to post three to five times every week than to post randomly. Consistency builds trust and keeps your products in people's minds when they are ready to buy.</p>

<h2>Types of Posts That Work</h2>
<p>Mix your content so your page does not feel like a constant sales pitch. Try these five types of posts:</p>
<ul>
<li><strong>Product post</strong>: A clear photo, price, and how to order.</li>
<li><strong>Behind the scenes</strong>: A photo or video of you making or packing the product.</li>
<li><strong>Customer testimonial</strong>: A screenshot of a happy customer message or a photo of someone using your product.</li>
<li><strong>Engagement post</strong>: A question or poll, such as "Which colour should we restock — red or blue?"</li>
<li><strong>Offer or reminder</strong>: A weekend special, payday deal, or limited-stock announcement.</li>
</ul>

<h2>Timing Around Zambian Rhythms</h2>
<p>Formal workers in Zambia are usually paid around the 28th of each month. The week after payday is the best time to push products and offers. The third week of the month is usually quieter — use it for engagement posts and trust-building content. Farming seasons also matter. After harvest, farmers have more cash for clothes, phones, and home goods. Before planting season, they spend on inputs and may cut back on extras.</p>
<p>Holidays such as Mother's Day, Father's Day, Farmer's Day, Independence Day, and Christmas are natural selling moments. Plan your content at least two weeks ahead so you are not rushing on the day.</p>

<h2>Sample Weekly Calendar</h2>
<table>
<tr><th>Day</th><th>Type</th><th>Post Idea</th></tr>
<tr><td>Monday</td><td>Product</td><td>Feature one bestseller with price and ordering details</td></tr>
<tr><td>Tuesday</td><td>Behind the scenes</td><td>Short video of packing orders or making the product</td></tr>
<tr><td>Wednesday</td><td>Engagement</td><td>Poll: "Which new product should we launch next?"</td></tr>
<tr><td>Thursday</td><td>Testimonial</td><td>Customer photo or review screenshot</td></tr>
<tr><td>Friday</td><td>Offer</td><td>Weekend special or payday promotion</td></tr>
<tr><td>Saturday</td><td>Product</td><td>Bundle deal or new arrival</td></tr>
<tr><td>Sunday</td><td>Rest or inspiration</td><td>Quote about entrepreneurship or community</td></tr>
</table>

<h2>Try It Yourself</h2>
<ol>
<li>Create a one-week content calendar for your business.</li>
<li>Write one post for each day using the types above.</li>
<li>Identify the next payday week or holiday and plan one special offer post.</li>
<li>Post one product photo with a description today and track how many views and messages it receives.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Content calendar</strong>: A schedule that plans what to post and when.</li>
<li><strong>Engagement post</strong>: Content designed to start conversation rather than make a direct sale.</li>
<li><strong>Testimonial</strong>: A positive comment or review from a customer.</li>
<li><strong>Payday week</strong>: The days immediately after most formal workers receive their salaries.</li>
<li><strong>Behind the scenes</strong>: Content that shows how a product is made or how the business operates.</li>
</ul>

<h2>Summary</h2>
<p>A weekly posting plan keeps your online shop active and professional. Mix product, engagement, testimonial, and offer posts, and time your sales messages around payday weeks and holidays for the best results.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.facebook.com/business/help" target="_blank" rel="noopener">Meta Business Help Centre — Creating Content</a></li>
<li><a href="https://www.canva.com/designschool/" target="_blank" rel="noopener">Canva Design School — Social Media Content</a></li>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/" target="_blank" rel="noopener">Google Digital Garage — Social Media Content Strategy</a></li>
</ul>
HTML;
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Photos, Descriptions, and Posting',
            'description' => 'Assess your understanding of mobile product photography, writing descriptions, and planning social media content.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the most important factor in taking good product photos with a smartphone?',
                    'explanation' => 'Light matters more than the camera price. Natural light produces clearer, more attractive product images.',
                    'options' => [
                        ['text' => 'Using the phone flash indoors', 'is_correct' => false],
                        ['text' => 'Buying an expensive phone', 'is_correct' => false],
                        ['text' => 'Using natural light and a clean background', 'is_correct' => true],
                        ['text' => 'Taking only one photo', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a benefit rather than a feature?',
                    'explanation' => 'A benefit explains what the customer gains. "Keeps vegetables fresh longer" describes a customer advantage.',
                    'options' => [
                        ['text' => 'Made from bamboo', 'is_correct' => false],
                        ['text' => 'Comes in three colours', 'is_correct' => false],
                        ['text' => 'Keeps vegetables fresh longer', 'is_correct' => true],
                        ['text' => 'Weighs 500 grams', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'When is the best time to run a major sales promotion in Zambia for formal workers?',
                    'explanation' => 'Most formal salaries are paid around the 28th, so spending power is highest in the following week.',
                    'options' => [
                        ['text' => 'The third week of the month', 'is_correct' => false],
                        ['text' => 'The week after payday', 'is_correct' => true],
                        ['text' => 'Only on public holidays', 'is_correct' => false],
                        ['text' => 'Early Monday morning', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the purpose of a "behind the scenes" post?',
                    'explanation' => 'Behind-the-scenes content builds trust by showing how products are made or packed.',
                    'options' => [
                        ['text' => 'To complain about competitors', 'is_correct' => false],
                        ['text' => 'To show how products are made and build trust', 'is_correct' => true],
                        ['text' => 'To ask customers for loans', 'is_correct' => false],
                        ['text' => 'To delete negative comments', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which type of post directly asks the audience to choose or respond?',
                    'explanation' => 'Engagement posts, such as polls and questions, invite the audience to interact.',
                    'options' => [
                        ['text' => 'Product post', 'is_correct' => false],
                        ['text' => 'Testimonial post', 'is_correct' => false],
                        ['text' => 'Engagement post', 'is_correct' => true],
                        ['text' => 'Offer post', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'During load-shedding, you should use your phone flash for product photos.',
                    'explanation' => 'Phone flash creates harsh, unflattering light. Natural light or a softened LED lamp is better.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A product description should focus only on features like size and material.',
                    'explanation' => 'Good descriptions explain benefits too — how the product improves the customer\'s life.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for the positive result a customer gets from a product feature? (One word)',
                    'explanation' => 'A benefit is the value or advantage a customer receives.',
                    'correct_answer' => 'Benefit',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is a positive comment from a customer that you can share in a post called? (One word)',
                    'explanation' => 'A testimonial is a customer endorsement that builds trust with new buyers.',
                    'correct_answer' => 'Testimonial',
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
                'title' => 'Lesson 3.1: Mobile Money Basics for Sellers',
                'duration_minutes' => 60,
                'content' => $this->lesson3_1(),
            ],
            [
                'title' => 'Lesson 3.2: Setting Up Lenco Payment Links',
                'duration_minutes' => 75,
                'content' => $this->lesson3_2(),
            ],
            [
                'title' => 'Lesson 3.3: Recording Payments and Avoiding Fraud',
                'duration_minutes' => 60,
                'content' => $this->lesson3_3(),
            ],
            [
                'title' => 'Module 3 Quiz: Mobile Money and Lenco Payments',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your understanding of mobile money payments, Lenco payment links, and how to protect your online business from fraud. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson3_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to accept payments using MTN Mobile Money, Airtel Money, and Zamtel Kwacha, explain the fees to customers, and confirm payments safely before sending goods.</p>

<h2>Why Mobile Money Powers Zambian E-Commerce</h2>
<p>Most Zambian online buyers do not have credit cards or PayPal accounts. They do have phones and mobile money wallets. MTN Mobile Money, Airtel Money, and Zamtel Kwacha allow customers to pay you from anywhere in the country, even during bank holidays or load-shedding. For an online seller, accepting mobile money is not optional — it is essential.</p>

<h2>How Customers Pay You</h2>
<p>There are two common ways to receive mobile money. The first is a direct wallet-to-wallet transfer. You give the customer your registered phone number, and they send the money from their phone using USSD or the app. The second is a merchant or payment link, such as Lenco, which creates a payment page the customer can open and pay through.</p>
<p>For direct transfers, keep your business number separate from your personal number. Register a SIM in your business name if possible, or use a dedicated line for sales. This makes record-keeping easier and protects your personal privacy.</p>

<h2>Confirming Payments</h2>
<p>Never send a product based only on a customer's word that they have paid. Always check your SMS or app notification. Some sellers ask the customer to send a screenshot of the confirmation message. Screenshots can be faked, so always verify on your own phone or by dialling the mobile money balance code. For Lenco links, log in to your dashboard and confirm the transaction status before dispatching goods.</p>

<h2>Understanding Fees</h2>
<p>Mobile money providers charge fees for deposits, transfers, and withdrawals. These fees reduce your profit if you do not plan for them. A common arrangement is to include the withdrawal fee in your price or to ask the customer to pay a small convenience fee. Be honest about fees. Customers respect sellers who explain costs clearly.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Register or confirm that your business phone number can receive mobile money.</li>
<li>Check the current withdrawal and transfer fees for MTN, Airtel, and Zamtel.</li>
<li>Write a standard payment message you can send to customers with your number and the exact amount.</li>
<li>Practice receiving a small test payment from a friend and confirming it.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Mobile money</strong>: A digital wallet linked to a phone number, used to send, receive, and store money.</li>
<li><strong>USSD code</strong>: A short code such as *115# or *303# used to access mobile money menus.</li>
<li><strong>Payment confirmation</strong>: A message or notification showing that a payment was successful.</li>
<li><strong>Withdrawal fee</strong>: A charge for taking money out of a mobile money wallet.</li>
<li><strong>Payment link</strong>: A web link that takes a customer to a page where they can pay online.</li>
</ul>

<h2>Summary</h2>
<p>Mobile money is the foundation of online selling in Zambia. Keep a dedicated business line, confirm every payment on your own device, understand the fees, and explain payment steps clearly to customers.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.mtn.co.zm/momo" target="_blank" rel="noopener">MTN Zambia — Mobile Money</a></li>
<li><a href="https://www.airtel.co.zm/" target="_blank" rel="noopener">Airtel Zambia — Mobile Money</a></li>
<li><a href="https://www.zamtel.zm/" target="_blank" rel="noopener">Zamtel — Mobile Money Services</a></li>
</ul>
HTML;
    }

    private function lesson3_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to explain what Lenco payment links are, describe how they help online sellers collect payments, and understand the basic steps to create and share a payment link.</p>

<h2>What Is Lenco?</h2>
<p>Lenco is a Zambian payment service that allows businesses to accept payments online through cards, bank transfers, and mobile money. For online sellers, Lenco provides payment links — simple web links you can send to a customer by WhatsApp, SMS, email, or Facebook Messenger. When the customer clicks the link, they see a payment page with the amount, your business name, and payment options.</p>

<h2>Why Use Payment Links?</h2>
<p>Payment links make your business look more professional. Instead of asking the customer to "send money to 0977-123456," you send a branded link that says "Pay K150 to Chitenge Creations." The customer pays through the secure page, and you receive a notification when payment is complete. This reduces errors, builds trust, and creates a record for your bookkeeping.</p>
<p>Payment links are especially useful for customers outside your town. A buyer in Lusaka who has never met you may hesitate to send money to a personal mobile money number. A Lenco link feels safer and more official. It also allows card payments, which some customers prefer for larger orders.</p>

<h2>How to Create and Share a Link</h2>
<p>While the exact steps may change as Lenco updates its platform, the general process is:</p>
<ol>
<li>Sign up for a Lenco business account and complete verification.</li>
<li>Create a new payment link from your dashboard.</li>
<li>Enter the amount, description, and expiry date if needed.</li>
<li>Copy the link and send it to the customer through WhatsApp, Facebook, or email.</li>
<li>Wait for the payment confirmation before packing or delivering the order.</li>
</ol>

<h2>Fees and Settlement</h2>
<p>Lenco charges a transaction fee, usually a percentage of the payment. The fee is often lower than the total cost and hassle of handling cash, especially for out-of-town orders. Settlement — the time it takes for money to reach your bank account — may take one to three business days. Factor this into your cash flow planning.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Visit the Lenco website and read the current pricing and signup requirements.</li>
<li>Write a sample message you would send to a customer with a Lenco payment link.</li>
<li>Compare Lenco's fees to the fees you would pay for direct mobile money withdrawals.</li>
<li>Decide whether payment links fit your business model at your current stage.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Payment link</strong>: A clickable URL that opens a secure page for a customer to pay.</li>
<li><strong>Settlement</strong>: The process of moving money from the payment provider to the seller's bank account.</li>
<li><strong>Transaction fee</strong>: A charge taken by the payment provider for processing a payment.</li>
<li><strong>Cash flow</strong>: The movement of money into and out of a business over time.</li>
<li><strong>Verification</strong>: The process of proving your business identity to a payment provider.</li>
</ul>

<h2>Summary</h2>
<p>Lenco payment links add professionalism and security to your online sales. They are useful for out-of-town buyers, card-paying customers, and sellers who want clear payment records. Understand the fees and settlement timing before you rely on them fully.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.lenco.co/" target="_blank" rel="noopener">Lenco — Zambian Business Payments</a></li>
<li><a href="https://www.boz.zm/" target="_blank" rel="noopener">Bank of Zambia — Financial Consumer Protection</a></li>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/" target="_blank" rel="noopener">Google Digital Garage — Online Payments</a></li>
</ul>
HTML;
    }

    private function lesson3_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to keep simple payment records, spot common online payment scams, and put basic safety practices in place to protect your business.</p>

<h2>Keep a Payment Log</h2>
<p>Every payment you receive should be recorded. You can use a simple notebook, a spreadsheet on your phone, or a free app. For each sale, write down the date, customer name, product, amount paid, payment method, mobile money reference number or Lenco transaction ID, and delivery status. This log helps you track who has paid, who is waiting for delivery, and how much money you have actually received.</p>
<p>At the end of each week, compare your mobile money balance and Lenco dashboard to your log. If the numbers do not match, investigate immediately. Small mistakes become big problems if they are ignored.</p>

<h2>Common Payment Scams</h2>
<p>Online sellers in Zambia face several common scams. The fake screenshot is the most frequent: a customer sends an edited image showing a payment that never happened. Always verify on your own phone before sending goods. Another scam is the "overpayment" trick, where a customer sends too much money and asks you to refund the difference — often from a stolen wallet. Refuse the order and ask for the correct amount.</p>
<p>Be careful of buyers who refuse to provide a delivery address or phone number, or who pressure you to send goods before payment. A legitimate customer will answer your questions and wait for confirmation.</p>

<h2>Basic Safety Rules</h2>
<ul>
<li>Confirm every payment on your own device before dispatching goods.</li>
<li>Keep screenshots and reference numbers for at least three months.</li>
<li>Use a dedicated business phone number, not your personal line.</li>
<li>Do not accept overpayments or refund requests outside the normal payment channel.</li>
<li>Never share your mobile money PIN, Lenco password, or banking details with anyone.</li>
</ul>

<h2>Communicating During Load-Shedding</h2>
<p>Load-shedding can interrupt your ability to confirm payments or reply to customers. Keep your phone charged with a power bank, and let customers know your typical response times. If you cannot confirm a payment immediately because of network or power issues, send a polite message explaining the delay. Most customers will wait if you communicate clearly.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a simple payment log template with columns for date, customer, product, amount, method, reference, and status.</li>
<li>Write three safety rules you will follow for every sale.</li>
<li>Role-play with a friend: they send a fake screenshot, and you explain how you would verify it.</li>
<li>Back up your important business contacts and payment records to cloud storage or a second device.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Payment log</strong>: A record of all money received and the details of each transaction.</li>
<li><strong>Reference number</strong>: A unique code attached to a mobile money or card transaction.</li>
<li><strong>Overpayment scam</strong>: A fraud where a customer sends too much money and asks for a refund.</li>
<li><strong>Fake screenshot</strong>: An edited image pretending to show a successful payment.</li>
<li><strong>Two-factor authentication</strong>: An extra security step that requires a second code to log in.</li>
</ul>

<h2>Summary</h2>
<p>Good record-keeping and basic safety habits protect your income and your reputation. Confirm every payment yourself, keep clear records, watch for common scams, and communicate honestly with customers during power or network problems.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.boz.zm/" target="_blank" rel="noopener">Bank of Zambia — Consumer Protection</a></li>
<li><a href="https://www.cybersecurity.gov.zm/" target="_blank" rel="noopener">Zambia Cybersecurity Guidelines</a></li>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/" target="_blank" rel="noopener">Google Digital Garage — Online Security Basics</a></li>
</ul>
HTML;
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Mobile Money and Lenco Payments',
            'description' => 'Assess your understanding of mobile money payments, Lenco links, and payment safety.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is mobile money essential for Zambian online sellers?',
                    'explanation' => 'Most Zambian buyers use mobile money because credit card and PayPal use is limited.',
                    'options' => [
                        ['text' => 'It is the only legal payment method in Zambia', 'is_correct' => false],
                        ['text' => 'Most online buyers do not have credit cards but do have mobile wallets', 'is_correct' => true],
                        ['text' => 'It is free for sellers', 'is_correct' => false],
                        ['text' => 'It works only in Lusaka', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should you do before sending a product to a customer?',
                    'explanation' => 'Always confirm payment on your own device; screenshots can be edited.',
                    'options' => [
                        ['text' => 'Trust the customer\'s screenshot without checking', 'is_correct' => false],
                        ['text' => 'Send the product immediately to save time', 'is_correct' => false],
                        ['text' => 'Confirm the payment on your own phone or dashboard', 'is_correct' => true],
                        ['text' => 'Post the product to a random address', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is a Lenco payment link?',
                    'explanation' => 'A Lenco link is a secure web page where customers can pay by card, bank transfer, or mobile money.',
                    'options' => [
                        ['text' => 'A USSD code for mobile money', 'is_correct' => false],
                        ['text' => 'A physical card reader', 'is_correct' => false],
                        ['text' => 'A web link that opens a branded payment page', 'is_correct' => true],
                        ['text' => 'A government tax receipt', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In an overpayment scam, what does the scammer usually ask for?',
                    'explanation' => 'The scammer sends too much money and then asks the seller to refund the difference, often from a stolen wallet.',
                    'options' => [
                        ['text' => 'A discount on the next order', 'is_correct' => false],
                        ['text' => 'A refund of the extra amount sent', 'is_correct' => true],
                        ['text' => 'Free delivery', 'is_correct' => false],
                        ['text' => 'A product sample', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is good payment safety practice?',
                    'explanation' => 'Keeping screenshots and reference numbers helps you resolve disputes and track your income.',
                    'options' => [
                        ['text' => 'Share your mobile money PIN with a trusted friend', 'is_correct' => false],
                        ['text' => 'Accept overpayments and refund the difference quickly', 'is_correct' => false],
                        ['text' => 'Keep records of every payment and reference number', 'is_correct' => true],
                        ['text' => 'Send goods before confirming payment', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A customer screenshot is enough proof that a mobile money payment was successful.',
                    'explanation' => 'Screenshots can be edited. Always verify the payment on your own device or dashboard.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Lenco payment links allow customers to pay using card, bank transfer, or mobile money.',
                    'explanation' => 'Lenco provides multiple payment options through a single link.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for the process of moving money from a payment provider to the seller\'s bank account? (One word)',
                    'explanation' => 'Settlement is the transfer of funds from the payment processor to the seller.',
                    'correct_answer' => 'Settlement',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What should you use instead of your personal phone number for business payments? (Two words)',
                    'explanation' => 'A dedicated business line keeps sales separate from personal calls and improves record-keeping.',
                    'correct_answer' => 'Business line',
                ],
            ],
        ];
    }


    // =========================================================================
    // MODULE 4
    // =========================================================================

    private function module4Lessons(): array
    {
        return [
            [
                'title' => 'Lesson 4.1: Safe Packaging on a Budget',
                'duration_minutes' => 60,
                'content' => $this->lesson4_1(),
            ],
            [
                'title' => 'Lesson 4.2: Delivery Options: Courier, Bus Parcel, and Local Pickup',
                'duration_minutes' => 75,
                'content' => $this->lesson4_2(),
            ],
            [
                'title' => 'Lesson 4.3: Handling Load-Shedding and Customer Updates',
                'duration_minutes' => 60,
                'content' => $this->lesson4_3(),
            ],
            [
                'title' => 'Module 4 Quiz: Packing and Delivering Orders',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your understanding of packaging, delivery options in Zambia, and communicating with customers during load-shedding. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson4_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to pack products safely using low-cost materials, protect fragile items during transport, and create a simple, professional unboxing experience for your customers.</p>

<h2>Why Packaging Matters</h2>
<p>Packaging is part of your product. A customer who receives a broken jar of honey or a crushed chitenge bag will not buy again and may tell others not to buy. Good packaging protects your product, shows that you are professional, and can even be shared on social media as an unboxing photo.</p>
<p>You do not need expensive branded boxes to start. Clean, sturdy materials from local shops and recycled items are enough. As you grow, you can invest in printed labels and custom boxes.</p>

<h2>Basic Packaging Supplies</h2>
<p>Start with these affordable supplies:</p>
<ul>
<li><strong>Clear plastic bags</strong>: For small items like jewellery, dried foods, or accessories.</li>
<li><strong>Bubble wrap or old newspapers</strong>: For cushioning fragile items.</li>
<li><strong>Cardboard boxes</strong>: Available at shops or can be collected from deliveries.</li>
<li><strong>Brown tape</strong>: To seal boxes securely.</li>
<li><strong>Printed or handwritten labels</strong>: With the customer's name, phone number, and delivery address.</li>
<li><strong>Thank-you note</strong>: A small paper with your business name, social media handles, and a short message.</li>
</ul>

<h2>Packing Different Products</h2>
<p><strong>Glass jars and bottles</strong>: Wrap each jar in bubble wrap or newspaper and place it in a box with cushioning on all sides. Mark the box "FRAGILE" on the top and sides.</p>
<p><strong>Clothing and fabrics</strong>: Fold neatly, place in a plastic sleeve or wrap in tissue paper, and seal in a waterproof bag. Chitenge fabric should be protected from moisture and dirt.</p>
<p><strong>Food items</strong>: Use food-grade bags or containers. Seal tightly to prevent leaks and smells. Include a label with the production date and any storage instructions.</p>
<p><strong>Electronics and accessories</strong>: Use anti-static bags if possible, or wrap in bubble wrap. Fill empty space in the box so the item does not move.</p>

<h2>The Unboxing Experience</h2>
<p>A small thank-you note, a business card, or a sticker can turn a simple delivery into a memorable experience. Write something like, "Thank you for supporting Chitenge Creations. We hope you love your order. Share a photo and tag us!" Happy customers become repeat customers.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose one product from your shop.</li>
<li>Collect basic packaging materials from local shops or recycled items.</li>
<li>Pack the product as if you were sending it to a customer in Lusaka.</li>
<li>Shake the package gently. Does anything move? If yes, add more cushioning.</li>
<li>Take a photo of the packed item and the unboxing experience.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Cushioning</strong>: Material used to protect items from shocks during transport.</li>
<li><strong>Fragile</strong>: An item that is easily broken or damaged.</li>
<li><strong>Unboxing experience</strong>: The customer's first impression when opening a delivered package.</li>
<li><strong>Food-grade packaging</strong>: Materials safe for direct contact with food.</li>
<li><strong>Waterproofing</strong>: Protecting a package from rain or moisture.</li>
</ul>

<h2>Summary</h2>
<p>Good packaging does not have to be expensive. Use clean, sturdy materials, cushion fragile items, label packages clearly, and add a small personal touch. A package that arrives safely and looks professional builds trust and encourages repeat orders.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool/" target="_blank" rel="noopener">Canva Design School — Packaging Design</a></li>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/" target="_blank" rel="noopener">Google Digital Garage — Customer Experience</a></li>
<li><a href="https://www.who.int/news-room/fact-sheets/detail/food-safety" target="_blank" rel="noopener">WHO — Food Safety Basics</a></li>
</ul>
HTML;
    }

    private function lesson4_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to compare courier services, bus parcel delivery, and local pickup options, choose the right method for each order, and explain delivery terms clearly to customers.</p>

<h2>Delivery Connects You to the Whole Country</h2>
<p>One of the biggest advantages of selling online is that you are not limited to customers who walk past your shop. A seller in Kalomo can deliver to Lusaka, Ndola, Kitwe, Livingstone, and beyond. The challenge is choosing the right delivery method for each product, destination, and budget.</p>

<h2>Option 1: Local Pickup</h2>
<p>Local pickup is the simplest and cheapest option. The customer comes to your shop or a safe meeting point, pays, and collects the item. This works well for customers in your own town and for items that are too large or valuable to send by bus. Always arrange pickups in public, well-lit places, and consider having a friend with you for safety.</p>

<h2>Option 2: Bus Parcel Services</h2>
<p>Bus parcel services are a popular and affordable way to send packages between towns in Zambia. You take your packed item to a bus company office, pay a parcel fee, and the bus carries it to the destination town. The customer collects it from the bus office on arrival. This method works well along major routes such as the Great North Road between Lusaka and the Copperbelt, and south to Livingstone.</p>
<p>Bus parcel is cheap, but it has risks. Packages may be delayed, rough-handled, or lost. Always get a receipt with a reference number, take a photo of the receipt, and send the customer the collection details. Never send fragile items by bus without strong packaging.</p>

<h2>Option 3: Courier Companies</h2>
<p>Courier companies offer door-to-door or office-to-office delivery with tracking. They are more expensive than bus parcels but more reliable and safer for valuable or fragile items. Many couriers operate in Lusaka, Ndola, Kitwe, and other major towns. Some also serve smaller towns on regular routes. Ask about their coverage, pricing by weight, and delivery time before you promise a customer.</p>

<h2>Choosing the Right Method</h2>
<table>
<tr><th>Method</th><th>Best For</th><th>Cost</th><th>Reliability</th></tr>
<tr><td>Local pickup</td><td>Same-town customers, heavy items</td><td>Free or low</td><td>High</td></tr>
<tr><td>Bus parcel</td><td>Light, non-fragile items to towns on main routes</td><td>Low</td><td>Medium</td></tr>
<tr><td>Courier</td><td>Valuable, fragile, or urgent items</td><td>Higher</td><td>High</td></tr>
</table>

<h2>Communicating Delivery Details</h2>
<p>Always confirm the customer's full name, phone number, and collection point before sending. Tell them the expected delivery day, the bus or courier name, and any reference number. Ask them to confirm when they receive the package. Clear communication prevents lost packages and angry customers.</p>

<h2>Try It Yourself</h2>
<ol>
<li>List three delivery methods available in your area.</li>
<li>Find out the cost to send a 1kg package to Lusaka, Ndola, and Kitwe using each method.</li>
<li>Write a standard delivery message you will send to customers after dispatch.</li>
<li>Decide which method you will use for fragile items, heavy items, and cheap items.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Courier</strong>: A company that delivers packages door-to-door or office-to-office.</li>
<li><strong>Bus parcel</strong>: A service that sends packages with passenger buses between towns.</li>
<li><strong>Local pickup</strong>: When the customer collects the order from the seller.</li>
<li><strong>Door-to-door delivery</strong>: Delivery directly to the customer's address.</li>
<li><strong>Tracking number</strong>: A code used to follow a package's journey.</li>
</ul>

<h2>Summary</h2>
<p>Each delivery method has its place. Use local pickup for nearby customers, bus parcels for affordable long-distance delivery, and couriers for valuable or fragile items. Clear communication about delivery details keeps customers informed and builds trust.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.zra.org.zm/" target="_blank" rel="noopener">Zambia Revenue Authority</a></li>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/" target="_blank" rel="noopener">Google Digital Garage — Logistics Basics</a></li>
<li><a href="https://www.rra.gov.zm/" target="_blank" rel="noopener">Road Transport and Safety Agency</a></li>
</ul>
HTML;
    }

    private function lesson4_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to keep customers informed during load-shedding, manage delays professionally, and use simple tools to track orders and communication.</p>

<h2>Load-Shedding Is Part of Business</h2>
<p>In Zambia, power outages are a reality for every business. Load-shedding can stop you from taking photos, confirming mobile money payments, replying to WhatsApp messages, or printing labels. Customers understand this if you communicate clearly, but they become frustrated if you disappear without explanation.</p>

<h2>Prepare for Outages</h2>
<p>Plan ahead so load-shedding does not stop your business completely. Keep these habits:</p>
<ul>
<li>Charge your phone and a power bank every day.</li>
<li>Keep a notebook with customer orders and phone numbers in case your phone dies.</li>
<li>Download mobile money apps and keep backup USSD codes written down.</li>
<li>Use a solar lamp or small generator if you need light for packing or photography.</li>
<li>Schedule important work, such as posting products or confirming payments, when power is most likely to be available.</li>
</ul>

<h2>Communicating Delays</h2>
<p>If load-shedding or network problems delay your response, send a brief, honest message. Example: "Good afternoon. We are currently experiencing a power outage. I will confirm your payment and send your tracking details as soon as power returns. Thank you for your patience." Most customers appreciate honesty and will wait.</p>
<p>Avoid making promises you cannot keep. If you say "I will deliver tomorrow" but the bus is full, you lose trust. It is better to under-promise and over-deliver.</p>

<h2>Tracking Orders and Messages</h2>
<p>Use a simple spreadsheet or notebook to track each order from payment to delivery. Include columns for customer name, product, amount paid, dispatch date, delivery method, tracking or reference number, and delivery confirmation. Update this log every time something changes.</p>
<p>When you have many orders, it is easy to forget who paid and who is waiting. A tracking system prevents mistakes and helps you answer customer questions quickly.</p>

<h2>Building Long-Term Trust</h2>
<p>Customers remember how you handle problems more than how you handle easy orders. A seller who communicates during load-shedding, fixes a delayed delivery quickly, and apologises sincerely earns loyal customers. A seller who ignores messages loses them forever.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Make a list of everything your business needs electricity for.</li>
<li>Identify one backup plan for each item, such as a power bank, solar lamp, or notebook.</li>
<li>Write two standard messages: one for delayed replies due to load-shedding, and one for delayed delivery.</li>
<li>Create a simple order tracking sheet with at least six columns.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Load-shedding</strong>: Planned power outages to manage electricity demand.</li>
<li><strong>Power bank</strong>: A portable battery used to charge phones and small devices.</li>
<li><strong>USSD code</strong>: A short code used to access services without mobile data.</li>
<li><strong>Order tracking</strong>: Monitoring the status of an order from payment to delivery.</li>
<li><strong>Customer service</strong>: The support and communication a business provides to buyers.</li>
</ul>

<h2>Summary</h2>
<p>Load-shedding and delays are normal in Zambia, but they do not have to ruin your business. Prepare with power backups, keep simple records, communicate honestly with customers, and turn challenges into opportunities to build trust.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.zesco.co.zm/" target="_blank" rel="noopener">ZESCO — Load Management Updates</a></li>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/" target="_blank" rel="noopener">Google Digital Garage — Customer Service</a></li>
<li><a href="https://www.ilongga.org/" target="_blank" rel="noopener">Local Business Support Networks</a></li>
</ul>
HTML;
    }

    private function module4Quiz(): array
    {
        return [
            'title' => 'Module 4 Quiz: Packing and Delivering Orders',
            'description' => 'Assess your understanding of packaging, delivery methods, and customer communication during load-shedding.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which delivery method is usually the cheapest for sending packages between towns in Zambia?',
                    'explanation' => 'Bus parcel services are generally cheaper than couriers, especially along major routes.',
                    'options' => [
                        ['text' => 'International air freight', 'is_correct' => false],
                        ['text' => 'Courier door-to-door delivery', 'is_correct' => false],
                        ['text' => 'Bus parcel service', 'is_correct' => true],
                        ['text' => 'Helicopter delivery', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should you always do when sending a package by bus parcel?',
                    'explanation' => 'A receipt with a reference number helps you and the customer track the package.',
                    'options' => [
                        ['text' => 'Send without any receipt', 'is_correct' => false],
                        ['text' => 'Get a receipt with a reference number', 'is_correct' => true],
                        ['text' => 'Ask the driver to deliver it personally', 'is_correct' => false],
                        ['text' => 'Hide the package under a seat', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which method is best for valuable or fragile items?',
                    'explanation' => 'Couriers offer more reliable handling and often provide tracking for valuable or fragile items.',
                    'options' => [
                        ['text' => 'Bus parcel', 'is_correct' => false],
                        ['text' => 'Local pickup only', 'is_correct' => false],
                        ['text' => 'Courier service', 'is_correct' => true],
                        ['text' => 'Throwing the package over a fence', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the best way to handle customer communication during load-shedding?',
                    'explanation' => 'Honest communication about delays helps maintain customer trust.',
                    'options' => [
                        ['text' => 'Ignore messages until power returns', 'is_correct' => false],
                        ['text' => 'Blame the customer for ordering during outages', 'is_correct' => false],
                        ['text' => 'Send a brief, honest message explaining the delay', 'is_correct' => true],
                        ['text' => 'Promise delivery times you cannot meet', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is it important to track every order in a notebook or spreadsheet?',
                    'explanation' => 'Tracking prevents mistakes, helps answer customer questions, and keeps the business organised.',
                    'options' => [
                        ['text' => 'It is required by Facebook', 'is_correct' => false],
                        ['text' => 'It prevents mistakes and helps answer customer questions', 'is_correct' => true],
                        ['text' => 'It makes the package lighter', 'is_correct' => false],
                        ['text' => 'It reduces delivery fees', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'You should send fragile items by bus parcel without extra cushioning.',
                    'explanation' => 'Bus parcels can be rough-handled. Fragile items need strong cushioning and may be better sent by courier.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A power bank is a useful backup tool for online sellers during load-shedding.',
                    'explanation' => 'Power banks keep phones charged so sellers can communicate and confirm payments during outages.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for planned power outages that affect businesses in Zambia? (Two words)',
                    'explanation' => 'Load-shedding is the planned reduction of electricity supply to manage demand.',
                    'correct_answer' => 'Load shedding',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What major road connects Lusaka to the Copperbelt and is commonly used for bus parcels? (Three words)',
                    'explanation' => 'The Great North Road is a major route used by buses and couriers connecting Lusaka to the Copperbelt.',
                    'correct_answer' => 'Great North Road',
                ],
            ],
        ];
    }


    // =========================================================================
    // MODULE 5
    // =========================================================================

    private function module5Lessons(): array
    {
        return [
            [
                'title' => 'Lesson 5.1: Simple Bookkeeping for Online Sellers',
                'duration_minutes' => 60,
                'content' => $this->lesson5_1(),
            ],
            [
                'title' => 'Lesson 5.2: ZRA Basics Every Online Seller Should Know',
                'duration_minutes' => 60,
                'content' => $this->lesson5_2(),
            ],
            [
                'title' => 'Lesson 5.3: Scaling Beyond Your First Customers',
                'duration_minutes' => 75,
                'content' => $this->lesson5_3(),
            ],
            [
                'title' => 'Module 5 Quiz: Taxes, Records, and Growth',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your understanding of simple bookkeeping, ZRA basics, and growing an online business in Zambia. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson5_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to keep simple records of sales and expenses, calculate your real profit, and use those records to make better business decisions.</p>

<h2>Why Bookkeeping Matters</h2>
<p>Many small business owners know they are busy but do not know if they are actually making money. Bookkeeping is the habit of writing down every Kwacha that comes in and every Kwacha that goes out. With clear records, you can see which products are profitable, which expenses are too high, and whether you can afford to grow.</p>

<h2>The Simple Record System</h2>
<p>You do not need accounting software to start. A simple notebook or spreadsheet with four columns is enough:</p>
<table>
<tr><th>Date</th><th>Item</th><th>Income (K)</th><th>Expense (K)</th></tr>
<tr><td>01 June</td><td>Honey jar sale</td><td>120</td><td></td></tr>
<tr><td>01 June</td><td>Packaging box</td><td></td><td>7</td></tr>
<tr><td>02 June</td><td>Courier fee</td><td></td><td>30</td></tr>
<tr><td>02 June</td><td>Two chitenge bags</td><td>180</td><td></td></tr>
</table>
<p>At the end of each week, add up the income column and the expense column. The difference is your profit for that week. If expenses are higher than income, you are losing money and need to change something.</p>

<h2>Separating Business and Personal Money</h2>
<p>One of the biggest mistakes new sellers make is mixing business money with personal money. They sell a product, use the money to buy airtime or food, and then wonder why the business has no cash. Open a separate mobile money line for business, or use a dedicated wallet. When you need to take money for personal use, record it as "owner's withdrawal" so your records stay honest.</p>

<h2>Tracking Inventory</h2>
<p>If you sell physical products, keep a simple stock list. Write down how many items you have at the start of the month, how many you sold, and how many you have left. This prevents overselling and helps you know when to restock. For example, if you start with 20 honey jars and sell 14, you know you need to source more before you run out.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a simple spreadsheet or notebook with the four columns above.</li>
<li>Record all income and expenses for one week.</li>
<li>Calculate your total profit or loss for the week.</li>
<li>List your current stock for one product and note how many you can still sell.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Bookkeeping</strong>: The practice of recording all business income and expenses.</li>
<li><strong>Income</strong>: Money received from sales.</li>
<li><strong>Expense</strong>: Money spent to run the business.</li>
<li><strong>Profit</strong>: Income minus expenses.</li>
<li><strong>Inventory</strong>: The products a business has available to sell.</li>
</ul>

<h2>Summary</h2>
<p>Simple bookkeeping turns guesses into facts. Record every sale and expense, separate business money from personal money, and track your stock. These habits help you grow from a hobby seller into a real business.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.zra.org.zm/" target="_blank" rel="noopener">Zambia Revenue Authority</a></li>
<li><a href="https://www.khanacademy.org/economics-finance-domain/microeconomics" target="_blank" rel="noopener">Khan Academy — Basic Economics</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/track-finances/" target="_blank" rel="noopener">Microsoft Learn — Track Your Finances</a></li>
</ul>
HTML;
    }

    private function lesson5_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will understand the basic tax responsibilities of an online seller in Zambia, know when to register with ZRA, and learn how to keep records that make tax reporting easier.</p>

<h2>Why Tax Matters for Online Sellers</h2>
<p>Some people think that because they sell on WhatsApp or Facebook, they do not need to worry about tax. This is not true. The Zambia Revenue Authority (ZRA) expects businesses of all sizes to follow tax rules. Paying taxes is also part of building a professional, trustworthy business that can grow and access bigger opportunities.</p>

<h2>When Do You Need to Register?</h2>
<p>If you are selling regularly and making money, you should consider registering your business with the Patents and Companies Registration Agency (PACRA) and then with ZRA. Registration requirements can change, so check the latest rules on the ZRA website or visit a ZRA office. Even if you are very small, keeping good records from day one makes registration much easier when the time comes.</p>

<h2>Types of Taxes to Know</h2>
<p><strong>Income tax</strong> is tax on the profit your business makes. If your business is registered and profitable, you may need to pay income tax based on your annual profit.</p>
<p><strong>Value Added Tax (VAT)</strong> applies to businesses that reach a certain turnover threshold. Most small online sellers will not need to charge VAT at the start, but it is important to know the threshold so you can prepare.</p>
<p><strong>Turnover tax</strong> is a simpler tax option for small businesses with lower sales. ZRA offers turnover tax as a way for small traders to pay tax without complex accounting.</p>

<h2>Keeping Records for Tax</h2>
<p>Keep records of all sales, expenses, and inventory. Keep receipts for packaging, delivery, phone airtime, and any business supplies. These receipts reduce your taxable profit because they are legitimate business costs. A seller who has no records may pay more tax than necessary or struggle to prove their income.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Visit the ZRA website and read the current registration requirements for small businesses.</li>
<li>Make a folder on your phone or computer for business receipts.</li>
<li>Write down three questions you would ask at a ZRA office or from an accountant.</li>
<li>Calculate your approximate monthly profit using your sales and expense records.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>ZRA</strong>: Zambia Revenue Authority, the government body responsible for tax collection.</li>
<li><strong>Income tax</strong>: Tax on the profit a business earns.</li>
<li><strong>VAT</strong>: Value Added Tax, charged on sales once a business reaches a certain size.</li>
<li><strong>Turnover tax</strong>: A simplified tax based on total sales rather than profit.</li>
<li><strong>Taxable profit</strong>: The profit on which tax is calculated, after allowable expenses.</li>
</ul>

<h2>Summary</h2>
<p>Taxes are part of running a real business. Learn the basics, keep clear records, register when required, and ask ZRA or an accountant when you are unsure. Paying taxes protects your business and helps you access bigger opportunities.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.zra.org.zm/" target="_blank" rel="noopener">Zambia Revenue Authority</a></li>
<li><a href="https://www.pacra.org.zm/" target="_blank" rel="noopener">PACRA — Business Registration</a></li>
<li><a href="https://www.smefinanceforum.org/" target="_blank" rel="noopener">SME Finance Forum — Small Business Resources</a></li>
</ul>
HTML;
    }

    private function lesson5_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to identify practical ways to grow your online business, decide when to invest in new tools, and build systems that let you handle more customers without becoming overwhelmed.</p>

<h2>From First Sale to Steady Business</h2>
<p>Your first ten customers teach you what works. Maybe they love your chitenge bags but ask for bigger sizes. Maybe they all found you through WhatsApp status, not Facebook. Pay attention to these patterns. Growth comes from doing more of what works and fixing what does not.</p>

<h2>Signs You Are Ready to Scale</h2>
<p>Scaling means growing in a controlled way. You are ready to scale when:</p>
<ul>
<li>You have steady orders every week.</li>
<li>You understand your costs and profit margins.</li>
<li>You can pack and deliver orders without missing deadlines.</li>
<li>Customers recommend you to others.</li>
<li>You have saved some profit to reinvest in the business.</li>
</ul>
<p>If orders are still random and you are losing money, focus on fixing the basics first. Scaling a broken business only creates bigger losses.</p>

<h2>Practical Growth Steps</h2>
<p><strong>Restock bestsellers</strong>: Identify the products that sell most often and make sure they are always available.</p>
<p><strong>Ask for reviews</strong>: Happy customers are your best marketers. Ask them to leave a comment on Facebook or send a testimonial you can screenshot.</p>
<p><strong>Introduce bundles</strong>: Combine related products at a slight discount. A "honey gift set" with two jar sizes sells better than individual jars.</p>
<p><strong>Expand delivery</strong>: Once you have reliable local sales, try sending packages to new towns. Start with one new route, such as Lusaka to Ndola, and test before expanding further.</p>
<p><strong>Improve your photos and descriptions</strong>: Better content leads to more sales without paying for adverts.</p>

<h2>When to Invest in Tools</h2>
<p>As you grow, you may need better tools. A simple spreadsheet may become a free accounting app. WhatsApp Business may be joined by a Facebook Page with boosted posts. You might buy a ring light for better photos or pay for a courier account. Invest only when the tool saves you time or makes you more money than it costs.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Look at your last ten sales. Which product sold most often?</li>
<li>Write one idea for a product bundle or upsell.</li>
<li>Ask three past customers for feedback or a testimonial.</li>
<li>Choose one growth step from this lesson and plan how to try it in the next 30 days.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Scaling</strong>: Growing a business in a sustainable, controlled way.</li>
<li><strong>Bestseller</strong>: A product that sells more frequently than others.</li>
<li><strong>Upsell</strong>: Offering a higher-value version or related product to increase the sale value.</li>
<li><strong>Testimonial</strong>: A positive statement from a customer used in marketing.</li>
<li><strong>Reinvest</strong>: Putting profit back into the business to help it grow.</li>
</ul>

<h2>Summary</h2>
<p>Growth is not about doing everything at once. It is about doing more of what works, fixing what does not, and adding tools only when your business can afford them. Start small, learn from your customers, and scale one step at a time.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/" target="_blank" rel="noopener">Google Digital Garage — Grow Your Business</a></li>
<li><a href="https://www.facebook.com/business/help" target="_blank" rel="noopener">Meta Business Help Centre — Advertising</a></li>
<li><a href="https://www.canva.com/designschool/" target="_blank" rel="noopener">Canva Design School — Brand Growth</a></li>
</ul>
HTML;
    }

    private function module5Quiz(): array
    {
        return [
            'title' => 'Module 5 Quiz: Taxes, Records, and Growth',
            'description' => 'Assess your understanding of bookkeeping, ZRA basics, and scaling an online business.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main purpose of bookkeeping for an online seller?',
                    'explanation' => 'Bookkeeping records income and expenses so the seller knows whether the business is truly profitable.',
                    'options' => [
                        ['text' => 'To impress customers with fancy software', 'is_correct' => false],
                        ['text' => 'To record every Kwacha in and out to see real profit', 'is_correct' => true],
                        ['text' => 'To avoid talking to suppliers', 'is_correct' => false],
                        ['text' => 'To make the packaging look better', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which Zambian government body is responsible for collecting taxes?',
                    'explanation' => 'The Zambia Revenue Authority (ZRA) administers tax collection in Zambia.',
                    'options' => [
                        ['text' => 'ZESCO', 'is_correct' => false],
                        ['text' => 'PACRA', 'is_correct' => false],
                        ['text' => 'Zambia Revenue Authority (ZRA)', 'is_correct' => true],
                        ['text' => 'Bank of Zambia', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is turnover tax?',
                    'explanation' => 'Turnover tax is a simplified tax based on total sales, designed for small businesses.',
                    'options' => [
                        ['text' => 'A tax on business vehicles', 'is_correct' => false],
                        ['text' => 'A tax based on total sales for small businesses', 'is_correct' => true],
                        ['text' => 'A tax on imported phones', 'is_correct' => false],
                        ['text' => 'A tax paid only by farmers', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a sign that you are ready to scale your online business?',
                    'explanation' => 'Steady weekly orders, known profit margins, and reliable delivery indicate readiness to scale.',
                    'options' => [
                        ['text' => 'You have random orders and unknown costs', 'is_correct' => false],
                        ['text' => 'You are losing money on every sale', 'is_correct' => false],
                        ['text' => 'You have steady orders and understand your profit margins', 'is_correct' => true],
                        ['text' => 'You have no delivery plan', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is a practical way to increase the value of each sale?',
                    'explanation' => 'Bundles and upsells encourage customers to buy more in one transaction.',
                    'options' => [
                        ['text' => 'Delete your WhatsApp Business account', 'is_correct' => false],
                        ['text' => 'Offer product bundles or related items', 'is_correct' => true],
                        ['text' => 'Stop posting on social media', 'is_correct' => false],
                        ['text' => 'Hide your prices', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Online sellers on WhatsApp and Facebook do not need to worry about tax.',
                    'explanation' => 'All regular businesses, including online sellers, should understand and follow tax rules.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Separating business money from personal money makes bookkeeping easier.',
                    'explanation' => 'A dedicated business line or wallet helps keep accurate records and prevents confusion.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for putting profit back into the business to help it grow? (One word)',
                    'explanation' => 'Reinvesting means using profits to improve or expand the business.',
                    'correct_answer' => 'Reinvest',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is a positive customer statement used in marketing called? (One word)',
                    'explanation' => 'A testimonial is a customer endorsement that builds trust with potential buyers.',
                    'correct_answer' => 'Testimonial',
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
        $this->command->info('=== E-Commerce & Online Business Content Seed Summary ===');
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
        $this->command->info('Certificate in E-Commerce & Online Business content seeded successfully.');
    }
}
