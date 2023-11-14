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
        $this->SetFont('helvetica', 'B', 20);
        $this->Image('../images/imgpreview.jpg', 10, 10, 33, 0, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->Cell(0, 15, "Visite d'inspection commune ", 0, false, 'C', 0, '', 0, false, 'M', 'M');
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
    $htmlcontent = '<h1 style="text-align:center;">' . $title . '</h1><hr><br>';
    foreach ($fields as $field) {
        if (!in_array($field, $checkboxFields)) {
            $postKey = str_replace(['.', ' ', '(', ')'], '_', $field);
            if (isset($_POST[$postKey])) {
                $field_value = $_POST[$postKey];
                if (is_array($field_value)) {
                    $field_value = formatMultiValueField($field_value);
                    $htmlcontent .= '<p style="margin-bottom: 10px;"><b>' . $field . ':</b> ' . $field_value . '</p>';
                } elseif (trim($field_value) != '') {
                    $field_value = formatMultiValueField($field_value);
                    $htmlcontent .= '<p style="margin-bottom: 10px;"><b>' . $field . ':</b> ' . $field_value . '</p>';
                }
            }
        }
    }
    if (count($checkboxFields) > 0) {
        $htmlcontent .= '<table width="100%"><tr>';
        $colWidth = 100 / $numCols;
        $colCount = 0;

        foreach ($checkboxFields as $checkboxField) {
            $postKey = str_replace(['.', ' ', '(', ')'], '_', $checkboxField);
            if (isset($_POST[$postKey])) {
                if ($colCount >= $numCols) {
                    $htmlcontent .= '</tr><tr>';
                    $colCount = 0;
                }
                $field_value = formatMultiValueField($_POST[$postKey], true);
                $htmlcontent .= '<td width="' . $colWidth . '%"><p>' . $checkboxField . '</p><p>' . $field_value . '</p></td>';
                $colCount++;
            }
        }
        $htmlcontent .= '</tr></table>';
    }

    $pdf->writeHTML($htmlcontent, true, false, true, false, '');
}
$fields_chapIntro = [
    'Chantier',
    'Maitre d’Ouvrage',
    'Maitre d’Œuvre',
    'Lot concerné'
];
addSectionToPdf($pdf, 'Information Chantier', $fields_chapIntro, []);

$fields_chap1 = [
    'Titulaire',
    'Sous-Traitant de',
    'Date début de travaux',
    'Date fin des travaux',
    'Travaux sous-traités',
    'EffectifsMoyens',
    'Effectifs de pointe'
];
addSectionToPdf($pdf, 'Entreprise Intervenante', $fields_chap1, []);

$checkboxFieldsChap2 = [
    'P.G.C',
    'D.I.C.T',
    'P.P.S.P.S',
    'Arrêtés de circulation',
    'PRA (SS3)',
    'MOA (SS4)'
];
$fields_chap2 = [
    'P.G.C',
    'D.I.C.T',
    'P.P.S.P.S',
    'Arrêtés de circulation',
    'PRA (SS3)',
    'MOA (SS4)',
    'Autres documents'
];
addSectionToPdf($pdf, 'Document préparatoire', $fields_chap2, $checkboxFieldsChap2);

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
    'Protections à installer',
    'Autres'
];
addSectionToPdf($pdf, 'Risques', $fields_chap5, []);

$nom_du_fichier = 'Formulaire_Visite_Inspection_' . date('Y_m_d_H_i_s') . '.pdf';
$chemin_du_fichier = __DIR__ . "/PDF/" . $nom_du_fichier;

$pdf->Output($chemin_du_fichier, 'F');

header("Location: pageMail.php?file=" . urlencode($nom_du_fichier));
exit;
