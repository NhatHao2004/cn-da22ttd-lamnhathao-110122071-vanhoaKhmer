<?php
/**
 * Test upload processing
 */
echo "<h2>Test Upload Result:</h2>";

if (!empty($_FILES['test_image'])) {
    $file = $_FILES['test_image'];
    echo "<h3>Ảnh:</h3>";
    echo "<pre>";
    print_r($file);
    echo "</pre>";
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/posts/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
            echo "<p>✅ Đã tạo thư mục: $upload_dir</p>";
        }
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_name = uniqid() . '_' . time() . '.' . $ext;
        $destination = $upload_dir . $new_name;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            echo "<p style='color: green;'>✅ Upload ảnh thành công: $new_name</p>";
            echo "<img src='uploads/posts/$new_name' style='max-width: 300px; border: 2px solid #00b894;'>";
        } else {
            echo "<p style='color: red;'>❌ Không thể upload ảnh!</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Lỗi upload: " . $file['error'] . "</p>";
    }
}

if (!empty($_FILES['test_file'])) {
    $file = $_FILES['test_file'];
    echo "<h3>Tài liệu:</h3>";
    echo "<pre>";
    print_r($file);
    echo "</pre>";
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/documents/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
            echo "<p>✅ Đã tạo thư mục: $upload_dir</p>";
        }
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_name = uniqid() . '_' . time() . '.' . $ext;
        $destination = $upload_dir . $new_name;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            echo "<p style='color: green;'>✅ Upload tài liệu thành công: $new_name</p>";
            echo "<p><a href='uploads/documents/$new_name' target='_blank'>📄 Xem file</a></p>";
        } else {
            echo "<p style='color: red;'>❌ Không thể upload tài liệu!</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Lỗi upload: " . $file['error'] . "</p>";
    }
}

echo "<p><a href='test_upload.php'>← Quay lại</a></p>";
?>
