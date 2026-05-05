<?php
/**
 * Cybersecurity Course Installation Script
 * 
 * This script installs the complete Cybersecurity Fundamentals course
 * into the Edutrack LMS database.
 * 
 * WARNING: Delete this file after installation!
 */

require_once '../src/bootstrap.php';

// Security check - require admin access or secret key
$allowed = false;

// Check if user is admin
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    $db = Database::getInstance();
    $roleCheck = $db->fetch("SELECT role_id FROM user_roles WHERE user_id = ? AND role_id = 1", [$_SESSION['user_id']]);
    if ($roleCheck) {
        $allowed = true;
    }
}

// Or check secret key
if (!$allowed && isset($_GET['key']) && $_GET['key'] === 'edutrack2024') {
    $allowed = true;
}

if (!$allowed) {
    http_response_code(403);
    die('Access denied. Admin login or valid key required.');
}

// Set longer execution time for this script
set_time_limit(300);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Cybersecurity Course - Edutrack LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Cybersecurity Course Installation</h1>
        <p class="text-gray-600 mb-6">This script will install the complete Cybersecurity Fundamentals course into your database.</p>
        
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <p class="text-yellow-700"><strong>Warning:</strong> This will insert a new course, modules, lessons, quizzes, and questions. Make sure you have a database backup before proceeding.</p>
        </div>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_install'])) {
            try {
                $db = Database::getInstance();
                $db->getConnection()->beginTransaction();
                
                $log = [];
                
                // 0. Ensure quiz_questions has AUTO_INCREMENT
                try {
                    $db->query("ALTER TABLE `quiz_questions` MODIFY `quiz_question_id` int(11) NOT NULL AUTO_INCREMENT");
                    $log[] = "Ensured quiz_questions table has AUTO_INCREMENT";
                } catch (Exception $e) {
                    // May already have AUTO_INCREMENT, ignore error
                    $log[] = "quiz_questions AUTO_INCREMENT check (may already exist)";
                }
                
                // 1. Insert or get category
                $category = $db->fetch("SELECT id FROM course_categories WHERE name = ?", ['Cybersecurity']);
                if ($category) {
                    $category_id = $category['id'];
                    $log[] = "Category 'Cybersecurity' already exists (ID: $category_id)";
                } else {
                    $db->query("INSERT INTO course_categories (name, category_description, color, icon_url, display_order, is_active, created_at) 
                                VALUES (?, ?, ?, ?, ?, 1, NOW())", 
                                ['Cybersecurity', 'Learn to protect systems, networks, and data from cyber threats', '#DC2626', 'fa-shield-alt', 4]);
                    $category_id = $db->getConnection()->lastInsertId();
                    $log[] = "Created category 'Cybersecurity' (ID: $category_id)";
                }
                
                // 2. Insert course
                $courseDesc = '<p>This comprehensive cybersecurity course prepares you for entry-level roles in the rapidly growing field of cybersecurity. You will learn fundamental concepts, network security, threat detection, ethical hacking basics, and security operations.</p><p>By the end of this course, you will understand how to protect systems, detect threats, and respond to security incidents using industry-standard tools and frameworks.</p>';
                $shortDesc = 'Master cybersecurity fundamentals and protect digital assets from cyber threats';
                $outcomes = 'Understand cybersecurity principles and the threat landscape|Identify and mitigate common network vulnerabilities|Implement security controls and defense strategies|Detect and respond to security incidents|Understand ethical hacking basics|Apply security frameworks like NIST';
                $prereqs = 'Basic computer literacy, Understanding of operating systems (Windows/Linux)';
                
                $db->query("INSERT INTO courses (title, slug, description, short_description, category_id, instructor_id, level, language, thumbnail_url, duration_weeks, total_hours, max_students, price, status, is_featured, learning_outcomes, prerequisites, created_at, updated_at) 
                           VALUES (?, ?, ?, ?, ?, 1, ?, 'English', ?, 12, 96.00, 30, 4500.00, 'published', 1, ?, ?, NOW(), NOW())",
                           ['Cybersecurity Fundamentals', 'cybersecurity-fundamentals', $courseDesc, $shortDesc, $category_id, 'Beginner', 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=800', $outcomes, $prereqs]);
                $course_id = $db->getConnection()->lastInsertId();
                $log[] = "Created course 'Cybersecurity Fundamentals' (ID: $course_id)";
                
                // 3. Insert modules
                $modules = [
                    ['Module 1: Introduction to Cybersecurity', 'Understanding the cybersecurity landscape, key concepts, and career paths', 1, 225],
                    ['Module 2: Networking Fundamentals', 'OSI model, TCP/IP, network protocols, and basic network security', 2, 300],
                    ['Module 3: Cyber Threats and Attacks', 'Types of malware, attack vectors, social engineering, and threat actors', 3, 285],
                    ['Module 4: Security Controls and Defense', 'Firewalls, IDS/IPS, encryption, access control, and defense strategies', 4, 330],
                    ['Module 5: Web Application Security', 'OWASP Top 10, secure coding, and web vulnerability assessment', 5, 345],
                    ['Module 6: Ethical Hacking Basics', 'Penetration testing methodology, reconnaissance, and vulnerability scanning', 6, 315],
                    ['Module 7: Incident Response and Forensics', 'Incident handling process, digital forensics fundamentals, and reporting', 7, 285],
                    ['Module 8: Security Operations (SOC)', 'SIEM tools, log analysis, monitoring, and SOC workflows', 8, 285],
                    ['Module 9: Governance, Risk, and Compliance', 'Security frameworks, risk management, and compliance standards', 9, 255],
                    ['Module 10: Capstone Project', 'Apply your skills to a real-world cybersecurity scenario', 10, 135],
                ];
                
                $module_ids = [];
                foreach ($modules as $mod) {
                    $db->query("INSERT INTO modules (course_id, title, description, display_order, duration_minutes, is_published, created_at) 
                               VALUES (?, ?, ?, ?, ?, 1, NOW())", 
                               [$course_id, $mod[0], $mod[1], $mod[2], $mod[3]]);
                    $module_ids[] = $db->getConnection()->lastInsertId();
                }
                $log[] = "Created " . count($modules) . " modules";
                
                // 4. Insert lessons
                $lessons = [
                    // Module 1 lessons
                    [$module_ids[0], 'What is Cybersecurity?', 'Video', 30, 1, 1, 10],
                    [$module_ids[0], 'The Cyber Threat Landscape', 'Video', 25, 2, 0, 10],
                    [$module_ids[0], 'Cybersecurity Career Paths', 'Video', 20, 3, 0, 10],
                    [$module_ids[0], 'Module 1: Knowledge Check', 'Quiz', 20, 4, 0, 25],
                    // Module 2 lessons
                    [$module_ids[1], 'The OSI Model', 'Video', 35, 1, 1, 15],
                    [$module_ids[1], 'TCP/IP and Network Protocols', 'Video', 30, 2, 0, 15],
                    [$module_ids[1], 'Network Security Basics', 'Video', 35, 3, 0, 15],
                    [$module_ids[1], 'Module 2: Knowledge Check', 'Quiz', 25, 4, 0, 25],
                    // Module 3 lessons
                    [$module_ids[2], 'Types of Malware', 'Video', 30, 1, 0, 15],
                    [$module_ids[2], 'Social Engineering Attacks', 'Video', 30, 2, 0, 15],
                    [$module_ids[2], 'Attack Vectors and Exploits', 'Video', 35, 3, 0, 15],
                    [$module_ids[2], 'Module 3: Knowledge Check', 'Quiz', 25, 4, 0, 25],
                    // Module 4 lessons
                    [$module_ids[3], 'Firewalls and Network Defense', 'Video', 35, 1, 0, 15],
                    [$module_ids[3], 'Encryption and Cryptography', 'Video', 40, 2, 0, 15],
                    [$module_ids[3], 'Access Control and Authentication', 'Video', 35, 3, 0, 15],
                    [$module_ids[3], 'Module 4: Knowledge Check', 'Quiz', 25, 4, 0, 25],
                    // Module 5 lessons
                    [$module_ids[4], 'OWASP Top 10 Vulnerabilities', 'Video', 40, 1, 0, 15],
                    [$module_ids[4], 'SQL Injection and XSS', 'Video', 40, 2, 0, 15],
                    [$module_ids[4], 'Secure Coding Practices', 'Video', 35, 3, 0, 15],
                    [$module_ids[4], 'Module 5: Knowledge Check', 'Quiz', 30, 4, 0, 25],
                    // Module 6 lessons
                    [$module_ids[5], 'Introduction to Ethical Hacking', 'Video', 30, 1, 0, 15],
                    [$module_ids[5], 'Reconnaissance and Scanning', 'Video', 40, 2, 0, 15],
                    [$module_ids[5], 'Vulnerability Exploitation Basics', 'Video', 35, 3, 0, 15],
                    [$module_ids[5], 'Module 6: Knowledge Check', 'Quiz', 25, 4, 0, 25],
                    // Module 7 lessons
                    [$module_ids[6], 'Incident Response Process', 'Video', 35, 1, 0, 15],
                    [$module_ids[6], 'Digital Forensics Fundamentals', 'Video', 35, 2, 0, 15],
                    [$module_ids[6], 'Incident Reporting and Documentation', 'Video', 25, 3, 0, 15],
                    [$module_ids[6], 'Module 7: Knowledge Check', 'Quiz', 25, 4, 0, 25],
                    // Module 8 lessons
                    [$module_ids[7], 'Introduction to SIEM', 'Video', 30, 1, 0, 15],
                    [$module_ids[7], 'Log Analysis and Monitoring', 'Video', 30, 2, 0, 15],
                    [$module_ids[7], 'SOC Operations and Workflow', 'Video', 35, 3, 0, 15],
                    [$module_ids[7], 'Module 8: Knowledge Check', 'Quiz', 25, 4, 0, 25],
                    // Module 9 lessons
                    [$module_ids[8], 'Security Frameworks and Standards', 'Video', 30, 1, 0, 15],
                    [$module_ids[8], 'Risk Management', 'Video', 30, 2, 0, 15],
                    [$module_ids[8], 'Compliance and Audit', 'Video', 25, 3, 0, 15],
                    [$module_ids[8], 'Module 9: Knowledge Check', 'Quiz', 25, 4, 0, 25],
                    // Module 10 lessons
                    [$module_ids[9], 'Capstone Project Overview', 'Assignment', 45, 1, 0, 100],
                    [$module_ids[9], 'Career Preparation and Next Steps', 'Video', 25, 2, 0, 10],
                    [$module_ids[9], 'Final Assessment', 'Quiz', 60, 3, 0, 150],
                ];
                
                // Lesson content array
                $lessonContents = [
                    // Module 1
                    '<h2>What is Cybersecurity?</h2><p>Cybersecurity is the practice of protecting systems, networks, and programs from digital attacks. These cyberattacks are usually aimed at accessing, changing, or destroying sensitive information; extorting money from users; or interrupting normal business processes.</p><h3>Why Cybersecurity Matters</h3><ul><li><strong>Data Protection:</strong> Organizations hold vast amounts of sensitive data</li><li><strong>Financial Impact:</strong> Cybercrime costs the global economy billions annually</li><li><strong>National Security:</strong> Critical infrastructure needs protection</li><li><strong>Personal Privacy:</strong> Individuals need protection from identity theft</li></ul><h3>The CIA Triad</h3><p>The foundation of cybersecurity is built on three principles:</p><ul><li><strong>Confidentiality:</strong> Ensuring information is accessible only to authorized users</li><li><strong>Integrity:</strong> Maintaining the accuracy and completeness of data</li><li><strong>Availability:</strong> Ensuring systems and data are accessible when needed</li></ul><h3>Key Terminology</h3><ul><li><strong>Asset:</strong> Anything of value to an organization</li><li><strong>Vulnerability:</strong> A weakness that could be exploited</li><li><strong>Threat:</strong> A potential danger to an asset</li><li><strong>Risk:</strong> The likelihood and impact of a threat exploiting a vulnerability</li><li><strong>Exploit:</strong> A method used to take advantage of a vulnerability</li></ul>',
                    
                    '<h2>The Cyber Threat Landscape</h2><p>Understanding who attacks systems and why is crucial for effective defense.</p><h3>Types of Threat Actors</h3><ul><li><strong>Script Kiddies:</strong> Unskilled attackers using existing tools</li><li><strong>Hacktivists:</strong> Attackers motivated by political or social causes</li><li><strong>Cybercriminals:</strong> Organized groups seeking financial gain</li><li><strong>State-Sponsored Actors:</strong> Nation-state backed attackers</li><li><strong>Insider Threats:</strong> Malicious or negligent employees</li></ul><h3>Common Attack Motivations</h3><ul><li>Financial gain (ransomware, fraud)</li><li>Political influence (election interference)</li><li>Corporate espionage (intellectual property theft)</li><li>Disruption (DDoS attacks on critical services)</li></ul><h3>Statistics in Zambia and Africa</h3><p>Africa is experiencing rapid digital transformation, making cybersecurity increasingly important. Mobile money platforms, government services, and businesses all face growing cyber threats.</p>',
                    
                    '<h2>Cybersecurity Career Paths</h2><p>The cybersecurity field offers diverse career opportunities with strong demand globally and in Zambia.</p><h3>Entry-Level Roles</h3><ul><li><strong>Security Analyst:</strong> Monitor systems for threats and vulnerabilities</li><li><strong>Junior Penetration Tester:</strong> Test systems for vulnerabilities</li><li><strong>SOC Analyst:</strong> Work in Security Operations Centers monitoring alerts</li></ul><h3>Mid-Level Roles</h3><ul><li><strong>Security Engineer:</strong> Design and implement security solutions</li><li><strong>Incident Responder:</strong> Handle security breaches and incidents</li><li><strong>Security Consultant:</strong> Advise organizations on security posture</li></ul><h3>Advanced Roles</h3><ul><li><strong>Security Architect:</strong> Design enterprise security infrastructure</li><li><strong>CISO:</strong> Lead organization-wide security strategy</li></ul><h3>Certifications to Consider</h3><ul><li>CompTIA Security+ (Entry level)</li><li>CEH - Certified Ethical Hacker</li><li>CISSP - Certified Information Systems Security Professional</li></ul>',
                    
                    '<h2>Module 1 Knowledge Check</h2><p>Test your understanding of cybersecurity fundamentals with this practice quiz.</p>',
                    
                    // Module 2
                    '<h2>The OSI Model Explained</h2><p>The Open Systems Interconnection (OSI) model is a conceptual framework that standardizes network communication into seven layers.</p><h3>The Seven Layers</h3><table class="w-full border-collapse border"><tr><th class="border p-2">Layer</th><th class="border p-2">Name</th><th class="border p-2">Function</th></tr><tr><td class="border p-2">7</td><td class="border p-2">Application</td><td class="border p-2">HTTP, FTP, SMTP</td></tr><tr><td class="border p-2">6</td><td class="border p-2">Presentation</td><td class="border p-2">Data formatting, encryption</td></tr><tr><td class="border p-2">5</td><td class="border p-2">Session</td><td class="border p-2">Session management</td></tr><tr><td class="border p-2">4</td><td class="border p-2">Transport</td><td class="border p-2">TCP, UDP</td></tr><tr><td class="border p-2">3</td><td class="border p-2">Network</td><td class="border p-2">IP, routing</td></tr><tr><td class="border p-2">2</td><td class="border p-2">Data Link</td><td class="border p-2">MAC addresses, switches</td></tr><tr><td class="border p-2">1</td><td class="border p-2">Physical</td><td class="border p-2">Cables, signals</td></tr></table><h3>Why the OSI Model Matters for Security</h3><p>Understanding the OSI model helps security professionals identify where attacks occur and implement security controls at appropriate layers.</p>',
                    
                    '<h2>TCP/IP and Network Protocols</h2><p>The TCP/IP model is the practical implementation of network communication used on the internet today.</p><h3>Key Protocols and Their Security Implications</h3><ul><li><strong>HTTP (Port 80):</strong> Unencrypted web traffic - vulnerable to interception</li><li><strong>HTTPS (Port 443):</strong> Encrypted web traffic using TLS/SSL</li><li><strong>FTP (Port 21):</strong> File transfer - sends credentials in plaintext</li><li><strong>SSH (Port 22):</strong> Secure remote access - encrypted alternative to Telnet</li><li><strong>DNS (Port 53):</strong> Domain resolution - target for cache poisoning</li><li><strong>SMTP (Port 25):</strong> Email sending - often exploited for spam</li></ul><h3>Network Segmentation</h3><p>Dividing networks into segments (VLANs) limits the spread of attacks. Critical systems should be isolated from general user networks.</p>',
                    
                    '<h2>Network Security Basics</h2><p>Protecting network infrastructure is the first line of defense against cyber attacks.</p><h3>Common Network Attacks</h3><ul><li><strong>Man-in-the-Middle (MitM):</strong> Intercepting communication between parties</li><li><strong>ARP Spoofing:</strong> Falsifying ARP messages to redirect traffic</li><li><strong>DNS Spoofing:</strong> Corrupting DNS cache to redirect users</li><li><strong>Packet Sniffing:</strong> Capturing and analyzing network traffic</li></ul><h3>Network Security Controls</h3><ul><li><strong>Network Access Control (NAC):</strong> Controls device access to the network</li><li><strong>Virtual LANs (VLANs):</strong> Segment networks logically</li><li><strong>VPNs:</strong> Encrypt traffic over public networks</li><li><strong>Network Monitoring:</strong> Continuous traffic analysis for anomalies</li></ul><h3>Wireshark Basics</h3><p>Wireshark is a free network protocol analyzer used to capture and inspect network traffic. It is essential for network troubleshooting and security analysis.</p>',
                    
                    '<h2>Module 2 Knowledge Check</h2><p>Test your networking knowledge with this practice quiz.</p>',
                    
                    // Module 3
                    '<h2>Types of Malware</h2><p>Malware (malicious software) is any program designed to harm or exploit systems.</p><h3>Common Malware Types</h3><ul><li><strong>Virus:</strong> Self-replicating code that attaches to legitimate programs</li><li><strong>Worm:</strong> Self-spreading malware that does not need a host program</li><li><strong>Trojan:</strong> Malware disguised as legitimate software</li><li><strong>Ransomware:</strong> Encrypts files and demands payment for decryption</li><li><strong>Spyware:</strong> Secretly monitors user activity</li><li><strong>Adware:</strong> Displays unwanted advertisements</li><li><strong>Rootkit:</strong> Hides malicious activity deep in the operating system</li><li><strong>Keylogger:</strong> Records keystrokes to steal passwords</li></ul><h3>Famous Malware Examples</h3><ul><li><strong>WannaCry (2017):</strong> Ransomware that affected 200,000+ computers globally</li><li><strong>Stuxnet (2010):</strong> First known cyberweapon targeting industrial systems</li><li><strong>Emotet:</strong> Banking trojan turned malware distribution platform</li></ul>',
                    
                    '<h2>Social Engineering Attacks</h2><p>Social engineering exploits human psychology rather than technical vulnerabilities.</p><h3>Common Social Engineering Techniques</h3><ul><li><strong>Phishing:</strong> Fraudulent emails impersonating trusted entities</li><li><strong>Spear Phishing:</strong> Targeted phishing against specific individuals</li><li><strong>Whaling:</strong> Phishing targeting high-level executives</li><li><strong>Pretexting:</strong> Creating a fabricated scenario to gain information</li><li><strong>Baiting:</strong> Leaving infected USB drives in public places</li><li><strong>Quid Pro Quo:</strong> Offering a service in exchange for information</li><li><strong>Tailgating:</strong> Following someone into a restricted area</li></ul><h3>Red Flags of Phishing Emails</h3><ul><li>Urgent or threatening language</li><li>Requests for personal information</li><li>Suspicious sender addresses</li><li>Poor grammar and spelling</li><li>Unexpected attachments</li></ul>',
                    
                    '<h2>Attack Vectors and Exploits</h2><p>Understanding how attackers gain access helps in building effective defenses.</p><h3>Common Attack Vectors</h3><ul><li><strong>Software Vulnerabilities:</strong> Unpatched systems and applications</li><li><strong>Weak Passwords:</strong> Easily guessable or reused credentials</li><li><strong>Insider Threats:</strong> Malicious or negligent employees</li><li><strong>Supply Chain:</strong> Attacking through third-party vendors</li><li><strong>Wireless Networks:</strong> Unsecured Wi-Fi networks</li></ul><h3>The Cyber Kill Chain</h3><ol><li><strong>Reconnaissance:</strong> Gathering information about the target</li><li><strong>Weaponization:</strong> Creating the attack payload</li><li><strong>Delivery:</strong> Transmitting the weapon to the target</li><li><strong>Exploitation:</strong> Triggering the vulnerability</li><li><strong>Installation:</strong> Establishing persistent access</li><li><strong>Command and Control:</strong> Remote control of compromised systems</li><li><strong>Actions on Objectives:</strong> Achieving the attacker\'s goal</li></ol>',
                    
                    '<h2>Module 3 Knowledge Check</h2><p>Test your knowledge of cyber threats and attacks.</p>',
                    
                    // Module 4
                    '<h2>Firewalls and Network Defense</h2><p>Firewalls are the primary defense mechanism for network security, controlling traffic based on security rules.</p><h3>Types of Firewalls</h3><ul><li><strong>Packet-Filtering Firewall:</strong> Inspects packets based on IP/port rules (Layer 3-4)</li><li><strong>Stateful Inspection:</strong> Tracks active connections and makes decisions based on connection state</li><li><strong>Proxy Firewall:</strong> Acts as intermediary between internal and external networks (Layer 7)</li><li><strong>Next-Generation Firewall (NGFW):</strong> Includes IDS/IPS, application awareness, and threat intelligence</li></ul><h3>IDS vs IPS</h3><ul><li><strong>IDS (Intrusion Detection System):</strong> Monitors and alerts on suspicious activity</li><li><strong>IPS (Intrusion Prevention System):</strong> Actively blocks detected threats in real-time</li></ul>',
                    
                    '<h2>Encryption and Cryptography</h2><p>Cryptography protects data confidentiality and integrity through mathematical algorithms.</p><h3>Types of Encryption</h3><ul><li><strong>Symmetric Encryption:</strong> Same key for encryption and decryption (AES, DES)</li><li><strong>Asymmetric Encryption:</strong> Public/private key pair (RSA, ECC)</li><li><strong>Hashing:</strong> One-way function producing fixed-size output (SHA-256)</li></ul><h3>Digital Certificates and PKI</h3><ul><li><strong>Certificate Authority (CA):</strong> Issues and validates certificates</li><li><strong>SSL/TLS Certificates:</strong> Enable HTTPS for secure websites</li></ul><h3>Practical Applications</h3><ul><li>HTTPS for secure web browsing</li><li>VPN encryption for remote access</li><li>Full disk encryption (BitLocker, FileVault)</li></ul>',
                    
                    '<h2>Access Control and Authentication</h2><p>Controlling who can access resources is fundamental to security.</p><h3>Authentication Factors</h3><ul><li><strong>Something you know:</strong> Passwords, PINs</li><li><strong>Something you have:</strong> Smart cards, tokens, mobile phones</li><li><strong>Something you are:</strong> Biometrics (fingerprint, face, iris)</li></ul><h3>Multi-Factor Authentication (MFA)</h3><p>MFA requires two or more authentication factors. It significantly reduces account compromise risk.</p><h3>Access Control Models</h3><ul><li><strong>RBAC:</strong> Access based on user roles</li><li><strong>MAC:</strong> System-enforced based on security labels</li><li><strong>DAC:</strong> Resource owner controls access</li></ul><h3>Password Security Best Practices</h3><ul><li>Minimum 12 characters with complexity</li><li>Use password managers</li><li>Unique passwords for each account</li></ul>',
                    
                    '<h2>Module 4 Knowledge Check</h2><p>Test your knowledge of security controls and defense.</p>',
                    
                    // Module 5
                    '<h2>OWASP Top 10 Vulnerabilities</h2><p>The OWASP Top 10 is a standard awareness document representing the most critical security risks to web applications.</p><h3>OWASP Top 10 (2021)</h3><ol><li><strong>A01: Broken Access Control:</strong> Restrictions on authenticated users are not properly enforced</li><li><strong>A02: Cryptographic Failures:</strong> Sensitive data exposed due to weak or missing encryption</li><li><strong>A03: Injection:</strong> Untrusted data sent to interpreters (SQL, NoSQL, OS command)</li><li><strong>A04: Insecure Design:</strong> Fundamental design flaws in the application</li><li><strong>A05: Security Misconfiguration:</strong> Default configurations, incomplete setups</li><li><strong>A06: Vulnerable Components:</strong> Using outdated or vulnerable libraries</li><li><strong>A07: Authentication Failures:</strong> Flaws in authentication mechanisms</li><li><strong>A08: Data Integrity Failures:</strong> Untrusted code or data without verification</li><li><strong>A09: Logging Failures:</strong> Insufficient logging and monitoring</li><li><strong>A10: Server-Side Request Forgery (SSRF):</strong> Server making unauthorized requests</li></ol>',
                    
                    '<h2>SQL Injection and XSS</h2><p>These are two of the most common and dangerous web application vulnerabilities.</p><h3>SQL Injection (SQLi)</h3><p>SQL Injection occurs when untrusted user input is concatenated into SQL queries. The best defense is using parameterized queries (prepared statements).</p><h3>Cross-Site Scripting (XSS)</h3><p>XSS allows attackers to inject malicious scripts into web pages viewed by other users:</p><ul><li><strong>Stored XSS:</strong> Malicious script stored on the server</li><li><strong>Reflected XSS:</strong> Script in URL parameters reflected in page response</li><li><strong>DOM-based XSS:</strong> Client-side JavaScript manipulates DOM unsafely</li></ul><h4>Prevention</h4><ul><li>Output encoding (HTML, JavaScript, URL)</li><li>Content Security Policy (CSP) headers</li><li>Input validation</li></ul>',
                    
                    '<h2>Secure Coding Practices</h2><p>Writing secure code from the start prevents vulnerabilities before they reach production.</p><h3>Input Validation</h3><ul><li>Validate all input on the server side</li><li>Use whitelist validation (accept known good)</li><li>Validate data type, length, format, and range</li></ul><h3>Security Headers</h3><ul><li><strong>Content-Security-Policy:</strong> Prevents XSS and data injection</li><li><strong>X-Frame-Options:</strong> Prevents clickjacking</li><li><strong>Strict-Transport-Security:</strong> Enforces HTTPS</li></ul><h3>Authentication and Session Management</h3><ul><li>Use strong, proven authentication libraries</li><li>Implement secure session handling (HttpOnly, Secure, SameSite cookies)</li><li>Never expose stack traces or database errors to users</li></ul>',
                    
                    '<h2>Module 5 Knowledge Check</h2><p>Test your web security knowledge.</p>',
                    
                    // Module 6
                    '<h2>Introduction to Ethical Hacking</h2><p>Ethical hacking involves authorized attempts to gain unauthorized access to systems to identify security weaknesses.</p><h3>White Hat vs Black Hat vs Gray Hat</h3><ul><li><strong>White Hat:</strong> Authorized hackers who help organizations improve security</li><li><strong>Black Hat:</strong> Malicious hackers who exploit vulnerabilities for personal gain</li><li><strong>Gray Hat:</strong> Hackers who operate without authorization but without malicious intent</li></ul><h3>Legal and Ethical Considerations</h3><ul><li>Always have written authorization (Rules of Engagement)</li><li>Define scope and boundaries clearly</li><li>Report all findings responsibly</li><li>Do not cause damage or disruption</li></ul><h3>Penetration Testing Types</h3><ul><li><strong>Black Box:</strong> No prior knowledge of the target</li><li><strong>White Box:</strong> Full knowledge of systems and architecture</li><li><strong>Gray Box:</strong> Partial knowledge (most common)</li></ul>',
                    
                    '<h2>Reconnaissance and Scanning</h2><p>Information gathering is the first and most critical phase of ethical hacking.</p><h3>Passive Reconnaissance</h3><ul><li><strong>OSINT:</strong> Publicly available information</li><li><strong>Google Dorking:</strong> Advanced search techniques</li><li><strong>Whois and DNS:</strong> Domain registration and DNS records</li></ul><h3>Active Reconnaissance</h3><ul><li><strong>Port Scanning:</strong> Identifying open ports and services (Nmap)</li><li><strong>Service Enumeration:</strong> Identifying software versions</li></ul><h3>Nmap Basics</h3><pre class="bg-gray-100 p-3 rounded"><code>nmap -sS target.com        # TCP SYN scan\nnmap -sV target.com        # Service version detection\nnmap -O target.com         # OS detection</code></pre><h3>Vulnerability Scanning</h3><ul><li><strong>Nessus:</strong> Comprehensive vulnerability scanner</li><li><strong>OpenVAS:</strong> Open-source vulnerability scanner</li><li><strong>Nikto:</strong> Web server vulnerability scanner</li></ul>',
                    
                    '<h2>Vulnerability Exploitation Basics</h2><p>Understanding exploitation helps defenders understand what they are protecting against.</p><h3>Exploit Databases and Resources</h3><ul><li><strong>Exploit-DB:</strong> Archive of public exploits</li><li><strong>CVE:</strong> Standardized vulnerability identifiers</li></ul><h3>Metasploit Framework</h3><p>Metasploit is the world\'s most used penetration testing framework with exploits, payloads, and auxiliary modules.</p><h3>Responsible Disclosure</h3><p>When vulnerabilities are discovered:</p><ol><li>Notify the organization privately</li><li>Allow reasonable time for remediation</li><li>Coordinate public disclosure</li><li>Never exploit for personal gain</li></ol>',
                    
                    '<h2>Module 6 Knowledge Check</h2><p>Test your ethical hacking knowledge.</p>',
                    
                    // Module 7
                    '<h2>Incident Response Process</h2><p>An effective incident response plan minimizes damage and recovery time when security breaches occur.</p><h3>NIST Incident Response Lifecycle</h3><ol><li><strong>Preparation:</strong> Establish policies, tools, and trained response team</li><li><strong>Detection and Analysis:</strong> Identify and assess security incidents</li><li><strong>Containment:</strong> Limit the scope and impact of the incident</li><li><strong>Eradication:</strong> Remove threats and vulnerabilities</li><li><strong>Recovery:</strong> Restore systems to normal operation</li><li><strong>Post-Incident Activity:</strong> Learn and improve</li></ol><h3>Incident Classification</h3><ul><li><strong>Severity Levels:</strong> Critical, High, Medium, Low</li><li><strong>Incident Types:</strong> Malware, Unauthorized Access, Data Breach, DDoS</li></ul><h3>First Response Priorities</h3><ul><li>Preserve evidence</li><li>Contain the threat</li><li>Document everything</li><li>Escalate appropriately</li></ul>',
                    
                    '<h2>Digital Forensics Fundamentals</h2><p>Digital forensics involves collecting, preserving, and analyzing digital evidence.</p><h3>Forensics Principles</h3><ul><li><strong>Evidence Integrity:</strong> Maintain chain of custody</li><li><strong>Documentation:</strong> Record every action taken</li><li><strong>Repeatability:</strong> Results must be reproducible</li></ul><h3>Evidence Collection</h3><ul><li><strong>Live Data:</strong> Running processes, network connections, memory</li><li><strong>Disk Images:</strong> Bit-for-bit copies of storage media</li><li><strong>Log Files:</strong> System, application, and security logs</li></ul><h3>Forensics Tools</h3><ul><li><strong>Autopsy:</strong> Open-source digital forensics platform</li><li><strong>Volatility:</strong> Memory forensics framework</li></ul>',
                    
                    '<h2>Incident Reporting and Documentation</h2><p>Proper documentation is essential for legal, compliance, and improvement purposes.</p><h3>Incident Report Components</h3><ul><li><strong>Executive Summary:</strong> High-level overview for leadership</li><li><strong>Timeline:</strong> Chronological sequence of events</li><li><strong>Technical Details:</strong> Indicators of compromise, affected systems</li><li><strong>Impact Assessment:</strong> Data, financial, and reputational impact</li><li><strong>Root Cause Analysis:</strong> How the incident occurred</li><li><strong>Recommendations:</strong> Preventive measures</li></ul><h3>Regulatory Requirements in Zambia</h3><p>Organizations must be aware of the Data Protection Act requirements for data breach notification.</p>',
                    
                    '<h2>Module 7 Knowledge Check</h2><p>Test your incident response knowledge.</p>',
                    
                    // Module 8
                    '<h2>Introduction to SIEM</h2><p>Security Information and Event Management (SIEM) systems collect and analyze security data from across an organization.</p><h3>What SIEM Does</h3><ul><li><strong>Log Collection:</strong> Aggregates logs from firewalls, servers, applications</li><li><strong>Correlation:</strong> Identifies patterns across multiple data sources</li><li><strong>Alerting:</strong> Generates alerts based on predefined rules</li><li><strong>Dashboards:</strong> Visualizes security posture</li></ul><h3>Popular SIEM Tools</h3><ul><li><strong>Splunk:</strong> Enterprise SIEM with powerful search capabilities</li><li><strong>Microsoft Sentinel:</strong> Cloud-native SIEM and SOAR</li><li><strong>Elastic Stack (ELK):</strong> Open-source log analysis platform</li><li><strong>Wazuh:</strong> Open-source security monitoring</li></ul>',
                    
                    '<h2>Log Analysis and Monitoring</h2><p>Effective log analysis is crucial for detecting and investigating security incidents.</p><h3>Important Log Sources</h3><ul><li><strong>Operating System Logs:</strong> Windows Event Logs, Linux Syslog</li><li><strong>Firewall Logs:</strong> Connection attempts, blocked traffic</li><li><strong>Web Server Logs:</strong> HTTP requests, errors, access patterns</li><li><strong>Authentication Logs:</strong> Login attempts, privilege changes</li></ul><h3>What to Look For</h3><ul><li>Multiple failed login attempts (brute force)</li><li>Logins outside business hours</li><li>Unusual data transfer volumes</li><li>Known malicious IP addresses</li><li>Missing or modified log files</li></ul>',
                    
                    '<h2>SOC Operations and Workflow</h2><p>A Security Operations Center (SOC) is a centralized function that monitors and responds to security incidents.</p><h3>SOC Tiers</h3><ul><li><strong>Tier 1 (Alert Triage):</strong> Initial alert review and prioritization</li><li><strong>Tier 2 (Incident Response):</strong> Investigation and containment</li><li><strong>Tier 3 (Threat Hunting):</strong> Proactive threat identification</li></ul><h3>Key SOC Metrics</h3><ul><li><strong>MTTD (Mean Time to Detect):</strong> Average time to detect threats</li><li><strong>MTTR (Mean Time to Respond):</strong> Average time to respond to incidents</li></ul><h3>MITRE ATT&CK Framework</h3><p>MITRE ATT&CK is a globally accessible knowledge base of adversary tactics and techniques used for threat modeling and detection.</p>',
                    
                    '<h2>Module 8 Knowledge Check</h2><p>Test your SOC knowledge.</p>',
                    
                    // Module 9
                    '<h2>Security Frameworks and Standards</h2><p>Security frameworks provide structured approaches to managing cybersecurity risks.</p><h3>NIST Cybersecurity Framework</h3><p>The NIST CSF provides a policy framework of computer security guidance:</p><ul><li><strong>Identify:</strong> Understand and manage cybersecurity risk</li><li><strong>Protect:</strong> Implement safeguards to ensure service delivery</li><li><strong>Detect:</strong> Implement activities to identify events</li><li><strong>Respond:</strong> Take action on detected incidents</li><li><strong>Recover:</strong> Restore capabilities after incidents</li></ul><h3>ISO 27001</h3><p>International standard for information security management systems (ISMS).</p><h3>Other Important Standards</h3><ul><li><strong>CIS Controls:</strong> 20 prioritized security controls</li><li><strong>PCI DSS:</strong> Payment card industry security standard</li></ul>',
                    
                    '<h2>Risk Management</h2><p>Risk management is the process of identifying, assessing, and controlling threats to an organization.</p><h3>Risk Assessment Process</h3><ol><li><strong>Asset Identification:</strong> What needs protection?</li><li><strong>Threat Identification:</strong> What could go wrong?</li><li><strong>Vulnerability Assessment:</strong> What weaknesses exist?</li><li><strong>Risk Calculation:</strong> Risk = Likelihood x Impact</li></ol><h3>Risk Treatment Options</h3><ul><li><strong>Accept:</strong> Acknowledge and bear the risk</li><li><strong>Mitigate:</strong> Reduce likelihood or impact</li><li><strong>Transfer:</strong> Insurance or outsourcing</li><li><strong>Avoid:</strong> Eliminate the risk source</li></ul>',
                    
                    '<h2>Compliance and Audit</h2><p>Compliance ensures organizations meet regulatory and industry security requirements.</p><h3>Types of Audits</h3><ul><li><strong>Internal Audit:</strong> Conducted by organization\'s own audit team</li><li><strong>External Audit:</strong> Independent third-party assessment</li><li><strong>Regulatory Audit:</strong> Government-mandated compliance check</li></ul><h3>Zambia Data Protection Act</h3><p>Organizations in Zambia must comply with the Data Protection Act which requires:</p><ul><li>Lawful processing of personal data</li><li>Data subject rights (access, correction, deletion)</li><li>Data breach notification requirements</li></ul>',
                    
                    '<h2>Module 9 Knowledge Check</h2><p>Test your GRC knowledge.</p>',
                    
                    // Module 10
                    '<h2>Capstone Project Overview</h2><p>This capstone project allows you to apply all the skills you have learned throughout the course to a real-world scenario.</p><h3>Project Scenario</h3><p>You are hired as a Junior Security Analyst for a small financial services company in Lusaka. The company has 50 employees and processes mobile money transactions. They have recently experienced a phishing attack and want to improve their security posture.</p><h3>Your Tasks</h3><ol><li><strong>Risk Assessment:</strong> Identify and assess key risks to the organization</li><li><strong>Security Policy:</strong> Create an acceptable use policy</li><li><strong>Network Design:</strong> Propose a secure network architecture</li><li><strong>Incident Response Plan:</strong> Develop a basic incident response plan</li><li><strong>Security Awareness:</strong> Create a training outline for employees</li></ol>',
                    
                    '<h2>Career Preparation and Next Steps</h2><p>Congratulations on completing the Cybersecurity Fundamentals course!</p><h3>What You Have Learned</h3><ul><li>Core cybersecurity principles (CIA Triad, threat landscape)</li><li>Networking fundamentals and security</li><li>Malware, social engineering, and attack vectors</li><li>Security controls (firewalls, encryption, access control)</li><li>Web application security and secure coding</li><li>Ethical hacking basics and methodology</li><li>Incident response and digital forensics</li><li>Security operations and SIEM</li><li>Governance, risk, and compliance</li></ul><h3>Recommended Next Steps</h3><ol><li><strong>Hands-On Practice:</strong> Set up a home lab with virtual machines</li><li><strong>Online Platforms:</strong> TryHackMe, Hack The Box, PortSwigger Web Security Academy</li><li><strong>Certification Path:</strong> Consider CompTIA Security+</li><li><strong>Networking:</strong> Join cybersecurity communities and attend local events</li></ol>',
                    
                    '<h2>Final Assessment</h2><p>Comprehensive final examination covering all modules.</p>',
                ];
                
                $lessonCount = 0;
                foreach ($lessons as $index => $lesson) {
                    $db->query("INSERT INTO lessons (module_id, title, content, lesson_type, duration_minutes, display_order, is_preview, is_mandatory, points, created_at) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, NOW())",
                               [$lesson[0], $lesson[1], $lessonContents[$index], $lesson[2], $lesson[3], $lesson[4], $lesson[5], $lesson[6]]);
                    $lessonCount++;
                }
                $log[] = "Created $lessonCount lessons";
                
                // 5. Insert quizzes
                $quizData = [
                    [$course_id, 'Module 1 Quiz: Introduction to Cybersecurity', 'Test your understanding of cybersecurity fundamentals', 'Graded', 20, 3, 70.00],
                    [$course_id, 'Module 2 Quiz: Networking Fundamentals', 'Test your networking knowledge', 'Graded', 25, 3, 70.00],
                    [$course_id, 'Module 3 Quiz: Cyber Threats and Attacks', 'Test your knowledge of cyber threats', 'Graded', 25, 3, 70.00],
                    [$course_id, 'Module 4 Quiz: Security Controls and Defense', 'Test your knowledge of security controls', 'Graded', 25, 3, 70.00],
                    [$course_id, 'Module 5 Quiz: Web Application Security', 'Test your web security knowledge', 'Graded', 30, 3, 70.00],
                    [$course_id, 'Module 6 Quiz: Ethical Hacking Basics', 'Test your ethical hacking knowledge', 'Graded', 25, 3, 70.00],
                    [$course_id, 'Module 7 Quiz: Incident Response and Forensics', 'Test your incident response knowledge', 'Graded', 25, 3, 70.00],
                    [$course_id, 'Module 8 Quiz: Security Operations', 'Test your SOC knowledge', 'Graded', 25, 3, 70.00],
                    [$course_id, 'Module 9 Quiz: Governance, Risk, and Compliance', 'Test your GRC knowledge', 'Graded', 25, 3, 70.00],
                    [$course_id, 'Final Assessment: Cybersecurity Fundamentals', 'Comprehensive final assessment covering all modules', 'Final Exam', 60, 2, 75.00],
                ];
                
                $quiz_ids = [];
                foreach ($quizData as $qz) {
                    $db->query("INSERT INTO quizzes (course_id, title, description, quiz_type, time_limit_minutes, max_attempts, passing_score, randomize_questions, show_correct_answers, is_published, created_at) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, 1, 1, 1, NOW())",
                               [$qz[0], $qz[1], $qz[2], $qz[3], $qz[4], $qz[5], $qz[6]]);
                    $quiz_ids[] = $db->getConnection()->lastInsertId();
                }
                $log[] = "Created " . count($quizData) . " quizzes";
                
                // Helper function to insert questions with options
                function insertQuizQuestions($db, $quiz_id, $questions) {
                    foreach ($questions as $q) {
                        $db->query("INSERT INTO questions (question_type, question_text, points, explanation, created_at) 
                                   VALUES (?, ?, ?, ?, NOW())",
                                   [$q['type'], $q['text'], $q['points'], $q['explanation']]);
                        $qid = $db->getConnection()->lastInsertId();
                        
                        foreach ($q['options'] as $i => $opt) {
                            $db->query("INSERT INTO question_options (question_id, option_text, is_correct, display_order) 
                                       VALUES (?, ?, ?, ?)",
                                       [$qid, $opt['text'], $opt['correct'] ? 1 : 0, $i + 1]);
                        }
                        
                        $db->query("INSERT INTO quiz_questions (quiz_id, question_id, display_order) VALUES (?, ?, ?)",
                                   [$quiz_id, $qid, $q['order']]);
                    }
                }
                
                // Module 1 Quiz Questions
                insertQuizQuestions($db, $quiz_ids[0], [
                    ['type' => 'Multiple Choice', 'text' => 'What does the "C" in the CIA Triad stand for?', 'points' => 1, 'explanation' => 'The CIA Triad consists of Confidentiality, Integrity, and Availability.', 'order' => 1,
                     'options' => [['text' => 'Control', 'correct' => false], ['text' => 'Confidentiality', 'correct' => true], ['text' => 'Certification', 'correct' => false], ['text' => 'Compliance', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'Which of the following is NOT a type of threat actor?', 'points' => 1, 'explanation' => 'System Administrators are typically defenders, not threat actors.', 'order' => 2,
                     'options' => [['text' => 'Script Kiddie', 'correct' => false], ['text' => 'Cybercriminal', 'correct' => false], ['text' => 'System Administrator', 'correct' => true], ['text' => 'State-Sponsored Actor', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'A weakness in a system that could be exploited is called a(n):', 'points' => 1, 'explanation' => 'A vulnerability is a weakness in a system.', 'order' => 3,
                     'options' => [['text' => 'Threat', 'correct' => false], ['text' => 'Risk', 'correct' => false], ['text' => 'Vulnerability', 'correct' => true], ['text' => 'Exploit', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'What is the primary motivation of cybercriminals?', 'points' => 1, 'explanation' => 'Cybercriminals are primarily motivated by financial gain.', 'order' => 4,
                     'options' => [['text' => 'Political change', 'correct' => false], ['text' => 'Social justice', 'correct' => false], ['text' => 'Financial gain', 'correct' => true], ['text' => 'Personal recognition', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'Which principle ensures data is accessible when needed?', 'points' => 1, 'explanation' => 'Availability ensures systems and data are accessible.', 'order' => 5,
                     'options' => [['text' => 'Confidentiality', 'correct' => false], ['text' => 'Integrity', 'correct' => false], ['text' => 'Availability', 'correct' => true], ['text' => 'Authentication', 'correct' => false]]],
                ]);
                
                // Module 2 Quiz Questions
                insertQuizQuestions($db, $quiz_ids[1], [
                    ['type' => 'Multiple Choice', 'text' => 'At which OSI layer does routing occur?', 'points' => 1, 'explanation' => 'Routing occurs at Layer 3 (Network Layer).', 'order' => 1,
                     'options' => [['text' => 'Layer 2', 'correct' => false], ['text' => 'Layer 3', 'correct' => true], ['text' => 'Layer 4', 'correct' => false], ['text' => 'Layer 7', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'Which protocol uses port 443 by default?', 'points' => 1, 'explanation' => 'HTTPS uses port 443.', 'order' => 2,
                     'options' => [['text' => 'HTTP', 'correct' => false], ['text' => 'FTP', 'correct' => false], ['text' => 'HTTPS', 'correct' => true], ['text' => 'SSH', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'What does ARP stand for?', 'points' => 1, 'explanation' => 'ARP maps IP addresses to MAC addresses.', 'order' => 3,
                     'options' => [['text' => 'Address Resolution Protocol', 'correct' => true], ['text' => 'Advanced Routing Protocol', 'correct' => false], ['text' => 'Application Resource Protocol', 'correct' => false], ['text' => 'Automatic Response Procedure', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'Which is the encrypted alternative to Telnet?', 'points' => 1, 'explanation' => 'SSH provides encrypted remote access.', 'order' => 4,
                     'options' => [['text' => 'FTP', 'correct' => false], ['text' => 'SSH', 'correct' => true], ['text' => 'HTTP', 'correct' => false], ['text' => 'SMTP', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'Network segmentation using VLANs primarily helps with:', 'points' => 1, 'explanation' => 'VLANs segment networks to contain breaches.', 'order' => 5,
                     'options' => [['text' => 'Increasing internet speed', 'correct' => false], ['text' => 'Limiting attack spread', 'correct' => true], ['text' => 'Reducing cable costs', 'correct' => false], ['text' => 'Improving wireless signal', 'correct' => false]]],
                ]);
                
                // Module 3 Quiz Questions
                insertQuizQuestions($db, $quiz_ids[2], [
                    ['type' => 'Multiple Choice', 'text' => 'Which malware type encrypts files and demands payment?', 'points' => 1, 'explanation' => 'Ransomware encrypts files and demands payment.', 'order' => 1,
                     'options' => [['text' => 'Virus', 'correct' => false], ['text' => 'Worm', 'correct' => false], ['text' => 'Ransomware', 'correct' => true], ['text' => 'Spyware', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'What is phishing?', 'points' => 1, 'explanation' => 'Phishing is a fraudulent attempt to obtain sensitive information.', 'order' => 2,
                     'options' => [['text' => 'A network scanning technique', 'correct' => false], ['text' => 'A fraudulent attempt to obtain sensitive information', 'correct' => true], ['text' => 'A type of firewall', 'correct' => false], ['text' => 'A password hashing method', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'In the Cyber Kill Chain, what comes after "Delivery"?', 'points' => 1, 'explanation' => 'The phases are: Reconnaissance -> Weaponization -> Delivery -> Exploitation -> Installation -> C2 -> Actions.', 'order' => 3,
                     'options' => [['text' => 'Reconnaissance', 'correct' => false], ['text' => 'Weaponization', 'correct' => false], ['text' => 'Exploitation', 'correct' => true], ['text' => 'Installation', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'Which of the following is a social engineering technique?', 'points' => 1, 'explanation' => 'Tailgating is a physical social engineering technique.', 'order' => 4,
                     'options' => [['text' => 'SQL Injection', 'correct' => false], ['text' => 'Tailgating', 'correct' => true], ['text' => 'Buffer Overflow', 'correct' => false], ['text' => 'ARP Spoofing', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'What is the primary purpose of a keylogger?', 'points' => 1, 'explanation' => 'A keylogger records keystrokes to capture passwords.', 'order' => 5,
                     'options' => [['text' => 'Encrypt files', 'correct' => false], ['text' => 'Record keystrokes', 'correct' => true], ['text' => 'Scan networks', 'correct' => false], ['text' => 'Block websites', 'correct' => false]]],
                ]);
                
                // Module 4 Quiz Questions
                insertQuizQuestions($db, $quiz_ids[3], [
                    ['type' => 'Multiple Choice', 'text' => 'Which encryption type uses the same key for encryption and decryption?', 'points' => 1, 'explanation' => 'Symmetric encryption uses a single shared key.', 'order' => 1,
                     'options' => [['text' => 'Asymmetric', 'correct' => false], ['text' => 'Symmetric', 'correct' => true], ['text' => 'Hashing', 'correct' => false], ['text' => 'Public-key', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'What does MFA stand for?', 'points' => 1, 'explanation' => 'Multi-Factor Authentication requires two or more verification factors.', 'order' => 2,
                     'options' => [['text' => 'Multi-Factor Authentication', 'correct' => true], ['text' => 'Managed Firewall Access', 'correct' => false], ['text' => 'Multi-Function Application', 'correct' => false], ['text' => 'Modular Framework Architecture', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'In RBAC, access is determined by:', 'points' => 1, 'explanation' => 'Role-Based Access Control grants access based on user roles.', 'order' => 3,
                     'options' => [['text' => 'User attributes', 'correct' => false], ['text' => 'Resource ownership', 'correct' => false], ['text' => 'User roles', 'correct' => true], ['text' => 'Security labels', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'Which system actively blocks detected threats in real-time?', 'points' => 1, 'explanation' => 'IPS actively blocks threats while IDS only detects and alerts.', 'order' => 4,
                     'options' => [['text' => 'IDS', 'correct' => false], ['text' => 'IPS', 'correct' => true], ['text' => 'Firewall', 'correct' => false], ['text' => 'Proxy', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'TLS is used for:', 'points' => 1, 'explanation' => 'TLS encrypts web traffic and enables HTTPS.', 'order' => 5,
                     'options' => [['text' => 'File compression', 'correct' => false], ['text' => 'Secure web communication', 'correct' => true], ['text' => 'Database indexing', 'correct' => false], ['text' => 'Email routing', 'correct' => false]]],
                ]);
                
                // Module 5 Quiz Questions
                insertQuizQuestions($db, $quiz_ids[4], [
                    ['type' => 'Multiple Choice', 'text' => 'Which OWASP Top 10 item involves untrusted data sent to interpreters?', 'points' => 1, 'explanation' => 'Injection flaws occur when untrusted data is sent to interpreters.', 'order' => 1,
                     'options' => [['text' => 'Broken Access Control', 'correct' => false], ['text' => 'Cryptographic Failures', 'correct' => false], ['text' => 'Injection', 'correct' => true], ['text' => 'Insecure Design', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'What is the best defense against SQL Injection?', 'points' => 1, 'explanation' => 'Parameterized queries separate SQL code from data.', 'order' => 2,
                     'options' => [['text' => 'Input validation only', 'correct' => false], ['text' => 'Parameterized queries', 'correct' => true], ['text' => 'Firewall rules', 'correct' => false], ['text' => 'URL encoding', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'Which XSS type stores the malicious script on the server?', 'points' => 1, 'explanation' => 'Stored (Persistent) XSS stores scripts permanently on the server.', 'order' => 3,
                     'options' => [['text' => 'Reflected XSS', 'correct' => false], ['text' => 'Stored XSS', 'correct' => true], ['text' => 'DOM-based XSS', 'correct' => false], ['text' => 'Blind XSS', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'Which header prevents clickjacking attacks?', 'points' => 1, 'explanation' => 'X-Frame-Options controls whether a page can be displayed in frames.', 'order' => 4,
                     'options' => [['text' => 'Content-Security-Policy', 'correct' => false], ['text' => 'X-Frame-Options', 'correct' => true], ['text' => 'X-XSS-Protection', 'correct' => false], ['text' => 'Strict-Transport-Security', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'What does CSP stand for in web security?', 'points' => 1, 'explanation' => 'Content Security Policy helps prevent XSS and data injection.', 'order' => 5,
                     'options' => [['text' => 'Cross-Site Protection', 'correct' => false], ['text' => 'Content Security Policy', 'correct' => true], ['text' => 'Client-Side Protocol', 'correct' => false], ['text' => 'Certificate Signing Process', 'correct' => false]]],
                ]);
                
                // Module 6 Quiz Questions
                insertQuizQuestions($db, $quiz_ids[5], [
                    ['type' => 'Multiple Choice', 'text' => 'Which type of hacker is authorized to test systems?', 'points' => 1, 'explanation' => 'White Hat hackers have authorization to test and improve security.', 'order' => 1,
                     'options' => [['text' => 'Black Hat', 'correct' => false], ['text' => 'White Hat', 'correct' => true], ['text' => 'Gray Hat', 'correct' => false], ['text' => 'Script Kiddie', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'What does OSINT stand for?', 'points' => 1, 'explanation' => 'OSINT refers to intelligence gathered from publicly available sources.', 'order' => 2,
                     'options' => [['text' => 'Open Source Intelligence', 'correct' => true], ['text' => 'Operating System Integration', 'correct' => false], ['text' => 'Online Security Intelligence', 'correct' => false], ['text' => 'Organizational Security Interface', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'Which tool is commonly used for port scanning?', 'points' => 1, 'explanation' => 'Nmap is the industry-standard tool for port scanning.', 'order' => 3,
                     'options' => [['text' => 'Wireshark', 'correct' => false], ['text' => 'Nmap', 'correct' => true], ['text' => 'Metasploit', 'correct' => false], ['text' => 'Burp Suite', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'In a Black Box test, the tester has:', 'points' => 1, 'explanation' => 'Black Box testing simulates an external attacker with no prior knowledge.', 'order' => 4,
                     'options' => [['text' => 'Full knowledge', 'correct' => false], ['text' => 'No prior knowledge', 'correct' => true], ['text' => 'Partial knowledge', 'correct' => false], ['text' => 'Source code access', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'What does CVE stand for?', 'points' => 1, 'explanation' => 'CVE provides standardized identifiers for known vulnerabilities.', 'order' => 5,
                     'options' => [['text' => 'Common Vulnerability Enumeration', 'correct' => false], ['text' => 'Common Vulnerabilities and Exposures', 'correct' => true], ['text' => 'Critical Vulnerability Entry', 'correct' => false], ['text' => 'Computer Virus Encyclopedia', 'correct' => false]]],
                ]);
                
                // Module 7 Quiz Questions
                insertQuizQuestions($db, $quiz_ids[6], [
                    ['type' => 'Multiple Choice', 'text' => 'Which NIST phase involves removing threats and vulnerabilities?', 'points' => 1, 'explanation' => 'Eradication removes threats and eliminates vulnerabilities.', 'order' => 1,
                     'options' => [['text' => 'Containment', 'correct' => false], ['text' => 'Eradication', 'correct' => true], ['text' => 'Recovery', 'correct' => false], ['text' => 'Detection', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'What is the primary purpose of chain of custody?', 'points' => 1, 'explanation' => 'Chain of custody ensures digital evidence is admissible in court.', 'order' => 2,
                     'options' => [['text' => 'Speed up investigation', 'correct' => false], ['text' => 'Ensure evidence admissibility', 'correct' => true], ['text' => 'Reduce costs', 'correct' => false], ['text' => 'Identify attackers', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'Which tool is used for memory forensics?', 'points' => 1, 'explanation' => 'Volatility is an open-source memory forensics framework.', 'order' => 3,
                     'options' => [['text' => 'Nmap', 'correct' => false], ['text' => 'Volatility', 'correct' => true], ['text' => 'Wireshark', 'correct' => false], ['text' => 'Nessus', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'What should be done FIRST when responding to an incident?', 'points' => 1, 'explanation' => 'Preserving evidence is critical for investigation.', 'order' => 4,
                     'options' => [['text' => 'Format affected systems', 'correct' => false], ['text' => 'Preserve evidence', 'correct' => true], ['text' => 'Notify the media', 'correct' => false], ['text' => 'Update antivirus', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'A bit-for-bit copy of storage media is called a:', 'points' => 1, 'explanation' => 'A disk image preserves all data including deleted files.', 'order' => 5,
                     'options' => [['text' => 'Snapshot', 'correct' => false], ['text' => 'Disk Image', 'correct' => true], ['text' => 'Backup', 'correct' => false], ['text' => 'Archive', 'correct' => false]]],
                ]);
                
                // Module 8 Quiz Questions
                insertQuizQuestions($db, $quiz_ids[7], [
                    ['type' => 'Multiple Choice', 'text' => 'What does SIEM stand for?', 'points' => 1, 'explanation' => 'SIEM collects, correlates, and analyzes security events.', 'order' => 1,
                     'options' => [['text' => 'Security Information and Event Management', 'correct' => true], ['text' => 'System Intelligence and Event Monitoring', 'correct' => false], ['text' => 'Secure Internet and Email Management', 'correct' => false], ['text' => 'System Integration and Enterprise Monitoring', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'Which SOC tier is responsible for initial alert review?', 'points' => 1, 'explanation' => 'Tier 1 analysts handle initial alert triage.', 'order' => 2,
                     'options' => [['text' => 'Tier 1', 'correct' => true], ['text' => 'Tier 2', 'correct' => false], ['text' => 'Tier 3', 'correct' => false], ['text' => 'Tier 4', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'MTTD stands for:', 'points' => 1, 'explanation' => 'MTTD measures average time to detect threats.', 'order' => 3,
                     'options' => [['text' => 'Mean Time to Detect', 'correct' => true], ['text' => 'Maximum Time to Detection', 'correct' => false], ['text' => 'Minimum Technical Threat Duration', 'correct' => false], ['text' => 'Managed Threat Transfer Delay', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'Which of the following is an open-source SIEM tool?', 'points' => 1, 'explanation' => 'Wazuh is an open-source security monitoring platform.', 'order' => 4,
                     'options' => [['text' => 'Splunk', 'correct' => false], ['text' => 'QRadar', 'correct' => false], ['text' => 'Wazuh', 'correct' => true], ['text' => 'Microsoft Sentinel', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'What is the MITRE ATT&CK framework used for?', 'points' => 1, 'explanation' => 'MITRE ATT&CK is used for threat modeling and detection.', 'order' => 5,
                     'options' => [['text' => 'Network routing', 'correct' => false], ['text' => 'Threat modeling and detection', 'correct' => true], ['text' => 'Password management', 'correct' => false], ['text' => 'Data encryption', 'correct' => false]]],
                ]);
                
                // Module 9 Quiz Questions
                insertQuizQuestions($db, $quiz_ids[8], [
                    ['type' => 'Multiple Choice', 'text' => 'The NIST CSF consists of how many core functions?', 'points' => 1, 'explanation' => 'NIST CSF has 5 core functions.', 'order' => 1,
                     'options' => [['text' => '3', 'correct' => false], ['text' => '4', 'correct' => false], ['text' => '5', 'correct' => true], ['text' => '6', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'ISO 27001 is a standard for:', 'points' => 1, 'explanation' => 'ISO 27001 is for Information Security Management Systems.', 'order' => 2,
                     'options' => [['text' => 'Payment processing', 'correct' => false], ['text' => 'Information security management', 'correct' => true], ['text' => 'Network routing', 'correct' => false], ['text' => 'Software development', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'Risk is calculated as:', 'points' => 1, 'explanation' => 'Risk = Likelihood x Impact.', 'order' => 3,
                     'options' => [['text' => 'Threat + Vulnerability', 'correct' => false], ['text' => 'Likelihood x Impact', 'correct' => true], ['text' => 'Asset Value - Cost', 'correct' => false], ['text' => 'Threat - Control', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'Which risk treatment involves transferring risk to a third party?', 'points' => 1, 'explanation' => 'Risk transfer moves financial consequence to another party.', 'order' => 4,
                     'options' => [['text' => 'Accept', 'correct' => false], ['text' => 'Mitigate', 'correct' => false], ['text' => 'Transfer', 'correct' => true], ['text' => 'Avoid', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'Which NIST function involves taking action on detected incidents?', 'points' => 1, 'explanation' => 'The Respond function includes taking action on incidents.', 'order' => 5,
                     'options' => [['text' => 'Identify', 'correct' => false], ['text' => 'Protect', 'correct' => false], ['text' => 'Detect', 'correct' => false], ['text' => 'Respond', 'correct' => true]]],
                ]);
                
                // Final Assessment Questions
                insertQuizQuestions($db, $quiz_ids[9], [
                    ['type' => 'Multiple Choice', 'text' => 'The three principles of the CIA Triad are:', 'points' => 1, 'explanation' => 'CIA Triad = Confidentiality, Integrity, Availability.', 'order' => 1,
                     'options' => [['text' => 'Control, Integrity, Authentication', 'correct' => false], ['text' => 'Confidentiality, Integrity, Availability', 'correct' => true], ['text' => 'Certification, Implementation, Assessment', 'correct' => false], ['text' => 'Compliance, Investigation, Analysis', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'Which layer of the OSI model handles routing?', 'points' => 1, 'explanation' => 'Layer 3 (Network Layer) handles routing.', 'order' => 2,
                     'options' => [['text' => 'Data Link', 'correct' => false], ['text' => 'Network', 'correct' => true], ['text' => 'Transport', 'correct' => false], ['text' => 'Application', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'Which malware type does NOT need a host program?', 'points' => 1, 'explanation' => 'Worms are self-replicating and do not need a host.', 'order' => 3,
                     'options' => [['text' => 'Virus', 'correct' => false], ['text' => 'Worm', 'correct' => true], ['text' => 'Trojan', 'correct' => false], ['text' => 'Rootkit', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'Which encryption algorithm is symmetric?', 'points' => 1, 'explanation' => 'AES is symmetric. RSA and ECC are asymmetric.', 'order' => 4,
                     'options' => [['text' => 'RSA', 'correct' => false], ['text' => 'AES', 'correct' => true], ['text' => 'ECC', 'correct' => false], ['text' => 'DSA', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'What is the primary defense against SQL Injection?', 'points' => 1, 'explanation' => 'Parameterized queries are the most effective defense.', 'order' => 5,
                     'options' => [['text' => 'Input validation', 'correct' => false], ['text' => 'Parameterized queries', 'correct' => true], ['text' => 'Web Application Firewall', 'correct' => false], ['text' => 'URL encoding', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'In a Black Box penetration test, the tester has:', 'points' => 1, 'explanation' => 'Black Box = no prior knowledge.', 'order' => 6,
                     'options' => [['text' => 'Full knowledge of systems', 'correct' => false], ['text' => 'No prior knowledge', 'correct' => true], ['text' => 'Source code access', 'correct' => false], ['text' => 'Administrator credentials', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'Which phase of incident response involves limiting damage?', 'points' => 1, 'explanation' => 'Containment isolates the incident.', 'order' => 7,
                     'options' => [['text' => 'Detection', 'correct' => false], ['text' => 'Containment', 'correct' => true], ['text' => 'Eradication', 'correct' => false], ['text' => 'Recovery', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'What does a SIEM system primarily do?', 'points' => 1, 'explanation' => 'SIEM collects and analyzes security events.', 'order' => 8,
                     'options' => [['text' => 'Encrypt network traffic', 'correct' => false], ['text' => 'Collect and analyze security events', 'correct' => true], ['text' => 'Block malicious websites', 'correct' => false], ['text' => 'Manage user passwords', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'The NIST Cybersecurity Framework has how many functions?', 'points' => 1, 'explanation' => 'NIST CSF has 5 functions.', 'order' => 9,
                     'options' => [['text' => '3', 'correct' => false], ['text' => '4', 'correct' => false], ['text' => '5', 'correct' => true], ['text' => '6', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'Which access control model grants permissions based on user roles?', 'points' => 1, 'explanation' => 'RBAC grants permissions based on roles.', 'order' => 10,
                     'options' => [['text' => 'DAC', 'correct' => false], ['text' => 'MAC', 'correct' => false], ['text' => 'RBAC', 'correct' => true], ['text' => 'ABAC', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'What is the purpose of a honeypot?', 'points' => 1, 'explanation' => 'A honeypot attracts and detects attackers.', 'order' => 11,
                     'options' => [['text' => 'Store backups', 'correct' => false], ['text' => 'Attract and detect attackers', 'correct' => true], ['text' => 'Encrypt data', 'correct' => false], ['text' => 'Speed up networks', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'Which social engineering technique involves following someone into a secure area?', 'points' => 1, 'explanation' => 'Tailgating follows someone into restricted areas.', 'order' => 12,
                     'options' => [['text' => 'Phishing', 'correct' => false], ['text' => 'Pretexting', 'correct' => false], ['text' => 'Tailgating', 'correct' => true], ['text' => 'Baiting', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'TLS is used to:', 'points' => 1, 'explanation' => 'TLS encrypts web traffic for HTTPS.', 'order' => 13,
                     'options' => [['text' => 'Compress files', 'correct' => false], ['text' => 'Encrypt web traffic', 'correct' => true], ['text' => 'Scan for viruses', 'correct' => false], ['text' => 'Route packets', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'What does OWASP stand for?', 'points' => 1, 'explanation' => 'Open Web Application Security Project.', 'order' => 14,
                     'options' => [['text' => 'Open Web Application Security Project', 'correct' => true], ['text' => 'Online Web Attack and Security Protocol', 'correct' => false], ['text' => 'Operational Web Application Standard Procedure', 'correct' => false], ['text' => 'Open Web Authentication Security Program', 'correct' => false]]],
                    ['type' => 'Multiple Choice', 'text' => 'Risk is best defined as:', 'points' => 1, 'explanation' => 'Risk = Likelihood x Impact.', 'order' => 15,
                     'options' => [['text' => 'Threat + Vulnerability', 'correct' => false], ['text' => 'Likelihood x Impact', 'correct' => true], ['text' => 'Asset x Control', 'correct' => false], ['text' => 'Cost + Benefit', 'correct' => false]]],
                ]);
                
                $log[] = "Created all quiz questions and options";
                
                // Commit transaction
                $db->getConnection()->commit();
                
                // Display success
                echo '<div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4">';
                echo '<h3 class="text-green-800 font-bold text-lg">Installation Successful!</h3>';
                echo '<p class="text-green-700">The Cybersecurity Fundamentals course has been installed successfully.</p>';
                echo '</div>';
                
                echo '<h3 class="font-bold text-gray-800 mb-2">Installation Log:</h3>';
                echo '<ul class="bg-gray-50 p-4 rounded list-disc list-inside text-gray-700">';
                foreach ($log as $entry) {
                    echo '<li>' . htmlspecialchars($entry) . '</li>';
                }
                echo '</ul>';
                
                echo '<div class="mt-6 p-4 bg-red-50 border border-red-200 rounded">';
                echo '<p class="text-red-700 font-bold">IMPORTANT: Delete this file after installation!</p>';
                echo '<p class="text-red-600 text-sm">File: ' . htmlspecialchars(__FILE__) . '</p>';
                echo '</div>';
                
            } catch (Exception $e) {
                if (isset($db)) {
                    $db->getConnection()->rollBack();
                }
                echo '<div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">';
                echo '<h3 class="text-red-800 font-bold text-lg">Installation Failed</h3>';
                echo '<p class="text-red-700">' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '</div>';
            }
        } else {
            // Show install form
            ?>
            <form method="POST" class="mt-6">
                <div class="flex items-center mb-4">
                    <input type="checkbox" id="confirm" name="confirm_install" value="1" required class="mr-2 h-4 w-4">
                    <label for="confirm" class="text-gray-700">I understand this will insert a new course into the database and I have made a backup if needed.</label>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
                    Install Cybersecurity Course
                </button>
            </form>
            <?php
        }
        ?>
    </div>
</body>
</html>
