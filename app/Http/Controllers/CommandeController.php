<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Produit;
use App\Models\Commande;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\OrderDetailsMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class CommandeController extends Controller
{
    
    public function index()
    {
        $commandes = Commande::orderByDesc('id')->get();

        return response(["commandes" => $commandes], 200);
    }

    public function show(string $id)
    {
        // Récupérez la commande par son ID
        $commande = Commande::find($id); 

        if ($commande) {
            // Trouvez le client associé à la commande
            $user = User::find($commande->user_id);

            // Décodez la liste des produits de la commande
            $produitsCommande = json_decode($commande->produit_id, true); 

            // Initialisez un tableau pour stocker les détails des produits de la commande
            $produits = [];

            // Parcourez les produits de la commande et récupérez les détails de chaque produit
            foreach ($produitsCommande as $item) {
                $idProduit = $item['id'];
                $quantite = $item['qty']; // Supposons que la quantité soit également dans l'objet JSON

                // Récupérez les détails du produit
                $produit = Produit::find($idProduit);
                
                // Ajoutez la propriété de quantité à l'objet $produit
                $produit->quantite = $quantite;
                
                // Ajoutez le produit à la liste des produits de la commande
                $produits[] = $produit;
            }

            // Ajoutez les produits et d'autres détails à l'objet de commande
            $commande->produits = $produits;
            $commande->customer_telephone =  $user->telephone;
            $commande->customer_email =  $user->email;
            
            // Retournez la commande avec ses détails
            return response()->json(['message' => $commande], 200);
        } else {
            // Si la commande n'est pas trouvée, renvoyez une réponse 404
            return response()->json(['message' => 'Commande non trouvée'], 404);
        }
    }

    public function mesCommandes()
    {
        $user = auth('sanctum')->user();

        $commandes = Commande::where('user_id', $user->id)
                            ->orderBy('id', 'desc')
                            ->get();

        return response(["commandes" => $commandes], 200);
    }

    public function validercommande(Request $request){
     
        $validator = Validator::make($request->all(), [
         
             'order_id' => 'required',
             'status' => 'required|in:Annulee,Livree',
           ]);
 
            
         if ($validator->fails()) {
               return response(['errors' => $validator->errors(), ], 422); 
           } 
          else {
                
             $commande = Commande::where('order_id', $request->order_id)->first();
             if($commande){ 
                     
                 if ($request->status =="Annulee") {
                     $commande->status = $request->status;
                     $commande->save();
                        return response(['message' => "Commande annulée"], 200);
                 }
                 elseif($request->status =="Livree"){
                         $data = $request->details; 
                         $commande->status = $request->status;
                         $commande->details = $data;
                         $commande->save();
                         $user= User::where('id', $commande->user_id)->first();
                        
                        if(Mail::to($user->email)->send(new OrderDetailsMail( $user,$data, $commande))){
                                 return response(['message' => "Details envoyé ".$user->email], 200);
                         } 
             
                       }
         
             }
                 else {
                     return response(['message' => 'Commande non trouvé'], 404);
                 }
 
            
         }
    }

    public function nombreCommandesEnAttente()
    {
        $nombreEnAttente = Commande::where('statut', 'en_attente')->count();
        
        return response()->json(['message' => "Le nombre de commande en attente est : " . $nombreEnAttente], 200);
    }

    public function nbrTotalCommandes(){

        $total = Commande::where('status', '!=', 'Unpaid')->count();

        return response()->json(['message' =>  $total], 200); 

    } 

}
