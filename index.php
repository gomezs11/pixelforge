<?php
$titulo = 'Vitrine';
require __DIR__ . '/includes/header.php';

$busca = trim((string)($_GET['q'] ?? ''));
$catId = filter_var($_GET['categoria'] ?? null, FILTER_VALIDATE_INT) ?: null;

$sql = 'SELECT p.id, p.nome, p.preco, p.estoque, p.imagem, c.nome AS categoria
        FROM produtos p JOIN categorias c ON c.id = p.categoria_id WHERE p.ativo = 1';
$params = [];
if ($busca !== '') { $sql .= ' AND (p.nome LIKE ? OR p.descricao LIKE ?)'; $params[] = "%$busca%"; $params[] = "%$busca%"; }
if ($catId)        { $sql .= ' AND p.categoria_id = ?'; $params[] = $catId; }
$sql .= ' ORDER BY p.nome';

$st = db()->prepare($sql);
$st->execute($params);
$produtos = $st->fetchAll();
$categorias = db()->query('SELECT id, nome FROM categorias ORDER BY nome')->fetchAll();
?>
<div class="hero p-4 p-md-5 mb-4">
  <h1 class="display-6 fw-bold">Equipe seu setup. Domine o jogo.</h1>
  <p class="mb-0 text-info">Consoles, jogos, periféricos e acessórios com pagamento simulado via PIX, boleto ou cartão.</p>
</div>

<form class="row g-2 mb-4" method="get">
  <div class="col-md-6"><input class="form-control" type="search" name="q" placeholder="Buscar produtos..." value="<?= e($busca) ?>"></div>
  <div class="col-md-4">
    <select class="form-select" name="categoria">
      <option value="">Todas as categorias</option>
      <?php foreach ($categorias as $c): ?>
        <option value="<?= $c['id'] ?>" <?= $catId === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['nome']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2 d-grid"><button class="btn btn-dark">Filtrar</button></div>
</form>

<?php if (!$produtos): ?>
  <div class="alert alert-info">Nenhum produto encontrado. <a href="<?= url() ?>">Limpar filtros</a></div>
<?php endif; ?>

<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
<?php foreach ($produtos as $p): ?>
  <div class="col">
    <div class="card card-produto h-100 shadow-sm">
      <a href="<?= url('produto.php?id=' . $p['id']) ?>"><img src="<?= e(img_url($p['imagem'])) ?>" class="card-img-top" alt="<?= e($p['nome']) ?>"></a>
      <div class="card-body d-flex flex-column">
        <span class="badge text-bg-secondary align-self-start mb-2"><?= e($p['categoria']) ?></span>
        <h2 class="h6"><a class="text-decoration-none text-dark" href="<?= url('produto.php?id=' . $p['id']) ?>"><?= e($p['nome']) ?></a></h2>
        <div class="preco fs-5 mt-auto"><?= brl($p['preco']) ?></div>
        <?php if ($p['estoque'] > 0): ?>
          <form method="post" action="<?= url('carrinho.php') ?>" class="mt-2">
            <?= csrf_field() ?><input type="hidden" name="acao" value="add">
            <input type="hidden" name="id" value="<?= $p['id'] ?>"><input type="hidden" name="qtd" value="1">
            <button class="btn btn-info w-100">Adicionar ao carrinho</button>
          </form>
        <?php else: ?>
          <button class="btn btn-secondary w-100 mt-2" disabled>Esgotado</button>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
