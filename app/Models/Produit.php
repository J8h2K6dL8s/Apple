<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produit extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'produits';

    protected $fillable = [
        'nom',
        'description',
        'prix',
        'categorie_id',
        'capacite',
        'couleur',
        'statut',
       
    ]; 

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($produit) {
            // Supprimer les images associées à ce produit
            $produit->images()->delete();
            // Supprimer les variantes associées à ce produit
            $produit->variantes()->delete();
            // Supprimer les entrées dans la table 'favoris' associées à ce produit
            $produit->favoris()->delete();
            // Supprimer les entrées dans la table 'panier' associées à ce produit
             Panier::where('idProduit', $produit->id)->delete();
        });
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id', 'id');
    }

    public function variantes()
    {
        return $this->hasMany(Variante::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function varianteImages()
    {
        return $this->hasMany(VarianteImage::class);
    }

    public function favoris()
    {
        return $this->hasMany(Favoris::class);
    }

}
