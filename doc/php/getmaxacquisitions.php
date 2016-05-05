<?php
include '_getmaxacquisitions.php';
if (isset($_GET['channel'])) {
    echo getmaxacquisitions($_GET['channel']);
}
else {
    echo getmaxacquisitions();
}
?>
