<?php
header('Content-Type: application/json');

// Captura de erros (não exibe para o usuário final, apenas loga)
error_reporting(0);
ini_set('display_errors', 0);

// Importação do PHPMailer (DEVE estar no topo, antes de qualquer output)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require_once 'vendor/autoload.php';

include 'conexao.php';

$response = ['success' => false, 'message' => 'Ação inválida.'];

/*
|--------------------------------------------------------------------------
| TRATAMENTO DE AÇÕES (GET = excluir, POST = alterar_status)
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Ação de Excluir
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
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Alterar Status
    if (isset($_POST['action']) && $_POST['action'] == 'alterar_status' 
        && isset($_POST['id']) && isset($_POST['novo_status']) && isset($_POST['tipo'])) {
        
        $id = intval($_POST['id']);
        $novo_status = $conn->real_escape_string($_POST['novo_status']);
        $tipo = $conn->real_escape_string($_POST['tipo']);
        
        $sql_select = "SELECT dados_json, status FROM cadastros WHERE id = ?";
        $stmt_select = $conn->prepare($sql_select);
        $stmt_select->bind_param("i", $id);

        if ($stmt_select->execute()) {
            $result = $stmt_select->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $dados = json_decode($row['dados_json'], true);
                $status_anterior = $row['status'];
                
                $sql_update = "UPDATE cadastros SET status = ? WHERE id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("si", $novo_status, $id);
                
                if ($stmt_update->execute()) {
                    $email_enviado = false;
                    try {
                        $email_enviado = enviarEmailStatus($tipo, $novo_status, $dados, $status_anterior);
                    } catch (Exception $e) {
                        error_log("Erro no envio de email: " . $e->getMessage());
                    }
                    
                    $response = [
                        'success' => true,
                        'message' => $email_enviado 
                            ? '✅ Status atualizado e email enviado!' 
                            : '✅ Status atualizado!'
                    ];
                } else {
                    $response = ['success' => false, 'message' => '❌ Erro ao atualizar status: ' . $stmt_update->error];
                }
                $stmt_update->close();
            } else {
                $response = ['success' => false, 'message' => '❌ Cadastro não encontrado.'];
            }
        } else {
            $response = ['success' => false, 'message' => '❌ Erro ao buscar cadastro.'];
        }
        $stmt_select->close();
    }
}

/*
|--------------------------------------------------------------------------
| FUNÇÕES DE SUPORTE
|--------------------------------------------------------------------------
*/
function enviarEmailStatus($tipo, $novo_status, $dados, $status_anterior) {
    $status_que_disparam_email = [
        'ativo', 'matriculado_turma', 'matriculado', 
        'curso_concluido', 'entrevista_agendada', 'parceria_ativa'
    ];
    
    if (!in_array($novo_status, $status_que_disparam_email)) return false;
    
    $email_usuario = $dados['email'] ?? $dados['email_parceria'] ?? '';
    if (empty($email_usuario)) return false;
    
    $nome_usuario = obterNomeUsuario($tipo, $dados);
    $assunto = '';
    $mensagem = '';
    
    switch($tipo) {
        case 'voluntariado':
        case 'projeto_mulheres':
            if ($novo_status === 'ativo') {
                $assunto = "🎉 Cadastro Aprovado - Instituto Integração Jovem";
                $mensagem = gerarMensagemAprovacao($nome_usuario, $tipo, $dados);
            }
            break;
        case 'curso_profissional':
            if ($novo_status === 'matriculado_turma') {
                $curso = $dados['curso_desejado'] ?? 'Curso Profissional';
                $assunto = "✅ Matrícula Confirmada - Instituto Integração Jovem";
                $mensagem = gerarMensagemMatriculaCurso($nome_usuario, $curso, $dados);
            } elseif ($novo_status === 'curso_concluido') {
                $curso = $dados['curso_desejado'] ?? 'Curso Profissional';
                $assunto = "🎓 Parabéns! Curso Concluído - Instituto Integração Jovem";
                $mensagem = gerarMensagemConclusaoCurso($nome_usuario, $curso, $dados);
            }
            break;
        case 'futebol':
            if ($novo_status === 'matriculado') {
                $assunto = "⚽ Matrícula Confirmada - Escolinha de Futebol";
                $mensagem = gerarMensagemMatriculaFutebol($nome_usuario, $dados);
            }
            break;
        case 'parcerias':
            if ($novo_status === 'parceria_ativa') {
                $assunto = "🤝 Parceria Ativada - Instituto Integração Jovem";
                $mensagem = gerarMensagemParceriaAtiva($nome_usuario, $dados);
            } elseif ($novo_status === 'entrevista_agendada') {
                $assunto = "📅 Entrevista Agendada - Instituto Integração Jovem";
                $mensagem = gerarMensagemEntrevistaAgendada($nome_usuario, $dados);
            }
            break;
    }
    
    if (empty($assunto) || empty($mensagem)) return false;
    
    return enviarEmailSMTP($email_usuario, $assunto, $mensagem);
}

function obterNomeUsuario($tipo, $dados) {
    switch($tipo) {
        case 'projeto_mulheres':
            return $dados['nome_completo'] ?? $dados['nome_mulher'] ?? 'Prezado(a)';
        case 'curso_profissional':
            return $dados['nome_completo'] ?? 'Prezado(a) Aluno(a)';
        case 'futebol':
            return $dados['nome_aluno'] ?? $dados['nome_completo'] ?? 'Prezado(a) Atleta';
        case 'parcerias':
            return $dados['nome_empresa_doador'] ?? $dados['empresa'] ?? 'Prezado(a) Parceiro(a)';
        case 'voluntariado':
            return $dados['nome_completo'] ?? $dados['nome_voluntario'] ?? 'Prezado(a) Voluntário(a)';
        default:
            return 'Prezado(a)';
    }
}

/*
|--------------------------------------------------------------------------
| FUNÇÕES DE TEMPLATE DE EMAIL (COMPLETAS)
|--------------------------------------------------------------------------
*/
function gerarMensagemAprovacao($nome, $tipo, $dados) {
    $tipo_texto = ($tipo === 'voluntariado') ? 'voluntário' : 'participante';
    $dados_html = gerarListagemDados($dados);
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
            .header { background: #003366; color: white; padding: 30px 20px; text-align: center; }
            .content { padding: 30px 20px; background: #f9f9f9; }
            .footer { background: #dddddd; padding: 20px; text-align: center; font-size: 12px; color: #666; }
            .dados-table { width: 100%; border-collapse: collapse; margin: 20px 0; background: white; }
            .dados-table td { padding: 10px; border-bottom: 1px solid #eee; }
            .status-info { background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Instituto Integração Jovem</h1>
            </div>
            <div class='content'>
                <h2>🎉 Cadastro Aprovado!</h2>
                <p>Olá <strong>$nome</strong>,</p>
                
                <div class='status-info'>
                    <p>É com grande alegria que informamos que seu cadastro como <strong>$tipo_texto</strong> foi <strong>APROVADO</strong>!</p>
                </div>
                
                <h3>📋 Informações Registradas:</h3>
                <table class='dados-table'>
                    $dados_html
                </table>
                
                <p><strong>Próximos passos:</strong><br>
                Nossa equipe entrará em contato para dar as boas-vindas oficialmente e alinhar os próximos passos.</p>
                
                <p><strong>Bem-vindo(a) à nossa família!</strong></p>
            </div>
            <div class='footer'>
                <p><strong>Instituto Integração Jovem</strong><br>
                Email: projetointegradorunivesp@alwaysdata.net<br>
                Telefone: (11) 99589-0901</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

function gerarMensagemMatriculaCurso($nome, $curso, $dados) {
    $curso_formatado = ucwords(str_replace('_', ' ', $curso));
    $dados_html = gerarListagemDados($dados);
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
            .header { background: #003366; color: white; padding: 30px 20px; text-align: center; }
            .content { padding: 30px 20px; background: #f9f9f9; }
            .footer { background: #dddddd; padding: 20px; text-align: center; font-size: 12px; color: #666; }
            .dados-table { width: 100%; border-collapse: collapse; margin: 20px 0; background: white; }
            .dados-table td { padding: 10px; border-bottom: 1px solid #eee; }
            .info-box { background: white; border: 2px solid #ffcc00; padding: 15px; margin: 20px 0; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Instituto Integração Jovem</h1>
            </div>
            <div class='content'>
                <h2>✅ Matrícula Confirmada!</h2>
                <p>Olá <strong>$nome</strong>,</p>
                <p>Sua matrícula no curso <strong>$curso_formatado</strong> foi confirmada com sucesso!</p>
                
                <div class='info-box'>
                    <h3>📋 Informações Importantes:</h3>
                    <p><strong>Curso:</strong> $curso_formatado</p>
                    <p><strong>Status:</strong> Matriculado na próxima turma</p>
                    <p><strong>Próximo passo:</strong> Aguarde nosso contato com informações sobre data de início, horários e local.</p>
                </div>
                
                <h3>📝 Dados do Cadastro:</h3>
                <table class='dados-table'>
                    $dados_html
                </table>
                
                <p><strong>Lembrete:</strong> Todos os cursos são totalmente gratuitos!</p>
            </div>
            <div class='footer'>
                <p><strong>Instituto Integração Jovem</strong><br>
                Email: projetointegradorunivesp@alwaysdata.net<br>
                Telefone: (11) 99589-0901</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

function gerarMensagemConclusaoCurso($nome, $curso, $dados) {
    $curso_formatado = ucwords(str_replace('_', ' ', $curso));
    $dados_html = gerarListagemDados($dados);
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
            .header { background: #003366; color: white; padding: 30px 20px; text-align: center; }
            .content { padding: 30px 20px; background: #f9f9f9; }
            .footer { background: #dddddd; padding: 20px; text-align: center; font-size: 12px; color: #666; }
            .dados-table { width: 100%; border-collapse: collapse; margin: 20px 0; background: white; }
            .dados-table td { padding: 10px; border-bottom: 1px solid #eee; }
            .congrats { text-align: center; font-size: 18px; color: #155724; background: #d4edda; padding: 15px; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Instituto Integração Jovem</h1>
            </div>
            <div class='content'>
                <div class='congrats'>
                    <h2>🎓 PARABÉNS!</h2>
                    <p>Curso Concluído com Sucesso</p>
                </div>
                
                <p>Olá <strong>$nome</strong>,</p>
                <p>É com enorme satisfação que comunicamos a <strong>conclusão do seu curso de $curso_formatado</strong>!</p>
                
                <h3>📝 Dados do Cadastro:</h3>
                <table class='dados-table'>
                    $dados_html
                </table>
                
                <p>📊 <strong>Seu desempenho foi acompanhado</strong> e estamos muito felizes com sua dedicação e empenho durante todo o processo de aprendizado.</p>
                
                <p>🎯 <strong>Próximos passos:</strong><br>
                - Em breve você receberá seu certificado de conclusão<br>
                - Nossa equipe entrará em contato sobre oportunidades<br>
                - Continue se desenvolvendo!</p>
                
                <p>Esta conquista é apenas o começo de uma jornada de sucesso!</p>
                
                <p style='text-align: center; margin-top: 30px;'>
                    <strong>Parabéns mais uma vez!</strong>
                </p>
            </div>
            <div class='footer'>
                <p><strong>Instituto Integração Jovem</strong><br>
                Email: projetointegradorunivesp@alwaysdata.net<br>
                Telefone: (11) 99589-0901</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

function gerarMensagemMatriculaFutebol($nome, $dados) {
    $dados_html = gerarListagemDados($dados);
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
            .header { background: #003366; color: white; padding: 30px 20px; text-align: center; }
            .content { padding: 30px 20px; background: #f9f9f9; }
            .footer { background: #dddddd; padding: 20px; text-align: center; font-size: 12px; color: #666; }
            .dados-table { width: 100%; border-collapse: collapse; margin: 20px 0; background: white; }
            .dados-table td { padding: 10px; border-bottom: 1px solid #eee; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Instituto Integração Jovem</h1>
                <p>Escolinha de Futebol</p>
            </div>
            <div class='content'>
                <h2>⚽ Matrícula Confirmada!</h2>
                <p>Olá <strong>$nome</strong>,</p>
                <p>Sua matrícula na <strong>Escolinha de Futebol</strong> foi confirmada com sucesso!</p>
                
                <h3>📝 Dados do Cadastro:</h3>
                <table class='dados-table'>
                    $dados_html
                </table>
                
                <p>🎯 <strong>Informações importantes:</strong><br>
                - Aguarde nosso contato com dias e horários dos treinos<br>
                - Documentação necessária será solicitada<br>
                - Uniforme e materiais serão informados</p>
                
                <p>Estamos muito animados para tê-lo(a) em nossa equipe!</p>
                
                <p><strong>Lembrete:</strong> O futebol vai muito além do campo - aqui formamos cidadãos!</p>
            </div>
            <div class='footer'>
                <p><strong>Instituto Integração Jovem - Escolinha de Futebol</strong><br>
                Email: projetointegradorunivesp@alwaysdata.net<br>
                Telefone: (11) 99589-0901</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

function gerarMensagemParceriaAtiva($nome, $dados) {
    $dados_html = gerarListagemDados($dados);
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
            .header { background: #003366; color: white; padding: 30px 20px; text-align: center; }
            .content { padding: 30px 20px; background: #f9f9f9; }
            .footer { background: #dddddd; padding: 20px; text-align: center; font-size: 12px; color: #666; }
            .dados-table { width: 100%; border-collapse: collapse; margin: 20px 0; background: white; }
            .dados-table td { padding: 10px; border-bottom: 1px solid #eee; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Instituto Integração Jovem</h1>
            </div>
            <div class='content'>
                <h2>🤝 Parceria Ativada!</h2>
                <p>Olá <strong>$nome</strong>,</p>
                <p>É com grande satisfação que informamos que nossa <strong>parceria foi oficialmente ativada</strong>!</p>
                
                <h3>📝 Dados da Parceria:</h3>
                <table class='dados-table'>
                    $dados_html
                </table>
                
                <p>🌟 <strong>Juntos podemos transformar realidades</strong> e impactar positivamente nossa comunidade.</p>
                
                <p>Nossa equipe de relacionamento estará em contato em breve para alinhar os detalhes e próximos passos desta colaboração.</p>
                
                <p>Agradecemos profundamente por acreditar em nossa causa e fazer parte desta transformação!</p>
            </div>
            <div class='footer'>
                <p><strong>Instituto Integração Jovem</strong><br>
                Email: projetointegradorunivesp@alwaysdata.net<br>
                Telefone: (11) 99589-0901</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

function gerarMensagemEntrevistaAgendada($nome, $dados) {
    $dados_html = gerarListagemDados($dados);
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
            .header { background: #003366; color: white; padding: 30px 20px; text-align: center; }
            .content { padding: 30px 20px; background: #f9f9f9; }
            .footer { background: #dddddd; padding: 20px; text-align: center; font-size: 12px; color: #666; }
            .dados-table { width: 100%; border-collapse: collapse; margin: 20px 0; background: white; }
            .dados-table td { padding: 10px; border-bottom: 1px solid #eee; }
            .info-box { background: white; border: 2px solid #003366; padding: 20px; margin: 20px 0; border-radius: 8px; }
            .highlight { background: #fff3cd; padding: 15px; border-left: 4px solid #ffcc00; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Instituto Integração Jovem</h1>
                <p>Transformando vidas através da educação e esporte</p>
            </div>
            <div class='content'>
                <h2>📅 Entrevista Agendada</h2>
                <p>Olá <strong>$nome</strong>,</p>
                <p>É com grande satisfação que confirmamos o <strong>agendamento da nossa entrevista</strong>!</p>
                
                <h3>📝 Dados do Cadastro:</h3>
                <table class='dados-table'>
                    $dados_html
                </table>
                
                <div class='info-box'>
                    <h3>📍 Local da Entrevista:</h3>
                    <p><strong>Instituto Integração Jovem</strong><br>
                    Rua Erva São Cristóvão, 126<br>
                    Vista Linda - São Paulo/SP</p>
                </div>
                
                <div class='highlight'>
                    <h3>📋 O que trazer para a entrevista:</h3>
                    <ul>
                        <li>Documento de identificação com foto</li>
                        <li>Comprovante de endereço</li>
                        <li>Documentos da empresa (se aplicável)</li>
                        <li>Proposta ou ideia de parceria (opcional)</li>
                    </ul>
                </div>
                
                <p>🎯 <strong>Objetivo da entrevista:</strong><br>
                Conhecer melhor sua proposta de parceria, alinhar expectativas e explorar as melhores formas de colaboração para impactar nossa comunidade.</p>
                
                <p>⏰ <strong>Duração estimada:</strong> 1 hora</p>
                
                <p>Nossa equipe entrará em contato em breve para confirmar a data e horário específicos.</p>
                
                <p style='text-align: center; margin-top: 25px;'>
                    <strong>Estamos ansiosos para conhecê-lo(a) pessoalmente!</strong>
                </p>
            </div>
            <div class='footer'>
                <p><strong>Instituto Integração Jovem</strong><br>
                📍 Rua Erva São Cristóvão, 126 - Vista Linda/SP<br>
                📧 Email: projetointegradorunivesp@alwaysdata.net<br>
                📞 Telefone: (11) 99589-0901</p>
                
                <p style='font-size: 11px; color: #888; margin-top: 15px;'>
                    💡 <strong>Dica:</strong> Use o WhatsApp para confirmar sua presença ou caso tenha alguma dúvida!
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
}

// FUNÇÃO PARA GERAR LISTAGEM DE DADOS (COMUM A TODOS OS EMAILS)
function gerarListagemDados($dados) {
    $html = '';
    foreach ($dados as $key => $value) {
        if (!empty($value) && $key !== 'password' && $key !== 'senha') {
            $label = ucwords(str_replace('_', ' ', $key));
            $html .= "<tr><td><strong>$label:</strong></td><td>$value</td></tr>";
        }
    }
    return $html;
}

/*
|--------------------------------------------------------------------------
| FUNÇÃO DE ENVIO SMTP
|--------------------------------------------------------------------------
*/
function enviarEmailSMTP($email, $assunto, $mensagem) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp-projetointegradorunivesp.alwaysdata.net';
        $mail->SMTPAuth = true;
        $mail->Username = 'projetointegradorunivesp@alwaysdata.net';
        $mail->Password = 'Univesp@25'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        $mail->setFrom('projetointegradorunivesp@alwaysdata.net', 'Instituto Integração Jovem');
        $mail->addAddress($email);
        $mail->addBCC('projetointegradorunivesp@alwaysdata.net', 'Instituto Integração Jovem');

        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body = $mensagem;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro ao enviar email de status: " . $mail->ErrorInfo);
        return false;
    }
}

$conn->close();
echo json_encode($response);
?>