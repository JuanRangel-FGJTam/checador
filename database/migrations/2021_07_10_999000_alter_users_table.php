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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('general_direction_id')->nullable()->constrained();
            $table->foreignId('direction_id')->nullable()->constrained();
            $table->foreignId('subdirectorates_id')->nullable()->constrained();
            $table->foreignId('departments_id')->nullable()->constrained();
            $table->integer('level_id')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['general_direction_id']);
            $table->dropColumn('general_direction_id');

            $table->dropForeign(['direction_id']);
            $table->dropColumn('direction_id');

            $table->dropForeign(['subdirectorates_id']);
            $table->dropColumn('subdirectorates_id');

            $table->dropForeign(['departments_id']);
            $table->dropColumn('departments_id');
            $table->dropColumn('level_id');

            $table->dropSoftDeletes();
            
        });
    }
};
