-- Script de création des tables pour l'application de covoiturage
-- Exécutez ce script dans phpMyAdmin ou via MySQL CLI

-- Créer la base de données si elle n'existe pas
CREATE DATABASE IF NOT EXISTS covoiturage CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE covoiturage;

-- Table des utilisateurs
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des trajets
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
    INDEX idx_statut (statut),
    INDEX idx_utilisateur (utilisateur_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des réservations
CREATE TABLE IF NOT EXISTS reservations (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insérer un utilisateur admin par défaut
-- Mot de passe: admin123 (à changer après première connexion)
INSERT INTO utilisateurs (nom, prenom, email, telephone, mot_de_passe, role, statut) 
VALUES (
    'Admin',
    'Système',
    'admin@covoiturage.com',
    '0000000000',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- admin123
    'admin',
    'actif'
) ON DUPLICATE KEY UPDATE id=id;

-- Insérer un utilisateur test
-- Mot de passe: test123
INSERT INTO utilisateurs (nom, prenom, email, telephone, mot_de_passe, role, statut) 
VALUES (
    'Test',
    'Utilisateur',
    'test@test.com',
    '1234567890',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- test123
    'user',
    'actif'
) ON DUPLICATE KEY UPDATE id=id;

-- Insérer quelques trajets de test
INSERT INTO trajets (utilisateur_id, lieu_depart, lieu_arrivee, date_trajet, heure_trajet, places_disponibles, places_initiales, prix, vehicule, description, statut) 
VALUES 
(2, 'Tunis', 'Sfax', '2025-12-01', '08:00:00', 3, 3, 15.00, 'Renault Clio', 'Trajet confortable', 'actif'),
(2, 'Sousse', 'Monastir', '2025-12-02', '14:00:00', 2, 3, 10.00, 'Peugeot 208', 'Départ centre-ville', 'actif'),
(2, 'Gabes', 'Medenine', '2025-12-03', '10:00:00', 4, 4, 20.00, 'Toyota Corolla', 'Climatisation', 'actif')
ON DUPLICATE KEY UPDATE id=id;

-- Vérifier que tout est OK
SELECT 'Tables créées avec succès!' as message;
SELECT COUNT(*) as nb_utilisateurs FROM utilisateurs;
SELECT COUNT(*) as nb_trajets FROM trajets;
