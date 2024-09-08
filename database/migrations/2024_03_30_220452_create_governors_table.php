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
        Schema::create('governors', function (Blueprint $table) {
            $table->id();
            $table->integer('wallet_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('amount');
            $table->string('type');
            $table->string('image_transactions')->nullable();
            $table->string('phone')->nullable();
            $table->string('transferCompany')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('governors');
    }
};
