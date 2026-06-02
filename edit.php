<?php
require_once 'classes/Database.php';
require_once 'classes/Auth.php';
require_once 'classes/EhrManager.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

if (!$auth->isLoggedIn() || $auth->getRole() !== 'doctor') {
    die("Access Denied: Doctors only.");
}

$ehrManager = new EhrManager($db);

if (!isset($_GET['id'])) { header("Location: index.php"); exit; }
$item = $ehrManager->readOne($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ehrManager->updateRecord($_GET['id'], $_POST['age'], $_POST['bmi'], $_POST['systolic_bp'], $_POST['diastolic_bp'], $_POST['blood_glucose'], $_POST['heart_rate']);
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Modify Medical Record</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow border-0">
                    <div class="card-body p-4">
                        <h4 class="card-title text-danger mb-4">Edit EHR Metrics</h4>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Age</label>
                                <input type="number" name="age" class="form-control" value="<?= $item['age'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">BMI</label>
                                <input type="number" step="0.01" name="bmi" class="form-control" value="<?= $item['bmi'] ?>" required>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <label class="form-label">Systolic BP</label>
                                    <input type="number" name="systolic_bp" class="form-control" value="<?= $item['systolic_bp'] ?>" required>
                                </div>
                                <div class="col">
                                    <label class="form-label">Diastolic BP</label>
                                    <input type="number" name="diastolic_bp" class="form-control" value="<?= $item['diastolic_bp'] ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Glucose Level</label>
                                <input type="number" name="blood_glucose" class="form-control" value="<?= $item['blood_glucose'] ?>" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Heart Rate</label>
                                <input type="number" name="heart_rate" class="form-control" value="<?= $item['heart_rate'] ?>" required>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-danger">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>