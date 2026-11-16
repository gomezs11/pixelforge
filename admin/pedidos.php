<?php
require_once __DIR__ . '/../includes/bootstrap.php';
exigir_admin();
$titulo = 'Admin · Pedidos';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/_menu.php';

$pedidos = db()->query('SELECT p.id, p.forma_pagamento, p.status, p.total, p.criado_em, u.nome, u.email
                        FROM pedidos p JOIN usuarios u ON u.id = p.usuario_id ORDER BY p.id DESC')->fetchAll();
$itens = [];
$q = db()->query('SELECT i.pedido_id, i.quantidade, i.preco_unitario, pr.nome
                  FROM itens_pedido i JOIN produtos pr ON pr.id = i.produto_id ORDER BY i.id');
foreach ($q->fetchAll() as $r) $itens[$r['pedido_id']][] = $r;
?>
<h1 class="h4 mb-3">Pedidos realizados</h1>
<?php if (!$pedidos): ?><div class="alert alert-info">Nenhum pedido até o momento.</div><?php endif; ?>
<div class="accordion" id="acc">
<?php foreach ($pedidos as $p): ?>
  <div class="accordion-item">
    <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#p<?= $p['id'] ?>">
      <span class="me-3 fw-bold">#<?= $p['id'] ?></span><span class="me-3"><?= e($p['nome']) ?></span>
      <span class="me-3 text-muted small"><?= date('d/m/Y H:i', strtotime($p['criado_em'])) ?> · <?= e($p['forma_pagamento']) ?></span>
      <span class="ms-auto me-3 preco"><?= brl($p['total']) ?></span></button></h2>
    <div id="p<?= $p['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#acc"><div class="accordion-body">
      <div class="small text-muted mb-2">Cliente: <?= e($p['email']) ?> · Status: <?= e($p['status']) ?></div>
      <table class="table table-sm mb-0"><thead><tr><th>Produto</th><th>Qtd</th><th>Preço unit.</th><th>Subtotal</th></tr></thead><tbody>
      <?php foreach ($itens[$p['id']] ?? [] as $i): ?>
        <tr><td><?= e($i['nome']) ?></td><td><?= (int)$i['quantidade'] ?></td><td><?= brl($i['preco_unitario']) ?></td><td><?= brl($i['quantidade'] * $i['preco_unitario']) ?></td></tr>
      <?php endforeach; ?>
      </tbody></table>
    </div></div>
  </div>
<?php endforeach; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
