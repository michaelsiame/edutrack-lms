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

class MonitoringAndEvaluationContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Monitoring & Evaluation')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Monitoring & Evaluation" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in Monitoring & Evaluation already has modules. Skipping content seed.');
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
                'title' => 'Module 1: What Is Monitoring & Evaluation and Why Projects Need It',
                'description' => 'Understand the difference between monitoring and evaluation, learn why M&E is essential for NGOs, government and donor-funded projects in Zambia, and master the core M&E cycle.',
            ],
            [
                'title' => 'Module 2: Results Frameworks and Logic Models',
                'description' => 'Build clear results frameworks and logic models that connect project inputs and activities to outputs, outcomes and long-term impact for communities in Zambia.',
            ],
            [
                'title' => 'Module 3: Data Collection Tools and Methods',
                'description' => 'Choose the right data-collection methods for rural and urban settings, design surveys and questionnaires, and use mobile tools such as KoBoToolbox and ODK on low-bandwidth networks.',
            ],
            [
                'title' => 'Module 4: Data Management, Analysis and Reporting',
                'description' => 'Clean and manage data, perform basic analysis with spreadsheets, and write M&E reports that inform decisions by district offices, donors and community partners.',
            ],
            [
                'title' => 'Module 5: Using M&E for Learning and Decision-Making',
                'description' => 'Turn M&E findings into action, engage stakeholders, build feedback loops, and develop a practical M&E plan that your organisation can use immediately.',
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
                'title' => 'Assignment 1: Design a Monitoring Plan for a Community Project in Zambia',
                'description' => 'Create a practical monitoring plan with indicators, data sources and collection tools for a real or imagined community project in Zambia.',
                'instructions' => "<ol><li>Choose one community project (for example, a rural water point rehabilitation project in Kalomo District, a girls' retention programme in community schools, or an agriculture extension project in Choma).</li><li>Write one paragraph describing the project's goal, target group and main activities.</li><li>List at least four indicators: two output indicators (what the project produces) and two outcome indicators (what changes for beneficiaries). Make each indicator SMART.</li><li>For each indicator, state the data source (for example, clinic register, KoBo survey, school attendance book, farmer focus group), who will collect the data, and how often.</li><li>Include one simple data-collection tool: either five survey questions, a focus-group discussion guide, or an observation checklist.</li><li>Save your work as a Word document or PDF and upload it here. Name the file: ME_MonitoringPlan_YourName.</li></ol>",
                'due_date' => now()->addWeeks(2),
            ],
            [
                'title' => 'Assignment 2: Build a Results Framework / Logic Model for a School-Feeding or Health-Outreach Programme',
                'description' => 'Develop a complete results framework or logic model that links resources, activities, outputs, outcomes and impact for a Zambian social programme.',
                'instructions' => "<ol><li>Choose either a school-feeding programme in a rural community school OR a mobile health-outreach programme serving rural clinics.</li><li>Draw or describe a logic model with five levels: Inputs, Activities, Outputs, Outcomes and Impact. Use a table or diagram.</li><li>For each level, list at least three concrete examples relevant to Zambia (for example, inputs: maize, beans, volunteer cooks; activities: cook meals, serve pupils, train cooks).</li><li>Write at least two SMART indicators for outputs and two for outcomes, with baselines and targets.</li><li>Add a short paragraph explaining how you would use the framework to report to the Ministry of Education or Ministry of Health and to a donor.</li><li>Save your work as a Word document or PDF and upload it here. Name the file: ME_LogicModel_YourName.</li></ol>",
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
                'title' => 'Lesson 1.1: The Difference Between Monitoring and Evaluation',
                'duration_minutes' => 60,
                'content' => $this->lesson1_1(),
            ],
            [
                'title' => 'Lesson 1.2: Why M&E Matters for Zambian Projects and Organisations',
                'duration_minutes' => 75,
                'content' => $this->lesson1_2(),
            ],
            [
                'title' => 'Lesson 1.3: The M&E Cycle and Key Terms',
                'duration_minutes' => 60,
                'content' => $this->lesson1_3(),
            ],
            [
                'title' => 'Module 1 Quiz: Introduction to Monitoring & Evaluation',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Complete this quiz to check your understanding of monitoring, evaluation, the M&amp;E cycle and key terms used in Zambian projects. You need 60% to pass. Good luck!</p>',
            ],
        ];
    }

    private function lesson1_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to explain the difference between monitoring and evaluation in plain language, describe when each is used, and give examples from real projects in Zambia.</p>

<figure><img class="lesson-diagram" src="/assets/diagrams/monitoring-evaluation/me-cycle.svg" alt="The M&amp;E cycle showing five stages: Plan, Collect, Analyse, Report and Use."><figcaption>Figure: The M&amp;E cycle used by NGOs and government programmes in Zambia.</figcaption></figure>

<h2>What Is Monitoring?</h2>
<p>Monitoring is the regular collection and review of information about a project while it is running. It answers the question, "Are we doing what we said we would do, on time and with the resources we planned?" Monitoring is like checking the speedometer and fuel gauge when you are driving from Kalomo to Lusaka. You do it continuously so you can correct course before you run out of fuel or miss a turn.</p>
<p>For example, imagine an NGO is distributing mosquito nets in Choma District. Monitoring would track how many nets were distributed each week, how many villages were reached, how much money was spent, and whether the distribution followed the agreed schedule. These numbers are usually collected from registers, tally sheets, or mobile forms and checked against monthly targets.</p>

<h2>What Is Evaluation?</h2>
<p>Evaluation is a deeper assessment that asks, "Is the project working? Is it making a difference? And is it worth the money and effort?" Evaluations usually happen at specific moments — at the middle or end of a project, or after a pilot phase — and they look at outcomes and impact, not just activities.</p>
<p>Using the same mosquito net example, an evaluation would ask whether the nets actually reduced malaria cases in children under five, whether families used the nets correctly, and whether the project reached the poorest households. Evaluations often compare data before and after the project, or compare villages that received nets with villages that did not.</p>

<h2>The Key Differences</h2>
<table>
<tr><th>Monitoring</th><th>Evaluation</th></tr>
<tr><td>Asks "Are we on track?"</td><td>Asks "Did we make a difference?"</td></tr>
<tr><td>Happens regularly (daily, weekly, monthly)</td><td>Happens at specific points (midline, endline)</td></tr>
<tr><td>Focuses on activities, outputs and spending</td><td>Focuses on outcomes, impact and value for money</td></tr>
<tr><td>Uses simple tools like registers and checklists</td><td>Uses surveys, interviews, comparisons and analysis</td></tr>
<tr><td>Managed by project staff</td><td>Often involves external evaluators for objectivity</td></tr>
</table>

<h2>Why Both Are Needed</h2>
<p>Monitoring without evaluation can keep a project busy without knowing whether it is changing lives. Evaluation without monitoring can be a one-off judgment that misses problems while the project is still running. Strong projects in Zambia combine both: monitoring keeps things moving, and evaluation proves whether the investment was worthwhile.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Think of a project you know (a school, a clinic, an NGO programme, or a TEVETA training).</li>
<li>List three pieces of information you would monitor every month.</li>
<li>List two questions you would ask in an end-of-project evaluation.</li>
<li>Explain the difference to a colleague in two sentences.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Monitoring</strong>: Regular tracking of project activities, outputs and resources against a plan.</li>
<li><strong>Evaluation</strong>: Systematic assessment of a project's results, effectiveness and impact.</li>
<li><strong>Output</strong>: A direct product or service produced by a project (for example, 500 people trained).</li>
<li><strong>Outcome</strong>: A short- to medium-term change resulting from outputs (for example, farmers applying new techniques).</li>
<li><strong>Impact</strong>: Long-term change in people's lives or conditions (for example, reduced malaria deaths).</li>
</ul>

<h2>Summary</h2>
<p>Monitoring tells you whether a project is being implemented as planned. Evaluation tells you whether the project is achieving its intended results. Both are essential for accountable, learning-oriented organisations in Zambia.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.betterevaluation.org/" target="_blank" rel="noopener">BetterEvaluation — Monitoring and Evaluation Frameworks</a></li>
<li><a href="https://www.mande.co.uk/" target="_blank" rel="noopener">M&E News &amp; Resources</a></li>
<li><a href="https://www.unicef.org/reports/what-why-and-how-monitoring-evaluation" target="_blank" rel="noopener">UNICEF — What, Why and How of M&E</a></li>
</ul>
HTML;
    }

    private function lesson1_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will understand why monitoring and evaluation are critical for NGOs, government ministries, donors and community-based organisations in Zambia, and you will be able to explain the benefits of good M&E to colleagues and managers.</p>

<h2>M&E Is About Accountability and Learning</h2>
<p>Every project in Zambia — whether run by the Ministry of Health, an NGO distributing food, a donor-funded education programme, or a community-based organisation (CBO) supporting orphans — uses resources that could have been spent elsewhere. M&E helps organisations answer two hard questions: "Did we use our resources well?" and "Did we make life better for the people we serve?"</p>
<p>Accountability means being able to show stakeholders — government, donors, community leaders, beneficiaries — what was done and what changed. Learning means using that information to improve the next round of work. A project that learns from its M&E data becomes more effective over time.</p>

<h2>Real Examples from Zambia</h2>
<p><strong>Ministry of Health:</strong> M&E systems track immunisation coverage, antenatal visits, malaria cases, and stock levels at rural clinics. Without monitoring, a clinic in Kalomo District might run out of vaccines. Without evaluation, the Ministry would not know whether a new community health worker programme actually reduced child deaths.</p>
<p><strong>Ministry of Education:</strong> The Ministry monitors school enrolment, teacher attendance, and examination pass rates. Evaluations tell policymakers whether school-feeding programmes improve attendance and learning, or whether capitation grants are reaching schools.</p>
<p><strong>NGOs and donor projects:</strong> A water, sanitation and hygiene (WASH) NGO might monitor borehole repairs and latrine construction. An evaluation would ask whether waterborne diseases fell after the intervention. Donors such as USAID, the EU, and the Global Fund require regular M&E reports before releasing the next tranche of funding.</p>
<p><strong>TEVETA and skills training:</strong> TEVETA-registered colleges like Edutrack monitor student attendance, completion rates, and exam results. Evaluations track whether graduates find work or start businesses, which proves the value of vocational training.</p>

<h2>Benefits of Good M&E</h2>
<ul>
<li><strong>Better decisions:</strong> Managers can see what is working and shift resources away from what is not.</li>
<li><strong>Early warning:</strong> Monitoring flags problems such as delayed supplies, low attendance, or budget shortfalls before they become crises.</li>
<li><strong>Donor confidence:</strong> Reliable data builds trust with funders and improves the chance of continued support.</li>
<li><strong>Community trust:</strong> When communities see transparent reports, they are more likely to participate and support the project.</li>
<li><strong>Evidence for scale-up:</strong> A successful pilot evaluated well can become the basis for a national programme.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Identify one organisation or project in your community (for example, a rural clinic, school, or NGO).</li>
<li>List three people or groups who need information about that project's performance.</li>
<li>For each group, write one sentence explaining what they need to know and why.</li>
<li>Discuss with a colleague: what could go wrong if the project had no M&E system?</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Accountability</strong>: The obligation to report on and be responsible for the use of resources and results achieved.</li>
<li><strong>Stakeholder</strong>: Any person or group with an interest in the project, including beneficiaries, donors, government and staff.</li>
<li><strong>Evidence-based decision-making</strong>: Making choices using data and analysis rather than assumptions.</li>
<li><strong>Value for money</strong>: Whether the benefits of a project justify the costs.</li>
<li><strong>Scale-up</strong>: Expanding a successful intervention to reach more people or areas.</li>
</ul>

<h2>Summary</h2>
<p>In Zambia, M&E is not just paperwork for donors. It is a practical tool that helps the Ministry of Health save lives, the Ministry of Education improve schools, NGOs serve communities better, and training colleges prove their impact. Good M&E builds accountability, supports learning, and protects scarce resources.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.gov.zm/" target="_blank" rel="noopener">Government of Zambia — Ministries and Policies</a></li>
<li><a href="https://www.tevet.org.zm/" target="_blank" rel="noopener">TEVETA Zambia</a></li>
<li><a href="https://www.who.int/data/gho/data/themes/topics/immunization" target="_blank" rel="noopener">WHO — Immunization Monitoring</a></li>
</ul>
HTML;
    }

    private function lesson1_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to describe the five stages of the M&E cycle, define common M&E terms, and explain how the cycle is used in a real Zambian project.</p>

<figure><img class="lesson-diagram" src="/assets/diagrams/monitoring-evaluation/me-cycle.svg" alt="The M&amp;E cycle showing five stages: Plan, Collect, Analyse, Report and Use."><figcaption>Figure: The repeating M&amp;E cycle keeps projects on track and focused on results.</figcaption></figure>

<h2>The Five Stages of the M&E Cycle</h2>
<p>The M&E cycle is not a straight line. It is a loop that repeats throughout the life of a project. The five stages are Plan, Collect, Analyse, Report and Use. Each stage depends on the one before it, and the final stage feeds back into the first.</p>

<h3>Stage 1: Plan</h3>
<p>Before any data is collected, the project must decide what success looks like. This means defining indicators, setting targets, choosing data-collection methods, and assigning responsibilities. A good M&E plan answers: What will we measure? How will we measure it? Who will collect the data? When? And how will we use it?</p>
<p>For example, a community school literacy project in Kalomo District might plan to measure "percentage of Grade 3 pupils reading at grade level" every term, using a standard oral reading assessment administered by teachers.</p>

<h3>Stage 2: Collect</h3>
<p>Collection is the regular gathering of data using tools such as attendance registers, surveys, focus groups, observation checklists, and mobile data forms. In rural Zambia, many organisations use KoBoToolbox or ODK because these tools work offline and sync when the enumerator returns to an area with mobile network coverage.</p>

<h3>Stage 3: Analyse</h3>
<p>Analysis turns raw numbers into useful information. This can be as simple as counting how many people attended a training, or as complex as comparing baseline and endline survey results. Spreadsheets such as Microsoft Excel or LibreOffice Calc are common because they work well on low-bandwidth connections and do not require expensive software.</p>

<h3>Stage 4: Report</h3>
<p>Reporting shares findings with the people who need them. A report for a donor may be long and detailed. A report for a village development committee may be a one-page poster in the local language. Good reports are timely, accurate and focused on decisions.</p>

<h3>Stage 5: Use</h3>
<p>The final stage is using the information to make decisions. This is where M&E creates value. If data shows that attendance drops during the rainy season, the project might adjust its schedule. If an evaluation shows that a training method is not changing behaviour, the project might redesign the curriculum.</p>

<h2>Key M&E Terms</h2>
<ul>
<li><strong>Indicator</strong>: A specific, measurable sign that shows whether a project is achieving its objectives.</li>
<li><strong>Baseline</strong>: Data collected at the start of a project, used for comparison later.</li>
<li><strong>Target</strong>: The planned level of achievement for an indicator by a specific date.</li>
<li><strong>Data source</strong>: The place or tool from which data is obtained (for example, a clinic register).</li>
<li><strong>Frequency</strong>: How often data is collected (daily, weekly, monthly, quarterly, annually).</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a project (for example, a school-feeding programme or a mobile clinic).</li>
<li>Write one activity for each of the five M&E cycle stages.</li>
<li>Identify one indicator and state its baseline, target, data source and frequency.</li>
<li>Describe one decision that could be made from the data.</li>
</ol>

<h2>Summary</h2>
<p>The M&E cycle moves from planning what to measure, through collecting and analysing data, to reporting and using findings. Because it is a cycle, the lessons learned from one period improve the plan for the next. This keeps projects relevant, accountable and focused on results.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.betterevaluation.org/" target="_blank" rel="noopener">BetterEvaluation — M&E Planning</a></li>
<li><a href="https://www.kobotoolbox.org/" target="_blank" rel="noopener">KoBoToolbox — Mobile Data Collection</a></li>
<li><a href="https://www.unicef.org/reports/what-why-and-how-monitoring-evaluation" target="_blank" rel="noopener">UNICEF — M&E Essentials</a></li>
</ul>
HTML;
    }


    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: Introduction to Monitoring & Evaluation',
            'description' => 'Test your understanding of the difference between monitoring and evaluation, the M&E cycle, and key terms used in Zambian projects.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which question best describes the main purpose of monitoring?',
                    'explanation' => 'Monitoring focuses on whether a project is being implemented according to plan, on time and within budget.',
                    'options' => [
                        ['text' => 'Did the project change people\'s lives in the long term?', 'is_correct' => false],
                        ['text' => 'Are we doing what we said we would do, on time and with the resources planned?', 'is_correct' => true],
                        ['text' => 'Should we expand the project to other districts?', 'is_correct' => false],
                        ['text' => 'Which external evaluator should we hire?', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main focus of an evaluation?',
                    'explanation' => 'Evaluations focus on outcomes, impact and value for money, usually at specific points in the project.',
                    'options' => [
                        ['text' => 'Daily attendance of project staff', 'is_correct' => false],
                        ['text' => 'Whether the project made a difference and was worth the investment', 'is_correct' => true],
                        ['text' => 'The exact number of pens purchased', 'is_correct' => false],
                        ['text' => 'The monthly mobile data allowance', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In the M&E cycle, which stage comes immediately after data collection?',
                    'explanation' => 'After data is collected, it must be analysed before it can be reported or used.',
                    'options' => [
                        ['text' => 'Report', 'is_correct' => false],
                        ['text' => 'Use', 'is_correct' => false],
                        ['text' => 'Analyse', 'is_correct' => true],
                        ['text' => 'Plan', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is an example of an output?',
                    'explanation' => 'An output is a direct product or service of the project, such as the number of people trained.',
                    'options' => [
                        ['text' => 'Reduced malaria deaths in children under five', 'is_correct' => false],
                        ['text' => '500 community health workers trained', 'is_correct' => true],
                        ['text' => 'Improved community trust in the clinic', 'is_correct' => false],
                        ['text' => 'Donor satisfaction with the project', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why do many Zambian organisations use KoBoToolbox or ODK for data collection?',
                    'explanation' => 'KoBoToolbox and ODK work offline and sync when network coverage is available, making them suitable for rural areas with low bandwidth.',
                    'options' => [
                        ['text' => 'They are the only tools approved by the Ministry of Finance', 'is_correct' => false],
                        ['text' => 'They work offline and sync later when mobile coverage returns', 'is_correct' => true],
                        ['text' => 'They automatically write donor reports', 'is_correct' => false],
                        ['text' => 'They do not require any training', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Monitoring and evaluation mean the same thing and can be used interchangeably.',
                    'explanation' => 'Monitoring and evaluation are related but distinct. Monitoring tracks ongoing implementation, while evaluation assesses results and impact.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A baseline is data collected at the start of a project to compare against later results.',
                    'explanation' => 'A baseline provides a starting point that allows a project to measure change over time.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the five-letter acronym for the cycle that includes Plan, Collect, Analyse, Report and Use?',
                    'explanation' => 'The M&E cycle includes the stages Plan, Collect, Analyse, Report and Use.',
                    'correct_answer' => 'M&E',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for a specific, measurable sign that shows whether a project is achieving its objectives?',
                    'explanation' => 'An indicator is a measurable marker used to track progress and results.',
                    'correct_answer' => 'Indicator',
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
                'title' => 'Lesson 2.1: Building a Results Framework',
                'duration_minutes' => 60,
                'content' => $this->lesson2_1(),
            ],
            [
                'title' => 'Lesson 2.2: Logic Models — Inputs, Activities, Outputs, Outcomes, Impact',
                'duration_minutes' => 75,
                'content' => $this->lesson2_2(),
            ],
            [
                'title' => 'Lesson 2.3: Indicators — SMART and Practical',
                'duration_minutes' => 60,
                'content' => $this->lesson2_3(),
            ],
            [
                'title' => 'Module 2 Quiz: Results Frameworks and Indicators',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your knowledge of results frameworks, logic models and SMART indicators for Zambian projects. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson2_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to explain what a results framework is, describe its main components, and create a simple results framework for a small project in Zambia.</p>

<h2>What Is a Results Framework?</h2>
<p>A results framework is a table or matrix that summarises what a project intends to achieve and how it will measure success. It links activities to outputs, outcomes and impact, and it specifies the indicators, baselines, targets and data sources for each level. Donors such as USAID, GIZ and the EU often require a results framework as part of a grant proposal.</p>
<p>Think of a results framework as a project's report card written in advance. It tells everyone — staff, donors, government partners and communities — what the project will deliver and how progress will be judged.</p>

<h2>Components of a Results Framework</h2>
<p>A typical results framework has the following columns:</p>
<ul>
<li><strong>Results statement</strong>: What the project expects to achieve at each level (output, outcome, impact).</li>
<li><strong>Indicator</strong>: A measurable sign of progress.</li>
<li><strong>Baseline</strong>: The value of the indicator before the project starts.</li>
<li><strong>Target</strong>: The value the project aims to reach by a specific date.</li>
<li><strong>Data source</strong>: Where the information will come from.</li>
<li><strong>Frequency</strong>: How often the indicator will be measured.</li>
<li><strong>Responsibility</strong>: Who will collect and report the data.</li>
</ul>

<h2>Example: Agricultural Extension Project in Choma</h2>
<table>
<tr><th>Level</th><th>Results Statement</th><th>Indicator</th><th>Baseline</th><th>Target</th></tr>
<tr><td>Output</td><td>Farmers trained in conservation farming</td><td>Number of farmers trained</td><td>0</td><td>500 by end of year 1</td></tr>
<tr><td>Outcome</td><td>Farmers adopt conservation farming practices</td><td>% of trained farmers using mulching and crop rotation</td><td>15%</td><td>60% by end of year 2</td></tr>
<tr><td>Impact</td><td>Improved food security in target communities</td><td>% of households reporting adequate food for 12 months</td><td>45%</td><td>65% by end of year 3</td></tr>
</table>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a simple project, such as a school library, a community garden, or a vocational skills class.</li>
<li>Write one results statement at output, outcome and impact level.</li>
<li>For each level, write one indicator, a baseline, and a target.</li>
<li>State the data source and how often it will be measured.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Results framework</strong>: A summary table linking project objectives to indicators, baselines and targets.</li>
<li><strong>Results statement</strong>: A brief description of the expected change at a given level.</li>
<li><strong>Baseline</strong>: The starting value of an indicator before the intervention.</li>
<li><strong>Target</strong>: The planned level to be reached by a specific time.</li>
<li><strong>Data source</strong>: The document, system or method used to obtain indicator data.</li>
</ul>

<h2>Summary</h2>
<p>A results framework translates a project plan into measurable results. It is one of the most important documents in M&E because it defines what success looks like and how it will be measured.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.usaid.gov/sites/default/files/documents/1870/USAID-AFR-Results-Framework-Guide.pdf" target="_blank" rel="noopener">USAID — Results Framework Guide</a></li>
<li><a href="https://www.betterevaluation.org/" target="_blank" rel="noopener">BetterEvaluation — Results Frameworks</a></li>
<li><a href="https://www.giz.de/en/html/index.html" target="_blank" rel="noopener">GIZ — Planning and Results-Based Monitoring</a></li>
</ul>
HTML;
    }

    private function lesson2_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to draw and explain a logic model, distinguish between inputs, activities, outputs, outcomes and impact, and apply the model to a Zambian social programme.</p>

<figure><img class="lesson-diagram" src="/assets/diagrams/monitoring-evaluation/logic-model.svg" alt="Logic model for a school feeding programme showing inputs, activities, outputs, outcomes and impact."><figcaption>Figure: A logic model shows how a project's resources and activities lead to results.</figcaption></figure>

<h2>What Is a Logic Model?</h2>
<p>A logic model is a simple diagram that shows the logical connection between what a project puts in, what it does, what it produces, and what changes result. It is often summarised as "if-then" thinking: if we have these inputs and do these activities, then we expect these outputs, outcomes and impacts.</p>
<p>Logic models are useful because they make assumptions visible. If a school-feeding programme assumes that meals will improve attendance, the logic model shows that assumption clearly and allows the project to test it.</p>

<h2>The Five Levels of a Logic Model</h2>
<p><strong>Inputs</strong> are the resources a project needs: money, staff, materials, equipment, vehicles, and office space. For a rural health outreach in Kalomo District, inputs might include a mobile clinic vehicle, nurses, vaccines, fuel, and a district health office grant.</p>
<p><strong>Activities</strong> are the actions the project takes using the inputs. Examples include conducting immunisation sessions, training community health workers, holding parent meetings, and distributing health education materials.</p>
<p><strong>Outputs</strong> are the direct, countable products of activities. Examples include "1,200 children immunised," "30 community health workers trained," or "50 community meetings held."</p>
<p><strong>Outcomes</strong> are the short- to medium-term changes in knowledge, behaviour or conditions. For the health outreach, outcomes might include "80% of under-1 children fully immunised" or "increased use of oral rehydration salts for diarrhoea."</p>
<p><strong>Impact</strong> is the long-term change in people's lives or conditions. For the same outreach, impact might be "reduced child mortality in target communities" or "improved overall community health status."</p>

<h2>Worked Example: School Feeding in a Community School</h2>
<table>
<tr><th>Level</th><th>Example</th></tr>
<tr><td>Inputs</td><td>Maize, beans, cooking oil, volunteer cooks, pots, stoves, grant from Ministry of Education</td></tr>
<tr><td>Activities</td><td>Cook meals, serve pupils, train cooks on food safety, monitor stocks</td></tr>
<tr><td>Outputs</td><td>450 pupils fed daily, 225 school days of meals provided, 12 cooks trained</td></tr>
<tr><td>Outcomes</td><td>Improved attendance, better concentration in class, higher retention to Grade 7</td></tr>
<tr><td>Impact</td><td>Improved literacy and health, reduced dropout rates</td></tr>
</table>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a programme: either a school-feeding programme or a mobile health clinic.</li>
<li>Draw a logic model with five boxes.</li>
<li>Fill each box with at least three realistic examples from Zambia.</li>
<li>Write one sentence explaining the "if-then" logic between activities and outcomes.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Logic model</strong>: A diagram showing the relationship between inputs, activities, outputs, outcomes and impact.</li>
<li><strong>Inputs</strong>: Resources used by a project.</li>
<li><strong>Activities</strong>: Actions the project takes to achieve its objectives.</li>
<li><strong>Outputs</strong>: Direct, countable products of activities.</li>
<li><strong>Assumption</strong>: A belief about how the project will work that is built into the logic model.</li>
</ul>

<h2>Summary</h2>
<p>A logic model makes the theory behind a project visible. It shows how inputs become activities, activities become outputs, and outputs lead to outcomes and impact. This clarity helps teams plan better, communicate with stakeholders, and identify what to measure.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.cdc.gov/evaluation/index.htm" target="_blank" rel="noopener">CDC — Logic Models</a></li>
<li><a href="https://www.betterevaluation.org/" target="_blank" rel="noopener">BetterEvaluation — Logic Models</a></li>
<li><a href="https://www.unicef.org/reports/what-why-and-how-monitoring-evaluation" target="_blank" rel="noopener">UNICEF — M&E Essentials</a></li>
</ul>
HTML;
    }

    private function lesson2_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to write SMART indicators, choose appropriate data sources, and explain why some indicators are better than others for projects in Zambia.</p>

<h2>What Makes a Good Indicator?</h2>
<p>An indicator is a measurable sign that shows whether a project is making progress. A good indicator is clear, relevant, measurable, reliable, and cost-effective to collect. Bad indicators are vague, impossible to measure, or so expensive to track that they drain the project budget.</p>
<p>For example, "improved community health" is too vague to be an indicator. "Percentage of children under one year fully immunised" is specific and measurable. "Number of boreholes rehabilitated" is clear. "Community happiness with water" is harder to measure unless a survey is designed carefully.</p>

<h2>The SMART Test</h2>
<p>SMART is a simple checklist for indicators:</p>
<ul>
<li><strong>Specific</strong>: The indicator clearly states what is being measured.</li>
<li><strong>Measurable</strong>: Data can be collected and expressed as a number or clear category.</li>
<li><strong>Achievable</strong>: It is realistic to collect the data with available resources.</li>
<li><strong>Relevant</strong>: The indicator directly relates to the project's objective.</li>
<li><strong>Time-bound</strong>: There is a clear deadline or frequency for measurement.</li>
</ul>

<h2>Examples: From Vague to SMART</h2>
<table>
<tr><th>Vague</th><th>SMART</th></tr>
<tr><td>Farmers trained well</td><td>Number of farmers who passed a post-training assessment with 70% or higher by 30 June 2026</td></tr>
<tr><td>Better school attendance</td><td>Average daily attendance in target schools increases from 72% to 85% by end of term 2</td></tr>
<tr><td>Community aware of HIV services</td><td>Percentage of adults in target villages who can name three HIV prevention services, measured by household survey</td></tr>
<tr><td>Clinics have supplies</td><td>Percentage of targeted clinics with no stock-outs of essential medicines in the past month</td></tr>
</table>

<h2>Quantitative and Qualitative Indicators</h2>
<p><strong>Quantitative indicators</strong> use numbers: counts, percentages, ratios, averages. They are useful for measuring coverage, attendance, and expenditure. <strong>Qualitative indicators</strong> describe quality, experience, or opinion. They are useful for understanding why something happened or how people feel about a service.</p>
<p>A strong M&E system uses both. For example, a quantitative indicator might show that 90% of pupils received meals. A qualitative indicator might show that pupils and teachers believe the meals improved concentration.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write one indicator for a project goal of your choice.</li>
<li>Check it against the SMART criteria and revise if needed.</li>
<li>State whether it is quantitative or qualitative.</li>
<li>Describe how you would collect the data in a rural Zambian setting.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>SMART indicator</strong>: An indicator that is Specific, Measurable, Achievable, Relevant and Time-bound.</li>
<li><strong>Quantitative indicator</strong>: An indicator expressed as a number.</li>
<li><strong>Qualitative indicator</strong>: An indicator describing quality, opinion or experience.</li>
<li><strong>Proxy indicator</strong>: An indirect measure used when the ideal indicator is hard to measure.</li>
<li><strong>Disaggregation</strong>: Breaking data into subgroups such as sex, age, location or disability status.</li>
</ul>

<h2>Summary</h2>
<p>Good indicators are the foundation of good M&E. Applying the SMART test ensures that indicators are clear, measurable and useful for decision-making. Mixing quantitative and qualitative indicators gives a fuller picture of what a project achieves.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.betterevaluation.org/en/evaluation-options/SMARTindicators" target="_blank" rel="noopener">BetterEvaluation — SMART Indicators</a></li>
<li><a href="https://www.usaid.gov/" target="_blank" rel="noopener">USAID — Performance Indicator Reference Sheets</a></li>
<li><a href="https://www.who.int/data/gho" target="_blank" rel="noopener">WHO — Global Health Observatory Indicators</a></li>
</ul>
HTML;
    }


    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Results Frameworks and Indicators',
            'description' => 'Assess your understanding of results frameworks, logic models and SMART indicators.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main purpose of a results framework?',
                    'explanation' => 'A results framework links project objectives to indicators, baselines and targets so progress can be measured and reported.',
                    'options' => [
                        ['text' => 'To list every employee in the project', 'is_correct' => false],
                        ['text' => 'To summarise what a project will achieve and how success will be measured', 'is_correct' => true],
                        ['text' => 'To replace the project budget', 'is_correct' => false],
                        ['text' => 'To advertise the project to the public', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In a logic model, which level describes direct, countable products of project activities?',
                    'explanation' => 'Outputs are the direct, countable products of activities, such as the number of people trained or meetings held.',
                    'options' => [
                        ['text' => 'Inputs', 'is_correct' => false],
                        ['text' => 'Outcomes', 'is_correct' => false],
                        ['text' => 'Outputs', 'is_correct' => true],
                        ['text' => 'Impact', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is the best example of a SMART indicator?',
                    'explanation' => 'A SMART indicator is specific, measurable, achievable, relevant and time-bound.',
                    'options' => [
                        ['text' => 'Improve community health', 'is_correct' => false],
                        ['text' => 'Train many farmers soon', 'is_correct' => false],
                        ['text' => 'Number of farmers trained in conservation farming in Choma District by 30 June 2026', 'is_correct' => true],
                        ['text' => 'Make schools better', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'For a school-feeding programme, which item is an input?',
                    'explanation' => 'Inputs are resources such as food, staff, equipment and money used to run the programme.',
                    'options' => [
                        ['text' => 'Higher pupil attendance', 'is_correct' => false],
                        ['text' => '450 pupils fed daily', 'is_correct' => false],
                        ['text' => 'Maize, beans and cooking oil', 'is_correct' => true],
                        ['text' => 'Improved literacy rates', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does the "M" in SMART stand for?',
                    'explanation' => 'SMART stands for Specific, Measurable, Achievable, Relevant and Time-bound.',
                    'options' => [
                        ['text' => 'Manageable', 'is_correct' => false],
                        ['text' => 'Measurable', 'is_correct' => true],
                        ['text' => 'Mandatory', 'is_correct' => false],
                        ['text' => 'Major', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'In a logic model, outcomes come before outputs.',
                    'explanation' => 'Outputs come before outcomes. Outputs are direct products; outcomes are the changes that result from those outputs.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A baseline is the value of an indicator measured after the project ends.',
                    'explanation' => 'A baseline is measured at the start of a project, before the intervention begins.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for the long-term change in people\'s lives or conditions in a logic model?',
                    'explanation' => 'Impact is the long-term change that a project aims to contribute to.',
                    'correct_answer' => 'Impact',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What does the "T" in SMART stand for?',
                    'explanation' => 'The T in SMART stands for Time-bound, meaning the indicator has a clear deadline.',
                    'correct_answer' => 'Time-bound',
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
                'title' => 'Lesson 3.1: Choosing Data Collection Methods',
                'duration_minutes' => 60,
                'content' => $this->lesson3_1(),
            ],
            [
                'title' => 'Lesson 3.2: Mobile Data Collection with KoBoToolbox and ODK',
                'duration_minutes' => 75,
                'content' => $this->lesson3_2(),
            ],
            [
                'title' => 'Lesson 3.3: Designing Surveys and Questionnaires',
                'duration_minutes' => 60,
                'content' => $this->lesson3_3(),
            ],
            [
                'title' => 'Lesson 3.4: Data Quality and Ethics',
                'duration_minutes' => 60,
                'content' => $this->lesson3_4(),
            ],
            [
                'title' => 'Module 3 Quiz: Data Collection Tools and Methods',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your understanding of data collection methods, mobile tools, survey design, and ethical data handling. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson3_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to choose appropriate data-collection methods for different M&E questions, and explain the strengths and weaknesses of each method in the Zambian context.</p>

<h2>Matching Methods to Questions</h2>
<p>The first step in choosing a data-collection method is to be clear about what you need to know. Different questions need different methods. If you want to know how many people attended a training, you count. If you want to know why attendance dropped, you ask people. If you want to observe whether a clinic follows infection-control procedures, you watch.</p>

<h2>Common Data Collection Methods</h2>
<p><strong>Quantitative methods</strong> produce numbers. They include surveys, structured interviews, tests, and reviews of records. These methods are good for measuring coverage, frequency, and change over time. For example, a household survey can measure the percentage of families using bed nets in Southern Province.</p>
<p><strong>Qualitative methods</strong> produce descriptions, stories, and explanations. They include focus group discussions, in-depth interviews, observation, and document review. These methods help explain why something happened. For example, a focus group can reveal that families do not use bed nets because they feel too hot.</p>

<h2>When to Use Each Method</h2>
<table>
<tr><th>Method</th><th>Best for</th><th>Example in Zambia</th></tr>
<tr><td>Household survey</td><td>Measuring prevalence, coverage, behaviour</td><td>Percentage of households with hand-washing facilities</td></tr>
<tr><td>Focus group discussion</td><td>Exploring opinions, norms, experiences</td><td>Why mothers prefer traditional birth attendants</td></tr>
<tr><td>Key informant interview</td><td>Expert or leader perspectives</td><td>Interviewing a district agriculture officer about extension services</td></tr>
<tr><td>Observation</td><td>Seeing actual practice</td><td>Checking whether teachers use new reading techniques</td></tr>
<tr><td>Document review</td><td>Tracking outputs and expenditures</td><td>Reviewing clinic registers and financial reports</td></tr>
<tr><td>Case study</td><td>Deep understanding of one situation</td><td>Following one community school through a literacy intervention</td></tr>
</table>

<h2>Practical Considerations in Zambia</h2>
<p>Rural areas may have poor roads, limited mobile network, and long distances between settlements. These factors affect method choice. A long paper survey may be hard to transport and enter. A mobile survey that works offline may be better. Focus groups may need to be scheduled around farming seasons and market days. Interviews may need to be conducted in local languages.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write one M&E question that is best answered with a number.</li>
<li>Write one M&E question that is best answered with a story or explanation.</li>
<li>For each question, choose the best method and explain why.</li>
<li>List two practical challenges you might face collecting this data in rural Zambia.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Quantitative data</strong>: Information expressed as numbers.</li>
<li><strong>Qualitative data</strong>: Information describing qualities, experiences or meanings.</li>
<li><strong>Focus group discussion</strong>: A guided group conversation used to explore views and experiences.</li>
<li><strong>Key informant interview</strong>: An in-depth interview with a person who has special knowledge.</li>
<li><strong>Triangulation</strong>: Using multiple methods or sources to strengthen confidence in findings.</li>
</ul>

<h2>Summary</h2>
<p>Choosing the right data-collection method depends on the M&E question, the context, and available resources. Strong M&E often combines quantitative and qualitative methods to get both numbers and explanations.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.betterevaluation.org/" target="_blank" rel="noopener">BetterEvaluation — Data Collection Methods</a></li>
<li><a href="https://www.unicef.org/reports/what-why-and-how-monitoring-evaluation" target="_blank" rel="noopener">UNICEF — M&E Methods</a></li>
<li><a href="https://www.who.int/classifications/icf/en/" target="_blank" rel="noopener">WHO — Data Collection for Health</a></li>
</ul>
HTML;
    }

    private function lesson3_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to set up a simple mobile data-collection form using KoBoToolbox or ODK, explain the benefits of mobile data collection in rural Zambia, and describe how to manage data when internet access is limited.</p>

<h2>Why Mobile Data Collection?</h2>
<p>In Zambia, many M&E officers spend days travelling to remote villages to collect data. Paper forms can be lost, damaged by rain, or entered incorrectly into a computer. Mobile data collection solves many of these problems. Enumerators use smartphones or tablets to fill forms, data is stored digitally, and it can be uploaded when network coverage is available.</p>

<h2>KoBoToolbox and ODK</h2>
<p><strong>KoBoToolbox</strong> and <strong>ODK (Open Data Kit)</strong> are free, open-source tools for mobile data collection. They allow you to design forms online, deploy them to Android devices, collect data offline, and then upload the data to a server when the device connects to the internet. Both tools are widely used by NGOs and government programmes in Zambia.</p>

<h2>Setting Up a KoBoToolbox Form</h2>
<ol>
<li><strong>Create an account</strong> at <a href="https://www.kobotoolbox.org/" target="_blank" rel="noopener">kobotoolbox.org</a>. A free humanitarian account is available for NGOs.</li>
<li><strong>Build a form</strong> using the online form builder. Add questions such as text, numbers, multiple choice, dates, photos, and GPS locations.</li>
<li><strong>Test the form</strong> on your own phone before sending enumerators to the field.</li>
<li><strong>Deploy the form</strong> and download it to the KoBoCollect app on each enumerator's device.</li>
<li><strong>Collect data offline</strong>. Enumerators fill forms in the village, even without network coverage.</li>
<li><strong>Upload when connected</strong>. When the enumerator returns to an area with mobile data or Wi-Fi, the forms sync to the server.</li>
<li><strong>Download data</strong> as an Excel or CSV file for analysis.</li>
</ol>

<h2>Practical Tips for Low-Bandwidth Settings</h2>
<ul>
<li><strong>Keep forms short</strong>. Long forms drain battery life and frustrate respondents.</li>
<li><strong>Use skip logic</strong>. Show only questions that are relevant to each respondent.</li>
<li><strong>Validate answers</strong>. Set ranges so impossible numbers cannot be entered.</li>
<li><strong>Collect GPS only when needed</strong>. GPS uses battery and data.</li>
<li><strong>Train enumerators well</strong>. A good form is useless if the person using it does not understand the questions.</li>
<li><strong>Back up devices</strong>. Encourage enumerators to upload data every evening if coverage allows.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Create a free KoBoToolbox account if you have internet access.</li>
<li>Design a short five-question form for a school attendance survey.</li>
<li>Include at least one text question, one number question, one multiple-choice question, and one date question.</li>
<li>Test the form on a phone and download the data as a CSV file.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>KoBoToolbox</strong>: A free platform for building and deploying mobile data-collection forms.</li>
<li><strong>ODK</strong>: Open Data Kit, another open-source mobile data-collection platform.</li>
<li><strong>Enumerator</strong>: A person who collects data by interviewing or observing respondents.</li>
<li><strong>Offline data collection</strong>: Collecting data without an internet connection and syncing later.</li>
<li><strong>Skip logic</strong>: A form feature that shows or hides questions based on previous answers.</li>
</ul>

<h2>Summary</h2>
<p>Mobile data collection with tools like KoBoToolbox and ODK makes field data collection faster, more accurate, and more reliable in Zambia's rural and low-bandwidth settings. Good form design and enumerator training are essential for success.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.kobotoolbox.org/" target="_blank" rel="noopener">KoBoToolbox Official Website</a></li>
<li><a href="https://getodk.org/" target="_blank" rel="noopener">ODK Official Website</a></li>
<li><a href="https://www.betterevaluation.org/" target="_blank" rel="noopener">BetterEvaluation — Mobile Data Collection</a></li>
</ul>
HTML;
    }

    private function lesson3_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to design clear, unbiased survey questions, choose appropriate response formats, and write a short questionnaire suitable for use in Zambia.</p>

<h2>Good Questions Get Good Answers</h2>
<p>A survey is only as good as its questions. Confusing, leading, or double-barrelled questions produce unreliable data. A well-designed questionnaire respects respondents' time, uses simple language, and matches the questions to the indicators in the M&E plan.</p>

<h2>Types of Survey Questions</h2>
<p><strong>Closed questions</strong> give respondents a fixed set of answers. They are easy to analyse and useful for quantitative indicators. Examples include yes/no questions, multiple-choice questions, and rating scales.</p>
<p><strong>Open questions</strong> allow respondents to answer in their own words. They are useful for exploring reasons and experiences but harder to analyse. Use them sparingly in large surveys.</p>

<h2>Writing Clear Questions</h2>
<ul>
<li><strong>Use simple language</strong>. Avoid technical terms such as "indicator" or "baseline." Say "How many times did you visit the clinic this year?"</li>
<li><strong>Ask one thing at a time</strong>. "Do you like the food and the teacher?" is two questions. Split it.</li>
<li><strong>Avoid leading questions</strong>. "Don't you agree that the clinic staff are helpful?" pushes the respondent toward yes. Ask "How would you describe the clinic staff?"</li>
<li><strong>Be specific about time</strong>. "In the past seven days" is better than "recently."</li>
<li><strong>Include all possible answers</strong>. If you use multiple choice, add "Other" and "Don't know" when appropriate.</li>
<li><strong>Test the questionnaire</strong>. Pilot it with people similar to your target respondents before full rollout.</li>
</ul>

<h2>Example Questionnaire for a Rural Water Project</h2>
<ol>
<li>Which village do you live in?</li>
<li>How many people live in your household?</li>
<li>How far is your nearest working borehole? (in minutes walking)</li>
<li>In the past month, how many times has the borehole been broken?</li>
<li>Who usually collects water in your household?</li>
<li>Is the water from the borehole clean? (Yes / No / Not sure)</li>
<li>What problems do you face with water supply? (Open answer)</li>
</ol>

<h2>Translation and Cultural Adaptation</h2>
<p>In Zambia, many respondents are more comfortable in local languages such as Tonga, Nyanja, Bemba or Lozi. Translate the questionnaire carefully, then back-translate it to check accuracy. Pilot test in the local language. Some concepts may not translate directly, so work with local translators and community leaders.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write a five-question survey about a health outreach programme.</li>
<li>Include at least two closed questions and one open question.</li>
<li>Check each question for bias, double meanings, and unclear timeframes.</li>
<li>Ask a friend to answer the survey and tell you which questions were confusing.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Closed question</strong>: A question with a fixed set of answer choices.</li>
<li><strong>Open question</strong>: A question that allows respondents to answer freely.</li>
<li><strong>Leading question</strong>: A question phrased in a way that suggests the desired answer.</li>
<li><strong>Double-barrelled question</strong>: A question that asks two things at once.</li>
<li><strong>Pilot test</strong>: A small trial of a questionnaire before full data collection.</li>
</ul>

<h2>Summary</h2>
<p>Clear, well-structured survey questions are essential for reliable M&E data. Use simple language, avoid bias, choose the right question type, and always pilot your questionnaire before launching full data collection.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.betterevaluation.org/" target="_blank" rel="noopener">BetterEvaluation — Survey Design</a></li>
<li><a href="https://www.who.int/reproductivehealth/publications/monitoring/9789241548088/en/" target="_blank" rel="noopener">WHO — Survey Guidelines</a></li>
<li><a href="https://www.unicef.org/reports/what-why-and-how-monitoring-evaluation" target="_blank" rel="noopener">UNICEF — M&E Tools</a></li>
</ul>
HTML;
    }

    private function lesson3_4(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will understand why data quality matters, how to check and improve data quality, and the ethical responsibilities of everyone who collects or uses M&E data in Zambia.</p>

<h2>Why Data Quality Matters</h2>
<p>Poor data leads to poor decisions. If attendance numbers are inflated, a project may look successful when it is not. If survey responses are entered incorrectly, an evaluation may blame the wrong factor. Good M&E depends on data that is accurate, complete, timely, and relevant.</p>

<h2>The Five Dimensions of Data Quality</h2>
<ul>
<li><strong>Accuracy</strong>: Data correctly reflects what happened. A form showing 50 attendees when only 30 came is inaccurate.</li>
<li><strong>Completeness</strong>: All required information is collected. Missing village names or dates make analysis difficult.</li>
<li><strong>Timeliness</strong>: Data is available when decisions need to be made. A report submitted three months late may be useless.</li>
<li><strong>Relevance</strong>: Data relates to the indicators and decisions at hand. Collecting parent's occupations may not be relevant for a borehole repair project.</li>
<li><strong>Reliability</strong>: The same method produces consistent results over time and across enumerators.</li>
</ul>

<h2>Checking Data Quality</h2>
<p><strong>During collection:</strong> Train enumerators, use supervision visits, and spot-check completed forms. Mobile tools can add validation rules to catch impossible answers immediately.</p>
<p><strong>During entry:</strong> Double-enter paper data, check for duplicate records, and resolve inconsistencies.</p>
<p><strong>During analysis:</strong> Look for outliers, check totals, compare results with other sources, and document any data cleaning steps.</p>

<h2>Ethics in Data Collection</h2>
<p>Collecting data from people carries responsibility. Respondents have the right to know why data is being collected, how it will be used, and who will see it. They should give informed consent before participating. They should be able to refuse or withdraw without penalty.</p>
<p><strong>Do no harm.</strong> Avoid asking questions that could put respondents at risk, such as questions about illegal activities or politically sensitive topics, unless necessary and protected.</p>
<p><strong>Protect confidentiality.</strong> Remove names and identifiers from datasets where possible. Store paper forms securely and protect electronic files with passwords.</p>
<p><strong>Be honest.</strong> Never invent data, change answers to please a donor, or hide negative findings. M&E ethics require truthful reporting.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write three data-quality checks you could apply to a school attendance dataset.</li>
<li>Describe how you would obtain informed consent from a participant in a rural community.</li>
<li>List two ways to keep survey data confidential.</li>
<li>What would you do if an enumerator admitted to making up responses?</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Data quality</strong>: The degree to which data meets standards of accuracy, completeness, timeliness, relevance and reliability.</li>
<li><strong>Informed consent</strong>: Agreement to participate after understanding the purpose, risks and uses of the data.</li>
<li><strong>Confidentiality</strong>: Protecting information so that individuals cannot be identified.</li>
<li><strong>Outlier</strong>: A data point that is very different from the rest and may indicate an error.</li>
<li><strong>Do no harm</strong>: An ethical principle that requires data collection to avoid causing harm to participants.</li>
</ul>

<h2>Summary</h2>
<p>Data quality and ethics are not optional extras. They are the foundation of credible M&E. Accurate, well-protected data supports good decisions and maintains trust with communities, donors and government partners.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.betterevaluation.org/" target="_blank" rel="noopener">BetterEvaluation — Data Quality</a></li>
<li><a href="https://www.unicef.org/reports/what-why-and-how-monitoring-evaluation" target="_blank" rel="noopener">UNICEF — Ethical M&E</a></li>
<li><a href="https://www.who.int/ethics/topics/research/en/" target="_blank" rel="noopener">WHO — Research Ethics</a></li>
</ul>
HTML;
    }


    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Data Collection Tools and Methods',
            'description' => 'Assess your understanding of data collection methods, mobile tools, survey design and data quality.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which data collection method is best for exploring why people behave a certain way?',
                    'explanation' => 'Qualitative methods such as focus groups and interviews are best for exploring reasons, opinions and experiences.',
                    'options' => [
                        ['text' => 'A household survey with fixed answers', 'is_correct' => false],
                        ['text' => 'A focus group discussion', 'is_correct' => true],
                        ['text' => 'A financial audit', 'is_correct' => false],
                        ['text' => 'A GPS reading', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why are KoBoToolbox and ODK useful for data collection in rural Zambia?',
                    'explanation' => 'These tools allow enumerators to collect data offline and upload it when network coverage is available.',
                    'options' => [
                        ['text' => 'They replace the need for enumerators', 'is_correct' => false],
                        ['text' => 'They work offline and sync later', 'is_correct' => true],
                        ['text' => 'They automatically write donor reports', 'is_correct' => false],
                        ['text' => 'They only work on expensive tablets', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is an example of a leading question?',
                    'explanation' => 'A leading question suggests the answer the questioner wants to hear.',
                    'options' => [
                        ['text' => 'How many times did you visit the clinic this year?', 'is_correct' => false],
                        ['text' => 'Do you agree that the clinic staff are very helpful?', 'is_correct' => true],
                        ['text' => 'What problems do you face with water supply?', 'is_correct' => false],
                        ['text' => 'In which village do you live?', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main purpose of pilot testing a questionnaire?',
                    'explanation' => 'Pilot testing identifies confusing questions and practical problems before full data collection begins.',
                    'options' => [
                        ['text' => 'To replace the final survey', 'is_correct' => false],
                        ['text' => 'To identify confusing questions and fix them before the full rollout', 'is_correct' => true],
                        ['text' => 'To train the respondents', 'is_correct' => false],
                        ['text' => 'To calculate the final budget', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which dimension of data quality means that all required information has been collected?',
                    'explanation' => 'Completeness means that no required fields or records are missing.',
                    'options' => [
                        ['text' => 'Accuracy', 'is_correct' => false],
                        ['text' => 'Timeliness', 'is_correct' => false],
                        ['text' => 'Completeness', 'is_correct' => true],
                        ['text' => 'Reliability', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Mobile data collection tools such as KoBoToolbox require a constant internet connection to collect data in the field.',
                    'explanation' => 'KoBoToolbox and ODK work offline and sync data later when a connection is available.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Informed consent means explaining the purpose of data collection and allowing people to refuse participation.',
                    'explanation' => 'Informed consent requires that participants understand what they are agreeing to and can refuse without penalty.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for using multiple methods or sources to strengthen confidence in findings?',
                    'explanation' => 'Triangulation uses multiple methods or data sources to verify findings.',
                    'correct_answer' => 'Triangulation',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What type of question allows respondents to answer in their own words?',
                    'explanation' => 'Open questions allow respondents to answer freely rather than choosing from fixed options.',
                    'correct_answer' => 'Open question',
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
                'title' => 'Lesson 4.1: Cleaning and Managing Data on Low-Bandwidth',
                'duration_minutes' => 60,
                'content' => $this->lesson4_1(),
            ],
            [
                'title' => 'Lesson 4.2: Basic Analysis with Spreadsheets',
                'duration_minutes' => 75,
                'content' => $this->lesson4_2(),
            ],
            [
                'title' => 'Lesson 4.3: Writing M&E Reports That Get Used',
                'duration_minutes' => 60,
                'content' => $this->lesson4_3(),
            ],
            [
                'title' => 'Module 4 Quiz: Data Management, Analysis and Reporting',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your knowledge of data cleaning, spreadsheet analysis, and report writing for M&amp;E in Zambia. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson4_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to organise and clean M&E data using simple tools, identify common data errors, and manage data in environments with limited internet access.</p>

<h2>Why Data Cleaning Matters</h2>
<p>Raw data is rarely ready for analysis. Forms may contain spelling mistakes, impossible ages, blank fields, duplicate entries, or inconsistent village names. Cleaning data means finding and fixing these problems before analysis. Skipping this step leads to wrong conclusions and embarrassing reports.</p>

<h2>Setting Up a Clean Dataset</h2>
<p>Whether you use Excel, LibreOffice Calc, or a database, follow these principles:</p>
<ul>
<li><strong>One row per respondent or event.</strong> Do not put multiple people in one cell.</li>
<li><strong>One column per variable.</strong> Each question or data point gets its own column.</li>
<li><strong>Use consistent codes.</strong> Use "M" and "F" for sex, not a mix of "male," "Male," "M," and "boy."</li>
<li><strong>Keep a codebook.</strong> A codebook explains what each column means and what codes stand for.</li>
<li><strong>Never delete original data.</strong> Make a copy and clean the copy. Keep the raw file unchanged.</li>
</ul>

<h2>Common Data Errors</h2>
<table>
<tr><th>Error</th><th>Example</th><th>How to Fix</th></tr>
<tr><td>Out-of-range value</td><td>Age entered as 150</td><td>Set valid ranges and check against them</td></tr>
<tr><td>Inconsistent text</td><td>"Kalomo," "kalomo," "Kalomo District"</td><td>Standardise names using find-and-replace</td></tr>
<tr><td>Missing data</td><td>Blank cells where answers are required</td><td>Follow up with enumerator or mark as missing</td></tr>
<tr><td>Duplicates</td><td>Same respondent interviewed twice</td><td>Remove or merge duplicate rows</td></tr>
<tr><td>Wrong date format</td><td>Mix of 12/05/2026 and 2026-05-12</td><td>Apply one date format to the whole column</td></tr>
</table>

<h2>Managing Data with Low Bandwidth</h2>
<p>In Zambia, reliable internet is not guaranteed. Plan for offline work:</p>
<ul>
<li>Store datasets on local computers and external drives, not only in the cloud.</li>
<li>Use spreadsheet software that works offline, such as Excel or LibreOffice Calc.</li>
<li>Compress files before emailing them.</li>
<li>Use mobile data sparingly; upload only when necessary.</li>
<li>Back up data regularly on a password-protected external drive.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Create a simple spreadsheet with ten rows of sample survey data.</li>
<li>Introduce three common errors (for example, a duplicate, an out-of-range value, inconsistent spelling).</li>
<li>Clean the dataset using the steps in this lesson.</li>
<li>Write a short codebook explaining each column.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Data cleaning</strong>: The process of finding and correcting errors in a dataset.</li>
<li><strong>Codebook</strong>: A document that explains variable names, codes and values in a dataset.</li>
<li><strong>Outlier</strong>: A value that is unusually high or low and may indicate an error.</li>
<li><strong>Duplicate record</strong>: The same data entered more than once.</li>
<li><strong>Raw data</strong>: Data as originally collected, before cleaning or analysis.</li>
</ul>

<h2>Summary</h2>
<p>Clean, well-organised data is essential for reliable analysis. Set up datasets correctly, standardise codes, check for errors, and keep secure backups. In low-bandwidth settings, plan for offline work and protect data from loss.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.betterevaluation.org/" target="_blank" rel="noopener">BetterEvaluation — Data Management</a></li>
<li><a href="https://libreoffice.org/" target="_blank" rel="noopener">LibreOffice Calc</a></li>
<li><a href="https://support.microsoft.com/en-us/office/excel-video-training-9bc05390-e94c-46af-a5b3-d7c22f6990bb" target="_blank" rel="noopener">Microsoft — Excel Training</a></li>
</ul>
HTML;
    }

    private function lesson4_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to perform basic analysis using a spreadsheet, calculate percentages and averages, and present findings in simple tables and charts.</p>

<h2>Analysis Does Not Have to Be Complicated</h2>
<p>Most M&E analysis in Zambia uses simple arithmetic: counting, adding, averaging, and calculating percentages. You do not need expensive software. A spreadsheet such as Microsoft Excel or LibreOffice Calc is enough for most projects. The most important skill is knowing which calculation answers your M&E question.</p>

<h2>Basic Calculations</h2>
<p><strong>Count:</strong> How many? Use =COUNTA for text or =COUNT for numbers.</p>
<p><strong>Sum:</strong> What is the total? Use =SUM.</p>
<p><strong>Average:</strong> What is the typical value? Use =AVERAGE.</p>
<p><strong>Percentage:</strong> What proportion? Divide the part by the whole and multiply by 100. For example, if 120 out of 200 pupils passed, the pass rate is (120/200) × 100 = 60%.</p>

<h2>Comparing Against Targets</h2>
<p>M&E analysis is most useful when it compares actual results to planned targets. Suppose a project aimed to train 300 farmers by June and actually trained 250. The achievement rate is 250 ÷ 300 × 100 = 83%. This tells the project manager that training fell short and action is needed.</p>

<h2>Disaggregation</h2>
<p>Disaggregation means breaking totals into groups. A project might find that overall attendance is 80%, but attendance among girls is 70% while attendance among boys is 90%. This disaggregated finding shows that the project is not reaching girls equally and needs to investigate why.</p>

<h2>Simple Tables and Charts</h2>
<p>Use tables to present exact numbers. Use charts to show patterns quickly. A bar chart is good for comparing categories, such as the number of clinics reached in each district. A line chart is good for showing trends over time, such as monthly malaria cases. Avoid 3D effects and too many colours. Keep charts simple and label everything clearly.</p>

<h2>Worked Example: School Attendance Data</h2>
<table>
<tr><th>District</th><th>Enrolled</th><th>Present (average)</th><th>Attendance %</th></tr>
<tr><td>Kalomo</td><td>450</td><td>382</td><td>84.9%</td></tr>
<tr><td>Choma</td><td>320</td><td>288</td><td>90.0%</td></tr>
<tr><td>Monze</td><td>280</td><td>210</td><td>75.0%</td></tr>
</table>
<p>The table shows that Monze has the lowest attendance. The project manager should investigate the reasons and take corrective action.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Enter 20 rows of sample data into a spreadsheet.</li>
<li>Calculate the total, average, and percentage for one indicator.</li>
<li>Disaggregate the data by sex or district.</li>
<li>Create one bar chart showing the results.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Descriptive statistics</strong>: Simple summaries of data such as counts, averages and percentages.</li>
<li><strong>Achievement rate</strong>: The percentage of a target that has been reached.</li>
<li><strong>Disaggregation</strong>: Breaking data into subgroups for deeper analysis.</li>
<li><strong>Baseline</strong>: The starting value used to measure change.</li>
<li><strong>Variance</strong>: The difference between planned and actual results.</li>
</ul>

<h2>Summary</h2>
<p>Basic spreadsheet analysis answers most M&E questions. Count, sum, average, calculate percentages, compare against targets, and disaggregate to see who is being reached. Present findings in clear tables and charts that support decision-making.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://support.microsoft.com/en-us/office/excel-video-training-9bc05390-e94c-46af-a5b3-d7c22f6990bb" target="_blank" rel="noopener">Microsoft — Excel Training</a></li>
<li><a href="https://libreoffice.org/" target="_blank" rel="noopener">LibreOffice Calc</a></li>
<li><a href="https://www.betterevaluation.org/" target="_blank" rel="noopener">BetterEvaluation — Data Analysis</a></li>
</ul>
HTML;
    }

    private function lesson4_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to write clear, concise M&E reports that communicate findings effectively to different audiences, from district officials to donor representatives.</p>

<h2>Why Reports Often Fail</h2>
<p>Many M&E reports sit unread on hard drives. They fail because they are too long, too technical, full of jargon, or written for the wrong audience. A good report answers the reader's questions quickly and suggests what to do next.</p>

<h2>Know Your Audience</h2>
<p>A report for the Ministry of Health may focus on policy implications and national targets. A report for a village development committee may focus on local changes and next steps. A report for a donor may focus on value for money and whether targets were met. Before writing, ask: Who will read this? What do they need to know? And what decision will they make?</p>

<h2>Structure of a Good M&E Report</h2>
<ol>
<li><strong>Executive summary</strong>: One page with the main findings, conclusions and recommendations.</li>
<li><strong>Background</strong>: Brief description of the project, its objectives, and the reporting period.</li>
<li><strong>Methodology</strong>: How data was collected and analysed.</li>
<li><strong>Findings</strong>: What the data shows, presented with tables, charts and quotes.</li>
<li><strong>Conclusions</strong>: What the findings mean.</li>
<li><strong>Recommendations</strong>: Specific, actionable steps.</li>
<li><strong>Annexes</strong>: Detailed data tables, questionnaires, or terms of reference.</li>
</ol>

<h2>Writing Tips</h2>
<ul>
<li><strong>Be concise.</strong> Use short sentences and simple words. Avoid acronyms unless you explain them.</li>
<li><strong>Use active voice.</strong> "The project trained 200 farmers" is clearer than "200 farmers were trained by the project."</li>
<li><strong>Lead with findings.</strong> Put the most important result first, not last.</li>
<li><strong>Include both numbers and stories.</strong> Numbers show scale. Stories show meaning.</li>
<li><strong>Be honest about challenges.</strong> Donors and managers respect reports that flag problems and propose solutions.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Write a one-page M&E report for a project of your choice.</li>
<li>Include at least one table, one chart, and two recommendations.</li>
<li>Ask a colleague to read it and tell you the three main messages.</li>
<li>Revise based on feedback.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Executive summary</strong>: A brief overview of the main points of a report.</li>
<li><strong>Findings</strong>: The factual results presented in a report.</li>
<li><strong>Conclusions</strong>: Interpretations of what the findings mean.</li>
<li><strong>Recommendations</strong>: Specific actions proposed based on the conclusions.</li>
<li><strong>Annex</strong>: Supporting material placed at the end of a report.</li>
</ul>

<h2>Summary</h2>
<p>Good M&E reports turn data into decisions. Know your audience, structure the report clearly, use simple language, and provide actionable recommendations. A report that is not used is a waste of the data it contains.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.betterevaluation.org/" target="_blank" rel="noopener">BetterEvaluation — Reporting</a></li>
<li><a href="https://www.unicef.org/reports/what-why-and-how-monitoring-evaluation" target="_blank" rel="noopener">UNICEF — M&E Reporting</a></li>
<li><a href="https://www.usaid.gov/" target="_blank" rel="noopener">USAID — Performance Reporting</a></li>
</ul>
HTML;
    }


    private function module4Quiz(): array
    {
        return [
            'title' => 'Module 4 Quiz: Data Management, Analysis and Reporting',
            'description' => 'Assess your understanding of data cleaning, spreadsheet analysis and report writing.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is it important to keep a copy of the raw data before cleaning?',
                    'explanation' => 'Keeping raw data unchanged allows you to trace back any mistakes and verify cleaning decisions.',
                    'options' => [
                        ['text' => 'To save storage space', 'is_correct' => false],
                        ['text' => 'So you can always return to the original information if needed', 'is_correct' => true],
                        ['text' => 'To make the file smaller', 'is_correct' => false],
                        ['text' => 'Because donors do not like raw data', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the achievement rate if a project aimed to train 300 farmers and trained 250?',
                    'explanation' => 'Achievement rate = actual ÷ target × 100. 250 ÷ 300 × 100 = 83.3%.',
                    'options' => [
                        ['text' => '50%', 'is_correct' => false],
                        ['text' => '83%', 'is_correct' => true],
                        ['text' => '120%', 'is_correct' => false],
                        ['text' => '30%', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which spreadsheet function calculates the typical value of a set of numbers?',
                    'explanation' => 'The AVERAGE function calculates the mean of a range of numbers.',
                    'options' => [
                        ['text' => 'SUM', 'is_correct' => false],
                        ['text' => 'COUNT', 'is_correct' => false],
                        ['text' => 'AVERAGE', 'is_correct' => true],
                        ['text' => 'MAX', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What should an executive summary contain?',
                    'explanation' => 'An executive summary provides a brief overview of the main findings, conclusions and recommendations.',
                    'options' => [
                        ['text' => 'Every raw data table in the report', 'is_correct' => false],
                        ['text' => 'Main findings, conclusions and recommendations', 'is_correct' => true],
                        ['text' => 'A list of all project staff', 'is_correct' => false],
                        ['text' => 'The full terms of reference for the evaluation', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is disaggregation important in M&E analysis?',
                    'explanation' => 'Disaggregation reveals differences between groups that overall totals can hide.',
                    'options' => [
                        ['text' => 'It makes reports longer', 'is_correct' => false],
                        ['text' => 'It shows whether some groups are being left behind', 'is_correct' => true],
                        ['text' => 'It removes the need for targets', 'is_correct' => false],
                        ['text' => 'It reduces data quality', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'M&E reports should hide negative findings so donors remain happy.',
                    'explanation' => 'Honest reporting, including challenges and proposed solutions, builds trust and supports learning.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A codebook explains what each column and code means in a dataset.',
                    'explanation' => 'A codebook documents variable names, codes and values so the dataset can be understood by others.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for data as originally collected, before any cleaning or analysis?',
                    'explanation' => 'Raw data is the original, unprocessed data collected from the field.',
                    'correct_answer' => 'Raw data',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What calculation do you use to find the proportion of a group that passed an exam?',
                    'explanation' => 'Percentage divides the part by the whole and multiplies by 100.',
                    'correct_answer' => 'Percentage',
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
                'title' => 'Lesson 5.1: From Data to Decisions',
                'duration_minutes' => 60,
                'content' => $this->lesson5_1(),
            ],
            [
                'title' => 'Lesson 5.2: Stakeholder Engagement and Feedback Loops',
                'duration_minutes' => 60,
                'content' => $this->lesson5_2(),
            ],
            [
                'title' => 'Lesson 5.3: Building an M&E Plan for Your Organisation',
                'duration_minutes' => 75,
                'content' => $this->lesson5_3(),
            ],
            [
                'title' => 'Module 5 Quiz: Using M&E for Learning and Decision-Making',
                'duration_minutes' => 20,
                'type' => 'Quiz',
                'content' => '<p>Test your knowledge of using M&amp;E for decisions, stakeholder engagement, and building practical M&amp;E plans. You need 60% to pass.</p>',
            ],
        ];
    }

    private function lesson5_1(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to explain how M&E findings lead to better decisions, describe common barriers to using data, and outline steps for creating a learning culture in a Zambian organisation.</p>

<h2>M&E Is Only Valuable If It Is Used</h2>
<p>Collecting data, cleaning it, analysing it and writing reports takes time and money. That investment is wasted if the findings are ignored. The ultimate purpose of M&E is to improve decisions — whether that means changing a training schedule, reallocating a budget, scaling up a successful activity, or stopping something that is not working.</p>

<h2>From Findings to Actions</h2>
<p>Every M&E finding should lead to a question and, ideally, an action. For example:</p>
<ul>
<li><strong>Finding:</strong> Attendance at adult literacy classes drops by 40% during the farming season.</li>
<li><strong>Question:</strong> Can we adjust the class timetable to fit around farming activities?</li>
<li><strong>Action:</strong> Move evening classes to early morning during planting and harvest periods.</li>
</ul>
<p>Another example:</p>
<ul>
<li><strong>Finding:</strong> Only 30% of trained farmers are applying the new technique.</li>
<li><strong>Question:</strong> Is the technique too expensive, too labour-intensive, or poorly demonstrated?</li>
<li><strong>Action:</strong> Conduct follow-up visits and focus groups, then revise the training approach.</li>
</ul>

<h2>Barriers to Using M&E Data</h2>
<p>Many organisations struggle to use M&E data. Common barriers include:</p>
<ul>
<li><strong>Reports arrive too late</strong> to influence decisions.</li>
<li><strong>Data is not shared</strong> with the people who can act on it.</li>
<li><strong>Managers reward good news only</strong>, so staff hide problems.</li>
<li><strong>Findings are too technical</strong> for non-specialists to understand.</li>
<li><strong>There is no process</strong> for reviewing data and deciding action.</li>
</ul>

<h2>Building a Learning Culture</h2>
<p>A learning culture treats M&E as a tool for improvement, not as a punishment. Steps to build this culture include:</p>
<ul>
<li>Holding regular data review meetings.</li>
<li>Asking "What did we learn?" not just "Did we meet the target?"</li>
<li>Celebrating honest reporting, including negative findings.</li>
<li>Linking M&E findings to budgets and work plans.</li>
<li>Creating simple dashboards that show key indicators at a glance.</li>
</ul>

<h2>Try It Yourself</h2>
<ol>
<li>Think of one M&E finding from a project you know.</li>
<li>Write one question it raises and one possible action.</li>
<li>Identify one barrier that might prevent the action from happening.</li>
<li>Suggest one way to overcome that barrier.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Data use</strong>: Applying M&E findings to inform decisions and improve programmes.</li>
<li><strong>Learning culture</strong>: An organisational environment where data is used openly to improve practice.</li>
<li><strong>Feedback loop</strong>: A process where information is collected, shared, and used to make adjustments.</li>
<li><strong>Dashboard</strong>: A simple visual summary of key indicators.</li>
<li><strong>Adaptive management</strong>: Adjusting project plans based on evidence and changing conditions.</li>
</ul>

<h2>Summary</h2>
<p>M&E data only creates value when it is used. Organisations that build learning cultures, remove barriers to data use, and link findings to action will continuously improve their impact in Zambia.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.betterevaluation.org/" target="_blank" rel="noopener">BetterEvaluation — Using Evaluation Findings</a></li>
<li><a href="https://www.usaid.gov/" target="_blank" rel="noopener">USAID — Adaptive Management</a></li>
<li><a href="https://www.unicef.org/reports/what-why-and-how-monitoring-evaluation" target="_blank" rel="noopener">UNICEF — M&E for Learning</a></li>
</ul>
HTML;
    }

    private function lesson5_2(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to identify key M&E stakeholders, design simple feedback mechanisms, and explain why stakeholder engagement improves project outcomes in Zambia.</p>

<h2>Who Are the Stakeholders?</h2>
<p>Stakeholders are all the people and groups who have an interest in the project. In Zambia, M&E stakeholders often include:</p>
<ul>
<li><strong>Beneficiaries</strong>: The people the project serves, such as pupils, patients, farmers, or community members.</li>
<li><strong>Project staff</strong>: Field officers, M&E officers, programme managers.</li>
<li><strong>Government partners</strong>: District education boards, Ministry of Health staff, local councils, TEVETA.</li>
<li><strong>Donors</strong>: Organisations that provide funding and require reports.</li>
<li><strong>Community leaders</strong>: Chiefs, headmen, religious leaders, school committees.</li>
<li><strong>Civil society</strong>: NGOs, CBOs, and advocacy groups working on similar issues.</li>
</ul>

<h2>Why Stakeholder Engagement Matters</h2>
<p>When stakeholders are involved in M&E, data becomes more relevant and more trusted. Communities know their own realities better than external evaluators. Government partners can use findings to improve public services. Donors are more likely to continue funding when they see transparent engagement.</p>
<p>Without stakeholder engagement, M&E can feel like an extractive process — data is taken from communities and never returned. Engaged stakeholders become partners in learning and improvement.</p>

<h2>Feedback Mechanisms</h2>
<p>A feedback mechanism is a formal way for stakeholders to share information and receive responses. Examples include:</p>
<ul>
<li><strong>Suggestion boxes</strong> at schools, clinics, or project offices.</li>
<li><strong>Community meetings</strong> where findings are shared and discussed.</li>
<li><strong>Mobile helplines</strong> for reporting problems or asking questions.</li>
<li><strong>Quarterly review meetings</strong> with staff and partners.</li>
<li><strong>Simple feedback forms</strong> distributed after training or services.</li>
</ul>

<h2>Closing the Loop</h2>
<p>Collecting feedback is not enough. People need to see that their input leads to action. If a community reports that a borehole is broken, the project should fix it and report back. If teachers say training was too theoretical, the next training should be redesigned. This is called closing the feedback loop.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Identify five stakeholders for a project of your choice.</li>
<li>Design one feedback mechanism suitable for beneficiaries in a rural area.</li>
<li>Describe how you would close the loop after receiving feedback.</li>
<li>List two ways to share M&E findings with community members who cannot read long reports.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Stakeholder</strong>: A person or group with an interest in a project.</li>
<li><strong>Feedback mechanism</strong>: A formal channel for stakeholders to share information and receive responses.</li>
<li><strong>Closing the loop</strong>: Acting on feedback and communicating the action back to those who provided it.</li>
<li><strong>Participatory M&E</strong>: An approach that involves stakeholders, especially beneficiaries, in designing and using M&E.</li>
<li><strong>Community feedback</strong>: Information provided by community members about project performance.</li>
</ul>

<h2>Summary</h2>
<p>Stakeholder engagement makes M&E more relevant, trusted, and useful. Good feedback mechanisms collect input from those affected by the project and ensure that input leads to visible action.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.betterevaluation.org/" target="_blank" rel="noopener">BetterEvaluation — Participatory Evaluation</a></li>
<li><a href="https://www.unicef.org/reports/what-why-and-how-monitoring-evaluation" target="_blank" rel="noopener">UNICEF — Community Engagement</a></li>
<li><a href="https://www.gsdrc.org/" target="_blank" rel="noopener">GSDRC — Stakeholder Engagement</a></li>
</ul>
HTML;
    }

    private function lesson5_3(): string
    {
        return <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson, you will be able to write a practical M&E plan for a small Zambian organisation, including objectives, indicators, data sources, responsibilities and a reporting schedule.</p>

<h2>What Is an M&E Plan?</h2>
<p>An M&E plan is a working document that describes how a project will monitor its activities and evaluate its results. It turns good intentions into a clear system. A complete M&E plan answers these questions:</p>
<ul>
<li>What are we trying to achieve?</li>
<li>How will we measure success?</li>
<li>Who will collect the data?</li>
<li>When and how often?</li>
<li>How will we report and use the information?</li>
</ul>

<h2>Components of an M&E Plan</h2>
<ol>
<li><strong>Project description and objectives</strong>: What the project does and what it hopes to achieve.</li>
<li><strong>Results framework or logic model</strong>: The chain from inputs to impact.</li>
<li><strong>Indicators</strong>: SMART indicators for each level.</li>
<li><strong>Data collection methods</strong>: Tools and approaches for each indicator.</li>
<li><strong>Data management plan</strong>: How data will be stored, cleaned and analysed.</li>
<li><strong>Roles and responsibilities</strong>: Who does what.</li>
<li><strong>Schedule</strong>: When data will be collected, analysed and reported.</li>
<li><strong>Budget</strong>: Resources needed for M&E activities.</li>
<li><strong>Learning and use plan</strong>: How findings will feed into decisions.</li>
</ol>

<h2>Example: M&E Plan for a Girls' Retention Programme</h2>
<table>
<tr><th>Indicator</th><th>Data Source</th><th>Frequency</th><th>Responsible</th></tr>
<tr><td>% of target girls enrolled</td><td>School registers</td><td>Termly</td><td>School M&E focal person</td></tr>
<tr><td>% of enrolled girls attending 80%+ of school days</td><td>Attendance records</td><td>Monthly</td><td>Class teacher</td></tr>
<tr><td>% of girls who feel safe at school</td><td>Student survey</td><td>Baseline, midline, endline</td><td>Project officer</td></tr>
<tr><td>Number of parent meetings held</td><td>Meeting minutes</td><td>Monthly</td><td>Community mobiliser</td></tr>
</table>

<h2>Keeping It Practical</h2>
<p>Many M&E plans fail because they are too ambitious. Start small. Choose a few key indicators. Use tools that staff can manage. Build the system gradually. A simple plan that is actually used is better than a perfect plan that sits on a shelf.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a small project or activity in your organisation or community.</li>
<li>Write a one-page M&E plan with at least three indicators.</li>
<li>For each indicator, state the data source, frequency, and responsible person.</li>
<li>Include one paragraph on how findings will be used.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>M&E plan</strong>: A document describing how a project will monitor and evaluate its performance.</li>
<li><strong>Responsibility matrix</strong>: A table showing who is responsible for each M&E task.</li>
<li><strong>Reporting schedule</strong>: A timetable for producing and sharing M&E reports.</li>
<li><strong>M&E budget</strong>: The financial resources allocated to monitoring and evaluation.</li>
<li><strong>Learning agenda</strong>: A set of priority questions a project wants to answer through M&E.</li>
</ul>

<h2>Summary</h2>
<p>A practical M&E plan links objectives to indicators, data sources, responsibilities and schedules. It keeps the team focused on learning and improvement, and it ensures that M&E resources are used wisely.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.betterevaluation.org/" target="_blank" rel="noopener">BetterEvaluation — M&E Plans</a></li>
<li><a href="https://www.unicef.org/reports/what-why-and-how-monitoring-evaluation" target="_blank" rel="noopener">UNICEF — Developing an M&E Plan</a></li>
<li><a href="https://www.usaid.gov/" target="_blank" rel="noopener">USAID — Performance Management Plan Guidance</a></li>
</ul>
HTML;
    }


    private function module5Quiz(): array
    {
        return [
            'title' => 'Module 5 Quiz: Using M&E for Learning and Decision-Making',
            'description' => 'Assess your understanding of data use, stakeholder engagement and M&E planning.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main purpose of M&E in a learning organisation?',
                    'explanation' => 'The main purpose of M&E is to inform decisions and improve programmes over time.',
                    'options' => [
                        ['text' => 'To produce long reports for donors', 'is_correct' => false],
                        ['text' => 'To collect data for its own sake', 'is_correct' => false],
                        ['text' => 'To inform decisions and improve programmes', 'is_correct' => true],
                        ['text' => 'To prove that staff are working hard', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is an example of closing the feedback loop?',
                    'explanation' => 'Closing the feedback loop means acting on feedback and telling stakeholders what was done.',
                    'options' => [
                        ['text' => 'Collecting complaints and ignoring them', 'is_correct' => false],
                        ['text' => 'Fixing a reported problem and informing the community', 'is_correct' => true],
                        ['text' => 'Writing a report that no one reads', 'is_correct' => false],
                        ['text' => 'Holding a meeting without recording decisions', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Who should be involved in M&E stakeholder engagement?',
                    'explanation' => 'Stakeholders include beneficiaries, staff, government, donors, community leaders and civil society.',
                    'options' => [
                        ['text' => 'Only the project manager', 'is_correct' => false],
                        ['text' => 'Only the donor', 'is_correct' => false],
                        ['text' => 'Beneficiaries, staff, government, donors and community leaders', 'is_correct' => true],
                        ['text' => 'Only external evaluators', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is adaptive management?',
                    'explanation' => 'Adaptive management means adjusting project plans based on evidence and changing conditions.',
                    'options' => [
                        ['text' => 'Sticking to the original plan no matter what', 'is_correct' => false],
                        ['text' => 'Adjusting plans based on evidence and changing conditions', 'is_correct' => true],
                        ['text' => 'Replacing all staff every year', 'is_correct' => false],
                        ['text' => 'Avoiding any changes to the budget', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which component should be included in an M&E plan?',
                    'explanation' => 'An M&E plan should include indicators, data sources, responsibilities, schedules and a learning plan.',
                    'options' => [
                        ['text' => 'A list of staff birthdays', 'is_correct' => false],
                        ['text' => 'Indicators, data sources, responsibilities and schedule', 'is_correct' => true],
                        ['text' => 'The project\'s social media passwords', 'is_correct' => false],
                        ['text' => 'A copy of the organisation\'s logo', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A practical M&E plan should start with many indicators to be comprehensive.',
                    'explanation' => 'It is better to start with a few key indicators that staff can actually collect and use.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Stakeholder engagement makes M&E findings more relevant and trusted.',
                    'explanation' => 'Involving stakeholders helps ensure that M&E focuses on what matters and that findings are accepted.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the term for adjusting project plans based on evidence and changing conditions?',
                    'explanation' => 'Adaptive management uses evidence to adjust plans and improve results.',
                    'correct_answer' => 'Adaptive management',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is a simple visual summary of key indicators called?',
                    'explanation' => 'A dashboard presents key indicators in a simple visual format for quick review.',
                    'correct_answer' => 'Dashboard',
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
        $this->command->info('=== Monitoring & Evaluation Content Seed Summary ===');
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
        $this->command->info('Certificate in Monitoring & Evaluation content seeded successfully.');
    }
}
