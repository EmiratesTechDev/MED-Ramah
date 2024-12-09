<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// التحقق من وجود معرف الموظف
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Employee ID is required'
    ]);
    exit;
}

// الحصول على معرف الموظف من المعاملات GET
$employee_id = intval($_GET['id']);

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

    // جلب تفاصيل الموظف مع الأجهزة المرتبطة
    $sql = "SELECT e.*, d.NameDepartment as department_name,
            (SELECT COUNT(*) FROM devtable WHERE employee_id = e.id) as device_count
            FROM empform e 
            LEFT JOIN departmenttable d ON e.department = d.IdDepartment
            WHERE e.id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Query preparation failed: " . $conn->error);
    }

    $stmt->bind_param("i", $employee_id);
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Employee not found'
        ]);
        exit;
    }

    $employee = $result->fetch_assoc();
    
    // جلب الأجهزة المرتبطة بالموظف
    $devices_sql = "SELECT * FROM devtable WHERE employee_id = ?";
    $devices_stmt = $conn->prepare($devices_sql);
    $devices_stmt->bind_param("i", $employee_id);
    $devices_stmt->execute();
    $devices_result = $devices_stmt->get_result();
    
    $devices = [];
    while ($device = $devices_result->fetch_assoc()) {
        $devices[] = $device;
    }
    
    // تنظيف وتحضير البيانات
    $response = [
        'success' => true,
        'data' => [
            'id' => $employee['id'],
            'firstName' => htmlspecialchars($employee['firstName']),
            'lastName' => htmlspecialchars($employee['lastName']),
            'position' => htmlspecialchars($employee['position'] ?? ''),
            'department' => htmlspecialchars($employee['department_name'] ?? ''),
            'nationality' => htmlspecialchars($employee['nationality'] ?? ''),
            'date_start' => htmlspecialchars($employee['date_start'] ?? ''),
            'contact_namber' => htmlspecialchars($employee['contact_namber'] ?? ''),
            'devices' => array_map(function($device) {
                return [
                    'id' => $device['id'],
                    'device_name' => htmlspecialchars($device['device_name'] ?? ''),
                    'serial_number' => htmlspecialchars($device['serial_number'] ?? ''),
                    'status' => htmlspecialchars($device['status'] ?? '')
                ];
            }, $devices),
            'device_count' => count($devices)
        ]
    ];

    // إغلاق الاتصال
    $stmt->close();
    $devices_stmt->close();
    $conn->close();

    // إرجاع البيانات
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
