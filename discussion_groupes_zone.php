<?php
// $monId, $groupeCible, $groupeSelectionneId doivent être définis !
$messages = getMessagesGroupe((string)$groupeCible['id']);

// Marquer les messages comme lus
if ($messages) {
    $lastMsgId = 0;
    foreach ($messages as $msg) {
        if ($msg['auteur'] != $monId) {
            $lastMsgId = max($lastMsgId, (int)$msg['id']);
        }
    }
    $_SESSION['groupes_lus'][(string)$groupeCible['id']] = $lastMsgId;
}

// Gestion envoi message
$messageErreur = '';
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['send_to_group'])
    && $_POST['send_to_group'] == (string)$groupeCible['id']
) {
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
        envoyerMessageGroupe((string)$groupeCible['id'], $monId, $contenu, $cheminFichier, $nomFichier);
        echo '<script>window.location.href="groupes.php?id=' . urlencode((string)$groupeCible['id']) . '";</script>';
        exit;
    } else {
        $messageErreur = "Le message ne peut pas être vide.";
    }
}

// Liste des membres
$membres = [];
foreach ($groupeCible->membres->membre as $m) {
    $u = trouverUtilisateurParId((string)$m['id']);
    if ($u) $membres[] = $u;
}
?>
<div class="center-header">
    <div style="display:flex;align-items:center;flex-wrap:wrap;gap:9px 12px;">
        <?php foreach ($membres as $m): ?>
            <span class="member">
                <img src="<?= htmlspecialchars($m->avatar && trim($m->avatar) ? $m->avatar : 'assets/img/default-avatar.png') ?>" class="avatar" alt="avatar">
                <?= htmlspecialchars($m->nom) ?>
                <?php if ($m->statut == "En ligne"): ?>
                    <span class="statut-enligne">●</span>
                <?php else: ?>
                    <span class="statut-horsligne">●</span>
                <?php endif; ?>
            </span>
        <?php endforeach; ?>
    </div>
    <div style="margin-left:auto;font-weight:bold;color:#0069c2;"><?= htmlspecialchars($groupeCible->nom) ?></div>
</div>
<div class="chat-messages">
    <?php if (!$messages): ?>
        <div style="color:#888;">Aucun message. Commence la discussion !</div>
    <?php else: ?>
        <?php foreach ($messages as $msg):
            $auteur = trouverUtilisateurParId((string)$msg['auteur']);
        ?>
            <div class="message-block <?= $msg['auteur'] == $monId ? 'me' : 'them' ?>">
                <div class="meta">
                    <?= $msg['auteur'] == $monId ? 'Moi' : htmlspecialchars($auteur ? $auteur->nom : 'Membre') ?>
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
    <input type="hidden" name="send_to_group" value="<?= $groupeCible['id'] ?>">
    <input type="text" name="message" placeholder="Écris un message au groupe...">
    <input type="file" name="fichier">
    <button type="submit">Envoyer</button>
</form>
<?php if ($messageErreur): ?>
    <div style="color:#b8002e;padding-left:16px;"><?= htmlspecialchars($messageErreur) ?></div>
<?php endif; ?>
