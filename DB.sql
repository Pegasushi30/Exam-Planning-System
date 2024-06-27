-- Create the database
CREATE DATABASE IF NOT EXISTS exam_planning_system;
USE exam_planning_system;

-- Create faculties table
CREATE TABLE IF NOT EXISTS faculties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- Create departments table
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    faculty_id INT,
    FOREIGN KEY (faculty_id) REFERENCES faculties(id)
);

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('assistant', 'secretary', 'head_of_department', 'head_of_secretary', 'dean') NOT NULL,
    department_id INT,
    faculty_id INT,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (faculty_id) REFERENCES faculties(id)
);

-- Create courses table
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    department_id INT,
    faculty_id INT,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (faculty_id) REFERENCES faculties(id)
);

-- Create course_schedule table
CREATE TABLE IF NOT EXISTS course_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT,
    day_of_week VARCHAR(10) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

-- Create exams table
CREATE TABLE IF NOT EXISTS exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT,
    name VARCHAR(100) NOT NULL,
    exam_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    num_assistants INT NOT NULL,
    num_classes INT NOT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

-- Create assistants table
CREATE TABLE IF NOT EXISTS assistants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    department_id INT,
    faculty_id INT,
    score INT DEFAULT 0,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (faculty_id) REFERENCES faculties(id)
);

-- Create exam_assignments table
CREATE TABLE IF NOT EXISTS exam_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT,
    assistant_id INT,
    FOREIGN KEY (exam_id) REFERENCES exams(id),
    FOREIGN KEY (assistant_id) REFERENCES assistants(id)
);

-- Create assistant_courses table
CREATE TABLE IF NOT EXISTS assistant_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assistant_id INT,
    course_id INT,
    FOREIGN KEY (assistant_id) REFERENCES assistants(id),
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

-- Create weekly_plan table
CREATE TABLE IF NOT EXISTS weekly_plan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assistant_id INT,
    course_name VARCHAR(100),
    exam_name VARCHAR(100),
    exam_date DATE,
    start_time TIME,
    end_time TIME,
    FOREIGN KEY (assistant_id) REFERENCES assistants(id)
);

-- Insert initial data

-- Faculties
INSERT INTO faculties (name) VALUES ('Engineering Faculty');

-- Departments
INSERT INTO departments (name, faculty_id) VALUES 
('Computer Engineering', 1), 
('Electrical Engineering', 1), 
('Mechanical Engineering', 1);

-- Users
INSERT INTO users (username, password, role, department_id, faculty_id) VALUES
('gulsah', 'password123', 'assistant', 1, 1),
('mali', 'password123', 'assistant', 1, 1),
('kerem', 'password123', 'assistant', 1, 1),
('burcu', 'password123', 'assistant', 1, 1),
('mert', 'password123', 'assistant', 2, 1),
('aydin', 'password123', 'assistant', 3, 1),
('perihan', 'password123', 'secretary', 1, 1),
('serpil', 'password123', 'secretary', 2, 1),
('nuran', 'password123', 'secretary', 3, 1),
('yasemin', 'password123', 'head_of_secretary', NULL, 1),
('cem', 'password123', 'dean', NULL, 1),
('gurhan', 'password123', 'head_of_department', 1, 1);

-- Courses
INSERT INTO courses (name, department_id, faculty_id) VALUES 
('CSE101', 1, 1), 
('CSE102', 1, 1), 
('ELE101', 2, 1),
('ELE102', 2, 1),
('MEC101', 3, 1),
('MEC102', 3, 1),
('ES224', NULL, 1),
('ES272', NULL, 1),
('CSE232', 1, 1);

-- Course Schedule
INSERT INTO course_schedule (course_id, day_of_week, start_time, end_time) VALUES
((SELECT id FROM courses WHERE name = 'CSE101'), 'Monday', '09:00:00', '11:00:00'),
((SELECT id FROM courses WHERE name = 'CSE102'), 'Tuesday', '11:00:00', '13:00:00'),
((SELECT id FROM courses WHERE name = 'ELE101'), 'Wednesday', '10:00:00', '12:00:00'),
((SELECT id FROM courses WHERE name = 'ELE102'), 'Thursday', '14:00:00', '16:00:00'),
((SELECT id FROM courses WHERE name = 'MEC101'), 'Friday', '08:00:00', '10:00:00'),
((SELECT id FROM courses WHERE name = 'MEC102'), 'Friday', '10:00:00', '12:00:00'),
((SELECT id FROM courses WHERE name = 'ES224'), 'Monday', '08:00:00', '10:00:00'),
((SELECT id FROM courses WHERE name = 'ES272'), 'Tuesday', '08:00:00', '10:00:00'),
((SELECT id FROM courses WHERE name = 'CSE232'), 'Wednesday', '11:00:00', '13:00:00');

-- Assistants
INSERT INTO assistants (name, department_id, faculty_id, score) VALUES
('Gülşah Gökhan Gökçek', 1, 1, 0),
('M. Ali Bayram', 1, 1, 0),
('O. Kerem Perente', 1, 1, 0),
('Burcu Selçuk', 1, 1, 0),
('Mert Korkut', 2, 1, 0),
('Aydın Akaltan', 3, 1, 0);