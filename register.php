<?php
// Include the database connection
include('db.php');

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Encrypt password
    $role = $_POST['role'];

    // Insert the user into the database
    $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password', '$role')";
    if (mysqli_query($conn, $sql)) {
        // Get the user ID of the newly registered user
        $user_id = mysqli_insert_id($conn);

        // Log the registration action
        $action = "User registered with username: $username";
        $log_sql = "INSERT INTO activity_log (user_id, action) VALUES ('$user_id', '$action')";
        mysqli_query($conn, $log_sql);

        // Display success message and redirect to login page after 3 seconds
        echo "<p>Registration successful! Redirecting to login page...</p>";
        header("refresh:2;url=login.php"); // Redirect to login page after 3 seconds
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}
?>

<!-- Registration Form -->
<form method="POST" action="register.php">
    <label for="username">Username:</label>
    <input type="text" name="username" required><br>
    
    <label for="email">Email:</label>
    <input type="email" name="email" required><br>
    
    <label for="password">Password:</label>
    <input type="password" name="password" required><br>
    
    <label for="role">Role:</label>
    <select name="role">
        <option value="writer">Writer</option>
        <option value="blogger">Blogger</option>
    </select><br>
    
    <button type="submit">Register</button>
</form>

<!-- Back to Login Button -->
<p>Already have an account? <a href="login.php">Back to Login</a></p>
