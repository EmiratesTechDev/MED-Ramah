<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// الاتصال بقاعدة البيانات
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "emlist";

$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode([
      'success' => false,
      'error' => 'Database connection failed: ' . $conn->connect_error
  ]);
  exit;
}

try {
  // التحقق من وجود جدول devtable
  $check_table = $conn->query("SHOW TABLES LIKE 'devtable'");
  if ($check_table->num_rows === 0) {
      throw new Exception("Table 'devtable' does not exist");
  }

  // التحقق من هيكل الجدول
  $check_structure = $conn->query("DESCRIBE devtable");
  $columns = [];
  while ($row = $check_structure->fetch_assoc()) {
      $columns[] = $row['Field'];
  }

  if (!in_array('employee_id', $columns)) {
      throw new Exception("Column 'employee_id' not found in devtable");
  }

  // جلب جميع الأجهزة مع معلومات الموظفين
  $sql = "SELECT d.*, e.firstName, e.lastName 
          FROM devtable d 
          LEFT JOIN empform e ON d.employee_id = e.id";

  $result = $conn->query($sql);

  if (!$result) {
      throw new Exception("Query failed: " . $conn->error);
  }

  // عدد الأجهزة الكلي
  $total_devices = $result->num_rows;

  $devices = [];
  while($row = $result->fetch_assoc()) {
      // تعديل طريقة تحديد توفر الجهاز
      $is_available = empty($row['employee_id']) || $row['employee_id'] == 0;

      $device = [
          'id_dev' => $row['id_dev'],
          'name_device' => $row['name_device'],
          'model_device' => $row['model_device'],
          'system_exp' => $row['system_exp'],
          'ip' => $row['ip'],
          'mac_adress' => $row['mac_adress'],
          'is_available' => $is_available,
          'current_employee' => $is_available ? null : [
              'id' => $row['employee_id'],
              'name' => $row['firstName'] . ' ' . $row['lastName']
          ]
      ];
      $devices[] = $device;
  }

  // عدد الأجهزة المتاحة
  $available_devices = array_filter($devices, function($device) {
      return $device['is_available'];
  });

  echo json_encode([
      'success' => true,
      'total_devices' => $total_devices,
      'available_devices' => count($available_devices),
      'devices' => $devices,
      'debug_info' => [
          'table_exists' => true,
          'columns' => $columns
      ]
  ]);

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode([
      'success' => false,
      'error' => $e->getMessage()
  ]);
}

$conn->close();
?>