<?php
/**
 * ✅ Định nghĩa PATH_ROOT
 */
if (!defined('PATH_ROOT')) {
    define('PATH_ROOT', __DIR__ . '/../');
}

/**
 * ✅ Kết nối database
 */
function connectDB()
{
    try {
        // ⚠️ SỬA LẠI THÔNG TIN DATABASE CỦA BẠN
        $host = 'localhost';
        $dbname = 'tour_management';  // ← Sửa tên database
        $username = 'root';
        $password = '';

        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);

        return $pdo;
        
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        die("Lỗi kết nối database: " . $e->getMessage());
    }
}

/**
 * ✅ Upload file với debug chi tiết
 */
function uploadFile($file, $targetDir)
{
    error_log("=== uploadFile() START ===");
    error_log("File info: " . print_r($file, true));
    error_log("Target dir: " . $targetDir);

    // Kiểm tra lỗi upload
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'Vượt quá upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'Vượt quá MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'Upload không hoàn tất',
            UPLOAD_ERR_NO_FILE => 'Không có file',
            UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm',
            UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file',
            UPLOAD_ERR_EXTENSION => 'Extension chặn upload'
        ];
        
        $errorCode = $file['error'] ?? 'UNKNOWN';
        $errorMsg = $errors[$errorCode] ?? "Lỗi: $errorCode";
        error_log("Upload error: $errorMsg");
        return false;
    }

    // Kiểm tra file tồn tại
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        error_log("File không hợp lệ");
        return false;
    }

    // Tạo đường dẫn đầy đủ
    $fullTargetDir = PATH_ROOT . $targetDir;
    error_log("Full target dir: " . $fullTargetDir);

    // Tạo thư mục nếu chưa có
    if (!file_exists($fullTargetDir)) {
        error_log("Creating directory: " . $fullTargetDir);
        if (!mkdir($fullTargetDir, 0755, true)) {
            error_log("Failed to create directory");
            error_log("Parent dir: " . dirname($fullTargetDir));
            error_log("Parent writable: " . (is_writable(dirname($fullTargetDir)) ? 'YES' : 'NO'));
            return false;
        }
        error_log("Directory created successfully");
    }

    // Kiểm tra quyền ghi
    if (!is_writable($fullTargetDir)) {
        error_log("Directory not writable: " . $fullTargetDir);
        $perms = fileperms($fullTargetDir);
        error_log("Current permissions: " . substr(sprintf('%o', $perms), -4));
        return false;
    }

    // Tạo tên file unique - FIX: Không dùng cả uniqid và time
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $newFileName = 'staff_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;
    $relativePath = $targetDir . $newFileName;
    $fullPath = PATH_ROOT . $relativePath;
    
    error_log("Generating filename...");
    error_log("Extension: " . $extension);
    error_log("New filename: " . $newFileName);
    error_log("Relative path: " . $relativePath);
    error_log("Full path: " . $fullPath);

    error_log("New filename: " . $newFileName);
    error_log("Full path: " . $fullPath);

    // Di chuyển file
    if (move_uploaded_file($file['tmp_name'], $fullPath)) {
        // Set quyền file
        chmod($fullPath, 0644);
        error_log("✅ Upload success: " . $relativePath);
        return $relativePath;
    } else {
        error_log("❌ move_uploaded_file() failed");
        error_log("From: " . $file['tmp_name']);
        error_log("To: " . $fullPath);
        error_log("Tmp file exists: " . (file_exists($file['tmp_name']) ? 'YES' : 'NO'));
        return false;
    }
}

/**
 * ✅ Xóa file
 */
function deleteFile($filePath)
{
    if (empty($filePath)) {
        return true;
    }

    $fullPath = PATH_ROOT . $filePath;
    error_log("Attempting to delete: " . $fullPath);
    
    if (file_exists($fullPath)) {
        if (unlink($fullPath)) {
            error_log("✅ File deleted: " . $filePath);
            return true;
        } else {
            error_log("❌ Failed to delete: " . $filePath);
            return false;
        }
    }
    
    error_log("⚠️ File not found: " . $fullPath);
    return true; // Không có file thì coi như đã xóa
}

/**
 * ✅ Format ngày tháng
 */
function formatDate($date, $format = 'd/m/Y')
{
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * ✅ Format số tiền
 */
function formatMoney($amount)
{
    return number_format($amount, 0, ',', '.') . ' ₫';
}

/**
 * ✅ Sanitize input
 */
function sanitize($data)
{
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * ✅ Check admin
 */
function isAdmin()
{
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'ADMIN';
}

/**
 * ✅ Check login
 */
function isLoggedIn()
{
    return isset($_SESSION['user']);
}

/**
 * ✅ Redirect
 */
function redirect($url)
{
    header("Location: $url");
    exit;
}

/**
 * ✅ Debug helper (chỉ dùng khi dev)
 */
function dd($data)
{
    echo "<pre>";
    var_dump($data);
    echo "</pre>";
    die();
}
?>