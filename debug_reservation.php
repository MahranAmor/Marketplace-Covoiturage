<?php
session_start();
require_once 'DB.php';

echo "<h2>Debug - État actuel</h2>";

// Utilisateur connecté
echo "<h3>Session actuelle:</h3>";
echo "user_id: " . ($_SESSION['user_id'] ?? 'NON CONNECTÉ') . "<br>";
echo "is_admin: " . ($_SESSION['is_admin'] ?? 'NON') . "<br>";

// Liste des utilisateurs
$db = new DB();
$conn = $db->getConnection();

echo "<h3>Utilisateurs dans la base:</h3>";
$stmt = $conn->query("SELECT id, nom, prenom, email, is_admin FROM utilisateurs");
while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID={$user['id']}: {$user['nom']} {$user['prenom']} - {$user['email']} - Admin: " . ($user['is_admin'] ? 'OUI' : 'NON') . "<br>";
}

// Liste des trajets
echo "<h3>Trajets disponibles:</h3>";
$stmt = $conn->query("SELECT id, lieu_depart, lieu_arrivee, utilisateur_id, statut FROM trajets WHERE statut IN ('actif', 'valide')");
while ($trajet = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID={$trajet['id']}: {$trajet['lieu_depart']} → {$trajet['lieu_arrivee']} (Créé par user_id={$trajet['utilisateur_id']}, statut={$trajet['statut']})<br>";
}

echo "<hr>";
echo "<p><strong>Problème:</strong> Si ton user_id est le même que celui des trajets, tu ne peux pas réserver.</p>";
echo "<p><strong>Solution:</strong> Connecte-toi avec un autre compte ou crée un nouveau compte.</p>";
?>
