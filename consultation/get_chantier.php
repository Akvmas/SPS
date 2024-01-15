<?php
include '../config.php';

$sql = "SELECT id, description FROM chantiers";
$stmt = $pdo->prepare($sql);
$stmt->execute();

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $row) {
    echo "<option value='" . $row["id"] . "'>" . $row["description"] . "</option>";
}
