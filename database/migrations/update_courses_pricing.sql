-- =====================================================
-- EDUTRACK COMPUTER TRAINING COLLEGE - Course Updates
-- Update course durations and pricing to match official rates
-- =====================================================

-- Note: Prices are in ZMW (Zambian Kwacha)
-- Duration conversions: 1 month = 4 weeks

-- =====================================================
-- UPDATE EXISTING COURSES
-- =====================================================

-- Core ICT Courses (category_id: 1)
-- Microsoft Office Suite - 8 weeks - K2500
UPDATE courses SET
    price = 2500.00,
    duration_weeks = 8,
    total_hours = 64.00,
    description = 'Comprehensive training in Microsoft Word, Excel, PowerPoint, Publisher and Internet skills. Learn document creation, spreadsheet analysis, presentations, desktop publishing and internet navigation for professional environments.',
    short_description = 'Master Word, Excel, PowerPoint, Publisher & Internet'
WHERE slug = 'microsoft-office-suite';

-- Digital Literacy - 2 weeks - K850
UPDATE courses SET
    price = 850.00,
    duration_weeks = 2,
    total_hours = 16.00,
    description = 'Essential digital skills for the modern workplace including email, internet research, cloud storage, online collaboration, and digital safety.',
    short_description = 'Essential digital skills for everyone'
WHERE slug = 'digital-literacy';

-- Record Management - 6 weeks - K1500
UPDATE courses SET
    price = 1500.00,
    duration_weeks = 6,
    total_hours = 48.00,
    description = 'Professional records and information management systems. Learn filing systems, document control, archiving, and compliance with data protection regulations.',
    short_description = 'Professional records management'
WHERE slug = 'record-management';

-- Programming & Software Development (category_id: 2)
-- Python Programming - 3 months (12 weeks) - K3000
UPDATE courses SET
    price = 3000.00,
    discount_price = NULL,
    duration_weeks = 12,
    total_hours = 96.00
WHERE slug = 'python-programming';

-- Java Programming - 3 months (12 weeks) - K3000
UPDATE courses SET
    price = 3000.00,
    duration_weeks = 12,
    total_hours = 96.00
WHERE slug = 'java-programming';

-- Web Development - 3 months (12 weeks) - K3000
UPDATE courses SET
    price = 3000.00,
    discount_price = NULL,
    duration_weeks = 12,
    total_hours = 96.00
WHERE slug = 'web-development';

-- Mobile App Development - 3 months (12 weeks) - K3000
UPDATE courses SET
    price = 3000.00,
    duration_weeks = 12,
    total_hours = 96.00
WHERE slug = 'mobile-app-development';

-- Software Engineering - 3 months (12 weeks) - K3000
UPDATE courses SET
    price = 3000.00,
    duration_weeks = 12,
    total_hours = 96.00,
    title = 'Certificate in Software Engineering',
    description = 'Software development methodologies, version control with Git/GitHub, testing, CI/CD, and collaborative development practices.',
    short_description = 'Professional software engineering'
WHERE slug = 'software-engineering-git';

-- Data, Security & Networks (category_id: 3)
-- Data Analysis - 8 weeks - K1500
UPDATE courses SET
    price = 1500.00,
    duration_weeks = 8,
    total_hours = 64.00
WHERE slug = 'data-analysis';

-- Cyber Security - 8 weeks - K2500
UPDATE courses SET
    price = 2500.00,
    discount_price = NULL,
    duration_weeks = 8,
    total_hours = 64.00
WHERE slug = 'cyber-security';

-- Database Management - 6 weeks - K1500
UPDATE courses SET
    price = 1500.00,
    duration_weeks = 6,
    total_hours = 48.00
WHERE slug = 'database-management';

-- Emerging Technologies (category_id: 4)
-- Artificial Intelligence - 3 weeks - K850
UPDATE courses SET
    price = 850.00,
    discount_price = NULL,
    duration_weeks = 3,
    total_hours = 24.00,
    title = 'Certificate in Artificial Intelligence',
    description = 'Introduction to artificial intelligence concepts and applications. Learn AI fundamentals, machine learning basics, and practical applications in modern technology.',
    short_description = 'AI fundamentals and applications'
WHERE slug = 'ai-machine-learning';

-- Digital Media & Creative Skills (category_id: 5)
-- Graphic Designing - 2 months (8 weeks) - K2500
UPDATE courses SET
    price = 2500.00,
    duration_weeks = 8,
    total_hours = 64.00
WHERE slug = 'graphic-designing';

-- Digital Content Creation - 3 weeks - K950
UPDATE courses SET
    price = 950.00,
    discount_price = NULL,
    duration_weeks = 3,
    total_hours = 24.00,
    title = 'Certificate in Digital & Content Creation',
    description = 'Create engaging multimedia content for education and business. Video editing, animation, interactive presentations, and e-learning materials.',
    short_description = 'Multimedia content creation'
WHERE slug = 'digital-content-creation';

-- Digital Marketing - 3 weeks - K950
UPDATE courses SET
    price = 950.00,
    duration_weeks = 3,
    total_hours = 24.00
WHERE slug = 'digital-marketing';

-- Business & Management (category_id: 6)
-- Entrepreneurship - 2 months 3 weeks (11 weeks) - K2500
UPDATE courses SET
    price = 2500.00,
    duration_weeks = 11,
    total_hours = 88.00
WHERE slug = 'entrepreneurship';

-- Project Management - K2500 (duration not specified, using 8 weeks)
UPDATE courses SET
    price = 2500.00,
    discount_price = NULL,
    duration_weeks = 8,
    total_hours = 64.00
WHERE slug = 'project-management';

-- Financial Technology (FinTech) - 3 weeks - K1200
UPDATE courses SET
    price = 1200.00,
    duration_weeks = 3,
    total_hours = 24.00
WHERE slug = 'financial-technology';

-- =====================================================
-- INSERT NEW COURSES
-- =====================================================

-- ICT Support & Hardware Repair - 8 weeks - K2500
INSERT INTO courses (title, slug, description, short_description, category_id, instructor_id, level, language, price, duration_weeks, total_hours, status, is_featured) VALUES
('Certificate in ICT Support & Hardware Repair', 'ict-support-hardware-repair', 'Comprehensive training in computer hardware, troubleshooting, maintenance, and repair. Learn to diagnose and fix common hardware issues, install operating systems, and provide technical support.', 'Computer hardware repair & support', 1, 1, 'Intermediate', 'English', 2500.00, 8, 64.00, 'published', 1);

-- Computer Studies - 3 months (12 weeks) - K3850
INSERT INTO courses (title, slug, description, short_description, category_id, instructor_id, level, language, price, duration_weeks, total_hours, status, is_featured) VALUES
('Certificate in Computer Studies', 'computer-studies', 'Comprehensive computer studies program covering fundamental concepts, applications, and practical skills. Ideal foundation for further ICT studies.', 'Foundation computer studies', 1, 1, 'Beginner', 'English', 3850.00, 12, 96.00, 'published', 1);

-- Computer Science General - 3 months (12 weeks) - K3000
INSERT INTO courses (title, slug, description, short_description, category_id, instructor_id, level, language, price, duration_weeks, total_hours, status, is_featured) VALUES
('Certificate in Computer Science General', 'computer-science-general', 'General computer science covering both software and hardware fundamentals. Learn programming basics, system architecture, and computing principles.', 'Software and hardware fundamentals', 1, 1, 'Intermediate', 'English', 3000.00, 12, 96.00, 'published', 0);

-- Information Technology (IT) - 2 months (8 weeks) - K2500
INSERT INTO courses (title, slug, description, short_description, category_id, instructor_id, level, language, price, duration_weeks, total_hours, status, is_featured) VALUES
('Certificate in Information Technology', 'information-technology', 'Comprehensive IT fundamentals covering networking, systems administration, IT security, and technical support. Prepare for entry-level IT positions.', 'IT fundamentals and systems', 1, 1, 'Intermediate', 'English', 2500.00, 8, 64.00, 'published', 1);

-- Computer & Business Handling - 4 weeks - K1200
INSERT INTO courses (title, slug, description, short_description, category_id, instructor_id, level, language, price, duration_weeks, total_hours, status, is_featured) VALUES
('Certificate in Computer & Business Handling', 'computer-business-handling', 'Essential computer skills for business professionals. Learn office applications, business communication, data entry, and basic accounting software.', 'Business computer skills', 1, 1, 'Beginner', 'English', 1200.00, 4, 32.00, 'published', 0);

-- C++ Programming - 3 months (12 weeks) - K3000
INSERT INTO courses (title, slug, description, short_description, category_id, instructor_id, level, language, price, duration_weeks, total_hours, status, is_featured) VALUES
('Certificate in C++ Programming', 'cpp-programming', 'Master C++ programming from basics to advanced concepts. Learn object-oriented programming, data structures, algorithms, and system programming.', 'Master C++ programming', 2, 2, 'Intermediate', 'English', 3000.00, 12, 96.00, 'published', 0);

-- Sales & Marketing - 2 months (8 weeks) - K2500
INSERT INTO courses (title, slug, description, short_description, category_id, instructor_id, level, language, price, duration_weeks, total_hours, status, is_featured) VALUES
('Certificate in Sales & Marketing', 'sales-marketing', 'Comprehensive sales and marketing training. Learn customer relationship management, sales techniques, market research, and marketing strategies.', 'Sales and marketing skills', 6, 5, 'Beginner', 'English', 2500.00, 8, 64.00, 'published', 0);

-- Monitoring & Evaluation - 2 months (8 weeks) - K2500
INSERT INTO courses (title, slug, description, short_description, category_id, instructor_id, level, language, price, duration_weeks, total_hours, status, is_featured) VALUES
('Certificate in Monitoring & Evaluation', 'monitoring-evaluation', 'Professional M&E training covering project monitoring frameworks, data collection, analysis, reporting, and evaluation methodologies.', 'M&E for projects and programs', 6, 5, 'Intermediate', 'English', 2500.00, 8, 64.00, 'published', 0);

-- Purchasing & Supply - 2 months (8 weeks) - K2500
INSERT INTO courses (title, slug, description, short_description, category_id, instructor_id, level, language, price, duration_weeks, total_hours, status, is_featured) VALUES
('Certificate in Purchasing & Supply', 'purchasing-supply', 'Professional procurement and supply chain management. Learn purchasing procedures, vendor management, inventory control, and logistics.', 'Procurement and supply chain', 6, 5, 'Intermediate', 'English', 2500.00, 8, 64.00, 'published', 0);

-- E-Commerce & Online Business - 3 weeks - K950
INSERT INTO courses (title, slug, description, short_description, category_id, instructor_id, level, language, price, duration_weeks, total_hours, status, is_featured) VALUES
('Certificate in E-Commerce & Online Business', 'ecommerce-online-business', 'Start and run an online business. Learn e-commerce platforms, online payment systems, digital storefronts, and online customer service.', 'Start your online business', 6, 5, 'Beginner', 'English', 950.00, 3, 24.00, 'published', 1);

-- Secretarial Ethics & Office Management / Typing Skills - 2 months (8 weeks) - K2500
INSERT INTO courses (title, slug, description, short_description, category_id, instructor_id, level, language, price, duration_weeks, total_hours, status, is_featured) VALUES
('Certificate in Secretarial & Office Management', 'secretarial-office-management', 'Professional secretarial training covering office management, business communication, typing skills, filing systems, and office ethics.', 'Office management & typing skills', 6, 5, 'Beginner', 'English', 2500.00, 8, 64.00, 'published', 0);

-- =====================================================
-- DELETE/ARCHIVE COURSES NOT IN NEW LIST
-- =====================================================

-- Archive Internet of Things (not in new list)
UPDATE courses SET status = 'archived' WHERE slug = 'internet-of-things';
