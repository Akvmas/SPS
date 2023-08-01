<?php
// Inclure les autres fichiers requis
require 'upload.php';
require 'uploadmail.php';

// 1. Enregistrer les données du formulaire dans une base de données
$result = saveFormData($_POST, $_FILES); // Fonction définie dans upload.php

if(!$result) {
    // Gérer l'erreur
    die("Une erreur s'est produite lors de l'enregistrement des données du formulaire.");
}

// 2. Générer un PDF à partir des données du formulaire
$pdfFilename = generatePdf($_POST, $_FILES); // Fonction définie dans uploadmail.php

if(!$pdfFilename) {
    // Gérer l'erreur
    die("Une erreur s'est produite lors de la génération du PDF.");
}

// Stocker le nom du fichier PDF en session pour l'utiliser plus tard
session_start();
$_SESSION['pdfFilename'] = $pdfFilename;

// Rediriger l'utilisateur vers la page d'envoi de l'e-mail
header("Location: ../mail/pageMail.php");
exit;
?>
