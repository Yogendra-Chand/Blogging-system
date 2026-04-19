<!-- this is to check if gd library is enabled -->
<?php
if (function_exists('gd_info')) {
    echo "GD library is enabled!";
} else {
    echo "GD library is NOT enabled!";
}
?>
