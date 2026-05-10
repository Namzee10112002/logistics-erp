# Quy tắc Phát triển Dự án (Development Guidelines)

Tài liệu này quy định các tiêu chuẩn về code, quản lý bộ nhớ, quy trình xử lý task và các kỹ năng cần thiết để duy trì dự án ổn định và chuyên nghiệp.

---

## 1. Quy trình xử lý Task (Workflow)
Mọi yêu cầu tính năng mới hoặc chỉnh sửa lớn đều tuân thủ 5 bước:

1.  **Phân tích (Research):** Tìm hiểu cấu trúc hiện tại, các DB liên quan và các ràng buộc nghiệp vụ Logistics.
2.  **Lập kế hoạch (Plan):** Cập nhật `implementation_plan.md` và chờ phê duyệt.
3.  **Thực thi (Execute):**
    - Tạo `task.md` để liệt kê TODO list.
    - Viết Code (Migrations -> Models -> Services -> Controllers -> Resources).
    - Viết Test (Unit/Feature Test).
4.  **Xác minh (Verify):** Chạy test và kiểm tra thủ công giao diện/API.
5.  **Tài liệu hóa (Document):** Cập nhật `DATABASE_SCHEMA.md` (nếu có đổi DB) và tạo `walkthrough.md`.

---

## 2. Tiêu chuẩn Code (Coding Standards)

### 🧩 Kiến trúc (Architecture)
- **Service Pattern:** Không viết logic nghiệp vụ (business logic) trong Controller. Tạo các lớp Service để xử lý logic.
- **Form Requests:** Luôn sử dụng FormRequest để validate dữ liệu đầu vào.
- **Eloquent Resources:** Sử dụng API Resources để định dạng dữ liệu trả về cho Front-end, đảm bảo tính nhất quán.
- **Named Routes:** Luôn sử dụng tên route (`route('jobs.show', $id)`) thay vì hardcode URL.

### 💎 PHP & Laravel
- **Type Hinting:** Luôn khai báo kiểu dữ liệu cho tham số và giá trị trả về của hàm.
- **Constructor Property Promotion:** Sử dụng tính năng của PHP 8 cho các Dependency Injection.
- **Casts:** Khai báo kiểu dữ liệu cho Model thông qua phương thức `casts()`.
- **Soft Deletes:** Luôn sử dụng `deleted_at` cho các bảng nghiệp vụ quan trọng để tránh mất dữ liệu.

---

## 3. Tối ưu Hiệu suất & Bộ nhớ (Memory & Performance)

### 🚀 Database & Query
- **N+1 Query:** Luôn sử dụng `with()` (Eager Loading) khi cần lấy dữ liệu quan hệ.
- **Indexing:** Đảm bảo các trường hay dùng để tìm kiếm (Mã lô hàng, MST, Số điện thoại) được đánh Index trong Migration.
- **Chunking:** Sử dụng `chunk()` hoặc `cursor()` khi xử lý tập dữ liệu lớn để tránh tràn bộ nhớ.

### ⚡ System
- **Job Queues:** Đẩy các tác vụ nặng (Xuất file PDF, gửi email thông báo, xử lý ảnh chứng từ) vào hàng đợi.
- **Cache:** Sử dụng Redis/File cache cho các danh mục ít thay đổi (Địa điểm, Biểu giá).

---

## 4. Kỹ năng & Công cụ (Skills & Tools)

- **Database Schema:** Duy trì file `DATABASE_SCHEMA.md` như một nguồn sự thật (Single Source of Truth).
- **Tinker:** Sử dụng `php artisan tinker` để kiểm tra logic nhanh mà không cần tạo UI.
- **Pint:** Luôn chạy `vendor/bin/pint` trước khi commit code để đảm bảo chuẩn PSR-12.
- **Testing:** Ưu tiên Feature Test để mô phỏng luồng nghiệp vụ của người dùng thật.

---

## 5. Quy tắc Tài liệu (Documentation)

- **Walkthrough:** Mỗi task hoàn thành phải có walkthrough kèm screenshot hoặc mô tả luồng dữ liệu.
- **Comments:** Chỉ viết comment cho các đoạn logic cực kỳ phức tạp (Logistics formulas, công thức tính cước phức tạp). Ưu tiên code tự giải thích (Self-documenting code).
