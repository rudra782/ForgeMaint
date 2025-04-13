from flask import Flask, request, jsonify # type: ignore
from datetime import datetime

app = Flask(__name__)

# Simulated ML model (replace with actual ML code)
def predict_maintenance(sensor_data):
    temperature = sensor_data.get('temperature')
    vibration = sensor_data.get('vibration')
    pressure = sensor_data.get('pressure')

    if temperature > 75 or vibration > 0.7 or pressure > 90:
        return "Maintenance Needed"
    return "Normal"

# In-memory store for sensor data (for demo purposes)
sensor_logs = []

@app.route('/') 
def index():
    return "Smart Monitoring Predictive Maintenance API"

@app.route('/api/predict', methods=['POST'])
def predict():
    data = request.get_json()
    if not data:
        return jsonify({"error": "No data received"}), 400

    prediction = predict_maintenance(data)
    timestamp = datetime.now().isoformat()

    log_entry = {
        "timestamp": timestamp,
        "sensor_data": data,
        "prediction": prediction
    }
    sensor_logs.append(log_entry)

    return jsonify({
        "prediction": prediction,
        "timestamp": timestamp
    })

@app.route('/api/logs', methods=['GET'])
def get_logs():
    return jsonify(sensor_logs)

if __name__ == '__main__':
    app.run(debug=True)