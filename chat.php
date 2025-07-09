<?php
session_start();
require_once 'inc/xml_utils.php';

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit;
}

$monId = $_SESSION['utilisateur_id'];
$monNom = $_SESSION['utilisateur_nom'];
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
<title>Plateforme Chat XML</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="styles.css">
<style>
body { margin:0; background: #f4f6fa; font-family: 'Segoe UI',Arial,sans-serif;}
.main-layout { display: flex; height: 100vh; }
@media (max-width: 700px) {.main-layout{flex-direction:column;}}
.sidebar {
    background: #fff;
    border-right: 1.5px solid #e2e8f0;
    width: 300px; min-width: 170px;
    display: flex; flex-direction: column; height: 100vh;
}
.sidebar-header { font-size: 1.2em; font-weight: bold; padding: 18px 15px; color:#0069c2; border-bottom:1.5px solid #e2e8f0;}
.contact-list { flex:1; overflow-y: auto; padding: 4px 0; }
.contact {
    display: flex; align-items: center; padding: 12px 15px;
    cursor:pointer; border-bottom:1px solid #f5f6fb;
    background: transparent; text-decoration: none; color:#222;
}
.contact.selected, .contact:hover { background: #f1f7ff;}
.avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 12px; border:1.5px solid #e0e0e0;}
.info { flex:1; }
.name { font-weight:bold; font-size:1.05em;}
.preview { color:#888; font-size:0.96em;}
.statut-enligne { color:green; font-size:1.1em; margin-left: 6px;}
.badge { background:#FFDE59; color:#222; border-radius:10px; padding:2px 8px; font-size:0.92em; margin-left:8px; }
.links { padding:11px 15px 6px 15px; border-bottom:1.5px solid #e2e8f0;}
.links a { margin-right:14px; color:#0069c2; text-decoration: none; font-size:1em;}
.links a:hover { text-decoration: underline;}
@media (max-width:700px) {.sidebar{width:100vw;min-width:0;height:auto;}}
.center-panel { flex:1; display: flex; flex-direction: column; min-width:0;}
.center-header {
    background:#fff; border-bottom:1.5px solid #e2e8f0;
    display: flex; align-items: center; padding: 15px;
}
.center-header .avatar { width: 36px; height: 36px; margin-right:10px;}
.center-header .name { font-size:1.1em;}
.center-header .statut-enligne { margin-left:8px;}
.chat-messages { flex:1; overflow-y:auto; padding: 22px 16px; background:#f4f8ff; min-height:120px;}
.message-block { margin-bottom:20px; }
.message-block.me .bubble { background: #d7eaff; border-radius: 12px 12px 4px 12px;}
.message-block.them .bubble { background: #f1f4fb; border-radius: 12px 12px 12px 4px;}
.bubble { display:inline-block; padding:9px 15px; font-size:1.03em; margin-top:2px;}
.meta { font-size:0.92em; color:#888; margin-bottom:2px;}
.chat-input { background:#fff; border-top:1.5px solid #e2e8f0; padding:13px 18px; display:flex; gap:10px;}
.chat-input input[type="text"] { flex:1; padding:8px 14px; border:1px solid #ccd; border-radius:6px;}
.chat-input input[type="file"] {padding:2px;}
.chat-input button { background: #0069c2; color: #fff; border: none; border-radius: 5px; padding: 8px 16px;}
@media (max-width:700px) {.center-panel{min-width:0;}}
</style>
</head>
<body>
<div class="main-layout">

    <!-- Sidebar contacts -->
    <aside class="sidebar">
        <div class="sidebar-header">
            Plateforme Chat
        </div>
        <div class="links">
            <a href="profil.php">Profil</a>
            <a href="groupes.php">Groupes</a>
            <a href="logout.php">Déconnexion</a>
        </div>
        <div class="contact-list">
        <?php if (count($autresUtilisateurs)): ?>
            <?php foreach ($autresUtilisateurs as $user): 
                // Notification : message non lu ?
                $messages = getMessagesPrives($monId, (string)$user['id']);
                $lastRead = $_SESSION['messages_lus'][(string)$user['id']] ?? 0;
                $nouveau = false;
                $dernierMsg = "";
                $dernierAuteurMoi = false;
                if ($messages) {
                    $dernier = end($messages);
                    $dernierMsg = htmlspecialchars(mb_strimwidth((string)$dernier,0,26,'...'));
                    $dernierAuteurMoi = ($dernier['auteur'] == $monId);
                    // Badge "Nouveau!"
                    foreach ($messages as $msg) {
                        if ($msg['auteur'] == (string)$user['id'] && $msg['id'] > $lastRead) {
                            $nouveau = true; break;
                        }
                    }
                }
                $selected = ($contactSelectionneId == (string)$user['id']);
            ?>
            <a href="chat.php?user=<?= (string)$user['id'] ?>" class="contact<?= $selected ? " selected" : "" ?>">
                <img class="avatar" src="<?= htmlspecialchars($user->avatar && trim($user->avatar) ? $user->avatar : 'assets/img/default-avatar.png') ?>" alt="avatar">
                <div class="info">
                    <div class="name"><?= htmlspecialchars($user->nom) ?>
                        <?php if ($user->statut == "En ligne"): ?>
                            <span class="statut-enligne">●</span>
                        <?php endif; ?>
                    </div>
                    <div class="preview">
                        <?php if ($dernierMsg): ?>
                            <?= $dernierAuteurMoi ? "Moi: " : "" ?><?= $dernierMsg ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($nouveau): ?>
                    <span class="badge">Nouveau!</span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="color:#aaa;padding:17px;">Aucun autre utilisateur</div>
        <?php endif; ?>
        </div>
    </aside>

    <!-- Centre : conversation -->
    <section class="center-panel">
    <?php
    $utilisateurCible = null;
    if ($contactSelectionneId) {
        foreach ($autresUtilisateurs as $u) {
            if ((string)$u['id'] === $contactSelectionneId) { $utilisateurCible = $u; break; }
        }
    }
    if ($utilisateurCible):
        include 'discussion_zone.php';
    else: ?>
        <div class="center-header"><div style="color:#888">Sélectionne un contact à gauche pour commencer une discussion</div></div>
    <?php endif; ?>
    </section>
</div>
</body>
</html>
