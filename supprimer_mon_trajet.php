<?php
session_start();
header('Content-Type: application/json');

// Debug logs
$logFile = __DIR__ . '/debug_detailed.log';
file_put_contents($logFile, "\n=== SUPPRIMER_MON_TRAJET.PHP START ===\n", FILE_APPEND);
file_put_contents($logFile, date('c') . ' METHOD: ' . ($_SERVER['REQUEST_METHOD'] ?? '-') . "\n", FILE_APPEND);

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    file_put_contents($logFile, date('c') . ' ERROR: User not logged in' . "\n", FILE_APPEND);
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Vous devez être connecté pour supprimer un trajet.'
    ]);
    exit();
}

file_put_contents($logFile, date('c') . ' User ID: ' . $_SESSION['user_id'] . "\n", FILE_APPEND);

include_once "DB.php";
include_once "Trajet.php";

// Connexion à la base de données
$database = new DB();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'supprimer_mon_trajet') {
    
    if (!isset($_POST['trajet_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID du trajet manquant.'
        ]);
        exit();
    }
    
    $trajet = new Trajet($db);
    $trajet->id = $_POST['trajet_id'];
    $trajet->utilisateur_id = $_SESSION['user_id'];
    
    file_put_contents($logFile, date('c') . ' Deleting trajet ID: ' . $_POST['trajet_id'] . "\n", FILE_APPEND);
    
    try {
        // Utiliser la méthode supprimer() qui vérifie que l'utilisateur est le propriétaire
        if ($trajet->supprimer()) {
            echo json_encode([
                'success' => true,
                'message' => 'Votre trajet a été supprimé avec succès.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur: Vous ne pouvez supprimer que vos propres trajets ou le trajet n\'existe pas.'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Requête invalide.'
    ]);
}
?>
