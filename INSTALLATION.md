# 🚀 دليل التثبيت - Football Analytics

## 📋 المحتويات

- [المتطلبات](#المتطلبات)
- [التثبيت على XAMPP](#التثبيت-على-xampp)
- [التثبيت على Hostinger](#التثبيت-على-hostinger)
- [التكوين الأساسي](#التكوين-الأساسي)
- [اختبار التثبيت](#اختبار-التثبيت)
- [استكشاف الأخطاء](#استكشاف-الأخطاء)

---

## المتطلبات

### متطلبات النظام

```
✅ PHP >= 7.4
✅ MySQL >= 5.7 أو MariaDB >= 10.3
✅ Apache مع mod_rewrite
✅ مساحة تخزين >= 500 MB
✅ اتصال إنترنت (للتحديثات)
```

### الملفات والمجلدات

```
football-analytics/
├── index.html              ✓ الصفحة الرئيسية
├── config.php             ✓ الإعدادات
├── api.php                ✓ واجهة API
├── helpers.php            ✓ دوال مساعدة
├── app.js                 ✓ منطق التطبيق
├── styles.css             ✓ التصاميم
├── football_db.sql        ✓ قاعدة البيانات
├── sample_data.sql        ✓ بيانات نموذجية
├── README_AR.md           ✓ التوثيق بالعربية
├── INSTALLATION.md        ✓ هذا الملف
└── backups/               📁 النسخ الاحتياطية (سينشأ تلقائياً)
```

---

## التثبيت على XAMPP

### الخطوة 1: التحضير

1. **تحميل XAMPP**
   - اذهب إلى https://www.apachefriends.org
   - حمّل النسخة الموافقة لنظام التشغيل

2. **تثبيت XAMPP**
   - اتبع خطوات التثبيت العادية
   - تأكد من تثبيت Apache و MySQL و PHP

3. **بدء الخدمات**
   - افتح XAMPP Control Panel
   - اضغط Start بجانب Apache و MySQL

### الخطوة 2: إنشاء المشروع

```bash
# Windows
cd C:\xampp\htdocs
mkdir football-analytics
cd football-analytics

# Linux/Mac
cd /opt/lampp/htdocs
mkdir football-analytics
cd football-analytics
```

### الخطوة 3: نسخ الملفات

انسخ جميع الملفات من المجلد المرفق إلى:
- **Windows**: `C:\xampp\htdocs\football-analytics\`
- **Linux**: `/opt/lampp/htdocs/football-analytics/`
- **Mac**: `/Applications/XAMPP/xamppfiles/htdocs/football-analytics/`

### الخطوة 4: إنشاء قاعدة البيانات

#### الطريقة الأولى: استخدام phpMyAdmin

1. افتح: `http://localhost/phpmyadmin`
2. انقر على "Databases" من القائمة العلوية
3. أدخل الاسم: `football_analytics`
4. اختر Collation: `utf8mb4_unicode_ci`
5. انقر "Create"
6. اختر قاعدة البيانات المنشأة
7. انقر على تبويب "Import"
8. اختر ملف `football_db.sql`
9. انقر "Import"

#### الطريقة الثانية: سطر الأوامر

```bash
# Windows
cd C:\xampp\mysql\bin
mysql -u root
CREATE DATABASE football_analytics CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE football_analytics;
source C:\xampp\htdocs\football-analytics\football_db.sql;

# Linux/Mac
mysql -u root -p
CREATE DATABASE football_analytics CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE football_analytics;
source /path/to/football_db.sql;
```

### الخطوة 5: إضافة البيانات النموذجية (اختياري)

```bash
# من phpMyAdmin: import sample_data.sql
# أو من سطر الأوامر:
mysql -u root football_analytics < sample_data.sql
```

### الخطوة 6: التحقق من الإعدادات

عدّل `config.php` إذا لزم الأمر:

```php
define('DB_HOST', 'localhost');    // عادة لا يحتاج تغيير
define('DB_USER', 'root');         // الاسم الافتراضي
define('DB_PASS', '');             // فارغ بشكل افتراضي
define('DB_NAME', 'football_analytics');
define('PRODUCTION', false);       // للاختبار المحلي
```

### الخطوة 7: اختبار التثبيت

افتح المتصفح وانتقل إلى:

```
http://localhost/football-analytics/
```

يجب أن ترى الصفحة الرئيسية والبيانات تُحمّل بنجاح.

---

## التثبيت على Hostinger

### الخطوة 1: تحضير الملفات

1. أنشئ ملف مضغوط (`football-analytics.zip`) يحتوي على جميع الملفات

### الخطوة 2: الاتصال بـ FTP

```
الخادم: ftp.yourdomain.com (من لوحة تحكم Hostinger)
المستخدم: اسم المستخدم FTP
كلمة المرور: كلمة مرور FTP
المنفذ: 21
```

### الخطوة 3: تحميل الملفات

1. افتح برنامج FTP (مثل FileZilla)
2. اتصل بخادمك
3. انتقل إلى مجلد `public_html`
4. احمِّل جميع الملفات إلى:
   - `public_html/football-analytics/` أو
   - `public_html/` مباشرة

### الخطوة 4: إنشاء قاعدة البيانات

من لوحة تحكم Hostinger:

1. اذهب إلى **Databases**
2. انقر **Create Database**
3. أدخل الاسم: `football_analytics`
4. اختر Collation: `utf8mb4_unicode_ci`
5. انقر **Create**

### الخطوة 5: استيراد قاعدة البيانات

من phpMyAdmin المتاح على Hostinger:

1. اختر قاعدة البيانات المنشأة
2. انقر **Import**
3. اختر `football_db.sql`
4. انقر **Import**

### الخطوة 6: تحديث config.php

من FTP أو File Manager:

```php
// احصل على بيانات الاتصال من Hostinger
define('DB_HOST', 'localhost');        // عادة localhost
define('DB_USER', 'your_db_user');    // من لوحة التحكم
define('DB_PASS', 'your_password');   // من لوحة التحكم
define('DB_NAME', 'football_analytics');
define('PRODUCTION', true);            // للاستضافة الحقيقية
```

### الخطوة 7: تفعيل SSL

من لوحة تحكم Hostinger:

1. اذهب إلى **SSL**
2. استخدم **Let's Encrypt** (مجاني)
3. فعّل الـ Auto-renewal

### الخطوة 8: تحديث الهيكل

تعديل `.htaccess` (اختياري):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /football-analytics/
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ api.php?request=$1 [QSA,L]
</IfModule>
```

### الخطوة 9: اختبار الموقع

افتح:
```
https://yourdomain.com/football-analytics/
```

---

## التكوين الأساسي

### إعدادات الأمان

**في الإنتاج، قم بـ:**

1. **تعطيل عرض الأخطاء:**
```php
define('PRODUCTION', true);
error_reporting(0);
```

2. **تحديث كلمات المرور الافتراضية:**
```sql
UPDATE users SET password = SHA2('newpassword123', 256) 
WHERE username = 'admin';
```

3. **إنشاء مجلد `.htaccess` للحماية:**
```apache
<Files "config.php">
    Order Allow,Deny
    Deny from all
</Files>
```

4. **تفعيل HTTPS:**
```php
// في config.php
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}
```

### إعدادات الأداء

```php
// Cache headers
header('Cache-Control: public, max-age=3600');
header('Pragma: cache');

// Compression
if (extension_loaded('zlib')) {
    ob_start('ob_gzhandler');
}

// Database optimization
$conn->set_charset("utf8mb4");
```

### إنشاء جدول المسؤولين

```sql
-- بيانات الدخول الافتراضية
INSERT INTO users (username, email, password, full_name, role, is_active) 
VALUES ('admin', 'admin@example.com', SHA2('admin123', 256), 'المسؤول', 'admin', TRUE);
```

---

## اختبار التثبيت

### اختبار قاعدة البيانات

```php
<?php
require_once 'config.php';

// اختبار الاتصال
if ($conn->connect_error) {
    die("خطأ في الاتصال");
} else {
    echo "✓ اتصال قاعدة البيانات ناجح";
}

// اختبار الجداول
$result = $conn->query("SHOW TABLES");
$tables = $result->fetch_all();
echo "✓ عدد الجداول: " . count($tables);

?>
```

### اختبار واجهة API

افتح في المتصفح:

```
http://localhost/football-analytics/api.php?action=get_tournaments
http://localhost/football-analytics/api.php?action=get_teams
http://localhost/football-analytics/api.php?action=get_matches
```

يجب أن ترى استجابة JSON.

### اختبار الواجهة الأمامية

تحقق من:
- ✅ تحميل الصفحة الرئيسية
- ✅ ظهور البيانات
- ✅ عمل الفلاتر
- ✅ عمل البحث
- ✅ استجابة التصميم على الهاتف

---

## استكشاف الأخطاء

### الخطأ: "خطأ في الاتصال بقاعدة البيانات"

```bash
# تحقق من أن MySQL يعمل
# تحقق من بيانات الاتصال في config.php
# تأكد من وجود قاعدة البيانات
mysql -u root -e "SHOW DATABASES;" | grep football_analytics
```

### الخطأ: "الجداول غير موجودة"

```bash
# استورد قاعدة البيانات مجدداً
mysql -u root football_analytics < football_db.sql
```

### الخطأ: "البيانات لا تظهر"

1. تحقق من الـ Console في أدوات المطور (F12)
2. تحقق من استجابة API
3. تأكد من أن البيانات مدرجة في قاعدة البيانات

### الخطأ: "البيانات بطيئة جداً"

```sql
-- أضف فهارس إضافية
ALTER TABLE matches ADD INDEX idx_tournament_status (tournament_id, status);
ALTER TABLE players ADD INDEX idx_team_position (team_id, position);
ALTER TABLE match_events ADD INDEX idx_match_type (match_id, event_type);
```

### الخطأ: "الصور لا تظهر"

1. تحقق من روابط الصور في قاعدة البيانات
2. استخدم `https://via.placeholder.com/` للاختبار
3. تأكد من السماح بالوصول للموارد الخارجية

---

## التحديثات والصيانة

### النسخ الاحتياطية المنتظمة

```bash
# نسخة احتياطية يومية
mysqldump -u root football_analytics > backups/backup_$(date +%Y-%m-%d).sql
```

### تنظيف قاعدة البيانات

```sql
-- حذف الإشعارات القديمة
DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- تحديث الإحصائيات
OPTIMIZE TABLE matches;
OPTIMIZE TABLE players;
OPTIMIZE TABLE users;
```

### مراقبة الأداء

```php
// قياس سرعة الاستعلامات
$start = microtime(true);
$result = $conn->query("SELECT * FROM matches");
$time = microtime(true) - $start;
echo "وقت الاستعلام: " . ($time * 1000) . " ms";
```

---

## الخطوات التالية

بعد التثبيت الناجح:

1. ✅ أضف المزيد من البيانات
2. ✅ خصّص الألوان والعلامات
3. ✅ اختبر على أجهزة مختلفة
4. ✅ فعّل SSL
5. ✅ اعدد النسخ الاحتياطية
6. ✅ راقب الأداء

---

## الدعم

للمساعدة:
- 📧 البريد: support@example.com
- 📖 الوثائق: README_AR.md
- 🔗 الرابط: https://github.com/yourusername/football-analytics

---

**نسخة الدليل**: 1.0  
**آخر تحديث**: 2024
