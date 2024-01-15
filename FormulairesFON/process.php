<?php
include '../config.php';
require('../vendor/autoload.php');
ini_set('memory_limit', '500M');
error_reporting(E_ALL);
session_start();

global $pdo;

function clean_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
function getImagePathsForObservation($observationId)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT image FROM observation_images WHERE observation_id = ?");
    $stmt->execute([$observationId]);

    $imageFilePaths = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $imageContent = $row['image'];
        $tempFileName = tempnam(sys_get_temp_dir(), 'obs_img_') . '.jpg';
        file_put_contents($tempFileName, $imageContent);
        $imageFilePaths[] = $tempFileName;
    }
    return $imageFilePaths;
}

function isValidImage($blob)
{
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->buffer($blob);
    return in_array($mime, $allowedMimes);
}

function saveFormData($postData, $fileData)
{
    global $pdo;

    $chantier = $postData['chantier'] ?? null;
    $date = date("Ymd");
    $maitreOuvrage = $postData['maitreOuvrage'] ?? null;
    $maitreOeuvre = $postData['maitreOeuvre'] ?? null;
    $coordonnateurSPS = $postData['coordonnateurSPS'] ?? null;


    $stmt = $pdo->prepare("INSERT INTO chantiers (description, maitreOuvrage, maitreOeuvre, coordonnateurSPS) VALUES (?, ?, ?, ?)");
    $stmt->execute([$chantier, $maitreOuvrage, $maitreOeuvre, $coordonnateurSPS]);

    $chantierId = $pdo->lastInsertId();

    $obsIndex = 1;
    $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
    $maxsize = 5 * 1920 * 1080;

    while (isset($postData['observation' . $obsIndex]) && !empty($postData['observation' . $obsIndex])) {
        $observation = $postData['observation' . $obsIndex];
        $entreprise = $postData['entreprise' . $obsIndex] ?? null;
        $effectif = $postData['effectif' . $obsIndex] ?? null;
        $dateObservation = $postData['date' . $obsIndex] ?? null;
        $heureObservation = $postData['heure' . $obsIndex] ?? null;
        $typeVisite = $postData['typeVisite' . $obsIndex] ?? null;
        if ($typeVisite === 'autre') {
            $autreDescription = $postData['autreDescription' . $obsIndex] ?? null;
        } else {
            $autreDescription = null;
        }
        $stmt = $pdo->prepare("INSERT INTO observations (chantier_id, observation_number, texte, entreprise, effectif, date, heure, typeVisite, autreDescription) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$chantierId, $obsIndex, $observation, $entreprise, $effectif, $dateObservation, $heureObservation, $typeVisite, $autreDescription]);
        $observationId = $pdo->lastInsertId();

        if (isset($fileData['photos' . $obsIndex])) {
            foreach ($fileData['photos' . $obsIndex]['name'] as $key => $filename) {
                $filetype = $fileData['photos' . $obsIndex]['type'][$key];
                $filesize = $fileData['photos' . $obsIndex]['size'][$key];

                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if (array_key_exists($ext, $allowed) && in_array($filetype, $allowed) && $filesize <= $maxsize) {
                    $photoContent = file_get_contents($fileData['photos' . $obsIndex]["tmp_name"][$key]);
                } else {
                    echo "Erreur lors du téléchargement de la photo " . $obsIndex . ".";
                    continue;
                }

                $stmt = $pdo->prepare("INSERT INTO observation_images (observation_id, image) VALUES (?, ?)");
                $stmt->execute([$observationId, $photoContent]);
            }
        }

        $obsIndex++;
    }
    if (isset($_POST['personne']) && is_array($_POST['personne'])) {
        $personnes = $_POST['personne'];
        foreach ($personnes as $nom) {
            if (!empty($nom)) {
                $nom = htmlspecialchars($nom);

                $sql = "INSERT INTO personnes_presentes (chantier_id, nom) VALUES (?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$chantierId, $nom]);
            }
        }
    }
    return $chantierId;
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
    global $pdo;
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
        $pdf->MultiCell(0, 10, 'Chantier: ' . clean_input($postData['chantier']), 0, 'L');
        $pdf->Ln(5);
    }

    $pdf->SetFont('helvetica', '', 12);

    if (!empty($postData['maitreOuvrage'])) {
        $pdf->Cell(0, 0, 'Maitre Ouvrage: ' . clean_input($postData['maitreOuvrage']), 0, 1, '');
    }

    if (!empty($postData['maitreOeuvre'])) {
        $pdf->Cell(0, 0, 'Maitre Oeuvre: ' . clean_input($postData['maitreOeuvre']), 0, 1, '');
    }

    $pdf->Ln(5);
    $y = $pdf->GetY();
    $pdf->Line(10, $y, 200, $y);
    $pdf->Ln(2);

    $pdf->SetFont('helvetica', '', 12);

    $sql = "SELECT nom FROM personnes_presentes WHERE chantier_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$chantierId]);
    $personnesPresentes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($personnesPresentes as $nom) {
        $pdf->Cell(0, 0, 'Nom de la personne présente: ' . clean_input($nom), 0, 1, '');
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
        if (!empty($postData["date{$i}"])) {
            $pdf->Cell(0, 0, 'Date: ' . clean_input($postData["date{$i}"]), 0, 1, '');
        }

        if (!empty($postData["heure{$i}"])) {
            $pdf->Cell(0, 0, 'Heure: ' . clean_input($postData["heure{$i}"]), 0, 1, '');
        }
        if (!empty($postData["typeVisite{$i}"])) {
            $typeVisite = clean_input($postData["typeVisite{$i}"]);

            if (!strpos($typeVisite, ' ')) {
                $typeVisite = str_replace('visiteInopinee', 'visite Inopinee', $typeVisite);
            }

            $pdf->Cell(0, 0, 'Type de Visite: ' . $typeVisite, 0, 1, '');
        }


        if ($postData["typeVisite{$i}"] === 'autre' && !empty($postData["autreDescription{$i}"])) {
            $pdf->Cell(0, 0, 'Description: ' . clean_input($postData["autreDescription{$i}"]), 0, 1, '');
        }
        if (!empty($postData["entreprise{$i}"])) {
            $pdf->Cell(0, 0, 'Entreprise: ' . clean_input($postData["entreprise{$i}"]), 0, 1, '');
        }

        if (!empty($postData["effectif{$i}"])) {
            $pdf->Cell(0, 0, 'Effectif: ' . clean_input($postData["effectif{$i}"]), 0, 1, '');
        }


        $pdf->Ln(5);
        $y = $pdf->GetY();
        $pdf->Line(10, $y, 200, $y);
        $pdf->Ln(2);
        $i++;


        $pdf->Ln();

        $texte = "Sans remarque de la part de l’entreprise dans un délai de 8 jours, les observations formulées par le Coordonnateur S.P.S. sont réputées acceptées sans réserve.";

        $pdf->MultiCell(0, 10, $texte, 0, 'L');
    }
    $chantierName = preg_replace("/[^a-zA-Z0-9]/", "_", $postData['chantier']);
    $currentDate = date('Ymd');
    $pdfFilename = __DIR__ . "/../RenduPdf/{$chantierName}_{$currentDate}.pdf";
    $pdf->Output($pdfFilename, 'F');

    return $pdfFilename;
}

$chantierId = saveFormData($_POST, $_FILES);
if ($chantierId) {
    echo "Données enregistrées avec succés.<br>";
} else {
    die("Erreur lors de l'enregistrement des données.");
}

$pdfFilename = generatePdf($_POST, $chantierId);
echo "PDF généré: " . $pdfFilename;

$_SESSION['pdfFilename'] = $pdfFilename;
header("Location: ../mail/pageMail.php?file=" . urlencode($nom_du_fichier));
die;
