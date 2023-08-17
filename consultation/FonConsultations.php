<?php
session_start();
$chantier = $_SESSION['chantier'];
$personnes = $_SESSION['personnes'];
$observations = $_SESSION['observations'];
?>
<!DOCTYPE html>
<html>
<head>
  <link rel = "stylesheet" href = "style.css">
  <script src="script.js" defer></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
<button id="toggleButton" onclick="toggleMode()">Switch to Edit Mode</button>
  <div class="container">
    <form id="myForm" action="upload.php" method="POST" enctype="multipart/form-data">
      <div class="part-one">
        <!-- Les noms de champs ont été ajustés pour mieux correspondre aux noms de tables et de champs -->
        <div class="input-group">
          <label for="chantierNom">Chantier:</label>
          <textarea name="chantierNom" id="chantierNom" rows="2" cols="50" required><?php echo $chantier['nom']; ?></textarea>
        </div>
        <div class="input-group">
          <label for="maitreOuvrage">Maître d'Ouvrage:</label>
          <input type="text" name="maitreOuvrage" id="maitreOuvrage" Value="<?php echo $chantier['maitreOuvrage']; ?>">
        </div>
        <div class="input-group">
          <label for="maitreOeuvre">Maître d'Œuvre:</label>
          <input type="text" name="maitreOeuvre" id="maitreOeuvre" Value="<?php echo $chantier['maitreOeuvre']; ?>" required>
        </div>
        <div class="input-group">
          <label for="coordonnateurSPS">Coordonnateur S.P.S.:</label>
          <input type="text" name="coordonnateurSPS" id="coordonnateurSPS" Value="<?php echo $chantier['coordonnateurSPS']; ?>">
        </div>
        <div class="input-group">
          <label>Personnes présentes:</label>
          <div id="dynamicInput">
            <?php 
            $i = 1;
            foreach ($personnes as $personne) {
              echo '<div class="personne-input">
                    <input type="text" name="personne'.$i.'" id="personne'.$i.'" Value="'.$personne['nom'].'" required>
                    <button type="button" class="remove-button" onclick="removeInput(this)">x</button>
                    <button type="button" onclick="addInput(\'dynamicInput\')">+</button>
                    </div>';
              $i++;
            }
            ?>
          </div>
        </div>
        <div class="input-group">
          <label>Date:</label>
          <input type="date" name="date" id="date" Value="<?php echo $chantier['date']; ?>" required>
          <label>Heure:</label>
          <input type="time" name="heure" id="heure" Value="<?php echo $chantier['heure']; ?>" required>
        </div>
        <label>Type de visite:</label>
        <div class="radio-buttons">
          <label for="reunion"><input type="radio" id="reunion" name="typeVisite" value="reunion" <?php if($chantier['typeVisite'] == 'reunion') echo 'checked'; ?>>Réunion</label>
          <label for="visiteInopinee"><input type="radio" id="visiteInopinee" name="typeVisite" value="visiteInopinee" <?php if($chantier['typeVisite'] == 'visiteInopinee') echo 'checked'; ?>>Visite inopinée</label>
          <label for="autre"><input type="radio" id="autre" name="typeVisite" value="autre" <?php if($chantier['typeVisite'] == 'autre') echo 'checked'; ?>>Autre</label>
        </div>
        <div class="input-group" id="autreText" style="display: none;">
          <label for="autreDescription">Précisez:</label>
          <input type="text" name="autreDescription" id="autreDescription" Value="<?php echo $chantier['autreDescription']; ?>">
        </div>
      </div>
      <div class="text-divider"></div>
      <div class="part-two">
        <button onclick="addObservation()">+</button>
        <button onclick="removeObservation()">x</button>
        <div id="tabs">
          <?php 
          $i = 1;
          foreach ($observations as $observation) {
            echo '<button class="tab-link '.($i == 1 ? 'active' : '').'" onclick="openTab(event, \'observation'.$i.'\')">Observation '.$i.'</button>';
            $i++;
          }
          ?>
        </div>
        <?php 
        $i = 1;
        foreach ($observations as $observation) {
          echo '<div id="observation'.$i.'" class="tab-content" style="display: '.($i == 1 ? 'block' : 'none').'">
                <textarea name="observationText'.$i.'" rows="5" cols="50" maxlength="1000" required>'.$observation['texte'].'</textarea>
                <br>
                <input type="file" name="photo'.$i.'" accept="image/*" required>
                <br>
                <label for="entreprise'.$i.'">Entreprise:</label>
                <input type="text" name="entreprise'.$i.'" id="entreprise'.$i.'" Value="'.$observation['entreprise'].'" required>
                <br>
                <label for="effectif'.$i.'">Effectif:</label>
                <input type="text" name="effectif'.$i.'" id="effectif'.$i.'" Value="'.$observation['effectif'].'" required>
                <br>
                </div>';
          $i++;
        }
        ?>
      </div>
      <input type="submit" value="Envoyer" name="submit">
    </form>
  </div>
</body>
</html>
