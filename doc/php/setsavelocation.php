<?php
include_once '_setsavelocation.php';
if (isset($_GET['channel'])) {
    echo setsavelocation($_GET['location'], $_GET['channel']);
}
else {
    echo setsavelocation($_GET['location']);
}
?>
