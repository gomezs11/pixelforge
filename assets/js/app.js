// Fecha alertas automaticamente e evita duplo envio de formularios.
document.querySelectorAll('.alert').forEach(a => setTimeout(() => bootstrap.Alert.getOrCreateInstance(a).close(), 6000));
document.querySelectorAll('form[data-once]').forEach(f => f.addEventListener('submit', () => {
  f.querySelectorAll('button[type=submit]').forEach(b => { b.disabled = true; b.textContent = 'Processando...'; });
}));
