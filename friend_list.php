<?php
session_start(); // Start the session to maintain login state
include('db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']); // Ensure user_id is an integer

// Fetch friends of the user
$sql_friends = "SELECT users.user_id, users.username
                FROM users
                JOIN friendships ON (friendships.user_id = users.user_id AND friendships.friend_id = $user_id)
                WHERE friendships.status = 'accepted'
                UNION
                SELECT users.user_id, users.username
                FROM users
                JOIN friendships ON (friendships.friend_id = users.user_id AND friendships.user_id = $user_id)
                WHERE friendships.status = 'accepted'";

$result_friends = mysqli_query($conn, $sql_friends);
if (!$result_friends) {
    die("Error fetching friends: " . mysqli_error($conn));
}
$friends = mysqli_fetch_all($result_friends, MYSQLI_ASSOC);

// Fetch all users to allow sending friend requests
$sql_all_users = "SELECT user_id, username FROM users WHERE user_id != $user_id";
$result_all_users = mysqli_query($conn, $sql_all_users);
if (!$result_all_users) {
    die("Error fetching users: " . mysqli_error($conn));
}
$all_users = mysqli_fetch_all($result_all_users, MYSQLI_ASSOC);

// Handle adding a friend
if (isset($_POST['add_friend'])) {
    $friend_id = intval($_POST['friend_id']); // Ensure friend_id is an integer
    
    // Check if already friends or if the request exists
    $check_query = "SELECT * FROM friendships WHERE (user_id = $user_id AND friend_id = $friend_id) 
                    OR (user_id = $friend_id AND friend_id = $user_id)";
    $result_check = mysqli_query($conn, $check_query);

    if (!$result_check) {
        die("Error checking friend status: " . mysqli_error($conn));
    }

    if (mysqli_num_rows($result_check) == 0) {
        $insert_query = "INSERT INTO friendships (user_id, friend_id, status) VALUES ($user_id, $friend_id, 'pending')";
        if (!mysqli_query($conn, $insert_query)) {
            die("Error inserting friend request: " . mysqli_error($conn));
        }
        echo "<script>alert('Friend request sent!'); window.location.href='friend_list.php';</script>";
    } else {
        echo "<script>alert('Friend request already exists or you are already friends.');</script>";
    }
}

// Handle removing a friend
if (isset($_POST['remove_friend'])) {
    $friend_id = intval($_POST['friend_id']); // Ensure friend_id is an integer
    $remove_query = "DELETE FROM friendships WHERE (user_id = $user_id AND friend_id = $friend_id) 
                     OR (user_id = $friend_id AND friend_id = $user_id)";
    if (!mysqli_query($conn, $remove_query)) {
        die("Error removing friend: " . mysqli_error($conn));
    }
    echo "<script>alert('Friend removed!'); window.location.href='friend_list.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friend List</title>
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
            flex-direction: column;
        }

        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h2, h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .back-button {
            text-align: center;
            margin-bottom: 15px;
        }

        .back-button a {
            text-decoration: none;
            color: white;
            background: #007bff;
            padding: 8px 15px;
            border-radius: 5px;
            margin: 5px;
            display: inline-block;
        }

        .back-button a:hover {
            background: #0056b3;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        ul li {
            background: #e9ecef;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        button {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        button:hover {
            background: #c82333;
        }

        select, input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .add-friend {
            margin-top: 20px;
            text-align: left;
        }

        .send-request {
            background: #28a745;
            width: 100%;
        }

        .send-request:hover {
            background: #218838;
        }
    </style>
</head>
<body>

<!-- Navigation Buttons -->
<div class="back-button">
    <a href="profile.php">Back to Profile</a>
    <a href="manage_friend_requests.php">Friend Request</a>
</div>

<!-- Friend List Container -->
<div class="container">
    <h2>Your Friend List</h2>

    <!-- Friend List -->
    <h3>Friends</h3>
    <?php if (count($friends) > 0): ?>
        <ul>
            <?php foreach ($friends as $friend): ?>
                <li>
                    <?php echo htmlspecialchars($friend['username']); ?>
                    <form action="" method="POST" style="display:inline;">
                        <input type="hidden" name="friend_id" value="<?php echo $friend['user_id']; ?>" />
                        <button type="submit" name="remove_friend">Remove</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>You have no friends yet.</p>
    <?php endif; ?>

    <!-- Friend Request Section -->
    <div class="add-friend">
        <h3>Add a New Friend</h3>
        <form action="" method="POST">
            <label for="friend_id">Select a friend:</label>
            <select name="friend_id" id="friend_id">
                <?php foreach ($all_users as $user): ?>
                    <option value="<?php echo $user['user_id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="add_friend" class="send-request">Send Friend Request</button>
        </form>
    </div>
</div>

</body>
</html>
