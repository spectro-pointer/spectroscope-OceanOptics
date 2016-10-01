
import matplotlib
import os
# matplotlib.use("Qt4Agg") #
#matplotlib.use("TkAgg")
matplotlib.rcParams['toolbar'] = 'None'
stylefile='../misc/rc/probpro'
matplotlib.rc_file(os.path.join(os.path.dirname(__file__), stylefile))
import matplotlib.pyplot as plt


fig=plt.figure()
# plt.get_current_fig_manager().window.setGeometry(50,50,200,150) # for QT backend
plt.get_current_fig_manager().window.wm_geometry("200x400+400+0") # tk backend
#plt.get_current_fig_manager().window.SetPosition((500, 0)) # wx backend


plt.plot([0,1,2,0,1,2]) # draw something
plt.xlabel('wavelenght (nm)')
plt.ylabel('amplitude')
#fig.tight_layout()
plt.show()

