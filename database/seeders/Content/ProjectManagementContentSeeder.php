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

class ProjectManagementContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Project Management')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Project Management" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in Project Management already has modules. Skipping content seed.');
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
                'title' => 'Module 1: Foundations of Project Management',
                'description' => 'Understand what a project is, explore the project lifecycle, identify key roles, and see how project management works in Zambia.',
            ],
            [
                'title' => 'Module 2: Scoping and Stakeholders',
                'description' => 'Define project scope, identify stakeholders, build a work breakdown structure, and control scope creep.',
            ],
            [
                'title' => 'Module 3: Planning, Scheduling and Tools',
                'description' => 'Plan projects step by step, create Gantt charts on paper and in Excel, and understand milestones and dependencies.',
            ],
            [
                'title' => 'Module 4: Budgeting, Procurement and People',
                'description' => 'Build budgets in Kwacha, understand procurement basics, manage project teams, and run effective meetings.',
            ],
            [
                'title' => 'Module 5: Risk, Tracking, Reporting and Closure',
                'description' => 'Manage risks, track progress, report to funders, and close projects with lessons learnt.',
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
                'title' => '1.1 What Is a Project and Why Does It Matter?',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what a project is, tell the difference between a project and everyday work, give examples of projects from your own community, and describe why project management skills help NGOs, churches, construction teams and government officers deliver results on time and within budget.</p>

<h2>What Is a Project?</h2>
<p>A project is a temporary piece of work that creates a unique product, service or result and has a clear start and end date. Temporary means it does not go on forever. Unique means the output is different from the routine things an organisation does every day. For example, teaching a class every term is routine work, but building a new classroom block is a project because it starts with planning, ends with a finished block, and happens only once.</p>
<p>Projects are all around us in Zambia. Drilling a borehole for a rural community is a project. Constructing a church building is a project. Running a CDF-funded road repair in Kalomo District is a project. Organising a youth skills camp, launching a mobile money agent network, or installing solar panels at a clinic are all projects. Each one has a goal, a deadline, limited money, and people who must work together.</p>

<h2>Projects versus Operations</h2>
<p>Every organisation does two kinds of work. <strong>Operations</strong> are ongoing, repetitive activities that keep the organisation alive. A shopkeeper selling mealie meal every day is doing operations. A college enrolling students each term is doing operations. <strong>Projects</strong> are one-time efforts that change or improve something. If the shopkeeper decides to expand the shop by adding a cold room, that expansion is a project. If the college decides to build a new computer laboratory, that construction is a project.</p>
<p>Knowing the difference matters because projects need a different management approach. They need a clear plan, a budget, a schedule, assigned responsibilities, and a defined finish line. Without these, projects drift, waste money, and disappoint the people who were supposed to benefit.</p>

<h2>Why Project Management Matters in Zambia</h2>
<p>Zambia has many projects funded by government, NGOs, churches, donors and private businesses. A CDF project must serve the community and account for every kwacha. An NGO water project must finish before the dry season. A church building project must respect the congregation's donations. A small construction company must buy materials, pay workers and deliver on the agreed date. In every case, good project management prevents confusion, reduces costs, and builds trust.</p>
<p>Project management is also a career path. School leavers who understand project basics can work as project assistants, site clerks, monitoring officers or programme coordinators. Experienced professionals can become project managers, earn recognised certifications such as PMBOK, PRINCE2 or the Google Project Management Certificate, and lead larger initiatives in health, education, agriculture and infrastructure.</p>

<h2>Worked Example: A Borehole Drilling Project</h2>
<p>Consider a borehole drilling project in a village near Kalomo. The project has a clear goal: provide safe drinking water to the community. The start date is 1 March and the end date is 30 June. The budget is K85,000. The outputs include a drilled borehole, a hand pump, a protected concrete apron, and training for a water pump committee. The project ends when the community receives clean water and the committee knows how to maintain the pump. This is temporary and unique, so it is a project.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Think of one project happening in your community right now. Write down its goal, its approximate start and end dates, and who is paying for it.</li>
<li>List three routine activities at your workplace, school or church. Then list one project happening or planned at the same place.</li>
<li>Interview a friend or family member who has worked on a project. Ask what went well and what was difficult. Write two sentences summarising what they said.</li>
<li>Describe why a chicken-rearing side business is operations, but building a new chicken house is a project.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Project</strong> — a temporary piece of work with a unique result and a clear end date.</li>
<li><strong>Operations</strong> — ongoing, repetitive work that keeps an organisation running.</li>
<li><strong>Stakeholder</strong> — any person or group with an interest in the project.</li>
<li><strong>Deliverable</strong> — a tangible or intangible output that the project must produce.</li>
<li><strong>Project management</strong> — the practice of planning, organising and controlling resources to achieve a project goal.</li>
</ul>

<h2>Summary</h2>
<p>A project is temporary work that produces a unique result, unlike routine operations that continue indefinitely. Examples in Zambia include borehole drilling, church construction, CDF-funded roads and solar installations. Project management helps organisations finish work on time, within budget and to the required quality. These skills are valuable for NGOs, churches, government offices, construction firms and small businesses, and they can open doors to internationally recognised certifications.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/certificates/project-management/">Google Project Management Certificate — Course Overview</a></li>
<li><a href="https://www.pmi.org/about/learn-about-pmi">Project Management Institute — What Is Project Management?</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more">Khan Academy — Careers and Personal Finance</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.2 The Project Lifecycle from Start to Finish',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to name the five main phases of a project lifecycle, explain what happens in each phase, and apply the lifecycle to a real Zambian project such as building a community school classroom or installing a maize milling plant.</p>

<h2>The Five Phases of a Project Lifecycle</h2>
<p>Most projects move through five clear phases. These phases help teams know what to do and when to do it. The five phases are:</p>
<ol>
<li><strong>Initiation</strong> — define the project, identify the need, and decide whether to go ahead.</li>
<li><strong>Planning</strong> — set objectives, build the schedule, estimate the budget, identify risks, and assign roles.</li>
<li><strong>Execution</strong> — do the actual work, manage people, buy materials, and keep everyone informed.</li>
<li><strong>Monitoring and Controlling</strong> — track progress, compare actual work to the plan, and make corrections.</li>
<li><strong>Closure</strong> — finish the work, hand over the result, evaluate lessons learnt, and close the accounts.</li>
</ol>
<p>These phases overlap in real life. Monitoring and controlling happens throughout execution, and planning continues even after the project has started because new information always appears.</p>

<h2>Initiation: Deciding to Do the Project</h2>
<p>Initiation begins when someone sees a need. A head teacher notices that overcrowded classrooms reduce learning. A church elder sees that the congregation has outgrown the current building. A chief requests a borehole because the existing wells dry up every October. During initiation, the project idea is written down, benefits are described, a rough budget is estimated, and a decision is made whether to proceed.</p>
<p>A useful initiation document is the <strong>project charter</strong>. This one- or two-page document names the project manager, states the goal, lists main stakeholders, gives a rough budget, and authorises the work to begin. Even for a small poultry project run by a youth group, a simple charter helps everyone agree on what is being done and who is in charge.</p>

<h2>Planning: Preparing the Roadmap</h2>
<p>Planning is where the project takes shape. The team defines exactly what will be delivered, when each task will happen, how much money is needed, and who is responsible for each part. Good planning reduces surprises. If you are building a church, planning includes choosing the site, drawing plans, getting quotations for bricks and cement, scheduling workers, and setting a date for the opening service.</p>

<h2>Execution, Monitoring and Closure</h2>
<p><strong>Execution</strong> is the phase most people see. Bricks are laid, boreholes are drilled, training is delivered, and reports are written. During execution, the project manager holds meetings, solves problems, pays suppliers, and communicates with stakeholders. <strong>Monitoring and controlling</strong> runs at the same time. The manager checks whether the project is on schedule and within budget, and takes action if it falls behind. <strong>Closure</strong> happens when the work is finished. The team hands over the project, prepares a final report, evaluates what went well, and captures lessons learnt for next time.</p>

<h2>Worked Example: Lifecycle for a Church Building Project</h2>
<p>A congregation in Kalomo wants to build a new church hall. The lifecycle might look like this:</p>
<ul>
<li><strong>Initiation</strong>: The church board agrees that the congregation has grown. They appoint Brother Mwansa as project manager and set a fundraising target of K250,000.</li>
<li><strong>Planning</strong>: Architects draw simple plans, the quantity surveyor estimates materials, and the committee sets a start date after the rainy season.</li>
<li><strong>Execution</strong>: Builders lay the foundation, members donate labour, and the project manager pays suppliers from a dedicated bank account.</li>
<li><strong>Monitoring</strong>: The committee meets monthly to compare spending to the budget and checks whether construction is keeping pace with the schedule.</li>
<li><strong>Closure</strong>: The hall is handed over, an opening service is held, and the committee records lessons such as "buy cement in bulk before prices rise."</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a project you know, such as a school building, borehole or community garden.</li>
<li>Write one sentence describing what happens in each of the five lifecycle phases for that project.</li>
<li>Identify which phase usually takes the longest and explain why.</li>
<li>List two questions the project team should ask during the closure phase.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Project lifecycle</strong> — the series of phases a project passes through from start to finish.</li>
<li><strong>Initiation</strong> — the phase in which the project is defined and authorised.</li>
<li><strong>Project charter</strong> — a short document that formally authorises a project and names the project manager.</li>
<li><strong>Monitoring and controlling</strong> — tracking progress against the plan and taking corrective action.</li>
<li><strong>Lessons learnt</strong> — knowledge gained during a project that helps future projects succeed.</li>
</ul>

<h2>Summary</h2>
<p>The project lifecycle gives structure to any project through five phases: initiation, planning, execution, monitoring and controlling, and closure. Each phase has specific tasks and outputs. Applying this lifecycle helps Zambian projects finish on time, spend money wisely, and leave behind knowledge that improves the next project. Whether you are drilling a borehole or constructing a church hall, the lifecycle keeps the team focused from the first idea to the final handover.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/certificates/project-management/">Google Project Management Certificate — Project Lifecycle</a></li>
<li><a href="https://www.w3schools.com/">W3Schools — General Learning Resources</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more">Khan Academy — Career Skills</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.3 Roles and Responsibilities in a Project',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe the main roles found in projects, explain what each person does, and identify who should make decisions in different situations. You will also see how these roles appear in small community projects, NGOs, churches and government-funded work in Zambia.</p>

<h2>Common Project Roles</h2>
<p>Projects work best when everyone knows their role. Confusion about who decides what causes delays, arguments and wasted money. The main roles you will encounter are the project sponsor, the project manager, the project team, the steering committee, stakeholders, and the end users or beneficiaries.</p>

<h3>The Project Sponsor</h3>
<p>The <strong>sponsor</strong> is the person or group that pays for the project and wants the result. In a church project, the sponsor might be the congregation or a supporting diocese. In a government project, the sponsor is the ministry or council that allocated the funds. In an NGO project, the sponsor may be a donor in Lusaka or overseas. The sponsor makes high-level decisions, approves major changes, and protects the project from political or financial problems.</p>

<h3>The Project Manager</h3>
<p>The <strong>project manager</strong> is responsible for making the project happen day by day. This person plans the work, manages the budget, coordinates the team, communicates with stakeholders, and solves problems. The project manager does not usually do all the technical work. Instead, they make sure the right people are doing the right things at the right time. In a CDF road project, the project manager might be a council engineer or a contracted supervisor.</p>

<h3>The Project Team</h3>
<p>The <strong>project team</strong> includes everyone who does the actual work. This can include builders, teachers, health workers, data clerks, drivers, accountants and volunteers. Each team member has specific tasks. Clear task assignment prevents duplication and gaps. For example, in a borehole project, one person handles community mobilisation, another handles payments, and another supervises the drilling contractor.</p>

<h3>The Steering Committee and Stakeholders</h3>
<p>A <strong>steering committee</strong> is a small group of senior people who guide the project. They meet regularly to review progress, approve changes, and remove obstacles. <strong>Stakeholders</strong> are any people or groups affected by the project. For a school feeding project, stakeholders include pupils, parents, teachers, suppliers, the Ministry of Education and the local community. Good projects keep stakeholders informed and listen to their concerns.</p>

<h2>Worked Example: Roles in a CDF Classroom Project</h2>
<p>The Kalomo Town Council receives CDF money to build two classrooms at a basic school. The roles are distributed as follows:</p>
<ul>
<li><strong>Sponsor</strong>: Kalomo Town Council, which approved the CDF allocation and holds the budget.</li>
<li><strong>Project manager</strong>: A council works officer who prepares plans, invites quotations, and supervises construction.</li>
<li><strong>Team</strong>: Builders, a clerk of works, a procurement officer, and a finance assistant.</li>
<li><strong>Steering committee</strong>: The council secretary, the school head teacher, and a PTA representative.</li>
<li><strong>Stakeholders</strong>: Pupils, parents, teachers, the local contractor, and the community.</li>
</ul>
<p>When the builder asks to change the type of roofing sheet, the project manager checks the budget and refers the decision to the steering committee. When parents complain about dust from construction, the project manager arranges for water to be sprinkled on the road.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Think of a project in your area. Name the sponsor, project manager, and at least two team members or stakeholders.</li>
<li>Write a simple responsibility chart for a chicken-rearing project run by five youths. Give each person one clear responsibility.</li>
<li>Describe a situation where the project manager should ask the sponsor before making a decision.</li>
<li>List three qualities a good project manager should have and explain why each matters.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Project sponsor</strong> — the person or group that provides funds and authority for the project.</li>
<li><strong>Project manager</strong> — the person responsible for planning and delivering the project.</li>
<li><strong>Project team</strong> — the people who carry out the project tasks.</li>
<li><strong>Steering committee</strong> — a senior group that guides the project and approves major decisions.</li>
<li><strong>Beneficiaries</strong> — the people who receive the final benefit from the project.</li>
</ul>

<h2>Summary</h2>
<p>Every project needs clear roles. The sponsor provides money and authority, the project manager coordinates daily work, the team carries out tasks, and the steering committee guides major decisions. Stakeholders and beneficiaries must be kept informed because their support determines whether the project succeeds. When roles are clear, Zambian projects run more smoothly, decisions happen faster, and communities receive the full benefit of the work.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/certificates/project-management/">Google Project Management Certificate — Project Roles</a></li>
<li><a href="https://www.pmi.org/about/learn-about-pmi">Project Management Institute — About Project Management</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more">Khan Academy — Career Development</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.4 Project Management in the Zambian Context',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe the opportunities and challenges of managing projects in Zambia, explain how culture, climate, mobile money and local government structures affect projects, and identify professional growth paths such as PMBOK, PRINCE2 and the Google Project Management Certificate.</p>

<h2>The Zambian Project Environment</h2>
<p>Projects in Zambia operate in a unique environment. The country has growing infrastructure needs, active NGOs, faith-based organisations, government programmes like CDF, and a rising number of small businesses. Mobile money has made payments faster, but poor road networks can delay deliveries. Solar power helps during load-shedding, but irregular electricity still disrupts work. Understanding this environment helps project managers plan realistically and avoid common mistakes.</p>

<h2>Opportunities for Project Managers</h2>
<p>Zambia needs people who can manage projects well. Construction companies need site supervisors. NGOs need programme officers. Government councils need project coordinators. Churches need trustees who can oversee building projects. Small businesses need owners who can plan expansions. Learning project management therefore improves employability and equips people to serve their communities better.</p>
<p>Technology has opened new opportunities. A project manager can now send reports by WhatsApp, receive payments through Airtel Money or MTN MoMo, store documents on Google Drive, and track schedules on a smartphone. These tools reduce paperwork, speed up communication, and create records that donors and auditors can review.</p>

<h2>Common Challenges</h2>
<p>Zambian projects face predictable challenges. <strong>Climate</strong> affects construction; heavy rains from December to March can stop road works and delay material deliveries. <strong>Currency movement</strong> changes the price of imported goods; a project that budgets for cement in January may find prices higher by March. <strong>Load-shedding</strong> disrupts computer-based work and meetings. <strong>Late payments</strong> from funders or clients can stall a project. <strong>Poor stakeholder communication</strong> leads to misunderstandings and conflicts.</p>
<p>Good project managers anticipate these challenges. They build extra time into schedules, include contingency money in budgets, keep stakeholders informed, and maintain paper backups of important records in case computers fail.</p>

<h2>Growth Paths and Certifications</h2>
<p>After this certificate, you can deepen your skills through recognised programmes. The <strong>PMBOK Guide</strong>, published by the Project Management Institute, is a global standard. <strong>PRINCE2</strong> is a process-based method popular in some donor-funded projects. The <strong>Google Project Management Certificate</strong> is an affordable online programme that teaches practical skills and is recognised by employers. These certifications can help you move from project assistant to project manager and eventually to programme director.</p>

<h2>Worked Example: Adapting a Project to Local Conditions</h2>
<p>An NGO plans to build a rural health post. The project manager knows that the rainy season will make some roads impassable. She therefore schedules material deliveries for the dry months, arranges a local supplier who accepts mobile money, and keeps printed copies of the budget in case electricity fails. She also holds community meetings in the local language so that elders understand the timeline. These adaptations increase the chance of finishing on time and within budget.</p>

<h2>Try It Yourself</h2>
<ol>
<li>List three opportunities and three challenges for project management in your district.</li>
<li>Describe how mobile money can help a project manager pay workers in a rural area.</li>
<li>Research one of these growth paths: PMBOK, PRINCE2 or Google Project Management Certificate. Write two sentences about what it covers.</li>
<li>Identify one local project that failed or was delayed. Suggest one project management practice that might have helped.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Project environment</strong> — the political, economic, social and technical conditions surrounding a project.</li>
<li><strong>Contingency</strong> — extra time or money set aside to deal with unexpected problems.</li>
<li><strong>Stakeholder communication</strong> — keeping people informed and listening to their feedback.</li>
<li><strong>Certification</strong> — a formal qualification that proves knowledge or competence in a field.</li>
<li><strong>PMBOK</strong> — Project Management Body of Knowledge, a widely used project management standard.</li>
</ul>

<h2>Summary</h2>
<p>Project management in Zambia offers strong opportunities because the country has many development needs and growing use of digital tools. However, managers must also handle challenges such as climate, currency changes, load-shedding and late payments. By planning carefully, communicating well, and using mobile money and cloud tools, project managers can deliver strong results. Professional certifications such as PMBOK, PRINCE2 and the Google Project Management Certificate provide pathways for career growth.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/certificates/project-management/">Google Project Management Certificate</a></li>
<li><a href="https://www.pmi.org/about/learn-about-pmi">Project Management Institute — Learn About PMI</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more">Khan Academy — Careers and Personal Finance</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: Foundations of Project Management',
            'description' => 'Test your understanding of what a project is, the project lifecycle, project roles, and the Zambian project environment.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which statement best describes a project?',
                    'explanation' => 'A project is temporary work that produces a unique result, unlike routine operations that continue indefinitely.',
                    'options' => [
                        ['text' => 'Ongoing work done every day', 'is_correct' => false],
                        ['text' => 'Temporary work that creates a unique result', 'is_correct' => true],
                        ['text' => 'A permanent department in an organisation', 'is_correct' => false],
                        ['text' => 'A list of job descriptions', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of these is an example of a project?',
                    'explanation' => 'Drilling a borehole is a temporary, unique effort with a clear end, unlike daily teaching or selling.',
                    'options' => [
                        ['text' => 'A shop selling mealie meal every day', 'is_correct' => false],
                        ['text' => 'A teacher marking homework weekly', 'is_correct' => false],
                        ['text' => 'Drilling a community borehole', 'is_correct' => true],
                        ['text' => 'A college registering students each term', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In which phase of the project lifecycle is the project charter created?',
                    'explanation' => 'The project charter is created during initiation to authorise the project and name the project manager.',
                    'options' => [
                        ['text' => 'Closure', 'is_correct' => false],
                        ['text' => 'Initiation', 'is_correct' => true],
                        ['text' => 'Execution', 'is_correct' => false],
                        ['text' => 'Monitoring', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Who usually provides the money and authority for a project?',
                    'explanation' => 'The project sponsor provides funding and high-level authority for the project.',
                    'options' => [
                        ['text' => 'The project manager', 'is_correct' => false],
                        ['text' => 'The project team', 'is_correct' => false],
                        ['text' => 'The project sponsor', 'is_correct' => true],
                        ['text' => 'The beneficiaries', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'The planning phase ends as soon as execution begins.',
                    'explanation' => 'Planning often continues during execution because new information and changes require updates to the plan.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Mobile money can help project managers pay workers in rural areas quickly.',
                    'explanation' => 'Airtel Money and MTN MoMo allow fast payments even where banks are far away, making them useful for project finance.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What does PMBOK stand for? (four words)',
                    'explanation' => 'PMBOK stands for Project Management Body of Knowledge, a widely used standard.',
                    'correct_answer' => 'Project Management Body of Knowledge',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which phase involves comparing actual progress to the plan and making corrections?',
                    'explanation' => 'Monitoring and controlling tracks progress against the plan and takes corrective action when needed.',
                    'options' => [
                        ['text' => 'Initiation', 'is_correct' => false],
                        ['text' => 'Execution', 'is_correct' => false],
                        ['text' => 'Monitoring and controlling', 'is_correct' => true],
                        ['text' => 'Closure', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is one common challenge for Zambian projects?',
                    'explanation' => 'Load-shedding, currency movement, climate and late payments are all common challenges in Zambia.',
                    'options' => [
                        ['text' => 'Too many qualified project managers', 'is_correct' => false],
                        ['text' => 'No need for community involvement', 'is_correct' => false],
                        ['text' => 'Irregular electricity supply', 'is_correct' => true],
                        ['text' => 'Unlimited donor funding', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which group is affected by or has an interest in the project?',
                    'explanation' => 'Stakeholders are any people or groups affected by or interested in the project.',
                    'options' => [
                        ['text' => 'Sponsors only', 'is_correct' => false],
                        ['text' => 'Stakeholders', 'is_correct' => true],
                        ['text' => 'The project manager only', 'is_correct' => false],
                        ['text' => 'Auditors only', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module2Lessons(): array
    {
        return [
            [
                'title' => '2.1 Defining Project Scope and Objectives',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to define project scope, write clear project objectives, distinguish between what is included and excluded from a project, and explain why scope clarity prevents conflict and wasted resources.</p>

<h2>What Is Project Scope?</h2>
<p><strong>Scope</strong> describes exactly what a project will and will not do. It sets the boundaries. If the scope is unclear, people expect different things, costs grow, and deadlines slip. For example, if a church says "we want to build a hall," the scope is vague. Does the project include chairs, sound equipment, toilets, parking space, or fencing? Each item changes the budget and timeline. A clear scope statement answers these questions before work begins.</p>

<h2>Writing Clear Objectives</h2>
<p>Objectives are short statements that say what the project will achieve. Good objectives are SMART: <strong>Specific, Measurable, Achievable, Relevant</strong> and <strong>Time-bound</strong>. A weak objective says, "We will improve water access." A SMART objective says, "By 30 September, we will drill and equip one borehole that provides clean water to 200 households in Chikanta Ward." The second version tells the team exactly what success looks like.</p>
<p>Objectives guide decisions. When a supplier offers an extra feature, the project manager checks whether it helps meet an objective. If not, it is probably scope creep and should be refused or handled separately.</p>

<h2>In-Scope and Out-of-Scope</h2>
<p>Every scope statement should list what is <strong>in scope</strong> and what is <strong>out of scope</strong>. In a school classroom project, in-scope items might include two classrooms with cement floors, metal roofs, and lockable doors. Out-of-scope items might include furniture, a staff house, or a borehole. Writing these down prevents arguments later. If the PTA later asks for desks, the project manager can point to the scope document and explain that desks require a separate budget.</p>

<h2>Worked Example: Scope for a Chicken House Project</h2>
<p>A youth group in Kalomo receives K30,000 to start a chicken-rearing project. The scope statement says:</p>
<ul>
<li><strong>In scope</strong>: Build one chicken house measuring 10 metres by 6 metres, buy 200 day-old chicks, provide feeders and drinkers for 200 birds, vaccinate the chicks, and train five youths in basic poultry management.</li>
<li><strong>Out of scope</strong>: Purchase of land, construction of a feed mill, salaries for full-time workers, and marketing beyond the local market.</li>
<li><strong>Objective</strong>: By 31 December, the youth group will have a functioning chicken house with 200 healthy birds and five trained members who can manage daily operations.</li>
</ul>
<p>This clarity helps the group focus its spending and tells donors exactly what they are funding.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a project idea, such as a community garden or a small library.</li>
<li>Write one SMART objective for the project.</li>
<li>List three items that are in scope and two items that are out of scope.</li>
<li>Explain in two sentences why writing the scope down protects the project manager.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Scope</strong> — the boundaries of a project; what it includes and excludes.</li>
<li><strong>Objective</strong> — a clear statement of what the project will achieve.</li>
<li><strong>SMART</strong> — Specific, Measurable, Achievable, Relevant, Time-bound.</li>
<li><strong>In scope</strong> — work that is included in the project.</li>
<li><strong>Out of scope</strong> — work that is not included in the project.</li>
</ul>

<h2>Summary</h2>
<p>Project scope defines what the project will and will not deliver. Clear objectives and explicit in-scope and out-of-scope lists prevent misunderstandings, control costs, and protect the project manager from pressure to do extra work without extra resources. SMART objectives make success measurable and help teams stay focused from start to finish.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/certificates/project-management/">Google Project Management Certificate — Project Planning</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more">Khan Academy — Goal Setting</a></li>
<li><a href="https://www.w3schools.com/">W3Schools — Learning Resources</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.2 Identifying and Managing Stakeholders',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to identify the stakeholders of a project, analyse their interests and influence, plan how to communicate with them, and manage common stakeholder challenges in Zambian community, NGO and government projects.</p>

<h2>Who Are Stakeholders?</h2>
<p>Stakeholders are any people, groups or organisations affected by a project or able to affect it. They can be inside or outside the project team. Stakeholders include sponsors, beneficiaries, government departments, suppliers, community leaders, religious leaders, media, and even people who oppose the project. Ignoring stakeholders is one of the main reasons projects fail.</p>

<h2>Identifying Stakeholders</h2>
<p>The first step is to make a list. Ask questions such as: Who wants this project? Who pays for it? Who will use the result? Who must approve it? Who might be unhappy about it? Who lives near the project site? For a borehole project, stakeholders include the village headperson, the water pump committee, women who fetch water, the drilling company, the Ministry of Local Government, the donor, and nearby farmers whose land might be affected.</p>

<h2>Analysing Interests and Influence</h2>
<p>Not all stakeholders are equally important. A simple grid helps you decide how much attention each one needs. Draw a square with four boxes. Label the vertical axis "Influence" from low to high. Label the horizontal axis "Interest" from low to high. Place each stakeholder in one box:</p>
<ul>
<li><strong>High influence, high interest</strong> — manage closely. These are sponsors, senior chiefs, and key donors.</li>
<li><strong>High influence, low interest</strong> — keep satisfied. These might include local government officials who can block permits.</li>
<li><strong>Low influence, high interest</strong> — keep informed. These are often beneficiaries and community members.</li>
<li><strong>Low influence, low interest</strong> — monitor. These groups need only occasional updates.</li>
</ul>

<h2>Communication Planning</h2>
<p>Different stakeholders need different information. A sponsor wants budget and progress reports. A community elder wants face-to-face updates in the local language. A supplier wants clear orders and payment schedules. A good stakeholder plan lists who needs what information, how often, and through which channel. In rural Zambia, WhatsApp groups, community meetings, SMS updates, and notice-board posters are often more effective than email.</p>

<h2>Worked Example: Stakeholder Grid for a CDF Road Project</h2>
<p>A CDF project repairs a rural road in Kalomo District. The project manager creates the following stakeholder grid:</p>
<ul>
<li><strong>Manage closely</strong>: Kalomo Town Council sponsor, ward councillor, main contractor.</li>
<li><strong>Keep satisfied</strong>: District Commissioner, Ministry of Local Government engineer, local chief.</li>
<li><strong>Keep informed</strong>: Market traders who use the road, schoolteachers, clinic staff, PTA members.</li>
<li><strong>Monitor</strong>: Nearby landowners, passing motorists from other areas.</li>
</ul>
<p>Based on this, the project manager holds monthly meetings with the sponsor and contractor, briefs the chief before work begins, sends WhatsApp updates to traders, and posts progress notices at the market.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a project from your community. List at least six stakeholders.</li>
<li>Place each stakeholder in one of the four influence-interest boxes.</li>
<li>Write one sentence describing the best way to communicate with each stakeholder.</li>
<li>Describe what could go wrong if one important stakeholder is ignored.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Stakeholder</strong> — any person or group with an interest in or influence on a project.</li>
<li><strong>Influence</strong> — the power a stakeholder has to help or block the project.</li>
<li><strong>Interest</strong> — how much a stakeholder cares about the project outcome.</li>
<li><strong>Stakeholder grid</strong> — a simple tool for ranking stakeholders by influence and interest.</li>
<li><strong>Communication plan</strong> — a plan that says who receives what information, when and how.</li>
</ul>

<h2>Summary</h2>
<p>Stakeholder management is the process of identifying who matters, understanding their interests and influence, and planning communication. Good stakeholder management builds support, reduces conflict, and keeps projects moving. In Zambia, where projects often depend on community goodwill and government approval, stakeholder management is especially important. Use a simple grid and a communication plan to keep everyone informed without wasting time.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/certificates/project-management/">Google Project Management Certificate — Stakeholder Management</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more">Khan Academy — Communication Skills</a></li>
<li><a href="https://www.w3schools.com/">W3Schools — General Learning Resources</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.3 Work Breakdown Structure (WBS)',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what a work breakdown structure is, create a simple WBS for a small project, estimate task durations, and understand how the WBS helps assign responsibilities and build a project schedule.</p>

<h2>What Is a Work Breakdown Structure?</h2>
<p>A <strong>work breakdown structure</strong>, or WBS, is a way of dividing a project into smaller, manageable pieces. It looks like a family tree. At the top is the whole project. Below that are major deliverables or phases. Below each deliverable are the tasks needed to complete it. The idea is to keep breaking work down until each piece is small enough to estimate, assign and track.</p>
<p>A good WBS helps the team see the full picture and the details at the same time. It also prevents tasks from being forgotten. If a task does not fit under any WBS item, it is probably out of scope.</p>

<h2>How to Build a WBS</h2>
<p>Start with the project name. Then list the main deliverables. Under each deliverable, list the tasks. Under each task, list sub-tasks if needed. For example, a borehole project might be broken down as follows:</p>
<ul>
<li><strong>1.0 Borehole project</strong>
<ul>
<li><strong>1.1 Community mobilisation</strong>: meet headperson, form water committee, sensitise households.</li>
<li><strong>1.2 Site survey</strong>: identify drilling location, conduct hydrogeological survey, approve site.</li>
<li><strong>1.3 Procurement</strong>: get quotations, select contractor, sign contract, pay deposit.</li>
<li><strong>1.4 Drilling and installation</strong>: drill borehole, install casing, test yield, install hand pump.</li>
<li><strong>1.5 Completion and handover</strong>: build concrete apron, train water committee, hand over to community.</li>
</ul>
</li>
</ul>

<h2>Using the WBS for Planning</h2>
<p>Once the WBS is complete, the team estimates how long each task takes and who will do it. The project manager assigns each task to one person. If two people are responsible, confusion is likely. For example, "get quotations" might be assigned to the procurement officer, while "train water committee" might be assigned to the NGO field officer.</p>
<p>The WBS also feeds the budget. Each task needs resources: labour, materials, transport, or equipment. By estimating costs at the lowest level of the WBS, the project manager builds a realistic budget.</p>

<h2>Worked Example: WBS for a Classroom Project</h2>
<p>A school needs two new classrooms. The WBS includes these major deliverables:</p>
<ul>
<li><strong>1.0 Two new classrooms</strong>
<ul>
<li><strong>1.1 Planning and approvals</strong>: prepare drawings, get council approval, prepare bill of quantities.</li>
<li><strong>1.2 Site preparation</strong>: clear land, level ground, mark foundations.</li>
<li><strong>1.3 Construction</strong>: foundations, walls, roofing, plastering, flooring, painting.</li>
<li><strong>1.4 Finishing</strong>: install doors and windows, electrical fittings, chalkboards.</li>
<li><strong>1.5 Handover</strong>: final inspection, defect list, handover ceremony.</li>
</ul>
</li>
</ul>
<p>Each item is numbered so the project manager can refer to "task 1.3.2 walls" in meetings and reports.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a small project, such as organising a community clean-up day or a poultry vaccination day.</li>
<li>Create a WBS with at least three major deliverables and two tasks under each deliverable.</li>
<li>Estimate the duration of each task in hours or days.</li>
<li>Assign each lowest-level task to one person.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Work breakdown structure (WBS)</strong> — a hierarchical division of project work into smaller pieces.</li>
<li><strong>Deliverable</strong> — a result or output the project must produce.</li>
<li><strong>Task</strong> — a specific piece of work that contributes to a deliverable.</li>
<li><strong>Sub-task</strong> — a smaller part of a task.</li>
<li><strong>Hierarchical</strong> — arranged in levels, with each level more detailed than the one above.</li>
</ul>

<h2>Summary</h2>
<p>A work breakdown structure divides a project into manageable deliverables and tasks. It helps the team see all the work, assign responsibilities, estimate durations, and build a budget. Every task should fit under a deliverable, and every lowest-level task should have one responsible person. The WBS is the foundation of scheduling, budgeting and progress tracking.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/certificates/project-management/">Google Project Management Certificate — Planning and Scheduling</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-science/algorithms">Khan Academy — Problem Solving</a></li>
<li><a href="https://www.w3schools.com/">W3Schools — Learning Resources</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.4 Scope Creep and How to Control It',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what scope creep is, recognise common causes, describe how to control changes, and apply a simple change-control process to a Zambian project.</p>

<h2>What Is Scope Creep?</h2>
<p><strong>Scope creep</strong> happens when extra work is added to a project without corresponding changes to time, budget or resources. It usually starts small. A sponsor says, "While you are building the classroom, can you also add a small store room?" A community leader asks, "Can the borehole also serve the neighbouring village?" Each request seems reasonable, but together they push the project over budget and past its deadline.</p>

<h2>Why Scope Creep Happens</h2>
<p>Scope creep happens for several reasons. Stakeholders may not understand the original scope. People may assume that "while we are at it" additions are free. Political pressure can force extras. Donors may add conditions after signing the agreement. Poor documentation makes it hard to prove what was agreed. In community projects, well-meaning leaders sometimes promise benefits that were not budgeted.</p>

<h2>The Cost of Scope Creep</h2>
<p>Scope creep drains money, demotivates the team, delays completion, and damages trust. A project that was supposed to build two classrooms may end up with two half-finished classrooms because money was diverted to a third unplanned structure. A borehole project may run out of funds before the pump is installed because the committee decided to extend the water pipeline beyond the budget.</p>

<h2>Controlling Scope Creep</h2>
<p>The best defence is a clear scope document signed by the sponsor. When someone requests a change, the project manager should:</p>
<ol>
<li>Listen carefully and write down the request.</li>
<li>Check whether the request is already in scope.</li>
<li>Estimate the impact on time, cost and quality.</li>
<li>Present the impact to the sponsor or steering committee.</li>
<li>Get a written decision before doing the extra work.</li>
</ol>
<p>This process is called <strong>change control</strong>. It does not reject every request; it simply makes sure changes are visible, approved, and funded.</p>

<h2>Worked Example: Controlling a Change Request</h2>
<p>A church building committee asks the project manager to add a veranda to the new church hall. The project manager follows the change-control process:</p>
<ol>
<li>She records the request in a change-request form.</li>
<li>She checks the scope statement and confirms that a veranda is out of scope.</li>
<li>She asks the builder for a quotation. The veranda will cost K18,000 and add two weeks.</li>
<li>She presents the quotation to the steering committee.</li>
<li>The committee approves the change, reallocates K18,000 from fundraising, and signs the change form.</li>
</ol>
<p>Because the change was documented and approved, the project stays under control and the builder knows exactly what to construct.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Think of a project you know. Describe one example of scope creep that happened or could happen.</li>
<li>Write a short change-control policy for a youth poultry project. State who can approve changes.</li>
<li>Explain why verbal agreements about extra work are risky.</li>
<li>Role-play with a classmate: one person requests an extra feature, and the other applies the five-step change-control process.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Scope creep</strong> — uncontrolled expansion of project scope without matching changes to time, cost or resources.</li>
<li><strong>Change control</strong> — a formal process for reviewing, approving or rejecting changes to the project.</li>
<li><strong>Change request</strong> — a documented proposal to change the project scope, schedule or budget.</li>
<li><strong>Impact analysis</strong> — estimating how a proposed change will affect time, cost and quality.</li>
<li><strong>Approval authority</strong> — the person or group with power to approve changes.</li>
</ul>

<h2>Summary</h2>
<p>Scope creep is one of the most common project problems. It happens when extra work is added without proper approval. Project managers control scope creep by documenting the original scope, using a change-control process, and making sure every change is visible and funded. This protects the project budget, schedule and quality, and it preserves trust between the project team and its stakeholders.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/certificates/project-management/">Google Project Management Certificate — Managing Changes</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more">Khan Academy — Decision Making</a></li>
<li><a href="https://www.w3schools.com/">W3Schools — Learning Resources</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Scoping and Stakeholders',
            'description' => 'Test your knowledge of project scope, objectives, stakeholders, work breakdown structures, and scope creep.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does project scope define?',
                    'explanation' => 'Scope defines the boundaries of a project, including what is included and what is excluded.',
                    'options' => [
                        ['text' => 'Only the project budget', 'is_correct' => false],
                        ['text' => 'What the project will and will not do', 'is_correct' => true],
                        ['text' => 'The names of all workers', 'is_correct' => false],
                        ['text' => 'The project manager', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which letter in SMART stands for "Time-bound"?',
                    'explanation' => 'SMART objectives are Specific, Measurable, Achievable, Relevant and Time-bound.',
                    'options' => [
                        ['text' => 'S', 'is_correct' => false],
                        ['text' => 'M', 'is_correct' => false],
                        ['text' => 'A', 'is_correct' => false],
                        ['text' => 'T', 'is_correct' => true],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'A stakeholder with high influence and high interest should be:',
                    'explanation' => 'High influence and high interest stakeholders need close management because they can strongly affect the project.',
                    'options' => [
                        ['text' => 'Ignored', 'is_correct' => false],
                        ['text' => 'Managed closely', 'is_correct' => true],
                        ['text' => 'Sent only one SMS', 'is_correct' => false],
                        ['text' => 'Monitored occasionally', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main purpose of a work breakdown structure?',
                    'explanation' => 'A WBS divides project work into smaller, manageable pieces so tasks can be estimated, assigned and tracked.',
                    'options' => [
                        ['text' => 'To list all project stakeholders', 'is_correct' => false],
                        ['text' => 'To divide work into manageable tasks', 'is_correct' => true],
                        ['text' => 'To calculate profit only', 'is_correct' => false],
                        ['text' => 'To replace the project manager', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Scope creep happens when extra work is added without changing the budget or schedule.',
                    'explanation' => 'Scope creep is uncontrolled expansion of scope without matching changes to time, cost or resources.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A project manager should approve every change request immediately to keep stakeholders happy.',
                    'explanation' => 'Change requests should be analysed and approved by the right authority, not automatically approved.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What does WBS stand for? (three words)',
                    'explanation' => 'WBS stands for Work Breakdown Structure.',
                    'correct_answer' => 'Work Breakdown Structure',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is the best example of an out-of-scope item for a borehole project?',
                    'explanation' => 'Building a community hall is unrelated to the borehole project and would be out of scope.',
                    'options' => [
                        ['text' => 'Drilling the borehole', 'is_correct' => false],
                        ['text' => 'Installing a hand pump', 'is_correct' => false],
                        ['text' => 'Building a community hall', 'is_correct' => true],
                        ['text' => 'Training the water committee', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Who are stakeholders?',
                    'explanation' => 'Stakeholders include anyone affected by the project or able to affect it, including beneficiaries, sponsors, and suppliers.',
                    'options' => [
                        ['text' => 'Only the project team', 'is_correct' => false],
                        ['text' => 'Only the sponsor', 'is_correct' => false],
                        ['text' => 'Anyone affected by or able to affect the project', 'is_correct' => true],
                        ['text' => 'Only government officials', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should a project manager do first when receiving a change request?',
                    'explanation' => 'The first step is to record the request so it can be properly analysed and decided upon.',
                    'options' => [
                        ['text' => 'Start the extra work immediately', 'is_correct' => false],
                        ['text' => 'Write down the request', 'is_correct' => true],
                        ['text' => 'Ignore the request', 'is_correct' => false],
                        ['text' => 'Ask the team to pay for it', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module3Lessons(): array
    {
        return [
            [
                'title' => '3.1 Planning a Project Step by Step',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe the main steps in planning a project, develop a simple project plan, set milestones, and explain why planning saves time and money during execution.</p>

<h2>Why Planning Matters</h2>
<p>Planning is the phase that prepares the project for success. A good plan answers the questions: What will we do? How will we do it? Who will do it? When will it happen? How much will it cost? What could go wrong? Without a plan, the team works blindly, wastes resources, and misses deadlines. With a plan, everyone knows what to expect and can measure progress.</p>

<h2>Steps in Project Planning</h2>
<p>Effective planning follows a logical sequence. The steps are:</p>
<ol>
<li><strong>Review the scope and objectives</strong> — confirm what the project must achieve.</li>
<li><strong>Break the work into tasks</strong> — use the WBS to list everything that must be done.</li>
<li><strong>Sequence the tasks</strong> — decide which tasks depend on others and which can happen at the same time.</li>
<li><strong>Estimate durations</strong> — decide how long each task takes in days or weeks.</li>
<li><strong>Assign resources</strong> — identify the people, materials, equipment and money needed.</li>
<li><strong>Develop the schedule</strong> — create a timeline showing start and end dates.</li>
<li><strong>Prepare the budget</strong> — estimate costs for each task and add contingency.</li>
<li><strong>Identify risks</strong> — list things that could delay or disrupt the project.</li>
<li><strong>Plan communication</strong> — decide how the team and stakeholders will be kept informed.</li>
<li><strong>Document the plan</strong> — write everything down and get approval.</li>
</ol>

<h2>Milestones</h2>
<p>A <strong>milestone</strong> is a significant point in the project with zero duration. It marks the completion of an important phase or deliverable. Examples include "site handover completed," "foundation laid," "roofing completed," or "training delivered." Milestones help the team see progress and give stakeholders clear points to celebrate or review.</p>

<h2>Worked Example: Planning a Community Garden Project</h2>
<p>A women's group in Kalomo plans a community vegetable garden to sell tomatoes and rape. The plan includes:</p>
<ul>
<li><strong>Objective</strong>: Establish a one-hectare garden with a water tank and drip irrigation by 30 April.</li>
<li><strong>Tasks</strong>: Secure land, fence the garden, install a water tank, prepare seedbeds, buy seeds and drip lines, plant, train members.</li>
<li><strong>Schedule</strong>: Land and fencing in January, water installation in February, planting in March, training in April.</li>
<li><strong>Budget</strong>: K12,000 for materials, K3,000 for labour, K2,000 contingency.</li>
<li><strong>Milestones</strong>: Fencing completed by 31 January, water system tested by 28 February, first planting done by 31 March.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a small project, such as a school party or a community clean-up.</li>
<li>List the main tasks in order.</li>
<li>Estimate how long each task takes.</li>
<li>Identify two milestones and write them as SMART statements.</li>
<li>List three resources you would need.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Project plan</strong> — a document that describes how the project will be executed, monitored and controlled.</li>
<li><strong>Milestone</strong> — a significant point or achievement in a project timeline.</li>
<li><strong>Sequencing</strong> — arranging tasks in the order they must be done.</li>
<li><strong>Contingency</strong> — extra time or money reserved for unexpected problems.</li>
<li><strong>Dependency</strong> — a relationship where one task cannot start until another finishes.</li>
</ul>

<h2>Summary</h2>
<p>Planning turns project ideas into actionable roadmaps. The process includes reviewing scope, breaking work into tasks, sequencing, estimating, assigning resources, building a schedule, budgeting, identifying risks, planning communication, and documenting everything. Milestones mark important progress points. Good planning reduces confusion, prevents waste, and increases the chance that the project finishes on time and within budget.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/certificates/project-management/">Google Project Management Certificate — Project Planning</a></li>
<li><a href="https://support.google.com/docs/answer/1218656">Google Sheets Help — Create and Edit</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more">Khan Academy — Career Skills</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.2 Simple Scheduling with Gantt Charts',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what a Gantt chart is, draw a simple Gantt chart on paper, read a basic Gantt chart, and understand how it helps a project manager track progress against time.</p>

<h2>What Is a Gantt Chart?</h2>
<p>A <strong>Gantt chart</strong> is a bar chart that shows project tasks along a timeline. Each task is represented by a horizontal bar. The length of the bar shows how long the task takes, and its position shows when it starts and ends. Gantt charts make it easy to see what should be happening, when it should happen, and which tasks overlap.</p>

<h2>Parts of a Gantt Chart</h2>
<p>A simple Gantt chart has a list of tasks on the left and a calendar along the top. Each row represents one task. The bars show the planned duration. Some Gantt charts also show milestones as diamond shapes and dependencies as arrows between bars. For most small projects in Zambia, a simple version with tasks and bars is enough.</p>

<h2>Drawing a Gantt Chart on Paper</h2>
<p>You do not need expensive software to create a useful Gantt chart. A ruler, a pen and squared paper are enough. Follow these steps:</p>
<ol>
<li>List all tasks down the left side.</li>
<li>Draw a calendar across the top with weeks or months.</li>
<li>For each task, draw a bar from the start week to the end week.</li>
<li>Mark milestones with a diamond or star.</li>
<li>Review the chart with the team and update it as the project progresses.</li>
</ol>

<h2>Worked Example: Gantt Chart for a Classroom Project</h2>
<p>A project manager draws a Gantt chart for building two classrooms starting in April. The chart shows:</p>
<ul>
<li><strong>Task 1: Site preparation</strong> — Weeks 1 to 2.</li>
<li><strong>Task 2: Foundations</strong> — Weeks 3 to 4.</li>
<li><strong>Task 3: Wall construction</strong> — Weeks 5 to 7.</li>
<li><strong>Task 4: Roofing</strong> — Weeks 8 to 9.</li>
<li><strong>Task 5: Plastering and painting</strong> — Weeks 10 to 12.</li>
<li><strong>Task 6: Finishing and handover</strong> — Weeks 13 to 14.</li>
</ul>
<p>The project manager can see at a glance that roofing cannot begin before walls are done and that plastering should start in week 10. If wall construction slips by one week, the whole schedule must be reviewed.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a project with at least five tasks, such as preparing a field day or organising a funeral committee event.</li>
<li>List the tasks and estimate each duration in days or weeks.</li>
<li>Draw a simple Gantt chart on paper using a ruler.</li>
<li>Mark at least one milestone.</li>
<li>Show your chart to a classmate and explain the schedule.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Gantt chart</strong> — a bar chart that shows project tasks against a timeline.</li>
<li><strong>Timeline</strong> — a visual representation of when tasks or events occur.</li>
<li><strong>Bar</strong> — the horizontal line in a Gantt chart that represents a task's duration.</li>
<li><strong>Milestone marker</strong> — a symbol, often a diamond, showing a significant project point.</li>
<li><strong>Overlap</strong> — when two or more tasks happen at the same time.</li>
</ul>

<h2>Summary</h2>
<p>A Gantt chart is a simple but powerful scheduling tool. It shows tasks, durations, start and end dates, and overlaps on a single page. Project managers can create Gantt charts on paper or in software such as Excel or Google Sheets. For Zambian projects, paper Gantt charts are often the most practical because they do not depend on electricity or computers. Regular updates keep the team aware of progress and delays.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/docs/answer/1218656">Google Sheets Help — Create and Edit</a></li>
<li><a href="https://grow.google/certificates/project-management/">Google Project Management Certificate — Scheduling</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-science/algorithms">Khan Academy — Planning and Problem Solving</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.3 Using Excel and Paper for Project Timelines',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create a simple project timeline in a spreadsheet, use basic formatting to make it readable, and decide when paper or digital tools are more appropriate for a Zambian project.</p>

<h2>Spreadsheets for Project Timelines</h2>
<p>Microsoft Excel, Google Sheets and LibreOffice Calc are useful for project timelines. A spreadsheet can hold task names, start dates, end dates, durations, responsible persons, and status updates. Conditional formatting can colour-code tasks that are late, on track, or completed. This makes it easy to see project health at a glance.</p>

<h2>Creating a Simple Timeline in a Spreadsheet</h2>
<p>Follow these steps to create a basic project timeline:</p>
<ol>
<li>Open a spreadsheet and create column headers: Task, Start Date, End Date, Duration, Responsible Person, Status.</li>
<li>List all tasks under the Task column.</li>
<li>Enter start and end dates for each task.</li>
<li>Calculate duration with a formula such as <code>=End_Date_Cell - Start_Date_Cell</code>.</li>
<li>Fill in the responsible person for each task.</li>
<li>Update the Status column regularly: Not Started, In Progress, Completed, Delayed.</li>
</ol>

<h2>When to Use Paper</h2>
<p>Paper timelines are better when electricity is unreliable, when the team is not comfortable with computers, or when the project site has no internet. A large sheet of paper on a notice board can serve as a shared timeline that everyone sees when they pass by. The project manager updates it with a marker during weekly meetings.</p>

<h2>When to Use Digital Tools</h2>
<p>Digital timelines are better when the team is spread across locations, when reports must be emailed to donors, or when many people need to edit the same document. Google Sheets is useful because it works on smartphones and can be shared through WhatsApp or email. However, the project manager should always keep a printed backup in case of load-shedding or network failure.</p>

<h2>Worked Example: Spreadsheet Timeline for a Poultry Project</h2>
<p>A youth poultry project has the following timeline in Google Sheets:</p>
<table>
<tr><th>Task</th><th>Start Date</th><th>End Date</th><th>Duration</th><th>Responsible</th><th>Status</th></tr>
<tr><td>Build chicken house</td><td>01-Mar</td><td>31-Mar</td><td>30</td><td>John</td><td>Completed</td></tr>
<tr><td>Buy day-old chicks</td><td>01-Apr</td><td>05-Apr</td><td>4</td><td>Mary</td><td>In Progress</td></tr>
<tr><td>Vaccinate chicks</td><td>10-Apr</td><td>12-Apr</td><td>2</td><td>Dr Banda</td><td>Not Started</td></tr>
<tr><td>Start selling chickens</td><td>15-Jun</td><td>30-Jun</td><td>15</td><td>Group</td><td>Not Started</td></tr>
</table>
<p>The project manager checks this sheet every Monday and updates the status after each visit to the site.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Open Google Sheets or LibreOffice Calc.</li>
<li>Create the six column headers shown above.</li>
<li>Enter at least five tasks for a project you know.</li>
<li>Use a formula to calculate duration for each task.</li>
<li>Apply colour to the Status column: green for Completed, yellow for In Progress, red for Delayed.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Spreadsheet</strong> — a digital grid of rows and columns used for organising data.</li>
<li><strong>Conditional formatting</strong> — automatic formatting that changes cell colour based on values.</li>
<li><strong>Status</strong> — the current condition of a task, such as Not Started or Completed.</li>
<li><strong>Timeline</strong> — a visual display of when tasks occur.</li>
<li><strong>Backup</strong> — a copy of important information kept safe in case the original is lost.</li>
</ul>

<h2>Summary</h2>
<p>Project timelines can be created on paper or in spreadsheets. Spreadsheets allow automatic calculations, easy sharing, and colour-coding, while paper timelines work well when electricity or computer skills are limited. The best tool is the one the team will actually use and update. For most Zambian projects, a combination works well: a digital master copy and a paper notice-board version for daily visibility.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/docs/answer/1218656">Google Sheets Help — Create and Edit</a></li>
<li><a href="https://www.libreoffice.org/get-help/">LibreOffice Help and Documentation</a></li>
<li><a href="https://grow.google/certificates/project-management/">Google Project Management Certificate — Tools</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.4 Milestones, Dependencies and Critical Path',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to define milestones and dependencies, explain the concept of the critical path in simple terms, and use these ideas to keep a project on schedule.</p>

<h2>Milestones Revisited</h2>
<p>Milestones are zero-duration events that mark important achievements. They do not consume time or resources, but they show progress. Examples include "contract signed," "first concrete poured," "roofing completed," "beneficiaries trained," and "project officially handed over." Milestones are useful for reporting because they give stakeholders clear, easy-to-understand progress signals.</p>

<h2>Understanding Dependencies</h2>
<p>A <strong>dependency</strong> exists when one task cannot start or finish until another task has reached a certain point. For example, you cannot plaster walls before the walls are built. You cannot install a hand pump before the borehole is drilled. Recognising dependencies helps the project manager sequence tasks correctly and avoid wasting time.</p>
<p>Common types of dependency are:</p>
<ul>
<li><strong>Finish-to-start</strong> — Task B cannot start until Task A finishes. This is the most common type.</li>
<li><strong>Start-to-start</strong> — Task B starts when Task A starts. For example, preparing meals for workers can start when construction starts.</li>
<li><strong>Finish-to-finish</strong> — Task B finishes when Task A finishes.</li>
</ul>

<h2>The Critical Path</h2>
<p>The <strong>critical path</strong> is the longest chain of dependent tasks in a project. It determines the shortest possible project duration. If any task on the critical path is delayed, the whole project is delayed. Tasks not on the critical path have some flexibility, called <strong>float</strong> or <strong>slack</strong>. Understanding the critical path helps the project manager focus attention on the tasks that matter most for the deadline.</p>

<h2>Worked Example: Critical Path for a Church Roof</h2>
<p>A church wants to install a new roof before the rainy season. The tasks and durations are:</p>
<ul>
<li>Order roofing sheets — 2 weeks.</li>
<li>Remove old roof — 1 week.</li>
<li>Install new trusses — 2 weeks.</li>
<li>Install roofing sheets — 1 week.</li>
<li>Paint the ceiling — 1 week.</li>
</ul>
<p>The painting cannot start until the roof sheets are installed. The roof sheets cannot be installed until trusses are in place. The critical path is: order sheets → install trusses → install sheets → paint ceiling. This path takes 6 weeks. If ordering sheets is delayed by one week, the whole project shifts by one week.</p>

<h2>Try It Yourself</h2>
<ol>
<li>List five tasks for a project you know.</li>
<li>Identify which tasks depend on others.</li>
<li>Draw the tasks in order and circle the critical path.</li>
<li>Estimate the total project duration based on the critical path.</li>
<li>Identify one task with float and explain why it has flexibility.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Milestone</strong> — a significant point in a project with no duration.</li>
<li><strong>Dependency</strong> — a relationship between tasks where one depends on another.</li>
<li><strong>Critical path</strong> — the longest sequence of dependent tasks that determines project duration.</li>
<li><strong>Float / slack</strong> — the amount of time a task can be delayed without delaying the project.</li>
<li><strong>Sequence</strong> — the order in which tasks must be performed.</li>
</ul>

<h2>Summary</h2>
<p>Milestones mark progress, dependencies show task relationships, and the critical path reveals which tasks control the overall project duration. By focusing on critical path tasks, project managers can protect deadlines and use float wisely. These concepts can be applied simply using paper, Excel or Google Sheets, making them accessible even for small community projects in Zambia.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/certificates/project-management/">Google Project Management Certificate — Scheduling</a></li>
<li><a href="https://www.khanacademy.org/computing/computer-science/algorithms">Khan Academy — Algorithms and Problem Solving</a></li>
<li><a href="https://support.google.com/docs/answer/1218656">Google Sheets Help — Formulas</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Planning, Scheduling and Tools',
            'description' => 'Test your understanding of project planning, Gantt charts, spreadsheet timelines, milestones, dependencies and the critical path.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main purpose of a Gantt chart?',
                    'explanation' => 'A Gantt chart shows project tasks against a timeline, making it easy to see when work happens.',
                    'options' => [
                        ['text' => 'To calculate profit', 'is_correct' => false],
                        ['text' => 'To show tasks against a timeline', 'is_correct' => true],
                        ['text' => 'To identify stakeholders', 'is_correct' => false],
                        ['text' => 'To write the project charter', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a milestone?',
                    'explanation' => 'A milestone is a zero-duration event marking an important achievement, such as roofing completed.',
                    'options' => [
                        ['text' => 'Buying cement over two weeks', 'is_correct' => false],
                        ['text' => 'Roofing completed', 'is_correct' => true],
                        ['text' => 'Painting walls for five days', 'is_correct' => false],
                        ['text' => 'Meeting the contractor', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is a dependency?',
                    'explanation' => 'A dependency exists when one task cannot start or finish until another task reaches a certain point.',
                    'options' => [
                        ['text' => 'A type of project budget', 'is_correct' => false],
                        ['text' => 'A relationship between tasks where one depends on another', 'is_correct' => true],
                        ['text' => 'A project stakeholder', 'is_correct' => false],
                        ['text' => 'A project meeting', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does the critical path tell the project manager?',
                    'explanation' => 'The critical path is the longest chain of dependent tasks and determines the shortest possible project duration.',
                    'options' => [
                        ['text' => 'Which tasks have the most workers', 'is_correct' => false],
                        ['text' => 'Which tasks control the project duration', 'is_correct' => true],
                        ['text' => 'Which tasks are most expensive', 'is_correct' => false],
                        ['text' => 'Which tasks are out of scope', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A milestone has zero duration and marks an important achievement.',
                    'explanation' => 'Milestones are points in time with no duration, used to mark significant progress.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Tasks not on the critical path cannot be delayed at all.',
                    'explanation' => 'Tasks not on the critical path have float, meaning they can be delayed without affecting the overall project duration.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the flexibility time called for a task that is not on the critical path? (one word)',
                    'explanation' => 'Float, also called slack, is the amount of time a non-critical task can be delayed.',
                    'correct_answer' => 'Float',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which tool is best for sharing a project timeline with a remote team?',
                    'explanation' => 'Google Sheets can be shared online and edited by multiple people, making it suitable for remote collaboration.',
                    'options' => [
                        ['text' => 'A notice board only', 'is_correct' => false],
                        ['text' => 'Google Sheets', 'is_correct' => true],
                        ['text' => 'A handwritten diary', 'is_correct' => false],
                        ['text' => 'A voice message', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In a spreadsheet timeline, what does the Status column usually show?',
                    'explanation' => 'The Status column shows whether a task is Not Started, In Progress, Completed or Delayed.',
                    'options' => [
                        ['text' => 'The project sponsor', 'is_correct' => false],
                        ['text' => 'The current condition of each task', 'is_correct' => true],
                        ['text' => 'The total project profit', 'is_correct' => false],
                        ['text' => 'The number of workers', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which is an example of a finish-to-start dependency?',
                    'explanation' => 'You cannot install a hand pump before the borehole is drilled, so pump installation depends on drilling finishing.',
                    'options' => [
                        ['text' => 'Painting cannot start until walls are built', 'is_correct' => true],
                        ['text' => 'Two tasks happening at the same time', 'is_correct' => false],
                        ['text' => 'A task that has no deadline', 'is_correct' => false],
                        ['text' => 'A task with extra budget', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module4Lessons(): array
    {
        return [
            [
                'title' => '4.1 Budgeting in Kwacha for Local Projects',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to prepare a simple project budget in Zambian Kwacha, estimate costs accurately, add contingency, track spending, and explain why realistic budgeting prevents projects from stalling halfway.</p>

<h2>Why Budgeting Matters</h2>
<p>A budget is a financial plan for the project. It says how much money is needed, what it will be spent on, and when. Without a budget, projects run out of money before completion. With a realistic budget, the project manager can pay suppliers on time, avoid debt, and show donors or sponsors that funds are being used properly. In Zambia, where cash flow can be tight and prices fluctuate, budgeting is especially important.</p>

<h2>Types of Project Costs</h2>
<p>Project costs usually fall into several categories:</p>
<ul>
<li><strong>Direct costs</strong> — expenses directly tied to project work, such as cement, bricks, labour, and equipment hire.</li>
<li><strong>Indirect costs</strong> — overhead expenses such as office rent, utilities, and administrative staff.</li>
<li><strong>Personnel costs</strong> — salaries, allowances, and transport for project staff.</li>
<li><strong>Materials and supplies</strong> — raw materials, tools, seeds, stationery, and other consumables.</li>
<li><strong>Contingency</strong> — extra money reserved for unexpected events such as price rises or rain damage.</li>
</ul>

<h2>Building a Budget Step by Step</h2>
<ol>
<li>List all tasks from the WBS.</li>
<li>For each task, identify the resources needed.</li>
<li>Get quotations from at least three suppliers for major items.</li>
<li>Enter quantities and unit costs into a spreadsheet.</li>
<li>Calculate line totals and add them up.</li>
<li>Add contingency, usually 5 to 15 percent depending on risk.</li>
<li>Review the budget with the sponsor and get approval.</li>
</ol>

<h2>Worked Example: Budget for a Community Borehole</h2>
<table>
<tr><th>Item</th><th>Quantity</th><th>Unit Cost (K)</th><th>Total (K)</th></tr>
<tr><td>Hydrogeological survey</td><td>1</td><td>5,000</td><td>5,000</td></tr>
<tr><td>Drilling and casing</td><td>1 job</td><td>45,000</td><td>45,000</td></tr>
<tr><td>Hand pump</td><td>1</td><td>12,000</td><td>12,000</td></tr>
<tr><td>Concrete apron</td><td>1</td><td>8,000</td><td>8,000</td></tr>
<tr><td>Community sensitisation</td><td>3 meetings</td><td>500</td><td>1,500</td></tr>
<tr><td>Project management</td><td>1</td><td>6,000</td><td>6,000</td></tr>
<tr><td>Contingency (10%)</td><td></td><td></td><td>7,750</td></tr>
<tr><td><strong>Total</strong></td><td></td><td></td><td><strong>85,250</strong></td></tr>
</table>
<p>This budget is realistic because it is based on quotations and includes contingency for unexpected costs.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a small project, such as a school prize-giving day or a church fundraiser.</li>
<li>List at least six cost items.</li>
<li>Estimate quantities and unit costs in Kwacha.</li>
<li>Calculate line totals and add a 10 percent contingency.</li>
<li>Write two sentences explaining how you would track spending against this budget.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Budget</strong> — a financial plan showing expected project income and expenses.</li>
<li><strong>Direct cost</strong> — an expense directly linked to project activities.</li>
<li><strong>Indirect cost</strong> — an overhead expense not tied to one specific task.</li>
<li><strong>Contingency</strong> — extra money set aside for unexpected costs.</li>
<li><strong>Quotation</strong> — a supplier's written estimate of the cost of goods or services.</li>
</ul>

<h2>Summary</h2>
<p>Budgeting in Kwacha requires listing all project costs, getting quotations, calculating totals, and adding contingency. A realistic budget protects the project from stalling, helps the manager pay suppliers on time, and demonstrates accountability to sponsors. Tracking actual spending against the budget throughout the project is just as important as preparing the budget at the start.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/docs/answer/1218656">Google Sheets Help — Budget Formulas</a></li>
<li><a href="https://grow.google/certificates/project-management/">Google Project Management Certificate — Budgeting</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more">Khan Academy — Personal Finance</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.2 Procurement Basics for NGOs, Churches and Government',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what procurement is, describe a simple procurement process, compare quotations fairly, and understand why transparency in procurement prevents corruption and builds trust.</p>

<h2>What Is Procurement?</h2>
<p><strong>Procurement</strong> is the process of buying goods, services or works for a project. It includes identifying what is needed, finding suppliers, comparing prices, selecting a supplier, signing a contract, and paying. Procurement happens in every project, whether you are buying cement for a church, hiring a drilling company for an NGO, or selecting a contractor for a CDF road.</p>

<h2>Simple Procurement Steps</h2>
<ol>
<li><strong>Needs identification</strong> — decide exactly what must be bought, in what quantity, and by when.</li>
<li><strong>Market survey</strong> — identify potential suppliers or contractors.</li>
<li><strong>Request for quotations</strong> — ask at least three suppliers to submit written prices.</li>
<li><strong>Evaluation</strong> — compare prices, quality, delivery time, and supplier reputation.</li>
<li><strong>Selection</strong> — choose the supplier that offers the best value for money.</li>
<li><strong>Contract or purchase order</strong> — document what will be supplied, when, and how payment will be made.</li>
<li><strong>Delivery and inspection</strong> — check that goods or services meet the agreed standard.</li>
<li><strong>Payment</strong> — pay according to the contract terms and keep records.</li>
</ol>

<h2>Value for Money</h2>
<p>Cheapest is not always best. A supplier offering cement at K95 per bag but delivering late may cost more than a supplier at K100 who delivers on time. When comparing quotations, consider price, quality, reliability, after-sales service, and payment terms. Document the reasons for selection so that the decision can be defended if questioned.</p>

<h2>Transparency and Accountability</h2>
<p>Transparent procurement means the process is open, fair, and documented. It reduces the risk of corruption, favouritism, and fraud. In government and donor-funded projects, procurement rules may require public tendering, bid committees, and written records. Even in small church projects, keeping quotations and a short selection note protects the committee from accusations of bias.</p>

<h2>Worked Example: Selecting a Builder</h2>
<p>A school committee needs to select a builder for two classrooms. They invite three builders to submit quotations:</p>
<table>
<tr><th>Builder</th><th>Price (K)</th><th>Proposed Duration</th><th>Reference</th></tr>
<tr><td>Chilumba Construction</td><td>180,000</td><td>10 weeks</td><td>Built classrooms at nearby school</td></tr>
<tr><td>Mwamba Builders</td><td>165,000</td><td>14 weeks</td><td>No recent school project</td></tr>
<tr><td>Quick Build Ltd</td><td>150,000</td><td>8 weeks</td><td>New company, no references</td></tr>
</table>
<p>The committee chooses Chilumba Construction because, although more expensive, the company has proven experience and a realistic timeline. They record the decision in the minutes.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Imagine you need to buy 50 bags of cement for a project.</li>
<li>Write three questions you would ask each supplier before placing an order.</li>
<li>Describe why "lowest price" should not always win.</li>
<li>Explain one way to make procurement more transparent in a community project.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Procurement</strong> — the process of acquiring goods, services or works for a project.</li>
<li><strong>Quotation</strong> — a written statement of the price for goods or services.</li>
<li><strong>Value for money</strong> — the best balance of price, quality and reliability.</li>
<li><strong>Tender</strong> — a formal offer to supply goods or services at a stated price.</li>
<li><strong>Transparency</strong> — openness and clarity in decision-making so that others can see fairness.</li>
</ul>

<h2>Summary</h2>
<p>Procurement is the process of buying what a project needs. A simple but disciplined process includes identifying needs, requesting quotations, evaluating options, selecting the best value, contracting, inspecting delivery, and paying with proper records. Transparency protects projects from corruption and builds trust among stakeholders, donors and communities.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/certificates/project-management/">Google Project Management Certificate — Procurement</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more">Khan Academy — Economics and Finance</a></li>
<li><a href="https://www.w3schools.com/">W3Schools — Learning Resources</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.3 Managing People and Project Teams',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe how to build and lead a project team, assign tasks clearly, motivate team members, handle conflict, and communicate expectations in a Zambian work setting.</p>

<h2>Building the Team</h2>
<p>Projects are done by people, so managing people is at the heart of project management. A good project manager selects team members based on skills, attitude and availability. In community projects, the team may include volunteers, paid workers, contractors, and committee members. The project manager must make sure everyone understands the project goal and their own role.</p>

<h2>Clear Task Assignment</h2>
<p>Every team member should know what they are responsible for, when it is due, and how their work fits into the bigger picture. A useful approach is to use a simple responsibility matrix. For each task, name one person as responsible. If two people share responsibility, confusion is likely. Write assignments down and review them in meetings.</p>

<h2>Motivation and Recognition</h2>
<p>People work harder when they feel valued. Project managers can motivate teams by:</p>
<ul>
<li>Setting clear and achievable targets.</li>
<li>Giving regular feedback.</li>
<li>Recognising good work in front of the team.</li>
<li>Ensuring people have the tools and resources they need.</li>
<li>Paying allowances or wages on time.</li>
<li>Creating a respectful and safe working environment.</li>
</ul>
<p>In volunteer projects, recognition may matter more than money. A public thank-you, a certificate, or mention in a report can mean a great deal.</p>

<h2>Handling Conflict</h2>
<p>Conflict is normal in projects because people have different interests and pressures. The project manager should listen to all sides, focus on the problem not the person, and find solutions that serve the project goal. Sometimes a private conversation is better than a public argument. If conflict threatens the project, the steering committee or sponsor may need to intervene.</p>

<h2>Worked Example: Team Roles in a School Feeding Project</h2>
<p>A school feeding project has the following team assignments:</p>
<ul>
<li><strong>Project manager</strong> — head teacher, coordinates the whole project.</li>
<li><strong>Procurement officer</strong> — PTA treasurer, buys maize and beans.</li>
<li><strong>Cook supervisor</strong> — senior parent, oversees daily cooking.</li>
<li><strong>Store keeper</strong> — deputy head, manages stock.</li>
<li><strong>Monitor</strong> — community health worker, checks nutrition records.</li>
</ul>
<p>Each person has one clear role. The head teacher holds weekly meetings to review progress and solve problems. When a cook complains about late deliveries, the procurement officer is held responsible for fixing the supply schedule.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Think of a team you have worked in. What made it effective or ineffective?</li>
<li>List three ways a project manager can motivate a volunteer team.</li>
<li>Describe a conflict that could arise in a construction project and how you would handle it.</li>
<li>Write a one-page responsibility chart for a five-person project team.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Team</strong> — a group of people working together toward a common project goal.</li>
<li><strong>Responsibility matrix</strong> — a table showing who is responsible for each task.</li>
<li><strong>Motivation</strong> — the reasons and encouragement that make people want to work well.</li>
<li><strong>Conflict resolution</strong> — the process of addressing disagreements constructively.</li>
<li><strong>Feedback</strong> — information given to a team member about their performance.</li>
</ul>

<h2>Summary</h2>
<p>Managing people means building a capable team, assigning clear responsibilities, motivating members, and resolving conflict. In Zambian projects, respect, regular communication, and timely payment are powerful motivators. A well-led team can overcome many practical problems, while a poorly led team can waste resources and damage community trust.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/certificates/project-management/">Google Project Management Certificate — Leading Teams</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more">Khan Academy — Communication Skills</a></li>
<li><a href="https://www.w3schools.com/">W3Schools — Learning Resources</a></li>
</ul>
HTML,
            ],
            [
                'title' => '4.4 Running Effective Project Meetings',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to plan and run productive project meetings, keep useful minutes, follow up on action points, and avoid common meeting problems such as late starts, long speeches, and unclear decisions.</p>

<h2>Why Meetings Matter</h2>
<p>Meetings are where project teams share information, solve problems, make decisions, and hold each other accountable. Poor meetings waste time and create confusion. Good meetings keep the project moving. In Zambia, meetings are also important for building relationships and showing respect, so they should be well organised but culturally sensitive.</p>

<h2>Before the Meeting</h2>
<p>Good meetings begin with preparation. The project manager should:</p>
<ul>
<li>Define the purpose of the meeting.</li>
<li>Prepare an agenda with time limits for each item.</li>
<li>Invite only the people who need to be there.</li>
<li>Share the agenda in advance, by WhatsApp if necessary.</li>
<li>Book a venue and ensure basic needs such as chairs and water.</li>
</ul>

<h2>During the Meeting</h2>
<p>Start on time. Review the agenda and any decisions from the last meeting. Focus on progress, problems, and decisions. Avoid letting one person dominate. Keep discussions relevant. When a decision is made, state it clearly and name the person responsible for action. End on time or early if possible.</p>

<h2>Minutes and Action Points</h2>
<p><strong>Minutes</strong> are a written record of what was discussed and decided. They do not need to capture every word, but they must record decisions, action points, responsible persons, and deadlines. After the meeting, distribute the minutes within a few days. Action points should be reviewed at the next meeting to ensure follow-through.</p>

<h2>Worked Example: Meeting Agenda for a CDF Project</h2>
<table>
<tr><th>Item</th><th>Time</th><th>Responsible</th></tr>
<tr><td>Review of previous action points</td><td>10 min</td><td>Secretary</td></tr>
<tr><td>Progress report on classroom construction</td><td>15 min</td><td>Site supervisor</td></tr>
<tr><td>Budget update and pending payments</td><td>15 min</td><td>Treasurer</td></tr>
<tr><td>Issue: delayed delivery of roofing sheets</td><td>15 min</td><td>Procurement officer</td></tr>
<tr><td>Next meeting date and action points</td><td>5 min</td><td>Chairperson</td></tr>
</table>

<h2>Try It Yourself</h2>
<ol>
<li>Write a one-page agenda for a project progress meeting.</li>
<li>Practise taking minutes during a real or role-play meeting.</li>
<li>List three ways to stop a meeting from running too long.</li>
<li>Describe how you would follow up on an action point after a meeting.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Agenda</strong> — a list of items to be discussed at a meeting.</li>
<li><strong>Minutes</strong> — a written record of meeting discussions and decisions.</li>
<li><strong>Action point</strong> — a specific task assigned to a person with a deadline.</li>
<li><strong>Quorum</strong> — the minimum number of people needed for a meeting to make valid decisions.</li>
<li><strong>Follow-up</strong> — checking that agreed actions have been completed.</li>
</ul>

<h2>Summary</h2>
<p>Effective meetings need preparation, a clear agenda, disciplined discussion, clear decisions, and good minutes. Action points must name a responsible person and a deadline. Regular follow-up turns meeting decisions into real progress. For Zambian projects, well-run meetings build trust, solve problems, and keep stakeholders aligned.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/certificates/project-management/">Google Project Management Certificate — Communication</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more">Khan Academy — Collaboration</a></li>
<li><a href="https://www.w3schools.com/">W3Schools — Learning Resources</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module4Quiz(): array
    {
        return [
            'title' => 'Module 4 Quiz: Budgeting, Procurement and People',
            'description' => 'Test your understanding of budgeting in Kwacha, procurement, team management, and running effective meetings.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main purpose of a project budget?',
                    'explanation' => 'A budget is a financial plan that shows how much money is needed and what it will be spent on.',
                    'options' => [
                        ['text' => 'To replace the project schedule', 'is_correct' => false],
                        ['text' => 'To plan project income and expenses', 'is_correct' => true],
                        ['text' => 'To list all stakeholders', 'is_correct' => false],
                        ['text' => 'To write meeting minutes', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of these is an indirect cost?',
                    'explanation' => 'Office rent is an overhead expense shared across the organisation, not tied to one project task.',
                    'options' => [
                        ['text' => 'Cement for construction', 'is_correct' => false],
                        ['text' => 'Bricks for a wall', 'is_correct' => false],
                        ['text' => 'Office rent', 'is_correct' => true],
                        ['text' => 'Labour on site', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In procurement, why is it useful to get at least three quotations?',
                    'explanation' => 'Multiple quotations allow fair comparison of price, quality and reliability before selecting a supplier.',
                    'options' => [
                        ['text' => 'To confuse the suppliers', 'is_correct' => false],
                        ['text' => 'To compare prices and value fairly', 'is_correct' => true],
                        ['text' => 'To delay the project', 'is_correct' => false],
                        ['text' => 'To avoid signing a contract', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should a project manager do when assigning a task?',
                    'explanation' => 'Each task should have one clearly responsible person to avoid confusion and duplication.',
                    'options' => [
                        ['text' => 'Assign it to as many people as possible', 'is_correct' => false],
                        ['text' => 'Give it to the sponsor', 'is_correct' => false],
                        ['text' => 'Name one responsible person', 'is_correct' => true],
                        ['text' => 'Keep the assignment secret', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Contingency money should be included in a project budget for unexpected costs.',
                    'explanation' => 'Contingency reserves help projects handle price rises, weather delays, and other unexpected events.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Meeting minutes should record every word spoken during the meeting.',
                    'explanation' => 'Minutes should record decisions and action points, not a word-for-word transcript.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the process of buying goods and services for a project called? (one word)',
                    'explanation' => 'Procurement is the process of acquiring goods, services or works for a project.',
                    'correct_answer' => 'Procurement',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of these is the best way to motivate a volunteer team?',
                    'explanation' => 'Recognition, respect, and clear purpose are strong motivators for volunteers.',
                    'options' => [
                        ['text' => 'Ignore their contributions', 'is_correct' => false],
                        ['text' => 'Recognise good work and give clear goals', 'is_correct' => true],
                        ['text' => 'Change roles every day', 'is_correct' => false],
                        ['text' => 'Cancel meetings', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does "value for money" mean in procurement?',
                    'explanation' => 'Value for money considers price, quality, reliability and service together, not just the lowest price.',
                    'options' => [
                        ['text' => 'Always choosing the cheapest option', 'is_correct' => false],
                        ['text' => 'The best balance of price, quality and reliability', 'is_correct' => true],
                        ['text' => 'Paying the highest price for the best brand', 'is_correct' => false],
                        ['text' => 'Buying without comparing quotations', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which document lists the topics to be discussed at a meeting?',
                    'explanation' => 'The agenda lists the meeting topics and helps keep the discussion focused.',
                    'options' => [
                        ['text' => 'Budget', 'is_correct' => false],
                        ['text' => 'Minutes', 'is_correct' => false],
                        ['text' => 'Agenda', 'is_correct' => true],
                        ['text' => 'WBS', 'is_correct' => false],
                    ],
                ],
            ],
        ];
    }

    private function module5Lessons(): array
    {
        return [
            [
                'title' => '5.1 Identifying and Managing Project Risks',
                'duration_minutes' => 90,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to define project risk, identify common risks for Zambian projects, assess risks by likelihood and impact, and plan responses that reduce harm or take advantage of opportunities.</p>

<h2>What Is Risk?</h2>
<p>A <strong>risk</strong> is any uncertain event that could affect a project. Risks can be negative, causing delays or extra costs, or positive, creating opportunities. Risk management does not mean avoiding all risk. It means identifying what could happen, deciding how serious it is, and preparing a response. Good risk management reduces surprises and protects the project.</p>

<h2>Common Risks in Zambian Projects</h2>
<p>Zambian projects face a familiar set of risks:</p>
<ul>
<li><strong>Weather and climate</strong> — heavy rains can stop construction, delay transport, and damage materials.</li>
<li><strong>Fuel price increases</strong> — higher transport costs affect delivery of materials and movement of staff.</li>
<li><strong>Kwacha movement</strong> — if a project buys imported goods, exchange-rate changes can raise prices.</li>
<li><strong>Late funding</strong> — donors or government may release money later than planned, slowing work.</li>
<li><strong>Load-shedding</strong> — power cuts disrupt computer work, welding, and pump operations.</li>
<li><strong>Community conflict</strong> — disagreements over land, benefits, or leadership can block progress.</li>
<li><strong>Supplier failure</strong> — a supplier may deliver late, deliver poor quality, or go out of business.</li>
</ul>

<h2>Risk Assessment</h2>
<p>After identifying risks, assess each one by <strong>likelihood</strong> and <strong>impact</strong>. Use a simple scale such as Low, Medium, High. A risk that is highly likely and highly impactful needs immediate attention. A risk that is unlikely and low impact can be monitored. This assessment helps the project manager focus limited resources on the most serious threats.</p>

<h2>Risk Responses</h2>
<p>There are several ways to respond to risks:</p>
<ul>
<li><strong>Avoid</strong> — change the plan so the risk cannot happen. For example, avoid building during the rainy season.</li>
<li><strong>Mitigate</strong> — reduce the likelihood or impact. For example, store cement under cover to prevent rain damage.</li>
<li><strong>Transfer</strong> — shift the risk to someone else. For example, buy insurance or use a contract penalty for late delivery.</li>
<li><strong>Accept</strong> — acknowledge the risk and prepare to handle it if it occurs. This is used for low-priority risks.</li>
<li><strong>Exploit</strong> — take advantage of a positive risk, such as early completion that frees up funds.</li>
</ul>

<h2>Worked Example: Risk Register for a Road Repair Project</h2>
<table>
<tr><th>Risk</th><th>Likelihood</th><th>Impact</th><th>Response</th></tr>
<tr><td>Heavy rains delay work</td><td>High</td><td>High</td><td>Schedule earthworks in dry months</td></tr>
<tr><td>Fuel prices rise</td><td>Medium</td><td>Medium</td><td>Include fuel contingency in budget</td></tr>
<tr><td>Community disputes over route</td><td>Medium</td><td>High</td><td>Hold consultations before work starts</td></tr>
<tr><td>Supplier delivers poor gravel</td><td>Low</td><td>High</td><td>Inspect materials before payment</td></tr>
</table>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a project you know, such as a construction or agriculture project.</li>
<li>List five risks that could affect it.</li>
<li>Rate each risk as Low, Medium or High for likelihood and impact.</li>
<li>Choose a response strategy for each high-priority risk.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Risk</strong> — an uncertain event that could affect the project.</li>
<li><strong>Likelihood</strong> — the chance that a risk will occur.</li>
<li><strong>Impact</strong> — the effect on the project if the risk occurs.</li>
<li><strong>Risk register</strong> — a document listing risks, their assessment, and planned responses.</li>
<li><strong>Mitigate</strong> — to reduce the likelihood or impact of a risk.</li>
</ul>

<h2>Summary</h2>
<p>Risk management is the process of identifying, assessing and responding to uncertainties. Common risks in Zambia include weather, fuel prices, kwacha movement, late funding, load-shedding, community conflict and supplier failure. By using a risk register and choosing appropriate responses, project managers can reduce surprises and protect the project schedule, budget, and reputation.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/certificates/project-management/">Google Project Management Certificate — Risk Management</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more">Khan Academy — Risk and Decision Making</a></li>
<li><a href="https://www.w3schools.com/">W3Schools — Learning Resources</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.2 Tracking Progress and Keeping Records',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to track project progress using simple methods, keep accurate records, compare actual progress to the plan, and take corrective action when a project falls behind.</p>

<h2>Why Tracking Matters</h2>
<p>Tracking progress is like checking a map while travelling. It tells you whether you are on the right road, how far you have gone, and whether you will reach your destination on time. Without tracking, problems grow unnoticed. With tracking, small issues can be fixed before they become big problems.</p>

<h2>Simple Progress Tracking Methods</h2>
<p>You do not need complex software to track progress. Useful methods include:</p>
<ul>
<li><strong>Percentage complete</strong> — estimate what percentage of each task is done.</li>
<li><strong>Milestone tracking</strong> — check whether key milestones were reached on time.</li>
<li><strong>Physical inspection</strong> — visit the site and observe actual work done.</li>
<li><strong>Timesheets</strong> — record hours worked by labourers or staff.</li>
<li><strong>Expense tracking</strong> — compare actual spending to the budget.</li>
<li><strong>Weekly reports</strong> — short updates from each team member.</li>
</ul>

<h2>Keeping Records</h2>
<p>Good records protect the project and prove accountability. Keep copies of contracts, quotations, receipts, payment vouchers, meeting minutes, attendance registers, and progress photos. In Zambia, where audits and donor reports are common, organised records are essential. Store digital copies on Google Drive or a computer, and keep paper copies in a lockable file. During load-shedding, paper records remain accessible.</p>

<h2>Comparing Actual to Planned</h2>
<p>Every week or month, compare what was planned with what actually happened. If a task is behind, ask why. Is it a lack of materials, late payment, bad weather, or poor planning? Then decide what to do: allocate more resources, change the sequence, accept a delay, or revise the schedule. Document the decision and communicate it to stakeholders.</p>

<h2>Worked Example: Weekly Progress Check</h2>
<p>A project manager visits a classroom construction site every Friday. She checks the work done during the week, takes photos, and compares it to the schedule. This week the brickwork is only 50 percent complete instead of the planned 80 percent. She learns that the brick supplier delivered late. She decides to order the next batch earlier and asks the builder to add one extra worker for three days. She records the delay and the corrective action in the project file.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Design a simple weekly progress report form for a project. Include at least five sections.</li>
<li>List five types of records a project manager should keep.</li>
<li>Describe how you would find out whether a task is behind schedule.</li>
<li>Write two sentences explaining why progress photos are useful.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Progress tracking</strong> — monitoring how much work has been done compared to the plan.</li>
<li><strong>Milestone tracking</strong> — checking whether key project points are reached on time.</li>
<li><strong>Variance</strong> — the difference between planned and actual progress or cost.</li>
<li><strong>Corrective action</strong> — steps taken to bring the project back on track.</li>
<li><strong>Accountability</strong> — being able to explain how money and resources were used.</li>
</ul>

<h2>Summary</h2>
<p>Tracking progress and keeping records are essential project management practices. Simple methods such as percentage complete, milestone tracking, site visits, and weekly reports keep the project manager informed. Comparing actual progress to the plan reveals problems early and allows corrective action. Good records demonstrate accountability and protect the project during audits or disputes.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/docs/answer/1218656">Google Sheets Help — Progress Tracking</a></li>
<li><a href="https://grow.google/certificates/project-management/">Google Project Management Certificate — Tracking Progress</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more">Khan Academy — Organisation Skills</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.3 Reporting to Funders and Sponsors',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to write clear progress reports for funders and sponsors, explain financial information simply, use appropriate formats, and understand what sponsors expect from project reports.</p>

<h2>Why Reporting Matters</h2>
<p>Funders and sponsors give money because they want to see results. Reporting shows them that their money is being used well. Good reports build trust, increase the chance of future funding, and help sponsors answer questions from their own boards or donors. Poor reporting creates suspicion and can lead to funding being stopped.</p>

<h2>What a Progress Report Should Include</h2>
<p>A good progress report is short, honest, and well-structured. It usually contains:</p>
<ul>
<li><strong>Executive summary</strong> — two or three sentences saying whether the project is on track.</li>
<li><strong>Progress against plan</strong> — what was planned, what was achieved, and any variances.</li>
<li><strong>Financial update</strong> — money received, money spent, and balance remaining.</li>
<li><strong>Challenges and risks</strong> — problems faced and how they are being managed.</li>
<li><strong>Next steps</strong> — what will be done in the next reporting period.</li>
<li><strong>Supporting evidence</strong> — photos, receipts, attendance lists, or testimonies.</li>
</ul>

<h2>Financial Reporting</h2>
<p>Financial reports should match the budget line by line. Show the budgeted amount, the actual amount spent, and the variance. Explain any large differences. For example, if cement cost more than budgeted because prices rose, say so and explain how the project absorbed the extra cost. Always keep receipts and payment records to support the figures.</p>

<h2>Choosing the Right Format</h2>
<p>Different sponsors want different formats. Some want a formal written report every quarter. Others are happy with a short email, a WhatsApp update, or a presentation slide. Ask the sponsor at the start what they need. Even if no formal report is required, regular updates strengthen the relationship.</p>

<h2>Worked Example: Quarterly Report Opening</h2>
<blockquote>
<p><strong>Project:</strong> Kalomo Community Borehole Project</p>
<p><strong>Reporting period:</strong> 1 April to 30 June</p>
<p><strong>Summary:</strong> The project is on schedule and within budget. Drilling was completed on 15 May, and the hand pump was installed on 10 June. The concrete apron will be finished by 15 July. We have spent K62,000 of the K85,250 budget. The main challenge was a two-day delay caused by heavy rain, which we recovered by extending work hours.</p>
</blockquote>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a project and write a one-page quarterly progress report.</li>
<li>Include a budget table showing planned, actual, and variance amounts.</li>
<li>Write three progress messages suitable for WhatsApp to a sponsor.</li>
<li>Explain why honesty about problems is better than hiding them in a report.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Progress report</strong> — a document that tells stakeholders what the project has achieved.</li>
<li><strong>Executive summary</strong> — a short overview of the most important information.</li>
<li><strong>Variance</strong> — the difference between planned and actual figures.</li>
<li><strong>Supporting evidence</strong> — documents or photos that prove claims in a report.</li>
<li><strong>Stakeholder update</strong> — a brief communication to keep interested parties informed.</li>
</ul>

<h2>Summary</h2>
<p>Reporting to funders and sponsors is essential for accountability and continued support. A good report includes an executive summary, progress against plan, financial update, challenges, next steps, and supporting evidence. Reports should be honest, clear, and matched to the sponsor's preferred format. Regular reporting builds trust and improves the project's reputation.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.google.com/docs/answer/88438">Google Docs Help — Writing Reports</a></li>
<li><a href="https://support.google.com/docs/answer/1218656">Google Sheets Help — Budget Tables</a></li>
<li><a href="https://grow.google/certificates/project-management/">Google Project Management Certificate — Communication</a></li>
</ul>
HTML,
            ],
            [
                'title' => '5.4 Closing a Project and Capturing Lessons Learnt',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to describe the steps for closing a project, hand over results properly, evaluate success against objectives, capture lessons learnt, and plan next steps for continuous improvement.</p>

<h2>Why Project Closure Matters</h2>
<p>Closure is the final phase of the project lifecycle. A project is not finished when the last brick is laid or the last training is delivered. It is finished when the result has been handed over, accounts are closed, reports are submitted, and lessons are captured. Poor closure leads to unfinished paperwork, disputes over defects, and lost knowledge.</p>

<h2>Steps for Closing a Project</h2>
<ol>
<li><strong>Confirm completion</strong> — verify that all deliverables meet the agreed scope and quality.</li>
<li><strong>Hand over</strong> — transfer the result to the owner or beneficiary with proper documentation.</li>
<li><strong>Close contracts</strong> — settle final payments, sign completion certificates, and release contractors.</li>
<li><strong>Finalise finances</strong> — reconcile the budget, account for all spending, and close the project account.</li>
<li><strong>Submit final reports</strong> — provide the sponsor with a completion report and financial statement.</li>
<li><strong>Evaluate success</strong> — compare results to the original objectives.</li>
<li><strong>Capture lessons learnt</strong> — document what went well, what went wrong, and what should be done differently next time.</li>
<li><strong>Celebrate</strong> — recognise the team and stakeholders who contributed.</li>
</ol>

<h2>Handover and Sustainability</h2>
<p>Handover is more than giving someone the keys. It includes training users, providing manuals or maintenance instructions, and agreeing who will operate and maintain the result. A borehole project should hand over a pump committee trained in maintenance. A classroom project should leave behind a defect list and a maintenance plan. Without this, the project result may deteriorate quickly.</p>

<h2>Capturing Lessons Learnt</h2>
<p>A <strong>lessons learnt</strong> session brings the team together to discuss experience honestly. Ask three simple questions:</p>
<ul>
<li>What went well?</li>
<li>What did not go well?</li>
<li>What should we do differently next time?</li>
</ul>
<p>Write the answers down and share them with the organisation. This knowledge improves future projects and prevents the same mistakes from being repeated.</p>

<h2>Worked Example: Lessons Learnt from a Church Hall Project</h2>
<table>
<tr><th>What Went Well</th><th>What Did Not Go Well</th><th>Do Differently Next Time</th></tr>
<tr><td>Community fundraising exceeded target</td><td>Cement prices rose during construction</td><td>Buy cement in bulk early</td></tr>
<tr><td>Builder completed work on time</td><td>Some chairs arrived damaged</td><td>Inspect deliveries before paying</td></tr>
<tr><td>Regular meetings kept everyone informed</td><td>No maintenance budget was planned</td><td>Set aside annual maintenance funds</td></tr>
</table>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a completed project you know and write three lessons learnt.</li>
<li>Describe what should be included in a project handover for a borehole.</li>
<li>Write a short project closure checklist with at least six items.</li>
<li>Explain why celebrating team contribution is important at closure.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Project closure</strong> — the final phase in which the project is formally completed.</li>
<li><strong>Handover</strong> — transferring the project result to the owner or users.</li>
<li><strong>Completion certificate</strong> — a document confirming that work has been finished satisfactorily.</li>
<li><strong>Lessons learnt</strong> — knowledge captured from experience to improve future projects.</li>
<li><strong>Sustainability</strong> — the ability of the project result to continue delivering benefits after the project ends.</li>
</ul>

<h2>Summary</h2>
<p>Project closure ensures that the result is handed over properly, finances are reconciled, reports are submitted, and lessons are captured. Handover should include training, documentation, and a maintenance plan. Lessons learnt sessions help organisations improve. Celebrating success recognises the team and closes the project on a positive note. Good closure protects the investment and prepares the organisation for the next project.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://grow.google/certificates/project-management/">Google Project Management Certificate — Project Closure</a></li>
<li><a href="https://www.khanacademy.org/college-careers-more">Khan Academy — Reflection and Growth</a></li>
<li><a href="https://www.w3schools.com/">W3Schools — Learning Resources</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module5Quiz(): array
    {
        return [
            'title' => 'Module 5 Quiz: Risk, Tracking, Reporting and Closure',
            'description' => 'Test your understanding of risk management, progress tracking, reporting to sponsors, and project closure.',
            'time_limit_minutes' => 25,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is a project risk?',
                    'explanation' => 'A risk is an uncertain event that could affect the project, either negatively or positively.',
                    'options' => [
                        ['text' => 'A guaranteed problem', 'is_correct' => false],
                        ['text' => 'An uncertain event that could affect the project', 'is_correct' => true],
                        ['text' => 'A completed task', 'is_correct' => false],
                        ['text' => 'A project sponsor', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which response reduces the likelihood or impact of a risk?',
                    'explanation' => 'Mitigation means taking action to reduce the likelihood or impact of a risk.',
                    'options' => [
                        ['text' => 'Avoid', 'is_correct' => false],
                        ['text' => 'Mitigate', 'is_correct' => true],
                        ['text' => 'Transfer', 'is_correct' => false],
                        ['text' => 'Accept', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is it important to track project progress regularly?',
                    'explanation' => 'Regular tracking reveals problems early so corrective action can be taken before delays grow.',
                    'options' => [
                        ['text' => 'To replace meetings', 'is_correct' => false],
                        ['text' => 'To find problems early and take action', 'is_correct' => true],
                        ['text' => 'To avoid talking to the team', 'is_correct' => false],
                        ['text' => 'To increase the budget', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which section should appear at the beginning of a progress report?',
                    'explanation' => 'The executive summary gives a quick overview of whether the project is on track.',
                    'options' => [
                        ['text' => 'Detailed receipts', 'is_correct' => false],
                        ['text' => 'Executive summary', 'is_correct' => true],
                        ['text' => 'Staff CVs', 'is_correct' => false],
                        ['text' => 'Supplier addresses', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Project closure happens as soon as the main construction work is finished.',
                    'explanation' => 'Closure includes handover, financial reconciliation, final reports, and capturing lessons learnt, not just finishing construction.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A lessons learnt session should discuss both what went well and what did not go well.',
                    'explanation' => 'Capturing both successes and failures helps future projects improve.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the transfer of the project result to the owner or users called? (one word)',
                    'explanation' => 'Handover is the process of transferring the completed project result to the owner or users.',
                    'correct_answer' => 'Handover',
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of these is a common risk for Zambian construction projects?',
                    'explanation' => 'Heavy rains during the wet season can delay earthworks and damage materials.',
                    'options' => [
                        ['text' => 'Too many qualified engineers', 'is_correct' => false],
                        ['text' => 'Heavy rains delaying work', 'is_correct' => true],
                        ['text' => 'Unlimited cement supply', 'is_correct' => false],
                        ['text' => 'No need for community approval', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should a project manager do with progress photos?',
                    'explanation' => 'Progress photos provide visual evidence for reports and records.',
                    'options' => [
                        ['text' => 'Delete them after one week', 'is_correct' => false],
                        ['text' => 'Use them as evidence in reports and records', 'is_correct' => true],
                        ['text' => 'Keep them only on personal phone', 'is_correct' => false],
                        ['text' => 'Print them without labels', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is sustainability in project management?',
                    'explanation' => 'Sustainability means the project result continues to deliver benefits after the project formally ends.',
                    'options' => [
                        ['text' => 'Finishing the project quickly', 'is_correct' => false],
                        ['text' => 'The project result continuing to deliver benefits after the project ends', 'is_correct' => true],
                        ['text' => 'Spending all the budget', 'is_correct' => false],
                        ['text' => 'Hiring the largest team', 'is_correct' => false],
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
            'title' => 'Plan a Small Community Project',
            'description' => 'Apply project management foundations by planning a small community project using a scope statement, WBS, schedule, budget, and stakeholder grid.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Choose a small project that could happen in your community, such as a borehole repair, a school garden, a church clean-up day, or a youth poultry start-up.
Step 2: Write a one-page project scope statement that includes: the project goal, one SMART objective, at least three in-scope items, and at least two out-of-scope items.
Step 3: Create a simple work breakdown structure with at least three major deliverables and two tasks under each deliverable.
Step 4: Draw a simple Gantt chart or timeline showing when each task will happen over a maximum of eight weeks.
Step 5: Prepare a budget in Zambian Kwacha with at least six cost items and a 10 percent contingency.
Step 6: Identify at least five stakeholders and place each one on the influence-interest grid.
Step 7: Compile your work into one document and submit it as a PDF.
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
            'title' => 'Risk Register and Final Project Report',
            'description' => 'Develop a risk register and write a final project report that demonstrates understanding of risk management, tracking, reporting and closure.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Use the same project from your first assignment, or choose a different realistic Zambian project such as a CDF road repair, a church building, or an NGO borehole project.
Step 2: Create a risk register with at least six risks. For each risk, state the likelihood (Low/Medium/High), impact (Low/Medium/High), and one response strategy (Avoid, Mitigate, Transfer, Accept, or Exploit).
Step 3: Write a one-page final project report to a sponsor or funder. Include: an executive summary, progress against plan, a financial update showing budget versus actual spending, two challenges faced, and next steps.
Step 4: Add a short lessons-learnt section with at least two things that went well, two things that did not go well, and two recommendations for future projects.
Step 5: Submit your risk register and report as one PDF document.
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
        $this->command->info('Project Management content seeded successfully.');
        $this->command->info('Modules: 5 | Lessons: 20 | Quizzes: 5 | Assignments: 2');
    }
}
