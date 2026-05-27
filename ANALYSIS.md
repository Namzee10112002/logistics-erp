# 📊 PHÂN TÍCH CÁC CHỨC NĂNG SETTINGS, USERS & REPORTS

## I. TỔNG QUAN

Ứng dụng Logistics ERP quản lý 3 module chính:
1. **Settings** - Cấu hình hệ thống, tham số vận hành, lưu trữ dữ liệu
2. **Users** - Quản lý nhân viên, phân quyền, phân công công việc
3. **Reports** - Báo cáo tài chính & vận hành, phân tích dữ liệu

---

## II. PHÂN TÍCH CHI TIẾT

### 🔧 **A. MODULE SETTINGS (Cài đặt Hệ thống)**

#### **1. KIẾN TRÚC & LUỒNG DỮ LIỆU**

```
User (Browser)
    ↓
ProfileController.php (Hồ sơ cá nhân)
SettingController.php (Cài đặt hệ thống)
    ↓
Setting Model (Database - `settings` table)
    ↓
View: settings/index.blade.php
```

#### **2. THÀNH PHẦN CHÍNH**

**SettingController.php - Phương thức quan trọng:**

| Phương thức | Chức năng | Logic |
|------------|---------|-------|
| `index()` | Hiển thị trang cài đặt | - Lấy tất cả settings từ DB, nhóm theo group<br>- Tạo auto các tham số mặc định nếu chưa tồn tại<br>- Hiển thị thông tin user hiện tại |
| `update()` | Cập nhật tham số | - Nhận 2 loại: `settings` (công ty) & `system_params` (hệ thống)<br>- Update hoặc Create mới từng cặp key-value<br>- Ghi log vào ActivityLog |
| `backup()` | Sao lưu DB | - Dùng `mysqldump` để export DB thành file .sql<br>- Lưu vào thư mục `storage/backup_tmp/`<br>- Tìm mysqldump.exe từ XAMPP hoặc PATH system |
| `restore()` | Khôi phục DB | - Validate file .sql & mật khẩu admin<br>- Dùng `mysql` CLI để restore<br>- Ghi log kết quả |

**ProfileController.php - Phương thức quan trọng:**

| Phương thức | Chức năng | Validation |
|------------|---------|-----------|
| `edit()` | Hiển thị form edit hồ sơ | - Lấy user hiện tại |
| `update()` | Cập nhật hồ sơ cá nhân | - **name**: bắt buộc, max 255<br>- **email**: unique (trừ user hiện tại)<br>- **password**: nullable, có ký tự đặc biệt, số, hoa/thường<br>- **theme_color**: hex color string<br>- **timezone**: chỉ cho phép 3 giá trị (HCM, Bangkok, UTC)<br>- **date_format**: d/m/Y | Y-m-d | d-m-Y |

#### **3. THAM SỐ HỆ THỐNG MẶC ĐỊNH**

```php
[
  'system.usd_rate' => 25450 đ,          // Tỷ giá USD-VND
  'system.vat_percent' => 10%,           // Thuế GTGT
  'system.fuel_limit' => 5,000,000 đ,    // Hạn mức chi tiêu dầu/chuyến
  'system.toll_limit' => 2,000,000 đ,    // Hạn mức phí cầu đường/chuyến
  'system.overage_alert' => 0            // Cảnh báo vượt hạn (on/off)
]
```

#### **4. GIAO DIỆN & TABS**

**Settings/index.blade.php - 5 TAB:**

| Tab | Quyền truy cập | Nội dung |
|-----|---------------|---------|
| **1. Hồ sơ cá nhân** | Tất cả | - Họ tên, Email<br>- Bộ phận (readonly), Chức vụ (readonly) |
| **2. Bảo mật & hệ thống** | Tất cả | - Đổi mật khẩu<br>- 2FA settings<br>- Sao lưu / Khôi phục DB |
| **3. Tham số hệ thống** | ADMIN only | - USD rate, VAT %, hạn mức dầu/phí cầu<br>- Cảnh báo vượt định mức |
| **4. Phân quyền & biểu mẫu** | ADMIN only | - Form permission mapping<br>- Role-based access control |
| **5. Lưu trữ dữ liệu** | ADMIN only | - Backup database (.sql)<br>- Restore database (với xác thực mật khẩu) |

#### **5. LOGIC NGHIỆP VỤ CHÍNH**

**A) Sao lưu Database:**
```
1. Lấy config DB (host, port, user, pass)
2. Tìm đường dẫn mysqldump.exe:
   - Ưu tiên C:\xampp\mysql\bin\mysqldump.exe
   - Hoặc dùng mysqldump từ PATH system
3. Chạy command: mysqldump > file.sql
4. Kiểm tra exit code:
   - 0 = thành công → download file
   - ≠ 0 = thất bại → báo lỗi
5. Ghi ActivityLog
6. Xóa file sau khi download
```

**B) Khôi phục Database:**
```
1. Validate: file .sql (max 50MB) + mật khẩu admin
2. Check mật khẩu admin Hash vs user.password
3. Upload file .sql vào storage/restore_tmp/
4. Chạy command: mysql < file.sql
5. Kiểm tra exit code & ghi log
6. Báo cáo kết quả cho user
7. Xóa file tạm
```

#### **6. SCHEMA DATABASE - SETTINGS TABLE**

```sql
CREATE TABLE settings (
  id BIGINT PRIMARY KEY,
  key VARCHAR(255) UNIQUE,        -- VD: 'system.usd_rate'
  value TEXT,                     -- '25450'
  group VARCHAR(100),             -- 'system', 'company', etc
  description TEXT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
)
```

---

### 👥 **B. MODULE USERS (Quản lý Nhân sự)**

#### **1. KIẾN TRÚC & LUỒNG DỮ LIỆU**

```
User Manager (Browser)
    ↓
UserController.php
    ├─ index()      → List users với filter
    ├─ create()     → Form thêm nhân viên
    ├─ store()      → Insert + validate
    ├─ edit()       → Form edit + authorize
    ├─ update()     → Update + validate
    └─ destroy()    → Xóa user + audit log
    ↓
User Model + Role Model
    ↓
Database: users, roles, role_user (pivot)
    ↓
View: users/index.blade.php | create.blade.php | edit.blade.php
```

#### **2. MODEL SCHEMA - USERS TABLE**

```sql
CREATE TABLE users (
  id BIGINT PRIMARY KEY,
  name VARCHAR(255),                    -- Họ tên
  email VARCHAR(255) UNIQUE,            -- Email login
  email_verified_at TIMESTAMP NULL,
  password VARCHAR(255),                -- Hash password
  username VARCHAR(100),                -- Tách từ email (user@domain.com → user)
  employee_code VARCHAR(50) UNIQUE,     -- Auto-generate: EMP-YYMMDD-XXXXX
  role_id BIGINT,                       -- FK → roles.id
  status INT (1 = active, 0 = inactive),
  position VARCHAR(100),                -- VD: "Trưởng phòng", "Nhân viên"
  department VARCHAR(100),              -- VD: "Kinh doanh", "Kỹ thuật"
  date_of_birth DATE,
  joined_at DATE,
  
  -- Optional fields (if exists in schema)
  timezone VARCHAR(50),                 -- Asia/Ho_Chi_Minh
  date_format VARCHAR(20),              -- d/m/Y | Y-m-d
  theme_color VARCHAR(7),               -- Hex: #1a237e
  is_dark_mode BOOLEAN,
  two_factor_enabled BOOLEAN,
  
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  
  FOREIGN KEY (role_id) REFERENCES roles(id)
)
```

#### **3. PHƯƠNG THỨC & LOGIC TRONG UserController**

**a) index() - Danh sách nhân viên**

```
1. Query users với eager load role
2. ROLE-BASED FILTERING:
   - Nếu DISPATCH role: chỉ filter Driver & Field Staff
   - Nếu ADMIN: xem tất cả
3. SEARCH/FILTER (các field):
   - employee_code, name, email, position, department
   - role_id (dropdown)
4. EXPORT (nếu có query param export=xlsx|csv|pdf|docx):
   - Lấy max 10,000 records
   - Gửi tới ExportService
   - Download file với headers
5. PAGINATION: 10 records/page
```

**b) create() - Form thêm nhân viên**

```
1. Load available ROLES:
   - ADMIN: xem tất cả roles
   - DISPATCH: chỉ DRIVER & FIELD
2. Load DEPARTMENTS & POSITIONS:
   - Lấy từ LogisticsOptions class
   - Chuẩn bị cho cascading dropdown
3. Pass data tới view template
```

**c) store() - Lưu nhân viên mới**

```
VALIDATION RULES:
- name: required, string, max 255
- email: required, email, unique:users
- password: required, confirmed, min 8 chars
           + uppercase + lowercase + numbers + symbols
- role_id: required, exists in roles table
- department: required, must be in LogisticsOptions::departmentPositionMap()
- position: required, must match department's valid positions
- date_of_birth: required, date, before today
- joined_at: required, date, within last 10 years, before or equal today

EXTRA CHECKS:
- If DISPATCH role: only allow creating DRIVER & FIELD Staff

CREATE LOGIC:
- Generate employee_code: $this->generateEmployeeCode()
  └─ Format: EMP-YYMMDD-NNNNN (auto-increment)
- Hash password with bcrypt
- Create User record dengan status = 1 (active)
- Extract username từ email (user@domain → user)
```

**d) edit() - Form chỉnh sửa**

```
AUTHORIZATION:
- If DISPATCH: only can edit DRIVER & FIELD users (check role.role_code)
- Otherwise: 403 Forbidden

LOAD DATA:
- User record (with role)
- Available roles (filtered by user's permission)
- Department & position lists
```

**e) update() - Lưu thay đổi**

```
VALIDATION:
- Tương tự như store() nhưng:
  * email: unique trừ user_id hiện tại
  * password: OPTIONAL (null)
  * Nếu password không rỗng: validate + hash

UPDATE LOGIC:
- Update name, email, department, position
- Update role_id, date_of_birth, joined_at
- If password filled: hash & update
- Ghi audit log
```

**f) destroy() - Xóa user**

```
1. Authorization check
2. Soft delete (mark as inactive) hoặc Hard delete
3. Ghi audit log
4. Redirect với success message
```

#### **4. CASCADING DEPARTMENT/POSITION DROPDOWN**

**Frontend JavaScript Logic (Create & Edit):**

```javascript
const DEPT_POSITION_MAP = @json(LogisticsOptions::departmentPositionMap());
// VD: {
//   'Kinh doanh': ['Trưởng phòng', 'Nhân viên'],
//   'Kỹ thuật': ['TL Kỹ thuật', 'Kỹ sư'],
//   'Tài xế': ['Tài xế']
// }

document.getElementById('department').addEventListener('change', (e) => {
  const dept = e.target.value;
  const positions = DEPT_POSITION_MAP[dept] || [];
  
  // Populate position dropdown with valid positions
  updatePositionDropdown(positions);
});
```

#### **5. PASSWORD VALIDATION (Frontend)**

- Min 8 characters
- Uppercase + Lowercase
- At least 1 number
- At least 1 special character (!@#$%^&*)
- Toggle password visibility (eye icon)
- Real-time validation feedback
- Confirm password match

#### **6. EMAIL LIVE VALIDATION**

```javascript
// Pattern: user@domain.com
const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
```

#### **7. EXPORT FUNCTIONALITY**

```
Route: users.index?export=xlsx|csv|pdf|docx

Flow:
1. Get filtered users (max 10,000)
2. Map to array: [employee_code, name, email, role, position, dept, DOB, join_date]
3. Call ExportService.download(format, title, subtitle, headers, data)
4. Return downloadable file
```

#### **8. ROLE-BASED ACCESS CONTROL**

```
DISPATCH Role Restrictions:
├─ Can only view/create/edit DRIVER & FIELD users
├─ Cannot access other roles
└─ Abort 403 if tries to access restricted roles

ADMIN Role:
├─ Full access to all users
├─ All roles visible
└─ All departments available
```

---

### 📈 **C. MODULE REPORTS (Báo cáo & Phân tích)**

#### **1. KIẾN TRÚC & LUỒNG DỮ LIỆU**

```
Report User (Browser)
    ↓
ReportController.php
    ├─ operational()  → Báo cáo vận hành
    └─ financial()    → Báo cáo tài chính
    ↓
Query Database (Complex joins & aggregations)
    ├─ ShippingJob model
    ├─ DispatchOrder model
    ├─ Driver model
    ├─ Vehicle model
    ├─ DebitNote model (Revenue)
    ├─ Expense model (Costs)
    └─ RecurringExpense model (Fixed costs)
    ↓
Export Service (if export param)
    └─ ExportService.download(format, data)
    ↓
View: reports/operational.blade.php | financial.blade.php
```

#### **2. OPERATIONAL REPORT (Báo cáo Vận hành)**

**A) Report Filter Options:**

```
Period Type:
├─ last_6_months (default)  → T-6 tháng đến hôm nay
├─ quarter                  → Quý cụ thể
└─ year                     → Năm cụ thể

resolveReportRange() Logic:
├─ Nếu period = 'last_6_months':
│  └─ dateFrom = 6 tháng trước
│  └─ dateTo = hôm nay
├─ Nếu period = 'quarter':
│  └─ dateFrom = Q1 = Jan 1
│  └─ dateTo = Q4 = Dec 31
└─ Nếu period = 'year':
   └─ dateFrom = Jan 1
   └─ dateTo = Dec 31
```

**B) Data Queries:**

```sql
1. JOB STATUS DISTRIBUTION:
   SELECT status, COUNT(*) as total
   FROM shipping_jobs
   WHERE created_at BETWEEN ? AND ?
   GROUP BY status
   
   Status values: pending, confirmed, in_transit, completed, cancelled, failed

2. VEHICLE USAGE & PERFORMANCE:
   SELECT vehicles.plate_number, COUNT(*) as total_trips
   FROM dispatch_orders
   JOIN vehicles ON dispatch_orders.vehicle_id = vehicles.id
   WHERE dispatch_orders.created_at BETWEEN ? AND ?
   GROUP BY vehicles.id
   ORDER BY total_trips DESC
   LIMIT 5 (Top 5 vehicles)

3. DRIVER PRODUCTIVITY:
   SELECT drivers.full_name, COUNT(*) as total_jobs
   FROM dispatch_orders
   JOIN drivers ON dispatch_orders.driver_id = drivers.id
   WHERE dispatch_status = 'completed'
     AND dispatch_orders.created_at BETWEEN ? AND ?
   GROUP BY drivers.id
   ORDER BY total_jobs DESC
   LIMIT 5 (Top 5 drivers)

4. DRIVER DETAILED METRICS:
   - total_trips: COUNT(*)
   - total_days: COUNT(DISTINCT DATE(start_time))
   - total_hours: SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time) / 60)
```

**C) Export Format:**

```
Single Sheet: "Năng suất tài xế"
Columns: [Tài xế, Số chuyến hoàn thành, Số ngày chạy, Tổng giờ chạy]
Rows: Array of driver productivity data
```

#### **3. FINANCIAL REPORT (Báo cáo Tài chính)**

**A) Summary Cards (Dashboard):**

```
┌─────────────────────────────────────────┐
│ Tổng Doanh thu        │ SUM(debit_notes.grand_total)
├─────────────────────────────────────────┤
│ Tổng Chi phí (Đã duyệt) │ SUM(expenses.amount) WHERE status='approved'
├─────────────────────────────────────────┤
│ Chi phí cố định/tháng  │ SUM(recurring_expenses.amount) WHERE status='active'
├─────────────────────────────────────────┤
│ Lợi nhuận gộp         │ Revenue - Expenses - Recurring Expenses
└─────────────────────────────────────────┘
```

**B) Core Queries:**

```sql
1. MONTHLY REVENUE (Chart):
   SELECT DATE_FORMAT(issued_at, '%m/%Y') as month,
          SUM(grand_total) as total
   FROM debit_notes
   WHERE issued_at BETWEEN ? AND ?
   GROUP BY month
   ORDER BY issued_at ASC

2. CUSTOMER REVENUE BREAKDOWN (Top 5):
   SELECT customers.company_name,
          SUM(debit_notes.grand_total) as total
   FROM debit_notes
   JOIN customers ON debit_notes.customer_id = customers.id
   WHERE debit_notes.issued_at BETWEEN ? AND ?
   GROUP BY customers.id
   ORDER BY total DESC
   LIMIT 5

3. REVENUE BY DEBIT NOTE:
   SELECT dn.note_number, sj.job_code, c.company_name,
          dn.issued_at, dn.total_service_fee,
          dn.total_expense_paid, dn.grand_total, dn.status
   FROM debit_notes dn
   LEFT JOIN customers c ON dn.customer_id = c.id
   LEFT JOIN shipping_jobs sj ON dn.shipping_job_id = sj.id
   WHERE dn.issued_at BETWEEN ? AND ?

4. OVERDUE DEBT (> 30 days unpaid):
   SELECT dn.note_number, c.company_name, dn.issued_at,
          dn.grand_total,
          DATEDIFF(NOW(), dn.issued_at) as days_overdue
   FROM debit_notes dn
   JOIN customers c ON dn.customer_id = c.id
   WHERE dn.status = 'unpaid'
     AND dn.issued_at < NOW() - INTERVAL 30 DAY
   LIMIT 25

5. RECURRING EXPENSES:
   SELECT expense_code, name, category, cycle, amount, status
   FROM recurring_expenses
   WHERE status = 'active'
   ORDER BY created_at DESC
   PAGINATE 10
```

**C) Financial Metrics Calculation:**

```
Tổng Doanh thu (Revenue):
  = SUM(debit_notes.grand_total) in period

Tổng Chi phí Biến đổi (Variable Expenses):
  = SUM(expenses.amount) WHERE status='approved' in period

Chi phí Cố định Hàng tháng (Recurring):
  = SUM(recurring_expenses.amount) WHERE status='active'

Lợi nhuận Gộp (Gross Profit):
  = Revenue - Variable Expenses - Monthly Recurring Costs

Lợi Nhuận Ròng (Net Profit):
  = Gross Profit - Additional overheads
```

**D) Recurring Expenses Management:**

```
- List recurring expenses (paginated)
- Edit: update name, category, cycle, amount
- Delete: soft delete or remove
- Cycles: 'monthly', 'quarterly', 'yearly'
- Status tracking: 'active', 'inactive'

UI Features:
├─ Add button opens modal
├─ Edit button redirects to edit page
└─ Delete button with confirmation
```

**E) Export Sheet Structure:**

```
Multiple sheets for different data:

Sheet 1: "Tóm tắt tài chính"
├─ Tổng doanh thu
├─ Tổng chi phí
├─ Chi phí cố định
└─ Lợi nhuận

Sheet 2: "Chi tiết doanh thu"
├─ Số báo nợ, Mã công việc, Khách hàng
├─ Ngày lập, Service fee, Expense paid, Total
└─ Status (paid/unpaid)

Sheet 3: "Chi tiết chi phí"
├─ Expense details (category, date, amount, status)

Sheet 4: "Chi phí cố định"
├─ Recurring expense list
```

#### **4. EXPORT SERVICE (ExportService.php)**

```
Supported Formats:
├─ CSV    → Simple comma-separated
├─ XLSX   → Excel workbook (multi-sheet)
├─ DOCX   → Word document
└─ PDF    → PDF with branding

Features:
├─ Company branding (logo, headers, footers)
├─ Vietnamese localization (currency, date format)
├─ Multi-sheet export (XLSX)
├─ Table formatting (headers, borders, colors)
└─ Filename: report_[type]_[period]_[date].ext

Usage:
app(ExportService::class)
  ->download(format, title, subtitle, headers, data)
```

#### **5. CHART.JS VISUALIZATION**

**Operational Report:**

```javascript
// Status Distribution (Pie Chart)
new Chart(statusCtx, {
  type: 'pie',
  data: {
    labels: ['pending', 'confirmed', 'completed', ...],
    datasets: [{ data: [10, 25, 40, ...] }]
  }
});

// Vehicle Performance (Bar Chart)
new Chart(vehicleCtx, {
  type: 'bar',
  data: {
    labels: ['Plate 1', 'Plate 2', ...],  // Top 5 vehicles
    datasets: [{ 
      label: 'Số chuyến đi',
      data: [15, 22, 18, ...] 
    }]
  }
});
```

**Financial Report:**

```javascript
// Monthly Revenue Trend (Line Chart)
new Chart(revenueCtx, {
  type: 'line',
  data: {
    labels: ['01/2024', '02/2024', ...],
    datasets: [{ 
      label: 'Doanh thu',
      data: [50000000, 65000000, ...],
      borderColor: '#1e3a8a'
    }]
  }
});

// Customer Revenue Distribution (Pie Chart)
new Chart(customerCtx, {
  type: 'pie',
  data: {
    labels: ['Cty A', 'Cty B', ...],
    datasets: [{ data: [30, 25, 20, ...] }]
  }
});
```

---

## III. FLOW DIAGRAMS

### **USER CREATION WORKFLOW**

```
Form Submit (create.blade.php)
    ↓
POST /users (UserController@store)
    ↓
Validate Request:
  ├─ Check email unique
  ├─ Check department-position mapping
  ├─ Check password strength
  ├─ Check date_of_birth < today
  └─ Check joined_at within 10 years
    ↓ (Validation Fails)
    ↓ Redirect back with errors
    ↓
Generate employee_code (auto-increment)
    ↓
Create User:
  ├─ name, email, password (hashed)
  ├─ role_id, department, position
  ├─ date_of_birth, joined_at
  ├─ status = 1 (active)
  └─ username (extracted from email)
    ↓
ActivityLog::log('create_user', ...)
    ↓
Redirect to users.index with success message
```

### **REPORT GENERATION WORKFLOW**

```
Select Report Period & Filter
    ↓
Form Submit: GET /reports/financial or /reports/operational
    ↓
ReportController resolveReportRange():
  ├─ Parse period (last_6_months|quarter|year)
  ├─ Parse quarter & year params
  └─ Return [dateFrom, dateTo, periodLabel]
    ↓
Execute Database Queries:
  ├─ Join multiple tables
  ├─ Filter by date range
  ├─ Group & aggregate
  └─ Sort & limit (Top 5/10)
    ↓
Check for export param?
    ├─ Yes: Call ExportService->download()
    │   ├─ Format data into sheets
    │   ├─ Add branding & headers
    │   └─ Return file for download
    │
    └─ No: Pass data to view (Blade template)
       ├─ Render cards with summary metrics
       ├─ Initialize Chart.js
       ├─ Render tables with detail data
       └─ Display in browser
```

### **SETTINGS UPDATE WORKFLOW**

```
Form Submit: settings/index tabs
    ↓
POST /settings (SettingController@update)
    ↓
Update Profile (ProfileController@update)?
  ├─ name, email, theme_color, timezone
  ├─ date_format, is_dark_mode
  └─ password (if filled)
    ↓
Update System Parameters?
  ├─ system.usd_rate
  ├─ system.vat_percent
  ├─ system.fuel_limit
  ├─ system.toll_limit
  └─ system.overage_alert
    ↓
Update Company Settings?
  ├─ company.name
  ├─ company.address
  ├─ company.phone
  └─ company.email
    ↓
ActivityLog::log('update_settings', ...)
    ↓
Redirect with success message
    ↓
Flash session('active_tab') to stay on same tab
```

### **DATABASE BACKUP/RESTORE WORKFLOW**

```
Backup:
  GET /settings/backup
    ↓
  Find mysqldump.exe (XAMPP or PATH)
    ↓
  Execute: mysqldump -h host -u user -p pass db > file.sql
    ↓
  Check exit code
    ├─ 0 = Success: Download file, log audit, delete temp
    └─ ≠0 = Error: Log error, show message

Restore:
  POST /settings/restore
    ↓
  Validate: file.sql + admin password
    ↓
  Verify password: Hash::check($input, auth()->user()->password)
    ↓ (Failed)
    ↓ Abort with error
    ↓
  Upload file to storage/restore_tmp/
    ↓
  Execute: mysql -h host -u user -p pass db < file.sql
    ↓
  Check exit code & log result
    ↓
  Clean up temp file
    ↓
  Redirect with status message
```

---

## IV. DATABASE RELATIONSHIPS

### **Users & Roles**

```
users (1) ──→ (M) roles
  ├─ user.role_id → role.id
  ├─ user.name
  ├─ user.email (unique)
  ├─ user.employee_code (unique, auto-generated)
  ├─ user.password (hashed)
  ├─ user.department
  ├─ user.position
  ├─ user.date_of_birth
  └─ user.joined_at

roles:
  ├─ role.id
  ├─ role.role_name (VD: "Quản trị viên")
  ├─ role.role_code (VD: "ADMIN")
  └─ role.permissions (relationship)
```

### **Reports & Models**

```
ReportController uses:
  ├─ ShippingJob (status, created_at)
  ├─ DispatchOrder (vehicle_id, driver_id, status, dates)
  ├─ Driver (full_name, id)
  ├─ Vehicle (plate_number, id)
  ├─ DebitNote (customer_id, grand_total, issued_at, status)
  ├─ Expense (amount, status, created_at)
  └─ RecurringExpense (amount, cycle, status)
```

---

## V. KEY BUSINESS LOGIC PATTERNS

### **1. Cascading Dropdown (Department → Position)**

```javascript
Map: Department → [Positions]
{
  'Kinh doanh': ['Trưởng phòng', 'Nhân viên'],
  'Vận hành': ['TL vận hành', 'Nhân viên'],
  'Tài xế': ['Tài xế'],
  'Kỹ thuật': ['Quản lý', 'Kỹ sư']
}

Logic: On department change → Load valid positions
```

### **2. Role-Based Access Control (RBAC)**

```
DISPATCH Role:
  ├─ Can view only: DRIVER, FIELD users
  ├─ Cannot: view/create ADMIN, ACCOUNTANT users
  └─ Security: Check role.role_code on each operation

ADMIN Role:
  ├─ Full access to all modules
  ├─ Can access all system settings
  └─ Can manage all users
```

### **3. Employee Code Auto-Generation**

```
Format: EMP-YYMMDD-NNNNN
Example: EMP-240527-00001

Logic:
  ├─ Extract current date (2024-05-27)
  ├─ Get last employee_code from DB
  ├─ Increment counter
  └─ Format: EMP + YYMMDD + 5-digit counter
```

### **4. Financial Metrics Calculation**

```
Revenue Stream:
  └─ DebitNote.grand_total (from issued invoices)

Cost Stream:
  ├─ Expense.amount (variable costs, must be approved)
  ├─ RecurringExpense.amount (fixed monthly costs)

Profit Calculation:
  └─ Revenue - Variable Costs - Recurring Costs
```

### **5. Period-Based Reporting**

```
Last 6 Months:
  └─ FROM: NOW() - 6 MONTHS TO: NOW()

Quarter:
  ├─ Q1: Jan 1 - Mar 31
  ├─ Q2: Apr 1 - Jun 30
  ├─ Q3: Jul 1 - Sep 30
  └─ Q4: Oct 1 - Dec 31

Year:
  └─ Jan 1 - Dec 31
```

---

## VI. SECURITY CONSIDERATIONS

| Module | Security Layer | Implementation |
|--------|--------------|-----------------|
| **Settings** | Admin-only tabs | `@if($isAdmin)` in Blade |
| **Users** | RBAC per operation | `hasRole()` check in controller |
| **Backup** | Admin password verify | `Hash::check()` before restore |
| **Exports** | Max 10,000 records | `limit(10000)` in query |
| **Database** | Parameterized queries | PDO prepared statements |
| **Passwords** | Bcrypt hashing | `Hash::make()` in controller |

---

## VII. TÓNG KHOÁT KIẾN TRÚC

```
┌─────────────────────────────────────────────────────────┐
│                   BLADE TEMPLATES                       │
│  (settings/index | users/index/create/edit | reports)  │
└────────────────┬────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────┐
│              CONTROLLERS                                │
│  ProfileController | SettingController | UserController│
│              ReportController                           │
└────────────────┬────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────┐
│              SERVICES                                   │
│           ExportService                                 │
└────────────────┬────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────┐
│              MODELS                                     │
│  User | Role | Setting | ShippingJob | DispatchOrder   │
│  Driver | Vehicle | DebitNote | Expense | Recurring... │
└────────────────┬────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────┐
│          DATABASE (MySQL)                               │
│  users | roles | settings | shipping_jobs |            │
│  dispatch_orders | drivers | vehicles | debit_notes    │
└─────────────────────────────────────────────────────────┘
```

---

## VIII. BẢNG SO SÁNH CÁC MODULE

| Tiêu chí | Settings | Users | Reports |
|---------|----------|-------|---------|
| **Chức năng chính** | Cấu hình hệ thống | CRUD nhân viên | Phân tích dữ liệu |
| **Quyền truy cập** | Admin > Tất cả | Admin > Tất cả | Tất cả |
| **Dữ liệu chính** | Settings, Profile | Users, Roles | Jobs, Drivers, Finance |
| **Export** | DB backup (.sql) | CSV/XLSX/DOCX | CSV/XLSX/DOCX/PDF |
| **Charts** | Không | Không | Chart.js (2 loại) |
| **Validation** | Email, password | Email, department-position | Date range, period |
| **Audit Log** | Tất cả thay đổi | Create/update/delete | Không (read-only) |

---

**Generated:** 27/05/2026 | **Application:** NT Logistics ERP | **Version:** Laravel 12
