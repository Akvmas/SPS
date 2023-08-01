<?php
include '../config.php'; 

$sql = "SELECT id, chantier FROM fon";
foreach($pdo->query($sql) as $row) {
    echo "<option value='".$row["id"]."'>".$row["chantier"]."</option>";
}
?>
