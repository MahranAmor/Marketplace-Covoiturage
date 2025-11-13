<?php
// admin.php : Backend pour gestion admin (utilisateurs, offres, notifications)
// Connexion à la base de données (à adapter selon votre config)

session_start();
require_once 'DB.php';
header('Content-Type: application/json');

// Debug logs détaillés
$logFile = __DIR__ . '/debug_detailed.log';
file_put_contents($logFile, "\n=== ADMIN.PHP START ===\n", FILE_APPEND);
file_put_contents($logFile, date('c') . ' METHOD: ' . ($_SERVER['REQUEST_METHOD'] ?? '-') . "\n", FILE_APPEND);
file_put_contents($logFile, date('c') . ' URI: ' . ($_SERVER['REQUEST_URI'] ?? '-') . "\n", FILE_APPEND);
file_put_contents($logFile, date('c') . ' GET: ' . json_encode($_GET) . "\n", FILE_APPEND);
file_put_contents($logFile, date('c') . ' POST: ' . json_encode($_POST) . "\n", FILE_APPEND);

// Protection : accès réservé aux admins
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    file_put_contents($logFile, date('c') . ' ERROR: Access denied - not admin' . "\n=== ADMIN.PHP END ===\n", FILE_APPEND);
    echo json_encode(['error'=>'Accès refusé']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
file_put_contents($logFile, date('c') . ' Action requested: ' . $action . "\n", FILE_APPEND);

function sendMail($to, $subject, $message) {
    // Utilise mail() ou une lib plus avancée selon config serveur
    $headers = "From: noreply@covoitplus.com\r\nContent-type: text/html; charset=UTF-8";
    return mail($to, $subject, $message, $headers);
}

switch ($action) {
    case 'list_users':
        file_put_contents($logFile, date('c') . ' Processing: list_users' . "\n", FILE_APPEND);
        // Retourne la liste des utilisateurs
        $db = new DB();
        $users = $db->query("SELECT id, nom, email, statut FROM utilisateurs");
        file_put_contents($logFile, date('c') . ' Users count: ' . count($users) . "\n", FILE_APPEND);
        echo json_encode($users);
        break;
    case 'block_user':
        $id = intval($_POST['id']);
        file_put_contents($logFile, date('c') . ' Processing: block_user ID=' . $id . "\n", FILE_APPEND);
        $db = new DB();
        $db->query("UPDATE utilisateurs SET statut='bloque' WHERE id=?", [$id]);
        echo json_encode(['success'=>true]);
        break;
    case 'unblock_user':
        $id = intval($_POST['id']);
        $db = new DB();
        $db->query("UPDATE utilisateurs SET statut='actif' WHERE id=?", [$id]);
        echo json_encode(['success'=>true]);
        break;
    case 'delete_user':
        $id = intval($_POST['id']);
        $db = new DB();
        $db->query("DELETE FROM utilisateurs WHERE id=?", [$id]);
        echo json_encode(['success'=>true]);
        break;
    case 'list_offers':
        file_put_contents($logFile, date('c') . ' Processing: list_offers' . "\n", FILE_APPEND);
        $db = new DB();
        $conn = $db->getConnection();
        $query = "SELECT t.id, t.lieu_depart as depart, t.lieu_arrivee as arrivee, 
                  t.date_trajet as date, u.nom as conducteur, u.email as email_utilisateur, t.statut 
                  FROM trajets t 
                  LEFT JOIN utilisateurs u ON t.utilisateur_id = u.id
                  ORDER BY t.date_creation DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        file_put_contents($logFile, date('c') . ' Offers count: ' . count($offers) . "\n", FILE_APPEND);
        echo json_encode($offers);
        break;
    case 'validate_offer':
        $id = intval($_POST['id']);
        file_put_contents($logFile, date('c') . ' Processing: validate_offer ID=' . $id . "\n", FILE_APPEND);
        $db = new DB();
        $conn = $db->getConnection();
        $conn->prepare("UPDATE trajets SET statut='valide' WHERE id=?")->execute([$id]);
        // Notifier l'utilisateur
        $stmt = $conn->prepare("SELECT u.email FROM trajets t JOIN utilisateurs u ON t.utilisateur_id = u.id WHERE t.id=?");
        $stmt->execute([$id]);
        $offer = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($offer && isset($offer['email'])) {
            sendMail($offer['email'], 'Offre validée', 'Votre offre de covoiturage a été validée par un administrateur.');
        }
        echo json_encode(['success'=>true]);
        break;
    case 'refuse_offer':
        $id = intval($_POST['id']);
        file_put_contents($logFile, date('c') . ' Processing: refuse_offer ID=' . $id . "\n", FILE_APPEND);
        $db = new DB();
        $conn = $db->getConnection();
        $conn->prepare("UPDATE trajets SET statut='refuse' WHERE id=?")->execute([$id]);
        // Notifier l'utilisateur
        $stmt = $conn->prepare("SELECT u.email FROM trajets t JOIN utilisateurs u ON t.utilisateur_id = u.id WHERE t.id=?");
        $stmt->execute([$id]);
        $offer = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($offer && isset($offer['email'])) {
            sendMail($offer['email'], 'Offre refusée', 'Votre offre de covoiturage a été refusée par un administrateur.');
        }
        echo json_encode(['success'=>true]);
        break;
    case 'notify_offer':
        // Appelé lors de la publication d'une offre
        $email = $_POST['email'] ?? '';
        if ($email) {
            sendMail($email, 'Offre publiée', 'Votre offre a bien été publiée.');
        }
        echo json_encode(['success'=>true]);
        break;
    default:
        file_put_contents($logFile, date('c') . ' ERROR: Unknown action' . "\n", FILE_APPEND);
        echo json_encode(['error'=>'Action inconnue']);
}
file_put_contents($logFile, date('c') . ' Response sent' . "\n=== ADMIN.PHP END ===\n", FILE_APPEND);
