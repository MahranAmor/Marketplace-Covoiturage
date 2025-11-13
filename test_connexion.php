<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "PHP fonctionne correctement !<br>";
echo "Version PHP : " . phpversion() . "<br>";

// Test de connexion à la base de données
include_once "DB.php";

try {
    $db = new DB();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "✅ Connexion à la base de données réussie !<br>";
        echo "Base de données : covoiturage<br>";
    } else {
        echo "❌ Erreur de connexion à la base de données<br>";
    }
} catch (Exception $e) {
    echo "❌ Exception : " . $e->getMessage() . "<br>";
}
?>
