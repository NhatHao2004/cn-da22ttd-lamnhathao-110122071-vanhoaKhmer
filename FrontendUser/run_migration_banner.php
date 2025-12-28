<?php
/**
 * Migration Script - Add Banner to Learning Groups
 * Chạy file này một lần để thêm cột anh_banner vào bảng nhom_hoc_tap
 */

require_once __DIR__ . '/config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "<h2>🚀 Bắt đầu migration...</h2>";
    
    // Kiểm tra xem cột đã tồn tại chưa
    $checkColumn = $pdo->query("SHOW COLUMNS FROM nhom_hoc_tap LIKE 'anh_banner'");
    
    if ($checkColumn->rowCount() > 0) {
        echo "<p style='color: orange;'>⚠️ Cột 'anh_banner' đã tồn tại trong bảng nhom_hoc_tap</p>";
    } else {
        // Thêm cột anh_banner
        $sql = "ALTER TABLE nhom_hoc_tap 
                ADD COLUMN anh_banner VARCHAR(500) NULL COMMENT 'Đường dẫn ảnh banner của nhóm' 
                AFTER icon";
        
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Đã thêm cột 'anh_banner' vào bảng nhom_hoc_tap</p>";
    }
    
    // Lấy danh sách nhóm hiện tại
    $groups = $pdo->query("SELECT ma_nhom, ten_nhom, icon FROM nhom_hoc_tap")->fetchAll();
    
    echo "<h3>📋 Danh sách nhóm hiện tại:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Tên nhóm</th><th>Icon</th><th>Banner</th></tr>";
    
    foreach ($groups as $group) {
        $banner = $group['anh_banner'] ?? 'Chưa có';
        echo "<tr>";
        echo "<td>{$group['ma_nhom']}</td>";
        echo "<td>{$group['ten_nhom']}</td>";
        echo "<td><i class='{$group['icon']}'></i> {$group['icon']}</td>";
        echo "<td>{$banner}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<h3>💡 Hướng dẫn tiếp theo:</h3>";
    echo "<ol>";
    echo "<li>Tạo thư mục: <code>uploads/group_banners/</code></li>";
    echo "<li>Upload ảnh banner cho các nhóm</li>";
    echo "<li>Cập nhật database bằng SQL:</li>";
    echo "</ol>";
    echo "<pre style='background: #f5f5f5; padding: 15px; border: 1px solid #ddd;'>";
    echo "UPDATE nhom_hoc_tap SET anh_banner = 'uploads/group_banners/khmer-basic.jpg' WHERE ma_nhom = 1;\n";
    echo "UPDATE nhom_hoc_tap SET anh_banner = 'uploads/group_banners/khmer-culture.jpg' WHERE ma_nhom = 2;";
    echo "</pre>";
    
    echo "<p style='color: green; font-weight: bold;'>✅ Migration hoàn tất!</p>";
    echo "<p><a href='learning_groups.php?debug=1'>👉 Xem trang nhóm học tập (debug mode)</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1200px;
    margin: 50px auto;
    padding: 20px;
    background: #f9f9f9;
}
h2, h3 {
    color: #333;
}
code {
    background: #ffe4b5;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: monospace;
}
</style>
