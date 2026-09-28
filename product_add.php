<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

check_admin_login();

// ดึงหมวดหมู่ทั้งหมดสำหรับใส่ใน Dropdown
$catStmt = $pdo->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC");
$categories = $catStmt->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = "CSRF Token ไม่ถูกต้อง";
    } else {
        $category_id = (int)$_POST['category_id'];
        $name = trim($_POST['name']);
        $sku = trim($_POST['sku']);
        $regular_price = (float)$_POST['regular_price'];
        $sale_price = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
        $stock = (int)$_POST['stock'];
        $description = trim($_POST['description']);
        $status = $_POST['status'];
        $is_recommended = isset($_POST['is_recommended']) ? 1 : 0;

        // เช็ค SKU ซ้ำ
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM products WHERE sku = ?");
        $stmtCheck->execute([$sku]);
        
        if ($stmtCheck->fetchColumn() > 0) {
            $error = "รหัสสินค้า (SKU) นี้มีในระบบแล้ว กรุณาใช้รหัสอื่น";
        } else {
            try {
                $pdo->beginTransaction();

                // 1. บันทึกข้อมูลสินค้า
                $stmt = $pdo->prepare("INSERT INTO products (category_id, name, sku, regular_price, sale_price, stock, description, status, is_recommended) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$category_id, $name, $sku, $regular_price, $sale_price, $stock, $description, $status, $is_recommended]);
                
                $product_id = $pdo->lastInsertId();

                // 2. จัดการอัปโหลดรูปภาพ (ถ้ามีการแนบไฟล์มา)
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = upload_image($_FILES['image'], '../uploads/products');
                    
                    if ($uploadResult['status']) {
                        $imgStmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url, is_main) VALUES (?, ?, 1)");
                        $imgStmt->execute([$product_id, $uploadResult['filename']]);
                    } else {
                        throw new Exception("บันทึกสินค้าสำเร็จ แต่อัปโหลดรูปภาพล้มเหลว: " . $uploadResult['message']);
                    }
                }

                $pdo->commit();
                $_SESSION['success'] = "เพิ่มสินค้าใหม่เรียบร้อยแล้ว";
                header("Location: products.php");
                exit;

            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
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
    <title>เพิ่มสินค้า - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 font-sans flex flex-col md:flex-row min-h-screen">

    <?php include '../includes/admin_sidebar.php'; ?>

    <main class="flex-1 w-full p-4 md:p-8">
        
        <div class="mb-6">
            <a href="products.php" class="text-gray-500 hover:text-blue-600 transition text-sm font-semibold">
                <i class="fas fa-arrow-left mr-1"></i> กลับไปหน้ารายการสินค้า
            </a>
            <h1 class="text-2xl font-bold text-gray-800 mt-2">เพิ่มสินค้าใหม่</h1>
        </div>

        <?php if ($error): ?>
            <script>Swal.fire({ icon: 'error', title: 'ไม่สามารถบันทึกได้', text: '<?php echo escape($error); ?>' });</script>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-sm p-6 md:p-8">
            <!-- ต้องใส่ enctype="multipart/form-data" เพื่อให้ส่งไฟล์ได้ -->
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- Column 1 & 2: ข้อมูลหลัก -->
                    <div class="md:col-span-2 space-y-6">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">ชื่อสินค้า <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">รหัสสินค้า (SKU) <span class="text-red-500">*</span></label>
                                <input type="text" name="sku" required class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">หมวดหมู่ <span class="text-red-500">*</span></label>
                                <select name="category_id" required class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                    <option value="">-- เลือกหมวดหมู่ --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo escape($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">รายละเอียดสินค้า</label>
                            <textarea name="description" rows="5" class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>

                    <!-- Column 3: ราคา สต๊อก และรูปภาพ -->
                    <div class="space-y-6 bg-gray-50 p-4 rounded border">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">รูปภาพสินค้า</label>
                            <input type="file" name="image" accept="image/jpeg, image/png, image/webp" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-xs text-gray-400 mt-1">รองรับ JPG, PNG, WEBP ขนาดไม่เกิน 5MB</p>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">ราคาปกติ (บาท) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="regular_price" required min="0" class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">ราคาพิเศษ / ลดราคา (บาท)</label>
                            <input type="number" step="0.01" name="sale_price" min="0" placeholder="เว้นว่างไว้ถ้าไม่มีการลดราคา" class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">จำนวนสต๊อก <span class="text-red-500">*</span></label>
                            <input type="number" name="stock" required min="0" value="1" class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">สถานะสินค้า</label>
                            <select name="status" class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                <option value="active">เปิดขาย</option>
                                <option value="inactive">ซ่อนไว้ (ไม่ขาย)</option>
                            </select>
                        </div>

                        <div class="flex items-center mt-4">
                            <input type="checkbox" name="is_recommended" id="is_recommended" value="1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            <label for="is_recommended" class="ml-2 text-sm font-medium text-gray-900 cursor-pointer text-yellow-600"><i class="fas fa-star mr-1"></i> ตั้งเป็นสินค้าแนะนำ (โชว์หน้าแรก)</label>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end border-t pt-6">
                    <a href="products.php" class="text-gray-500 hover:text-gray-800 font-semibold transition mr-4">ยกเลิก</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-8 rounded shadow transition">
                        <i class="fas fa-save mr-2"></i> บันทึกสินค้า
                    </button>
                </div>
            </form>
        </div>

    </main>

</body>
</html>