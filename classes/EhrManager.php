<?php
class EhrManager {
    private $db;
    private $table = "ehr_records";

    public function __construct($db) {
        $this->db = $db;
    }

    public function createRecord($patient_id, $age, $bmi, $systolic, $diastolic, $glucose, $heart_rate) {
        $query = "INSERT INTO " . $this->table . " (patient_id, age, bmi, systolic_bp, diastolic_bp, blood_glucose, heart_rate) 
                  VALUES (:patient_id, :age, :bmi, :systolic, :diastolic, :glucose, :heart_rate)";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(":patient_id", $patient_id);
        $stmt->bindParam(":age", $age);
        $stmt->bindParam(":bmi", $bmi);
        $stmt->bindParam(":systolic", $systolic);
        $stmt->bindParam(":diastolic", $diastolic);
        $stmt->bindParam(":glucose", $glucose);
        $stmt->bindParam(":heart_rate", $heart_rate);
        return $stmt->execute();
    }

    public function readRecords($user_id, $role) {
        if ($role === 'doctor') {
            $query = "SELECT e.*, u.username as patient_name FROM " . $this->table . " e 
                      JOIN users u ON e.patient_id = u.id ORDER BY e.recorded_at DESC";
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

    public function updateRecord($id, $age, $bmi, $systolic, $diastolic, $glucose, $heart_rate) {
        $query = "UPDATE " . $this->table . " 
                  SET age = :age, bmi = :bmi, systolic_bp = :systolic, diastolic_bp = :diastolic, blood_glucose = :glucose, heart_rate = :heart_rate 
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":age", $age);
        $stmt->bindParam(":bmi", $bmi);
        $stmt->bindParam(":systolic", $systolic);
        $stmt->bindParam(":diastolic", $diastolic);
        $stmt->bindParam(":glucose", $glucose);
        $stmt->bindParam(":heart_rate", $heart_rate);
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