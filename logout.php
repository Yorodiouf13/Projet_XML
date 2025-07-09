<?php
session_start();
require_once 'inc/xml_utils.php';
if (isset($_SESSION['utilisateur_id'])) {
    modifierStatut($_SESSION['utilisateur_id'], "Hors ligne");
}
session_unset();      // Vide toutes les variables de session
session_destroy();    // Détruit la session
header('Location: connexion.php'); // Redirige vers la page de connexion
exit;
