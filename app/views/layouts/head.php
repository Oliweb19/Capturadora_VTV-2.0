<?php
/**
 * Layout: head.php
 * Parámetros esperados en $head_data:
 *   'title'    => string  (título de la página)
 *   'css'      => array   (rutas relativas a BASE_URL/public/css/)
 */
$title = $head_data['title'] ?? 'Capturadora VTV';
$css   = $head_data['css']   ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>

    <!-- Google Fonts: Material Symbols -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

    <!-- CSS Base -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/base/variables.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/base/reset.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/base/typography.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/components/buttons.css">

    <!-- CSS específico de la página -->
    <?php foreach ($css as $file): ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/<?= htmlspecialchars($file) ?>">
    <?php endforeach; ?>

    <link rel="shortcut icon" href="<?= BASE_URL ?>/img/favicon.ico">
</head>
<body>
