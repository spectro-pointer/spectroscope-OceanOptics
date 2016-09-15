#!/usr/bin/python
import RPi.GPIO as GPIO, time, os, subprocess

from detector import Detector

ip = 'localhost'
det = Detector(ip)

location = '/home/pi/spectrometer/spectrums'
det.location = location

# Use the Broadcom SOC Pin numbers
# Setup the Pin with Internal pullups enabled and PIN in reading mode.
GPIO.setmode(GPIO.BCM)
gpio_start = 17
gpio_stop  = 18
GPIO.setup(gpio_start, GPIO.IN, pull_up_down = GPIO.PUD_UP)
GPIO.setup(gpio_stop , GPIO.IN, pull_up_down = GPIO.PUD_UP)

# Our function on what to do when the button is pressed
def Start(channel):
#	subprocess.call(['spectrometer.py'])
    print "start"
    det.start()

def Stop(channel):
#	subprocess.call(['spectrometer.py'])
    print "stop"
    det.stop()

# Add our function to execute when the button pressed event happens
GPIO.add_event_detect(gpio_start, GPIO.FALLING, callback = Start)
GPIO.add_event_detect(gpio_stop , GPIO.FALLING, callback = Stop )

# Now wait!
while True:
    time.sleep(1)
