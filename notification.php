<!-- Notification page show the notification -->
<?php
session_start();
include 'connection.php';
include 'navbar.php';

$user_id = $_SESSION['user_id'];

// Mark notifications as read
mysqli_query($conn, "UPDATE notifications SET is_read=1 WHERE user_id='$user_id'");

// Fetch notifications along with the blog title
$sql = "SELECT n.*, p.title
        FROM notifications n
        LEFT JOIN posts p ON n.post_id = p.post_id
        WHERE n.user_id = $user_id
        ORDER BY n.created_at DESC";
$res = mysqli_query($conn, $sql);
?>

<div class="container mt-4">
    <h2>Your Notifications</h2>
    <?php while($row = mysqli_fetch_assoc($res)): ?>
        <div class="card mb-2 p-2">
            <p><strong>Blog:</strong> <?= htmlspecialchars($row['title'] ?? 'Unknown') ?></p>
            <p><strong>Action: Please Update or Delete this Blog </strong> 
        
            <p><strong>Date:</strong> <?= $row['created_at'] ?></p>

            <!--  View blog button -->
            <a href="read-blog.php?id=<?= $row['post_id'] ?>" class="btn btn-info btn-sm">View Blog</a>
        </div>
    <?php endwhile; ?>
</div>