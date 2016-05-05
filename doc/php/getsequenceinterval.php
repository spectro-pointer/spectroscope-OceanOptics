<?php
include '_getsequenceinterval.php';
if (isset($_GET['channel'])) {
    echo getsequenceinterval($_GET['channel']);
}
else {
    echo getsequenceinterval();
}
?>
