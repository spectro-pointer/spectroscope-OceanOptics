<?php
include '_stopsequence.php';
if (isset($_GET['channel'])) {
    echo stopsequence($_GET['channel']);
}
else {
    echo stopsequence();
}
?>
