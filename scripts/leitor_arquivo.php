<?php

include __DIR__ . '/../common.php';

if (php_sapi_name() === 'cli') {
    global $argv;

    if (!isset($argv[1])) {
        resposta(["success" => false, "mensagem" => "Uso: php leitor_arquivo.php <caminho_do_arquivo>\n"]);
        exit(1);
    }

    $filePath = $argv[1];

    if (!file_exists($filePath)) {
        resposta(["success" => false, "mensagem" => "Arquivo não encontrado: $filePath\n"]);
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
        case 'text/plain':
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