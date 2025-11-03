<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
if (!isset($_GET['url']) || strpos($_GET['url'], 'instagram.com') === false) {
    header('HTTP/1.1 403 Forbidden');
    die('Acesso negado');
}
$url = $_GET['url'];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Segue redirecionamentos
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
$data = curl_exec($ch);
if ($data === false) {
    die('Falha ao carregar a imagem: ' . curl_error($ch));
}
$finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // Pega o URL final após redirecionamento
$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $finalUrl);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
$data = curl_exec($ch2);
if ($data === false) {
    die('Falha ao carregar a imagem do URL final: ' . curl_error($ch2));
}
$contentType = curl_getinfo($ch2, CURLINFO_CONTENT_TYPE);
header('Content-Type: ' . $contentType);
echo $data;
curl_close($ch2);
curl_close($ch);
?>

