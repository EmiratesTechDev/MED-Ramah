<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// التحقق من وجود employee_id
if (!isset($_GET['employee_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Employee ID is required'
    ]);
    exit;
}

// الحصول على (employee_id) من المعاملات GET
$employee_id = intval($_GET['employee_id']);

if ($employee_id <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid employee ID'
    ]);
    exit;
}

try {
    // الاتصال بقاعدة البيانات
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "emlist";

    $conn = new mysqli($servername, $username, $password, $dbname);

    // التحقق من الاتصال
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // جلب الأجهزة المخصصة للموظف المحدد مع معلومات إضافية
    $sql = "SELECT d.*, e.firstName, e.lastName 
            FROM devtable d 
            LEFT JOIN empform e ON d.employee_id = e.id 
            WHERE d.employee_id = ?";
            
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Query preparation failed: " . $conn->error);
    }

    $stmt->bind_param("i", $employee_id);
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $devices = [];

    while ($row = $result->fetch_assoc()) {
        // تنظيف وتحضير البيانات
        $device = [
            'id_dev' => $row['id_dev'],
            'name_device' => htmlspecialchars($row['name_device']),
            'model_device' => htmlspecialchars($row['model_device']),
            'system_exp' => htmlspecialchars($row['system_exp'] ?? ''),
            'ip' => htmlspecialchars($row['ip'] ?? ''),
            'mac_adress' => htmlspecialchars($row['mac_adress'] ?? ''),
            'employee' => [
                'id' => $row['employee_id'],
                'name' => trim($row['firstName'] . ' ' . $row['lastName'])
            ]
        ];
        $devices[] = $device;
    }

    // إغلاق الاتصال
    $stmt->close();
    $conn->close();

    // إرجاع النتائج
    echo json_encode([
        'success' => true,
        'count' => count($devices),
        'devices' => $devices
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>