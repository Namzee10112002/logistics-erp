# 📑 Kế hoạch Kiểm thử Hệ thống Logistics ERP

Tài liệu này dùng để theo dõi tiến độ kiểm thử qua từng giai đoạn triển khai.

---

## 🔐 Giai đoạn 1.1: Xác thực & Phân quyền

### Bước 1: Đăng nhập
1. Truy cập trang `/login`.
2. Nhập tài khoản: `admin_test` / mật khẩu: `password`.
- **Kết quả mong đợi:** Đăng nhập thành công, chuyển hướng về Dashboard. Hiển thị đúng tên và vai trò trên thanh điều hướng.

---

## 📂 Giai đoạn 1.2: Quản lý Danh mục (Master Data)

### Bước 1: Quản lý Khách hàng
1. Chọn mục **"Khách hàng"** trên Sidebar.
2. Thêm mới một khách hàng với đầy đủ thông tin.
- **Kết quả mong đợi:** Khách hàng mới hiển thị trong danh sách. Nút Sửa/Xóa hoạt động đúng.

### Bước 2: Quản lý Phương tiện & Tài xế
1. Vào mục **"Đội xe"** và **"Tài xế"**.
2. Thêm mới dữ liệu mẫu.
- **Kết quả mong đợi:** Dữ liệu lưu thành công, hiển thị đúng biển số xe và tên tài xế.

---

## 🚢 Giai đoạn 2: Quản lý Đơn hàng vận chuyển (Shipping Jobs)

### Bước 1: Xem danh sách & Tìm kiếm
1. Chọn mục **"Đơn hàng"** trên Sidebar.
- **Kết quả mong đợi:** Hiển thị danh sách các lô hàng.
2. Thử nhập từ khóa vào ô tìm kiếm.
- **Kết quả mong đợi:** Danh sách tự động lọc theo từ khóa.

### Bước 2: Tạo đơn hàng mới
1. Nhấn **"TẠO ĐƠN HÀNG MỚI"**.
2. Nhập đầy đủ thông tin lộ trình, loại hàng.
3. Nhấn "Lưu đơn hàng".
- **Kết quả mong đợi:** Hệ thống tự động sinh mã Job dạng `JOB-YYYYMMDD-XXX`. Trạng thái là **"Mới tạo"**.

---

## 🚛 Giai đoạn 3: Phân hệ Điều vận (Dispatch Orders)

### Bước 1: Lập lệnh điều xe
1. Vào chi tiết một Đơn hàng mới.
2. Nhấn **"LẬP LỆNH ĐIỀU XE"**.
3. Chọn Tài xế và Xe.
- **Kết quả mong đợi:** Trạng thái Job chuyển sang **"Đã điều xe"**. Lệnh điều xe hiển thị trong danh sách hành trình.

---

## 💰 Giai đoạn 4: Quản lý Chi phí & Chứng từ (Billing & Expenses)

### Bước 1: Ghi nhận chi phí
1. Tại trang chi tiết Đơn hàng, nhấn **"THÊM CHI PHÍ"**.
2. Nhập loại phí và số tiền.
- **Kết quả mong đợi:** Bảng chi phí cập nhật và tự động tính tổng tiền.

### Bước 2: Tải lên chứng từ
1. Nhấn biểu tượng **Upload** tại sidebar chi tiết Job.
2. Chọn file và tải lên.
- **Kết quả mong đợi:** File hiển thị trong danh sách hồ sơ, có thể nhấn xem hoặc xóa.
    
---

## 💰 Giai đoạn 5: Tài chính nâng cao & Quyết toán (Cash Advances & Billing)

### Bước 1: Yêu cầu & Phê duyệt Tạm ứng
1. Tại sidebar trang chi tiết đơn hàng, nhấn **"YÊU CẦU TẠM ỨNG"**.
2. Nhập số tiền (vd: 500.000) và lý do.
3. Nhấn **"Duyệt"** tại bảng danh sách tạm ứng (Giả định bạn đang có quyền kế toán/giám đốc).
- **Kết quả mong đợi:** Trạng thái chuyển sang **"Đã chi"**.

### Bước 2: Lập Giấy báo nợ (Debit Note)
1. Tại sidebar, nhấn **"LẬP GIẤY BÁO NỢ"**.
- **Kết quả mong đợi:** 
  - Hệ thống tự động tổng hợp phí vận chuyển (dựa trên loại Container) và các chi phí đã duyệt.
  - Hiển thị bảng tóm tắt số báo nợ, tổng tiền và trạng thái **"Chưa thu tiền"**.
2. Nhấn **"Xem & In Báo Nợ"**.
- **Kết quả mong đợi:** Mở ra trang chi tiết báo nợ với giao diện in ấn chuyên nghiệp.

### Bước 3: Ghi nhận thanh toán
1. Quay lại đơn hàng, nhấn **"Ghi nhận Thanh toán"**.
2. Nhập số tiền khớp với tổng tiền báo nợ.
- **Kết quả mong đợi:** Trạng thái báo nợ chuyển sang **"Đã tất toán"**.

---

---

## 🔐 Giai đoạn 7: Phân quyền Nâng cao & Cá nhân hóa (Mới cập nhật)

### Bước 1: Kiểm tra quyền Kinh doanh (SALES)
1. Đăng nhập bằng tài khoản **Kinh doanh**.
2. Thử tạo mới một đơn hàng -> **Kết quả mong đợi:** Thành công.
3. Thử sửa một đơn hàng hiện có -> **Kết quả mong đợi:** Thành công.

### Bước 2: Kiểm tra quyền Tài xế (DRIVER) & Hiện trường (FIELD)
1. Đăng nhập bằng tài khoản **Tài xế**.
2. Truy cập `/shipping-jobs/create` -> **Kết quả mong đợi:** Lỗi 403 (Cấm truy cập).
3. Xem chi tiết đơn hàng và cập nhật trạng thái chuyến đi -> **Kết quả mong đợi:** Thành công.
4. Đăng nhập bằng tài khoản **Hiện trường**.
5. Tải lên chứng từ cho đơn hàng -> **Kết quả mong đợi:** Thành công.

### Bước 3: Kiểm tra quyền Điều vận (DISPATCH)
1. Đăng nhập bằng tài khoản **Điều vận**.
2. Truy cập mục Báo cáo -> Tài chính -> **Kết quả mong đợi:** Lỗi 403 hoặc menu bị ẩn.
3. Tại trang Điều vận, kiểm tra bảng "Đơn hàng chờ phân công" -> **Kết quả mong đợi:** Hiển thị đúng các đơn hàng chưa có lệnh điều xe.

### Bước 4: Cập nhật Hồ sơ & Theme
1. Vào mục **"Hồ sơ của tôi"** ở Sidebar.
2. Thay đổi Theme Color (ví dụ sang màu Đỏ) và bật Dark Mode.
3. Nhấn "Lưu thay đổi".
- **Kết quả mong đợi:** 
  - Giao diện ngay lập tức chuyển sang chế độ tối.
  - Các thành phần quan trọng (Nút, Sidebar) chuyển sang tông màu đỏ đã chọn.
  - Sau khi đăng xuất và đăng nhập lại, cài đặt vẫn được giữ nguyên.

### Bước 5: Kiểm tra Sidebar
1. Kiểm tra vị trí nút "Đăng xuất" -> **Kết quả mong đợi:** Luôn nằm cố định ở góc dưới bên trái, không bị trôi khi cuộn danh sách menu.
