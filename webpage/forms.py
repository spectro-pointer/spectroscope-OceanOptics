from flask_wtf import FlaskForm
from wtforms import SubmitField, FloatField, IntegerField
from wtforms.validators import DataRequired, Length
#from config import *

class ConfigForm(FlaskForm):
    threshold                 = IntegerField('threshold')
    integration_time          = FloatField('integration_time')
    integration_factor        = FloatField('integration_factor')
    submit                    = SubmitField('Actualizar')
