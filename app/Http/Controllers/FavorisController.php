<?php

namespace App\Http\Controllers;

use App\Models\Favoris;
use App\Models\Produit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FavorisController extends Controller
{
    public function ajouterAuxFavoris($id)
    {
        $produit = Produit::find($id);

        if (!$produit) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        $user = auth('sanctum')->user() ;


        // Vérifier si le produit existe déjà dans les favoris de l'utilisateur
        $favorisExistant = Favoris::where('produit_id', $produit->id)
                                ->where('user_id',$user->id)
                                ->first();

        if ($favorisExistant) {
            return response()->json(['message' => 'Ce produit est déjà dans les favoris.']);
        }

        // Ajouter le produit aux favoris de l'utilisateur
        $favoris = new Favoris();
        $favoris->produit_id = $produit->id;
        $favoris->user_id = $user->id;
        $favoris->save();

        return response()->json(['message' => 'Ce produit a été ajouté aux favoris.'] , 200);
    }

    public function mesFavoris()
    {
        $user = auth('sanctum')->user() ;


        // Récupérer la liste des produits favoris de l'utilisateur
        $produitsFavoris = Favoris::where('user_id', $user->id)
                                ->with('produit.images', 'produit.variantes.images')
                                ->get();
             
        return response()->json(['produits_favoris' => $produitsFavoris] , 200);
    }

    public function delete($id)
    {
        $favori = Favoris::find($id);

        if (!$favori) {
            return response()->json(['message' => 'Favori non trouvé'], 404);
        }

        // Suppression souple du favori
        $favori->delete();

        return response()->json(['message' => 'Favori supprimé avec succès'], 200);
    }
}
