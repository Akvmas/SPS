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
$observation_images = [];

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

    $observation_images[$i] = $images;
  } else {
    $observations[$i] = [
      'details' => [],
      'images' => []
    ];
    $observation_images[$i] = [];
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
$stmt = $pdo->prepare("SELECT * FROM observations WHERE chantier_id = :chantier_id ORDER BY observation_number");
$stmt->bindParam(':chantier_id', $chantier_id);
$stmt->execute();
$observations = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT observation_id FROM observations WHERE chantier_id = :chantier_id ORDER BY observation_number");
$stmt->bindParam(':chantier_id', $chantier_id);
$stmt->execute();
$observationsIDs = $stmt->fetchAll();
?>


<!DOCTYPE html>
<html>

<head>
  <link rel="stylesheet" href="style.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>

<body>
  <div class="container">
    <div id="myForm">
      <div class="part-one">
        <div class="input-group">
          <textarea hidden name="chantier_id" id="chantier_id" rows="2" cols="50"><?= $chantier_id ?></textarea>
          <label for="chantier">Chantier:</label>
          <textarea name="chantierNom" id="chantierNom" rows="2" cols="50"><?= $chantier['description'] ?></textarea>
        </div>
        <div class="input-group">
          <label for="maitreouvrage">Maître d'Ouvrage:</label>
          <input type="text" name="maitreOuvrage" id="maitreOuvrage" Value="<?= $chantier['maitreOuvrage'] ?>">
        </div>
        <div class="input-group">
          <label for="maitreoeuvre">Maître d'Œuvre:</label>
          <input type="text" name="maitreOeuvre" value="<?= $chantier['maitreOeuvre'] ?>">
        </div>
        <div class="input-group">
          <label for="coordonateursps">Coordonnateur S.P.S.:</label>
          <input type="text" name="coordonnateurSPS" id="coordonnateurSPS" Value="Gaël MONGARS">
        </div>
        <div class="input-group">
          <label for="personnespresentes">Personnes présentes:</label>
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
          <?php foreach ($observations as $obs) : ?>
            <button type="button" class="tab-link <?= $obs['observation_number'] == 1 ? 'active' : '' ?>" onclick="openTab(event, 'observation<?= $obs['observation_number'] ?>')">Observation <?= $obs['observation_number'] ?></button>
          <?php endforeach; ?>
          <button id="newObservationButton" type="button">Nouvelle Observation</button>
          <button class="delete-observation" data-observation-number="<?= $obs['observation_number'] ?>">Supprimer</button>
        </div>
        <br>
        <?php foreach ($observations as $obs) : ?>
          <?php
          $key = $obs['observation_number'] - 1;
          ?>
          <div id="observation<?= $obs['observation_number'] ?>" class="tab-content" style="<?= $obs['observation_number'] == 1 ? 'display: block;' : 'display: none;' ?>">
            <form action="process.php?observation_number=<?= $obs['observation_number'] ?>" method="POST" enctype="multipart/form-data">
              <input type="hidden"name="chantierNom" id="chantierNom"value = "<?= $chantier['description'] ?>">
              <input type="hidden" name="chantier_id" value="<?= $chantier_id ?>">
              <input type="hidden" name="observation_id" value="<?= $obs['observation_id'] ?>">
              <label for="personnesPresentes<?= $obs['observation_number'] ?>">Personnes présentes:</label>
              <textarea name="personnesPresentes<?= $obs['observation_number'] ?>" ><?= isset($personnes['details']) ? implode(", ", array_column($personnes['details'], 'nom')) : "" ?></textarea>
              <label for="type de visite">Type de visite:</label>
              <div class="radio-buttons">
                <label for="reunion<?= $obs['observation_number'] ?>">
                  <input type="radio" id="reunion<?= $obs['observation_number'] ?>" name="typeVisite<?= $obs['observation_number'] ?>" value="reunion" <?= $obs['typeVisite'] == 'reunion' ? 'checked' : '' ?>> Réunion
                </label>
                <label for="visiteInopinee<?= $obs['observation_number'] ?>">
                  <input type="radio" id="visiteInopinee<?= $obs['observation_number'] ?>" name="typeVisite<?= $obs['observation_number'] ?>" value="Visite inopinée" <?= $obs['typeVisite'] == 'Visite inopinée' ? 'checked' : '' ?>> Visite inopinée
                </label>
                <label for="autre<?= $obs['observation_number'] ?>">
                  <input type="radio" id="autre<?= $obs['observation_number'] ?>" name="typeVisite<?= $obs['observation_number'] ?>" value="autre" <?= $obs['typeVisite'] == 'autre' ? 'checked' : '' ?>> Autre
                </label>
              </div>
              <div class="input-group" id="autreText<?= $obs['observation_number'] ?>" style="<?= $obs['typeVisite'] == 'autre' ? 'display: block;' : 'display: none;' ?>">
                <label for="autredescription<?= $obs['observation_number'] ?>">Précisez:</label>
                <input type="text" name="autreDescription<?= $obs['observation_number'] ?>" value="<?= $obs['autreDescription'] ?>">
              </div>
              <label for="date">Date:</label>
              <input type="date" name="date<?= $obs['observation_number'] ?>" value="<?= $obs['date'] ?>">
              <label for="heure">Heure:</label>
              <input type="time" name="heure<?= $obs['observation_number'] ?>" value="<?= $obs['heure'] ?>">
              <label for="observation">Observation:</label>
              <textarea name="observation<?= $obs['observation_number'] ?>" rows="5" cols="50" maxlength="1000"><?= $obs['texte'] ?></textarea>

              <?php if (!empty($observation_images[$obs['observation_number']])) : ?>
                <?php foreach ($observation_images[$obs['observation_number']] as $image) : ?>
                  <img src="data:image/jpeg;base64,<?= $image['image_base64'] ?>" alt="Photo d'observation <?= $obs['observation_number'] ?>" />
                <?php endforeach; ?>
              <?php else : ?>
                <input type="file" name="photo[<?= $obs['observation_number'] ?>]" accept="image/*" multiple>
              <?php endif; ?>

              <label for="entreprise<?= $obs['observation_number'] ?>">Entreprise:</label>
              <input type="text" name="entreprise<?= $obs['observation_number'] ?>" value="<?= $obs['entreprise'] ?>">
              <br>
              <label for="effectif<?= $obs['observation_number'] ?>">Effectif:</label>
              <input type="text" name="effectif<?= $obs['observation_number'] ?>" value="<?= $obs['effectif'] ?>">
              <br>
              <input type="submit" value="Enregistrer Observation <?= $obs['observation_number'] ?>">
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  </div>
</body>
<script>
  function openTab(evt, tabName) {
    var tabContent = document.getElementsByClassName("tab-content");
    Array.from(tabContent).forEach(tab => {
      tab.style.display = "none";
    });
    var tabLinks = document.getElementsByClassName("tab-link");
    Array.from(tabLinks).forEach(link => {
      link.classList.remove("active");
    });
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.classList.add("active");
  }

  function addInput(divName, event) {
    event.preventDefault();
    var newDiv = document.createElement('div');
    newDiv.className = 'personne-input';

    var newInput = document.createElement('input');
    newInput.type = 'text';
    newInput.name = 'personne' + counter;
    newInput.id = 'personne' + counter;
    newInput.required = true;

    var removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.className = 'remove-button';
    removeButton.textContent = 'x';
    removeButton.onclick = function(event) {
      removeInput(newDiv, event);
    };

    newDiv.appendChild(newInput);
    newDiv.appendChild(removeButton);

    document.getElementById(divName).appendChild(newDiv);
    counter++;
  }

  function removeInput(element, event) {
    event.preventDefault();
    element.parentNode.removeChild(element);
  }

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && e.target.nodeName !== 'TEXTAREA') {
      e.preventDefault();
    }
  });

  document.querySelectorAll('form input').forEach(function(input) {
    input.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' && e.target.nodeName !== 'TEXTAREA') {
        e.preventDefault();
      }
    });
  });

  $(document).ready(function() {
    $('#newObservationButton').click(function(e) {
      e.preventDefault();
      var chantierId = $('#chantier_id').val();
      $.ajax({
        url: 'genOnglet.php',
        type: 'POST',
        data: {
          chantier_id: chantierId
        },
        success: function(response) {
          var responseData = JSON.parse(response);
          if (responseData.last_observation_id) {
            $('#last_observation_id').val(responseData.last_observation_id);
            location.reload();
          } else {
            console.error("L'ID de la dernière observation n'a pas été reçu.");
          }
        },

        error: function(xhr, status, error) {
          console.error("Une erreur s'est produite: " + error);
        }
      });
    });
  });
  $(document).on('click', '.delete-observation', function(e) {
      e.preventDefault();
      var observationNumber = $(this).data('observation-number');
      var chantierId = $('#chantier_id').val();

      $.ajax({
        url: 'deleteObservation.php',
        type: 'POST',
        data: {
          observationNumber: observationNumber,
          chantier_id: chantierId
        },
        success: function(response) {
          console.log("Observation supprimée avec succès : ", response);
          location.reload();
        },
        error: function(xhr, status, error) {
          console.error("Une erreur s'est produite lors de la suppression : " + error);
        }
      });
    });
</script>

</html>