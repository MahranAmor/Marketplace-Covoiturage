<?php
session_start();
require_once 'DB.php';

if (!isset($_SESSION['user_id'])) {
    die("❌ Non connecté");
}

$db = new DB();
$conn = $db->getConnection();
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "<h2>Test Réservation Direct SQL</h2>";

// Récupérer un trajet disponible
$stmt = $conn->query("SELECT id, lieu_depart, lieu_arrivee, prix, places_disponibles, utilisateur_id 
                       FROM trajets WHERE statut IN ('actif', 'valide') 
                       AND utilisateur_id != {$_SESSION['user_id']} 
                       LIMIT 1");

$trajet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$trajet) {
    die("<p style='color: red;'>❌ Aucun trajet disponible que tu ne possèdes pas</p>");
}

echo "<p>Trajet sélectionné: {$trajet['lieu_depart']} → {$trajet['lieu_arrivee']}</p>";
echo "<p>ID Trajet: {$trajet['id']}</p>";
echo "<p>Ton user_id: {$_SESSION['user_id']}</p>";
echo "<hr>";

try {
    // Commencer transaction
    $conn->beginTransaction();
    echo "<p>✅ Transaction démarrée</p>";
    
    // Vérifier places avec FOR UPDATE
    $stmt_check = $conn->prepare("SELECT places_disponibles FROM trajets WHERE id = ? FOR UPDATE");
    $stmt_check->execute([$trajet['id']]);
    $places = $stmt_check->fetch(PDO::FETCH_ASSOC);
    echo "<p>✅ Places disponibles vérifiées: {$places['places_disponibles']}</p>";
    
    if ($places['places_disponibles'] < 1) {
        throw new Exception("Pas assez de places");
    }
    
    // Insérer réservation
    $prix_total = $trajet['prix'] * 1;
    $stmt_insert = $conn->prepare("INSERT INTO reservations (trajet_id, utilisateur_id, nombre_places, prix_total, statut) 
                                    VALUES (?, ?, ?, ?, 'confirmee')");
    $stmt_insert->execute([$trajet['id'], $_SESSION['user_id'], 1, $prix_total]);
    $reservation_id = $conn->lastInsertId();
    echo "<p>✅ Réservation insérée avec ID: $reservation_id</p>";
    
    // Décrémenter places
    $stmt_update = $conn->prepare("UPDATE trajets SET places_disponibles = places_disponibles - 1 WHERE id = ?");
    $stmt_update->execute([$trajet['id']]);
    echo "<p>✅ Places mises à jour</p>";
    
    // Commit
    $conn->commit();
    echo "<p style='color: green; font-size: 20px;'>✅ RÉSERVATION RÉUSSIE !</p>";
    echo "<p>Réservation ID: $reservation_id</p>";
    echo "<p>Prix total: $prix_total DT</p>";
    
} catch (Exception $e) {
    $conn->rollBack();
    echo "<p style='color: red;'>❌ ERREUR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
