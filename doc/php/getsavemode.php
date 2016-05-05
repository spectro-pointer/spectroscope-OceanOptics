<?php
include '_getsavemode.php';
if (isset($_GET['channel'])) {
    echo getsavemode($_GET['channel']);
}
else {
    echo getsavemode();
}
?>
