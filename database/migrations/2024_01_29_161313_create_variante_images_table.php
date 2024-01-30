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
        Schema::create('variante_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variante_id');
            $table->string('chemin_image');
            $table->timestamps();
            $table->foreign('variante_id')->references('id')->on('variantes')->onDelete('cascade'); 
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variante_images');
    }
};
