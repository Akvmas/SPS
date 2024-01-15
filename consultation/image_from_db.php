<?php
require('../config.php');

if (isset($_GET['observation_id'])) {
    $stmt = $pdo->prepare("SELECT photo FROM observations WHERE observation_id = ?");
    $stmt->execute([$_GET['observation_id']]);
    $image = $stmt->fetchColumn();

    header("Content-Type: image/jpeg");
    echo $image;
}
