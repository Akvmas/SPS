<?php
try {
    $bdd = new pdo('mysql:host=yourhost;dbname=yourdb', 'youruser', 'yourpasswd');
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}
