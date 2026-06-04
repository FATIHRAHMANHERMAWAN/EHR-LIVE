<?php
require_once 'classes/Database.php';
require_once 'classes/Auth.php';
require_once 'classes/EhrManager.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

if (!$auth->isLoggedIn()) { header("Location: login.php"); exit; }

$ehrManager = new EhrManager($db);
$userId = $_SESSION['user_id'];
$userRole = $auth->getRole();

$currentSort = $_GET['sort'] ?? 'recorded_at';
$currentOrder = $_GET['order'] ?? 'DESC';
$toggleOrder = (strtoupper($currentOrder) === 'ASC') ? 'DESC' : 'ASC';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_ehr']) && $userRole === 'patient') {
    $age = (int)$_POST['age'];
    if($age >= 0 && $age <= 120) {
        $ehrManager->createRecord(
            $userId, $age, $_POST['weight_kg'], $_POST['height_cm'], $_POST['systolic_bp'], 
            $_POST['diastolic_bp'], $_POST['blood_glucose'], $_POST['heart_rate'],
            $_POST['hba1c'], $_POST['cholesterol_mgdl'], $_POST['smoking_status'],
            $_POST['nation'], $_POST['birth']
        );
        header("Location: index.php");
        exit;
    }
}

if (isset($_GET['delete_id']) && $userRole === 'doctor') {
    $ehrManager->deleteRecord($_GET['delete_id']);
    header("Location: index.php");
    exit;
}

$records = $ehrManager->readRecords($userId, $userRole, $currentSort, $currentOrder);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EHR Medical Intelligence Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark <?= $userRole === 'doctor' ? 'bg-danger' : 'bg-primary' ?> mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">EHR Core Intelligence System</a>
            <div class="navbar-text ms-auto text-white me-3">
                User: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> 
                <span class="badge bg-light text-dark text-uppercase"><?= $userRole ?></span>
            </div>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container-fluid px-4">
        
        <?php if ($userRole === 'patient'): ?>
            <?php foreach ($records as $check): ?>
                <?php if ($check['prediction_status'] === 'Approved'): ?>
                    <div class="alert alert-danger shadow border-0 mb-4 p-4" role="alert">
                        <h4 class="alert-heading">⚠️ Verified Clinical Diagnostics Alert</h4>
                        <p class="mb-2"><strong>AI Sequential Prediction:</strong> <?= htmlspecialchars($check['rnn_prediction']) ?></p>
                        <hr>
                        <p class="mb-0"><strong>Physician Directives / Reçete:</strong> <?= $check['doctor_notes'] ? htmlspecialchars($check['doctor_notes']) : 'No clinician feedback appended yet.' ?></p>
                    </div>
                    <?php break; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="row">
            
            <?php if ($userRole === 'patient'): ?>
            <div class="col-xl-3 col-lg-4 mb-4">
                <div class="card shadow border-0">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">Log Physiological Metrics</h5>
                        <form method="POST" action="">
                            <div class="mb-2">
                                <label class="form-label small">Age / Nation</label>
                                <div class="input-group">
                                    <input type="number" name="age" class="form-control" value="30" min="0" max="120" required>
                                    <input type="text" name="nation" class="form-control" value="Indonesia" required>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Birth Date</label>
                                <input type="date" name="birth" class="form-control" value="1996-06-01" required>
                            </div>
                            <div class="row mb-2">
                                <div class="col">
                                    <label class="form-label small">Weight (kg) / Height (cm)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.1" id="w_input" name="weight_kg" class="form-control" value="70.0" oninput="calculateLiveBMI()" required>
                                        <input type="number" step="0.1" id="h_input" name="height_cm" class="form-control" value="175.0" oninput="calculateLiveBMI()" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small text-muted">Auto-Computed BMI</label>
                                <input type="text" id="bmi_preview" class="form-control bg-light" value="22.86" readonly>
                            </div>
                            <div class="row mb-2">
                                <div class="col">
                                    <label class="form-label small">Systolic / Diastolic BP</label>
                                    <div class="input-group">
                                        <input type="number" name="systolic_bp" class="form-control" value="120" required>
                                        <input type="number" name="diastolic_bp" class="form-control" value="80" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Glucose (mg/dL) / HbA1c (%)</label>
                                <div class="input-group">
                                    <input type="number" name="blood_glucose" class="form-control" value="90" required>
                                    <input type="number" step="0.1" name="hba1c" class="form-control" value="5.4" required>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Cholesterol (mg/dL) / Pulse</label>
                                <div class="input-group">
                                    <input type="number" name="cholesterol_mgdl" class="form-control" value="180" required>
                                    <input type="number" name="heart_rate" class="form-control" value="72" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Smoking Lifestyle Status</label>
                                <select name="smoking_status" class="form-select" required>
                                    <option value="Never">Never Smoked</option>
                                    <option value="Former">Former Smoker</option>
                                    <option value="Active">Active Smoker</option>
                                </select>
                            </div>
                            <button type="submit" name="add_ehr" class="btn btn-primary w-100">Transmit to Processing Pipeline</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="<?= $userRole === 'patient' ? 'col-xl-9 col-lg-8' : 'col-12' ?>">
                <div class="card shadow border-0">
                    <div class="card-body">
                        <h5 class="card-title mb-3 <?= $userRole === 'doctor' ? 'text-danger' : 'text-primary' ?>">
                            <?= $userRole === 'doctor' ? 'System Master Patient Registry Matrix' : 'Your Longitudinal Tracking Array' ?>
                        </h5>
                        
                        <?php if (empty($records)): ?>
                            <div class="alert alert-warning">No patient records matched or found.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped align-middle shadow-sm rounded border text-center small">
                                    <thead class="table-dark">
                                        <tr>
                                            <?php if($userRole === 'doctor'): ?> 
                                                <th><a href="index.php?sort=patient_name&order=<?= $toggleOrder ?>" class="text-white text-decoration-none">Patient ⇅</a></th> 
                                                <th>Sex</th>
                                            <?php endif; ?>
                                            <th>Age</th>
                                            <th>Nation</th>
                                            <th>Birth Date</th>
                                            <th>BMI</th>
                                            <th>BP (S/D)</th>
                                            <th>Glucose</th>
                                            <th>HbA1c</th>
                                            <th>Cholesterol</th>
                                            <th>Smoking</th>
                                            <th>RNN Prediction Analysis</th>
                                            <th><a href="index.php?sort=prediction_status&order=<?= $toggleOrder ?>" class="text-white text-decoration-none">EHR Pipeline Status ⇅</a></th>
                                            <?php if($userRole === 'doctor'): ?> <th class="text-nowrap">Clinical Evaluation</th> <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($records as $row): ?>
                                            <tr>
                                                <?php if($userRole === 'doctor'): ?>
                                                    <td><strong><?= htmlspecialchars($row['patient_name']) ?></strong></td>
                                                    <td><span class="badge bg-light text-dark border"><?= $row['patient_gender'] ?></span></td>
                                                <?php endif; ?>
                                                <td><?= $row['age'] ?></td>
                                                <td><?= htmlspecialchars($row['nation']) ?></td>
                                                <td><?= $row['birth'] ?></td>
                                                <td><strong><?= $row['bmi'] ?></strong></td>
                                                <td><span class="badge bg-secondary"><?= $row['systolic_bp'] ?>/<?= $row['diastolic_bp'] ?></span></td>
                                                <td><?= $row['blood_glucose'] ?> mg/dL</td>
                                                <td><span class="badge <?= $row['hba1c'] >= 6.5 ? 'bg-danger' : 'bg-success' ?>"><?= $row['hba1c'] ?>%</span></td>
                                                <td><?= $row['cholesterol_mgdl'] ?> mg/dL</td>
                                                <td><small><?= $row['smoking_status'] ?></small></td>
                                                
                                                <td>
                                                    <?php if($userRole === 'doctor'): ?>
                                                        <span class="text-dark fw-bold"><?= htmlspecialchars($row['rnn_prediction']) ?></span>
                                                    <?php else: ?>
                                                        <?php if($row['prediction_status'] === 'Approved'): ?>
                                                            <span class="text-danger fw-bold">⚠️ <?= htmlspecialchars($row['rnn_prediction']) ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted text-center">In pipeline queue...</span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <span class="badge <?= $row['prediction_status'] === 'Approved' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                                        <?= $row['prediction_status'] ?>
                                                    </span>
                                                </td>

                                                <?php if($userRole === 'doctor'): ?>
                                                    <td class="text-nowrap">
                                                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm">Review & Approve</a>
                                                        <a href="index.php?delete_id=<?= $row['id'] ?>" class="btn btn-outline-dark btn-sm" onclick="return confirm('Wipe EHR entry?')">Delete</a>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
    function calculateLiveBMI() {
        const weight = parseFloat(document.getElementById('w_input').value);
        const height = parseFloat(document.getElementById('h_input').value);
        if(weight > 0 && height > 0) {
            const hMeters = height / 100;
            const bmi = weight / (hMeters * hMeters);
            document.getElementById('bmi_preview').value = bmi.toFixed(2);
        }
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>