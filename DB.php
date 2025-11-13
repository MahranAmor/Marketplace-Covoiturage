<?php
/**
 * Classe DB
 * Gère la connexion à la base de données avec PDO
 */
class DB {
    private $host = "localhost";
    private $db_name = "covoiturage";
    private $username = "root";
    private $password = "";
    private $conn;

    /**
     * Obtenir la connexion à la base de données
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Erreur de connexion : " . $e->getMessage());
            return null;
        }

        return $this->conn;
    }

    /**
     * Exécuter une requête SQL
     * @param string $sql La requête SQL
     * @param array $params Les paramètres à binder
     * @return array|bool Les résultats ou true/false
     */
    public function query($sql, $params = []) {
        if ($this->conn === null) {
            $this->getConnection();
        }
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            
            // Si c'est un SELECT, retourner les résultats
            if (stripos(trim($sql), 'SELECT') === 0) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            // Sinon retourner true pour les UPDATE, INSERT, DELETE
            return true;
        } catch(PDOException $e) {
            error_log("Erreur de requête : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fermer la connexion
     */
    public function closeConnection() {
        $this->conn = null;
    }
}
?>