<?php
header('Content-Type: application/json');

include_once "DB.php";
include_once "Trajet.php";

try {
    // Connexion à la base de données
    $database = new DB();
    $db = $database->getConnection();
    
    if ($db === null) {
        throw new Exception("Erreur de connexion à la base de données");
    }
    
    $trajet = new Trajet($db);
    
    // Lire tous les trajets disponibles (statut = 'valide' ou 'en_attente')
    $stmt = $trajet->lireTous();
    $trajets = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $trajets[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'trajets' => $trajets
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}
?>
