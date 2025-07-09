<?php
session_start();
require_once 'inc/xml_utils.php';

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit;
}
$monId = $_SESSION['utilisateur_id'];
$monNom = $_SESSION['utilisateur_nom'];

$idGroupe = $_GET['id'] ?? null;
$groupe = $idGroupe ? trouverGroupeParId($idGroupe) : false;

$estMembre = false;
if ($groupe) {
    foreach ($groupe->membres->membre as $m) {
        if ((string)$m['id'] === (string)$monId) {
            $estMembre = true;
            break;
        }
    }
}
if (!$groupe || !$estMembre) {
    header('Location: groupes.php');
    exit;
}

// Envoi de message
$messageErreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contenu = trim($_POST['message'] ?? '');
    $cheminFichier = null;
    $nomFichier = null;
    if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
        $nomFichier = basename($_FILES['fichier']['name']);
        $dossier = "assets/files/";
        if (!is_dir($dossier)) mkdir($dossier, 0777, true);
        $cheminCible = $dossier . uniqid() . '_' . $nomFichier;
        if (move_uploaded_file($_FILES['fichier']['tmp_name'], $cheminCible)) {
            $cheminFichier = $cheminCible;
        }
    }
    if ($contenu || $cheminFichier) {
        envoyerMessageGroupe($idGroupe, $monId, $contenu, $cheminFichier, $nomFichier);
        header("Location: discussion_groupe.php?id=" . urlencode($idGroupe));
        exit;
    } else {
        $messageErreur = "Le message ne peut pas être vide.";
    }
}

$messages = getMessagesGroupe($idGroupe);

// MAJ de la lecture des messages pour la notification “Nouveau !” sur groupes.php
if (!isset($_SESSION['groupes_lus'])) $_SESSION['groupes_lus'] = [];
if ($messages) {
    $lastMsgId = 0;
    foreach ($messages as $msg) {
        if ($msg['auteur'] != $monId) {
            $lastMsgId = max($lastMsgId, (int)$msg['id']);
        }
    }
    $_SESSION['groupes_lus'][(string)$idGroupe] = $lastMsgId;
}

// Liste des membres du groupe
$membres = [];
foreach ($groupe->membres->membre as $m) {
    $u = trouverUtilisateurParId((string)$m['id']);
    if ($u) $membres[] = $u;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars((string)$groupe->nom) ?> - Groupe</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6fa; }
        .container { width: 90%; max-width: 700px; margin: 40px auto; background: #fff; padding: 28px 32px; border-radius: 8px; box-shadow: 0 2px 12px #0002; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; }
        .group-name { font-weight: bold; font-size: 1.2em; }
        .back { text-decoration: none; color: #0069c2; font-size: 0.97em; }
        .members-list { margin: 12px 0 18px 0; padding: 0; display: flex; flex-wrap: wrap; gap: 18px;}
        .member { display: flex; align-items: center; gap: 7px; background: #f1f4fb; padding: 4px 9px; border-radius: 6px;}
        .avatar { vertical-align: middle; width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 1.5px solid #e0e0e0;}
        .statut-enligne { color:green;font-weight:bold; margin-left:8px;}
        .statut-horsligne { color:#aaa; margin-left:8px;}
        .chat-area { min-height: 220px; margin: 20px 0; background: #f8faff; border-radius: 8px; padding: 18px; box-shadow: 0 1px 3px #0001; }
        .message { margin-bottom: 17px; }
        .auteur { font-weight: bold; color: #0069c2; }
        .date { font-size: 0.85em; color: #888; margin-left: 8px; }
        .mine { background: #d7eaff; border-radius: 12px 12px 4px 12px; padding: 8px 12px; display: inline-block; }
        .other { background: #f1f4fb; border-radius: 12px 12px 12px 4px; padding: 8px 12px; display: inline-block; }
        form { margin-top: 18px; display: flex; gap: 10px; }
        input[type="text"] { flex: 1; padding: 8px 12px; border: 1px solid #ccd; border-radius: 5px; }
        input[type="file"] { padding: 2px; }
        button { background: #0069c2; color: #fff; border: none; padding: 8px 16px; border-radius: 5px; font-size: 1em; }
        button:hover { background: #004c91; }
        .error { color: #b8002e; }
    </style>
</head>
<body>
<div class="container">
    <div class="top-bar">
        <div><a href="groupes.php" class="back">&larr; Retour aux groupes</a></div>
        <div class="group-name"><?= htmlspecialchars($groupe->nom) ?></div>
    </div>
    <div class="members-list">
        <?php foreach ($membres as $m): ?>
            <span class="member">
                <?php if ($m->avatar && trim($m->avatar)): ?>
                    <img src="<?= htmlspecialchars($m->avatar) ?>" alt="avatar" class="avatar">
                <?php else: ?>
                    <img src="assets/img/default-avatar.png" alt="avatar" class="avatar">
                <?php endif; ?>
                <?= htmlspecialchars($m->nom) ?>
                <?php if ($m->statut == "En ligne"): ?>
                    <span class="statut-enligne">•</span>
                <?php else: ?>
                    <span class="statut-horsligne">•</span>
                <?php endif; ?>
            </span>
        <?php endforeach; ?>
    </div>
    <div class="chat-area">
        <?php if (!$messages): ?>
            <p style="color:#888;">Aucun message pour le moment. Démarre la discussion de groupe !</p>
        <?php else: ?>
            <?php foreach ($messages as $msg): 
                $auteur = trouverUtilisateurParId((string)$msg['auteur']);
            ?>
                <div class="message">
                    <span class="<?= $msg['auteur'] == $monId ? 'auteur mine' : 'auteur other' ?>">
                        <?= $msg['auteur'] == $monId ? 'Moi' : htmlspecialchars($auteur ? $auteur->nom : 'Membre') ?>
                    </span>
                    <span class="date"><?= date('d/m/Y H:i', strtotime($msg['date'])) ?></span>
                    <div class="<?= $msg['auteur'] == $monId ? 'mine' : 'other' ?>">
                        <?= nl2br(htmlspecialchars((string)$msg)) ?>
                        <?php if (isset($msg->fichier)) : ?>
                            <br>
                            <a href="<?= htmlspecialchars($msg->fichier['chemin']) ?>" download="<?= htmlspecialchars($msg->fichier['nom']) ?>">
                                📎 <?= htmlspecialchars($msg->fichier['nom']) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php if ($messageErreur): ?><div class="error"><?= htmlspecialchars($messageErreur) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data" autocomplete="off">
        <input type="text" name="message" placeholder="Écris ton message au groupe..." required>
        <input type="file" name="fichier">
        <button type="submit">Envoyer</button>
    </form>
</div>
</body>
</html>
