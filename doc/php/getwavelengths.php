<?php
include '_getwavelengths.php';
if (isset($_GET['channel'])) {
    echo getwavelengths($_GET['channel']);
}
else {
    echo getwavelengths();
}
?>
