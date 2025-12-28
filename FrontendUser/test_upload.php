<?php
/**
 * Test script để kiểm tra upload và database
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Kiểm tra database structure
try {
    $pdo = getDBConnection();
    
    echo "<h2>1. Kiểm tra cấu trúc bảng bai_viet_nhom:</h2>";
    $stmt = $pdo->query("DESCRIBE bai_viet_nhom");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasAnhDinhKem = false;
    $hasTaiLieuDinhKem = false;
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
        
        if ($col['Field'] === 'anh_dinh_kem') $hasAnhDinhKem = true;
        if ($col['Field'] === 'tai_lieu_dinh_kem') $hasTaiLieuDinhKem = true;
    }
    echo "</table>";
    
    echo "<h3>Kết quả:</h3>";
    echo "<p>✅ Cột 'anh_dinh_kem': " . ($hasAnhDinhKem ? "CÓ" : "KHÔNG CÓ - CẦN CHẠY SQL!") . "</p>";
    echo "<p>✅ Cột 'tai_lieu_dinh_kem': " . ($hasTaiLieuDinhKem ? "CÓ" : "KHÔNG CÓ - CẦN CHẠY SQL!") . "</p>";
    
    if (!$hasAnhDinhKem || !$hasTaiLieuDinhKem) {
        echo "<h3 style='color: red;'>⚠️ CẦN CHẠY SQL SAU:</h3>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
        echo "ALTER TABLE bai_viet_nhom \n";
        echo "ADD COLUMN anh_dinh_kem TEXT NULL COMMENT 'JSON array chứa tên file ảnh',\n";
        echo "ADD COLUMN tai_lieu_dinh_kem TEXT NULL COMMENT 'JSON array chứa thông tin tài liệu';";
        echo "</pre>";
    }
    
    // Kiểm tra bài viết có dữ liệu
    echo "<h2>2. Kiểm tra bài viết gần đây:</h2>";
    $stmt = $pdo->query("SELECT ma_bai_viet, tieu_de, anh_dinh_kem, tai_lieu_dinh_kem, ngay_dang FROM bai_viet_nhom ORDER BY ngay_dang DESC LIMIT 5");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($posts)) {
        echo "<p>Chưa có bài viết nào.</p>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Tiêu đề</th><th>Ảnh</th><th>Tài liệu</th><th>Ngày đăng</th></tr>";
        foreach ($posts as $post) {
            echo "<tr>";
            echo "<td>{$post['ma_bai_viet']}</td>";
            echo "<td>" . substr($post['tieu_de'], 0, 50) . "</td>";
            echo "<td>" . ($post['anh_dinh_kem'] ? "✅ Có (" . count(json_decode($post['anh_dinh_kem'], true)) . " ảnh)" : "❌ Không") . "</td>";
            echo "<td>" . ($post['tai_lieu_dinh_kem'] ? "✅ Có (" . count(json_decode($post['tai_lieu_dinh_kem'], true)) . " file)" : "❌ Không") . "</td>";
            echo "<td>{$post['ngay_dang']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Kiểm tra thư mục uploads
    echo "<h2>3. Kiểm tra thư mục uploads:</h2>";
    $uploadDirs = [
        'uploads/posts' => __DIR__ . '/uploads/posts',
        'uploads/documents' => __DIR__ . '/uploads/documents'
    ];
    
    foreach ($uploadDirs as $name => $path) {
        if (file_exists($path)) {
            $writable = is_writable($path);
            $files = scandir($path);
            $fileCount = count($files) - 2; // Trừ . và ..
            
            echo "<p>📁 <strong>$name</strong>: ";
            echo "Tồn tại ✅ | ";
            echo "Ghi được: " . ($writable ? "✅" : "❌ KHÔNG") . " | ";
            echo "Số file: $fileCount";
            echo "</p>";
            
            if (!$writable) {
                echo "<p style='color: red;'>⚠️ Cần chmod 777 cho thư mục này!</p>";
            }
        } else {
            echo "<p style='color: red;'>📁 <strong>$name</strong>: KHÔNG TỒN TẠI ❌</p>";
            echo "<p>Tạo thư mục: <code>mkdir -p $path && chmod 777 $path</code></p>";
        }
    }
    
    echo "<h2>4. Test upload form:</h2>";
    echo "<form method='POST' enctype='multipart/form-data' action='test_upload_process.php'>";
    echo "<p><label>Chọn ảnh: <input type='file' name='test_image' accept='image/*'></label></p>";
    echo "<p><label>Chọn tài liệu: <input type='file' name='test_file' accept='.pdf,.doc,.docx'></label></p>";
    echo "<p><button type='submit'>Test Upload</button></p>";
    echo "</form>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Lỗi: " . $e->getMessage() . "</h3>";
}
?>
