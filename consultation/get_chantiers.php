<?php
include '../config.php';

$fon_id = $_POST['chantier'];

// Extraction des données de 'fon' depuis la base de données
$sql_fon = "SELECT * FROM fon WHERE id=:fon_id";
$stmt = $pdo->prepare($sql_fon);
$stmt->execute(['fon_id' => $fon_id]);
$result_fon = $stmt->fetch();

// Extraction des données de 'personnes' depuis la base de données
$sql_personnes = "SELECT * FROM personnes WHERE fon_id=:fon_id";
$stmt = $pdo->prepare($sql_personnes);
$stmt->execute(['fon_id' => $fon_id]);
$result_personnes = $stmt->fetchAll();

// Extraction des données de 'observations' depuis la base de données
$sql_observations = "SELECT * FROM observations WHERE fon_id=:fon_id";
$stmt = $pdo->prepare($sql_observations);
$stmt->execute(['fon_id' => $fon_id]);
$result_observations = $stmt->fetchAll();

// Enregistrement des résultats dans des variables de session pour les récupérer sur la page HTML
session_start();
$_SESSION['fon'] = $result_fon;
$_SESSION['personnes'] = $result_personnes;
$_SESSION['observations'] = $result_observations;

// Redirection vers la page du formulaire avec l'ID du chantier en paramètre d'URL
header("Location: FonConsultations.php?id=".$fon_id);
?>
