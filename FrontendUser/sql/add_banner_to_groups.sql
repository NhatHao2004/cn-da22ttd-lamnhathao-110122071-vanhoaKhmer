-- Thêm cột anh_banner vào bảng nhom_hoc_tap
ALTER TABLE nhom_hoc_tap 
ADD COLUMN IF NOT EXISTS anh_banner VARCHAR(500) NULL COMMENT 'Đường dẫn ảnh banner của nhóm' 
AFTER icon;

-- Cập nhật một số ảnh banner mẫu (tùy chọn)
-- UPDATE nhom_hoc_tap SET anh_banner = 'uploads/banners/default-group-banner.jpg' WHERE anh_banner IS NULL;
