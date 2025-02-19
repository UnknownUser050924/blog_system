<?php
// Include the database connection
include('db.php');
session_start();

// Fetch all posts
$sql = "SELECT * FROM posts ORDER BY created_at DESC"; // Fetching all posts
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Platform</title>
    <style>
        /* Basic styles for the posts */
        .post {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }
        .post-title {
            font-size: 1.5em;
            margin-bottom: 10px;
        }
        .post-content {
            font-size: 1.1em;
        }
        .post-footer {
            margin-top: 10px;
        }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }
        .btn.disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <h1>Welcome to the Guest Platform</h1>

    <?php
    if (mysqli_num_rows($result) > 0) {
        while ($post = mysqli_fetch_assoc($result)) {
            // Display each post
            echo '<div class="post">';
            echo '<h2 class="post-title">' . htmlspecialchars($post['title']) . '</h2>';
            echo '<p class="post-content">' . nl2br(htmlspecialchars($post['content'])) . '</p>';
            echo '<div class="post-footer">';
            echo '<span>Posted on ' . $post['created_at'] . '</span>';

            // Check if the user is logged in before allowing interactions
            if (isset($_SESSION['user_id'])) {
                // Logged-in users can like and comment
                echo '<br><a href="like_post.php?post_id=' . $post['post_id'] . '" class="btn">Like</a>';
                echo '<a href="comment_post.php?post_id=' . $post['post_id'] . '" class="btn">Comment</a>';
            } else {
                // Guests are prompted to log in
                echo '<br><a href="login.php" class="btn disabled">Like</a>';
                echo '<a href="login.php" class="btn disabled">Comment</a>';
            }

            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<p>No posts available.</p>';
    }
    ?>

    <br>
    <p><a href="login.php" class="btn">Back to Login</a></p>
</body>
</html>
