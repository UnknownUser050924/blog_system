<?php
include('db.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the user's profile information
$sql = "SELECT * FROM users WHERE user_id = '$user_id' LIMIT 1";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .profile-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h2 {
            margin-bottom: 10px;
        }

        .back-buttons {
            margin-bottom: 15px;
        }

        .back-buttons a {
            text-decoration: none;
            color: white;
            background: #007bff;
            padding: 8px 15px;
            border-radius: 5px;
            margin: 5px;
            display: inline-block;
        }

        .back-buttons a:hover {
            background: #0056b3;
        }

        .profile-picture {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #007bff;
            margin-bottom: 15px;
        }

        .profile-info {
            text-align: left;
            font-size: 16px;
            margin-top: 15px;
        }

        .profile-info strong {
            color: #333;
        }

        .update-profile {
            margin-top: 20px;
        }

        .update-profile a {
            text-decoration: none;
            color: white;
            background: #28a745;
            padding: 8px 15px;
            border-radius: 5px;
            display: inline-block;
        }

        .update-profile a:hover {
            background: #218838;
        }
    </style>
</head>
<body>

<div class="profile-container">
    <!-- Back Buttons -->
    <div class="back-buttons">
        <a href="blogger_dashboard.php">Back to Dashboard</a>
        <a href="friend_list.php">Friend List</a>
    </div>

    <!-- Profile Header -->
    <h2><?php echo htmlspecialchars($user['username']); ?>'s Profile</h2>

    <!-- Display Profile Information -->
    <div class="profile-info">
        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Age:</strong> <?php echo htmlspecialchars($user['age']); ?></p>
        <p><strong>Gender:</strong> <?php echo htmlspecialchars($user['gender']); ?></p>
        <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($user['address'])); ?></p>
        <p><strong>State:</strong> <?php echo htmlspecialchars($user['state']); ?></p>
        <p><strong>Country:</strong> <?php echo htmlspecialchars($user['country']); ?></p>
    </div>

    <!-- Update Profile Button -->
    <div class="update-profile">
        <a href="update_profile.php">Update Profile</a>
    </div>
</div>

</body>
</html>
