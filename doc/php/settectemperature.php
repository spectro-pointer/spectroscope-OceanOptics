<?php
include_once '_settectemperature.php';
if (isset($_GET['channel'])) {
    echo settectemperature($_GET["temp"], $_GET['channel']);
}
else {
    echo settectemperature($_GET["temp"]);
}
?>
