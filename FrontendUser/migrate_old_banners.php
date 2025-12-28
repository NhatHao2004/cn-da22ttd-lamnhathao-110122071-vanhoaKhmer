<?php
/**
 * Migration: Copy dữ liệu từ cột hinh_anh sang anh_banner
 */
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "<h1>🔄 Migration: hinh_anh → anh_banner</h1>";
    echo "<style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        pre { background: white; padding: 10px; border: 1px solid #ddd; }
    </style>";
    
    // Kiểm tra cột hinh_anh có tồn tại không
    $columns = $pdo->query("SHOW COLUMNS FROM nhom_hoc_tap")->fetchAll(PDO::FETCH_ASSOC);
    $hasHinhAnh = false;
    $hasAnhBanner = false;
    
    foreach ($columns as $col) {
        if ($col['Field'] === 'hinh_anh') $hasHinhAnh = true;
        if ($col['Field'] === 'anh_banner') $hasAnhBanner = true;
    }
    
    echo "<h2>📋 Kiểm tra cột:</h2>";
    echo "<p>Cột 'hinh_anh': " . ($hasHinhAnh ? "<span class='success'>✅ Có</span>" : "<span class='error'>❌ Không</span>") . "</p>";
    echo "<p>Cột 'anh_banner': " . ($hasAnhBanner ? "<span class='success'>✅ Có</span>" : "<span class='error'>❌ Không</span>") . "</p>";
    
    if (!$hasAnhBanner) {
        echo "<p class='error'>❌ Cột 'anh_banner' chưa tồn tại. Vui lòng chạy migration trước!</p>";
        echo "<p><a href='run_migration_banner.php'>▶️ Chạy Migration</a></p>";
        exit;
    }
    
    if ($hasHinhAnh) {
        echo "<h2>🔄 Bắt đầu migration dữ liệu...</h2>";
        
        // Lấy các nhóm có hinh_anh nhưng chưa có anh_banner
        $groups = $pdo->query("
            SELECT ma_nhom, ten_nhom, hinh_anh, anh_banner 
            FROM nhom_hoc_tap 
            WHERE hinh_anh IS NOT NULL AND hinh_anh != ''
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($groups)) {
            echo "<p class='info'>ℹ️ Không có dữ liệu cần migrate</p>";
        } else {
            echo "<p class='info'>Tìm thấy " . count($groups) . " nhóm có ảnh cần migrate</p>";
            
            $updated = 0;
            foreach ($groups as $group) {
                $old_path = $group['hinh_anh'];
                
                // Chuyển đổi đường dẫn
                if (strpos($old_path, 'uploads/') === 0) {
                    // Đã có đường dẫn đầy đủ
                    $new_path = $old_path;
                } else {
                    // Chỉ có tên file, thêm đường dẫn
                    $new_path = 'uploads/groups/' . $old_path;
                }
                
                // Cập nhật nếu anh_banner đang trống
                if (empty($group['anh_banner'])) {
                    $stmt = $pdo->prepare("UPDATE nhom_hoc_tap SET anh_banner = ? WHERE ma_nhom = ?");
                    $stmt->execute([$new_path, $group['ma_nhom']]);
                    
                    echo "<p class='success'>✅ Nhóm #{$group['ma_nhom']} ({$group['ten_nhom']}): {$old_path} → {$new_path}</p>";
                    $updated++;
                } else {
                    echo "<p class='info'>⏭️ Nhóm #{$group['ma_nhom']} ({$group['ten_nhom']}): Đã có anh_banner</p>";
                }
            }
            
            echo "<h3 class='success'>✅ Hoàn tất! Đã cập nhật {$updated} nhóm</h3>";
        }
        
        // Tạo thư mục mới nếu cần
        $old_dir = __DIR__ . '/uploads/groups/';
        $new_dir = __DIR__ . '/uploads/group_banners/';
        
        if (is_dir($old_dir) && !is_dir($new_dir)) {
            mkdir($new_dir, 0755, true);
            echo "<p class='success'>✅ Đã tạo thư mục: uploads/group_banners/</p>";
            echo "<p class='info'>💡 Bạn có thể copy ảnh từ uploads/groups/ sang uploads/group_banners/</p>";
        }
        
    } else {
        echo "<p class='info'>ℹ️ Không có cột 'hinh_anh' cũ, không cần migrate</p>";
    }
    
    echo "<hr>";
    echo "<h2>📊 Kết quả hiện tại:</h2>";
    
    $result = $pdo->query("
        SELECT ma_nhom, ten_nhom, anh_banner 
        FROM nhom_hoc_tap 
        ORDER BY ma_nhom
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; background: white;'>";
    echo "<tr><th>ID</th><th>Tên nhóm</th><th>Banner</th></tr>";
    
    foreach ($result as $row) {
        $banner = $row['anh_banner'] ?: '<span style="color: #999;">Chưa có</span>';
        echo "<tr>";
        echo "<td>{$row['ma_nhom']}</td>";
        echo "<td>{$row['ten_nhom']}</td>";
        echo "<td>{$banner}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<hr>";
    echo "<p><a href='learning_groups.php'>👉 Xem trang nhóm học tập</a></p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Lỗi: " . $e->getMessage() . "</p>";
}
?>
