<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VarianteImage extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'variante_images';

    protected $fillable = ['variante_id', 'chemin_image'];

    public function variante()
    {
        return $this->belongsTo(Variante::class);
    }
}
