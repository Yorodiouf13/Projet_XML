# Plateforme de messagerie collaborative (PHP + XML)

Ce projet est une application web de messagerie instantanée, inspirée de WhatsApp Web, développée en PHP, sans base de données classique : **toutes les données sont stockées en XML** (fichier `plateforme.xml`).

## Fonctionnalités principales

- Inscription, connexion, gestion de profil avec avatar
- Discussions privées et de groupe
- Envoi de fichiers et d’images
- Interface moderne et responsive
- Notifications de nouveaux messages
- Statut "en ligne" des utilisateurs

---

## Installation et premiers pas

### Prérequis

- **WAMP** (pour Windows) ou **MAMP** (pour Mac) ou tout serveur local compatible PHP 8+
- Un navigateur web

### Déploiement

1. **Clonez ce dépôt** dans votre répertoire web (ex : `www/` sous WAMP)
   ```sh
   git clone https://github.com/votre-utilisateur/votre-projet.git
2. Créez les dossiers nécessaires :

data/
(stocke le fichier de données principal : plateforme.xml)

assets/files/
(stocke les fichiers envoyés dans les discussions)

assets/img/
(stocke les images de profil des utilisateurs)


3. Démarrez WAMP/MAMP et ouvrez l’application à
http://localhost/nom_du_projet/

4. Créez un compte utilisateur

À la première inscription, le fichier data/plateforme.xml sera automatiquement généré et initialisé.
Il n’est donc pas nécessaire de le créer à la main.

Structure des données
Fichier XML principal :
data/plateforme.xml
(stocke : utilisateurs, groupes, messages, fichiers, etc.)

Fichiers envoyés :
assets/files/
(stocke tous les documents et images partagés dans les discussions)

Avatars utilisateurs :
assets/img/
(stocke les images de profil, uploadées lors de l’inscription ou dans le profil)

## Important :
Tous ces fichiers et dossiers sont exclus du contrôle de version grâce au fichier .gitignore.
Ainsi, aucune donnée personnelle ni fichier volumineux n’est versionné sur GitHub.

## Bonnes pratiques
Ne jamais pousser de données utilisateurs ou de fichiers uploadés sur le dépôt
Documenter les évolutions du code via les commits
Pour ajouter des fonctionnalités, respecter l’architecture du projet (PHP + XML, MVC simplifié)
Utilisez les issues et les pull requests pour collaborer efficacement

## Ressources
Documentation PHP SimpleXML
Documentation WAMP
Documentation MAMP

# Collaboration
  ### - Oumar Yoro DIOUF
  ### - Adja Sira DOUMBOUYA
  ### - Amadou Sall GUEYE
  ### - Zeynabou BA
