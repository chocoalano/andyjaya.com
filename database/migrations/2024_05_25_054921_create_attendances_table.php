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
        Schema::create('attendances_in', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('att_group_schedule_id')->foreign('att_group_schedule_id')->references('id')->on('att_group_schedule')->onDelete('cascade');
            $table->json('location')->nullable();
            $table->float('lat');
            $table->float('lng');
            $table->time('time');
            $table->time('difference');
            $table->string('photo');
            $table->enum('status', ['late', 'unlate', 'early']);
            $table->timestamps();
        });
        Schema::create('attendances_out', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('att_id')->foreign('att_id')->references('id')->on('attendances_in')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('att_group_schedule_id')->foreign('att_group_schedule_id')->references('id')->on('att_group_schedule')->onDelete('cascade');
            $table->json('location')->nullable();
            $table->float('lat');
            $table->float('lng');
            $table->time('time');
            $table->time('difference');
            $table->string('photo');
            $table->enum('status', ['late', 'unlate', 'early']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances_in');
        Schema::dropIfExists('attendances_out');
    }
};
