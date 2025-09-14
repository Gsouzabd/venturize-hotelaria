<?php

// Script para verificar logs do Laravel
$logPath = '/home/venturize/venturize.codebeans.dev/storage/logs/laravel.log';

header('Content-Type: application/json');

if (!file_exists($logPath)) {
    echo json_encode([
        'error' => 'Log file not found',
        'path' => $logPath
    ]);
    exit;
}

// Ler as últimas 50 linhas do log
$lines = [];
$file = new SplFileObject($logPath);
$file->seek(PHP_INT_MAX);
$totalLines = $file->key();

// Pegar as últimas 50 linhas
$startLine = max(0, $totalLines - 50);
$file->seek($startLine);

while (!$file->eof()) {
    $line = $file->current();
    if (stripos($line, 'PrintController') !== false || 
        stripos($line, 'impressao') !== false ||
        stripos($line, 'pedido_id') !== false) {
        $lines[] = trim($line);
    }
    $file->next();
}

echo json_encode([
    'success' => true,
    'total_lines' => $totalLines,
    'matching_lines' => count($lines),
    'logs' => $lines
], JSON_PRETTY_PRINT);
?>