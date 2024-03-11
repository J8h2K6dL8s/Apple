<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LivraisonController extends Controller
{
    public function payer(Request $request) {

        $user = auth('sanctum')->user(); 

        // Initialisation des variables
        $codePromo = null;
        $prix = 0;
        $qty = 0;
        $produits = [];

        // Vérification de l'existence et de l'utilisation du code promo
        if ($request->has('codepromo') && $request->codepromo !== "UNDEFINED") {
            $promo = codePromo::where('intitule', $request->input('codepromo'))->first();
            if ($promo && $promo->nombreUtilisation > 0) {
                $codePromo = $request->codepromo;
                $promo->nombreUtilisation--;
                $promo->save();
            } else {
                return response()->json(['erreur' => "Code promo erroné ou épuisé"], 400);
            }
        }

        // Calcul du prix en fonction des produits ou du panier
        if (!$request->has('idProduit')) {
            $url = "https://mrapple-store.com/payer";
            $token = $request->header('Authorization');
            $paniers = Panier::where('token', $token)->get();
            foreach ($paniers as $produit) {
                $qty += $produit->qty;
                $prix += $produit->prix * $produit->qty;
                $produits[] = ["id" => $produit->idProduit, "qty" => $produit->qty];
            }
        } else {
            $url = "https://mrapple-store.com/payer";
            $produit = Produit::findOrFail($request->idProduit);
            $produits[] = ["id" => $produit->id, "qty" => $produit->quantite];
            $prix = $produit->prix;
            $qty = 1;
        }

        // Appliquer la réduction du code promo au prix total
        if ($codePromo) {
            $prix -= $promo->valeur * $prix / 100;
        }

        // Génération de l'ID de commande aléatoire
        $orderID = 'MRAPPLE_ORDER-' . mt_rand(1000, 9999);
        $produits_serialized = json_encode($produits);

        // Création de la commande avec le statut "Payer à la livraison"
        $vente = Commande::create([
            'order_id' => $orderID,
            'codePromo' => $codePromo,
            'produit_id' => $produits_serialized,
            'prix_total' => $prix,
            'status' => "Payer à la livraison",
            'quantite' => $qty,
            'user_id' => $user->id,
            'user_name' => $user->nom . ' ' . $user->prenoms,
            'date_created' => now()
        ]);

        // Retourner la réponse avec l'URL de confirmation de commande et les détails de la commande
        return response()->json(['confirmation_url' => $url, 'commande' => $vente], 200);
    }

    public function savePayment(Request $request) {
        $validator = Validator::make($request->all(), [
             "idTransaction" => 'required|integer', 
             "order_id" => 'required'
        ]);
        
        if ($validator->fails()) {
            return response([
                 'errors' => $validator->errors(),
            ], 422); // Code de réponse HTTP 422 Unprocessable Entity
        }
        
        try {
            \FedaPay\FedaPay::setApiKey("sk_sandbox_fKlwsz7knL1sZiXTjXLhTaOw");
            \FedaPay\FedaPay::setEnvironment('sandbox');
            
            $transaction = \FedaPay\Transaction::retrieve($request->idTransaction);
 
            if ($transaction->status == "approved") {
                return response(['error' =>$transaction], 404);
            } 
    
            $user = auth('sanctum')->user(); // Utilisateur actuellement authentifié
    
            if(empty($request->produit_id)){
                $token = $request->header('Authorization');
                $paniers = Panier::where('token', $token)->get();
            
                if(!$paniers){
                    return response(['message' => 'Panier vide'], 404);
                }
                
                $idsDansLePanier = [];
                $nomProduits= "" ;
                foreach ($paniers  as $item) {
                    $idsDansLePanier[] = $item->idProduit;
                    $nomProduits .= $item->nomProduit.' \ ';
                } 
                $produits=$idsDansLePanier;
                $listeProduit = $nomProduits;
            }
            else{
                $produits =$request->produit_id;
                $listeProduit = Produit::find($request->produit_id)->nom;
            } 
                
            $vente = Commande::where('order_id', $request->order_id)->first();
                
            if($vente && $transaction->status == 'approved'){
                // Mise à jour du statut de la commande en "en attente"
                $vente->status = 'En attente';
                $vente->save();
                
                // Suppression des paniers
                $paniers = Panier::where('token', $token)->delete();
    
                // Envoi du mail à l'adresse de contact
                Mail::to("contact@mrapple-store.com")->send(new OrderAchatMail($vente, $listeProduit));
                
                // Envoi du mail à l'utilisateur
                if(Mail::to($user->email)->send(new OrderAchatMail($vente, $listeProduit))){
                    return response(['success' => 'Achat effectué avec succès. Un e-mail a été envoyé à '.$user->email, 'id'=>$vente->id ], 200);
                } else {
                    // Gestion d'une éventuelle erreur lors de l'envoi du mail
                    return response(['error' => 'Erreur lors de l\'envoi du mail'], 500);
                }                   
            }
            else{
                return response(['error' => 'Commande non trouvée'], 404);
            }
        } catch (\FedaPay\Error\Base $e) {
            return response(['error' => 'Transaction erronée'], 500);
        }  
    }

    public function payments(Request $request ){
  
        if($request->codepromo !== "UNDEFINED"){
           
            $promo = codePromo::where('intitule', $request->input('codepromo'))->first();
            
                if($promo && $promo->nombreUtilisation > 0){
                    $codePromo =$request->codepromo ;
                    $promo->nombreUtilisation =  $promo->nombreUtilisation - 1;
                    $promo ->save();
                 
                    if(!$request->idProduit) {
                           $url="https://mrapple-store.com/panier";
            $token = $request->header('Authorization');
            $paniers = Panier::where('token', $token)->get();
            $prix = 0;
             $qty= 0;
             $idsDansLePanier = [];
            
           foreach ($paniers  as $produit) {
              
                $qty += $produit->qty;
                
                 $prix += $produit->prix *  $qty;
                $idsDansLePanier[] =["id" =>$produit->idProduit, "qty" =>$produit->qty];
             }
              $produits=$idsDansLePanier;
           
              $prix = $prix - $promo->valeur * $prix /100;
                      
                    }
                    else {
                           $url="https://mrapple-store.com/payment";
            $produit=Produit::findorfail($request->idProduit);
            $produits[]=["id" =>$produit->id, "qty" =>$produit->quantite];
            $prix =$produit->prix; 
            $qty = 1;
                       
                        $prix = $prix - $promo->valeur * $prix /100;
                    }
                }
                else 
                {
                    return response()->json(['erreur' => "Code promo errone ou code épuisé "]);
                }
        } 
        else {
       
            $codePromo=null;
            if(!$request->idProduit ) {
                $url="https://mrapple-store.com/panier";
                $token = $request->header('Authorization');
                $paniers = Panier::where('token', $token)->get();
                $prix = 0;
                $qty= 0;
                $idsDansLePanier = [];
            foreach ($paniers  as $produit) {
                    $qty += $produit->qty;
                    $prix += $produit->prix *  $produit->qty;
                    $idsDansLePanier[] =["id" =>$produit->idProduit, "qty" =>$produit->qty];
                }
                $produits=$idsDansLePanier;
    
            }
            // else {
                
            //         $url="https://mrapple-store.com/payement";
            //     $produit=Produit::findorfail($request->idProduit);
            //     $produits[]=["id" =>$produit->id, "qty" =>$produit->quantite];
            //     $prix =$produit->prix; 
            //     $qty = 1;
            // }
 
        }
      
       
        $prefix = 'MRAPPLE_ORDER-';
        $randomNumber = mt_rand(1000, 9999); 
        $orderID = $prefix . $randomNumber;
       $produits_serialized = json_encode($produits);

      
        $vente=Commande::create([
            'order_id' => $orderID,
            'codePromo' => $codePromo ? $codePromo : null,
            'produit_id'=> $produits_serialized ,
            'prix_total' => $prix ,
            'status' => "Unpaid",
            'quantite' => $qty ,
            'user_id' => app('currentUser')->id,
            'user_name' => app('currentUser')->nom.' '.app('currentUser')->prenoms,
            'date_created'=> Carbon::now()
          ]);
         
        //  $_SESSION[app('currentUser')->nom]=$vente->order_id; 
          

 
         /* Rempacez VOTRE_CLE_API par votre véritable clé API */
        \FedaPay\FedaPay::setApiKey("sk_sandbox_fKlwsz7knL1sZiXTjXLhTaOw");
         // \FedaPay\FedaPay::setApiKey("sk_sandbox_mGVNXupMPNzgS08eH8BGsJlo");
       /* Précisez si vous souhaitez exécuter votre requête en mode test ou live */
           \FedaPay\FedaPay::setEnvironment('sandbox'); //ou setEnvironment('live');
 
           /* Créer la transaction */ 
          $transaction = \FedaPay\Transaction::create(array(
           "description" =>   app('currentUser')->nom." ".$prix,
           "amount" => $prix,
           "currency" => ["iso" => "XOF"],
           "callback_url" => $url,
           "customer" => [
               "firstname" =>app('currentUser')->nom,
            //    "lastname" => app('currentUser')->prenoms,
               "email" => app('currentUser')->email,
               "phone_number" => [
                   "number" => app('currentUser')->telephone,
                   "country" => "bj"
               ]
           ]
           ));
           
          
           $token = $transaction->generateToken(); 
           
           return response()->json(['url' => $token->url, 'commande'=> $vente], 200);
        
          
    }

    // public function savePayment(Request $request) {
       
    //     $validator = Validator::make($request->all(), [
    //          "idTransaction" => 'required', 
    //          "order_id" => 'required'
    //        ]);
           
    //         if ($validator->fails()) {
    //           return response([
    //                  'errors' => $validator->errors(),
    //           ], 422); // Code de r&eacute;ponse HTTP 422 Unprocessable Entity
    //         }
          
    //         try {
    //         \FedaPay\FedaPay::setApiKey("sk_sandbox_fKlwsz7knL1sZiXTjXLhTaOw");
    //         \FedaPay\FedaPay::setEnvironment('sandbox');
        
    //         $transaction = \FedaPay\Transaction::retrieve($request->idTransaction);

    //         if ($transaction->status !== "approved") {
    //                 return response(['error' => 'Transaction echouée'], 404);
    //         } 


    //         if(empty($request->produit_id)){
    //             $token = $request->header('Authorization');
    //             $paniers = Panier::where('token', $token)->get();
        
    //                 if(!$paniers){
    //                     return response(['message' => 'Panier vide', 404]);
    //                 }
            
    //             $idsDansLePanier = [];
    //             $nomProduits= "" ;
    //         foreach ($paniers  as $item) {
    //                 $idsDansLePanier[] = $item->idProduit;
    //                 $nomProduits .= $item->nomProduit.' \ ';
    //             } 
    //             $produits=$idsDansLePanier;
    //             $listeProduit = $nomProduits;

        
    //         }
    //         else{

    //             $produits =$request->produit_id;

    //             $listeProduit = Produit::find($request->produit_id)->nom;
    //         } 
            
    //         $vente = Commande::where('order_id', $request->order_id)->first();
            
            
    //         if($vente && $transaction->status == 'approved'){
            
    //         // Session::forget(app('currentUser')->nom); 
    //         $paniers = Panier::where('token', $token)->delete();
    //         Mail::to("contact@mrapple-store.com")->send(new OrderAchatMail( $vente,$listeProduit));
    //             if(Mail::to(app('currentUser')->email)->send(new OrderAchatMail( $vente,$listeProduit)))
    //             {
    //                 return response(['success' => 'Achat effectue avec succes', 'id'=>$vente->id ], 200);
    //             } else {dd("error");}
                    
    //         }
    //         else{
    //             return response(['error' => 'Commande non trouvé'], 404);
    //         }

    //     } catch (\FedaPay\Error\Base $e) {
    //         return response(['error' => 'Transaction erronée'], 500);

    //     }  
    // }
}
