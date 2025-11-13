<?php
class Offre {
    private $conn;
    private $table = "offres";

    public $id;
    public $titre;
    public $description;
    public $date_creation;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function ajouter($titre, $description) {
        $query = "INSERT INTO " . $this->table . " (titre, description, date_creation) VALUES (:titre, :description, NOW())";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':description', $description);

        return $stmt->execute();
    }

    public function lireToutes() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY date_creation DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
