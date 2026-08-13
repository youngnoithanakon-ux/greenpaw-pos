---
name: greenpaw-laravel
description: ให้ข้อมูลและรายละเอียดเชิงลึกเกี่ยวกับระบบ GreenPaw Laravel Point of Sale (POS) และระบบจัดการสต็อกสินค้า รวมถึงสถาปัตยกรรม ฟีเจอร์ โครงสร้างฐานข้อมูล และ Stack ที่ใช้
---

# ข้อมูลระบบ GreenPaw Laravel (System Overview & Guidelines)

## 1. สถาปัตยกรรมและเทคโนโลยี (System Architecture & Tech Stack)
- **Framework**: Laravel 13.x
- **PHP Version**: PHP 8.3 (ทดสอบแล้วว่าสามารถทำงานเข้ากันได้กับ PHP 8.5)
- **Frontend**: Blade Templates, TailwindCSS, Alpine.js, Vite
- **Database**: MySQL/MariaDB

## 2. โมดูลและฟีเจอร์หลัก (Core Features & Modules)
- **ระบบขายหน้าร้าน (Point of Sale - POS)**: 
  - เข้าถึงได้ที่ `/pos`
  - มีระบบตะกร้าสินค้าแบบ Real-time โดยใช้ `session('cart')`
  - มีช่องกรอกเงินสดรับ (Cash Input) และคำนวณเงินทอน (Change Calculator) แบบ Real-time ด้วย JavaScript (`calcChange()`)
  - เมื่อชำระเงินสำเร็จ จะสร้างใบเสร็จรับเงิน (Receipt) รูปแบบกระดาษเทอร์มอล พร้อม QR Code อ้างอิงเลขที่บิล (ใช้ไลบรารี `qrcode.js`)
- **ระบบจัดการคลังสินค้า (Inventory Management)**:
  - จัดการสินค้า (Products) และหมวดหมู่สินค้า (Categories)
  - ระบบติดตามและบันทึกประวัติการเปลี่ยนแปลงสต็อก (StockLog)
- **ระบบรายงานและวิเคราะห์ (Reports & Analytics)**:
  - หน้า Dashboard สรุปยอดขายรายวันและกราฟแนวโน้มโดยใช้ Chart.js
  - รายงานยอดขายประจำเดือน รองรับการ Export เป็นไฟล์ CSV/Excel (ระบบ Native CSV) และ PDF (ผ่าน `barryvdh/laravel-dompdf`)
  - ประวัติการขาย (Sales History) พร้อมฟังก์ชันยกเลิกบิล (Void) ซึ่งจะคืนจำนวนสินค้าเข้าสต็อกอัตโนมัติ
- **ระบบจัดการการปลูกหญ้าแมว (Plant Batches)**:
  - โมดูลเฉพาะสำหรับการบันทึกและจัดการรอบการปลูกหญ้าแมว
- **ระบบจัดการผู้ใช้งาน (User Management)**:
  - ฟังก์ชันสำหรับแอดมินในการเพิ่ม ลบ และจัดการสิทธิ์ผู้ใช้งาน (Staff/Admin)

## 3. โครงสร้างไฟล์ที่สำคัญ (Key Directories & Files)
- **Controllers** (`app/Http/Controllers/`):
  - `PosController.php`: จัดการหน้า POS, การเพิ่ม/ลดสินค้าในตะกร้า, กระบวนการ Checkout (ทำผ่าน DB Transaction) และการสร้างใบเสร็จ
  - `SaleController.php`: จัดการหน้าประวัติการขาย, การยกเลิกบิล (Void) และการดึงข้อมูล Export เป็น CSV
  - `ReportController.php`: ดูแลรายงานยอดขายรายเดือน, ข้อมูลกราฟ, และการ Export (CSV/PDF)
  - `DashboardController.php`: ดึงข้อมูลสถิติภาพรวมสำหรับหน้า Dashboard
- **Models** (`app/Models/`):
  - `Sale`: บันทึกข้อมูลบิลขายที่สำเร็จ มีคอลัมน์สำคัญเช่น `total_amount`, `received_amount`, `change`, `status` เป็นต้น
  - `SaleItem`: รายละเอียดรายการสินค้าแต่ละชิ้นภายในบิล
  - `Product`, `Category`, `StockLog`, `PlantBatch`, `User`
- **Views** (`resources/views/`):
  - `pos/index.blade.php`: อินเทอร์เฟซหลักของระบบ POS
  - `pos/receipt.blade.php`: เทมเพลตใบเสร็จความร้อน (Thermal) ใช้ `@media print` พร้อมแสดง QR Code
  - `admin/reports/index.blade.php`: หน้าจอรายงานประจำเดือน พร้อมปุ่ม Export
- **Exports** (`app/Exports/`):
  - `SalesReportExport.php`, `SalesHistoryExport.php`: คลาสจัดการการสร้าง Native CSV

## 4. โครงสร้างฐานข้อมูลที่ควรระวัง (Database Schema Notables)
- **ตาราง sales**: ได้มีการเพิ่มคอลัมน์ `received_amount` (เงินสดที่รับจากลูกค้า) ผ่าน Migration `2026_08_04_000001_add_received_amount_to_sales_table.php` หากติดตั้งระบบใหม่ ต้องตรวจสอบว่ารัน Migration ครบ
- **สถานะบิล (status)**: บิลขายจะมีสถานะเป็น `normal` (ปกติ) หรือ `voided` (ถูกยกเลิก) เมื่อยกเลิกบิล สินค้าในบิลนั้นจะถูกเพิ่มกลับเข้าสต็อก (คืนสต็อก)

## 5. แนวทางในการพัฒนา (Development Guidelines & Rules)
1. **การทำ Export ข้อมูล (Excel/CSV)**:
   - **ห้าม**ใช้ไลบรารี `maatwebsite/excel` เด็ดขาด เนื่องจากมีปัญหา Incompatibility กับ PHP 8.5/Laravel 13
   - **แนวทางแก้ปัญหา**: ให้ใช้การสร้าง CSV แบบ Native ผ่าน `Symfony\Component\HttpFoundation\StreamedResponse` แทน (ดูตัวอย่างใน `App\Exports`)
2. **การทำ Export PDF**:
   - ใช้ไลบรารี `barryvdh/laravel-dompdf` สำหรับการสร้างไฟล์ PDF
   - ออกแบบ View แยกเฉพาะสำหรับการพิมพ์ PDF เช่น `resources/views/exports/`
3. **การสร้าง QR Code และ Barcode**:
   - เพื่อหลีกเลี่ยงปัญหา Dependency ฝั่ง Server (เช่น ขาด Imagick หรือ GD) ให้สร้าง QR Code ด้วย **JavaScript ฝั่ง Client (Frontend)** (เช่น ใช้ `qrcode.js` ผ่าน CDN ในหน้า View)
4. **การคำนวณเงินใน POS (POS Logic)**:
   - ตะกร้าสินค้าในระหว่างการขายจะถูกบันทึกไว้ใน `session('cart')`
   - การ Checkout ตัดสต็อกและบันทึกบิล **ต้องทำงานภายใน `DB::transaction()` เสมอ** เพื่อป้องกันข้อมูลไม่สมบูรณ์หากเกิดข้อผิดพลาดตรงกลาง
   - การคำนวณส่วนลด, ยอดรวม, เงินทอน ควรคำนวณและตรวจสอบเงื่อนไขใหม่ที่ฝั่ง Backend ตอน Checkout อีกครั้งเสมอ (แม้ Frontend จะคำนวณแล้วก็ตาม)
5. **UI/UX Design**:
   - คงแนวคิดความเรียบง่าย ใช้ TailwindCSS ในการจัดการ Layout 
   - ระบบ POS หน้าเครื่องคิดเงิน ให้ความสำคัญกับความเร็ว (Keyboard accessibility หรือ UX แบบ Real-time ที่ไม่ต้องรีเฟรชหน้าบ่อย)
6. **การทำ Migration**:
   - หากมีการแก้ไข Schema หลัก (เช่น ยอดเงิน, สต็อก) ให้พิจารณาสร้าง Migration เพิ่มเติม แทนการแก้ไขไฟล์ Migration เดิมที่รันไปแล้ว เพื่อไม่ให้มีปัญหากับ Production Database
