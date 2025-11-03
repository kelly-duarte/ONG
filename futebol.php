<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos - Instituto Integração Jovem</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Ajuste rápido só para garantir que o carrossel funcione bem */
      main {
          max-width: 1200px;  /* largura máxima */
          margin: 0 auto;     /* centraliza horizontalmente */
          padding: 10px;
       }       
  
       .carousel {
            width: 100%;
            max-width: 600px;
            margin: 10px auto;
            overflow: hidden;
            position: relative;
            border: 5px solid #004aad; /* azul */
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .carousel img {
            width: 100%;
            display: block;
            border-radius: 10px;
        }

        h2 {
            color: #004aad; /* azul */
            text-align: center;
            margin-top: 0px;
        }

        p {
            max-width: 900px;
            margin: 10px auto;
            padding: 0 15px;
            text-align: justify;
        }

        /* Estilos dos novos botões de navegação */
        .carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            font-size: 24px;
            transition: background-color 0.3s;
            z-index: 10;
        }

        .carousel-btn:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }

        .prev {
            left: 10px;
            border-radius: 5px 0 0 5px;
        }

        .next {
            right: 10px;
            border-radius: 0 5px 5px 0;
        }

        .pause-play {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
            transition: background-color 0.3s;
            z-index: 10;
        }

        .pause-play:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }

        /* Novo botão "Cadastre-se" */
        .btn-cadastro {
            display: block;
            width: fit-content;
            margin: 30px auto;
            padding: 15px 30px;
            background-color: #ffcc00;
            color: #003366;
            font-weight: bold;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            transition: background-color 0.3s;
        }
        
        .btn-cadastro:hover {
            background-color: #e6b800;
        }

    </style>
</head>
<body>

<?php include 'header.php'; ?>

    <main>
        <h2>Futebol</h2>
        <p>
            O Instituto Integração Jovem oferece aulas de futebol e futsal como parte de seu projeto social na Zona Oeste de São Paulo.O objetivo do futebol no Instituto Integração Jovem vai além da prática esportiva, buscando impactar positivamente a vida dos jovens através dos valores e benefícios que o esporte pode proporcionar.A inclusão social no esporte promove a importância da diversidade e combate preconceitos, criando ambientes mais acolhedores e inclusivos.As aulas são destinadas a crianças e adolescentes de 6 a 17 anos e ocorrem às terças e quintas-feiras e possuem dois turnos: manhã (8h30 às 11h00) e tarde (14h30 às 17h00).⚽⚽⚽
Você pode ser beneficiado atraves de nossas ações ou ser um apoiador do nosso trabalho.
        </p>

        <div class="carousel">
            <img id="carousel-image" src="https://uploads.onecompiler.io/43fvz8gg6/43vemwgzp/FB_IMG_1756726982782.jpg" alt="Carrossel Ações Sociais">
            <button class="carousel-btn prev" onclick="prevImage()">&#10094;</button>
            <button class="carousel-btn next" onclick="nextImage()">&#10095;</button>
            <button id="pause-play-btn" class="pause-play" onclick="togglePlayPause()">Pausar</button>
        </div>
        
        <a href="cadastro.php" class="btn-cadastro">Cadastre-se</a>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        const images = [
            "https://uploads.onecompiler.io/43fvz8gg6/43vemwgzp/FB_IMG_1756726982782.jpg",
            "https://uploads.onecompiler.io/43fvz8gg6/43vemwgzp/futebol2.jpg",
            "https://uploads.onecompiler.io/43fvz8gg6/43vemwgzp/FB_IMG_1759241584508.jpg",
            "https://uploads.onecompiler.io/43fvz8gg6/43vemwgzp/FB_IMG_1759241603258.jpg",
            "https://uploads.onecompiler.io/43fvz8gg6/43vemwgzp/FB_IMG_1759241686721.jpg",
            "https://uploads.onecompiler.io/43fvz8gg6/43vemwgzp/futebol.jpg",
        ];

        let currentIndex = 0;
        const carouselImage = document.getElementById("carousel-image");
        const pausePlayBtn = document.getElementById("pause-play-btn");
        let intervalId;

        function nextImage() {
            currentIndex = (currentIndex + 1) % images.length;
            carouselImage.src = images[currentIndex];
        }
        
        function prevImage() {
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            carouselImage.src = images[currentIndex];
        }

        function startCarousel() {
            intervalId = setInterval(nextImage, 5000);
        }
        
        function stopCarousel() {
            clearInterval(intervalId);
        }
        
        function togglePlayPause() {
            if (pausePlayBtn.textContent === "Pausar") {
                stopCarousel();
                pausePlayBtn.textContent = "Reproduzir";
            } else {
                startCarousel();
                pausePlayBtn.textContent = "Pausar";
            }
        }

        window.onload = startCarousel;
    </script>
</body>
</html>