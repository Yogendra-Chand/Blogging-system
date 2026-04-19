<?php
include 'connection.php';

if (isset($_POST['post'])) {

  session_start();

  $user_id = $_SESSION['user_id'];
  $postTitle = mysqli_real_escape_string($conn, $_POST['title']);
  $excerpt = mysqli_real_escape_string($conn, $_POST['excerpt']);
  $content = mysqli_real_escape_string($conn, $_POST['content']);
  $category = mysqli_real_escape_string($conn, $_POST['category']);

$image = $_FILES['blog-image'];
$image_name = $image['name'];
$image_size = $image['size'];

if (!empty($image_name)) {

    $ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

    if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {

        if ($image_size > 0 && $image_size < 5242880) { 
           
            $image_final_name = "post-" . time() . ".jpg";
            $uploadDir = "uploads/";

            // GD compression function
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

            $compressedPath = compressImage($image['tmp_name'], $uploadDir . $image_final_name, 75);

            if ($compressedPath) {
                // Insert post into database with compressed image
                $insertSql = "INSERT INTO posts (user_id, title, category_id, blog_image, excerpt, content)
                              VALUES ('$user_id','$postTitle', '$category','$image_final_name','$excerpt','$content')";
                $insertResult = mysqli_query($conn, $insertSql);

                if ($insertResult) {
                    echo "<div class='alert alert-success alert-dismissible fade show container' role='alert'>
                            <strong>Blog posted successfully!</strong>
                          </div>
                          <meta http-equiv='refresh' content='2'>";
                } else {
                    echo "<div class='alert alert-danger'>Failed to save blog post!</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Image compression failed!</div>";
            }

        } else {
            echo "<div class='alert alert-danger'>File too large! Max 5MB allowed.</div>";
        }

    } else {
        echo "<div class='alert alert-danger'>Invalid file type! Only jpg, png, webp, gif allowed.</div>";
    }

} else {
    echo "<div class='alert alert-danger'>Please select an image!</div>";
}

    ?>
    <!-- <div class="container alert alert-danger alert-dismissible fade show" role="alert">
      <strong>Image is required!</strong>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div> -->
    <?php
  }




$categorySql = "SELECT * FROM categories ORDER BY category_name";
$categoryResult = mysqli_query($conn, $categorySql);
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
<!-- Wrriting  blog   content-->
  <div class="container">
    <h3 class="text-center fw-bold">Write Blog</h3>

    <form action="#" method="post" enctype="multipart/form-data" class="shadow p-4 rounded">
      <div class="form-group my-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" name="title" class="form-control" id="title">
      </div>

      <div class="form-group">
        <label for="category" class="form-label">Category</label>
        <select name="category" id="" class="form-select">
          <option value="<?php echo $d; ?>">Select Category</option>
          <?php
          while ($category = mysqli_fetch_assoc($categoryResult)) {
            ?>
            <option value="<?php echo $category['category_id']; ?>"><?php echo $category['category_name']; ?></option>
            <?php
          }
          ?>
        </select>
      </div>

      <div class="form-group my-3">
        <label for="excerpt" class="form-label">Excerpt</label>
        <input type="text" name="excerpt" class="form-control" id="excerpt" required>
      </div>

      <div class="form-group my-3">
        <label for="blog-image" class="form-label">Image</label>
        <input type="file" name="blog-image" id="blog-image" class="form-control">
      </div>

      <div class="form-group">
        <label for="content" class="form-label">Content</label>
        <textarea name="content" id="content" class="form-control" rows="10"></textarea>
      </div>

      <input type="submit" name="post" value="Post" class="bg-primary my-2 w-100 text-light h5 border-0 p-2 rounded">
    </form>
  </div>
</body>

</html>