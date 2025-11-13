<?php
// mes_trajets.php : Gérer les trajets de l'utilisateur connecté
session_start();
header('Content-Type: application/json');

require_once 'DB.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit;
}

$action = $_GET['action'] ?? 'list';
$user_id = $_SESSION['user_id'];

try {
    $db = new DB();
    $conn = $db->getConnection();
    
    switch ($action) {
        case 'list':
            // Récupérer les trajets publiés par l'utilisateur
            $query = "SELECT t.*, 
                      (SELECT COUNT(*) FROM reservations WHERE trajet_id = t.id) as nb_reservations
                      FROM trajets t
                      WHERE t.utilisateur_id = ?
                      ORDER BY t.date_trajet DESC, t.heure_trajet DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute([$user_id]);
            $trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'trajets' => $trajets]);
            break;
            
        case 'mes_reservations':
            // Récupérer les réservations faites par l'utilisateur
            $query = "SELECT r.*, t.lieu_depart, t.lieu_arrivee, t.date_trajet, t.heure_trajet,
                      u.nom, u.prenom, u.telephone
                      FROM reservations r
                      JOIN trajets t ON r.trajet_id = t.id
                      JOIN utilisateurs u ON t.utilisateur_id = u.id
                      WHERE r.utilisateur_id = ?
                      ORDER BY t.date_trajet DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute([$user_id]);
            $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'reservations' => $reservations]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Action inconnue']);
    }
    
} catch (Exception $e) {
    error_log("Erreur mes_trajets.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
?>
