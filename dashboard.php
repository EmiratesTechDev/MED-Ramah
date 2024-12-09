<?php
session_start();

// التحقق من تسجيل دخول المستخدم
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include config file
require_once "config.php";

// Get total employees
$sql_total_employees = "SELECT COUNT(*) as total FROM empform";
$result = mysqli_query($conn, $sql_total_employees);
$total_employees = mysqli_fetch_assoc($result)['total'];

// Get total devices
$sql_total_devices = "SELECT COUNT(*) as total FROM devtable";
$result = mysqli_query($conn, $sql_total_devices);
$total_devices = mysqli_fetch_assoc($result)['total'];

// Get used devices (devices with employee_id not null)
$sql_used_devices = "SELECT COUNT(*) as total FROM devtable WHERE employee_id IS NOT NULL";
$result = mysqli_query($conn, $sql_used_devices);
$used_devices = mysqli_fetch_assoc($result)['total'];

// Get available devices (devices with no employee assigned)
$sql_available_devices = "SELECT COUNT(*) as total FROM devtable WHERE employee_id IS NULL";
$result = mysqli_query($conn, $sql_available_devices);
$available_devices = mysqli_fetch_assoc($result)['total'];

// Get employees per department
$sql_dept_count = "SELECT d.NameDepartment as name_dep, COUNT(e.id) as emp_count 
                   FROM departmenttable d 
                   LEFT JOIN empform e ON d.NameDepartment = e.department 
                   GROUP BY d.NameDepartment";
$dept_result = mysqli_query($conn, $sql_dept_count);

// Get device types count
$sql_device_types = "SELECT name_device, COUNT(*) as type_count 
                     FROM devtable 
                     GROUP BY name_device";
$device_types_result = mysqli_query($conn, $sql_device_types);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/aaa.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .main-content {
           /* margin-right: 250px;*/
            
            padding: 20px;
            background-color: #f4f6f9;
            min-height: 100vh;
        }

        .stats-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #34495e;
        }

        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }

        .stats-title {
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        .page-title {
            color: #2c3e50;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #34495e;
        }

        .info-section {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-top: 30px;
            padding: 20px;
        }

        .info-section h3 {
            color: #34495e;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }

        .department-stat {
            padding: 15px;
            border-radius: 8px;
            background: #f8f9fa;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .department-stat:hover {
            background: #edf2f7;
        }

        .device-type-stat {
            padding: 15px;
            border-radius: 8px;
            background: #f8f9fa;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .device-type-stat:hover {
            background: #edf2f7;
        }

        .stat-label {
            color: #34495e;
            font-weight: 500;
        }

        .stat-value {
            color: #2c3e50;
            font-weight: bold;
            background: #fff;
            padding: 5px 15px;
            border-radius: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            .main-content {
                margin-right: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>Dashboard</h3>
        </div>
        <ul class="sidebar-menu">
            <li class="active"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="index.php"><i class="fas fa-users"></i> Employees</a></li>
            <li><a href="manage_departments.php"><i class="fas fa-building"></i> Departments</a></li>
            <li><a href="mange_diveces.php"><i class="fas fa-laptop"></i> Devices</a></li>
            <li><a href="index.html"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="page-title"> Dashboard Overview <span>  -  </span> نظرة عامة على لوحة القيادة</h1>
       
        <br>
        <br>
        <style>.main-content {
  text-align: center; /*  النص في المنتصف */
  padding: 20px;  
}

.page-title {
  font-size: 2em; 
  margin-bottom: 10px;
}

h3 {
  font-size: 1.2em; /*  حجم العنوان الفرعي */
  color: #555; 
}</style>
        
        <div class="row">
            <!-- Employees Stats -->
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-users stats-icon"></i>
                    <div class="stats-number"><?php echo $total_employees; ?></div>
                    <div class="stats-title">Total Employees</div>
                </div>
            </div>

            <!-- Total Devices -->
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-laptop stats-icon"></i>
                    <div class="stats-number"><?php echo $total_devices; ?></div>
                    <div class="stats-title">Total Devices</div>
                </div>
            </div>

            
<!-- 
            -- Used Devices --

            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-desktop stats-icon"></i>
                    <div class="stats-number"><?php echo $used_devices; ?></div>
                    <div class="stats-title">Used Devices</div>
                </div>
            </div>
-->


<!--
           -- Available Devices --

            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-box-open stats-icon"></i>
                    <div class="stats-number"><?php echo $available_devices; ?></div>
                    <div class="stats-title">Available Devices</div>
                </div>
            </div>
        </div>

-->
        <div class="row">
            <!-- Employees per Department -->
            <div class="col-md-6">
                <div class="info-section">
                    <h3><i class="fas fa-building me-2"></i>Employees per Department</h3>
                    <?php
                    while ($dept_row = mysqli_fetch_assoc($dept_result)) {
                        echo '<div class="department-stat">';
                        echo '<span class="stat-label">' . htmlspecialchars($dept_row['name_dep']) . '</span>';
                        echo '<span class="stat-value">' . $dept_row['emp_count'] . ' Employees</span>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Device Types Distribution -->
            <div class="col-md-6">
                <div class="info-section">
                    <h3><i class="fas fa-laptop-code me-2"></i>Device Types Distribution</h3>
                    <?php
                    while ($device_row = mysqli_fetch_assoc($device_types_result)) {
                        if (!empty($device_row['name_device'])) {
                            echo '<div class="device-type-stat">';
                            echo '<span class="stat-label">' . htmlspecialchars($device_row['name_device']) . '</span>';
                            echo '<span class="stat-value">' . $device_row['type_count'] . ' Devices</span>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>