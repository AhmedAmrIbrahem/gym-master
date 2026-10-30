# Weekly Cairo reminders upgrade file list

Overlay on the installed WhatsApp reminders version. Includes the earlier 429 fix and profile/sidebar design changes. Run the NEW migration; preserve .env and provider auth data.

Paths relative to Laravel root:

- app/Console/Commands/DispatchGymReminders.php
- app/Http/Controllers/Admin/WhatsAppSenderController.php
- app/Http/Controllers/GymReminderController.php
- app/Jobs/SendGymReminder.php
- app/Models/GymReminder.php
- app/Models/GymReminderMessage.php
- app/Services/GymReminders/ReminderMessageTemplate.php
- database/migrations/2026_10_31_000000_add_weekly_gym_reminders.php
- docs/WEEKLY_CAIRO_REMINDERS_AR.md
- docs/WEEKLY_PATCH_FILES.md
- resources/views/admin/whatsapp-sender.blade.php
- resources/views/layouts/app.blade.php
- resources/views/profile/partials/gym-reminder.blade.php
- routes/web.php
- tests/Feature/GymReminderTest.php
- tests/Feature/WeeklyGymReminderMigrationTest.php
- tests/Feature/WeeklyGymReminderTest.php
- tests/Feature/WhatsAppSenderAdminTest.php
