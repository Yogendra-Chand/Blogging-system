<!-- sends notification to the blog author about the report -->
<?php
require 'connection.php';
session_start();

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
$triggered_by = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

if ($user_id == 0 || $post_id == 0) {
    echo "Invalid request.";
    exit;
}

if (isset($_POST['submit'])) {

    $type = 'blog_reported'; // This will be  Action

    $insert = "INSERT INTO notifications (user_id, type, post_id, triggered_by_user_id, created_at, is_read)
               VALUES ($user_id, '$type', $post_id, $triggered_by, NOW(), 0)";

    if(mysqli_query($conn, $insert)){
        header('Location: admin-reports.php?success=1');
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Notify Author</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3>Send Notification to Author</h3>

    <form method="POST">
        <p class="text-muted">
            The author will be notified that their blog has been reported and must review it.
        </p>

        <button type="submit" name="submit" class="btn btn-primary">
            Send Notification
        </button>
        <a href="admin-reports.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>