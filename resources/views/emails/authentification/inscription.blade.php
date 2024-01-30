<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription sur Apple</title>
</head>
<body>
    <p>Bonjour {{ $user->prenom }},</p>
    
    <p>Merci de vous être inscrit sur Apple. Pour vérifier votre adresse e-mail, veuillez cliquer sur le lien ci-dessous :</p>
    
    <a href="{{ $url }}">Vérifier votre adresse e-mail</a>
    
    <p>Si vous n'avez pas tenté de vous inscrire sur Apple, veuillez ignorer ce message.</p>
    
    <p>Merci.</p>
</body>
</html>
