<?php
require_once 'classes/Database.php';
require_once 'classes/Auth.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

if ($auth->isLoggedIn()) { header("Location: index.php"); exit; }

$message = "";
$status = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($auth->register($username, $password, $role)) {
        $status = "success";
        $message = "Registration successful! You can now log in.";
    } else {
        $status = "danger";
        $message = "Username already exists or invalid data.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EHR System - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow border-0">
                    <div class="card-body p-4">
                        <h3 class="card-title text-center text-primary mb-4">Create Account</h3>
                        <?php if($message): ?>
                            <div class="alert alert-<?= $status ?> text-center"><?= $message ?></div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Account Type (Role)</label>
                                <select name="role" class="form-select" required>
                                    <option value="patient">Patient (Enter Medical Data)</option>
                                    <option value="doctor">Medical Staff / Doctor</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">Sign Up</button>
                            <p class="text-center small mb-0">Already have an account? <a href="login.php" class="text-decoration-none">Login here</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>