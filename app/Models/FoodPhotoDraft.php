<?php

namespace App\Models;

use App\Casts\UtcDateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class FoodPhotoDraft extends Model
{
    use MassPrunable;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['items' => 'array', 'expires_at' => UtcDateTime::class, 'consumed_at' => UtcDateTime::class];
    }

    public function prunable(): Builder
    {
        return static::where('expires_at', '<', now('UTC')->subDays(7)->format('Y-m-d H:i:s'));
    }
}
