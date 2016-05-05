<?php
include_once '_settecenable.php';
if (isset($_GET['channel'])) {
    echo settecenable($_GET["enable"], $_GET['channel']);
}
else {
    echo settecenable($_GET["enable"]);
}
?>
