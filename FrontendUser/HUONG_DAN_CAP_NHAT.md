# Hướng dẫn cập nhật database

## Bước 1: Chạy SQL để thêm cột mới

Chạy file SQL sau trong phpMyAdmin hoặc MySQL client:

```sql
-- File: sql/add_post_attachments.sql
ALTER TABLE bai_viet_nhom 
ADD COLUMN IF NOT EXISTS anh_dinh_kem TEXT NULL COMMENT 'JSON array chứa tên file ảnh',
ADD COLUMN IF NOT EXISTS tai_lieu_dinh_kem TEXT NULL COMMENT 'JSON array chứa thông tin tài liệu';
```

## Bước 2: Tạo thư mục uploads

Đảm bảo các thư mục sau tồn tại và có quyền ghi (chmod 777):

```
FrontendUser/uploads/posts/
FrontendUser/uploads/documents/
```

## Bước 3: Test chức năng

1. Đăng nhập vào hệ thống
2. Vào một nhóm học tập
3. Thử tạo bài viết mới với:
   - Nội dung HTML (sử dụng thẻ `<p>`, `<strong>`, `<em>`, `<ul>`, `<li>`, etc.)
   - Upload ảnh (jpg, png, gif, webp)
   - Upload tài liệu (pdf, doc, docx, xls, xlsx, ppt, pptx, txt)

## Các tính năng đã được thêm:

✅ Hỗ trợ HTML trong nội dung bài viết
✅ Upload và hiển thị nhiều ảnh
✅ Upload và hiển thị tài liệu đính kèm
✅ Xem ảnh full size khi click
✅ Download tài liệu
✅ Icon file theo loại (PDF, Word, Excel, PowerPoint)
✅ Responsive design cho mobile

## Lưu ý:

- Ảnh tối đa 5MB mỗi file
- Tài liệu tối đa 10MB mỗi file
- Nội dung HTML sẽ được render trực tiếp (cẩn thận với XSS)
- Khi sửa bài viết, có thể thêm ảnh/tài liệu mới
