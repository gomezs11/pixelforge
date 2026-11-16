<?php
require_once __DIR__ . '/../includes/bootstrap.php';
exigir_admin();

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
$p = ['nome' => '', 'descricao' => '', 'preco' => '', 'estoque' => 0, 'categoria_id' => '', 'imagem' => null, 'ativo' => 1];
if ($id) {
    $st = db()->prepare('SELECT * FROM produtos WHERE id = ?');
    $st->execute([$id]);
    $p = $st->fetch() ?: null;
    if (!$p) { flash('danger', 'Produto não encontrado.'); redirect('admin/'); }
}
$categorias = db()->query('SELECT id, nome FROM categorias ORDER BY nome')->fetchAll();
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $d = [
        'nome'         => trim((string)($_POST['nome'] ?? '')),
        'descricao'    => trim((string)($_POST['descricao'] ?? '')),
        'preco'        => str_replace(',', '.', trim((string)($_POST['preco'] ?? ''))),
        'estoque'      => filter_var($_POST['estoque'] ?? null, FILTER_VALIDATE_INT),
        'categoria_id' => filter_var($_POST['categoria_id'] ?? null, FILTER_VALIDATE_INT),
        'ativo'        => isset($_POST['ativo']) ? 1 : 0,
    ];
    if ($d['nome'] === '' || mb_strlen($d['nome']) > 150) $erros[] = 'Nome obrigatório (até 150 caracteres).';
    if ($d['descricao'] === '')                           $erros[] = 'Descrição obrigatória.';
    if (!is_numeric($d['preco']) || (float)$d['preco'] < 0) $erros[] = 'Preço inválido.';
    if ($d['estoque'] === false || $d['estoque'] < 0)     $erros[] = 'Estoque deve ser um inteiro ≥ 0.';
    if (!$d['categoria_id'])                              $erros[] = 'Selecione uma categoria.';

    // upload opcional com validação de tipo real (MIME) e tamanho
    $imagem = $p['imagem'];
    if (!empty($_FILES['imagem']['name']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][(new finfo(FILEINFO_MIME_TYPE))->file($_FILES['imagem']['tmp_name'])] ?? null;
        if (!$ext || $_FILES['imagem']['size'] > 2 * 1024 * 1024) {
            $erros[] = 'Imagem deve ser JPG, PNG ou WEBP de até 2 MB.';
        } else {
            $nomeArq = bin2hex(random_bytes(8)) . '.' . $ext;
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], __DIR__ . '/../assets/uploads/' . $nomeArq)) $imagem = 'uploads/' . $nomeArq;
            else $erros[] = 'Falha ao salvar a imagem.';
        }
    }

    if (!$erros) {
        $v = [$d['categoria_id'], $d['nome'], $d['descricao'], number_format((float)$d['preco'], 2, '.', ''), $d['estoque'], $imagem, $d['ativo']];
        if ($id) {
            db()->prepare('UPDATE produtos SET categoria_id=?, nome=?, descricao=?, preco=?, estoque=?, imagem=?, ativo=? WHERE id=?')->execute([...$v, $id]);
            flash('success', 'Produto atualizado.');
        } else {
            db()->prepare('INSERT INTO produtos (categoria_id, nome, descricao, preco, estoque, imagem, ativo) VALUES (?,?,?,?,?,?,?)')->execute($v);
            flash('success', 'Produto cadastrado.');
        }
        redirect('admin/');
    }
    $p = array_merge($p, $d, ['imagem' => $imagem]);
}

$titulo = $id ? 'Editar produto' : 'Novo produto';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/_menu.php';
?>
<h1 class="h4 mb-3"><?= e($titulo) ?></h1>
<?php foreach ($erros as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" enctype="multipart/form-data" class="card shadow-sm" data-once><div class="card-body row g-3">
  <?= csrf_field() ?>
  <div class="col-md-8"><label class="form-label">Nome</label><input class="form-control" name="nome" maxlength="150" value="<?= e($p['nome']) ?>" required></div>
  <div class="col-md-4"><label class="form-label">Categoria</label>
    <select class="form-select" name="categoria_id" required><option value="">Selecione...</option>
      <?php foreach ($categorias as $c): ?><option value="<?= $c['id'] ?>" <?= (int)$p['categoria_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['nome']) ?></option><?php endforeach; ?>
    </select></div>
  <div class="col-12"><label class="form-label">Descrição</label><textarea class="form-control" name="descricao" rows="4" required><?= e($p['descricao']) ?></textarea></div>
  <div class="col-md-3"><label class="form-label">Preço (R$)</label><input class="form-control" name="preco" inputmode="decimal" value="<?= e((string)$p['preco']) ?>" required></div>
  <div class="col-md-3"><label class="form-label">Estoque</label><input class="form-control" type="number" min="0" name="estoque" value="<?= (int)$p['estoque'] ?>" required></div>
  <div class="col-md-6"><label class="form-label">Imagem (JPG/PNG/WEBP, até 2 MB)</label><input class="form-control" type="file" name="imagem" accept="image/jpeg,image/png,image/webp">
    <?php if ($p['imagem']): ?><img class="thumb mt-2" src="<?= e(img_url($p['imagem'])) ?>" alt=""><?php endif; ?></div>
  <div class="col-12 form-check ms-2"><input class="form-check-input" type="checkbox" name="ativo" id="ativo" <?= $p['ativo'] ? 'checked' : '' ?>><label class="form-check-label" for="ativo">Produto ativo (visível na loja)</label></div>
  <div class="col-12"><button class="btn btn-dark" type="submit">Salvar</button> <a class="btn btn-outline-secondary" href="<?= url('admin/') ?>">Cancelar</a></div>
</div></form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
