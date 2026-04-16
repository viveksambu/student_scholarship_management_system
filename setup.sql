CREATE DATABASE IF NOT EXISTS scholarship_db;
USE scholarship_db;

-- Drop old table if exists
DROP TABLE IF EXISTS scholarships;

-- Create scholarships table
CREATE TABLE scholarships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    provider VARCHAR(255) NOT NULL,
    stream VARCHAR(100) NOT NULL,
    amount VARCHAR(100) NOT NULL,
    deadline DATE NOT NULL,
    description TEXT,
    eligibility TEXT,
    apply_url VARCHAR(500),
    status VARCHAR(50) DEFAULT 'Active'
);

-- Create site statistics table
CREATE TABLE IF NOT EXISTS site_stats (
    id INT PRIMARY KEY,
    total_students INT NOT NULL
);

-- Create suggested scholarships table
CREATE TABLE IF NOT EXISTS suggested_scholarships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    provider VARCHAR(255) NOT NULL,
    amount VARCHAR(100) NOT NULL,
    stream VARCHAR(100) NOT NULL,
    deadline DATE NOT NULL,
    apply_url VARCHAR(500) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create ads table
CREATE TABLE IF NOT EXISTS ads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    target_url VARCHAR(500) NOT NULL,
    banner_image VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert initial stats
INSERT INTO site_stats (id, total_students)
VALUES (1, 15420)
ON DUPLICATE KEY UPDATE total_students = total_students;

-- Insert scholarships data
INSERT INTO scholarships
(name, provider, stream, amount, deadline, description, eligibility, apply_url, status)
VALUES
(
    'Global Engineering Innovators Scholarship',
    'TechCorp Foundation',
    'Engineering',
    'Up to INR 50,000',
    '2026-08-15',
    'Awarded to outstanding engineering students demonstrating innovation.',
    'Open to engineering students with excellent academic performance.',
    'https://www.buddy4study.com',
    'Active'
),
(
    'Women in Tech Scholarship',
    'Global Tech Initiative',
    'Engineering',
    'Up to INR 30,000',
    '2026-09-01',
    'Supporting female students pursuing degrees in technology and engineering.',
    'Female engineering students with good academic record.',
    'https://www.buddy4study.com',
    'Active'
),
(
    'SBI Youth for India Fellowship',
    'SBI Foundation',
    'All',
    'Up to INR 90,000',
    '2026-07-30',
    'For medical students showing exceptional promise.',
    ' SBI Youth for India Fellowship is an opportunity offered by the State Bank of India (SBI) Foundation in partnership with various reputed NGOs for bachelor’s degree holders between the age group of 21 to 32 years. The fellowship empowers graduates to tackle pressing rural development challenges by living and working alongside communities, promoting a transformative leadership journey. The selected fellows will receive a monthly stipend of ₹16,000, a readjustment allowance of ₹90,000 and other benefits.',
    'https://youthforindia.org/',
    'Active'
),
(
    'Reliance Foundation Undergraduate Scholarships',
    'Reliance Foundation',
    'All',
    'Up to INR 2,00,000',
    '2026-12-31',
    'Merit & Means scholarship for first-year undergraduate students in India.',
    'Student must be an Indian citizen, minimum 60% in class 12, first year full-time UG student, family income below INR 15 Lakhs, aptitude test compulsory.',
    'https://scholarships.reliancefoundation.org/UG_Scholarship.aspx',
    'Active'
);