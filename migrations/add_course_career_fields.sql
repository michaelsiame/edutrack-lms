-- Add career outcomes and key skills fields to courses table
ALTER TABLE courses 
ADD COLUMN career_outcomes JSON NULL COMMENT 'Array of job titles for graduates',
ADD COLUMN key_skills JSON NULL COMMENT 'Array of key skills gained',
ADD COLUMN avg_salary VARCHAR(50) DEFAULT 'ZMW 6,500' COMMENT 'Average starting salary',
ADD COLUMN placement_rate VARCHAR(10) DEFAULT '85%' COMMENT 'Job placement rate';

-- Insert sample career data for existing courses (if any)
-- Example data structure for reference:
-- career_outcomes: ["IT Support Specialist", "Network Administrator", "Systems Analyst"]
-- key_skills: ["Network Configuration", "Troubleshooting", "System Administration"]
