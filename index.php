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
    <title>EHR AI Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark <?= $userRole === 'doctor' ? 'bg-danger' : 'bg-primary' ?> mb-4 shadow-sm">
        <div class="container-fluid mx-3">
            <a class="navbar-brand" href="#">EHR Intelligence System</a>
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
                        <h4 class="alert-heading">⚠️ Verified Diagnostics Release</h4>
                        <p class="mb-2"><strong>AI Prediction Evaluation:</strong> <?= htmlspecialchars($check['rnn_prediction']) ?></p>
                        <hr>
                        <p class="mb-0"><strong>Physician Notes:</strong> <?= $check['doctor_notes'] ? htmlspecialchars($check['doctor_notes']) : 'No clinical comments left.' ?></p>
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
                        <h5 class="card-title text-primary mb-3">Input Vitals</h5>
                        <form method="POST" action="">
                            <div class="mb-2">
                                <label class="form-label small">Age / Nation</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="age" class="form-control" value="30" min="0" max="120" required>
                                    <input type="text" name="nation" class="form-control" value="Indonesia" required>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Birth Date</label>
                                <input type="date" name="birth" class="form-control form-control-sm" value="1996-06-01" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Weight (kg) / Height (cm)</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.1" id="w_input" name="weight_kg" class="form-control" value="70.0" oninput="calculateLiveMetrics()" required>
                                    <input type="number" step="0.1" id="h_input" name="height_cm" class="form-control" value="175.0" oninput="calculateLiveMetrics()" required>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col">
                                    <label class="form-label text-muted" style="font-size:11px;">Live BMI</label>
                                    <input type="text" id="bmi_preview" class="form-control form-control-sm bg-light" value="22.86" readonly>
                                </div>
                                <div class="col">
                                    <label class="form-label text-muted" style="font-size:11px;">Live MAP (mmHg)</label>
                                    <input type="text" id="map_preview" class="form-control form-control-sm bg-light" value="93" readonly>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Systolic / Diastolic BP</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" id="sys_input" name="systolic_bp" class="form-control" value="120" oninput="calculateLiveMetrics()" required>
                                    <input type="number" id="dia_input" name="diastolic_bp" class="form-control" value="80" oninput="calculateLiveMetrics()" required>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Glucose (mg/dL) / HbA1c (%)</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="blood_glucose" class="form-control" value="90" required>
                                    <input type="number" step="0.1" name="hba1c" class="form-control" value="5.4" required>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Cholesterol (mg/dL) / Pulse</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="cholesterol_mgdl" class="form-control" value="180" required>
                                    <input type="number" name="heart_rate" class="form-control" value="72" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Smoking Status</label>
                                <select name="smoking_status" class="form-select form-select-sm" required>
                                    <option value="Never">Never Smoked</option>
                                    <option value="Former">Former Smoker</option>
                                    <option value="Active">Active Smoker</option>
                                </select>
                            </div>
                            <button type="submit" name="add_ehr" class="btn btn-primary btn-sm w-100 shadow-sm">Submit Vector Data</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="<?= $userRole === 'patient' ? 'col-xl-9 col-lg-8' : 'col-12' ?>">
                <div class="card shadow border-0">
                    <div class="card-body">
                        <h5 class="card-title mb-3 <?= $userRole === 'doctor' ? 'text-danger' : 'text-primary' ?>">
                            Patient Data Records
                        </h5>
                        
                        <div class="table-responsive">
                            <table class="table table-striped align-middle shadow-sm rounded border text-center table-sm small" style="font-size:12px;">
                                <thead class="table-dark">
                                    <tr>
                                        <?php if($userRole === 'doctor'): ?> <th>Patient</th> <th>Sex</th> <?php endif; ?>
                                        <th>Age</th>
                                        <th>Nation</th>
                                        <th>BMI</th>
                                        <th>BP (S/D)</th>
                                        <th>MAP</th>
                                        <th>Glucose/HbA1c</th>
                                        <th>Cholesterol</th>
                                        <th>Smoking</th>
                                        <th style="width:15%;">Explainable AI (XAI) Weight Matrix</th>
                                        <th>Inference Output</th>
                                        <th>Status</th>
                                        <?php if($userRole === 'doctor'): ?> <th class="text-nowrap">Actions</th> <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($records as $row): ?>
                                        <tr>
                                            <?php if($userRole === 'doctor'): ?>
                                                <td><strong><?= htmlspecialchars($row['patient_name']) ?></strong></td>
                                                <td><?= $row['patient_gender'] ?></td>
                                            <?php endif; ?>
                                            <td><?= $row['age'] ?></td>
                                            <td><?= htmlspecialchars($row['nation']) ?></td>
                                            <td><?= $row['bmi'] ?></td>
                                            <td><?= $row['systolic_bp'] ?>/<?= $row['diastolic_bp'] ?></td>
                                            <td><strong><?= $row['map_mmhg'] ?> <span class="text-muted" style="font-size:10px;">mmHg</span></strong></td>
                                            <td><?= $row['blood_glucose'] ?> / <?= $row['hba1c'] ?>%</td>
                                            <td><?= $row['cholesterol_mgdl'] ?></td>
                                            <td><?= $row['smoking_status'] ?></td>
                                            
                                            <td>
                                                <?php if($row['glucose_impact_pct'] == 0 && $row['cardio_impact_pct'] == 0 && $row['lifestyle_impact_pct'] == 0): ?>
                                                    <span class="text-muted text-center" style="font-size:10px;">Optimal Baseline</span>
                                                <?php else: ?>
                                                    <div class="progress shadow-sm" style="height: 14px; font-size:9px; font-weight:bold;">
                                                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $row['glucose_impact_pct'] ?>%">G:<?= $row['glucose_impact_pct'] ?>%</div>
                                                        <div class="progress-bar bg-warning text-dark" role="progressbar" style="width: <?= $row['cardio_impact_pct'] ?>%">C:<?= $row['cardio_impact_pct'] ?>%</div>
                                                        <div class="progress-bar bg-info text-dark" role="progressbar" style="width: <?= $row['lifestyle_impact_pct'] ?>%">L:<?= $row['lifestyle_impact_pct'] ?>%</div>
                                                    </div>
                                                <?php endif; ?>
                                            </td>

                                            <td><small class="fw-bold"><?= htmlspecialchars($row['rnn_prediction']) ?></small></td>
                                            <td><span class="badge <?= $row['prediction_status'] === 'Approved' ? 'bg-success' : 'bg-warning text-dark' ?>"><?= $row['prediction_status'] ?></span></td>

                                            <?php if($userRole === 'doctor'): ?>
                                                <td class="text-nowrap">
                                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm py-0" style="font-size:11px;">Evaluate</a>
                                                    <a href="index.php?delete_id=<?= $row['id'] ?>" class="btn btn-outline-dark btn-sm py-0" style="font-size:11px;" onclick="return confirm('Wipe data?')">Wipe</a>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
    function calculateLiveMetrics() {
        const weight = parseFloat(document.getElementById('w_input').value);
        const height = parseFloat(document.getElementById('h_input').value);
        const sys = parseFloat(document.getElementById('sys_input').value);
        const dia = parseFloat(document.getElementById('dia_input').value);
        
        if(weight > 0 && height > 0) {
            const hMeters = height / 100;
            document.getElementById('bmi_preview').value = (weight / (hMeters * hMeters)).toFixed(2);
        }
        if(sys > 0 && dia > 0) {
            document.getElementById('map_preview').value = Math.round((sys + (2 * dia)) / 3);
        }
    }
    </script>
</body>
</html>