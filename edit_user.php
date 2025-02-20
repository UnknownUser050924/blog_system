<?php
include('db.php');
session_start();

// Check if user ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_users.php");
    exit();
}

$user_id = intval($_GET['id']);

// Fetch user details before update
$stmt = $conn->prepare("SELECT username, email, role FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: manage_users.php");
    exit();
}

$user = $result->fetch_assoc();
$old_username = $user['username'];
$old_email = $user['email'];
$old_role = $user['role']; // Fetch the role
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = trim($_POST['username'] ?? '');
    $new_email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? '';
    $password = trim($_POST['password'] ?? '');

    // Basic validation
    if (empty($new_username) || empty($new_email) || empty($role)) {
        $_SESSION['message'] = "All fields except password are required!";
        $_SESSION['msg_type'] = "danger";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "Invalid email format!";
        $_SESSION['msg_type'] = "danger";
    } else {
        // Update query
        if (!empty($password)) {
            // Hash new password if provided
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, password = ? WHERE user_id = ?");
            $stmt->bind_param("ssssi", $new_username, $new_email, $role, $hashed_password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE user_id = ?");
            $stmt->bind_param("sssi", $new_username, $new_email, $role, $user_id);
        }

        if ($stmt->execute()) {
            $_SESSION['edit_message'] = "User <b>$old_username</b> has been updated to <b>$new_username</b> (Email: <b>$new_email</b>) successfully.";
        } else {
            $_SESSION['edit_message'] = "Error updating user!";
        }
        $stmt->close();

        // Redirect back to manage_users.php
        header("Location: manage_users.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
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
            height: 100vh;
            background-color: #f4f4f9;
        }
        .sidebar {
            width: 250px;
            background: #343a40;
            color: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 22px;
        }
        .sidebar a {
            display: block;
            padding: 10px;
            color: white;
            text-decoration: none;
            margin: 5px 0;
            border-radius: 5px;
        }
        .sidebar a.active, .sidebar a:hover {
            background: #007bff;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: auto;
        }
        .logout-btn {
            margin-top: 20px;
            display: block;
            background: #dc3545;
            text-align: center;
        }
        .logout-btn:hover {
            background: #c82333;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="manage_users.php" class="active">Manage Users</a>
    <a href="manage_posts.php">Manage Posts</a>
    <a href="manage_comments.php">Manage Comments</a>
    <a href="activity_logs.php">Activity Logs</a>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <h2 class="mb-4">Edit User</h2>

    <div class="form-container">
        <h4 class="text-center">Update User Information</h4>

        <!-- Success & Error Messages -->
        <?php if (!empty($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['msg_type']; ?>">
                <?= $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['msg_type']); ?>
        <?php endif; ?>

        <!-- User Edit Form -->
        <form action="edit_user.php?id=<?= $user_id ?>" method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-control" required>
                    <option value="blogger" <?= ($old_role == 'blogger') ? 'selected' : '' ?>>Blogger</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">New Password (Leave blank to keep current)</label>
                <input type="password" name="password" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary w-100">Update User</button>
        </form>

        <div class="text-center mt-3">
            <a href="manage_users.php" class="btn btn-secondary">Back to Users</a>
        </div>
    </div>
</div>

<!-- Bootstrap Script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
