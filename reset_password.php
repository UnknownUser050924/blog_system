<?php
// Include the database connection
include('db.php');
session_start();

// Handle the password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = $_SESSION['user_id']; // Get the current user ID

    // Fetch current password from the database
    $sql = "SELECT * FROM users WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    // Check if the current password matches the one in the database
    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $sql = "UPDATE users SET password = '$hashed_password' WHERE user_id = '$user_id'";
            if (mysqli_query($conn, $sql)) {
                // Log the password reset action
                $action = "User reset their password";
                $log_sql = "INSERT INTO activity_log (user_id, action) VALUES ('$user_id', '$action')";
                mysqli_query($conn, $log_sql);

                echo "Password updated successfully.";
            } else {
                echo "Error updating password.";
            }
        } else {
            echo "New passwords do not match.";
        }
    } else {
        echo "Incorrect current password.";
    }
}
?>

<!-- Reset Password Form -->
<form method="POST" action="reset_password.php">
    <label for="current_password">Current Password:</label>
    <input type="password" name="current_password" required><br>
    
    <label for="new_password">New Password:</label>
    <input type="password" name="new_password" required><br>
    
    <label for="confirm_password">Confirm New Password:</label>
    <input type="password" name="confirm_password" required><br>
    
    <button type="submit">Reset Password</button>
</form>

<!-- Back to Login Link -->
<p>Remember your password? <a href="login.php">Back to Login</a></p>
