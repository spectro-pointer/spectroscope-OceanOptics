#!/usr/bin/env python3
# -*- coding: UTF-8 -*-

import os
import threading
from datetime import datetime
from spectrometer3 import Spectrometer

class Thread(threading.Thread):
    """A stoppable subclass of threading.Thread"""

    def __init__(self):
        threading.Thread.__init__(self)
        self.shallStop = False

    def __enter__(self):
        return self

    def __exit__(self, exc_type, exc_val, exc_tb):
        self.shutdown()

    def shutdown(self):
        self.shallStop = True
        self.join()

class Detector(Thread):
    DEFAULT_INTEGRATION_TIME    = 1.    # [seconds]
    DEFAULT_INTEGRATION_FACTOR  = 1./2  # For increasing/decreasing integration time
    DEFAULT_THRESHOLD           = 25000  # Over baseline

    MAX_INTEGRATION_TIME        = 60.
    DEFECTS = (1,)  # defective pixels
    def __init__(self, ip, port=1865, debug_mode=True):
        Thread.__init__(self)
        self.cv = threading.Condition()
        
        self._spectrometer = Spectrometer(ip, port, debug_mode=debug_mode)
        self.SERIAL = self._spectrometer.get_serial()
#        print('Serial:', self.SERIAL)
#        print('Version:', self.spectrometer.get_version())
        self.DEFAULT_LOCATION     = self._spectrometer.get_save_location()
        print('Save location:', self.DEFAULT_LOCATION)

        self.MIN_INTEGRATION_TIME = float(self._spectrometer.get_min_integration())/1e6
#        self.MAX_INTEGRATION_TIME = float(self._spectrometer.get_max_integration())/1e6
        print('Max integration time:', self.MAX_INTEGRATION_TIME)
        
        self.MAX_INTENSITY        = int(self._spectrometer.get_max_intensity())
        print('Max intensity:', self.MAX_INTENSITY,'\n')

        self._location          = self.DEFAULT_LOCATION

        self._threshold         = self.DEFAULT_THRESHOLD
        self._saturation        = self.MAX_INTENSITY-1
        
        self._integration_time  = self.DEFAULT_INTEGRATION_TIME
        self._integration_factor = self.DEFAULT_INTEGRATION_FACTOR

        self._spectrometer.set_integration(self._integration_time*1e6)
        
        self._wavelengths = tuple(w for w in self._spectrometer.get_wavelengths().split())

        self._last_spectrum = []

        self.started = False
        
        self.setDaemon(True)
        Thread.start(self)
    
    @property
    def location(self):
        '''Gets/Sets directory for saving spectrums

        '''
        return (self._location)

    @location.setter
    def location(self, location):
        self._location = location

    @property
    def integration_time(self):
        '''Gets/Sets integration time [seconds]

        '''
        return (self._integration_time)

    @integration_time.setter
    def integration_time(self, integration_time):
        assert self.MIN_INTEGRATION_TIME <= integration_time <= self.MAX_INTEGRATION_TIME
        self._integration_time = integration_time
        
        self._spectrometer.set_integration(self._integration_time*1e6)

    @property
    def threshold(self):
        '''Gets/Sets intensity threshold

        '''
        return (self._threshold)

    @threshold.setter
    def threshold(self, threshold):
        assert 0 <= threshold <= self.MAX_INTENSITY
        self._threshold = threshold

    @property
    def saturation(self):
        '''Gets/Sets saturation intensity threshold

        '''
        return (self._saturation)

    @saturation.setter
    def saturation(self, saturation):
        assert 0 <= saturation <= self.MAX_INTENSITY
        self._saturation = saturation

    @property
    def integration_factor(self):
        '''Gets/Sets the integration factor for sensitivity adjustment

        '''
        return (self._integration_factor)

    @integration_factor.setter
    def integration_factor(self, integration_factor):
        assert 0. < integration_factor < 1.
        self._integration_factor = integration_factor

    def _save_spectrum(self, path, spectrum):
        '''
            Saves a spectrum to a file with a timestamp 
            Header:
            Serial Number: S07482
            Integration time: 1000000
            Wavelengths Intensities
        '''
        f = os.path.join(path, '%s.txt' % datetime.strftime(datetime.now(), '%d-%m-%Y_%H:%M:%S'))
        print("Saving in '%s'\n" % f)
        with open(f, 'w') as dst:
            print('Serial Number:', self.SERIAL, file=dst)
            print('Integration time: %d' % (self._integration_time*1e6), file=dst)
            print('Wavelength Intensities', file=dst)
            for i, s in enumerate(spectrum):
                print('%s	%.8f' % (self._wavelengths[i], s), file=dst)
                
    def shutdown(self):
        Thread.shutdown(self)

    def start(self):
        with self.cv:
            self.started = True
            self.cv.notifyAll()

    def stop(self):
        with self.cv:
            self.started = False
            self.cv.notifyAll()
        
    def run(self):
        while self.shallStop is False:
            with self.cv:
                while self.started is False:
                        self.cv.wait(1)
                if self.shallStop:
                    break
            while self.started is True:
                with self.cv:
                    if self.shallStop or self.started is False:
                            break
                # processing
                spectrum = self._spectrometer.get_spectrum()
                if spectrum is None:
                    print('Warning: no spectrum')
                    continue
                spectrum = [float(v) for v in spectrum.split()]
                for d in self.DEFECTS:
                    spectrum[1] = 0.
                MIN = min(spectrum)
        #        MEAN = sum(spectrum)/len(spectrum)
                # Saturation detection
                MAX = max(spectrum)
                if MAX >= self._saturation:
                    self._integration_time *= self._integration_factor
                    print('Saturation: %d. Lowering integration time: %f' % (MAX, self._integration_time))
                    self._spectrometer.set_integration(self._integration_time*1e6)
                    continue
                # Baseline reduction
                spectrum = [v-MIN for v in spectrum]
                # Detection
                MAX -= MIN
                if MAX > self._threshold: # Detection
                    # Save spectrum
                    print('Detection: %d' % MAX)
                    self._save_spectrum(self._location, spectrum)
                else:
                    # Increase integration time
                    self._integration_time /= self._integration_factor
                    if self._integration_time > self.MAX_INTEGRATION_TIME:
                        self._integration_time = self.MAX_INTEGRATION_TIME
                    print('No detection: %d. Integration time: %f' % (MAX, self._integration_time))
                    self._spectrometer.set_integration(self._integration_time*1e6)
                    continue
                self._last_spectrum = spectrum
    #    print('done.\nCurrent status:', spectrometer.get_current_status())

    def get_last_spectrum(self):
        return self._last_spectrum

    def get_wavelengths(self):
        return self._wavelengths

if __name__ == '__main__':
    from time import sleep

    # server address
    ip_address = 'localhost'
#    port = 1865
#    integration_time = 1. # [seconds]
#    max_integration_time = 5.

    detector = Detector(ip_address,debug_mode=True)
    location = '/home/pi/spectrometer/spectrums'
    detector.location = location
    
#    detector.integration_time = integration_time
    print('Integration time: %s s\n' % detector.integration_time)

    detector.start()
    
    while True:
        sleep(1)

    detector.stop()
