<?php
include '_getbinning.php';
if (isset($_GET['channel'])) {
    echo getbinning($_GET['channel']);
}
else {
    echo getbinning();
}
?>
