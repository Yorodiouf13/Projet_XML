<?php
session_start();
require_once 'inc/xml_utils.php';

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit;
}

$monId = $_SESSION['utilisateur_id'];
$monUtilisateur = trouverUtilisateurParId($monId);
$message = '';
$messageType = '';

// Traitement des formulaires (même logique que précédemment)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $nouveauNom = trim($_POST['nom'] ?? '');
        $nouveauEmail = trim($_POST['email'] ?? '');
        
        if ($nouveauNom && $nouveauEmail) {
            $emailExiste = trouverUtilisateurParEmail($nouveauEmail);
            if ($emailExiste && (string)$emailExiste['id'] !== $monId) {
                $message = "Cet email est déjà utilisé par un autre utilisateur.";
                $messageType = 'error';
            } else {
                $xml = chargerXML();
                foreach ($xml->utilisateurs->utilisateur as $u) {
                    if ((string)$u['id'] === $monId) {
                        $u->nom = htmlspecialchars($nouveauNom);
                        $u->email = htmlspecialchars($nouveauEmail);
                        sauvegarderXML($xml);
                        
                        $_SESSION['utilisateur_nom'] = $nouveauNom;
                        
                        $message = "Profil mis à jour avec succès !";
                        $messageType = 'success';
                        $monUtilisateur = trouverUtilisateurParId($monId);
                        break;
                    }
                }
            }
        } else {
            $message = "Tous les champs sont obligatoires.";
            $messageType = 'error';
        }
    }
    
    if (isset($_POST['update_avatar']) && isset($_FILES['avatar'])) {
        if ($_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $nomFichier = basename($_FILES['avatar']['name']);
            $extension = strtolower(pathinfo($nomFichier, PATHINFO_EXTENSION));
            $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($extension, $extensionsAutorisees)) {
                $dossier = "assets/img/avatars/";
                if (!is_dir($dossier)) mkdir($dossier, 0777, true);
                
                $nouveauNom = $monId . '_' . time() . '.' . $extension;
                $cheminCible = $dossier . $nouveauNom;
                
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $cheminCible)) {
                    if ($monUtilisateur->avatar && file_exists($monUtilisateur->avatar)) {
                        unlink($monUtilisateur->avatar);
                    }
                    
                    modifierAvatar($monId, $cheminCible);
                    $message = "Avatar mis à jour avec succès !";
                    $messageType = 'success';
                    $monUtilisateur = trouverUtilisateurParId($monId);
                } else {
                    $message = "Erreur lors de l'upload de l'avatar.";
                    $messageType = 'error';
                }
            } else {
                $message = "Format d'image non autorisé. Utilisez JPG, PNG ou GIF.";
                $messageType = 'error';
            }
        }
    }
    
    if (isset($_POST['change_password'])) {
        $ancienMdp = $_POST['ancien_mdp'] ?? '';
        $nouveauMdp = $_POST['nouveau_mdp'] ?? '';
        $confirmerMdp = $_POST['confirmer_mdp'] ?? '';
        
        if ($ancienMdp && $nouveauMdp && $confirmerMdp) {
            if (password_verify($ancienMdp, (string)$monUtilisateur->motdepasse)) {
                if ($nouveauMdp === $confirmerMdp) {
                    if (strlen($nouveauMdp) >= 6) {
                        $xml = chargerXML();
                        foreach ($xml->utilisateurs->utilisateur as $u) {
                            if ((string)$u['id'] === $monId) {
                                $u->motdepasse = password_hash($nouveauMdp, PASSWORD_DEFAULT);
                                sauvegarderXML($xml);
                                $message = "Mot de passe modifié avec succès !";
                                $messageType = 'success';
                                break;
                            }
                        }
                    } else {
                        $message = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
                        $messageType = 'error';
                    }
                } else {
                    $message = "Les nouveaux mots de passe ne correspondent pas.";
                    $messageType = 'error';
                }
            } else {
                $message = "Ancien mot de passe incorrect.";
                $messageType = 'error';
            }
        } else {
            $message = "Tous les champs sont obligatoires.";
            $messageType = 'error';
        }
    }
}

modifierStatut($monId, 'En ligne');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil - Plateforme Chat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="profile-container">
    <!-- Header de navigation -->
    <div class="profile-header">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1 style="color: var(--text-primary); margin: 0;">
                <i class="fas fa-user-circle"></i>
                Mon Profil
            </h1>
            <div style="display: flex; gap: 12px;">
                <a href="chat.php" class="action-btn secondary">
                    <i class="fas fa-comment"></i>
                    Chat
                </a>
                <a href="groupes.php" class="action-btn secondary">
                    <i class="fas fa-users"></i>
                    Groupes
                </a>
                <a href="logout.php" class="action-btn" style="background: var(--error-color); color: white;">
                    <i class="fas fa-sign-out-alt"></i>
                    Déconnexion
                </a>
            </div>
        </div>
        
        <img src="<?= htmlspecialchars($monUtilisateur->avatar && trim($monUtilisateur->avatar) ? $monUtilisateur->avatar : 'assets/img/default-avatar.png') ?>" 
             alt="Avatar" class="profile-avatar" id="currentAvatar">
        
        <h2 class="profile-name"><?= htmlspecialchars($monUtilisateur->nom) ?></h2>
        <p class="profile-email"><?= htmlspecialchars($monUtilisateur->email) ?></p>
        
        <?php
        $mesGroupes = getGroupesUtilisateur($monId);
        $autresUtilisateurs = getAutresUtilisateurs($monId);
        $totalMessages = 0;
        
        foreach ($autresUtilisateurs as $user) {
            $messages = getMessagesPrives($monId, (string)$user['id']);
            $totalMessages += count($messages);
        }
        
        foreach ($mesGroupes as $groupe) {
            $messagesGroupe = getMessagesGroupe((string)$groupe['id']);
            $totalMessages += count($messagesGroupe);
        }
        ?>
        
        <div class="welcome-stats" style="margin-top: 20px;">
            <div class="stat-item">
                <div class="stat-number"><?= count($autresUtilisateurs) ?></div>
                <div class="stat-label">Contacts</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?= count($mesGroupes) ?></div>
                <div class="stat-label">Groupes</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?= $totalMessages ?></div>
                <div class="stat-label">Messages</div>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>">
            <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Section Avatar -->
    <div class="profile-section">
        <h3 class="section-title">
            <i class="fas fa-camera"></i>
            Photo de profil
        </h3>
        <div style="text-align: center;">
            <img src="<?= htmlspecialchars($monUtilisateur->avatar && trim($monUtilisateur->avatar) ? $monUtilisateur->avatar : 'assets/img/default-avatar.png') ?>" 
                 alt="Avatar" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin-bottom: 16px; border: 3px solid var(--primary-color);" id="avatarPreview">
            <form method="post" enctype="multipart/form-data" style="display: inline-block;">
                <div class="form-group">
                    <input type="file" name="avatar" accept="image/*" onchange="previewAvatar(event)" required style="margin-bottom: 12px;">
                </div>
                <button type="submit" name="update_avatar" class="action-btn primary">
                    <i class="fas fa-upload"></i>
                    Changer l'avatar
                </button>
            </form>
        </div>
    </div>

    <!-- Section Informations personnelles -->
    <div class="profile-section">
        <h3 class="section-title">
            <i class="fas fa-user-edit"></i>
            Informations personnelles
        </h3>
        <form method="post">
            <div class="form-group">
                <label for="nom">
                    <i class="fas fa-user"></i>
                    Nom complet
                </label>
                <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($monUtilisateur->nom) ?>" required>
            </div>
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i>
                    Adresse email
                </label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($monUtilisateur->email) ?>" required>
            </div>
            <button type="submit" name="update_profile" class="action-btn primary">
                <i class="fas fa-save"></i>
                Mettre à jour le profil
            </button>
        </form>
    </div>

    <!-- Section Mot de passe -->
    <div class="profile-section">
        <h3 class="section-title">
            <i class="fas fa-lock"></i>
            Changer le mot de passe
        </h3>
        <form method="post">
            <div class="form-group">
                <label for="ancien_mdp">
                    <i class="fas fa-key"></i>
                    Ancien mot de passe
                </label>
                <input type="password" name="ancien_mdp" id="ancien_mdp" required>
            </div>
            <div class="form-group">
                <label for="nouveau_mdp">
                    <i class="fas fa-lock"></i>
                    Nouveau mot de passe
                </label>
                <input type="password" name="nouveau_mdp" id="nouveau_mdp" required>
            </div>
            <div class="form-group">
                <label for="confirmer_mdp">
                    <i class="fas fa-lock"></i>
                    Confirmer le nouveau mot de passe
                </label>
                <input type="password" name="confirmer_mdp" id="confirmer_mdp" required>
            </div>
            <button type="submit" name="change_password" class="action-btn primary">
                <i class="fas fa-key"></i>
                Changer le mot de passe
            </button>
        </form>
    </div>
</div>

<script>
function previewAvatar(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}
</script>
</body>
</html>
