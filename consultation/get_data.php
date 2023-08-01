<?php
require '../config.php';

$id = $_GET['id'];

$stmt = $pdo->prepare('
  SELECT fon.*, personnes.personne, observations.observation, observations.entreprise, observations.effectif, observations.photo 
  FROM fon 
  LEFT JOIN personnes ON fon.id = personnes.fon_id
  LEFT JOIN observations ON fon.id = observations.fon_id
  WHERE fon.id = ?
');
$stmt->execute([$id]);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = ['fon' => $data[0], 'personnes' => [], 'observations' => []];

foreach($data as $row) {
  if (!empty($row['personne'])) {
    $result['personnes'][] = $row['personne'];
  }
  if (!empty($row['observation'])) {
    $result['observations'][] = [
      'observation' => $row['observation'],
      'entreprise' => $row['entreprise'],
      'effectif' => $row['effectif'],
      'photo' => $row['photo']
    ];
  }
}

echo json_encode($result);
