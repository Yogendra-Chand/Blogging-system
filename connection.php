<!-- COnnects to the database -->
<?php
$conn = mysqli_connect('localhost', 'root', '', 'blogging_system');

if (!$conn) {
    die('Database connection failed!');
}
