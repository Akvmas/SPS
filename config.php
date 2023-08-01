<?php
$host = 'localhost'; // l'adresse de votre serveur de base de données, généralement localhost ou 127.0.0.1
$db   = 'sps'; // le nom de votre base de données
$user = 'root'; // votre nom d'utilisateur pour la base de données
$pass = ''; // votre mot de passe pour la base de données

$dsn = "mysql:host=$host;dbname=$db";
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $opt);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
