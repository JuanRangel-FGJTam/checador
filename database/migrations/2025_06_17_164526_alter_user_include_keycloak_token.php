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
         Schema::table('users', function(Blueprint $table)
        {
            $table->text('access_token')->nullable();
            $table->text('access_refresh_token')->nullable();
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->dropColumn('access_token');
            $table->dropColumn('access_refresh_token');

            $table->string('password')->default('')->change();
        });
    }
};
