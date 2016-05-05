<?php
include '_setcalibrationeeprom.php';
if (isset($_GET['channel'])) {
    echo setcalibrationeeprom($_GET["calibration"], $_GET['channel']);
}
else {
    echo setcalibrationeeprom($_GET["calibration"]);
}
?>
