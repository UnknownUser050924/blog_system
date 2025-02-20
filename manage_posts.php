<?php
include('db.php');

$success = $error = ""; // Initialize message variables

if (isset($_GET['delete'])) {
    $post_id = intval($_GET['delete']);

    // Fetch post details before deletion
    $stmt = $conn->prepare("SELECT title, users.username FROM posts JOIN users ON posts.user_id = users.user_id WHERE posts.post_id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $stmt->bind_result($post_title, $author_name);
    $stmt->fetch();
    $stmt->close();

    if ($post_title && $author_name) {
        // First, delete all likes related to the post
        $stmt1 = $conn->prepare("DELETE FROM post_likes WHERE post_id = ?");
        $stmt1->bind_param("i", $post_id);
        $stmt1->execute();
        $stmt1->close();

        // Then, delete all comments related to the post
        $stmt2 = $conn->prepare("DELETE FROM comments WHERE post_id = ?");
        $stmt2->bind_param("i", $post_id);
        $stmt2->execute();
        $stmt2->close();

        // Finally, delete the post itself
        $stmt3 = $conn->prepare("DELETE FROM posts WHERE post_id = ?");
        $stmt3->bind_param("i", $post_id);
        if ($stmt3->execute()) {
            $success = "Post '<strong>$post_title</strong>' by <strong>$author_name</strong> has been deleted.";
        } else {
            $error = "Error deleting post!";
        }
        $stmt3->close();
    } else {
        $error = "Post not found!";
    }
}

// Fetch posts
$result = $conn->query("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.user_id ORDER BY posts.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Posts</title>
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
        .btn-sm {
            padding: 5px 10px;
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
        .fade-out {
            opacity: 1;
            transition: opacity 0.5s ease-out;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="manage_users.php">Manage Users</a>
    <a href="manage_posts.php" class="active">Manage Posts</a>
    <a href="manage_comments.php">Manage Comments</a>
    <a href="activity_logs.php">Activity Logs</a>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <h2 class="mb-4">Manage Posts</h2>

    <div class="table-container">
        <h4 class="text-center">All Posts</h4>

        <!-- Success & Error Messages -->
        <?php if (!empty($success)): ?>
            <div id="message" class="alert alert-success fade-out"><?= $success; ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div id="message" class="alert alert-danger fade-out"><?= $error; ?></div>
        <?php endif; ?>

        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($post = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($post['post_id']) ?></td>
                        <td><?= htmlspecialchars($post['title']) ?></td>
                        <td><?= htmlspecialchars($post['username']) ?></td>
                        <td><?= htmlspecialchars($post['created_at']) ?></td>
                        <td>
                            <a href="manage_posts.php?delete=<?= $post['post_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Auto-hide messages -->
<script>
    setTimeout(() => {
        let message = document.getElementById('message');
        if (message) {
            message.style.opacity = '0';
            setTimeout(() => { message.style.display = 'none'; }, 500);
        }
    }, 2000);
</script>

<!-- Bootstrap Script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
