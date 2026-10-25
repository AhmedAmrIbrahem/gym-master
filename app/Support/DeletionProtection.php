<?php

namespace App\Support;

use App\Models\User;

class DeletionProtection
{
    /** A message is returned when deleting the account would remove dependent data. */
    public static function userReason(User $user): ?string
    {
        if ($user->workoutPrograms()->exists()) {
            return 'لا يمكن حذف الحساب لأنه مرتبط بأنظمة تدريبية. احتفظنا بالحساب لحماية بيانات التمارين.';
        }

        if ($user->workoutSessions()->exists()) {
            return 'لا يمكن حذف الحساب لأنه مرتبط بسجل جلسات تدريب.';
        }

        if ($user->dailyMeals()->exists()) {
            return 'لا يمكن حذف الحساب لأنه مرتبط بوجبات مسجلة وسجل تغذية.';
        }

        if ($user->customFoods()->exists()) {
            return 'لا يمكن حذف الحساب لأنه يحتوي على أصناف في بنك الطعام.';
        }

        if ($user->bodyMeasurements()->exists()) {
            return 'لا يمكن حذف الحساب لأنه يحتوي على قياسات جسم مسجلة.';
        }

        if (\App\Models\Exercise::where('user_id', $user->id)->exists()) {
            return 'لا يمكن حذف الحساب لأنه مرتبط بتمارين خاصة بالمستخدم.';
        }

        return null;
    }
}
