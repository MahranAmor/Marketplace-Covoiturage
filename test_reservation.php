<?php
// Test de débogage pour les réservations
session_start();
header('Content-Type: application/json');

// Afficher toutes les erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo json_encode([
    'session_user_id' => $_SESSION['user_id'] ?? 'NON_CONNECTE',
    'post_data' => $_POST,
    'php_version' => phpversion(),
    'files_exist' => [
        'DB.php' => file_exists('DB.php'),
        'reservation.php' => file_exists('reservation.php'),
        'creer_reservation.php' => file_exists('creer_reservation.php')
    ]
], JSON_PRETTY_PRINT);
?>
