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

if (!isset($_SESSION['groupes_lus'])) {
    $_SESSION['groupes_lus'] = [];
}

$mesGroupes = getGroupesUtilisateur($monId);
$groupeSelectionneId = isset($_GET['id']) ? $_GET['id'] : null;

// TRAITEMENT CREATION DE GROUPE (formulaire de la modale)
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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Groupes - Plateforme XML</title>
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
.group-list { flex:1; overflow-y: auto; padding: 4px 0; }
.group {
    display: flex; align-items: center; padding: 12px 15px;
    cursor:pointer; border-bottom:1px solid #f5f6fb;
    background: transparent; text-decoration: none; color:#222;
}
.group.selected, .group:hover { background: #f1f7ff;}
.group-info { flex:1; }
.group-name { font-weight:bold; font-size:1.05em;}
.group-meta { color:#888; font-size:0.96em;}
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
.member { display: flex; align-items: center; gap: 7px; background: #f1f4fb; padding: 4px 9px; border-radius: 6px; margin-right:12px;}
.avatar { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; border: 1.5px solid #e0e0e0;}
.statut-enligne { color:green;font-size:1.06em; margin-left: 6px;}
.statut-horsligne { color:#aaa;font-size:1.06em; margin-left: 6px;}
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
/* --- MODALE --- */
#modalGroupe {display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(20,40,60,0.12);z-index:1100;align-items:center;justify-content:center;}
#modalGroupe .modale-content {
    background:#fff;max-width:400px;width:94vw;border-radius:13px;box-shadow:0 8px 40px #0003;
    padding:28px 30px;position:relative;
}
#modalGroupe h2 {color:#0069c2;margin-top:0;margin-bottom:13px;}
#modalGroupe label {font-weight:bold;}
#modalGroupe input[type="text"] { width:100%;padding:8px;border-radius:5px;border:1px solid #ccd;margin-bottom:14px;}
#modalGroupe select { width: 100%; min-height: 45px; padding: 4px; border-radius: 5px; margin-bottom:10px;}
#modalGroupe button[type="submit"] { background:#0069c2;color:#fff;border:none;border-radius:5px;padding:8px 20px;font-size:1em;}
#modalGroupe #btnCloseModal {position:absolute;right:12px;top:12px;background:transparent;font-size:1.6em;color:#0069c2;border:none;cursor:pointer;}
#modalGroupe #modalMsg {margin-top:10px;color:#b8002e;}
</style>
</head>
<body>
<div class="main-layout">

    <!-- Sidebar groupes -->
    <aside class="sidebar">
        <div class="sidebar-header">
            Mes Groupes
        </div>
        <div class="links">
            <a href="profil.php">Profil</a>
            <a href="chat.php">Contacts</a>
            <a href="logout.php">Déconnexion</a>
        </div>
        <div class="group-list">
        <?php if ($mesGroupes): ?>
            <?php foreach ($mesGroupes as $groupe): 
                $messages = getMessagesGroupe((string)$groupe['id']);
                $lastRead = $_SESSION['groupes_lus'][(string)$groupe['id']] ?? 0;
                $nouveau = false;
                $dernierMsg = "";
                $dernierAuteurMoi = false;
                if ($messages) {
                    $dernier = end($messages);
                    $dernierAuteurMoi = ($dernier['auteur'] == $monId);
                    $dernierMsg = htmlspecialchars(mb_strimwidth((string)$dernier,0,26,'...'));
                    foreach ($messages as $msg) {
                        if ($msg['auteur'] != $monId && $msg['id'] > $lastRead) {
                            $nouveau = true; break;
                        }
                    }
                }
                $selected = ($groupeSelectionneId == (string)$groupe['id']);
            ?>
            <a href="groupes.php?id=<?= (string)$groupe['id'] ?>" class="group<?= $selected ? " selected" : "" ?>">
                <div class="group-info">
                    <div class="group-name"><?= htmlspecialchars($groupe->nom) ?></div>
                    <div class="group-meta"><?= count($groupe->membres->membre) ?> membres
                        <?php if ($dernierMsg): ?>
                            &nbsp;• <?= $dernierAuteurMoi ? "Moi: " : "" ?><?= $dernierMsg ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($nouveau): ?>
                    <span class="badge">Nouveau!</span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="color:#aaa;padding:17px;">Aucun groupe</div>
        <?php endif; ?>
        </div>
        <div style="padding:15px;">
            <button id="btnOpenModal" style="background:#0069c2;color:#fff;padding:8px 16px;border-radius:6px;border:none;font-size:1em;cursor:pointer;">+ Créer un groupe</button>
        </div>
    </aside>

    <!-- Centre : discussion groupe -->
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
        <div class="center-header"><div style="color:#888">Sélectionne un groupe à gauche pour commencer la discussion</div></div>
    <?php endif; ?>
    </section>
</div>

<!-- MODALE POP-UP CREATION GROUPE -->
<div id="modalGroupe">
  <div class="modale-content">
    <button id="btnCloseModal">&times;</button>
    <h2>Créer un groupe</h2>
    <form method="post" id="formCreerGroupe">
      <label for="nom_groupe">Nom du groupe</label><br>
      <input type="text" name="nom_groupe" id="nom_groupe" required>
      <br>
      <label for="membres">Ajouter des membres</label><br>
      <select name="membres[]" id="membres" multiple required>
        <?php foreach ($autresUtilisateurs as $user): ?>
          <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user->nom) ?> (<?= htmlspecialchars($user->email) ?>)</option>
        <?php endforeach; ?>
      </select>
      <div style="color:#888;font-size:0.97em;margin-bottom:10px;">Ctrl/Cmd + clic pour sélection multiple</div>
      <button type="submit">Créer</button>
    </form>
    <div id="modalMsg"><?php if($message) echo htmlspecialchars($message); ?></div>
  </div>
</div>
<?php if ($message): ?>
<script>
document.getElementById('modalGroupe').style.display = 'flex';
document.getElementById('modalMsg').textContent = <?= json_encode($message) ?>;
</script>
<?php endif; ?>
<script>
// Ouvrir/Fermer la modale
document.getElementById('btnOpenModal').onclick = function() {
  document.getElementById('modalGroupe').style.display = 'flex';
};
document.getElementById('btnCloseModal').onclick = function() {
  document.getElementById('modalGroupe').style.display = 'none';
  document.getElementById('modalMsg').textContent = "";
};
document.getElementById('modalGroupe').onclick = function(e) {
  if(e.target === this) {
    this.style.display = 'none';
    document.getElementById('modalMsg').textContent = "";
  }
};
</script>
</body>
</html>
