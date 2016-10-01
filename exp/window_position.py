
import matplotlib
# matplotlib.use("Qt4Agg") #
#matplotlib.use("TkAgg")
matplotlib.rcParams['toolbar'] = 'None'
import matplotlib.pyplot as plt


plt.figure()
# plt.get_current_fig_manager().window.setGeometry(50,50,200,150) # for QT backend
plt.get_current_fig_manager().window.wm_geometry("200x400+400+0") # tk backend
#plt.get_current_fig_manager().window.SetPosition((500, 0)) # wx backend


plt.plot([0,1,2,0,1,2]) # draw something
plt.show()

