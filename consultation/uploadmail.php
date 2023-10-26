<?php
require('../vendor/autoload.php');
require('../config.php');
ini_set('memory_limit', '256M');
session_start();
$chantier = $_SESSION['chantier'];
$personnes = $_SESSION['personnes'];
$observations = $_SESSION['observations'];

global $pdo;

// Fonction pour nettoyer les entrées
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fonction pour vérifier la validité de l'image
function isValidImage($blob) {
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->buffer($blob);
    return in_array($mime, $allowedMimes);
}
function getImagePathsForObservation($chantierId, $observationNumber) {
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
            echo "Erreur: Format d'image non valide ou non supporté.";
            continue;
        }

        $imagePath = tempnam(sys_get_temp_dir(), 'obs');
        file_put_contents($imagePath, $photoBlob);
        $imagePaths[] = $imagePath;
    }

    return $imagePaths;
}

function getImageFromDatabase($chantierId) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT photo FROM observations WHERE chantier_id = ?");
    $stmt->execute([$chantierId]);

    return $stmt->fetchColumn();
}

// Classe PDF personnalisée
class MYPDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 20);
        $this->Image('../images/imgpreview.jpg', 10, 10, 33, 0, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->Cell(0, 15, "Fiche d'observation ou de notification ", 0, false, 'C', 0, '', 0, false, 'M', 'M');        
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Fonction pour générer le PDF
function generatePdf($postData, $chantierId) {
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
$pdf->Cell(0, 0, 'Personnes présentes:', 0, 1, '');
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
        $pdf->Cell(0, 0, 'Observation N°' . $i . ': ' . clean_input($postData["observation{$i}"]), 0, 1, '');
        
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

        // Ajoutez les autres détails pour cette observation
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
            $pdf->Cell(0, 0, 'Description: Non spécifié', 0, 1, '');
        }

        $pdf->Ln(5);
        $y = $pdf->GetY();
        $pdf->Line(10, $y, 200, $y);
        $pdf->Ln(2);
        $i++;

    }
    $chantierName = preg_replace("/[^a-zA-Z0-9]/", "_", $postData['chantierNom']); // Remplacez les caractères non alphanumériques par des underscores pour éviter les problèmes de nom de fichier
    $currentDate = date('Ymd');
    $pdfFilename = __DIR__ . "/../RenduPdf/{$chantierName}_{$currentDate}.pdf";
    $pdf->Output($pdfFilename, 'F');
    
    return $pdfFilename;
}
// Générez le PDF
$pdfFilename = generatePdf($_POST, $chantierId);
echo "PDF généré: " . $pdfFilename;