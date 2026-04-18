# Edutrack LMS - Learning Management System

**Edutrack Computer Training College - TEVETA Registered Institution**

A custom PHP-based Learning Management System for delivering the Cybersecurity Certificate Program and other vocational courses.

## 📁 Repository Structure

```
edutrack-lms/
├── README.md                 # This file
├── AGENTS.md                 # AI agent documentation
├── .env                      # Environment configuration
├── .htaccess                 # Apache rewrite rules
│
├── course_materials/         # 📚 All course content
│   ├── README.md
│   ├── module1_foundation/   # Module 1 (Weeks 1-3)
│   │   ├── topic1_computer_fundamentals/
│   │   ├── topic2_os/
│   │   ├── topic3_programming/      # Python exercises
│   │   ├── topic4_math/
│   │   └── topic5_networking/
│   ├── module2_cybersecurity/
│   ├── module3_threat_detection/
│   └── module4_capstone/
│
├── docs/                     # 📄 Documentation
│   ├── planning/             # Program outlines
│   ├── CODE_REVIEW.md        # Code review findings
│   ├── SYSTEM_REVIEW.md      # System architecture review
│   └── ...
│
├── scripts/                  # 🔧 Utility scripts
│   └── tools/                # PPTX generation, etc.
│
├── public/                   # 🌐 Web root
│   ├── index.php
│   ├── assets/               # CSS, JS, images
│   ├── api/                  # REST API endpoints
│   ├── admin/                # Admin panel
│   ├── instructor/           # Instructor dashboard
│   └── student/              # Student pages
│
├── src/                      # 💻 Application source
│   ├── bootstrap.php         # Application initialization
│   ├── classes/              # PHP classes (Course, User, etc.)
│   ├── includes/             # Core functions
│   ├── middleware/           # Access control
│   ├── mail/                 # Email templates
│   └── templates/            # View components
│
├── config/                   # ⚙️ Configuration files
├── database/                 # 🗄️ SQL schemas and migrations
├── cron/                     # ⏰ Scheduled tasks
└── storage/                  # 💾 Logs, sessions, cache
```

## 🚀 Quick Start

### Prerequisites
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Apache with mod_rewrite
- Composer

### Installation
1. Clone the repository
2. Copy `.env.example` to `.env` and configure
3. Run `composer install`
4. Import `database/complete_lms_schema.sql`
5. Ensure `storage/` and `public/uploads/` are writable
6. Configure Apache to point to `public/` directory

### Course Materials
All course content is organized in `course_materials/`:

```bash
# Navigate to Python exercises
cd course_materials/module1_foundation/topic3_programming/python_exercises

# Run an exercise
python3 01_hello_security.py
```

## 📖 Key Documentation

- **AGENTS.md** - Guidelines for AI assistants working on this codebase
- **docs/CYBERSECURITY_PROGRAM_OUTLINE.md** - Full program curriculum
- **docs/CODE_REVIEW.md** - Code quality findings and fixes
- **course_materials/README.md** - Course content navigation

## 🛠️ Technology Stack

| Component | Technology |
|-----------|------------|
| Backend | PHP 8.0+ (custom framework) |
| Database | MySQL/MariaDB |
| Frontend | Tailwind CSS, Alpine.js, Chart.js |
| Email | PHPMailer |
| PDF | TCPDF |
| Payment | Lenco Payment Gateway |

## 🔒 Security

See `AGENTS.md` for security considerations and `docs/CODE_REVIEW.md` for recent security fixes.

## 📞 Support

- **Email:** edutrackzambia@gmail.com
- **Phone:** +260 770 666 937
- **Location:** Kalomo, Zambia

## 📜 License

© 2024 Edutrack Computer Training College. All rights reserved.

TEVETA Registration: [Registration Number]
