<?php

namespace App\Models;

use App\Casts\UtcDateTime;
use Illuminate\Database\Eloquent\Model;

class GymReminderDelivery extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['scheduled_at' => UtcDateTime::class, 'gym_at' => UtcDateTime::class, 'offset_minutes' => 'integer', 'sent_at' => 'datetime'];
    }
}
