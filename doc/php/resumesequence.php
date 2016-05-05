<?php
include '_resumesequence.php';
if (isset($_GET['channel'])) {
    echo resumesequence($_GET['channel']);
}
else {
    echo resumesequence();
}
?>
