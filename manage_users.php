<?php
include('db.php');
session_start();

// Handle delete request
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);

    // Get the username before deleting
    $result = $conn->query("SELECT username FROM users WHERE user_id = $user_id");
    $user = $result->fetch_assoc();
    $username = $user['username'] ?? 'Unknown User';

    // First, delete related logs
    $conn->query("DELETE FROM activity_log WHERE user_id = $user_id");

    // Then, delete the user
    $conn->query("DELETE FROM users WHERE user_id = $user_id");

    // Store delete success message in session
    $_SESSION['delete_message'] = "User <b>$username</b> has been deleted successfully.";

    header("Location: manage_users.php");
    exit();
}

// Fetch users
$result = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { display: flex; height: 100vh; background-color: #f4f4f9; }
        .sidebar { width: 250px; background: #343a40; color: white; padding: 20px; height: 100vh; position: fixed; }
        .sidebar h2 { text-align: center; margin-bottom: 20px; font-size: 22px; }
        .sidebar a { display: block; padding: 10px; color: white; text-decoration: none; margin: 5px 0; border-radius: 5px; }
        .sidebar a.active, .sidebar a:hover { background: #007bff; }
        .main-content { margin-left: 250px; padding: 20px; width: calc(100% - 250px); }
        .table-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); }
        .table th, .table td { vertical-align: middle; text-align: center; }
        .btn-sm { padding: 5px 10px; }
        .btn-add { background: #28a745; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; }
        .btn-add:hover { background: #218838; }
        .logout-btn { margin-top: 20px; display: block; background: #dc3545; text-align: center; }
        .logout-btn:hover { background: #c82333; }
        .alert { position: relative; padding: 10px; border-radius: 5px; margin-bottom: 10px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-danger { background: #f8d7da; color: #721c24; }
        .fade-out { animation: fadeOut 2s forwards; }
        @keyframes fadeOut { 0% { opacity: 1; } 100% { opacity: 0; display: none; } }
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
    <h2 class="mb-4">Manage Users</h2>

    <!-- Success & Error Messages -->
    <?php if (isset($_SESSION['delete_message'])): ?>
        <div class="alert alert-danger fade-out"><?= $_SESSION['delete_message']; ?></div>
        <?php unset($_SESSION['delete_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['edit_message'])): ?>
        <div class="alert alert-success fade-out"><?= $_SESSION['edit_message']; ?></div>
        <?php unset($_SESSION['edit_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['add_message'])): ?>
        <div class="alert alert-success fade-out"><?= $_SESSION['add_message']; ?></div>
        <?php unset($_SESSION['add_message']); ?>
    <?php endif; ?>

    <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>User List</h4>
            <a href="add_user.php" class="btn-add">+ Add User</a>
        </div>

        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['user_id']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td><?= htmlspecialchars($user['created_at']) ?></td>
                        <td>
                            <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="manage_users.php?delete=<?= $user['user_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap & JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Automatically fade out success/error messages after 2 seconds
    setTimeout(() => {
        document.querySelectorAll('.fade-out').forEach(el => el.style.display = 'none');
    }, 2000);
</script>

</body>
</html>
