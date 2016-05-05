<?php
include_once 'common.php';

function getboxcar($channel = 0) {
	global $ip_address, $port, $cmd_get_boxcar_width;

	$socket = connect_or_abort($ip_address, $port);

    $arguments = $channel . ";"; 
    $msg = $cmd_get_boxcar_width . string_length_bytes($arguments);
    $msg = $msg . $arguments;

	socket_write_all($socket, $msg);
	$response_string = socket_read_all($socket);
	socket_close($socket);

	$result_code = extract_result_code_from_response($response_string);

	return $response_string;
}
?>
