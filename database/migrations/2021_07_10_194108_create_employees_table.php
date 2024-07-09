<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('general_direction_id')->references('id')->on('general_directions');
            $table->foreignId('direction_id')->references('id')->on('directions');
            $table->foreignId('subdirectorate_id')->references('id')->on('subdirectorates');
            $table->foreignId('department_id')->references('id')->on('departments');
            $table->bigInteger('plantilla_id')->nullable();
            $table->integer('status_id')->default(1);
            $table->string('name');
            $table->string('photo')->nullable();
            $table->binary('fingerprint')->nullable();
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
        Schema::dropIfExists('employees');
    }
}
