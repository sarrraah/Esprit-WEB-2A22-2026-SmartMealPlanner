<?php
require_once "../../controller/ProduitController.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $produit = new Produit(
        $_POST['nom'],
        $_POST['description'],
        $_POST['prix'],
        $_POST['quantiteStock'],
        isset($_POST['estDurable']) ? 1 : 0,
        $_POST['dateExpiration'],
        $_POST['image'],
        $_POST['statut'],
        $_POST['id_categorie']
    );

    $controller = new ProduitController();
    $controller->addProduit($produit);

    header("Location: listProduit.php");
}
?>

<h2>Ajouter Produit</h2>

<form method="POST">
    Nom: <input type="text" name="nom"><br>
    Description: <input type="text" name="description"><br>
    Prix: <input type="number" name="prix"><br>
    Stock: <input type="number" name="quantiteStock"><br>
    Durable: <input type="checkbox" name="estDurable"><br>
    Date Expiration: <input type="date" name="dateExpiration"><br>
    Image: <input type="text" name="image"><br>
    Statut: <input type="text" name="statut"><br>
    Categorie: <input type="number" name="id_categorie"><br>

    <button type="submit">Ajouter</button>
</form>