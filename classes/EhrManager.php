<?php
class EhrManager {
    private $db;
    private $table = "ehr_records";

    public function __construct($db) {
        $this->db = $db;
    }

    public function createRecord($patient_id, $age, $bmi, $systolic, $diastolic, $glucose, $heart_rate, $nation, $birth) {
        $query = "INSERT INTO " . $this->table . " (patient_id, age, bmi, systolic_bp, diastolic_bp, blood_glucose, heart_rate, nation, birth) 
                  VALUES (:patient_id, :age, :bmi, :systolic, :diastolic, :glucose, :heart_rate, :nation, :birth)";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(":patient_id", $patient_id);
        $stmt->bindParam(":age", $age);
        $stmt->bindParam(":bmi", $bmi);
        $stmt->bindParam(":systolic", $systolic);
        $stmt->bindParam(":diastolic", $diastolic);
        $stmt->bindParam(":glucose", $glucose);
        $stmt->bindParam(":heart_rate", $heart_rate);
        $stmt->bindParam(":nation", $nation);
        $stmt->bindParam(":birth", $birth);
        return $stmt->execute();
    }

    // Extended with strict whitelist sorting variables
    public function readRecords($user_id, $role, $sort = 'recorded_at', $order = 'DESC') {
        if ($role === 'doctor') {
            // Security Whitelist: Prevents SQL Injection through unquoted column structures
            $allowed_sort_columns = ['patient_name', 'age', 'nation', 'birth', 'bmi', 'systolic_bp', 'diastolic_bp', 'blood_glucose', 'heart_rate', 'recorded_at'];
            if (!in_array($sort, $allowed_sort_columns)) {
                $sort = 'recorded_at';
            }

            // Sanitize direction keywords
            $order = (strtoupper($order) === 'ASC') ? 'ASC' : 'DESC';

            // Resolve ambiguous column names or virtual joined aliases
            $target_field = ($sort === 'patient_name') ? "u.username" : "e." . $sort;

            $query = "SELECT e.*, u.username as patient_name FROM " . $this->table . " e 
                      JOIN users u ON e.patient_id = u.id 
                      ORDER BY " . $target_field . " " . $order;
            
            $stmt = $this->db->prepare($query);
        } else {
            // Patients see their own records chronologically
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

    public function updateRecord($id, $age, $bmi, $systolic, $diastolic, $glucose, $heart_rate, $nation, $birth) {
        $query = "UPDATE " . $this->table . " 
                  SET age = :age, bmi = :bmi, systolic_bp = :systolic, diastolic_bp = :diastolic, blood_glucose = :glucose, heart_rate = :heart_rate, nation = :nation, birth = :birth 
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":age", $age);
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