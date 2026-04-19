<!-- Admin can read blog posts -->
<?php
include 'connection.php';

if(!isset($_GET['id'])){
    echo "No blog selected.";
    exit;
}

$post_id = (int) $_GET['id'];

$sql = "SELECT p.*, u.username 
        FROM posts p 
        INNER JOIN users u ON p.user_id = u.user_id
        WHERE p.post_id = $post_id";

$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) == 0){
    echo "Blog not found.";
    exit;
}

$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Blog</title>
    <link href="bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2><?= htmlspecialchars($row['title']) ?></h2>
    <p><strong>Author:</strong> <?= htmlspecialchars($row['username']) ?></p>
    <p><strong>Date:</strong> <?= $row['created_at'] ?></p>

    <hr>

    <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>

    <a href="admin/dashboard/admin-reports.php" class="btn btn-secondary mt-3">Back</a>
</div>

</body>
</html>