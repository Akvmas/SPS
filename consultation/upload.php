<?php
include '../config.php';

function updateFormData($postData, $fileData) {
    global $pdo;

    $chantierId = $postData['chantier_id'];
    $chantierNom = $postData['description'];  // Assurez-vous que cette clé est correcte.
    $date = date("Ymd"); // Utilisez la date actuelle comme format "YYYYMMDD".
    
    $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
    $maxsize = 5 * 1024 * 1024;
    $obsIndex = 1;

    while (isset($postData['observationText' . $obsIndex]) && !empty($postData['observationText' . $obsIndex])) {
        $observation = $postData['observationText' . $obsIndex];

        // Vérification si l'observation existe déjà
        $stmt_check = $pdo->prepare("SELECT * FROM Observations WHERE chantier_id = ? AND texte = ?");
        $stmt_check->execute([$chantierId, $observation]);

        if (!$stmt_check->fetch()) {
            $entreprise = $postData['entreprise' . $obsIndex] ?? null;
            $effectif = $postData['effectif' . $obsIndex] ?? null;

            $photo = null;
            if (isset($fileData['photo' . $obsIndex]) && $fileData['photo' . $obsIndex]['error'] == 0) {
                $filename = $fileData['photo' . $obsIndex]['name'];
                $filetype = $fileData['photo' . $obsIndex]['type'];
                $filesize = $fileData['photo' . $obsIndex]['size'];

                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if (array_key_exists($ext, $allowed) && in_array($filetype, $allowed) && $filesize <= $maxsize) {
                    
                    // Générer un nom en fonction du chantier, de la date et de l'indice de l'observation pour l'image
                    $newFilename = $chantierNom . "_" . $date . "_observation" . $obsIndex . "." . $ext;

                    // Vérifiez si le fichier existe déjà. Si c'est le cas, ajoutez un horodatage pour le rendre unique.
                    if (file_exists("../ImagesChantier/" . $newFilename)) {
                        $newFilename = $chantierNom . "_" . $date . "_observation" . $obsIndex . "_" . time() . "." . $ext;
                    }
                    
                    move_uploaded_file($fileData['photo' . $obsIndex]["tmp_name"], "../ImagesChantier/" . $newFilename);
                    $photo = $newFilename;  // Utilisez le nouveau nom de fichier pour enregistrer dans la base de données
                } else {
                    echo "Erreur lors du téléchargement de la photo " . $obsIndex . ".";
                }
            }

            $stmt = $pdo->prepare("INSERT INTO Observations (chantier_id, texte, photo, entreprise, effectif) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$chantierId, $observation, $photo, $entreprise, $effectif]);
        }
        $obsIndex++;
    }

    return true;  // Retourne true si tout s'est bien passé
}
?>
