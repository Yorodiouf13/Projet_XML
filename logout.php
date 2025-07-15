<?php
session_start();
require_once 'inc/xml_utils.php';

// Mettre le statut hors ligne avant de se déconnecter
if (isset($_SESSION['utilisateur_id'])) {
    modifierStatut($_SESSION['utilisateur_id'], 'Hors ligne');
}

// Détruire la session
session_destroy();

// Rediriger vers la page d'accueil
header('Location: index.php');
exit;
?>
