# Tableau de visualisation et de suivi de vacances.

### code ###
    efesfse
**gras**

**Objectif :** Visualiser par équipe qui est absent ou non dans une période donnée.

Projet réaliser lors du stage de BTS SIO 1er année, danns l'établissement Adeo Informatique.
Le projet possède une gestion d'utilisateurs, de groupes et de vacances, qui se visualise dans un calendrier.

### 1. Installation ###
    git clone https://github.com/RomainChardon/CalendrierVacance.git

### 2. Configuration de la base de donnée ###
Inscrire votre base de donnée dans le *.env*

### 2. Migration de la base de donnée ###
    php bin/console doctrine:migrations:migrate

### 3. Créer un administrateur  ###
Comme aucun utilisateur est inscrit dans la base de donnée, il faut créer un administrateur.

Rendez-vous sur le lien : ***votre_ip*/vacances/gestionGroupe/groupe**
Puis creer un groupe.

![screenGroupe](/src/screenREADME/groupe.png)


Rendez-vous sur le lien : ***votre_ip*/vacances/gestionUtilisateur/utilisateur**
Puis creer un utilisateur administrateur.

![screenUser](/src/screenREADME/user.png)

### 4. Ajout des droit d'accès  ###
Enlever les commentaire de la ligne 42 à 45 du fichier security.yaml

![screenSecu](/src/screenREADME/secu.png)

Technologie utilisée : Symfony 5.3.2, SQLite, langage PHP 8.0.6
