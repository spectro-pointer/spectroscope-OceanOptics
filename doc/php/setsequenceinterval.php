<?php
include '_setsequenceinterval.php';
if (isset($_GET['channel'])) {
    echo setsequenceinterval($_GET['interval'], $_GET['channel']);
}
else {
    echo setsequenceinterval($_GET['interval']);
}
?>
