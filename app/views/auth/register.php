<div class="card shadow-sm border-0">
  <div class="card-body p-4">
    <h4 class="card-title mb-4 text-center fw-semibold">Регистрация</h4>

    <form method="POST" action="<?= BASE_URL ?>/register" novalidate>
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

      <div class="row g-2 mb-3">
        <div class="col-6">
          <label class="form-label fw-medium">Имя</label>
          <input type="text" name="first_name" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                 value="<?= htmlspecialchars($old['first_name'] ?? '') ?>" placeholder="Иван" required>
          <?php if (!empty($errors['first_name'])): ?>
          <div class="invalid-feedback"><?= $errors['first_name'] ?></div>
          <?php endif; ?>
        </div>
        <div class="col-6">
          <label class="form-label fw-medium">Фамилия</label>
          <input type="text" name="last_name" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>"
                 value="<?= htmlspecialchars($old['last_name'] ?? '') ?>" placeholder="Иванов" required>
          <?php if (!empty($errors['last_name'])): ?>
          <div class="invalid-feedback"><?= $errors['last_name'] ?></div>
          <?php endif; ?>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-medium">Имя пользователя</label>
        <div class="input-group">
          <span class="input-group-text">@</span>
          <input type="text" name="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                 value="<?= htmlspecialchars($old['username'] ?? '') ?>" placeholder="ivan_ivanov" required>
          <?php if (!empty($errors['username'])): ?>
          <div class="invalid-feedback"><?= $errors['username'] ?></div>
          <?php endif; ?>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-medium">Email</label>
        <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
               value="<?= htmlspecialchars($old['email'] ?? '') ?>" placeholder="you@example.com" required>
        <?php if (!empty($errors['email'])): ?>
        <div class="invalid-feedback"><?= $errors['email'] ?></div>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label class="form-label fw-medium">Пароль</label>
        <div class="input-group">
          <input type="password" name="password" id="regPwd" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                 placeholder="Минимум 6 символов" required>
          <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('regPwd',this)">
            <i class="bi bi-eye"></i>
          </button>
          <?php if (!empty($errors['password'])): ?>
          <div class="invalid-feedback"><?= $errors['password'] ?></div>
          <?php endif; ?>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-medium">Повторите пароль</label>
        <input type="password" name="password2" class="form-control <?= isset($errors['password2']) ? 'is-invalid' : '' ?>"
               placeholder="••••••" required>
        <?php if (!empty($errors['password2'])): ?>
        <div class="invalid-feedback"><?= $errors['password2'] ?></div>
        <?php endif; ?>
      </div>

      <div class="row g-2 mb-3">
        <div class="col-6">
          <label class="form-label fw-medium">Пол</label>
          <select name="gender" class="form-select">
            <option value="">Не указан</option>
            <option value="male"   <?= ($old['gender'] ?? '') === 'male'   ? 'selected' : '' ?>>Мужской</option>
            <option value="female" <?= ($old['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Женский</option>
            <option value="other"  <?= ($old['gender'] ?? '') === 'other'  ? 'selected' : '' ?>>Другой</option>
          </select>
        </div>
        <div class="col-6">
          <label class="form-label fw-medium">Дата рождения</label>
          <input type="date" name="birth_date" class="form-control"
                 value="<?= htmlspecialchars($old['birth_date'] ?? '') ?>">
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label fw-medium">Город</label>
        <input type="text" name="city" class="form-control"
               value="<?= htmlspecialchars($old['city'] ?? '') ?>" placeholder="Москва">
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
        <i class="bi bi-person-plus me-1"></i>Создать аккаунт
      </button>
    </form>
  </div>
</div>

<p class="text-center mt-3 text-muted">
  Уже есть аккаунт? <a href="<?= BASE_URL ?>/login" class="fw-medium">Войти</a>
</p>

<script>
function togglePwd(id, btn) {
  const inp = document.getElementById(id);
  const show = inp.type === 'password';
  inp.type = show ? 'text' : 'password';
  btn.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
}
</script>
