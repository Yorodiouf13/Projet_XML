<?php
// $monId, $utilisateurCible, $contactSelectionneId doivent être définis avant l'inclusion !
$messages = getMessagesPrives($monId, (string)$utilisateurCible['id']);

// Marquer les messages comme lus
if ($messages) {
    $lastMsgId = 0;
    foreach ($messages as $msg) {
        if ($msg['auteur'] == (string)$utilisateurCible['id']) {
            $lastMsgId = max($lastMsgId, (int)$msg['id']);
        }
    }
    $_SESSION['messages_lus'][(string)$utilisateurCible['id']] = $lastMsgId;
}

// Traitement envoi message
$messageErreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_to']) && $_POST['send_to'] == (string)$utilisateurCible['id']) {
    $contenu = trim($_POST['message'] ?? '');
    $cheminFichier = null; $nomFichier = null;
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
        envoyerMessagePrive($monId, (string)$utilisateurCible['id'], $contenu, $cheminFichier, $nomFichier);
        header("Location: chat.php?user=" . urlencode((string)$utilisateurCible['id']));
        exit;
    } else {
        $messageErreur = "Le message ne peut pas être vide.";
    }
}
?>
<div class="center-header">
    <img class="avatar" src="<?= htmlspecialchars($utilisateurCible->avatar && trim($utilisateurCible->avatar) ? $utilisateurCible->avatar : 'assets/img/default-avatar.png') ?>" alt="avatar">
    <div class="name"><?= htmlspecialchars($utilisateurCible->nom) ?>
        <?php if ($utilisateurCible->statut == "En ligne"): ?>
            <span class="statut-enligne">●</span>
        <?php else: ?>
            <span style="color:#aaa;font-size:1.1em;">●</span>
        <?php endif; ?>
    </div>
</div>
<div class="chat-messages">
    <?php if (!$messages): ?>
        <div style="color:#888;">Aucun message. Commence la conversation !</div>
    <?php else: ?>
        <?php foreach ($messages as $msg): ?>
            <div class="message-block <?= $msg['auteur'] == $monId ? 'me' : 'them' ?>">
                <div class="meta">
                    <?= $msg['auteur'] == $monId ? 'Moi' : htmlspecialchars($utilisateurCible->nom) ?>
                    • <?= date('H:i', strtotime($msg['date'])) ?>
                </div>
                <div class="bubble">
                    <?= nl2br(htmlspecialchars((string)$msg)) ?>
                    <?php if (isset($msg->fichier)) : ?>
                        <br>
                        <a href="<?= htmlspecialchars($msg->fichier['chemin']) ?>" download="<?= htmlspecialchars($msg->fichier['nom']) ?>">📎 <?= htmlspecialchars($msg->fichier['nom']) ?></a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<form class="chat-input" method="post" enctype="multipart/form-data" autocomplete="off">
    <input type="hidden" name="send_to" value="<?= $utilisateurCible['id'] ?>">
    <input type="text" name="message" placeholder="Écris un message...">
    <input type="file" name="fichier">
    <button type="submit">Envoyer</button>
</form>
<?php if ($messageErreur): ?>
    <div style="color:#b8002e;padding-left:16px;"><?= htmlspecialchars($messageErreur) ?></div>
<?php endif; ?>
