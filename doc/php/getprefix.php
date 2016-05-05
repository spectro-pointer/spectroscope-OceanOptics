<?php
include '_getprefix.php';
if (isset($_GET['channel'])) {
    echo getprefix($_GET['channel']);
}
else {
    echo getprefix();
}
?>
