<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Mail\ContactMail;
use Illuminate\Http\Request;
use App\Models\ResetCodePassword;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendCodeResetPasswordMail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;

class AuthentificationController extends Controller
{
    public function contact()
    {
        return view('emails/orderDetailsCommande');
    }

    public function index()
    {
        $admins = User::where('type', 'admin')->orderBy('id', 'desc')->get();

        return response(['admins' => $admins], 200);
    }

    public function delete(Request $request, $id)
    {
            $user = User::withTrashed()->find($id);

            if ($user) {
                $user->delete();
                return response()->json(['message' => 'Utilisateur supprimé avec succès'], 200);
            } else {
                return response()->json(['error' => 'Utilisateur non trouvé'], 404);
            }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'telephone' => 'required|integer',
            'type' => 'required|in:user,admin,superadmin',
            'email' => "required|string|email:rfc,dns|max:255|unique:" . User::class,
            'password' => 'required',
            'confirmPassword' => 'required'
        ]);

        if ($validator->fails()) {
            return response(["error" => $validator->errors()->all()], 200);
        } else {
            $user = User::create([
                'nom' => $request->nom,
                'telephone' => $request->telephone,
                'email' => $request->email,
                'type' => $request->type,
                'password' => Hash::make($request->password),
                'confirmPassword' => Hash::make($request->password),
            ]);

            if ($user->type == 'admin') {
                // Activer automatiquement l'email pour les administrateurs
                $user->email_verified_at = now();
                $user->save();
            } else {
                // Envoyer un email de vérification pour les autres types d'utilisateurs
                Notification::send($user, new VerifyEmail($user));
            }

            $token = $user->createToken('api_token')->plainTextToken;

            return response([
                'user' => $user,
                'token' => $token,
            ], 201);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email:rfc,dns|max:255',
            'password' => 'required',
            'type' => 'required|in:user,admin,superadmin',

        ]);

        if (Auth::attempt($credentials)) {
           
            $user = Auth::user();
            if($user->hasVerifiedEmail()){
            $token = $user->createToken('authToken')->plainTextToken;

            return response([
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
            }
            else {
                return response(['message' => ' Vérifier votre email pour activer votre compte'], 401);
            }
        } else {
            return response(['message' => 'Identifiants incorrects ! Veuillez réessayer'], 401);
        }
    }

    public function update(Request $request, $id)
    {   
        $user = User::find($id);

        if (!$user) {
            return response(['error' => 'Utilisateur introuvable'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            // 'prenom' => 'required|string',
            'telephone' => 'required|integer',  
            // 'email' =>"required|string|email:rfc,dns|max:255|unique:".User::class,
            // 'password' => 'required' 
            // 'confirpassword' => 'required' 
            
        
        ]);

        if ($validator->fails()) {
            return response(["error" => $validator->errors()], 200);
        } 
        else { 

            $user->update([
                'nom' => $request->nom,
                'telephone' => $request->telephone,
                // 'prenom' => $request->prenom,
                // 'type' => $request->type, 
                // 'email' => $request->email,
            ]);

            return response([
                'message' => 'Utilisateur mis à jour avec succès',
                'user' => $user,
            ], 200);
        }
    }
    
    public function logout(Request $request)
    { 
        $user = auth('sanctum')->user();
        if($user){
            $user->tokens()->delete();
            return [
                'Message' => 'Utilisateur déconnecté avec succès.'
                ];
        }
            else {
                return ['error'=>"Cet utilisateur n'existe pas"];
            }
    }

    public function currentUser(Request $request)
    {      
        $user = auth('sanctum')->user()  ;
            
            if($user){
                return response($user,200);

                } else {
                    return response(['error' => 'Aucun utilisateur trouver;',],200);

            }
    }

    public function modifyPassword(Request $request)
    {      
        $user = auth('sanctum')->user()  ;

                    $validator = Validator::make($request->all(), [
                        'current_password' => 'required',
                        'new_password' => 'required', ]);
                    
                if ($validator->fails()) {
                        return response(["error" =>  $validator->errors()], 200);  
                        }
                            else {
                        
                        // Vérifier si le mot de passe actuel est correct
                        if (!Hash::check($request->current_password, $user->password)) {
                            return response(["error" => "Le mot de passe actuel est incorrect."], 422); 
                        }

                        // Mettre à jour le mot de passe
                        $user->password = Hash::make($request->new_password);
                        $user->save();

                        return response(['success' => 'Mot de passe modifié avec succès.']);
                            
                        }
    } 

    public function verify($user_id, Request $request)
    {
        // Récupère l'utilisateur correspondant à l'identifiant $user_id

        $user = User::findOrFail($user_id);
        
        // Vérifie si l'e-mail de l'utilisateur a déjà été vérifié
        if (!$user->hasVerifiedEmail()) {

            // Si l'e-mail n'a pas été vérifié, marque l'e-mail comme vérifié
            $user->markEmailAsVerified();
            
        }

        // return response(['success' => 'Email vérifié avec succès.']);
           return redirect('http://localhost:5173/comptevalide'); 
    }

    public function resendEmailVerification() {
     
        $user = auth('sanctum')->user()  ;
        if ($user) {
            if ($user->hasVerifiedEmail()) {
                return response(["msg" => "Email already verified."], 400);
            } else {
               Notification::send($user, new VerifyEmail($user));
                return response(["msg" => "Un lien de v&eacute;rification a &eacute;t&eacute; envoy&eacute; &agrave; votre a²resse mail."]);
            }
        } else {
            return response(["msg" => "Utilisateur introuvable ! Veuillez v&eacute;rifier votre adresse mail"], 401);
        }
        

    }

    public function sendMailPasswordForgot(Request $request)
    { 
        
        $validator = Validator::make($request->all(), [
            
        'email' =>"required|string|email|max:255",  ]);
        
        if ($validator->fails()) {
            response(["msg" =>  $validator->errors()], 200);
        }
                else {
                            if(User::firstWhere('email', $request->email)){
                        // Delete all old code that user send before.
                        ResetCodePassword::where('email', $request->email)->delete();

                        // Generate random code
                        $code = mt_rand(100000, 999999);

                        // Create a new code
                        $codeData = ResetCodePassword::create([
                            'code'=>$code,
                            'email'=>$request->email,
                        ]);

                        // Send email to user
                        if(Mail::to($request->email)->send(new SendCodeResetPasswordMail($codeData->code,User::firstWhere('email', $request->email)->type))){
    
                            return response(['message' => trans('passwords.sent')], 200);
                        } else {dd("error");}
                    }
                    else {
                        return response(["msg" => "Utilisateur introuvable ! Veuillez vérifier votre adresse mail"], 404);
                    }
            }
    }

    public function passwordReset(Request $request)
    { 
        
        $validator = Validator::make($request->all(), [
            
        'code' => 'required|string|exists:reset_code_passwords',
        'password' => 'required|string|',
        ]);
        
        if ($validator->fails()) {
            return response([
                    'errors' => $validator->errors(),
            ], 422); // Code de r&eacute;ponse HTTP 422 Unprocessable Entity
        }
        else{
                // find the code
                $passwordReset = ResetCodePassword::firstWhere('code', $request->code);

                    if($passwordReset){
                // check if it does not expired: the time is one hour
                    if ($passwordReset->created_at > now()->addHour()) {
                        $passwordReset->delete();
                        return response(['message' => trans('passwords.code_is_expire')], 422);
                    }
                }
                else {
                    return response(['message' => trans('passwords.code_is_not_valid')], 422);
                }

                // find user's email 
                $user = User::firstWhere('email', $passwordReset->email);

                // update user password
                $user->update([
                    'password' => Hash::make($request->password)
                ]);

                // delete current code 
                $passwordReset->delete();

                return response(['message' =>'Le mot de passe a été réinitialisé avec succès'], 200);
            }
    }

    public function sendform(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required',
            'prenom' =>'required',
            'email' => 'required|email',
            'message' => 'required',
            'telephone'=>'required|'
           ]);
           
            if ($validator->fails()) {
              return response([
                     'errors' => $validator->errors(),
              ], 422); 
          }
          $data = $request->all();
          Mail::to('contact@mrapple-store.com')->send(new ContactMail($data));

          return response()->json(['message' => 'Message envoyé avec succès'], 200);
    }

}
