<?php
include '_setcalibrationbuffer.php';
if (isset($_GET['channel'])) {
    echo setcalibrationbuffer($_GET["calibration"] , $_GET['channel']);
}
else {
    echo setcalibrationbuffer($_GET["calibration"]);
}
?>
