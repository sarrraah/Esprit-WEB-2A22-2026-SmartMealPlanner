<?php
require_once "../../controller/ProduitController.php";

$controller = new ProduitController();

$id = $_GET['id'];
$produit = $controller->getProduitById($id);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $produit->setId($id);

    $produit = new Produit(
        $_POST['nom'],
        $_POST['description'],
        $_POST['prix'],
        $_POST['quantiteStock'],
        isset($_POST['estDurable']) ? 1 : 0,
        $_POST['dateExpiration'],
        $_POST['image'],
        $_POST['statut'],
        $_POST['id_categorie'],
        $id
    );

    $controller->updateProduit($produit);

    header("Location: listProduit.php");
}
?>

<h2>Modifier Produit</h2>

<form method="POST">
    Nom: <input type="text" name="nom" value="<?= $produit->getNom() ?>"><br>
    Description: <input type="text" name="description" value="<?= $produit->getDescription() ?>"><br>
    Prix: <input type="number" name="prix" value="<?= $produit->getPrix() ?>"><br>
    Stock: <input type="number" name="quantiteStock" value="<?= $produit->getQuantiteStock() ?>"><br>
    Date Expiration: <input type="date" name="dateExpiration" value="<?= $produit->getDateExpiration() ?>"><br>
    Image: <input type="text" name="image" value="<?= $produit->getImage() ?>"><br>
    Statut: <input type="text" name="statut" value="<?= $produit->getStatut() ?>"><br>
    Categorie: <input type="number" name="id_categorie" value="<?= $produit->getIdCategorie() ?>"><br>

    <button type="submit">Modifier</button>
</form>