<?php
include '_currentspectrum.php';
if (isset($_GET['channel'])) {
    echo currentspectrum($_GET['channel']);
}
else {
    echo currentspectrum();
}
?>
