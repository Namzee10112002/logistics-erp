# Danh sách Tính năng & Phân quyền Hệ thống Logistics

Tài liệu này chi tiết các vai trò người dùng và các phân hệ chức năng trong hệ thống quản trị Logistics.

---

## 1. Định nghĩa Vai trò (Roles)

| Ký hiệu | Vai trò / Bộ phận | Mô tả quyền hạn |
|:---:|:---|:---|
| **GĐ** | **Giám đốc / Quản trị viên** | Quản lý chung, cấu hình hệ thống, phân quyền, xem báo cáo tổng quan. |
| **KD** | **Kinh doanh** | Tạo và quản lý thông tin đối tác, khách hàng, khởi tạo đơn hàng. |
| **CT** | **Chứng từ** | Xử lý, phân loại, tải lên và kiểm duyệt hồ sơ, tài liệu. |
| **ĐV** | **Điều vận** | Lập kế hoạch vận tải, phân công xe, tài xế và theo dõi hành trình. |
| **KT** | **Kế toán** | Duyệt chi phí, lập hóa đơn, thu chi tạm ứng và quản lý công nợ. |
| **HT** | **Hiện trường / Tài xế** | Nhận lệnh, cập nhật trạng thái chuyến đi, chụp ảnh tải lên biên lai/chi phí. |

---

## 2. Chi tiết Phân hệ Chức năng

### ⚙️ Quản lý Hệ thống
| Tính năng | Mô tả chi tiết | Phân quyền | Trạng thái |
|:---|:---|:---:|:---:|
| **Xác thực & Phiên làm việc** | Đăng nhập, đăng xuất, duy trì trạng thái kết nối an toàn. | Tất cả | ✅ |
| **Cấu hình hệ thống** | Thiết lập các tham số, quy tắc hoạt động chung (ví dụ: hằng số thuế, tỷ giá). | GĐ | ✅ |
| **Quản lý tài khoản & Phân quyền** | Thêm/sửa/xóa tài khoản nhân viên; thiết lập ma trận quyền hạn. | GĐ | ✅ |
| **Hệ thống Thông báo** | Thông báo thời gian thực về duyệt phí, cập nhật chuyến xe. | Tất cả | ✅ |
| **Sao lưu & Khôi phục** | Định kỳ lưu trữ dự phòng cơ sở dữ liệu và phục hồi khi có sự cố. | GĐ | ✅ |

### 📂 Quản lý Danh mục (Master Data)
| Tính năng | Mô tả chi tiết | Phân quyền | Trạng thái |
|:---|:---|:---|:---:|
| **Quản lý Khách hàng** | Hồ sơ thông tin đối tác, mã số thuế, địa chỉ liên hệ. | KD, GĐ | ✅ |
| **Quản lý Nguồn lực (Xe / Tài xế)** | Lưu thông tin biển số, tải trọng, hạn đăng kiểm xe; hồ sơ, bằng lái của tài xế. | ĐV, GĐ | ✅ |
| **Quản lý Biểu giá** | Thiết lập bảng giá cước chuẩn cho từng tuyến/dịch vụ để tính công nợ tự động. | KT, KD, GĐ | ✅ |
| **Địa điểm & Đơn vị tính** | Chuẩn hóa danh sách cảng, ICD; thiết lập đơn vị đo lường. | ĐV, GĐ | ✅ |

### 🚢 Quản lý Đơn hàng
| Tính năng | Mô tả chi tiết | Phân quyền | Trạng thái |
|:---|:---|:---|:---:|
| **Khởi tạo & Cập nhật Booking** | Tạo hồ sơ lô hàng mới, nhập thông số kỹ thuật, quy cách đóng gói, thời gian dự kiến. | KD | ✅ |
| **Theo dõi Trạng thái** | Ghi nhận và hiển thị tiến trình lô hàng (đang xử lý, đang thông quan, hoàn thành). | Tất cả | ✅ |
| **Liên kết dữ liệu** | Tự động kết nối đơn hàng với chứng từ và chi phí liên đới thuộc các phân hệ khác. | Tất cả | ✅ |

### 📄 Quản lý Chứng từ (Kho dữ liệu số)
| Tính năng | Mô tả chi tiết | Phân quyền | Trạng thái |
|:---|:---|:---|:---:|
| **Tạo & Tiếp nhận chứng từ** | Tải ảnh/file chứng từ (biên lai, EIR, phiếu hạ bãi) từ hiện trường lên hệ thống qua thiết bị di động. | HT, CT | ✅ |
| **Định danh & Phân loại** | Gắn thẻ (tag) chứng từ theo danh mục (Invoice, Vận đơn) và gắn vào đúng mã lô hàng. | CT | ✅ |
| **Kiểm duyệt trạng thái** | Bộ phận chuyên môn xác nhận tính hợp lệ, rõ nét và đầy đủ của chứng từ. | CT | ✅ |
| **Lưu trữ & Tra cứu tập trung** | Đưa chứng từ vào kho số an toàn chống chỉnh sửa; hỗ trợ tìm kiếm nhanh để phục vụ đối soát. | CT, KT, GĐ | ✅ |

### 💰 Quản lý Công nợ & Tài chính
| Tính năng | Mô tả chi tiết | Phân quyền | Trạng thái |
|:---|:---|:---|:---:|
| **Xử lý tạm ứng** | Tiếp nhận yêu cầu và duyệt chi tiền đi đường cho nhân sự hiện trường. | HT (Tạo), KT (Duyệt) | ✅ |
| **Ghi nhận & Duyệt chi phí** | Nhập chi phí phát sinh thực tế kèm hình ảnh biên lai và đối chiếu tính hợp lệ. | HT (Nhập), KT (Duyệt) | ✅ |
| **Lập Giấy báo nợ (Debit Note)** | Tự động tổng hợp chi phí chi hộ, áp giá dịch vụ và xuất file PDF đòi tiền khách hàng. | KT | ✅ |
| **Chốt & Theo dõi công nợ** | Ghi nhận tiền về (UNC), cấn trừ số dư, báo cáo nợ quá hạn và hoàn tất nợ. | KT, GĐ | ✅ |

### 🚛 Quản lý Điều vận
| Tính năng | Mô tả chi tiết | Phân quyền | Trạng thái |
|:---|:---|:---|:---:|
| **Tiếp nhận & Lập kế hoạch** | Nhận yêu cầu từ phân hệ đơn hàng, lên phương án vận tải tránh trùng lịch. | ĐV | ✅ |
| **Phân công (Gán lệnh)** | Gán nối chính xác xe (đầu kéo, mooc) và tài xế cho từng chuyến hàng. | ĐV | ✅ |
| **Theo dõi hành trình (Real-time)** | Giám sát trạng thái, thời gian xuất/nhập bãi và lộ trình thực tế của tài xế. | HT (Cập nhật), ĐV (Xem) | ✅ |
| **Quản lý chi phí vận hành** | Theo dõi định mức nhiên liệu, phí cầu đường gắn với từng lệnh điều động. | ĐV, KT | ✅ |

### 📊 Báo cáo & Thống kê
| Tính năng | Mô tả chi tiết | Phân quyền | Trạng thái |
|:---|:---|:---|:---:|
| **Dashboard Giám sát (KPIs)** | Màn hình hiển thị trực quan các chỉ số hoạt động kinh doanh tổng quan theo thời gian thực. | GĐ | ✅ |
| **Báo cáo Tài chính** | Thống kê doanh thu, tỷ suất lợi nhuận, và cảnh báo công nợ (nợ xấu, quá hạn). | GĐ, KT | ✅ |
| **Báo cáo Vận hành** | Đánh giá năng lực đội xe (hiệu suất vận tải) và tình trạng xử lý các lô hàng (tồn đọng, hoàn tất). | GĐ, ĐV, KD | ✅ |