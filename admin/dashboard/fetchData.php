<?php
include '../../connection.php';

if (!isset($_GET['post_id'])) {
    die("Invalid request");
}

$post_id = (int) $_GET['post_id'];

//fetch blog details with author info
$sql = "SELECT p.*, u.username 
        FROM posts p
        INNER JOIN users u ON u.user_id = p.user_id
        WHERE p.post_id = $post_id";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Blog not found");
}

$data = mysqli_fetch_assoc($result);
?>

<div class="card mt-3">
    <div class="card-header">
        <strong><?= htmlspecialchars($data['title']) ?></strong>
    </div>
    <div class="card-body">
        <p><strong>Author:</strong> <?= htmlspecialchars($data['username']) ?></p>

        <?php if (!empty($data['blog_image'])): ?>
            <img src="../../uploads/<?= htmlspecialchars($data['blog_image']) ?>" 
                 class="img-fluid mb-3">
        <?php endif; ?>
<div class="mb-3">
    <h5>Excerpt</h5>
    <p class="text-muted">
        <?= htmlspecialchars($data['excerpt']); ?>
    </p>
</div>

<hr>

<div>
    <h5>Full Content</h5>
    <?= $data['content']; ?>
</div>

    </div>
</div>
