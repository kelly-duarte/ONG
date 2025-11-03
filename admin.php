<?php
// INICIAR SESSÃO
session_start();

// Sistema de autenticação por senha
$senha_correta = "jovem"; 
$max_tentativas = 3;
$bloqueio_tempo = 300;

// Inicializar variáveis de sessão
if (!isset($_SESSION['tentativas'])) {
    $_SESSION['tentativas'] = 0;
}
if (!isset($_SESSION['bloqueado_ate'])) {
    $_SESSION['bloqueado_ate'] = 0;
}

// Verificar se o usuário já está autenticado
$autenticado = isset($_SESSION['autenticado']) && $_SESSION['autenticado'] === true;

// Sistema de Status por Tipo de Cadastro
$status_por_tipo = array(
    'curso_profissional' => array(
        'aguardando_contato' => '🟡 Aguardando contato',
        'matriculado_turma' => '✅ Matriculado na próxima turma', 
        'curso_concluido' => '🎓 Curso Concluído',
        'desistente' => '❌ Desistente'
    ),
    'futebol' => array(
        'aguardando_contato' => '🟡 Aguardando contato',
        'matriculado' => '✅ Matriculado',
        'inativo' => '⚪ Inativo',
        'desistente' => '❌ Desistente'
    ),
    'parcerias' => array(
        'aguardando_contato' => '🟡 Aguardando contato', 
        'entrevista_agendada' => '📅 Entrevista Agendada',
        'parceria_ativa' => '🤝 Parceria Ativa',
        'parceria_encerrada' => '🔒 Parceria Encerrada'
    ),
    'voluntariado' => array(
        'aguardando_contato' => '🟡 Aguardando contato',
        'ativo' => '🌟 Ativo',
        'inativo' => '⚪ Inativo'
    ),
    'projeto_mulheres' => array(
        'aguardando_contato' => '🟡 Aguardando contato',
        'ativo' => '🌟 Ativo', 
        'inativo' => '⚪ Inativo'
    )
);

// Processar o envio do formulário de autenticação
if (!$autenticado && isset($_POST['senha'])) {
    if (time() < $_SESSION['bloqueado_ate']) {
        $tempo_restante = $_SESSION['bloqueado_ate'] - time();
        $mensagem_erro = "Acesso bloqueado. Tente novamente em " . ceil($tempo_restante/60) . " minutos.";
    } else {
        if ($_POST['senha'] === $senha_correta) {
            $_SESSION['autenticado'] = true;
            $_SESSION['tentativas'] = 0;
            $autenticado = true;
        } else {
            $_SESSION['tentativas']++;
            $tentativas_restantes = $max_tentativas - $_SESSION['tentativas'];
            
            if ($tentativas_restantes > 0) {
                $mensagem_erro = "Senha incorreta. Você tem $tentativas_restantes tentativa(s) restante(s).";
            } else {
                $_SESSION['bloqueio_ate'] = time() + $bloqueio_tempo;
                $mensagem_erro = "Número máximo de tentativas excedido. Tente novamente em 5 minutos.";
            }
        }
    }
}

// Processar logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit();
}

// Verificar qual conteúdo mostrar
$conteudo_ativo = isset($_GET['conteudo']) && $_GET['conteudo'] === 'painel' ? 'painel' : 'cadastros';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Instituto Integração Jovem</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-main {
            padding: 0px;
            max-width: 1200px;
            margin: 10px auto;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .admin-main h2 {
            text-align: center;
            color: #003366;
            margin-bottom: 10px;
            display: flex;
            flex-direction: column;
            line-height: 1.4;
        }
        .admin-table-container {
            margin-bottom: 40px;
            overflow-x: auto;
        }
        .admin-table-container h3 {
            background-color: #003366;
            color: white;
            padding: 10px 15px;
            border-radius: 5px 5px 0 0;
            margin: 0;
        }
        .admin-table-container h4 {
            background-color: #e0e0e0;
            padding: 10px;
            margin: 20px 0 0 0;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .admin-table th, .admin-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            font-size: 14px;
            vertical-align: top;
        }
        .admin-table th {
            background-color: #e6e6e6;
            color: #333;
            font-weight: bold;
        }
        .admin-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .admin-table .actions button {
            cursor: pointer;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 13px;
        }
        .btn-alterar {
            background-color: #007bff;
        }
        .btn-excluir {
            background-color: #dc3545;
        }
        .popup {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }
        .popup-content {
            background: white;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .admin-table th:nth-child(1),
        .admin-table td:nth-child(1) {
            width: 15%;
        }

        .admin-table th:nth-child(2),
        .admin-table td:nth-child(2) {
            width: 35%;
        }

        .admin-table th:nth-child(3),
        .admin-table td:nth-child(3) {
            width: 10%;
        }

        .admin-table th:nth-child(4),
        .admin-table td:nth-child(4) {
            width: 15%;
        }

        .admin-table th:nth-child(5),
        .admin-table td:nth-child(5) {
            width: 10%;
        }

        .status-cell {
            text-align: center;
            font-weight: bold;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            display: inline-block;
            min-width: 120px;
        }

        .status-aguardando_contato {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-matriculado, 
        .status-matriculado_turma,
        .status-parceria_ativa,
        .status-ativo {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .status-curso_concluido {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-entrevista_agendada {
            background-color: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
        }

        .status-inativo,
        .status-parceria_encerrada {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-desistente {
            background-color: #f5e6cc;
            color: #8a6d3b;
            border: 1px solid #faebcc;
        }

        .popup-buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .admin-table td:nth-child(2) ul {
            margin: 0;
            padding: 0;
            max-height: 120px;
            overflow-y: auto;
        }

        .admin-table td:nth-child(2) li {
            margin-bottom: 5px;
            word-break: break-word;
            list-style-type: none;
            border-bottom: 1px dotted #eee;
            padding: 3px 0;
        }

        .admin-table td:nth-child(2) li:last-child {
            border-bottom: none;
        }

        .header-with-icon {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            position: relative;
        }

        .botao-conteudo {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #003366;
            padding: 15px;
            border-radius: 10px;
            background-color: #f0f4f8;
            border: 2px solid #003366;
            min-width: 120px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .botao-conteudo.ativo {
            background-color: #003366;
            color: white;
            transform: scale(1.05);
        }

        .botao-conteudo:hover {
            background-color: #003366;
            color: white;
            transform: scale(1.05);
        }

        .botao-conteudo img {
            width: 50px;
            height: 50px;
            margin-bottom: 8px;
            object-fit: contain;
        }

        .botao-conteudo span {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
        }

        /* Seções do painel */
        .painel-section {
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .painel-section h3 {
            color: #003366;
            margin-bottom: 15px;
        }

        /* Formulário do painel */
        .painel-section form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: end;
        }

        .painel-section input[type="text"],
        .painel-section input[type="url"] {
            flex: 1;
            min-width: 200px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .mensagem {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: center;
        }
        .sucesso {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .erro {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .btn-sair {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 10px;
        }

        .btn-sair:hover {
            background-color: #c82333;
        }

        .conteudo-dinamico {
            display: none;
        }

        .conteudo-dinamico.ativo {
            display: block;
        }

        @media (max-width: 768px) {
            .admin-main {
                padding: 0px;
                max-width: 100%;
                margin: 10px auto;
            }

            .admin-table {
               font-size: 12px;
               min-width: 600px; 
            }

            .admin-table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .admin-main h2 {
                text-align: center;
                color: #003366;
                margin-bottom: 10px;
                display: flex;
                flex-direction: column;
                line-height: 1.4;
            }

            .admin-table th:nth-child(1),
            .admin-table td:nth-child(1) {
                width: 15%;
                min-width: 80px;
            }

            .admin-table th:nth-child(2),
            .admin-table td:nth-child(2) {
                width: 30%;
                min-width: 120px;
            }

            .admin-table th:nth-child(3),
            .admin-table td:nth-child(3) {
                min-width: 100px;
                white-space: nowrap;
            }

            .admin-table th:nth-child(4),
            .admin-table td:nth-child(4) {
                min-width: 100px;
                white-space: nowrap;
            }

            .admin-table th:nth-child(5),
            .admin-table td:nth-child(5) {
                min-width: 120px; 
            }

            .admin-table td:nth-child(3),
            .admin-table td:nth-child(4) {
                white-space: normal;
                font-size: 12px;
                word-break: break-word;
                text-align: center;
            }

            .admin-table th:nth-child(4),
            .admin-table td:nth-child(4) {
                min-width: 120px; 
            }
            .admin-table .actions {
                display: flex;
                flex-direction: column;
                gap: 5px;
                align-items: center;
                text-align: center;
                margin-top: 5px;
            }
            
            .admin-table-container {
                margin-bottom: 30px;
            }

            .header-with-icon {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .botao-conteudo {
                flex-direction: row;
                gap: 10px;
                min-width: auto;
                padding: 12px 15px;
                width: 100%;
                justify-content: center;
            }
            
            .botao-conteudo img {
                width: 30px;
                height: 30px;
                margin-bottom: 0;
            }
            
            .painel-section form {
                flex-direction: column;
            }
            
            .painel-section input[type="text"],
            .painel-section input[type="url"] {
                min-width: 100%;
            }
        }

        .auth-container {
            max-width: 500px;
            margin: 100px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .auth-container h2 {
            color: #003366;
            margin-bottom: 20px;
        }

        .auth-message {
            padding: 10px;
            margin: 15px 0;
            border-radius: 5px;
            display: none;
        }

        .auth-error {
            background-color: #ffebee;
            color: #d32f2f;
            border: 1px solid #f5c6cb;
        }

        .auth-form {
            margin-bottom: 20px;
        }

        .auth-input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .auth-btn {
            background-color: #003366;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 5px;
        }

        .auth-btn:hover {
            background-color: #00264d;
        }

        .auth-btn-secondary {
            background-color: #6c757d;
        }

        .auth-btn-secondary:hover {
            background-color: #5a6268;
        }

        .attempts-info {
            margin-top: 15px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<?php
if (!$autenticado) {
    echo '<main class="admin-main">';
    echo '<div class="auth-container">';
    echo '<h2>Acesso Restrito</h2>';
    echo '<p>Esta área é restrita a administradores. Por favor, insira a senha para continuar.</p>';
    
    if (isset($mensagem_erro)) {
        echo '<div class="auth-message auth-error">' . $mensagem_erro . '</div>';
        echo '<script>document.addEventListener("DOMContentLoaded", function() { document.querySelector(".auth-message").style.display = "block"; });</script>';
    }
    
    if (time() < $_SESSION['bloqueado_ate']) {
        $tempo_restante = $_SESSION['bloqueado_ate'] - time();
        echo '<div class="auth-message auth-error">Acesso bloqueado. Tente novamente em ' . ceil($tempo_restante/60) . ' minutos.</div>';
        echo '<script>document.addEventListener("DOMContentLoaded", function() { document.querySelector(".auth-message").style.display = "block"; });</script>';
        echo '<button class="auth-btn auth-btn-secondary" onclick="window.location.href=\'index.php\'">Voltar ao Início</button>';
    } else {
        echo '<form method="POST" class="auth-form">';
        echo '<input type="password" name="senha" class="auth-input" placeholder="Digite a senha" required autocomplete="off">';
        echo '<div class="attempts-info">Tentativas restantes: ' . ($max_tentativas - $_SESSION['tentativas']) . '</div>';
        echo '<button type="submit" class="auth-btn">Acessar</button>';
        echo '<button type="button" class="auth-btn auth-btn-secondary" onclick="window.location.href=\'index.php\'">Voltar ao Início</button>';
        echo '</form>';
    }
    
    echo '</div>';
    echo '</main>';
    
    include 'footer.php';
    exit();
}
?>

<main class="admin-main">
    <div class="header-with-icon">
        <div class="botoes-conteudo" style="display: flex; gap: 20px; align-items: center;">
            <div class="botao-conteudo <?= $conteudo_ativo === 'cadastros' ? 'ativo' : '' ?>" 
                 onclick="window.location.href='?conteudo=cadastros'">
                <img src="https://uploads.onecompiler.io/43fvz8gg6/43vemwgzp/cadastros.png" alt="Relatórios de Cadastros">
                <span>Relatórios de Cadastros</span>
            </div>
            <div class="botao-conteudo <?= $conteudo_ativo === 'painel' ? 'ativo' : '' ?>" 
                 onclick="window.location.href='?conteudo=painel'">
                <img src="https://uploads.onecompiler.io/43fvz8gg6/43vemwgzp/painel.png" alt="Painel Visual">
                <span>Painel Visual</span>
            </div>
        </div>
        <a href="?logout=true" class="btn-sair">Sair</a>
    </div>

    <!-- Conteúdo dos Relatórios de Cadastros -->
    <div id="conteudo-cadastros" class="conteudo-dinamico <?= $conteudo_ativo === 'cadastros' ? 'ativo' : '' ?>">
        <?php
        include 'conexao.php';

        $tipos_cadastros = array(
            'projeto_mulheres' => 'Projeto Mulheres do Amor',
            'curso_profissional' => 'Curso Qualificação Profissional',
            'futebol' => 'Escolinha de Futebol',
            'parcerias' => 'Parcerias e doações',
            'voluntariado' => 'Voluntariado'
        );

        foreach ($tipos_cadastros as $tipo_slug => $tipo_titulo) {
            echo "<div class='admin-table-container'>";
            echo "<h3>" . htmlspecialchars($tipo_titulo) . "</h3>";
            
            if ($tipo_slug === 'curso_profissional') {
                $sql = "SELECT id, dados_json, data_cadastro, status FROM cadastros WHERE tipo = ? ORDER BY data_cadastro DESC";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $tipo_slug);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $cursos_agrupados = array();
                    while ($row = $result->fetch_assoc()) {
                        $dados = json_decode($row['dados_json'], true);
                        $curso_interesse = isset($dados['curso_interesse']) ? $dados['curso_interesse'] : 'Não especificado';
                        
                        if (!isset($cursos_agrupados[$curso_interesse])) {
                            $cursos_agrupados[$curso_interesse] = array();
                        }
                        $cursos_agrupados[$curso_interesse][] = $row;
                    }
                    
                    foreach ($cursos_agrupados as $curso_nome => $registros) {
                        echo "<h4>Curso: " . htmlspecialchars($curso_nome) . " (" . count($registros) . " inscritos)</h4>";
                        
                        echo "<table class='admin-table'>";
                        echo "<thead><tr><th>Nome</th><th>Dados</th><th>Data Cadastro</th><th>Status</th><th class='actions'>Ações</th></tr></thead>";
                        echo "<tbody>";
                        
        foreach ($registros as $row) {
            $dados = json_decode($row['dados_json'], true);
            $nome = isset($dados['nome_completo']) ? $dados['nome_completo'] : 'N/A';
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($nome) . "</td>";
            echo "<td>";
            echo "<ul>";
            foreach ($dados as $key => $value) {
                if ($key !== 'curso_interesse') {
                    echo "<li><strong>" . htmlspecialchars(str_replace('_', ' ', $key)) . ":</strong> " . htmlspecialchars($value) . "</li>";
                }
            }
            echo "</ul>";
            echo "</td>";
            echo "<td>" . date('d/m/Y H:i', strtotime($row['data_cadastro'])) . "</td>";
            echo "<td class='status-cell'>";
            $status_atual = $row['status'] ?? 'aguardando_contato';
            $status_texto = $status_por_tipo['curso_profissional'][$status_atual] ?? '🟡 Aguardando contato';
            echo "<span class='status-badge status-" . htmlspecialchars($status_atual) . "'>" . $status_texto . "</span>";
            echo "</td>";
            echo "<td class='actions'>";
            echo "<button class='btn-alterar' onclick='openPopup(" . $row['id'] . ", \"curso_profissional\", " . json_encode($dados) . ", \"" . htmlspecialchars($status_atual) . "\")'>Alterar Status</button>";
            echo "<button class='btn-excluir' onclick='excluirCadastro(" . $row['id'] . ")'>Excluir</button>";
            echo "</td>";
            echo "</tr>";
        }
                        echo "</tbody></table>";
                    }
                } else {
                    echo "<p>Nenhum cadastro encontrado para esta categoria.</p>";
                }
                $stmt->close();
                
            } else {
                $sql = "SELECT id, dados_json, data_cadastro, status FROM cadastros WHERE tipo = ? ORDER BY data_cadastro DESC";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $tipo_slug);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    echo "<table class='admin-table'>";
                    echo "<thead><tr><th>Nome</th><th>Dados</th><th>Data Cadastro</th><th>Status</th><th class='actions'>Ações</th></tr></thead>";
                    echo "<tbody>";

                    while ($row = $result->fetch_assoc()) {
                        $dados = json_decode($row['dados_json'], true);
                        $nome = 'N/A';
                        
                        if ($tipo_slug === 'projeto_mulheres') {
                            if (isset($dados['nome_completo'])) {
                                $nome = $dados['nome_completo'];
                            } elseif (isset($dados['nome_mulher'])) {
                                $nome = $dados['nome_mulher'];
                            }
                        } elseif ($tipo_slug === 'curso_profissional') {
                            if (isset($dados['nome_completo'])) {
                                $nome = $dados['nome_completo'];
                            }
                        } elseif ($tipo_slug === 'futebol') {
                            if (isset($dados['nome_aluno']) && isset($dados['nome_completo'])) {
                                $nome = $dados['nome_completo'] . ' (' . $dados['nome_aluno'] . ')';
                            } elseif (isset($dados['nome_aluno'])) {
                                $nome = $dados['nome_aluno'];
                            } elseif (isset($dados['nome_completo'])) {
                                $nome = $dados['nome_completo'];
                            }
                        } elseif ($tipo_slug === 'parcerias') {
                            if (isset($dados['nome_empresa_doador'])) {
                                $nome = $dados['nome_empresa_doador'];
                            } elseif (isset($dados['empresa'])) {
                                $nome = $dados['empresa'];
                            }
                        } elseif ($tipo_slug === 'voluntariado') {
                            if (isset($dados['nome_completo'])) {
                                $nome = $dados['nome_completo'];
                            } elseif (isset($dados['nome_voluntario'])) {
                                $nome = $dados['nome_voluntario'];
                            }
                        }

                        if ($nome === 'N/A') {
                            $campos_nome = array('nome_completo', 'nome', 'nome_aluno', 'nome_voluntario', 'nome_mulher', 'nome_empresa_doador', 'empresa');
                            foreach ($campos_nome as $campo) {
                                if (isset($dados[$campo]) && !empty($dados[$campo])) {
                                    $nome = $dados[$campo];
                                    break;
                                }
                            }
                        }

                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($nome) . "</td>";
                        echo "<td>";
                        echo "<ul>";
                        foreach ($dados as $key => $value) {
                            echo "<li><strong>" . htmlspecialchars(str_replace('_', ' ', $key)) . ":</strong> " . htmlspecialchars($value) . "</li>";
                        }
                        echo "</ul>";
                        echo "</td>";
                        echo "<td>" . date('d/m/Y H:i', strtotime($row['data_cadastro'])) . "</td>";
                        echo "<td class='status-cell'>";
                        $status_atual = $row['status'] ?? 'aguardando_contato';
                        $status_texto = $status_por_tipo[$tipo_slug][$status_atual] ?? '🟡 Aguardando contato';
                        echo "<span class='status-badge status-" . htmlspecialchars($status_atual) . "'>" . $status_texto . "</span>";
                        echo "</td>";
                        echo "<td class='actions'>";
                        echo "<button class='btn-alterar' onclick='openPopup(" . $row['id'] . ", \"" . htmlspecialchars($tipo_slug) . "\", " . json_encode($dados) . ", \"" . htmlspecialchars($status_atual) . "\")'>Alterar Status</button>";
                        echo "<button class='btn-excluir' onclick='excluirCadastro(" . $row['id'] . ")'>Excluir</button>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    echo "</tbody></table>";
                } else {
                    echo "<p>Nenhum cadastro encontrado para esta categoria.</p>";
                }
                $stmt->close();
            }
            
            echo "</div>";
        }

        $conn->close();
        ?>
    </div>

<!-- Conteúdo do Painel Visual -->
    <div id="conteudo-painel" class="conteudo-dinamico <?= $conteudo_ativo === 'painel' ? 'ativo' : '' ?>">
        <?php
        include 'conexao.php';

// Função para verificar e processar URLs do Instagram
    function processarUrlImagem($url) {
        if (strpos($url, 'instagram.com') !== false) {
            // É uma URL do Instagram, usa o proxy
            return 'proxy.php?url=' . urlencode($url);
        }
        // Não é Instagram, retorna a URL original
        return $url;
    }

            // Processar formulários do painel (adicionar/remover links)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['url']) && isset($_POST['nome']) && isset($_POST['secao'])) {
                // Adicionar link
                $nome = $_POST['nome'];
                $url = $_POST['url'];
                $secao = $_POST['secao'];
                
                $stmt = $conn->prepare('INSERT INTO links (secao, nome, url) VALUES (?, ?, ?)');
                $stmt->bind_param("sss", $secao, $nome, $url);
                
                if ($stmt->execute()) {
                    $mensagem_sucesso = "Link adicionado com sucesso na seção: " . 
                        ($secao === 'cursos' ? 'Cursos' : 'Ações Sociais');
                } else {
                    $mensagem_erro = "Erro ao adicionar link: " . $conn->error;
                }
                $stmt->close();
            }
            
            if (isset($_POST['remover_id'])) {
                // Remover link
                $id = $_POST['remover_id'];
                $stmt = $conn->prepare('DELETE FROM links WHERE id = ?');
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $mensagem_sucesso = "Link removido com sucesso!";
                } else {
                    $mensagem_erro = "Erro ao remover link: " . $conn->error;
                }
                $stmt->close();
            }
        }

        // Buscar links
        $result = $conn->query('SELECT * FROM links ORDER BY id DESC');
        ?>

        <!-- Mensagens -->
        <?php if (isset($mensagem_sucesso)): ?>
            <div class="mensagem sucesso"><?= $mensagem_sucesso ?></div>
        <?php endif; ?>
        <?php if (isset($mensagem_erro)): ?>
            <div class="mensagem erro"><?= $mensagem_erro ?></div>
        <?php endif; ?>

        <!-- Formulário de adicionar link -->
        <section class="painel-section">
            <h3>Adicionar novo link</h3>
            <form method="POST">
                <!-- Primeiro: Seleção da Seção -->
                <div class="form-group">
                    <label for="secao">Seção:</label>
                    <select name="secao" id="secao" required>
                        <option value="">Selecione uma seção</option>
                        <option value="cursos">🎓 Cursos</option>
                        <option value="acoes_sociais">🤝 Ações Sociais</option>
                    </select>
                </div>
                
                <!-- Segundo: Nome do Projeto -->
                <div class="form-group">
                    <label for="nome">Nome do projeto:</label>
                    <input type="text" name="nome" id="nome" placeholder="Ex: Curso de Informática, Doação de Cestas Básicas" required>
                </div>
                
                <!-- Terceiro: Link da Imagem -->
                <div class="form-group">
                    <label for="url">Link da imagem:</label>
                    <input type="url" name="url" id="url" placeholder="https://exemplo.com/imagem.jpg" required>
                </div>
                
                <button type="submit" class="btn-submit">Adicionar Link</button>
            </form>
        </section>

        <!-- Lista de links -->
        <section class="painel-section">
            <h3>Links cadastrados</h3>
            <table class="admin-table">
                <tr><th>ID</th><th>Seção</th><th>Nome</th><th>URL</th><th>Ações</th></tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td>
                            <?php 
                            $icone_secao = $row['secao'] === 'cursos' ? '🎓' : '🤝';
                            $nome_secao = $row['secao'] === 'cursos' ? 'Cursos' : 'Ações Sociais';
                            echo $icone_secao . ' ' . $nome_secao;
                            ?>
                        </td>
                        <td><?= htmlspecialchars($row['nome']) ?></td>
                        <td><a href="<?= htmlspecialchars($row['url']) ?>" target="_blank">Ver Imagem</a></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="remover_id" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn-excluir">Remover</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </section>
        
        <?php $conn->close(); ?>
    </div>
</main>

<style>
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #003366;
}

.form-group select,
.form-group input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
    box-sizing: border-box;
}

.form-group select:focus,
.form-group input:focus {
    border-color: #003366;
    outline: none;
    box-shadow: 0 0 5px rgba(0, 51, 102, 0.3);
}

.btn-submit {
    background-color: #003366;
    color: white;
    padding: 15px 30px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    width: 100%;
    transition: background-color 0.3s;
}

.btn-submit:hover {
    background-color: #00264d;
}

.painel-section {
    margin-bottom: 30px;
    padding: 25px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.painel-section h3 {
    color: #003366;
    margin-bottom: 20px;
    border-bottom: 2px solid #003366;
    padding-bottom: 10px;
}
</style>

<?php include 'footer.php'; ?>

<!-- Popup para alterar status (mantido do código original) -->
<div id="popup-alterar" class="popup">
    <div class="popup-content">
        <h3>Alterar Status do Cadastro</h3>
        <form id="form-alterar">
            <input type="hidden" id="cadastro-id" name="id">
            <input type="hidden" id="cadastro-tipo" name="tipo">
            
            <p><strong>Nome:</strong> <span id="nome-alterar"></span></p>
            <p><strong>Tipo:</strong> <span id="tipo-alterar"></span></p>
            <p><strong>Status Atual:</strong> <span id="status-atual"></span></p>
            
            <label for="novo-status">Novo Status:</label>
            <select id="novo-status" name="novo_status" required>
            </select>
            
            <div class="popup-buttons">
                <button type="submit" class="btn-alterar">Salvar Status</button>
                <button type="button" onclick="closePopup()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
    // JavaScript mantido do código original
    const statusPorTipo = {
        'curso_profissional': {
            'aguardando_contato': '🟡 Aguardando contato',
            'matriculado_turma': '✅ Matriculado na próxima turma',
            'curso_concluido': '🎓 Curso Concluído', 
            'desistente': '❌ Desistente'
        },
        'futebol': {
            'aguardando_contato': '🟡 Aguardando contato',
            'matriculado': '✅ Matriculado',
            'inativo': '⚪ Inativo',
            'desistente': '❌ Desistente'
        },
        'parcerias': {
            'aguardando_contato': '🟡 Aguardando contato',
            'entrevista_agendada': '📅 Entrevista Agendada',
            'parceria_ativa': '🤝 Parceria Ativa',
            'parceria_encerrada': '🔒 Parceria Encerrada'
        },
        'voluntariado': {
            'aguardando_contato': '🟡 Aguardando contato', 
            'ativo': '🌟 Ativo',
            'inativo': '⚪ Inativo'
        },
        'projeto_mulheres': {
            'aguardando_contato': '🟡 Aguardando contato',
            'ativo': '🌟 Ativo',
            'inativo': '⚪ Inativo'
        }
    };

    let currentId = null;
    let currentTipo = null;
    let currentDados = null;

    function openPopup(id, tipo, dados, statusAtual) {
        currentId = id;
        currentTipo = tipo;
        currentDados = dados;
        
        let nome = 'N/A';
        if (tipo === 'projeto_mulheres') {
            if (dados.nome_completo) {
                nome = dados.nome_completo;
            } else if (dados.nome_mulher) {
                nome = dados.nome_mulher;
            }
        } else if (tipo === 'curso_profissional') {
            if (dados.nome_completo) {
                nome = dados.nome_completo;
            }
        } else if (tipo === 'futebol') {
            if (dados.nome_aluno && dados.nome_completo) {
                nome = dados.nome_completo + ' (' + dados.nome_aluno + ')';
            } else if (dados.nome_aluno) {
                nome = dados.nome_aluno;
            } else if (dados.nome_completo) {
                nome = dados.nome_completo;
            }
        } else if (tipo === 'parcerias') {
            if (dados.nome_empresa_doador) {
                nome = dados.nome_empresa_doador;
            } else if (dados.empresa) {
                nome = dados.empresa;
            }
        } else if (tipo === 'voluntariado') {
            if (dados.nome_completo) {
                nome = dados.nome_completo;
            } else if (dados.nome_voluntario) {
                nome = dados.nome_voluntario;
            }
        }

        if (nome === 'N/A') {
            const camposNome = ['nome_completo', 'nome', 'nome_aluno', 'nome_voluntario', 'nome_mulher', 'nome_empresa_doador', 'empresa'];
            for (const campo of camposNome) {
                if (dados[campo]) {
                    nome = dados[campo];
                    break;
                }
            }
        }

        document.getElementById('cadastro-id').value = id;
        document.getElementById('cadastro-tipo').value = tipo;
        document.getElementById('nome-alterar').textContent = nome;
        document.getElementById('tipo-alterar').textContent = tipo.replace(/_/g, ' ');
        document.getElementById('status-atual').textContent = statusPorTipo[tipo]?.[statusAtual] || statusAtual;

        const selectStatus = document.getElementById('novo-status');
        selectStatus.innerHTML = '';
        
        if (statusPorTipo[tipo]) {
            for (const [valor, texto] of Object.entries(statusPorTipo[tipo])) {
                const option = document.createElement('option');
                option.value = valor;
                option.textContent = texto;
                if (valor === statusAtual) {
                    option.selected = true;
                }
                selectStatus.appendChild(option);
            }
        }

        document.getElementById('popup-alterar').style.display = 'flex';
    }

    document.getElementById('form-alterar').addEventListener('submit', function(e) {
        e.preventDefault();
        const novoStatus = document.getElementById('novo-status').value;
        const id = document.getElementById('cadastro-id').value;
        const tipo = document.getElementById('cadastro-tipo').value;

        fetch('actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                'action': 'alterar_status',
                'id': id,
                'tipo': tipo,
                'novo_status': novoStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                closePopup();
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao alterar status. Verifique o console para mais detalhes.');
        });
    });

    function closePopup() {
        document.getElementById('popup-alterar').style.display = 'none';
    }

    function excluirCadastro(id) {
        if (confirm("Tem certeza que deseja excluir este cadastro?")) {
            fetch('actions.php?action=excluir&id=' + id)
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        window.location.reload();
                    }
                });
        }
    }
</script>
</body>
</html>