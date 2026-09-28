<?php
// 1. ปิดการแสดงผล Error ของ PHP ทุกชนิดบนหน้าเว็บ
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// 2. ตั้งค่าการเชื่อมต่อฐานข้อมูล
define('DB_HOST', 'localhost');
define('DB_USER', 'u835268696_cgracingshop'); // เปลี่ยนเป็น Username ฐานข้อมูลของคุณ
define('DB_PASS', '!Chang2992'); // เปลี่ยนเป็น Password ฐานข้อมูลของคุณ
define('DB_NAME', 'u835268696_cgracingshop');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        // ยังให้ PDO จับ Error ไว้หลังบ้าน (เผื่อใช้เก็บ Log) แต่จะไม่แสดงออกทางหน้าจอ
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    // 3. ปิดไม่ให้แสดงข้อความเชิงลึก ($e->getMessage()) ออกทางหน้าจอ
    // แต่แสดงข้อความทั่วไปแทน เพื่อความปลอดภัย
    die("เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล กรุณาติดต่อผู้ดูแลระบบ");
    
    // (ทางเลือก) หากคุณต้องการเก็บ Error ไว้ดูเองในไฟล์ log บนโฮสต์ ให้ลบ // บรรทัดล่างนี้ออก:
    // error_log("Database Connection Error: " . $e->getMessage());
}
?>