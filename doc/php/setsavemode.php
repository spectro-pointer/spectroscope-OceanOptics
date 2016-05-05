<?php
include_once '_setsavemode.php';
if (isset($_GET['channel'])) {
    echo setsavemode($_GET['savemode'], $_GET['channel']);
}
else {
    echo setsavemode($_GET['savemode']);
}
?>
