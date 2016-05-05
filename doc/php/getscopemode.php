<?php
include '_getscopemode.php';
if (isset($_GET['channel'])) {
    echo getscopemode($_GET['channel']);
}
else {
    echo getscopemode();
}
?>
