<?php
require_once __DIR__ . '/includes/bootstrap.php';
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$st = db()->prepare('SELECT p.*, c.nome AS categoria FROM produtos p JOIN categorias c ON c.id = p.categoria_id WHERE p.id = ? AND p.ativo = 1');
$st->execute([$id ?: 0]);
$p = $st->fetch();
if (!$p) { http_response_code(404); $titulo = 'Não encontrado'; require __DIR__ . '/includes/header.php';
  echo '<div class="alert alert-warning">Produto não encontrado. <a href="' . url() . '">Voltar à loja</a></div>';
  require __DIR__ . '/includes/footer.php'; exit; }

$titulo = $p['nome'];
require __DIR__ . '/includes/header.php';
$estoque = (int)$p['estoque'];
?>
<nav aria-label="breadcrumb"><ol class="breadcrumb">
  <li class="breadcrumb-item"><a href="<?= url() ?>">Loja</a></li>
  <li class="breadcrumb-item"><a href="<?= url('?categoria=' . $p['categoria_id']) ?>"><?= e($p['categoria']) ?></a></li>
  <li class="breadcrumb-item active"><?= e($p['nome']) ?></li>
</ol></nav>
<div class="row g-4">
  <div class="col-md-6"><img class="img-detalhe shadow-sm" src="<?= e(img_url($p['imagem'])) ?>" alt="<?= e($p['nome']) ?>"></div>
  <div class="col-md-6">
    <span class="text-muted small">Cód. #<?= $p['id'] ?> · <?= e($p['categoria']) ?></span>
    <h1 class="h2"><?= e($p['nome']) ?></h1>
    <div class="preco display-6 my-3"><?= brl($p['preco']) ?> <small class="fs-6 text-muted">/ unidade</small></div>
    <?php if ($estoque === 0): ?>
      <span class="badge text-bg-danger fs-6">Esgotado</span>
    <?php elseif ($estoque <= 5): ?>
      <span class="badge text-bg-warning fs-6">Últimas <?= $estoque ?> unidades!</span>
    <?php else: ?>
      <span class="badge text-bg-success fs-6">Em estoque (<?= $estoque ?> disponíveis)</span>
    <?php endif; ?>
    <p class="mt-3"><?= nl2br(e($p['descricao'])) ?></p>
    <?php if ($estoque > 0): ?>
      <form method="post" action="<?= url('carrinho.php') ?>" class="row g-2 align-items-end">
        <?= csrf_field() ?><input type="hidden" name="acao" value="add"><input type="hidden" name="id" value="<?= $p['id'] ?>">
        <div class="col-4 col-md-3"><label class="form-label small">Quantidade</label>
          <input class="form-control" type="number" name="qtd" value="1" min="1" max="<?= $estoque ?>"></div>
        <div class="col"><button class="btn btn-info btn-lg">Adicionar ao carrinho</button></div>
      </form>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
