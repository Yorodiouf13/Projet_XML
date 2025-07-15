<?php
session_start();
require_once 'inc/xml_utils.php';

$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST["nom"]);
    $email = trim($_POST["email"]);
    $motdepasse = $_POST["motdepasse"];
    $confirmer = $_POST["confirmer"];

    if ($nom && $email && $motdepasse && $confirmer) {
        if ($motdepasse === $confirmer) {
            if (strlen($motdepasse) >= 6) {
                // Gestion de l'avatar
                $cheminAvatar = '';
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $nomFichier = basename($_FILES['avatar']['name']);
                    $extension = strtolower(pathinfo($nomFichier, PATHINFO_EXTENSION));
                    $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'gif'];
                    
                    if (in_array($extension, $extensionsAutorisees)) {
                        $dossier = "assets/img/avatars/";
                        if (!is_dir($dossier)) mkdir($dossier, 0777, true);
                        
                        $nouveauNom = uniqid() . '.' . $extension;
                        $cheminCible = $dossier . $nouveauNom;
                        
                        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $cheminCible)) {
                            $cheminAvatar = $cheminCible;
                        }
                    }
                }

                if (ajouterUtilisateur($nom, $email, $motdepasse, $cheminAvatar)) {
                    $message = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
                    $messageType = 'success';
                } else {
                    $message = "Un utilisateur avec cet email existe déjà.";
                    $messageType = 'error';
                }
            } else {
                $message = "Le mot de passe doit contenir au moins 6 caractères.";
                $messageType = 'error';
            }
        } else {
            $message = "Les mots de passe ne correspondent pas.";
            $messageType = 'error';
        }
    } else {
        $message = "Tous les champs sont obligatoires.";
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Inscription - Plateforme Chat</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="styles.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
.avatar-preview {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 auto 16px;
    border: 3px solid var(--primary-color);
    display: none;
}
</style>
</head>
<body>
<div class="auth-container">
    <div class="auth-box">
        <div class="auth-logo">
            <i class="fas fa-user-plus"></i>
        </div>
        
        <h1 class="auth-title">Créer un compte</h1>
        <p class="auth-subtitle">Rejoignez notre plateforme de chat</p>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>">
                <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <img id="avatarPreview" class="avatar-preview" alt="Aperçu avatar">
                <label for="avatar">
                    <i class="fas fa-camera"></i>
                    Photo de profil (optionnel)
                </label>
                <input type="file" name="avatar" id="avatar" accept="image/*" onchange="previewAvatar(event)">
            </div>
            
            <div class="form-group">
                <label for="nom">
                    <i class="fas fa-user"></i>
                    Nom complet
                </label>
                <input type="text" name="nom" id="nom" required 
                       value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>"
                       placeholder="Votre nom complet">
            </div>
            
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i>
                    Adresse email
                </label>
                <input type="email" name="email" id="email" required autocomplete="username"
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                       placeholder="votre@email.com">
            </div>
            
            <div class="form-group">
                <label for="motdepasse">
                    <i class="fas fa-lock"></i>
                    Mot de passe
                </label>
                <input type="password" name="motdepasse" id="motdepasse" required autocomplete="new-password"
                       placeholder="Entrez votre mot de passe">
            </div>
            
            <div class="form-group">
                <label for="confirmer">
                    <i class="fas fa-lock"></i>
                    Confirmer le mot de passe
                </label>
                <input type="password" name="confirmer" id="confirmer" required autocomplete="new-password"
                       placeholder="Confirmez votre mot de passe">
            </div>
            
            <button type="submit" class="btn-primary">
                <i class="fas fa-user-plus"></i>
                Créer mon compte
            </button>
        </form>
        
        <a href="connexion.php" class="auth-link">
            <i class="fas fa-sign-in-alt"></i>
            Déjà un compte ? Se connecter
        </a>
        
        <a href="index.php" class="auth-link" style="margin-left: 20px;">
            <i class="fas fa-arrow-left"></i>
            Retour à l'accueil
        </a>
    </div>
</div>

<script>
function previewAvatar(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('avatarPreview');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
}
</script>
</body>
</html>
