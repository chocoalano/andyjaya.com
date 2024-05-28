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
        Schema::create('permission_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->enum('request_type', ['present-late', 'sick', 'not-present', 'personal permission', 'other'])->default('personal permission');
            $table->date('from_date');
            $table->date('to_date');
            $table->longText('notes');
            $table->string('file')->nullable();
            $table->enum('status_hr', ['waiting', 'approved', 'rejected'])->default('waiting');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_forms');
    }
};
