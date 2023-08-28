<?php
require('../vendor/autoload.php');
require('../config.php');
ini_set('memory_limit', '256M');

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

function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function getImagePathForObservation($observationNumber) {
    $imageDirectory = realpath(dirname(__FILE__)) . '/../ImagesChantier/';
    $imagePath = $imageDirectory . "observation{$observationNumber}.jpg";

    return file_exists($imagePath) ? $imagePath : null;
}

function generatePdf($postData) {
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
        
        $imageFilePath = getImagePathForObservation($i);
    
        if ($imageFilePath) {
            $pdf->Image($imageFilePath, '', '', 0, 60, '', '', 'T', false, 300, '', false, false, 1, false, false, false);
            $pdf->Ln(65);
            unlink($imageFilePath); // Supprime l'image après utilisation
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

$pdfFilename = generatePdf($_POST);
echo "PDF généré: " . $pdfFilename;
?>
