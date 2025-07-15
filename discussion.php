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

// Gestion de la sélection de fichier
function handleFileSelect(input) {
    const fileBubble = document.getElementById('fileBubble');
    if (input.files.length > 0) {
        fileBubble.classList.add('file-selected');
        fileBubble.title = 'Fichier sélectionné: ' + input.files[0].name;
    } else {
        fileBubble.classList.remove('file-selected');
        fileBubble.title = 'Joindre un fichier';
    }
}

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
