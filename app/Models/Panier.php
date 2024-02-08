<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Panier extends Model
{
    use HasFactory;
    
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'paniers';

    protected $fillable = [
        'token',
        'nomProduit',
        'idProduit',
        'idVariante',
        'qty',
        // 'image',
        'prix'
    ];
}
