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
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->float('price');
            $table->integer('number_students');
            $table->string('file');
            $table->string('place');
            $table->timestamp('start_date');
            $table->timestamp('end_date')->nullable();
            $table->boolean('active')->default(true);
            $table->bigInteger('profile_teacher_id')->unsigned();
            $table->foreign('profile_teacher_id')->references('id')->on('profile_teachers')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
