<?php
require('../vendor/autoload.php');

class MYPDF extends \TCPDF {
    // Page header
    public function Header() {
        $this->SetFont('helvetica', 'B', 20);
        $this->Cell(0, 15, 'Details du formulaire', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }
}

function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generatePdf($postData, $filesData) {
    // Create a new PDF document
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Your Name');
    $pdf->SetTitle('Form Details');

    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 12);

    // Collect and clean data
    $chantier = clean_input($_POST['chantier']);
    $maitreOuvrage = clean_input($_POST['maitreOuvrage']);
    $maitreOeuvre = clean_input($_POST['maitreOeuvre']);
    $coordonnateurSPS = clean_input($_POST['coordonnateurSPS']);
    $date = clean_input($_POST['date']);
    $heure = clean_input($_POST['heure']);
    $typeVisite = clean_input($_POST['typeVisite']);
    $autreDescription = isset($_POST['autreDescription']) ? clean_input($_POST['autreDescription']) : "";
    $coordonnateurSPS_copy = clean_input($_POST['coordonnateurSPS_copy']);
    $copie = clean_input($_POST['copie']);

    // Add data to PDF
    $pdf->Cell(0, 0, 'Chantier: '.$chantier, 0, 1, '');
    $pdf->Cell(0, 0, 'Maitre Ouvrage: '.$maitreOuvrage, 0, 1, '');
    $pdf->Cell(0, 0, 'Maitre Oeuvre: '.$maitreOeuvre, 0, 1, '');
    $pdf->Cell(0, 0, 'Coordonnateur S.P.S.: '.$coordonnateurSPS, 0, 1, '');
    $pdf->Cell(0, 0, 'Date: '.$date, 0, 1, '');
    $pdf->Cell(0, 0, 'Heure: '.$heure, 0, 1, '');
    $pdf->Cell(0, 0, 'Type de visite: '.$typeVisite, 0, 1, '');
    $pdf->Cell(0, 0, 'Description: '.$autreDescription, 0, 1, '');
    $pdf->Cell(0, 0, 'Le Coordonnateur S.P.S.: '.$coordonnateurSPS_copy, 0, 1, '');
    $pdf->Cell(0, 0, 'Copie à: '.$copie, 0, 1, '');

    // Handle dynamic input (e.g., persons, observations)
    $i = 1;
    while (isset($_POST["personne".$i])) {
        $personne = clean_input($_POST["personne".$i]);
        $pdf->Cell(0, 0, 'Personne '.$i.': '.$personne, 0, 1, '');
        $i++;
    }

    // Save the PDF
    $pdfFilename = __DIR__ . '/../RenduPdf/' . $chantier . '_' . $date . '.pdf';
    $pdf->Output($pdfFilename, 'F');

    return $pdfFilename;
}

// Appel de la fonction generatePdf
$pdfFilename = generatePdf($_POST, $_FILES);
?>
