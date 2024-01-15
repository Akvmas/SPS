<?php
include '../config.php';

$chantier_id = $_POST['chantier'];

// Extraction des données de 'chantiers' depuis la base de données
$sql_chantier = "SELECT * FROM chantiers WHERE id=:chantier_id";
$stmt = $pdo->prepare($sql_chantier);
$stmt->execute(['chantier_id' => $chantier_id]);
$result_chantier = $stmt->fetch();

// Extraction des données de 'personnes_presentes' depuis la base de données
$sql_personnes = "SELECT * FROM personnes_presentes WHERE chantier_id=:chantier_id";
$stmt = $pdo->prepare($sql_personnes);
$stmt->execute(['chantier_id' => $chantier_id]);
$result_personnes = $stmt->fetchAll();

// Extraction des données de 'observations' depuis la base de données
$sql_observations = "SELECT * FROM observations WHERE chantier_id=:chantier_id";
$stmt = $pdo->prepare($sql_observations);
$stmt->execute(['chantier_id' => $chantier_id]);
$result_observations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Enregistrement des résultats dans des variables de session pour les récupérer sur la page HTML
session_start();
$_SESSION['chantier'] = $result_chantier;
$_SESSION['personnes'] = $result_personnes;
$_SESSION['observations'] = $result_observations;

// Redirection vers la page du formulaire avec l'ID du chantier en paramètre d'URL
header("Location: FonConsultations.php?chantier_id=" . $chantier_id);
