<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GymReminderMessage extends Model
{
    protected $fillable = ['id', 'text'];

    public $incrementing = false;
}
