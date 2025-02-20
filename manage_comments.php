<?php
include('db.php');
session_start();

// Ensure the user is an admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['username'])) {
    echo "Access Denied. Admin login is required.";
    exit();
}

// Handle comment deletion
if (isset($_GET['delete_comment_id'])) {
    $comment_id = intval($_GET['delete_comment_id']); // Ensure it's an integer

    // Fetch comment details for logging
    $query = "SELECT c.comment_id, c.content, u.username, p.title 
              FROM comments c 
              JOIN users u ON c.user_id = u.user_id 
              JOIN posts p ON c.post_id = p.post_id 
              WHERE c.comment_id = ?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $comment_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $comment = mysqli_fetch_assoc($result);

        if ($comment) {
            // Log admin action
            $log_action = "Admin deleted comment from '{$comment['username']}' on post '{$comment['title']}': '{$comment['content']}'";
            $log_query = "INSERT INTO activity_log (user_id, action) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $log_query);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "is", $_SESSION['admin_id'], $log_action);
                mysqli_stmt_execute($stmt);
            }

            // Delete the comment
            $deleteQuery = "DELETE FROM comments WHERE comment_id = ?";
            $stmt = mysqli_prepare($conn, $deleteQuery);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $comment_id);
                mysqli_stmt_execute($stmt);
            }
        }
    }

    header("Location: manage_comments.php");
    exit();
}

// Fetch all comments
$query = "SELECT c.comment_id, c.content, c.created_at, u.username, p.title 
          FROM comments c 
          JOIN users u ON c.user_id = u.user_id 
          JOIN posts p ON c.post_id = p.post_id 
          ORDER BY c.created_at DESC";
$result = mysqli_query($conn, $query);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Comments</title>
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
    <a href="manage_comments.php" class="active">Manage Comments</a>
    <a href="activity_logs.php">Activity Logs</a>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <h2 class="mb-4">Manage Comments</h2>

    <div class="table-container">
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Username</th>
                    <th>Post Title</th>
                    <th>Comment</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <tr>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['content']) ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td>
                            <a href="manage_comments.php?delete_comment_id=<?= $row['comment_id'] ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Are you sure you want to delete this comment?');">
                                Delete
                            </a>
                        </td>
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
                    