<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Commande;
use App\Mail\OrderDetailsMail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ValidationCommandeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Générez des données de commande pour simuler le processus de validation
         $commande = Commande::where('status', 'Unpaid')->first();
         if ($commande) {
             $commande->status = 'Livree';
             $commande->details = 'Détails de la commande livrée';
             $commande->save();
 
             $user = User::where('id', $commande->user_id)->first();
             if ($user) {
                 Mail::to($user->email)->send(new OrderDetailsMail($user, $commande->details, $commande));
             }
         }
    }
}
