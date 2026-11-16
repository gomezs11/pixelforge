<?php
declare(strict_types=1);

/** Carrega variaveis do arquivo .env para $_ENV/getenv. */
function carregar_env(string $arquivo): void
{
    if (!is_file($arquivo)) {
        http_response_code(500);
        exit('Arquivo .env nao encontrado. Copie .env.example para .env e configure.');
    }
    foreach (file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linha) {
        $linha = trim($linha);
        if ($linha === '' || $linha[0] === '#' || !str_contains($linha, '=')) {
            continue;
        }
        [$chave, $valor] = explode('=', $linha, 2);
        $valor = trim($valor, " \t\"'");
        $_ENV[trim($chave)] = $valor;
        putenv(trim($chave) . '=' . $valor);
    }
}

function env(string $chave, ?string $padrao = null): ?string
{
    if (isset($_ENV[$chave])) {
        return $_ENV[$chave];
    }
    $v = getenv($chave);
    return $v === false ? $padrao : $v;
}
