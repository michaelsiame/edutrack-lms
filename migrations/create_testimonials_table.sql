-- Create testimonials table for student success stories
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(255) NOT NULL,
    student_photo VARCHAR(255),
    course_taken VARCHAR(255) NOT NULL,
    graduation_year INT,
    current_job_title VARCHAR(255),
    company VARCHAR(255),
    testimonial_text TEXT NOT NULL,
    rating INT DEFAULT 5,
    is_featured BOOLEAN DEFAULT FALSE,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    submitted_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_featured (is_featured),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample testimonials
INSERT INTO testimonials (student_name, course_taken, graduation_year, current_job_title, company, testimonial_text, rating, is_featured, status) VALUES
('Mwamba Chanda', 'Certificate in Cybersecurity', 2024, 'IT Security Analyst', 'Zambia National Commercial Bank', 'The cybersecurity program at Edutrack completely transformed my career. The hands-on training and TEVETA certification gave me the confidence to apply for positions at major banks. Within 2 months of graduating, I secured my dream job!', 5, TRUE, 'approved'),
('Grace Mulenga', 'Diploma in Web Development', 2023, 'Full Stack Developer', 'BongoHive', 'I started with zero coding knowledge and now I''m building web applications for one of Zambia''s top tech hubs. The instructors at Edutrack are patient and really know how to teach complex concepts simply.', 5, TRUE, 'approved'),
('Brian Phiri', 'Microsoft Office Specialist', 2024, 'Administrative Assistant', 'Ministry of Education', 'The flexible evening classes allowed me to study while working. The skills I gained in Excel and data management helped me get promoted within 6 months. Highly recommend Edutrack!', 5, TRUE, 'approved'),
('Precious Nkonde', 'Certificate in Digital Marketing', 2023, 'Marketing Coordinator', 'MTN Zambia', 'Edutrack''s digital marketing course covered everything from SEO to social media advertising. The practical projects helped me build a portfolio that impressed my employers.', 5, FALSE, 'approved');
