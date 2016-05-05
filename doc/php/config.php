<?php
require_once 'Config/Lite.php';
include '_getversion.php';
include '_getserial.php';

$config = "/etc/ocean/ocean-daemon.conf";

$channelarg="";
if (isset($_GET['channel']) && is_numeric($_GET['channel'])) {
    $channelarg="?channel=" . $_GET['channel'];
}
else if (isset($_POST['channel']) && is_numeric($_POST['channel'])) {
    $channelarg="?channel=" . $_POST['channel'];
}

$config_array = new Config_Lite($config);
$channel = 0;

if (isset($_GET['channel']) && is_numeric($_GET['channel'])) {
    $channel = $_GET['channel'];
}
else if (isset($_POST['channel']) && is_numeric($_POST['channel']))
{
    $channel = $_POST['channel'];
}

$serial = getserial($channel);

$posted = false;
if (!empty($_POST)) {
	$posted = true;
	$success = true;
	// TODO check all values first

    foreach ($_POST as $key => $value) {
        if ($key != 'channel')
        {
            $config_array->set($serial, $key, $value);
        }
    }
    $config_array->save();
}

$section = $config_array[$serial];

?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="/css/ocean.css">
</head>

<body<?php
if ($posted) {
	$msg = $success ? "Updated OK" : "Update failed";
	echo " onload='alert(\"" . $msg . "\");'";
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
<li><a id="menulink" href="/cgi-bin/settings.php<?php echo $channelarg;?>">Settings</a></li>
<li><a id="menulink" href="/cgi-bin/sequence_control.php<?php echo $channelarg;?>">Acquisition Control</a></li>
<li><a id="menulink" href="/cgi-bin/dark.php<?php echo $channelarg;?>">Dark Measurement</a></li>
<li><a id="menulink" href="/cgi-bin/reference.php<?php echo $channelarg;?>">Reference Measurement</a></li>
<li><a id="menulink" href="/cgi-bin/immediate.php<?php echo $channelarg;?>">Single Measurement</a></li>
<li><a id="menulink" href="/cgi-bin/manage_results.php<?php echo $channelarg;?>">Manage Results</a></li>
<li><a id="menulinkactive" href="/cgi-bin/config.php<?php echo $channelarg;?>">Configuration</a></li>
</ul>
</div>
<p>
NOTE: Changes made here will only take effect on re-start.
</p>
<p>
Please refer to the documentation for the meaning of the configuration values.
</p>
<table>
<tr>
<td>Software version:</td>
<td><?php echo getversion();?></td>
</tr>
</table>
<br>
<?php
echo "<form action='" .  $_SERVER['PHP_SELF'] . $channelarg . "' method='post'>";
echo $serial;
echo "<table>";
foreach ($section as $key => $value) {
	echo "<tr>";
	if ($key != "") {
		echo '<td align=right>' . $key . '</td><td><input type="text" name="' . $key . '" value="' . $value . '"></td>';
	}
	echo "</tr>";
}
echo "</table>";
echo "<br>";
echo "<input type='hidden' name='channel' value='" . $channel . "'>";
echo "<input type='submit' value='Submit' id='set'>";
echo "</form>";
?>
</body>
</html>
