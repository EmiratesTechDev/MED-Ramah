<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// الحصول على البيانات المرسلة عبر POST
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['employee_id']) || !isset($input['device_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input: employee_id and device_id are required']);
    exit;
}

$employee_id = intval($input['employee_id']);
$device_id = intval($input['device_id']);

if ($employee_id <= 0 || $device_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input: employee_id and device_id must be positive numbers']);
    exit;
}

// الاتصال بقاعدة البيانات
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "emlist";

$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// التحقق من وجود الجهاز وأنه متاح
$check_sql = "SELECT employee_id FROM devtable WHERE id_dev = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $device_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Device not found']);
    $check_stmt->close();
    $conn->close();
    exit;
}

$device = $result->fetch_assoc();
if ($device['employee_id'] !== null && $device['employee_id'] != 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Device is already assigned to another employee']);
    $check_stmt->close();
    $conn->close();
    exit;
}

// تعيين الجهاز للموظف
$sql = "UPDATE devtable SET employee_id = ? WHERE id_dev = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $employee_id, $device_id);

if ($stmt->execute()) {
    // التحقق من نجاح التحديث
    if ($stmt->affected_rows > 0) {
        // إضافة سجل في جدول device_history
        $history_sql = "INSERT INTO device_history (device_id, employee_id, action_type) VALUES (?, ?, 'assigned')";
        $history_stmt = $conn->prepare($history_sql);
        $history_stmt->bind_param("ii", $device_id, $employee_id);
        $history_stmt->execute();
        
        echo json_encode(['success' => true]);
    } else {
        error_log("Device assignment failed: No rows affected. Device ID: $device_id, Employee ID: $employee_id");
        http_response_code(500);
        echo json_encode(['error' => 'Failed to assign device: No changes made']);
    }
} else {
    error_log("Device assignment failed: " . $stmt->error);
    http_response_code(500);
    echo json_encode(['error' => 'Failed to assign device: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>