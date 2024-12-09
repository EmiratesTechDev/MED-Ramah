<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// التحقق من وجود device_id
if (!isset($_POST['device_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Device ID is required'
    ]);
    exit;
}

$device_id = intval($_POST['device_id']);

try {
    // الاتصال بقاعدة البيانات
    require_once('config.php');
    
    // تحديث الجهاز لإزالة ارتباطه بالموظف
    $sql = "UPDATE devtable SET employee_id = NULL, status = 'Available' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Query preparation failed: " . $conn->error);
    }

    $stmt->bind_param("i", $device_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Device removed successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Device not found or already unassigned'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
