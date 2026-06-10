<div class="card">
  <div class="card-body p-4">
    <h5 class="fw-bold mb-4 text-center">Вход в аккаунт</h5>

    <?php if (!empty($error)): ?>
    <div class="alert alert-danger py-2 mb-3">
      <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/login" novalidate>
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

      <div class="mb-3">
        <label class="form-label">Email</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" name="email" class="form-control" placeholder="you@kadrora.ru" required autofocus>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label">Пароль</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" name="password" id="loginPwd" class="form-control" placeholder="••••••" required>
          <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('loginPwd',this)">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
        <i class="bi bi-box-arrow-in-right me-1"></i>Войти
      </button>
    </form>
  </div>
</div>

<p class="text-center mt-3" style="color:var(--text-muted)">
  Нет аккаунта?
  <a href="<?= BASE_URL ?>/register" class="fw-semibold text-accent">Зарегистрироваться</a>
</p>

<script>
function togglePwd(id, btn) {
  const inp = document.getElementById(id);
  inp.type = inp.type === 'password' ? 'text' : 'password';
  btn.innerHTML = inp.type === 'text' ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
}
</script>
