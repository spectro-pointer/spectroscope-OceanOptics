<?php
include '_getspectrum.php';
if (isset($_GET['channel'])) {
    echo getspectrum($_GET['channel']);
}
else {
    echo getspectrum();
}
?>
