<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class TradeCertificateComputerStudiesLevel3DiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Trade Certificate in Computer Studies Level III lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Trade Certificate in Computer Studies Level III')->first();

        if (! $course) {
            $this->command->error('Course "Trade Certificate in Computer Studies Level III" not found. Aborting.');

            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'A1.1.1 Parts of a Computer and the Input-Process-Output Cycle',
                'filename' => 'input-process-output-cycle.svg',
                'alt' => 'The input-process-output cycle: input devices feed data to the CPU and RAM, which produce output on monitors, printers and speakers.',
                'caption' => 'Figure: Every useful computer task follows the input-process-output cycle.',
            ],
            [
                'lesson_title' => 'A1.1.2 Peripherals and Ports: Connecting Devices Correctly',
                'filename' => 'computer-ports-peripherals.svg',
                'alt' => 'A desktop system unit connected to a monitor, keyboard, mouse, printer and USB drive through the correct ports.',
                'caption' => 'Figure: Peripherals extend a computer when plugged into the right port.',
            ],
            [
                'lesson_title' => 'A1.2.3 Basic Operations, Copyright, Security and User Interfaces',
                'filename' => 'storage-capacity-ladder.svg',
                'alt' => 'Storage capacity ladder from bit to terabyte, showing how each unit is about 1024 times larger than the one below it.',
                'caption' => 'Figure: Storage capacity grows by roughly 1024 at each step up the ladder.',
            ],
            [
                'lesson_title' => 'A1.3.4 Pagination, Saving and Printing',
                'filename' => 'word-document-workflow.svg',
                'alt' => 'Word document workflow: type the draft, format the text, proofread, paginate, save the file, then print or share.',
                'caption' => 'Figure: A finished Word document moves through six clear stages.',
            ],
            [
                'lesson_title' => 'A1.4.1 Introduction to Spreadsheets',
                'filename' => 'spreadsheet-anatomy.svg',
                'alt' => 'Spreadsheet anatomy: a workbook holds worksheets made of rows, columns and cells, where a formula produces a chart.',
                'caption' => 'Figure: Workbooks, worksheets, cells, formulas and charts work together.',
            ],
            [
                'lesson_title' => 'A1.5.1 Getting Started with Presentation Software',
                'filename' => 'presentation-workflow.svg',
                'alt' => 'Presentation workflow: plan the message, build the slides, format and animate, then present to the audience.',
                'caption' => 'Figure: A strong presentation is planned, built, formatted and then delivered.',
            ],
            [
                'lesson_title' => 'A1.6.3 Elementary Graphics and Text Frames',
                'filename' => 'dtp-page-layout.svg',
                'alt' => 'Desktop publishing page layout showing headline, body text, image and caption frames aligned to margins and guides.',
                'caption' => 'Figure: A DTP page is built from linked text and graphics frames.',
            ],
            [
                'lesson_title' => 'A1.7.1 Understanding Computer Networks',
                'filename' => 'lan-vs-wan.svg',
                'alt' => 'Comparison of LAN and WAN networks: a LAN covers one building with a switch, while a WAN spans cities through routers and the internet.',
                'caption' => 'Figure: LANs are local; WANs connect LANs over long distances.',
            ],
            [
                'lesson_title' => 'A1.7.3 Browsing the Web with Search Engines',
                'filename' => 'web-request-response.svg',
                'alt' => 'How a browser uses a URL and DNS to send a request to a web server and receive a webpage in response.',
                'caption' => 'Figure: DNS, requests and responses bring webpages to your browser.',
            ],
            [
                'lesson_title' => 'A1.8.5 Creating a Multimedia Presentation',
                'filename' => 'multimedia-pipeline.svg',
                'alt' => 'Multimedia production pipeline: capture raw media, edit each element, combine text, image, audio and video, then publish.',
                'caption' => 'Figure: Multimedia moves from capture through editing, combination and publishing.',
            ],
        ];

        $updatedIds = [];

        foreach ($diagrams as $diagram) {
            $lesson = Lesson::whereIn('module_id', function ($query) use ($course) {
                $query->select('id')
                    ->from('modules')
                    ->where('course_id', $course->id);
            })
                ->where('title', 'like', '%'.$diagram['lesson_title'].'%')
                ->first();

            if (! $lesson) {
                $this->command->warn("Lesson '{$diagram['lesson_title']}' not found. Skipping.");

                continue;
            }

            if (str_contains($lesson->content ?? '', $diagram['filename'])) {
                $this->command->info("Lesson {$lesson->id} already contains {$diagram['filename']}. Skipping.");

                continue;
            }

            $figure = sprintf(
                '<figure><img class="lesson-diagram" src="/assets/diagrams/computer-studies-level-iii/%s" alt="%s"><figcaption>%s</figcaption></figure>',
                $diagram['filename'],
                $diagram['alt'],
                $diagram['caption']
            );

            $content = $lesson->content ?? '';

            $firstParagraphEnd = stripos($content, '</p>');

            if ($firstParagraphEnd !== false) {
                $insertAt = $firstParagraphEnd + 4;
                $content = substr($content, 0, $insertAt)."\n\n{$figure}".substr($content, $insertAt);
            } else {
                $content = "{$figure}\n\n{$content}";
            }

            $lesson->content = $content;
            $lesson->save();

            $updatedIds[] = $lesson->id;
            $this->command->info("Updated lesson {$lesson->id} with {$diagram['filename']}.");
        }

        if (empty($updatedIds)) {
            $this->command->info('No lessons were updated.');
        } else {
            $this->command->info('Updated lesson IDs: '.implode(', ', $updatedIds));
        }
    }
}
