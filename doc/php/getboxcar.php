<?php
include '_getboxcar.php';
if (isset($_GET['channel'])) {
    echo getboxcar($_GET['channel']);
}
else {
    echo getboxcar();
}
?>
