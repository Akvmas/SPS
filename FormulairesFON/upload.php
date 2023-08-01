<?php
function saveFormData($postData, $filesData) {
    $targetDir = "../imageChantier/"; // Dossier où les photos seront stockées

    // vérifier et créer le répertoire d'upload si nécessaire
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    try {
        $db = new PDO('mysql:host=localhost;dbname=sps;charset=utf8', 'root', '');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        echo "Erreur : " . $e->getMessage();
        return false;
    }

    try {
        $db->beginTransaction();

        // préparation de la requête d'insertion
        $sql = "INSERT INTO FON (chantier, maitreOuvrage, maitreOeuvre, coordonnateurSPS, date, heure, typeVisite, autreDescription, coordonnateurSPS_copy, copie) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        
        $stmt->execute([
            $postData["chantier"],
            $postData["maitreOuvrage"],
            $postData["maitreOeuvre"],
            $postData["coordonnateurSPS"],
            $postData["date"],
            $postData["heure"],
            $postData["typeVisite"],
            $postData["autreDescription"],
            $postData["coordonnateurSPS_copy"],
            $postData["copie"]
        ]);

        $fonId = $db->lastInsertId();

        // insertions pour chaque personne
        foreach ($postData as $key => $value) {
            if (strpos($key, 'personne') !== false) {
                $sql = "INSERT INTO personnes (fon_id, personne) VALUES (?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$fonId, $value]);
            }
        }

        // insertions pour chaque observation
        $i = 1;
        while (isset($postData['observation' . $i])) {
            $sql = "INSERT INTO observations (fon_id, observation, entreprise, effectif) VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$fonId, $postData['observation' . $i], $postData['entreprise' . $i], $postData['effectif' . $i]]);
            $observationId = $db->lastInsertId();

            // gestion de l'upload de l'image
            if ($filesData['photo' . $i]["error"] == UPLOAD_ERR_OK) {
                $tmp_name = $filesData['photo' . $i]["tmp_name"];
                $name = $filesData['photo' . $i]["name"];
                move_uploaded_file($tmp_name, $targetDir . $name);

                $sql = "UPDATE observations SET photo = ? WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$targetDir . $name, $observationId]);
            }

            $i++;
        }

        $db->commit();
        return true;
    } catch(PDOException $e) {
        $db->rollBack();
        echo "Erreur : " . $e->getMessage();
        return false;
    }
}
?>
