<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

check_admin_login();

// จัดการการลบสินค้า
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    if (verify_csrf_token($_POST['csrf_token'])) {
        $id = (int)$_POST['delete_id'];
        
        // ดึงชื่อไฟล์รูปภาพมาลบออกจาก Server
        $imgStmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ?");
        $imgStmt->execute([$id]);
        $images = $imgStmt->fetchAll();
        foreach ($images as $img) {
            $filePath = '../uploads/products/' . $img['image_url'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // ลบสินค้า
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['success'] = "ลบสินค้าเรียบร้อยแล้ว";
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบสินค้า";
        }
        header("Location: products.php");
        exit;
    }
}

// ดึงข้อมูลสินค้าทั้งหมด พร้อมชื่อหมวดหมู่และรูปภาพหลัก
$sql = "SELECT p.*, c.name as category_name, 
        (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id ORDER BY is_main DESC LIMIT 1) as image 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.id DESC";
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสินค้า - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 font-sans flex flex-col md:flex-row min-h-screen">

    <?php include '../includes/admin_sidebar.php'; ?>

    <main class="flex-1 w-full p-4 md:p-8">
        
        <?php if (isset($_SESSION['success'])): ?>
            <script>Swal.fire({ icon: 'success', title: 'สำเร็จ', text: '<?php echo $_SESSION['success']; ?>', timer: 2000, showConfirmButton: false });</script>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <script>Swal.fire({ icon: 'error', title: 'ข้อผิดพลาด', text: '<?php echo $_SESSION['error']; ?>' });</script>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0"><i class="fas fa-box mr-2"></i>จัดการสินค้า</h1>
            <a href="product_add.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow transition">
                <i class="fas fa-plus mr-2"></i>เพิ่มสินค้าใหม่
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">รูปภาพ</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">สินค้า / SKU</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">หมวดหมู่</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ราคา</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">สต๊อก</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">สถานะ</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                <?php $imgUrl = $product['image'] ? '../uploads/products/' . escape($product['image']) : '../assets/images/no-image.jpg'; ?>
                                <img src="<?php echo $imgUrl; ?>" alt="Product" class="w-16 h-16 object-cover rounded shadow-sm">
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 font-bold whitespace-normal line-clamp-2"><?php echo escape($product['name']); ?></p>
                                <p class="text-gray-500 text-xs mt-1">SKU: <?php echo escape($product['sku']); ?></p>
                                <?php if($product['is_recommended']): ?>
                                    <span class="inline-block mt-1 px-2 py-0.5 bg-yellow-100 text-yellow-800 text-[10px] rounded border border-yellow-200"><i class="fas fa-star mr-1"></i>แนะนำ</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap"><?php echo escape($product['category_name']); ?></p>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                <?php if (!empty($product['sale_price'])): ?>
                                    <p class="text-red-600 font-bold whitespace-no-wrap">฿<?php echo number_format($product['sale_price'], 2); ?></p>
                                    <p class="text-gray-400 text-xs line-through whitespace-no-wrap">฿<?php echo number_format($product['regular_price'], 2); ?></p>
                                <?php else: ?>
                                    <p class="text-gray-900 font-bold whitespace-no-wrap">฿<?php echo number_format($product['regular_price'], 2); ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                <?php if ($product['stock'] > 10): ?>
                                    <span class="text-green-600 font-bold"><?php echo $product['stock']; ?></span>
                                <?php elseif ($product['stock'] > 0): ?>
                                    <span class="text-orange-500 font-bold"><?php echo $product['stock']; ?></span>
                                <?php else: ?>
                                    <span class="text-red-600 font-bold bg-red-100 px-2 py-1 rounded text-xs">หมด</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                <?php 
                                    $statusColors = [
                                        'active' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'เปิดขาย'],
                                        'low_stock' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'label' => 'ใกล้หมด'],
                                        'out_of_stock' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'สินค้าหมด'],
                                        'inactive' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => 'ปิดใช้งาน']
                                    ];
                                    $s = $statusColors[$product['status']];
                                ?>
                                <span class="px-2 py-1 rounded text-xs font-semibold <?php echo $s['bg'] . ' ' . $s['text']; ?>">
                                    <?php echo $s['label']; ?>
                                </span>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm text-center">
                                <div class="flex justify-center space-x-2">
                                    
                                    <!-- ปุ่มแก้ไข (อัปเดตเป็นลิงก์ <a> เรียบร้อย) -->
                                    <a href="product_edit.php?id=<?php echo $product['id']; ?>" class="inline-flex items-center justify-center w-8 h-8 text-blue-500 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 rounded transition" title="แก้ไข">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <!-- ปุ่มลบ -->
                                    <form id="delete-form-<?php echo $product['id']; ?>" method="POST" class="hidden">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="delete_id" value="<?php echo $product['id']; ?>">
                                    </form>

                                    <button type="button" onclick="confirmDelete(<?php echo $product['id']; ?>, '<?php echo escape($product['name']); ?>')" class="inline-flex items-center justify-center w-8 h-8 text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 rounded transition" title="ลบ">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($products)): ?>
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center border-b border-gray-200 bg-white text-sm text-gray-500">
                                ยังไม่มีข้อมูลสินค้าในระบบ
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        function confirmDelete(id, name) {
            Swal.fire({
                title: 'คุณแน่ใจหรือไม่?',
                text: "ต้องการลบสินค้า '" + name + "' การกระทำนี้ไม่สามารถย้อนกลับได้!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ยืนยันการลบ',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            })
        }
    </script>
</body>
</html>