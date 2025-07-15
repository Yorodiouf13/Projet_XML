<?php
// Chemin vers le fichier XML (adapter si besoin)
define('XML_PATH', __DIR__ . '/../data/plateforme.xml');

// Charger le XML (créé un squelette si absent)
function chargerXML() {
    if (!file_exists(XML_PATH)) {
        // Créer un XML de base si inexistant
        $xml = new SimpleXMLElement('<?xml version="1.0"?><plateforme><utilisateurs/></plateforme>');
        $xml->asXML(XML_PATH);
    }
    return simplexml_load_file(XML_PATH);
}

// Sauvegarder le XML
function sauvegarderXML($xml) {
    $xml->asXML(XML_PATH);
}

// Trouver un utilisateur par email
function trouverUtilisateurParEmail($email) {
    $xml = chargerXML();
    foreach ($xml->utilisateurs->utilisateur as $utilisateur) {
        if (strtolower((string)$utilisateur->email) === strtolower($email)) {
            return $utilisateur;
        }
    }
    return false;
}

// Ajouter un utilisateur (renvoie true/false)
function ajouterUtilisateur($nom, $email, $motdepasse, $avatar = null) {
    $xml = chargerXML();
    // Vérifier que l'email n'est pas déjà utilisé
    if (trouverUtilisateurParEmail($email)) {
        return false; // email déjà pris
    }
    
    // Générer un nouvel ID unique
    $lastId = 0;
    foreach ($xml->utilisateurs->utilisateur as $u) {
        $lastId = max($lastId, intval($u['id']));
    }
    $newId = $lastId + 1;
    
    $utilisateur = $xml->utilisateurs->addChild('utilisateur');
    $utilisateur->addAttribute('id', $newId);
    $utilisateur->addChild('nom', htmlspecialchars($nom));
    $utilisateur->addChild('email', htmlspecialchars($email));
    $utilisateur->addChild('motdepasse', password_hash($motdepasse, PASSWORD_DEFAULT));
    $utilisateur->addChild('avatar', $avatar ?? '');
    $utilisateur->addChild('statut', 'Hors ligne');
    
    sauvegarderXML($xml);
    return true;
}

// Vérifier mot de passe (login)
function verifierMotDePasse($email, $motdepasse) {
    $utilisateur = trouverUtilisateurParEmail($email);
    if ($utilisateur) {
        return password_verify($motdepasse, (string)$utilisateur->motdepasse) ? $utilisateur : false;
    }
    return false;
}

// Retourne tous les utilisateurs sauf l'utilisateur connecté
function getAutresUtilisateurs($monId) {
    $xml = chargerXML();
    $users = [];
    foreach ($xml->utilisateurs->utilisateur as $u) {
        if ((string)$u['id'] !== (string)$monId) {
            $users[] = $u;
        }
    }
    return $users;
}

function trouverUtilisateurParId($id) {
    $xml = chargerXML();
    foreach ($xml->utilisateurs->utilisateur as $utilisateur) {
        if ((string)$utilisateur['id'] === (string)$id) {
            return $utilisateur;
        }
    }
    return false;
}

// Récupérer tous les messages privés entre deux utilisateurs
function getMessagesPrives($id1, $id2) {
    $xml = chargerXML();
    if (!isset($xml->messages)) {
        return [];
    }
    
    $messages = [];
    foreach ($xml->messages->message as $msg) {
        $auteur = (string)$msg['auteur'];
        $destinataire = (string)$msg['destinataire'];
        if (
            ($auteur === (string)$id1 && $destinataire === (string)$id2) ||
            ($auteur === (string)$id2 && $destinataire === (string)$id1)
        ) {
            $messages[] = $msg;
        }
    }
    
    // Tri par date croissante
    usort($messages, function($a, $b) {
        return strcmp($a['date'], $b['date']);
    });
    
    return $messages;
}

// Envoyer un message privé
function envoyerMessagePrive($auteur, $destinataire, $contenu, $cheminFichier = null, $nomFichier = null) {
    $xml = chargerXML();
    if (!isset($xml->messages)) {
        $xml->addChild('messages');
    }
    
    $lastId = 0;
    foreach ($xml->messages->message as $m) {
        $lastId = max($lastId, intval($m['id']));
    }
    $newId = $lastId + 1;
    
    $msg = $xml->messages->addChild('message', htmlspecialchars($contenu));
    $msg->addAttribute('id', $newId);
    $msg->addAttribute('auteur', $auteur);
    $msg->addAttribute('destinataire', $destinataire);
    $msg->addAttribute('date', date('c'));
    
    // Si fichier joint
    if ($cheminFichier && $nomFichier) {
        $fichier = $msg->addChild('fichier');
        $fichier->addAttribute('chemin', $cheminFichier);
        $fichier->addAttribute('nom', $nomFichier);
    }
    
    sauvegarderXML($xml);
    return true;
}

// Fonctions pour les groupes
function chargerGroupes() {
    $xml = chargerXML();
    return isset($xml->groupes) ? $xml->groupes->groupe : [];
}

function creerGroupe($nom, $idsMembres) {
    $xml = chargerXML();
    if (!isset($xml->groupes)) {
        $xml->addChild('groupes');
    }
    
    $lastId = 0;
    foreach ($xml->groupes->groupe as $groupe) {
        $lastId = max($lastId, intval($groupe['id']));
    }
    $newId = $lastId + 1;
    
    $groupe = $xml->groupes->addChild('groupe');
    $groupe->addAttribute('id', $newId);
    $groupe->addChild('nom', htmlspecialchars($nom));
    
    $membres = $groupe->addChild('membres');
    foreach ($idsMembres as $id) {
        $membres->addChild('membre')->addAttribute('id', $id);
    }
    
    $groupe->addChild('messages');
    
    sauvegarderXML($xml);
    return $newId;
}

function getGroupesUtilisateur($idUtilisateur) {
    $groupes = chargerGroupes();
    $result = [];
    foreach ($groupes as $groupe) {
        foreach ($groupe->membres->membre as $membre) {
            if ((string)$membre['id'] === (string)$idUtilisateur) {
                $result[] = $groupe;
                break;
            }
        }
    }
    return $result;
}

function trouverGroupeParId($id) {
    $groupes = chargerGroupes();
    foreach ($groupes as $groupe) {
        if ((string)$groupe['id'] === (string)$id) {
            return $groupe;
        }
    }
    return false;
}

function getMessagesGroupe($idGroupe) {
    $groupe = trouverGroupeParId($idGroupe);
    if ($groupe && isset($groupe->messages)) {
        $messages = [];
        foreach ($groupe->messages->message as $msg) {
            $messages[] = $msg;
        }
        usort($messages, function($a, $b) {
            return strcmp($a['date'], $b['date']);
        });
        return $messages;
    }
    return [];
}

function envoyerMessageGroupe($idGroupe, $auteur, $contenu, $cheminFichier = null, $nomFichier = null) {
    $xml = chargerXML();
    $groupe = null;
    foreach ($xml->groupes->groupe as $g) {
        if ((string)$g['id'] === (string)$idGroupe) {
            $groupe = $g;
            break;
        }
    }
    
    if (!$groupe) return false;
    
    $lastId = 0;
    foreach ($groupe->messages->message as $m) {
        $lastId = max($lastId, intval($m['id']));
    }
    $newId = $lastId + 1;
    
    $msg = $groupe->messages->addChild('message', htmlspecialchars($contenu));
    $msg->addAttribute('id', $newId);
    $msg->addAttribute('auteur', $auteur);
    $msg->addAttribute('date', date('c'));
    
    if ($cheminFichier && $nomFichier) {
        $fichier = $msg->addChild('fichier');
        $fichier->addAttribute('chemin', $cheminFichier);
        $fichier->addAttribute('nom', $nomFichier);
    }
    
    sauvegarderXML($xml);
    return true;
}

function modifierAvatar($idUtilisateur, $cheminAvatar) {
    $xml = chargerXML();
    foreach ($xml->utilisateurs->utilisateur as $u) {
        if ((string)$u['id'] === (string)$idUtilisateur) {
            $u->avatar = $cheminAvatar;
            sauvegarderXML($xml);
            return true;
        }
    }
    return false;
}

function modifierStatut($idUtilisateur, $statut) {
    $xml = chargerXML();
    foreach ($xml->utilisateurs->utilisateur as $u) {
        if ((string)$u['id'] === (string)$idUtilisateur) {
            $u->statut = htmlspecialchars($statut);
            sauvegarderXML($xml);
            return true;
        }
    }
    return false;
}
?>
