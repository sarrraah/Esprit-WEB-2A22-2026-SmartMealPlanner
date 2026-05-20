# Smart Meal Planner – Web Application

## Overview

This project was developed as part of the WEB – 2nd Year Engineering Program at **Esprit School of Engineering – Tunisia** (Academic Year 2025–2026).

Smart Meal Planner is an intelligent web application that helps users organize their nutrition by providing personalized meal planning, nutritional recommendations, and analysis of eating habits.

**Objective:** Help users adopt a healthier, more balanced, and responsible diet through intelligent meal planning.

**Problem Solved:** Many people struggle with choosing healthy meals, understanding nutritional values, organizing grocery shopping, and tracking their eating habits.

**Keywords:** `esprit-school-of-engineering` `academic-project` `esprit-web` `2025-2026` `php` `mysql` `web-development` `meal-planner`

---

## Features

### User Management
- User registration with email verification
- Secure authentication with hashed passwords
- Role-based access control (Client, Coach, Nutritionist, Admin)
- Profile management with picture upload
- Account activation, deactivation and reactivation
- Admin dashboard with user statistics and charts

### Meal Planning
- Personalized meal plan creation with goal setting (Lose Weight, Maintain Weight, Gain Muscle, Eat Healthy)
- Daily calorie target calculation based on objective
- Meal gallery with search and filtering by category (Breakfast, Lunch, Dinner, Snack, Low Calories)
- Daily meal suggestions with real-time calorie tracking
- Smart Meal Assistant (AI-powered chatbot) for nutrition guidance
- Meal type distribution charts and nutritional statistics
- Admin meal management (add, edit, delete meals with images and recipe URLs)

### Recipe Management
- Browse and search recipes by category or ingredient
- Detailed recipe pages with ingredients, steps, and nutritional info
- User ability to save favourite recipes
- Admin recipe management (add, edit, delete recipes)

### Shop
- Product catalogue with categories and filtering
- Product detail pages with descriptions and prices
- Shopping cart and order management
- Admin product management (add, edit, delete products)
- Order tracking and history

### Events Management
- Browse upcoming nutrition and wellness events
- Event detail pages with date, location, and description
- User event registration and booking
- Admin event management (create, edit, delete events)
- Event attendance tracking

---

## Tech Stack

### Frontend
- HTML
- CSS
- JavaScript

### Backend
- PHP
- MySQL
- PDO

---

## Architecture

The application is based on a two-part web architecture:

- **Front Office**: user-facing interface for end users (navigation, meal planning, recipes, shop, and events)
- **Back Office**: administration interface for managing users, meals, recipes, shop products, and events
- The Front Office communicates with the Back Office via HTTP requests

---

## Contributors

| Name | Module |
|------|--------|
| Sarah Skioui | *Gestion Utilisateurs* |
| Bakis Harrabi | *Gestion Meal Planner* |
| Rana Ben Abid | *Gestion Des Evenements* |
| Ryhem Hajji | *Gestion Shop* |
| Mootaz Ibn EL Hadj | *Gestion Des Recettes* |

---

## Academic Context

Developed at **Esprit School of Engineering – Tunisia**
Module: WEB | Class: 2A22 | Academic Year: 2025–2026

This project is part of the academic curriculum of **Esprit School of Engineering**, developed by 2nd year engineering students as part of the WEB module.

---

## Getting Started

### Prerequisites
- XAMPP (Apache + MySQL)
- A web browser

### Installation

1. Clone the repository:
```bash
git clone https://github.com/sarrraah/Esprit-WEB-2A22-2025-2026-SmartMealPlanner.git
cd Esprit-WEB-2A22-2025-2026-SmartMealPlanner
```

2. Place the project in your XAMPP `htdocs` folder and start **Apache** and **MySQL**

3. Open `phpMyAdmin`, create a database named `smart_meal_planner` and import the provided `.sql` file

4. Open `config.php` and set your database credentials

### Usage

1. Open your browser and go to `http://localhost/Esprit-WEB-2A22-2025-2026-SmartMealPlanner`
2. Register a new account or sign in
3. Take the interactive nutritional test
4. View your personalized meal recommendations and nutritional score
5. Admins can access the back office at `/view/back/` to manage users, meals, recipes, shop, and events

---

## Acknowledgments

We thank **Esprit School of Engineering** and our supervisors for their guidance throughout this academic project.