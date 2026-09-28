<?php
session_start();

// XSS Protection
function escape($html) {
    return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

// CSRF Token Generation
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF Validation
function verify_csrf_token($token) {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}

// Image Upload Security Check
function upload_image($file, $destination_folder) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
    $max_size = 5 * 1024 * 1024; // 5MB

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($mime, $allowed_types) || !in_array($ext, $allowed_exts)) {
        return ['status' => false, 'message' => 'ประเภทไฟล์ไม่รองรับ'];
    }

    if ($file['size'] > $max_size) {
        return ['status' => false, 'message' => 'ขนาดไฟล์เกิน 5MB'];
    }

    $new_filename = uniqid('img_', true) . '.' . $ext;
    $target_path = $destination_folder . '/' . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return ['status' => true, 'filename' => $new_filename];
    }
    
    return ['status' => false, 'message' => 'เกิดข้อผิดพลาดในการอัปโหลด'];
}
?>