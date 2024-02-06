<?php
include '../config.php';

$chantier_id = $_POST['chantier'];

$sql_chantier = "SELECT * FROM chantiers WHERE id=:chantier_id";
$stmt = $pdo->prepare($sql_chantier);
$stmt->execute(['chantier_id' => $chantier_id]);
$result_chantier = $stmt->fetch();
$sql_observations = "SELECT * FROM observations WHERE chantier_id=:chantier_id";
$stmt = $pdo->prepare($sql_observations);
$stmt->execute(['chantier_id' => $chantier_id]);
$result_observations = $stmt->fetchAll(PDO::FETCH_ASSOC);

session_start();
$_SESSION['chantier'] = $result_chantier;
$_SESSION['observations'] = $result_observations;

header("Location: FonConsultations.php?chantier_id=" . $chantier_id);
