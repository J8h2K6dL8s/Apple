<?php

namespace App\Http\Controllers;

use App\Models\Panier;
use App\Models\Produit;
use App\Models\Variante;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Cart;

class PanierController extends Controller
{

    public function addcart(Request $request, $id) {
        $token = $request->header('Authorization');
        $paniers = Panier::where('token', $token)->get(); 
        $ids = Panier::where('token', $token)->pluck('idProduit')->toArray();
    
        $product=Produit::find($id);
        
        if($product) {
            // Vérifier si la variante est spécifiée
            $idVariante = $request->input('idVariante');
            $prix = $product->prix; 
    
            if ($idVariante) {
                $variante = Variante::find($idVariante);
                if ($variante && $variante->produit_id == $id) { 
                    $prix = $variante->prix; // Utilisez le prix de la variante
                }
            }
    
            if(count($paniers) !== 0) {
                if(in_array($product->id, $ids)) {
                    $pdt = Panier::where('idProduit', $product->id)->first();
                    $pdt->qty += 1;
                    // $pdt->prix = $prix * $pdt->qty; 
                    $pdt->save();
                    return response(["message"=> "Produit ajouté" ], 200);
                } else {
                    Panier::create([
                        'token' => $token,
                        'idProduit' => $product->id,
                        'idVariante' => $idVariante,
                        'nomProduit' => $product->nom,
                        'qty' => 1,
                        'prix' => $prix 
                    ]);
                    return response(["message"=> "Produit ajouté" ], 200);
                }
            } else {
                Panier::create([
                    'token' => $token,
                    'idProduit' => $product->id,
                    'nomProduit' => $product->nom,
                    'qty' => 1,
                    'prix' => $prix
                ]);
                return response(["message"=> "Produit ajouté" ], 200);
            }
        } else {
            return response(["message"=>"Produit non trouvé"], 404);
        }
    }

    public function recupererContenuPanier(Request $request)
    {   
        $token = $request->header('Authorization');  
        $paniers = Panier::where('token', $token)->get();

        $prixTotal = 0;

        // Parcourez tous les paniers de l'utilisateur
        foreach ($paniers as $produit) {
            $prixTotal += $produit->prix * $produit->qty;
        }
        
        // Répondez avec le contenu du panier et le prix total
        return response()->json(['paniers' => $paniers, 'prixTotal' => $prixTotal]);
    }

    public function removeCart(Request $request, $id) {
        // Récupérez le jeton ou l'ID de l'utilisateur, selon votre système d'authentification
        $token = $request->header('Authorization'); 
    
        // Recherchez le panier de l'utilisateur en fonction du jeton ou de l'ID utilisateur.
        $produit = Panier::where('token', $token)->where('idProduit', $id)->first();
    
        if ($produit) {

            $produit->delete();
    
            return response(["message" => "Produit supprimé du panier"], 200);
        } else {
            return response(["message" => "Produit introuvable"], 404);
        }
    }

    public function modifierQuantite(Request $request, $id)
    {
        $token = $request->header('Authorization'); 
        $produit = Panier::where('token', $token)->where('idProduit', $id)->first();

        if ($produit) {
            // Utilisez la méthode `where` pour trouver l'élément spécifique du panier en fonction de l'ID du produit et du panier.
            $produit->qty = $request->qte;
             $produit->save();
    
            return response(["message" => "Quantité mis a jour avec succès"], 200);
        } else {
            return response(["erreur" => "Produit introuvable"], 404);
        }
        $panier = Panier::findOrFail($id);
      

        // Redirigez l'utilisateur vers la page du panier ou une autre page appropriée
        return response(["message" => "Quantité mise à jour avec succès"], 404);

        // return redirect()->route('panier.index')->with('success', 'Quantité mise à jour avec succès');
    }
    

        
}
