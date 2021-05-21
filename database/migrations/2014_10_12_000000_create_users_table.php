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
            $table->bigIncrements('id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('username')->unique()->nullable();
            $table->string('gender')->nullable();
            $table->string('job')->nullable();
            $table->string('dob')->nullable();
            $table->text('about_yourself')->nullable();
            $table->string('profile_pic')->nullable();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->string('device_type')->nullable();
            $table->string('device_token')->nullable();
            $table->enum('account_type', ['personal', 'business'])->default('personal')->nullable();
            $table->string('otp')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password')->nullable();
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
    }
}
