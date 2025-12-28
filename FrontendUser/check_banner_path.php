<?php
require_once __DIR__ . '/config/database.php';

$pdo = getDBConnection();
$groups = $pdo->query("SELECT ma_nhom, ten_nhom, anh_banner FROM nhom_hoc_tap")->fetchAll();

echo "<h1>🔍 Kiểm tra đường dẫn Banner</h1>";
echo "<style>
    body { font-family: Arial; padding: 20px; background: #f5f5f5; }
    .group { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border: 2px solid #ddd; }
    .banner-preview { margin-top: 10px; padding: 10px; background: #f9f9f9; border: 2px solid #ddd; }
    img { max-width: 300px; border: 3px solid #1a1a1a; border-radius: 8px; }
    code { background: #ffe4b5; padding: 2px 6px; border-radius: 3px; }
    .error { color: red; font-weight: bold; }
    .success { color: green; font-weight: bold; }
</style>";

foreach ($groups as $group) {
    echo "<div class='group'>";
    echo "<h2>{$group['ten_nhom']} (ID: {$group['ma_nhom']})</h2>";
    
    $banner = $group['anh_banner'];
    echo "<p><strong>Đường dẫn trong DB:</strong> <code>" . htmlspecialchars($banner) . "</code></p>";
    
    if (empty($banner)) {
        echo "<p class='error'>❌ Không có banner</p>";
    } else {
        // Kiểm tra các đường dẫn khác nhau
        $paths = [
            "Đường dẫn gốc" => $banner,
            "BASE_URL + banner" => BASE_URL . '/' . ltrim($banner, '/'),
            "Đường dẫn tuyệt đối" => $_SERVER['DOCUMENT_ROOT'] . '/DoAn_ChuyenNganh/FrontendUser/' . ltrim($banner, '/')
        ];
        
        echo "<h3>Thử các đường dẫn:</h3>";
        foreach ($paths as $label => $path) {
            echo "<p><strong>{$label}:</strong> <code>{$path}</code></p>";
            
            if ($label === "Đường dẫn tuyệt đối") {
                if (file_exists($path)) {
                    echo "<p class='success'>✅ File tồn tại trên server</p>";
                } else {
                    echo "<p class='error'>❌ File KHÔNG tồn tại trên server</p>";
                }
            } else {
                echo "<div class='banner-preview'>";
                echo "<p>Preview:</p>";
                echo "<img src='{$path}' alt='Banner' onerror=\"this.parentElement.innerHTML='<p class=\\'error\\'>❌ Không tải được ảnh từ: {$path}</p>'\">";
                echo "</div>";
            }
        }
    }
    
    echo "</div>";
}

echo "<hr>";
echo "<h2>📋 Thông tin hệ thống:</h2>";
echo "<p><strong>BASE_URL:</strong> <code>" . BASE_URL . "</code></p>";
echo "<p><strong>DOCUMENT_ROOT:</strong> <code>" . $_SERVER['DOCUMENT_ROOT'] . "</code></p>";
echo "<p><strong>Current Dir:</strong> <code>" . __DIR__ . "</code></p>";
?>
