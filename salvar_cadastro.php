<?php

header('Content-Type: application/json');

// Dados do banco de dados (já fornecidos)
$host = "mysql-projetointegradorunivesp.alwaysdata.net";
$user = "426539";
$password = "Univesp@25";
$database = "projetointegradorunivesp_25";

// Conecta-se ao banco de dados
$conn = new mysqli($host, $user, $password, $database);

// Verifica a conexão
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Falha na conexão com o banco de dados.']));
}

// Verifica se os dados foram enviados
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Define o fuso horário
    date_default_timezone_set('America/Sao_Paulo');

    // Pega o tipo de cadastro
    $tipo = $_POST['tipo'];
    
    // salvar apenas os campos dinâmicos
    unset($_POST['tipo']);

    // Converte formulário para JSON
    $dados_json = json_encode($_POST);

    $data_atual = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("INSERT INTO cadastros (tipo, data_cadastro, dados_json) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $tipo, $data_atual, $dados_json);

    if ($stmt->execute()) {
        // Envio bem-sucedido
        echo json_encode(['success' => true, 'message' => 'Dados salvos com sucesso!']);
    } else {
        // Erro no envio
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar os dados: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    // Requisição inválida
    echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
}

$conn->close();

?>
