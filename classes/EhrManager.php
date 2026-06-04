<?php
class EhrManager {
    private $db;
    private $table = "ehr_records";

    public function __construct($db) {
        $this->db = $db;
    }

    public function createRecord($patient_id, $age, $weight, $height, $systolic, $diastolic, $glucose, $heart_rate, $hba1c, $cholesterol, $smoking, $nation, $birth) {
        // 1. BMI Hesaplama
        $heightMeters = $height / 100;
        $bmi = round($weight / ($heightMeters * $heightMeters), 2);

        // 2. Dinamik Klinik MAP Hesaplama
        $map = round(($systolic + (2 * $diastolic)) / 3);

        // 3. Yapay Zeka Risk ve XAI (Açıklanabilir AI) Katkı Payı Hesaplama
        $xai = $this->calculateXAIWeights($bmi, $systolic, $map, $glucose, $hba1c, $cholesterol, $smoking);

        $query = "INSERT INTO " . $this->table . " (patient_id, age, weight_kg, height_cm, bmi, systolic_bp, diastolic_bp, map_mmhg, blood_glucose, heart_rate, hba1c, cholesterol_mgdl, smoking_status, nation, birth, rnn_prediction, glucose_impact_pct, cardio_impact_pct, lifestyle_impact_pct, prediction_status) 
                  VALUES (:patient_id, :age, :weight, :height, :bmi, :systolic, :diastolic, :map, :glucose, :heart_rate, :hba1c, :cholesterol, :smoking, :nation, :birth, :prediction, :g_pct, :c_pct, :l_pct, 'Pending Approval')";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":patient_id", $patient_id);
        $stmt->bindParam(":age", $age);
        $stmt->bindParam(":weight", $weight);
        $stmt->bindParam(":height", $height);
        $stmt->bindParam(":bmi", $bmi);
        $stmt->bindParam(":systolic", $systolic);
        $stmt->bindParam(":diastolic", $diastolic);
        $stmt->bindParam(":map", $map);
        $stmt->bindParam(":glucose", $glucose);
        $stmt->bindParam(":heart_rate", $heart_rate);
        $stmt->bindParam(":hba1c", $hba1c);
        $stmt->bindParam(":cholesterol", $cholesterol);
        $stmt->bindParam(":smoking", $smoking);
        $stmt->bindParam(":nation", $nation);
        $stmt->bindParam(":birth", $birth);
        $stmt->bindParam(":prediction", $xai['prediction']);
        $stmt->bindParam(":g_pct", $xai['g_pct']);
        $stmt->bindParam(":c_pct", $xai['c_pct']);
        $stmt->bindParam(":l_pct", $xai['l_pct']);
        
        return $stmt->execute();
    }

    private function calculateXAIWeights($bmi, $systolic, $map, $glucose, $hba1c, $cholesterol, $smoking) {
        $g_weight = 0; $c_weight = 0; $l_weight = 0;

        // Risk Faktörleri Ağırlık Dağılımı
        if ($glucose > 125 || $hba1c >= 6.5) { $g_weight += 45; }
        if ($systolic > 130 || $map > 100 || $cholesterol > 200) { $c_weight += 45; }
        if ($bmi >= 25.0) { $l_weight += 15; }
        if ($smoking === 'Active') { $l_weight += 20; }

        $total = $g_weight + $c_weight + $l_weight;
        
        if($total == 0) {
            return ['prediction' => 'Low Risk', 'g_pct' => 0, 'c_pct' => 0, 'l_pct' => 0];
        }

        // Değerleri Göreceli Yüzdelere Normalize Etme (SHAP/LIME mantığı)
        $g_pct = round(($g_weight / $total) * 100);
        $c_pct = round(($c_weight / $total) * 100);
        $l_pct = round(($l_weight / $total) * 100);

        $prediction = ($total >= 50) ? "High Risk Pattern" : "Moderate Risk Pattern";

        return ['prediction' => $prediction, 'g_pct' => $g_pct, 'c_pct' => $c_pct, 'l_pct' => $l_pct];
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
        $map = round(($systolic + (2 * $diastolic)) / 3);

        $query = "UPDATE " . $this->table . " 
                  SET age = :age, weight_kg = :weight, height_cm = :height, bmi = :bmi, systolic_bp = :systolic, diastolic_bp = :diastolic, map_mmhg = :map, blood_glucose = :glucose, heart_rate = :heart_rate, hba1c = :hba1c, cholesterol_mgdl = :cholesterol, smoking_status = :smoking, nation = :nation, birth = :birth 
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":age", $age);
        $stmt->bindParam(":weight", $weight);
        $stmt->bindParam(":height", $height);
        $stmt->bindParam(":bmi", $bmi);
        $stmt->bindParam(":systolic", $systolic);
        $stmt->bindParam(":diastolic", $diastolic);
        $stmt->bindParam(":map", $map);
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