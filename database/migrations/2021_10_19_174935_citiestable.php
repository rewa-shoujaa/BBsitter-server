<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Citiestable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mytable', function (Blueprint $table) {
            $table->integer('dest_id')->primary();
            $table->string('dest');
            $table->decimal('lat')->nullable();
            $table->decimal('lng')->nullable();
            $table->string('country');
            $table->string('country_iso')->nullable();
            $table->string('dest_parent')->nullable();
            $table->string('dest_type')->nullable();
            $table->integer('population')->nullable();
            $table->integer('population_proper')->nullable();

            $table->rememberToken();
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
        Schema::dropIfExists('mytable');
    }
}
