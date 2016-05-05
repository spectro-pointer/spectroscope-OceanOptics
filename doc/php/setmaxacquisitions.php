<?php
include '_setmaxacquisitions.php';
if (isset($_GET['channel'])) {
    echo setmaxacquisitions($_GET['acquisitions'], $_GET['channel']);
}
else {
    echo setmaxacquisitions($_GET['acquisitions']);
}
?>
