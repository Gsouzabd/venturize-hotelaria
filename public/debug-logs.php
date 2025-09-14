<?php

// Script temporário para verificar logs
$logFile = '/var/www/html/storage/logs/laravel.log';

if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $lines = explode("\n", $logs);
    $debugLines = [];
    
    foreach ($lines as $line) {
        if (strpos($line, 'Debug impressão') !== false) {
            $debugLines[] = $line;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'debug_lines' => array_slice($debugLines, -10), // Últimas 10 linhas
        'total_debug_lines' => count($debugLines)
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Log file not found',
        'expected_path' => $logFile
    ]);
}