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

// Controller Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_ehr']) && $userRole === 'patient') {
    $age = (int)$_POST['age'];
    if($age >= 0 && $age <= 120) {
        $ehrManager->createRecord(
            $userId, $age, $_POST['weight_kg'], $_POST['height_cm'], $_POST['systolic_bp'], 
            $_POST['diastolic_bp'], $_POST['blood_glucose'], $_POST['heart_rate'],
            $_POST['nation'], $_POST['birth']
        );
        header("Location: index.php");
        exit;
    }
}

// Doctor Action: Approval Trigger Routine
if (isset($_GET['approve_id']) && $userRole === 'doctor') {
    $ehrManager->approvePrediction($_GET['approve_id']);
    header("Location: index.php?sort=$currentSort&order=$currentOrder");
    exit;
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
    <title>EHR Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark <?= $userRole === 'doctor' ? 'bg-danger' : 'bg-primary' ?> mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">EHR Intelligence System</a>
            <div class="navbar-text ms-auto text-white me-3">
                User: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> 
                <span class="badge bg-light text-dark text-uppercase"><?= $userRole ?></span>
            </div>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container">
        
        <?php if ($userRole === 'patient'): ?>
            <?php foreach ($records as $check): ?>
                <?php if ($check['prediction_status'] === 'Approved' && $check['rnn_prediction'] !== 'Low Risk: No Anomalies Detected.'): ?>
                    <div class="alert alert-danger alert-dismissible fade show shadow border-0 mb-4" role="alert">
                        <h4 class="alert-heading">⚠️ Medical Staff Verified AI Diagnosis Notice</h4>
                        <p class="mb-0"><strong>Status Evaluation:</strong> <?= htmlspecialchars($check['rnn_prediction']) ?></p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php break; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="row">
            
            <?php if ($userRole === 'patient'): ?>
            <div class="col-md-4 mb-4">
                <div class="card shadow border-0">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">Input Vitals (RNN Input Data)</h5>
                        <form method="POST" action="">
                            <div class="mb-2">
                                <label class="form-label small">Age (0 - 120)</label>
                                <input type="number" name="age" class="form-control" value="30" min="0" max="120" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Nation / Nationality</label>
                                <input type="text" name="nation" class="form-control" value="Indonesia" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Birth Date</label>
                                <input type="date" name="birth" class="form-control" value="1996-06-01" required>
                            </div>
                            <div class="row mb-2">
                                <div class="col">
                                    <label class="form-label small">Weight (kg)</label>
                                    <input type="number" step="0.1" id="w_input" name="weight_kg" class="form-control" value="70.0" oninput="calculateLiveBMI()" required>
                                </div>
                                <div class="col">
                                    <label class="form-label small">Height (cm)</label>
                                    <input type="number" step="0.1" id="h_input" name="height_cm" class="form-control" value="175.0" oninput="calculateLiveBMI()" required>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small text-muted">Computed Target BMI</label>
                                <input type="text" id="bmi_preview" class="form-control bg-light" value="22.86" readonly>
                            </div>
                            <div class="row mb-2">
                                <div class="col">
                                    <label class="form-label small">Systolic BP (Büyük)</label>
                                    <input type="number" name="systolic_bp" class="form-control" value="120" required>
                                </div>
                                <div class="col">
                                    <label class="form-label small">Diastolic BP (Küçük)</label>
                                    <input type="number" name="diastolic_bp" class="form-control" value="80" required>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Glucose Level (mg/dL)</label>
                                <input type="number" name="blood_glucose" class="form-control" value="90" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Heart Rate (bpm)</label>
                                <input type="number" name="heart_rate" class="form-control" value="72" required>
                            </div>
                            <button type="submit" name="add_ehr" class="btn btn-primary w-100">Submit to Pipeline</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="<?= $userRole === 'patient' ? 'col-md-8' : 'col-md-12' ?>">
                <div class="card shadow border-0">
                    <div class="card-body">
                        <h5 class="card-title mb-3 <?= $userRole === 'doctor' ? 'text-danger' : 'text-primary' ?>">
                            <?= $userRole === 'doctor' ? 'All System EHR Records' : 'Your Physiological History' ?>
                        </h5>
                        
                        <?php if (empty($records)): ?>
                            <div class="alert alert-warning">No records found.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped align-middle shadow-sm rounded">
                                    <thead class="table-dark">
                                        <tr>
                                            <?php if($userRole === 'doctor'): ?> 
                                                <th><a href="index.php?sort=patient_name&order=<?= $toggleOrder ?>" class="text-white text-decoration-none">Patient ⇅</a></th> 
                                            <?php endif; ?>
                                            <th>Age</th>
                                            <th>Nation</th>
                                            <th>Birth Date</th>
                                            <th>BMI</th>
                                            <th>Blood Pressure</th>
                                            <th>Glucose</th>
                                            <th>Pulse</th>
                                            <th>RNN Diagnostics Prediction</th>
                                            <?php if($userRole === 'doctor'): ?> 
                                                <th><a href="index.php?sort=prediction_status&order=<?= $toggleOrder ?>" class="text-white text-decoration-none">Status ⇅</a></th>
                                                <th class="text-center">Actions</th> 
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($records as $row): ?>
                                            <tr>
                                                <?php if($userRole === 'doctor'): ?>
                                                    <td><strong><?= htmlspecialchars($row['patient_name']) ?></strong></td>
                                                <?php endif; ?>
                                                <td><?= $row['age'] ?></td>
                                                <td><?= htmlspecialchars($row['nation']) ?></td>
                                                <td><?= $row['birth'] ?></td>
                                                <td><span class="badge bg-light text-dark border"><?= $row['bmi'] ?></span></td>
                                                <td><span class="badge bg-secondary"><?= $row['systolic_bp'] ?>/<?= $row['diastolic_bp'] ?></span></td>
                                                <td><?= $row['blood_glucose'] ?> mg/dL</td>
                                                <td><?= $row['heart_rate'] ?> bpm</td>
                                                
                                                <td>
                                                    <?php if($userRole === 'doctor'): ?>
                                                        <small class="text-dark fw-bold"><?= htmlspecialchars($row['rnn_prediction']) ?></small>
                                                    <?php else: ?>
                                                        <?php if($row['prediction_status'] === 'Approved'): ?>
                                                            <span class="text-danger fw-bold">⚠️ <?= htmlspecialchars($row['rnn_prediction']) ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted italic">Processing in pipeline...</span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>

                                                <?php if($userRole === 'doctor'): ?>
                                                    <td>
                                                        <?php if($row['prediction_status'] === 'Pending Approval'): ?>
                                                            <a href="index.php?approve_id=<?= $row['id'] ?>&sort=<?= $currentSort ?>&order=<?= $currentOrder ?>" class="btn btn-outline-success btn-sm font-monospace fw-bold">Approve ✓</a>
                                                        <?php else: ?>
                                                            <span class="badge bg-success">Pushed to Dashboard</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm me-1">Edit</a>
                                                        <a href="index.php?delete_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this medical record?')">Delete</a>
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