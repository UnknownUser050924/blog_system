<?php
include('db.php');
session_start();

// Ensure the user is an admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['username'])) {
    echo "Access Denied. Admin login is required.";
    exit();
}

// Fetch all activity logs, including admin actions
$query = "SELECT a.log_id, a.action, a.timestamp, 
                 COALESCE(u.username, 'Admin') AS username
          FROM activity_log a
          LEFT JOIN users u ON a.user_id = u.user_id
          ORDER BY a.timestamp DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs</title>
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
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
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
    <a href="manage_users.php">Manage Users</a>
    <a href="manage_posts.php">Manage Posts</a>
    <a href="manage_comments.php">Manage Comments</a>
    <a href="activity_logs.php" class="active">Activity Logs</a>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <h2 class="mb-4">Activity Logs</h2>

    <div class="table-container">
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Log ID</th>
                    <th>Admin Username</th>
                    <th>Action</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <tr>
                        <td><?= $row['log_id'] ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['action']) ?></td>
                        <td><?= $row['timestamp'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap Script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
