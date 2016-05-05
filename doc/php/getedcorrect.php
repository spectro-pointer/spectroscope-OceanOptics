<?php
include '_getedcorrect.php';
if (isset($_GET['channel'])) {
    echo getedcorrect($_GET['channel']);
}
else {
    echo getedcorrect();
}
?>
