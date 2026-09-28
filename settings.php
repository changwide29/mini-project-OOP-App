<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

check_admin_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error'] = "CSRF Token ไม่ถูกต้อง";
    } else {
        $fb_url = trim($_POST['facebook_page_url']);
        $store_name = trim($_POST['store_name']);

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'facebook_page_url'");
            $stmt->execute([$fb_url]);

            $stmt2 = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'store_name'");
            $stmt2->execute([$store_name]);

            $pdo->commit();
            $_SESSION['success'] = "บันทึกการตั้งค่าเรียบร้อยแล้ว";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
        }
        
        header("Location: settings.php");
        exit;
    }
}

// ดึงการตั้งค่าปัจจุบัน
$stmt = $pdo->query("SELECT * FROM settings");
$settings_raw = $stmt->fetchAll();
$settings = [];
foreach ($settings_raw as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตั้งค่าเว็บไซต์ - Admin Panel</title>
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

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800"><i class="fas fa-cog mr-2"></i>ตั้งค่าเว็บไซต์</h1>
            <p class="text-gray-600 mt-1">จัดการข้อมูลพื้นฐานและการเชื่อมต่อกับ Facebook Messenger</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 max-w-2xl">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">ชื่อร้านค้า (Store Name)</label>
                    <input type="text" name="store_name" value="<?php echo escape($settings['store_name'] ?? ''); ?>" required class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">ชื่อร้านที่จะแสดงในบางส่วนของเว็บไซต์</p>
                </div>

                <div class="mb-8">
                    <label class="block text-gray-700 text-sm font-bold mb-2">ลิงก์ Facebook Page / Messenger URL <span class="text-red-500">*</span></label>
                    <div class="flex">
                        <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-r-0 border-gray-300 rounded-l-md">
                            <i class="fab fa-facebook-messenger text-blue-600"></i>
                        </span>
                        <input type="url" name="facebook_page_url" value="<?php echo escape($settings['facebook_page_url'] ?? ''); ?>" required placeholder="https://m.me/yourpage" class="rounded-none rounded-r-lg border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5">
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        ตัวอย่าง: <code>https://m.me/usernameเพจ</code><br>
                        * ลิงก์นี้จะถูกใช้เมื่อลูกค้ากดปุ่ม "สั่งซื้อผ่าน Facebook Messenger" ระบบจะส่งข้อความอัตโนมัติไปที่ลิงก์นี้
                    </p>
                </div>

                <div class="flex items-center">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow transition">
                        <i class="fas fa-save mr-2"></i> บันทึกการตั้งค่า
                    </button>
                </div>
            </form>
        </div>

    </main>

</body>
</html>