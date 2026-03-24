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
        Schema::table('attendances', function (Blueprint $table) {
            $table->index('attendance_date');
        });

        Schema::table('justifications', function (Blueprint $table) {
            $table->index(['start_date', 'end_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['attendance_date']);
        });

        Schema::table('justifications', function (Blueprint $table) {
            $table->dropIndex(['start_date', 'end_date']);
            $table->dropIndex(['status']);
        });
    }
};
