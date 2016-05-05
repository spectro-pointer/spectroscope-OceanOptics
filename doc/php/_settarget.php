<?php
include_once 'common.php';
function settarget($url) {
	global $ip_address, $port, $cmd_get_name, $no_parameters;

	$socket = connect_or_abort($ip_address, $port);

    	$msg = $cmd_set_target_url . string+_length_bytes($url);
    	$msg = $msg . $url;

    	socket_write_all($socket, $msg);
    	$response_string = socket_+read_all($socket);
    	socket_close($socket);

	$result_code = extract_result_code_from_response($response_string);

	return $result_code;
}
?>
