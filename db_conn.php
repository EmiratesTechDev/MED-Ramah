<?php
$conn = mysqli_connect("localhost", "root", "", "emlist");
if (!$conn) {
 die("Connection failed: " . mysqli_connect_error());
}
?>