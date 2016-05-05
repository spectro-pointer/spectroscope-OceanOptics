<?php
include_once 'common.php';
function gettarget() {
	global $ip_address, $port, $cmd_get_target_url, $no_parameters;

	$socket = connect_or_abort($ip_address, $port);
    
	$msg = $cmd_get_target_url . $no_parameters;
	
	socket_write_all($socket, $msg);

	$response_string = socket_read_all($socket);
	socket_close($socket);

	$result_code = extract_result_code_from_response($response_string);

	return $response_string;
}
?>
