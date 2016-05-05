<?php
include '_getaverage.php';
if (isset($_GET['channel'])) {
    echo getaverage($_GET['channel']);
}
else {
    echo getaverage();
}
?>
