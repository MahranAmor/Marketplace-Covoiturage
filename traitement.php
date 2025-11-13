<?php
class OffreManager {
    private $db;
    
    public function __construct() {
        $this->connectDB();
    }
    
    private function connectDB() {
        $host = "localhost";
        $dbname = "covoiturage";
        $username = "root";
        $password = "";

        try {
            $this->db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        } catch (PDOException $e) {
            die("Erreur connexion: " . $e->getMessage());
        }
    }
    
    public function processForm() {
        include "offre.php";
        $offre = new Offre($this->db);

        if (!empty($_POST['titre']) && !empty($_POST['description'])) {
            $titre = $_POST['titre'];
            $description = $_POST['description'];

            if ($offre->ajouter($titre, $description)) {
                echo "✅ Offre ajoutée avec succès !";
            } else {
                echo "❌ Erreur lors de l’ajout.";
            }
        } else {
            echo "⚠ Veuillez remplir tous les champs.";
        }
        
        echo '<br><br><a href="formulaire.html">↩ Retour</a>';
    }
}

// Utilisation de la classe
$offreManager = new OffreManager();
$offreManager->processForm();

// Debug: log HTTP method and URI (processForm handles POST from formulaire.html)
file_put_contents(__DIR__ . '/debug_http_methods.log', date('c') . ' ' . ($_SERVER['REQUEST_METHOD'] ?? '-') . ' ' . ($_SERVER['REQUEST_URI'] ?? '-') . "\n", FILE_APPEND);
?>