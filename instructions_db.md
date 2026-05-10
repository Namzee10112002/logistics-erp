Để xây dựng một cơ sở dữ liệu (Database) MySQL hoàn chỉnh và tối ưu cho Hệ thống Quản trị Logistics Nguyên Tâm theo nguyên tắc "Single Source of Truth" (mọi thứ xoay quanh mã Lô hàng/Job), tôi đã thiết kế cho bạn một lược đồ (Schema) chi tiết.

Dưới đây là danh sách các bảng (Tables), trường dữ liệu (Fields), kiểu dữ liệu (Data Types) và các liên kết (Relationships). Tôi sẽ chia theo từng nhóm phân hệ để bạn dễ hình dung:

Ký hiệu	Vai trò / Bộ phận	Mô tả quyền hạn
GĐ	Giám đốc / Quản trị viên	Quản lý chung, cấu hình hệ thống, phân quyền, xem báo cáo tổng quan.
KD	Kinh doanh	Tạo và quản lý thông tin đối tác, khách hàng, khởi tạo đơn hàng.
CT	Chứng từ	Xử lý, phân loại, tải lên và kiểm duyệt hồ sơ, tài liệu.
ĐV	Điều vận	Lập kế hoạch vận tải, phân công xe, tài xế và theo dõi hành trình.
KT	Kế toán	Duyệt chi phí, lập hóa đơn, thu chi tạm ứng và quản lý công nợ.
HT	Hiện trường / Tài xế	Nhận lệnh, cập nhật trạng thái chuyến đi, chụp ảnh tải lên biên lai/chi phí.

1. Nhóm Quản trị Hệ thống (System & Auth)
1.1. Bảng roles (Nhóm quyền)
Lưu trữ các vai trò trong hệ thống (GĐ, KD, CT, ĐV, KT, HT).

role_id (INT, Auto Increment) - PK

role_code (VARCHAR 50) - Mã quyền (VD: ADMIN, SALES, ACCOUNTANT)

role_name (VARCHAR 100) - Tên quyền hiển thị

description (TEXT) - Mô tả

1.2. Bảng users (Tài khoản người dùng)

user_id (INT, Auto Increment) - PK

username (VARCHAR 50, Unique) - Tên đăng nhập

password_hash (VARCHAR 255) - Mật khẩu (đã mã hóa)

full_name (VARCHAR 100) - Họ và tên nhân viên

role_id (INT) - FK liên kết bảng roles

status (TINYINT) - Trạng thái (1: Hoạt động, 0: Khóa)

created_at (TIMESTAMP) - Ngày tạo

2. Nhóm Danh mục (Master Data)
2.1. Bảng customers (Khách hàng)

customer_id (INT, Auto Increment) - PK

customer_code (VARCHAR 50, Unique) - Mã khách hàng

company_name (VARCHAR 255) - Tên công ty

tax_code (VARCHAR 50) - Mã số thuế

address (TEXT) - Địa chỉ

contact_person (VARCHAR 100) - Người liên hệ

phone (VARCHAR 20) - Số điện thoại

email (VARCHAR 100) - Email

2.2. Bảng vehicles (Xe / Phương tiện)

vehicle_id (INT, Auto Increment) - PK

license_plate (VARCHAR 20, Unique) - Biển số xe

vehicle_type (ENUM) - Loại xe ('Đầu kéo', 'Mooc', 'Xe tải')

payload (DECIMAL 10,2) - Tải trọng (Tấn)

registration_expiry (DATE) - Hạn đăng kiểm

status (ENUM) - Trạng thái ('Rảnh', 'Đang chạy', 'Bảo trì')

2.3. Bảng drivers (Tài xế)

driver_id (INT, Auto Increment) - PK

full_name (VARCHAR 100) - Tên tài xế

phone (VARCHAR 20) - Số điện thoại

license_class (VARCHAR 10) - Hạng bằng lái (VD: FC)

status (ENUM) - Trạng thái ('Rảnh', 'Đang chạy', 'Nghỉ')

2.4. Bảng locations (Địa điểm Cảng / Kho / ICD)

location_id (INT, Auto Increment) - PK

location_name (VARCHAR 255) - Tên địa điểm

location_type (ENUM) - Loại ('Cảng', 'ICD', 'Kho bãi', 'Cửa khẩu')

address (TEXT) - Địa chỉ chi tiết

2.5. Bảng service_prices (Biểu giá dịch vụ chuẩn)

price_id (INT, Auto Increment) - PK

service_name (VARCHAR 255) - Tên dịch vụ (VD: Cước Trucking, Phí khai HQ)

unit (VARCHAR 50) - Đơn vị tính (Chuyến, Cont 20, Cont 40)

unit_price (DECIMAL 15,2) - Đơn giá chuẩn

3. Nhóm Quản lý Đơn hàng (Core)
3.1. Bảng jobs (Lô hàng / Booking)
Đây là "trái tim" của hệ thống.

job_id (INT, Auto Increment) - PK

job_code (VARCHAR 50, Unique) - Mã Lô hàng (VD: JOB-202310-001)

customer_id (INT) - FK liên kết customers

customs_declaration_no (VARCHAR 100) - Số tờ khai Hải quan

container_number (VARCHAR 50) - Số hiệu Container

pickup_location_id (INT) - FK liên kết locations (Điểm lấy)

delivery_location_id (INT) - FK liên kết locations (Điểm giao)

cargo_type (VARCHAR 100) - Loại hàng hóa

container_type (VARCHAR 50) - Loại Cont (20ft, 40ft, Lạnh)

expected_date (DATETIME) - Thời gian yêu cầu thực hiện

status (ENUM) - Trạng thái ('Khởi tạo', 'Đang xử lý', 'Đang vận chuyển', 'Hoàn thành', 'Hủy')

created_by (INT) - FK liên kết users (Sales tạo đơn)

created_at (TIMESTAMP) - Ngày tạo đơn

4. Nhóm Quản lý Chứng từ
4.1. Bảng documents (Kho chứng từ số)

document_id (INT, Auto Increment) - PK

job_id (INT) - FK liên kết jobs (Gắn chặt với 1 Lô hàng)

doc_category (ENUM) - Phân loại ('Tờ khai', 'Invoice', 'Vận đơn', 'Biên lai phí', 'Phiếu EIR')

file_url (VARCHAR 255) - Đường dẫn file lưu trữ trên server

uploaded_by (INT) - FK liên kết users (Người tải lên)

status (ENUM) - Kiểm duyệt ('Chờ duyệt', 'Hợp lệ', 'Yêu cầu tải lại')

uploaded_at (TIMESTAMP) - Thời gian tải lên

5. Nhóm Quản lý Điều vận
5.1. Bảng dispatch_orders (Lệnh điều vận)
1 Job có thể có nhiều lệnh điều vận (VD: Lấy vỏ cont -> Đóng hàng -> Hạ bãi).

dispatch_id (INT, Auto Increment) - PK

job_id (INT) - FK liên kết jobs

vehicle_id (INT) - FK liên kết vehicles (Gán xe)

driver_id (INT) - FK liên kết drivers (Gán tài)

dispatch_status (ENUM) - Trạng thái lệnh ('Chờ chạy', 'Đang chạy', 'Hoàn thành')

start_time (DATETIME) - Thời gian xuất phát thực tế

end_time (DATETIME) - Thời gian hoàn thành thực tế

created_by (INT) - FK liên kết users (Điều vận viên tạo lệnh)

5.2. Bảng tracking_logs (Nhật ký hành trình)

log_id (INT, Auto Increment) - PK

dispatch_id (INT) - FK liên kết dispatch_orders

status_update (VARCHAR 255) - Trạng thái (VD: "Đã lấy vỏ cont", "Đang kẹt xe tại cảng")

updated_by (INT) - FK liên kết users (Tài xế/Hiện trường cập nhật)

created_at (TIMESTAMP)

6. Nhóm Quản lý Công nợ & Tài chính (Smart Accounting)
6.1. Bảng cash_advances (Tạm ứng tiền mặt)

advance_id (INT, Auto Increment) - PK

job_id (INT) - FK liên kết jobs

requested_by (INT) - FK liên kết users (Hiện trường/Tài xế xin ứng)

approved_by (INT) - FK liên kết users (Kế toán duyệt)

amount (DECIMAL 15,2) - Số tiền ứng

reason (TEXT) - Lý do ứng

status (ENUM) - ('Chờ duyệt', 'Đã duyệt chi', 'Đã hoàn ứng')

6.2. Bảng expenses (Chi phí thực tế / Chi hộ)

expense_id (INT, Auto Increment) - PK

job_id (INT) - FK liên kết jobs

dispatch_id (INT) - FK liên kết dispatch_orders (Có thể NULL nếu chi phí chung của Job)

expense_type (VARCHAR 100) - Loại phí (Cầu đường, Nâng hạ, Lưu bãi, Thuế)

amount (DECIMAL 15,2) - Số tiền thực chi

document_id (INT) - FK liên kết documents (Bắt buộc phải có ảnh hóa đơn minh chứng)

reported_by (INT) - FK liên kết users

status (ENUM) - ('Chờ duyệt', 'Hợp lệ / Đã duyệt')

6.3. Bảng debit_notes (Giấy báo nợ / Hóa đơn tổng)

debit_note_id (INT, Auto Increment) - PK

job_id (INT) - FK liên kết jobs

customer_id (INT) - FK liên kết customers

total_service_fee (DECIMAL 15,2) - Tổng phí dịch vụ (Doanh thu)

total_expense_paid (DECIMAL 15,2) - Tổng tiền chi hộ (Từ bảng expenses)

grand_total (DECIMAL 15,2) - Tổng cộng tiền khách phải trả

status (ENUM) - ('Chưa thu', 'Thanh toán một phần', 'Đã tất toán')

created_at (TIMESTAMP)

6.4. Bảng payments (Lịch sử thanh toán của khách)

payment_id (INT, Auto Increment) - PK

debit_note_id (INT) - FK liên kết debit_notes

amount_paid (DECIMAL 15,2) - Số tiền khách trả

payment_method (ENUM) - ('Chuyển khoản', 'Tiền mặt', 'Cấn trừ nợ')

payment_date (DATETIME) - Ngày nhận tiền

Tổng kết về Liên kết (Relationships) trong DB này:
Trung tâm (Hub): Bảng jobs là trung tâm. Khóa chính job_id xuất hiện dưới dạng khóa ngoại ở hầu hết các bảng nghiệp vụ (documents, dispatch_orders, cash_advances, expenses, debit_notes). Đúng chuẩn Single Source of Truth mà tài liệu yêu cầu.

Ràng buộc minh chứng (Evidence Rule): Trong bảng expenses, trường document_id đóng vai trò ép buộc dòng tiền chi ra phải có minh chứng vật lý (biên lai số hóa) đính kèm.

Tách bạch Dòng tiền: Tách rõ bảng Tạm ứng (cash_advances) và bảng Thực chi (expenses) để đối soát công nợ nội bộ với tài xế, sau đó mới đẩy sang debit_notes để đòi tiền khách hàng.