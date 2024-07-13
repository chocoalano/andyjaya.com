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
        Schema::table('attendances_in', function (Blueprint $table) {
            $table->float('lat')->nullable()->change();
            $table->float('lng')->nullable()->change();
            $table->time('time')->nullable()->change();
            $table->time('difference')->nullable()->change();
            $table->string('photo')->nullable()->change();
            $table->enum('status', ['late', 'unlate', 'early'])->nullable()->change();
        });
        Schema::table('attendances_out', function (Blueprint $table) {
            $table->float('lat')->nullable()->change();
            $table->float('lng')->nullable()->change();
            $table->time('time')->nullable()->change();
            $table->time('difference')->nullable()->change();
            $table->string('photo')->nullable()->change();
            $table->enum('status', ['late', 'unlate', 'early'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances_in', function (Blueprint $table) {
            $table->float('lat')->nullable(false)->change();
            $table->float('lng')->nullable(false)->change();
            $table->time('time')->nullable(false)->change();
            $table->time('difference')->nullable(false)->change();
            $table->string('photo')->nullable(false)->change();
            $table->enum('status', ['late', 'unlate', 'early'])->nullable(false)->change();
        });
        Schema::table('attendances_out', function (Blueprint $table) {
            $table->float('lat')->nullable(false)->change();
            $table->float('lng')->nullable(false)->change();
            $table->time('time')->nullable(false)->change();
            $table->time('difference')->nullable(false)->change();
            $table->string('photo')->nullable(false)->change();
            $table->enum('status', ['late', 'unlate', 'early'])->nullable(false)->change();
        });
    }
};
