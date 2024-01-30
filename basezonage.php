<?php
$pdo = new PDO('mysql:host=localhost;dbname=zonage', 'root', '');

$sql = "SELECT * FROM t_renseignements";
$stmt = $pdo->query($sql);

$donneesParINSEE = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $insee = $row['N°INSEE'];
    if ($insee >= 17002 && $insee <= 17486) {
        if (!isset($donneesParINSEE[$insee])) {
            $donneesParINSEE[$insee] = [];
        }
        $donneesParINSEE[$insee][] = $row;
    }
}

echo "<table border='1'>";

echo "<tr><th>INSEE</th>";

$maxChamps = 0;
foreach ($donneesParINSEE as $group) {
    foreach ($group as $ligne) {
        $nbChamps = count($ligne);
        if ($nbChamps > $maxChamps) {
            $maxChamps = $nbChamps;
        }
    }
}

for ($i = 1; $i <= $maxChamps; $i++) {
    echo "<th>Donnée $i</th>";
}
echo "</tr>";

foreach ($donneesParINSEE as $insee => $group) {
    echo "<tr>"; 
    echo "<td>" . htmlspecialchars($insee) . "</td>"; 
    foreach ($group as $ligne) {
        foreach ($ligne as $cle => $valeur) {
            echo "<td><b>" . htmlspecialchars($cle) . ":</b> " . htmlspecialchars($valeur) . "</td>";
        }
    }
    
    for ($i = count($group); $i < $maxChamps; $i++) {
        echo "<td></td>";
    }
    echo "</tr>"; 
}

echo "</table>"; 
?>
