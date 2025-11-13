<?php
/**
 * Classe Reservation
 * Gère toutes les opérations liées aux réservations
 */
class Reservation {
    private $conn;
    private $table = "reservations";

    // Propriétés
    public $id;
    public $trajet_id;
    public $utilisateur_id;
    public $nombre_places;
    public $prix_total;
    public $statut;
    public $date_reservation;

    /**
     * Constructeur
     * @param PDO $db Connexion à la base de données
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Créer une nouvelle réservation
     * @return bool
     */
    public function creer() {
        // Commencer une transaction
        $this->conn->beginTransaction();

        try {
            // Vérifier la disponibilité des places
            $query_check = "SELECT places_disponibles FROM trajets WHERE id = :trajet_id FOR UPDATE";
            $stmt_check = $this->conn->prepare($query_check);
            $stmt_check->bindParam(":trajet_id", $this->trajet_id);
            $stmt_check->execute();
            
            $trajet = $stmt_check->fetch(PDO::FETCH_ASSOC);
            
            if (!$trajet || $trajet['places_disponibles'] < $this->nombre_places) {
                $this->conn->rollBack();
                return false;
            }

            // Créer la réservation
            $query = "INSERT INTO " . $this->table . " 
                      (trajet_id, utilisateur_id, nombre_places, prix_total, statut) 
                      VALUES (:trajet_id, :utilisateur_id, :nombre_places, :prix_total, :statut)";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":trajet_id", $this->trajet_id);
            $stmt->bindParam(":utilisateur_id", $this->utilisateur_id);
            $stmt->bindParam(":nombre_places", $this->nombre_places);
            $stmt->bindParam(":prix_total", $this->prix_total);
            $stmt->bindParam(":statut", $this->statut);

            $stmt->execute();
            $this->id = $this->conn->lastInsertId();

            // Décrémenter les places disponibles
            $query_update = "UPDATE trajets 
                            SET places_disponibles = places_disponibles - :nombre_places 
                            WHERE id = :trajet_id";
            
            $stmt_update = $this->conn->prepare($query_update);
            $stmt_update->bindParam(":nombre_places", $this->nombre_places);
            $stmt_update->bindParam(":trajet_id", $this->trajet_id);
            $stmt_update->execute();

            // Valider la transaction
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->conn->rollBack();
            error_log("Erreur lors de la réservation : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lire toutes les réservations d'un utilisateur
     * @return PDOStatement
     */
    public function lireParUtilisateur() {
        $query = "SELECT r.*, 
                         t.lieu_depart, t.lieu_arrivee, t.date_trajet, t.heure_trajet,
                         u.nom as conducteur_nom, u.prenom as conducteur_prenom, u.telephone as conducteur_tel
                  FROM " . $this->table . " r
                  INNER JOIN trajets t ON r.trajet_id = t.id
                  INNER JOIN utilisateurs u ON t.utilisateur_id = u.id
                  WHERE r.utilisateur_id = :utilisateur_id
                  ORDER BY r.date_reservation DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":utilisateur_id", $this->utilisateur_id);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Lire toutes les réservations d'un trajet
     * @return PDOStatement
     */
    public function lireParTrajet() {
        $query = "SELECT r.*, 
                         u.nom, u.prenom, u.email, u.telephone
                  FROM " . $this->table . " r
                  INNER JOIN utilisateurs u ON r.utilisateur_id = u.id
                  WHERE r.trajet_id = :trajet_id
                  ORDER BY r.date_reservation ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":trajet_id", $this->trajet_id);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Lire une réservation spécifique
     * @return bool
     */
    public function lireUne() {
        $query = "SELECT r.*, 
                         t.lieu_depart, t.lieu_arrivee, t.date_trajet, t.heure_trajet, t.prix,
                         u.nom as conducteur_nom, u.prenom as conducteur_prenom
                  FROM " . $this->table . " r
                  INNER JOIN trajets t ON r.trajet_id = t.id
                  INNER JOIN utilisateurs u ON t.utilisateur_id = u.id
                  WHERE r.id = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->trajet_id = $row['trajet_id'];
            $this->utilisateur_id = $row['utilisateur_id'];
            $this->nombre_places = $row['nombre_places'];
            $this->prix_total = $row['prix_total'];
            $this->statut = $row['statut'];
            $this->date_reservation = $row['date_reservation'];
            
            return true;
        }

        return false;
    }

    /**
     * Annuler une réservation
     * @return bool
     */
    public function annuler() {
        $this->conn->beginTransaction();

        try {
            // Changer le statut de la réservation
            $query = "UPDATE " . $this->table . " 
                      SET statut = 'annulee' 
                      WHERE id = :id AND utilisateur_id = :utilisateur_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $this->id);
            $stmt->bindParam(":utilisateur_id", $this->utilisateur_id);
            $stmt->execute();

            // Récupérer le nombre de places pour le restituer
            $query_places = "SELECT nombre_places, trajet_id FROM " . $this->table . " WHERE id = :id";
            $stmt_places = $this->conn->prepare($query_places);
            $stmt_places->bindParam(":id", $this->id);
            $stmt_places->execute();
            $reservation = $stmt_places->fetch(PDO::FETCH_ASSOC);

            // Restituer les places au trajet
            $query_update = "UPDATE trajets 
                            SET places_disponibles = places_disponibles + :nombre_places 
                            WHERE id = :trajet_id";
            
            $stmt_update = $this->conn->prepare($query_update);
            $stmt_update->bindParam(":nombre_places", $reservation['nombre_places']);
            $stmt_update->bindParam(":trajet_id", $reservation['trajet_id']);
            $stmt_update->execute();

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Erreur lors de l'annulation : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Confirmer une réservation
     * @return bool
     */
    public function confirmer() {
        $query = "UPDATE " . $this->table . " 
                  SET statut = 'confirmee' 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Supprimer une réservation
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
     * Obtenir le nombre total de réservations
     * @return int
     */
    public function compterReservations() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE statut != 'annulee'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'];
    }

    /**
     * Vérifier si un utilisateur a déjà réservé un trajet
     * @return bool
     */
    public function dejaReserve() {
        $query = "SELECT id FROM " . $this->table . " 
                  WHERE trajet_id = :trajet_id 
                  AND utilisateur_id = :utilisateur_id 
                  AND statut != 'annulee'
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":trajet_id", $this->trajet_id);
        $stmt->bindParam(":utilisateur_id", $this->utilisateur_id);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Obtenir les statistiques de réservations
     * @return array
     */
    public function statistiques() {
        $query = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN statut = 'confirmee' THEN 1 END) as confirmees,
                    COUNT(CASE WHEN statut = 'en_attente' THEN 1 END) as en_attente,
                    COUNT(CASE WHEN statut = 'annulee' THEN 1 END) as annulees,
                    SUM(prix_total) as revenu_total
                  FROM " . $this->table;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>