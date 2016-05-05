<?php
include '_getsequencenumber.php';
if (isset($_GET['channel'])) {
    echo getsequencenumber($_GET['channel']);
}
else {
    echo getsequencenumber();
}
?>
