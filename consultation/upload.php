<?php
include '../config.php';

function updateFormData($postData, $fileData) {
    global $pdo;

    $chantierId = $postData['chantier_id'];

    // Mise à jour des détails du chantier
    $stmt = $pdo->prepare("UPDATE chantiers SET description = ?, maitreOuvrage = ?, maitreOeuvre = ? WHERE id = ?");
    $stmt->execute([$postData['chantier'], $postData['maitreOuvrage'], $postData['maitreOeuvre'], $chantierId]);

    // Gestion des observations
    for ($obsIndex = 1; $obsIndex <= 3; $obsIndex++) {
        $observationText = $postData['observation' . $obsIndex] ?? null;
        $entreprise = $postData['entreprise' . $obsIndex] ?? null;
        $effectif = $postData['effectif' . $obsIndex] ?? null;
        $typeVisite = $postData['typeVisite' . $obsIndex] ?? null;
        $autreDescription = $postData['autreDescription' . $obsIndex] ?? null;
        $date = $postData['date' . $obsIndex] ?? null;
        $heure = $postData['heure' . $obsIndex] ?? null;

        // Vérification si l'observation existe déjà
        $stmt_check = $pdo->prepare("SELECT * FROM Observations WHERE chantier_id = ? AND observation_number = ?");
        $stmt_check->execute([$chantierId, $obsIndex]);

        $photo = null;
        if (isset($fileData['photos' . $obsIndex]) && $fileData['photos' . $obsIndex]['error'] == 0) {
            $photo = file_get_contents($fileData['photos' . $obsIndex]['tmp_name']);
        }

        if ($row = $stmt_check->fetch()) {
            // Mise à jour de l'observation existante
            $stmt = $pdo->prepare("UPDATE Observations SET texte = ?, photo = ?, entreprise = ?, effectif = ?, typeVisite = ?, autreDescription = ?, date = ?, heure = ? WHERE observation_id = ?");
            $stmt->execute([$observationText, $photo, $entreprise, $effectif, $typeVisite, $autreDescription, $date, $heure, $row['observation_id']]);
        } else {
            // Insertion d'une nouvelle observation
            $stmt = $pdo->prepare("INSERT INTO Observations (chantier_id, texte, photo, entreprise, effectif, observation_number, typeVisite, autreDescription, date, heure) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$chantierId, $observationText, $photo, $entreprise, $effectif, $obsIndex, $typeVisite, $autreDescription, $date, $heure]);
        }
    }

    return true;  // Retourne true si tout s'est bien passé
}

?>
