#!/usr/bin/env python3
# -*- coding: UTF-8 -*-
"""
	OceanOptics Spectrometer python client
	
	(C) 2016 Mauro Lacy <mauro@lacy.com.ar>
	
    This is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this software.  If not, see <http://www.gnu.org/licenses/>.

"""
__version__ = '0.5'

import socket
from sys import exit
from time import sleep
from datetime import datetime
import os.path
import struct

# server address
ip_address = '127.0.0.1'
port = 1865

class Spectrometer(object):
	# command constants
	cmd_set_integration_time = "\x00\x01"
	cmd_get_integration_time = "\x00\x02"
	cmd_set_boxcar_width = "\x00\x03"
	cmd_get_boxcar_width = "\x00\x04"
	cmd_set_scans_to_average = "\x00\x05"
	cmd_get_scans_to_average = "\x00\x06"
	cmd_set_target_url = "\x00\x07"
	cmd_get_target_url = "\x00\x08"
	cmd_get_spectrum = "\x00\x09"
	cmd_get_wavelengths = "\x00\x0A"
	cmd_get_serial_number = "\x00\x0B"
	cmd_get_name = "\x00\x0C"
	cmd_get_version = "\x00\x0D"
	cmd_get_calibration_coefficients_from_buffer = "\x00\x0E"
	cmd_set_calibration_coefficients_to_buffer = "\x00\x0F"
	cmd_get_calibration_coefficients_from_eeprom = "\x00\x10"
	cmd_set_calibration_coefficients_to_eeprom = "\x00\x11"
	cmd_get_pixel_binning_factor = "\x00\x12"
	cmd_set_pixel_binning_factor = "\x00\x13"
	cmd_get_integration_time_minimum = "\x00\x14"
	cmd_get_integration_time_maximum = "\x00\x15"
	cmd_get_intensity_maximum = "\x00\x16"
	cmd_get_electric_dark_correction = "\x00\x17"
	cmd_set_electric_dark_correction = "\x00\x18"
	cmd_set_tec_enable = "\x00\x1A"
	cmd_set_tec_temperature = "\x00\x1B"
	cmd_get_tec_temperature = "\x00\x1C"
	cmd_set_lamp_enable = "\x00\x1D"

	# OceanHandler commands  
	cmd_get_current_status = "\x00\x20"
	cmd_get_current_spectrum = "\x00\x21"
	cmd_set_max_acquisitions = "\x00\x22"
	cmd_get_max_acquisitions = "\x00\x23"
	cmd_set_file_save_mode = "\x00\x24"
	cmd_get_file_save_mode = "\x00\x25"
	cmd_set_file_prefix = "\x00\x26"
	cmd_get_file_prefix = "\x00\x27"
	cmd_set_sequence_type = "\x00\x28"
	cmd_get_sequence_type = "\x00\x29"
	cmd_set_sequence_interval = "\x00\x2A"
	cmd_get_sequence_interval = "\x00\x2B"
	cmd_set_save_directory = "\x00\x2C"
	cmd_get_save_directory = "\x00\x2D"
	cmd_save_spectrum = "\x00\x2E"
	cmd_start_sequence = "\x00\x2F"
	cmd_pause_sequence = "\x00\x30"
	cmd_resume_sequence = "\x00\x31"
	cmd_stop_sequence = "\x00\x32"
	cmd_get_sequence_state = "\x00\x33"
	cmd_get_current_sequence_number = "\x00\x34"
	cmd_set_scope_mode = "\x00\x35"
	cmd_get_scope_mode = "\x00\x36"
	cmd_set_scope_interval = "\x00\x37"
	cmd_get_scope_interval = "\x00\x38"

	# convenience constant for null/no parameters to a command
	no_parameters = "\x00\x00"

	def __init__(self, ip_address, port=port, channel=0, debug_mode=False):
		# connect timeout
		self.timeout = 85
		# default refresh interval for scope mode
		self.default_scope_interval = 5
		
		self.ip_address = ip_address
		self.port = port
		self.debug_mode = debug_mode
		self.channel=channel
		
		if self.debug_mode:
			from exp.outputs.load_data import LoadData
			self.load_data = LoadData()
		else:
			self.sock = self._connect_or_abort(ip_address, port)

	def _connect_or_abort(self, ip_address, port):
		sock = socket.create_connection((ip_address, port), timeout=self.timeout)
		if sock is None: 
			print("socket: connect failed.")
			exit(1)
		return sock

	def _string_length_bytes(self, s):
		l = len(s)
		hi = l & 0xFF00
		hi = hi >> 8
		lo = l & 0xFF
		result = chr(hi) + chr(lo)
		return result

	def _socket_write_all(self, buffer):
		buffer_length = len(buffer)
		total_sent = 0
		while (total_sent < buffer_length):
			count = self.sock.send(buffer[total_sent:]) 
			total_sent += count
		if count == None: 
			total_sent = None
		return total_sent

	def _socket_read_all(self):
		response_string = ''
		out = True
		while (out):
			out = self.sock.recv(2048) 
			response_string += str(out, 'iso8859-1')
		return response_string

	def _socket_read_n(self, expected_length):
		remaining = expected_length
		response_string = ''
		chunk = True
		while (chunk):
			chunk = self.sock.recv(remaining)
			out = str(chunk, 'iso8859-1') 
			response_string += out
			remaining -= len(out)
		return response_string, expected_length - remaining

	def _extract_result_code_from_response(self, response_string):
		result = ord(response_string[0]) * 256 + ord(response_string[1]);
		response_string = response_string[2:]
		return result, response_string
	
	def _close(self):
		self.sock.close()
		self.sock = None
	
	def _build_command(self, cmd, *args):
		msg = bytearray(cmd, 'iso8859-1')
		arguments = ''
		if len(args):
			for i, a in enumerate(args):
				arguments += str(a)
				if len(args) == 1 or i < len(args)-1:
					arguments += ';'
			msg += struct.pack('!H', len(arguments));
		else:
			arguments = self.no_parameters
		msg += bytearray(arguments, 'iso8859-1');
		return msg
	
	def _send_command(self, cmd, *args):
		if self.debug_mode:
			return 0

		if self.sock is None:
			self.sock = self._connect_or_abort(ip_address, port)
		msg = self._build_command(cmd, *args)

		self._socket_write_all(msg)
		
		result = self._socket_read_all()
		
		self._close()
		
		code = 0
		if len(result) >= 2:
			code, result = self._extract_result_code_from_response(result)
		return code if len(result) == 2 else result if code == 1 else None # FIXME?: what are valid code values?

	def _send_command_n(self, cmd, *args):
		if self.debug_mode:
			return 0
			
		if self.sock is None:
			self.sock = self._connect_or_abort(ip_address, port)

		msg = self._build_command(cmd, *args)

		self._socket_write_all(msg)

		output_string = None

		# we are expecting 2 bytes result code
		result_code = 0
		out, result = self._socket_read_n(2)
		if result == 2:
			res0 = ord(out[0]) << 8
			res1 = ord(out[1])
			result_code = res0 | res1
			
			# now we are expecting 4 bytes with the result length
			out, result = self._socket_read_n(4)
			if result == 4:
				b0 = ord(out[0]) << 24
				b1 = ord(out[1]) << 16
				b2 = ord(out[2]) << 8
				b3 = ord(out[3])
				result_length = b0 | b1 | b2 | b3
		
				output_string, _ = self._socket_read_n(result_length)
		self._close()
		
		return output_string, result_code

	def get_version(self):
		return self._send_command(self.cmd_get_version)

	def get_serial(self):
		if self.debug_mode:
			return 'DEBUG_MODE'
		else:
			return self._send_command(self.cmd_get_serial_number, self.channel)

	def get_current_status(self):
		return self._send_command(self.cmd_get_current_status, self.channel)

	def get_integration(self):
		return self._send_command(self.cmd_get_integration_time, self.channel)
	
	def get_get_boxcar(self):
		return self._send_command(self.cmd_get_boxcar_width, self.channel)
	
	def get_average(self):
		return self._send_command(self.cmd_get_scans_to_average, self.channel)

	def get_target(self):
		return self._send_command(self.cmd_get_target_url)

	def get_spectrum(self):
		if self.debug_mode:
			sleep(self.load_data.get_integration())
			return self.load_data.get_spectrum()
		else:
			return self._send_command_n(self.cmd_get_spectrum, self.channel)[0]

	def get_wavelengths(self):
		if self.debug_mode:
			return self.load_data.get_wavelengths()
		else:
			return self._send_command_n(self.cmd_get_wavelengths, self.channel)[0]

	def get_name(self):
		return self._send_command(self.cmd_get_name, self.channel)

	def get_calibration_buffer(self):
		return self._send_command_n(self.cmd_get_calibration_coefficients_from_buffer, self.channel)[0]

	def get_calibration_eeprom(self):
		return self._send_command_n(self.cmd_get_calibration_coefficients_from_eeprom, self.channel)[0]

	def get_binning(self):
		return self._send_command(self.cmd_get_pixel_binning_factor, self.channel)

	def get_min_integration(self):
		if self.debug_mode:
			return self.load_data.get_min_integration()
		else: 
			return self._send_command(self.cmd_get_integration_time_minimum, self.channel)

	def get_max_integration(self):
		return self._send_command(self.cmd_get_integration_time_maximum, self.channel)

	def get_max_intensity(self):
		if self.debug_mode:
			return self.load_data.get_max_intensity()
		else:
			return self._send_command(self.cmd_get_intensity_maximum, self.channel)

	def get_e_d_correct(self):
		return self._send_command(self.cmd_get_electric_dark_correction, self.channel)

	def get_tec_temperature(self):
		return self._send_command(self.cmd_get_tec_temperature, self.channel)

	def current_spectrum(self):
		return self._send_command_n(self.cmd_get_current_spectrum)[0]

	def get_max_acquisitions(self):
		return self._send_command(self.cmd_get_max_acquisitions, self.channel)

	def get_save_mode(self):
		return self._send_command(self.cmd_get_file_save_mode, self.channel)

	def get_prefix(self):
		return self._send_command(self.cmd_get_file_prefix, self.channel)

	def get_sequence_type(self):
		return self._send_command(self.cmd_get_sequence_type, self.channel)

	def get_sequence_interval(self):
		return self._send_command(self.cmd_get_sequence_interval, self.channel)

	def get_save_location(self):
		return self._send_command(self.cmd_get_save_directory, self.channel)

	def get_sequence_state(self):
		return self._send_command(self.cmd_get_sequence_state, self.channel)

	def get_sequence_number(self):
		return self._send_command(self.cmd_get_current_sequence_number, self.channel)

	def get_scope_mode(self):
		return self._send_command(self.cmd_get_scope_mode, self.channel)

	def get_scope_interval(self):
		return self._send_command(self.cmd_get_scope_interval, self.channel)

	def set_integration(self, microseconds):
		if self.debug_mode:
			self.load_data.set_integration(microseconds/1e6)
		else:
			return self._send_command(self.cmd_set_integration_time, self.channel, int(microseconds))

	def set_boxcar(self, width):
		return self._send_command(self.cmd_set_boxcar_width, self.channel, width)

	def set_average(self, scans):
		return self._send_command(self.cmd_set_scans_to_average, self.channel, scans)
		
	def set_target(self, url):
		return self._send_command(self.cmd_set_target_url, url)
	
	def set_calibration_buffer(self, calibration):
		return self._send_command_n(self.cmd_set_calibration_coefficients_to_buffer,
								self.channel, self.calibration)[1]
	
	def set_calibration_eeprom(self, calibration):
		return self._send_command_n(self.cmd_set_calibration_coefficients_to_eeprom,
								self.channel, self.calibration)[1]
	
	def set_binning(self, bins):	
		return self._send_command(self.cmd_set_pixel_binning_factor,

								self.channel, bins)
	
	def set_e_d_correct(self, electric):
		return self._send_command(self.cmd_set_electric_dark_correction, self.channel, electric)
	
	def set_tec_enable(self, enable):
		return self._send_command(self.cmd_set_tec_enable, self.channel, enable)
	
	def set_tec_temperature(self, temperature):
		return self._send_command(self.cmd_set_tec_temperature, self.channel, temperature)
	
	def set_lamp_enable(self, enable):
		return self._send_command(self.cmd_set_lamp_enable, self.channel, enable)
	
	def set_max_acquisitions(self, acquisitions):
		return self._send_command(self.cmd_set_max_acquisitions, self.channel, acquisitions)
	
	def set_save_mode(self, mode):
		return self._send_command(self.cmd_set_file_save_mode, self.channel, mode)
	
	def set_prefix(self, prefix):
		return self._send_command(self.cmd_set_file_prefix, self.channel, prefix)
	
	def set_sequence_type(self, t):
		return self._send_command(self.cmd_set_sequence_type, self.channel, t)
	
	def set_sequence_interval(self, interval):
		return self._send_command(self.cmd_set_sequence_interval, self.channel, interval)
	
	def set_save_location(self, location):
		return self._send_command(self.cmd_set_save_directory, self.channel, location)
	
	def set_scope_mode(self, mode):
		return self._send_command(self.cmd_set_scope_mode, self.channel, mode)
	
	def set_scope_interval(self, interval):
		return self._send_command(self.cmd_set_scope_interval, self.channel, interval)
	
	def save_spectrum(self, location):
		return self._send_command(self.cmd_save_spectrum, self.channel, location)
	
	def start_sequence(self):
		return self._send_command(self.cmd_start_sequence, self.channel)
	
	def pause_sequence(self): 	
		return self._send_command(self.cmd_pause_sequence, self.channel)
	
	def resume_sequence(self):
		return self._send_command(self.cmd_resume_sequence, self.channel)
	
	def stop_sequence(self):
		return self._send_command(self.cmd_stop_sequence, self.channel)

if __name__ == '__main__':
	integration_time = 1 # [seconds]
	location = '/home/pi/spectrometer/spectrums'
#	prefix = 'spectrum'
	
	spectrometer = Spectrometer(ip_address, port)
	print('Version:', spectrometer.get_version())
	print('Serial:', spectrometer.get_serial())
	spectrometer.set_integration(integration_time*1e6)
	print('Integration time: %s µs' % spectrometer.get_integration())

	# 1) start/stop sequence
#	spectrometer.set_save_location(location)
#	print('Save location:', spectrometer.get_save_location())
#	spectrometer.set_prefix(prefix)
#	print('File prefix:', spectrometer.get_prefix())
	
#	print('Start Sequence:', spectrometer.start_sequence())
#	sleep(integration_time+1)
#	print('Stop Sequence:', spectrometer.stop_sequence())

	# 2) save_spectrum
	file = os.path.join(location, '%s.txt' % datetime.strftime(datetime.now(), '%d-%m-%Y_%H:%M:%S'))
	print('Saving spectrum:', file)
	spectrometer.save_spectrum(file)
	# 3) get_spectrum
#	print('Getting spectrum...')
#	print('Spectrum:')
#	spectrum = spectrometer.get_spectrum()
#	spectrum = [v for v in spectrum.split()]

	print('done.\nCurrent status:', spectrometer.get_current_status())
