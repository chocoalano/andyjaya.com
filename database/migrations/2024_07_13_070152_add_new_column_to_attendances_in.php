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
            $table
                ->unsignedBigInteger('area_id')
                ->foreign('area_id')
                ->references('id')
                ->on('att_areas')
                ->onDelete('cascade')
                ->after('att_group_schedule_id');
            $table
                ->unsignedBigInteger('departement_id')
                ->foreign('departement_id')
                ->references('id')
                ->on('departemen')
                ->onDelete('cascade')
                ->after('att_group_schedule_id');
            $table
                ->unsignedBigInteger('position_id')
                ->foreign('position_id')
                ->references('id')
                ->on('position')
                ->onDelete('cascade')
                ->after('att_group_schedule_id');
            $table
                ->unsignedBigInteger('level_id')
                ->foreign('level_id')
                ->references('id')
                ->on('level')
                ->onDelete('cascade')
                ->after('att_group_schedule_id');
        });
        Schema::table('attendances_out', function (Blueprint $table) {
            $table
                ->unsignedBigInteger('area_id')
                ->foreign('area_id')
                ->references('id')
                ->on('att_areas')
                ->onDelete('cascade')
                ->after('att_group_schedule_id');
            $table
                ->unsignedBigInteger('departement_id')
                ->foreign('departement_id')
                ->references('id')
                ->on('departemen')
                ->onDelete('cascade')
                ->after('att_group_schedule_id');
            $table
                ->unsignedBigInteger('position_id')
                ->foreign('position_id')
                ->references('id')
                ->on('position')
                ->onDelete('cascade')
                ->after('att_group_schedule_id');
            $table
                ->unsignedBigInteger('level_id')
                ->foreign('level_id')
                ->references('id')
                ->on('level')
                ->onDelete('cascade')
                ->after('att_group_schedule_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances_in', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropColumn('area_id');
            $table->dropForeign(['departement_id']);
            $table->dropColumn('departement_id');
            $table->dropForeign(['position_id']);
            $table->dropColumn('position_id');
            $table->dropForeign(['level_id']);
            $table->dropColumn('level_id');
        });
        Schema::table('attendances_out', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropColumn('area_id');
            $table->dropForeign(['departement_id']);
            $table->dropColumn('departement_id');
            $table->dropForeign(['position_id']);
            $table->dropColumn('position_id');
            $table->dropForeign(['level_id']);
            $table->dropColumn('level_id');
        });
    }
};
