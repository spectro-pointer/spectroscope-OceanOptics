<?php
$posted = isset($_POST['commit']);
$configFile = "/etc/hostapd/hostapd.conf";
$passMin = 8;
$passMax = 63;
$idMax = 32;
$ssidString="ssid";
$passwdString="wpa_passphrase";
$msg = "Success";

if ($posted) {
		$pass1 = $_POST['pass1'];
		$pass2 = $_POST['pass2'];
		$id = $_POST['ssid'];
		$passLength = strlen($pass1);
		// this is not bullet proof...the standard allows strange characters in an SSID
        $idLength = strlen($id);
        if ($pass1 != $pass2) {
			$msg = "Not changed. Passwords do not match";
        }
        else if ($passLength < $passMin) {
			$msg = "Not changed. Password length should be at least " . $passMin . " characters.";
		}
		else if ($passLength > $passMax) {
			$msg = "Not changed. Password length should be no more than " . $passMax . " characters.";
		}
		else if ($idLength == 0) {
			$msg = "Not changed. SSID must not be empty";
		}
		else if ($idLength > $idMax) {
			$msg = "Not changed. SSID must be no more than " . $idMax . " characters";
		}
		else {
			$stream = fopen($configFile, "r");
			$output = "";
			while (($line = fgets($stream)) !== FALSE) {
				if (strpos(ltrim($line),"#") !== FALSE) {
					$output = $output . $line;
				}
				else if (strpos($line, $ssidString) !== FALSE) {
					$output = $output . $ssidString . "=" . $id . "\n";
				}
				else if (strpos($line, $passwdString) !== FALSE) {
					$output = $output . $passwdString . "=" . $pass1 . "\n";
				}
				else {
					$output = $output . $line;
				}
			}
			fclose($stream);
			if (file_put_contents($configFile, $output) === FALSE) {
				$msg = "Unable to write to the configuration file";
			}
		}
}

$stream = fopen($configFile, "r");
$currentSsid = "";
$currentPasswd = "";
$passFound = false;
$ssidFound = false;
if ($stream !== FALSE) {
	while (($line = fgets($stream)) !== FALSE) {
		if (strpos(ltrim($line),"#") !== FALSE) {
			// this is a comment line so do nothing
		}
		else if (strpos($line, $ssidString) !== FALSE) {
			$parts = split("=", $line);
			if (count($parts) == 2) {
				$currentSsid = trim($parts[1]);
			}
			else {
				$msg = "SSID configuration does not have two parts";
			}
			$ssidFound = true;
		}
		else if (strpos($line, $passwdString) !== FALSE) {
			$parts = split("=", $line);
			if (count($parts) == 2) {
				$currentPasswd = trim($parts[1]);
			}
			else {
				$msg = "Password configuration does not have two parts";
			}
			$passFound = true;
		}
	}
	fclose($stream);
}
else {
	$msg = "Configuration " . $configFile . " not found";
}
if (!$ssidFound) {
		$msg = "SSID not found";
}
if (!$passFound) {
		$msg = "Password not found";
}
?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="/css/ocean.css">
</head>

<body<?php
if ($posted) {
	echo ' onload="alert(\'' . $msg . '\');"';
}
?>>
<img class="logo" src="/images/oo.gif">
<span style="border:2px solid grey;padding: 20px;font-size: 24px;font-family:sans-serif;font-weight:bold;color:white;background-color:#005aab; margin:20px; textalign:bottom">
WIFI Spectrometer Access Point Configuration
</span>
<p>
</p>
<div style="clear:both">
</div>
<p>
</p>
Change the wireless network ID and password (these changes will take effect on reboot):<br><br>
<form action=<?php echo $_SERVER['PHP_SELF'];?> method=post>
<table>
<tr>
<td>
Name (SSID):
</td>
<td>
<input type=text name=ssid value='<?php echo $currentSsid;?>'>
</td>
</tr>
<tr>
<td>
Password:
</td>
<td>
<input type=password name=pass1>
</td>
</tr>
<tr>
<td>
Confirm Password:
</td>
<td>
<input type=password name=pass2>
</td>
</tr>
</table>
<br>
<input type=submit name=commit value='Submit' id='set'>
</form>
</body>
</html>


