# Smart Meal Planner – Web Application

## Overview
This project was developed as part of the WEB – 2nd Year Engineering Program at **Esprit School of Engineering – Tunisia** (Academic Year 2025–2026).

Smart Meal Planner is an intelligent web application that helps users organize their nutrition by providing personalized meal planning, nutritional recommendations, and analysis of eating habits.

## Features

### Objectif
L’objectif de ce projet est d’aider les utilisateurs à adopter une alimentation plus saine, équilibrée et responsable grâce à une planification intelligente des repas.

### Problème résolu
De nombreuses personnes rencontrent des difficultés à :
- choisir des repas sains
- comprendre la valeur nutritionnelle de leurs repas
- organiser leurs courses
- suivre leurs habitudes alimentaires
- adopter un mode de consommation durable

### Fonctionnalités principales

#### Front Office
- Interface utilisateur intuitive avec présentation du site
- Test nutritionnel interactif
- Résultats intelligents avec score nutritionnel
- Recommandations personnalisées
- Score écologique des repas
- Statistiques et visualisation des habitudes alimentaires
- Génération automatique d’une liste de courses

#### Back Office
- Gestion des utilisateurs
- Gestion des repas et repas
- Gestion des aliments durables
- Tableau de bord avec statistiques
- Gestion du contenu nutritionnel

#### Catégories de Repas
Les repas sont classés dans la table `categorie_repas` de la base de données afin de faciliter la recherche et la gestion.

Catégories par défaut :
- Entrée
- Plat principal
- Dessert
- Boisson

## Tech Stack

### Frontend
- HTML
- CSS
- JavaScript

### Backend
Le backend sera développé ultérieurement pour gérer :
- la logique métier
- le stockage et la gestion des données
- l’authentification des utilisateurs
- la communication avec le frontend via des API


## Architecture
L’application repose sur une architecture web en deux parties :

- **Front Office** : interface utilisateur destinée aux utilisateurs finaux (navigation, test nutritionnel, affichage des résultats)
- **Back Office** : interface d’administration permettant la gestion des utilisateurs, des repas et des données nutritionnelles
- Le Front Office communique avec le Back Office via des requêtes HTTP (API).


## Contributors
- Sarah Skioui
- Bakis Harrabi
- Rana Ben Abid
- Ryhem Hajji
- Mootaz Ibn EL Hadj


## Academic Context
Developed at **Esprit School of Engineering – Tunisia**  
Module : WEB  
Classe : 2A22
Année universitaire : 2025–2026

## Getting Started

## Installation

1. Cloner le dépôt :

```
git clone https://github.com/sarrraah/Esprit-WEB-2A22-2025-2026-SmartMealPlanner.git
cd Esprit-WEB-2A22-2025-2026-SmartMealPlanner
```

## Utilisation
1. Ouvrez `view/front/repas.php` pour afficher et supprimer les repas côté utilisateur.
2. Ouvrez `view/front/add_repas.php` pour ajouter un nouveau repas.
3. Ouvrez `view/back/repas.php` pour gérer les repas depuis le back office.
4. Utilisez `controller/RepasController.php` pour les opérations de création, mise à jour et suppression.


## Acknowledgments
Nous remercions **Esprit School of Engineering** et nos encadrants pour leur accompagnement dans ce projet académique.
