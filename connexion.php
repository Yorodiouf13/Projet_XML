<?php
session_start();
require_once 'inc/xml_utils.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $motdepasse = $_POST["motdepasse"];

    if ($email && $motdepasse) {
        $utilisateur = verifierMotDePasse($email, $motdepasse);
        
        if ($utilisateur) {
            // Connexion réussie
            $_SESSION["utilisateur_id"] = (string)$utilisateur["id"];
            $_SESSION["utilisateur_nom"] = (string)$utilisateur->nom;
            $_SESSION["utilisateur_email"] = (string)$utilisateur->email;
            
            // Mettre le statut en ligne
            modifierStatut((string)$utilisateur["id"], 'En ligne');
            
            header("Location: chat.php");
            exit;
        } else {
            $message = "Identifiants incorrects.";
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Connexion - Plateforme Chat</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="styles.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="auth-container">
    <div class="auth-box">
        <div class="auth-logo">
            <i class="fas fa-comments"></i>
        </div>
        
        <h1 class="auth-title">Connexion</h1>
        <p class="auth-subtitle">Accédez à votre plateforme de chat</p>
        
        <?php if ($message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <form method="post">
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
                <input type="password" name="motdepasse" id="motdepasse" required autocomplete="current-password"
                       placeholder="Votre mot de passe">
            </div>
            
            <button type="submit" class="btn-primary">
                <i class="fas fa-sign-in-alt"></i>
                Se connecter
            </button>
        </form>
        
        <a href="inscription.php" class="auth-link">
            <i class="fas fa-user-plus"></i>
            Pas encore de compte ? S'inscrire
        </a>
        
        <a href="index.php" class="auth-link" style="margin-left: 20px;">
            <i class="fas fa-arrow-left"></i>
            Retour à l'accueil
        </a>
    </div>
</div>
</body>
</html>
