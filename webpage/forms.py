from flask_wtf import FlaskForm
from wtforms import SubmitField, FloatField, IntegerField
from wtforms.validators import DataRequired
#from config import *

class ConfigForm(FlaskForm):
    threshold                 = IntegerField('threshold', validators=[DataRequired(message="threshold has no value")])
    integration_time          = FloatField('integration_time', validators=[DataRequired(message="integration_time has no value")])
    integration_factor        = FloatField('integration_factor', validators=[DataRequired(message="integration_factor has no value")])
    submit                    = SubmitField('Actualizar')
