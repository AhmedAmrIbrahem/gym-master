<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-white">إدارة رقم إرسال واتساب</h2>
    </x-slot>

    <section class="max-w-3xl mx-auto bg-gray-800 p-6 rounded-2xl border border-gray-700 space-y-5"
        x-data="{
            session: @js($providerStatus),
            labels: { idle: 'لم يتم الربط', starting: 'جارٍ بدء الاتصال', qr: 'بانتظار مسح QR', ready: 'متصل وجاهز', disconnected: 'غير متصل', auth_failure: 'يلزم إعادة الربط', error: 'تعذر الاتصال', disconnecting: 'جارٍ الفصل', unavailable: 'البروفايدر غير متاح أو إعداداته غير مكتملة' },
            timer: null,
            refreshing: false,
            init() { this.timer = setInterval(() => this.refresh(), 5000); },
            destroy() { clearInterval(this.timer); },
            async refresh() {
                if (this.refreshing) return;
                this.refreshing = true;
                try {
                    const response = await fetch(@js(route('admin.whatsapp.status')), { headers: { Accept: 'application/json' }, credentials: 'same-origin', cache: 'no-store' });
                    if (!response.ok) throw new Error('unavailable');
                    this.session = await response.json();
                } catch { this.session = { status: 'unavailable', sender_phone: null, qr_data_url: null }; }
                finally { this.refreshing = false; }
            }
        }">
        <p class="text-sm text-gray-300">رقم واحد للجيم يرسل لكل مشترك في محادثة خاصة. السوبر أدمن فقط يستطيع عرض QR وربط رقم الإرسال أو فصله واستبداله.</p>
        <p class="text-sm text-gray-300">رقم الإرسال هو حساب واتساب الذي تمسح منه QR، وليس رقمًا تكتبه في نموذج. بيانات المشترك وموعد تذكيره تبقى منفصلة.</p>
        <div class="bg-gray-900 rounded-xl p-4 space-y-2" aria-live="polite">
            <p class="text-white">الحالة: <strong x-text="labels[session.status] || labels.unavailable"></strong></p>
            <p class="text-white" x-show="session.sender_phone">رقم الإرسال المرتبط: <bdi x-text="session.sender_phone"></bdi></p>
        </div>
        <div x-show="session.qr_data_url" style="display:none" class="space-y-3">
            <p class="text-sm text-gray-300">افتح واتساب على هاتف رقم الجيم ← الأجهزة المرتبطة ← ربط جهاز. لا تشارك QR مع أي مشترك.</p>
            <img :src="session.qr_data_url || undefined" alt="رمز ربط رقم إرسال الجيم" class="w-72 max-w-full bg-white p-3 rounded-xl" width="288" height="288">
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <form method="post" action="{{ route('admin.whatsapp.connect') }}">
                @csrf
                <button type="submit" :disabled="['ready', 'starting', 'qr', 'disconnecting', 'unavailable'].includes(session.status)" class="bg-blue-600 text-white font-bold px-5 py-2.5 rounded-xl disabled:opacity-50">بدء الربط / إعادة الاتصال</button>
            </form>
            <button type="button" @click="refresh()" :disabled="refreshing" class="bg-gray-700 text-white font-bold px-5 py-2.5 rounded-xl">تحديث الحالة</button>
        </div>
        <form method="post" action="{{ route('admin.whatsapp.disconnect') }}" class="pt-4 border-t border-gray-700 space-y-3">
            @csrf
            <p class="text-sm text-gray-300">لتغيير الرقم، افصل الحالي ثم ابدأ الربط وامسح QR من الهاتف الجديد. الفصل يوقف إرسال التذكيرات لحين ربط رقم، ولا يمسح إعدادات المشتركين أو سجل منع التكرار.</p>
            <button type="submit" :disabled="['idle', 'starting', 'disconnecting', 'unavailable'].includes(session.status)" class="bg-red-700 text-white font-bold px-5 py-2.5 rounded-xl disabled:opacity-50">فصل رقم الإرسال</button>
        </form>
        <p class="text-xs text-gray-400">تتحدث الحالة تلقائيًا كل خمس ثوانٍ. إذا بقيت حالة بدء الاتصال معلقة، راجع خدمة البروفايدر. إعدادات الخادم ومفاتيح الربط لا تظهر في المتصفح.</p>
    </section>
    <section class="max-w-3xl mx-auto mt-6 bg-gray-800 p-6 rounded-2xl border border-gray-700 space-y-4">
        <h3 class="text-lg font-bold text-white">نص رسالة التذكير للمشتركين</h3>
        <p class="text-sm text-gray-300">السوبر أدمن فقط يتحكم في النص. موعد كل مشترك وأيامه يُحددان من إعداداته بتوقيت القاهرة.</p>
        @if($errors->whatsappMessage->any())
            <ul role="alert" class="text-red-400 text-sm">
                @foreach($errors->whatsappMessage->all() as $message)<li>{{ $message }}</li>@endforeach
            </ul>
        @endif
        <form method="post" action="{{ route('admin.whatsapp.message.update') }}" class="space-y-4">
            @csrf
            @method('put')
            <label for="gym-message-template" class="block text-sm font-bold text-white">محتوى الرسالة</label>
            <textarea id="gym-message-template" name="text" required maxlength="1000" rows="5" class="w-full bg-gray-900 border-gray-700 text-white rounded-xl p-4 text-sm leading-relaxed">{{ old('text', $messageTemplate) }}</textarea>
            <p class="text-xs text-gray-400" dir="rtl">المتغيرات المتاحة: <bdi>{name}</bdi> الاسم · <bdi>{time}</bdi> الساعة · <bdi>{day}</bdi> اليوم · <bdi>{date}</bdi> التاريخ · <bdi>{timezone}</bdi> القاهرة.</p>
            <div class="bg-gray-900 border border-gray-700 rounded-xl p-4 space-y-2">
                <p class="text-xs text-gray-400">مثال للنص المحفوظ (المتغيرات تُستبدل تلقائيًا عند الإرسال):</p>
                <p class="text-sm text-gray-200 whitespace-pre-wrap">{{ strtr($messageTemplate, \App\Services\GymReminders\ReminderMessageTemplate::values('أحمد', \Carbon\CarbonImmutable::now('Africa/Cairo')->setTime(18, 0))) }}</p>
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold px-5 py-2.5 rounded-xl">حفظ نص الرسالة</button>
        </form>
    </section>
</x-app-layout>
