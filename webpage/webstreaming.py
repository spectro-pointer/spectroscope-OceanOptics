from utils import *
from tracker_lib import *
import threading
import argparse
from flask import Flask, url_for, jsonify
from flask import render_template
from flask_bootstrap import Bootstrap
from flask import request, redirect
#from flask_appconfig import AppConfig
from config import *
from forms import ConfigForm
from time import sleep

def create_app(configfile=None):
    app = Flask(
                __name__,
                template_folder="templates",
                static_folder="static"
                )

    Bootstrap(app)

    app.config['SECRET_KEY'] = 'devkey'
    app.config['RECAPTCHA_PUBLIC_KEY'] = \
        '6Lfol9cSAAAAADAkodaYl9wvQCwBMr3qGR_PPHcw'

    @app.route("/")
    def index():
        # redirects to config page
        return redirect(url_for('set_config'))

    # This function selects between automatic mode and manual mode into the DB
    @app.route('/select_mode', methods = ['POST'])
    def get_post_select_mode_data():
        select_mode = request.form['select_mode']
        print("SELECT_MODE",select_mode)
        return select_mode

    @app.route('/data')
    def spectro_data():
        #--------------------------------------------
        #TODO Change load_data.get_wavelengths by tuple(w for w in self._spectrometer.get_wavelengths().split())
        yAxe = load_data.get_wavelengths()
        return jsonify({'results':yAxe})

    @app.route("/save_spectrum", methods=["GET","POST"])
    def save_spectrum():
        det._save_picture()

    @app.route("/spectroscope", methods=["GET","POST"])
    def set_config_spectrometer():
        form = ConfigForm()

        if request.method == 'POST':
            if form.validate_on_submit():
                # spectro_pointer_config = {}
                #spectro_pointer_config['use_raspberry']             = form.integration_time.data
                #spectro_pointer_config['correct_vertical_camera']   = form.integration_factor.data
                #spectro_pointer_config['correct_horizontal_camera'] = form.threshold.data
                print("INTEGRATION TIME",form.integration_time.data)
                print("INTEGRATION FACTOR",form.integration_factor.data)
                print("THRESHOLD",form.threshold.data)

                # with lock:
                #     set_sp_config(app,**spectro_pointer_config)
                #     update_params(app,set_camera_attr_en=True)

            return redirect(url_for('set_config_spectrometer'))
        else:
            print("INTEGRATION TIME",form.integration_time.data)
            print("INTEGRATION FACTOR",form.integration_factor.data)
            print("THRESHOLD",form.threshold.data)
            form.integration_time.render_kw     = {'value':2000}#get_sp_config('USE_RASPBERRY',app)}
            form.integration_factor.render_kw   = {'value':2000}#get_sp_config('CORRECT_VERTICAL_CAMERA',app)}
            form.threshold.render_kw            = {'value':2000}#get_sp_config('CORRECT_HORIZONTAL_CAMERA',app)}

            form.integration_time.label         = 'INTEGRATION TIME:'
            form.integration_factor.label       = 'INTEGRATION FACTOR:'
            form.threshold.label                = 'THRESHOLD:'

        return render_template("spectroscope.html",data_x=data_x_1,integration_time=form.integration_time.data,form=form)

    @app.route("/default",methods=['GET','POST'])
    def set_default_config():
        if request.method == 'POST':
            with lock:
                delete_db(app)
                load_db(app)
                update_params(app,set_camera_attr_en=True)
            return redirect(url_for('set_config_spectrometer'))
        else:
            return redirect(url_for('set_config_spectrometer'))
    return app

def start_webstreaming():
    ap = argparse.ArgumentParser()
    ap.add_argument("-i", "--ip", type=str, required=True,
        help="ip address of the device")
    ap.add_argument("-o", "--port", type=int, required=True,
        help="ephemeral port number of the server (1024 to 65535)")
    args = vars(ap.parse_args())

    # start the flask app
    app = create_app()
    init_db(app)

    t1 = threading.Thread(target=camera_loop,args=(app,))
    t1.daemon = True
    t1.start()

    app.run(host=args["ip"], port=args["port"], debug=True,
        threaded=True, use_reloader=False)
