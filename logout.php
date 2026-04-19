<!-- logs user out and destorys session -->
<?php

session_start();
session_destroy();

header('location:login.php');
exit();

?>