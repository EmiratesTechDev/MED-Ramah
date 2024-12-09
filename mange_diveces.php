<?php
session_start();
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// التحقق من تسجيل دخول المستخدم
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include config file
require_once "config.php";

/*******************************************************
 * 2. معالجة إضافة جهاز جديد عند تقديم نموذج الإضافة
 ******************************************************/

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_device"])) {
    $name_device = $_POST["name_device"];
    $model_device = $_POST["model_device"];
    $system_exp = $_POST["system_exp"];
    $note = $_POST["note"];
    $ip = $_POST["ip"];
    $mac_adress = $_POST["mac_adress"];

    $insert_sql = "INSERT INTO devtable (name_device, model_device, system_exp, note, ip, mac_adress) VALUES (?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ssssss", $name_device, $model_device, $system_exp, $note, $ip, $mac_adress);

    if ($insert_stmt->execute()) {
        $message = "Device added successfully";
        $message_type = "success";
    } else {
        $message = "Error adding device: " . $conn->error;
        $message_type = "error";
    }
}

/**********************************************************
 * 3. معالجة تحديث بيانات الجهاز
 *********************************************************/

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_device"])) {
    $edit_id = $_POST['edit_id'];
    $name_device = $_POST["name_device"];
    $model_device = $_POST["model_device"];
    $system_exp = $_POST["system_exp"];
    $note = $_POST["note"];
    $ip = $_POST["ip"];
    $mac_adress = $_POST["mac_adress"];

    $update_sql = "UPDATE devtable SET name_device=?, model_device=?, system_exp=?, note=?, ip=?, mac_adress=? WHERE id_dev = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssssi", $name_device, $model_device, $system_exp, $note, $ip, $mac_adress, $edit_id);

    if ($update_stmt->execute()) {
        $message = "Device updated successfully";
        $message_type = "success";
    } else {
        $message = "Error updating device: " . $conn->error;
        $message_type = "error";
    }
    unset($device_data);
}

/*********************************************************
 * 4. معالجة حذف الجهاز
 ********************************************************/

if (isset($_GET["delete_id"])) {
    $delete_id = $_GET["delete_id"];
    
    try {
        $conn->begin_transaction();

        $update_history_sql = "UPDATE device_history SET device_id = NULL WHERE device_id = ?";
        $update_history_stmt = $conn->prepare($update_history_sql);
        $update_history_stmt->bind_param("i", $delete_id);
        $update_history_stmt->execute();

        $delete_sql = "DELETE FROM devtable WHERE id_dev = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $delete_id);
        $delete_stmt->execute();

        $conn->commit();
        
        $message = "Device deleted successfully";
        $message_type = "success";
        
        header("Location: mange_diveces.php");
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error deleting device: " . $e->getMessage();
        $message_type = "error";
    }
}

/*************************************************************
 * 5. استرجاع بيانات الجهاز للتعديل
 ************************************************************/

if (isset($_GET["edit_id"])) {
    $edit_id = $_GET["edit_id"];
    $select_query = "SELECT * FROM devtable WHERE id_dev = ?";
    $stmt = $conn->prepare($select_query);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result_edit = $stmt->get_result();
    $device_data = $result_edit->fetch_assoc();
    $stmt->close();
}

/***********************************************
 * 6. البحث عن الأجهزة
 **********************************************/

$search_term = "";
if (isset($_GET["search"])) {
    $search_term = $_GET["search"];
}

$sql = "SELECT * FROM devtable WHERE 
        id_dev LIKE ? OR 
        name_device LIKE ? OR 
        model_device LIKE ? OR 
        system_exp LIKE ? OR  
        note LIKE ? OR 
        ip LIKE ? OR 
        mac_adress LIKE ?";

$search_param = '%' . $search_term . '%';
$stmt_search = $conn->prepare($sql);
$stmt_search->bind_param("sssssss", $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param);
$stmt_search->execute();
$result = $stmt_search->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devices Management</title>
    <link rel="stylesheet" href="css/aaa.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  

  <!-- Custom CSS -->
  
  
  <!-- JavaScript -->
 
</head>
<body>

<!-- Sidebar Toggle Button -->
<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3>Devices</h3>
    </div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="index.php"><i class="fas fa-users"></i> Employees</a></li>
        <li><a href="manage_departments.php"><i class="fas fa-building"></i> Departments</a></li>
        <li class="active"><a href="mange_diveces.php"><i class="fas fa-laptop"></i> Devices</a></li>
        <li><a href="index.html"><i class="fas fa-cog"></i> Settings</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</div>

<!-- Main Content Wrapper -->
<div class="main-content">

  <style>
   .action-buttons {
    display: flex; /* ترتيب الأزرار بطريقة مرنة */
    flex-direction: column; /* ترتيب الأزرار عموديًا */
    align-items: flex-start; /* محاذاة الأزرار إلى اليسار */
    gap: 4px; /* تقليل المسافة بين الأزرار */
    margin: 15px auto; /* تقليل المسافة حول المجموعة وجعلها تتوسط الصفحة */
    max-width: 250px; /* تصغير العرض الأقصى للمجموعة */
}

.action-buttons button {
    padding: 6px 8px; /* تصغير الحشو الداخلي للأزرار */
    cursor: pointer;
    background-color: #007bff; /* لون الزر الافتراضي */
    color: white; /* لون النص */
    border: none; /* إزالة الحدود */
    border-radius: 4px; /* زوايا دائرية صغيرة */
    box-shadow: 0 3px 5px rgba(0, 0, 0, 0.1); /* إضافة ظل ناعم */
    font-size: 10px; /* تصغير حجم الخط */
    font-weight: 500; /* الحفاظ على وضوح النص */
    width: 100%; /* جعل جميع الأزرار بنفس العرض */
    text-align: left; /* تحسين المحاذاة داخل الزر */
    transition: all 0.3s ease-in-out; /* تأثير انتقال ناعم */
}

.action-buttons button:hover {
    background-color: #0056b3; /* لون مختلف عند التمرير */
    box-shadow: 0 4px 7px rgba(0, 0, 0, 0.15); /* تأثير ظل أقوى عند التمرير */
    transform: translateY(-1px); /* تأثير رفع الزر */
}


</style>

<div class="container">
  <h1>Manage Devices</h1>

  <?php
  // عرض الرسائل إذا وجدت
  if (isset($message)) {
      echo "<div class='message {$message_type}'>{$message}</div>";
  }
  ?>
<?php if(isset($device_data)): ?>
<div class="form-container">
  <h2>Edit Device</h2>
  <form method="POST" action="mange_diveces.php">
    <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
    <div class="form-grid">
      <input type="text" name="name_device" value="<?php echo htmlspecialchars($device_data['name_device']); ?>" placeholder="Device Name *" required>
      
      <!-- قائمة منسدلة للمودل -->
      <select name="model_device" required>
        <option value="Computer">Computer</option>
        <option value="Laptop">Laptop</option>
        <option value="Tablet">Tablet</option>
        <option value="Router">Router</option>
        <option value="Switch">Switch</option>
        <option value="Camera">Camera</option>
        <option value="Other">Other</option>
      </select>
      
      <input type="text" name="system_exp" value="<?php echo htmlspecialchars($device_data['system_exp']); ?>" placeholder="System Expiry">
      <input type="text" name="note" value="<?php echo htmlspecialchars($device_data['note']); ?>" placeholder="Note">
      <input type="text" name="ip" value="<?php echo htmlspecialchars($device_data['ip']); ?>" placeholder="IP Address">
      <input type="text" name="mac_adress" value="<?php echo htmlspecialchars($device_data['mac_adress']); ?>" placeholder="MAC Address">
      <button type="submit" name="update_device">Update Device</button>
    </div>
  </form>
</div>
<?php else: ?>
<div class="form-container">
  <h2>Add Device</h2>
  <form action="mange_diveces.php" method="post">
    <div class="form-grid">
      <input type="text" name="name_device" placeholder="Device Name *" required>
      
      <!-- قائمة منسدلة للمودل -->
      <select name="model_device" required>
        <option value="Computer">Computer</option>
        <option value="Laptop">Laptop</option>
        <option value="Tablet">Tablet</option>
        <option value="Router">Router</option>
        <option value="Switch">Switch</option>
        <option value="Camera">Camera</option>
        <option value="Other">Other</option>
      </select>
      
      <input type="text" name="system_exp" placeholder="System Exp">
      <input type="text" name="note" placeholder="Note">
      <input type="text" name="ip" placeholder="IP Address">
      <input type="text" name="mac_adress" placeholder="MAC Address">
      <button type="submit" name="add_device">Add Device</button>
    </div>
  </form>
</div>
<?php endif; ?>

  <div class="search-container">
    <form method="GET" action="mange_diveces.php">
      <input
        type="text"
        name="search"
        placeholder="Search devices..."
        class="search-input"
        value="<?php echo htmlspecialchars($search_term); ?>"
      />
    </form>
  </div>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Device Name</th>
          <th>Model</th>
          <th>System Expiry</th>
          <th>Note</th>
          <th>IP Address</th>
          <th>MAC Address</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row["id_dev"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["name_device"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["model_device"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["system_exp"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["note"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["ip"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["mac_adress"]) . "</td>";
                echo "<td>";
                echo "<div class='action-buttons'>";
                echo "<button onclick=\"location.href='mange_diveces.php?edit_id=" . $row["id_dev"] . "'\">Edit</button>";
                echo "<button onclick=\"if(confirm('Are you sure you want to delete this device?')) location.href='mange_diveces.php?delete_id=" . $row["id_dev"] . "';\">Delete</button>";
                echo "</div>";
                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='8'>No devices found.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<?php
/**********************************************
 * 11. إغلاق الاتصال بقاعدة البيانات
 *********************************************/
// بعد ما نخلص كل العمليات، نسكر الاتصال بقاعدة البيانات عشان نحرر الموارد
$conn->close();
?>

</body>
</html>