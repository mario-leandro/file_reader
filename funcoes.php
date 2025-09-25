<?php

include_once __DIR__ . '/database/database.php';

function resposta(array $mensagem) {
    echo json_encode($mensagem);
}

function validarArquivo($arquivo) {
    $infoArquivo = finfo_open(FILEINFO_MIME_TYPE);
    $tipoArquivo = finfo_file($infoArquivo, $arquivo);
    finfo_close($infoArquivo);

    $tiposPermitidos = ['text/csv', 'text/plain', 'application/json', 'text/xml', 'application/xml'];

    return in_array($tipoArquivo, $tiposPermitidos);
}

function salvarNoBanco($db_connection, $dados) {
    try {
        $sql = $db_connection->prepare("INSERT INTO pedido (nome, telefone, email, rua_coleta, numero_coleta, bairro_coleta, cidade_coleta, estado_coleta, cep_coleta, rua_entrega, numero_entrega, bairro_entrega, cidade_entrega, estado_entrega, cep_entrega) VALUES (:nome, :telefone, :email, :rua_coleta, :numero_coleta, :bairro_coleta, :cidade_coleta, :estado_coleta, :cep_coleta, :rua_entrega, :numero_entrega, :bairro_entrega, :cidade_entrega, :estado_entrega, :cep_entrega)");
        
        $arr_dados = [
            ':nome' => $dados['cliente']['nome'],
            ':telefone' => $dados['cliente']['telefone'],
            ':email' => $dados['cliente']['email'],
            ':rua_coleta' => $dados['endereco_coleta']['rua'],
            ':numero_coleta' => $dados['endereco_coleta']['numero'],
            ':bairro_coleta' => $dados['endereco_coleta']['bairro'],
            ':cidade_coleta' => $dados['endereco_coleta']['cidade'],
            ':estado_coleta' => $dados['endereco_coleta']['estado'],
            ':cep_coleta' => $dados['endereco_coleta']['cep'],
            ':rua_entrega' => $dados['endereco_entrega']['rua'],
            ':numero_entrega' => $dados['endereco_entrega']['numero'],
            ':bairro_entrega' => $dados['endereco_entrega']['bairro'],
            ':cidade_entrega' => $dados['endereco_entrega']['cidade'],
            ':estado_entrega' => $dados['endereco_entrega']['estado'],
            ':cep_entrega' => $dados['endereco_entrega']['cep']
        ];

        $sql->execute($arr_dados);
        resposta(["success" => true, "mensagem" => "Dados salvos com sucesso."]);
    } catch (PDOException $e) {
        resposta(["success" => false, "error" => "Database insertion failed: " . $e->getMessage()]);
        return false;
    }
}

function processarCSV($db_connection, $arquivo) {
    if(!validarArquivo($arquivo)) {
        resposta(["success" => false, "mensagem" => "Tipo de arquivo não permitido."]);
        return;
    }
    
    if (($handle = fopen($arquivo, 'r')) !== false) {
        $header = fgetcsv($handle, 1000, ',');
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $row = array_combine($header, $data);
            
            // Reorganizar os dados para o formato aninhado
            $dados_formatados = [
                'cliente' => [
                    'nome' => $row['nome'],
                    'telefone' => $row['telefone'],
                    'email' => $row['email']
                ],
                'endereco_coleta' => [
                    'rua' => $row['rua_coleta'],
                    'numero' => $row['numero_coleta'],
                    'bairro' => $row['bairro_coleta'],
                    'cidade' => $row['cidade_coleta'],
                    'estado' => $row['estado_coleta'],
                    'cep' => $row['cep_coleta'],
                ],
                'endereco_entrega' => [
                    'rua' => $row['rua_entrega'],
                    'numero' => $row['numero_entrega'],
                    'bairro' => $row['bairro_entrega'],
                    'cidade' => $row['cidade_entrega'],
                    'estado' => $row['estado_entrega'],
                    'cep' => $row['cep_entrega'],
                ]
            ];

            var_dump($dados_formatados);

            salvarNoBanco($db_connection, $dados_formatados);
        }
        fclose($handle);
        resposta(["success" => true, "mensagem" => "Arquivo CSV processado com sucesso."]);
    } else {
        resposta(["success" => false, "mensagem" => "Erro ao abrir o arquivo CSV."]);
    }
}

function processarJSON($db_connection, $arquivo) {
    if (!validarArquivo($arquivo)) {
        resposta(["success" => false, "mensagem" => "Tipo de arquivo não permitido."]);
        return;
    }

    $db_connection = getConnectionDB();

    $infoArquivo = finfo_open(FILEINFO_MIME_TYPE);
    $tipoArquivo = finfo_file($infoArquivo, $arquivo);
    finfo_close($infoArquivo);

    if ($tipoArquivo !== 'application/json') {
        resposta(["success" => false, "mensagem" => "Tipo de arquivo não suportado para JSON."]);
        return;
    }

    $conteudo_arquivo = file_get_contents($arquivo);
    $dados = json_decode($conteudo_arquivo, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        resposta(["success" => false, "mensagem" => "Erro ao decodificar JSON: " . json_last_error_msg()]);
        return;
    }

    foreach ($dados as $item) {
        $arr_dados = [
            'cliente' => [
                'nome' => $item['cliente']['nome'],
                'telefone' => $item['cliente']['telefone'],
                'email' => $item['cliente']['email']
            ],
            'endereco_coleta' => [
                'rua' => $item['endereco_coleta']['rua'],
                'numero' => $item['endereco_coleta']['numero'],
                'bairro' => $item['endereco_coleta']['bairro'],
                'cidade' => $item['endereco_coleta']['cidade'],
                'estado' => $item['endereco_coleta']['estado'],
                'cep' => $item['endereco_coleta']['cep'],
            ],
            'endereco_entrega' => [
                'rua' => $item['endereco_entrega']['rua'],
                'numero' => $item['endereco_entrega']['numero'],
                'bairro' => $item['endereco_entrega']['bairro'],
                'cidade' => $item['endereco_entrega']['cidade'],
                'estado' => $item['endereco_entrega']['estado'],
                'cep' => $item['endereco_entrega']['cep'],
            ]
        ];

        // var_dump($arr_dados);

        salvarNoBanco($db_connection, $arr_dados);
    }

    resposta(["success" => true, "mensagem" => "Arquivo JSON processado com sucesso."]);
}

function processarXML($db_connection, $arquivo) {
    libxml_use_internal_errors(true);

    $xml = simplexml_load_file($arquivo);
    if (!$xml) {
        echo "Erro ao carregar XML\n";
        foreach (libxml_get_errors() as $error) {
            echo $error->message . "\n";
        }
        libxml_clear_errors();
        return;
    }

    foreach ($xml->projeto as $projeto) {
        $arr_dados = [
            'cliente' => [
                'nome'  => (string) $projeto->cliente->nome,
                'telefone' => (string) $projeto->cliente->telefone,
                'email' => (string) $projeto->cliente->email
            ],
            'endereco_coleta' => [
                'rua'    => (string) $projeto->endereco_coleta->rua,
                'numero' => (string) $projeto->endereco_coleta->numero,
                'bairro' => (string) $projeto->endereco_coleta->bairro,
                'cidade' => (string) $projeto->endereco_coleta->cidade,
                'estado' => (string) $projeto->endereco_coleta->estado,
                'cep'    => (string) $projeto->endereco_coleta->cep,
            ],
            'endereco_entrega' => [
                'rua'    => (string) $projeto->endereco_entrega->rua,
                'numero' => (string) $projeto->endereco_entrega->numero,
                'bairro' => (string) $projeto->endereco_entrega->bairro,
                'cidade' => (string) $projeto->endereco_entrega->cidade,
                'estado' => (string) $projeto->endereco_entrega->estado,
                'cep'    => (string) $projeto->endereco_entrega->cep,
            ]
        ];

        salvarNoBanco($db_connection, $arr_dados);
    }


    resposta(["success" => true, "mensagem" => "Arquivo XML processado com sucesso."]);
}
