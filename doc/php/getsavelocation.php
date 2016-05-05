<?php
include '_getsavelocation.php';
if (isset($_GET['channel'])) {
    echo getsavelocation($_GET['channel']);
}
else {
    echo getsavelocation();
}
?>
