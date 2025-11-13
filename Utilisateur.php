<?php
/**
 * Classe Utilisateur
 * Gère toutes les opérations liées aux utilisateurs
 */
class Utilisateur {
    private $conn;
    private $table = "utilisateurs";

    // Propriétés
    public $id;
    public $nom;
    public $prenom;
    public $email;
    public $telephone;
    public $mot_de_passe;
    public $role;
    public $statut;
    public $date_inscription;

    /**
     * Constructeur
     * @param PDO $db Connexion à la base de données
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Inscription d'un nouvel utilisateur
     * @return bool
     */
    public function inscription() {
        $query = "INSERT INTO " . $this->table . " 
                  (nom, prenom, email, telephone, mot_de_passe, role, statut) 
                  VALUES (:nom, :prenom, :email, :telephone, :mot_de_passe, :role, :statut)";

        $stmt = $this->conn->prepare($query);

        // Nettoyage des données
        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->prenom = htmlspecialchars(strip_tags($this->prenom));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->telephone = htmlspecialchars(strip_tags($this->telephone));
        
        // Hachage du mot de passe
        $hashed_password = password_hash($this->mot_de_passe, PASSWORD_BCRYPT);

        // Liaison des paramètres
        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":prenom", $this->prenom);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":telephone", $this->telephone);
        $stmt->bindParam(":mot_de_passe", $hashed_password);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":statut", $this->statut);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Connexion d'un utilisateur
     * @return bool|array
     */
    public function connexion() {
        $query = "SELECT id, nom, prenom, email, telephone, mot_de_passe, role, statut 
                  FROM " . $this->table . " 
                  WHERE email = :email AND statut = 'actif' 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Vérifier le mot de passe
            if(password_verify($this->mot_de_passe, $row['mot_de_passe'])) {
                $this->id = $row['id'];
                $this->nom = $row['nom'];
                $this->prenom = $row['prenom'];
                $this->email = $row['email'];
                $this->telephone = $row['telephone'];
                $this->role = $row['role'];
                $this->statut = $row['statut'];
                
                return $row;
            }
        }

        return false;
    }

    /**
     * Vérifier si l'email existe déjà
     * @return bool
     */
    public function emailExiste() {
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Récupérer un utilisateur par ID
     * @return bool
     */
    public function lireUnUtilisateur() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->nom = $row['nom'];
            $this->prenom = $row['prenom'];
            $this->email = $row['email'];
            $this->telephone = $row['telephone'];
            $this->role = $row['role'];
            $this->statut = $row['statut'];
            $this->date_inscription = $row['date_inscription'];
            
            return true;
        }

        return false;
    }

    /**
     * Lire tous les utilisateurs
     * @return PDOStatement
     */
    public function lireTousLesUtilisateurs() {
        $query = "SELECT id, nom, prenom, email, telephone, role, statut, date_inscription 
                  FROM " . $this->table . " 
                  ORDER BY date_inscription DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Mettre à jour le profil utilisateur
     * @return bool
     */
    public function mettreAJour() {
        $query = "UPDATE " . $this->table . " 
                  SET nom = :nom, prenom = :prenom, telephone = :telephone 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->prenom = htmlspecialchars(strip_tags($this->prenom));
        $this->telephone = htmlspecialchars(strip_tags($this->telephone));

        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":prenom", $this->prenom);
        $stmt->bindParam(":telephone", $this->telephone);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Changer le mot de passe
     * @param string $nouveau_mdp
     * @return bool
     */
    public function changerMotDePasse($nouveau_mdp) {
        $query = "UPDATE " . $this->table . " SET mot_de_passe = :mot_de_passe WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $hashed_password = password_hash($nouveau_mdp, PASSWORD_BCRYPT);

        $stmt->bindParam(":mot_de_passe", $hashed_password);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Bloquer/Débloquer un utilisateur (Admin)
     * @param int $user_id
     * @param string $statut
     * @return bool
     */
    public function changerStatut($user_id, $statut) {
        $query = "UPDATE " . $this->table . " SET statut = :statut WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":statut", $statut);
        $stmt->bindParam(":id", $user_id);

        return $stmt->execute();
    }

    /**
     * Supprimer un utilisateur (Admin)
     * @return bool
     */
    public function supprimer() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Obtenir le nombre total d'utilisateurs
     * @return int
     */
    public function compterUtilisateurs() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'];
    }
}
?>