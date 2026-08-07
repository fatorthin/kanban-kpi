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
        Schema::create('kpi_weight_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('month')->nullable(); // Null for default template
            $table->integer('year')->nullable();  // Null for default template
            $table->decimal('production_weight', 5, 2)->default(25.00);
            $table->decimal('quality_weight', 5, 2)->default(35.00);
            $table->decimal('timeliness_weight', 5, 2)->default(25.00);
            $table->decimal('subjective_weight', 5, 2)->default(15.00);
            $table->timestamps();

            $table->unique(['month', 'year'], 'period_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_weight_settings');
    }
};
