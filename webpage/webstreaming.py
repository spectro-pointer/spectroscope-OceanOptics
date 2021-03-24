from webpage.utils import *
import argparse
from flask import Flask, url_for, jsonify
from flask import render_template
from flask_bootstrap import Bootstrap
from flask import request, redirect
from webpage.config import *
from webpage.forms import ConfigForm
from detector3 import Detector
from spectrometer3 import Spectrometer


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
        data = []
        yAxe = det.get_last_spectrum()
        xAxe = det.get_wavelengths()
        max_intensity = det.MAX_INTENSITY
        for x,y in zip(xAxe,yAxe):
            data.append([x,y])
        if not data:
            data.append([0,0])
        return jsonify({'data':data,'max_intensity':max_intensity,'button_flag':(det.button_flag == "true")})

    @app.route('/web_config')
    def set_web_config():
        integration_time = det.integration_time
        auto_en = bool("automatic" == det.operation_mode)
        return jsonify({'integration_time': integration_time,'auto_en':auto_en})

    @app.route("/save_spectrum", methods=["GET","POST"])
    def save_spectrum():
        det._save_spectrum(det.location,det.get_last_spectrum())
        return "Ok"

    @app.route("/stop_graph", methods=["GET","POST"])
    def stop_graph():
        det.stop_graph = request.form['state'] == 'true'
        return "Ok"

    @app.route("/spectroscope", methods=["GET","POST"])
    def set_config_spectrometer():
        form = ConfigForm()

        if request.method == 'POST':
            # validate values inside detector
            det.integration_time    = form.integration_time.data
            if det.operation_mode == "manual":
                det.integration_factor  = form.integration_factor.data
                det.threshold           = form.threshold.data

            # save values to DB
            spectro_scope_config = {}
            spectro_scope_config['integration_time']     = det.integration_time
            spectro_scope_config['integration_factor']   = det.integration_factor
            spectro_scope_config['threshold']            = det.threshold
            set_spectro_scope(app,**spectro_scope_config)
        else:
            # load values form DB
            det.integration_time    = get_spectro_scope('integration_time',app)
            det.integration_factor  = get_spectro_scope('integration_factor',app)
            det.threshold           = get_spectro_scope('threshold',app)

        # load form
        form.integration_time.render_kw     = {'value':det.integration_time}
        form.integration_factor.render_kw   = {'value':det.integration_factor}
        form.threshold.render_kw            = {'value':det.threshold}
        form.integration_time.label         = 'INTEGRATION TIME:'
        form.integration_factor.label       = 'INTEGRATION FACTOR:'
        form.threshold.label                = 'THRESHOLD:'

        return render_template("spectroscope.html",
                                form=form,
                                auto_en=("automatic" == det.operation_mode))

    @app.route("/default",methods=['GET','POST'])
    def set_default_config():
        return redirect(url_for('set_config_spectrometer'))
    return app

def start_webstreaming():
    ap = argparse.ArgumentParser()

    ap.add_argument("-i", "--ip", type=str, default="0.0.0.0", help="ip address of the device")
    ap.add_argument("-o", "--port", type=int, default=8087, help="ephemeral port number of the server")
    ap.add_argument("-l", "--location", type=str, default='captures', help="folder to save the files")
    ap.add_argument("-id","--ip_det", type=str, default="0.0.0.0", help="ip address of the device")

    args = vars(ap.parse_args())

    # start the flask app
    print("Using mock spectrometer")
    spectrometer = Spectrometer(ip_address=args["ip_det"], port=1865, channel=0)
    det = Detector(spectrometer)
    app = create_app(det)
    init_db(app)

    det.location = args["location"]
    det.configure_gpio()
    det.start()

    app.run(host=args["ip"], port=args["port"], debug=True, threaded=True, use_reloader=False)
