<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gym_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(false)->index();
            $table->string('time', 5);
            $table->string('timezone', 64);
            $table->string('consent_phone', 16)->nullable();
            $table->timestamp('consented_at')->nullable();
            $table->uuid('version');
            $table->timestamps();
        });

        Schema::create('gym_reminder_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('local_date');
            $table->uuid('idempotency_key')->unique();
            $table->uuid('settings_version');
            $table->dateTime('scheduled_at');
            $table->string('status', 16)->default('pending')->index();
            $table->string('reason', 64)->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'local_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gym_reminder_deliveries');
        Schema::dropIfExists('gym_reminders');
    }
};
