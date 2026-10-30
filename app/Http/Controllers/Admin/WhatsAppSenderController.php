<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GymReminderMessage;
use App\Services\GymReminders\ReminderMessageTemplate;
use App\Services\GymReminders\WhatsAppProviderAdmin;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WhatsAppSenderController extends Controller
{
    public function index(WhatsAppProviderAdmin $provider): Response
    {
        return response()->view('admin.whatsapp-sender', ['providerStatus' => $provider->status(), 'messageTemplate' => ReminderMessageTemplate::current()])
            ->header('Cache-Control', 'no-store, private')->header('Referrer-Policy', 'no-referrer');
    }

    public function status(WhatsAppProviderAdmin $provider): JsonResponse
    {
        return response()->json($provider->status())->header('Cache-Control', 'no-store, private');
    }

    public function updateMessage(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('whatsappMessage', [
            'text' => ['bail', 'required', 'string', 'max:1000', function ($attribute, $value, $fail) {
                preg_match_all('/\{[^{}]*\}/u', $value, $matches);
                $allowed = ['{name}', '{time}', '{day}', '{date}', '{timezone}'];
                if (array_diff($matches[0], $allowed)) {
                    $fail('استخدم المتغيرات المتاحة فقط: {name}، {time}، {day}، {date}، {timezone}.');
                }
                $worstCase = strtr($value, ReminderMessageTemplate::values(str_repeat('😀', 255), CarbonImmutable::parse('2026-11-04 18:00', 'Africa/Cairo')));
                if (! ReminderMessageTemplate::fitsProvider($worstCase)) {
                    $fail('الرسالة طويلة بعد استبدال المتغيرات. اختصر النص أو قلل تكرار اسم المستخدم.');
                }
            }],
        ]);
        GymReminderMessage::updateOrCreate(['id' => 1], ['text' => $data['text']]);

        return redirect()->route('admin.whatsapp.index')->with('success', 'تم حفظ نص رسالة التذكير. سيُستخدم عند بدء الإرسال للتذكيرات التالية.');
    }

    public function connect(WhatsAppProviderAdmin $provider): RedirectResponse
    {
        $ok = $provider->connect();

        return redirect()->route('admin.whatsapp.index')->with($ok ? 'success' : 'error', $ok
            ? 'بدأ ربط رقم الإرسال. انتظر ظهور QR ثم امسحه من واتساب رقم الجيم.'
            : 'تعذر بدء الربط. تحقق من تشغيل البروفايدر ومفتاح الإدارة، أو انتظر انتهاء العملية الحالية.')
            ->header('Cache-Control', 'no-store, private');
    }

    public function disconnect(WhatsAppProviderAdmin $provider): RedirectResponse
    {
        $ok = $provider->disconnect();

        return redirect()->route('admin.whatsapp.index')->with($ok ? 'success' : 'error', $ok
            ? 'تم فصل رقم الإرسال. يمكنك الآن بدء ربط رقم آخر. إعدادات المشتركين محفوظة.'
            : 'تعذر الفصل. قد يكون الإرسال أو بدء الاتصال قيد التنفيذ؛ انتظر ثم حدّث الحالة قبل المحاولة.')
            ->header('Cache-Control', 'no-store, private');
    }
}
