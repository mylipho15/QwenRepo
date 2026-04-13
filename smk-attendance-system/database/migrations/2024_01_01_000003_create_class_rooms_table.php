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
        Schema::create('class_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "XII RPL 1"
            $table->string('grade'); // e.g., "X", "XI", "XII"
            $table->string('suffix')->nullable(); // e.g., "1", "2", "3"
            $table->foreignId('major_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->nullable()->constrained('users')->onDelete('set null'); // Homeroom teacher
            $table->integer('academic_year')->default(2024);
            $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['grade', 'major_id']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_rooms');
    }
};
