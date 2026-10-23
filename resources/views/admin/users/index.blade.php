<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-white flex items-center gap-2">
            <span>👥</span> إدارة المشتركين والحسابات
        </h2>
    </x-slot>

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
                const whatsappLink = Array.from(document.querySelectorAll('a[data-member-id]'))
                    .find(link => String(link.dataset.memberId) === String(credentials.id));
                let digits = String(credentials.phone || '').replace(/\D/g, '');
                if (digits.startsWith('01')) digits = `2${digits}`;
                else if (digits.startsWith('00')) digits = digits.slice(2);
                const credentialsUrl = `https://wa.me/${digits}?text=${encodeURIComponent(message)}`;
                if (sendWhatsapp) sendWhatsapp.href = credentialsUrl;
                const originalWhatsappUrl = whatsappLink?.href;
                if (whatsappLink) whatsappLink.href = credentialsUrl;
                let remaining = 30;
                const close = () => {
                    card?.remove();
                    if (sendWhatsapp) sendWhatsapp.removeAttribute('href');
                    if (whatsappLink && originalWhatsappUrl) whatsappLink.href = originalWhatsappUrl;
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

    <div class="space-y-6" x-data="{ editModalOpen: false, passwordModalOpen: false, currentUser: {}, passwordUser: {} }">



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
            <div class="p-4 bg-gray-900/80 border-b border-gray-700 flex justify-between items-center">
                <h3 class="text-sm font-bold text-white">قائمة المشتركين وإحصائيات النشاط</h3>
                <span class="text-xs bg-blue-600/20 text-blue-400 font-bold px-3 py-1 rounded-lg">إجمالي المشتركين: {{ $users->total() }}</span>
            </div>

            <div class="overflow-x-auto">
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

            <div class="p-4">
                {{ $users->links() }}
            </div>
        </div>

        <!-- Modal تعديل المشترك وكلمة المرور -->
        <div x-show="editModalOpen" x-cloak class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div @click.away="editModalOpen = false" class="bg-gray-800 border border-gray-700 rounded-2xl w-full max-w-lg p-6 space-y-4 shadow-2xl">
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
        <div x-show="passwordModalOpen" x-cloak class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div @click.away="passwordModalOpen = false" class="bg-gray-800 border border-gray-700 rounded-2xl w-full max-w-lg p-6 space-y-4 shadow-2xl">
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
