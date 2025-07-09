<?php
session_start();
require_once 'inc/xml_utils.php';

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit;
}
$monId = $_SESSION['utilisateur_id'];
$user = trouverUtilisateurParId($monId);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $avatar = $user->avatar ?? '';
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif'])) {
            $avatar_dir = "assets/avatars/";
            if (!is_dir($avatar_dir)) mkdir($avatar_dir, 0777, true);
            $avatar = $avatar_dir . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar);
        }
    }
    modifierUtilisateur($monId, $nom, $email, $avatar);
    $message = "Profil mis à jour !";
    $user = trouverUtilisateurParId($monId); // Recharger
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Mon profil</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="styles.css">
<style>
body { background: #f4f6fa; font-family: 'Segoe UI', Arial, sans-serif;}
.form-box {
    background: #fff; max-width:400px; margin: 60px auto;
    border-radius: 16px; box-shadow: 0 6px 32px #0001;
    padding: 36px 32px 32px 32px; text-align: center;
}
.form-box h2 { margin-bottom: 15px; color: #0069c2;}
.form-box form { margin-top: 14px;}
.input-group { margin-bottom: 16px; text-align: left;}
.input-group label { display:block; margin-bottom:4px; font-weight:bold; color: #333;}
.input-group input {
    width:100%; padding:10px 12px; border:1.5px solid #b5c4d3;
    border-radius:7px; font-size:1.06em; background: #f8fbff;
}
input[type="file"] { border: none; background: transparent; margin-top: 6px;}
input[type="submit"] {
    background: #0069c2; color:#fff; border: none; font-size: 1.07em;
    border-radius:7px; padding:10px 24px; cursor:pointer;
    transition: background .16s;
}
input[type="submit"]:hover { background: #004c91; }
.succesmsg { color:#197d2b; font-size:1.07em; margin-bottom: 14px;}
.avatar-preview {margin-bottom: 17px; border-radius: 50%; width: 76px; height: 76px; object-fit: cover; border:2px solid #e2e2e2;}
a.tiny-link { color:#0069c2; font-size:1em; text-decoration: none; }
a.tiny-link:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="form-box">
    <h2>Mon profil</h2>
    <?php if ($message): ?><div class="succesmsg"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <img class="avatar-preview" src="<?= htmlspecialchars($user->avatar && trim($user->avatar) ? $user->avatar : 'assets/img/default-avatar.png') ?>" alt="avatar">
    <form method="post" enctype="multipart/form-data">
        <div class="input-group">
            <label for="nom">Nom complet</label>
            <input type="text" name="nom" id="nom" required value="<?= htmlspecialchars($user->nom) ?>">
        </div>
        <div class="input-group">
            <label for="email">Adresse email</label>
            <input type="email" name="email" id="email" required value="<?= htmlspecialchars($user->email) ?>">
        </div>
        <div class="input-group">
            <label for="avatar">Changer de photo</label>
            <input type="file" name="avatar" id="avatar" accept="image/*">
        </div>
        <input type="submit" value="Mettre à jour">
    </form>
    <a class="tiny-link" href="chat.php">&larr; Retour à la messagerie</a>
</div>
</body>
</html>
