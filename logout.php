<?php
session_start();

// ลบข้อมูล Session ของ Admin ทั้งหมด
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);

// พาเด้งกลับไปหน้า Login
header("Location: login.php");
exit;
?>