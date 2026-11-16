<?php
require_once __DIR__ . '/bootstrap.php';
$u = usuario();
$titulo = $titulo ?? 'PixelForge';
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titulo) ?> | PixelForge</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark pf-nav sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= url() ?>">⚒ Pixel<span class="text-info">Forge</span></a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div id="nav" class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="<?= url() ?>">Loja</a></li>
        <?php if ($u && $u['perfil'] === 'admin'): ?>
          <li class="nav-item"><a class="nav-link" href="<?= url('admin/') ?>">Painel Admin</a></li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav align-items-lg-center gap-lg-2">
        <li class="nav-item"><a class="nav-link" href="<?= url('carrinho.php') ?>">🛒 Carrinho
          <span class="badge bg-info text-dark"><?= carrinho_qtd() ?></span></a></li>
        <?php if ($u): ?>
          <li class="nav-item"><span class="nav-link">Olá, <?= e(explode(' ', $u['nome'])[0]) ?></span></li>
          <li class="nav-item"><a class="btn btn-outline-light btn-sm" href="<?= url('logout.php') ?>">Sair</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= url('login.php') ?>">Entrar</a></li>
          <li class="nav-item"><a class="btn btn-info btn-sm" href="<?= url('cadastro.php') ?>">Cadastrar</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<main class="container py-4 flex-grow-1">
<?php flash_render(); ?>
