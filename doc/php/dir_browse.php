<html>
<head>
<link rel="stylesheet" type="text/css" href="/css/ocean.css">
</head>

<body>
<img class="logo" src="/images/oo.gif">
<span style="border:2px solid grey;padding: 20px;font-size: 24px;font-family:sans-serif;font-weight:bold;color:white;background-color:#005aab; margin:20px; textalign:bottom">
WIFI Spectrometer Control
</span>
<p>
</p>
<div style="clear:both">
</div>
<?php
session_start();
?>
<?php
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
$create_result = false;

if (isset($_POST['create']) && isset($_POST['subdir']) && !empty($_POST['subdir'])) {
	$d = $current . "/" . $_POST['subdir'];
	$create_result = mkdir($d);
	if ($create_result == TRUE) {
		chmod($d, 0777);
	}
}

?>
<!--
<body onload='window.opener.document.forms[0].savedir.value="<?php echo $current;?>";'>
-->
<body>
<form method=post>
Current directory: &nbsp;
<input type=text readonly value="<?php echo $current;?>">
<ul>
<br>
<?php
$list = scandir($current);
foreach ($list as $item) {
	switch ($item) {
	case ".":
		break;
	case "..":
			echo "<a href=" . $_SERVER['PHP_SELF'] . "?current=" . dirname($current) . ">" . "{up a level}</a><br><br>";
		break;
	default:
		$full_path = $current . "/" . $item;
		if (is_dir($full_path)) {
			echo "<a href=" . $_SERVER['PHP_SELF'] . "?current=" . $current . "/" . $item . ">" . $item . "</a><br>";
		}
		break;
	}
}
?>
</ul>
<br>
<table>
<tr>
<td>
Create new subdirectory<?php
if (isset($_POST['create']) && !$create_result) {
	echo " {failed} ";
}
?>:
</td>
<td>
<input type=text name=subdir<?php if (isset($_POST['create'])) echo ' value="' . $_POST['subdir'] . '"';?>>
</td>
</tr>
</table>
<p>
</p>
<input id='set' type=submit name=select value=Select onClick='window.opener.document.forms[0].savedir.value="<?php echo $current;?>";top.close(); return false;'>
<input id='set' type=submit name=create value=Create>
<input id='set' type=submit name=cancel value=Cancel onClick='top.close(); return false;'>
</form>
</body>
</html>
