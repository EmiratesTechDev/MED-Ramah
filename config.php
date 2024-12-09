<?php
/* معلومات الاتصال بقاعدة البيانات */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'emlist');
 
/* محاولة الاتصال بقاعدة البيانات */
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// التحقق من الاتصال
if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

// تعيين charset للاتصال
$conn->set_charset("utf8mb4");
?>