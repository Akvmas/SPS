<!DOCTYPE html>
<html>

<head>
  <link rel="stylesheet" href="style.css">
  <script src="script.js" defer></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
  <div class="container">
    <form id="myForm" action="process.php" method="POST" enctype="multipart/form-data">
      <div class="part-one">
        <div class="input-group">
          <label for="chantier">Chantier:</label>
          <textarea name="chantier" id="chantier" rows="2" cols="50"></textarea>
        </div>
        <div class="input-group">
          <label for="maitreOuvrage">Maître d'Ouvrage:</label>
          <input type="text" name="maitreOuvrage" id="maitreOuvrage" Value="Eau17">
        </div>
        <div class="input-group">
          <label for="maitreOeuvre">Maître d'Œuvre:</label>
          <input type="text" name="maitreOeuvre" id="maitreOeuvre">
        </div>
        <div class="input-group">
          <label for="coordonnateurSPS">Coordonnateur S.P.S.:</label>
          <input type="text" name="coordonnateurSPS" id="coordonnateurSPS" Value="Gaël MONGARS">
        </div>
        <div class="input-group">
          <label>Personnes présentes:</label>
          <div id="dynamicInput">
            <div class="personne-input">
              <input type="text" name="personne[]" id="personne1">
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
        </div>
        <br>
        <div id="observation1" class="tab-content" style="display: block;">
          <label>Type de visite:</label>
          <div class="radio-buttons">
            <label for="reunion"><input type="radio" id="reunion1" name="typeVisite1" value="reunion">Réunion</label>
            <label for="visiteInopinee"><input type="radio" id="visiteInopinee1" name="typeVisite1" value="visiteInopinee">Visite inopinée</label>
            <label for="autre"><input type="radio" id="autre1" name="typeVisite1" value="autre">Autre</label>
          </div>
          <div class="input-group" id="autreText1" style="display: none;">
            <label for="autreDescription">Précisez:</label>
            <input type="text" name="autreDescription1" id="autreDescription1">
          </div>
          <label>Date:</label>
          <input type="date" name="date1" id="date1">
          <label>Heure:</label>
          <input type="time" name="heure1" id="heure1">
          <br>
          <textarea name="observation1" rows="5" cols="50" maxlength="1000" placeholder="Saisissez votre observation ici..."></textarea>
          <br>
          <input type="file" name="photos1[]" accept="image/*" multiple>
          <br>
          <label for="entreprise1">Entreprise:</label>
          <input type="text" name="entreprise1" id="entreprise1">
          <br>
          <label for="effectif1">Effectif:</label>
          <input type="text" name="effectif1" id="effectif1">
          <br>
        </div>
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
  </div>
</body>
</html>