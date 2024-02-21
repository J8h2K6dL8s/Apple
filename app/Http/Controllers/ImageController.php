<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Produit;
use App\Models\Variante;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\VarianteImage;
use Illuminate\Support\Facades\Storage;


class ImageController extends Controller
{

    public function addImages(Request $request)
    {
        // Validation des données envoyées
        $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'images.*' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Validation des images
        ]);
    
        // Récupération du produit
        $produit = Produit::findOrFail($request->produit_id);
    
        // Création d'un tableau pour stocker les chemins des images ajoutées
        $imagePaths = [];
    
        // Traitement des images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Téléchargement et stockage de l'image
                $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('public/fichiers_produit/', $filename);
                $imageName = 'public/fichiers_produit/' . $filename;
    
                // Création d'une nouvelle instance de modèle Image et association au produit
                $produit->images()->create([
                    'chemin_image' => $imageName,
                ]);
    
                // Ajout du chemin de l'image au tableau
                $imagePaths[] = $imageName;
            }
        }
    
        return response()->json(['message' => 'Images ajoutées avec succès.', 'images' => $imagePaths], 200);
    }

    public function deleteImage(Request $request, $lieuId)
    {
            $images = Image::withTrashed()->find($lieuId);

            if ($images) {
                $images->delete();
                return response()->json(['message' => 'Image supprimé avec succès'], 200);
            } else {
                return response()->json(['error' => 'Image non trouvé'], 404);
            }
    }


    public function addImagesVariante(Request $request)
    {
        $request->validate([
            'variante_id' => 'required|exists:variantes,id',
            'images' => 'required|array|min:1',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Récupération de la variante
        $variante = Variante::findOrFail($request->variante_id);

        // Traitement des images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Téléchargement et stockage de l'image
                $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('public/fichiers_variante/', $filename);
                $imageName = 'public/fichiers_variante/' . $filename;

                // Enregistrement du chemin de l'image dans la table variante_images
                $variante->images()->create([
                    'chemin_image' => $imageName,
                ]);
            }
        }

        // Chargement des images pour la variante actuelle
        $variante->load('images');

        return response()->json(['message' => 'Images ajoutées à la variante avec succès', 'variante' => $variante], 200);
    }

    public function delete(Request $request, $lieuId)
    {
            $images = VarianteImage::withTrashed()->find($lieuId);

            if ($images) {
                $images->delete();
                return response()->json(['message' => 'Image supprimé avec succès'], 200);
            } else {
                return response()->json(['error' => 'Image non trouvé'], 404);
            }
    }

    

}
