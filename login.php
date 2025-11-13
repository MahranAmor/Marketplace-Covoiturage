<?php
// login.php : Authentification utilisateur/admin
// Démarrer la session AVANT tout output
session_start();

// Capturer les erreurs PHP et les logger au lieu de les afficher
error_reporting(0);
ini_set('display_errors', 0);

// Header JSON AVANT tout include
header('Content-Type: application/json');

// Debug: log HTTP method and URI
$logFile = __DIR__ . '/debug_detailed.log';
file_put_contents($logFile, "\n=== LOGIN.PHP START ===\n", FILE_APPEND);
file_put_contents($logFile, date('c') . ' METHOD: ' . ($_SERVER['REQUEST_METHOD'] ?? '-') . "\n", FILE_APPEND);
file_put_contents($logFile, date('c') . ' URI: ' . ($_SERVER['REQUEST_URI'] ?? '-') . "\n", FILE_APPEND);
file_put_contents($logFile, date('c') . ' POST data: ' . json_encode($_POST) . "\n", FILE_APPEND);

// Inclure DB.php avec gestion d'erreur
try {
    require_once 'DB.php';
    file_put_contents($logFile, date('c') . ' DB.php loaded' . "\n", FILE_APPEND);
} catch (Exception $e) {
    file_put_contents($logFile, date('c') . ' ERROR loading DB.php: ' . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['success'=>false, 'message'=>'Erreur serveur (DB)']);
    exit;
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'user';

file_put_contents($logFile, date('c') . ' Email: ' . $email . ' | Role: ' . $role . "\n", FILE_APPEND);

if (!$email || !$password || !$role) {
    file_put_contents($logFile, date('c') . ' ERROR: Champs manquants' . "\n", FILE_APPEND);
    echo json_encode(['success'=>false, 'message'=>'Champs manquants']);
    exit;
}
file_put_contents($logFile, date('c') . ' Validation OK - Connexion DB...' . "\n", FILE_APPEND);

try {
    $db = new DB();
    $conn = $db->getConnection();
    if (!$conn) {
        file_put_contents($logFile, date('c') . ' ERROR: Connexion BDD échouée' . "\n", FILE_APPEND);
        echo json_encode(['success'=>false, 'message'=>'Erreur de connexion BDD']);
        exit;
    }
    file_put_contents($logFile, date('c') . ' DB connected - Query user...' . "\n", FILE_APPEND);
} catch (Exception $e) {
    file_put_contents($logFile, date('c') . ' EXCEPTION DB: ' . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['success'=>false, 'message'=>'Erreur serveur']);
    exit;
}

try {
    $query = "SELECT id, nom, email, mot_de_passe, role, statut FROM utilisateurs WHERE email = :email LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    file_put_contents($logFile, date('c') . ' Query executed - Rows: ' . $stmt->rowCount() . "\n", FILE_APPEND);
} catch (Exception $e) {
    file_put_contents($logFile, date('c') . ' EXCEPTION Query: ' . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['success'=>false, 'message'=>'Erreur base de données (table utilisateurs)']);
    exit;
}
if ($stmt->rowCount() > 0) {
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    file_put_contents($logFile, date('c') . ' User found: ' . $user['email'] . ' | Status: ' . $user['statut'] . "\n", FILE_APPEND);
    if ($user['statut'] !== 'actif') {
        file_put_contents($logFile, date('c') . ' ERROR: Compte non actif' . "\n", FILE_APPEND);
        echo json_encode(['success'=>false, 'message'=>'Compte bloqué ou inactif']);
        exit;
    }
    if (password_verify($password, $user['mot_de_passe'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['is_admin'] = ($user['role'] === 'admin');
        file_put_contents($logFile, date('c') . ' Password OK - Session set' . "\n", FILE_APPEND);
        if ($role === 'admin' && $user['role'] !== 'admin') {
            file_put_contents($logFile, date('c') . ' ERROR: User not admin' . "\n", FILE_APPEND);
            echo json_encode(['success'=>false, 'message'=>'Accès administrateur refusé']);
            exit;
        }
        file_put_contents($logFile, date('c') . ' SUCCESS - Login OK' . "\n=== LOGIN.PHP END ===\n", FILE_APPEND);
        echo json_encode(['success'=>true]);
        exit;
    } else {
        file_put_contents($logFile, date('c') . ' ERROR: Wrong password' . "\n", FILE_APPEND);
        echo json_encode(['success'=>false, 'message'=>'Mot de passe incorrect']);
        exit;
    }
} else {
    file_put_contents($logFile, date('c') . ' ERROR: Email not found' . "\n", FILE_APPEND);
    echo json_encode(['success'=>false, 'message'=>'Email inconnu']);
    exit;
}
