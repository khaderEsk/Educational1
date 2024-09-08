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
        Schema::create('calendar_hours', function (Blueprint $table) {
            $table->id();
            $table->integer('day_id')->references('id')->on('calendar_days')->onDelete('cascade');
            $table->integer('status')->default(0);
            $table->time('hour');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_hours');
    }
};
