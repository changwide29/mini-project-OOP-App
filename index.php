<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// ตรวจสอบสิทธิ์การเข้าถึง (ต้อง Login ก่อน)
check_admin_login();

// 1. นับจำนวนสินค้าทั้งหมด
$stmtProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE status != 'inactive'");
$totalProducts = $stmtProducts->fetchColumn();

// 2. นับจำนวนหมวดหมู่
$stmtCategories = $pdo->query("SELECT COUNT(*) FROM categories WHERE status = 'active'");
$totalCategories = $stmtCategories->fetchColumn();

// 3. สินค้าที่สต๊อกหมด (Stock = 0)
$stmtOutOfStock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock <= 0 AND status != 'inactive'");
$outOfStock = $stmtOutOfStock->fetchColumn();

// 4. สินค้าที่สต๊อกต่ำ (Stock 1 - 5 ชิ้น)
$stmtLowStock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock > 0 AND stock <= 5 AND status != 'inactive'");
$lowStock = $stmtLowStock->fetchColumn();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CG Racing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 font-sans flex flex-col md:flex-row min-h-screen">

    <!-- เรียกใช้ Admin Sidebar -->
    <?php include '../includes/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 w-full p-4 md:p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Dashboard</h1>
            <a href="../index.php" target="_blank" class="bg-blue-100 text-blue-700 px-4 py-2 rounded-lg font-semibold hover:bg-blue-200 transition text-sm">
                <i class="fas fa-external-link-alt mr-1"></i> ดูหน้าร้าน
            </a>
        </div>

        <!-- Dashboard Cards (Statistics) -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
            
            <!-- Card: จำนวนสินค้าทั้งหมด -->
            <div class="bg-white rounded-lg shadow-sm p-6 flex items-center border-l-4 border-blue-500">
                <div class="p-4 rounded-full bg-blue-100 text-blue-600 mr-4">
                    <i class="fas fa-box-open text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-semibold mb-1">สินค้าทั้งหมด (เปิดใช้งาน)</p>
                    <p class="text-3xl font-bold text-gray-800"><?php echo number_format($totalProducts); ?></p>
                </div>
            </div>

            <!-- Card: จำนวนหมวดหมู่ -->
            <div class="bg-white rounded-lg shadow-sm p-6 flex items-center border-l-4 border-purple-500">
                <div class="p-4 rounded-full bg-purple-100 text-purple-600 mr-4">
                    <i class="fas fa-tags text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-semibold mb-1">หมวดหมู่สินค้า</p>
                    <p class="text-3xl font-bold text-gray-800"><?php echo number_format($totalCategories); ?></p>
                </div>
            </div>

            <!-- Card: สินค้าใกล้หมดสต๊อก -->
            <div class="bg-white rounded-lg shadow-sm p-6 flex items-center border-l-4 border-orange-500">
                <div class="p-4 rounded-full bg-orange-100 text-orange-600 mr-4">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-semibold mb-1">สต๊อกเหลือน้อย (1-5 ชิ้น)</p>
                    <p class="text-3xl font-bold text-gray-800"><?php echo number_format($lowStock); ?></p>
                </div>
            </div>

            <!-- Card: สินค้าหมดสต๊อก -->
            <div class="bg-white rounded-lg shadow-sm p-6 flex items-center border-l-4 border-red-500">
                <div class="p-4 rounded-full bg-red-100 text-red-600 mr-4">
                    <i class="fas fa-times-circle text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-semibold mb-1">สินค้าหมด (0 ชิ้น)</p>
                    <p class="text-3xl font-bold text-red-600"><?php echo number_format($outOfStock); ?></p>
                </div>
            </div>

        </div>

        <!-- Warning Section for Out of Stock Products -->
        <?php if($outOfStock > 0): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-8 rounded shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700 font-semibold">
                        แจ้งเตือน! มีสินค้า <?php echo $outOfStock; ?> รายการที่สต๊อกหมด กรุณา <a href="products.php" class="underline font-bold">ตรวจสอบและอัปเดตสต๊อกสินค้า</a>
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </main>

</body>
</html>