-- Thêm cột lưu ảnh và tài liệu đính kèm vào bảng bai_viet_nhom
ALTER TABLE bai_viet_nhom 
ADD COLUMN IF NOT EXISTS anh_dinh_kem TEXT NULL COMMENT 'JSON array chứa tên file ảnh',
ADD COLUMN IF NOT EXISTS tai_lieu_dinh_kem TEXT NULL COMMENT 'JSON array chứa thông tin tài liệu';
