<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeImage extends Model
{
    use HasFactory;

    protected $table = 'trade_images';

    protected $fillable = ['trade_id', 'chemin_image'];

    public function trade()
    {
        return $this->belongsTo(Trade::class);
    }
}
