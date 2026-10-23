<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('body_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->decimal('weight', 5, 2); // الوزن بالكجم
            $table->decimal('chest', 5, 2)->nullable(); // الصدر (سم)
            $table->decimal('waist', 5, 2)->nullable(); // الخصر (سم)
            $table->decimal('biceps', 5, 2)->nullable(); // الذراع (سم)
            $table->decimal('thighs', 5, 2)->nullable(); // الفخذ (سم)
            $table->decimal('neck', 5, 2)->nullable(); // الرقبة (سم)
            $table->text('notes')->nullable(); // ملاحظات
            $table->timestamps();

            // منع تكرار أكثر من تسجيل لنفس التاريخ للمستخدم
            $table->unique(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('body_measurements');
    }
};
