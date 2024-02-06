<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use App\Models\Variante;
use Illuminate\Http\Request;
use App\Models\VarianteImage;
use App\Jobs\VarianteImageJob;
use App\Http\Controllers\Controller;

class VarianteController extends Controller
{
    public function store(Request $request)
    {  
        $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'variantes.*.type' => 'required|string|in:couleur,capacite',
            'variantes.*.valeur' => 'required|string',
            'variantes.*.prix' => 'required|numeric',
            'variantes.*.images' => 'nullable|array|min:1', 
            'variantes.*.images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $variantes = [];

        foreach ($request->variantes as $varianteData) {
            $variante = Variante::create([
                'produit_id' => $request->produit_id, 
                'type' => $varianteData['type'],
                'valeur' => $varianteData['valeur'],
                'prix' => $varianteData['prix'],
            ]);

            // Ajout des images à la variante actuelle
            if (isset($varianteData['images'])) {
                foreach ($varianteData['images'] as $image) {
                    $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('public/fichiers_variante/', $filename);
                    $imageName = 'public/fichiers_variante/' . $filename;

                    // Enregistrement du chemin de l'image dans la table variante_images
                    VarianteImage::create([
                        'variante_id' => $variante->id,
                        'chemin_image' => $imageName,
                    ]);
                }
            }

            // Chargement des images pour la variante actuelle
            $variante->load('images');

            // Ajout de la variante à la liste des variantes
            $variantes[] = $variante;
        }

        return response()->json(['message' => 'Variantes ajoutées avec succès', 'variantes' => $variantes], 201);
    }

    public function update(Request $request, Variante $variante)
    {  
        $request->validate([
            'type' => 'required|string|in:couleur,capacite',
            'valeur' => 'required|string',
            'prix' => 'required|numeric',
            'images' => 'nullable|array|min:1', 
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $variante->update([
            'type' => $request->type,
            'valeur' => $request->valeur,
            'prix' => $request->prix,
        ]);

        // Ajouter les nouvelles images seulement si elles sont fournies
        if ($request->hasFile('images')) {
            // Supprimer les images existantes
            $variante->images()->delete();
            
            // Ajouter les nouvelles images
            foreach ($request->file('images') as $image) {
                $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('public/fichiers_variante/', $filename);
                $imageName = 'public/fichiers_variante/' . $filename;

                // Enregistrement du chemin de l'image dans la table variante_images
                VarianteImage::create([
                    'variante_id' => $variante->id,
                    'chemin_image' => $imageName,
                ]);
            }
        }

        // Chargement des images pour la variante mise à jour
        $variante->load('images');

        return response()->json(['message' => 'Variante mise à jour avec succès', 'variante' => $variante], 200);
    }




}
