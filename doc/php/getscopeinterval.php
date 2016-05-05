<?php
include '_getscopeinterval.php';
if (isset($_GET['channel'])) {
    echo getscopeinterval($_GET['channel']);
}
else {
    echo getscopeinterval();
}
?>
