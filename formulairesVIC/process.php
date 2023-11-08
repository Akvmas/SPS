<?php
require('../vendor/autoload.php');
error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED & ~E_USER_DEPRECATED);

function formatMultiValueField($value)
{
    if (is_array($value)) {
        $value = array_map('htmlspecialchars', $value);
        return implode(', ', $value);
    }
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
class MYPDF extends TCPDF
{

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

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Votre Nom');
$pdf->SetTitle('Rapport de Visite d\'Inspection');
$pdf->SetSubject('TCPDF ');
$pdf->SetKeywords('TCPDF');

$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 001', PDF_HEADER_STRING);

$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);



$pdf->SetFont('helvetica', 'B', 14);
$pdf->Ln(30);
function addSectionToPdf($pdf, $title, $fields)
{
    $pdf->AddPage(); // Assurez-vous que chaque section commence sur une nouvelle page
    $htmlcontent = '<h1 style="text-align:center;">' . $title . '</h1><hr>'; // Utilisez un titre centralisé et une ligne de séparation
    foreach ($fields as $field) {
        $postKey = str_replace(['.', ' ', '(', ')'], '_', $field); // Remplace les caractères non valides
        if (isset($_POST[$postKey])) { // Vérifiez seulement si le champ est défini
            $field_value = formatMultiValueField($_POST[$postKey]);
            $htmlcontent .= '<h2>' . $field . '</h2><p>' . $field_value . '</p>';
        }
    }
    $pdf->writeHTML($htmlcontent, true, false, true, false, '');
}

$fields_chap1 = [
    'Titulaire', 'STD',
    'DDT', 'DFT', 'TSS',
    'EffectifsMoyens', 'EDP'
];
echo '<pre>' . print_r($_POST, true) . '</pre>';
addSectionToPdf($pdf, 'Chapitre 1: Entreprise Intervenante', $fields_chap1);

$fields_chap2 = [
    'P.G.C', 'D.I.C.T', 'P.P.S.P.S', 'Arrêtés de circulation', 'PRA (SS3)', 'MOA (SS4)', 'Autres documents'
];
addSectionToPdf($pdf, 'Chapitre 2: Document préparatoire', $fields_chap2);


$fields_chap3 = [
    'Zones d’installation de chantier', 'Autres', 'Installation hygiène', 'Base vie mobile',
    'Local existant', 'Bungalow chantier', 'Sanitaires', 'Réfectoire', 'Vestiaires',
    'Repas pris au restaurant', 'A.E.P.', 'E.U.', 'Electricité', 'Signalisation travaux',
    'Conditions et mode d’approvisionnement', 'Survol de grue', 'Contrôle', 'Moyens de secours',
    'Secouristes', 'Moyens d’alerte', 'Trousse d’urgence', 'Extincteur'
];
addSectionToPdf($pdf, 'Chapitre 3: Installations de chantier', $fields_chap3);


$fields_chap4 = [
    'Bruit', 'Poussières', 'Chute de hauteur', 'Risque d’ensevelissement',
    'Electrisation', 'Espaces Confinés', 'Circulation', 'Manœuvre d’engin', 'Protections à installer',
    'Déblais', 'Evacuation', 'Stockage', 'Tri des déchets', 'Autres',
    'Réseaux', 'Réseaux Entérrées', 'Réseaux Aériens'
];
addSectionToPdf($pdf, 'Chapitre 4: Environnement', $fields_chap4);


$fields_chap5 = [
    'Risques exportés', 'Risques importés', 'ISDD',
    'PAI', 'Autres'
];
addSectionToPdf($pdf, 'Chapitre 5: Risques', $fields_chap5);

// Fermer et sortir le document PDF
$nom_du_fichier = 'Formulaire_Visite_Inspection_' . date('Y_m_d_H_i_s') . '.pdf';
$chemin_du_fichier = __DIR__ . "/PDF/" . $nom_du_fichier; // Assurez-vous que ce chemin est correct et accessible en écriture

// Enregistrement du fichier sur le serveur
$pdf->Output($chemin_du_fichier, 'F');

// Pour éviter les erreurs "headers already sent", vous pouvez utiliser la fonction ob_start() au début de votre script.
// Redirection vers mail.php avec le chemin du fichier PDF
header("Location: pageMail.php?file=" . urlencode($nom_du_fichier));
exit;