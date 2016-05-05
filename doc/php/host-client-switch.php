<?php
$posted = isset($_POST['submit']);
// The path to the configuration file
$wpaFile = "/etc/wpa_supplicant/wpa_supplicant.conf";
// patterns to search for in the configuration file
$ssidPattern = "ssid";
$pskPattern = "psk";
// Default result
$msg = "Success";
$wpa = NULL;

if ($posted) {
    if ($_POST['mode'] === "client") {
        $stream = fopen($wpaFile, "r");
        if ($stream !== FALSE) {
            while (($line = fgets($stream)) !== FALSE) {
    		    if (strpos(ltrim($line),"#") === 0) {
				    $wpa = $wpa . $line;
			    }
                else {
                    $parts = explode("=", $line);
                    if (count($parts) == 2) {
                        $lhs = trim($parts[0]);
                        $rhs = trim($parts[1]);
                        if (strcmp($lhs, $ssidPattern) === 0) {
                            $wpa = $wpa . "\t" . $ssidPattern . "=\"" . $_POST['ap'] . "\"\n";
                        }
                        else if (strcmp($lhs, $pskPattern) === 0) {
                            $wpa = $wpa . "\t" . $pskPattern . "=\"" . $_POST['passwd'] . "\"\n";
                        }
                        else {
                            $wpa = $wpa . $line;
                        }
                    }
                    else {
                        $wpa = $wpa . $line;
                    }
                }
            }
            fclose($stream);

            file_put_contents($wpaFile, $wpa);
        }
        else {
            $msg = "Failed to open configuration file";
        }
        exec("sudo /usr/sbin/apcontrol client");
    }
    else if ($_POST['mode'] === "host") {
        exec("sudo /usr/sbin/apcontrol host");
    }
}
else {
    $points = array();
    $current = NULL;
    $output = NULL;
    $result = NULL;

    exec("sudo iwlist wlan0 scan", $output, $result);
    $state = "unknown";
    $error = false;

    foreach ($output as $item) {

	    $line = trim($item);
	    $process = true;
        while ($process) {
		    switch ($state) {
                case "unknown":
				    $process = false;
                    if (strpos($line, "Cell") === 0) {
					    $state = "essid_not_found";
					    $process = true;
                    }
				    break;

                case "essid_not_found":
                    $pending = false;
				    $process = false;
                    if (strpos($line, "ESSID") === 0) {
                        $parts = split(":", $line);
                        if (count($parts == 2)) {
                            $current = trim($parts[1], '"');
					        $state = "ie_not_found";
                            $process = true;
                        }
                        else {
                            $msg = "Error reading visible access point SSID";
                            $process = false;
                            $error = true;
                        }
				    }
				    break;

			    case "ie_not_found":
                    $process = false;
                    if (strpos($line, "IE") === 0) {
                        $parts = split(":", $line);
                        if (count($parts) == 2) {
                            $state = "authentication_not_found";
                            $process = true;
                        }
                        else {
                            $msg = "Error reading security type";
                            $process = false;
                            $error = true;
                        }
                    }
                    else if (strpos($line, "Cell") === 0) {
                        $current = NULL;
                        $state = "essid_not_found";
                        $process = true;
                    }
                    break;

			    case "authentication_not_found":
				    $process = false;
                    if (strpos($line, "Authentication Suites") === 0) {
                        if (strpos($line, "PSK") > 0 && (in_array($current, $points) === FALSE)) {
                            array_push($points, $current);
                        }
                        $state = "ie_not_found";
                        $process = true;
                    }
                    break;

                default:
                    $msg = "Error reading wireless scan results";
                    $process = false;
                    $error = true;
                    break;
            }

            if ($error) {
                break;
            }
        }
    }
}
?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="/css/ocean.css">
</head>

<body<?php
if ($posted) {
    echo " onload='alert(\"" . $msg . "\")';";
}
?>
>
<img class="logo" src="/images/oo.gif">
<span style="border:2px solid grey;padding: 20px;font-size: 24px;font-family:sans-serif;font-weight:bold;color:white;background-color:#005aab; margin:20px; textalign:bottom">
WIFI Spectrometer Host/Client Configuration
</span>
<p>
</p>
<div style="clear:both">
</div>
<p>
</p>
Configure the device as a wireless host or client.<br>
This will reconfigure and reboot the device which may take several minutes.<br><br>

<form action=<?php echo $_SERVER['PHP_SELF'];?> method=post>
<table>
<tr>
<td>
Set wireless as:
</td>
<td>
<input type=radio name=mode value=host checked
onclick='getElementById("ap").disabled=true;getElementById("passwd").disabled=true;getElementById("showpw").disabled=true;'>host
<input type=radio name=mode value=client
onclick='getElementById("ap").disabled=false;getElementById("passwd").disabled=false;getElementById("showpw").disabled=false;'>client
</td>
</tr>
<tr>
<td>Visible access points:</td>
<td>
<select name=ap id=ap disabled>
<?php
foreach ($points as $p) {
    if (!empty($p)) {
        echo "<option value='" . $p . "'>" . $p . "</option>\n";
    }
}
?>
</select>
</td>
</tr>
<tr>
<td>
Password:
</td>
<td>
<input type=password name=passwd id=passwd disabled>&nbsp;
<input type=checkbox id=showpw name=showpw disabled onclick='if (this.checked) {getElementById("passwd").setAttribute("type","text");} else {getElementById("passwd").setAttribute("type","password");}'>Show password
</td>
</tr>
</table>
<br>
<input type=submit name=submit value=" Set " id="set"> &nbsp; 
</p>
</form>
</body>
</html>
