<?php
require_once 'classes/Database.php';
require_once 'classes/Auth.php';
require_once 'classes/EhrManager.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

if (!$auth->isLoggedIn() || $auth->getRole() !== 'doctor') {
    die("Access Denied: Clinicians only.");
}

$ehrManager = new EhrManager($db);

if (!isset($_GET['id'])) { header("Location: index.php"); exit; }
$item = $ehrManager->readOne($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Process clinical metrics updates
    $ehrManager->updateRecord(
        $_GET['id'], $_POST['age'], $_POST['weight_kg'], $_POST['height_cm'], $_POST['systolic_bp'], 
        $_POST['diastolic_bp'], $_POST['blood_glucose'], $_POST['heart_rate'],
        $_POST['hba1c'], $_POST['cholesterol_mgdl'], $_POST['smoking_status'],
        $_POST['nation'], $_POST['birth']
    );
    
    // 2. Commit doctor note annotations and update validation state
    $ehrManager->processDoctorReview($_GET['id'], $_POST['prediction_status'], $_POST['doctor_notes']);
    
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Physician Evaluation Terminal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow border-0">
                    <div class="card-body p-5">
                        <h3 class="card-title text-danger mb-2">Physician Evaluation & Review Portal</h3>
                        <p class="text-muted mb-4">Patient Profile: <strong><?= htmlspecialchars($item['patient_name']) ?></strong> (Sex: <?= $item['patient_gender'] ?>)</p>
                        <hr>
                        <form method="POST" action="">
                            
                            <div class="row bg-light p-3 rounded mb-4 border">
                                <h5 class="text-dark mb-3">1. Update Clinical Metrics</h5>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label small">Age</label>
                                    <input type="number" name="age" class="form-control" value="<?= $item['age'] ?>" min="0" max="120" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label small">Nation</label>
                                    <input type="text" name="nation" class="form-control" value="<?= htmlspecialchars($item['nation']) ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label small">Birth Date</label>
                                    <input type="date" name="birth" class="form-control" value="<?= $item['birth'] ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small">Weight (kg)</label>
                                    <input type="number" step="0.1" name="weight_kg" class="form-control" value="<?= $item['weight_kg'] ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small">Height (cm)</label>
                                    <input type="number" step="0.1" name="height_cm" class="form-control" value="<?= $item['height_cm'] ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small">Systolic BP</label>
                                    <input type="number" name="systolic_bp" class="form-control" value="<?= $item['systolic_bp'] ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small">Diastolic BP</label>
                                    <input type="number" name="diastolic_bp" class="form-control" value="<?= $item['diastolic_bp'] ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label small">Glucose (mg/dL)</label>
                                    <input type="number" name="blood_glucose" class="form-control" value="<?= $item['blood_glucose'] ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label small">HbA1c (%)</label>
                                    <input type="number" step="0.1" name="hba1c" class="form-control" value="<?= $item['hba1c'] ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label small">Cholesterol (mg/dL)</label>
                                    <input type="number" name="cholesterol_mgdl" class="form-control" value="<?= $item['cholesterol_mgdl'] ?>" required>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <label class="form-label small">Smoking Lifestyle Status</label>
                                    <select name="smoking_status" class="form-select" required>
                                        <option value="Never" <?= $item['smoking_status']=='Never'?'selected':'' ?>>Never Smoked</option>
                                        <option value="Former" <?= $item['smoking_status']=='Former'?'selected':'' ?>>Former Smoker</option>
                                        <option value="Active" <?= $item['smoking_status']=='Active'?'selected':'' ?>>Active Smoker</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row bg-white p-3 rounded mb-4 border">
                                <h5 class="text-danger mb-3">2. Diagnostic Validation & Epikriz</h5>
                                <div class="col-12 mb-3">
                                    <label class="form-label small fw-bold">EHR Pipeline State Assignment</label>
                                    <select name="prediction_status" class="form-select border-danger" required>
                                        <option value="Pending Approval" <?= $item['prediction_status']=='Pending Approval'?'selected':'' ?>>Pending Approval (Keep Masked from Patient)</option>
                                        <option value="Approved" <?= $item['prediction_status']=='Approved'?'selected':'' ?>>Approved (Push Diagnostics & Notes to Patient Panel)</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-2">
                                    <label class="form-label small fw-bold">Clinician Progress Notes / Directives (Reçete)</label>
                                    <textarea name="doctor_notes" class="form-control" rows="4" placeholder="Type diagnostic confirmations, lifestyle instructions, or prescriptions here..." required><?= htmlspecialchars($item['doctor_notes'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary px-4">Back to Registry</a>
                                <button type="submit" class="btn btn-danger px-4">Commit Review & Sign Off</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>