#!/usr/bin/env python3
# -*- coding: UTF-8 -*-

import os
from datetime import datetime
from spectrometer import Spectrometer

def save_spectrum(path, spectrum):
    '''
        Saves spectrum to file with a timestamp 
    '''
    f = os.path.join(path, '%s.txt' % datetime.strftime(datetime.now(), '%d-%m-%Y_%H:%M:%S'))
    print("Saving in '%s'" % f)
    min_wavelength = 337.35134888
    max_wavelength = 822.96687986
    steps=1024
    step = (max_wavelength-min_wavelength)/steps
    wavelength=min_wavelength
    with open(f, 'w') as dst:
        for i in spectrum:
            print('%.8f	%.8f' % (wavelength, i), file=dst)
            wavelength += step

if __name__ == '__main__':
    # server address
    ip_address = '192.168.2.154'
    port = 1865
    integration_time = 1. # [seconds]
    max_integration_time = 5.

    spectrometer = Spectrometer(ip_address, port)
    location = '/home/pi/spectrometer/spectrums'
    print('Version:', spectrometer.get_version())
    print('Serial:', spectrometer.get_serial())
    spectrometer.set_integration(integration_time*1e6)
    print('Integration time: %s µs' % spectrometer.get_integration())

    # 4) continuous spectrum
    THRESHOLD=2000 # over baseline
    SATURATION=16000
    saturation_factor = 0.5
    while True:
        spectrum = spectrometer.get_spectrum()
        if spectrum is None:
            print('Warning: no spectrum')
            continue
        spectrum = [int(v) for v in spectrum.split()]
        MIN = min(spectrum)
#        MEAN = sum(spectrum)/len(spectrum)
        # Saturation detection
        MAX = max(spectrum)
        if MAX >= SATURATION:
            integration_time *= saturation_factor
            print('Saturation: %d. Lowering integration time: %f' % (MAX, integration_time))
            spectrometer.set_integration(integration_time*1e6)
            continue
        # Baseline reduction
        spectrum = [v-MIN for v in spectrum]
        # Detection
        MAX -= MIN
        if MAX > THRESHOLD: # Detection
            # Save spectrum
            print('Detection: %d' % MAX)
            save_spectrum(location, spectrum)
        else:
            # Increase integration time
            integration_time /= saturation_factor
            if integration_time > max_integration_time:
                integration_time = max_integration_time
            print('No detection: %d. Integration time: %f' % (MAX, integration_time))
            spectrometer.set_integration(integration_time*1e6)
            continue
#    print('done.\nCurrent status:', spectrometer.get_current_status())
