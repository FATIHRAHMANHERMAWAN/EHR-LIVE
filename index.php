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

// Controller Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_ehr']) && $userRole === 'patient') {
    $ehrManager->createRecord($userId, $_POST['age'], $_POST['bmi'], $_POST['systolic_bp'], $_POST['diastolic_bp'], $_POST['blood_glucose'], $_POST['heart_rate']);
    header("Location: index.php");
    exit;
}

if (isset($_GET['delete_id']) && $userRole === 'doctor') {
    $ehrManager->deleteRecord($_GET['delete_id']);
    header("Location: index.php");
    exit;
}

$records = $ehrManager->readRecords($userId, $userRole);
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
        <div class="row">
            
            <?php if ($userRole === 'patient'): ?>
            <div class="col-md-4 mb-4">
                <div class="card shadow border-0">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">Input Vitals (RNN Input Data)</h5>
                        <form method="POST" action="">
                            <div class="mb-2">
                                <label class="form-label small">Age</label>
                                <input type="number" name="age" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">BMI (Body Mass Index)</label>
                                <input type="number" step="0.01" name="bmi" class="form-control" required>
                            </div>
                            <div class="row mb-2">
                                <div class="col">
                                    <label class="form-label small">Systolic BP (Büyük)</label>
                                    <input type="number" name="systolic_bp" class="form-control" required>
                                </div>
                                <div class="col">
                                    <label class="form-label small">Diastolic BP (Küçük)</label>
                                    <input type="number" name="diastolic_bp" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Glucose Level (mg/dL)</label>
                                <input type="number" name="blood_glucose" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Heart Rate (bpm)</label>
                                <input type="number" name="heart_rate" class="form-control" required>
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
                                            <?php if($userRole === 'doctor'): ?> <th>Patient</th> <?php endif; ?>
                                            <th>Age</th>
                                            <th>BMI</th>
                                            <th>Blood Pressure</th>
                                            <th>Glucose</th>
                                            <th>Pulse</th>
                                            <th>Timestamp</th>
                                            <?php if($userRole === 'doctor'): ?> <th class="text-center">Actions</th> <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($records as $row): ?>
                                            <tr>
                                                <?php if($userRole === 'doctor'): ?>
                                                    <td><strong><?= htmlspecialchars($row['patient_name']) ?></strong></td>
                                                <?php endif; ?>
                                                <td><?= $row['age'] ?></td>
                                                <td><?= $row['bmi'] ?></td>
                                                <td><span class="badge bg-secondary"><?= $row['systolic_bp'] ?>/<?= $row['diastolic_bp'] ?></span></td>
                                                <td><span class="badge <?= $row['blood_glucose'] > 125 ? 'bg-danger' : 'bg-success' ?>"><?= $row['blood_glucose'] ?> mg/dL</span></td>
                                                <td><?= $row['heart_rate'] ?> bpm</td>
                                                <td><small class="text-muted"><?= $row['recorded_at'] ?></small></td>
                                                <?php if($userRole === 'doctor'): ?>
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
</body>
</html>