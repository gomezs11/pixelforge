<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (usuario()) redirect('');

$erros = []; $nome = $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $nome  = trim((string)($_POST['nome'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $senha = (string)($_POST['senha'] ?? '');

    if (mb_strlen($nome) < 3 || mb_strlen($nome) > 120) $erros[] = 'Informe seu nome (3 a 120 caracteres).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))      $erros[] = 'E-mail inválido.';
    if (strlen($senha) < 8)                              $erros[] = 'A senha deve ter ao menos 8 caracteres.';
    if ($senha !== (string)($_POST['senha2'] ?? ''))     $erros[] = 'As senhas não conferem.';

    if (!$erros) {
        $st = db()->prepare('SELECT 1 FROM usuarios WHERE email = ?');
        $st->execute([$email]);
        if ($st->fetch()) {
            $erros[] = 'Este e-mail já está cadastrado.';
        } else {
            // perfil sempre 'cliente': admin só é criado direto no banco
            $ins = db()->prepare("INSERT INTO usuarios (nome, email, senha_hash, perfil) VALUES (?, ?, ?, 'cliente')");
            $ins->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['usuario'] = ['id' => (int)db()->lastInsertId(), 'nome' => $nome, 'perfil' => 'cliente'];
            flash('success', 'Cadastro realizado com sucesso!');
            $destino = $_SESSION['retorno'] ?? url(); unset($_SESSION['retorno']);
            redirect($destino);
        }
    }
}
$titulo = 'Cadastro';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center"><div class="col-md-6">
  <div class="card shadow-sm"><div class="card-body p-4">
    <h1 class="h4 mb-3">Criar conta</h1>
    <?php foreach ($erros as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <div class="mb-3"><label class="form-label">Nome completo</label><input class="form-control" name="nome" value="<?= e($nome) ?>" required></div>
      <div class="mb-3"><label class="form-label">E-mail</label><input class="form-control" type="email" name="email" value="<?= e($email) ?>" required></div>
      <div class="row">
        <div class="col-md-6 mb-3"><label class="form-label">Senha (mín. 8)</label><input class="form-control" type="password" name="senha" minlength="8" required></div>
        <div class="col-md-6 mb-3"><label class="form-label">Confirmar senha</label><input class="form-control" type="password" name="senha2" minlength="8" required></div>
      </div>
      <button class="btn btn-info w-100" type="submit">Cadastrar</button>
    </form>
    <p class="mt-3 mb-0 small">Já tem conta? <a href="<?= url('login.php') ?>">Entrar</a></p>
  </div></div>
</div></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
