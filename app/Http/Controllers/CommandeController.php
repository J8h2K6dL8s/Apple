<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Panier;
use App\Models\Produit;
use App\Models\Commande;
use App\Models\codePromo;
use Illuminate\Http\Request;
use App\Mail\OrderDetailsMail;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Mail\OrderAchatMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
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
    public function mesCommande()
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
                                 return response(['message' => "Details envoyé à ".$user->email], 200);
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
        $nombreEnAttente = Commande::where('status', 'En attente')->count();
        
        return response()->json(['message' => "Le nombre de commande en attente est : " . $nombreEnAttente], 200);
    }

    public function nbrTotalCommandes(){

        $total = Commande::where('status', '!=', 'Unpaid')->count();

        return response()->json(['message' =>  $total], 200); 

    } 

    public function payment(Request $request) {
  
        $user = auth('sanctum')->user(); 
    
        if($request->codepromo !== "UNDEFINED"){
           
            $promo = codePromo::where('intitule', $request->input('codepromo'))->first();
            
            if($promo && $promo->nombreUtilisation > 0){
                $codePromo = $request->codepromo;
                $promo->nombreUtilisation = $promo->nombreUtilisation - 1;
                $promo->save();
             
                if(!$request->idProduit) {
                    $url = "https://mrapple-store.com/panier";
                    $token = $request->header('Authorization');
                    $paniers = Panier::where('token', $token)->get();
                    $prix = 0;
                    $qty = 0;
                    $idsDansLePanier = [];
                    
                    foreach ($paniers as $produit) {
                        $qty += $produit->qty;
                        $prix += $produit->prix * $qty;
                        $idsDansLePanier[] = ["id" => $produit->idProduit, "qty" => $produit->qty];
                    }
                    $produits = $idsDansLePanier;
        
                    $prix = $prix - $promo->valeur * $prix /100;
                        
                } else {
                    $url = "https://mrapple-store.com/payment";
                    $produit = Produit::findOrFail($request->idProduit);
                    $produits[] = ["id" => $produit->id, "qty" => $produit->quantite];
                    $prix = $produit->prix; 
                    $qty = 1;
                        
                    $prix = $prix - $promo->valeur * $prix /100;
                }
            } else {
                return response()->json(['erreur' => "Code promo erroné ou code épuisé"]);
            }
        } else {
            $codePromo = null;
            if(!$request->idProduit) {
                $url = "https://mrapple-store.com/panier";
                $token = $request->header('Authorization');
                $paniers = Panier::where('token', $token)->get();
                $prix = 0;
                $qty = 0;
                $idsDansLePanier = [];
                foreach ($paniers as $produit) {
                    $qty += $produit->qty;
                    $prix += $produit->prix * $produit->qty;
                    $idsDansLePanier[] = ["id" => $produit->idProduit, "qty" => $produit->qty];
                }
                $produits = $idsDansLePanier;
            }
        }
    
        $prefix = 'MRAPPLE_ORDER-';
        $randomNumber = mt_rand(1000, 9999); 
        $orderID = $prefix . $randomNumber;
        $produits_serialized = json_encode($produits);
    
        $vente = Commande::create([
            'order_id' => $orderID,
            'codePromo' => $codePromo ? $codePromo : null,
            'produit_id' => $produits_serialized,
            'prix_total' => $prix,
            'status' => "Unpaid",
            'quantite' => $qty,
            'user_id' => $user->id,
            'user_name' => $user->nom . ' ' . $user->prenoms,
            'date_created' => now()
        ]);
    
        // Remplacez VOTRE_CLE_API par votre véritable clé API
        \FedaPay\FedaPay::setApiKey("sk_sandbox_fKlwsz7knL1sZiXTjXLhTaOw");
        // \FedaPay\FedaPay::setApiKey("sk_sandbox_mGVNXupMPNzgS08eH8BGsJlo");
        // Précisez si vous souhaitez exécuter votre requête en mode test ou live
        \FedaPay\FedaPay::setEnvironment('sandbox'); //ou setEnvironment('live');
    
        // Créer la transaction
        // $transaction = \FedaPay\Transaction::create([
          
            $tab=explode(' ',$user->nom);
        $transaction = \FedaPay\Transaction::create(array(

            "description" => $user->nom . " " . $prix,
            "amount" => $prix,
            "currency" => ["iso" => "XOF"],
            "callback_url" => $url,
            "customer" => [
                "firstname" =>$tab[0],
                "lastname" =>$tab[1],
                "email" => $user->email,
                "phone_number" => [
                    "number" => $user->telephone,
                    "country" => "bj"
                ]
            ]
        ));

    
        $token = $transaction->generateToken(); 
    
        return response()->json(['url' => $token->url, 'commande' => $vente], 200);
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

    public function savePayment(Request $request) {
        $validator = Validator::make($request->all(), [
             "idTransaction" => 'required', 
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
    
            if ($transaction->status !== "approved") {
                return response(['error' => 'Transaction échouée'], 404);
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
                    return response(['success' => 'Achat effectué avec succès', 'id'=>$vente->id ], 200);
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

    // public function savePayment(Request $request) {
       
    //     $validator = Validator::make($request->all(), [
    //          "idTransaction" => 'required', 
    //          "order_id" => 'required'
    //        ]);
           
    //         if ($validator->fails()) {
    //           return response([
    //                  'errors' => $validator->errors(),
    //           ], 422); // Code de r&eacute;ponse HTTP 422 Unprocessable Entity
    //       }
          
          
    //         try {
    //     \FedaPay\FedaPay::setApiKey("sk_sandbox_fKlwsz7knL1sZiXTjXLhTaOw");
    //     \FedaPay\FedaPay::setEnvironment('sandbox');
    
    //     $transaction = \FedaPay\Transaction::retrieve($request->idTransaction);

    //     if ($transaction->status !== "approved") {
    //             return response(['error' => 'Transaction echouée'], 404);
    //     } 


    //     if(empty($request->produit_id)){
    //         $token = $request->header('Authorization');
    //         $paniers = Panier::where('token', $token)->get();
       
    //             if(!$paniers){
    //                 return response(['message' => 'Panier vide', 404]);
    //             }
        
    //         $idsDansLePanier = [];
    //         $nomProduits= "" ;
    //        foreach ($paniers  as $item) {
    //             $idsDansLePanier[] = $item->idProduit;
    //             $nomProduits .= $item->nomProduit.' \ ';
    //          } 
    //         $produits=$idsDansLePanier;
    //         $listeProduit = $nomProduits;

       
    //     }
    //     else{

    //         $produits =$request->produit_id;

    //         $listeProduit = Produit::find($request->produit_id)->nom;
    //     } 
         
    //     $vente = Commande::where('order_id', $request->order_id)->first();
        
         
    //     if($vente && $transaction->status == 'approved'){


    //             // $nbreProduitsManuel = automatisationController::getnbreProduitsManuel($idsDansLePanier);

    //             // if($nbreProduitsManuel == 0) {
    //             //         $contenuMail= "";
    //             //         $user = auth('sanctum')->user() ;
                        
    //             //         foreach($idsDansLePanier as $id) {
    //             //             $p = Produit::find($id);
 
    //             //             $latestAbonnement = abonnements::where('attribue', 'false')
    //             //             ->latest('created_at')
    //             //             ->where('produit_id', $p->id )
    //             //                 ->first();

    //             //                 $a = abonnements::where('attribue', 'false')
    //             //                 ->latest('created_at')
    //             //                 ->where('produit_id', $p->id )
    //             //                     ->count();

                                   
    //             //                 if($a == 3) {
    //             //                     Mail::to('hello@heimdall-store.com ')
    //             //                     ->send(new mailpresqueEpuise( $p->nom));
                                 
    //             //                 }
        
    //             //                 elseif($a == 1) {
    //             //                     Mail::to('hello@heimdall-store.com ')
    //             //                     ->send(new mailabonnementEpuise($p->nom));
                                   
    //             //                     $p->statut = "Indisponible";
    //             //                     $p->save();
         
    //             //                 }  
                                
    //             //                 $latestAbonnement->nomClient = $user->nom.' '.$user->prenoms;
    //             //                 $latestAbonnement->emailClient = $user->email;
    //             //                 $latestAbonnement->attribue = "true";
    //             //                 $latestAbonnement->dateAchat=now();
    //             //                 $latestAbonnement->save();
                                
    
                              

    //             //                 $contenuMail .= '<p style="text-align:center"><strong>'.$p->nom.'</strong></p>

    //             //                '.$latestAbonnement->details.'
                                
    //             //                 <p>&nbsp;</p>
                                
                               
    //             //                 ' ;

                              


    //             //         }

    //             //         $vente->box = $request->box;
    //             //         $vente->status = "Livree";
    //             //         $vente->save();
                      
    //             //         Mail::to($user->email)
    //             //         ->send(new orderDetailsMail( $user,$contenuMail , $vente ));
                   
                        
    //             // }
             
    //             // elseif ($nbreProduitsManuel > 0 ) {
                  
    //             //     $vente->box = $request->box;
    //             //     $vente->status = "En attente"; 
    //             //     $vente->save();
                   

    //             // }
           
    //        // Session::forget(app('currentUser')->nom); 
    //        $paniers = Panier::where('token', $token)->delete();
    //        Mail::to("hello@heimdall-store.com")->send(new OrderAchatMail( $vente,$listeProduit));
    //         if(Mail::to(app('currentUser')->email)->send(new OrderAchatMail( $vente,$listeProduit)))
    //         {
    //             return response(['success' => 'Achat effectue avec succes', 'id'=>$vente->id ], 200);
    //         } else {dd("error");}
                
    //     }
    //     else{
    //         return response(['error' => 'Commande non trouvé'], 404);
    //     }


    //     } catch (\FedaPay\Error\Base $e) {
    //         return response(['error' => 'Transaction erronée'], 500);

    //     }  
    // }

    // public function payments(Request $request ){
  
    //     if($request->codepromo !== "UNDEFINED"){
           
    //         $promo = codePromo::where('intitule', $request->input('codepromo'))->first();
            
    //             if($promo && $promo->nombreUtilisation > 0){
    //                 $codePromo =$request->codepromo ;
    //                 $promo->nombreUtilisation =  $promo->nombreUtilisation - 1;
    //                 $promo ->save();
                 
    //                 if(!$request->idProduit) {
    //                        $url="https://mrapple-store.com/panier";
    //         $token = $request->header('Authorization');
    //         $paniers = Panier::where('token', $token)->get();
    //         $prix = 0;
    //          $qty= 0;
    //          $idsDansLePanier = [];
            
    //        foreach ($paniers  as $produit) {
              
    //             $qty += $produit->qty;
                
    //              $prix += $produit->prix *  $qty;
    //             $idsDansLePanier[] =["id" =>$produit->idProduit, "qty" =>$produit->qty];
    //          }
    //           $produits=$idsDansLePanier;
           
    //           $prix = $prix - $promo->valeur * $prix /100;
                      
    //                 }
    //                 else {
    //                        $url="https://mrapple-store.com/payment";
    //         $produit=Produit::findorfail($request->idProduit);
    //         $produits[]=["id" =>$produit->id, "qty" =>$produit->quantite];
    //         $prix =$produit->prix; 
    //         $qty = 1;
                       
    //                     $prix = $prix - $promo->valeur * $prix /100;
    //                 }
    //             }
    //             else 
    //             {
    //                 return response()->json(['erreur' => "Code promo errone ou code épuisé "]);
    //             }
    //     } 
    //     else {
       
    //         $codePromo=null;
    //         if(!$request->idProduit ) {
    //             $url="https://mrapple-store.com/panier";
    //             $token = $request->header('Authorization');
    //             $paniers = Panier::where('token', $token)->get();
    //             $prix = 0;
    //             $qty= 0;
    //             $idsDansLePanier = [];
    //         foreach ($paniers  as $produit) {
    //                 $qty += $produit->qty;
    //                 $prix += $produit->prix *  $produit->qty;
    //                 $idsDansLePanier[] =["id" =>$produit->idProduit, "qty" =>$produit->qty];
    //             }
    //             $produits=$idsDansLePanier;
    
    //         }
    //         // else {
                
    //         //         $url="https://mrapple-store.com/payement";
    //         //     $produit=Produit::findorfail($request->idProduit);
    //         //     $produits[]=["id" =>$produit->id, "qty" =>$produit->quantite];
    //         //     $prix =$produit->prix; 
    //         //     $qty = 1;
    //         // }
 
    //     }
      
       
    //     $prefix = 'MRAPPLE_ORDER-';
    //     $randomNumber = mt_rand(1000, 9999); 
    //     $orderID = $prefix . $randomNumber;
    //    $produits_serialized = json_encode($produits);

      
    //     $vente=Commande::create([
    //         'order_id' => $orderID,
    //         'codePromo' => $codePromo ? $codePromo : null,
    //         'produit_id'=> $produits_serialized ,
    //         'prix_total' => $prix ,
    //         'status' => "Unpaid",
    //         'quantite' => $qty ,
    //         'user_id' => app('currentUser')->id,
    //         'user_name' => app('currentUser')->nom.' '.app('currentUser')->prenoms,
    //         'date_created'=> Carbon::now()
    //       ]);
         
    //     //  $_SESSION[app('currentUser')->nom]=$vente->order_id; 
          

 
    //      /* Rempacez VOTRE_CLE_API par votre véritable clé API */
    //     \FedaPay\FedaPay::setApiKey("sk_sandbox_fKlwsz7knL1sZiXTjXLhTaOw");
    //      // \FedaPay\FedaPay::setApiKey("sk_sandbox_mGVNXupMPNzgS08eH8BGsJlo");
    //    /* Précisez si vous souhaitez exécuter votre requête en mode test ou live */
    //        \FedaPay\FedaPay::setEnvironment('sandbox'); //ou setEnvironment('live');
 
    //        /* Créer la transaction */ 
    //       $transaction = \FedaPay\Transaction::create(array(
    //        "description" =>   app('currentUser')->nom." ".$prix,
    //        "amount" => $prix,
    //        "currency" => ["iso" => "XOF"],
    //        "callback_url" => $url,
    //        "customer" => [
    //            "firstname" =>app('currentUser')->nom,
    //         //    "lastname" => app('currentUser')->prenoms,
    //            "email" => app('currentUser')->email,
    //            "phone_number" => [
    //                "number" => app('currentUser')->telephone,
    //                "country" => "bj"
    //            ]
    //        ]
    //        ));
           
          
    //        $token = $transaction->generateToken(); 
           
    //        return response()->json(['url' => $token->url, 'commande'=> $vente], 200);
        
          
    // }

}
