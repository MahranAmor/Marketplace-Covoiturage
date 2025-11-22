<?php
require 'DB.php';

$db = new DB();
$conn = $db->getConnection();

// Créer un trajet avec l'admin (user_id = 1) pour permettre les tests
$stmt = $conn->prepare('
    INSERT INTO trajets 
    (utilisateur_id, lieu_depart, lieu_arrivee, date_trajet, heure_trajet, 
     places_disponibles, places_initiales, prix, vehicule, description, statut) 
    VALUES 
    (1, "Bizerte", "Nabeul", "2025-12-05", "09:00:00", 4, 4, 18, "Peugeot 308", "Trajet confortable avec l\'admin", "actif")
');

if ($stmt->execute()) {
    echo "✅ Trajet créé avec succès!\n";
    echo "ID: " . $conn->lastInsertId() . "\n";
    echo "Vous pouvez maintenant réserver ce trajet avec votre compte utilisateur (ID=2)\n";
} else {
    echo "❌ Erreur lors de la création du trajet\n";
}
?>
