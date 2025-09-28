<?php
// fuso horário para 'America/Sao_Paulo'
date_default_timezone_set('America/Sao_Paulo');

// Dados do banco de dados
$host = "mysql-projetointegradorunivesp.alwaysdata.net";
$user = "426539";
$password = "Univesp@25";
$database = "projetointegradorunivesp_25";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>