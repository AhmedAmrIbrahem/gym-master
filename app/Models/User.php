<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'is_active',
        'password',
        'avatar',
        'target_calories',
        'target_protein',
        'target_carbs',
        'target_fats',
        'rest_target_calories',
        'rest_target_protein',
        'rest_target_carbs',
        'rest_target_fats',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // التحقق هل المستخدم Super Admin
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    // رابط الصورة الشخصية الافتراضي
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/'.$this->avatar);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=2563eb&color=ffffff&bold=true';
    }

    // العلاقات

    /** @return HasMany<BodyMeasurement, $this> */
    public function bodyMeasurements(): HasMany
    {
        return $this->hasMany(BodyMeasurement::class);
    }

    // ربط علاقة measurements بـ BodyMeasurement لعدم حدوث أخطاء مع الخدمات الأخرى

    /** @return HasMany<BodyMeasurement, $this> */
    public function measurements(): HasMany
    {
        return $this->hasMany(BodyMeasurement::class);
    }

    /** @return HasOne<BodyMeasurement, $this> */
    public function latestMeasurement(): HasOne
    {
        return $this->hasOne(BodyMeasurement::class)->latestOfMany();
    }

    /** @return HasMany<WorkoutProgram, $this> */
    public function workoutPrograms(): HasMany
    {
        return $this->hasMany(WorkoutProgram::class);
    }

    /** @return HasMany<WorkoutSession, $this> */
    public function workoutSessions(): HasMany
    {
        return $this->hasMany(WorkoutSession::class);
    }

    /** @return HasMany<DailyMeal, $this> */
    public function dailyMeals(): HasMany
    {
        return $this->hasMany(DailyMeal::class);
    }

    /** @return HasMany<Food, $this> */
    public function customFoods(): HasMany
    {
        return $this->hasMany(Food::class);
    }
}
