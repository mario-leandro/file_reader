<?php

include __DIR__ . '/../common.php';

headers();

if($_FILES['file'] && $_FILES['file']['error'] === 0) {
    $arquivo = $_FILES['file']['name'];

    $pastaArquivos = __DIR__ . '/../arquivos/' . $arquivo;

    $infoArquivo = finfo_open(FILEINFO_MIME_TYPE);
    $tipoArquivo = finfo_file($infoArquivo, $_FILES['file']['tmp_name']);
    finfo_close($infoArquivo);

    // aquivos permitidos, xml, json, csv
    $tiposDeArquivos = ["application/xml", "text/xml", "application/json", "text/csv", "text/plain"];

    
} else {
    resposta(400, ["success" => false, "error" => "No file uploaded or upload error."]);
}