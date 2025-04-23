<?php
// config.php สำหรับเชื่อมต่อฐานข้อมูล MySQL
$host = "localhost";
$username = "root";
$password = "";
$dbname = "Audiogram";

// สร้างการเชื่อมต่อ
$conn = new mysqli($host, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}
?>