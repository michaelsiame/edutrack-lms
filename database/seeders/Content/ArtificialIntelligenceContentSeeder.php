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

class ArtificialIntelligenceContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Artificial Intelligence')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Artificial Intelligence" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in Artificial Intelligence already has modules. Skipping content seed.');
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
                'title' => 'Module 1: Understanding AI in Everyday Zambia',
                'description' => 'Learn what artificial intelligence really is, recognise AI you already use on your phone and in daily life, and master the basics of talking to ChatGPT and Gemini.',
            ],
            [
                'title' => 'Module 2: AI for Work and Small Business',
                'description' => 'Use AI to write professional letters, CVs, and business plans; create product descriptions and customer replies; and make flyers and images for your business.',
            ],
            [
                'title' => 'Module 3: Staying Safe and Looking Ahead',
                'description' => 'Understand AI risks including scams and deepfakes, learn how to protect your personal data, and explore what AI means for jobs and opportunities in Zambia.',
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
                'title' => '1.1 What Is Artificial Intelligence?',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what artificial intelligence is in simple words, describe what AI can and cannot do, and recognise at least three examples of AI that affect your daily life in Zambia.</p>

<h2>What Is Artificial Intelligence?</h2>
<p>Artificial intelligence, or AI, is a type of computer program that can learn from examples and make decisions without being told exactly what to do for every situation. Think of it like teaching a child to recognise mangoes. At first, you show the child many mangoes of different sizes and colours. After a while, the child learns to spot a mango even if it is a new variety they have never seen before. AI works in a similar way: engineers feed millions of examples into a computer, and the computer learns patterns that help it recognise faces, understand speech, or predict what word you want to type next.</p>
<p>However, AI is not magic. It does not think, feel, or understand the world the way a human does. It is simply very good at spotting patterns in data. A calculator adds numbers faster than you, but nobody calls it intelligent. AI is similar: it processes information at incredible speed, but it has no common sense, no emotions, and no conscience. It is a powerful tool created by people to help people.</p>

<h2>What AI Is Not</h2>
<p>There is a lot of confusion about AI because of films and news headlines. Let us clear up some common myths:</p>
<ul>
<li><strong>AI is not a robot with a body.</strong> Most AI exists only as software inside phones, computers, and internet servers. The chatbot you message is AI; the physical robot in a factory may or may not use AI.</li>
<li><strong>AI does not know everything.</strong> AI learns only from the data it was trained on. If the training data is old, biased, or incomplete, the AI will give wrong or outdated answers.</li>
<li><strong>AI cannot replace human judgment.</strong> A doctor uses AI to analyse X-rays, but the doctor still makes the final diagnosis. A farmer uses weather apps, but the farmer still decides when to plant.</li>
<li><strong>AI is not alive.</strong> It does not have feelings, desires, or intentions. When a chatbot says "I am happy to help," it is just repeating a pattern it learned. It does not feel happiness.</li>
</ul>

<h2>AI You Already Use in Zambia</h2>
<p>Even if you have never opened ChatGPT, you already use AI every day. When you type a message on WhatsApp and your phone suggests the next word, that is AI. When you take a selfie and the camera automatically smooths your skin or brightens the background, that is AI. When MTN MoMo or Airtel Money flags a suspicious transaction and sends you a warning SMS, that is AI detecting fraud patterns. When Google Maps suggests the fastest route from Kalomo to Lusaka, it uses AI to analyse traffic data.</p>
<p>In Zambia, banks use AI to detect unusual spending on your debit card. The ZESCO smart meter in your home uses simple algorithms to estimate your usage. Social media platforms use AI to decide which posts you see first. AI is already woven into the fabric of modern life, and understanding it will help you use it wisely.</p>

<h2>Worked Example: Explaining AI to a Market Vendor</h2>
<p>Mrs Nkhoma runs a vegetable stall at Soweto Market. Her grandson tells her about AI, but she does not understand. He explains it like this:</p>
<ol>
<li>"Grandma, remember how you sort tomatoes? Small ones for K5, medium for K10, large for K15. At first, you had to think hard about each tomato. Now you do it without thinking because you have sorted thousands of tomatoes."</li>
<li>"AI is the same. A computer looks at thousands of photos of tomatoes, and it learns what a small, medium, or large tomato looks like. Then it can sort new tomatoes it has never seen before."</li>
<li>"But if you show the computer a pumpkin, it might still call it a large tomato because it only knows tomatoes. That is why humans must always check the computer's work."</li>
</ol>
<p>Mrs Nkhoma now understands: AI is like experience stored inside a machine. It is fast and helpful, but it still needs human oversight.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Pick up your smartphone. Open any messaging app and start typing a sentence. Notice the three grey words that appear above your keyboard. Tap one of them three times in a row and see what sentence the AI creates.</li>
<li>Open your camera app and switch to selfie mode. Look for options like "Portrait" or "Beauty." Take two photos: one with the filter on and one with it off. Compare them and list two changes the AI made.</li>
<li>Ask a friend or family member what they think AI is. Write down their answer in one sentence. Then explain AI to them using the tomato example from this lesson.</li>
<li>List three apps on your phone that you use every day. For each one, guess whether it uses AI. Write your guesses down and compare them with the answers you learn in Lesson 1.2.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Artificial intelligence (AI)</strong> — computer software that learns patterns from data and uses those patterns to make predictions or decisions.</li>
<li><strong>Algorithm</strong> — a set of rules or instructions that a computer follows to solve a problem or complete a task.</li>
<li><strong>Training data</strong> — the examples and information used to teach an AI system how to recognise patterns.</li>
<li><strong>Pattern recognition</strong> — the ability to identify regularities or repeated features in data, which is the core skill of most AI systems.</li>
<li><strong>Prediction</strong> — using past data to guess what will happen next, such as suggesting the next word in a text message.</li>
</ul>

<h2>Summary</h2>
<p>Artificial intelligence is computer software that learns from examples and spots patterns, allowing it to perform tasks like suggesting words, filtering photos, and detecting fraud. AI is not alive, all-knowing, or capable of replacing human judgment. It is simply a fast and powerful tool. In Zambia, you already encounter AI through predictive text, mobile money security, camera filters, and navigation apps. Understanding what AI really is will help you use it confidently and avoid being misled by hype or fear.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://learndigital.withgoogle.com/digitalgarage">Google Digital Garage — Free AI and Digital Skills Courses</a></li>
<li><a href="https://www.w3schools.com/ai/">W3Schools — Artificial Intelligence Tutorial</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.2 AI You Already Use Every Day',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to identify at least five AI-powered features you already use on your smartphone and online, explain how each one works in simple terms, and appreciate that AI is not a distant future technology but a present reality in your pocket.</p>

<h2>Predictive Text and Autocorrect</h2>
<p>Every time you type a message on WhatsApp, Facebook, or SMS, AI is watching. Predictive text uses patterns from billions of messages to guess what word you want next. If you type "I am going to the market to buy," the AI might suggest "tomatoes" because that word often follows "buy" in sentences about markets. Autocorrect goes further: if you type "tomtoes," the AI recognises the mistake and changes it to "tomatoes" automatically.</p>
<p>This works because the AI was trained on enormous collections of text. It learned that "tomatoes" is a real word and "tomtoes" is a common misspelling. In Zambia, where people often switch between English and local languages in the same message, predictive text can be less accurate. However, the more you type, the more your phone learns your personal style. Over time, it may even start suggesting words you use often, such as names of local places or businesses.</p>

<h2>Camera Filters and Face Unlock</h2>
<p>When you open your camera app and see options like "Portrait," "Night Mode," or "Beauty," you are using AI. The camera detects faces in the frame, separates the person from the background, and applies effects. Portrait mode blurs the background to make the subject stand out. Night mode combines multiple photos taken in quick succession to brighten dark scenes without using a flash. Beauty filters use AI to smooth skin, brighten eyes, and even reshape features slightly.</p>
<p><strong>Face unlock</strong> is another AI feature. When you set up face unlock on your Android phone, the camera captures many angles of your face and converts them into a unique mathematical pattern. Every time you unlock the phone, the camera compares your face to the stored pattern. If they match closely enough, the phone unlocks. This is convenient, but it is not as secure as a PIN or fingerprint, because someone with a photo of your face might trick it.</p>

<h2>Mobile Money Fraud Detection</h2>
<p>Every day in Zambia, thousands of mobile money transactions flow through Airtel Money and MTN MoMo. Behind the scenes, AI systems analyse these transactions in real time to spot fraud. If you normally send K100 to family in Lusaka on weekends, and suddenly a K2,000 transfer is made to an unknown number in the middle of the night, the AI flags it as unusual. The system may block the transaction temporarily and send you an SMS asking you to confirm it.</p>
<p>This protects millions of Zambians from scams. However, the AI is not perfect. Sometimes a genuine transaction looks unusual to the computer, and you may receive a warning even when everything is fine. In those cases, you simply confirm the transaction and continue. The important thing is that the AI acts as a watchful guard, not a prison warden.</p>

<h2>Recommendations and Navigation</h2>
<p>When YouTube suggests a video you might like, or Spotify recommends a song, AI is working. These platforms analyse what you have watched or listened to before, compare your habits with millions of other users, and predict what you will enjoy next. The same happens on Facebook and Instagram, where AI decides which posts appear at the top of your feed.</p>
<p>Google Maps uses AI for route planning. When you search for directions from Kalomo to Livingstone, Maps considers current traffic, road closures, accidents, and even historical data about congestion at different times of day. It calculates the fastest route and updates it as conditions change. This is why Maps can sometimes predict your journey time with surprising accuracy.</p>

<h2>Worked Example: Finding AI on Your Phone</h2>
<p>Mr Banda buys a new Android phone and wants to know which features use AI. He follows these steps:</p>
<ol>
<li>He opens <strong>Settings</strong> and searches for "Smart suggestions." He finds that his phone suggests replies to messages, such as "Thank you" or "I am on my way." This is AI.</li>
<li>He opens the <strong>Camera</strong> and taps the AI icon. The phone recognises that he is photographing food and adjusts the colours to make the meal look more appetising.</li>
<li>He checks <strong>Google Assistant</strong> by saying "Hey Google, what is the weather in Kalomo?" The assistant understands his spoken words, converts them to text, searches the internet, and reads the answer aloud. Speech recognition is AI.</li>
<li>He opens his <strong>Email</strong> app and notices that spam messages have been filtered into a separate folder. The email provider uses AI to classify messages as spam or genuine.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Open your phone's Settings app. Search for "AI" or "Smart." List two AI features you find and write one sentence describing what each does.</li>
<li>Send a message to a friend on WhatsApp. Before you finish typing, tap the suggested word three times. Screenshot the result and share it with your class group.</li>
<li>Open Google Maps and search for directions from your location to the nearest market or bus station. Notice how the app suggests the fastest route. Write down the estimated travel time.</li>
<li>Check your SMS inbox. Look for any message from Airtel Money or MTN MoMo about a suspicious transaction. If you find one, write down what the message said and what you did about it.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Predictive text</strong> — a feature that suggests the next word or phrase as you type, based on patterns learned from large collections of text.</li>
<li><strong>Autocorrect</strong> — a system that automatically fixes spelling mistakes by comparing what you typed to a dictionary of correct words.</li>
<li><strong>Fraud detection</strong> — the use of AI to identify unusual financial transactions that may indicate theft or scams.</li>
<li><strong>Recommendation system</strong> — software that suggests videos, music, products, or posts based on your past behaviour and preferences.</li>
<li><strong>Speech recognition</strong> — technology that converts spoken words into written text so computers can understand voice commands.</li>
</ul>

<h2>Summary</h2>
<p>AI is already a quiet companion in your daily life. It suggests words when you type, beautifies your photos, protects your mobile money, recommends music and videos, and plans your travel routes. Recognising these features helps you understand that AI is not a mysterious future force but a practical tool you already depend on. The next step is learning how to talk directly to AI tools like ChatGPT and Gemini so you can ask them to help with specific tasks.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/android/answer/6174145">Google Android Help — Device Basics</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://learndigital.withgoogle.com/digitalgarage">Google Digital Garage — Free Courses</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.3 Talking to AI: ChatGPT, Gemini and Good Prompting',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to open ChatGPT or Google Gemini, write clear prompts that produce useful answers, refine your prompts when the first answer is not good enough, and avoid common mistakes that waste your time and mobile data.</p>

<h2>What Are ChatGPT and Gemini?</h2>
<p><strong>ChatGPT</strong> is an AI chatbot created by a company called OpenAI. You type questions or instructions in plain English, and it replies with paragraphs of text, lists, tables, or even simple computer code. <strong>Google Gemini</strong> is a similar chatbot made by Google. Both tools are free to use on the web, and both work on smartphones through a browser such as Chrome.</p>
<p>These tools are called <strong>large language models</strong>. They were trained on billions of web pages, books, and articles. They do not browse the internet in real time (though some versions can), and they do not know private information about you. They simply predict what words would form a good response to your question. This means they can be wonderfully helpful, but they can also be confidently wrong. Always treat their answers as a first draft, not a final truth.</p>

<h2>Writing a Good Prompt</h2>
<p>A <strong>prompt</strong> is simply the message you send to the AI. The quality of the answer depends heavily on the quality of the prompt. A vague prompt like "Tell me about business" will give you a vague, general answer. A specific prompt like "Write a one-page business plan for a chicken-rearing business in Kalomo, Zambia, with a starting budget of K5,000" will give you something far more useful.</p>
<p>Here is a simple formula for good prompts:</p>
<ol>
<li><strong>State the task clearly.</strong> What do you want the AI to do? Write? Summarise? Explain? Compare?</li>
<li><strong>Give context.</strong> Who is the audience? Where are you? What is your situation?</li>
<li><strong>Specify the format.</strong> Do you want bullet points, a table, a letter, or a paragraph?</li>
<li><strong>Set limits.</strong> How long should the answer be? Should it be simple or detailed?</li>
</ol>

<h2>Worked Example: Writing a Job Application Letter</h2>
<p>Ms Zulu wants to apply for a shop assistant position at a supermarket in Lusaka. She opens ChatGPT on her phone and tries three prompts:</p>
<blockquote>
<p><strong>Bad prompt:</strong> "Write me a letter."</p>
<p><strong>Result:</strong> A generic, boring letter that could be for anything.</p>
</blockquote>
<blockquote>
<p><strong>Better prompt:</strong> "Write a job application letter for a shop assistant position."</p>
<p><strong>Result:</strong> A decent letter, but it sounds like it was written for America and mentions qualifications she does not have.</p>
</blockquote>
<blockquote>
<p><strong>Best prompt:</strong> "Write a formal job application letter for a shop assistant position at a supermarket in Lusaka, Zambia. I am a 24-year-old woman with a Certificate in Digital Literacy, one year of retail experience at a market stall in Kalomo, and strong customer service skills. The letter should be polite, professional, and no longer than 300 words. Include my contact number +260 97X XXX XXX."</p>
<p><strong>Result:</strong> A personalised, professional letter she can edit and send.</p>
</blockquote>

<h2>Refining Your Prompts</h2>
<p>If the first answer is not quite right, do not give up. Treat the conversation like a chat with a helpful assistant. You can say:</p>
<ul>
<li>"Make it shorter."</li>
<li>"Use simpler words."</li>
<li>"Add a section about my experience with mobile money payments."</li>
<li>"Rewrite this as a WhatsApp message instead of a formal letter."</li>
<li>"Give me three different versions to choose from."</li>
</ul>
<p>Each time you refine the prompt, the AI adjusts its answer. This back-and-forth is called <strong>iterative prompting</strong>, and it is the secret to getting great results. Remember that you are in charge. The AI is a tool; you are the craftsman.</p>

<h2>Common Mistakes to Avoid</h2>
<ul>
<li><strong>Trusting everything the AI says.</strong> AI can make up facts, invent names, and give wrong dates. Always verify important information.</li>
<li><strong>Sharing private information.</strong> Never type your PIN, password, NRC number, or banking details into ChatGPT or Gemini. The companies may store your conversations.</li>
<li><strong>Being too vague.</strong> "Help me with business" is too broad. "Help me write a price list for tomatoes and onions at Soweto Market" is specific.</li>
<li><strong>Using one long prompt when several short ones work better.</strong> Break complex tasks into steps.</li>
<li><strong>Forgetting to check your data bundle.</strong> ChatGPT and Gemini use mobile data. If you are on a limited bundle, keep your sessions short or use Wi-Fi at college.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Open ChatGPT or Gemini in your phone's browser (chat.openai.com or gemini.google.com). Create a free account if you do not have one.</li>
<li>Type this prompt: "Explain artificial intelligence in three simple sentences that a ten-year-old child in Zambia would understand."</li>
<li>Read the answer. Now reply with: "Make it shorter, using only two sentences." Compare the two versions.</li>
<li>Write a prompt asking the AI to create a shopping list for a family of four in Kalomo for one week, with a budget of K800. Review the list and note any items that do not match Zambian eating habits.</li>
<li>Write a prompt asking the AI to draft a polite WhatsApp message to a customer who has not paid for goods delivered last week. The message should be firm but respectful.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Prompt</strong> — the instruction or question you type into an AI chatbot to get a response.</li>
<li><strong>Large language model</strong> — an AI system trained on vast amounts of text that can generate human-like written responses.</li>
<li><strong>Iterative prompting</strong> — the practice of refining your instructions through back-and-forth conversation with an AI to improve the result.</li>
<li><strong>Context</strong> — background information you provide in a prompt to help the AI give a more relevant and accurate answer.</li>
<li><strong>Chatbot</strong> — a computer program that simulates conversation with human users through text messages.</li>
</ul>

<h2>Summary</h2>
<p>ChatGPT and Google Gemini are powerful AI chatbots that respond to written instructions. The quality of their answers depends on how clearly you write your prompts. A good prompt states the task, gives context, specifies the format, and sets limits. When the first answer is not perfect, refine your prompt and try again. Always verify facts, never share private information, and be mindful of your mobile data usage. With practice, you can use these tools to draft letters, create lists, explain concepts, and solve problems in minutes rather than hours.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learndigital.withgoogle.com/digitalgarage">Google Digital Garage — Free AI Courses</a></li>
<li><a href="https://www.w3schools.com/ai/">W3Schools — Artificial Intelligence Tutorial</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module2Lessons(): array
    {
        return [
            [
                'title' => '2.1 Writing with AI: Letters, CVs, and Business Plans',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to use ChatGPT or Gemini to draft professional letters, create a simple curriculum vitae, write a basic lesson plan, and outline a small business plan. You will also learn how to edit AI-generated text so it sounds like your own voice.</p>

<h2>Using AI for Professional Letters</h2>
<p>Writing formal letters can be intimidating, especially if English is not your first language or if you have never written one before. AI can help you get started by creating a clear, polite first draft. You then edit the draft to add personal details and correct any mistakes.</p>
<p>Here are examples of letters you might need:</p>
<ul>
<li>A <strong>job application letter</strong> to a company in Lusaka or Livingstone</li>
<li>A <strong>letter of complaint</strong> to a supplier who delivered damaged goods</li>
<li>A <strong>request letter</strong> to the council for a market stall permit</li>
<li>A <strong>thank-you letter</strong> to a sponsor or donor</li>
<li>A <strong>leave request</strong> to an employer or school head teacher</li>
</ul>
<p>The key is to give the AI enough detail. Instead of "Write a complaint letter," try "Write a formal complaint letter to Zambezi Suppliers in Lusaka. I ordered twenty bags of maize seed on 10 June 2025 for K3,500. Five bags arrived torn and mouldy. I request a replacement or a refund within fourteen days. My business name is Banda Agro Supplies, Kalomo."</p>

<h2>Creating a CV with AI Help</h2>
<p>A <strong>curriculum vitae</strong>, or CV, is a document that lists your education, work experience, skills, and contact details. Employers read CVs to decide whom to interview. A good CV is clear, honest, and no longer than two pages.</p>
<p>To create a CV with AI, gather your information first:</p>
<ol>
<li>Your full name, phone number, email address, and location</li>
<li>Your highest level of education and any certificates</li>
<li>Every job you have had, with the employer's name, your role, and the dates</li>
<li>Skills such as typing, using Microsoft Office, speaking languages, or driving</li>
<li>Any volunteer work or community roles</li>
</ol>
<p>Then write a prompt like: "Create a professional two-page CV for a 28-year-old man from Kalomo, Zambia, with a Certificate in Digital Literacy and two years' experience as a retail assistant. He speaks English and Tonga, can use Google Sheets and Gmail, and volunteers as a treasurer for his church. Format it clearly with sections for Personal Details, Education, Work Experience, Skills, and References."</p>
<p>The AI will generate a structured CV. You must then check every detail. Make sure the phone number is yours, the dates are correct, and the skills listed are genuinely yours. Never claim a qualification you do not have. Employers in Zambia often verify certificates, and dishonesty will damage your reputation.</p>

<h2>Writing Lesson Plans</h2>
<p>If you are a teacher, a PTA member who tutors children, or a community educator, AI can help you plan lessons. A good lesson plan includes:</p>
<ul>
<li>The <strong>topic</strong> and <strong>grade level</strong></li>
<li><strong>Objectives</strong>: what students should know by the end</li>
<li><strong>Materials</strong> needed, such as chalk, paper, or a projector</li>
<li><strong>Activities</strong>: what the teacher and students will do</li>
<li><strong>Assessment</strong>: how to check whether students understood</li>
</ul>
<p>A prompt example: "Write a 45-minute lesson plan for Grade 5 pupils in Zambia about the importance of hand washing. Include objectives, a short story to read aloud, a group activity, and a simple quiz at the end."</p>

<h2>Drafting a Business Plan</h2>
<p>A business plan is a document that describes what your business will do, who your customers are, how much money you need, and how you will make a profit. Banks and investors often ask for a business plan before giving a loan.</p>
<p>AI can create a basic outline, but you must fill in the real numbers. A prompt might be: "Write a one-page business plan for a small egg-selling business in Kalomo. The owner has K4,000 to start, plans to buy fifty layer chickens, and will sell eggs at the local market. Include sections for Business Description, Target Market, Startup Costs, Monthly Expenses, and Expected Profit."</p>
<p>The AI will produce a sensible draft. However, you must research real prices. If the AI guesses that chicken feed costs K150 per bag, but the actual price at Kalomo agro dealers is K280, your plan will be wrong. Always verify costs with local suppliers.</p>

<h2>Worked Example: From AI Draft to Personal Letter</h2>
<p>Mr Chisala wants to apply for a bursary from a local NGO. He uses Gemini to draft the letter, then edits it carefully:</p>
<ol>
<li><strong>AI draft:</strong> He prompts: "Write a formal letter requesting an educational bursary. I am a 22-year-old student from Kalomo studying for a Diploma in Business Administration at Edutrack Computer Training College. My parents are farmers with irregular income. I need K3,500 for tuition and books. I achieved excellent grades in my Certificate in Digital Literacy."</li>
<li><strong>First edit:</strong> He replaces the AI's generic greeting "To Whom It May Concern" with the actual name of the NGO coordinator, Mrs Mutale.</li>
<li><strong>Second edit:</strong> He adds a specific sentence about his ambition to open a computer training centre in a rural area.</li>
<li><strong>Third edit:</strong> He changes the AI's formal closing to something warm but respectful: "I remain hopeful and grateful for your consideration."</li>
<li><strong>Final check:</strong> He reads the letter aloud to his sister. She spots one spelling mistake. He corrects it, prints the letter, and signs it by hand.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Choose one type of letter from this lesson that you might need in the next year. Write a detailed prompt for ChatGPT or Gemini and generate a draft. Edit the draft to make it personal and accurate.</li>
<li>Gather your real education and work details. Write a prompt asking the AI to create a CV for you. Review the output carefully and correct any invented details.</li>
<li>Imagine you want to start a small business selling second-hand clothes. Write a prompt asking the AI for a one-page business plan. Research real prices in Kalomo and update the AI's guesses with accurate figures.</li>
<li>Write a prompt asking the AI to create a 30-minute lesson plan about mobile money safety for adults in your community. Review the plan and add one local example the AI missed.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Curriculum vitae (CV)</strong> — a document summarising your education, work experience, skills, and contact information for potential employers.</li>
<li><strong>Business plan</strong> — a written document describing a business's goals, target customers, costs, and expected profits.</li>
<li><strong>Lesson plan</strong> — a teacher's guide that outlines what will be taught, how it will be taught, and how learning will be checked.</li>
<li><strong>Draft</strong> — a first version of a document that is meant to be reviewed and improved before final use.</li>
<li><strong>Edit</strong> — to read a document carefully and make changes to improve clarity, accuracy, and tone.</li>
</ul>

<h2>Summary</h2>
<p>AI chatbots can help you draft professional letters, build a CV, create lesson plans, and outline business plans in minutes. The key to success is writing detailed prompts that include context, audience, and format. However, AI output is only a starting point. You must edit every draft to add personal details, correct invented facts, verify local prices, and ensure the tone matches your personality. Treat AI as a helpful assistant, not a replacement for your own judgment and effort.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.libreoffice.org/get-help/">LibreOffice Help and Documentation</a></li>
<li><a href="https://grow.google/">Grow with Google — Free Training</a></li>
<li><a href="https://learndigital.withgoogle.com/digitalgarage">Google Digital Garage — Business Skills</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.2 AI for Your Small Business',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to use AI to write product descriptions for online selling, draft polite and professional replies to customer messages, create social media posts for your business, and use AI to help with basic price calculations and budgeting.</p>

<h2>Writing Product Descriptions</h2>
<p>If you sell goods at a market stall, through WhatsApp, or on a Facebook page, good descriptions help customers understand what you offer and why they should buy. A product description is not just a list of features. It should tell the customer how the product will improve their life.</p>
<p>For example, instead of "Brown eggs, K35 per tray," a better description is: "Fresh, free-range brown eggs from healthy village chickens. Each tray contains thirty large, nutrient-rich eggs perfect for family breakfasts, baking, and selling at your own stall. Delivered daily from Kalomo. Order by 6 p.m. for next-morning delivery. K35 per tray, discount available for orders of five trays or more."</p>
<p>AI can generate descriptions like this from simple prompts. Try: "Write a product description for dried kapenta fish sold in 500-gram packets. The fish is sun-dried, salted, and sourced from Lake Kariba. Target customers are families in Lusaka who want affordable, protein-rich meals. Include price K45 per packet and mention bulk discounts."</p>
<p>Always check the AI's output. If it claims the fish is "organic certified" and it is not, remove that claim. False advertising can get you into trouble with customers and regulators.</p>

<h2>Drafting Customer Replies</h2>
<p>Running a business means receiving messages from customers every day. Some ask about prices. Some complain about late deliveries. Some want to negotiate. Responding quickly and politely builds trust and brings repeat customers.</p>
<p>AI can draft replies for common situations:</p>
<ul>
<li><strong>Price enquiry:</strong> "Thank you for your interest. Our tomatoes are K8 per kilogram and onions are K6 per kilogram. We offer a 10 percent discount for orders over K200. Delivery within Kalomo is free. How many kilograms would you like?"</li>
<li><strong>Complaint about quality:</strong> "We are sorry to hear the maize meal did not meet your expectations. Customer satisfaction is important to us. Please send a photo of the product, and we will arrange a replacement or refund within 24 hours."</li>
<li><strong>Late delivery apology:</strong> "We sincerely apologise for the delay. The truck from Lusaka was held up by roadworks. Your order will arrive tomorrow morning by 10 a.m. As an apology, we will add an extra kilogram of tomatoes at no charge."</li>
</ul>
<p>To use AI for replies, paste the customer's message into ChatGPT or Gemini and add a prompt: "Draft a polite, professional reply to this customer message. My business is [name]. The customer says: [paste message]. I want to be firm but friendly."</p>

<h2>Creating Social Media Posts</h2>
<p>Social media is one of the cheapest ways to advertise in Zambia. A good post on Facebook or WhatsApp Status can reach hundreds of potential customers without costing a kwacha. AI can help you write posts that are catchy and clear.</p>
<p>Tips for good social media posts:</p>
<ul>
<li>Keep it short. People scroll quickly on their phones.</li>
<li>Use emojis sparingly to draw attention, but do not overdo it.</li>
<li>Include a clear call to action, such as "Order now" or "Message us today."</li>
<li>Add your phone number or a "Click to message" link.</li>
<li>Mention promotions or limited-time offers to create urgency.</li>
</ul>
<p>A prompt example: "Write a Facebook post for my tailoring business in Kalomo. I am offering 20 percent off school uniform alterations for the month of January. The post should be friendly, mention that bookings are limited, and include a call to action to WhatsApp me on +260 97X XXX XXX. Keep it under 80 words."</p>

<h2>Price Calculations and Budgeting</h2>
<p>AI can help you work out prices and budgets, but you must understand the maths yourself. Never blindly trust AI with money calculations. Use it as a calculator that explains its steps.</p>
<p>For example, you might ask: "I buy chickens at K45 each. I spend K15 per chicken on feed and medicine over six weeks. Each chicken then sells for K90. What is my profit per chicken, and what is my profit margin as a percentage?"</p>
<p>The AI will show you:</p>
<ul>
<li>Total cost per chicken: K45 + K15 = K60</li>
<li>Selling price: K90</li>
<li>Profit per chicken: K90 - K60 = K30</li>
<li>Profit margin: (K30 / K90) × 100 = 33.3 percent</li>
</ul>
<p>Double-check every figure with a calculator. If the AI makes an arithmetic error, you could lose money.</p>

<h2>Worked Example: A Day of AI-Assisted Business</h2>
<p>Mrs Zulu sells handmade chitenge bags online. Here is how she uses AI in one morning:</p>
<ol>
<li>She receives a WhatsApp message asking about custom orders. She copies it into Gemini and asks for a polite reply. She edits the draft and sends it within two minutes.</li>
<li>She needs to post her new bag designs on Facebook. She asks the AI: "Write a short, exciting Facebook post about new chitenge bags with phone pockets and zip closures. Price K85. Available in Kalomo and Lusaka." She adds photos and posts it.</li>
<li>A customer asks for a discount on ten bags. She asks the AI: "If my normal price is K85 per bag, what is a fair bulk price for ten bags that still gives me a 25 percent profit margin if my cost is K55 per bag?" The AI calculates K73 per bag. She rounds it to K70 to make the deal attractive.</li>
<li>She writes a prompt asking the AI to draft a simple monthly budget for her business, listing materials, transport, packaging, and phone data costs.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Choose one product you could sell, real or imaginary. Write a detailed prompt asking the AI to create a product description. Edit the result to make it accurate and compelling.</li>
<li>Write a fake customer complaint message. Paste it into an AI chatbot and ask for three different reply options: apologetic, firm, and solution-focused. Compare them and choose the best.</li>
<li>Ask the AI to write three social media posts for a small business of your choice. Review them for length, tone, and accuracy. Edit at least one and explain why you changed it.</li>
<li>Create a simple maths problem about your own business or a hypothetical one. Ask the AI to solve it and show its working. Check the answer with a calculator.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Product description</strong> — written text that explains what a product is, what it does, and why a customer should buy it.</li>
<li><strong>Call to action</strong> — a clear instruction in an advertisement or post telling the reader what to do next, such as "Order now" or "Call today."</li>
<li><strong>Profit margin</strong> — the percentage of the selling price that is profit, calculated as (profit divided by selling price) multiplied by 100.</li>
<li><strong>Bulk discount</strong> — a reduced price offered to customers who buy a large quantity of a product.</li>
<li><strong>Customer complaint</strong> — a message from a customer expressing dissatisfaction with a product or service.</li>
</ul>

<h2>Summary</h2>
<p>AI can be a valuable assistant for small business owners in Zambia. It helps write product descriptions that attract buyers, draft customer replies that build trust, create social media posts that reach new audiences, and calculate prices and budgets. The key is to give detailed prompts, edit the output for accuracy, verify all calculations yourself, and never make claims your business cannot support. Used wisely, AI saves time and helps you present a professional image, even if you run your business from a single market stall.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/">Grow with Google — Free Business Training</a></li>
<li><a href="https://www.canva.com/designschool/">Canva Design School — Free Marketing and Design Courses</a></li>
<li><a href="https://learndigital.withgoogle.com/digitalgarage">Google Digital Garage — Business Skills</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.3 Creating Images and Flyers with AI',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to use free AI image tools to create simple flyers, social media graphics, and business visuals, describe what an image should look like in words the AI understands, and recognise the legal and ethical limits of using AI-generated images for business.</p>

<h2>What Are AI Image Generators?</h2>
<p>AI image generators are tools that create pictures from written descriptions. You type a sentence like "a colourful poster for a community clean-up day in Kalomo, Zambia, with people sweeping the street and trees in the background," and the AI generates an image that matches your description. These tools do not search the internet for existing photos. Instead, they create new images by combining patterns they learned from millions of pictures during training.</p>
<p>Popular free tools include <strong>Canva's Magic Media</strong>, <strong>Microsoft Copilot Image Creator</strong>, and <strong>Adobe Firefly</strong>. Many of these work inside a web browser and have free tiers that allow a limited number of images per month. This is usually enough for a small business that needs a flyer once a week or a social media graphic a few times a month.</p>

<h2>Writing Good Image Descriptions</h2>
<p>The words you use to describe an image are called a <strong>prompt</strong>, just like with ChatGPT. A good image prompt is specific about:</p>
<ul>
<li><strong>Subject:</strong> What or who is in the picture?</li>
<li><strong>Setting:</strong> Where is it happening?</li>
<li><strong>Style:</strong> Do you want a photograph, a cartoon, a painting, or a poster?</li>
<li><strong>Colours and mood:</strong> Bright and cheerful? Professional and serious?</li>
<li><strong>Text:</strong> Do you want words on the image, or will you add those later?</li>
</ul>
<p>For example:</p>
<blockquote>
<p><strong>Weak prompt:</strong> "A flyer for a chicken business."</p>
<p><strong>Strong prompt:</strong> "A bright, professional flyer poster for a poultry business in Zambia. Show healthy brown chickens in a clean coop. Include green vegetables and a basket of fresh brown eggs in the foreground. Sunny day, blue sky, African rural setting. Style: colourful digital illustration, no text."</p>
</blockquote>

<h2>Creating a Simple Flyer</h2>
<p>A flyer is a single-page advertisement designed to catch attention quickly. Whether you print it on A4 paper and post it on community notice boards or share it as a JPEG on WhatsApp, the principles are the same:</p>
<ol>
<li><strong>Headline:</strong> A large, short phrase that tells people what the flyer is about. "Fresh Eggs Daily — Kalomo" or "Learn to Use Computers — Enrol Now."</li>
<li><strong>Image:</strong> A clear, attractive picture that relates to your message.</li>
<li><strong>Details:</strong> The what, when, where, and how much. Keep it brief.</li>
<li><strong>Contact:</strong> Phone number, WhatsApp link, or location. Make it easy to find.</li>
<li><strong>Call to action:</strong> Tell people what to do. "Order by WhatsApp" or "Visit us today."</li>
</ol>
<p>Most small business owners use <strong>Canva</strong> for this because it is free and easy. You can create an account at canva.com, choose a flyer template, replace the placeholder text with your own details, and add an AI-generated image. The whole process takes less than thirty minutes, even on a smartphone.</p>

<h2>Adding Text to Images</h2>
<p>AI image generators are not always good at putting readable text inside pictures. The letters may be blurry, misspelled, or in the wrong language. A safer approach is to generate the image without text, then add the text yourself using Canva, Pixlr, or even Microsoft Word.</p>
<p>Tips for text on flyers:</p>
<ul>
<li>Use large, bold fonts for the headline. Avoid fancy fonts that are hard to read on a phone screen.</li>
<li>Leave plenty of space around the text so it does not crowd the image.</li>
<li>Use high contrast: dark text on a light background, or light text on a dark background.</li>
<li>Keep the phone number in the largest font after the headline. You want people to notice it immediately.</li>
<li>Check spelling carefully. A flyer with a spelling mistake looks unprofessional.</li>
</ul>

<h2>Legal and Ethical Limits</h2>
<p>AI-generated images exist in a grey area when it comes to ownership. In most cases, the tool's terms of service say you can use the images for personal or commercial purposes, but you should read the rules for each tool. Some important guidelines:</p>
<ul>
<li>Do not use AI to create fake images of real people without their permission. This includes politicians, celebrities, and private individuals.</li>
<li>Do not generate images that mislead customers about your product. If you sell small tomatoes, do not create a picture of giant tomatoes.</li>
<li>Be careful with images of people. AI sometimes produces strange hands, extra fingers, or distorted faces. Always review images before using them.</li>
<li>If an AI image includes a logo or brand name by accident, remove it to avoid trademark issues.</li>
</ul>

<h2>Worked Example: Making a Community Event Flyer</h2>
<p>Mr Tembo is organising a free computer literacy workshop at Edutrack College. He needs a flyer to share on WhatsApp and print for the community notice board. He follows these steps:</p>
<ol>
<li>He opens Canva on his phone and searches for "Event flyer" templates. He chooses a bright, clean design.</li>
<li>He goes to Microsoft Copilot Image Creator and types: "A friendly illustration of African adults and teenagers using desktop computers in a bright classroom. Zambian flag colours in the background. Cheerful, educational mood. No text."</li>
<li>He downloads the image and uploads it to his Canva template.</li>
<li>He adds the headline: "FREE Computer Skills Workshop."</li>
<li>He adds the details: "Saturday, 21 June 2025, 9 a.m. to 1 p.m. Edutrack College, Kalomo."</li>
<li>He adds contact information: "WhatsApp +260 97X XXX XXX to register."</li>
<li>He reviews the flyer, checks spelling, and exports it as a PNG for WhatsApp and a PDF for printing.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Sign up for a free Canva account at canva.com on your phone or computer.</li>
<li>Search for "flyer" templates and choose one you like. Replace the placeholder text with details for a real or imaginary event or business.</li>
<li>Use a free AI image generator such as Microsoft Copilot Image Creator to generate one picture for your flyer. Download it and add it to your Canva design.</li>
<li>Export your flyer as a JPEG and share it with a friend for feedback. Ask them if the phone number is easy to read and if the message is clear.</li>
<li>Write a short reflection: list one advantage and one risk of using AI-generated images for business.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>AI image generator</strong> — a tool that creates new pictures based on written descriptions using patterns learned from existing images.</li>
<li><strong>Flyer</strong> — a single-page advertisement designed to promote an event, product, or service.</li>
<li><strong>Template</strong> — a pre-designed layout that you can customise with your own text and images.</li>
<li><strong>Copyright</strong> — the legal right that protects original creative work from being copied without permission.</li>
<li><strong>Trademark</strong> — a symbol, word, or design legally registered to represent a company or product.</li>
</ul>

<h2>Summary</h2>
<p>AI image generators let you create custom visuals for your business without hiring a designer or buying expensive software. By writing detailed prompts, you can generate images for flyers, social media posts, and advertisements. Combine these images with text added through free tools like Canva to produce professional-looking materials. Always check images for errors, respect legal and ethical boundaries, and verify that the tool you use allows commercial use. With practice, you can create eye-catching marketing materials in under an hour.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool/">Canva Design School — Free Design Courses</a></li>
<li><a href="https://www.microsoft.com/en-us/ai">Microsoft AI — Image Creator and Copilot</a></li>
<li><a href="https://www.w3schools.com/ai/">W3Schools — AI Tutorial</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module3Lessons(): array
    {
        return [
            [
                'title' => '3.1 AI Risks: Scams, Deepfakes, and Wrong Answers',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe three major risks of using AI, recognise the signs of AI-generated scams and deepfakes, explain why AI sometimes gives wrong answers, and apply simple verification steps before trusting AI-generated information.</p>

<h2>AI-Powered Scams</h2>
<p>Scammers in Zambia and around the world are already using AI to make their crimes more convincing. AI allows criminals to operate faster, cheaper, and at a larger scale than ever before. Here are the most dangerous AI scams you should know about:</p>
<ul>
<li><strong>Voice cloning scams:</strong> A scammer records a few seconds of someone's voice from a video or phone call, perhaps from a WhatsApp status or Facebook post. They use AI to clone that voice and then call the person's relatives, pretending to be in trouble. "Mama, I have been arrested in Lusaka. Please send K2,000 via MoMo immediately." The voice sounds exactly like the real person. Many parents have fallen for this.</li>
<li><strong>AI-generated phishing messages:</strong> Scammers use AI to write flawless, personalised emails and SMS messages in perfect English. These messages no longer have the spelling mistakes that once made scams easy to spot. They may pretend to be from your bank, ZRA, or a mobile money provider.</li>
<li><strong>Fake customer service bots:</strong> Criminals create fake chatbots that look like real bank support. When you message them with a complaint, they ask for your PIN, OTP, or password. No real bank will ever ask for these.</li>
</ul>
<p>The best defence is scepticism. If someone calls or messages you asking for money or personal details, hang up or ignore the message. Call the person back on a number you know, or visit the bank in person. Never send money based on a voice call alone, even if it sounds exactly like your son or daughter.</p>

<h2>Deepfakes</h2>
<p>A <strong>deepfake</strong> is a fake video or audio recording created by AI. The technology can make it appear that a real person said or did something they never did. In 2024 and 2025, deepfake videos of politicians, celebrities, and business leaders spread across social media in Africa and beyond. Some were obvious fakes; others were disturbingly realistic.</p>
<p>Deepfakes are dangerous because they destroy trust. If a fake video shows a respected community leader making hateful statements, people may believe it. If a fake audio clip appears to capture a business deal that never happened, reputations are ruined. In Zambia, where WhatsApp is a primary news source for many people, deepfakes can spread rapidly before anyone checks their authenticity.</p>
<p>Signs of a deepfake include:</p>
<ul>
<li>Unnatural blinking or lack of blinking</li>
<li>Strange shadows or lighting that does not match the background</li>
<li>Audio that does not perfectly sync with lip movements</li>
<li>Unusual facial movements, especially around the eyes and mouth</li>
<li>A video that appears on social media but is not reported by any trusted news source</li>
</ul>
<p>If you see a shocking video of a well-known person, pause before sharing. Search for the same story on reputable news websites. If no legitimate source is reporting it, the video is probably fake.</p>

<h2>When AI Is Confidently Wrong</h2>
<p>One of the most important things to understand about ChatGPT, Gemini, and similar tools is that they can be <strong>confidently wrong</strong>. When the AI does not know the answer, it does not say "I do not know." Instead, it invents something that sounds plausible. This is called a <strong>hallucination</strong>.</p>
<p>For example, if you ask ChatGPT, "Who is the current Minister of Education in Zambia?" and its training data is old, it might give you the name of a former minister. The answer will be stated with complete confidence, as if it were fact. If you use this answer in an important letter, you will look foolish or dishonest.</p>
<p>AI also struggles with:</p>
<ul>
<li>Very recent events, because its training data has a cut-off date</li>
<li>Local details such as current market prices, bus fares, or local regulations</li>
<li>Complex maths involving multiple steps, where small errors compound</li>
<li>Medical or legal advice, where wrong information can cause serious harm</li>
</ul>

<h2>How to Verify AI Information</h2>
<p>Before you trust any AI-generated fact, follow these steps:</p>
<ol>
<li><strong>Check the source.</strong> If the AI mentions a law, a person, or an organisation, search for the official website and verify it.</li>
<li><strong>Cross-reference.</strong> Look for the same information on at least two trusted websites. If only the AI says it, be suspicious.</li>
<li><strong>Check the date.</strong> Is the information current? Laws, prices, and officials change.</li>
<li><strong>Ask a human expert.</strong> For medical, legal, or financial matters, consult a qualified professional rather than relying on AI.</li>
<li><strong>Use common sense.</strong> If an AI answer seems too good to be true, it probably is.</li>
</ol>

<h2>Worked Example: Spotting a Fake Video</h2>
<p>Mrs Banda receives a WhatsApp video showing the President of Zambia announcing a new cash grant for all citizens. The video looks realistic, but she is careful:</p>
<ol>
<li>She notices the President's mouth movements seem slightly delayed compared to the audio.</li>
<li>She checks ZNBC and the official government Facebook page. Neither mentions any new grant.</li>
<li>She searches Google for "Zambia cash grant 2025." Only a few social media posts mention it, and none are from official sources.</li>
<li>She asks her son, who works in civil service. He confirms there is no such grant.</li>
<li>She deletes the video and warns her church group not to share it.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Ask ChatGPT or Gemini: "Who is the current Deputy Minister of Technology and Science in Zambia?" Then search the official government website to verify the answer. Note whether the AI was correct.</li>
<li>Search online for "how to spot a deepfake video" and write down three warning signs in your own words.</li>
<li>Imagine a friend receives a voice call from someone who sounds exactly like their brother, asking for K1,000 via MoMo because of an emergency. Write a short script explaining to your friend why they should not send money and what they should do instead.</li>
<li>Ask an AI chatbot a maths question with several steps, such as calculating compound interest on K5,000 at 15 percent per year for three years. Solve the same problem with a calculator. Compare the answers.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Deepfake</strong> — a fake video or audio recording created by AI that makes it appear a real person said or did something they did not.</li>
<li><strong>Voice cloning</strong> — using AI to copy someone's voice so accurately that it sounds identical to the real person.</li>
<li><strong>Hallucination</strong> — when an AI generates false information that sounds convincing because it is designed to produce plausible-sounding answers.</li>
<li><strong>Phishing</strong> — a scam where criminals send fake messages to steal passwords, PINs, or personal information.</li>
<li><strong>Verify</strong> — to check information against trusted sources to confirm it is true and accurate.</li>
</ul>

<h2>Summary</h2>
<p>AI brings incredible opportunities, but it also creates new dangers. Scammers use AI to clone voices and write convincing phishing messages. Deepfake videos can spread lies that damage reputations and destabilise communities. AI chatbots sometimes give wrong answers with complete confidence. Protecting yourself requires scepticism, verification, and a commitment to checking facts before you act or share. Never send money based on a voice call alone, always cross-check shocking news against trusted sources, and treat every AI-generated fact as a starting point for your own research, not the final word.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.owasp.org/">OWASP — Online Security and Scam Awareness</a></li>
<li><a href="https://support.google.com/accounts/answer/6294825">Google Help — Avoid Phishing and Scams</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.2 Protecting Your Data and Using AI Responsibly',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what personal data AI services collect, identify which information should never be shared with AI tools, describe responsible use principles for AI, and decide when it is appropriate to use AI and when it is better to rely on human expertise.</p>

<h2>What Data Do AI Services Collect?</h2>
<p>When you use ChatGPT, Gemini, or any AI tool, the company behind it records your conversations. This data may be used to improve the AI, fix bugs, or train future versions. In some cases, human reviewers read anonymised conversations to check the AI's performance. This means that anything you type into an AI chatbot could potentially be seen by someone else.</p>
<p>The data collected typically includes:</p>
<ul>
<li>The exact words you type, known as <strong>prompts</strong></li>
<li>The AI's responses to you</li>
<li>Your account details, such as your email address</li>
<li>Your device type, browser, and approximate location</li>
<li>How long you spend on the service and which features you use</li>
</ul>
<p>Most reputable companies say they do not sell your personal data to advertisers, but their privacy policies can change. It is your responsibility to read the terms and understand what you are agreeing to. If a service is free, remember the old saying: "If you are not paying for the product, you are the product." Your data has value, and companies collect it for a reason.</p>

<h2>Never Share These with AI</h2>
<p>There are certain types of information you should never type into any AI chatbot, no matter how convenient it seems:</p>
<ul>
<li><strong>Your National Registration Card (NRC) number.</strong> This is a key piece of identity data. If it leaks, criminals can use it for fraud.</li>
<li><strong>Bank account numbers, debit card details, or mobile money PINs.</strong> No legitimate service needs these. If an AI asks for them, it is either a scam or a serious security flaw.</li>
<li><strong>Passwords.</strong> Never share passwords, even if the AI claims it needs them to "help you."</li>
<li><strong>Medical records or HIV status.</strong> Health information is deeply private. AI services are not bound by medical confidentiality.</li>
<li><strong>Details of illegal activities.</strong> Even if you are just curious, sharing this creates a permanent record.</li>
<li><strong>Other people's private information.</strong> Do not paste someone else's emails, messages, or personal details without their permission.</li>
</ul>

<h2>Responsible Use Principles</h2>
<p>Using AI responsibly means thinking about the impact of your actions on yourself, other people, and society. Here are five principles to guide you:</p>
<ol>
<li><strong>Be honest about AI use.</strong> If you use AI to write an essay, a report, or a business proposal, say so. Passing off AI-generated work as entirely your own is dishonest. In academic and professional settings, this can get you expelled or fired.</li>
<li><strong>Do not use AI to cheat or deceive.</strong> Using AI to create fake reviews, impersonate someone, or generate fraudulent documents is wrong and often illegal.</li>
<li><strong>Respect copyright and creativity.</strong> AI learns from existing books, articles, and images. When you use AI-generated content for business, make sure you understand who owns it and whether you have the right to use it commercially.</li>
<li><strong>Check for bias.</strong> AI systems can reflect the biases of their training data. If you ask an AI about gender roles, business leadership, or cultural practices, the answer may reflect stereotypes from the countries where most of the training data originated. Always think critically.</li>
<li><strong>Consider the environment.</strong> Training and running large AI systems requires enormous amounts of electricity and water for cooling data centres. Use AI when it genuinely helps you, but do not waste resources on trivial requests.</li>
</ol>

<h2>When to Use AI and When to Ask a Human</h2>
<p>AI is excellent for brainstorming, drafting, calculating, and explaining general concepts. It is not suitable for situations that require empathy, legal responsibility, or expert judgment. Use this guide:</p>
<table>
<tr><th>Use AI for</th><th>Ask a human for</th></tr>
<tr><td>Drafting emails and letters</td><td>Legal contracts and court documents</td></tr>
<tr><td>Explaining study topics</td><td>Medical diagnosis and treatment</td></tr>
<tr><td>Creating social media posts</td><td>Mental health counselling</td></tr>
<tr><td>Basic maths and budgeting</td><td>Complex tax planning</td></tr>
<tr><td>Language translation</td><td>Sensitive personal advice</td></tr>
<tr><td>Generating ideas</td><td>Final decisions that affect people's lives</td></tr>
</table>

<h2>Worked Example: Deciding What to Share</h2>
<p>Mr Mutale wants help writing a business plan for his mobile money agency. He opens ChatGPT and considers what to include in his prompt:</p>
<ol>
<li>He wants to include his monthly revenue to get accurate advice. <strong>Decision:</strong> He rounds the figure to the nearest thousand instead of giving the exact amount. This gives the AI enough context without exposing precise financial data.</li>
<li>The AI asks for his TPIN to "verify his business status." <strong>Decision:</strong> He closes the chat immediately. No AI needs a TPIN. This is a red flag.</li>
<li>He wants to mention that he employs two people. <strong>Decision:</strong> He shares this general fact but does not include their names, phone numbers, or NRC numbers.</li>
<li>He needs advice on mobile money regulations. <strong>Decision:</strong> He uses the AI for a general explanation, then visits the Bank of Zambia website to confirm the actual rules.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Read the privacy policy of ChatGPT or Gemini. Write down three types of data the service collects. Note how long they say they keep your conversations.</li>
<li>Create a personal "red list" of information you will never share with AI. Include at least five items specific to your life in Zambia.</li>
<li>Think of a situation where you used or might use AI. Write two sentences explaining why it is appropriate in that situation, and two sentences describing a related situation where a human expert would be better.</li>
<li>Ask an AI chatbot: "What are the risks of sharing my NRC number online?" Review the answer. Does it mention all the risks you learned in this lesson? Add any missing points.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Privacy policy</strong> — a legal document explaining how a company collects, uses, and protects your personal data.</li>
<li><strong>Personal data</strong> — any information that can identify you, such as your name, NRC number, phone number, or address.</li>
<li><strong>Confidentiality</strong> — the obligation to keep private information secret and not share it without permission.</li>
<li><strong>Responsible use</strong> — using technology in ways that are honest, respectful, safe, and considerate of others.</li>
<li><strong>Bias</strong> — a tendency to favour or disfavour certain groups, ideas, or outcomes, often reflected in AI systems through their training data.</li>
</ul>

<h2>Summary</h2>
<p>AI services collect and store your conversations, so you must be careful about what you share. Never give an AI chatbot your NRC number, banking details, passwords, medical records, or other people's private information. Responsible AI use means being honest about when you use AI, avoiding deception, respecting copyright, thinking critically about bias, and knowing when to seek human expertise. AI is a powerful assistant, but you are the one who must make ethical decisions and protect your own privacy. Treat your personal data like cash: once you give it away, you may never get it back.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/accounts/answer/6294825">Google Help — Protect Your Account</a></li>
<li><a href="https://www.owasp.org/">OWASP — Web Security and Privacy Resources</a></li>
<li><a href="https://learndigital.withgoogle.com/digitalgarage">Google Digital Garage — Online Safety Courses</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.3 AI and the Future of Work in Zambia',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe how AI might change certain jobs in Zambia, explain why many jobs still need human skills, identify opportunities that AI creates for entrepreneurs and workers, and create a personal plan for staying relevant in an economy where AI is becoming more common.</p>

<h2>Which Jobs Might Change?</h2>
<p>AI is already changing work around the world, and Zambia will not be exempt. Some jobs are at higher risk of being automated or heavily assisted by AI, while others remain firmly in human hands. Understanding the difference helps you prepare for the future.</p>
<p>Jobs that may see significant change include:</p>
<ul>
<li><strong>Data entry and basic bookkeeping:</strong> AI can read receipts, categorise expenses, and generate reports faster than a human. Small businesses that currently hire bookkeepers may switch to AI-powered accounting apps.</li>
<li><strong>Customer service via text:</strong> Many companies now use AI chatbots to answer common questions. A chatbot can handle "What are your opening hours?" or "How much is delivery?" without human involvement.</li>
<li><strong>Basic translation:</strong> AI translation tools are improving rapidly. Simple translation tasks may be handled by software rather than human translators.</li>
<li><strong>Content writing for simple articles:</strong> News organisations and marketing teams may use AI to draft routine reports, product descriptions, and social media updates.</li>
<li><strong>Quality inspection in factories:</strong> AI-powered cameras can spot defects in products faster and more consistently than human eyes.</li>
</ul>
<p>However, change does not always mean elimination. In many cases, AI will assist workers rather than replace them. A bookkeeper might use AI to handle data entry but still review the final reports and advise the business owner. A customer service agent might handle only complex complaints while AI manages simple queries.</p>

<h2>Jobs That Still Need Humans</h2>
<p>There are many skills that AI cannot replicate, no matter how advanced it becomes. These skills will remain valuable for decades:</p>
<ul>
<li><strong>Empathy and emotional intelligence:</strong> A nurse comforting a worried patient, a teacher encouraging a struggling child, or a counsellor helping someone through grief cannot be replaced by AI. Machines do not feel.</li>
<li><strong>Physical dexterity in unpredictable environments:</strong> A mechanic fixing a bus on a muddy roadside, a farmer adapting to erratic rainfall, or a tailor fitting a garment to an unusual body shape needs human hands and judgment.</li>
<li><strong>Creativity and cultural understanding:</strong> AI can generate images and text, but it does not understand Zambian culture, humour, or community values. A storyteller, a traditional musician, or a cultural event organiser brings something AI cannot.</li>
<li><strong>Complex problem-solving with limited resources:</strong> In Zambia, many problems require ingenuity. How do you keep a computer lab running during load-shedding? How do you transport fresh produce to market with a broken-down truck? These situations need human creativity.</li>
<li><strong>Trust and relationship-building:</strong> People buy from people they trust. A market vendor who remembers your name, a bank officer who knows your family history, or a teacher who believes in you builds loyalty that no AI can match.</li>
</ul>

<h2>New Opportunities AI Creates</h2>
<p>AI does not only take jobs; it also creates them. Here are opportunities for Zambians who learn to use AI well:</p>
<ul>
<li><strong>AI-assisted freelancers:</strong> Writers, designers, and virtual assistants who use AI to work faster can take on more clients and earn more money.</li>
<li><strong>AI trainers and data labellers:</strong> AI systems need humans to label photos, transcribe audio, and check outputs. Companies in Zambia and abroad hire people for this work.</li>
<li><strong>Digital marketing specialists:</strong> Businesses need people who understand both traditional marketing and AI tools for targeting ads, analysing data, and creating content.</li>
<li><strong>Tech support and repair:</strong> As more businesses adopt AI tools, they need people who can install, troubleshoot, and maintain the technology.</li>
<li><strong>Entrepreneurs using AI:</strong> A young Zambian could start a business offering AI-generated logos, AI-assisted translation, or AI-powered farm advice to local communities.</li>
</ul>

<h2>Preparing Yourself for an AI World</h2>
<p>The best way to stay employable is to combine human skills with digital skills. Here is a practical plan:</p>
<ol>
<li><strong>Learn one digital skill deeply.</strong> Whether it is spreadsheets, graphic design, social media management, or AI prompting, become genuinely good at something.</li>
<li><strong>Practise communication.</strong> Write clearly, speak confidently, and listen carefully. These skills are rare and valuable.</li>
<li><strong>Build a network.</strong> Join professional groups, attend community events, and stay in touch with classmates. Opportunities often come through people you know.</li>
<li><strong>Stay curious.</strong> Technology changes fast. Commit to learning something new every month, even if it is just a short online tutorial.</li>
<li><strong>Develop problem-solving skills.</strong> Volunteer for challenging tasks, start a small side project, or help a community organisation. Real experience teaches you what classrooms cannot.</li>
</ol>

<h2>Worked Example: A Career Plan with AI</h2>
<p>Grace is 22 years old and lives in Kalomo. She has just completed her Certificate in Artificial Intelligence. She wants to work in digital marketing. Her six-month plan looks like this:</p>
<table>
<tr><th>Month</th><th>Goal</th><th>Action</th></tr>
<tr><td>1</td><td>Build a portfolio</td><td>Create five sample social media campaigns for imaginary businesses using Canva and AI-generated text</td></tr>
<tr><td>2</td><td>Gain real experience</td><td>Offer to manage social media for a local shop or church for free</td></tr>
<tr><td>3</td><td>Learn analytics</td><td>Complete a free Google Analytics course online</td></tr>
<tr><td>4</td><td>Network</td><td>Attend a business expo in Livingstone and collect contacts</td></tr>
<tr><td>5</td><td>Apply for jobs</td><td>Send applications to five companies in Lusaka with her portfolio</td></tr>
<tr><td>6</td><td>Start freelancing</td><td>Register on a freelance website and offer AI-assisted content writing</td></tr>
</table>

<h2>Try It Yourself</h2>
<ol>
<li>Write down your current job or the job you hope to have. List two ways AI might help you in that role and two ways it might never replace you.</li>
<li>Research one new job that did not exist ten years ago, such as social media manager or data analyst. Write three sentences about what the job involves and why it matters in Zambia.</li>
<li>Ask ChatGPT or Gemini: "What skills will be most valuable in Zambia over the next ten years?" Review the answer critically. Does it understand the Zambian context? Add two skills you think the AI missed.</li>
<li>Create a simple six-month learning plan for yourself. Include one digital skill, one communication skill, and one practical project you will complete.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Automation</strong> — using technology to perform tasks that were previously done by humans, often faster and cheaper.</li>
<li><strong>Freelancer</strong> — a self-employed person who offers services to multiple clients rather than working for a single employer.</li>
<li><strong>Digital literacy</strong> — the ability to use digital technology confidently to find, create, and communicate information.</li>
<li><strong>Entrepreneur</strong> — a person who starts and runs their own business, taking risks in the hope of making a profit.</li>
<li><strong>Portfolio</strong> — a collection of your best work that demonstrates your skills to potential employers or clients.</li>
</ul>

<h2>Summary</h2>
<p>AI will change the world of work in Zambia, but it will not eliminate the need for human skills. Jobs involving empathy, physical adaptability, cultural understanding, and relationship-building will remain essential. At the same time, AI creates new opportunities for freelancers, marketers, technicians, and entrepreneurs who learn to use it well. The best preparation is to develop a combination of digital and human skills, stay curious, build a network, and approach the future with confidence rather than fear. AI is a tool. The person who wields it wisely will always be more valuable than the tool itself.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/">Grow with Google — Free Career and Business Training</a></li>
<li><a href="https://learndigital.withgoogle.com/digitalgarage">Google Digital Garage — Digital Skills Courses</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: Understanding AI in Everyday Zambia',
            'description' => 'Test your knowledge of what AI is, AI you already use, and how to talk to ChatGPT and Gemini effectively.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following best describes artificial intelligence?',
                    'explanation' => 'AI is software that learns patterns from data and uses them to make predictions or decisions, not a conscious being or physical robot.',
                    'options' => [
                        ['text' => 'A conscious robot that thinks like a human', 'is_correct' => false],
                        ['text' => 'Software that learns patterns from data to make predictions', 'is_correct' => true],
                        ['text' => 'A supercomputer that knows everything', 'is_correct' => false],
                        ['text' => 'A type of internet connection', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of these is an example of AI you already use on your smartphone?',
                    'explanation' => 'Predictive text suggests the next word as you type by learning patterns from billions of messages.',
                    'options' => [
                        ['text' => 'The calculator app', 'is_correct' => false],
                        ['text' => 'Predictive text in WhatsApp', 'is_correct' => true],
                        ['text' => 'The phone\'s torch', 'is_correct' => false],
                        ['text' => 'The alarm clock', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'When MTN MoMo flags an unusual transaction and sends you a warning SMS, what technology is most likely involved?',
                    'explanation' => 'Mobile money providers use AI fraud detection systems to spot unusual spending patterns and protect customers.',
                    'options' => [
                        ['text' => 'A human employee watching every transaction', 'is_correct' => false],
                        ['text' => 'AI fraud detection', 'is_correct' => true],
                        ['text' => 'Random guessing', 'is_correct' => false],
                        ['text' => 'GPS tracking', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What makes a prompt to ChatGPT or Gemini more effective?',
                    'explanation' => 'Specific prompts that include context, format, and limits produce much more useful and accurate answers.',
                    'options' => [
                        ['text' => 'Making it as short as possible', 'is_correct' => false],
                        ['text' => 'Using only one word', 'is_correct' => false],
                        ['text' => 'Being specific about the task, context, and format', 'is_correct' => true],
                        ['text' => 'Writing it in all capital letters', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'AI systems can feel emotions such as happiness and sadness.',
                    'explanation' => 'AI has no feelings or consciousness. It processes patterns but does not experience emotions.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'When an AI chatbot says "I am happy to help," it is simply repeating a pattern it learned and does not actually feel happiness.',
                    'explanation' => 'AI chatbots use learned language patterns. They do not have subjective experiences or emotions.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the name of the AI chatbot created by OpenAI? (one word)',
                    'explanation' => 'ChatGPT is the large language model chatbot developed by OpenAI.',
                    'correct_answer' => 'ChatGPT',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following should you NEVER share with an AI chatbot?',
                    'explanation' => 'You should never share banking details, PINs, passwords, or NRC numbers with AI chatbots because conversations may be stored and reviewed.',
                    'options' => [
                        ['text' => 'A recipe for nshima', 'is_correct' => false],
                        ['text' => 'Your favourite colour', 'is_correct' => false],
                        ['text' => 'Your mobile money PIN', 'is_correct' => true],
                        ['text' => 'A shopping list', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is "iterative prompting"?',
                    'explanation' => 'Iterative prompting means refining your instructions through back-and-forth conversation with an AI to improve the result.',
                    'options' => [
                        ['text' => 'Sending the same prompt one hundred times', 'is_correct' => false],
                        ['text' => 'Refining your instructions through back-and-forth conversation', 'is_correct' => true],
                        ['text' => 'Using only one prompt forever', 'is_correct' => false],
                        ['text' => 'Prompting in a foreign language', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: AI for Work and Small Business',
            'description' => 'Test your knowledge of using AI to write letters and CVs, create business content, and design flyers and images.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'When using AI to help write a CV, what is the most important thing you must do afterwards?',
                    'explanation' => 'AI may invent qualifications, dates, or skills. You must check every detail and ensure it matches your real experience.',
                    'options' => [
                        ['text' => 'Print it immediately and send it', 'is_correct' => false],
                        ['text' => 'Check every detail and remove any invented information', 'is_correct' => true],
                        ['text' => 'Add a fancy border', 'is_correct' => false],
                        ['text' => 'Make it at least five pages long', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is the best prompt for getting a useful business letter from AI?',
                    'explanation' => 'Specific prompts with context, names, dates, and desired outcomes produce the most useful drafts.',
                    'options' => [
                        ['text' => 'Write me a letter', 'is_correct' => false],
                        ['text' => 'Write a formal complaint letter to Zambezi Suppliers about five damaged bags of maize seed, requesting replacement within 14 days', 'is_correct' => true],
                        ['text' => 'Letter about business', 'is_correct' => false],
                        ['text' => 'Help with a problem', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'When writing a product description with AI, why is it important to check the output carefully?',
                    'explanation' => 'AI may add false claims such as "organic certified" or incorrect prices that could mislead customers and cause legal problems.',
                    'options' => [
                        ['text' => 'To make it longer', 'is_correct' => false],
                        ['text' => 'To remove false claims the AI may have invented', 'is_correct' => true],
                        ['text' => 'To add more emojis', 'is_correct' => false],
                        ['text' => 'To translate it into French', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'You should always trust AI-generated maths calculations without checking them yourself.',
                    'explanation' => 'AI can make arithmetic errors, especially with multi-step calculations. Always verify financial figures with a calculator.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A call to action in a social media post tells the reader what to do next, such as "Order now" or "Message us today."',
                    'explanation' => 'A call to action is a clear instruction that guides the reader toward the next step you want them to take.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the name of the popular free design tool that lets you create flyers and add text to images? (one word)',
                    'explanation' => 'Canva is a widely used free design platform for creating social media graphics, flyers, and presentations.',
                    'correct_answer' => 'Canva',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a risk of using AI-generated images for business?',
                    'explanation' => 'AI-generated images may contain distorted faces, extra fingers, or accidental logos that look unprofessional or create trademark issues.',
                    'options' => [
                        ['text' => 'They are always perfect', 'is_correct' => false],
                        ['text' => 'They may contain errors like distorted faces or accidental logos', 'is_correct' => true],
                        ['text' => 'They cost too much money', 'is_correct' => false],
                        ['text' => 'They cannot be saved', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'If your normal price is K90 and your cost is K60, what is your profit margin?',
                    'explanation' => 'Profit margin is calculated as (profit / selling price) × 100. Here, profit is K30, so (30 / 90) × 100 = 33.3 percent.',
                    'options' => [
                        ['text' => '50 percent', 'is_correct' => false],
                        ['text' => '33.3 percent', 'is_correct' => true],
                        ['text' => '25 percent', 'is_correct' => false],
                        ['text' => '66.6 percent', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should you do if an AI image generator creates a picture that accidentally includes a well-known brand logo?',
                    'explanation' => 'Using someone else\'s trademark without permission can cause legal problems. Remove accidental logos before using the image.',
                    'options' => [
                        ['text' => 'Leave it in and sell the image', 'is_correct' => false],
                        ['text' => 'Remove the logo to avoid trademark issues', 'is_correct' => true],
                        ['text' => 'Claim the logo as your own', 'is_correct' => false],
                        ['text' => 'Ignore it because AI images have no rules', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Staying Safe and Looking Ahead',
            'description' => 'Test your knowledge of AI risks, data protection, responsible use, and the future of work in Zambia.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is a deepfake?',
                    'explanation' => 'A deepfake is a fake video or audio created by AI that makes it appear a real person said or did something they never did.',
                    'options' => [
                        ['text' => 'A very deep swimming pool', 'is_correct' => false],
                        ['text' => 'A fake video or audio created by AI', 'is_correct' => true],
                        ['text' => 'A type of computer virus', 'is_correct' => false],
                        ['text' => 'A secure password', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In a voice cloning scam, what do criminals typically ask victims to do?',
                    'explanation' => 'Scammers clone a voice and call relatives claiming an emergency, then urgently request money via mobile money.',
                    'options' => [
                        ['text' => 'Send money urgently via MoMo or Airtel Money', 'is_correct' => true],
                        ['text' => 'Download a free game', 'is_correct' => false],
                        ['text' => 'Visit a website for a prize', 'is_correct' => false],
                        ['text' => 'Change their phone wallpaper', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is an AI hallucination?',
                    'explanation' => 'An AI hallucination is when the AI generates false information that sounds convincing because it predicts plausible-sounding words rather than verified facts.',
                    'options' => [
                        ['text' => 'When the AI sees imaginary pictures', 'is_correct' => false],
                        ['text' => 'When the AI generates false but convincing information', 'is_correct' => true],
                        ['text' => 'When the AI crashes', 'is_correct' => false],
                        ['text' => 'When the AI learns too fast', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'It is safe to share your mobile money PIN with an AI chatbot if it promises to help you fix your account.',
                    'explanation' => 'No legitimate AI or company employee will ever ask for your PIN. This is always a scam or serious security risk.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'AI systems can reflect biases from the data they were trained on.',
                    'explanation' => 'AI learns from existing data, which may contain cultural, gender, or racial biases. Critical thinking is essential when interpreting AI outputs.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for a fake video created by AI that makes it look like someone said something they did not? (one word)',
                    'explanation' => 'Deepfake is the term for AI-generated fake video or audio that impersonates real people.',
                    'correct_answer' => 'Deepfake',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following jobs is LEAST likely to be replaced by AI?',
                    'explanation' => 'Nursing requires empathy, physical care, and emotional intelligence that AI cannot replicate.',
                    'options' => [
                        ['text' => 'Data entry clerk', 'is_correct' => false],
                        ['text' => 'Basic translation', 'is_correct' => false],
                        ['text' => 'Nurse', 'is_correct' => true],
                        ['text' => 'Simple content writing', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the best defence against an AI-powered scam?',
                    'explanation' => 'Scepticism and verification are the best defences. Always call back on a known number and never send money based on an unexpected call.',
                    'options' => [
                        ['text' => 'Send money quickly before the offer expires', 'is_correct' => false],
                        ['text' => 'Scepticism, verification, and calling back on known numbers', 'is_correct' => true],
                        ['text' => 'Share the message with all your contacts', 'is_correct' => false],
                        ['text' => 'Reply to every suspicious message to investigate', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of these is an example of using AI responsibly?',
                    'explanation' => 'Responsible use includes being honest about AI assistance, verifying facts, and respecting privacy.',
                    'options' => [
                        ['text' => 'Using AI to create fake reviews for your business', 'is_correct' => false],
                        ['text' => 'Claiming AI-generated work as entirely your own without mentioning it', 'is_correct' => false],
                        ['text' => 'Using AI to draft a letter and then editing it to add your own details', 'is_correct' => true],
                        ['text' => 'Sharing your friend\'s NRC number with AI to help them fill a form', 'is_correct' => false],
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
            'title' => 'Write a Professional Letter Using AI',
            'description' => 'Use ChatGPT or Gemini to draft a professional letter, then edit it to make it personal, accurate, and ready to send.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Choose ONE of the following scenarios:
(a) Write a job application letter for a shop assistant position at a supermarket in your nearest town.
(b) Write a formal complaint letter to a supplier who delivered damaged goods.
(c) Write a request letter to your local council asking for permission to operate a market stall.

Step 2: Open ChatGPT or Gemini in your browser. Write a detailed prompt that includes: your role, the recipient's details, the purpose of the letter, any specific facts (dates, amounts, names), and the tone you want.

Step 3: Generate the draft. Copy it into a word processor such as Google Docs or LibreOffice Writer.

Step 4: Edit the draft carefully. Add at least two personal details the AI could not have known. Remove any invented facts. Check spelling and grammar.

Step 5: Write a short reflection of at least 100 words at the end of your document. Explain: what prompt you used, what the AI did well, what you had to change, and what you learned about using AI for writing.

Step 6: Save your document as a PDF named "AI_Letter_Assignment.pdf" and upload it here.
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
            'title' => 'AI-Powered Small Business Project',
            'description' => 'Create a simple business marketing piece using AI tools, including a product description, a social media post, and a flyer.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Choose a small business you know or imagine one. Examples: a chicken-rearing business, a vegetable stall, a tailoring service, a mobile money agency, or a second-hand clothes shop.

Step 2: Use ChatGPT or Gemini to write a product description for one item or service your business offers. The description must be at least 80 words and include price in Zambian Kwacha.

Step 3: Use the same AI tool to draft a social media post promoting your business. The post must be under 100 words and include a clear call to action and a phone number.

Step 4: Use Canva (canva.com) to create a simple flyer for your business. Include a headline, the product description from Step 2, the social media post text from Step 3, and at least one image. You may use an AI-generated image or a free stock photo. The flyer must fit on one A4 page.

Step 5: Export the flyer as a PDF or JPEG.

Step 6: Write a reflection of at least 100 words explaining: which AI tools you used, what worked well, what challenges you faced, and one thing you would do differently next time.

Step 7: Submit both the flyer file and your reflection document as a single ZIP file named "AI_Business_Project.zip".
INSTRUCTIONS,
            'max_points' => 100,
            'passing_points' => 50,
            'due_date' => null,
            'allow_late_submission' => 1,
            'allowed_file_types' => 'pdf,jpg,png,zip',
            'max_file_size_mb' => 10,
        ]);
    }

    private function printSummary(): void
    {
        $this->command->info('Certificate in Artificial Intelligence content seeded successfully.');
        $this->command->info('Modules: 3 | Lessons: 9 | Quizzes: 3 | Assignments: 2');
    }
}
