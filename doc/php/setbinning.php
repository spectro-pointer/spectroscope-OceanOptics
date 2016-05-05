<?php
include '_setbinning.php';
if (isset($_GET['channel'])) {
    echo setbinning($_GET['bin'], $_GET['channel']);
}
else {
    echo setbinning($_GET['bin']);
}
?>
