<?php
require 'functions.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Routing
if ($uri == '/') {
    echo "Smart Monitoring Predictive Maintenance API";
} elseif ($uri == '/api/predict' && $method == 'POST') {
    predict();
} elseif ($uri == '/api/logs' && $method == 'GET') {
    get_logs();
} else {
    http_response_code(404);
    echo json_encode(["error" => "Endpoint not found"]);
}
?>
<?php
date_default_timezone_set('UTC');
require_once 'db.php';

// In-memory logs (reset on each restart)
$sensor_logs = [];

function predict_maintenance($sensor_data) {
    $temperature = floatval($sensor_data['temperature'] ?? 0);
    $vibration = floatval($sensor_data['vibration'] ?? 0);
    $pressure = floatval($sensor_data['pressure'] ?? 0);

    if ($temperature > 75 || $vibration > 0.7 || $pressure > 90) {
        return "Maintenance Needed";
    }
    return "Normal";
}

function predict() {
    global $sensor_logs;

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(["error" => "No data received"]);
        return;
    }

    $temperature = floatval($data['temperature'] ?? 0);
    $vibration = floatval($data['vibration'] ?? 0);
    $pressure = floatval($data['pressure'] ?? 0);

    $prediction = predict_maintenance($data);
    $timestamp = date(DATE_ISO8601);

    // Save to in-memory log
    $sensor_logs[] = [
        "timestamp" => $timestamp,
        "sensor_data" => $data,
        "prediction" => $prediction
    ];

    // Log to file (synchronously)
    $log_msg = "[$timestamp] Data: " . json_encode($data) . " → Prediction: $prediction\n";
    file_put_contents('predictions.log', $log_msg, FILE_APPEND);

    // Save to DB
    save_to_db($timestamp, $temperature, $vibration, $pressure, $prediction);

    echo json_encode([
        "prediction" => $prediction,
        "timestamp" => $timestamp
    ]);
}

function get_logs() {
    global $sensor_logs;
    header('Content-Type: application/json');
    echo json_encode($sensor_logs);
}
?>
<?php
function get_db_connection() {
    $db = new PDO('sqlite:sensors.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
}

function save_to_db($timestamp, $temperature, $vibration, $pressure, $prediction) {
    try {
        $db = get_db_connection();
        $stmt = $db->prepare("INSERT INTO logs (timestamp, temperature, vibration, pressure, prediction)
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$timestamp, $temperature, $vibration, $pressure, $prediction]);
    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage());
    }
}

function init_db() {
    try {
        $db = get_db_connection();
        $db->exec("CREATE TABLE IF NOT EXISTS logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            timestamp TEXT,
            temperature REAL,
            vibration REAL,
            pressure REAL,
            prediction TEXT
        )");
    } catch (PDOException $e) {
        error_log("DB Initialization Error: " . $e->getMessage());
    }
}

// Call DB init on load
init_db();
?>
