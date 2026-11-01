# استكمال ملفات تحديث الجدول الأسبوعي

الفحص للنسخة المثبتة وجد أن ملف app/Models/GymReminder.php ما زال من النسخة القديمة، ولذلك ظهر الخطأ scheduleForWeek غير موجودة. ووجد أيضًا غياب نموذج الرسالة وخدمة إعداد نصها، واختلاف مهمة الإرسال عن نسخة التحديث.

تحتوي هذه الحزمة نسخ الملفات الأربعة المتوافقة مع بقية التحديث الذي ثبّتّه:

- app/Models/GymReminder.php
- app/Models/GymReminderMessage.php
- app/Services/GymReminders/ReminderMessageTemplate.php
- app/Jobs/SendGymReminder.php

تضم أيضًا هذا الدليل الجديد docs/WEEKLY_MERGE_COMPLETION_AR.md. بقية ملفات تحديث الجدول الأسبوعي الموجودة في جهازك طابقت نسخة التحديث عند الفحص، بما فيها الـmigration.

أوقف عامل queue:work والمجدول schedule:work مؤقتًا بـCtrl+C، وفك الملفات داخل جذر مشروع Laravel مع الحفاظ على المسارات واستبدال الموجود، ثم نفّذ:

```powershell
php artisan migrate
php artisan optimize:clear
```

أعد تشغيل php artisan serve إذا كنت تستخدمه، ثم عامل الطابور والمجدول بالأوامر السابقة. اترك البروفايدر npm start كما هو؛ لا حاجة لإعادة ربط رقم الإرسال أو تعديل .env. حدّث صفحة البروفايل.

لا تستخدم migrate:fresh أو تحذف قاعدة البيانات. هذه نسخ الملفات نفسها التي اجتازت اختبارات التحديث؛ لا تتضمن تغييرات برمجية جديدة أو أسرارًا أو بيانات حسابات.
