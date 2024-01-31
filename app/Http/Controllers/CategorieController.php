<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CategorieController extends Controller
{
    public function index()
    {
        $categories = Categorie::orderBy('id', 'desc')->get();

        return response()->json(['message' => 'Liste des catégories', 'categories' => $categories], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }

        $categorie = Categorie::create([
            'nom' => $request->nom,
        ]);

        return response()->json(['message' =>'Categorie ajouté avec succès','categorie' => $categorie], 201);
    }

    public function show($id)
    {
        $categorie = Categorie::find($id);

        if (!$categorie) {
            return response()->json(['error' => 'Catégorie non trouvée'], 404);
        }

        return response()->json(['categorie' => $categorie], 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }

        $categorie = Categorie::find($id);

        if (!$categorie) {
            return response()->json(['error' => 'Catégorie non trouvée'], 404);
        }

        $categorie->update([
            'nom' => $request->nom,
        ]);

        return response()->json(['message' => 'Catégorie mis à jour avec succès','categorie' => $categorie], 200);
    }

    public function delete(Request $request, $id)
    {
            $categorie = Categorie::withTrashed()->find($id);

            if ($categorie) {
                $categorie->delete();
                return response()->json(['message' => 'Catégorie supprimé avec succès'], 200);
            } else {
                return response()->json(['error' => 'Catégorie non trouvé'], 404);
            }
    }

    public function liste_produits_par_categorie($idCategorie)
    {
        $categorie = Categorie::find($idCategorie);

        $produits = $categorie->produits() ->orderBy('id', 'desc')->get(); 
 
        return response()->json(['produits' => $produits], 200);
    }


    public function nbrTotalCatgories()
    {
        $total = Categorie::count();
        return response()->json(['message' => 'Le nombre total de catégorie est :', 'Total'=>  $total], 200);
    }

}
