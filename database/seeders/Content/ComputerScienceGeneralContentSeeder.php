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

class ComputerScienceGeneralContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Computer Science General')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Computer Science General" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in Computer Science General already has modules. Skipping content seed.');
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
                'title' => 'Module 1: How Computers Represent Data',
                'description' => 'Learn how computers store numbers, text, images, and sound using binary, and understand why this matters for memory, storage, and speed.',
            ],
            [
                'title' => 'Module 2: Algorithms and Problem Solving',
                'description' => 'Discover what algorithms are, how to express them with flowcharts and pseudocode, and how to break big problems into smaller pieces.',
            ],
            [
                'title' => 'Module 3: Introduction to Programming with Python',
                'description' => 'Write your first Python programs using variables, input and output, decisions, and loops to solve small real-life problems.',
            ],
            [
                'title' => 'Module 4: Data Structures Conceptually',
                'description' => 'Understand lists, queues, stacks, and records using everyday Zambian examples such as bank queues and market stock lists.',
            ],
            [
                'title' => 'Module 5: The Internet, Databases, and Software Development',
                'description' => 'Explore how the internet and databases work, and see how professional software is planned, built, and improved.',
            ],
            [
                'title' => 'Module 6: Computational Thinking for Zambia',
                'description' => 'Apply computational thinking to real Zambian challenges such as minibus routes, market stock, and load-shedding planning.',
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
                'title' => '1.1 Binary, Bits, and Bytes: Why Computers Use Only Two Digits',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain why computers use only ones and zeros, convert small decimal numbers into binary, and describe the difference between a bit and a byte. You will also understand why this matters when you buy a phone, save a document, or check your remaining data bundle.</p>

<h2>Why Do Computers Use Binary?</h2>
<p>A computer is made of millions of tiny electronic switches called transistors. Each switch can only be in one of two states: on or off, high voltage or low voltage. We represent these two states with the digits <strong>1</strong> (on) and <strong>0</strong> (off). This system is called <strong>binary</strong>, and it is the language that computers understand at their most basic level.</p>
<p>Imagine a light switch in your room in Kalomo. The bulb is either on or off; there is no halfway state. A computer works the same way. It does not understand English, Nyanja, or Bemba directly. It understands patterns of on and off signals. By grouping these signals, computers can represent numbers, letters, pictures, sounds, and even the videos you watch on your phone.</p>

<h2>Bits and Bytes</h2>
<p>A single binary digit, either 1 or 0, is called a <strong>bit</strong>. It is the smallest piece of information a computer can store. On its own, one bit cannot do much. It can only answer a simple yes-or-no question, such as "Is the door locked?" or "Is this transaction complete?"</p>
<p>When we group eight bits together, we get a <strong>byte</strong>. One byte can represent 256 different values, which is enough to store one character of text, such as the letter "A" or the number "7". For example, the word "Zambia" needs six bytes because it has six letters.</p>

<h2>Worked Example: Counting in Binary</h2>
<p>In everyday life we use decimal numbers, which have ten digits: 0 to 9. In binary we only have two digits: 0 and 1. Here is how to count from zero to five in both systems:</p>
<table>
<thead>
<tr><th>Decimal</th><th>Binary</th></tr>
</thead>
<tbody>
<tr><td>0</td><td>0</td></tr>
<tr><td>1</td><td>1</td></tr>
<tr><td>2</td><td>10</td></tr>
<tr><td>3</td><td>11</td></tr>
<tr><td>4</td><td>100</td></tr>
<tr><td>5</td><td>101</td></tr>
</tbody>
</table>
<p>To convert the decimal number 5 into binary, think about which powers of two add up to 5. Four plus one equals five. The place values for three binary digits are 4, 2, and 1. So we put a 1 in the 4 place, a 0 in the 2 place, and a 1 in the 1 place, giving <strong>101</strong>.</p>

<h2>Why Binary Matters in Zambia</h2>
<p>When you buy a smartphone in Lusaka or Kalomo, the seller may say it has "128 GB of storage" and "8 GB of RAM." GB stands for gigabyte, which means billions of bytes. Because everything is stored as binary, the actual usable space is often slightly less than the advertised number, but the principle is the same: more bytes means more space for apps, photos, music, and videos.</p>
<p>Your mobile money transaction also relies on binary. When you send K50 via Airtel Money or MTN MoMo, the network converts your request into binary signals, sends it through computers, and records the result. Without binary, none of this would work.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write down the decimal numbers 0 to 7 in one column.</li>
<li>Convert each number to binary using the place values 4, 2, and 1.</li>
<li>Check your answers by converting back to decimal.</li>
<li>On your phone, open the settings and look at your storage size. How many GB do you have in total, and how many are free?</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Binary:</strong> A number system that uses only two digits, 0 and 1, matching the on and off states of computer circuits.</li>
<li><strong>Bit:</strong> A single binary digit, the smallest unit of data in a computer.</li>
<li><strong>Byte:</strong> A group of eight bits, enough to represent one character of text.</li>
<li><strong>Transistor:</strong> A tiny electronic switch inside a computer processor.</li>
<li><strong>Decimal:</strong> The everyday number system with ten digits from 0 to 9.</li>
</ul>

<h2>Summary</h2>
<p>Computers use binary because their hardware is built from switches that are either on or off. A bit is one binary digit, and a byte is eight bits. By combining bytes, computers can represent numbers, text, images, sound, and transactions. Understanding binary helps you make sense of phone storage, data bundles, and the digital world around you.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/math/algebra-home/alg-intro-to-algebra/algebra-alternate-number-bases/v/number-systems-introduction">Khan Academy — Number Systems Introduction</a></li>
<li><a href="https://www.w3schools.com/python/python_operators.asp">W3Schools — Python Operators</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/get-started-computers/">Microsoft Learn — Get Started with Computers</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.2 Representing Numbers, Text, and Characters',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain how computers turn numbers and letters into binary patterns, describe the purpose of character encoding, and recognise why the same file may look different on different devices. You will also understand how special characters such as those in Zambian languages are stored.</p>

<h2>Numbers in Binary</h2>
<p>We already know that computers store everything as bits. For whole numbers, the computer simply uses binary place values. The number 13 in decimal becomes 1101 in binary because 8 + 4 + 0 + 1 = 13. For negative numbers, computers use special methods such as a "sign bit" or a system called two's complement, which you will meet in later studies. For now, the important idea is that every number has a binary pattern.</p>
<p>When you type your age, your NRC number, or the price of an item into a computer, the device converts those decimal digits into binary before storing or processing them. If you enter K75 for a bag of mealie meal, the computer stores the digits 7 and 5 as binary patterns and later converts them back to display K75 on the screen.</p>

<h2>Text and Character Encoding</h2>
<p>Computers do not understand letters directly. Instead, every letter, digit, and symbol is given a unique number, and that number is stored in binary. A system that maps characters to numbers is called a <strong>character encoding</strong>. The most common encoding today is <strong>UTF-8</strong>, which can represent letters from many languages, including accented letters and some African scripts.</p>
<p>For example, in UTF-8 the capital letter "A" is stored as the number 65, which is 01000001 in binary. The digit "0" is stored as 48, and a space is stored as 32. When you type an SMS to a friend in Kalomo, your phone converts every character into these binary codes, sends them across the network, and the friend's phone converts them back into readable text.</p>

<h2>ASCII and Unicode</h2>
<p>An older encoding called <strong>ASCII</strong> was widely used in early computers. It only had codes for English letters, digits, and a few symbols. This was a problem for languages such as French, Chinese, and many African languages that need extra characters. <strong>Unicode</strong> solved this by giving a unique code to almost every character used in the world. UTF-8 is the most popular way to store Unicode because it saves space for common characters.</p>
<p>If a website in Zambia displays strange boxes or question marks instead of letters, it is often because the browser is using the wrong character encoding. Choosing UTF-8 helps websites and apps display text correctly for users in many languages.</p>

<h2>Worked Example: Storing a Name</h2>
<p>Suppose you want to store the name "Ada" on a computer. Using ASCII or UTF-8, each letter gets a number:</p>
<ul>
<li>A = 65</li>
<li>d = 100</li>
<li>a = 97</li>
</ul>
<p>The computer stores these three numbers as binary bytes: 01000001, 01100100, 01100001. When the file is opened, the software reads the bytes, looks up the characters, and displays "Ada" on the screen. This happens so quickly that you do not notice the conversion.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Pick a three-letter word, such as your initials or the name of your home town.</li>
<li>Look up the ASCII decimal value of each letter using an online ASCII table.</li>
<li>Convert each decimal value to an 8-bit binary byte.</li>
<li>Write the complete sequence of bytes that represents your word.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Character encoding:</strong> A system that maps letters, digits, and symbols to unique numbers so computers can store text.</li>
<li><strong>UTF-8:</strong> A common character encoding that supports many languages and is used on most websites.</li>
<li><strong>ASCII:</strong> An older encoding for English letters, digits, and basic symbols.</li>
<li><strong>Unicode:</strong> A worldwide standard that gives a unique number to almost every written character.</li>
</ul>

<h2>Summary</h2>
<p>Computers store numbers as binary values and text as binary codes through character encoding. UTF-8 is the modern standard that supports English, local languages, and symbols. When you type, send messages, or read web pages, your device is constantly converting between human-readable characters and binary patterns.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/charsets/default.asp">W3Schools — HTML Character Sets</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Glossary/Unicode">MDN Web Docs — Unicode</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.3 Images, Sound, and Video as Digital Data',
                'duration_minutes' => 70,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain how computers store images, sound, and video as binary data, describe the trade-off between quality and file size, and choose appropriate formats for everyday tasks such as sending photos on WhatsApp or recording voice notes.</p>

<h2>Images as Pixels</h2>
<p>When you take a photo with your phone, the camera divides the picture into a grid of tiny coloured squares called <strong>pixels</strong>. Each pixel is stored as a set of numbers representing the amount of red, green, and blue light. For example, a bright yellow pixel might be stored as high red, high green, and low blue values.</p>
<p>A photo with more pixels has more detail but also a larger file size. A 12-megapixel camera captures roughly 12 million pixels. If each pixel needs three bytes of colour information, the raw image needs about 36 megabytes. That is why phones compress photos before saving or sending them.</p>

<h2>Image Compression</h2>
<p>Compression reduces file size by removing or simplifying information. <strong>JPEG</strong> compression is great for photographs because it removes details the human eye is less likely to notice. <strong>PNG</strong> keeps more detail and supports transparent backgrounds, so it is better for logos and screenshots. If you send a photo of your market stall to a customer, JPEG is usually the right choice because it keeps the file small.</p>

<h2>Sound as Samples</h2>
<p>Sound is a wave. To store sound digitally, a microphone measures the height of the wave thousands of times per second. Each measurement is called a <strong>sample</strong>, and each sample is stored as a binary number. The more samples per second, the more accurate the recording, but the larger the file.</p>
<p>When you record a voice note on WhatsApp, the app samples your voice, compresses it, and sends it as a small audio file. MP3 and AAC are common compressed audio formats. They remove sounds that most people cannot hear, which keeps files small enough to send over mobile networks.</p>

<h2>Video: Images Plus Sound</h2>
<p>Video is simply a sequence of images shown quickly, usually 24 to 60 frames per second, plus a soundtrack. A one-minute video can contain thousands of images, so compression is essential. Services such as YouTube and WhatsApp Status use advanced compression to reduce file sizes while keeping the video watchable.</p>

<h2>Worked Example: Choosing a Format</h2>
<p>Imagine you run a small business selling chickens in Kalomo and you want to send product photos to customers:</p>
<ul>
<li><strong>JPEG:</strong> Best for photos of chickens or eggs because the file is small and the quality is good enough.</li>
<li><strong>PNG:</strong> Best for your business logo with a transparent background.</li>
<li><strong>MP4:</strong> Best for a short video showing your healthy chicks.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Take three photos with your phone: one in bright light, one in shade, and one close-up.</li>
<li>Check the file size of each photo in your gallery.</li>
<li>Send one photo as a document and one as an image in WhatsApp. Compare the file sizes after sending.</li>
<li>Write one sentence explaining why the sizes are different.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Pixel:</strong> A tiny coloured square that makes up a digital image.</li>
<li><strong>Compression:</strong> Reducing file size by removing or simplifying data.</li>
<li><strong>Sample:</strong> A measurement of a sound wave taken at a point in time.</li>
<li><strong>Frame:</strong> A single image in a video sequence.</li>
</ul>

<h2>Summary</h2>
<p>Images, sound, and video are all stored as binary data. Images are grids of pixels, sound is a series of samples, and video is a stream of frames with audio. Compression reduces file size so we can store and share media easily. Choosing the right format helps you save storage space and mobile data.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet/xcae6f4a7ff015e7d:digital-information">Khan Academy — Digital Information</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Web/Media/Formats/Image_types">MDN Web Docs — Image File Types</a></li>
<li><a href="https://www.w3schools.com/html/html_media.asp">W3Schools — HTML Media</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.4 Why Binary Matters: Memory, Storage, and Processors',
                'duration_minutes' => 65,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain the difference between memory and storage, describe how processor speed relates to binary operations, and make better decisions when buying or using computers and smartphones in Zambia.</p>

<h2>Memory versus Storage</h2>
<p>When people talk about computer memory, they often confuse two different things. <strong>Storage</strong> is where files are kept when the computer is turned off, such as on a hard drive or SD card. <strong>Memory</strong>, usually called <strong>RAM</strong>, is where the computer keeps data while it is actively working. When you switch off the power, RAM is cleared.</p>
<p>Think of storage as a filing cabinet and RAM as the desk where you spread out the papers you are currently using. A bigger desk lets you work on more papers at once without constantly opening the cabinet. More RAM lets a computer run more programs at the same time smoothly.</p>

<h2>Processor Speed</h2>
<p>The processor, or <strong>CPU</strong>, is the part of the computer that performs calculations and follows instructions. Its speed is measured in <strong>gigahertz</strong>, which tells you how many cycles the processor can complete each second. Each cycle can perform many binary operations. A 2 GHz processor does roughly two billion cycles per second.</p>
<p>However, more gigahertz does not always mean a faster computer. The number of cores, the amount of RAM, and the type of storage also matter. A modern phone with a good processor and enough RAM can feel faster than an old desktop computer.</p>

<h2>Binary in Everyday Devices</h2>
<p>Every digital device you use depends on binary. Your ZESCO prepaid meter records tokens as binary data. The ATM at the bank counts money using binary. Your smartphone camera saves photos as binary pixels. Even the bar code on a bag of mealie meal is read as a series of black and white stripes that represent binary digits.</p>

<h2>Worked Example: Buying a Phone</h2>
<p>Suppose you have K3,500 to buy a smartphone for your small business. Two phones are available:</p>
<ul>
<li>Phone A: 4 GB RAM, 64 GB storage, older processor</li>
<li>Phone B: 6 GB RAM, 128 GB storage, newer processor</li>
</ul>
<p>Phone B will likely last longer because it has more RAM for running apps, more storage for photos and videos, and a newer processor that handles binary operations more efficiently. If you plan to use mobile money apps, WhatsApp Business, and a camera for stock photos, Phone B is the better investment.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open the settings on your phone or a college computer.</li>
<li>Find the total RAM and total storage.</li>
<li>Calculate how much storage is still free as a percentage of the total.</li>
<li>Write down whether you think the device has enough resources for your daily tasks.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>RAM:</strong> Random Access Memory, the temporary workspace a computer uses while running programs.</li>
<li><strong>Storage:</strong> Permanent space for files, such as a hard drive, SSD, or SD card.</li>
<li><strong>CPU:</strong> Central Processing Unit, the brain of the computer that executes instructions.</li>
<li><strong>Gigahertz:</strong> A measure of processor speed, billions of cycles per second.</li>
</ul>

<h2>Summary</h2>
<p>Binary is the foundation of all computing. Memory holds active data, storage keeps files permanently, and the processor performs billions of binary operations each second. Understanding these ideas helps you choose devices wisely and use them efficiently, whether you are a student, marketeer, or small business owner.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/training/modules/get-started-computers/">Microsoft Learn — Get Started with Computers</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://www.w3schools.com/python/python_datatypes.asp">W3Schools — Python Data Types</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: How Computers Represent Data',
            'description' => 'Test your understanding of binary, bits, bytes, character encoding, and how computers store images and sound.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'How many bits are in one byte?',
                    'explanation' => 'A byte is a group of eight bits, which is enough to represent one character of text.',
                    'options' => [
                        ['text' => '4', 'is_correct' => false],
                        ['text' => '8', 'is_correct' => true],
                        ['text' => '16', 'is_correct' => false],
                        ['text' => '32', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which digit is NOT used in the binary number system?',
                    'explanation' => 'Binary uses only 0 and 1 because computer circuits have two states: on and off.',
                    'options' => [
                        ['text' => '0', 'is_correct' => false],
                        ['text' => '1', 'is_correct' => false],
                        ['text' => '2', 'is_correct' => true],
                        ['text' => 'None of the above', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'RAM keeps its contents after the computer is turned off.',
                    'explanation' => 'RAM is temporary memory. It is cleared when the power is turned off. Storage devices keep data permanently.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does UTF-8 help a computer to do?',
                    'explanation' => 'UTF-8 is a character encoding that maps letters, digits, and symbols to binary codes so computers can store text.',
                    'options' => [
                        ['text' => 'Compress video files', 'is_correct' => false],
                        ['text' => 'Store text from many languages', 'is_correct' => true],
                        ['text' => 'Increase processor speed', 'is_correct' => false],
                        ['text' => 'Connect to the internet', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which format is usually best for sending a photograph over WhatsApp?',
                    'explanation' => 'JPEG compression makes photograph files small while keeping acceptable quality, which saves mobile data.',
                    'options' => [
                        ['text' => 'PNG', 'is_correct' => false],
                        ['text' => 'JPEG', 'is_correct' => true],
                        ['text' => 'MP3', 'is_correct' => false],
                        ['text' => 'TXT', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A digital image is made up of tiny coloured squares called pixels.',
                    'explanation' => 'Images are stored as grids of pixels, where each pixel has colour values for red, green, and blue.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the binary representation of the decimal number 5? (three digits)',
                    'explanation' => 'Decimal 5 equals 4 plus 1, so the binary digits are 1 in the 4 place, 0 in the 2 place, and 1 in the 1 place: 101.',
                    'correct_answer' => '101',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main job of the CPU?',
                    'explanation' => 'The CPU executes instructions and performs calculations, making it the brain of the computer.',
                    'options' => [
                        ['text' => 'Store files permanently', 'is_correct' => false],
                        ['text' => 'Display images on the screen', 'is_correct' => false],
                        ['text' => 'Execute instructions and process data', 'is_correct' => true],
                        ['text' => 'Provide internet connectivity', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What do we call a single binary digit, either 0 or 1? (one word)',
                    'explanation' => 'A bit is the smallest unit of data in a computer.',
                    'correct_answer' => 'bit',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'When you send K50 via MTN MoMo, the transaction is processed as:',
                    'explanation' => 'Computers process mobile money transactions by converting instructions and amounts into binary signals.',
                    'options' => [
                        ['text' => 'Written words in English', 'is_correct' => false],
                        ['text' => 'Binary signals between computers', 'is_correct' => true],
                        ['text' => 'Paper receipts only', 'is_correct' => false],
                        ['text' => 'Voice calls to the bank', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module2Lessons(): array
    {
        return [
            [
                'title' => '2.1 What Is an Algorithm?',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to define an algorithm in plain language, identify algorithms in everyday life, and explain why clear step-by-step instructions are essential for both people and computers.</p>

<h2>Defining an Algorithm</h2>
<p>An <strong>algorithm</strong> is a clear, step-by-step set of instructions for solving a problem or completing a task. You already follow algorithms every day, even if you do not call them that. A recipe for nshima is an algorithm. The instructions for sending money on Airtel Money are an algorithm. The process a shopkeeper follows to count stock at closing time is also an algorithm.</p>
<p>For a computer, an algorithm must be very precise because computers cannot guess or fill in missing steps. If you tell a person to "make some tea," they understand what you mean. If you tell a computer the same thing, it needs every tiny step: boil water, add tea leaves, wait three minutes, pour into a cup, and so on.</p>

<h2>Algorithms in Everyday Zambia</h2>
<p>Algorithms are everywhere. When you board a minibus in Kalomo, the conductor follows an algorithm: greet passengers, collect fares, issue tickets, tell the driver when to stop. When you buy ZESCO tokens, the steps are an algorithm: enter meter number, enter amount, pay, receive token, enter token on meter. Mobile money apps use algorithms to check your PIN, verify your balance, and update both sender and receiver accounts.</p>

<h2>Properties of a Good Algorithm</h2>
<p>A good algorithm has several important properties:</p>
<ul>
<li><strong>Finite:</strong> It must end after a limited number of steps. An algorithm that never stops is useless.</li>
<li><strong>Clear:</strong> Each step must be easy to understand and unambiguous.</li>
<li><strong>Effective:</strong> Each step must be something that can actually be done.</li>
<li><strong>Correct:</strong> It must solve the problem it is meant to solve.</li>
</ul>

<h2>Worked Example: Making a Phone Call</h2>
<p>Here is an algorithm for calling a friend on a smartphone:</p>
<ol>
<li>Unlock the phone.</li>
<li>Open the phone or contacts app.</li>
<li>Find the friend's name.</li>
<li>Tap the name to start the call.</li>
<li>Wait for the friend to answer.</li>
<li>If the friend answers, speak. If not, leave a voice note or try again later.</li>
<li>End the call.</li>
</ol>
<p>This algorithm is finite, clear, and effective. It also includes a decision in step 6, which is common in real algorithms.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a simple task you do often, such as preparing for college, sending money, or opening a shop.</li>
<li>Write down every step in order. Pretend you are explaining it to someone who has never done it before.</li>
<li>Ask a classmate to follow your steps exactly. Did they get the right result?</li>
<li>Improve any steps that were unclear.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Algorithm:</strong> A step-by-step set of instructions for solving a problem or completing a task.</li>
<li><strong>Finite:</strong> An algorithm that ends after a limited number of steps.</li>
<li><strong>Unambiguous:</strong> A step that has only one possible meaning.</li>
<li><strong>Input:</strong> Information given to an algorithm at the start.</li>
<li><strong>Output:</strong> The result produced by an algorithm.</li>
</ul>

<h2>Summary</h2>
<p>An algorithm is a clear, step-by-step procedure for solving a problem. Good algorithms are finite, clear, effective, and correct. Algorithms appear everywhere in Zambian life, from cooking and travel to mobile money and business. Learning to design algorithms is the first step toward programming.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/computing/computer-science/algorithms">Khan Academy — Algorithms</a></li>
<li><a href="https://www.w3schools.com/dsa/dsa_intro.php">W3Schools — Data Structures and Algorithms</a></li>
<li><a href="https://www.freecodecamp.org/news/what-is-an-algorithm/">freeCodeCamp — What Is an Algorithm?</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.2 Flowcharts and Pseudocode',
                'duration_minutes' => 70,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to express simple algorithms using flowcharts and pseudocode, choose the right tool for planning a solution, and convert an everyday task into a structured plan.</p>

<h2>Planning Before Coding</h2>
<p>Before writing a program, programmers usually plan their solution. Two popular planning tools are <strong>flowcharts</strong> and <strong>pseudocode</strong>. Flowcharts use shapes and arrows to show the flow of steps. Pseudocode uses a mixture of everyday language and simple programming-style words to describe the steps. Both help you think clearly before you touch the keyboard.</p>

<h2>Flowchart Symbols</h2>
<p>Flowcharts use standard shapes:</p>
<ul>
<li><strong>Oval:</strong> Start or end of the algorithm.</li>
<li><strong>Rectangle:</strong> A process or action.</li>
<li><strong>Diamond:</strong> A decision with yes/no or true/false branches.</li>
<li><strong>Parallelogram:</strong> Input or output.</li>
<li><strong>Arrows:</strong> Show the direction of flow.</li>
</ul>

<h2>Pseudocode Basics</h2>
<p>Pseudocode is not a real programming language. It is a way of writing steps that looks a little like code but is easy for humans to read. For example, pseudocode for checking a mobile money PIN might look like this:</p>
<pre><code>START
    ASK user for PIN
    IF PIN is correct THEN
        SHOW account balance
    ELSE
        SHOW "Wrong PIN. Try again."
    END IF
END</code></pre>

<h2>Worked Example: Buying ZESCO Tokens</h2>
<p>Here is a flowchart-style plan for buying electricity tokens using mobile money:</p>
<ol>
<li><strong>Start</strong></li>
<li><strong>Input:</strong> Enter meter number and amount.</li>
<li><strong>Process:</strong> Confirm amount is at least K10.</li>
<li><strong>Decision:</strong> Is the balance enough?</li>
<li>If yes, <strong>process</strong> payment and send token.</li>
<li>If no, <strong>output</strong> "Insufficient balance."</li>
<li><strong>End</strong></li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Think of a decision you make often, such as whether to carry an umbrella or how to decide what to cook.</li>
<li>Draw a simple flowchart on paper with ovals, rectangles, and one diamond for the decision.</li>
<li>Write the same algorithm in pseudocode.</li>
<li>Compare the two versions. Which one was easier to create? Which is easier to explain to a friend?</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Flowchart:</strong> A diagram that shows the steps of an algorithm using shapes and arrows.</li>
<li><strong>Pseudocode:</strong> A plain-language description of an algorithm that looks similar to code.</li>
<li><strong>Decision:</strong> A point in an algorithm where the path depends on a yes/no question.</li>
<li><strong>Process:</strong> An action or calculation performed by the algorithm.</li>
</ul>

<h2>Summary</h2>
<p>Flowcharts and pseudocode are planning tools that help you design algorithms before writing real code. Flowcharts are visual and good for showing decisions. Pseudocode is quick to write and close to real programming. Both are essential skills for anyone who wants to solve problems with computers.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/dsa/dsa_intro.php">W3Schools — Data Structures and Algorithms</a></li>
<li><a href="https://www.freecodecamp.org/news/what-is-pseudocode/">freeCodeCamp — What Is Pseudocode?</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-science/algorithms/intro-to-algorithms">Khan Academy — Intro to Algorithms</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.3 Problem Decomposition and Abstraction',
                'duration_minutes' => 70,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to break a large problem into smaller manageable parts, explain what abstraction means in computing, and apply these ideas to real situations such as planning an event or managing a shop.</p>

<h2>Breaking Problems Down</h2>
<p>Large problems can feel overwhelming. <strong>Problem decomposition</strong> means breaking a big problem into smaller, easier problems. Each small problem can be solved on its own, and then the solutions are combined. This is one of the most important skills in computer science.</p>
<p>Imagine you want to organise a school fundraising day in Kalomo. The big problem is "How do we run a successful fundraising day?" This can be decomposed into smaller problems: find a venue, plan activities, advertise the event, sell tickets, manage money on the day, and clean up afterwards. Each of those can be broken down further. "Advertise the event" could become design posters, share on WhatsApp, announce at assembly, and tell local churches.</p>

<h2>Abstraction</h2>
<p><strong>Abstraction</strong> means hiding unnecessary details so you can focus on what matters. When you use a mobile money app, you do not need to understand how the network routes your request or how the database updates your balance. The app abstracts those details away and shows you a simple screen: enter number, enter amount, enter PIN, send.</p>
<p>When driving a car, you use the steering wheel and pedals without thinking about the engine, gearbox, or fuel injection system. The car abstracts the complex mechanics. In computing, abstraction lets programmers build large systems by using simple building blocks without worrying about every tiny detail inside them.</p>

<h2>Worked Example: Running a Small Shop</h2>
<p>Suppose you run a small shop selling groceries. The big problem is "How do I make a profit?" Decompose it:</p>
<ul>
<li><strong>Stock management:</strong> Decide what to buy, how much, and when.</li>
<li><strong>Pricing:</strong> Set prices that cover costs and attract customers.</li>
<li><strong>Sales:</strong> Record each sale and payment method.</li>
<li><strong>Accounting:</strong> Calculate profit, expenses, and tax.</li>
<li><strong>Customer service:</strong> Keep customers happy and coming back.</li>
</ul>
<p>For stock management, you could abstract the details by using a simple spreadsheet. You only need to know item name, quantity bought, quantity sold, and remaining stock. You do not need to think about the file system, the spreadsheet formula engine, or the computer memory storing the file.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a complex task such as preparing for exams, planning a trip, or starting a small business.</li>
<li>Write the big problem at the top of a page.</li>
<li>Break it into at least four smaller problems.</li>
<li>Pick one smaller problem and break it into even smaller steps.</li>
<li>Circle any details you could hide or ignore when explaining the plan to someone else.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Problem decomposition:</strong> Breaking a large problem into smaller, more manageable sub-problems.</li>
<li><strong>Abstraction:</strong> Hiding unnecessary details to focus on what is important.</li>
<li><strong>Sub-problem:</strong> A smaller problem that is part of a larger problem.</li>
<li><strong>Building block:</strong> A reusable piece of a solution that can be combined with others.</li>
</ul>

<h2>Summary</h2>
<p>Problem decomposition and abstraction are powerful thinking tools. Decomposition breaks big problems into smaller pieces, making them easier to solve. Abstraction hides complex details so you can focus on what matters. Together they help programmers, business owners, and students tackle difficult challenges.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.bbc.co.uk/bitesize/guides/zbfny4j/revision/1">BBC Bitesize — Computational Thinking</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-science/algorithms">Khan Academy — Algorithms and Computational Thinking</a></li>
<li><a href="https://www.freecodecamp.org/news/computational-thinking/">freeCodeCamp — Computational Thinking</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.4 Searching and Sorting Algorithms',
                'duration_minutes' => 70,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe two simple ways to search a list and two simple ways to sort a list, compare their strengths, and see how these algorithms appear in everyday applications.</p>

<h2>Why Search and Sort?</h2>
<p>Computers store huge amounts of data. Finding one item quickly is important. So is arranging data in order. <strong>Searching</strong> means looking for a specific item in a collection. <strong>Sorting</strong> means arranging items in a particular order, such as alphabetical or numerical.</p>
<p>When you scroll through your contacts to find a name, your phone is searching. When you sort products on an online shop by price from lowest to highest, the website is sorting. When a bank looks up your account by NRC number, it is searching a database.</p>

<h2>Linear Search</h2>
<p><strong>Linear search</strong> checks each item in a list one by one until it finds the target. It is simple but slow for long lists. If you are looking for a student named "Bwalya" in a handwritten class register, you read each name from the top until you find it. If the list has 100 names and Bwalya is last, you check all 100 names.</p>

<h2>Binary Search</h2>
<p><strong>Binary search</strong> is faster, but it only works if the list is already sorted. You start in the middle. If the target is smaller, you search the left half. If it is larger, you search the right half. You repeat this until you find the item. A phone book is a classic example. To find "Mulenga" you open near the middle, decide whether to go earlier or later in the alphabet, and keep halving the section.</p>

<h2>Simple Sorting</h2>
<p>Two simple sorting methods are <strong>bubble sort</strong> and <strong>insertion sort</strong>. Bubble sort repeatedly steps through the list, compares pairs of items, and swaps them if they are in the wrong order. It is easy to understand but not efficient for large lists. Insertion sort builds a sorted list one item at a time by inserting each new item into its correct place. It works well for small lists or lists that are almost sorted.</p>

<h2>Worked Example: Sorting Exam Marks</h2>
<p>A teacher has these marks from a test: 58, 72, 45, 88, 61. Using bubble sort, the teacher would compare 58 and 72, keep them, compare 72 and 45, swap them, compare 72 and 88, keep them, compare 88 and 61, swap them. After the first pass the largest number, 88, has bubbled to the end. The teacher repeats the process until the list is sorted: 45, 58, 61, 72, 88.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write down a list of ten prices from a nearby shop, for example: 5, 12, 3, 8, 15, 2, 9, 11, 4, 7.</li>
<li>Use bubble sort to arrange them from smallest to largest. Count how many swaps you make.</li>
<li>Pick one price and use linear search to find its position.</li>
<li>Now sort the list and use binary search to find the same price. Which method needed fewer checks?</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Linear search:</strong> Checking each item in a list one by one until the target is found.</li>
<li><strong>Binary search:</strong> Repeatedly dividing a sorted list in half to find a target quickly.</li>
<li><strong>Bubble sort:</strong> A sorting method that repeatedly swaps neighbouring items if they are in the wrong order.</li>
<li><strong>Insertion sort:</strong> A sorting method that inserts each item into its correct place in a growing sorted list.</li>
</ul>

<h2>Summary</h2>
<p>Searching and sorting are fundamental tasks in computing. Linear search is simple but slow for long lists. Binary search is much faster but requires sorted data. Bubble sort and insertion sort are simple sorting methods suitable for small lists. These algorithms power contact lists, online shops, databases, and many other tools you use every day.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/computing/computer-science/algorithms">Khan Academy — Algorithms</a></li>
<li><a href="https://www.w3schools.com/dsa/dsa_intro.php">W3Schools — Data Structures and Algorithms</a></li>
<li><a href="https://www.freecodecamp.org/news/sorting-algorithms-explained/">freeCodeCamp — Sorting Algorithms Explained</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Algorithms and Problem Solving',
            'description' => 'Test your knowledge of algorithms, flowcharts, pseudocode, problem decomposition, abstraction, and basic searching and sorting.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is an algorithm?',
                    'explanation' => 'An algorithm is a clear, step-by-step set of instructions for solving a problem or completing a task.',
                    'options' => [
                        ['text' => 'A type of computer virus', 'is_correct' => false],
                        ['text' => 'A step-by-step set of instructions', 'is_correct' => true],
                        ['text' => 'A programming language', 'is_correct' => false],
                        ['text' => 'A computer monitor', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A good algorithm must eventually come to an end.',
                    'explanation' => 'A good algorithm is finite, meaning it stops after a limited number of steps.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which shape in a flowchart represents a decision?',
                    'explanation' => 'A diamond represents a decision point with yes/no or true/false branches.',
                    'options' => [
                        ['text' => 'Oval', 'is_correct' => false],
                        ['text' => 'Rectangle', 'is_correct' => false],
                        ['text' => 'Diamond', 'is_correct' => true],
                        ['text' => 'Circle', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What do we call the process of breaking a large problem into smaller problems? (two words)',
                    'explanation' => 'Problem decomposition means dividing a complex problem into smaller, manageable sub-problems.',
                    'correct_answer' => 'Problem decomposition',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which search method repeatedly divides a sorted list in half?',
                    'explanation' => 'Binary search halves the search range each time, making it much faster than linear search for sorted data.',
                    'options' => [
                        ['text' => 'Linear search', 'is_correct' => false],
                        ['text' => 'Bubble search', 'is_correct' => false],
                        ['text' => 'Binary search', 'is_correct' => true],
                        ['text' => 'Random search', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Abstraction means adding as many details as possible to a problem.',
                    'explanation' => 'Abstraction hides unnecessary details so we can focus on what is important.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Pseudocode is best described as:',
                    'explanation' => 'Pseudocode uses everyday language mixed with simple code-like structure to describe an algorithm.',
                    'options' => [
                        ['text' => 'A real programming language', 'is_correct' => false],
                        ['text' => 'A diagram made of shapes and arrows', 'is_correct' => false],
                        ['text' => 'A plain-language plan that looks similar to code', 'is_correct' => true],
                        ['text' => 'A type of computer hardware', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which sorting method swaps neighbouring items if they are in the wrong order?',
                    'explanation' => 'Bubble sort repeatedly compares adjacent items and swaps them if they are out of order.',
                    'options' => [
                        ['text' => 'Insertion sort', 'is_correct' => false],
                        ['text' => 'Bubble sort', 'is_correct' => true],
                        ['text' => 'Binary sort', 'is_correct' => false],
                        ['text' => 'Quick sort', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'In computing, what word means hiding unnecessary details? (one word)',
                    'explanation' => 'Abstraction is the process of hiding complex details to focus on the important parts.',
                    'correct_answer' => 'Abstraction',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'If a list is not sorted, which search method can you definitely use?',
                    'explanation' => 'Linear search works on any list, sorted or unsorted, because it checks each item in order.',
                    'options' => [
                        ['text' => 'Binary search', 'is_correct' => false],
                        ['text' => 'Linear search', 'is_correct' => true],
                        ['text' => 'Bubble search', 'is_correct' => false],
                        ['text' => 'No search method works', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module3Lessons(): array
    {
        return [
            [
                'title' => '3.1 Your First Python Program',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to install or access Python, write and run a simple program that prints text, and understand the difference between code, output, and errors.</p>

<h2>What Is Python?</h2>
<p><strong>Python</strong> is a programming language that is popular because it is easy to read and write. It is used for websites, data analysis, automation, games, artificial intelligence, and education. Many schools and colleges around the world teach Python as a first language because the code looks close to plain English.</p>
<p>In Zambia, Python skills can help you build small business tools, analyse sales data, automate repetitive tasks, or prepare for further studies in software development. You can run Python on a college computer, a laptop, or even some Android apps.</p>

<h2>Getting Started</h2>
<p>There are several free ways to start using Python:</p>
<ul>
<li><strong>College computer:</strong> Ask your instructor if Python is installed. Open a terminal or command prompt and type <code>python</code> or <code>python3</code>.</li>
<li><strong>Online interpreter:</strong> Websites such as Replit, Python Tutor, or the official Python online shell let you write code in a browser.</li>
<li><strong>Android phone:</strong> Apps such as Pydroid 3 or Sololearn allow you to practise Python on your phone.</li>
</ul>

<h2>Your First Program</h2>
<p>Every programmer begins with a simple program that prints a message. In Python, we use the <code>print()</code> function. Open your Python environment and type:</p>
<pre><code>print("Hello, Zambia!")</code></pre>
<p>When you run the program, the output is:</p>
<pre><code>Hello, Zambia!</code></pre>
<p>The text inside the brackets is called a <strong>string</strong>. A string is a sequence of characters. The quotation marks tell Python where the string begins and ends. You can use single quotes or double quotes, but they must match.</p>

<h2>Writing Multiple Lines</h2>
<p>You can write several print statements one after another:</p>
<pre><code>print("Welcome to Edutrack.")
print("Today we learn Python.")
print("Kalomo is a great place to study.")</code></pre>
<p>Python runs the lines from top to bottom, just like reading a recipe.</p>

<h2>Worked Example: A Simple Receipt Header</h2>
<p>Suppose you want to print a header for a shop receipt. You could write:</p>
<pre><code>print("========================")
print("  MULENGA'S CORNER SHOP")
print("  Kalomo, Zambia")
print("========================")</code></pre>
<p>This produces a neat header that you could imagine at the top of a receipt.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open a Python environment on your phone or computer.</li>
<li>Type <code>print("My name is ...")</code> using your own name.</li>
<li>Write three more print statements about your town, your favourite subject, and your goal for this course.</li>
<li>Run the program and check the output.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Python:</strong> A popular programming language known for being easy to read and learn.</li>
<li><strong>Program:</strong> A set of instructions written in a programming language.</li>
<li><strong>Function:</strong> A named block of code that performs a specific task, such as <code>print()</code>.</li>
<li><strong>String:</strong> A sequence of characters, usually written inside quotation marks.</li>
<li><strong>Output:</strong> The result produced by a program.</li>
</ul>

<h2>Summary</h2>
<p>Python is a friendly programming language that you can run on computers or phones. Your first program used the <code>print()</code> function to display text. Programs run line by line from top to bottom. Writing simple output programs is the first step toward solving real problems with code.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.python.org/about/gettingstarted/">Python.org — Getting Started</a></li>
<li><a href="https://www.w3schools.com/python/python_intro.asp">W3Schools — Python Introduction</a></li>
<li><a href="https://www.freecodecamp.org/news/python-programming-for-beginners/">freeCodeCamp — Python for Beginners</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.2 Variables, Input, and Output',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to use variables to store information, accept input from a user, perform simple arithmetic, and display formatted output in Python.</p>

<h2>What Is a Variable?</h2>
<p>A <strong>variable</strong> is a named place in the computer's memory where you can store a value. Think of it as a labelled box. You can put a value inside the box, look at it later, or change it. In Python, creating a variable is simple:</p>
<pre><code>name = "Chileshe"
age = 22
price = 15.50</code></pre>
<p>Here, <code>name</code> stores a string, <code>age</code> stores a whole number, and <code>price</code> stores a decimal number. Python figures out the type of value automatically.</p>

<h2>Getting Input from the User</h2>
<p>The <code>input()</code> function lets a program ask the user for information. Whatever the user types is returned as a string. For example:</p>
<pre><code>name = input("What is your name? ")
print("Hello, " + name + "!")</code></pre>
<p>If the user types "Mwape", the program prints "Hello, Mwape!".</p>

<h2>Doing Arithmetic</h2>
<p>Python can do mathematics using these operators:</p>
<ul>
<li><code>+</code> addition</li>
<li><code>-</code> subtraction</li>
<li><code>*</code> multiplication</li>
<li><code>/</code> division</li>
<li><code>**</code> exponentiation, for example <code>2 ** 3</code> means 2 cubed</li>
</ul>
<p>If you want to use input as a number, you must convert it using <code>int()</code> for whole numbers or <code>float()</code> for decimals:</p>
<pre><code>quantity = int(input("How many items? "))
price = float(input("Price per item? "))
total = quantity * price
print("Total cost:", total)</code></pre>

<h2>Worked Example: Shop Total</h2>
<p>Imagine a customer buys 3 bottles of cooking oil at K28.50 each. The program below calculates the total:</p>
<pre><code>quantity = 3
unit_price = 28.50
total = quantity * unit_price
print("Quantity:", quantity)
print("Unit price: K", unit_price)
print("Total: K", total)</code></pre>
<p>The output will be:</p>
<pre><code>Quantity: 3
Unit price: K 28.5
Total: K 85.5</code></pre>

<h2>Try It Yourself</h2>
<ol>
<li>Write a program that asks for the price of one item and the quantity bought.</li>
<li>Calculate the total cost.</li>
<li>Display the result with a clear message such as "You owe K...".</li>
<li>Test your program with different numbers, such as 5 items at K12 each.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Variable:</strong> A named storage location for a value.</li>
<li><strong>Input:</strong> Information entered by the user while the program is running.</li>
<li><strong>String:</strong> A value made of text characters.</li>
<li><strong>Integer:</strong> A whole number without a decimal point.</li>
<li><strong>Float:</strong> A number that can have a decimal point.</li>
</ul>

<h2>Summary</h2>
<p>Variables let programs remember information. The <code>input()</code> function collects data from users, and arithmetic operators perform calculations. Converting input to the right type is essential when working with numbers. These tools are the building blocks of useful programs such as shop calculators and registration forms.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/python/python_variables.asp">W3Schools — Python Variables</a></li>
<li><a href="https://www.python.org/about/gettingstarted/">Python.org — Getting Started</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Computer Programming</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.3 Decisions with if Statements',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to write Python programs that make decisions using <code>if</code>, <code>elif</code>, and <code>else</code> statements, and apply these decisions to real situations such as checking balances, ages, and grades.</p>

<h2>Making Decisions</h2>
<p>Programs often need to make choices. Should a mobile money transfer go ahead? Is a student old enough to register? Is a customer eligible for a discount? In Python, we use <code>if</code> statements to make decisions.</p>
<p>An <code>if</code> statement checks a condition. If the condition is true, the program runs a block of code. If not, it skips that block.</p>

<h2>Comparison Operators</h2>
<p>Conditions use comparison operators:</p>
<ul>
<li><code>==</code> equal to</li>
<li><code>!=</code> not equal to</li>
<li><code>&gt;</code> greater than</li>
<li><code>&lt;</code> less than</li>
<li><code>&gt;=</code> greater than or equal to</li>
<li><code>&lt;=</code> less than or equal to</li>
</ul>

<h2>Simple if Statement</h2>
<pre><code>age = int(input("Enter your age: "))
if age &gt;= 18:
    print("You are an adult.")</code></pre>
<p>If the user enters 20, the condition is true and the message prints. If the user enters 16, nothing happens.</p>

<h2>if...else</h2>
<pre><code>pin = input("Enter your PIN: ")
if pin == "1234":
    print("Access granted.")
else:
    print("Access denied.")</code></pre>
<p>The <code>else</code> block runs when the condition is false.</p>

<h2>if...elif...else</h2>
<p>When there are several possible conditions, use <code>elif</code>, short for "else if":</p>
<pre><code>mark = int(input("Enter exam mark: "))
if mark &gt;= 70:
    print("Grade: Distinction")
elif mark &gt;= 60:
    print("Grade: Merit")
elif mark &gt;= 50:
    print("Grade: Credit")
else:
    print("Grade: Fail")</code></pre>

<h2>Worked Example: Checking a Minimum Order</h2>
<p>A shop offers free delivery for orders of K100 or more. Otherwise, delivery costs K15:</p>
<pre><code>order_total = float(input("Enter order total: "))
if order_total &gt;= 100:
    print("You qualify for free delivery.")
else:
    delivery = 15
    final_total = order_total + delivery
    print("Delivery: K", delivery)
    print("Final total: K", final_total)</code></pre>

<h2>Try It Yourself</h2>
<ol>
<li>Write a program that asks for a customer's age and prints whether they qualify for a youth discount under 25.</li>
<li>Write a program that asks for an exam mark and prints "Pass" if the mark is 50 or above, otherwise "Resit".</li>
<li>Write a program that asks for a PIN and prints "Welcome" if it matches "2024", otherwise "Wrong PIN".</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>if statement:</strong> Code that runs only when a condition is true.</li>
<li><strong>Condition:</strong> A comparison that is either true or false.</li>
<li><strong>else:</strong> A block of code that runs when the if condition is false.</li>
<li><strong>elif:</strong> A way to check another condition if the first one is false.</li>
<li><strong>Comparison operator:</strong> A symbol such as ==, &gt;, or &lt; that compares two values.</li>
</ul>

<h2>Summary</h2>
<p>Decision-making is a core part of programming. Using <code>if</code>, <code>elif</code>, and <code>else</code>, you can write programs that respond differently depending on the situation. This lets you build tools that check passwords, calculate discounts, assign grades, and much more.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/python/python_conditions.asp">W3Schools — Python If...Else</a></li>
<li><a href="https://www.python.org/about/gettingstarted/">Python.org — Getting Started</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Computer Programming</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.4 Repeating with Loops',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to use <code>while</code> and <code>for</code> loops in Python to repeat instructions, and apply loops to tasks such as counting, summing, and displaying lists.</p>

<h2>Why Use Loops?</h2>
<p>Computers are good at repeating tasks without getting tired. A <strong>loop</strong> is a way to repeat a block of code many times. Instead of writing the same line ten times, you write it once and tell Python how many times to run it. Loops save time and reduce mistakes.</p>

<h2>while Loops</h2>
<p>A <code>while</code> loop repeats as long as a condition is true:</p>
<pre><code>count = 1
while count &lt;= 5:
    print(count)
    count = count + 1</code></pre>
<p>This prints the numbers 1 to 5. The loop stops when <code>count</code> becomes 6.</p>

<h2>for Loops</h2>
<p>A <code>for</code> loop is useful when you know how many times to repeat or when you want to work through a list:</p>
<pre><code>for number in range(1, 6):
    print(number)</code></pre>
<p>This also prints 1 to 5. The <code>range(1, 6)</code> includes 1 and stops before 6.</p>

<h2>Looping Through a List</h2>
<p>You can use a loop to process each item in a list:</p>
<pre><code>prices = [12, 18, 25, 10, 30]
total = 0
for price in prices:
    total = total + price
print("Total:", total)</code></pre>
<p>This calculates the total of all prices in the list.</p>

<h2>Worked Example: Counting Change</h2>
<p>Suppose a shopkeeper counts how many K5 coins are needed to reach K50:</p>
<pre><code>total = 0
coins = 0
while total &lt; 50:
    total = total + 5
    coins = coins + 1
    print("Added one coin. Total:", total)
print("You need", coins, "coins.")</code></pre>
<p>The loop runs ten times, adding K5 each time, until the total reaches K50.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write a for loop that prints the numbers from 1 to 10.</li>
<li>Create a list of five prices and use a loop to calculate the total.</li>
<li>Write a while loop that asks the user to enter a password until they type the correct one.</li>
<li>Write a loop that prints the multiplication table for 3, from 3 x 1 to 3 x 12.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Loop:</strong> A structure that repeats a block of code.</li>
<li><strong>while loop:</strong> A loop that repeats while a condition is true.</li>
<li><strong>for loop:</strong> A loop that repeats a known number of times or through a collection.</li>
<li><strong>Iteration:</strong> One single run of a loop body.</li>
<li><strong>range:</strong> A Python function that generates a sequence of numbers.</li>
</ul>

<h2>Summary</h2>
<p>Loops allow programs to repeat instructions efficiently. A while loop repeats while a condition is true, and a for loop is useful for known counts or lists. With loops, you can process many items, count, sum, and build more powerful programs.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/python/python_while_loops.asp">W3Schools — Python While Loops</a></li>
<li><a href="https://www.w3schools.com/python/python_for_loops.asp">W3Schools — Python For Loops</a></li>
<li><a href="https://www.freecodecamp.org/news/python-for-loop-example/">freeCodeCamp — Python For Loops</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Introduction to Programming with Python',
            'description' => 'Test your understanding of Python basics, variables, input and output, decisions, and loops.',
            'time_limit_minutes' => 25,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which Python function displays output on the screen?',
                    'explanation' => 'The print() function is used to display text or values on the screen.',
                    'options' => [
                        ['text' => 'input()', 'is_correct' => false],
                        ['text' => 'display()', 'is_correct' => false],
                        ['text' => 'print()', 'is_correct' => true],
                        ['text' => 'show()', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'In Python, what function is used to get text typed by the user? (one word)',
                    'explanation' => 'The input() function collects text entered by the user.',
                    'correct_answer' => 'input',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the correct way to create a variable called age with the value 21 in Python?',
                    'explanation' => 'Python uses the equals sign to assign a value to a variable, with no keyword like var or let.',
                    'options' => [
                        ['text' => 'var age = 21', 'is_correct' => false],
                        ['text' => 'age = 21', 'is_correct' => true],
                        ['text' => 'int age = 21', 'is_correct' => false],
                        ['text' => 'age := 21', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'In Python, the input() function always returns a string, even if the user types a number.',
                    'explanation' => 'input() returns a string. To use the value as a number, you must convert it with int() or float().',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which operator tests whether two values are equal in Python?',
                    'explanation' => 'The == operator checks for equality, while = is used for assignment.',
                    'options' => [
                        ['text' => '=', 'is_correct' => false],
                        ['text' => '==', 'is_correct' => true],
                        ['text' => '!=', 'is_correct' => false],
                        ['text' => '&gt;', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What Python keyword is used to check another condition after an if statement?',
                    'explanation' => 'elif, short for "else if", checks another condition when the first condition is false.',
                    'options' => [
                        ['text' => 'else', 'is_correct' => false],
                        ['text' => 'elif', 'is_correct' => true],
                        ['text' => 'elseif', 'is_correct' => false],
                        ['text' => 'or', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the output of print(2 + 3 * 4) in Python? (just the number)',
                    'explanation' => 'Multiplication happens before addition, so 3 * 4 = 12, then 2 + 12 = 14.',
                    'correct_answer' => '14',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A for loop in Python can repeat through each item in a list.',
                    'explanation' => 'A for loop can iterate over collections such as lists, ranges, and strings.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which loop repeats while a condition remains true?',
                    'explanation' => 'A while loop continues repeating as long as its condition is true.',
                    'options' => [
                        ['text' => 'for loop', 'is_correct' => false],
                        ['text' => 'while loop', 'is_correct' => true],
                        ['text' => 'if loop', 'is_correct' => false],
                        ['text' => 'repeat loop', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What will the following code display? for n in range(1, 4): print(n)',
                    'explanation' => 'range(1, 4) generates 1, 2, and 3. It stops before 4.',
                    'options' => [
                        ['text' => '1 2 3 4', 'is_correct' => false],
                        ['text' => '0 1 2 3', 'is_correct' => false],
                        ['text' => '1 2 3', 'is_correct' => true],
                        ['text' => '4 3 2 1', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'Which Python keyword runs a block of code only when a condition is true? (one word)',
                    'explanation' => 'The if keyword begins a conditional block that runs only when its condition is true.',
                    'correct_answer' => 'if',
                ],
            ],
        ];
    }

    private function module4Lessons(): array
    {
        return [
            [
                'title' => '4.1 Lists and Arrays: Organising Data',
                'duration_minutes' => 65,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what a list is, create and use lists in Python, access items by position, and understand why lists are useful for organising related data.</p>

<h2>What Is a List?</h2>
<p>A <strong>list</strong> is a collection of related items stored in a single variable. Items stay in order, and each item has a position called an <strong>index</strong>. Lists are useful when you need to work with many related values, such as prices, names, or scores.</p>
<p>In Python, a list is written inside square brackets, with commas between items:</p>
<pre><code>prices = [12, 18, 25, 10, 30]
names = ["Anna", "Brian", "Chanda", "Doris"]</code></pre>

<h2>Accessing Items</h2>
<p>Items are numbered starting from 0. The first item is at index 0, the second at index 1, and so on:</p>
<pre><code>names = ["Anna", "Brian", "Chanda", "Doris"]
print(names[0])  # Anna
print(names[2])  # Chanda</code></pre>
<p>You can also count from the end using negative indices. <code>names[-1]</code> is the last item.</p>

<h2>Changing and Adding Items</h2>
<p>Lists are flexible. You can change an item, add an item, or remove an item:</p>
<pre><code>prices = [12, 18, 25]
prices[1] = 20          # change second item
prices.append(30)       # add to the end
prices.remove(12)       # remove the value 12</code></pre>

<h2>Worked Example: Market Stock List</h2>
<p>A market stall owner tracks stock using a list:</p>
<pre><code>items = ["tomatoes", "onions", "cabbage", "cooking oil"]
quantities = [25, 40, 15, 10]

print("Items for sale:", items)
print("Tomatoes in stock:", quantities[0])

# Sell 5 tomatoes
quantities[0] = quantities[0] - 5
print("Tomatoes remaining:", quantities[0])</code></pre>

<h2>Try It Yourself</h2>
<ol>
<li>Create a list of five items you sell or buy regularly.</li>
<li>Create a second list with their prices.</li>
<li>Print the third item and its price.</li>
<li>Add a new item to both lists and print the updated lists.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>List:</strong> An ordered collection of items stored in a single variable.</li>
<li><strong>Index:</strong> The position of an item in a list, starting from 0.</li>
<li><strong>Element:</strong> A single item in a list.</li>
<li><strong>Append:</strong> To add an item to the end of a list.</li>
</ul>

<h2>Summary</h2>
<p>Lists let you store and organise related data in one place. You can access items by index, change values, add items, and remove items. Lists are essential for programs that work with collections of data such as stock, scores, or names.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/python/python_lists.asp">W3Schools — Python Lists</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Computer Programming</a></li>
<li><a href="https://docs.python.org/3/tutorial/introduction.html#lists">Python.org — Lists Tutorial</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.2 Queues and Stacks: Real-Life Analogies',
                'duration_minutes' => 65,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe queues and stacks, explain the difference between first-in-first-out and last-in-first-out, and give Zambian examples of each structure.</p>

<h2>Queues: First In, First Out</h2>
<p>A <strong>queue</strong> is a collection where the first item added is the first item removed. This is called <strong>FIFO</strong>, or "first in, first out." A queue works like a line of people waiting at a bank or a water tap. The first person to arrive is the first person to be served.</p>
<p>When you visit a bank in Lusaka and join the queue, you stand behind the person who arrived before you. When the teller is free, the person at the front is served next. Banking apps, ticket systems, and customer support lines all use queues to manage requests fairly.</p>

<h2>Stacks: Last In, First Out</h2>
<p>A <strong>stack</strong> is a collection where the last item added is the first item removed. This is called <strong>LIFO</strong>, or "last in, first out." A stack works like a pile of plates. You add plates to the top, and when you need one, you take the top plate first.</p>
<p>On your phone, the "back" button uses a stack. Each screen you open is placed on top of the previous one. When you press back, you return to the screen you were on last. Undo buttons in word processors also use stacks: the last action you did is the first one you can undo.</p>

<h2>Worked Example: A Minibus Queue</h2>
<p>At a minibus station in Kalomo, passengers form a queue. The first passenger to arrive boards first when the bus is ready. If the queue is managed by a conductor with a notebook, the names might be stored in a queue:</p>
<pre><code>passengers = ["Mwape", "Chileshe", "Namukonda"]
# Mwape arrived first, so Mwape boards first
next_passenger = passengers.pop(0)
print("Boarding:", next_passenger)</code></pre>
<p>This removes "Mwape" from the front of the queue.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write down three real-life examples of queues in your community.</li>
<li>Write down three real-life examples of stacks.</li>
<li>Explain to a classmate why a bank should use a queue rather than a stack to serve customers.</li>
<li>Think about your phone apps. Which action behaves like a stack?</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Queue:</strong> A data structure where the first item added is the first item removed.</li>
<li><strong>Stack:</strong> A data structure where the last item added is the first item removed.</li>
<li><strong>FIFO:</strong> First In, First Out, the rule for queues.</li>
<li><strong>LIFO:</strong> Last In, First Out, the rule for stacks.</li>
</ul>

<h2>Summary</h2>
<p>Queues and stacks are simple but powerful ways to organise data. Queues use FIFO, like bank lines and minibus boarding. Stacks use LIFO, like piles of plates and phone back buttons. Understanding these structures helps you design fair and efficient systems.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/dsa/dsa_data_queues.asp">W3Schools — Queues</a></li>
<li><a href="https://www.w3schools.com/dsa/dsa_data_stacks.asp">W3Schools — Stacks</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-science/algorithms">Khan Academy — Algorithms</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.3 Dictionaries and Records',
                'duration_minutes' => 65,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what a dictionary is, describe how records store related facts about one thing, and understand why dictionaries are useful for looking up information quickly.</p>

<h2>What Is a Dictionary?</h2>
<p>A <strong>dictionary</strong> stores data as pairs of keys and values. Each key is a label, and each value is the information you want to store. In Python, dictionaries are written with curly braces:</p>
<pre><code>student = {
    "name": "Mutale",
    "age": 19,
    "course": "Computer Science General",
    "town": "Kalomo"
}</code></pre>
<p>You can look up a value using its key:</p>
<pre><code>print(student["name"])  # Mutale
print(student["town"])  # Kalomo</code></pre>

<h2>Records in Real Life</h2>
<p>A <strong>record</strong> is a set of related facts about one thing. Your NRC record contains your name, date of birth, place of birth, and photo. A customer record at a shop contains name, phone number, and purchase history. Each record has the same kind of fields, but the values are different.</p>
<p>In a database, a table stores many records. Each row is one record, and each column is one field. For example, a shop might have a customers table with columns for name, phone, and town.</p>

<h2>Worked Example: Product Lookup</h2>
<p>A small shop uses a dictionary to store prices:</p>
<pre><code>prices = {
    "mealie meal": 85,
    "sugar": 55,
    "cooking oil": 78,
    "soap": 12
}

item = input("Enter item name: ")
if item in prices:
    print("Price: K", prices[item])
else:
    print("Item not found.")</code></pre>

<h2>Try It Yourself</h2>
<ol>
<li>Create a dictionary that stores information about yourself: name, age, town, and course.</li>
<li>Print each value using its key.</li>
<li>Create a dictionary of five items and their prices in Kwacha.</li>
<li>Ask the user for an item and print its price, or a "not found" message.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Dictionary:</strong> A data structure that stores key-value pairs.</li>
<li><strong>Key:</strong> A label used to look up a value in a dictionary.</li>
<li><strong>Value:</strong> The data stored under a key.</li>
<li><strong>Record:</strong> A set of related facts about one person, thing, or event.</li>
</ul>

<h2>Summary</h2>
<p>Dictionaries store information as key-value pairs, making it easy to look up values quickly. Records are collections of related facts about one item. Together, lists, queues, stacks, and dictionaries give programmers flexible ways to organise data.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/python/python_dictionaries.asp">W3Schools — Python Dictionaries</a></li>
<li><a href="https://docs.python.org/3/tutorial/datastructures.html#dictionaries">Python.org — Dictionaries</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Computer Programming</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.4 Choosing the Right Structure',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to compare lists, queues, stacks, and dictionaries, and choose the most appropriate structure for a given problem.</p>

<h2>Comparing Structures</h2>
<p>Each data structure has strengths and weaknesses. Choosing the right one makes your program simpler and faster.</p>
<table>
<thead>
<tr><th>Structure</th><th>Best For</th><th>Example</th></tr>
</thead>
<tbody>
<tr><td>List</td><td>Ordered collection of items</td><td>Class register of names</td></tr>
<tr><td>Queue</td><td>Fair first-come-first-served order</td><td>Bank customer line</td></tr>
<tr><td>Stack</td><td>Undo or reverse order</td><td>Phone back button</td></tr>
<tr><td>Dictionary</td><td>Fast lookup by name or key</td><td>Price list by item name</td></tr>
</tbody>
</table>

<h2>When to Use Each</h2>
<p>Use a <strong>list</strong> when order matters and you want to access items by position. Use a <strong>queue</strong> when fairness matters and the first item added should be processed first. Use a <strong>stack</strong> when the most recent item is the most important. Use a <strong>dictionary</strong> when you want to look up information using a meaningful key.</p>

<h2>Worked Example: Choosing for a Shop</h2>
<p>Imagine you are building a simple system for a shop in Kalomo. Here are good choices:</p>
<ul>
<li><strong>List:</strong> Store the daily sales amounts in order.</li>
<li><strong>Dictionary:</strong> Store item prices so the cashier can look them up by name.</li>
<li><strong>Queue:</strong> Manage customers waiting to pay.</li>
<li><strong>Stack:</strong> Allow the cashier to undo the last entry if a mistake is made.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>For each scenario below, choose the best data structure:
<ul>
<li>Storing students' exam marks with their names.</li>
<li>Managing cars waiting to refuel at a petrol station.</li>
<li>Tracking the history of web pages visited in a browser.</li>
</ul></li>
<li>Explain your choice to a classmate.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Data structure:</strong> A way of organising data so it can be used efficiently.</li>
<li><strong>Lookup:</strong> Finding information using a key or index.</li>
<li><strong>Order:</strong> The arrangement of items in a collection.</li>
</ul>

<h2>Summary</h2>
<p>Different data structures suit different problems. Lists keep ordered items, queues manage fair waiting lines, stacks handle last-in-first-out tasks, and dictionaries provide fast lookup by key. Choosing wisely makes programs clearer and more efficient.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/dsa/dsa_intro.php">W3Schools — Data Structures and Algorithms</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-science/algorithms">Khan Academy — Algorithms</a></li>
<li><a href="https://www.freecodecamp.org/news/data-structures-explained/">freeCodeCamp — Data Structures Explained</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module4Quiz(): array
    {
        return [
            'title' => 'Module 4 Quiz: Data Structures Conceptually',
            'description' => 'Test your understanding of lists, queues, stacks, dictionaries, and how to choose the right data structure.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In a Python list, what is the index of the first item?',
                    'explanation' => 'Python lists use zero-based indexing, so the first item is at index 0.',
                    'options' => [
                        ['text' => '0', 'is_correct' => true],
                        ['text' => '1', 'is_correct' => false],
                        ['text' => '-1', 'is_correct' => false],
                        ['text' => 'None of the above', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'In a queue, the last person to arrive is served first.',
                    'explanation' => 'Queues follow FIFO: first in, first out. The first person to arrive is served first.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which data structure uses the LIFO rule?',
                    'explanation' => 'A stack uses Last In, First Out, like a pile of plates or a phone back button.',
                    'options' => [
                        ['text' => 'Queue', 'is_correct' => false],
                        ['text' => 'List', 'is_correct' => false],
                        ['text' => 'Stack', 'is_correct' => true],
                        ['text' => 'Dictionary', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What do the letters FIFO stand for? (four words)',
                    'explanation' => 'FIFO stands for First In, First Out, the rule used by queues.',
                    'correct_answer' => 'First In First Out',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which data structure is best for looking up a price by item name?',
                    'explanation' => 'Dictionaries store key-value pairs, so you can use the item name as a key to look up the price.',
                    'options' => [
                        ['text' => 'Queue', 'is_correct' => false],
                        ['text' => 'Stack', 'is_correct' => false],
                        ['text' => 'Dictionary', 'is_correct' => true],
                        ['text' => 'FIFO', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A list in Python can contain items of different types, such as strings and numbers.',
                    'explanation' => 'Python lists can hold mixed types, although it is often cleaner to keep items of the same type.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which method adds an item to the end of a Python list?',
                    'explanation' => 'The append() method adds a new item to the end of a list.',
                    'options' => [
                        ['text' => 'add()', 'is_correct' => false],
                        ['text' => 'insert()', 'is_correct' => false],
                        ['text' => 'append()', 'is_correct' => true],
                        ['text' => 'extend()', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'In Python, what type of brackets are used to create a dictionary? (one word)',
                    'explanation' => 'Dictionaries in Python are created using curly braces {}.',
                    'correct_answer' => 'curly',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which structure is best for a bank queue where customers are served in arrival order?',
                    'explanation' => 'A queue follows FIFO, making it fair for serving customers in the order they arrive.',
                    'options' => [
                        ['text' => 'Stack', 'is_correct' => false],
                        ['text' => 'Queue', 'is_correct' => true],
                        ['text' => 'Dictionary', 'is_correct' => false],
                        ['text' => 'Set', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does each entry in a dictionary consist of?',
                    'explanation' => 'A dictionary stores pairs of keys and values.',
                    'options' => [
                        ['text' => 'Rows and columns', 'is_correct' => false],
                        ['text' => 'Key and value', 'is_correct' => true],
                        ['text' => 'Bits and bytes', 'is_correct' => false],
                        ['text' => 'Input and output', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module5Lessons(): array
    {
        return [
            [
                'title' => '5.1 How the Internet Works: DNS, Servers, and Packets',
                'duration_minutes' => 70,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain how data travels across the internet, describe the roles of DNS, servers, and packets, and understand why websites sometimes load slowly.</p>

<h2>The Internet as a Network</h2>
<p>The <strong>internet</strong> is a global network of connected computers. When you open a website, send a WhatsApp message, or check your email, your device communicates with other computers across this network. No single company owns the whole internet. It is made up of millions of devices connected by cables, fibre optics, satellites, and wireless links.</p>

<h2>Packets</h2>
<p>When you send data over the internet, it is broken into small chunks called <strong>packets</strong>. Each packet contains part of the message plus information about where it came from and where it is going. Packets can travel different routes and are reassembled at the destination. If one route is busy, packets can take another path.</p>
<p>Think of it like sending a long letter by cutting it into pieces and mailing each piece separately. Each piece has the address on it. The receiver puts the pieces back in order to read the full letter.</p>

<h2>IP Addresses</h2>
<p>Every device on the internet has a unique address called an <strong>IP address</strong>. It looks something like 192.168.1.1 for local networks, or longer for the modern internet. IP addresses help packets find their way to the right device.</p>

<h2>DNS: The Internet's Phone Book</h2>
<p>People remember names better than numbers. <strong>DNS</strong>, the Domain Name System, translates human-friendly names such as <code>google.com</code> into IP addresses. When you type a website address, your device asks a DNS server for the matching IP address, then connects to that server.</p>

<h2>Servers and Clients</h2>
<p>A <strong>client</strong> is a device that asks for information, such as your phone or computer. A <strong>server</strong> is a computer that provides information or services. When you visit a website, your browser is the client and the website runs on a server. When you use mobile money, your phone is the client and the bank's computers are the servers.</p>

<h2>Worked Example: Loading a Website</h2>
<p>When you type <code>www.zra.org.zm</code> into your browser:</p>
<ol>
<li>Your browser asks a DNS server for the IP address of <code>www.zra.org.zm</code>.</li>
<li>The DNS server replies with the IP address.</li>
<li>Your browser sends a request to that IP address.</li>
<li>The ZRA server receives the request and sends back packets containing the webpage.</li>
<li>Your browser reassembles the packets and displays the page.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Open a web browser and visit a familiar website.</li>
<li>While it loads, think about the steps: DNS lookup, request, packets, reassembly.</li>
<li>Try loading the same website on mobile data and on Wi-Fi if possible. Which is faster?</li>
<li>Write down one reason why a website might load slowly.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Internet:</strong> A global network of connected computers.</li>
<li><strong>Packet:</strong> A small chunk of data sent over a network.</li>
<li><strong>IP address:</strong> A unique address assigned to each device on a network.</li>
<li><strong>DNS:</strong> Domain Name System, which translates website names into IP addresses.</li>
<li><strong>Server:</strong> A computer that provides services or data to clients.</li>
</ul>

<h2>Summary</h2>
<p>The internet moves data in packets between clients and servers using IP addresses. DNS translates website names into addresses so humans do not need to remember numbers. Understanding these basics helps you troubleshoot connection problems and use the internet more effectively.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn/Common_questions/Web_mechanics/How_does_the_Internet_work">MDN Web Docs — How Does the Internet Work?</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://www.w3schools.com/dns/dns_intro.asp">W3Schools — DNS Introduction</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.2 What Is a Database?',
                'duration_minutes' => 65,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what a database is, describe the difference between a database and a spreadsheet, and understand how databases help organisations manage information reliably.</p>

<h2>What Is a Database?</h2>
<p>A <strong>database</strong> is an organised collection of data that is stored electronically. Databases are designed to store large amounts of information, allow fast searching, keep data consistent, and let many users access information at the same time.</p>
<p>When you register for a course, your details go into a database. When a shop checks stock, it queries a database. When a bank looks up your account balance, it reads from a database. Even WhatsApp stores your messages in a database on your phone.</p>

<h2>Tables, Rows, and Columns</h2>
<p>Most databases organise data into <strong>tables</strong>. Each table has <strong>rows</strong> and <strong>columns</strong>. A row is one record, and a column is one field. For example, a shop's customers table might look like this:</p>
<table>
<thead>
<tr><th>Customer ID</th><th>Name</th><th>Phone</th><th>Town</th></tr>
</thead>
<tbody>
<tr><td>1</td><td>Mutale</td><td>0977123456</td><td>Kalomo</td></tr>
<tr><td>2</td><td>Namukonda</td><td>0967987654</td><td>Livingstone</td></tr>
<tr><td>3</td><td>Chileshe</td><td>0976543210</td><td>Lusaka</td></tr>
</tbody>
</table>

<h2>Database versus Spreadsheet</h2>
<p>A spreadsheet is good for small amounts of data and one user at a time. A database is better when:</p>
<ul>
<li>Many users need access at the same time.</li>
<li>The data is large or grows quickly.</li>
<li>You need to prevent duplicate or inconsistent data.</li>
<li>You want to connect information across multiple tables.</li>
</ul>

<h2>Worked Example: A School Database</h2>
<p>A college might use a database with tables for students, courses, enrollments, and payments. The enrollment table connects students to courses using student IDs and course IDs. This prevents the same student from being entered many times and keeps records accurate.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open a spreadsheet or a notebook.</li>
<li>Design a simple table for a shop with these columns: Item, Quantity, Price, Supplier.</li>
<li>Add five rows of sample data.</li>
<li>Write one advantage a database would have over this spreadsheet if the shop grew to 1000 items.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Database:</strong> An organised collection of electronic data.</li>
<li><strong>Table:</strong> A structure in a database made of rows and columns.</li>
<li><strong>Row:</strong> One record in a table.</li>
<li><strong>Column:</strong> One field or type of information in a table.</li>
<li><strong>Query:</strong> A request to retrieve or change data in a database.</li>
</ul>

<h2>Summary</h2>
<p>Databases store organised data so it can be searched, updated, and shared reliably. Tables, rows, and columns structure the data. Databases scale better than spreadsheets and are essential for businesses, schools, banks, and government services.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/computing/computer-programming/sql">Khan Academy — SQL</a></li>
<li><a href="https://www.w3schools.com/sql/sql_intro.asp">W3Schools — SQL Introduction</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/intro-to-relational-data/">Microsoft Learn — Relational Data</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.3 Software Development Life Cycle',
                'duration_minutes' => 70,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe the main stages of the software development life cycle, explain why planning and testing matter, and recognise these stages in projects around you.</p>

<h2>What Is the SDLC?</h2>
<p>The <strong>Software Development Life Cycle</strong>, or <strong>SDLC</strong>, is the process teams follow to build software. It includes planning, designing, building, testing, deploying, and maintaining software. Following a clear process helps teams avoid mistakes, stay within budget, and deliver useful products.</p>

<h2>The Main Stages</h2>
<ul>
<li><strong>Requirements:</strong> Find out what the software needs to do. Talk to users, write down features, and set goals.</li>
<li><strong>Design:</strong> Plan how the software will look and work. Create sketches, flowcharts, and database designs.</li>
<li><strong>Development:</strong> Write the actual code. Programmers build the features decided during design.</li>
<li><strong>Testing:</strong> Check that the software works correctly. Find and fix bugs before users see them.</li>
<li><strong>Deployment:</strong> Release the software for users. This might mean uploading to a server or publishing an app.</li>
<li><strong>Maintenance:</strong> Fix problems, add features, and keep the software running over time.</li>
</ul>

<h2>Worked Example: Building a Mobile Money App</h2>
<p>Imagine a Zambian company wants to improve its mobile money app:</p>
<ol>
<li><strong>Requirements:</strong> Users want to check balances, send money, and pay bills.</li>
<li><strong>Design:</strong> Designers create screens showing menus, PIN entry, and confirmation pages.</li>
<li><strong>Development:</strong> Programmers write code for Android and iOS.</li>
<li><strong>Testing:</strong> Testers send K1 between accounts, enter wrong PINs, and check error messages.</li>
<li><strong>Deployment:</strong> The updated app is released on the Play Store and App Store.</li>
<li><strong>Maintenance:</strong> The team fixes crashes and adds new bill payment options.</li>
</ol>

<h2>Why Planning and Testing Matter</h2>
<p>Skipping planning leads to software that does not solve the right problem. Skipping testing leads to bugs that frustrate users and can cause financial loss. A well-tested app builds trust. A poorly tested app can damage a company's reputation.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Think of an app or website you use often, such as a mobile money app or a college portal.</li>
<li>For each SDLC stage, write one sentence about what the developers probably did.</li>
<li>Identify one bug or problem you have noticed in software. Which stage could have prevented it?</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>SDLC:</strong> Software Development Life Cycle, the process of building software.</li>
<li><strong>Requirements:</strong> The features and goals a software system must meet.</li>
<li><strong>Deployment:</strong> Releasing software for users.</li>
<li><strong>Maintenance:</strong> Keeping software running and improving it over time.</li>
<li><strong>Bug:</strong> An error or fault in a program.</li>
</ul>

<h2>Summary</h2>
<p>The software development life cycle guides teams from idea to finished product. Requirements, design, development, testing, deployment, and maintenance each play an important role. Good planning and thorough testing lead to reliable software that users can trust.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/whatis/whatis_sdlc.asp">W3Schools — What is SDLC?</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/intro-to-azure-fundamentals/">Microsoft Learn — Software Development Basics</a></li>
<li><a href="https://www.freecodecamp.org/news/software-development-life-cycle/">freeCodeCamp — Software Development Life Cycle</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.4 Version Control and Collaboration',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain why version control is important, describe how teams collaborate on code, and understand the basic ideas behind tools such as Git.</p>

<h2>Why Version Control?</h2>
<p>When programmers work on a project, they make many changes over time. <strong>Version control</strong> keeps track of every change, who made it, and when. It allows developers to go back to earlier versions if something breaks, and it helps teams work on the same files without overwriting each other's work.</p>
<p>Imagine writing a long essay and saving it as "essay_final.docx", then "essay_final2.docx", then "essay_really_final.docx." Version control replaces this mess with a clear history of changes.</p>

<h2>Git and GitHub</h2>
<p><strong>Git</strong> is the most popular version control tool. It stores snapshots of files and tracks changes. <strong>GitHub</strong> is an online service that hosts Git repositories and helps teams collaborate. You can use Git on your own computer and push your work to GitHub to share it.</p>

<h2>Basic Concepts</h2>
<ul>
<li><strong>Repository:</strong> A folder containing a project and its version history.</li>
<li><strong>Commit:</strong> A saved snapshot of changes with a message describing what was done.</li>
<li><strong>Branch:</strong> A separate line of development, like a copy of the project where you can experiment.</li>
<li><strong>Merge:</strong> Combining changes from one branch back into another.</li>
</ul>

<h2>Worked Example: Team Project</h2>
<p>A team of students in Kalomo is building a website. One student works on the homepage, another on the contact form, and another on the database. They each create a branch in Git, make their changes, and merge them back into the main project. If one branch has a problem, the others are not affected, and the team can see exactly who changed what.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Create a folder for a simple project on your computer.</li>
<li>Write a short text file and save it.</li>
<li>Change the file and save it again with a different name to simulate versions.</li>
<li>Write down three problems this manual approach could cause if five people were editing the file.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Version control:</strong> Tracking changes to files over time.</li>
<li><strong>Repository:</strong> A storage location for a project and its version history.</li>
<li><strong>Commit:</strong> A saved record of changes.</li>
<li><strong>Branch:</strong> A separate version of a project used for development.</li>
<li><strong>Merge:</strong> Combining changes from different branches.</li>
</ul>

<h2>Summary</h2>
<p>Version control tracks changes and helps teams collaborate without losing work. Git is a popular tool for this, and GitHub hosts projects online. Branches let developers experiment safely, and commits create a clear history of the project.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/git/git_intro.asp">W3Schools — Git Introduction</a></li>
<li><a href="https://www.freecodecamp.org/news/what-is-git-and-how-to-use-it/">freeCodeCamp — What Is Git?</a></li>
<li><a href="https://docs.github.com/en/get-started">GitHub Docs — Getting Started</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module5Quiz(): array
    {
        return [
            'title' => 'Module 5 Quiz: The Internet, Databases, and Software Development',
            'description' => 'Test your understanding of how the internet works, databases, the software development life cycle, and version control.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does DNS translate a website name into?',
                    'explanation' => 'DNS, the Domain Name System, translates human-readable domain names into IP addresses.',
                    'options' => [
                        ['text' => 'A file name', 'is_correct' => false],
                        ['text' => 'An IP address', 'is_correct' => true],
                        ['text' => 'A phone number', 'is_correct' => false],
                        ['text' => 'A password', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What do we call a small chunk of data sent over the internet? (one word)',
                    'explanation' => 'Data on the internet is broken into packets before being sent and reassembled at the destination.',
                    'correct_answer' => 'packet',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A database is always less useful than a spreadsheet for managing business data.',
                    'explanation' => 'Databases are usually better than spreadsheets for large, multi-user, or connected data.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In a database table, what is a single row called?',
                    'explanation' => 'Each row in a database table represents one record.',
                    'options' => [
                        ['text' => 'A field', 'is_correct' => false],
                        ['text' => 'A column', 'is_correct' => false],
                        ['text' => 'A record', 'is_correct' => true],
                        ['text' => 'A query', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which SDLC stage involves releasing software to users?',
                    'explanation' => 'Deployment is the stage where software is released and made available to users.',
                    'options' => [
                        ['text' => 'Design', 'is_correct' => false],
                        ['text' => 'Testing', 'is_correct' => false],
                        ['text' => 'Deployment', 'is_correct' => true],
                        ['text' => 'Requirements', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A server is a computer that provides services or data to clients.',
                    'explanation' => 'Servers respond to requests from clients such as browsers, phones, or apps.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does a commit record in version control?',
                    'explanation' => 'A commit is a saved snapshot of changes with a description of what was done.',
                    'options' => [
                        ['text' => 'A list of bugs', 'is_correct' => false],
                        ['text' => 'A saved snapshot of changes', 'is_correct' => true],
                        ['text' => 'A new server', 'is_correct' => false],
                        ['text' => 'A website domain', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What do the letters SDLC stand for? (four words)',
                    'explanation' => 'SDLC stands for Software Development Life Cycle.',
                    'correct_answer' => 'Software Development Life Cycle',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which tool is commonly used for version control?',
                    'explanation' => 'Git is the most widely used version control system.',
                    'options' => [
                        ['text' => 'Excel', 'is_correct' => false],
                        ['text' => 'Git', 'is_correct' => true],
                        ['text' => 'Photoshop', 'is_correct' => false],
                        ['text' => 'Chrome', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'When you visit a website, which device is acting as the client?',
                    'explanation' => 'Your phone, tablet, or computer requests the website and is therefore the client.',
                    'options' => [
                        ['text' => 'The website server', 'is_correct' => false],
                        ['text' => 'Your phone or computer', 'is_correct' => true],
                        ['text' => 'The DNS server only', 'is_correct' => false],
                        ['text' => 'The internet cable', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module6Lessons(): array
    {
        return [
            [
                'title' => '6.1 Computational Thinking in Everyday Zambian Life',
                'duration_minutes' => 65,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe computational thinking, identify its four main parts, and recognise how you already use it in daily life in Zambia.</p>

<h2>What Is Computational Thinking?</h2>
<p><strong>Computational thinking</strong> is a way of solving problems that draws on ideas from computer science. It does not always mean using a computer. It means thinking in a logical, organised way. The four main parts are:</p>
<ul>
<li><strong>Decomposition:</strong> Breaking a big problem into smaller parts.</li>
<li><strong>Pattern recognition:</strong> Finding similarities between problems.</li>
<li><strong>Abstraction:</strong> Focusing on what matters and ignoring details that are not important.</li>
<li><strong>Algorithms:</strong> Creating step-by-step instructions to solve a problem.</li>
</ul>

<h2>Everyday Examples</h2>
<p>You already use computational thinking. When you plan a trip from Kalomo to Lusaka, you decompose the journey: find transport, pack, travel, arrive, find accommodation. When you notice that tomatoes are cheaper on Wednesdays, you are recognising a pattern. When you use a mobile money app without understanding the network, you are benefiting from abstraction. When you follow a recipe, you are following an algorithm.</p>

<h2>Why It Matters</h2>
<p>Computational thinking helps you solve problems faster and more reliably. It helps you plan businesses, manage time, teach others, and design systems. In a world where technology is everywhere, thinking like a computer scientist is a valuable skill, even if you never write a program.</p>

<h2>Worked Example: Planning Load-Shedding</h2>
<p>Suppose ZESCO announces load-shedding in your area. You need to plan your day:</p>
<ol>
<li><strong>Decomposition:</strong> List the tasks that need electricity: charging phone, ironing, pumping water, studying.</li>
<li><strong>Pattern recognition:</strong> Notice that power usually goes off from 08:00 to 14:00 on weekdays.</li>
<li><strong>Abstraction:</strong> Ignore entertainment for now and focus on essential tasks.</li>
<li><strong>Algorithm:</strong> Create a schedule: charge phone before 08:00, iron clothes in the evening, study after 14:00.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Think of a problem you faced this week.</li>
<li>Write how you broke it down.</li>
<li>Did you notice any patterns?</li>
<li>What details did you ignore?</li>
<li>What steps did you follow?</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Computational thinking:</strong> A problem-solving approach using computer science ideas.</li>
<li><strong>Pattern recognition:</strong> Finding similarities or trends in problems or data.</li>
<li><strong>Decomposition:</strong> Breaking a problem into smaller parts.</li>
<li><strong>Abstraction:</strong> Ignoring unnecessary details.</li>
<li><strong>Algorithm:</strong> A step-by-step plan to solve a problem.</li>
</ul>

<h2>Summary</h2>
<p>Computational thinking is a practical way to solve problems. Decomposition, pattern recognition, abstraction, and algorithms help you tackle challenges at home, at work, and in your community. These skills are useful whether you become a programmer, a teacher, a business owner, or a civil servant.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.bbc.co.uk/bitesize/guides/zbfny4j/revision/1">BBC Bitesize — Computational Thinking</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-science/algorithms">Khan Academy — Algorithms and Computational Thinking</a></li>
<li><a href="https://www.freecodecamp.org/news/computational-thinking/">freeCodeCamp — Computational Thinking</a></li>
</ul>
HTML,
            ],
            [
                'title' => '6.2 Optimising a Minibus Route',
                'duration_minutes' => 70,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe what optimisation means, identify factors that affect a minibus route, and use computational thinking to suggest a better route.</p>

<h2>What Is Optimisation?</h2>
<p><strong>Optimisation</strong> means finding the best solution given certain limits. "Best" might mean fastest, cheapest, safest, or most profitable. In computing, optimisation is important because resources such as time, money, fuel, and data are limited.</p>

<h2>The Minibus Problem</h2>
<p>Minibuses are a common form of transport in Zambia. A driver wants to pick up passengers at several points and reach the final destination. The route should be short enough to save fuel, fast enough to keep passengers happy, and safe enough to avoid dangerous roads. But the driver also wants to pick up enough passengers to make a profit.</p>

<h2>Factors to Consider</h2>
<ul>
<li><strong>Distance:</strong> A shorter route uses less fuel.</li>
<li><strong>Traffic:</strong> Some roads are busy at certain times.</li>
<li><strong>Road quality:</strong> Paved roads are faster and safer than muddy ones.</li>
<li><strong>Passenger demand:</strong> Some stops have more passengers in the morning or evening.</li>
<li><strong>Fare collection:</strong> More stops may mean more passengers but slower travel.</li>
</ul>

<h2>Worked Example: Route A versus Route B</h2>
<p>A minibus travels from Kalomo to a nearby trading centre. Two routes are possible:</p>
<ul>
<li><strong>Route A:</strong> 25 km on a good road, passes through two busy villages, takes 35 minutes.</li>
<li><strong>Route B:</strong> 18 km on a rough road, passes through one village, takes 40 minutes.</li>
</ul>
<p>Route A uses more fuel but is faster and carries more passengers. Route B is shorter but slower and may damage the vehicle. The best choice depends on whether the driver values time, fuel, or passenger numbers more.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Draw a simple map of your area with five important places.</li>
<li>Choose a starting point and an ending point.</li>
<li>List at least two possible routes.</li>
<li>For each route, list distance, likely time, and any problems.</li>
<li>Decide which route is better and explain why.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Optimisation:</strong> Finding the best solution within given limits.</li>
<li><strong>Constraint:</strong> A limit such as time, money, or fuel.</li>
<li><strong>Route:</strong> A path from one place to another.</li>
<li><strong>Trade-off:</strong> Giving up one benefit to gain another.</li>
</ul>

<h2>Summary</h2>
<p>Optimisation helps us make better decisions when resources are limited. A minibus route is a good example: distance, traffic, road quality, and passenger demand all affect the best choice. Computational thinking helps us weigh these factors and choose a route that balances speed, cost, and profit.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.khanacademy.org/computing/computer-science/algorithms">Khan Academy — Algorithms</a></li>
<li><a href="https://www.freecodecamp.org/news/optimization-algorithms/">freeCodeCamp — Optimization Algorithms</a></li>
<li><a href="https://www.w3schools.com/dsa/dsa_intro.php">W3Schools — Data Structures and Algorithms</a></li>
</ul>
HTML,
            ],
            [
                'title' => '6.3 Managing Market Stock with Simple Records',
                'duration_minutes' => 70,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to design a simple stock management system using lists and dictionaries, explain how recording sales helps a business, and write a basic Python program to track stock.</p>

<h2>Why Record Stock?</h2>
<p>A market stall owner who records stock can answer important questions: What is selling well? What is running low? What should I buy tomorrow? Without records, decisions are based on memory and guesswork, which can lead to lost sales or wasted stock.</p>

<h2>Simple Stock Record</h2>
<p>A simple stock record can be kept in a notebook with columns for item, quantity in stock, quantity sold, and price. A computer or phone can do the same thing faster and calculate totals automatically.</p>

<h2>Worked Example: Python Stock Tracker</h2>
<p>Here is a simple Python program that tracks stock for a small shop:</p>
<pre><code>stock = {
    "tomatoes": 30,
    "onions": 50,
    "cooking oil": 10,
    "soap": 25
}

# Sell some items
stock["tomatoes"] = stock["tomatoes"] - 5
stock["cooking oil"] = stock["cooking oil"] - 1

print("Current stock:")
for item, quantity in stock.items():
    print(item, ":", quantity)

# Find items running low
print("Low stock alert:")
for item, quantity in stock.items():
    if quantity &lt; 10:
        print(item, "needs restocking.")</code></pre>

<h2>Try It Yourself</h2>
<ol>
<li>Create a dictionary for a shop with five items and their quantities.</li>
<li>Write code that sells three of one item and two of another.</li>
<li>Print the updated stock.</li>
<li>Add code that warns when any item has fewer than five left.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Stock:</strong> The goods a business has available to sell.</li>
<li><strong>Inventory:</strong> A detailed list of stock.</li>
<li><strong>Restocking:</strong> Buying more items to replace what has been sold.</li>
<li><strong>Low stock alert:</strong> A warning that an item needs to be reordered.</li>
</ul>

<h2>Summary</h2>
<p>Recording stock helps businesses avoid shortages and waste. Lists and dictionaries in Python can model stock records and automate calculations. A simple stock tracker can alert owners when items are running low, helping them plan purchases and improve profits.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.w3schools.com/python/python_dictionaries.asp">W3Schools — Python Dictionaries</a></li>
<li><a href="https://www.python.org/about/gettingstarted/">Python.org — Getting Started</a></li>
<li><a href="https://www.freecodecamp.org/news/python-programming-for-beginners/">freeCodeCamp — Python for Beginners</a></li>
</ul>
HTML,
            ],
            [
                'title' => '6.4 Solving Real Problems with Code',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to identify real problems in Zambia that can be solved with simple programs, outline a solution using the skills from this course, and explain how technology can help communities.</p>

<h2>Technology for Real Problems</h2>
<p>Technology is most valuable when it solves real problems. In Zambia, simple programs and systems can help farmers track rainfall, shops manage stock, schools record attendance, and clinics schedule patients. You do not need to build a big app to make a difference. A well-designed spreadsheet, a simple Python script, or a clear database can save hours of work.</p>

<h2>Examples of Local Problems</h2>
<ul>
<li><strong>Farming:</strong> A farmer wants to know which crop variety gives the best yield. A simple record-keeping program can track planting dates, rainfall, and harvest amounts.</li>
<li><strong>Education:</strong> A teacher wants to calculate term grades quickly. A Python program can average test scores and print report summaries.</li>
<li><strong>Health:</strong> A clinic wants to remind patients of appointment dates. A simple database with phone numbers can help send SMS reminders.</li>
<li><strong>Business:</strong> A shop owner wants to know daily profit. A program can subtract costs from sales and show totals.</li>
</ul>

<h2>Worked Example: Rainfall Tracker</h2>
<p>A farmer in Southern Province wants to compare rainfall across months. A simple Python program could store monthly rainfall in a list and calculate the total and average:</p>
<pre><code>rainfall = [120, 95, 80, 45, 10, 0, 0, 5, 30, 90, 150, 140]
total = sum(rainfall)
average = total / len(rainfall)

print("Total rainfall:", total, "mm")
print("Average monthly rainfall:", average, "mm")

if total &lt; 600:
    print("Warning: rainfall is below average this year.")</code></pre>

<h2>Try It Yourself</h2>
<ol>
<li>Think of one problem in your community, school, or family.</li>
<li>Describe how decomposition, pattern recognition, abstraction, and algorithms apply.</li>
<li>Sketch a simple solution using Python, a spreadsheet, or a database.</li>
<li>Share your idea with a classmate and ask for feedback.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Real-world problem:</strong> A challenge faced by people in everyday life.</li>
<li><strong>Solution design:</strong> Planning how technology can solve a problem.</li>
<li><strong>Automation:</strong> Using technology to perform tasks automatically.</li>
<li><strong>Impact:</strong> The effect a solution has on people or a community.</li>
</ul>

<h2>Summary</h2>
<p>Computer science is not just about computers. It is about solving problems. By combining computational thinking, programming, data structures, and an understanding of systems, you can build tools that help real people in Zambia. Start small, focus on a clear problem, and keep improving your solution.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.python.org/about/gettingstarted/">Python.org — Getting Started</a></li>
<li><a href="https://www.freecodecamp.org/news/python-programming-for-beginners/">freeCodeCamp — Python for Beginners</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-programming">Khan Academy — Computer Programming</a></li>
<li><a href="https://www.w3schools.com/python/python_lists.asp">W3Schools — Python Lists</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module6Quiz(): array
    {
        return [
            'title' => 'Module 6 Quiz: Computational Thinking for Zambia',
            'description' => 'Test your understanding of computational thinking, optimisation, stock management, and solving real problems with code.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of these is one of the four main parts of computational thinking?',
                    'explanation' => 'The four parts are decomposition, pattern recognition, abstraction, and algorithms.',
                    'options' => [
                        ['text' => 'Memorisation', 'is_correct' => false],
                        ['text' => 'Decomposition', 'is_correct' => true],
                        ['text' => 'Typing speed', 'is_correct' => false],
                        ['text' => 'Drawing', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Optimisation means finding the best solution without considering any limits.',
                    'explanation' => 'Optimisation finds the best solution within given limits or constraints.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'A minibus driver choosing between a short rough road and a longer good road is making a:',
                    'explanation' => 'The driver is weighing benefits against drawbacks, which is a trade-off.',
                    'options' => [
                        ['text' => 'Database query', 'is_correct' => false],
                        ['text' => 'Trade-off', 'is_correct' => true],
                        ['text' => 'Binary search', 'is_correct' => false],
                        ['text' => 'Packet transfer', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What Python data structure pairs keys with values? (one word)',
                    'explanation' => 'A dictionary stores data as key-value pairs.',
                    'correct_answer' => 'dictionary',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a good reason to keep stock records in a shop?',
                    'explanation' => 'Stock records help owners know what to reorder and what is selling well.',
                    'options' => [
                        ['text' => 'To make the shop look modern', 'is_correct' => false],
                        ['text' => 'To avoid shortages and wasted stock', 'is_correct' => true],
                        ['text' => 'To increase the price of goods', 'is_correct' => false],
                        ['text' => 'To replace the shopkeeper', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Computational thinking can be used without writing any code.',
                    'explanation' => 'Computational thinking is a problem-solving approach that can be applied with or without a computer.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'When planning around load-shedding, ignoring entertainment to focus on essential tasks is an example of:',
                    'explanation' => 'Abstraction means focusing on what matters and ignoring unnecessary details.',
                    'options' => [
                        ['text' => 'Decomposition', 'is_correct' => false],
                        ['text' => 'Pattern recognition', 'is_correct' => false],
                        ['text' => 'Abstraction', 'is_correct' => true],
                        ['text' => 'Optimisation', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What word means finding similarities or trends in problems or data? (two words)',
                    'explanation' => 'Pattern recognition is the process of identifying similarities or trends.',
                    'correct_answer' => 'Pattern recognition',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of these is an example of a real-world problem that a simple program could help solve?',
                    'explanation' => 'A program can track planting dates, rainfall, and harvests to help farmers compare crop performance.',
                    'options' => [
                        ['text' => 'Choosing a favourite colour', 'is_correct' => false],
                        ['text' => 'Tracking crop yields for a farmer', 'is_correct' => true],
                        ['text' => 'Deciding what to wear', 'is_correct' => false],
                        ['text' => 'Cooking nshima', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In the rainfall tracker example, what does the variable total represent?',
                    'explanation' => 'The total is the sum of all monthly rainfall values in the list.',
                    'options' => [
                        ['text' => 'The number of months', 'is_correct' => false],
                        ['text' => 'The sum of monthly rainfall', 'is_correct' => true],
                        ['text' => 'The average rainfall', 'is_correct' => false],
                        ['text' => 'The highest monthly rainfall', 'is_correct' => false],
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
            'title' => 'Mid-Course Project: Design an Algorithm for a Local Service',
            'description' => 'Apply problem decomposition, flowcharts, and pseudocode to design a clear algorithm that solves a real problem in your community.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Choose a local service or process that could be improved with a clear algorithm. Examples include registering students at a college, serving customers at a mobile money booth, managing a minibus queue, or restocking a market stall.

Step 2: Write a short paragraph describing the problem and why it matters.

Step 3: Use problem decomposition to break the process into at least five clear steps.

Step 4: Draw a flowchart on paper or using a free online tool such as draw.io or Canva. Include at least one decision diamond and one process rectangle.

Step 5: Write the same algorithm in pseudocode.

Step 6: Submit your paragraph, flowchart as an image or PDF, and pseudocode as a text or Word document. Name your files clearly, for example: "Flowchart.jpg" and "Pseudocode.txt".
INSTRUCTIONS,
            'max_points' => 100,
            'passing_points' => 50,
            'due_date' => null,
            'allow_late_submission' => 1,
            'allowed_file_types' => 'pdf,doc,docx,jpg,png,txt',
            'max_file_size_mb' => 10,
        ]);

        Assignment::create([
            'course_id' => $this->courseId,
            'lesson_id' => null,
            'title' => 'End-of-Course Project: Build a Simple Python Tool',
            'description' => 'Write a small Python program that uses variables, decisions, loops, and lists or dictionaries to solve a real problem in Zambia.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Choose one of the following scenarios, or propose your own with approval from your instructor:
- A shop receipt calculator that asks for item prices and quantities, then prints a total.
- A simple grade checker that asks for exam marks and prints Pass or Resit.
- A stock tracker that stores item quantities and warns when stock is low.
- A rainfall or expense tracker that stores monthly values and calculates totals and averages.

Step 2: Write a short description of what your program does and who would use it.

Step 3: Write your Python program. It must include at least:
- One variable
- One input statement
- One if statement
- One loop
- One list or dictionary

Step 4: Test your program with at least three different sets of input. Take screenshots of the code and the output.

Step 5: Save your program as a .py file and submit it along with your screenshots and description as a PDF or ZIP file. Name the main file "my_project.py".
INSTRUCTIONS,
            'max_points' => 100,
            'passing_points' => 50,
            'due_date' => null,
            'allow_late_submission' => 1,
            'allowed_file_types' => 'pdf,doc,docx,py,zip,jpg,png',
            'max_file_size_mb' => 10,
        ]);
    }

    private function printSummary(): void
    {
        $moduleCount = Module::where('course_id', $this->courseId)->count();
        $lessonCount = Lesson::whereHas('module', function ($query) {
            $query->where('course_id', $this->courseId);
        })->count();
        $quizCount = Quiz::where('course_id', $this->courseId)->count();
        $questionCount = Quiz::where('course_id', $this->courseId)
            ->withCount('questions')
            ->get()
            ->sum('questions_count');
        $assignmentCount = Assignment::where('course_id', $this->courseId)->count();

        $this->command->newLine();
        $this->command->info('Computer Science General content seeded successfully.');
        $this->command->table(
            ['Course', 'Modules', 'Lessons', 'Quizzes', 'Questions', 'Assignments'],
            [['Certificate in Computer Science General', $moduleCount, $lessonCount, $quizCount, $questionCount, $assignmentCount]]
        );
    }
}
