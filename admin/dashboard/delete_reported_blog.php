<!-- Admin deletes a reported blog post -->
<?php
session_start();
include 'connection.php';


if (!isset($_SESSION['user_id'])) {
    die("Login required");
}


if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die("Access denied");
}

if (!isset($_GET['post_id'])) {
    die("Invalid post id");
}

$post_id = (int)$_GET['post_id'];

// Fetch blog
$sql = "SELECT blog_image FROM posts WHERE post_id = $post_id";
$result = mysqli_query($conn, $sql);
$post = mysqli_fetch_assoc($result);

if (!$post) {
    die("Blog not found");
}

//Delete image 
if (!empty($post['blog_image'])) {
    $imagePath = "../uploads/" . $post['blog_image'];
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
}

// delete related data 
mysqli_query($conn, "DELETE FROM comments WHERE post_id = $post_id");
mysqli_query($conn, "DELETE FROM likes WHERE post_id = $post_id");
mysqli_query($conn, "DELETE FROM reports WHERE post_id = $post_id");

/* delete blog */
mysqli_query($conn, "DELETE FROM posts WHERE post_id = $post_id");

//  redirect back 
header("Location: admin-reports.php");
exit;
