<?php
// view_post.php
include('db.php'); // Assuming db.php connects to the database
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die('You must be logged in to view this post.');
}

// Check if 'id' is set in the URL and is a valid number
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid post ID');
}

$post_id = $_GET['id'];
$user_id = $_SESSION['user_id']; // User ID from the session

// Increment view count (using a prepared statement)
$query = "UPDATE posts SET views = views + 1 WHERE post_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $post_id); // 'i' for integer
if (!$stmt->execute()) {
    die('Error updating views: ' . $stmt->error);
}

// Fetch the post details (using a prepared statement)
$query = "SELECT posts.*, users.username FROM posts 
          JOIN users ON posts.user_id = users.user_id 
          WHERE post_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $post_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if post exists
if ($result->num_rows == 0) {
    die('Post not found');
}

$post = $result->fetch_assoc();

// Fetch comments for the post (using a prepared statement)
$comment_query = "SELECT comments.*, users.username FROM comments 
                  JOIN users ON comments.user_id = users.user_id
                  WHERE post_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($comment_query);
$stmt->bind_param('i', $post_id);
$stmt->execute();
$comments_result = $stmt->get_result();

// Log the activity (using a prepared statement)
$action = "User viewed post ID: $post_id";
$log_query = "INSERT INTO activity_log (user_id, action) VALUES (?, ?)";
$stmt = $conn->prepare($log_query);
$stmt->bind_param('is', $user_id, $action);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?></title>
    <style>
        /* General Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }

        .container {
            max-width: 700px;
            width: 100%;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Post Header */
        .post-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .post-title {
            font-size: 26px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .post-meta {
            font-size: 14px;
            color: #777;
        }

        /* Post Content */
        .post-content {
            font-size: 16px;
            line-height: 1.6;
            color: #444;
            padding: 15px 0;
            border-bottom: 1px solid #ddd;
        }

        /* Comments Section */
        .comments-section {
            margin-top: 20px;
        }

        .comments-section h2 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            border-left: 4px solid #007bff;
            padding-left: 10px;
            color: #007bff;
        }

        .comment-box {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .comment-box p {
            font-size: 14px;
            margin: 0;
            color: #444;
        }

        .comment-meta {
            font-size: 12px;
            color: #777;
            margin-top: 5px;
        }

        /* Comment Form */
        .comment-form textarea {
            width: 100%;
            height: 100px;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            font-size: 14px;
            resize: none;
            outline: none;
        }

        .comment-form button {
            display: block;
            width: 100%;
            background: #007bff;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        .comment-form button:hover {
            background: #0056b3;
        }

        /* Back Button */
        .btn-back {
            display: block;
            width: 100%;
            text-align: center;
            padding: 10px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
            font-size: 16px;
            transition: 0.3s;
        }

        .btn-back:hover {
            background: #5a6268;
        }

    </style>
</head>
<body>

<div class="container">
    <!-- Post Header -->
    <div class="post-header">
        <div class="post-title"><?php echo htmlspecialchars($post['title']); ?></div>
        <div class="post-meta">
            Posted by: <strong><?php echo htmlspecialchars($post['username']); ?></strong> | 
            Category: <?php echo htmlspecialchars($post['category']); ?> | 
            Views: <?php echo $post['views']; ?>
        </div>
    </div>

    <!-- Post Content -->
    <div class="post-content">
        <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
    </div>

    <!-- Comments Section -->
    <div class="comments-section">
        <h2>Comments</h2>

        <?php
        if ($comments_result->num_rows > 0) {
            while ($comment = $comments_result->fetch_assoc()) {
                // Format the date for better readability
                $formatted_date = date("F j, Y, g:i a", strtotime($comment['created_at']));
                echo '<div class="comment-box">';
                echo '<p><strong>' . htmlspecialchars($comment['username']) . ':</strong> ' . nl2br(htmlspecialchars($comment['comment'])) . '</p>';
                echo '<p class="comment-meta">' . $formatted_date . '</p>';
                echo '</div>';
            }
        } else {
            echo '<p>No comments yet. Be the first to comment!</p>';
        }
        ?>

        <!-- Comment Form -->
        <form class="comment-form" action="add_comment.php" method="post">
            <textarea name="comment" placeholder="Write a comment..." required></textarea>
            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
            <button type="submit">Submit Comment</button>
        </form>
    </div>

    <!-- Back Button -->
    <a href="blogger_dashboard.php" class="btn-back">Back to Dashboard</a>
</div>

</body>
</html>
