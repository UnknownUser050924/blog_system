<?php
include('db.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$friend_id = $_POST['friend_id'] ?? null;

if ($friend_id && $friend_id != $user_id) {
    $check_query = "SELECT * FROM friendships WHERE (user_id = $user_id AND friend_id = $friend_id) 
                    OR (user_id = $friend_id AND friend_id = $user_id)";
    $result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($result) == 0) {
        $insert_query = "INSERT INTO friendships (user_id, friend_id, status) VALUES ($user_id, $friend_id, 'pending')";
        mysqli_query($conn, $insert_query);
        echo "Friend request sent!";
    } else {
        echo "Friend request already exists!";
    }
} else {
    echo "Invalid request!";
}
?>
