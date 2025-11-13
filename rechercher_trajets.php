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
    
    // Récupérer les critères de recherche
    $criteres = [];
    
    if (isset($_GET['lieu_depart']) && !empty($_GET['lieu_depart'])) {
        $criteres['lieu_depart'] = $_GET['lieu_depart'];
    }
    
    if (isset($_GET['lieu_arrivee']) && !empty($_GET['lieu_arrivee'])) {
        $criteres['lieu_arrivee'] = $_GET['lieu_arrivee'];
    }
    
    if (isset($_GET['date_trajet']) && !empty($_GET['date_trajet'])) {
        $criteres['date_trajet'] = $_GET['date_trajet'];
    }
    
    // Rechercher les trajets
    $stmt = $trajet->rechercher($criteres);
    $trajets = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Ajouter le nom complet du conducteur
        $row['conducteur_nom'] = trim(($row['nom'] ?? '') . ' ' . ($row['prenom'] ?? ''));
        $trajets[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'trajets' => $trajets,
        'count' => count($trajets)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}
?>
