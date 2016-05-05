<?php
include '_getmaxintensity.php';
if (isset($_GET['channel'])) {
    echo getmaxintensity($_GET['channel']);
}
else {
    echo getmaxintensity();
}
?>
