# Tài liệu Kỹ thuật: Sơ đồ Cơ sở dữ liệu Logistics Nguyên Tâm

Tài liệu này mô tả chi tiết cấu trúc cơ sở dữ liệu được triển khai cho Hệ thống Quản trị Logistics Nguyên Tâm theo nguyên tắc **Single Source of Truth** (mọi nghiệp vụ xoay quanh mã Lô hàng/Job).

---

## 1. Nhóm Quản trị Hệ thống (System & Auth)

### Bảng: `roles` (Vai trò)
Lưu trữ danh sách các quyền hạn trong hệ thống.
- **id**: (Primary Key)
- **role_code**: (VARCHAR 50) Mã quyền (ADMIN, SALES, DOCUMENT, v.v.) - Unique.
- **role_name**: (VARCHAR 100) Tên hiển thị của vai trò.
- **description**: (TEXT) Mô tả chi tiết chức năng.
- **created_at / updated_at**: Thời điểm tạo và cập nhật.
- **deleted_at**: Thời điểm xóa mềm.

### Bảng: `users` (Tài khoản người dùng)
Quản lý thông tin đăng nhập và phân quyền nhân viên.
- **id**: (Primary Key)
- **username**: (VARCHAR 50) Tên đăng nhập - Unique.
- **full_name**: (VARCHAR 100) Họ tên đầy đủ.
- **email**: (VARCHAR 255) Email liên lạc - Unique.
- **password**: (VARCHAR 255) Mật khẩu mã hóa.
- **role_id**: (Foreign Key -> `roles.id`) Vai trò của người dùng.
- **status**: (NATIVE ENUM) Trạng thái tài khoản (1: Hoạt động, 0: Khóa).
- **created_at / updated_at**: Thời điểm tạo và cập nhật.
- **deleted_at**: Thời điểm xóa mềm.

---

## 2. Nhóm Dữ liệu Danh mục (Master Data)

### Bảng: `customers` (Khách hàng)
- **customer_code**: Mã khách hàng (Unique).
- **company_name**: Tên công ty/đối tác.
- **tax_code**: Mã số thuế.
- **address**: Địa chỉ trụ sở.
- **phone/email**: Thông tin liên lạc.
- **created_at / updated_at**: Thời điểm tạo và cập nhật.
- **deleted_at**: Thời điểm xóa mềm.

### Bảng: `vehicles` (Phương tiện)
- **license_plate**: Biển số xe (Unique).
- **vehicle_type**: (NATIVE ENUM) Loại xe ('Đầu kéo', 'Mooc', 'Xe tải').
- **payload**: Tải trọng (Tấn).
- **status**: (NATIVE ENUM) Trạng thái ('Rảnh', 'Đang chạy', 'Bảo trì').
- **created_at / updated_at**: Thời điểm tạo và cập nhật.
- **deleted_at**: Thời điểm xóa mềm.

### Bảng: `drivers` (Tài xế)
- **full_name**: Tên tài xế.
- **phone**: Số điện thoại.
- **license_class**: Hạng bằng lái (FC, C, v.v.).
- **status**: (NATIVE ENUM) Trạng thái ('Rảnh', 'Đang chạy', 'Nghỉ').
- **created_at / updated_at**: Thời điểm tạo và cập nhật.
- **deleted_at**: Thời điểm xóa mềm.

### Bảng: `locations` (Địa điểm)
- **location_name**: Tên Cảng / Kho / ICD.
- **location_type**: (NATIVE ENUM) Loại ('Cảng', 'ICD', 'Kho bãi', 'Cửa khẩu').
- **address**: Địa chỉ chi tiết.
- **created_at / updated_at**: Thời điểm tạo và cập nhật.
- **deleted_at**: Thời điểm xóa mềm.

### Bảng: `service_prices` (Biểu giá dịch vụ)
- **service_name**: Tên dịch vụ (Cước Trucking, Phí HQ, v.v.).
- **unit**: Đơn vị tính (Chuyến, Cont 20, Cont 40).
- **unit_price**: Đơn giá chuẩn.
- **created_at / updated_at**: Thời điểm tạo và cập nhật.
- **deleted_at**: Thời điểm xóa mềm.

---

## 3. Nhóm Nghiệp vụ Cốt lõi (Core Operations)

### Bảng: `shipping_jobs` (Lô hàng / Booking)
**Trái tim của hệ thống.**
- **job_code**: Mã lô hàng (Unique, ví dụ: JOB-202310-001).
- **customer_id**: (Foreign Key -> `customers.id`).
- **customs_declaration_no**: Số tờ khai hải quan.
- **container_number**: Số hiệu container.
- **pickup_location_id**: (Foreign Key -> `locations.id`) Điểm lấy hàng.
- **delivery_location_id**: (Foreign Key -> `locations.id`) Điểm giao hàng.
- **status**: (NATIVE ENUM) 'Khởi tạo', 'Đang xử lý', 'Đang vận chuyển', 'Hoàn thành', 'Hủy'.
- **created_by**: (Foreign Key -> `users.id`) Sales tạo đơn.
- **created_at / updated_at**: Thời điểm tạo và cập nhật.
- **deleted_at**: Thời điểm xóa mềm.

### Bảng: `documents` (Kho chứng từ số)
- **shipping_job_id**: (Foreign Key -> `shipping_jobs.id`).
- **doc_category**: (NATIVE ENUM) 'Tờ khai', 'Invoice', 'Vận đơn', 'Biên lai phí', 'Phiếu EIR'.
- **file_url**: Đường dẫn tệp tin trên máy chủ.
- **uploaded_by**: Người tải lên.
- **status**: 'Chờ duyệt', 'Hợp lệ', 'Yêu cầu tải lại'.
- **created_at / updated_at**: Thời điểm tạo và cập nhật.
- **deleted_at**: Thời điểm xóa mềm.

### Bảng: `dispatch_orders` (Lệnh điều vận)
- **shipping_job_id**: Gắn với lô hàng nào.
- **vehicle_id / driver_id**: Xe và tài xế được phân công.
- **dispatch_status**: (NATIVE ENUM) 'Chờ chạy', 'Đang chạy', 'Hoàn thành'.
- **start_time / end_time**: Thời gian thực hiện thực tế.
- **created_at / updated_at**: Thời điểm tạo và cập nhật.
- **deleted_at**: Thời điểm xóa mềm.

---

## 4. Nhóm Tài chính (Finance & Accounting)

### Bảng: `cash_advances` (Tạm ứng)
Quản lý tiền ứng cho tài xế/hiện trường.
- **requested_by / approved_by**: Người xin ứng và Kế toán duyệt.
- **amount**: Số tiền ứng.
- **status**: 'Chờ duyệt', 'Đã duyệt chi', 'Đã hoàn ứng'.
- **created_at / updated_at**: Thời điểm tạo và cập nhật.
- **deleted_at**: Thời điểm xóa mềm.

### Bảng: `expenses` (Chi phí thực tế)
Quản lý các khoản chi hộ hoặc chi phí thực tế phát sinh.
- **document_id**: (Foreign Key -> `documents.id`) Bắt buộc có minh chứng ảnh hóa đơn/biên lai.
- **status**: 'Chờ duyệt', 'Hợp lệ / Đã duyệt'.
- **created_at / updated_at**: Thời điểm tạo và cập nhật.
- **deleted_at**: Thời điểm xóa mềm.

### Bảng: `debit_notes` (Giấy báo nợ / Hóa đơn tổng)
- **total_service_fee**: Doanh thu dịch vụ.
- **total_expense_paid**: Tổng chi hộ (từ bảng expenses).
- **grand_total**: Tổng cộng tiền khách phải trả.
- **created_at / updated_at**: Thời điểm tạo và cập nhật.
- **deleted_at**: Thời điểm xóa mềm.
- **status**: 'Chưa thu', 'Thanh toán một phần', 'Đã tất toán'.
---

## 5. Sơ đồ Quan hệ (Relationships Summary)
1. **1:N**: Một `Role` có nhiều `Users`.
2. **1:N**: Một `Customer` có nhiều `ShippingJobs`.
3. **1:N**: Một `ShippingJob` là trung tâm, liên kết với nhiều `Documents`, `DispatchOrders`, `Expenses`, `CashAdvances`.
4. **1:1**: Một `ShippingJob` có một `DebitNote` tổng quát.
5. **1:N**: Một `DebitNote` có thể được thanh toán qua nhiều đợt (`Payments`).

---
*Tài liệu được tạo tự động bởi Antigravity AI.*
