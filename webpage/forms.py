from flask_wtf import FlaskForm
from wtforms import SubmitField, FloatField, IntegerField
from wtforms.validators import DataRequired, NumberRange


class ConfigForm(FlaskForm):
    fields = ['threshold', 'integration_time', 'integration_factor']
    threshold = IntegerField(
        'threshold', validators=[DataRequired(), NumberRange(min=0, max=65535)])
    integration_time = FloatField(
        'integration_time', validators=[DataRequired(), NumberRange(min=.1, max=60)])
    integration_factor = FloatField(
        'integration_factor', validators=[DataRequired(), NumberRange(min=0, max=1)])
    submit = SubmitField('Actualizar')
