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
        // 'traitement'
       
    ]; 

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($produit) {
            // Supprimer les images associées à ce produit
            $produit->images()->delete();
            // Supprimer les variantes associées à ce produit
            $produit->variantes()->delete();
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

    // public function commandes()
    // {
    //     return $this->belongsToMany(Commande::class);
    // }
}
