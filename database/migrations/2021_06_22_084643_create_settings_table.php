<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->enum('story_privacy', ['public', 'friends', 'custom'])->default('public')->nullable();
            $table->enum('location_privacy', ['public', 'private'])->default('public')->nullable();
            $table->enum('profile_privacy', ['public', 'private'])->default('public')->nullable();
            $table->boolean('location_services')->default(false)->nullable();
            $table->boolean('notifications')->default(false)->nullblae();
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
        Schema::dropIfExists('settings');
    }
}
