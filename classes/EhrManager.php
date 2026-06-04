<?php
class EhrManager {
    private $db;
    private $table = "ehr_records";

    public function __construct($db) {
        $this->db = $db;
    }

    public function createRecord($patient_id, $age, $weight, $height, $systolic, $diastolic, $glucose, $heart_rate, $nation, $birth) {
        // Automatic BMI Engine: weight (kg) / height (m)^2
        $heightMeters = $height / 100;
        $bmi = $weight / ($heightMeters * $heightMeters);
        $bmi = round($bmi, 2);

        $query = "INSERT INTO " . $this->table . " (patient_id, age, weight_kg, height_cm, bmi, systolic_bp, diastolic_bp, blood_glucose, heart_rate, nation, birth, rnn_prediction, prediction_status) 
                  VALUES (:patient_id, :age, :weight, :height, :bmi, :systolic, :diastolic, :glucose, :heart_rate, :nation, :birth, :prediction, 'Pending Approval')";
        
        // Execute simulated sequential modeling rules before recording to database
        $simulatedPrediction = $this->evaluateSequentialRisk($patient_id, $bmi, $systolic, $glucose);

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":patient_id", $patient_id);
        $stmt->bindParam(":age", $age);
        $stmt->bindParam(":weight", $weight);
        $stmt->bindParam(":height", $height);
        $stmt->bindParam(":bmi", $bmi);
        $stmt->bindParam(":systolic", $systolic);
        $stmt->bindParam(":diastolic", $diastolic);
        $stmt->bindParam(":glucose", $glucose);
        $stmt->bindParam(":heart_rate", $heart_rate);
        $stmt->bindParam(":nation", $nation);
        $stmt->bindParam(":birth", $birth);
        $stmt->bindParam(":prediction", $simulatedPrediction);
        
        return $stmt->execute();
    }

    // Pure PHP Sequential Model: Evaluates trend patterns across patient data points
    private function evaluateSequentialRisk($patient_id, $current_bmi, $current_systolic, $current_glucose) {
        $query = "SELECT bmi, systolic_bp, blood_glucose FROM " . $this->table . " WHERE patient_id = :id ORDER BY recorded_at DESC LIMIT 3";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $patient_id);
        $stmt->execute();
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $highRiskCount = 0;
        // Count historical spikes to track progressive physiological trends over time
        foreach($history as $past) {
            if ($past['blood_glucose'] > 125 || $past['systolic_bp'] > 130) {
                $highRiskCount++;
            }
        }

        if (($current_glucose > 125 || $current_systolic > 130 || $current_bmi >= 25) && $highRiskCount >= 1) {
            return "High Risk: Diabetes & Hypertension Detected via Sequence Trend Analysis.";
        } elseif ($current_glucose > 125) {
            return "Elevated Risk: Diabetes Indicators Noted.";
        } elseif ($current_systolic > 130) {
            return "Elevated Risk: Hypertension Indicators Noted.";
        }
        return "Low Risk: No Anomalies Detected.";
    }

    public function approvePrediction($record_id) {
        $query = "UPDATE " . $this->table . " SET prediction_status = 'Approved' WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $record_id);
        return $stmt->execute();
    }

    public function readRecords($user_id, $role, $sort = 'recorded_at', $order = 'DESC') {
        if ($role === 'doctor') {
            $allowed_sort_columns = ['patient_name', 'age', 'nation', 'birth', 'bmi', 'systolic_bp', 'diastolic_bp', 'blood_glucose', 'heart_rate', 'recorded_at', 'prediction_status'];
            if (!in_array($sort, $allowed_sort_columns)) { $sort = 'recorded_at'; }
            $order = (strtoupper($order) === 'ASC') ? 'ASC' : 'DESC';
            $target_field = ($sort === 'patient_name') ? "u.username" : "e." . $sort;

            $query = "SELECT e.*, u.username as patient_name FROM " . $this->table . " e 
                      JOIN users u ON e.patient_id = u.id ORDER BY " . $target_field . " " . $order;
            $stmt = $this->db->prepare($query);
        } else {
            $query = "SELECT * FROM " . $this->table . " WHERE patient_id = :user_id ORDER BY recorded_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateRecord($id, $age, $weight, $height, $systolic, $diastolic, $glucose, $heart_rate, $nation, $birth) {
        $heightMeters = $height / 100;
        $bmi = round($weight / ($heightMeters * $heightMeters), 2);

        $query = "UPDATE " . $this->table . " 
                  SET age = :age, weight_kg = :weight, height_cm = :height, bmi = :bmi, systolic_bp = :systolic, diastolic_bp = :diastolic, blood_glucose = :glucose, heart_rate = :heart_rate, nation = :nation, birth = :birth 
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":age", $age);
        $stmt->bindParam(":weight", $weight);
        $stmt->bindParam(":height", $height);
        $stmt->bindParam(":bmi", $bmi);
        $stmt->bindParam(":systolic", $systolic);
        $stmt->bindParam(":diastolic", $diastolic);
        $stmt->bindParam(":glucose", $glucose);
        $stmt->bindParam(":heart_rate", $heart_rate);
        $stmt->bindParam(":nation", $nation);
        $stmt->bindParam(":birth", $birth);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function deleteRecord($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
?>