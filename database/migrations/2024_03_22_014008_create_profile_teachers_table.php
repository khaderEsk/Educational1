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
        Schema::create('profile_teachers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('certificate'); // صورة الشهادة
            $table->text('description');
            $table->string('jurisdiction'); //الاختصاص من المفاضلة
            //$table->string('domain');  // مجال العمل
            $table->boolean('status');// الاستاذ مقبول في المنصة أو قيد الانتظار
            $table->integer('assessing'); //درجة تحقيق وثقية الشخص
            //$table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_teachers');
    }
};
