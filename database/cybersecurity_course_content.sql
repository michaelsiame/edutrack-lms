-- ============================================
-- Cybersecurity Course Content for Edutrack LMS
-- ============================================
-- RUN THIS ENTIRE FILE AT ONCE in phpMyAdmin or mysql CLI
-- It uses a transaction so if anything fails, nothing is committed.

-- Ensure all tables have AUTO_INCREMENT before inserting
-- (some production databases may be missing these from incomplete migrations)
ALTER TABLE `course_categories` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `courses` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `modules` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `lessons` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `quizzes` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `questions` MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `question_options` MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `quiz_questions` MODIFY `quiz_question_id` int(11) NOT NULL AUTO_INCREMENT;

START TRANSACTION;

-- ============================================
-- 1. CATEGORY
-- ============================================
INSERT INTO `course_categories` (`id`, `name`, `category_description`, `parent_category_id`, `icon_url`, `display_order`, `is_active`, `created_at`, `updated_at`, `color`)
VALUES (NULL, 'Cybersecurity', 'Learn to protect systems, networks, and data from cyber threats', NULL, 'fa-shield-alt', 4, 1, NOW(), NOW(), '#DC2626')
ON DUPLICATE KEY UPDATE `id` = LAST_INSERT_ID(`id`);
SET @cat_id = LAST_INSERT_ID();

-- ============================================
-- 2. COURSE
-- ============================================
INSERT INTO `courses` (`id`, `title`, `slug`, `description`, `short_description`, `category_id`, `instructor_id`, `level`, `language`, `thumbnail_url`, `video_intro_url`, `start_date`, `end_date`, `price`, `discount_price`, `duration_weeks`, `total_hours`, `max_students`, `enrollment_count`, `status`, `is_featured`, `rating`, `total_reviews`, `prerequisites`, `learning_outcomes`, `created_at`, `updated_at`)
VALUES (NULL, 'Cybersecurity Fundamentals', 'cybersecurity-fundamentals',
'<p>This comprehensive cybersecurity course prepares you for entry-level roles in the rapidly growing field of cybersecurity. You will learn fundamental concepts, network security, threat detection, ethical hacking basics, and security operations.</p><p>By the end of this course, you will understand how to protect systems, detect threats, and respond to security incidents using industry-standard tools and frameworks.</p>',
'Master cybersecurity fundamentals and protect digital assets from cyber threats',
@cat_id, 1, 'Beginner', 'English', 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=800', NULL, NULL, NULL, 4500.00, NULL, 12, 96.00, 30, 0, 'published', 1, 0.00, 0,
'Basic computer literacy, Understanding of operating systems (Windows/Linux)',
'Understand cybersecurity principles and the threat landscape|Identify and mitigate common network vulnerabilities|Implement security controls and defense strategies|Detect and respond to security incidents|Understand ethical hacking basics|Apply security frameworks like NIST',
NOW(), NOW());
SET @c_id = LAST_INSERT_ID();

-- ============================================
-- 3. MODULES (insert one at a time to capture IDs)
-- ============================================
INSERT INTO `modules` (`id`, `course_id`, `title`, `description`, `display_order`, `duration_minutes`, `is_published`, `unlock_date`, `created_at`, `updated_at`)
VALUES (NULL, @c_id, 'Module 1: Introduction to Cybersecurity', 'Understanding the cybersecurity landscape, key concepts, and career paths', 1, 225, 1, NULL, NOW(), NOW());
SET @m1 = LAST_INSERT_ID();

INSERT INTO `modules` (`id`, `course_id`, `title`, `description`, `display_order`, `duration_minutes`, `is_published`, `unlock_date`, `created_at`, `updated_at`)
VALUES (NULL, @c_id, 'Module 2: Networking Fundamentals', 'OSI model, TCP/IP, network protocols, and basic network security', 2, 300, 1, NULL, NOW(), NOW());
SET @m2 = LAST_INSERT_ID();

INSERT INTO `modules` (`id`, `course_id`, `title`, `description`, `display_order`, `duration_minutes`, `is_published`, `unlock_date`, `created_at`, `updated_at`)
VALUES (NULL, @c_id, 'Module 3: Cyber Threats and Attacks', 'Types of malware, attack vectors, social engineering, and threat actors', 3, 285, 1, NULL, NOW(), NOW());
SET @m3 = LAST_INSERT_ID();

INSERT INTO `modules` (`id`, `course_id`, `title`, `description`, `display_order`, `duration_minutes`, `is_published`, `unlock_date`, `created_at`, `updated_at`)
VALUES (NULL, @c_id, 'Module 4: Security Controls and Defense', 'Firewalls, IDS/IPS, encryption, access control, and defense strategies', 4, 330, 1, NULL, NOW(), NOW());
SET @m4 = LAST_INSERT_ID();

INSERT INTO `modules` (`id`, `course_id`, `title`, `description`, `display_order`, `duration_minutes`, `is_published`, `unlock_date`, `created_at`, `updated_at`)
VALUES (NULL, @c_id, 'Module 5: Web Application Security', 'OWASP Top 10, secure coding, and web vulnerability assessment', 5, 345, 1, NULL, NOW(), NOW());
SET @m5 = LAST_INSERT_ID();

INSERT INTO `modules` (`id`, `course_id`, `title`, `description`, `display_order`, `duration_minutes`, `is_published`, `unlock_date`, `created_at`, `updated_at`)
VALUES (NULL, @c_id, 'Module 6: Ethical Hacking Basics', 'Penetration testing methodology, reconnaissance, and vulnerability scanning', 6, 315, 1, NULL, NOW(), NOW());
SET @m6 = LAST_INSERT_ID();

INSERT INTO `modules` (`id`, `course_id`, `title`, `description`, `display_order`, `duration_minutes`, `is_published`, `unlock_date`, `created_at`, `updated_at`)
VALUES (NULL, @c_id, 'Module 7: Incident Response and Forensics', 'Incident handling process, digital forensics fundamentals, and reporting', 7, 285, 1, NULL, NOW(), NOW());
SET @m7 = LAST_INSERT_ID();

INSERT INTO `modules` (`id`, `course_id`, `title`, `description`, `display_order`, `duration_minutes`, `is_published`, `unlock_date`, `created_at`, `updated_at`)
VALUES (NULL, @c_id, 'Module 8: Security Operations (SOC)', 'SIEM tools, log analysis, monitoring, and SOC workflows', 8, 285, 1, NULL, NOW(), NOW());
SET @m8 = LAST_INSERT_ID();

INSERT INTO `modules` (`id`, `course_id`, `title`, `description`, `display_order`, `duration_minutes`, `is_published`, `unlock_date`, `created_at`, `updated_at`)
VALUES (NULL, @c_id, 'Module 9: Governance, Risk, and Compliance', 'Security frameworks, risk management, and compliance standards', 9, 255, 1, NULL, NOW(), NOW());
SET @m9 = LAST_INSERT_ID();

INSERT INTO `modules` (`id`, `course_id`, `title`, `description`, `display_order`, `duration_minutes`, `is_published`, `unlock_date`, `created_at`, `updated_at`)
VALUES (NULL, @c_id, 'Module 10: Capstone Project', 'Apply your skills to a real-world cybersecurity scenario', 10, 135, 1, NULL, NOW(), NOW());
SET @m10 = LAST_INSERT_ID();

-- ============================================
-- 4. LESSONS (multi-row insert, no ID capture needed)
-- ============================================
INSERT INTO `lessons` (`id`, `module_id`, `title`, `content`, `lesson_type`, `duration_minutes`, `display_order`, `video_url`, `video_duration`, `is_preview`, `is_mandatory`, `points`, `created_at`, `updated_at`) VALUES
(NULL, @m1, 'What is Cybersecurity?',
'<h2>What is Cybersecurity?</h2><p>Cybersecurity is the practice of protecting systems, networks, and programs from digital attacks.</p><h3>The CIA Triad</h3><ul><li><strong>Confidentiality:</strong> Ensuring information is accessible only to authorized users</li><li><strong>Integrity:</strong> Maintaining the accuracy and completeness of data</li><li><strong>Availability:</strong> Ensuring systems and data are accessible when needed</li></ul><h3>Key Terminology</h3><ul><li><strong>Asset:</strong> Anything of value to an organization</li><li><strong>Vulnerability:</strong> A weakness that could be exploited</li><li><strong>Threat:</strong> A potential danger to an asset</li><li><strong>Risk:</strong> The likelihood and impact of a threat exploiting a vulnerability</li></ul>',
'Video', 30, 1, NULL, NULL, 1, 1, 10, NOW(), NOW()),

(NULL, @m1, 'The Cyber Threat Landscape',
'<h2>The Cyber Threat Landscape</h2><h3>Types of Threat Actors</h3><ul><li><strong>Script Kiddies:</strong> Unskilled attackers using existing tools</li><li><strong>Hacktivists:</strong> Attackers motivated by political or social causes</li><li><strong>Cybercriminals:</strong> Organized groups seeking financial gain</li><li><strong>State-Sponsored Actors:</strong> Nation-state backed attackers</li><li><strong>Insider Threats:</strong> Malicious or negligent employees</li></ul><h3>Statistics in Zambia and Africa</h3><p>Africa is experiencing rapid digital transformation, making cybersecurity increasingly important.</p>',
'Video', 25, 2, NULL, NULL, 0, 1, 10, NOW(), NOW()),

(NULL, @m1, 'Cybersecurity Career Paths',
'<h2>Cybersecurity Career Paths</h2><h3>Entry-Level Roles</h3><ul><li><strong>Security Analyst:</strong> Monitor systems for threats</li><li><strong>Junior Penetration Tester:</strong> Test systems for vulnerabilities</li><li><strong>SOC Analyst:</strong> Work in Security Operations Centers</li></ul><h3>Certifications</h3><ul><li>CompTIA Security+</li><li>CEH - Certified Ethical Hacker</li><li>CISSP</li></ul>',
'Video', 20, 3, NULL, NULL, 0, 1, 10, NOW(), NOW()),

(NULL, @m1, 'Module 1: Knowledge Check', '<h2>Module 1 Knowledge Check</h2><p>Test your understanding of cybersecurity fundamentals.</p>', 'Quiz', 20, 4, NULL, NULL, 0, 1, 25, NOW(), NOW()),

(NULL, @m2, 'The OSI Model',
'<h2>The OSI Model</h2><table class="w-full border-collapse border"><tr><th class="border p-2">Layer</th><th class="border p-2">Name</th><th class="border p-2">Function</th></tr><tr><td class="border p-2">7</td><td class="border p-2">Application</td><td class="border p-2">HTTP, FTP, SMTP</td></tr><tr><td class="border p-2">6</td><td class="border p-2">Presentation</td><td class="border p-2">Data formatting</td></tr><tr><td class="border p-2">5</td><td class="border p-2">Session</td><td class="border p-2">Session management</td></tr><tr><td class="border p-2">4</td><td class="border p-2">Transport</td><td class="border p-2">TCP, UDP</td></tr><tr><td class="border p-2">3</td><td class="border p-2">Network</td><td class="border p-2">IP, routing</td></tr><tr><td class="border p-2">2</td><td class="border p-2">Data Link</td><td class="border p-2">MAC addresses</td></tr><tr><td class="border p-2">1</td><td class="border p-2">Physical</td><td class="border p-2">Cables, signals</td></tr></table>',
'Video', 35, 1, NULL, NULL, 1, 1, 15, NOW(), NOW()),

(NULL, @m2, 'TCP/IP and Network Protocols',
'<h2>TCP/IP and Network Protocols</h2><ul><li><strong>HTTP (Port 80):</strong> Unencrypted web traffic</li><li><strong>HTTPS (Port 443):</strong> Encrypted web traffic</li><li><strong>FTP (Port 21):</strong> File transfer - credentials in plaintext</li><li><strong>SSH (Port 22):</strong> Secure remote access</li><li><strong>DNS (Port 53):</strong> Domain resolution</li><li><strong>SMTP (Port 25):</strong> Email sending</li></ul>',
'Video', 30, 2, NULL, NULL, 0, 1, 15, NOW(), NOW()),

(NULL, @m2, 'Network Security Basics',
'<h2>Network Security Basics</h2><h3>Common Network Attacks</h3><ul><li><strong>Man-in-the-Middle (MitM):</strong> Intercepting communication</li><li><strong>ARP Spoofing:</strong> Falsifying ARP messages</li><li><strong>DNS Spoofing:</strong> Corrupting DNS cache</li><li><strong>Packet Sniffing:</strong> Capturing network traffic</li></ul><h3>Wireshark Basics</h3><p>Wireshark is a free network protocol analyzer used to capture and inspect network traffic.</p>',
'Video', 35, 3, NULL, NULL, 0, 1, 15, NOW(), NOW()),

(NULL, @m2, 'Module 2: Knowledge Check', '<h2>Module 2 Knowledge Check</h2><p>Test your networking knowledge.</p>', 'Quiz', 25, 4, NULL, NULL, 0, 1, 25, NOW(), NOW()),

(NULL, @m3, 'Types of Malware',
'<h2>Types of Malware</h2><ul><li><strong>Virus:</strong> Self-replicating code attaching to programs</li><li><strong>Worm:</strong> Self-spreading, no host needed</li><li><strong>Trojan:</strong> Disguised as legitimate software</li><li><strong>Ransomware:</strong> Encrypts files, demands payment</li><li><strong>Spyware:</strong> Secretly monitors activity</li><li><strong>Keylogger:</strong> Records keystrokes</li></ul><h3>Famous Examples</h3><ul><li><strong>WannaCry (2017):</strong> 200,000+ computers affected</li><li><strong>Stuxnet (2010):</strong> First known cyberweapon</li></ul>',
'Video', 30, 1, NULL, NULL, 0, 1, 15, NOW(), NOW()),

(NULL, @m3, 'Social Engineering Attacks',
'<h2>Social Engineering Attacks</h2><ul><li><strong>Phishing:</strong> Fraudulent emails</li><li><strong>Spear Phishing:</strong> Targeted phishing</li><li><strong>Whaling:</strong> Targeting executives</li><li><strong>Tailgating:</strong> Following someone into secure areas</li></ul><h3>Red Flags</h3><ul><li>Urgent or threatening language</li><li>Requests for personal information</li><li>Suspicious sender addresses</li></ul>',
'Video', 30, 2, NULL, NULL, 0, 1, 15, NOW(), NOW()),

(NULL, @m3, 'Attack Vectors and Exploits',
'<h2>Attack Vectors and Exploits</h2><h3>The Cyber Kill Chain</h3><ol><li>Reconnaissance</li><li>Weaponization</li><li>Delivery</li><li>Exploitation</li><li>Installation</li><li>Command and Control</li><li>Actions on Objectives</li></ol>',
'Video', 35, 3, NULL, NULL, 0, 1, 15, NOW(), NOW()),

(NULL, @m3, 'Module 3: Knowledge Check', '<h2>Module 3 Knowledge Check</h2><p>Test your knowledge of cyber threats.</p>', 'Quiz', 25, 4, NULL, NULL, 0, 1, 25, NOW(), NOW()),

(NULL, @m4, 'Firewalls and Network Defense',
'<h2>Firewalls and Network Defense</h2><h3>Types of Firewalls</h3><ul><li><strong>Packet-Filtering:</strong> IP/port rules</li><li><strong>Stateful Inspection:</strong> Tracks connections</li><li><strong>Proxy Firewall:</strong> Intermediary</li><li><strong>NGFW:</strong> Includes IDS/IPS</li></ul><h3>IDS vs IPS</h3><ul><li><strong>IDS:</strong> Detects and alerts</li><li><strong>IPS:</strong> Actively blocks</li></ul>',
'Video', 35, 1, NULL, NULL, 0, 1, 15, NOW(), NOW()),

(NULL, @m4, 'Encryption and Cryptography',
'<h2>Encryption and Cryptography</h2><ul><li><strong>Symmetric:</strong> Same key (AES)</li><li><strong>Asymmetric:</strong> Public/private pair (RSA)</li><li><strong>Hashing:</strong> One-way (SHA-256)</li></ul><h3>PKI</h3><ul><li><strong>CA:</strong> Issues certificates</li><li><strong>SSL/TLS:</strong> Enables HTTPS</li></ul>',
'Video', 40, 2, NULL, NULL, 0, 1, 15, NOW(), NOW()),

(NULL, @m4, 'Access Control and Authentication',
'<h2>Access Control and Authentication</h2><h3>Authentication Factors</h3><ul><li>Something you know (password)</li><li>Something you have (token)</li><li>Something you are (biometric)</li></ul><h3>MFA</h3><p>Multi-Factor Authentication significantly reduces account compromise.</p><h3>RBAC</h3><p>Role-Based Access Control grants permissions based on user roles.</p>',
'Video', 35, 3, NULL, NULL, 0, 1, 15, NOW(), NOW()),

(NULL, @m4, 'Module 4: Knowledge Check', '<h2>Module 4 Knowledge Check</h2><p>Test your knowledge of security controls.</p>', 'Quiz', 25, 4, NULL, NULL, 0, 1, 25, NOW(), NOW()),

(NULL, @m5, 'OWASP Top 10 Vulnerabilities',
'<h2>OWASP Top 10 (2021)</h2><ol><li>Broken Access Control</li><li>Cryptographic Failures</li><li>Injection</li><li>Insecure Design</li><li>Security Misconfiguration</li><li>Vulnerable Components</li><li>Authentication Failures</li><li>Data Integrity Failures</li><li>Logging Failures</li><li>SSRF</li></ol>',
'Video', 40, 1, NULL, NULL, 0, 1, 15, NOW(), NOW()),

(NULL, @m5, 'SQL Injection and XSS',
'<h2>SQL Injection and XSS</h2><h3>SQL Injection</h3><p>Occurs when untrusted input is concatenated into SQL queries. Defense: parameterized queries.</p><h3>XSS Types</h3><ul><li><strong>Stored:</strong> Script stored on server</li><li><strong>Reflected:</strong> Script in URL</li><li><strong>DOM-based:</strong> Client-side manipulation</li></ul>',
'Video', 40, 2, NULL, NULL, 0, 1, 15, NOW(), NOW()),

(NULL, @m5, 'Secure Coding Practices',
'<h2>Secure Coding Practices</h2><ul><li>Validate all input server-side</li><li>Use whitelist validation</li><li>Output encoding</li><li>Use security headers (CSP, X-Frame-Options)</li><li>Never expose stack traces</li></ul>',
'Video', 35, 3, NULL, NULL, 0, 1, 15, NOW(), NOW()),

(NULL, @m5, 'Module 5: Knowledge Check', '<h2>Module 5 Knowledge Check</h2><p>Test your web security knowledge.</p>', 'Quiz', 30, 4, NULL, NULL, 0, 1, 25, NOW(), NOW()),

(NULL, @m6, 'Introduction to Ethical Hacking',
'<h2>Introduction to Ethical Hacking</h2><h3>Hat Types</h3><ul><li><strong>White Hat:</strong> Authorized, helps improve security</li><li><strong>Black Hat:</strong> Malicious, illegal</li><li><strong>Gray Hat:</strong> Unauthorized but not malicious</li></ul><h3>Testing Types</h3><ul><li><strong>Black Box:</strong> No prior knowledge</li><li><strong>White Box:</strong> Full knowledge</li><li><strong>Gray Box:</strong> Partial knowledge</li></ul>',
'Video', 30, 1, NULL, NULL, 0, 1, 15, NOW(), NOW()),

(NULL, @m6, 'Reconnaissance and Scanning',
'<h2>Reconnaissance and Scanning</h2><h3>Passive</h3><ul><li>OSINT</li><li>Google Dorking</li><li>Whois/DNS records</li></ul><h3>Active</h3><ul><li>Port scanning (Nmap)</li><li>Service enumeration</li></ul><h3>Nmap</h3><pre>nmap -sS target.com\nnmap -sV target.com</pre>',
'Video', 40, 2, NULL, NULL, 0, 1, 15, NOW(), NOW()),

(NULL, @m6, 'Vulnerability Exploitation Basics',
'<h2>Vulnerability Exploitation Basics</h2><h3>Resources</h3><ul><li>Exploit-DB</li><li>CVE</li></ul><h3>Metasploit</h3><p>World\'s most used penetration testing framework.</p><h3>Responsible Disclosure</h3><ol><li>Notify privately</li><li>Allow time to fix</li><li>Coordinate disclosure</li></ol>',
'Video', 35, 3, NULL, NULL, 0, 1, 15, NOW(), NOW()),

(NULL, @m6, 'Module 6: Knowledge Check', '<h2>Module 6 Knowledge Check</h2><p>Test your ethical hacking knowledge.</p>', 'Quiz', 25, 4, NULL, NULL, 0, 1, 25, NOW(), NOW()),

(NULL, @m7, 'Incident Response Process',
'<h2>Incident Response Process</h2><h3>NIST Lifecycle</h3><ol><li>Preparation</li><li>Detection and Analysis</li><li>Containment</li><li>Eradication</li><li>Recovery</li><li>Post-Incident Activity</li></ol>',
'Video', 35, 1, NULL, NULL, 0, 1, 15, NOW(), NOW()),

(NULL, @m7, 'Digital Forensics Fundamentals',
'<h2>Digital Forensics</h2><h3>Principles</h3><ul><li>Evidence integrity</li><li>Documentation</li><li>Repeatability</li></ul><h3>Tools</h3><ul><li>Autopsy</li><li>Volatility</li></ul>',
'Video', 35, 2, NULL, NULL, 0, 1, 15, NOW(), NOW()),

(NULL, @m7, 'Incident Reporting and Documentation',
'<h2>Incident Reporting</h2><h3>Components</h3><ul><li>Executive summary</li><li>Timeline</li><li>Technical details</li><li>Impact assessment</li><li>Root cause analysis</li><li>Recommendations</li></ul><h3>Zambia DPA</h3><p>Data breach notification requirements.</p>',
'Video', 25, 3, NULL, NULL, 0, 1, 15, NOW(), NOW()),

(NULL, @m7, 'Module 7: Knowledge Check', '<h2>Module 7 Knowledge Check</h2><p>Test your incident response knowledge.</p>', 'Quiz', 25, 4, NULL, NULL, 0, 1, 25, NOW(), NOW()),

(NULL, @m8, 'Introduction to SIEM',
'<h2>Introduction to SIEM</h2><h3>What SIEM Does</h3><ul><li>Log collection</li><li>Correlation</li><li>Alerting</li><li>Dashboards</li></ul><h3>Tools</h3><ul><li>Splunk</li><li>Microsoft Sentinel</li><li>Elastic Stack (ELK)</li><li>Wazuh</li></ul>',
'Video', 30, 1, NULL, NULL, 0, 1, 15, NOW(), NOW()),

(NULL, @m8, 'Log Analysis and Monitoring',
'<h2>Log Analysis</h2><h3>Log Sources</h3><ul><li>OS logs</li><li>Firewall logs</li><li>Web server logs</li><li>Authentication logs</li></ul><h3>What to Look For</h3><ul><li>Multiple failed logins</li><li>Off-hours logins</li><li>Unusual data transfers</li><li>Known malicious IPs</li></ul>',
'Video', 30, 2, NULL, NULL, 0, 1, 15, NOW(), NOW()),

(NULL, @m8, 'SOC Operations and Workflow',
'<h2>SOC Operations</h2><h3>Tiers</h3><ul><li><strong>Tier 1:</strong> Alert triage</li><li><strong>Tier 2:</strong> Incident response</li><li><strong>Tier 3:</strong> Threat hunting</li></ul><h3>Metrics</h3><ul><li>MTTD - Mean Time to Detect</li><li>MTTR - Mean Time to Respond</li></ul><h3>MITRE ATT&CK</h3><p>Knowledge base of adversary tactics and techniques.</p>',
'Video', 35, 3, NULL, NULL, 0, 1, 15, NOW(), NOW()),

(NULL, @m8, 'Module 8: Knowledge Check', '<h2>Module 8 Knowledge Check</h2><p>Test your SOC knowledge.</p>', 'Quiz', 25, 4, NULL, NULL, 0, 1, 25, NOW(), NOW()),

(NULL, @m9, 'Security Frameworks and Standards',
'<h2>Security Frameworks</h2><h3>NIST CSF</h3><ul><li>Identify</li><li>Protect</li><li>Detect</li><li>Respond</li><li>Recover</li></ul><h3>ISO 27001</h3><p>Information Security Management Systems.</p><h3>CIS Controls</h3><p>20 prioritized security controls.</p>',
'Video', 30, 1, NULL, NULL, 0, 1, 15, NOW(), NOW()),

(NULL, @m9, 'Risk Management',
'<h2>Risk Management</h2><h3>Assessment</h3><ol><li>Asset identification</li><li>Threat identification</li><li>Vulnerability assessment</li><li>Risk calculation: Likelihood x Impact</li></ol><h3>Treatment</h3><ul><li>Accept</li><li>Mitigate</li><li>Transfer</li><li>Avoid</li></ul>',
'Video', 30, 2, NULL, NULL, 0, 1, 15, NOW(), NOW()),

(NULL, @m9, 'Compliance and Audit',
'<h2>Compliance and Audit</h2><h3>Audit Types</h3><ul><li>Internal</li><li>External</li><li>Regulatory</li></ul><h3>Zambia DPA</h3><ul><li>Lawful processing</li><li>Data subject rights</li><li>Breach notification</li></ul>',
'Video', 25, 3, NULL, NULL, 0, 1, 15, NOW(), NOW()),

(NULL, @m9, 'Module 9: Knowledge Check', '<h2>Module 9 Knowledge Check</h2><p>Test your GRC knowledge.</p>', 'Quiz', 25, 4, NULL, NULL, 0, 1, 25, NOW(), NOW()),

(NULL, @m10, 'Capstone Project Overview',
'<h2>Capstone Project</h2><p>You are hired as a Junior Security Analyst for a company in Lusaka. They experienced a phishing attack and want to improve security.</p><h3>Tasks</h3><ol><li>Risk Assessment</li><li>Security Policy</li><li>Network Design</li><li>Incident Response Plan</li><li>Security Awareness Training</li></ol>',
'Assignment', 45, 1, NULL, NULL, 0, 1, 100, NOW(), NOW()),

(NULL, @m10, 'Career Preparation and Next Steps',
'<h2>Career Preparation</h2><h3>Next Steps</h3><ol><li>Set up a home lab</li><li>TryHackMe, Hack The Box</li><li>CompTIA Security+</li><li>Join cybersecurity communities</li></ol>',
'Video', 25, 2, NULL, NULL, 0, 1, 10, NOW(), NOW()),

(NULL, @m10, 'Final Assessment', '<h2>Final Assessment</h2><p>Comprehensive final examination covering all modules.</p>', 'Quiz', 60, 3, NULL, NULL, 0, 1, 150, NOW(), NOW());

-- ============================================
-- 5. QUIZZES (one at a time to capture IDs)
-- ============================================
INSERT INTO `quizzes` (`id`, `course_id`, `lesson_id`, `title`, `description`, `quiz_type`, `time_limit_minutes`, `max_attempts`, `passing_score`, `randomize_questions`, `show_correct_answers`, `available_from`, `available_until`, `is_published`, `created_at`, `updated_at`)
VALUES (NULL, @c_id, NULL, 'Module 1 Quiz: Introduction to Cybersecurity', 'Test your understanding of cybersecurity fundamentals', 'Graded', 20, 3, 70.00, 1, 1, NULL, NULL, 1, NOW(), NOW());
SET @q1 = LAST_INSERT_ID();

INSERT INTO `quizzes` (`id`, `course_id`, `lesson_id`, `title`, `description`, `quiz_type`, `time_limit_minutes`, `max_attempts`, `passing_score`, `randomize_questions`, `show_correct_answers`, `available_from`, `available_until`, `is_published`, `created_at`, `updated_at`)
VALUES (NULL, @c_id, NULL, 'Module 2 Quiz: Networking Fundamentals', 'Test your networking knowledge', 'Graded', 25, 3, 70.00, 1, 1, NULL, NULL, 1, NOW(), NOW());
SET @q2 = LAST_INSERT_ID();

INSERT INTO `quizzes` (`id`, `course_id`, `lesson_id`, `title`, `description`, `quiz_type`, `time_limit_minutes`, `max_attempts`, `passing_score`, `randomize_questions`, `show_correct_answers`, `available_from`, `available_until`, `is_published`, `created_at`, `updated_at`)
VALUES (NULL, @c_id, NULL, 'Module 3 Quiz: Cyber Threats and Attacks', 'Test your knowledge of cyber threats', 'Graded', 25, 3, 70.00, 1, 1, NULL, NULL, 1, NOW(), NOW());
SET @q3 = LAST_INSERT_ID();

INSERT INTO `quizzes` (`id`, `course_id`, `lesson_id`, `title`, `description`, `quiz_type`, `time_limit_minutes`, `max_attempts`, `passing_score`, `randomize_questions`, `show_correct_answers`, `available_from`, `available_until`, `is_published`, `created_at`, `updated_at`)
VALUES (NULL, @c_id, NULL, 'Module 4 Quiz: Security Controls and Defense', 'Test your knowledge of security controls', 'Graded', 25, 3, 70.00, 1, 1, NULL, NULL, 1, NOW(), NOW());
SET @q4 = LAST_INSERT_ID();

INSERT INTO `quizzes` (`id`, `course_id`, `lesson_id`, `title`, `description`, `quiz_type`, `time_limit_minutes`, `max_attempts`, `passing_score`, `randomize_questions`, `show_correct_answers`, `available_from`, `available_until`, `is_published`, `created_at`, `updated_at`)
VALUES (NULL, @c_id, NULL, 'Module 5 Quiz: Web Application Security', 'Test your web security knowledge', 'Graded', 30, 3, 70.00, 1, 1, NULL, NULL, 1, NOW(), NOW());
SET @q5 = LAST_INSERT_ID();

INSERT INTO `quizzes` (`id`, `course_id`, `lesson_id`, `title`, `description`, `quiz_type`, `time_limit_minutes`, `max_attempts`, `passing_score`, `randomize_questions`, `show_correct_answers`, `available_from`, `available_until`, `is_published`, `created_at`, `updated_at`)
VALUES (NULL, @c_id, NULL, 'Module 6 Quiz: Ethical Hacking Basics', 'Test your ethical hacking knowledge', 'Graded', 25, 3, 70.00, 1, 1, NULL, NULL, 1, NOW(), NOW());
SET @q6 = LAST_INSERT_ID();

INSERT INTO `quizzes` (`id`, `course_id`, `lesson_id`, `title`, `description`, `quiz_type`, `time_limit_minutes`, `max_attempts`, `passing_score`, `randomize_questions`, `show_correct_answers`, `available_from`, `available_until`, `is_published`, `created_at`, `updated_at`)
VALUES (NULL, @c_id, NULL, 'Module 7 Quiz: Incident Response and Forensics', 'Test your incident response knowledge', 'Graded', 25, 3, 70.00, 1, 1, NULL, NULL, 1, NOW(), NOW());
SET @q7 = LAST_INSERT_ID();

INSERT INTO `quizzes` (`id`, `course_id`, `lesson_id`, `title`, `description`, `quiz_type`, `time_limit_minutes`, `max_attempts`, `passing_score`, `randomize_questions`, `show_correct_answers`, `available_from`, `available_until`, `is_published`, `created_at`, `updated_at`)
VALUES (NULL, @c_id, NULL, 'Module 8 Quiz: Security Operations', 'Test your SOC knowledge', 'Graded', 25, 3, 70.00, 1, 1, NULL, NULL, 1, NOW(), NOW());
SET @q8 = LAST_INSERT_ID();

INSERT INTO `quizzes` (`id`, `course_id`, `lesson_id`, `title`, `description`, `quiz_type`, `time_limit_minutes`, `max_attempts`, `passing_score`, `randomize_questions`, `show_correct_answers`, `available_from`, `available_until`, `is_published`, `created_at`, `updated_at`)
VALUES (NULL, @c_id, NULL, 'Module 9 Quiz: Governance, Risk, and Compliance', 'Test your GRC knowledge', 'Graded', 25, 3, 70.00, 1, 1, NULL, NULL, 1, NOW(), NOW());
SET @q9 = LAST_INSERT_ID();

INSERT INTO `quizzes` (`id`, `course_id`, `lesson_id`, `title`, `description`, `quiz_type`, `time_limit_minutes`, `max_attempts`, `passing_score`, `randomize_questions`, `show_correct_answers`, `available_from`, `available_until`, `is_published`, `created_at`, `updated_at`)
VALUES (NULL, @c_id, NULL, 'Final Assessment: Cybersecurity Fundamentals', 'Comprehensive final assessment covering all modules', 'Final Exam', 60, 2, 75.00, 1, 0, NULL, NULL, 1, NOW(), NOW());
SET @qf = LAST_INSERT_ID();

-- ============================================
-- 6. QUESTIONS (one at a time to capture IDs)
-- ============================================

-- Module 1 Questions
INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'What does the "C" in the CIA Triad stand for?', 1, 'The CIA Triad consists of Confidentiality, Integrity, and Availability.', NOW(), NOW());
SET @m1q1 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which of the following is NOT a type of threat actor?', 1, 'System Administrators are defenders, not threat actors.', NOW(), NOW());
SET @m1q2 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'A weakness in a system that could be exploited is called a(n):', 1, 'A vulnerability is a weakness in a system.', NOW(), NOW());
SET @m1q3 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'What is the primary motivation of cybercriminals?', 1, 'Cybercriminals are primarily motivated by financial gain.', NOW(), NOW());
SET @m1q4 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which principle ensures data is accessible when needed?', 1, 'Availability ensures systems and data are accessible.', NOW(), NOW());
SET @m1q5 = LAST_INSERT_ID();

-- Module 2 Questions
INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'At which OSI layer does routing occur?', 1, 'Routing occurs at Layer 3 (Network Layer).', NOW(), NOW());
SET @m2q1 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which protocol uses port 443 by default?', 1, 'HTTPS uses port 443.', NOW(), NOW());
SET @m2q2 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'What does ARP stand for?', 1, 'ARP maps IP addresses to MAC addresses.', NOW(), NOW());
SET @m2q3 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which is the encrypted alternative to Telnet?', 1, 'SSH provides encrypted remote access.', NOW(), NOW());
SET @m2q4 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Network segmentation using VLANs primarily helps with:', 1, 'VLANs segment networks to contain breaches.', NOW(), NOW());
SET @m2q5 = LAST_INSERT_ID();

-- Module 3 Questions
INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which malware type encrypts files and demands payment?', 1, 'Ransomware encrypts files and demands payment.', NOW(), NOW());
SET @m3q1 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'What is phishing?', 1, 'Phishing is a fraudulent attempt to obtain sensitive information.', NOW(), NOW());
SET @m3q2 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'In the Cyber Kill Chain, what comes after "Delivery"?', 1, 'The phases are: Reconnaissance -> Weaponization -> Delivery -> Exploitation.', NOW(), NOW());
SET @m3q3 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which of the following is a social engineering technique?', 1, 'Tailgating is a physical social engineering technique.', NOW(), NOW());
SET @m3q4 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'What is the primary purpose of a keylogger?', 1, 'A keylogger records keystrokes to capture passwords.', NOW(), NOW());
SET @m3q5 = LAST_INSERT_ID();

-- Module 4 Questions
INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which encryption type uses the same key for encryption and decryption?', 1, 'Symmetric encryption uses a single shared key.', NOW(), NOW());
SET @m4q1 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'What does MFA stand for?', 1, 'Multi-Factor Authentication requires two or more verification factors.', NOW(), NOW());
SET @m4q2 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'In RBAC, access is determined by:', 1, 'Role-Based Access Control grants access based on user roles.', NOW(), NOW());
SET @m4q3 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which system actively blocks detected threats in real-time?', 1, 'IPS actively blocks threats while IDS only detects and alerts.', NOW(), NOW());
SET @m4q4 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'TLS is used for:', 1, 'TLS encrypts web traffic and enables HTTPS.', NOW(), NOW());
SET @m4q5 = LAST_INSERT_ID();

-- Module 5 Questions
INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which OWASP Top 10 item involves untrusted data sent to interpreters?', 1, 'Injection flaws occur when untrusted data is sent to interpreters.', NOW(), NOW());
SET @m5q1 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'What is the best defense against SQL Injection?', 1, 'Parameterized queries separate SQL code from data.', NOW(), NOW());
SET @m5q2 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which XSS type stores the malicious script on the server?', 1, 'Stored (Persistent) XSS stores scripts permanently on the server.', NOW(), NOW());
SET @m5q3 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which header prevents clickjacking attacks?', 1, 'X-Frame-Options controls whether a page can be displayed in frames.', NOW(), NOW());
SET @m5q4 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'What does CSP stand for in web security?', 1, 'Content Security Policy helps prevent XSS and data injection.', NOW(), NOW());
SET @m5q5 = LAST_INSERT_ID();

-- Module 6 Questions
INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which type of hacker is authorized to test systems?', 1, 'White Hat hackers have authorization to test and improve security.', NOW(), NOW());
SET @m6q1 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'What does OSINT stand for?', 1, 'OSINT refers to intelligence gathered from publicly available sources.', NOW(), NOW());
SET @m6q2 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which tool is commonly used for port scanning?', 1, 'Nmap is the industry-standard tool for port scanning.', NOW(), NOW());
SET @m6q3 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'In a Black Box test, the tester has:', 1, 'Black Box testing simulates an external attacker with no prior knowledge.', NOW(), NOW());
SET @m6q4 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'What does CVE stand for?', 1, 'CVE provides standardized identifiers for known vulnerabilities.', NOW(), NOW());
SET @m6q5 = LAST_INSERT_ID();

-- Module 7 Questions
INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which NIST phase involves removing threats and vulnerabilities?', 1, 'Eradication removes threats and eliminates vulnerabilities.', NOW(), NOW());
SET @m7q1 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'What is the primary purpose of chain of custody?', 1, 'Chain of custody ensures digital evidence is admissible in court.', NOW(), NOW());
SET @m7q2 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which tool is used for memory forensics?', 1, 'Volatility is an open-source memory forensics framework.', NOW(), NOW());
SET @m7q3 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'What should be done FIRST when responding to an incident?', 1, 'Preserving evidence is critical for investigation.', NOW(), NOW());
SET @m7q4 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'A bit-for-bit copy of storage media is called a:', 1, 'A disk image preserves all data including deleted files.', NOW(), NOW());
SET @m7q5 = LAST_INSERT_ID();

-- Module 8 Questions
INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'What does SIEM stand for?', 1, 'SIEM collects, correlates, and analyzes security events.', NOW(), NOW());
SET @m8q1 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which SOC tier is responsible for initial alert review?', 1, 'Tier 1 analysts handle initial alert triage.', NOW(), NOW());
SET @m8q2 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'MTTD stands for:', 1, 'MTTD measures average time to detect threats.', NOW(), NOW());
SET @m8q3 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which of the following is an open-source SIEM tool?', 1, 'Wazuh is an open-source security monitoring platform.', NOW(), NOW());
SET @m8q4 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'What is the MITRE ATT&CK framework used for?', 1, 'MITRE ATT&CK is used for threat modeling and detection.', NOW(), NOW());
SET @m8q5 = LAST_INSERT_ID();

-- Module 9 Questions
INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'The NIST CSF consists of how many core functions?', 1, 'NIST CSF has 5 core functions.', NOW(), NOW());
SET @m9q1 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'ISO 27001 is a standard for:', 1, 'ISO 27001 is for Information Security Management Systems.', NOW(), NOW());
SET @m9q2 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Risk is calculated as:', 1, 'Risk = Likelihood x Impact.', NOW(), NOW());
SET @m9q3 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which risk treatment involves transferring risk to a third party?', 1, 'Risk transfer moves financial consequence to another party.', NOW(), NOW());
SET @m9q4 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which NIST function involves taking action on detected incidents?', 1, 'The Respond function includes taking action on incidents.', NOW(), NOW());
SET @m9q5 = LAST_INSERT_ID();

-- Final Assessment Questions
INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'The three principles of the CIA Triad are:', 1, 'Confidentiality, Integrity, and Availability.', NOW(), NOW());
SET @f1 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which layer of the OSI model handles routing?', 1, 'Layer 3 (Network Layer).', NOW(), NOW());
SET @f2 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which malware type does NOT need a host program?', 1, 'Worms are self-replicating and do not need a host.', NOW(), NOW());
SET @f3 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which encryption algorithm is symmetric?', 1, 'AES is symmetric. RSA and ECC are asymmetric.', NOW(), NOW());
SET @f4 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'What is the primary defense against SQL Injection?', 1, 'Parameterized queries are the most effective defense.', NOW(), NOW());
SET @f5 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'In a Black Box penetration test, the tester has:', 1, 'No prior knowledge.', NOW(), NOW());
SET @f6 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which phase of incident response involves limiting damage?', 1, 'Containment isolates the incident.', NOW(), NOW());
SET @f7 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'What does a SIEM system primarily do?', 1, 'Collects and analyzes security events.', NOW(), NOW());
SET @f8 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'The NIST Cybersecurity Framework has how many functions?', 1, 'NIST CSF has 5 functions.', NOW(), NOW());
SET @f9 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which access control model grants permissions based on user roles?', 1, 'RBAC grants permissions based on roles.', NOW(), NOW());
SET @f10 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'What is the purpose of a honeypot?', 1, 'A honeypot attracts and detects attackers.', NOW(), NOW());
SET @f11 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Which social engineering technique involves following someone into a secure area?', 1, 'Tailgating follows someone into restricted areas.', NOW(), NOW());
SET @f12 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'TLS is used to:', 1, 'TLS encrypts web traffic for HTTPS.', NOW(), NOW());
SET @f13 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'What does OWASP stand for?', 1, 'Open Web Application Security Project.', NOW(), NOW());
SET @f14 = LAST_INSERT_ID();

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`)
VALUES (NULL, 'Multiple Choice', 'Risk is best defined as:', 1, 'Risk = Likelihood x Impact.', NOW(), NOW());
SET @f15 = LAST_INSERT_ID();

-- ============================================
-- 7. QUESTION OPTIONS (multi-row per question)
-- ============================================
INSERT INTO `question_options` (`option_id`, `question_id`, `option_text`, `is_correct`, `display_order`) VALUES
(NULL, @m1q1, 'Control', 0, 1), (NULL, @m1q1, 'Confidentiality', 1, 2), (NULL, @m1q1, 'Certification', 0, 3), (NULL, @m1q1, 'Compliance', 0, 4),
(NULL, @m1q2, 'Script Kiddie', 0, 1), (NULL, @m1q2, 'Cybercriminal', 0, 2), (NULL, @m1q2, 'System Administrator', 1, 3), (NULL, @m1q2, 'State-Sponsored Actor', 0, 4),
(NULL, @m1q3, 'Threat', 0, 1), (NULL, @m1q3, 'Risk', 0, 2), (NULL, @m1q3, 'Vulnerability', 1, 3), (NULL, @m1q3, 'Exploit', 0, 4),
(NULL, @m1q4, 'Political change', 0, 1), (NULL, @m1q4, 'Social justice', 0, 2), (NULL, @m1q4, 'Financial gain', 1, 3), (NULL, @m1q4, 'Personal recognition', 0, 4),
(NULL, @m1q5, 'Confidentiality', 0, 1), (NULL, @m1q5, 'Integrity', 0, 2), (NULL, @m1q5, 'Availability', 1, 3), (NULL, @m1q5, 'Authentication', 0, 4),
(NULL, @m2q1, 'Layer 2', 0, 1), (NULL, @m2q1, 'Layer 3', 1, 2), (NULL, @m2q1, 'Layer 4', 0, 3), (NULL, @m2q1, 'Layer 7', 0, 4),
(NULL, @m2q2, 'HTTP', 0, 1), (NULL, @m2q2, 'FTP', 0, 2), (NULL, @m2q2, 'HTTPS', 1, 3), (NULL, @m2q2, 'SSH', 0, 4),
(NULL, @m2q3, 'Address Resolution Protocol', 1, 1), (NULL, @m2q3, 'Advanced Routing Protocol', 0, 2), (NULL, @m2q3, 'Application Resource Protocol', 0, 3), (NULL, @m2q3, 'Automatic Response Procedure', 0, 4),
(NULL, @m2q4, 'FTP', 0, 1), (NULL, @m2q4, 'SSH', 1, 2), (NULL, @m2q4, 'HTTP', 0, 3), (NULL, @m2q4, 'SMTP', 0, 4),
(NULL, @m2q5, 'Increasing internet speed', 0, 1), (NULL, @m2q5, 'Limiting attack spread', 1, 2), (NULL, @m2q5, 'Reducing cable costs', 0, 3), (NULL, @m2q5, 'Improving wireless signal', 0, 4),
(NULL, @m3q1, 'Virus', 0, 1), (NULL, @m3q1, 'Worm', 0, 2), (NULL, @m3q1, 'Ransomware', 1, 3), (NULL, @m3q1, 'Spyware', 0, 4),
(NULL, @m3q2, 'A network scanning technique', 0, 1), (NULL, @m3q2, 'A fraudulent attempt to obtain sensitive information', 1, 2), (NULL, @m3q2, 'A type of firewall', 0, 3), (NULL, @m3q2, 'A password hashing method', 0, 4),
(NULL, @m3q3, 'Reconnaissance', 0, 1), (NULL, @m3q3, 'Weaponization', 0, 2), (NULL, @m3q3, 'Exploitation', 1, 3), (NULL, @m3q3, 'Installation', 0, 4),
(NULL, @m3q4, 'SQL Injection', 0, 1), (NULL, @m3q4, 'Tailgating', 1, 2), (NULL, @m3q4, 'Buffer Overflow', 0, 3), (NULL, @m3q4, 'ARP Spoofing', 0, 4),
(NULL, @m3q5, 'Encrypt files', 0, 1), (NULL, @m3q5, 'Record keystrokes', 1, 2), (NULL, @m3q5, 'Scan networks', 0, 3), (NULL, @m3q5, 'Block websites', 0, 4),
(NULL, @m4q1, 'Asymmetric', 0, 1), (NULL, @m4q1, 'Symmetric', 1, 2), (NULL, @m4q1, 'Hashing', 0, 3), (NULL, @m4q1, 'Public-key', 0, 4),
(NULL, @m4q2, 'Multi-Factor Authentication', 1, 1), (NULL, @m4q2, 'Managed Firewall Access', 0, 2), (NULL, @m4q2, 'Multi-Function Application', 0, 3), (NULL, @m4q2, 'Modular Framework Architecture', 0, 4),
(NULL, @m4q3, 'User attributes', 0, 1), (NULL, @m4q3, 'Resource ownership', 0, 2), (NULL, @m4q3, 'User roles', 1, 3), (NULL, @m4q3, 'Security labels', 0, 4),
(NULL, @m4q4, 'IDS', 0, 1), (NULL, @m4q4, 'IPS', 1, 2), (NULL, @m4q4, 'Firewall', 0, 3), (NULL, @m4q4, 'Proxy', 0, 4),
(NULL, @m4q5, 'File compression', 0, 1), (NULL, @m4q5, 'Secure web communication', 1, 2), (NULL, @m4q5, 'Database indexing', 0, 3), (NULL, @m4q5, 'Email routing', 0, 4),
(NULL, @m5q1, 'Broken Access Control', 0, 1), (NULL, @m5q1, 'Cryptographic Failures', 0, 2), (NULL, @m5q1, 'Injection', 1, 3), (NULL, @m5q1, 'Insecure Design', 0, 4),
(NULL, @m5q2, 'Input validation only', 0, 1), (NULL, @m5q2, 'Parameterized queries', 1, 2), (NULL, @m5q2, 'Firewall rules', 0, 3), (NULL, @m5q2, 'URL encoding', 0, 4),
(NULL, @m5q3, 'Reflected XSS', 0, 1), (NULL, @m5q3, 'Stored XSS', 1, 2), (NULL, @m5q3, 'DOM-based XSS', 0, 3), (NULL, @m5q3, 'Blind XSS', 0, 4),
(NULL, @m5q4, 'Content-Security-Policy', 0, 1), (NULL, @m5q4, 'X-Frame-Options', 1, 2), (NULL, @m5q4, 'X-XSS-Protection', 0, 3), (NULL, @m5q4, 'Strict-Transport-Security', 0, 4),
(NULL, @m5q5, 'Cross-Site Protection', 0, 1), (NULL, @m5q5, 'Content Security Policy', 1, 2), (NULL, @m5q5, 'Client-Side Protocol', 0, 3), (NULL, @m5q5, 'Certificate Signing Process', 0, 4),
(NULL, @m6q1, 'Black Hat', 0, 1), (NULL, @m6q1, 'White Hat', 1, 2), (NULL, @m6q1, 'Gray Hat', 0, 3), (NULL, @m6q1, 'Script Kiddie', 0, 4),
(NULL, @m6q2, 'Open Source Intelligence', 1, 1), (NULL, @m6q2, 'Operating System Integration', 0, 2), (NULL, @m6q2, 'Online Security Intelligence', 0, 3), (NULL, @m6q2, 'Organizational Security Interface', 0, 4),
(NULL, @m6q3, 'Wireshark', 0, 1), (NULL, @m6q3, 'Nmap', 1, 2), (NULL, @m6q3, 'Metasploit', 0, 3), (NULL, @m6q3, 'Burp Suite', 0, 4),
(NULL, @m6q4, 'Full knowledge', 0, 1), (NULL, @m6q4, 'No prior knowledge', 1, 2), (NULL, @m6q4, 'Partial knowledge', 0, 3), (NULL, @m6q4, 'Source code access', 0, 4),
(NULL, @m6q5, 'Common Vulnerability Enumeration', 0, 1), (NULL, @m6q5, 'Common Vulnerabilities and Exposures', 1, 2), (NULL, @m6q5, 'Critical Vulnerability Entry', 0, 3), (NULL, @m6q5, 'Computer Virus Encyclopedia', 0, 4),
(NULL, @m7q1, 'Containment', 0, 1), (NULL, @m7q1, 'Eradication', 1, 2), (NULL, @m7q1, 'Recovery', 0, 3), (NULL, @m7q1, 'Detection', 0, 4),
(NULL, @m7q2, 'Speed up investigation', 0, 1), (NULL, @m7q2, 'Ensure evidence admissibility', 1, 2), (NULL, @m7q2, 'Reduce costs', 0, 3), (NULL, @m7q2, 'Identify attackers', 0, 4),
(NULL, @m7q3, 'Nmap', 0, 1), (NULL, @m7q3, 'Volatility', 1, 2), (NULL, @m7q3, 'Wireshark', 0, 3), (NULL, @m7q3, 'Nessus', 0, 4),
(NULL, @m7q4, 'Format affected systems', 0, 1), (NULL, @m7q4, 'Preserve evidence', 1, 2), (NULL, @m7q4, 'Notify the media', 0, 3), (NULL, @m7q4, 'Update antivirus', 0, 4),
(NULL, @m7q5, 'Snapshot', 0, 1), (NULL, @m7q5, 'Disk Image', 1, 2), (NULL, @m7q5, 'Backup', 0, 3), (NULL, @m7q5, 'Archive', 0, 4),
(NULL, @m8q1, 'Security Information and Event Management', 1, 1), (NULL, @m8q1, 'System Intelligence and Event Monitoring', 0, 2), (NULL, @m8q1, 'Secure Internet and Email Management', 0, 3), (NULL, @m8q1, 'System Integration and Enterprise Monitoring', 0, 4),
(NULL, @m8q2, 'Tier 1', 1, 1), (NULL, @m8q2, 'Tier 2', 0, 2), (NULL, @m8q2, 'Tier 3', 0, 3), (NULL, @m8q2, 'Tier 4', 0, 4),
(NULL, @m8q3, 'Mean Time to Detect', 1, 1), (NULL, @m8q3, 'Maximum Time to Detection', 0, 2), (NULL, @m8q3, 'Minimum Technical Threat Duration', 0, 3), (NULL, @m8q3, 'Managed Threat Transfer Delay', 0, 4),
(NULL, @m8q4, 'Splunk', 0, 1), (NULL, @m8q4, 'QRadar', 0, 2), (NULL, @m8q4, 'Wazuh', 1, 3), (NULL, @m8q4, 'Microsoft Sentinel', 0, 4),
(NULL, @m8q5, 'Network routing', 0, 1), (NULL, @m8q5, 'Threat modeling and detection', 1, 2), (NULL, @m8q5, 'Password management', 0, 3), (NULL, @m8q5, 'Data encryption', 0, 4),
(NULL, @m9q1, '3', 0, 1), (NULL, @m9q1, '4', 0, 2), (NULL, @m9q1, '5', 1, 3), (NULL, @m9q1, '6', 0, 4),
(NULL, @m9q2, 'Payment processing', 0, 1), (NULL, @m9q2, 'Information security management', 1, 2), (NULL, @m9q2, 'Network routing', 0, 3), (NULL, @m9q2, 'Software development', 0, 4),
(NULL, @m9q3, 'Threat + Vulnerability', 0, 1), (NULL, @m9q3, 'Likelihood x Impact', 1, 2), (NULL, @m9q3, 'Asset Value - Cost', 0, 3), (NULL, @m9q3, 'Threat - Control', 0, 4),
(NULL, @m9q4, 'Accept', 0, 1), (NULL, @m9q4, 'Mitigate', 0, 2), (NULL, @m9q4, 'Transfer', 1, 3), (NULL, @m9q4, 'Avoid', 0, 4),
(NULL, @m9q5, 'Identify', 0, 1), (NULL, @m9q5, 'Protect', 0, 2), (NULL, @m9q5, 'Detect', 0, 3), (NULL, @m9q5, 'Respond', 1, 4),
(NULL, @f1, 'Control, Integrity, Authentication', 0, 1), (NULL, @f1, 'Confidentiality, Integrity, Availability', 1, 2), (NULL, @f1, 'Certification, Implementation, Assessment', 0, 3), (NULL, @f1, 'Compliance, Investigation, Analysis', 0, 4),
(NULL, @f2, 'Data Link', 0, 1), (NULL, @f2, 'Network', 1, 2), (NULL, @f2, 'Transport', 0, 3), (NULL, @f2, 'Application', 0, 4),
(NULL, @f3, 'Virus', 0, 1), (NULL, @f3, 'Worm', 1, 2), (NULL, @f3, 'Trojan', 0, 3), (NULL, @f3, 'Rootkit', 0, 4),
(NULL, @f4, 'RSA', 0, 1), (NULL, @f4, 'AES', 1, 2), (NULL, @f4, 'ECC', 0, 3), (NULL, @f4, 'DSA', 0, 4),
(NULL, @f5, 'Input validation', 0, 1), (NULL, @f5, 'Parameterized queries', 1, 2), (NULL, @f5, 'Web Application Firewall', 0, 3), (NULL, @f5, 'URL encoding', 0, 4),
(NULL, @f6, 'Full knowledge of systems', 0, 1), (NULL, @f6, 'No prior knowledge', 1, 2), (NULL, @f6, 'Source code access', 0, 3), (NULL, @f6, 'Administrator credentials', 0, 4),
(NULL, @f7, 'Detection', 0, 1), (NULL, @f7, 'Containment', 1, 2), (NULL, @f7, 'Eradication', 0, 3), (NULL, @f7, 'Recovery', 0, 4),
(NULL, @f8, 'Encrypt network traffic', 0, 1), (NULL, @f8, 'Collect and analyze security events', 1, 2), (NULL, @f8, 'Block malicious websites', 0, 3), (NULL, @f8, 'Manage user passwords', 0, 4),
(NULL, @f9, '3', 0, 1), (NULL, @f9, '4', 0, 2), (NULL, @f9, '5', 1, 3), (NULL, @f9, '6', 0, 4),
(NULL, @f10, 'DAC', 0, 1), (NULL, @f10, 'MAC', 0, 2), (NULL, @f10, 'RBAC', 1, 3), (NULL, @f10, 'ABAC', 0, 4),
(NULL, @f11, 'Store backups', 0, 1), (NULL, @f11, 'Attract and detect attackers', 1, 2), (NULL, @f11, 'Encrypt data', 0, 3), (NULL, @f11, 'Speed up networks', 0, 4),
(NULL, @f12, 'Phishing', 0, 1), (NULL, @f12, 'Pretexting', 0, 2), (NULL, @f12, 'Tailgating', 1, 3), (NULL, @f12, 'Baiting', 0, 4),
(NULL, @f13, 'Compress files', 0, 1), (NULL, @f13, 'Encrypt web traffic', 1, 2), (NULL, @f13, 'Scan for viruses', 0, 3), (NULL, @f13, 'Route packets', 0, 4),
(NULL, @f14, 'Open Web Application Security Project', 1, 1), (NULL, @f14, 'Online Web Attack and Security Protocol', 0, 2), (NULL, @f14, 'Operational Web Application Standard Procedure', 0, 3), (NULL, @f14, 'Open Web Authentication Security Program', 0, 4),
(NULL, @f15, 'Threat + Vulnerability', 0, 1), (NULL, @f15, 'Likelihood x Impact', 1, 2), (NULL, @f15, 'Asset x Control', 0, 3), (NULL, @f15, 'Cost + Benefit', 0, 4);

-- ============================================
-- 8. QUIZ QUESTIONS (link quizzes to questions)
-- ============================================
INSERT INTO `quiz_questions` (`quiz_question_id`, `quiz_id`, `question_id`, `display_order`, `points_override`) VALUES
(NULL, @q1, @m1q1, 1, NULL), (NULL, @q1, @m1q2, 2, NULL), (NULL, @q1, @m1q3, 3, NULL), (NULL, @q1, @m1q4, 4, NULL), (NULL, @q1, @m1q5, 5, NULL),
(NULL, @q2, @m2q1, 1, NULL), (NULL, @q2, @m2q2, 2, NULL), (NULL, @q2, @m2q3, 3, NULL), (NULL, @q2, @m2q4, 4, NULL), (NULL, @q2, @m2q5, 5, NULL),
(NULL, @q3, @m3q1, 1, NULL), (NULL, @q3, @m3q2, 2, NULL), (NULL, @q3, @m3q3, 3, NULL), (NULL, @q3, @m3q4, 4, NULL), (NULL, @q3, @m3q5, 5, NULL),
(NULL, @q4, @m4q1, 1, NULL), (NULL, @q4, @m4q2, 2, NULL), (NULL, @q4, @m4q3, 3, NULL), (NULL, @q4, @m4q4, 4, NULL), (NULL, @q4, @m4q5, 5, NULL),
(NULL, @q5, @m5q1, 1, NULL), (NULL, @q5, @m5q2, 2, NULL), (NULL, @q5, @m5q3, 3, NULL), (NULL, @q5, @m5q4, 4, NULL), (NULL, @q5, @m5q5, 5, NULL),
(NULL, @q6, @m6q1, 1, NULL), (NULL, @q6, @m6q2, 2, NULL), (NULL, @q6, @m6q3, 3, NULL), (NULL, @q6, @m6q4, 4, NULL), (NULL, @q6, @m6q5, 5, NULL),
(NULL, @q7, @m7q1, 1, NULL), (NULL, @q7, @m7q2, 2, NULL), (NULL, @q7, @m7q3, 3, NULL), (NULL, @q7, @m7q4, 4, NULL), (NULL, @q7, @m7q5, 5, NULL),
(NULL, @q8, @m8q1, 1, NULL), (NULL, @q8, @m8q2, 2, NULL), (NULL, @q8, @m8q3, 3, NULL), (NULL, @q8, @m8q4, 4, NULL), (NULL, @q8, @m8q5, 5, NULL),
(NULL, @q9, @m9q1, 1, NULL), (NULL, @q9, @m9q2, 2, NULL), (NULL, @q9, @m9q3, 3, NULL), (NULL, @q9, @m9q4, 4, NULL), (NULL, @q9, @m9q5, 5, NULL),
(NULL, @qf, @f1, 1, NULL), (NULL, @qf, @f2, 2, NULL), (NULL, @qf, @f3, 3, NULL), (NULL, @qf, @f4, 4, NULL), (NULL, @qf, @f5, 5, NULL),
(NULL, @qf, @f6, 6, NULL), (NULL, @qf, @f7, 7, NULL), (NULL, @qf, @f8, 8, NULL), (NULL, @qf, @f9, 9, NULL), (NULL, @qf, @f10, 10, NULL),
(NULL, @qf, @f11, 11, NULL), (NULL, @qf, @f12, 12, NULL), (NULL, @qf, @f13, 13, NULL), (NULL, @qf, @f14, 14, NULL), (NULL, @qf, @f15, 15, NULL);

COMMIT;

SELECT CONCAT('Cybersecurity course created with ID: ', @c_id) AS status;
