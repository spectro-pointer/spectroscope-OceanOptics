<?php
include '_startsequence.php';
if (isset($_GET['channel'])) {
    echo startsequence($_GET['channel']);
}
else {
    echo startsequence();
}
?>
