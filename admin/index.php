<?php
require_once __DIR__ . '/../includes/bootstrap.php';
exigir_admin();
$titulo = 'Admin · Produtos';
require __DIR__ . '/../includes/header.php';

$produtos = db()->query('SELECT p.id, p.nome, p.preco, p.estoque, p.ativo, p.imagem, c.nome AS categoria
                         FROM produtos p JOIN categorias c ON c.id = p.categoria_id ORDER BY p.id DESC')->fetchAll();
$res = db()->query("SELECT COUNT(*) qtd, COALESCE(SUM(total),0) soma FROM pedidos WHERE status = 'pago'")->fetch();
?>
<h1 class="h3 mb-3">Painel administrativo</h1>
<div class="row g-3 mb-4">
  <div class="col-sm-4"><div class="card shadow-sm"><div class="card-body"><div class="text-muted small">Produtos</div><div class="fs-3 fw-bold"><?= count($produtos) ?></div></div></div></div>
  <div class="col-sm-4"><div class="card shadow-sm"><div class="card-body"><div class="text-muted small">Pedidos pagos</div><div class="fs-3 fw-bold"><?= (int)$res['qtd'] ?></div></div></div></div>
  <div class="col-sm-4"><div class="card shadow-sm"><div class="card-body"><div class="text-muted small">Faturamento</div><div class="fs-3 fw-bold preco"><?= brl($res['soma']) ?></div></div></div></div>
</div>
<?php require __DIR__ . '/_menu.php'; ?>
<div class="table-responsive bg-white rounded shadow-sm">
<table class="table align-middle mb-0">
  <thead><tr><th>#</th><th>Produto</th><th>Categoria</th><th>Preço</th><th>Estoque</th><th>Status</th><th class="text-end">Ações</th></tr></thead>
  <tbody>
  <?php foreach ($produtos as $p): ?>
    <tr class="<?= $p['ativo'] ? '' : 'table-secondary' ?>">
      <td><?= $p['id'] ?></td>
      <td><img class="thumb me-2" src="<?= e(img_url($p['imagem'])) ?>" alt=""><?= e($p['nome']) ?></td>
      <td><?= e($p['categoria']) ?></td>
      <td><?= brl($p['preco']) ?></td>
      <td><?= (int)$p['estoque'] ?></td>
      <td><span class="badge text-bg-<?= $p['ativo'] ? 'success' : 'secondary' ?>"><?= $p['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
      <td class="text-end">
        <a class="btn btn-sm btn-outline-dark" href="<?= url('admin/produto_form.php?id=' . $p['id']) ?>">Editar</a>
        <form method="post" action="<?= url('admin/produto_excluir.php') ?>" class="d-inline"
              onsubmit="return confirm('Excluir este produto? Se já tiver vendas, ele será apenas desativado.')">
          <?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>">
          <button class="btn btn-sm btn-outline-danger">Excluir</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
