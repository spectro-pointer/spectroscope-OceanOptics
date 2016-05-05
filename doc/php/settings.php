<?php
include 'common.php';
include '_getname.php';
include '_getserial.php';
include '_getintegration.php';
include '_getboxcar.php';
include '_getaverage.php';
include '_getsequencestate.php';
include '_getcurrentstatus.php';
include '_setintegration.php';
include '_setaverage.php';
include '_setboxcar.php';

$channel = 0;
if (isset($_GET['channel'])) {
    $channel = $_GET['channel'];
}

$posted = false;
$good = true;
$msg = "";
if (isset($_POST['shutdown'])) {
	$posted = true;
	$command = 'echo "' . $_POST['security'] . '" | sudo -k -S shutdown -h now';
	//system("sudo shutdown -h now");
	$shutdown = exec($command, $output, $result);
}
else if (isset($_POST['submit'])) {
	$posted = true;
	if (setintegration($_POST['time'], $channel) != 1) {
		$good = false;
	}
	else if (setaverage($_POST['scans'], $channel) != 1) {
		$good = false;
	}
	else if (setboxcar($_POST['width'], $channel) != 1) {
		$good = false;
	}
	$msg = getcurrentstatus($channel);
}
?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="/css/ocean.css">
</head>

<body<?php
if ($posted) {
	if (!isset($_POST['shutdown'])) {
		echo ' onload="alert(\'' . $msg . '\');"';
	}
	else if ($result == 0) {
		// if we get this far the shutdown command was given the wrong password
		echo ' onload="alert(\'Shutting down...\');"';
	}
	else if ($result != 0) {
		// if we get this far the shutdown command was given the wrong password
		echo ' onload="alert(\'Incorrect password\');"';
	}
}
?>>
<img class="logo" src="/images/oo.gif">
<span style="border:2px solid grey;padding: 20px;font-size: 24px;font-family:sans-serif;font-weight:bold;color:white;background-color:#005aab; margin:20px; textalign:bottom">
WIFI Spectrometer Control
</span>
<p>
</p>
<div style="clear:both">
<ul>
<li><a id="menulinkactive" href="/cgi-bin/settings.php?channel=<?php echo $channel;?>">Settings</a></li>
<li><a id="menulink" href="/cgi-bin/sequence_control.php?channel=<?php echo $channel;?>">Acquisition Control</a></li>
<li><a id="menulink" href="/cgi-bin/dark.php?channel=<?php echo $channel;?>">Dark Measurement</a></li>
<li><a id="menulink" href="/cgi-bin/reference.php?channel=<?php echo $channel;?>">Reference Measurement</a></li>
<li><a id="menulink" href="/cgi-bin/immediate.php?channel=<?php echo $channel;?>">Single Measurement</a></li>
<li><a id="menulink" href="/cgi-bin/manage_results.php?channel=<?php echo $channel;?>">Manage Results</a></li>
<li><a id="menulink" href="/cgi-bin/config.php?channel=<?php echo $channel;?>">Configuration</a></li>
</ul>
</div>
<p>
</p>
<?php
$state_label = "Unknown";
$disabled = false;
$sequence_state = getsequencestate($channel);
switch ($sequence_state) {
case 'not_yet_configured':
case 'not_yet_started':
	$state_label = "Not yet started";
	break;
case 'paused':
	$state_label = "Paused";
	$disabled = true;
	break;
case 'active':
	$state_label = "Running";
	$disabled = true;
	break;
}
if (isset($retval)) {echo "retval is " . $retval . "<br> " . strlen($retval) . "<br>";}
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
<td align=right>Spectrometer Type</td>
<td><input type=text name=time readonly value="<?php echo getname($channel); ?>"></td>
</tr>
<tr>
<td align=right>Serial Number</td>
<td><input type=text name=time readonly value="<?php echo getserial($channel); ?>"></td>
</tr>
<tr>
<td align=right>Integration time (microsecs)</td>
<td><input type=text name=time<?php if ($disabled) echo " readonly";?> value="<?php echo getintegration($channel); ?>"></td>
</tr>
<tr>
<td align=right>Scans to average</td>
<td><input type=text name=scans<?php if ($disabled) echo " readonly";?> value="<?php echo getaverage($channel); ?>"></td>
</tr>
<tr>
<td align=right>Boxcar width</td>
<td><input type=text name=width<?php if ($disabled) echo " readonly";?> value="<?php echo getboxcar($channel); ?>"></td>
</tr>
<tr>
<td>
Password (only required for shutdown):
</td>
<td>
<input type=password name=security>
</td>
</tr>
</table>
<p>
<input type=submit name=submit<?php if ($disabled) echo " disabled";?> value=" Set " id="set"> &nbsp; 
<input type=submit name=shutdown value=Shutdown id="set">
</p>
</form>
</body>
</html>
