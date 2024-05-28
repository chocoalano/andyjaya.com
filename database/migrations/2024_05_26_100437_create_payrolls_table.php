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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('total_schedule');
            $table->integer('total_present');
            $table->integer('total_late');
            $table->integer('total_unlate');
            $table->integer('total_early');
            $table->float('subtotal_payroll');
            $table->float('total_payroll')->nullable();
            $table->timestamps();
        });
        Schema::create('payroll_components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_id')->foreign('payroll_id')->references('id')->on('payrolls')->onDelete('cascade');
            $table->string('title', 100);
            $table->enum('operator', ['plus', 'minus', 'devide', 'times']);
            $table->float('amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
