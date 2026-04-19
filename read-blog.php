<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id'])) die("Please login first.");
$user_id = (int) $_SESSION['user_id'];

if (!isset($_GET['id'])) die("Invalid blog ID.");
$getBlogId = (int) $_GET['id'];

//STACK RECENTLY VIEWED BLOGS 

if (!isset($_SESSION['recently_viewed'])) {
    $_SESSION['recently_viewed'] = [];
}

// Remove if already exists 
$_SESSION['recently_viewed'] = array_filter(
    $_SESSION['recently_viewed'],
    fn($item) => $item['post_id'] !== $getBlogId
);

// Push current blog to top of stack
array_unshift($_SESSION['recently_viewed'], [
    'post_id' => $getBlogId,
    'title'   => '' 
]);

// Keep only last 5 (stack limit)
$_SESSION['recently_viewed'] = array_slice($_SESSION['recently_viewed'], 0, 5);


// FETCH BLOG 
$fetchSql = "SELECT p.*, u.username, c.category_name 
             FROM posts p 
             INNER JOIN users u ON p.user_id = u.user_id 
             INNER JOIN categories c ON p.category_id = c.category_id
             WHERE p.post_id = $getBlogId";
$fetchResult = mysqli_query($conn, $fetchSql);
if (!$fetchResult || mysqli_num_rows($fetchResult) === 0) die("Blog post not found.");
$blogData = mysqli_fetch_assoc($fetchResult);
// Now update the title in stack
foreach ($_SESSION['recently_viewed'] as &$item) {
    if ($item['post_id'] === $getBlogId) {
        $item['title'] = $blogData['title'];
        break;
    }
}
unset($item);

//like logic
if (isset($_POST['like'])) {
    $checkLike = "SELECT * FROM likes WHERE post_id = $getBlogId AND user_id = $user_id";
    $checkResult = mysqli_query($conn, $checkLike);
    if (mysqli_num_rows($checkResult) > 0) {
        mysqli_query($conn, "DELETE FROM likes WHERE post_id = $getBlogId AND user_id = $user_id");
    } else {
        mysqli_query($conn, "INSERT INTO likes (post_id, user_id) VALUES ($getBlogId, $user_id)");
    }
    header("Location: read-blog.php?id=$getBlogId");
    exit;
}

//report logic
if (isset($_POST['report'])) {
    if ($blogData['user_id'] == $user_id) {
        $_SESSION['report_error'] = "You cannot report your own blog.";
        header("Location: read-blog.php?id=$getBlogId");
        exit;
    }

    $checkReport = mysqli_query($conn, "SELECT report_id FROM reports WHERE post_id = $getBlogId AND reported_by = $user_id");
    if (mysqli_num_rows($checkReport) == 0) {
        mysqli_query($conn, "INSERT INTO reports (post_id, reported_by, reason, status) 
                             VALUES ($getBlogId, $user_id, 'Inappropriate content', 'pending')");
        $_SESSION['report_success'] = "Blog reported successfully.";
    } else {
        $_SESSION['report_error'] = "You already reported this blog.";
    }

    header("Location: read-blog.php?id=$getBlogId");
    exit;
}

// comment logic
$error = [];
if (isset($_POST['submit'])) {
    if (!empty(trim($_POST['comment-text']))) {
        $comment = mysqli_real_escape_string($conn, $_POST['comment-text']);
        mysqli_query($conn, "INSERT INTO comments (post_id, user_id, comment_text) VALUES ($getBlogId, $user_id, '$comment')");
        header("Location: read-blog.php?id=$getBlogId");
        exit;
    } else {
        $error['commentError'] = "Comment field is required!";
    }
}

//fetch like count and user status
$likeCountResult = mysqli_query($conn, "SELECT COUNT(*) AS total_likes FROM likes WHERE post_id = $getBlogId");
$likeCount = mysqli_fetch_assoc($likeCountResult)['total_likes'];
$userLikedResult = mysqli_query($conn, "SELECT * FROM likes WHERE post_id = $getBlogId AND user_id = $user_id");
$userLiked = mysqli_num_rows($userLikedResult) > 0;

// fetch logged in user
$selectUser = "SELECT u.username, up.profile_picture 
               FROM users u 
               LEFT JOIN user_profiles up ON u.user_id = up.user_id
               WHERE u.user_id = $user_id";
$userData = mysqli_fetch_assoc(mysqli_query($conn, $selectUser));

//fetch comments
$selectCommentResult = mysqli_query($conn, "SELECT c.*, u.username, up.profile_picture
    FROM comments c
    INNER JOIN users u ON c.user_id = u.user_id
    LEFT JOIN user_profiles up ON u.user_id = up.user_id
    WHERE c.post_id = $getBlogId
    ORDER BY c.created_at DESC");
$commentCount = mysqli_num_rows($selectCommentResult);



include 'navbar.php';
?>

<link rel="stylesheet" href="bootstrap.min.css">

<div class="container mt-3">

    <!-- REPORT MESSAGES -->
    <?php if (isset($_SESSION['report_success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['report_success']; unset($_SESSION['report_success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['report_error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['report_error']; unset($_SESSION['report_error']); ?></div>
    <?php endif; ?>

    <div class="row mx-3">

        <!-- BLOG CONTENT -->
        <div class="col-12 col-lg-8 mt-4">
            <span class="fw-bold text-success h4">Author: <?= htmlspecialchars($blogData['username']) ?></span><br>
            <span class="fw-bold text-success h4">Category: <?= htmlspecialchars($blogData['category_name']) ?></span>

            <p class="h1 fw-bold mt-2"><?= htmlspecialchars($blogData['title']) ?></p>
            <p class="h4"><?= htmlspecialchars($blogData['excerpt']) ?></p>

            <img src="uploads/<?= htmlspecialchars($blogData['blog_image']) ?>" class="rounded mt-4 mb-3 w-100">

            <p class="h3"><?= nl2br(htmlspecialchars(strip_tags($blogData['content']))) ?></p>
     <?php if ((int)$blogData['user_id'] === (int)$user_id): ?>
    <div class="mt-4 p-3 border rounded bg-light">
        <h5 class="text-primary mb-3"> Blog Actions</h5>

        <a href="edit-blog.php?id=<?= $getBlogId ?>" class="btn btn-warning me-2">
             Edit Blog
        </a>

        <a href="delete-blog.php?id=<?= $getBlogId ?>"
           class="btn btn-danger"
           onclick="return confirm('Are you sure you want to delete this blog?');">
            🗑️ Delete Blog
        </a>
    </div>
<?php endif; ?>




            <!-- LIKE BUTTON -->
            <form method="post" class="mt-3">
                <button type="submit" name="like" class="btn <?= $userLiked ? 'btn-danger' : 'btn-outline-danger' ?>">
                    ❤️ Like (<?= $likeCount ?>)
                </button>
            </form>


            <!-- REPORT BUTTON -->
            <form method="post" class="mt-2">
                <button type="submit" name="report" class="btn btn-outline-warning">
                  🚩Report Blog
                </button>
            </form>
        </div>


<?php
$isAuthor = ($user_id == $blogData['user_id']);
$isAdmin  = (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1);
?>





        <!-- COMMENTS -->
        <div class="col-12 col-lg-4 shadow-lg rounded p-3">
            <p class="fw-bolder h3 mt-3"><?= ($commentCount <= 1) ? "Comment ($commentCount)" : "Comments ($commentCount)" ?></p>

            <!-- COMMENT FORM -->
            <div class="card shadow-sm p-2 mt-3">
                <div class="d-flex align-items-center gap-2">
                    <img src="uploads/<?= !empty($userData['profile_picture']) ? $userData['profile_picture'] : 'default.png'; ?>"
                         class="rounded-circle" style="width:32px;height:32px;">
                    <span class="fw-bold"><?= htmlspecialchars($userData['username']) ?></span>
                </div>

                <form method="post">
                    <textarea name="comment-text" rows="2" class="form-control mt-2"
                              placeholder="Write comment here!"></textarea>
                    <small class="text-danger"><?= isset($error['commentError']) ? $error['commentError'] : '' ?></small>
                    <input type="submit" name="submit" class="btn btn-success mt-2 fw-bold" value="Comment">
                </form>
            </div>

            <hr>

            <!-- COMMENT LIST -->
            <?php while ($commentData = mysqli_fetch_assoc($selectCommentResult)) { ?>
                <div class="mb-3">
                    <div class="d-flex gap-2 align-items-center">
                        <img src="uploads/<?= !empty($commentData['profile_picture']) ? $commentData['profile_picture'] : 'default.png'; ?>"
                             class="rounded-circle" style="width:32px;height:32px;">
                        <strong><?= htmlspecialchars($commentData['username']) ?></strong>
                    </div>
                    <small class="text-muted"><?= $commentData['created_at'] ?></small>
                    <p class="mt-1"><?= nl2br(htmlspecialchars($commentData['comment_text'])) ?></p>
                    <hr>
                </div>
            <?php } ?>
        </div>
    </div>
</div>