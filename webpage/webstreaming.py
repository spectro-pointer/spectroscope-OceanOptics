from webpage.utils import *
#from tracker_lib import *
import threading
import argparse
from flask import Flask, url_for, jsonify
from flask import render_template
from flask_bootstrap import Bootstrap
from flask import request, redirect
#from flask_appconfig import AppConfig
from webpage.config import *
from webpage.forms import ConfigForm
from time import sleep
from detector3 import Detector

def create_app(det, configfile=None):
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
        return redirect(url_for('set_config_spectrometer'))

    # This function selects between automatic mode and manual mode into the DB
    @app.route('/select_mode', methods = ['POST'])
    def get_post_select_mode_data():
        select_mode = request.form['select_mode']
        print("SELECT_MODE",select_mode)
        det.set_operation_mode(select_mode)
        return select_mode

    @app.route('/data')
    def spectro_data():
        yAxe = det.get_last_spectrum()
        return jsonify({'results':yAxe})

    @app.route("/save_spectrum", methods=["GET","POST"])
    def save_spectrum():
        det._save_spectrum(det.location,det.get_last_spectrum())

    @app.route("/spectroscope", methods=["GET","POST"])
    def set_config_spectrometer():
        form = ConfigForm()

        if request.method == 'POST':
            if form.validate_on_submit():
                spectro_scope_config = {}
                spectro_scope_config['integration_time']     = form.integration_time.data
                spectro_scope_config['integration_factor']   = form.integration_factor.data
                spectro_scope_config['threshold']            = form.threshold.data
                print("INTEGRATION TIME",form.integration_time.data)
                print("INTEGRATION FACTOR",form.integration_factor.data)
                print("THRESHOLD",form.threshold.data)

                set_spectro_scope(app,**spectro_scope_config)

            return redirect(url_for('set_config_spectrometer'))

        else:
            #print("INTEGRATION TIME",form.integration_time.data)
            #print("INTEGRATION FACTOR",form.integration_factor.data)
            #print("THRESHOLD",form.threshold.data)
            form.integration_time.render_kw     = {'value':get_spectro_scope('integration_time',app)}
            form.integration_factor.render_kw   = {'value':get_spectro_scope('integration_factor',app)}
            form.threshold.render_kw            = {'value':get_spectro_scope('threshold',app)}

            form.integration_time.label         = 'INTEGRATION TIME:'
            form.integration_factor.label       = 'INTEGRATION FACTOR:'
            form.threshold.label                = 'THRESHOLD:'

        integration_time                    = get_spectro_scope('integration_time',app)*1000 #Pass integration time to ms

        return render_template("spectroscope.html",data_x=det.get_wavelengths(),integration_time=integration_time,form=form)
        #return render_template("spectroscope.html",data_x=det.get_wavelengths(),integration_time=int(det.integration_time*1000),form=form)

    @app.route("/default",methods=['GET','POST'])
    def set_default_config():
        if request.method == 'POST':
            # with lock:
            #     delete_db(app)
            #     load_db(app)
            #     update_params(app,set_camera_attr_en=True)
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

    ap.add_argument("-loc", "--location", type=str, required=True,
        help="ephemeral port number of the server (1024 to 65535)")

    ap.add_argument("-id","--ip_det", type=str, required=True,
        help="ip address of the device")

    args = vars(ap.parse_args())

    # start the flask app
    det = Detector(args["ip_det"],debug_mode=True)
    app = create_app(det)
    init_db(app)

    det.location = args["location"]
    det.start()


    # t1 = threading.Thread(target=camera_loop,args=(app,))
    # t1.daemon = True
    # t1.start()

    app.run(host=args["ip"], port=args["port"], debug=True,
        threaded=True, use_reloader=False)
