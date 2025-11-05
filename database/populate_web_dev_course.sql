-- ============================================
-- EDUTRACK LMS - Certificate in Web Development
-- Complete Course Data Population
-- ============================================
-- This script populates the course with modules, lessons, quizzes, assignments, and resources
-- Run this in phpMyAdmin after confirming the course exists

-- Get the course ID (adjust if needed)
SET @course_id = (SELECT id FROM courses WHERE slug = 'certificate-in-web-development' LIMIT 1);

-- If course doesn't exist, show error
SELECT CASE
    WHEN @course_id IS NULL THEN 'ERROR: Course not found! Please create the course first.'
    ELSE CONCAT('SUCCESS: Course ID = ', @course_id)
END AS status;

-- ============================================
-- MODULE 1: HTML Fundamentals
-- ============================================
INSERT INTO course_modules (course_id, title, description, order_index, created_at, updated_at) VALUES
(@course_id, 'HTML Fundamentals', 'Learn the building blocks of web pages with HTML. Master semantic markup, forms, and document structure.', 1, NOW(), NOW());

SET @module1_id = LAST_INSERT_ID();

-- Module 1 Lessons
INSERT INTO lessons (module_id, title, description, content, type, content_url, duration_minutes, order_index, is_preview, created_at, updated_at) VALUES
(@module1_id, 'Introduction to HTML', 'Understanding HTML and its role in web development',
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
'video', 'https://www.youtube.com/embed/qz0aGYrrlhU', 15, 1, 1, NOW(), NOW()),

(@module1_id, 'HTML Document Structure', 'Learn about the basic structure of an HTML document',
'<h3>Basic HTML Document Structure</h3>
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;My First Web Page&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;h1&gt;Welcome to My Website&lt;/h1&gt;
    &lt;p&gt;This is a paragraph.&lt;/p&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>
<h3>Key Elements:</h3>
<ul>
<li><strong>&lt;!DOCTYPE html&gt;</strong> - Document type declaration</li>
<li><strong>&lt;html&gt;</strong> - Root element</li>
<li><strong>&lt;head&gt;</strong> - Metadata container</li>
<li><strong>&lt;body&gt;</strong> - Visible content</li>
</ul>',
'video', 'https://www.youtube.com/embed/UB1O30fR-EE', 20, 2, 1, NOW(), NOW()),

(@module1_id, 'HTML Text Elements', 'Working with headings, paragraphs, and text formatting',
'<h3>Text Elements in HTML</h3>
<p>HTML provides various elements for structuring and formatting text content.</p>
<h4>Headings:</h4>
<pre><code>&lt;h1&gt;Main Heading&lt;/h1&gt;
&lt;h2&gt;Sub Heading&lt;/h2&gt;
&lt;h3&gt;Section Heading&lt;/h3&gt;</code></pre>
<h4>Text Formatting:</h4>
<ul>
<li><strong>&lt;strong&gt;</strong> - Important text (bold)</li>
<li><strong>&lt;em&gt;</strong> - Emphasized text (italic)</li>
<li><strong>&lt;mark&gt;</strong> - Highlighted text</li>
<li><strong>&lt;small&gt;</strong> - Smaller text</li>
</ul>',
'video', 'https://www.youtube.com/embed/MDLn5-zSQQI', 25, 3, 0, NOW(), NOW()),

(@module1_id, 'HTML Lists and Links', 'Creating lists and hyperlinks',
'<h3>HTML Lists</h3>
<h4>Unordered List:</h4>
<pre><code>&lt;ul&gt;
    &lt;li&gt;Item 1&lt;/li&gt;
    &lt;li&gt;Item 2&lt;/li&gt;
&lt;/ul&gt;</code></pre>
<h4>Ordered List:</h4>
<pre><code>&lt;ol&gt;
    &lt;li&gt;First&lt;/li&gt;
    &lt;li&gt;Second&lt;/li&gt;
&lt;/ol&gt;</code></pre>
<h3>Hyperlinks</h3>
<pre><code>&lt;a href="https://example.com"&gt;Visit Example&lt;/a&gt;
&lt;a href="#section"&gt;Jump to Section&lt;/a&gt;
&lt;a href="mailto:email@example.com"&gt;Email Us&lt;/a&gt;</code></pre>',
'video', 'https://www.youtube.com/embed/2T1JofmYR0k', 20, 4, 0, NOW(), NOW()),

(@module1_id, 'HTML Forms Basics', 'Introduction to HTML forms and input elements',
'<h3>HTML Forms</h3>
<p>Forms allow users to input data that can be sent to a server for processing.</p>
<pre><code>&lt;form action="/submit" method="POST"&gt;
    &lt;label for="name"&gt;Name:&lt;/label&gt;
    &lt;input type="text" id="name" name="name" required&gt;

    &lt;label for="email"&gt;Email:&lt;/label&gt;
    &lt;input type="email" id="email" name="email" required&gt;

    &lt;button type="submit"&gt;Submit&lt;/button&gt;
&lt;/form&gt;</code></pre>
<h4>Common Input Types:</h4>
<ul>
<li>text, email, password</li>
<li>number, tel, url</li>
<li>checkbox, radio</li>
<li>date, time, file</li>
</ul>',
'video', 'https://www.youtube.com/embed/fNcJuPIZ2WE', 30, 5, 0, NOW(), NOW());

-- ============================================
-- MODULE 2: CSS Styling
-- ============================================
INSERT INTO course_modules (course_id, title, description, order_index, created_at, updated_at) VALUES
(@course_id, 'CSS Styling', 'Master CSS to style and layout your web pages. Learn selectors, properties, flexbox, and responsive design.', 2, NOW(), NOW());

SET @module2_id = LAST_INSERT_ID();

-- Module 2 Lessons
INSERT INTO lessons (module_id, title, description, content, type, content_url, duration_minutes, order_index, is_preview, created_at, updated_at) VALUES
(@module2_id, 'Introduction to CSS', 'Understanding CSS and how it styles HTML',
'<h3>What is CSS?</h3>
<p>CSS (Cascading Style Sheets) is used to style and layout web pages. It controls colors, fonts, spacing, and positioning.</p>
<h3>Three Ways to Add CSS:</h3>
<ol>
<li><strong>Inline CSS:</strong> <code>style="color: red;"</code></li>
<li><strong>Internal CSS:</strong> <code>&lt;style&gt;</code> tag in head</li>
<li><strong>External CSS:</strong> Separate .css file (recommended)</li>
</ol>
<pre><code>&lt;link rel="stylesheet" href="styles.css"&gt;</code></pre>',
'video', 'https://www.youtube.com/embed/yfoY53QXEnI', 18, 1, 0, NOW(), NOW()),

(@module2_id, 'CSS Selectors', 'Learn different types of CSS selectors',
'<h3>CSS Selectors</h3>
<h4>Basic Selectors:</h4>
<pre><code>/* Element selector */
p { color: blue; }

/* Class selector */
.highlight { background: yellow; }

/* ID selector */
#header { font-size: 24px; }

/* Universal selector */
* { margin: 0; padding: 0; }</code></pre>
<h4>Combinators:</h4>
<ul>
<li>Descendant: <code>div p</code></li>
<li>Child: <code>div > p</code></li>
<li>Adjacent sibling: <code>h1 + p</code></li>
</ul>',
'video', 'https://www.youtube.com/embed/l1mER1bV0N0', 25, 2, 0, NOW(), NOW()),

(@module2_id, 'CSS Box Model', 'Understanding margin, padding, border, and content',
'<h3>The CSS Box Model</h3>
<p>Every HTML element is a box consisting of:</p>
<ul>
<li><strong>Content</strong> - The actual content (text, images)</li>
<li><strong>Padding</strong> - Space around content (inside border)</li>
<li><strong>Border</strong> - Border around padding</li>
<li><strong>Margin</strong> - Space outside border</li>
</ul>
<pre><code>.box {
    width: 300px;
    padding: 20px;
    border: 2px solid black;
    margin: 10px;
}</code></pre>',
'video', 'https://www.youtube.com/embed/rIO5326FgPE', 22, 3, 0, NOW(), NOW()),

(@module2_id, 'Flexbox Layout', 'Modern CSS layout with Flexbox',
'<h3>CSS Flexbox</h3>
<p>Flexbox is a powerful layout system for creating responsive designs.</p>
<pre><code>.container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.item {
    flex: 1;
}</code></pre>
<h4>Key Properties:</h4>
<ul>
<li><code>display: flex</code> - Enable flexbox</li>
<li><code>flex-direction</code> - row or column</li>
<li><code>justify-content</code> - Horizontal alignment</li>
<li><code>align-items</code> - Vertical alignment</li>
</ul>',
'video', 'https://www.youtube.com/embed/fYq5PXgSsbE', 30, 4, 0, NOW(), NOW()),

(@module2_id, 'Responsive Design', 'Making websites work on all devices',
'<h3>Responsive Web Design</h3>
<p>Create websites that adapt to different screen sizes.</p>
<h4>Media Queries:</h4>
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
}</code></pre>',
'video', 'https://www.youtube.com/embed/srvUrASNj0s', 28, 5, 0, NOW(), NOW());

-- ============================================
-- MODULE 3: JavaScript Fundamentals
-- ============================================
INSERT INTO course_modules (course_id, title, description, order_index, created_at, updated_at) VALUES
(@course_id, 'JavaScript Fundamentals', 'Learn programming with JavaScript. Master variables, functions, DOM manipulation, and events.', 3, NOW(), NOW());

SET @module3_id = LAST_INSERT_ID();

-- Module 3 Lessons
INSERT INTO lessons (module_id, title, description, content, type, content_url, duration_minutes, order_index, is_preview, created_at, updated_at) VALUES
(@module3_id, 'Introduction to JavaScript', 'Understanding JavaScript and its role in web development',
'<h3>What is JavaScript?</h3>
<p>JavaScript is a programming language that adds interactivity and dynamic behavior to websites.</p>
<h3>What Can JavaScript Do?</h3>
<ul>
<li>Respond to user interactions (clicks, typing)</li>
<li>Manipulate HTML and CSS dynamically</li>
<li>Validate form inputs</li>
<li>Make API calls and fetch data</li>
<li>Create animations and effects</li>
</ul>
<pre><code>// Your first JavaScript
console.log("Hello, World!");
alert("Welcome to JavaScript!");</code></pre>',
'video', 'https://www.youtube.com/embed/W6NZfCO5SIk', 20, 1, 0, NOW(), NOW()),

(@module3_id, 'Variables and Data Types', 'Working with variables and different data types',
'<h3>JavaScript Variables</h3>
<pre><code>// Variable declarations
let name = "John";
const age = 25;
var city = "Lusaka"; // old way

// Data Types
let string = "Hello";
let number = 42;
let boolean = true;
let array = [1, 2, 3];
let object = { name: "John", age: 25 };
let nullValue = null;
let undefinedValue;</code></pre>
<h4>Naming Rules:</h4>
<ul>
<li>Start with letter, _, or $</li>
<li>Case-sensitive</li>
<li>Use camelCase convention</li>
</ul>',
'video', 'https://www.youtube.com/embed/edlFjlzxkSI', 25, 2, 0, NOW(), NOW()),

(@module3_id, 'Functions in JavaScript', 'Creating and using functions',
'<h3>JavaScript Functions</h3>
<h4>Function Declaration:</h4>
<pre><code>function greet(name) {
    return "Hello, " + name + "!";
}

console.log(greet("Sarah")); // Hello, Sarah!</code></pre>
<h4>Arrow Functions (Modern):</h4>
<pre><code>const add = (a, b) => a + b;
const multiply = (x, y) => {
    return x * y;
};

console.log(add(5, 3)); // 8</code></pre>',
'video', 'https://www.youtube.com/embed/N8ap4k_1QEQ', 28, 3, 0, NOW(), NOW()),

(@module3_id, 'DOM Manipulation', 'Interacting with HTML using JavaScript',
'<h3>Document Object Model (DOM)</h3>
<p>The DOM is a programming interface for HTML documents.</p>
<pre><code>// Selecting elements
const heading = document.getElementById("title");
const buttons = document.querySelectorAll(".btn");

// Changing content
heading.textContent = "New Title";
heading.innerHTML = "&lt;strong&gt;Bold Title&lt;/strong&gt;";

// Changing styles
heading.style.color = "blue";
heading.style.fontSize = "32px";

// Adding classes
heading.classList.add("highlight");
heading.classList.remove("old-class");
heading.classList.toggle("active");</code></pre>',
'video', 'https://www.youtube.com/embed/5fb2aPlgoys', 30, 4, 0, NOW(), NOW()),

(@module3_id, 'Event Handling', 'Responding to user interactions',
'<h3>JavaScript Events</h3>
<p>Events allow JavaScript to react to user actions.</p>
<pre><code>// Click event
document.getElementById("btn").addEventListener("click", function() {
    alert("Button clicked!");
});

// Form submit
document.getElementById("form").addEventListener("submit", (e) => {
    e.preventDefault();
    console.log("Form submitted");
});

// Input change
document.getElementById("input").addEventListener("input", (e) => {
    console.log(e.target.value);
});</code></pre>
<h4>Common Events:</h4>
<ul>
<li>click, dblclick</li>
<li>submit, change, input</li>
<li>mouseover, mouseout</li>
<li>keydown, keyup, keypress</li>
</ul>',
'video', 'https://www.youtube.com/embed/XF1_MlZ5l6M', 32, 5, 0, NOW(), NOW());

-- ============================================
-- MODULE 4: Responsive Web Design Project
-- ============================================
INSERT INTO course_modules (course_id, title, description, order_index, created_at, updated_at) VALUES
(@course_id, 'Responsive Web Design Project', 'Apply your HTML, CSS, and JavaScript skills to build a complete responsive website.', 4, NOW(), NOW());

SET @module4_id = LAST_INSERT_ID();

-- Module 4 Lessons
INSERT INTO lessons (module_id, title, description, content, type, content_url, duration_minutes, order_index, is_preview, created_at, updated_at) VALUES
(@module4_id, 'Project Planning', 'Planning your website project',
'<h3>Project: Personal Portfolio Website</h3>
<p>Build a responsive portfolio website showcasing your skills.</p>
<h4>Features to Include:</h4>
<ul>
<li>Hero section with introduction</li>
<li>About section</li>
<li>Skills/Services section</li>
<li>Portfolio/Projects gallery</li>
<li>Contact form</li>
<li>Responsive navigation</li>
</ul>
<h4>Planning Steps:</h4>
<ol>
<li>Sketch wireframes</li>
<li>Choose color scheme</li>
<li>Gather content (text, images)</li>
<li>Plan page structure</li>
</ol>',
'text', NULL, 15, 1, 0, NOW(), NOW()),

(@module4_id, 'Building the HTML Structure', 'Creating semantic HTML structure',
'<h3>HTML Structure</h3>
<p>Create a well-structured HTML foundation for your portfolio.</p>
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;My Portfolio&lt;/title&gt;
    &lt;link rel="stylesheet" href="style.css"&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;nav&gt;&lt;!-- Navigation --&gt;&lt;/nav&gt;
    &lt;header&gt;&lt;!-- Hero section --&gt;&lt;/header&gt;
    &lt;section id="about"&gt;&lt;!-- About --&gt;&lt;/section&gt;
    &lt;section id="skills"&gt;&lt;!-- Skills --&gt;&lt;/section&gt;
    &lt;section id="projects"&gt;&lt;!-- Projects --&gt;&lt;/section&gt;
    &lt;section id="contact"&gt;&lt;!-- Contact --&gt;&lt;/section&gt;
    &lt;footer&gt;&lt;!-- Footer --&gt;&lt;/footer&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>',
'video', 'https://www.youtube.com/embed/ldwlOzRvYOU', 35, 2, 0, NOW(), NOW()),

(@module4_id, 'Styling with CSS', 'Adding styles and layout',
'<h3>CSS Styling</h3>
<p>Make your portfolio visually appealing with CSS.</p>
<h4>Key Techniques:</h4>
<ul>
<li>CSS Variables for color scheme</li>
<li>Flexbox for layout</li>
<li>Grid for project gallery</li>
<li>Smooth scrolling</li>
<li>Hover effects and transitions</li>
</ul>
<pre><code>:root {
    --primary-color: #2E70DA;
    --secondary-color: #F6B745;
    --dark: #1a1a1a;
    --light: #f5f5f5;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: "Arial", sans-serif;
    line-height: 1.6;
}</code></pre>',
'video', 'https://www.youtube.com/embed/sXnFmIYUFl4', 40, 3, 0, NOW(), NOW()),

(@module4_id, 'Adding Interactivity', 'JavaScript functionality',
'<h3>JavaScript Interactivity</h3>
<p>Add dynamic features to enhance user experience.</p>
<h4>Features to Implement:</h4>
<ul>
<li>Mobile menu toggle</li>
<li>Smooth scroll to sections</li>
<li>Form validation</li>
<li>Project filtering</li>
<li>Scroll animations</li>
</ul>
<pre><code>// Mobile menu toggle
const menuBtn = document.getElementById("menu-toggle");
const nav = document.querySelector("nav");

menuBtn.addEventListener("click", () => {
    nav.classList.toggle("active");
});

// Smooth scroll
document.querySelectorAll(\'a[href^="#"]\').forEach(anchor => {
    anchor.addEventListener("click", function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute("href"));
        target.scrollIntoView({ behavior: "smooth" });
    });
});</code></pre>',
'video', 'https://www.youtube.com/embed/3PHXvlpOkf4', 35, 4, 0, NOW(), NOW()),

(@module4_id, 'Testing and Deployment', 'Testing and publishing your website',
'<h3>Testing Your Website</h3>
<h4>Testing Checklist:</h4>
<ul>
<li>Test on different browsers (Chrome, Firefox, Safari)</li>
<li>Test on different devices (mobile, tablet, desktop)</li>
<li>Check all links work</li>
<li>Validate HTML and CSS</li>
<li>Test form submission</li>
<li>Check loading speed</li>
</ul>
<h3>Deployment Options</h3>
<ol>
<li><strong>GitHub Pages</strong> - Free hosting for static sites</li>
<li><strong>Netlify</strong> - Easy deployment with continuous integration</li>
<li><strong>Vercel</strong> - Modern hosting platform</li>
</ol>
<p>Congratulations! You have completed your first web development project!</p>',
'video', 'https://www.youtube.com/embed/QyFcl_Fba-k', 25, 5, 0, NOW(), NOW());

-- ============================================
-- QUIZZES
-- ============================================

-- Quiz 1: HTML Fundamentals Quiz
INSERT INTO quizzes (course_id, title, description, passing_score, time_limit, max_attempts, status, created_at, updated_at) VALUES
(@course_id, 'HTML Fundamentals Quiz', 'Test your knowledge of HTML basics', 70, 20, 3, 'published', NOW(), NOW());

SET @quiz1_id = LAST_INSERT_ID();

-- Quiz 1 Questions
INSERT INTO quiz_questions (quiz_id, question, question_type, points, order_index, created_at, updated_at) VALUES
(@quiz1_id, 'What does HTML stand for?', 'multiple_choice', 5, 1, NOW(), NOW()),
(@quiz1_id, 'Which HTML element is used for the largest heading?', 'multiple_choice', 5, 2, NOW(), NOW()),
(@quiz1_id, 'What is the correct HTML element for inserting a line break?', 'multiple_choice', 5, 3, NOW(), NOW()),
(@quiz1_id, 'Which attribute is used to provide alternative text for an image?', 'multiple_choice', 5, 4, NOW(), NOW()),
(@quiz1_id, 'What is the purpose of the <head> element in HTML?', 'multiple_choice', 5, 5, NOW(), NOW());

-- Quiz 1 Answers
SET @q1 = (SELECT id FROM quiz_questions WHERE quiz_id = @quiz1_id ORDER BY order_index LIMIT 0,1);
SET @q2 = (SELECT id FROM quiz_questions WHERE quiz_id = @quiz1_id ORDER BY order_index LIMIT 1,1);
SET @q3 = (SELECT id FROM quiz_questions WHERE quiz_id = @quiz1_id ORDER BY order_index LIMIT 2,1);
SET @q4 = (SELECT id FROM quiz_questions WHERE quiz_id = @quiz1_id ORDER BY order_index LIMIT 3,1);
SET @q5 = (SELECT id FROM quiz_questions WHERE quiz_id = @quiz1_id ORDER BY order_index LIMIT 4,1);

INSERT INTO quiz_answers (question_id, answer, is_correct, order_index, created_at, updated_at) VALUES
(@q1, 'HyperText Markup Language', 1, 1, NOW(), NOW()),
(@q1, 'HighText Machine Language', 0, 2, NOW(), NOW()),
(@q1, 'Home Tool Markup Language', 0, 3, NOW(), NOW()),
(@q1, 'Hyperlinks and Text Markup Language', 0, 4, NOW(), NOW()),

(@q2, '<h1>', 1, 1, NOW(), NOW()),
(@q2, '<heading>', 0, 2, NOW(), NOW()),
(@q2, '<h6>', 0, 3, NOW(), NOW()),
(@q2, '<head>', 0, 4, NOW(), NOW()),

(@q3, '<br>', 1, 1, NOW(), NOW()),
(@q3, '<break>', 0, 2, NOW(), NOW()),
(@q3, '<lb>', 0, 3, NOW(), NOW()),
(@q3, '<newline>', 0, 4, NOW(), NOW()),

(@q4, 'alt', 1, 1, NOW(), NOW()),
(@q4, 'title', 0, 2, NOW(), NOW()),
(@q4, 'src', 0, 3, NOW(), NOW()),
(@q4, 'desc', 0, 4, NOW(), NOW()),

(@q5, 'To contain metadata about the document', 1, 1, NOW(), NOW()),
(@q5, 'To display the main content', 0, 2, NOW(), NOW()),
(@q5, 'To create navigation links', 0, 3, NOW(), NOW()),
(@q5, 'To add scripts', 0, 4, NOW(), NOW());

-- Quiz 2: CSS Styling Quiz
INSERT INTO quizzes (course_id, title, description, passing_score, time_limit, max_attempts, status, created_at, updated_at) VALUES
(@course_id, 'CSS Styling Quiz', 'Test your CSS knowledge', 70, 20, 3, 'published', NOW(), NOW());

SET @quiz2_id = LAST_INSERT_ID();

-- Quiz 2 Questions
INSERT INTO quiz_questions (quiz_id, question, question_type, points, order_index, created_at, updated_at) VALUES
(@quiz2_id, 'What does CSS stand for?', 'multiple_choice', 5, 1, NOW(), NOW()),
(@quiz2_id, 'Which property is used to change the background color?', 'multiple_choice', 5, 2, NOW(), NOW()),
(@quiz2_id, 'How do you select an element with id "header" in CSS?', 'multiple_choice', 5, 3, NOW(), NOW()),
(@quiz2_id, 'Which CSS property controls the text size?', 'multiple_choice', 5, 4, NOW(), NOW()),
(@quiz2_id, 'What is the correct CSS syntax for making all <p> elements bold?', 'multiple_choice', 5, 5, NOW(), NOW());

-- ============================================
-- ASSIGNMENTS
-- ============================================

-- Assignment 1: HTML Structure Assignment
INSERT INTO assignments (course_id, title, description, instructions, points, due_days, status, created_at, updated_at) VALUES
(@course_id, 'Create a Personal Bio Page',
'Build a simple HTML page with your personal information',
'<h3>Assignment Instructions:</h3>
<p>Create an HTML page that includes the following:</p>
<ol>
<li>A proper HTML5 document structure with DOCTYPE, html, head, and body tags</li>
<li>A heading with your name (use h1 tag)</li>
<li>A paragraph describing yourself and your interests</li>
<li>An unordered list of your hobbies</li>
<li>An ordered list of your educational background</li>
<li>At least 2 external links to your social media or websites</li>
<li>An email link to your email address</li>
</ol>
<h3>Submission Requirements:</h3>
<ul>
<li>Submit your HTML file named "bio.html"</li>
<li>Ensure your code is properly indented and commented</li>
<li>Test your page in a browser before submitting</li>
</ul>
<h3>Grading Criteria:</h3>
<ul>
<li>Proper HTML structure (30 points)</li>
<li>All required elements present (40 points)</li>
<li>Code quality and organization (20 points)</li>
<li>Creativity and content (10 points)</li>
</ul>',
100, 7, 'published', NOW(), NOW());

-- Assignment 2: CSS Styling Assignment
INSERT INTO assignments (course_id, title, description, instructions, points, due_days, status, created_at, updated_at) VALUES
(@course_id, 'Style Your Bio Page with CSS',
'Add CSS styling to your HTML bio page',
'<h3>Assignment Instructions:</h3>
<p>Take your bio.html page from the previous assignment and style it with CSS:</p>
<ol>
<li>Create an external CSS file named "style.css"</li>
<li>Link the CSS file to your HTML page</li>
<li>Apply the following styles:
    <ul>
    <li>Set a background color for the page</li>
    <li>Choose a custom font family</li>
    <li>Style your heading with a different color and font size</li>
    <li>Add padding and margin to your content</li>
    <li>Style your lists with custom list markers</li>
    <li>Add hover effects to your links</li>
    <li>Center your content on the page</li>
    </ul>
</li>
<li>Use at least 3 different types of CSS selectors (element, class, ID)</li>
</ol>
<h3>Bonus Challenge (10 extra points):</h3>
<ul>
<li>Make your page responsive using media queries</li>
<li>Add CSS transitions or animations</li>
</ul>',
100, 7, 'published', NOW(), NOW());

-- Assignment 3: JavaScript Interactive Assignment
INSERT INTO assignments (course_id, title, description, instructions, points, due_days, status, created_at, updated_at) VALUES
(@course_id, 'Add Interactivity with JavaScript',
'Create an interactive contact form with JavaScript validation',
'<h3>Assignment Instructions:</h3>
<p>Build a contact form with JavaScript validation:</p>
<ol>
<li>Create an HTML form with the following fields:
    <ul>
    <li>Name (required)</li>
    <li>Email (required, must be valid email)</li>
    <li>Phone number (optional)</li>
    <li>Message (required, minimum 20 characters)</li>
    <li>Submit button</li>
    </ul>
</li>
<li>Write JavaScript to validate the form:
    <ul>
    <li>Prevent form submission if fields are invalid</li>
    <li>Show error messages for invalid fields</li>
    <li>Email must contain @ and .</li>
    <li>Message must be at least 20 characters</li>
    <li>Show success message when form is valid</li>
    </ul>
</li>
<li>Add event listeners for form submission and input validation</li>
<li>Style the form with CSS</li>
</ol>
<h3>Files to Submit:</h3>
<ul>
<li>contact.html</li>
<li>style.css</li>
<li>script.js</li>
</ul>',
150, 10, 'published', NOW(), NOW());

-- Assignment 4: Final Project
INSERT INTO assignments (course_id, title, description, instructions, points, due_days, status, created_at, updated_at) VALUES
(@course_id, 'Build a Complete Portfolio Website',
'Create a fully responsive portfolio website showcasing all your skills',
'<h3>Final Project: Portfolio Website</h3>
<p>Build a complete, responsive portfolio website that demonstrates everything you have learned.</p>

<h3>Required Sections:</h3>
<ol>
<li><strong>Navigation Bar</strong> - Sticky header with links to all sections</li>
<li><strong>Hero Section</strong> - Welcome message with your name and title</li>
<li><strong>About Section</strong> - Information about yourself</li>
<li><strong>Skills Section</strong> - Display your technical skills</li>
<li><strong>Projects Section</strong> - Showcase at least 3 projects with images</li>
<li><strong>Contact Section</strong> - Contact form with validation</li>
<li><strong>Footer</strong> - Copyright and social media links</li>
</ol>

<h3>Technical Requirements:</h3>
<ul>
<li>Semantic HTML5 elements</li>
<li>External CSS file with organized styles</li>
<li>Responsive design (mobile, tablet, desktop)</li>
<li>JavaScript for interactivity (menu, smooth scroll, form validation)</li>
<li>Clean, well-commented code</li>
<li>Cross-browser compatibility</li>
</ul>

<h3>Grading Breakdown (200 points):</h3>
<ul>
<li>HTML Structure & Semantics: 40 points</li>
<li>CSS Styling & Design: 50 points</li>
<li>Responsive Design: 40 points</li>
<li>JavaScript Functionality: 40 points</li>
<li>Code Quality: 20 points</li>
<li>Creativity & Polish: 10 points</li>
</ul>

<h3>Submission:</h3>
<p>Submit a ZIP file containing all your project files, or provide a link to your hosted website (GitHub Pages, Netlify, etc.)</p>',
200, 14, 'published', NOW(), NOW());

-- ============================================
-- COURSE RESOURCES
-- ============================================

-- Note: This table may not exist, check your schema
-- If resources table exists, uncomment below:

/*
INSERT INTO course_resources (course_id, title, description, type, file_url, created_at, updated_at) VALUES
(@course_id, 'HTML Cheat Sheet', 'Quick reference for HTML elements and attributes', 'pdf', 'https://htmlcheatsheet.com/pdf/html-cheat-sheet.pdf', NOW(), NOW()),
(@course_id, 'CSS Properties Reference', 'Complete guide to CSS properties', 'pdf', 'https://cssreference.io', NOW(), NOW()),
(@course_id, 'JavaScript Basics Handbook', 'Comprehensive JavaScript reference guide', 'pdf', 'https://javascript.info', NOW(), NOW()),
(@course_id, 'Web Development Tools', 'List of essential tools for web developers', 'link', 'https://developer.mozilla.org/en-US/docs/Learn/Common_questions/Tools_and_setup/What_software_do_I_need', NOW(), NOW()),
(@course_id, 'Color Picker Tool', 'Tool for choosing color schemes', 'link', 'https://coolors.co', NOW(), NOW()),
(@course_id, 'Free Images Repository', 'Free stock photos for your projects', 'link', 'https://unsplash.com', NOW(), NOW()),
(@course_id, 'Font Awesome Icons', 'Free icon library', 'link', 'https://fontawesome.com', NOW(), NOW()),
(@course_id, 'VS Code Setup Guide', 'How to set up your development environment', 'video', 'https://www.youtube.com/embed/fnPhJHN0jTE', NOW(), NOW());
*/

-- ============================================
-- VERIFICATION QUERIES
-- ============================================

-- Check what was created
SELECT 'Modules Created:' AS info, COUNT(*) AS count FROM course_modules WHERE course_id = @course_id
UNION ALL
SELECT 'Lessons Created:', COUNT(*) FROM lessons l JOIN course_modules m ON l.module_id = m.id WHERE m.course_id = @course_id
UNION ALL
SELECT 'Quizzes Created:', COUNT(*) FROM quizzes WHERE course_id = @course_id
UNION ALL
SELECT 'Quiz Questions:', COUNT(*) FROM quiz_questions qq JOIN quizzes q ON qq.quiz_id = q.id WHERE q.course_id = @course_id
UNION ALL
SELECT 'Assignments Created:', COUNT(*) FROM assignments WHERE course_id = @course_id;

-- Success message
SELECT '✓ Course data populated successfully!' AS result,
       'You can now test the full student learning experience!' AS message;
