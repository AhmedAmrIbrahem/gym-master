المرحلة الأولى: قاعدة معرفة Gym Master (FAQ)

الملفات الموجودة في هذه الحزمة فقط هي المطلوبة لهذه المرحلة.
انسخ محتويات المجلد إلى جذر مشروع C:\xampp\htdocs\gym-dashboard مع الحفاظ على المسارات.
الملفان الموجودان اللذان ستستبدلهما: routes/web.php و app/Http/Controllers/ChatbotController.php
الملفات الجديدة: migration + Model + Service + Admin Controller + Admin Blade.

قبل النسخ: احتفظ بنسخة من الملفين routes/web.php و ChatbotController.php لو عدلتهما بعد تاريخ ZIP.
لا تستبدل المشروع بالكامل بهذه الحزمة.

بعد النسخ افتح PowerShell داخل المشروع:
php artisan optimize:clear
php artisan migrate
php artisan route:list --path=admin/chatbot

سجل الدخول بحساب super_admin، ثم افتح:
http://localhost/gym-dashboard/public/admin/chatbot/faqs
(أو /admin/chatbot/faqs في عنوان موقعك المعتاد، حسب إعداد XAMPP)

أضف سؤالاً مثلاً: مواعيد الجيم
إجابة مثلاً: اسأل الإدارة عن ساعات العمل المحدّثة.
ثم اكتب نفس السؤال في الشات. سيجيب محلياً بدون Gemini.

ملاحظة: المطابقة التامة بعد توحيد بعض الحروف العربية، مع إمكانية صياغات إضافية.
أي سؤال آخر يستمر إلى Gemini بالطريقة القديمة. لن تظهر صفحة FAQ في القائمة بعد؛ افتح الرابط مباشرة.
لا حاجة إلى تعديل GeminiService أو واجهة الشات في هذه المرحلة.
