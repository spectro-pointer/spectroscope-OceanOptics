from config import *
import sqlite3
from flask import g

DATABASE = './static/sp_config.db'

def connect_db():
    return sqlite3.connect('static/sp_config.db')

def delete_db(app):

    # Insert tuple with config data into database
    with app.app_context():
        conn = connect_db()
        c = conn.cursor()
        c.execute("DELETE FROM sp_config")
        conn.commit()
        conn.close()

def load_db(app):
    
    spectro_pointer_config = {}
    # Create empty list for appending every value
    config_data = list()

    spectro_pointer_config['use_raspberry']             = int(USE_RASPBERRY)
    config_data.append(spectro_pointer_config['use_raspberry'])

    spectro_pointer_config['correct_vertical_camera']   = int(CORRECT_VERTICAL_CAMERA)
    config_data.append(spectro_pointer_config['correct_vertical_camera'])

    spectro_pointer_config['correct_horizontal_camera'] = int(CORRECT_HORIZONTAL_CAMERA)
    config_data.append(spectro_pointer_config['correct_horizontal_camera'])

    spectro_pointer_config['center_radius']             = int(CENTER_RADIUS)
    config_data.append(spectro_pointer_config['center_radius'])

    spectro_pointer_config['show_center_circle']        = int(SHOW_CENTER_CIRCLE)
    config_data.append(spectro_pointer_config['show_center_circle'])

    spectro_pointer_config['enable_photo']              = int(ENABLE_PHOTO)
    config_data.append(spectro_pointer_config['enable_photo'])

    spectro_pointer_config['enable_video']              = int(ENABLE_VIDEO)
    config_data.append(spectro_pointer_config['enable_video'])

    spectro_pointer_config['record_seconds']            = int(RECORD_SECONDS)
    config_data.append(spectro_pointer_config['record_seconds'])

    spectro_pointer_config['threshold']            = int(THRESHOLD)
    config_data.append(spectro_pointer_config['threshold'])

    spectro_pointer_config['resolution']            = str(RESOLUTION)
    config_data.append(spectro_pointer_config['resolution'])

    spectro_pointer_config['framerate']            = int(FRAMERATE)
    config_data.append(spectro_pointer_config['framerate'])

    spectro_pointer_config['sensor_mode']            = int(SENSOR_MODE)
    config_data.append(spectro_pointer_config['sensor_mode'])

    spectro_pointer_config['shutter_speed']            = int(SHUTTER_SPEED)
    config_data.append(spectro_pointer_config['shutter_speed'])

    spectro_pointer_config['iso']            = int(ISO)
    config_data.append(spectro_pointer_config['iso'])

    #  Transform config_data list into a tuple
    config_data = tuple(config_data)

    # Insert tuple with config data into database
    with app.app_context():
        conn = connect_db()
        c = conn.cursor()
        c.execute("INSERT INTO sp_config VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)", config_data)
        conn.commit()
        conn.close()

def init_db(app):
    conn = connect_db()
    c = conn.cursor()
    try:
        c.execute('''create table sp_config (USE_RASPBERRY int, CORRECT_VERTICAL_CAMERA int,
                                             CORRECT_HORIZONTAL_CAMERA int, CENTER_RADIUS int,
                                             SHOW_CENTER_CIRCLE int, ENABLE_PHOTO int,
                                             ENABLE_VIDEO int,RECORD_SECONDS int, THRESHOLD int,
                                             RESOLUTION text, FRAMERATE int, SENSOR_MODE int,
                                             SHUTTER_SPEED int, ISO int)''')        
        load_db(app)

    except sqlite3.OperationalError as e:
        print('table sp_config already exists' in str(e))
    conn.commit()
    conn.close()


def get_db():
    db = getattr(g, '_database', None)
    if db is None:
        db = g._database = sqlite3.connect(DATABASE)
    return db

# Function for sql UPDATE statement string building
def sql_stat_build(str1,str2,cont,listM,valueSP): 
    # If value is resolution do not convert to int
    if str2 == "RESOLUTION=?":
        listM.append(valueSP)
    else:    
        listM.append(int(valueSP))

    if cont == 0:
        str1 += str2
    else:
        str1 += "," + str2

    return str1

def set_sp_config(app,**spectro_pointer_config):
    with app.app_context():
        conn = connect_db()
        # Aux variable for value control
        value_control = 0
        # Create string were SQL statements will be added
        string_sql = "UPDATE sp_config SET "
        # Create empty list were values to update will be appended
        l_sp_config = []

        configuration_mapping = {
            'use_raspberry'             : 'USE_RASPBERRY',
            'correct_vertical_camera'   : 'CORRECT_VERTICAL_CAMERA',
            'correct_horizontal_camera' : 'CORRECT_HORIZONTAL_CAMERA',
            'center_radius'             : 'CENTER_RADIUS',
            'show_center_circle'        : 'SHOW_CENTER_CIRCLE',
            'enable_photo'              : 'ENABLE_PHOTO',
            'enable_video'              : 'ENABLE_VIDEO',
            'record_seconds'            : 'RECORD_SECONDS',
            'threshold'                 : 'THRESHOLD',
            'resolution'                : 'RESOLUTION',
            'framerate'                 : 'FRAMERATE',
            'sensor_mode'               : 'SENSOR_MODE',
            'shutter_speed'             : 'SHUTTER_SPEED',
            'iso'                       : 'ISO'
        }

        for variable in configuration_mapping.keys():
            if spectro_pointer_config[variable]:
                config_str = f"{configuration_mapping[variable]}=?"
                string_sql = sql_stat_build(string_sql, config_str, value_control, l_sp_config, spectro_pointer_config[variable])
                value_control+=1

        # Convert list into tuple
        l_sp_config = tuple(l_sp_config)
        
        # Execute UPDATE statement
        conn.execute(string_sql,l_sp_config)

        conn.commit()
        conn.close()

def get_sp_config(param,app):
    with app.app_context():
        g.db = connect_db()
        config_table = g.db.execute('select '+param+' from sp_config')
        result = 0
        try:
            result = config_table.fetchall()[0][0]
        except:
            result = 0

        g.db.close()
        return result
