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
      link: '#quem-somos-geral',
      title: 'Quem Somos',
      description: 'O Instituto Integração Jovem Santa Terezinha...'
    },
    {
      img: 'https://uploads.onecompiler.io/43fvz8gg6/43vemwgzp/FB_IMG_1756726982782.jpg',
      link: '#futebol',
      title: 'Futebol',
      description: 'No Instituto Integração Jovem, o futebol vai muito além...'
    },
    {
      img: 'https://uploads.onecompiler.io/43fvz8gg6/43vemwgzp/FB_IMG_1756726014741.jpg',
      link: '#acoes-sociais',
      title: 'Ações Sociais',
      description: 'Nossas ações sociais são o coração do Instituto...'
    },
    {
      img: 'https://uploads.onecompiler.io/43fvz8gg6/43v2cgpmr/1000947633.jpg',
      link: 'cursos.php',
      title: 'Cursos',
      description: 'O Instituto Integração Jovem oferece uma variedade de cursos...'
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
