<?php

namespace App\Http\Controllers;

use App\Models\codePromo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CodePromoController extends Controller
{
    public function index()
    {
        $codePromo = CodePromo::orderBy('created_at', 'desc')->get();

        return response()->json(['codepromos' => $codePromo], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
        
            'intitule' => 'required',
            'nombreUtilisation' =>"required|min:1",
            'valeur' => 'required|integer|max:100',
           ]);
           
            if ($validator->fails()) {
              return response(['errors' => $validator->errors(), ], 422); 
            }
          $codePromo=codePromo::create([
            'intitule' => $request->intitule,
            'valeur' => $request->valeur,
            'nombreUtilisation' => $request->nombreUtilisation
          ]);
 
        return response()->json(['message' => 'Code Promo ajouté avec succès','codePromo' => $codePromo], 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'intitule' => 'required',
            'nombreUtilisation' => 'required',
            'valeur' => 'required|integer|max:100',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }

        $codePromo = CodePromo::find($id);

        if (!$codePromo) {
            return response()->json(['message' => 'Code Promo non trouvé'], 404);
        }

        $codePromo->update([
            'intitule' => $request->intitule,
            'valeur' => $request->valeur,
            'nombreUtilisation' => $request->nombreUtilisation,
        ]);

        return response()->json(['message' => 'Code Promo mis à jour avec succès', 'codePromo' => $codePromo], 200);
    }

    public function show(string $id)
    {
        $codePromo = codePromo::find($id);

        if (!$codePromo) {
            return response()->json(['error' => 'Code Promo non trouvée'], 404);
        }

        return response()->json(['CodePromo' => $codePromo], 200);
    }

    public function checkValidity(Request $request)
    {
    
        $validator = Validator::make($request->all(), [
            'codePromo' => 'required|string',
        ]);
           
        if ($validator->fails()) {
              return response(['errors' => $validator->errors(), ], 422); 
        }

        // Récupération du code promo depuis la base de données
        $promo = codePromo::where('intitule', $request->input('codePromo'))->first();

        // Vérification de la validité du code promo
        if ($promo && $promo->nombreUtilisation > 0) {
            // Le code promo est valide, vous pouvez ajouter des actions supplémentaires ici
            return response()->json(['message' => 'Code Promo valide','value' => $promo->valeur/100]);
        } else {
            // Le code promo n'est pas valide
            return response()->json(['erreur' => "Code promo errone ou épuisé"]);
        }
    }

    public function delete(Request $request, $id)
    {
            $codePromo = codePromo::withTrashed()->find($id);

            if ($codePromo) {
                $codePromo->delete();
                return response()->json(['message' => 'Code Promo supprimé avec succès'], 200);
            } else {
                return response()->json(['error' => 'Code Promo non trouvé'], 404);
            }
    }
}
