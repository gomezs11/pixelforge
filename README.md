# ⚒ PixelForge — E-commerce de Games e Tecnologia

Aplicação web de comércio eletrônico com fluxo completo de venda: vitrine → produto → carrinho (sessão) → login/cadastro → checkout simulado → baixa de estoque → painel administrativo.

**Stack:** HTML5, CSS3, Bootstrap 5, JavaScript, PHP 8.0+ (Apache/XAMPP), MySQL/MariaDB via PDO.

## Como executar (XAMPP)
1. Copie a pasta para `htdocs/pixelforge` e inicie **Apache** e **MySQL**.
2. Importe o banco: `database/schema.sql` (phpMyAdmin → Importar, ou `mysql -u root < database/schema.sql`).
3. Crie o `.env` a partir do modelo: `cp .env.example .env` e ajuste usuário/senha do banco e `APP_BASE`.
4. Acesse `http://localhost/pixelforge/`.

## Contas de teste
| Perfil  | E-mail                 | Senha        |
|---------|------------------------|--------------|
| Admin   | admin@pixelforge.com   | Admin@123    |
| Cliente | cliente@teste.com      | Cliente@123  |

## Requisitos atendidos
- **.env**: credenciais fora do código (`config/env.php`); `.env` no `.gitignore`; modelo em `.env.example`.
- **Segurança**: 100% Prepared Statements (PDO, `EMULATE_PREPARES=false`); `password_hash()`/`password_verify()`; rotas `/admin` protegidas no servidor (`exigir_admin()`); CSRF token em todos os POSTs; escape de saída (`e()`); `session_regenerate_id` no login; cookies HttpOnly/SameSite; upload validado por MIME real.
- **Catálogo**: listagem dinâmica, busca e filtro por categoria, página de produto com estoque.
- **Carrinho** em `$_SESSION`: adicionar, alterar quantidade (limitada ao estoque), remover, totais dinâmicos.
- **Checkout** restrito a autenticados; PIX/Boleto/Cartão simulados; transação (`FOR UPDATE`) cria pedido, itens com **preço congelado**, decrementa estoque e limpa o carrinho.
- **Admin**: CRUD de produtos (produto com vendas é desativado, preservando histórico) e listagem de pedidos com itens e total.

## Estrutura
```
admin/      painel (index, produto_form, produto_excluir, pedidos)
assets/     css, js, img, uploads
config/     env.php (carrega .env) e db.php (PDO)
database/   schema.sql (tabelas + carga inicial)
includes/   bootstrap.php (sessão, CSRF, auth, carrinho), header, footer
*.php       index, produto, carrinho, login, cadastro, logout, checkout, pedido_sucesso
```

## Fluxo Git sugerido (histórico contínuo da equipe)
```bash
git init && git branch -M main
git add .gitignore .env.example README.md && git commit -m "chore: estrutura inicial, .gitignore e .env.example"
git add database && git commit -m "feat(db): schema.sql com tabelas, FKs e carga de teste"
git add config includes && git commit -m "feat: configuração por .env, conexão PDO e helpers de sessão/CSRF"
git add login.php cadastro.php logout.php && git commit -m "feat(auth): login e cadastro com password_hash"
git add index.php produto.php assets && git commit -m "feat(catalogo): vitrine, busca, filtro e detalhe do produto"
git add carrinho.php && git commit -m "feat(carrinho): carrinho em sessão com validação de estoque"
git add checkout.php pedido_sucesso.php && git commit -m "feat(checkout): pagamento simulado, pedido e baixa de estoque"
git add admin && git commit -m "feat(admin): CRUD de produtos e listagem de pedidos"
git remote add origin <URL_DO_REPOSITORIO> && git push -u origin main
```
> Dica: em equipe, faça commits por integrante/funcionalidade (branches + pull requests) para o histórico refletir o trabalho de cada um.
