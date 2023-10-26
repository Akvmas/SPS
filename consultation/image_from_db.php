<?php
require('../config.php');  // Assurez-vous que ceci initialise votre objet PDO $pdo

if(isset($_GET['observation_id'])){
    $stmt = $pdo->prepare("SELECT photo FROM observations WHERE observation_id = ?");
    $stmt->execute([$_GET['observation_id']]);
    $image = $stmt->fetchColumn();

    header("Content-Type: image/jpeg");  // ou image/png, ou image/gif selon votre BLOB
    echo $image;
}
