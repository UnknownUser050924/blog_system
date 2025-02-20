<?php
session_start();
include('db.php'); // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check if admin exists
    $query = "SELECT * FROM admins WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $admin['password'])) {
            // Set session for admin
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['username'] = $admin['username'];

            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: linear-gradient(135deg, #667eea, #764ba2); 
            height: 100vh; 
            display: flex; 
            justify-content: center; 
            align-items: center;
        }
        .login-container { 
            width: 400px; 
            background: white; 
            padding: 25px; 
            border-radius: 12px; 
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        .login-container h2 {
            font-weight: bold;
            color: #333;
        }
        .form-control {
            border-radius: 8px;
        }
        .btn-primary {
            background-color: #667eea;
            border: none;
            transition: 0.3s;
            font-weight: bold;
        }
        .btn-primary:hover {
            background-color: #5a67d8;
        }
        .footer {
            margin-top: 15px;
            font-size: 12px;
            color: #666;
        }
        .icon {
            font-size: 50px;
            color: #667eea;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="icon">🔒</div>
    <h2>Admin Login</h2>
    <p class="text-muted">Sign in to manage your dashboard</p>

    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form action="admin_login.php" method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <div class="footer">© <?php echo date("Y"); ?> Admin Panel | All Rights Reserved</div>
</div>

</body>
</html>

