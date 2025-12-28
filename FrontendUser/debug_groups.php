<?php
/**
 * Debug Groups - Kiểm tra dữ liệu nhóm
 */
require_once __DIR__ . '/config/database.php';

$pdo = getDBConnection();

echo "<h1>🔍 Debug Nhóm Học Tập</h1>";
echo "<style>
    body { font-family: Arial; padding: 20px; background: #f5f5f5; }
    h1 { color: #333; }
    .section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border: 2px solid #ddd; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
    th { background: #FF9800; color: white; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    pre { background: #f9f9f9; padding: 10px; border: 1px solid #ddd; overflow-x: auto; }
</style>";

// 1. Kiểm tra bảng tồn tại
echo "<div class='section'>";
echo "<h2>1️⃣ Kiểm tra bảng nhom_hoc_tap</h2>";
try {
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'nhom_hoc_tap'")->rowCount();
    if ($tableCheck > 0) {
        echo "<p class='success'>✅ Bảng nhom_hoc_tap tồn tại</p>";
    } else {
        echo "<p class='error'>❌ Bảng nhom_hoc_tap KHÔNG tồn tại</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Lỗi: " . $e->getMessage() . "</p>";
    exit;
}
echo "</div>";

// 2. Kiểm tra cấu trúc bảng
echo "<div class='section'>";
echo "<h2>2️⃣ Cấu trúc bảng nhom_hoc_tap</h2>";
try {
    $columns = $pdo->query("SHOW COLUMNS FROM nhom_hoc_tap")->fetchAll();
    echo "<table>";
    echo "<tr><th>Tên cột</th><th>Kiểu dữ liệu</th><th>Null</th><th>Mặc định</th></tr>";
    
    $hasAnhBanner = false;
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
        
        if ($col['Field'] === 'anh_banner') {
            $hasAnhBanner = true;
        }
    }
    echo "</table>";
    
    if ($hasAnhBanner) {
        echo "<p class='success'>✅ Cột 'anh_banner' đã tồn tại</p>";
    } else {
        echo "<p class='error'>❌ Cột 'anh_banner' CHƯA tồn tại - Cần chạy migration!</p>";
        echo "<p><a href='run_migration_banner.php' style='padding: 10px 20px; background: #FF9800; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px;'>▶️ Chạy Migration Ngay</a></p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Lỗi: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 3. Lấy dữ liệu nhóm
echo "<div class='section'>";
echo "<h2>3️⃣ Dữ liệu nhóm học tập</h2>";
try {
    $groups = $pdo->query("SELECT * FROM nhom_hoc_tap ORDER BY ma_nhom")->fetchAll();
    
    if (empty($groups)) {
        echo "<p class='error'>❌ Không có nhóm nào trong database</p>";
    } else {
        echo "<p class='success'>✅ Tìm thấy " . count($groups) . " nhóm</p>";
        
        echo "<table>";
        echo "<tr><th>ID</th><th>Tên nhóm</th><th>Icon</th><th>Banner</th><th>Trạng thái</th></tr>";
        
        foreach ($groups as $group) {
            $banner = $group['anh_banner'] ?? '';
            $bannerStatus = empty($banner) ? '❌ Chưa có' : '✅ Có';
            
            echo "<tr>";
            echo "<td>{$group['ma_nhom']}</td>";
            echo "<td>{$group['ten_nhom']}</td>";
            echo "<td><i class='{$group['icon']}'></i> {$group['icon']}</td>";
            echo "<td>{$bannerStatus}<br><small style='color: #666;'>{$banner}</small></td>";
            echo "<td>{$group['trang_thai']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Hiển thị dữ liệu chi tiết nhóm đầu tiên
        echo "<h3>📋 Chi tiết nhóm đầu tiên (raw data):</h3>";
        echo "<pre>";
        print_r($groups[0]);
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Lỗi: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 4. Hướng dẫn tiếp theo
echo "<div class='section'>";
echo "<h2>4️⃣ Hướng dẫn tiếp theo</h2>";

if (!$hasAnhBanner) {
    echo "<ol>";
    echo "<li><strong>Chạy migration:</strong> <a href='run_migration_banner.php'>run_migration_banner.php</a></li>";
    echo "<li>Sau đó quay lại trang này để kiểm tra</li>";
    echo "</ol>";
} else {
    $hasEmptyBanner = false;
    foreach ($groups as $group) {
        if (empty($group['anh_banner'])) {
            $hasEmptyBanner = true;
            break;
        }
    }
    
    if ($hasEmptyBanner) {
        echo "<p>✅ Cột banner đã có, nhưng các nhóm chưa có ảnh banner.</p>";
        echo "<ol>";
        echo "<li>Tạo thư mục: <code>uploads/group_banners/</code></li>";
        echo "<li>Upload ảnh banner vào thư mục đó</li>";
        echo "<li>Cập nhật banner: <a href='update_group_banners.php'>update_group_banners.php</a></li>";
        echo "</ol>";
    } else {
        echo "<p class='success'>✅ Tất cả đã sẵn sàng! Kiểm tra trang nhóm:</p>";
        echo "<p><a href='learning_groups.php' style='padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>👉 Xem trang nhóm học tập</a></p>";
    }
}
echo "</div>";

echo "<div class='section'>";
echo "<h2>🔗 Quick Links</h2>";
echo "<ul>";
echo "<li><a href='run_migration_banner.php'>▶️ Chạy Migration</a></li>";
echo "<li><a href='update_group_banners.php'>🖼️ Cập nhật Banner</a></li>";
echo "<li><a href='learning_groups.php'>👥 Trang Nhóm Học Tập</a></li>";
echo "<li><a href='debug_groups.php'>🔄 Refresh trang này</a></li>";
echo "</ul>";
echo "</div>";
?>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
