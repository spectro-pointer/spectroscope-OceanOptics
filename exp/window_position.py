import matplotlib
matplotlib.use("Qt4Agg") # set the backend
import matplotlib.pyplot as plt

plt.figure()
plt.plot([0,1,2,0,1,2]) # draw something
plt.show(block=False)

plt.get_current_fig_manager().window.setGeometry(600,400,1000,800)
