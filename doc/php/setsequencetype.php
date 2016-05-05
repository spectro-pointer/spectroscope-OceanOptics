<?php
include '_setsequencetype.php';
if (isset($_GET['channel'])) {
    echo setsequencetype($_GET['type'], $_GET['channel']);
}
else {
    echo setsequencetype($_GET['type']);
}
?>
