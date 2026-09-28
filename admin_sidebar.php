<?php
// หาว่าปัจจุบันอยู่หน้าอะไร เพื่อทำ Active Menu
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!-- Mobile Header & Hamburger -->
<div class="md:hidden bg-gray-800 text-white flex justify-between items-center p-4 shadow-md sticky top-0 z-50">
    <div class="text-xl font-bold">CG Racing Admin</div>
    <button id="mobile-admin-btn" class="focus:outline-none">
        <i class="fas fa-bars text-2xl"></i>
    </button>
</div>

<!-- Sidebar -->
<aside id="admin-sidebar" class="bg-gray-800 text-white w-64 min-h-screen flex-col hidden md:flex fixed md:relative z-40 h-full transition-all duration-300">
    <div class="p-6 flex items-center justify-center border-b border-gray-700">
        <a href="index.php" class="text-2xl font-bold text-white flex items-center">
            <i class="fas fa-cogs mr-2"></i> Admin Panel
        </a>
    </div>
    
    <div class="p-4">
        <div class="text-gray-400 text-xs uppercase font-bold tracking-wider mb-2 mt-4">เมนูหลัก</div>
        <nav class="space-y-1">
            <a href="index.php" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'index.php') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                <i class="fas fa-tachometer-alt w-6"></i> Dashboard
            </a>
            <a href="products.php" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'products.php' || $currentPage == 'product_add.php' || $currentPage == 'product_edit.php') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                <i class="fas fa-box w-6"></i> จัดการสินค้า
            </a>
            <a href="categories.php" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'categories.php') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                <i class="fas fa-tags w-6"></i> จัดการหมวดหมู่
            </a>
        </nav>

        <div class="text-gray-400 text-xs uppercase font-bold tracking-wider mb-2 mt-8">การตั้งค่า</div>
        <nav class="space-y-1">
            <a href="settings.php" class="flex items-center px-4 py-3 rounded-lg transition <?php echo ($currentPage == 'settings.php') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                <i class="fas fa-cog w-6"></i> ตั้งค่าเว็บไซต์
            </a>
        </nav>
    </div>
    
    <div class="mt-auto p-4 border-t border-gray-700">
        <div class="flex items-center mb-4 px-2">
            <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold mr-3">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <p class="text-sm font-semibold"><?php echo isset($_SESSION['admin_name']) ? escape($_SESSION['admin_name']) : 'Admin'; ?></p>
            </div>
        </div>
        <a href="logout.php" class="flex items-center justify-center w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition">
            <i class="fas fa-sign-out-alt mr-2"></i> ออกจากระบบ
        </a>
    </div>
</aside>

<script>
    // Script สำหรับเปิด-ปิดเมนูบนมือถือ
    document.getElementById('mobile-admin-btn').addEventListener('click', function() {
        const sidebar = document.getElementById('admin-sidebar');
        sidebar.classList.toggle('hidden');
        sidebar.classList.toggle('absolute');
        sidebar.classList.toggle('top-16');
        sidebar.classList.toggle('w-full');
    });
</script>