<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class ChiffreAffaireController extends Controller
{
    public function calculerChiffreAffaires()
    {
        $chiffreAffaire = Commande::where('status', '!=', 'Unpaid')->sum('prix_total');

        return response()->json(['message' => "Le chiffre d'affaire total est : " .  $chiffreAffaire], 200);
    }

    public function calculerChiffreAffairesMoisEnCours()
    {
        $moisEnCours = Carbon::now()->month;

        $chiffreAffaires = Commande::whereMonth('created_at', $moisEnCours)
            ->where('status', '!=', 'Unpaid')
            ->sum('prix_total');

        return response()->json(['message' => "Le chiffre d'affaire du mois en cours est : " .  $chiffreAffaires], 200);
    }

}
