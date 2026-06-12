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

class DigitalContentCreationContentSeeder extends Seeder
{
    private int $courseId;

    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Digital & Content Creation')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Digital & Content Creation" not found. Aborting.');
            return;
        }

        $this->courseId = $course->id;

        if (Module::where('course_id', $this->courseId)->exists()) {
            $this->command->info('Certificate in Digital & Content Creation already has modules. Skipping content seed.');
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
                    'time_limit_minutes' => 20,
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
                'title' => 'Module 1: Planning and Capturing Content on Your Phone',
                'description' => 'Learn how to plan content your audience wants, shoot professional photos and videos on a phone, and edit them using free apps.',
            ],
            [
                'title' => 'Module 2: Writing, Posting, and Growing on Social Media',
                'description' => 'Master captions and hooks, build a strategy for TikTok, Facebook, and WhatsApp Status, and grow a loyal audience.',
            ],
            [
                'title' => 'Module 3: From Views to Money and Creator Responsibility',
                'description' => 'Turn content views into income through brand deals and product sales, understand creator law and etiquette, and build a sustainable career.',
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
                'title' => '1.1 Finding Content Ideas Your Zambian Audience Actually Wants',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to identify what your audience cares about, brainstorm content ideas that match their daily lives, and plan a simple content calendar so that you never run out of things to post.</p>

<h2>Why Content Planning Matters</h2>
<p>Many people start creating content by opening their phone, taking a random photo or video, and posting it immediately. This often leads to low views, few likes, and frustration. The creators who grow are the ones who plan. They know what their audience wants, they prepare ideas in advance, and they post with purpose. Whether you are a market vendor in Kalomo wanting to show your fresh tomatoes, a hairdresser showcasing braids, or a student sharing study tips, planning helps you stand out in a crowded feed.</p>

<h2>Understanding Your Audience</h2>
<p>Your audience is the group of people you want to reach. In Zambia, most social media users are on Facebook, TikTok, and WhatsApp. They use phones with limited data bundles, so they prefer short, useful, or entertaining content. They relate to real situations: load-shedding, high transport costs, finding affordable school uniforms, cooking on a budget, or juggling a side business with a full-time job.</p>
<p>Before you create anything, ask yourself these questions:</p>
<ul>
<li>Who am I trying to reach? (School leavers, parents, business owners, farmers?)</li>
<li>What problems do they face every day?</li>
<li>What would make them stop scrolling and pay attention?</li>
<li>What language do they use? (English, Nyanja, Bemba, Tonga, or a mix?)</li>
</ul>

<h2>Types of Content That Work Locally</h2>
<p>Here are content formats that perform well with Zambian audiences:</p>
<ul>
<li><strong>How-to tutorials</strong> — "How to style a chitenge skirt in three ways" or "How to check your NAPSA contributions on your phone."</li>
<li><strong>Behind-the-scenes</strong> — Show how you prepare your market stall at 5 a.m., how you braid hair, or how you bake buns for sale.</li>
<li><strong>Before and after</strong> — Transformations sell. A dirty plot turned into a vegetable garden, a plain room decorated on a budget, or a student desk organised for exams.</li>
<li><strong>Day-in-the-life</strong> — Follow a nurse, teacher, shopkeeper, or farmer through their day. People love real stories.</li>
<li><strong>Tips and hacks</strong> — "Three ways to make your Airtel data last longer" or "How to keep tomatoes fresh for one week without a fridge."</li>
</ul>

<h2>Worked Example: Planning a Week of Content for a Chicken-Rearing Business</h2>
<p>Mrs Tembo runs a small chicken-rearing business in Kalomo and wants to attract customers who buy live chickens and eggs. She plans one week of content:</p>
<table>
<tr><th>Day</th><th>Content Idea</th><th>Format</th></tr>
<tr><td>Monday</td><td>Show the chicks arriving at her home</td><td>30-second video</td></tr>
<tr><td>Tuesday</td><td>Tip: "How I keep my chickens healthy without expensive medicine"</td><td>Photo + caption</td></tr>
<tr><td>Wednesday</td><td>Behind-the-scenes: Feeding time</td><td>Reel/TikTok</td></tr>
<tr><td>Thursday</td><td>Customer review: A neighbour talks about the quality of her eggs</td><td>Video testimonial</td></tr>
<tr><td>Friday</td><td>Price list for chickens and eggs with contact details</td><td>Canva graphic</td></tr>
<tr><td>Saturday</td><td>Day-in-the-life: From morning feeding to evening counting of sales</td><td>Photo carousel</td></tr>
<tr><td>Sunday</td><td>Rest or repost the most popular post from the week</td><td>Reshare</td></tr>
</table>
<p>This plan mixes education, trust-building, and sales. Mrs Tembo never runs out of ideas because each day has a clear purpose.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a business or interest you have. It could be selling clothes, cooking, farming, tutoring, or even your college course.</li>
<li>Write down three problems your audience faces related to that topic.</li>
<li>Brainstorm five content ideas that solve those problems or entertain your audience.</li>
<li>For each idea, decide whether it will be a photo, video, graphic, or text post.</li>
<li>Draw a simple table with five days and place one idea on each day. This is your first content plan.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Audience</strong> — the group of people you want to reach and influence with your content.</li>
<li><strong>Content calendar</strong> — a simple schedule that tells you what to post and when.</li>
<li><strong>Behind-the-scenes</strong> — content that shows the real work and process behind a finished product or service.</li>
<li><strong>Testimonial</strong> — a video or written review from a happy customer that builds trust.</li>
<li><strong>Carousel</strong> — a social media post with multiple images that viewers swipe through.</li>
</ul>

<h2>Summary</h2>
<p>Great content starts with understanding your audience and planning ahead. Instead of posting randomly, think about what your audience needs, choose formats they enjoy, and organise your ideas into a simple calendar. A well-planned week of content will always outperform a random post, because it shows purpose, consistency, and care.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool/">Canva Design School — Free Content Creation Tutorials</a></li>
<li><a href="https://grow.google/">Grow with Google — Digital Marketing Basics</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.2 Shooting Professional Photos on Your Phone',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to use natural light, compose shots using the rule of thirds, adjust basic phone camera settings, and take photos that look clean and professional enough to sell products or attract followers.</p>

<h2>Light Is Everything</h2>
<p>The difference between a blurry, dull photo and a sharp, appealing one is usually light. Professional cameras have large sensors that handle low light well, but phone cameras are small and need more help. The best light is free: the sun. In Zambia, we have strong sunlight for most of the year, which is a huge advantage.</p>
<p>Here are practical rules for using light:</p>
<ul>
<li><strong>Shoot during the "golden hours"</strong> — the first hour after sunrise and the last hour before sunset. The light is soft, warm, and flattering.</li>
<li><strong>Avoid harsh midday sun</strong> — between 11 a.m. and 2 p.m., the sun is directly overhead and creates strong shadows under the eyes and nose. If you must shoot then, find shade under a tree, a veranda, or an umbrella.</li>
<li><strong>Use window light indoors</strong> — place your subject next to a window, not with their back to it. The window becomes a soft, natural light source.</li>
<li><strong>Never use the phone flash</strong> — it creates red eyes, harsh shadows, and washed-out colours. Use another phone's torchlight from the side instead if it is dark.</li>
</ul>

<h2>Framing and Composition</h2>
<p>Composition means how you arrange the things in your photo. A well-composed photo guides the viewer's eye and makes the subject clear.</p>
<p>The <strong>rule of thirds</strong> is the easiest technique to learn. Imagine your screen divided into nine equal squares by two horizontal and two vertical lines. Place your main subject where two of those lines cross, rather than dead in the centre. Most phones have a grid option in the camera settings that shows these lines. Turn it on.</p>
<p>Other composition tips:</p>
<ul>
<li><strong>Fill the frame</strong> — get close to your subject so it takes up most of the photo. A photo of one mango where you can see the texture is better than a photo of a whole tree with tiny fruit.</li>
<li><strong>Use leading lines</strong> — roads, fences, table edges, or rows of maize can guide the eye toward your subject.</li>
<li><strong>Keep the background clean</strong> — move rubbish, clothes, or distracting objects out of the shot. A plain wall or a blurred background makes your subject stand out.</li>
<li><strong>Shoot from different angles</strong> — crouch low for drama, shoot from above for a flat-lay of products, or shoot at eye level for portraits.</li>
</ul>

<h2>Phone Camera Settings You Should Know</h2>
<p>Most Android phones have a "Pro" or "Manual" mode in the camera app. Learn these three settings and you will already be ahead of most people:</p>
<ul>
<li><strong>Focus</strong> — tap on the screen where you want the camera to focus. A yellow or white box appears. Hold it for a second to lock focus so it does not shift if you move slightly.</li>
<li><strong>Exposure</strong> — after tapping to focus, swipe up or down to brighten or darken the image. If your subject is backlit (the sun is behind them), increase exposure so their face is visible.</li>
<li><strong>HDR</strong> — High Dynamic Range combines multiple shots to balance bright and dark areas. Turn it on for landscape shots or scenes with mixed light.</li>
</ul>

<h2>Worked Example: Taking Product Photos for a Market Stall</h2>
<p>Mr Banda sells handmade wooden spoons at Soweto Market. He wants photos for his WhatsApp Status and Facebook page. Here is how he shoots:</p>
<ol>
<li>He waits until 4 p.m. when the sun is lower and the light is golden.</li>
<li>He clears a small table and places a clean white chitenge cloth as the background.</li>
<li>He arranges five spoons in a fan shape with a small bowl of groundnuts nearby for colour contrast.</li>
<li>He taps the screen on the middle spoon to set focus, then lowers the exposure slightly so the wood grain shows.</li>
<li>He takes five photos from slightly different angles: directly above, 45 degrees, and two close-ups of the carved handle.</li>
<li>He reviews the photos on his phone screen and picks the sharpest, best-lit one for editing.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Open your phone camera and turn on the grid lines in the settings.</li>
<li>Find an object in your home or college — a book, a shoe, a plate of food, or a plant.</li>
<li>Take one photo with the object in the centre, then take another using the rule of thirds. Compare them.</li>
<li>Take the same object near a window, then take it under artificial light only. Compare the results.</li>
<li>Take three photos of the same object from different angles: above, below, and eye level.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Golden hour</strong> — the period shortly after sunrise or before sunset when light is soft and warm.</li>
<li><strong>Rule of thirds</strong> — a composition technique that places the subject at the intersection of imaginary grid lines for a balanced photo.</li>
<li><strong>Exposure</strong> — how bright or dark a photo is; controlled by light and camera settings.</li>
<li><strong>HDR</strong> — High Dynamic Range; a camera mode that balances very bright and very dark areas in one photo.</li>
<li><strong>Flat-lay</strong> — a photo taken from directly above, showing objects arranged on a flat surface.</li>
</ul>

<h2>Summary</h2>
<p>Professional-looking photos do not require an expensive camera. They require good light, thoughtful composition, and a few basic phone settings. Shoot in golden light, use the rule of thirds, tap to focus, and keep your background clean. Practise with everyday objects around you, and soon your photos will look as good as those from creators with far more expensive equipment.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool/">Canva Design School — Photography Basics</a></li>
<li><a href="https://support.google.com/pixelphone/answer/6327185">Google Pixel Help — Camera Tips (applies to most Android phones)</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.3 Filming Videos That Look and Sound Good',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to hold your phone steady while filming, frame video shots correctly, capture clear audio, and use simple lighting techniques so that your videos look polished even when filmed on an ordinary smartphone.</p>

<h2>Stability: The Foundation of Good Video</h2>
<p>Shaky video is the fastest way to lose viewers. When a video wobbles, it looks unprofessional and can even make people feel dizzy. The good news is that you can fix shakiness without buying expensive gear.</p>
<p>Here are ways to keep your phone steady:</p>
<ul>
<li><strong>Use both hands</strong> — hold the phone horizontally (landscape) with both hands, elbows tucked into your sides. This creates a natural tripod with your body.</li>
<li><strong>Lean against a wall or table</strong> — any solid surface absorbs movement and keeps the shot stable.</li>
<li><strong>Rest the phone on a surface</strong> — a stack of books, a window sill, or a table can hold the phone at the right angle. Use a small object like a rubber band or a wallet to prop it up.</li>
<li><strong>Walk like a ninja</strong> — if you must move while filming, bend your knees slightly and step softly. Do not run or make sudden turns.</li>
<li><strong>Turn on video stabilisation</strong> — many Android phones have a stabilisation setting in the camera app. Look for "Steady shot" or "Video stabilisation" and enable it.</li>
</ul>

<h2>Framing and Orientation</h2>
<p>There are two ways to hold your phone: <strong>vertical (portrait)</strong> and <strong>horizontal (landscape)</strong>. Each has its place.</p>
<ul>
<li><strong>Portrait</strong> is best for TikTok, Instagram Reels, and WhatsApp Status. These platforms are designed for phone screens held upright.</li>
<li><strong>Landscape</strong> is best for YouTube, Facebook Watch, and any video you want to look cinematic or be viewed on a TV or computer.</li>
</ul>
<p>Whatever orientation you choose, keep it consistent. Do not switch between portrait and landscape in the same video unless you are doing it on purpose for effect. Leave a small amount of space above the subject's head — not too much, not too little. This is called <strong>headroom</strong>.</p>

<h2>Audio Matters More Than You Think</h2>
<p>Viewers will tolerate slightly poor video quality, but they will not tolerate bad sound. If they cannot hear you clearly, they will scroll away within three seconds. Phone microphones pick up a lot of background noise: barking dogs, generators, wind, traffic, and market crowds.</p>
<p>Here is how to capture better audio:</p>
<ul>
<li><strong>Film in a quiet place</strong> — close windows, turn off fans and radios, and ask people nearby to pause their conversations.</li>
<li><strong>Get close to the subject</strong> — the closer the phone is to the person speaking, the louder and clearer their voice will be compared to background noise.</li>
<li><strong>Use a pair of earphones with a microphone</strong> — even cheap wired earphones have a small microphone that records clearer audio than the phone's built-in mic, especially if you clip the mic near the speaker's mouth.</li>
<li><strong>Shield the microphone from wind</strong> — if filming outdoors, cup your hand around the mic or film from the sheltered side of a building.</li>
<li><strong>Record voice separately</strong> — if the location is noisy, film the video and record the explanation later in a quiet room using a voice recorder app. You can combine them in CapCut.</li>
</ul>

<h2>Lighting for Video</h2>
<p>The same light rules from photography apply to video, with one extra tip: <strong>keep the light source in front of your subject, not behind</strong>. If the window or sun is behind the person, their face will be dark and hard to see. Turn them around so the light falls on their face. If you are filming indoors during load-shedding, use a rechargeable LED lamp or even a phone torch bounced off a white wall to soften the light.</p>

<h2>Worked Example: Filming a Cooking Tutorial</h2>
<p>Ms Zulu wants to film a two-minute video showing how she makes chikanda. She follows these steps:</p>
<ol>
<li>She chooses 8 a.m. when her kitchen gets soft morning light through the window.</li>
<li>She clears the counter, moves dirty dishes out of frame, and places the ingredients neatly.</li>
<li>She props her phone on a stack of cookbooks in landscape orientation, tapping to focus on the chopping board.</li>
<li>She tests the audio by recording ten seconds and playing it back. She realises the blender noise is too loud, so she films the blending separately and narrates over it later.</li>
<li>She films each step: ingredients, mixing, cooking, and the final plated dish. Each clip is ten to twenty seconds.</li>
<li>She films an intro clip looking at the camera and saying, "Today I am making chikanda. Let us start." This personal touch builds connection.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Find a quiet spot indoors or outdoors with good natural light.</li>
<li>Film a 30-second video of yourself introducing your favourite hobby or business. Hold the phone horizontally and at eye level.</li>
<li>Play the video back. Is the sound clear? Is the background distracting?</li>
<li>Film the same introduction again, but this time with the phone propped on a stable surface.</li>
<li>If you have earphones with a mic, record a third version using the earphone microphone. Compare all three versions and note which sounds best.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Portrait</strong> — vertical video orientation, used for TikTok, Reels, and WhatsApp Status.</li>
<li><strong>Landscape</strong> — horizontal video orientation, used for YouTube and Facebook Watch.</li>
<li><strong>Headroom</strong> — the space between the top of a person's head and the top of the video frame.</li>
<li><strong>Voice-over</strong> — narration recorded separately and added to a video during editing.</li>
<li><strong>Stabilisation</strong> — a camera feature that reduces shakiness in video footage.</li>
</ul>

<h2>Summary</h2>
<p>Good video is not about expensive equipment. It is about stability, clear sound, proper lighting, and thoughtful framing. Hold your phone steady, get close for better audio, keep the light on your subject's face, and choose the right orientation for the platform you are targeting. These habits will make your videos look professional even if you are filming on a phone that costs less than K2,000.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool/">Canva Design School — Video Basics</a></li>
<li><a href="https://support.google.com/pixelphone/answer/6327185">Google Pixel Help — Video Recording Tips</a></li>
<li><a href="https://grow.google/">Grow with Google — Digital Content Fundamentals</a></li>
</ul>
HTML,
            ],
            [
                'title' => '1.4 Editing with Free Apps: CapCut and Canva Basics',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to trim and arrange video clips in CapCut, add text and music to videos, create graphics and thumbnails in Canva, and export finished content ready for posting on social media.</p>

<h2>Why Editing Matters</h2>
<p>Raw footage is rarely good enough to post. Editing removes mistakes, adds personality, and turns scattered clips into a story that holds attention. A one-minute video with jump cuts, text labels, and background music feels far more professional than a single, unbroken clip. The good news is that you do not need a computer or expensive software. Two free apps — CapCut for video and Canva for graphics — give you everything you need.</p>

<h2>CapCut: Video Editing on Your Phone</h2>
<p>CapCut is a free video editing app owned by ByteDance, the same company that owns TikTok. It is available on Android and works well even on phones with limited storage. It is the most popular editing app for short-form video in Africa.</p>
<p>Here are the essential editing steps in CapCut:</p>
<ol>
<li><strong>Import clips</strong> — open CapCut, start a new project, and select the video clips from your phone gallery.</li>
<li><strong>Trim and split</strong> — drag the edges of a clip to trim off unwanted parts. Use the "Split" tool to cut a long clip into smaller pieces. Remove pauses, mistakes, and boring sections.</li>
<li><strong>Arrange clips</strong> — drag clips into the order you want. A cooking video might show ingredients first, then mixing, then cooking, then the final dish.</li>
<li><strong>Add text</strong> — tap "Text," type your caption or label, and choose a font. Place text at the top or bottom so it does not cover the main subject. Keep text large enough to read on a small phone screen.</li>
<li><strong>Add music</strong> — CapCut has a free music library. Choose a track that matches the mood of your video. Keep the music volume lower than any speaking, or remove the music during talking sections.</li>
<li><strong>Add transitions</strong> — between clips, add a simple transition such as a fade or a slide. Do not overuse flashy transitions; they distract from your content.</li>
<li><strong>Adjust speed</strong> — you can speed up boring sections or slow down important moments. A common trick is to speed up the mixing process and slow down the moment the finished dish is revealed.</li>
<li><strong>Export</strong> — choose 1080p resolution and 30 frames per second for most social media. Save the video to your gallery.</li>
</ol>

<h2>Canva: Graphics and Thumbnails</h2>
<p>Canva is a free design tool that runs in your web browser and as a phone app. It is perfect for creating social media graphics, price lists, event posters, and video thumbnails. A thumbnail is the still image people see before they click on your video. A good thumbnail increases views dramatically.</p>
<p>How to create a thumbnail in Canva:</p>
<ol>
<li>Open Canva and search for "YouTube Thumbnail" or "TikTok Cover."</li>
<li>Choose a template or start with a blank canvas.</li>
<li>Upload a photo from your phone or choose one from Canva's free image library.</li>
<li>Add a bold headline in large, readable font. Use white text with a black outline so it is visible on any background.</li>
<li>Keep the design simple. One photo, one headline, and maybe one small icon. Too much text looks crowded on a phone screen.</li>
<li>Download the image as a PNG file for the best quality.</li>
</ol>

<h2>Worked Example: Editing a Market Stall Promo Video</h2>
<p>Mr Banda filmed clips of his wooden spoons at the market. He edits them in CapCut:</p>
<ol>
<li>He imports five clips: the spoons on display, a close-up of the carving, a customer picking one up, Mr Banda smiling at the camera, and a shot of his phone number written on a chalkboard.</li>
<li>He trims the first clip to three seconds, removing the wobble at the start.</li>
<li>He splits the close-up clip and keeps only the sharpest two seconds.</li>
<li>He adds text to the opening clip: "Handmade Wooden Spoons — Kalomo."</li>
<li>He adds gentle background music from CapCut's "Acoustic" category, lowering the volume to 20 percent.</li>
<li>He adds a final text overlay with his WhatsApp number: "Order on WhatsApp: 097X XXX XXX."</li>
<li>He exports the video in 1080p and posts it to his WhatsApp Status and Facebook page.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Download CapCut from the Google Play Store if you do not already have it.</li>
<li>Import two or three short video clips from your phone gallery into a new CapCut project.</li>
<li>Trim each clip to remove any shaky or boring parts.</li>
<li>Add text to at least one clip. Write a simple label or your name.</li>
<li>Add a free music track from CapCut and adjust the volume so it does not drown out any speech.</li>
<li>Export the final video and save it to your gallery.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Trim</strong> — to cut off the beginning or end of a video clip to remove unwanted footage.</li>
<li><strong>Split</strong> — to divide one clip into two pieces so you can rearrange or delete a section from the middle.</li>
<li><strong>Transition</strong> — a visual effect between two clips, such as a fade or slide.</li>
<li><strong>Thumbnail</strong> — the still image that represents a video before someone clicks to watch it.</li>
<li><strong>Resolution</strong> — the number of pixels in a video; 1080p is high definition and looks sharp on most screens.</li>
</ul>

<h2>Summary</h2>
<p>Editing transforms raw footage into content people want to watch. CapCut gives you powerful video editing tools for free on your phone, while Canva helps you create graphics and thumbnails that attract clicks. Learn to trim, split, add text, and export in CapCut, and practise making simple but bold thumbnails in Canva. These two apps alone are enough to build a professional-looking content presence.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool/">Canva Design School — Free Tutorials</a></li>
<li><a href="https://www.capcut.com/">CapCut Official Website</a></li>
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
                'title' => '2.1 Writing Captions and Hooks That Stop the Scroll',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to write opening lines that grab attention in the first two seconds, structure captions that keep readers interested, include clear calls-to-action, and adapt your writing style for Zambian social media audiences.</p>

<h2>The Hook: Your First Two Seconds</h2>
<p>On social media, you have about two seconds to convince someone to stop scrolling and read your post. Those two seconds are determined by your hook: the very first line of your caption or the first words spoken in your video. If the hook is weak, the rest of your content is wasted because no one will see it.</p>
<p>A strong hook does one of these things:</p>
<ul>
<li><strong>Asks a relatable question</strong> — "Do you spend more than K50 on transport to Lusaka every week?"</li>
<li><strong>Makes a surprising statement</strong> — "I made K3,000 last month selling tomatoes on WhatsApp Status."</li>
<li><strong>Promises a benefit</strong> — "Here is how to keep your phone battery alive during a full day of load-shedding."</li>
<li><strong>Creates curiosity</strong> — "The reason your chapatis are not soft has nothing to do with flour."</li>
<li><strong>Speaks directly to the reader</strong> — "If you are a parent in Kalomo struggling with school fees, read this."</li>
</ul>

<h2>Structuring a Caption</h2>
<p>A good caption is not one long paragraph. It is structured to guide the reader from interest to action. Here is a simple formula:</p>
<ol>
<li><strong>Hook</strong> — the first line that stops the scroll.</li>
<li><strong>Story or value</strong> — two to four sentences that deliver what the hook promised. Be specific. Use real numbers, real names, and real situations.</li>
<li><strong>Key point or list</strong> — a bullet list or numbered steps that make the content easy to scan.</li>
<li><strong>Call-to-action</strong> — tell the reader exactly what to do next. "Comment with your favourite market in Kalomo," "Save this for later," or "DM me for prices."</li>
</ol>

<h2>Writing for Zambian Audiences</h2>
<p>Zambian social media is multilingual and informal. People switch between English, Nyanja, Bemba, and Tonga freely. Your captions should feel natural, not like a school essay. Use short sentences. Use local references. If your audience is mostly in Southern Province, mention places and events they know. If you are targeting young people, use the slang and expressions they use. If you are targeting parents, be respectful and practical.</p>
<p>Avoid these common mistakes:</p>
<ul>
<li>Writing long paragraphs with no breaks. Phone screens are small. Break text into one or two-sentence chunks.</li>
<li>Using complicated words to sound smart. Simple words reach more people.</li>
<li>Forgetting to proofread. A caption full of spelling mistakes looks unprofessional.</li>
<li>Being too salesy. Build trust first, then sell. A caption that is 100 percent advertising will be ignored.</li>
</ul>

<h2>Calls-to-Action</h2>
<p>A call-to-action, or CTA, is the instruction you give your reader at the end of a post. Without a CTA, people will read and scroll away. With a CTA, they engage, share, or buy. Good CTAs for Zambian creators include:</p>
<ul>
<li>"Double-tap if you agree."</li>
<li>"Tag a friend who needs to see this."</li>
<li>"Save this post for the next time load-shedding hits."</li>
<li>"Comment 'YES' if you want me to share the recipe."</li>
<li>"DM me or WhatsApp 097X XXX XXX to place your order."</li>
</ul>

<h2>Worked Example: Writing Captions for a Salon Business</h2>
<p>Mary runs a hair salon in Kalomo. She wants to promote her braiding service. Here are three captions she could use:</p>
<p><strong>Weak caption:</strong></p>
<blockquote>
<p>We do braids. Come to our salon. Good prices.</p>
</blockquote>
<p><strong>Strong caption:</strong></p>
<blockquote>
<p>Three hours. One head. Zero stress. 🙌</p>
<p>That is what Mrs Mulenga said after her braiding session last Saturday. She walked in at 9 a.m. with dry, tangled hair and walked out at noon with neat, long braids that will last six weeks.</p>
<p>Here is what you get when you book with us:</p>
<ul>
<li>A comfortable chair and a friendly chat</li>
<li>Quality braids that do not loosen after one week</li>
<li>Prices starting from K80 — no hidden charges</li>
</ul>
<p>We are behind the main bus station in Kalomo. DM us or WhatsApp 097X XXX XXX to book your slot. Walk-ins welcome too!</p>
<p>#KalomoBraids #ZambianHair #BraidsKalomo</p>
</blockquote>
<p>The strong caption has a hook, a story, a list, a CTA, and hashtags. It builds trust before asking for money.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a product or service you could sell or promote. It could be anything from chapatis to tutoring.</li>
<li>Write five different hook lines for the same product. Use different hook types: question, surprise, benefit, curiosity, and direct address.</li>
<li>Pick your strongest hook and write a full caption using the four-part structure: hook, story, list, CTA.</li>
<li>Read your caption aloud. Does it sound natural? Would you stop scrolling to read it?</li>
<li>Ask a friend or classmate to read it and tell you which part made them most interested.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Hook</strong> — the first line of a caption or video that is designed to grab attention immediately.</li>
<li><strong>Call-to-action (CTA)</strong> — a clear instruction telling the reader what to do next after reading your post.</li>
<li><strong>Caption</strong> — the text that accompanies a photo or video on social media.</li>
<li><strong>Hashtag</strong> — a word or phrase preceded by the # symbol that helps people find your content.</li>
<li><strong>Engagement</strong> — any interaction with your post, including likes, comments, shares, and saves.</li>
</ul>

<h2>Summary</h2>
<p>Great captions start with a hook, deliver value through story and structure, and end with a clear call-to-action. Write the way Zambians speak: simply, warmly, and directly. Test different hooks, keep your paragraphs short, and always tell your reader what to do next. The best captions do not just describe; they connect, teach, and invite action.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool/">Canva Design School — Social Media Writing</a></li>
<li><a href="https://grow.google/">Grow with Google — Digital Marketing Fundamentals</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.2 TikTok, Facebook, and WhatsApp Status Strategy for Zambia',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to choose the right platform for your content, create a basic posting strategy for TikTok, Facebook, and WhatsApp Status, understand how each platform's audience behaves, and adapt your content format to match what works best on each.</p>

<h2>Choosing the Right Platform</h2>
<p>Not every platform suits every creator. In Zambia, TikTok is growing fast among young people, Facebook remains the most popular platform overall, and WhatsApp Status is the most intimate way to reach people who already know you. Your strategy should start with one main platform and expand later. A common mistake is trying to be everywhere at once and doing a poor job on all of them.</p>
<p>Here is a quick guide:</p>
<ul>
<li><strong>TikTok</strong> — best for short, entertaining videos under 60 seconds. Great for dancers, comedians, cooks, fashion creators, and anyone who can teach something quickly. The algorithm can show your content to strangers, which means fast growth is possible.</li>
<li><strong>Facebook</strong> — best for longer videos, photo carousels, event announcements, and community building. Facebook Groups are especially powerful for niche topics like farming, parenting, or church activities. The audience is older and more diverse than TikTok.</li>
<li><strong>WhatsApp Status</strong> — best for quick updates, price lists, daily offers, and personal connections. Your Status is only visible to people who have your phone number, so it works best for building trust with existing customers rather than finding new ones.</li>
</ul>

<h2>TikTok Strategy for Zambian Creators</h2>
<p>TikTok's algorithm rewards consistency and watch time. If people watch your video all the way to the end, TikTok shows it to more people. Here is how to succeed:</p>
<ul>
<li><strong>Post one to three times per day</strong> — frequency matters more on TikTok than on any other platform.</li>
<li><strong>Hook in the first second</strong> — start with action, a question, or a bold statement. No slow introductions.</li>
<li><strong>Use trending sounds</strong> — tap the discover page to find sounds that are popular in Zambia or Africa. Using them increases your chances of being seen.</li>
<li><strong>Add captions on-screen</strong> — many people watch without sound. Text helps them understand your video.</li>
<li><strong>Use relevant hashtags</strong> — mix broad ones like #Zambia and #FYP with niche ones like #ZambianFood or #KalomoCreator.</li>
<li><strong>Reply to comments with videos</strong> — TikTok lets you film a video response to a comment. This creates more content and shows your audience you care.</li>
</ul>

<h2>Facebook Strategy for Zambian Creators</h2>
<p>Facebook rewards content that sparks conversation. A post with many comments will be shown to more people than a post with only likes.</p>
<ul>
<li><strong>Post two to four times per week</strong> — quality beats quantity on Facebook.</li>
<li><strong>Ask questions</strong> — "What is the best market in Kalomo for fresh vegetables?" or "How do you save money on school uniforms?" Questions get comments.</li>
<li><strong>Use photo carousels</strong> — Facebook shows carousels to more people than single images. Use them for tutorials, before-and-afters, or product showcases.</li>
<li><strong>Go live</strong> — Facebook Live gets priority in the news feed. Announce your live session in advance, go live at a consistent time, and answer questions in real time.</li>
<li><strong>Join and contribute to groups</strong> — do not just promote yourself. Answer questions, share advice, and build your reputation. When people trust you, they will follow your page.</li>
</ul>

<h2>WhatsApp Status Strategy</h2>
<p>WhatsApp Status is 24-hour stories visible to your contacts. It is perfect for daily business updates because it feels personal.</p>
<ul>
<li><strong>Post three to five Status updates per day</strong> — mix photos, short videos, and text quotes.</li>
<li><strong>Show your products</strong> — a photo of fresh tomatoes with a price tag, a video of a hairstyle you just finished, or a screenshot of a happy customer's message.</li>
<li><strong>Add your contact details</strong> — every few Status updates, remind people how to reach you.</li>
<li><strong>Use the Status as a mini-catalogue</strong> — save your best Status posts as highlights or pin them to your profile if your WhatsApp version supports it.</li>
<li><strong>Do not spam</strong> — ten Status updates in a row will annoy people. Space them throughout the day.</li>
</ul>

<h2>Worked Example: A Week-Long Strategy for a Tailoring Business</h2>
<p>Mrs Zulu runs a tailoring business. Here is how she uses all three platforms in one week:</p>
<table>
<tr><th>Day</th><th>TikTok</th><th>Facebook</th><th>WhatsApp Status</th></tr>
<tr><td>Monday</td><td>15-sec video: "Before and after of a torn chitenge dress I fixed"</td><td>Photo carousel: five styles of chitenge skirts she makes</td><td>Photo: "Open today until 5 p.m. — alterations K30"</td></tr>
<tr><td>Tuesday</td><td>30-sec tutorial: "How to measure your waist correctly"</td><td>—</td><td>Video: time-lapse of sewing a zip</td></tr>
<tr><td>Wednesday</td><td>—</td><td>Question post: "What is your favourite fabric for church clothes?"</td><td>Photo: new fabric arrivals with prices</td></tr>
<tr><td>Thursday</td><td>15-sec video: "The mistake everyone makes when ironing a chitenge"</td><td>—</td><td>Testimonial screenshot from a happy customer</td></tr>
<tr><td>Friday</td><td>—</td><td>Facebook Live: "Ask me anything about tailoring"</td><td>Photo: "Weekend special — 10% off alterations"</td></tr>
<tr><td>Saturday</td><td>Dance video wearing one of her designs</td><td>Photo: family wearing matching outfits she made</td><td>Photo: "Thank you to everyone who visited this week"</td></tr>
<tr><td>Sunday</td><td>—</td><td>—</td><td>Rest day or inspirational quote</td></tr>
</table>

<h2>Try It Yourself</h2>
<ol>
<li>Choose one product or service you could promote.</li>
<li>Decide which platform is your main focus: TikTok, Facebook, or WhatsApp Status. Explain why in two sentences.</li>
<li>Write three post ideas for that platform, each with a different format: one video, one photo, and one text-based post.</li>
<li>For each post idea, write the hook line and the call-to-action.</li>
<li>If you have time, create one of the three posts on your phone and show it to a classmate for feedback.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Algorithm</strong> — the set of rules a platform uses to decide which posts to show to which users.</li>
<li><strong>News feed</strong> — the main stream of posts you see when you open Facebook or TikTok.</li>
<li><strong>Carousel</strong> — a post with multiple images or videos that viewers swipe through.</li>
<li><strong>Engagement rate</strong> — the percentage of your followers who interact with a post through likes, comments, or shares.</li>
<li><strong>Hashtag strategy</strong> — the planned use of hashtags to help new people discover your content.</li>
</ul>

<h2>Summary</h2>
<p>Each social platform has its own culture and rewards different behaviours. TikTok favours frequent, entertaining short videos. Facebook rewards conversation and community. WhatsApp Status excels at personal, daily business updates. Choose your main platform based on where your audience spends time, learn its rules, and create content that fits its format. A creator who masters one platform will grow faster than one who posts randomly across three.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool/">Canva Design School — Social Media Strategy</a></li>
<li><a href="https://grow.google/">Grow with Google — Social Media Marketing</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '2.3 Posting Consistency and Building Your Audience',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to create a simple posting schedule, understand why consistency matters more than viral hits, engage with your audience in ways that build loyalty, and track basic growth signals so you know what is working.</p>

<h2>Consistency Beats Viral Hits</h2>
<p>Many new creators dream of one post that goes viral and changes everything. While virality can bring a burst of followers, it is not a strategy. Most viral creators are forgotten within weeks because they have no follow-up content. The creators who build real audiences are the ones who show up regularly, even when their last post got only five views.</p>
<p>Consistency does three things:</p>
<ul>
<li><strong>It trains the algorithm</strong> — platforms like TikTok and Facebook push content from accounts that post regularly. If you post every day for a month, the platform learns to expect your content and shows it to more people.</li>
<li><strong>It builds audience trust</strong> — when followers know you post every Tuesday and Friday, they look forward to your content. When you disappear for three weeks, they forget about you.</li>
<li><strong>It improves your skills</strong> — every post is practice. A creator who makes 100 posts will be far better than one who made 10 perfect posts and stopped.</li>
</ul>

<h2>Creating a Content Calendar</h2>
<p>A content calendar is simply a plan that tells you what to post and when. It removes the daily stress of wondering what to create. You do not need fancy software. A notebook, a phone note, or a simple table in Google Sheets works perfectly.</p>
<p>Here is how to build one:</p>
<ol>
<li><strong>Choose your posting days</strong> — start with what you can actually manage. Three posts per week is better than seven posts one week and zero the next.</li>
<li><strong>Assign themes to days</strong> — for example, Monday is "Tip Day," Wednesday is "Behind-the-Scenes Day," and Friday is "Product or Service Day." Themes make brainstorming easier.</li>
<li><strong>Write ideas in advance</strong> — every Sunday, plan the next week's posts. Write the hook and the main points. Film or photograph when you have time, and schedule posts if the platform allows it.</li>
<li><strong>Leave room for spontaneity</strong> — not everything needs to be planned. If something interesting happens, post it. Your calendar is a guide, not a prison.</li>
</ol>

<h2>Engaging With Your Audience</h2>
<p>Growth is not just about posting. It is about conversation. When someone comments on your post, reply. When someone shares your video, thank them. When someone asks a question in your DMs, answer promptly. These small interactions turn casual viewers into loyal followers.</p>
<p>Practical engagement habits:</p>
<ul>
<li><strong>Reply to every comment in the first hour</strong> — the algorithm sees early engagement as a sign of quality. Plus, it makes the commenter feel valued.</li>
<li><strong>Ask follow-up questions</strong> — if someone comments "Nice work," reply with "Thank you. Which part did you like best?" This keeps the conversation going.</li>
<li><strong>Share user content</strong> — if a customer posts a photo wearing your product or using your service, repost it with credit. This builds community and provides social proof.</li>
<li><strong>Go live regularly</strong> — live sessions let people see the real you. Answer questions, show your workspace, or teach something simple. Even 15 minutes is enough.</li>
</ul>

<h2>Tracking What Works</h2>
<p>You do not need expensive analytics tools. The apps themselves give you enough data. Here is what to watch:</p>
<ul>
<li><strong>Views</strong> — how many people saw your post. High views mean your hook and thumbnail worked.</li>
<li><strong>Likes</strong> — a basic signal that people enjoyed the content.</li>
<li><strong>Comments</strong> — the best sign of engagement. A post with 50 comments is more valuable than one with 500 likes and no comments.</li>
<li><strong>Saves or shares</strong> — these mean people found your content so useful that they want to return to it or show someone else.</li>
<li><strong>Follower growth</strong> — check weekly, not daily. Look for trends rather than obsessing over single posts.</li>
</ul>

<h2>Worked Example: A 30-Day Plan for a New Creator</h2>
<p>Mr Mutale wants to become a farming content creator. He plans his first month:</p>
<table>
<tr><th>Week</th><th>Monday</th><th>Wednesday</th><th>Friday</th></tr>
<tr><td>Week 1</td><td>Tip: How to prepare tomato seedlings</td><td>Behind-the-scenes: Morning routine on the farm</td><td>Product: Selling tomatoes — prices and delivery</td></tr>
<tr><td>Week 2</td><td>Tip: Natural pest control using chilli water</td><td>Q&A: Answering a follower question about fertiliser</td><td>Testimonial: A buyer talks about the quality</td></tr>
<tr><td>Week 3</td><td>Tip: How to water during load-shedding</td><td>Behind-the-scenes: Packing orders for delivery</td><td>Product: New cabbage harvest available</td></tr>
<tr><td>Week 4</td><td>Tip: Storing vegetables without refrigeration</td><td>Live session: 15-min farm tour</td><td>Monthly recap: What I learned this month</td></tr>
</table>
<p>This plan gives him twelve posts in thirty days, a mix of education, trust-building, and sales, without overwhelming him.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose a topic or business you want to create content about.</li>
<li>Decide how many days per week you can realistically post. Be honest.</li>
<li>Create a two-week content calendar with specific post ideas for each day. Assign a theme to each day.</li>
<li>For each post, write the hook line and the call-to-action.</li>
<li>Show your calendar to a classmate. Ask them which post they would be most likely to engage with.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Content calendar</strong> — a schedule that plans what content to post and when.</li>
<li><strong>Viral</strong> — when a post spreads rapidly and is seen by far more people than usual.</li>
<li><strong>Engagement</strong> — any interaction with your content, including likes, comments, shares, and saves.</li>
<li><strong>Social proof</strong> — evidence that other people trust and value your product or service, such as testimonials and reviews.</li>
<li><strong>Analytics</strong> — data about how your content performs, such as views, likes, and follower growth.</li>
</ul>

<h2>Summary</h2>
<p>Consistency is the secret weapon of successful creators. A regular posting schedule trains the algorithm, builds trust with your audience, and forces you to improve with every post. Create a simple content calendar, engage with your followers personally, and track basic metrics to see what works. Remember that one viral post is nice, but one hundred consistent posts will build a career.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool/">Canva Design School — Content Planning</a></li>
<li><a href="https://grow.google/">Grow with Google — Social Media Marketing</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module3Lessons(): array
    {
        return [
            [
                'title' => '3.1 Turning Views into Income: Brand Deals, Selling Products, and Services',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain how content creators earn money in Zambia, identify three ways to monetise your audience, negotiate basic brand deals, and set up simple systems for selling products and services through social media.</p>

<h2>How Creators Earn Money in Zambia</h2>
<p>There is a myth that you need millions of followers to make money from content. In reality, a Zambian creator with 2,000 engaged followers in a specific niche can earn more than a creator with 50,000 random followers who never comment or buy. The key is not the size of your audience; it is the trust you have built with them.</p>
<p>Here are the main ways Zambian creators earn income:</p>
<ul>
<li><strong>Brand deals and sponsorships</strong> — a business pays you to mention or show their product in your content.</li>
<li><strong>Selling your own products</strong> — physical goods such as clothes, food, or crafts, or digital goods such as e-books or design templates.</li>
<li><strong>Selling services</strong> — using your content to attract clients for hair styling, tutoring, photography, event planning, or consulting.</li>
<li><strong>Affiliate marketing</strong> — promoting someone else's product and earning a commission for every sale made through your link.</li>
<li><strong>Tips and donations</strong> — followers send money via Airtel Money or MTN MoMo to support your work.</li>
</ul>

<h2>Brand Deals and Sponsorships</h2>
<p>A brand deal is when a company pays you to create content featuring their product. In Zambia, local brands such as bakeries, clothing shops, phone accessory sellers, and restaurants are increasingly looking for creators with loyal audiences.</p>
<p>To attract brand deals:</p>
<ul>
<li><strong>Build a clear niche</strong> — brands want to know exactly who your audience is. A food creator attracts restaurant deals. A fashion creator attracts clothing brand deals.</li>
<li><strong>Show engagement</strong> — brands care more about comments and saves than raw follower numbers. Screenshot your best-performing posts as proof.</li>
<li><strong>Create a simple media kit</strong> — a one-page document or Canva graphic that shows your follower count, engagement rate, audience location, and examples of your best content. You do not need a professional designer.</li>
<li><strong>Reach out directly</strong> — send a polite WhatsApp or email to local businesses explaining who you are, what you create, and how a partnership could help them. Do not wait for them to find you.</li>
</ul>
<p>When negotiating, know your worth. A creator with 5,000 engaged followers might charge K200 to K500 for a single post, depending on the effort required. Always agree on deliverables, deadlines, and payment method before you create anything. Get at least half the payment upfront.</p>

<h2>Selling Your Own Products</h2>
<p>If you already make something, content is the best marketing tool. A baker who shows the process of decorating a cake will sell more cakes than one who only posts prices. A tailor who films the cutting and sewing process will get more orders than one who only posts finished clothes.</p>
<p>Tips for selling through content:</p>
<ul>
<li><strong>Show the making process</strong> — people pay more when they see the effort and skill involved.</li>
<li><strong>Use WhatsApp for orders</strong> — create a separate business WhatsApp number, save customer details, and send payment confirmations via Airtel Money or MTN MoMo.</li>
<li><strong>Post prices clearly</strong> — do not make people guess. A clear price list in your WhatsApp Status or on a Canva graphic builds trust.</li>
<li><strong>Offer limited-time deals</strong> — "This week only, chapatis are K3 each instead of K4" creates urgency without being dishonest.</li>
</ul>

<h2>Selling Services</h2>
<p>Content creators often become known as experts in their field. A woman who posts hair tutorials becomes the go-to person for braiding. A man who shares farming tips becomes the person people call for advice. Use your content to position yourself as an expert, then offer paid services.</p>
<p>How to sell services through content:</p>
<ul>
<li><strong>Teach for free, charge for execution</strong> — show people how to do something, but offer to do it for them at a fee.</li>
<li><strong>Show results</strong> — before-and-after photos, testimonials, and case studies prove you can deliver.</li>
<li><strong>Make booking easy</strong> — put your WhatsApp number in every bio and every post. Respond to messages within a few hours.</li>
<li><strong>Ask for referrals</strong> — happy customers are your best marketers. Offer a small discount for every new client they bring.</li>
</ul>

<h2>Worked Example: Monetising a Food Content Account</h2>
<p>Grace runs a TikTok account where she shares quick Zambian recipes. She has 4,000 followers and an average of 200 likes per video. Here is how she monetises:</p>
<ol>
<li><strong>Brand deal</strong> — a local cooking oil brand pays her K300 to use their oil in three recipe videos. She tags them and mentions the brand naturally in the video.</li>
<li><strong>Product sales</strong> — she starts selling pre-made spice mixes for pilau and chikanda. She films the packaging process and posts prices on WhatsApp Status. She sells ten mixes per week at K30 each.</li>
<li><strong>Services</strong> — she offers small-group cooking classes at her home in Kalomo for K100 per person. She posts class dates on Facebook and fills four spots each month through DMs.</li>
</ol>
<p>Her total monthly income from content is roughly K1,500 to K2,000, which supplements her husband's salary and covers school fees.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Choose one skill, product, or service you could sell. It can be as simple as braiding hair or as complex as tutoring mathematics.</li>
<li>Write down three types of content you could create to attract buyers for that product or service.</li>
<li>Decide which monetisation method fits you best right now: brand deals, product sales, or services. Explain why in two sentences.</li>
<li>Create a simple price list on paper or in Canva for your product or service.</li>
<li>Write a short pitch message you could send to a local business if you wanted a brand deal. Keep it polite, brief, and specific.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Brand deal</strong> — a paid agreement where a business pays a creator to feature their product in content.</li>
<li><strong>Engagement rate</strong> — the percentage of followers who interact with your posts through likes, comments, and shares.</li>
<li><strong>Media kit</strong> — a simple document that shows your audience size, engagement, and content examples to potential partners.</li>
<li><strong>Affiliate marketing</strong> — earning a commission by promoting another company's products through a special link.</li>
<li><strong>Call-to-action</strong> — a clear instruction in your content that tells viewers what to do next, such as "DM me to order."</li>
</ul>

<h2>Summary</h2>
<p>Content creation becomes sustainable when it earns income. Brand deals, product sales, and services are the three main paths for Zambian creators. Start by building trust with a clear niche, then choose the monetisation method that fits your skills and audience. Negotiate fairly, get paid upfront when possible, and always deliver on your promises. A creator who treats their content like a business will eventually earn like one.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool/">Canva Design School — Branding Basics</a></li>
<li><a href="https://grow.google/">Grow with Google — Online Business Fundamentals</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.2 Creator Etiquette, Copyright, and the Law in Zambia',
                'duration_minutes' => 75,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain what copyright means for creators, avoid common legal mistakes when using other people's content, understand defamation and privacy laws, follow advertising disclosure rules, and build a reputation as an honest and ethical creator.</p>

<h2>What Is Copyright?</h2>
<p>Copyright is the legal right that protects original creative work. When someone creates a photo, video, song, piece of writing, or design, they automatically own the copyright to it. This means no one else can use, copy, or share that work without permission. In Zambia, copyright is governed by the Copyright and Performance Rights Act. You do not need to register copyright for it to exist; it exists the moment the work is created.</p>
<p>For content creators, copyright matters in two directions:</p>
<ul>
<li><strong>Protecting your own work</strong> — your photos and videos belong to you. If someone steals them and uses them without credit, you have the right to ask them to stop or even take legal action.</li>
<li><strong>Respecting other people's work</strong> — you cannot use someone else's photo, music, or video just because you found it on the internet. You need permission or a licence.</li>
</ul>

<h2>Music and Video: What You Can and Cannot Use</h2>
<p>One of the most common mistakes new creators make is using popular music in their videos without permission. Just because a song is on YouTube or TikTok does not mean you have the right to use it in your own content.</p>
<p>Safe options for music:</p>
<ul>
<li>Use the free music libraries inside CapCut, TikTok, and Facebook. These tracks are pre-licensed for use on those platforms.</li>
<li>Search for "royalty-free music" on websites that offer free tracks for creators. Always read the licence to confirm commercial use is allowed.</li>
<li>Use original music from Zambian artists who have given you written permission.</li>
</ul>
<p>Unsafe options:</p>
<ul>
<li>Using the latest song by a famous international or Zambian artist without permission.</li>
<li>Downloading music from free MP3 sites and adding it to your video.</li>
<li>Using a TV show clip or movie scene in your content.</li>
</ul>
<p>If you use copyrighted music, platforms may mute your video, remove it, or suspend your account. It is not worth the risk.</p>

<h2>Photos and Graphics</h2>
<p>The same rules apply to images. You cannot take a photo from Google Images and use it in your Canva design or Facebook post without checking the licence. Instead:</p>
<ul>
<li>Use your own photos. This is always the safest and most authentic option.</li>
<li>Use Canva's free image library, which contains photos licensed for commercial use.</li>
<li>Use stock photo sites that offer free images for commercial use.</li>
<li>If you must use someone else's photo, ask for permission in writing and give clear credit.</li>
</ul>

<h2>Defamation and False Information</h2>
<p>Defamation means saying or publishing something false that damages someone's reputation. In Zambia, both spoken defamation (slander) and written defamation (libel) can lead to legal action. As a creator, you must be careful when reviewing businesses, criticising individuals, or sharing accusations.</p>
<p>Rules to follow:</p>
<ul>
<li>Do not make false claims about a person or business to get views.</li>
<li>If you review a product or service, be honest but fair. Stick to facts you can prove.</li>
<li>Do not share screenshots of private conversations without permission.</li>
<li>If you made a mistake and posted false information, correct it publicly and apologise.</li>
</ul>

<h2>Privacy and Consent</h2>
<p>When you film in public or include other people in your content, you should respect their privacy. If someone clearly asks not to be filmed, stop. If you film children, get permission from their parents or guardians. If you film inside a private business, ask the owner first. These habits protect you from complaints and show that you are a responsible creator.</p>

<h2>Advertising Disclosure</h2>
<p>When a brand pays you to promote a product, you must tell your audience. Hiding a sponsorship is dishonest and, in many countries, illegal. Even in Zambia, transparency builds trust. Use clear language such as:</p>
<ul>
<li>"This post is sponsored by [Brand Name]."</li>
<li>"Ad — I was paid to share this product with you."</li>
<li>"Paid partnership with [Brand Name]."</li>
</ul>
<p>Never claim you love a product if you have not actually used it. Your reputation is worth more than one payment.</p>

<h2>Worked Example: Checking if Content Is Legal to Post</h2>
<p>Mr Zulu wants to make a video reviewing a new restaurant in Kalomo. Before posting, he checks:</p>
<ol>
<li><strong>Music</strong> — he uses a free track from CapCut's library instead of a popular song.</li>
<li><strong>Photos</strong> — he only uses photos he took himself during his visit.</li>
<li><strong>Honesty</strong> — he mentions one dish he did not enjoy, but he explains why rather than insulting the restaurant. His review is fair.</li>
<li><strong>Consent</strong> — he filmed the owner talking about the menu, but he asked permission first and showed the owner the clip before posting.</li>
<li><strong>No payment</strong> — he paid for his own meal, so this is an honest review. If the restaurant had paid him, he would add "Paid partnership" to the caption.</li>
</ol>

<h2>Try It Yourself</h2>
<ol>
<li>Go through your phone gallery and pick one photo you did not take yourself. Ask yourself: do I have permission to post this? If not, delete it or replace it with your own photo.</li>
<li>Open CapCut and find the free music library. Pick one track and note its name so you know it is safe to use.</li>
<li>Write a one-sentence advertising disclosure you could use if a brand paid you to promote their product.</li>
<li>Think of a time you were unhappy with a product or service. Write a fair, factual review that explains what went wrong without being insulting or making unproven claims.</li>
<li>Ask three people for their permission before including them in a video you plan to post online.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Copyright</strong> — the legal right that protects original creative work from being used without permission.</li>
<li><strong>Royalty-free</strong> — music or images that can be used without paying ongoing fees, though they may require an initial licence.</li>
<li><strong>Defamation</strong> — making a false statement that harms someone's reputation.</li>
<li><strong>Consent</strong> — permission given by a person to be filmed, photographed, or mentioned in your content.</li>
<li><strong>Disclosure</strong> — clearly telling your audience when you have been paid to promote a product or service.</li>
</ul>

<h2>Summary</h2>
<p>Being a successful creator means more than making good content. It means being honest, respectful, and law-abiding. Understand copyright and only use music and images you have the right to use. Avoid defamation by sticking to facts. Respect people's privacy by asking for consent. Disclose brand deals clearly. A creator with integrity will last longer and earn more trust than one who cuts corners. Your reputation is your most valuable asset.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool/">Canva Design School — Legal Basics for Creators</a></li>
<li><a href="https://www.w3schools.com/">W3Schools — General Learning Resources</a></li>
<li><a href="https://www.khanacademy.org/computing/computers-and-internet">Khan Academy — Computers and the Internet</a></li>
</ul>
HTML,
            ],
            [
                'title' => '3.3 Building a Sustainable Content Creation Career',
                'duration_minutes' => 60,
                'content' => <<<'HTML'
<h2>What You Will Learn</h2>
<p>By the end of this lesson you will be able to explain why diversifying your income matters, describe how to build a personal brand that lasts, set realistic short-term and long-term goals, and identify free resources for continuing to learn and grow as a creator in Zambia.</p>

<h2>Diversifying Your Income</h2>
<p>Relying on one source of income is risky for any creator. A brand might stop paying. A platform might change its rules. A product might go out of season. Sustainable creators build multiple income streams so that if one slows down, the others keep them going.</p>
<p>Here are income streams a Zambian creator can build over time:</p>
<ul>
<li><strong>Platform monetisation</strong> — if TikTok or YouTube introduces direct payment for views in Zambia, eligible creators can earn from ads.</li>
<li><strong>Brand deals</strong> — ongoing relationships with three to five local businesses provide steady income.</li>
<li><strong>Product sales</strong> — physical or digital products that can be sold repeatedly without much extra work.</li>
<li><strong>Services and consulting</strong> — using your expertise to help others for a fee.</li>
<li><strong>Teaching and workshops</strong> — running paid classes online or in person.</li>
<li><strong>Donations and memberships</strong> — loyal followers who send tips via mobile money or pay for exclusive content.</li>
</ul>
<p>The goal is to have at least three income streams within your first two years as a creator. This protects you from the ups and downs of any single source.</p>

<h2>Building a Personal Brand</h2>
<p>Your personal brand is what people think of when they hear your name or see your content. It is not a logo or a colour scheme. It is the combination of your values, your style, your expertise, and the way you treat people. A strong personal brand makes you memorable and trustworthy.</p>
<p>How to build a personal brand:</p>
<ul>
<li><strong>Be consistent in style</strong> — use the same colour palette, fonts, and tone across all platforms. If your content is warm and funny on TikTok, do not be cold and formal on Facebook.</li>
<li><strong>Be known for one thing</strong> — the most successful creators are associated with a specific topic. "She is the chapati lady." "He is the guy who knows everything about tomatoes." Pick your niche and own it.</li>
<li><strong>Show your face</strong> — people connect with people, not faceless accounts. Even if you are shy, show your face occasionally. It builds trust.</li>
<li><strong>Be reliable</strong> — if you say you will post every Tuesday, post every Tuesday. If you promise to reply to DMs, reply. Reliability is part of your brand.</li>
<li><strong>Be honest</strong> — admit mistakes, share struggles, and celebrate wins. Authenticity attracts loyal followers.</li>
</ul>

<h2>Setting Realistic Goals</h2>
<p>Dreaming big is good, but unrealistic goals lead to burnout. Instead of saying "I want one million followers," set goals you can control:</p>
<ul>
<li>"I will post three times per week for the next three months."</li>
<li>"I will reply to every comment within two hours."</li>
<li>"I will reach out to five local businesses for brand deals this month."</li>
<li>"I will learn one new editing technique every week."</li>
<li>"I will track my analytics every Sunday and adjust my strategy."</li>
</ul>
<p>These goals are specific, measurable, and within your power. Achieve them one by one, and the big numbers will follow naturally.</p>

<h2>Continuous Learning</h2>
<p>Social media changes fast. New apps appear, old ones change their rules, and audience tastes shift. Creators who stop learning become irrelevant. Commit to learning something new every month. Watch tutorials, follow successful creators, read articles, and experiment with new formats. The best creators are also the best students.</p>

<h2>Worked Example: A Six-Month Growth Plan</h2>
<p>Grace, the food content creator from Module 3, writes a six-month plan:</p>
<table>
<tr><th>Month</th><th>Focus</th><th>Action</th></tr>
<tr><td>1</td><td>Consistency</td><td>Post four times per week on TikTok and Facebook</td></tr>
<tr><td>2</td><td>Engagement</td><td>Reply to every comment; reach out to five local food brands</td></tr>
<tr><td>3</td><td>Products</td><td>Launch a spice mix; post the making process</td></tr>
<tr><td>4</td><td>Teaching</td><td>Host one paid cooking class; film a behind-the-scenes video</td></tr>
<tr><td>5</td><td>Collaboration</td><td>Collaborate with two other local creators for cross-promotion</td></tr>
<tr><td>6</td><td>Review</td><td>Analyse what worked; set goals for the next six months</td></tr>
</table>
<p>This plan is realistic, diversified, and focused on actions Grace can control.</p>

<h2>Try It Yourself</h2>
<ol>
<li>Write down three income streams you could realistically build in the next year.</li>
<li>Describe your personal brand in three sentences. What do you want people to think when they see your content?</li>
<li>Set three specific, achievable goals for the next month. Make sure they are things you can control.</li>
<li>Find one successful Zambian or African creator in your niche. Watch five of their posts and write down three techniques they use that you could try.</li>
<li>Create a simple six-month plan using a table or list. Include one focus area and one action for each month.</li>
</ol>

<h2>Key Terms</h2>
<ul>
<li><strong>Personal brand</strong> — the reputation and identity you build through your content, values, and behaviour.</li>
<li><strong>Income diversification</strong> — earning money from multiple sources rather than depending on one.</li>
<li><strong>Burnout</strong> — physical and mental exhaustion caused by working too hard without rest.</li>
<li><strong>Cross-promotion</strong> — collaborating with another creator to introduce each other to your respective audiences.</li>
<li><strong>Authenticity</strong> — being genuine and true to yourself in your content and interactions.</li>
</ul>

<h2>Summary</h2>
<p>A sustainable content creation career is built on multiple income streams, a strong personal brand, realistic goals, and a commitment to continuous learning. Do not chase viral moments. Chase consistency, trust, and improvement. Diversify your income so you are protected from change. Build a brand that people remember and respect. Set goals you can control, and keep learning as the platforms evolve. The creators who succeed are the ones who treat content creation as a marathon, not a sprint.</p>

<h2>Free Resources</h2>
<ul>
<li><a href="https://www.canva.com/designschool/">Canva Design School — Branding and Marketing</a></li>
<li><a href="https://grow.google/">Grow with Google — Free Digital Skills Training</a></li>
<li><a href="https://www.khanacademy.org/">Khan Academy — Free Courses in Many Subjects</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Learn">MDN Web Docs — Learn Web Development</a></li>
</ul>
HTML,
            ],
        ];
    }

    private function module1Quiz(): array
    {
        return [
            'title' => 'Module 1 Quiz: Planning and Capturing Content',
            'description' => 'Test your knowledge of content planning, phone photography, video filming, and basic editing with CapCut and Canva.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main purpose of a content calendar?',
                    'explanation' => 'A content calendar helps you plan what to post and when, removing the daily stress of deciding what to create.',
                    'options' => [
                        ['text' => 'To randomly post whenever you feel like it', 'is_correct' => false],
                        ['text' => 'To plan what to post and when', 'is_correct' => true],
                        ['text' => 'To delete old posts automatically', 'is_correct' => false],
                        ['text' => 'To hack into other accounts', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which time of day provides the best natural light for phone photography?',
                    'explanation' => 'The golden hour, shortly after sunrise or before sunset, gives soft, warm light that flatters subjects.',
                    'options' => [
                        ['text' => 'Midday, when the sun is directly overhead', 'is_correct' => false],
                        ['text' => 'Late at night with the phone flash', 'is_correct' => false],
                        ['text' => 'The golden hour, shortly after sunrise or before sunset', 'is_correct' => true],
                        ['text' => 'During a thunderstorm', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does the rule of thirds help you do in photography?',
                    'explanation' => 'The rule of thirds is a composition technique that places the subject at grid intersections for a balanced photo.',
                    'options' => [
                        ['text' => 'Calculate the price of a photo', 'is_correct' => false],
                        ['text' => 'Divide a video into three equal parts', 'is_correct' => false],
                        ['text' => 'Create balanced and visually appealing compositions', 'is_correct' => true],
                        ['text' => 'Set the camera timer to three seconds', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which phone orientation is best for TikTok and WhatsApp Status videos?',
                    'explanation' => 'Portrait (vertical) orientation is designed for TikTok, Instagram Reels, and WhatsApp Status.',
                    'options' => [
                        ['text' => 'Landscape (horizontal)', 'is_correct' => false],
                        ['text' => 'Portrait (vertical)', 'is_correct' => true],
                        ['text' => 'Upside down', 'is_correct' => false],
                        ['text' => 'Diagonal', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'In CapCut, what does the "Split" tool do?',
                    'explanation' => 'The Split tool divides one video clip into two pieces so you can remove or rearrange sections.',
                    'options' => [
                        ['text' => 'It merges two clips into one', 'is_correct' => false],
                        ['text' => 'It divides one clip into two pieces', 'is_correct' => true],
                        ['text' => 'It changes the video colour', 'is_correct' => false],
                        ['text' => 'It uploads the video to the internet', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is a thumbnail in video content?',
                    'explanation' => 'A thumbnail is the still image viewers see before clicking on a video; a good one increases views.',
                    'options' => [
                        ['text' => 'A small version of your phone', 'is_correct' => false],
                        ['text' => 'The still image people see before clicking a video', 'is_correct' => true],
                        ['text' => 'A type of music track', 'is_correct' => false],
                        ['text' => 'A camera lens attachment', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'You should always use your phone flash for indoor photography to get the best results.',
                    'explanation' => 'Phone flashes create harsh shadows and red eyes. Window light or soft artificial light is better.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'In Zambia, an Android smartphone is a powerful tool for creating professional content without buying expensive equipment.',
                    'explanation' => 'Smartphones are capable of taking professional photos and videos when used with proper light, composition, and editing.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'Name one free app used for video editing in this course. (one word)',
                    'explanation' => 'CapCut is the free video editing app recommended in this course for trimming, splitting, and adding music to videos.',
                    'correct_answer' => 'CapCut',
                ],
            ],
        ];
    }

    private function module2Quiz(): array
    {
        return [
            'title' => 'Module 2 Quiz: Writing, Posting, and Growing',
            'description' => 'Test your knowledge of caption writing, platform strategy, posting consistency, and audience engagement.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main job of a "hook" in a social media caption?',
                    'explanation' => 'A hook is the first line designed to grab attention immediately and stop people from scrolling past.',
                    'options' => [
                        ['text' => 'To list product prices', 'is_correct' => false],
                        ['text' => 'To grab attention in the first two seconds', 'is_correct' => true],
                        ['text' => 'To add hashtags at the end', 'is_correct' => false],
                        ['text' => 'To write the date of the post', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which platform is best for short, entertaining videos under 60 seconds in Zambia?',
                    'explanation' => 'TikTok is designed for short videos and has a strong presence among young Zambian audiences.',
                    'options' => [
                        ['text' => 'LinkedIn', 'is_correct' => false],
                        ['text' => 'TikTok', 'is_correct' => true],
                        ['text' => 'Microsoft Word', 'is_correct' => false],
                        ['text' => 'Google Sheets', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does CTA stand for in content creation?',
                    'explanation' => 'CTA stands for Call-to-Action, which is an instruction telling the reader what to do next.',
                    'options' => [
                        ['text' => 'Creative Text App', 'is_correct' => false],
                        ['text' => 'Call-to-Action', 'is_correct' => true],
                        ['text' => 'Camera Timer Alert', 'is_correct' => false],
                        ['text' => 'Content Tracking Algorithm', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is the best sign that your audience finds your content valuable?',
                    'explanation' => 'Saves and shares indicate people found your content useful enough to return to it or show others.',
                    'options' => [
                        ['text' => 'A high number of views but no comments', 'is_correct' => false],
                        ['text' => 'Many saves and shares', 'is_correct' => true],
                        ['text' => 'Only likes from family members', 'is_correct' => false],
                        ['text' => 'Comments from bots', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why is consistency more important than viral hits for long-term growth?',
                    'explanation' => 'Consistency trains the algorithm, builds audience trust, and improves your skills over time.',
                    'options' => [
                        ['text' => 'Viral hits are impossible to achieve', 'is_correct' => false],
                        ['text' => 'Consistency builds trust and trains the algorithm', 'is_correct' => true],
                        ['text' => 'Consistent posts cost less money', 'is_correct' => false],
                        ['text' => 'Viral content is always illegal', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is the main advantage of using WhatsApp Status for a small business?',
                    'explanation' => 'WhatsApp Status is visible to existing contacts, making it ideal for personal, daily updates and trust-building.',
                    'options' => [
                        ['text' => 'It reaches strangers automatically', 'is_correct' => false],
                        ['text' => 'It feels personal and reaches existing contacts', 'is_correct' => true],
                        ['text' => 'It requires a computer to post', 'is_correct' => false],
                        ['text' => 'It is only available on iPhones', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Long paragraphs with no breaks work best for social media captions.',
                    'explanation' => 'Phone screens are small. Short, broken-up text is easier to read and keeps people engaged.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'Facebook Live videos get priority in the Facebook news feed compared to regular posts.',
                    'explanation' => 'Facebook promotes live videos more heavily because they generate real-time engagement.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What is the name of the free design tool recommended for creating graphics and thumbnails? (one word)',
                    'explanation' => 'Canva is the free design tool recommended for creating social media graphics, thumbnails, and price lists.',
                    'correct_answer' => 'Canva',
                ],
            ],
        ];
    }

    private function module3Quiz(): array
    {
        return [
            'title' => 'Module 3 Quiz: Views to Money and Creator Responsibility',
            'description' => 'Test your knowledge of monetisation, brand deals, copyright, defamation, and building a sustainable creator career.',
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of the following is a safe way to add music to your video?',
                    'explanation' => 'CapCut and TikTok provide free, pre-licensed music libraries that are safe for creators to use.',
                    'options' => [
                        ['text' => 'Download the latest popular song from a free MP3 site', 'is_correct' => false],
                        ['text' => 'Use music from CapCut or TikToks free library', 'is_correct' => true],
                        ['text' => 'Record music playing from the radio', 'is_correct' => false],
                        ['text' => 'Copy a song from someone elses video', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is a brand deal in content creation?',
                    'explanation' => 'A brand deal is when a business pays a creator to feature or mention their product in content.',
                    'options' => [
                        ['text' => 'A free gift from a friend', 'is_correct' => false],
                        ['text' => 'A paid agreement to feature a product in your content', 'is_correct' => true],
                        ['text' => 'A type of video transition', 'is_correct' => false],
                        ['text' => 'A music licensing agreement', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Why should you disclose a paid partnership in your content?',
                    'explanation' => 'Disclosure is honest, builds trust with your audience, and is an ethical requirement for sponsored content.',
                    'options' => [
                        ['text' => 'Because it makes the post look longer', 'is_correct' => false],
                        ['text' => 'Because it is required by law and builds audience trust', 'is_correct' => true],
                        ['text' => 'Because it increases the video resolution', 'is_correct' => false],
                        ['text' => 'Because it hides the sponsors name', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What is defamation?',
                    'explanation' => 'Defamation is making a false statement that harms someones reputation, which can lead to legal action.',
                    'options' => [
                        ['text' => 'A type of camera lens', 'is_correct' => false],
                        ['text' => 'Making a false statement that damages someones reputation', 'is_correct' => true],
                        ['text' => 'A video editing effect', 'is_correct' => false],
                        ['text' => 'A method of increasing followers', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'What does it mean to diversify your income as a creator?',
                    'explanation' => 'Diversifying means earning from multiple sources so you are not dependent on a single stream.',
                    'options' => [
                        ['text' => 'Post on only one social platform', 'is_correct' => false],
                        ['text' => 'Earn money from multiple sources rather than one', 'is_correct' => true],
                        ['text' => 'Delete old content regularly', 'is_correct' => false],
                        ['text' => 'Use only one payment method', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'Multiple Choice',
                    'text' => 'Which of these is an example of selling services through content?',
                    'explanation' => 'Offering paid tutoring based on your educational content is an example of selling services.',
                    'options' => [
                        ['text' => 'Giving away all your products for free', 'is_correct' => false],
                        ['text' => 'Offering paid tutoring based on your teaching content', 'is_correct' => true],
                        ['text' => 'Downloading copyrighted music', 'is_correct' => false],
                        ['text' => 'Deleting negative comments', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'True/False',
                    'text' => 'You can use any photo you find on Google Images in your content without checking the licence.',
                    'explanation' => 'Most images on Google are copyrighted. You need permission or a licence to use them legally.',
                    'correct_answer' => 'False',
                ],
                [
                    'type' => 'True/False',
                    'text' => 'A creators reputation and trustworthiness are more valuable than a single brand deal payment.',
                    'explanation' => 'Your reputation is your most valuable asset. One bad deal can damage trust permanently.',
                    'correct_answer' => 'True',
                ],
                [
                    'type' => 'Short Answer',
                    'text' => 'What legal concept protects original creative work from being used without permission? (one word)',
                    'explanation' => 'Copyright is the legal right that protects original creative work from unauthorised use.',
                    'correct_answer' => 'Copyright',
                ],
            ],
        ];
    }

    private function createAssignments(): void
    {
        Assignment::create([
            'course_id' => $this->courseId,
            'lesson_id' => null,
            'title' => 'Your First Piece of Content',
            'description' => 'Apply planning, filming, and editing skills by creating a short piece of content ready for social media.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Choose a topic or product to promote. It can be a real business, a hobby, or a fictional product for practice.
Step 2: Plan your content using a simple content calendar format. Write down: the hook, the main message, the platform (TikTok, Facebook, or WhatsApp Status), and the call-to-action.
Step 3: Using your phone, shoot at least three photos or one short video (30-60 seconds) that illustrate your topic. Apply the lighting and composition techniques from Module 1.
Step 4: Edit your photo in Canva or your video in CapCut. Add text, a simple caption, and music if relevant.
Step 5: Write the full caption you would use if posting this content, including the hook and call-to-action.
Step 6: Export your final image or video. Submit the file together with a document (PDF or DOCX) that contains your content plan, caption, and a 100-word reflection on what you learned.
INSTRUCTIONS,
            'max_points' => 100,
            'passing_points' => 50,
            'due_date' => null,
            'allow_late_submission' => 1,
            'allowed_file_types' => 'pdf,doc,docx,jpg,png,mp4,zip',
            'max_file_size_mb' => 10,
        ]);

        Assignment::create([
            'course_id' => $this->courseId,
            'lesson_id' => null,
            'title' => 'My Social Media Campaign',
            'description' => 'Design a seven-day social media campaign for a Zambian business, complete with posts, captions, and a monetisation idea.',
            'instructions' => <<<'INSTRUCTIONS'
Step 1: Choose a small business in Zambia. It could be a market stall, a salon, a bakery, a tailoring shop, or a farming business.
Step 2: Create a seven-day content calendar with one post per day. For each day, specify: the platform, the format (photo, video, graphic, or text), the hook, and the call-to-action.
Step 3: Write the full caption for three of the seven posts. Each caption must include a hook, a story or value section, a list or tip, and a clear call-to-action.
Step 4: Design one graphic or thumbnail in Canva for one of your posts. Export it as a PNG or JPG.
Step 5: Write a 150-word monetisation plan. Explain how this business could earn money from content through brand deals, product sales, or services.
Step 6: Compile everything into one document (PDF or DOCX): the content calendar, the three captions, the monetisation plan, and the Canva graphic. Upload the document and the image file.
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
        $this->command->info('Digital & Content Creation content seeded successfully.');
        $this->command->info('Modules: 3 | Lessons: 10 | Quizzes: 3 | Assignments: 2');
    }
}
