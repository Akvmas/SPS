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
<html>

<head>
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <h1>Choix du chantier</h1>
  <form action="get_chantiers.php" method="post">
    <select name="chantier" style="width:500px;">
      <?php
      include 'get_chantier.php';
      ?>
    </select>
    <br>
    <input type="submit">
  </form>
</body>

</html>