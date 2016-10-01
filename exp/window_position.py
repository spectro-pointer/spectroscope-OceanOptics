
# the following solution works with QT backends
import matplotlib
matplotlib.use("Qt4Agg") # set the backend
import matplotlib.pyplot as plt

plt.figure()
plt.get_current_fig_manager().window.setGeometry(50,50,200,150)

plt.plot([0,1,2,0,1,2]) # draw something
plt.show()





