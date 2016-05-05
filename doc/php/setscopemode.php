<?php
include '_setscopemode.php';
if (isset($_GET['channel'])) {
    echo setscopemode($_GET['mode'], $_GET['channel']);
}
else {
    echo setscopemode($_GET['mode']);
}
?>
