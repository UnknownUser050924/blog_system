<?php
// add_comment.php
include('db.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id']; // Get logged-in user ID
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    // Insert comment into the database
    $query = "INSERT INTO comments (post_id, user_id, comment, created_at) VALUES ($post_id, $user_id, '$comment', NOW())";
    mysqli_query($conn, $query);

    // Log the activity
    $action = "User commented on post ID: $post_id";
    $log_query = "INSERT INTO activity_log (user_id, action) VALUES ($user_id, '$action')";
    mysqli_query($conn, $log_query);

    // Redirect back to the post
    header("Location: view_post.php?id=$post_id");
}
?>
