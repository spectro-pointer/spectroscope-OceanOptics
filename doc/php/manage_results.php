<?php
session_start();

$channel = 0;
if (isset($_GET['channel'])) {
    $channel = $_GET['channel'];
}
?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="/css/ocean.css">
</head>
<?php
include '_getsequencestate.php';

$current = "/home/ocean";
if (isset($_GET['current'])) {
	if (file_exists($_GET['current']) && is_dir($_GET['current'])) {
		$current = $_GET['current'];
	}
}
else if (isset($_POST['current'])) {
	if (file_exists($_POST['current']) && is_dir($_POST['current'])) {
		$current = $_POST['current'];
	}
}

$current = realpath($current);
$_SESSION['selected_dir'] = $current;
$selectall = false;
$zipfile = "/tmp/selected" . time() . ".zip";
if (isset($_POST['selectall'])) {
	$selectall = true;
}
else if (isset($_POST['delete'])) {
	foreach ($_POST['checkedlist'] as $p) {
		$f = $current . "/" . $p;
		unlink($f);
	}
}
else if (isset($_POST['download'])) {
	set_time_limit(0);
	$zip = new ZipArchive();
	if ($zip->open($zipfile, ZipArchive::CREATE) == TRUE) {
		foreach ($_POST['checkedlist'] as $p) {
			$i = $current . "/" . $p;
			$zip->addFile($i, $p);
		}
		$zip->close();

	}
	else {
		echo "Create failed<br>";
	}
}

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
?>
<body<?php if (isset($_POST) && file_exists($zipfile)) echo ' onload="window.open(\'/cgi-bin/getdata.php?path=' . $zipfile . '\');"';?>>
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
<li><a id="menulink" href="/cgi-bin/dark.php?channel=<?php echo $channel;?>">Dark Measurement</a></li>
<li><a id="menulink" href="/cgi-bin/reference.php?channel=<?php echo $channel;?>">Reference Measurement</a></li>
<li><a id="menulink" href="/cgi-bin/immediate.php?channel=<?php echo $channel;?>">Single Measurement</a></li>
<li><a id="menulinkactive" href="/cgi-bin/manage_results.php?channel=<?php echo $channel;?>">Manage Results</a></li>
<li><a id="menulink" href="/cgi-bin/config.php?channel=<?php echo $channel;?>">Configuration</a></li>
</ul>
</div>
<p>
</p>
<form action=<?php echo $_SERVER['PHP_SELF'] . "?channel=" . $channel;?> method=POST>
<input type=hidden name=current value="<?php echo $current;?>">
<table>
<tr>
<td>Sequence state:</td>
<td><input type=text name=state readonly value='<?php echo $state_label;?>'></td>
</tr>
</table>
<br>
<table border=1>
<tr valign=top>
<td>
Current directory: &nbsp;
<input type=text readonly value="<?php echo $current;?>">
<p>
</p>
<?php
$list = scandir($current);
foreach ($list as $item) {
	switch ($item) {
	case ".":
		break;
	case "..":
			echo "<a href=" . $_SERVER['PHP_SELF'] . "?channel=" . $channel . "&current=" . dirname($current) . ">" . "{up a level}</a><br><br>";
		break;
	default:
		$full_path = realpath($current . "/" . $item);
		if (is_dir($full_path)) {
			echo "<a href=" . $_SERVER['PHP_SELF'] . "?channel=" . $channel . "&current=" . $full_path . ">" . $item . "</a><br>";
		}
		break;
	}
}
?>
</td>
<td>
Files:
<p>
</p>
<?php
$list = scandir($current);
foreach ($list as $item) {
	$full_path = $current . "/" . $item;
	if (!is_dir($full_path)) {
		echo "<input type=checkbox value='" . $item . "' name='checkedlist[]'";
		if ($selectall) {
			echo " checked";
		}
		echo ">". $item . "<br>";
	}
}
?>
</td>
</tr>
</table>
<p>
</p>
<input id="set" type=submit name=selectall value="Select all"> &nbsp;
<input id="set" type=submit name=download value="Download selection"> &nbsp;
<input id="set" type=submit name=delete value="Delete selection"">
</form>
</body>
</html>
