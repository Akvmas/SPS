<?php
session_start();
if (!isset($_SESSION["username"])) {
  header("Location: ../login.php");
  exit();
}
include '../config.php';
$chantier_id = $_GET['chantier_id'];

$stmt = $pdo->prepare("SELECT * FROM chantiers WHERE id = :chantier_id");
$stmt->bindParam(':chantier_id', $chantier_id, PDO::PARAM_INT);
$stmt->execute();
$chantier = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM personnes_presentes WHERE chantier_id = :chantier_id");
$stmt->bindParam(':chantier_id', $chantier_id, PDO::PARAM_INT);
$stmt->execute();
$personnes = $stmt->fetchAll();

$observations = [];
for ($i = 1; $i <= 3; $i++) {
  $stmt = $pdo->prepare("SELECT * FROM observations WHERE chantier_id = :chantier_id AND observation_number = :obs_number");
  $stmt->execute(['chantier_id' => $chantier_id, 'obs_number' => $i]);
  $observation = $stmt->fetch();

  if ($observation) {
    $imgStmt = $pdo->prepare("SELECT TO_BASE64(image) AS image_base64 FROM observation_images WHERE observation_id = :observation_id");
    $imgStmt->execute(['observation_id' => $observation['observation_id']]);
    $images = $imgStmt->fetchAll();

    $observations[$i] = [
      'details' => $observation,
      'images' => $images
    ];
  } else {
    $observations[$i] = [
      'details' => [],
      'images' => []
    ];
  }
}
$stmt = $pdo->prepare("SELECT * FROM observations WHERE chantier_id = :chantier_id AND observation_number > 3");
$stmt->execute(['chantier_id' => $chantier_id]);
while ($observation = $stmt->fetch()) {
  $imgStmt = $pdo->prepare("SELECT TO_BASE64(image) AS image_base64 FROM observation_images WHERE observation_id = :observation_id");
  $imgStmt->execute(['observation_id' => $observation['observation_id']]);
  $images = $imgStmt->fetchAll();

  $observations[$observation['observation_number']] = [
    'details' => $observation,
    'images' => $images
  ];
}

?>

<!DOCTYPE html>
<html>

<head>
  <link rel="stylesheet" href="style.css">
  <script defer src="script.js"></script>
  <script type="text/javascript"> var observationCounter = <?= count($observations) + 1 ?>;</script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>

<body>
  <div class="container">
    <form id="myForm" action="process.php" method="POST" enctype="multipart/form-data">
      <div class="part-one">
        <div class="input-group">
          <textarea hidden name="chantier_id" id="chantier_id" rows="2" cols="50"><?= $chantier_id ?></textarea>
          <label for="chantier">Chantier:</label>
          <textarea name="chantierNom" id="chantierNom" rows="2" cols="50"><?= $chantier['description'] ?></textarea>
        </div>
        <div class="input-group">
          <label for="maitreOuvrage">Maître d'Ouvrage:</label>
          <input type="text" name="maitreOuvrage" id="maitreOuvrage" Value="<?= $chantier['maitreOuvrage'] ?>">
        </div>
        <div class="input-group">
          <label for="maitreOeuvre">Maître d'Œuvre:</label>
          <input type="text" name="maitreOeuvre" value="<?= $chantier['maitreOeuvre'] ?>">
        </div>
        <div class="input-group">
          <label for="coordonnateurSPS">Coordonnateur S.P.S.:</label>
          <input type="text" name="coordonnateurSPS" id="coordonnateurSPS" Value="Gaël MONGARS">
        </div>
        <div class="input-group">
          <label>Personnes présentes:</label>
          <div id="dynamicInput">
            <div class="personne-input">
              <input type="text" name="personne1" id="personne1">
              <button type="button" class="remove-button" onclick="removeInput(this,event)">x</button>
              <button type="button" onclick="addInput('dynamicInput',event)">+</button>
            </div>
          </div>
        </div>
      </div>
      <div class="text-divider"></div>
      <div class="part-two">
        <div id="tabs">
          <?php foreach ($observations as $num => $obs) : ?>
            <button type="button" class="tab-link <?= $num == 1 ? 'active' : '' ?>" onclick="openTab(event, 'observation<?= $num ?>')">Observation <?= $num ?></button>
          <?php endforeach; ?>
          <button type="button" onclick="addObservation()">Ajouter Observation</button>
        </div>
        <br>
        <?php foreach ($observations as $num => $obs) : ?>
          <div id="observation<?= $num ?>" class="tab-content" style="<?= $num == 1 ? 'display: block;' : 'display: none;' ?>">
            <label>Type de visite:</label>
            <div class="radio-buttons">
              <label for="reunion">
                <input type="radio" id="reunion<?= $num ?>" name="typeVisite<?= $num ?>" value="reunion" <?php echo ($observations[$num]['details']['typeVisite'] ?? '') == 'reunion' ? 'checked' : ''; ?>>Réunion</label>
              <label for="visiteInopinee">
                <input type="radio" id="visiteInopinee<?= $num ?>" name="typeVisite<?= $num ?>" value="visiteInopinee" <?php echo (isset($observations[$num]['typeVisite']) && $observations[$num]['typeVisite'] == 'visiteInopinee') ? 'checked' : ''; ?>>Visite inopinée</label>
              <label for="autre">
                <input type="radio" id="autre<?= $num ?>" name="typeVisite<?= $num ?>" value="autre" <?php echo (isset($observations[$num]['typeVisite']) && $observations[$num]['typeVisite'] == 'autre') ? 'checked' : ''; ?>>Autre</label>
            </div>
            <div class="input-group" id="autreText<?= $num ?>" style="<?php echo (isset($observations[$num]['typeVisite']) && $observations[$num]['typeVisite'] == 'autre') ? 'display: block;' : 'display: none;'; ?>">
              <label for="autreDescription">Précisez:</label>
              <input type="text" name="autreDescription<?= $num ?>" id="autreDescription<?= $num ?>" value="<?php echo isset($observations[$num]['autreDescription']) ? $observations[$num]['autreDescription'] : ''; ?>">
            </div>
            <label>Date:</label>
            <input type="date" name="date<?= $num ?>" id="date<?= $num ?>" value="<?= $observations[$num]['details']['date'] ?? '' ?>">
            <label>Heure:</label>
            <input type="time" name="heure<?= $num ?>" id="heure<?= $num ?>" value="<?= $observations[$num]['details']['heure'] ?? '' ?>">
            <br>
            <textarea name="observation1" rows="5" cols="50" maxlength="1000"><?php echo $observations[$num]['details']['texte'] ?? ''; ?></textarea>
            <?php if (!empty($observations[$num]['images'])) : ?>
              <?php foreach ($observations[$num]['images'] as $image) : ?>
                <img src="data:image/jpeg;base64,<?= $image['image_base64'] ?>" alt="Photo d'observation <?= $num ?>" />
              <?php endforeach; ?>
            <?php else : ?>
              <input type="file" name="photo[<?= $num ?>]" accept="image/*" multiple>
            <?php endif; ?>
            <label for="entreprise1">Entreprise:</label>
            <input type="text" name="entreprise<?= $num ?>" id="entreprise<?= $num ?>" value="<?php echo $observations[$num]['details']['entreprise'] ?? ''; ?>">
            <br>
            <label for="effectif1">Effectif:</label>
            <input type="text" name="effectif<?= $num ?>" id="effectif<?= $num ?>" value="<?php echo $observations[$num]['details']['effectif'] ?? ''; ?>">
            <br>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="text-divider"></div>
        <p>Sans remarque de la part de l’entreprise dans un délai de 8 jours, les observations formulées par le Coordonnateur S.P.S. sont réputées acceptées sans réserve.</p>
        <div class="part-three">
          <div class="column1">
            <label for="coordonnateurSPS_copy">Le Coordonnateur S.P.S :</label>
            <input type="text" name="coordonnateurSPS_copy" id="coordonnateurSPS_copy" Value="Gaël MONGARS">
            <label for="signature">Signature:</label>
            <img src="../images/signature.png" width="220" height="100">
          </div>
        </div>
        <input type="submit" value="Upload">
      </div>
    </form>
  </div>
</body>
</html>