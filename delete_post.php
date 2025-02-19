<?php
// Include the database connection
include('db.php');
session_start();

// Check if the user is logged in and is a blogger
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'blogger') {
    header('Location: login.php');
    exit;
}

// Handle the post deletion
if (isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];

    // Fetch the post title before deletion
    $sql_post = "SELECT title FROM posts WHERE post_id = '$post_id' AND user_id = '{$_SESSION['user_id']}'";
    $result_post = mysqli_query($conn, $sql_post);
    
    if (mysqli_num_rows($result_post) > 0) {
        $post = mysqli_fetch_assoc($result_post);
        $post_title = $post['title'];

        // Delete the post from the database
        $sql = "DELETE FROM posts WHERE post_id = '$post_id' AND user_id = '{$_SESSION['user_id']}'";
        if (mysqli_query($conn, $sql)) {
            // Log the action after the post is successfully deleted
            $action = "User deleted the post with title: $post_title";
            $log_sql = "INSERT INTO activity_log (user_id, action) VALUES ('{$_SESSION['user_id']}', '$action')";
            mysqli_query($conn, $log_sql);

            // Redirect to the blogger dashboard
            header('Location: blogger_dashboard.php');
            exit;
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "Post not found or you do not have permission to delete this post.";
        exit;
    }
} else {
    echo "Post not found.";
    exit;
}
?>
