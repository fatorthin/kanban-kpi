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
        // 1. Categories (e.g., I. Kompetensi Dasar)
        Schema::create('eval_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable(); // e.g. "I"
            $table->string('name');             // e.g. "Kompetensi Dasar"
            $table->integer('sort_order')->default(1);
            $table->timestamps();
        });

        // 2. Criteria (e.g., 1. Rispek, 2. Antusias, 3. Fatanah, 4. Amanah)
        Schema::create('eval_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eval_category_id')->constrained('eval_categories')->onDelete('cascade');
            $table->integer('number')->default(1); // 1, 2, 3, 4
            $table->string('name');               // e.g. "Rispek"
            $table->integer('sort_order')->default(1);
            $table->timestamps();
        });

        // 3. Indicators (e.g., a. Menerima dan menghargai perbedaan...)
        Schema::create('eval_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eval_criterion_id')->constrained('eval_criteria')->onDelete('cascade');
            $table->string('letter')->default('a'); // 'a', 'b'
            $table->text('statement');              // The question / indicator text
            $table->integer('sort_order')->default(1);
            $table->timestamps();
        });

        // 4. Subjective Evaluations Header
        Schema::create('subjective_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('evaluator_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('month'); // 1 - 12
            $table->integer('year');  // e.g. 2026
            $table->enum('self_status', ['Draft', 'Submitted'])->default('Draft');
            $table->enum('manager_status', ['Draft', 'Submitted'])->default('Draft');
            $table->timestamp('self_submitted_at')->nullable();
            $table->timestamp('manager_submitted_at')->nullable();
            $table->decimal('average_self_score', 5, 2)->nullable();
            $table->decimal('average_manager_score', 5, 2)->nullable();
            $table->decimal('final_subjective_score', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'month', 'year'], 'user_period_unique');
        });

        // 5. Subjective Evaluation Scores Detail
        Schema::create('subjective_evaluation_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subjective_evaluation_id')->constrained('subjective_evaluations')->onDelete('cascade');
            $table->foreignId('eval_indicator_id')->constrained('eval_indicators')->onDelete('cascade');
            $table->unsignedTinyInteger('self_score')->nullable();    // 1 - 5
            $table->unsignedTinyInteger('manager_score')->nullable(); // 1 - 5
            $table->timestamps();

            $table->unique(['subjective_evaluation_id', 'eval_indicator_id'], 'eval_score_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjective_evaluation_scores');
        Schema::dropIfExists('subjective_evaluations');
        Schema::dropIfExists('eval_indicators');
        Schema::dropIfExists('eval_criteria');
        Schema::dropIfExists('eval_categories');
    }
};
