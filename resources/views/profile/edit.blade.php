<x-app-layout>
    <style>
        .gym-profile-avatar-input::file-selector-button {
            background:#2563eb; color:#fff; font-weight:800; border:0;
            border-radius:10px; padding:10px 12px; margin-inline-end:12px;
            font-family:inherit; cursor:pointer;
        }
        .gym-profile-avatar-input::file-selector-button:hover { background:#3b82f6; }
    </style>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-white flex items-center gap-2">
            <span>👤</span> الملف الشخصي والإعدادات
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">

        <!-- 1. كارت الهوية والإحصائيات السريعة -->
        <div class="bg-gray-800 p-4 sm:p-6 rounded-2xl border border-gray-700 shadow-xl flex flex-col md:flex-row items-stretch md:items-center justify-between gap-5 md:gap-6 min-w-0">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0 w-full md:w-auto">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl overflow-hidden border-2 border-blue-500 shrink-0 shadow-lg">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-xl sm:text-2xl font-black text-white break-words">{{ $user->name }}</h3>
                    <p class="text-xs text-gray-400 mt-1 break-all" dir="ltr">{{ $user->email }}</p>
                    @unless($user->isSuperAdmin())
                        <span class="inline-block mt-2 text-[10px] bg-gray-900 text-gray-400 border border-gray-700 px-3 py-1 rounded-full">
                            🗓️ عضويتك منذ: {{ $user->created_at->format('Y-m-d') }}
                        </span>
                    @endunless
                </div>
            </div>

            @unless($user->isSuperAdmin())
                <div class="flex gap-3 w-full md:w-auto">
                    <div class="bg-gray-900/80 border border-gray-700/80 p-3.5 rounded-xl text-center flex-1 md:w-32">
                        <p class="text-[10px] text-gray-400 font-bold">إجمالي الجلسات</p>
                        <p class="text-xl font-extrabold text-blue-400 mt-1">{{ $totalSessions ?? 0 }}</p>
                    </div>
                    <div class="bg-gray-900/80 border border-gray-700/80 p-3.5 rounded-xl text-center flex-1 md:w-36">
                        <p class="text-[10px] text-gray-400 font-bold">النظام الحالي</p>
                        <p class="text-sm font-extrabold text-emerald-400 truncate mt-1">
                            {{ $activeProgram->name ?? 'غير محدد' }}
                        </p>
                    </div>
                </div>
            @endunless
        </div>

        <!-- 2. تعديل البيانات الشخصية والصورة، والأهداف للمشتركين فقط -->
        <div class="bg-gray-800 p-4 sm:p-6 rounded-2xl border border-gray-700 shadow-xl space-y-6 min-w-0">
            <h3 class="text-lg font-bold text-white border-b border-gray-700 pb-3 flex items-center gap-2">
                <span>⚙️</span> {{ $user->isSuperAdmin() ? 'تعديل البيانات الشخصية' : 'تعديل البيانات والأهداف اليومية' }}
            </h3>

            <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('patch')

                <!-- رفع صورة جديدة -->
                <div>
                    <label for="avatar" class="block text-xs font-bold text-gray-300 mb-2">الصورة الشخصية</label>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 min-w-0">
                        <img src="{{ $user->avatar_url }}" alt="الصورة الشخصية الحالية" class="w-12 h-12 shrink-0 rounded-xl object-cover border border-gray-700">
                        <input id="avatar" name="avatar" type="file" accept="image/*"
                               class="gym-profile-avatar-input block w-full min-w-0 max-w-full text-sm text-gray-400 file:mr-2 file:py-3 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-500 bg-gray-900 border border-gray-700 rounded-xl cursor-pointer">
                    </div>
                    <x-input-error class="mt-2 text-xs text-red-400" :messages="$errors->get('avatar')" />
                </div>

                <!-- الاسم والإيميل -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-xs font-bold text-gray-300 mb-1">الاسم الكامل</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name"
                               class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                        <x-input-error class="mt-2 text-xs text-red-400" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold text-gray-300 mb-1">البريد الإلكتروني</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                               class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                        <x-input-error class="mt-2 text-xs text-red-400" :messages="$errors->get('email')" />
                    </div>
                </div>

                @unless($user->isSuperAdmin())
                <!-- قسم أهداف السعرات والماكروز اليومية (أيام الجيم vs أيام الراحة) -->
                <div class="pt-4 border-t border-gray-700/80 space-y-4">
                    <div>
                        <h4 class="text-sm font-bold text-white flex items-center gap-2">
                            🎯 أهداف السعرات والماكروز (أيام الجيم vs أيام الراحة)
                        </h4>
                        <p class="text-xs text-gray-400 mt-0.5">
                            حدد مستهدفات السعرات والماكروز المختلفة لأيام التمرين وأيام الراحة ليتم حساب التقدم والنسب تلقائياً.
                        </p>
                    </div>

                    <!-- 🏋️‍♂️ أهداف أيام التمرين (الجيم) -->
                    <div class="bg-gray-900/60 p-4 rounded-xl border border-blue-500/30 space-y-3">
                        <h5 class="text-xs font-bold text-blue-400 flex items-center gap-1">🏋️‍♂️ أهداف أيام الجيم (Workout Days)</h5>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-300 mb-1">🔥 سعرات الجيم</label>
                                <input type="number" name="target_calories" value="{{ old('target_calories', $user->target_calories ?? 2500) }}" class="w-full bg-gray-800 border-gray-700 text-amber-400 font-bold rounded-xl text-xs p-2.5">
                                <x-input-error class="mt-1 text-xs text-red-400" :messages="$errors->get('target_calories')" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-300 mb-1">🥩 بروتين (جم)</label>
                                <input type="number" step="0.1" name="target_protein" value="{{ old('target_protein', $user->target_protein ?? 160) }}" class="w-full bg-gray-800 border-gray-700 text-emerald-400 font-bold rounded-xl text-xs p-2.5">
                                <x-input-error class="mt-1 text-xs text-red-400" :messages="$errors->get('target_protein')" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-300 mb-1">🍞 كارب (جم)</label>
                                <input type="number" step="0.1" name="target_carbs" value="{{ old('target_carbs', $user->target_carbs ?? 250) }}" class="w-full bg-gray-800 border-gray-700 text-blue-400 font-bold rounded-xl text-xs p-2.5">
                                <x-input-error class="mt-1 text-xs text-red-400" :messages="$errors->get('target_carbs')" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-300 mb-1">🥑 دهون (جم)</label>
                                <input type="number" step="0.1" name="target_fats" value="{{ old('target_fats', $user->target_fats ?? 60) }}" class="w-full bg-gray-800 border-gray-700 text-purple-400 font-bold rounded-xl text-xs p-2.5">
                                <x-input-error class="mt-1 text-xs text-red-400" :messages="$errors->get('target_fats')" />
                            </div>
                        </div>
                    </div>

                    <!-- 😴 أهداف أيام الراحة (Rest Days) -->
                    <div class="bg-gray-900/60 p-4 rounded-xl border border-purple-500/30 space-y-3">
                        <h5 class="text-xs font-bold text-purple-400 flex items-center gap-1">😴 أهداف أيام الراحة (Rest Days)</h5>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-300 mb-1">🔥 سعرات الراحة</label>
                                <input type="number" name="rest_target_calories" value="{{ old('rest_target_calories', $user->rest_target_calories ?? 2000) }}" class="w-full bg-gray-800 border-gray-700 text-amber-400 font-bold rounded-xl text-xs p-2.5">
                                <x-input-error class="mt-1 text-xs text-red-400" :messages="$errors->get('rest_target_calories')" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-300 mb-1">🥩 بروتين (جم)</label>
                                <input type="number" step="0.1" name="rest_target_protein" value="{{ old('rest_target_protein', $user->rest_target_protein ?? 160) }}" class="w-full bg-gray-800 border-gray-700 text-emerald-400 font-bold rounded-xl text-xs p-2.5">
                                <x-input-error class="mt-1 text-xs text-red-400" :messages="$errors->get('rest_target_protein')" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-300 mb-1">🍞 كارب (جم)</label>
                                <input type="number" step="0.1" name="rest_target_carbs" value="{{ old('rest_target_carbs', $user->rest_target_carbs ?? 180) }}" class="w-full bg-gray-800 border-gray-700 text-blue-400 font-bold rounded-xl text-xs p-2.5">
                                <x-input-error class="mt-1 text-xs text-red-400" :messages="$errors->get('rest_target_carbs')" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-300 mb-1">🥑 دهون (جم)</label>
                                <input type="number" step="0.1" name="rest_target_fats" value="{{ old('rest_target_fats', $user->rest_target_fats ?? 65) }}" class="w-full bg-gray-800 border-gray-700 text-purple-400 font-bold rounded-xl text-xs p-2.5">
                                <x-input-error class="mt-1 text-xs text-red-400" :messages="$errors->get('rest_target_fats')" />
                            </div>
                        </div>
                    </div>
                </div>

                @endunless

                <div class="flex items-center gap-4 pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold px-6 py-2.5 rounded-xl text-xs transition shadow-lg">
                        {{ $user->isSuperAdmin() ? '💾 حفظ البيانات' : '💾 حفظ البيانات والأهداف' }}
                    </button>
                </div>
            </form>
        </div>

        @if($user->role === 'user')
            @include('profile.partials.gym-reminder')
        @endif

        <!-- 3. تغيير كلمة المرور -->
        <div class="bg-gray-800 p-6 rounded-2xl border border-gray-700 shadow-xl space-y-4">
            <h3 class="text-lg font-bold text-white border-b border-gray-700 pb-3 flex items-center gap-2">
                <span>🔒</span> تغيير كلمة المرور
            </h3>

            <form method="post" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                @method('put')

                <div>
                    <label for="update_password_current_password" class="block text-xs font-bold text-gray-300 mb-1">كلمة المرور الحالية</label>
                    <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                           class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                    <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2 text-xs text-red-400" />
                </div>

                <div>
                    <label for="update_password_password" class="block text-xs font-bold text-gray-300 mb-1">كلمة المرور الجديدة</label>
                    <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                           class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                    <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2 text-xs text-red-400" />
                </div>

                <div>
                    <label for="update_password_password_confirmation" class="block text-xs font-bold text-gray-300 mb-1">تأكيد كلمة المرور الجديدة</label>
                    <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                           class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                    <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2 text-xs text-red-400" />
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold px-6 py-2.5 rounded-xl text-xs transition shadow-lg">
                        تحديث كلمة المرور
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
