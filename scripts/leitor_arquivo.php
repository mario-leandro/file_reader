<?php

include __DIR__ . '/../common.php';

if (php_sapi_name() === 'cli') {
    global $argv;

    if (!isset($argv[1])) {
        echo "Uso: php leitor_arquivo.php <caminho_arquivo>\n";
        exit(1);
    }

    $filePath = $argv[1];

    if (!file_exists($filePath)) {
        echo "Arquivo não encontrado: $filePath\n";
        exit(1);
    }

    $db_connection = getConnectionDB();

    $tipoArquivo = mime_content_type($filePath);

    switch ($tipoArquivo) {
        case 'text/csv':
            processarCSV($db_connection, $filePath);
            break;

        case 'application/json':
            processarJSON($db_connection, $filePath);
            break;

        case 'text/xml':
        case 'application/xml':
        case 'text/plain': // Alguns arquivos XML podem ser detectados como text/plain
            processarXML($db_connection, $filePath);
            break;

        default:
            echo "Tipo de arquivo não suportado: $tipoArquivo\n";
            break;
    }
    exit;
}

if (php_sapi_name() !== 'cli') {
    headers();
}