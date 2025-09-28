<?php
header('Content-Type: application/json');

include 'conexao.php';

$response = ['success' => false, 'message' => 'Ação inválida.'];

// Ação de Excluir (mantida sem alterações)
if (isset($_GET['action']) && $_GET['action'] == 'excluir' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM cadastros WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Cadastro excluído com sucesso.'];
    } else {
        $response = ['success' => false, 'message' => 'Erro ao excluir cadastro: ' . $stmt->error];
    }
    $stmt->close();

// Ação de Alterar (modificada para usar a coluna status_turma)
} elseif (isset($_POST['action']) && $_POST['action'] == 'alterar' && isset($_POST['id']) && isset($_POST['status'])) {
    $id = intval($_POST['id']);
    $status = $_POST['status'];
    
    // Prepara a consulta SQL para atualizar apenas a nova coluna
    $stmt = $conn->prepare("UPDATE cadastros SET status_turma = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Status do cadastro alterado com sucesso.'];
    } else {
        $response = ['success' => false, 'message' => 'Erro ao alterar status: ' . $stmt->error];
    }
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>