from spectrometer3 import Spectrometer
from exp.outputs.load_data import LoadData
from time import sleep


class MockSpectrometer(Spectrometer):

    def __init__(self, ip_address, port=1865, channel=0):
        super().__init__(ip_address, port, channel)
        self.load_data = LoadData()

    def _connect_or_abort(self, ip_address, port):
        print("Using mock spectrometer")

    def _send_command(self, cmd, *args):
        return 0

    def _send_command_n(self, cmd, *args):
        return 0

    def get_serial(self):
        return 'DEBUG_MODE'

    def get_spectrum(self):
        sleep(self.load_data.get_integration())
        return self.load_data.get_spectrum()

    def get_wavelengths(self):
        return self.load_data.get_wavelengths()

    def get_min_integration(self):
        return self.load_data.get_min_integration()

    def get_max_intensity(self):
        return self.load_data.get_max_intensity()

    def set_integration(self, microseconds):
        self.load_data.set_integration(microseconds / 1e6)
