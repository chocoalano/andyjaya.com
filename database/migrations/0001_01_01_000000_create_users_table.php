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
        Schema::create('departemen', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('position', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('level', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('departemen_id')->foreign('departemen_id')->references('id')->on('departemen')->onDelete('cascade')->nullable();
            $table->unsignedBigInteger('position_id')->foreign('position_id')->references('id')->on('position')->onDelete('cascade')->nullable();
            $table->unsignedBigInteger('level_id')->foreign('level_id')->references('id')->on('level')->onDelete('cascade')->nullable();
            $table->string('nik')->unique()->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_suspended')->default(true);
            $table->enum('work_location', ['office', 'store', 'warehouse'])->default('office');
            $table->integer('saldo_cuti')->default(0);
            $table->date('join_at')->nullable();
            $table->float('loan_limit', 8.2)->default(0);
            $table->float('total_salary', 8.2)->default(0);
            $table->integer('approval_line')->nullable();
            $table->integer('approval_manager')->nullable();
            $table->integer('approval_hr')->nullable();
            $table->integer('approval_owner')->nullable();
            $table->integer('approval_fat')->nullable();
            $table->string('image')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departemen');
        Schema::dropIfExists('position');
        Schema::dropIfExists('level');
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
