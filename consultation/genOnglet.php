<?php
include '../config.php';
$chantier_id = $_POST['chantier_id'];

$query = $pdo->prepare("SELECT COUNT(*) FROM observations WHERE chantier_id = :chantier_id");
$query->bindParam(':chantier_id', $chantier_id);
$query->execute();
$count = $query->fetchColumn();
$num_observation = $count + 1;

$insert = $pdo->prepare("INSERT INTO observations (chantier_id, observation_number) VALUES (:chantier_id, :observation_number)");
$insert->bindParam(':chantier_id', $chantier_id);
$insert->bindParam(':observation_number', $num_observation);
$insert->execute();
echo json_encode([
    'status' => 'success',
    'message' => 'Nouvelle observation créée avec succès.',
    'observation_number' => $num_observation
]);
?>