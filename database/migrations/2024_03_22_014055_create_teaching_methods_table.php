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
        Schema::create('teaching_methods', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type');
            $table->text('description');
            $table->string('file');
            $table->boolean('status');
            $table->double('price');
            $table->integer('views')->default(0);


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
        Schema::dropIfExists('teaching_methods');
    }
};
