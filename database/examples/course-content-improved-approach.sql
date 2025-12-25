-- =====================================================
-- IMPROVED COURSE CONTENT APPROACH
-- Example: Microsoft Office Suite Course (First Module)
-- =====================================================

-- This demonstrates the CORRECT way to structure course content:
-- 1. Brief lesson overview in content field
-- 2. Downloadable resources for detailed materials

-- =====================================================
-- MODULE 1: INTRODUCTION TO MICROSOFT OFFICE
-- =====================================================

INSERT INTO `modules` (`course_id`, `title`, `description`, `display_order`, `duration_minutes`, `is_published`) VALUES
(1, 'Introduction to Microsoft Office Suite', 'Overview of Microsoft Office applications and common features', 1, 240, 1);

SET @module_id = LAST_INSERT_ID();

-- =====================================================
-- LESSON 1: Welcome and Overview
-- =====================================================

INSERT INTO `lessons` (`module_id`, `title`, `content`, `lesson_type`, `video_url`, `duration_minutes`, `display_order`, `is_preview`, `is_mandatory`, `points`) VALUES
(@module_id,
'Welcome to Microsoft Office Suite',
'### Welcome to the Course!

**Learning Objectives:**
- Understand the components of Microsoft Office Suite
- Learn about Word, Excel, PowerPoint, and Publisher
- Identify common use cases for each application

**What You Will Learn:**
In this lesson, we provide an overview of the Microsoft Office Suite and introduce you to each application you\'ll be mastering throughout this course.

**Get Started:**
1. Watch the welcome video below
2. Download the course syllabus (PDF)
3. Download the Office Suite overview guide
4. Review the getting started checklist

**Note:** All detailed course materials are available as downloadable resources below the video.',
'Video',
'YOUR_YOUTUBE_VIDEO_ID',  -- Replace with actual YouTube video ID
15,
1,
1,  -- This lesson is a preview
1,  -- This lesson is mandatory
5);

SET @lesson_id = LAST_INSERT_ID();

-- Add downloadable resources for Lesson 1
INSERT INTO `lesson_resources` (`lesson_id`, `title`, `description`, `resource_type`, `file_url`, `file_size_kb`) VALUES
(@lesson_id, 'Course Syllabus (PDF)', 'Complete course outline including modules, lessons, and assessment schedule', 'PDF', 'https://example.com/materials/office-suite-syllabus.pdf', 250),
(@lesson_id, 'Office Suite Overview Guide', 'Comprehensive PDF guide introducing all Office applications with screenshots', 'PDF', 'https://example.com/materials/office-overview-guide.pdf', 1500),
(@lesson_id, 'Getting Started Checklist', 'Step-by-step checklist to prepare for the course', 'PDF', 'https://example.com/materials/getting-started-checklist.pdf', 150);

-- =====================================================
-- LESSON 2: Common Interface Elements
-- =====================================================

INSERT INTO `lessons` (`module_id`, `title`, `content`, `lesson_type`, `video_url`, `duration_minutes`, `display_order`, `is_preview`, `is_mandatory`, `points`) VALUES
(@module_id,
'Common Interface Elements',
'### Mastering the Office Interface

**Learning Objectives:**
- Navigate the Ribbon interface
- Use the Quick Access Toolbar
- Understand the File menu (Backstage View)
- Learn essential keyboard shortcuts

**What You\'ll Learn:**
All Microsoft Office applications share a common interface design. Understanding these elements will help you work efficiently across Word, Excel, PowerPoint, and Publisher.

**Study Materials:**
Download the interface guide (PDF) and the keyboard shortcuts reference sheet. Practice identifying interface elements in the practice workbook.

**Practice Exercise:**
After watching the video and reviewing the materials, complete the interface identification exercise (included in the practice file).',
'Video',
'YOUR_YOUTUBE_VIDEO_ID_2',
25,
2,
1,  -- Also a preview lesson
1,  -- Mandatory
15);

SET @lesson_id = LAST_INSERT_ID();

-- Add resources for Lesson 2
INSERT INTO `lesson_resources` (`lesson_id`, `title`, `description`, `resource_type`, `file_url`, `file_size_kb`) VALUES
(@lesson_id, 'Office Interface Guide (PDF)', 'Visual guide with labeled screenshots of the Office interface', 'PDF', 'https://example.com/materials/interface-guide.pdf', 800),
(@lesson_id, 'Keyboard Shortcuts Reference', 'Complete list of essential Office keyboard shortcuts', 'PDF', 'https://example.com/materials/office-shortcuts.pdf', 200),
(@lesson_id, 'Interface Practice Workbook', 'Interactive exercise to identify and practice using interface elements', 'Document', 'https://example.com/materials/interface-practice.docx', 150);

-- =====================================================
-- LESSON 3: File Management Basics
-- =====================================================

INSERT INTO `lessons` (`module_id`, `title`, `content`, `lesson_type`, `video_url`, `duration_minutes`, `display_order`, `is_preview`, `is_mandatory`, `points`) VALUES
(@module_id,
'File Management Basics',
'### Working with Office Files

**Learning Objectives:**
- Create, save, and open documents
- Understand file formats (.docx, .xlsx, .pptx)
- Organize files effectively
- Use AutoSave and version history

**Key Topics:**
- Creating new documents
- Saving with descriptive names
- File format options
- Folder organization best practices

**Practice:**
Download the file management exercise kit which includes practice files and a folder structure template.',
'Video',
'YOUR_YOUTUBE_VIDEO_ID_3',
20,
3,
0,  -- Not a preview
1,  -- Mandatory
10);

SET @lesson_id = LAST_INSERT_ID();

-- Add resources for Lesson 3
INSERT INTO `lesson_resources` (`lesson_id`, `title`, `description`, `resource_type`, `file_url`, `file_size_kb`) VALUES
(@lesson_id, 'File Management Guide (PDF)', 'Best practices for naming, organizing, and backing up Office files', 'PDF', 'https://example.com/materials/file-management-guide.pdf', 500),
(@lesson_id, 'Practice Files Package', 'Sample files for practicing file operations', 'Archive', 'https://example.com/materials/practice-files.zip', 2500),
(@lesson_id, 'Folder Structure Template', 'Recommended folder organization system for Office documents', 'Document', 'https://example.com/materials/folder-template.docx', 100);

-- =====================================================
-- MODULE 2: MICROSOFT WORD - DOCUMENT CREATION
-- =====================================================

INSERT INTO `modules` (`course_id`, `title`, `description`, `display_order`, `duration_minutes`, `is_published`) VALUES
(1, 'Microsoft Word - Document Creation', 'Master document creation, formatting, and basic editing in Microsoft Word', 2, 480, 1);

SET @module_id = LAST_INSERT_ID();

-- =====================================================
-- LESSON 1: Introduction to Microsoft Word
-- =====================================================

INSERT INTO `lessons` (`module_id`, `title`, `content`, `lesson_type`, `video_url`, `duration_minutes`, `display_order`, `is_preview`, `is_mandatory`, `points`) VALUES
(@module_id,
'Introduction to Microsoft Word',
'### Getting Started with Word

**What You Will Learn:**
- The Word interface and views
- Creating your first document
- Basic text entry and editing
- Saving and printing documents

**Course Materials:**
This lesson includes comprehensive PDF guides and practice documents. Download all materials before starting the video tutorial.

**Practice Exercise:**
Complete the "My First Document" exercise using the provided template.',
'Video',
'YOUR_YOUTUBE_VIDEO_ID_4',
30,
1,
0,
1,
10);

SET @lesson_id = LAST_INSERT_ID();

-- Add Word introduction resources
INSERT INTO `lesson_resources` (`lesson_id`, `title`, `description`, `resource_type`, `file_url`, `file_size_kb`) VALUES
(@lesson_id, 'Word Basics Guide (PDF)', 'Complete beginner\'s guide to Microsoft Word with screenshots', 'PDF', 'https://example.com/materials/word-basics-guide.pdf', 2000),
(@lesson_id, 'Practice Document Template', 'Starter template for your first Word document', 'Document', 'https://example.com/materials/first-document-template.docx', 50),
(@lesson_id, 'Word Exercise - My First Document', 'Guided exercise with step-by-step instructions', 'Document', 'https://example.com/materials/word-exercise-1.docx', 120),
(@lesson_id, 'Exercise Answer Key', 'Completed example of the practice exercise', 'Document', 'https://example.com/materials/word-exercise-1-answers.docx', 150);

-- =====================================================
-- LESSON 2: Text Formatting
-- =====================================================

INSERT INTO `lessons` (`module_id`, `title`, `content`, `lesson_type`, `video_url`, `duration_minutes`, `display_order`, `is_preview`, `is_mandatory`, `points`) VALUES
(@module_id,
'Character and Paragraph Formatting',
'### Making Your Documents Look Professional

**Learning Objectives:**
- Apply character formatting (font, size, color, bold, italic)
- Use paragraph alignment and spacing
- Work with bullets and numbering
- Apply borders and shading

**What\'s Included:**
- Video tutorial demonstrating all formatting techniques
- Formatting reference guide (PDF)
- Practice documents with exercises
- Professional document examples

**Practice Assignment:**
Format the provided unformatted document using the techniques learned. Compare your result with the example solution.',
'Video',
'YOUR_YOUTUBE_VIDEO_ID_5',
40,
2,
0,
1,
20);

SET @lesson_id = LAST_INSERT_ID();

-- Add formatting resources
INSERT INTO `lesson_resources` (`lesson_id`, `title`, `description`, `resource_type`, `file_url`, `file_size_kb`) VALUES
(@lesson_id, 'Formatting Reference Guide (PDF)', 'Quick reference for all Word formatting options', 'PDF', 'https://example.com/materials/word-formatting-guide.pdf', 1200),
(@lesson_id, 'Unformatted Practice Document', 'Document to practice formatting techniques', 'Document', 'https://example.com/materials/format-practice.docx', 80),
(@lesson_id, 'Formatted Example', 'Professionally formatted version showing best practices', 'Document', 'https://example.com/materials/format-example.docx', 120),
(@lesson_id, 'Professional Document Templates', 'Collection of formatted document templates', 'Archive', 'https://example.com/materials/word-templates.zip', 800);

-- =====================================================
-- MODULE 3: MICROSOFT EXCEL - FUNDAMENTALS
-- =====================================================

INSERT INTO `modules` (`course_id`, `title`, `description`, `display_order`, `duration_minutes`, `is_published`) VALUES
(1, 'Microsoft Excel - Fundamentals', 'Introduction to spreadsheets, data entry, basic formulas, and formatting', 4, 540, 1);

SET @module_id = LAST_INSERT_ID();

-- =====================================================
-- LESSON 1: Introduction to Excel
-- =====================================================

INSERT INTO `lessons` (`module_id`, `title`, `content`, `lesson_type`, `video_url`, `duration_minutes`, `display_order`, `is_preview`, `is_mandatory`, `points`) VALUES
(@module_id,
'Introduction to Microsoft Excel',
'### Excel Fundamentals

**Learning Objectives:**
- Navigate the Excel interface
- Understand workbooks, worksheets, cells, rows, and columns
- Enter and edit data
- Use basic Excel features

**Course Materials:**
This lesson includes:
- Excel interface guide (PDF)
- Practice workbook with guided exercises
- Sample data files for practice
- Quick reference sheet

**Get Started:**
Watch the introduction video, then download the practice workbook and follow along with the exercises.',
'Video',
'YOUR_YOUTUBE_VIDEO_ID_6',
30,
1,
0,
1,
10);

SET @lesson_id = LAST_INSERT_ID();

-- Add Excel introduction resources
INSERT INTO `lesson_resources` (`lesson_id`, `title`, `description`, `resource_type`, `file_url`, `file_size_kb`) VALUES
(@lesson_id, 'Excel Interface Guide (PDF)', 'Visual guide to Excel interface with labeled components', 'PDF', 'https://example.com/materials/excel-interface-guide.pdf', 1000),
(@lesson_id, 'Practice Workbook - Getting Started', 'Guided exercises for Excel beginners', 'Spreadsheet', 'https://example.com/materials/excel-practice-1.xlsx', 150),
(@lesson_id, 'Sample Data Files', 'Various data sets for practice exercises', 'Archive', 'https://example.com/materials/excel-sample-data.zip', 500),
(@lesson_id, 'Excel Quick Reference (PDF)', 'One-page cheat sheet for Excel basics', 'PDF', 'https://example.com/materials/excel-quick-reference.pdf', 200);

-- =====================================================
-- LESSON 2: Formulas and Functions
-- =====================================================

INSERT INTO `lessons` (`module_id`, `title`, `content`, `lesson_type`, `video_url`, `duration_minutes`, `display_order`, `is_preview`, `is_mandatory`, `points`) VALUES
(@module_id,
'Basic Formulas and Functions',
'### Calculating with Excel

**What You Will Learn:**
- Write basic formulas using arithmetic operators
- Use SUM, AVERAGE, COUNT, MAX, MIN functions
- Understand cell references (relative vs. absolute)
- Copy formulas effectively

**Practice Materials:**
- Formula reference guide (PDF)
- Practice workbook with exercises
- Real-world examples (budget, grade book)
- Answer keys for self-checking

**Assignment:**
Complete the formula exercises in the practice workbook. Check your answers with the provided solution file.',
'Video',
'YOUR_YOUTUBE_VIDEO_ID_7',
45,
2,
0,
1,
20);

SET @lesson_id = LAST_INSERT_ID();

-- Add formula resources
INSERT INTO `lesson_resources` (`lesson_id`, `title`, `description`, `resource_type`, `file_url`, `file_size_kb`) VALUES
(@lesson_id, 'Formula Reference Guide (PDF)', 'Complete guide to basic Excel formulas with examples', 'PDF', 'https://example.com/materials/excel-formulas-guide.pdf', 1500),
(@lesson_id, 'Formula Practice Workbook', 'Exercises covering all basic formulas and functions', 'Spreadsheet', 'https://example.com/materials/formula-practice.xlsx', 200),
(@lesson_id, 'Budget Template with Formulas', 'Real-world example: Monthly budget spreadsheet', 'Spreadsheet', 'https://example.com/materials/budget-template.xlsx', 120),
(@lesson_id, 'Grade Book Example', 'Grade calculation spreadsheet demonstrating formulas', 'Spreadsheet', 'https://example.com/materials/gradebook-example.xlsx', 150),
(@lesson_id, 'Practice Exercise Solutions', 'Answer key for all formula exercises', 'Spreadsheet', 'https://example.com/materials/formula-answers.xlsx', 250);

-- =====================================================
-- NOTES ON THIS APPROACH
-- =====================================================

/*
KEY BENEFITS OF THIS APPROACH:

1. BRIEF LESSON CONTENT:
   - Easy to read on screen
   - Clear learning objectives
   - Instructions for using resources
   - Overview only (not entire course materials)

2. RICH DOWNLOADABLE RESOURCES:
   - PDF guides for detailed reference
   - Practice files (Word, Excel, PowerPoint)
   - Templates for real-world use
   - Answer keys for self-assessment
   - Organized by lesson

3. BETTER USER EXPERIENCE:
   - Students can download materials once
   - Work offline with practice files
   - Print PDF guides if needed
   - Keep resources for future reference

4. FLEXIBILITY:
   - Can use external URLs (Google Drive, Dropbox)
   - Can upload files to server
   - Mix of both approaches
   - Easy to update individual resources

5. TRACKING:
   - Download counts tracked automatically
   - See which resources are most popular
   - Analytics on student engagement

IMPLEMENTATION TIPS:

1. Keep lesson content to 200-500 words
2. Focus on learning objectives and instructions
3. Provide detailed content in downloadable PDFs
4. Always include practice files for hands-on learning
5. Provide answer keys/solutions
6. Use descriptive resource titles
7. Add helpful descriptions to each resource
8. Organize files logically

FILE HOSTING OPTIONS:

Option 1: Upload to Server
- Use /api/lesson-resources.php to upload files
- Files stored in /public/uploads/courses/lessons/resources/
- Full control over files
- No external dependencies

Option 2: External URLs
- Google Drive (shareable link)
- Dropbox (public link)
- OneDrive (share link)
- YouTube (for videos)
- Any accessible URL

Option 3: Mixed Approach (Recommended)
- Host PDFs and documents on your server
- Link to YouTube for videos
- Use Google Drive for large files
- Mix as needed

NEXT STEPS:

1. Create your course content (lessons) with brief overviews
2. Prepare detailed materials (PDFs, practice files, templates)
3. Upload files or get external URLs
4. Add resources using INSERT statements above
5. Or use the instructor interface to upload via web UI

*/
