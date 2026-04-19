<!-- Handles deletion of a category from Db -->
<?php
require 'connection.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $deleteQuery = "DELETE FROM categories WHERE category_id='$id'";
    if (mysqli_query($conn, $deleteQuery)) {
        echo "<script>alert('Category deleted successfully'); window.location='categories.php';</script>";
    } else {
        echo "<script>alert('Error deleting category'); window.location='categories.php';</script>";
    }
}
?>