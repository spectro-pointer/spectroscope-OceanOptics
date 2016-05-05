<?php
include '_getcurrentstatus.php';
if (isset($_GET['channel'])) {
    echo getcurrentstatus($_GET['channel']);
}
else {
    echo getcurrentstatus();
}
?>
