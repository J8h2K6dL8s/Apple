<?php

namespace App\Http\Controllers;

use App\Jobs\ImageJob;
use App\Models\Produit;
use App\Models\Variante;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\VarianteImageJob;
use App\Models\VarianteImage;

class ProduitController extends Controller
{
    
    public function index()
    {
        // Récupérez tous les produits avec leurs variantes
        $produits = Produit::with('images','variantes')->orderBy('id', 'desc')->get();

        return response()->json(['produits' => $produits], 200);
    }

    public function show(Produit $produit)
    {
        // Chargez le produit avec les variantes associées
        $produit->load('images','variantes');

        return response()->json(['produit' => $produit], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'description' => 'required|string',
            'capacite' => 'required|string', 
            'couleur' => 'required|string', 
            'prix' => 'required|numeric',
            'categorie_id' => 'required|exists:categories,id',
            'images' => 'required|array|min:1',
            'images.*' => 'image|file|mimes:jpeg,png,jpg,|max:2048',
        ]);

        // Création du produit
        $produit = Produit::create([
            'nom' => $request->input('nom'),
            'description' => $request->input('description'),
            'capacite' => $request->input('capacite'),
            'couleur' => $request->input('couleur'),
            'prix' => $request->input('prix'),
            'categorie_id' => $request->input('categorie_id'),
        ]);

        // Ajout des images au produit
        if ($request->hasFile('images')) {

            foreach ($request->file('images') as $image) {
                $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('public/fichiers_produit/', $filename);
                $imageName = 'public/fichiers_produit/' . $filename;

                // Dispatch du job pour traiter l'image en arrière-plan
                dispatch(new ImageJob($produit->id, $imageName));
            }
        }
        $produit->images;

        return response()->json(['message' => 'Produit ajouté avec succès', 'produit' => $produit], 201);
    }

    public function update(Request $request, Produit $produit)
    {
        // Validation des données de la requête
        $request->validate([
            'nom' => 'required|string',
            'description' => 'required|string',
            'capacite' => 'nullable|string',
            'couleur' => 'nullable|string',
            'prix' => 'required|numeric',
            'categorie_id' => 'required|exists:categories,id',
            'images' => 'nullable|array|min:1', 
            'images.*' => 'image|file|mimes:jpeg,png,jpg,|max:2048',
            'variantes.*.type' => 'string|in:couleur,capacite',
            'variantes.*.valeur' => 'string',
            'variantes.*.prix' => 'numeric',
            'variantes.*.image' => 'nullable|image|file|mimes:jpeg,png,jpg,|max:2048', 
        ]);
    
        // Mise à jour des données du produit
        $produit->update([
            'nom' => $request->input('nom'),
            'description' => $request->input('description'),
            'capacite' => $request->input('capacite'),
            'couleur' => $request->input('couleur'),
            'prix' => $request->input('prix'),
            'categorie_id' => $request->input('categorie_id'),
        ]);
    
        // Mise à jour des images du produit
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('public/fichiers_produit/', $filename);
                $imageName = 'public/fichiers_produit/' . $filename;
    
                // Dispatch du job pour traiter l'image en arrière-plan
                dispatch(new ImageJob($produit->id, $imageName));
            }
        }

        $produit->images;
    
        // Mise à jour des variantes si elles sont fournies
        if ($request->has('variantes')) {
            $produit->variantes()->delete(); // Supprimer toutes les variantes existantes avant d'ajouter les nouvelles
    
            foreach ($request->variantes as $varianteData) {
                // Créez une variante
                $variante = new Variante([
                    'type' => $varianteData['type'],
                    'valeur' => $varianteData['valeur'],
                    'prix' => $varianteData['prix'],
                ]);
    
                // Ajout de l'image de variante si elle est fournie
                if (isset($varianteData['image'])) {
                    $filename = uniqid() . '.' . $varianteData['image']->getClientOriginalExtension();
                    $path = $varianteData['image']->storeAs('public/fichiers_variantes/', $filename);
                    $imageName = 'public/fichiers_variantes/' . $filename;
                    $variante->image = $imageName;
                }
    
                $produit->variantes()->save($variante);
            }
        }
    
        $produit->load('variantes'); // Recharger les variantes après la mise à jour
    
        return response()->json(['message' => 'Produit mis à jour avec succès', 'produit' => $produit], 200);
    }
    
    public function delete(Request $request, $id)
    {
            $produit = Produit::withTrashed()->find($id);

            if ($produit) {
                $produit->delete();
                return response()->json(['message' => 'Produit supprimé avec succès'], 200);
            } else {
                return response()->json(['error' => 'Produit non trouvé'], 404);
            }
    }

    public function nbrTotalProduits(){

        $total = Produit::count();
    
        return response()->json(['message' => 'Le nombre total de produit est :', 'Total'=>  $total], 200);

    }  

    public function rechercher_produit_par_nom(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
        ]);

        // Recherche des produits par nom
        $produits = Produit::where('nom', 'like', '%' . $request->input('nom') . '%')->get();

        // Vous pouvez également charger les variantes pour chaque produit si nécessaire
        $produits->load('variantes');

        return response()->json(['produits' => $produits], 200);
    }

}
