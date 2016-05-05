<?php
include '_setscopeinterval.php';
if (isset($_GET['channel'])) {
    echo setscopeinterval($_GET['interval'], $_GET['channel']);
}
else {
    echo setscopeinterval($_GET['interval']);
}
?>
