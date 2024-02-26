<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use App\Models\TradeImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\TradeMail;
use Illuminate\Support\Facades\Mail;

class TradeController extends Controller
{
    public function index()
    {
        $trades = Trade::orderByDesc('created_at')->get();
        
        $trades->load('images');

        return response()->json(['trades' => $trades], 200);
    }

    public function show($id)
    {
        $trade = Trade::findOrFail($id);
        $trade->load('images');
        return response()->json(['trade' => $trade], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string',
            'description' => 'required|string',
            'images' => 'nullable|array|min:1',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'email' => 'required|email',
            'telephone' => 'nullable|string',
        ]);

        // Création du trade
        $trade = Trade::create([
            'titre' => $request->titre,
            'description' => $request->description,
            'email' => $request->email,
            'telephone' => $request->telephone,
        ]);

        // Ajout des images au trade
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('public/trade_images/', $filename);
                $imageName = 'public/trade_images/' . $filename;

                // Enregistrement du chemin de l'image dans la table trade_images
                TradeImage::create([
                    'trade_id' => $trade->id,
                    'chemin_image' => $imageName,
                ]);
            }
        }

        // Chargement des images pour le trade actuel
        $trade->load('images');

        Mail::to('contact@mrapple-store.com')->send(new TradeMail($trade));

        return response()->json(['message' => 'Trade ajouté avec succès', 'trade' => $trade], 201);
    }

}
