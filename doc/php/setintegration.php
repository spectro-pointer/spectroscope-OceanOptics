<?php
include '_setintegration.php';
if (isset($_GET['channel'])) {
    echo setintegration($_GET['time'], $_GET['channel']);
}
else {
    echo setintegration($_GET['time']);
}
?>
