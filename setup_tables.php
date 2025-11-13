<?php
// Script pour créer les tables automatiquement
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Installation de la base de données</h2>";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=covoiturage;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✅ Connexion à la base de données réussie</p>";
    
    // Créer la table utilisateurs
    $sql_utilisateurs = "CREATE TABLE IF NOT EXISTS utilisateurs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        prenom VARCHAR(100),
        email VARCHAR(150) UNIQUE NOT NULL,
        telephone VARCHAR(20),
        mot_de_passe VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        statut ENUM('actif', 'inactif', 'bloque') DEFAULT 'actif',
        date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_role (role)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql_utilisateurs);
    echo "<p style='color: green;'>✅ Table 'utilisateurs' créée</p>";
    
    // Créer la table trajets
    $sql_trajets = "CREATE TABLE IF NOT EXISTS trajets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        utilisateur_id INT NOT NULL,
        lieu_depart VARCHAR(200) NOT NULL,
        lieu_arrivee VARCHAR(200) NOT NULL,
        date_trajet DATE NOT NULL,
        heure_trajet TIME NOT NULL,
        places_disponibles INT NOT NULL,
        places_initiales INT NOT NULL,
        prix DECIMAL(10, 2) NOT NULL,
        vehicule VARCHAR(100),
        description TEXT,
        preferences TEXT,
        statut ENUM('en_attente', 'actif', 'complet', 'annule', 'valide', 'refuse') DEFAULT 'en_attente',
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
        INDEX idx_date (date_trajet),
        INDEX idx_depart (lieu_depart),
        INDEX idx_arrivee (lieu_arrivee),
        INDEX idx_statut (statut),
        INDEX idx_utilisateur (utilisateur_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql_trajets);
    echo "<p style='color: green;'>✅ Table 'trajets' créée</p>";
    
    // Créer la table reservations
    $sql_reservations = "CREATE TABLE IF NOT EXISTS reservations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        trajet_id INT NOT NULL,
        utilisateur_id INT NOT NULL,
        nombre_places INT NOT NULL DEFAULT 1,
        statut ENUM('en_attente', 'confirmee', 'annulee') DEFAULT 'en_attente',
        date_reservation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (trajet_id) REFERENCES trajets(id) ON DELETE CASCADE,
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
        INDEX idx_trajet (trajet_id),
        INDEX idx_utilisateur (utilisateur_id),
        INDEX idx_statut (statut)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql_reservations);
    echo "<p style='color: green;'>✅ Table 'reservations' créée</p>";
    
    // Insérer l'admin par défaut (mot de passe: admin123)
    $sql_admin = "INSERT INTO utilisateurs (nom, prenom, email, telephone, mot_de_passe, role, statut) 
    VALUES (
        'Admin',
        'Système',
        'admin@covoiturage.com',
        '0000000000',
        '" . password_hash('admin123', PASSWORD_DEFAULT) . "',
        'admin',
        'actif'
    ) ON DUPLICATE KEY UPDATE id=id";
    
    $pdo->exec($sql_admin);
    echo "<p style='color: green;'>✅ Compte admin créé (email: admin@covoiturage.com, mot de passe: admin123)</p>";
    
    // Insérer l'utilisateur test (mot de passe: test123)
    $sql_test = "INSERT INTO utilisateurs (nom, prenom, email, telephone, mot_de_passe, role, statut) 
    VALUES (
        'Test',
        'Utilisateur',
        'test@test.com',
        '1234567890',
        '" . password_hash('test123', PASSWORD_DEFAULT) . "',
        'user',
        'actif'
    ) ON DUPLICATE KEY UPDATE id=id";
    
    $pdo->exec($sql_test);
    echo "<p style='color: green;'>✅ Compte test créé (email: test@test.com, mot de passe: test123)</p>";
    
    // Insérer quelques trajets de test
    $sql_trajets_test = "INSERT INTO trajets (utilisateur_id, lieu_depart, lieu_arrivee, date_trajet, heure_trajet, places_disponibles, places_initiales, prix, vehicule, description, statut) 
    SELECT 
        (SELECT id FROM utilisateurs WHERE email='test@test.com'),
        'Tunis',
        'Sfax',
        '2025-12-01',
        '08:00:00',
        3,
        3,
        15.00,
        'Renault Clio',
        'Trajet confortable',
        'actif'
    FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM trajets WHERE lieu_depart='Tunis' AND lieu_arrivee='Sfax' LIMIT 1)";
    
    $pdo->exec($sql_trajets_test);
    echo "<p style='color: green;'>✅ Trajets de test créés</p>";
    
    // Vérification finale
    $count_users = $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
    $count_trajets = $pdo->query("SELECT COUNT(*) FROM trajets")->fetchColumn();
    
    echo "<hr>";
    echo "<h3 style='color: green;'>✅ Installation terminée avec succès !</h3>";
    echo "<p>👤 Nombre d'utilisateurs : <strong>$count_users</strong></p>";
    echo "<p>🚗 Nombre de trajets : <strong>$count_trajets</strong></p>";
    echo "<hr>";
    echo "<p><strong>Vous pouvez maintenant tester l'application :</strong></p>";
    echo "<ul>";
    echo "<li>Email: <code>test@test.com</code> - Mot de passe: <code>test123</code> (Utilisateur)</li>";
    echo "<li>Email: <code>admin@covoiturage.com</code> - Mot de passe: <code>admin123</code> (Admin)</li>";
    echo "</ul>";
    echo "<p><a href='index_one.html' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px;'>🚀 Aller à l'application</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erreur : " . $e->getMessage() . "</p>";
}
?>
