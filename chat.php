<?php
session_start();
require_once 'inc/xml_utils.php';
ob_start();

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit;
}

$monId = $_SESSION['utilisateur_id'];
$monNom = $_SESSION['utilisateur_nom'];

// Mettre le statut en ligne
modifierStatut($monId, 'En ligne');

$autresUtilisateurs = getAutresUtilisateurs($monId);

if (!isset($_SESSION['messages_lus'])) {
    $_SESSION['messages_lus'] = [];
}

$contactSelectionneId = isset($_GET['user']) ? $_GET['user'] : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Chat - Plateforme XML</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="styles.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="main-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <!-- Header avec profil utilisateur -->
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
                <button class="header-btn" onclick="window.location.href='groupes.php'" title="Groupes">
                    <i class="fas fa-users"></i>
                </button>
                <button class="header-btn" onclick="showNewChatMenu()" title="Nouvelle discussion">
                    <i class="fas fa-comment-plus"></i>
                </button>
                <button class="header-btn" onclick="window.location.href='logout.php'" title="Déconnexion">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </div>

        <!-- Barre de recherche -->
        <div class="search-bar">
            <input type="text" class="search-input" placeholder="Rechercher ou démarrer une nouvelle discussion" id="searchInput">
        </div>

        <!-- Navigation tabs -->
        <div class="nav-tabs">
            <a href="chat.php" class="nav-tab active">
                <i class="fas fa-comment"></i> Discussions
            </a>
            <a href="groupes.php" class="nav-tab">
                <i class="fas fa-users"></i> Groupes
            </a>
        </div>

        <!-- Liste des contacts -->
        <div class="contact-list" id="contactList">
        <?php if (count($autresUtilisateurs)): ?>
            <?php foreach ($autresUtilisateurs as $user): 
                // Notification : message non lu ?
                $messages = getMessagesPrives($monId, (string)$user['id']);
                $lastRead = $_SESSION['messages_lus'][(string)$user['id']] ?? 0;
                $nouveau = false;
                $dernierMsg = "";
                $dernierAuteurMoi = false;
                $dernierTime = "";
                
                if ($messages) {
                    $dernier = end($messages);
                    $dernierMsg = htmlspecialchars(mb_strimwidth((string)$dernier, 0, 35, '...'));
                    $dernierAuteurMoi = ($dernier['auteur'] == $monId);
                    $dernierTime = date('H:i', strtotime($dernier['date']));
                    
                    // Badge "Nouveau!"
                    foreach ($messages as $msg) {
                        if ($msg['auteur'] == (string)$user['id'] && $msg['id'] > $lastRead) {
                            $nouveau = true; 
                            break;
                        }
                    }
                }
                
                $selected = ($contactSelectionneId == (string)$user['id']);
                $avatarSrc = ($user->avatar && trim($user->avatar)) 
                    ? $user->avatar 
                    : 'assets/img/default-avatar.png';
            ?>
            <a href="chat.php?user=<?= (string)$user['id'] ?>" class="contact<?= $selected ? " selected" : "" ?>" data-name="<?= htmlspecialchars(strtolower($user->nom)) ?>">
                <img class="contact-avatar" src="<?= htmlspecialchars($avatarSrc) ?>" alt="avatar">
                <div class="contact-info">
                    <div class="contact-name">
                        <?= htmlspecialchars($user->nom) ?>
                        <span class="status-indicator <?= $user->statut == "En ligne" ? "status-online" : "status-offline" ?>"></span>
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
                <p>Aucun contact disponible</p>
                <p style="font-size: 12px; margin-top: 8px;">Invitez vos amis à rejoindre la plateforme !</p>
            </div>
        <?php endif; ?>
        </div>
    </aside>

    <!-- Zone de chat principale -->
    <section class="center-panel">
    <?php
    $utilisateurCible = null;
    if ($contactSelectionneId) {
        foreach ($autresUtilisateurs as $u) {
            if ((string)$u['id'] === $contactSelectionneId) { 
                $utilisateurCible = $u; 
                break; 
            }
        }
    }
    
    if ($utilisateurCible):
        $avatarCible = ($utilisateurCible->avatar && trim($utilisateurCible->avatar)) 
            ? $utilisateurCible->avatar 
            : 'assets/img/default-avatar.png';
    ?>
        <!-- Header du chat -->
        <div class="chat-header">
            <img src="<?= htmlspecialchars($avatarCible) ?>" alt="avatar" class="chat-avatar">
            <div class="chat-info">
                <div class="chat-name"><?= htmlspecialchars($utilisateurCible->nom) ?></div>
                <div class="chat-status">
                    <?php if ($utilisateurCible->statut == "En ligne"): ?>
                        <i class="fas fa-circle" style="color: #4fc3f7; font-size: 8px;"></i> En ligne
                    <?php else: ?>
                        <i class="fas fa-circle" style="color: #8696a0; font-size: 8px;"></i> Hors ligne
                    <?php endif; ?>
                </div>
            </div>
            <div class="chat-actions">
                <button class="chat-btn" title="Rechercher">
                    <i class="fas fa-search"></i>
                </button>
                <button class="chat-btn" title="Plus d'options">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </div>
        </div>

        <!-- Messages -->
        <?php 
        $messageErreur = $messageErreur ?? ''; // Ajout de cette ligne
        include 'discussion_zone.php'; 
        ?>

    <?php else: ?>
        <!-- Écran d'accueil -->
        <div class="welcome-screen">
            <div class="welcome-icon">
                <i class="fas fa-comments"></i>
            </div>
            <h2 class="welcome-title">Bienvenue sur votre plateforme de chat</h2>
            <p class="welcome-subtitle">
                Sélectionnez une conversation dans la liste pour commencer à discuter avec vos contacts.
                Vous pouvez également créer de nouveaux groupes ou gérer votre profil.
            </p>
            
            <?php
            $mesGroupes = getGroupesUtilisateur($monId);
            $totalMessages = 0;
            
            // Compter les messages privés
            foreach ($autresUtilisateurs as $user) {
                $messages = getMessagesPrives($monId, (string)$user['id']);
                $totalMessages += count($messages);
            }
            
            // Compter les messages de groupe
            foreach ($mesGroupes as $groupe) {
                $messagesGroupe = getMessagesGroupe((string)$groupe['id']);
                $totalMessages += count($messagesGroupe);
            }
            ?>
            
            <div class="welcome-stats">
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
            
            <div class="welcome-actions">
                <a href="groupes.php" class="action-btn primary">
                    <i class="fas fa-users"></i>
                    Mes groupes
                </a>
                <a href="profil.php" class="action-btn secondary">
                    <i class="fas fa-user"></i>
                    Mon profil
                </a>
            </div>
        </div>
    <?php endif; ?>
    </section>
</div>

<script>
// Recherche en temps réel
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const contacts = document.querySelectorAll('.contact');
    
    contacts.forEach(contact => {
        const name = contact.getAttribute('data-name');
        if (name.includes(searchTerm)) {
            contact.style.display = 'flex';
        } else {
            contact.style.display = 'none';
        }
    });
});

// Auto-scroll vers le bas des messages
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.querySelector('.chat-messages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
});

// Menu nouvelle discussion (placeholder)
function showNewChatMenu() {
    alert('Fonctionnalité à venir : Nouvelle discussion');
}

// Auto-refresh léger (optionnel)
<?php if ($contactSelectionneId): ?>
setInterval(function() {
    // Ici vous pourriez ajouter une vérification AJAX pour les nouveaux messages
}, 30000);
<?php endif; ?>
</script>
</body>
</html>
<?php
ob_end_flush();
?>
