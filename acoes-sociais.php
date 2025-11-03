<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ações Sociais - Instituto Integração Jovem</title>
    <link rel="stylesheet" href="style.css">
    <style>
        main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 10px;
        }       
        .carousel {
            width: 100%;
            max-width: 600px;
            margin: 10px auto;
            overflow: hidden;
            position: relative;
            border: 5px solid #004aad;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .carousel img {
            width: 100%;
            display: block;
            border-radius: 10px;
        }
        h2 {
            color: #004aad;
            text-align: center;
            margin-top: 0px;
        }
        p {
            max-width: 900px;
            margin: 10px auto;
            padding: 0 15px;
            text-align: justify;
        }
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

<?php
include 'conexao.php';

// Buscar apenas links da seção "acoes_sociais" para o carousel
$sql_carousel = "SELECT url FROM links WHERE secao = 'acoes_sociais' ORDER BY id DESC";
$result_carousel = $conn->query($sql_carousel);

$imageUrls = [];

if ($result_carousel && $result_carousel->num_rows > 0) {
    while ($row = $result_carousel->fetch_assoc()) {
        $url = trim($row['url']);

        // ✅ Caso 1: Instagram (modelo 2)
        if (strpos($url, 'instagram.com/p/') !== false) {
            $parts = explode('/', $url);
            $postId = isset($parts[4]) ? $parts[4] : '';
            if (!empty($postId)) {
                $imageUrl = 'https://www.instagram.com/p/' . $postId . '/media/?size=l';
                $imageUrls[] = 'proxy.php?url=' . urlencode($imageUrl);
            }
        }

        // ✅ Caso 2: Facebook - imagem direta (scontent)
        elseif (strpos($url, 'scontent') !== false && preg_match('/\.(jpg|jpeg|png|gif|webp)/i', $url)) {
            $imageUrls[] = $url; // já é imagem direta, não precisa proxy
        }

        // ✅ Caso 3: Facebook - link de post, usa proxy
        elseif (strpos($url, 'facebook.com') !== false) {
            $imageUrls[] = 'proxy.php?url=' . urlencode($url);
        }

        // ✅ Caso 4: Imagem genérica (link direto)
        elseif (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $url)) {
            $imageUrls[] = $url;
        }

        // ⚙️ Caso 5: outro tipo de link (mantém original ou ignora)
        else {
            $imageUrls[] = $url;
        }
    }
}


$conn->close();
?>

<main>
    <h2>Ações Sociais</h2>
    <p>
        A importância do Instituto Integração Jovem no trabalho SOCIAL, junto à comunidade que já atua há mais de 5 anos, atendendo mais de 500 famílias diretamente e indiretamente e já atendeu muito mais, com muita luta e dedicação, levando cestas básicas, hortifrútis e o programa Viva Leite mensalmente, entre outros. O trabalho não para! Força, foco e fé!! 🙏🙏🙏🏽🙌🏽.
        Você pode ser beneficiado através de nossas ações ou ser um apoiador do nosso trabalho.
    </p>

    <div class="carousel">
        <img id="carousel-image" src="<?= !empty($imageUrls) ? htmlspecialchars($imageUrls[0]) : '#' ?>" alt="Carrossel Ações Sociais">
        <button class="carousel-btn prev" onclick="prevImage()">&#10094;</button>
        <button class="carousel-btn next" onclick="nextImage()">&#10095;</button>
        <button id="pause-play-btn" class="pause-play" onclick="togglePlayPause()">Pausar</button>
    </div>
    
    <a href="cadastro.php" class="btn-cadastro">Cadastre-se</a>
</main>

<?php include 'footer.php'; ?>

<script>
    const images = <?= json_encode($imageUrls) ?>;
    let currentIndex = 0;
    const carouselImage = document.getElementById("carousel-image");
    const pausePlayBtn = document.getElementById("pause-play-btn");
    let intervalId;

    function nextImage() {
        if (images.length === 0) return;
        currentIndex = (currentIndex + 1) % images.length;
        carouselImage.src = images[currentIndex];
    }
    
    function prevImage() {
        if (images.length === 0) return;
        currentIndex = (currentIndex - 1 + images.length) % images.length;
        carouselImage.src = images[currentIndex];
    }

    function startCarousel() {
        if (images.length > 1) {
            intervalId = setInterval(nextImage, 5000);
        } else {
            pausePlayBtn.style.display = 'none';
        }
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