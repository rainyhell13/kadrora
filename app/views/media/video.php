<div class="row justify-content-center">
  <div class="col-lg-10">

    <div class="d-flex align-items-center justify-content-between mb-3">
      <h4 class="fw-bold mb-0">
        <i class="bi bi-camera-video me-2" style="color:var(--accent)"></i>Видеозаписи
        <a href="<?= BASE_URL ?>/profile/<?= htmlspecialchars($profile['username']) ?>"
           class="text-decoration-none" style="font-size:.9rem;color:var(--text-muted)">
          — <?= htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']) ?>
        </a>
      </h4>
    </div>

    <!-- Форма загрузки -->
    <?php if ($isOwner): ?>
    <div class="card mb-3">
      <div class="card-body p-3">
        <form method="POST" action="<?= BASE_URL ?>/video/upload" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
          <div class="row g-2 align-items-end">
            <div class="col-md-5">
              <label class="form-label">Название видео *</label>
              <input type="text" name="title" class="form-control form-control-sm" placeholder="Название" required>
            </div>
            <div class="col-md-5">
              <label class="form-label">Файл (mp4, webm)</label>
              <input type="file" name="video" class="form-control form-control-sm" accept="video/*" required>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary btn-sm w-100">
                <i class="bi bi-upload me-1"></i>Загрузить
              </button>
            </div>
          </div>
          <div class="mt-1" style="font-size:.72rem;color:var(--text-muted)">Максимальный размер файла — 100 МБ</div>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <!-- Сетка видео -->
    <?php if (empty($videos)): ?>
    <div class="card text-center py-5" style="color:var(--text-muted)">
      <i class="bi bi-camera-video-off" style="font-size:3rem;opacity:.2;display:block;margin-bottom:12px"></i>
      <p class="mb-0">Видеозаписей пока нет</p>
    </div>
    <?php else: ?>
    <div class="row g-3">
      <?php foreach ($videos as $v): ?>
      <div class="col-sm-6 col-md-4" id="video-<?= $v['id'] ?>">
        <div class="card">
          <video class="w-100" style="border-radius:var(--radius) var(--radius) 0 0;background:#000;max-height:200px"
                 controls preload="metadata">
            <source src="<?= BASE_URL ?>/uploads/video/<?= htmlspecialchars($v['filename']) ?>">
            Ваш браузер не поддерживает видео.
          </video>
          <div class="p-2">
            <div class="d-flex justify-content-between align-items-start gap-1">
              <div class="fw-semibold" style="font-size:.875rem"><?= htmlspecialchars($v['title']) ?></div>
              <?php if ($isOwner): ?>
              <button class="btn btn-sm border-0 px-1 flex-shrink-0" style="background:none;color:var(--text-muted)"
                      onclick="deleteVideo(<?= $v['id'] ?>)" title="Удалить">
                <i class="bi bi-trash"></i>
              </button>
              <?php endif; ?>
            </div>
            <div style="font-size:.72rem;color:var(--text-muted)"><?= timeAgo($v['created_at']) ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
function deleteVideo(id) {
  if (!confirm('Удалить видеозапись?')) return;
  postAction(BASE_URL + '/video/delete', { video_id: id }, () => {
    document.getElementById('video-' + id)?.remove();
    showToast('Удалено', 'info');
  });
}
</script>
