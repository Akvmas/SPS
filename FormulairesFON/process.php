<?php
// Inclure les dépendances et configurations
include '../config.php';
require('../vendor/autoload.php');
ini_set('memory_limit', '256M');
session_start();

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

// Fonction pour obtenir les chemins des images pour une observation
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

// Fonction pour enregistrer les données du formulaire dans la base de données
function saveFormData($postData, $fileData) {
    global $pdo;

    $chantier = $postData['chantier'] ?? null;
    $date = date("Ymd");
    $maitreOuvrage = $postData['maitreOuvrage'] ?? null;
    $maitreOeuvre = $postData['maitreOeuvre'] ?? null;
    $coordonnateurSPS = $postData['coordonnateurSPS'] ?? null;
    $typeVisite = $postData['typeVisite'] ?? null;
    $autreDescription = $postData['autreDescription'] ?? null;

    $stmt = $pdo->prepare("INSERT INTO chantiers (description, maitreOuvrage, maitreOeuvre, coordonnateurSPS, typeVisite, autreDescription) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$chantier, $maitreOuvrage, $maitreOeuvre, $coordonnateurSPS, $typeVisite, $autreDescription]);

    $chantierId = $pdo->lastInsertId();

    $obsIndex = 1;
    $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
    $maxsize = 5 * 1024 * 1024;

    while (isset($postData['observation' . $obsIndex]) && !empty($postData['observation' . $obsIndex])) {
        $observation = $postData['observation' . $obsIndex];
        $entreprise = $postData['entreprise' . $obsIndex] ?? null;
        $effectif = $postData['effectif' . $obsIndex] ?? null;
        $dateObservation = $postData['date' . $obsIndex] ?? null;  // Get the date for the observation
        $heureObservation = $postData['heure' . $obsIndex] ?? null;  // Get the time for the observation
    
        if (isset($fileData['photos' . $obsIndex])) {
            foreach ($fileData['photos' . $obsIndex]['name'] as $key => $filename) {
                $filetype = $fileData['photos' . $obsIndex]['type'][$key];
                $filesize = $fileData['photos' . $obsIndex]['size'][$key];
    
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if (array_key_exists($ext, $allowed) && in_array($filetype, $allowed) && $filesize <= $maxsize) {
                    $photoContent = file_get_contents($fileData['photos' . $obsIndex]["tmp_name"][$key]);
                } else {
                    echo "Erreur lors du téléchargement de la photo " . $obsIndex . ".";
                }
    
                $stmt = $pdo->prepare("INSERT INTO observations (chantier_id, observation_number, texte, photo, entreprise, effectif, date, heure) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$chantierId, $obsIndex, $observation, $photoContent, $entreprise, $effectif, $dateObservation, $heureObservation]);
            }
        }
    
        $obsIndex++;
    }
    return $chantierId;
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
    
    // Add general data to PDF
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Ln(30);
    
    if (!empty($postData['chantier'])) {
        $pdf->Cell(0, 0, 'Chantier: ' . clean_input($postData['chantier']), 0, 1, '');
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
    
    if (!empty($postData['coordonnateurSPS'])) {
        $pdf->Cell(0, 0, 'Coordonnateur S.P.S.: ' . clean_input($postData['coordonnateurSPS']), 0, 1, '');
    }
    
    if (!empty($postData['date'])) {
        $pdf->Cell(0, 0, 'Date: ' . clean_input($postData['date']), 0, 1, '');
    }
    
    if (!empty($postData['heure'])) {
        $pdf->Cell(0, 0, 'Heure: ' . clean_input($postData['heure']), 0, 1, '');
    }
    
    if (!empty($postData['autreDescription'])) {
        $pdf->Cell(0, 0, 'Description: ' . clean_input($postData['autreDescription']), 0, 1, '');
    }
    
    $y = $pdf->GetY();
    $pdf->Line(10, $y, 200, $y);
    $pdf->Ln(2);
    
    $i = 1;
    while (isset($postData["observation{$i}"]) && !empty($postData["observation{$i}"])) {
        $pdf->Cell(0, 0, 'Observation N°' . $i . ': ' . clean_input($postData["observation{$i}"]), 0, 1, '');
        
        $imageFilePaths = getImagePathsForObservation($chantierId, $i);
    
        foreach ($imageFilePaths as $imageFilePath) {
            if ($imageFilePath) {
                $pdf->Image($imageFilePath, '', '', 0, 60, '', '', 'T', false, 300, '', false, false, 1, false, false, false);
                $pdf->Ln(65);
                unlink($imageFilePath); // Delete the temporary image after use
            }
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
    }

    $pdfFilename = __DIR__ . '/../RenduPdf/' . date('YmdHis') . '.pdf';
    $pdf->Output($pdfFilename, 'F');
    
    return $pdfFilename;
}

// Enregistrez les données du formulaire dans la base de données
$chantierId = saveFormData($_POST, $_FILES);
if ($chantierId) {
    echo "Données enregistrées avec succès.<br>";
} else {
    die("Erreur lors de l'enregistrement des données.");
}

// Générez le PDF
$pdfFilename = generatePdf($_POST, $chantierId);
echo "PDF généré: " . $pdfFilename;

// Stocker le nom du fichier PDF en session pour l'utiliser plus tard
$_SESSION['pdfFilename'] = $pdfFilename;

// Rediriger l'utilisateur vers la page d'envoi de l'e-mail
header("Location: ../mail/pageMail.php");
die;
?>
