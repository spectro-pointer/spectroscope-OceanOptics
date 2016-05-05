<html>
<head>
<link rel="stylesheet" type="text/css" href="/css/ocean.css">
<meta charset="utf-8">
<?php
include '_getsequencestate.php';

$channel = 0;
if (isset($_GET['channel'])) {
    $channel = $_GET['channel'];
}

$running = true;
if (getsequencestate($channel) == "not_yet_started") {
	$running = false;
}
if (isset($_GET['refresh']) && is_numeric($_GET['refresh']) && $running) {
	echo '<meta http-equiv="refresh" content="' . $_GET['refresh'] . '">';
}

$height = 480;
$xMargin = 25;
$yMargin = 60;
$width = 640;
?>

<style media="screen">
 #drawing_board {
    height: <?php echo ($height + $xMargin); ?>px;
    width: <?php echo ($width + $yMargin); ?>px;
    border:1px solid #000000;
}
</style>
</head>

<body>
<!--
<img class="logo" src="/images/oo.gif">
<span style="border:2px solid grey;padding: 20px;font-size: 24px;font-family:sans-serif;font-weight:bold;color:white;background-color:#005aab; margin:20px; textalign:bottom">
WIFI Spectrometer Control
</span>
<p>
</p>
-->
<div style="clear:both">
</div>
<!--
<p>
Scope Mode Spectrum
-->
<?php
if ($_GET['mode'] == 'sequence' && !$running) {
	echo ": sequence has stopped";
}
?>
<!--
</p>
-->
<?php
	include '_currentspectrum.php';
	include '_getspectrum.php';
	include '_getwavelengths.php';
	include '_getmaxintensity.php';

	$maxIntensity = getmaxintensity($channel);
	$wavelengths = explode(" ", getwavelengths($channel));
	$pixels = count($wavelengths);
  $range = $wavelengths[$pixels - 1] - $wavelengths[0];
  if (isset($_GET['mode']) && $_GET['mode'] == "immediate") {
    $spec = getspectrum($channel);
  }
  else {
    $spec = currentspectrum($channel);
  }
  $counts = explode(" ", $spec);

	echo '<script>';
	echo file_get_contents('raphael.js');
	echo '</script>';

    	echo '<script>
    	window.onload = function() {
		var paper = Raphael("drawing_board");
		var xLabel = paper.text(' . ($width / 2 + $yMargin) . ', ' . ($height + 20) . ', "Wavelength (nm)");
		var yLabel = paper.text(40, 10, "Intensity");
		var line = paper.path("M' . $yMargin . ' 0';
	$i = 0;
	foreach ($counts as $c) {
		echo 'L' . ($i * $width / $pixels + $yMargin) . ' ' . ($height - $c / $maxIntensity * $height);
		$i++;
	}
	echo '");';
	echo 'paper.path("M' . $yMargin . ' ' . $height . 'L' . ($width + $yMargin) . ' ' . $height . '");';
	echo 'paper.path("M' . $yMargin . ' ' . $height . 'L' . $yMargin . ' 0");';
	$s = 0;
	for ($s = 0; $s < $maxIntensity; $s += 5000) {
		$y = $height - $s / $maxIntensity * $height;
		echo 'paper.text(30, ' . $y . ', "' . $s . '");';
	}
	$w = 0;
	for ($w = 0; $w < 5; $w++) {
		$p = $pixels / 5 * $w;
		$x = $p * $width / $pixels;
		echo 'paper.text(' . ($x + $yMargin) . ', ' . ($height + 10) . ', "' . (5 * round($wavelengths[$p]/5)) . '");';
	}
    	echo '};</script>';
	echo '<div id="drawing_board"></div>';
?>
</body>
</html>
