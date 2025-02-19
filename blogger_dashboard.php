<?php
// Include the database connection
include('db.php');
session_start();

// Check if the user is logged in and is a blogger
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'blogger') {
    header('Location: login.php');
    exit;
}

// Fetch the user's username
$user_id = $_SESSION['user_id'];
$sql = "SELECT username FROM users WHERE user_id = '$user_id' LIMIT 1";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);
$username = $user['username'];

// Log the activity
$action = "User accessed the blogger dashboard";
$log_sql = "INSERT INTO activity_log (user_id, action) VALUES ('$user_id', '$action')";
mysqli_query($conn, $log_sql);

// Fetch posts with visibility settings (Public, Friends-Only, Private)
$sql_posts = "SELECT posts.*, users.username AS created_by
              FROM posts 
              LEFT JOIN users ON posts.user_id = users.user_id 
              LEFT JOIN friendships ON (friendships.user_id = '$user_id' AND friendships.friend_id = posts.user_id AND friendships.status = 'accepted')
              WHERE (posts.visibility = 'Public'
                     OR (posts.visibility = 'Private' AND posts.user_id = '$user_id') 
                     OR (posts.visibility = 'Friends-Only' AND friendships.status = 'accepted'))
              ORDER BY posts.created_at DESC";
$result_posts = mysqli_query($conn, $sql_posts);
$posts = mysqli_fetch_all($result_posts, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blogger Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: #f4f4f4;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #333;
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
        }

        .header h2 {
            margin: 0;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown button {
            background-color: #444;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            z-index: 1;
            width: 150px;
        }

        .dropdown-content a {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: #333;
        }

        .dropdown-content a:hover {
            background-color: #ddd;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .container {
            max-width: 900px;
            margin: 20px auto;
        }

        .add-post {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .post-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .post {
            background-color: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .post-title {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }

        .post-content {
            font-size: 14px;
            color: #555;
            flex-grow: 1;
        }

        .post-footer {
            font-size: 12px;
            color: #777;
            margin-top: 10px;
        }

        .btn {
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            margin: 5px 0;
        }

        .btn.edit {
            background-color: #28a745;
            color: white;
        }

        .btn.delete {
            background-color: #dc3545;
            color: white;
        }

        .btn.view-more {
            background-color: #007bff;
            color: white;
            text-align: center;
            display: block;
        }
    </style>
</head>
<body>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blogger Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f4f4;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #333;
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
        }

        .header h2 {
            margin: 0;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown button {
            background-color: #444;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            z-index: 1;
            width: 150px;
        }

        .dropdown-content a {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: #333;
        }

        .dropdown-content a:hover {
            background-color: #ddd;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .container {
            max-width: 900px;
            margin: 20px auto;
        }

        .add-post {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .post-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
        }

        .post {
            background-color: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .post-title {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .post-content {
            font-size: 14px;
            color: #555;
            flex-grow: 1;
            margin-bottom: 10px;
        }

        .post-footer {
            font-size: 12px;
            color: #777;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }

        .btn-container {
            display: flex;
            gap: 10px;
        }

        .btn {
            text-decoration: none;
            padding: 7px 12px;
            border-radius: 5px;
            font-size: 14px;
            text-align: center;
        }

        .btn.edit {
            background-color: #28a745;
            color: white;
        }

        .btn.delete {
            background-color: #dc3545;
            color: white;
        }

        .btn.view-more {
            background-color: #007bff;
            color: white;
            text-align: center;
            width: 100%;
            margin-top: 10px;
            display: block;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
    <div class="dropdown">
        <button>Menu ▼</button>
        <div class="dropdown-content">
            <a href="profile.php">Profile</a>
            <a href="logout_blogger.php">Logout</a>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container">
    <a href="create_post.php" class="add-post">Create New Post</a>
    <h2>Your Posts</h2>

    <div class="post-grid">
        <?php
        if (count($posts) > 0) {
            foreach ($posts as $post) {
                echo '<div class="post">';
                echo '<h3 class="post-title">' . htmlspecialchars($post['title']) . '</h3>';
                echo '<p class="post-content">' . nl2br(htmlspecialchars($post['content'])) . '</p>';
                
                // Display tags if available
                if (!empty($post['tags'])) {
                    echo '<p><strong>Tags:</strong> ' . htmlspecialchars($post['tags']) . '</p>';
                }

                // Post footer with edit and delete buttons
                echo '<div class="post-footer">';
                echo '<span>Posted on ' . $post['created_at'] . '</span>';
                echo '<div class="btn-container">';
                echo '<a href="edit_post.php?post_id=' . $post['post_id'] . '" class="btn edit">Edit</a>';
                echo '<a href="delete_post.php?post_id=' . $post['post_id'] . '" class="btn delete">Delete</a>';
                echo '</div>';
                echo '</div>';

                // Link to view the full post
                echo '<a href="view_post.php?id=' . $post['post_id'] . '" class="btn view-more">View More</a>';
                echo '</div>';
            }
        } else {
            echo '<p>No posts available.</p>';
        }
        ?>
    </div>
</div>

</body>
</html>
