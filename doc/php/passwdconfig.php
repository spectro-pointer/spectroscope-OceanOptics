<?php
$posted = isset($_POST['commit']);
$msg = "Success";

if ($posted) {
		$user = $_POST['user'];
		$pass1 = $_POST['pass1'];
		$pass2 = $_POST['pass2'];
		$auth = $_POST['auth'];

		if (strlen($user) == 0) {
				$msg = "Cannot have an empty username";
		}
		else if (strlen($pass1) == 0) {
				$msg = "Cannot have an empty password";
		}
		else if ($pass1 != $pass2) {
				$msg = "Passwords do not match";
		}
		else {
				$count = 1;
				$file = "/home/ocean/." . rand() . ".touch";
				while (file_exists($file) && $count <= 20) {
					$file = "/home/ocean/." . rand() . ".touch";
					$count++;
				}
				if (!file_exists($file)) {
                    // try to change the password
                    $command1 = "/bin/bash -c \"echo -e '" . $pass1 . "\\n" . $pass2 . "\\n" . $auth . "' | sudo -k -S passwd " . $user . "\"";
					//$command1 = "echo -e \"" . $auth . "\\n" . $pass1 . "\\n" . $pass2 . "\" | sudo -k -S passwd ". $user;
					$out = exec($command1);
                    // now touch a temporary file via "su -c" as the user we are changing the pasword for
                    $command2 = "/bin/bash -c \"echo -e '" . $pass1 . "\\n' | sudo -u " . $user . " touch " . $file . "\"";
					//$command2 = "echo -e \"" . $pass1 . "\\n\" | su -c \"touch " . $file . "\" " . $user;
					$out = exec($command2);
					sleep(5);
					// and check to see if the file exists - if it does the password must be set
					if (file_exists($file) === FALSE) {
						$msg = "Did not succeed in changing the password";
					}
					else {
							unlink($file);
					}
				}
				else {
					$msg = "Error in checking the password has changed, please try again";
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
	echo ' onload="alert(\'' . $msg . '\');"';
}
?>>
<img class="logo" src="/images/oo.gif">
<span style="border:2px solid grey;padding: 20px;font-size: 24px;font-family:sans-serif;font-weight:bold;color:white;background-color:#005aab; margin:20px; textalign:bottom">
WIFI Spectrometer: Change Password
</span>
<p>
</p>
<div style="clear:both">
</div>
<p>
</p>
<form action=<?php echo $_SERVER['PHP_SELF'];?> method=post>
<table>
<tr>
<td>
Change password for user:
</td>
<td>
<input type=text name=user>
</td>
</tr>
<tr>
<td>
New password:
</td>
<td>
<input type=password name=pass1>
</td>
</tr>
<tr>
<td>
Confirm new password:
</td>
<td>
<input type=password name=pass2>
</td>
</tr>
<tr>
<td>
User "ocean" password:
</td>
<td>
<input type=password name=auth>
</td>
</tr>
</table>
<br>
<input type=submit name=commit value='Submit' id='set'>
</form>
</body>
</html>
