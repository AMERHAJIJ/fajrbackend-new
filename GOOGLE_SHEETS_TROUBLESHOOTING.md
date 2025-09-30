# دليل استكشاف أخطاء Google Sheets

## المشكلة: "الجدول غير موجود أو لا يمكن الوصول إليه"

### الأسباب المحتملة والحلول:

#### 1. مشكلة في Spreadsheet ID
- **السبب**: الـ Spreadsheet ID غير صحيح أو الجدول محذوف
- **الحل**: 
  - تأكد من صحة الـ Spreadsheet ID في ملف `config/services.php`
  - تأكد من أن الجدول موجود في Google Sheets
  - يمكنك الحصول على الـ ID من رابط الجدول: `https://docs.google.com/spreadsheets/d/SPREADSHEET_ID/edit`

#### 2. مشكلة في صلاحيات Service Account
- **السبب**: Service Account لا يملك صلاحيات للوصول للجدول
- **الحل**:
  - افتح الجدول في Google Sheets
  - اضغط على "مشاركة" (Share)
  - أضف البريد الإلكتروني للـ Service Account: `laravel-sheets@fecir-amer.iam.gserviceaccount.com`
  - امنحه صلاحيات "محرر" (Editor)

#### 3. مشكلة في ملف Service Account
- **السبب**: ملف JSON غير صحيح أو تالف
- **الحل**:
  - تأكد من وجود الملف في `storage/app/public/google-service-account.json`
  - تأكد من صحة محتوى الملف JSON
  - تأكد من أن الملف قابل للقراءة

#### 4. مشكلة في إعدادات البيئة
- **السبب**: متغيرات البيئة غير محددة
- **الحل**:
  - تأكد من وجود ملف `.env` مع الإعدادات التالية:
  ```
  GOOGLE_SHEETS_SPREADSHEET_ID=your_spreadsheet_id
  GOOGLE_SHEETS_SHEET_NAME=Sheet1
  ```

### خطوات التشخيص:

1. **تشغيل أمر الاختبار**:
   ```bash
   php artisan google-sheets:test
   ```

2. **فحص ملفات السجل**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **التحقق من الإعدادات**:
   ```bash
   php artisan config:show services.google_sheets
   ```

### نصائح إضافية:

- تأكد من أن Google Sheets API مفعل في Google Cloud Console
- تأكد من أن Service Account له الصلاحيات المطلوبة
- تأكد من أن الجدول يحتوي على ورقة باسم "Sheet1" أو الاسم المحدد في الإعدادات
- تأكد من أن الخدمة تعمل في بيئة آمنة (HTTPS في الإنتاج)

### رسائل الخطأ الشائعة:

- `404 Not Found`: الجدول غير موجود
- `403 Forbidden`: لا توجد صلاحيات
- `401 Unauthorized`: مشكلة في المصادقة
- `Service Account file not found`: ملف Service Account غير موجود
