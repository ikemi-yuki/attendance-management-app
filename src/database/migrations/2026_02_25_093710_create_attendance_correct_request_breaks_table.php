<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceCorrectRequestBreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_correct_request_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_correct_request_id');
            $table->foreign('attendance_correct_request_id', 'acr_breaks_acr_id_fk')
                ->references('id')
                ->on('attendance_correct_requests')
                ->cascadeOnDelete();
            $table->foreignId('attendance_break_id')->nullable();
            $table->foreign('attendance_break_id', 'acr_breaks_break_id_fk')
                ->references('id')
                ->on('attendance_breaks')
                ->nullOnDelete();
            $table->dateTime('requested_break_start');
            $table->dateTime('requested_break_end');
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
        Schema::dropIfExists('attendance_correct_request_breaks');
    }
}
