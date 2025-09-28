<?php
$is_index_page = basename($_SERVER['PHP_SELF']) === 'index.php';
?>

<div class="social-links-container <?php echo $is_index_page ? 'index-page' : 'other-page'; ?>">
    <div class="social-icons">
        <a href="https://www.facebook.com/institutointegracaojovem/" target="_blank" class="social-icon">
            <i class="fab fa-facebook-f"></i>
        </a>
        <a href="https://www.instagram.com/institutointegracaojovem/" target="_blank" class="social-icon">
            <i class="fab fa-instagram"></i>
        </a>
        <a href="https://www.tiktok.com/@institutointegracaojovem" target="_blank" class="social-icon">
            <i class="fab fa-tiktok"></i>
        </a>
        <a href="https://wa.me/5511995890901" target="_blank" class="social-icon">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>
</div>