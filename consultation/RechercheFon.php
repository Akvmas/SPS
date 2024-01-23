<!DOCTYPE html>
<?php
// Initialiser la session
session_start();
// Vérifiez si l'utilisateur est connecté, sinon redirigez-le vers la page de connexion
if (!isset($_SESSION["username"])) {
  header("Location: ../login.php");
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
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="img js-fullheight" style="background-image: url(../images/bg.jpeg);">
  <section class="ftco-section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6 text-center mb-5">
          <h1>Choix de votre chantier</h1>
        </div>
      </div>
      <div class="login-wrap p-0">
        <div class="form-group">
          <form action="get_chantiers.php" method="post">
            <select class="form-control btn btn-primary submit px-3" name="chantier">
              <?php
              include 'get_chantier.php';
              ?>
              </select>
              <br>
              <br>
              <input class=" btn btn-primary submit px-3" value="Ouvrir" type="submit">
            </form>
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