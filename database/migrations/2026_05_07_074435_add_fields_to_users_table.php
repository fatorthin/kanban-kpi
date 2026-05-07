<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('division_id')->nullable()->after('name')->constrained()->nullOnDelete();
            $table->decimal('base_point_rate', 12, 2)->default(0)->after('division_id');
            $table->string('fcm_token')->nullable()->after('base_point_rate');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('division_id');
            $table->dropColumn(['base_point_rate', 'fcm_token']);
            $table->dropSoftDeletes();
        });
    }
};
