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
        Schema::create('complete_teachers', function (Blueprint $table) {
            $table->id();
            $table->integer('teacher_id')
                ->references('id')->on('profile_teachers')
                ->onDelete('cascade');
            $table->string('cv')->nullable();
            $table->string('self_identity')->nullable();
            $table->string('phone')->nullable();
            $table->integer('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complete_teachers');
    }
};
