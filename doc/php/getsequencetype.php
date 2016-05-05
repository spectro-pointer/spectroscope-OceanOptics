<?php
include '_getsequencetype.php';
if (isset($_GET['channel'])) {
    echo getsequencetype($_GET['channel']);
}
else {
    echo getsequencetype();
}
?>
