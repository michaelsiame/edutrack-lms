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

class IctSupportHardwareRepairContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in ICT Support & Hardware Repair')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in ICT Support & Hardware Repair" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in ICT Support & Hardware Repair already has modules. Skipping content seed.');
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
                'title' => 'Module 1: PC Hardware Essentials and Safe Hands-On Practice',
                'description' => 'Open a desktop tower safely, identify motherboard, CPU, RAM, storage and power components, and learn preventive maintenance habits for dusty Zambian conditions.',
            ],
            [
                'title' => 'Module 2: Diagnosing Common Hardware and Software Faults',
                'description' => 'Troubleshoot no power, no display, overheating, slow Windows, viruses from flash disks, and perform Windows reinstallation while protecting customer data.',
            ],
            [
                'title' => 'Module 3: Peripherals, Networks and Printer Care',
                'description' => 'Install and maintain printers, keyboards, mice and monitors, set up basic wired and wireless networks, and share internet safely in a small office or college lab.',
            ],
            [
                'title' => 'Module 4: Building a Repair Side-Business in a Township',
                'description' => 'Price repairs in Kwacha, source parts from Lusaka and trusted online suppliers, build customer trust, keep records, and launch your own repair desk.',
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
                'title' => '1.1 Opening a Computer Safely and Identifying Parts',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to shut down and unplug a desktop computer safely, remove the side panel without damaging anything, name the major parts inside the case, and explain why safety rules matter when you are working on customer equipment in a Zambian repair shop.</p>

<h2>Why Learn the Inside of a Computer?</h2>
<p>Many people are afraid to open a computer case. They think the parts are too delicate or too complicated. In reality, a desktop tower is a collection of simple building blocks that fit together like Lego. If you want to repair computers for a living in Kalomo, Lusaka, or any township, you must be comfortable opening a case, looking at the motherboard, and swapping parts. Customers will trust you more when they see you work calmly and safely.</p>

<h2>Safety First</h2>
<p>Before you touch anything inside a computer, follow these safety steps every single time:</p>
<ol>
<li>Save the user’s work and shut down Windows properly through the Start menu.</li>
<li>Unplug the power cable from the wall socket. In Zambia, power can return suddenly after load-shedding, and a live motherboard can shock you or destroy a component.</li>
<li>Press the power button once after unplugging. This drains leftover power from the capacitors.</li>
<li>Work on a clean, flat table away from water and direct sunlight.</li>
<li>Touch a metal part of the case before handling circuit boards to discharge static electricity from your body.</li>
</ol>
<p>If you have an anti-static wrist strap, clip it to the metal chassis. If not, touch the metal case frequently, especially before handling RAM, CPU, or expansion cards.</p>

<h2>Opening the Tower</h2>
<p>Most desktop cases have thumbscrews or Phillips screws on the left side panel when you face the front. Remove the screws, slide the panel backwards, and lift it away. Some older cases have a single large cover that lifts off like a lid. Look at the case before forcing anything. If a panel will not move, check for a hidden screw or latch. Never use excessive force.</p>

<h2>What You See Inside</h2>
<p>Once the side panel is off, you will see several main parts:</p>
<ul>
<li><strong>The motherboard</strong> — the large flat circuit board that everything connects to.</li>
<li><strong>The CPU and its heatsink</strong> — a small chip under a metal block with a fan on top.</li>
<li><strong>RAM modules</strong> — long thin circuit boards standing upright in slots.</li>
<li><strong>The storage drive</strong> — a boxy hard disk or a small flat solid-state drive.</li>
<li><strong>The power supply unit (PSU)</strong> — a metal box with many cables, usually at the top or bottom rear.</li>
<li><strong>Expansion cards</strong> — such as a graphics card or network card, plugged into slots on the motherboard.</li>
</ul>

<h2>Worked Example: Identifying Parts on a College Computer</h2>
<p>Mary is asked to name the parts inside a desktop at Edutrack College. She follows the safety steps, removes the side panel, and writes down what she sees. She notes the CPU fan spinning freely, two RAM sticks labelled 4 GB each, a 500 GB hard drive, and a PSU labelled 450 W. She also sees a small graphics card because the monitor cable plugs into the back of it rather than the motherboard. Her instructor checks her list and confirms every item.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Find a desktop computer that you are allowed to inspect. Shut it down and unplug the power cable.</li>
<li>Remove the side panel and lay it aside carefully.</li>
<li>Point to the motherboard, CPU heatsink, RAM sticks, storage drive, and PSU.</li>
<li>Count the RAM sticks and read the capacity printed on each label.</li>
<li>Replace the side panel and screws before plugging the computer back in.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Chassis</strong> — the metal frame and case that hold all computer components.</li>
<li><strong>Motherboard</strong> — the main circuit board that connects the CPU, RAM, storage, and other parts.</li>
<li><strong>PSU</strong> — Power Supply Unit; converts mains electricity to the low voltages needed inside the computer.</li>
<li><strong>Anti-static</strong> — measures that prevent static electricity from damaging sensitive electronic parts.</li>
<li><strong>Expansion card</strong> — a circuit board added to the motherboard to add features such as better graphics.</li>
</ul>

<h2>Summary</h2>
<p>Opening a computer safely is the foundation of hardware repair. Always shut down, unplug, and discharge static before removing the side panel. Inside the case you will find the motherboard, CPU, RAM, storage, PSU, and expansion cards. Learning to identify these parts by sight lets you diagnose problems faster and speak confidently to customers who need their machines fixed.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/windows/">Microsoft Learn — Windows Documentation</a></li>
<li><a href="https://www.w3schools.com/computer/">W3Schools — Computer Basics</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://support.google.com/android/answer/6174145">Google Android Help — Device Basics</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.2 The Motherboard, CPU, RAM and Storage',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe the role of the motherboard, CPU, RAM, and storage in a computer, explain how they work together, and decide which part to upgrade when a customer complains about slowness or lack of space.</p>

<h2>The Motherboard: The Nervous System</h2>
<p>The motherboard is the large printed circuit board that every other component plugs into. It carries power and data between parts. Think of it as the nervous system of the computer. The motherboard has slots for RAM, a socket for the CPU, SATA or M.2 connectors for storage, PCIe slots for graphics cards, and headers for front-panel buttons, USB ports, and fans. When a computer fails, the problem is often on the motherboard or one of its connections.</p>

<h2>The CPU: The Brain</h2>
<p>The Central Processing Unit, or CPU, follows instructions and performs calculations. A faster CPU can open programs more quickly and handle more tasks at once. CPUs are measured by speed in gigahertz and by the number of cores. A dual-core CPU can do two things at once; a quad-core can do four. For a customer who only writes documents and browses the internet, a modest CPU is fine. For video editing or large spreadsheets, a faster CPU helps.</p>

<h2>RAM: Short-Term Memory</h2>
<p>RAM stands for Random Access Memory. It stores data that the computer is using right now. When you open Microsoft Word, a copy of the program loads into RAM. When you close the program, that space is freed. RAM is fast but temporary; everything in RAM disappears when the power goes off. In Zambia, many second-hand computers come with only 2 GB or 4 GB of RAM. Upgrading to 8 GB can transform a slow machine. RAM modules click into slots on the motherboard, and they only fit one way.</p>

<h2>Storage: Long-Term Memory</h2>
<p>Storage keeps files, programs, and the operating system even when the computer is off. There are two main types:</p>
<ul>
<li><strong>HDD (Hard Disk Drive)</strong> — uses spinning magnetic platters. It is cheap and holds a lot of data, but it is slower and easier to damage if dropped.</li>
<li><strong>SSD (Solid State Drive)</strong> — uses flash memory chips. It is much faster, uses less power, and has no moving parts, but it costs more per gigabyte.</li>
</ul>
<p>Replacing an old HDD with an SSD is one of the best upgrades you can sell to a customer. A machine that takes two minutes to start Windows can boot in under thirty seconds after the swap.</p>

<h2>Worked Example: Choosing the Right Upgrade</h2>
<p>A small shop owner in Kalomo says her computer is very slow. You check and find a dual-core CPU, 2 GB of RAM, and a 500 GB hard disk running Windows 10. The CPU is adequate for her work. The best first upgrade is RAM to at least 4 GB or 8 GB. The second upgrade is replacing the hard disk with a 256 GB SSD. You quote her K350 for the SSD and K200 for the RAM, including installation. This is cheaper than buying a new computer and gives her a noticeable speed improvement.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open a desktop computer and locate the CPU heatsink, RAM slots, and storage drive.</li>
<li>Read the labels on the RAM sticks. Note the capacity and speed if printed.</li>
<li>Identify whether the storage drive is an HDD or an SSD by its shape and connectors.</li>
<li>Write down the CPU model if you can read it on the heatsink or by starting Windows and checking System Properties.</li>
<li>Explain to a classmate which upgrade you would recommend for that machine and why.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>CPU</strong> — Central Processing Unit; the chip that carries out instructions and calculations.</li>
<li><strong>RAM</strong> — Random Access Memory; temporary memory used while the computer is running.</li>
<li><strong>HDD</strong> — Hard Disk Drive; storage with spinning platters.</li>
<li><strong>SSD</strong> — Solid State Drive; fast storage with no moving parts.</li>
<li><strong>Core</strong> — an independent processing unit inside a CPU that can handle tasks.</li>
</ul>

<h2>Summary</h2>
<p>The motherboard connects all parts, the CPU processes instructions, RAM provides fast temporary workspace, and storage keeps data permanently. Understanding these four components helps you choose cost-effective repairs and upgrades. For many slow computers in Zambia, adding RAM and swapping an HDD for an SSD gives the biggest improvement for the least money.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/windows/">Microsoft Learn — Windows</a></li>
<li><a href="https://www.w3schools.com/computer/">W3Schools — Computer Basics</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://support.google.com/android/answer/6174145">Google Android Help — Device Basics</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.3 Power Supplies, Cables and Connectors',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to identify common power and data cables inside and outside a computer, understand the difference between internal and external connectors, and safely reconnect everything after a repair.</p>

<h2>The Power Supply Unit</h2>
<p>The PSU takes 220 V alternating current from the ZESCO wall socket and converts it to low-voltage direct current for the computer. A PSU has a rating in watts, such as 300 W, 450 W, or 650 W. The wattage tells you how much power the unit can supply to all components combined. If a customer adds a powerful graphics card, the original PSU may be too small and must be upgraded. A failing PSU can cause random shutdowns, failure to start, or a burning smell.</p>

<h2>Internal Power Connectors</h2>
<p>From the PSU, several types of cables run to components:</p>
<ul>
<li><strong>24-pin ATX connector</strong> — the large plug that powers the motherboard.</li>
<li><strong>4-pin or 8-pin CPU power</strong> — a smaller plug near the CPU socket that supplies extra power to the processor.</li>
<li><strong>SATA power</strong> — flat connectors for hard drives and SSDs.</li>
<li><strong>Molex</strong> — older four-pin connectors used for fans and some drives.</li>
<li><strong>PCIe power</strong> — six-pin or eight-pin connectors for graphics cards.</li>
</ul>
<p>These connectors are keyed, meaning they have shapes or notches that prevent them from being inserted the wrong way. Never force a power connector. If it does not slide in easily, check the orientation.</p>

<h2>Data Cables</h2>
<p>Data cables carry information between the motherboard and storage or other devices. The most common are SATA data cables for hard drives and SSDs. They are thin, flat cables with small L-shaped connectors. Older drives used wide, flat IDE cables. USB cables connect external devices such as keyboards, mice, printers, and phones. Inside the case, front-panel USB ports connect to headers on the motherboard with small plugs.</p>

<h2>External Ports</h2>
<p>At the back of the computer you will find USB ports, HDMI or VGA for the monitor, Ethernet for a network cable, and audio jacks. Knowing these ports helps you connect a monitor, printer, or internet cable correctly. A common mistake is plugging the monitor cable into the motherboard instead of the graphics card, which can lead to “no display” complaints even though the computer is working.</p>

<h2>Worked Example: Reassembling After a Repair</h2>
<p>John upgrades a customer’s hard drive to an SSD. He removes the old SATA data and power cables from the HDD, fits the SSD into the mounting bracket, connects a SATA data cable from the SSD to the motherboard, and connects a SATA power cable from the PSU. He double-checks that the 24-pin motherboard power and 4-pin CPU power are still seated. He replaces the side panel, plugs the monitor into the graphics card, connects the power cord, and starts the computer. The SSD is detected, and he proceeds to install Windows.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Trace the power cable from the wall socket to the PSU, then trace each PSU cable to its component.</li>
<li>Identify the 24-pin motherboard power and the CPU power connector.</li>
<li>Find a SATA data cable and follow it from a storage drive to the motherboard.</li>
<li>Look at the back of the computer and name each port you can see.</li>
<li>Disconnect and reconnect one cable, making sure it clicks into place without forcing it.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>PSU</strong> — Power Supply Unit; converts mains power to voltages used by computer parts.</li>
<li><strong>ATX connector</strong> — the main 24-pin power plug for the motherboard.</li>
<li><strong>SATA</strong> — Serial ATA; a type of cable and connector used for modern storage drives.</li>
<li><strong>PCIe</strong> — a high-speed slot and power connector used for graphics cards and other add-ons.</li>
<li><strong>Keyed connector</strong> — a plug shaped so it can only be inserted correctly.</li>
</ul>

<h2>Summary</h2>
<p>Cables and connectors are the highways of a computer. The PSU supplies power through ATX, CPU, SATA, and PCIe connectors, while SATA and USB cables carry data. External ports let you connect monitors, networks, and peripherals. Learning these connectors lets you assemble, upgrade, and repair machines without guessing where each cable belongs.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/windows/">Microsoft Learn — Windows</a></li>
<li><a href="https://www.w3schools.com/computer/">W3Schools — Computer Basics</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://support.google.com/android/answer/6174145">Google Android Help — Device Basics</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.4 Preventive Maintenance: Dust, Heat and Load-Shedding',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to clean a computer safely, recognise the signs of overheating, protect equipment from power surges after load-shedding, and advise customers on simple habits that extend the life of their machines.</p>

<h2>Dust Is the Enemy</h2>
<p>In Zambia, dust is everywhere, especially during the dry season. Computers have fans that suck in air to cool the CPU, GPU, and power supply. Over time, dust builds up on heatsinks and fans, blocking airflow. A dusty computer runs hotter, becomes slower, and may shut down without warning. Cleaning dust out is one of the cheapest and most profitable services you can offer. Many customers do not realise how much dust is inside their machines.</p>

<h2>Safe Cleaning Tools</h2>
<p>You do not need expensive equipment. A soft brush, a can of compressed air, and a microfibre cloth are enough. Never use a household vacuum cleaner directly on circuit boards; the strong airflow and static can damage components. Instead, use short bursts of compressed air to blow dust out of the case. Hold fans still while you clean them so they do not spin and generate electricity. Wipe the outside of the case with a slightly damp cloth, but keep moisture away from internal parts.</p>

<h2>Signs of Overheating</h2>
<p>Tell customers to watch for these warning signs:</p>
<ul>
<li>The computer feels very hot on the side or bottom.</li>
<li>The fan is always loud even when the computer is idle.</li>
<li>Games or programs crash after a few minutes.</li>
<li>The laptop shuts down suddenly during heavy use.</li>
</ul>
<p>These symptoms usually mean dust is blocking airflow or the thermal paste between the CPU and heatsink has dried out. Reapplying thermal paste is an intermediate repair, but cleaning is the first step.</p>

<h2>Protecting Against Load-Shedding</h2>
<p>Power cuts and surges are common in Zambia. When electricity returns, the voltage can spike and destroy a PSU or motherboard. Teach customers to unplug computers during load-shedding and wait five minutes after power returns before plugging them back in. A surge protector is a worthwhile investment. For businesses, an uninterruptible power supply gives time to save work and shut down safely.</p>

<h2>Worked Example: A Dusty College Lab PC</h2>
<p>A college computer keeps restarting during classes. You open the case and find the CPU heatsink completely covered in dust and the side air vents blocked. You take the computer outside, blow out the dust with compressed air, hold the fans still, and clean the air vents with a brush. After reassembly, the CPU temperature drops by twenty degrees and the random restarts stop. The college pays you K150 for the service and asks you to maintain all twenty machines every term.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Inspect a computer for dust. Check fans, heatsinks, and air vents.</li>
<li>Take the computer outside and blow out dust using short bursts of compressed air.</li>
<li>Hold the fan blades still while cleaning so they do not spin freely.</li>
<li>Wipe the outside case and monitor with a dry or slightly damp cloth.</li>
<li>Write a short checklist that you would give to a customer for protecting their computer during load-shedding.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Heatsink</strong> — a metal block that absorbs heat from the CPU or GPU and radiates it into the air.</li>
<li><strong>Thermal paste</strong> — a compound that improves heat transfer between the CPU and the heatsink.</li>
<li><strong>Surge protector</strong> — a device that absorbs voltage spikes before they reach electronics.</li>
<li><strong>Load-shedding</strong> — planned electricity outages that occur when demand exceeds supply.</li>
<li><strong>Airflow</strong> — the movement of cool air into and hot air out of a computer case.</li>
</ul>

<h2>Summary</h2>
<p>Preventive maintenance saves money. Removing dust, improving airflow, and protecting computers from power surges are simple but valuable services. In Zambia’s dusty climate and unstable power environment, customers who follow these habits will have fewer breakdowns and lower repair bills. As a technician, you can build a steady income by offering regular cleaning and surge-protection advice.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/windows/">Microsoft Learn — Windows</a></li>
<li><a href="https://www.w3schools.com/computer/">W3Schools — Computer Basics</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://support.google.com/android/answer/6174145">Google Android Help — Device Basics</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: PC Hardware Essentials',
            'description' => 'Test your knowledge of computer parts, safety procedures, power connectors, and preventive maintenance.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which tool helps prevent electrostatic discharge when handling a motherboard?',
                    'explanation' => 'An anti-static wrist strap grounds your body so static electricity does not damage sensitive electronic components.',
                    'options' => [
                        ['text' => 'Screwdriver', 'is_correct' => false],
                        ['text' => 'Anti-static wrist strap', 'is_correct' => true],
                        ['text' => 'Heat gun', 'is_correct' => false],
                        ['text' => 'Multimeter', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does RAM stand for?',
                    'explanation' => 'RAM is Random Access Memory, the temporary workspace used by programs while the computer is on.',
                    'options' => [
                        ['text' => 'Readily Available Memory', 'is_correct' => false],
                        ['text' => 'Random Access Memory', 'is_correct' => true],
                        ['text' => 'Real-time Application Memory', 'is_correct' => false],
                        ['text' => 'Remote Access Module', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which component keeps files safe even when the computer is turned off?',
                    'explanation' => 'Storage drives, whether HDD or SSD, retain data without power. RAM loses its contents when power is removed.',
                    'options' => [
                        ['text' => 'RAM', 'is_correct' => false],
                        ['text' => 'CPU', 'is_correct' => false],
                        ['text' => 'HDD or SSD', 'is_correct' => true],
                        ['text' => 'PSU', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the large 24-pin connector from the PSU used for?',
                    'explanation' => 'The 24-pin ATX connector is the main power plug that supplies electricity to the motherboard.',
                    'options' => [
                        ['text' => 'CPU power', 'is_correct' => false],
                        ['text' => 'Motherboard power', 'is_correct' => true],
                        ['text' => 'Graphics card power', 'is_correct' => false],
                        ['text' => 'Monitor power', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which storage upgrade usually gives the biggest speed improvement for an old computer?',
                    'explanation' => 'Replacing a mechanical hard disk with a solid-state drive makes Windows and programs start much faster.',
                    'options' => [
                        ['text' => 'Adding a larger monitor', 'is_correct' => false],
                        ['text' => 'Replacing the CPU', 'is_correct' => false],
                        ['text' => 'Replacing the HDD with an SSD', 'is_correct' => true],
                        ['text' => 'Adding a new keyboard', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A household vacuum cleaner is the best tool for removing dust from inside a computer.',
                    'explanation' => 'Vacuum cleaners can create static electricity and damage components. Compressed air or a soft brush is safer.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'The CPU is commonly called the brain of the computer.',
                    'explanation' => 'The CPU processes instructions and performs calculations, so it is often called the brain.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What should you do to a computer first before opening its case? (one word)',
                    'explanation' => 'Always unplug the power cable before opening a case to avoid electric shock and protect components.',
                    'correct_answer' => 'unplug',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which part converts 220 V mains power to low-voltage DC for computer parts?',
                    'explanation' => 'The Power Supply Unit converts mains electricity to the low voltages needed by the motherboard and other components.',
                    'options' => [
                        ['text' => 'Motherboard', 'is_correct' => false],
                        ['text' => 'PSU', 'is_correct' => true],
                        ['text' => 'CPU', 'is_correct' => false],
                        ['text' => 'RAM', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'You should wait five minutes after electricity returns before plugging a computer back in.',
                    'explanation' => 'Waiting reduces the risk of damage from voltage surges that often occur when power returns after load-shedding.',
                    'correct_answer' => 'True',
                ],
            ],
        ];
    }

    private function module2Lessons(): array
    {
        return [
            [
                'title' => '2.1 No Power, No Display and Beep Codes',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to diagnose a computer that does not turn on, a monitor that shows nothing, and a motherboard that beeps. You will follow a logical step-by-step process instead of guessing.</p>

<h2>Start with the Obvious</h2>
<p>When a customer says their computer has no power, check the simple things first. Is the power cable plugged into the wall and the PSU? Is the wall socket working? Try a different socket or a known working appliance. Is the power switch on the PSU set to the on position? Many towers have a small switch at the back. Is the monitor turned on and set to the correct input? In Zambia, where load-shedding is common, customers often forget that a power cut may have turned off the monitor.</p>

<h2>No Power at All</h2>
<p>If the computer does nothing when you press the power button, follow this order:</p>
<ol>
<li>Check the power cable and socket.</li>
<li>Listen for any fan movement or LED lights when you press the button.</li>
<li>Disconnect all USB devices except the keyboard and mouse. A faulty USB device can prevent booting.</li>
<li>Open the case and check that the 24-pin ATX and CPU power connectors are seated.</li>
<li>Try a different power cable or a known working PSU if one is available.</li>
</ol>
<p>If a known good PSU makes the computer work, the original PSU has failed. This is a common and profitable repair.</p>

<h2>No Display</h2>
<p>If the computer turns on but the monitor shows nothing, the problem is usually the monitor, the cable, or the graphics output. Check that the monitor is on and the cable is secure. Confirm the monitor is plugged into the graphics card, not the motherboard, if a separate graphics card is installed. Try a different cable or monitor. If the machine has a graphics card, reseat it by removing it and pushing it firmly back into the PCIe slot.</p>

<h2>Understanding Beep Codes</h2>
<p>Some motherboards make a series of beeps when they detect a hardware problem. The pattern tells you which part is at fault. One long beep followed by two short beeps often indicates a video problem. Repeated long beeps may mean RAM is loose or missing. The exact meaning depends on the BIOS manufacturer. Search online for the beep code plus the motherboard model. Always write down the beep pattern before searching.</p>

<h2>Worked Example: A Customer’s Dead Desktop</h2>
<p>Mrs Zulu brings a desktop that will not turn on. You check the power cable and socket; both are fine. You open the case and notice the 24-pin ATX connector is slightly loose, probably from moving the machine on a bumpy bus from Lusaka. You unplug it and push it firmly back in until it clicks. You press the power button and the computer starts. Mrs Zulu is relieved that she only needs to pay K80 for labour instead of buying a new computer.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Find a non-working or test desktop. Write down what happens when you press the power button.</li>
<li>Check the power cable, socket, and PSU switch.</li>
<li>Open the case and verify the 24-pin and CPU power connectors.</li>
<li>If the computer turns on but has no display, check the monitor cable and input source.</li>
<li>Listen for beeps during startup and write down the pattern.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>No POST</strong> — when a computer fails the Power On Self Test and does not boot properly.</li>
<li><strong>Beep code</strong> — a series of sounds from the motherboard speaker that indicates a hardware fault.</li>
<li><strong>Reseat</strong> — to remove a component and reinstall it firmly to improve the connection.</li>
<li><strong>BIOS</strong> — Basic Input/Output System; firmware that starts the hardware before Windows loads.</li>
<li><strong>POST</strong> — Power On Self Test; the hardware check a computer performs when turned on.</li>
</ul>

<h2>Summary</h2>
<p>No power and no display problems are common but usually simple. Start with cables, sockets, and switches. Check power connectors inside the case. Listen for beep codes and reseat RAM or graphics cards when needed. A methodical approach saves time, impresses customers, and avoids unnecessary part purchases.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/windows/">Microsoft Learn — Windows</a></li>
<li><a href="https://www.w3schools.com/computer/">W3Schools — Computer Basics</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://support.google.com/android/answer/6174145">Google Android Help — Device Basics</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.2 Overheating, Slow Windows and Virus Removal from Flash Disks',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to identify the causes of overheating and slow performance, remove dust safely, use Windows tools to speed up a computer, and clean malware that spreads through flash disks.</p>

<h2>Overheating and Slowness Go Together</h2>
<p>A hot CPU slows itself down to protect itself. This is called thermal throttling. The result is a computer that feels slow, freezes, or shuts down. In Zambia, dust and high temperatures make overheating very common. Always check dust and fans before assuming the computer needs expensive parts.</p>

<h2>Cleaning Dust the Right Way</h2>
<p>Turn off and unplug the computer. Take it outside if possible. Use short bursts of compressed air to blow dust out of the CPU heatsink, GPU fans, PSU vents, and case filters. Hold fan blades still so they do not spin and create electricity. After cleaning, boot the computer and listen. The fans should be quieter, and the case should feel cooler. If the CPU still overheats, you may need to replace the thermal paste.</p>

<h2>Speeding Up Slow Windows</h2>
<p>After cleaning, use Windows built-in tools to improve performance. Open Task Manager by pressing Ctrl+Shift+Esc. Look at the Startup tab and disable programs that launch automatically but are not needed. Many customers have ten or more programs starting with Windows, which wastes RAM and slows boot time. Next, uninstall unused programs through Settings &gt; Apps. Then run Disk Cleanup to remove temporary files. Finally, check whether the hard disk is nearly full; Windows needs free space to work well.</p>

<h2>Malware from Flash Disks</h2>
<p>Flash disks move between computers, schools, internet cafés, and offices. They are a major way viruses spread in Zambia. Some malware uses the autorun feature to launch automatically when the disk is inserted. Others hide files and create fake shortcuts. Never double-click unknown files on a flash disk. Instead, scan the disk with Windows Security or another reputable antivirus before opening any file.</p>

<h2>Worked Example: Cleaning an Infected College Machine</h2>
<p>A college computer is very slow and keeps showing pop-up advertisements. You open Task Manager and see an unknown program using ninety percent of the CPU. You boot into Safe Mode, run a full scan with Windows Security, and remove three threats. You then clean dust from the CPU heatsink, disable unnecessary startup programs, and uninstall old browser toolbars. The computer boots in under a minute and no longer shows pop-ups. The college pays K120 for the service.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open Task Manager and count how many programs start automatically on a test computer.</li>
<li>Disable two unnecessary startup programs and restart to see if boot time improves.</li>
<li>Run Disk Cleanup and note how much space is freed.</li>
<li>Insert a flash disk and scan it with Windows Security before opening any files.</li>
<li>Check the CPU temperature if software is available, then clean dust and check again.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Thermal throttling</strong> — when a CPU slows down to avoid damage from overheating.</li>
<li><strong>Task Manager</strong> — a Windows tool that shows running programs, performance, and startup items.</li>
<li><strong>Safe Mode</strong> — a limited Windows startup mode used for troubleshooting and malware removal.</li>
<li><strong>Malware</strong> — malicious software such as viruses and spyware that harms a computer.</li>
<li><strong>Autorun</strong> — a Windows feature that can launch software automatically when removable media is inserted.</li>
</ul>

<h2>Summary</h2>
<p>Overheating, slowness, and malware are often connected. Clean dust, improve airflow, manage startup programs, and scan for viruses to bring a slow computer back to life. Flash disks are a major source of infection in Zambia, so always scan before opening. These skills let you solve the most common customer complaints without buying new hardware.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/windows/">Microsoft Learn — Windows</a></li>
<li><a href="https://support.google.com/chrome/answer/95346">Google Chrome Help — Browse the Web</a></li>
<li><a href="https://www.w3schools.com/computer/">W3Schools — Computer Basics</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.3 Reinstalling Windows and Backing Up User Data',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to back up a user’s important files, create Windows installation media, reinstall Windows cleanly, and restore data so that the customer does not lose school work, business records, or family photos.</p>

<h2>Why Reinstall Windows?</h2>
<p>Sometimes a computer is so infected, corrupted, or slow that the best solution is to erase everything and reinstall Windows. This gives the customer a fresh start. However, reinstalling also removes all personal files if you are not careful. Always back up data first. Explain the process clearly and get the customer’s permission in writing. A good technician never loses a customer’s documents.</p>

<h2>Backing Up Before You Begin</h2>
<p>Ask the customer where their important files are. Common locations are the Desktop, Documents, Downloads, Pictures, and Videos folders. Copy these folders to an external hard drive or a large flash disk. If the computer will not boot, you can remove the hard drive and connect it to another machine using a USB-to-SATA adapter. In Zambia, where many people store school assignments and business records on one machine, backups are essential. Encourage customers to keep copies on Google Drive or a flash disk as well.</p>

<h2>Creating Installation Media</h2>
<p>Microsoft provides a free tool called the Media Creation Tool that downloads Windows and creates a bootable USB flash disk. You need a USB drive of at least 8 GB. Download the tool from Microsoft’s official website on a working computer, run it, choose the correct language and edition, and write the files to the USB. Keep this USB safe; it is a valuable tool in any repair business.</p>

<h2>Reinstalling Windows Step by Step</h2>
<ol>
<li>Insert the bootable USB and start the computer.</li>
<li>Enter the boot menu, usually by pressing F12, F10, Esc, or F2 when the computer starts.</li>
<li>Select the USB drive as the boot device.</li>
<li>Follow the Windows setup screens. Choose the language, time, and keyboard.</li>
<li>When asked, select Custom install and choose the drive where Windows will go.</li>
<li>Delete the old Windows partition to start fresh, then install on the unallocated space.</li>
<li>After installation, install drivers and Windows updates.</li>
</ol>
<p>If the computer has a Windows licence, it usually reactivates automatically when connected to the internet. Otherwise, ask the customer for their product key.</p>

<h2>Worked Example: A Fresh Start for a Small Business</h2>
<p>Mr Banda’s shop computer is full of viruses and crashes daily. You back up his Documents folder, ZRA spreadsheets, and customer photos to an external drive. You use a Windows 10 USB you created earlier, reinstall Windows cleanly, and install the latest updates. You copy his files back to the Documents folder and install LibreOffice so he can open his spreadsheets. The computer is stable again. You charge K250 for the backup, reinstall, and data restore. Mr Banda is happy because his business records are safe.</p>

<h2>Try It Yourself</h2>
<ol>
<li>List the folders you would back up on a typical customer computer.</li>
<li>Download the Windows Media Creation Tool on a working computer and create a bootable USB.</li>
<li>Practise booting a test computer from the USB without installing Windows.</li>
<li>Write a simple checklist you would follow before reinstalling Windows.</li>
<li>Explain to a classmate why backing up data is the most important step.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Reinstall</strong> — to erase and install an operating system again to fix serious problems.</li>
<li><strong>Bootable USB</strong> — a flash disk that can start a computer and run installation software.</li>
<li><strong>Partition</strong> — a section of a hard drive treated as a separate drive by the operating system.</li>
<li><strong>Driver</strong> — software that lets Windows communicate with hardware such as graphics cards and printers.</li>
<li><strong>Product key</strong> — a code that proves you have a valid licence for Windows.</li>
</ul>

<h2>Summary</h2>
<p>Reinstalling Windows is a powerful repair tool, but it must be done carefully. Always back up customer data first, create reliable installation media, and follow a clear checklist. After installation, restore files, install updates, and install drivers. Customers will trust you when they see that their documents, photos, and business records are safe.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/windows/">Microsoft Learn — Windows</a></li>
<li><a href="https://support.google.com/chrome/answer/95346">Google Chrome Help — Browse the Web</a></li>
<li><a href="https://www.w3schools.com/computer/">W3Schools — Computer Basics</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.4 Mobile Phone Basics: Screens, Batteries and Charging Ports',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to diagnose common smartphone problems, decide which repairs are worth doing yourself and which should be outsourced, and advise customers on screen, battery, and charging port issues.</p>

<h2>The Economics of Phone Repair</h2>
<p>Smartphones are everywhere in Zambia, but new devices are expensive. Many people would rather repair a cracked screen or failing battery than buy a new phone. As an ICT support technician, you do not need to do every repair with your own hands. You can diagnose the problem, source the part, and either fix it yourself or send it to a specialist while charging a fair margin. The key is honesty and reliable service.</p>

<h2>Cracked Screens</h2>
<p>A cracked screen is the most common phone repair. The glass digitiser and the LCD or OLED display may be separate or bonded together. If the touch still works and only the glass is cracked, the repair is cheaper. If the display is black or has coloured lines, the whole screen assembly must be replaced. Screen replacement requires small screwdrivers, a heat gun or hair dryer to soften adhesive, and patience. For beginners, it is often better to outsource screen replacement and learn by watching.</p>

<h2>Battery Problems</h2>
<p>Phone batteries wear out after two or three years. Signs include fast draining, sudden shutdowns, and a swollen battery. A swollen battery is dangerous because it can catch fire. If you see a swollen battery, stop using the phone immediately and replace it safely. Many modern phones have sealed backs, so opening them requires heat and plastic prying tools. Always dispose of old batteries at a proper collection point, not in ordinary rubbish.</p>

<h2>Charging Port Issues</h2>
<p>If a phone charges only when the cable is held at an angle, the charging port is probably loose or full of pocket lint. First, shine a light into the port and carefully remove lint with a wooden toothpick or plastic tool. Do not use metal pins because they can damage the port. If cleaning does not help, the port may need replacement. Charging port replacement is a common repair that can be learned with practice.</p>

<h2>Worked Example: A Student’s Broken Phone</h2>
<p>A student drops her Android phone and the screen cracks. The touch still works, but glass shards are cutting her finger. You check the model number and find a replacement screen assembly online for K280. A specialist in Lusaka charges K150 for fitting. You quote the student K500 total, including the part, labour, and your sourcing fee. She agrees, you order the part, send it to the specialist, and return a working phone within a week. She tells her classmates, and three more students ask for quotes.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Inspect a phone charging port with a torch and remove any visible lint carefully.</li>
<li>Check the battery health setting on an Android phone if available.</li>
<li>Look up the price of a replacement screen for a common phone model sold in Zambia.</li>
<li>Write a short price list for screen, battery, and charging port repairs in your area.</li>
<li>Discuss with a classmate which phone repairs you would do yourself and which you would outsource.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Digitiser</strong> — the layer on a touchscreen that detects finger touches.</li>
<li><strong>LCD/OLED</strong> — types of display panels used in phones and monitors.</li>
<li><strong>Swollen battery</strong> — a battery that has expanded due to damage or age and may be dangerous.</li>
<li><strong>Charging port</strong> — the socket where the charging cable connects to the phone.</li>
<li><strong>Adhesive</strong> — sticky material that holds modern phone screens and backs in place.</li>
</ul>

<h2>Summary</h2>
<p>Phone repair is a valuable add-on service for any computer technician. Learn to diagnose screen, battery, and charging port problems. Some repairs can be outsourced while you handle customer service and sourcing. Always be honest about what you can and cannot do, and charge fairly for parts and labour. Word of mouth will grow your business.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/android/answer/6174145">Google Android Help — Device Basics</a></li>
<li><a href="https://support.google.com/googleplay/answer/2521768">Google Play Help — Install Apps</a></li>
<li><a href="https://www.w3schools.com/computer/">W3Schools — Computer Basics</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Fault Diagnosis and Software Repair',
            'description' => 'Test your knowledge of no power, no display, overheating, malware removal, Windows reinstallation, and phone repair economics.',
            'time_limit_minutes' => 25,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'A desktop turns on but the monitor shows nothing. What should you check first?',
                    'explanation' => 'Check simple things first: power to the monitor, correct input source, and that the cable is connected to the right port.',
                    'options' => [
                        ['text' => 'Replace the motherboard', 'is_correct' => false],
                        ['text' => 'Check the monitor cable and input source', 'is_correct' => true],
                        ['text' => 'Reinstall Windows', 'is_correct' => false],
                        ['text' => 'Buy a new PSU', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does a series of beeps during startup usually indicate?',
                    'explanation' => 'Beep codes are sounds produced by the motherboard to report specific hardware faults.',
                    'options' => [
                        ['text' => 'A software update is available', 'is_correct' => false],
                        ['text' => 'A hardware fault', 'is_correct' => true],
                        ['text' => 'The internet is disconnected', 'is_correct' => false],
                        ['text' => 'The monitor is too bright', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which Windows tool shows startup programs and lets you disable them?',
                    'explanation' => 'Task Manager has a Startup tab where you can enable or disable programs that run when Windows starts.',
                    'options' => [
                        ['text' => 'File Explorer', 'is_correct' => false],
                        ['text' => 'Task Manager', 'is_correct' => true],
                        ['text' => 'Calculator', 'is_correct' => false],
                        ['text' => 'Notepad', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which type of media is a common source of malware in Zambia?',
                    'explanation' => 'Flash disks move between many computers and are a major way viruses spread.',
                    'options' => [
                        ['text' => 'Ethernet cables', 'is_correct' => false],
                        ['text' => 'Flash disks', 'is_correct' => true],
                        ['text' => 'HDMI cables', 'is_correct' => false],
                        ['text' => 'Power cables', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Reinstalling Windows always deletes all personal files on the computer.',
                    'explanation' => 'You can choose to keep files during some reinstalls, but a clean install removes data unless you back it up first.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A swollen phone battery should be replaced immediately and disposed of safely.',
                    'explanation' => 'Swollen batteries can be dangerous and may catch fire. They must be replaced and disposed of properly.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the name of the limited Windows mode used to remove malware? (two words)',
                    'explanation' => 'Safe Mode starts Windows with only essential drivers and services, making it easier to remove malware.',
                    'correct_answer' => 'Safe Mode',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should you do before reinstalling Windows on a customer’s computer?',
                    'explanation' => 'Always back up important files before reinstalling to avoid losing customer data.',
                    'options' => [
                        ['text' => 'Delete all partitions immediately', 'is_correct' => false],
                        ['text' => 'Back up important data', 'is_correct' => true],
                        ['text' => 'Install new antivirus first', 'is_correct' => false],
                        ['text' => 'Format the USB drive', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which repair is usually best outsourced when you are beginning?',
                    'explanation' => 'Screen replacement requires specialised tools and skills; many technicians outsource it while learning.',
                    'options' => [
                        ['text' => 'Cleaning dust from a PC', 'is_correct' => false],
                        ['text' => 'Phone screen replacement', 'is_correct' => true],
                        ['text' => 'Uninstalling malware', 'is_correct' => false],
                        ['text' => 'Checking monitor cables', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Thermal throttling happens when a CPU slows down to protect itself from heat.',
                    'explanation' => 'Thermal throttling reduces CPU speed to prevent overheating damage.',
                    'correct_answer' => 'True',
                ],
            ],
        ];
    }

    private function module3Lessons(): array
    {
        return [
            [
                'title' => '3.1 Printer Installation, Maintenance and Clearing Paper Jams',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to install a printer on Windows, perform routine maintenance, clear paper jams safely, and advise a small office or school on keeping printers running reliably.</p>

<h2>Types of Printers</h2>
<p>The two most common printer types are inkjet and laser. Inkjet printers spray tiny droplets of liquid ink onto paper. They are cheap to buy but expensive to run because ink cartridges are small and costly. Laser printers use toner powder and a heated drum. They cost more upfront but are cheaper per page and better for high-volume printing. In a Zambian school or small business, a laser printer is usually the better long-term choice.</p>

<h2>Installing a Printer</h2>
<p>Connect the printer to power and turn it on. Connect it to the computer with a USB cable, or connect it to the network with an Ethernet cable or Wi-Fi. Windows usually detects USB printers automatically. For network printers, you may need to know the printer’s IP address, which you can find in the printer’s network settings menu. Install the correct driver from the manufacturer’s website rather than relying on generic drivers. Test with a simple page before handing the printer back to the customer.</p>

<h2>Clearing Paper Jams</h2>
<p>Paper jams are frustrating but normal. Always turn the printer off before removing jammed paper. Open the access panels and remove toner or ink cartridges if needed. Pull the paper gently in the direction of the paper path; do not rip it. If a small piece tears off, use a torch to find it, because leftover paper will cause more jams. After clearing the jam, close all panels, turn the printer on, and print a test page.</p>

<h2>Routine Maintenance</h2>
<p>Printers need regular care. Clean the paper feed rollers with a lint-free cloth slightly dampened with water. Replace toner or ink before it runs completely dry. Update the printer firmware if the manufacturer recommends it. Keep the printer in a clean, dry place because dust and humidity damage paper and internal parts. For offices near dusty roads, cover the printer when it is not in use.</p>

<h2>Worked Example: Fixing the School Printer</h2>
<p>The printer at a community school keeps jamming. You open it and find a torn piece of paper stuck near the rollers. You remove it, clean the rollers, and fan the paper tray before reloading. You also notice the toner is low and warn the office. You charge K100 for the service call and leave a note about ordering toner. The printer works reliably for the rest of the term.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Install a printer on a Windows computer using a USB cable and print a test page.</li>
<li>Find the printer’s IP address if it is a network printer.</li>
<li>Practise clearing a simulated paper jam by following the manufacturer’s instructions.</li>
<li>Clean the paper feed rollers with a soft cloth.</li>
<li>Write a monthly maintenance checklist for a small office printer.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Inkjet</strong> — a printer that sprays liquid ink onto paper.</li>
<li><strong>Laser printer</strong> — a printer that uses toner powder and heat to print pages.</li>
<li><strong>Toner</strong> — dry powder used in laser printers instead of liquid ink.</li>
<li><strong>Driver</strong> — software that lets Windows communicate with the printer.</li>
<li><strong>Paper path</strong> — the route paper takes through the printer.</li>
</ul>

<h2>Summary</h2>
<p>Printers are essential in schools and offices. Choose the right type for the workload, install the correct driver, clear jams gently, and perform regular maintenance. Reliable printers make your customers happy and reduce emergency call-outs.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/windows/">Microsoft Learn — Windows</a></li>
<li><a href="https://support.google.com/chrome/answer/95346">Google Chrome Help — Browse the Web</a></li>
<li><a href="https://www.w3schools.com/computer/">W3Schools — Computer Basics</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.2 Keyboards, Mice, Monitors and Projectors',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to troubleshoot common problems with keyboards, mice, monitors, and projectors, clean them safely, and connect them correctly in a classroom or office.</p>

<h2>Keyboards and Mice</h2>
<p>Keyboards and mice are input devices. Most modern models connect by USB, while older ones use PS/2 round connectors. Wireless keyboards and mice use a small USB receiver or Bluetooth. Common problems include stuck keys, unresponsive buttons, dirt under keys, and flat batteries in wireless devices. Spilled drinks are a major cause of keyboard death; advise customers to keep drinks away from computers.</p>

<h2>Cleaning a Keyboard</h2>
<p>Unplug the keyboard or remove batteries. Turn it upside down and tap gently to remove loose debris. Use a soft brush or compressed air between keys. Wipe the key surfaces with a slightly damp cloth. Do not soak the keyboard because water can damage the circuit board. For deep cleaning, remove keycaps carefully with a flat tool, clean underneath, and press the caps back on.</p>

<h2>Monitors</h2>
<p>A monitor can fail to show a picture for many reasons. Check power, brightness, input source, and cable. Try a different cable or computer to see whether the monitor or the computer is at fault. Dead pixels, flickering, or colour lines usually mean the monitor needs replacement. Clean the screen with a dry microfibre cloth. Do not spray liquid directly onto the screen.</p>

<h2>Projectors</h2>
<p>Projectors are common in schools and churches. They need clean air filters and cool ventilation. A clogged filter causes overheating and shortens the lamp life. Replace the lamp when the image becomes dim. Connect the projector to the computer with HDMI or VGA and select the correct input. If the image is the wrong shape, adjust the aspect ratio in the projector menu.</p>

<h2>Worked Example: The Fuzzy Classroom Projector</h2>
<p>A teacher complains that the projector image is dim and yellow. You remove the filter and find it clogged with dust. You clean it with water, let it dry, and reinstall it. The projector runs cooler and the image improves slightly, but it is still dim. You explain that the lamp is old and should be replaced. You source a compatible lamp for K450 and install it. The classroom now has a bright, clear image.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Disconnect and reconnect a USB keyboard and mouse.</li>
<li>Clean a keyboard by turning it upside down and using compressed air.</li>
<li>Check the input source on a monitor and switch between HDMI and VGA.</li>
<li>Locate the air filter on a projector and clean it according to the manual.</li>
<li>Write a short guide for teachers on how to keep projectors working well.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Input device</strong> — a piece of hardware used to send data into a computer, such as a keyboard or mouse.</li>
<li><strong>Dead pixel</strong> — a tiny dot on a screen that does not display the correct colour.</li>
<li><strong>Aspect ratio</strong> — the proportional relationship between the width and height of an image.</li>
<li><strong>Air filter</strong> — a removable mesh that stops dust entering a projector or computer.</li>
<li><strong>HDMI</strong> — a common digital cable for video and audio.</li>
</ul>

<h2>Summary</h2>
<p>Input devices and displays are simple but important. Clean them regularly, check connections and input sources, and replace consumables such as projector lamps on time. These small maintenance tasks keep classrooms, offices, and churches running smoothly.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/windows/">Microsoft Learn — Windows</a></li>
<li><a href="https://support.google.com/chrome/answer/95346">Google Chrome Help — Browse the Web</a></li>
<li><a href="https://www.w3schools.com/computer/">W3Schools — Computer Basics</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.3 Basic Networking: Cables, Wi-Fi and Sharing Internet',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to set up a small wired or wireless network, connect a router, share an internet connection safely, and solve common network problems in a home or small office.</p>

<h2>What Is a Network?</h2>
<p>A network connects computers and devices so they can share files, printers, and internet access. A small office network usually has a router that connects to the internet through an ISP, plus cables or Wi-Fi that connect computers and phones. In Zambia, many small businesses use a router from Airtel, MTN, or Zamtel that combines a modem and Wi-Fi in one box.</p>

<h2>Wired vs Wireless</h2>
<p>Wired connections use Ethernet cables with RJ45 connectors. They are faster and more reliable than Wi-Fi because they are not affected by walls or interference. Wireless connections use radio signals and are convenient for phones, laptops, and places where running cables is difficult. A good small office uses wired connections for desktops and Wi-Fi for mobile devices.</p>

<h2>Connecting a Router</h2>
<p>Place the router in a central, raised location away from metal objects. Connect the WAN or internet port to the modem or fibre box. Connect LAN ports to desktop computers with Ethernet cables. Power on the router and wait for the lights to stabilise. Access the router settings by typing its IP address, often 192.168.1.1 or 192.168.0.1, into a browser. Change the default admin password and set a strong Wi-Fi password. Use WPA2 or WPA3 security, not WEP.</p>

<h2>Sharing Internet Safely</h2>
<p>Change the default router password to prevent attackers from taking control. Hide the SSID if the customer wants extra privacy, though this is not essential. Set a guest network for visitors so they do not access business files. Keep the router firmware updated. In a Zambian context, where many people share one connection, set bandwidth limits if the router supports them so one user does not slow everyone else down.</p>

<h2>Worked Example: Connecting a Small Office</h2>
<p>A shop in Kalomo wants internet for two desktops and three phones. You connect the ISP router to the wall socket. You run Ethernet cables to the two desktops and configure the Wi-Fi name and password. You set the router admin password to something strong and disable WPS. You test that all devices can reach the internet. You charge K200 for setup and leave a note with the Wi-Fi password and admin password stored safely.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Identify the WAN and LAN ports on a router.</li>
<li>Connect a computer to the router with an Ethernet cable and check that it gets internet.</li>
<li>Log in to the router settings and change the Wi-Fi password.</li>
<li>Check the security mode and make sure it is set to WPA2 or WPA3.</li>
<li>Draw a simple network diagram for a small office with two desktops and four phones.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Router</strong> — a device that forwards data between computers and the internet.</li>
<li><strong>Ethernet</strong> — a wired networking technology that uses cables with RJ45 plugs.</li>
<li><strong>RJ45</strong> — the plastic connector on the end of an Ethernet cable.</li>
<li><strong>SSID</strong> — the name of a Wi-Fi network.</li>
<li><strong>WPA2/WPA3</strong> — security standards that protect Wi-Fi networks with encryption.</li>
</ul>

<h2>Summary</h2>
<p>Networking connects devices and shares internet access. Use wired connections where possible and secure Wi-Fi with strong passwords and modern encryption. A well-configured router keeps a small office productive and protects it from unauthorised access. Network setup is a valuable service you can offer to schools, shops, and churches.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://learn.microsoft.com/en-us/windows/">Microsoft Learn — Windows</a></li>
<li><a href="https://support.google.com/android/answer/6174145">Google Android Help — Device Basics</a></li>
<li><a href="https://www.w3schools.com/computer/">W3Schools — Computer Basics</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Peripherals and Networking',
            'description' => 'Test your knowledge of printer maintenance, monitors, projectors, keyboards, mice, and basic networking.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which printer type usually has a lower cost per page over time?',
                    'explanation' => 'Laser printers use toner and are cheaper per page for high-volume printing than inkjet printers.',
                    'options' => [
                        ['text' => 'Inkjet printer', 'is_correct' => false],
                        ['text' => 'Laser printer', 'is_correct' => true],
                        ['text' => 'Dot matrix printer', 'is_correct' => false],
                        ['text' => 'Thermal printer', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should you do first when clearing a paper jam?',
                    'explanation' => 'Turn the printer off to avoid injury and damage while removing jammed paper.',
                    'options' => [
                        ['text' => 'Pull the paper quickly', 'is_correct' => false],
                        ['text' => 'Turn the printer off', 'is_correct' => true],
                        ['text' => 'Shake the printer', 'is_correct' => false],
                        ['text' => 'Remove the toner first', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which cable is commonly used for wired Ethernet networks?',
                    'explanation' => 'Ethernet cables have RJ45 connectors and are used to connect computers to routers or switches.',
                    'options' => [
                        ['text' => 'HDMI cable', 'is_correct' => false],
                        ['text' => 'RJ45 Ethernet cable', 'is_correct' => true],
                        ['text' => 'VGA cable', 'is_correct' => false],
                        ['text' => 'USB cable', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should you clean on a projector to prevent overheating?',
                    'explanation' => 'Clogged air filters reduce airflow and cause projectors to overheat.',
                    'options' => [
                        ['text' => 'The lens with alcohol', 'is_correct' => false],
                        ['text' => 'The air filter', 'is_correct' => true],
                        ['text' => 'The power cable', 'is_correct' => false],
                        ['text' => 'The remote control', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which security standard should you use for a Wi-Fi network?',
                    'explanation' => 'WPA2 and WPA3 are modern, secure Wi-Fi encryption standards. WEP is outdated and unsafe.',
                    'options' => [
                        ['text' => 'WEP', 'is_correct' => false],
                        ['text' => 'WPA2 or WPA3', 'is_correct' => true],
                        ['text' => 'HTTP', 'is_correct' => false],
                        ['text' => 'FTP', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'You should spray cleaning liquid directly onto a monitor screen.',
                    'explanation' => 'Liquid can seep into the monitor and damage it. Use a slightly damp cloth instead.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A router connects multiple devices to the internet and to each other.',
                    'explanation' => 'A router forwards data between devices on a local network and the internet.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the name of a Wi-Fi network called? (three letters)',
                    'explanation' => 'The SSID is the broadcast name that identifies a Wi-Fi network.',
                    'correct_answer' => 'SSID',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why should a small office change the default router admin password?',
                    'explanation' => 'Default passwords are public knowledge, so changing them protects the router from unauthorised access.',
                    'options' => [
                        ['text' => 'To make Wi-Fi faster', 'is_correct' => false],
                        ['text' => 'To prevent attackers from controlling the router', 'is_correct' => true],
                        ['text' => 'To hide the network name', 'is_correct' => false],
                        ['text' => 'To reduce electricity use', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Wired Ethernet is usually more reliable than Wi-Fi because it is not affected by walls or interference.',
                    'explanation' => 'Ethernet cables provide a stable connection without wireless interference.',
                    'correct_answer' => 'True',
                ],
            ],
        ];
    }

    private function module4Lessons(): array
    {
        return [
            [
                'title' => '4.1 Pricing Repairs and Estimating Parts in Kwacha',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to set fair prices for common repairs, estimate the cost of parts, build a simple price list in Kwacha, and explain your charges clearly to customers.</p>

<h2>Why Pricing Matters</h2>
<p>Many new technicians are unsure what to charge. If you charge too little, you will struggle to pay for transport, parts, and tools. If you charge too much, customers will go elsewhere. Fair pricing is based on three things: the cost of parts, the time and skill required, and what customers in your area can afford. In a township, a transparent price list builds trust faster than fancy marketing.</p>

<h2>The Parts Cost</h2>
<p>Always know the real cost of any part before you quote. Ask suppliers in Lusaka, check reputable online shops, or call other technicians. Add a small margin to cover your time sourcing the part and the risk if the part is wrong. A common markup is between twenty and forty percent. If a screen costs K300, you might charge the customer K360 to K420. Do not hide the part cost; customers respect honesty.</p>

<h2>The Labour Cost</h2>
<p>Labour is your skill and time. Charge a flat rate for common jobs and an hourly rate for complicated diagnostics. For example, you might charge K80 for cleaning dust, K150 for installing Windows, and K200 for diagnosing a motherboard fault. If a job takes longer than expected, communicate with the customer before doing extra work. Never surprise a customer with a higher bill.</p>

<h2>A Sample Price List</h2>
<p>Here is a simple price list for a repair desk in Kalomo:</p>
<ul>
<li>Computer dust cleaning and check-up — K80</li>
<li>Windows installation with data backup — K250</li>
<li>Virus removal and cleanup — K120</li>
<li>RAM upgrade labour — K50</li>
<li>SSD upgrade labour — K100</li>
<li>Phone screen replacement sourcing and fitting — K400 to K700 depending on model</li>
<li>Charging port cleaning — K40</li>
<li>Network setup for small office — K200</li>
</ul>
<p>Adjust these prices based on your location, experience, and competition.</p>

<h2>Worked Example: Quoting a Laptop Upgrade</h2>
<p>A customer wants her laptop to be faster. You check and recommend upgrading the 4 GB RAM to 8 GB and replacing the hard disk with a 256 GB SSD. The RAM costs K180 from a Lusaka supplier, and the SSD costs K320. You add a twenty percent parts margin, bringing parts to K600. Labour for both upgrades is K150. You quote K750 total. The customer agrees because it is cheaper than a new laptop. You complete the work in two hours and earn a fair profit.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Call or message two suppliers and ask for prices of a 256 GB SSD and 8 GB laptop RAM.</li>
<li>Create a price list for five common services you can offer.</li>
<li>Calculate the total price for a RAM and SSD upgrade including a twenty percent parts margin.</li>
<li>Write a short explanation you would give to a customer about why your prices are fair.</li>
<li>Compare your prices with another technician in your area if possible.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Markup</strong> — the amount added to the cost price to cover expenses and profit.</li>
<li><strong>Labour</strong> — the charge for a technician’s time and skill.</li>
<li><strong>Quote</strong> — an estimate of how much a repair will cost before the work begins.</li>
<li><strong>Margin</strong> — the difference between the cost of a part and the selling price.</li>
<li><strong>Diagnostic fee</strong> — a charge for finding out what is wrong before repair.</li>
</ul>

<h2>Summary</h2>
<p>Fair pricing is the foundation of a sustainable repair business. Know your part costs, add a reasonable margin, charge for your time, and communicate clearly. A simple price list in Kwacha helps customers trust you and makes your business look professional.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/">Grow with Google — Free Digital Skills Training</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://www.w3schools.com/computer/">W3Schools — Computer Basics</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Training</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.2 Sourcing Parts from Lusaka and Online Marketplaces',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to find reliable suppliers for computer and phone parts, avoid fake or wrong parts, compare prices, and manage delivery from Lusaka or online marketplaces.</p>

<h2>Where to Buy Parts</h2>
<p>In Zambia, parts come from several sources. Local shops in major towns may sell basic items such as cables, mice, and keyboards. Lusaka has larger markets and specialist computer shops with a wider range of laptop screens, RAM, SSDs, and phone parts. Online marketplaces such as Jumia, local Facebook groups, and WhatsApp sellers can also supply parts. Each source has advantages and risks.</p>

<h2>Choosing a Supplier</h2>
<p>A good supplier offers genuine parts, clear prices, a return policy, and reliable delivery. Before buying, ask other technicians for recommendations. Check whether the seller provides a warranty or at least allows you to return wrong items. Be careful of prices that are far below the market rate; they may indicate fake or stolen goods. Always ask for the exact model number and check compatibility.</p>

<h2>Avoiding Fake Parts</h2>
<p>Fake memory sticks, fake chargers, and low-quality batteries are common. Signs of fake goods include misspelled labels, flimsy packaging, missing serial numbers, and prices that seem too good to be true. For laptop chargers, the wrong voltage or amperage can damage the laptop. For batteries, poor quality can be dangerous. Buy from trusted sellers even if they cost a little more.</p>

<h2>Managing Delivery</h2>
<p>If you order from Lusaka, ask whether the supplier can send parts by bus or courier to Kalomo. Make sure the package is clearly labelled and insured if possible. For small items, a trusted traveller can carry them. Always test parts as soon as they arrive. If a part is wrong or dead, contact the supplier immediately. Keep records of every order so you can track spending and identify reliable suppliers.</p>

<h2>Worked Example: Sourcing a Screen</h2>
<p>You need a screen for an HP laptop. A local shop quotes K850 but does not have stock. A Lusaka supplier quotes K620 and can send it by bus for K50. An online seller offers K450 but has no reviews and no warranty. You choose the Lusaka supplier because the saving is worth the delivery fee and the supplier has a seven-day return policy. You pass K670 to the customer as the part cost plus your sourcing margin.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Find three suppliers for one common part such as a 256 GB SSD or laptop RAM.</li>
<li>Compare their prices, warranty terms, and delivery options.</li>
<li>Write a list of warning signs that a part might be fake.</li>
<li>Ask a local technician which supplier they trust and why.</li>
<li>Create a simple order record sheet to track parts you buy.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Genuine part</strong> — a component made by or approved by the original manufacturer.</li>
<li><strong>Compatibility</strong> — whether a part works correctly with a specific model of computer or phone.</li>
<li><strong>Aftermarket</strong> — parts made by third-party companies, often cheaper than original parts.</li>
<li><strong>Warranty</strong> — a promise from the seller to repair or replace a faulty part within a set time.</li>
<li><strong>Courier</strong> — a service that delivers packages between towns or countries.</li>
</ul>

<h2>Summary</h2>
<p>Reliable parts are the backbone of repair work. Compare suppliers, check compatibility, avoid fakes, and keep good records. Building relationships with trusted sellers in Lusaka and online saves money and protects your reputation.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/">Grow with Google — Free Digital Skills Training</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://www.w3schools.com/computer/">W3Schools — Computer Basics</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Training</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.3 Customer Trust, Warranties and Record Keeping',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to build trust with customers, offer simple warranties, keep repair records, and handle complaints professionally.</p>

<h2>Trust Is Everything</h2>
<p>In a township, your reputation travels fast. A satisfied customer tells friends and family. An angry customer tells even more people. Trust comes from honesty, clear communication, and keeping your word. If you say a repair will be ready on Friday, make it ready on Friday. If a problem is worse than expected, call the customer immediately and explain the options. Never promise what you cannot deliver.</p>

<h2>Clear Communication</h2>
<p>Explain the problem in simple language. Avoid jargon such as “the northbridge is failing” unless the customer asks for details. Tell them the likely cost, how long the repair will take, and what could go wrong. Get their approval before buying expensive parts. Write everything down on a job card or receipt. A clear record protects both you and the customer.</p>

<h2>Warranties</h2>
<p>A warranty is your promise that the repair will work for a certain time. A common warranty is seven to thirty days on labour, depending on the job. Parts usually carry the supplier’s warranty. Make sure the customer understands what is covered. For example, you might say, “The new SSD has a one-year supplier warranty. My labour is guaranteed for fourteen days if the same problem returns.” Put this in writing to avoid arguments.</p>

<h2>Record Keeping</h2>
<p>Keep a simple log of every repair. Write the date, customer name, phone number, device model, problem, work done, parts used, price, and warranty. A notebook or a spreadsheet is enough to start. Good records help you track income, follow up with customers, and prove what was agreed if there is a dispute. They also show you which repairs are most profitable.</p>

<h2>Worked Example: A Warranty Dispute</h2>
<p>A customer returns a week after a Windows installation and says the computer is slow again. You check your job card and see that you cleaned dust and reinstalled Windows. You discover the customer has been using infected flash disks. You explain politely that the slowness is caused by new malware, not your work. You offer to clean it again for a reduced fee. Because you wrote a clear job card, the customer accepts your explanation and recommends you to others.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Design a simple job card form for your repair desk.</li>
<li>Write a warranty statement for one common service.</li>
<li>Role-play explaining a repair to a customer using only simple words.</li>
<li>Create a spreadsheet to track five imaginary repair jobs.</li>
<li>List three ways to build trust with customers in your community.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Job card</strong> — a document that records details of a repair job.</li>
<li><strong>Warranty</strong> — a guarantee that a repair or part will work for a set period.</li>
<li><strong>Jargon</strong> — technical language that customers may not understand.</li>
<li><strong>Labour warranty</strong> — a guarantee covering the technician’s work rather than the parts.</li>
<li><strong>Dispute</strong> — a disagreement between a customer and a technician about a repair.</li>
</ul>

<h2>Summary</h2>
<p>Trust is built through honest communication, written agreements, and keeping good records. Offer fair warranties, explain repairs clearly, and document every job. Customers who trust you will return and refer others, which is the best form of advertising.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/">Grow with Google — Free Digital Skills Training</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://www.w3schools.com/computer/">W3Schools — Computer Basics</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Training</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.4 Starting Your Township Repair Desk — Business Plan',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to write a simple business plan for a repair desk, choose a location, decide what services to offer, advertise cheaply, and manage your money responsibly.</p>

<h2>Why a Repair Desk?</h2>
<p>A repair desk is a small, focused business that can start with very little capital. You do not need a big shop at first. A table in a trusted shop, a corner at home, or a mobile service where you visit customers can all work. Many successful technicians in Zambia began by helping friends and neighbours and grew by word of mouth. The demand is there because computers and phones break every day.</p>

<h2>Your Business Plan</h2>
<p>A simple business plan answers four questions:</p>
<ol>
<li><strong>What will you sell?</strong> Services such as cleaning, virus removal, Windows installation, RAM and SSD upgrades, phone screen sourcing, charging port cleaning, and network setup.</li>
<li><strong>Who will buy?</strong> Students, small businesses, schools, churches, and households in your area.</li>
<li><strong>How much will it cost to start?</strong> Basic tools, a few spare cables, a diagnostic USB, and advertising. You can begin with under K1,000.</li>
<li><strong>How will you make money?</strong> Charge for labour and add a margin on parts. Track income and expenses so you know your profit.</li>
</ol>

<h2>Advertising on a Budget</h2>
<p>You do not need paid radio ads. Use free or cheap methods. Create a simple poster with your services, phone number, and WhatsApp number. Ask shop owners if you can put it on their notice boards. Join local WhatsApp groups and PTA groups and post helpful tips occasionally, not just adverts. Ask happy customers to tell their friends. A satisfied customer is your best marketer.</p>

<h2>Managing Money</h2>
<p>Keep business money separate from personal money if possible. Record every kwacha you receive and spend. Save some profit for buying tools and parts. Pay yourself a small wage rather than spending everything. If your business grows, consider registering for a ZRA TPIN and opening a mobile money business account. Acting professionally from the start makes growth easier.</p>

<h2>Worked Example: Chanda’s Repair Desk</h2>
<p>Chanda starts a repair desk with K800. She buys a screwdriver set, a can of compressed air, a multimeter, a USB-to-SATA adapter, and some phone opening tools. She offers cleaning, Windows installation, virus removal, and phone screen sourcing. She prints twenty posters and asks local shops to display them. In the first month, she earns K1,400 and spends K600 on parts and transport. She reinvests K400 in tools and keeps K400 as savings. By month three, she has regular customers and adds network setup to her services.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write a one-page business plan for your own repair desk.</li>
<li>List the tools you would buy with K1,000 and their estimated prices.</li>
<li>Design a simple poster or WhatsApp advert for your services.</li>
<li>Create a monthly budget showing expected income and expenses.</li>
<li>Identify three places in your community where you could advertise for free.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Capital</strong> — the money used to start and run a business.</li>
<li><strong>Profit</strong> — the money left after all expenses are paid.</li>
<li><strong>Reinvest</strong> — to put profit back into the business to help it grow.</li>
<li><strong>TPIN</strong> — Taxpayer Identification Number from ZRA.</li>
<li><strong>Target market</strong> — the group of customers most likely to buy your services.</li>
</ul>

<h2>Summary</h2>
<p>Starting a repair desk is one of the most practical ways to turn your ICT skills into income. Write a simple plan, start small, advertise cheaply, and manage your money well. Serve customers honestly, and your township repair desk can grow into a respected business.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/">Grow with Google — Free Digital Skills Training</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
<li><a href="https://www.w3schools.com/computer/">W3Schools — Computer Basics</a></li>
<li><a href="https://learn.microsoft.com/en-us/training/">Microsoft Learn — Training</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module4Quiz(): array
    {
        return [
            'title' => 'Module 4 Quiz: Building a Repair Business',
            'description' => 'Test your knowledge of pricing, sourcing parts, customer trust, warranties, record keeping, and starting a repair desk.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which is the best way to set fair repair prices?',
                    'explanation' => 'Fair prices consider part costs, labour time, and what local customers can afford.',
                    'options' => [
                        ['text' => 'Charge the highest price possible', 'is_correct' => false],
                        ['text' => 'Copy prices from another country', 'is_correct' => false],
                        ['text' => 'Consider parts, labour, and local affordability', 'is_correct' => true],
                        ['text' => 'Always charge less than everyone else', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why should you avoid parts that are far cheaper than the market rate?',
                    'explanation' => 'Very cheap parts may be fake, stolen, or low quality and can damage customer devices.',
                    'options' => [
                        ['text' => 'They are always better', 'is_correct' => false],
                        ['text' => 'They may be fake or faulty', 'is_correct' => true],
                        ['text' => 'They are too heavy', 'is_correct' => false],
                        ['text' => 'They cannot be delivered', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main purpose of a job card?',
                    'explanation' => 'A job card records the device, problem, work done, cost, and warranty for every repair.',
                    'options' => [
                        ['text' => 'To advertise on social media', 'is_correct' => false],
                        ['text' => 'To keep a written repair record', 'is_correct' => true],
                        ['text' => 'To replace a receipt', 'is_correct' => false],
                        ['text' => 'To order parts online', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which advertising method is cheapest and most effective in a township?',
                    'explanation' => 'Word of mouth and local WhatsApp groups are powerful, low-cost ways to reach customers.',
                    'options' => [
                        ['text' => 'Television advert', 'is_correct' => false],
                        ['text' => 'Billboard in Lusaka', 'is_correct' => false],
                        ['text' => 'Word of mouth and WhatsApp groups', 'is_correct' => true],
                        ['text' => 'International newspaper', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A repair business should register for a ZRA TPIN as it grows.',
                    'explanation' => 'Registering for a TPIN makes the business formal and compliant with tax rules as it expands.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'You should promise a fixed price before diagnosing the fault.',
                    'explanation' => 'It is better to diagnose first and then quote, because the real problem may be different from what the customer describes.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What do you call the money left after all business expenses are paid? (one word)',
                    'explanation' => 'Profit is the amount remaining after subtracting expenses from income.',
                    'correct_answer' => 'profit',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should you do if a repair will cost more than you quoted?',
                    'explanation' => 'Always contact the customer and explain the new cost before doing extra work.',
                    'options' => [
                        ['text' => 'Do the work and surprise them later', 'is_correct' => false],
                        ['text' => 'Call the customer and explain the new cost', 'is_correct' => true],
                        ['text' => 'Hide the extra cost', 'is_correct' => false],
                        ['text' => 'Return the device unfixed', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which document protects both you and the customer if a dispute arises?',
                    'explanation' => 'A clear job card or receipt records what was agreed and helps resolve disagreements.',
                    'options' => [
                        ['text' => 'A social media post', 'is_correct' => false],
                        ['text' => 'A verbal promise', 'is_correct' => false],
                        ['text' => 'A written job card', 'is_correct' => true],
                        ['text' => 'A newspaper advert', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Keeping business money separate from personal money helps you manage finances responsibly.',
                    'explanation' => 'Separating finances makes it easier to track profit, pay taxes, and plan for growth.',
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
            'title' => 'Mid-Course Assignment: Diagnostic Report and Preventive Maintenance Plan',
            'description' => 'Inspect a desktop or laptop computer, diagnose its hardware and software condition, clean it safely, and produce a professional maintenance report with recommendations.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Choose one desktop or laptop computer that you are allowed to inspect. It can be a college machine, a family computer, or a customer device with permission.
Step 2: Record the computer model, CPU, RAM amount, storage type and size, and operating system version.
Step 3: Open the case if it is a desktop, or inspect the vents and ports if it is a laptop. Document dust levels, fan condition, and any visible damage.
Step 4: Check the power cable, monitor cable, and external ports. Test whether the computer turns on, reaches Windows, and connects to the internet.
Step 5: Clean the machine safely. Remove dust from vents, fans, and heatsinks. Wipe the keyboard, mouse, and screen.
Step 6: Use Task Manager to check startup programs and disk usage. Run an antivirus scan and note any threats found.
Step 7: Write a maintenance report in a Word document or PDF. Include sections: Computer Details, Findings, Cleaning Done, Performance Observations, and Recommendations.
Step 8: Recommend at least two upgrades or repairs with estimated prices in Kwacha. Explain why each recommendation will help the user.
Step 9: Submit your report as a PDF or Word document. Include photos of the computer before and after cleaning if possible.
INSTRUCTIONS,
            'max_points' => 100,
            'passing_points' => 50,
            'due_date' => null,
            'allow_late_submission' => 1,
            'allowed_file_types' => 'pdf,doc,docx,jpg,png',
            'max_file_size_mb' => 10,
        ]);

        Assignment::create([
            'course_id' => $this->courseId,
            'lesson_id' => null,
            'title' => 'End-of-Course Project: Launch Your Repair Desk Business Plan',
            'description' => 'Create a practical business plan for a township ICT repair desk, including services, pricing, supplier list, marketing plan, and a sample job card.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Name your repair desk and choose a location, such as a home corner, a table in a shop, or a mobile service.
Step 2: List at least six services you will offer, for example dust cleaning, virus removal, Windows installation, RAM or SSD upgrades, phone screen sourcing, and network setup.
Step 3: Create a price list in Kwacha. Show the labour charge and at least two part prices with your markup clearly explained.
Step 4: Identify three places in Lusaka, online, or locally where you can source parts. Include supplier name, contact method, and one item they sell.
Step 5: Design a simple job card or receipt form. It must include: date, customer name, phone number, device model, problem, work done, parts used, total cost, and warranty terms.
Step 6: Write a marketing plan. Describe how you will advertise using posters, WhatsApp groups, word of mouth, or any other free or cheap method. Include the exact wording of one advert.
Step 7: Create a one-month budget. Estimate income from five repairs and expenses for parts, transport, and advertising. Calculate your expected profit.
Step 8: Write a short reflection on how you will build customer trust and handle a complaint.
Step 9: Submit your business plan as a PDF or Word document. Include any supporting images such as a poster design or sample job card.
INSTRUCTIONS,
            'max_points' => 100,
            'passing_points' => 50,
            'due_date' => null,
            'allow_late_submission' => 1,
            'allowed_file_types' => 'pdf,doc,docx,jpg,png',
            'max_file_size_mb' => 10,
        ]);
    }

    private function printSummary(): void
    {
        $this->command->info('ICT Support & Hardware Repair content seeded successfully.');
        $this->command->info('Modules: 4 | Lessons: 15 | Quizzes: 4 | Assignments: 2');
    }
}
