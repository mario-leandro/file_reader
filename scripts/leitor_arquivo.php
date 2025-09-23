<?php

include __DIR__ . '/../common.php';

headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    resposta(405, ["success" => false, "mensagem" => "Método não permitido."]);
    exit;
}

if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
    
    if (!validarArquivo($_FILES['file']['tmp_name'])) {
        resposta(400, ["success" => false, "mensagem" => "Tipo de arquivo não permitido."]);
        exit;
    }
    
    $db_connection = getConnectionDB();
    
    $infoArquivo = finfo_open(FILEINFO_MIME_TYPE);
    $tipoArquivo = finfo_file($infoArquivo, $_FILES['file']['tmp_name']);
    finfo_close($infoArquivo);

    switch ($tipoArquivo) {
        case 'text/csv':
        case 'text/plain':
            processarCSV($db_connection, $_FILES['file']['tmp_name']);
            break;
        case 'application/json':
            processarJSON($db_connection, $_FILES['file']['tmp_name']);
            break;
        case 'application/xml':
        case 'text/xml':
            processarXML($db_connection, $_FILES['file']['tmp_name']);
            break;
        default:
            resposta(400, ["success" => false, "mensagem" => "Tipo de arquivo não suportado."]);
            break;
    }
} else {
    resposta(400, ["success" => false, "mensagem" => "Nenhum arquivo enviado ou erro no upload."]);
}