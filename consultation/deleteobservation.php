<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: ../login.php");
    exit();
}
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chantier_id = $_POST['chantier_id'];
    $observation_number = $_POST['observationNumber'];

    $delete = $pdo->prepare("DELETE FROM observations WHERE chantier_id = :chantier_id AND observation_number = :observation_number");
    $delete->bindParam(':chantier_id', $chantier_id);
    $delete->bindParam(':observation_number', $observation_number);
    $delete->execute();


    echo json_encode([
        'status' => 'success',
        'message' => 'Observation supprimée avec succès.'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Méthode de requête incorrecte.'
    ]);
}
?>