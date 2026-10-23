<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealItem extends Model
{
    use HasFactory;

    protected $fillable = ['daily_meal_id', 'food_id', 'grams'];

    /** @return BelongsTo<DailyMeal, $this> */
    public function dailyMeal(): BelongsTo
    {
        return $this->belongsTo(DailyMeal::class);
    }

    /** @return BelongsTo<Food, $this> */
    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }

    // حساب الماكروز للكمية المحددة بالجرام
    public function getCaloriesAttribute()
    {
        return round(($this->grams / 100) * $this->food->calories_per_100g, 1);
    }

    public function getProteinAttribute()
    {
        return round(($this->grams / 100) * $this->food->protein_per_100g, 1);
    }

    public function getCarbsAttribute()
    {
        return round(($this->grams / 100) * $this->food->carbs_per_100g, 1);
    }

    public function getFatsAttribute()
    {
        return round(($this->grams / 100) * $this->food->fats_per_100g, 1);
    }
}
