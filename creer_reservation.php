<?php
// creer_reservation.php : Créer une nouvelle réservation
session_start();
header('Content-Type: application/json');

require_once 'DB.php';
require_once 'reservation.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour réserver']);
    exit;
}

// Récupérer les données
$trajet_id = intval($_POST['trajet_id'] ?? 0);
$nombre_places = intval($_POST['nombre_places'] ?? 1);

if ($trajet_id <= 0 || $nombre_places <= 0) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

try {
    $db = new DB();
    $conn = $db->getConnection();
    
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données']);
        exit;
    }
    
    // LOG: Debug
    error_log("DEBUG - user_id: " . $_SESSION['user_id'] . ", trajet_id: " . $trajet_id . ", places: " . $nombre_places);
    
    // Vérifier que le trajet existe et récupérer son prix
    $query = "SELECT id, prix, places_disponibles, utilisateur_id FROM trajets WHERE id = ? AND statut IN ('actif', 'valide')";
    $stmt = $conn->prepare($query);
    $stmt->execute([$trajet_id]);
    $trajet = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // LOG: Trajet trouvé ou non
    error_log("DEBUG - Trajet trouvé: " . ($trajet ? "OUI (utilisateur_id={$trajet['utilisateur_id']})" : "NON"));
    
    if (!$trajet) {
        echo json_encode(['success' => false, 'message' => 'Trajet introuvable ou non disponible']);
        exit;
    }
    
    // Vérifier que l'utilisateur ne réserve pas son propre trajet
    if ($trajet['utilisateur_id'] == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas réserver votre propre trajet']);
        exit;
    }
    
    // Vérifier les places disponibles
    if ($trajet['places_disponibles'] < $nombre_places) {
        echo json_encode(['success' => false, 'message' => 'Pas assez de places disponibles']);
        exit;
    }
    
    // Créer la réservation
    $reservation = new Reservation($conn);
    $reservation->trajet_id = $trajet_id;
    $reservation->utilisateur_id = $_SESSION['user_id'];
    $reservation->nombre_places = $nombre_places;
    $reservation->prix_total = $trajet['prix'] * $nombre_places;
    $reservation->statut = 'confirmee';
    
    if ($reservation->creer()) {
        echo json_encode([
            'success' => true,
            'message' => 'Réservation effectuée avec succès !',
            'reservation_id' => $reservation->id,
            'prix_total' => $reservation->prix_total
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la réservation']);
    }
    
} catch (Exception $e) {
    error_log("Erreur réservation: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur technique. Veuillez réessayer.']);
}
?>
