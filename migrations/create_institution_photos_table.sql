-- Create institution_photos table for campus/facilities gallery
CREATE TABLE IF NOT EXISTS institution_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category ENUM('campus', 'classroom', 'lab', 'event', 'faculty', 'student_life') DEFAULT 'campus',
    image_path VARCHAR(255) NOT NULL,
    is_featured BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_category (category),
    INDEX idx_featured (is_featured),
    INDEX idx_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create hero_slides table for homepage carousel
CREATE TABLE IF NOT EXISTS hero_slides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subtitle VARCHAR(500),
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    cta_text VARCHAR(100) DEFAULT 'Get Started',
    cta_link VARCHAR(255) DEFAULT 'courses.php',
    secondary_cta_text VARCHAR(100),
    secondary_cta_link VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_active (is_active),
    INDEX idx_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample hero slides (admin can replace images later)
INSERT INTO hero_slides (title, subtitle, description, image_path, cta_text, cta_link, secondary_cta_text, secondary_cta_link, is_active, display_order, created_by) VALUES
('Launch Your Tech Career', 'With Industry-Recognized Skills', 'Join 5,000+ Zambians who transformed their lives through TEVETA-certified programs in Cybersecurity, Web Development, and Digital Marketing.', 'hero-slide-1.jpg', 'Explore Courses', 'courses.php', 'Visit Campus', 'campus.php', TRUE, 1, 1),
('State-of-the-Art Computer Labs', 'Learn on Modern Equipment', 'Our facilities feature the latest hardware and software to ensure you gain practical experience with industry-standard tools.', 'hero-slide-2.jpg', 'Take a Tour', 'campus.php', 'View Programs', 'courses.php', TRUE, 2, 1),
('Your Success is Our Mission', '85% Job Placement Rate', 'Our graduates work at top companies like MTN, Airtel, and Zambia National Commercial Bank. Start your journey to a rewarding tech career today.', 'hero-slide-3.jpg', 'Apply Now', 'register.php', 'Contact Us', 'contact.php', TRUE, 3, 1);

-- Insert sample institution photos
INSERT INTO institution_photos (title, description, category, image_path, is_featured, display_order, uploaded_by) VALUES
('Main Campus Building', 'The welcoming entrance to Edutrack Computer Training College in Kalomo', 'campus', 'campus-main.jpg', TRUE, 1, 1),
('Computer Lab 1', 'Our primary computer lab with 30 workstations for hands-on learning', 'lab', 'lab-1.jpg', TRUE, 2, 1),
('Classroom Setting', 'Interactive learning environment with projector and modern teaching aids', 'classroom', 'classroom-1.jpg', FALSE, 3, 1),
('Student Workshop', 'Students participating in a practical cybersecurity workshop', 'event', 'event-workshop.jpg', TRUE, 4, 1),
('Graduation Ceremony', 'Celebrating our 2024 graduates and their achievements', 'event', 'graduation-2024.jpg', TRUE, 5, 1),
('Library & Study Area', 'Quiet space for students to study and access digital resources', 'campus', 'library.jpg', FALSE, 6, 1);
