<?php
include '../config.php';
require('../vendor/autoload.php');
ini_set('memory_limit', '256M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
global $pdo;
$chantierId = $_POST['chantier_id'];


function updateFormData($postData, $fileData)
{
    global $pdo;
    if (!isset($postData['chantier_id'])) {
        die("L'ID du chantier est manquant.");
    }
    $chantierId = $postData['chantier_id'];

    // Mise Ã  jour des dÃ©tails du chantier
    $stmt = $pdo->prepare("UPDATE chantiers SET description = ?, maitreOuvrage = ?, maitreOeuvre = ? WHERE id = ?");
    $stmt->execute([$postData['chantierNom'], $postData['maitreOuvrage'], $postData['maitreOeuvre'], $chantierId]);

    // Gestion des observations
    for ($obsIndex = 1; $obsIndex <= 3; $obsIndex++) {
        $observationText = $postData['observation' . $obsIndex] ?? null;
        $entreprise = $postData['entreprise' . $obsIndex] ?? null;
        $effectif = $postData['effectif' . $obsIndex] ?? null;
        $typeVisite = $postData['typeVisite' . $obsIndex] ?? null;
        $autreDescription = $postData['autreDescription' . $obsIndex] ?? null;
        $date = $postData['date' . $obsIndex] ?? null;
        $formattedDate = null; // Initialiser à null

        // Valider la date seulement si elle n'est pas vide
        if (!empty($date)) {
            $dateObject = DateTime::createFromFormat('Y-m-d', $date);
            if ($dateObject) {
                $formattedDate = $dateObject->format('Y-m-d');
            } else {
                die("Le format de la date pour l'observation $obsIndex est invalide.");
            }
        }
        $heure = $postData['heure' . $obsIndex] ?? null;

        // VÃ©rification si l'observation existe dÃ©jÃ 
        $stmt_check = $pdo->prepare("SELECT * FROM observations WHERE chantier_id = ? AND observation_number = ?");
        $stmt_check->execute([$chantierId, $obsIndex]);

        $photo = null;
        $photoColumnName = 'photo' . $obsIndex;
        if (isset($fileData[$photoColumnName]) && $fileData[$photoColumnName]['error'] == 0) {
            // Nouvelle photo fournie, elle remplacera l'ancienne
            $photo = file_get_contents($fileData[$photoColumnName]['tmp_name']);
        } else {
            // Aucune nouvelle photo fournie, nous garderons l'ancienne
            if ($row = $stmt_check->fetch()) {
                $photo = $row['photo']; // Conserver la photo existante
            }
        }

        if ($row = $stmt_check->fetch()) {
            // Mise à jour de l'observation existante
            $stmt = $pdo->prepare("UPDATE observations SET texte = COALESCE(?, texte), photo = COALESCE(?, photo), entreprise = COALESCE(?, entreprise), effectif = COALESCE(?, effectif), typeVisite = COALESCE(?, typeVisite), autreDescription = COALESCE(?, autreDescription), date = COALESCE(?, date), heure = COALESCE(?, heure) WHERE chantier_id = ? AND observation_number = ?");
            $stmt->execute([$observationText, $photo, $entreprise, $effectif, $typeVisite, $autreDescription, $formattedDate, $heure, $chantierId, $obsIndex]);
        } else {
            // Insertion d'une nouvelle observation
            $stmt = $pdo->prepare("INSERT INTO observations (chantier_id, texte, photo, entreprise, effectif, observation_number, typeVisite, autreDescription, date, heure) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$chantierId, $observationText, $photo, $entreprise, $effectif, $obsIndex, $typeVisite, $autreDescription, $formattedDate, $heure]);
        }
    }

    return true;
}

function clean_input($data)
{
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

    $stmt = $pdo->prepare("SELECT photo FROM observations WHERE chantier_id = ? AND observation_number = ?");
    $stmt->execute([$chantierId, $observationNumber]);

    $photos = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $imagePaths = [];

    foreach ($photos as $photoBlob) {
        if (!$photoBlob) {
            echo "Erreur: Image BLOB vide.";
            continue;
        }

        if (strlen($photoBlob) > (5 * 1024 * 1024)) { // 5MB
            echo "Erreur: Image trop volumineuse.";
            continue;
        }

        if (!isValidImage($photoBlob)) {
            echo "Erreur: Format d'image non valide ou non supportÃ©.";
            continue;
        }

        $imagePath = tempnam(sys_get_temp_dir(), 'obs');
        file_put_contents($imagePath, $photoBlob);
        $imagePaths[] = $imagePath;
    }

    return $imagePaths;
}

function getImageFromDatabase($chantierId)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT photo FROM observations WHERE chantier_id = ?");
    $stmt->execute([$chantierId]);

    return $stmt->fetchColumn();
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

    // Add persons
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 0, 'Personnes prÃ©sentes:', 0, 1, '');
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

    // Add observations
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 0, 'Observations:', 0, 1, '');

    $i = 1;
    while (isset($postData["observation{$i}"]) && !empty($postData["observation{$i}"])) {
        $pdf->MultiCell(0, 10, 'Observation ' . $i . ': ' . clean_input($postData["observation{$i}"]), 0, 'L');

        $imageFilePaths = getImagePathsForObservation($chantierId, $i);

        foreach ($imageFilePaths as $imageFilePath) {
            if ($imageFilePath) {
                $maxHeight = 60;
                list($width, $height) = getimagesize($imageFilePath);
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

        // Ajoutez les autres dÃ©tails pour cette observation
        $fields = [
            'date' => 'Date',
            'heure' => 'Heure',
            'typeVisite' => 'Type de Visite',
            'autreDescription' => 'Description',
            'entreprise' => 'Entreprise',
            'effectif' => 'Effectif',
        ];

        foreach ($fields as $key => $label) {
            if (!empty($postData["{$key}{$i}"])) {
                $pdf->Cell(0, 0, $label . ': ' . clean_input($postData["{$key}{$i}"]), 0, 1, '');
            }
        }

        if ($postData["typeVisite{$i}"] === 'autre' && empty($postData["autreDescription{$i}"])) {
            $pdf->Cell(0, 0, 'Description: Non spÃ©cifiÃ©', 0, 1, '');
        }

        $pdf->Ln(5);
        $y = $pdf->GetY();
        $pdf->Line(10, $y, 200, $y);
        $pdf->Ln(2);
        $i++;
    }
    $chantierName = preg_replace("/[^a-zA-Z0-9]/", "_", $postData['chantierNom']); // Remplacez les caractÃ¨res non alphanumÃ©riques par des underscores pour Ã©viter les problÃ¨mes de nom de fichier
    $currentDate = date('Ymd');
    $pdfFilename = __DIR__ . "/../RenduPdf/{$chantierName}_{$currentDate}.pdf";
    $pdf->Output($pdfFilename, 'F');

    return $pdfFilename;
}

// GÃ©nÃ©rez le PDF
$pdfFilename = generatePdf($_POST, $chantierId);
echo "PDF gÃ©nÃ©rÃ©: " . $pdfFilename;

// Logique principale
$result = updateFormData($_POST, $_FILES);
if (!$result) {
    die("Une erreur s'est produite lors de l'enregistrement des données du formulaire.");
}

// Redirection vers la page de succès avec le chemin du PDF généré
header("Location: ../mail/pageMail.php?pdf=" . urlencode($pdfFilename));
exit;
