<?php
include_once '_setedcorrect.php';
if (isset($_GET['channel'])) {
    echo setedcorrect($_GET["electric"], $_GET['channel']);
}
else {
    echo setedcorrect($_GET["electric"]);
}
?>
