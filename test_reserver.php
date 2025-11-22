<?php
session_start();
require_once 'DB.php';

if (!isset($_SESSION['user_id'])) {
    die("❌ Tu dois être connecté. <a href='index_one.html'>Se connecter</a>");
}

$db = new DB();
$conn = $db->getConnection();

echo "<h2>Test de Réservation</h2>";
echo "<p>Utilisateur connecté: ID = {$_SESSION['user_id']}</p>";

// Récupérer les trajets disponibles
$stmt = $conn->query("SELECT id, lieu_depart, lieu_arrivee, prix, places_disponibles, utilisateur_id 
                       FROM trajets WHERE statut IN ('actif', 'valide') ORDER BY id");

echo "<h3>Trajets disponibles:</h3>";

while ($trajet = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $peutReserver = ($trajet['utilisateur_id'] != $_SESSION['user_id']);
    $couleur = $peutReserver ? 'green' : 'red';
    
    echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
    echo "<strong>ID: {$trajet['id']}</strong> - {$trajet['lieu_depart']} → {$trajet['lieu_arrivee']}<br>";
    echo "Prix: {$trajet['prix']} DT | Places: {$trajet['places_disponibles']}<br>";
    echo "Créé par utilisateur ID: {$trajet['utilisateur_id']}<br>";
    echo "<span style='color: $couleur;'>";
    if ($peutReserver) {
        echo "✅ Tu peux réserver ce trajet<br>";
        echo "<form method='POST' action='test_reserver_action.php' style='display:inline;'>
                <input type='hidden' name='trajet_id' value='{$trajet['id']}'>
                <input type='number' name='nombre_places' value='1' min='1' max='{$trajet['places_disponibles']}' style='width: 60px;'>
                <button type='submit' style='padding: 5px 10px; background: blue; color: white; border: none; cursor: pointer;'>Réserver</button>
              </form>";
    } else {
        echo "❌ C'est TON trajet, tu ne peux pas le réserver";
    }
    echo "</span>";
    echo "</div>";
}
?>
