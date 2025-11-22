<?php
session_start();
require_once 'DB.php';
require_once 'reservation.php';

if (!isset($_SESSION['user_id'])) {
    die("❌ Non connecté");
}

$trajet_id = intval($_POST['trajet_id'] ?? 0);
$nombre_places = intval($_POST['nombre_places'] ?? 1);

echo "<h2>Résultat de la réservation</h2>";
echo "<p>User ID: {$_SESSION['user_id']}</p>";
echo "<p>Trajet ID: $trajet_id</p>";
echo "<p>Nombre de places: $nombre_places</p>";
echo "<hr>";

try {
    $db = new DB();
    $conn = $db->getConnection();
    
    // Récupérer le trajet
    $stmt = $conn->prepare("SELECT id, prix, places_disponibles, utilisateur_id, lieu_depart, lieu_arrivee 
                            FROM trajets WHERE id = ? AND statut IN ('actif', 'valide')");
    $stmt->execute([$trajet_id]);
    $trajet = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$trajet) {
        echo "<p style='color: red;'>❌ Trajet introuvable ou non disponible</p>";
        echo "<a href='test_reserver.php'>Retour</a>";
        exit;
    }
    
    echo "<p>Trajet trouvé: {$trajet['lieu_depart']} → {$trajet['lieu_arrivee']}</p>";
    echo "<p>Créé par utilisateur ID: {$trajet['utilisateur_id']}</p>";
    
    if ($trajet['utilisateur_id'] == $_SESSION['user_id']) {
        echo "<p style='color: red;'>❌ Tu ne peux pas réserver ton propre trajet!</p>";
        echo "<a href='test_reserver.php'>Retour</a>";
        exit;
    }
    
    if ($trajet['places_disponibles'] < $nombre_places) {
        echo "<p style='color: red;'>❌ Pas assez de places disponibles</p>";
        echo "<a href='test_reserver.php'>Retour</a>";
        exit;
    }
    
    // Activer les erreurs PDO
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Créer la réservation
    $reservation = new Reservation($conn);
    $reservation->trajet_id = $trajet_id;
    $reservation->utilisateur_id = $_SESSION['user_id'];
    $reservation->nombre_places = $nombre_places;
    $reservation->prix_total = $trajet['prix'] * $nombre_places;
    $reservation->statut = 'confirmee';
    
    echo "<p>Tentative de réservation avec:</p>";
    echo "<pre>";
    echo "trajet_id: $trajet_id\n";
    echo "utilisateur_id: {$_SESSION['user_id']}\n";
    echo "nombre_places: $nombre_places\n";
    echo "prix_total: {$reservation->prix_total}\n";
    echo "statut: confirmee\n";
    echo "</pre>";
    
    if ($reservation->creer()) {
        echo "<p style='color: green; font-size: 20px;'>✅ RÉSERVATION RÉUSSIE !</p>";
        echo "<p>Réservation ID: {$reservation->id}</p>";
        echo "<p>Prix total: {$reservation->prix_total} DT</p>";
    } else {
        echo "<p style='color: red;'>❌ Erreur lors de la création de la réservation</p>";
        echo "<p>Vérifie le fichier C:\\xampp\\apache\\logs\\error.log pour plus de détails</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<a href='test_reserver.php'>Retour à la liste</a>";
?>
