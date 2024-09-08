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
        Schema::create('lock_hours', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id')->references('id')->on('profile_students')->onDelete('cascade');
            $table->integer('service_id')->references('id')->on('service_teachers')->onDelete('cascade');
            $table->integer('hour_id')->references('id')->on('calendar_hours')->onDelete('cascade');
            $table->float('value')->default(0.0);
            $table->integer('status')->default(0);
            $table->string('appointmentAddress')->nullable();
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lock_hours');
    }
};
