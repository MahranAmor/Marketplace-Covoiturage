<?php
session_start();

// Debug logs détaillés
$logFile = __DIR__ . '/debug_detailed.log';
file_put_contents($logFile, "\n=== SUPPRIMER_TRAJET.PHP START ===\n", FILE_APPEND);
file_put_contents($logFile, date('c') . ' METHOD: ' . ($_SERVER['REQUEST_METHOD'] ?? '-') . "\n", FILE_APPEND);
file_put_contents($logFile, date('c') . ' URI: ' . ($_SERVER['REQUEST_URI'] ?? '-') . "\n", FILE_APPEND);
file_put_contents($logFile, date('c') . ' POST: ' . json_encode($_POST) . "\n", FILE_APPEND);
file_put_contents($logFile, date('c') . ' Session user_id: ' . ($_SESSION['user_id'] ?? 'not set') . "\n", FILE_APPEND);
file_put_contents($logFile, date('c') . ' Session is_admin: ' . (isset($_SESSION['is_admin']) ? ($_SESSION['is_admin'] ? 'true' : 'false') : 'not set') . "\n", FILE_APPEND);

// Vérifier si l'utilisateur est connecté et est administrateur
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    file_put_contents($logFile, date('c') . ' ERROR: Access denied - not admin' . "\n=== SUPPRIMER_TRAJET.PHP END ===\n", FILE_APPEND);
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Accès refusé. Seuls les administrateurs peuvent supprimer des trajets.'
    ]);
    exit();
}

header('Content-Type: application/json');
include_once "DB.php";
include_once "Trajet.php";
file_put_contents($logFile, date('c') . ' Admin verified - Includes loaded' . "\n", FILE_APPEND);

// Connexion à la base de données
$database = new DB();
$db = $database->getConnection();
file_put_contents($logFile, date('c') . ' DB connected' . "\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'supprimer_trajet') {
    file_put_contents($logFile, date('c') . ' Action validated: supprimer_trajet' . "\n", FILE_APPEND);
    
    if (!isset($_POST['trajet_id'])) {
        file_put_contents($logFile, date('c') . ' ERROR: trajet_id missing' . "\n", FILE_APPEND);
        echo json_encode([
            'success' => false,
            'message' => 'ID du trajet manquant.'
        ]);
        exit();
    }
    
    file_put_contents($logFile, date('c') . ' Trajet ID: ' . $_POST['trajet_id'] . "\n", FILE_APPEND);
    
    $trajet = new Trajet($db);
    $trajet->id = $_POST['trajet_id'];
    
    try {
        file_put_contents($logFile, date('c') . ' Calling supprimerAdmin()...' . "\n", FILE_APPEND);
        if ($trajet->supprimerAdmin()) {
            file_put_contents($logFile, date('c') . ' SUCCESS: Trajet deleted' . "\n=== SUPPRIMER_TRAJET.PHP END ===\n", FILE_APPEND);
            echo json_encode([
                'success' => true,
                'message' => 'Trajet supprimé avec succès.'
            ]);
        } else {
            file_put_contents($logFile, date('c') . ' ERROR: supprimerAdmin() returned false' . "\n", FILE_APPEND);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la suppression du trajet.'
            ]);
        }
    } catch (Exception $e) {
        file_put_contents($logFile, date('c') . ' EXCEPTION: ' . $e->getMessage() . "\n", FILE_APPEND);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur: ' . $e->getMessage()
        ]);
    }
} else {
    file_put_contents($logFile, date('c') . ' ERROR: Invalid request - METHOD=' . $_SERVER['REQUEST_METHOD'] . ' ACTION=' . ($_POST['action'] ?? 'none') . "\n=== SUPPRIMER_TRAJET.PHP END ===\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Requête invalide.'
    ]);
}
?>
