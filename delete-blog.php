<!-- lets user delete their own blog post -->
<?php
session_start();
include 'connection.php';

//Login check
if (!isset($_SESSION['user_id'])) {
    die("Login required");
}

$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true;

//post id check
if (!isset($_GET['id'])) {
    die("Invalid request");
}

$post_id = (int)$_GET['id'];

//fetch post
$sql = "SELECT user_id, blog_image FROM posts WHERE post_id = $post_id";
$result = mysqli_query($conn, $sql);
$post = mysqli_fetch_assoc($result);

if (!$post) {
    die("Post not found");
}

//permission check
/*
Author → can delete own post
Admin  → can delete any post
*/
if ($post['user_id'] != $user_id && !$is_admin) {
    die("Unauthorized access");
}
//delete image
if (!empty($post['blog_image'])) {
    $imagePath = "uploads/" . $post['blog_image'];
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
}

// delete related data
mysqli_query($conn, "DELETE FROM comments WHERE post_id = $post_id");
mysqli_query($conn, "DELETE FROM likes WHERE post_id = $post_id");
mysqli_query($conn, "DELETE FROM reports WHERE post_id = $post_id");
mysqli_query($conn, "DELETE FROM notifications WHERE post_id = $post_id");

// delete post
mysqli_query($conn, "DELETE FROM posts WHERE post_id = $post_id");

//redirect
if ($is_admin) {
    header("Location: admin-reports.php");
} else {
    header("Location: index.php");
}
exit;
