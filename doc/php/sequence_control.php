<?php
session_start();

include 'common.php';
include '_getsavemode.php';
include '_getprefix.php';
include '_getsavelocation.php';
include '_getmaxacquisitions.php';
include '_getsequencetype.php';
include '_getsequenceinterval.php';
include '_getsequencestate.php';
include '_startsequence.php';
include '_pausesequence.php';
include '_resumesequence.php';
include '_stopsequence.php';
include '_setsavemode.php';
include '_setprefix.php';
include '_setsavelocation.php';
include '_setmaxacquisitions.php';
include '_setsequencetype.php';
include '_setsequenceinterval.php';
include '_getstatus.php';
include '_getscopemode.php';
include '_setscopemode.php';
include '_getscopeinterval.php';
include '_setscopeinterval.php';
include '_getcurrentstatus.php';

$channel = 0;
if (isset($_GET['channel'])) {
    $channel = $_GET['channel'];
}

$show_scope = false;
$posted = false;
$good = true;
$msg = "";
$show_msg = false;
$refresh = false;
if (isset($_POST['apply'])) {
	$posted = true;
	if (setsavelocation($_POST['savedir'], $channel) != 1) {
		$good = false;
	}
	else if (setmaxacquisitions($_POST['maxacq'], $channel) != 1) {
		$good = false;
	}
	else if (setsequenceinterval($_POST['interval'], $channel) != 1) {
		$good = false;
	}
	else if (setscopeinterval($_POST['scopeinterval'], $channel) != 1) {
		$good = false;
	}
	else {
		setsavemode($_POST['savemode'], $channel);
		setprefix($_POST['fileprefix'], $channel);
		setsequencetype("timer", $channel);
		setscopemode($_POST['scopemode'], $channel);
	}
	$msg = getcurrentstatus($channel);
	$show_msg = true;
}
else if (isset($_POST['start'])) {
	$posted = true;

	switch ($_POST['start']) {
	case "Start":
		if (setsavelocation($_POST['savedir'], $channel) != 1) {
			$good = false;
		}
		else if (setmaxacquisitions($_POST['maxacq'], $channel) != 1) {
			$good = false;
		}
		else if (setsequenceinterval($_POST['interval'], $channel) != 1) {
			$good = false;
		}
		else if (setscopeinterval($_POST['scopeinterval'], $channel) != 1) {
			$good = false;
		}
		else {
			setsavemode($_POST['savemode'], $channel);
			setprefix($_POST['fileprefix'], $channel);
			setsequencetype("timer", $channel);
			setscopemode($_POST['scopemode'], $channel);
			//if ($_POST['scopemode'] == "on") $show_scope = true;

			if (startsequence($channel) != 1) {
				$good = false;
			}
		}
		$msg = getcurrentstatus($channel);
        $show_msg = !$good;
		break;

	case "Pause":
		pausesequence($channel);
		break;

	case "Resume":
		resumesequence($channel);
		break;

	case "Stop":
		stopsequence($channel);
		break;
	}
}
else if (isset($_POST['stop'])) {
	stopsequence($channel);
}
$scopeinterval = getscopeinterval($channel);
$state = getsequencestate($channel);
if ($state != "not_yet_started" && $state != "not_yet_configured") {
	$refresh = true;
}
?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="/css/ocean.css">
<?php if ($refresh)
echo '<meta http-equiv="refresh" content="' . $scopeinterval . '">';
?>
</head>

<body<?php
if ($posted) {
	echo ' onload="';
	if ($show_msg) {
		echo 'alert(\'' . $msg .  '\');';
	}
	if ($good && $show_scope) {
		//echo 'window.open(\'/cgi-bin/scope.php?refresh=' . $_POST['scopeinterval'] .  '&channel=' . $channel . '\',\'\',\'width=1024,height=768,resizable=yes,scrollbars=yes\');';
	}
	echo '"';
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
<li><a id="menulink" href="/cgi-bin/settings.php?channel=<?php echo $channel;?>">Settings</a></li>
<li><a id="menulinkactive" href="/cgi-bin/sequence_control.php?channel=<?php echo $channel;?>">Acquisition Control</a></li>
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
$savemode = getsavemode($channel);
$sequencetype = getsequencetype($channel);
$sequencestate = getsequencestate($channel);
$scopemode = getscopemode($channel);
//$scopeinterval = getscopeinterval($channel);
$start_label = '';
$show_stop = false;
$show_apply = false;
$disabled = false;
$state_label = "Unknown";

switch ($sequencestate) {
case 'not_yet_configured':
    $show_scope = false;
case 'not_yet_started':
	$start_label = "Start";
	$state_label = "Not yet started";
    $show_apply = true;
    $show_scope = false;
	break;
case 'paused':
	$start_label = "Resume";
	$state_label = "Paused";
	$show_stop = true;
	$disabled  = true;
    $show_scope = $scopemode == 'on';
	break;
case 'active':
	$start_label = "Pause";
	$state_label = "Running";
	$show_stop = true;
	$disabled  = true;
    $show_scope = $scopemode == 'on';
	break;
}
?>
<!-- <form action=<?php echo $_SERVER['PHP_SELF'] . "?refresh=" . $scopeinterval . "&channel=" . $channel;?> method=post>
-->
<form action=<?php echo $_SERVER['PHP_SELF'] . "?channel=" . $channel;?> method=post>
<table>
<tr>
<td>Sequence state:</td>
<td><input type=text name=state readonly value='<?php echo $state_label;?>'></td>
</tr>
</table>
<?php
if ($good && $show_scope) {
	//echo '<iframe style=\'float:right; margin-left:20px\' frameBorder=0 src=\'/cgi-bin/scope.php?refresh=' . $_POST['scopeinterval'] .  '&channel=' . $channel . '&mode=sequence\' width=800 height=600></iframe>';
	echo '<iframe style=\'float:right; margin-left:20px\' frameBorder=0 src=\'/cgi-bin/scope.php?channel=' . $channel . '&mode=sequence\' width=800 height=600></iframe>';
}
?>
<br>
<table>
<tr>
<td align=right>Save spectra to multiple files?</td>
<td>
<input type=radio name=savemode value="multi"<?php if ($disabled) echo " disabled"; if ($savemode == "multi") echo " checked";?>>yes &nbsp;
<input type=radio name=savemode value="single"<?php if ($disabled) echo " disabled"; if ($savemode != "multi") echo " checked";?>>no
</td>
</tr>
<tr>
<td align=right>Filename prefix</td>
<td><input type=text name=fileprefix<?php if ($disabled) echo " readonly";?> value="<?php echo getprefix($channel); ?>"></td>
</tr>
<tr>
<td align=right>Save spectra to</td>
<td><input type=text name=savedir<?php if ($disabled) echo " readonly";?> value="<?php echo getsavelocation($channel); ?>">&nbsp;
<input type=button value="Browse..."<?php if ($disabled) echo " disabled";?> onClick='var w = window.open("dir_browse.php");' id="set"></td>
</tr>
<tr>
<td align=right>Set maximum number of acquisitions</td>
<td><input type=text name=maxacq<?php if ($disabled) echo " readonly";?> value="<?php echo getmaxacquisitions($channel); ?>"></td>
</tr>
<tr>
<td align=right>Save spectra every (millisecs)</td>
<td><input type=text<?php if ($disabled) echo " readonly";?> name=interval value="<?php echo getsequenceinterval($channel); ?>"></td>
</tr>
<tr>
<td align=right>Show in scope mode</td>
<td>
<input type=radio name=scopemode value="on"<?php
if ($scopemode == 'on') {
	echo ' checked';
}
else if ($disabled) {
	echo ' disabled';
}
?>>yes &nbsp;
<input type=radio name=scopemode value="off"<?php
if ($scopemode == 'off') {
	echo ' checked';
}
else if ($disabled) {
	echo ' disabled';
}
?>>no
</td>
</tr>
<tr>
<td align=right>Refresh scope every (secs)</td>
<td><input type=number<?php if ($disabled) echo " readonly";?> name=scopeinterval value=<?php echo $scopeinterval;?>></td>
</tr>
</table>
<p>
<?php
if ($show_apply) {
	echo '<input type=submit name=apply value="Apply settings" id="set">&nbsp;';
}
?>
<input type=submit name=start value=<?php echo $start_label;?> id="set">
<?php
if ($show_stop) {
	echo '&nbsp; <input type=submit name=stop value=Stop id="set">';
}
?>
</p>
</form>
</body>
</html>
