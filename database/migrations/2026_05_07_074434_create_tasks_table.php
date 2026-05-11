<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_reference_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pic_id')->constrained('users');
            $table->foreignId('original_pic_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('manager_id')->constrained('users');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('task_type', ['Client', 'Internal']);
            $table->enum('status', ['New', 'In_Progress', 'Review', 'Revision', 'Completed'])->default('New');
            $table->enum('previous_status', ['New', 'In_Progress', 'Review', 'Revision', 'Completed'])->nullable();
            $table->boolean('is_takeover')->default(false);
            $table->string('takeover_reason')->nullable();
            $table->integer('difficulty_points')->default(0);
            $table->integer('revision_count')->default(0);
            $table->dateTime('deadline');
            $table->dateTime('completed_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
