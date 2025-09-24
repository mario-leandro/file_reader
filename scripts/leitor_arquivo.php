<?php

include __DIR__ . '/../common.php';

// Verifica se o script está sendo executado a partir da linha de comando (CLI)
if (php_sapi_name() === 'cli') {
    // Lógica para CLI:
    // Pega o caminho do arquivo do primeiro argumento da linha de comando
    // O nome do arquivo é obtido a partir de $argv[1]
    global $argv;
    if (isset($argv[1])) {
        $filePath = $argv[1];
        if (file_exists($filePath)) {
            // Monta o array $_FILES manualmente para simular o upload
            $_FILES['file'] = [
                'name' => basename($filePath),
                'type' => mime_content_type($filePath),
                'tmp_name' => $filePath,
                'error' => 0,
                'size' => filesize($filePath)
            ];
            // Define o método da requisição como POST para o processamento
            $_SERVER['REQUEST_METHOD'] = 'POST';
        } else {
            resposta(["success" => false, "mensagem" => "Arquivo não encontrado."]);
            exit;
        }
    } else {
        resposta(["success" => false, "mensagem" => "Nenhum arquivo especificado."]);
        exit;
    }
}

headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    resposta(["success" => false, "mensagem" => "Método não permitido."]);
    exit;
}

if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
    
    if (!validarArquivo($_FILES['file']['tmp_name'])) {
        resposta(["success" => false, "mensagem" => "Tipo de arquivo não permitido."]);
        exit;
    }
    
    $db_connection = getConnectionDB();
    
    $infoArquivo = finfo_open(FILEINFO_MIME_TYPE);
    $tipoArquivo = finfo_file($infoArquivo, $_FILES['file']['tmp_name']);
    finfo_close($infoArquivo);

    switch ($tipoArquivo) {
        case 'text/csv' || 'text/plain' || 'application/vnd.ms-excel':
            processarCSV($db_connection, $_FILES['file']['tmp_name']);
            echo "Processado CSV\n";
            break;
        case 'application/json':
            // processarJSON($db_connection, $_FILES['file']['tmp_name']);
            echo "Processado JSON\n";
            break;
        case 'application/xml':
        case 'text/xml':
            // processarXML($db_connection, $_FILES['file']['tmp_name']);
            echo "Processado XML\n";
            break;
        default:
            resposta(["success" => false, "mensagem" => "Tipo de arquivo não suportado."]);
            break;
    }
} else {
    resposta(["success" => false, "mensagem" => "Nenhum arquivo enviado ou erro no upload."]);
}