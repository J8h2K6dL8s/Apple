<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FavorisController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\CodePromoController;
use App\Http\Controllers\Auth\AuthentificationController;
use App\Http\Controllers\ChiffreAffaireController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\PanierController;

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

                                        //USER

Route::post('/register', [AuthentificationController::class, 'register']); 

Route::post('/login', [AuthentificationController::class, 'login']); 

Route::post('/check-email', [AuthentificationController::class, 'checkEmail']); 

Route::post('/sendMailPasswordForgot', [AuthentificationController::class, 'sendMailPasswordForgot']);

Route::get('/verify-email/{id}', [AuthentificationController::class, 'verify'])->name('verification.verify');

Route::post('/email/verification-notification', [AuthentificationController::class, 'resendEmailVerification'])->name('verification.send');

Route::post('/update-password', [AuthentificationController::class, 'passwordReset']);

Route::post('/contact', [AuthentificationController::class, 'sendform'] );


Route::middleware(['auth'])->group(function () { 
    
    Route::get('/current-user', [AuthentificationController::class, 'currentUser']);          

    Route::post('/update-user/{id}', [AuthentificationController::class, 'update']);

    Route::post('/modify-password', [AuthentificationController::class, 'modifyPassword']);

    Route::get('/logout', [AuthentificationController::class, 'logout']);

});

Route::group(['middleware' => ['superadmin']], function () {

    Route::post('/ajouter-admin', [AuthentificationController::class, 'register']); 

 });

 
Route::group(['middleware' => ['admin']], function () {

                                        //CATEGORIES

    Route::post('/ajouter-categorie', [CategorieController::class, 'store']); 

    Route::post('/modifier-categorie/{id}', [CategorieController::class, 'update']);

    Route::get('/voir-categories/{id}', [CategorieController::class, 'show']);

    Route::get('/supprimer-categorie/{id}', [CategorieController::class, 'delete']);

    Route::get('/liste-categories', [CategorieController::class, 'index']);

    Route::get('/{idCategorie}/liste_produits_par_categorie', [CategorieController::class, 'liste_produits_par_categorie']);

    Route::get('/total-categorie', [CategorieController::class, 'nbrTotalCatgories']);


                                            //PRODUITS

    Route::post('/ajouter-produit', [ProduitController::class, 'store']); 

    Route::post('/modifier-produit/{produit}', [ProduitController::class, 'update']); 

    Route::get  ('/supprimer-produit/{id}', [ProduitController::class, 'delete']); 

    Route::get('/voir-produits/{produit}', [ProduitController::class, 'show']); 

    Route::get('/liste-produits', [ProduitController::class, 'index']); 
    
    Route::get('/total-produit', [ProduitController::class, 'nbrTotalProduits']);

    Route::post('/rechercher-produit', [ProduitController::class, 'rechercher_produit_par_nom']);

                                            //CODES PROMOS
                                            
    Route::post('/ajouter-codePromo', [CodePromoController::class, 'store']);

    Route::get('/modifier-codePromo/{id}', [CodePromoController::class, 'update']);

    Route::get('/supprimer-codePromo/{id}', [CodePromoController::class, 'delete']);

    Route::get('/voir-codePromo/{id}', [CodePromoController::class, 'show']);
    
    Route::post('/verifier-codePromo', [CodePromoController::class, 'checkValidity']);  

    Route::get('/liste-codePromos', [CodePromoController::class, 'index']);

});

                                        //FAVORIS

Route::get('/ajouter-favoris/{id}', [FavorisController::class, 'ajouterAuxFavoris']);

Route::get('/supprimer-favoris/{id}', [FavorisController::class, 'supprimerUnFavoris']);
                
Route::get('/mes-favoris', [FavorisController::class, 'mesFavoris']);


                                    //CHIFFRES DAFFAIRES

Route::get('/chiffre-affaires', [ChiffreAffaireController::class, 'calculerChiffreAffaires']);

Route::get('/chiffre-affaires/mois-en-cours', [ChiffreAffaireController::class, 'calculerChiffreAffairesMoisEnCours']);
                 

                                    //COMMANDES

Route::get('/ajouter-panier/{id}', [PanierController::class, 'addCart']);

Route::get('/supprimer-panier/{rowId}', [PanierController::class, 'removeCart']);

Route::get('/contenu-panier', [PanierController::class, 'recupererContenuPanier']);

