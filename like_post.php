<?php
include('db.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['status' => 'error', 'message' => 'You must be logged in to like a post.']));
}

$post_id = intval($_POST['post_id']);
$user_id = $_SESSION['user_id'];

// Check if user already liked the post
$check_query = "SELECT * FROM post_likes WHERE post_id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param('ii', $post_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$check_stmt->close();

if ($check_result->num_rows > 0) {
    // User already liked the post, so remove like (unlike)
    $delete_query = "DELETE FROM post_likes WHERE post_id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param('ii', $post_id, $user_id);
    if ($delete_stmt->execute()) {
        echo json_encode(['status' => 'unliked']);
    }
    $delete_stmt->close();
} else {
    // User hasn't liked it yet, so insert a like
    $insert_query = "INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param('ii', $post_id, $user_id);
    if ($insert_stmt->execute()) {
        echo json_encode(['status' => 'liked']);
    }
    $insert_stmt->close();
}

$conn->close();
?>
