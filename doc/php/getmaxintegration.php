<?php
include '_getmaxintegration.php';
if (isset($_GET['channel'])) {
    echo getmaxintegration($_GET['channel']);
}
else {
    echo getmaxintegration();
}
?>
