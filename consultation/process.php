<?php
include '../config.php';
require('../vendor/autoload.php');
ini_set('memory_limit', '256M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
global $pdo;
$chantierId = $_POST['chantier_id'];


function updateFormData($postData, $fileData) {
    global $pdo;
    if (!isset($postData['chantier_id'])) {
        die("L'ID du chantier est manquant.");
    }
    $chantierId = $postData['chantier_id'];

    $stmt = $pdo->prepare("UPDATE chantiers SET description = ?, maitreOuvrage = ?, maitreOeuvre = ? WHERE id = ?");
    $stmt->execute([$postData['chantierNom'], $postData['maitreOuvrage'], $postData['maitreOeuvre'], $chantierId]);

    for ($obsIndex = 1; $obsIndex <= 8; $obsIndex++) {
        $photoColumnName = 'photo' . $obsIndex;
        $observationText = $postData['observation' . $obsIndex] ?? null;

        if (!empty($observationText)) {
            $entreprise = $postData['entreprise' . $obsIndex] ?? null;
            $effectif = $postData['effectif' . $obsIndex] ?? null;
            $typeVisite = isset($postData["typeVisite$obsIndex"]) ? $postData["typeVisite$obsIndex"] : null;
            $autreDescription = isset($postData["autreDescription$obsIndex"]) ? $postData["autreDescription$obsIndex"] : null;
            $date = isset($postData["date$obsIndex"]) ? $postData["date$obsIndex"] : null;
            $heure = isset($postData["heure$obsIndex"]) ? $postData["heure$obsIndex"] : null;
            $formattedDate = !empty($date) ? DateTime::createFromFormat('Y-m-d', $date)->format('Y-m-d') : null;

            $stmt_check = $pdo->prepare("SELECT observation_id FROM observations WHERE chantier_id = ? AND observation_number = ?");
            $stmt_check->execute([$chantierId, $obsIndex]);
            if ($row = $stmt_check->fetch()) {
                $observationId = $row['observation_id'];
                $stmt = $pdo->prepare("UPDATE observations SET texte = ?, entreprise = ?, effectif = ?, typeVisite = ?, autreDescription = ?, date = ?, heure = ? WHERE observation_id = ?");
                $stmt->execute([$observationText, $entreprise, $effectif, $typeVisite, $autreDescription, $formattedDate, $heure, $observationId]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO observations (chantier_id, texte, entreprise, effectif, observation_number, typeVisite, autreDescription, date, heure) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$chantierId, $observationText, $entreprise, $effectif, $obsIndex, $typeVisite, $autreDescription, $formattedDate, $heure]);
                $observationId = $pdo->lastInsertId();
            }
    
            $photoColumnName = 'photo' . $obsIndex;
            if (isset($fileData[$photoColumnName]) && is_array($fileData[$photoColumnName]['tmp_name'])) {
                foreach ($fileData[$photoColumnName]['tmp_name'] as $index => $fileTmpName) {
                    if ($fileData[$photoColumnName]['error'][$index] === UPLOAD_ERR_OK) {
                        if (is_uploaded_file($fileTmpName)) {
                            $photo = file_get_contents($fileTmpName);
                            $insertImgStmt = $pdo->prepare("INSERT INTO observation_images (observation_id, image) VALUES (?, ?)");
                            $insertImgStmt->execute([$observationId, $photo]);
                        }
                    }
                }
            }
        }
    
        return true;
    }
}

function clean_input($data)
{
    if ($data === null) {
        return '';
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function isValidImage($blob)
{
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->buffer($blob);
    return in_array($mime, $allowedMimes);
}

function getImagePathsForObservation($chantierId, $observationNumber)
{
    global $pdo;

    $obsStmt = $pdo->prepare("SELECT observation_id FROM observations WHERE chantier_id = ? AND observation_number = ?");
    $obsStmt->execute([$chantierId, $observationNumber]);
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

function getImageFromDatabase($observationId)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT image FROM observation_images WHERE observation_id = ?");
    $stmt->execute([$observationId]);

    return $stmt->fetchColumn();
}


class MYPDF extends TCPDF
{
    public function Header()
    {
        $this->SetFont('helvetica', 'B', 20);
        $this->Image('../images/imgpreview.jpg', 10, 10, 33, 0, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->Cell(0, 15, "Fiche d'observation ou de notification ", 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->SetFooterMargin(PDF_MARGIN_FOOTER);
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
function getObservationDetails($chantierId, $observationNumber)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM observations WHERE chantier_id = ? AND observation_number = ?");
    $stmt->execute([$chantierId, $observationNumber]);

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

function getImagesForObservation($observationId)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT image FROM observation_images WHERE observation_id = ?");
    $stmt->execute([$observationId]);

    $imagePaths = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $photoBlob = $row['image'];
        if (!$photoBlob) {
            echo "Erreur: Image BLOB vide pour l'observation $observationId.";
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
function generatePdf($postData, $chantierId)
{
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Your Name');
    $pdf->SetTitle('Form Details');
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Ln(30);

    if (!empty($postData['chantier'])) {
        $pdf->Cell(0, 0, 'Chantier: ' . clean_input($postData['chantier']), 0, 1, '');
        $pdf->Ln(5);
    }

    $pdf->SetFont('helvetica', '', 12);

    if (!empty($postData['maitreOuvrage'])) {
        $pdf->Cell(0, 0, 'Maitre d\'Ouvrage: ' . clean_input($postData['maitreOuvrage']), 0, 1, '');
    }

    if (!empty($postData['maitreOeuvre'])) {
        $pdf->Cell(0, 0, 'Maitre d\'Oeuvre: ' . clean_input($postData['maitreOeuvre']), 0, 1, '');
    }

    if (!empty($postData['coordonnateurSPS'])) {
        $pdf->Cell(0, 0, 'Coordonnateur S.P.S.: ' . clean_input($postData['coordonnateurSPS']), 0, 1, '');
    }

    $pdf->Ln(5);
    $y = $pdf->GetY();
    $pdf->Line(10, $y, 200, $y);
    $pdf->Ln(2);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 0, 'Personnes presentes:', 0, 1, '');
    $pdf->SetFont('helvetica', '', 12);

    $i = 1;
    while (isset($postData["personne{$i}"]) && !empty($postData["personne{$i}"])) {
        $pdf->Cell(0, 0, clean_input($postData["personne{$i}"]), 0, 1, '');
        $i++;
    }

    $pdf->Ln(5);
    $y = $pdf->GetY();
    $pdf->Line(10, $y, 200, $y);
    $pdf->Ln(2);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 0, 'Observations:', 0, 1, '');

    $observations = getObservationsForChantier($chantierId);
    foreach ($observations as $observation) {
        $pdf->MultiCell(0, 10, 'Observation ' . $observation['observation_number'] . ': ' . clean_input($observation['texte']), 0, 'L');

        $imageFilePaths = getImagesForObservation($observation['observation_id']);
        foreach ($imageFilePaths as $imageFilePath) {
            if (file_exists($imageFilePath)) {
                list($width, $height) = getimagesize($imageFilePath);
                $maxHeight = 60;
                $newWidth = ($maxHeight / $height) * $width;
                $margins = $pdf->getMargins();
                $pageWidth = 210 - $margins['left'] - $margins['right'];

                if ($newWidth > $pageWidth) {
                    $newWidth = $pageWidth;
                    $maxHeight = ($newWidth / $width) * $height;
                }

                $x = $margins['left'] + ($pageWidth - $newWidth) / 2;

                $pdf->Image($imageFilePath, $x, '', $newWidth, $maxHeight, '', '', 'T', false, 300, '', false, false, 1, false, false, false);
                $pdf->Ln($maxHeight + 5);
                unlink($imageFilePath);
            }
        }

        $fields = [
            'date' => 'Date',
            'heure' => 'Heure',
            'typeVisite' => 'Type de Visite',
            'autreDescription' => 'Description',
            'entreprise' => 'Entreprise',
            'effectif' => 'Effectif',
        ];

        foreach ($fields as $key => $label) {
            if (!empty($observation[$key])) {
                $pdf->Cell(0, 0, $label . ': ' . clean_input($observation[$key]), 0, 1, '');
            }
        }

        if ($observation["typeVisite"] === 'autre' && empty($observation["autreDescription"])) {
            $pdf->Cell(0, 0, 'Description: Non spécifé', 0, 1, '');
        }

        $pdf->Ln(5);
        $y = $pdf->GetY();
        $pdf->Line(10, $y, 200, $y);
        $pdf->Ln(2);
    }

    $chantierName = preg_replace("/[^a-zA-Z0-9]/", "_", $postData['chantierNom']);
    $currentDate = date('Ymd');
    $pdfFilename = __DIR__ . "/../RenduPdf/{$chantierName}_{$currentDate}.pdf";
    $pdf->Output($pdfFilename, 'F');

    return $pdfFilename;
}
$pdfFilename = generatePdf($_POST, $chantierId);
echo "PDF généré: " . $pdfFilename;

$result = updateFormData($_POST, $_FILES);
if (!$result) {
    die("Une erreur s'est produite lors de l'enregistrement des données du formulaire.");
}

error_log("Observation 2 Data: " . print_r($_POST['observation2'], true));
exit;