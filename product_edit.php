<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

check_admin_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';

// ดึงข้อมูลสินค้าปัจจุบัน พร้อมรูปภาพหลัก
$stmt = $pdo->prepare("SELECT p.*, 
        (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id ORDER BY is_main DESC LIMIT 1) as image 
        FROM products p WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['error'] = "ไม่พบข้อมูลสินค้าที่ต้องการแก้ไข";
    header("Location: products.php");
    exit;
}

// ดึงหมวดหมู่ทั้งหมดสำหรับ Dropdown
$catStmt = $pdo->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC");
$categories = $catStmt->fetchAll();

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

        // เช็ค SKU ซ้ำ (ยกเว้นไอดีของตัวเอง)
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM products WHERE sku = ? AND id != ?");
        $stmtCheck->execute([$sku, $id]);
        
        if ($stmtCheck->fetchColumn() > 0) {
            $error = "รหัสสินค้า (SKU) นี้ถูกใช้ไปแล้ว กรุณาใช้รหัสอื่น";
        } else {
            try {
                $pdo->beginTransaction();

                // 1. อัปเดตข้อมูลสินค้าหลัก
                $updateStmt = $pdo->prepare("UPDATE products SET category_id=?, name=?, sku=?, regular_price=?, sale_price=?, stock=?, description=?, status=?, is_recommended=? WHERE id=?");
                $updateStmt->execute([$category_id, $name, $sku, $regular_price, $sale_price, $stock, $description, $status, $is_recommended, $id]);
                
                // 2. จัดการอัปโหลดรูปภาพใหม่ (ถ้ามีการเลือกไฟล์เข้ามาใหม่)
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = upload_image($_FILES['image'], '../uploads/products');
                    
                    if ($uploadResult['status']) {
                        // ลบรูปภาพเก่าออกจาก Server เพื่อประหยัดพื้นที่
                        if (!empty($product['image'])) {
                            $oldFilePath = '../uploads/products/' . $product['image'];
                            if (file_exists($oldFilePath)) {
                                unlink($oldFilePath);
                            }
                            // ลบข้อมูลรูปเก่าจากฐานข้อมูล
                            $delImgStmt = $pdo->prepare("DELETE FROM product_images WHERE product_id = ?");
                            $delImgStmt->execute([$id]);
                        }

                        // เพิ่มรูปภาพใหม่ลงฐานข้อมูล
                        $imgStmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url, is_main) VALUES (?, ?, 1)");
                        $imgStmt->execute([$id, $uploadResult['filename']]);
                    } else {
                        throw new Exception("อัปเดตข้อมูลสำเร็จ แต่อัปโหลดรูปภาพใหม่ล้มเหลว: " . $uploadResult['message']);
                    }
                }

                $pdo->commit();
                $_SESSION['success'] = "อัปเดตข้อมูลสินค้าเรียบร้อยแล้ว";
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
    <title>แก้ไขสินค้า - Admin Panel</title>
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
            <h1 class="text-2xl font-bold text-gray-800 mt-2">แก้ไขสินค้า: <?php echo escape($product['name']); ?></h1>
        </div>

        <?php if ($error): ?>
            <script>Swal.fire({ icon: 'error', title: 'ข้อผิดพลาด', text: '<?php echo escape($error); ?>' });</script>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-sm p-6 md:p-8">
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- ข้อมูลหลัก -->
                    <div class="md:col-span-2 space-y-6">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">ชื่อสินค้า <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="<?php echo escape($product['name']); ?>" required class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">รหัสสินค้า (SKU) <span class="text-red-500">*</span></label>
                                <input type="text" name="sku" value="<?php echo escape($product['sku']); ?>" required class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">หมวดหมู่ <span class="text-red-500">*</span></label>
                                <select name="category_id" required class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo ($product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                            <?php echo escape($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">รายละเอียดสินค้า</label>
                            <textarea name="description" rows="5" class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo escape($product['description']); ?></textarea>
                        </div>
                    </div>

                    <!-- ราคา สต๊อก และรูปภาพ -->
                    <div class="space-y-6 bg-gray-50 p-4 rounded border">
                        
                        <!-- แสดงรูปภาพปัจจุบัน -->
                        <div class="text-center mb-4">
                            <p class="text-sm font-bold text-gray-700 mb-2">รูปภาพปัจจุบัน</p>
                            <?php $currentImg = $product['image'] ? '../uploads/products/' . escape($product['image']) : '../assets/images/no-image.jpg'; ?>
                            <img src="<?php echo $currentImg; ?>" class="w-32 h-32 object-cover rounded shadow mx-auto mb-2">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">เปลี่ยนรูปภาพใหม่ (ไม่บังคับ)</label>
                            <input type="file" name="image" accept="image/jpeg, image/png, image/webp" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">ราคาปกติ (บาท) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="regular_price" value="<?php echo $product['regular_price']; ?>" required min="0" class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">ราคาพิเศษ (บาท)</label>
                            <input type="number" step="0.01" name="sale_price" value="<?php echo $product['sale_price']; ?>" min="0" class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">จำนวนสต๊อก <span class="text-red-500">*</span></label>
                            <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required min="0" class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">สถานะสินค้า</label>
                            <select name="status" class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="active" <?php echo ($product['status'] == 'active') ? 'selected' : ''; ?>>เปิดขาย</option>
                                <option value="low_stock" <?php echo ($product['status'] == 'low_stock') ? 'selected' : ''; ?>>ใกล้หมด</option>
                                <option value="out_of_stock" <?php echo ($product['status'] == 'out_of_stock') ? 'selected' : ''; ?>>สินค้าหมด</option>
                                <option value="inactive" <?php echo ($product['status'] == 'inactive') ? 'selected' : ''; ?>>ซ่อนไว้ (ไม่ขาย)</option>
                            </select>
                        </div>

                        <div class="flex items-center mt-4">
                            <input type="checkbox" name="is_recommended" id="is_recommended" value="1" <?php echo $product['is_recommended'] ? 'checked' : ''; ?> class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            <label for="is_recommended" class="ml-2 text-sm font-medium text-gray-900 cursor-pointer text-yellow-600"><i class="fas fa-star mr-1"></i> ตั้งเป็นสินค้าแนะนำ</label>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end border-t pt-6">
                    <a href="products.php" class="text-gray-500 hover:text-gray-800 font-semibold transition mr-4">ยกเลิก</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-8 rounded shadow transition">
                        <i class="fas fa-save mr-2"></i> บันทึกการเปลี่ยนแปลง
                    </button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>