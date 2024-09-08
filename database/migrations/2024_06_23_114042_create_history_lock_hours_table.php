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
        Schema::create('history_lock_hours', function (Blueprint $table) {
            $table->id();
            $table->string("type");
            $table->string("nameStudent");
            $table->string("hour");
            $table->date("date");
            $table->date("dateAccept");
            $table->string("day");
            $table->string("status");
            $table->float('price');
            $table->integer('idProfileTeacher');
            $table->string('case')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_lock_hours');
    }
};
