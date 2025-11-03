<?php

header('Content-Type: application/json');

// Inclui o autoload do Composer
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Dados do banco de dados
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

// Verifica se os dados foram enviados via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Define o fuso horário para América/Sao_Paulo (Horário de Brasília)
    date_default_timezone_set('America/Sao_Paulo');

    // Pega o tipo de cadastro
    $tipo = $_POST['tipo'];
    
    // Extrai e valida o email do formulário
    $email_usuario = '';
    $email_valido = false;

    // Encontra e valida o campo de email
    if (isset($_POST['email']) && !empty(trim($_POST['email']))) {
        $email_temp = trim($_POST['email']);
        if (filter_var($email_temp, FILTER_VALIDATE_EMAIL)) {
            $email_usuario = $email_temp;
            $email_valido = true;
        }
    } elseif (isset($_POST['email_parceria']) && !empty(trim($_POST['email_parceria']))) {
        $email_temp = trim($_POST['email_parceria']);
        if (filter_var($email_temp, FILTER_VALIDATE_EMAIL)) {
            $email_usuario = $email_temp;
            $email_valido = true;
        }
    }

    // Se email é obrigatório mas está inválido/vazio
    if (!$email_valido) {
        echo json_encode(['success' => false, 'message' => 'Por favor, insira um email válido.']);
        exit;
    }
    
    // Remove o tipo dos dados para salvar apenas os campos dinâmicos
    unset($_POST['tipo']);

    // Converte os dados do formulário para o formato JSON
    $dados_json = json_encode($_POST);

    // Gera a data e hora atuais, formatadas para o banco de dados
    $data_atual = date("Y-m-d H:i:s");

    // Prepara a consulta SQL
    $status_padrao = 'aguardando_contato';
    $stmt = $conn->prepare("INSERT INTO cadastros (tipo, data_cadastro, dados_json, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $tipo, $data_atual, $dados_json, $status_padrao);

    if ($stmt->execute()) {
        // Envio bem-sucedido - AGORA ENVIA O EMAIL
        $email_enviado = false;
        
        if (!empty($email_usuario)) {
            $email_enviado = enviarEmailSMTP($email_usuario, $tipo, $_POST);
        }
        
        if ($email_enviado) {
            echo json_encode(['success' => true, 'message' => 'Dados salvos com sucesso! Email de confirmação enviado.']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Dados salvos com sucesso! (Email não pôde ser enviado)']);
        }
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

// FUNÇÃO PARA ENVIAR EMAIL VIA SMTP
function enviarEmailSMTP($email, $tipo, $dados) {
    $mail = new PHPMailer(true);
    
    try {
        // Configurações do servidor SMTP da Alwaysdata
        $mail->isSMTP();
        $mail->Host = 'smtp-projetointegradorunivesp.alwaysdata.net';
        $mail->SMTPAuth = true;
        $mail->Username = 'projetointegradorunivesp@alwaysdata.net';
        $mail->Password = 'Univesp@25'; // 🔑 SUBSTITUA PELA SENHA REAL
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        
        // Configurações adicionais para melhor compatibilidade
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
       // Remetente e destinatário
        $mail->setFrom('projetointegradorunivesp@alwaysdata.net', 'Instituto Integração Jovem');
        $mail->addAddress($email); // Para o usuário que se cadastrou
        $mail->addBCC('projetointegradorunivesp@alwaysdata.net', 'Instituto Integração Jovem'); // Cópia oculta para o instituto
        $mail->addReplyTo('projetointegradorunivesp@alwaysdata.net', 'Instituto Integração Jovem');
        
        // Conteúdo do email
        $mail->isHTML(true);
        $mail->Subject = 'Confirmação de Cadastro - Instituto Integração Jovem';
        
        // Obtém o nome do usuário e a mensagem personalizada
        $nome_usuario = obterNomeUsuario($tipo, $dados);
        $mensagem = gerarMensagemEmail($tipo, $nome_usuario, $dados);
        
        $mail->Body = $mensagem;
        
        // Texto alternativo para clientes de email que não suportam HTML
        $mail->AltBody = gerarMensagemTexto($tipo, $nome_usuario, $dados);
        
        // Envia o email
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        // Log do erro (em produção, você pode salvar em um arquivo de log)
        error_log("Erro ao enviar email para $email: " . $mail->ErrorInfo);
        return false;
    }
}

// FUNÇÃO PARA OBTER O NOME DO USUÁRIO
function obterNomeUsuario($tipo, $dados) {
    switch($tipo) {
        case 'projeto_mulheres':
            return isset($dados['nome_completo']) ? $dados['nome_completo'] : 'Prezado(a)';
        case 'curso_profissional':
            return isset($dados['nome_completo']) ? $dados['nome_completo'] : 'Prezado(a) Aluno(a)';
        case 'futebol':
            return isset($dados['nome_aluno']) ? $dados['nome_aluno'] : 'Prezado(a) Atleta';
        case 'parcerias':
            return isset($dados['nome_empresa_doador']) ? $dados['nome_empresa_doador'] : 'Prezado(a) Parceiro(a)';
        case 'voluntariado':
            return isset($dados['nome_completo']) ? $dados['nome_completo'] : 'Prezado(a) Voluntário(a)';
        default:
            return 'Prezado(a)';
    }
}

// FUNÇÃO PARA GERAR A MENSAGEM DO EMAIL EM HTML
function gerarMensagemEmail($tipo, $nome, $dados = []) {
    $titulos = [
        'projeto_mulheres' => 'Projeto Mulheres do Amor',
        'curso_profissional' => 'Cursos de Capacitação Profissional',
        'futebol' => 'Escolinha de Futebol',
        'parcerias' => 'Parcerias e Doações',
        'voluntariado' => 'Voluntariado'
    ];
    
    $titulo = $titulos[$tipo] ?? 'Cadastro Geral';
    
    $mensagens = [
        'projeto_mulheres' => "
            <p>Olá <strong>$nome</strong>,</p>
            <p>Seu cadastro no <strong>Projeto Mulheres do Amor</strong> foi realizado com sucesso!</p>
            <p>Agradecemos seu interesse em fazer parte desta iniciativa que acolhe e fortalece mulheres em tratamento oncológico.</p>
            <p>Em breve nossa equipe entrará em contato para mais informações sobre as oficinas terapêuticas e como você pode participar.</p>
        ",
        
        'curso_profissional' => "
            <p>Olá <strong>$nome</strong>,</p>
            <p>Sua pré-inscrição em nosso <strong>Curso de Capacitação Profissional</strong> foi recebida com sucesso!</p>
            <p>Estamos analisando sua solicitação e em breve nossa equipe entrará em contato para confirmar sua vaga e fornecer todas as informações necessárias sobre horários, local e documentação.</p>
            <p><strong>Lembrete:</strong> Todos os cursos são totalmente gratuitos!</p>
        ",
        
        'futebol' => "
            <p>Olá <strong>$nome</strong>,</p>
            <p>Sua inscrição na <strong>Escolinha de Futebol</strong> foi realizada com sucesso!</p>
            <p>Em breve entraremos em contato para informar sobre os dias e horários dos treinos, documentação necessária e demais informações.</p>
            <p>Estamos ansiosos para tê-lo(a) conosco!</p>
        ",
        
        'parcerias' => "
            <p>Olá <strong>$nome</strong>,</p>
            <p>Seu interesse em estabelecer parceria conosco foi registrado com sucesso!</p>
            <p>Nossa equipe de relacionamento entrará em contato em breve para discutir as possibilidades de colaboração e como podemos trabalhar juntos para transformar vidas.</p>
            <p>Agradecemos seu interesse em apoiar nossa causa.</p>
        ",
        
        'voluntariado' => "
            <p>Olá <strong>$nome</strong>,</p>
            <p>Seu cadastro como <strong>voluntário(a)</strong> foi realizado com sucesso!</p>
            <p>Ficamos muito felizes com seu interesse em fazer a diferença. Em breve nossa equipe entrará em contato para alinhar expectativas, disponibilidade e áreas de atuação.</p>
            <p>Juntos podemos transformar realidades!</p>
        "
    ];
    
    $mensagem_conteudo = $mensagens[$tipo] ?? "
        <p>Olá <strong>$nome</strong>,</p>
        <p>Seu cadastro no Instituto Integração Jovem foi realizado com sucesso!</p>
        <p>Em breve nossa equipe entrará em contato com mais informações.</p>
    ";
    
    // Adiciona as informações registradas
    $informacoes_registradas = gerarInformacoesRegistradas($tipo, $dados);
    
    return "
        <!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Confirmação de Cadastro</title>
            <style>
                body { 
                    font-family: 'Arial', sans-serif; 
                    line-height: 1.6; 
                    color: #333; 
                    margin: 0; 
                    padding: 0; 
                    background-color: #f4f4f4;
                }
                .container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    background-color: #ffffff;
                }
                .header { 
                    background: #003366; 
                    color: white; 
                    padding: 30px 20px; 
                    text-align: center;
                }
                .header h1 { 
                    margin: 0; 
                    font-size: 24px;
                }
                .content { 
                    padding: 30px 20px; 
                    background: #f9f9f9;
                }
                .footer { 
                    background: #dddddd; 
                    padding: 20px; 
                    text-align: center; 
                    font-size: 12px; 
                    color: #666;
                }
                .info-section {
                    background: #fff;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    padding: 15px;
                    margin: 20px 0;
                }
                .info-section h3 {
                    color: #003366;
                    margin-top: 0;
                    border-bottom: 2px solid #ffcc00;
                    padding-bottom: 5px;
                }
                .info-item {
                    margin: 8px 0;
                    padding: 5px 0;
                    border-bottom: 1px dotted #eee;
                }
                .info-label {
                    font-weight: bold;
                    color: #003366;
                }
                .btn {
                    display: inline-block;
                    background: #ffcc00;
                    color: #003366;
                    padding: 12px 25px;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: bold;
                    margin: 15px 0;
                }
                @media only screen and (max-width: 600px) {
                    .container {
                        width: 100% !important;
                    }
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Instituto Integração Jovem</h1>
                    <p>Transformando vidas através da educação e esporte</p>
                </div>
                <div class='content'>
                    <h2>Confirmação de Cadastro - $titulo</h2>
                    $mensagem_conteudo
                    
                    <div class='info-section'>
                        <h3>📋 Informações Registradas:</h3>
                        $informacoes_registradas
                    </div>
                    
                    <p style='margin-top: 25px; padding-top: 15px; border-top: 1px solid #ddd;'>
                        <strong>Atenciosamente,</strong><br>
                        Equipe Instituto Integração Jovem
                    </p>
                </div>
                <div class='footer'>
                    <p><strong>Instituto Integração Jovem</strong><br>
                    Email: projetointegradorunivesp@alwaysdata.net<br>
                    Telefone: (11) 99589-0901<br>
                    Este é um email automático, por favor não responda.</p>
                </div>
            </div>
        </body>
        </html>
    ";
}

// FUNÇÃO PARA GERAR AS INFORMAÇÕES REGISTRADAS
function gerarInformacoesRegistradas($tipo, $dados) {
    $html = '';
    
    // Campos que NÃO devem ser mostrados (sensíveis ou irrelevantes)
    $campos_ocultos = ['tipo', 'senha', 'confirmar_senha', 'password'];
    
    foreach ($dados as $campo => $valor) {
        // Pula campos vazios, nulos ou sensíveis
        if (empty($valor) || in_array($campo, $campos_ocultos)) {
            continue;
        }
        
        // Formata o nome do campo (remove underscores e capitaliza)
        $nome_campo = ucwords(str_replace('_', ' ', $campo));
        
        // Formata valores específicos
        $valor_formatado = formatarValor($campo, $valor);
        
        $html .= "<div class='info-item'>";
        $html .= "<span class='info-label'>$nome_campo:</span> $valor_formatado";
        $html .= "</div>";
    }
    
    return $html ?: "<p>Nenhuma informação adicional registrada.</p>";
}

// FUNÇÃO PARA FORMATAR VALORES ESPECÍFICOS
function formatarValor($campo, $valor) {
    // Formata datas
    if (strpos($campo, 'data') !== false || strpos($campo, 'nascimento') !== false) {
        $data = DateTime::createFromFormat('Y-m-d', $valor);
        if ($data) {
            return $data->format('d/m/Y');
        }
    }
    
    // Formata telefones (assume formato (11) 99999-9999)
    if (strpos($campo, 'telefone') !== false || strpos($campo, 'celular') !== false) {
        $telefone = preg_replace('/\D/', '', $valor);
        if (strlen($telefone) === 11) {
            return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7);
        }
    }
    
    // Formata CPF
    if (strpos($campo, 'cpf') !== false) {
        $cpf = preg_replace('/\D/', '', $valor);
        if (strlen($cpf) === 11) {
            return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
        }
    }
    
    // Formata valores booleanos (sim/não)
    if ($valor === 'sim' || $valor === 'nao') {
        return $valor === 'sim' ? 'Sim' : 'Não';
    }
    
    return htmlspecialchars($valor);
}

// FUNÇÃO PARA GERAR MENSAGEM EM TEXTO SIMPLES (alternativa para clientes de email)
function gerarMensagemTexto($tipo, $nome, $dados = []) {
    $mensagens = [
        'projeto_mulheres' => "Olá $nome, seu cadastro no Projeto Mulheres do Amor foi realizado com sucesso! Agradecemos seu interesse. Em breve nossa equipe entrará em contato.",
        'curso_profissional' => "Olá $nome, sua pré-inscrição no Curso de Capacitação Profissional foi recebida! Em breve entraremos em contato para confirmar sua vaga.",
        'futebol' => "Olá $nome, sua inscrição na Escolinha de Futebol foi realizada com sucesso! Em breve informaremos sobre treinos.",
        'parcerias' => "Olá $nome, seu interesse em parceria foi registrado! Nossa equipe entrará em contato em breve.",
        'voluntariado' => "Olá $nome, seu cadastro como voluntário foi realizado! Em breve entraremos em contato."
    ];
    
    $mensagem_base = $mensagens[$tipo] ?? "Olá $nome, seu cadastro foi realizado com sucesso! Em breve entraremos em contato.";
    
    // Adiciona informações básicas no texto simples
    $info_adicional = "";
    if (isset($dados['email'])) {
        $info_adicional .= "\nEmail: " . $dados['email'];
    }
    if (isset($dados['telefone_contato'])) {
        $info_adicional .= "\nTelefone: " . $dados['telefone_contato'];
    }
    if (isset($dados['nome_completo'])) {
        $info_adicional .= "\nNome: " . $dados['nome_completo'];
    }
    
    return $mensagem_base . $info_adicional;
}

?>
