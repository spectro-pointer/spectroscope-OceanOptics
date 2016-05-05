<?php
include '_getcalibrationbuffer.php';
if (isset($_GET['channel'])) {
    echo implode(" ", getcalibrationbuffer($_GET['channel']));
}
else {
    echo implode(" ", getcalibrationbuffer());
}
?>
