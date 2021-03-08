from webpage.utils import *
import threading
import argparse
from flask import Flask, url_for, jsonify
from flask import render_template
from flask_bootstrap import Bootstrap
from flask import request, redirect
from webpage.config import *
from webpage.forms import ConfigForm
from time import sleep
from detector3_mock import MockDetector


def create_app(det):
    app = Flask(
                __name__,
                template_folder="templates",
                static_folder="static"
                )

    Bootstrap(app)

    app.config['SECRET_KEY'] = 'devkey'
    app.config['RECAPTCHA_PUBLIC_KEY'] = '6Lfol9cSAAAAADAkodaYl9wvQCwBMr3qGR_PPHcw'

    @app.route("/")
    def index():
        # redirects to config page
        return redirect(url_for('set_config_spectrometer'))

    # This function selects between automatic mode and manual mode into the DB
    @app.route('/select_mode', methods = ['POST'])
    def get_post_select_mode_data():
        select_mode = request.form['select_mode']
        det.operation_mode = select_mode
        det.integration_time    = get_spectro_scope('integration_time',app)
        det.integration_factor  = get_spectro_scope('integration_factor',app)
        det.threshold           = get_spectro_scope('threshold',app)
        return select_mode

    @app.route('/data')
    def spectro_data():
        yAxe = det.get_last_spectrum()
        return jsonify({'results':yAxe})

    @app.route("/save_spectrum", methods=["GET","POST"])
    def save_spectrum():
        det._save_spectrum(det.location,det.get_last_spectrum())
        return "Ok"

    @app.route("/spectroscope", methods=["GET","POST"])
    def set_config_spectrometer():
        form = ConfigForm()

        if request.method == 'POST':
            spectro_scope_config = {}
            spectro_scope_config['integration_time']     = form.integration_time.data
            spectro_scope_config['integration_factor']   = form.integration_factor.data
            spectro_scope_config['threshold']            = form.threshold.data

            set_spectro_scope(app,**spectro_scope_config)

            return redirect(url_for('set_config_spectrometer'))

        else:
            form.integration_time.render_kw     = {'value':get_spectro_scope('integration_time',app)}
            form.integration_factor.render_kw   = {'value':get_spectro_scope('integration_factor',app)}
            form.threshold.render_kw            = {'value':get_spectro_scope('threshold',app)}

            form.integration_time.label         = 'INTEGRATION TIME:'
            form.integration_factor.label       = 'INTEGRATION FACTOR:'
            form.threshold.label                = 'THRESHOLD:'

        integration_time                    = get_spectro_scope('integration_time',app)*1000 #Pass integration time to ms
        det.integration_time    = get_spectro_scope('integration_time',app)
        det.integration_factor  = get_spectro_scope('integration_factor',app)
        det.threshold           = get_spectro_scope('threshold',app)
        return render_template("spectroscope.html",data_x=det.get_wavelengths(),integration_time=integration_time,form=form, auto_en= (True if "automatic" == det.operation_mode else False))

    @app.route("/default",methods=['GET','POST'])
    def set_default_config():
        return redirect(url_for('set_config_spectrometer'))
    return app

def start_webstreaming():
    ap = argparse.ArgumentParser()

    ap.add_argument("-i", "--ip", type=str, default="0.0.0.0", help="ip address of the device")
    ap.add_argument("-o", "--port", type=int, default=8081, help="ephemeral port number of the server")
    ap.add_argument("-l", "--location", type=str, default='captures', help="folder to save the files")
    ap.add_argument("-id","--ip_det", type=str, default="0.0.0.1", help="ip address of the device")

    args = vars(ap.parse_args())

    # start the flask app
    det = MockDetector(args["ip_det"])
    app = create_app(det)
    init_db(app)

    det.location = args["location"]
    det.configure_gpio()
    det.start()

    app.run(host=args["ip"], port=args["port"], debug=True, threaded=True, use_reloader=False)
