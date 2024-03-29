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
    Schema::create('produits', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->text('description');
        $table->integer('prix'); 
        $table->unsignedBigInteger('categorie_id');
        $table->integer('capacite'); // Changer le type de 'capacite' en integer
        $table->string('unite')->nullable(); // Ajouter le champ 'unite'
        $table->string('couleur')->nullable();
        $table->string('statut');
        $table->timestamps();
        $table->foreign('categorie_id')->references('id')->on('categories')->onDelete('cascade');
        $table->softDeletes();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
