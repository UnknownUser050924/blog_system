<?php
include('db.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the user's profile information
$sql = "SELECT * FROM users WHERE user_id = '$user_id' LIMIT 1";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

// Update profile information
if (isset($_POST['update_profile'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $state = $_POST['state'];
    $country = $_POST['country'];

    // Update the user's profile in the database
    $update_sql = "UPDATE users SET username = '$username', email = '$email', age = '$age', gender = '$gender', address = '$address', state = '$state', country = '$country' WHERE user_id = '$user_id'";

    if (mysqli_query($conn, $update_sql)) {
        echo "<script>alert('Profile updated successfully!'); window.location.href='profile.php';</script>";
        exit();
    } else {
        echo "Error updating profile: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .update-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .back-button {
            text-align: left;
            margin-bottom: 15px;
        }

        .back-button a {
            text-decoration: none;
            color: white;
            background: #007bff;
            padding: 8px 15px;
            border-radius: 5px;
            display: inline-block;
        }

        .back-button a:hover {
            background: #0056b3;
        }

        .form-group {
            text-align: left;
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        input, select, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }

        input:focus, select:focus, textarea:focus {
            border-color: #007bff;
            outline: none;
        }

        .profile-picture {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #007bff;
            margin-bottom: 15px;
        }

        .update-profile button {
            width: 100%;
            background: #28a745;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .update-profile button:hover {
            background: #218838;
        }
    </style>
</head>
<body>

<div class="update-container">
    <!-- Update Profile Header -->
    <h2>Update Profile</h2>

    <!-- Profile Update Form -->
    <form action="update_profile.php" method="POST">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <div class="form-group">
            <label for="age">Age:</label>
            <input type="number" name="age" id="age" value="<?php echo htmlspecialchars($user['age']); ?>" min="1">
        </div>

        <div class="form-group">
            <label for="gender">Gender:</label>
            <select name="gender" id="gender">
                <option value="male" <?php echo $user['gender'] == 'male' ? 'selected' : ''; ?>>Male</option>
                <option value="female" <?php echo $user['gender'] == 'female' ? 'selected' : ''; ?>>Female</option>
                <option value="other" <?php echo $user['gender'] == 'other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="address">Address:</label>
            <textarea name="address" id="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="state">State:</label>
            <input type="text" name="state" id="state" value="<?php echo htmlspecialchars($user['state']); ?>">
        </div>

        <div class="form-group">
            <label for="country">Country:</label>
            <input type="text" name="country" id="country" value="<?php echo htmlspecialchars($user['country']); ?>">
        </div>

        <div class="update-profile">
            <button type="submit" name="update_profile">Update Profile</button>
        </div>
        <div class="back-button">
        <a href="profile.php">Back to Profile</a>
    </div>

    </form>
</div>

</body>
</html>
