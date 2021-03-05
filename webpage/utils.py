from webpage.config import *
import sqlite3
from flask import g

DATABASE = 'webpage/static/spectro_scope.db'

def connect_db():
    return sqlite3.connect('webpage/static/spectro_scope.db')

def delete_db(app):

    # Insert tuple with config data into database
    with app.app_context():
        conn = connect_db()
        c = conn.cursor()
        c.execute("DELETE FROM spectro_scope")
        conn.commit()
        conn.close()

def load_db(app):
    
    spectro_scope_config = {}
    # Create empty list for appending every value
    config_data = list()

    spectro_scope_config['integration_time']             = INTEGRATION_TIME
    config_data.append(spectro_scope_config['integration_time'])

    spectro_scope_config['integration_factor']           = INTEGRATION_FACTOR
    config_data.append(spectro_scope_config['integration_factor'])

    spectro_scope_config['threshold']                    = THRESHOLD
    config_data.append(spectro_scope_config['threshold'])

    #  Transform config_data list into a tuple
    config_data = tuple(config_data)

    # Insert tuple with config data into database
    with app.app_context():
        conn = connect_db()
        c = conn.cursor()
        c.execute("INSERT INTO spectro_scope VALUES (?,?,?)", config_data)
        conn.commit()
        conn.close()

def init_db(app):
    conn = connect_db()
    c = conn.cursor()
    try:
        c.execute('''create table spectro_scope (INTEGRATION_TIME real,
                                                 INTEGRATION_FACTOR real,
                                                 THRESHOLD int
                                                )''')
        load_db(app)

    except sqlite3.OperationalError as e:
        print('table spectro_scope already exists' in str(e))
    conn.commit()
    conn.close()


def get_db():
    db = getattr(g, '_database', None)
    if db is None:
        db = g._database = sqlite3.connect(DATABASE)
    return db

# Function for sql UPDATE statement string building
def sql_stat_build(str1,str2,cont,listM,valueSP): 
    # If value is threshold convert to int
    if str2 == "THRESHOLD=?":
        listM.append(int(valueSP))
    else:    
        listM.append(float(valueSP))

    if cont == 0:
        str1 += str2
    else:
        str1 += "," + str2

    return str1

def set_spectro_scope(app,**spectro_scope_config):
    with app.app_context():
        conn = connect_db()
        # Aux variable for value control
        value_control = 0
        # Create string were SQL statements will be added
        string_sql = "UPDATE spectro_scope SET "
        # Create empty list were values to update will be appended
        l_spectro_scope = []

        configuration_mapping = {
            'integration_time'   : 'INTEGRATION_TIME',
            'integration_factor' : 'INTEGRATION_FACTOR',
            'threshold'          : 'THRESHOLD',
        }

        for variable in configuration_mapping.keys():
            if spectro_scope_config[variable]:
                config_str = f"{configuration_mapping[variable]}=?"
                string_sql = sql_stat_build(string_sql, config_str, value_control, l_spectro_scope, spectro_scope_config[variable])
                value_control+=1

        # Convert list into tuple
        l_spectro_scope = tuple(l_spectro_scope)
        
        # Execute UPDATE statement
        conn.execute(string_sql,l_spectro_scope)

        conn.commit()
        conn.close()

def get_spectro_scope(param,app):
    with app.app_context():
        g.db = connect_db()
        config_table = g.db.execute('select '+param+' from spectro_scope')
        result = 0
        try:
            result = config_table.fetchall()[0][0]
        except:
            result = 0

        g.db.close()
        return result
