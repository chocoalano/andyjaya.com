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
        Schema::create('att_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('address');
            $table->float('lat');
            $table->float('lng');
            $table->integer('radius');
            $table->timestamps();
        });
        Schema::create('user_att_areas', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('att_area_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('att_areas');
        Schema::dropIfExists('user_att_areas');
    }
};
