<?php
require_once 'DB.php';

$db = new DB();
$conn = $db->getConnection();

echo "<h2>Ajout de la colonne prix_total à la table reservations</h2>";

try {
    // Vérifier si la colonne existe déjà
    $stmt = $conn->query("DESCRIBE reservations");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('prix_total', $columns)) {
        echo "<p style='color: orange;'>⚠️ La colonne 'prix_total' existe déjà !</p>";
    } else {
        // Ajouter la colonne
        $conn->exec("ALTER TABLE reservations ADD COLUMN prix_total DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER nombre_places");
        echo "<p style='color: green; font-size: 20px;'>✅ Colonne 'prix_total' ajoutée avec succès !</p>";
    }
    
    // Afficher la nouvelle structure
    echo "<h3>Structure actuelle de la table reservations:</h3>";
    $stmt = $conn->query("DESCRIBE reservations");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        $style = $col['Field'] === 'prix_total' ? "background: #90EE90;" : "";
        echo "<tr style='$style'>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur: " . $e->getMessage() . "</p>";
}
?>
