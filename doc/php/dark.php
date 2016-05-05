<!DOCTYPE html>
<?php
include '_getsequencestate.php';
include '_getsavelocation.php';
include '_savespectrum.php';
include '_getcurrentstatus.php';

$channel = 0;
if (isset($_GET['channel'])) {
    $channel= $_GET['channel'];
}

$posted = false;
$good = true;
if (isset($_POST['savedir']) && isset($_POST['filename'])) {
	$posted = true;
	$save_result = "";
	if (empty($_POST['savedir'])) {
		$save_result = "Not saved: directory must not be blank";
	}
	else if (empty($_POST['filename'])) {
		$save_result = "Not saved: filename must not be blank";
	}
	else {
		$location = realpath($_POST['savedir']) . "/" . $_POST['filename'];
		savespectrum($location, $channel);
		$save_result = getcurrentstatus($channel);
	}
}
?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="/css/ocean.css">
</head>

<body<?php if ($posted) echo ' onload="alert(\'' . $save_result . '\');"';?>>
<img class="logo" src="/images/oo.gif">
<span style="border:2px solid grey;padding: 20px;font-size: 24px;font-family:sans-serif;font-weight:bold;color:white;background-color:#005aab; margin:20px; textalign:bottom">
WIFI Spectrometer Control
</span>
<p>
</p>
<div style="clear:both">
<ul>
<li><a id="menulink" href="/cgi-bin/settings.php?channel=<?php echo $channel;?>">Settings</a></li>
<li><a id="menulink" href="/cgi-bin/sequence_control.php?channel=<?php echo $channel;?>">Acquisition Control</a></li>
<li><a id="menulinkactive" href="/cgi-bin/dark.php?channel=<?php echo $channel;?>">Dark Measurement</a></li>
<li><a id="menulink" href="/cgi-bin/reference.php?channel=<?php echo $channel;?>">Reference Measurement</a></li>
<li><a id="menulink" href="/cgi-bin/immediate.php?channel=<?php echo $channel;?>">Single Measurement</a></li>
<li><a id="menulink" href="/cgi-bin/manage_results.php?channel=<?php echo $channel;?>">Manage Results</a></li>
<li><a id="menulink" href="/cgi-bin/config.php?channel=<?php echo $channel;?>">Configuration</a></li>
</ul>
</div>
<p>
</p>
<div style="float:left">
<?php
$state_label = "Unknown";
$disabled = false;
$sequence_state = getsequencestate($channel);
$saved_ok = false;
switch ($sequence_state) {
case 'not_yet_configured':
case 'not_yet_started':
	$state_label = "Not yet started";
	break;
case 'paused':
	$state_label = "Paused";
	break;
case 'active':
	$state_label = "Running";
	$disabled = true;
	break;
}

?>
<form action=<?php echo $_SERVER['PHP_SELF'] . "?channel=" . $channel;?> method=post>
<table>
<tr>
<td>Sequence state:</td>
<td><input type=text name=state readonly value='<?php echo $state_label;?>'></td>
</tr>
</table>
<br>
<table>
<tr>
<td align=right>Save dark spectrum to</td>
<td><input type=text name=savedir<?php if ($disabled) echo " readonly";?> value="<?php
if (isset($_POST['savedir'])) {
	echo $_POST['savedir'];
}
else {
	echo getsavelocation($channel);
}
?>">
</td>
<td>
<input type=button value="Browse..."<?php if ($disabled) echo " disabled";?> onClick='var w = window.open("dir_browse.php");' id="set"></td>
</tr>
<tr>
<td align=right>File name:</td>
<td><input type=text name=filename<?php
if ($disabled) echo " readonly";
if (isset($_POST['filename'])) {
	echo " value='" . $_POST['filename'] . "'";
}
?>></td>
</tr>
</table>
<br>
<input type=submit name=submit<?php if ($disabled) echo " disabled";?> value="Acquire and Save" id="set"> &nbsp; 
</form>
</div>
<?php
if ($posted) {
    echo '<div style="float:right">';
    echo '<iframe style=\'float:right\' frameBorder=0 src=\'/cgi-bin/scope.php?refresh=' . $_POST['scopeinterval'] .  '&channel=' . $channel . '&mode=immediate\' width=800 height=600></iframe>';
    echo '</div>';
}
?>
<!--
</div>
-->
</body>
</html>
