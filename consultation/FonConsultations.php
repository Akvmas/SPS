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
?>
<!DOCTYPE html>
<html>

<head>
  <link rel="stylesheet" href="style.css">
  <script src="script.js"></script>
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
          <button type="button" class="tab-link active" onclick="openTab(event, 'observation1')">Observation 1</button>
          <button type="button" class="tab-link" onclick="openTab(event, 'observation2')">Observation 2</button>
          <button type="button" class="tab-link" onclick="openTab(event, 'observation3')">Observation 3</button>
        </div>
        <br>
        <div id="observation1" class="tab-content" style="display: block;">
          <label>Type de visite:</label>
          <div class="radio-buttons">
            <label for="reunion">
              <input type="radio" id="reunion1" name="typeVisite1" value="reunion" <?php echo ($observations[1]['details']['typeVisite'] ?? '') == 'reunion' ? 'checked' : ''; ?>>Réunion</label>
            <label for="visiteInopinee">
              <input type="radio" id="visiteInopinee1" name="typeVisite1" value="visiteInopinee" <?php echo (isset($observations[1]['typeVisite']) && $observations[1]['typeVisite'] == 'visiteInopinee') ? 'checked' : ''; ?>>Visite inopinée</label>
            <label for="autre">
              <input type="radio" id="autre1" name="typeVisite1" value="autre" <?php echo (isset($observations[1]['typeVisite']) && $observations[1]['typeVisite'] == 'autre') ? 'checked' : ''; ?>>Autre</label>
          </div>
          <div class="input-group" id="autreText1" style="<?php echo (isset($observations[1]['typeVisite']) && $observations[1]['typeVisite'] == 'autre') ? 'display: block;' : 'display: none;'; ?>">
            <label for="autreDescription">Précisez:</label>
            <input type="text" name="autreDescription1" id="autreDescription1" value="<?php echo isset($observations[1]['autreDescription']) ? $observations[1]['autreDescription'] : ''; ?>">
          </div>
          <label>Date:</label>
          <input type="date" name="date1" id="date1" value="<?= $observations[1]['details']['date'] ?? '' ?>">
          <label>Heure:</label>
          <input type="time" name="heure1" id="heure1" value="<?= $observations[1]['details']['heure'] ?? '' ?>">
          <br>
          <textarea name="observation1" rows="5" cols="50" maxlength="1000"><?php echo $observations[1]['details']['texte'] ?? ''; ?></textarea>
          <?php if (!empty($observations[1]['images'])) : ?>
            <?php foreach ($observations[1]['images'] as $image) : ?>
              <img src="data:image/jpeg;base64,<?= $image['image_base64'] ?>" alt="Photo d'observation 1" />
            <?php endforeach; ?>
          <?php else : ?>
            <input type="file" name="photo1[]" accept="image/*" multiple>
          <?php endif; ?>
          <label for="entreprise1">Entreprise:</label>
          <input type="text" name="entreprise1" id="entreprise1" value="<?php echo $observations[1]['details']['entreprise'] ?? ''; ?>">
          <br>
          <label for="effectif1">Effectif:</label>
          <input type="text" name="effectif1" id="effectif1" value="<?php echo $observations[1]['details']['effectif'] ?? ''; ?>">
          <br>
        </div>
        <div id="observation2" class="tab-content">
          <label>Type de visite:</label>
          <div class="radio-buttons">
            <label for="reunion">
              <input type="radio" id="reunion2" name="typeVisite2" value="reunion" <?php echo (isset($observations[2]['typeVisite']) && $observations[2]['typeVisite'] == 'reunion') ? 'checked' : ''; ?>>Réunion</label>

            <label for="visiteInopinee">
              <input type="radio" id="visiteInopinee2" name="typeVisite2" value="visiteInopinee" <?php echo (isset($observations[2]['typeVisite']) && $observations[2]['typeVisite'] == 'visiteInopinee') ? 'checked' : ''; ?>>Visite inopinée</label>

            <label for="autre">
              <input type="radio" id="autre2" name="typeVisite2" value="autre" <?php echo (isset($observations[2]['typeVisite']) && $observations[2]['typeVisite'] == 'autre') ? 'checked' : ''; ?>>Autre</label>
          </div>
          <div class="input-group" id="autreText2" style="<?php echo (isset($observations[2]['typeVisite']) && $observations[2]['typeVisite'] == 'autre') ? 'display: block;' : 'display: none;'; ?>">
            <label for="autreDescription">Précisez:</label>
            <input type="text" name="autreDescription2" id="autreDescription2" value="<?php echo isset($observations[2]['autreDescription']) ? $observations[2]['autreDescription'] : ''; ?>">
          </div>
          <label>Date:</label>
          <input type="date" name="date2" id="date2" value="<?= $observations[2]['details']['date'] ?? '' ?>">
          <label>Heure:</label>
          <input type="time" name="heure2" id="heure2" value="<?= $observations[2]['details']['heure'] ?? '' ?>">
          <br>
          <textarea name="observation2" rows="5" cols="50" maxlength="1000"><?php echo $observations[2]['details']['texte'] ?? ''; ?></textarea>
          <?php if (!empty($observations[2]['images'])) : ?>
            <?php foreach ($observations[2]['images'] as $image) : ?>
              <img src="data:image/jpeg;base64,<?= $image['image_base64'] ?>" alt="Photo d'observation 2" />
            <?php endforeach; ?>
          <?php else : ?>
            <input type="file" name="photo2[]" accept="image/*" multiple>
          <?php endif; ?>
          <label for="entreprise2">Entreprise:</label>
          <input type="text" name="entreprise2" id="entreprise2" value="<?php echo $observations[2]['details']['entreprise'] ?? ''; ?>">
          <br>
          <label for="effectif2">Effectif:</label>
          <input type="text" name="effectif2" id="effectif2" value="<?php echo $observations[2]['details']['effectif'] ?? ''; ?>">
          <br>
        </div>
        <div id="observation3" class="tab-content">
          <label>Type de visite:</label>
          <div class="radio-buttons">
            <label for="reunion">
              <input type="radio" id="reunion3" name="typeVisite3" value="reunion" <?php echo (isset($observations[3]['typeVisite']) && $observations[3]['typeVisite'] == 'reunion') ? 'checked' : ''; ?>>Réunion</label>
            <label for="visiteInopinee">
              <input type="radio" id="visiteInopinee3" name="typeVisite3" value="visiteInopinee" <?php echo (isset($observations[3]['typeVisite']) && $observations[3]['typeVisite'] == 'visiteInopinee') ? 'checked' : ''; ?>>Visite inopinée</label>
            <label for="autre">
              <input type="radio" id="autre3" name="typeVisite3" value="autre" <?php echo (isset($observations[3]['typeVisite']) && $observations[3]['typeVisite'] == 'autre') ? 'checked' : ''; ?>>Autre</label>
          </div>
          <div class="input-group" id="autreText3" style="<?php echo (isset($observations[3]['typeVisite']) && $observations[3]['typeVisite'] == 'autre') ? 'display: block;' : 'display: none;'; ?>">
            <label for="autreDescription">Précisez:</label>
            <input type="text" name="autreDescription3" id="autreDescription3" value="<?php echo isset($observations[3]['autreDescription']) ? $observations[3]['autreDescription'] : ''; ?>">
          </div>
          <label>Date:</label>
          <input type="date" name="date3" id="date3" value="<?= $observations[3]['details']['date'] ?? '' ?>">
          <label>Heure:</label>
          <input type="time" name="heure3" id="heure3" value="<?= $observations[3]['details']['heure'] ?? '' ?>">
          <br>
          <textarea name="observation3" rows="5" cols="50" maxlength="1000"><?php echo $observations[3]['details']['texte'] ?? ''; ?></textarea>
          <br>
          <?php if (!empty($observations[3]['images'])) : ?>
            <?php foreach ($observations[3]['images'] as $image) : ?>
              <img src="data:image/jpeg;base64,<?= $image['image_base64'] ?>" alt="Photo d'observation 3" />
            <?php endforeach; ?>
          <?php else : ?>
            <input type="file" name="photo3[]" accept="image/*" multiple>
          <?php endif; ?>
          <label for="entreprise3">Entreprise:</label>
          <input type="text" name="entreprise3" id="entreprise3" value="<?php echo $observations[3]['details']['entreprise'] ?? ''; ?>">
          <br>
          <label for="effectif3">Effectif:</label>
          <input type="text" name="effectif3" id="effectif3" value="<?php echo $observations[3]['details']['effectif'] ?? ''; ?>">
          <br>
        </div>
        <br>
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
    </form>
    <div id="pdfGenerationPopup" style="position: fixed; left: 50%; top: 50%; transform: translate(-50%, -50%); background-color: white; padding: 20px; border: 1px solid #ddd; z-index: 1000;">
        <form action="generate_pdf.php" method="post" target="_blank">
            <div class="input-group">
              <textarea hidden name="chantier_id" id="chantier_id_popup" rows="2" cols="50"><?= $chantier_id ?></textarea>
              <label for="chantier">Chantier:</label>
              <textarea name="chantierNom" id="chantierNom_popup" rows="2" cols="50"><?= $chantier['description'] ?></textarea>
            </div>
            <div class="input-group">
              <label for="maitreOuvrage">Maître d'Ouvrage:</label>
              <input type="text" name="maitreOuvrage" id="maitreOuvrage_popup" Value="<?= $chantier['maitreOuvrage'] ?>">
            </div>
            <div class="input-group">
              <label for="maitreOeuvre">Maître d'Œuvre:</label>
              <input type="text" name="maitreOeuvre" id="maitreOeuvre_popup" value="<?= $chantier['maitreOeuvre'] ?>">
            </div>
            <div class="input-group">
              <label for="coordonnateurSPS">Coordonnateur S.P.S.:</label>
              <input type="text" name="coordonnateurSPS" id="coordonnateurSPS_popup" Value="Gaël MONGARS">
            </div>
            <button type="submit">Générer le PDF</button>
            <button id ="close-popup-button" name="close-popup-button"type="button" onclick="closePopup()">Fermer</button>
        </form>
    </div>
  </div>
</body>
</html>