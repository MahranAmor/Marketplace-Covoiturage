<?php
session_start();

include_once "DB.php";
include_once "Trajet.php";

// Debug logs détaillés
$logFile = __DIR__ . '/debug_detailed.log';
file_put_contents($logFile, "\n=== TRAITEMENT_TRAJET.PHP START ===\n", FILE_APPEND);
file_put_contents($logFile, date('c') . ' METHOD: ' . ($_SERVER['REQUEST_METHOD'] ?? '-') . "\n", FILE_APPEND);
file_put_contents($logFile, date('c') . ' URI: ' . ($_SERVER['REQUEST_URI'] ?? '-') . "\n", FILE_APPEND);
file_put_contents($logFile, date('c') . ' POST keys: ' . implode(', ', array_keys($_POST)) . "\n", FILE_APPEND);

// Connexion à la base de données
$database = new DB();
$db = $database->getConnection();
file_put_contents($logFile, date('c') . ' DB connected' . "\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents($logFile, date('c') . ' POST request validated' . "\n", FILE_APPEND);
    file_put_contents($logFile, date('c') . ' POST fields: ' . implode(', ', array_keys($_POST)) . "\n", FILE_APPEND);
    // Vérifier que les champs requis sont présents
    if (isset($_POST['lieu_depart']) && isset($_POST['lieu_arrivee']) && 
        isset($_POST['date_trajet']) && isset($_POST['heure_trajet']) &&
        isset($_POST['places_disponibles']) && isset($_POST['prix']) &&
        isset($_POST['marque']) && isset($_POST['modele']) && isset($_POST['description'])) {
        
        file_put_contents($logFile, date('c') . ' All required fields present' . "\n", FILE_APPEND);
        
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            file_put_contents($logFile, date('c') . ' ERROR: User not logged in' . "\n", FILE_APPEND);
            http_response_code(403);
            echo "<!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Erreur</title>
            </head>
            <body>
                <div class='error'>❌ Vous devez être connecté pour publier un trajet.</div>
                <a href='index_one.html'>Se connecter</a>
            </body>
            </html>";
            exit();
        }
        
        file_put_contents($logFile, date('c') . ' User ID from session: ' . $_SESSION['user_id'] . "\n", FILE_APPEND);
        
        // Créer une instance de Trajet
        $trajet = new Trajet($db);
        
        // Assigner les valeurs depuis le formulaire
        $trajet->utilisateur_id = $_SESSION['user_id']; // Depuis la session
        $trajet->lieu_depart = $_POST['lieu_depart'];
        $trajet->lieu_arrivee = $_POST['lieu_arrivee'];
        $trajet->date_trajet = $_POST['date_trajet'];
        $trajet->heure_trajet = $_POST['heure_trajet'];
        $trajet->places_disponibles = $_POST['places_disponibles'];
        $trajet->places_initiales = $_POST['places_disponibles']; // Même valeur au début
        $trajet->prix = $_POST['prix'];
        
        // Combiner marque et modèle pour le champ vehicule
        $marque = $_POST['marque'] ?? '';
        $modele = $_POST['modele'] ?? '';
        $trajet->vehicule = trim($marque . ' ' . $modele);
        
        $trajet->description = $_POST['description'];
        
        // Gérer les préférences (tableau de checkboxes)
        $preferences_array = $_POST['preferences'] ?? [];
        $trajet->preferences = is_array($preferences_array) ? implode(', ', $preferences_array) : '';
        
        $trajet->statut = 'en_attente'; // Statut par défaut
        
        // Tenter de créer le trajet
        try {
            file_put_contents($logFile, date('c') . ' Calling trajet->creer()...' . "\n", FILE_APPEND);
            if ($trajet->creer()) {
                file_put_contents($logFile, date('c') . ' SUCCESS: Trajet created' . "\n=== TRAITEMENT_TRAJET.PHP END ===\n", FILE_APPEND);
                echo "<!DOCTYPE html>
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <title>Succès</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            height: 100vh;
                            margin: 0;
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        }
                        .message {
                            background: white;
                            padding: 30px;
                            border-radius: 10px;
                            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                            text-align: center;
                        }
                        .success {
                            color: #27ae60;
                            font-size: 24px;
                            margin-bottom: 20px;
                        }
                        .btn {
                            display: inline-block;
                            padding: 10px 20px;
                            background: #667eea;
                            color: white;
                            text-decoration: none;
                            border-radius: 5px;
                            margin-top: 20px;
                        }
                        .btn:hover {
                            background: #764ba2;
                        }
                    </style>
                </head>
                <body>
                    <div class='message'>
                        <div class='success'>✅ Trajet publié avec succès !</div>
                        <p>Votre trajet a été ajouté à la base de données.</p>
                        <a href='publier.html' class='btn'>Publier un autre trajet</a>
                        <a href='index_one.html' class='btn'>Retour à l'accueil</a>
                    </div>
                </body>
                </html>";
            } else {
                file_put_contents($logFile, date('c') . ' ERROR: trajet->creer() returned false' . "\n", FILE_APPEND);
                throw new Exception("Erreur lors de l'insertion dans la base de données.");
            }
        } catch (Exception $e) {
            file_put_contents($logFile, date('c') . ' EXCEPTION: ' . $e->getMessage() . "\n", FILE_APPEND);
            echo "<!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Erreur</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 100vh;
                        margin: 0;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    }
                    .message {
                        background: white;
                        padding: 30px;
                        border-radius: 10px;
                        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                        text-align: center;
                    }
                    .error {
                        color: #e74c3c;
                        font-size: 24px;
                        margin-bottom: 20px;
                    }
                    .btn {
                        display: inline-block;
                        padding: 10px 20px;
                        background: #667eea;
                        color: white;
                        text-decoration: none;
                        border-radius: 5px;
                        margin-top: 20px;
                    }
                    .btn:hover {
                        background: #764ba2;
                    }
                </style>
            </head>
            <body>
                <div class='message'>
                    <div class='error'>❌ Erreur lors de la publication</div>
                    <p>" . htmlspecialchars($e->getMessage()) . "</p>
                    <a href='publier.html' class='btn'>Retour au formulaire</a>
                </div>
            </body>
            </html>";
        }
    } else {
        file_put_contents($logFile, date('c') . ' ERROR: Missing required fields' . "\n=== TRAITEMENT_TRAJET.PHP END ===\n", FILE_APPEND);
        echo "<!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Erreur</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                }
                .message {
                    background: white;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                    text-align: center;
                }
                .error {
                    color: #e74c3c;
                    font-size: 24px;
                    margin-bottom: 20px;
                }
                .btn {
                    display: inline-block;
                    padding: 10px 20px;
                    background: #667eea;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    margin-top: 20px;
                }
                .btn:hover {
                    background: #764ba2;
                }
            </style>
        </head>
        <body>
            <div class='message'>
                <div class='error'>⚠ Veuillez remplir tous les champs requis.</div>
                <a href='publier.html' class='btn'>Retour au formulaire</a>
            </div>
        </body>
        </html>";
    }
} else {
    file_put_contents($logFile, date('c') . ' ERROR: Not POST - Redirecting to publier.html' . "\n=== TRAITEMENT_TRAJET.PHP END ===\n", FILE_APPEND);
    header('Location: publier.html');
    exit();
}
?>
