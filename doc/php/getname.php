<?php
include '_getname.php';
if (isset($_GET['channel'])) {
    echo getname($_GET['channel']);
}
else {
    echo getname();
}
?>
