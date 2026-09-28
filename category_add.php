<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

check_admin_login();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = "CSRF Token ไม่ถูกต้อง";
    } else {
        $name = trim($_POST['name']);
        $slug = trim($_POST['slug']);
        $description = trim($_POST['description']);
        $status = $_POST['status'];

        // ตรวจสอบว่า slug ซ้ำหรือไม่
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
        $stmtCheck->execute([$slug]);
        if ($stmtCheck->fetchColumn() > 0) {
            $error = "Slug นี้มีในระบบแล้ว กรุณาใช้ชื่ออื่น (เช่น $slug-2)";
        } else {
            // บันทึกลงฐานข้อมูล
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, status) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $slug, $description, $status])) {
                $_SESSION['success'] = "เพิ่มหมวดหมู่ใหม่เรียบร้อยแล้ว";
                header("Location: categories.php");
                exit;
            } else {
                $error = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มหมวดหมู่ - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 font-sans flex flex-col md:flex-row min-h-screen">

    <?php include '../includes/admin_sidebar.php'; ?>

    <main class="flex-1 w-full p-4 md:p-8">
        
        <div class="mb-6">
            <a href="categories.php" class="text-gray-500 hover:text-blue-600 transition text-sm font-semibold">
                <i class="fas fa-arrow-left mr-1"></i> กลับไปหน้ารายการหมวดหมู่
            </a>
            <h1 class="text-2xl font-bold text-gray-800 mt-2">เพิ่มหมวดหมู่สินค้าใหม่</h1>
        </div>

        <?php if ($error): ?>
            <script>Swal.fire({ icon: 'error', title: 'ไม่สามารถบันทึกได้', text: '<?php echo escape($error); ?>' });</script>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-sm p-6 md:p-8 max-w-3xl">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">ชื่อหมวดหมู่ <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required placeholder="เช่น น้ำมันเครื่องรถยนต์" 
                               class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Slug (URL ของหมวดหมู่) <span class="text-red-500">*</span></label>
                        <input type="text" name="slug" required placeholder="เช่น car-engine-oil" pattern="[a-zA-Z0-9-]+" title="ภาษาอังกฤษ ตัวเลข และขีดกลาง (-) เท่านั้น"
                               class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">ใช้ตัวอักษรภาษาอังกฤษ ตัวเลข และขีดกลาง (-) เท่านั้น ไม่เว้นวรรค</p>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">รายละเอียด (ทางเลือก)</label>
                    <textarea name="description" rows="3" placeholder="อธิบายเกี่ยวกับหมวดหมู่นี้..." 
                              class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="mb-8">
                    <label class="block text-gray-700 text-sm font-bold mb-2">สถานะ</label>
                    <select name="status" class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="active">เปิดใช้งาน (แสดงหน้าร้าน)</option>
                        <option value="inactive">ซ่อนไว้ (ไม่แสดงหน้าร้าน)</option>
                    </select>
                </div>

                <div class="flex items-center">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow transition mr-4">
                        <i class="fas fa-save mr-2"></i> บันทึกข้อมูล
                    </button>
                    <a href="categories.php" class="text-gray-500 hover:text-gray-800 font-semibold transition">ยกเลิก</a>
                </div>
            </form>
        </div>

    </main>

</body>
</html>