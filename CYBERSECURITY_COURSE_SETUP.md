# Cybersecurity Course Setup Guide

## Overview

A complete **Cybersecurity Fundamentals** course has been created for the Edutrack LMS. This course covers essential cybersecurity topics from beginner to intermediate level, preparing students for entry-level cybersecurity roles.

## Course Details

| Attribute | Value |
|-----------|-------|
| **Title** | Cybersecurity Fundamentals |
| **Slug** | `cybersecurity-fundamentals` |
| **Level** | Beginner |
| **Duration** | 12 weeks |
| **Price** | ZMW 4,500 |
| **Category** | Cybersecurity (new category created) |
| **Total Hours** | 96 hours |

## Course Structure

### Module 1: Introduction to Cybersecurity
- What is Cybersecurity?
- The Cyber Threat Landscape
- Cybersecurity Career Paths
- **Quiz:** 5 questions on fundamentals

### Module 2: Networking Fundamentals
- The OSI Model (7 layers)
- TCP/IP and Network Protocols
- Network Security Basics
- **Quiz:** 5 questions on networking

### Module 3: Cyber Threats and Attacks
- Types of Malware (virus, worm, trojan, ransomware, etc.)
- Social Engineering Attacks (phishing, tailgating, etc.)
- Attack Vectors and Exploits
- **Quiz:** 5 questions on threats

### Module 4: Security Controls and Defense
- Firewalls and Network Defense
- Encryption and Cryptography
- Access Control and Authentication
- **Quiz:** 5 questions on controls

### Module 5: Web Application Security
- OWASP Top 10 Vulnerabilities
- SQL Injection and XSS
- Secure Coding Practices
- **Quiz:** 5 questions on web security

### Module 6: Ethical Hacking Basics
- Introduction to Ethical Hacking
- Reconnaissance and Scanning
- Vulnerability Exploitation Basics
- **Quiz:** 5 questions on ethical hacking

### Module 7: Incident Response and Forensics
- Incident Response Process (NIST framework)
- Digital Forensics Fundamentals
- Incident Reporting and Documentation
- **Quiz:** 5 questions on incident response

### Module 8: Security Operations (SOC)
- Introduction to SIEM
- Log Analysis and Monitoring
- SOC Operations and Workflow
- **Quiz:** 5 questions on SOC operations

### Module 9: Governance, Risk, and Compliance
- Security Frameworks and Standards (NIST, ISO 27001)
- Risk Management
- Compliance and Audit
- **Quiz:** 5 questions on GRC

### Module 10: Capstone Project
- Capstone Project Overview (real-world scenario)
- Career Preparation and Next Steps
- **Final Assessment:** 15 comprehensive questions

## Installation Methods

### Method 1: Web Installer (Recommended)

1. Upload the file `public/install-cybersecurity-course.php` to your server
2. Access it via browser:
   - As admin: Log into admin panel first, then visit `/install-cybersecurity-course.php`
   - Or use secret key: `/install-cybersecurity-course.php?key=edutrack2024`
3. Check the confirmation checkbox and click "Install Cybersecurity Course"
4. **Delete the file after installation** for security

### Method 2: SQL Import

1. Import the file `database/cybersecurity_course_content.sql` into your MySQL database
2. The SQL file uses MySQL variables and should work with MariaDB 10.3+ / MySQL 5.7+
3. Run: `mysql -u username -p database_name < database/cybersecurity_course_content.sql`

**Note:** The SQL file is designed to be idempotent regarding the category (uses INSERT IGNORE), but will create a new course each time it's run. Only run it once.

## Content Statistics

| Item | Count |
|------|-------|
| Course Categories | 1 (Cybersecurity) |
| Courses | 1 (Cybersecurity Fundamentals) |
| Modules | 10 |
| Lessons | 43 |
| Quizzes | 10 |
| Questions | 65 |
| Question Options | 260 |

## Key Features

- **Comprehensive Coverage:** From cybersecurity basics to advanced topics like SIEM, incident response, and GRC
- **Zambia-Focused Content:** References to local context (mobile money, Data Protection Act, Lusaka scenarios)
- **Career-Oriented:** Includes career paths, certification recommendations, and job search tips
- **Hands-On:** Covers practical tools like Nmap, Wireshark, Metasploit, Splunk
- **Assessments:** Each module has a graded quiz + a comprehensive final exam
- **Capstone Project:** Real-world scenario applying all learned skills

## Next Steps

1. **Install the course** using one of the methods above
2. **Add video content** - The lessons are set up with placeholder content. Add actual video URLs to the `video_url` field in the `lessons` table
3. **Assign an instructor** - Update the `instructor_id` field in the `courses` table if needed
4. **Set start/end dates** if you want scheduled enrollment periods
5. **Promote the course** on the homepage and social media

## Customization

To modify the course content after installation:
- Edit lessons through the admin panel at `/admin/index.php?page=modules`
- Edit quiz questions through the database or build an admin quiz editor
- Update course pricing in the admin settings

## Security Note

Remember to delete `public/install-cybersecurity-course.php` after installation to prevent unauthorized course creation.

---

*Created for Edutrack Computer Training College, Kalomo, Zambia*
