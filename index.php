<?php
session_start();
if (isset($_SESSION['utilisateur_id'])) {
    header('Location: chat.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bienvenue sur Plateforme XML Chat</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { background: #f4f6fa; font-family: Arial, sans-serif; }
        .container { margin: 80px auto; max-width: 400px; background: #fff; border-radius: 10px; box-shadow: 0 4px 14px #0001; padding: 35px 30px; text-align: center; }
        h1 { margin-bottom: 12px; font-size: 2em; color: #0069c2;}
        p { color: #555; margin-bottom: 28px;}
        .btn { background: #0069c2; color: #fff; border: none; border-radius: 5px; font-size: 1.1em; padding: 11px 28px; text-decoration: none; margin: 0 12px;}
        .btn:hover { background: #004c91; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bienvenue 👋</h1>
        <p>Bienvenue sur la plateforme de discussions en ligne.<br>Veuillez vous connecter ou créer un compte pour accéder au chat.</p>
        <a class="btn" href="connexion.php">Connexion</a>
        <a class="btn" href="inscription.php">Inscription</a>
    </div>
</body>
</html>
