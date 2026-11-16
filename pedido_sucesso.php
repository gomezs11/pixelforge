<?php
require_once __DIR__ . '/includes/bootstrap.php';
exigir_login();

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$st = db()->prepare('SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?');   // só o dono vê o pedido
$st->execute([$id ?: 0, usuario()['id']]);
$ped = $st->fetch();
if (!$ped) { http_response_code(404); exit('Pedido não encontrado.'); }

$it = db()->prepare('SELECT i.quantidade, i.preco_unitario, p.nome FROM itens_pedido i JOIN produtos p ON p.id = i.produto_id WHERE i.pedido_id = ?');
$it->execute([$ped['id']]);
$itens = $it->fetchAll();

$titulo = 'Pedido confirmado';
require __DIR__ . '/includes/header.php';
?>
<div class="text-center mb-4"><div class="display-3">✅</div><h1 class="h3">Pagamento confirmado!</h1>
  <p class="text-muted">Pedido #<?= (int)$ped['id'] ?> · <?= e($ped['forma_pagamento']) ?> · <?= date('d/m/Y H:i', strtotime($ped['criado_em'])) ?></p></div>
<div class="card shadow-sm mx-auto" style="max-width:640px"><ul class="list-group list-group-flush">
  <?php foreach ($itens as $i): ?>
    <li class="list-group-item d-flex justify-content-between"><span><?= (int)$i['quantidade'] ?>× <?= e($i['nome']) ?></span><span><?= brl($i['quantidade'] * $i['preco_unitario']) ?></span></li>
  <?php endforeach; ?>
  <li class="list-group-item d-flex justify-content-between fw-bold"><span>Total pago</span><span class="preco"><?= brl($ped['total']) ?></span></li>
</ul></div>
<div class="text-center mt-4"><a class="btn btn-dark" href="<?= url() ?>">Voltar à loja</a></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
