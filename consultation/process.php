<?php
include '../config.php';
require('../vendor/autoload.php');
ini_set('memory_limit', '256M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
global $pdo;
$chantierId = $_POST['chantier_id'];
$obsIndex = $_POST['observation_id'];
$obsNumber = $_GET['observation_number'];
error_log(print_r($_FILES, true));
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = processSingleObservation($obsNumber, $chantierId, $obsIndex, $_POST, $_FILES);
    if ($result) {
        echo "L'observation a été traitée avec succès.";
    } else {
        echo "Une erreur s'est produite lors du traitement de l'observation.";
    }
}
function processSingleObservation($obsNumber, $chantierId, $obsIndex, $postData, $fileData)
{
    global $pdo;
    $obsIndex = $postData['observation_id'];
    $observationText = clean_input($postData['observation' . $obsNumber]);
    $typeVisite = clean_input($postData['typeVisite' . $obsNumber]);
    $autreDescription = clean_input($postData['autreDescription'] ?? '');
    $date = clean_input($postData['date' . $obsNumber]);
    $heure = clean_input($postData['heure' . $obsNumber]);
    $entreprise = clean_input($postData['entreprise' . $obsNumber]);
    $effectif = clean_input($postData['effectif' . $obsNumber]);

    $existingObsStmt = $pdo->prepare("SELECT COUNT(*) FROM observations WHERE chantier_id = ? AND observation_number = ?");
    $existingObsStmt->execute([$chantierId, $obsNumber]);
    $observationExists = (bool) $existingObsStmt->fetchColumn();

    if ($observationExists) {
        $stmt = $pdo->prepare("UPDATE observations SET texte = ?, typeVisite = ?, autreDescription = ?, date = ?, heure = ?, entreprise = ?, effectif = ? WHERE chantier_id = ? AND observation_number = ?");
        $stmt->execute([$observationText, $typeVisite, $autreDescription, $date, $heure, $entreprise, $effectif, $chantierId, $obsNumber]);
        $obsID = $obsIndex;
    } else {
        $stmt = $pdo->prepare("INSERT INTO observations (chantier_id, observation_number, texte, typeVisite, autreDescription, date, heure, entreprise, effectif) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$chantierId, $obsNumber, $observationText, $typeVisite, $autreDescription, $date, $heure, $entreprise, $effectif]);
        $obsID = $pdo->lastInsertId();
    }
    if (isset($postData['personnesPresentes' . $obsNumber]) && !empty($postData['personnesPresentes' . $obsNumber])) {
        $personnesPresentes = explode("\n", str_replace("\r", "", $postData['personnesPresentes' . $obsNumber]));
        foreach ($personnesPresentes as $personne) {
            $personne = clean_input($personne);
            if (!empty($personne)) {
                $stmt = $pdo->prepare("INSERT INTO personnes_presentes (observation_id, nom) VALUES (?, ?)");
                $stmt->execute([$obsIndex, $personne]);
            }
        }
    }
    if (isset($fileData['photo']['tmp_name']) && is_array($fileData['photo']['tmp_name'])) {
        foreach ($fileData['photo']['tmp_name'] as $tmpName) {
            if ($tmpName && file_exists($tmpName)) {
                processImageForObservation($obsID, $tmpName);
            }
        }
    }
    return true;
}
function processImageForObservation($obsID, $fileTmpName)
{
    global $pdo;
    $photo = file_get_contents($fileTmpName);
    if ($photo === false) {
        echo "Impossible de lire le fichier image.";
        return;
    }
    if (!isValidImage($photo)) {
        echo "Fichier image non valide.";
        return;
    }
    $insertImgStmt = $pdo->prepare("INSERT INTO observation_images (observation_id, image) VALUES (?, ?)");
    $insertImgStmt->execute([$obsID, $photo]);
}
function clean_input($data)
{
    if (isset($data)) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
    }
    return $data;
}

function isValidImage($blob)
{
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->buffer($blob);
    return in_array($mime, $allowedMimes);
}

function getImagePathsForObservation($chantierId, $obsNumber)
{
    global $pdo;

    $obsStmt = $pdo->prepare("SELECT observation_id FROM observations WHERE chantier_id = ? AND observation_number = ?");
    $obsStmt->execute([$chantierId, $obsNumber]);
    $observation = $obsStmt->fetch();

    if ($observation) {
        $imgStmt = $pdo->prepare("SELECT image FROM observation_images WHERE observation_id = ?");
        $imgStmt->execute([$observation['observation_id']]);
        $photos = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        return [];
    }

    $imagePaths = [];
    foreach ($photos as $photoBlob) {
        if (!$photoBlob) {
            echo "Erreur: Image BLOB vide.";
            continue;
        }

        $imagePath = tempnam(sys_get_temp_dir(), 'obs');
        file_put_contents($imagePath, $photoBlob);
        $imagePaths[] = $imagePath;
    }

    return $imagePaths;
}

function getImageFromDatabase($obsIndex)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT image FROM observation_images WHERE observation_id = ?");
    $stmt->execute([$obsIndex]);

    return $stmt->fetchColumn();
}
function getObservationDetails($chantierId, $obsNumber)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM observations WHERE chantier_id = ? AND observation_number = ?");
    $stmt->execute([$chantierId, $obsNumber]);

    $observationDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($observationDetails) {
        return $observationDetails;
    } else {
        return null;
    }
}

function getImagesForChantier($chantierId)
{
    global $pdo;

    $obsStmt = $pdo->prepare("SELECT observation_id, observation_number FROM observations WHERE chantier_id = ?");
    $obsStmt->execute([$chantierId]);
    $observations = $obsStmt->fetchAll(PDO::FETCH_ASSOC);

    $imagesByObservation = [];

    foreach ($observations as $observation) {
        $imgStmt = $pdo->prepare("SELECT image FROM observation_images WHERE observation_id = ?");
        $imgStmt->execute([$observation['observation_id']]);
        $photos = $imgStmt->fetchAll(PDO::FETCH_COLUMN);

        $imagePaths = [];
        foreach ($photos as $photoBlob) {
            if (!$photoBlob) {
                echo "Erreur: Image BLOB vide pour l'observation " . $observation['observation_number'] . ".";
                continue;
            }

            $imagePath = tempnam(sys_get_temp_dir(), 'obs');
            file_put_contents($imagePath, $photoBlob);
            $imagePaths[] = $imagePath;
        }

        $imagesByObservation[$observation['observation_number']] = $imagePaths;
    }

    return $imagesByObservation;
}
function getObservationsForChantier($chantierId)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM observations WHERE chantier_id = ?");
    $stmt->execute([$chantierId]);

    $observations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $observations;
}

function getImagesForObservation($obsIndex)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT image FROM observation_images WHERE observation_id = ?");
    $stmt->execute([$obsIndex]);

    $imagePaths = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $photoBlob = $row['image'];
        if (!$photoBlob) {
            echo "Erreur: Image BLOB vide pour l'observation $obsIndex.";
            continue;
        }

        $imagePath = tempnam(sys_get_temp_dir(), 'obs');
        file_put_contents($imagePath, $photoBlob);
        $imagePaths[] = $imagePath;
    }

    return $imagePaths;
}
function getNombreObservations($chantierId)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM observations WHERE chantier_id = ?");
        $stmt->execute([$chantierId]);
        $nombreObservations = $stmt->fetchColumn();
        return $nombreObservations;
    } catch (Exception $e) {
        error_log("Erreur lors de la récupération du nombre d'observations: " . $e->getMessage());
        return 0;
    }
}

function getLastObservationForChantier($chantierId)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM observations WHERE chantier_id = ? ORDER BY observation_number DESC LIMIT 1");
    $stmt->execute([$chantierId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getChantierDetails($chantierId)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM chantiers WHERE id = ?");
    $stmt->execute([$chantierId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function displayObservationImages($pdf, $imageFilePaths)
{
    $x = 10;
    $y = $pdf->GetY() + 2;
    $maxHeight = 0;

    foreach ($imageFilePaths as $imageFilePath) {
        if (file_exists($imageFilePath)) {
            list($width, $height) = getimagesize($imageFilePath);
            $aspectRatio = $width / $height;
            $maxWidth = 180;
            $maxHeightImage = 60;
            $resizeWidth = $maxWidth;
            $resizeHeight = $resizeWidth / $aspectRatio;
            if ($resizeHeight > $maxHeightImage) {
                $resizeHeight = $maxHeightImage;
                $resizeWidth = $resizeHeight * $aspectRatio;
            }

            if ($x + $resizeWidth > ($pdf->GetPageWidth() - 10)) {
                $x = 10;
                $y += $maxHeight + 2;
                $maxHeight = 0;
            }

            $pdf->Image($imageFilePath, $x, $y, $resizeWidth, $resizeHeight, '', '', '', false, 300, '', false, false, 0, false, false, false);
            unlink($imageFilePath);

            $x += $resizeWidth + 2;
            $maxHeight = max($maxHeight, $resizeHeight);
        }
    }
    $pdf->SetY($y + $maxHeight + 10);
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
function generatePdf($postData, $chantierId, $obsIndex)
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
    $pdf->SetAuthor('MONGARS Gaël');
    $pdf->SetTitle("Rapport de fiche d'observation ou de notification");

    $pdf->SetMargins(MARGIN_LEFT, MARGIN_TOP, MARGIN_RIGHT);
    $pdf->SetHeaderMargin(MARGIN_HEADER);
    $pdf->SetFooterMargin(MARGIN_FOOTER);
    $pdf->SetAutoPageBreak(TRUE, MARGIN_BOTTOM);

    $pdf->AddPage();

    $chantierDetails = getChantierDetails($chantierId);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 0, 'Chantier : ' . clean_input($chantierDetails['description']), 0, 1, 'L');
    $pdf->Cell(0, 0, 'Maître d\'Ouvrage : ' . clean_input($chantierDetails['maitreOuvrage']), 0, 1, 'L');
    $pdf->Cell(0, 0, 'Maître d\'Œuvre : ' . clean_input($chantierDetails['maitreOeuvre']), 0, 1, 'L');
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 10);
    $label = 'Personne(s) présente(s) : ';
    $labelWidth = $pdf->GetStringWidth($label) + 2;
    $stmt = $pdo->prepare("SELECT nom FROM personnes_presentes WHERE observation_id = ?");
    $stmt->execute([$obsIndex]);
    $personnesPresentes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $nomsPersonnesPresentes = implode(', ', array_map('clean_input', $personnesPresentes));

    if (!empty($nomsPersonnesPresentes)) {
        $pdf->Cell($labelWidth, 10, $label, 0, false);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 10, $nomsPersonnesPresentes, 0, 1,'L');
    }

    $pdf->Ln(5);
    $y = $pdf->GetY();
    $pdf->Line(10, $y, 200, $y);
    $pdf->Ln(2);
    $texteSPS = "Gaël MONGARS";
    $pdf->Cell(0, 0, 'Coordonnateur S.P.S.: ' . $texteSPS , 0, 1, '');
    

    $y = $pdf->GetY();
    $pdf->Line(10, $y, 200, $y);
    $pdf->Ln(2);

    $lastObservation = getLastObservationForChantier($chantierId);

    if ($lastObservation) {

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 10, 'Observation N°' . $lastObservation['observation_number'] . ':', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->MultiCell(0, 10, clean_input($lastObservation['texte']), 0, 'L');
        displayObservationImages($pdf, getImagesForObservation($lastObservation['observation_id']));
        $pdf->Cell(0, 0, 'Date: ' . $lastObservation['date'], 0, 1, '');
        $pdf->Cell(0, 0, 'Heure: ' . $lastObservation['heure'], 0, 1, '');
        $pdf->Cell(0, 0, 'Type de Visite: ' . $lastObservation['typeVisite'], 0, 1, '');

        if (!empty($lastObservation['autreDescription'])) {
            $pdf->Cell(0, 0, 'Description: ' . $lastObservation['autreDescription'], 0, 1, '');
        }

        $pdf->Cell(0, 0, 'Entreprise: ' . $lastObservation['entreprise'], 0, 1, '');
        $pdf->Cell(0, 0, 'Effectif: ' . $lastObservation['effectif'], 0, 1, '');
    }
    $pdf->Ln(10);

    $pdf->Ln();
    $texte = "Sans remarque de la part de l’entreprise dans un délai de 8 jours, les observations formulées par le Coordonnateur S.P.S. sont réputées acceptées sans réserve.";
    $pdf->MultiCell(0, 10, $texte, 0, 'L');
    $pdf->Ln(20);
    $signatureImagePath = '../images/signature.png';
    $imageWidth = 40;
    $imageHeight = 20;
    $offsetX = 0;
    $pdf->Image($signatureImagePath, $pdf->GetX() - $offsetX, $pdf->GetY() - 10, $imageWidth, $imageHeight, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    $chantierName = preg_replace("/[^a-zA-Z0-9]/", "_", $chantierDetails['description']);
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
    return __DIR__ . "/../RenduPdf/" . $pdfFilename;
}

$chantierId = $_POST['chantier_id'] ?? 'DefaultChantierId';
$pdfFilename = generatePdf($_POST, $chantierId, $obsIndex);
echo "PDF généré: " . $pdfFilename;
header("Location: ../mail/pageMail.php?file=" . urlencode($pdfFilename));
die;
