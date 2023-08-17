<!DOCTYPE html>
<html>
  <head>
  <link rel = "stylesheet" href = "style.css">
  </head>
  <body>
  <h1>Choix du chantier</h1>
  <form action="get_chantiers.php" method="post">
    <select name="chantier">
      <?php 
        include 'get_chantier.php'; //Ce fichier devrait implémenter le code pour obtenir tous les chantiers depuis la base de données
      ?>
    </select>
    <br>
    <input type="submit">
  </form>
</body>
</html>
