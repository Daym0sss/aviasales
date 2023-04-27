<?php
require './vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . '/kursach/client/views');
$twig = new \Twig\Environment($loader);
$template = $twig->load('index.html.twig');
echo $template->render();
