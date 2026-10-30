# WhatsApp reminders patch v2 — super-admin sender management

This full patch replaces the earlier patch. Apply paths relative to the original Laravel project root. Existing v1 installations can also overlay it, add the new management secret on both sides, run provider npm install and restart the provider. See setup instructions.

| Path | Change against original project ZIP |
| --- | --- |
| .env.example | modified |
| app/Casts/UtcDateTime.php | new |
| app/Console/Commands/DispatchGymReminders.php | new |
| app/Http/Controllers/Admin/WhatsAppSenderController.php | new |
| app/Http/Controllers/GymReminderController.php | new |
| app/Http/Controllers/ProfileController.php | modified |
| app/Jobs/SendGymReminder.php | new |
| app/Models/GymReminder.php | new |
| app/Models/GymReminderDelivery.php | new |
| app/Providers/AppServiceProvider.php | modified |
| app/Services/GymReminders/PhoneNumber.php | new |
| app/Services/GymReminders/WebWhatsAppSender.php | new |
| app/Services/GymReminders/WhatsAppProviderAdmin.php | new |
| app/Services/GymReminders/WhatsAppSender.php | new |
| config/gym_reminders.php | new |
| database/migrations/2026_10_30_000000_create_gym_reminders_tables.php | new |
| docs/WHATSAPP_PATCH_FILES.md | new |
| docs/WHATSAPP_REMINDERS_AR.md | new |
| docs/WHATSAPP_TEST_REPORT.md | new |
| resources/views/admin/whatsapp-sender.blade.php | new |
| resources/views/layouts/app.blade.php | modified |
| resources/views/profile/edit.blade.php | modified |
| resources/views/profile/partials/gym-reminder.blade.php | new |
| routes/console.php | modified |
| routes/web.php | modified |
| services/whatsapp-provider/.env.example | new |
| services/whatsapp-provider/.gitignore | new |
| services/whatsapp-provider/core.mjs | new |
| services/whatsapp-provider/http.mjs | new |
| services/whatsapp-provider/package.json | new |
| services/whatsapp-provider/server.mjs | new |
| services/whatsapp-provider/session.mjs | new |
| services/whatsapp-provider/test/provider.test.mjs | new |
| services/whatsapp-provider/test/session.test.mjs | new |
| tests/Feature/GymReminderTest.php | new |
| tests/Feature/WhatsAppSenderAdminTest.php | new |

Setup: docs/WHATSAPP_REMINDERS_AR.md
Verification: docs/WHATSAPP_TEST_REPORT.md

No deletion of existing project files is required. No .env, secrets, database, logs, linked-account session, QR files, vendor, node_modules or frontend build files are included.
