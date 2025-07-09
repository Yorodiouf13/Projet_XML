<?php
session_start();
require_once 'inc/xml_utils.php';

// Protection d'accès
if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit;
}

$monId = $_SESSION['utilisateur_id'];
$monNom = $_SESSION['utilisateur_nom'];
$autreId = $_GET['user'] ?? null;

if (!$autreId || $autreId == $monId) {
    header('Location: chat.php');
    exit;
}
$autreUser = trouverUtilisateurParId($autreId);
if (!$autreUser) {
    header('Location: chat.php');
    exit;
}

// Gestion de l'envoi de message
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
        envoyerMessagePrive($monId, $autreId, $contenu, $cheminFichier, $nomFichier);
        header("Location: discussion.php?user=" . urlencode($autreId));
        exit;
    } else {
        $messageErreur = "Le message ne peut pas être vide.";
    }
}

// Récupération et mise à jour notifications (messages lus)
$messages = getMessagesPrives($monId, $autreId);
if ($messages) {
    $lastMsgId = 0;
    foreach ($messages as $msg) {
        if ($msg['auteur'] == $autreId) {
            $lastMsgId = max($lastMsgId, (int)$msg['id']);
        }
    }
    $_SESSION['messages_lus'][$autreId] = $lastMsgId;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Discussion privée</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6fa; }
        .container { width: 90%; max-width: 650px; margin: 40px auto; background: #fff; padding: 28px 32px; border-radius: 8px; box-shadow: 0 2px 12px #0002; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; }
        .user { font-weight: bold; }
        .back { text-decoration: none; color: #0069c2; font-size: 0.95em; }
        .statut-enligne { color:green;font-weight:bold; margin-left:8px;}
        .statut-horsligne { color:#aaa; margin-left:8px;}
        .chat-area { min-height: 220px; margin: 25px 0; background: #f8faff; border-radius: 8px; padding: 18px; box-shadow: 0 1px 3px #0001; }
        .message { margin-bottom: 17px; }
        .auteur { font-weight: bold; color: #0069c2; }
        .date { font-size: 0.85em; color: #888; margin-left: 8px; }
        .mine { background: #d7eaff; border-radius: 12px 12px 4px 12px; padding: 8px 12px; display: inline-block; }
        .other { background: #f1f4fb; border-radius: 12px 12px 12px 4px; padding: 8px 12px; display: inline-block; }
        .avatar { vertical-align: middle; width: 34px; height: 34px; border-radius: 50%; object-fit: cover; margin-right: 8px; border: 1.5px solid #e0e0e0;}
        form { margin-top: 22px; display: flex; gap: 10px; }
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
            <div>
                <a href="chat.php" class="back">&larr; Retour aux contacts</a>
            </div>
            <div class="user">
                <?php if ($autreUser->avatar && trim($autreUser->avatar)): ?>
                    <img src="<?= htmlspecialchars($autreUser->avatar) ?>" alt="avatar" class="avatar">
                <?php else: ?>
                    <img src="assets/img/default-avatar.png" alt="avatar" class="avatar">
                <?php endif; ?>
                <?= htmlspecialchars($autreUser->nom) ?>
                <?php if ($autreUser->statut == "En ligne"): ?>
                    <span class="statut-enligne">• En ligne</span>
                <?php else: ?>
                    <span class="statut-horsligne">• Hors ligne</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="chat-area">
            <?php if (!$messages): ?>
                <p style="color:#888;">Aucun message pour le moment. Démarre la discussion !</p>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message">
                        <span class="<?= $msg['auteur'] == $monId ? 'auteur mine' : 'auteur other' ?>">
                            <?= $msg['auteur'] == $monId ? 'Moi' : htmlspecialchars($autreUser->nom) ?>
                        </span>
                        <span class="date">
                            <?= date('d/m/Y H:i', strtotime($msg['date'])) ?>
                        </span>
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
            <input type="text" name="message" placeholder="Écris ton message..." required>
            <input type="file" name="fichier">
            <button type="submit">Envoyer</button>
        </form>
    </div>
</body>
</html>
