<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Favoris extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'favoris';
    
    protected $fillable = [
        'user_id',
        'produit_id',
    ];


    public function produit(){

        return $this->belongsTo(Produit::class);
    }
}
