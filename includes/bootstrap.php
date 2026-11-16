<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';
carregar_env(dirname(__DIR__) . '/.env');

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
session_start();

/* ---------- Utilitarios ---------- */
function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function url(string $p = ''): string { return rtrim(env('APP_BASE', ''), '/') . '/' . ltrim($p, '/'); }
function redirect(string $p): void { header('Location: ' . (str_starts_with($p, 'http') || str_starts_with($p, '/') ? $p : url($p))); exit; }
function brl(float|string $v): string { return 'R$ ' . number_format((float)$v, 2, ',', '.'); }
function img_url(?string $img): string { return url('assets/' . ($img ?: 'img/placeholder.svg')); }

function flash(string $tipo, string $msg): void { $_SESSION['flash'][] = [$tipo, $msg]; }
function flash_render(): void
{
    foreach ($_SESSION['flash'] ?? [] as [$t, $m]) {
        echo '<div class="alert alert-' . e($t) . ' alert-dismissible fade show" role="alert">' . e($m)
           . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    unset($_SESSION['flash']);
}

/* ---------- CSRF ---------- */
function csrf_token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_verificar(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
        http_response_code(419);
        exit('Sessao expirada ou requisicao invalida. Volte e tente novamente.');
    }
}

/* ---------- Autenticacao / Autorizacao (validadas no servidor) ---------- */
function usuario(): ?array { return $_SESSION['usuario'] ?? null; }
function exigir_login(): void
{
    if (!usuario()) {
        $_SESSION['retorno'] = $_SERVER['REQUEST_URI'];
        flash('warning', 'Faça login para continuar.');
        redirect('login.php');
    }
}
function exigir_admin(): void
{
    exigir_login();
    if (usuario()['perfil'] !== 'admin') {
        http_response_code(403);
        exit('Acesso negado: area restrita a administradores.');
    }
}

/* ---------- Carrinho em $_SESSION: [produto_id => quantidade] ---------- */
function carrinho(): array { return $_SESSION['carrinho'] ?? []; }
function carrinho_qtd(): int { return array_sum(carrinho()); }

/** Retorna [linhas, total] com dados atuais do banco e subtotais. */
function carrinho_itens(): array
{
    $c = carrinho();
    if (!$c) return [[], 0.0];
    $ids = array_map('intval', array_keys($c));
    $in = implode(',', array_fill(0, count($ids), '?'));
    $st = db()->prepare("SELECT id, nome, preco, estoque, imagem FROM produtos WHERE ativo = 1 AND id IN ($in)");
    $st->execute($ids);
    $itens = []; $total = 0.0;
    foreach ($st->fetchAll() as $p) {
        $p['qtd'] = min($c[$p['id']], (int)$p['estoque']);
        $p['subtotal'] = $p['qtd'] * (float)$p['preco'];
        $total += $p['subtotal'];
        $itens[] = $p;
    }
    return [$itens, $total];
}
