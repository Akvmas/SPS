<?php
require_once '../config.php';
require('../vendor/autoload.php');
ini_set('memory_limit', '500M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

global $pdo;

function clean_input($data) {
    return is_null($data) ? '' : htmlspecialchars(stripslashes(trim($data)));
}

function saveFormData($pdo)
{
    if (!$pdo) {
        die("L'objet PDO n'est pas disponible.");
    }
    $observationIds = [];
    // Traitement des données du chantier
    $chantier = clean_input($_POST['chantier']);
    $maitreOuvrage = clean_input($_POST['maitreOuvrage']);
    $maitreOeuvre = clean_input($_POST['maitreOeuvre']);
    $coordonnateurSPS = clean_input($_POST['coordonnateurSPS']);

    // Insertion des données de chantier dans la base de données
    $stmt = $pdo->prepare("INSERT INTO chantiers (description, maitreOuvrage, maitreOeuvre, coordonnateurSPS) VALUES (?, ?, ?, ?)");
    $stmt->execute([$chantier, $maitreOuvrage, $maitreOeuvre, $coordonnateurSPS]);
    $chantierId = $pdo->lastInsertId();

    // Traitement des personnes présentes
    if (isset($_POST['personne']) && is_array($_POST['personne'])) {
        foreach ($_POST['personne'] as $personne) {
            if (!empty($personne)) {
                $stmt = $pdo->prepare("INSERT INTO personnes_presentes (chantier_id, nom) VALUES (?, ?)");
                $stmt->execute([$chantierId, clean_input($personne)]);
            }
        }
    }

    // Traitement des observations
    $uploadedImages = [];
    $obsIndex = 1;
    while (isset($_POST['observation' . $obsIndex])) {
        $observation = clean_input($_POST['observation' . $obsIndex]);
        $dateObservation = $_POST['date' . $obsIndex];
        $heureObservation = $_POST['heure' . $obsIndex];
        $entreprise = clean_input($_POST['entreprise' . $obsIndex]);
        $effectif = clean_input($_POST['effectif' . $obsIndex]);
        $typeVisite = $_POST['typeVisite' . $obsIndex];
        $autreDescription = isset($_POST['autreDescription' . $obsIndex]) ? clean_input($_POST['autreDescription' . $obsIndex]) : null;

        // Insertion de l'observation dans la base de données
        $stmt = $pdo->prepare("INSERT INTO observations (chantier_id, texte, date, heure, entreprise, effectif, typeVisite, autreDescription) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$chantierId, $observation, $dateObservation, $heureObservation, $entreprise, $effectif, $typeVisite, $autreDescription]);
        $observationId = $pdo->lastInsertId();
        $observationIds[] = $observationId;
        // Traitement des images
        if (isset($_FILES['photos' . $obsIndex])) {
            foreach ($_FILES['photos' . $obsIndex]['tmp_name'] as $index => $tmpName) {
                if (file_exists($tmpName)) {
                    $uploadedImages[$obsIndex][] = $tmpName; // Stockez le chemin temporaire de l'image
                }
            }
        }

        $obsIndex++;
    }

    return ['chantierId' => $chantierId, 'observationIds' => $observationIds];
}

class MYPDF extends TCPDF
{
    public function Header()
    {
        $this->SetFont('helvetica', 'B', 20);
        $this->Image('../images/imgpreview.jpg', 10, 10, 33, 0, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->Cell(0, 15, "Fiche d'observation ou de notification ", 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

function generatePdf($postData, $chantierId, $observationIds, $uploadedImages)
    {
    global $pdo;
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Configuration initiale du PDF
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Votre Nom');
    $pdf->SetTitle('Détails du Formulaire');
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->AddPage();

    // En-tête du PDF
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Ln(10);
    $pdf->MultiCell(0, 10, 'Détails du Chantier', 0, 'C');
    $pdf->Ln(5);

    // Informations du chantier
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 0, 'Chantier: ' . clean_input($postData['chantier']), 0, 1, 'L');
    $pdf->Cell(0, 0, 'Maître d\'Ouvrage: ' . clean_input($postData['maitreOuvrage']), 0, 1, 'L');
    $pdf->Cell(0, 0, 'Maître d\'Œuvre: ' . clean_input($postData['maitreOeuvre']), 0, 1, 'L');
    $pdf->Cell(0, 0, 'Coordonnateur S.P.S.: ' . clean_input($postData['coordonnateurSPS']), 0, 1, 'L');
    $pdf->Ln(5);

    // Personnes présentes
    $pdf->MultiCell(0, 10, 'Personnes Présentes:', 0, 'L');
    $stmt = $pdo->prepare("SELECT nom FROM personnes_presentes WHERE chantier_id = ?");
    $stmt->execute([$chantierId]);
    $personnesPresentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($personnesPresentes as $nom) {
        $pdf->Cell(0, 0, clean_input($nom), 0, 1, 'L');
    }
    $pdf->Ln(5);
    $y = $pdf->GetY();
    $pdf->Line(10, $y, 200, $y);
    $pdf->Ln(2);

    if (!empty($postData['coordonnateurSPS'])) {
        $pdf->Cell(0, 0, 'Coordonnateur S.P.S.: ' . clean_input($postData['coordonnateurSPS']), 0, 1, '');
    }


    $y = $pdf->GetY();
    $pdf->Line(10, $y, 200, $y);
    $pdf->Ln(2);

    $obsIndex = 1;
    foreach ($observationIds as $obsIndex => $observationId) {
        $obsIndexAdjusted = $obsIndex + 1;
        $pdf->MultiCell(0, 10, 'Observation ' . $obsIndex . ': ' . clean_input($postData["observation{$obsIndexAdjusted}"]), 0, 'L');
        $pdf->Ln(5);

        $stmt = $pdo->prepare("SELECT image FROM observation_images WHERE observation_id = ?");
        $stmt->execute([$observationId]); 
        if (!empty($uploadedImages[$obsIndexAdjusted])) {
            foreach ($uploadedImages[$obsIndexAdjusted] as $imagePath) {
                if (file_exists($imagePath)) {
                    list($width, $height) = getimagesize($imagePath);
                    $ratio = $width / $height;
                    $maxHeight = 60;
                    $newWidth = $ratio * $maxHeight;
                    $pdf->Image($imagePath, '', '', $newWidth, $maxHeight, 'JPEG', '', 'T', false, 300, '', false, false, 1, false, false, false);
                    $pdf->Ln($maxHeight + 5);
                } else {
                    echo "Image introuvable: $imagePath";
                }
            }
        }

        if (!empty($postData["date{$obsIndexAdjusted}"])) {
            $pdf->Cell(0, 0, 'Date: ' . clean_input($postData["date{$obsIndexAdjusted}"]), 0, 1, '');
        }
        if (!empty($postData["heure{$obsIndexAdjusted}"])) {
            $pdf->Cell(0, 0, 'Heure: ' . clean_input($postData["heure{$obsIndexAdjusted}"]), 0, 1, '');
        }
        if (!empty($postData["typeVisite{$obsIndexAdjusted}"])) {
            $typeVisite = clean_input($postData["typeVisite{$obsIndexAdjusted}"]);
            $pdf->Cell(0, 0, 'Type de Visite: ' . $typeVisite, 0, 1, '');
        }
        if ($postData["typeVisite{$obsIndexAdjusted}"] === 'autre' && !empty($postData["autreDescription{$obsIndexAdjusted}"])) {
            $pdf->Cell(0, 0, 'Description: ' . clean_input($postData["autreDescription{$obsIndexAdjusted}"]), 0, 1, '');
        }
        if (!empty($postData["entreprise{$obsIndexAdjusted}"])) {
            $pdf->Cell(0, 0, 'Entreprise: ' . clean_input($postData["entreprise{$obsIndexAdjusted}"]), 0, 1, '');
        }
        if (!empty($postData["effectif{$obsIndexAdjusted}"])) {
            $pdf->Cell(0, 0, 'Effectif: ' . clean_input($postData["effectif{$obsIndexAdjusted}"]), 0, 1, '');
        }

        $pdf->Ln(10); 

        $obsIndex++;
    }


    $pdf->Ln();

    $texte = "Sans remarque de la part de l’entreprise dans un délai de 8 jours, les observations formulées par le Coordonnateur S.P.S. sont réputées acceptées sans réserve.";

    $pdf->MultiCell(0, 10, $texte, 0, 'L');
    $chantierName = preg_replace("/[^a-zA-Z0-9]/", "_", $postData['chantier']);
    $currentDate = date('Ymd');
    $pdfFilename = __DIR__ . "/../RenduPdf/{$chantierName}_{$currentDate}.pdf";
    $pdf->Output($pdfFilename, 'F');
    $pdf->SetPrintHeader(false);
    $pdf->SetPrintFooter(false);

    return $pdfFilename;
}

$result = saveFormData($pdo);
if ($result['chantierId']) {
    echo "Données enregistrées avec succés.<br>";
    $pdfFilename = generatePdf($_POST, $result['chantierId'], $result['observationIds'], $result['uploadedImages']);
    echo "PDF généré: " . $pdfFilename;
} else {
    die("Erreur lors de l'enregistrement des données.");
}

$_SESSION['pdfFilename'] = $pdfFilename;
header("Location: ../mail/pageMail.php?file=" . urlencode($nom_du_fichier));
die;
