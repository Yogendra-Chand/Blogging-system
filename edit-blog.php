<!-- lets user edit their own blog posts -->
<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id'])) die("Login required");

$user_id = $_SESSION['user_id'];
$post_id = (int)$_GET['id'];

// Fetch existing post
$postResult = mysqli_query($conn, "SELECT * FROM posts WHERE post_id = $post_id");
$post = mysqli_fetch_assoc($postResult);

if (!$post) die("Post not found");

// Security: only author can edit
if ($post['user_id'] != $user_id) {
    die("Unauthorized access");
}

// Fetch categories
$categorySql = "SELECT * FROM categories ORDER BY category_name";
$categoryResult = mysqli_query($conn, $categorySql);

// UPDATE LOGIC
if (isset($_POST['update'])) {

    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $excerpt = mysqli_real_escape_string($conn, $_POST['excerpt']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    $imageSql = "";

    // IMAGE OPTIONAL
    if (!empty($_FILES['blog-image']['name'])) {

        $image = $_FILES['blog-image'];
        $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));

        if (in_array($ext, ['jpg','jpeg','png','webp','gif']) && $image['size'] < 5242880) {

            $image_final_name = "post-" . time() . ".jpg";
            $uploadDir = "uploads/";

            function compressImage($source, $destination, $quality = 75) {
                $info = getimagesize($source);
                if ($info['mime'] == 'image/jpeg') $img = imagecreatefromjpeg($source);
                elseif ($info['mime'] == 'image/png') $img = imagecreatefrompng($source);
                elseif ($info['mime'] == 'image/gif') $img = imagecreatefromgif($source);
                elseif ($info['mime'] == 'image/webp') $img = imagecreatefromwebp($source);
                else return false;

                imagejpeg($img, $destination, $quality);
                imagedestroy($img);
                return $destination;
            }

            compressImage($image['tmp_name'], $uploadDir . $image_final_name, 75);
            $imageSql = ", blog_image='$image_final_name'";
        }
    }

    mysqli_query($conn, "
        UPDATE posts 
        SET title='$title',
            excerpt='$excerpt',
            content='$content',
            category_id='$category'
            $imageSql
        WHERE post_id=$post_id
    ");

    header("Location: read-blog.php?id=$post_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<script src="https://cdn.tiny.cloud/1/bu8xy1qdhoi45svuu1wisy4ro8tg8k9uz6wkvopvsqu80oex/tinymce/7/tinymce.min.js"
referrerpolicy="origin"></script>

<script>
tinymce.init({
    selector: '#content'
});
</script>

<link rel="stylesheet" href="bootstrap.min.css">
<link rel="stylesheet" href="style.css">
<script src="bootstrap.bundle.min.js"></script>
</head>

<body>

<?php include 'navbar.php'; ?>

<div class="container">
<h3 class="text-center fw-bold">Edit Blog</h3>

<form method="post" enctype="multipart/form-data" class="shadow p-4 rounded">

<div class="form-group my-3">
<label class="form-label">Title</label>
<input type="text" name="title" class="form-control"
value="<?= htmlspecialchars($post['title']) ?>" required>
</div>

<div class="form-group">
<label class="form-label">Category</label>
<select name="category" class="form-select">
<?php while ($cat = mysqli_fetch_assoc($categoryResult)) { ?>
<option value="<?= $cat['category_id'] ?>"
<?= $cat['category_id'] == $post['category_id'] ? 'selected' : '' ?>>
<?= $cat['category_name'] ?>
</option>
<?php } ?>
</select>
</div>

<div class="form-group my-3">
<label class="form-label">Excerpt</label>
<input type="text" name="excerpt" class="form-control"
value="<?= htmlspecialchars($post['excerpt']) ?>" required>
</div>

<div class="form-group my-3">
<label class="form-label">Current Image</label><br>
<img src="uploads/<?= $post['blog_image'] ?>" width="150" class="rounded">
</div>

<div class="form-group my-3">
<label class="form-label">Change Image (optional)</label>
<input type="file" name="blog-image" class="form-control">
</div>

<div class="form-group my-3">
<label class="form-label">Content</label>
<textarea name="content" id="content" class="form-control" rows="10">
<?= htmlspecialchars($post['content']) ?>
</textarea>
</div>

<input type="submit" name="update"
value="Update Blog"
class="bg-success my-2 w-100 text-light h5 border-0 p-2 rounded">

</form>
</div>

</body>
</html>
