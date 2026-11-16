-- PixelForge - Schema MySQL/MariaDB + carga inicial de teste
-- Uso: mysql -u root -p < database/schema.sql   (ou importe pelo phpMyAdmin)
CREATE DATABASE IF NOT EXISTS pixelforge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pixelforge;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS itens_pedido, pedidos, produtos, categorias, usuarios;
SET FOREIGN_KEY_CHECKS = 1;

-- Autenticacao e perfis (e-mail unico, senha em hash bcrypt via password_hash)
CREATE TABLE usuarios (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome        VARCHAR(120) NOT NULL,
  email       VARCHAR(150) NOT NULL,
  senha_hash  VARCHAR(255) NOT NULL,
  perfil      ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
  criado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB;

CREATE TABLE categorias (
  id    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome  VARCHAR(80) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categorias_nome (nome)
) ENGINE=InnoDB;

CREATE TABLE produtos (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  categoria_id  INT UNSIGNED NOT NULL,
  nome          VARCHAR(150) NOT NULL,
  descricao     TEXT NOT NULL,
  preco         DECIMAL(10,2) NOT NULL,
  estoque       INT UNSIGNED NOT NULL DEFAULT 0,
  imagem        VARCHAR(255) NULL,
  ativo         TINYINT(1) NOT NULL DEFAULT 1,
  criado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_produtos_categoria (categoria_id),
  KEY idx_produtos_nome (nome),
  CONSTRAINT fk_produtos_categoria FOREIGN KEY (categoria_id) REFERENCES categorias (id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT ck_produtos_preco CHECK (preco >= 0)
) ENGINE=InnoDB;

CREATE TABLE pedidos (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id       INT UNSIGNED NOT NULL,
  forma_pagamento  ENUM('PIX','Boleto','Cartao') NOT NULL,
  status           ENUM('pago','cancelado') NOT NULL DEFAULT 'pago',
  total            DECIMAL(12,2) NOT NULL,
  criado_em        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_pedidos_usuario (usuario_id),
  CONSTRAINT fk_pedidos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Resolve N:N produtos x pedidos; preco_unitario congela o valor praticado na compra
CREATE TABLE itens_pedido (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  pedido_id       INT UNSIGNED NOT NULL,
  produto_id      INT UNSIGNED NOT NULL,
  quantidade      INT UNSIGNED NOT NULL,
  preco_unitario  DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_item_pedido_produto (pedido_id, produto_id),
  KEY idx_itens_produto (produto_id),
  CONSTRAINT fk_itens_pedido  FOREIGN KEY (pedido_id)  REFERENCES pedidos (id)  ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_itens_produto FOREIGN KEY (produto_id) REFERENCES produtos (id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT ck_itens_qtd CHECK (quantidade > 0)
) ENGINE=InnoDB;

-- ===================== CARGA INICIAL DE TESTE =====================
-- admin@pixelforge.com / Admin@123   |   cliente@teste.com / Cliente@123
INSERT INTO usuarios (nome, email, senha_hash, perfil) VALUES
('Administrador', 'admin@pixelforge.com', '$2y$10$ikmgNNAbrsonM74nIHqS9eXsVH.NeyQmLVM2TqN8glZveh82p8gRK', 'admin'),
('Cliente Teste', 'cliente@teste.com',   '$2y$10$/R4bH08EEGsCrr2jF48F/O/OCIS9LiFDWxycA7Z3Uz6JxYRnvX6Xy', 'cliente');

INSERT INTO categorias (nome) VALUES ('Consoles'), ('Jogos'), ('Periféricos'), ('Acessórios');

INSERT INTO produtos (categoria_id, nome, descricao, preco, estoque, imagem) VALUES
(1, 'Console NovaStation 5 Slim', 'Console de nova geração com SSD ultrarrápido de 1 TB, ray tracing e saída 4K a 120 fps.', 3299.90, 8,  'img/prod-1.svg'),
(1, 'PortaPlay Handheld', 'Portátil com tela OLED de 7", bateria de 8 horas e biblioteca de jogos em nuvem.', 2499.00, 12, 'img/prod-2.svg'),
(2, 'Reino de Aether (Aventura Épica)', 'RPG de mundo aberto com mais de 80 horas de campanha, combate dinâmico e história envolvente.', 249.90, 40, 'img/prod-3.svg'),
(2, 'Turbo Drift 2026', 'Corrida arcade com 120 carros licenciados, multiplayer online e pistas por todo o mundo.', 199.90, 25, 'img/prod-4.svg'),
(2, 'Space Raiders Collection', 'Coletânea com três clássicos de tiro espacial remasterizados. Edição de colecionador.', 129.90, 0, 'img/prod-5.svg'),
(3, 'Teclado Mecânico RGB Phantom', 'Switches ópticos, layout ABNT2, iluminação RGB por tecla e apoio de pulso removível.', 399.90, 30, 'img/prod-6.svg'),
(3, 'Mouse Gamer Viper 26K', 'Sensor de 26.000 DPI, 62 g, 8 botões programáveis e cabo flexível de baixa fricção.', 229.90, 3, 'img/prod-7.svg'),
(3, 'Headset Surround 7.1 Echo', 'Áudio 7.1 virtual, microfone com cancelamento de ruído e almofadas em espuma viscoelástica.', 349.90, 18, 'img/prod-8.svg'),
(4, 'Controle Sem Fio Pro', 'Gatilhos adaptativos, vibração háptica, bateria de 12 horas e compatível com PC e consoles.', 449.00, 22, 'img/prod-9.svg'),
(4, 'Cadeira Gamer Ergo Max', 'Ergonômica, reclinável 180°, com ajuste 4D nos braços e revestimento respirável.', 1299.00, 5, 'img/prod-10.svg');
