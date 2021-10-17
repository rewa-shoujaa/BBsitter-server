<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->integer('user_type'); // 1 admin 2 parent 3 babysitter
            $table->integer('has_details');
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('parents', function (Blueprint $table) {
            $table->id('id');
            $table->integer('user_id');
            $table->date('date_of_birth');
            $table->integer('phone_number');
            $table->tinyInteger('is_approved');
            $table->tinyInteger('gender'); //0 female 1 male
            $table->string('picture')->nullable();
            $table->integer('address_id');
            $table->integer('alternative_contact')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('babysitter', function (Blueprint $table) {
            $table->id('id');
            $table->integer('user_id');
            $table->date('date_of_birth');
            $table->integer('phone_number');
            $table->tinyInteger('gender'); //0 female 1 male
            $table->tinyInteger('is_approved');
            $table->tinyInteger('is_available');
            $table->string('qualifications')->nullable();
            $table->string('experience')->nullable;
            $table->string('picture')->nullable;
            $table->integer('address_id');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('alternative_contact', function (Blueprint $table) {
            $table->id('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->integer('phone_number');
            $table->string('details');
            $table->tinyInteger('is_active');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id('id');
            $table->string('country');
            $table->integer('city_id');
            $table->string('address');
            $table->double('address_latitude');
            $table->double('address_longitude');
            $table->tinyInteger('is_active');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id('id');
            $table->integer('parent_id');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('details')->nullable();
            $table->string('address_id');
            $table->tinyInteger('is_scheduled');
            $table->tinyInteger('is_canceled');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('appointment_details', function (Blueprint $table) {
            $table->id('id');
            $table->integer('appointment_ID');
            $table->integer('babysitter_id');
            $table->tinyInteger('is_approved');
            $table->tinyInteger('is_canceled');
            $table->rememberToken();
            $table->timestamps();
        });


        Schema::create('ratings', function (Blueprint $table) {
            $table->id('id');
            $table->integer('rating');
            $table->string('comment')->nullable();
            $table->integer('target_user_id');
            $table->integer('source_user_id');
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
        Schema::dropIfExists('users');
        Schema::dropIfExists('parents');
        Schema::dropIfExists('babysitter');
        Schema::dropIfExists('children');
        Schema::dropIfExists('alternative_contact');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('appointment_details');
        Schema::dropIfExists('appointment_kids_details');
        Schema::dropIfExists('ratings');
    }
}
