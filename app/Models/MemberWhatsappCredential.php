<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MemberWhatsappCredential extends Model
{
    protected $table = 'member_whatsapp_credentials';

    protected $guarded = [];

    protected $hidden = ['encrypted_password', 'last_attempt_key'];

    protected $casts = [
        'password_expires_at' => 'datetime',
        'last_attempt_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
