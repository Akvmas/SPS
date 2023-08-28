<?php
include '../config.php';

function saveFormData($postData, $fileData) {
    global $pdo;

    if (!is_writable("../ImagesChantier/")) {
        die("Erreur: Le répertoire 'ImagesChantier' n'est pas accessible en écriture.");
    }
    
    $chantier = $postData['chantier']?? null;
    $date = date("Ymd");

    $maitreOuvrage = $postData['maitreOuvrage']?? null;
    $maitreOeuvre = $postData['maitreOeuvre']?? null;
    $coordonnateurSPS = $postData['coordonnateurSPS']?? null;
    $dateVisite = $postData['date']?? null;
    $heure = $postData['heure']?? null;
    $typeVisite = $postData['typeVisite'] ?? null;
    $autreDescription = $postData['autreDescription'] ?? null;

    $stmt = $pdo->prepare("INSERT INTO chantiers (description, maitreOuvrage, maitreOeuvre, coordonnateurSPS, date, heure, typeVisite, autreDescription) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$chantier, $maitreOuvrage, $maitreOeuvre, $coordonnateurSPS, $dateVisite, $heure, $typeVisite, $autreDescription]);

    $obsIndex = 1;
    $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
    $maxsize = 5 * 1024 * 1024;

    while (isset($postData['observation' . $obsIndex]) && !empty($postData['observation' . $obsIndex])) {
        $observation = $postData['observation' . $obsIndex];
        $entreprise = $postData['entreprise' . $obsIndex] ?? null;
        $effectif = $postData['effectif' . $obsIndex] ?? null;

        $photo = null;
        if (isset($fileData['photo' . $obsIndex]) && $fileData['photo' . $obsIndex]['error'] == 0) {
            $filename = $fileData['photo' . $obsIndex]['name'];
            $filetype = $fileData['photo' . $obsIndex]['type'];
            $filesize = $fileData['photo' . $obsIndex]['size'];

            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if (array_key_exists($ext, $allowed) && in_array($filetype, $allowed) && $filesize <= $maxsize) {

                $newFilename = $chantier . "_" . $date . "_observation" . $obsIndex . "." . $ext;
                if (file_exists("../ImagesChantier/" . $newFilename)) {
                    $newFilename = $chantier . "_" . $date . "_observation" . $obsIndex . "_" . time() . "." . $ext;
                }

                if (!move_uploaded_file($fileData['photo' . $obsIndex]["tmp_name"], "../ImagesChantier/" . $newFilename)) {
                    echo "Erreur lors du déplacement du fichier vers 'ImagesChantier/'.";
                } else {
                    $photo = $newFilename;
                }

            } else {
                echo "Erreur lors du téléchargement de la photo " . $obsIndex . ".";
            }
        } elseif (isset($fileData['photo' . $obsIndex])) {
            switch ($fileData['photo' . $obsIndex]['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    echo "La photo téléchargée est trop volumineuse.";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    echo "La photo a été partiellement téléchargée.";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    echo "Aucun fichier n'a été téléchargé.";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    echo "Il manque un dossier temporaire.";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    echo "Échec de l'écriture du fichier sur le disque.";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    echo "Une extension PHP a arrêté le téléchargement du fichier.";
                    break;
                default:
                    echo "Erreur de téléchargement inconnue.";
                    break;
            }
        }

        // Ajustement de la requête pour ne pas utiliser chantierId
        $stmt = $pdo->prepare("INSERT INTO observations (texte, photo, entreprise, effectif) VALUES (?, ?, ?, ?)");
        $stmt->execute([$observation, $photo, $entreprise, $effectif]);

        $obsIndex++;
    }
    return true;
}
?>
