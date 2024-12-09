<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
?>
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "emlist";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// إضافة قسم جديد
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_department"])) {
    $name = $_POST["department_name"];
    $sql = "INSERT INTO departmenttable (NameDepartment) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $name);
    
    if ($stmt->execute()) {
        $message = "Department added successfully";
        $message_type = "success";
    } else {
        $message = "Error adding department: " . $conn->error;
        $message_type = "error";
    }
}

// حذف قسم
if (isset($_GET["delete_id"])) {
    $id = $_GET["delete_id"];
    $sql = "DELETE FROM departmenttable WHERE IdDepartment = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "Department deleted successfully";
        $message_type = "success";
    } else {
        $message = "Error deleting department: " . $conn->error;
        $message_type = "error";
    }
}

// جلب كل الأقسام
$sql = "SELECT * FROM departmenttable";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departments Management</title>
    <link rel="stylesheet" href="css/aaa.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>



<style>



   .action-buttons {
    display: flex; /* ترتيب الأزرار بطريقة مرنة */
    flex-direction: column; /* ترتيب الأزرار عموديًا */
    align-items: flex-start; /* محاذاة الأزرار إلى اليسار */
    gap: 4px; /* تقليل المسافة بين الأزرار */
    margin: 15px auto; /* تقليل المسافة حول المجموعة وجعلها تتوسط الصفحة */
    max-width: 100px; /* تصغير العرض الأقصى للمجموعة */
}

.action-buttons button {
    padding: 6px 8px; /* تصغير الحشو الداخلي للأزرار */
    cursor: pointer;
    background-color: #007bff; /* لون الزر الافتراضي */
    color: white; /* لون النص */
    border: none; /* إزالة الحدود */
    border-radius: 4px; /* زوايا دائرية صغيرة */
    box-shadow: 0 3px 5px rgba(0, 0, 0, 0.1); /* إضافة ظل ناعم */
    font-size: 14px; /* تصغير حجم الخط */
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

button {
    margin-top: 6px;
}





    
</style>


    <!----------------------الشريط------------------------->


<!-- Sidebar Toggle Button -->
<button class="sidebar-toggle" onclick="toggleSidebar()">
    
</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
    <h3>Departments</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="index.php"><i class="fas fa-users"></i> Employees</a></li>
            <li class="active"><a href="manage_departments.php"><i class="fas fa-building"></i> Departments</a></li>
            <li><a href="mange_diveces.php"><i class="fas fa-laptop"></i> Devices</a></li>
            <li><a href="index.html"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>


<!-- Main Content Wrapper -->
<div class="main-content">


    <!---- -------------------------------------------->
<div class="container">
    <h1>Manage Departments</h1>
    
    <?php if (isset($message)): ?>
        <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <div class="form-container">
        <h2>Add New Department</h2>
        <form method="POST">
            <input type="text" name="department_name" placeholder="Department Name" required>
            <button type="submit" name="add_department">Add Department</button>
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Department Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row["IdDepartment"]); ?></td>
                        <td><?php echo htmlspecialchars($row["NameDepartment"]); ?></td>
                        <td>
                        <div class="action-buttons">
    <button onclick="if(confirm('Are you sure?')) location.href='?delete_id=<?php echo $row["IdDepartment"]; ?>'">
        Delete
    </button>
</div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    
</div>


    

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
}
</script>

</body>
</html>