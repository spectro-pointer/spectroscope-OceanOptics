<?php
include '_setboxcar.php';
if (isset($_GET['channel'])) {
    echo setboxcar($_GET['width'], $_GET['channel']);
}
else {
    echo setboxcar($_GET['width']);
}
?>
