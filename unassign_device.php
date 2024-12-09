<?php
header('Content-Type: application/json');

// الحصول على البيانات المرسلة عبر POST
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['device_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$device_id = intval($input['device_id']);

// الاتصال بقاعدة البيانات
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "emlist";

$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// إزالة تخصيص الجهاز (تعيين employee_id إلى NULL)
$sql = "UPDATE devtable SET employee_id = NULL WHERE id_dev = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $device_id);

// الحصول على معرف الموظف قبل إزالة التخصيص
$get_employee_sql = "SELECT employee_id FROM devtable WHERE id_dev = ?";
$get_employee_stmt = $conn->prepare($get_employee_sql);
$get_employee_stmt->bind_param("i", $device_id);
$get_employee_stmt->execute();
$result = $get_employee_stmt->get_result();
$employee_data = $result->fetch_assoc();
$employee_id = $employee_data['employee_id'];

if ($stmt->execute()) {
    // إضافة سجل في جدول device_history
    if ($employee_id) {
        $history_sql = "INSERT INTO device_history (device_id, employee_id, action_type) VALUES (?, ?, 'unassigned')";
        $history_stmt = $conn->prepare($history_sql);
        $history_stmt->bind_param("ii", $device_id, $employee_id);
        $history_stmt->execute();
    }
    
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to unassign device']);
}

$conn->close();
?>