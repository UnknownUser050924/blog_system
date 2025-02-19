<?php
include('db.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']); // Ensure it's an integer

// Fetch pending friend requests
$query = "SELECT f.id, u.username 
          FROM friendships f 
          JOIN users u ON f.user_id = u.user_id 
          WHERE f.friend_id = $user_id AND f.status = 'pending'";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error fetching friend requests: " . mysqli_error($conn));
}

// Handle friend request actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = isset($_POST['request_id']) ? intval($_POST['request_id']) : null;
    $action = isset($_POST['action']) ? $_POST['action'] : null;

    if ($request_id && ($action === 'accept' || $action === 'reject')) {
        $status = mysqli_real_escape_string($conn, $action === 'accept' ? 'accepted' : 'rejected');

        $update_query = "UPDATE friendships SET status = '$status' WHERE id = $request_id AND friend_id = $user_id";
        if (!mysqli_query($conn, $update_query)) {
            die("Error updating friend request: " . mysqli_error($conn));
        }
        
        // Log the action
        $log_action = "Friend request from request ID $request_id was $status";
        $log_query = "INSERT INTO activity_log (user_id, action) VALUES ($user_id, '$log_action')";

        if (!mysqli_query($conn, $log_query)) {
            die("Error logging action: " . mysqli_error($conn));
        }

        // Refresh the page to update the request list
        header("Location: manage_friend_requests.php");
        exit();
    } else {
        echo "<p style='color:red;'>Invalid action!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Friend Requests</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            min-height: 100vh;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .container {
            width: 100%;
            max-width: 500px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .request-box {
            background: #fff;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .request-box p {
            font-size: 16px;
            font-weight: 500;
            margin: 0;
            color: #444;
        }

        .button-group {
            display: flex;
            gap: 10px;
        }

        button {
            border: none;
            padding: 8px 15px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 5px;
            transition: 0.3s;
        }

        .btn-accept {
            background: #4CAF50;
            color: white;
        }

        .btn-reject {
            background: #e74c3c;
            color: white;
        }

        .btn-accept:hover {
            background: #388e3c;
        }

        .btn-reject:hover {
            background: #c0392b;
        }

        .cancel-link {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            color: #3498db;
            font-weight: 500;
        }

        .cancel-link:hover {
            color: #217dbb;
        }

        @media (max-width: 600px) {
            .container {
                width: 90%;
            }
        }
    </style>
</head>
<body>

<h2>Manage Friend Requests</h2>
<div class="container">
    <a href="friend_list.php" class="cancel-link">Go Back</a>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="request-box">
                <p><?= htmlspecialchars($row['username']) ?> sent you a friend request.</p>
                <div class="button-group">
                    <form method="post">
                        <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                        <button type="submit" name="action" value="accept" class="btn-accept">Accept</button>
                    </form>
                    <form method="post">
                        <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                        <button type="submit" name="action" value="reject" class="btn-reject">Reject</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No pending friend requests.</p>
    <?php endif; ?>
</div>

</body>
</html>
