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

    @app.route('/data')
    def spectro_data():
        #--------------------------------------------
        #TODO Change load_data.get_wavelengths by tuple(w for w in self._spectrometer.get_wavelengths().split())
        yAxe = load_data.get_wavelengths()
        return jsonify({'results':yAxe})

    @app.route('/spectroscope')
    def render_large_template():
        return render_template('spectroscope.html',data_x=data_x_1)

    @app.route("/config", methods=["GET","POST"])
    def set_config():
        form = ConfigForm()

        if request.method == 'POST':
            if form.validate_on_submit():
                spectometer_config = {}
                spectometer_config['use_raspberry']             = form.use_raspberry.data
                spectometer_config['correct_vertical_camera']   = form.correct_vertical_camera.data

                with lock:
                    set_sp_config(app,**spectro_pointer_config)
                    update_params(app,set_camera_attr_en=True)

            return redirect(url_for('set_config'))
        else:
            form.use_raspberry.render_kw                 = {'value':get_sp_config('USE_RASPBERRY',app)}
            form.correct_vertical_camera.render_kw       = {'value':get_sp_config('CORRECT_VERTICAL_CAMERA',app)}

            form.resolution.data                         = get_sp_config('RESOLUTION',app)
            form.framerate.render_kw                     = {'value':get_sp_config('FRAMERATE',app)}

            form.use_raspberry.label                     = 'USE RASPBERRY:'
            form.correct_vertical_camera.label           = 'CORRECT VERTICAL CAMERA:'

            form.resolution.label                        = 'RESOLUTION:'
            form.framerate.label                         = 'FRAMERATE:'

        return render_template("config.html",form=form)

    @app.route("/default",methods=['GET','POST'])
    def set_default_config():
        if request.method == 'POST':
            with lock:
                delete_db(app)
                load_db(app)
                update_params(app,set_camera_attr_en=True)
            return redirect(url_for('set_config'))
        else:
            return redirect(url_for('set_config'))
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
