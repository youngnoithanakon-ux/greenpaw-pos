---
inclusion: manual
---

# GreenPaw Systems — Test Cases

ครอบคลุมทุก module: Auth, POS, Dashboard, Products, Categories, Stock, Sales, Reports, Plant Batches, Users, Settings

---

## วิธีใช้ไฟล์นี้

- ✅ = ผ่าน | ❌ = ไม่ผ่าน | ⏭ = ข้าม/ไม่เกี่ยว
- คอลัมน์ **Expected** = ผลที่ควรได้
- ทดสอบบน `php artisan serve` → `http://127.0.0.1:8000`
- Seeded accounts: `admin/password` (admin), `staff01/password` (staff)

---

## TC-01: Authentication

| ID | Test Case | Steps | Expected | Result |
|---|---|---|---|---|
| TC-01-01 | Login ด้วย username ถูกต้อง (admin) | ไปที่ `/login` → username: `admin`, password: `password` → กด Login | Redirect ไป `/` แสดงหน้า Home เมนูครบทุกรายการ | |
| TC-01-02 | Login ด้วย username ถูกต้อง (staff) | username: `staff01`, password: `password` | Redirect ไป `/` เห็นแค่เมนู POS, Dashboard, โปรไฟล์ | |
| TC-01-03 | Login ด้วย password ผิด | username: `admin`, password: `wrongpass` | อยู่หน้า `/login` แสดง error "รหัสผ่านไม่ถูกต้อง" | |
| TC-01-04 | Login ด้วย username ไม่มีในระบบ | username: `ghost`, password: `password` | แสดง error ไม่ redirect | |
| TC-01-05 | เข้าหน้า protected โดยไม่ login | เปิด `/pos` โดยตรงโดยไม่ login | Redirect ไปหน้า `/login` | |
| TC-01-06 | Staff เข้า admin route | Login staff01 → เปิด `/admin/products` | 403 Access Denied | |
| TC-01-07 | Logout | คลิก "ออกจากระบบ" ใน dropdown | Redirect ไป `/login` session ถูกลบ | |
| TC-01-08 | Remember me | Login + ติ๊ก "จดจำฉัน" → ปิด browser → เปิดใหม่ | ยังอยู่ใน session | |

---

## TC-02: POS — เพิ่มสินค้าลงตะกร้า

| ID | Test Case | Steps | Expected | Result |
|---|---|---|---|---|
| TC-02-01 | เพิ่มสินค้าที่มีสต็อก | POS → เลือกสินค้า qty=1 → กด "+ ใส่" | สินค้าปรากฏในตะกร้าขวา ราคาถูกต้อง | |
| TC-02-02 | เพิ่มสินค้าซ้ำ | เพิ่มสินค้าเดิมอีกครั้ง qty=1 | qty ในตะกร้าเพิ่มขึ้น (ไม่ขึ้น row ใหม่) | |
| TC-02-03 | เพิ่มเกินสต็อก | สินค้ามีสต็อก 3 → เพิ่ม qty=5 | แสดง error "สต็อกไม่พอ" ไม่เพิ่มลงตะกร้า | |
| TC-02-04 | เพิ่มสินค้าหมดสต็อก | สินค้ามี stock_qty=0 | ปุ่ม disabled แสดง "สินค้าหมด" | |
| TC-02-05 | ลบสินค้าออกจากตะกร้า | กด ✕ ท้ายรายการ | รายการถูกลบ ยอดรวมอัปเดต | |
| TC-02-06 | ค้นหาสินค้าใน POS | พิมพ์ชื่อในช่องค้นหา | แสดงเฉพาะสินค้าที่ตรง | |
| TC-02-07 | qty ทศนิยม | ใส่ qty=0.5 → เพิ่มสินค้า | ตะกร้าแสดง 0.5 ยอดคำนวณถูกต้อง | |

---

## TC-03: POS — เงินทอน

| ID | Test Case | Steps | Expected | Result |
|---|---|---|---|---|
| TC-03-01 | รับเงินพอดี | ยอด ฿100 → กรอกรับเงิน 100 | เงินทอน ฿0.00 แสดงสีเขียว | |
| TC-03-02 | รับเงินเกิน | ยอด ฿100 → กรอก 200 | เงินทอน ฿100.00 แสดงทันที | |
| TC-03-03 | รับเงินไม่พอ | ยอด ฿100 → กรอก 50 | แสดง "ยังขาดอีก ฿50.00" สีแดง | |
| TC-03-04 | กด checkout เงินไม่พอ | ยอด ฿100, รับ 50 → กด checkout | confirm dialog ขึ้นว่า "เงินยังไม่พอ ขาดอีก ฿50" | |
| TC-03-05 | ไม่กรอกเงินสด | ตะกร้ามีสินค้า → ไม่กรอก cash → กด checkout | confirm dialog ปกติ ปิดบิลได้ | |

---

## TC-04: POS — Checkout และ Receipt

| ID | Test Case | Steps | Expected | Result |
|---|---|---|---|---|
| TC-04-01 | Checkout สำเร็จ | เพิ่มสินค้า → กรอกเงิน → ยืนยัน | Redirect ไป `/pos/receipt/{id}` แสดงใบเสร็จ | |
| TC-04-02 | ใบเสร็จแสดงข้อมูลครบ | ดูหน้า receipt | เลขบิล, วันที่, ผู้ขาย, รายการ, ยอดรวม, เงินรับ, เงินทอน | |
| TC-04-03 | สต็อกลดหลัง checkout | ก่อน checkout สินค้า X มี 10 → checkout 2 | สต็อกสินค้า X เหลือ 8 | |
| TC-04-04 | StockLog บันทึก | หลัง checkout → ดู `/admin/stock/log` | มี log type=out ของสินค้าที่ขาย | |
| TC-04-05 | ตะกร้าว่างหลัง checkout | หลัง redirect ไป receipt → กด "ขายต่อ" | กลับ POS ตะกร้าว่าง | |
| TC-04-06 | Checkout ตะกร้าว่าง | POST `/pos/checkout` โดยไม่มีสินค้า | Redirect กลับ POS แสดง error "ตะกร้าสินค้าว่างเปล่า" | |
| TC-04-07 | ปุ่มพิมพ์ใบเสร็จ | กด "🖨️ พิมพ์ใบเสร็จ" | Print dialog ขึ้น | |

---

## TC-05: Dashboard

| ID | Test Case | Steps | Expected | Result |
|---|---|---|---|---|
| TC-05-01 | ยอดวันนี้อัปเดต | ทำ checkout 1 บิล → เปิด `/dashboard` | จำนวนบิล +1, ยอดขายเพิ่ม | |
| TC-05-02 | บิล void ไม่นับ | Void บิลที่มีอยู่ → ดู dashboard | ยอดไม่รวมบิลที่ void | |
| TC-05-03 | กราฟ 7 วัน | ดู dashboard | กราฟแสดง 7 วันย้อนหลัง แกน Y เป็นบาท | |
| TC-05-04 | Low stock panel | มีสินค้าสต็อก ≤ 5 | แสดงในตาราง "สต็อกใกล้หมด" | |
| TC-05-05 | Staff ดู dashboard | Login staff01 → ดู dashboard | เห็นยอดขาย/กราฟ แต่ไม่เห็นกำไร/ต้นทุน | |
| TC-05-06 | Admin ดู dashboard | Login admin → ดู dashboard | เห็นทุก stat card รวมกำไรสุทธิ | |

---

## TC-06: จัดการสินค้า (Admin)

| ID | Test Case | Steps | Expected | Result |
|---|---|---|---|---|
| TC-06-01 | เพิ่มสินค้าแบบผลิตเอง | `/admin/products/create` → กรอกข้อมูล → บันทึก | สินค้าปรากฏในรายการ, stock_qty=0 | |
| TC-06-02 | คำนวณต้นทุนอัตโนมัติ | ใส่ seed=10, soil=5, labor=5, waste=10% | displayCost = (10+5+5)×1.1 = ฿22.00 | |
| TC-06-03 | เพิ่มสินค้าแบบซื้อมา | เลือก product_type=bought, ใส่ fixed_cost=50 | cost_price บันทึกเป็น 50 | |
| TC-06-04 | Upload รูปภาพ | เลือกรูป jpg ขนาด < 2MB → บันทึก | รูปแสดงในตาราง, thumbnail ถูกต้อง | |
| TC-06-05 | Upload รูปเกินขนาด | เลือกรูป > 2MB | Validation error "ขนาดไฟล์เกิน 2MB" | |
| TC-06-06 | แก้ไขสินค้า | กด "แก้ไข" → เปลี่ยนราคา → บันทึก | ราคาอัปเดตในรายการ | |
| TC-06-07 | แก้ไขโดยไม่อัปโหลดรูปใหม่ | กด "แก้ไข" → แก้ชื่อ → บันทึก | รูปเดิมยังคงอยู่ ไม่หาย | |
| TC-06-08 | ปิด/เปิดสินค้า (toggleStatus) | กด toggle สถานะ | สถานะเปลี่ยน, inactive ไม่แสดงใน POS | |
| TC-06-09 | ลบสินค้า | กด "ลบ" → confirm | สินค้าเปลี่ยนเป็น inactive (ไม่ลบจริง) | |
| TC-06-10 | Filter ตามหมวดหมู่ | เลือก dropdown หมวดหมู่ | แสดงเฉพาะสินค้าในหมวดนั้น | |
| TC-06-11 | ค้นหาชื่อสินค้า | พิมพ์ในช่องค้นหา | filter realtime ไม่ reload หน้า | |

---

## TC-07: จัดการหมวดหมู่ (Admin)

| ID | Test Case | Steps | Expected | Result |
|---|---|---|---|---|
| TC-07-01 | เพิ่มหมวดหมู่ | กรอกชื่อ → กด "เพิ่มหมวดหมู่" | ปรากฏในตาราง | |
| TC-07-02 | เพิ่มชื่อซ้ำ | กรอกชื่อที่มีอยู่แล้ว | Validation error "มีชื่อนี้แล้ว" | |
| TC-07-03 | ลบหมวดหมู่ | กด "ลบ" → confirm | ลบออกจากตาราง | |
| TC-07-04 | หมวดหมู่ปรากฏใน dropdown สินค้า | เพิ่มหมวดหมู่ใหม่ → ไปหน้าเพิ่มสินค้า | หมวดใหม่ปรากฏใน dropdown | |

---

## TC-08: จัดการสต็อก (Admin)

| ID | Test Case | Steps | Expected | Result |
|---|---|---|---|---|
| TC-08-01 | เติมสต็อกสินค้า | `/admin/stock` → เลือกสินค้า, qty=10 → กด "+ เพิ่มเข้าคลัง" | stock_qty เพิ่ม +10 | |
| TC-08-02 | เติมสต็อกทศนิยม | qty=2.5 | stock_qty เพิ่ม +2.5 | |
| TC-08-03 | สินค้า inactive เติมไม่ได้ | สินค้าที่ inactive → กดปุ่ม | ปุ่ม disabled หรือ error | |
| TC-08-04 | StockLog บันทึกหลังเติม | เติมสต็อก → ดูประวัติ | log type=in ปรากฏ | |
| TC-08-05 | ดูประวัติสต็อก | กด "📜 ดูประวัติสต็อก" | แสดงตารางล่าสุด 200 รายการ in/out | |
| TC-08-06 | ค้นหาสินค้าในหน้าสต็อก | พิมพ์ชื่อ | filter realtime ทั้งชื่อและหมวดหมู่ | |

---

## TC-09: ประวัติการขาย (Admin)

| ID | Test Case | Steps | Expected | Result |
|---|---|---|---|---|
| TC-09-01 | ดูประวัติวันนี้ | `/admin/sales` | แสดงบิลวันนี้พร้อม summary cards | |
| TC-09-02 | Filter ช่วงวันที่ | เลือก start/end date → ค้นหา | แสดงเฉพาะบิลในช่วงนั้น | |
| TC-09-03 | ดูรายการในบิล (AJAX Modal) | กด "👁️ รายการ" | Modal แสดงรายการสินค้า ราคา รวม ครบถ้วน | |
| TC-09-04 | Void บิลปกติ | กด "🚫 Void" → confirm | บิลเปลี่ยนเป็น "ยกเลิกแล้ว", สต็อกคืน | |
| TC-09-05 | Void บิลที่ void แล้ว | เข้า route void บิลที่ void แล้ว | Error "บิลนี้ถูกยกเลิกไปแล้ว" ไม่ void ซ้ำ | |
| TC-09-06 | Summary ไม่นับบิล void | void บิล → ดู summary | ยอดลด, void ไม่รวมใน profit | |
| TC-09-07 | Export Excel (ประวัติ) | กด "📥 Export Excel" | ดาวน์โหลด .csv UTF-8 BOM เปิด Excel อ่านไทยได้ | |
| TC-09-08 | Export รายละเอียด item | เปิด CSV | มีแถวแยกตาม item ของแต่ละบิล | |

---

## TC-10: รายงาน (Admin)

| ID | Test Case | Steps | Expected | Result |
|---|---|---|---|---|
| TC-10-01 | ดูรายงานเดือนปัจจุบัน | `/admin/reports` | แสดง stat cards 5 ใบ + กราฟ | |
| TC-10-02 | เลือกเดือน/ปี | dropdown เดือน + ปี → ค้นหา | ตัวเลขอัปเดตตามเดือนที่เลือก | |
| TC-10-03 | รายงานเดือนไม่มีข้อมูล | เลือกเดือนที่ไม่มีการขาย | ตัวเลขเป็น 0, กราฟแสดง "ไม่พบข้อมูล" | |
| TC-10-04 | Export Excel (รายงาน) | กด "📥 Export Excel" | CSV มีสรุปรวม + รายวัน | |
| TC-10-05 | Export PDF | กด "📄 Export PDF" | ดาวน์โหลด PDF landscape A4 | |
| TC-10-06 | PDF มีข้อมูลถูกต้อง | เปิด PDF | ชื่อเดือน/ปี, stat cards, ตารางบิลทั้งหมด | |
| TC-10-07 | พิมพ์รายงาน | กด "🖨️ พิมพ์รายงาน" | Print dialog ขึ้น, ปุ่มหาย (print CSS) | |

---

## TC-11: ระบบปลูกหญ้าแมว (Admin)

| ID | Test Case | Steps | Expected | Result |
|---|---|---|---|---|
| TC-11-01 | บันทึกรอบปลูกใหม่ | `/admin/plant-batches/create` → กรอกข้อมูล → บันทึก | ปรากฏในรายการ status=growing | |
| TC-11-02 | คำนวณระยะเวลา | กรอก plant_date + expected_harvest_date | "ระยะเวลาการปลูก: X วัน" อัปเดต realtime | |
| TC-11-03 | สินค้าต้องเป็น produced เท่านั้น | dropdown แสดงสินค้า | เห็นเฉพาะ product_type=produced | |
| TC-11-04 | Auto status growing→ready | รอบปลูกที่ expected_harvest_date = วันนี้ | เมื่อโหลดหน้า status เปลี่ยนเป็น ready | |
| TC-11-05 | Countdown วันเหลือ | รอบที่ยังปลูกอยู่ | แสดง "อีก X วัน" หรือ "เลย X วัน" สีต่างกัน | |
| TC-11-06 | ดูรายละเอียดรอบปลูก | กด "📋 จัดการ" | หน้า show แสดงข้อมูลครบ + ฟอร์มเก็บเกี่ยว | |
| TC-11-07 | เก็บเกี่ยว | กรอก qty_harvested + วันที่ → ยืนยัน | status=harvested, stock เพิ่ม, StockLog บันทึก | |
| TC-11-08 | StockLog หลังเก็บเกี่ยว | ดู stock log | มี entry type=in พร้อม note อ้างอิงรอบ # | |
| TC-11-09 | บันทึกล้มเหลว | กรอก fail_notes → ยืนยัน | status=failed, สต็อกไม่เปลี่ยน | |
| TC-11-10 | Harvest/Fail บิลที่ harvested แล้ว | เข้า show ของบิลที่ harvested | ฟอร์ม harvest/fail ไม่แสดง | |
| TC-11-11 | Filter ตามสถานะ | เลือก status filter | แสดงเฉพาะรอบที่ตรง | |
| TC-11-12 | Filter ตามสินค้า | เลือก product filter | แสดงเฉพาะรอบของสินค้านั้น | |
| TC-11-13 | Stat cards นับถูกต้อง | ดูหน้า index | growing/ready/harvested/failed ตัวเลขถูกต้อง | |

---

## TC-12: จัดการพนักงาน (Admin)

| ID | Test Case | Steps | Expected | Result |
|---|---|---|---|---|
| TC-12-01 | เพิ่มพนักงานใหม่ | กรอก username/fullname/password/role=staff → บันทึก | ปรากฏในตาราง | |
| TC-12-02 | Username ซ้ำ | กรอก username ที่มีอยู่แล้ว | Validation error | |
| TC-12-03 | Login ด้วย account ใหม่ | ใช้ username/password ที่สร้าง | Login สำเร็จ role ถูกต้อง | |
| TC-12-04 | แก้ไขข้อมูล | กด "แก้ไข" → เปลี่ยน fullname/role → บันทึก | ข้อมูลอัปเดต | |
| TC-12-05 | เปลี่ยนรหัสผ่าน | กรอก password ใหม่ → บันทึก | Login ด้วยรหัสใหม่ได้ | |
| TC-12-06 | ไม่เปลี่ยนรหัสผ่าน | แก้ไข fullname เท่านั้น ช่อง password ว่าง | รหัสเดิมยังใช้ได้ | |
| TC-12-07 | ลบพนักงาน | กด "ลบ" → confirm | พนักงานถูกลบออกจากตาราง | |
| TC-12-08 | ลบตัวเอง | กดลบ account ที่กำลัง login อยู่ | Error "ไม่สามารถลบบัญชีของตัวเองได้" | |

---

## TC-13: ตั้งค่าระบบ & LINE Messaging API (Admin)

| ID | Test Case | Steps | Expected | Result |
|---|---|---|---|---|
| TC-13-01 | บันทึกชื่อร้าน | `/admin/settings` → แก้ชื่อ → บันทึก | ชื่อใหม่แสดงในข้อความ LINE | |
| TC-13-02 | เปิด LINE Notify | toggle เปิด → บันทึก | status badge เปลี่ยนเป็น "✅ เปิดใช้งาน" | |
| TC-13-03 | บันทึก Channel Token | กรอก token → บันทึก | token ถูกบันทึก (แสดงแบบซ่อนด้วย ***) | |
| TC-13-04 | บันทึก Target ID | กรอก User ID หรือ Group ID → บันทึก | ค่าถูกบันทึก | |
| TC-13-05 | ทดสอบ LINE (token+targetId ถูกต้อง) | กรอก token+targetId → กด "ส่งทดสอบ" | "✅ ส่งสำเร็จ", LINE ได้รับข้อความ | |
| TC-13-06 | ทดสอบ LINE (token ผิด) | กรอก token ผิด → ส่ง | "❌ ส่งไม่สำเร็จ" | |
| TC-13-07 | ทดสอบ LINE ไม่กรอก token | ไม่กรอก ส่ง | Error "กรุณากรอก Channel Access Token" | |
| TC-13-08 | ทดสอบ LINE ไม่กรอก targetId | กรอก token แต่ไม่กรอก targetId | Error "กรุณากรอก Target ID" | |
| TC-13-09 | ตั้งค่า stock threshold | ใส่ threshold=10 → บันทึก | ค่าถูกบันทึก (ใช้ตอน harvest) | |
| TC-13-10 | Token แสดงแบบ password | โหลดหน้า settings | token field แสดงแบบซ่อน (•••) | |
| TC-13-11 | Toggle แสดง/ซ่อน token | กด 👁️ | สลับระหว่าง plain text กับ hidden | |

---

## TC-14: Profile

| ID | Test Case | Steps | Expected | Result |
|---|---|---|---|---|
| TC-14-01 | แก้ไข username | `/profile` → เปลี่ยน username → บันทึก | username อัปเดต, navbar แสดงชื่อใหม่ | |
| TC-14-02 | แก้ไข fullname | เปลี่ยน fullname → บันทึก | fullname ใน navbar อัปเดต | |
| TC-14-03 | Username ซ้ำกับคนอื่น | ใช้ username ของคนอื่น | Validation error | |
| TC-14-04 | เปลี่ยนรหัสผ่าน | กรอก current password ถูก + new password → บันทึก | Login ด้วยรหัสใหม่ได้ | |
| TC-14-05 | current password ผิด | กรอก current password ผิด | Validation error | |
| TC-14-06 | ลบบัญชีตัวเอง | กรอก password ยืนยัน → ลบ | Logout, account ถูกลบ | |

---

## TC-15: Navigation & UI

| ID | Test Case | Steps | Expected | Result |
|---|---|---|---|---|
| TC-15-01 | Backend dropdown เปิด/ปิด | คลิก "⚙️ Backend" | Dropdown แสดง/ซ่อนเมนู admin | |
| TC-15-02 | Backend dropdown ปิดเมื่อคลิกที่อื่น | เปิด dropdown แล้วคลิก area อื่น | Dropdown ปิด | |
| TC-15-03 | Active state เมนู | อยู่หน้า `/admin/products` | เมนู "สินค้า" ใน Backend dropdown highlight | |
| TC-15-04 | Mobile hamburger | ย่อหน้าจอ < 768px → กด hamburger | Mobile menu แสดง/ซ่อน | |
| TC-15-05 | Flash success message | บันทึกข้อมูลสำเร็จ | แสดง banner สีเขียว "✅ ..." | |
| TC-15-06 | Flash error message | เกิด error | แสดง banner สีแดง "⛔ ..." | |
| TC-15-07 | Staff ไม่เห็น Backend dropdown | Login staff | Backend dropdown และเมนู admin ไม่แสดง | |

---

## TC-16: Security

| ID | Test Case | Steps | Expected | Result |
|---|---|---|---|---|
| TC-16-01 | CSRF protection | ส่ง POST โดยไม่มี _token | 419 Page Expired | |
| TC-16-02 | Staff เข้า admin URL โดยตรง | `/admin/products` | 403 Access Denied | |
| TC-16-03 | Staff ดู receipt คนอื่น | `/pos/receipt/{sale_id_ของคนอื่น}` | 403 Access Denied | |
| TC-16-04 | Guest เข้า auth route | `/pos` โดยไม่ login | Redirect `/login` | |
| TC-16-05 | SQL injection ในช่องค้นหา | ใส่ `' OR 1=1 --` | ไม่มีผลอะไร, query ปลอดภัย (Eloquent) | |

---

## TC-17: Edge Cases

| ID | Test Case | Steps | Expected | Result |
|---|---|---|---|---|
| TC-17-01 | สินค้าไม่มีรูปภาพ | เพิ่มสินค้าโดยไม่อัปโหลดรูป | แสดง placeholder icon แทน | |
| TC-17-02 | สินค้าไม่มีหมวดหมู่ | เพิ่มสินค้าโดยไม่เลือกหมวด | บันทึกได้ category_id=null | |
| TC-17-03 | Export ช่วงวันที่ไม่มีข้อมูล | Export CSV วันที่ไม่มีบิล | CSV มีแค่ header row ไม่ error | |
| TC-17-04 | Export PDF เดือนไม่มีข้อมูล | Export PDF เดือนไม่มีการขาย | PDF สร้างได้ ตัวเลขเป็น 0 | |
| TC-17-05 | Report ปีที่ไม่มีข้อมูล | ดู report เดือนที่ไม่มีข้อมูล | กราฟแสดง "ไม่พบข้อมูล" | |
| TC-17-06 | Plant batch qty_harvested > qty_planted | harvest 100 จาก batch ที่ปลูก 10 | ระบบยอมรับ (ไม่มี validation บังคับ) | |
| TC-17-07 | LINE ปิดอยู่ → harvest สำเร็จ | ปิด line_notify_enabled → harvest | harvest สำเร็จ ไม่ส่ง LINE ไม่ error | |
| TC-17-08 | รูปภาพเสีย storage:link ไม่ได้รัน | เปิดหน้าสินค้าโดยไม่มี symlink | แสดง placeholder ไม่ error 500 | |

---

## สรุปจำนวน Test Cases

| Module | จำนวน TC |
|---|---|
| TC-01 Authentication | 8 |
| TC-02 POS เพิ่มตะกร้า | 7 |
| TC-03 POS เงินทอน | 5 |
| TC-04 POS Checkout & Receipt | 7 |
| TC-05 Dashboard | 6 |
| TC-06 จัดการสินค้า | 11 |
| TC-07 หมวดหมู่ | 4 |
| TC-08 สต็อก | 6 |
| TC-09 ประวัติการขาย | 8 |
| TC-10 รายงาน | 7 |
| TC-11 ระบบปลูก | 13 |
| TC-12 จัดการพนักงาน | 8 |
| TC-13 Settings & LINE | 11 |
| TC-14 Profile | 6 |
| TC-15 Navigation & UI | 7 |
| TC-16 Security | 5 |
| TC-17 Edge Cases | 8 |
| **รวม** | **127 TC** |

---

## หมายเหตุ

- **ทดสอบ LINE จริง** ต้องมี Channel Access Token และ Target ID จริง
- **ทดสอบ PDF** ต้องมี `barryvdh/laravel-dompdf` ติดตั้งแล้ว (`composer.json` มีอยู่แล้ว)
- **TC-17-06** เป็น by design — ไม่มี constraint qty_harvested ≤ qty_planted เพราะในทางปฏิบัติสามารถเพาะได้มากกว่าที่วางแผน
