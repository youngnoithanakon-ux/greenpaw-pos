# GreenPaw Systems — Migrate to Laravel 11

## ภาพรวม

ย้ายระบบ POS และจัดการร้านค้า **GreenPaw Systems** จาก Vanilla PHP ไปยัง **Laravel 11**
โดยจะสร้างโปรเจกต์ใหม่ที่ `C:\www\GreenPaw_Laravel` ควบคู่กับโปรเจกต์เดิม (ไม่ลบของเดิม)

---

## สิ่งที่ต้องรีวิว (User Review Required)

> [!IMPORTANT]
> **โปรเจกต์ใหม่จะถูกสร้างที่ `C:\www\GreenPaw_Laravel`** — โปรเจกต์เดิมจะยังอยู่ครบถ้วน ไม่มีการลบ

> [!WARNING]
> **ต้องติดตั้ง Composer** ก่อนรัน Laravel ได้ หากยังไม่มี จะต้องดาวน์โหลดที่ https://getcomposer.org/

> [!NOTE]
> Database `greenpaw_db` เดิมจะถูกนำมาใช้ต่อเลย **ไม่ต้องกรอกข้อมูลใหม่** — Laravel Migrations จะ sync schema ให้

---

## Database Schema ที่จะย้าย

จาก Vanilla PHP, ระบบมีตาราง:
- `users` — ผู้ใช้งาน (username, password hash, fullname, role: admin/staff)
- `categories` — หมวดหมู่สินค้า
- `products` — สินค้า (ผลิตเอง/ซื้อมา, ราคาทุน, ราคาขาย, ค่าฝาก, รูปภาพ, สต็อก)
- `sales` — บิลขาย (status: normal/void)
- `sale_items` — รายการสินค้าในบิล
- `stock_logs` — ประวัติการเคลื่อนไหวสต็อก

---

## Proposed Changes

### Component 1: Laravel Project Setup

#### [NEW] Laravel 11 Project — `C:\www\GreenPaw_Laravel\`
- ติดตั้งผ่าน `composer create-project laravel/laravel`
- ติดตั้ง **Laravel Breeze** สำหรับ Auth (Login/Logout/Session)
- ติดตั้ง **Spatie Laravel-Permission** สำหรับ Role-based Access Control (admin/staff)
- กำหนดค่า `.env` เชื่อมต่อ MySQL `greenpaw_db`

---

### Component 2: Database Layer

#### [NEW] Migrations (แทน schema เดิม)
- `create_users_table` — เพิ่มคอลัมน์ `fullname`, `role`
- `create_categories_table`
- `create_products_table` — ทุก field จาก vanilla รวม `image_path`, `product_type`, cost breakdowns
- `create_sales_table` — รวม `status` enum (normal/void)
- `create_sale_items_table`
- `create_stock_logs_table`

#### [NEW] Eloquent Models
- `User` — hasMany Sales, hasMany StockLogs
- `Category` — hasMany Products
- `Product` — belongsTo Category, hasMany SaleItems, hasMany StockLogs
- `Sale` — belongsTo User, hasMany SaleItems
- `SaleItem` — belongsTo Sale, belongsTo Product
- `StockLog` — belongsTo Product, belongsTo User

---

### Component 3: Authentication & Middleware

#### [NEW] Auth via Laravel Breeze
- หน้า Login ที่ปลอดภัย (CSRF protection ในตัว)
- Session management แบบ Laravel standard

#### [NEW] Middleware
- `AdminMiddleware` — บล็อก route ที่เป็น admin-only อัตโนมัติ
- ไม่ต้องเขียน `if (!$_SESSION['role'] !== 'admin')` ในทุกไฟล์อีกต่อไป

---

### Component 4: Controllers

#### [NEW] Controllers (แทนไฟล์ PHP เดิม)
| Controller | แทนไฟล์เดิม | Features |
|---|---|---|
| `HomeController` | `index.php` | Dashboard หลัก, Low stock alert |
| `PosController` | `sales/pos.php` | Cart (Session), Add/Remove, Checkout |
| `DashboardController` | `sales/dashboard.php` | ยอดวันนี้, กราฟ 7 วัน, Top sellers |
| `ProductController` | `admin/product_list.php` + `product_add.php` | CRUD + Image upload |
| `CategoryController` | `admin/categories.php` | CRUD |
| `StockController` | `admin/stock_update.php` + `stock_log.php` | เติมสต็อก, ประวัติ |
| `SaleController` | `admin/sale_details.php` + `void_bill.php` | ประวัติบิล, Void (แก้บั๊กแล้ว) |
| `ReportController` | `admin/reports.php` | รายงานรายเดือน + Chart.js |
| `UserController` | `admin/users.php` | CRUD พนักงาน |

---

### Component 5: Routes

#### [NEW] `routes/web.php`
```
Public:
  GET  /login          → AuthController
  POST /login

Auth (all logged-in users):
  GET  /               → HomeController@index
  GET  /pos            → PosController@index
  POST /pos/add        → PosController@addToCart
  POST /pos/checkout   → PosController@checkout
  GET  /pos/remove     → PosController@removeFromCart
  GET  /dashboard      → DashboardController@index

Admin only:
  Resource /admin/products     → ProductController
  Resource /admin/categories   → CategoryController
  Resource /admin/users        → UserController
  Resource /admin/sales        → SaleController (void included)
  GET      /admin/stock        → StockController@index
  POST     /admin/stock        → StockController@update
  GET      /admin/stock/log    → StockController@log
  GET      /admin/reports      → ReportController@index
```

---

### Component 6: Views (Blade Templates)

#### [NEW] Layout: `layouts/app.blade.php`
- Navbar Responsive รองรับ mobile (เหมือนเดิม แต่ Blade syntax)
- Alpine.js สำหรับ Dropdown Toggle (แทน vanilla JS)

#### [NEW] Views สำหรับแต่ละหน้า (Blade Templates)
- แบบ Green/Dark theme เหมือนเดิม
- ใช้ CSRF token `@csrf` ในทุก Form

---

## Verification Plan

### Automated
- `php artisan migrate` — ตรวจสอบว่า Migration สำเร็จ ไม่มี Error
- `php artisan route:list` — ตรวจสอบว่า Routes ถูกต้องครบถ้วน
- `php artisan serve` — รันระบบทดสอบ

### Manual Verification
- ทดสอบ Login/Logout ด้วย user ทั้ง 2 role (admin, staff)
- ทดสอบ POS: เพิ่มสินค้า → Checkout → ตรวจสต็อกลด
- ทดสอบ Void Bill: Void บิลเดิมซ้ำ → ต้องขึ้น Error (บั๊กเดิมได้รับการแก้ไข)
- ทดสอบว่า Staff ไม่สามารถเข้า `/admin/*` ได้
