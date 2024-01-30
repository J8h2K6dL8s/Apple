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
}
