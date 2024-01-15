<?php
require('../vendor/autoload.php');
error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED & ~E_USER_DEPRECATED);

function formatMultiValueField($value, $isCheckbox = false)
{
    if ($isCheckbox) {
        return $value ? '✓' : '☐';
    } elseif (is_array($value)) {
        $value = array_map('htmlspecialchars', $value);
        return implode(', ', $value);
    }
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

class MYPDF extends TCPDF
{

    public function Header()
    {
        $this->Image('../images/imgpreview.jpg', 10, 10, 33, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->SetFont('helvetica', 'B', 20);
        $this->SetY(25);
        $this->Cell(0, 15, "Rapport de Visite de chantier", 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('MONGARS Gaël');
$pdf->SetTitle('Rapport de Visite de chantier');
$pdf->SetMargins(PDF_MARGIN_LEFT, 50, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

$pdf->SetFont('dejavusans', 'B', 10);
$pdf->Ln(40);
function addSectionToPdf($pdf, $title, $fields, $checkboxFields = [], $numCols = 2)
{
    $pdf->AddPage();
    $pdf->SetFont('dejavusans', 'B', 12);
    $htmlcontent = '<h1 style="text-align:center;">' . $title . '</h1><hr><br>';
    $pdf->SetFont('dejavusans', '', 10);

    foreach ($fields as $field) {
        $postKey = str_replace(['.', ' ', '(', ')'], '_', $field);
        if (isset($_POST[$postKey])) {
            $field_value = $_POST[$postKey];
            if (is_array($field_value)) {
                $field_value = formatMultiValueField($field_value);
                $htmlcontent .= '<p><b>' . str_replace('_', ' ', $field) . ':</b></p><p>' . $field_value . '</p>';
            } elseif (trim($field_value) != '') {
                $field_value = formatMultiValueField($field_value);
                $htmlcontent .= '<p><b>' . str_replace('_', ' ', $field) . ':</b></p><p>' . $field_value . '</p>';
            }
        }
    }

    if (count($checkboxFields) > 0) {
        $htmlcontent .= '<table width="100%"><tr>';
        $colWidth = 100 / $numCols;
        $colCount = 0;

        foreach ($checkboxFields as $checkboxField) {
            $postKey = str_replace(['.', ' ', '(', ')', '[', ']'], '_', $checkboxField);
            if (isset($_POST[$postKey])) {
                if ($colCount >= $numCols) {
                    $htmlcontent .= '</tr><tr>';
                    $colCount = 0;
                }
                $field_value = formatMultiValueField($_POST[$postKey], true);
                $htmlcontent .= '<td width="' . $colWidth . '%"><p><b>' . str_replace('_', ' ', $checkboxField) . ':</b></p><p>' . $field_value . '</p></td>';
                $colCount++;
            }
        }
        $htmlcontent .= '</tr></table>';
    }

    $pdf->writeHTML($htmlcontent, true, false, true, false, '');
}



$checkboxFieldsChapIntro = [
    'P.G.C',
    'D.I.C.T',
    'P.P.S.P.S',
    'Arrêtés de circulation',
    'PRA (SS3)',
    'MOA (SS4)'
];

$fields_chapIntro = [
    'Chantier',
    'Maitre d’Ouvrage',
    'Maitre d’Œuvre',
    'Lot concerné',
    'Titulaire',
    'Sous-Traitant de',
    'Date début de travaux',
    'Date fin des travaux',
    'Travaux sous-traités',
    'EffectifsMoyens',
    'Effectifs de pointe',
    'P.G.C',
    'D.I.C.T',
    'P.P.S.P.S',
    'Arrêtés de circulation',
    'PRA_SS3',
    'MOA_SS4',
    'Autres documents'
];
addSectionToPdf($pdf, 'Information Chantier', $fields_chapIntro, $checkboxFieldsChapIntro);

$checkboxFieldsChap3 = [
    'Base vie mobile',
    'Local existant',
    'Bungalow chantier',
    'Sanitaires',
    'Réfectoire',
    'Vestiaires',
    'Repas pris au restaurant',
    'A.E.P.',
    'E.U.',
    'Electricité'
];
$fields_chap3 = [
    'Zones d’installation de chantier',
    'Autres',
    'Installation hygiène',
    'Base vie mobile',
    'Local existant',
    'Bungalow chantier',
    'Sanitaires',
    'Réfectoire',
    'Vestiaires',
    'Repas pris au restaurant',
    'A.E.P.',
    'E.U.',
    'Electricité',
    'Signalisation travaux',
    'Conditions et mode d’approvisionnement',
    'Survol de grue',
    'Contrôle',
    'Moyens de secours',
    'Secouristes',
    'Moyens d’alerte',
    'Trousse d’urgence',
    'Extincteur'
];
addSectionToPdf($pdf, 'Installations de chantier', $fields_chap3, $checkboxFieldsChap3);

$checkboxFieldsChap4 = [
    'Bruit',
    'Poussières',
    'Chute de hauteur',
    'Risque d’ensevelissement',
    'Electrisation',
    'Espaces Confinés',
    'Evacuation',
    'Stockage'
];
$fields_chap4 = [
    'Bruit',
    'Poussières',
    'Chute de hauteur',
    'Risque d’ensevelissement',
    'Electrisation',
    'Espaces Confinés',
    'Circulation',
    'Manœuvre d’engin',
    'Protection à installer',
    'Déblais ',
    'Evacuation',
    'Stockage',
    'Tri des déchets',
    'Autres',
    'Réseaux',
    'Réseaux Entérrées',
    'Réseaux Aériens'
];
addSectionToPdf($pdf, 'Environnement', $fields_chap4, $checkboxFieldsChap4);

$fields_chap5 = [
    'Risques exportés',
    'Risques importés',
    'Interventions susceptibles d’être dangereuses',
    'AutresChantier1',
    'Protections à installer',
    'AutresChantier2'
];
addSectionToPdf($pdf, 'Risques', $fields_chap5, []);
$pdf->AddPage();

$signatureData = isset($_POST['signatureData']) ? $_POST['signatureData'] : null;

$htmlcontent = '
    <h3>Visite du site faite ce jour pour analyse des conditions d’exécution des travaux</h3>
    <table>
        <tr>
            <td>Entreprise: ' . htmlspecialchars($postData['EntrepriseSignature'][0] ?? '') . '</td>
            <td>Coordonnateur S.P.S: ' . htmlspecialchars($postData['Nom2'] ?? 'MONGARS Gaël') . '</td>
        </tr>
        <tr>
            <td>Nom: ' . htmlspecialchars($postData['Nom1'] ?? '') . '</td>
            <td>Date: ' . date('d/m/Y') . '</td>
        </tr>
            <tr>
                <td>Date: ' . date('d/m/Y') . '</td>
                <td><img src="../images/signature.png" width="220" height="100"></td>
            </tr>
            <tr>
        </table>';

$pdf->writeHTML($htmlcontent, true, false, true, false, '');

if (isset($_POST['signatureData']) && !empty($_POST['signatureData'])) {
    $signatureData = str_replace('data:image/png;base64,', '', $_POST['signatureData']);
    $signatureData = str_replace(' ', '+', $signatureData);
    $signatureImage = base64_decode($signatureData);
    $signatureFilePath = tempnam(sys_get_temp_dir(), 'sig');
    file_put_contents($signatureFilePath, $signatureImage);

    $pdf->Image($signatureFilePath, 15, 80, 40, 20, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);

    unlink($signatureFilePath);
}



$nom_du_fichier = 'Formulaire_Visite_Inspection_' . date('Y_m_d_H_i_s') . '.pdf';
$chemin_du_fichier = __DIR__ . "/PDF/" . $nom_du_fichier;

$pdf->Output($chemin_du_fichier, 'F');

header("Location: pageMail.php?file=" . urlencode($nom_du_fichier));
exit;
