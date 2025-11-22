<?php
$logFile = 'C:\\xampp\\apache\\logs\\error.log';

if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -50); // Les 50 dernières lignes
    
    echo "<h2>Dernières erreurs Apache</h2>";
    echo "<pre style='background: #f5f5f5; padding: 10px; overflow: auto; max-height: 500px;'>";
    
    foreach ($lastLines as $line) {
        if (stripos($line, 'erreur') !== false || stripos($line, 'error') !== false) {
            echo htmlspecialchars($line);
        }
    }
    
    echo "</pre>";
    
    echo "<hr>";
    echo "<h3>Toutes les 50 dernières lignes:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; overflow: auto; max-height: 500px;'>";
    echo htmlspecialchars(implode('', $lastLines));
    echo "</pre>";
} else {
    echo "<p>Le fichier error.log n'existe pas à cet emplacement.</p>";
}
?>
