# نظام فواتير المشتريات — المرحلة 1 (Laravel 11/12)

تسجيل الدخول + لوحة التحكم + إدارة المستخدمين + إيميل المبلغ المستحق (5%).

## التركيب
```bash
composer create-project laravel/laravel invoices
cd invoices
```
1) انسخ محتويات هذا المجلد فوق مشروع لارافيل (وافق على استبدال `bootstrap/app.php` و`routes/console.php` و`app/Models/User.php` و`database/seeders/DatabaseSeeder.php`).

2) عدّل `.env`:
```env
APP_LOCALE=ar
APP_TIMEZONE=Asia/Riyadh
DB_CONNECTION=mysql        # أو sqlite
ADMIN_EMAIL=you@company.com
ADMIN_PASSWORD=كلمة-مرور-قوية

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@company.com
MAIL_FROM_NAME="فواتير المشتريات"
INVOICE_REMIND_DAYS_BEFORE=1
```
3) شغّل:
```bash
php artisan migrate --seed
php artisan serve
```
ادخل على `/login` بحساب المدير (البريد وكلمة المرور من `.env`).

## تشغيل التذكير
- تجربة بدون إرسال: `php artisan invoices:send-due-reminders --dry-run`
- إرسال فعلي: `php artisan invoices:send-due-reminders`
- تلقائياً كل يوم 8 صباحاً: أضف على السيرفر مهمة cron واحدة
```
* * * * * cd /path/to/invoices && php artisan schedule:run >> /dev/null 2>&1
```

## كيف يعمل التذكير
- يشمل الفواتير التي: لها مستحق 5%، ولم يُفعّل «تم استحقاق المبلغ»، وتاريخ استحقاقها اليوم أو خلال `INVOICE_REMIND_DAYS_BEFORE` يوم.
- يصل الإيميل إلى بريد **صاحب المشروع** (`projects.user_id`)، رسالة واحدة تجمع كل فواتيره المستحقة.
- لا يتكرر التذكير لنفس الفاتورة في اليوم نفسه (`last_reminded_on`)، فيصل تذكير قبل الموعد وآخر يوم الاستحقاق.
- المستحق = 5% من المبلغ الخاضع فقط، والنسب في `config/invoices.php`.

## الصلاحيات
- لا يوجد تسجيل عام؛ المدير فقط يضيف المستخدمين من «المستخدمون».
- كل الصفحات تتطلب تسجيل الدخول، والحساب المعطّل لا يدخل ويُخرَج فوراً.
- المدير يرى كل المشاريع، والمستخدم العادي يرى مشاريعه فقط.
- لا يمكن حذف أو تعطيل آخر مدير، ولا حذف مستخدم لديه مشاريع.

## المرحلة التالية
شاشات المشاريع وملفات الأشهر والفواتير (CRUD + رفع الصورة + زر «تم الاستحقاق») بنفس تصميم ملف `invoices.html`.
الجداول والموديلات الخاصة بها جاهزة.
