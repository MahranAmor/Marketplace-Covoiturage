<?php
// register.php : Inscription d'un nouvel utilisateur
session_start();
header('Content-Type: application/json');

require_once 'DB.php';

// Récupérer les données du formulaire
$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$email = trim($_POST['email'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

// Validation des champs
if (empty($nom) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Nom, email et mot de passe sont obligatoires']);
    exit;
}

// Validation format email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Format d\'email invalide']);
    exit;
}

// Validation mot de passe (minimum 6 caractères)
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caractères']);
    exit;
}

// Vérifier que les mots de passe correspondent
if ($password !== $password_confirm) {
    echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas']);
    exit;
}

try {
    $db = new DB();
    $conn = $db->getConnection();
    
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données']);
        exit;
    }
    
    // Vérifier si l'email existe déjà
    $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
        exit;
    }
    
    // Hasher le mot de passe
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insérer le nouvel utilisateur
    $query = "INSERT INTO utilisateurs (nom, prenom, email, telephone, mot_de_passe, role, statut) 
              VALUES (?, ?, ?, ?, ?, 'user', 'actif')";
    $stmt = $conn->prepare($query);
    $stmt->execute([$nom, $prenom, $email, $telephone, $password_hash]);
    
    // Récupérer l'ID du nouvel utilisateur
    $user_id = $conn->lastInsertId();
    
    // Connecter automatiquement l'utilisateur
    $_SESSION['user_id'] = $user_id;
    $_SESSION['is_admin'] = false;
    
    echo json_encode([
        'success' => true, 
        'message' => 'Inscription réussie ! Bienvenue ' . htmlspecialchars($prenom),
        'user_id' => $user_id
    ]);
    
} catch (PDOException $e) {
    error_log("Erreur inscription: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'inscription. Veuillez réessayer.']);
}
?>
