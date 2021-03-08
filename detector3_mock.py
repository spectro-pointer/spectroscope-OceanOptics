from detector3 import Detector
from spectrometer3 import Spectrometer
from spectrometer3_mock import MockSpectrometer

class MockDetector(Detector):
    def button_start(self):
        print("GPIO START event")
        self._gpio_started = True

    def configure_gpio(self):
        self.button_start()


if __name__ == '__main__':
    from time import sleep

    # server address
    ip_address = 'localhost'

    detector = MockDetector(ip_address)
    location = '/home/pi/spectrometer/spectrums'
    detector.location = location

    #    detector.integration_time = integration_time
    print('Integration time: %s s\n' % detector.integration_time)

    detector.start()

    while True:
        sleep(1)

    detector.stop()
