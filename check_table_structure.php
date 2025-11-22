<?php
require_once 'DB.php';

$db = new DB();
$conn = $db->getConnection();

echo "<h2>Structure de la table reservations</h2>";

$stmt = $conn->query("DESCRIBE reservations");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
foreach ($columns as $col) {
    echo "<tr>";
    echo "<td>{$col['Field']}</td>";
    echo "<td>{$col['Type']}</td>";
    echo "<td>{$col['Null']}</td>";
    echo "<td>{$col['Key']}</td>";
    echo "<td>{$col['Default']}</td>";
    echo "<td>{$col['Extra']}</td>";
    echo "</tr>";
}
echo "</table>";
?>
