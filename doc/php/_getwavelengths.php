<?php
include_once 'common.php';

function getwavelengths($channel = 0) {

	global $ip_address, $port, $cmd_get_wavelengths;

	$socket = connect_or_abort($ip_address, $port);

    $arguments = $channel . ";";
    $msg = $cmd_get_wavelengths . string_length_bytes($arguments);
    $msg = $msg . $arguments;

	socket_write_all($socket, $msg);

	// we are expecting 2 bytes result code
	$out = "";
	$result = socket_read_n($socket, $out, 2);
	$res0 = ord($out[0]) << 8;
	$res1 = ord($out[1]);
	$result_code = $res0 | $res1;


	// now we are expecting 4 bytes with the result length
	$out = "";
	$result = socket_read_n($socket, $out, 4);
	$b0 = ord($out[0]) << 24;
	$b1 = ord($out[1]) << 16;
	$b2 = ord($out[2]) << 8;
	$b3 = ord($out[3]);
	$result_length = $b0 | $b1 | $b2 | $b3;

	$result = socket_read_n($socket, $wavelength_string, $result_length);
	socket_close($socket);

	return $wavelength_string;
}
?>
