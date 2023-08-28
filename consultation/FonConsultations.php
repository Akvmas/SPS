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
<div class="container">
    <form id="myForm" action="process.php" method="POST" enctype="multipart/form-data">
      <div class="part-one">
        <div class="input-group">
          <input type="hidden" name="chantier_id" value="<?php echo $chantier['id']; ?>">
          <label for="chantierNom">Chantier:</label>
          <textarea name="chantierNom" id="chantierNom" rows="2" cols="50" ><?php echo $chantier['description']; ?></textarea>
        </div>
        <div class="input-group">
          <label for="maitreOuvrage">Maître d'Ouvrage:</label>
          <input type="text" name="maitreOuvrage" id="maitreOuvrage" Value="<?php echo $chantier['maitreOuvrage']; ?>">
        </div>
        <div class="input-group">
          <label for="maitreOeuvre">Maître d'Œuvre:</label>
          <input type="text" name="maitreOeuvre" id="maitreOeuvre" Value="<?php echo $chantier['maitreOeuvre']; ?>" >
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
                    <input type="text" name="personne'.$i.'" id="personne'.$i.'" Value="'.$personne['nom'].'" >
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
          <input type="date" name="date" id="date" Value="<?php echo $chantier['date']; ?>" >
          <label>Heure:</label>
          <input type="time" name="heure" id="heure" Value="<?php echo $chantier['heure']; ?>" >
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
        <button type="button" onclick="addObservation()">+</button>
        <button type="button" onclick="removeObservation()">x</button>
        <div id="tabs">
            <?php 
            for ($i = 1; $i <= count($observations) + 1; $i++) {
                echo '<button type="button" class="tab-link '.($i == 1 ? 'active' : '').'" onclick="openTab(event, \'observation'.$i.'\')">Observation '.$i.'</button>';
            }
            ?>
        </div>
        <?php 
        for ($i = 1; $i <= count($observations) + 1; $i++) {
          $currentObservation = $observations[$i-1] ?? [];
          $text = $currentObservation['texte'] ?? '';
          $entreprise = $currentObservation['entreprise'] ?? '';
          $effectif = $currentObservation['effectif'] ?? '';
          $photo = $currentObservation['photo'] ?? '';
          echo '<div id="observation'.$i.'" class="tab-content" style="display: '.($i == 1 ? 'block' : 'none').'">
                  <textarea name="observationText'.$i.'" rows="5" cols="50" maxlength="1000">'.$text.'</textarea>
                  <br>';

          // Si une photo existe pour cette observation, affichez-la
          if (!empty($photo)) {
              echo '<img src="../ImagesChantier/'.$photo.'" alt="Observation Photo" width="500">';
          } else {
              // Si aucune photo n'existe, affichez l'input pour ajouter une nouvelle photo
              echo '<input type="file" name="photo'.$i.'" accept="image/*"><br>';
          }

          echo '<label for="entreprise'.$i.'">Entreprise:</label>
                <input type="text" name="entreprise'.$i.'" id="entreprise'.$i.'" Value="'.$entreprise.'">
                <br>
                <label for="effectif'.$i.'">Effectif:</label>
                <input type="text" name="effectif'.$i.'" id="effectif'.$i.'" Value="'.$effectif.'">
                <br>
                </div>';
      }
        ?>
      </div>
      <input type="submit" value="Envoyer" name="submit">
    </div>
    </form>
</body>
</html>