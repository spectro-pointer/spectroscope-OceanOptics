<?php
include '_getsequencestate.php';
if (isset($_GET['channel'])) {
    echo getsequencestate($_GET['channel']);
}
else {
    echo getsequencestate();
}
?>
