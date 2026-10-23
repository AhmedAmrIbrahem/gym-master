<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // 1. التحقق من حالة حساب المستخدم (مفعل أم معطل)
        if (! auth()->user()->is_active) {
            auth()->guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'هذا الحساب معطل حالياً. يرجى التواصل مع إدارة الجيم.',
            ]);
        }

        $request->session()->regenerate();

        // 2. توجيه السوبر أدمن تلقائياً لوحة الإحصائيات العامة
        if (auth()->user()->isSuperAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        // 3. توجيه المشترك العادي للداش بورد الخاصة به
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
