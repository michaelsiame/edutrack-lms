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

class IoTContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Internet of Things')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Internet of Things" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in Internet of Things already has modules. Skipping content seed.');
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
                'title' => 'Module 1: Understanding IoT with Everyday Zambian Examples',
                'description' => 'Discover what the Internet of Things really means, how sensors and actuators work together, and why IoT matters for farms, shops, and homes across Zambia.',
            ],
            [
                'title' => 'Module 2: Building Your First IoT System — Hardware and Components',
                'description' => 'Learn about affordable microcontrollers, common sensors, and how to keep your IoT devices powered through load-shedding using batteries and solar panels.',
            ],
            [
                'title' => 'Module 3: Connecting IoT Devices to the Internet',
                'description' => 'Compare Wi-Fi, mobile data, and long-range radio options available through MTN, Airtel, and Zamtel, and build a simple gateway that forwards data to the cloud.',
            ],
            [
                'title' => 'Module 4: Reading Data and Making Decisions',
                'description' => 'Turn raw sensor readings into useful information, send alerts by SMS or WhatsApp, and create simple automations that control pumps, lights, or alarms.',
            ],
            [
                'title' => 'Module 5: IoT for Farms, Shops, and Homes in Zambia',
                'description' => 'Apply IoT to real Zambian contexts: smart irrigation for maize and vegetables, cold-storage monitoring, shop-stock alerts, and home security during load-shedding.',
            ],
            [
                'title' => 'Module 6: IoT Safety, Privacy, and Business Opportunities',
                'description' => 'Protect your devices and data, explore how to turn an IoT idea into a small business in Kalomo or Lusaka, and build a project plan you can start this month.',
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
                'title' => 'Assignment 1: Design a Simple IoT Monitoring System for a Local Farm or Business',
                'description' => 'Create a diagram and short report for an IoT system that solves a real problem for a farm, shop, or household in Zambia.',
                'instructions' => "<ol><li>Choose one real problem to solve: soil dryness in a maize field, tank water level at a school, shop-stock temperature during load-shedding, or home security at night.</li><li>Draw a simple system diagram showing at least three parts: the sensor/device, the connection method, and the place where the user sees the information.</li><li>List the hardware you would use (for example, Arduino, ESP32, soil-moisture sensor, solar panel, battery).</li><li>Explain how your system sends data to the user, using MTN/Airtel mobile data, Wi-Fi, or LoRa.</li><li>Describe one alert or automatic action the system would take (for example, send a WhatsApp message or turn on a pump).</li><li>Estimate the rough cost in ZMW and explain how it could save money or reduce risk.</li><li>Save your diagram and report as a PDF or Word document and upload it here. Name the file: IoTDesign_YourName.</li></ol>",
                'due_date' => now()->addWeeks(2),
            ],
            [
                'title' => 'Assignment 2: Build a Prototype Plan for a Smart Device Using Affordable Components Available in Zambia',
                'description' => 'Write a practical prototype plan for a smart device that can be built with components found in Lusaka, Kitwe, or from local online suppliers.',
                'instructions' => "<ol><li>Choose one smart device idea: a soil-moisture monitor, a solar water-pump controller, a cold-room temperature alarm, a security light that detects motion, or another idea approved by your instructor.</li><li>List every component you need, including the microcontroller, sensors, power supply, casing, and cables. Mention where you could buy each item locally or online.</li><li>Draw a wiring sketch or block diagram showing how the components connect.</li><li>Write the step-by-step plan for building and testing the prototype, including safety checks.</li><li>Explain how you would protect the device from dust, rain, and power surges during ZESCO load-shedding.</li><li>Describe how you would share the data with the user and what action the user could take.</li><li>Save your plan as a PDF or Word document and upload it here. Name the file: IoTPrototype_YourName.</li></ol>",
                'due_date' => now()->addWeeks(4),
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
                'title' => 'Lesson 1.1: What Is IoT and Why It Matters for Your Farm or Shop',
                'duration_minutes' => 60,
                'content' => $this->lesson1_1(),
            ],
            [
                'title' => 'Lesson 1.2: Sensors, Actuators, and the Internet Connection',
                'duration_minutes' => 75,
                'content' => $this->lesson1_2(),
            ],
            [
                'title' => 'Lesson 1.3: IoT in Zambia — Load-Shedding, Mobile Money, and Solar Power',
                'duration_minutes' => 60,
                'content' => $this->lesson1_3(),
            ],
            [
                'title' => 'Module 1 Quiz: Understanding IoT with Everyday Zambian Examples',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Complete this quiz to check your understanding of IoT basics, sensors and actuators, and why IoT is useful for Zambian farms, shops, and homes. You need 60% to pass. Good luck!</p>',
            ],
        ];
    }

    private function lesson1_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to explain what the Internet of Things (IoT) means in plain language, give examples of IoT devices that are already around you, and describe how IoT can help a small business or farm in Zambia save time, money, and water.</p>

<figure><img class="lesson-diagram" src="/assets/diagrams/internet-of-things/iot-layers-stack.svg" alt="IoT layers diagram showing physical sensors at the bottom, network connectivity, cloud platform, and mobile application at the top."><figcaption>Figure: IoT layers from sensor to phone.</figcaption></figure>

<h2>What Is the Internet of Things?</h2>
<p>The Internet of Things, usually called IoT, is a simple idea with a complicated name. It means everyday objects — things — that can sense information, connect to the internet, and share that information with people or with other machines. A "thing" can be a water tank, a maize field, a refrigerator, a security gate, or even a cow collar.</p>
<p>Think about your smartphone. It knows where you are, how bright the room is, and whether you are moving. It connects to the internet and sends that information to apps. An IoT device is like a very small, specialised smartphone that does one job very well, such as measuring soil moisture or counting how many times a door opens.</p>
<p>There are four basic parts to almost every IoT system. First, there is a <strong>sensor</strong> or device that collects data. Second, there is a <strong>connection</strong> such as Wi-Fi, mobile data, or a long-range radio signal. Third, there is a <strong>cloud platform</strong> that receives, stores, and makes sense of the data. Fourth, there is an <strong>application</strong> — usually an app, website, SMS, or WhatsApp message — where a human sees the information and decides what to do.</p>

<h2>Examples Already Around You</h2>
<p>IoT is not science fiction. If you have ever used a bank ATM, a prepaid electricity meter, or a phone-based car-tracking service, you have already used IoT. In Zambia, many boreholes now have remote monitoring so that water companies know when a pump breaks before villagers call. Some large farms use soil sensors to decide when to irrigate. Cold-chain companies use temperature sensors to make sure medicines and frozen chicken do not spoil on the road from Lusaka to Kitwe.</p>
<p>On a smaller scale, a shop owner in Kalomo can use a simple device to check whether the freezer is still cold during load-shedding. A poultry farmer can receive an SMS when the temperature in a chick brooder rises too high. A security guard can get a WhatsApp alert when someone opens a gate at night. These are all IoT.</p>

<h2>Why IoT Matters for Zambia</h2>
<p>Zambia faces challenges that IoT can help solve. Load-shedding damages appliances and spoils stock. Water is scarce in many areas, so irrigation must be efficient. Farms are spread over large areas, making it hard for one person to check everything every day. Mobile money has already proven that Zambians are ready to use technology for daily business. IoT builds on that same comfort with phones and mobile networks.</p>
<p>For a small farm, IoT can reduce waste. Instead of guessing whether maize needs water, a sensor tells you the exact soil moisture. Instead of driving to check a water tank, you look at your phone. For a shop, IoT can protect stock. A temperature sensor in a freezer can send an alert the moment power goes off, giving the owner time to move goods or start a generator. For a household, a simple solar battery monitor can show whether there is enough charge to run lights and a phone overnight.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Walk around your home, farm, or workplace and list five "things" that could tell you useful information if they were connected.</li>
<li>For each item, write down what data it would produce (for example, temperature, water level, open/closed, movement).</li>
<li>Pick the one that would save you the most time or money and explain why in one paragraph.</li>
<li>Ask a friend or family member which item on your list they would find most useful.</li>
<li>Draw a simple diagram showing the sensor, the connection, the phone, and the person who takes action.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Internet of Things (IoT)</strong>: Physical objects that collect data and exchange it over the internet.</li>
<li><strong>Sensor</strong>: A component that measures something in the physical world, such as temperature, moisture, light, or movement.</li>
<li><strong>Actuator</strong>: A component that physically does something, such as turning a motor, opening a valve, or switching a light.</li>
<li><strong>Cloud platform</strong>: A remote service that stores and processes data from devices.</li>
<li><strong>Gateway</strong>: A device that receives local sensor data and forwards it to the internet.</li>
</ul>

<h2>Summary</h2>
<p>IoT connects ordinary objects to the internet so they can share useful information. The idea is already present in banking, agriculture, and security across Zambia. By combining sensors, connectivity, cloud platforms, and phone apps, even a small farm or shop can monitor conditions, reduce waste, and respond faster to problems.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.ibm.com/topics/internet-of-things" target="_blank" rel="noopener">IBM — What is the Internet of Things?</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-science/internet-intro" target="_blank" rel="noopener">Khan Academy — Internet Intro</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/modules/intro-to-iot/" target="_blank" rel="noopener">Microsoft Learn — Introduction to IoT</a></li>
</ul>
HTML;
    }

    private function lesson1_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to explain the difference between a sensor and an actuator, name common examples of each, and describe how an IoT device connects to the internet using Wi-Fi, mobile data, or long-range radio.</p>

<h2>Sensors: The Eyes and Ears of IoT</h2>
<p>A sensor is any device that detects a physical condition and turns it into an electrical signal. That signal can then be read by a small computer called a microcontroller. Sensors are the reason IoT devices can "see" the world. Without sensors, a device would have no information to share.</p>
<p>Common sensors used in Zambian IoT projects include soil-moisture sensors for irrigation, temperature and humidity sensors for chicken brooders and cold rooms, water-level sensors for tanks and boreholes, motion sensors for security lights, and light sensors for greenhouses. There are also more advanced sensors such as GPS trackers for vehicles, gas sensors for mines, and camera modules for security.</p>
<p>Most sensors give an output that changes with the thing they measure. For example, a soil-moisture sensor might give a low number when the soil is dry and a high number when it is wet. A temperature sensor gives a number that rises or falls with degrees Celsius. The microcontroller reads these numbers and decides what to do next.</p>

<h2>Actuators: The Hands and Feet of IoT</h2>
<p>If sensors collect information, <strong>actuators</strong> take action. An actuator is anything that moves or changes something in the physical world when it receives a command. Without actuators, IoT would only be able to tell you what is happening; it could not do anything about it.</p>
<p>A simple actuator is a relay, which is an electrically operated switch. A relay can turn a water pump on or off, open a gate, or switch on a security light. A motor can move a valve or adjust a solar panel. A buzzer can sound an alarm. Even a small fan inside a brooder is an actuator if it is controlled automatically.</p>
<p>Many useful IoT projects combine one sensor and one actuator. A soil-moisture sensor plus a relay-controlled water pump makes a smart irrigation system. A temperature sensor plus a fan makes a smart cooling system. A motion sensor plus a light and buzzer makes a security alarm.</p>

<h2>The Internet Connection</h2>
<p>For an IoT device to be useful, it usually needs to send its data somewhere. The three most common ways to do this in Zambia are Wi-Fi, mobile data, and LoRa.</p>
<p><strong>Wi-Fi</strong> is best when the device is near a building with a reliable router. It is cheap and fast, but it does not work far from the building. A shop owner monitoring a freezer inside the shop could use Wi-Fi. A farm sensor two kilometres away could not.</p>
<p><strong>Mobile data</strong> uses the same networks that carry your phone calls and internet: MTN, Airtel, and Zamtel. An IoT device with a small SIM card can send data from almost anywhere with network coverage. This is ideal for farms, boreholes, and vehicles. The cost depends on how much data the device sends. A simple sensor sending one small message every hour can run on a very cheap data bundle.</p>
<p><strong>LoRa</strong> stands for Long Range. It is a low-power radio technology that can send small messages over several kilometres without using mobile data. A LoRa sensor in a field sends data to a nearby gateway, which then forwards it to the internet. LoRa is excellent for rural farms because the sensor battery can last for years and there are no monthly SIM costs.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose one problem: a dry garden, a hot room, a gate that should not open at night, or a tank that runs empty.</li>
<li>Write down one sensor that would detect the problem.</li>
<li>Write down one actuator that could fix or warn about the problem.</li>
<li>Choose the best connection method for your location: Wi-Fi, mobile data, or LoRa. Explain why.</li>
<li>Draw a simple sketch showing the sensor, the microcontroller, the connection, and the actuator.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Sensor</strong>: A device that measures a physical condition and converts it into an electrical signal.</li>
<li><strong>Actuator</strong>: A device that moves or controls a mechanism when it receives a command.</li>
<li><strong>Relay</strong>: An electrically operated switch used to turn high-power devices on and off.</li>
<li><strong>Microcontroller</strong>: A small computer on a single chip that reads sensors and controls actuators.</li>
<li><strong>LoRa</strong>: A long-range, low-power wireless technology useful for rural IoT projects.</li>
</ul>

<h2>Summary</h2>
<p>Every IoT system needs a way to sense the world, a way to act on information, and a way to connect to the internet. Sensors measure conditions, actuators make things happen, and Wi-Fi, mobile data, or LoRa carry the data. Choosing the right combination is the first step in building a useful project.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.sparkfun.com/tutorials/sensors-and-actuators" target="_blank" rel="noopener">SparkFun — Sensors and Actuators</a></li>
<li><a href="https://www.arduino.cc/en/Guide/Introduction" target="_blank" rel="noopener">Arduino — Introduction</a></li>
<li><a href="https://lora-alliance.org/about-lorawan/" target="_blank" rel="noopener">LoRa Alliance — About LoRaWAN</a></li>
</ul>
HTML;
    }

    private function lesson1_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will understand how IoT fits into daily life in Zambia, why solar power and battery backup are important, and how mobile money networks make it easier to pay for data and maintain remote devices.</p>

<h2>IoT and Load-Shedding</h2>
<p>Load-shedding is a reality for almost every Zambian business and household. When ZESCO power goes off, many devices stop working. This is a serious problem for IoT because the device, the router, and the gateway may all lose power at the same time. A freezer monitor that only works when the main power is on is useless exactly when you need it.</p>
<p>The solution is to design for unreliable power from the start. A good IoT device uses very little electricity. It can run on a small battery that charges from a solar panel during the day. The gateway that forwards data can also have its own battery backup. Some devices are programmed to sleep most of the time and only wake up to take a reading and send it. This saves power and keeps the system working through the night.</p>
<p>Solar panels are now affordable in Zambia. A small 10-watt panel with a 12-volt battery can power a microcontroller, a sensor, and a mobile data module for many days. For a rural borehole or farm sensor, this is often the only practical power source.</p>

<h2>Mobile Money and IoT Data Costs</h2>
<p>IoT devices that use mobile data need a way to pay for that data. In Zambia, mobile money is the easiest way for most people to buy small data bundles. A farmer in Choma can buy an Airtel Money or MTN MoMo bundle, load it onto the SIM in the device, and keep the sensor sending data. There is no need for a bank account or a long contract.</p>
<p>Mobile money is also useful when an IoT system sends alerts. A shop owner might receive an SMS warning that the freezer temperature is rising. The cost of that SMS is tiny compared with the value of the frozen stock it protects. Some platforms can even send WhatsApp messages, which cost nothing if there is an internet connection.</p>
<p>When designing an IoT project, think about who will pay for the SIM and data. Will it be the farm owner, the shop owner, or a service company? How will they top up? A system that relies on someone in Lusaka to pay a monthly bill may fail if the money does not arrive on time.</p>

<h2>Local Examples of IoT Value</h2>
<p>Imagine a small irrigation scheme near Kalomo. Maize and vegetables are planted on several hectares. Instead of paying someone to walk the field twice a day checking soil moisture, the cooperative installs soil sensors. The sensors send readings every hour to a simple phone app. When moisture drops below a set level, the app sends a message to the pump operator. Water is used only when needed, diesel for the pump is saved, and yields improve because crops are never too dry for long.</p>
<p>Or imagine a shop in Lusaka that sells frozen fish. The owner installs a temperature sensor in the freezer and a small battery backup. During load-shedding, the sensor continues to work and sends an alert if the temperature rises. The owner can move the fish to another freezer or buy ice before anything spoils. The sensor pays for itself by preventing one stock loss.</p>

<table>
<tr><th>Challenge</th><th>IoT Solution</th><th>Benefit</th></tr>
<tr><td>Load-shedding</td><td>Solar panel + battery backup</td><td>Device stays online 24 hours</td></tr>
<tr><td>Dry farmland</td><td>Soil-moisture sensor + pump alert</td><td>Water saved, crop yield improved</td></tr>
<tr><td>Freezer failure</td><td>Temperature sensor + SMS alert</td><td>Stock saved before spoilage</td></tr>
<tr><td>Remote water tank</td><td>Water-level sensor + mobile data</td><td>No need to travel to check</td></tr>
<tr><td>Night security</td><td>Motion sensor + light + WhatsApp alert</td><td>Faster response to intruders</td></tr>
</table>

<h2>Try It Yourself</h2>
<ol>
<li>Think of one place where you experience load-shedding. What device would you want to keep monitoring during a power cut?</li>
<li>Calculate the rough cost of a small solar panel and battery in your area. Would it be enough to power a sensor that uses less power than a phone charger?</li>
<li>List three ways mobile money could help you pay for IoT data or receive alerts.</li>
<li>Find one local business or farm that loses money because of a problem that a sensor could detect. Describe the sensor and the alert.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Load-shedding</strong>: Planned power cuts used to manage electricity demand on the national grid.</li>
<li><strong>Battery backup</strong>: A rechargeable battery that keeps a device running when mains power is off.</li>
<li><strong>Mobile data</strong>: Internet access provided through a cellular network such as MTN, Airtel, or Zamtel.</li>
<li><strong>Data bundle</strong>: A prepaid amount of mobile data sold by a network provider.</li>
<li><strong>Remote monitoring</strong>: Checking the status of equipment or conditions from a distance, usually via a phone or computer.</li>
</ul>

<h2>Summary</h2>
<p>IoT in Zambia must be designed around real conditions: load-shedding, solar power, mobile money, and often long distances. Devices that sleep to save power, use solar and battery backup, and send alerts through SMS or WhatsApp are practical and valuable. The best IoT projects solve a clear local problem and keep running even when the grid does not.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.zesco.co.zm/" target="_blank" rel="noopener">ZESCO — Understanding Your Power Supply</a></li>
<li><a href="https://www.mtn.zm/" target="_blank" rel="noopener">MTN Zambia</a></li>
<li><a href="https://www.airtel.co.zm/" target="_blank" rel="noopener">Airtel Zambia</a></li>
</ul>
HTML;
    }

    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: Understanding IoT with Everyday Zambian Examples',
            'description' => 'Test your understanding of IoT basics, sensors, actuators, and how IoT applies to farms and shops in Zambia.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following best describes the Internet of Things (IoT)?',
                    'explanation' => 'IoT refers to physical objects that collect data and exchange it over the internet.',
                    'options' => [
                        ['text' => 'A type of social media app', 'is_correct' => false],
                        ['text' => 'Physical objects that sense data and share it over the internet', 'is_correct' => true],
                        ['text' => 'A government website for paying bills', 'is_correct' => false],
                        ['text' => 'A brand of computer virus protection', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main job of a sensor in an IoT system?',
                    'explanation' => 'A sensor measures a physical condition such as temperature, moisture, or movement and converts it into a signal.',
                    'options' => [
                        ['text' => 'To move a motor or switch a light', 'is_correct' => false],
                        ['text' => 'To store data on the internet', 'is_correct' => false],
                        ['text' => 'To measure a physical condition and create an electrical signal', 'is_correct' => true],
                        ['text' => 'To charge the battery using solar power', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which connection method is usually best for a farm sensor located two kilometres from the nearest building?',
                    'explanation' => 'Mobile data or LoRa work over long distances, while Wi-Fi is limited to the range of a router.',
                    'options' => [
                        ['text' => 'Wi-Fi', 'is_correct' => false],
                        ['text' => 'Bluetooth', 'is_correct' => false],
                        ['text' => 'Mobile data or LoRa', 'is_correct' => true],
                        ['text' => 'Infrared remote control', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is solar power often important for IoT projects in Zambia?',
                    'explanation' => 'Solar power with battery backup keeps devices running during load-shedding and in off-grid rural areas.',
                    'options' => [
                        ['text' => 'It makes the device connect faster to the internet', 'is_correct' => false],
                        ['text' => 'It keeps the device running when ZESCO power is off', 'is_correct' => true],
                        ['text' => 'It replaces the need for a microcontroller', 'is_correct' => false],
                        ['text' => 'It stores data directly from sensors', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'An actuator is the part of an IoT system that measures temperature or soil moisture.',
                    'explanation' => 'Sensors measure conditions. Actuators take physical action, such as switching a pump or sounding an alarm.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Mobile money makes it easier to buy small data bundles for an IoT device without a bank account.',
                    'explanation' => 'In Zambia, MTN MoMo and Airtel Money allow people to buy data bundles using only a mobile phone.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What do we call a device that receives local sensor data and forwards it to the internet? (One word)',
                    'explanation' => 'A gateway collects data from local sensors and sends it to the internet or cloud platform.',
                    'correct_answer' => 'Gateway',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for planned power cuts used to manage electricity demand in Zambia? (Two words)',
                    'explanation' => 'Load-shedding is the planned switching off of electricity to balance supply and demand.',
                    'correct_answer' => 'Load shedding',
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
                'title' => 'Lesson 2.1: Affordable Microcontrollers Available in Zambia',
                'duration_minutes' => 75,
                'content' => $this->lesson2_1(),
            ],
            [
                'title' => 'Lesson 2.2: Sensors for Soil, Water, Temperature, and Security',
                'duration_minutes' => 60,
                'content' => $this->lesson2_2(),
            ],
            [
                'title' => 'Lesson 2.3: Powering Your Device with Solar and Battery Backup',
                'duration_minutes' => 75,
                'content' => $this->lesson2_3(),
            ],
            [
                'title' => 'Module 2 Quiz: Building Your First IoT System',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your knowledge of microcontrollers, sensors, actuators, and power systems for IoT devices in Zambia. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson2_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to compare popular microcontrollers such as Arduino and ESP32, understand why the ESP32 is especially useful for IoT in Zambia, and know where to look for components locally or online.</p>

<h2>What Is a Microcontroller?</h2>
<p>A microcontroller is a very small computer on a single chip. It can read inputs from sensors, run a simple program, and control outputs such as motors, lights, or relays. Unlike a laptop or smartphone, a microcontroller does not have a screen or keyboard. It is designed to do one job reliably, cheaply, and with very little power.</p>
<p>Think of a microcontroller as the brain of your IoT device. The sensor is the eye, the actuator is the hand, and the microcontroller decides what the hand should do based on what the eye sees. For example, if a soil-moisture sensor reports dry soil, the microcontroller can turn on a pump.</p>

<h2>Arduino: The Beginner's Friend</h2>
<p>Arduino is one of the most popular microcontroller platforms in the world. It is easy to learn, has a huge community, and there are many tutorials online. The most common board is the Arduino Uno. It has pins where you connect sensors and actuators, and you write programs on a computer and upload them through a USB cable.</p>
<p>Arduino is excellent for learning and for projects that stay in one place. However, most Arduino boards do not have built-in Wi-Fi or mobile data. If you want to send data to the internet, you usually need to add an extra module, which increases cost and complexity. For a simple farm project in a remote area, Arduino alone is usually not enough.</p>

<h2>ESP32: Built for IoT</h2>
<p>The ESP32 is a microcontroller that has built-in Wi-Fi and Bluetooth. Some versions also support mobile data. It is slightly more advanced than Arduino, but it is very popular for IoT because it can connect directly to the internet without extra modules.</p>
<p>For Zambia, the ESP32 is often the best choice. A small shop or home can use its Wi-Fi to send freezer or security data. A farm can add a mobile data module or use the ESP32 with a LoRa chip to send sensor readings over long distances. The ESP32 is also cheap. In many shops in Lusaka and Kitwe, or through online suppliers, an ESP32 board costs less than a good meal in a restaurant.</p>

<h2>Other Options</h2>
<p>For more complex projects, some people use a <strong>Raspberry Pi</strong>. This is a small Linux computer, not just a microcontroller. It can run a full operating system, show video, and handle more data. A Raspberry Pi is useful as a gateway or as a local server, but it uses more power and costs more than an ESP32.</p>
<p>For very low-power, long-range projects, there are special boards such as the Arduino MKR series with LoRa. These are good when a sensor must run for years on a small battery and send data over several kilometres.</p>

<table>
<tr><th>Board</th><th>Best For</th><th>Connectivity</th><th>Power Use</th></tr>
<tr><td>Arduino Uno</td><td>Learning, simple local projects</td><td>USB only; needs add-ons</td><td>Low</td></tr>
<tr><td>ESP32</td><td>Wi-Fi IoT projects</td><td>Built-in Wi-Fi and Bluetooth</td><td>Low to medium</td></tr>
<tr><td>ESP32 with SIM</td><td>Remote farms, vehicles</td><td>Mobile data</td><td>Medium</td></tr>
<tr><td>Raspberry Pi</td><td>Gateway, local server, camera</td><td>Wi-Fi / Ethernet</td><td>Higher</td></tr>
<tr><td>LoRa node</td><td>Long-range rural sensors</td><td>LoRa to gateway</td><td>Very low</td></tr>
</table>

<h2>Try It Yourself</h2>
<ol>
<li>Search online for "ESP32 price in Zambia" or ask at an electronics shop in your town.</li>
<li>List three projects where Arduino would be enough and three where ESP32 would be better.</li>
<li>Write down the names of two local or online shops where you could buy a microcontroller.</li>
<li>Watch one beginner video about Arduino or ESP32 and write three new facts you learned.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Microcontroller</strong>: A small computer on a chip that reads inputs and controls outputs.</li>
<li><strong>Arduino</strong>: A popular open-source microcontroller platform for learning and prototyping.</li>
<li><strong>ESP32</strong>: A low-cost microcontroller with built-in Wi-Fi and Bluetooth, popular for IoT.</li>
<li><strong>Raspberry Pi</strong>: A small, full-featured computer often used as a gateway or server.</li>
<li><strong>GPIO pins</strong>: Physical pins on a board that connect to sensors, buttons, and actuators.</li>
</ul>

<h2>Summary</h2>
<p>Choosing the right microcontroller depends on what you need. Arduino is great for learning simple projects. ESP32 is usually the best choice for internet-connected IoT in Zambia because it has Wi-Fi built in and is affordable. For remote farms, add mobile data or LoRa. For gateways and servers, consider a Raspberry Pi.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.arduino.cc/en/Guide/Introduction" target="_blank" rel="noopener">Arduino — Getting Started</a></li>
<li><a href="https://docs.espressif.com/projects/esp-idf/en/latest/esp32/get-started/" target="_blank" rel="noopener">Espressif — ESP32 Getting Started</a></li>
<li><a href="https://www.raspberrypi.org/documentation/" target="_blank" rel="noopener">Raspberry Pi Documentation</a></li>
</ul>
HTML;
    }

    private function lesson2_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to name common sensors used in Zambian IoT projects, explain what each one measures, and choose the right sensor for a farm, shop, or home application.</p>

<h2>Soil-Moisture Sensors</h2>
<p>A soil-moisture sensor measures how much water is in the soil. It usually has two metal probes that push into the ground. When the soil is dry, electricity passes less easily between the probes and the sensor gives a low reading. When the soil is wet, electricity passes more easily and the reading is high.</p>
<p>These sensors are very useful for maize, vegetables, and fruit trees. Instead of guessing when to irrigate, the farmer knows exactly when the soil is dry. This saves water, fuel for pumps, and labour. For best results, place the sensor near the roots of the plant, not right at the surface, and protect the electronic part from rain.</p>

<h2>Temperature and Humidity Sensors</h2>
<p>The DHT11 and DHT22 are popular sensors that measure both temperature and humidity. They are cheap and easy to use with Arduino or ESP32. They are ideal for chicken brooders, greenhouses, cold rooms, and server cabinets.</p>
<p>In a poultry business, young chicks die quickly if the temperature is wrong. A DHT22 sensor can send an alert if the brooder gets too hot or too cold. In a vegetable shop, the same sensor can warn if the storage room becomes too humid and vegetables start to rot. For accurate readings, keep the sensor out of direct sunlight and away from heat sources such as motors or lights.</p>

<h2>Water-Level Sensors</h2>
<p>Water-level sensors tell you how much water is in a tank, drum, or reservoir. There are several types. A float switch turns on or off when the water reaches a certain level. An ultrasonic sensor measures the distance from the top of the tank down to the water surface. A pressure sensor measures the weight of the water column.</p>
<p>For a school or clinic that relies on a rooftop tank, an ultrasonic sensor can send a daily water level to the caretaker's phone. If the level drops suddenly, there may be a leak. If it stays full, the pump can be switched off to save electricity. In rural areas, this saves long walks to check tanks.</p>

<h2>Motion, Light, and Security Sensors</h2>
<p>A PIR motion sensor detects movement by sensing infrared heat. It is often used to turn on security lights or send alerts when someone enters an area. A light sensor measures brightness and can automatically turn on lights at sunset. A door sensor, also called a reed switch, tells you whether a gate or door is open or closed.</p>
<p>These sensors are affordable and practical for small businesses and homes. A shop in Kitwe can use a door sensor to receive a message if the back door opens after closing time. A farmer can use a motion sensor near a storage shed to scare away thieves with a light and buzzer.</p>

<h2>Other Useful Sensors</h2>
<ul>
<li><strong>Rain sensor</strong>: Detects rainfall and can delay irrigation automatically.</li>
<li><strong>Gas sensor</strong>: Detects smoke, LPG leaks, or harmful gases in homes and workshops.</li>
<li><strong>Current sensor</strong>: Measures how much electricity a device is using.</li>
<li><strong>GPS module</strong>: Tracks the location of vehicles or animals.</li>
<li><strong>Camera module</strong>: Captures images for security or crop monitoring.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Choose one problem at home, on a farm, or in a shop.</li>
<li>List at least two sensors that could help detect or measure the problem.</li>
<li>For each sensor, write down the exact thing it measures and the unit (for example, degrees Celsius, percent moisture, centimetres).</li>
<li>Search online for the price of one of these sensors and note whether it is available locally.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Soil-moisture sensor</strong>: A sensor that measures the amount of water in soil.</li>
<li><strong>Temperature sensor</strong>: A sensor that measures how hot or cold something is.</li>
<li><strong>Humidity sensor</strong>: A sensor that measures the amount of moisture in the air.</li>
<li><strong>Ultrasonic sensor</strong>: A sensor that measures distance using sound waves.</li>
<li><strong>PIR sensor</strong>: A passive infrared sensor that detects movement by sensing body heat.</li>
</ul>

<h2>Summary</h2>
<p>Sensors are the foundation of any IoT project. Soil-moisture sensors help farmers irrigate wisely. Temperature and humidity sensors protect animals, food, and medicine. Water-level sensors reduce wasted journeys. Motion and door sensors improve security. Choosing the right sensor for the job is the first step toward a reliable system.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.sparkfun.com/tutorials/soil-moisture-sensor-hookup-guide" target="_blank" rel="noopener">SparkFun — Soil Moisture Sensor</a></li>
<li><a href="https://www.circuitbasics.com/how-to-set-up-the-dht11-humidity-sensor-on-an-arduino/" target="_blank" rel="noopener">Circuit Basics — DHT11 Humidity Sensor</a></li>
<li><a href="https://lastminuteengineers.com/pir-sensor-arduino-tutorial/" target="_blank" rel="noopener">Last Minute Engineers — PIR Sensor Tutorial</a></li>
</ul>
HTML;
    }

    private function lesson2_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will understand how to power an IoT device using mains electricity, batteries, and solar panels, and you will be able to design a simple power system that survives Zambian load-shedding.</p>

<h2>Why Power Matters</h2>
<p>An IoT device is only useful if it stays on. In Zambia, where load-shedding can last several hours, power design is not an afterthought. It is one of the most important parts of the project. A device that stops working when the power goes off is not really a smart device.</p>
<p>The good news is that most microcontrollers and sensors use very little electricity. An Arduino Uno can run on less than half a watt. An ESP32 uses a little more when sending Wi-Fi data, but still far less than a light bulb. This means that even a small solar panel and battery can keep a device running for days.</p>

<h2>Mains Power with a USB Adapter</h2>
<p>The simplest way to power a microcontroller is with a USB phone charger. A 5-volt, 1-amp charger is enough for most Arduino and ESP32 projects. You can plug the USB cable into the board and the charger into a wall socket. This works well for projects inside a shop or home where mains power is usually available.</p>
<p>The problem is load-shedding. When ZESCO cuts power, the USB charger stops. If your project is indoors and only needs to work during the day, this may be fine. But for monitoring a freezer, a security gate, or a water tank, you need a backup.</p>

<h2>Battery Backup</h2>
<p>A battery backup keeps the device running when mains power fails. The most common setup uses a rechargeable lithium battery or a 12-volt lead-acid battery. A charging module connects the solar panel or mains adapter to the battery, and the battery powers the microcontroller.</p>
<p>For small projects, a 3.7-volt lithium battery with a charging module is enough. The module charges the battery when power is available and the battery keeps the device running when it is not. For larger projects, such as a gateway with a Raspberry Pi, a 12-volt battery is better because it stores more energy.</p>

<h2>Solar Power</h2>
<p>Solar power is the best choice for most rural IoT projects in Zambia. The country has plenty of sunshine, and solar panels have become affordable. A small 10-watt solar panel can charge a 12-volt battery during the day. That battery can then power a microcontroller, a sensor, and a mobile data module through the night.</p>
<p>When designing a solar system, you need to know three things: how much power the device uses, how many hours of sunlight you get, and how many cloudy days you want to survive. A simple weather station might use 1 watt continuously. Over 24 hours, that is 24 watt-hours. A 10-watt panel in good sun produces about 50 watt-hours per day, so it can easily keep up.</p>

<h2>Power-Saving Tips</h2>
<ul>
<li><strong>Sleep mode</strong>: Program the microcontroller to sleep between readings. It wakes up, measures, sends data, and sleeps again.</li>
<li><strong>Reduce data</strong>: Send readings less often. A soil sensor only needs to report every hour, not every second.</li>
<li><strong>Turn off radios</strong>: Switch off the Wi-Fi or mobile module between transmissions to save power.</li>
<li><strong>Use efficient regulators</strong>: Choose voltage regulators that do not waste energy as heat.</li>
<li><strong>Size the battery</strong>: Make sure the battery can run the device for at least two cloudy days.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Find the power rating on a USB charger in your home or shop. It is usually printed as "Output: 5V 1A" or similar.</li>
<li>Calculate how many watts the charger can provide. Multiply volts by amps (for example, 5V × 1A = 5W).</li>
<li>Estimate how long your device could run on a 12V 7Ah battery if it uses 1 watt. Hint: watts = volts × amps.</li>
<li>Look up the price of a small solar panel and battery in your area. Could you afford one for a farm sensor?</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Watt</strong>: A unit of power that shows how fast electricity is being used.</li>
<li><strong>Watt-hour</strong>: A unit of energy that shows how much electricity is used over time.</li>
<li><strong>Voltage regulator</strong>: A component that keeps the voltage steady for the microcontroller.</li>
<li><strong>Sleep mode</strong>: A low-power state where the microcontroller pauses most operations.</li>
<li><strong>Deep-cycle battery</strong>: A battery designed to be discharged and recharged many times.</li>
</ul>

<h2>Summary</h2>
<p>Reliable power is essential for IoT in Zambia. Mains USB adapters are fine indoors, but battery backup is needed for load-shedding. Solar panels are ideal for rural and off-grid projects. By using sleep modes, reducing data transmission, and sizing the battery correctly, you can build a device that works around the clock, even when the grid fails.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.sparkfun.com/tutorials/voltage-regulators" target="_blank" rel="noopener">SparkFun — Voltage Regulators</a></li>
<li><a href="https://docs.espreso.io/en/latest/api-reference/system/sleep_modes.html" target="_blank" rel="noopener">ESP-IDF — Sleep Modes</a></li>
<li><a href="https://www.energy.gov/eere/solar/solar-energy-technologies-office" target="_blank" rel="noopener">U.S. DOE — Solar Energy Basics</a></li>
</ul>
HTML;
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Building Your First IoT System',
            'description' => 'Assess your understanding of microcontrollers, sensors, actuators, and power systems.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which microcontroller has built-in Wi-Fi and is popular for low-cost IoT projects?',
                    'explanation' => 'The ESP32 includes built-in Wi-Fi and Bluetooth, making it ideal for internet-connected projects.',
                    'options' => [
                        ['text' => 'Arduino Uno', 'is_correct' => false],
                        ['text' => 'Raspberry Pi 5', 'is_correct' => false],
                        ['text' => 'ESP32', 'is_correct' => true],
                        ['text' => 'Intel Core i7', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which sensor would be most useful for automatically watering a maize field?',
                    'explanation' => 'A soil-moisture sensor measures how much water is in the soil and can trigger irrigation when needed.',
                    'options' => [
                        ['text' => 'PIR motion sensor', 'is_correct' => false],
                        ['text' => 'Soil-moisture sensor', 'is_correct' => true],
                        ['text' => 'Light sensor', 'is_correct' => false],
                        ['text' => 'GPS module', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does an actuator do in an IoT system?',
                    'explanation' => 'An actuator takes physical action, such as switching a pump, moving a motor, or sounding a buzzer.',
                    'options' => [
                        ['text' => 'Measures temperature', 'is_correct' => false],
                        ['text' => 'Stores data in the cloud', 'is_correct' => false],
                        ['text' => 'Takes physical action based on commands', 'is_correct' => true],
                        ['text' => 'Displays video on a screen', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is a battery backup important for an IoT device in Zambia?',
                    'explanation' => 'Battery backup keeps the device running during load-shedding when mains power is off.',
                    'options' => [
                        ['text' => 'It makes the device connect to satellites', 'is_correct' => false],
                        ['text' => 'It keeps the device running during power cuts', 'is_correct' => true],
                        ['text' => 'It replaces the microcontroller', 'is_correct' => false],
                        ['text' => 'It increases the internet speed', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A Raspberry Pi is a microcontroller like an Arduino Uno.',
                    'explanation' => 'A Raspberry Pi is a full computer that runs an operating system, while an Arduino is a simpler microcontroller.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Sending sensor data less often can help a solar-powered IoT device last longer.',
                    'explanation' => 'Reducing how often the device transmits data saves power, which is important for solar and battery systems.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What type of sensor detects movement by sensing body heat? (Three letters)',
                    'explanation' => 'PIR stands for passive infrared, a sensor that detects movement by sensing infrared heat.',
                    'correct_answer' => 'PIR',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What unit is used to measure how fast electricity is being used? (One word)',
                    'explanation' => 'Power is measured in watts, while energy is measured in watt-hours.',
                    'correct_answer' => 'Watt',
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
                'title' => 'Lesson 3.1: Using MTN, Airtel, and Zamtel Networks for IoT',
                'duration_minutes' => 60,
                'content' => $this->lesson3_1(),
            ],
            [
                'title' => 'Lesson 3.2: Wi-Fi vs Mobile Data vs LoRa for Rural Areas',
                'duration_minutes' => 75,
                'content' => $this->lesson3_2(),
            ],
            [
                'title' => 'Lesson 3.3: Building a Simple Gateway with Raspberry Pi',
                'duration_minutes' => 60,
                'content' => $this->lesson3_3(),
            ],
            [
                'title' => 'Module 3 Quiz: Connecting IoT Devices to the Internet',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your knowledge of mobile networks, connectivity options, and gateways for IoT projects in Zambia. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson3_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will understand how IoT devices use SIM cards and mobile data from MTN, Airtel, and Zamtel, how to choose a data plan, and how to keep costs low.</p>

<h2>Why Mobile Networks Matter for IoT</h2>
<p>Most farms, boreholes, and remote sites in Zambia do not have Wi-Fi. They do have mobile phone signal. This makes mobile data the most practical way to connect IoT devices outside towns and cities. A small device with a SIM card can send data from a maize field near Kalomo to a phone in Lusaka in seconds.</p>
<p>Mobile networks in Zambia are provided mainly by MTN, Airtel, and Zamtel. Each company sells SIM cards and data bundles. The network coverage and data prices vary by area, so it is important to check which network has the strongest signal at your project site before buying hardware.</p>

<h2>How an IoT Device Uses a SIM Card</h2>
<p>A device that uses mobile data has a small module called a GSM or LTE module. This module is like the modem inside a smartphone. It needs a SIM card, just like your phone. The microcontroller sends data to the module, and the module sends it over the mobile network to the internet.</p>
<p>The SIM card can be a normal phone SIM or a special IoT SIM. Some companies sell IoT SIMs with lower data prices for machines. However, in Zambia it is often easier to buy a standard prepaid SIM, load a small data bundle through mobile money, and use that. Make sure the SIM is registered correctly according to ZICTA rules.</p>

<h2>Choosing a Data Plan</h2>
<p>IoT devices usually send very small amounts of data. A temperature reading might be only a few bytes. Even if the device sends data every ten minutes, it may use only a few megabytes per month. This means you do not need an expensive unlimited data plan.</p>
<p>Look for a small monthly bundle. MTN, Airtel, and Zamtel all offer bundles of a few hundred megabytes to a few gigabytes. For many IoT projects, 500 MB per month is enough. Use mobile money to top up automatically when possible, or set a calendar reminder so the device never runs out of data.</p>

<table>
<tr><th>Network</th><th>Useful For</th><th>Check Before Buying</th></tr>
<tr><td>MTN</td><td>Towns and major roads</td><td>Signal strength at the site</td></tr>
<tr><td>Airtel</td><td>Towns and many rural areas</td><td>Data bundle prices</td></tr>
<tr><td>Zamtel</td><td>Some towns and copperbelt areas</td><td>Coverage map for the area</td></tr>
</table>

<h2>Reducing Data Costs</h2>
<ul>
<li><strong>Send less often</strong>: A soil sensor does not need to report every second. Once per hour is usually enough.</li>
<li><strong>Send smaller messages</strong>: Use short codes instead of long sentences. For example, send "T:32,H:65" instead of "Temperature is 32 degrees and humidity is 65 percent."</li>
<li><strong>Use SMS for alerts</strong>: For urgent warnings, an SMS may be cheaper than keeping a data connection open.</li>
<li><strong>Compress data</strong>: Combine several readings into one message instead of sending many separate messages.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Check the mobile signal at your home, farm, or shop. Which network has the strongest signal?</li>
<li>Look up the current price of a 500 MB or 1 GB data bundle from MTN, Airtel, and Zamtel.</li>
<li>Calculate how many messages a device could send in one month using 500 MB if each message is 100 bytes.</li>
<li>Ask a shopkeeper whether they have ever used mobile money to buy data. What was the process?</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>SIM card</strong>: A small chip that identifies a device on a mobile network.</li>
<li><strong>GSM module</strong>: A component that lets a microcontroller send data over a mobile network.</li>
<li><strong>LTE</strong>: A faster mobile data technology, also known as 4G.</li>
<li><strong>Data bundle</strong>: A prepaid amount of mobile data sold by a network provider.</li>
<li><strong>APN</strong>: Access Point Name, a setting that tells the device how to connect to the mobile internet.</li>
</ul>

<h2>Summary</h2>
<p>Mobile networks are the backbone of rural IoT in Zambia. By choosing the right network, using a small data bundle, and sending small, infrequent messages, you can keep an IoT device connected for a low monthly cost. Always test signal strength at the project site before finalising your design.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.mtn.zm/" target="_blank" rel="noopener">MTN Zambia</a></li>
<li><a href="https://www.airtel.co.zm/" target="_blank" rel="noopener">Airtel Zambia</a></li>
<li><a href="https://www.zamtel.zm/" target="_blank" rel="noopener">Zamtel</a></li>
</ul>
HTML;
    }

    private function lesson3_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to compare Wi-Fi, mobile data, and LoRa for different IoT applications, and choose the best option for a project in a town, farm, or remote rural area.</p>

<h2>Three Ways to Connect</h2>
<p>Almost every IoT project must send data from the device to the user. In Zambia, the three most common ways to do this are Wi-Fi, mobile data, and LoRa. Each has strengths and weaknesses. The right choice depends on distance, power, cost, and whether mains power is available.</p>

<h2>Wi-Fi</h2>
<p>Wi-Fi is fast and cheap when the device is near a building with a router. A shop monitoring its freezer, an office checking its server room, or a home automating lights can all use Wi-Fi. Most households and businesses in towns already have Wi-Fi routers, so there is no extra data cost.</p>
<p>The disadvantage is range. A typical Wi-Fi router only covers a building and perhaps a small yard. It will not reach a field two kilometres away. Wi-Fi also uses more power than some low-power alternatives, so it is less ideal for battery-powered outdoor sensors.</p>

<h2>Mobile Data</h2>
<p>Mobile data uses the same towers that carry phone calls and internet. It works anywhere there is mobile signal, which includes most towns and many rural areas in Zambia. A device with a SIM card and a GSM or LTE module can send data directly to the cloud.</p>
<p>The main cost is the data bundle. However, because IoT messages are small, the cost is usually low. Mobile data is ideal for remote boreholes, vehicle trackers, farm sensors, and any project where Wi-Fi is not available. The device needs a SIM card, which must be kept topped up.</p>

<h2>LoRa</h2>
<p>LoRa is a long-range, low-power radio technology designed specifically for IoT. A LoRa sensor can send small messages over five to fifteen kilometres, depending on the terrain. It does not need a SIM card or mobile data. Instead, it sends data to a nearby LoRa gateway, which then forwards it to the internet.</p>
<p>LoRa is excellent for large farms, game reserves, and rural communities where mobile signal is weak. The sensor itself uses very little power and can run for years on a small battery. The downside is that you need to install a gateway, and the messages are small and slow. LoRa is not suitable for sending videos or large files.</p>

<table>
<tr><th>Method</th><th>Range</th><th>Power Use</th><th>Cost</th><th>Best Use</th></tr>
<tr><td>Wi-Fi</td><td>Building / small yard</td><td>Medium</td><td>No extra data cost</td><td>Shops, homes, offices</td></tr>
<tr><td>Mobile data</td><td>Wherever phone signal exists</td><td>Medium</td><td>Monthly data bundle</td><td>Remote farms, vehicles, boreholes</td></tr>
<tr><td>LoRa</td><td>5–15 km to gateway</td><td>Very low</td><td>Gateway setup once</td><td>Large farms, rural sensors</td></tr>
</table>

<h2>When to Combine Methods</h2>
<p>Many projects use more than one method. A farm might use LoRa sensors in the fields, a solar-powered gateway near the farmhouse, and mobile data from the gateway to the internet. A shop might use Wi-Fi for indoor sensors and mobile data as a backup if the Wi-Fi fails.</p>
<p>The key is to match the technology to the job. Use the cheapest option that reliably covers the distance and power constraints of your project. Do not over-engineer. A simple Wi-Fi temperature sensor in a shop is better than an expensive mobile-data system if Wi-Fi is already available.</p>

<h2>Try It Yourself</h2>
<ol>
<li>For each of these locations, choose Wi-Fi, mobile data, or LoRa: a shop in Kitwe, a maize field 5 km from the farmhouse, a water tank on a school roof, a vehicle travelling between Lusaka and Livingstone.</li>
<li>Explain your choice for each location in one sentence.</li>
<li>Draw a diagram of a farm that uses LoRa sensors and a mobile-data gateway.</li>
<li>Find out whether anyone in your area sells LoRa modules or gateways.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Wi-Fi</strong>: A short-range wireless technology commonly used in homes and offices.</li>
<li><strong>Mobile data</strong>: Internet access through cellular networks such as MTN, Airtel, or Zamtel.</li>
<li><strong>LoRa</strong>: Long Range, a low-power radio technology for sending small data over long distances.</li>
<li><strong>Gateway</strong>: A device that collects local data and forwards it to the internet.</li>
<li><strong>Range</strong>: The maximum distance over which a wireless signal can reliably travel.</li>
</ul>

<h2>Summary</h2>
<p>Wi-Fi is best for indoor projects where a router already exists. Mobile data reaches anywhere with phone signal and is ideal for remote locations. LoRa covers long distances with very low power but requires a gateway. The best IoT designs often combine these methods to balance cost, range, and reliability.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.cisco.com/c/en/us/solutions/internet-of-things/what-is-iot.html" target="_blank" rel="noopener">Cisco — What Is IoT?</a></li>
<li><a href="https://lora-alliance.org/about-lorawan/" target="_blank" rel="noopener">LoRa Alliance — About LoRaWAN</a></li>
<li><a href="https://www.gsma.com/iot/" target="_blank" rel="noopener">GSMA — IoT</a></li>
</ul>
HTML;
    }

    private function lesson3_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will understand what a gateway does, how to build a simple one using a Raspberry Pi, and how it connects local sensors to the cloud.</p>

<h2>What Does a Gateway Do?</h2>
<p>A gateway is a bridge between two networks. In IoT, it usually bridges your local sensors and the internet. Local sensors may use Wi-Fi, Bluetooth, or LoRa to talk to the gateway. The gateway then uses mobile data or a wired internet connection to send everything to the cloud.</p>
<p>Think of a gateway as the post office for your sensors. The sensors write small letters. The gateway collects them and puts them on the long-distance bus to the city. Without the gateway, each sensor would need its own expensive long-distance connection.</p>

<h2>Why Use a Raspberry Pi as a Gateway?</h2>
<p>A Raspberry Pi is a small, low-cost computer that can run Linux. It has Wi-Fi, Ethernet, USB ports, and GPIO pins. This makes it very flexible as a gateway. It can receive data from local sensors over Wi-Fi or LoRa, store it temporarily, and forward it to a cloud platform.</p>
<p>For a farm in Zambia, a Raspberry Pi gateway might sit in a farmhouse with a small solar panel and battery. It receives LoRa messages from soil sensors across the fields. It then uses a mobile data dongle or an old smartphone hotspot to send the data to the internet. The farmer sees everything on a phone app.</p>

<h2>Basic Gateway Setup</h2>
<p>Step 1: Install Raspberry Pi OS on a microSD card and boot the Raspberry Pi.</p>
<p>Step 2: Connect the Raspberry Pi to the internet through Wi-Fi, Ethernet, or a USB mobile-data dongle.</p>
<p>Step 3: Install software that listens for local sensor data. For LoRa, you might use a LoRa hat on the Pi and software such as The Things Network or a private LoRa server.</p>
<p>Step 4: Write a small script that receives sensor data, formats it, and sends it to a cloud service such as ThingSpeak, Blynk, or your own server.</p>
<p>Step 5: Add power backup. A small UPS or solar battery keeps the gateway running during load-shedding.</p>

<h2>Handling Network Failures</h2>
<p>In Zambia, the internet is not always reliable. A good gateway stores data locally when the internet is down and sends it later. This is called buffering or store-and-forward. For example, if the mobile network is weak for two hours, the gateway saves the sensor readings and uploads them when the signal returns.</p>
<p>Buffering is important because it prevents gaps in your data. A farmer checking weekly trends needs every reading, not just the ones sent when the network was perfect. A simple database or even text files on the Raspberry Pi can store readings until the connection returns.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Draw a diagram showing three sensors, one gateway, the internet, and a phone.</li>
<li>Write down three reasons a farm might prefer one gateway over giving every sensor a SIM card.</li>
<li>Look up the price of a Raspberry Pi and a LoRa hat in Zambia.</li>
<li>Research one cloud platform such as ThingSpeak or Blynk and write one paragraph about what it does.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Gateway</strong>: A device that connects a local network to a larger network such as the internet.</li>
<li><strong>Raspberry Pi</strong>: A small computer often used as a server or gateway.</li>
<li><strong>LoRa hat</strong>: An add-on board that gives a Raspberry Pi LoRa radio capabilities.</li>
<li><strong>Buffering</strong>: Temporarily storing data when the network is unavailable.</li>
<li><strong>Cloud platform</strong>: A remote service that receives, stores, and displays IoT data.</li>
</ul>

<h2>Summary</h2>
<p>A gateway collects data from local sensors and forwards it to the cloud. A Raspberry Pi is a flexible, affordable gateway platform. With mobile data or Wi-Fi for internet access, and LoRa or Wi-Fi for local sensors, you can build a gateway that keeps working through load-shedding and network interruptions.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.raspberrypi.org/documentation/" target="_blank" rel="noopener">Raspberry Pi Documentation</a></li>
<li><a href="https://thingspeak.com/" target="_blank" rel="noopener">ThingSpeak — IoT Analytics Platform</a></li>
<li><a href="https://blynk.io/" target="_blank" rel="noopener">Blynk — IoT Platform</a></li>
</ul>
HTML;
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Connecting IoT Devices to the Internet',
            'description' => 'Test your understanding of mobile networks, connectivity methods, and gateways.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which mobile network technology requires a SIM card in the IoT device?',
                    'explanation' => 'Mobile data uses SIM cards to connect to cellular networks such as MTN, Airtel, or Zamtel.',
                    'options' => [
                        ['text' => 'Wi-Fi', 'is_correct' => false],
                        ['text' => 'LoRa', 'is_correct' => false],
                        ['text' => 'Mobile data', 'is_correct' => true],
                        ['text' => 'Bluetooth', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which connection method is usually best for sending small messages over long distances with very low power?',
                    'explanation' => 'LoRa is designed for long-range, low-power communication of small messages.',
                    'options' => [
                        ['text' => 'Wi-Fi', 'is_correct' => false],
                        ['text' => 'Mobile data', 'is_correct' => false],
                        ['text' => 'LoRa', 'is_correct' => true],
                        ['text' => 'Ethernet cable', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main job of a gateway in an IoT system?',
                    'explanation' => 'A gateway collects local sensor data and forwards it to the internet or cloud platform.',
                    'options' => [
                        ['text' => 'To measure soil moisture directly', 'is_correct' => false],
                        ['text' => 'To store solar power for the night', 'is_correct' => false],
                        ['text' => 'To bridge local sensors to the internet', 'is_correct' => true],
                        ['text' => 'To display advertisements on a screen', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is buffering useful for a gateway in Zambia?',
                    'explanation' => 'Buffering stores data when the internet is down and sends it later, preventing gaps in records.',
                    'options' => [
                        ['text' => 'It makes the gateway faster', 'is_correct' => false],
                        ['text' => 'It stores data during network failures', 'is_correct' => true],
                        ['text' => 'It reduces the need for sensors', 'is_correct' => false],
                        ['text' => 'It increases mobile data speed', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Wi-Fi is the best choice for a sensor located five kilometres from the nearest building.',
                    'explanation' => 'Wi-Fi has a short range. For long distances, mobile data or LoRa are better choices.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A Raspberry Pi can be used as an IoT gateway.',
                    'explanation' => 'A Raspberry Pi can run gateway software, connect to local sensors, and forward data to the cloud.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What do we call a small chip that identifies a device on a mobile network? (Three letters)',
                    'explanation' => 'A SIM card identifies the device to the mobile network and is required for mobile data.',
                    'correct_answer' => 'SIM',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the name of the long-range, low-power radio technology often used in rural IoT? (Four letters)',
                    'explanation' => 'LoRa stands for Long Range and is used for low-power communication over long distances.',
                    'correct_answer' => 'LoRa',
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
                'title' => 'Lesson 4.1: Turning Sensor Readings into Useful Information',
                'duration_minutes' => 60,
                'content' => $this->lesson4_1(),
            ],
            [
                'title' => 'Lesson 4.2: Alerts by SMS, WhatsApp, and Email',
                'duration_minutes' => 75,
                'content' => $this->lesson4_2(),
            ],
            [
                'title' => 'Lesson 4.3: Simple Automation for Irrigation and Security Lights',
                'duration_minutes' => 60,
                'content' => $this->lesson4_3(),
            ],
            [
                'title' => 'Module 4 Quiz: Reading Data and Making Decisions',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your knowledge of data interpretation, alerts, and automation for IoT systems in Zambia. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson4_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to read raw sensor numbers, turn them into meaningful information, display them on a simple dashboard, and spot trends that help you make better decisions.</p>

<h2>From Numbers to Meaning</h2>
<p>A sensor gives you numbers. A soil-moisture sensor might send "432." A temperature sensor might send "31." These numbers are useless by themselves. The value of IoT comes from turning those numbers into meaning. "432" becomes "soil is dry; irrigation recommended." "31" becomes "brooder temperature is too high; open ventilation."</p>
<p>This transformation happens in three steps. First, the microcontroller reads the raw value. Second, the value is converted into a real-world unit using a formula or calibration table. Third, the converted value is compared to a threshold or trend to produce advice or action.</p>

<h2>Calibration and Units</h2>
<p>Most sensors need calibration. Calibration means matching the sensor's output to a known real-world value. For example, a soil-moisture sensor might give 0 in dry air and 800 when fully submerged in water. By testing it in dry soil and wet soil, you can map the readings to "very dry," "dry," "moist," and "waterlogged."</p>
<p>Temperature sensors are usually already calibrated in degrees Celsius. Pressure sensors may need a formula to convert volts into water depth. Current sensors need to know the rating of the wire they clamp around. Always read the sensor datasheet and test the sensor in known conditions before trusting it.</p>

<h2>Thresholds and Alerts</h2>
<p>A threshold is a value that separates normal from abnormal. For a chicken brooder, the normal temperature might be 32 to 35 degrees Celsius. Below 32 is too cold. Above 35 is too hot. The IoT system compares each reading to these thresholds and triggers an alert only when the value is outside the normal range.</p>
<p>Setting good thresholds takes time. If the threshold is too sensitive, you get too many false alarms. If it is too relaxed, you miss real problems. Start with safe values and adjust based on experience. For a freezer, an alert at -12 degrees Celsius might warn you before food starts to thaw, giving time to act.</p>

<h2>Dashboards and Trends</h2>
<p>A dashboard is a simple screen that shows current readings and recent history. Free platforms such as ThingSpeak, Blynk, and Ubidots let you create dashboards with graphs and gauges. You can see whether soil moisture is rising after irrigation, whether temperature fluctuates at night, or whether water use is increasing.</p>
<p>Trends are often more useful than single readings. A single high temperature reading might be a mistake. But five high readings in a row show a real problem. Likewise, a slowly dropping water level over a week might indicate a leak, even if each individual reading looks normal.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Find a simple online dashboard tool such as ThingSpeak and create a free account.</li>
<li>Imagine a soil-moisture sensor. Write four labels: "very dry," "dry," "moist," and "waterlogged."</li>
<li>Assign example sensor values to each label, such as 0-200, 201-400, 401-700, and 701-1023.</li>
<li>Write the alert message a farmer should receive when the soil is "very dry."</li>
<li>Draw a simple graph showing soil moisture over one week, with irrigation marked on the days it happened.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Calibration</strong>: Matching a sensor's output to known real-world values.</li>
<li><strong>Threshold</strong>: A value that separates normal from abnormal conditions.</li>
<li><strong>Dashboard</strong>: A visual display of current and historical sensor data.</li>
<li><strong>Trend</strong>: A pattern of change over time.</li>
<li><strong>False alarm</strong>: An alert triggered when there is no real problem.</li>
</ul>

<h2>Summary</h2>
<p>Raw sensor numbers become valuable only when they are converted into real-world units, compared to thresholds, and displayed as trends. A good dashboard helps users spot problems early and make decisions based on evidence rather than guesswork.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://thingspeak.com/" target="_blank" rel="noopener">ThingSpeak</a></li>
<li><a href="https://blynk.io/" target="_blank" rel="noopener">Blynk</a></li>
<li><a href="https://ubidots.com/" target="_blank" rel="noopener">Ubidots</a></li>
</ul>
HTML;
    }

    private function lesson4_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to set up SMS, WhatsApp, and email alerts from an IoT system, choose the right alert method for each situation, and avoid sending so many alerts that people ignore them.</p>

<h2>Why Alerts Matter</h2>
<p>A dashboard is useful when you are looking at it. But farmers, shop owners, and security guards are busy. They cannot watch a screen all day. Alerts bring important information directly to the person who needs it, at the moment it matters. A timely SMS about a failing freezer can save thousands of Kwacha of stock.</p>
<p>The best alert is one that arrives at the right time, goes to the right person, and says exactly what to do. A bad alert is vague, arrives too late, or arrives so often that people stop paying attention.</p>

<h2>SMS Alerts</h2>
<p>SMS is the most reliable alert method in Zambia. Almost every phone can receive SMS, even old feature phones. An SMS does not need internet on the receiving phone. This makes it ideal for urgent alerts sent to farmers or guards in rural areas.</p>
<p>To send SMS from an IoT device, you usually use a GSM module and a SIM card, or you use an SMS gateway service on the internet. Some services let you send SMS through an API. The cost per SMS is low, but it adds up if you send many alerts. Reserve SMS for situations that need immediate action, such as "Freezer temperature high — check now."</p>

<h2>WhatsApp Alerts</h2>
<p>WhatsApp is cheaper than SMS when internet is available. It also allows longer messages, photos, and even voice notes. A WhatsApp alert could include a graph of the last 24 hours, a photo from a security camera, or a voice message explaining the situation.</p>
<p>WhatsApp Business API and third-party services can send automated messages. However, setting this up is more complex than SMS. For small projects, some platforms can send WhatsApp messages through unofficial libraries, but these may break when WhatsApp updates its systems. For a reliable project, use a supported WhatsApp Business provider or keep SMS as a backup.</p>

<h2>Email Alerts</h2>
<p>Email is useful for daily or weekly summaries rather than urgent alerts. A shop owner might receive an email every morning with the previous day's temperature graph. A farm manager might receive a weekly water-use summary. Email can include detailed information and attachments, but it is not reliable for emergencies because people may not check it immediately.</p>

<h2>Alert Best Practices</h2>
<ul>
<li><strong>Be specific</strong>: "Water tank 2 is below 20%" is better than "Problem with tank."</li>
<li><strong>Suggest action</strong>: "Switch to backup pump" or "Check freezer door."</li>
<li><strong>Avoid alert fatigue</strong>: Do not send an alert every time a reading changes slightly. Use thresholds and delays.</li>
<li><strong>Choose the right channel</strong>: Use SMS for urgent problems, WhatsApp for rich updates, and email for summaries.</li>
<li><strong>Test regularly</strong>: Send a test alert every week to make sure the system is still working.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Write three example alert messages for a chicken brooder: one normal update, one warning, and one emergency.</li>
<li>For each message, decide whether SMS, WhatsApp, or email is the best channel and explain why.</li>
<li>Design an alert rule: "If temperature is above 35 degrees for more than 10 minutes, send an SMS."</li>
<li>Ask three people which alert channel they check most often during the day.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Alert</strong>: A message sent when a condition needs attention.</li>
<li><strong>SMS gateway</strong>: A service that sends text messages through an API or device.</li>
<li><strong>WhatsApp Business API</strong>: An official way for businesses to send automated WhatsApp messages.</li>
<li><strong>Alert fatigue</strong>: The problem of receiving so many alerts that important ones are ignored.</li>
<li><strong>API</strong>: Application Programming Interface, a way for software to communicate.</li>
</ul>

<h2>Summary</h2>
<p>Alerts turn IoT data into action. SMS is reliable and works on any phone. WhatsApp is rich and cheap when internet is available. Email is best for summaries. Good alerts are specific, actionable, and not too frequent. Matching the channel to the urgency of the situation is key.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.twilio.com/messaging" target="_blank" rel="noopener">Twilio — Messaging</a></li>
<li><a href="https://business.whatsapp.com/products/business-platform" target="_blank" rel="noopener">WhatsApp Business Platform</a></li>
<li><a href="https://sendgrid.com/" target="_blank" rel="noopener">SendGrid — Email API</a></li>
</ul>
HTML;
    }

    private function lesson4_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to design simple automations that turn sensor readings into actions, such as switching a pump, turning on a light, or sounding an alarm.</p>

<h2>What Is Automation?</h2>
<p>Automation means a system takes action without a human giving the command every time. In IoT, automation happens when a microcontroller compares a sensor reading to a rule and then controls an actuator. The simplest rule is an if-then statement: "If soil moisture is below 30%, then turn on the pump for ten minutes."</p>
<p>Automation saves time and reduces mistakes. A farmer does not need to walk to the pump at midnight. A shop owner does not need to check the freezer every hour. The system watches and acts according to the rules.</p>

<h2>Automation Example: Smart Irrigation</h2>
<p>A smart irrigation system for a maize field has three parts: a soil-moisture sensor, a microcontroller, and a relay that controls the water pump. The farmer sets a threshold, for example 35% moisture. When the sensor reads below 35%, the microcontroller closes the relay and the pump starts. When the sensor reads above 60%, the pump stops.</p>
<p>Good automation also includes safety rules. The pump should not run if the water tank is empty. It should not run during the hottest part of the day if evaporation is high. It should send a confirmation message to the farmer so the farmer knows what happened. These extra rules make the automation reliable and trustworthy.</p>

<h2>Automation Example: Security Light</h2>
<p>A motion sensor near a farm store detects movement at night. When movement is detected, the microcontroller turns on a bright light and sends a WhatsApp message to the owner. If no more movement is detected for five minutes, the light turns off. This simple automation deters thieves and alerts the owner immediately.</p>
<p>The same idea works for a gate sensor. A reed switch on the gate tells the microcontroller whether the gate is open or closed. If the gate opens after 9 p.m., the system turns on the light and sends an SMS. The owner can call the guard or check the camera.</p>

<h2>Automation Rules to Follow</h2>
<ul>
<li><strong>Start simple</strong>: Automate one thing first, such as turning on a pump. Add more rules later.</li>
<li><strong>Always have a manual override</strong>: The farmer or shop owner must be able to turn the pump or light on and off manually.</li>
<li><strong>Use timers and delays</strong>: A pump should not switch on and off every few seconds. Add a minimum run time.</li>
<li><strong>Log every action</strong>: Record when the system turned something on or off. This helps with troubleshooting.</li>
<li><strong>Test safely</strong>: Test automation with small, safe devices before connecting expensive equipment.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Write an if-then rule for a chicken brooder fan that turns on when the temperature is above 33 degrees Celsius.</li>
<li>Add a second rule that sends an SMS if the temperature stays above 35 degrees for more than 15 minutes.</li>
<li>Draw a diagram showing the sensor, microcontroller, relay, and device for a security light.</li>
<li>Explain why a manual override is important for any automated pump.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Automation</strong>: A system taking action automatically based on rules.</li>
<li><strong>If-then rule</strong>: A simple logic rule that triggers an action when a condition is met.</li>
<li><strong>Manual override</strong>: A way for a human to take control away from the automation.</li>
<li><strong>Minimum run time</strong>: A setting that prevents a device from switching on and off too quickly.</li>
<li><strong>Actuator</strong>: A device that physically does something when commanded.</li>
</ul>

<h2>Summary</h2>
<p>Automation makes IoT truly useful by letting systems respond immediately to sensor data. Smart irrigation, security lights, and temperature-controlled fans all follow simple if-then rules. Good automation includes safety checks, manual overrides, and clear logging so that people can trust the system.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.adafruit.com/automating-your-life-with-arduino" target="_blank" rel="noopener">Adafruit — Automating with Arduino</a></li>
<li><a href="https://www.hackster.io/projects?tag=automation" target="_blank" rel="noopener">Hackster — Automation Projects</a></li>
<li><a href="https://www.instructables.com/Arduino-Home-Automation/" target="_blank" rel="noopener">Instructables — Arduino Home Automation</a></li>
</ul>
HTML;
    }

    private function module4Quiz(): array
    {
        return [
            'title' => 'Module 4 Quiz: Reading Data and Making Decisions',
            'description' => 'Assess your understanding of data interpretation, alerts, and automation.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the purpose of calibrating a sensor?',
                    'explanation' => 'Calibration matches the sensor\'s output to known real-world values so readings are accurate.',
                    'options' => [
                        ['text' => 'To make the sensor connect to Wi-Fi', 'is_correct' => false],
                        ['text' => 'To match sensor output to real-world values', 'is_correct' => true],
                        ['text' => 'To increase the battery size', 'is_correct' => false],
                        ['text' => 'To delete old data', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which alert channel is most reliable for urgent messages in rural Zambia?',
                    'explanation' => 'SMS works on almost any phone, even without internet, making it the most reliable channel.',
                    'options' => [
                        ['text' => 'Email', 'is_correct' => false],
                        ['text' => 'WhatsApp', 'is_correct' => false],
                        ['text' => 'SMS', 'is_correct' => true],
                        ['text' => 'Facebook Messenger', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is a threshold in an IoT system?',
                    'explanation' => 'A threshold is a value that separates normal from abnormal conditions and triggers alerts or actions.',
                    'options' => [
                        ['text' => 'The maximum speed of a motor', 'is_correct' => false],
                        ['text' => 'A value that separates normal from abnormal readings', 'is_correct' => true],
                        ['text' => 'The colour of a dashboard', 'is_correct' => false],
                        ['text' => 'The brand of a sensor', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why should automation include a manual override?',
                    'explanation' => 'A manual override lets a human take control in case the automation fails or conditions change.',
                    'options' => [
                        ['text' => 'To make the system more expensive', 'is_correct' => false],
                        ['text' => 'To allow a human to take control when needed', 'is_correct' => true],
                        ['text' => 'To increase the number of alerts', 'is_correct' => false],
                        ['text' => 'To replace the microcontroller', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Email is the best channel for urgent alerts that need immediate action.',
                    'explanation' => 'Email is better for summaries and non-urgent reports because people may not check it immediately.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Sending too many alerts can cause people to ignore important warnings.',
                    'explanation' => 'Alert fatigue happens when people receive too many alerts and begin to ignore them.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What type of message is best for urgent, immediate problems: SMS, WhatsApp, or email? (Three letters)',
                    'explanation' => 'SMS is the most reliable for urgent alerts because it works on almost any phone.',
                    'correct_answer' => 'SMS',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for a system taking action automatically based on rules? (One word)',
                    'explanation' => 'Automation means a system acts without human intervention based on programmed rules.',
                    'correct_answer' => 'Automation',
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
                'title' => 'Lesson 5.1: Smart Irrigation for Maize and Vegetable Gardens',
                'duration_minutes' => 75,
                'content' => $this->lesson5_1(),
            ],
            [
                'title' => 'Lesson 5.2: Monitoring Cold Storage and Shop Stock',
                'duration_minutes' => 60,
                'content' => $this->lesson5_2(),
            ],
            [
                'title' => 'Lesson 5.3: Home Security and Energy Monitoring During Load-Shedding',
                'duration_minutes' => 60,
                'content' => $this->lesson5_3(),
            ],
            [
                'title' => 'Module 5 Quiz: IoT for Farms, Shops, and Homes in Zambia',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your knowledge of practical IoT applications for farms, shops, and homes in Zambia. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson5_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to design a smart irrigation system for maize, vegetables, or other crops, choose the right sensors and actuators, and explain how it saves water and labour.</p>

<figure><img class="lesson-diagram" src="/assets/diagrams/internet-of-things/smart-irrigation-flow.svg" alt="Smart irrigation flow diagram showing a soil sensor, solar-powered gateway, cloud platform, farmer's phone, and pump valve."><figcaption>Figure: Smart irrigation system flow for a Zambian farm.</figcaption></figure>

<h2>Why Smart Irrigation?</h2>
<p>Water is one of the most precious resources for Zambian farmers. Too little water stresses the crop and reduces yield. Too much water wastes fuel for the pump, leaches fertiliser, and can drown roots. Traditional irrigation schedules, such as "water every Tuesday and Friday," do not account for rainfall, temperature, or soil type.</p>
<p>Smart irrigation uses soil-moisture sensors and weather data to water only when needed. It can also run at the best time of day, such as early morning, to reduce evaporation. For a farmer growing maize near Kalomo or vegetables outside Lusaka, this can mean higher yields, lower diesel bills, and more free time.</p>

<h2>System Parts</h2>
<p>A basic smart irrigation system has four parts. First, one or more <strong>soil-moisture sensors</strong> placed at root depth. Second, a <strong>microcontroller</strong> such as an ESP32 that reads the sensors and makes decisions. Third, a <strong>relay</strong> that switches the pump on and off. Fourth, a <strong>power system</strong> that may include mains power, a battery, and a solar panel.</p>
<p>The system also needs a way to report to the farmer. This could be an SMS when irrigation starts, a WhatsApp message with the current soil moisture, or a simple dashboard showing trends. During load-shedding, a solar panel and battery keep the sensors and controller running even if the pump itself cannot run.</p>

<h2>Designing Zones</h2>
<p>Large fields should be divided into zones. Each zone may have different soil, crops, and water needs. A vegetable garden with sandy soil needs more frequent watering than a maize field with clay soil. By placing one sensor per zone, the system can water each zone independently.</p>
<p>For a smallholder farm, two or three zones may be enough. For a larger commercial farm, there may be ten or more zones. The cost increases with more sensors, but the savings in water and labour usually justify the investment.</p>

<h2>Local Example</h2>
<p>Imagine a one-hectare vegetable plot near Choma. The farmer installs three soil-moisture sensors, one in tomatoes, one in rape, and one in onions. The ESP32 reads each sensor every hour. If any zone drops below the threshold, the system opens the valve for that zone for fifteen minutes and sends an SMS to the farmer. After one season, the farmer uses 30% less water and notices fewer cases of root disease because the soil is never waterlogged.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Draw a map of a small farm or garden and mark two zones with different water needs.</li>
<li>Choose a soil-moisture threshold for each zone and explain your reasoning.</li>
<li>Write the if-then rules for turning the pump on and off in each zone.</li>
<li>Calculate the daily water saving if the system prevents one unnecessary irrigation per week on a one-hectare plot.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Smart irrigation</strong>: An irrigation system that uses sensors and automation to water only when needed.</li>
<li><strong>Zone</strong>: A separate area of a field that can be watered independently.</li>
<li><strong>Evaporation</strong>: The process of water turning into vapour and leaving the soil.</li>
<li><strong>Root depth</strong>: The depth in the soil where most of a plant's roots absorb water.</li>
<li><strong>Waterlogging</strong>: A condition where soil is saturated with water, which can harm plant roots.</li>
</ul>

<h2>Summary</h2>
<p>Smart irrigation helps Zambian farmers use water more efficiently. Soil sensors, a microcontroller, relays, and solar power work together to water crops only when needed. Dividing fields into zones and reporting to the farmer by SMS or WhatsApp makes the system practical and trustworthy.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.fao.org/3/i2800e/i2800e.pdf" target="_blank" rel="noopener">FAO — Smart Irrigation</a></li>
<li><a href="https://www.worldbank.org/en/topic/water" target="_blank" rel="noopener">World Bank — Water</a></li>
<li><a href="https://www.cgiar.org/innovation/irrigation/" target="_blank" rel="noopener">CGIAR — Irrigation Innovation</a></li>
</ul>
HTML;
    }

    private function lesson5_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to design an IoT system that monitors temperature and humidity in cold storage, freezers, and shops, and sends alerts before stock is damaged.</p>

<h2>The Cold-Chain Challenge</h2>
<p>Many products in Zambia need to stay cold: frozen fish, meat, dairy products, medicines, and some vegetables. When a freezer fails or the power goes off, the temperature rises. If nobody notices quickly, the stock spoils and money is lost. A small shop can lose a whole day's profit from one freezer failure.</p>
<p>Load-shedding makes this worse. Even if the freezer has good insulation, it can only stay cold for a few hours without power. A temperature sensor that sends alerts during load-shedding gives the owner time to move stock, buy ice, or start a generator.</p>

<h2>Simple Temperature Monitoring</h2>
<p>A basic system uses a waterproof temperature sensor placed inside the freezer or cold room. The sensor connects to a microcontroller near the freezer. The microcontroller sends readings to the cloud every few minutes. If the temperature rises above a set level, the system sends an SMS or WhatsApp alert.</p>
<p>The sensor should be placed where air circulates, not right against the wall or directly under the cooling fan. It should also be protected from water and ice. A waterproof DS18B20 sensor is a popular choice because it is cheap, accurate, and easy to use.</p>

<h2>Humidity Monitoring</h2>
<p>For some products, humidity matters as much as temperature. Vegetables in a cold room need high humidity to stay crisp. Medicines may need low humidity to prevent damage. A DHT22 sensor can measure both temperature and humidity in one device.</p>
<p>A shop owner selling fresh vegetables can use humidity data to decide when to mist the produce or improve ventilation. A pharmacy can use it to make sure medicines are stored within the correct range on the label.</p>

<h2>Alerts and Reports</h2>
<p>The most important alert is a high-temperature warning. Set the threshold a few degrees below the danger point so the owner has time to act. For a freezer, an alert at -15 degrees Celsius may give enough warning before food starts to thaw at -10 degrees.</p>
<p>Daily summary reports are also useful. A shop owner can see whether the freezer temperature stayed stable overnight, how often the door was opened, and whether load-shedding affected the cold chain. This information helps when choosing a better freezer, backup battery, or generator.</p>

<table>
<tr><th>Product</th><th>Safe Temperature</th><th>Alert Threshold</th></tr>
<tr><td>Frozen fish / meat</td><td>-18 C to -15 C</td><td>-15 C</td></tr>
<tr><td>Fresh milk / yoghurt</td><td>1 C to 4 C</td><td>6 C</td></tr>
<tr><td>Fresh vegetables</td><td>4 C to 10 C</td><td>12 C</td></tr>
<tr><td>Most medicines</td><td>2 C to 8 C</td><td>10 C</td></tr>
</table>

<h2>Try It Yourself</h2>
<ol>
<li>Choose one cold product sold in a shop near you. Find its recommended storage temperature.</li>
<li>Design an alert rule for that product, including the threshold and the message.</li>
<li>Explain how the shop owner could respond to the alert within ten minutes.</li>
<li>Calculate the value of stock that could be saved by a sensor that costs K300.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Cold chain</strong>: The system of keeping products at the right temperature from production to sale.</li>
<li><strong>Waterproof sensor</strong>: A sensor sealed so it can be used in wet or icy conditions.</li>
<li><strong>Humidity</strong>: The amount of moisture in the air.</li>
<li><strong>Threshold</strong>: A value that triggers an alert when crossed.</li>
<li><strong>Daily summary</strong>: A report showing the condition of a system over a day.</li>
</ul>

<h2>Summary</h2>
<p>Cold storage monitoring protects valuable stock in shops, pharmacies, and restaurants. Temperature and humidity sensors, combined with SMS or WhatsApp alerts, give owners early warning of problems. During load-shedding, this early warning can be the difference between saving stock and losing it.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.who.int/teams/health-product-policy-and-standards/assistive-and-medical-devices/medical-device-alert" target="_blank" rel="noopener">WHO — Medical Device Alerts</a></li>
<li><a href="https://www.fao.org/3/ca4055en/ca4055en.pdf" target="_blank" rel="noopener">FAO — Cold Chain Development</a></li>
<li><a href="https://learn.adafruit.com/adafruit-dht11-dht22-sensor-breakouts" target="_blank" rel="noopener">Adafruit — DHT Sensor Guide</a></li>
</ul>
HTML;
    }

    private function lesson5_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to design simple home security and energy monitoring systems that work during load-shedding and help families stay safe and manage power use.</p>

<h2>Home Security with IoT</h2>
<p>Security is a concern for many families and small businesses in Zambia. IoT can provide affordable, automatic security that does not require paying a guard. A few sensors and a cheap microcontroller can watch gates, doors, and outdoor areas and alert the owner immediately.</p>
<p>A basic security system uses PIR motion sensors, door sensors, and a buzzer or light. When motion is detected at night, or when a door opens unexpectedly, the system turns on a bright light and sends an SMS or WhatsApp message. The message can include the time and location, such as "Motion detected at back gate, 22:15."</p>

<h2>Door and Gate Sensors</h2>
<p>A door sensor, also called a reed switch, has two parts. One part attaches to the door, and the other to the door frame. When the door is closed, the two parts are close together and the circuit is complete. When the door opens, the circuit breaks and the microcontroller knows the door is open.</p>
<p>These sensors are cheap and easy to install. They can be used on main doors, back doors, gates, and even refrigerator doors. For a shop, a door sensor after closing time is a simple way to detect break-ins. For a home, a gate sensor can alert parents when children arrive home from school.</p>

<h2>Energy Monitoring</h2>
<p>During load-shedding, families and businesses often use generators, inverters, or solar batteries. An energy monitor measures how much electricity is being used and how much is left in the battery. This helps people avoid draining the battery completely or overloading the generator.</p>
<p>A simple current sensor clamped around the main wire can measure power use. The data is sent to a phone app or displayed on a small screen. The user can see which devices use the most power and decide what to turn off. For example, a geyser or electric iron uses much more power than LED lights and a phone charger.</p>

<h2>Load-Shedding Readiness</h2>
<p>The best IoT security and energy systems are designed for unreliable power. They use low-power microcontrollers, battery backup, and solar charging. They also fail safely. If the internet is down, the system should still sound the local alarm. If the main power is off, the battery should keep sensors and alerts working.</p>
<p>A small solar panel on the roof or gate post can charge a 12-volt battery. That battery can power the microcontroller, sensors, and a small siren for many hours. Even a modest 10-watt panel is enough for a basic security system in sunny Zambia.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Walk around your home or workplace and identify three places where a door or motion sensor would improve security.</li>
<li>Write an alert message for each location, including the time and place.</li>
<li>List five devices in order of power use, from highest to lowest.</li>
<li>Design a small solar and battery system that could power a security light and motion sensor through the night.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Reed switch</strong>: A magnetic sensor used to detect whether a door or window is open or closed.</li>
<li><strong>PIR motion sensor</strong>: A sensor that detects movement by sensing body heat.</li>
<li><strong>Current sensor</strong>: A device that measures the flow of electricity in a wire.</li>
<li><strong>Inverter</strong>: A device that converts battery power into mains-type electricity.</li>
<li><strong>Fail-safe</strong>: A design that keeps the system safe even when something goes wrong.</li>
</ul>

<h2>Summary</h2>
<p>IoT security and energy monitoring make homes and small businesses safer and more efficient. Door sensors, motion sensors, and current sensors provide real-time information. Battery backup and solar power keep the system working during load-shedding. With clear alerts, owners can respond quickly to security risks and manage their power wisely.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.energy.gov/energysaver/energy-saver" target="_blank" rel="noopener">U.S. DOE — Energy Saver</a></li>
<li><a href="https://learn.sparkfun.com/tutorials/non-invasive-current-sensor-30a" target="_blank" rel="noopener">SparkFun — Current Sensor</a></li>
<li><a href="https://www.instructables.com/DIY-Home-Security-and-Automation-With-Raspberry-Pi/" target="_blank" rel="noopener">Instructables — DIY Home Security</a></li>
</ul>
HTML;
    }

    private function module5Quiz(): array
    {
        return [
            'title' => 'Module 5 Quiz: IoT for Farms, Shops, and Homes in Zambia',
            'description' => 'Assess your understanding of smart irrigation, cold storage, and home security applications.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which sensor is most important for a smart irrigation system?',
                    'explanation' => 'A soil-moisture sensor tells the system when the soil is dry and irrigation is needed.',
                    'options' => [
                        ['text' => 'Light sensor', 'is_correct' => false],
                        ['text' => 'Soil-moisture sensor', 'is_correct' => true],
                        ['text' => 'GPS module', 'is_correct' => false],
                        ['text' => 'Gas sensor', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is temperature monitoring especially important during load-shedding?',
                    'explanation' => 'When power is off, freezers and cold rooms warm up, risking stock spoilage.',
                    'options' => [
                        ['text' => 'It reduces internet costs', 'is_correct' => false],
                        ['text' => 'It prevents freezer stock from spoiling', 'is_correct' => true],
                        ['text' => 'It improves mobile signal', 'is_correct' => false],
                        ['text' => 'It charges solar batteries', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What type of sensor is used to detect whether a door or gate is open?',
                    'explanation' => 'A reed switch or door sensor detects when a door or gate is open or closed.',
                    'options' => [
                        ['text' => 'Temperature sensor', 'is_correct' => false],
                        ['text' => 'Reed switch', 'is_correct' => true],
                        ['text' => 'Soil-moisture sensor', 'is_correct' => false],
                        ['text' => 'Current sensor', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main benefit of dividing an irrigation field into zones?',
                    'explanation' => 'Different zones may have different soil and crops, so each can be watered according to its own needs.',
                    'options' => [
                        ['text' => 'It makes the pump more expensive', 'is_correct' => false],
                        ['text' => 'Each zone gets water based on its own needs', 'is_correct' => true],
                        ['text' => 'It removes the need for sensors', 'is_correct' => false],
                        ['text' => 'It stops rain from falling', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A PIR motion sensor detects movement by sensing body heat.',
                    'explanation' => 'PIR sensors detect changes in infrared radiation, which is emitted by warm bodies such as people and animals.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A smart irrigation system should water every day at the same time, regardless of soil moisture.',
                    'explanation' => 'Smart irrigation waters based on need, not a fixed schedule, to save water and prevent overwatering.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What do we call the system of keeping products at the right temperature from production to sale? (Two words)',
                    'explanation' => 'The cold chain includes storage and transport at correct temperatures.',
                    'correct_answer' => 'Cold chain',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What device converts battery power into mains-type electricity for household appliances? (One word)',
                    'explanation' => 'An inverter converts DC battery power into AC power used by most household appliances.',
                    'correct_answer' => 'Inverter',
                ],
            ],
        ];
    }


    // =========================================================================
    // MODULE 6
    // =========================================================================

    private function module6Lessons(): array
    {
        return [
            [
                'title' => 'Lesson 6.1: Keeping Your IoT Data Private and Secure',
                'duration_minutes' => 60,
                'content' => $this->lesson6_1(),
            ],
            [
                'title' => 'Lesson 6.2: Turning an IoT Idea into a Small Business in Kalomo or Lusaka',
                'duration_minutes' => 75,
                'content' => $this->lesson6_2(),
            ],
            [
                'title' => 'Lesson 6.3: Building a Project Plan and Finding Help',
                'duration_minutes' => 60,
                'content' => $this->lesson6_3(),
            ],
            [
                'title' => 'Module 6 Quiz: IoT Safety, Privacy, and Business Opportunities',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your understanding of IoT security, business opportunities, and project planning in Zambia. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson6_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will understand the main security risks of IoT, how to protect devices and data, and why privacy matters for farms, shops, and homes in Zambia.</p>

<h2>Why IoT Security Matters</h2>
<p>Every connected device is a potential doorway into a network. If an attacker gains control of an IoT device, they might spy on the owner, disrupt operations, or use the device to attack others. For a farm, this could mean crop data is stolen. For a shop, it could mean security cameras are disabled. For a home, it could mean private conversations are recorded.</p>
<p>In Zambia, many people think security is only a problem for big companies. This is not true. A small device with a default password can be hacked just as easily as a corporate server. Good security does not have to be expensive. It starts with simple habits.</p>

<h2>Change Default Passwords</h2>
<p>Many IoT devices come with default passwords such as "admin" or "1234." These are well known and easy to guess. The first step after setting up any device is to change the default password to something strong. A strong password is at least 12 characters long and includes letters, numbers, and symbols.</p>
<p>If the device does not allow password changes, consider whether it is safe to use. Some cheap devices are designed without security in mind. For important systems such as security cameras or farm monitors, it is worth buying from a reputable source.</p>

<h2>Keep Software Updated</h2>
<p>Manufacturers release updates to fix security problems. If your device can be updated, install updates regularly. This includes the microcontroller firmware, the gateway software, and any phone apps. An old version may have known weaknesses that attackers can exploit.</p>
<p>For custom-built devices, keep track of the libraries and tools you use. If a security issue is found in a library such as a Wi-Fi library or web server, update your code and reflash the device.</p>

<h2>Use Encryption</h2>
<p>Encryption scrambles data so that only authorised people can read it. When sending data over the internet, always use HTTPS instead of HTTP. When using MQTT, a common IoT messaging protocol, use MQTT over TLS. This prevents attackers from reading your data as it travels.</p>
<p>Even within a local network, avoid sending passwords or sensitive data in plain text. Many free cloud platforms now offer encrypted connections by default. Make sure encryption is enabled before deploying your project.</p>

<h2>Protect Physical Access</h2>
<p>Security is not only digital. If someone can physically touch your device, they might reset it, steal the SD card, or replace it with a fake device. Place gateways and controllers in locked boxes or rooms. Use tamper detection so the system alerts you if a device is moved or opened.</p>
<p>For outdoor sensors, use weatherproof enclosures. For cameras, mount them high enough to avoid easy interference. A visible camera can also deter attackers, but it should be backed up by alerts so you know if it is covered or damaged.</p>

<h2>Privacy and Data Ownership</h2>
<p>When using a cloud platform, read the terms of service. Who owns the data? Can the platform sell or share it? For sensitive farm or business data, you may prefer a platform that keeps data private or a self-hosted server. A self-hosted server is one you control, such as a Raspberry Pi in your office.</p>
<p>Also think about the privacy of other people. A security camera should not point into a neighbour's window. A vehicle tracker should only be used with the driver's knowledge. Respecting privacy builds trust and prevents legal problems.</p>

<h2>Try It Yourself</h2>
<ol>
<li>List three IoT devices or accounts you use and check whether they have strong, unique passwords.</li>
<li>Write a strong password for an imaginary IoT device. It should be at least 12 characters.</li>
<li>Research one cloud platform and answer: who owns the data stored on it?</li>
<li>Draw a simple security plan for an outdoor sensor, including physical protection.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Default password</strong>: A preset password that comes with a device and should be changed immediately.</li>
<li><strong>Encryption</strong>: The process of scrambling data so only authorised parties can read it.</li>
<li><strong>HTTPS</strong>: A secure version of HTTP that encrypts data sent over the web.</li>
<li><strong>MQTT</strong>: A lightweight messaging protocol commonly used in IoT.</li>
<li><strong>Self-hosted</strong>: Running software on your own server instead of a third-party cloud service.</li>
</ul>

<h2>Summary</h2>
<p>IoT security is essential for protecting farms, shops, and homes. Change default passwords, keep software updated, use encryption, and protect physical devices. Respect privacy by choosing trustworthy platforms and being careful about where sensors and cameras point.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.ncsc.gov.uk/collection/top-tips-for-staying-secure-online" target="_blank" rel="noopener">UK NCSC — Cyber Security Tips</a></li>
<li><a href="https://www.cisa.gov/secure-our-world" target="_blank" rel="noopener">CISA — Secure Our World</a></li>
<li><a href="https://owasp.org/www-project-top-ten/" target="_blank" rel="noopener">OWASP Top Ten</a></li>
</ul>
HTML;
    }

    private function lesson6_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to identify business opportunities in IoT, estimate costs and prices, and explain how a small IoT service could earn money in Zambia.</p>

<h2>IoT as a Business</h2>
<p>IoT is not only for engineers. It is also a business opportunity. A person who understands local problems can build or install IoT solutions for farmers, shop owners, schools, clinics, and households. In towns like Kalomo, Choma, and Kitwe, and in cities like Lusaka and Ndola, there are customers who would pay for reliable monitoring and automation.</p>
<p>The business model can take several forms. You can sell devices outright, charge an installation fee, offer a monthly monitoring service, or earn money by maintaining systems. Many customers prefer a service model because they do not want to learn how to fix the technology themselves.</p>

<h2>Finding a Problem Worth Solving</h2>
<p>The best IoT businesses solve a painful, expensive problem. Ask people what keeps them awake at night. A poultry farmer may lose chicks when the heat lamp fails. A pharmacy may throw away spoiled vaccines. A lodge may waste diesel running a generator longer than necessary. Each of these is a business opportunity.</p>
<p>Start with one problem and one type of customer. Do not try to serve everyone at once. If you become known as the person who keeps freezers safe for butcheries, customers will find you. Later you can expand to other industries.</p>

<h2>Estimating Costs and Prices</h2>
<p>To build a business, you must know your numbers. Add up the cost of hardware, labour, data, transport, and a margin for repairs. For example, a freezer temperature monitor might cost K400 in parts and take two hours to install. If you charge K900 for the device plus K100 per month for monitoring, you cover your costs and earn a profit.</p>
<p>Be honest about ongoing costs. The customer needs to know about SIM data, battery replacement, and software fees. If you hide these costs, the customer will be angry later. A clear, simple price builds trust.</p>

<table>
<tr><th>Service</th><th>Possible Price</th><th>Ongoing Cost</th></tr>
<tr><td>Freezer temperature monitor</td><td>K800–1,200</td><td>K50–100 / month data</td></tr>
<tr><td>Smart irrigation sensor</td><td>K1,500–2,500</td><td>K50–150 / month data</td></tr>
<tr><td>Security alarm system</td><td>K1,000–3,000</td><td>K30–80 / month data</td></tr>
<tr><td>Monthly monitoring service</td><td>K100–300 / month</td><td>Support and maintenance</td></tr>
</table>

<h2>Marketing Your IoT Service</h2>
<p>Start with people you know. Install a system for a friend or family member and ask them to recommend you. Use WhatsApp Status, Facebook, and word of mouth. Show photos and short videos of your devices working. Explain the problem and the saving in simple terms, not technical jargon.</p>
<p>A good sales message might be: "Stop losing stock to freezer failures. Get an SMS alert the moment your freezer gets warm. Installation in Kalomo and surrounding areas." This message names the problem, the benefit, and the location.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Interview one farmer, shop owner, or household about a problem that IoT could solve.</li>
<li>Estimate the hardware cost for a simple solution to that problem.</li>
<li>Decide on a price you would charge and a monthly service fee.</li>
<li>Write a one-paragraph sales message for WhatsApp or Facebook.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Business model</strong>: The way a business makes money.</li>
<li><strong>Service model</strong>: Charging customers regularly for ongoing service rather than only selling a product once.</li>
<li><strong>Hardware margin</strong>: The difference between what you pay for parts and what you charge the customer.</li>
<li><strong>Value proposition</strong>: A clear statement of the benefit the customer receives.</li>
<li><strong>Target market</strong>: The specific group of customers you focus on.</li>
</ul>

<h2>Summary</h2>
<p>IoT offers real business opportunities in Zambia for people who can solve local problems. Start with one painful problem, estimate costs honestly, and sell the benefit clearly. A service model with monthly monitoring can create steady income while helping customers protect their farms, shops, and homes.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.zda.org.zm/" target="_blank" rel="noopener">Zambia Development Agency</a></li>
<li><a href="https://www.smega.co.zm/" target="_blank" rel="noopener">SMEDCO / SME Development</a></li>
<li><a href="https://grow.google/intl/en_uk/digitalgarage/" target="_blank" rel="noopener">Google Digital Garage — Business Skills</a></li>
</ul>
HTML;
    }

    private function lesson6_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to write a simple project plan for an IoT system, identify where to find help and components in Zambia, and prepare a presentation to explain your idea to others.</p>

<h2>Why You Need a Project Plan</h2>
<p>Many IoT projects fail because people start buying parts before they know what they are building. A project plan forces you to think through the problem, the solution, the cost, and the timeline. It also makes it easier to ask for help, because others can see exactly what you are trying to do.</p>
<p>A good project plan does not need to be long. One or two pages is enough. It should clearly state the problem, the proposed solution, the components needed, the budget, the timeline, and the risks.</p>

<h2>Sections of a Simple IoT Project Plan</h2>
<p><strong>1. Problem statement</strong>: What problem are you solving? Be specific. "Maize plants on my uncle's farm often suffer from drought because the irrigation pump is turned on too late."</p>
<p><strong>2. Proposed solution</strong>: Describe the IoT system in plain language. "Install a soil-moisture sensor and an ESP32 that sends an SMS when the soil is dry. The farmer can then decide to start the pump."</p>
<p><strong>3. Components</strong>: List every part, including the microcontroller, sensors, power supply, enclosure, SIM card, and cables. Include estimated prices.</p>
<p><strong>4. Budget</strong>: Add up the component costs and add a small amount for unexpected expenses. Include ongoing costs such as data bundles.</p>
<p><strong>5. Timeline</strong>: Break the project into steps such as research, purchasing, assembly, testing, and deployment. Give each step a date.</p>
<p><strong>6. Risks and backups</strong>: What could go wrong? Network failure, power cuts, sensor damage, or lack of parts. How will you handle each?</p>

<h2>Finding Help and Components in Zambia</h2>
<p>Components can be found in electronics shops in Lusaka, Kitwe, and Ndola. Some shops sell Arduino, ESP32, sensors, relays, and solar panels. If you cannot find something locally, many online shops ship to Zambia. Always compare prices including shipping and customs fees.</p>
<p>Help is available online. Websites such as Arduino forums, Stack Overflow, and YouTube have tutorials for almost every sensor and microcontroller. Local maker spaces, universities, and technical colleges may also offer support. Do not be afraid to ask questions. The IoT community is generally helpful.</p>

<h2>Presenting Your Idea</h2>
<p>When presenting your project, start with the problem, not the technology. Most people do not care about the microcontroller; they care about the benefit. Use simple language, pictures, and a cost estimate. Explain what the user will see on their phone and what action they will take.</p>
<p>Practice your presentation on a friend or family member. If they do not understand the value within two minutes, simplify it. A good test is whether they can repeat back the problem and the benefit.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose one IoT idea from this course.</li>
<li>Write a one-page project plan using the six sections above.</li>
<li>Find the price of at least three components from local or online shops.</li>
<li>Prepare a two-minute spoken explanation of your project and present it to someone.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Project plan</strong>: A document that describes what a project will do, how, and when.</li>
<li><strong>Risk</strong>: Something that could go wrong and affect the project.</li>
<li><strong>Backup plan</strong>: An alternative way to proceed if the main plan fails.</li>
<li><strong>Maker space</strong>: A shared workshop where people can use tools and learn technology skills.</li>
<li><strong>Deployment</strong>: Installing and starting to use a system in its real location.</li>
</ul>

<h2>Summary</h2>
<p>A clear project plan turns an idea into action. It helps you think through the problem, solution, budget, timeline, and risks. Components and help are available in Zambia if you know where to look. Presenting your idea simply is just as important as building it.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.arduino.cc/" target="_blank" rel="noopener">Arduino Official Website</a></li>
<li><a href="https://stackoverflow.com/questions/tagged/iot" target="_blank" rel="noopener">Stack Overflow — IoT Questions</a></li>
<li><a href="https://www.hackster.io/" target="_blank" rel="noopener">Hackster — IoT Projects</a></li>
</ul>
HTML;
    }

    private function module6Quiz(): array
    {
        return [
            'title' => 'Module 6 Quiz: IoT Safety, Privacy, and Business Opportunities',
            'description' => 'Assess your understanding of IoT security, business models, and project planning.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the first security step after setting up a new IoT device?',
                    'explanation' => 'Changing default passwords prevents attackers from using well-known credentials to access the device.',
                    'options' => [
                        ['text' => 'Install a camera', 'is_correct' => false],
                        ['text' => 'Change the default password', 'is_correct' => true],
                        ['text' => 'Paint the device green', 'is_correct' => false],
                        ['text' => 'Delete the instruction manual', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why should IoT data be sent over HTTPS or MQTT-TLS instead of plain HTTP?',
                    'explanation' => 'Encrypted connections prevent attackers from reading or changing data as it travels over the internet.',
                    'options' => [
                        ['text' => 'To make messages larger', 'is_correct' => false],
                        ['text' => 'To encrypt data in transit', 'is_correct' => true],
                        ['text' => 'To reduce battery life', 'is_correct' => false],
                        ['text' => 'To remove the need for a SIM card', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a good IoT business model for a beginner in Zambia?',
                    'explanation' => 'A service model with installation and monthly monitoring creates steady income and helps customers who do not want to maintain devices themselves.',
                    'options' => [
                        ['text' => 'Selling devices only, with no support', 'is_correct' => false],
                        ['text' => 'Installation plus monthly monitoring service', 'is_correct' => true],
                        ['text' => 'Giving everything away for free', 'is_correct' => false],
                        ['text' => 'Importing devices and never testing them', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should a project plan include?',
                    'explanation' => 'A project plan should state the problem, solution, components, budget, timeline, and risks.',
                    'options' => [
                        ['text' => 'Only the brand of the microcontroller', 'is_correct' => false],
                        ['text' => 'Problem, solution, components, budget, timeline, and risks', 'is_correct' => true],
                        ['text' => 'The names of all competitors', 'is_correct' => false],
                        ['text' => 'A list of every website on the internet', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Using default passwords such as "admin" or "1234" is safe if the device is small.',
                    'explanation' => 'Default passwords are easy to guess and should be changed on all devices, regardless of size.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'When presenting an IoT idea, it is best to start by explaining the technical details of the microcontroller.',
                    'explanation' => 'Start with the problem and benefit. Most audiences care more about the value than the technology.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for running software on your own server instead of a third-party cloud service? (Two words)',
                    'explanation' => 'Self-hosting means you control the server and the data stored on it.',
                    'correct_answer' => 'Self hosting',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the process of scrambling data so only authorised parties can read it? (One word)',
                    'explanation' => 'Encryption protects data by converting it into a form that cannot be read without the correct key.',
                    'correct_answer' => 'Encryption',
                ],
            ],
        ];
    }

    private function printSummary(): void
    {
        $moduleCount = Module::where('course_id', $this->courseId)->count();
        $lessonCount = Lesson::whereIn('module_id', Module::where('course_id', $this->courseId)->pluck('id'))->count();
        $quizCount = Quiz::where('course_id', $this->courseId)->count();
        $assignmentCount = Assignment::where('course_id', $this->courseId)->count();

        $this->command->info('IoT content seeded successfully:');
        $this->command->info("  - {$moduleCount} modules");
        $this->command->info("  - {$lessonCount} lessons");
        $this->command->info("  - {$quizCount} quizzes");
        $this->command->info("  - {$assignmentCount} assignments");
    }
}
