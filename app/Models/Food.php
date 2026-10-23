<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Food extends Model
{
    use HasFactory;

    // تحديد اسم الجدول صراحة لتفادي البحث عن جدول 'food'
    protected $table = 'foods';

    protected $fillable = [
        'user_id',
        'name',
        'calories_per_100g',
        'protein_per_100g',
        'carbs_per_100g',
        'fats_per_100g',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
