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
        Schema::create('form_paid_leaves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->date('from_date');
            $table->date('to_date');
            $table->longText('notes');
            $table->enum('status_line', ['waiting', 'approved', 'rejected'])->default('waiting');
            $table->enum('status_mngr', ['waiting', 'approved', 'rejected'])->default('waiting');
            $table->enum('status_hr', ['waiting', 'approved', 'rejected'])->default('waiting');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_paid_leaves');
    }
};
