<?php
include('db.php'); // Database connection
session_start();

if (!isset($_SESSION['user_id'])) {
    die('You must be logged in to view this post.');
}

$user_id = $_SESSION['user_id']; // Logged-in user ID

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid post ID.');
}

$post_id = intval($_GET['id']);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

// Fetch post details
$post_query = "SELECT posts.*, users.username FROM posts 
               JOIN users ON posts.user_id = users.user_id 
               WHERE post_id = ?";
$post_stmt = $conn->prepare($post_query);
$post_stmt->bind_param('i', $post_id);
$post_stmt->execute();
$post_result = $post_stmt->get_result();

if ($post_result->num_rows == 0) {
    die('Post not found.');
}

$post = $post_result->fetch_assoc();
$post_stmt->close();

// Check post visibility
if ($post['visibility'] == 'Private' && $post['user_id'] != $user_id) {
    die('This post is private.');
} elseif ($post['visibility'] == 'Friends-Only') {
    $friend_query = "SELECT * FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)";
    $friend_stmt = $conn->prepare($friend_query);
    $friend_stmt->bind_param('iiii', $post['user_id'], $user_id, $user_id, $post['user_id']);
    $friend_stmt->execute();
    $friend_result = $friend_stmt->get_result();
    $friend_stmt->close();

    if ($friend_result->num_rows == 0 && $post['user_id'] != $user_id) {
        die('This post is visible to friends only.');
    }
}

// Prevent multiple view counts
if (!isset($_SESSION["viewed_posts"]) || !in_array($post_id, $_SESSION["viewed_posts"])) {
    $update_view_query = "UPDATE posts SET views = views + 1 WHERE post_id = ?";
    $update_stmt = $conn->prepare($update_view_query);
    $update_stmt->bind_param('i', $post_id);
    $update_stmt->execute();
    $update_stmt->close();

    $_SESSION["viewed_posts"][] = $post_id;
}

// Fetch like & dislike counts
$like_query = "SELECT COUNT(*) AS total_likes FROM post_likes WHERE post_id = ? AND type = 'like'";
$like_stmt = $conn->prepare($like_query);
$like_stmt->bind_param('i', $post_id);
$like_stmt->execute();
$like_result = $like_stmt->get_result()->fetch_assoc();
$total_likes = $like_result['total_likes'];
$like_stmt->close();

$dislike_query = "SELECT COUNT(*) AS total_dislikes FROM post_likes WHERE post_id = ? AND type = 'dislike'";
$dislike_stmt = $conn->prepare($dislike_query);
$dislike_stmt->bind_param('i', $post_id);
$dislike_stmt->execute();
$dislike_result = $dislike_stmt->get_result()->fetch_assoc();
$total_dislikes = $dislike_result['total_dislikes'];
$dislike_stmt->close();

// Check if user already liked or disliked
$check_query = "SELECT type FROM post_likes WHERE post_id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param('ii', $post_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result()->fetch_assoc();
$user_reaction = $check_result['type'] ?? null;
$check_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            width: 90%;
            max-width: 750px;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: left;
        }
        h2 {
            color: #333;
            margin-bottom: 10px;
        }
        .post-info {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }
        .post-content {
            margin-bottom: 20px;
            font-size: 16px;
            line-height: 1.6;
            color: #444;
        }
        .comments-section {
            margin-top: 20px;
        }
        .comment-box {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: none;
        }
        .submit-btn {
            margin-top: 10px;
            padding: 8px 12px;
            border: none;
            background: #007BFF;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        .submit-btn:hover {
            background: #0056b3;
        }
        .comment {
            background: #f9f9f9;
            padding: 12px;
            margin-top: 10px;
            border-radius: 6px;
            border-left: 5px solid #007BFF;
        }
        .dashboard-btn {
            display: inline-block;
            text-align: center;
            padding: 10px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .dashboard-btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>

<div class="container">
    <h2><?= htmlspecialchars($post['title']) ?></h2>
    <p class="post-info">
        <strong>Author:</strong> <?= htmlspecialchars($post['username']) ?> | 
        <strong>Category:</strong> <?= htmlspecialchars($post['category']) ?> | 
        <strong>Views:</strong> <?= $post['views'] ?>
    </p>
    <p class="post-content"><?= nl2br(htmlspecialchars($post['content'])) ?></p>

    <!-- Comments Section -->
    <div class="comments-section">
        <h3>Comments</h3>
        <textarea id="comment" class="comment-box" placeholder="Write a comment..."></textarea>
        <button class="submit-btn" onclick="submitComment(<?= $post['post_id'] ?>)">Post Comment</button>
        <div id="commentsList">
            <!-- Load existing comments -->
            <?php
            $comments_query = "SELECT c.content, c.created_at, u.username 
                               FROM comments c 
                               JOIN users u ON c.user_id = u.user_id 
                               WHERE c.post_id = ? ORDER BY c.created_at DESC";
            $stmt = $conn->prepare($comments_query);
            $stmt->bind_param('i', $post['post_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($comment = $result->fetch_assoc()) {
                echo '<div class="comment">';
                echo '<strong>' . htmlspecialchars($comment['username']) . '</strong> (' . $comment['created_at'] . ')<br>';
                echo htmlspecialchars($comment['content']);
                echo '</div>';
            }

            $stmt->close();
            ?>
        </div>
    </div>

    <a href="blogger_dashboard.php" class="dashboard-btn">Back to Dashboard</a>
</div>

<script>
function submitComment(postId) {
    let commentText = document.getElementById("comment").value.trim();
    if (commentText === '') {
        alert("Comment cannot be empty!");
        return;
    }

    fetch('add_comment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `post_id=${postId}&content=${encodeURIComponent(commentText)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            // Append new comment to the comments list
            let commentElement = document.createElement("div");
            commentElement.classList.add("comment");
            commentElement.innerHTML = `<strong>You</strong> (just now)<br>${commentText}`;
            document.getElementById("commentsList").prepend(commentElement);
            document.getElementById("comment").value = "";
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error("Error:", error));
}
</script>

</body>
</html>
