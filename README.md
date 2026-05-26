# PEO FileShare (ระบบคลังเอกสาร ศธจ.)

ระบบดาวน์โหลดเอกสารและจัดเก็บสถิติการคลิก/ดาวน์โหลด แบบแยกหมวดหมู่และรองรับผู้ใช้งานหลายระดับ (Multi-User) พัฒนาขึ้นโดยเน้นความเบาหวิว (Lightweight) ไม่พึ่งพาฐานข้อมูล (Database-less) โดยจัดเก็บข้อมูลในรูปแบบ Flat File (PHP Array & JSON) พร้อมระบบความปลอดภัย File Locking รองรับการทำงานบน Shared Hosting ทั่วไปได้อย่างเสถียร

---
## user และ password เปลี่ยนได้ใน .env
- defualt admin: admin
-  default password: P@ssw0rd

---

## 🌟 คุณสมบัติเด่น (Features)
* **หน้าบ้าน (Frontend):** แสดงตารางรายการเอกสารแยกตามหมวดหมู่ สามารถกรองข้อมูล (Filter) เฉพาะหมวดหมู่ที่ต้องการผ่าน URL ได้ รองรับการแสดงผลแบบ Responsive คลีนและ Scannable
* **หลังบ้าน (Management UI):** มีระบบล็อกอินควบคุมสิทธิ์ผ่าน Session 
* **สิทธิ์การใช้งาน (Access Control):**
    * **Admin:** สามารถจัดการหมวดหมู่ และเพิ่ม/แก้ไข/ลบ เอกสารได้ของทุกคนในระบบ
    * **User ทั่วไป:** สามารถเพิ่มเอกสารใหม่ และแก้ไข/ลบ ได้**เฉพาะเอกสารที่ตนเองเป็นเจ้าของเท่านั้น** ไม่สามารถก้าวล่วงไฟล์ของคนอื่นได้
* **ระบบอัปโหลด (Hybrid Upload):** รองรับทั้งการอัปโหลดไฟล์จริงขึ้นไปเก็บส่วนตัวบนโฮสต์ (`uploads/`) หรือการแปะลิงก์ตรงจากภายนอก (เช่น Google Drive, OneDrive)
* **ทนทานสูง (High Concurrency):** ระบบนับสถิติสกัดกั้นข้อมูลพังด้วยกลไก File Locking (`flock`) ต่อให้มีการคลิกพร้อมกันจำนวนมาก ข้อมูลก็ไม่สูญหาย
* **ปลอดภัย (Security):** บล็อกการเข้าถึงไฟล์คอนฟิก, ไฟล์สถิติ และไฟล์ `.env` หลังบ้านโดยเด็ดขาดผ่าน `.htaccess`

---

## 📂 โครงสร้างโฟลเดอร์ (Directory Structure)
~~~text
peo-fileshare/
├── .env                 # ไฟล์เก็บข้อมูลบัญชีผู้ใช้งานระบบ
├── .htaccess            # ไฟล์ควบคุมความปลอดภัยระบบและปลดล็อกขนาดอัปโหลด (Apache)
├── config.php           # ไฟล์โครงสร้างอะเรย์เก็บข้อมูลเอกสาร (สร้างอัตโนมัติเมื่อบันทึก)
├── categories.json      # ไฟล์เก็บข้อมูลหมวดหมู่ (JSON)
├── download_stats.json  # ไฟล์นับยอดสถิติการคลิกดาวน์โหลด (JSON)
├── index.php            # หน้าแรกแสดงผลตารางดาวน์โหลดสำหรับประชาชน/ผู้รับบริการ
├── download.php         # สคริปต์หลังบ้านทำหน้าที่นับจำนวนการคลิกแล้ว Redirect
└── admin.php            # แผงควบคุมระบบสำหรับเจ้าหน้าที่และผู้ดูแลระบบ
~~~

---

## 🚀 วิธีการติดตั้ง (Installation)
1. นำไฟล์ทั้งหมดอัปโหลดขึ้นเซิร์ฟเวอร์ (Shared Hosting หรือจำลองผ่าน XAMPP) ไว้ในโฟลเดอร์ที่ต้องการ (เช่น `/data_files/`)
2. ตรวจสอบและแก้ไขสิทธิ์ (Permission) ของโฟลเดอร์โครงการให้ PHP สามารถเขียนไฟล์ลงไปได้ (แนะนำสิทธิ์ `0755` หรือ `0777` ตามคอนฟิกของโฮสติ้งแต่ละแห่ง)
3. เข้าสู่ระบบหลังบ้านผ่าน `yourdomain.com/folder/admin.php` เพื่อเริ่มต้นเพิ่มหมวดหมู่และอัปโหลดไฟล์

---

## 🌐 วิธีการนำไปใช้งานบนเว็บไซต์ (Embedding Guide)

คุณสามารถนำหน้าแสดงตารางรายการเอกสารและการนับสถิติดาวน์โหลด (`index.php`) ไปฝังลงบนเว็บไซต์หลักของหน่วยงาน หรือเว็บไซต์อื่น ๆ ผ่านการใช้งานแท็ก `<iframe>` ได้ 2 รูปแบบดังนี้:

### 1. ฝังภายในหน้าเพจ (Responsive Windows)
เหมาะสำหรับกรณีที่ต้องการให้ตัวตารางดาวน์โหลดแสดงผลอยู่กึ่งกลางหน้า โดยรอบข้างยังคงมีเมนู แถบข้าง (Sidebar) หรือหัวเว็บเดิมของเว็บไซต์หลักอยู่

~~~html
<div style="width: 100%; overflow: hidden; margin: 15px 0;">
    <iframe 
        src="https://www.yourdomain.com/data_files/index.php" 
        title="ระบบสถิติการดาวน์โหลดเอกสาร กลุ่มนโยบายและแผน"
        width="100%" 
        height="650px" 
        style="border: none; width: 100%; height: 650px; display: block; background-color: #ffffff;"
        loading="lazy"
        allowfullscreen>
    </iframe>
</div>
~~~

### 2. ฝังแบบเต็มหน้าจอ (Full Screen Single Page)
เหมาะสำหรับกรณีที่สร้างหน้าเพจใหม่ขึ้นมาเฉพาะ แล้วต้องการให้ระบบคลังเอกสารกางตัวคลุมเต็มพื้นที่หน้าจอเบ็ดเสร็จ ไร้ขอบขาว และซ่อน Scrollbar ซ้อนของตัวธีมเดิมบน WordPress

~~~html
<div class="peo-iframe-fullscreen-wrapper">
    <iframe 
        src="https://www.yourdomain.com/data_files/index.php" 
        class="peo-fullscreen-iframe"
        title="ระบบสถิติการดาวน์โหลดเอกสาร"
        loading="lazy"
        allowfullscreen>
    </iframe>
</div>

<style>
    html, body {
        overflow: hidden !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .peo-iframe-fullscreen-wrapper {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 999999;
        background-color: #ffffff;
    }
    .peo-fullscreen-iframe {
        width: 100%;
        height: 100%;
        border: none;
        display: block;
    }
</style>
~~~

ถ้าหากต้องการให้แสดงเฉพาะ Catagory ให้ใช้ index.php?cat_id=1
cat_id ให้ดูใน admin.php

### 🛠️ วิธีติดตั้งผ่าน WordPress (Gutenberg / Elementor)
1. สร้างหน้าเพจใหม่ (Add New Page) บน WordPress
2. หากใช้ **Gutenberg (Editor ปกติ)**: เพิ่มบล็อก **"Custom HTML"** แล้วนำโค้ดด้านบนไปวาง
3. หากใช้ **Elementor**: ลาก Widget **"HTML"** มาวางใน Section แล้วนำโค้ดไปแปะ
4. กดเผยแพร่ (Publish) หน้าเพจเพื่อใช้งานจริง

### 🔍 การกรองแสดงผลเฉพาะหมวดหมู่ผ่าน URL
คุณสามารถสั่งให้หน้าเว็บคัดกรองแสดงเฉพาะหมวดหมู่เอกสารที่ต้องการได้ทันที โดยการส่งค่าคีย์ ID หมวดหมู่ (เช่น `cat_1`, `cat_2`) ต่อท้ายลิงก์ URL ในแท็ก iframe ตัวอย่างเช่น:
* แสดงเฉพาะคู่มือและฟอร์ม: `https://www.loeipeo.go.th/data_files/index.php?cat=cat_1`
* แสดงเฉพาะรายงานสถิติ: `https://www.loeipeo.go.th/data_files/index.php?cat=cat_2`

---

## 🔒 ข้อควรระวังเรื่องความปลอดภัย
* ไฟล์นี้ได้รับการตั้งค่าดักการดาวน์โหลดไฟล์ `.env` ผ่าน `.htaccess` ของ Apache ไว้แล้ว หากระบบของท่านรันบน Nginx โปรดเพิ่มกฎระเบียบบล็อก (Deny) การเข้าถึงไฟล์ `.env` ที่ตัว Server Configuration
* **ห้ามเปลี่ยนสถานะ Repository นี้เป็น Public บน GitHub เด็ดขาด** หากยังไม่ได้นำรหัสผ่านจริงออกจากไฟล์ `.env` หรือยังไม่ได้ระบุชื่อไฟล์ `.env` ไว้ใน `.gitignore`

---

## 👨‍💻 ออกแบบและพัฒนาโดย
**นายสุทธิชัย ชมชื่น** นักวิชาการคอมพิวเตอร์ชำนาญการ  
กลุ่มนโยบายและแผน สำนักงานศึกษาธิการจังหวัดอุดรธานี
