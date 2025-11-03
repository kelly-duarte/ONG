<script>
  const carouselImage = document.getElementById('carousel-image');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const dynamicTitle = document.getElementById('dynamic-title');
  const dynamicText = document.getElementById('dynamic-text');
  let autoSlideInterval;

  const slides = [
    {
      img: 'https://uploads.onecompiler.io/43fvz8gg6/43vemwgzp/FB_IMG_1756736411188.jpg',
      link: 'quemsomos.php',
      title: 'Quem Somos',
      description: 'O Instituto Integração Jovem é uma organização dedicada a transformar a vida de crianças, adolescentes e suas famílias por meio da educação, do esporte e da cultura. Nosso compromisso é oferecer oportunidades que promovam cidadania, inclusão social e desenvolvimento humano, criando caminhos para um futuro melhor. Atuamos de forma comunitária, com voluntários e profissionais engajados em impactar positivamente a sociedade'
    },
    {
      img: 'https://uploads.onecompiler.io/43fvz8gg6/43vemwgzp/FB_IMG_1756726982782.jpg',
      link: 'futebol.php',
      title: 'Futebol',
      description: 'O futebol é uma das principais ferramentas de integração do Instituto. Por meio do esporte, ensinamos disciplina, trabalho em equipe, respeito e perseverança. As escolinhas de futebol atendem jovens de diferentes idades, promovendo não apenas a prática esportiva, mas também valores que formam cidadãos conscientes e preparados para os desafios da vida. Nosso objetivo vai além do campo: queremos formar campeões dentro e fora dele.'
    },
    {
      img: 'https://uploads.onecompiler.io/43fvz8gg6/43vemwgzp/FB_IMG_1756726014741.jpg',
      link: 'acoes-sociais.php',
      title: 'Ações Sociais',
      description: 'Nossas ações sociais têm como foco apoiar famílias em situação de vulnerabilidade. Realizamos campanhas de arrecadação de alimentos, roupas e brinquedos, além de oferecer oficinas e atividades culturais para toda a comunidade. Acreditamos que pequenas atitudes podem gerar grandes mudanças, e por isso trabalhamos lado a lado com parceiros e voluntários para promover solidariedade, inclusão e esperança.'
    },
    {
      img: 'https://uploads.onecompiler.io/43fvz8gg6/43v2cgpmr/1000947633.jpg',
      link: 'cursos.php',
      title: 'Cursos',
      description: 'O Instituto Integração Jovem oferece cursos voltados para a capacitação profissional e o desenvolvimento pessoal de jovens e adultos. Entre as áreas disponíveis estão: informática básica e avançada, manutenção de computadores, design de sobrancelhas, manicure e pedicure, além de oficinas de arte e cultura. Nosso objetivo é preparar os participantes para o mercado de trabalho, incentivando o empreendedorismo e fortalecendo a autoestima.'
    }
  ];

  let currentSlideIndex = 0;

  function updateCarousel() {
    const slide = slides[currentSlideIndex];
    carouselImage.src = slide.img;
    carouselImage.onclick = () => window.location.href = slide.link;
    dynamicTitle.textContent = slide.title;
    dynamicText.textContent = slide.description;
  }

  function changeSlide(direction) {
    currentSlideIndex = (currentSlideIndex + direction + slides.length) % slides.length;
    updateCarousel();
  }

  function startAutoSlide() {
    autoSlideInterval = setInterval(() => changeSlide(1), 5000);
  }

  function stopAutoSlide() {
    clearInterval(autoSlideInterval);
  }

  prevBtn.addEventListener('click', () => {
    stopAutoSlide();
    changeSlide(-1);
    startAutoSlide();
  });

  nextBtn.addEventListener('click', () => {
    stopAutoSlide();
    changeSlide(1);
    startAutoSlide();
  });

  document.addEventListener('DOMContentLoaded', () => {
    updateCarousel();
    startAutoSlide();
  });
</script>
