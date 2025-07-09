<?php
session_start();
require_once 'inc/xml_utils.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $motdepasse = $_POST['motdepasse'] ?? '';
    $user = trouverUtilisateurParEmail($email);
    if (!$user || !password_verify($motdepasse, $user->motdepasse)) {
        $message = "Identifiants incorrects.";
    } else {
        $_SESSION['utilisateur_id'] = (string)$user['id'];
        $_SESSION['utilisateur_nom'] = (string)$user->nom;
        header('Location: chat.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Connexion</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- Google Fonts pour un style plus doux -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<style>
body {
    background: linear-gradient(120deg,#d0e7ff 0%, #f5edff 100%);
    font-family: 'Inter', Arial, sans-serif;
    min-height:100vh;
    margin:0;
}
.centered-box {
    max-width: 380px;
    margin: 60px auto 0 auto;
    background: #fff;
    border-radius: 22px;
    box-shadow: 0 6px 32px 0 rgba(80, 130, 190, 0.12), 0 1.5px 0 #e7edfa;
    padding: 44px 38px 32px 38px;
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: box-shadow .18s;
    position: relative;
    min-height: 420px;
}
@media (max-width:500px) {
    .centered-box {max-width:95vw; padding:32px 7vw;}
}
h2 {
    color: #2056ae;
    margin-bottom: 16px;
    font-size: 2.1em;
    letter-spacing: -1px;
}
form { width: 100%; }
.input-group { margin-bottom: 22px; width: 100%; }
.input-group label {
    display: block; margin-bottom: 7px; font-weight: 600; color: #384872; letter-spacing: 0.01em;
}
.input-group input {
    width: 100%; padding: 13px 14px;
    border: none;
    border-radius: 10px;
    background: #f3f6fd;
    font-size: 1.09em;
    color: #232b43;
    transition: box-shadow .14s, border .14s;
    box-shadow: 0 2px 8px 0 #e5ecfb inset;
    outline: none;
    border: 1.5px solid transparent;
}
.input-group input:focus {
    border: 1.5px solid #8cb8f3;
    background: #fafdff;
    box-shadow: 0 2px 8px 0 #c4dcfc inset;
}
button[type="submit"] {
    width: 100%;
    padding: 13px 0;
    background: linear-gradient(90deg,#4187f7 10%, #7e51f8 95%);
    color: #fff;
    font-size: 1.15em;
    font-weight: bold;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    margin-top: 3px;
    box-shadow: 0 2px 8px #a4bff1;
    letter-spacing: 0.02em;
    transition: background .17s, box-shadow .13s;
}
button[type="submit"]:hover {
    background: linear-gradient(90deg,#316be4 10%, #713fe0 95%);
    box-shadow: 0 2px 16px #7e9ffb;
}
.errormsg {
    color: #f25353;
    background: #fff1f2;
    border: 1px solid #f9c2cd;
    border-radius: 8px;
    padding: 10px 0;
    margin-bottom: 15px;
    font-size:1.07em;
    width:100%;
    text-align:center;
    box-sizing: border-box;
}
.logo {
    margin-bottom: 13px;
    width:60px; height:60px;
    border-radius: 50%;
    box-shadow: 0 0 0 4px #d8e9fc, 0 1px 6px #aaa2;
    object-fit: cover;
}
.tiny-link {
    margin-top: 24px;
    color: #4187f7;
    font-size: 1em;
    text-decoration: none;
    font-weight: 500;
    letter-spacing: 0.01em;
    transition: color .15s;
}
.tiny-link:hover { color: #713fe0; text-decoration: underline;}
::selection { background: #cbe2ff; }
</style>
</head>
<body>
<div class="centered-box">
    <h2>Connexion</h2>
    <?php if ($message): ?><div class="errormsg"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <form method="post">
        <div class="input-group">
            <label for="email">Adresse email</label>
            <input type="email" name="email" id="email" required autocomplete="username">
        </div>
        <div class="input-group">
            <label for="motdepasse">Mot de passe</label>
            <input type="password" name="motdepasse" id="motdepasse" required autocomplete="current-password">
        </div>
        <button type="submit">Connexion</button>
    </form>
    <a class="tiny-link" href="inscription.php">Créer un compte</a>
</div>
</body>
</html>
