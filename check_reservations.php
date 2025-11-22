<?php
require 'DB.php';

echo "=== VÉRIFICATION BASE DE DONNÉES ===\n\n";

$db = new DB();
$conn = $db->getConnection();

// 1. Vérifier la table reservations
echo "1. Structure de la table 'reservations':\n";
$stmt = $conn->query("DESCRIBE reservations");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("   - %s: %s %s\n", $row['Field'], $row['Type'], $row['Null'] === 'NO' ? '(requis)' : '');
}

// 2. Compter les réservations
echo "\n2. Nombre total de réservations: ";
$count = $conn->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
echo $count . "\n";

// 3. Afficher les dernières réservations
echo "\n3. Dernières réservations:\n";
$stmt = $conn->query("SELECT r.*, t.lieu_depart, t.lieu_arrivee, u.email 
                       FROM reservations r
                       LEFT JOIN trajets t ON r.trajet_id = t.id
                       LEFT JOIN utilisateurs u ON r.utilisateur_id = u.id
                       ORDER BY r.date_reservation DESC
                       LIMIT 5");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("   ID %d: %s réserve %s → %s (%d places, %s DT) [%s]\n",
        $row['id'],
        $row['email'],
        $row['lieu_depart'],
        $row['lieu_arrivee'],
        $row['nombre_places'],
        $row['prix_total'],
        $row['statut']
    );
}

echo "\n=== FIN VÉRIFICATION ===\n";
?>
