<?php
include '_setaverage.php';
if (isset($_GET['channel'])) {
    echo setaverage($_GET['scans'], $_GET['channel']);
}
else {
    echo setaverage($_GET['scans']);
}
?>
