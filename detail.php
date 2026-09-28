<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                       FROM products p 
                       JOIN categories c ON p.category_id = c.id 
                       WHERE p.id = ? AND p.status != 'inactive'");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: index.php");
    exit;
}

// Fetch Main Image
$imgStmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? ORDER BY is_main DESC LIMIT 1");
$imgStmt->execute([$id]);
$image = $imgStmt->fetch();
$imageUrl = $image ? 'uploads/products/' . escape($image['image_url']) : 'assets/images/no-image.jpg';

// Fetch Facebook URL Setting
$setStmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'facebook_page_url'");
$fbSetting = $setStmt->fetch();
$fbBaseUrl = $fbSetting ? $fbSetting['setting_value'] : '#';

// Generate Message
$message = "สวัสดีครับ สนใจสั่งซื้อสินค้า: " . $product['name'] . "\n";
$message .= "SKU: " . $product['sku'] . "\n";
$message .= "ราคา: ฿" . number_format($product['sale_price'] ?? $product['regular_price'], 2);
$fbLink = $fbBaseUrl . "?text=" . urlencode($message);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($product['name']); ?> - E-Commerce</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navbar Placeholder -->
    <?php include 'includes/navbar.php'; ?>

    <div class="container mx-auto px-4 py-8 mt-16">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="md:flex">
                <div class="md:w-1/2 p-4 border-r">
                    <img src="<?php echo $imageUrl; ?>" alt="<?php echo escape($product['name']); ?>" class="w-full h-auto object-cover rounded-lg">
                </div>
                <div class="md:w-1/2 p-8">
                    <div class="text-sm text-blue-600 mb-2 font-semibold">
                        <?php echo escape($product['category_name']); ?>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-4"><?php echo escape($product['name']); ?></h1>
                    <p class="text-gray-500 text-sm mb-4">SKU: <?php echo escape($product['sku']); ?></p>
                    
                    <div class="mb-6 flex items-baseline">
                        <?php if ($product['sale_price']): ?>
                            <span class="text-3xl font-bold text-red-600 mr-2">฿<?php echo number_format($product['sale_price'], 2); ?></span>
                            <span class="text-lg text-gray-400 line-through">฿<?php echo number_format($product['regular_price'], 2); ?></span>
                        <?php else: ?>
                            <span class="text-3xl font-bold text-gray-800">฿<?php echo number_format($product['regular_price'], 2); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="mb-6">
                        <h3 class="font-semibold text-gray-700 mb-2">สถานะสินค้า: 
                            <?php if ($product['stock'] > 10): ?>
                                <span class="text-green-600">มีสินค้า (<?php echo $product['stock']; ?>)</span>
                            <?php elseif ($product['stock'] > 0): ?>
                                <span class="text-orange-500">เหลือน้อย (<?php echo $product['stock']; ?>)</span>
                            <?php else: ?>
                                <span class="text-red-600">สินค้าหมด</span>
                            <?php endif; ?>
                        </h3>
                    </div>

                    <p class="text-gray-600 mb-8 leading-relaxed">
                        <?php echo nl2br(escape($product['description'])); ?>
                    </p>

                    <a href="<?php echo $product['stock'] > 0 ? $fbLink : '#'; ?>" 
                       target="_blank"
                       class="<?php echo $product['stock'] > 0 ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed'; ?> text-white font-bold py-3 px-8 rounded-lg shadow-md transition duration-300 flex items-center justify-center w-full md:w-auto">
                        <i class="fab fa-facebook-messenger mr-2 text-xl"></i>
                        สั่งซื้อผ่าน Facebook Messenger
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>