<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use App\Models\Variante;
use Illuminate\Http\Request;
use App\Models\VarianteImage;
use App\Jobs\VarianteImageJob;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class VarianteController extends Controller
{

    public function index(Request $request)
    {
        // Récupération de toutes les variantes dans l'ordre décroissant
        $variantes = Variante::orderBy('id', 'desc')->get();

        // Chargement des images pour chaque variante
        $variantes->load('images');

        return response()->json(['variantes' => $variantes], 200);
    }

    public function show(Variante $variante)
    {
        // Chargez les images associées à la variante
        $variante->load('images');

        return response()->json(['variante' => $variante], 200);
    }


    public function store(Request $request)
    {
        $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'type' => 'required|string|in:couleur,capacite',
            'valeur' => 'required|string',
            'unite' => [
                'required_if:type,capacite',
                'nullable',
            ],
            'prix' => 'required|integer',
            'statut' => 'required|in:Disponible,Indisponible',
            'images' => [
                $request->type == 'couleur' ? 'required' : 'nullable',
                'array',
                'min:1',
            ],
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $variante = Variante::create([
            'produit_id' => $request->produit_id,
            'type' => $request->type,
            'valeur' => $request->valeur,
            'unite' => $request->unite ?? null,
            'prix' => $request->prix,
            'statut' => $request->statut,

        ]);

        // Ajout des images à la variante actuelle si le type est "couleur"
        if ($request->type == 'couleur' && $request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('public/fichiers_variante/', $filename);
                $imageName = 'public/fichiers_variante/' . $filename;

                // dispatch(new VarianteImageJob($varianteId->id, $imageName));


                VarianteImage::create([
                    'variante_id' => $variante->id,
                    'chemin_image' => $imageName,
                ]);
            }
        }

        // Chargement des images pour la variante actuelle
        $variante->load('images');

        return response()->json(['message' => 'Variante ajoutée avec succès', 'variante' => $variante], 201);
    }


    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'produit_id' => 'required|exists:produits,id',
    //         'type' => 'required|string|in:couleur,capacite',
    //         'valeur' => 'required|string',
    //         // 'unite' => [
    //         //     'required_if:type,capacite', // L'unité est requise si le type est 'capacite'
    //         //     Rule::in(['Go', 'To']), // L'unité doit être 'Go' ou 'To'
    //         // ],
    //         'unite' => [
    //             'required_if:type,capacite', // L'unité est requise si le type est 'capacite'
    //             'nullable', // L'unité peut être null si le type est 'couleur'
    //         ],
    //         'prix' => 'required|numeric',
    //         'images' => 'nullable|array|min:1',
    //         'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //     ]);

    //     $variante = Variante::create([
    //         'produit_id' => $request->produit_id,
    //         'type' => $request->type,
    //         'valeur' => $request->valeur,
    //         'unite' => $request->unite ?? null, // Assurez-vous que l'unité est correctement définie ou null si non applicable
    //         'prix' => $request->prix,
    //     ]);

    //     // Ajout des images à la variante actuelle
    //     if ($request->hasFile('images')) {
    //         foreach ($request->file('images') as $image) {
    //             $filename = uniqid() . '.' . $image->getClientOriginalExtension();
    //             $path = $image->storeAs('public/fichiers_variante/', $filename);
    //             $imageName = 'public/fichiers_variante/' . $filename;

    //             // Enregistrement du chemin de l'image dans la table variante_images
    //             VarianteImage::create([
    //                 'variante_id' => $variante->id,
    //                 'chemin_image' => $imageName,
    //             ]);
    //         }
    //     }

    //     // Chargement des images pour la variante actuelle
    //     $variante->load('images');

    //     return response()->json(['message' => 'Variante ajoutée avec succès', 'variante' => $variante], 201);
    // }

    public function stores(Request $request)
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
            'statut' => 'required|in:Disponible,Indisponible',
            'unite' => [
                'required_if:type,capacite',
                'nullable',
            ],
            'images' => 'nullable|array|min:1', 
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Mise à jour des attributs de la variante
        $variante->update([
            'type' => $request->type,
            'valeur' => $request->valeur,
            'prix' => $request->prix,
            'statut' => $request->statut,
            'unite' => $request->unite ?? null, // Assurez-vous que l'unité est correctement définie ou null si non applicable
        ]);

        // Ajout des nouvelles images seulement si elles sont fournies
        if ($request->hasFile('images')) {
            // Supprimer les anciennes images
            // $variante->images()->delete();
            
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


    public function delete(Request $request, $id)
    {
            $variante = Variante::withTrashed()->find($id);

            if ($variante) {
                $variante->delete();
                return response()->json(['message' => 'Variante supprimé avec succès'], 200);
            } else {
                return response()->json(['error' => 'Variante non trouvé'], 404);
            }
    }


}
