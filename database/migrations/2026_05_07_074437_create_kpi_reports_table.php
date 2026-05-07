<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('month');
            $table->integer('year');
            $table->integer('total_load_points')->default(0);
            $table->decimal('productivity_score', 8, 2)->default(0);
            $table->decimal('quality_score', 8, 2)->default(0);
            $table->decimal('timeliness_score', 8, 2)->default(0);
            $table->decimal('final_kpi_score', 8, 2)->default(0);
            $table->decimal('total_incentive', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_reports');
    }
};
