<?php
require_once __DIR__ . '/includes/bootstrap.php';
exigir_login();

[$itens, $total] = carrinho_itens();
if (!$itens) { flash('info', 'Seu carrinho está vazio.'); redirect('carrinho.php'); }

$metodos = ['PIX' => 'PIX (aprovação imediata)', 'Boleto' => 'Boleto bancário', 'Cartao' => 'Cartão de crédito'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $metodo = (string)($_POST['pagamento'] ?? '');
    if (!isset($metodos[$metodo])) { flash('danger', 'Selecione uma forma de pagamento.'); redirect('checkout.php'); }

    $pdo = db();
    try {
        $pdo->beginTransaction();
        $totalReal = 0.0; $linhas = [];
        $sel = $pdo->prepare('SELECT id, nome, preco, estoque FROM produtos WHERE id = ? AND ativo = 1 FOR UPDATE');

        // revalida preço e estoque dentro da transação (evita venda acima do estoque)
        foreach (carrinho() as $pid => $qtd) {
            $sel->execute([(int)$pid]);
            $p = $sel->fetch();
            if (!$p || (int)$p['estoque'] < $qtd) {
                throw new RuntimeException('“' . ($p['nome'] ?? "Produto #$pid") . '” não possui estoque suficiente. Ajuste seu carrinho.');
            }
            $linhas[] = [$p['id'], $qtd, $p['preco']];
            $totalReal += $qtd * (float)$p['preco'];
        }

        $pdo->prepare('INSERT INTO pedidos (usuario_id, forma_pagamento, status, total) VALUES (?, ?, ?, ?)')
            ->execute([usuario()['id'], $metodo, 'pago', number_format($totalReal, 2, '.', '')]);
        $pedidoId = (int)$pdo->lastInsertId();

        $insItem = $pdo->prepare('INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)');
        $baixa   = $pdo->prepare('UPDATE produtos SET estoque = estoque - ? WHERE id = ? AND estoque >= ?');
        foreach ($linhas as [$pid, $qtd, $preco]) {
            $insItem->execute([$pedidoId, $pid, $qtd, $preco]);   // preço congelado
            $baixa->execute([$qtd, $pid, $qtd]);                  // baixa de estoque
            if ($baixa->rowCount() !== 1) throw new RuntimeException('Estoque alterado durante a compra. Tente novamente.');
        }
        $pdo->commit();
        unset($_SESSION['carrinho']);
        redirect('pedido_sucesso.php?id=' . $pedidoId);
    } catch (RuntimeException $ex) {
        $pdo->rollBack();
        flash('danger', $ex->getMessage());
        redirect('carrinho.php');
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log($ex->getMessage());
        flash('danger', 'Não foi possível concluir o pedido. Tente novamente.');
        redirect('checkout.php');
    }
}

$titulo = 'Checkout';
require __DIR__ . '/includes/header.php';
?>
<h1 class="h3 mb-4">Finalizar compra</h1>
<div class="row g-4">
  <div class="col-lg-7">
    <div class="card shadow-sm"><div class="card-header fw-semibold">Resumo do pedido</div>
      <ul class="list-group list-group-flush">
        <?php foreach ($itens as $i): ?>
          <li class="list-group-item d-flex justify-content-between"><span><?= (int)$i['qtd'] ?>× <?= e($i['nome']) ?></span><span><?= brl($i['subtotal']) ?></span></li>
        <?php endforeach; ?>
        <li class="list-group-item d-flex justify-content-between fw-bold"><span>Total</span><span class="preco"><?= brl($total) ?></span></li>
      </ul>
    </div>
  </div>
  <div class="col-lg-5">
    <form method="post" class="card shadow-sm" data-once>
      <div class="card-header fw-semibold">Pagamento (simulado)</div>
      <div class="card-body">
        <?= csrf_field() ?>
        <?php foreach ($metodos as $k => $rotulo): ?>
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio" name="pagamento" id="p<?= $k ?>" value="<?= $k ?>" required>
            <label class="form-check-label" for="p<?= $k ?>"><?= e($rotulo) ?></label>
          </div>
        <?php endforeach; ?>
        <p class="small text-muted">Nenhum dado financeiro real é coletado. A confirmação é imediata.</p>
        <button class="btn btn-info btn-lg w-100" type="submit">Confirmar pagamento de <?= brl($total) ?></button>
      </div>
    </form>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
