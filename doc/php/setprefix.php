<?php
include '_setprefix.php';
if (isset($_GET['channel'])) {
    echo setprefix($_GET['prefix'], $_GET['channel']);
}
else {
    echo setprefix($_GET['prefix']);
}
?>
