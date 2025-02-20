<?php
include('db.php'); // Database connection

// Check if an admin already exists
$query = "SELECT * FROM admins LIMIT 1";
$result = $conn->query($query);
$admin_exists = ($result->num_rows > 0);

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['confirm'])) {
        // Confirm the default admin account
        $admin_username = "admin";
        $admin_password = "admin123"; // Default password
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

        $insert_query = "INSERT INTO admins (username, password) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param('ss', $admin_username, $hashed_password);

        if ($stmt->execute()) {
            $message = "The account <strong>admin</strong> with password <strong>admin123</strong> has been created.<br>Redirecting you to the login page...";
            echo "<script>
                    setTimeout(function() {
                        window.location.href = 'admin_login.php';
                    }, 3000);
                  </script>";
        } else {
            $message = "Error creating the default admin account.";
        }

        $stmt->close();
    } elseif (isset($_POST['create'])) {
        // Create admin with custom details
        $admin_username = trim($_POST['admin_name']);
        $admin_password = trim($_POST['admin_password']);

        if (!empty($admin_username) && !empty($admin_password)) {
            $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

            $insert_query = "INSERT INTO admins (username, password) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param('ss', $admin_username, $hashed_password);

            if ($stmt->execute()) {
                $message = "Your account <strong>$admin_username</strong> with password <strong>$admin_password</strong> has been successfully created!<br>Welcome aboard! Your contributions will help in managing and maintaining a seamless experience.";
            } else {
                $message = "Error creating your account.";
            }

            $stmt->close();
        } else {
            $message = "Please fill in both username and password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f9;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
        }
        .hidden {
            display: none;
        }
        .btn-confirm {
            background: #28a745;
            color: white;
        }
        .btn-confirm:hover {
            background: #218838;
        }
        .btn-cancel {
            background: #dc3545;
            color: white;
        }
        .btn-cancel:hover {
            background: #c82333;
        }
        .input-group {
            margin-bottom: 15px;
        }
        .message-box {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            margin-top: 15px;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Create Admin Account</h2>
    <p>Please confirm or create a new admin account.</p>

    <?php if ($admin_exists): ?>
        <p><strong>An admin account already exists.</strong></p>
        <a href="admin_login.php" class="btn btn-primary">Go to Login</a>
    <?php else: ?>
        <div id="defaultAccount">
            <p><strong>Admin Username:</strong> admin</p>
            <p><strong>Admin Password:</strong> admin123</p>
            <form method="post">
                <button type="submit" name="confirm" class="btn btn-confirm">Confirm</button>
                <button type="button" id="cancelBtn" class="btn btn-cancel">Cancel</button>
            </form>
        </div>

        <div id="customAccount" class="hidden">
            <form method="post">
                <div class="input-group">
                    <input type="text" name="admin_name" class="form-control" placeholder="Enter Admin Name" required>
                </div>
                <div class="input-group">
                    <input type="password" name="admin_password" class="form-control" placeholder="Enter Password" required>
                </div>
                <button type="submit" name="create" class="btn btn-primary">Create Account</button>
            </form>
        </div>
    <?php endif; ?>

    <?php if (!empty($message)): ?>
        <div class="message-box" id="messageBox"><?php echo $message; ?></div>
        <script>
            setTimeout(function() {
                document.getElementById("messageBox").style.display = "none";
            }, 3000);
        </script>
    <?php endif; ?>
</div>

<script>
    document.getElementById("cancelBtn").addEventListener("click", function() {
        document.getElementById("defaultAccount").classList.add("hidden");
        document.getElementById("customAccount").classList.remove("hidden");
    });
</script>

</body>
</html>
