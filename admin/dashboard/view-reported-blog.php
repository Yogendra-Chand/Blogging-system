<!-- Admin can view blogs reported by users -->
<?php
include 'connection.php';

$post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
$reason = isset($_GET['reason']) ? $_GET['reason'] : '';

$sql = "SELECT p.*, u.username 
        FROM posts p 
        INNER JOIN users u ON p.user_id = u.user_id
        WHERE p.post_id = $post_id";

$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
?>

<div class="container mt-5">
    <h2><?= htmlspecialchars($row['title']) ?></h2>

    <p><strong>Author:</strong> <?= htmlspecialchars($row['username']) ?></p>

    <p><strong>Reported Reason:</strong> 
        <span class="text-danger"><?= htmlspecialchars($reason) ?></span>
    </p>

    <hr>

  <div><?= $row['content'] ?></div>

    <a href="admin-reports.php" class="btn btn-secondary">Back</a>
</div>