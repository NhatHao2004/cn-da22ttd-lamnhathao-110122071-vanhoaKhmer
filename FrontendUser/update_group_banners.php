<?php
/**
 * Update Group Banners - Quick Tool
 * Tool đơn giản để cập nhật banner cho các nhóm
 */

require_once __DIR__ . '/config/database.php';

$pdo = getDBConnection();

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_banner'])) {
    $ma_nhom = $_POST['ma_nhom'];
    $anh_banner = $_POST['anh_banner'];
    
    try {
        $stmt = $pdo->prepare("UPDATE nhom_hoc_tap SET anh_banner = ? WHERE ma_nhom = ?");
        $stmt->execute([$anh_banner, $ma_nhom]);
        $message = "✅ Đã cập nhật banner cho nhóm ID: $ma_nhom";
    } catch (Exception $e) {
        $message = "❌ Lỗi: " . $e->getMessage();
    }
}

// Lấy danh sách nhóm
$groups = $pdo->query("SELECT * FROM nhom_hoc_tap ORDER BY ma_nhom")->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật Banner Nhóm</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: linear-gradient(135deg, #FFF6E5 0%, #FFE4B5 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 30px;
            border: 3px solid #1a1a1a;
            box-shadow: 6px 6px 0px #1a1a1a;
        }
        h1 {
            color: #1a1a1a;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 1rem;
        }
        .message {
            padding: 15px 20px;
            background: #d4edda;
            border: 3px solid #1a1a1a;
            border-radius: 12px;
            margin-bottom: 20px;
            color: #155724;
            font-weight: 600;
        }
        .group-card {
            background: #f9f9f9;
            border: 3px solid #1a1a1a;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .group-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        .group-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #FF9800, #F57C00);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            border: 3px solid #1a1a1a;
        }
        .group-info h3 {
            color: #1a1a1a;
            font-size: 1.25rem;
            margin-bottom: 5px;
        }
        .group-info p {
            color: #666;
            font-size: 0.875rem;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #1a1a1a;
        }
        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 3px solid #1a1a1a;
            border-radius: 10px;
            font-size: 0.9375rem;
            font-family: monospace;
        }
        input[type="text"]:focus {
            outline: none;
            border-color: #FF9800;
            box-shadow: 0 0 0 4px rgba(255, 152, 0, 0.2);
        }
        .btn {
            padding: 12px 24px;
            background: #FF9800;
            color: white;
            border: 3px solid #1a1a1a;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            font-size: 0.9375rem;
            transition: all 0.3s ease;
            box-shadow: 3px 3px 0px #1a1a1a;
        }
        .btn:hover {
            background: #F57C00;
            transform: translate(-2px, -2px);
            box-shadow: 5px 5px 0px #1a1a1a;
        }
        .preview {
            margin-top: 10px;
            padding: 10px;
            background: white;
            border: 2px dashed #ddd;
            border-radius: 8px;
        }
        .preview img {
            max-width: 200px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #1a1a1a;
        }
        .hint {
            font-size: 0.875rem;
            color: #666;
            margin-top: 5px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: white;
            color: #1a1a1a;
            text-decoration: none;
            border: 3px solid #1a1a1a;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .back-link:hover {
            background: #1a1a1a;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🖼️ Cập nhật Banner Nhóm Học Tập</h1>
        <p class="subtitle">Thêm hoặc cập nhật ảnh banner cho các nhóm học tập</p>
        
        <?php if (isset($message)): ?>
        <div class="message"><?= $message ?></div>
        <?php endif; ?>
        
        <?php foreach ($groups as $group): ?>
        <div class="group-card">
            <div class="group-header">
                <div class="group-icon">
                    <i class="<?= htmlspecialchars($group['icon']) ?>"></i>
                </div>
                <div class="group-info">
                    <h3><?= htmlspecialchars($group['ten_nhom']) ?></h3>
                    <p>ID: <?= $group['ma_nhom'] ?> | Icon: <?= htmlspecialchars($group['icon']) ?></p>
                </div>
            </div>
            
            <form method="POST" onsubmit="return confirm('Cập nhật banner cho nhóm này?')">
                <input type="hidden" name="ma_nhom" value="<?= $group['ma_nhom'] ?>">
                
                <div class="form-group">
                    <label>Đường dẫn ảnh banner:</label>
                    <input type="text" 
                           name="anh_banner" 
                           value="<?= htmlspecialchars($group['anh_banner'] ?? '') ?>"
                           placeholder="uploads/group_banners/example.jpg"
                           oninput="updatePreview(this, <?= $group['ma_nhom'] ?>)">
                    <p class="hint">💡 Ví dụ: uploads/group_banners/khmer-basic.jpg hoặc URL đầy đủ</p>
                    
                    <?php if (!empty($group['anh_banner'])): ?>
                    <div class="preview" id="preview-<?= $group['ma_nhom'] ?>">
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($group['anh_banner']) ?>" 
                             alt="Preview"
                             onerror="this.parentElement.innerHTML='<p style=\'color:red;\'>❌ Không tải được ảnh</p>'">
                    </div>
                    <?php else: ?>
                    <div class="preview" id="preview-<?= $group['ma_nhom'] ?>" style="display: none;"></div>
                    <?php endif; ?>
                </div>
                
                <button type="submit" name="update_banner" class="btn">
                    💾 Cập nhật Banner
                </button>
            </form>
        </div>
        <?php endforeach; ?>
        
        <a href="learning_groups.php" class="back-link">← Quay lại trang nhóm học tập</a>
    </div>
    
    <script>
    function updatePreview(input, groupId) {
        const preview = document.getElementById('preview-' + groupId);
        const value = input.value.trim();
        
        if (value) {
            const baseUrl = '<?= BASE_URL ?>';
            const imgSrc = value.startsWith('http') ? value : baseUrl + '/' + value;
            
            preview.style.display = 'block';
            preview.innerHTML = `<img src="${imgSrc}" alt="Preview" onerror="this.parentElement.innerHTML='<p style=\\'color:red;\\'>❌ Không tải được ảnh</p>'">`;
        } else {
            preview.style.display = 'none';
        }
    }
    </script>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>
