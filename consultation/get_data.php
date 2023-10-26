<?php
require '../config.php';

$id = $_GET['id'];

$stmt = $pdo->prepare('
  SELECT chantiers.*, personnes_presentes.personne, 
         observations.observation, observations.entreprise, 
         observations.effectif, observations.photo, 
         observations.typeVisite, observations.date, observations.heure 
  FROM chantiers 
  LEFT JOIN personnes_presentes ON chantiers.id = personnes_presentes.chantier_id
  LEFT JOIN observations ON chantiers.id = observations.chantier_id
  WHERE chantiers.id = ?
');
$stmt->execute([$id]);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = ['chantier' => $data[0], 'personnes' => [], 'observations' => []];

foreach($data as $row) {
  if (!empty($row['personne'])) {
    $result['personnes'][] = $row['personne'];
  }
  if (!empty($row['observation'])) {
    $result['observations'][] = [
      'observation' => $row['observation'],
      'entreprise' => $row['entreprise'],
      'effectif' => $row['effectif'],
      'photo' => $row['photo'],
      'typeVisite' => $row['typeVisite'],
      'date' => $row['date'],
      'heure' => $row['heure']
    ];
  }
}

echo json_encode($result);
?>
