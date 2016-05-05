<?php
$instructionName = "instruction.txt";
$libraryTarget = "/usr/lib/libseabreeze.so";
$daemonTarget = "/sbin/ocean-daemon";
$msg = "Success";
$success = false;
$posted = false;
$count = 0;
if (isset($_POST['commit'])) {
    $posted = true;
    if ($_FILES['file']['error'] !== 0) {
        $msg = "File not uploaded successfully";
    }
    else if (empty($_POST['auth'])) {
        $msg = "Password must not be empty";
    }
    else {

    	// we have got a valid file upload
    	set_time_limit(0);
    	$zip = new ZipArchive();
    	if ($zip->open($_FILES['file']['tmp_name']) === TRUE) {
    		// the uploaded file is a zip archive
    		// the contents should be a text file with instructions followed by the actual files to upgrade

			$stream = $zip->getStream($instructionName);
			if ($stream !== FALSE) {

				while (($instructions = fgets($stream)) !== FALSE) {

					// get the type, name, location, owner, group and mode of the file
					$upgrade = FALSE;
					$type = NULL;
                    $upgradeName = NULL;
                    $location = NULL;
                    $owner = NULL;
                    $group = NULL;
                    $mode = NULL;

                    $parts = explode(":", $instructions);
                    $count = count($parts);

					if ($count === 6) {
						$type = trim($parts[0]);
                        $upgradeName = trim($parts[1]);
                        $location = realpath(trim($parts[2]));
                        $owner = trim($parts[3]);
                        $group = trim($parts[4]);
                        $mode = trim($parts[5]);
					}
					else {
                        $msg = "Instructions contain unexpected contents: " . $instructions;
                        break;
					}
	
                    $badType = !($type === 'script' || $type === 'daemon' || $type === 'library' || $type === 'config' || $type === 'other');
                    if ($badType) {
                        $msg = "Upgrade type is incorrect: ". $type;
                        break;
                    }

                    if (is_dir($location) === FALSE) {
                        $msg = "Location is not a directory: " . $location;
                        break;
                    }

                    $upgrade = $zip->getFromName($upgradeName);

					$upgradeGood = false;
					if ($upgrade !== FALSE) {
						$upgradeName = "/home/ocean/" . $upgradeName;
						$upgradeHandle = fopen($upgradeName, 'wb');
						if ($upgradeHandle === FALSE) {
                            $msg = "Failed to open upgrade file " . $upgradeName;
                            break;
						}
						else if (fwrite($upgradeHandle, $upgrade) === FALSE) {
                            $msg = "Failed to write upgrade file " . $upgradeName;
                            break;
						}
						else {
							fflush($upgradeHandle);
							fclose($upgradeHandle);
							$upgradeGood = true;
						}
					}
					else {
                        $msg = "Failed to find the upgrade file " . $upgradeName . " in the archive";
                        break;
                    }

                    if ($upgradeGood) {

                        $dest = $location . "/" . basename($upgradeName);
                        // get the source contents for comparison with the destination once we have upgraded this file
				    	$sourceContents = file_get_contents($upgradeName);

						exec("echo " . $_POST['auth'] . " | sudo -k -S chown " . $owner . ":" . $group . " " . $upgradeName);
						exec("echo " . $_POST['auth'] . " | sudo -k -S chmod " . $mode . " " . $upgradeName);
						exec("echo " . $_POST['auth'] . " | sudo -k -S mv -f " . $upgradeName . " " . $dest);

                        $targetContents = NULL;
                        if ($type === "library") {
						    exec("echo " . $_POST['auth'] . " | sudo -k -S ln -sf " . $dest . " " . $libraryTarget);
                            $targetContents = file_get_contents($libraryTarget);
                        }
                        else if ($type === "daemon") {
						    exec("echo " . $_POST['auth'] . " | sudo -k -S ln -sf " . $dest . " " . $daemonTarget);
                            $targetContents = file_get_contents($daemonTarget);
                        }
                        else {
                            $targetContents = file_get_contents($dest);
                        }

						if ($sourceContents != $targetContents) {
                            $msg = "Failed to move " . $upgradeName . " to " . $dest . ". Password incorrect?";
                            // if we have failed to move the upgrade file then remove it now.
                            if (file_exists($upgradeName)) {
                                unlink($upgradeName);
                            }
                            break;
                        }
					}
				}
			}
			else {
				$msg = "Instruction file not found in archive";
            }
            $zip->close();
	    }
	    else {
		    $msg = "Cannot open zip file: " . $_FILES['file']['tmp_name'];
	    }
    }
}
?>

<html>
<head>
<link rel="stylesheet" type="text/css" href="/css/ocean.css">
</head>

<body<?php
if (isset($posted) && $posted) {
	echo ' onload="alert(\'' . $msg . '\');"';
}
?>>

<img class="logo" src="/images/oo.gif">
<span style="border:2px solid grey;padding: 20px;font-size: 24px;font-family:sans-serif;font-weight:bold;color:white;background-color:#005aab; margin:20px; textalign:bottom">
WIFI Spectrometer Upgrade
</span>
<p>
</p>
<div style="clear:both">
</div>
<p>
</p>
<form action=<?php echo $_SERVER['PHP_SELF'];?> method=post enctype='multipart/form-data'>
<table>
<tr>
<td>
Upgrade the software:
</td>
</tr>
<tr>
<td>
File:
</td>
<td>
<input type=file name=file>
</td>
</tr>
<tr>
<td>
Password
</td>
<td>
<input type=password name=auth>
</td>
</tr>
</table>
<br>
<input type=submit name=commit value='Upgrade' id='set'>
</form>
</body>
</html>
