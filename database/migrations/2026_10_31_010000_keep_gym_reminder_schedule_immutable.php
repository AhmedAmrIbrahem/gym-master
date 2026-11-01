<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Some MariaDB/MySQL installations give the first non-null TIMESTAMP an
        // implicit ON UPDATE CURRENT_TIMESTAMP. The appointment must never change
        // when a worker changes status. Values remain UTC, via UtcDateTime.
        Schema::table('gym_reminder_deliveries', function (Blueprint $table) {
            $table->dateTime('scheduled_at')->change();
        });
    }

    public function down(): void
    {
        // Intentionally retain DATETIME on rollback to avoid reintroducing an
        // implicit automatic update. No history is deleted or replayed.
    }
};
