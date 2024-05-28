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
        Schema::create('att_group_schedule', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_att_group_id')->constrained()->onDelete('cascade');
            $table->foreignId('att_time_id')->constrained()->onDelete('cascade');
            $table->date('date_work');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('att_group_schedule');
    }
};
