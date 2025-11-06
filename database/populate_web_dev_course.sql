-- ============================================
-- EDUTRACK LMS - Certificate in Web Development
-- Complete Course Data Population
-- ============================================
-- This script populates the course with modules, lessons, quizzes, and assignments
-- Run this in phpMyAdmin after confirming the course exists

-- Get the course ID
SET @course_id = (SELECT id FROM courses WHERE slug = 'certificate-in-web-development' LIMIT 1);

-- If course doesn't exist, show error
SELECT CASE
    WHEN @course_id IS NULL THEN 'ERROR: Course not found! Please create the course first.'
    ELSE CONCAT('SUCCESS: Course ID = ', @course_id)
END AS status;

-- ============================================
-- MODULES
-- ============================================

-- Module 1: HTML Fundamentals
INSERT INTO course_modules (course_id, title, description, display_order, duration_minutes, is_preview) VALUES
(@course_id, 'HTML Fundamentals', 'Learn the building blocks of web pages with HTML. Master semantic markup, forms, and document structure.', 1, 90, 1);
SET @module1_id = LAST_INSERT_ID();

-- Module 2: CSS Styling
INSERT INTO course_modules (course_id, title, description, display_order, duration_minutes, is_preview) VALUES
(@course_id, 'CSS Styling', 'Transform your web pages with CSS. Learn layouts, responsive design, and modern styling techniques.', 2, 120, 0);
SET @module2_id = LAST_INSERT_ID();

-- Module 3: JavaScript Fundamentals
INSERT INTO course_modules (course_id, title, description, display_order, duration_minutes, is_preview) VALUES
(@course_id, 'JavaScript Fundamentals', 'Add interactivity to your websites with JavaScript. Learn programming basics and DOM manipulation.', 3, 150, 0);
SET @module3_id = LAST_INSERT_ID();

-- Module 4: Responsive Web Design Project
INSERT INTO course_modules (course_id, title, description, display_order, duration_minutes, is_preview) VALUES
(@course_id, 'Responsive Web Design Project', 'Apply everything you learned to build a complete responsive portfolio website from scratch.', 4, 180, 0);
SET @module4_id = LAST_INSERT_ID();

-- ============================================
-- LESSONS
-- ============================================

-- MODULE 1: HTML Fundamentals (5 lessons)
INSERT INTO lessons (module_id, course_id, title, description, content, lesson_type, video_url, video_duration_seconds, video_platform, display_order, duration_minutes, is_preview, is_mandatory) VALUES

-- Lesson 1: Introduction to HTML
(@module1_id, @course_id, 'Introduction to HTML', 'Understanding HTML and its role in web development',
'<h3>What is HTML?</h3>
<p>HTML (HyperText Markup Language) is the standard markup language for creating web pages. It describes the structure and content of web pages using elements and tags.</p>
<h3>Key Concepts:</h3>
<ul>
<li>HTML elements and tags</li>
<li>Document structure</li>
<li>Semantic HTML</li>
<li>HTML5 features</li>
</ul>
<h3>Why Learn HTML?</h3>
<p>HTML is the foundation of all websites. Every web developer must master HTML to build modern, accessible web applications.</p>',
'video', 'qz0aGYrrlhU', 900, 'youtube', 1, 15, 1, 1),

-- Lesson 2: HTML Document Structure
(@module1_id, @course_id, 'HTML Document Structure', 'Learn about the basic structure of an HTML document',
'<h3>Basic HTML Document Structure</h3>
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;title&gt;Page Title&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;h1&gt;My First Heading&lt;/h1&gt;
    &lt;p&gt;My first paragraph.&lt;/p&gt;
&lt;/body&gt;
&lt;/html&gt;
</code></pre>
<h3>Key Elements:</h3>
<ul>
<li><strong>&lt;!DOCTYPE html&gt;</strong> - Declares HTML5 document</li>
<li><strong>&lt;html&gt;</strong> - Root element</li>
<li><strong>&lt;head&gt;</strong> - Metadata container</li>
<li><strong>&lt;body&gt;</strong> - Visible content</li>
</ul>',
'video', 'UB1O30fR-EE', 720, 'youtube', 2, 12, 1, 1),

-- Lesson 3: Common HTML Elements
(@module1_id, @course_id, 'Common HTML Elements', 'Master headings, paragraphs, lists, and formatting',
'<h3>Text Elements</h3>
<ul>
<li><strong>Headings:</strong> &lt;h1&gt; to &lt;h6&gt;</li>
<li><strong>Paragraphs:</strong> &lt;p&gt;</li>
<li><strong>Line Breaks:</strong> &lt;br&gt;</li>
<li><strong>Bold:</strong> &lt;strong&gt; or &lt;b&gt;</li>
<li><strong>Italic:</strong> &lt;em&gt; or &lt;i&gt;</li>
</ul>
<h3>Lists</h3>
<ul>
<li><strong>Unordered Lists:</strong> &lt;ul&gt; with &lt;li&gt;</li>
<li><strong>Ordered Lists:</strong> &lt;ol&gt; with &lt;li&gt;</li>
<li><strong>Description Lists:</strong> &lt;dl&gt;, &lt;dt&gt;, &lt;dd&gt;</li>
</ul>',
'video', 'kUMe1FH4CHE', 840, 'youtube', 3, 14, 0, 1),

-- Lesson 4: Links and Navigation
(@module1_id, @course_id, 'Links and Navigation', 'Create hyperlinks and navigation menus',
'<h3>Creating Links</h3>
<pre><code>&lt;a href="https://example.com"&gt;Visit Example&lt;/a&gt;
&lt;a href="about.html"&gt;About Page&lt;/a&gt;
&lt;a href="#section"&gt;Jump to Section&lt;/a&gt;
&lt;a href="mailto:info@example.com"&gt;Email Us&lt;/a&gt;
</code></pre>
<h3>Link Attributes:</h3>
<ul>
<li><strong>href</strong> - Destination URL</li>
<li><strong>target</strong> - Where to open (_blank, _self)</li>
<li><strong>title</strong> - Tooltip text</li>
<li><strong>rel</strong> - Relationship (nofollow, noopener)</li>
</ul>',
'video', 'PlxWf493en4', 660, 'youtube', 4, 11, 0, 1),

-- Lesson 5: HTML Forms Basics
(@module1_id, @course_id, 'HTML Forms Basics', 'Create interactive forms to collect user input',
'<h3>Form Elements</h3>
<pre><code>&lt;form action="/submit" method="POST"&gt;
    &lt;label for="name"&gt;Name:&lt;/label&gt;
    &lt;input type="text" id="name" name="name"&gt;

    &lt;label for="email"&gt;Email:&lt;/label&gt;
    &lt;input type="email" id="email" name="email"&gt;

    &lt;button type="submit"&gt;Submit&lt;/button&gt;
&lt;/form&gt;
</code></pre>
<h3>Input Types:</h3>
<ul>
<li>text, email, password, number</li>
<li>checkbox, radio, file</li>
<li>date, time, color</li>
</ul>',
'video', 'fNcJuPIZ2WE', 1020, 'youtube', 5, 17, 0, 1);

-- MODULE 2: CSS Styling (5 lessons)
INSERT INTO lessons (module_id, course_id, title, description, content, lesson_type, video_url, video_duration_seconds, video_platform, display_order, duration_minutes, is_preview, is_mandatory) VALUES

-- Lesson 6: Introduction to CSS
(@module2_id, @course_id, 'Introduction to CSS', 'Learn how CSS transforms HTML into beautiful designs',
'<h3>What is CSS?</h3>
<p>CSS (Cascading Style Sheets) is used to style and layout web pages. It controls colors, fonts, spacing, and positioning.</p>
<h3>Three Ways to Add CSS:</h3>
<ul>
<li><strong>Inline:</strong> &lt;p style="color: red;"&gt;Text&lt;/p&gt;</li>
<li><strong>Internal:</strong> &lt;style&gt; in &lt;head&gt;</li>
<li><strong>External:</strong> &lt;link rel="stylesheet" href="style.css"&gt;</li>
</ul>
<h3>CSS Syntax:</h3>
<pre><code>selector {
    property: value;
}
</code></pre>',
'video', 'yfoY53QXEnI', 900, 'youtube', 1, 15, 0, 1),

-- Lesson 7: CSS Selectors
(@module2_id, @course_id, 'CSS Selectors', 'Target HTML elements with precision',
'<h3>Basic Selectors</h3>
<ul>
<li><strong>Element:</strong> p { color: blue; }</li>
<li><strong>Class:</strong> .header { font-size: 24px; }</li>
<li><strong>ID:</strong> #main { width: 100%; }</li>
<li><strong>Universal:</strong> * { margin: 0; }</li>
</ul>
<h3>Combinators</h3>
<ul>
<li><strong>Descendant:</strong> div p { }</li>
<li><strong>Child:</strong> div > p { }</li>
<li><strong>Adjacent:</strong> h1 + p { }</li>
</ul>',
'video', 'l1mER1bV0N0', 960, 'youtube', 2, 16, 0, 1),

-- Lesson 8: Box Model
(@module2_id, @course_id, 'CSS Box Model', 'Understand margins, borders, padding, and content',
'<h3>The Box Model</h3>
<p>Every HTML element is a box with four layers:</p>
<ol>
<li><strong>Content</strong> - The actual content (text, images)</li>
<li><strong>Padding</strong> - Space between content and border</li>
<li><strong>Border</strong> - Line around padding</li>
<li><strong>Margin</strong> - Space outside the border</li>
</ol>
<pre><code>.box {
    width: 300px;
    padding: 20px;
    border: 2px solid black;
    margin: 10px;
}
</code></pre>',
'video', 'rIO5326FgPE', 780, 'youtube', 3, 13, 0, 1),

-- Lesson 9: Flexbox Layout
(@module2_id, @course_id, 'Flexbox Layout', 'Create flexible responsive layouts easily',
'<h3>Flexbox Basics</h3>
<pre><code>.container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
}
</code></pre>
<h3>Main Properties:</h3>
<ul>
<li><strong>justify-content:</strong> Horizontal alignment</li>
<li><strong>align-items:</strong> Vertical alignment</li>
<li><strong>flex-direction:</strong> row, column</li>
<li><strong>flex-wrap:</strong> wrap, nowrap</li>
<li><strong>gap:</strong> Space between items</li>
</ul>',
'video', 'JJSoEo8JSnc', 1200, 'youtube', 4, 20, 0, 1),

-- Lesson 10: Responsive Design Basics
(@module2_id, @course_id, 'Responsive Design Basics', 'Make websites work on all screen sizes',
'<h3>Media Queries</h3>
<pre><code>/* Mobile First */
.container {
    width: 100%;
}

/* Tablet */
@media (min-width: 768px) {
    .container {
        width: 750px;
    }
}

/* Desktop */
@media (min-width: 1200px) {
    .container {
        width: 1140px;
    }
}
</code></pre>
<h3>Responsive Units:</h3>
<ul>
<li><strong>%</strong> - Percentage</li>
<li><strong>em/rem</strong> - Relative to font size</li>
<li><strong>vw/vh</strong> - Viewport width/height</li>
</ul>',
'video', 'srvUrASNj0s', 1080, 'youtube', 5, 18, 0, 1);

-- MODULE 3: JavaScript Fundamentals (5 lessons)
INSERT INTO lessons (module_id, course_id, title, description, content, lesson_type, video_url, video_duration_seconds, video_platform, display_order, duration_minutes, is_preview, is_mandatory) VALUES

-- Lesson 11: Introduction to JavaScript
(@module3_id, @course_id, 'Introduction to JavaScript', 'Your first steps into programming',
'<h3>What is JavaScript?</h3>
<p>JavaScript is a programming language that adds interactivity to websites. It runs in the browser and can respond to user actions.</p>
<h3>Adding JavaScript:</h3>
<pre><code>&lt;script&gt;
    console.log("Hello, World!");
&lt;/script&gt;

&lt;script src="app.js"&gt;&lt;/script&gt;
</code></pre>
<h3>Your First Code:</h3>
<pre><code>alert("Welcome to JavaScript!");
console.log("This appears in the console");
</code></pre>',
'video', 'W6NZfCO5SIk', 1200, 'youtube', 1, 20, 0, 1),

-- Lesson 12: Variables and Data Types
(@module3_id, @course_id, 'Variables and Data Types', 'Store and work with different types of data',
'<h3>Variables</h3>
<pre><code>let name = "John";
const age = 25;
var city = "New York"; // Old way

let isStudent = true;
let score = 95.5;
</code></pre>
<h3>Data Types:</h3>
<ul>
<li><strong>String:</strong> "text", \'text\'</li>
<li><strong>Number:</strong> 42, 3.14</li>
<li><strong>Boolean:</strong> true, false</li>
<li><strong>Array:</strong> [1, 2, 3]</li>
<li><strong>Object:</strong> {name: "John"}</li>
</ul>',
'video', 'edlFjlzxkSI', 960, 'youtube', 2, 16, 0, 1),

-- Lesson 13: Functions and Events
(@module3_id, @course_id, 'Functions and Events', 'Create reusable code and respond to user actions',
'<h3>Functions</h3>
<pre><code>function greet(name) {
    return "Hello, " + name + "!";
}

const result = greet("Sarah");
console.log(result); // "Hello, Sarah!"
</code></pre>
<h3>Events</h3>
<pre><code>&lt;button onclick="handleClick()"&gt;Click Me&lt;/button&gt;

&lt;script&gt;
function handleClick() {
    alert("Button clicked!");
}
&lt;/script&gt;
</code></pre>',
'video', 'N8ap4k_1QEQ', 1080, 'youtube', 3, 18, 0, 1),

-- Lesson 14: DOM Manipulation
(@module3_id, @course_id, 'DOM Manipulation', 'Change HTML and CSS with JavaScript',
'<h3>Selecting Elements</h3>
<pre><code>// By ID
const heading = document.getElementById("title");

// By Class
const items = document.getElementsByClassName("item");

// By CSS Selector
const button = document.querySelector(".btn");
const allButtons = document.querySelectorAll(".btn");
</code></pre>
<h3>Modifying Elements</h3>
<pre><code>heading.textContent = "New Title";
heading.style.color = "blue";
heading.classList.add("active");
</code></pre>',
'video', 'y17RuWkWdn8', 1200, 'youtube', 4, 20, 0, 1),

-- Lesson 15: Form Validation
(@module3_id, @course_id, 'Form Validation with JavaScript', 'Validate user input before submission',
'<h3>Basic Validation</h3>
<pre><code>function validateForm() {
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    if (email === "") {
        alert("Email is required");
        return false;
    }

    if (password.length < 8) {
        alert("Password must be at least 8 characters");
        return false;
    }

    return true;
}
</code></pre>
<h3>Email Validation:</h3>
<pre><code>const emailPattern = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/;
if (!emailPattern.test(email)) {
    alert("Invalid email format");
}
</code></pre>',
'video', 'In0nB0ABaUk', 900, 'youtube', 5, 15, 0, 1);

-- MODULE 4: Responsive Web Design Project (5 lessons)
INSERT INTO lessons (module_id, course_id, title, description, content, lesson_type, video_url, video_duration_seconds, video_platform, display_order, duration_minutes, is_preview, is_mandatory) VALUES

-- Lesson 16: Project Planning
(@module4_id, @course_id, 'Project Planning and Design', 'Plan your portfolio website structure',
'<h3>Project Overview</h3>
<p>You will build a complete responsive portfolio website that showcases your skills and projects.</p>
<h3>Required Sections:</h3>
<ul>
<li>Header with Navigation</li>
<li>Hero Section with Introduction</li>
<li>About Me Section</li>
<li>Skills Section</li>
<li>Projects Gallery</li>
<li>Contact Form</li>
<li>Footer</li>
</ul>
<h3>Planning Steps:</h3>
<ol>
<li>Sketch wireframes for mobile and desktop</li>
<li>Choose a color scheme (3-4 colors)</li>
<li>Select fonts (heading and body)</li>
<li>Plan content for each section</li>
<li>Create folder structure</li>
</ol>',
'text', NULL, 0, NULL, 1, 30, 0, 1),

-- Lesson 17: Building the Structure
(@module4_id, @course_id, 'Building HTML Structure', 'Create semantic HTML for your portfolio',
'<h3>HTML Structure</h3>
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;My Portfolio&lt;/title&gt;
    &lt;link rel="stylesheet" href="style.css"&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;header&gt;
        &lt;nav&gt;&lt;/nav&gt;
    &lt;/header&gt;

    &lt;main&gt;
        &lt;section id="hero"&gt;&lt;/section&gt;
        &lt;section id="about"&gt;&lt;/section&gt;
        &lt;section id="skills"&gt;&lt;/section&gt;
        &lt;section id="projects"&gt;&lt;/section&gt;
        &lt;section id="contact"&gt;&lt;/section&gt;
    &lt;/main&gt;

    &lt;footer&gt;&lt;/footer&gt;
    &lt;script src="app.js"&gt;&lt;/script&gt;
&lt;/body&gt;
&lt;/html&gt;
</code></pre>',
'text', NULL, 0, NULL, 2, 45, 0, 1),

-- Lesson 18: Styling the Design
(@module4_id, @course_id, 'Styling with CSS', 'Apply styles to create a beautiful design',
'<h3>CSS Organization</h3>
<pre><code>/* Reset and Base Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Variables */
:root {
    --primary-color: #2563eb;
    --text-dark: #1f2937;
    --text-light: #6b7280;
}

/* Header Styles */
header {
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 100;
}

/* Responsive Grid */
.projects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}
</code></pre>',
'text', NULL, 0, NULL, 3, 60, 0, 1),

-- Lesson 19: Adding Interactivity
(@module4_id, @course_id, 'JavaScript Interactivity', 'Add dynamic features to your portfolio',
'<h3>Key Features to Add:</h3>
<ol>
<li><strong>Mobile Navigation Toggle</strong>
<pre><code>const menuBtn = document.querySelector(".menu-btn");
const nav = document.querySelector("nav");

menuBtn.addEventListener("click", () => {
    nav.classList.toggle("active");
});
</code></pre>
</li>
<li><strong>Smooth Scrolling</strong>
<pre><code>document.querySelectorAll(\'a[href^="#"]\').forEach(anchor => {
    anchor.addEventListener("click", function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute("href"));
        target.scrollIntoView({ behavior: "smooth" });
    });
});
</code></pre>
</li>
<li><strong>Form Validation</strong> - Validate contact form before submission</li>
<li><strong>Scroll Animations</strong> - Fade in sections as user scrolls</li>
</ol>',
'text', NULL, 0, NULL, 4, 45, 0, 1),

-- Lesson 20: Testing and Deployment
(@module4_id, @course_id, 'Final Testing and Deployment', 'Test and publish your portfolio',
'<h3>Testing Checklist</h3>
<ul>
<li>✓ Test on multiple browsers (Chrome, Firefox, Safari)</li>
<li>✓ Test on mobile devices and tablets</li>
<li>✓ Check all links work correctly</li>
<li>✓ Validate HTML and CSS</li>
<li>✓ Test form submission</li>
<li>✓ Check loading speed</li>
<li>✓ Ensure images are optimized</li>
</ul>
<h3>Deployment Options</h3>
<ol>
<li><strong>GitHub Pages</strong> - Free hosting for static sites</li>
<li><strong>Netlify</strong> - Easy drag-and-drop deployment</li>
<li><strong>Vercel</strong> - Fast and simple deployment</li>
</ol>
<h3>After Deployment:</h3>
<ul>
<li>Share your portfolio URL</li>
<li>Add it to your resume</li>
<li>Update regularly with new projects</li>
</ul>',
'text', NULL, 0, NULL, 5, 30, 0, 1);

-- ============================================
-- QUIZZES
-- ============================================

-- Quiz 1: HTML Fundamentals Quiz
INSERT INTO quizzes (course_id, lesson_id, title, description, quiz_type, time_limit_minutes, passing_score, max_attempts, status) VALUES
(@course_id, NULL, 'HTML Fundamentals Quiz', 'Test your understanding of HTML basics and document structure', 'graded', 20, 70.00, 3, 'published');
SET @quiz1_id = LAST_INSERT_ID();

-- Quiz 2: CSS Styling Quiz
INSERT INTO quizzes (course_id, lesson_id, title, description, quiz_type, time_limit_minutes, passing_score, max_attempts, status) VALUES
(@course_id, NULL, 'CSS Styling Quiz', 'Test your knowledge of CSS selectors, box model, and layouts', 'graded', 20, 70.00, 3, 'published');
SET @quiz2_id = LAST_INSERT_ID();

-- ============================================
-- QUIZ QUESTIONS - HTML Quiz
-- ============================================

INSERT INTO quiz_questions (quiz_id, question_type, question_text, points, display_order) VALUES
(@quiz1_id, 'multiple_choice', 'What does HTML stand for?', 5.00, 1);
SET @q1 = LAST_INSERT_ID();

INSERT INTO quiz_questions (quiz_id, question_type, question_text, points, display_order) VALUES
(@quiz1_id, 'multiple_choice', 'Which HTML element is used for the largest heading?', 5.00, 2);
SET @q2 = LAST_INSERT_ID();

INSERT INTO quiz_questions (quiz_id, question_type, question_text, points, display_order) VALUES
(@quiz1_id, 'multiple_choice', 'What is the correct HTML for creating a hyperlink?', 5.00, 3);
SET @q3 = LAST_INSERT_ID();

INSERT INTO quiz_questions (quiz_id, question_type, question_text, points, display_order) VALUES
(@quiz1_id, 'multiple_choice', 'Which HTML element defines the document type?', 5.00, 4);
SET @q4 = LAST_INSERT_ID();

INSERT INTO quiz_questions (quiz_id, question_type, question_text, points, display_order) VALUES
(@quiz1_id, 'multiple_choice', 'What is the correct HTML for making a text input field?', 5.00, 5);
SET @q5 = LAST_INSERT_ID();

-- ============================================
-- QUIZ ANSWERS - HTML Quiz
-- ============================================

-- Question 1 answers
INSERT INTO quiz_answers (question_id, answer_text, is_correct, display_order) VALUES
(@q1, 'HyperText Markup Language', 1, 1),
(@q1, 'HighText Machine Language', 0, 2),
(@q1, 'HyperTool Multi Language', 0, 3),
(@q1, 'Home Tool Markup Language', 0, 4);

-- Question 2 answers
INSERT INTO quiz_answers (question_id, answer_text, is_correct, display_order) VALUES
(@q2, '<heading>', 0, 1),
(@q2, '<h1>', 1, 2),
(@q2, '<h6>', 0, 3),
(@q2, '<head>', 0, 4);

-- Question 3 answers
INSERT INTO quiz_answers (question_id, answer_text, is_correct, display_order) VALUES
(@q3, '<a url="http://example.com">Example</a>', 0, 1),
(@q3, '<a href="http://example.com">Example</a>', 1, 2),
(@q3, '<link>http://example.com</link>', 0, 3),
(@q3, '<a>http://example.com</a>', 0, 4);

-- Question 4 answers
INSERT INTO quiz_answers (question_id, answer_text, is_correct, display_order) VALUES
(@q4, '<!DOCTYPE html>', 1, 1),
(@q4, '<doctype html>', 0, 2),
(@q4, '<document>', 0, 3),
(@q4, '<!DOCTYPE>', 0, 4);

-- Question 5 answers
INSERT INTO quiz_answers (question_id, answer_text, is_correct, display_order) VALUES
(@q5, '<input type="text">', 1, 1),
(@q5, '<textinput type="text">', 0, 2),
(@q5, '<input type="textfield">', 0, 3),
(@q5, '<textfield>', 0, 4);

-- ============================================
-- QUIZ QUESTIONS - CSS Quiz
-- ============================================

INSERT INTO quiz_questions (quiz_id, question_type, question_text, points, display_order) VALUES
(@quiz2_id, 'multiple_choice', 'What does CSS stand for?', 5.00, 1);
SET @q6 = LAST_INSERT_ID();

INSERT INTO quiz_questions (quiz_id, question_type, question_text, points, display_order) VALUES
(@quiz2_id, 'multiple_choice', 'Which property is used to change the background color?', 5.00, 2);
SET @q7 = LAST_INSERT_ID();

INSERT INTO quiz_questions (quiz_id, question_type, question_text, points, display_order) VALUES
(@quiz2_id, 'multiple_choice', 'How do you select an element with id "header" in CSS?', 5.00, 3);
SET @q8 = LAST_INSERT_ID();

INSERT INTO quiz_questions (quiz_id, question_type, question_text, points, display_order) VALUES
(@quiz2_id, 'multiple_choice', 'Which CSS property controls the text size?', 5.00, 4);
SET @q9 = LAST_INSERT_ID();

INSERT INTO quiz_questions (quiz_id, question_type, question_text, points, display_order) VALUES
(@quiz2_id, 'multiple_choice', 'What are the four layers of the CSS box model (from inside to outside)?', 5.00, 5);
SET @q10 = LAST_INSERT_ID();

-- ============================================
-- QUIZ ANSWERS - CSS Quiz
-- ============================================

-- Question 6 answers
INSERT INTO quiz_answers (question_id, answer_text, is_correct, display_order) VALUES
(@q6, 'Cascading Style Sheets', 1, 1),
(@q6, 'Creative Style System', 0, 2),
(@q6, 'Computer Style Sheets', 0, 3),
(@q6, 'Colorful Style Sheets', 0, 4);

-- Question 7 answers
INSERT INTO quiz_answers (question_id, answer_text, is_correct, display_order) VALUES
(@q7, 'bgcolor', 0, 1),
(@q7, 'background-color', 1, 2),
(@q7, 'color-background', 0, 3),
(@q7, 'bg-color', 0, 4);

-- Question 8 answers
INSERT INTO quiz_answers (question_id, answer_text, is_correct, display_order) VALUES
(@q8, '.header', 0, 1),
(@q8, '#header', 1, 2),
(@q8, 'header', 0, 3),
(@q8, '*header', 0, 4);

-- Question 9 answers
INSERT INTO quiz_answers (question_id, answer_text, is_correct, display_order) VALUES
(@q9, 'text-size', 0, 1),
(@q9, 'font-size', 1, 2),
(@q9, 'text-style', 0, 3),
(@q9, 'font-style', 0, 4);

-- Question 10 answers
INSERT INTO quiz_answers (question_id, answer_text, is_correct, display_order) VALUES
(@q10, 'Content, Padding, Border, Margin', 1, 1),
(@q10, 'Margin, Border, Padding, Content', 0, 2),
(@q10, 'Content, Border, Padding, Margin', 0, 3),
(@q10, 'Padding, Content, Margin, Border', 0, 4);

-- ============================================
-- ASSIGNMENTS
-- ============================================

-- Assignment 1: Personal Bio Page
INSERT INTO assignments (course_id, lesson_id, title, description, instructions, max_points, passing_score, due_date, status) VALUES
(@course_id, 5, 'Create a Personal Bio Page', 'Build a simple HTML page with your personal information',
'<h3>Assignment Instructions:</h3>
<ol>
<li>Create an HTML file named <code>bio.html</code></li>
<li>Include a proper HTML5 document structure</li>
<li>Add the following sections:
<ul>
<li>A heading with your name</li>
<li>A paragraph about yourself</li>
<li>An unordered list of your hobbies</li>
<li>An ordered list of your top 3 skills</li>
<li>A link to your favorite website</li>
</ul>
</li>
<li>Use semantic HTML elements where appropriate</li>
<li>Ensure all HTML validates correctly</li>
</ol>
<h3>Grading Criteria:</h3>
<ul>
<li>Correct document structure (20 points)</li>
<li>All required sections included (40 points)</li>
<li>Proper use of HTML elements (30 points)</li>
<li>Code quality and organization (10 points)</li>
</ul>',
100.00, 70.00, DATE_ADD(NOW(), INTERVAL 7 DAY), 'published');

-- Assignment 2: Style Your Bio Page
INSERT INTO assignments (course_id, lesson_id, title, description, instructions, max_points, passing_score, due_date, status) VALUES
(@course_id, 10, 'Style Your Bio Page with CSS', 'Add beautiful styling to your HTML bio page',
'<h3>Assignment Instructions:</h3>
<ol>
<li>Create a CSS file named <code>style.css</code> and link it to your bio.html</li>
<li>Apply the following styles:
<ul>
<li>Set a custom font family</li>
<li>Add background color to the page</li>
<li>Style the heading with a different color and larger size</li>
<li>Add padding and margin to create whitespace</li>
<li>Style the lists with custom colors</li>
<li>Make the link change color on hover</li>
</ul>
</li>
<li>Use CSS classes for reusable styles</li>
<li>Ensure the page is visually appealing</li>
</ol>
<h3>Grading Criteria:</h3>
<ul>
<li>External CSS file properly linked (10 points)</li>
<li>All required styles applied (50 points)</li>
<li>Good use of colors and spacing (20 points)</li>
<li>Overall design and aesthetics (20 points)</li>
</ul>',
100.00, 70.00, DATE_ADD(NOW(), INTERVAL 14 DAY), 'published');

-- Assignment 3: Interactive Contact Form
INSERT INTO assignments (course_id, lesson_id, title, description, instructions, max_points, passing_score, due_date, status) VALUES
(@course_id, 15, 'Create an Interactive Contact Form', 'Build a contact form with JavaScript validation',
'<h3>Assignment Instructions:</h3>
<ol>
<li>Create a contact form with the following fields:
<ul>
<li>Name (text input, required)</li>
<li>Email (email input, required)</li>
<li>Subject (text input, required)</li>
<li>Message (textarea, required)</li>
<li>Submit button</li>
</ul>
</li>
<li>Add JavaScript validation that:
<ul>
<li>Checks all fields are filled</li>
<li>Validates email format</li>
<li>Shows error messages for invalid inputs</li>
<li>Prevents form submission if validation fails</li>
<li>Shows a success message on valid submission</li>
</ul>
</li>
<li>Style the form to be user-friendly and attractive</li>
</ol>
<h3>Grading Criteria:</h3>
<ul>
<li>Form structure and HTML (20 points)</li>
<li>JavaScript validation logic (40 points)</li>
<li>Error handling and messages (20 points)</li>
<li>Form styling and usability (20 points)</li>
</ul>',
100.00, 70.00, DATE_ADD(NOW(), INTERVAL 21 DAY), 'published');

-- Assignment 4: Final Portfolio Project
INSERT INTO assignments (course_id, lesson_id, title, description, instructions, max_points, passing_score, due_date, status) VALUES
(@course_id, 20, 'Build Your Portfolio Website', 'Create a complete responsive portfolio website',
'<h3>Final Project Requirements:</h3>
<ol>
<li><strong>Required Sections:</strong>
<ul>
<li>Header with navigation menu</li>
<li>Hero section with introduction</li>
<li>About me section</li>
<li>Skills section (display at least 6 skills)</li>
<li>Projects section (showcase at least 3 projects)</li>
<li>Contact form (with validation)</li>
<li>Footer with social links</li>
</ul>
</li>
<li><strong>Technical Requirements:</strong>
<ul>
<li>Fully responsive design (mobile, tablet, desktop)</li>
<li>Semantic HTML5 elements</li>
<li>External CSS with organized structure</li>
<li>JavaScript for interactivity (menu toggle, smooth scroll, form validation)</li>
<li>Clean, well-commented code</li>
</ul>
</li>
<li><strong>Design Requirements:</strong>
<ul>
<li>Professional and cohesive design</li>
<li>Consistent color scheme</li>
<li>Good typography and spacing</li>
<li>Optimized images</li>
</ul>
</li>
</ol>
<h3>Submission:</h3>
<p>Submit a ZIP file containing all HTML, CSS, JavaScript files, and images. Include a README file with:</p>
<ul>
<li>Brief description of your portfolio</li>
<li>Technologies used</li>
<li>Any special features you implemented</li>
<li>Instructions for viewing your site</li>
</ul>
<h3>Grading Criteria:</h3>
<ul>
<li>Completeness - All sections included (30 points)</li>
<li>Responsiveness - Works on all devices (20 points)</li>
<li>Code Quality - Clean, organized code (20 points)</li>
<li>Design - Professional appearance (20 points)</li>
<li>Functionality - All features work correctly (10 points)</li>
</ul>',
150.00, 70.00, DATE_ADD(NOW(), INTERVAL 30 DAY), 'published');

-- ============================================
-- COMPLETION MESSAGE
-- ============================================

SELECT '✓ Course data populated successfully!' AS result;
SELECT CONCAT('Course ID: ', @course_id) AS course_info;
SELECT CONCAT('Modules: ', COUNT(*)) AS module_count FROM course_modules WHERE course_id = @course_id;
SELECT CONCAT('Lessons: ', COUNT(*)) AS lesson_count FROM lessons WHERE course_id = @course_id;
SELECT CONCAT('Quizzes: ', COUNT(*)) AS quiz_count FROM quizzes WHERE course_id = @course_id;
SELECT CONCAT('Assignments: ', COUNT(*)) AS assignment_count FROM assignments WHERE course_id = @course_id;
