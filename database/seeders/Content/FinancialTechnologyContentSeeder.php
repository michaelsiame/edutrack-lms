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

class FinancialTechnologyContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Financial Technology')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Financial Technology" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in Financial Technology already has modules. Skipping content seed.');
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

                // Update module duration
                $module->duration_minutes = $moduleDuration;
                $module->save();

                // Create module quiz
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

            // Assignments
            $this->createAssignments();
        });

        $this->printSummary();
    }

    private function createModules(): array
    {
        $moduleData = [
            [
                'title' => 'Module 1: Mobile Money and Zambia\'s Digital Payment Rails',
                'description' => 'Understand how mobile money works behind the scenes, the role of agents and float, interoperability via the National Financial Switch, digital banking, and e-commerce payments for small businesses.',
            ],
            [
                'title' => 'Module 2: Digital Lending, Savings, and Security',
                'description' => 'Learn to read digital lending terms, calculate the true cost of borrowing, digitise savings groups, protect yourself from fintech fraud, and understand consumer protection in Zambia.',
            ],
            [
                'title' => 'Module 3: Blockchain, Emerging Tech, and Fintech Careers',
                'description' => 'Explore blockchain and cryptocurrency with honest risk warnings, understand the Bank of Zambia stance, discover emerging fintech trends, and map out career pathways in Zambia\'s growing digital finance sector.',
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
                'title' => '1.1 How Mobile Money Works Behind the Scenes',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain how mobile money moves from one person to another, describe the roles of the mobile network operator, the agent, and the customer, and understand why a transaction can succeed or fail even when your phone shows a confirmation message.</p>

<h2>What Is Mobile Money?</h2>
<p>Mobile money is a service that stores funds electronically on your mobile phone number, allowing you to send, receive, and pay without needing a traditional bank account. In Zambia, over ten million adults use mobile money regularly. For a market vendor in Kalomo who sells vegetables at Soweto Market, mobile money means she can accept payment from a customer in Lusaka without ever handling cash. For a farmer in a rural village, it means receiving payment for maize directly on his phone instead of travelling to town.</p>
<p>Unlike a bank account, mobile money is tied to your SIM card and managed by your mobile network operator. Airtel Money and MTN Mobile Money (MoMo) are the two dominant providers in Zambia. When you deposit cash into your mobile wallet, the money does not physically enter your phone. Instead, the mobile network operator records a balance against your phone number in their central system, and you receive an SMS confirming the amount.</p>

<h2>The Three Players in Every Transaction</h2>
<p>Every mobile money transaction involves three parties. Understanding who they are and what they do helps you troubleshoot problems and avoid scams.</p>

<h3>The Customer</h3>
<p>The customer is you, the person sending or receiving money. You initiate transactions by dialling a USSD code, using a mobile app, or asking an agent to help. You must protect your PIN because it is the only barrier between a fraudster and your wallet.</p>

<h3>The Agent</h3>
<p>An agent is a registered business or individual authorised by the mobile network operator to accept cash deposits and process withdrawals. Agents earn a small commission on every transaction. You will recognise them by the branded umbrellas and signs outside shops. Agents hold a balance of physical cash and electronic float, which we will explore in the next lesson.</p>

<h3>The Mobile Network Operator</h3>
<p>Airtel and MTN run the central systems that record balances, route payments, and settle transactions. Their servers handle millions of transactions daily. When you send K100 to your cousin, the operator debits your wallet and credits theirs, usually within seconds.</p>

<h2>How a Send-Money Transaction Works</h2>
<p>Let us follow a real transaction step by step. Mrs Banda wants to send K250 to her son at the University of Zambia:</p>
<ol>
<li>She opens the Airtel Money app on her Android phone and selects "Send Money."</li>
<li>She enters her son's phone number and the amount K250.</li>
<li>The system checks that her wallet balance is at least K250 plus the transaction fee.</li>
<li>She confirms the details and enters her four-digit PIN.</li>
<li>The system encrypts the request and sends it to Airtel's transaction server.</li>
<li>The server verifies her PIN, debits K250 from her wallet, and credits K250 to her son's wallet.</li>
<li>Both Mrs Banda and her son receive SMS confirmations within seconds.</li>
</ol>
<p>If Mrs Banda mistypes the phone number, the money goes to a stranger. This is why you must always double-check the recipient's number before confirming. Some systems now show the recipient's registered name as a safety check.</p>

<h2>Worked Example: Reversing a Wrong Transfer</h2>
<p>Mr Tembo accidentally sends K500 to the wrong number. Here is what he should do:</p>
<ol>
<li>He immediately screenshots the confirmation SMS showing the transaction ID and wrong number.</li>
<li>He dials the mobile money customer care line for his provider.</li>
<li>He provides the transaction ID, his phone number, the recipient's number, and the amount.</li>
<li>Customer care places a hold on the funds if they have not yet been withdrawn.</li>
<li>If the recipient has not spent the money, the operator can reverse the transaction.</li>
<li>Mr Tembo receives a reversal SMS and the K500 returns to his wallet.</li>
</ol>
<p>Important: reversals are not guaranteed. If the recipient has already withdrawn the cash, recovery becomes much harder. Prevention is always better than cure.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open your mobile money app or dial the USSD code for your provider. Check your current balance and write it down.</li>
<li>Look at your last three transaction receipts or SMS confirmations. Identify the transaction ID on each one.</li>
<li>Practise sending a small amount, such as K5, to a trusted family member. Double-check the phone number before confirming.</li>
<li>Find the customer care number for your mobile money provider and save it in your phone contacts.</li>
<li>Ask your nearest mobile money agent to explain their commission structure for deposits and withdrawals.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Mobile wallet</strong> — the electronic balance stored against your phone number by your mobile network operator.</li>
<li><strong>USSD code</strong> — a short code such as *211# that you dial to access mobile money menus without needing an app.</li>
<li><strong>Transaction ID</strong> — a unique reference number generated for every mobile money transaction, used for tracking and disputes.</li>
<li><strong>PIN</strong> — your secret Personal Identification Number that authorises mobile money transactions.</li>
<li><strong>Agent</strong> — an authorised person or shop where you can deposit or withdraw cash for mobile money.</li>
</ul>

<h2>Summary</h2>
<p>Mobile money is not magic. It is a carefully managed system of electronic balances, encrypted messages, and authorised agents. Every transaction moves through your mobile network operator's central servers, and your PIN is the key that unlocks your wallet. By understanding how the system works, you can use it confidently, spot errors quickly, and protect yourself from the most common mistakes such as sending money to the wrong number.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/economics-finance-domain/core-finance">Khan Academy — Core Finance</a></li>
<li><a href="https://grow.google/">Grow with Google — Digital Skills</a></li>
<li><a href="https://www.w3schools.com/">W3Schools — Technology Basics</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.2 Agents, Float, and the National Financial Switch',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what float is and why agents need it, describe how the National Financial Switch enables interoperability between Airtel Money and MTN MoMo, and understand why an agent sometimes cannot serve you even when they have cash.</p>

<h2>What Is Float?</h2>
<p>Float is the electronic balance that a mobile money agent holds in their business wallet. When you hand an agent K200 in cash to deposit into your mobile wallet, the agent does not send your physical notes to Airtel or MTN headquarters. Instead, they transfer K200 from their own electronic float into your wallet. The agent now has K200 more in physical cash but K200 less in electronic float. To stay in business, the agent must periodically "buy" more float from a super-agent or the mobile network operator, exchanging physical cash for electronic balance.</p>
<p>This system works beautifully when float and cash are balanced. But problems arise when they are not. If many customers want to deposit cash and few want to withdraw, the agent runs out of float even though their till is full of banknotes. Conversely, if many customers want to withdraw cash, the agent may have plenty of float but run out of physical money. You have probably heard an agent say, "I have no float" or "I have no cash." Now you understand why.</p>

<h2>How Agents Manage Float</h2>
<p>Successful agents treat float management like inventory control. They forecast busy periods, such as market days or month-end when salaries are paid, and stock up on both cash and float in advance. Some agents work in pairs, with one managing the cash box and the other monitoring the float balance on a dedicated phone. Super-agents, also called master agents, hold large pools of float and distribute it to smaller agents for a fee.</p>
<p>Let us look at a day in the life of Mrs Nkhoma, a mobile money agent in Kalomo:</p>
<ul>
<li>She starts the day with K5,000 in physical cash and K5,000 in electronic float.</li>
<li>By mid-morning, ten customers have deposited K300 each. She now has K8,000 in cash but only K2,000 in float.</li>
<li>She calls her super-agent and transfers K3,000 in cash to buy K3,000 in float. Her balances are restored.</li>
<li>In the afternoon, twelve customers withdraw K200 each. She now has K5,600 in cash and K5,400 in float.</li>
<li>She closes the day balanced, ready for tomorrow.</li>
</ul>

<h2>The National Financial Switch</h2>
<p>Before 2019, Airtel Money and MTN MoMo were separate islands. You could not send money directly from an Airtel number to an MTN number. Customers had to withdraw cash and physically walk to an agent of the other network, which was expensive and time-consuming. In 2019, Zambia launched the <strong>National Financial Switch</strong>, also known as the <strong>Zambia Instant Payment System</strong>, managed by the Bank of Zambia.</p>
<p>The Switch acts like a central translator between different payment systems. When you send money from Airtel Money to an MTN MoMo user, the Switch receives the instruction from Airtel, verifies the recipient's network, and routes the funds to MTN. MTN then credits the recipient's wallet. The entire process usually takes under thirty seconds. The Switch also connects banks and mobile money providers, meaning you can now move money from your bank account to a mobile wallet and vice versa.</p>

<h2>Interoperability in Practice</h2>
<p>Interoperability means that different systems can work together. For a small business owner in Zambia, this is transformative. Mr Banda runs a hardware shop in Kalomo. His customers use both Airtel and MTN. Before interoperability, he needed two phones and two agent relationships. Now, customers can pay him directly regardless of their network, and the funds land in his preferred wallet. He can also pay his supplier in Lusaka by transferring from his mobile wallet to the supplier's bank account through the Switch.</p>

<h2>Worked Example: Sending Across Networks</h2>
<p>Ms Chanda has MTN MoMo and wants to pay K350 to her landlord, who uses Airtel Money:</p>
<ol>
<li>She opens her MTN MoMo app and selects "Send Money."</li>
<li>She enters the landlord's Airtel number. The app recognises it as an Airtel number and shows a slightly higher fee for cross-network transfers.</li>
<li>She enters K350 and confirms.</li>
<li>The MTN system sends the instruction to the National Financial Switch.</li>
<li>The Switch verifies the recipient network and forwards the request to Airtel.</li>
<li>Airtel credits the landlord's wallet.</li>
<li>Both parties receive SMS confirmations. The landlord now has K350 in his Airtel Money wallet.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Visit two mobile money agents in your area. Ask each one how much float they typically hold at the start of the day.</li>
<li>Check the transaction fees in your mobile money app for sending money within the same network versus sending to a different network. Note the difference.</li>
<li>Ask a friend on a different mobile network to send you K2. Time how long the SMS confirmation takes to arrive.</li>
<li>Draw a simple diagram showing the flow of money from a sender on MTN to a receiver on Airtel, including the National Financial Switch in the middle.</li>
<li>Research one benefit of interoperability that you did not know before and share it with a classmate.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Float</strong> — the electronic balance a mobile money agent holds to process customer deposits and withdrawals.</li>
<li><strong>Interoperability</strong> — the ability of different payment systems, such as Airtel Money and MTN MoMo, to work together and transfer funds between each other.</li>
<li><strong>National Financial Switch</strong> — Zambia's central payment infrastructure that connects mobile money providers and banks for seamless transfers.</li>
<li><strong>Super-agent</strong> — a large-scale agent or distributor who supplies float and cash to smaller mobile money agents.</li>
<li><strong>Cross-network transfer</strong> — a mobile money transaction sent from one network provider to another, such as from MTN to Airtel.</li>
</ul>

<h2>Summary</h2>
<p>Mobile money agents are the human face of digital finance, and float is the lifeblood of their business. Without enough float, an agent cannot accept deposits. Without enough cash, they cannot process withdrawals. The National Financial Switch has revolutionised Zambia's payment landscape by connecting Airtel, MTN, and banks into one interoperable network. As a user, this means you can send and receive money freely across networks and between mobile wallets and bank accounts, opening new possibilities for trade and personal finance.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/economics-finance-domain/core-finance">Khan Academy — Core Finance</a></li>
<li><a href="https://grow.google/">Grow with Google — Digital Skills</a></li>
<li><a href="https://www.w3schools.com/">W3Schools — Technology Basics</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.3 Digital Banking and Cards in Zambia',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain the difference between mobile money and a bank account, describe how debit and credit cards work, use internet banking safely, and understand why a Zambian small business might choose one payment method over another.</p>

<h2>Mobile Money versus Bank Accounts</h2>
<p>Mobile money and bank accounts both store money electronically, but they serve different purposes. Mobile money is designed for speed, convenience, and people who may not have formal banking documents. You can open a mobile money wallet with just a registered SIM card and your NRC. Bank accounts, on the other hand, usually require proof of address, a reference, and a minimum deposit. They offer higher transaction limits, interest on savings, and access to loans.</p>
<p>In Zambia, many people use both. A civil servant in Kalomo might receive her salary into a bank account, then transfer a portion to her mobile wallet for daily spending. A market vendor might keep his trading float in mobile money but open a savings account at a bank to earn interest. Understanding the strengths of each system helps you manage money wisely.</p>

<h2>Debit Cards and How They Work</h2>
<p>A debit card is a plastic card linked directly to your bank account. When you swipe it at a shop or enter the details online, the money is deducted immediately from your balance. In Zambia, debit cards issued by banks such as Zanaco, Stanbic, and Absa are widely accepted at supermarkets, fuel stations, and ATMs. Most debit cards now carry a chip that encrypts the transaction data, making them more secure than the older magnetic stripe cards.</p>
<p>When you use a debit card at a point-of-sale machine, several things happen behind the scenes. The machine reads your card and sends the transaction details to your bank through a payment processor. Your bank checks that you have enough money, places a hold on the funds, and sends an approval back to the machine. The entire process takes two to three seconds. The shop receives the money in its bank account within one to three business days.</p>

<h2>Credit Cards in Zambia</h2>
<p>Credit cards are less common in Zambia than debit cards, but they are growing in popularity among professionals and business owners. A credit card allows you to borrow money from the bank up to a set limit. You receive a monthly bill and must repay at least the minimum amount to avoid penalties. The bank charges interest on any balance you carry forward.</p>
<p>Credit cards are useful for large purchases, travel bookings, and building a credit history. However, they can be dangerous if misused. The interest rates on credit cards in Zambia often exceed twenty-five percent per year. If you buy a refrigerator for K8,000 on credit and only pay the minimum each month, you could end up paying more than K12,000 in total. Responsible use means paying the full balance every month and treating the card as a convenience, not a source of extra income.</p>

<h2>Internet and Mobile Banking</h2>
<p>Most Zambian banks now offer internet banking through websites and mobile apps. You can check balances, transfer money, pay bills, and apply for loans without visiting a branch. Security features include one-time passwords sent by SMS, biometric login using your fingerprint, and transaction limits that you can set yourself.</p>
<p>Safe internet banking habits include never logging in from a public computer, always typing the bank's web address manually instead of clicking links in emails, and logging out completely when finished. If your bank offers two-factor authentication, enable it. This means that even if someone steals your password, they cannot access your account without your phone.</p>

<h2>Worked Example: Paying a Supplier by Bank Transfer</h2>
<p>Mrs Zulu owns a grocery shop and needs to pay her supplier in Lusaka K4,500 for a new stock delivery. She uses her bank's mobile app:</p>
<ol>
<li>She opens the Zanaco Mobile App and logs in with her PIN and fingerprint.</li>
<li>She selects "Transfer" and chooses "To Another Zanaco Account."</li>
<li>She selects the supplier from her saved beneficiaries list.</li>
<li>She enters K4,500 and adds a reference: "Stock Delivery June."</li>
<li>The app shows a summary. She confirms and receives an OTP by SMS.</li>
<li>She enters the OTP to authorise the transfer.</li>
<li>The app confirms the transfer and provides a reference number.</li>
<li>She screenshots the confirmation and sends it to her supplier via WhatsApp.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>If you have a bank account, log in to your internet banking or mobile app and explore the menu. List three services you did not know were available.</li>
<li>Compare the fees for sending K1,000 using mobile money versus a bank transfer to another account at the same bank. Which is cheaper?</li>
<li>Look at the front and back of your debit card. Identify the chip, the card number, the expiry date, and the CVV security code.</li>
<li>Check whether your bank offers two-factor authentication and turn it on if it is available.</li>
<li>Write down the customer care number for your bank and keep it separate from your phone in case your phone is stolen.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Debit card</strong> — a plastic card linked to your bank account that deducts money immediately when you make a purchase.</li>
<li><strong>Credit card</strong> — a card that lets you borrow money from a bank up to a set limit, with interest charged on unpaid balances.</li>
<li><strong>Point-of-sale (POS) machine</strong> — the electronic device in shops that reads your card and processes the payment.</li>
<li><strong>Two-factor authentication</strong> — a security feature that requires two different proofs of identity, such as a password and an SMS code.</li>
<li><strong>Internet banking</strong> — managing your bank account online through a website or mobile app.</li>
</ul>

<h2>Summary</h2>
<p>Digital banking and mobile money are complementary tools in Zambia's financial landscape. Bank accounts offer security, interest, and higher limits, while mobile money offers speed and accessibility. Debit cards deduct money straight from your account, while credit cards let you borrow at a cost. Internet banking puts branch services in your pocket, but only if you protect your login details. By choosing the right tool for each situation, you can manage money efficiently whether you are a student, a trader, or a professional.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/economics-finance-domain/core-finance">Khan Academy — Core Finance</a></li>
<li><a href="https://grow.google/">Grow with Google — Digital Skills</a></li>
<li><a href="https://www.w3schools.com/">W3Schools — Technology Basics</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.4 E-Commerce Payments for Small Business',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to set up payment links, accept payments via USSD and QR codes, understand the fees involved in e-commerce transactions, and choose the best payment method for your small business in Zambia.</p>

<h2>Why E-Commerce Payments Matter for Small Businesses</h2>
<p>A few years ago, selling goods online in Zambia meant asking customers to send mobile money before you dispatched the item. Trust was low, fraud was common, and many buyers refused to pay in advance. Today, tools such as payment links, USSD codes, and QR codes have made it safer and easier for small businesses to accept digital payments. A tailor in Kalomo can now sell uniforms to parents in Lusaka, receive payment instantly, and ship the goods with confidence.</p>
<p>E-commerce payments reduce the risks of handling cash, eliminate the need for exact change, and create automatic records of every sale. They also allow you to sell outside your physical location. If you run a chicken-rearing side business, you can accept orders and payments through WhatsApp before the customer even arrives at your gate.</p>

<h2>Payment Links</h2>
<p>A payment link is a web address that you send to a customer. When they click it, they see a page with your business name, the amount to pay, and a button to complete the payment using mobile money or a card. Companies such as Lenco, PayZapp, and Flutterwave provide payment link services in Zambia. You create the link on their website or app, set the amount, and share it via WhatsApp, SMS, email, or social media.</p>
<p>Payment links are ideal for freelancers, event organisers, and small traders who do not have a website. A graphic designer can send a payment link with every invoice. A church treasurer can send a link for tithe collections. The customer pays in seconds, and the business owner receives the funds in their linked bank account or mobile wallet, usually within one business day.</p>

<h2>USSD Payments</h2>
<p>USSD, which stands for Unstructured Supplementary Service Data, is the technology behind the codes you dial to check your airtime balance or access mobile money menus. Businesses can also use USSD to collect payments. A customer dials a short code, enters a merchant ID, types the amount, and confirms with their PIN. The merchant receives the funds instantly.</p>
<p>USSD works on every phone, including the oldest feature phones. This makes it powerful for reaching customers who do not own smartphones. A farmer's cooperative can set up a USSD code for members to pay their monthly contributions. A school can use USSD for parents to pay fees without visiting the bursar's office.</p>

<h2>QR Codes</h2>
<p>A QR code is a square pattern that a smartphone camera can read. When a customer scans your business QR code, their phone opens a payment page pre-filled with your details. They enter the amount and confirm. QR codes are common in supermarkets and restaurants in Lusaka, and they are spreading to smaller towns.</p>
<p>To use QR codes, you need a merchant account with a provider that supports them. The provider generates a unique QR code for your business, which you can print and display at your till, paste on delivery packages, or share digitally. QR payments are fast because the customer does not need to type your phone number or merchant ID manually.</p>

<h2>Understanding the Fees</h2>
<p>Every digital payment method has a cost. Payment link providers typically charge between one and a half and three percent of each transaction. USSD and QR payments may have fixed fees or percentages depending on the provider. Mobile money transfers between individuals usually have lower fees than merchant payments because merchants receive additional services such as instant notifications and settlement reports.</p>
<p>When setting prices, smart business owners account for these fees. If you sell an item for K100 and the payment provider charges two percent, you receive K98. For a high-volume business, these fees add up. Some business owners build the fee into their price by charging K102 instead of K100. Others absorb the fee as a cost of doing business, recognising that the convenience leads to more sales overall.</p>

<h2>Worked Example: Setting Up a Payment Link</h2>
<p>Mr Mwamba runs a small electronics repair shop in Kalomo. He wants customers to pay a deposit before he orders spare parts. He sets up a payment link using a local provider:</p>
<ol>
<li>He downloads the provider's merchant app and registers with his business name, phone number, and NRC.</li>
<li>He verifies his account by uploading a photo of his NRC and a selfie.</li>
<li>In the app, he taps "Create Payment Link" and enters "Repair Deposit" as the description.</li>
<li>He sets the amount to K150, which is his standard deposit for phone screen repairs.</li>
<li>The app generates a link: pay.example.com/mwamba-repairs/150</li>
<li>He copies the link and sends it to a customer via WhatsApp with a message: "Please pay your deposit here. I will order the screen once received."</li>
<li>The customer clicks the link, pays with MTN MoMo, and both receive confirmation messages.</li>
<li>The K150, minus the provider's fee, settles into Mr Mwamba's mobile wallet within twenty-four hours.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Research three payment link providers available in Zambia. Write down their names and one feature of each.</li>
<li>Ask a local shop whether they accept digital payments. If yes, ask which method they use and whether they are happy with the fees.</li>
<li>Practise scanning a QR code with your phone camera. Many Zambian supermarkets display them at the till.</li>
<li>Calculate the total fees you would pay if you sold K5,000 worth of goods through a payment link that charges 2.5 percent per transaction.</li>
<li>Draft a simple WhatsApp message you could send to a customer that includes a payment link and clear instructions on what they are paying for.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Payment link</strong> — a web address that directs a customer to a pre-filled payment page where they can pay by mobile money or card.</li>
<li><strong>USSD</strong> — Unstructured Supplementary Service Data; a technology that allows transactions through short codes dialled on any mobile phone.</li>
<li><strong>QR code</strong> — a square scannable pattern that opens a payment page when scanned by a smartphone camera.</li>
<li><strong>Settlement</strong> — the process by which a payment provider transfers the collected funds to the merchant's account.</li>
<li><strong>Merchant account</strong> — a business account with a payment provider that allows you to accept digital payments from customers.</li>
</ul>

<h2>Summary</h2>
<p>E-commerce payments have opened new doors for Zambian small businesses. Payment links let you collect money through WhatsApp and social media. USSD codes work on every phone, including old feature phones. QR codes make in-person payments fast and error-free. Each method has fees, but the benefits of convenience, safety, and expanded reach usually outweigh the costs. By choosing the right mix of payment tools, you can turn a local stall into a business that serves customers across the country.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/">Grow with Google — Digital Skills</a></li>
<li><a href="https://www.khanacademy.org/economics-finance-domain/core-finance">Khan Academy — Core Finance</a></li>
<li><a href="https://www.w3schools.com/">W3Schools — Technology Basics</a></li>
</ul>
HTML,
            ],
        ];
    }


    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: Mobile Money and Digital Payments',
            'description' => 'Test your understanding of mobile money mechanics, float, the National Financial Switch, digital banking, and e-commerce payments.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does a mobile money agent exchange when they "buy float" from a super-agent?',
                    'explanation' => 'Agents exchange physical cash for electronic float. This restores their ability to process customer deposits.',
                    'options' => [
                        ['text' => 'Physical cash for electronic balance', 'is_correct' => true],
                        ['text' => 'Airtime for mobile data', 'is_correct' => false],
                        ['text' => 'Bank cheques for coins', 'is_correct' => false],
                        ['text' => 'Customer PINs for passwords', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which organisation manages the National Financial Switch in Zambia?',
                    'explanation' => 'The Bank of Zambia manages the National Financial Switch, which connects mobile money providers and banks.',
                    'options' => [
                        ['text' => 'Airtel Zambia', 'is_correct' => false],
                        ['text' => 'MTN Zambia', 'is_correct' => false],
                        ['text' => 'Bank of Zambia', 'is_correct' => true],
                        ['text' => 'Zambia Revenue Authority', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main advantage of interoperability between Airtel Money and MTN MoMo?',
                    'explanation' => 'Interoperability allows customers on different networks to send money directly to each other without withdrawing cash first.',
                    'options' => [
                        ['text' => 'It makes airtime cheaper', 'is_correct' => false],
                        ['text' => 'It allows cross-network money transfers', 'is_correct' => true],
                        ['text' => 'It removes all transaction fees', 'is_correct' => false],
                        ['text' => 'It doubles your wallet balance', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following deducts money immediately from your bank account?',
                    'explanation' => 'A debit card is linked directly to your bank account and deducts funds immediately at the point of sale.',
                    'options' => [
                        ['text' => 'Credit card', 'is_correct' => false],
                        ['text' => 'Debit card', 'is_correct' => true],
                        ['text' => 'Store voucher', 'is_correct' => false],
                        ['text' => 'Loan cheque', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A mobile money agent can run out of float even when they have plenty of physical cash in their till.',
                    'explanation' => 'This is true. Float is electronic balance, not physical cash. If many customers deposit and few withdraw, the agent\'s float decreases while cash increases.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Credit cards in Zambia typically charge interest rates below five percent per year.',
                    'explanation' => 'This is false. Credit card interest rates in Zambia are often above twenty-five percent per year, making them expensive if balances are not paid in full.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the name of the central system that connects Airtel Money, MTN MoMo, and banks in Zambia? (three words)',
                    'explanation' => 'The National Financial Switch, also called the Zambia Instant Payment System, connects different payment providers.',
                    'correct_answer' => 'National Financial Switch',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is a payment link used for in e-commerce?',
                    'explanation' => 'A payment link is a web address that directs customers to a pre-filled payment page where they can complete a purchase.',
                    'options' => [
                        ['text' => 'To download product photos', 'is_correct' => false],
                        ['text' => 'To direct customers to a payment page', 'is_correct' => true],
                        ['text' => 'To track package delivery', 'is_correct' => false],
                        ['text' => 'To apply for a business loan', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which payment method works on both smartphones and old feature phones?',
                    'explanation' => 'USSD codes work on every mobile phone, including basic feature phones, because they do not require an app or internet connection.',
                    'options' => [
                        ['text' => 'QR code payments', 'is_correct' => false],
                        ['text' => 'Mobile app payments', 'is_correct' => false],
                        ['text' => 'USSD payments', 'is_correct' => true],
                        ['text' => 'Contactless card payments', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module2Lessons(): array
    {
        return [
            [
                'title' => '2.1 Digital Lending Apps: Reading Terms and True Cost',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to read and understand the terms and conditions of digital lending apps, calculate the true cost of a loan including fees and interest, compare different loan offers, and recognise warning signs of predatory lending in Zambia.</p>

<h2>The Rise of Digital Lending in Zambia</h2>
<p>Over the past five years, dozens of mobile lending apps have entered the Zambian market. They promise instant loans with no paperwork, no collateral, and no visits to a bank branch. For a market vendor who needs K500 to restock tomatoes before the weekend rush, or a student who needs K300 for transport to write exams, these apps can seem like a lifeline. But convenience comes at a price, and that price is often much higher than it first appears.</p>
<p>Digital lenders use your mobile money transaction history, phone usage data, and even your contacts list to decide whether to lend to you. The entire process from application to disbursement can take less than five minutes. The money lands directly in your mobile wallet. There is no human interaction, no questions asked, and sometimes no clear explanation of what you will actually repay.</p>

<h2>Understanding Interest Rates and Fees</h2>
<p>The biggest mistake borrowers make is looking only at the loan amount and ignoring the total repayment. A lender may advertise "Borrow K1,000 and pay back in thirty days" without prominently displaying that the total repayment is K1,350. That K350 difference represents a thirty-five percent charge for just one month, which annualises to well over four hundred percent if you keep borrowing at that rate.</p>
<p>In Zambia, the Bank of Zambia regulates lending rates, but some digital lenders operate in grey areas or structure their products as "service fees" rather than interest to avoid scrutiny. Always look for the total cost before you accept any loan. The total cost includes:</p>
<ul>
<li><strong>Interest</strong> — the percentage charged on the principal amount borrowed.</li>
<li><strong>Processing fees</strong> — upfront charges deducted before you receive the money.</li>
<li><strong>Late payment penalties</strong> — extra charges if you miss the due date.</li>
<li><strong>Rollover fees</strong> — charges for extending the repayment period.</li>
</ul>

<h2>How to Calculate the True Cost</h2>
<p>Let us look at a real example. Mr Banda borrows K2,000 from a digital lender:</p>
<ul>
<li>The advertised interest rate is five percent per month.</li>
<li>There is a K100 processing fee deducted upfront, so he only receives K1,900.</li>
<li>The repayment is due in thirty days.</li>
<li>If he repays on time, he pays K2,000 principal plus K100 interest, totalling K2,100.</li>
<li>But because he only received K1,900, his true cost is K200 on K1,900 borrowed, which is 10.5 percent for one month.</li>
</ul>
<p>If Mr Banda cannot repay on time and the lender charges a K150 late fee plus rolls the loan over for another month at five percent, his total debt becomes K2,100 plus K150 plus K105, which equals K2,355. He originally borrowed K1,900 and now owes K2,355. That is a true cost of almost twenty-four percent in just two months.</p>

<h2>Reading the Terms and Conditions</h2>
<p>Most people click "I agree" without reading the terms. This is dangerous. The terms document is a contract, and by agreeing to it, you give the lender legal rights that may surprise you. Key clauses to look for include:</p>
<ul>
<li><strong>Data usage</strong> — Does the app access your contacts, photos, or location? Some lenders use your contacts to shame you if you default.</li>
<li><strong>Repayment method</strong> — Can the lender auto-debit your mobile wallet on the due date? If so, will they leave you with nothing for airtime or food?</li>
<li><strong>Default consequences</strong> — What happens if you cannot pay? Are there daily penalty fees? Will they report you to credit bureaus?</li>
<li><strong>Cooling-off period</strong> — Do you have a window to cancel the loan without penalty after accepting it?</li>
</ul>

<h2>Warning Signs of Predatory Lending</h2>
<p>Predatory lenders trap borrowers in cycles of debt. Watch for these red flags:</p>
<ul>
<li>Interest rates or fees that are not clearly stated before you accept the loan.</li>
<li>Pressure to borrow again immediately after repaying a previous loan.</li>
<li>Threats or harassment via phone calls, SMS, or social media if you miss a payment.</li>
<li>Requests for access to your contacts, social media accounts, or passwords.</li>
<li>Loan terms that make it mathematically impossible to repay without borrowing again.</li>
</ul>

<h2>Worked Example: Comparing Two Loan Offers</h2>
<p>Mrs Lungu needs K1,500 urgently. She receives two offers:</p>
<table>
<tr><th>Feature</th><th>Lender A</th><th>Lender B</th></tr>
<tr><td>Loan amount</td><td>K1,500</td><td>K1,500</td></tr>
<tr><td>Processing fee</td><td>K75</td><td>K0</td></tr>
<tr><td>Interest rate</td><td>4% per month</td><td>6% per month</td></tr>
<tr><td>Repayment period</td><td>30 days</td><td>30 days</td></tr>
<tr><td>Late fee</td><td>K100</td><td>K50 per day</td></tr>
</table>
<p>Analysis: Lender A deducts K75 upfront, so she receives K1,425. She repays K1,500 plus K60 interest, totalling K1,560. Her true cost is K135 on K1,425, which is 9.5 percent. Lender B gives her the full K1,500 but charges K90 interest, so she repays K1,590. Her true cost is K90 on K1,500, which is 6 percent. If she repays on time, Lender B is cheaper. But if she is one day late with Lender B, the fee is K50 per day, which quickly makes Lender A the better choice. Mrs Lungu must assess her own ability to repay on time before deciding.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Download the terms and conditions of one digital lending app available in Zambia. Highlight three clauses that concern you.</li>
<li>Calculate the true cost of a K3,000 loan with a K150 processing fee, five percent monthly interest, and a K200 late fee if repaid in forty-five days.</li>
<li>Ask three friends or family members whether they have ever used a digital lending app. If yes, ask whether they read the terms before borrowing.</li>
<li>List five warning signs of predatory lending and keep the list on your phone for reference before borrowing.</li>
<li>Write a short message you would send to a friend advising them to think carefully before taking a digital loan for non-essential spending.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Principal</strong> — the original amount of money borrowed before any interest or fees are added.</li>
<li><strong>Interest rate</strong> — the percentage charged by a lender for borrowing money, usually expressed per month or per year.</li>
<li><strong>Processing fee</strong> — an upfront charge deducted from the loan amount before you receive the funds.</li>
<li><strong>Predatory lending</strong> — lending practices that impose unfair, deceptive, or abusive terms on borrowers.</li>
<li><strong>Default</strong> — failure to repay a loan according to the agreed terms.</li>
</ul>

<h2>Summary</h2>
<p>Digital lending apps offer speed and convenience, but the true cost of borrowing is often hidden in fees, processing charges, and punitive late penalties. Before accepting any loan, read the terms carefully, calculate the total repayment amount, compare multiple offers, and honestly assess your ability to repay on time. Remember that a loan should solve a problem, not create a bigger one. If the terms seem unclear or the pressure feels excessive, walk away.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/economics-finance-domain/core-finance">Khan Academy — Core Finance</a></li>
<li><a href="https://grow.google/">Grow with Google — Digital Skills</a></li>
<li><a href="https://www.w3schools.com/">W3Schools — Technology Basics</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.2 Savings Groups Going Digital',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain how traditional savings groups work, describe the benefits and risks of digitising group savings, set up a simple digital savings system for a small group, and choose the right tools for your community or business association.</p>

<h2>Traditional Savings Groups in Zambia</h2>
<p>Savings groups have existed in Zambian communities for generations. Known by names such as Chilimbas, Village Savings and Loan Associations, or burial societies, these groups bring together friends, neighbours, or workmates who contribute a fixed amount of money regularly, usually weekly or monthly. The pooled funds are then lent to members at low interest, given out as loans for emergencies, or shared equally at the end of a cycle. A typical group might have twenty members who each contribute K50 every week. After fifty weeks, the group has accumulated K50,000, which is distributed back to members or reinvested.</p>
<p>Traditional groups rely on trust, physical record-keeping, and face-to-face meetings. The treasurer keeps a handwritten ledger. The box with the cash is stored in a member's house or a locked cupboard at church. While this system builds strong social bonds, it also has weaknesses. Cash can be stolen. Records can be lost in a fire or flood. A treasurer might make honest mistakes in addition. And members who move to another town often lose track of their contributions.</p>

<h2>Why Go Digital?</h2>
<p>Digitising a savings group means replacing cash and handwritten books with mobile money, bank accounts, spreadsheets, or dedicated savings apps. The benefits are significant:</p>
<ul>
<li><strong>Safety</strong> — Money stored electronically cannot be stolen from a box under a bed. Even if a phone is lost, the mobile wallet is protected by a PIN.</li>
<li><strong>Transparency</strong> — Every transaction leaves a digital trail. Members can check balances and transaction histories at any time.</li>
<li><strong>Accuracy</strong> — Automatic calculations eliminate human error in adding up contributions and interest.</li>
<li><strong>Accessibility</strong> — Members who travel or relocate can continue contributing and receiving their share without attending meetings in person.</li>
<li><strong>Growth</strong> — Digital records make it easier to apply for formal loans or grants from banks and NGOs.</li>
</ul>

<h2>Tools for Digital Savings Groups</h2>
<p>Several tools are available to Zambian savings groups. The right choice depends on the group's size, education level, and access to technology:</p>
<ul>
<li><strong>Dedicated mobile money group accounts</strong> — Some providers offer special business or group wallets with multiple authorised signatories.</li>
<li><strong>Bank group accounts</strong> — Banks such as Zanaco and Stanbic offer group savings accounts with joint signatory requirements.</li>
<li><strong>Spreadsheets</strong> — Google Sheets or Excel can track contributions, loans, and interest for groups comfortable with basic computer skills.</li>
<li><strong>Dedicated apps</strong> — International platforms such as DreamSave and E-Lockbox offer purpose-built savings group management, though internet access is required.</li>
</ul>

<h2>Setting Up a Simple Digital Savings System</h2>
<p>Here is a practical approach for a small group starting out:</p>
<ol>
<li><strong>Choose a treasurer and a co-treasurer.</strong> The treasurer holds the main PIN. The co-treasurer holds a backup record.</li>
<li><strong>Open a group mobile money wallet or bank account.</strong> Register it in the group's name with two required signatories for withdrawals.</li>
<li><strong>Create a shared record.</strong> Use Google Sheets with columns for Date, Member Name, Contribution, Loan Taken, Repayment, and Running Balance. Share the link with all members.</li>
<li><strong>Set rules.</strong> Agree on contribution amounts, meeting frequency, loan limits, interest rates, and penalties for late payment. Write these down and have every member sign.</li>
<li><strong>Collect contributions digitally.</strong> Members send their weekly or monthly contributions to the group wallet. The treasurer records each payment in the spreadsheet.</li>
<li><strong>Review together.</strong> At each meeting, display the spreadsheet or mobile money statement so everyone can verify the balance.</li>
</ol>

<h2>Risks to Manage</h2>
<p>Digitisation is not perfect. Groups must manage these risks:</p>
<ul>
<li><strong>PIN security</strong> — If the treasurer shares the PIN, anyone can withdraw funds. The PIN must be known only to the treasurer and kept physically separate from the phone.</li>
<li><strong>Phone loss or damage</strong> — If the treasurer's phone is stolen, the group may lose access. Always keep a backup of the spreadsheet in Google Drive or another cloud service.</li>
<li><strong>Transaction fees</strong> — Mobile money fees eat into group savings. Calculate fees into the group's budget or choose a bank account with lower charges for bulk transactions.</li>
<li><strong>Technical literacy</strong> — Not every member is comfortable with apps and spreadsheets. Provide patient training and appoint a tech-savvy assistant.</li>
</ul>

<h2>Worked Example: A Digital Chilimba</h2>
<p>Mrs Mutale's church group decides to digitise their weekly Chilimba:</p>
<ol>
<li>The group has fifteen members who each contribute K100 every Sunday.</li>
<li>They open a Zanaco group savings account requiring two signatures for withdrawals.</li>
<li>Mrs Mutale creates a Google Sheet with columns for Week, Member, Amount, and Notes. She shares the link in the group's WhatsApp chat.</li>
<li>Each Sunday, members transfer K100 to the group account using mobile money or bank transfer.</li>
<li>Mrs Mutale updates the sheet every Monday and posts a screenshot in WhatsApp showing the total balance.</li>
<li>At the end of the year, the group has saved K78,000 plus interest, with a complete digital record of every contribution.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Interview a member of a local savings group. Ask how they keep records and what problems they face.</li>
<li>Create a simple Google Sheet for a fictional savings group with ten members. Add sample data for four weeks.</li>
<li>Calculate the total mobile money fees for fifteen members sending K100 each week for fifty-two weeks at current rates.</li>
<li>Draft a simple group agreement with rules for contributions, loans, and withdrawals.</li>
<li>Ask your mobile money provider whether they offer a group or business wallet with multiple signatories.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Savings group</strong> — a community-based organisation where members contribute money regularly and share the pooled funds.</li>
<li><strong>Chilimba</strong> — a traditional Zambian rotating savings group where members take turns receiving the pooled contributions.</li>
<li><strong>Signatory</strong> — a person authorised to access or withdraw funds from a group account.</li>
<li><strong>Interest</strong> — money earned on savings or charged on loans, expressed as a percentage.</li>
<li><strong>Transparency</strong> — openness in financial dealings so that all members can see and verify transactions.</li>
</ul>

<h2>Summary</h2>
<p>Digitising savings groups brings safety, transparency, and accuracy to a tradition that has served Zambian communities for decades. By using mobile money, bank accounts, and shared spreadsheets, groups can protect their funds from theft, eliminate record-keeping errors, and include members who live far away. The key to success is choosing tools that match the group's technical skills, protecting PINs and passwords, and maintaining the social trust that makes savings groups work in the first place.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/economics-finance-domain/core-finance">Khan Academy — Core Finance</a></li>
<li><a href="https://grow.google/">Grow with Google — Digital Skills</a></li>
<li><a href="https://support.google.com/docs/answer/1218656">Google Sheets Help — Create and Edit</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.3 Fintech Security and Fraud Prevention',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to identify the most common fintech fraud schemes in Zambia, protect your mobile money and bank accounts from unauthorised access, respond correctly if you suspect fraud, and adopt daily security habits that keep your digital finances safe.</p>

<h2>Why Fintech Fraud Is Growing</h2>
<p>As more Zambians adopt mobile money, internet banking, and digital payments, criminals have shifted their attention from physical theft to digital fraud. A fraudster no longer needs to snatch a handbag. Instead, they can trick you into sharing your PIN, spoof a mobile money confirmation message, or create a fake lending app that steals your data. The anonymity of digital channels makes fraud harder to trace, and the speed of electronic transfers means stolen money can disappear in seconds.</p>
<p>Fintech fraud is not just a problem for wealthy people. In fact, low-income users are often targeted more aggressively because fraudsters assume they have less education about digital security. A market vendor with K800 in her mobile wallet is just as attractive to a criminal as a businessman with K80,000 in the bank.</p>

<h2>Common Fintech Fraud Schemes in Zambia</h2>
<p>Understanding how fraudsters operate is the first step to protecting yourself. Here are the schemes you are most likely to encounter:</p>

<h3>The Fake Agent</h3>
<p>A fraudster sets up an unauthorised mobile money agent stall with branding that looks identical to Airtel or MTN. Customers deposit cash, but the fraudster keeps the money and issues fake SMS confirmations. By the time victims realise, the agent has packed up and disappeared.</p>

<h3>The PIN Request Scam</h3>
<p>You receive a call from someone claiming to be from your bank or mobile money provider. They say there is a problem with your account and ask for your PIN or OTP to "fix" it. No legitimate provider will ever ask for your PIN. This is always a scam.</p>

<h3>The Spoofed Confirmation</h3>
<p>A buyer sends you a fake SMS that looks exactly like a mobile money confirmation, claiming they have paid for goods. You hand over the goods, but the money never arrives. The SMS was generated by a spoofing app.</p>

<h3>The Fake Loan App</h3>
<p>You download a lending app that asks for your mobile money PIN during registration. Instead of giving you a loan, the app drains your wallet. The app may also copy your contacts and threaten to share embarrassing information if you do not pay a fake debt.</p>

<h3>The Phishing Link</h3>
<p>You receive an email or WhatsApp message with a link to what looks like your bank's login page. You enter your username and password. The page is fake, and the fraudster now has your banking credentials.</p>

<h2>Protecting Your Accounts</h2>
<p>Security is a habit, not a one-time action. Adopt these practices every day:</p>
<ul>
<li><strong>Use strong, unique PINs and passwords.</strong> Do not use your birth year, 1234, or the same PIN for mobile money and your phone lock screen.</li>
<li><strong>Enable two-factor authentication.</strong> If your bank or payment app offers SMS codes or app-based authentication, turn it on.</li>
<li><strong>Never share your PIN or OTP.</strong> Not with friends, not with family, and certainly not with callers claiming to be from your bank.</li>
<li><strong>Verify payment confirmations independently.</strong> Do not trust SMS screenshots. Check your own mobile money balance or bank statement.</li>
<li><strong>Download apps only from official stores.</strong> The Google Play Store and Apple App Store scan for malware. Side-loaded apps are much riskier.</li>
<li><strong>Keep your phone and apps updated.</strong> Updates fix security holes that fraudsters exploit.</li>
<li><strong>Be cautious on public Wi-Fi.</strong> Avoid logging into banking apps on unsecured networks in cafés or markets.</li>
</ul>

<h2>What to Do If You Are a Victim</h2>
<p>If you suspect fraud, speed matters:</p>
<ol>
<li><strong>Change your PINs and passwords immediately.</strong> Do this for mobile money, banking apps, and email.</li>
<li><strong>Contact your bank or mobile money provider.</strong> Report the fraud and ask them to freeze your account if necessary.</li>
<li><strong>Report to the police.</strong> Visit the nearest police station and file a report. Bring all evidence, including SMS messages, transaction IDs, and screenshots.</li>
<li><strong>Report to the Bank of Zambia.</strong> For serious financial fraud, the central bank has channels for consumer complaints.</li>
<li><strong>Warn others.</strong> Share your experience with friends and family so they do not fall for the same scheme.</li>
</ol>

<h2>Worked Example: Spotting a Spoofed Payment</h2>
<p>Mr Chanda sells phone accessories at Soweto Market. A customer buys a screen protector for K150 and shows him an SMS that reads: "You have received K150 from 097X XXX XXX. Ref: ABC123." Instead of handing over the item immediately, Mr Chanda does the following:</p>
<ol>
<li>He opens his own mobile money app and checks his balance. It has not increased.</li>
<li>He checks his transaction history. The payment does not appear.</li>
<li>He politely tells the customer, "I need to see the confirmation on my own phone before I can release the item."</li>
<li>The customer admits the SMS was fake and leaves.</li>
<li>Mr Chanda warns the neighbouring vendors about the scam.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Review your phone's app permissions. Revoke access to your contacts, messages, and camera for any app that does not need them.</li>
<li>Change your mobile money PIN to a number that is not related to your birthday, NRC, or phone number.</li>
<li>Find the customer care numbers for your bank and mobile money provider. Save them and write them down on paper.</li>
<li>Create a simple checklist of five questions to ask yourself before clicking any link in an email or WhatsApp message.</li>
<li>Discuss with a friend what you would do if someone called asking for your mobile money PIN "to fix a system error."</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Fraud</strong> — deliberate deception to secure unfair or unlawful gain, especially in financial transactions.</li>
<li><strong>Spoofing</strong> — creating a fake message, email, or website that appears to come from a legitimate source.</li>
<li><strong>Two-factor authentication</strong> — a security process that requires two different forms of identification to access an account.</li>
<li><strong>OTP</strong> — One-Time Password; a temporary code sent to your phone to verify a transaction or login.</li>
<li><strong>Phishing</strong> — a scam where criminals send fake messages to trick you into giving away passwords or personal information.</li>
</ul>

<h2>Summary</h2>
<p>Fintech fraud is a serious and growing threat in Zambia, but you can protect yourself by understanding how fraudsters operate and adopting strong security habits. Never share your PIN or OTP, verify payment confirmations independently, download apps only from trusted sources, and report fraud immediately. Remember that legitimate banks and mobile money providers will never ask for your PIN over the phone. When in doubt, hang up, log in through the official app, and verify everything yourself.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/economics-finance-domain/core-finance">Khan Academy — Core Finance</a></li>
<li><a href="https://grow.google/">Grow with Google — Digital Skills</a></li>
<li><a href="https://www.w3schools.com/">W3Schools — Technology Basics</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.4 Consumer Protection and Regulation in Zambia',
                'duration_minutes' => 45,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe the role of the Bank of Zambia in regulating financial services, explain your rights as a consumer of digital financial products, identify where to report unfair treatment, and understand how regulation keeps the fintech ecosystem safe.</p>

<h2>Why Regulation Matters</h2>
<p>Without rules, financial markets can become dangerous places. Unregulated lenders can charge impossible interest rates. Unlicensed payment providers can disappear with customer deposits. Fraudsters can operate freely. Regulation exists to protect consumers, maintain trust in the financial system, and ensure that businesses compete fairly. In Zambia, the Bank of Zambia is the primary regulator of banks, mobile money providers, and other financial institutions.</p>

<h2>The Bank of Zambia</h2>
<p>The Bank of Zambia is the central bank of the Republic of Zambia. Its responsibilities include issuing the Kwacha, setting monetary policy, supervising banks, and licensing financial service providers. When it comes to fintech, the Bank of Zambia issues licences to mobile money providers, sets rules for anti-money laundering, and investigates consumer complaints about licensed institutions. If a bank or mobile money operator treats you unfairly, you have the right to complain to the Bank of Zambia.</p>

<h2>Your Rights as a Consumer</h2>
<p>Zambian law and regulatory guidelines protect consumers in several important ways:</p>
<ul>
<li><strong>Right to information</strong> — Providers must disclose fees, interest rates, and terms in clear language before you agree.</li>
<li><strong>Right to fairness</strong> — You cannot be charged hidden fees or subjected to terms you were not told about.</li>
<li><strong>Right to privacy</strong> — Your financial data must be kept confidential and cannot be shared without your consent.</li>
<li><strong>Right to redress</strong> — If something goes wrong, you have the right to complain and receive a fair response.</li>
<li><strong>Right to choose</strong> — Providers cannot force you to buy unwanted products or services as a condition of a loan.</li>
</ul>

<h2>Licensing and Who to Trust</h2>
<p>Before using any financial service, check whether the provider is licensed. Banks display their licence numbers on websites and branch walls. Mobile money providers are licensed by the Bank of Zambia and must meet capital requirements and security standards. If a company refuses to show its licence or claims to be "registered" without specifying the regulator, be cautious. Unlicensed operators are not covered by consumer protection laws and may disappear without warning.</p>

<h2>Where to Report Problems</h2>
<p>If you experience unfair treatment, fraud, or disputes with a licensed provider, you have several options:</p>
<ol>
<li><strong>Contact the provider first.</strong> Most banks and mobile money operators have customer care teams and formal complaint procedures.</li>
<li><strong>Escalate to the Bank of Zambia.</strong> If the provider does not resolve your complaint, file a complaint with the Bank of Zambia's Consumer Protection Unit.</li>
<li><strong>Report fraud to the police.</strong> For criminal activity such as theft, fraud, or hacking, file a report with the Zambia Police.</li>
<li><strong>Seek legal advice.</strong> For large disputes, consider consulting a lawyer or a consumer rights organisation.</li>
</ol>

<h2>Worked Example: Filing a Complaint</h2>
<p>Ms Zulu discovers that her mobile money provider has been deducting a monthly "account maintenance fee" that she was never told about:</p>
<ol>
<li>She gathers evidence: screenshots of her transaction history showing the deductions, and the original terms she agreed to.</li>
<li>She calls customer care and asks for an explanation. The agent cannot justify the fee.</li>
<li>She writes a formal complaint letter to the provider, attaching her evidence and requesting a refund.</li>
<li>After two weeks with no response, she forwards the complaint to the Bank of Zambia with a copy of her original letter.</li>
<li>The Bank of Zambia investigates and orders the provider to refund the fees and stop the practice.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Find the Bank of Zambia website and locate the Consumer Protection section. Note the complaint procedure.</li>
<li>Check whether your bank or mobile money provider publishes its licence number. Write it down.</li>
<li>Read the terms of one financial product you use. Highlight any fees you did not know about.</li>
<li>Draft a simple complaint letter template that you could use if a provider treats you unfairly.</li>
<li>Ask a shop owner whether they know how to verify if a payment provider is licensed in Zambia.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Regulation</strong> — rules and oversight applied by government bodies to ensure fair and safe financial services.</li>
<li><strong>Central bank</strong> — the national bank that manages currency, monetary policy, and financial system stability.</li>
<li><strong>Licence</strong> — official permission granted by a regulator to operate a financial service.</li>
<li><strong>Redress</strong> — the right to have a complaint heard and corrected.</li>
<li><strong>Consumer protection</strong> — laws and policies designed to prevent unfair treatment of customers by businesses.</li>
</ul>

<h2>Summary</h2>
<p>Regulation is not an obstacle to business; it is a foundation of trust. The Bank of Zambia licenses and supervises financial providers to protect consumers like you. As a user of digital financial services, you have rights to information, fairness, privacy, and redress. Always check that a provider is licensed before trusting them with your money. If something goes wrong, complain first to the provider, then escalate to the regulator. Knowing your rights makes you a stronger, safer participant in Zambia's digital economy.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/economics-finance-domain/core-finance">Khan Academy — Core Finance</a></li>
<li><a href="https://grow.google/">Grow with Google — Digital Skills</a></li>
<li><a href="https://www.w3schools.com/">W3Schools — Technology Basics</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Digital Lending, Savings, and Security',
            'description' => 'Test your knowledge of digital lending terms, savings group digitisation, fintech fraud prevention, and consumer protection.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the "true cost" of a digital loan?',
                    'explanation' => 'The true cost includes interest, processing fees, late penalties, and any other charges, not just the advertised interest rate.',
                    'options' => [
                        ['text' => 'Only the advertised interest rate', 'is_correct' => false],
                        ['text' => 'The total amount you repay minus what you actually received', 'is_correct' => true],
                        ['text' => 'The loan amount divided by twelve months', 'is_correct' => false],
                        ['text' => 'The amount the lender advertises on social media', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a warning sign of predatory lending?',
                    'explanation' => 'Predatory lenders often hide fees, pressure borrowers, and use harassment. Clear terms and reasonable rates are signs of legitimate lending.',
                    'options' => [
                        ['text' => 'Fees that are clearly stated before borrowing', 'is_correct' => false],
                        ['text' => 'A cooling-off period of forty-eight hours', 'is_correct' => false],
                        ['text' => 'Pressure to borrow again immediately after repayment', 'is_correct' => true],
                        ['text' => 'A licensed provider with a physical office', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main risk of a savings group treasurer sharing the group wallet PIN?',
                    'explanation' => 'If multiple people know the PIN, any of them could withdraw funds without authorisation, making it impossible to trace who took the money.',
                    'options' => [
                        ['text' => 'The phone battery will drain faster', 'is_correct' => false],
                        ['text' => 'Anyone with the PIN could withdraw funds', 'is_correct' => true],
                        ['text' => 'Mobile money fees will increase', 'is_correct' => false],
                        ['text' => 'The group will receive more interest', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A legitimate bank or mobile money provider may call you and ask for your PIN to fix a system error.',
                    'explanation' => 'This is false. No legitimate provider will ever ask for your PIN or OTP over the phone. This is always a scam.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Digitising a savings group eliminates the need for trust between members.',
                    'explanation' => 'This is false. Digital tools improve transparency and safety, but trust remains essential. Members must still believe the treasurer will act honestly.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the name of Zambia\'s central bank that regulates financial services? (three words)',
                    'explanation' => 'The Bank of Zambia is the central bank responsible for licensing banks, supervising mobile money, and protecting consumers.',
                    'correct_answer' => 'Bank of Zambia',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which action is the safest response to a spoofed mobile money confirmation SMS?',
                    'explanation' => 'Always verify payment by checking your own mobile money balance or transaction history, not by trusting a screenshot or SMS shown by someone else.',
                    'options' => [
                        ['text' => 'Accept the SMS and hand over the goods immediately', 'is_correct' => false],
                        ['text' => 'Check your own mobile money balance and history', 'is_correct' => true],
                        ['text' => 'Ask the buyer to send another SMS', 'is_correct' => false],
                        ['text' => 'Call the buyer\'s friend to confirm', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should you do first if you suspect fintech fraud?',
                    'explanation' => 'Changing your PINs and passwords immediately limits further damage. Then report to your provider and the police.',
                    'options' => [
                        ['text' => 'Post about it on Facebook', 'is_correct' => false],
                        ['text' => 'Change your PINs and passwords immediately', 'is_correct' => true],
                        ['text' => 'Wait to see if more money disappears', 'is_correct' => false],
                        ['text' => 'Confront the suspected fraudster yourself', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a consumer right under Zambian financial regulation?',
                    'explanation' => 'Consumers have the right to clear information about fees and terms before agreeing to any financial product.',
                    'options' => [
                        ['text' => 'Right to unlimited overdrafts', 'is_correct' => false],
                        ['text' => 'Right to information about fees and terms', 'is_correct' => true],
                        ['text' => 'Right to borrow without any identification', 'is_correct' => false],
                        ['text' => 'Right to free mobile data from providers', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module3Lessons(): array
    {
        return [
            [
                'title' => '3.1 What Is Blockchain? A Plain-Language Explanation',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what blockchain is in simple terms, describe how blocks are chained together, understand why blockchain is considered tamper-resistant, and identify real-world uses of blockchain technology beyond cryptocurrency.</p>

<h2>What Is a Blockchain?</h2>
<p>A blockchain is a digital record-keeping system that stores information in a way that makes it very difficult to change or delete. Imagine a village meeting where every decision is written in a large public ledger. After each meeting, the villagers sign the page, make a copy for everyone, and agree that no page can be rewritten once it is signed. If someone tries to change an old decision, everyone else has the original copy and can spot the fraud. A blockchain works on a similar principle, but it uses computers and cryptography instead of pens and paper.</p>
<p>At its core, a blockchain is a chain of blocks. Each block contains a list of transactions or records, a timestamp, and a unique code called a hash. The hash of each block is mathematically linked to the hash of the previous block, creating a chain. If someone tries to alter a transaction in an old block, the hash changes, breaking the chain. Every computer in the network would notice the break and reject the altered block.</p>

<h2>How Blocks Are Chained</h2>
<p>Let us use a simple analogy. Mrs Banda runs a village savings group and keeps a record book. Every week, she writes the week's contributions on a new page. At the bottom of each page, she writes a short code that summarises the entire page. She also copies the code from the previous page onto the new page. If a dishonest member tries to change a contribution on page five, the summary code for page five changes. Because page six already contains the old code from page five, the mismatch is obvious. Everyone knows the book has been tampered with.</p>
<p>In a digital blockchain, this summary code is called a hash. A hash is generated by a mathematical algorithm that turns any amount of data into a fixed-length string of letters and numbers. Even changing a single comma in the data produces a completely different hash. The hash of each block is stored inside the next block, linking them together like a chain.</p>

<h2>Decentralisation and Consensus</h2>
<p>Most traditional record systems have one central authority. A bank keeps the master copy of your account. If the bank's server is hacked or an employee makes a mistake, the record can be changed. Blockchains are typically decentralised, meaning thousands of computers around the world each hold a complete copy of the entire chain. When a new block is added, all the computers check it against their copies. Only when a majority agree that the block is valid is it accepted. This process is called consensus.</p>
<p>Decentralisation makes blockchains resistant to censorship and single points of failure. If one computer goes offline, thousands of others still hold the record. If someone tries to add a fraudulent block, the majority of computers will reject it. However, decentralisation also makes blockchains slower and more energy-intensive than centralised databases.</p>

<h2>Real-World Uses Beyond Cryptocurrency</h2>
<p>Blockchain is most famous for powering Bitcoin and other cryptocurrencies, but the technology has many other applications:</p>
<ul>
<li><strong>Supply chain tracking</strong> — Companies use blockchain to track goods from farm to shelf. A Zambian coffee exporter could prove that beans were grown organically by recording each step on a blockchain.</li>
<li><strong>Land registries</strong> — Some countries use blockchain to record property ownership, making it harder for corrupt officials to alter land records.</li>
<li><strong>Voting systems</strong> — Blockchain-based voting could create tamper-proof election records, though this is still experimental.</li>
<li><strong>Digital identity</strong> — Blockchain can store identity documents in a way that gives individuals control over who sees their data.</li>
<li><strong>Smart contracts</strong> — These are self-executing agreements written in code. For example, a smart contract could automatically release payment to a supplier when a delivery is confirmed.</li>
</ul>

<h2>Limitations of Blockchain</h2>
<p>Blockchain is not a miracle solution. It has real limitations:</p>
<ul>
<li><strong>Speed</strong> — Processing transactions on a blockchain is slower than on a centralised database. Bitcoin handles about seven transactions per second, while Visa handles thousands.</li>
<li><strong>Energy use</strong> — Some blockchain systems use enormous amounts of electricity to secure the network.</li>
<li><strong>Complexity</strong> — Understanding and implementing blockchain requires specialised technical knowledge.</li>
<li><strong>Regulation</strong> — Many governments, including Zambia, have not yet developed clear laws for blockchain applications.</li>
</ul>

<h2>Worked Example: Tracking a Maize Shipment</h2>
<p>A Zambian agricultural cooperative wants to prove that its maize is non-GMO and sustainably grown. It uses a blockchain tracking system:</p>
<ol>
<li>The farmer records the planting date, seed type, and location on the blockchain.</li>
<li>When the maize is harvested, the cooperative records the harvest date and quality test results.</li>
<li>The trucking company records the pickup time and transport conditions.</li>
<li>The warehouse records the arrival time and storage temperature.</li>
<li>The exporter records the shipping container number and destination.</li>
<li>A buyer in Europe scans a QR code on the package and sees the entire journey, verified by the blockchain.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Draw a simple diagram of three blocks in a blockchain. Label the hash of each block and show how each block contains the previous block's hash.</li>
<li>Research one real-world blockchain project in Africa. Write two sentences about what problem it tries to solve.</li>
<li>Explain to a family member what a blockchain is, using the village ledger analogy from this lesson.</li>
<li>List three advantages and three disadvantages of blockchain compared to a traditional database.</li>
<li>Find one news article about blockchain being used for land registries or supply chains. Summarise the main point.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Blockchain</strong> — a digital record-keeping system where blocks of data are linked together in a chain that is very difficult to alter.</li>
<li><strong>Hash</strong> — a unique digital fingerprint generated from data by a mathematical algorithm.</li>
<li><strong>Decentralisation</strong> — the distribution of data and control across many computers rather than one central authority.</li>
<li><strong>Consensus</strong> — the process by which the majority of computers in a blockchain network agree that a new block is valid.</li>
<li><strong>Smart contract</strong> — a self-executing agreement written in computer code that automatically enforces its terms.</li>
</ul>

<h2>Summary</h2>
<p>Blockchain is a tamper-resistant digital record-keeping system that links blocks of data together using cryptographic hashes. Its decentralised nature makes it resistant to fraud and censorship, but it is also slower and more complex than traditional databases. While blockchain powers cryptocurrencies, it also has promising applications in supply chain tracking, land registries, and digital identity. Understanding blockchain means recognising both its potential and its limitations, and approaching it with curiosity rather than hype.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Web">MDN Web Docs — Web Technology</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://www.w3schools.com/">W3Schools — Technology Basics</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.2 Cryptocurrency: Honest Risks, Scams, and the Bank of Zambia Stance',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what cryptocurrency is, describe the major risks including volatility and scams, understand the Bank of Zambia's position on digital currencies, and make an informed personal decision about whether to engage with cryptocurrency.</p>

<h2>What Is Cryptocurrency?</h2>
<p>Cryptocurrency is a type of digital money that exists only on computer networks. Unlike the Kwacha, which is issued by the Bank of Zambia and backed by the government, cryptocurrencies are created and managed by software algorithms. Bitcoin, created in 2009, was the first cryptocurrency. Since then, thousands of others have emerged, including Ethereum, Ripple, and various coins created specifically for African markets.</p>
<p>Cryptocurrencies use blockchain technology to record transactions. When you send cryptocurrency to someone, the transaction is broadcast to the network, verified by computers called miners or validators, and added to the blockchain. There is no central bank, no branch to visit, and no customer care line to call if something goes wrong. You hold your cryptocurrency in a digital wallet, which is protected by a private key, a long password that only you should know.</p>

<h2>The Appeal and the Hype</h2>
<p>Cryptocurrency attracts people for several reasons. Some see it as a way to send money across borders without bank fees. Others view it as an investment that could grow in value. In countries with unstable currencies, some people use cryptocurrency as a store of value. Social media is full of stories about people who bought Bitcoin early and became wealthy. These stories create powerful fear of missing out, or FOMO, which pushes inexperienced people to invest money they cannot afford to lose.</p>
<p>It is essential to separate the technology from the hype. Blockchain is a real and potentially useful innovation. But the price of Bitcoin and other cryptocurrencies is driven by speculation, not by any underlying productive asset. A share in a company represents ownership of a business that earns profits. A Kwacha in the bank is backed by the central bank and the economy. A Bitcoin represents nothing except what the next buyer is willing to pay.</p>

<h2>Major Risks of Cryptocurrency</h2>
<p>Before considering any cryptocurrency, you must understand these risks honestly:</p>

<h3>Extreme Volatility</h3>
<p>The price of Bitcoin has risen and fallen by fifty percent or more within weeks. In 2021, Bitcoin reached nearly $65,000. A year later, it fell below $20,000. If you had invested K10,000 at the peak, your investment would have been worth around K3,000 at the low. This level of volatility is not suitable for savings, school fees, or emergency funds.</p>

<h3>Scams and Fraud</h3>
<p>The cryptocurrency space is filled with scams. Fake exchanges steal deposits. Ponzi schemes promise guaranteed returns. Rug pulls happen when developers create a new coin, hype it on social media, and then disappear with investors' money. In 2022 alone, global cryptocurrency scams stole over $3 billion. Zambia has not been immune. Social media groups promote "investment opportunities" that are nothing more than Ponzi schemes.</p>

<h3>No Consumer Protection</h3>
<p>If your bank makes an error, you can complain to the Bank of Zambia. If a mobile money transaction fails, you can call customer care. If your cryptocurrency is stolen or sent to the wrong address, there is no authority to help you. Transactions on a blockchain are irreversible. There is no undo button.</p>

<h3>Regulatory Uncertainty</h3>
<p>Governments around the world are still deciding how to regulate cryptocurrency. Some have banned it entirely. Others have imposed heavy taxes. In Zambia, the regulatory landscape is evolving, and engaging with unlicensed platforms could expose you to legal risks.</p>

<h2>The Bank of Zambia Stance</h2>
<p>The Bank of Zambia has issued clear public statements about cryptocurrency. It does not recognise cryptocurrencies as legal tender in Zambia. The Kwacha remains the only legal currency. The Bank has warned consumers that cryptocurrency investments are not protected by law and that anyone who invests does so entirely at their own risk.</p>
<p>The Bank of Zambia has also cautioned against unlicensed cryptocurrency exchanges and investment schemes operating in the country. It has stated that it is exploring the potential of central bank digital currencies, which would be digital versions of the Kwacha issued and regulated by the central bank, but these are not the same as private cryptocurrencies like Bitcoin.</p>

<h2>How to Protect Yourself</h2>
<p>If you choose to learn about cryptocurrency, do so safely:</p>
<ul>
<li><strong>Never invest money you cannot afford to lose.</strong> Treat any cryptocurrency purchase as a speculative gamble, not an investment.</li>
<li><strong>Avoid guarantees of returns.</strong> Anyone promising fixed daily or monthly returns from cryptocurrency is running a scam.</li>
<li><strong>Use reputable sources.</strong> Learn from established educational platforms, not from Telegram or WhatsApp groups.</li>
<li><strong>Secure your wallet.</strong> If you create a cryptocurrency wallet, write down your private key on paper and store it in a safe place. Never store it on your phone or in an email.</li>
<li><strong>Verify licences.</strong> Any platform claiming to trade cryptocurrency in Zambia should be able to show its regulatory status. If it cannot, stay away.</li>
</ul>

<h2>Worked Example: Recognising a Crypto Scam</h2>
<p>Mr Phiri sees a Facebook post advertising a "guaranteed twenty percent monthly return on Bitcoin investment." He analyses it:</p>
<ol>
<li>He asks himself whether any legitimate investment guarantees twenty percent monthly. The answer is no. Even the best funds average ten to fifteen percent per year.</li>
<li>He checks whether the company has a physical address, a licence, and verifiable staff. It does not.</li>
<li>He searches online for reviews and finds multiple reports calling it a Ponzi scheme.</li>
<li>He notices that the promoters pressure people to recruit friends, a classic sign of a pyramid scheme.</li>
<li>He reports the post to Facebook and warns his friends not to invest.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Find the Bank of Zambia's official statement on cryptocurrency. Read it and summarise the key warnings in your own words.</li>
<li>Search for news about one major cryptocurrency scam from the past two years. Note how much money was lost and how the scam operated.</li>
<li>Explain to a friend why cryptocurrency is not the same as mobile money, using three clear differences.</li>
<li>Write a short personal policy for yourself: under what conditions, if any, would you consider buying cryptocurrency?</li>
<li>List five red flags that should make you immediately reject any cryptocurrency investment offer.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Cryptocurrency</strong> — a digital form of money that uses cryptography and blockchain technology, not backed by any government.</li>
<li><strong>Volatility</strong> — the degree to which the price of an asset rises and falls rapidly and unpredictably.</li>
<li><strong>Ponzi scheme</strong> — a fraud where returns are paid to earlier investors using money from newer investors, rather than from genuine profits.</li>
<li><strong>Private key</strong> — a secret password that gives access to a cryptocurrency wallet. Anyone with the key controls the funds.</li>
<li><strong>Legal tender</strong> — currency that must be accepted for payment of debts within a country.</li>
</ul>

<h2>Summary</h2>
<p>Cryptocurrency is a fascinating technology, but it is also a high-risk, unregulated, and frequently fraudulent space. The price can collapse by half in weeks, scams are rampant, and there is no consumer protection. The Bank of Zambia does not recognise cryptocurrencies as legal tender and has warned Zambians to approach them with extreme caution. If you choose to engage with cryptocurrency, do so only with money you can afford to lose, avoid any promise of guaranteed returns, and never share your private keys. For most people, especially beginners, the safest approach is to learn and observe rather than invest.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/economics-finance-domain/core-finance">Khan Academy — Core Finance</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://grow.google/">Grow with Google — Digital Skills</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.3 Fintech Careers and the Future of Digital Finance in Zambia',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe the main career pathways in Zambia's fintech sector, identify the skills and qualifications needed for entry-level roles, understand how the digital finance landscape is evolving, and create a personal action plan for pursuing a fintech career.</p>

<h2>The Growth of Fintech in Zambia</h2>
<p>Zambia's fintech sector is expanding rapidly. Mobile money transactions now total billions of Kwacha annually. Banks are digitising their services. Start-ups are building payment solutions for farmers, schools, and health clinics. International companies are hiring local talent. This growth creates jobs for people with a wide range of skills, from software development to customer support, from compliance to sales.</p>
<p>The opportunity is not limited to Lusaka. As internet connectivity improves in towns such as Kalomo, Mongu, and Kasama, digital finance services need local agents, trainers, and support staff. A young person who understands both technology and local business can build a meaningful career without moving to the capital.</p>

<h2>Career Pathways in Fintech</h2>
<p>Fintech careers fall into several categories. You do not need to be a programmer to work in fintech. Here are the main pathways:</p>

<h3>Technology and Development</h3>
<p>Software developers, mobile app developers, and systems administrators build and maintain the platforms that power digital finance. These roles typically require programming skills in languages such as Python, JavaScript, or PHP. A computer science diploma or degree is helpful but not always required if you can demonstrate strong coding skills through a portfolio of projects.</p>

<h3>Operations and Customer Support</h3>
<p>Operations staff manage day-to-day processes such as transaction monitoring, reconciliation, and agent network management. Customer support representatives handle complaints, troubleshoot technical issues, and educate users. These roles require strong communication skills, patience, and a solid understanding of the products. They are often the entry point for people new to the industry.</p>

<h3>Sales and Business Development</h3>
<p>Sales teams sign up merchants, recruit agents, and expand the customer base. Business development professionals identify partnerships and new market opportunities. These roles suit people with strong interpersonal skills, persuasive ability, and an understanding of local business culture. A background in sales, marketing, or entrepreneurship is valuable.</p>

<h3>Compliance and Risk Management</h3>
<p>Compliance officers ensure that the company follows regulations set by the Bank of Zambia and other authorities. Risk managers identify and mitigate threats such as fraud, money laundering, and operational failures. These roles require attention to detail, analytical thinking, and often a background in law, accounting, or finance.</p>

<h3>Product Management and Design</h3>
<p>Product managers decide what features to build and prioritise. User experience designers create interfaces that are easy for customers to use. These roles bridge technology and business, requiring a mix of creativity, analytical thinking, and empathy for users.</p>

<h2>Skills You Need</h2>
<p>Regardless of the specific role, these foundational skills will help you succeed in fintech:</p>
<ul>
<li><strong>Digital literacy</strong> — Comfort with computers, smartphones, spreadsheets, and internet tools.</li>
<li><strong>Financial literacy</strong> — Understanding of basic banking, mobile money, loans, and interest.</li>
<li><strong>Communication</strong> — Ability to explain technical concepts to non-technical people.</li>
<li><strong>Problem-solving</strong> — Willingness to investigate issues and find practical solutions.</li>
<li><strong>Integrity</strong> — Trustworthiness when handling money and sensitive customer data.</li>
<li><strong>Adaptability</strong> — Willingness to learn new tools and adapt to a fast-changing industry.</li>
</ul>

<h2>Building Your Path</h2>
<p>Here is a practical roadmap for entering the fintech sector:</p>
<ol>
<li><strong>Start with education.</strong> Complete courses in digital literacy, finance, or technology. Edutrack's Certificate in Financial Technology is an excellent foundation.</li>
<li><strong>Get certified.</strong> Consider additional certifications in areas such as data analysis, digital marketing, or cybersecurity.</li>
<li><strong>Practise on real platforms.</strong> Open mobile money and internet banking accounts. Use payment links. Understand the user experience from the inside.</li>
<li><strong>Build a network.</strong> Attend fintech events, join online communities, and connect with professionals on LinkedIn.</li>
<li><strong>Start small.</strong> Apply for internships, agent coordinator roles, or customer support positions. These roles teach you the industry from the ground up.</li>
<li><strong>Create a portfolio.</strong> If you are technical, build a simple app or website. If you are business-oriented, write a case study analysing a local fintech product.</li>
</ol>

<h2>The Future of Digital Finance in Zambia</h2>
<p>Several trends will shape the next decade of fintech in Zambia:</p>
<ul>
<li><strong>Financial inclusion</strong> — More rural and low-income citizens will gain access to banking services through mobile technology.</li>
<li><strong>Regulatory technology</strong> — Regulators will use digital tools to monitor transactions and enforce compliance in real time.</li>
<li><strong>Cross-border payments</strong> — Improved interoperability will make it cheaper and faster to send money across African borders.</li>
<li><strong>Credit scoring</strong> — Digital transaction data will enable more accurate credit assessments, expanding access to loans for small businesses.</li>
<li><strong>Artificial intelligence</strong> — AI will be used for fraud detection, customer service chatbots, and personalised financial advice.</li>
</ul>

<h2>Worked Example: A Career Plan</h2>
<p>Grace is a school leaver in Kalomo who wants to work in fintech:</p>
<ol>
<li>She completes the Certificate in Financial Technology at Edutrack Computer Training College.</li>
<li>She takes a free online course in Google Sheets and data analysis.</li>
<li>She volunteers to help her church digitise its offering records using mobile money.</li>
<li>She applies for a customer support role at a mobile money provider in Livingstone.</li>
<li>After one year, she moves into an operations analyst role, monitoring transaction patterns.</li>
<li>After three years, she completes a part-time diploma in business management and becomes a team leader.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>List three fintech career roles that match your current skills and interests.</li>
<li>Research one fintech company operating in Zambia. Write two sentences about what they do and what roles they might hire for.</li>
<li>Create a simple one-page CV highlighting your digital skills. Save it as a PDF.</li>
<li>Find one free online course related to fintech and commit to completing it within thirty days.</li>
<li>Interview someone who works in banking, mobile money, or payments. Ask them how they started and what advice they would give you.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Fintech</strong> — the use of technology to deliver financial services, including mobile money, digital banking, and online lending.</li>
<li><strong>Compliance</strong> — ensuring that a company follows all relevant laws, regulations, and internal policies.</li>
<li><strong>Reconciliation</strong> — the process of comparing financial records to ensure they match and identify discrepancies.</li>
<li><strong>Financial inclusion</strong> — making financial services accessible and affordable to all people, especially those who are underserved.</li>
<li><strong>Portfolio</strong> — a collection of work samples, projects, or case studies that demonstrate your skills to potential employers.</li>
</ul>

<h2>Summary</h2>
<p>Zambia's fintech sector offers growing opportunities for people with diverse skills and backgrounds. Whether you are technically inclined, business-minded, or people-focused, there is a pathway for you. The key is to build a strong foundation in digital and financial literacy, gain practical experience with real platforms, and approach your career with patience and integrity. The future of digital finance in Zambia is bright, and the people who prepare now will be the ones who lead it.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/">Grow with Google — Free Digital Skills Training</a></li>
<li><a href="https://www.khanacademy.org/economics-finance-domain/core-finance">Khan Academy — Core Finance</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn">MDN Web Docs — Learn Web Development</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Blockchain, Crypto, and Fintech Careers',
            'description' => 'Test your understanding of blockchain technology, cryptocurrency risks, the Bank of Zambia stance, and career pathways in fintech.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What makes a blockchain resistant to tampering?',
                    'explanation' => 'Each block contains the hash of the previous block. Changing one block breaks the chain, and the network rejects the alteration.',
                    'options' => [
                        ['text' => 'It is stored on a single powerful computer', 'is_correct' => false],
                        ['text' => 'Each block is linked to the previous block by a hash', 'is_correct' => true],
                        ['text' => 'It uses paper backups in a vault', 'is_correct' => false],
                        ['text' => 'It is protected by a single password', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the Bank of Zambia\'s position on cryptocurrency?',
                    'explanation' => 'The Bank of Zambia does not recognise cryptocurrencies as legal tender and warns consumers that investments are unprotected.',
                    'options' => [
                        ['text' => 'It promotes Bitcoin as the national currency', 'is_correct' => false],
                        ['text' => 'It does not recognise crypto as legal tender and warns consumers', 'is_correct' => true],
                        ['text' => 'It guarantees all cryptocurrency investments', 'is_correct' => false],
                        ['text' => 'It operates its own Bitcoin exchange', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a realistic use of blockchain beyond cryptocurrency?',
                    'explanation' => 'Blockchain can track goods through supply chains, creating transparent and tamper-proof records from producer to consumer.',
                    'options' => [
                        ['text' => 'Replacing all banks worldwide', 'is_correct' => false],
                        ['text' => 'Supply chain tracking', 'is_correct' => true],
                        ['text' => 'Eliminating the need for electricity', 'is_correct' => false],
                        ['text' => 'Printing physical money', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Cryptocurrency transactions can be reversed if you contact customer care.',
                    'explanation' => 'This is false. Blockchain transactions are irreversible. There is no central authority to reverse or refund a mistaken or fraudulent transaction.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A career in fintech always requires a university degree in computer science.',
                    'explanation' => 'This is false. Fintech offers diverse roles in operations, sales, compliance, and support that do not require programming degrees. Skills and practical experience matter greatly.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for a fraud where returns are paid to early investors using money from new investors? (two words)',
                    'explanation' => 'A Ponzi scheme is a fraud that pays early investors with money from newer investors, not from genuine profits.',
                    'correct_answer' => 'Ponzi scheme',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is a smart contract?',
                    'explanation' => 'A smart contract is a self-executing agreement written in code that automatically enforces its terms when conditions are met.',
                    'options' => [
                        ['text' => 'A paper agreement signed by a lawyer', 'is_correct' => false],
                        ['text' => 'A self-executing agreement written in computer code', 'is_correct' => true],
                        ['text' => 'A verbal promise between friends', 'is_correct' => false],
                        ['text' => 'A job contract for software developers', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which skill is most important for an entry-level fintech customer support role?',
                    'explanation' => 'Customer support roles require the ability to communicate clearly, explain products, and resolve issues patiently.',
                    'options' => [
                        ['text' => 'Advanced programming in Python', 'is_correct' => false],
                        ['text' => 'Strong communication and problem-solving skills', 'is_correct' => true],
                        ['text' => 'Ability to design mobile apps', 'is_correct' => false],
                        ['text' => 'Expert knowledge of blockchain mining', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should you do if a cryptocurrency investment promises guaranteed twenty percent monthly returns?',
                    'explanation' => 'Guaranteed high returns are a classic sign of a scam. No legitimate investment can guarantee fixed monthly returns.',
                    'options' => [
                        ['text' => 'Invest as much as possible immediately', 'is_correct' => false],
                        ['text' => 'Treat it as a scam and avoid it', 'is_correct' => true],
                        ['text' => 'Borrow money to invest more', 'is_correct' => false],
                        ['text' => 'Recruit friends to join', 'is_correct' => false],
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
            'title' => 'Analyse a Digital Lending Offer',
            'description' => 'Apply your understanding of digital lending terms, fees, and risks by analysing a real or fictional loan offer and calculating its true cost.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Find a digital lending advertisement or app available in Zambia. If you cannot find one, use the following fictional offer: "Borrow K2,500 with 5% monthly interest, K125 processing fee, and K150 late fee if repaid after 30 days."
Step 2: Identify the principal, interest rate, processing fee, late fee, and repayment period.
Step 3: Calculate the total amount you would repay if you pay on time after 30 days. Show your working.
Step 4: Calculate the total amount you would repay if you are 15 days late. Show your working.
Step 5: Calculate the true cost as a percentage in both scenarios.
Step 6: Write a one-page analysis in a Word document or Google Doc. Include the loan details, your calculations, and a paragraph explaining whether you would take this loan and why.
Step 7: Export the document as a PDF named "Digital_Lending_Analysis.pdf" and upload it here.
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
            'title' => 'Design a Digital Payment System for a Small Business',
            'description' => 'Create a practical plan for a small Zambian business to accept digital payments using payment links, USSD, and QR codes.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Choose a small business scenario. For example: a tailor in Kalomo, a vegetable vendor at Soweto Market, a chicken-rearing side business, or a phone repair shop.
Step 2: Research three payment methods available in Zambia: payment links, USSD codes, and QR codes.
Step 3: Create a one-page business plan document that includes:
  - The business name and what it sells.
  - A description of the typical customer (e.g., local buyers, WhatsApp orders, walk-in customers).
  - Which payment method(s) you would use and why.
  - The estimated fees for each method based on current rates.
  - A step-by-step explanation of how a customer would pay using each method.
  - One security measure you would put in place to prevent fraud.
Step 4: Include a simple diagram or flowchart showing how money moves from the customer to the business.
Step 5: Export the document as a PDF named "Payment_System_Plan.pdf" and upload it here.
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
        $this->command->info('Financial Technology content seeded successfully.');
        $this->command->info('Modules: 3 | Lessons: 11 | Quizzes: 3 | Assignments: 2');
    }
}
