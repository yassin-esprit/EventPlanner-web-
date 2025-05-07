# MYTHOS PLANNER

## 🎯 Présentation

**Mythos Planner** est une application web développée avec Symfony dans le cadre du module **PIDEV 3A** à **Esprit School of Engineering**.  
Elle repose sur une architecture modulaire comprenant six modules principaux : Utilisateur, événement , publicité , logistique et services, sponsor , marchandise et donation.
Chaque module remplit une fonction bien définie, permettant une gestion complète et flexible des événements.
L’application prend en charge plusieurs rôles utilisateur (administrateur, propriétaire d’événement, participant), chacun disposant de droits et de fonctionnalités spécifiques selon son profil.

## 📚 Table des matières

- [Fonctionnalités](#Fonctionnalités)
- [Technologies utilisées](#technologies-utilisées)
- [Installation](#installation)
- [Utilisation](#utilisation)
- [Modules du projet](#modules-du-projet)
  - [Module Utilisateur](#1-module-utilisateur)
  - [Module Événement](#2-module-événement)
  - [Publicités & Campagnes](#3-publicités--campagnes)
  - [Module Équipement](#4-module-équipement)
  - [Module Sponsor](#5-module-sponsor)
  - [Module Marchandises](#6-module-marchandises)
- [Contribution](#contribution)
- [Licence](#licence)
- [Remerciements](#remerciements)

## ✨ Fonctionnalités

- Gestion complète des événements
- Contrôle d’accès basé sur les rôles
- Système de publicités et campagnes interactives
- Gestion des sponsors 
- Gestion des équipements et reservation par événement
- Boutique de marchandises spécifique à chaque événement

## 🛠 Technologies utilisées

- **Framework** : Symfony 6.4.20
- **Langage** : PHP 8.2
- **Frontend** : Twig / HTML / CSS / JS
- **Base de données** : MySQL avec Doctrine ORM
- **APIs externes** :
  - API météo (ex : OpenWeatherMap)
  - API Map (ex: OpensStreetMap)
  - Twilio sms API
  - API  QrCode
  - Calendrier (ex: Google calender)
  - ChatBot API (Gemini)

## 🚀 Installation

1. Cloner le dépôt :
   ```bash
   git clone https://github.com/votre-utilisateur/eventmaster.git
   cd EventPlanner-web-
   ```
2. Installer les dépendances :
   ```bash
   composer i
   ```
3. Configurer l’environnement :
   - Copier `.env` en `.env.local`
   - Renseigner les identifiants de base de données, mailer, clés d’API, etc.

4. Exécuter les migrations :
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

5. Lancer le serveur local :
   ```bash
   symfony serve:start
   ```

## 💡 Utilisation

- Accédez à l’application via `http://localhost:8000`
- Créez un compte et connectez-vous selon l’un des rôles : administrateur, propriétaire ou participant
- Gérez vos événements, publicites, équipements, sponsors et marchandises

## 🧩 Modules du projet

### 1. 👤 Module Utilisateur

- **Administrateur** : Contrôle total du système
- **Propriétaire d’événement** : Ajoute, modifie ou supprime ses événements ; gère ses publicités, sponsors, équipements et marchandises
- **Participant** : Participe à des événements, voit les publicités et achète des marchandises

### 2. 📅 Module Événement

- Affichage des événements disponibles
- Participation aux événements
- Prévisions météorologiques intégrées via une API

### 3. 📢 Publicités & Campagnes

- Création de publicités et campagnes par les propriétaires
- Affichage dynamique des publicités selon les interactions

### 4. ⚙ Module Équipement

- Attribution d’équipements aux événements
- Gestion par les propriétaires d’événements

### 5. 💼 Module Sponsor

- Ajout et gestion des sponsors pour chaque événement
- Administration réservée aux propriétaires d’événements

### 6. 🛍 Module Marchandises

- Boutique dédiée pour chaque événement
- Achat de produits par les participants

## 🤝 Contribution

Les contributions sont les bienvenues !  
Veuillez forker le dépôt, créer une branche, proposer des pull requests, et respecter les standards PSR et la documentation.

## 📜 Licence

Ce projet est sous licence MIT. Consultez le fichier [LICENSE](LICENSE) pour plus d’informations.

