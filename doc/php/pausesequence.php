<?php
include '_pausesequence.php';
if (isset($_GET['channel'])) {
    echo pausesequence($_GET['channel']);
}
else {
    echo pausesequence();
}
?>
