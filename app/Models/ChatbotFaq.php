<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotFaq extends Model
{
    protected $fillable = ['question', 'answer', 'category', 'aliases', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
