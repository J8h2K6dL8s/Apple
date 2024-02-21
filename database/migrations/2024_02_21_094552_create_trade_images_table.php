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
        Schema::create('trade_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trade_id');
            $table->string('chemin_image');
            $table->timestamps();
            $table->foreign('trade_id')->references('id')->on('trades')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_images');
    }
};
