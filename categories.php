<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

check_admin_login();

// จัดการการลบหมวดหมู่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    if (verify_csrf_token($_POST['csrf_token'])) {
        $id = (int)$_POST['delete_id'];
        
        // เช็คก่อนว่ามีสินค้าอยู่ในหมวดหมู่นี้หรือไม่
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $checkStmt->execute([$id]);
        if ($checkStmt->fetchColumn() > 0) {
            $_SESSION['error'] = "ไม่สามารถลบได้ เนื่องจากมีสินค้าอยู่ในหมวดหมู่นี้";
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            if ($stmt->execute([$id])) {
                $_SESSION['success'] = "ลบหมวดหมู่เรียบร้อยแล้ว";
            }
        }
        header("Location: categories.php");
        exit;
    }
}

// ดึงข้อมูลหมวดหมู่ทั้งหมด
$stmt = $pdo->query("SELECT * FROM categories ORDER BY id DESC");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการหมวดหมู่ - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 font-sans flex flex-col md:flex-row min-h-screen">

    <?php include '../includes/admin_sidebar.php'; ?>

    <main class="flex-1 w-full p-4 md:p-8">
        
        <!-- เช็ค Alert -->
        <?php if (isset($_SESSION['success'])): ?>
            <script>Swal.fire({ icon: 'success', title: 'สำเร็จ', text: '<?php echo $_SESSION['success']; ?>', timer: 2000, showConfirmButton: false });</script>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <script>Swal.fire({ icon: 'error', title: 'ข้อผิดพลาด', text: '<?php echo $_SESSION['error']; ?>' });</script>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0"><i class="fas fa-tags mr-2"></i>จัดการหมวดหมู่</h1>
            <a href="category_add.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow transition">
                <i class="fas fa-plus mr-2"></i>เพิ่มหมวดหมู่ใหม่
            </a>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ชื่อหมวดหมู่</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Slug (URL)</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">สถานะ</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap"><?php echo $cat['id']; ?></p>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 font-bold whitespace-no-wrap"><?php echo escape($cat['name']); ?></p>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-500 whitespace-no-wrap"><?php echo escape($cat['slug']); ?></p>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                <?php if ($cat['status'] == 'active'): ?>
                                    <span class="relative inline-block px-3 py-1 font-semibold text-green-900 leading-tight">
                                        <span aria-hidden class="absolute inset-0 bg-green-200 opacity-50 rounded-full"></span>
                                        <span class="relative text-xs">เปิดใช้งาน</span>
                                    </span>
                                <?php else: ?>
                                    <span class="relative inline-block px-3 py-1 font-semibold text-red-900 leading-tight">
                                        <span aria-hidden class="absolute inset-0 bg-red-200 opacity-50 rounded-full"></span>
                                        <span class="relative text-xs">ปิดใช้งาน</span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm text-center">
                                <div class="flex justify-center space-x-2">
                                    <!-- ปุ่มแก้ไข -->
                                    <a href="category_edit.php?id=<?php echo $cat['id']; ?>" class="text-blue-500 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 p-2 rounded transition" title="แก้ไข">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <!-- ปุ่มลบ -->
                                    <form id="delete-form-<?php echo $cat['id']; ?>" method="POST" class="hidden">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="delete_id" value="<?php echo $cat['id']; ?>">
                                    </form>

                                    <button onclick="confirmDelete(<?php echo $cat['id']; ?>, '<?php echo escape($cat['name']); ?>')" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded transition" title="ลบ">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($categories)): ?>
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center border-b border-gray-200 bg-white text-sm text-gray-500">
                                ยังไม่มีข้อมูลหมวดหมู่
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
                title: 'ยืนยันการลบ?',
                text: "คุณต้องการลบหมวดหมู่ '" + name + "' ใช่หรือไม่? (หากมีสินค้าในหมวดหมู่นี้จะไม่สามารถลบได้)",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ใช่, ลบเลย!',
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