CREATE DATABASE IF NOT EXISTS ehr_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE ehr_db;

-- 1. Users Table (Role-based: Patient or Doctor)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('patient', 'doctor') NOT NULL DEFAULT 'patient',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. EHR Records Table (Sequential Time-Series for RNN/Transformers input)
CREATE TABLE IF NOT EXISTS ehr_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    age INT NOT NULL,
    bmi DECIMAL(4,2) NOT NULL,
    systolic_bp INT NOT NULL,      -- Systolic Blood Pressure
    diastolic_bp INT NOT NULL,     -- Diastolic Blood Pressure
    blood_glucose INT NOT NULL,    -- Fasting Blood Glucose (mg/dL)
    heart_rate INT NOT NULL,       -- Heart Rate (bpm)
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Seed an initial Doctor Account for testing (Username: dr_alex, Password: doctor123)
INSERT INTO users (username, password, role) 
VALUES ('dr_alex', '$2y$10$vE6wX6pGjY8GEX9YUXA2UeaP9O5U0eSmG5C6jFzeL7gP9DqQp.YtG', 'doctor')
ON DUPLICATE KEY UPDATE id=id;