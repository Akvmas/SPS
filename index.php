<?php
  // Initialiser la session
  session_start();
  // Vérifiez si l'utilisateur est connecté, sinon redirigez-le vers la page de connexion
  if(!isset($_SESSION["username"])){
    header("Location: login.php");
    exit(); 
  }
?>
<!DOCTYPE html>
<html>
  <head>
  <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
  </head>
  <body class="img js-fullheight" style="background-image: url(images/bg.jpeg);">
    <section class="ftco-section">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-6 text-center mb-5">
            <h1>Bienvenue <?php echo $_SESSION["username"]; ?>!</h1>
            <p>C'est votre espace utilisateur.</p>
          </div>
        </div>
        <div class="login-wrap p-0">
          <div class="form-group">
            <button name="Formulaires VIC" class = "form-control btn btn-primary submit px-3" id = FormulaireVIC value="Formulaire" onclick="self.location.href='formulairesVIC/FormulairesVIC.php'">Visite d'inspection commune</button>
            <br>
            </br>
            <button name="Formulaires FON" class = "form-control btn btn-primary submit px-3" id = "FormulaireFON"value="Formulaire" onclick="self.location.href='FormulairesFON/FormulaireFON.php'">Fiche d'observation ou de notification</button>
            <br>
          </br>
            <button name="Formulaires FON Consultation" class = "form-control btn btn-primary submit px-3" id = "Fonconsult" value="Formulaire" onclick="self.location.href='consultation/RechercheFon.php'">Consultation Fiche d'observation ou de notification</button>
          </div>
          <div class="container">
            <div class="login-wrap p-0">
              <div class="form-group">
                <a href="logout.php">Déconnexion</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <script src='js/jquery.min.js'></script>
    <script src='js/popper.js'></script>
    <script src='js/bootstrap.min.js'></script>
    <script src='js/main.js'></script>
  </body>
</html>
