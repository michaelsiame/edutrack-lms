-- ============================================
-- Cybersecurity Course Content for Edutrack LMS
-- ============================================
-- This script inserts a complete Cybersecurity course
-- with modules, lessons, quizzes, questions, and options
-- Run this after the main schema is installed

-- ============================================
-- 0. ENSURE quiz_questions TABLE HAS AUTO_INCREMENT
-- ============================================
-- Note: ALTER TABLE causes an implicit commit, so it runs outside the transaction
ALTER TABLE `quiz_questions`
  MODIFY `quiz_question_id` int(11) NOT NULL AUTO_INCREMENT;

-- ============================================
-- BEGIN TRANSACTION
-- ============================================
START TRANSACTION;

-- ============================================
-- 1. INSERT COURSE CATEGORY (if not exists)
-- ============================================
INSERT IGNORE INTO course_categories (name, category_description, color, icon_url, display_order, is_active, created_at)
VALUES ('Cybersecurity', 'Learn to protect systems, networks, and data from cyber threats', '#DC2626', 'fa-shield-alt', 4, 1, NOW());

-- Get the category ID (whether just inserted or already existed)
SET @category_id = (SELECT id FROM course_categories WHERE name = 'Cybersecurity' LIMIT 1);

-- ============================================
-- 2. INSERT CYBERSECURITY COURSE
-- ============================================
INSERT INTO courses (
    title, slug, description, short_description, category_id,
    instructor_id, level, language, thumbnail_url,
    duration_weeks, total_hours, max_students, price,
    status, is_featured,
    learning_outcomes, prerequisites,
    created_at, updated_at
) VALUES (
    'Cybersecurity Fundamentals',
    'cybersecurity-fundamentals',
    '<p>This comprehensive cybersecurity course prepares you for entry-level roles in the rapidly growing field of cybersecurity. You will learn fundamental concepts, network security, threat detection, ethical hacking basics, and security operations.</p>
    <p>By the end of this course, you will understand how to protect systems, detect threats, and respond to security incidents using industry-standard tools and frameworks.</p>',
    'Master cybersecurity fundamentals and protect digital assets from cyber threats',
    @category_id,
    1,
    'Beginner',
    'English',
    'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=800',
    12,
    96.00,
    30,
    4500.00,
    'published',
    1,
    'Understand cybersecurity principles and the threat landscape|Identify and mitigate common network vulnerabilities|Implement security controls and defense strategies|Detect and respond to security incidents|Understand ethical hacking basics|Apply security frameworks like NIST',
    'Basic computer literacy, Understanding of operating systems (Windows/Linux)',
    NOW(),
    NOW()
);

SET @course_id = LAST_INSERT_ID();

-- ============================================
-- 3. INSERT MODULES
-- ============================================
INSERT INTO modules (course_id, title, description, display_order, duration_minutes, is_published, created_at) VALUES
(@course_id, 'Module 1: Introduction to Cybersecurity', 'Understanding the cybersecurity landscape, key concepts, and career paths', 1, 225, 1, NOW()),
(@course_id, 'Module 2: Networking Fundamentals', 'OSI model, TCP/IP, network protocols, and basic network security', 2, 300, 1, NOW()),
(@course_id, 'Module 3: Cyber Threats and Attacks', 'Types of malware, attack vectors, social engineering, and threat actors', 3, 285, 1, NOW()),
(@course_id, 'Module 4: Security Controls and Defense', 'Firewalls, IDS/IPS, encryption, access control, and defense strategies', 4, 330, 1, NOW()),
(@course_id, 'Module 5: Web Application Security', 'OWASP Top 10, secure coding, and web vulnerability assessment', 5, 345, 1, NOW()),
(@course_id, 'Module 6: Ethical Hacking Basics', 'Penetration testing methodology, reconnaissance, and vulnerability scanning', 6, 315, 1, NOW()),
(@course_id, 'Module 7: Incident Response and Forensics', 'Incident handling process, digital forensics fundamentals, and reporting', 7, 285, 1, NOW()),
(@course_id, 'Module 8: Security Operations (SOC)', 'SIEM tools, log analysis, monitoring, and SOC workflows', 8, 285, 1, NOW()),
(@course_id, 'Module 9: Governance, Risk, and Compliance', 'Security frameworks, risk management, and compliance standards', 9, 255, 1, NOW()),
(@course_id, 'Module 10: Capstone Project', 'Apply your skills to a real-world cybersecurity scenario', 10, 135, 1, NOW());

SET @module1_id = LAST_INSERT_ID();
SET @module2_id = @module1_id + 1;
SET @module3_id = @module1_id + 2;
SET @module4_id = @module1_id + 3;
SET @module5_id = @module1_id + 4;
SET @module6_id = @module1_id + 5;
SET @module7_id = @module1_id + 6;
SET @module8_id = @module1_id + 7;
SET @module9_id = @module1_id + 8;
SET @module10_id = @module1_id + 9;

-- ============================================
-- 4. INSERT LESSONS - MODULE 1
-- ============================================
INSERT INTO lessons (module_id, title, content, lesson_type, duration_minutes, display_order, is_preview, is_mandatory, points, created_at) VALUES
(@module1_id, 'What is Cybersecurity?', 
'<h2>What is Cybersecurity?</h2>
<p>Cybersecurity is the practice of protecting systems, networks, and programs from digital attacks. These cyberattacks are usually aimed at accessing, changing, or destroying sensitive information; extorting money from users; or interrupting normal business processes.</p>
<h3>Why Cybersecurity Matters</h3>
<ul>
<li><strong>Data Protection:</strong> Organizations hold vast amounts of sensitive data</li>
<li><strong>Financial Impact:</strong> Cybercrime costs the global economy billions annually</li>
<li><strong>National Security:</strong> Critical infrastructure needs protection</li>
<li><strong>Personal Privacy:</strong> Individuals need protection from identity theft</li>
</ul>
<h3>The CIA Triad</h3>
<p>The foundation of cybersecurity is built on three principles:</p>
<ul>
<li><strong>Confidentiality:</strong> Ensuring information is accessible only to authorized users</li>
<li><strong>Integrity:</strong> Maintaining the accuracy and completeness of data</li>
<li><strong>Availability:</strong> Ensuring systems and data are accessible when needed</li>
</ul>
<h3>Key Terminology</h3>
<ul>
<li><strong>Asset:</strong> Anything of value to an organization (data, systems, hardware)</li>
<li><strong>Vulnerability:</strong> A weakness that could be exploited</li>
<li><strong>Threat:</strong> A potential danger to an asset</li>
<li><strong>Risk:</strong> The likelihood and impact of a threat exploiting a vulnerability</li>
<li><strong>Exploit:</strong> A method used to take advantage of a vulnerability</li>
</ul>',
'Video', 30, 1, 1, 1, 10, NOW()),

(@module1_id, 'The Cyber Threat Landscape',
'<h2>The Cyber Threat Landscape</h2>
<p>Understanding who attacks systems and why is crucial for effective defense.</p>
<h3>Types of Threat Actors</h3>
<ul>
<li><strong>Script Kiddies:</strong> Unskilled attackers using existing tools</li>
<li><strong>Hacktivists:</strong> Attackers motivated by political or social causes</li>
<li><strong>Cybercriminals:</strong> Organized groups seeking financial gain</li>
<li><strong>State-Sponsored Actors:</strong> Nation-state backed attackers</li>
<li><strong>Insider Threats:</strong> Malicious or negligent employees</li>
</ul>
<h3>Common Attack Motivations</h3>
<ul>
<li>Financial gain (ransomware, fraud)</li>
<li>Political influence (election interference)</li>
<li>Corporate espionage (intellectual property theft)</li>
<li>Disruption (DDoS attacks on critical services)</li>
</ul>
<h3>Statistics in Zambia and Africa</h3>
<p>Africa is experiencing rapid digital transformation, making cybersecurity increasingly important. Mobile money platforms, government services, and businesses all face growing cyber threats.</p>',
'Video', 25, 2, 0, 1, 10, NOW()),

(@module1_id, 'Cybersecurity Career Paths',
'<h2>Cybersecurity Career Paths</h2>
<p>The cybersecurity field offers diverse career opportunities with strong demand globally and in Zambia.</p>
<h3>Entry-Level Roles</h3>
<ul>
<li><strong>Security Analyst:</strong> Monitor systems for threats and vulnerabilities</li>
<li><strong>Junior Penetration Tester:</strong> Test systems for vulnerabilities</li>
<li><strong>SOC Analyst:</strong> Work in Security Operations Centers monitoring alerts</li>
</ul>
<h3>Mid-Level Roles</h3>
<ul>
<li><strong>Security Engineer:</strong> Design and implement security solutions</li>
<li><strong>Incident Responder:</strong> Handle security breaches and incidents</li>
<li><strong>Security Consultant:</strong> Advise organizations on security posture</li>
</ul>
<h3>Advanced Roles</h3>
<ul>
<li><strong>Security Architect:</strong> Design enterprise security infrastructure</li>
<li><strong>CISO:</strong> Lead organization-wide security strategy</li>
</ul>
<h3>Certifications to Consider</h3>
<ul>
<li>CompTIA Security+ (Entry level)</li>
<li>CEH - Certified Ethical Hacker</li>
<li>CISSP - Certified Information Systems Security Professional</li>
</ul>',
'Video', 20, 3, 0, 1, 10, NOW()),

(@module1_id, 'Module 1: Knowledge Check',
'<h2>Module 1 Knowledge Check</h2>
<p>Test your understanding of cybersecurity fundamentals with this practice quiz.</p>',
'Quiz', 20, 4, 0, 1, 25, NOW());

-- ============================================
-- 5. INSERT LESSONS - MODULE 2
-- ============================================
INSERT INTO lessons (module_id, title, content, lesson_type, duration_minutes, display_order, is_preview, is_mandatory, points, created_at) VALUES
(@module2_id, 'The OSI Model',
'<h2>The OSI Model Explained</h2>
<p>The Open Systems Interconnection (OSI) model is a conceptual framework that standardizes network communication into seven layers.</p>
<h3>The Seven Layers</h3>
<table class="w-full border-collapse border">
<tr><th class="border p-2">Layer</th><th class="border p-2">Name</th><th class="border p-2">Function</th></tr>
<tr><td class="border p-2">7</td><td class="border p-2">Application</td><td class="border p-2">HTTP, FTP, SMTP - User interfaces</td></tr>
<tr><td class="border p-2">6</td><td class="border p-2">Presentation</td><td class="border p-2">Data formatting, encryption</td></tr>
<tr><td class="border p-2">5</td><td class="border p-2">Session</td><td class="border p-2">Session management</td></tr>
<tr><td class="border p-2">4</td><td class="border p-2">Transport</td><td class="border p-2">TCP, UDP - Reliable delivery</td></tr>
<tr><td class="border p-2">3</td><td class="border p-2">Network</td><td class="border p-2">IP, routing - Logical addressing</td></tr>
<tr><td class="border p-2">2</td><td class="border p-2">Data Link</td><td class="border p-2">MAC addresses, switches</td></tr>
<tr><td class="border p-2">1</td><td class="border p-2">Physical</td><td class="border p-2">Cables, signals, hardware</td></tr>
</table>
<h3>Why the OSI Model Matters for Security</h3>
<p>Understanding the OSI model helps security professionals identify where attacks occur and implement security controls at appropriate layers.</p>',
'Video', 35, 1, 1, 1, 15, NOW()),

(@module2_id, 'TCP/IP and Network Protocols',
'<h2>TCP/IP and Network Protocols</h2>
<p>The TCP/IP model is the practical implementation of network communication used on the internet today.</p>
<h3>Key Protocols and Their Security Implications</h3>
<ul>
<li><strong>HTTP (Port 80):</strong> Unencrypted web traffic - vulnerable to interception</li>
<li><strong>HTTPS (Port 443):</strong> Encrypted web traffic using TLS/SSL</li>
<li><strong>FTP (Port 21):</strong> File transfer - sends credentials in plaintext</li>
<li><strong>SSH (Port 22):</strong> Secure remote access - encrypted alternative to Telnet</li>
<li><strong>DNS (Port 53):</strong> Domain resolution - target for cache poisoning</li>
<li><strong>SMTP (Port 25):</strong> Email sending - often exploited for spam</li>
</ul>
<h3>Network Segmentation</h3>
<p>Dividing networks into segments (VLANs) limits the spread of attacks. Critical systems should be isolated from general user networks.</p>',
'Video', 30, 2, 0, 1, 15, NOW()),

(@module2_id, 'Network Security Basics',
'<h2>Network Security Basics</h2>
<p>Protecting network infrastructure is the first line of defense against cyber attacks.</p>
<h3>Common Network Attacks</h3>
<ul>
<li><strong>Man-in-the-Middle (MitM):</strong> Intercepting communication between parties</li>
<li><strong>ARP Spoofing:</strong> Falsifying ARP messages to redirect traffic</li>
<li><strong>DNS Spoofing:</strong> Corrupting DNS cache to redirect users</li>
<li><strong>Packet Sniffing:</strong> Capturing and analyzing network traffic</li>
</ul>
<h3>Network Security Controls</h3>
<ul>
<li><strong>Network Access Control (NAC):</strong> Controls device access to the network</li>
<li><strong>Virtual LANs (VLANs):</strong> Segment networks logically</li>
<li><strong>VPNs:</strong> Encrypt traffic over public networks</li>
<li><strong>Network Monitoring:</strong> Continuous traffic analysis for anomalies</li>
</ul>
<h3>Wireshark Basics</h3>
<p>Wireshark is a free network protocol analyzer used to capture and inspect network traffic. It is essential for network troubleshooting and security analysis.</p>',
'Video', 35, 3, 0, 1, 15, NOW()),

(@module2_id, 'Module 2: Knowledge Check',
'<h2>Module 2 Knowledge Check</h2>
<p>Test your networking knowledge with this practice quiz.</p>',
'Quiz', 25, 4, 0, 1, 25, NOW());

-- ============================================
-- 6. INSERT LESSONS - MODULE 3
-- ============================================
INSERT INTO lessons (module_id, title, content, lesson_type, duration_minutes, display_order, is_preview, is_mandatory, points, created_at) VALUES
(@module3_id, 'Types of Malware',
'<h2>Types of Malware</h2>
<p>Malware (malicious software) is any program designed to harm or exploit systems.</p>
<h3>Common Malware Types</h3>
<ul>
<li><strong>Virus:</strong> Self-replicating code that attaches to legitimate programs</li>
<li><strong>Worm:</strong> Self-spreading malware that doesn''t need a host program</li>
<li><strong>Trojan:</strong> Malware disguised as legitimate software</li>
<li><strong>Ransomware:</strong> Encrypts files and demands payment for decryption</li>
<li><strong>Spyware:</strong> Secretly monitors user activity</li>
<li><strong>Adware:</strong> Displays unwanted advertisements</li>
<li><strong>Rootkit:</strong> Hides malicious activity deep in the operating system</li>
<li><strong>Keylogger:</strong> Records keystrokes to steal passwords</li>
</ul>
<h3>Famous Malware Examples</h3>
<ul>
<li><strong>WannaCry (2017):</strong> Ransomware that affected 200,000+ computers globally</li>
<li><strong>Stuxnet (2010):</strong> First known cyberweapon targeting industrial systems</li>
<li><strong>Emotet:</strong> Banking trojan turned malware distribution platform</li>
</ul>',
'Video', 30, 1, 0, 1, 15, NOW()),

(@module3_id, 'Social Engineering Attacks',
'<h2>Social Engineering Attacks</h2>
<p>Social engineering exploits human psychology rather than technical vulnerabilities.</p>
<h3>Common Social Engineering Techniques</h3>
<ul>
<li><strong>Phishing:</strong> Fraudulent emails impersonating trusted entities</li>
<li><strong>Spear Phishing:</strong> Targeted phishing against specific individuals</li>
<li><strong>Whaling:</strong> Phishing targeting high-level executives</li>
<li><strong>Pretexting:</strong> Creating a fabricated scenario to gain information</li>
<li><strong>Baiting:</strong> Leaving infected USB drives in public places</li>
<li><strong>Quid Pro Quo:</strong> Offering a service in exchange for information</li>
<li><strong>Tailgating:</strong> Following someone into a restricted area</li>
</ul>
<h3>Red Flags of Phishing Emails</h3>
<ul>
<li>Urgent or threatening language</li>
<li>Requests for personal information</li>
<li>Suspicious sender addresses</li>
<li>Poor grammar and spelling</li>
<li>Unexpected attachments</li>
</ul>',
'Video', 30, 2, 0, 1, 15, NOW()),

(@module3_id, 'Attack Vectors and Exploits',
'<h2>Attack Vectors and Exploits</h2>
<p>Understanding how attackers gain access helps in building effective defenses.</p>
<h3>Common Attack Vectors</h3>
<ul>
<li><strong>Software Vulnerabilities:</strong> Unpatched systems and applications</li>
<li><strong>Weak Passwords:</strong> Easily guessable or reused credentials</li>
<li><strong>Insider Threats:</strong> Malicious or negligent employees</li>
<li><strong>Supply Chain:</strong> Attacking through third-party vendors</li>
<li><strong>Wireless Networks:</strong> Unsecured Wi-Fi networks</li>
</ul>
<h3>The Cyber Kill Chain</h3>
<ol>
<li><strong>Reconnaissance:</strong> Gathering information about the target</li>
<li><strong>Weaponization:</strong> Creating the attack payload</li>
<li><strong>Delivery:</strong> Transmitting the weapon to the target</li>
<li><strong>Exploitation:</strong> Triggering the vulnerability</li>
<li><strong>Installation:</strong> Establishing persistent access</li>
<li><strong>Command and Control:</strong> Remote control of compromised systems</li>
<li><strong>Actions on Objectives:</strong> Achieving the attacker''s goal</li>
</ol>',
'Video', 35, 3, 0, 1, 15, NOW()),

(@module3_id, 'Module 3: Knowledge Check',
'<h2>Module 3 Knowledge Check</h2>
<p>Test your knowledge of cyber threats and attacks.</p>',
'Quiz', 25, 4, 0, 1, 25, NOW());

-- ============================================
-- 7. INSERT LESSONS - MODULE 4
-- ============================================
INSERT INTO lessons (module_id, title, content, lesson_type, duration_minutes, display_order, is_preview, is_mandatory, points, created_at) VALUES
(@module4_id, 'Firewalls and Network Defense',
'<h2>Firewalls and Network Defense</h2>
<p>Firewalls are the primary defense mechanism for network security, controlling traffic based on security rules.</p>
<h3>Types of Firewalls</h3>
<ul>
<li><strong>Packet-Filtering Firewall:</strong> Inspects packets based on IP/port rules (Layer 3-4)</li>
<li><strong>Stateful Inspection:</strong> Tracks active connections and makes decisions based on connection state</li>
<li><strong>Proxy Firewall:</strong> Acts as intermediary between internal and external networks (Layer 7)</li>
<li><strong>Next-Generation Firewall (NGFW):</strong> Includes IDS/IPS, application awareness, and threat intelligence</li>
</ul>
<h3>Firewall Rules Best Practices</h3>
<ul>
<li>Default deny - block everything not explicitly allowed</li>
<li>Principle of least privilege - only allow necessary traffic</li>
<li>Regular rule review and cleanup</li>
</ul>
<h3>IDS vs IPS</h3>
<ul>
<li><strong>IDS (Intrusion Detection System):</strong> Monitors and alerts on suspicious activity</li>
<li><strong>IPS (Intrusion Prevention System):</strong> Actively blocks detected threats in real-time</li>
</ul>',
'Video', 35, 1, 0, 1, 15, NOW()),

(@module4_id, 'Encryption and Cryptography',
'<h2>Encryption and Cryptography</h2>
<p>Cryptography protects data confidentiality and integrity through mathematical algorithms.</p>
<h3>Types of Encryption</h3>
<ul>
<li><strong>Symmetric Encryption:</strong> Same key for encryption and decryption (AES, DES)</li>
<li><strong>Asymmetric Encryption:</strong> Public/private key pair (RSA, ECC)</li>
<li><strong>Hashing:</strong> One-way function producing fixed-size output (SHA-256)</li>
</ul>
<h3>Digital Certificates and PKI</h3>
<ul>
<li><strong>Certificate Authority (CA):</strong> Issues and validates certificates</li>
<li><strong>SSL/TLS Certificates:</strong> Enable HTTPS for secure websites</li>
</ul>
<h3>Practical Applications</h3>
<ul>
<li>HTTPS for secure web browsing</li>
<li>VPN encryption for remote access</li>
<li>Full disk encryption (BitLocker, FileVault)</li>
</ul>',
'Video', 40, 2, 0, 1, 15, NOW()),

(@module4_id, 'Access Control and Authentication',
'<h2>Access Control and Authentication</h2>
<p>Controlling who can access resources is fundamental to security.</p>
<h3>Authentication Factors</h3>
<ul>
<li><strong>Something you know:</strong> Passwords, PINs</li>
<li><strong>Something you have:</strong> Smart cards, tokens, mobile phones</li>
<li><strong>Something you are:</strong> Biometrics (fingerprint, face, iris)</li>
</ul>
<h3>Multi-Factor Authentication (MFA)</h3>
<p>MFA requires two or more authentication factors. It significantly reduces account compromise risk.</p>
<h3>Access Control Models</h3>
<ul>
<li><strong>RBAC:</strong> Access based on user roles</li>
<li><strong>MAC:</strong> System-enforced based on security labels</li>
<li><strong>DAC:</strong> Resource owner controls access</li>
</ul>
<h3>Password Security Best Practices</h3>
<ul>
<li>Minimum 12 characters with complexity</li>
<li>Use password managers</li>
<li>Unique passwords for each account</li>
</ul>',
'Video', 35, 3, 0, 1, 15, NOW()),

(@module4_id, 'Module 4: Knowledge Check',
'<h2>Module 4 Knowledge Check</h2>
<p>Test your knowledge of security controls and defense.</p>',
'Quiz', 25, 4, 0, 1, 25, NOW());

-- ============================================
-- 8. INSERT LESSONS - MODULE 5
-- ============================================
INSERT INTO lessons (module_id, title, content, lesson_type, duration_minutes, display_order, is_preview, is_mandatory, points, created_at) VALUES
(@module5_id, 'OWASP Top 10 Vulnerabilities',
'<h2>OWASP Top 10 Vulnerabilities</h2>
<p>The OWASP Top 10 is a standard awareness document representing the most critical security risks to web applications.</p>
<h3>OWASP Top 10 (2021)</h3>
<ol>
<li><strong>A01: Broken Access Control:</strong> Restrictions on authenticated users are not properly enforced</li>
<li><strong>A02: Cryptographic Failures:</strong> Sensitive data exposed due to weak or missing encryption</li>
<li><strong>A03: Injection:</strong> Untrusted data sent to interpreters (SQL, NoSQL, OS command)</li>
<li><strong>A04: Insecure Design:</strong> Fundamental design flaws in the application</li>
<li><strong>A05: Security Misconfiguration:</strong> Default configurations, incomplete setups</li>
<li><strong>A06: Vulnerable Components:</strong> Using outdated or vulnerable libraries</li>
<li><strong>A07: Authentication Failures:</strong> Flaws in authentication mechanisms</li>
<li><strong>A08: Data Integrity Failures:</strong> Untrusted code or data without verification</li>
<li><strong>A09: Logging Failures:</strong> Insufficient logging and monitoring</li>
<li><strong>A10: Server-Side Request Forgery (SSRF):</strong> Server making unauthorized requests</li>
</ol>',
'Video', 40, 1, 0, 1, 15, NOW()),

(@module5_id, 'SQL Injection and XSS',
'<h2>SQL Injection and Cross-Site Scripting (XSS)</h2>
<p>These are two of the most common and dangerous web application vulnerabilities.</p>
<h3>SQL Injection (SQLi)</h3>
<p>SQL Injection occurs when untrusted user input is concatenated into SQL queries. The best defense is using parameterized queries (prepared statements).</p>
<h3>Cross-Site Scripting (XSS)</h3>
<p>XSS allows attackers to inject malicious scripts into web pages viewed by other users:</p>
<ul>
<li><strong>Stored XSS:</strong> Malicious script stored on the server</li>
<li><strong>Reflected XSS:</strong> Script in URL parameters reflected in page response</li>
<li><strong>DOM-based XSS:</strong> Client-side JavaScript manipulates DOM unsafely</li>
</ul>
<h4>Prevention</h4>
<ul>
<li>Output encoding (HTML, JavaScript, URL)</li>
<li>Content Security Policy (CSP) headers</li>
<li>Input validation</li>
</ul>',
'Video', 40, 2, 0, 1, 15, NOW()),

(@module5_id, 'Secure Coding Practices',
'<h2>Secure Coding Practices</h2>
<p>Writing secure code from the start prevents vulnerabilities before they reach production.</p>
<h3>Input Validation</h3>
<ul>
<li>Validate all input on the server side</li>
<li>Use whitelist validation (accept known good)</li>
<li>Validate data type, length, format, and range</li>
</ul>
<h3>Security Headers</h3>
<ul>
<li><strong>Content-Security-Policy:</strong> Prevents XSS and data injection</li>
<li><strong>X-Frame-Options:</strong> Prevents clickjacking</li>
<li><strong>Strict-Transport-Security:</strong> Enforces HTTPS</li>
</ul>
<h3>Authentication and Session Management</h3>
<ul>
<li>Use strong, proven authentication libraries</li>
<li>Implement secure session handling (HttpOnly, Secure, SameSite cookies)</li>
<li>Never expose stack traces or database errors to users</li>
</ul>',
'Video', 35, 3, 0, 1, 15, NOW()),

(@module5_id, 'Module 5: Knowledge Check',
'<h2>Module 5 Knowledge Check</h2>
<p>Test your web security knowledge.</p>',
'Quiz', 30, 4, 0, 1, 25, NOW());

-- ============================================
-- 9. INSERT LESSONS - MODULE 6
-- ============================================
INSERT INTO lessons (module_id, title, content, lesson_type, duration_minutes, display_order, is_preview, is_mandatory, points, created_at) VALUES
(@module6_id, 'Introduction to Ethical Hacking',
'<h2>Introduction to Ethical Hacking</h2>
<p>Ethical hacking involves authorized attempts to gain unauthorized access to systems to identify security weaknesses.</p>
<h3>White Hat vs Black Hat vs Gray Hat</h3>
<ul>
<li><strong>White Hat:</strong> Authorized hackers who help organizations improve security</li>
<li><strong>Black Hat:</strong> Malicious hackers who exploit vulnerabilities for personal gain</li>
<li><strong>Gray Hat:</strong> Hackers who operate without authorization but without malicious intent</li>
</ul>
<h3>Legal and Ethical Considerations</h3>
<ul>
<li>Always have written authorization (Rules of Engagement)</li>
<li>Define scope and boundaries clearly</li>
<li>Report all findings responsibly</li>
<li>Do not cause damage or disruption</li>
</ul>
<h3>Penetration Testing Types</h3>
<ul>
<li><strong>Black Box:</strong> No prior knowledge of the target</li>
<li><strong>White Box:</strong> Full knowledge of systems and architecture</li>
<li><strong>Gray Box:</strong> Partial knowledge (most common)</li>
</ul>',
'Video', 30, 1, 0, 1, 15, NOW()),

(@module6_id, 'Reconnaissance and Scanning',
'<h2>Reconnaissance and Scanning</h2>
<p>Information gathering is the first and most critical phase of ethical hacking.</p>
<h3>Passive Reconnaissance</h3>
<ul>
<li><strong>OSINT:</strong> Publicly available information</li>
<li><strong>Google Dorking:</strong> Advanced search techniques</li>
<li><strong>Whois and DNS:</strong> Domain registration and DNS records</li>
</ul>
<h3>Active Reconnaissance</h3>
<ul>
<li><strong>Port Scanning:</strong> Identifying open ports and services (Nmap)</li>
<li><strong>Service Enumeration:</strong> Identifying software versions</li>
</ul>
<h3>Nmap Basics</h3>
<pre class="bg-gray-100 p-3 rounded"><code>nmap -sS target.com        # TCP SYN scan
nmap -sV target.com        # Service version detection
nmap -O target.com         # OS detection</code></pre>
<h3>Vulnerability Scanning</h3>
<ul>
<li><strong>Nessus:</strong> Comprehensive vulnerability scanner</li>
<li><strong>OpenVAS:</strong> Open-source vulnerability scanner</li>
<li><strong>Nikto:</strong> Web server vulnerability scanner</li>
</ul>',
'Video', 40, 2, 0, 1, 15, NOW()),

(@module6_id, 'Vulnerability Exploitation Basics',
'<h2>Vulnerability Exploitation Basics</h2>
<p>Understanding exploitation helps defenders understand what they are protecting against.</p>
<h3>Exploit Databases and Resources</h3>
<ul>
<li><strong>Exploit-DB:</strong> Archive of public exploits</li>
<li><strong>CVE:</strong> Standardized vulnerability identifiers</li>
</ul>
<h3>Metasploit Framework</h3>
<p>Metasploit is the world''s most used penetration testing framework with exploits, payloads, and auxiliary modules.</p>
<h3>Responsible Disclosure</h3>
<p>When vulnerabilities are discovered:</p>
<ol>
<li>Notify the organization privately</li>
<li>Allow reasonable time for remediation</li>
<li>Coordinate public disclosure</li>
<li>Never exploit for personal gain</li>
</ol>',
'Video', 35, 3, 0, 1, 15, NOW()),

(@module6_id, 'Module 6: Knowledge Check',
'<h2>Module 6 Knowledge Check</h2>
<p>Test your ethical hacking knowledge.</p>',
'Quiz', 25, 4, 0, 1, 25, NOW());

-- ============================================
-- 10. INSERT LESSONS - MODULE 7
-- ============================================
INSERT INTO lessons (module_id, title, content, lesson_type, duration_minutes, display_order, is_preview, is_mandatory, points, created_at) VALUES
(@module7_id, 'Incident Response Process',
'<h2>Incident Response Process</h2>
<p>An effective incident response plan minimizes damage and recovery time when security breaches occur.</p>
<h3>NIST Incident Response Lifecycle</h3>
<ol>
<li><strong>Preparation:</strong> Establish policies, tools, and trained response team</li>
<li><strong>Detection and Analysis:</strong> Identify and assess security incidents</li>
<li><strong>Containment:</strong> Limit the scope and impact of the incident</li>
<li><strong>Eradication:</strong> Remove threats and vulnerabilities</li>
<li><strong>Recovery:</strong> Restore systems to normal operation</li>
<li><strong>Post-Incident Activity:</strong> Learn and improve</li>
</ol>
<h3>Incident Classification</h3>
<ul>
<li><strong>Severity Levels:</strong> Critical, High, Medium, Low</li>
<li><strong>Incident Types:</strong> Malware, Unauthorized Access, Data Breach, DDoS</li>
</ul>
<h3>First Response Priorities</h3>
<ul>
<li>Preserve evidence</li>
<li>Contain the threat</li>
<li>Document everything</li>
<li>Escalate appropriately</li>
</ul>',
'Video', 35, 1, 0, 1, 15, NOW()),

(@module7_id, 'Digital Forensics Fundamentals',
'<h2>Digital Forensics Fundamentals</h2>
<p>Digital forensics involves collecting, preserving, and analyzing digital evidence.</p>
<h3>Forensics Principles</h3>
<ul>
<li><strong>Evidence Integrity:</strong> Maintain chain of custody</li>
<li><strong>Documentation:</strong> Record every action taken</li>
<li><strong>Repeatability:</strong> Results must be reproducible</li>
</ul>
<h3>Evidence Collection</h3>
<ul>
<li><strong>Live Data:</strong> Running processes, network connections, memory</li>
<li><strong>Disk Images:</strong> Bit-for-bit copies of storage media</li>
<li><strong>Log Files:</strong> System, application, and security logs</li>
</ul>
<h3>Forensics Tools</h3>
<ul>
<li><strong>Autopsy:</strong> Open-source digital forensics platform</li>
<li><strong>Volatility:</strong> Memory forensics framework</li>
</ul>',
'Video', 35, 2, 0, 1, 15, NOW()),

(@module7_id, 'Incident Reporting and Documentation',
'<h2>Incident Reporting and Documentation</h2>
<p>Proper documentation is essential for legal, compliance, and improvement purposes.</p>
<h3>Incident Report Components</h3>
<ul>
<li><strong>Executive Summary:</strong> High-level overview for leadership</li>
<li><strong>Timeline:</strong> Chronological sequence of events</li>
<li><strong>Technical Details:</strong> Indicators of compromise, affected systems</li>
<li><strong>Impact Assessment:</strong> Data, financial, and reputational impact</li>
<li><strong>Root Cause Analysis:</strong> How the incident occurred</li>
<li><strong>Recommendations:</strong> Preventive measures</li>
</ul>
<h3>Regulatory Requirements in Zambia</h3>
<p>Organizations must be aware of the Data Protection Act requirements for data breach notification.</p>',
'Video', 25, 3, 0, 1, 15, NOW()),

(@module7_id, 'Module 7: Knowledge Check',
'<h2>Module 7 Knowledge Check</h2>
<p>Test your incident response knowledge.</p>',
'Quiz', 25, 4, 0, 1, 25, NOW());

-- ============================================
-- 11. INSERT LESSONS - MODULE 8
-- ============================================
INSERT INTO lessons (module_id, title, content, lesson_type, duration_minutes, display_order, is_preview, is_mandatory, points, created_at) VALUES
(@module8_id, 'Introduction to SIEM',
'<h2>Introduction to SIEM</h2>
<p>Security Information and Event Management (SIEM) systems collect and analyze security data from across an organization.</p>
<h3>What SIEM Does</h3>
<ul>
<li><strong>Log Collection:</strong> Aggregates logs from firewalls, servers, applications</li>
<li><strong>Correlation:</strong> Identifies patterns across multiple data sources</li>
<li><strong>Alerting:</strong> Generates alerts based on predefined rules</li>
<li><strong>Dashboards:</strong> Visualizes security posture</li>
</ul>
<h3>Popular SIEM Tools</h3>
<ul>
<li><strong>Splunk:</strong> Enterprise SIEM with powerful search capabilities</li>
<li><strong>Microsoft Sentinel:</strong> Cloud-native SIEM and SOAR</li>
<li><strong>Elastic Stack (ELK):</strong> Open-source log analysis platform</li>
<li><strong>Wazuh:</strong> Open-source security monitoring</li>
</ul>',
'Video', 30, 1, 0, 1, 15, NOW()),

(@module8_id, 'Log Analysis and Monitoring',
'<h2>Log Analysis and Monitoring</h2>
<p>Effective log analysis is crucial for detecting and investigating security incidents.</p>
<h3>Important Log Sources</h3>
<ul>
<li><strong>Operating System Logs:</strong> Windows Event Logs, Linux Syslog</li>
<li><strong>Firewall Logs:</strong> Connection attempts, blocked traffic</li>
<li><strong>Web Server Logs:</strong> HTTP requests, errors, access patterns</li>
<li><strong>Authentication Logs:</strong> Login attempts, privilege changes</li>
</ul>
<h3>What to Look For</h3>
<ul>
<li>Multiple failed login attempts (brute force)</li>
<li>Logins outside business hours</li>
<li>Unusual data transfer volumes</li>
<li>Known malicious IP addresses</li>
<li>Missing or modified log files</li>
</ul>',
'Video', 30, 2, 0, 1, 15, NOW()),

(@module8_id, 'SOC Operations and Workflow',
'<h2>SOC Operations and Workflow</h2>
<p>A Security Operations Center (SOC) is a centralized function that monitors and responds to security incidents.</p>
<h3>SOC Tiers</h3>
<ul>
<li><strong>Tier 1 (Alert Triage):</strong> Initial alert review and prioritization</li>
<li><strong>Tier 2 (Incident Response):</strong> Investigation and containment</li>
<li><strong>Tier 3 (Threat Hunting):</strong> Proactive threat identification</li>
</ul>
<h3>Key SOC Metrics</h3>
<ul>
<li><strong>MTTD (Mean Time to Detect):</strong> Average time to detect threats</li>
<li><strong>MTTR (Mean Time to Respond):</strong> Average time to respond to incidents</li>
</ul>
<h3>MITRE ATT&CK Framework</h3>
<p>MITRE ATT&CK is a globally accessible knowledge base of adversary tactics and techniques used for threat modeling and detection.</p>',
'Video', 35, 3, 0, 1, 15, NOW()),

(@module8_id, 'Module 8: Knowledge Check',
'<h2>Module 8 Knowledge Check</h2>
<p>Test your SOC knowledge.</p>',
'Quiz', 25, 4, 0, 1, 25, NOW());

-- ============================================
-- 12. INSERT LESSONS - MODULE 9
-- ============================================
INSERT INTO lessons (module_id, title, content, lesson_type, duration_minutes, display_order, is_preview, is_mandatory, points, created_at) VALUES
(@module9_id, 'Security Frameworks and Standards',
'<h2>Security Frameworks and Standards</h2>
<p>Security frameworks provide structured approaches to managing cybersecurity risks.</p>
<h3>NIST Cybersecurity Framework</h3>
<p>The NIST CSF provides a policy framework of computer security guidance:</p>
<ul>
<li><strong>Identify:</strong> Understand and manage cybersecurity risk</li>
<li><strong>Protect:</strong> Implement safeguards to ensure service delivery</li>
<li><strong>Detect:</strong> Implement activities to identify events</li>
<li><strong>Respond:</strong> Take action on detected incidents</li>
<li><strong>Recover:</strong> Restore capabilities after incidents</li>
</ul>
<h3>ISO 27001</h3>
<p>International standard for information security management systems (ISMS).</p>
<h3>Other Important Standards</h3>
<ul>
<li><strong>CIS Controls:</strong> 20 prioritized security controls</li>
<li><strong>PCI DSS:</strong> Payment card industry security standard</li>
</ul>',
'Video', 30, 1, 0, 1, 15, NOW()),

(@module9_id, 'Risk Management',
'<h2>Risk Management</h2>
<p>Risk management is the process of identifying, assessing, and controlling threats to an organization.</p>
<h3>Risk Assessment Process</h3>
<ol>
<li><strong>Asset Identification:</strong> What needs protection?</li>
<li><strong>Threat Identification:</strong> What could go wrong?</li>
<li><strong>Vulnerability Assessment:</strong> What weaknesses exist?</li>
<li><strong>Risk Calculation:</strong> Risk = Likelihood x Impact</li>
</ol>
<h3>Risk Treatment Options</h3>
<ul>
<li><strong>Accept:</strong> Acknowledge and bear the risk</li>
<li><strong>Mitigate:</strong> Reduce likelihood or impact</li>
<li><strong>Transfer:</strong> Insurance or outsourcing</li>
<li><strong>Avoid:</strong> Eliminate the risk source</li>
</ul>',
'Video', 30, 2, 0, 1, 15, NOW()),

(@module9_id, 'Compliance and Audit',
'<h2>Compliance and Audit</h2>
<p>Compliance ensures organizations meet regulatory and industry security requirements.</p>
<h3>Types of Audits</h3>
<ul>
<li><strong>Internal Audit:</strong> Conducted by organization''s own audit team</li>
<li><strong>External Audit:</strong> Independent third-party assessment</li>
<li><strong>Regulatory Audit:</strong> Government-mandated compliance check</li>
</ul>
<h3>Zambia Data Protection Act</h3>
<p>Organizations in Zambia must comply with the Data Protection Act which requires:</p>
<ul>
<li>Lawful processing of personal data</li>
<li>Data subject rights (access, correction, deletion)</li>
<li>Data breach notification requirements</li>
</ul>',
'Video', 25, 3, 0, 1, 15, NOW()),

(@module9_id, 'Module 9: Knowledge Check',
'<h2>Module 9 Knowledge Check</h2>
<p>Test your GRC knowledge.</p>',
'Quiz', 25, 4, 0, 1, 25, NOW());

-- ============================================
-- 13. INSERT LESSONS - MODULE 10 (Capstone)
-- ============================================
INSERT INTO lessons (module_id, title, content, lesson_type, duration_minutes, display_order, is_preview, is_mandatory, points, created_at) VALUES
(@module10_id, 'Capstone Project Overview',
'<h2>Capstone Project Overview</h2>
<p>This capstone project allows you to apply all the skills you have learned throughout the course to a real-world scenario.</p>
<h3>Project Scenario</h3>
<p>You are hired as a Junior Security Analyst for a small financial services company in Lusaka. The company has 50 employees and processes mobile money transactions. They have recently experienced a phishing attack and want to improve their security posture.</p>
<h3>Your Tasks</h3>
<ol>
<li><strong>Risk Assessment:</strong> Identify and assess key risks to the organization</li>
<li><strong>Security Policy:</strong> Create an acceptable use policy</li>
<li><strong>Network Design:</strong> Propose a secure network architecture</li>
<li><strong>Incident Response Plan:</strong> Develop a basic incident response plan</li>
<li><strong>Security Awareness:</strong> Create a training outline for employees</li>
</ol>',
'Assignment', 45, 1, 0, 1, 100, NOW()),

(@module10_id, 'Career Preparation and Next Steps',
'<h2>Career Preparation and Next Steps</h2>
<p>Congratulations on completing the Cybersecurity Fundamentals course!</p>
<h3>What You Have Learned</h3>
<ul>
<li>Core cybersecurity principles (CIA Triad, threat landscape)</li>
<li>Networking fundamentals and security</li>
<li>Malware, social engineering, and attack vectors</li>
<li>Security controls (firewalls, encryption, access control)</li>
<li>Web application security and secure coding</li>
<li>Ethical hacking basics and methodology</li>
<li>Incident response and digital forensics</li>
<li>Security operations and SIEM</li>
<li>Governance, risk, and compliance</li>
</ul>
<h3>Recommended Next Steps</h3>
<ol>
<li><strong>Hands-On Practice:</strong> Set up a home lab with virtual machines</li>
<li><strong>Online Platforms:</strong> TryHackMe, Hack The Box, PortSwigger Web Security Academy</li>
<li><strong>Certification Path:</strong> Consider CompTIA Security+</li>
<li><strong>Networking:</strong> Join cybersecurity communities and attend local events</li>
</ol>',
'Video', 25, 2, 0, 1, 10, NOW()),

(@module10_id, 'Final Assessment',
'<h2>Final Assessment</h2>
<p>Comprehensive final examination covering all modules.</p>',
'Quiz', 60, 3, 0, 1, 150, NOW());

-- ============================================
-- 14. INSERT QUIZZES
-- ============================================
INSERT INTO quizzes (course_id, lesson_id, title, description, quiz_type, time_limit_minutes, max_attempts, passing_score, randomize_questions, show_correct_answers, is_published, created_at) VALUES
-- Module 1 Quiz
(@course_id, NULL, 'Module 1 Quiz: Introduction to Cybersecurity', 'Test your understanding of cybersecurity fundamentals', 'Graded', 20, 3, 70.00, 1, 1, 1, NOW()),
-- Module 2 Quiz
(@course_id, NULL, 'Module 2 Quiz: Networking Fundamentals', 'Test your networking knowledge', 'Graded', 25, 3, 70.00, 1, 1, 1, NOW()),
-- Module 3 Quiz
(@course_id, NULL, 'Module 3 Quiz: Cyber Threats and Attacks', 'Test your knowledge of cyber threats', 'Graded', 25, 3, 70.00, 1, 1, 1, NOW()),
-- Module 4 Quiz
(@course_id, NULL, 'Module 4 Quiz: Security Controls and Defense', 'Test your knowledge of security controls', 'Graded', 25, 3, 70.00, 1, 1, 1, NOW()),
-- Module 5 Quiz
(@course_id, NULL, 'Module 5 Quiz: Web Application Security', 'Test your web security knowledge', 'Graded', 30, 3, 70.00, 1, 1, 1, NOW()),
-- Module 6 Quiz
(@course_id, NULL, 'Module 6 Quiz: Ethical Hacking Basics', 'Test your ethical hacking knowledge', 'Graded', 25, 3, 70.00, 1, 1, 1, NOW()),
-- Module 7 Quiz
(@course_id, NULL, 'Module 7 Quiz: Incident Response and Forensics', 'Test your incident response knowledge', 'Graded', 25, 3, 70.00, 1, 1, 1, NOW()),
-- Module 8 Quiz
(@course_id, NULL, 'Module 8 Quiz: Security Operations', 'Test your SOC knowledge', 'Graded', 25, 3, 70.00, 1, 1, 1, NOW()),
-- Module 9 Quiz
(@course_id, NULL, 'Module 9 Quiz: Governance, Risk, and Compliance', 'Test your GRC knowledge', 'Graded', 25, 3, 70.00, 1, 1, 1, NOW()),
-- Final Assessment
(@course_id, NULL, 'Final Assessment: Cybersecurity Fundamentals', 'Comprehensive final assessment covering all modules', 'Final Exam', 60, 2, 75.00, 1, 0, 1, NOW());

SET @quiz1_id = LAST_INSERT_ID();
SET @quiz2_id = @quiz1_id + 1;
SET @quiz3_id = @quiz1_id + 2;
SET @quiz4_id = @quiz1_id + 3;
SET @quiz5_id = @quiz1_id + 4;
SET @quiz6_id = @quiz1_id + 5;
SET @quiz7_id = @quiz1_id + 6;
SET @quiz8_id = @quiz1_id + 7;
SET @quiz9_id = @quiz1_id + 8;
SET @quiz_final_id = @quiz1_id + 9;

-- ============================================
-- 15. INSERT QUESTIONS - MODULE 1 QUIZ
-- ============================================
INSERT INTO questions (question_type, question_text, points, explanation, created_at) VALUES
('Multiple Choice', 'What does the "C" in the CIA Triad stand for?', 1, 'The CIA Triad consists of Confidentiality, Integrity, and Availability - the three core principles of cybersecurity.', NOW()),
('Multiple Choice', 'Which of the following is NOT a type of threat actor?', 1, 'System Administrators are typically defenders, not threat actors.', NOW()),
('Multiple Choice', 'A weakness in a system that could be exploited is called a(n):', 1, 'A vulnerability is a weakness in a system. A threat is a potential danger. Risk is the combination of threat and vulnerability.', NOW()),
('Multiple Choice', 'What is the primary motivation of cybercriminals?', 1, 'Cybercriminals are primarily motivated by financial gain through activities like ransomware, fraud, and data theft.', NOW()),
('Multiple Choice', 'Which principle ensures data is accessible when needed?', 1, 'Availability ensures that systems and data are accessible to authorized users when they need them.', NOW());

SET @q1_1 = LAST_INSERT_ID();
SET @q1_2 = @q1_1 + 1;
SET @q1_3 = @q1_1 + 2;
SET @q1_4 = @q1_1 + 3;
SET @q1_5 = @q1_1 + 4;

INSERT INTO question_options (question_id, option_text, is_correct, display_order) VALUES
(@q1_1, 'Control', 0, 1), (@q1_1, 'Confidentiality', 1, 2), (@q1_1, 'Certification', 0, 3), (@q1_1, 'Compliance', 0, 4),
(@q1_2, 'Script Kiddie', 0, 1), (@q1_2, 'Cybercriminal', 0, 2), (@q1_2, 'System Administrator', 1, 3), (@q1_2, 'State-Sponsored Actor', 0, 4),
(@q1_3, 'Threat', 0, 1), (@q1_3, 'Risk', 0, 2), (@q1_3, 'Vulnerability', 1, 3), (@q1_3, 'Exploit', 0, 4),
(@q1_4, 'Political change', 0, 1), (@q1_4, 'Social justice', 0, 2), (@q1_4, 'Financial gain', 1, 3), (@q1_4, 'Personal recognition', 0, 4),
(@q1_5, 'Confidentiality', 0, 1), (@q1_5, 'Integrity', 0, 2), (@q1_5, 'Availability', 1, 3), (@q1_5, 'Authentication', 0, 4);

INSERT INTO quiz_questions (quiz_id, question_id, display_order) VALUES
(@quiz1_id, @q1_1, 1), (@quiz1_id, @q1_2, 2), (@quiz1_id, @q1_3, 3), (@quiz1_id, @q1_4, 4), (@quiz1_id, @q1_5, 5);

-- ============================================
-- 16. INSERT QUESTIONS - MODULE 2 QUIZ
-- ============================================
INSERT INTO questions (question_type, question_text, points, explanation, created_at) VALUES
('Multiple Choice', 'At which OSI layer does routing occur?', 1, 'Routing occurs at Layer 3 (Network Layer) where IP addressing and logical network paths are managed.', NOW()),
('Multiple Choice', 'Which protocol uses port 443 by default?', 1, 'HTTPS (HTTP Secure) uses port 443 and encrypts web traffic using TLS/SSL.', NOW()),
('Multiple Choice', 'What does ARP stand for?', 1, 'ARP (Address Resolution Protocol) maps IP addresses to MAC addresses on local networks.', NOW()),
('Multiple Choice', 'Which is the encrypted alternative to Telnet?', 1, 'SSH (Secure Shell) provides encrypted remote access, replacing the insecure Telnet protocol.', NOW()),
('Multiple Choice', 'Network segmentation using VLANs primarily helps with:', 1, 'VLANs segment networks to contain breaches and limit lateral movement by attackers.', NOW());

SET @q2_1 = LAST_INSERT_ID();
SET @q2_2 = @q2_1 + 1;
SET @q2_3 = @q2_1 + 2;
SET @q2_4 = @q2_1 + 3;
SET @q2_5 = @q2_1 + 4;

INSERT INTO question_options (question_id, option_text, is_correct, display_order) VALUES
(@q2_1, 'Layer 2', 0, 1), (@q2_1, 'Layer 3', 1, 2), (@q2_1, 'Layer 4', 0, 3), (@q2_1, 'Layer 7', 0, 4),
(@q2_2, 'HTTP', 0, 1), (@q2_2, 'FTP', 0, 2), (@q2_2, 'HTTPS', 1, 3), (@q2_2, 'SSH', 0, 4),
(@q2_3, 'Address Resolution Protocol', 1, 1), (@q2_3, 'Advanced Routing Protocol', 0, 2), (@q2_3, 'Application Resource Protocol', 0, 3), (@q2_3, 'Automatic Response Procedure', 0, 4),
(@q2_4, 'FTP', 0, 1), (@q2_4, 'SSH', 1, 2), (@q2_4, 'HTTP', 0, 3), (@q2_4, 'SMTP', 0, 4),
(@q2_5, 'Increasing internet speed', 0, 1), (@q2_5, 'Limiting attack spread', 1, 2), (@q2_5, 'Reducing cable costs', 0, 3), (@q2_5, 'Improving wireless signal', 0, 4);

INSERT INTO quiz_questions (quiz_id, question_id, display_order) VALUES
(@quiz2_id, @q2_1, 1), (@quiz2_id, @q2_2, 2), (@quiz2_id, @q2_3, 3), (@quiz2_id, @q2_4, 4), (@quiz2_id, @q2_5, 5);

-- ============================================
-- 17. INSERT QUESTIONS - MODULE 3 QUIZ
-- ============================================
INSERT INTO questions (question_type, question_text, points, explanation, created_at) VALUES
('Multiple Choice', 'Which malware type encrypts files and demands payment?', 1, 'Ransomware encrypts victims'' files and demands payment for the decryption key.', NOW()),
('Multiple Choice', 'What is phishing?', 1, 'Phishing is a social engineering attack where attackers impersonate trusted entities to steal credentials.', NOW()),
('Multiple Choice', 'In the Cyber Kill Chain, what comes after "Delivery"?', 1, 'The Cyber Kill Chain phases are: Reconnaissance -> Weaponization -> Delivery -> Exploitation -> Installation -> Command & Control -> Actions on Objectives.', NOW()),
('Multiple Choice', 'Which of the following is a social engineering technique?', 1, 'Tailgating (following someone into a restricted area) is a physical social engineering technique.', NOW()),
('Multiple Choice', 'What is the primary purpose of a keylogger?', 1, 'A keylogger records keystrokes to capture passwords and other sensitive information.', NOW());

SET @q3_1 = LAST_INSERT_ID();
SET @q3_2 = @q3_1 + 1;
SET @q3_3 = @q3_1 + 2;
SET @q3_4 = @q3_1 + 3;
SET @q3_5 = @q3_1 + 4;

INSERT INTO question_options (question_id, option_text, is_correct, display_order) VALUES
(@q3_1, 'Virus', 0, 1), (@q3_1, 'Worm', 0, 2), (@q3_1, 'Ransomware', 1, 3), (@q3_1, 'Spyware', 0, 4),
(@q3_2, 'A network scanning technique', 0, 1), (@q3_2, 'A fraudulent attempt to obtain sensitive information', 1, 2), (@q3_2, 'A type of firewall', 0, 3), (@q3_2, 'A password hashing method', 0, 4),
(@q3_3, 'Reconnaissance', 0, 1), (@q3_3, 'Weaponization', 0, 2), (@q3_3, 'Exploitation', 1, 3), (@q3_3, 'Installation', 0, 4),
(@q3_4, 'SQL Injection', 0, 1), (@q3_4, 'Tailgating', 1, 2), (@q3_4, 'Buffer Overflow', 0, 3), (@q3_4, 'ARP Spoofing', 0, 4),
(@q3_5, 'Encrypt files', 0, 1), (@q3_5, 'Record keystrokes', 1, 2), (@q3_5, 'Scan networks', 0, 3), (@q3_5, 'Block websites', 0, 4);

INSERT INTO quiz_questions (quiz_id, question_id, display_order) VALUES
(@quiz3_id, @q3_1, 1), (@quiz3_id, @q3_2, 2), (@quiz3_id, @q3_3, 3), (@quiz3_id, @q3_4, 4), (@quiz3_id, @q3_5, 5);

-- ============================================
-- 18. INSERT QUESTIONS - MODULE 4 QUIZ
-- ============================================
INSERT INTO questions (question_type, question_text, points, explanation, created_at) VALUES
('Multiple Choice', 'Which encryption type uses the same key for encryption and decryption?', 1, 'Symmetric encryption uses a single shared key for both encryption and decryption.', NOW()),
('Multiple Choice', 'What does MFA stand for?', 1, 'Multi-Factor Authentication requires users to provide two or more verification factors.', NOW()),
('Multiple Choice', 'In RBAC, access is determined by:', 1, 'Role-Based Access Control grants access based on a user''s role within an organization.', NOW()),
('Multiple Choice', 'Which system actively blocks detected threats in real-time?', 1, 'Intrusion Prevention Systems (IPS) actively block threats, while IDS only detect and alert.', NOW()),
('Multiple Choice', 'TLS is used for:', 1, 'TLS (Transport Layer Security) encrypts web traffic and enables HTTPS.', NOW());

SET @q4_1 = LAST_INSERT_ID();
SET @q4_2 = @q4_1 + 1;
SET @q4_3 = @q4_1 + 2;
SET @q4_4 = @q4_1 + 3;
SET @q4_5 = @q4_1 + 4;

INSERT INTO question_options (question_id, option_text, is_correct, display_order) VALUES
(@q4_1, 'Asymmetric', 0, 1), (@q4_1, 'Symmetric', 1, 2), (@q4_1, 'Hashing', 0, 3), (@q4_1, 'Public-key', 0, 4),
(@q4_2, 'Multi-Factor Authentication', 1, 1), (@q4_2, 'Managed Firewall Access', 0, 2), (@q4_2, 'Multi-Function Application', 0, 3), (@q4_2, 'Modular Framework Architecture', 0, 4),
(@q4_3, 'User attributes', 0, 1), (@q4_3, 'Resource ownership', 0, 2), (@q4_3, 'User roles', 1, 3), (@q4_3, 'Security labels', 0, 4),
(@q4_4, 'IDS', 0, 1), (@q4_4, 'IPS', 1, 2), (@q4_4, 'Firewall', 0, 3), (@q4_4, 'Proxy', 0, 4),
(@q4_5, 'File compression', 0, 1), (@q4_5, 'Secure web communication', 1, 2), (@q4_5, 'Database indexing', 0, 3), (@q4_5, 'Email routing', 0, 4);

INSERT INTO quiz_questions (quiz_id, question_id, display_order) VALUES
(@quiz4_id, @q4_1, 1), (@quiz4_id, @q4_2, 2), (@quiz4_id, @q4_3, 3), (@quiz4_id, @q4_4, 4), (@quiz4_id, @q4_5, 5);

-- ============================================
-- 19. INSERT QUESTIONS - MODULE 5 QUIZ
-- ============================================
INSERT INTO questions (question_type, question_text, points, explanation, created_at) VALUES
('Multiple Choice', 'Which OWASP Top 10 item involves untrusted data sent to interpreters?', 1, 'Injection flaws occur when untrusted data is sent to interpreters as part of a command or query.', NOW()),
('Multiple Choice', 'What is the best defense against SQL Injection?', 1, 'Parameterized queries separate SQL code from data, making SQL injection impossible.', NOW()),
('Multiple Choice', 'Which XSS type stores the malicious script on the server?', 1, 'Stored (Persistent) XSS occurs when malicious scripts are permanently stored on the target server.', NOW()),
('Multiple Choice', 'Which header prevents clickjacking attacks?', 1, 'X-Frame-Options controls whether a page can be displayed in frames, preventing clickjacking.', NOW()),
('Multiple Choice', 'What does CSP stand for in web security?', 1, 'Content Security Policy helps prevent XSS and data injection attacks.', NOW());

SET @q5_1 = LAST_INSERT_ID();
SET @q5_2 = @q5_1 + 1;
SET @q5_3 = @q5_1 + 2;
SET @q5_4 = @q5_1 + 3;
SET @q5_5 = @q5_1 + 4;

INSERT INTO question_options (question_id, option_text, is_correct, display_order) VALUES
(@q5_1, 'Broken Access Control', 0, 1), (@q5_1, 'Cryptographic Failures', 0, 2), (@q5_1, 'Injection', 1, 3), (@q5_1, 'Insecure Design', 0, 4),
(@q5_2, 'Input validation only', 0, 1), (@q5_2, 'Parameterized queries', 1, 2), (@q5_2, 'Firewall rules', 0, 3), (@q5_2, 'URL encoding', 0, 4),
(@q5_3, 'Reflected XSS', 0, 1), (@q5_3, 'Stored XSS', 1, 2), (@q5_3, 'DOM-based XSS', 0, 3), (@q5_3, 'Blind XSS', 0, 4),
(@q5_4, 'Content-Security-Policy', 0, 1), (@q5_4, 'X-Frame-Options', 1, 2), (@q5_4, 'X-XSS-Protection', 0, 3), (@q5_4, 'Strict-Transport-Security', 0, 4),
(@q5_5, 'Cross-Site Protection', 0, 1), (@q5_5, 'Content Security Policy', 1, 2), (@q5_5, 'Client-Side Protocol', 0, 3), (@q5_5, 'Certificate Signing Process', 0, 4);

INSERT INTO quiz_questions (quiz_id, question_id, display_order) VALUES
(@quiz5_id, @q5_1, 1), (@quiz5_id, @q5_2, 2), (@quiz5_id, @q5_3, 3), (@quiz5_id, @q5_4, 4), (@quiz5_id, @q5_5, 5);

-- ============================================
-- 20. INSERT QUESTIONS - MODULE 6 QUIZ
-- ============================================
INSERT INTO questions (question_type, question_text, points, explanation, created_at) VALUES
('Multiple Choice', 'Which type of hacker is authorized to test systems?', 1, 'White Hat hackers are security professionals who have authorization to test and improve system security.', NOW()),
('Multiple Choice', 'What does OSINT stand for?', 1, 'OSINT refers to intelligence gathered from publicly available sources.', NOW()),
('Multiple Choice', 'Which tool is commonly used for port scanning?', 1, 'Nmap is the industry-standard tool for network discovery and port scanning.', NOW()),
('Multiple Choice', 'In a Black Box test, the tester has:', 1, 'Black Box testing simulates an external attacker with no prior knowledge of the target.', NOW()),
('Multiple Choice', 'What does CVE stand for?', 1, 'CVE provides standardized identifiers for publicly known cybersecurity vulnerabilities.', NOW());

SET @q6_1 = LAST_INSERT_ID();
SET @q6_2 = @q6_1 + 1;
SET @q6_3 = @q6_1 + 2;
SET @q6_4 = @q6_1 + 3;
SET @q6_5 = @q6_1 + 4;

INSERT INTO question_options (question_id, option_text, is_correct, display_order) VALUES
(@q6_1, 'Black Hat', 0, 1), (@q6_1, 'White Hat', 1, 2), (@q6_1, 'Gray Hat', 0, 3), (@q6_1, 'Script Kiddie', 0, 4),
(@q6_2, 'Open Source Intelligence', 1, 1), (@q6_2, 'Operating System Integration', 0, 2), (@q6_2, 'Online Security Intelligence', 0, 3), (@q6_2, 'Organizational Security Interface', 0, 4),
(@q6_3, 'Wireshark', 0, 1), (@q6_3, 'Nmap', 1, 2), (@q6_3, 'Metasploit', 0, 3), (@q6_3, 'Burp Suite', 0, 4),
(@q6_4, 'Full knowledge', 0, 1), (@q6_4, 'No prior knowledge', 1, 2), (@q6_4, 'Partial knowledge', 0, 3), (@q6_4, 'Source code access', 0, 4),
(@q6_5, 'Common Vulnerability Enumeration', 0, 1), (@q6_5, 'Common Vulnerabilities and Exposures', 1, 2), (@q6_5, 'Critical Vulnerability Entry', 0, 3), (@q6_5, 'Computer Virus Encyclopedia', 0, 4);

INSERT INTO quiz_questions (quiz_id, question_id, display_order) VALUES
(@quiz6_id, @q6_1, 1), (@quiz6_id, @q6_2, 2), (@quiz6_id, @q6_3, 3), (@quiz6_id, @q6_4, 4), (@quiz6_id, @q6_5, 5);

-- ============================================
-- 21. INSERT QUESTIONS - MODULE 7 QUIZ
-- ============================================
INSERT INTO questions (question_type, question_text, points, explanation, created_at) VALUES
('Multiple Choice', 'Which NIST phase involves removing threats and vulnerabilities?', 1, 'Eradication is the phase where threats are removed and vulnerabilities are eliminated.', NOW()),
('Multiple Choice', 'What is the primary purpose of chain of custody?', 1, 'Chain of custody documentation ensures digital evidence is admissible in legal proceedings.', NOW()),
('Multiple Choice', 'Which tool is used for memory forensics?', 1, 'Volatility is an open-source memory forensics framework used to analyze RAM dumps.', NOW()),
('Multiple Choice', 'What should be done FIRST when responding to an incident?', 1, 'Preserving evidence is critical for investigation and potential legal proceedings.', NOW()),
('Multiple Choice', 'A bit-for-bit copy of storage media is called a:', 1, 'A disk image is a bit-for-bit copy that preserves all data including deleted files.', NOW());

SET @q7_1 = LAST_INSERT_ID();
SET @q7_2 = @q7_1 + 1;
SET @q7_3 = @q7_1 + 2;
SET @q7_4 = @q7_1 + 3;
SET @q7_5 = @q7_1 + 4;

INSERT INTO question_options (question_id, option_text, is_correct, display_order) VALUES
(@q7_1, 'Containment', 0, 1), (@q7_1, 'Eradication', 1, 2), (@q7_1, 'Recovery', 0, 3), (@q7_1, 'Detection', 0, 4),
(@q7_2, 'Speed up investigation', 0, 1), (@q7_2, 'Ensure evidence admissibility', 1, 2), (@q7_2, 'Reduce costs', 0, 3), (@q7_2, 'Identify attackers', 0, 4),
(@q7_3, 'Nmap', 0, 1), (@q7_3, 'Volatility', 1, 2), (@q7_3, 'Wireshark', 0, 3), (@q7_3, 'Nessus', 0, 4),
(@q7_4, 'Format affected systems', 0, 1), (@q7_4, 'Preserve evidence', 1, 2), (@q7_4, 'Notify the media', 0, 3), (@q7_4, 'Update antivirus', 0, 4),
(@q7_5, 'Snapshot', 0, 1), (@q7_5, 'Disk Image', 1, 2), (@q7_5, 'Backup', 0, 3), (@q7_5, 'Archive', 0, 4);

INSERT INTO quiz_questions (quiz_id, question_id, display_order) VALUES
(@quiz7_id, @q7_1, 1), (@quiz7_id, @q7_2, 2), (@quiz7_id, @q7_3, 3), (@quiz7_id, @q7_4, 4), (@quiz7_id, @q7_5, 5);

-- ============================================
-- 22. INSERT QUESTIONS - MODULE 8 QUIZ
-- ============================================
INSERT INTO questions (question_type, question_text, points, explanation, created_at) VALUES
('Multiple Choice', 'What does SIEM stand for?', 1, 'SIEM collects, correlates, and analyzes security events from across an organization.', NOW()),
('Multiple Choice', 'Which SOC tier is responsible for initial alert review?', 1, 'Tier 1 analysts handle initial alert triage, filtering out false positives.', NOW()),
('Multiple Choice', 'MTTD stands for:', 1, 'MTTD measures the average time it takes to detect a security threat.', NOW()),
('Multiple Choice', 'Which of the following is an open-source SIEM tool?', 1, 'Wazuh is an open-source security monitoring platform.', NOW()),
('Multiple Choice', 'What is the MITRE ATT&CK framework used for?', 1, 'MITRE ATT&CK is a knowledge base of adversary tactics used for threat modeling.', NOW());

SET @q8_1 = LAST_INSERT_ID();
SET @q8_2 = @q8_1 + 1;
SET @q8_3 = @q8_1 + 2;
SET @q8_4 = @q8_1 + 3;
SET @q8_5 = @q8_1 + 4;

INSERT INTO question_options (question_id, option_text, is_correct, display_order) VALUES
(@q8_1, 'Security Information and Event Management', 1, 1), (@q8_1, 'System Intelligence and Event Monitoring', 0, 2), (@q8_1, 'Secure Internet and Email Management', 0, 3), (@q8_1, 'System Integration and Enterprise Monitoring', 0, 4),
(@q8_2, 'Tier 1', 1, 1), (@q8_2, 'Tier 2', 0, 2), (@q8_2, 'Tier 3', 0, 3), (@q8_2, 'Tier 4', 0, 4),
(@q8_3, 'Mean Time to Detect', 1, 1), (@q8_3, 'Maximum Time to Detection', 0, 2), (@q8_3, 'Minimum Technical Threat Duration', 0, 3), (@q8_3, 'Managed Threat Transfer Delay', 0, 4),
(@q8_4, 'Splunk', 0, 1), (@q8_4, 'QRadar', 0, 2), (@q8_4, 'Wazuh', 1, 3), (@q8_4, 'Microsoft Sentinel', 0, 4),
(@q8_5, 'Network routing', 0, 1), (@q8_5, 'Threat modeling and detection', 1, 2), (@q8_5, 'Password management', 0, 3), (@q8_5, 'Data encryption', 0, 4);

INSERT INTO quiz_questions (quiz_id, question_id, display_order) VALUES
(@quiz8_id, @q8_1, 1), (@quiz8_id, @q8_2, 2), (@quiz8_id, @q8_3, 3), (@quiz8_id, @q8_4, 4), (@quiz8_id, @q8_5, 5);

-- ============================================
-- 23. INSERT QUESTIONS - MODULE 9 QUIZ
-- ============================================
INSERT INTO questions (question_type, question_text, points, explanation, created_at) VALUES
('Multiple Choice', 'The NIST CSF consists of how many core functions?', 1, 'NIST CSF has 5 core functions: Identify, Protect, Detect, Respond, and Recover.', NOW()),
('Multiple Choice', 'ISO 27001 is a standard for:', 1, 'ISO 27001 is the international standard for Information Security Management Systems (ISMS).', NOW()),
('Multiple Choice', 'Risk is calculated as:', 1, 'Risk is typically calculated as the product of likelihood and impact.', NOW()),
('Multiple Choice', 'Which risk treatment involves transferring risk to a third party?', 1, 'Risk transfer moves the financial consequence of risk to another party, typically through insurance.', NOW()),
('Multiple Choice', 'Which NIST function involves taking action on detected incidents?', 1, 'The Respond function includes taking action regarding detected cybersecurity incidents.', NOW());

SET @q9_1 = LAST_INSERT_ID();
SET @q9_2 = @q9_1 + 1;
SET @q9_3 = @q9_1 + 2;
SET @q9_4 = @q9_1 + 3;
SET @q9_5 = @q9_1 + 4;

INSERT INTO question_options (question_id, option_text, is_correct, display_order) VALUES
(@q9_1, '3', 0, 1), (@q9_1, '4', 0, 2), (@q9_1, '5', 1, 3), (@q9_1, '6', 0, 4),
(@q9_2, 'Payment processing', 0, 1), (@q9_2, 'Information security management', 1, 2), (@q9_2, 'Network routing', 0, 3), (@q9_2, 'Software development', 0, 4),
(@q9_3, 'Threat + Vulnerability', 0, 1), (@q9_3, 'Likelihood x Impact', 1, 2), (@q9_3, 'Asset Value - Cost', 0, 3), (@q9_3, 'Threat - Control', 0, 4),
(@q9_4, 'Accept', 0, 1), (@q9_4, 'Mitigate', 0, 2), (@q9_4, 'Transfer', 1, 3), (@q9_4, 'Avoid', 0, 4),
(@q9_5, 'Identify', 0, 1), (@q9_5, 'Protect', 0, 2), (@q9_5, 'Detect', 0, 3), (@q9_5, 'Respond', 1, 4);

INSERT INTO quiz_questions (quiz_id, question_id, display_order) VALUES
(@quiz9_id, @q9_1, 1), (@quiz9_id, @q9_2, 2), (@quiz9_id, @q9_3, 3), (@quiz9_id, @q9_4, 4), (@quiz9_id, @q9_5, 5);

-- ============================================
-- 24. INSERT QUESTIONS - FINAL ASSESSMENT
-- ============================================
INSERT INTO questions (question_type, question_text, points, explanation, created_at) VALUES
('Multiple Choice', 'The three principles of the CIA Triad are:', 1, 'The CIA Triad consists of Confidentiality, Integrity, and Availability.', NOW()),
('Multiple Choice', 'Which layer of the OSI model handles routing?', 1, 'Layer 3 (Network Layer) is responsible for logical addressing and routing.', NOW()),
('Multiple Choice', 'Which malware type does NOT need a host program?', 1, 'Worms are self-replicating and do not require a host program to spread.', NOW()),
('Multiple Choice', 'Which encryption algorithm is symmetric?', 1, 'AES is a symmetric encryption algorithm. RSA and ECC are asymmetric.', NOW()),
('Multiple Choice', 'What is the primary defense against SQL Injection?', 1, 'Parameterized queries are the most effective defense against SQL injection.', NOW()),
('Multiple Choice', 'In a Black Box penetration test, the tester has:', 1, 'Black Box testing simulates an external attacker with no prior knowledge.', NOW()),
('Multiple Choice', 'Which phase of incident response involves limiting damage?', 1, 'Containment isolates the incident to prevent further damage.', NOW()),
('Multiple Choice', 'What does a SIEM system primarily do?', 1, 'SIEM collects, correlates, and analyzes security events from multiple sources.', NOW()),
('Multiple Choice', 'The NIST Cybersecurity Framework has how many functions?', 1, 'NIST CSF consists of 5 functions: Identify, Protect, Detect, Respond, Recover.', NOW()),
('Multiple Choice', 'Which access control model grants permissions based on user roles?', 1, 'RBAC grants permissions based on predefined roles within an organization.', NOW()),
('Multiple Choice', 'What is the purpose of a honeypot?', 1, 'A honeypot is a decoy system designed to attract and study attackers.', NOW()),
('Multiple Choice', 'Which social engineering technique involves following someone into a secure area?', 1, 'Tailgating involves following an authorized person into a restricted area.', NOW()),
('Multiple Choice', 'TLS is used to:', 1, 'TLS encrypts data transmitted over networks, enabling HTTPS.', NOW()),
('Multiple Choice', 'What does OWASP stand for?', 1, 'OWASP is a nonprofit foundation that works to improve software security.', NOW()),
('Multiple Choice', 'Risk is best defined as:', 1, 'Risk is the product of the likelihood of a threat and the resulting impact.', NOW());

SET @qf_1 = LAST_INSERT_ID();
SET @qf_2 = @qf_1 + 1;
SET @qf_3 = @qf_1 + 2;
SET @qf_4 = @qf_1 + 3;
SET @qf_5 = @qf_1 + 4;
SET @qf_6 = @qf_1 + 5;
SET @qf_7 = @qf_1 + 6;
SET @qf_8 = @qf_1 + 7;
SET @qf_9 = @qf_1 + 8;
SET @qf_10 = @qf_1 + 9;
SET @qf_11 = @qf_1 + 10;
SET @qf_12 = @qf_1 + 11;
SET @qf_13 = @qf_1 + 12;
SET @qf_14 = @qf_1 + 13;
SET @qf_15 = @qf_1 + 14;

INSERT INTO question_options (question_id, option_text, is_correct, display_order) VALUES
(@qf_1, 'Control, Integrity, Authentication', 0, 1), (@qf_1, 'Confidentiality, Integrity, Availability', 1, 2), (@qf_1, 'Certification, Implementation, Assessment', 0, 3), (@qf_1, 'Compliance, Investigation, Analysis', 0, 4),
(@qf_2, 'Data Link', 0, 1), (@qf_2, 'Network', 1, 2), (@qf_2, 'Transport', 0, 3), (@qf_2, 'Application', 0, 4),
(@qf_3, 'Virus', 0, 1), (@qf_3, 'Worm', 1, 2), (@qf_3, 'Trojan', 0, 3), (@qf_3, 'Rootkit', 0, 4),
(@qf_4, 'RSA', 0, 1), (@qf_4, 'AES', 1, 2), (@qf_4, 'ECC', 0, 3), (@qf_4, 'DSA', 0, 4),
(@qf_5, 'Input validation', 0, 1), (@qf_5, 'Parameterized queries', 1, 2), (@qf_5, 'Web Application Firewall', 0, 3), (@qf_5, 'URL encoding', 0, 4),
(@qf_6, 'Full knowledge of systems', 0, 1), (@qf_6, 'No prior knowledge', 1, 2), (@qf_6, 'Source code access', 0, 3), (@qf_6, 'Administrator credentials', 0, 4),
(@qf_7, 'Detection', 0, 1), (@qf_7, 'Containment', 1, 2), (@qf_7, 'Eradication', 0, 3), (@qf_7, 'Recovery', 0, 4),
(@qf_8, 'Encrypt network traffic', 0, 1), (@qf_8, 'Collect and analyze security events', 1, 2), (@qf_8, 'Block malicious websites', 0, 3), (@qf_8, 'Manage user passwords', 0, 4),
(@qf_9, '3', 0, 1), (@qf_9, '4', 0, 2), (@qf_9, '5', 1, 3), (@qf_9, '6', 0, 4),
(@qf_10, 'DAC', 0, 1), (@qf_10, 'MAC', 0, 2), (@qf_10, 'RBAC', 1, 3), (@qf_10, 'ABAC', 0, 4),
(@qf_11, 'Store backups', 0, 1), (@qf_11, 'Attract and detect attackers', 1, 2), (@qf_11, 'Encrypt data', 0, 3), (@qf_11, 'Speed up networks', 0, 4),
(@qf_12, 'Phishing', 0, 1), (@qf_12, 'Pretexting', 0, 2), (@qf_12, 'Tailgating', 1, 3), (@qf_12, 'Baiting', 0, 4),
(@qf_13, 'Compress files', 0, 1), (@qf_13, 'Encrypt web traffic', 1, 2), (@qf_13, 'Scan for viruses', 0, 3), (@qf_13, 'Route packets', 0, 4),
(@qf_14, 'Open Web Application Security Project', 1, 1), (@qf_14, 'Online Web Attack and Security Protocol', 0, 2), (@qf_14, 'Operational Web Application Standard Procedure', 0, 3), (@qf_14, 'Open Web Authentication Security Program', 0, 4),
(@qf_15, 'Threat + Vulnerability', 0, 1), (@qf_15, 'Likelihood x Impact', 1, 2), (@qf_15, 'Asset x Control', 0, 3), (@qf_15, 'Cost + Benefit', 0, 4);

INSERT INTO quiz_questions (quiz_id, question_id, display_order) VALUES
(@quiz_final_id, @qf_1, 1), (@quiz_final_id, @qf_2, 2), (@quiz_final_id, @qf_3, 3), (@quiz_final_id, @qf_4, 4), (@quiz_final_id, @qf_5, 5),
(@quiz_final_id, @qf_6, 6), (@quiz_final_id, @qf_7, 7), (@quiz_final_id, @qf_8, 8), (@quiz_final_id, @qf_9, 9), (@quiz_final_id, @qf_10, 10),
(@quiz_final_id, @qf_11, 11), (@quiz_final_id, @qf_12, 12), (@quiz_final_id, @qf_13, 13), (@quiz_final_id, @qf_14, 14), (@quiz_final_id, @qf_15, 15);

-- ============================================
-- COMMIT TRANSACTION
-- ============================================
COMMIT;

-- ============================================
-- COMPLETION
-- ============================================
SELECT CONCAT('Cybersecurity course created with ID: ', @course_id) AS status;
