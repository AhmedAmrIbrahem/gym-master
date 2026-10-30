<?php

namespace App\Http\Controllers;

use App\Models\GymReminder;
use App\Services\GymReminders\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class GymReminderController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('gymReminder', [
            'enabled' => ['required', 'boolean'],
            'days' => ['required', 'array:0,1,2,3,4,5,6', 'size:7'],
            'days.*' => ['required', 'array:enabled,time'],
            'days.*.enabled' => ['required', 'boolean'],
            'days.*.time' => ['required', 'date_format:H:i'],
            'opt_in' => [Rule::excludeIf(! $request->boolean('enabled')), 'required', 'accepted'],
        ]);
        $phone = PhoneNumber::normalize($request->user()->phone);
        if ($request->boolean('enabled') && ! $phone) {
            throw ValidationException::withMessages([
                'enabled' => 'يلزم رقم هاتف صحيح في حسابك مع كود الدولة، أو رقم محمول مصري. اطلب تحديثه من الإدارة.',
            ])->errorBag('gymReminder');
        }

        $schedule = [];
        foreach ($data['days'] as $day => $settings) {
            if (filter_var($settings['enabled'], FILTER_VALIDATE_BOOLEAN)) {
                $schedule[$day] = $settings['time'];
            }
        }
        ksort($schedule, SORT_NUMERIC);
        if ($request->boolean('enabled') && $schedule === []) {
            throw ValidationException::withMessages(['days' => 'اختر يومًا واحدًا على الأقل لتفعيل التذكير.'])->errorBag('gymReminder');
        }

        DB::transaction(function () use ($request, $schedule, $phone) {
            // Lock the account to serialize concurrent creates/saves for this user's settings.
            $request->user()->newQuery()->whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $reminder = GymReminder::firstOrNew(['user_id' => $request->user()->id]);
            $oldSchedule = $reminder->exists ? $reminder->scheduleForWeek() : [];
            ksort($oldSchedule, SORT_NUMERIC);
            $enabled = $request->boolean('enabled');
            $changed = ! $reminder->exists || $oldSchedule !== $schedule || $reminder->enabled !== $enabled
                || $reminder->consent_phone !== ($enabled ? $phone : null);
            $reminder->fill([
                'enabled' => $enabled, 'weekly_schedule' => $schedule,
                'time' => $schedule === [] ? '18:00' : reset($schedule),
                'timezone' => GymReminder::TIMEZONE,
                'consent_phone' => $enabled ? $phone : null,
                'consented_at' => $enabled ? now() : null,
            ]);
            if ($changed) {
                $reminder->version = (string) Str::uuid();
                // Saving at/past the chosen minute never triggers a catch-up message on a new revision.
                $reminder->eligible_after = now('UTC');
            }
            $reminder->save();
        });

        return redirect()->route('profile.edit')->with('success', 'تم حفظ إعدادات تذكير واتساب.');
    }
}
