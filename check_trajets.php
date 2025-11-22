<?php
require 'DB.php';
$db = new DB();
$conn = $db->getConnection();
$stmt = $conn->query('SELECT id, lieu_depart, lieu_arrivee, places_disponibles, statut FROM trajets LIMIT 5');
echo "Trajets disponibles:\n";
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("ID: %d | %s -> %s | Places: %d | Statut: %s\n", 
        $row['id'], 
        $row['lieu_depart'], 
        $row['lieu_arrivee'], 
        $row['places_disponibles'], 
        $row['statut']
    );
}
?>
