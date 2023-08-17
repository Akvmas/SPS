<?php
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Vérifiez si le fichier a été uploadé sans erreur.
    if (isset($_FILES['photo1']) && $_FILES['photo1']['error'] == 0) {
        $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
        $filename = $_FILES['photo1']['name'];
        $filetype = $_FILES['photo1']['type'];
        $filesize = $_FILES['photo1']['size'];
    
        // Vérifiez l'extension du fichier
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            die("Erreur : Veuillez sélectionner un format de fichier valide.");
        }
    
        // Vérifiez la taille du fichier - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            die("Erreur : La taille du fichier est supérieure à la limite autorisée.");
        }
    
        // Vérifiez le type MIME du fichier
        if (in_array($filetype, $allowed)) {
            // Vérifiez si le fichier existe avant de le télécharger.
            if (file_exists("uploads/" . $_FILES["photo1"]["name"])) {
                echo $_FILES["photo1"]["name"] . " existe déjà.";
            } else {
                move_uploaded_file($_FILES["photo1"]["tmp_name"], "uploads/" . $_FILES["photo1"]["name"]);
                echo "Votre fichier a été téléchargé avec succès.";
            }
        } else {
            echo "Erreur: Il y a eu un problème lors de l'upload de votre fichier. Veuillez réessayer.";
        }
    } else {
        echo "Erreur: " . $_FILES["photo1"]["error"];
    }
    $chantier = $_POST['chantier'];
    $maitreOuvrage = $_POST['maitreOuvrage'];
    $maitreOeuvre = $_POST['maitreOeuvre'];
    $coordonnateurSPS = $_POST['coordonnateurSPS'];
    $date = $_POST['date'];
    $heure = $_POST['heure'];
    $typeVisite = $_POST['typeVisite'];
    $autreDescription = $_POST['autreDescription'] ?? null; // Si "autre" est choisi, sinon null
    $copie = $_POST['copie'];


    // Insertion dans la table Chantier
    $stmt = $pdo->prepare("INSERT INTO Chantier (description, maitreOuvrage, maitreOeuvre, coordonnateurSPS, date, heure, typeVisite, autreDescription) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$chantier, $maitreOuvrage, $maitreOeuvre, $coordonnateurSPS, $date, $heure, $typeVisite, $autreDescription]);

    $chantierId = $pdo->lastInsertId();  // Récupère l'ID du dernier chantier inséré

    // Insertion des personnes présentes
    $personIndex = 1;
    while(isset($_POST['personne' . $personIndex])) {
        $personne = $_POST['personne' . $personIndex];
        $stmt = $pdo->prepare("INSERT INTO PersonnePrésente (chantier_id, nom) VALUES (?, ?)");
        $stmt->execute([$chantierId, $personne]);
        $personIndex++;
    }

    // Insertion des observations
    $obsIndex = 1;
    while(isset($_POST['observation' . $obsIndex])) {
        $observation = $_POST['observation' . $obsIndex];
        $photo = $_FILES['photo' . $obsIndex]['name'];  // A traiter séparément pour sauvegarder le fichier sur le serveur
        $entreprise = $_POST['entreprise' . $obsIndex];
        $effectif = $_POST['effectif' . $obsIndex];
        $stmt = $pdo->prepare("INSERT INTO Observation (chantier_id, texte, photo, entreprise, effectif) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$chantierId, $observation, $photo, $entreprise, $effectif]);
        $obsIndex++;
    }
}
?>