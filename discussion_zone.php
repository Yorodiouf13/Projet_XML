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

<!-- Zone des messages -->
<div class="chat-messages" id="chatMessages">
    <?php if (!$messages): ?>
        <div style="text-align: center; padding: 40px; color: var(--text-muted);">
            <i class="fas fa-comment" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
            <p>Aucun message pour le moment</p>
            <p style="font-size: 12px; margin-top: 8px;">Envoyez votre premier message à <?= htmlspecialchars($utilisateurCible->nom) ?> !</p>
        </div>
    <?php else: ?>
        <?php 
        $lastDate = '';
        foreach ($messages as $msg): 
            $msgDate = date('Y-m-d', strtotime($msg['date']));
            $today = date('Y-m-d');
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            
            // Afficher le séparateur de date
            if ($msgDate !== $lastDate) {
                $dateLabel = '';
                if ($msgDate === $today) {
                    $dateLabel = "Aujourd'hui";
                } elseif ($msgDate === $yesterday) {
                    $dateLabel = "Hier";
                } else {
                    $dateLabel = date('d/m/Y', strtotime($msg['date']));
                }
                echo '<div style="text-align: center; margin: 20px 0; color: var(--text-muted); font-size: 12px;">' . $dateLabel . '</div>';
                $lastDate = $msgDate;
            }
        ?>
            <div class="message-block <?= $msg['auteur'] == $monId ? 'me' : 'them' ?>">
                <div class="message-bubble">
                    <div class="message-text">
                        <?= nl2br(htmlspecialchars((string)$msg)) ?>
                        <?php if (isset($msg->fichier)): ?>
                            <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid rgba(0,0,0,0.1);">
                                <a href="<?= htmlspecialchars($msg->fichier['chemin']) ?>" 
                                   download="<?= htmlspecialchars($msg->fichier['nom']) ?>"
                                   style="color: var(--primary-color); text-decoration: none; display: flex; align-items: center; gap: 6px;">
                                    <i class="fas fa-paperclip"></i>
                                    <?= htmlspecialchars($msg->fichier['nom']) ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="message-meta">
                        <span class="message-time"><?= date('H:i', strtotime($msg['date'])) ?></span>
                        <?php if ($msg['auteur'] == $monId): ?>
                            <span class="message-status">
                                <i class="fas fa-check-double" style="color: var(--primary-color);"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Zone de saisie -->
<div class="chat-input-container">
    <?php if ($messageErreur): ?>
        <div style="color: #e74c3c; font-size: 12px; margin-bottom: 8px; padding: 8px; background: #fdf2f2; border-radius: 6px;">
            <?= htmlspecialchars($messageErreur) ?>
        </div>
    <?php endif; ?>
    
    <form class="chat-input-form" method="post" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="send_to" value="<?= $utilisateurCible['id'] ?>">
        
        <!-- Bulle pour joindre un fichier -->
        <label class="action-bubble file-bubble" id="fileBubble" title="Joindre un fichier">
            <i class="fas fa-paperclip"></i>
            <input type="file" name="fichier" id="fileInput" onchange="handleFileSelect(this)">
        </label>
        
        <div class="input-wrapper">
            <textarea class="chat-input" 
                      name="message" 
                      placeholder="Tapez votre message..." 
                      rows="1"
                      id="messageInput"></textarea>
        </div>
        
        <!-- Bulle pour envoyer -->
        <button type="submit" class="action-bubble send-bubble" id="sendBtn" title="Envoyer">
            <i class="fas fa-paper-plane"></i>
        </button>
    </form>
</div>


<script>
// Auto-resize du textarea
const messageInput = document.getElementById('messageInput');
if (messageInput) {
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
    
    // Envoyer avec Entrée (Shift+Entrée pour nouvelle ligne)
    messageInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            document.querySelector('.chat-input-form').submit();
        }
    });
}

// Afficher le nom du fichier sélectionné
document.getElementById('fileInput').addEventListener('change', function(e) {
    if (e.target.files.length > 0) {
        const fileName = e.target.files[0].name;
        // Vous pouvez afficher le nom du fichier quelque part
        console.log('Fichier sélectionné:', fileName);
    }
});

// Auto-scroll vers le bas
function scrollToBottom() {
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

// Scroll au chargement
document.addEventListener('DOMContentLoaded', scrollToBottom);

// Scroll après envoi de message
window.addEventListener('load', scrollToBottom);
</script>
