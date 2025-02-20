<?php
session_start();
include('db.php'); // Database connection

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['username'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_name = $_SESSION['username']; // Get admin name
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        .logout-btn {
            margin-top: 20px;
            display: block;
            background: #dc3545;
            text-align: center;
            padding: 10px;
            border-radius: 5px;
        }
        .logout-btn:hover {
            background: #c82333;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }
        .dashboard-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card {
            border-radius: 8px;
            padding: 20px;
            color: white;
        }
        /* Welcome message styling */
        .welcome-message {
            background: #28a745;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            font-weight: bold;
            display: block;
            transition: opacity 1s ease-in-out;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php" class="active">Dashboard</a>
    <a href="manage_users.php">Manage Users</a>
    <a href="manage_posts.php">Manage Posts</a>
    <a href="manage_comments.php">Manage Comments</a>
    <a href="activity_logs.php">Activity Logs</a>
    <a href="admin_logout.php" class="logout-btn">Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <div id="welcomeMessage" class="welcome-message">Welcome, <?php echo $admin_name; ?>!</div>

    <h2 class="mt-3">Admin Dashboard</h2>

    <div class="dashboard-container">
        <h4 class="mb-3">Overview</h4>

        <div class="row">
            <div class="col-md-3">
                <div class="card bg-primary">
                    <h5>Total Bloggers</h5>
                    <h2>
                        <?php
                        $query = "SELECT COUNT(*) AS total FROM users WHERE role = 'blogger'";
                        $result = $conn->query($query);
                        echo $result->fetch_assoc()['total'];
                        ?>
                    </h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success">
                    <h5>Total Posts/Articles</h5>
                    <h2>
                        <?php
                        $query = "SELECT COUNT(*) AS total FROM posts";
                        $result = $conn->query($query);
                        echo $result->fetch_assoc()['total'];
                        ?>
                    </h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning">
                    <h5>Total Comments</h5>
                    <h2>
                        <?php
                        $query = "SELECT COUNT(*) AS total FROM comments";
                        $result = $conn->query($query);
                        echo $result->fetch_assoc()['total'];
                        ?>
                    </h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger">
                    <h5>Recent Activities</h5>
                    <h2>
                        <?php
                        $query = "SELECT COUNT(*) AS total FROM activity_log";
                        $result = $conn->query($query);
                        echo $result->fetch_assoc()['total'];
                        ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- JavaScript to Auto-Hide Welcome Message -->
<script>
    setTimeout(() => {
        let welcomeMsg = document.getElementById("welcomeMessage");
        if (welcomeMsg) {
            welcomeMsg.style.opacity = "0";
            setTimeout(() => {
                welcomeMsg.style.display = "none";
            }, 1000);
        }
    }, 3000);
</script>

</body>
</html>
