<?php
class EhrManager {
    private $db;
    private $table = "ehr_records";

    public function __construct($db) {
        $this->db = $db;
    }

    public function createRecord($patient_id, $age, $weight, $height, $systolic, $diastolic, $glucose, $heart_rate, $hba1c, $cholesterol, $smoking, $nation, $birth) {
        $heightMeters = $height / 100;
        $bmi = round($weight / ($heightMeters * $heightMeters), 2);

        // Fetch patient biological sex context from session data
        $gender = $_SESSION['gender'] ?? 'Male';

        // Calculate custom time-series risk vectors
        $simulatedPrediction = $this->evaluateAdvancedSequentialRisk($patient_id, $bmi, $systolic, $glucose, $hba1c, $cholesterol, $smoking, $gender);

        $query = "INSERT INTO " . $this->table . " (patient_id, age, weight_kg, height_cm, bmi, systolic_bp, diastolic_bp, blood_glucose, heart_rate, hba1c, cholesterol_mgdl, smoking_status, nation, birth, rnn_prediction, prediction_status) 
                  VALUES (:patient_id, :age, :weight, :height, :bmi, :systolic, :diastolic, :glucose, :heart_rate, :hba1c, :cholesterol, :smoking, :nation, :birth, :prediction, 'Pending Approval')";
        
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
        $stmt->bindParam(":hba1c", $hba1c);
        $stmt->bindParam(":cholesterol", $cholesterol);
        $stmt->bindParam(":smoking", $smoking);
        $stmt->bindParam(":nation", $nation);
        $stmt->bindParam(":birth", $birth);
        $stmt->bindParam(":prediction", $simulatedPrediction);
        
        return $stmt->execute();
    }

    private function evaluateAdvancedSequentialRisk($patient_id, $bmi, $systolic, $glucose, $hba1c, $cholesterol, $smoking, $gender) {
        // Query historical time-series layers to check trend directions
        $query = "SELECT systolic_bp, blood_glucose, hba1c FROM " . $this->table . " WHERE patient_id = :id ORDER BY recorded_at DESC LIMIT 2";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $patient_id);
        $stmt->execute();
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $riskScore = 0;
        
        // Base parameter weights
        if ($glucose > 125 || $hba1c >= 6.5) { $riskScore += 3; }
        if ($systolic > 130 || $cholesterol > 200) { $riskScore += 3; }
        if ($bmi >= 25.0) { $riskScore += 1; }
        if ($smoking === 'Active') { $riskScore += 2; }
        if ($gender === 'Female' && $systolic > 130) { $riskScore += 1; } // Sex-specific cardiovascular weighting

        // Longitudinal evaluation pass (Simulating Hidden State transitions of an RNN)
        foreach($history as $past) {
            if ($past['blood_glucose'] > 125 || $past['systolic_bp'] > 130) {
                $riskScore += 1.5; 
            }
        }

        if ($riskScore >= 6) {
            return "High Risk: Chronic Diabetes & Hypertension Sequence Pattern Confirmed.";
        } elseif ($riskScore >= 3) {
            return "Moderate Risk: Elevated Metabolic / Cardiovascular Activity Detected.";
        }
        return "Low Risk: Normal Physiological Continuity Verified.";
    }

    public function processDoctorReview($record_id, $status, $notes) {
        $query = "UPDATE " . $this->table . " SET prediction_status = :status, doctor_notes = :notes WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":notes", $notes);
        $stmt->bindParam(":id", $record_id);
        return $stmt->execute();
    }

    public function readRecords($user_id, $role, $sort = 'recorded_at', $order = 'DESC') {
        if ($role === 'doctor') {
            $allowed_sort_columns = ['patient_name', 'age', 'nation', 'birth', 'bmi', 'systolic_bp', 'blood_glucose', 'recorded_at', 'prediction_status'];
            if (!in_array($sort, $allowed_sort_columns)) { $sort = 'recorded_at'; }
            $order = (strtoupper($order) === 'ASC') ? 'ASC' : 'DESC';
            $target_field = ($sort === 'patient_name') ? "u.username" : "e." . $sort;

            $query = "SELECT e.*, u.username as patient_name, u.gender as patient_gender FROM " . $this->table . " e 
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
        $query = "SELECT e.*, u.username as patient_name, u.gender as patient_gender FROM " . $this->table . " e JOIN users u ON e.patient_id = u.id WHERE e.id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateRecord($id, $age, $weight, $height, $systolic, $diastolic, $glucose, $heart_rate, $hba1c, $cholesterol, $smoking, $nation, $birth) {
        $heightMeters = $height / 100;
        $bmi = round($weight / ($heightMeters * $heightMeters), 2);

        $query = "UPDATE " . $this->table . " 
                  SET age = :age, weight_kg = :weight, height_cm = :height, bmi = :bmi, systolic_bp = :systolic, diastolic_bp = :diastolic, blood_glucose = :glucose, heart_rate = :heart_rate, hba1c = :hba1c, cholesterol_mgdl = :cholesterol, smoking_status = :smoking, nation = :nation, birth = :birth 
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
        $stmt->bindParam(":hba1c", $hba1c);
        $stmt->bindParam(":cholesterol", $cholesterol);
        $stmt->bindParam(":smoking", $smoking);
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