-- Migration: 002_seed_data
-- Description: Initial seed data for roles, categories, and payment methods
-- Date: 2025-11-21

-- Insert roles
INSERT INTO `Roles` (`role_id`, `role_name`, `description`, `permissions`) VALUES
(1, 'Super Admin', 'Full system access and control', '{"all": true}'),
(2, 'Admin', 'Administrative access to manage system', '{"users": ["create", "read", "update", "delete"], "courses": ["create", "read", "update", "delete"], "reports": ["read"]}'),
(3, 'Instructor', 'Can create and manage courses', '{"courses": ["create", "read", "update"], "students": ["read"], "grades": ["create", "update"]}'),
(4, 'Student', 'Can enroll and access courses', '{"courses": ["read", "enroll"], "assignments": ["submit"], "quizzes": ["take"]}'),
(5, 'Content Creator', 'Can create course content', '{"courses": ["create", "read", "update"], "content": ["create", "update"]}')
ON DUPLICATE KEY UPDATE role_name=VALUES(role_name);

-- Insert course categories
INSERT INTO `Course_Categories` (`category_id`, `category_name`, `category_description`, `parent_category_id`, `icon_url`, `display_order`, `is_active`) VALUES
(1, 'Core ICT & Digital Skills', 'Fundamental computer and digital literacy courses covering essential office applications, digital tools, and basic ICT competencies', NULL, NULL, 1, 1),
(2, 'Programming & Software Development', 'Programming languages, software engineering practices, web and mobile application development courses', NULL, NULL, 2, 1),
(3, 'Data, Security & Networks', 'Data analysis, cybersecurity, database management, and network infrastructure courses', NULL, NULL, 3, 1),
(4, 'Emerging Technologies', 'Cutting-edge technology courses including AI, machine learning, and Internet of Things', NULL, NULL, 4, 1),
(5, 'Digital Media & Design', 'Creative and digital content courses covering graphic design, multimedia, and digital marketing', NULL, NULL, 5, 1),
(6, 'Business & Management', 'Business administration, entrepreneurship, project management, and professional development courses', NULL, NULL, 6, 1)
ON DUPLICATE KEY UPDATE category_name=VALUES(category_name);

-- Insert payment methods
INSERT INTO `Payment_Methods` (`payment_method_id`, `method_name`, `description`, `is_active`) VALUES
(1, 'Credit Card', 'Visa, Mastercard, American Express', 1),
(2, 'Mobile Money', 'MTN Mobile Money, Airtel Money', 1),
(3, 'Bank Transfer', 'Direct bank transfer', 1),
(4, 'PayPal', 'PayPal payment gateway', 1),
(5, 'Cash', 'Cash payment at office', 1)
ON DUPLICATE KEY UPDATE method_name=VALUES(method_name);
