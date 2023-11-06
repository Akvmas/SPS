<?php
include '../config.php'; 

// Requête pour sélectionner les chantiers de la table 'chantiers'
$sql = "SELECT id, description FROM chantiers";
$stmt = $pdo->prepare($sql);
$stmt->execute();

// Récupérer tous les résultats
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Afficher chaque chantier sous forme d'option
foreach ($results as $row) {
    echo "<option value='" . $row["id"] . "'>" . $row["description"] . "</option>";
}
