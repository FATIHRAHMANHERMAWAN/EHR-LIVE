-- 1. Users Table (Extended with Biological Sex for baseline AI weights)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('patient', 'doctor') NOT NULL DEFAULT 'patient',
    gender ENUM('Male', 'Female') NOT NULL DEFAULT 'Male',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Comprehensive Clinical EHR Table (Fully Optimized with MAP & XAI Metrics)
CREATE TABLE IF NOT EXISTS ehr_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    age INT NOT NULL,
    weight_kg DECIMAL(5,2) NOT NULL,
    height_cm DECIMAL(5,2) NOT NULL,
    bmi DECIMAL(4,2) NOT NULL,
    systolic_bp INT NOT NULL,
    diastolic_bp INT NOT NULL,
    map_mmhg INT NOT NULL,                                        -- Bio-Mathematical Metric
    blood_glucose INT NOT NULL,
    heart_rate INT NOT NULL,
    hba1c DECIMAL(3,1) NOT NULL,                                  -- 3-Month Diabetes Marker
    cholesterol_mgdl INT NOT NULL,                                -- Cardiovascular Triad Parameter
    smoking_status ENUM('Never', 'Former', 'Active') NOT NULL DEFAULT 'Never', -- Lifestyle Co-factor
    nation VARCHAR(100) NOT NULL,
    birth DATE NOT NULL,
    rnn_prediction VARCHAR(255) DEFAULT 'No Risk Detected',
    glucose_impact_pct INT DEFAULT 0,                             -- Explainable AI (XAI) Weight
    cardio_impact_pct INT DEFAULT 0,                              -- Explainable AI (XAI) Weight
    lifestyle_impact_pct INT DEFAULT 0,                           -- Explainable AI (XAI) Weight
    prediction_status ENUM('Pending Approval', 'Approved') DEFAULT 'Pending Approval',
    doctor_notes TEXT DEFAULT NULL,                               -- Physician Progress Directives
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Seed Doctor Account (Username: doktorbey, Password: doctor123)
INSERT INTO users (username, password, role, gender) 
VALUES ('doktorbey', '$2y$10$vE6wX6pGjY8GEX9YUXA2UeaP9O5U0eSmG5C6jFzeL7gP9DqQp.YtG', 'doctor', 'Male')
ON DUPLICATE KEY UPDATE id=id;