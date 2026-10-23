<x-guest-layout>
    <div class="bg-gray-800 border border-gray-700 p-8 rounded-3xl shadow-2xl space-y-6">

        <!-- اللوجو والعنوان -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600/20 border border-blue-500 text-3xl rounded-2xl mb-2">
                🏋️‍♂️
            </div>
            <h2 class="text-2xl font-extrabold text-white">GYM DASHBOARD</h2>
            <p class="text-xs text-gray-400">سجّل دخولك لمتابعة وتحديث تمارينك وأوزانك</p>
        </div>

        <!-- رسائل الأخطاء -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <!-- البريد الإلكتروني -->
            <div>
                <label for="email" class="block text-xs font-bold text-gray-300 mb-1">البريد الإلكتروني</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="w-full bg-gray-900 border-gray-700 text-white placeholder-gray-500 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400 text-xs" />
            </div>

            <!-- كلمة المرور -->
            <div>
                <label for="password" class="block text-xs font-bold text-gray-300 mb-1">كلمة المرور</label>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       class="w-full bg-gray-900 border-gray-700 text-white placeholder-gray-500 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400 text-xs" />
            </div>

            <!-- تذكرني -->
            <div class="flex items-center justify-between text-xs text-gray-400">
                <label for="remember_me" class="inline-flex items-center cursor-pointer">
                    <input id="remember_me" type="checkbox" class="rounded bg-gray-900 border-gray-700 text-blue-600 focus:ring-blue-500" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span class="ms-2">تذكر بياناتي</span>
                </label>
            </div>

            <!-- زر الدخول -->
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-extrabold py-3.5 rounded-xl shadow-lg transition text-sm">
                تسجيل الدخول 🚀
            </button>
        </form>
    </div>
</x-guest-layout>
