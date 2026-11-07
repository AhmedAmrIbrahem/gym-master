<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('member_whatsapp_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('encrypted_password')->nullable();
            $table->timestamp('password_expires_at')->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('message_type', 20)->nullable();
            $table->uuid('last_attempt_key')->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_whatsapp_credentials');
    }
};
