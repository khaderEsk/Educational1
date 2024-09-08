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
        Schema::create('reservation_teaching_methods', function (Blueprint $table) {
            $table->id();
            $table->timestamp('reserved_at');

            $table->boolean('deducted')->default(0);

            $table->bigInteger('profile_student_id')->unsigned();
            $table->foreign('profile_student_id')->references('id')->on('profile_students')->onDelete('cascade');

            $table->bigInteger('teaching_method_id')->unsigned();
            $table->foreign('teaching_method_id')->references('id')->on('teaching_methods')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_teaching_methods');
    }
};
