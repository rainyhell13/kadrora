<div class="row justify-content-center">
  <div class="col-lg-7">
    <h4 class="fw-bold mb-4"><i class="bi bi-plus-circle me-2" style="color:var(--accent)"></i>Создать сообщество</h4>

    <div class="card">
      <div class="card-body p-4">
        <form method="POST" action="<?= BASE_URL ?>/groups/create">
          <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

          <div class="mb-3">
            <label class="form-label">Название сообщества *</label>
            <input type="text" name="name" class="form-control" placeholder="Например: Любители кошек" required maxlength="150">
          </div>

          <div class="mb-3">
            <label class="form-label">Описание</label>
            <textarea name="description" class="form-control" rows="3"
                      placeholder="О чём это сообщество?" maxlength="1000"></textarea>
          </div>

          <div class="mb-4">
            <label class="form-label">Тип сообщества</label>
            <div class="d-flex gap-2">
              <label class="flex-fill cursor-pointer">
                <input type="radio" name="privacy" value="public" class="d-none" checked>
                <div class="privacy-option p-3 rounded border text-center" style="border-color:var(--accent)!important;background:var(--accent-glow)">
                  <i class="bi bi-globe2 d-block mb-1" style="font-size:1.3rem;color:var(--accent)"></i>
                  <div class="fw-semibold" style="font-size:.875rem">Открытое</div>
                  <div style="font-size:.75rem;color:var(--text-muted)">Все могут вступить</div>
                </div>
              </label>
              <label class="flex-fill cursor-pointer">
                <input type="radio" name="privacy" value="private" class="d-none">
                <div class="privacy-option p-3 rounded border text-center" style="border-color:var(--border)">
                  <i class="bi bi-lock d-block mb-1" style="font-size:1.3rem;color:var(--text-muted)"></i>
                  <div class="fw-semibold" style="font-size:.875rem">Закрытое</div>
                  <div style="font-size:.75rem;color:var(--text-muted)">Только по приглашению</div>
                </div>
              </label>
            </div>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4 fw-semibold">
              <i class="bi bi-check-lg me-1"></i>Создать
            </button>
            <a href="<?= BASE_URL ?>/groups" class="btn btn-outline-secondary">Отмена</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('input[name="privacy"]').forEach(radio => {
  radio.addEventListener('change', () => {
    document.querySelectorAll('.privacy-option').forEach(el => {
      el.style.borderColor = 'var(--border)';
      el.style.background = 'transparent';
    });
    const opt = radio.nextElementSibling;
    opt.style.borderColor = 'var(--accent)';
    opt.style.background = 'var(--accent-glow)';
  });
});
</script>
