<?php
/**
 * Classe Trajet
 * Gère toutes les opérations liées aux trajets
 */
class Trajet {
    private $conn;
    private $table = "trajets";

    // Propriétés
    public $id;
    public $utilisateur_id;
    public $lieu_depart;
    public $lieu_arrivee;
    public $date_trajet;
    public $heure_trajet;
    public $places_disponibles;
    public $places_initiales;
    public $prix;
    public $vehicule;
    public $description;
    public $preferences;
    public $statut;
    public $date_creation;

    /**
     * Constructeur
     * @param PDO $db Connexion à la base de données
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Créer un nouveau trajet
     * @return bool
     */
    public function creer() {
        try {
            $query = "INSERT INTO " . $this->table . " 
                      (utilisateur_id, lieu_depart, lieu_arrivee, date_trajet, heure_trajet, 
                       places_disponibles, places_initiales, prix, vehicule, description, 
                       preferences, statut)
                      VALUES (:utilisateur_id, :lieu_depart, :lieu_arrivee, :date_trajet, 
                              :heure_trajet, :places_disponibles, :places_initiales, :prix, 
                              :vehicule, :description, :preferences, :statut)";

            $stmt = $this->conn->prepare($query);

            // Nettoyage des données
            $this->lieu_depart = htmlspecialchars(strip_tags($this->lieu_depart));
            $this->lieu_arrivee = htmlspecialchars(strip_tags($this->lieu_arrivee));
            $this->description = htmlspecialchars(strip_tags($this->description));
            $this->vehicule = htmlspecialchars(strip_tags($this->vehicule));
            $this->preferences = htmlspecialchars(strip_tags($this->preferences));

            // Liaison des paramètres
            $stmt->bindParam(":utilisateur_id", $this->utilisateur_id, PDO::PARAM_INT);
            $stmt->bindParam(":lieu_depart", $this->lieu_depart);
            $stmt->bindParam(":lieu_arrivee", $this->lieu_arrivee);
            $stmt->bindParam(":date_trajet", $this->date_trajet);
            $stmt->bindParam(":heure_trajet", $this->heure_trajet);
            $stmt->bindParam(":places_disponibles", $this->places_disponibles, PDO::PARAM_INT);
            $stmt->bindParam(":places_initiales", $this->places_initiales, PDO::PARAM_INT);
            $stmt->bindParam(":prix", $this->prix);
            $stmt->bindParam(":vehicule", $this->vehicule);
            $stmt->bindParam(":description", $this->description);
            $stmt->bindParam(":preferences", $this->preferences);
            $stmt->bindParam(":statut", $this->statut);

            if($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }

            // Log l'erreur si l'exécution échoue
            error_log("Erreur lors de l'exécution de la requête: " . print_r($stmt->errorInfo(), true));
            return false;
        } catch(PDOException $e) {
            error_log("Erreur PDO lors de la création du trajet: " . $e->getMessage());
            throw new Exception("Erreur lors de la création du trajet: " . $e->getMessage());
        }
    }

    /**
     * Lire tous les trajets disponibles
     * @return PDOStatement
     */
    public function lireTous() {
        $query = "SELECT t.*, u.nom, u.prenom, u.email, u.telephone 
                  FROM " . $this->table . " t
                  LEFT JOIN utilisateurs u ON t.utilisateur_id = u.id
                  WHERE t.statut = 'actif' 
                  AND t.date_trajet >= CURDATE()
                  AND t.places_disponibles > 0
                  ORDER BY t.date_trajet ASC, t.heure_trajet ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Lire un trajet spécifique
     * @return bool
     */
    public function lireUn() {
        $query = "SELECT t.*, u.nom, u.prenom, u.email, u.telephone 
                  FROM " . $this->table . " t
                  LEFT JOIN utilisateurs u ON t.utilisateur_id = u.id
                  WHERE t.id = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->utilisateur_id = $row['utilisateur_id'];
            $this->lieu_depart = $row['lieu_depart'];
            $this->lieu_arrivee = $row['lieu_arrivee'];
            $this->date_trajet = $row['date_trajet'];
            $this->heure_trajet = $row['heure_trajet'];
            $this->places_disponibles = $row['places_disponibles'];
            $this->places_initiales = $row['places_initiales'];
            $this->prix = $row['prix'];
            $this->vehicule = $row['vehicule'];
            $this->description = $row['description'];
            $this->preferences = $row['preferences'];
            $this->statut = $row['statut'];
            $this->date_creation = $row['date_creation'];
            
            return true;
        }

        return false;
    }

    /**
     * Lire les trajets d'un utilisateur spécifique
     * @return PDOStatement
     */
    public function lireParUtilisateur() {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE utilisateur_id = :utilisateur_id 
                  ORDER BY date_trajet DESC, heure_trajet DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":utilisateur_id", $this->utilisateur_id);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Mettre à jour un trajet
     * @return bool
     */
    public function mettreAJour() {
        $query = "UPDATE " . $this->table . " 
                  SET lieu_depart = :lieu_depart, 
                      lieu_arrivee = :lieu_arrivee, 
                      date_trajet = :date_trajet, 
                      heure_trajet = :heure_trajet, 
                      places_disponibles = :places_disponibles,
                      prix = :prix, 
                      vehicule = :vehicule, 
                      description = :description,
                      preferences = :preferences
                  WHERE id = :id AND utilisateur_id = :utilisateur_id";

        $stmt = $this->conn->prepare($query);

        // Nettoyage des données
        $this->lieu_depart = htmlspecialchars(strip_tags($this->lieu_depart));
        $this->lieu_arrivee = htmlspecialchars(strip_tags($this->lieu_arrivee));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->vehicule = htmlspecialchars(strip_tags($this->vehicule));

        $stmt->bindParam(":lieu_depart", $this->lieu_depart);
        $stmt->bindParam(":lieu_arrivee", $this->lieu_arrivee);
        $stmt->bindParam(":date_trajet", $this->date_trajet);
        $stmt->bindParam(":heure_trajet", $this->heure_trajet);
        $stmt->bindParam(":places_disponibles", $this->places_disponibles);
        $stmt->bindParam(":prix", $this->prix);
        $stmt->bindParam(":vehicule", $this->vehicule);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":preferences", $this->preferences);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":utilisateur_id", $this->utilisateur_id);

        return $stmt->execute();
    }

    /**
     * Décrémenter le nombre de places disponibles
     * @param int $nombre_places
     * @return bool
     */
    public function decrementerPlaces($nombre_places = 1) {
        $query = "UPDATE " . $this->table . " 
                  SET places_disponibles = places_disponibles - :nombre_places 
                  WHERE id = :id AND places_disponibles >= :nombre_places";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nombre_places", $nombre_places);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Changer le statut d'un trajet
     * @param string $nouveau_statut
     * @return bool
     */
    public function changerStatut($nouveau_statut) {
        $query = "UPDATE " . $this->table . " SET statut = :statut WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":statut", $nouveau_statut);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Supprimer un trajet
     * @return bool
     */
    public function supprimer() {
        $query = "DELETE FROM " . $this->table . " 
                  WHERE id = :id AND utilisateur_id = :utilisateur_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":utilisateur_id", $this->utilisateur_id);

        return $stmt->execute();
    }

    /**
     * Supprimer un trajet (Admin)
     * @return bool
     */
    public function supprimerAdmin() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Obtenir le nombre total de trajets
     * @return int
     */
    public function compterTrajets() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE statut = 'actif'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'];
    }

    /**
     * Obtenir les trajets en attente de validation (Admin)
     * @return PDOStatement
     */
    public function lireTrajetsEnAttente() {
        $query = "SELECT t.*, u.nom, u.prenom, u.email 
                  FROM " . $this->table . " t
                  LEFT JOIN utilisateurs u ON t.utilisateur_id = u.id
                  WHERE t.statut = 'en_attente'
                  ORDER BY t.date_creation DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Rechercher des trajets selon des critères
     * @param array $criteres Critères de recherche (lieu_depart, lieu_arrivee, date_trajet)
     * @return PDOStatement
     */
    public function rechercher($criteres = []) {
        $query = "SELECT t.*, u.nom, u.prenom, u.telephone 
                  FROM " . $this->table . " t
                  LEFT JOIN utilisateurs u ON t.utilisateur_id = u.id
                  WHERE t.statut IN ('actif', 'valide') AND t.places_disponibles > 0";
        
        $params = [];
        
        if (!empty($criteres['lieu_depart'])) {
            $query .= " AND t.lieu_depart LIKE ?";
            $params[] = '%' . $criteres['lieu_depart'] . '%';
        }
        
        if (!empty($criteres['lieu_arrivee'])) {
            $query .= " AND t.lieu_arrivee LIKE ?";
            $params[] = '%' . $criteres['lieu_arrivee'] . '%';
        }
        
        if (!empty($criteres['date_trajet'])) {
            $query .= " AND t.date_trajet = ?";
            $params[] = $criteres['date_trajet'];
        }
        
        $query .= " ORDER BY t.date_trajet ASC, t.heure_trajet ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt;
    }
}
?>