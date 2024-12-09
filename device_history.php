<?php
session_start();

// التحقق من تسجيل دخول المستخدم
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
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
    die("Connection failed: " . $conn->connect_error);
}

// استعلام لجلب سجل الأجهزة مع معلومات الموظف والجهاز
$sql = "SELECT 
            dh.*, 
            d.name_device, 
            d.model_device,
            CONCAT(e.firstName, ' ', e.lastName) as employee_name
        FROM device_history dh
        JOIN devtable d ON dh.device_id = d.id_dev
        JOIN empform e ON dh.employee_id = e.id
        ORDER BY dh.action_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="UTF-8">
    <title>سجل الأجهزة</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .history-table {
            width: 90%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-right: 20px;
            margin-left: 20px;
        }
        .history-table th, .history-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: right;
        }
        .history-table th {
            background-color: #f5f5f5;
        }
        .assigned {
            color: green;
        }
        .unassigned {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1> DIVECES RECORED</h1>
        <a href="index.php" class="back-button">العودة للصفحة الرئيسية</a>
        
        <table class="history-table">
            <thead>
                <tr>
                    <th>history</th>
                    <th>diveces</th>
                    <th>employee</th>
                    <th>action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('Y-m-d H:i', strtotime($row['action_date'])); ?></td>
                        <td><?php echo htmlspecialchars($row['name_device'] . ' - ' . $row['model_device']); ?></td>
                        <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
                        <td class="<?php echo $row['action_type']; ?>">
                            <?php echo $row['action_type'] == 'assigned' ? ' Assigned' : 'Cancelled'; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
