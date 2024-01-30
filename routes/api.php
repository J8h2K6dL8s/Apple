<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FavorisController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\CodePromoController;
use App\Http\Controllers\Auth\AuthentificationController;
use App\Http\Controllers\CommandeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

                        //USERS

Route::post('/register', [AuthentificationController::class, 'register']); 

Route::post('/login', [AuthentificationController::class, 'login']); 

Route::get('/verify-email/{id}/', [AuthentificationController::class, 'verify'])->name('verification.verify');

Route::post('/email/verification-notification', [AuthentificationController::class, 'resendEmailVerification'])->name('verification.send');

Route::post('/sendMailPasswordForgot', [AuthentificationController::class, 'sendMailPasswordForgot']);

Route::post('/update-user/{id}', [AuthentificationController::class, 'update']);

Route::post('/update-password', [AuthentificationController::class, 'passwordReset']);

Route::post('/contact', [AuthentificationController::class, 'sendform'] );




Route::group(['middleware' => 'auth'], function () {

    Route::get('/current-user', [AuthentificationController::class, 'currentUser']);

    Route::post('/modify-password', [AuthentificationController::class, 'modifyPassword']);

    Route::get('/logout', [AuthentificationController::class, 'logout']);

});

Route::group(['middleware' => 'superadmin'], function () {

    Route::post('/ajouter-admin', [AuthentificationController::class, 'register']); 


});

                                //CATEGORIES

Route::post('/ajouter-categorie', [CategorieController::class, 'store']); 

Route::post('/modifier-categorie/{id}', [CategorieController::class, 'update']);

Route::get('/voir-categories/{id}', [CategorieController::class, 'show']);

Route::get('/supprimer-categorie/{id}', [CategorieController::class, 'delete']);

Route::get('/total-categorie', [CategorieController::class, 'nbrTotalCatgories']);

Route::get('/{idCategorie}/liste_produits_par_categorie', [CategorieController::class, 'liste_produits_par_categorie']);



                            //PRODUITS

Route::get('/liste-produits', [ProduitController::class, 'index']);

Route::post('/ajouter-produit', [ProduitController::class, 'store']);

Route::post('/modifier-produit/{produit}', [ProduitController::class, 'update']);

Route::get('/voir-produits/{produit}', [ProduitController::class, 'show']);

Route::get('/supprimer-produit/{id}', [ProduitController::class, 'delete']);

Route::get('/total-produit', [ProduitController::class, 'nbrTotalProduits']);

Route::post('/rechercher-produit', [ProduitController::class, 'rechercher_produit_par_nom']);


                            //FAVORIS

Route::get('/ajouter-favoris/{id}', [FavorisController::class, 'ajouterAuxFavoris']);

Route::get('/supprimer-favoris/{id}', [FavorisController::class, 'delete']);

Route::get('/mes-favoris', [FavorisController::class, 'mesFavoris']);


                          //CODES PROMOS

Route::post('/ajouter-codePromo', [CodePromoController::class, 'store']);

Route::post('/modifier-codePromo/{id}', [CodePromoController::class, 'update']);

Route::get('/supprimer-codePromo/{id}', [CodePromoController::class, 'delete']);
    
Route::get('/voir-codePromo/{id}', [CodePromoController::class, 'show']);
 
Route::post('/verifier-codePromo', [CodePromoController::class, 'checkValidity']);  
   
Route::get('/liste-codePromos', [CodePromoController::class, 'index']);


                            //COMMANDES

Route::get('/ajouter-panier/{id}', [CommandeController::class, 'addCart']);

Route::get('/supprimer-panier/{rowId}', [CommandeController::class, 'removeCart']);

Route::get('/contenu-panier', [CommandeController::class, 'recupererContenuPanier']);

Route::get('/liste-commandes', [CommandeController::class, 'index']);

Route::get('/mes-commandes', [CommandeController::class, 'mesCommandes']);

Route::get('/voir-commande/{id}', [CommandeController::class, 'show']);

Route::get('/commandes-en-attente', [CommandeController::class, 'nombreCommandesEnAttente']);

Route::get('/total-commandes', [CommandeController::class, 'nbrTotalCommandes']);

Route::post('/valider-commandes', [CommandeController::class, 'validercommande']);

Route::post('/modifier-quantite-panier/{id}', [CommandeController::class, 'modifierQuantite']);

Route::get('/payer', [CommandeController::class, 'payer']);



                        //CHIFFRES DAFFAIRES
// Route::get('/chiffre-affaires', [chiffreAffaireController::class, 'calculerChiffreAffaires']);

// Route::get('/chiffre-affaires/mois-en-cours', [chiffreAffaireController::class, 'calculerChiffreAffairesMoisEnCours']);








 
