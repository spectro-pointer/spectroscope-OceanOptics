<?php
include '_getcalibrationeeprom.php';
if (isset($_GET['channel'])) {
    echo getcalibrationeeprom($_GET['channel;']);
}
else {
    echo getcalibrationeeprom();
}
?>
