<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    use HasFactory;

    protected $table = 'trades';

    protected $fillable = ['titre', 'description', 'email', 'telephone'];

    protected static function boot()
    {
        parent::boot();
    
        // Lorsque le trade est supprimé, supprimer également les images associées
        static::deleting(function($trade) {
            $trade->images()->delete();
        });
    }

    public function images()
    {
        return $this->hasMany(TradeImage::class);
    }
}
