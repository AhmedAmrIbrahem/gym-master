<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-white flex items-center gap-2">
            <span>👥</span> إدارة المشتركين والحسابات
        </h2>
    </x-slot>


    <style>
        /* A separate mobile presentation: table semantics stay unchanged on desktop. */
        .gym-members-mobile { display: none; }
        .gym-members-mobile-pagination { display: none; }
        @media (max-width: 767px) {
            .gym-admin-users-page { min-width: 0; }
            .gym-admin-users-page > .bg-gray-800 { padding-left: 14px; padding-right: 14px; }
            .gym-members-heading { align-items: flex-start; flex-wrap: wrap; gap: 10px; }
            .gym-members-heading h3 { line-height: 1.8; }
            .gym-members-table-wrap, .gym-members-desktop-pagination { display: none !important; }
            .gym-members-mobile { display: grid !important; grid-template-columns: minmax(0,1fr); gap: 12px; padding: 12px; }
            .gym-member-card { min-width: 0; padding: 14px; border-radius: 15px;
                border: 1px solid #475569; background: #111827; color: #e5e7eb; }
            .gym-member-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
            .gym-member-identity { display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1; }
            .gym-member-photo { width: 45px; height: 45px; border-radius: 12px; flex: none;
                object-fit: cover; border: 1px solid #64748b; }
            .gym-member-name { margin: 0; font-size: 14px; font-weight: 800; line-height: 1.6;
                overflow-wrap: anywhere; }
            .gym-member-date { font-size: 10px; color: #94a3b8; }
            .gym-member-status-btn { min-height: 38px; padding: 6px 9px; border-radius: 10px;
                border: 1px solid #34d399; background: #064e3b; color: #a7f3d0;
                font-weight: 800; font-size: 11px; white-space: nowrap; }
            .gym-member-status-btn.is-inactive { border-color: #f87171; background: #7f1d1d; color: #fecaca; }
            .gym-member-contact { padding-top: 12px; margin-top: 12px; border-top: 1px solid #374151;
                display: grid; gap: 6px; }
            .gym-member-contact a { display: block; max-width: 100%; overflow-wrap: anywhere;
                word-break: break-word; direction: ltr; text-align: right; text-decoration: none; font-size: 12px; }
            .gym-member-contact .gym-member-email { color: #93c5fd; }
            .gym-member-contact .gym-member-phone { color: #6ee7b7; }
            .gym-member-stats { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: 8px; margin-top: 12px; }
            .gym-member-stat { display: flex; flex-direction: column; align-items: center; justify-content: center;
                min-width: 0; min-height: 68px; gap: 6px; border-radius: 10px; padding: 7px 3px;
                background: #1f2937; border: 1px solid #374151; text-align: center; }
            .gym-member-stat-label { font-size: 10px; color: #cbd5e1; }
            .gym-member-stat-value { font-size: 13px; font-weight: 800; color: #93c5fd; }
            .gym-member-stat-value.is-weight { color: #6ee7b7; }
            .gym-member-actions { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 8px; margin-top: 12px; }
            .gym-member-action { display: inline-flex; justify-content: center; align-items: center; gap: 5px;
                min-height: 44px; padding: 7px 6px; min-width: 0; text-align: center; border-radius: 10px;
                border: 1px solid #475569; font-size: 11px; font-weight: 800; text-decoration: none; }
            .gym-member-action.whatsapp { background: #064e3b; color: #a7f3d0; border-color: #047857; }
            .gym-member-action.edit { background: #1e3a8a; color: #bfdbfe; border-color: #2563eb; }
            .gym-member-action.password { background: #164e63; color: #a5f3fc; border-color: #0891b2; }
            .gym-member-action.delete { background: #7f1d1d; color: #fecaca; border-color: #dc2626; width: 100%; }
            .gym-members-mobile-pagination { display: flex; align-items: center; justify-content: space-between;
                flex-wrap: wrap; gap: 8px; padding: 13px; border-top: 1px solid #475569; }
            .gym-members-mobile-pagination a, .gym-members-mobile-pagination span.gym-pagination-disabled {
                display: inline-flex; align-items: center; justify-content: center; min-height: 42px;
                padding: 9px 13px; border-radius: 10px; text-decoration: none; font-size: 12px; font-weight: 800;
                background: #2563eb; color: white; }
            .gym-members-mobile-pagination .gym-pagination-disabled { opacity: .55; background: #374151; color: #cbd5e1; }
            .gym-members-mobile-pagination .gym-pagination-current { color: #cbd5e1; font-size: 12px; font-weight: 700; }
        }
        html[data-theme="light"] .gym-member-card { background: #fff; color: #1e293b; border-color: #dbe5f1; }
        html[data-theme="light"] .gym-member-date { color: #64748b; }
        html[data-theme="light"] .gym-member-contact { border-color: #e2e8f0; }
        html[data-theme="light"] .gym-member-email { color: #1d4ed8; }
        html[data-theme="light"] .gym-member-phone { color: #047857; }
        html[data-theme="light"] .gym-member-stat { background: #f1f5f9; border-color: #e2e8f0; }
        html[data-theme="light"] .gym-member-stat-label { color: #475569; }
        html[data-theme="light"] .gym-member-stat-value { color: #1d4ed8; }
        html[data-theme="light"] .gym-member-stat-value.is-weight { color: #047857; }
        html[data-theme="light"] .gym-member-status-btn { background: #ecfdf5; color: #047857; border-color: #6ee7b7; }
        html[data-theme="light"] .gym-member-status-btn.is-inactive { background: #fef2f2; color: #b91c1c; border-color: #fca5a5; }
        html[data-theme="light"] .gym-member-action.whatsapp { background: #ecfdf5; color: #047857; border-color: #6ee7b7; }
        html[data-theme="light"] .gym-member-action.edit { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        html[data-theme="light"] .gym-member-action.password { background: #ecfeff; color: #0e7490; border-color: #67e8f9; }
        html[data-theme="light"] .gym-member-action.delete { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
        html[data-theme="light"] .gym-members-mobile-pagination .gym-pagination-current { color: #475569; }
        html[data-theme="light"] .gym-members-mobile-pagination .gym-pagination-disabled { background: #f1f5f9; color: #64748b; }

        /* Mobile edit/password dialogs: keyboard-safe and internally scrollable. */
        .gym-admin-modal-overlay {
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }
        .gym-admin-modal-panel {
            max-height: min(calc(100dvh - 32px), calc(var(--gym-visual-viewport-height, 100dvh) - 24px));
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }
        @media (max-width: 767px) {
            .gym-admin-modal-overlay { align-items: flex-start !important;
                padding: 12px 12px max(12px, env(safe-area-inset-bottom, 0px)) !important; }
            .gym-admin-modal-panel { padding: 18px !important; border-radius: 17px !important; }
            .gym-admin-users-page input:not([type="checkbox"]),
            .gym-admin-users-page select,
            .gym-admin-users-page textarea { font-size: 16px !important; }
            .gym-admin-modal-panel input:not([type="checkbox"]),
            .gym-admin-modal-panel select { min-height: 44px; }
            .gym-admin-modal-panel form button { min-height: 44px; }
        }
    </style>

    <!-- إضافة مكتبة SweetAlert2 للإشعارات الجانبية -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- سكربت إظهار إشعارات التوست الجانبية الصغيرة والأنيقة -->
    @if(session('success'))
        <script>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '{{ session("success") }}',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                background: '#1f2937',
                color: '#fff',
                customClass: {
                    popup: 'rounded-xl border border-emerald-500/30 shadow-2xl p-3 text-xs',
                    title: 'text-xs font-bold text-white mr-2',
                    icon: 'scale-75'
                }
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: @js(session('error')),
                showConfirmButton: false,
                timer: 6500,
                background: '#1f2937',
                color: '#fff'
            });
        </script>
    @endif

    @if(session('new_member_credentials'))
        @php
            $newMember = session('new_member_credentials');
        @endphp
        <div id="member-credentials-notice" role="status" class="fixed top-5 left-5 right-5 sm:left-auto sm:right-5 z-[100] sm:w-[430px] bg-gray-800 text-white rounded-2xl border border-blue-500/50 shadow-2xl p-5 space-y-3" dir="rtl">
            <div class="flex items-center justify-between gap-3">
                <strong class="text-sm text-emerald-300">بيانات الدخول الجديدة — متاحة لمدة 30 ثانية</strong>
                <button type="button" id="dismiss-member-credentials" class="text-gray-300 hover:text-white" aria-label="إغلاق">✕</button>
            </div>
            <p class="text-xs text-gray-300">{{ $newMember['name'] }} — اضغط إرسال واتساب لتجهيز رسالة الدخول بكلمة المرور الجديدة.</p>
            <p class="text-xs">كلمة المرور: <span class="text-emerald-300 font-mono select-all" dir="ltr">{{ $newMember['password'] }}</span></p>
            <div class="flex items-center gap-2">
                <a id="send-member-credentials-whatsapp" href="#" target="_blank" rel="noopener noreferrer" class="bg-emerald-600 hover:bg-emerald-500 px-3 py-2 rounded-xl text-xs font-bold">إرسال واتساب</a>
                <button type="button" id="copy-member-credentials" class="bg-blue-600 hover:bg-blue-500 px-3 py-2 rounded-xl text-xs font-bold">نسخ كلمة المرور</button>
                <span id="member-credentials-countdown" class="text-xs text-gray-400">30 ثانية</span>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const card = document.getElementById('member-credentials-notice');
                const dismiss = document.getElementById('dismiss-member-credentials');
                const copy = document.getElementById('copy-member-credentials');
                const countdown = document.getElementById('member-credentials-countdown');
                const sendWhatsapp = document.getElementById('send-member-credentials-whatsapp');
                const credentials = @js($newMember);
                const message = `مرحباً ${credentials.name}\nبيانات دخول Gym Master\n\nالبريد الإلكتروني: ${credentials.email}\nكلمة المرور: ${credentials.password}\n\nرابط تسجيل الدخول:\n${credentials.login_url}`;
                // The password is only available immediately after creation or an admin reset.
                // Match the member by ID so pagination and duplicate phone formatting cannot
                // cause a password to be sent to the wrong member.
                const whatsappLinks = Array.from(document.querySelectorAll('a[data-member-id]'))
                    .filter(link => String(link.dataset.memberId) === String(credentials.id));
                let digits = String(credentials.phone || '').replace(/\D/g, '');
                if (digits.startsWith('01')) digits = `2${digits}`;
                else if (digits.startsWith('00')) digits = digits.slice(2);
                const credentialsUrl = `https://wa.me/${digits}?text=${encodeURIComponent(message)}`;
                if (sendWhatsapp) sendWhatsapp.href = credentialsUrl;
                const originalWhatsappUrls = whatsappLinks.map(link => link.href);
                whatsappLinks.forEach(link => { link.href = credentialsUrl; });
                let remaining = 30;
                const close = () => {
                    card?.remove();
                    if (sendWhatsapp) sendWhatsapp.removeAttribute('href');
                    whatsappLinks.forEach((link, index) => {
                        if (originalWhatsappUrls[index]) link.href = originalWhatsappUrls[index];
                    });
                    clearInterval(interval);
                };
                dismiss?.addEventListener('click', close);
                copy?.addEventListener('click', async () => {
                    try {
                        await navigator.clipboard.writeText(credentials.password);
                        copy.textContent = 'تم النسخ';
                    } catch (_) {
                        const field = document.createElement('textarea');
                        field.value = credentials.password;
                        document.body.appendChild(field);
                        field.select();
                        const success = document.execCommand('copy');
                        field.remove();
                        if (success) copy.textContent = 'تم النسخ';
                    }
                });
                const interval = setInterval(() => {
                    remaining -= 1;
                    if (remaining <= 0) close();
                    else countdown.textContent = `${remaining} ثانية`;
                }, 1000);
            });
        </script>
    @endif

    @if($errors->any())
        @php
            $firstError = $errors->first();
        @endphp
        <script>
            const rawError = @js($firstError);
            let arabicError = rawError;
            if (rawError.includes('already been taken')) {
                if (rawError.includes('email')) {
                    arabicError = 'عذراً، البريد الإلكتروني مسجل مسبقاً!';
                } else if (rawError.includes('phone')) {
                    arabicError = 'عذراً، رقم الهاتف مسجل مسبقاً!';
                } else {
                    arabicError = 'هذه البيانات مسجلة مسبقاً.';
                }
            }

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: arabicError,
                showConfirmButton: false,
                timer: 4500,
                timerProgressBar: true,
                background: '#1f2937',
                color: '#fff',
                customClass: {
                    popup: 'rounded-xl border border-red-500/40 shadow-2xl p-3 text-xs',
                    title: 'text-xs font-bold text-white mr-2',
                    icon: 'scale-75'
                }
            });
        </script>
    @endif

    <div class="space-y-6 gym-admin-users-page" x-data="{ editModalOpen: false, passwordModalOpen: false, currentUser: {}, passwordUser: {} }">



        <!-- نموذج إضافة مشترك جديد -->
        <div class="bg-gray-800 p-6 rounded-2xl border border-gray-700 shadow-xl space-y-4">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                ➕ إضافة مشترك جديد
            </h3>

            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">الاسم الكامل</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="أحمد محمد" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-xs p-3">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">البريد الإلكتروني</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="user@gym.com" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-xs p-3">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">رقم الهاتف</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="01012345678" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-xs p-3">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">كلمة المرور</label>
                        <input type="password" name="password" minlength="8" autocomplete="new-password" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-xs p-3">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">تأكيد كلمة المرور</label>
                        <input type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-xs p-3">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold px-6 py-2.5 rounded-xl text-xs shadow-lg transition">
                        💾 إنشاء حساب المشترك
                    </button>
                </div>
            </form>
        </div>

        <!-- جدول قائمة المشتركين والإحصائيات -->
        <div class="bg-gray-800 rounded-2xl border border-gray-700 shadow-xl overflow-hidden">
            <div class="p-4 bg-gray-900/80 border-b border-gray-700 flex justify-between items-center gym-members-heading">
                <h3 class="text-sm font-bold text-white">قائمة المشتركين وإحصائيات النشاط</h3>
                <span class="text-xs bg-blue-600/20 text-blue-400 font-bold px-3 py-1 rounded-lg">إجمالي المشتركين: {{ $users->total() }}</span>
            </div>

            <div class="overflow-x-auto gym-members-table-wrap">
                <table class="w-full text-right text-xs text-gray-300">
                    <thead class="bg-gray-900/60 text-gray-400">
                    <tr>
                        <th class="p-3">المشترك</th>
                        <th class="p-3">بيانات التواصل</th>
                        <th class="p-3 text-center">🏋️‍♂️ التمارين المسجلة</th>
                        <th class="p-3 text-center">🥗 الأيام المتبعة</th>
                        <th class="p-3 text-center">⚖️ أحدث وزن</th>
                        <th class="p-3 text-center">الحالة</th>
                        <th class="p-3 text-center">إجراءات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/40">
                    @forelse($users as $u)
                        <tr class="hover:bg-gray-700/20">
                            <td class="p-3 font-bold text-white flex items-center gap-2">
                                <img src="{{ $u->avatar_url }}" class="w-8 h-8 rounded-lg object-cover border border-gray-600">
                                <div>
                                    <p class="text-white">{{ $u->name }}</p>
                                    <span class="text-[10px] text-gray-400">منذ {{ $u->created_at->format('Y/m/d') }}</span>
                                </div>
                            </td>
                            <td class="p-3">
                                <p class="text-blue-400 font-bold">{{ $u->email }}</p>
                                <p class="text-emerald-400 font-bold mt-0.5">{{ $u->phone }}</p>
                            </td>
                            <td class="p-3 text-center">
                                <span class="bg-blue-900/40 text-blue-300 font-black px-2.5 py-1 rounded-lg border border-blue-700/50">
                                    {{ $u->workout_sessions_count }} جلسة
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <span class="bg-amber-900/40 text-amber-300 font-black px-2.5 py-1 rounded-lg border border-amber-700/50">
                                    {{ $u->daily_meals_count }} يوم
                                </span>
                            </td>
                            <td class="p-3 text-center font-extrabold text-emerald-400">
                                {{ $u->latestMeasurement->weight ?? '--' }} <span class="text-[10px] text-gray-400">كجم</span>
                            </td>
                            <td class="p-3 text-center">
                                <form action="{{ route('admin.users.toggle-status', $u->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-[11px] font-bold px-3 py-1 rounded-lg border transition {{ $u->is_active ? 'bg-emerald-900/40 text-emerald-300 border-emerald-500/50 hover:bg-emerald-800/60' : 'bg-red-900/40 text-red-300 border-red-500/50 hover:bg-red-800/60' }}">
                                        {{ $u->is_active ? 'مفعل ✓' : 'معطل ✕' }}
                                    </button>
                                </form>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @php
                                        $formattedPhone = preg_replace('/[^0-9]/', '', $u->phone);
                                        if(str_starts_with($formattedPhone, '01')) {
                                            $formattedPhone = '2' . $formattedPhone;
                                        }
                                        $waMessage = rawurlencode("مرحباً {$u->name} 👋\nتذكير ببيانات حسابك في Gym Master:\n\nالبريد الإلكتروني: {$u->email}\nرقم الهاتف: {$u->phone}\n\nرابط تسجيل الدخول:\n" . route('login'));
                                    @endphp

                                    <a href="https://wa.me/{{ $formattedPhone }}?text={{ $waMessage }}" data-member-id="{{ $u->id }}" data-member-email="{{ $u->email }}" data-member-phone="{{ $formattedPhone }}" target="_blank" rel="noopener noreferrer" title="إرسال تذكير بالحساب عبر الواتساب" class="text-emerald-400 hover:text-emerald-300 bg-emerald-600/10 p-2 rounded-lg font-bold flex items-center gap-1">
                                        💬 واتساب
                                    </a>

                                    <button @click="currentUser = {{ json_encode($u) }}; editModalOpen = true" class="text-blue-400 hover:text-blue-300 bg-blue-600/10 p-2 rounded-lg font-bold">
                                        ✏️ تعديل
                                    </button>

                                    <button type="button" @click="passwordUser = { id: {{ $u->id }}, name: @js($u->name) }; passwordModalOpen = true" title="تغيير كلمة مرور المشترك" class="text-sky-300 bg-sky-600/10 p-2 rounded-lg font-bold">🔐 تغيير كلمة المرور</button>

                                    <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('هل أنت تأكد من حذف هذا المشترك؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 bg-red-600/10 p-2 rounded-lg font-bold">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-gray-500 italic">لا يوجد مشتركين مسجلين بعد.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>


            <!-- Mobile cards: never squeeze seven table columns into a phone viewport. -->
            <div class="gym-members-mobile" aria-label="قائمة المشتركين على الموبايل">
                @forelse($users as $u)
                    <article class="gym-member-card">
                        <div class="gym-member-head">
                            <div class="gym-member-identity">
                                <img src="{{ $u->avatar_url }}" alt="" class="gym-member-photo">
                                <div class="min-w-0">
                                    <h4 class="gym-member-name">{{ $u->name }}</h4>
                                    <div class="gym-member-date">عضو منذ {{ $u->created_at->format('Y/m/d') }}</div>
                                </div>
                            </div>
                            <form action="{{ route('admin.users.toggle-status', $u->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="gym-member-status-btn {{ $u->is_active ? '' : 'is-inactive' }}" title="تغيير حالة الحساب">
                                    {{ $u->is_active ? 'مفعل ✓' : 'معطل ✕' }}
                                </button>
                            </form>
                        </div>

                        <div class="gym-member-contact">
                            <a class="gym-member-email" href="mailto:{{ $u->email }}">{{ $u->email }}</a>
                            <a class="gym-member-phone" href="tel:{{ $u->phone }}">{{ $u->phone }}</a>
                        </div>

                        <div class="gym-member-stats">
                            <div class="gym-member-stat"><span class="gym-member-stat-label">التمارين</span><span class="gym-member-stat-value">{{ $u->workout_sessions_count }} جلسة</span></div>
                            <div class="gym-member-stat"><span class="gym-member-stat-label">أيام التغذية</span><span class="gym-member-stat-value">{{ $u->daily_meals_count }} يوم</span></div>
                            <div class="gym-member-stat"><span class="gym-member-stat-label">آخر وزن</span><span class="gym-member-stat-value is-weight">{{ $u->latestMeasurement->weight ?? '--' }} كجم</span></div>
                        </div>

                        @php
                            $mobilePhone = preg_replace('/[^0-9]/', '', $u->phone);
                            if (str_starts_with($mobilePhone, '01')) {
                                $mobilePhone = '2' . $mobilePhone;
                            }
                            $mobileWaMessage = rawurlencode("مرحباً {$u->name} 👋\nتذكير ببيانات حسابك في Gym Master:\n\nالبريد الإلكتروني: {$u->email}\nرقم الهاتف: {$u->phone}\n\nرابط تسجيل الدخول:\n" . route('login'));
                        @endphp
                        <div class="gym-member-actions">
                            <a href="https://wa.me/{{ $mobilePhone }}?text={{ $mobileWaMessage }}" data-member-id="{{ $u->id }}" data-member-email="{{ $u->email }}" data-member-phone="{{ $mobilePhone }}" target="_blank" rel="noopener noreferrer" class="gym-member-action whatsapp">💬 واتساب</a>
                            <button type="button" @click="currentUser = {{ json_encode($u) }}; editModalOpen = true" class="gym-member-action edit">✏️ تعديل الحساب</button>
                            <button type="button" @click="passwordUser = { id: {{ $u->id }}, name: @js($u->name) }; passwordModalOpen = true" class="gym-member-action password">🔐 كلمة المرور</button>
                            <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا المشترك؟')" class="min-w-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="gym-member-action delete">🗑️ حذف الحساب</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <p class="p-5 text-center text-gray-400">لا يوجد مشتركين مسجلين بعد.</p>
                @endforelse
            </div>

            @if($users->hasPages())
                <nav class="gym-members-mobile-pagination" aria-label="صفحات المشتركين">
                    @if($users->onFirstPage())
                        <span class="gym-pagination-disabled">السابق</span>
                    @else
                        <a href="{{ $users->previousPageUrl() }}" rel="prev">السابق</a>
                    @endif
                    <span class="gym-pagination-current">{{ $users->currentPage() }} / {{ $users->lastPage() }}</span>
                    @if($users->hasMorePages())
                        <a href="{{ $users->nextPageUrl() }}" rel="next">التالي</a>
                    @else
                        <span class="gym-pagination-disabled">التالي</span>
                    @endif
                </nav>
            @endif

            <div class="p-4 gym-members-desktop-pagination">
                {{ $users->links() }}
            </div>
        </div>

        <!-- Modal تعديل المشترك وكلمة المرور -->
        <div x-show="editModalOpen" x-cloak class="gym-admin-modal-overlay fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div @click.away="editModalOpen = false" class="gym-admin-modal-panel bg-gray-800 border border-gray-700 rounded-2xl w-full max-w-lg p-6 space-y-4 shadow-2xl">
                <div class="flex justify-between items-center border-b border-gray-700 pb-3">
                    <h3 class="text-base font-bold text-white">✏️ تعديل حساب المشترك</h3>
                    <button @click="editModalOpen = false" class="text-gray-400 hover:text-white font-bold">✕</button>
                </div>

                <form :action="'/admin/users/' + currentUser.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">الاسم الكامل</label>
                        <input type="text" name="name" x-model="currentUser.name" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-xs p-3">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">البريد الإلكتروني</label>
                        <input type="email" name="email" x-model="currentUser.email" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-xs p-3">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">رقم الهاتف</label>
                        <input type="text" name="phone" x-model="currentUser.phone" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-xs p-3">
                    </div>
                    <p class="text-xs text-sky-300">لتغيير كلمة المرور استخدم زر تغيير كلمة المرور من قائمة المشتركين.</p>

                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">حالة الحساب</label>
                        <select name="is_active" x-model="currentUser.is_active" class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-xs p-3">
                            <option :value="1">مفعل</option>
                            <option :value="0">معطل</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-gray-700">
                        <button type="button" @click="editModalOpen = false" class="bg-gray-700 text-gray-300 font-bold px-4 py-2 rounded-xl text-xs">إلغاء</button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold px-6 py-2 rounded-xl text-xs shadow-lg">حفظ التغييرات</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- نافذة تعيين كلمة مرور بواسطة السوبر أدمن -->
        <div x-show="passwordModalOpen" x-cloak class="gym-admin-modal-overlay fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div @click.away="passwordModalOpen = false" class="gym-admin-modal-panel bg-gray-800 border border-gray-700 rounded-2xl w-full max-w-lg p-6 space-y-4 shadow-2xl">
                <div class="flex justify-between items-center border-b border-gray-700 pb-3">
                    <h3 class="text-base font-bold text-white">🔐 تغيير كلمة مرور المشترك</h3>
                    <button type="button" @click="passwordModalOpen = false" class="text-gray-400 hover:text-white font-bold">✕</button>
                </div>
                <p class="text-xs text-gray-300" x-text="passwordUser.name"></p>
                <form :action="'{{ url('/admin/users') }}/' + passwordUser.id + '/reset-password'" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">كلمة المرور الجديدة</label>
                        <input type="password" name="password" minlength="8" autocomplete="new-password" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-xs p-3">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">تأكيد كلمة المرور الجديدة</label>
                        <input type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-xs p-3">
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t border-gray-700">
                        <button type="button" @click="passwordModalOpen = false" class="bg-gray-700 text-gray-300 font-bold px-4 py-2 rounded-xl text-xs">إلغاء</button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold px-6 py-2 rounded-xl text-xs shadow-lg">حفظ كلمة المرور</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
