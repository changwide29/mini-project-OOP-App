<footer class="bg-gray-800 text-white mt-12 py-10">
    <div class="container mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-8">
        <div>
            <h3 class="text-xl font-bold mb-4">CG Racing</h3>
            <p class="text-gray-400 text-sm leading-relaxed">
                จำหน่ายน้ำมันเครื่อง สารเติมแต่ง และอะไหล่ยนต์คุณภาพสูง ตอบโจทย์ทุกการขับขี่ บริการจัดส่งรวดเร็วทันใจ
            </p>
        </div>
        <div>
            <h3 class="text-lg font-bold mb-4">ลิงก์ด่วน</h3>
            <ul class="space-y-2 text-gray-400 text-sm">
                <li><a href="index.php" class="hover:text-white transition">หน้าแรก</a></li>
                <li><a href="products.php" class="hover:text-white transition">สินค้าทั้งหมด</a></li>
            </ul>
        </div>
        <div>
            <h3 class="text-lg font-bold mb-4">ติดต่อเรา</h3>
            <div class="flex space-x-4">
                <?php
                // ดึงลิงก์ Facebook จาก Setting
                $stmtFb = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'facebook_page_url'");
                $fbUrl = $stmtFb->fetchColumn();
                $fbUrl = $fbUrl ? $fbUrl : '#';
                ?>
                <a href="<?php echo escape($fbUrl); ?>" target="_blank" class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center hover:bg-blue-700 transition">
                    <i class="fab fa-facebook-f"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="border-t border-gray-700 mt-8 pt-6 text-center text-gray-500 text-sm">
        &copy; <?php echo date('Y'); ?> CG Racing Shop. All rights reserved.
    </div>
</footer>