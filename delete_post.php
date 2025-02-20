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
    $sql_post = "SELECT title FROM posts WHERE post_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql_post);
    mysqli_stmt_bind_param($stmt, "ii", $post_id, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result_post = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result_post) > 0) {
        $post = mysqli_fetch_assoc($result_post);
        $post_title = $post['title'];

        // Delete related records in dependent tables (e.g., post_likes, comments, etc.)
        $deleteLikes = "DELETE FROM post_likes WHERE post_id = ?";
        $stmt = mysqli_prepare($conn, $deleteLikes);
        mysqli_stmt_bind_param($stmt, "i", $post_id);
        mysqli_stmt_execute($stmt);

        $deleteComments = "DELETE FROM comments WHERE post_id = ?";
        $stmt = mysqli_prepare($conn, $deleteComments);
        mysqli_stmt_bind_param($stmt, "i", $post_id);
        mysqli_stmt_execute($stmt);

        // Now delete the post
        $deletePost = "DELETE FROM posts WHERE post_id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $deletePost);
        mysqli_stmt_bind_param($stmt, "ii", $post_id, $_SESSION['user_id']);
        if (mysqli_stmt_execute($stmt)) {
            // Log the action after the post is successfully deleted
            $action = "User deleted the post with title: $post_title";
            $log_sql = "INSERT INTO activity_log (user_id, action) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $log_sql);
            mysqli_stmt_bind_param($stmt, "is", $_SESSION['user_id'], $action);
            mysqli_stmt_execute($stmt);

            // Redirect to the blogger dashboard
            header('Location: blogger_dashboard.php');
            exit;
        } else {
            echo "Error deleting post: " . mysqli_error($conn);
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
