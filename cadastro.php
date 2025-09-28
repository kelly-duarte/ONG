<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro — Instituto Integração Jovem</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-wrapper { display: flex; gap: 20px; flex-wrap: wrap; }
        .form-container { flex: 2; min-width: 300px; }
        .form-description { flex: 1; min-width: 250px; background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #ddd; }
        .form-description h3 { margin-top: 0; }
        label { display: block; margin: 8px 0 4px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; margin-bottom: 12px; }
        .radio-group label { display: inline; margin-right: 15px; font-weight: normal; }
        .radio-group input[type="radio"], .checkbox-group input[type="checkbox"] { width: auto; display: inline-block; margin-right: 5px; }
        .radio-group, .checkbox-group { margin-bottom: 12px; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <main>
        <h2>Cadastro</h2>
        <div class="form-wrapper">
            <div class="form-container">
                <form id="cadastroForm">
                    <label for="tipo">Escolha a categoria:</label>
                    <select id="tipo" name="tipo" required>
                        <option value="">-- Selecione --</option>
                        <option value="projeto_mulheres">Projeto Mulheres do Amor</option>
                        <option value="curso_profissional">Cursos de Capacitação Profissional</option>
                        <option value="futebol">Escolinha de Futebol</option>
                        <option value="parcerias">Parcerias e Doações</option>
                        <option value="voluntariado">Voluntariado</option>
                    </select>

                    <div id="dynamic-fields"></div>

                    <button type="submit" class="btn">Enviar Cadastro</button>
                </form>
            </div>
            <div class="form-description" id="form-description">
                <h3>Descrição do formulário</h3>
                <p>Escolha uma categoria para ver os detalhes do cadastro aqui.</p>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

<script>
    const tipo = document.getElementById("tipo");
    const dynFields = document.getElementById("dynamic-fields");
    const descBox = document.getElementById("form-description");
    const cadastroForm = document.getElementById("cadastroForm");

    const titulos = {
        projeto_mulheres: "Projeto Mulheres do Amor",
        curso_profissional: "Cursos de Capacitação Profissional",
        futebol: "Escolinha de Futebol",
        parcerias: "Parcerias e Doações",
        voluntariado: "Voluntariado"
    };

    // gerar campos padrões
    function gerarCamposPadroes() {
        return `
            <label>Nome Completo:</label><input type="text" name="nome_completo" required>
            <label>Data de Nascimento:</label><input type="date" name="data_nascimento" required>
            <label>Endereço Completo:</label><input type="text" name="endereco_completo" required>
            <label>CPF:</label><input type="text" name="cpf" required>
            <label>RG:</label><input type="text" name="rg" required>
            <label>Telefone de contato:</label><input type="tel" name="telefone_contato" placeholder="(xx) xxxxx-xxxx" required>
            <label>E-mail:</label><input type="email" name="email" required>
        `;
    }

    // gerar campos escolaridade
    function gerarCamposEscolaridade() {
        return `
            <label>Escolaridade:</label>
            <div class="radio-group">
                <input type="radio" name="escolaridade" value="fundamental_incompleto"> <label>Ensino Fundamental (incompleto)</label><br>
                <input type="radio" name="escolaridade" value="fundamental_completo"> <label>Ensino Fundamental (completo)</label><br>
                <input type="radio" name="escolaridade" value="medio_incompleto"> <label>Ensino Médio (incompleto)</label><br>
                <input type="radio" name="escolaridade" value="medio_completo"> <label>Ensino Médio (completo)</label><br>
                <input type="radio" name="escolaridade" value="superior_cursando"> <label>Ensino Superior (cursando)</label><br>
                <input type="radio" name="escolaridade" value="superior_incompleto"> <label>Ensino Superior (incompleto)</label><br>
                <input type="radio" name="escolaridade" value="superior_completo"> <label>Ensino Superior (completo)</label>
            </div>
        `;
    }

    tipo.addEventListener("change", () => {
        dynFields.innerHTML = "";
        let info = "";
        
        const tipoSelecionado = tipo.value;
        const camposPadroes = gerarCamposPadroes();

        if (tipoSelecionado === "projeto_mulheres") {
            info = `O Instituto Integração Jovem é uma organização sem fins lucrativos...`;
            dynFields.innerHTML = camposPadroes + `
                <h4>Informações adicionais para o Projeto Mulheres do Amor</h4>
                <p><strong>Você já teve ou foi diagnosticada(o) com câncer?</strong></p>
                <div class="radio-group">
                    <input type="radio" name="tem_cancer" value="sim"> <label>Sim</label>
                    <input type="radio" name="tem_cancer" value="nao"> <label>Não</label>
                    <input type="text" name="tipo_cancer_proprio" placeholder="Qual tipo?">
                </div>
                <p><strong>Você conhece alguém com câncer?</strong></p>
                <div class="radio-group">
                    <input type="radio" name="conhece_cancer" value="familiar"> <label>Familiar</label>
                    <input type="radio" name="conhece_cancer" value="amigo"> <label>Amiga(o)</label>
                    <input type="radio" name="conhece_cancer" value="nao"> <label>Não</label>
                    <input type="text" name="tipo_cancer_conhecido" placeholder="Qual tipo?">
                </div>
                <p><strong>Se estiver em tratamento, informe a modalidade:</strong></p>
                <div class="radio-group">
                    <input type="radio" name="modalidade_tratamento" value="sus"> <label>SUS</label>
                    <input type="radio" name="modalidade_tratamento" value="particular"> <label>Particular</label>
                    <input type="radio" name="modalidade_tratamento" value="nao_tratamento"> <label>Não estou em tratamento</label>
                </div>
                <label>O que motivou sua participação na Oficina Mulheres do Amor?</label>
                <textarea name="motivo_participacao" rows="3"></textarea>
                <label>Como ficou sabendo da oficina?</label>
                <div class="radio-group">
                    <input type="radio" name="como_soube" value="redes_sociais"> <label>Redes sociais</label>
                    <input type="radio" name="como_soube" value="amigo"> <label>Por uma amiga(o)</label>
                    <input type="radio" name="como_soube" value="outra_forma"> <label>Outra forma</label>
                    <input type="text" name="como_soube_outra" placeholder="Qual?">
                </div>
                <label>Gostaria de doar cabelo?</label>
                <div class="radio-group">
                    <input type="radio" name="doar_cabelo" value="sim_mechas"> <label>Sim, mechas (mínimo 15 cm)</label><br>
                    <input type="radio" name="doar_cabelo" value="sim_comprimento"> <label>Sim, comprimento (cabelos limpos e secos)</label><br>
                    <input type="radio" name="doar_cabelo" value="nao"> <label>Não</label>
                </div>
                <label>Deseja atuar como voluntária(o) no projeto voltado para pacientes oncológicos?</label>
                <div class="radio-group">
                    <input type="radio" name="voluntaria_mulheres" value="sim"> <label>Sim</label>
                    <input type="radio" name="voluntaria_mulheres" value="nao"> <label>Não</label>
                </div>
            `;

        } else if (tipoSelecionado === "curso_profissional") {
            info = `Este é um formulário de pré-inscrição...`;
            dynFields.innerHTML = camposPadroes + `
                <h4>Informações adicionais para Cursos de Capacitação Profissional</h4>
                ${gerarCamposEscolaridade()}
                <label>Curso desejado:</label>
                <div class="radio-group">
                    <input type="radio" name="curso_desejado" value="sobrancelha_maquiagem"> <label>Design de sobrancelhas e maquiagem</label><br>
                    <input type="radio" name="curso_desejado" value="manicure_pedicure"> <label>Manicure e pedicure</label><br>
                    <input type="radio" name="curso_desejado" value="informatica"> <label>Informática – montagem e manutenção</label><br>
                    <input type="radio" name="curso_desejado" value="adm_logistica"> <label>Administração/Logística (noite)</label><br>
                    <input type="radio" name="curso_desejado" value="cuidador_idoso"> <label>Cuidador de idoso</label>
                </div>
                <label>Horário disponível:</label>
                <div class="radio-group">
                    <input type="radio" name="horario_disponivel" value="manha"> <label>Manhã</label>
                    <input type="radio" name="horario_disponivel" value="tarde"> <label>Tarde</label>
                </div>
                <label>Por que escolheu este curso?</label>
                <div class="radio-group">
                    <input type="radio" name="motivo_curso" value="nova_area"> <label>Conhecimento em nova área / atuação profissional</label><br>
                    <input type="radio" name="motivo_curso" value="desempregado"> <label>Estou desempregada</label><br>
                    <input type="radio" name="motivo_curso" value="seguranca"> <label>Já atuo na área, mas quero mais segurança</label><br>
                    <input type="radio" name="motivo_curso" value="migrar"> <label>Quero migrar de área e mudar de trabalho</label><br>
                    <input type="radio" name="motivo_curso" value="aposentado"> <label>Estou aposentada e quero ocupar meu tempo</label><br>
                    <input type="radio" name="motivo_curso" value="recolocacao"> <label>Recolocação no mercado de trabalho, de forma qualificada</label><br>
                    <input type="radio" name="motivo_curso" value="outro"> <label>Outro:</label>
                    <input type="text" name="motivo_outro">
                </div>
                <label>Você recebe algum benefício do governo?</label>
                <div class="radio-group">
                    <input type="radio" name="beneficio_governo" value="sim"> <label>Sim</label>
                    <input type="radio" name="beneficio_governo" value="nao"> <label>Não</label>
                    <input type="text" name="qual_beneficio" placeholder="Qual benefício?">
                </div>
            `;
        } else if (tipoSelecionado === "futebol") {
            info = `Escolinha de Futebol — Inscrição para categorias de base...`;
            dynFields.innerHTML = camposPadroes + `
                <h4>Informações adicionais para Escolinha de Futebol</h4>
                <label>Nome do Aluno:</label><input type="text" name="nome_aluno" required>
                <label>Data de Nascimento do Aluno:</label><input type="date" name="nascimento_aluno" required>
                <label>Parentesco:</label><input type="text" name="parentesco" required>
                <label>Disponibilidade de horário:</label>
                <select name="horario_futebol">
                    <option value="manha">Manhã</option>
                    <option value="tarde">Tarde</option>
                    <option value="noite">Noite</option>
                </select>
            `;
        } else if (tipoSelecionado === "parcerias") {
            info = `Parcerias e Doações — Espaço para empresas...`;
            dynFields.innerHTML = `
                <h4>Informações de Parceria/Doação</h4>
                <label>Nome da Empresa ou Doador:</label><input type="text" name="nome_empresa_doador" required>
                <label>Nome do Responsável pelo contato:</label><input type="text" name="responsavel_parceria" required>
                <label>CPF ou CNPJ:</label><input type="text" name="documento_parceria" required>
                <label>Telefone com DDD:</label><input type="tel" name="telefone_parceria" placeholder="(xx) xxxxx-xxxx" required>
                <label>E-mail:</label><input type="email" name="email_parceria" required>
                <label>Endereço completo:</label><input type="text" name="endereco_parceria" required>
                <label>Itens para doação:</label><textarea name="itens_doacao" rows="3"></textarea>
                <label>Valores para doação:</label><input type="text" name="valores_doacao">
                <label>Sugestão de Parceria ou Patrocínio:</label><textarea name="sugestao_parceria" rows="3"></textarea>
            `;
        } else if (tipoSelecionado === "voluntariado") {
            info = `Formulário de Voluntariado — Venha ser um voluntário do Instituto Integração Jovem.`;
            dynFields.innerHTML = camposPadroes + `
                <h4>Informações adicionais para Voluntariado</h4>
                ${gerarCamposEscolaridade()}
                <label>Curso de Formação:</label><input type="text" name="curso_formacao">
                <label>Disponibilidade de Dia e horário:</label><textarea name="disponibilidade" rows="3"></textarea>
            `;
        } else {
            info = "Escolha uma categoria para ver os detalhes aqui.";
            dynFields.innerHTML = ""; // Limpa os campos se nada for selecionado
        }
        
        const titulo = titulos[tipoSelecionado] || "Descrição do formulário";
        descBox.innerHTML = `<h3>${titulo}</h3><p>${info}</p>`;
    });

    // Novo script para enviar o formulário
    cadastroForm.addEventListener("submit", function(event) {
        event.preventDefault(); // Impede o envio padrão do formulário

        const formData = new FormData(this);

        fetch('salvar_cadastro.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Dados salvos com sucesso!");
                cadastroForm.reset(); // Limpa o formulário
                dynFields.innerHTML = ""; // Limpa os campos dinâmicos
                descBox.innerHTML = '<h3>Descrição do formulário</h3><p>Escolha uma categoria para ver os detalhes do cadastro aqui.</p>'; // Reseta a descrição
                tipo.value = ""; // Reseta a seleção
            } else {
                alert("Erro: " + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert("Ocorreu um erro ao enviar o formulário.");
        });
    });
</script>
</body>
</html>