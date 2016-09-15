from pylab import *
import sys

subplot(2,1,1)
wavelengths=[]
intensities=[]
with open(sys.argv[1], "r") as capture:
	i=0
	for line in capture:	
		line=line.split()
		if(i > 6):
			if(len(line)>1):
				wavelengths.append(float(line[0]))
				intensities.append(float(line[1]))
		i+=1
n = len(wavelengths)
X = wavelengths
Y = intensities
plot (X, Y, color='blue', alpha=1.00)
xlim(300,1000)

if(len(sys.argv)>2):
	subplot(2,1,2)
	wavelengths2=[]
	intensities2=[]
	with open(sys.argv[2], "r") as capture2:
		i=0
		for line in capture2:	
			line=line.split()
			if(i > 6):
				if(len(line)>1):
					wavelengths2.append(float(line[0]))
					intensities2.append(float(line[1]))
			i+=1
	n = len(wavelengths2)
	X2 = wavelengths2
	Y2 = intensities2
	plot (X2, Y2, color='blue', alpha=1.00)
	xlim(300,1000)

show()

