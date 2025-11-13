<?php
// Test direct de login.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test de connexion</h2>";

// Simuler une requête POST
$_POST['email'] = 'test@test.com';
$_POST['password'] = 'test123';
$_POST['role'] = 'user';

echo "<p>Simulation de POST avec:</p>";
echo "<ul>";
echo "<li>Email: " . $_POST['email'] . "</li>";
echo "<li>Password: " . $_POST['password'] . "</li>";
echo "<li>Role: " . $_POST['role'] . "</li>";
echo "</ul>";

echo "<hr>";
echo "<h3>Exécution de login.php...</h3>";

// Capturer la sortie de login.php
ob_start();
include 'login.php';
$output = ob_get_clean();

echo "<pre style='background: #f4f4f4; padding: 10px; border: 1px solid #ddd;'>";
echo htmlspecialchars($output);
echo "</pre>";

echo "<hr>";
echo "<p><strong>Sortie JSON décodée:</strong></p>";
$json = json_decode($output, true);
if ($json) {
    echo "<pre>";
    print_r($json);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>La sortie n'est pas du JSON valide!</p>";
}
?>
