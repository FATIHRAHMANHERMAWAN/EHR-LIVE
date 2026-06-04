USE ehr_db;

-- Drop the old table to avoid constraint conflicts
DROP TABLE IF EXISTS ehr_records;

-- Create the clean table using 'birth'
CREATE TABLE IF NOT EXISTS ehr_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    age INT NOT NULL,
    bmi DECIMAL(4,2) NOT NULL,
    systolic_bp INT NOT NULL,
    diastolic_bp INT NOT NULL,
    blood_glucose INT NOT NULL,
    heart_rate INT NOT NULL,
    nation VARCHAR(100) NOT NULL,
    birth DATE NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE
);