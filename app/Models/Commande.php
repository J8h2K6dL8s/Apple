<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Commande extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'commandes';

    protected $fillable = [
        'user_id',
        'produit_id',
        'order_id',
        'date_created',
        'status',
        'box',
        'codePromo',
        'quantite',
        'prix_total',
        'user_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
 
}
