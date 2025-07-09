<?php
session_start();
require_once 'inc/xml_utils.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $motdepasse = $_POST['motdepasse'] ?? '';
    $confirm = $_POST['confirmer'] ?? '';
    if (!$nom || !$email || !$motdepasse) {
        $message = "Tous les champs sont obligatoires.";
    } elseif ($motdepasse !== $confirm) {
        $message = "Les mots de passe ne correspondent pas.";
    } elseif (trouverUtilisateurParEmail($email)) {
        $message = "Cet email est déjà inscrit.";
    } else {
        $avatar = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif'])) {
                $avatar_dir = "assets/avatars/";
                if (!is_dir($avatar_dir)) mkdir($avatar_dir, 0777, true);
                $avatar = $avatar_dir . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar);
            }
        }
        ajouterUtilisateur($nom, $email, password_hash($motdepasse, PASSWORD_DEFAULT), $avatar);
        header('Location: connexion.php?register=ok');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Inscription</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<style>
body {
    background: linear-gradient(120deg,#d0e7ff 0%, #f5edff 100%);
    font-family: 'Inter', Arial, sans-serif;
    min-height:100vh;
    margin:0;
}
.centered-box {
    max-width: 400px;
    margin: 52px auto 0 auto;
    background: #fff;
    border-radius: 22px;
    box-shadow: 0 6px 32px 0 rgba(80, 130, 190, 0.12), 0 1.5px 0 #e7edfa;
    padding: 44px 38px 32px 38px;
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: box-shadow .18s;
    position: relative;
}
@media (max-width:500px) {
    .centered-box {max-width:98vw; padding:30px 6vw;}
}
h2 {
    color: #2056ae;
    margin-bottom: 14px;
    font-size: 2.1em;
    letter-spacing: -1px;
}
form { width: 100%; }
.input-group { margin-bottom: 20px; width: 100%; }
.input-group label {
    display: block; margin-bottom: 7px; font-weight: 600; color: #384872; letter-spacing: 0.01em;
}
.input-group input[type="text"], .input-group input[type="email"], .input-group input[type="password"] {
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
.input-group input[type="file"] {
    background: none;
    border: none;
    box-shadow: none;
    margin-top: 7px;
    font-size:1.02em;
}
.avatar-preview {
    display: block;
    width: 62px;
    height: 62px;
    object-fit: cover;
    border-radius: 50%;
    border:2px solid #e2e2e2;
    margin: 0 auto 18px auto;
    box-shadow: 0 0 0 4px #e7edfa, 0 1px 5px #9991;
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
    margin-top: 4px;
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
    width:62px; height:62px;
    border-radius: 50%;
    box-shadow: 0 0 0 4px #d8e9fc, 0 1px 6px #aaa2;
    object-fit: cover;
}
.tiny-link {
    margin-top: 22px;
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
    <h2>Créer un compte</h2>
    <?php if ($message): ?><div class="errormsg"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data" id="insc-form">
        <div class="input-group">
            <label for="nom">Nom complet</label>
            <input type="text" name="nom" id="nom" required>
        </div>
        <div class="input-group">
            <label for="email">Adresse email</label>
            <input type="email" name="email" id="email" required autocomplete="username">
        </div>
        <div class="input-group">
            <label for="motdepasse">Mot de passe</label>
            <input type="password" name="motdepasse" id="motdepasse" required autocomplete="new-password">
        </div>
        <div class="input-group">
            <label for="confirmer">Confirmer le mot de passe</label>
            <input type="password" name="confirmer" id="confirmer" required autocomplete="new-password">
        </div>
        <div class="input-group">
            <label for="avatar">Photo de profil (optionnel)</label>
            <input type="file" name="avatar" id="avatar" accept="image/*" onchange="previewAvatar(event)">
        </div>
        <img id="preview" class="avatar-preview" src="assets/img/default-avatar.png" alt="avatar preview" style="display:none;">
        <button type="submit">S'inscrire</button>
    </form>
    <a class="tiny-link" href="connexion.php">Déjà un compte ? Connexion</a>
</div>
<script>
// Affichage de la preview de l’avatar
function previewAvatar(e){
    let img = document.getElementById('preview');
    let file = e.target.files[0];
    if(file){
        let url = URL.createObjectURL(file);
        img.src = url; img.style.display="block";
    }else{
        img.src = ""; img.style.display="none";
    }
}
</script>
</body>
</html>
