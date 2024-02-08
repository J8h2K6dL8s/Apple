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
        Schema::create('paniers', function (Blueprint $table) {
            $table->id();
            $table->string('token');
            $table->unsignedBigInteger('idProduit');
            $table->string('nomProduit')->nullable(); 
            $table->unsignedBigInteger('idVariante')->nullable(); 
            $table->integer('qty'); 
            // $table->string('image'); 
            $table->integer('prix'); 
            $table->timestamps();
            $table->foreign('idProduit')->references('id')->on('produits')->onDelete('cascade');
            $table->foreign('idVariante')->references('id')->on('variantes')->onDelete('set null'); 
            $table->softDeletes();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paniers');
    }
};
