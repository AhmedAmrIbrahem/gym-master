<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeUserMail;
use App\Models\User;
use App\Models\WorkoutSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    // 1. صفحة الإحصائيات العامة للسوبر أدمن
    public function dashboard()
    {
        Carbon::setLocale('ar');

        $totalUsers = User::where('role', 'user')->count();
        $activeUsers = User::where('role', 'user')->where('is_active', true)->count();
        $inactiveUsers = User::where('role', 'user')->where('is_active', false)->count();

        $totalSessions = WorkoutSession::count();
        $thisWeekSessions = WorkoutSession::where('date', '>=', now()->startOfWeek())->count();

        // بيانات الرسم البياني للمشتركين الجدد (آخر 6 أشهر)
        $userGrowthLabels = [];
        $userGrowthData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $userGrowthLabels[] = $date->locale('ar')->translatedFormat('M Y');
            $userGrowthData[] = User::where('role', 'user')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        }

        // أحدث 5 مشتركين مسجلين
        $latestUsers = User::where('role', 'user')
            ->withCount(['workoutSessions', 'dailyMeals'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeUsers',
            'inactiveUsers',
            'totalSessions',
            'thisWeekSessions',
            'userGrowthLabels',
            'userGrowthData',
            'latestUsers'
        ));
    }

    // 2. صفحة جدول وإدارة المشتركين
    public function index()
    {
        $users = User::where('role', 'user')
            ->withCount(['workoutSessions', 'dailyMeals'])
            ->with(['latestMeasurement'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    // The super admin sets the member's initial password directly.
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ]);

        $plainPassword = $validated['password'];
        $validated['password'] = Hash::make($plainPassword);
        unset($validated['password_confirmation']);
        $validated['role'] = 'user';
        $validated['is_active'] = true;
        $user = User::create($validated);

        // Keep credentials for one redirect only so the admin can copy them.
        $redirect = back()->with('new_member_credentials', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'password' => $plainPassword,
            'login_url' => route('login'),
        ]);

        try {
            Mail::to($user->email)->send(new WelcomeUserMail($user, $plainPassword));

            return $redirect->with('success', 'تم إنشاء المشترك وإرسال بيانات الدخول إلى بريده الإلكتروني.');
        } catch (\Throwable $exception) {
            report($exception);

            return $redirect->with('error', 'تم إنشاء المشترك، لكن تعذر إرسال رسالة البريد. يمكنك نسخ بيانات الدخول من البطاقة الظاهرة.');
        }
    }

    public function update(Request $request, User $user)
    {
        abort_if($user->isSuperAdmin(), 403);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'required|string|unique:users,phone,'.$user->id,
            'is_active' => 'required|boolean',
        ]);

        $user->update($validated);

        return back()->with('success', 'تم تحديث بيانات المشترك بنجاح.');
    }

    // تغيير حالة الحساب (مفعل / غير مفعل)
    public function toggleStatus(User $user)
    {
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'لا يمكن تعديل حالة السوبر أدمن.');
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        $statusText = $user->is_active ? 'تفعيل' : 'إيقاف';

        return back()->with('success', "تم {$statusText} حساب المشترك بنجاح.");
    }

    // حذف مشترك
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        abort_if($user->isSuperAdmin(), 403);
        $user->delete();

        return redirect()->back()->with('success', 'تم حذف المشترك بنجاح!');
    }

    public function resetPassword(Request $request, User $user)
    {
        abort_if($user->isSuperAdmin(), 403);

        $validated = $request->validate([
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ]);

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ])->save();

        return back()
            ->with('success', 'تم تغيير كلمة مرور المشترك بنجاح.')
            ->with('new_member_credentials', [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'password' => $validated['password'],
                'login_url' => route('login'),
            ]);
    }
}
