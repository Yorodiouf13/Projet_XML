<?php
ob_start();
session_start();
require_once 'inc/xml_utils.php';

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit;
}
$monId = $_SESSION['utilisateur_id'];
$monNom = $_SESSION['utilisateur_nom'];
$autresUtilisateurs = getAutresUtilisateurs($monId);

if (!isset($_SESSION['groupes_lus'])) {
    $_SESSION['groupes_lus'] = [];
}

$mesGroupes = getGroupesUtilisateur($monId);
$groupeSelectionneId = isset($_GET['id']) ? $_GET['id'] : null;

// TRAITEMENT CREATION DE GROUPE
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom_groupe'])) {
    $nomGroupe = trim($_POST['nom_groupe'] ?? '');
    $membres = $_POST['membres'] ?? [];
    $membres[] = $monId;
    $membres = array_unique($membres);

    if (!$nomGroupe) {
        $message = "Le nom du groupe est obligatoire.";
    } elseif (count($membres) < 2) {
        $message = "Il faut au moins deux membres.";
    } else {
        creerGroupe($nomGroupe, $membres);
        header('Location: groupes.php');
        exit;
    }
}

modifierStatut($monId, 'En ligne');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Groupes - Plateforme Chat</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="styles.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
/* Modal styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 16px;
    padding: 32px;
    max-width: 500px;
    width: 90vw;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: var(--shadow-lg);
    position: relative;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.modal-title {
    font-size: 20px;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

.modal-close {
    width: 32px;
    height: 32px;
    border: none;
    background: none;
    color: var(--text-secondary);
    cursor: pointer;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: background-color 0.2s;
}

.modal-close:hover {
    background: var(--hover-color);
}

.member-select {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 8px;
}

.member-option {
    display: flex;
    align-items: center;
    padding: 8px;
    cursor: pointer;
    border-radius: 6px;
    transition: background-color 0.2s;
}

.member-option:hover {
    background: var(--hover-color);
}

.member-option input {
    margin-right: 12px;
}

.member-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 8px;
}
</style>
</head>
<body>
<div class="main-layout">
    <!-- Sidebar groupes -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="user-info">
                <?php 
                $monUtilisateur = trouverUtilisateurParId($monId);
                $avatarSrc = ($monUtilisateur && $monUtilisateur->avatar && trim($monUtilisateur->avatar)) 
                    ? $monUtilisateur->avatar 
                    : 'assets/img/default-avatar.png';
                ?>
                <img src="<?= htmlspecialchars($avatarSrc) ?>" alt="Mon avatar" class="user-avatar" onclick="window.location.href='profil.php'">
                <span class="user-name"><?= htmlspecialchars($monNom) ?></span>
            </div>
            <div class="header-actions">
                <button class="header-btn" onclick="openModal()" title="Nouveau groupe">
                    <i class="fas fa-plus"></i>
                </button>
                <button class="header-btn" onclick="window.location.href='logout.php'" title="Déconnexion">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </div>
        
        <div class="nav-tabs">
            <a href="chat.php" class="nav-tab">
                <i class="fas fa-comment"></i> Discussions
            </a>
            <a href="groupes.php" class="nav-tab active">
                <i class="fas fa-users"></i> Groupes
            </a>
        </div>
        
        <div class="contact-list">
        <?php if ($mesGroupes): ?>
            <?php foreach ($mesGroupes as $groupe): 
                $messages = getMessagesGroupe((string)$groupe['id']);
                $lastRead = $_SESSION['groupes_lus'][(string)$groupe['id']] ?? 0;
                $nouveau = false;
                $dernierMsg = "";
                $dernierAuteurMoi = false;
                $dernierTime = "";
                
                if ($messages) {
                    $dernier = end($messages);
                    $dernierAuteurMoi = ($dernier['auteur'] == $monId);
                    $dernierMsg = htmlspecialchars(mb_strimwidth((string)$dernier,0,35,'...'));
                    $dernierTime = date('H:i', strtotime($dernier['date']));
                    foreach ($messages as $msg) {
                        if ($msg['auteur'] != $monId && $msg['id'] > $lastRead) {
                            $nouveau = true; break;
                        }
                    }
                }
                $selected = ($groupeSelectionneId == (string)$groupe['id']);
            ?>
            <a href="groupes.php?id=<?= (string)$groupe['id'] ?>" class="contact<?= $selected ? " selected" : "" ?>">
                <div class="contact-avatar" style="background: var(--primary-color); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="contact-info">
                    <div class="contact-name">
                        <?= htmlspecialchars($groupe->nom) ?>
                        <span style="font-size: 12px; color: var(--text-muted);">(<?= count($groupe->membres->membre) ?>)</span>
                    </div>
                    <?php if ($dernierMsg): ?>
                        <div class="contact-preview">
                            <?= $dernierAuteurMoi ? "Vous: " : "" ?><?= $dernierMsg ?>
                        </div>
                    <?php else: ?>
                        <div class="contact-preview text-muted">Aucun message</div>
                    <?php endif; ?>
                </div>
                <div class="contact-meta">
                    <?php if ($dernierTime): ?>
                        <div class="contact-time"><?= $dernierTime ?></div>
                    <?php endif; ?>
                    <?php if ($nouveau): ?>
                        <div class="unread-badge">Nouveau</div>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="padding: 40px 20px; text-align: center; color: var(--text-muted);">
                <i class="fas fa-users" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                <p>Aucun groupe</p>
                <p style="font-size: 12px; margin-top: 8px;">Créez votre premier groupe !</p>
            </div>
        <?php endif; ?>
        </div>
    </aside>

    <!-- Centre : discussion groupe -->
    <section class="center-panel">
    <?php
    $groupeCible = null;
    if ($groupeSelectionneId) {
        foreach ($mesGroupes as $g) {
            if ((string)$g['id'] === $groupeSelectionneId) { $groupeCible = $g; break; }
        }
    }
    if ($groupeCible):
        include 'discussion_groupes_zone.php';
    else: ?>
        <div class="welcome-screen">
            <div class="welcome-icon">
                <i class="fas fa-users"></i>
            </div>
            <h2 class="welcome-title">Vos groupes de discussion</h2>
            <p class="welcome-subtitle">
                Sélectionnez un groupe pour commencer la discussion ou créez un nouveau groupe pour inviter vos contacts.
            </p>
            
            <div class="welcome-stats">
                <div class="stat-item">
                    <div class="stat-number"><?= count($mesGroupes) ?></div>
                    <div class="stat-label">Groupes rejoints</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= count($autresUtilisateurs) ?></div>
                    <div class="stat-label">Contacts disponibles</div>
                </div>
            </div>
            
            <div class="welcome-actions">
                <button onclick="openModal()" class="action-btn primary">
                    <i class="fas fa-plus"></i>
                    Créer un groupe
                </button>
                <a href="chat.php" class="action-btn secondary">
                    <i class="fas fa-comment"></i>
                    Messages privés
                </a>
            </div>
        </div>
    <?php endif; ?>
    </section>
</div>

<!-- Modal de création de groupe -->
<div id="groupModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-users"></i>
                Créer un nouveau groupe
            </h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form method="post">
            <div class="form-group">
                <label for="nom_groupe">
                    <i class="fas fa-tag"></i>
                    Nom du groupe
                </label>
                <input type="text" name="nom_groupe" id="nom_groupe" required 
                       placeholder="Entrez le nom du groupe">
            </div>
            
            <div class="form-group">
                <label>
                    <i class="fas fa-user-friends"></i>
                    Sélectionner les membres
                </label>
                <div class="member-select">
                    <?php foreach ($autresUtilisateurs as $user): ?>
                        <label class="member-option">
                            <input type="checkbox" name="membres[]" value="<?= $user['id'] ?>">
                            <img src="<?= htmlspecialchars($user->avatar && trim($user->avatar) ? $user->avatar : 'assets/img/default-avatar.png') ?>" 
                                 alt="avatar" class="member-avatar">
                            <span><?= htmlspecialchars($user->nom) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <small style="color: var(--text-muted); margin-top: 8px; display: block;">
                    <i class="fas fa-info-circle"></i>
                    Sélectionnez au moins un membre pour créer le groupe
                </small>
            </div>
            
            <button type="submit" class="btn-primary">
                <i class="fas fa-plus"></i>
                Créer le groupe
            </button>
        </form>
        
        <?php if ($message): ?>
            <div class="alert alert-error" style="margin-top: 16px;">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('groupModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('groupModal').style.display = 'none';
}

// Fermer la modal en cliquant à l'extérieur
document.getElementById('groupModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Afficher la modal si il y a une erreur
<?php if ($message): ?>
openModal();
<?php endif; ?>
</script>
</body>
</html>
<?php
ob_end_flush();
?>
