<?php
require_once __DIR__ . '/../includes/bootstrap.php';
exigir_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('admin/');
csrf_verificar();

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if ($id) {
    $vendas = db()->prepare('SELECT COUNT(*) FROM itens_pedido WHERE produto_id = ?');
    $vendas->execute([$id]);
    if ((int)$vendas->fetchColumn() > 0) {
        // preserva o histórico de pedidos: apenas desativa
        db()->prepare('UPDATE produtos SET ativo = 0 WHERE id = ?')->execute([$id]);
        flash('warning', 'Produto com vendas registradas foi desativado (histórico preservado).');
    } else {
        db()->prepare('DELETE FROM produtos WHERE id = ?')->execute([$id]);
        flash('success', 'Produto excluído.');
    }
}
redirect('admin/');
