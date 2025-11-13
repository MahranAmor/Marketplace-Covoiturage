<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Base de Données</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #667eea; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .btn {
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
        }
        .btn:hover { background: #5568d3; }
        pre {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background: #667eea; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Installation Base de Données - Covoiturage</h1>
        
        <?php
        if (isset($_POST['install'])) {
            try {
                // Connexion à MySQL sans sélectionner de base
                $conn = new PDO(
                    "mysql:host=localhost;charset=utf8mb4",
                    "root",
                    "",
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                echo "<div class='info'>✓ Connexion à MySQL réussie</div>";
                
                // Créer la base de données
                $conn->exec("CREATE DATABASE IF NOT EXISTS covoiturage CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                echo "<div class='success'>✓ Base de données 'covoiturage' créée/vérifiée</div>";
                
                // Sélectionner la base
                $conn->exec("USE covoiturage");
                
                // Créer la table utilisateurs
                $conn->exec("
                    CREATE TABLE IF NOT EXISTS utilisateurs (
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
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                echo "<div class='success'>✓ Table 'utilisateurs' créée</div>";
                
                // Créer la table trajets
                $conn->exec("
                    CREATE TABLE IF NOT EXISTS trajets (
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
                        INDEX idx_statut (statut)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                echo "<div class='success'>✓ Table 'trajets' créée</div>";
                
                // Créer la table réservations
                $conn->exec("
                    CREATE TABLE IF NOT EXISTS reservations (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        trajet_id INT NOT NULL,
                        utilisateur_id INT NOT NULL,
                        nombre_places INT NOT NULL DEFAULT 1,
                        statut ENUM('en_attente', 'confirmee', 'annulee') DEFAULT 'en_attente',
                        date_reservation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (trajet_id) REFERENCES trajets(id) ON DELETE CASCADE,
                        FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                echo "<div class='success'>✓ Table 'reservations' créée</div>";
                
                // Insérer utilisateurs par défaut
                $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
                $stmt = $conn->prepare("
                    INSERT IGNORE INTO utilisateurs (nom, prenom, email, telephone, mot_de_passe, role, statut) 
                    VALUES ('Admin', 'Système', 'admin@covoiturage.com', '0000000000', ?, 'admin', 'actif')
                ");
                $stmt->execute([$passwordHash]);
                echo "<div class='success'>✓ Compte admin créé (email: admin@covoiturage.com, mot de passe: admin123)</div>";
                
                $passwordHash = password_hash('test123', PASSWORD_DEFAULT);
                $stmt = $conn->prepare("
                    INSERT IGNORE INTO utilisateurs (nom, prenom, email, telephone, mot_de_passe, role, statut) 
                    VALUES ('Test', 'Utilisateur', 'test@test.com', '1234567890', ?, 'user', 'actif')
                ");
                $stmt->execute([$passwordHash]);
                echo "<div class='success'>✓ Compte test créé (email: test@test.com, mot de passe: test123)</div>";
                
                // Afficher les statistiques
                $stmt = $conn->query("SELECT COUNT(*) as total FROM utilisateurs");
                $count = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<div class='info'>📊 Total utilisateurs: " . $count['total'] . "</div>";
                
                $stmt = $conn->query("SELECT COUNT(*) as total FROM trajets");
                $count = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<div class='info'>📊 Total trajets: " . $count['total'] . "</div>";
                
                echo "<div class='success'><h3>✅ Installation terminée avec succès!</h3></div>";
                echo "<a href='index_one.html' class='btn'>🚗 Aller à l'application</a>";
                echo "<a href='test_endpoints.html' class='btn'>🔬 Tester les endpoints</a>";
                
            } catch (PDOException $e) {
                echo "<div class='error'><strong>❌ Erreur:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
                echo "<div class='info'><strong>Solution:</strong> Vérifiez que MySQL est démarré dans XAMPP et que les paramètres de connexion sont corrects.</div>";
            }
        } else {
            ?>
            <div class="info">
                <h3>📋 Cette page va créer:</h3>
                <ul>
                    <li>La base de données <strong>covoiturage</strong></li>
                    <li>Les tables: <strong>utilisateurs</strong>, <strong>trajets</strong>, <strong>reservations</strong></li>
                    <li>Un compte admin: <code>admin@covoiturage.com</code> / <code>admin123</code></li>
                    <li>Un compte test: <code>test@test.com</code> / <code>test123</code></li>
                </ul>
            </div>
            
            <form method="POST">
                <button type="submit" name="install" class="btn">🚀 Installer la base de données</button>
            </form>
            
            <div style="margin-top: 30px;">
                <h3>Ou exécuter le script SQL manuellement:</h3>
                <p>Si vous préférez, vous pouvez ouvrir <strong>phpMyAdmin</strong> (http://localhost/phpmyadmin) et importer le fichier <code>create_database.sql</code></p>
            </div>
            <?php
        }
        ?>
    </div>
</body>
</html>
