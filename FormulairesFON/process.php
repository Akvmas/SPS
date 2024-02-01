<?php
require_once '../config.php';
require('../vendor/autoload.php');
ini_set('memory_limit', '256M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

global $pdo;
$uploadedFiles = $_FILES['photos1'];

function clean_input($data)
{
    return is_null($data) ? '' : htmlspecialchars(stripslashes(trim($data)));
}

function saveFormData($pdo, $uploadedFiles)
{
    if (!$pdo) {
        die("L'objet PDO n'est pas disponible.");
    }
    $observationIds = [];
    $chantier = clean_input($_POST['chantier']);
    $maitreOuvrage = clean_input($_POST['maitreOuvrage']);
    $maitreOeuvre = clean_input($_POST['maitreOeuvre']);
    $coordonnateurSPS = clean_input($_POST['coordonnateurSPS']);

    $stmt = $pdo->prepare("INSERT INTO chantiers (description, maitreOuvrage, maitreOeuvre, coordonnateurSPS) VALUES (?, ?, ?, ?)");
    $stmt->execute([$chantier, $maitreOuvrage, $maitreOeuvre, $coordonnateurSPS]);
    $chantierId = $pdo->lastInsertId();

    if (isset($_POST['personne']) && is_array($_POST['personne'])) {
        foreach ($_POST['personne'] as $personne) {
            if (!empty($personne)) {
                $stmt = $pdo->prepare("INSERT INTO personnes_presentes (chantier_id, nom) VALUES (?, ?)");
                $stmt->execute([$chantierId, clean_input($personne)]);
            }
        }
    }

    $obsIndex = 1;
    while (isset($_POST['observation' . $obsIndex])) {
        $observation = clean_input($_POST['observation' . $obsIndex]);
        $dateObservation = $_POST['date' . $obsIndex];
        $heureObservation = $_POST['heure' . $obsIndex];
        $entreprise = clean_input($_POST['entreprise' . $obsIndex]);
        $effectif = clean_input($_POST['effectif' . $obsIndex]);
        $typeVisite = $_POST['typeVisite' . $obsIndex];
        $autreDescription = isset($_POST['autreDescription' . $obsIndex]) ? clean_input($_POST['autreDescription' . $obsIndex]) : null;

        $stmt = $pdo->prepare("INSERT INTO observations (chantier_id, texte, date, heure, entreprise, effectif,observation_number, typeVisite, autreDescription) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$chantierId, $observation, $dateObservation, $heureObservation, $entreprise, $effectif, $obsIndex, $typeVisite, $autreDescription]);
        $observationId = $pdo->lastInsertId();
        $observationIds[] = $observationId;

        if (isset($_FILES['photos1']) && is_array($_FILES['photos1']['tmp_name'])) {
            foreach ($_FILES['photos1']['tmp_name'] as $tmpName) {
                if (is_uploaded_file($tmpName)) {
                    $photoContent = file_get_contents($tmpName);
                    $stmt = $pdo->prepare("INSERT INTO observation_images (observation_id, image) VALUES (?, ?)");
                    $stmt->bindParam(1, $observationId, PDO::PARAM_INT);
                    $stmt->bindParam(2, $photoContent, PDO::PARAM_LOB);
                    $stmt->execute();
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

function generatePdf($postData, $chantierId, $observationIds)
{
    global $pdo;
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    define('MARGIN_LEFT', 15);
    define('MARGIN_TOP', 50);
    define('MARGIN_RIGHT', 15);
    define('MARGIN_HEADER', 5);
    define('MARGIN_FOOTER', 10);
    define('MARGIN_BOTTOM', 25);

    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('MONGARS Gael');
    $pdf->SetTitle('Détails du Chantier');

    $pdf->SetMargins(MARGIN_LEFT, MARGIN_TOP, MARGIN_RIGHT);
    $pdf->SetHeaderMargin(MARGIN_HEADER);
    $pdf->SetFooterMargin(MARGIN_FOOTER);
    $pdf->SetAutoPageBreak(TRUE, MARGIN_BOTTOM);

    $pdf->AddPage();

    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 0, 'Chantier : ' . clean_input($postData['chantier']), 0, 1, 'L');
    $pdf->Cell(0, 0, 'Maître d\'Ouvrage : ' . clean_input($postData['maitreOuvrage']), 0, 1, 'L');
    $pdf->Cell(0, 0, 'Maître d\'Œuvre : ' . clean_input($postData['maitreOeuvre']), 0, 1, 'L');
    $pdf->Ln(5);

    $pdf->MultiCell(0, 10, 'Personne(s) présente(s) :', 0, 'L');
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

    $obsNumber = 1;
    foreach ($observationIds as $observationId) {
        if ($pdf->GetY() + 120 > ($pdf->getPageHeight() - PDF_MARGIN_BOTTOM)) {
            $pdf->AddPage();
        }
        $pdf->MultiCell(0, 10, 'Observation ' . $obsNumber . ': ' . clean_input($postData["observation{$obsNumber}"]), 0, 'L');
        $pdf->Ln(5);
        $maxImageWidth = 100;
        $maxImageHeight = 40;
        $stmt = $pdo->prepare("SELECT image FROM observation_images WHERE observation_id = ?");
        $stmt->execute([$observationId]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $imageContent = $row['image'];
            $image = @imagecreatefromstring($imageContent);
            if ($image !== false) {
                $tempImagePath = tempnam(sys_get_temp_dir(), 'img');
                imagepng($image, $tempImagePath);
                list($width, $height) = getimagesize($tempImagePath);
                $ratio = min($maxImageWidth / $width, $maxImageHeight / $height);
                $newWidth = $width * $ratio;
                $newHeight = $height * $ratio;
                $pdf->Image($tempImagePath, '', '', $newWidth, $newHeight, 'PNG', '', 'T', false, 300, '', false, false, 1, false, false, false);
                $pdf->Ln($newHeight + 5); 
                unlink($tempImagePath); 
            }
        }

        if (!empty($postData["date{$obsNumber}"])) {
            $pdf->Cell(0, 0, 'Date : ' . clean_input($postData["date{$obsNumber}"]), 0, 1, '');
        }
        if (!empty($postData["heure{$obsNumber}"])) {
            $pdf->Cell(0, 0, 'Heure : ' . clean_input($postData["heure{$obsNumber}"]), 0, 1, '');
        }
        if (!empty($postData["typeVisite{$obsNumber}"])) {
            $typeVisite = clean_input($postData["typeVisite{$obsNumber}"]);
            $pdf->Cell(0, 0, 'Type de visite : ' . $typeVisite, 0, 1, '');
        }
        if ($postData["typeVisite{$obsNumber}"] === 'autre' && !empty($postData["autreDescription{$obsNumber}"])) {
            $pdf->Cell(0, 0, 'Description : ' . clean_input($postData["autreDescription{$obsNumber}"]), 0, 1, '');
        }
        if (!empty($postData["entreprise{$obsNumber}"])) {
            $pdf->Cell(0, 0, 'Entreprise : ' . clean_input($postData["entreprise{$obsNumber}"]), 0, 1, '');
        }
        if (!empty($postData["effectif{$obsNumber}"])) {
            $pdf->Cell(0, 0, 'Effectif : ' . clean_input($postData["effectif{$obsNumber}"]), 0, 1, '');
        }

        $pdf->Ln(10);

        $obsNumber++;
    }

    $pdf->Ln();

    $texte = "Sans remarque de la part de l’entreprise dans un délai de 8 jours, les observations formulées par le Coordonnateur S.P.S. sont réputées acceptées sans réserve.";
    $pdf->MultiCell(0, 10, $texte, 0, 'L');
    $pdf->Ln(20);
    $signatureImagePath = '../images/signature.png';
    $imageWidth = 40;
    $imageHeight = 20;
    $offsetX = 0;
    $pdf->Image($signatureImagePath, $pdf->GetX() - $offsetX, $pdf->GetY() - 10, $imageWidth, $imageHeight, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    $chantierName = preg_replace("/[^a-zA-Z0-9]/", "_", $postData['chantier']);
    $currentDate = date('Ymd');
    $pdfFilename = __DIR__ . "/../RenduPdf/{$chantierName}_{$currentDate}.pdf";
    $pdf->Output($pdfFilename, 'F');
    $pdf->SetPrintHeader(false);
    $pdf->SetPrintFooter(false);
    if (is_array($_FILES['photos1']['tmp_name'])) {
        foreach ($_FILES['photos1']['tmp_name'] as $index => $tmpName) {
            if (is_uploaded_file($tmpName)) {
                $imagePath = $_FILES['photos1']['tmp_name'][$index];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
        }
    }

    return $pdfFilename;
}

$result = saveFormData($pdo, $uploadedFiles);
if ($result['chantierId']) {
    echo "Données enregistrées avec succès.<br>";
    $pdfFilename = generatePdf($_POST, $result['chantierId'], $result['observationIds']);
    echo "PDF généré: " . $pdfFilename;
} else {
    die("Erreur lors de l'enregistrement des données.");
}
$_SESSION['pdfFilename'] = $pdfFilename;
header("Location: ../mail/pageMail.php?file=" . urlencode($pdfFilename));
die;
