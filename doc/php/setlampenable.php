<?php
include_once '_setlampenable.php';
if (isset($_GET['channel'])) {
    echo setlampenable($_GET["enable"], $_GET['channel']);
}
else {
    echo setlampenable($_GET["enable"]);
}
?>
