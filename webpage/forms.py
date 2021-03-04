from flask_wtf import FlaskForm
from wtforms import StringField, SubmitField, PasswordField, SelectField
from wtforms.validators import DataRequired, Email, Length
#from config import *

class ConfigForm(FlaskForm):
    threshold                 = StringField('threshold', validators=[Length(max=64)])
    integration_time          = StringField('integration_time', validators=[Length(max=64)])
    integration_factor        = StringField('integration_factor', validators=[Length(max=64)])
    submit                    = SubmitField('Actualizar')
