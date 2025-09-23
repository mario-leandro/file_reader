<?php

function headers() {
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Origin: *");
}

function resposta(int $status, array $mensagem) {
    http_response_code($status);
    echo json_encode($mensagem);
}