<?php
session_start();
//Manage Departments
// التحقق من تسجيل دخول المستخدم
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "emlist";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//     هذا الكود لجلب الأقسام
$departments_query = "SELECT * FROM departmenttable";
$departments_result = $conn->query($departments_query);
$departments = [];
while($dept = $departments_result->fetch_assoc()) {
    $departments[] = $dept;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_employee"])) {
    $ref = $_POST["ref"];
    $lastName = $_POST["lastName"];
    $firstName = $_POST["firstName"];
    $position = $_POST["position"];
    $department = $_POST["department"];
    $nationality = $_POST["nationality"];
    $date_start = $_POST["date_start"];
    $contact_namber = $_POST["contact_namber"];

    $insert_sql = "INSERT INTO empform (ref, lastName, firstName, position, department, nationality, date_start, contact_namber) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ssssssss", $ref, $lastName, $firstName, $position, $department, $nationality, $date_start, $contact_namber);

    if ($insert_stmt->execute()) {
        $message = "Employee added successfully";
        $message_type = "success";
    } else {
        $message = "Error adding employee: " . $conn->error;
        $message_type = "error";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_employee"])) {
    $edit_id = $_POST['edit_id'];
    $ref = $_POST["ref"];
    $lastName = $_POST["lastName"];
    $firstName = $_POST["firstName"];
    $position = $_POST["position"];
    $department = $_POST["department"]; 
    $nationality = $_POST["nationality"];
    $date_start = $_POST["date_start"];
    $contact_namber = $_POST["contact_namber"];
    
    $update_sql = "UPDATE empform SET ref=?, lastName=?, firstName=?, position=?, department=?, nationality=?, date_start=?, contact_namber=? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssssssi", $ref, $lastName, $firstName, $position, $department, $nationality, $date_start, $contact_namber, $edit_id);

    if ($update_stmt->execute()) {
        $message = "Employee updated successfully";
        $message_type = "success";
    } else {
        $message = "Error updating employee: " . $conn->error;
        $message_type = "error";
    }
    unset($employee_data);
}

if (isset($_GET["delete_id"])) {
    $delete_id = $_GET["delete_id"];
    $update_sql = "UPDATE empform SET is_active = 0 WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $delete_id);

    if ($update_stmt->execute()) {
        $message = "Employee deactivated successfully";
        $message_type = "success";
        // إعادة توجيه إلى الصفحة الرئيسية
        header("Location: index.php");
        exit();
    } else {
        $message = "Error deactivating employee: " . $conn->error;
        $message_type = "error";
    }
}

if (isset($_GET["edit_id"])) {
    $edit_id = $_GET["edit_id"];
    $select_query = "SELECT * FROM empform WHERE id = ?";
    $stmt = $conn->prepare($select_query);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result_edit = $stmt->get_result();
    $employee_data = $result_edit->fetch_assoc();
    $stmt->close();
}

$search_term = "";
if (isset($_GET["search"])) {
    $search_term = $_GET["search"];
}

// تحديد الصفحة الحالية
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 30;
$offset = ($page - 1) * $items_per_page;

// استعلام لحساب إجمالي عدد السجلات
$count_query = "SELECT COUNT(*) as total FROM empform WHERE is_active = 1";
$count_result = $conn->query($count_query);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $items_per_page);

// استعلام البحث الرئيسي مع إضافة شرط is_active
$sql = "SELECT * FROM empform WHERE is_active = 1 AND (
        id LIKE ? OR 
        ref LIKE ? OR 
        lastName LIKE ? OR 
        firstName LIKE ? OR 
        position LIKE ? OR 
        department LIKE ? OR
        nationality LIKE ? OR
        date_start LIKE ? OR 
        contact_namber LIKE ?) 
        LIMIT $items_per_page OFFSET $offset";

$search_param = '%' . $search_term . '%';
$stmt_search = $conn->prepare($sql);
$stmt_search->bind_param("sssssssss", $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param);
$stmt_search->execute();
$result = $stmt_search->get_result();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة الأجهزة</title>
    <link rel="stylesheet" href="css/aaa.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
</head>
<body>


<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
    <h3>Employees</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li class="active"><a href="index.php"><i class="fas fa-users"></i> Employees</a></li>
            <li><a href="manage_departments.php"><i class="fas fa-building"></i> Departments</a></li>
            <li><a href="mange_diveces.php"><i class="fas fa-laptop"></i> Devices</a></li>
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
.top-actions {
        display: flex; /* استخدام Flexbox لترتيب العناصر بشكل مرن */
        gap: 6px; /* إضافة مسافة بين الأزرار */
        justify-content: flex-start; /* محاذاة الأزرار إلى اليسار */
        margin-bottom: 3px;
    }

    .top-actions .export-button button,
    .top-actions .device-history-button button {
        padding: 6px 12px; /* إضافة الحشو للزر */
        cursor: pointer; /* تغيير الشكل عند التمرير على الزر */
        background-color: #2980b9;
        ; /* لون الزر */
        color: white; /* لون النص */
        border: none; /* إزالة الحدود */
        border-radius: 1.4px; /* إضافة زوايا دائرية */
        font-size: 14px; /* حجم الخط */
        transition: background-color 0.3s ease; /* تأثير عند التمرير */
    }

    .top-actions .export-button button:hover,
    .top-actions .device-history-button button:hover {
        background-color: #0056b3; /* تغيير اللون عند التمرير */
    }


    

    
</style>
  <!-- سطر صفحة الأقسام-->



<div class="container">
  <h1>Manage employees</h1>


    <?php
    // عرض الرسائل إذا وجدت
    if (isset($message)) {
        echo "<div class='message {$message_type}'>{$message}</div>";
    }
  ?>
  <?php if(isset($employee_data)): ?>
  <div class="form-container">
    <h2>Edit Employee</h2>
    <form method="POST" action="index.php">
      <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
      <div class="form-grid">
        <input type="text" name="ref" value="<?php echo htmlspecialchars($employee_data['ref']); ?>" placeholder="Reference *" required>
        <input type="text" name="lastName" value="<?php echo htmlspecialchars($employee_data['lastName']); ?>" placeholder="Last Name *" required>
        <input type="text" name="firstName" value="<?php echo htmlspecialchars($employee_data['firstName']); ?>" placeholder="First Name *" required>
        <input type="text" name="position" value="<?php echo htmlspecialchars($employee_data['position']); ?>" placeholder="Position">
        <input type="text" name="nationality" value="<?php echo htmlspecialchars($employee_data['nationality']); ?>" placeholder="nationality">
        <input type="date" name="date_start" value="<?php echo htmlspecialchars( $employee_data['date_start']); ?>" placeholder="date_start">

        <select name="department" required>
            <option value="">Select Department</option>
            <?php foreach($departments as $dept): ?>
                <option value="<?php echo htmlspecialchars($dept['NameDepartment']); ?>"
                    <?php echo ($employee_data['department'] == $dept['NameDepartment']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($dept['NameDepartment']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="contact_namber" value="<?php echo htmlspecialchars($employee_data['contact_namber']); ?>" placeholder="Contact Number"> 
        <button type="submit" name="update_employee">Update Employee</button>
      </div>
    </form>
  </div>
  <?php else: ?>
  <div class="form-container">
    <h2>Add Employee</h2>
    <form action="index.php" method="post">
      <div class="form-grid">
        <input type="text" name="ref" placeholder="Reference *" required>
        <input type="text" name="lastName" placeholder="Last Name *" required>
        <input type="text" name="firstName" placeholder="First Name *" required>
        <input type="text" name="position" placeholder="Position">
        <input type="text" name="nationality" placeholder="nationality">
        <input type="date" name="date_start" placeholder="date_start">
        <input type="text" name="contact_namber" placeholder="contact_namber">

       
  <!--القائمة المنسدلة للأقسام  -->
        <select name="department" required>
         <option value="">Select Department</option>
            <?php foreach($departments as $dept): ?>
            <option value="<?php echo htmlspecialchars($dept['NameDepartment']); ?>"
            <?php if(isset($employee_data) && $employee_data['department'] == $dept['NameDepartment']) echo 'selected'; ?>>
            <?php echo htmlspecialchars($dept['NameDepartment']); ?>
           </option>
         <?php endforeach; ?>
       </select>  

    <button type="submit" name="add_employee">Add Employee</button>
      </div>
    </form>
  </div>
  <?php endif; ?>
  <div class="search-container">
    <form method="GET" action="index.php">
      <input
        type="text"
        name="search"
        placeholder="Search employees..."
        class="search-input"
        value="<?php echo htmlspecialchars($search_term); ?>"
      />
    </form>
  </div>

  <div class="top-actions">
    <div class="export-button">
      <button onclick="location.href='export_pdf.php'">Export as PDF</button>
    </div>


  <!-- زر تصدير   -->
   <div class="export-button">
        <button onclick="location.href='generate_pdf.php'" >print</button>
     </div>


    <div class="device-history-button">
      <button onclick="location.href='device_history.php'">سجل الأجهزة</button>
    </div>
  </div>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Reference</th>
          <th>Last Name</th>
          <th>First Name</th>
          <th>Position</th>
          <th>Department</th>
          <th>Nationality</th>
          <th>Start date</th>
          <th>Contact Number</th>

          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["ref"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["lastName"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["firstName"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["position"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["department"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["nationality"]) . "</td>";
                echo "<td>" . htmlspecialchars($row ["date_start"]) . "</td>";
                echo "<td>" . htmlspecialchars($row ["contact_namber"]) . "</td>";
                echo "<td>" . "<div class='action-buttons'>";
                echo "<button onclick=\"location.href='index.php?edit_id=" . $row["id"] . "'\">Edit</button>";
                echo "<button onclick=\"if(confirm('Are you sure you want to deactivate this employee?')) location.href='index.php?delete_id=" . $row["id"] . "';\">Deactivate</button>";
                // إضافة زر Details مع معالجة خاصة للنصوص  . "</td>";

                echo "<button onclick=\"showEmployeeDetails(" . 
    intval($row["id"]) . ", '" . 
    addslashes(htmlspecialchars($row["ref"])) . "', '" . 
    addslashes(htmlspecialchars($row["lastName"])) . "', '" . 
    addslashes(htmlspecialchars($row["firstName"])) . "', '" . 
    addslashes(htmlspecialchars($row["position"])) . "', '" . 
    addslashes(htmlspecialchars($row["department"])) . "', '" . 
    addslashes(htmlspecialchars($row["nationality"])) . "', '" . 
    addslashes(htmlspecialchars($row["date_start"])) . "', '" . 
    addslashes(htmlspecialchars($row["contact_namber"])) . 
    "')\">Details</button>";
echo "</div>";
echo "</td>";
                echo "</div>";
                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No employees found.</td></tr>";
        }
                echo "<td>";
                echo "<td>";
// إضافة زر Details مع معالجة خاصة للنصوص

        ?>
      </tbody>
    </table>
  </div>
  <!-- إضافة أزرار التنقل بين الصفحات -->
    <div class="pagination">
        <?php if($total_pages > 1): ?>
            <?php if($page > 1): ?>
                <a href="?page=<?php echo ($page-1); ?>" class="page-link">&laquo; السابق</a>
            <?php endif; ?>
            
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <?php if($i == $page): ?>
                    <span class="current-page"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>" class="page-link"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if($page < $total_pages): ?>
                <a href="?page=<?php echo ($page+1); ?>" class="page-link">التالي &raquo;</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.pagination {
    margin: 20px 0;
    text-align: center;
}

.page-link {
    display: inline-block;
    padding: 8px 16px;
    margin: 0 4px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-decoration: none;
    color: #333;
}

.page-link:hover {
    background-color: #f5f5f5;
}

.current-page {
    display: inline-block;
    padding: 8px 16px;
    margin: 0 4px;
    border: 1px solid #007bff;
    border-radius: 4px;
    background-color: #007bff;
    color: white;
}
</style>

<!-- إضافة حقل مخفي لتخزين معرف الموظف الحالي -->
<input type="hidden" id="currentEmployeeId" value="<?php echo $edit_id; ?>">

<?php
$conn->close();
?>

<style>

    /* نمط البطاقة  */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.4);
        
    }

    .modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
    border-radius: 5px;
    position: relative;
    max-height: 80vh; /* تحديد ارتفاع أقصى للبطاقة */
    overflow-y: auto; /* تفعيل التمرير العمودي */
}

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover {
        color: black;
    }

    .employee-details {
        margin-top: 20px;
    }

    .detail-row {
        display: flex;
        margin-bottom: 10px;
        border-bottom: 1px solid #eee;
        padding: 5px 0;
    }

    .detail-label {
        font-weight: bold;
        width: 150px;
    }

    .detail-value {
        flex: 1;
    }
    button {
        margin-bottom: 3px;
        margin-top: 3px;
    }
</style>


<!-- Modal للتفاصيل -->
<div id="employeeModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Employee Details</h2>
        <div class="employee-details" id="employeeDetails"></div>
        <div id="employeeDevices" class="devices-section"></div>
        <div class="device-assignment">
            <h3>Assign Device</h3>
            <input type="hidden" id="currentEmployeeId">
            <div class="device-select-container">
                <select id="deviceSelect_modal">
                    <option value="">Select Device</option>
                </select>
                <button onclick="assignDevice()" class="assign-button">Assign Device</button>
            </div>
        </div>
        <div class="export-section">
            <button onclick="exportEmployeeDetails()" class="export-button">Export to PDF</button>
        </div>
    </div>
</div>



<script>
    // الحصول على عناصر Modal
    var modal = document.getElementById("employeeModal");
    var span = document.getElementsByClassName("close")[0];

    // عند النقر على زر الإغلاق
    span.onclick = function() {
        modal.style.display = "none";
    }

    // إغلاق Modal عند النقر خارجها
    window.onclick = function(event) {
        const modal = document.getElementById('employeeModal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // دالة لعرض تفاصيل الموظف
async function showEmployeeDetails(id, ref, lastName, firstName, position, department, nationality, date_start, contact_namber) {
    console.log('Showing details for employee:', { id, ref, lastName, firstName });
    
    const modal = document.getElementById('employeeModal');
    const details = document.getElementById('employeeDetails');
    const currentEmployeeId = document.getElementById('currentEmployeeId');
    
    if (!modal || !details || !currentEmployeeId) {
        console.error('Required DOM elements not found');
        return;
    }
    
    currentEmployeeId.value = id;
    
    details.innerHTML = `
        <div class="detail-row">
            <div class="detail-label">ID:</div>
            <div class="detail-value">${id}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Reference:</div>
            <div class="detail-value">${ref}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Full Name:</div>
            <div class="detail-value">${firstName} ${lastName}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Position:</div>
            <div class="detail-value">${position}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Department:</div>
            <div class="detail-value">${department}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Nationality:</div>
            <div class="detail-value">${nationality}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Start Date:</div>
            <div class="detail-value">${date_start}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Contact Number:</div>
            <div class="detail-value">${contact_namber}</div>
        </div>
    `;

    // تحميل الأجهزة
    console.log('Loading devices for employee:', id);
    try {
        await loadEmployeeDevices(id);
        await loadAvailableDevices();
        console.log('Devices loaded successfully');
    } catch (error) {
        console.error('Error loading devices:', error);
    }
    
    // إضافة معالج حدث للزر Close
    const closeBtn = modal.querySelector('.close');
    if (closeBtn) {
        closeBtn.onclick = function() {
            modal.style.display = "none";
        }
    }
    
    // عرض النافذة المنبثقة
    modal.style.display = "block";
}

// إغلاق النافذة المنبثقة عند النقر خارجها
window.onclick = function(event) {
    const modal = document.getElementById('employeeModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

// دالة لجلب الأجهزة المخصصة للموظف
async function loadEmployeeDevices(employeeId) {
  try {
      const response = await fetch(`get_employee_devices.php?employee_id=${employeeId}`);

        console.log('Response status:', response.status);
        
        const responseText = await response.text();
        console.log('Raw response:', responseText);
        let data;
      try {
          data = JSON.parse(responseText);
      } catch (error) {
          console.error('Error parsing JSON:', error);
          document.getElementById('employeeDevices').innerHTML = 
              '<p class="error">Error parsing device data.</p>';
          return;
      }

      // هنا نضيف السطر المطلوب
      const container = document.getElementById('employeeDevices');

        
        
        if (!data.success || !data.devices) {
            console.error('Invalid response:', data);
            container.innerHTML = '<p class="error">Error loading devices.</p>';
            return;
        }
        
        if (data.devices.length === 0) {
            container.innerHTML = '<p>No devices assigned to this employee.</p>';
            return;
        }
        
        container.innerHTML = `<h3>Assigned Devices (${data.devices.length})</h3>`;
        data.devices.forEach(device => {
            container.innerHTML += `
                <div class="device-item">
                    <strong>${device.name_device || 'Unknown Device'}</strong>
                    ${device.model_device ? ` - ${device.model_device}` : ''}
                    <br>
                    <small>System: ${device.system_exp || 'N/A'}</small>
                    <br>
                    <small>IP: ${device.ip || 'N/A'}</small>
                    <br>
                    <small>MAC: ${device.mac_adress || 'N/A'}</small>
                    <div class="device-actions">
                        <button onclick="unassignDevice(${device.id_dev})">
                            Remove Device
                        </button>
                    </div>
                </div>`;
        });
    } catch (error) {
        console.error('Error loading devices:', error);
        document.getElementById('employeeDevices').innerHTML = 
            '<p class="error">Error loading devices. Please check console for details.</p>';
    }
}

// دالة لجلب الأجهزة المتاحة
async function loadAvailableDevices() {
  console.log('Loading available devices...');
  try {
      const response = await fetch('get_available_devices.php');
      console.log('Response received:', response.status);

      const responseText = await response.text();
      console.log('Response text:', responseText);

      let data;
      try {
          data = JSON.parse(responseText);
      } catch (error) {
          console.error('Error parsing JSON:', error);
          alert('حدث خطأ في تحليل البيانات المستلمة من الخادم.');
          return;
      }
      console.log('Devices data:', data);

        
      const select = document.getElementById('deviceSelect_modal');
      if (!select) {
        console.error('Select element not found');
        return;
      }
      select.innerHTML = '<option value="">Select Device</option>';
        
      if (data.success && Array.isArray(data.devices)) {
          const availableDevices = data.devices.filter(device => device.is_available);
          console.log('Available devices:', availableDevices.length);

            if (availableDevices.length > 0) {
                availableDevices.forEach(device => {
                    select.innerHTML += `
                        <option value="${device.id_dev}">
                            ${device.name_device} - ${device.model_device}
                            (${device.system_exp || 'N/A'})
                        </option>`;
                });
            } else {
                select.innerHTML = '<option value="">No devices available</option>';
            }
        } else {
          console.error('Data format is incorrect or devices array is missing:', data);
          alert('البيانات المستلمة من الخادم غير صحيحة.');
      }
  } catch (error) {
      console.error('Error in loadAvailableDevices:', error);
      const select = document.getElementById('deviceSelect_modal');
      if (select) {
        select.innerHTML = '<option value="">Error loading devices</option>';
      }
      alert('حدث خطأ أثناء تحميل الأجهزة. الرجاء المحاولة مرة أخرى.');
  }
}

// دالة لتعيين جهاز للموظف
async function assignDevice() {
    const employeeId = document.getElementById('currentEmployeeId').value;
    const deviceSelect = document.getElementById('deviceSelect_modal');
    const deviceId = deviceSelect.value;
    
    if (!deviceId) {
        alert('Please select a device');
        return;
    }
    
    try {
        const response = await fetch('assign_device.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                employee_id: employeeId,
                device_id: deviceId
            })
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            // تحديث قوائم الأجهزة
            await loadEmployeeDevices(employeeId);
            await loadAvailableDevices();
            deviceSelect.value = ''; // إعادة تعيين القائمة المنسدلة
        } else {
            console.error('Server error:', data.error);
            alert('Error assigning device: ' + (data.error || 'Unknown error. Please check the console for details.'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error assigning device. Please try again. Check the console for details.');
    }
}

// دالة لإزالة تخصيص جهاز من الموظف
async function unassignDevice(deviceId) {
    if (!confirm('Are you sure you want to remove this device?')) {
        return;
    }
    
    try {
        const response = await fetch('unassign_device.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                device_id: deviceId
            })
        });
        
        if (response.ok) {
            const employeeId = document.getElementById('currentEmployeeId').value;
            await loadEmployeeDevices(employeeId);
            await loadAvailableDevices();
        } else {
            alert('Error removing device');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function exportEmployeeDetails() {
    const employeeId = document.getElementById('currentEmployeeId').value;
    window.location.href = `export_employee_pdf.php?employee_id=${employeeId}`;
}
</script>
</body>
</html>