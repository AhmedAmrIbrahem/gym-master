<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyMeal extends Model
{
    use HasFactory;

    protected $casts = [
        'date' => 'date',
    ];

    protected $fillable = ['user_id', 'date', 'meal_type'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<MealItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(MealItem::class);
    }

    public function getTotalCaloriesAttribute()
    {
        return $this->items->sum('calories');
    }

    public function getTotalProteinAttribute()
    {
        return $this->items->sum('protein');
    }

    public function getTotalCarbsAttribute()
    {
        return $this->items->sum('carbs');
    }

    public function getTotalFatsAttribute()
    {
        return $this->items->sum('fats');
    }
}
