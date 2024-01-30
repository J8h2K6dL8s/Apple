<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau Message sur Apple</title>
</head>
<body>
    <p>Nouveau message reçu depuis le formulaire de contact sur Apple:</p>

    <p><strong>Nom:</strong> {{ $data['nom'] }}</p>
    <p><strong>Prénom:</strong> {{ $data['prenom'] }}</p>
    <p><strong>Email:</strong> {{ $data['email'] }}</p>
    <p><strong>Numéro de téléphone:</strong> {{ $data['telephone'] }}</p>

    <p><strong>Message:</strong></p>
    <p>{{ $data['message'] }}</p>

    <p>Merci.</p>
</body>
</html>
