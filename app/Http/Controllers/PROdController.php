<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PROdController extends Controller
{

    public function storeVraie(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'description' => 'required|string',
            'capacite' => 'nullable|string', // Ajoutez la validation pour la capacité
            'couleur' => 'nullable|string', 
            'prix' => 'required|numeric',
            'categorie_id' => 'required|exists:categories,id',
            'images' => 'required|array|min:1', // Validation pour les images du produit
            'images.*' => 'image|file|mimes:jpeg,png,jpg,|max:2048',
            'variantes.*.type' => 'string|in:couleur,capacite', 
            'variantes.*.valeur' => 'string',
            'variantes.*.prix' => 'numeric',
            'variantes.*.image.*' => 'image|file|mimes:jpeg,png,jpg,|max:2048',
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

        // Ajout des variantes si elles sont fournies
        if ($request->has('variantes')) {
            foreach ($request->variantes as $varianteData) {
                // Créez une variante
                $variante = new Variante([
                    'type' => $varianteData['type'],
                    'valeur' => $varianteData['valeur'],
                    'prix' => $varianteData['prix'],
                ]);
                $produit->variantes()->save($variante);

                // $produit->load('variantes');

            
                //Gérez les images de la variante
                // if ($request->hasFile('images')) {

                //     foreach ($request->file('images') as $image) {
                //         $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                //         $path = $image->storeAs('public/fichiers_variante/', $filename);
                //         $imageName = 'public/fichiers_variante/' . $filename;
        
                //         // Dispatch du job pour traiter l'image en arrière-plan
                //         dispatch(new VarianteImageJob($variante->id, $imageName));

                //     }
                // }
                // $variante->images;

            }
            
        }
        $produit->load('variantes');

        return response()->json(['message' => 'Produit ajouté avec succès', 'produit' => $produit], 201);
    }

     // public function store(Request $request)
    // {
        
    //     $request->validate([
    //         'nom' => 'required|string',
    //         'description' => 'required|string',
    //         'prix' => 'required|numeric',
    //         'categorie_id' => 'required|exists:categories,id',
    //         'images' => 'required|array|min:1',
    //         'images.*' => 'image|mimes:jpeg,png,jpg,|max:2048',
    //         'variantes.*.capacite' => 'string',
    //         'variantes.*.couleur' => 'string',
    //         'variantes.*.prix' => 'numeric',
    //     ]);

    //     // Création du produit
    //     $produit = Produit::create([
    //         'nom' => $request->input('nom'),
    //         'description' => $request->input('description'),
    //         'prix' => $request->input('prix'),
    //         'categorie_id' => $request->input('categorie_id'),
    //     ]);

    //     // Ajout des variantes si elles sont fournies

    //     if ($request->has('variantes')) {
    //         foreach ($request->variantes as $varianteData) {
    //             // Créer une nouvelle instance de Variante
    //             $addVar = new Variante();
    //             $addVar->produit_id = $produit->id;
    //             $addVar->capacite = $varianteData['capacite'];
    //             $addVar->couleur = $varianteData['couleur'];
    //             $addVar->prix = $varianteData['prix'];
    //             $addVar->save();
    //         }
        
    //         // Charger les variantes pour les associer au produit
    //         $produit->load('variantes');

    //         // Attribuer les variantes au produit
    //         $produit->variantes = $produit->variantes;
    //     }

    //     // Gestion de l'ajout des images

    //     if ($request->hasFile('images')) {
    //         foreach ($request->file('images') as $image) {
    //             $filename = uniqid() . '.' . $image->getClientOriginalExtension();

    //             // Stockez l'image avec le nom généré
    //             $path = $image->storeAs('public/fichiers_produit/', $filename);

    //             // Assurez-vous que le chemin stocké dans la base de données est relatif
    //             $imageName = 'public/fichiers_produit/' . $filename;

    //             dispatch(new ImageJob($produit->id, $imageName));

    //         }
    //     }
    //     $produit->save();

    //     $produit->images;

    //     return response()->json(['message' => 'Produit ajouté avec succès', 'produit' => $produit], 201);
    // }

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
            'capacite' => 'nullable|string', // Ajoutez la validation pour la capacité
            'couleur' => 'nullable|string', 
            'prix' => 'required|numeric',
            'categorie_id' => 'required|exists:categories,id',
            'images' => 'required|array|min:1', // Validation pour les images du produit
            'images.*' => 'image|file|mimes:jpeg,png,jpg,|max:2048',
            'variantes.*.type' => 'string|in:couleur,capacite', 
            'variantes.*.valeur' => 'string',
            'variantes.*.prix' => 'numeric',
            'variantes.*.image.*' => 'image|file|mimes:jpeg,png,jpg,|max:2048',
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

        // Ajout des variantes si elles sont fournies
        if ($request->has('variantes')) {
            foreach ($request->variantes as $varianteData) {
                // Créez une variante
                $variante = new Variante([
                    'type' => $varianteData['type'],
                    'valeur' => $varianteData['valeur'],
                    'prix' => $varianteData['prix'],
                ]);
                $produit->variantes()->save($variante);

                $produit->load('variantes');

                //     // Gérez l'image de la variante
                // $varianteImage = $varianteData['image'];
                // $filename = uniqid() . '.' . $varianteImage->getClientOriginalExtension();
                // $path = $varianteImage->storeAs('public/fichiers_variante/', $filename);
                // $imageName = 'public/fichiers_variante/' . $filename;

                // dispatch(new VarianteImageJob($variante->id, $imageName));

            
                //Gérez les images de la variante
                if (isset($varianteData['images']) && is_array($varianteData['images'])) {
                    foreach ($varianteData['images'] as $image) {
                        $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                        $path = $image->storeAs('public/fichiers_variante/', $filename);
                        $imageName = 'public/fichiers_variante/' . $filename;
            
                        // Dispatch du job pour traiter l'image en arrière-plan
                        dispatch(new VarianteImageJob($variante->id, $imageName));

                    }
                }
            }
            
        }
        $produit->load('variantes');

        return response()->json(['message' => 'Produit ajouté avec succès', 'produit' => $produit], 201);
    }

    public function storeOriginal(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'description' => 'required|string',
            'capacite' => 'nullable|string',
            'couleur' => 'nullable|string',
            'prix' => 'required|numeric',
            'categorie_id' => 'required|exists:categories,id',
            'images' => 'required|array|min:1',
            'images.*' => 'image|file|mimes:jpeg,png,jpg,|max:2048',
            'variantes.*.type' => 'string|in:couleur,capacite',
            'variantes.*.valeur' => 'string',
            'variantes.*.prix' => 'numeric',
            'variantes.*.image' => 'image|mimes:jpeg,png,jpg,|max:2048',
        ]);
    
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
    
        // Ajout des variantes si elles sont fournies
        if ($request->has('variantes')) {
            foreach ($request->variantes as $varianteData) {
                $variante = new Variante([
                    'type' => $varianteData['type'],
                    'valeur' => $varianteData['valeur'],
                    'prix' => $varianteData['prix'],
                ]);
                $produit->variantes()->save($variante);
    
                // Gérez l'image de la variante
                if ($varianteData['image']) {
                    $varianteImage = $varianteData['image'];
                    $filename = uniqid() . '.' . $varianteImage->getClientOriginalExtension();
                    $path = $varianteImage->storeAs('public/fichiers_variante/', $filename);
                    $imageName = 'public/fichiers_variante/' . $filename;
    
                    dispatch(new VarianteImageJob($variante->id, $imageName));
                }
            }
        }
    
        $produit->load('variantes');
    
        return response()->json(['message' => 'Produit ajouté avec succès', 'produit' => $produit], 201);
    }
    

    public function update(Request $request, Produit $produit)
    {
        $request->validate([
            'nom' => 'required|string',
            'description' => 'required|string',
            'prix' => 'required|numeric',
            'categorie_id' => 'required|exists:categories,id',
            'images' => 'required|array|min:1', // Validation pour les images du produit
            'images.*' => 'image|mimes:jpeg,png,jpg,|max:2048',
            'variantes.*.capacite' => 'string',
            'variantes.*.couleur' => 'string',
            'variantes.*.prix' => 'numeric',
            'variantes.*.images' => 'array|min:1', // Validation pour les images de chaque variante
            'variantes.*.images.*' => 'image|mimes:jpeg,png,jpg,|max:2048',
        ]);

        // Mise à jour des informations de base du produit
        $produit->update([
            'nom' => $request->input('nom'),
            'description' => $request->input('description'),
            'prix' => $request->input('prix'),
            'categorie_id' => $request->input('categorie_id'),
        ]);

        // Mise à jour des images du produit (si fournies)
        if ($request->has('images')) {
            foreach ($request->images as $image) {
                $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('public/fichiers_produit/', $filename);
                $imageName = 'public/fichiers_produit/' . $filename;

                // Dispatch du job pour traiter l'image en arrière-plan
                dispatch(new ImageJob($produit->id, $imageName));
            }
        }

        // Mise à jour des variantes (si fournies)
        if ($request->has('variantes')) {
            foreach ($request->variantes as $varianteData) {
                $variante = Variante::updateOrCreate(
                    ['id' => $varianteData['id']], // Supposons que chaque variante ait un champ 'id'
                    [
                        'capacite' => $varianteData['capacite'],
                        'couleur' => $varianteData['couleur'],
                        'prix' => $varianteData['prix'],
                    ]
                );

                // Mise à jour des images de la variante (si fournies)
                if (isset($varianteData['images']) && is_array($varianteData['images'])) {
                    foreach ($varianteData['images'] as $image) {
                        $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                        $path = $image->storeAs('public/fichiers_variante/', $filename);
                        $imageName = 'public/fichiers_variante/' . $filename;

                        // Dispatch du job pour traiter l'image en arrière-plan
                        dispatch(new VarianteImageJob($variante->id, $imageName));
                    }
                }
            }
        }

        $produit->load('variantes');

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

    public function stores(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'description' => 'required|string',
            'prix' => 'required|numeric',
            'categorie_id' => 'required|exists:categories,id',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,|max:2048',
            'variantes.*.capacite' => 'string',
            'variantes.*.couleur' => 'string',
            'variantes.*.prix' => 'numeric',
        ]);

        // Création du produit
        $produit = Produit::create([
            'nom' => $request->input('nom'),
            'description' => $request->input('description'),
            'prix' => $request->input('prix'),
            'categorie_id' => $request->input('categorie_id'),
        ]);

        // Ajout des variantes si elles sont fournies
        if ($request->has('variantes')) {
            $variantes = [];
            foreach ($request->variantes as $varianteData) {
                $variantes[] = new Variante([
                    'capacite' => $varianteData['capacite'],
                    'couleur' => $varianteData['couleur'],
                    'prix' => $varianteData['prix'],
                ]);
            }

            // Associer les variantes au produit
            $produit->variantes()->saveMany($variantes);

            // Charger les variantes pour les inclure dans la réponse
            $produit->load('variantes');
        }

        // Gestion de l'ajout des images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('public/fichiers_produit/', $filename);
                $imageName = 'public/fichiers_produit/' . $filename;

                // Dispatch du job pour traiter l'image en arrière-plan
                dispatch(new ImageJob($produit->id, $imageName));
            }
        }

        return response()->json(['message' => 'Produit ajouté avec succès', 'produit' => $produit], 201);
    }

}
