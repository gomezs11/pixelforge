<?php $atual = basename($_SERVER['SCRIPT_NAME']); ?>
<ul class="nav nav-pills mb-4">
  <li class="nav-item"><a class="nav-link <?= $atual === 'index.php' ? 'active' : '' ?>" href="<?= url('admin/') ?>">Produtos</a></li>
  <li class="nav-item"><a class="nav-link <?= $atual === 'produto_form.php' ? 'active' : '' ?>" href="<?= url('admin/produto_form.php') ?>">+ Novo produto</a></li>
  <li class="nav-item"><a class="nav-link <?= $atual === 'pedidos.php' ? 'active' : '' ?>" href="<?= url('admin/pedidos.php') ?>">Pedidos</a></li>
</ul>
