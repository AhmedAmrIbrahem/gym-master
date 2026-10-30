<?php

namespace App\Models;

use App\Casts\UtcDateTime;
use Illuminate\Database\Eloquent\Model;

class GymReminder extends Model
{
    public const TIMEZONE = 'Africa/Cairo';

    public const DAYS = [6 => 'السبت', 0 => 'الأحد', 1 => 'الاثنين', 2 => 'الثلاثاء', 3 => 'الأربعاء', 4 => 'الخميس', 5 => 'الجمعة'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean', 'consented_at' => 'datetime', 'weekly_schedule' => 'array', 'eligible_after' => UtcDateTime::class];
    }

    public function scheduleForWeek(): array
    {
        return $this->weekly_schedule ?? array_fill(0, 7, $this->time);
    }
}
