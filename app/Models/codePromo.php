<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class codePromo extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'code_promos';

    protected $fillable = [
        'intitule',
        'valeur',
        'nombreUtilisation',
        'nombreUtilise',
    ];
}
