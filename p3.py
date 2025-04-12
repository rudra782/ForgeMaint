from flask import Flask, request, jsonify
from datetime import datetime
import sqlite3
import logging
from threading import Lock

app = Flask(__name__)

# Logging setup
logging.basicConfig(filename='predictions.log', level=logging.INFO)

# SQLite connection helper
def get_db_connection():
    return sqlite3.connect('sensors.db')

# Initialize SQLite DB
def init_db():
    conn = get_db_connection()
    cur = conn.cursor()
    cur.execute('''
        CREATE TABLE IF NOT EXISTS logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            timestamp TEXT,
            temperature REAL,
            vibration REAL,
            pressure REAL,
            prediction TEXT
        )
    ''')
    conn.commit()
    conn.close()

# Simulated ML model logic
def predict_maintenance(sensor_data):
    temperature = sensor_data.get('temperature')
    vibration = sensor_data.get('vibration')
    pressure = sensor_data.get('pressure')

    if temperature > 75 or vibration > 0.7 or pressure > 90:
        return "Maintenance Needed"
    return "Normal"

# In-memory log storage
sensor_logs = []
logs_lock = Lock()

@app.route('/')
def index():
    print("Your flask is running")
    return "✅ Smart Monitoring Predictive Maintenance API is running."

@app.route('/api/predict', methods=['POST'])
def predict():
    data = request.get_json()
    if not data or not all(k in data for k in ('temperature', 'vibration', 'pressure')):
        return jsonify({"error": "Invalid data received"}), 400

    try:
        temperature = float(data['temperature'])
        vibration = float(data['vibration'])
        pressure = float(data['pressure'])
    except (ValueError, TypeError):
        return jsonify({"error": "Invalid data types"}), 400

    prediction = predict_maintenance(data)
    timestamp = datetime.now().isoformat()

    # In-memory log
    log_entry = {
        "timestamp": timestamp,
        "sensor_data": data,
        "prediction": prediction
    }
    with logs_lock:
        sensor_logs.append(log_entry)

    # Log to file
    log_msg = f"[{timestamp}] Data: {data} → Prediction: {prediction}"
    logging.info(log_msg)

    # Save to DB (synchronously for now)
    try:
        conn = get_db_connection()
        cur = conn.cursor()
        cur.execute('''
            INSERT INTO logs (timestamp, temperature, vibration, pressure, prediction)
            VALUES (?, ?, ?, ?, ?)
        ''', (timestamp, temperature, vibration, pressure, prediction))
        conn.commit()
    except Exception as e:
        logging.error(f"Database error: {e}")
    finally:
        conn.close()

    return jsonify({
        "prediction": prediction,
        "timestamp": timestamp
    })

@app.route('/api/logs', methods=['GET'])
def get_logs():
    with logs_lock:
        return jsonify(sensor_logs)

if __name__ == '__main__':
    init_db()
    app.run(debug=True, host='127.0.0.1', port=5000)
