<?php
// CONFIGURAÇÃO DE SENHA PARA ACESSAR
session_start();

// Sistema de autenticação por senha
// Configurações da autenticação
$senha_correta = "jovem";
$max_tentativas = 3;
$bloqueio_tempo = 300; // 5 minutos em segundos

// Inicializar variáveis de sessão se não existirem
if (!isset($_SESSION['tentativas'])) {
    $_SESSION['tentativas'] = 0;
}
if (!isset($_SESSION['bloqueado_ate'])) {
    $_SESSION['bloqueado_ate'] = 0;
}

// Verificar se o usuário já está autenticado
$autenticado = isset($_SESSION['autenticado']) && $_SESSION['autenticado'] === true;

// Processar o envio do formulário de autenticação
if (!$autenticado && isset($_POST['senha'])) {
    // Verificar se está bloqueado
    if (time() < $_SESSION['bloqueado_ate']) {
        $tempo_restante = $_SESSION['bloqueado_ate'] - time();
        $mensagem_erro = "Acesso bloqueado. Tente novamente em " . ceil($tempo_restante/60) . " minutos.";
    } else {
        // Verificar a senha
        if ($_POST['senha'] === $senha_correta) {
            // Senha correta - autenticar usuário
            $_SESSION['autenticado'] = true;
            $_SESSION['tentativas'] = 0;
            $autenticado = true;
        } else {
            // Senha incorreta
            $_SESSION['tentativas']++;
            $tentativas_restantes = $max_tentativas - $_SESSION['tentativas'];
            
            if ($tentativas_restantes > 0) {
                $mensagem_erro = "Senha incorreta. Você tem $tentativas_restantes tentativa(s) restante(s).";
            } else {
                // Bloquear acesso após exceder tentativas
                $_SESSION['bloqueado_ate'] = time() + $bloqueio_tempo;
                $mensagem_erro = "Número máximo de tentativas excedido. Tente novamente em 5 minutos.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Instituto Integração Jovem</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Estilos para o painel administrativo */
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
           flex-direction: row;
           justify-content: center;
           gap: 0px;
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
        .admin-table .actions a, .admin-table .actions form {
            display: inline-block;
            margin-right: 5px;
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
        
        /* Estilos para padronização das tabelas */
        .admin-table th:nth-child(1),
        .admin-table td:nth-child(1) {
            width: 10%; /* Nome */
        }

        .admin-table th:nth-child(2),
        .admin-table td:nth-child(2) {
            width: 40%; /* Dados */
        }

        .admin-table th:nth-child(3),
        .admin-table td:nth-child(3) {
            width: 10%; /* Data Cadastro */
        }

        .admin-table th:nth-child(4),
        .admin-table td:nth-child(4) {
            width: 5%; /* Ações */
        }

        /* Melhorar a exibição dos dados */
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

        /* Responsividade para telas menores */
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
/* largura das colunas */
.admin-table th:nth-child(1),
.admin-table td:nth-child(1) {
    width: 10%;
    min-width: 60px;
}

.admin-table th:nth-child(2),
.admin-table td:nth-child(2) {
    width: 40%;
    min-width: 150px;
}

.admin-table th:nth-child(3),
.admin-table td:nth-child(3) {
    min-width: 100px;
    white-space: nowrap;
}

    /* Coluna de Data em 2 linhas */
    .admin-table td:nth-child(3) {
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
        }

        /* sistema de autenticação */
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
// Se não estiver autenticado, exibir formulário de login
if (!$autenticado) {
    echo '<main class="admin-main">';
    echo '<div class="auth-container">';
    echo '<h2>Acesso Restrito</h2>';
    echo '<p>Esta área é restrita a administradores. Por favor, insira a senha para continuar.</p>';
    
    if (isset($mensagem_erro)) {
        echo '<div class="auth-message auth-error">' . $mensagem_erro . '</div>';
        echo '<script>document.addEventListener("DOMContentLoaded", function() { document.querySelector(".auth-message").style.display = "block"; });</script>';
    }
    
    // Verificar se está bloqueado
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
    
    // Incluir o footer 
    include 'footer.php';
    exit();
}
?>

<!-- Conteúdo protegido (apenas exibido se autenticado) -->
<main class="admin-main">
    <h2>
        <span>Painel Administrativo</span>
        <span>Relatórios de Cadastros</span>
    </h2>

    <?php
    include 'conexao.php';

        // Array modificado com os novos tipos de cadastro
        $tipos_cadastros = [
            'projeto_mulheres' => 'Projeto Mulheres do Amor',
            'curso_profissional' => 'Curso Qualificação Profissional',
            'futebol' => 'Escolinha de Futebol',
            'parcerias' => 'Parcerias e doações',
            'voluntariado' => 'Voluntariado'
        ];

        foreach ($tipos_cadastros as $tipo_slug => $tipo_titulo) {
            echo "<div class='admin-table-container'>";
            echo "<h3>" . htmlspecialchars($tipo_titulo) . "</h3>";
            
            if ($tipo_slug === 'curso_profissional') {
                // PARA CURSO QUALIFICAÇÃO PROFISSIONAL: Agrupar por curso_interesse
                $sql = "SELECT id, dados_json, data_cadastro FROM cadastros WHERE tipo = ? ORDER BY data_cadastro DESC";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $tipo_slug);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    // Agrupar registros por curso_interesse
                    $cursos_agrupados = [];
                    while ($row = $result->fetch_assoc()) {
                        $dados = json_decode($row['dados_json'], true);
                        $curso_interesse = isset($dados['curso_interesse']) ? $dados['curso_interesse'] : 'Não especificado';
                        
                        if (!isset($cursos_agrupados[$curso_interesse])) {
                            $cursos_agrupados[$curso_interesse] = [];
                        }
                        $cursos_agrupados[$curso_interesse][] = $row;
                    }
                    
                    // Exibir cada grupo de curso
                    foreach ($cursos_agrupados as $curso_nome => $registros) {
                        echo "<h4>Curso: " . htmlspecialchars($curso_nome) . " (" . count($registros) . " inscritos)</h4>";
                        
                        echo "<table class='admin-table'>";
                        echo "<thead><tr><th>Nome</th><th>Dados</th><th>Data Cadastro</th><th class='actions'>Ações</th></tr></thead>";
                        echo "<tbody>";
                        
                        foreach ($registros as $row) {
                            $dados = json_decode($row['dados_json'], true);
                            $nome = isset($dados['nome_curso']) ? $dados['nome_curso'] : 'N/A';
                            
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($nome) . "</td>";
                            echo "<td>";
                            echo "<ul>";
                            foreach ($dados as $key => $value) {
                                if ($key !== 'curso_interesse') { // Não mostrar curso_interesse novamente
                                    echo "<li><strong>" . htmlspecialchars(str_replace('_', ' ', $key)) . ":</strong> " . htmlspecialchars($value) . "</li>";
                                }
                            }
                            echo "</ul>";
                            echo "</td>";
                            echo "<td>" . date('d/m/Y H:i', strtotime($row['data_cadastro'])) . "</td>";
                            echo "<td class='actions'>";
                            echo "<button class='btn-alterar' onclick='openPopup(" . $row['id'] . ", \"" . htmlspecialchars($tipo_slug) . "\", " . json_encode($dados) . ")'>Alterar</button>";
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
                // PARA OS DEMAIS TIPOS
                $sql = "SELECT id, dados_json, data_cadastro FROM cadastros WHERE tipo = ? ORDER BY data_cadastro DESC";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $tipo_slug);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    echo "<table class='admin-table'>";
                    echo "<thead><tr><th>Nome</th><th>Dados</th><th>Data Cadastro</th><th class='actions'>Ações</th></tr></thead>";
                    echo "<tbody>";

                    while ($row = $result->fetch_assoc()) {
                        $dados = json_decode($row['dados_json'], true);
                        $nome = 'N/A';
                        
                        // Lógica para extrair o nome baseado no tipo de cadastro
                        if ($tipo_slug === 'projeto_mulheres' && isset($dados['nome_mulher'])) {
                            $nome = $dados['nome_mulher'];
                        } elseif ($tipo_slug === 'curso_interesse' && isset($dados['nome'])) {
                            $nome = $dados['nome'];
                        } elseif ($tipo_slug === 'futebol' && isset($dados['nome_aluno'])) {
                            $nome = $dados['nome_aluno'];
                        } elseif ($tipo_slug === 'parcerias' && isset($dados['empresa'])) {
                            $nome = $dados['empresa'];
                        } elseif ($tipo_slug === 'voluntariado' && isset($dados['nome_voluntario'])) {
                            $nome = $dados['nome_voluntario'];
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
                        echo "<td class='actions'>";
                        echo "<button class='btn-alterar' onclick='openPopup(" . $row['id'] . ", \"" . htmlspecialchars($tipo_slug) . "\", " . json_encode($dados) . ")'>Alterar</button>";
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
    </main>
    
    <?php include 'footer.php'; ?>

    <div id="popup-alterar" class="popup">
        <div class="popup-content">
            <h3>Alterar Status de Cadastro</h3>
            <form id="form-alterar">
                <input type="hidden" id="cadastro-id" name="id">
                <p><strong>Nome:</strong> <span id="nome-alterar"></span></p>
                <p><strong>Tipo:</strong> <span id="tipo-alterar"></span></p>
                <label for="status">Incluir em Turma/Projeto:</label>
                <select id="status" name="status">
                    <option value="aguardando">Aguardando</option>
                    <option value="incluido">Incluído</option>
                    <option value="concluido">Concluído</option>
                    <option value="desistente">Desistente</option>
                </select>
                <button type="submit" class="btn-alterar">Salvar</button>
                <button type="button" onclick="closePopup()">Cancelar</button>
            </form>
        </div>
    </div>
    
    <script>
        // Funções JavaScript para interagir com o PHP
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

        let currentId, currentNome, currentTipo, currentDados;

        function openPopup(id, tipo, dados) {
            currentId = id;
            currentTipo = tipo;
            currentDados = dados;
            
            let nome = 'N/A';
            if (tipo === 'projeto_mulheres' && dados.nome_mulher) {
                nome = dados.nome_mulher;
            } else if ((tipo === 'curso_profissional' || tipo === 'curso_sobrancelha_maquiagem') && dados.nome_curso) {
                nome = dados.nome_curso;
            } else if (tipo === 'futebol' && dados.nome_aluno) {
                nome = dados.nome_aluno;
            } else if (tipo === 'parcerias' && dados.empresa) {
                nome = dados.empresa;
            }

            document.getElementById('cadastro-id').value = id;
            document.getElementById('nome-alterar').textContent = nome;
            document.getElementById('tipo-alterar').textContent = tipo.replace(/_/g, ' ');

            document.getElementById('popup-alterar').style.display = 'flex';
        }
        
        function closePopup() {
            document.getElementById('popup-alterar').style.display = 'none';
        }
        
        document.getElementById('form-alterar').addEventListener('submit', function(e) {
            e.preventDefault();
            const status = document.getElementById('status').value;
            const id = document.getElementById('cadastro-id').value;

            fetch('actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    'action': 'alterar',
                    'id': id,
                    'status': status
                })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    closePopup();
                    window.location.reload();
                }
            });
        });
    </script>
</body>
</html>