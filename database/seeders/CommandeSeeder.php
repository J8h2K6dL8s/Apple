<?php

namespace Database\Seeders;

use App\Models\Commande;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CommandeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Commande::create([
            'user_id' => 1,
            'produit_id' => '[{"id":1,"qty":2},{"id":2,"qty":1}]',
            'order_id' => 'MRAPPLE_ORDER-1234',
            'date_created' => Carbon::now(),
            'status' => 'Unpaid',
            'box' => 'standard',
            'codePromo' => 'VOTRE_CODE_PROMO',
            'quantite' => 3,
            'prix_total' => 150.00,
            'user_name' => 'John Doe',
        ]);
    }
}
