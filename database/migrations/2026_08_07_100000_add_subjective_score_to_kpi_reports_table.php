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
        Schema::table('kpi_reports', function (Blueprint $table) {
            $table->decimal('subjective_score', 8, 2)->default(0)->after('timeliness_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpi_reports', function (Blueprint $table) {
            $table->dropColumn('subjective_score');
        });
    }
};
