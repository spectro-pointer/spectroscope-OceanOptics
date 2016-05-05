<?php
include '_getintegration.php';
if (isset($_GET['channel'])) {
    echo getintegration($_GET['channel']);
}
else {
    echo getintegration();
}
?>
