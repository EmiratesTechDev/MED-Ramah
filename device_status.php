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

// استعلام لجلب حالة جميع الأجهزة
$sql = "SELECT 
            d.*,
            CONCAT(e.firstName, ' ', e.lastName) as employee_name
        FROM devtable d
        LEFT JOIN empform e ON d.employee_id = e.id
        ORDER BY d.name_device";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حالة الأجهزة</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        ☰
    </button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>لوحة التحكم</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fas fa-home"></i>الرئيسية</a></li>
            <li><a href="manage_departments.php"><i class="fas fa-building"></i>الأقسام</a></li>
            <li><a href="mange_diveces.php"><i class="fas fa-laptop"></i>الأجهزة</a></li>
            <li><a href="#"><i class="fas fa-users"></i>الموظفون</a></li>
            <li><a href="#"><i class="fas fa-cog"></i>الإعدادات</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="top-actions">
                <h1>حالة الأجهزة</h1>
                <a href="index.php" class="btn btn-secondary">العودة للصفحة الرئيسية</a>
            </div>

            <div class="status-cards">
                <?php
                $total = $result->num_rows;
                $assigned = 0;
                $available = 0;
                
                while($row = $result->fetch_assoc()) {
                    if($row['employee_id']) {
                        $assigned++;
                    } else {
                        $available++;
                    }
                    $devices[] = $row;
                }
                ?>
                <div class="card">
                    <h4>إجمالي الأجهزة</h4>
                    <div class="number"><?php echo $total; ?></div>
                </div>
                <div class="card">
                    <h4>الأجهزة المخصصة</h4>
                    <div class="number assigned"><?php echo $assigned; ?></div>
                </div>
                <div class="card">
                    <h4>الأجهزة المتاحة</h4>
                    <div class="number available"><?php echo $available; ?></div>
                </div>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>اسم الجهاز</th>
                            <th>الموديل</th>
                            <th>النظام</th>
                            <th>IP</th>
                            <th>MAC</th>
                            <th>الحالة</th>
                            <th>المستخدم الحالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($devices as $device): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($device['name_device']); ?></td>
                                <td><?php echo htmlspecialchars($device['model_device']); ?></td>
                                <td><?php echo htmlspecialchars($device['system_exp']); ?></td>
                                <td><?php echo htmlspecialchars($device['ip']); ?></td>
                                <td><?php echo htmlspecialchars($device['mac_adress']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $device['employee_id'] ? 'assigned' : 'available'; ?>">
                                        <?php echo $device['employee_id'] ? 'مخصص' : 'متاح'; ?>
                                    </span>
                                </td>
                                <td><?php echo $device['employee_id'] ? htmlspecialchars($device['employee_name']) : '-'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
    }
    </script>
    <script src="js/sidebar.js" defer></script>
</body>
</html>
