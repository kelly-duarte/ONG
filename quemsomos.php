<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quem Somos - Instituto Integração Jovem</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* página Quem Somos */
     main {
     max-width: 1200px;  
     margin: 0 auto;     
     padding: 20px;
      }

        .content-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 15px;
        }

        .content-container h2 {
            text-align: center;
            color: #003366;
            margin-bottom: 20px;
        }

        .activity-section {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .activity-section:nth-child(odd) {
            flex-direction: row;
        }
        
        .activity-section:nth-child(even) {
            flex-direction: row-reverse;
        }

        .activity-text {
            flex: 1;
            min-width: 280px;
        }

        .activity-image {
            width: 45%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .intro-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            width: 100%;
        }

        .btn-primary, .btn-secondary {
            display: inline-block;
            padding: 12px 25px;
            font-weight: bold;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background-color: #ffcc00;
            color: #003366;
        }

        .btn-secondary {
            background-color: #003366;
            color: white;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .activity-section {
                flex-direction: column;
                text-align: center;
            }
            .activity-image {
                width: 100%;
                margin-top: 15px;
            }
            .intro-buttons {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <main>
        <div class="content-container">
            <h2>Nossa História e Missão</h2>
            <p style="text-align: justify;">O Instituto Integração Jovem é dedicado a transformar vidas por meio do esporte, educação e ações sociais. Acreditamos que, ao oferecer um ambiente seguro e de apoio, podemos capacitar jovens a desenvolverem seu potencial máximo e se tornarem cidadãos ativos e responsáveis. Nossa jornada começou com a visão de criar um espaço onde a comunidade pudesse crescer junta, fortalecendo laços e construindo um futuro melhor para todos.</p>
        </div>

        <div class="content-container">
            <h2>Nossas Atividades</h2>
            
            <div class="activity-section">
                <div class="activity-text">
                    <h3>Futebol</h3>
                    <p style="text-align: justify;">Nossas aulas de futebol vão muito além do campo. Elas ensinam valores como trabalho em equipe, disciplina, respeito e resiliência. Através de treinamentos e participação em campeonatos locais, os jovens aprendem a superar desafios e a celebrar conquistas em grupo.</p>
                </div>
                <img src="https://uploads.onecompiler.io/43fvz8gg6/43vemwgzp/FB_IMG_1756726982782.jpg" alt="Atividade de Futebol" class="activity-image">
            </div>

            <div class="activity-section">
                <div class="activity-text">
                    <h3>Ações Sociais</h3>
                    <p style="text-align: justify;">Realizamos mutirões de limpeza, campanhas de doação de alimentos e eventos comunitários. O nosso objetivo é fortalecer os laços com a comunidade e mostrar aos jovens a importância de serem cidadãos ativos e de fazer a diferença no mundo ao seu redor.</p>
                </div>
                <img src="https://uploads.onecompiler.io/43fvz8gg6/43vemwgzp/FB_IMG_1756726014741.jpg" alt="Ação Social" class="activity-image">
            </div>

            <div class="activity-section">
                <div class="activity-text">
                    <h3>Cursos de Informática</h3>
                    <p style="text-align: justify;">Oferecemos cursos de informática para equipar nossos jovens com habilidades digitais essenciais para o mercado de trabalho. Nossos cursos incluem pacote Office, noções de programação e segurança online, preparando-os para as oportunidades do futuro.</p>
                </div>
                <img src="https://uploads.onecompiler.io/43fvz8gg6/43v2cgpmr/1000947633.jpg" alt="Cursos de Informática" class="activity-image">
            </div>
        </div>
        
        <div class="intro-buttons">
            <a href="cadastro.php" class="btn btn-primary">Faça Parte do Nosso Time</a>
            <a href="https://wa.me/5511995890901" class="btn btn-secondary" target="_blank">Entre em Contato</a>
        </div>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>