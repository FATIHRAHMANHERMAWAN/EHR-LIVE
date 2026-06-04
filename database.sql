CREATE DATABASE IF NOT EXISTS ehr_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE ehr_db;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('patient', 'doctor') NOT NULL DEFAULT 'patient',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Upgraded EHR Table for Automatic BMI Calculation & Sequential RNN Tracking
CREATE TABLE IF NOT EXISTS ehr_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    age INT NOT NULL,
    weight_kg DECIMAL(5,2) NOT NULL,
    height_cm DECIMAL(5,2) NOT NULL,
    bmi DECIMAL(4,2) NOT NULL,
    systolic_bp INT NOT NULL,
    diastolic_bp INT NOT NULL,
    blood_glucose INT NOT NULL,
    heart_rate INT NOT NULL,
    nation VARCHAR(100) NOT NULL,
    birth DATE NOT NULL,
    rnn_prediction VARCHAR(255) DEFAULT 'No Risk Detected',
    prediction_status ENUM('None', 'Pending Approval', 'Approved') DEFAULT 'None',
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Seed Doctor Account (Username: doktorbey, Password: doctor123)
INSERT INTO users (username, password, role) 
VALUES ('doktorbey', '$2y$10$vE6wX6pGjY8GEX9YUXA2UeaP9O5U0eSmG5C6jFzeL7gP9DqQp.YtG', 'doctor')
ON DUPLICATE KEY UPDATE id=id;