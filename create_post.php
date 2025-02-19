<?php
session_start();
include('db.php');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle post creation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category = $_POST['category'];
    $visibility = $_POST['visibility'];
    $tags = $_POST['tags'];

    if ($tags === 'Other') {
        $tags = trim($_POST['custom_tags']);
    }

    $tags = implode(',', array_map('trim', explode(',', $tags)));

    // Insert post into the database
    $query = "INSERT INTO posts (user_id, title, content, category, visibility, tags, created_at) 
              VALUES ('" . $_SESSION['user_id'] . "', '$title', '$content', '$category', '$visibility', '$tags', NOW())";
    mysqli_query($conn, $query);

    // Log the activity
    $action = "User created a post: $title";
    $log_query = "INSERT INTO activity_log (user_id, action) VALUES (" . $_SESSION['user_id'] . ", '$action')";
    mysqli_query($conn, $log_query);

    // Redirect to the dashboard
    header("Location: blogger_dashboard.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 700px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .back-links {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .form-control, .form-select {
            margin-bottom: 15px;
        }
        .btn-submit {
            width: 100%;
        }
        #custom-tag-div {
            display: none;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="back-links">
        <a href="blogger_dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
    </div>
    <h2>Create a New Post</h2>

    <form action="create_post.php" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" class="form-control" rows="5" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category" class="form-select" required>
                <option value="">-- Select Category --</option>
                <option value="Technology">Technology</option>
                <option value="Health">Health</option>
                <option value="Travel">Travel</option>
                <option value="Education">Education</option>
                <option value="Food">Food</option>
                <option value="Finance">Finance</option>
                <option value="Entertainment">Entertainment</option>
                <option value="Lifestyle">Lifestyle</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Tags</label>
            <select name="tags" id="tags" class="form-select" onchange="toggleCustomTags()">
                <option value="">-- Select Tag --</option>
                <option value="Web Development">Web Development</option>
                <option value="PHP">PHP</option>
                <option value="JavaScript">JavaScript</option>
                <option value="HTML">HTML</option>
                <option value="CSS">CSS</option>
                <option value="Laravel">Laravel</option>
                <option value="React">React</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div class="mb-3" id="custom-tag-div">
            <label class="form-label">Custom Tag</label>
            <input type="text" name="custom_tags" class="form-control" placeholder="Enter your custom tag">
        </div>

        <div class="mb-3">
            <label class="form-label">Visibility</label>
            <select name="visibility" class="form-select">
                <option value="Public">Public</option>
                <option value="Private">Private</option>
                <option value="Friends-Only">Friends-Only</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary btn-submit">Create Post</button>
    </form>
</div>

<script>
// Toggle the visibility of the custom tag textbox
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
