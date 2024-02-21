<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Categorie extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'categories';

    protected $fillable = ['nom'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($categorie) {
            $categorie->produits()->delete();
        });
    }

    public function produits()
    {
        return $this->hasMany('App\Models\Produit','categorie_id');
    }
}
