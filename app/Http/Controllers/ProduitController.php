<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Jobs\ImageJob;
use App\Models\Produit;
use App\Models\Variante;
use Illuminate\Http\Request;
use App\Models\VarianteImage;
use App\Jobs\VarianteImageJob;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

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
        $produit->load('images', 'variantes');
        $produit->load('variantes.images');

        if ($produit->categorie) {
            // Accédez au nom de la catégorie
            $nomCategorie = $produit->categorie->nom;

            // Retournez le produit avec le nom de la catégorie au lieu de l'ID
            return response()->json(['produit' => $produit->setAttribute('categorie_nom', $nomCategorie)], 200);
        } 
        else {
            return response()->json(['produit' => $produit], 200);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'description' => 'required|string',
            'capacite' => 'nullable|integer',
            'unite' => [
                'required_with:capacite', 
                Rule::in(['Go', 'To']), 
            ], 
            'couleur' => 'nullable|string', 
            'prix' => 'required|integer',
            'categorie_id' => 'required|exists:categories,id',
            'statut' => 'required|in:Disponible,Indisponible',
            'images' => 'required|array|min:1',
            'images.*' => 'image|file|mimes:jpeg,png,jpg,|max:2048',
        ]);

        // Création du produit
        $produit = Produit::create([
            'nom' => $request->input('nom'),
            'description' => $request->input('description'),
            'capacite' => $request->input('capacite'),
            'unite' => $request->input('unite'),
            'couleur' => $request->input('couleur'),
            'prix' => $request->input('prix'),
            'categorie_id' => $request->input('categorie_id'),
            'statut' => $request->input('statut'),

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
        $request->validate([
            'nom' => 'required|string',
            'description' => 'required|string',
            'capacite' => 'nullable|integer',
            'unite' => [
                'required_with:capacite', // L'unité est requise si la capacité est fournie
                Rule::in(['Go', 'To']), // L'unité doit être 'Go' ou 'To'
            ], 
            'couleur' => 'nullable|string', 
            'prix' => 'required|integer',
            'categorie_id' => 'required|exists:categories,id',
            'statut' => 'required|in:Disponible,Indisponible',
            'images' => 'nullable|array|min:1',
            'images.*' => 'nullable|image|file|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Mise à jour des attributs du produit
        $produit->update([
            'nom' => $request->input('nom'),
            'description' => $request->input('description'),
            'capacite' => $request->input('capacite'),
            'unite' => $request->input('unite'),
            'couleur' => $request->input('couleur'),
            'prix' => $request->input('prix'),
            'categorie_id' => $request->input('categorie_id'),
            'statut' => $request->input('statut'),

        ]);

        // Ajout des nouvelles images seulement si elles sont fournies
        if ($request->hasFile('images')) {
            // Supprimer les anciennes images
            $produit->images()->delete();
            
            // Ajouter les nouvelles images
            foreach ($request->file('images') as $image) {
                $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('public/fichiers_produit/', $filename);
                $imageName = 'public/fichiers_produit/' . $filename;

                // Enregistrement du chemin de l'image dans la table produit_images
                Image::create([
                    'produit_id' => $produit->id,
                    'chemin_image' => $imageName,
                ]);
            }
        }

        // Chargement des images pour le produit mis à jour
        $produit->load('images');

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
