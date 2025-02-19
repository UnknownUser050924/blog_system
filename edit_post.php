<?php
// edit_post.php
include('db.php');
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the post ID
$post_id = $_GET['post_id'] ?? null;
if ($post_id === null) {
    die("Post ID is required.");
}

// Fetch the post data from the database
$query = "SELECT * FROM posts WHERE post_id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $post_id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$post = mysqli_fetch_assoc($result);

if (!$post) {
    die("Post not found or you don't have permission to edit this post.");
}

// Handle post update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'];
    $visibility = $_POST['visibility'];
    $tags = $_POST['tags']; // Get the selected tags

    // If 'Other' is selected, handle the custom tag
    if ($tags === 'Other') {
        $tags = $_POST['custom_tags']; // Get the custom tags from the textbox
    }

    // Sanitize tags (remove spaces and ensure comma-separated format)
    $tags = implode(',', array_map('trim', explode(',', $tags)));

    // Update the post in the database
    $updateQuery = "UPDATE posts SET title = ?, content = ?, category = ?, visibility = ?, tags = ? WHERE post_id = ?";
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, "sssssi", $title, $content, $category, $visibility, $tags, $post_id);
    mysqli_stmt_execute($stmt);

    // Log the activity
    $action = "User updated post with title: $title";
    $log_query = "INSERT INTO activity_log (user_id, action) VALUES (" . $_SESSION['user_id'] . ", '$action')";
    mysqli_query($conn, $log_query);

    // Redirect to the dashboard
    header("Location: blogger_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 700px;
            margin-top: 50px;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-label {
            font-weight: 600;
        }
        .btn-primary {
            width: 100%;
        }
    </style>
</head>
<body>

<div class="container">
    <h3 class="mb-3 text-center">Edit Your Post</h3>
    
    <form action="edit_post.php?post_id=<?= htmlspecialchars($post['post_id']) ?>" method="post">
        
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea class="form-control" name="content" rows="5" required><?= htmlspecialchars($post['content']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Category</label>
            <select class="form-select" name="category" required>
                <option value="Technology" <?= $post['category'] === 'Technology' ? 'selected' : '' ?>>Technology</option>
                <option value="Health" <?= $post['category'] === 'Health' ? 'selected' : '' ?>>Health</option>
                <option value="Travel" <?= $post['category'] === 'Travel' ? 'selected' : '' ?>>Travel</option>
                <option value="Education" <?= $post['category'] === 'Education' ? 'selected' : '' ?>>Education</option>
                <option value="Food" <?= $post['category'] === 'Food' ? 'selected' : '' ?>>Food</option>
                <option value="Finance" <?= $post['category'] === 'Finance' ? 'selected' : '' ?>>Finance</option>
                <option value="Entertainment" <?= $post['category'] === 'Entertainment' ? 'selected' : '' ?>>Entertainment</option>
                <option value="Lifestyle" <?= $post['category'] === 'Lifestyle' ? 'selected' : '' ?>>Lifestyle</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Tags</label>
            <select class="form-select" name="tags" id="tags" onchange="toggleCustomTags()">
                <option value="">-- Select Tag --</option>
                <option value="Web Development" <?= in_array('Web Development', explode(',', $post['tags'])) ? 'selected' : '' ?>>Web Development</option>
                <option value="PHP" <?= in_array('PHP', explode(',', $post['tags'])) ? 'selected' : '' ?>>PHP</option>
                <option value="JavaScript" <?= in_array('JavaScript', explode(',', $post['tags'])) ? 'selected' : '' ?>>JavaScript</option>
                <option value="HTML" <?= in_array('HTML', explode(',', $post['tags'])) ? 'selected' : '' ?>>HTML</option>
                <option value="CSS" <?= in_array('CSS', explode(',', $post['tags'])) ? 'selected' : '' ?>>CSS</option>
                <option value="Laravel" <?= in_array('Laravel', explode(',', $post['tags'])) ? 'selected' : '' ?>>Laravel</option>
                <option value="React" <?= in_array('React', explode(',', $post['tags'])) ? 'selected' : '' ?>>React</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div id="custom-tag-div" class="mb-3" style="display:none;">
            <label class="form-label">Custom Tag</label>
            <input type="text" class="form-control" name="custom_tags" placeholder="Enter your custom tag">
        </div>

        <div class="mb-3">
            <label class="form-label">Visibility</label>
            <select class="form-select" name="visibility">
                <option value="Public" <?= $post['visibility'] === 'Public' ? 'selected' : '' ?>>Public</option>
                <option value="Private" <?= $post['visibility'] === 'Private' ? 'selected' : '' ?>>Private</option>
                <option value="Friends-Only" <?= $post['visibility'] === 'Friends-Only' ? 'selected' : '' ?>>Friends-Only</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update Post</button>
    </form>

    <a href="blogger_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>

<script>
function toggleCustomTags() {
    var tagsSelect = document.getElementById("tags");
    var customTagDiv = document.getElementById("custom-tag-div");
    if (tagsSelect.value === "Other") {
        customTagDiv.style.display = "block";
    } else {
        customTagDiv.style.display = "none";
    }
}
</script>

</body>
</html>
