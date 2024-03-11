<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre achat | Mr Apple</title>
</head>
<body>
    <h1>Votre achat</h1>
    {{-- <p>Bonjour {{ $user->nom }},</p> --}}
    
    <p>Merci d'avoir effectué un achat sur Mr Apple.</p>
    
    <p>Détails de votre commande :</p>
    <ul>
        <li>Numéro de commande : {{ $vente->order_id }}</li>
        <li>Produits achetés : {{ $listeProduit }}</li>
        <li>Prix total : {{ $vente->prix_total }} XOF</li>
        <li>Statut de la commande : {{ $vente->status }}</li>
    </ul>
    
    <p>Nous vous remercions pour votre confiance et restons à votre disposition pour toute question.</p>
    
    <p>Cordialement,<br>
    L'équipe Mr Apple</p>
</body>
</html>
