<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nis')->unique();
            $table->string('nisn')->unique();
            $table->foreignId('class_id')->constrained('class_rooms')->onDelete('set null');
            $table->foreignId('major_id')->constrained()->onDelete('restrict');
            $table->enum('gender', ['male', 'female']);
            $table->date('birth_date')->nullable();
            $table->string('birth_place')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('parent_name')->nullable();
            $table->string('parent_phone')->nullable();
            $table->string('qr_code')->unique()->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['class_id', 'is_active']);
            $table->index('nis');
            $table->index('nisn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
