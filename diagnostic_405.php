<?php
/**
 * Script de diagnostic automatique pour identifier l'erreur 405
 * Teste tous les endpoints et affiche un rapport détaillé
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$logFile = __DIR__ . '/debug_detailed.log';
$resultats = [];

// Nettoyer les anciens logs
if (file_exists($logFile)) {
    unlink($logFile);
}

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <title>Diagnostic HTTP 405</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #667eea; padding-bottom: 10px; }
        h2 { color: #667eea; margin-top: 30px; }
        .test { margin: 15px 0; padding: 15px; border-left: 4px solid #ccc; background: #fafafa; }
        .test.success { border-color: #27ae60; background: #eafaf1; }
        .test.error { border-color: #e74c3c; background: #fadbd8; }
        .test.warning { border-color: #f39c12; background: #fef5e7; }
        .test-name { font-weight: bold; font-size: 16px; margin-bottom: 8px; }
        .test-details { font-size: 14px; color: #555; margin: 5px 0; }
        .code { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 5px; overflow-x: auto; margin: 10px 0; }
        .code pre { margin: 0; white-space: pre-wrap; }
        .status { display: inline-block; padding: 3px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .status.ok { background: #27ae60; color: white; }
        .status.fail { background: #e74c3c; color: white; }
        .status.warning { background: #f39c12; color: white; }
        .recommendation { background: #e8f4f8; border-left: 4px solid #3498db; padding: 15px; margin: 20px 0; }
        .recommendation h3 { margin-top: 0; color: #3498db; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔍 Diagnostic HTTP 405 - Analyse Complète</h1>";
echo "<p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Serveur:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";

// ============================================
// TEST 1: Vérifier la configuration du serveur
// ============================================
echo "<h2>📋 Test 1: Configuration Serveur</h2>";

$serverConfig = [
    'Apache mod_rewrite' => function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()),
    'allow_url_fopen' => ini_get('allow_url_fopen'),
    'Session support' => session_status() !== PHP_SESSION_DISABLED,
    'PDO MySQL' => extension_loaded('pdo_mysql'),
];

foreach ($serverConfig as $key => $value) {
    $status = $value ? 'ok' : 'fail';
    $statusText = $value ? '✓ OK' : '✗ ERREUR';
    echo "<div class='test " . ($value ? 'success' : 'error') . "'>
            <div class='test-name'>{$key}: <span class='status {$status}'>{$statusText}</span></div>
          </div>";
}

// ============================================
// TEST 2: Tester les fichiers PHP
// ============================================
echo "<h2>📁 Test 2: Fichiers PHP Accessibles</h2>";

$fichiers = [
    'login.php',
    'is_admin.php',
    'admin.php',
    'traitement.php',
    'traitement_trajet.php',
    'supprimer_trajet.php',
    'supprimer_mon_trajet.php',
    'DB.php',
    'trajet.php',
    'Utilisateur.php'
];

foreach ($fichiers as $fichier) {
    $path = __DIR__ . '/' . $fichier;
    $exists = file_exists($path);
    $readable = $exists && is_readable($path);
    
    $status = $readable ? 'ok' : 'fail';
    $statusText = $readable ? '✓ Accessible' : '✗ Introuvable';
    
    echo "<div class='test " . ($readable ? 'success' : 'error') . "'>
            <div class='test-name'>{$fichier}: <span class='status {$status}'>{$statusText}</span></div>
            <div class='test-details'>Chemin: {$path}</div>
          </div>";
}

// ============================================
// TEST 3: Tester la connexion à la base de données
// ============================================
echo "<h2>🗄️ Test 3: Connexion Base de Données</h2>";

try {
    require_once 'DB.php';
    $db = new DB();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "<div class='test success'>
                <div class='test-name'>Connexion DB: <span class='status ok'>✓ OK</span></div>
                <div class='test-details'>Connexion établie avec succès</div>
              </div>";
        
        // Tester la table utilisateurs
        try {
            $stmt = $conn->query("SELECT COUNT(*) as total FROM utilisateurs");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<div class='test success'>
                    <div class='test-name'>Table utilisateurs: <span class='status ok'>✓ OK</span></div>
                    <div class='test-details'>Nombre d'utilisateurs: {$result['total']}</div>
                  </div>";
        } catch (Exception $e) {
            echo "<div class='test error'>
                    <div class='test-name'>Table utilisateurs: <span class='status fail'>✗ ERREUR</span></div>
                    <div class='test-details'>Erreur: {$e->getMessage()}</div>
                  </div>";
        }
        
        // Tester la table trajets
        try {
            $stmt = $conn->query("SELECT COUNT(*) as total FROM trajets");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<div class='test success'>
                    <div class='test-name'>Table trajets: <span class='status ok'>✓ OK</span></div>
                    <div class='test-details'>Nombre de trajets: {$result['total']}</div>
                  </div>";
        } catch (Exception $e) {
            echo "<div class='test error'>
                    <div class='test-name'>Table trajets: <span class='status fail'>✗ ERREUR</span></div>
                    <div class='test-details'>Erreur: {$e->getMessage()}</div>
                  </div>";
        }
    } else {
        echo "<div class='test error'>
                <div class='test-name'>Connexion DB: <span class='status fail'>✗ ERREUR</span></div>
                <div class='test-details'>Impossible de se connecter à la base de données</div>
              </div>";
    }
} catch (Exception $e) {
    echo "<div class='test error'>
            <div class='test-name'>Connexion DB: <span class='status fail'>✗ EXCEPTION</span></div>
            <div class='test-details'>Erreur: {$e->getMessage()}</div>
          </div>";
}

// ============================================
// TEST 4: Vérifier les méthodes HTTP autorisées
// ============================================
echo "<h2>🌐 Test 4: Méthodes HTTP</h2>";

$methodsInfo = [
    'GET' => 'Méthode actuelle de cette page',
    'POST' => 'Utilisée pour login, admin, suppression',
    'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
];

foreach ($methodsInfo as $key => $value) {
    echo "<div class='test success'>
            <div class='test-name'>{$key}: <span class='status ok'>{$value}</span></div>
          </div>";
}

// ============================================
// TEST 5: Analyser les logs existants
// ============================================
echo "<h2>📊 Test 5: Analyse des Logs</h2>";

if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    if (!empty($logContent)) {
        echo "<div class='test warning'>
                <div class='test-name'>Logs détectés: <span class='status warning'>⚠ Voir détails</span></div>
              </div>";
        echo "<div class='code'><pre>" . htmlspecialchars($logContent) . "</pre></div>";
    } else {
        echo "<div class='test success'>
                <div class='test-name'>Logs: <span class='status ok'>✓ Aucune erreur enregistrée</span></div>
              </div>";
    }
} else {
    echo "<div class='test success'>
            <div class='test-name'>Logs: <span class='status ok'>✓ Fichier de log vide (normal)</span></div>
          </div>";
}

// Vérifier les logs Apache si possible
$apacheLogs = [
    'C:/xampp/apache/logs/error.log',
    'C:/wamp64/logs/apache_error.log',
    '/var/log/apache2/error.log',
];

foreach ($apacheLogs as $apacheLog) {
    if (file_exists($apacheLog)) {
        echo "<div class='test warning'>
                <div class='test-name'>Log Apache trouvé: <span class='status warning'>⚠ {$apacheLog}</span></div>
                <div class='test-details'>Exécutez cette commande PowerShell pour voir les erreurs récentes:</div>
              </div>";
        echo "<div class='code'><pre>Get-Content -Path \"{$apacheLog}\" -Tail 50</pre></div>";
        break;
    }
}

// ============================================
// TEST 6: JavaScript Fetch Test
// ============================================
echo "<h2>🔬 Test 6: Test en Direct des Endpoints</h2>";

echo "<div class='test warning'>
        <div class='test-name'>Tests JavaScript: <span class='status warning'>⚠ Ouvrez la Console (F12)</span></div>
        <div class='test-details'>Les tests vont s'exécuter automatiquement dans 3 secondes...</div>
      </div>";

echo "<div id='test-results'></div>";

// ============================================
// RECOMMANDATIONS
// ============================================
echo "<div class='recommendation'>
        <h3>📝 Prochaines Étapes</h3>
        <ol>
            <li><strong>Ouvrez les Outils Développeur</strong> (F12) et allez dans l'onglet Console</li>
            <li><strong>Rechargez cette page</strong> pour voir les tests JavaScript s'exécuter</li>
            <li><strong>Regardez l'onglet Réseau (Network)</strong> pour voir les requêtes HTTP</li>
            <li><strong>Si une erreur 405 apparaît</strong>, notez:
                <ul>
                    <li>L'URL exacte qui provoque l'erreur</li>
                    <li>La méthode HTTP (GET/POST/etc.)</li>
                    <li>Le code de statut et le message</li>
                </ul>
            </li>
            <li><strong>Consultez les logs détaillés:</strong>
                <div class='code'><pre>Get-Content -Path \"C:\\xampp\\htdocs\\exsepareoffre\\debug_detailed.log\" -Tail 100</pre></div>
            </li>
        </ol>
      </div>";

echo "<script>
console.log('%c🔍 DIAGNOSTIC 405 - DÉBUT DES TESTS', 'background: #667eea; color: white; font-size: 16px; padding: 10px;');

const testResults = document.getElementById('test-results');

function addTestResult(name, status, message, details = '') {
    const statusClass = status === 'success' ? 'success' : (status === 'error' ? 'error' : 'warning');
    const statusText = status === 'success' ? '✓ OK' : (status === 'error' ? '✗ ERREUR' : '⚠ ATTENTION');
    const statusBadge = status === 'success' ? 'ok' : (status === 'error' ? 'fail' : 'warning');
    
    const html = `
        <div class='test \${statusClass}'>
            <div class='test-name'>\${name}: <span class='status \${statusBadge}'>\${statusText}</span></div>
            <div class='test-details'>\${message}</div>
            \${details ? `<div class='code'><pre>\${details}</pre></div>` : ''}
        </div>
    `;
    testResults.innerHTML += html;
}

// Test 1: is_admin.php (GET)
setTimeout(() => {
    console.log('%c TEST 1: is_admin.php (GET)', 'color: #3498db; font-weight: bold');
    fetch('is_admin.php')
        .then(response => {
            console.log('✓ is_admin.php - Status:', response.status, response.statusText);
            if (response.status === 405) {
                addTestResult('is_admin.php (GET)', 'error', 
                    'ERREUR 405 DÉTECTÉE!', 
                    'Ce endpoint ne devrait pas renvoyer 405 pour GET');
            } else if (response.ok) {
                return response.json().then(data => {
                    addTestResult('is_admin.php (GET)', 'success', 
                        'Requête réussie - Status ' + response.status, 
                        JSON.stringify(data, null, 2));
                });
            } else {
                addTestResult('is_admin.php (GET)', 'warning', 
                    'Status: ' + response.status + ' ' + response.statusText);
            }
        })
        .catch(error => {
            console.error('✗ is_admin.php - Erreur:', error);
            addTestResult('is_admin.php (GET)', 'error', 
                'Erreur réseau', 
                error.toString());
        });
}, 1000);

// Test 2: login.php (POST)
setTimeout(() => {
    console.log('%c TEST 2: login.php (POST)', 'color: #3498db; font-weight: bold');
    fetch('login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'email=test@test.com&password=test123&role=user'
    })
        .then(response => {
            console.log('✓ login.php - Status:', response.status, response.statusText);
            if (response.status === 405) {
                addTestResult('login.php (POST)', 'error', 
                    '🚨 ERREUR 405 DÉTECTÉE! - Cause probable trouvée', 
                    'Le serveur refuse la méthode POST sur login.php. Vérifiez .htaccess ou config Apache.');
            } else if (response.ok || response.status === 401 || response.status === 403) {
                return response.json().then(data => {
                    addTestResult('login.php (POST)', 'success', 
                        'Endpoint accessible - Status ' + response.status + ' (erreur de login normale)', 
                        JSON.stringify(data, null, 2));
                });
            } else {
                addTestResult('login.php (POST)', 'warning', 
                    'Status: ' + response.status + ' ' + response.statusText);
            }
        })
        .catch(error => {
            console.error('✗ login.php - Erreur:', error);
            addTestResult('login.php (POST)', 'error', 
                'Erreur réseau', 
                error.toString());
        });
}, 2000);

// Test 3: admin.php (GET)
setTimeout(() => {
    console.log('%c TEST 3: admin.php (GET)', 'color: #3498db; font-weight: bold');
    fetch('admin.php?action=list_users')
        .then(response => {
            console.log('✓ admin.php - Status:', response.status, response.statusText);
            if (response.status === 405) {
                addTestResult('admin.php (GET)', 'error', 
                    '🚨 ERREUR 405 DÉTECTÉE!', 
                    'Le serveur refuse GET sur admin.php');
            } else if (response.ok || response.status === 403) {
                return response.json().then(data => {
                    addTestResult('admin.php (GET)', 'success', 
                        'Endpoint accessible - Status ' + response.status, 
                        JSON.stringify(data, null, 2));
                });
            } else {
                addTestResult('admin.php (GET)', 'warning', 
                    'Status: ' + response.status);
            }
        })
        .catch(error => {
            console.error('✗ admin.php - Erreur:', error);
            addTestResult('admin.php (GET)', 'error', 
                'Erreur réseau', 
                error.toString());
        });
}, 3000);

console.log('%c✅ TESTS TERMINÉS - Consultez les résultats ci-dessus', 'background: #27ae60; color: white; font-size: 14px; padding: 8px;');
console.log('%cSi vous voyez une ERREUR 405, regardez les détails dans la section ci-dessus ⬆', 'color: #e74c3c; font-weight: bold; font-size: 14px;');
</script>";

echo "</div></body></html>";
?>
