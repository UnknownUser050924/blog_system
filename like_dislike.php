<?php
include('db.php');
session_start();

if (!isset($_SESSION['user_id'])) { die('Unauthorized'); }

$user_id = $_SESSION['user_id'];
$post_id = intval($_POST['post_id']);
$type = $_POST['type'];

// Remove existing reaction
$conn->query("DELETE FROM post_likes WHERE post_id = $post_id AND user_id = $user_id");

// Insert new reaction
$conn->query("INSERT INTO post_likes (post_id, user_id, type) VALUES ($post_id, $user_id, '$type')");
?>
