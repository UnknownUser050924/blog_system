<?php
include('db.php');
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to add a comment.']);
    exit;
}

// Check if request is JSON
$inputData = json_decode(file_get_contents("php://input"), true);

// Get data from either JSON or POST
$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : ($inputData['post_id'] ?? 0);
$content = isset($_POST['content']) ? trim($_POST['content']) : ($inputData['content'] ?? '');
$user_id = intval($_SESSION['user_id']);

// Validate input
if ($post_id <= 0 || empty($content)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request or empty comment.']);
    exit;
}

// Check if post_id exists
$check_post = $conn->prepare("SELECT post_id FROM posts WHERE post_id = ?");
$check_post->bind_param("i", $post_id);
$check_post->execute();
$check_post->store_result();

if ($check_post->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid post ID.']);
    exit;
}
$check_post->close();

// Insert comment into database
$query = "INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($query);
$stmt->bind_param('iis', $post_id, $user_id, $content);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Comment added successfully!', 'comment_id' => $stmt->insert_id]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error adding comment: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
