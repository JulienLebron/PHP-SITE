<?php
require_once '../inc/init.inc.php';

//---------------------------------- TRAITEMENT PHP --------------------------------------------//
if(!internauteEstConnecteEtEstAdmin())
{
    header("location: ../connexion.php");
}
//---------------------------------- SUPPRESSION DU PRODUIT -----------------------------------//
if(isset($_GET['action']) && $_GET['action'] == "suppression")
{
    $resultat = executeRequete("SELECT * FROM produit WHERE id_produit = $_GET[id_produit]");
    $produit_a_supprimer = $resultat->fetch_assoc();
    $chemin_photo_a_supprimer = $_SERVER['DOCUMENT_ROOT'] . $produit_a_supprimer['photo'];
    if(!empty($produit_a_supprimer['photo']) && file_exists($chemin_photo_a_supprimer))
    {
        unlink($chemin_photo_a_supprimer);
    }
    executeRequete("DELETE FROM produit WHERE id_produit = $_GET[id_produit]");
    $contenu .= '<div class="alert alert-success text-center">Suppression du produit n° : ' . $_GET['id_produit'] . ' réalisé avec succès !</div>';
    $_GET['action'] = 'affichage';
}
//---------------------------------- ENREGISTREMENT DU PRODUIT --------------------------------//
if(!empty($_POST))
{
    debug($_POST);
    $photo_bdd = "";
    if(isset($_GET['action']) && $_GET['action'] == 'modification')
    {
        $photo_bdd = $_POST['photo_actuelle'];
    }
    if(!empty($_FILES['photo']['name']))
    {
        $nom_photo = $_POST['reference'] . '_' . $_FILES['photo']['name'];
        $photo_bdd = RACINE_SITE . "photo/$nom_photo";
        $photo_dossier = $_SERVER['DOCUMENT_ROOT'] . RACINE_SITE . "/photo/$nom_photo";
        // echo '<pre>'; print_r($_FILES); echo '</pre>';
        copy($_FILES['photo']['tmp_name'], $photo_dossier); 
    }
    foreach($_POST AS $indice => $valeur)
    {
        $_POST[$indice] = htmlentities(addslashes($valeur));
    }
    if(!empty($_POST['id_produit']))
    {
        executeRequete("REPLACE INTO produit (id_produit, reference, categorie, titre, description, couleur, taille, public, photo, prix, stock) VALUES ('$_POST[id_produit]', '$_POST[reference]', '$_POST[categorie]', '$_POST[titre]', '$_POST[description]', '$_POST[couleur]', '$_POST[taille]', '$_POST[public]', '$photo_bdd', '$_POST[prix]', '$_POST[stock]')");
    }
    $contenu .= '<div class="alert alert-success text-center">✅ Le produit à bien été enregistrer en base de données !</div>';
}
//---------------------------------- LIENS GESTION PRODUIT --------------------------------//
$contenu .= '<div class="container my-4"><div class="row text-center"><div class="col-md-6"><button type="button" class="btn btn-primary"><a href="?action=affichage">Affichage des produits</a></button></div><br>';
$contenu .= '<div class="col-md-6"><button type="button" class="btn btn-primary"><a href="?action=ajout">Ajouter un produit</a></button></div></div></div><br>';
//---------------------------------- AFFICHAGE TABLEAU PRODUIT --------------------------------//
if(isset($_GET['action']) && $_GET['action'] == "affichage")
{
    $resultat = executeRequete("SELECT * FROM produit");
    $contenu .= '<h2>Affichage des Produits</h2>';
    $contenu .= '<div class="alert alert-info">Nombre de produit(s) dans la boutique : ' . $resultat->num_rows .'</div>';

    $contenu .= '<table class="table table-bordered"><thead class="table-dark"><tr>';
    while($colonne = $resultat->fetch_field())
    {
        $contenu .= '<th style="text-align: center;">' . $colonne->name . '</th>';
    }
    $contenu .= '<th>Editer</th>';
    $contenu .= '<th>Supprimer</th>';
    $contenu .= '</tr></thead>';

    while($ligne = $resultat->fetch_assoc())
    {
        $contenu .= '<tbody><tr>';
        foreach($ligne AS $indice => $valeur)
        {
            if($indice == "photo")
            {
                $contenu .= '<td><img src="' . $valeur . '" class="img-gestion-produit"></td>';
            }
            else
            {
                $contenu .= '<td style="vertical-align: middle; text-align: center;">' . $valeur . '</td>';
            }
        }
        $contenu .= '<td class="text-center" style="vertical-align: middle;"><button class="btn btn-info"><a href="?action=modification&id_produit=' . $ligne['id_produit'] . '"><i class="far fa-edit"></i></a></button></td>';
        $contenu .= '<td class="text-center" style="vertical-align: middle;"><button class="btn btn-dark"><a href="?action=suppression&id_produit=' . $ligne['id_produit'] . '" Onclick="return(confirm(\'⚠ Vous êtes sur le point de supprimer ce produit. En êtes vous certain ?\'));"><i class="far fa-trash"></i></a></button></td>';
    }
    $contenu .= '</tbody></table><br><hr><br>';
}
//---------------------------------- AFFICHAGE HTML --------------------------------//
require_once '../inc/haut.inc.php';
echo $contenu;
// ici on regarde dans l'url si on a une action = ajout ou modification
if(isset($_GET['action']) && ($_GET['action'] == 'ajout' || $_GET['action'] == 'modification'))
{
    // ici on regarde si dans l'url on a un indice id_produit
    if(isset($_GET['id_produit']))
    {
        // pour pré compléter les champs du formulaire avec les informations du produit qu'on souhaite modifier on commence par récupérer les informations en bdd
        $resultat = executeRequete("SELECT * FROM produit WHERE id_produit = $_GET[id_produit]");
        $produit_actuel = $resultat->fetch_assoc();
        // debug($produit_actuel);
    }
    // ici on affiche le formulaire (si on créer un produit on affiche le formulaire avec les inputs vide sinon on affiche le formulaire avec les inputs contenant les informations du produit qu'on modifie)
    echo '

    <div class="jumbotron text-center mt-5">
        <h2>Gestion des produits</h2>
    </div>

    <form action="" method="post" enctype="multipart/form-data">

        <input type="hidden" id="id_produit" name="id_produit" value="'; if(isset($produit_actuel['id_produit'])) echo $produit_actuel['id_produit']; echo '">

        <div class="mb-3">
            <label for="reference" class="form-label">Référence</label>
            <input type="text" class="form-control" name="reference" id="reference" placeholder="🔑 La référence du produit" value="'; if(isset($produit_actuel['reference'])) echo $produit_actuel['reference']; echo '">
        </div>
        <div class="mb-3">
            <label for="categorie" class="form-label">Catégorie</label>
            <input type="text" class="form-control" name="categorie" id="categorie" placeholder="📑 La catégorie du produit" value="'; if(isset($produit_actuel['categorie'])) echo $produit_actuel['categorie']; echo '">
        </div>
        <div class="mb-3">
            <label for="titre" class="form-label">Titre</label>
            <input type="text" class="form-control" name="titre" id="titre" placeholder="💬 La titre du produit" value="'; if(isset($produit_actuel['titre'])) echo $produit_actuel['titre']; echo '">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" cols="30" rows="10" class="form-control" placeholder="💬 La description du produit">'; if(isset($produit_actuel['description'])) echo $produit_actuel['description']; echo '</textarea>
        </div>
        <div class="mb-3">
            <label for="couleur" class="form-label">Couleur</label>
            <input type="text" class="form-control" name="couleur" id="couleur" placeholder="🌈 La couleur du produit" value="'; if(isset($produit_actuel['couleur'])) echo $produit_actuel['couleur']; echo '">
        </div>
        <div class="mb-3">
            <label for="taille" class="form-label">Taille</label>
            <select name="taille" id="taille" class="form-select">
                <option selected>Choisir une taille</option>
                <option value="S"'; if(isset($produit_actuel) && $produit_actuel['taille'] == 'S') echo ' selected '; echo'>S</option>
                <option value="M"'; if(isset($produit_actuel) && $produit_actuel['taille'] == 'M') echo ' selected '; echo'>M</option>
                <option value="L"'; if(isset($produit_actuel) && $produit_actuel['taille'] == 'L') echo ' selected '; echo'>L</option>
                <option value="XL"'; if(isset($produit_actuel) && $produit_actuel['taille'] == 'XL') echo ' selected '; echo'>XL</option>
                <option value="XXL"'; if(isset($produit_actuel) && $produit_actuel['taille'] == 'XXL') echo ' selected '; echo'>XXL</option>
                <option value="XXXL"'; if(isset($produit_actuel) && $produit_actuel['taille'] == 'XXXL') echo ' selected '; echo'>XXXL</option>
                <option value="XXXXL"'; if(isset($produit_actuel) && $produit_actuel['taille'] == 'XXXXL') echo ' selected '; echo'>XXXXL</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="public" class="form-label">Public</label> <br>
            <input type="radio" name="public" value="m"'; if(isset($produit_actuel) && $produit_actuel['public'] == 'm') echo ' checked '; elseif(!isset($produit_actuel) && !isset($_POST['public'])) echo 'checked'; echo '>&nbsp; 🤵 Homme <br>
            <input type="radio" name="public" value="f"'; if(isset($produit_actuel) && $produit_actuel['public'] == 'f') echo ' checked '; echo '>&nbsp; 👩‍💼 Femme <br>
            <input type="radio" name="public" value="mixte"'; if(isset($produit_actuel) && $produit_actuel['public'] == 'mixte') echo ' checked '; echo '>&nbsp; 🤵👩‍💼 Mixte <br>
        </div>
        <div class="mb-3">
            <label for="photo" class="form-label">Photo</label>
            <input type="file" class="form-control" name="photo" id="photo">';
            if(isset($produit_actuel))
            {
                echo '<div class="alert alert-info">💬 Vous pouvez uploader une nouvelle photo si vous souhaitez la changer</div><br>';
                echo '<img src="' . $produit_actuel['photo'] . '" . width="100" height="100" class="img-gestion-produit-modif"><br>';
                echo '<input type="hidden" name="photo_actuelle" value="' . $produit_actuel['photo'] . '"><br>';
            }
            echo '
        </div>
        <div class="mb-3">
            <label for="prix" class="form-label">Prix</label>
            <input type="text" class="form-control" name="prix" id="prix" placeholder="💲💲💲 Le prix unitaire du produit" value="'; if(isset($produit_actuel['prix'])) echo $produit_actuel['prix']; echo '">
        </div>
        <div class="mb-3">
            <label for="stock" class="form-label">Stock</label>
            <input type="text" class="form-control" name="stock" id="stock" placeholder="🏭 Le stock disponible du produit" value="'; if(isset($produit_actuel['stock'])) echo $produit_actuel['stock']; echo '">
        </div>
        <div class="mb-3 text-center mt-5">
            <button class="btn btn-primary btn-lg">Enregistrer le produit ✅</button>
        </div>
    </form>';

}
 
require_once '../inc/bas.inc.php';