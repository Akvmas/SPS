<?php
include '../config.php';

$chantier_id = $_POST['chantier'];

$sql_chantier = "SELECT * FROM chantiers WHERE id=:chantier_id";
$stmt = $pdo->prepare($sql_chantier);
$stmt->execute(['chantier_id' => $chantier_id]);
$result_chantier = $stmt->fetch();

$sql_personnes = "SELECT * FROM personnes_presentes WHERE chantier_id=:chantier_id";
$stmt = $pdo->prepare($sql_personnes);
$stmt->execute(['chantier_id' => $chantier_id]);
$result_personnes = $stmt->fetchAll();

$sql_observations = "SELECT * FROM observations WHERE chantier_id=:chantier_id";
$stmt = $pdo->prepare($sql_observations);
$stmt->execute(['chantier_id' => $chantier_id]);
$result_observations = $stmt->fetchAll(PDO::FETCH_ASSOC);

session_start();
$_SESSION['chantier'] = $result_chantier;
$_SESSION['personnes'] = $result_personnes;
$_SESSION['observations'] = $result_observations;

header("Location: FonConsultations.php?chantier_id=" . $chantier_id);
