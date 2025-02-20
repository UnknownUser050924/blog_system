<?php
// Include the database connection
include('db.php');
session_start();

// Handle the login process
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if the user exists in the database
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];

        // Log the login action
        $user_id = $user['user_id'];
        $action = "User logged in with email: $email";
        $log_sql = "INSERT INTO activity_log (user_id, action) VALUES ('$user_id', '$action')";
        mysqli_query($conn, $log_sql);

        // Redirect based on user role
        if ($user['role'] == 'blogger') {
            header("Location: blogger_dashboard.php");
        } elseif ($user['role'] == 'writer') {
            header("Location: writer_dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        $error_message = "Invalid email or password. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Blog System</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h2 {
            margin-bottom: 10px;
        }

        .description {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
        }

        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input {
            width: 94.5%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 14px;
            color: #007bff;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #0056b3;
        }

        .extra-links {
            margin-top: 15px;
            font-size: 14px;
        }

        .extra-links a {
            color: #007bff;
            text-decoration: none;
        }

        .extra-links a:hover {
            text-decoration: underline;
        }

        .benefits {
            margin-top: 20px;
            font-size: 14px;
            background: #e3f2fd;
            padding: 10px;
            border-radius: 5px;
            text-align: left;
        }

        .benefits ul {
            padding-left: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Welcome Back!</h2>
    <p class="description">Log in to manage your blog, write new posts, and engage with your audience.</p>

    <?php if (isset($error_message)): ?>
        <p class="error-message"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group password-wrapper">
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
            <span class="toggle-password" onclick="togglePassword()">Show</span>
        </div>

        <button type="submit">Login</button>
    </form>

    <p class="extra-links">
        Don't have an account? <a href="register.php">Register here</a><br>
        Forgot your password? <a href="reset_password.php">Reset Password</a>
    </p>

    <div class="benefits">
        <p><strong>Why login?</strong></p>
        <ul>
            <li>Manage and customize your blog</li>
            <li>Write and publish engaging posts</li>
            <li>Connect with fellow bloggers</li>
            <li>Track your blog analytics</li>
        </ul>
    </div>
</div>

<script>
    function togglePassword() {
        var passwordField = document.getElementById("password");
        var toggleText = document.querySelector(".toggle-password");

        if (passwordField.type === "password") {
            passwordField.type = "text";
            toggleText.textContent = "Hide";
        } else {
            passwordField.type = "password";
            toggleText.textContent = "Show";
        }
    }
</script>

</body>
</html>
