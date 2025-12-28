# 🖼️ Hướng dẫn thêm Banner cho Nhóm Học Tập

## Bước 1: Chạy Migration
Truy cập URL sau để thêm cột `anh_banner` vào database:
```
http://localhost/DoAn_ChuyenNganh/FrontendUser/run_migration_banner.php
```

## Bước 2: Tạo thư mục lưu ảnh
Tạo thư mục để lưu ảnh banner:
```
DoAn_ChuyenNganh/uploads/group_banners/
```

## Bước 3: Upload ảnh banner
- Upload các ảnh banner vào thư mục `uploads/group_banners/`
- Kích thước đề xuất: 800x400px hoặc tỷ lệ 2:1
- Format: JPG, PNG

## Bước 4: Cập nhật banner cho nhóm
Có 2 cách:

### Cách 1: Dùng giao diện web (Khuyến nghị)
Truy cập:
```
http://localhost/DoAn_ChuyenNganh/FrontendUser/update_group_banners.php
```
Nhập đường dẫn ảnh và click "Cập nhật Banner"

### Cách 2: Dùng SQL trực tiếp
```sql
UPDATE nhom_hoc_tap 
SET anh_banner = 'uploads/group_banners/khmer-basic.jpg' 
WHERE ma_nhom = 1;

UPDATE nhom_hoc_tap 
SET anh_banner = 'uploads/group_banners/khmer-culture.jpg' 
WHERE ma_nhom = 2;
```

## Bước 5: Kiểm tra kết quả
Truy cập trang nhóm học tập:
```
http://localhost/DoAn_ChuyenNganh/FrontendUser/learning_groups.php
```

## Debug Mode
Để xem dữ liệu chi tiết:
```
http://localhost/DoAn_ChuyenNganh/FrontendUser/learning_groups.php?debug=1
```

## Lưu ý
- Nếu không có ảnh banner, hệ thống sẽ hiển thị icon mặc định
- Ảnh banner sẽ được hiển thị với kích thước 180x100px
- Hỗ trợ cả đường dẫn tương đối và URL đầy đủ
