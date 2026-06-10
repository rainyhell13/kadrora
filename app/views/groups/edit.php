<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <h4 class="fw-bold mb-0">
        <i class="bi bi-gear me-2" style="color:var(--accent)"></i>Управление сообществом
      </h4>
      <a href="<?= BASE_URL ?>/groups/<?= htmlspecialchars($group['slug']) ?>"
         class="btn btn-outline-secondary btn-sm">← Назад</a>
    </div>

    <ul class="nav nav-tabs mb-4">
      <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-info">Основное</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-danger">Опасная зона</button></li>
    </ul>

    <div class="tab-content">
      <div class="tab-pane fade show active" id="tab-info">
        <div class="card">
          <div class="card-body p-4">
            <form method="POST" action="<?= BASE_URL ?>/groups/<?= htmlspecialchars($group['slug']) ?>/edit"
                  enctype="multipart/form-data">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

              <!-- Avatar -->
              <div class="mb-4 text-center">
                <?php if ($group['avatar']): ?>
                <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($group['avatar']) ?>"
                     id="avatarPreview" class="rounded-circle" width="90" height="90"
                     style="object-fit:cover;border:3px solid var(--border)" alt="">
                <?php else: ?>
                <div class="avatar-placeholder mx-auto" id="avatarPlaceholder"
                     style="width:90px;height:90px;font-size:2.2rem">
                  <?= mb_strtoupper(mb_substr($group['name'],0,1)) ?>
                </div>
                <?php endif; ?>
                <div class="mt-2">
                  <label for="avatarInput" class="btn btn-outline-secondary btn-sm cursor-pointer">
                    <i class="bi bi-camera me-1"></i>Сменить аватар
                  </label>
                  <input type="file" id="avatarInput" name="avatar" class="d-none" accept="image/*"
                         onchange="previewAvatar(this)">
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Название</label>
                <input type="text" name="name" class="form-control"
                       value="<?= htmlspecialchars($group['name']) ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Описание</label>
                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($group['description'] ?? '') ?></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Тип</label>
                <select name="privacy" class="form-select">
                  <option value="public"  <?= $group['privacy']==='public'  ? 'selected':'' ?>>Открытое</option>
                  <option value="private" <?= $group['privacy']==='private' ? 'selected':'' ?>>Закрытое</option>
                </select>
              </div>
              <div class="mb-4">
                <label class="form-label">Обложка</label>
                <?php if ($group['cover']): ?>
                <div class="mb-2">
                  <img src="<?= BASE_URL ?>/uploads/photos/<?= htmlspecialchars($group['cover']) ?>"
                       class="img-fluid rounded" style="max-height:100px" alt="">
                </div>
                <?php endif; ?>
                <input type="file" name="cover" class="form-control" accept="image/*">
              </div>

              <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg me-1"></i>Сохранить изменения
              </button>
            </form>
          </div>
        </div>
      </div>

      <div class="tab-pane fade" id="tab-danger">
        <div class="card" style="border-color:var(--danger)!important">
          <div class="card-body p-4">
            <h6 class="fw-bold mb-1" style="color:var(--danger)">Удалить сообщество</h6>
            <p style="color:var(--text-muted);font-size:.875rem">
              Это действие необратимо. Все записи и данные сообщества будут удалены.
            </p>
            <form method="POST" action="<?= BASE_URL ?>/groups/<?= htmlspecialchars($group['slug']) ?>/delete"
                  onsubmit="return confirm('Вы уверены? Это действие нельзя отменить!')">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
              <button type="submit" class="btn btn-danger btn-sm">
                <i class="bi bi-trash me-1"></i>Удалить сообщество навсегда
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function previewAvatar(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      const img = document.getElementById('avatarPreview');
      const ph  = document.getElementById('avatarPlaceholder');
      if (img) img.src = e.target.result;
      else if (ph) {
        const ni = document.createElement('img');
        ni.id='avatarPreview'; ni.className='rounded-circle'; ni.width=ni.height=90;
        ni.style='object-fit:cover;border:3px solid var(--border)'; ni.src=e.target.result;
        ph.replaceWith(ni);
      }
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>
