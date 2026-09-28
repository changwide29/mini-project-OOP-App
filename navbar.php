<?php
// ดึงข้อมูลหมวดหมู่สินค้าที่มีสถานะ 'active' เพื่อแสดงในเมนู
$catStmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC");
$navCategories = $catStmt->fetchAll();
?>
<nav class="bg-white shadow-md fixed w-full z-50 top-0 transition-all duration-300">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <!-- Logo -->
            <a href="index.php" class="text-2xl font-bold text-blue-600 flex items-center">
                <i class="fas fa-car-side mr-2"></i> CG Racing
            </a>

            <!-- Desktop Menu -->
            <div class="hidden md:flex space-x-6 items-center">
                <a href="index.php" class="text-gray-700 hover:text-blue-600 font-medium">หน้าแรก</a>
                <a href="products.php" class="text-gray-700 hover:text-blue-600 font-medium">สินค้าทั้งหมด</a>
                
                <!-- Dropdown หมวดหมู่ -->
                <div class="relative group">
                    <button class="text-gray-700 hover:text-blue-600 font-medium flex items-center">
                        หมวดหมู่ <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>
                    <div class="absolute left-0 mt-2 w-48 bg-white border rounded shadow-lg hidden group-hover:block">
                        <?php foreach ($navCategories as $cat): ?>
                            <a href="products.php?category=<?php echo $cat['id']; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                <?php echo escape($cat['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Search Bar (Desktop) -->
            <div class="hidden md:block flex-1 max-w-md mx-6">
                <form action="products.php" method="GET" class="relative">
                    <input type="text" name="search" placeholder="ค้นหาสินค้า, SKU..." class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </form>
            </div>

            <!-- Admin Login Link -->
            <div class="hidden md:block">
                <a href="admin/login.php" class="text-gray-500 hover:text-gray-800 text-sm">
                    <i class="fas fa-user-shield"></i> ผู้ดูแลระบบ
                </a>
            </div>

            <!-- Mobile Hamburger Button -->
            <button id="mobile-menu-btn" class="md:hidden text-gray-700 focus:outline-none">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden bg-white border-t pb-4">
        <form action="products.php" method="GET" class="px-4 py-3">
            <input type="text" name="search" placeholder="ค้นหาสินค้า..." class="w-full px-4 py-2 border rounded focus:outline-none">
        </form>
        <a href="index.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">หน้าแรก</a>
        <a href="products.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">สินค้าทั้งหมด</a>
        <div class="px-4 py-2 font-bold text-gray-800 bg-gray-50">หมวดหมู่สินค้า</div>
        <?php foreach ($navCategories as $cat): ?>
            <a href="products.php?category=<?php echo $cat['id']; ?>" class="block px-8 py-2 text-sm text-gray-700 hover:bg-blue-50">
                - <?php echo escape($cat['name']); ?>
            </a>
        <?php endforeach; ?>
        <a href="admin/login.php" class="block px-4 py-2 text-gray-500 border-t mt-2"><i class="fas fa-user-shield"></i> เข้าสู่ระบบจัดการร้าน</a>
    </div>
</nav>

<script>
    // สคริปต์สำหรับเปิด-ปิดเมนูมือถือ
    document.getElementById('mobile-menu-btn').addEventListener('click', function() {
        document.getElementById('mobile-menu').classList.toggle('hidden');
    });
</script>