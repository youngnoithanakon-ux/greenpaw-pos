---
inclusion: auto
---

# GreenPaw Systems — Laravel Project Reference

ระบบ POS และจัดการร้านค้าหญ้าแมว ย้ายจาก Vanilla PHP → Laravel 11
โปรเจกต์อยู่ที่ `C:\www\GreenPaw_Laravel`

---

## Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13 / PHP 8.5 |
| Frontend | Blade + Vanilla CSS (Dark/Green theme) + Chart.js |
| Database | MySQL 8.x — `greenpaw_db` |
| Auth | Laravel Breeze (username-based, ไม่ใช้ email) |
| PDF Export | barryvdh/laravel-dompdf |
| LINE Notify | LINE Messaging API (push message) |
| Assets | Vite + TailwindCSS (minimal use) |

---

## Database Schema

### `users`
| Column | Type | Note |
|---|---|---|
| id | int PK | |
| username | varchar(50) | ใช้ login แทน email |
| password | varchar(255) | bcrypt |
| fullname | varchar(100) | |
| role | enum(admin,staff) | |
| remember_token | varchar(100) | |

### `categories`
| Column | Type |
|---|---|
| id | int PK |
| category_name | varchar(100) |
> ไม่มี timestamps (`public $timestamps = false`)

### `products`
| Column | Type | Note |
|---|---|---|
| id | int PK | |
| category_id | int nullable | FK → categories |
| product_name | varchar(255) | |
| image_path | varchar(255) nullable | เก็บแค่ชื่อไฟล์ ไม่ใช่ full path |
| product_type | enum(produced,bought) | |
| cost_price | decimal(10,2) | คำนวณอัตโนมัติจาก seed/soil/pot/labor+waste |
| sale_price | decimal(10,2) | |
| consignment_fee | decimal(10,2) | ค่าฝากขายต่อหน่วย |
| stock_qty | int | |
| seed_cost | decimal(10,2) | เฉพาะ produced |
| soil_cost | decimal(10,2) | |
| pot_cost | decimal(10,2) | |
| labor_cost | decimal(10,2) | |
| waste_percent | decimal(10,2) | |
| status | boolean | 1=active, 0=inactive |

**cost_price formula (produced):**
```
cost_price = (seed + soil + pot + labor) × (1 + waste% / 100)
```

### `sales`
| Column | Type | Note |
|---|---|---|
| id | int PK | |
| user_id | int | FK → users |
| sale_date | timestamp | useCurrent() default |
| total_amount | decimal(10,2) | |
| total_cost | decimal(10,2) | |
| total_consignment_fee | decimal(10,2) | |
| received_amount | decimal(10,2) nullable | เงินสดที่รับมา |
| status | enum(normal,void) | |
> ไม่มี Laravel timestamps (`public $timestamps = false`)

**เงินทอน** = `received_amount - total_amount`

### `sale_items`
| Column | Type |
|---|---|
| id | int PK |
| sale_id | int |
| product_id | int |
| qty | decimal(10,2) |
| unit_price | decimal(10,2) |
| unit_cost | decimal(10,2) |
| consignment_fee | decimal(10,2) |

### `stock_logs`
| Column | Type | Note |
|---|---|---|
| id | int PK | |
| product_id | int | |
| user_id | int | |
| type | enum(in,out) | |
| qty_before | decimal(10,2) | |
| qty_change | decimal(10,2) | |
| qty_after | decimal(10,2) | |
| note | varchar(255) nullable | |
| created_at | timestamp | useCurrent() |

### `plant_batches`
| Column | Type | Note |
|---|---|---|
| id | int PK | |
| product_id | int | FK → products (produced only) |
| created_by | int | FK → users |
| plant_date | date | วันที่เริ่มเพาะ |
| expected_harvest_date | date | |
| actual_harvest_date | date nullable | |
| qty_planted | decimal(10,2) | จำนวนกระถาง |
| qty_harvested | decimal(10,2) nullable | |
| status | enum(growing,ready,harvested,failed) | |
| notes | text nullable | |
| harvest_stock_log_id | int nullable | FK → stock_logs |

**Status transitions:**
- `growing` → `ready` (auto เมื่อถึงวันเก็บ)
- `ready` → `harvested` (manual กดปุ่ม)
- `growing/ready` → `failed` (manual)

### `system_configs`
| Column | Type | Note |
|---|---|---|
| id | int PK | |
| key | varchar(100) unique | |
| value | text nullable | |
| label | varchar(255) nullable | |
| group | varchar(50) | general/line/stock |

**Config keys ที่ใช้:**
- `store_name` — ชื่อร้าน
- `line_notify_enabled` — 0/1
- `line_channel_token` — LINE Messaging API token
- `line_target_id` — User ID (U...) หรือ Group ID (C...)
- `stock_alert_threshold` — จำนวน int แจ้งเตือนสต็อกต่ำ

---

## Models & Relationships

```
User
  hasMany → Sale (user_id)
  hasMany → StockLog (user_id)
  hasMany → PlantBatch (created_by)

Category
  hasMany → Product (category_id)
  $timestamps = false

Product
  belongsTo → Category
  hasMany → SaleItem
  hasMany → StockLog
  hasMany → PlantBatch

Sale
  belongsTo → User
  hasMany → SaleItem (items())
  $timestamps = false

SaleItem
  belongsTo → Sale
  belongsTo → Product

StockLog
  belongsTo → Product
  belongsTo → User

PlantBatch
  belongsTo → Product
  belongsTo → User (creator, FK: created_by)
  belongsTo → StockLog (harvest_stock_log_id)
  accessors: days_until_harvest, status_label, status_color

SystemConfig
  static get(key, default)
  static set(key, value, label, group)
  static many([keys])  → ['key' => value]
  Cache TTL: 600 วินาที (10 นาที)
```

---

## Auth

- **Login field:** `username` (ไม่ใช้ email)
- `Auth::attempt(['username' => ..., 'password' => ...])` — ทำงานได้เองโดยไม่ต้อง override
- `Auth::id()` คืน integer `id` (ไม่ได้ override `getAuthIdentifierName`)
- Roles: `admin`, `staff`
- **AdminMiddleware** (`app/Http/Middleware/AdminMiddleware.php`) — เช็ค `Auth::user()->role === 'admin'`
- Alias: `'admin'` ใน `bootstrap/app.php`

---

## Routes Summary

```
GET  /                    home          HomeController@index
GET  /pos                 pos.index     PosController@index
POST /pos/add             pos.add       PosController@addToCart
GET  /pos/remove/{id}     pos.remove    PosController@removeFromCart
POST /pos/checkout        pos.checkout  PosController@checkout
GET  /pos/receipt/{sale}  pos.receipt   PosController@receipt
GET  /dashboard           dashboard     DashboardController@index
GET  /profile             profile.edit  ProfileController@edit

--- Admin prefix: /admin (middleware: auth, admin) ---
GET|POST /admin/products          admin.products.*
GET|POST /admin/categories        admin.categories.*
GET|POST /admin/users             admin.users.*
GET      /admin/sales             admin.sales.index
GET      /admin/sales/export-excel admin.sales.export-excel
GET      /admin/sales/{id}/items  admin.sales.items  (JSON)
POST     /admin/sales/{id}/void   admin.sales.void
GET|POST /admin/stock             admin.stock.index/store
GET      /admin/stock/log         admin.stock.log
GET      /admin/reports           admin.reports.index
GET      /admin/reports/export-excel admin.reports.export-excel
GET      /admin/reports/export-pdf   admin.reports.export-pdf
GET      /admin/plant-batches     admin.plant-batches.index
GET      /admin/plant-batches/create  admin.plant-batches.create
POST     /admin/plant-batches     admin.plant-batches.store
GET      /admin/plant-batches/{id}    admin.plant-batches.show
POST     /admin/plant-batches/{id}/harvest admin.plant-batches.harvest
POST     /admin/plant-batches/{id}/fail    admin.plant-batches.fail
GET|POST /admin/settings          admin.settings.index/update
POST     /admin/settings/test-line admin.settings.test-line
```

---

## Controllers

| Controller | หน้าที่หลัก |
|---|---|
| HomeController | หน้าหลัก + low stock alert |
| PosController | index, addToCart, removeFromCart, checkout (บันทึก received_amount), receipt |
| DashboardController | ยอดวันนี้ + กราฟ 7 วัน + top sellers |
| ProductController | CRUD + image upload (PHP `move()`) + toggleStatus |
| CategoryController | index, store, destroy |
| StockController | index (เติมสต็อก), store, log |
| SaleController | index (filter วันที่), items (JSON), void, exportExcel |
| ReportController | index (กราฟ), exportExcel (CSV), exportPdf (dompdf) |
| UserController | CRUD + ป้องกันลบตัวเอง |
| PlantBatchController | index (auto status), create, store, show, harvest, fail |
| SystemConfigController | index, update, testLine |

---

## Services & Exports

### `App\Services\LineMessagingService`
- **API:** `POST https://api.line.me/v2/bot/message/push`
- `send(string $message): bool` — ใช้ config จาก SystemConfig
- `test(string $token, string $targetId): bool` — ทดสอบโดยตรง

**Trigger points:**
1. `PlantBatchController@store` — เมื่อ status = ready ทันที
2. `PlantBatchController@index` — auto growing→ready พร้อมแจ้ง LINE
3. `PlantBatchController@harvest` — เก็บเกี่ยวสำเร็จ + เตือนสต็อกต่ำ

### `App\Exports\SalesHistoryExport`
- `exportCsv(startDate, endDate): StreamedResponse`
- UTF-8 BOM, แสดงรายการสินค้าแยกแถวต่อ item

### `App\Exports\SalesReportExport`
- `exportCsv(month, year): StreamedResponse`
- สรุปรวม + รายวัน

---

## Views Structure

```
resources/views/
├── layouts/
│   └── app.blade.php         ← Dark/Green navbar + Backend dropdown + flash messages
├── home/index.blade.php       ← Menu หลัก + low stock alert
├── dashboard/index.blade.php  ← Stats cards + Chart.js 7 วัน
├── pos/
│   ├── index.blade.php        ← Product grid + Cart + เงินทอน calculator
│   └── receipt.blade.php      ← ใบเสร็จ (print-ready)
├── admin/
│   ├── products/
│   │   ├── index.blade.php
│   │   └── form.blade.php     ← Create/Edit shared, image preview, cost calc JS
│   ├── categories/index.blade.php
│   ├── stock/
│   │   ├── index.blade.php
│   │   └── log.blade.php
│   ├── sales/index.blade.php  ← filter, summary cards, AJAX modal, export
│   ├── reports/index.blade.php ← Chart.js + export Excel/PDF
│   ├── users/index.blade.php
│   ├── plant-batches/
│   │   ├── index.blade.php    ← stat cards, countdown วัน, filter
│   │   ├── create.blade.php   ← date picker, duration calc JS
│   │   └── show.blade.php     ← detail + harvest form + fail form
│   └── settings/index.blade.php ← LINE token, toggle, test form
├── exports/
│   └── sales-report-pdf.blade.php ← dompdf template (landscape A4)
└── profile/edit.blade.php
```

---

## Image Upload Pattern

```php
// ProductController::handleImageUpload()
$directory = storage_path('app/public/products');
$file->move($directory, $filename);  // PHP native move (ไม่ใช้ storeAs)

// URL ใน Blade:
asset('storage/products/' . $product->image_path)

// ต้องรัน: php artisan storage:link
```

---

## POS Flow

```
1. User เพิ่มสินค้าลงตะกร้า (session 'cart')
   cart = [ product_id => [name, price, cost, consignment, qty] ]

2. กรอกเงินสดที่รับมา → JS คำนวณเงินทอน

3. POST /pos/checkout
   - สร้าง Sale (received_amount บันทึกด้วย)
   - สร้าง SaleItem ต่อรายการ
   - Product::decrement('stock_qty', qty)
   - สร้าง StockLog (type: out)
   - session()->forget('cart')
   - redirect → /pos/receipt/{sale_id}

4. หน้า Receipt แสดงใบเสร็จ + ปุ่มพิมพ์
```

---

## Harvest Flow

```
1. บันทึกรอบปลูก → PlantBatch::create(status: growing/ready)
2. เมื่อ expected_harvest_date ถึง → auto growing→ready (ตอนโหลดหน้า index/show)
3. LINE แจ้งเตือน "พร้อมเก็บเกี่ยว"
4. กด "เก็บเกี่ยว" ใน show.blade.php
   - Product::increment('stock_qty', qty_harvested)
   - StockLog::create(type: in)
   - PlantBatch::update(status: harvested, harvest_stock_log_id)
   - LINE แจ้ง "เก็บเกี่ยวสำเร็จ" + เตือนถ้าสต็อก <= threshold
```

---

## Seeded Accounts

| Username | Password | Role |
|---|---|---|
| admin | password | admin |
| staff01 | password | staff |
| dee | password | staff |

---

## Key Conventions

- **Auth:** `username` ไม่ใช้ `email`, ไม่มี email verification
- **Timestamps:** `sales` และ `categories` ปิด timestamps — ใช้ `sale_date` และ `useCurrent()` เอง
- **qty fields:** ทุกที่ใช้ `decimal(10,2)` ไม่ใช้ `integer` (รองรับสินค้าขายเป็นกิโล/ชุด)
- **Image:** เก็บแค่ชื่อไฟล์ใน DB ไม่ใช่ full path
- **Soft delete:** ไม่มี — ใช้ `status=0` (inactive) สำหรับสินค้าแทน
- **Export CSV:** UTF-8 BOM (`\xEF\xBB\xBF`) เพื่อให้ Excel อ่านภาษาไทยได้
- **LINE:** ใช้ Messaging API push (ไม่ใช้ LINE Notify ที่ปิดบริการแล้ว)
- **Route prefix:** admin routes ทั้งหมดอยู่ภายใต้ `prefix('admin')->name('admin.')`

---

## ไฟล์สำคัญ

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── PosController.php
│   │   ├── SaleController.php
│   │   ├── ProductController.php
│   │   ├── PlantBatchController.php
│   │   ├── ReportController.php
│   │   └── SystemConfigController.php
│   └── Middleware/AdminMiddleware.php
├── Models/
│   ├── User.php, Product.php, Sale.php
│   ├── SaleItem.php, StockLog.php
│   ├── PlantBatch.php, SystemConfig.php
│   └── Category.php
├── Services/LineMessagingService.php
└── Exports/
    ├── SalesHistoryExport.php
    └── SalesReportExport.php

routes/web.php
routes/auth.php
database/migrations/  (8 migration files)
resources/views/      (ดู Views Structure ด้านบน)
.env                  (DB, APP_KEY, TIMEZONE — ลบ MAIL/QUEUE/BROADCAST ออกแล้ว)
```

---

## สิ่งที่ยังไม่ได้ทำ (Feature Backlog)

- [ ] Activity Log (ใครแก้ไขอะไร เวลาไหน)
- [ ] Backup DB ผ่าน UI (Export SQL)
- [ ] Stock Alert badge บน navbar (real-time)
- [ ] Gantt-style chart รอบปลูก
- [ ] ระบบส่วนลด / Promotion ใน POS
- [ ] Multi-image สินค้า
