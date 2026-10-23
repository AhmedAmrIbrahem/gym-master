<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $activeProgram = $user->workoutPrograms()->where('is_active', true)->first();
        $totalSessions = $user->workoutSessions()->count();

        return view('profile.edit', compact('user', 'activeProgram', 'totalSessions'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$request->user()->id],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'], // التحقق من أن الملف صورة وبحجم مناسب
            'target_calories' => ['required', 'integer', 'min:500', 'max:10000'],
            'target_protein' => ['required', 'numeric', 'min:0', 'max:1000'],
            'target_carbs' => ['required', 'numeric', 'min:0', 'max:1000'],
            'target_fats' => ['required', 'numeric', 'min:0', 'max:1000'],
            'rest_target_calories' => ['nullable', 'integer', 'min:500', 'max:10000'],
            'rest_target_protein' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'rest_target_carbs' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'rest_target_fats' => ['nullable', 'numeric', 'min:0', 'max:1000'],
        ]);

        $user = $request->user();

        // معالجة رفع الصورة الشخصية الجديدة
        if ($request->hasFile('avatar')) {
            // حذف الصورة القديمة إن وجدت لتوفير مساحة السيرفر
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // حفظ الصورة الجديدة في مجلد avatars داخل public storage
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'تم تحديث الملف الشخصي والصورة بنجاح! 🚀');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
