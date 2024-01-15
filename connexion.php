<?php
try {
    $bdd = new pdo('mysql:host=localhost;dbname=sps', 'sps', '');
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}
