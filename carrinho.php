<?php
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $acao = $_POST['acao'] ?? '';
    $id   = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $qtd  = filter_var($_POST['qtd'] ?? 1, FILTER_VALIDATE_INT);

    if ($acao === 'limpar') {
        unset($_SESSION['carrinho']);
    } elseif ($id) {
        $st = db()->prepare('SELECT nome, estoque FROM produtos WHERE id = ? AND ativo = 1');
        $st->execute([$id]);
        $p = $st->fetch();

        if (!$p && $acao !== 'remover') {
            flash('danger', 'Produto indisponível.');
        } elseif ($acao === 'remover') {
            unset($_SESSION['carrinho'][$id]);
            flash('secondary', 'Item removido do carrinho.');
        } elseif (in_array($acao, ['add', 'atualizar'], true) && $qtd !== false) {
            $estoque = (int)$p['estoque'];
            $nova = $acao === 'add' ? ($_SESSION['carrinho'][$id] ?? 0) + max(1, $qtd) : $qtd;
            if ($nova <= 0) {
                unset($_SESSION['carrinho'][$id]);
            } elseif ($estoque === 0) {
                flash('danger', "“{$p['nome']}” está esgotado.");
            } else {
                if ($nova > $estoque) {
                    $nova = $estoque;
                    flash('warning', "Quantidade ajustada ao estoque disponível ($estoque) de “{$p['nome']}”.");
                } elseif ($acao === 'add') {
                    flash('success', "“{$p['nome']}” adicionado ao carrinho.");
                }
                $_SESSION['carrinho'][$id] = $nova;
            }
        }
    }
    redirect('carrinho.php');
}

$titulo = 'Carrinho';
require __DIR__ . '/includes/header.php';
[$itens, $total] = carrinho_itens();
?>
<h1 class="h3 mb-4">🛒 Seu carrinho</h1>
<?php if (!$itens): ?>
  <div class="alert alert-info">Seu carrinho está vazio. <a href="<?= url() ?>">Explorar produtos</a></div>
<?php else: ?>
<div class="table-responsive bg-white rounded shadow-sm">
<table class="table align-middle mb-0">
  <thead><tr><th>Produto</th><th>Preço</th><th style="width:170px">Qtd</th><th>Subtotal</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($itens as $i): ?>
    <tr>
      <td><img class="thumb me-2" src="<?= e(img_url($i['imagem'])) ?>" alt=""><a href="<?= url('produto.php?id=' . $i['id']) ?>"><?= e($i['nome']) ?></a></td>
      <td><?= brl($i['preco']) ?></td>
      <td>
        <form method="post" class="d-flex gap-1">
          <?= csrf_field() ?><input type="hidden" name="acao" value="atualizar"><input type="hidden" name="id" value="<?= $i['id'] ?>">
          <input class="form-control form-control-sm" type="number" name="qtd" value="<?= $i['qtd'] ?>" min="1" max="<?= (int)$i['estoque'] ?>">
          <button class="btn btn-sm btn-outline-dark" title="Atualizar">↻</button>
        </form>
        <small class="text-muted">Máx.: <?= (int)$i['estoque'] ?></small>
      </td>
      <td class="fw-semibold"><?= brl($i['subtotal']) ?></td>
      <td>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="acao" value="remover"><input type="hidden" name="id" value="<?= $i['id'] ?>">
          <button class="btn btn-sm btn-outline-danger">Remover</button></form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
  <tfoot><tr class="table-light"><th colspan="3" class="text-end">Total geral</th><th class="preco fs-5"><?= brl($total) ?></th><th></th></tr></tfoot>
</table>
</div>
<div class="d-flex justify-content-between mt-3">
  <form method="post"><?= csrf_field() ?><input type="hidden" name="acao" value="limpar"><button class="btn btn-outline-secondary">Esvaziar carrinho</button></form>
  <div><a class="btn btn-outline-dark me-2" href="<?= url() ?>">Continuar comprando</a>
       <a class="btn btn-info btn-lg" href="<?= url('checkout.php') ?>">Finalizar compra</a></div>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
