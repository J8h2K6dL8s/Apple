<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Variante extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $dates = ['deleted_at'];
    
    protected $table = 'variantes'; 

    protected $fillable = ['produit_id', 'type', 'valeur', 'prix','image'];

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'produit_id');
    }

    // public function image()
    // {
    //     return $this->hasOne(Image::class, 'id');
    // }

    // public function images()
    // {
    //     return $this->belongsTo(Image::class);
    // }

    // public function images()
    // {
    //     return $this->hasMany(Image::class);
    // }
}
