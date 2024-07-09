<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJustifiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('justifies', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->integer('type_justify_id');
            $table->date('date_start')->nullable();
            $table->date('date_finish')->nullable();
            $table->string('file', 255)->nullable();
            $table->string('details', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('justifies');
    }
}
