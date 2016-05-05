<?php

// daemon constants
$ip_address = "192.168.2.205";
$port = 1865;

// command constants
$cmd_set_integration_time = "\x00\x01";
$cmd_get_integration_time = "\x00\x02";
$cmd_set_boxcar_width = "\x00\x03";
$cmd_get_boxcar_width = "\x00\x04";
$cmd_set_scans_to_average = "\x00\x05";
$cmd_get_scans_to_average = "\x00\x06";
$cmd_set_target_url = "\x00\x07";
$cmd_get_target_url = "\x00\x08";
$cmd_get_spectrum = "\x00\x09";
$cmd_get_wavelengths = "\x00\x0A";
$cmd_get_serial_number = "\x00\x0B";
$cmd_get_name = "\x00\x0C";
$cmd_get_version = "\x00\x0D";
$cmd_get_calibration_coefficients_from_buffer = "\x00\x0E";
$cmd_set_calibration_coefficients_to_buffer = "\x00\x0F";
$cmd_get_calibration_coefficients_from_eeprom = "\x00\x10";
$cmd_set_calibration_coefficients_to_eeprom = "\x00\x11";
$cmd_get_pixel_binning_factor = "\x00\x12";
$cmd_set_pixel_binning_factor = "\x00\x13";
$cmd_get_integration_time_minimum = "\x00\x14";
$cmd_get_integration_time_maximum = "\x00\x15";
$cmd_get_intensity_maximum = "\x00\x16";
$cmd_get_electric_dark_correction = "\x00\x17";
$cmd_set_electric_dark_correction = "\x00\x18";
$cmd_set_tec_enable = "\x00\x1A";
$cmd_set_tec_temperature = "\x00\x1B";
$cmd_get_tec_temperature = "\x00\x1C";
$cmd_set_lamp_enable = "\x00\x1D";

// OceanHandler commands  
$cmd_get_current_status = "\x00\x20";
$cmd_get_current_spectrum = "\x00\x21";
$cmd_set_max_acquisitions = "\x00\x22";
$cmd_get_max_acquisitions = "\x00\x23";
$cmd_set_file_save_mode = "\x00\x24";
$cmd_get_file_save_mode = "\x00\x25";
$cmd_set_file_prefix = "\x00\x26";
$cmd_get_file_prefix = "\x00\x27";
$cmd_set_sequence_type = "\x00\x28";
$cmd_get_sequence_type = "\x00\x29";
$cmd_set_sequence_interval = "\x00\x2A";
$cmd_get_sequence_interval = "\x00\x2B";
$cmd_set_save_directory = "\x00\x2C";
$cmd_get_save_directory = "\x00\x2D";
$cmd_save_spectrum = "\x00\x2E";
$cmd_start_sequence = "\x00\x2F";
$cmd_pause_sequence = "\x00\x30";
$cmd_resume_sequence = "\x00\x31";
$cmd_stop_sequence = "\x00\x32";
$cmd_get_sequence_state = "\x00\x33";
$cmd_get_current_sequence_number = "\x00\x34";
$cmd_set_scope_mode = "\x00\x35";
$cmd_get_scope_mode = "\x00\x36";
$cmd_set_scope_interval = "\x00\x37";
$cmd_get_scope_interval = "\x00\x38";

// convenience constant for null/no parameters to a command
$no_parameters = "\x00\x00";

// timeout for curl commands i.e. response to a target URL
$curl_timeout = 10;

// default refresh interval for scope mode
$default_scope_interval = 5;

function string_length_bytes($s) {

	$l = strlen($s);

	$hi = $l & 0xFF00;
	$hi = $hi >> 8;
	$lo = $l & 0xFF;

	$result = chr($hi) . chr($lo);

	return $result;
}

function connect_or_abort($ip_address, $port) {
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

	if ($socket === false) {
    	echo "socket_create() failed: reason: " . socket_strerror(socket_last_error());
    	exit("aborting...");
	}

	$result = socket_connect($socket, $ip_address, $port);
	if ($result === false) {
    	echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket));
    	exit("aborting...");
	}
	return $socket;
}

function socket_write_all(&$socket, &$buffer) {
	$buffer_length = strlen($buffer);
	$total_sent = 0;
	while ($count = socket_write($socket, substr($buffer, $total_sent))) {
		$total_sent += $count;
	}

	if ($count === FALSE) {
		$total_sent = FALSE;
	}

	return $total_sent;
}

function socket_read_all(&$socket) {
        $response_string = "";
        while ($out = socket_read($socket, 2048)) {
            $response_string = $response_string . $out;
	}
	return $response_string;
}

function socket_read_n(&$socket, &$buffer, $expected_length) {

	$remaining = $expected_length;
	$chunk = socket_read($socket, $remaining);
	while ($chunk != "") {
		$buffer .= $chunk;
		$remaining -= strlen($chunk);
		$chunk = socket_read($socket, $remaining);
	}

	$result = $expected_length - $remaining;

	if ($chunk === FALSE) {
		$result = FALSE;
	}
	return $result;
}

function extract_result_code_from_response(&$response_string) {
	$result = ord($response_string[0]) * 256 + ord($response_string[1]);
	$response_string = substr($response_string, 2);
	return $result;	
}
?>
