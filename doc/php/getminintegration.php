<?php
include '_getminintegration.php';
if (isset($_GET['channel'])) {
    echo getminintegration($_GET['channel']);
}
else {
    echo getminintegration();
}
?>
