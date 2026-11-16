<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (usuario()) redirect('');

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $senha = (string)($_POST['senha'] ?? '');

    $st = db()->prepare('SELECT id, nome, senha_hash, perfil FROM usuarios WHERE email = ?');
    $st->execute([$email]);
    $u = $st->fetch();

    // hash fictício evita diferença de tempo quando o e-mail não existe
    $hash = $u['senha_hash'] ?? '$2y$10$usesomesillystringforsaleeuIeuQd1m0g8vG3yq0x5Qm0y1h8Ue';
    if (password_verify($senha, $hash) && $u) {
        session_regenerate_id(true);
        $_SESSION['usuario'] = ['id' => (int)$u['id'], 'nome' => $u['nome'], 'perfil' => $u['perfil']];
        $destino = $_SESSION['retorno'] ?? ($u['perfil'] === 'admin' ? url('admin/') : url());
        unset($_SESSION['retorno']);
        flash('success', 'Bem-vindo(a), ' . $u['nome'] . '!');
        redirect($destino);
    }
    $erro = 'E-mail ou senha inválidos.';
}
$titulo = 'Entrar';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center"><div class="col-md-5">
  <div class="card shadow-sm"><div class="card-body p-4">
    <h1 class="h4 mb-3">Entrar</h1>
    <?php if ($erro): ?><div class="alert alert-danger"><?= e($erro) ?></div><?php endif; ?>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <div class="mb-3"><label class="form-label">E-mail</label><input class="form-control" type="email" name="email" required autofocus></div>
      <div class="mb-3"><label class="form-label">Senha</label><input class="form-control" type="password" name="senha" required></div>
      <button class="btn btn-dark w-100" type="submit">Entrar</button>
    </form>
    <p class="mt-3 mb-0 small">Não tem conta? <a href="<?= url('cadastro.php') ?>">Cadastre-se</a></p>
  </div></div>
</div></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
