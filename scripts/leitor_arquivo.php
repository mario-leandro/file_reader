<?php

include __DIR__ . '/../common.php';

headers();

if($_FILES['file'] && $_FILES['file']['error'] === 0) {

    $infoArquivo = finfo_open(FILEINFO_MIME_TYPE);
    $tipoArquivo = finfo_file($infoArquivo, $_FILES['file']['tmp_name']);
    finfo_close($infoArquivo);

    // aquivos permitidos, xml, json, csv
    $tiposDeArquivos = ["application/xml", "text/xml", "application/json", "text/csv", "text/plain"];

    // Verifica se o tipo de arquivo é permitido
    if(!in_array($tipoArquivo, $tiposDeArquivos)) {
        resposta(400, ["success" => false, "mensagem" => "Tipo de arquivo não permitido."]);
    }

    $pegarArquivos = curl_init('http://localhost:8000/api/upload');
    
} else {
    resposta(400, ["success" => false, "mensagem" => "Nenhum arquivo enviado ou erro no upload."]);
}