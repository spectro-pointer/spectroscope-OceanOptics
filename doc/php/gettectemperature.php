<?php
include '_gettectemperature.php';
if (isset($_GET['channel'])) {
    echo gettectemperature($_GET['channel']);
}
else {
    echo gettectemperature();
}
?>
