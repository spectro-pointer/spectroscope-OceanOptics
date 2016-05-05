<?php
include '_getserial.php';
if (isset($_GET['channel'])) {
    echo getserial($_GET['channel']);
}
else {
    echo getserial();
}
?>
